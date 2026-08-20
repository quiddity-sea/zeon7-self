<?php
/**
 * AuthService
 * Handles authentication via DB users table (username/password + Google OAuth).
 * Falls back to ADMIN_PASSWORD env var for emergency access.
 */

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/UserService.php';

class AuthService {

    private UserService $userService;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userService = new UserService();
    }

    /**
     * DB user login — primary method
     */
    public function loginWithCredentials(string $username, string $password): bool {
        $user = $this->userService->findByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            $this->createUserSession($user);
            $this->recordCurrentIp((int) $user['id']);
            return true;
        }

        // Emergency fallback: env ADMIN_PASSWORD
        return $this->legacyEnvLogin($password);
    }

    /**
     * Legacy single-arg login (for backward compat with old login.php that only sent password)
     */
    public function login(string $password): bool {
        return $this->legacyEnvLogin($password);
    }

    /**
     * Google OAuth login — called after callback verifies token
     */
    public function loginWithGoogle(string $googleId, string $email): bool {
        $user = $this->userService->findByGoogleIdOrEmail($googleId, $email);
        if (!$user) {
            return false;
        }

        // Link google_id if matched by email only
        if (empty($user['google_id'])) {
            $this->userService->updateGoogleId((int) $user['id'], $googleId, $email);
        }

        $this->createUserSession($user);
        $this->recordCurrentIp((int) $user['id']);
        return true;
    }

    /**
     * Log out the current user.
     */
    public function logout(): void {
        session_unset();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Check if the current session is authenticated.
     */
    public function isAuthenticated(): bool {
        $timeout = (int) ($_ENV['SESSION_TIMEOUT'] ?? 1800);

        // DB-backed session
        if (!empty($_SESSION['user_id'])) {
            if ($timeout > 0 && isset($_SESSION['login_time'])) {
                if ((time() - (int) $_SESSION['login_time']) > $timeout) {
                    $this->logout();
                    return false;
                }
            }
            $_SESSION['login_time'] = time();
            return true;
        }

        // Legacy env-password session fallback
        if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            if ($timeout > 0 && isset($_SESSION['login_time'])) {
                if ((time() - (int) $_SESSION['login_time']) > $timeout) {
                    $this->logout();
                    return false;
                }
            }
            $_SESSION['login_time'] = time();
            return true;
        }

        return false;
    }

    /**
     * Get the currently authenticated user's session data.
     */
    public function getCurrentUser(): array {
        return [
            'user_id'    => $_SESSION['user_id'] ?? null,
            'username'   => $_SESSION['username'] ?? ($_SESSION['user_name'] ?? 'OPERATOR'),
            'first_name' => $_SESSION['first_name'] ?? null,
            'last_name'  => $_SESSION['last_name'] ?? null,
        ];
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    private function createUserSession(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id']         = $user['id'];
        $_SESSION['username']        = $user['username'];
        $_SESSION['first_name']      = $user['first_name'] ?? null;
        $_SESSION['last_name']       = $user['last_name'] ?? null;
        $_SESSION['login_time']      = time();
        $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
        // Keep legacy flag so old guards still pass
        $_SESSION['admin_logged_in'] = true;
    }

    private function recordCurrentIp(int $userId): void {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        // Take first IP if comma-separated proxy chain
        $ip = trim(explode(',', $ip)[0]);
        $this->userService->recordIp($userId, $ip);
    }

    private function legacyEnvLogin(string $password): bool {
        $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? null;
        if (!$adminPassword) {
            return false;
        }
        if ($password === $adminPassword) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['login_time']      = time();
            $_SESSION['user_name']       = $_ENV['ADMIN_USER'] ?? 'OPERATOR';
            $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
            return true;
        }
        return false;
    }
}
