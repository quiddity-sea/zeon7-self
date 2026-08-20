<?php
/**
 * UserService - CRUD operations for the users table
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';

class UserService extends BaseService {

    /**
     * Find a user by username
     */
    public function findByUsername(string $username): ?array {
        $sql = 'SELECT * FROM users WHERE username = ? LIMIT 1';
        return $this->fetchOne($sql, [$username]);
    }

    /**
     * Find a user by Google ID
     */
    public function findByGoogleId(string $googleId): ?array {
        $sql = 'SELECT * FROM users WHERE google_id = ? LIMIT 1';
        return $this->fetchOne($sql, [$googleId]);
    }

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?array {
        $sql = 'SELECT * FROM users WHERE email = ? LIMIT 1';
        return $this->fetchOne($sql, [$email]);
    }

    /**
     * Find a user by Google ID or email (for OAuth linking)
     */
    public function findByGoogleIdOrEmail(string $googleId, string $email): ?array {
        $sql = 'SELECT * FROM users WHERE google_id = ? OR email = ? LIMIT 1';
        return $this->fetchOne($sql, [$googleId, $email]);
    }

    /**
     * Find a user by ID
     */
    public function findById(int $id): ?array {
        $sql = 'SELECT * FROM users WHERE id = ? LIMIT 1';
        return $this->fetchOne($sql, [$id]);
    }

    /**
     * Find a user by IP address
     */
    public function findByIp(string $ip): ?array {
        $sql = 'SELECT * FROM users WHERE last_10_ips LIKE ? ORDER BY created_at DESC LIMIT 1';
        return $this->fetchOne($sql, ['%"ip":"' . $ip . '"%']);
    }

    /**
     * Find a user by username or first name (case-insensitive)
     */
    public function findByName(string $name): ?array {
        $sql = 'SELECT * FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(first_name) = LOWER(?) ORDER BY is_prime_user DESC, created_at ASC LIMIT 1';
        return $this->fetchOne($sql, [$name, $name]);
    }

    /**
     * Create a new user
     */
    public function create(
        string $username,
        string $password,
        ?string $email = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $location = null,
        bool $isPrimeUser = false
    ): int {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $sql = 'INSERT INTO users (username, password_hash, email, first_name, last_name, location, is_prime_user)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        $this->executeQuery($sql, [
            $username,
            $passwordHash,
            $email,
            $firstName,
            $lastName,
            $location,
            $isPrimeUser ? 1 : 0,
        ]);
        return (int) $this->lastInsertId();
    }

    /**
     * Update an existing user
     */
    public function updateUser(int $id, array $data): bool {
        $fields = [];
        $params = [];

        if (isset($data['username'])) {
            $fields[] = 'username = ?';
            $params[] = $data['username'];
        }
        if (array_key_exists('email', $data)) {
            $fields[] = 'email = ?';
            $params[] = $data['email'] !== '' ? $data['email'] : null;
        }
        if (array_key_exists('first_name', $data)) {
            $fields[] = 'first_name = ?';
            $params[] = $data['first_name'] !== '' ? $data['first_name'] : null;
        }
        if (array_key_exists('last_name', $data)) {
            $fields[] = 'last_name = ?';
            $params[] = $data['last_name'] !== '' ? $data['last_name'] : null;
        }
        if (array_key_exists('location', $data)) {
            $fields[] = 'location = ?';
            $params[] = $data['location'] !== '' ? $data['location'] : null;
        }
        if (isset($data['is_prime_user'])) {
            $fields[] = 'is_prime_user = ?';
            $params[] = $data['is_prime_user'] ? 1 : 0;
        }
        if (!empty($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete a user by ID
     */
    public function deleteUser(int $id): bool {
        $sql = 'DELETE FROM users WHERE id = ?';
        $stmt = $this->executeQuery($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Link a Google account to an existing user
     */
    public function updateGoogleId(int $userId, string $googleId, string $email): bool {
        $sql = 'UPDATE users SET google_id = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $stmt = $this->executeQuery($sql, [$googleId, $email, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Record an IP address in the rolling last-10-IPs log
     */
    public function recordIp(int $userId, string $ip): void {
        $user = $this->findById($userId);
        if (!$user) return;

        $ips = json_decode($user['last_10_ips'] ?? '[]', true);
        if (!is_array($ips)) {
            $ips = [];
        }

        // Prepend new IP, keep max 10
        array_unshift($ips, [
            'ip'   => $ip,
            'time' => date('Y-m-d H:i:s'),
        ]);
        $ips = array_slice($ips, 0, 10);

        $sql = 'UPDATE users SET last_10_ips = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $this->executeQuery($sql, [json_encode($ips), $userId]);
    }

    /**
     * Remove a specific IP address from a user's IP history
     */
    public function removeIp(int $userId, string $ipToRemove): bool {
        $user = $this->findById($userId);
        if (!$user) return false;

        $ips = json_decode($user['last_10_ips'] ?? '[]', true);
        if (!is_array($ips)) {
            $ips = [];
        }

        $filtered = array_values(array_filter($ips, function($entry) use ($ipToRemove) {
            $entryIp = is_array($entry) ? ($entry['ip'] ?? '') : (string)$entry;
            return $entryIp !== $ipToRemove;
        }));

        $sql = 'UPDATE users SET last_10_ips = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $this->executeQuery($sql, [json_encode($filtered), $userId]);
        return true;
    }

    /**
     * Clear all recorded IPs for a user
     */
    public function clearAllIps(int $userId): bool {
        $sql = 'UPDATE users SET last_10_ips = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?';
        $this->executeQuery($sql, [json_encode([]), $userId]);
        return true;
    }

    /**
     * Get all users (admin use)
     */
    public function getAll(): array {
        $sql = 'SELECT id, username, email, first_name, last_name, location, is_prime_user, last_10_ips, created_at, updated_at FROM users ORDER BY created_at DESC';
        return $this->fetchAll($sql);
    }
}
