<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 News Desk</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/zeon7-theme.css?v=3"> 
    <link rel="stylesheet" href="css/news-desk.css?v=2">
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <div class="header-bar">
            <div><span class="page-title">NEWS DESK</span><span class="page-subtitle">CONTENT GENERATION COCKPIT</span></div>
            <div style="font-family:var(--font-ui); color:var(--cyan); font-weight:700;">USER: MERRILL LEO</div>
        </div>
        <div class="news-desk-grid">
            <div class="cockpit">
                <div class="chat-panel">
                    <div class="chat-header">
                        <div class="status-block"><div class="status-dot"></div> SYSTEM ONLINE :: V3.7</div>
                        <div class="latency-indicator">LATENCY: 12ms</div>
                    </div>
                    <div class="chat-stream" id="chatStream">
                        <div class="msg ai">
                            <div>
                                <span class="label">ZEON7 // SYSTEM</span>
                                <div class="content">I've synchronized with the <strong>Survival Monday</strong> protocol.</div>
                            </div>
                        </div>
                    </div>
                    <div class="input-area">
                        <input type="text" id="chatInput" class="input-box" placeholder="INPUT COMMAND // SPACEBAR FOR VOICE">
                        <button id="sendBtn" class="btn-send">EXEC</button>
                    </div>
                </div>

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
                                <span class="context-day">MON</span>
                                <span class="context-theme">The Signal Still Comes Through</span>
                            </div>
                        </div>
                        <div class="widget">
                            <div class="widget-head">TONE EQ</div>
                            <div class="slider-row"><span class="slider-label">SATIRE</span><input type="range" id="toneSatire" value="20"><span class="slider-label">GRIT</span></div>
                            <div class="slider-row"><span class="slider-label">HOPE</span><input type="range" id="toneHope" value="80"><span class="slider-label">CYNIC</span></div>
                        </div>
                        <div class="widget">
                            <div class="widget-head">INTELLIGENCE SCANNER</div>
                            <button id="scanBtn" class="btn-scanner"><span>INITIATE SCAN</span><span>[+]</span></button>
                            <div id="leadContainer"></div>
                        </div>
                        <button id="generateBtn" class="btn-generate">GENERATE SUITE</button>

                        <!-- Generated Content Area -->
                            <!-- Lore items will be loaded here -->
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