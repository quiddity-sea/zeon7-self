<?php
/**
 * API: Create New Instruction Version for Agent
 * Endpoint: POST /api/instruction/create.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/InstructionService.php';
require_once __DIR__ . '/../../src/services/AgentContextService.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class CreateInstructionController extends BaseController {
    private InstructionService $instructionService;
    private AgentContextService $agentCtx;
    
    public function __construct() {
        parent::__construct();
        $this->agentCtx = new AgentContextService();
        $this->instructionService = new InstructionService($this->agentCtx);
    }
    
    public function handleRequest(): void {
        $this->requireMethod('POST');
        
        try {
            $body = $this->getJsonBody();
            $content = trim($body['content'] ?? '');
            $agentId = $body['agent_id'] ?? $this->agentCtx->getAgentId();
            $component = $body['component'] ?? 'custom';
            $type = $body['type'] ?? 'core';
            
            if (empty($content)) {
                $this->sendError('Instruction content is required', 400);
                return;
            }
            
            $versionId = $this->instructionService->createVersion($content, $agentId, $type, $component);
            
            $this->sendResponse([
                'success'    => true,
                'version_id' => $versionId,
                'agent_id'   => $agentId,
                'component'  => $component,
                'message'    => 'Instruction version saved and activated'
            ]);
            
        } catch (\Throwable $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new CreateInstructionController();
$controller->handleRequest();
