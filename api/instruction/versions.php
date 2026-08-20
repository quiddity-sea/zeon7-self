<?php
/**
 * API: Get Instruction Version History for Agent
 * Endpoint: GET /api/instruction/versions.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/InstructionService.php';
require_once __DIR__ . '/../../src/services/AgentContextService.php';

class VersionHistoryController extends BaseController {
    private InstructionService $instructionService;
    private AgentContextService $agentCtx;
    
    public function __construct() {
        parent::__construct();
        $this->agentCtx = new AgentContextService();
        $this->instructionService = new InstructionService($this->agentCtx);
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
            return;
        }
        
        try {
            $agentId = $_GET['agent'] ?? $this->agentCtx->getAgentId();
            $versions = $this->instructionService->getVersions($agentId);
            
            $this->sendResponse([
                'success'  => true,
                'agent_id' => $agentId,
                'versions' => $versions
            ]);
            
        } catch (\Throwable $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new VersionHistoryController();
$controller->handleRequest();
