<?php
require_once __DIR__ . '/../../src/config/env.php';
require_once __DIR__ . '/../../src/services/CouncilClient.php';
require_once __DIR__ . '/../../src/services/AgentContextService.php';

header('Content-Type: application/json');

try {
    $council = new CouncilClient();
    $agentCtx = new AgentContextService();
    $available = $council->isAvailable();

    echo json_encode([
        'success'      => true,
        'council_url'  => $_ENV['COUNCIL_API_URL'] ?? 'http://127.0.0.1:8080',
        'agent_id'     => $agentCtx->getAgentId(),
        'display_name' => $agentCtx->getDisplayName(),
        'available'    => $available,
        'backends'     => [
            'memory'       => $_ENV['MEMORY_BACKEND'] ?? 'local',
            'knowledge'    => $_ENV['KNOWLEDGE_BACKEND'] ?? 'local',
            'conversation' => $_ENV['CONVERSATION_BACKEND'] ?? 'local',
            'soul'         => $_ENV['SOUL_BACKEND'] ?? 'local',
        ],
    ], JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
    ]);
}
