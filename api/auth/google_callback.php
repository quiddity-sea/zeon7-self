<?php
/**
 * API: Google OAuth Callback
 * Endpoint: GET /api/auth/google_callback.php
 */

require_once __DIR__ . '/../../src/config/env.php';
require_once __DIR__ . '/../../src/services/AuthService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$code         = $_GET['code'] ?? null;
$state        = $_GET['state'] ?? null;
$sessionState = $_SESSION['google_login_state'] ?? null;
$error        = $_GET['error'] ?? null;

// User cancelled or error from Google
if ($error) {
    header('Location: /admin/login.php?error=google_cancelled');
    exit;
}

// CSRF state mismatch
if (!$code || !$state || $state !== $sessionState) {
    header('Location: /admin/login.php?error=invalid_state');
    exit;
}
unset($_SESSION['google_login_state']);

$clientId     = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
$redirectUri  = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://')
    . $_SERVER['HTTP_HOST']
    . '/api/auth/google_callback.php';

// Exchange code for access token
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'code'          => $code,
        'redirect_uri'  => $redirectUri,
        'grant_type'    => 'authorization_code',
    ]),
]);
$tokenResponse = curl_exec($ch);
curl_close($ch);
$tokenData = json_decode($tokenResponse, true);

if (empty($tokenData['access_token'])) {
    header('Location: /admin/login.php?error=token_failed');
    exit;
}

// Fetch Google user profile
$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $tokenData['access_token']],
]);
$profileResponse = curl_exec($ch);
curl_close($ch);
$googleUser = json_decode($profileResponse, true);

if (empty($googleUser['email'])) {
    header('Location: /admin/login.php?error=no_email');
    exit;
}

$authService = new AuthService();
$loggedIn    = $authService->loginWithGoogle($googleUser['id'], $googleUser['email']);

if ($loggedIn) {
    header('Location: /admin/index.php');
} else {
    // Google account not linked to any user — strict mode, no auto-provisioning
    header('Location: /admin/login.php?error=link_required');
}
exit;
