<?php
/**
 * API: Update Configuration
 * Endpoint: POST /api/config/update.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/ConfigService.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class ConfigUpdateController extends BaseController {
    private ConfigService $configService;
    
    public function __construct() {
        parent::__construct();
        $this->configService = new ConfigService();
        
        CsrfMiddleware::handle();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getJsonBody();
        
        try {
            if (isset($data['provider'])) {
                $this->configService->setProvider($data['provider']);
            }
            
            if (isset($data['model'])) {
                $this->configService->setModel($data['provider'] ?? 'gemini', $data['model']);
            }
            
            if (!empty($data['api_key'])) {
                $this->configService->setApiKey($data['provider'] ?? 'gemini', $data['api_key']);
            }
            
            if (isset($data['ollama_think'])) {
                $this->configService->setOllamaThink(filter_var($data['ollama_think'], FILTER_VALIDATE_BOOLEAN));
            }

            if (isset($data['ollama_host'])) {
                $this->configService->setOllamaHost($data['ollama_host']);
            }
            
            if (isset($data['public_chat_agent'])) {
                $this->configService->setPublicChatAgent($data['public_chat_agent']);
            }

            if (isset($data['authenticated_default_agent'])) {
                $this->configService->setAuthenticatedDefaultAgent($data['authenticated_default_agent']);
            }

            if (isset($data['agent_engines']) && is_array($data['agent_engines'])) {
                foreach ($data['agent_engines'] as $slug => $eng) {
                    $prov = $eng['provider'] ?? 'gemini';
                    $mod = $eng['model'] ?? 'gemini-2.5-flash';
                    $thk = !empty($eng['think']);
                    $this->configService->setAgentEngine($slug, $prov, $mod, $thk);
                }
            } elseif (isset($data['agents']) && is_array($data['agents'])) {
                foreach ($data['agents'] as $slug => $eng) {
                    $prov = $eng['provider'] ?? 'gemini';
                    $mod = $eng['model'] ?? 'gemini-2.5-flash';
                    $thk = !empty($eng['think']);
                    $this->configService->setAgentEngine($slug, $prov, $mod, $thk);
                }
            }
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Configuration updated successfully',
                'config' => $this->configService->getAll()
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new ConfigUpdateController();
$controller->handleRequest();
