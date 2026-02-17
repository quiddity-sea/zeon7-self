<?php
/**
 * API: Check Auth Status
 * Endpoint: GET /api/auth/check.php
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/AuthService.php';

class AuthCheckController extends BaseController {
    private AuthService $authService;
    
    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
        }
        
        require_once __DIR__ . '/../../src/middleware/CsrfMiddleware.php';
        
        $isAuthenticated = $this->authService->isAuthenticated();
        $csrfToken = $isAuthenticated ? CsrfMiddleware::getToken() : null;
        
        $this->sendResponse([
            'authenticated' => $isAuthenticated,
            'csrf_token' => $csrfToken
        ]);
    }
}

$controller = new AuthCheckController();
$controller->handleRequest();
