<?php
// Manually load .env since we are in CLI
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            putenv(sprintf('%s=%s', trim($parts[0]), trim($parts[1])));
            $_ENV[trim($parts[0])] = trim($parts[1]);
        }
    }
}

require_once __DIR__ . '/../src/core/DatabaseService.php';

try {
    $db = DatabaseService::getInstance();
    
    $loreCount = $db->query("SELECT COUNT(*) FROM lore")->fetchColumn();
    echo "Lore Count: " . $loreCount . "\n";

    $docCount = $db->query("SELECT COUNT(*) FROM knowledge_doc")->fetchColumn();
    echo "Knowledge Docs: " . $docCount . "\n";

    $instrCount = $db->query("SELECT COUNT(*) FROM system_instructions")->fetchColumn();
    echo "Instructions: " . $instrCount . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
