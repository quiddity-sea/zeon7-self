<?php
/**
 * The Eye — Sovereign Geospatial Intelligence Viewer
 * Entry point. Renders the fullscreen CesiumJS globe.
 */

// Load env and auth from either parent dir or self webroot
$selfRoot = file_exists(__DIR__ . '/../src/config/env.php') 
    ? dirname(__DIR__) 
    : '/var/www/vhosts/bjorntyrsson.co.uk/self.foreverbox.co.uk';

require_once $selfRoot . '/src/config/env.php';
require_once $selfRoot . '/src/services/AuthService.php';
require_once __DIR__ . '/src/services/EyeService.php';

// Auth check
$authService = new AuthService();
$isAuth = $authService->isAuthenticated();
$currentUser = $isAuth ? $authService->getCurrentUser() : null;
$userId = $currentUser['user_id'] ?? null;

// Session tracking
$eyeService = new EyeService();
$sessionToken = $eyeService->openSession($userId);

// Config for JS
$cesiumToken = $_ENV['CESIUM_ION_TOKEN'] ?? '';
$googleMapsKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? '';
?>
<!DOCTYPE html>
<html lang="en-GB" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Eye // Geospatial Intelligence — ForeverBox</title>

    <!-- Zeon7 theme (shared palette) -->
    <link rel="stylesheet" href="/css/zeon7-theme.css?v=16.0">

    <!-- The Eye specific styles -->
    <link rel="stylesheet" href="css/the-eye.css?v=1.0">

    <!-- CesiumJS from CDN -->
    <link href="https://cesium.com/downloads/cesiumjs/releases/1.124/Build/Cesium/Widgets/widgets.css" rel="stylesheet">
    <script src="https://cesium.com/downloads/cesiumjs/releases/1.124/Build/Cesium/Cesium.js"></script>

    <!-- Satellite.js for orbital calculations -->
    <script src="https://cdn.jsdelivr.net/npm/satellite.js@6.0.2/dist/satellite.min.js"></script>

    <!-- Configuration injection -->
    <script>
        window.EYE_CONFIG = {
            cesiumToken: "<?= htmlspecialchars($cesiumToken, ENT_QUOTES) ?>",
            googleMapsKey: "<?= htmlspecialchars($googleMapsKey, ENT_QUOTES) ?>",
            isAuth: <?= $isAuth ? 'true' : 'false' ?>,
            userId: <?= $userId ? (int) $userId : 'null' ?>,
            sessionToken: "<?= htmlspecialchars($sessionToken, ENT_QUOTES) ?>",
            userName: "<?= $isAuth ? htmlspecialchars($currentUser['username'] ?? 'OPERATOR', ENT_QUOTES) : '' ?>"
        };
    </script>
</head>
<body>
    <!-- Globe container (CesiumJS renders here) -->
    <div id="cesiumContainer"></div>

    <!-- HUD overlay (sits on top of globe, pointer-events: none by default) -->
    <div id="hud-overlay">

        <!-- Corner decorations (cybernetic brackets) -->
        <div class="hud-corner hud-corner-tl"></div>
        <div class="hud-corner hud-corner-tr"></div>
        <div class="hud-corner hud-corner-bl"></div>
        <div class="hud-corner hud-corner-br"></div>

        <!-- Top-left: Tactical HUD (camera coords, clock, telemetry) -->
        <div id="tactical-hud">
            <div id="hud-title">THE EYE <span class="hud-version">v1.0</span></div>
            <div id="hud-clock"></div>
            <div id="hud-coords"></div>
            <div id="hud-stats"></div>
            <div id="hud-telemetry" class="hidden"></div>
        </div>

        <!-- Top-right: Layer control panel -->
        <div id="layer-panel" class="glass-panel">
            <div class="panel-header">LAYERS</div>
            <div id="layer-toggles"></div>
        </div>

        <!-- Top-right below layers: Sensor style switcher -->
        <div id="sensor-panel" class="glass-panel">
            <div class="panel-header">OPTICS</div>
            <div id="sensor-buttons"></div>
        </div>

        <!-- Top-right below optics: Navigation controls -->
        <div id="navigation-panel" class="glass-panel">
            <div class="panel-header">NAVIGATION</div>
            <div class="nav-actions">
                <button id="btn-reset-view" class="eye-btn-sm" title="Center and reset globe view">⌂ RESET</button>
                <button id="btn-control-help" class="eye-btn-sm" title="View flight & mouse controls">? MANUAL</button>
            </div>
        </div>

        <!-- Bottom-left: Contact info card (shown when entity is tracked) -->
        <div id="contact-card" class="glass-panel hidden">
            <div class="panel-header">CONTACT</div>
            <div id="contact-info"></div>
            <div class="contact-actions">
                <button id="btn-cockpit" class="eye-btn">COCKPIT</button>
                <button id="btn-untrack" class="eye-btn">RELEASE</button>
            </div>
        </div>

        <!-- Bottom-center: Agent bar (authenticated users only) -->
        <?php if ($isAuth): ?>
        <div id="agent-bar-container">
            <div id="agent-last-reply"></div>
            <div id="agent-bar">
                <span class="agent-label">OTEC</span>
                <input type="text" id="agent-input" placeholder="Ask Otec..." autocomplete="off">
                <span id="agent-word-count">0/40</span>
            </div>
        </div>

        <!-- Agent popup (expanded chat, initially hidden) -->
        <div id="agent-popup" class="glass-panel hidden">
            <div class="popup-header">
                <span>OTEC // Eye Commander</span>
                <button id="close-popup" class="eye-btn-sm">✕</button>
            </div>
            <div id="chat-history"></div>
            <div class="popup-input-row">
                <input type="text" id="popup-input" placeholder="Command Otec..." autocomplete="off">
                <button id="popup-send" class="eye-btn-sm">▶</button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fallback menu icon (for when new-tab is blocked) -->
        <div id="eye-menu-toggle" class="hidden">
            <button id="menu-hamburger" class="eye-btn-sm">☰</button>
        </div>
        <nav id="eye-menu-drawer" class="glass-panel hidden">
            <a href="https://self.foreverbox.co.uk" class="eye-menu-link">Dashboard</a>
            <a href="https://self.foreverbox.co.uk/news-desk.php" class="eye-menu-link">News Desk</a>
            <a href="https://self.foreverbox.co.uk/posts.php" class="eye-menu-link">Posts</a>
            <a href="https://self.foreverbox.co.uk/knowledge.php" class="eye-menu-link">Knowledge</a>
            <a href="https://self.foreverbox.co.uk/chat_logs.php" class="eye-menu-link">Chat Logs</a>
            <a href="https://self.foreverbox.co.uk/settings.php" class="eye-menu-link">Settings</a>
        </nav>

        <!-- Controls Manual Modal (hidden by default) -->
        <div id="eye-help-modal" class="glass-panel hidden">
            <div class="popup-header">
                <span>SYSTEM MANUAL // FLIGHT & CONTROLS</span>
                <button id="close-help-modal" class="eye-btn-sm">✕</button>
            </div>
            <div class="help-content">
                <div class="help-section">
                    <div class="help-section-title">KEYBOARD FLIGHT (WASD)</div>
                    <div class="help-grid">
                        <span class="key-badge">W / S</span><span>Pan Up / Down (or Fly Ahead / Back)</span>
                        <span class="key-badge">A / D</span><span>Pan West / East (Left / Right)</span>
                        <span class="key-badge">Q / E</span><span>Rotate Heading (Look Left / Right)</span>
                        <span class="key-badge">Space</span><span>Ascend / Zoom Out</span>
                        <span class="key-badge">Shift</span><span>Descend / Zoom In</span>
                    </div>
                </div>
                <div class="help-section">
                    <div class="help-section-title">MOUSE CONTROLS</div>
                    <div class="help-grid">
                        <span class="key-badge">Left Drag</span><span>Orbit & Pan Globe</span>
                        <span class="key-badge">Right Drag / Scroll</span><span>Zoom Altitude</span>
                        <span class="key-badge">Middle Drag / Ctrl+Left</span><span>Tilt Horizon / Pitch</span>
                    </div>
                </div>
                <div class="help-section">
                    <div class="help-section-title">SENSOR OPTICS & COMMANDS</div>
                    <div class="help-grid">
                        <span class="key-badge">1 - 6</span><span>Optic Shader Filters (Normal, CRT, NVG, FLIR...)</span>
                        <span class="key-badge">G</span><span>Toggle HUD Detection Grid</span>
                        <span class="key-badge">H</span><span>Toggle Tactical HUD Overlay</span>
                        <span class="key-badge">C</span><span>Toggle Entity Cockpit Chase Cam</span>
                        <span class="key-badge">ESC</span><span>Untrack Entity / Close Popups & Modals</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Application JS (loaded as module) -->
    <script src="js/eye-app.js" type="module"></script>
</body>
</html>
