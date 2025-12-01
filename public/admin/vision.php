<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 Vision Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3">
    <style>
        .vision-layout { display: flex; height: calc(100vh - 80px); }
        .queue-panel { 
            flex: 1; border-right: var(--border-hairline); padding: 2rem; 
            overflow-y: auto; background: rgba(0,0,0,0.2); 
        }
        .gallery-panel { flex: 3; padding: 2rem; overflow-y: auto; }

        .section-head { 
            font-family: var(--font-ui); color: var(--orange); font-weight: 700; 
            letter-spacing: 2px; text-transform: uppercase; margin-bottom: 1.5rem; font-size: 0.8rem; 
        }
        
        /* Queue Grid */
        .queue-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .img-card { 
            aspect-ratio: 1; background: var(--bg-panel); border: 1px solid var(--border-hairline); 
            position: relative; cursor: pointer; transition: border 0.2s; overflow: hidden;
        }
        .img-card:hover { border-color: var(--cyan); box-shadow: 0 0 10px rgba(77, 238, 234, 0.1); }
        /* Placeholder for image logic */
        .img-card::before {
            content: 'IMG'; display: flex; align-items: center; justify-content: center;
            height: 100%; color: var(--text-muted); font-family: var(--font-ui); font-size: 0.8rem;
        }
        
        /* Folders */
        .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 2rem; }
        .folder { 
            text-align: center; cursor: pointer; padding: 1rem; 
            border: 1px solid transparent; border-radius: 4px; transition: all 0.2s; 
        }
        .folder:hover { background: rgba(255, 255, 255, 0.03); border-color: var(--border-hairline); }
        
        .folder-icon { 
            font-size: 3rem; color: var(--text-muted); display: block; margin-bottom: 0.5rem; transition: color 0.2s; 
        }
        .folder:hover .folder-icon { color: var(--cyan); text-shadow: 0 0 10px var(--cyan-dim); }
        
        .folder-name { 
            font-family: var(--font-ui); font-size: 0.8rem; color: var(--text-main); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; 
        }

        .dropzone {
            border: 2px dashed var(--border-hairline); padding: 3rem; text-align: center;
            margin-bottom: 2rem; color: var(--text-muted); font-family: var(--font-ui);
            transition: all 0.2s; cursor: pointer;
        }
        .dropzone:hover { border-color: var(--cyan); color: var(--cyan); background: rgba(77, 238, 234, 0.05); }
    </style>
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <div class="header-bar">
            <div>
                <span class="page-title">VISION STUDIO</span>
                <span class="page-subtitle">VISUAL ANALYSIS & PORTFOLIO</span>
            </div>
            <div style="display:flex; gap:10px;">
                <button class="btn-secondary">SYNC DB</button>
                <button class="btn-primary">ANALYZE BATCH</button>
            </div>
        </div>

        <div class="vision-layout">
            <!-- Left: Incoming Queue -->
            <div class="queue-panel">
                <div class="dropzone">DROP INCOMING VISUALS</div>
                <div class="section-head">UNPROCESSED (12)</div>
                <div class="queue-grid">
                    <!-- Mock Images (Grid of 4) -->
                    <div class="img-card"></div>
                    <div class="img-card"></div>
                    <div class="img-card"></div>
                    <div class="img-card"></div>
                </div>
            </div>

            <!-- Right: Processed Gallery -->
            <div class="gallery-panel">
                <div class="section-head">LIVE PORTFOLIOS</div>
                <div class="folder-grid">
                    <!-- Categories from your Dev Version -->
                    <div class="folder">
                        <i class="folder-icon">📁</i>
                        <span class="folder-name">Architecture</span>
                    </div>
                    <div class="folder">
                        <i class="folder-icon">📁</i>
                        <span class="folder-name">Portrait</span>
                    </div>
                    <div class="folder">
                        <i class="folder-icon">📁</i>
                        <span class="folder-name">Nature</span>
                    </div>
                    <div class="folder">
                        <i class="folder-icon">📁</i>
                        <span class="folder-name">Experimental</span>
                    </div>
                    <!-- Added for completeness based on your structure -->
                    <div class="folder">
                        <i class="folder-icon">📁</i>
                        <span class="folder-name">Documentary</span>
                    </div>
                    <div class="folder">
                        <i class="folder-icon">📁</i>
                        <span class="folder-name">Seascapes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/app.js"></script>
    <script>
        App.requireAuth();
    </script>
</body>
</html>