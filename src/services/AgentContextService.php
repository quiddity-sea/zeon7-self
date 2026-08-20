<?php
/**
 * AgentContextService — resolves the active agent identity for the current request.
 *
 * Sources agent configuration from:
 * 1. The UI manifest YAML in foreverbox-data/profiles/{agent_id}/ui-manifest.yaml
 * 2. Hardcoded Zeon7 defaults as final fallback
 */
class AgentContextService
{
    private ?array $manifest = null;
    private string $agentId;

    public function __construct()
    {
        $this->agentId = $_ENV['COUNCIL_AGENT_ID'] ?? 'zeon7';
    }

    public function getAgentId(): string
    {
        return $this->agentId;
    }

    public function getDisplayName(): string
    {
        return $this->getManifest()['display_name'] ?? ucfirst($this->agentId);
    }

    public function getTagline(): string
    {
        return $this->getManifest()['tagline'] ?? '';
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
        return $this->getManifest()['capabilities'] ?? ['chat'];
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
     * Load and cache the agent manifest.
     */
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

    /**
     * Minimal YAML parser for flat and nested keys.
     */
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
                $val = trim($m[2], "\"' ");
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
                $result[$currentSection][$currentSubSection][$m[1]] = trim($m[2], "\"' ");
                continue;
            }

            if ($currentSection && preg_match('/^  (\w[\w_]+):\s*(.+)$/', $line, $m)) {
                $result[$currentSection][$m[1]] = trim($m[2], "\"' ");
                continue;
            }

            if ($currentSection && preg_match('/^  - (.+)$/', $line, $m)) {
                if (!is_array($result[$currentSection])) $result[$currentSection] = [];
                $result[$currentSection][] = trim($m[1], "\"' ");
                continue;
            }

            if ($currentSection && $currentSubSection && preg_match('/^    - (.+)$/', $line, $m)) {
                if (!is_array($result[$currentSection][$currentSubSection])) {
                    $result[$currentSection][$currentSubSection] = [];
                }
                $result[$currentSection][$currentSubSection][] = trim($m[1], "\"' ");
                continue;
            }
        }

        return $result;
    }
}
