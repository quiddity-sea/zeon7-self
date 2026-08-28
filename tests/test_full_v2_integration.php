<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config/env.php';
$_ENV['COUNCIL_API_URL'] = 'http://127.0.0.1:8080';
$_ENV['COUNCIL_API_KEY'] = 'dev-key-change-in-production';
$_ENV['COUNCIL_AGENT_ID'] = 'zeon7';
$_ENV['MEMORY_BACKEND'] = 'council';
$_ENV['KNOWLEDGE_BACKEND'] = 'council';
$_ENV['CONVERSATION_BACKEND'] = 'council';
$_ENV['SOUL_BACKEND'] = 'council';

require_once __DIR__ . '/../src/services/CouncilClient.php';
require_once __DIR__ . '/../src/services/InstructionService.php';
require_once __DIR__ . '/../src/services/AgentContextService.php';

echo "====================================================\n";
echo "    HERMES V2 INTEGRATION SUITE — FULL PASS        \n";
echo "====================================================\n\n";

$client = new CouncilClient();
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

// 1. Connectivity
check("Council Core REST API Connectivity", $client->isAvailable());

// 2. SOUL / Identity
$agents = $client->getAgents();
check("Agent Roster (5 Canonical Agents)", ($agents['count'] ?? 0) >= 5);

$heads = $client->getHeads('zeon7');
check("Zeon7 Dynamic SOUL Components", ($heads['count'] ?? 0) > 0);

// 3. Sanctum Memory
$memKey = 'test_integration_fact';
$upsertMem = $client->upsertMemory('core', $memKey, [
    'content'    => 'Zeon7 is the primary curator of the ForeverBox ecosystem.',
    'importance' => 85,
    'source'     => 'integration_test'
]);
check("Sanctum Memory Upsert", ($upsertMem['success'] ?? false) === true);

$searchMem = $client->searchMemory('primary curator');
check("Sanctum Memory Search", is_array($searchMem['results'] ?? null));

$delMem = $client->deleteMemory('core', $memKey);
check("Sanctum Memory Delete", ($delMem['success'] ?? false) === true);

// 4. Commons Knowledge
$files = $client->listFiles(['limit' => 5]);
check("Quiddity Commons File Listing", ($files['success'] ?? false) === true);

$searchKnow = $client->searchKnowledge('Foreverbox');
check("Quiddity Commons Knowledge Search", ($searchKnow['success'] ?? false) === true);

// 5. Sanctum Conversations
$conv = $client->createConversation();
$sid = $conv['session_id'] ?? '';
check("Sanctum Conversation Creation", !empty($sid));

if ($sid) {
    $appendUser = $client->appendMessage($sid, 'user', 'Hello Zeon7, test turn.');
    check("Sanctum Append User Turn", ($appendUser['success'] ?? false) === true);

    $appendAsst = $client->appendMessage($sid, 'assistant', 'Greetings operator, turn logged.');
    check("Sanctum Append Assistant Turn", ($appendAsst['success'] ?? false) === true);

    $hist = $client->getConversation($sid);
    check("Sanctum Conversation Retrieval (2 Turns)", count($hist['messages'] ?? []) === 2);
}

// 6. Token Budget
$budget = $client->getBudget();
check("Council Token Budget Ledger", ($budget['success'] ?? false) === true);

// 7. LoreService Council Authority
require_once __DIR__ . '/../src/services/LoreService.php';
$loreService = new LoreService();
$allLore = $loreService->getAll();
check("LoreService Routing to Council Sanctum", is_array($allLore));

// 8. KnowledgeService Council Authority
require_once __DIR__ . '/../src/services/KnowledgeService.php';
$knowService = new KnowledgeService();
$allFiles = $knowService->getAllFiles();
check("KnowledgeService Routing to Council Commons Files", is_array($allFiles) && count($allFiles) > 0);

$knowSearch = $knowService->searchChunks('Foreverbox');
check("KnowledgeService Vector/Hybrid Search over Commons", is_array($knowSearch));

echo "\n----------------------------------------------------\n";
echo "Results: {$pass} / {$total} Tests Passed.\n";
if ($pass === $total) {
    echo ">>> HERMES V2 FULL COUNCIL INTEGRATION VERIFIED 100%! <<<\n";
}
echo "----------------------------------------------------\n";

