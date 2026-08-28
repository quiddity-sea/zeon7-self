<?php
declare(strict_types=1);

require_once '/var/www/self/src/config/env.php';

// Force Council backends for Phase 8 verification
$_ENV['COUNCIL_API_URL'] = 'http://100.126.174.30:8080';
$_ENV['COUNCIL_API_KEY'] = '9a66eb987eb2a1949b0381556fc8c487808ae7f134bdbe2923ce45f1c6197073';
$_ENV['COUNCIL_AGENT_ID'] = 'zeon7';
$_ENV['MEMORY_BACKEND'] = 'council';
$_ENV['KNOWLEDGE_BACKEND'] = 'council';
$_ENV['CONVERSATION_BACKEND'] = 'council';
$_ENV['SOUL_BACKEND'] = 'council';

require_once '/var/www/self/src/services/CouncilClient.php';
require_once '/var/www/self/src/services/AgentContextService.php';
require_once '/var/www/self/src/services/InstructionService.php';
require_once '/var/www/self/src/services/LoreService.php';
require_once '/var/www/self/src/services/KnowledgeService.php';
require_once '/var/www/self/src/services/ConfigService.php';

echo "====================================================\n";
echo "   PHASE 8 TEST SUITE: CANONICAL CHAT PIPELINE     \n";
echo "====================================================\n\n";

$pass = 0;
$total = 0;

function check(string $name, bool $cond, string $msg = '') {
    global $pass, $total;
    $total++;
    if ($cond) {
        $pass++;
        echo "  [PASS] {$name}\n";
    } else {
        echo "  [FAIL] {$name} ({$msg})\n";
    }
}

// 1. Agent Context Resolution
$agentContext = new AgentContextService();
$activeSlug = $agentContext->getActiveAgentSlug();
check("Active Agent Context matches 'zeon7'", $activeSlug === 'zeon7');

$roster = $agentContext->getAvailableAgents();
check("Council Dynamic Agent Roster contains >= 5 agents", count($roster) >= 5);

// 2. Canonical Dynamic SOUL Assembly for Active Agent
$instructionService = new InstructionService();
$systemPrompt = $instructionService->getCurrentContent();
check("InstructionService resolves dynamic SOUL from Council", !empty($systemPrompt) && strlen($systemPrompt) > 100);

// 3. Sanctum Memory & Commons Knowledge retrieval
$council = (new CouncilClient())->withAgent($activeSlug);
check("Council API Client connected over Tailscale", $council->isAvailable());

$memSearch = $council->searchMemory('sovereignty');
check("Sanctum Memory retrieval returns structured results", is_array($memSearch['results'] ?? null));

$commonsSearch = $council->searchCommons('foreverbox', 3);
check("Commons Knowledge hybrid retrieval returns results", is_array($commonsSearch['results'] ?? null));

// 4. Conversation Turn Logging in Council
$testSid = 'test_phase8_' . bin2hex(random_bytes(8));
$userTurn = $council->appendMessage(
    sessionId:  $testSid,
    role:       'user',
    content:    'Hello Zeon7, this is Phase 8 verification.',
    metadata:   ['model' => 'layer_1_intuitive_reflex', 'provider' => 'council'],
    ipAddress:  '127.0.0.1',
    operatorId: 1
);
check("Append User Message Turn to Council Sanctum", ($userTurn['success'] ?? false) === true);

$asstTurn = $council->appendMessage(
    sessionId:  $testSid,
    role:       'assistant',
    content:    'Greetings. The canonical chat pipeline is verified.',
    metadata:   ['model' => 'layer_1_intuitive_reflex', 'provider' => 'council', 'tokens' => 42],
    ipAddress:  '127.0.0.1',
    operatorId: 1
);
check("Append Assistant Message Turn to Council Sanctum", ($asstTurn['success'] ?? false) === true);

// 5. Verify Conversation History Retrieval
$history = $council->getConversation($testSid);
$messages = $history['messages'] ?? [];
check("Retrieve Logged Conversation History (2 turns)", count($messages) === 2);
if (count($messages) === 2) {
    check("First turn is 'user' role", $messages[0]['role'] === 'user');
    check("Second turn is 'assistant' role", $messages[1]['role'] === 'assistant');
    check("Operator ID preserved on turn", (int)$messages[0]['operator_id'] === 1);
}

// 6. Cognitive Router Model Profiles
$models = $council->getModels();
check("Council Cognitive Router profiles returned", !empty($models['profiles']));

echo "\n----------------------------------------------------\n";
echo "Results: {$pass} / {$total} Tests Passed.\n";
if ($pass === $total) {
    echo ">>> PHASE 8 CHAT PIPELINE VERIFIED 100%! <<<\n";
}
echo "----------------------------------------------------\n";
