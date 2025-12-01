<?php
/**
 * API: List Knowledge Files
 * Endpoint: GET /api/knowledge/list.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/KnowledgeService.php';

class ListController extends BaseController {
    private KnowledgeService $knowledgeService;
    
    public function __construct() {
        parent::__construct();
        $this->knowledgeService = new KnowledgeService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        try {
            $files = $this->knowledgeService->getAllFiles();
            
            $this->sendResponse([
                'success' => true,
                'count' => count($files),
                'files' => $files
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new ListController();
$controller->handleRequest();
