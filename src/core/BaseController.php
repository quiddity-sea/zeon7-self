<?php
/**
 * BaseController - Foundation for all API controllers
 * Provides standardized JSON responses and request handling
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/../config/env.php';

abstract class BaseController {
    protected PDO $db;
    
    public function __construct() {
        // Enable error reporting if debug mode is on
        // Enable error reporting if debug mode is on
        if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
            // ini_set('display_errors', 1); // Corrupts JSON
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(E_ALL);
        }

        $this->db = DatabaseService::getInstance();
    }
    
    /**
     * Send standardized JSON response
     */
    protected function jsonResponse(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Send error response
     */
    protected function sendError(string $message, int $code = 400): void {
        $this->jsonResponse(['error' => $message], $code);
    }

    protected function errorResponse(string $message, int $code = 400): void {
        $this->sendError($message, $code);
    }
    
    /**
     * Send success response
     */
    protected function sendResponse(mixed $data, int $status = 200): void {
        $this->jsonResponse($data, $status);
    }
    
    /**
     * Validate request data against rules
     */
    protected function validateRequest(array $data, array $rules): array {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            // Required field check
            if (($rule['required'] ?? false) && !isset($data[$field])) {
                $errors[$field] = "{$field} is required";
                continue;
            }
            
            // Skip further validation if field is null and not required
            if (!isset($data[$field])) {
                continue;
            }
            
            $value = $data[$field];
            
            // Max length check
            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = "{$field} exceeds maximum length of {$rule['max_length']}";
            }
            
            // Min length check
            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = "{$field} must be at least {$rule['min_length']} characters";
            }
            
            // Pattern check (regex)
            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = "{$field} format is invalid";
            }
            
            // URL check
            if (isset($rule['type']) && $rule['type'] === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[$field] = "{$field} must be a valid URL";
            }
        }
        
        if (!empty($errors)) {
            $this->jsonResponse(ApiResponse::error('Validation failed', 422, $errors), 422);
        }
        
        return $data;
    }
    
    /**
     * Get request body as JSON
     */
    protected function getJsonBody(): array {
        $raw = file_get_contents('php://input');
        if (empty($raw) || trim($raw) === '') {
            return !empty($_POST) ? $_POST : [];
        }

        $data = json_decode($raw, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            if (!empty($_POST)) {
                return $_POST;
            }
            $this->errorResponse('Invalid JSON: ' . json_last_error_msg(), 400);
        }
        
        return $data ?? [];
    }
    
    /**
     * Get query parameters
     */
    protected function getQueryParams(): array {
        return $_GET;
    }
    
    /**
     * Require specific HTTP method
     */
    protected function requireMethod(string $method): void {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            $this->errorResponse('Method not allowed', 405);
        }
    }
}
