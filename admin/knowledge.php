<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Manager — Zeon7 Admin</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .knowledge-content { padding: 1.5rem 2rem; max-width: 1600px; }
        .upload-card-hud { 
            border: 2px dashed rgba(34, 211, 238, 0.3); 
            border-radius: var(--radius-sm); 
            padding: 2rem; 
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
        .upload-icon { font-size: 2.2rem; margin-bottom: 0.5rem; }
        .subfolder-select-wrap {
            margin-top: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--text-muted);
            background: rgba(0,0,0,0.4);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            border: 1px solid rgba(34, 211, 238, 0.2);
        }
        .subfolder-select-wrap select {
            background: #0b1219;
            color: var(--color-cyan);
            border: 1px solid rgba(34, 211, 238, 0.4);
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            cursor: pointer;
        }
        .hud-badge.blue {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.4);
        }
        .hud-badge.amber {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.4);
        }
        .hud-badge.red {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
        }
        .hud-btn-action {
            background: transparent;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.75rem;
            font-family: var(--font-mono);
            padding: 0.25rem 0.5rem;
            transition: all 0.2s ease;
        }
        .hud-btn-action.reingest {
            border: 1px solid rgba(34, 211, 238, 0.4);
            color: var(--color-cyan);
            margin-right: 0.4rem;
        }
        .hud-btn-action.reingest:hover {
            background: rgba(34, 211, 238, 0.15);
        }
        .hud-btn-action.delete {
            border: 1px solid var(--danger);
            color: var(--danger);
        }
        .hud-btn-action.delete:hover {
            background: rgba(239, 68, 68, 0.15);
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'KNOWLEDGE ENGINE';
        $pageSubtitle = 'QUIDDITY LORE SEA & VECTOR CORPUS';
        include 'components/header.php';
        ?>

        <div class="knowledge-content">
            <!-- Upload Zone -->
            <div id="uploadZone" class="hud-border upload-card-hud" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>
                <div class="upload-icon">⚡</div>
                <h3 style="margin-bottom: 0.5rem;" class="text-cyan">INGEST LORE SEA DOCUMENTS</h3>
                <p style="color: var(--text-muted); font-family: var(--font-mono); font-size: 0.8rem;">
                    Drag & drop markdown (.md), text (.txt), or PDF files to save directly to Quiddity Lore Sea and generate dense 384-dim semantic embeddings.
                </p>
                <div class="subfolder-select-wrap" onclick="event.stopPropagation();">
                    <label for="subfolderSelect">Target Subfolder:</label>
                    <select id="subfolderSelect">
                        <option value="">(Root — Auto-Classify)</option>
                        <option value="01_TheForeverbox_Mythos">01_TheForeverbox_Mythos</option>
                        <option value="02_ReInvigor_Texts">02_ReInvigor_Texts</option>
                        <option value="03_TheInitiative_Audio">03_TheInitiative_Audio</option>
                        <option value="04_FromTheNoise_Archives">04_FromTheNoise_Archives</option>
                        <option value="05_Agent_Profiles">05_Agent_Profiles</option>
                        <option value="06_QuiddityLtd_Dev_Specs">06_QuiddityLtd_Dev_Specs</option>
                        <option value="07_MerrillLeo_CreativeWorks">07_MerrillLeo_CreativeWorks</option>
                        <option value="08_VisualMedia">08_VisualMedia</option>
                    </select>
                </div>
                <input type="file" id="fileInput" accept=".md,.txt,.pdf" style="display: none;">
            </div>
            <div id="uploadStatus" style="margin-bottom: 2rem; display: none;"></div>

            <!-- Files List Table -->
            <div class="hud-border" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid rgba(34, 211, 238, 0.2); padding-bottom: 0.5rem;">
                    <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: var(--color-cyan);">
                        QUIDDITY LORE SEA INGESTED CORPUS
                    </span>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick="Knowledge.loadFiles()" class="hud-btn-action reingest">REFRESH</button>
                        <span class="hud-badge green" style="font-size: 0.65rem;">COUNCIL COMMONS SYNCED</span>
                    </div>
                </div>

                <div class="table-container">
                    <table class="hud-table">
                        <thead>
                            <tr>
                                <th>DOCUMENT</th>
                                <th>LORE SEA PATH</th>
                                <th>SIZE</th>
                                <th>STATUS</th>
                                <th>LAST INDEXED</th>
                                <th style="text-align: right;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="filesList">
                            <tr>
                                <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-muted); font-family: var(--font-mono);">
                                    QUERYING COUNCIL COMMONS REPOSITORY...
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
