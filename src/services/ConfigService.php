<?php
/**
 * ConfigService - Application & AI Provider Configuration Manager
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/EncryptionService.php';

class ConfigService extends BaseService {
    private EncryptionService $encryption;
    private array $config = [];

    public function __construct() {
        parent::__construct();
        $this->encryption = new EncryptionService();
        $this->loadConfig();
    }

    private function loadConfig(): void {
        $sql = "SELECT config_key, config_value FROM config";
        $rows = $this->fetchAll($sql);
        foreach ($rows as $row) {
            $this->config[$row['config_key']] = $row['config_value'];
        }
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
                return null;
            }
        }

        return null;
    }

    public function setApiKey(string $provider, string $apiKey): void {
        if (empty($apiKey)) {
            return;
        }

        $encryptedData = $this->encryption->encrypt($apiKey);

        $sql = "INSERT INTO api_keys (provider, encrypted_key, iv) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE encrypted_key = VALUES(encrypted_key), iv = VALUES(iv), updated_at = CURRENT_TIMESTAMP";

        $this->executeQuery($sql, [
            $provider,
            $encryptedData['encrypted'],
            $encryptedData['iv']
        ]);
    }

    public function getModel(string $provider): string {
        if (isset($this->config[$provider . '_model'])) {
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

    public function getOllamaHost(): string {
        if (!empty($this->config['ollama_host'])) {
            return $this->config['ollama_host'];
        }
        return $_ENV['OLLAMA_HOST'] ?? 'http://127.0.0.1:11434';
    }

    public function setOllamaHost(string $host): void {
        $this->saveConfigValue('ollama_host', rtrim(trim($host), '/'));
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
            'ollama_think' => $this->getOllamaThink(),
            'ollama_host' => $this->getOllamaHost()
        ];
    }

    public function getTotalTokens(): int {
        $result = $this->fetchOne("SELECT SUM(total_tokens) as total FROM token_usage");
        if (!$result) return 0;
        return (int)($result['total'] ?? 0);
    }

    private function saveConfigValue(string $key, string $value): void {
        $sql = "INSERT INTO config (config_key, config_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = CURRENT_TIMESTAMP";

        $this->executeQuery($sql, [$key, $value]);
        $this->config[$key] = $value;
    }
}
