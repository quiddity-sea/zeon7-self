<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 News Desk</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3"> 
    <link rel="stylesheet" href="css/components/header-row.css"> 
    <link rel="stylesheet" href="css/pages/news-desk.css?v=2">
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <!-- Row 1: Header -->
        <?php
        $pageTitle = 'NEWS DESK';
        $pageSubtitle = 'CONTENT GENERATION COCKPIT';
        
        require_once __DIR__ . '/../../src/Services/ConfigService.php';
        require_once __DIR__ . '/../../src/Services/DashboardService.php';
        $dashboardService = new DashboardService();
        $dailyContext = $dashboardService->getDailyTheme();
        
        include 'components/header.php';
        ?>

        <div class="dashboard-container">
            <!-- Row 2: Main Content (Chat & Deck) -->
            <div class="row-modules-terminal">
                <!-- Col 1: Chat Panel (Takes 2/3 space) -->
                <div class="col-modules chat-container">
                    <div class="chat-panel">
                        <div class="chat-header">
                            <div class="status-block"><div class="status-dot"></div> SYSTEM ONLINE :: V3.7</div>
                            <div class="latency-indicator">LATENCY: 12ms</div>
                        </div>
                        <div class="chat-stream" id="chatStream">
                            <div class="msg ai">
                                <div>
                                    <span class="label">ZEON7 // SYSTEM</span>
                                    <div class="content">I've synchronized with the <strong><?php echo htmlspecialchars($dailyContext['theme']); ?></strong> protocol.</div>
                                </div>
                            </div>
                        </div>
                        <div class="input-area">
                            <input type="text" id="chatInput" class="input-box" placeholder="INPUT COMMAND // SPACEBAR FOR VOICE">
                            <button id="sendBtn" class="btn-send">EXEC</button>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Deck Panel (Takes 1/3 space) -->
                <div class="col-terminal deck-container">
                    <div class="deck-panel">
                        <div class="tabs">
                            <button class="tab active" data-target="produce">PRODUCE</button>
                            <button class="tab" data-target="brain">BRAIN</button>
                            <button class="tab" data-target="memory">MEMORY</button>
                        </div>

                        <!-- TAB 1: PRODUCE -->
                        <div id="view-produce" class="controls active">
                            <div class="widget">
                                <div class="widget-head">ACTIVE CONTEXT</div>
                                <div class="context-display">
                                    <span class="context-day"><?php echo strtoupper(date('D')); ?></span>
                                    <span class="context-theme"><?php echo htmlspecialchars($dailyContext['theme']); ?></span>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem; font-style: italic;">
                                        "<?php echo htmlspecialchars($dailyContext['tagline']); ?>"
                                    </div>
                                </div>
                            </div>
                            <div class="widget">
                                <div class="widget-head">TONE EQ <span id="tonePreview" style="font-size: 0.7rem; color: var(--text-muted); margin-left: 1rem; font-weight: normal;">SETTINGS: Satire 20%, Hope 80%</span></div>
                                <div class="slider-row"><span class="slider-label">SATIRE</span><input type="range" id="toneSatire" value="20"><span class="slider-label">GRIT</span></div>
                                <div class="slider-row"><span class="slider-label">HOPE</span><input type="range" id="toneHope" value="80"><span class="slider-label">CYNIC</span></div>
                            </div>
                            <div class="widget">
                                <div class="widget-head">INTELLIGENCE SCANNER</div>
                                <button id="scanBtn" class="btn-scanner"><span>INITIATE SCAN</span><span>[+]</span></button>
                                <div id="leadContainer"></div>
                            </div>
                            <button id="generateBtn" class="btn-generate">GENERATE SUITE</button>
                        </div>

                        <!-- TAB 2: BRAIN (Placeholder) -->
                        <div id="view-brain" class="controls">
                            <div class="widget">
                                <div class="widget-head">FILE SYSTEM</div>
                                <div class="dropzone">DRAG KNOWLEDGE FILES HERE</div>
                            </div>
                        </div>

                        <!-- TAB 3: MEMORY (Placeholder) -->
                        <div id="view-memory" class="controls">
                            <div class="widget">
                                <div class="widget-head">LORE LOGS</div>
                                <div style="color: var(--text-muted); font-style: italic;">No active lore entries.</div>
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