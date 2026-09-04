<?php
/**
 * RateLimitMiddleware - IP-based rate limiting for public endpoints
 * Tracks usage in the api_usage table
 */
class RateLimitMiddleware {
    private PDO $db;
    private int $limit;
    private int $windowSeconds;
    
    /**
     * Constructor
     * @param int $limit Requests allowed per window
     * @param int $windowSeconds Time window in seconds (default 60 = 1 minute)
     */
    public function __construct(int $limit = 10, int $windowSeconds = 60) {
        $this->db = DatabaseService::getInstance();
        $this->limit = $limit;
        $this->windowSeconds = $windowSeconds;
    }
    
    /**
     * Check if request is allowed
     * @return bool True if allowed, false if rate limit exceeded
     */
    public function check(string $endpoint, string $ip): bool {
        $windowStart = new DateTime();
        $windowStart->modify("-{$this->windowSeconds} seconds");
        
        // Get or create usage record
        $sql = "SELECT request_count, window_start 
                FROM api_usage 
                WHERE endpoint = ? AND ip_address = ?  
                AND window_start >= ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$endpoint, $ip, $windowStart->format('Y-m-d H:i:s')]);
        $record = $stmt->fetch();
        
        if (!$record) {
            // No recent usage, create new record
            $this->createUsageRecord($endpoint, $ip);
            return true;
        }
        
        $count = (int) $record['request_count'];
        
        if ($count >= $this->limit) {
            // Rate limit exceeded
            return false;
        }
        
        // Increment counter
        $this->incrementUsage($endpoint, $ip);
        return true;
    }
    
    /**
     * Get remaining time until next allowed request (in seconds)
     */
    public function getRemainingTime(string $endpoint, string $ip): int {
        $windowStart = new DateTime();
        $windowStart->modify("-{$this->windowSeconds} seconds");
        
        $sql = "SELECT window_start FROM api_usage 
                WHERE endpoint = ? AND ip_address = ? 
                AND window_start >= ?
                ORDER BY window_start DESC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$endpoint, $ip, $windowStart->format('Y-m-d H:i:s')]);
        $record = $stmt->fetch();
        
        if (!$record) {
            return 0;
        }
        
        $recordTime = new DateTime($record['window_start']);
        $resetTime = clone $recordTime;
        $resetTime->modify("+{$this->windowSeconds} seconds");
        
        $now = new DateTime();
        $diff = $resetTime->getTimestamp() - $now->getTimestamp();
        
        return max(0, $diff);
    }
    
    /**
     * Require rate limit check or throw exception
     */
    public function requireCheck(string $endpoint, string $ip): void {
        if (!$this->check($endpoint, $ip)) {
            $wait = $this->getRemainingTime($endpoint, $ip);
            throw new RateLimitException(
                "Rate limit exceeded. Please wait {$wait} seconds.",
                429
            );
        }
    }
    
    /**
     * Create new usage record
     */
    private function createUsageRecord(string $endpoint, string $ip): void {
        $sql = "INSERT INTO api_usage (endpoint, ip_address, user_agent, request_count, window_start) 
                VALUES (?, ?, ?, 1, NOW())";
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$endpoint, $ip, $userAgent]);
    }
    
    /**
     * Increment usage counter
     */
    private function incrementUsage(string $endpoint, string $ip): void {
        $sql = "UPDATE api_usage 
                SET request_count = request_count + 1 
                WHERE endpoint = ? AND ip_address = ? 
                AND window_start >= DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$endpoint, $ip, $this->windowSeconds]);
    }
    
    /**
     * Cleanup old records (should be run periodically via cron)
     */
    public static function cleanup(int $daysToKeep = 7): int {
        $db = DatabaseService::getInstance();
        $sql = "DELETE FROM api_usage WHERE window_start < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$daysToKeep]);
        return $stmt->rowCount();
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIp(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Take first IP if multiple (X-Forwarded-For can have multiple IPs)
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }
        
        return '0.0.0.0';
    }
    /**
     * Static helper to handle rate limiting in one line
     */
    public static function handle(string $endpoint, int $limit = 10, int $windowSeconds = 60): void {
        $instance = new self($limit, $windowSeconds);
        $ip = self::getClientIp();
        
        try {
            $instance->requireCheck($endpoint, $ip);
        } catch (RateLimitException $e) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}

if (!class_exists('RateLimitException')) {
    class RateLimitException extends Exception {}
}
