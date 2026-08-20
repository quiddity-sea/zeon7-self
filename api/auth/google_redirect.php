<?php
/**
 * API: Google OAuth Redirect
 * Endpoint: GET /api/auth/google_redirect.php
 * Starts the Google OAuth flow by redirecting to Google's consent screen.
 */

require_once __DIR__ . '/../../src/config/env.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? null;
if (!$clientId) {
    header('Location: /admin/login.php?error=google_not_configured');
    exit;
}

$redirectUri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://')
    . $_SERVER['HTTP_HOST']
    . '/api/auth/google_callback.php';

$state = bin2hex(random_bytes(16));
$_SESSION['google_login_state'] = $state;

$params = [
    'client_id'     => $clientId,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'state'         => $state,
    'access_type'   => 'online',
    'prompt'        => 'select_account',
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
