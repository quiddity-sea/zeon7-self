<?php
/**
 * API: Upsert Lore Entry
 * Endpoint: POST /api/lore/upsert.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/LoreService.php';
require_once __DIR__ . '/../../../src/middleware/CsrfMiddleware.php';

class LoreUpsertController extends BaseController {
    private LoreService $loreService;
    
    public function __construct() {
        parent::__construct();
        $this->loreService = new LoreService();
        
        // Protect with CSRF
        CsrfMiddleware::handle();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getJsonBody();
        
        if (empty($data['type']) || empty($data['content'])) {
            $this->sendError('Type and Content are required', 400);
        }

        $id = $data['id'] ?? null;
        $type = $data['type'];
        $content = $data['content'];
        $tags = $data['tags'] ?? [];
        $isPublic = $data['is_public'] ?? false;
        
        try {
            if ($id) {
                $this->loreService->update($id, $type, $content, $tags, $isPublic);
                $message = 'Lore entry updated successfully';
            } else {
                $id = $this->loreService->create($type, $content, $tags, $isPublic);
                $message = 'Lore entry created successfully';
            }
            
            $this->sendResponse([
                'success' => true,
                'id' => $id,
                'message' => $message
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new LoreUpsertController();
$controller->handleRequest();
