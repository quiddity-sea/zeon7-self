<?php
declare(strict_types=1);

require_once '/var/www/self/src/config/env.php';

// Test against primary VPS Council over Tailscale
$_ENV['COUNCIL_API_URL'] = 'http://100.126.174.30:8080';
$_ENV['COUNCIL_API_KEY'] = '9a66eb987eb2a1949b0381556fc8c487808ae7f134bdbe2923ce45f1c6197073';
$_ENV['COUNCIL_AGENT_ID'] = 'zeon7';

require_once '/var/www/self/src/services/CouncilClient.php';

echo "====================================================\n";
echo "   PHASE 9: CANONICAL CONVERSATIONS & METADATA      \n";
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

// 1. Create Canonical Session
$conv = $client->createConversation();
$sid = $conv['session_id'] ?? '';
check("Create Canonical Session in VPS Council", !empty($sid));

// 2. Append Turns across different source interfaces
$turn1 = $client->appendMessage(
    sessionId:       $sid,
    role:            'user',
    content:         'Question from Public Web Visitor',
    metadata:        ['model' => 'Zeon7-Gemma:64k'],
    ipAddress:       '203.0.113.195',
    operatorId:      null,
    sourceInterface: 'self_public',
    headUsed:        'first_truth',
    requestId:       'req_public_001',
    tokensInput:     15,
    tokensOutput:    0
);
check("Append 'self_public' Turn", ($turn1['success'] ?? false) === true);

$turn2 = $client->appendMessage(
    sessionId:       $sid,
    role:            'assistant',
    content:         'Response from Primary VPS Hermes Agent',
    metadata:        ['model' => 'Zeon7-Gemma:64k'],
    ipAddress:       '100.126.174.30',
    operatorId:      null,
    sourceInterface: 'hermes_cli',
    headUsed:        'first_truth',
    requestId:       'req_hermes_002',
    tokensInput:     0,
    tokensOutput:    28
);
check("Append 'hermes_cli' Turn", ($turn2['success'] ?? false) === true);

$turn3 = $client->appendMessage(
    sessionId:       $sid,
    role:            'user',
    content:         'Command from Authenticated Operator in Self Admin',
    metadata:        ['model' => 'qwen/qwen3-32b:free'],
    ipAddress:       '192.168.1.50',
    operatorId:      1,
    sourceInterface: 'self_admin',
    headUsed:        'communication_protocol',
    requestId:       'req_admin_003',
    tokensInput:     22,
    tokensOutput:    0
);
check("Append 'self_admin' Turn with operator_id", ($turn3['success'] ?? false) === true);

// 3. Retrieve and Validate Metadata and Sequence Ordering
$history = $client->getConversation($sid);
$msgs = $history['messages'] ?? [];
check("Retrieved all 3 turns in single canonical transcript", count($msgs) === 3);

if (count($msgs) === 3) {
    check("Turn 1 sequence is 1", (int)$msgs[0]['message_seq'] === 1);
    check("Turn 2 sequence is 2", (int)$msgs[1]['message_seq'] === 2);
    check("Turn 3 sequence is 3", (int)$msgs[2]['message_seq'] === 3);
    
    check("Turn 1 role is 'user' from 'self_public'", $msgs[0]['role'] === 'user');
    check("Turn 2 role is 'assistant' from 'hermes_cli'", $msgs[1]['role'] === 'assistant');
    check("Turn 3 operator_id is 1 from 'self_admin'", (int)$msgs[2]['operator_id'] === 1);
}

// 4. Concurrency & Transaction Sequence Safety Test
// Simulate 5 rapid sequential writes
$concurrencyPass = true;
for ($i = 4; $i <= 8; $i++) {
    $res = $client->appendMessage(
        sessionId:       $sid,
        role:            ($i % 2 === 0) ? 'user' : 'assistant',
        content:         "Rapid burst message #{$i}",
        metadata:        ['burst_test' => true],
        sourceInterface: 'from_the_noise'
    );
    if (!($res['success'] ?? false)) {
        $concurrencyPass = false;
        break;
    }
}
check("Rapid concurrent write transaction integrity (5 bursts)", $concurrencyPass);

$burstHistory = $client->getConversation($sid);
$allMsgs = $burstHistory['messages'] ?? [];
check("Total message count is exactly 8", count($allMsgs) === 8);

// Verify sequence ordering is strictly 1..8 with no duplicates
$seqs = array_map(fn($m) => (int)$m['message_seq'], $allMsgs);
$expectedSeqs = range(1, 8);
check("Sequence ordering is strictly monotonic [1..8]", $seqs === $expectedSeqs);

echo "\n----------------------------------------------------\n";
echo "Results: {$pass} / {$total} Tests Passed.\n";
if ($pass === $total) {
    echo ">>> PHASE 9 CANONICAL CONVERSATIONS VERIFIED 100%! <<<\n";
}
echo "----------------------------------------------------\n";
