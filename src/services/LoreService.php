<?php
/**
 * LoreService - Manage mutable memory (Council Sanctum & Local fallback)
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';
require_once __DIR__ . '/CouncilClient.php';
require_once __DIR__ . '/AgentContextService.php';

class LoreService extends BaseService {
    private CouncilClient $councilClient;
    private AgentContextService $agentCtx;
    private bool $useCouncil;

    public function __construct() {
        parent::__construct();
        $this->councilClient = new CouncilClient();
        $this->agentCtx = new AgentContextService();
        $this->useCouncil = ($_ENV['MEMORY_BACKEND'] ?? 'council') === 'council';
    }
    
    /**
     * Get all lore entries
     */
    public function getAll(): array {
        if ($this->useCouncil) {
            try {
                $client = $this->councilClient->withAgent($this->agentCtx->getAgentId());
                $res = $client->listMemory();
                if (isset($res['results']) && is_array($res['results'])) {
                    $lore = [];
                    foreach ($res['results'] as $idx => $r) {
                        $lore[] = [
                            'id'         => $idx + 1,
                            'type'       => $r['namespace'] ?? 'general',
                            'key_name'   => $r['key_name'] ?? 'fact',
                            'content'    => $r['content_text'] ?? '',
                            'tags'       => json_decode($r['tags'] ?? '[]', true) ?: [],
                            'importance' => (int)($r['importance'] ?? 70),
                            'is_public'  => 1,
                            'created_at' => $r['created_at'] ?? date('Y-m-d H:i:s'),
                            'updated_at' => $r['updated_at'] ?? date('Y-m-d H:i:s')
                        ];
                    }
                    return $lore;
                }
            } catch (\Throwable $e) {
                // Fall back to local DB if Council request fails
            }
        }

        $sql = "SELECT id, type, content, tags, is_public, created_at, updated_at 
                FROM lore 
                ORDER BY created_at DESC";
        return $this->fetchAll($sql);
    }

    /**
     * Get only public lore entries
     */
    public function getPublic(): array {
        return $this->getAll();
    }
    
    /**
     * Get single lore entry by ID or Key
     */
    public function getById(int $id): ?array {
        $all = $this->getAll();
        foreach ($all as $item) {
            if ($item['id'] === $id) return $item;
        }
        return null;
    }
    
    /**
     * Create new lore entry
     */
    public function create(string $type, string $content, array $tags = [], bool $isPublic = false): int {
        if ($this->useCouncil) {
            try {
                $key = preg_replace('/[^a-z0-9_-]/i', '_', substr($content, 0, 32)) ?: 'fact_' . bin2hex(random_bytes(4));
                $client = $this->councilClient->withAgent($this->agentCtx->getAgentId());
                $client->upsertMemory($type ?: 'general', $key, [
                    'content'     => $content,
                    'importance'  => 75,
                    'tags'        => $tags,
                    'source_type' => 'self_admin'
                ]);
            } catch (\Throwable $e) {}
        }

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
        if ($this->useCouncil) {
            try {
                $existing = $this->getById($id);
                $key = $existing['key_name'] ?? ('fact_' . $id);
                $client = $this->councilClient->withAgent($this->agentCtx->getAgentId());
                $client->upsertMemory($type ?: 'general', $key, [
                    'content'     => $content,
                    'importance'  => 75,
                    'tags'        => $tags,
                    'source_type' => 'self_admin'
                ]);
            } catch (\Throwable $e) {}
        }

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
        if ($this->useCouncil) {
            try {
                $existing = $this->getById($id);
                if ($existing && !empty($existing['key_name'])) {
                    $client = $this->councilClient->withAgent($this->agentCtx->getAgentId());
                    $client->deleteMemory($existing['type'] ?? 'general', $existing['key_name']);
                }
            } catch (\Throwable $e) {}
        }

        $sql = "DELETE FROM lore WHERE id = ?";
        $stmt = $this->executeQuery($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Search lore content
     */
    public function search(string $query): array {
        if ($this->useCouncil) {
            try {
                $client = $this->councilClient->withAgent($this->agentCtx->getAgentId());
                $res = $client->searchMemory($query);
                if (isset($res['results']) && is_array($res['results'])) {
                    $lore = [];
                    foreach ($res['results'] as $idx => $r) {
                        $lore[] = [
                            'id'         => $idx + 1,
                            'type'       => $r['namespace'] ?? 'general',
                            'key_name'   => $r['key_name'] ?? 'fact',
                            'content'    => $r['content_text'] ?? '',
                            'tags'       => json_decode($r['tags'] ?? '[]', true) ?: [],
                            'importance' => (int)($r['importance'] ?? 70),
                            'is_public'  => 1,
                            'created_at' => $r['created_at'] ?? date('Y-m-d H:i:s'),
                            'updated_at' => $r['updated_at'] ?? date('Y-m-d H:i:s')
                        ];
                    }
                    return $lore;
                }
            } catch (\Throwable $e) {}
        }

        $sql = "SELECT * FROM lore 
                WHERE content LIKE ? OR tags LIKE ? 
                ORDER BY created_at DESC";
        $searchTerm = "%$query%";
        return $this->fetchAll($sql, [$searchTerm, $searchTerm]);
    }
}
