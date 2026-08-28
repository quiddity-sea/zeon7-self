<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config/env.php';
$_ENV['COUNCIL_API_URL'] = 'http://127.0.0.1:8080';
$_ENV['COUNCIL_API_KEY'] = 'dev-key-change-in-production';
$_ENV['COUNCIL_AGENT_ID'] = 'zeon7';

require_once __DIR__ . '/../src/services/CouncilClient.php';

echo "=== TESTING SELF -> COUNCIL CLIENT INTEGRATION ===\n\n";

$client = new CouncilClient();

// 1. Health check
$avail = $client->isAvailable();
echo "1. Council API Available: " . ($avail ? "[PASS]" : "[FAIL]") . "\n";

// 2. Agents Catalogue
$agents = $client->getAgents();
echo "2. Agents Catalogue: " . (($agents['success'] ?? false) && count($agents['agents'] ?? []) >= 5 ? "[PASS] (" . count($agents['agents']) . " agents)" : "[FAIL]") . "\n";
foreach ($agents['agents'] ?? [] as $a) {
    echo "   - {$a['slug']} ({$a['display_name']}): " . count($a['heads']) . " heads\n";
}

// 3. Heads Catalogue
$heads = $client->getHeads();
echo "3. Heads Catalogue: " . (($heads['success'] ?? false) && count($heads['heads'] ?? []) > 0 ? "[PASS] (" . count($heads['heads']) . " heads)" : "[FAIL]") . "\n";

// 4. Model Profiles
$models = $client->getModels();
echo "4. Model Profiles: " . (($models['success'] ?? false) && isset($models['profiles']) ? "[PASS]" : "[FAIL]") . "\n";

// 5. Sanctum Memory via Client
$mems = $client->listMemory();
echo "5. Sanctum Memory List: " . (is_array($mems['results'] ?? null) ? "[PASS]" : "[FAIL]") . "\n";

// 6. InstructionService Dynamic Heads Bridge
require_once __DIR__ . '/../src/services/InstructionService.php';
$instService = new InstructionService();
$components = $instService->getAgentComponents('zeon7');
echo "6. InstructionService Dynamic Heads: " . (count($components) > 0 ? "[PASS] (" . count($components) . " components loaded from Council)" : "[FAIL]") . "\n";
foreach ($components as $k => $c) {
    echo "   - [{$k}] {$c['name']} (Order: {$c['order']})\n";
}

echo "\n>>> COUNCIL CLIENT VALIDATION COMPLETE <<<\n";

