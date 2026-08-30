<?php
/**
 * API: Create Post
 * Endpoint: POST /api/posts/create.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/PostService.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class PostCreateController extends BaseController {
    private PostService $postService;
    
    public function __construct() {
        parent::__construct();
        $this->postService = new PostService();
        
        // Protect with CSRF
        CsrfMiddleware::handle();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getJsonBody();
        
        if (empty($data['title']) || empty($data['content'])) {
            $this->sendError('Title and content are required', 400);
        }
        
        try {
            // Generate slug if not provided
            $slug = $data['slug'] ?? $this->postService->generateUniqueSlug($data['title']);
            
            $id = $this->postService->createPost(
                $data['title'],
                $slug,
                $data['content'],
                $data['source_url'] ?? null
            );
            
            // Add image prompts if provided
            if (!empty($data['image_prompts']) && is_array($data['image_prompts'])) {
                $this->postService->addImagePrompts($id, $data['image_prompts']);
            }
            
            $this->sendResponse([
                'success' => true,
                'id' => $id,
                'slug' => $slug,
                'message' => 'Post created successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new PostCreateController();
$controller->handleRequest();
