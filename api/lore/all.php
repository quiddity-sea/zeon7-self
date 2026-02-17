<?php
/**
 * API: Get All Lore
 * Endpoint: GET /api/lore/all.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/LoreService.php';

class LoreListController extends BaseController {
    private LoreService $loreService;
    
    public function __construct() {
        parent::__construct();
        $this->loreService = new LoreService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        try {
            $lore = $this->loreService->getAll();
            
            $this->sendResponse([
                'success' => true,
                'count' => count($lore),
                'lore' => $lore
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new LoreListController();
$controller->handleRequest();
