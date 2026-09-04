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
            $debug = [
                'error' => $e->getMessage(),
                'session_started' => session_status() === PHP_SESSION_ACTIVE,
                'session_token' => $_SESSION['csrf_token'] ?? 'missing',
                'post_token' => $_POST['csrf_token'] ?? 'missing',
                'header_token' => $_SERVER['HTTP_X_CSRF_TOKEN'] ?? 'missing',
                'headers' => function_exists('getallheaders') ? getallheaders() : []
            ];
            file_put_contents(__DIR__ . '/../../debug_csrf.log', print_r($debug, true), FILE_APPEND);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    public static function validate(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        
        if (!$sessionToken) {
            return false;
        }
        
        $requestToken = $_POST['csrf_token'] ?? null;
        
        if (!$requestToken) {
            $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
        
        if (!$requestToken) {
            $json = json_decode(file_get_contents('php://input'), true);
            if (is_array($json) && isset($json['csrf_token'])) {
                $requestToken = $json['csrf_token'];
            }
        }
        
        if (!$requestToken) {
            return false;
        }
        
        return hash_equals($sessionToken, $requestToken);
    }
    
    public static function requireValidToken(): void {
        if (!self::validate()) {
            throw new CsrfException("Invalid or missing CSRF token", 403);
        }
    }
    
    public static function getToken(): string {
        return self::generateToken();
    }
}
