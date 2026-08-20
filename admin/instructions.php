<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruction Editor ? Zeon7 Admin</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .editor-layout {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 1.5rem;
            height: calc(100vh - 120px);
            padding: 1.5rem 2rem;
        }
        
        .editor-main {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            height: 100%;
        }

        .editor-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            height: 100%;
            overflow-y: auto;
        }

        #instructionEditor {
            flex: 1;
            width: 100%;
            padding: 1.5rem;
            background: rgba(2, 4, 8, 0.9);
            border: 1px solid rgba(34, 211, 238, 0.3);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: var(--font-mono);
            font-size: 0.9rem;
            line-height: 1.6;
            resize: none;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.8);
        }

        #instructionEditor:focus {
            outline: none;
            border-color: var(--color-cyan);
            box-shadow: inset 0 0 20px rgba(0,0,0,0.8), 0 0 15px rgba(var(--color-cyan-rgb), 0.2);
        }

        .version-item {
            padding: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 36, 0.8);
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 0.5rem;
            border-radius: var(--radius-sm);
        }

        .version-item:hover {
            border-color: var(--color-cyan);
            background: rgba(34, 211, 238, 0.08);
            transform: translateX(3px);
        }

        .version-item.active {
            border-color: var(--color-primary);
            background: rgba(0, 255, 65, 0.08);
            box-shadow: 0 0 10px rgba(var(--color-primary-rgb), 0.1);
        }

        .version-meta {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
            font-family: var(--font-mono);
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'SYSTEM INSTRUCTIONS';
        $pageSubtitle = 'CRISPE PROMPT & BEHAVIOUR DEFINITION';
        include 'components/header.php';
        ?>

        <div id="statusMessage" style="padding: 0 2rem; margin-top: 1rem; display: none;"></div>
        
        <div class="editor-layout">
            <div class="hud-border editor-main" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-cyan); font-weight: 700;">
                        ACTIVE PROMPT ARCHITECTURE (CRISPE V3.7)
                    </span>
                    <span class="hud-badge green" style="font-size: 0.65rem;">SYNCHRONISED</span>
                </div>

                <textarea id="instructionEditor" placeholder="Enter system instructions / CRISPE context here..."></textarea>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button id="saveBtn" class="btn btn-green">SAVE NEW VERSION</button>
                </div>
            </div>
            
            <div class="hud-border editor-sidebar">
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-cyan); font-weight: 700; border-bottom: 1px solid rgba(34,211,238,0.2); padding-bottom: 0.5rem;">
                    VERSION REGISTRY
                </div>
                
                <div id="versionHistory">
                    <div style="padding: 1rem; text-align: center; color: var(--text-muted); font-family: var(--font-mono); font-size: 0.8rem;">
                        Loading version tree...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/app.js"></script>
<script src="js/instructions.js"></script>
<script>
    App.requireAuth();
</script>
</body>
</html>
