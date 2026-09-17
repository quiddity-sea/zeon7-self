<?php
/**
 * EyeService — Session tracking, rate limiting, and persistence for The Eye.
 * Extends BaseService for PDO access to zeon7_self_dev.
 */

$selfRoot = file_exists(__DIR__ . '/../../../src/core/BaseService.php')
    ? dirname(__DIR__, 3)
    : '/var/www/vhosts/bjorntyrsson.co.uk/self.foreverbox.co.uk';

require_once $selfRoot . '/src/core/BaseService.php';

class EyeService extends BaseService {

    /**
     * Open or resume an Eye session.
     * Returns the session token string.
     */
    public function openSession(?int $userId): string {
        // Check for existing session in PHP session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['eye_token'])) {
            $existing = $this->fetchOne(
                "SELECT id, session_token FROM eye_sessions WHERE session_token = ?",
                [$_SESSION['eye_token']]
            );
            if ($existing) {
                $this->executeQuery(
                    "UPDATE eye_sessions SET last_seen_at = NOW(), user_id = COALESCE(?, user_id) WHERE id = ?",
                    [$userId, $existing['id']]
                );
                return $existing['session_token'];
            }
        }

        // Create new session
        $token = bin2hex(random_bytes(32));
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ip = trim(explode(',', $ip)[0]);
        $ipHash = hash('sha256', $ip . ($_ENV['APP_KEY'] ?? 'eye-salt'));
        $uaHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

        $this->executeQuery(
            "INSERT INTO eye_sessions (session_token, user_id, ip_hash, user_agent_hash) VALUES (?, ?, ?, ?)",
            [$token, $userId, $ipHash, $uaHash]
        );

        $_SESSION['eye_token'] = $token;
        return $token;
    }

    /**
     * Update the layers_used field for a session.
     */
    public function touchSession(string $token, array $layersUsed): void {
        $this->executeQuery(
            "UPDATE eye_sessions SET last_seen_at = NOW(), layers_used = ?, request_count = request_count + 1 WHERE session_token = ?",
            [json_encode($layersUsed), $token]
        );
    }

    /**
     * Rate limit check. Returns true if request is allowed, false if rate exceeded.
     */
    public function rateCheck(string $token): bool {
        $session = $this->fetchOne(
            "SELECT user_id, request_count, last_seen_at FROM eye_sessions WHERE session_token = ?",
            [$token]
        );
        if (!$session) {
            $this->openSession(null);
            return true;
        }

        $isAuth = !empty($session['user_id']);
        $limit = (int) ($_ENV[$isAuth ? 'EYE_RATE_LIMIT_AUTH' : 'EYE_RATE_LIMIT_PUBLIC'] ?? ($isAuth ? 300 : 120));

        // Check if more than 60 seconds have passed since last seen
        $lastSeen = !empty($session['last_seen_at']) ? strtotime($session['last_seen_at']) : 0;
        if (time() - $lastSeen > 60) {
            $this->executeQuery(
                "UPDATE eye_sessions SET request_count = 1, last_seen_at = NOW() WHERE session_token = ?",
                [$token]
            );
            return true;
        }

        if ((int)$session['request_count'] >= $limit) {
            return false;
        }

        $this->executeQuery(
            "UPDATE eye_sessions SET request_count = request_count + 1, last_seen_at = NOW() WHERE session_token = ?",
            [$token]
        );

        return true;
    }

    /**
     * Save an annotation for an authenticated user.
     */
    public function saveAnnotation(int $userId, ?string $agentId, string $label, string $type, array $geometry, ?array $style = null): int {
        $this->executeQuery(
            "INSERT INTO eye_annotations (user_id, agent_id, label, type, geometry, style) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $agentId, $label, $type, json_encode($geometry), $style ? json_encode($style) : null]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get all annotations for a user.
     */
    public function getAnnotations(int $userId): array {
        return $this->fetchAll(
            "SELECT * FROM eye_annotations WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    /**
     * Save a camera view bookmark.
     */
    public function saveView(int $userId, ?string $agentId, string $label, array $camera, array $layers, ?string $sensorStyle): int {
        $this->executeQuery(
            "INSERT INTO eye_saved_views (user_id, agent_id, label, camera, layers, sensor_style) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $agentId, $label, json_encode($camera), json_encode($layers), $sensorStyle]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get all saved views for a user.
     */
    public function getSavedViews(int $userId): array {
        return $this->fetchAll(
            "SELECT * FROM eye_saved_views WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    /**
     * Log an agent query.
     */
    public function logAgentQuery(int $userId, string $agentId, string $sessionToken, string $userMessage, string $agentReply, ?array $eyeCommand, bool $executed): int {
        $this->executeQuery(
            "INSERT INTO eye_agent_queries (user_id, agent_id, session_token, user_message, agent_reply, eye_command, executed) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$userId, $agentId, $sessionToken, $userMessage, $agentReply, $eyeCommand ? json_encode($eyeCommand) : null, $executed ? 1 : 0]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get agent query history for a user.
     */
    public function getAgentHistory(int $userId, int $limit = 50): array {
        return $this->fetchAll(
            "SELECT * FROM eye_agent_queries WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
