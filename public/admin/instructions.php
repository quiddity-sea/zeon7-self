<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instruction Editor - Zeon7 Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3">
    <link rel="stylesheet" href="css/components/header-row.css">
    <style>
        .editor-layout {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 2rem;
            height: calc(100vh - 80px);
            padding: 2rem;
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
            border-left: var(--border-hairline);
            padding-left: 2rem;
        }

        #instructionEditor {
            flex: 1;
            width: 100%;
            padding: 1.5rem;
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border-hairline);
            border-radius: 2px;
            color: var(--text-main);
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.95rem;
            line-height: 1.6;
            resize: none;
            transition: border 0.3s;
        }

        #instructionEditor:focus {
            outline: none;
            border-color: var(--cyan);
        }

        .version-item {
            padding: 1rem;
            border: 1px solid var(--border-hairline);
            border-radius: 2px;
            background: var(--bg-panel);
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 0.5rem;
        }

        .version-item:hover {
            border-color: var(--cyan);
            background: rgba(77, 238, 234, 0.05);
        }

        .version-item.active {
            border-color: var(--cyan);
            background: rgba(77, 238, 234, 0.1);
        }

        .version-meta {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
            font-family: var(--font-ui);
        }
        
        .section-head { 
            font-family: var(--font-ui); color: var(--orange); font-weight: 700; 
            letter-spacing: 2px; text-transform: uppercase; margin-bottom: 1rem; font-size: 0.8rem; 
        }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'SYSTEM INSTRUCTIONS';
        $pageSubtitle = 'CORE BEHAVIOR DEFINITION';
        include 'components/header.php';
        ?>

        <div id="statusMessage" style="padding: 0 2rem; margin-top: 1rem; display: none;"></div>
        
        <div class="editor-layout">
            <div class="editor-main">
                <textarea id="instructionEditor" placeholder="Enter system instructions here..."></textarea>
                <div style="margin-top: 1rem; text-align: right;">
                    <button id="saveBtn" class="btn-primary">SAVE NEW VERSION</button>
                </div>
            </div>
            
            <div class="editor-sidebar">
                <div class="section-head">VERSION HISTORY</div>
                <div id="versionHistory">
                    <div style="padding: 1rem; text-align: center; color: var(--text-muted); font-family: var(--font-ui);">Loading history...</div>
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
