<?php
/**
 * API: Get Current Instruction Version and Agent Components
 * Endpoint: GET /api/instruction/current.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/InstructionService.php';
require_once __DIR__ . '/../../src/services/AgentContextService.php';

class CurrentInstructionController extends BaseController {
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
            $current = $this->instructionService->getCurrentVersion($agentId);
            $components = $this->instructionService->getAgentComponents($agentId);
            
            $this->sendResponse([
                'success'      => true,
                'agent_id'     => $agentId,
                'agent_name'   => $this->agentCtx->getDisplayName(),
                'version'      => $current['version'] ?? 0,
                'component'    => $current['component'] ?? 'custom',
                'content'      => $current['content'] ?? '',
                'created_at'   => $current['created_at'] ?? null,
                'created_by'   => $current['created_by'] ?? 'system',
                'components'   => $components
            ]);
            
        } catch (\Throwable $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new CurrentInstructionController();
$controller->handleRequest();
