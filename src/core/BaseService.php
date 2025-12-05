<?php
/**
 * BaseService - Foundation for all service layer classes
 * Provides common database operations and transaction management
 */

require_once __DIR__ . '/DatabaseService.php';

abstract class BaseService {
    protected PDO $db;
    
    public function __construct() {
        $this->db = DatabaseService::getInstance();
    }
    
    /**
     * Execute query with error handling
     */
    protected function executeQuery(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            throw new DatabaseException("Database query failed", 500, $e);
        }
    }
    
    /**
     * Fetch single row
     */
    protected function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->executeQuery($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Fetch all rows
     */
    protected function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Begin transaction
     */
    protected function beginTransaction(): void {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }
    
    /**
     * Commit transaction
     */
    protected function commit(): void {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }
    
    /**
     * Rollback transaction
     */
    protected function rollback(): void {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
    
    /**
     * Execute within transaction
     */
    protected function transaction(callable $callback): mixed {
        $this->beginTransaction();
        
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get last inserted ID
     */
    protected function lastInsertId(): string {
        return $this->db->lastInsertId();
    }
}
