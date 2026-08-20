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
    public function chat(string $message, array $history = [], array $context = []): array {
        $url = $this->host . '/api/chat';
        
        $messages = [];
        
        // Include context as system instructions if present
        if (!empty($context)) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->formatContext($context)
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
    
    /**
     * Get list of locally available models from Ollama
     */
    public function getInstalledModels(): array {
        $url = $this->host . '/api/tags';
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            $resp = curl_exec($ch);
            curl_close($ch);
            
            $data = json_decode($resp, true);
            $models = [];
            if (!empty($data['models'])) {
                foreach ($data['models'] as $m) {
                    if (!empty($m['name'])) {
                        $models[] = $m['name'];
                    }
                }
            }
            return !empty($models) ? $models : ['Brain32:latest'];
        } catch (Exception $e) {
            return ['Brain32:latest'];
        }
    }
    
    /**
     * Make HTTP request to Ollama API
     */
    private function makeRequest(string $url, array $body): array {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if (!empty($curlError)) {
            throw new ApiException("Cannot connect to local Ollama server at {$this->host}: $curlError. Ensure 'ollama serve' is active.", 503);
        }
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $errorMsg = $error['error'] ?? "Ollama returned HTTP $httpCode";
            try {
                $this->logUsage(0, 0, 'error', $errorMsg);
            } catch (Exception $e) {}
            throw new ApiException("Local Ollama Error: $errorMsg", $httpCode);
        }
        
        return json_decode($response, true) ?? [];
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
            'ollama/chat',
            $promptTokens,
            $responseTokens,
            $totalTokens,
            $this->model,
            $status,
            $error
        ]);

        if ($totalTokens > 0) {
            $sqlToken = "INSERT INTO token_usage (prompt_tokens, response_tokens, total_tokens) VALUES (?, ?, ?)";
            $this->executeQuery($sqlToken, [$promptTokens, $responseTokens, $totalTokens]);
        }
    }
    
    /**
     * Scan news using local Ollama model
     */
    public function scanNews(string $topic, string $angle): string {
        $prompt = "Find 4-6 recent tech news angles about $topic focusing on $angle. Return a valid JSON array of objects with keys 'title', 'summary' (80-120 words), 'angles' (array of strings), 'sources' (array of strings). Return ONLY raw JSON.";
        $res = $this->chat($prompt);
        return $res['reply'] ?? '[]';
    }
}
