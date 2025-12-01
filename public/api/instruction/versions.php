<?php
/**
 * API: List Instruction Versions
 * Endpoint: GET /api/instruction/versions.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/InstructionService.php';

class InstructionVersionsController extends BaseController {
    private InstructionService $instructionService;
    
    public function __construct() {
        parent::__construct();
        $this->instructionService = new InstructionService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        try {
            $versions = $this->instructionService->getAllVersions();
            
            $this->sendResponse([
                'success' => true,
                'count' => count($versions),
                'versions' => $versions
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new InstructionVersionsController();
$controller->handleRequest();
