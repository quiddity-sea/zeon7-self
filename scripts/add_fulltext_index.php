<?php
// Manually load .env since we are in CLI and env.php is missing
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
        }
    }
}
require_once __DIR__ . '/../src/core/DatabaseService.php';

try {
    $db = DatabaseService::getInstance();
    echo "Checking for FULLTEXT index on knowledge_chunk...\n";
    
    // Check if index exists
    $stmt = $db->query("SHOW INDEX FROM knowledge_chunk WHERE Key_name = 'idx_content_fulltext'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "Index missing. Creating FULLTEXT index...\n";
        $db->exec("ALTER TABLE knowledge_chunk ADD FULLTEXT idx_content_fulltext (content)");
        echo "Index created successfully.\n";
    } else {
        echo "FULLTEXT index already exists.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
