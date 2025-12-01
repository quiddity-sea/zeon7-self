<?php
/**
 * API: Get Single Post
 * Endpoint: GET /api/posts/get.php?id=1 OR ?slug=my-post
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/PostService.php';

class PostGetController extends BaseController {
    private PostService $postService;
    
    public function __construct() {
        parent::__construct();
        $this->postService = new PostService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        $id = $_GET['id'] ?? null;
        $slug = $_GET['slug'] ?? null;
        
        try {
            $post = null;
            
            if ($id) {
                $post = $this->postService->getPostById((int)$id);
            } elseif ($slug) {
                $post = $this->postService->getPostBySlug($slug);
            } else {
                $this->sendError('ID or slug required', 400);
            }
            
            if ($post) {
                $this->sendResponse([
                    'success' => true,
                    'post' => $post
                ]);
            } else {
                $this->sendError('Post not found', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new PostGetController();
$controller->handleRequest();
