<?php
/**
 * BaseApi — Shared utilities for all Eye data proxy endpoints.
 * Handles rate limiting, curl fetching, caching, and CORS headers.
 */

$selfRoot = file_exists(__DIR__ . '/../../src/config/env.php')
    ? dirname(__DIR__, 2)
    : '/var/www/vhosts/bjorntyrsson.co.uk/self.foreverbox.co.uk';

require_once $selfRoot . '/src/config/env.php';
require_once __DIR__ . '/../src/services/EyeService.php';

class BaseApi {

    /**
     * Set standard API response headers.
     */
    public static function headers(int $cacheMaxAge = 10): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: https://eye.foreverbox.co.uk');
        header('Access-Control-Allow-Headers: X-Eye-Token');
        header("Cache-Control: public, max-age={$cacheMaxAge}");

        // Handle CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /**
     * Check rate limit using the X-Eye-Token header.
     * Exits with 429 if rate exceeded.
     */
    public static function checkRateLimit(): void {
        $token = $_SERVER['HTTP_X_EYE_TOKEN'] ?? '';
        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing X-Eye-Token header']);
            exit;
        }

        $eyeService = new EyeService();
        if (!$eyeService->rateCheck($token)) {
            http_response_code(429);
            echo json_encode(['error' => 'Rate limit exceeded. Try again in 60 seconds.']);
            exit;
        }
    }

    /**
     * Fetch a URL via curl. Returns the response body string or false on failure.
     *
     * @param string      $url      URL to fetch
     * @param string|null $auth     Optional "user:pass" basic auth string
     * @param array       $headers  Optional extra headers
     * @param int         $timeout  Request timeout in seconds
     * @return string|false
     */
    public static function fetch(string $url, ?string $auth = null, array $headers = [], int $timeout = 8) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            // Standard browser UA — many public APIs reject custom bots
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; ForeverBox-TheEye/1.0; +https://eye.foreverbox.co.uk)',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING       => 'gzip, deflate',
        ]);

        if ($auth) {
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            error_log("Eye API fetch failed: {$url} HTTP {$httpCode} Error: {$error}");
            return false;
        }

        return $response;
    }

    /**
     * Simple file-based response cache. Returns cached content if fresh, null otherwise.
     */
    public static function getCache(string $key, int $maxAgeSeconds): ?string {
        $cacheDir = sys_get_temp_dir() . '/eye_cache';
        $cacheFile = $cacheDir . '/' . md5($key) . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $maxAgeSeconds) {
            return file_get_contents($cacheFile);
        }
        return null;
    }

    /**
     * Write response to file cache.
     */
    public static function setCache(string $key, string $content): void {
        $cacheDir = sys_get_temp_dir() . '/eye_cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        file_put_contents($cacheDir . '/' . md5($key) . '.json', $content);
    }

    /**
     * Output JSON and exit.
     */
    public static function respond(string $json): void {
        echo $json;
        exit;
    }

    /**
     * Output error JSON and exit.
     */
    public static function error(string $message, int $code = 500): void {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }
}
