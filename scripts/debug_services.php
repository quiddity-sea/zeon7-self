<?php
// Manually load .env
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            putenv(trim($line));
        }
    }
}

require_once __DIR__ . '/../src/services/LoreService.php';
require_once __DIR__ . '/../src/services/KnowledgeService.php';

try {
    echo "--- LORE ---\n";
    $lore = new LoreService();
    $allLore = $lore->getAll();
    echo json_encode($allLore, JSON_PRETTY_PRINT) . "\n";

    echo "--- KNOWLEDGE ---\n";
    $kn = new KnowledgeService();
    $allFiles = $kn->getAllFiles();
    echo json_encode($allFiles, JSON_PRETTY_PRINT) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
