<?php
/**
 * AIServiceFactory - Factory for creating AI service instances
 */

require_once __DIR__ . '/GeminiService.php';
require_once __DIR__ . '/OpenRouterService.php';

class AIServiceFactory {
    
    /**
     * Create AI service instance based on provider
     */
    public static function create(string $provider, string $apiKey, string $model = ''): GeminiService|OpenRouterService {
        switch (strtolower($provider)) {
            case 'gemini':
                $defaultModel = $model ?: 'gemini-pro-latest';
                return new GeminiService($apiKey, $defaultModel);
                
            case 'openrouter':
                $defaultModel = $model ?: 'openai/gpt-4';
                return new OpenRouterService($apiKey, $defaultModel);
                
            default:
                throw new InvalidArgumentException("Unsupported AI provider: $provider. Use 'gemini' or 'openrouter'.");
        }
    }
}
