<?php
declare(strict_types=1);

require_once '/var/www/self/src/config/env.php';

// Force Council backends for Phase 10 verification
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

echo "====================================================\n";
echo "   PHASE 10: FROM THE NOISE RECONCILIATION TEST     \n";
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

// 1. Agent & Dynamic Head Authority
$agentContext = new AgentContextService();
$activeSlug = $agentContext->getActiveAgentSlug();
check("Active Agent context is 'zeon7'", $activeSlug === 'zeon7');

$instructionService = new InstructionService();
$soul = $instructionService->getCurrentContent();
check("From the Noise editorial instructions sourced from Council SOUL", !empty($soul) && strlen($soul) > 100);

// 2. Knowledge & Memory Retrieval
$council = (new CouncilClient())->withAgent($activeSlug);
check("Council Client connected to Primary VPS", $council->isAvailable());

$knowledge = $council->searchCommons('editorial', 3);
check("Commons Knowledge search accessible for editorial pipeline", is_array($knowledge['results'] ?? null));

$memory = $council->searchMemory('news');
check("Sanctum Memory search accessible for editorial pipeline", is_array($memory['results'] ?? null));

// 3. Editorial Generation Turn Logging
$sessionId = 'ftn_test_' . bin2hex(random_bytes(6));
$userTurn = $council->appendMessage(
    sessionId:       $sessionId,
    role:            'user',
    content:         'Editorial Command: Generate weekly dispatch on AI sovereignty',
    metadata:        ['tone' => ['satire' => 30, 'hope' => 70], 'model' => 'qwen/qwen3-32b:free'],
    sourceInterface: 'from_the_noise',
    headUsed:        'first_truth'
);
check("From the Noise User Turn logged to Council", ($userTurn['success'] ?? false) === true);

$asstTurn = $council->appendMessage(
    sessionId:       $sessionId,
    role:            'assistant',
    content:         'Generated Dispatch: In the depths of the signal, sovereignty remains intact.',
    metadata:        ['model' => 'qwen/qwen3-32b:free', 'tokens' => 128],
    sourceInterface: 'from_the_noise',
    headUsed:        'first_truth',
    tokensOutput:    128
);
check("From the Noise Assistant Turn logged to Council", ($asstTurn['success'] ?? false) === true);

// 4. Validate Canonical Conversation Record
$history = $council->getConversation($sessionId);
$msgs = $history['messages'] ?? [];
check("Transcript retrieved from Council Sanctum (2 turns)", count($msgs) === 2);

if (count($msgs) === 2) {
    check("Turn 1 source_interface is 'from_the_noise'", $msgs[0]['source_interface'] === 'from_the_noise');
    check("Turn 2 source_interface is 'from_the_noise'", $msgs[1]['source_interface'] === 'from_the_noise');
    check("Turn 1 head_used is 'first_truth'", $msgs[0]['head_used'] === 'first_truth');
}

echo "\n----------------------------------------------------\n";
echo "Results: {$pass} / {$total} Tests Passed.\n";
if ($pass === $total) {
    echo ">>> PHASE 10 FROM THE NOISE RECONCILIATION VERIFIED 100%! <<<\n";
}
echo "----------------------------------------------------\n";
