<?php
/**
 * CsrfMiddleware - CSRF token validation for state-changing operations
 * Applied to all POST/PUT/DELETE endpoints
 */
require_once __DIR__ . '/../core/Exceptions.php';

class CsrfMiddleware {
    /**
     * Handle CSRF check (convenience wrapper)
     */
    public static function handle(): void {
        try {
            self::requireValidToken();
        } catch (CsrfException $e) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Generate CSRF token for current session
     */
    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token from request
     * Checks both POST data and custom header
     */
    public static function validate(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        
        if (!$sessionToken) {
            return false;
        }
        
        // Check POST parameter first
        $requestToken = $_POST['csrf_token'] ?? null;
        
        // Fall back to custom header (for AJAX requests)
        if (!$requestToken) {
            $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
        
        if (!$requestToken) {
            return false;
        }
        
        // Timing-safe comparison
        return hash_equals($sessionToken, $requestToken);
    }
    
    /**
     * Require valid CSRF token or throw exception
     */
    public static function requireValidToken(): void {
        if (!self::validate()) {
            throw new CsrfException("Invalid or missing CSRF token", 403);
        }
    }
    
    /**
     * Get token for inclusion in forms/AJAX headers
     */
    public static function getToken(): string {
        return self::generateToken();
    }
}
