<?php
/**
 * API: Get Configuration
 * Endpoint: GET /api/config/get.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/ConfigService.php';

class ConfigGetController extends BaseController {
    private ConfigService $configService;
    
    public function __construct() {
        parent::__construct();
        $this->configService = new ConfigService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        try {
            $config = $this->configService->getAll();

            $councilInfo = ['available' => false];
            try {
                require_once __DIR__ . '/../../src/services/CouncilClient.php';
                $client = new CouncilClient();
                if ($client->isAvailable()) {
                    $models = $client->getModels();
                    $councilInfo = [
                        'available' => true,
                        'profiles'  => $models['profiles'] ?? [],
                        'endpoint'  => $models['local_endpoint'] ?? ''
                    ];
                }
            } catch (\Throwable $e) {}

            $this->sendResponse([
                'success'      => true,
                'config'       => $config,
                'council'      => $councilInfo,
                'total_tokens' => $this->configService->getTotalTokens()
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new ConfigGetController();
$controller->handleRequest();
