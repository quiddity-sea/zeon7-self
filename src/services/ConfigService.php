<?php
/**
 * ConfigService - Manage API configuration settings
 */

require_once __DIR__ . '/../config/env.php';

class ConfigService {
    private string $configFile;
    private array $config = [];

    public function __construct() {
        $this->configFile = __DIR__ . '/../../config/settings.json';
        $this->loadConfig();
    }

    private function loadConfig(): void {
        if (file_exists($this->configFile)) {
            $json = file_get_contents($this->configFile);
            $this->config = json_decode($json, true) ?? [];
        }
    }

    private function saveConfig(): void {
        file_put_contents($this->configFile, json_encode($this->config, JSON_PRETTY_PRINT));
    }

    public function getCurrentProvider(): string {
        return $this->config['provider'] ?? $_ENV['AI_PROVIDER'] ?? 'gemini';
    }

    public function setProvider(string $provider): void {
        if (!in_array($provider, ['gemini', 'openrouter'])) {
            throw new InvalidArgumentException("Invalid provider: $provider");
        }
        $this->config['provider'] = $provider;
        $this->saveConfig();
    }

    public function getApiKey(string $provider): ?string {
        // Check local config first, then ENV
        if (!empty($this->config[$provider . '_api_key'])) {
            return $this->config[$provider . '_api_key'];
        }
        $key = strtoupper($provider) . '_API_KEY';
        return $_ENV[$key] ?? null;
    }

    public function setApiKey(string $provider, string $key): void {
        $this->config[$provider . '_api_key'] = $key;
        $this->saveConfig();
    }

    public function getModel(string $provider): string {
        if (!empty($this->config[$provider . '_model'])) {
            return $this->config[$provider . '_model'];
        }
        
        $defaults = [
            'gemini' => $_ENV['GEMINI_MODEL'] ?? 'gemini-pro',
            'openrouter' => $_ENV['OPENROUTER_MODEL'] ?? 'openai/gpt-4'
        ];
        return $defaults[$provider] ?? '';
    }

    public function setModel(string $provider, string $model): void {
        $this->config[$provider . '_model'] = $model;
        $this->saveConfig();
    }

    public function getAll(): array {
        return [
            'provider' => $this->getCurrentProvider(),
            'model' => $this->getModel($this->getCurrentProvider()),
            'gemini_key_set' => !empty($this->getApiKey('gemini')),
            'openrouter_key_set' => !empty($this->getApiKey('openrouter')),
        ];
    }
}
