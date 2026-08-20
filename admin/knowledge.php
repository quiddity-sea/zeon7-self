<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Manager ? Zeon7 Admin</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .knowledge-content { padding: 1.5rem 2rem; max-width: 1600px; }
        .upload-card-hud { 
            border: 2px dashed rgba(34, 211, 238, 0.3); 
            border-radius: var(--radius-sm); 
            padding: 2.5rem; 
            text-align: center; 
            cursor: pointer; 
            transition: all 0.2s ease; 
            background: rgba(11, 18, 25, 0.6);
            margin-bottom: 2rem;
            position: relative;
        }
        .upload-card-hud:hover { 
            border-color: var(--color-cyan); 
            background: rgba(34, 211, 238, 0.05); 
            box-shadow: 0 0 20px rgba(var(--color-cyan-rgb), 0.15);
        }
        .upload-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'KNOWLEDGE ENGINE';
        $pageSubtitle = 'DOCUMENT CORPUS & VECTOR INGESTION';
        include 'components/header.php';
        ?>

        <div class="knowledge-content">
            <!-- Upload Zone -->
            <div id="uploadZone" class="hud-border upload-card-hud" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>
                <div class="upload-icon">??</div>
                <h3 style="margin-bottom: 0.5rem;" class="text-cyan">INGEST KNOWLEDGE DOCUMENTS</h3>
                <p style="color: var(--text-muted); font-family: var(--font-mono); font-size: 0.8rem;">
                    Drag & drop markdown (.md) or raw text (.txt) corpus files to generate semantic embeddings & chunks.
                </p>
                <input type="file" id="fileInput" accept=".md,.txt" style="display: none;">
            </div>
            <div id="uploadStatus" style="margin-bottom: 2rem; display: none;"></div>

            <!-- Files List Table -->
            <div class="hud-border" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid rgba(34, 211, 238, 0.2); padding-bottom: 0.5rem;">
                    <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: var(--color-cyan);">
                        INGESTED CORPUS DOCUMENTS
                    </span>
                    <span class="hud-badge green" style="font-size: 0.65rem;">VECTOR READY</span>
                </div>

                <div class="table-container">
                    <table class="hud-table">
                        <thead>
                            <tr>
                                <th>DOCUMENT FILENAME</th>
                                <th>SIZE</th>
                                <th>INGESTION DATE</th>
                                <th style="text-align: right;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="filesList">
                            <tr>
                                <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-muted); font-family: var(--font-mono);">
                                    QUERYING DOCUMENT REPOSITORY...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/app.js"></script>
<script src="js/knowledge.js"></script>
<script>
    App.requireAuth();
</script>
</body>
</html>
