<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 Lore Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3">
    <style>
        .lore-container { padding: 3rem; max-width: 1400px; }
        
        /* Table Styles */
        .lore-table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        .lore-table th { 
            text-align: left; padding: 1rem; color: var(--cyan); 
            font-family: var(--font-ui); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;
            border-bottom: 1px solid var(--cyan-dim);
        }
        .lore-table td {
            padding: 1.2rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);
            font-family: var(--font-body); color: var(--text-main);
        }
        .lore-key { 
            font-family: 'Courier New', monospace; color: var(--orange); 
            background: rgba(255,69,0,0.1); padding: 4px 8px; border-radius: 4px; font-size: 0.9rem;
        }
        .actions { display: flex; gap: 10px; justify-content: flex-end; }
        .btn-mini { 
            background: transparent; border: 1px solid var(--border-hairline); 
            color: var(--text-muted); padding: 4px 8px; font-size: 0.7rem; cursor: pointer; text-transform: uppercase; 
        }
        .btn-mini:hover { color: var(--cyan); border-color: var(--cyan); }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <div class="header-bar">
            <div><span class="page-title">MEMORY BANK</span></div><button id="addBtn" class="btn-primary">+ ADD LORE</button>
        </div>
        <div class="lore-container">
            <table class="lore-table">
                <thead><tr><th>KEY</th><th>VALUE</th><th>UPDATED</th><th></th></tr></thead>
                <tbody id="loreList"><!-- JS fills this --></tbody>
            </table>
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