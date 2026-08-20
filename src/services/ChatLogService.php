<?php
/**
 * ChatLogService - Persist conversation turns to chat_logs
 */

require_once __DIR__ . '/../core/BaseService.php';

class ChatLogService extends BaseService {

    /**
     * Log a single message turn (user or assistant).
     */
    public function log(
        string $sessionId,
        string $role,
        string $content,
        ?int $userId = null,
        ?string $provider = null,
        ?string $model = null,
        ?bool $think = null,
        ?int $tokens = null,
        ?string $ip = null,
        array $meta = []
    ): int {
        $sql = 'INSERT INTO chat_logs
                    (session_id, user_id, role, content, metadata, provider, model, think, tokens_used, ip_address)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $metadata = !empty($meta) ? json_encode($meta) : null;
        $thinkInt = $think !== null ? ($think ? 1 : 0) : null;

        $this->executeQuery($sql, [
            $sessionId,
            $userId,
            $role,
            $content,
            $metadata,
            $provider,
            $model,
            $thinkInt,
            $tokens,
            $ip,
        ]);

        return (int) $this->lastInsertId();
    }

    /**
     * Get all messages for a given session, in chronological order.
     */
    public function getSession(string $sessionId): array {
        $sql = 'SELECT * FROM chat_logs WHERE session_id = ? ORDER BY created_at ASC';
        return $this->fetchAll($sql, [$sessionId]);
    }

    /**
     * Get associated user ID for an active session
     */
    public function getUserIdBySession(string $sessionId): ?int {
        $sql = 'SELECT user_id FROM chat_logs WHERE session_id = ? AND user_id IS NOT NULL ORDER BY id DESC LIMIT 1';
        $row = $this->fetchOne($sql, [$sessionId]);
        return !empty($row['user_id']) ? (int) $row['user_id'] : null;
    }

    /**
     * Get recent sessions summary for admin analysis.
     */
    public function getRecentSessions(int $limit = 50): array {
        $sql = 'SELECT
                    session_id,
                    user_id,
                    ip_address,
                    provider,
                    model,
                    COUNT(*) AS total_messages,
                    SUM(CASE WHEN role = "user" THEN 1 ELSE 0 END) AS user_turns,
                    SUM(tokens_used) AS total_tokens,
                    MIN(created_at) AS started_at,
                    MAX(created_at) AS last_active
                FROM chat_logs
                GROUP BY session_id, user_id, ip_address, provider, model
                ORDER BY last_active DESC
                LIMIT ?';
        return $this->fetchAll($sql, [$limit]);
    }
}
