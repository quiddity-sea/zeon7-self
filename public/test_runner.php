<?php
/**
 * Browser-based Test Runner
 * Access via: http://localhost:8000/test_runner.php
 */

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Helper to assert conditions
function assert_true($condition, $message) {
    $status = $condition ? 'PASS' : 'FAIL';
    $color = $condition ? 'green' : 'red';
    $icon = $condition ? '✅' : '❌';
    
    echo "<div style='margin-bottom: 10px; padding: 10px; border-left: 5px solid $color; background: #f9f9f9;'>";
    echo "<strong>$icon $status:</strong> $message";
    echo "</div>";
    
    return $condition;
}

// Helper to make internal requests
function make_request($method, $url, $data = [], $headers = [], $cookies = []) {
    $ch = curl_init();
    
    // If url starts with /, prepend current scheme and host
    if (str_starts_with($url, '/')) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $url = "$protocol://$host" . $url;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Handle Cookies
    if (!empty($cookies)) {
        $cookieStr = [];
        foreach ($cookies as $k => $v) {
            $cookieStr[] = "$k=$v";
        }
        curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookieStr));
    }
    
    // Handle Headers
    $httpHeaders = [];
    foreach ($headers as $k => $v) {
        $httpHeaders[] = "$k: $v";
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $httpHeaders[] = 'Content-Type: application/json';
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
    curl_setopt($ch, CURLOPT_HEADER, true); // Get headers to capture cookies
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headerStr = substr($response, 0, $headerSize);
    $bodyStr = substr($response, $headerSize);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    // Extract cookies from response headers
    $newCookies = [];
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headerStr, $matches);
    foreach($matches[1] as $item) {
        parse_str($item, $cookie);
        $newCookies = array_merge($newCookies, $cookie);
    }
    
    return [
        'code' => $httpCode,
        'body' => json_decode($bodyStr, true) ?? $bodyStr,
        'cookies' => array_merge($cookies, $newCookies)
    ];
}

// Get Admin Password from .env
$env = [];
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
}
$adminPassword = $env['ADMIN_PASSWORD'] ?? 'password';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 API Tests</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 2rem auto; line-height: 1.5; }
        h1 { border-bottom: 2px solid #eee; padding-bottom: 0.5rem; }
        .summary { margin-top: 2rem; padding: 1rem; background: #eee; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>⚡ Zeon7 API Test Runner</h1>
    
    <?php
    $cookies = [];
    $csrfToken = null;
    
    echo "<h3>1. Authentication Flow</h3>";
    
    // Test 1: Initial Auth Check
    $res = make_request('GET', '/api/auth/check.php', [], [], $cookies);
    $cookies = $res['cookies']; // Update cookies
    assert_true($res['code'] === 200 && ($res['body']['authenticated'] ?? null) === false, 'Initial Auth Check (Should be false)');
    
    // Test 2: Login
    $res = make_request('POST', '/api/auth/login.php', ['password' => $adminPassword], [], $cookies);
    $cookies = $res['cookies'];
    $success = ($res['body']['success'] ?? false) === true;
    assert_true($res['code'] === 200 && $success, 'Login with correct password');
    if (!$success) {
        echo "<div style='color: red; margin-left: 20px;'>Error Response: " . json_encode($res['body']) . "</div>";
    }
    
    // Test 3: Authenticated Check
    $res = make_request('GET', '/api/auth/check.php', [], [], $cookies);
    $isAuthenticated = ($res['body']['authenticated'] ?? false);
    $csrfToken = $res['body']['csrf_token'] ?? null;
    assert_true($res['code'] === 200 && $isAuthenticated === true, 'Authenticated Check (Should be true)');
    assert_true(!empty($csrfToken), 'CSRF Token received');
    
    echo "<h3>2. Post Management</h3>";
    
    if ($csrfToken) {
        $headers = ['X-CSRF-TOKEN' => $csrfToken];
        
        // Test 4: Create Post
        $res = make_request('POST', '/api/posts/create.php', [
            'title' => 'Browser Test Post ' . time(),
            'content' => 'Created via browser test runner.'
        ], $headers, $cookies);
        
        assert_true($res['code'] === 200 && ($res['body']['success'] ?? false) === true, 'Create Post');
        
        if (($res['body']['success'] ?? false)) {
            $postId = $res['body']['id'];
            
            // Test 5: Delete Post
            $res = make_request('DELETE', "/api/posts/delete.php?id=$postId", [], $headers, $cookies);
            assert_true($res['code'] === 200 && ($res['body']['success'] ?? false) === true, 'Delete Post');
        }
    } else {
        echo "<div style='color: red;'>Skipping Post tests due to missing CSRF token.</div>";
    }
    
    ?>
    
    <div class="summary">
        <strong>Tests Completed.</strong>
    </div>
</body>
</html>
