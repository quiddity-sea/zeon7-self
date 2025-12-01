<?php
/**
 * API: Delete Lore Entry
 * Endpoint: DELETE /api/lore/delete.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/LoreService.php';
require_once __DIR__ . '/../../../src/middleware/CsrfMiddleware.php';

class LoreDeleteController extends BaseController {
    private LoreService $loreService;
    
    public function __construct() {
        parent::__construct();
        $this->loreService = new LoreService();
        
        // Protect with CSRF
        CsrfMiddleware::handle();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->sendError('Method not allowed', 405);
        }
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->sendError('ID is required', 400);
        }
        
        try {
            $success = $this->loreService->delete((int)$id);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Lore entry deleted successfully'
                ]);
            } else {
                $this->sendError('Lore entry not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new LoreDeleteController();
$controller->handleRequest();
