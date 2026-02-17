<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 Lore Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=4">
    <link rel="stylesheet" href="../css/pages/admin-lore.css">
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'MEMORY BANK';
        $pageSubtitle = 'LORE & FACTS';
        include 'components/header.php';
        ?>
        
        <div class="lore-container">
            <table class="lore-table">
                <thead><tr><th>KEY</th><th>VALUE</th><th>UPDATED</th><th></th></tr></thead>
                <tbody id="loreList"><!-- JS fills this --></tbody>
            </table>
            <div style="margin-top: 2rem; text-align: right;">
                <button id="addBtn" class="btn-primary">+ ADD LORE</button>
            </div>
        </div>

        <!-- Modal -->
        <div id="loreModal" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3 id="modalTitle">Add Lore</h3>
                    <button id="cancelBtn" class="close-btn">&times;</button>
                </div>
                <form id="loreForm">
                    <div class="form-group">
                        <label>Key (Unique ID)</label>
                        <input type="text" id="loreKey" class="input-box" placeholder="e.g. project_alpha_status" required>
                    </div>
                    <div class="form-group">
                        <label>Value (Content)</label>
                        <textarea id="loreValue" class="input-box" rows="5" placeholder="Enter lore content..." required></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">SAVE MEMORY</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="js/app.js"></script>
    <script src="js/lore.js"></script>
    <script>
        App.requireAuth();
        document.addEventListener('DOMContentLoaded', () => {
            LoreManager.init();
        });
    </script>
</body>
</html>