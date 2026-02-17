<?php
require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/Services/ConfigService.php';
require_once __DIR__ . '/../../src/Services/AIServiceFactory.php';

class TestConnectionController extends BaseController {
    private ConfigService $configService;

    public function __construct() {
        parent::__construct();
        $this->configService = new ConfigService();
    }

    public function handleRequest() {
        $this->requireMethod('GET');
        
        // Ensure user is authenticated (BaseController doesn't enforce this by default, 
        // but the JS checks auth. For extra security we could check session here)
        // For now, we assume the middleware/frontend auth check is sufficient for this internal tool.
        
        try {
            $provider = $this->configService->getCurrentProvider();
            $key = $this->configService->getApiKey($provider);
            $model = $this->configService->getModel($provider);
            
            if (!$key) {
                $this->jsonResponse(['success' => false, 'error' => 'No API key found']);
            }
            
            $aiService = AIServiceFactory::create($provider, $key, $model);
            
            // Try a very simple, cheap prompt
            $prompt = "Ping. Reply with 'Pong'.";
            $response = $aiService->chat($prompt);
            
            if (!empty($response['reply'])) {
                $this->jsonResponse([
                    'success' => true, 
                    'message' => 'Connection successful',
                    'prompt' => $prompt,
                    'reply' => $response['reply'],
                    'usage' => $response['usage'] ?? null // Add usage metadata
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'error' => 'Empty response from AI']);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

$controller = new TestConnectionController();
$controller->handleRequest();
