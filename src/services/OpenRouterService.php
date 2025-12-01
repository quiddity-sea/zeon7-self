<?php
/**
 * OpenRouterService - OpenRouter API integration (alternative AI provider)
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';

class OpenRouterService {
    
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    
    /**
     * Initialize with API key and model
     */
    public function __construct(string $apiKey, string $model = 'openai/gpt-4') {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }
    
    /**
     * Generate content from prompt
     */
    public function generateContent(string $prompt, array $context = []): string {
        // Format context if provided
        $fullPrompt = empty($context) ? $prompt : $this->formatContext($context) . "\n\n" . $prompt;
        
        $requestBody = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $fullPrompt]
            ]
        ];
        
        $response = $this->makeRequest($requestBody);
        return $response['choices'][0]['message']['content'] ?? '';
    }
    
    /**
     * Conversational chat with history
     */
    public function chat(string $message, array $history = []): array {
        // Build message array
        $messages = [];
        
        foreach ($history as $turn) {
            $messages[] = [
                'role' => $turn['role'] ?? 'user',
                'content' => $turn['content']
            ];
        }
        
        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $requestBody = [
            'model' => $this->model,
            'messages' => $messages
        ];
        
        $response = $this->makeRequest($requestBody);
        
        return [
            'reply' => $response['choices'][0]['message']['content'] ?? '',
            'usage' => [
                'prompt' => $response['usage']['prompt_tokens'] ?? 0,
                'response' => $response['usage']['completion_tokens'] ?? 0,
                'total' => $response['usage']['total_tokens'] ?? 0
            ]
        ];
    }
    
    /**
     * Make HTTP request to OpenRouter API
     */
    private function makeRequest(array $body): array {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: https://zeon7.com',
            'X-Title: Zeon7 Platform'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $errorMsg = $error['error']['message'] ?? 'Unknown error';
            throw new ApiException("OpenRouter API error: $errorMsg", $httpCode);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Format context array into text
     */
    private function formatContext(array $context): string {
        $formatted = "--- Context ---\n";
        
        foreach ($context as $key => $value) {
            $formatted .= "$key: $value\n";
        }
        
        return $formatted;
    }
}
