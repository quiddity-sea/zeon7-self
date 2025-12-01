<?php
/**
 * Database Connection Test
 * 
 * Verifies that we can connect to the zeon7_self_dev database
 * Run: wsl php /mnt/e/Dev/Projects/self/tests/test_db_connection.php
 */

// Load environment variables
require_once __DIR__ . '/../src/config/env.php';

// Try to connect
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $name = $_ENV['DB_NAME'] ?? 'zeon7_self_dev';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    
    echo "Attempting to connect to database...\n";
    echo "Host: $host\n";
    echo "Database: $name\n";
    echo "User: $user\n";
    echo "Password: " . (empty($pass) ? '(empty)' : '****') . "\n";
    echo "\n";
    
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Database connection successful!\n\n";
    
    // Test query to show tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "Tables found (" . count($tables) . "):\n";
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    } else {
        echo "⚠ No tables found. You may need to run the migration:\n";
        echo "  wsl mysql -u $user -p $name < /mnt/e/Dev/Projects/self/docs/database/migration.sql\n";
    }
    
    echo "\n✓ Database test completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Database connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    echo "Troubleshooting steps:\n";
    echo "1. Verify MySQL is running:\n";
    echo "   wsl systemctl status mysql\n\n";
    echo "2. Test credentials manually:\n";
    echo "   wsl mysql -u $user -p\n\n";
    echo "3. Create database if it doesn't exist:\n";
    echo "   wsl mysql -u $user -p -e \"CREATE DATABASE $name;\"\n\n";
    
    exit(1);
}
