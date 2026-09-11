<?php
/**
 * API: Upload Knowledge File to Council Commons (Quiddity Lore Sea)
 * Endpoint: POST /api/knowledge/upload.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/CouncilClient.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class UploadController extends BaseController {
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
        
        // Check file upload
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->sendError('No file uploaded or upload error', 400);
        }
        
        $file = $_FILES['file'];
        $filename = $file['name'];
        $tmpPath = $file['tmp_name'];
        
        // Validate extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, ['md', 'txt', 'pdf'], true)) {
            $this->sendError('Only .md, .txt, and .pdf files are allowed', 400);
        }
        
        try {
            $subfolder = !empty($_POST['subfolder']) ? trim((string)$_POST['subfolder']) : null;

            // Forward to Council API — the single source of truth
            $result = $this->councilClient->uploadToCommons(
                $tmpPath,
                $filename,
                $subfolder
            );
            
            $this->sendResponse([
                'success'      => true,
                'id'           => $result['file_id'] ?? 0,
                'filename'     => $result['relative_path'] ?? $filename,
                'chunks_count' => $result['chunk_count'] ?? 0,
                'status'       => $result['indexing_status'] ?? 'indexed',
                'message'      => 'File uploaded to Quiddity Lore Sea and ingested successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new UploadController();
$controller->handleRequest();
