<?php
declare(strict_types=1);

require_once '/var/www/self/src/config/env.php';

// Test against primary VPS Council over Tailscale
$_ENV['COUNCIL_API_URL'] = 'http://100.126.174.30:8080';
$_ENV['COUNCIL_API_KEY'] = '9a66eb987eb2a1949b0381556fc8c487808ae7f134bdbe2923ce45f1c6197073';
$_ENV['COUNCIL_AGENT_ID'] = 'zeon7';

require_once '/var/www/self/src/services/CouncilClient.php';

echo "====================================================\n";
echo "   PHASE 13 & 14: ASSIGNMENTS & SECURITY HARDENING  \n";
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

// 1. Phase 13: User-Agent-Template Assignment Lifecycle
$testUid = 9999;
$assignmentData = [
    'user_id'      => $testUid,
    'agent_id'     => 'zeon7',
    'template_id'  => 'zeon7-cockpit',
    'permissions'  => ['chat', 'memory_read', 'commons_read'],
    'memory_scope' => 'assigned_agent',
    'status'       => 'active'
];

$upsertRes = $client->upsertAssignment($assignmentData);
check("Upsert User-Agent Assignment in Council", ($upsertRes['success'] ?? false) === true);

$userAssignments = $client->getUserAssignments($testUid);
$list = $userAssignments['assignments'] ?? [];
check("Retrieve User Assignments for user_id {$testUid}", count($list) >= 1);

if (!empty($list)) {
    $first = $list[0];
    check("Assigned agent is 'zeon7'", $first['agent_id'] === 'zeon7');
    check("Assigned template is 'zeon7-cockpit'", $first['template_id'] === 'zeon7-cockpit');
    check("Assigned permissions contain 'chat'", in_array('chat', $first['permissions'] ?? []));
}

// 2. Phase 14: Security Hardening Checks
$baseUrl = 'http://100.126.174.30:8080';

// A. Unauthenticated access rejected with 401
$ch = curl_init("{$baseUrl}/v1/sanctum/memory");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 5
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
check("Unauthenticated request to protected route returns 401", $code === 401);

// B. Invalid token rejected with 401
$ch = curl_init("{$baseUrl}/v1/sanctum/memory");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer invalid_test_token_xyz'],
    CURLOPT_TIMEOUT        => 5
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
check("Invalid token request returns 401", $code === 401);

// C. Valid token accepted with 200
$ch = curl_init("{$baseUrl}/v1/sanctum/memory");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer 9a66eb987eb2a1949b0381556fc8c487808ae7f134bdbe2923ce45f1c6197073',
        'X-Agent-ID: zeon7'
    ],
    CURLOPT_TIMEOUT        => 5
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
check("Valid Bearer token accepted with 200", $code === 200);

// D. Public Health check accessible with 200 (no auth required)
$ch = curl_init("{$baseUrl}/v1/healthz");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 5
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
check("Public /v1/healthz endpoint accessible without auth (200)", $code === 200);

echo "\n----------------------------------------------------\n";
echo "Results: {$pass} / {$total} Tests Passed.\n";
if ($pass === $total) {
    echo ">>> PHASE 13 & 14 ASSIGNMENTS & SECURITY VERIFIED 100%! <<<\n";
}
echo "----------------------------------------------------\n";
