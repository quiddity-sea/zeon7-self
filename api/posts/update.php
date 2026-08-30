<?php
/**
 * API: Update Post
 * Endpoint: PUT /api/posts/update.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/PostService.php';
require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';

class PostUpdateController extends BaseController {
    private PostService $postService;
    
    public function __construct() {
        parent::__construct();
        $this->postService = new PostService();
        
        // Protect with CSRF
        CsrfMiddleware::handle();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getJsonBody();
        $id = $data['id'] ?? null;
        
        if (!$id) {
            $this->sendError('Post ID is required', 400);
        }
        
        try {
            $success = $this->postService->updatePost($id, $data);
            
            if ($success) {
                $this->sendResponse([
                    'success' => true,
                    'message' => 'Post updated successfully'
                ]);
            } else {
                $this->sendError('Post not found or no changes made', 404);
            }
            
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
}

$controller = new PostUpdateController();
$controller->handleRequest();
