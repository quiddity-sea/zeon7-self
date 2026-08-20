<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();

require_once __DIR__ . '/../src/Services/ConfigService.php';
require_once __DIR__ . '/../src/Services/DashboardService.php';

$dashboardService = new DashboardService();
$dailyContext = $dashboardService->getDailyTheme();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 News Desk ? Content Generation Cockpit</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0"> 
    <link rel="stylesheet" href="css/pages/news-desk.css?v=3">
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'NEWS DESK';
        $pageSubtitle = 'AI SOURCING & PRODUCTION MATRIX';
        include 'components/header.php';
        ?>

        <div class="dashboard-container" style="height: calc(100vh - 80px); padding-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; height: 100%;">
                
                <!-- Col 1: Chat Stream Panel (HUD Border) -->
                <div class="hud-border chat-container" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;" data-tilt>
                    <div class="hud-corner-tr"></div>
                    <div class="hud-corner-bl"></div>
                    
                    <div class="chat-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid rgba(34, 211, 238, 0.2); background: rgba(0,0,0,0.3);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="status-dot"></span>
                            <span style="font-family: var(--font-mono); font-size: 0.8rem; font-weight: 700; color: var(--color-cyan); letter-spacing: 0.1em;">
                                NEURAL CHAT STREAM :: ACTIVE
                            </span>
                        </div>
                        <div class="hud-badge green" style="font-size: 0.7rem;">LATENCY: 12ms</div>
                    </div>

                    <div class="chat-stream" id="chatStream">
                        <div class="msg ai">
                            <div>
                                <span class="label text-cyan">ZEON7 // DISPATCH CORE</span>
                                <div class="content">
                                    I am synchronised with the <strong><?php echo is_array($dailyContext) ? htmlspecialchars($dailyContext['theme'] ?? 'General') : htmlspecialchars($dailyContext); ?></strong> protocol. Ready for generation commands.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="input-area" style="padding: 1rem 1.5rem; background: rgba(0,0,0,0.4); border-top: 1px solid rgba(255,255,255,0.06);">
                        <input type="text" id="chatInput" class="input-box" placeholder="ENTER INSTRUCTION // E.G. 'WRITE TECH DISPATCH ON AI CHIPS'..." autocomplete="off">
                        <button id="sendBtn" class="btn btn-primary" style="margin-left: 1rem;">EXECUTE</button>
                    </div>
                </div>

                <!-- Col 2: Deck Controls (HUD Border) -->
                <div class="hud-border deck-container" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                    <div class="hud-corner-tr"></div>
                    <div class="hud-corner-bl"></div>

                    <div class="tabs" style="border-bottom: 1px solid rgba(34, 211, 238, 0.2); background: rgba(0,0,0,0.3);">
                        <button class="tab active" data-target="produce">PRODUCE</button>
                        <button class="tab" data-target="brain">CORPUS</button>
                        <button class="tab" data-target="memory">MEMORY</button>
                    </div>

                    <!-- TAB 1: PRODUCE -->
                    <div id="view-produce" class="controls active" style="padding: 1.5rem;">
                        <div class="widget">
                            <div class="widget-head">ACTIVE PROTOCOL CONTEXT</div>
                            <div class="context-display" style="padding-left: 1rem; border-left: 2px solid var(--color-cyan);">
                                <span class="context-day text-cyan" style="font-size: 2.5rem;"><?php echo strtoupper(date('D')); ?></span>
                                <span class="context-theme" style="font-weight: 700; color: var(--text-primary);">
                                    <?php echo is_array($dailyContext) ? htmlspecialchars($dailyContext['theme'] ?? 'General') : htmlspecialchars($dailyContext); ?>
                                </span>
                                <?php if (is_array($dailyContext) && !empty($dailyContext['tagline'])): ?>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; font-style: italic;">
                                        "<?php echo htmlspecialchars($dailyContext['tagline']); ?>"
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="widget">
                            <div class="widget-head">TONE MATRIX EQ</div>
                            <div class="slider-row">
                                <span class="slider-label" style="font-family: var(--font-mono); font-size: 0.7rem;">SATIRE</span>
                                <input type="range" id="toneSatire" value="20" min="0" max="100">
                                <span class="slider-label" style="font-family: var(--font-mono); font-size: 0.7rem;">GRIT</span>
                            </div>
                            <div class="slider-row">
                                <span class="slider-label" style="font-family: var(--font-mono); font-size: 0.7rem;">HOPE</span>
                                <input type="range" id="toneHope" value="80" min="0" max="100">
                                <span class="slider-label" style="font-family: var(--font-mono); font-size: 0.7rem;">CYNIC</span>
                            </div>
                        </div>

                        <div class="widget">
                            <div class="widget-head">INTELLIGENCE SCANNER</div>
                            <button id="scanBtn" class="btn btn-secondary" style="width: 100%; justify-content: space-between;">
                                <span>INITIATE DEEP SCAN</span>
                                <span>[ + ]</span>
                            </button>
                            <div id="leadContainer" style="margin-top: 1rem;"></div>
                        </div>

                        <button id="generateBtn" class="btn btn-green" style="width: 100%; margin-top: 1.5rem; padding: 1rem;">
                            ? GENERATE ARTICLE SUITE
                        </button>
                        
                        <div id="generatedContent" style="display:none; margin-top:1.5rem;">
                            <div class="widget-head" style="color:var(--color-primary);">GENERATED SUITE ARTIFACTS</div>
                            <div id="resultsContainer"></div>
                        </div>
                    </div>

                    <!-- TAB 2: BRAIN -->
                    <div id="view-brain" class="controls" style="padding: 1.5rem;">
                        <div class="widget">
                            <div class="widget-head">KNOWLEDGE VECTOR UPLOAD</div>
                            <div class="dropzone" id="brainDropzone" style="border: 2px dashed rgba(34, 211, 238, 0.4); padding: 2rem; text-align: center; border-radius: 4px;">
                                DROP CORPUS FILES (.MD, .TXT)
                            </div>
                            <div id="brainFileList" style="margin-top:1rem;"></div>
                        </div>
                    </div>

                    <!-- TAB 3: MEMORY -->
                    <div id="view-memory" class="controls" style="padding: 1.5rem;">
                        <div class="widget">
                            <div class="widget-head">IMMUTABLE LORE LOGS</div>
                            <div id="memoryLogContainer" style="color: var(--text-muted); font-size: 0.8rem; font-family: var(--font-mono);">
                                Querying active memory entries...
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="js/app.js"></script>
<script src="js/news_desk.js"></script>
<script>
    App.requireAuth();
    document.addEventListener('DOMContentLoaded', () => {
        NewsDesk.init();
    });
</script>
</body>
</html>
