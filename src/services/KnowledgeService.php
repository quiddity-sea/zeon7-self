<?php
/**
 * KnowledgeService - Manage knowledge file uploads, chunking, and retrieval
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';

class KnowledgeService extends BaseService {
    
    /**
     * Upload and store knowledge file metadata
     */
    public function uploadFile(string $filename, string $content, string $hash, int $size, bool $isPublic = false): int {
        $sql = "INSERT INTO knowledge_doc (filename, file_hash, file_size, is_public) 
                VALUES (?, ?, ?, ?)";
        
        $this->executeQuery($sql, [$filename, $hash, $size, $isPublic ? 1 : 0]);
        return (int) $this->lastInsertId();
    }
    
    /**
     * Store file chunks for selective retrieval
     */
    public function chunkFile(int $docId, array $chunks): void {
        $sql = "INSERT INTO knowledge_chunk (doc_id, heading, content, chunk_index) 
                VALUES (?, ?, ?, ?)";
        
        $this->beginTransaction();
        
        try {
            foreach ($chunks as $index => $chunk) {
                $heading = $chunk['heading'] ?? null;
                $content = $chunk['content'] ?? '';
                
                $this->executeQuery($sql, [$docId, $heading, $content, $index]);
            }
            
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    // ... (skipping unchanged)

    public function getChunksByDocId(int $docId): array {
        $sql = "SELECT heading, content, chunk_index 
                FROM knowledge_chunk 
                WHERE doc_id = ? 
                ORDER BY chunk_index ASC";
        return $this->fetchAll($sql, [$docId]);
    }
    
    /**
     * Full-text search across all knowledge chunks
     * Optional: Filter by public access
     */
    public function searchChunks(string $query, bool $publicOnly = false): array {
        $sql = "SELECT kc.*, kd.filename 
                FROM knowledge_chunk kc
                JOIN knowledge_doc kd ON kc.doc_id = kd.id
                WHERE MATCH(kc.content) AGAINST(? IN NATURAL LANGUAGE MODE)";
        
        if ($publicOnly) {
            $sql .= " AND kd.is_public = 1";
        }
        
        $sql .= " ORDER BY kc.updated_at DESC LIMIT 20";
        
        return $this->fetchAll($sql, [$query]);
    }
    
    /**
     * Delete knowledge file and all chunks (CASCADE)
     */
    public function deleteFile(int $id): bool {
        $sql = "DELETE FROM knowledge_doc WHERE id = ?";
        $stmt = $this->executeQuery($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Check if filename already exists
     */
    public function fileExists(string $filename): bool {
        $sql = "SELECT COUNT(*) as count FROM knowledge_doc WHERE filename = ?";
        $result = $this->fetchOne($sql, [$filename]);
        return ($result['count'] ?? 0) > 0;
    }
}
