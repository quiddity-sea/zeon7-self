<?php
/**
 * AuthMiddleware
 * Protects routes and pages requiring admin authentication.
 */

require_once __DIR__ . '/../services/AuthService.php';

class AuthMiddleware {
    
    /**
     * Enforce authentication on HTML Admin Pages.
     * Redirects to login.php if session is missing or expired.
     */
    public static function enforcePageAuth(): void {
        $auth = new AuthService();
        if (!$auth->isAuthenticated()) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Handle authentication check for JSON API endpoints.
     * Returns HTTP 401 Unauthorized if not logged in.
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