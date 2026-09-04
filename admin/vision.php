<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Vision Studio ? Visual Matrix</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .vision-layout {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 1.5rem;
            padding: 1.5rem 2rem;
            height: calc(100vh - 80px);
        }
        .queue-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 1rem; 
            margin-top: 1rem;
        }
        .img-card { 
            aspect-ratio: 1; 
            background: rgba(3, 6, 9, 0.7); 
            border: 1px solid rgba(34, 211, 238, 0.2); 
            position: relative; 
            cursor: pointer; 
            transition: all 0.2s ease; 
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .img-card:hover { 
            border-color: var(--color-cyan); 
            box-shadow: 0 0 15px rgba(var(--color-cyan-rgb), 0.3);
            transform: scale(1.02);
        }
        .img-card::before {
            content: '?? RAW_IMG';
            color: var(--text-muted);
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.1em;
        }
        
        .folder-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); 
            gap: 1.25rem; 
            margin-top: 1rem;
        }
        .folder-card { 
            text-align: center; 
            cursor: pointer; 
            padding: 1.5rem 1rem; 
            background: rgba(13, 20, 30, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: var(--radius-sm); 
            transition: all 0.25s ease; 
        }
        .folder-card:hover { 
            background: rgba(20, 30, 45, 0.9); 
            border-color: var(--color-cyan);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4), 0 0 15px rgba(var(--color-cyan-rgb), 0.2);
            transform: translateY(-3px);
        }
        .folder-icon { 
            font-size: 2.5rem; 
            display: block; 
            margin-bottom: 0.5rem; 
            transition: transform 0.2s ease; 
        }
        .folder-card:hover .folder-icon { 
            transform: scale(1.1); 
        }
        .folder-name { 
            font-family: var(--font-mono); 
            font-size: 0.8rem; 
            color: var(--text-primary); 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.08em; 
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'VISION STUDIO';
        $pageSubtitle = 'MULTIMODAL VISUAL SCANNING & ARCHIVE';
        include 'components/header.php';
        ?>

        <div class="vision-layout">
            <!-- Left: Incoming Visual Queue -->
            <div class="hud-border" style="display: flex; flex-direction: column; overflow-y: auto;" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="border: 2px dashed rgba(34, 211, 238, 0.3); padding: 1.5rem; text-align: center; border-radius: var(--radius-sm); cursor: pointer; background: rgba(0,0,0,0.2);">
                    <div style="font-size: 1.8rem; margin-bottom: 0.25rem;">??</div>
                    <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-cyan); font-weight: 700;">
                        INGEST VISUAL ASSETS
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; border-bottom: 1px solid rgba(34, 211, 238, 0.2); padding-bottom: 0.4rem;">
                    <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-cyan); font-weight: 700;">
                        UNPROCESSED QUEUE (4)
                    </span>
                    <span class="hud-badge orange" style="font-size: 0.65rem;">PENDING OCR</span>
                </div>

                <div class="queue-grid">
                    <div class="img-card"></div>
                    <div class="img-card"></div>
                    <div class="img-card"></div>
                    <div class="img-card"></div>
                </div>

                <div style="margin-top: auto; padding-top: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <button class="btn btn-secondary" style="width:100%">SYNC VISION MATRIX</button>
                    <button class="btn btn-primary" style="width:100%">? ANALYSE MULTIMODAL BATCH</button>
                </div>
            </div>

            <!-- Right: Processed Portfolio Galleries -->
            <div class="hud-border" style="overflow-y: auto;" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(34, 211, 238, 0.2); padding-bottom: 0.5rem;">
                    <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: var(--color-cyan);">
                        CURATED PORTFOLIO MATRICES
                    </span>
                    <span class="hud-badge green" style="font-size: 0.65rem;">SYNCED</span>
                </div>

                <div class="folder-grid">
                    <div class="folder-card">
                        <span class="folder-icon">???</span>
                        <div class="folder-name">Architecture</div>
                    </div>
                    <div class="folder-card">
                        <span class="folder-icon">??</span>
                        <div class="folder-name">Portraiture</div>
                    </div>
                    <div class="folder-card">
                        <span class="folder-icon">??</span>
                        <div class="folder-name">Nature & Bot</div>
                    </div>
                    <div class="folder-card">
                        <span class="folder-icon">??</span>
                        <div class="folder-name">Experimental</div>
                    </div>
                    <div class="folder-card">
                        <span class="folder-icon">??</span>
                        <div class="folder-name">Documentary</div>
                    </div>
                    <div class="folder-card">
                        <span class="folder-icon">??</span>
                        <div class="folder-name">Seascapes</div>
                    </div>
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
