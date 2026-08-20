<?php
/**
 * ConfigService - Application configuration and secrets management
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/EncryptionService.php';

class ConfigService extends BaseService {
    
    private array $config = [];
    private EncryptionService $encryption;
    
    public function __construct() {
        parent::__construct();
        $this->encryption = new EncryptionService();
        $this->loadConfig();
    }
    
    /**
     * Load all configuration from database
     */
    private function loadConfig(): void {
        try {
            $rows = $this->fetchAll("SELECT key_name, value FROM config");
            foreach ($rows as $row) {
                $this->config[$row['key_name']] = $row['value'];
            }
        } catch (Exception $e) {
            error_log("Failed to load config from database: " . $e->getMessage());
        }
    }
    
    /**
     * Get configuration value
     */
    public function get(string $key, mixed $default = null): mixed {
        if ($key === 'ai_provider') {
            return $this->getCurrentProvider();
        }
        
        if ($key === 'ai_api_key') {
             $provider = $this->getCurrentProvider();
             return $this->getApiKey($provider);
        }
        
        if ($key === 'ai_model') {
             $provider = $this->getCurrentProvider();
             return $this->getModel($provider);
        }

        return $this->config[$key] ?? $default;
    }

    public function getCurrentProvider(): string {
        return $this->config['provider'] ?? $_ENV['AI_PROVIDER'] ?? 'ollama';
    }

    public function setProvider(string $provider): void {
        if (!in_array($provider, ['gemini', 'openrouter', 'ollama'])) {
            throw new InvalidArgumentException("Invalid provider: $provider");
        }
        $this->saveConfigValue('provider', $provider);
    }

    public function getApiKey(string $provider): ?string {
        if ($provider === 'ollama') {
            return 'local-ollama';
        }

        $sql = "SELECT encrypted_key, iv FROM api_keys WHERE provider = ?";
        $row = $this->fetchOne($sql, [$provider]);

        if ($row) {
            try {
                return $this->encryption->decrypt($row['encrypted_key'], $row['iv']);
            } catch (Exception $e) {
                error_log("Failed to decrypt API key for $provider: " . $e->getMessage());
            }
        }

        if (!empty($this->config[$provider . '_api_key'])) {
            return $this->config[$provider . '_api_key'];
        }
        $key = strtoupper($provider) . '_API_KEY';
        return $_ENV[$key] ?? null;
    }

    public function setApiKey(string $provider, string $key): void {
        try {
            $encrypted = $this->encryption->encrypt($key);
            $sql = "INSERT INTO api_keys (provider, encrypted_key, iv) VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE encrypted_key = ?, iv = ?";
            
            $this->executeQuery($sql, [
                $provider, 
                $encrypted['data'], 
                $encrypted['iv'],
                $encrypted['data'],
                $encrypted['iv']
            ]);
        } catch (Exception $e) {
            error_log("Failed to encrypt API key for $provider: " . $e->getMessage());
            throw $e;
        }
    }

    public function getModel(string $provider): string {
        if (!empty($this->config[$provider . '_model'])) {
            return $this->config[$provider . '_model'];
        }
        
        $defaults = [
            'gemini' => $_ENV['GEMINI_MODEL'] ?? 'gemini-2.5-flash',
            'openrouter' => $_ENV['OPENROUTER_MODEL'] ?? 'openai/gpt-4',
            'ollama' => $_ENV['OLLAMA_MODEL'] ?? 'Brain32:latest'
        ];
        return $defaults[$provider] ?? 'Brain32:latest';
    }

    public function setModel(string $provider, string $model): void {
        $this->saveConfigValue($provider . '_model', $model);
    }

    public function getOllamaThink(): bool {
        if (isset($this->config['ollama_think'])) {
            return filter_var($this->config['ollama_think'], FILTER_VALIDATE_BOOLEAN);
        }
        return false; // Default is think=false
    }

    public function setOllamaThink(bool $think): void {
        $this->saveConfigValue('ollama_think', $think ? 'true' : 'false');
    }

    public function getAll(): array {
        return [
            'provider' => $this->getCurrentProvider(),
            'model' => $this->getModel($this->getCurrentProvider()),
            'gemini_key_set' => !empty($this->getApiKey('gemini')),
            'openrouter_key_set' => !empty($this->getApiKey('openrouter')),
            'ollama_key_set' => true,
            'gemini_model' => $this->getModel('gemini'),
            'openrouter_model' => $this->getModel('openrouter'),
            'ollama_model' => $this->getModel('ollama'),
            'ollama_think' => $this->getOllamaThink()
        ];
    }

    public function getTotalTokens(): int {
        $result = $this->fetchOne("SELECT SUM(total_tokens) as total FROM token_usage");
        if (!$result) return 0;
        return (int)($result['total'] ?? 0);
    }

    /**
     * Save a configuration value to database
     */
    private function saveConfigValue(string $key, string $value): void {
        $sql = "INSERT INTO config (key_name, value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE value = ?";
        
        $this->executeQuery($sql, [$key, $value, $value]);
        $this->config[$key] = $value;
    }
}