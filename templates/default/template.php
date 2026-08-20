<?php
/**
 * Default Agent Template — minimal, clean interface for agents without custom templates.
 */
require_once __DIR__ . '/../../src/services/AgentContextService.php';
require_once __DIR__ . '/../../components/chat/chat-widget.php';

$agentCtx = $agentCtx ?? new AgentContextService();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $agentCtx->getThemeMode() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($agentCtx->getPageTitle()) ?></title>
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/components.css">
    <style>
        :root { --agent-accent: <?= htmlspecialchars($agentCtx->getThemeAccent()) ?>; }
    </style>
</head>
<body>
    <header style="padding: 2rem; text-align: center;">
        <h1><?= htmlspecialchars($agentCtx->getDisplayName()) ?></h1>
        <p><?= htmlspecialchars($agentCtx->getTagline()) ?></p>
    </header>
    <main style="max-width: 800px; margin: 0 auto; padding: 1rem;">
        <?php renderChatWidget($agentCtx); ?>
    </main>
    <script src="/js/app.js"></script>
    <script src="/js/chat-widget.js"></script>
</body>
</html>
