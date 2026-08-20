<?php
/**
 * CouncilClient — HTTP client for the Council Library REST API.
 *
 * All Council interactions from i-am-self flow through this service.
 * Council is the authority for memory, knowledge search, conversation
 * storage, agent identity (SOUL), and permissions.
 *
 * Base URL is configured via COUNCIL_API_URL in .env
 * Agent ID is configured via COUNCIL_AGENT_ID in .env (defaults to "zeon7")
 */
class CouncilClient
{
    private string $baseUrl;
    private string $agentId;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim($_ENV['COUNCIL_API_URL'] ?? 'http://127.0.0.1:8080', '/');
        $this->agentId = $_ENV['COUNCIL_AGENT_ID'] ?? 'zeon7';
        $this->apiKey  = $_ENV['COUNCIL_API_KEY'] ?? '';
        $this->timeout = (int)($_ENV['COUNCIL_TIMEOUT'] ?? 10);
    }

    // ─── SOUL / IDENTITY ─────────────────────────────────────

    /**
     * Retrieve the agent's SOUL identity YAML from Council Sanctum.
     * @return array{soul_yaml: string, ...}
     */
    public function getSoul(): array
    {
        return $this->get('/v1/sanctum/soul');
    }

    /**
     * Retrieve user context (profile, relationship notes) from Sanctum.
     * @return array
     */
    public function getUserContext(): array
    {
        return $this->get('/v1/sanctum/user-context');
    }

    // ─── MEMORY (SANCTUM) ────────────────────────────────────

    /**
     * List memory entries from the agent's Sanctum.
     * @param string|null $namespace  Filter by namespace
     * @param int|null    $importance Minimum importance threshold
     * @return array
     */
    public function listMemory(?string $namespace = null, ?int $importance = null): array
    {
        $query = [];
        if ($namespace !== null) $query['namespace'] = $namespace;
        if ($importance !== null) $query['min_importance'] = $importance;
        return $this->get('/v1/sanctum/memory', $query);
    }

    /**
     * Search memory using FULLTEXT keywords.
     * @param string $query  Search terms
     * @return array
     */
    public function searchMemory(string $query): array
    {
        return $this->post('/v1/sanctum/memory/search', ['query' => $query]);
    }

    /**
     * Get a specific memory entry by namespace and key.
     * @param string $namespace
     * @param string $key
     * @return array
     */
    public function getMemory(string $namespace, string $key): array
    {
        return $this->get("/v1/sanctum/memory/{$namespace}/{$key}");
    }

    /**
     * Create or update a memory entry in Sanctum.
     * @param string $namespace
     * @param string $key
     * @param array  $data  {content, importance, metadata}
     * @return array
     */
    public function upsertMemory(string $namespace, string $key, array $data): array
    {
        return $this->put("/v1/sanctum/memory/{$namespace}/{$key}", $data);
    }

    /**
     * Delete a memory entry from Sanctum.
     * @param string $namespace
     * @param string $key
     * @return array
     */
    public function deleteMemory(string $namespace, string $key): array
    {
        return $this->delete("/v1/sanctum/memory/{$namespace}/{$key}");
    }

    // ─── KNOWLEDGE / COMMONS SEARCH ──────────────────────────

    /**
     * Hybrid semantic + fulltext search over the Quiddity Lore Sea.
     * @param string $query   Search terms
     * @param int    $limit   Max results (default 5)
     * @return array
     */
    public function searchCommons(string $query, int $limit = 5): array
    {
        return $this->get('/v1/commons/search', [
            'q'     => $query,
            'limit' => $limit,
        ]);
    }

    /**
     * List indexed files in the Commons.
     * @return array
     */
    public function listCommonsFiles(): array
    {
        return $this->get('/v1/commons/files');
    }

    // ─── CONVERSATIONS ───────────────────────────────────────

    /**
     * Create a new conversation session in Council.
     * @return array{session_id: string}
     */
    public function createConversation(): array
    {
        return $this->post('/v1/sanctum/conversations', []);
    }

    /**
     * Append a message turn to a conversation in Council.
     * @param string $sessionId
     * @param string $role       'user' | 'assistant'
     * @param string $content
     * @param array  $metadata   Optional metadata (model, tokens, provider)
     * @param string|null $ipAddress
     * @param int|null $operatorId
     * @return array
     */
    public function appendMessage(
        string $sessionId,
        string $role,
        string $content,
        array $metadata = [],
        ?string $ipAddress = null,
        ?int $operatorId = null
    ): array {
        return $this->post("/v1/sanctum/conversations/{$sessionId}/messages", [
            'role'        => $role,
            'content'     => $content,
            'metadata'    => $metadata,
            'ip_address'  => $ipAddress,
            'operator_id' => $operatorId,
        ]);
    }

    /**
     * List conversation sessions.
     * @return array
     */
    public function listConversations(): array
    {
        return $this->get('/v1/sanctum/conversations');
    }

    /**
     * Get full conversation transcript.
     * @param string $sessionId
     * @return array
     */
    public function getConversation(string $sessionId): array
    {
        return $this->get("/v1/sanctum/conversations/{$sessionId}");
    }

    // ─── WOLF / TASK DISPATCH ────────────────────────────────

    /**
     * Get wolf worker status.
     * @return array
     */
    public function getWolfStatus(): array
    {
        return $this->get('/v1/sanctum/wolves/status');
    }

    /**
     * Dispatch a task to a wolf worker.
     * @param string $wolfId
     * @param array  $taskData  {prompt, context, ...}
     * @return array
     */
    public function dispatchWolfTask(string $wolfId, array $taskData): array
    {
        return $this->post("/v1/sanctum/wolves/{$wolfId}/task", $taskData);
    }

    // ─── USER-AGENT ASSIGNMENTS ──────────────────────────────

    /**
     * Get all active agent assignments for a user.
     * @param int $userId
     * @return array{assignments: array}
     */
    public function getUserAssignments(int $userId): array
    {
        return $this->get('/v1/registry/assignments', ['user_id' => $userId]);
    }

    /**
     * Create or update a user-agent assignment.
     * @param array $assignment  {user_id, agent_id, template_id, permissions, memory_scope}
     * @return array
     */
    public function upsertAssignment(array $assignment): array
    {
        return $this->put('/v1/registry/assignments', $assignment);
    }

    // ─── AGENT MANIFEST (from foreverbox-data) ───────────────

    /**
     * Load the agent's UI manifest from the local foreverbox-data profiles directory.
     * Falls back gracefully if the file doesn't exist.
     * @param string|null $agentId  Override agent ID
     * @return array
     */
    public function getAgentManifest(?string $agentId = null): array
    {
        $id = $agentId ?? $this->agentId;
        $manifestPath = $_ENV['FOREVERBOX_DATA_PATH'] ?? '/foreverbox_data';
        $file = "{$manifestPath}/profiles/{$id}/ui-manifest.yaml";

        if (!file_exists($file)) {
            return ['agent_id' => $id, 'display_name' => ucfirst($id)];
        }

        if (function_exists('yaml_parse_file')) {
            return yaml_parse_file($file) ?: ['agent_id' => $id];
        }

        $content = file_get_contents($file);
        $manifest = ['agent_id' => $id];
        if (preg_match('/^display_name:\s*["\']?(.+?)["\']?\s*$/m', $content, $m)) {
            $manifest['display_name'] = trim($m[1]);
        }
        if (preg_match('/^tagline:\s*["\']?(.+?)["\']?\s*$/m', $content, $m)) {
            $manifest['tagline'] = trim($m[1]);
        }
        return $manifest;
    }

    // ─── HEALTH CHECK ────────────────────────────────────────

    /**
     * Check Council API availability.
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $result = $this->get('/v1/healthz');
            return ($result['status'] ?? '') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    // ─── HTTP TRANSPORT ──────────────────────────────────────

    private function get(string $path, array $query = []): array
    {
        $url = $this->baseUrl . $path;
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        return $this->request('GET', $url);
    }

    private function post(string $path, array $body): array
    {
        return $this->request('POST', $this->baseUrl . $path, $body);
    }

    private function put(string $path, array $body): array
    {
        return $this->request('PUT', $this->baseUrl . $path, $body);
    }

    private function delete(string $path): array
    {
        return $this->request('DELETE', $this->baseUrl . $path);
    }

    private function request(string $method, string $url, ?array $body = null): array
    {
        $ch = curl_init($url);

        $headers = [
            'Accept: application/json',
            'X-Agent-ID: ' . $this->agentId,
            'X-Request-ID: ' . bin2hex(random_bytes(8)),
        ];

        if (!empty($this->apiKey)) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_CUSTOMREQUEST  => $method,
        ]);

        if ($body !== null && in_array($method, ['POST', 'PUT'])) {
            $json = json_encode($body);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($json);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("Council API error: {$error}");
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400) {
            $msg = $decoded['error'] ?? $decoded['message'] ?? "HTTP {$httpCode}";
            throw new \RuntimeException("Council API [{$method} {$url}]: {$msg}");
        }

        return $decoded ?? [];
    }
}
