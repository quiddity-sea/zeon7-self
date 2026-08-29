<?php
/**
 * AiService - Unified AI Orchestration Service
 * Handles provider selection (Gemini, Ollama Brain32, OpenRouter) and delegates calls.
 */

require_once __DIR__ . '/ConfigService.php';
require_once __DIR__ . '/AIServiceFactory.php';

class AiService {
    private $providerService;
    private ConfigService $configService;

    public function __construct() {
        $this->configService = new ConfigService();
        $this->initializeProvider();
    }

    private function initializeProvider() {
        $provider = $this->configService->getCurrentProvider();
        $apiKey = $this->configService->getApiKey($provider);
        $model = $this->configService->getModel($provider);

        if ($provider !== 'ollama' && empty($apiKey)) {
            throw new Exception("API key not configured for provider: $provider");
        }

        $this->providerService = AIServiceFactory::create($provider, $apiKey ?? '', $model);
    }

    /**
     * Chat with the active AI Provider
     * @param array $messages List of messages [['role' => 'user'|'system'|'assistant', 'content' => '...'], ...]
     * @return string AI response text
     */
    public function chat(array $messages): string {
        if (empty($messages)) {
            return '';
        }

        $lastMsg = end($messages);
        $userPrompt = $lastMsg['content'] ?? '';
        $history = array_slice($messages, 0, count($messages) - 1);

        $res = $this->providerService->chat($userPrompt, $history);
        if (is_array($res)) {
            return $res['reply'] ?? '';
        }
        return (string)$res;
    }
    
    /**
     * Scan News with Grounding / Local LLM
     */
    public function scanNews(string $topic, string $angle): string {
        if (method_exists($this->providerService, 'scanNews')) {
            return $this->providerService->scanNews($topic, $angle);
        }
        return '[]';
    }
}