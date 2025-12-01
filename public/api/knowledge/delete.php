<?php
/**
 * API: Delete Knowledge File
 * Endpoint: DELETE /api/knowledge/delete.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/KnowledgeService.php';
require_once __DIR__ . '/../../../src/middleware/CsrfMiddleware.php';

class DeleteController extends BaseController {
    private KnowledgeService $knowledgeService;
    
    public function __construct() {
        parent::__construct();
        $this->knowledgeService = new KnowledgeService();
        
        // Protect with CSRF
        CsrfMiddleware::handle();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendError('Method not allowed', 405);
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->sendError('File ID is required', 400);
        }
        
        try {
            $success = $this->knowledgeService->deleteFile((int)$id);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            } else {
                $this->sendError('File not found or could not be deleted', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new DeleteController();
$controller->handleRequest();
