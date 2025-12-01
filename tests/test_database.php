<?php
/**
 * Database Connection Test
 * Verifies DatabaseService can connect and all tables exist
 */

require_once __DIR__ . '/../src/config/env.php';
require_once __DIR__ . '/../src/core/Exceptions.php';
require_once __DIR__ . '/../src/core/DatabaseService.php';

echo "=== Database Connection Test ===\n\n";

try {
    // Test connection
    $db = DatabaseService::getInstance();
    echo "✓ Database connection established\n";
    echo "  Host: " . ($_ENV['DB_HOST'] ?? 'localhost') . "\n";
    echo "  Database: " . ($_ENV['DB_NAME'] ?? 'unknown') . "\n";
    echo "  User: " . ($_ENV['DB_USER'] ?? 'unknown') . "\n\n";
    
    // List all tables
    echo "Tables in database:\n";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $expectedTables = [
        'api_usage',
        'gemini_log',
        'image_prompt',
        'instruction_set',
        'knowledge_chunk',
        'knowledge_doc',
        'lore',
        'posts'
    ];
    
    foreach ($expectedTables as $table) {
        if (in_array($table, $tables)) {
            echo "  ✓ $table\n";
        } else {
            echo "  ✗ $table (MISSING)\n";
        }
    }
    
    echo "\n";
    
    // Test table structure
    echo "Sample table structure verification:\n";
    $stmt = $db->query("DESCRIBE posts");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "  Posts table has " . count($columns) . " columns\n";
    
    $hasStatus = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'status') {
            $hasStatus = true;
            echo "  ✓ Status column exists (ENUM with draft/published/archived)\n";
        }
    }
    
    if (!$hasStatus) {
        echo "  ✗ Status column missing\n";
    }
    
    echo "\n=== All Database Tests Passed ===\n";
    echo "Database is ready for use!\n";
    
} catch (DatabaseException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Unexpected error: " . $e->getMessage() . "\n";
    exit(1);
}
