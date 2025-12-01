<?php
/**
 * API: Admin Logout
 * Endpoint: POST /api/auth/logout.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/AuthService.php';

class LogoutController extends BaseController {
    private AuthService $authService;
    
    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $this->authService->logout();
        
        $this->sendResponse([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}

$controller = new LogoutController();
$controller->handleRequest();
