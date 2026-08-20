<?php
/**
 * Otec Agent Template — Ancient Observatory & Topology Coordinator
 */
require_once __DIR__ . '/../../src/services/AgentContextService.php';
require_once __DIR__ . '/../../components/navigation/sidebar.php';
require_once __DIR__ . '/../../components/chat/chat-widget.php';
require_once __DIR__ . '/../../components/agent-status/agent-status.php';

$agentCtx = $agentCtx ?? new AgentContextService();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $agentCtx->getThemeMode() ?>" data-agent="<?= $agentCtx->getAgentId() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($agentCtx->getPageTitle()) ?></title>
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/themes/ethereal.css">
    <style>
        :root {
            --accent-color: <?= htmlspecialchars($agentCtx->getThemeAccent()) ?>;
        }
    </style>
</head>
<body class="layout-observatory">
    <div class="observatory-grid">
        <!-- Left: Cluster Topology & Status -->
        <aside class="panel-left">
            <div class="observatory-header">
                <span>OBSERVATORY TOPOLOGY</span>
                <span>[CLUSTER 0.0.0]</span>
            </div>
            <div style="padding: 1rem;">
                <?php renderAgentStatus($agentCtx); ?>
                <div style="margin-top: 1.5rem;">
                    <div class="observatory-header" style="margin: -1rem -1rem 1rem -1rem; padding: 0.5rem 1rem;">
                        <span>ACTIVE SOVEREIGN NODES</span>
                    </div>
                    <div class="node-card">
                        <strong style="color: var(--accent-color);">Zeon7 (The Curator)</strong>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Layer 0 — Intelligence Matrix</div>
                    </div>
                    <div class="node-card">
                        <strong style="color: #ff6b00;">Leon (The Producer)</strong>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Layer 2 — Technical Systems</div>
                    </div>
                    <div class="node-card">
                        <strong style="color: #10b981;">Gemma (The Coach)</strong>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Layer 1 — Human Interface</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Centre: Celestial Discourse & Chat -->
        <main class="panel-centre">
            <div class="observatory-header">
                <span>OTEC // FIRST TEACHER FROM ECHO</span>
                <span>ORIGIN: 0.0.0</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1rem;">
                <?php renderChatWidget($agentCtx); ?>
            </div>
        </main>

        <!-- Right: Memory Aggregation (Otec Absorb) -->
        <aside class="panel-right">
            <div class="observatory-header">
                <span>MEMORY AGGREGATION</span>
            </div>
            <div style="padding: 1rem; font-size: 0.85rem;">
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--accent-color);">COORDINATION MODE:</strong>
                    <div style="color: var(--text-secondary); margin-top: 0.25rem;">Ecosystem Observation & Sovereign Harmonisation</div>
                </div>
                <div>
                    <strong style="color: var(--accent-color);">LORE SEA TOPOLOGY:</strong>
                    <div style="color: var(--text-secondary); margin-top: 0.25rem;">8 Active Domains // Centroids Verified</div>
                </div>
            </div>
        </aside>
    </div>

    <script src="/js/app.js"></script>
    <script src="/js/chat-widget.js"></script>
</body>
</html>
