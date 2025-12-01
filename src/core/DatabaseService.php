<?php
/**
 * Database Service - Singleton PDO connection manager
 */
require_once __DIR__ . '/Exceptions.php';

class DatabaseService {
    private static ?PDO $instance = null;
    
    /**
     * Get PDO instance (creates if doesn't exist)
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }
    
    /**
     * Create new PDO connection
     */
    private static function createConnection(): PDO {
        $host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $name = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?? 'zeon7';
        $user = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER') ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASS') ?? '';
        
        // Debug logging
        error_log("Connecting to DB: host=$host, name=$name, user=$user");
        
        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
        
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false
            ]);
            
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new DatabaseException("Could not connect to database: " . $e->getMessage(), 500, $e);
        }
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialization singleton");
    }
}


