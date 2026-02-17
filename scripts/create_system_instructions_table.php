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
    echo "Checking 'system_instructions' table...\n";

    // Create table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS system_instructions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL DEFAULT 'core',
        content TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'system_instructions' created/checked successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
