<?php
/**
 * OllamaService - Local Ollama AI Integration
 * Supports Brain32:latest and any local Ollama models with dynamic think toggle
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';
require_once __DIR__ . '/ConfigService.php';

class OllamaService extends BaseService {
    
    private string $model;
    private string $host;
    private bool $think;
    
    public function __construct(string $model = 'Brain32:latest', ?string $host = null, ?bool $think = null) {
        parent::__construct();
        $this->model = !empty($model) ? $model : 'Brain32:latest';
        
        $config = new ConfigService();
        $dbHost = $config->getOllamaHost();
        $envHost = $_ENV['OLLAMA_HOST'] ?? '';

        if (!empty($host)) {
            $this->host = rtrim($host, '/');
        } elseif (!empty($dbHost)) {
            $this->host = rtrim($dbHost, '/');
        } elseif (!empty($envHost)) {
            $this->host = rtrim($envHost, '/');
        } else {
            $this->host = 'http://127.0.0.1:11434';
        }
        
        if ($think !== null) {
            $this->think = $think;
        } else {
            $this->think = $config->getOllamaThink();
        }
    }
    
    /**
     * Send chat request to Ollama
     */
    public function chat(string $message, array $history = [], string|array $context = ''): array {
        $url = $this->host . '/api/chat';
        
        $messages = [];
        
        // Include system prompt as system role
        if (!empty($context)) {
            $systemContent = is_array($context) ? $this->formatContext($context) : (string)$context;
            $messages[] = [
                'role' => 'system',
                'content' => $systemContent
            ];
        }
        
        // Append history
        foreach ($history as $turn) {
            $role = ($turn['role'] === 'user') ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => $turn['content'] ?? ''
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'think' => $this->think,
            'options' => [
                'temperature' => 0.7
            ]
        ];
        
        $response = $this->makeRequest($url, $payload);
        
        $rawReply = $response['message']['content'] ?? '';
        
        if (!$this->think) {
            // Strip any residual <think>...</think> tags if think is disabled
            $reply = trim(preg_replace('/<think>[\s\S]*?<\/think>/i', '', $rawReply));
            if (empty($reply) && !empty($rawReply)) {
                $reply = trim($rawReply);
            }
        } else {
            $reply = trim($rawReply);
        }
        
        $promptTokens = (int)($response['prompt_eval_count'] ?? 0);
        $responseTokens = (int)($response['eval_count'] ?? 0);
        $totalTokens = $promptTokens + $responseTokens;
        
        $tokens = [
            'prompt' => $promptTokens,
            'response' => $responseTokens,
            'total' => $totalTokens
        ];
        
        try {
            $this->logUsage($promptTokens, $responseTokens, 'success');
        } catch (Exception $e) {}
        
        return [
            'reply' => $reply,
            'usage' => $tokens
        ];
    }

    public function generateContent(string $prompt, array $context = []): string {
        $res = $this->chat($prompt, [], $context);
        return $res['reply'];
    }
    
    private function makeRequest(string $url, array $body): array {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if (!empty($curlError)) {
            throw new ApiException("Network error connecting to Ollama: $curlError", 500);
        }
        
        if ($httpCode !== 200) {
            throw new ApiException("Ollama API error (HTTP $httpCode): " . $response, $httpCode);
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            throw new ApiException("Invalid JSON response from Ollama", 500);
        }
        
        return $data;
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
    
    private function logUsage(int $promptTokens, int $responseTokens, string $status): void {
        $sql = "INSERT INTO token_usage (provider, model, prompt_tokens, completion_tokens, total_tokens, request_status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $this->executeQuery($sql, [
            'ollama',
            $this->model,
            $promptTokens,
            $responseTokens,
            $promptTokens + $responseTokens,
            $status
        ]);
    }
}
