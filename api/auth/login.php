<?php
/**
 * API: Login
 * Endpoint: POST /api/auth/login.php
 * Accepts: { username, password }
 */

require_once __DIR__ . '/../../src/core/BaseController.php';
require_once __DIR__ . '/../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/middleware/RateLimitMiddleware.php';

class LoginController extends BaseController {
    private AuthService $authService;

    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
        RateLimitMiddleware::handle('login_attempt', 5, 60);
    }

    public function handleRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }

        $data     = $this->getJsonBody();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->sendError('Username and password are required', 400);
        }

        if ($this->authService->loginWithCredentials($username, $password)) {
            $user = $this->authService->getCurrentUser();
            $this->sendResponse([
                'success'    => true,
                'message'    => 'Login successful',
                'username'   => $user['username'],
                'first_name' => $user['first_name'],
            ]);
        } else {
            usleep(300000); // 300ms delay to deter timing attacks
            $this->sendError('ACCESS DENIED: Invalid credentials', 401);
        }
    }
}

$controller = new LoginController();
$controller->handleRequest();
