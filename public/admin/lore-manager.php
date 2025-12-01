<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 Lore Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css">
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 Lore Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css">
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
    <nav class="sidebar">
        <div class="brand-container">
            <img src="assets/logo_1759683970.png" class="brand-logo" alt="ZEON7">
            <span class="brand-text">ZEON7</span>
        </div>
        <a href="index.php" class="nav-item"><i>⊞</i> Dashboard</a>
        <a href="news-desk.php" class="nav-item"><i>●</i> News Desk</a>
        <a href="vision.php" class="nav-item"><i>👁</i> Vision</a>
        <a href="lore-manager.php" class="nav-item active"><i>∞</i> Lore</a>
        <div style="flex:1"></div>
        <a href="#" class="nav-item"><i>×</i> Logout</a>
    </nav>

    <div class="main-stage">
        <div class="header-bar">
            <div>
                <span class="page-title">MEMORY BANK</span>
                <span class="page-subtitle">PERSISTENT KNOWLEDGE STORE</span>
            </div>
            <button id="addBtn" class="btn-primary">+ ADD LORE</button>
        </div>

        <div class="lore-container">
            <div style="display:flex; gap:1rem;">
                <input type="text" id="searchLore" placeholder="QUERY NEURAL NET..." style="background:var(--bg-void); border:1px solid var(--cyan-dim); padding:1rem; color:white; flex:1; font-family:var(--font-ui);">
            </div>

            <table class="lore-table">
                <thead>
                    <tr>
                        <th width="10%">TYPE</th>
                        <th width="40%">CONTENT</th>
                        <th width="20%">TAGS</th>
                        <th width="10%">PUBLIC</th>
                        <th width="10%">UPDATED</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody id="loreList">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="loreModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
        <div class="modal-content" style="background:var(--bg-panel); padding:2rem; width:600px; border:1px solid var(--cyan); border-radius:4px;">
            <h2 id="modalTitle" style="color:var(--cyan); margin-top:0;">ADD LORE</h2>
            <form id="loreForm">
                <input type="hidden" id="loreId">
                
                <div style="margin-bottom:1rem;">
                    <label style="display:block; color:var(--text-muted); font-size:0.8rem; margin-bottom:0.5rem;">TYPE</label>
                    <select id="loreType" style="width:100%; padding:0.5rem; background:var(--bg-void); color:white; border:1px solid var(--border-hairline);">
                        <option value="memory">Memory</option>
                        <option value="journal">Journal</option>
                        <option value="admin_note">Admin Note</option>
                    </select>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="display:block; color:var(--text-muted); font-size:0.8rem; margin-bottom:0.5rem;">CONTENT</label>
                    <textarea id="loreContent" rows="5" style="width:100%; padding:0.5rem; background:var(--bg-void); color:white; border:1px solid var(--border-hairline); font-family:var(--font-body);"></textarea>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="display:block; color:var(--text-muted); font-size:0.8rem; margin-bottom:0.5rem;">TAGS (Comma separated)</label>
                    <input type="text" id="loreTags" style="width:100%; padding:0.5rem; background:var(--bg-void); color:white; border:1px solid var(--border-hairline);">
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="display:flex; align-items:center; gap:0.5rem; color:var(--text-main); cursor:pointer;">
                        <input type="checkbox" id="lorePublic">
                        <span>Make Public (Visible to Guests)</span>
                    </label>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:1rem;">
                    <button type="button" id="cancelBtn" class="btn-mini" style="padding:0.8rem 1.5rem;">Cancel</button>
                    <button type="submit" class="btn-primary">SAVE ENTRY</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/app.js"></script> <!-- Assuming global app helpers -->
    <script src="js/lore.js"></script>
</body>
</html>