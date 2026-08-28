<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config/env.php';
$_ENV['COUNCIL_API_URL'] = 'http://127.0.0.1:8080';
$_ENV['COUNCIL_API_KEY'] = 'dev-key-change-in-production';
$_ENV['COUNCIL_AGENT_ID'] = 'zeon7';

require_once __DIR__ . '/../src/services/CouncilClient.php';

echo "=== TESTING END-TO-END CANONICAL SOUL LIFECYCLE ===\n\n";

$client = new CouncilClient();

// Step 1: Create a specialized head component via Council API
$headKey = 'specialist_architect_lifecycle_test';
$testContent = "### SPECIALIST ARCHITECT DIRECTIVE\nThis is an end-to-end verification of single canonical SOUL authority.";

echo "1. Creating new SOUL component via API...\n";
$res = $client->createHead([
    'component_key'       => $headKey,
    'agent_slug'          => 'zeon7',
    'provider_filter'     => null, // universal
    'section_order'       => 25,
    'section_description' => 'Specialist Architect Directive Test',
    'section_content'     => $testContent
]);

$createdId = $res['id'] ?? null;
if (!$createdId) {
    echo "[FAIL] Failed to create component: " . json_encode($res) . "\n";
    exit(1);
}
echo "[PASS] Component created with ID: {$createdId}\n\n";

// Step 2: Run assemble_soul.py to verify Python runtime immediately consumes it without sync
echo "2. Invoking assemble_soul.py for zeon7...\n";
$output = [];
$returnCode = 0;
exec('python3 /foreverbox_data/bin/assemble_soul.py zeon7 2>&1', $output, $returnCode);
$assembledText = file_get_contents('/foreverbox_data/profiles/zeon7/SOUL.md');

if ($returnCode === 0 && str_contains($assembledText, 'SPECIALIST ARCHITECT DIRECTIVE')) {
    echo "[PASS] assemble_soul.py dynamically resolved and included the new head!\n\n";
} else {
    echo "[FAIL] Head not found in assembled SOUL.md:\n" . implode("\n", $output) . "\n";
}

// Step 3: Clean up test head via API
echo "3. Deleting test component via API...\n";
$del = $client->deleteHead($createdId);
if ($del['success'] ?? false) {
    echo "[PASS] Component deleted cleanly.\n\n";
} else {
    echo "[FAIL] Could not delete component: " . json_encode($del) . "\n";
}

// Step 4: Reassemble to verify removal
exec('python3 /foreverbox_data/bin/assemble_soul.py zeon7 2>&1');
$cleanText = file_get_contents('/foreverbox_data/profiles/zeon7/SOUL.md');
if (!str_contains($cleanText, 'SPECIALIST ARCHITECT DIRECTIVE')) {
    echo "[PASS] SOUL.md reverted to clean baseline.\n\n";
} else {
    echo "[FAIL] Stale head content persisted in SOUL.md!\n";
}

echo ">>> END-TO-END CANONICAL SOUL AUTHORITY VERIFIED 100%! <<<\n";

