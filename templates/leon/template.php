<?php
/**
 * Leon Agent Template — Industrial Systems Workspace
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
    <link rel="stylesheet" href="/themes/industrial.css">
    <style>
        :root {
            --accent-color: <?= htmlspecialchars($agentCtx->getThemeAccent()) ?>;
        }
    </style>
</head>
<body class="layout-workspace">
    <div class="workspace-grid">
        <!-- Left: Systems Status & Tasks -->
        <aside class="panel-left">
            <div class="workspace-header">
                <span>SYSTEM STATUS</span>
                <span>[ENG // 02]</span>
            </div>
            <div style="padding: 1rem;">
                <?php renderAgentStatus($agentCtx); ?>
                <div style="margin-top: 1.5rem;">
                    <div class="workspace-header" style="margin: -1rem -1rem 1rem -1rem; padding: 0.5rem 1rem;">
                        <span>ACTIVE DIRECTIVES</span>
                    </div>
                    <div class="task-card">
                        <div class="task-title">The Initiative Master Audio</div>
                        <div class="task-meta">Status: In Progress // Priority: Alpha</div>
                    </div>
                    <div class="task-card">
                        <div class="task-title">Quiddity Sea Pipeline Sync</div>
                        <div class="task-meta">Status: Active Daemon // Interval: 30m</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Centre: Execution Terminal & Chat -->
        <main class="panel-centre">
            <div class="workspace-header">
                <span>LEON // TECHNICAL WORKSPACE</span>
                <span>PORTAL: SECURE</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1rem;">
                <?php renderChatWidget($agentCtx); ?>
            </div>
        </main>

        <!-- Right: Tools & Context -->
        <aside class="panel-right">
            <div class="workspace-header">
                <span>ENGINEERING TOOLS</span>
            </div>
            <div style="padding: 1rem; font-size: 0.85rem;">
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--accent-color);">COGNITIVE LAYER:</strong>
                    <div style="color: var(--text-secondary); margin-top: 0.25rem;">Layer 2 — Analytical Engine</div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <strong style="color: var(--accent-color);">TOOL MATRIX:</strong>
                    <ul style="padding-left: 1.25rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        <li>Vector Search (384-dim)</li>
                        <li>Wolf Task Dispatch</li>
                        <li>Quiddity Commons Access</li>
                        <li>Hermes Automation</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>

    <script src="/js/app.js"></script>
    <script src="/js/chat-widget.js"></script>
</body>
</html>
