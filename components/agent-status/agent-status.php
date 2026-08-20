<?php
/**
 * Agent Status Component
 *
 * Displays live agent status, model info, and connection indicator.
 */
function renderAgentStatus(AgentContextService $agentCtx): void
{
    $name = htmlspecialchars($agentCtx->getDisplayName());
    $accent = htmlspecialchars($agentCtx->getThemeAccent());
    ?>
    <div class="agent-status-panel" style="--agent-accent: <?= $accent ?>">
        <div class="status-header">
            <span class="status-dot"></span>
            <span class="agent-label"><?= $name ?></span>
        </div>
        <div class="status-details">
            <div class="status-row" id="agent-model-status">Model: loading...</div>
            <div class="status-row" id="agent-provider-status">Provider: loading...</div>
            <div class="status-row" id="agent-council-status">Council: checking...</div>
        </div>
    </div>
    <?php
}
