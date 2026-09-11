<?php
/**
 * API: Reingest Knowledge File in Council Commons
 * Endpoint: POST /api/knowledge/reingest.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/CouncilClient.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class ReingestController extends BaseController {
    private CouncilClient $councilClient;
    
    public function __construct() {
        parent::__construct();
        $this->councilClient = new CouncilClient();
        
        // Protect with CSRF
        CsrfMiddleware::handle();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id = $body['file_id'] ?? $body['id'] ?? null;
        
        if (!$id) {
            $this->sendError('File ID is required', 400);
        }
        
        try {
            $res = $this->councilClient->reingestFiles([(int)$id]);
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Re-ingestion triggered successfully',
                'result'  => $res
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new ReingestController();
$controller->handleRequest();
