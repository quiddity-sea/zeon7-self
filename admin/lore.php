<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Memory Bank ? Lore Manager</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'MEMORY BANK';
        $pageSubtitle = 'CORE LORE & FACTUAL ANCHORS';
        include 'components/header.php';
        ?>
        
        <div class="dashboard-container">
            
            <div class="hud-border" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <div>
                        <h2>IMMUTABLE LORE REGISTRY</h2>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                            Persistent biography, worldview anchors, and contextual constants injected into Zeon7 prompt cycles.
                        </p>
                    </div>
                    <button id="addBtn" class="btn btn-green">+ REGISTER NEW LORE</button>
                </div>

                <div class="table-container">
                    <table class="hud-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">KEY IDENTIFIER</th>
                                <th style="width: 50%;">MEMORY CONTENT</th>
                                <th style="width: 15%;">UPDATED</th>
                                <th style="width: 15%; text-align: right;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="loreList">
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2.5rem; color: var(--text-muted); font-family: var(--font-mono);">
                                    QUERYING NEURAL MEMORY BANK...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Pop-In HUD Modal -->
        <div id="loreModal" class="modal-overlay">
            <div class="modal hud-border">
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>
                
                <div class="modal-header">
                    <h3 id="modalTitle" class="text-cyan">REGISTER LORE FACT</h3>
                    <button id="cancelBtn" class="close-btn">&times;</button>
                </div>
                
                <form id="loreForm">
                    <div style="margin-bottom: 1.25rem;">
                        <label>Key Identifier (Unique Slug)</label>
                        <input type="text" id="loreKey" class="input-box" placeholder="e.g. origin_year_2025" required>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label>Lore Anchor Content</label>
                        <textarea id="loreValue" class="input-box" rows="6" placeholder="Enter factual context or persona rule..." required></textarea>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                        <button type="button" id="modalCloseBtn" class="btn btn-secondary">CANCEL</button>
                        <button type="submit" class="btn btn-primary">SAVE TO MEMORY</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="js/app.js"></script>
<script src="js/lore.js"></script>
<script>
    App.requireAuth();
    document.addEventListener('DOMContentLoaded', () => {
        LoreManager.init();
        
        // Enhance modal with GSAP pop-in
        const modal = document.getElementById('loreModal');
        const origOpen = document.getElementById('addBtn');
        const origCancel = document.getElementById('cancelBtn');
        const closeBtn = document.getElementById('modalCloseBtn');

        origCancel?.addEventListener('click', () => ZeonAnimations.animateModalClose(modal));
        closeBtn?.addEventListener('click', () => ZeonAnimations.animateModalClose(modal));
    });
</script>
</body>
</html>
