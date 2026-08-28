<?php
declare(strict_types=1);

require_once '/var/www/self/src/config/env.php';
$_ENV['COUNCIL_API_URL'] = 'http://100.126.174.30:8080';
$_ENV['COUNCIL_API_KEY'] = '9a66eb987eb2a1949b0381556fc8c487808ae7f134bdbe2923ce45f1c6197073';
$_ENV['COUNCIL_AGENT_ID'] = 'zeon7';
$_ENV['MEMORY_BACKEND'] = 'council';
$_ENV['KNOWLEDGE_BACKEND'] = 'council';
$_ENV['CONVERSATION_BACKEND'] = 'council';
$_ENV['SOUL_BACKEND'] = 'council';

require_once '/var/www/self/src/services/CouncilClient.php';
require_once '/var/www/self/src/services/LoreService.php';
require_once '/var/www/self/src/services/KnowledgeService.php';
require_once '/var/www/self/src/services/InstructionService.php';
require_once '/var/www/self/src/services/AgentContextService.php';

echo "====================================================\n";
echo "   HERMES V2 INTEGRATION — MAIN PC TO VPS RUNTIME   \n";
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

// 1. Connectivity over Tailscale to VPS
check("VPS Council REST API over Tailscale (100.126.174.30:8080)", $client->isAvailable());

// 2. Agent Roster from VPS
$agents = $client->getAgents();
check("VPS Agent Roster (5 Canonical Agents)", ($agents['count'] ?? 0) >= 5);

$heads = $client->getHeads('zeon7');
check("VPS Zeon7 Dynamic SOUL Components", ($heads['count'] ?? 0) > 0);

// 3. Sanctum Memory on VPS
$memKey = 'vps_tailscale_test_fact';
$upsertMem = $client->upsertMemory('core', $memKey, [
    'content'    => 'Zeon7 is operational on the primary VPS runtime over Tailscale mesh.',
    'importance' => 90,
    'source'     => 'tailscale_vps_test'
]);
check("Sanctum Memory Upsert on VPS", ($upsertMem['success'] ?? false) === true);

$searchMem = $client->searchMemory('primary VPS runtime');
check("Sanctum Memory Search on VPS", is_array($searchMem['results'] ?? null));

$delMem = $client->deleteMemory('core', $memKey);
check("Sanctum Memory Delete on VPS", ($delMem['success'] ?? false) === true);

// 4. Sanctum Conversations on VPS
$conv = $client->createConversation();
$sid = $conv['session_id'] ?? '';
check("Sanctum Conversation Creation on VPS", !empty($sid));

if ($sid) {
    $appendUser = $client->appendMessage($sid, 'user', 'Ping from Main PC to VPS.');
    check("Sanctum Append User Turn on VPS", ($appendUser['success'] ?? false) === true);

    $appendAsst = $client->appendMessage($sid, 'assistant', 'Pong from primary VPS Hermes.');
    check("Sanctum Append Assistant Turn on VPS", ($appendAsst['success'] ?? false) === true);

    $hist = $client->getConversation($sid);
    check("Sanctum Conversation Retrieval (2 Turns) on VPS", count($hist['messages'] ?? []) === 2);
}

// 5. Token Budget on VPS
$budget = $client->getBudget();
check("Council Token Budget Ledger on VPS", ($budget['success'] ?? false) === true);

// 6. LoreService Routing to VPS Sanctum
$loreService = new LoreService();
$allLore = $loreService->getAll();
check("LoreService Routing to VPS Sanctum", is_array($allLore));

echo "\n----------------------------------------------------\n";
echo "Results: {$pass} / {$total} Tests Passed.\n";
if ($pass === $total) {
    echo ">>> MAIN PC TO PRIMARY VPS COUNCIL RUNTIME VERIFIED 100%! <<<\n";
}
echo "----------------------------------------------------\n";
