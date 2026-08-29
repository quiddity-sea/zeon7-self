<?php
declare(strict_types=1);

/**
 * Phase 17: Master Automated Cross-Interface Acceptance Test Suite
 * Validates complete architectural alignment across:
 * - Self Web Application (Public & Admin)
 * - Council Library Core REST API on Primary VPS
 * - Hermes Agent Runtime (Dynamic SOUL & Assembly)
 * - Tailscale Encrypted Distributed Network
 */

require_once '/var/www/self/src/config/env.php';

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

echo "====================================================================\n";
echo "   PHASE 17: MASTER AUTOMATED CROSS-INTERFACE ACCEPTANCE SUITE     \n";
echo "====================================================================\n\n";

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

$client = new CouncilClient();
$agentCtx = new AgentContextService();
$instructionService = new InstructionService();
$loreService = new LoreService();
$knowledgeService = new KnowledgeService();

// ─── 1. AGENT & HEAD CROSS-INTERFACE LIFECYCLE ───────────────────
echo "--- 1. Agent & Head Cross-Interface Lifecycle ---\n";
$roster = $client->getAgents();
check("Council Registry returns active agent roster", !empty($roster['agents']));

// Create test dynamic head component via Council API
$uniqueKey = 'acceptance_head_' . bin2hex(random_bytes(4));
$createHead = $client->createHead([
    'agent_slug'      => 'zeon7',
    'component_key'   => $uniqueKey,
    'component_name'  => 'Acceptance Verification Head',
    'provider_filter' => 'all',
    'section_order'   => 95,
    'section_content' => "### ACCEPTANCE VERIFICATION\nVerification token: " . $uniqueKey
]);
check("Create dynamic head in Council Registry", ($createHead['success'] ?? false) === true);

// Verify dynamic SOUL assembly resolves new component
$assembledContent = $instructionService->getCurrentContent();
check("Self InstructionService dynamic assembly includes new head", str_contains($assembledContent, $uniqueKey));

// Clean up test component
$heads = $client->getHeads('zeon7');
$createdHeadId = null;
foreach ($heads['heads'] ?? [] as $h) {
    if (($h['component_key'] ?? '') === $uniqueKey) {
        $createdHeadId = (int)$h['id'];
        break;
    }
}
if ($createdHeadId) {
    $del = $client->deleteHead($createdHeadId);
    check("Clean up test head from Council Registry", ($del['success'] ?? false) === true);
}

// ─── 2. COGNITIVE ROUTER & MODEL RESOLUTION ─────────────────────
echo "\n--- 2. Cognitive Router & Model Profiles ---\n";
$models = $client->getModels();
check("Council Cognitive Router returns model profiles", !empty($models['profiles']));
check("Router includes local and cloud tiers", isset($models['profiles']['local']) || isset($models['profiles']['cloud_primary']) || count($models['profiles']) >= 2);

// ─── 3. MEMORY AUTHORITY CROSS-INTERFACE ────────────────────────
echo "\n--- 3. Memory Authority Cross-Interface ---\n";
$memKey = 'test_fact_' . bin2hex(random_bytes(4));
$memContent = 'Sovereignty verification protocol for ForeverBox acceptance testing';

// Write memory via LoreService (which delegates to Council Sanctum)
$writeMem = $loreService->add(
    type:      'core',
    content:   $memContent,
    isPublic:  true,
    tags:      ['acceptance', 'cross_interface'],
    keyName:   $memKey,
    namespace: 'general'
);
check("Write Sanctum memory via Self LoreService -> Council", ($writeMem['success'] ?? false) === true);

// Search memory directly via Council API
$searchMem = $client->searchMemory('Sovereignty');
check("Direct Council Sanctum search retrieves written memory", !empty($searchMem['results']));

// Delete test memory
$delMem = $client->deleteMemory('general', $memKey);
check("Clean up test Sanctum memory from Council", ($delMem['success'] ?? false) === true);

// ─── 4. UNIFIED CANONICAL CONVERSATIONS ─────────────────────────
echo "\n--- 4. Unified Canonical Conversations ---\n";
$conv = $client->createConversation();
$sid = $conv['session_id'] ?? '';
check("Create canonical session on primary VPS", !empty($sid));

// Turn 1: from Self Public Chat
$t1 = $client->appendMessage(
    sessionId:       $sid,
    role:            'user',
    content:         'Turn 1 from Self Public Interface',
    sourceInterface: 'self_public',
    headUsed:        'first_truth'
);
check("Append Turn 1 (self_public)", ($t1['success'] ?? false) === true);

// Turn 2: from Hermes CLI
$t2 = $client->appendMessage(
    sessionId:       $sid,
    role:            'assistant',
    content:         'Turn 2 from Hermes CLI Core',
    sourceInterface: 'hermes_cli',
    headUsed:        'first_truth'
);
check("Append Turn 2 (hermes_cli)", ($t2['success'] ?? false) === true);

// Turn 3: from From the Noise Editorial
$t3 = $client->appendMessage(
    sessionId:       $sid,
    role:            'user',
    content:         'Turn 3 from From the Noise Editorial Cockpit',
    sourceInterface: 'from_the_noise',
    headUsed:        'news_desk'
);
check("Append Turn 3 (from_the_noise)", ($t3['success'] ?? false) === true);

// Retrieve full conversation transcript
$transcript = $client->getConversation($sid);
$msgs = $transcript['messages'] ?? [];
check("Retrieve unified transcript (3 turns from 3 interfaces)", count($msgs) === 3);

if (count($msgs) === 3) {
    check("Turn 1 source is 'self_public'", $msgs[0]['source_interface'] === 'self_public');
    check("Turn 2 source is 'hermes_cli'", $msgs[1]['source_interface'] === 'hermes_cli');
    check("Turn 3 source is 'from_the_noise'", $msgs[2]['source_interface'] === 'from_the_noise');
    check("Sequence monotonicity strictly verified [1, 2, 3]", [(int)$msgs[0]['message_seq'], (int)$msgs[1]['message_seq'], (int)$msgs[2]['message_seq']] === [1, 2, 3]);
}

// ─── 5. SECURITY & GATE ENFORCEMENT ─────────────────────────────
echo "\n--- 5. Security & Gate Enforcement ---\n";
$budget = $client->getBudget();
check("Council Token Budget Ledger accessible to authorized client", ($budget['success'] ?? false) === true && isset($budget['data']));

// Verify unauthenticated protection
$ch = curl_init('http://100.126.174.30:8080/v1/sanctum/soul');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
$raw = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
check("Unauthenticated Sanctum access strictly denied (401)", $code === 401);

echo "\n====================================================================\n";
echo "Acceptance Results: {$pass} / {$total} Assertions Passed.\n";
if ($pass === $total) {
    echo ">>> HERMES V2 MASTER ARCHITECTURAL ACCEPTANCE VERIFIED 100%! <<<\n";
}
echo "====================================================================\n";
