<?php
/**
 * API: Create New Instruction Version
 * Endpoint: POST /api/instruction/create.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/InstructionService.php';
require_once __DIR__ . '/../../../src/middleware/CsrfMiddleware.php';

class CreateInstructionController extends BaseController {
    private InstructionService $instructionService;
    
    public function __construct() {
        parent::__construct();
        $this->instructionService = new InstructionService();
        
        // Protect with CSRF
        CsrfMiddleware::handle();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getJsonBody();
        
        if (empty($data['content'])) {
            $this->sendError('Content is required', 400);
        }
        
        $content = $data['content'];
        $createdBy = $data['created_by'] ?? 'admin';
        
        try {
            $version = $this->instructionService->createVersion($content, $createdBy);
            
            $this->sendResponse([
                'success' => true,
                'version' => $version,
                'message' => 'New instruction version created successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new CreateInstructionController();
$controller->handleRequest();
