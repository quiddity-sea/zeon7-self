<?php
/**
 * API: Admin Login
 * Endpoint: POST /api/auth/login.php
 */

require_once __DIR__ . '/../../../src/core/BaseController.php';
require_once __DIR__ . '/../../../src/services/AuthService.php';
require_once __DIR__ . '/../../../src/middleware/RateLimitMiddleware.php';

class LoginController extends BaseController {
    private AuthService $authService;
    
    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
        
        // Rate limit login attempts to prevent brute force
        RateLimitMiddleware::handle('login_attempt', 5, 60); // 5 attempts per minute
    }
    
    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getJsonBody();
        $password = $data['password'] ?? '';
        
        if (empty($password)) {
            $this->sendError('Password is required', 400);
        }
        
        // DEBUG LOGGING
        error_log("Login Attempt: Input Password: '$password'");
        error_log("Env Password: '" . ($_ENV['ADMIN_PASSWORD'] ?? 'NOT SET') . "'");
        
        if ($this->authService->login($password)) {
            $this->sendResponse([
                'success' => true,
                'message' => 'Login successful'
            ]);
        } else {
            // Add a small delay to deter timing attacks
            usleep(200000); // 200ms
            $this->sendError('Invalid password. Debug: Input=[' . $password . '] Env=[' . ($_ENV['ADMIN_PASSWORD'] ?? 'NULL') . ']', 401);
        }
    }
}

$controller = new LoginController();
$controller->handleRequest();
