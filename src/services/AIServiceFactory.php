<?php
/**
 * AIServiceFactory - Factory for creating AI service instances
 */

require_once __DIR__ . '/GeminiService.php';
require_once __DIR__ . '/OpenRouterService.php';
require_once __DIR__ . '/OllamaService.php';
require_once __DIR__ . '/ConfigService.php';

class AIServiceFactory {
    
    /**
     * Create AI service instance based on provider
     */
    public static function create(string $provider, string $apiKey = '', string $model = '', ?bool $think = null): GeminiService|OpenRouterService|OllamaService {
        switch (strtolower($provider)) {
            case 'gemini':
                $defaultModel = $model ?: 'gemini-2.5-flash';
                return new GeminiService($apiKey, $defaultModel);
                
            case 'openrouter':
                $defaultModel = $model ?: 'openai/gpt-4';
                return new OpenRouterService($apiKey, $defaultModel);
                
            case 'ollama':
                $configService = new ConfigService();
                $defaultModel = $model ?: ($configService->getModel('ollama') ?? 'Brain32:latest');
                $host = $configService->getOllamaHost();
                $thinkMode = $think !== null ? $think : $configService->getOllamaThink();
                return new OllamaService($defaultModel, $host, $thinkMode);
                
            default:
                throw new InvalidArgumentException("Unsupported AI provider: $provider. Use 'gemini', 'openrouter', or 'ollama'.");
        }
    }
}
