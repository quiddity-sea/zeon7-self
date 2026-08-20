<?php
/**
 * TemplateLoader — resolves and renders the correct agent template.
 */
class TemplateLoader
{
    private AgentContextService $agentCtx;

    public function __construct(AgentContextService $agentCtx)
    {
        $this->agentCtx = $agentCtx;
    }

    public function getTemplatePath(): string
    {
        $agentId = $this->agentCtx->getAgentId();
        $path = __DIR__ . "/../../templates/{$agentId}";

        if (is_dir($path)) {
            return $path;
        }

        $default = __DIR__ . '/../../templates/default';
        if (is_dir($default)) {
            return $default;
        }

        return __DIR__ . '/../../templates/zeon7';
    }

    public function getThemeCss(): string
    {
        $theme = $this->agentCtx->getThemeBase();
        $path = "themes/{$theme}.css";

        if (file_exists(__DIR__ . "/../../{$path}")) {
            return $path;
        }

        return 'css/theme-cybernetic.css';
    }

    public function renderPublic(): void
    {
        $templatePath = $this->getTemplatePath();
        $templateFile = "{$templatePath}/template.php";

        if (file_exists($templateFile)) {
            $agentCtx = $this->agentCtx;
            require $templateFile;
        } else {
            throw new \RuntimeException("Template not found: {$templateFile}");
        }
    }

    public function getThemeVariables(): string
    {
        return ":root { --agent-accent: {$this->agentCtx->getThemeAccent()}; }";
    }
}
