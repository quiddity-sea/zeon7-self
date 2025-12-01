<?php
/**
 * LoreService - Manage mutable memory (key-value lore storage)
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';

class LoreService extends BaseService {
    
    /**
     * Get all lore entries
     */
    public function getAll(): array {
        $sql = "SELECT id, type, content, tags, is_public, created_at, updated_at 
                FROM lore 
                ORDER BY created_at DESC";
        return $this->fetchAll($sql);
    }

    /**
     * Get only public lore entries
     */
    public function getPublic(): array {
        $sql = "SELECT type, content, tags 
                FROM lore 
                WHERE is_public = 1 
                ORDER BY created_at DESC";
        return $this->fetchAll($sql);
    }
    
    /**
     * Get single lore entry by ID
     */
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM lore WHERE id = ?";
        return $this->fetchOne($sql, [$id]);
    }
    
    /**
     * Create new lore entry
     */
    public function create(string $type, string $content, array $tags = [], bool $isPublic = false): int {
        $sql = "INSERT INTO lore (type, content, tags, is_public) 
                VALUES (?, ?, ?, ?)";
        
        $tagsJson = json_encode($tags);
        $this->executeQuery($sql, [$type, $content, $tagsJson, $isPublic ? 1 : 0]);
        return (int) $this->lastInsertId();
    }

    /**
     * Update lore entry
     */
    public function update(int $id, string $type, string $content, array $tags, bool $isPublic): bool {
        $sql = "UPDATE lore 
                SET type = ?, content = ?, tags = ?, is_public = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $tagsJson = json_encode($tags);
        $stmt = $this->executeQuery($sql, [$type, $content, $tagsJson, $isPublic ? 1 : 0, $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete lore entry
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM lore WHERE id = ?";
        $stmt = $this->executeQuery($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Search lore content
     */
    public function search(string $query): array {
        $sql = "SELECT * FROM lore 
                WHERE content LIKE ? OR tags LIKE ? 
                ORDER BY created_at DESC";
        $searchTerm = "%$query%";
        return $this->fetchAll($sql, [$searchTerm, $searchTerm]);
    }
}
