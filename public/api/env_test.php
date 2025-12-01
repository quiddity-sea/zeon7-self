<?php
require_once __DIR__ . '/../../src/config/env.php';

header('Content-Type: application/json');

$vars = [
    'DB_HOST' => [
        'ENV' => $_ENV['DB_HOST'] ?? null,
        'SERVER' => $_SERVER['DB_HOST'] ?? null,
        'getenv' => getenv('DB_HOST')
    ],
    'DB_NAME' => [
        'ENV' => $_ENV['DB_NAME'] ?? null,
        'SERVER' => $_SERVER['DB_NAME'] ?? null,
        'getenv' => getenv('DB_NAME')
    ],
    'DB_USER' => [
        'ENV' => $_ENV['DB_USER'] ?? null,
        'SERVER' => $_SERVER['DB_USER'] ?? null,
        'getenv' => getenv('DB_USER')
    ],
    'DB_PASS_LEN' => [
        'ENV' => isset($_ENV['DB_PASS']) ? strlen($_ENV['DB_PASS']) : 0,
        'SERVER' => isset($_SERVER['DB_PASS']) ? strlen($_SERVER['DB_PASS']) : 0,
        'getenv' => getenv('DB_PASS') ? strlen(getenv('DB_PASS')) : 0
    ],
    'env_file_path' => realpath(__DIR__ . '/../../.env'),
    'env_file_exists' => file_exists(__DIR__ . '/../../.env')
];

$vars['pdo_mysql_loaded'] = extension_loaded('pdo_mysql');

try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $name = $_ENV['DB_NAME'] ?? 'zeon7_self_dev';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    
    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
    $vars['dsn_attempted'] = $dsn;
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $vars['db_connection'] = 'SUCCESS';
} catch (PDOException $e) {
    $vars['db_connection'] = 'FAILED';
    $vars['db_error'] = $e->getMessage();
    $vars['db_code'] = $e->getCode();
}

echo json_encode($vars, JSON_PRETTY_PRINT);
