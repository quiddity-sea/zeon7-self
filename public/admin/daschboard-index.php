<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 Mission Control</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css">
    <style>
        .dashboard-content { padding: 3rem; max-width: 1600px; }
        
        /* Stats Row */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 4rem; }
        .stat-card {
            background: var(--bg-panel); border: 1px solid var(--border-hairline);
            padding: 2rem; border-radius: 4px; position: relative;
        }
        .stat-val { font-family: var(--font-head); font-size: 2.5rem; font-weight: 900; line-height: 1; color: var(--text-main); }
        .stat-label { font-family: var(--font-ui); font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-top: 0.5rem; }
        .stat-icon { position: absolute; top: 1.5rem; right: 1.5rem; font-size: 1.5rem; opacity: 0.5; }

        /* Action Grid */
        .grid-header { font-family: var(--font-ui); color: var(--cyan); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 1.5rem; border-bottom: 1px solid var(--cyan-dim); padding-bottom: 0.5rem; display: inline-block; }
        .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        
        .action-card {
            background: rgba(11, 18, 25, 0.6); border: 1px solid var(--border-hairline);
            padding: 2.5rem; text-align: center; text-decoration: none;
            transition: all 0.3s; position: relative; overflow: hidden;
        }
        .action-card:hover { 
            border-color: var(--orange); transform: translateY(-5px); 
            background: linear-gradient(180deg, rgba(255,69,0,0.05) 0%, transparent 100%);
        }
        .card-icon { font-size: 2.5rem; margin-bottom: 1.5rem; display: block; filter: grayscale(1); transition: filter 0.3s; }
        .action-card:hover .card-icon { filter: grayscale(0) drop-shadow(0 0 10px var(--orange)); }
        
        .card-title { font-family: var(--font-head); font-weight: 800; font-size: 1.1rem; color: var(--text-main); text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 0.5rem; }
        .card-desc { font-family: var(--font-body); font-size: 0.9rem; color: var(--text-muted); line-height: 1.4; }
    </style>
</head>
<body>
    <nav class="sidebar">
        <div class="brand-container">
            <img src="assets/logo_1759683970.png" class="brand-logo" alt="ZEON7">
            <span class="brand-text">ZEON7</span>
        </div>
        <a href="index.html" class="nav-item active"><i>⊞</i> Dashboard</a>
        <a href="news-desk.html" class="nav-item"><i>●</i> News Desk</a>
        <a href="vision.html" class="nav-item"><i>👁</i> Vision</a>
        <a href="lore.html" class="nav-item"><i>∞</i> Lore</a>
        <div style="flex:1"></div>
        <a href="#" class="nav-item"><i>×</i> Logout</a>
    </nav>

    <div class="main-stage">
        <div class="header-bar">
            <div>
                <span class="page-title">MISSION CONTROL</span>
                <span class="page-subtitle">SYSTEM STATUS: OPTIMAL</span>
            </div>
            <div style="font-family:var(--font-ui); color:var(--cyan); font-weight:700;">USER: MERRILL LEO</div>
        </div>

        <div class="dashboard-content">
            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-val" style="color:var(--cyan)">45%</div>
                    <div class="stat-label">Context Memory Load</div>
                    <div class="stat-icon">🧠</div>
                </div>
                <div class="stat-card">
                    <div class="stat-val" style="color:var(--orange)">12</div>
                    <div class="stat-label">Images Pending Analysis</div>
                    <div class="stat-icon">👁</div>
                </div>
                <div class="stat-card">
                    <div class="stat-val">148</div>
                    <div class="stat-label">API Requests Today</div>
                    <div class="stat-icon">⚡</div>
                </div>
            </div>

            <!-- Grid -->
            <div class="grid-header">Operational Modules</div>
            <div class="action-grid">
                <a href="news-desk.html" class="action-card">
                    <span class="card-icon">📰</span>
                    <span class="card-title">Open News Desk</span>
                    <span class="card-desc">Drafting cockpit. Active Theme: <strong>Survival Monday</strong>.</span>
                </a>
                <a href="vision.html" class="action-card">
                    <span class="card-icon">👁</span>
                    <span class="card-title">Vision Studio</span>
                    <span class="card-desc">Process incoming visuals and manage portfolios.</span>
                </a>
                <a href="lore.html" class="action-card">
                    <span class="card-icon">📚</span>
                    <span class="card-title">Lore Manager</span>
                    <span class="card-desc">Edit deep memory and biography facts.</span>
                </a>
                <a href="#" class="action-card">
                    <span class="card-icon">⚠️</span>
                    <span class="card-title">System Override</span>
                    <span class="card-desc">Force global tone shift or reset context.</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>