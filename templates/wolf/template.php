<?php
/**
 * Wolf Agent Template
 *
 * Composes shared components into the Wolf synthesis & execution layout.
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
    <link rel="stylesheet" href="/css/theme-cybernetic.css">
    <style>
        :root {
            --agent-accent: <?= htmlspecialchars($agentCtx->getThemeAccent()) ?>;
        }
    </style>
</head>
<body class="layout-<?= htmlspecialchars($agentCtx->getLayoutType()) ?>">
    <div class="cockpit-grid">
        <aside class="panel-left">
            <?php renderAgentStatus($agentCtx); ?>
        </aside>

        <main class="panel-centre">
            <?php renderChatWidget($agentCtx); ?>
        </main>
    </div>

    <script src="/js/app.js"></script>
    <script src="/js/chat-widget.js"></script>
</body>
</html>
