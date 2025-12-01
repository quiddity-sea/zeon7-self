<?php
/**
 * AuthService
 * Handles admin authentication via session and environment variable password.
 */

require_once __DIR__ . '/../config/env.php';

class AuthService {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Attempt to log in with the provided password.
     */
    public function login(string $password): bool {
        $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? null;
        
        if (!$adminPassword) {
            error_log('ADMIN_PASSWORD not set in .env');
            return false;
        }
        
        if ($password === $adminPassword) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time'] = time();
            return true;
        }
        
        return false;
    }
    
    /**
     * Log out the current user.
     */
    public function logout(): void {
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['login_time']);
        session_destroy();
    }
    
    /**
     * Check if the user is authenticated.
     */
    public function isAuthenticated(): bool {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}
