<?php
/**
 * KnowledgeService - Manage knowledge file uploads, chunking, and retrieval (Council Commons & Local fallback)
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';
require_once __DIR__ . '/CouncilClient.php';

class KnowledgeService extends BaseService {
    private CouncilClient $councilClient;
    private bool $useCouncil;

    public function __construct() {
        parent::__construct();
        $this->councilClient = new CouncilClient();
        $this->useCouncil = ($_ENV['KNOWLEDGE_BACKEND'] ?? 'council') === 'council';
    }
    
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

    /**
     * Get all knowledge files with chunk counts (from Council Commons or Local DB)
     */
    public function getAllFiles(): array {
        if ($this->useCouncil) {
            try {
                $res = $this->councilClient->listFiles(['limit' => 100]);
                if (isset($res['files']) && is_array($res['files'])) {
                    $files = [];
                    foreach ($res['files'] as $f) {
                        $files[] = [
                            'id'           => (int)$f['id'],
                            'filename'     => $f['relative_path'] ?? 'file.md',
                            'file_hash'    => $f['content_hash'] ?? '',
                            'file_size'    => (int)($f['file_size_bytes'] ?? 0),
                            'is_public'    => 1,
                            'chunk_count'  => 0, // dynamic count
                            'status'       => $f['indexing_status'] ?? 'indexed',
                            'created_at'   => $f['last_modified'] ?? date('Y-m-d H:i:s'),
                            'updated_at'   => $f['indexed_at'] ?? date('Y-m-d H:i:s')
                        ];
                    }
                    return $files;
                }
            } catch (\Throwable $e) {
                // Fallback to local DB
            }
        }

        $sql = "SELECT kd.*, COUNT(kc.id) as chunk_count 
                FROM knowledge_doc kd 
                LEFT JOIN knowledge_chunk kc ON kd.id = kc.doc_id 
                GROUP BY kd.id 
                ORDER BY kd.created_at DESC";
        return $this->fetchAll($sql);
    }

    public function getChunksByDocId(int $docId): array {
        if ($this->useCouncil) {
            try {
                $res = $this->councilClient->getFileChunks($docId);
                if (isset($res['chunks']) && is_array($res['chunks'])) {
                    $chunks = [];
                    foreach ($res['chunks'] as $c) {
                        $meta = json_decode($c['chunk_metadata'] ?? '[]', true);
                        $chunks[] = [
                            'heading'     => $meta['heading'] ?? ('Chunk #' . ($c['chunk_index'] ?? 0)),
                            'content'     => $c['chunk_text'] ?? '',
                            'chunk_index' => (int)($c['chunk_index'] ?? 0)
                        ];
                    }
                    return $chunks;
                }
            } catch (\Throwable $e) {}
        }

        $sql = "SELECT heading, content, chunk_index 
                FROM knowledge_chunk 
                WHERE doc_id = ? 
                ORDER BY chunk_index ASC";
        return $this->fetchAll($sql, [$docId]);
    }
    
    /**
     * Search across all knowledge chunks (Council Commons Vector/Hybrid or Local FULLTEXT)
     */
    public function searchChunks(string $query, bool $publicOnly = false): array {
        if ($this->useCouncil) {
            try {
                $res = $this->councilClient->searchCommons($query, 10);
                if (isset($res['results']) && is_array($res['results'])) {
                    $results = [];
                    foreach ($res['results'] as $r) {
                        $results[] = [
                            'id'         => $r['id'] ?? 0,
                            'doc_id'     => $r['file_id'] ?? 0,
                            'filename'   => $r['relative_path'] ?? '',
                            'content'    => $r['chunk_text'] ?? '',
                            'heading'    => 'Similarity: ' . round((float)($r['similarity'] ?? 1.0), 3),
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                    }
                    return $results;
                }
            } catch (\Throwable $e) {}
        }

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
