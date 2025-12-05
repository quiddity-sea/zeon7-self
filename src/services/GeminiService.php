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
    public function __construct(string $apiKey, string $model = 'gemini-pro') {
        parent::__construct();
        $this->apiKey = $apiKey;
        $this->model = $model;
    }
    
    /**
     * Generate content from prompt
     */
    public function generateContent(string $prompt, array $context = []): string {
        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        // Build request body
        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];
        
        // Add context if provided
        if (!empty($context)) {
            $contextText = $this->formatContext($context);
            $requestBody['contents'][0]['parts'][] = ['text' => $contextText];
        }
        
        // Make API request
        $response = $this->makeRequest($url, $requestBody);
        
        // Extract and return text
        return $this->extractText($response);
    }
    
    /**
     * Conversational chat with history
     */
    public function chat(string $message, array $history = []): array {
        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        // Build conversation history
        $contents = [];
        foreach ($history as $turn) {
            $contents[] = [
                'role' => $turn['role'] ?? 'user',
                'parts' => [['text' => $turn['content']]]
            ];
        }
        
        // Add current message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];
        
        $requestBody = ['contents' => $contents];
        
        $response = $this->makeRequest($url, $requestBody);
        $reply = $this->extractText($response);
        
        // Log usage
        $tokens = $this->extractTokenUsage($response);
        $this->logUsage($tokens['prompt'], $tokens['response'], 'success');
        
        return [
            'reply' => $reply,
            'usage' => $tokens
        ];
    }
    
    /**
     * Make HTTP request to Gemini API
     */
    private function makeRequest(string $url, array $body): array {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $errorMsg = $error['error']['message'] ?? 'Unknown error';
            $this->logUsage(0, 0, 'error', $errorMsg);
            throw new ApiException("Gemini API error: $errorMsg", $httpCode);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Extract text from API response
     */
    private function extractText(array $response): string {
        return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
    
    /**
     * Extract token usage from response
     */
    private function extractTokenUsage(array $response): array {
        $usage = $response['usageMetadata'] ?? [];
        
        return [
            'prompt' => $usage['promptTokenCount'] ?? 0,
            'response' => $usage['candidatesTokenCount'] ?? 0,
            'total' => $usage['totalTokenCount'] ?? 0
        ];
    }
    
    /**
     * Format context array into text
     */
    private function formatContext(array $context): string {
        $formatted = "\n\n--- Context ---\n";
        
        foreach ($context as $key => $value) {
            $formatted .= "$key: $value\n";
        }
        
        return $formatted;
    }
    
    /**
     * Log API usage to database
     */
    public function logUsage(int $promptTokens, int $responseTokens, string $status, ?string $error = null): void {
        $sql = "INSERT INTO gemini_log (endpoint, prompt_tokens, response_tokens, total_tokens, model, status, error_message) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $totalTokens = $promptTokens + $responseTokens;
        
        $this->executeQuery($sql, [
            'generateContent',
            $promptTokens,
            $responseTokens,
            $totalTokens,
            $this->model,
            $status,
            $error
        ]);

        // Log to generic token_usage table (for persistent counter)
        if ($totalTokens > 0) {
            $sqlToken = "INSERT INTO token_usage (prompt_tokens, response_tokens, total_tokens) VALUES (?, ?, ?)";
            $this->executeQuery($sqlToken, [$promptTokens, $responseTokens, $totalTokens]);
        }
    }
    /**
     * Scan news using Google Search Grounding
     */
    public function scanNews(string $topic, string $angle): string {
        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        $prompt = "Find 4-6 recent news stories about $topic. Focus on $angle. Return a JSON array of objects. Each object must have: 'title' (string), 'summary' (80-120 words), 'angles' (array of 3-5 strings), 'sources' (array of strings). Do not use Markdown formatting, just raw JSON.";
        
        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'tools' => [
                ['google_search' => new stdClass()]
            ]
        ];
        
        $response = $this->makeRequest($url, $requestBody);
        return $this->extractText($response);
    }

    /**
     * Analyze image with Vision
     */
    public function scanVision(string $imageData, string $mimeType, string $prompt): string {
        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => base64_encode($imageData)
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        $response = $this->makeRequest($url, $requestBody);
        return $this->extractText($response);
    }
}
