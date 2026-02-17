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

require_once __DIR__ . '/../src/core/DatabaseService.php';

try {
    $db = DatabaseService::getInstance();
    echo "Fixing Lore table...\n";
    
    // Check if created_at exists
    $stmt = $db->query("SHOW COLUMNS FROM lore LIKE 'created_at'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE lore ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_public");
        echo "Added 'created_at' column.\n";
    } else {
        echo "'created_at' already exists.\n";
    }

    // Also check correct column types while we are here, just in case
    // The previous debug showed 'key' and 'value', which are legacy.
    // 'type', 'content', 'tags', 'is_public' exist.
    
    echo "Lore table schema patched.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
