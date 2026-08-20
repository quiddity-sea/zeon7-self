<?php
/**
 * AgentContextService — resolves and manages the active agent identity for the current session.
 */
class AgentContextService
{
    private ?array $manifest = null;
    private string $agentId;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }

        // Allow runtime query override (?agent=leon) which persists in session
        if (!empty($_GET['agent'])) {
            $candidate = strtolower(trim($_GET['agent']));
            if (preg_match('/^[a-z0-9_\-]+$/', $candidate)) {
                $_SESSION['active_agent'] = $candidate;
            }
        }

        $this->agentId = $_SESSION['active_agent']
            ?? $_ENV['COUNCIL_AGENT_ID']
            ?? 'zeon7';
    }

    public function getAgentId(): string
    {
        return $this->agentId;
    }

    public function setAgentId(string $agentId): void
    {
        $this->agentId = strtolower(trim($agentId));
        $_SESSION['active_agent'] = $this->agentId;
        $this->manifest = null;
    }

    public function getDisplayName(): string
    {
        return $this->getManifest()['display_name'] ?? ucfirst($this->agentId);
    }

    public function getTagline(): string
    {
        return $this->getManifest()['tagline'] ?? '';
    }

    public function getRole(): string
    {
        return $this->getManifest()['role'] ?? '';
    }

    public function getPageTitle(): string
    {
        $prefix = $this->getManifest()['branding']['page_title_prefix'] ?? $this->getDisplayName();
        $tagline = $this->getTagline();
        return $tagline ? "{$prefix} — {$tagline}" : $prefix;
    }

    public function getLogoPath(): string
    {
        return $this->getManifest()['branding']['logo'] ?? 'assets/images/logo_1759683970.png';
    }

    public function getThemeBase(): string
    {
        return $this->getManifest()['theme']['base'] ?? 'cybernetic';
    }

    public function getThemeAccent(): string
    {
        return $this->getManifest()['theme']['accent'] ?? '#00ffff';
    }

    public function getThemeMode(): string
    {
        return $this->getManifest()['theme']['mode'] ?? 'dark';
    }

    public function getFallbackGreeting(): string
    {
        return $this->getManifest()['persona']['fallback_greeting']
            ?? 'You are a helpful AI assistant.';
    }

    public function getVisionStyle(): string
    {
        return $this->getManifest()['persona']['vision_style']
            ?? 'analytical, direct';
    }

    public function getDispatchLabel(): string
    {
        return $this->getManifest()['persona']['dispatch_label']
            ?? strtoupper($this->getDisplayName()) . ' // CORE';
    }

    public function getCapabilities(): array
    {
        return $this->getManifest()['capabilities'] ?? ['chat', 'memory', 'knowledge'];
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->getCapabilities(), true);
    }

    public function getPanels(): array
    {
        return $this->getManifest()['panels'] ?? [];
    }

    public function getLayoutType(): string
    {
        return $this->getManifest()['layout']['type'] ?? 'cockpit';
    }

    /**
     * List all available agents in the ForeverBox ecosystem.
     */
    public function listAvailableAgents(): array
    {
        $dataPath = $_ENV['FOREVERBOX_DATA_PATH'] ?? '/foreverbox_data';
        $profilesDir = "{$dataPath}/profiles";
        $agents = [];

        $known = ['zeon7', 'leon', 'gemma', 'otec', 'wolf'];
        
        if (is_dir($profilesDir)) {
            $dirs = scandir($profilesDir);
            foreach ($dirs as $d) {
                if ($d !== '.' && $d !== '..' && is_dir("{$profilesDir}/{$d}")) {
                    if (!in_array($d, $known, true)) {
                        $known[] = $d;
                    }
                }
            }
        }

        foreach ($known as $slug) {
            $manifestFile = "{$profilesDir}/{$slug}/ui-manifest.yaml";
            $name = ucfirst($slug);
            $tagline = '';
            $role = '';
            $accent = '#00ffff';

            if (file_exists($manifestFile)) {
                $content = file_get_contents($manifestFile);
                $parsed = $this->parseSimpleYaml($content);
                $name = $parsed['display_name'] ?? ucfirst($slug);
                $tagline = $parsed['tagline'] ?? '';
                $role = $parsed['role'] ?? '';
                $accent = $parsed['theme']['accent'] ?? '#00ffff';
            }

            $agents[$slug] = [
                'id'        => $slug,
                'name'      => $name,
                'tagline'   => $tagline,
                'role'      => $role,
                'accent'    => $accent,
                'is_active' => ($slug === $this->agentId)
            ];
        }

        return $agents;
    }

    private function getManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        $dataPath = $_ENV['FOREVERBOX_DATA_PATH'] ?? '/foreverbox_data';
        $file = "{$dataPath}/profiles/{$this->agentId}/ui-manifest.yaml";

        if (file_exists($file)) {
            if (function_exists('yaml_parse_file')) {
                $this->manifest = yaml_parse_file($file) ?: [];
            } else {
                $this->manifest = $this->parseSimpleYaml(file_get_contents($file));
            }
        } else {
            $this->manifest = [];
        }

        return $this->manifest;
    }

    private function cleanValue(string $val): string
    {
        if (preg_match('/^"([^"]*)"\s*(?:#.*)?$/', $val, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/^\'([^\']*)\'\s*(?:#.*)?$/', $val, $m)) {
            return trim($m[1]);
        }
        $parts = explode('#', $val, 2);
        return trim(trim($parts[0]), "\"' ");
    }

    private function parseSimpleYaml(string $content): array
    {
        $result = [];
        $lines = explode("\n", $content);
        $currentSection = null;
        $currentSubSection = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) continue;

            if (preg_match('/^(\w[\w_]+):\s*(.+)$/', $line, $m)) {
                $key = $m[1];
                $val = $this->cleanValue($m[2]);
                $result[$key] = $val;
                $currentSection = null;
                $currentSubSection = null;
                continue;
            }

            if (preg_match('/^(\w[\w_]+):\s*$/', $line, $m)) {
                $currentSection = $m[1];
                $result[$currentSection] = [];
                $currentSubSection = null;
                continue;
            }

            if ($currentSection && preg_match('/^  (\w[\w_]+):\s*$/', $line, $m)) {
                $currentSubSection = $m[1];
                $result[$currentSection][$currentSubSection] = [];
                continue;
            }

            if ($currentSection && $currentSubSection && preg_match('/^    (\w[\w_]+):\s*(.+)$/', $line, $m)) {
                $result[$currentSection][$currentSubSection][$m[1]] = $this->cleanValue($m[2]);
                continue;
            }

            if ($currentSection && preg_match('/^  (\w[\w_]+):\s*(.+)$/', $line, $m)) {
                $result[$currentSection][$m[1]] = $this->cleanValue($m[2]);
                continue;
            }

            if ($currentSection && preg_match('/^  - (.+)$/', $line, $m)) {
                if (!is_array($result[$currentSection])) $result[$currentSection] = [];
                $result[$currentSection][] = $this->cleanValue($m[1]);
                continue;
            }

            if ($currentSection && $currentSubSection && preg_match('/^    - (.+)$/', $line, $m)) {
                if (!is_array($result[$currentSection][$currentSubSection])) {
                    $result[$currentSection][$currentSubSection] = [];
                }
                $result[$currentSection][$currentSubSection][] = $this->cleanValue($m[1]);
                continue;
            }
        }

        return $result;
    }
}
