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
    echo "Updating 'lore' table schema...\n";

    // Add 'type' if missing
    $stmt = $db->query("SHOW COLUMNS FROM lore LIKE 'type'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE lore ADD COLUMN type VARCHAR(50) NOT NULL DEFAULT 'general' AFTER id");
        echo "Added 'type' column.\n";
    }

    // Add 'content' if missing
    $stmt = $db->query("SHOW COLUMNS FROM lore LIKE 'content'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE lore ADD COLUMN content TEXT DEFAULT NULL AFTER type");
        echo "Added 'content' column.\n";
    }

    // Add 'tags' if missing
    $stmt = $db->query("SHOW COLUMNS FROM lore LIKE 'tags'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE lore ADD COLUMN tags JSON DEFAULT NULL AFTER content");
        echo "Added 'tags' column.\n";
    }

    // Add 'is_public' if missing
    $stmt = $db->query("SHOW COLUMNS FROM lore LIKE 'is_public'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE lore ADD COLUMN is_public TINYINT(1) DEFAULT 0 AFTER tags");
        echo "Added 'is_public' column.\n";
    }

    echo "Schema update complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
