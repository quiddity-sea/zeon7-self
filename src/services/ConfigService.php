<?php
/**
 * ConfigService - Manage API configuration settings via Database
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../config/env.php';


require_once __DIR__ . '/EncryptionService.php';

class ConfigService extends BaseService {
    private array $config = [];
    private EncryptionService $encryption;

    public function __construct() {
        parent::__construct();
        $this->encryption = new EncryptionService();
        $this->loadConfig();
    }

    private function loadConfig(): void {
        $rows = $this->fetchAll("SELECT key_name, value FROM config");
        $this->config = [];
        foreach ($rows as $row) {
            $this->config[$row['key_name']] = $row['value'];
        }
    }

    private function saveConfigValue(string $key, string $value): void {
        $sql = "INSERT INTO config (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?";
        $this->executeQuery($sql, [$key, $value, $value]);
        $this->config[$key] = $value;
    }

    /**
     * Generic get method for backward compatibility and dynamic access
     */
    public function get(string $key, $default = null) {
        // Map legacy/generic keys to specific methods
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
        return $this->config['provider'] ?? $_ENV['AI_PROVIDER'] ?? 'gemini';
    }

    public function setProvider(string $provider): void {
        if (!in_array($provider, ['gemini', 'openrouter'])) {
            throw new InvalidArgumentException("Invalid provider: $provider");
        }
        $this->saveConfigValue('provider', $provider);
    }

    public function getApiKey(string $provider): ?string {
        // Check api_keys table first (Encrypted)
        $sql = "SELECT encrypted_key, iv FROM api_keys WHERE provider = ?";
        $row = $this->fetchOne($sql, [$provider]);

        if ($row) {
            try {
                return $this->encryption->decrypt($row['encrypted_key'], $row['iv']);
            } catch (Exception $e) {
                error_log("Failed to decrypt API key for $provider: " . $e->getMessage());
                // Fallthrough to Env/Config table if decrypt fails? Or return null?
                // Let's fallthrough to ENV just in case of migration overlap.
            }
        }

        // Check Legacy DB config
        if (!empty($this->config[$provider . '_api_key'])) {
            return $this->config[$provider . '_api_key'];
        }
        // Fallback to ENV
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
            'gemini' => $_ENV['GEMINI_MODEL'] ?? 'gemini-pro-latest',
            'openrouter' => $_ENV['OPENROUTER_MODEL'] ?? 'openai/gpt-4'
        ];
        return $defaults[$provider] ?? '';
    }

    public function setModel(string $provider, string $model): void {
        $this->saveConfigValue($provider . '_model', $model);
    }

    public function getAll(): array {
        return [
            'provider' => $this->getCurrentProvider(),
            'model' => $this->getModel($this->getCurrentProvider()),
            'gemini_key_set' => !empty($this->getApiKey('gemini')),
            'openrouter_key_set' => !empty($this->getApiKey('openrouter')),
        ];
    }
    public function getTotalTokens(): int {
        $result = $this->fetchOne("SELECT SUM(total_tokens) as total FROM token_usage");
        if (!$result) return 0;
        return (int)($result['total'] ?? 0);
    }
}
