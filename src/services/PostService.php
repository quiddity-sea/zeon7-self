<?php
/**
 * PostService - Manage blog posts and image prompts
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';

class PostService extends BaseService {
    
    /**
     * Create new draft post
     */
    public function createPost(string $title, string $slug, string $content, ?string $sourceUrl = null): int {
        $sql = "INSERT INTO posts (title, slug, content, source_url, status) 
                VALUES (?, ?, ?, ?, 'draft')";
        
        $this->executeQuery($sql, [$title, $slug, $content, $sourceUrl]);
        return (int) $this->lastInsertId();
    }
    
    /**
     * Get post by ID with image prompts
     */
    public function getPostById(int $id): ?array {
        $sql = "SELECT * FROM posts WHERE id = ?";
        $post = $this->fetchOne($sql, [$id]);
        
        if ($post) {
            $post['image_prompts'] = $this->getImagePrompts($id);
        }
        
        return $post;
    }
    
    /**
     * Get published post by slug (for public view)
     */
    public function getPostBySlug(string $slug): ?array {
        $sql = "SELECT * FROM posts WHERE slug = ? AND status = 'published'";
        $post = $this->fetchOne($sql, [$slug]);
        
        if ($post) {
            $post['image_prompts'] = $this->getImagePrompts($post['id']);
        }
        
        return $post;
    }
    
    /**
     * Get all posts (optionally filtered by status)
     */
    public function getAllPosts(string $status = 'all'): array {
        if ($status === 'all') {
            $sql = "SELECT id, title, slug, status, created_at, updated_at, published_at 
                    FROM posts 
                    ORDER BY created_at DESC";
            return $this->fetchAll($sql);
        } else {
            $sql = "SELECT id, title, slug, status, created_at, updated_at, published_at 
                    FROM posts 
                    WHERE status = ?
                    ORDER BY created_at DESC";
            return $this->fetchAll($sql, [$status]);
        }
    }
    
    /**
     * Update post fields
     */
    public function updatePost(int $id, array $data): bool {
        $allowedFields = ['title', 'slug', 'content', 'source_url', 'status'];
        $updates = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE posts SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Publish post (set status and published_at timestamp)
     */
    public function publishPost(int $id): bool {
        $sql = "UPDATE posts 
                SET status = 'published', published_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->executeQuery($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Add image prompts to a post
     */
    public function addImagePrompts(int $postId, array $prompts): void {
        $sql = "INSERT INTO image_prompt (post_id, prompt, prompt_order) 
                VALUES (?, ?, ?)";
        
        $this->beginTransaction();
        
        try {
            foreach ($prompts as $index => $prompt) {
                $this->executeQuery($sql, [$postId, $prompt, $index]);
            }
            
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
    
    /**
     * Get image prompts for a post
     */
    public function getImagePrompts(int $postId): array {
        $sql = "SELECT prompt, prompt_order 
                FROM image_prompt 
                WHERE post_id = ? 
                ORDER BY prompt_order ASC";
        
        return $this->fetchAll($sql, [$postId]);
    }
    
    /**
     * Delete post and all image prompts (CASCADE)
     */
    public function deletePost(int $id): bool {
        $sql = "DELETE FROM posts WHERE id = ?";
        $stmt = $this->executeQuery($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Check if slug already exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM posts WHERE slug = ? AND id != ?";
            $result = $this->fetchOne($sql, [$slug, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM posts WHERE slug = ?";
            $result = $this->fetchOne($sql, [$slug]);
        }
        
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Generate unique slug from title
     */
    public function generateUniqueSlug(string $title): string {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        
        if (!$this->slugExists($slug)) {
            return $slug;
        }
        
        $counter = 1;
        while ($this->slugExists("$slug-$counter")) {
            $counter++;
        }
        
        return "$slug-$counter";
    }
}
