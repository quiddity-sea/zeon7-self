<?php
require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/ConfigService.php';
require_once __DIR__ . '/../../src/services/AIServiceFactory.php';

class TestConnectionController extends BaseController {
    private ConfigService $configService;

    public function __construct() {
        parent::__construct();
        $this->configService = new ConfigService();
    }

    public function handleRequest() {
        $this->requireMethod('GET');
        
        try {
            $provider = $this->configService->getCurrentProvider();
            $key = $this->configService->getApiKey($provider) ?? '';
            $model = $this->configService->getModel($provider);
            
            if ($provider !== 'ollama' && empty($key)) {
                $this->jsonResponse(['success' => false, 'error' => "No API key found for $provider"]);
            }
            
            $aiService = AIServiceFactory::create($provider, $key, $model);
            
            $prompt = "Ping. Reply in one short sentence starting with 'Pong'.";
            $response = $aiService->chat($prompt);
            
            if (!empty($response['reply'])) {
                $this->jsonResponse([
                    'success' => true, 
                    'message' => 'Connection successful',
                    'provider' => $provider,
                    'model' => $model,
                    'prompt' => $prompt,
                    'reply' => $response['reply'],
                    'usage' => $response['usage'] ?? null
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'Empty response from AI engine']);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

$controller = new TestConnectionController();
$controller->handleRequest();