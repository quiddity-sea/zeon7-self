<?php
/**
 * AiService - Wrapper for AI Providers
 * Handles provider selection and delegates calls.
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

        if (!$apiKey) {
            throw new Exception("API key not configured for provider: $provider");
        }

        $this->providerService = AIServiceFactory::create($provider, $apiKey, $model);
    }

    /**
     * Chat with the AI
     * @param array $messages List of messages [['role' => 'user', 'content' => '...'], ...]
     * @return string AI response
     */
    public function chat(array $messages): string {
        // Map 'chat' call to specific provider implementation
        // Assuming providers have a generateContent or similar method
        // We might need to adapt the interface here.
        
        // For now, let's assume providers implement a common interface or we adapt.
        // Looking at previous generate.php, it used generateContent($prompt).
        // Chat is different. Let's check GeminiService/OpenRouterService.
        
        if (method_exists($this->providerService, 'chat')) {
            return $this->providerService->chat($messages);
        }
        
        // Fallback for providers that only support simple generation (convert chat to prompt)
        $prompt = "";
        foreach ($messages as $msg) {
            $prompt .= ucfirst($msg['role']) . ": " . $msg['content'] . "\n\n";
        }
        $prompt .= "Assistant:";
        
        return $this->providerService->generateContent($prompt);
    }
}
