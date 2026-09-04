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
        $sql = "SELECT key_name, value FROM config";
        $rows = $this->fetchAll($sql);
        foreach ($rows as $row) {
            $this->config[$row['key_name']] = $row['value'];
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
            $encryptedData['data'],
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

    public function getCurrentModel(): string {
        $provider = $this->getCurrentProvider();
        return $this->getModel($provider);
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

    public function getThinkMode(): bool {
        return $this->getOllamaThink();
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

    public function getPublicChatAgent(): string {
        return $this->config['public_chat_agent'] ?? 'zeon7';
    }

    public function setPublicChatAgent(string $agentSlug): void {
        $this->saveConfigValue('public_chat_agent', strtolower(trim($agentSlug)));
    }

    public function getAuthenticatedDefaultAgent(): string {
        return $this->config['authenticated_default_agent'] ?? 'zeon7';
    }

    public function setAuthenticatedDefaultAgent(string $agentSlug): void {
        $this->saveConfigValue('authenticated_default_agent', strtolower(trim($agentSlug)));
    }

    public function getAgentProvider(string $agentSlug): string {
        $slug = strtolower(trim($agentSlug));
        $val = $this->config["agent_{$slug}_provider"] ?? null;
        return !empty($val) ? $val : $this->getCurrentProvider();
    }

    public function getAgentModel(string $agentSlug): string {
        $slug = strtolower(trim($agentSlug));
        $val = $this->config["agent_{$slug}_model"] ?? null;
        if (!empty($val)) return $val;
        $provider = $this->getAgentProvider($slug);
        return $this->getModel($provider);
    }

    public function getAgentThink(string $agentSlug): bool {
        $slug = strtolower(trim($agentSlug));
        $val = $this->config["agent_{$slug}_think"] ?? null;
        return ($val !== null) ? ((string)$val === '1' || $val === true || $val === 'true') : $this->getOllamaThink();
    }

    public function setAgentEngine(string $agentSlug, string $provider, string $model, bool $think = false): void {
        $slug = strtolower(trim($agentSlug));
        $this->saveConfigValue("agent_{$slug}_provider", $provider);
        $this->saveConfigValue("agent_{$slug}_model", $model);
        $this->saveConfigValue("agent_{$slug}_think", $think ? '1' : '0');
    }

    public function getAll(): array {
        $all = [
            'provider' => $this->getCurrentProvider(),
            'model' => $this->getCurrentModel(),
            'gemini_key_set' => !empty($this->getApiKey('gemini')),
            'openrouter_key_set' => !empty($this->getApiKey('openrouter')),
            'ollama_key_set' => true,
            'gemini_model' => $this->getModel('gemini'),
            'openrouter_model' => $this->getModel('openrouter'),
            'ollama_model' => $this->getModel('ollama'),
            'ollama_think' => $this->getOllamaThink(),
            'ollama_host' => $this->getOllamaHost(),
            'public_chat_agent' => $this->getPublicChatAgent(),
            'authenticated_default_agent' => $this->getAuthenticatedDefaultAgent(),
            'agent_engines' => []
        ];

        $knownAgents = ['zeon7', 'leon', 'gemma', 'otec', 'wolf'];
        foreach ($knownAgents as $slug) {
            $all['agent_engines'][$slug] = [
                'provider' => $this->getAgentProvider($slug),
                'model'    => $this->getAgentModel($slug),
                'think'    => $this->getAgentThink($slug)
            ];
        }

        return $all;
    }

    public function getTotalTokens(): int {
        $result = $this->fetchOne("SELECT SUM(total_tokens) as total FROM token_usage");
        if (!$result) return 0;
        return (int)($result['total'] ?? 0);
    }

    private function saveConfigValue(string $key, string $value): void {
        $sql = "INSERT INTO config (key_name, value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP";

        $this->executeQuery($sql, [$key, $value]);
        $this->config[$key] = $value;
    }
}
