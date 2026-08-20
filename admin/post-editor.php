<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Editor ? Zeon7 Admin</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .editor-container-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            height: calc(100vh - 180px);
        }
        .preview-pane-hud {
            padding: 1.5rem;
            background: rgba(3, 6, 9, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            overflow-y: auto;
            border-radius: var(--radius-sm);
        }
        .preview-pane-hud h1, .preview-pane-hud h2, .preview-pane-hud h3 {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .preview-pane-hud p {
            margin-bottom: 1rem;
            line-height: 1.6;
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'DISPATCH COMPOSER';
        $pageSubtitle = 'DUAL-PANE MARKDOWN EDITOR';
        include 'components/header.php';
        ?>

        <div class="dashboard-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <input type="text" id="postTitle" class="input-box" placeholder="DISPATCH TITLE..." style="max-width: 600px; font-size: 1.1rem; font-weight: 700;">
                <div style="display: flex; gap: 0.75rem;">
                    <button id="saveDraftBtn" class="btn btn-secondary">SAVE DRAFT</button>
                    <button id="publishBtn" class="btn btn-primary">? PUBLISH LIVE</button>
                </div>
            </div>

            <div class="editor-container-grid">
                <!-- Left: Markdown Input -->
                <div class="hud-border" style="display: flex; flex-direction: column; padding: 1rem;" data-tilt>
                    <div class="hud-corner-tr"></div>
                    <div class="hud-corner-bl"></div>
                    <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-cyan); margin-bottom: 0.5rem;">
                        MARKDOWN SOURCE STREAM
                    </div>
                    <textarea id="postContent" class="input-box" style="flex: 1; resize: none; font-size: 0.9rem; line-height: 1.6;" placeholder="# Enter dispatch markdown content here..."></textarea>
                </div>

                <!-- Right: Live Preview -->
                <div class="hud-border" style="display: flex; flex-direction: column; padding: 1rem;" data-tilt>
                    <div class="hud-corner-tr"></div>
                    <div class="hud-corner-bl"></div>
                    <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-primary); margin-bottom: 0.5rem;">
                        REAL-TIME HUD RENDER PREVIEW
                    </div>
                    <div id="previewPane" class="preview-pane-hud" style="flex: 1;">
                        <span style="color: var(--text-muted); font-family: var(--font-mono); font-size: 0.8rem;">Preview will generate dynamically as you type...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="js/app.js"></script>
<script>
    App.requireAuth();
    document.addEventListener('DOMContentLoaded', () => {
        const contentInput = document.getElementById('postContent');
        const preview = document.getElementById('previewPane');

        contentInput?.addEventListener('input', () => {
            if (typeof marked !== 'undefined') {
                preview.innerHTML = marked.parse(contentInput.value || '*No content yet.*');
            }
        });
    });
</script>
</body>
</html>
