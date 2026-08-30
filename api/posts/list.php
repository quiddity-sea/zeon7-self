<?php
/**
 * API: List Posts
 * Endpoint: GET /api/posts/list.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/PostService.php';

class PostListController extends BaseController {
    private PostService $postService;
    
    public function __construct() {
        parent::__construct();
        $this->postService = new PostService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        $status = $_GET['status'] ?? 'all';
        
        try {
            $posts = $this->postService->getAllPosts($status);
            
            $this->sendResponse([
                'success' => true,
                'count' => count($posts),
                'posts' => $posts
            ]);
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new PostListController();
$controller->handleRequest();
