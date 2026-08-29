<?php
require_once __DIR__ . '/../src/config/env.php';
require_once __DIR__ . '/../src/core/DatabaseService.php';

try {
    $pdo = DatabaseService::getInstance();

    $sql = "
    CREATE TABLE IF NOT EXISTS token_usage (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        prompt_tokens INT,
        response_tokens INT,
        total_tokens INT,
        INDEX idx_timestamp (timestamp)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Table 'token_usage' created successfully (or already exists).\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
