<?php
/**
 * API Integration Tests
 * 
 * Usage:
 * 1. Start PHP server: php -S localhost:8000 -t public
 * 2. Run tests: php tests/test_api.php
 */

class TestClient {
    private string $baseUrl;
    private string $cookieFile;

    public function __construct(string $baseUrl = 'http://localhost:8000/api') {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'cookie');
    }

    public function request(string $method, string $endpoint, array $data = [], array $headers = []): array {
        $ch = curl_init();
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        
        $httpHeaders = [];
        foreach ($headers as $key => $value) {
            $httpHeaders[] = "$key: $value";
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $httpHeaders[] = 'Content-Type: application/json';
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $httpHeaders[] = 'Content-Type: application/json';
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $httpCode,
            'body' => json_decode($response, true) ?? $response
        ];
    }

    public function get(string $endpoint, array $headers = []): array {
        return $this->request('GET', $endpoint, [], $headers);
    }

    public function post(string $endpoint, array $data, array $headers = []): array {
        return $this->request('POST', $endpoint, $data, $headers);
    }
    
    public function put(string $endpoint, array $data, array $headers = []): array {
        return $this->request('PUT', $endpoint, $data, $headers);
    }
    
    public function delete(string $endpoint, array $headers = []): array {
        return $this->request('DELETE', $endpoint, [], $headers);
    }
}

function assert_true($condition, $message) {
    if ($condition) {
        echo "✅ PASS: $message\n";
    } else {
        echo "❌ FAIL: $message\n";
    }
}

// --- Run Tests ---

$client = new TestClient();

echo "--- Starting API Tests ---\n";

// 1. Check Auth (Should be false initially)
$res = $client->get('auth/check.php');
assert_true($res['code'] === 200 && ($res['body']['authenticated'] ?? null) === false, 'Initial Auth Check');

// 2. Login
// Note: You need to know the password set in .env. Assuming 'admin123' or similar if not changed.
// I'll try to read .env to get the password for the test.
// Manual .env parsing
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
$password = $env['ADMIN_PASSWORD'] ?? 'password';

$res = $client->post('auth/login.php', ['password' => $password]);
assert_true($res['code'] === 200 && ($res['body']['success'] ?? false) === true, 'Login');

// 3. Check Auth (Should be true)
$res = $client->get('auth/check.php');
$isAuthenticated = ($res['body']['authenticated'] ?? false);
$csrfToken = $res['body']['csrf_token'] ?? null;
assert_true($res['code'] === 200 && $isAuthenticated === true, 'Authenticated Check');
assert_true(!empty($csrfToken), 'CSRF Token received');

// 4. Create Post (Protected)
$headers = [];
if ($csrfToken) {
    $headers['X-CSRF-TOKEN'] = $csrfToken;
}

$res = $client->post('posts/create.php', [
    'title' => 'Test Post ' . time(),
    'content' => 'This is a test post created by automated test.'
], $headers);

assert_true($res['code'] === 200 && ($res['body']['success'] ?? false) === true, 'Create Post with CSRF');

if (($res['body']['success'] ?? false)) {
    $postId = $res['body']['id'];
    echo "   Created Post ID: $postId\n";
    
    // 5. Delete Post
    $res = $client->delete("posts/delete.php?id=$postId", $headers);
    assert_true($res['code'] === 200 && ($res['body']['success'] ?? false) === true, 'Delete Post');
}

echo "\nTests Completed.\n";
