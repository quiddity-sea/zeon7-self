<?php
/**
 * InstructionService - Manage multi-agent persona instructions & heads
 */

require_once __DIR__ . '/../core/BaseService.php';
require_once __DIR__ . '/../core/Exceptions.php';
require_once __DIR__ . '/AgentContextService.php';

class InstructionService extends BaseService {
    private AgentContextService $agentCtx;

    public function __construct(?AgentContextService $agentCtx = null) {
        parent::__construct();
        $this->agentCtx = $agentCtx ?? new AgentContextService();
    }

    /**
     * Get current active instructions for an agent.
     */
    public function getCurrentVersion(?string $agentId = null): ?array {
        $agent = $agentId ?? $this->agentCtx->getAgentId();

        $sql = "SELECT * FROM system_instructions 
                WHERE agent_id = ? AND is_active = 1
                ORDER BY created_at DESC 
                LIMIT 1";
        $row = $this->fetchOne($sql, [$agent]);

        if ($row) {
            return [
                'version'    => (int)$row['id'],
                'agent_id'   => $row['agent_id'],
                'component'  => $row['component'] ?? 'custom',
                'content'    => $row['content'],
                'created_at' => $row['created_at'],
                'created_by' => 'operator'
            ];
        }

        // Fallback: load baseline head for this agent
        $baseline = $this->getDefaultHeadContent($agent);
        if ($baseline) {
            return [
                'version'    => 0,
                'agent_id'   => $agent,
                'component'  => 'baseline',
                'content'    => $baseline,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => 'system'
            ];
        }

        return null;
    }

    public function getCurrentContent(?string $agentId = null): string {
        $v = $this->getCurrentVersion($agentId);
        return $v['content'] ?? '';
    }

    /**
     * Get available persona heads and components for an agent.
     * Fetches live dynamic components from Council Registry soul_components when available.
     */
    public function getAgentComponents(?string $agentId = null): array {
        $agent = $agentId ?? $this->agentCtx->getAgentId();
        $components = [];

        // 1. Try Council Registry soul_components
        try {
            require_once __DIR__ . '/CouncilClient.php';
            $client = new CouncilClient();
            $resp = $client->getHeads($agent);
            if (!empty($resp['heads'])) {
                foreach ($resp['heads'] as $h) {
                    $headId = (int)$h['id'];
                    $fullHead = $client->getHead($headId);
                    $key = $h['component_key'];
                    $filter = $h['provider_filter'] ? " [{$h['provider_filter']}]" : "";
                    $components[$key] = [
                        'id'          => $headId,
                        'key'         => $key,
                        'name'        => ($h['section_description'] ?: ucfirst($key)) . $filter,
                        'description' => $h['section_description'] ?? '',
                        'order'       => (int)$h['section_order'],
                        'provider'    => $h['provider_filter'],
                        'agent_slug'  => $h['agent_slug'],
                        'content'     => $fullHead['head']['section_content'] ?? ''
                    ];
                }
                if (!empty($components)) {
                    return $components;
                }
            }
        } catch (\Throwable $e) {
            // Council unavailable, fallback to profile files
        }

        // 2. Fallback to local profile files
        $dataPath = $_ENV['FOREVERBOX_DATA_PATH'] ?? '/foreverbox_data';
        if ($agent === 'zeon7') {
            $ftnFile = __DIR__ . '/../../instructions/Restart/current-instructions.md';
            if (file_exists($ftnFile)) {
                $components['from_the_noise'] = [
                    'id'          => 1,
                    'key'         => 'from_the_noise',
                    'name'        => 'From the Noise (Curator & Journalist)',
                    'description' => 'The complete CRISPE prompt: Media production, "Signal vs Noise", 8-part content suite, and News Desk sourcing.',
                    'content'     => file_get_contents($ftnFile)
                ];
            }
            $coderFile = "{$dataPath}/profiles/zeon7/SOUL.md";
            if (file_exists($coderFile)) {
                $components['coder'] = [
                    'id'          => 2,
                    'key'         => 'coder',
                    'name'        => 'Coder Variant (System Architect)',
                    'description' => 'Clean architecture, code patching, implementation diffs, zero em-dashes.',
                    'content'     => file_get_contents($coderFile)
                ];
            }
        } else {
            $soulFile = "{$dataPath}/profiles/{$agent}/SOUL.md";
            if (file_exists($soulFile)) {
                $components[$agent] = [
                    'id'          => 1,
                    'key'         => $agent,
                    'name'        => ucfirst($agent) . ' Default Head',
                    'description' => ucfirst($agent) . ' SOUL baseline persona',
                    'content'     => file_get_contents($soulFile)
                ];
            }
        }

        return $components;
    }

    /**
     * Create and activate a new version of instructions for an agent.
     */
    public function createVersion(string $content, ?string $agentId = null, string $type = 'core', string $component = 'custom'): int {
        $agent = $agentId ?? $this->agentCtx->getAgentId();

        $this->beginTransaction();
        try {
            // Deactivate existing active prompt for this agent
            $sqlDeactivate = "UPDATE system_instructions SET is_active = 0 WHERE agent_id = ?";
            $this->executeQuery($sqlDeactivate, [$agent]);

            // Insert new active prompt
            $sqlInsert = "INSERT INTO system_instructions (agent_id, component, type, content, is_active) 
                          VALUES (?, ?, ?, ?, 1)";
            $this->executeQuery($sqlInsert, [$agent, $component, $type, $content]);
            $versionId = (int)$this->lastInsertId();

            $this->commit();
            return $versionId;
        } catch (\Throwable $e) {
            $this->rollback();
            throw new AppException("Failed to create instruction version: " . $e->getMessage());
        }
    }

    /**
     * Get version history for an agent.
     */
    public function getVersions(?string $agentId = null, int $limit = 20): array {
        $agent = $agentId ?? $this->agentCtx->getAgentId();
        $sql = "SELECT id, agent_id, component, type, is_active, content, created_at 
                FROM system_instructions 
                WHERE agent_id = ?
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->fetchAll($sql, [$agent, $limit]);
    }

    private function getDefaultHeadContent(string $agentId): string {
        $components = $this->getAgentComponents($agentId);
        if (!empty($components)) {
            $first = reset($components);
            return $first['content'] ?? '';
        }
        return '';
    }
}
