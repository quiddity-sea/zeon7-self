<?php
/**
 * AuthMiddleware
 * Protects routes requiring admin authentication.
 */

require_once __DIR__ . '/../services/AuthService.php';

class AuthMiddleware {
    
    /**
     * Handle authentication check.
     * If not authenticated, returns 401 Unauthorized (for API) or redirects (optional).
     */
    public static function handle(): void {
        $auth = new AuthService();
        
        if (!$auth->isAuthenticated()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
}
