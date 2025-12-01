<?php
/**
 * API: Upload Knowledge File
 * Endpoint: POST /api/knowledge/upload.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/KnowledgeService.php';
require_once __DIR__ . '/../../../src/middleware/CsrfMiddleware.php';

class UploadController extends BaseController {
    private KnowledgeService $knowledgeService;
    
    public function __construct() {
        parent::__construct();
        $this->knowledgeService = new KnowledgeService();
        
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
        $size = $file['size'];
        
        // Validate extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext !== 'md' && $ext !== 'txt') {
            $this->sendError('Only .md and .txt files are allowed', 400);
        }
        
        // Read content
        $content = file_get_contents($tmpPath);
        if ($content === false) {
            $this->sendError('Failed to read file content', 500);
        }
        
        // Calculate hash
        $hash = hash('sha256', $content);
        
        try {
            // Check if exists
            if ($this->knowledgeService->fileExists($filename)) {
                $this->sendError("File '$filename' already exists", 409);
            }
            
            // Get Public Flag
            $isPublic = isset($_POST['is_public']) && $_POST['is_public'] === '1';

            // Upload metadata
            $docId = $this->knowledgeService->uploadFile($filename, $content, $hash, $size, $isPublic);
            
            // Chunk content (simple splitting by headers for now)
            $chunks = $this->parseChunks($content);
            $this->knowledgeService->chunkFile($docId, $chunks);
            
            $this->sendResponse([
                'success' => true,
                'id' => $docId,
                'filename' => $filename,
                'chunks_count' => count($chunks),
                'message' => 'File uploaded and processed successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    /**
     * Parse markdown content into chunks based on headers
     */
    private function parseChunks(string $content): array {
        $lines = explode("\n", $content);
        $chunks = [];
        $currentHeading = 'Introduction';
        $currentContent = '';
        
        foreach ($lines as $line) {
            // Check for headers (H1-H3)
            if (preg_match('/^#{1,3}\s+(.+)$/', $line, $matches)) {
                // Save previous chunk if not empty
                if (!empty(trim($currentContent))) {
                    $chunks[] = [
                        'heading' => $currentHeading,
                        'content' => trim($currentContent)
                    ];
                }
                
                $currentHeading = $matches[1];
                $currentContent = $line . "\n";
            } else {
                $currentContent .= $line . "\n";
            }
        }
        
        // Save last chunk
        if (!empty(trim($currentContent))) {
            $chunks[] = [
                'heading' => $currentHeading,
                'content' => trim($currentContent)
            ];
        }
        
        return $chunks;
    }
}

$controller = new UploadController();
$controller->handleRequest();
