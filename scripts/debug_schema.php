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
    $tables = ['lore', 'knowledge_doc', 'knowledge_chunk', 'system_instructions'];

    foreach ($tables as $table) {
        echo "Table: $table\n";
        try {
            $stmt = $db->query("DESCRIBE `$table`");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo " - " . $col['Field'] . " (" . $col['Type'] . ")\n";
            }
        } catch (Exception $e) {
            echo " - Error describing table: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Connection Error: " . $e->getMessage() . "\n";
}
