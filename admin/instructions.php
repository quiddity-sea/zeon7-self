<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();

require_once __DIR__ . '/../src/config/env.php';
require_once __DIR__ . '/../src/services/AgentContextService.php';

$agentCtx = new AgentContextService();
$agentName = $agentCtx->getDisplayName();
$agentId = $agentCtx->getAgentId();
$agentAccent = $agentCtx->getThemeAccent();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline" style="--agent-accent: <?= htmlspecialchars($agentAccent) ?>;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruction Editor — <?= htmlspecialchars($agentName) ?> Admin</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=15.0">
    <style>
        .editor-layout {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 1.5rem;
            height: calc(100vh - 120px);
            padding: 1rem 0;
        }
        
        .editor-main {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            height: 100%;
        }

        .editor-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            height: 100%;
            overflow-y: auto;
        }

        .component-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .comp-tab {
            background: rgba(18, 22, 28, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--text-secondary);
            padding: 0.4rem 0.85rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .comp-tab:hover {
            border-color: var(--agent-accent);
            color: #fff;
        }

        .comp-tab.active {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--agent-accent);
            color: var(--agent-accent);
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.15);
        }

        #instructionEditor {
            flex: 1;
            width: 100%;
            padding: 1.25rem;
            background: rgba(2, 4, 8, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 4px;
            color: var(--text-primary);
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            resize: none;
            outline: none;
            box-sizing: border-box;
        }

        #instructionEditor:focus {
            border-color: var(--agent-accent);
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.1);
        }

        .version-item {
            padding: 0.85rem 1rem;
            background: rgba(18, 22, 28, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-left: 3px solid transparent;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .version-item:hover {
            border-color: rgba(255, 255, 255, 0.2);
            background: rgba(25, 30, 38, 0.9);
        }

        .version-item.active {
            border-left-color: var(--agent-accent);
            background: rgba(25, 30, 38, 0.9);
        }

        .version-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
<div class="app-wrapper">

    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <div class="main-stage">
        <?php 
            $pageTitle = strtoupper($agentName) . " INSTRUCTIONS";
            $pageSubtitle = "PERSONA ARCHITECTURE & COMPONENT HEADS";
            include __DIR__ . '/components/header.php'; 
        ?>

        <div class="dashboard-container" style="padding: 1rem 2rem; overflow: hidden; height: calc(100vh - 80px);">
            <div class="editor-layout">
                <!-- Main Editor Area -->
                <div class="editor-main">
                    <!-- Head / Component Tabs -->
                    <div class="component-tabs" id="componentTabs">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; margin-right: 0.5rem;">
                            HEADS / COMPONENTS:
                        </span>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary);" id="activeComponentInfo">
                            Active Prompt: Loading...
                        </div>
                        <div id="wordCount" style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">0 words</div>
                    </div>

                    <textarea id="instructionEditor" placeholder="Loading instruction content..." spellcheck="false"></textarea>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div id="statusMessage" style="font-size: 0.85rem; display: none;"></div>
                        <div style="display: flex; gap: 0.75rem;">
                            <button id="resetHeadBtn" class="btn" style="background: rgba(255,255,255,0.05); color: var(--text-secondary); border: 1px solid rgba(255,255,255,0.1); padding: 0.5rem 1rem;">
                                Reload Baseline Head
                            </button>
                            <button id="saveBtn" class="btn btn-primary" style="background: var(--agent-accent); color: #000; font-weight: bold; padding: 0.5rem 1rem;">
                                Save & Activate Version
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Version History Sidebar -->
                <div class="editor-sidebar">
                    <div class="hud-panel" style="flex: 1; display: flex; flex-direction: column; overflow: hidden; padding: 1rem;">
                        <div class="panel-header" style="margin-bottom: 0.75rem;">
                            <span class="panel-title" style="color: var(--agent-accent);">VERSION HISTORY (<?= strtoupper($agentName) ?>)</span>
                        </div>
                        <div id="versionHistory" style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="padding: 1rem; text-align: center; color: var(--text-secondary);">Loading history...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="js/app.js"></script>
<script src="js/instructions.js?v=2.0"></script>
</body>
</html>
