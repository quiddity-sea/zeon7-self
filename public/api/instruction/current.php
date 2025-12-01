<?php
/**
 * API: Get Current Instruction Version
 * Endpoint: GET /api/instruction/current.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/InstructionService.php';

class CurrentInstructionController extends BaseController {
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
            $current = $this->instructionService->getCurrentVersion();
            
            if ($current) {
                $this->sendResponse([
                    'success' => true,
                    'version' => (int)$current['version'],
                    'content' => $current['content'],
                    'created_at' => $current['created_at'],
                    'created_by' => $current['created_by']
                ]);
            } else {
                $this->sendResponse([
                    'success' => false,
                    'message' => 'No instructions found',
                    'version' => 0
                ]);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new CurrentInstructionController();
$controller->handleRequest();
