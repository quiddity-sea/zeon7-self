<?php
/**
 * OpenRouterService - OpenRouter API integration (alternative AI provider)
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';

class OpenRouterService extends BaseService {
    
    private string $apiKey;
    private string $model;
    private string $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    
    /**
     * Initialize with API key and model
     */
    public function __construct(string $apiKey, string $model = 'openai/gpt-4') {
        parent::__construct();
        $this->apiKey = $apiKey;
        $this->model = $model;
    }
    
    /**
     * Generate content from prompt
     */
    public function generateContent(string $prompt, array $context = []): string {
        $res = $this->chat($prompt, [], !empty($context) ? $this->formatContext($context) : '');
        return $res['reply'];
    }
    
    /**
     * Conversational chat with history and system instruction
     */
    public function chat(string $message, array $history = [], string $systemPrompt = '', array $tools = []): array {
        $messages = [];

        if (!empty($systemPrompt)) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }
        
        foreach ($history as $turn) {
            $messages[] = [
                'role' => $turn['role'] ?? 'user',
                'content' => $turn['content'] ?? ''
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $requestBody = [
            'model' => $this->model,
            'messages' => $messages
        ];
        
        if (!empty($tools)) {
            $requestBody['tools'] = $tools;
            // Some models require tool_choice to be explicitly set
            $requestBody['tool_choice'] = 'auto';
        }
        
        $response = $this->makeRequest($requestBody);
        
        $messageObj = $response['choices'][0]['message'] ?? [];
        
        // Check if the model decided to call a tool
        if (!empty($messageObj['tool_calls'])) {
            $toolCall = $messageObj['tool_calls'][0]['function'];
            return [
                'functionCall' => [
                    'name' => $toolCall['name'],
                    'args' => is_string($toolCall['arguments']) ? json_decode($toolCall['arguments'], true) : ($toolCall['arguments'] ?? [])
                ],
                'usage' => [
                    'prompt' => $response['usage']['prompt_tokens'] ?? 0,
                    'response' => $response['usage']['completion_tokens'] ?? 0,
                    'total' => $response['usage']['total_tokens'] ?? 0
                ]
            ];
        }
        
        return [
            'reply' => $messageObj['content'] ?? '',
            'usage' => [
                'prompt' => $response['usage']['prompt_tokens'] ?? 0,
                'response' => $response['usage']['completion_tokens'] ?? 0,
                'total' => $response['usage']['total_tokens'] ?? 0
            ]
        ];
    }
    
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
}
