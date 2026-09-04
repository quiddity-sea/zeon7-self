<?php
/**
 * ApiResponse - Standardized API response formatting
 */
class ApiResponse {
    /**
     * Success response with optional data and message
     */
    public static function success(mixed $data = null, ?string $message = null): array {
        $response = ['success' => true];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }
    
    /**
     * Error response with message, code, and optional details
     */
    public static function error(string $message, int $code = 400, ?array $details = null): array {
        $response = [
            'success' => false,
            'error' => $message,
            'code' => $code
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        return $response;
    }
    
    /**
     * Paginated response for list endpoints
     */
    public static function paginated(array $items, int $total, int $page, int $perPage): array {
        return [
            'success' => true,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
                'has_more' => $page < ceil($total / $perPage)
            ]
        ];
    }
}
