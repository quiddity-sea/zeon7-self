<?php
/**
 * GeminiService - Google Gemini API integration
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';

class GeminiService extends BaseService {
    
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    /**
     * Initialize with API key and model
     */
    public function __construct(string $apiKey, string $model = 'gemini-2.5-flash') {
        parent::__construct();
        $this->apiKey = $apiKey;
        $this->model = !empty($model) ? $model : 'gemini-2.5-flash';
    }
    
    /**
     * Generate content from prompt
     */
    public function generateContent(string $prompt, array $context = []): string {
        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];
        
        if (!empty($context)) {
            $contextText = $this->formatContext($context);
            $requestBody['systemInstruction'] = [
                'parts' => [['text' => $contextText]]
            ];
        }
        
        $response = $this->makeRequest($url, $requestBody);
        return $this->extractText($response);
    }
    
    /**
     * Conversational chat with history and system instruction
     */
    public function chat(string $message, array $history = [], string $systemPrompt = '', array $tools = []): array {
        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        $contents = [];
        foreach ($history as $turn) {
            $role = (isset($turn['role']) && ($turn['role'] === 'assistant' || $turn['role'] === 'model')) ? 'model' : 'user';
            
            $part = [];
            if (isset($turn['functionCall'])) {
                $part['functionCall'] = $turn['functionCall'];
            } elseif (isset($turn['functionResponse'])) {
                $part['functionResponse'] = $turn['functionResponse'];
            } else {
                $part['text'] = $turn['content'] ?? '';
            }
            
            $contents[] = [
                'role' => $role,
                'parts' => [$part]
            ];
        }
        
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];
        
        $requestBody = ['contents' => $contents];

        if (!empty($systemPrompt)) {
            $requestBody['systemInstruction'] = [
                'parts' => [['text' => $systemPrompt]]
            ];
        }
        
        if (!empty($tools)) {
            $requestBody['tools'] = [['functionDeclarations' => $tools]];
        }
        
        $response = $this->makeRequest($url, $requestBody);
        
        // Extract reply or function call
        $part = $response['candidates'][0]['content']['parts'][0] ?? [];
        $reply = $part['text'] ?? '';
        $functionCall = $part['functionCall'] ?? null;
        
        // Log usage
        $tokens = $this->extractTokenUsage($response);
        try {
            $this->logUsage($tokens['prompt'], $tokens['response'], 'success');
        } catch (Exception $e) {}
        
        $result = [
            'reply' => $reply,
            'usage' => $tokens
        ];
        
        if ($functionCall) {
            $result['functionCall'] = $functionCall;
        }
        
        return $result;
    }
    
    /**
     * Make HTTP request to Gemini API
     */
    private function makeRequest(string $url, array $body): array {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Increased timeout for tool use
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if (!empty($curlError)) {
            throw new ApiException("Network error connecting to Gemini API: $curlError", 500);
        }
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $errorMsg = $error['error']['message'] ?? "HTTP $httpCode error";
            throw new ApiException("Gemini API error: $errorMsg", $httpCode);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Extract text from Gemini response
     */
    private function extractText(array $response): string {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
    
    /**
     * Extract token usage from Gemini response
     */
    private function extractTokenUsage(array $response): array {
        $meta = $response['usageMetadata'] ?? [];
        return [
            'prompt' => $meta['promptTokenCount'] ?? 0,
            'response' => $meta['candidatesTokenCount'] ?? 0,
            'total' => $meta['totalTokenCount'] ?? 0
        ];
    }
    
    /**
     * Format context array into readable string
     */
    private function formatContext(array $context): string {
        $lines = [];
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $lines[] = strtoupper($key) . ": " . implode(", ", $value);
            } else {
                $lines[] = strtoupper($key) . ": $value";
            }
        }
        return implode("\n", $lines);
    }
    
    /**
     * Log token usage to database
     */
    private function logUsage(int $promptTokens, int $responseTokens, string $status): void {
        $sql = "INSERT INTO token_usage (provider, model, prompt_tokens, completion_tokens, total_tokens, request_status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $this->executeQuery($sql, [
            'gemini',
            $this->model,
            $promptTokens,
            $responseTokens,
            $promptTokens + $responseTokens,
            $status
        ]);
    }
}
