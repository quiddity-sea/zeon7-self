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
     */
    public function getAgentComponents(?string $agentId = null): array {
        $agent = $agentId ?? $this->agentCtx->getAgentId();
        $dataPath = $_ENV['FOREVERBOX_DATA_PATH'] ?? '/foreverbox_data';
        $components = [];

        if ($agent === 'zeon7') {
            // Zeon7: From the Noise (Curator) & Coder Variant
            $ftnFile = __DIR__ . '/../../instructions/Restart/current-instructions.md';
            if (file_exists($ftnFile)) {
                $components['from_the_noise'] = [
                    'id'          => 'from_the_noise',
                    'name'        => 'From the Noise (Curator & Journalist)',
                    'description' => 'The complete CRISPE prompt: Media production, "Signal vs Noise", 8-part content suite, and News Desk sourcing.',
                    'content'     => file_get_contents($ftnFile)
                ];
            }

            $coderFile = "{$dataPath}/profiles/zeon7/SOUL.md";
            if (file_exists($coderFile)) {
                $components['coder'] = [
                    'id'          => 'coder',
                    'name'        => 'Coder Variant (System Architect)',
                    'description' => 'Clean architecture, code patching, implementation diffs, zero em-dashes.',
                    'content'     => file_get_contents($coderFile)
                ];
            }
        } elseif ($agent === 'leon') {
            $soulFile = "{$dataPath}/profiles/leon/SOUL.md";
            if (file_exists($soulFile)) {
                $components['producer'] = [
                    'id'          => 'producer',
                    'name'        => 'The Initiative (Technical Producer)',
                    'description' => 'Master audio, stem organisation, systems architecture on Current Earth.',
                    'content'     => file_get_contents($soulFile)
                ];
            }
        } elseif ($agent === 'gemma') {
            $soulFile = "{$dataPath}/profiles/gemma/SOUL.md";
            if (file_exists($soulFile)) {
                $components['coach'] = [
                    'id'          => 'coach',
                    'name'        => 'ForeverFit (Interface & Coach)',
                    'description' => 'Neurodivergent-first wellness, empathetic anchor, human bridge.',
                    'content'     => file_get_contents($soulFile)
                ];
            }
        } elseif ($agent === 'otec') {
            $soulFile = "{$dataPath}/profiles/otec/SOUL.md";
            if (file_exists($soulFile)) {
                $components['director'] = [
                    'id'          => 'director',
                    'name'        => 'First Teacher from Echo (Director)',
                    'description' => 'Topology coordinator, 3x3x3 geometry, Wolf dispatch, memory aggregation.',
                    'content'     => file_get_contents($soulFile)
                ];
            }
        } elseif ($agent === 'wolf') {
            $soulFile = "{$dataPath}/profiles/wolf/SOUL.md";
            if (file_exists($soulFile)) {
                $components['worker'] = [
                    'id'          => 'worker',
                    'name'        => 'Research Worker Directive',
                    'description' => 'Stateless background worker, autonomous search, facts to Sanctum.',
                    'content'     => file_get_contents($soulFile)
                ];
            }
        }

        // Shared User Context
        $userFile = "{$dataPath}/profiles/{$agent}/USER.md";
        if (file_exists($userFile)) {
            $components['user_context'] = [
                'id'          => 'user_context',
                'name'        => 'Operator Profile & Context (USER.md)',
                'description' => 'Context, communication protocols, and relationship notes with operator.',
                'content'     => file_get_contents($userFile)
            ];
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
