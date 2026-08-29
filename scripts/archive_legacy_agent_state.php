<?php
declare(strict_types=1);

/**
 * Phase 11: Legacy Self Agent State Archival Tool
 * Safely exports legacy agent tables before deprecation.
 */

require_once '/var/www/self/src/config/env.php';
require_once '/var/www/self/src/core/DatabaseService.php';

$archiveDir = '/var/www/self/storage/archives';
if (!is_dir($archiveDir)) {
    mkdir($archiveDir, 0755, true);
}

$timestamp = date('Ymd_His');
$archiveFile = "{$archiveDir}/legacy_agent_state_{$timestamp}.json";

echo "====================================================\n";
echo "   PHASE 11: LEGACY AGENT STATE ARCHIVAL TOOL      \n";
echo "====================================================\n\n";

$db = DatabaseService::getInstance();

$tablesToArchive = [
    'lore',
    'knowledge_doc',
    'knowledge_chunk',
    'system_instructions',
    'instruction_set',
    'chat_logs',
];

$exportData = [
    'exported_at' => date('c'),
    'database'    => 'zeon7_self_dev',
    'tables'      => []
];

foreach ($tablesToArchive as $table) {
    try {
        $stmt = $db->query("SELECT * FROM `{$table}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $exportData['tables'][$table] = [
            'count' => count($rows),
            'data'  => $rows
        ];
        echo "  [OK] Archived {$table}: " . count($rows) . " records\n";
    } catch (\PDOException $e) {
        echo "  [SKIP] Table {$table} not found or error: " . $e->getMessage() . "\n";
    }
}

file_put_contents($archiveFile, json_encode($exportData, JSON_PRETTY_PRINT));

echo "\n----------------------------------------------------\n";
echo "Archive saved to: {$archiveFile} (" . round(filesize($archiveFile) / 1024, 2) . " KB)\n";
echo ">>> PHASE 11 ARCHIVAL SNAPSHOT COMPLETE <<<\n";
echo "----------------------------------------------------\n";
