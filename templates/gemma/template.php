<?php
/**
 * Gemma Agent Template — Wellness & Coaching Dashboard
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
    <link rel="stylesheet" href="/themes/organic.css">
    <style>
        :root {
            --accent-color: <?= htmlspecialchars($agentCtx->getThemeAccent()) ?>;
        }
    </style>
</head>
<body class="layout-dashboard">
    <div class="dashboard-grid">
        <!-- Left: Status & Wellness Focus -->
        <aside class="panel-left">
            <div class="dashboard-header">
                <span>GEMMA WELLNESS</span>
                <span>[FOREVERFIT]</span>
            </div>
            <div style="padding: 1.25rem;">
                <?php renderAgentStatus($agentCtx); ?>
                <div style="margin-top: 1.5rem;">
                    <div class="wellness-card">
                        <div style="font-weight: 600; color: var(--accent-color); margin-bottom: 0.25rem;">Daily Focus</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Neurodivergent pacing, restorative flow & mental clarity.</div>
                    </div>
                    <div class="wellness-card">
                        <div style="font-weight: 600; color: var(--accent-color); margin-bottom: 0.25rem;">Coaching State</div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">Empathy active // Supportive guidance enabled.</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main: Interactive Conversation -->
        <main class="panel-main">
            <div class="dashboard-header">
                <span>GEMMA // INTERFACE & COACH</span>
                <span>CONNECTED</span>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1.25rem;">
                <?php renderChatWidget($agentCtx); ?>
            </div>
        </main>
    </div>

    <script src="/js/app.js"></script>
    <script src="/js/chat-widget.js"></script>
</body>
</html>
