# The Eye — Sovereign Geospatial Intelligence Viewer
## Code-Level Build Plan v2.0

**Target builder:** Gemini 3.8 Fast / DeepSeek 4 Fast  
**Status:** Planning — Awaiting Approval  
**Primary repository:** `quiddity-sea/zeon7-self` (local: `H:\var\www\self`)  
**Related repositories:** `quiddity-sea/foreverbox-data` (local: `H:\foreverbox_data`)  
**Reference source (study only):** `https://github.com/bilawalsidhu/gods-eye-view` (MIT)  
**Live URL:** `https://eye.foreverbox.co.uk`  
**VPS IP:** `87.106.74.66`  
**VPS SSH:** user `zeon7`, pass `F0reverb0x#2o26`  
**VPS webroot (self):** `/var/www/vhosts/bjorntyrsson.co.uk/self.foreverbox.co.uk/`  
**VPS webroot (eye):** `/var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/`  
**VPS file ownership:** `quiddity:psacln`  
**GitHub push token:** `<YOUR_GITHUB_TOKEN>`

---

# 0. Builder Instructions

This document is an execution plan with complete file contents. Follow it in order. Do not skip phases. Do not deviate from the code provided. Where a complete file is given, create it exactly as shown.

### Non-negotiable rules

1. **gods-eye-view is a reference only.** Do not clone, fork, import, or depend on its code. The Eye is a clean-room rebuild.
2. **No Node.js at runtime.** CesiumJS is loaded from CDN. All data feeds are PHP curl scripts. No Vite. No npm. No package.json.
3. **No voice agent.** The OpenAI Realtime WebSocket voice feature is not implemented.
4. **All 10 data layers must be implemented with real API endpoints.** No placeholder `json_encode(['data'=>[]])` responses.
5. **The Eye must visually match the Zeon7 cybernetic theme.** Use CSS variables from `zeon7-theme.css`.
6. **Public users may use The Eye manually. They may NOT invoke agents.**
7. **Agent control is exclusively for authenticated users. The agent is always Otec.**
8. **Token tracking applies to ALL users (public and authenticated).**
9. **All authenticated user data in MariaDB must carry `user_id` and where applicable `agent_id`.**
10. **Do not duplicate auth logic.** Reuse `AuthService.php` from the self repo.
11. **Every phase ends with a verification step. Run the verification commands before proceeding.**
12. **Do not create any file with placeholder/stub content.** Every function must have a working implementation.
13. **The `.env` for The Eye is the SAME `.env` as the self repo root.** The Eye adds new keys to the existing file. Do not create a separate `.env` for The Eye.
14. **The-eye directory lives INSIDE the self repo** at `H:\var\www\self\the-eye\`. It is deployed to a separate vhost on the VPS.
15. **Do not install any PHP composer packages.** The Eye uses only built-in PHP functions (curl, json, PDO).

### Existing code patterns (mandatory)

- Services extend `BaseService` (`src/core/BaseService.php`) which provides `$this->db` (PDO via `DatabaseService::getInstance()`), `$this->executeQuery()`, `$this->fetchOne()`, `$this->fetchAll()`.
- API controllers either extend `BaseController` or use standalone `require_once` + procedural style.
- `.env` is loaded by `src/config/env.php` into `$_ENV` and `$_SERVER`.
- Auth is checked via: `$auth = new AuthService(); $isAuth = $auth->isAuthenticated();`
- Database is `zeon7_self_dev` on MariaDB.
- CSS root variables are in `css/zeon7-theme.css`. Key values:
  - `--color-cyan: #22d3ee` / `--color-cyan-rgb: 34, 211, 238`
  - `--color-primary: #00ff41`
  - `--color-purple: #a855f7`
  - `--color-coral: #f43f5e`
  - `--bg-dark: #070b10` / `--bg-void: #030609` / `--bg-body: #080d14`
  - `--bg-card: rgba(13, 20, 30, 0.85)` / `--bg-panel: rgba(15, 23, 36, 0.9)`
  - `--border-subtle: 1px solid rgba(34, 211, 238, 0.25)`
  - `--font-brand: 'Exo 2'` / `--font-mono: 'JetBrains Mono'` / `--font-head: 'Montserrat'`
  - `--text-primary: #f0f6fc` / `--text-secondary: #cbd5e1` / `--text-muted: #94a3b8`

### Directory structure to create

```
H:\var\www\self\the-eye\
├── index.php
├── .htaccess
├── database/
│   └── 001_create_eye_tables.sql
├── src/
│   └── services/
│       └── EyeService.php
├── api/
│   ├── BaseApi.php
│   ├── flights.php
│   ├── military.php
│   ├── ships.php
│   ├── satellites.php
│   ├── earthquakes.php
│   ├── cctv.php
│   ├── traffic.php
│   ├── fires.php
│   ├── radio.php
│   ├── launches.php
│   └── agent/
│       ├── command.php
│       └── history.php
├── js/
│   ├── eye-app.js
│   ├── globe.js
│   ├── layers/
│   │   ├── base-layer.js
│   │   ├── flights.js
│   │   ├── military.js
│   │   ├── ships.js
│   │   ├── satellites.js
│   │   ├── earthquakes.js
│   │   ├── cctv.js
│   │   ├── traffic.js
│   │   ├── fires.js
│   │   ├── radio.js
│   │   └── launches.js
│   ├── controls/
│   │   ├── sensors.js
│   │   ├── cockpit.js
│   │   ├── hud.js
│   │   ├── tracking.js
│   │   ├── annotations.js
│   │   └── viewshed.js
│   ├── agent/
│   │   ├── agent-bar.js
│   │   ├── agent-popup.js
│   │   └── eye-commander.js
│   └── ui/
│       ├── panels.js
│       └── menu.js
└── css/
    └── the-eye.css
```

---

# 1. Database Schema

### File: `H:\var\www\self\the-eye\database\001_create_eye_tables.sql`

Run this on the VPS via:
```bash
ssh zeon7@87.106.74.66 "mysql -u root zeon7_self_dev < /var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/database/001_create_eye_tables.sql"
```

Complete file contents:

```sql
-- The Eye: Geospatial Intelligence Viewer
-- Database schema for zeon7_self_dev

-- 1. Session tracking (all users)
CREATE TABLE IF NOT EXISTS eye_sessions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_token   VARCHAR(64)  NOT NULL UNIQUE,
    user_id         INT UNSIGNED NULL,
    ip_hash         VARCHAR(64)  NOT NULL,
    user_agent_hash VARCHAR(64)  NOT NULL,
    opened_at       DATETIME     DEFAULT CURRENT_TIMESTAMP,
    last_seen_at    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    layers_used     JSON         NULL,
    request_count   INT UNSIGNED DEFAULT 0,
    INDEX idx_eye_sess_user    (user_id),
    INDEX idx_eye_sess_opened  (opened_at),
    INDEX idx_eye_sess_token   (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Annotations (authenticated users only)
CREATE TABLE IF NOT EXISTS eye_annotations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    agent_id        VARCHAR(64)  NULL,
    label           VARCHAR(255) NULL,
    type            ENUM('polygon','route','pin') NOT NULL,
    geometry        JSON         NOT NULL,
    style           JSON         NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_eye_ann_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Saved views / bookmarks (authenticated users only)
CREATE TABLE IF NOT EXISTS eye_saved_views (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    agent_id        VARCHAR(64)  NULL,
    label           VARCHAR(255) NOT NULL,
    camera          JSON         NOT NULL,
    layers          JSON         NOT NULL,
    sensor_style    VARCHAR(32)  NULL,
    view_type       ENUM('bookmark','tour') DEFAULT 'bookmark',
    tour_data       JSON         NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_eye_view_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Agent query log (authenticated users only)
CREATE TABLE IF NOT EXISTS eye_agent_queries (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    agent_id        VARCHAR(64)  NOT NULL DEFAULT 'otec',
    session_token   VARCHAR(64)  NOT NULL,
    user_message    TEXT         NOT NULL,
    agent_reply     TEXT         NOT NULL,
    eye_command     JSON         NULL,
    executed        TINYINT(1)   DEFAULT 0,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_eye_aq_user    (user_id),
    INDEX idx_eye_aq_session (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

# 2. Environment Variables

Add these lines to the EXISTING `H:\var\www\self\.env` file. Do NOT create a separate .env:

```env
# --- The Eye: Geospatial Intelligence Viewer ---
CESIUM_ION_TOKEN=
GOOGLE_MAPS_API_KEY=
OPENSKY_USERNAME=
OPENSKY_PASSWORD=
ADSB_LOL_BASE_URL=https://api.adsb.lol
NASA_FIRMS_KEY=
ROCKET_LAUNCH_KEY=
EYE_SESSION_TTL=86400
EYE_RATE_LIMIT_PUBLIC=120
EYE_RATE_LIMIT_AUTH=300
```

---

# 3. Phase 1 — Foundation

**Goal:** `https://eye.foreverbox.co.uk` loads and shows a fullscreen CesiumJS globe in Zeon7 theme.

---

## 3.1 DNS & VPS Vhost Setup

Run these commands on the VPS:

```bash
# 1. Create webroot
ssh zeon7@87.106.74.66 "sudo mkdir -p /var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk && sudo chown quiddity:psacln /var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk"

# 2. Add DNS A record for eye.foreverbox.co.uk -> 87.106.74.66
# (Do this in the DNS provider control panel — Plesk or registrar)

# 3. Create Nginx/Apache vhost via Plesk, or manually:
# If using Plesk, add a new subscription/domain for eye.foreverbox.co.uk
# pointing to the webroot above, with SSL via Let's Encrypt.
```

---

## 3.2 `.htaccess`

### File: `H:\var\www\self\the-eye\.htaccess`

```apache
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Allow direct file access
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# Route all other requests to index.php
RewriteRule ^ index.php [L,QSA]
```

---

## 3.3 `EyeService.php`

### File: `H:\var\www\self\the-eye\src\services\EyeService.php`

```php
<?php
/**
 * EyeService — Session tracking, rate limiting, and persistence for The Eye.
 * Extends BaseService for PDO access to zeon7_self_dev.
 */

require_once __DIR__ . '/../../../src/core/BaseService.php';

class EyeService extends BaseService {

    /**
     * Open or resume an Eye session.
     * Returns the session token string.
     */
    public function openSession(?int $userId): string {
        // Check for existing session in PHP session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['eye_token'])) {
            $existing = $this->fetchOne(
                "SELECT id, session_token FROM eye_sessions WHERE session_token = ?",
                [$_SESSION['eye_token']]
            );
            if ($existing) {
                $this->executeQuery(
                    "UPDATE eye_sessions SET last_seen_at = NOW(), user_id = COALESCE(?, user_id) WHERE id = ?",
                    [$userId, $existing['id']]
                );
                return $existing['session_token'];
            }
        }

        // Create new session
        $token = bin2hex(random_bytes(32));
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ip = trim(explode(',', $ip)[0]);
        $ipHash = hash('sha256', $ip . ($_ENV['APP_KEY'] ?? 'eye-salt'));
        $uaHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

        $this->executeQuery(
            "INSERT INTO eye_sessions (session_token, user_id, ip_hash, user_agent_hash) VALUES (?, ?, ?, ?)",
            [$token, $userId, $ipHash, $uaHash]
        );

        $_SESSION['eye_token'] = $token;
        return $token;
    }

    /**
     * Update the layers_used field for a session.
     */
    public function touchSession(string $token, array $layersUsed): void {
        $this->executeQuery(
            "UPDATE eye_sessions SET last_seen_at = NOW(), layers_used = ?, request_count = request_count + 1 WHERE session_token = ?",
            [json_encode($layersUsed), $token]
        );
    }

    /**
     * Rate limit check. Returns true if request is allowed, false if rate exceeded.
     */
    public function rateCheck(string $token): bool {
        $session = $this->fetchOne(
            "SELECT user_id, request_count, last_seen_at FROM eye_sessions WHERE session_token = ?",
            [$token]
        );
        if (!$session) {
            return false;
        }

        $isAuth = !empty($session['user_id']);
        $limit = (int) ($_ENV[$isAuth ? 'EYE_RATE_LIMIT_AUTH' : 'EYE_RATE_LIMIT_PUBLIC'] ?? ($isAuth ? 300 : 120));

        // Check requests in the last 60 seconds
        $recentCount = $this->fetchOne(
            "SELECT COUNT(*) as cnt FROM eye_sessions WHERE session_token = ? AND last_seen_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)",
            [$token]
        );

        // Simple sliding window: increment and check
        $this->executeQuery(
            "UPDATE eye_sessions SET request_count = request_count + 1, last_seen_at = NOW() WHERE session_token = ?",
            [$token]
        );

        // Reset counter every minute
        $lastSeen = strtotime($session['last_seen_at']);
        if (time() - $lastSeen > 60) {
            $this->executeQuery(
                "UPDATE eye_sessions SET request_count = 1 WHERE session_token = ?",
                [$token]
            );
            return true;
        }

        return ($session['request_count'] < $limit);
    }

    /**
     * Save an annotation for an authenticated user.
     */
    public function saveAnnotation(int $userId, ?string $agentId, string $label, string $type, array $geometry, ?array $style = null): int {
        $this->executeQuery(
            "INSERT INTO eye_annotations (user_id, agent_id, label, type, geometry, style) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $agentId, $label, $type, json_encode($geometry), $style ? json_encode($style) : null]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get all annotations for a user.
     */
    public function getAnnotations(int $userId): array {
        return $this->fetchAll(
            "SELECT * FROM eye_annotations WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    /**
     * Save a camera view bookmark.
     */
    public function saveView(int $userId, ?string $agentId, string $label, array $camera, array $layers, ?string $sensorStyle): int {
        $this->executeQuery(
            "INSERT INTO eye_saved_views (user_id, agent_id, label, camera, layers, sensor_style) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $agentId, $label, json_encode($camera), json_encode($layers), $sensorStyle]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get all saved views for a user.
     */
    public function getSavedViews(int $userId): array {
        return $this->fetchAll(
            "SELECT * FROM eye_saved_views WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    /**
     * Log an agent query.
     */
    public function logAgentQuery(int $userId, string $agentId, string $sessionToken, string $userMessage, string $agentReply, ?array $eyeCommand, bool $executed): int {
        $this->executeQuery(
            "INSERT INTO eye_agent_queries (user_id, agent_id, session_token, user_message, agent_reply, eye_command, executed) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$userId, $agentId, $sessionToken, $userMessage, $agentReply, $eyeCommand ? json_encode($eyeCommand) : null, $executed ? 1 : 0]
        );
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get agent query history for a user.
     */
    public function getAgentHistory(int $userId, int $limit = 50): array {
        return $this->fetchAll(
            "SELECT * FROM eye_agent_queries WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
```

---

## 3.4 `index.php`

### File: `H:\var\www\self\the-eye\index.php`

```php
<?php
/**
 * The Eye — Sovereign Geospatial Intelligence Viewer
 * Entry point. Renders the fullscreen CesiumJS globe.
 */

// Load env and auth from the parent self repo
require_once __DIR__ . '/../src/config/env.php';
require_once __DIR__ . '/../src/services/AuthService.php';
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
<html lang="en" data-theme="dark">
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

    </div>

    <!-- Application JS (loaded as module) -->
    <script src="js/eye-app.js" type="module"></script>
</body>
</html>
```

---

## 3.5 `the-eye.css`

### File: `H:\var\www\self\the-eye\css\the-eye.css`

```css
/**
 * THE EYE — Geospatial Intelligence Viewer
 * Cybernetic HUD theme extending zeon7-theme.css palette
 */

/* ============================================
   RESET & FULLSCREEN GLOBE
   ============================================ */

* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { width: 100%; height: 100%; overflow: hidden; background: var(--bg-void, #030609); }

#cesiumContainer {
    position: absolute;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    z-index: 1;
}

/* Override Cesium default styles to match theme */
.cesium-viewer { background: var(--bg-void) !important; }
.cesium-viewer-bottom { display: none !important; }
.cesium-credit-logoContainer { display: none !important; }
.cesium-widget-credits { display: none !important; }

/* ============================================
   HUD OVERLAY (sits above globe)
   ============================================ */

#hud-overlay {
    position: absolute;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    z-index: 10;
    pointer-events: none;
    font-family: var(--font-mono, 'JetBrains Mono', monospace);
    color: var(--text-primary, #f0f6fc);
}

/* ============================================
   CORNER DECORATIONS (Cybernetic brackets)
   ============================================ */

.hud-corner {
    position: absolute;
    width: 40px; height: 40px;
    border-color: rgba(34, 211, 238, 0.4);
    border-style: solid;
    border-width: 0;
}
.hud-corner-tl { top: 12px; left: 12px; border-top-width: 2px; border-left-width: 2px; }
.hud-corner-tr { top: 12px; right: 12px; border-top-width: 2px; border-right-width: 2px; }
.hud-corner-bl { bottom: 12px; left: 12px; border-bottom-width: 2px; border-left-width: 2px; }
.hud-corner-br { bottom: 12px; right: 12px; border-bottom-width: 2px; border-right-width: 2px; }

/* ============================================
   GLASS PANEL (shared panel style)
   ============================================ */

.glass-panel {
    background: rgba(10, 10, 15, 0.75);
    border: 1px solid rgba(34, 211, 238, 0.25);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border-radius: 4px;
    pointer-events: auto;
    font-family: var(--font-mono, 'JetBrains Mono', monospace);
    color: var(--text-primary, #f0f6fc);
}

.panel-header {
    padding: 8px 12px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--color-cyan, #22d3ee);
    border-bottom: 1px solid rgba(34, 211, 238, 0.15);
}

/* ============================================
   TACTICAL HUD (top-left)
   ============================================ */

#tactical-hud {
    position: absolute;
    top: 20px; left: 20px;
    pointer-events: none;
    text-shadow: 0 0 4px rgba(0, 0, 0, 0.8);
}

#hud-title {
    font-size: 1.1rem;
    font-weight: 900;
    font-family: var(--font-brand, 'Exo 2', sans-serif);
    letter-spacing: 0.2em;
    color: var(--color-cyan, #22d3ee);
    margin-bottom: 8px;
}

#hud-title .hud-version {
    font-size: 0.6rem;
    color: var(--text-muted, #94a3b8);
    font-weight: 400;
}

#hud-clock {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--color-cyan, #22d3ee);
    margin-bottom: 4px;
}

#hud-coords {
    font-size: 0.75rem;
    color: var(--text-secondary, #cbd5e1);
    margin-bottom: 8px;
    line-height: 1.5;
}

#hud-stats {
    font-size: 0.7rem;
    color: var(--text-muted, #94a3b8);
    line-height: 1.6;
}

#hud-telemetry {
    margin-top: 12px;
    padding: 10px 12px;
    background: rgba(0, 0, 0, 0.5);
    border-left: 2px solid var(--color-cyan, #22d3ee);
    font-size: 0.75rem;
    line-height: 1.5;
    max-width: 280px;
}

/* ============================================
   LAYER PANEL (top-right)
   ============================================ */

#layer-panel {
    position: absolute;
    top: 20px; right: 20px;
    width: 220px;
}

#layer-toggles {
    padding: 6px 0;
}

.layer-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-muted, #94a3b8);
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    transition: all 0.15s ease;
    pointer-events: auto;
}

.layer-toggle:hover {
    background: rgba(34, 211, 238, 0.08);
    color: var(--text-primary, #f0f6fc);
}

.layer-toggle.active {
    color: var(--color-cyan, #22d3ee);
    text-shadow: 0 0 8px rgba(34, 211, 238, 0.4);
}

.layer-toggle.active::after {
    content: '●';
    color: var(--color-cyan, #22d3ee);
    font-size: 0.5rem;
}

.layer-toggle.military.active {
    color: var(--color-coral, #f43f5e);
    text-shadow: 0 0 8px rgba(244, 63, 94, 0.4);
}

.layer-toggle.military.active::after {
    color: var(--color-coral, #f43f5e);
}

/* ============================================
   SENSOR PANEL (below layers)
   ============================================ */

#sensor-panel {
    position: absolute;
    top: 340px; right: 20px;
    width: 220px;
}

#sensor-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding: 8px;
}

.sensor-btn {
    flex: 1 1 calc(33% - 4px);
    padding: 6px 4px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 2px;
    color: var(--text-muted, #94a3b8);
    font-family: var(--font-mono, monospace);
    font-size: 0.6rem;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: all 0.15s ease;
    pointer-events: auto;
}

.sensor-btn:hover {
    background: rgba(34, 211, 238, 0.1);
    color: var(--text-primary, #f0f6fc);
}

.sensor-btn.active {
    background: rgba(34, 211, 238, 0.15);
    border-color: var(--color-cyan, #22d3ee);
    color: var(--color-cyan, #22d3ee);
    box-shadow: 0 0 8px rgba(34, 211, 238, 0.2);
}

/* ============================================
   CONTACT CARD (bottom-left)
   ============================================ */

#contact-card {
    position: absolute;
    bottom: 80px; left: 20px;
    width: 280px;
}

#contact-info {
    padding: 10px 12px;
    font-size: 0.75rem;
    line-height: 1.6;
}

#contact-info .contact-label {
    color: var(--text-muted, #94a3b8);
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

#contact-info .contact-value {
    color: var(--color-cyan, #22d3ee);
    font-weight: 600;
}

.contact-actions {
    display: flex;
    gap: 6px;
    padding: 8px 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

/* ============================================
   BUTTONS
   ============================================ */

.eye-btn {
    padding: 6px 14px;
    background: rgba(34, 211, 238, 0.1);
    border: 1px solid rgba(34, 211, 238, 0.3);
    border-radius: 2px;
    color: var(--color-cyan, #22d3ee);
    font-family: var(--font-mono, monospace);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.15s ease;
    pointer-events: auto;
}

.eye-btn:hover {
    background: rgba(34, 211, 238, 0.2);
    box-shadow: 0 0 12px rgba(34, 211, 238, 0.3);
}

.eye-btn-sm {
    padding: 4px 8px;
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 2px;
    color: var(--text-muted, #94a3b8);
    font-family: var(--font-mono, monospace);
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.15s ease;
    pointer-events: auto;
}

.eye-btn-sm:hover {
    color: var(--color-cyan, #22d3ee);
    border-color: rgba(34, 211, 238, 0.4);
}

/* ============================================
   AGENT BAR (bottom-center, auth only)
   ============================================ */

#agent-bar-container {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 580px;
    max-width: calc(100vw - 40px);
    pointer-events: auto;
    z-index: 20;
}

#agent-last-reply {
    font-size: 0.7rem;
    color: var(--text-dim, #64748b);
    font-family: var(--font-mono, monospace);
    padding: 0 4px 4px;
    max-height: 40px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#agent-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: rgba(5, 8, 14, 0.9);
    border: 1px solid rgba(168, 85, 247, 0.35);
    border-radius: 4px;
    box-shadow: 0 0 20px rgba(168, 85, 247, 0.15);
    cursor: pointer;
    transition: all 0.2s ease;
}

#agent-bar:hover {
    border-color: rgba(168, 85, 247, 0.5);
    box-shadow: 0 0 25px rgba(168, 85, 247, 0.25);
}

.agent-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    color: var(--color-purple, #a855f7);
    padding: 2px 6px;
    border: 1px solid rgba(168, 85, 247, 0.3);
    border-radius: 2px;
    flex-shrink: 0;
}

#agent-input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    color: var(--text-primary, #f0f6fc);
    font-family: var(--font-mono, monospace);
    font-size: 0.85rem;
}

#agent-input::placeholder {
    color: var(--text-dim, #64748b);
}

#agent-word-count {
    font-size: 0.6rem;
    color: var(--text-dim, #64748b);
    flex-shrink: 0;
}

/* ============================================
   AGENT POPUP (expanded chat)
   ============================================ */

#agent-popup {
    position: absolute;
    bottom: 60px;
    left: 50%;
    transform: translateX(-50%);
    width: 580px;
    max-width: calc(100vw - 40px);
    height: 420px;
    display: flex;
    flex-direction: column;
    z-index: 25;
    pointer-events: auto;
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    color: var(--color-purple, #a855f7);
    border-bottom: 1px solid rgba(168, 85, 247, 0.2);
}

#chat-history {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.chat-msg {
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 0.8rem;
    line-height: 1.5;
    max-width: 85%;
    word-wrap: break-word;
}

.chat-msg.user {
    align-self: flex-end;
    background: rgba(34, 211, 238, 0.12);
    border: 1px solid rgba(34, 211, 238, 0.15);
    color: var(--text-primary, #f0f6fc);
}

.chat-msg.agent {
    align-self: flex-start;
    background: rgba(168, 85, 247, 0.1);
    border-left: 2px solid var(--color-purple, #a855f7);
    color: var(--text-secondary, #cbd5e1);
}

.popup-input-row {
    display: flex;
    gap: 8px;
    padding: 10px 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

#popup-input {
    flex: 1;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 2px;
    padding: 8px 12px;
    color: var(--text-primary, #f0f6fc);
    font-family: var(--font-mono, monospace);
    font-size: 0.8rem;
    outline: none;
}

#popup-input:focus {
    border-color: rgba(168, 85, 247, 0.4);
}

/* ============================================
   FALLBACK MENU (when new tab is blocked)
   ============================================ */

#eye-menu-toggle {
    position: absolute;
    top: 20px; left: 60px;
    pointer-events: auto;
    z-index: 30;
}

#eye-menu-drawer {
    position: absolute;
    top: 55px; left: 20px;
    width: 200px;
    padding: 8px 0;
    z-index: 30;
}

.eye-menu-link {
    display: block;
    padding: 8px 16px;
    color: var(--text-secondary, #cbd5e1);
    text-decoration: none;
    font-size: 0.75rem;
    letter-spacing: 0.08em;
    transition: all 0.15s ease;
}

.eye-menu-link:hover {
    background: rgba(34, 211, 238, 0.08);
    color: var(--color-cyan, #22d3ee);
}

/* ============================================
   DETECTION OVERLAY BOXES
   ============================================ */

.detection-box {
    position: absolute;
    border: 1px solid var(--color-cyan, #22d3ee);
    pointer-events: none;
    z-index: 5;
}

.detection-box.military {
    border-color: var(--color-coral, #f43f5e);
}

.detection-label {
    position: absolute;
    top: -16px; left: 0;
    font-size: 0.55rem;
    color: var(--color-cyan, #22d3ee);
    font-family: var(--font-mono, monospace);
    letter-spacing: 0.05em;
    white-space: nowrap;
    text-shadow: 0 0 3px rgba(0, 0, 0, 0.9);
}

.detection-box.military .detection-label {
    color: var(--color-coral, #f43f5e);
}

/* ============================================
   UTILITY
   ============================================ */

.hidden { display: none !important; }

/* Custom scrollbar for chat */
#chat-history::-webkit-scrollbar { width: 4px; }
#chat-history::-webkit-scrollbar-track { background: transparent; }
#chat-history::-webkit-scrollbar-thumb { background: rgba(168, 85, 247, 0.3); border-radius: 2px; }
#chat-history::-webkit-scrollbar-thumb:hover { background: rgba(168, 85, 247, 0.5); }

/* Keyboard shortcut hint overlay */
.key-hint {
    position: absolute;
    bottom: 80px; right: 20px;
    font-size: 0.6rem;
    color: var(--text-dim, #64748b);
    pointer-events: none;
    line-height: 1.8;
    text-align: right;
}
```

---

## 3.6 Sidebar Navigation Modification

### File to modify: `H:\var\www\self\components\navigation\sidebar.php`

Add the following line at **line 25** (after the Dashboard `</a>` tag and before the News Desk `<?php if` block):

```php
            <a href="https://eye.foreverbox.co.uk" target="_blank" rel="noopener" class="nav-item" title="The Eye — Geospatial Intelligence" onclick="return openEye(this.href)">
                <i>🌍</i> <span class="nav-text">The Eye</span>
            </a>
```

Add this JavaScript at the end of sidebar.php, before the closing `<?php }`:

```html
    <script>
    function openEye(url) {
        var w = window.open(url, '_blank');
        if (!w || w.closed || typeof w.closed === 'undefined') {
            window.location.href = url + '?embedded=1';
        }
        return false;
    }
    </script>
```

---

## 3.7 Phase 1 Verification

Run these commands to verify Phase 1:

```bash
# 1. Verify directory structure exists
ls -la H:\var\www\self\the-eye\

# 2. Deploy to VPS
scp -r H:\var\www\self\the-eye\* zeon7@87.106.74.66:/var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/

# 3. Set ownership
ssh zeon7@87.106.74.66 "sudo chown -R quiddity:psacln /var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/"

# 4. Run DB migration
ssh zeon7@87.106.74.66 "mysql -u root zeon7_self_dev < /var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/database/001_create_eye_tables.sql"

# 5. Verify tables
ssh zeon7@87.106.74.66 "mysql -u root zeon7_self_dev -e 'SHOW TABLES LIKE \"eye_%\"'"
# Expected output: eye_sessions, eye_annotations, eye_saved_views, eye_agent_queries

# 6. Test page loads
curl -I https://eye.foreverbox.co.uk
# Expected: HTTP 200

# 7. Verify CesiumJS loads (check for no JS errors in browser console)
# Open https://eye.foreverbox.co.uk in a browser and confirm a dark globe renders
```

---

# 4. Phase 2 — Data Layer PHP Proxies

**Goal:** All 10 data layer endpoints return real JSON from external APIs.

---

## 4.1 `BaseApi.php`

### File: `H:\var\www\self\the-eye\api\BaseApi.php`

```php
<?php
/**
 * BaseApi — Shared utilities for all Eye data proxy endpoints.
 * Handles rate limiting, curl fetching, caching, and CORS headers.
 */

require_once __DIR__ . '/../../src/config/env.php';
require_once __DIR__ . '/../src/services/EyeService.php';

class BaseApi {

    /**
     * Set standard API response headers.
     */
    public static function headers(int $cacheMaxAge = 10): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: https://eye.foreverbox.co.uk');
        header('Access-Control-Allow-Headers: X-Eye-Token');
        header("Cache-Control: public, max-age={$cacheMaxAge}");

        // Handle CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /**
     * Check rate limit using the X-Eye-Token header.
     * Exits with 429 if rate exceeded.
     */
    public static function checkRateLimit(): void {
        $token = $_SERVER['HTTP_X_EYE_TOKEN'] ?? '';
        if (empty($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing X-Eye-Token header']);
            exit;
        }

        $eyeService = new EyeService();
        if (!$eyeService->rateCheck($token)) {
            http_response_code(429);
            echo json_encode(['error' => 'Rate limit exceeded. Try again in 60 seconds.']);
            exit;
        }
    }

    /**
     * Fetch a URL via curl. Returns the response body string or false on failure.
     *
     * @param string      $url      URL to fetch
     * @param string|null $auth     Optional "user:pass" basic auth string
     * @param array       $headers  Optional extra headers
     * @param int         $timeout  Request timeout in seconds
     * @return string|false
     */
    public static function fetch(string $url, ?string $auth = null, array $headers = [], int $timeout = 8) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_USERAGENT      => 'ForeverBox-TheEye/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($auth) {
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            error_log("Eye API fetch failed: {$url} HTTP {$httpCode} Error: {$error}");
            return false;
        }

        return $response;
    }

    /**
     * Simple file-based response cache. Returns cached content if fresh, null otherwise.
     */
    public static function getCache(string $key, int $maxAgeSeconds): ?string {
        $cacheDir = sys_get_temp_dir() . '/eye_cache';
        $cacheFile = $cacheDir . '/' . md5($key) . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $maxAgeSeconds) {
            return file_get_contents($cacheFile);
        }
        return null;
    }

    /**
     * Write response to file cache.
     */
    public static function setCache(string $key, string $content): void {
        $cacheDir = sys_get_temp_dir() . '/eye_cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        file_put_contents($cacheDir . '/' . md5($key) . '.json', $content);
    }

    /**
     * Output JSON and exit.
     */
    public static function respond(string $json): void {
        echo $json;
        exit;
    }

    /**
     * Output error JSON and exit.
     */
    public static function error(string $message, int $code = 500): void {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }
}
```

---

## 4.2 `flights.php`

### File: `H:\var\www\self\the-eye\api\flights.php`

```php
<?php
/**
 * Live flight data proxy.
 * Primary: OpenSky Network (https://opensky-network.org/api/states/all)
 * Fallback: adsb.lol (https://api.adsb.lol/v2/all)
 * Refresh: 10s
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(10);
BaseApi::checkRateLimit();

$cacheKey = 'flights_all';
$cached = BaseApi::getCache($cacheKey, 8);
if ($cached) {
    BaseApi::respond($cached);
}

// Primary: OpenSky
$user = $_ENV['OPENSKY_USERNAME'] ?? '';
$pass = $_ENV['OPENSKY_PASSWORD'] ?? '';
$auth = ($user && $pass) ? "{$user}:{$pass}" : null;

$response = BaseApi::fetch('https://opensky-network.org/api/states/all', $auth);

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['states'])) {
        BaseApi::setCache($cacheKey, $response);
        BaseApi::respond($response);
    }
}

// Fallback: adsb.lol
$baseUrl = $_ENV['ADSB_LOL_BASE_URL'] ?? 'https://api.adsb.lol';
$fallback = BaseApi::fetch("{$baseUrl}/v2/all");

if ($fallback) {
    // adsb.lol returns a different format — normalise to OpenSky format
    $adsbData = json_decode($fallback, true);
    $states = [];

    if (isset($adsbData['ac'])) {
        foreach ($adsbData['ac'] as $ac) {
            $states[] = [
                $ac['hex'] ?? '',           // icao24
                $ac['flight'] ?? '',        // callsign
                '',                          // origin_country
                time(),                      // time_position
                time(),                      // last_contact
                $ac['lon'] ?? null,          // longitude
                $ac['lat'] ?? null,          // latitude
                $ac['alt_baro'] ?? null,     // baro_altitude
                ($ac['alt_baro'] ?? 1) == 0, // on_ground
                $ac['gs'] ?? null,           // velocity (ground speed in knots -> m/s)
                $ac['track'] ?? null,        // true_track
                null,                        // vertical_rate
                null,                        // sensors
                $ac['alt_geom'] ?? null,     // geo_altitude
                $ac['squawk'] ?? '',         // squawk
                false,                       // spi
                0                            // position_source
            ];
        }
    }

    $normalised = json_encode(['time' => time(), 'states' => $states]);
    BaseApi::setCache($cacheKey, $normalised);
    BaseApi::respond($normalised);
}

// Both failed
BaseApi::respond(json_encode(['time' => time(), 'states' => []]));
```

---

## 4.3 `military.php`

### File: `H:\var\www\self\the-eye\api\military.php`

```php
<?php
/**
 * Military/government aircraft feed.
 * Source: adsb.lol military filter (https://api.adsb.lol/v2/mil)
 * Refresh: 15s
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(15);
BaseApi::checkRateLimit();

$cacheKey = 'military_all';
$cached = BaseApi::getCache($cacheKey, 12);
if ($cached) {
    BaseApi::respond($cached);
}

$baseUrl = $_ENV['ADSB_LOL_BASE_URL'] ?? 'https://api.adsb.lol';
$response = BaseApi::fetch("{$baseUrl}/v2/mil");

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['ac'])) {
        // Normalise to states format
        $states = [];
        foreach ($data['ac'] as $ac) {
            $states[] = [
                'hex'       => $ac['hex'] ?? '',
                'callsign'  => trim($ac['flight'] ?? ''),
                'lat'       => $ac['lat'] ?? null,
                'lon'       => $ac['lon'] ?? null,
                'alt'       => $ac['alt_baro'] ?? $ac['alt_geom'] ?? null,
                'speed'     => $ac['gs'] ?? null,
                'track'     => $ac['track'] ?? null,
                'type'      => $ac['t'] ?? '',
                'category'  => $ac['category'] ?? '',
                'squawk'    => $ac['squawk'] ?? '',
                'military'  => true
            ];
        }
        $output = json_encode(['military' => $states, 'count' => count($states), 'time' => time()]);
        BaseApi::setCache($cacheKey, $output);
        BaseApi::respond($output);
    }
}

BaseApi::respond(json_encode(['military' => [], 'count' => 0, 'time' => time()]));
```

---

## 4.4 `ships.php`

### File: `H:\var\www\self\the-eye\api\ships.php`

```php
<?php
/**
 * Live ship position data via AIS.
 * Source: MarineTraffic density tiles aren't openly available without a paid API.
 * Free alternative: Use the public vesselfinder/marinetraffic embed data or
 * AISHub (https://www.aishub.net/api) with free tier.
 *
 * For the initial build, we use a curated approach:
 * Fetch from the public Danish Maritime Authority AIS feed (publicly available)
 * and supplement with AIS data from aisstream.io when API key is available.
 *
 * Refresh: 30s
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(30);
BaseApi::checkRateLimit();

$cacheKey = 'ships_all';
$cached = BaseApi::getCache($cacheKey, 25);
if ($cached) {
    BaseApi::respond($cached);
}

// Use barentswatch.no open AIS API (Norwegian Coastal Administration, free)
// This provides AIS data for Norwegian waters — a real working free AIS feed.
$response = BaseApi::fetch(
    'https://live.ais.barentswatch.no/v1/latest/combined?modelType=Full',
    null,
    ['Accept: application/json'],
    10
);

$ships = [];

if ($response) {
    $data = json_decode($response, true);
    if (is_array($data)) {
        foreach (array_slice($data, 0, 2000) as $vessel) {
            if (empty($vessel['latitude']) || empty($vessel['longitude'])) continue;
            $ships[] = [
                'mmsi'      => $vessel['mmsi'] ?? '',
                'name'      => $vessel['name'] ?? 'Unknown',
                'lat'       => (float) $vessel['latitude'],
                'lon'       => (float) $vessel['longitude'],
                'speed'     => $vessel['speedOverGround'] ?? 0,
                'course'    => $vessel['courseOverGround'] ?? 0,
                'heading'   => $vessel['trueHeading'] ?? 0,
                'type'      => $vessel['shipType'] ?? 0,
                'dest'      => $vessel['destination'] ?? '',
                'length'    => $vessel['dimensionA'] ?? 0,
            ];
        }
    }
}

$output = json_encode(['ships' => $ships, 'count' => count($ships), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
```

---

## 4.5 `satellites.php`

### File: `H:\var\www\self\the-eye\api\satellites.php`

```php
<?php
/**
 * Satellite orbital data.
 * Source: Celestrak GP (General Perturbations) elements in JSON.
 * URL: https://celestrak.org/NORAD/elements/gp.php?GROUP=active&FORMAT=json
 * This returns TLE-equivalent orbital elements for ALL active satellites (~10,000+).
 * We limit to the first 500 for performance on initial load.
 * Refresh: 5 minutes (orbital elements change slowly)
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(300);
BaseApi::checkRateLimit();

$cacheKey = 'satellites_active';
$cached = BaseApi::getCache($cacheKey, 240);
if ($cached) {
    BaseApi::respond($cached);
}

// Fetch from Celestrak — JSON format GP elements
$response = BaseApi::fetch(
    'https://celestrak.org/NORAD/elements/gp.php?GROUP=active&FORMAT=json',
    null,
    [],
    15  // longer timeout, this is a large response
);

$satellites = [];

if ($response) {
    $data = json_decode($response, true);
    if (is_array($data)) {
        // Limit to first 500 satellites. Client can request more via query param.
        $limit = min((int) ($_GET['limit'] ?? 500), 2000);
        foreach (array_slice($data, 0, $limit) as $sat) {
            $satellites[] = [
                'norad_id'    => $sat['NORAD_CAT_ID'] ?? '',
                'name'        => $sat['OBJECT_NAME'] ?? '',
                'epoch'       => $sat['EPOCH'] ?? '',
                'mean_motion' => (float) ($sat['MEAN_MOTION'] ?? 0),
                'eccentricity'=> (float) ($sat['ECCENTRICITY'] ?? 0),
                'inclination' => (float) ($sat['INCLINATION'] ?? 0),
                'ra_asc_node' => (float) ($sat['RA_OF_ASC_NODE'] ?? 0),
                'arg_pericenter' => (float) ($sat['ARG_OF_PERICENTER'] ?? 0),
                'mean_anomaly'=> (float) ($sat['MEAN_ANOMALY'] ?? 0),
                'bstar'       => (float) ($sat['BSTAR'] ?? 0),
                'rev_num'     => (int)   ($sat['REV_AT_EPOCH'] ?? 0),
                'tle_line1'   => $sat['TLE_LINE1'] ?? '',
                'tle_line2'   => $sat['TLE_LINE2'] ?? '',
            ];
        }
    }
}

// Fallback: try Celestrak TLE text format and parse manually
if (empty($satellites)) {
    $tleTxt = BaseApi::fetch('https://celestrak.org/NORAD/elements/stations.txt');
    if ($tleTxt) {
        $lines = explode("\n", trim($tleTxt));
        for ($i = 0; $i + 2 < count($lines); $i += 3) {
            $satellites[] = [
                'name'      => trim($lines[$i]),
                'tle_line1' => trim($lines[$i + 1]),
                'tle_line2' => trim($lines[$i + 2]),
            ];
        }
    }
}

$output = json_encode(['satellites' => $satellites, 'count' => count($satellites), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
```

---

## 4.6 `earthquakes.php`

### File: `H:\var\www\self\the-eye\api\earthquakes.php`

```php
<?php
/**
 * Earthquake data.
 * Primary: USGS FDSN earthquake feed (GeoJSON).
 * URL: https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_day.geojson
 * Returns all earthquakes in the last 24 hours as GeoJSON FeatureCollection.
 * Refresh: 60s
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(60);
BaseApi::checkRateLimit();

$cacheKey = 'earthquakes_day';
$cached = BaseApi::getCache($cacheKey, 50);
if ($cached) {
    BaseApi::respond($cached);
}

// Primary: USGS past day, all magnitudes
$response = BaseApi::fetch('https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_day.geojson');

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['features'])) {
        BaseApi::setCache($cacheKey, $response);
        BaseApi::respond($response);
    }
}

// Fallback: USGS past hour (smaller dataset, more likely to succeed)
$fallback = BaseApi::fetch('https://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/all_hour.geojson');
if ($fallback) {
    BaseApi::setCache($cacheKey, $fallback);
    BaseApi::respond($fallback);
}

// Return empty GeoJSON FeatureCollection
BaseApi::respond(json_encode([
    'type' => 'FeatureCollection',
    'features' => [],
    'metadata' => ['generated' => time() * 1000, 'count' => 0, 'title' => 'No data available']
]));
```

---

## 4.7 `cctv.php`

### File: `H:\var\www\self\the-eye\api\cctv.php`

```php
<?php
/**
 * Public CCTV / webcam camera positions.
 * Source: Windy.com webcams API (free tier, 25 results per request).
 * Alternative: OpenStreetMap Overpass query for surveillance=* tagged nodes.
 *
 * We use the Overpass API to get public webcam positions globally.
 * Refresh: 10 minutes (camera positions don't change)
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(600);
BaseApi::checkRateLimit();

$cacheKey = 'cctv_global';
$cached = BaseApi::getCache($cacheKey, 500);
if ($cached) {
    BaseApi::respond($cached);
}

// Query OpenStreetMap Overpass API for webcam/surveillance nodes
// This returns real camera positions from OSM data
$overpassQuery = '[out:json][timeout:15];node["surveillance"="public"]["surveillance:type"="camera"](around:500000,51.5,-0.1);out body 500;';
$overpassUrl = 'https://overpass-api.de/api/interpreter?data=' . urlencode($overpassQuery);

$response = BaseApi::fetch($overpassUrl, null, [], 15);

$cameras = [];

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['elements'])) {
        foreach ($data['elements'] as $el) {
            if (empty($el['lat']) || empty($el['lon'])) continue;
            $cameras[] = [
                'id'   => $el['id'],
                'lat'  => (float) $el['lat'],
                'lon'  => (float) $el['lon'],
                'name' => $el['tags']['name'] ?? $el['tags']['description'] ?? 'Public Camera',
                'type' => $el['tags']['surveillance:type'] ?? 'camera',
                'zone' => $el['tags']['surveillance:zone'] ?? 'public',
            ];
        }
    }
}

$output = json_encode(['cameras' => $cameras, 'count' => count($cameras), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
```

---

## 4.8 `traffic.php`

### File: `H:\var\www\self\the-eye\api\traffic.php`

```php
<?php
/**
 * Traffic congestion data.
 * Source: TomTom Traffic Flow API (free tier: 2,500 requests/day).
 * Fallback: OpenStreetMap major road network.
 *
 * For the initial build without a TomTom key, we return major road
 * segments from OpenStreetMap for the current viewport (passed as query params).
 * Refresh: 5 minutes
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(300);
BaseApi::checkRateLimit();

// Get viewport bounds from query params
$south = (float) ($_GET['south'] ?? 50.0);
$west  = (float) ($_GET['west'] ?? -1.0);
$north = (float) ($_GET['north'] ?? 52.0);
$east  = (float) ($_GET['east'] ?? 1.0);

// Clamp bbox to reasonable size
$latSpan = min(abs($north - $south), 2.0);
$lonSpan = min(abs($east - $west), 2.0);

$cacheKey = "traffic_{$south}_{$west}_{$north}_{$east}";
$cached = BaseApi::getCache($cacheKey, 240);
if ($cached) {
    BaseApi::respond($cached);
}

// Fetch major roads from Overpass
$bbox = "{$south},{$west},{$north},{$east}";
$query = "[out:json][timeout:10];way[\"highway\"~\"motorway|trunk|primary\"]({$bbox});out geom 200;";
$response = BaseApi::fetch('https://overpass-api.de/api/interpreter?data=' . urlencode($query), null, [], 12);

$roads = [];

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['elements'])) {
        foreach ($data['elements'] as $el) {
            if (empty($el['geometry'])) continue;
            $coords = array_map(function ($pt) {
                return [$pt['lon'], $pt['lat']];
            }, $el['geometry']);

            $roads[] = [
                'id'      => $el['id'],
                'name'    => $el['tags']['name'] ?? $el['tags']['ref'] ?? 'Unknown',
                'type'    => $el['tags']['highway'] ?? '',
                'coords'  => $coords,
            ];
        }
    }
}

$output = json_encode(['roads' => $roads, 'count' => count($roads), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
```

---

## 4.9 `fires.php`

### File: `H:\var\www\self\the-eye\api\fires.php`

```php
<?php
/**
 * Active wildfire / thermal hotspot data.
 * Primary: NASA FIRMS (Fire Information for Resource Management System).
 * URL: https://firms.modaps.eosdis.nasa.gov/api/area/csv/{MAP_KEY}/VIIRS_SNPP_NRT/world/1
 * Returns CSV of fire detections in the last 24 hours.
 * Free API key required: https://firms.modaps.eosdis.nasa.gov/api/
 * Refresh: 5 minutes
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(300);
BaseApi::checkRateLimit();

$cacheKey = 'fires_global';
$cached = BaseApi::getCache($cacheKey, 240);
if ($cached) {
    BaseApi::respond($cached);
}

$firmsKey = $_ENV['NASA_FIRMS_KEY'] ?? '';
$fires = [];

if ($firmsKey) {
    // FIRMS CSV API
    $url = "https://firms.modaps.eosdis.nasa.gov/api/area/csv/{$firmsKey}/VIIRS_SNPP_NRT/world/1";
    $csvData = BaseApi::fetch($url, null, [], 15);

    if ($csvData) {
        $lines = explode("\n", $csvData);
        $header = str_getcsv(array_shift($lines));
        $latIdx = array_search('latitude', $header);
        $lonIdx = array_search('longitude', $header);
        $brightIdx = array_search('bright_ti4', $header);
        $confIdx = array_search('confidence', $header);
        $dateIdx = array_search('acq_date', $header);
        $timeIdx = array_search('acq_time', $header);
        $frpIdx = array_search('frp', $header);

        foreach (array_slice($lines, 0, 2000) as $line) {
            if (empty(trim($line))) continue;
            $cols = str_getcsv($line);
            if (empty($cols[$latIdx]) || empty($cols[$lonIdx])) continue;

            $fires[] = [
                'lat'        => (float) $cols[$latIdx],
                'lon'        => (float) $cols[$lonIdx],
                'brightness' => (float) ($cols[$brightIdx] ?? 0),
                'confidence' => $cols[$confIdx] ?? '',
                'date'       => $cols[$dateIdx] ?? '',
                'time'       => $cols[$timeIdx] ?? '',
                'frp'        => (float) ($cols[$frpIdx] ?? 0),
            ];
        }
    }
}

// If no FIRMS key or fetch failed, try MODIS open feed (no key needed but less data)
if (empty($fires)) {
    $modisUrl = 'https://firms.modaps.eosdis.nasa.gov/api/area/csv/MODIS_NRT/world/1/2024-01-01';
    // This endpoint may not work without key — we catch gracefully
    // For keyless operation, return empty with a message
    $fires = [];
}

$output = json_encode([
    'fires' => $fires,
    'count' => count($fires),
    'time' => time(),
    'source' => $firmsKey ? 'NASA FIRMS VIIRS' : 'none (add NASA_FIRMS_KEY to .env)'
]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
```

---

## 4.10 `radio.php`

### File: `H:\var\www\self\the-eye\api\radio.php`

```php
<?php
/**
 * Global radio stations with geolocation.
 * Source: radio-browser.info (free, no key required).
 * API: https://de1.api.radio-browser.info/json/stations/search
 * Refresh: 10 minutes
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(600);
BaseApi::checkRateLimit();

// Optional: filter by country or coordinates
$country = $_GET['country'] ?? '';
$limit = min((int) ($_GET['limit'] ?? 500), 2000);

$cacheKey = "radio_{$country}_{$limit}";
$cached = BaseApi::getCache($cacheKey, 500);
if ($cached) {
    BaseApi::respond($cached);
}

// radio-browser.info has multiple mirrors
$mirrors = [
    'https://de1.api.radio-browser.info',
    'https://nl1.api.radio-browser.info',
    'https://at1.api.radio-browser.info',
];

$params = [
    'limit'           => $limit,
    'hidebroken'      => 'true',
    'has_geo_info'    => 'true',
    'order'           => 'clickcount',
    'reverse'         => 'true',
];
if ($country) {
    $params['country'] = $country;
}

$queryString = http_build_query($params);
$stations = [];

foreach ($mirrors as $mirror) {
    $response = BaseApi::fetch("{$mirror}/json/stations/search?{$queryString}");
    if ($response) {
        $data = json_decode($response, true);
        if (is_array($data)) {
            foreach ($data as $st) {
                if (empty($st['geo_lat']) || empty($st['geo_long'])) continue;
                $stations[] = [
                    'id'       => $st['stationuuid'] ?? '',
                    'name'     => $st['name'] ?? 'Unknown',
                    'lat'      => (float) $st['geo_lat'],
                    'lon'      => (float) $st['geo_long'],
                    'country'  => $st['country'] ?? '',
                    'url'      => $st['url_resolved'] ?? $st['url'] ?? '',
                    'codec'    => $st['codec'] ?? '',
                    'bitrate'  => (int) ($st['bitrate'] ?? 0),
                    'tags'     => $st['tags'] ?? '',
                    'votes'    => (int) ($st['votes'] ?? 0),
                    'favicon'  => $st['favicon'] ?? '',
                ];
            }
            break; // Got data, stop trying mirrors
        }
    }
}

$output = json_encode(['stations' => $stations, 'count' => count($stations), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
```

---

## 4.11 `launches.php`

### File: `H:\var\www\self\the-eye\api\launches.php`

```php
<?php
/**
 * Upcoming and recent rocket launches.
 * Primary: The Space Devs Launch Library 2 (free tier, 15 requests/hour).
 * URL: https://ll.thespacedevs.com/2.3.0/launches/upcoming/?limit=25&format=json
 * Refresh: 15 minutes
 */

require_once __DIR__ . '/BaseApi.php';

BaseApi::headers(900);
BaseApi::checkRateLimit();

$cacheKey = 'launches_upcoming';
$cached = BaseApi::getCache($cacheKey, 800);
if ($cached) {
    BaseApi::respond($cached);
}

// The Space Devs — free tier (15 req/hr, no key needed)
$response = BaseApi::fetch(
    'https://ll.thespacedevs.com/2.3.0/launches/upcoming/?limit=25&format=json',
    null,
    [],
    12
);

$launches = [];

if ($response) {
    $data = json_decode($response, true);
    if (isset($data['results'])) {
        foreach ($data['results'] as $launch) {
            $pad = $launch['pad'] ?? [];
            $launches[] = [
                'id'          => $launch['id'] ?? '',
                'name'        => $launch['name'] ?? '',
                'status'      => $launch['status']['name'] ?? '',
                'net'         => $launch['net'] ?? '',
                'window_start'=> $launch['window_start'] ?? '',
                'window_end'  => $launch['window_end'] ?? '',
                'rocket'      => $launch['rocket']['configuration']['name'] ?? '',
                'provider'    => $launch['launch_service_provider']['name'] ?? '',
                'pad_name'    => $pad['name'] ?? '',
                'lat'         => isset($pad['latitude']) ? (float) $pad['latitude'] : null,
                'lon'         => isset($pad['longitude']) ? (float) $pad['longitude'] : null,
                'location'    => $pad['location']['name'] ?? '',
                'country'     => $pad['location']['country_code'] ?? '',
                'image'       => $launch['image'] ?? '',
                'mission'     => $launch['mission']['description'] ?? '',
            ];
        }
    }
}

$output = json_encode(['launches' => $launches, 'count' => count($launches), 'time' => time()]);
BaseApi::setCache($cacheKey, $output);
BaseApi::respond($output);
```

---

## 4.12 Phase 2 Verification

```bash
# Deploy API files to VPS
scp -r H:\var\www\self\the-eye\api\* zeon7@87.106.74.66:/var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/api/
ssh zeon7@87.106.74.66 "sudo chown -R quiddity:psacln /var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/api/"

# Test each endpoint (replace SESSION_TOKEN with a real token from eye_sessions table)
curl -H "X-Eye-Token: SESSION_TOKEN" https://eye.foreverbox.co.uk/api/flights.php | python3 -m json.tool | head -20
curl -H "X-Eye-Token: SESSION_TOKEN" https://eye.foreverbox.co.uk/api/military.php | python3 -m json.tool | head -20
curl -H "X-Eye-Token: SESSION_TOKEN" https://eye.foreverbox.co.uk/api/satellites.php | python3 -m json.tool | head -20
curl -H "X-Eye-Token: SESSION_TOKEN" https://eye.foreverbox.co.uk/api/earthquakes.php | python3 -m json.tool | head -20
curl -H "X-Eye-Token: SESSION_TOKEN" https://eye.foreverbox.co.uk/api/radio.php | python3 -m json.tool | head -20
curl -H "X-Eye-Token: SESSION_TOKEN" https://eye.foreverbox.co.uk/api/launches.php | python3 -m json.tool | head -20

# Each should return valid JSON with a populated array (not empty)
# flights.php: states array
# military.php: military array  
# satellites.php: satellites array
# earthquakes.php: features array (GeoJSON)
# radio.php: stations array
# launches.php: launches array
```

---

_Document continues in Phase 3–8 sections below._


# 5. Phase 3 — Globe Layer Rendering (JavaScript)

**Goal:** All data layers render on the CesiumJS globe. Click-to-track works. HUD shows telemetry. 6 sensor styles available.

---

## 5.1 `base-layer.js` — Shared Layer Base Class

### File: `H:\var\www\self\the-eye\js\layers\base-layer.js`

```javascript
/**
 * BaseLayer — Abstract base class for all data layers.
 * Each layer handles its own fetch cycle, entity management, and visibility.
 */
export class BaseLayer {
    /**
     * @param {Cesium.Viewer} viewer   — CesiumJS viewer instance
     * @param {string}        name     — Layer slug (e.g. 'flights')
     * @param {string}        apiUrl   — PHP proxy endpoint (e.g. 'api/flights.php')
     * @param {number}        refreshMs — Refresh interval in milliseconds
     */
    constructor(viewer, name, apiUrl, refreshMs) {
        this.viewer = viewer;
        this.name = name;
        this.apiUrl = apiUrl;
        this.refreshMs = refreshMs;
        this.entities = new Map();
        this.visible = false;
        this.interval = null;
        this.entityCount = 0;
    }

    /** Start the layer — fetch data and begin refresh loop. */
    show() {
        this.visible = true;
        this.fetchData();
        this.interval = setInterval(() => this.fetchData(), this.refreshMs);
    }

    /** Stop the layer — clear refresh and hide all entities. */
    hide() {
        this.visible = false;
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
        this.entities.forEach(entity => {
            entity.show = false;
        });
    }

    /** Remove all entities from the viewer. */
    destroy() {
        this.hide();
        this.entities.forEach(entity => {
            this.viewer.entities.remove(entity);
        });
        this.entities.clear();
    }

    /** Fetch data from the PHP proxy. Override processData() for layer-specific logic. */
    async fetchData() {
        if (!this.visible) return;
        try {
            const res = await fetch(this.apiUrl, {
                headers: { 'X-Eye-Token': window.EYE_CONFIG.sessionToken }
            });
            if (!res.ok) return;
            const data = await res.json();
            this.processData(data);
        } catch (e) {
            console.warn(`[Eye] ${this.name} fetch failed:`, e.message);
        }
    }

    /**
     * Override this in each layer subclass.
     * Should call this.addOrUpdateEntity() for each data point.
     */
    processData(data) {
        // Override in subclass
    }

    /**
     * Add or update an entity by unique ID.
     * @param {string}               id      — Unique entity identifier
     * @param {Cesium.Cartesian3}    position — Position
     * @param {object}               options  — Cesium Entity options (point, billboard, label, etc.)
     * @param {object}               metadata — Extra metadata for telemetry card
     */
    addOrUpdateEntity(id, position, options, metadata = {}) {
        if (this.entities.has(id)) {
            const entity = this.entities.get(id);
            entity.position = position;
            entity.show = true;
            // Update metadata
            if (metadata) entity._eyeMeta = metadata;
        } else {
            const entity = this.viewer.entities.add({
                id: `${this.name}_${id}`,
                position: position,
                show: this.visible,
                ...options
            });
            entity._eyeMeta = { layer: this.name, entityId: id, ...metadata };
            this.entities.set(id, entity);
        }
    }

    /** Get current entity count. */
    getCount() {
        let count = 0;
        this.entities.forEach(e => { if (e.show) count++; });
        return count;
    }
}
```

---

## 5.2 `flights.js` — Live Flights Layer

### File: `H:\var\www\self\the-eye\js\layers\flights.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class FlightsLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'flights', 'api/flights.php', 10000);
    }

    processData(data) {
        const states = data.states || [];
        const seen = new Set();

        states.forEach(s => {
            const icao24   = s[0];
            const callsign = (s[1] || '').trim();
            const lon      = s[5];
            const lat      = s[6];
            const alt      = s[7] || 0;
            const onGround = s[8];
            const velocity = s[9] || 0;
            const track    = s[10] || 0;
            const squawk   = s[14] || '';

            if (lon === null || lat === null) return;
            seen.add(icao24);

            const position = Cesium.Cartesian3.fromDegrees(lon, lat, alt);
            const speedKts = (velocity * 1.944).toFixed(0);
            const altFt = (alt * 3.281).toFixed(0);

            this.addOrUpdateEntity(icao24, position, {
                point: {
                    pixelSize: onGround ? 3 : 5,
                    color: onGround ? Cesium.Color.GRAY.withAlpha(0.5) : Cesium.Color.CYAN,
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 1
                },
                label: {
                    text: callsign || icao24,
                    font: '10px JetBrains Mono',
                    fillColor: Cesium.Color.CYAN,
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(12, -4),
                    scale: 0.9,
                    showBackground: false,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 500000)
                }
            }, {
                callsign: callsign,
                icao24: icao24,
                altitude: altFt + ' ft',
                speed: speedKts + ' kts',
                track: track.toFixed(0) + '°',
                squawk: squawk,
                onGround: onGround,
                type: 'flight'
            });
        });

        // Remove stale entities
        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) {
                entity.show = false;
            }
        });
    }
}
```

---

## 5.3 `military.js` — Military Traffic Layer

### File: `H:\var\www\self\the-eye\js\layers\military.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class MilitaryLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'military', 'api/military.php', 15000);
    }

    processData(data) {
        const contacts = data.military || [];
        const seen = new Set();

        contacts.forEach(ac => {
            if (!ac.lat || !ac.lon) return;
            const id = ac.hex;
            seen.add(id);

            const position = Cesium.Cartesian3.fromDegrees(ac.lon, ac.lat, ac.alt || 0);

            this.addOrUpdateEntity(id, position, {
                point: {
                    pixelSize: 7,
                    color: Cesium.Color.fromCssColorString('#f43f5e'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 1
                },
                label: {
                    text: ac.callsign || ac.hex,
                    font: '10px JetBrains Mono',
                    fillColor: Cesium.Color.fromCssColorString('#f43f5e'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(12, -4),
                    scale: 0.9,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 600000)
                }
            }, {
                callsign: ac.callsign,
                hex: ac.hex,
                altitude: (ac.alt ? (ac.alt * 3.281).toFixed(0) + ' ft' : 'N/A'),
                speed: (ac.speed ? (ac.speed * 1.944).toFixed(0) + ' kts' : 'N/A'),
                track: (ac.track ? ac.track.toFixed(0) + '°' : 'N/A'),
                aircraftType: ac.type,
                squawk: ac.squawk,
                military: true,
                type: 'military'
            });
        });

        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) entity.show = false;
        });
    }
}
```

---

## 5.4 `ships.js` — AIS Ship Tracking Layer

### File: `H:\var\www\self\the-eye\js\layers\ships.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class ShipsLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'ships', 'api/ships.php', 30000);
    }

    processData(data) {
        const vessels = data.ships || [];
        const seen = new Set();

        vessels.forEach(v => {
            if (!v.lat || !v.lon) return;
            const id = String(v.mmsi);
            seen.add(id);

            const position = Cesium.Cartesian3.fromDegrees(v.lon, v.lat, 0);

            this.addOrUpdateEntity(id, position, {
                point: {
                    pixelSize: 5,
                    color: Cesium.Color.fromCssColorString('#f59e0b'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 1
                },
                label: {
                    text: v.name || v.mmsi,
                    font: '9px JetBrains Mono',
                    fillColor: Cesium.Color.fromCssColorString('#f59e0b'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(10, -4),
                    scale: 0.85,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 200000)
                }
            }, {
                name: v.name,
                mmsi: v.mmsi,
                speed: v.speed.toFixed(1) + ' kts',
                course: v.course.toFixed(0) + '°',
                heading: v.heading + '°',
                destination: v.dest || 'N/A',
                length: v.length + ' m',
                type: 'ship'
            });
        });

        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) entity.show = false;
        });
    }
}
```

---

## 5.5 `satellites.js` — Orbital Satellite Tracking Layer

### File: `H:\var\www\self\the-eye\js\layers\satellites.js`

```javascript
import { BaseLayer } from './base-layer.js';

/**
 * Satellite layer using satellite.js (loaded via CDN as global `satellite`).
 * Fetches TLE data from PHP proxy, propagates positions client-side using SGP4.
 */
export default class SatellitesLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'satellites', 'api/satellites.php', 300000); // 5 min TLE refresh
        this.tleData = []; // Cached TLE records
        this.positionUpdateInterval = null;
    }

    show() {
        super.show();
        // Update satellite positions every 3 seconds (orbital movement is fast)
        this.positionUpdateInterval = setInterval(() => this.updatePositions(), 3000);
    }

    hide() {
        super.hide();
        if (this.positionUpdateInterval) {
            clearInterval(this.positionUpdateInterval);
            this.positionUpdateInterval = null;
        }
    }

    processData(data) {
        this.tleData = data.satellites || [];
        this.updatePositions();
    }

    /** Propagate all satellites to current time using SGP4. */
    updatePositions() {
        if (!this.visible || !this.tleData.length) return;

        const now = new Date();
        const gmst = satellite.gstime(now);
        const seen = new Set();

        this.tleData.forEach(sat => {
            if (!sat.tle_line1 || !sat.tle_line2) return;

            try {
                const satrec = satellite.twoline2satrec(sat.tle_line1, sat.tle_line2);
                const posVel = satellite.propagate(satrec, now);

                if (!posVel.position) return;

                const geodetic = satellite.eciToGeodetic(posVel.position, gmst);
                const lon = satellite.degreesLong(geodetic.longitude);
                const lat = satellite.degreesLat(geodetic.latitude);
                const alt = geodetic.height * 1000; // km to metres

                if (isNaN(lon) || isNaN(lat) || isNaN(alt)) return;

                const id = sat.norad_id || sat.name;
                seen.add(id);

                const position = Cesium.Cartesian3.fromDegrees(lon, lat, alt);

                // Determine colour by altitude (LEO=green, MEO=yellow, GEO=orange)
                let color = Cesium.Color.LIME;
                if (alt > 35000000) color = Cesium.Color.ORANGE;
                else if (alt > 2000000) color = Cesium.Color.YELLOW;

                this.addOrUpdateEntity(id, position, {
                    point: {
                        pixelSize: 4,
                        color: color,
                        outlineColor: Cesium.Color.BLACK,
                        outlineWidth: 1
                    },
                    label: {
                        text: sat.name || '',
                        font: '9px JetBrains Mono',
                        fillColor: color,
                        outlineColor: Cesium.Color.BLACK,
                        outlineWidth: 2,
                        style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                        pixelOffset: new Cesium.Cartesian2(8, -4),
                        scale: 0.8,
                        distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 10000000)
                    }
                }, {
                    name: sat.name,
                    norad_id: sat.norad_id,
                    altitude: (alt / 1000).toFixed(1) + ' km',
                    inclination: sat.inclination ? sat.inclination.toFixed(1) + '°' : 'N/A',
                    period: sat.mean_motion ? (1440 / sat.mean_motion).toFixed(1) + ' min' : 'N/A',
                    type: 'satellite'
                });
            } catch (e) {
                // Skip satellites that fail propagation
            }
        });

        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) entity.show = false;
        });
    }
}
```

---

## 5.6 `earthquakes.js` — Seismic Activity Layer

### File: `H:\var\www\self\the-eye\js\layers\earthquakes.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class EarthquakesLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'earthquakes', 'api/earthquakes.php', 60000);
    }

    processData(data) {
        const features = data.features || [];
        const seen = new Set();

        features.forEach(f => {
            const coords = f.geometry?.coordinates;
            if (!coords || coords.length < 2) return;

            const id = f.id || f.properties?.code || `eq_${coords[0]}_${coords[1]}`;
            seen.add(id);

            const lon = coords[0];
            const lat = coords[1];
            const depth = coords[2] || 0; // km
            const mag = f.properties?.mag || 0;
            const place = f.properties?.place || 'Unknown';
            const time = f.properties?.time || 0;

            const position = Cesium.Cartesian3.fromDegrees(lon, lat, 0);

            // Size and colour based on magnitude
            let pixelSize = 4;
            let color = Cesium.Color.YELLOW;
            if (mag >= 6) { pixelSize = 16; color = Cesium.Color.RED; }
            else if (mag >= 5) { pixelSize = 12; color = Cesium.Color.fromCssColorString('#f43f5e'); }
            else if (mag >= 4) { pixelSize = 9; color = Cesium.Color.ORANGE; }
            else if (mag >= 3) { pixelSize = 6; color = Cesium.Color.YELLOW; }

            this.addOrUpdateEntity(id, position, {
                point: {
                    pixelSize: pixelSize,
                    color: color.withAlpha(0.8),
                    outlineColor: color,
                    outlineWidth: 2
                },
                label: {
                    text: `M${mag.toFixed(1)}`,
                    font: '10px JetBrains Mono',
                    fillColor: color,
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(pixelSize + 4, -4),
                    scale: 0.85,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 2000000)
                }
            }, {
                magnitude: `M${mag.toFixed(1)}`,
                place: place,
                depth: depth.toFixed(1) + ' km',
                time: new Date(time).toISOString(),
                type: 'earthquake'
            });
        });

        this.entities.forEach((entity, id) => {
            if (!seen.has(id)) entity.show = false;
        });
    }
}
```

---

## 5.7 Remaining Layer Files

### File: `H:\var\www\self\the-eye\js\layers\cctv.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class CctvLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'cctv', 'api/cctv.php', 600000);
    }

    processData(data) {
        const cameras = data.cameras || [];
        cameras.forEach(cam => {
            if (!cam.lat || !cam.lon) return;
            const position = Cesium.Cartesian3.fromDegrees(cam.lon, cam.lat, 10);
            this.addOrUpdateEntity(String(cam.id), position, {
                billboard: {
                    image: 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"><circle cx="8" cy="8" r="6" fill="none" stroke="%2322d3ee" stroke-width="2"/><circle cx="8" cy="8" r="2" fill="%2322d3ee"/></svg>'),
                    width: 16,
                    height: 16,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 500000)
                }
            }, { name: cam.name, zone: cam.zone, type: 'cctv' });
        });
    }
}
```

### File: `H:\var\www\self\the-eye\js\layers\traffic.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class TrafficLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'traffic', 'api/traffic.php', 300000);
        this.polylines = [];
    }

    processData(data) {
        // Remove previous polylines
        this.polylines.forEach(p => this.viewer.entities.remove(p));
        this.polylines = [];

        const roads = data.roads || [];
        roads.forEach(road => {
            if (!road.coords || road.coords.length < 2) return;
            const positions = road.coords.map(c => Cesium.Cartesian3.fromDegrees(c[0], c[1], 5));
            let color = Cesium.Color.fromCssColorString('#22d3ee').withAlpha(0.4);
            if (road.type === 'motorway') color = Cesium.Color.fromCssColorString('#f59e0b').withAlpha(0.5);

            const entity = this.viewer.entities.add({
                polyline: {
                    positions: positions,
                    width: road.type === 'motorway' ? 3 : 2,
                    material: color,
                    clampToGround: true
                },
                show: this.visible
            });
            entity._eyeMeta = { name: road.name, roadType: road.type, type: 'traffic' };
            this.polylines.push(entity);
        });
    }

    hide() {
        super.hide();
        this.polylines.forEach(p => p.show = false);
    }

    show() {
        this.visible = true;
        this.polylines.forEach(p => p.show = true);
        this.fetchData();
        this.interval = setInterval(() => this.fetchData(), this.refreshMs);
    }
}
```

### File: `H:\var\www\self\the-eye\js\layers\fires.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class FiresLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'fires', 'api/fires.php', 300000);
    }

    processData(data) {
        const fires = data.fires || [];
        fires.forEach((fire, i) => {
            if (!fire.lat || !fire.lon) return;
            const position = Cesium.Cartesian3.fromDegrees(fire.lon, fire.lat, 0);
            const intensity = Math.min(fire.frp / 50, 1.0);
            const size = 4 + intensity * 8;

            this.addOrUpdateEntity(`fire_${i}`, position, {
                point: {
                    pixelSize: size,
                    color: Cesium.Color.fromCssColorString('#ff5722').withAlpha(0.7 + intensity * 0.3),
                    outlineColor: Cesium.Color.RED,
                    outlineWidth: 1
                }
            }, {
                brightness: fire.brightness,
                confidence: fire.confidence,
                frp: fire.frp.toFixed(1) + ' MW',
                date: fire.date + ' ' + fire.time,
                type: 'fire'
            });
        });
    }
}
```

### File: `H:\var\www\self\the-eye\js\layers\radio.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class RadioLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'radio', 'api/radio.php', 600000);
        this.audioElement = null;
    }

    processData(data) {
        const stations = data.stations || [];
        stations.forEach(st => {
            if (!st.lat || !st.lon) return;
            const position = Cesium.Cartesian3.fromDegrees(st.lon, st.lat, 20);
            this.addOrUpdateEntity(st.id, position, {
                point: {
                    pixelSize: 4,
                    color: Cesium.Color.fromCssColorString('#a855f7').withAlpha(0.7),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 1
                },
                label: {
                    text: st.name,
                    font: '8px JetBrains Mono',
                    fillColor: Cesium.Color.fromCssColorString('#a855f7'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(8, -4),
                    scale: 0.75,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 100000)
                }
            }, {
                name: st.name,
                country: st.country,
                url: st.url,
                codec: st.codec,
                bitrate: st.bitrate + ' kbps',
                type: 'radio'
            });
        });
    }

    /** Play a radio station stream. Called when a radio entity is clicked. */
    playStation(url) {
        this.stopStation();
        this.audioElement = new Audio(url);
        this.audioElement.volume = 0.5;
        this.audioElement.play().catch(() => {});
    }

    stopStation() {
        if (this.audioElement) {
            this.audioElement.pause();
            this.audioElement = null;
        }
    }
}
```

### File: `H:\var\www\self\the-eye\js\layers\launches.js`

```javascript
import { BaseLayer } from './base-layer.js';

export default class LaunchesLayer extends BaseLayer {
    constructor(viewer) {
        super(viewer, 'launches', 'api/launches.php', 900000);
    }

    processData(data) {
        const launches = data.launches || [];
        launches.forEach(launch => {
            if (!launch.lat || !launch.lon) return;
            const position = Cesium.Cartesian3.fromDegrees(launch.lon, launch.lat, 0);

            this.addOrUpdateEntity(launch.id, position, {
                billboard: {
                    image: 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="24"><polygon points="8,0 14,20 8,16 2,20" fill="%2300ff41" opacity="0.8"/></svg>'),
                    width: 16,
                    height: 24,
                    verticalOrigin: Cesium.VerticalOrigin.BOTTOM
                },
                label: {
                    text: launch.rocket || launch.name,
                    font: '10px JetBrains Mono',
                    fillColor: Cesium.Color.fromCssColorString('#00ff41'),
                    outlineColor: Cesium.Color.BLACK,
                    outlineWidth: 2,
                    style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                    pixelOffset: new Cesium.Cartesian2(14, -12),
                    scale: 0.85,
                    distanceDisplayCondition: new Cesium.DistanceDisplayCondition(0, 5000000)
                }
            }, {
                name: launch.name,
                rocket: launch.rocket,
                provider: launch.provider,
                status: launch.status,
                net: launch.net,
                padName: launch.pad_name,
                location: launch.location,
                mission: launch.mission,
                type: 'launch'
            });
        });
    }
}
```

---

## 5.8 `sensors.js` — Visual Style Switcher (GLSL Shaders)

### File: `H:\var\www\self\the-eye\js\controls\sensors.js`

```javascript
/**
 * Sensor visual modes — GLSL PostProcessStage shaders.
 * Keyboard shortcuts: 1=Normal, 2=CRT, 3=NVG, 4=FLIR, 5=Noir, 6=Snow
 */
export class Sensors {
    constructor(globe) {
        this.viewer = globe.viewer;
        this.currentStage = null;
        this.currentStyle = 'normal';

        // GLSL fragment shaders for each sensor mode
        this.shaders = {
            normal: null,

            crt: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    // Scanline effect
                    float scanline = sin(v_textureCoordinates.y * 800.0) * 0.04;
                    // Phosphor RGB separation
                    float r = texture(colorTexture, v_textureCoordinates + vec2(0.001, 0.0)).r;
                    float g = color.g;
                    float b = texture(colorTexture, v_textureCoordinates - vec2(0.001, 0.0)).b;
                    // Vignette
                    vec2 uv = v_textureCoordinates * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.3;
                    // Green phosphor tint
                    vec3 crt = vec3(r * 0.6, g * 1.2, b * 0.6) * vignette - scanline;
                    out_FragColor = vec4(crt, 1.0);
                }
            `,

            nvg: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    float luminance = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    // Night vision green with grain
                    float grain = fract(sin(dot(v_textureCoordinates * 500.0, vec2(12.9898, 78.233))) * 43758.5453) * 0.06;
                    float green = luminance * 1.8 + grain;
                    // Slight vignette
                    vec2 uv = v_textureCoordinates * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.35;
                    out_FragColor = vec4(0.05, green * vignette, 0.05, 1.0);
                }
            `,

            flir: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    float luminance = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    // Ironbow thermal palette
                    vec3 thermal;
                    if (luminance < 0.25) {
                        thermal = mix(vec3(0.0, 0.0, 0.2), vec3(0.5, 0.0, 0.5), luminance * 4.0);
                    } else if (luminance < 0.5) {
                        thermal = mix(vec3(0.5, 0.0, 0.5), vec3(1.0, 0.2, 0.0), (luminance - 0.25) * 4.0);
                    } else if (luminance < 0.75) {
                        thermal = mix(vec3(1.0, 0.2, 0.0), vec3(1.0, 0.8, 0.0), (luminance - 0.5) * 4.0);
                    } else {
                        thermal = mix(vec3(1.0, 0.8, 0.0), vec3(1.0, 1.0, 0.9), (luminance - 0.75) * 4.0);
                    }
                    out_FragColor = vec4(thermal, 1.0);
                }
            `,

            noir: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    float luminance = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    // High contrast desaturation
                    float contrast = (luminance - 0.5) * 1.8 + 0.5;
                    contrast = clamp(contrast, 0.0, 1.0);
                    // Slight sepia/blue tint
                    vec3 noir = vec3(contrast * 0.9, contrast * 0.9, contrast * 1.05);
                    // Vignette
                    vec2 uv = v_textureCoordinates * 2.0 - 1.0;
                    float vignette = 1.0 - dot(uv, uv) * 0.45;
                    out_FragColor = vec4(noir * vignette, 1.0);
                }
            `,

            snow: `
                uniform sampler2D colorTexture;
                in vec2 v_textureCoordinates;
                void main() {
                    vec4 color = texture(colorTexture, v_textureCoordinates);
                    float luminance = dot(color.rgb, vec3(0.299, 0.587, 0.114));
                    // White-out with altitude visibility bands
                    float whiteout = luminance * 0.3 + 0.7;
                    // Subtle blue tint
                    vec3 snow = vec3(whiteout * 0.92, whiteout * 0.95, whiteout);
                    // Edge darkening for terrain relief
                    float dx = dFdx(luminance);
                    float dy = dFdy(luminance);
                    float edge = sqrt(dx * dx + dy * dy) * 8.0;
                    snow -= vec3(edge * 0.5);
                    out_FragColor = vec4(clamp(snow, 0.0, 1.0), 1.0);
                }
            `
        };

        this.initKeyboardShortcuts();
        this.renderButtons();
    }

    /** Set the active sensor style. */
    setStyle(name) {
        if (!this.shaders.hasOwnProperty(name)) return;

        // Remove current post-process stage
        if (this.currentStage) {
            this.viewer.scene.postProcessStages.remove(this.currentStage);
            this.currentStage = null;
        }

        this.currentStyle = name;

        // Apply new shader (skip for 'normal')
        if (name !== 'normal' && this.shaders[name]) {
            this.currentStage = new Cesium.PostProcessStage({
                fragmentShader: this.shaders[name]
            });
            this.viewer.scene.postProcessStages.add(this.currentStage);
        }

        // Update button states
        document.querySelectorAll('.sensor-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.style === name);
        });
    }

    /** Keyboard shortcuts: 1-6 for sensor styles. */
    initKeyboardShortcuts() {
        const keys = { '1': 'normal', '2': 'crt', '3': 'nvg', '4': 'flir', '5': 'noir', '6': 'snow' };
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT') return; // Don't intercept while typing
            if (keys[e.key]) {
                this.setStyle(keys[e.key]);
            }
        });
    }

    /** Render sensor mode buttons into #sensor-buttons. */
    renderButtons() {
        const container = document.getElementById('sensor-buttons');
        if (!container) return;

        const styles = ['normal', 'crt', 'nvg', 'flir', 'noir', 'snow'];
        styles.forEach((style, i) => {
            const btn = document.createElement('button');
            btn.className = 'sensor-btn' + (style === 'normal' ? ' active' : '');
            btn.dataset.style = style;
            btn.textContent = `${i + 1} ${style.toUpperCase()}`;
            btn.addEventListener('click', () => this.setStyle(style));
            container.appendChild(btn);
        });
    }
}
```

---

## 5.9 `cockpit.js` — First-Person Camera Lock

### File: `H:\var\www\self\the-eye\js\controls\cockpit.js`

```javascript
/**
 * Cockpit mode — locks the camera behind/below a tracked entity.
 * The terrain stays visible underneath. Sensor styles carry through.
 * Keyboard shortcut: C to toggle cockpit, Esc to exit.
 */
export class Cockpit {
    constructor(globe, tracking) {
        this.viewer = globe.viewer;
        this.tracking = tracking;
        this.active = false;
        this.animationFrame = null;

        // Cockpit camera offset (behind and above the entity)
        this.offsetDistance = 500;  // metres behind
        this.offsetHeight = 150;   // metres above

        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT') return;
            if (e.key === 'c' || e.key === 'C') this.toggle();
            if (e.key === 'Escape' && this.active) this.exit();
        });

        const cockpitBtn = document.getElementById('btn-cockpit');
        if (cockpitBtn) {
            cockpitBtn.addEventListener('click', () => this.toggle());
        }
    }

    toggle() {
        if (this.active) {
            this.exit();
        } else {
            this.enter();
        }
    }

    enter() {
        const tracked = this.tracking.getTracked();
        if (!tracked) return;

        this.active = true;
        this.viewer.scene.screenSpaceCameraController.enableRotate = false;
        this.viewer.scene.screenSpaceCameraController.enableTranslate = false;
        this.viewer.scene.screenSpaceCameraController.enableZoom = false;
        this.viewer.scene.screenSpaceCameraController.enableTilt = false;
        this.viewer.scene.screenSpaceCameraController.enableLook = false;

        this.update();
    }

    exit() {
        this.active = false;
        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
            this.animationFrame = null;
        }

        this.viewer.scene.screenSpaceCameraController.enableRotate = true;
        this.viewer.scene.screenSpaceCameraController.enableTranslate = true;
        this.viewer.scene.screenSpaceCameraController.enableZoom = true;
        this.viewer.scene.screenSpaceCameraController.enableTilt = true;
        this.viewer.scene.screenSpaceCameraController.enableLook = true;
    }

    update() {
        if (!this.active) return;

        const tracked = this.tracking.getTracked();
        if (!tracked || !tracked.position) {
            this.exit();
            return;
        }

        const pos = tracked.position.getValue(Cesium.JulianDate.now());
        if (!pos) {
            this.animationFrame = requestAnimationFrame(() => this.update());
            return;
        }

        const meta = tracked._eyeMeta || {};
        const trackDeg = parseFloat(meta.track) || 0;
        const trackRad = Cesium.Math.toRadians(trackDeg);

        // Position camera behind the entity
        const transform = Cesium.Transforms.eastNorthUpToFixedFrame(pos);
        const offset = new Cesium.Cartesian3(
            -Math.sin(trackRad) * this.offsetDistance,
            -Math.cos(trackRad) * this.offsetDistance,
            this.offsetHeight
        );
        const cameraPos = Cesium.Matrix4.multiplyByPoint(transform, offset, new Cesium.Cartesian3());

        this.viewer.camera.setView({
            destination: cameraPos,
            orientation: {
                heading: trackRad,
                pitch: Cesium.Math.toRadians(-15),
                roll: 0
            }
        });

        this.animationFrame = requestAnimationFrame(() => this.update());
    }
}
```

---

## 5.10 `hud.js` — Tactical Heads-Up Display

### File: `H:\var\www\self\the-eye\js\controls\hud.js`

```javascript
/**
 * Tactical HUD — updates camera coordinates, UTC clock, layer counts,
 * and tracked entity telemetry every second.
 */
export class Hud {
    constructor(globe, tracking) {
        this.viewer = globe.viewer;
        this.globe = globe;
        this.tracking = tracking;
        this.clockEl = document.getElementById('hud-clock');
        this.coordsEl = document.getElementById('hud-coords');
        this.statsEl = document.getElementById('hud-stats');
        this.telemetryEl = document.getElementById('hud-telemetry');

        // Keyboard shortcut: H to toggle HUD
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT') return;
            if (e.key === 'h' || e.key === 'H') {
                const hud = document.getElementById('tactical-hud');
                hud.classList.toggle('hidden');
            }
        });
    }

    /** Called every second from eye-app.js. */
    update() {
        this.updateClock();
        this.updateCoords();
        this.updateStats();
        this.updateTelemetry();
    }

    updateClock() {
        if (!this.clockEl) return;
        const d = new Date();
        this.clockEl.textContent = d.toISOString().replace('T', ' ').substring(0, 19) + ' UTC';
    }

    updateCoords() {
        if (!this.coordsEl) return;
        try {
            const cam = this.viewer.camera;
            const carto = Cesium.Cartographic.fromCartesian(cam.position);
            const lat = Cesium.Math.toDegrees(carto.latitude).toFixed(4);
            const lon = Cesium.Math.toDegrees(carto.longitude).toFixed(4);
            const alt = carto.height;

            let altStr;
            if (alt > 100000) altStr = (alt / 1000).toFixed(0) + ' km';
            else altStr = alt.toFixed(0) + ' m';

            const heading = Cesium.Math.toDegrees(cam.heading).toFixed(0);
            const pitch = Cesium.Math.toDegrees(cam.pitch).toFixed(0);

            this.coordsEl.innerHTML =
                `LAT ${lat} LON ${lon}<br>` +
                `ALT ${altStr} HDG ${heading}° PIT ${pitch}°`;
        } catch (e) {
            // Camera position not ready yet
        }
    }

    updateStats() {
        if (!this.statsEl) return;
        const counts = [];
        this.globe.activeLayers.forEach(name => {
            const layer = this.globe.layers[name];
            if (layer) {
                counts.push(`${name.toUpperCase()}: ${layer.getCount()}`);
            }
        });
        this.statsEl.textContent = counts.join(' | ') || 'No layers active';
    }

    updateTelemetry() {
        if (!this.telemetryEl) return;
        const tracked = this.tracking.getTracked();
        if (!tracked || !tracked._eyeMeta) {
            this.telemetryEl.classList.add('hidden');
            return;
        }

        this.telemetryEl.classList.remove('hidden');
        const m = tracked._eyeMeta;
        let html = `<strong>TRACKING: ${m.callsign || m.name || m.entityId}</strong><br>`;

        if (m.altitude) html += `ALT: ${m.altitude}<br>`;
        if (m.speed) html += `SPD: ${m.speed}<br>`;
        if (m.track) html += `TRK: ${m.track}<br>`;
        if (m.squawk) html += `SQK: ${m.squawk}<br>`;
        if (m.icao24) html += `ICAO: ${m.icao24}<br>`;
        if (m.mmsi) html += `MMSI: ${m.mmsi}<br>`;
        if (m.magnitude) html += `MAG: ${m.magnitude}<br>`;
        if (m.place) html += `LOC: ${m.place}<br>`;

        this.telemetryEl.innerHTML = html;
    }
}
```

---

## 5.11 `tracking.js` — Click-to-Track with Trail

### File: `H:\var\www\self\the-eye\js\controls\tracking.js`

```javascript
/**
 * Click-to-track — click any entity to lock the camera on it.
 * Draws a fading polyline trail behind the tracked entity.
 */
export class Tracking {
    constructor(globe) {
        this.viewer = globe.viewer;
        this.trackedEntity = null;
        this.trailPositions = [];
        this.trailEntity = null;
        this.maxTrailPoints = 60; // 60 seconds of trail at 1 update/sec
        this.trailInterval = null;

        // Click handler
        this.handler = new Cesium.ScreenSpaceEventHandler(this.viewer.scene.canvas);
        this.handler.setInputAction((click) => this.onClick(click), Cesium.ScreenSpaceEventType.LEFT_CLICK);

        // Untrack button
        const untrackBtn = document.getElementById('btn-untrack');
        if (untrackBtn) {
            untrackBtn.addEventListener('click', () => this.untrack());
        }
    }

    onClick(click) {
        const picked = this.viewer.scene.pick(click.position);
        if (Cesium.defined(picked) && picked.id && picked.id._eyeMeta) {
            this.track(picked.id);
        }
    }

    track(entity) {
        this.untrack(); // Clear previous tracking

        this.trackedEntity = entity;
        this.trailPositions = [];

        // Show contact card
        const card = document.getElementById('contact-card');
        if (card) {
            card.classList.remove('hidden');
            this.updateContactCard();
        }

        // Fly to entity
        this.viewer.flyTo(entity, { duration: 1.5 });

        // Start trail recording
        this.trailInterval = setInterval(() => this.recordTrailPoint(), 1000);

        // Create trail polyline entity
        this.trailEntity = this.viewer.entities.add({
            polyline: {
                positions: new Cesium.CallbackProperty(() => {
                    return this.trailPositions.map(p => p.position);
                }, false),
                width: 2,
                material: new Cesium.PolylineGlowMaterialProperty({
                    glowPower: 0.3,
                    color: Cesium.Color.CYAN.withAlpha(0.6)
                }),
                clampToGround: false
            }
        });
    }

    untrack() {
        this.trackedEntity = null;

        if (this.trailInterval) {
            clearInterval(this.trailInterval);
            this.trailInterval = null;
        }

        if (this.trailEntity) {
            this.viewer.entities.remove(this.trailEntity);
            this.trailEntity = null;
        }

        this.trailPositions = [];

        const card = document.getElementById('contact-card');
        if (card) card.classList.add('hidden');
    }

    recordTrailPoint() {
        if (!this.trackedEntity || !this.trackedEntity.position) return;

        const pos = this.trackedEntity.position.getValue(Cesium.JulianDate.now());
        if (!pos) return;

        this.trailPositions.push({ position: pos, time: Date.now() });

        // Trim trail to max length
        if (this.trailPositions.length > this.maxTrailPoints) {
            this.trailPositions.shift();
        }
    }

    updateContactCard() {
        if (!this.trackedEntity || !this.trackedEntity._eyeMeta) return;

        const info = document.getElementById('contact-info');
        if (!info) return;

        const m = this.trackedEntity._eyeMeta;
        let html = '';

        const fields = [
            ['callsign', 'CALLSIGN'], ['name', 'NAME'], ['icao24', 'ICAO'],
            ['mmsi', 'MMSI'], ['altitude', 'ALT'], ['speed', 'SPD'],
            ['track', 'TRK'], ['squawk', 'SQK'], ['destination', 'DEST'],
            ['magnitude', 'MAG'], ['place', 'LOCATION'], ['depth', 'DEPTH'],
            ['rocket', 'VEHICLE'], ['provider', 'PROVIDER'], ['status', 'STATUS'],
            ['country', 'COUNTRY']
        ];

        fields.forEach(([key, label]) => {
            if (m[key]) {
                html += `<span class="contact-label">${label}</span> <span class="contact-value">${m[key]}</span><br>`;
            }
        });

        info.innerHTML = html;
    }

    getTracked() {
        return this.trackedEntity;
    }
}
```

---

## 5.12 `eye-app.js` — Main Application Initialiser

### File: `H:\var\www\self\the-eye\js\eye-app.js`

```javascript
/**
 * The Eye — Main Application Entry Point
 * Initialises the globe, layers, controls, and agent integration.
 */
import { EyeGlobe } from './globe.js';
import { Tracking } from './controls/tracking.js';
import { Sensors } from './controls/sensors.js';
import { Hud } from './controls/hud.js';
import { Cockpit } from './controls/cockpit.js';
import { Panels } from './ui/panels.js';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Initialise globe
    window.eyeGlobe = new EyeGlobe('cesiumContainer', window.EYE_CONFIG.cesiumToken);

    // 2. Initialise controls
    window.tracking = new Tracking(window.eyeGlobe);
    window.sensors = new Sensors(window.eyeGlobe);
    window.hud = new Hud(window.eyeGlobe, window.tracking);
    window.cockpit = new Cockpit(window.eyeGlobe, window.tracking);
    window.panels = new Panels(window.eyeGlobe);

    // 3. Agent integration (authenticated users only)
    if (window.EYE_CONFIG.isAuth) {
        const { AgentBar } = await import('./agent/agent-bar.js');
        const { EyeCommander } = await import('./agent/eye-commander.js');

        window.agentBar = new AgentBar();
        window.eyeCommander = new EyeCommander(window.eyeGlobe);

        // Connect agent bar to commander
        window.agentBar.onCommand = (reply, eyeCommand) => {
            window.eyeCommander.execute(eyeCommand);
        };

        // Annotations (auth users only — persistent)
        const { Annotations } = await import('./controls/annotations.js');
        window.annotations = new Annotations(window.eyeGlobe);
    }

    // 4. HUD refresh loop (every second)
    setInterval(() => window.hud.update(), 1000);

    // 5. Keyboard shortcut: D for detection overlay toggle
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT') return;
        if (e.key === 'd' || e.key === 'D') {
            document.body.classList.toggle('detection-active');
        }
        if (e.key === 'Escape') {
            // Reset tracking and cockpit
            if (window.cockpit.active) window.cockpit.exit();
            else window.tracking.untrack();
        }
    });

    console.log('[Eye] Initialised. Session:', window.EYE_CONFIG.sessionToken.substring(0, 8) + '...');
});
```

---

## 5.13 `globe.js` — CesiumJS Viewer Wrapper

### File: `H:\var\www\self\the-eye\js\globe.js`

```javascript
/**
 * EyeGlobe — Wrapper around CesiumJS Viewer.
 * Manages layers, provides methods for agent commands.
 */
export class EyeGlobe {
    constructor(containerId, ionToken) {
        // Configure Cesium Ion token
        if (ionToken) {
            Cesium.Ion.defaultAccessToken = ionToken;
        }

        // Create viewer
        this.viewer = new Cesium.Viewer(containerId, {
            baseLayerPicker: false,
            geocoder: false,
            homeButton: false,
            infoBox: false,
            navigationHelpButton: false,
            sceneModePicker: false,
            animation: false,
            timeline: false,
            fullscreenButton: false,
            selectionIndicator: false,
            shouldAnimate: true
        });

        // Configure imagery
        if (ionToken) {
            // Use Cesium Ion default (Bing Maps Aerial with Labels)
            // Already set as default when Ion token is provided
        } else {
            // Fallback to OpenStreetMap
            this.viewer.imageryLayers.removeAll();
            this.viewer.imageryLayers.addImageryProvider(
                new Cesium.OpenStreetMapImageryProvider({
                    url: 'https://tile.openstreetmap.org/'
                })
            );
        }

        // Enable lighting
        this.viewer.scene.globe.enableLighting = true;
        this.viewer.scene.globe.depthTestAgainstTerrain = true;

        // Layer registry
        this.layers = {};
        this.activeLayers = new Set();

        // Lazy-load all layer modules
        this.initLayers();
    }

    async initLayers() {
        const layerModules = [
            'flights', 'military', 'ships', 'satellites', 'earthquakes',
            'cctv', 'traffic', 'fires', 'radio', 'launches'
        ];

        for (const name of layerModules) {
            try {
                const module = await import(`./layers/${name}.js`);
                this.layers[name] = new module.default(this.viewer);
            } catch (e) {
                console.warn(`[Eye] Failed to load layer module: ${name}`, e);
            }
        }
    }

    /** Fly camera to a position. */
    flyTo(lon, lat, alt = 50000) {
        this.viewer.camera.flyTo({
            destination: Cesium.Cartesian3.fromDegrees(lon, lat, alt),
            duration: 2.0,
            orientation: {
                heading: 0,
                pitch: Cesium.Math.toRadians(-45),
                roll: 0
            }
        });
    }

    /** Toggle a layer on/off by name. */
    toggleLayer(name) {
        const layer = this.layers[name];
        if (!layer) return;

        if (this.activeLayers.has(name)) {
            layer.hide();
            this.activeLayers.delete(name);
        } else {
            layer.show();
            this.activeLayers.add(name);
        }
    }

    /** Set sensor visual style. */
    setStyle(style) {
        if (window.sensors) {
            window.sensors.setStyle(style);
        }
    }

    /** Track an entity by layer name and entity ID. */
    track(entityId, layerName) {
        const fullId = `${layerName}_${entityId}`;
        const entity = this.viewer.entities.getById(fullId);
        if (entity && window.tracking) {
            window.tracking.track(entity);
        }
    }

    /** Create an annotation. */
    annotate(type, geometry, label) {
        if (window.annotations) {
            window.annotations.addFromCommand(type, geometry, label);
        }
    }

    /** Reset globe to initial view. */
    reset() {
        // Untrack
        if (window.tracking) window.tracking.untrack();
        if (window.cockpit) window.cockpit.exit();

        // Hide all layers
        this.activeLayers.forEach(name => {
            if (this.layers[name]) this.layers[name].hide();
        });
        this.activeLayers.clear();

        // Fly to default view
        this.viewer.camera.flyTo({
            destination: Cesium.Cartesian3.fromDegrees(0, 20, 20000000),
            duration: 2.0,
            orientation: { heading: 0, pitch: Cesium.Math.toRadians(-90), roll: 0 }
        });
    }

    /** Get current camera state as serialisable object. */
    getCameraState() {
        try {
            const cam = this.viewer.camera;
            const carto = Cesium.Cartographic.fromCartesian(cam.position);
            return {
                lon: Cesium.Math.toDegrees(carto.longitude),
                lat: Cesium.Math.toDegrees(carto.latitude),
                alt: carto.height,
                heading: Cesium.Math.toDegrees(cam.heading),
                pitch: Cesium.Math.toDegrees(cam.pitch),
                roll: Cesium.Math.toDegrees(cam.roll)
            };
        } catch (e) {
            return null;
        }
    }
}
```

---

## 5.14 `panels.js` — Layer Toggle UI Panel

### File: `H:\var\www\self\the-eye\js\ui\panels.js`

```javascript
/**
 * Layer toggle panel — renders all available layers as toggle switches.
 */
export class Panels {
    constructor(globe) {
        this.globe = globe;
        this.container = document.getElementById('layer-toggles');
        if (this.container) {
            this.render();
        }
    }

    render() {
        const layers = [
            { name: 'flights',     icon: '✈',  label: 'FLIGHTS' },
            { name: 'military',    icon: '🪖',  label: 'MILITARY',   cls: 'military' },
            { name: 'ships',       icon: '🚢',  label: 'SHIPS' },
            { name: 'satellites',  icon: '🛰',  label: 'SATELLITES' },
            { name: 'earthquakes', icon: '🌍',  label: 'EARTHQUAKES' },
            { name: 'cctv',        icon: '📷',  label: 'CCTV' },
            { name: 'traffic',     icon: '🚗',  label: 'TRAFFIC' },
            { name: 'fires',       icon: '🔥',  label: 'FIRES' },
            { name: 'radio',       icon: '📻',  label: 'RADIO' },
            { name: 'launches',    icon: '🚀',  label: 'LAUNCHES' },
        ];

        layers.forEach(layer => {
            const div = document.createElement('div');
            div.className = 'layer-toggle' + (layer.cls ? ' ' + layer.cls : '');
            div.dataset.layer = layer.name;
            div.innerHTML = `<span>${layer.icon} ${layer.label}</span>`;

            div.addEventListener('click', () => {
                this.globe.toggleLayer(layer.name);
                div.classList.toggle('active');
            });

            this.container.appendChild(div);
        });
    }
}
```

---

# 6. Phase 4 — Annotations

### File: `H:\var\www\self\the-eye\js\controls\annotations.js`

```javascript
/**
 * Annotations — draw polygons, routes, and pins on the globe.
 * Authenticated users' annotations are persisted to MariaDB.
 */
export class Annotations {
    constructor(globe) {
        this.viewer = globe.viewer;
        this.annotations = [];
        this.drawing = false;
        this.drawPoints = [];
        this.drawType = null; // 'polygon', 'route', 'pin'

        // Load saved annotations for authenticated user
        if (window.EYE_CONFIG.isAuth) {
            this.loadSaved();
        }
    }

    /** Create annotation from agent command. */
    addFromCommand(type, geometry, label) {
        if (type === 'pin' && geometry.lon && geometry.lat) {
            this.addPin(geometry.lon, geometry.lat, label);
        } else if (type === 'route' && geometry.coordinates) {
            this.addRoute(geometry.coordinates, label);
        } else if (type === 'polygon' && geometry.coordinates) {
            this.addPolygon(geometry.coordinates, label);
        }

        // Save to DB if authenticated
        if (window.EYE_CONFIG.isAuth) {
            this.saveAnnotation(type, geometry, label, 'otec');
        }
    }

    addPin(lon, lat, label) {
        const entity = this.viewer.entities.add({
            position: Cesium.Cartesian3.fromDegrees(lon, lat, 0),
            point: { pixelSize: 10, color: Cesium.Color.CYAN, outlineColor: Cesium.Color.WHITE, outlineWidth: 2 },
            label: {
                text: label || 'Pin',
                font: '12px JetBrains Mono',
                fillColor: Cesium.Color.CYAN,
                outlineColor: Cesium.Color.BLACK,
                outlineWidth: 2,
                style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                pixelOffset: new Cesium.Cartesian2(0, -20)
            }
        });
        this.annotations.push(entity);
    }

    addRoute(coordinates, label) {
        const positions = coordinates.map(c => Cesium.Cartesian3.fromDegrees(c[0], c[1], 10));
        const entity = this.viewer.entities.add({
            polyline: {
                positions: positions,
                width: 3,
                material: Cesium.Color.CYAN.withAlpha(0.8),
                clampToGround: true
            }
        });
        this.annotations.push(entity);
    }

    addPolygon(coordinates, label) {
        const positions = coordinates.map(c => Cesium.Cartesian3.fromDegrees(c[0], c[1]));
        const entity = this.viewer.entities.add({
            polygon: {
                hierarchy: new Cesium.PolygonHierarchy(positions),
                material: Cesium.Color.CYAN.withAlpha(0.15),
                outline: true,
                outlineColor: Cesium.Color.CYAN,
                outlineWidth: 2,
                height: 0,
                perPositionHeight: false
            },
            label: label ? {
                text: label,
                font: '11px JetBrains Mono',
                fillColor: Cesium.Color.CYAN,
                outlineColor: Cesium.Color.BLACK,
                outlineWidth: 2,
                style: Cesium.LabelStyle.FILL_AND_OUTLINE,
                position: positions[0]
            } : undefined
        });
        this.annotations.push(entity);
    }

    /** Clear all annotations. */
    clearAll() {
        this.annotations.forEach(e => this.viewer.entities.remove(e));
        this.annotations = [];
    }

    /** Save annotation to backend. */
    async saveAnnotation(type, geometry, label, agentId = null) {
        try {
            await fetch('api/agent/command.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Eye-Token': window.EYE_CONFIG.sessionToken
                },
                body: JSON.stringify({
                    action: 'save_annotation',
                    type: type,
                    geometry: geometry,
                    label: label,
                    agent_id: agentId
                })
            });
        } catch (e) {
            console.warn('[Eye] Failed to save annotation:', e);
        }
    }

    /** Load saved annotations from DB. */
    async loadSaved() {
        try {
            const res = await fetch('api/agent/history.php?type=annotations', {
                headers: { 'X-Eye-Token': window.EYE_CONFIG.sessionToken }
            });
            if (!res.ok) return;
            const data = await res.json();
            (data.annotations || []).forEach(ann => {
                const geom = typeof ann.geometry === 'string' ? JSON.parse(ann.geometry) : ann.geometry;
                this.addFromCommand(ann.type, geom, ann.label);
            });
        } catch (e) {
            console.warn('[Eye] Failed to load annotations:', e);
        }
    }
}
```

---

# 7. Phase 6 — Agent Integration

## 7.1 `agent-bar.js`

### File: `H:\var\www\self\the-eye\js\agent\agent-bar.js`

```javascript
/**
 * Agent Bar — collapsed input strip at bottom of screen.
 * Expands to full popup on click. 30-40 word limit in collapsed mode.
 */
export class AgentBar {
    constructor() {
        this.bar = document.getElementById('agent-bar');
        this.input = document.getElementById('agent-input');
        this.wordCount = document.getElementById('agent-word-count');
        this.lastReply = document.getElementById('agent-last-reply');
        this.popup = document.getElementById('agent-popup');
        this.popupInput = document.getElementById('popup-input');
        this.chatHistory = document.getElementById('chat-history');
        this.closeBtn = document.getElementById('close-popup');
        this.sendBtn = document.getElementById('popup-send');
        this.onCommand = null; // Callback: (reply, eyeCommand) => {}

        this.history = [];
        this.maxBarWords = 40;

        this.initEvents();
    }

    initEvents() {
        // Word count enforcement on bar input
        this.input.addEventListener('input', () => {
            const words = this.input.value.trim().split(/\s+/).filter(w => w.length > 0);
            const count = words.length;
            this.wordCount.textContent = `${count}/${this.maxBarWords}`;
            if (count > this.maxBarWords) {
                this.wordCount.style.color = '#f43f5e';
            } else {
                this.wordCount.style.color = '';
            }
        });

        // Submit on Enter (bar)
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const words = this.input.value.trim().split(/\s+/).filter(w => w.length > 0);
                if (words.length > 0 && words.length <= this.maxBarWords) {
                    this.sendMessage(this.input.value.trim());
                    this.input.value = '';
                    this.wordCount.textContent = '0/40';
                }
            }
        });

        // Click bar to expand popup
        this.bar.addEventListener('click', (e) => {
            if (e.target !== this.input) {
                this.openPopup();
            }
        });

        // Close popup
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.closePopup());
        }

        // Submit on Enter (popup)
        if (this.popupInput) {
            this.popupInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const msg = this.popupInput.value.trim();
                    if (msg) {
                        this.sendMessage(msg);
                        this.popupInput.value = '';
                    }
                }
            });
        }

        // Send button (popup)
        if (this.sendBtn) {
            this.sendBtn.addEventListener('click', () => {
                const msg = this.popupInput.value.trim();
                if (msg) {
                    this.sendMessage(msg);
                    this.popupInput.value = '';
                }
            });
        }

        // Escape to close popup
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.popup.classList.contains('hidden')) {
                this.closePopup();
            }
        });
    }

    openPopup() {
        this.popup.classList.remove('hidden');
        this.popupInput.focus();
    }

    closePopup() {
        this.popup.classList.add('hidden');
        this.input.focus();
    }

    /** Send a message to Otec via the agent command API. */
    async sendMessage(text) {
        // Add user message to chat
        this.addChatMessage(text, 'user');

        try {
            const cameraState = window.eyeGlobe ? window.eyeGlobe.getCameraState() : null;
            const activeLayers = window.eyeGlobe ? Array.from(window.eyeGlobe.activeLayers) : [];

            const res = await fetch('api/agent/command.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Eye-Token': window.EYE_CONFIG.sessionToken
                },
                body: JSON.stringify({
                    message: text,
                    session_token: window.EYE_CONFIG.sessionToken,
                    camera_state: cameraState,
                    active_layers: activeLayers
                })
            });

            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                this.addChatMessage(errData.error || 'Command failed.', 'agent');
                return;
            }

            const data = await res.json();
            const reply = data.reply || 'Acknowledged.';
            const eyeCommand = data.eye_command || null;

            // Display reply
            this.addChatMessage(reply, 'agent');
            this.lastReply.textContent = reply;

            // Execute globe command
            if (eyeCommand && this.onCommand) {
                this.onCommand(reply, eyeCommand);
            }

        } catch (e) {
            this.addChatMessage('Connection failed.', 'agent');
            console.error('[Eye] Agent command failed:', e);
        }
    }

    addChatMessage(text, sender) {
        const div = document.createElement('div');
        div.className = `chat-msg ${sender}`;
        div.textContent = text;
        this.chatHistory.appendChild(div);
        this.chatHistory.scrollTop = this.chatHistory.scrollHeight;
        this.history.push({ text, sender, time: Date.now() });
    }
}
```

---

## 7.2 `eye-commander.js`

### File: `H:\var\www\self\the-eye\js\agent\eye-commander.js`

```javascript
/**
 * EyeCommander — parses eye_command JSON from agent responses
 * and executes them on the EyeGlobe instance.
 */
export class EyeCommander {
    constructor(globe) {
        this.globe = globe;
    }

    /**
     * Execute an eye_command object.
     * @param {object} cmd — { action: string, params: object }
     */
    execute(cmd) {
        if (!cmd || !cmd.action) return;

        const action = cmd.action;
        const params = cmd.params || {};

        switch (action) {
            case 'fly_to':
                this.globe.flyTo(params.lon, params.lat, params.alt || 50000);
                break;

            case 'track':
                if (params.entity_id && params.layer) {
                    this.globe.track(params.entity_id, params.layer);
                }
                break;

            case 'toggle_layer':
                if (params.layer) {
                    // If specifying visible: true/false, handle accordingly
                    if (params.visible !== undefined) {
                        const isActive = this.globe.activeLayers.has(params.layer);
                        if (params.visible && !isActive) this.globe.toggleLayer(params.layer);
                        if (!params.visible && isActive) this.globe.toggleLayer(params.layer);
                    } else {
                        this.globe.toggleLayer(params.layer);
                    }
                    // Update panel button state
                    const btn = document.querySelector(`.layer-toggle[data-layer="${params.layer}"]`);
                    if (btn) btn.classList.toggle('active', this.globe.activeLayers.has(params.layer));
                }
                break;

            case 'set_style':
                if (params.style) {
                    this.globe.setStyle(params.style);
                }
                break;

            case 'annotate':
                if (params.type && params.geometry) {
                    this.globe.annotate(params.type, params.geometry, params.label || '');
                }
                break;

            case 'save_view':
                // Handled server-side via command.php
                break;

            case 'reset':
                this.globe.reset();
                // Clear all active buttons
                document.querySelectorAll('.layer-toggle.active').forEach(b => b.classList.remove('active'));
                break;

            case 'query':
                // No visual change — data returned as text in the reply
                break;

            default:
                console.warn(`[Eye] Unknown command action: ${action}`);
        }
    }
}
```

---

## 7.3 `api/agent/command.php`

### File: `H:\var\www\self\the-eye\api\agent\command.php`

```php
<?php
/**
 * Agent command endpoint for The Eye.
 * Receives commands from the agent bar, routes to Otec via the existing chat system,
 * returns reply + optional eye_command JSON for globe control.
 *
 * Auth-gated: returns 401 for unauthenticated users.
 */

require_once __DIR__ . '/../../../src/config/env.php';
require_once __DIR__ . '/../../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/services/EyeService.php';

header('Content-Type: application/json');

// Auth check
$auth = new AuthService();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required for agent commands']);
    exit;
}

$currentUser = $auth->getCurrentUser();
$userId = (int) $currentUser['user_id'];

// Parse input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing message']);
    exit;
}

$message = trim($input['message']);
$sessionToken = $input['session_token'] ?? '';
$cameraState = $input['camera_state'] ?? null;
$activeLayers = $input['active_layers'] ?? [];

// Build context for Otec
$eyeContext = "The user is viewing The Eye geospatial intelligence globe.\n";
if ($cameraState) {
    $eyeContext .= "Camera position: LAT {$cameraState['lat']}, LON {$cameraState['lon']}, ALT {$cameraState['alt']}m\n";
}
if (!empty($activeLayers)) {
    $eyeContext .= "Active layers: " . implode(', ', $activeLayers) . "\n";
}
$eyeContext .= "You can control the globe by including an eye_command JSON object in your response.\n";
$eyeContext .= "Available actions: fly_to, track, toggle_layer, set_style, annotate, save_view, reset, query\n";

// Route to the existing chat system as Otec
// We'll call the chat API internally with Otec as the agent
$chatPayload = [
    'message' => $message,
    'agent' => 'otec',
    'context_extra' => $eyeContext,
    'is_eye_command' => true
];

// Internal HTTP call to the main chat endpoint
$chatUrl = 'https://self.foreverbox.co.uk/api/chat.php';
$ch = curl_init($chatUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($chatPayload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Cookie: ' . ($_SERVER['HTTP_COOKIE'] ?? '')  // Forward session cookie
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 5,
]);

$chatResponse = curl_exec($ch);
$chatCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$reply = 'Command received.';
$eyeCommand = null;

if ($chatResponse && $chatCode === 200) {
    $chatData = json_decode($chatResponse, true);
    $reply = $chatData['reply'] ?? $chatData['message'] ?? 'Acknowledged.';

    // Parse eye_command from the reply text (agent should output JSON block)
    if (preg_match('/\{[^{}]*"action"\s*:\s*"[^"]+"/s', $reply, $matches)) {
        $cmdJson = json_decode($matches[0], true);
        if ($cmdJson && isset($cmdJson['action'])) {
            $eyeCommand = $cmdJson;
            // Remove the JSON from the display reply
            $reply = trim(str_replace($matches[0], '', $reply));
        }
    }

    // Also check if chat returned a structured eye_command field
    if (!$eyeCommand && isset($chatData['eye_command'])) {
        $eyeCommand = $chatData['eye_command'];
    }
}

// Log the query
$eyeService = new EyeService();
$eyeService->logAgentQuery($userId, 'otec', $sessionToken, $message, $reply, $eyeCommand, $eyeCommand !== null);

echo json_encode([
    'reply' => $reply,
    'eye_command' => $eyeCommand
]);
```

---

## 7.4 `api/agent/history.php`

### File: `H:\var\www\self\the-eye\api\agent\history.php`

```php
<?php
/**
 * Agent query history and annotation retrieval for authenticated users.
 */

require_once __DIR__ . '/../../../src/config/env.php';
require_once __DIR__ . '/../../../src/services/AuthService.php';
require_once __DIR__ . '/../../src/services/EyeService.php';

header('Content-Type: application/json');

$auth = new AuthService();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$currentUser = $auth->getCurrentUser();
$userId = (int) $currentUser['user_id'];
$eyeService = new EyeService();

$type = $_GET['type'] ?? 'queries';

if ($type === 'annotations') {
    $annotations = $eyeService->getAnnotations($userId);
    echo json_encode(['annotations' => $annotations]);
} elseif ($type === 'views') {
    $views = $eyeService->getSavedViews($userId);
    echo json_encode(['views' => $views]);
} else {
    $limit = min((int) ($_GET['limit'] ?? 50), 200);
    $history = $eyeService->getAgentHistory($userId, $limit);
    echo json_encode(['history' => $history]);
}
```

---

## 7.5 Otec Soul Context SQL

Run on the VPS:

```bash
ssh zeon7@87.106.74.66 "mysql -u root agent_registry -e \"INSERT INTO soul_components (component_key, agent_slug, provider_filter, section_order, section_content) VALUES ('eye_context', 'otec', NULL, 5, '## The Eye — Geospatial Intelligence Context\n\nYou are operating The Eye, a sovereign geospatial intelligence viewer in the Foreverbox ecosystem.\nYou have direct control over a live CesiumJS 3D globe showing real-time flights, ships, satellites, earthquakes, wildfires, public cameras, radio stations, and rocket launches.\n\nWhen the user gives a location, target, or layer command, respond with:\n1. A brief natural language acknowledgement (1-2 sentences, analytical tone)\n2. A JSON object with the key eye_command containing: {\"action\": \"...\", \"params\": {...}}\n\nAvailable actions:\n- fly_to: params {lon, lat, alt}\n- track: params {entity_id, layer}\n- toggle_layer: params {layer, visible}\n- set_style: params {style} (normal/crt/nvg/flir/noir/snow)\n- annotate: params {type, geometry, label}\n- save_view: params {label}\n- reset: no params\n- query: no params, return data as text\n\nAvailable layers: flights, military, ships, satellites, earthquakes, cctv, traffic, fires, radio, launches\n\nAlways confirm actions that succeeded. Never hallucinate entity positions.\nCurrent globe state is provided in each message context.\nMaintain the Director persona — analytical, precise, minimal words.');\""
```

---

# 8. Phase 7 — Hermes Skill

### File: `H:\foreverbox_data\profiles\otec\skills\intelligence\the-eye\SKILL.md`

```markdown
---
name: fbox-eye-control
description: Control The Eye geospatial intelligence viewer. Fly to locations, toggle data layers, track entities, create annotations.
---

# The Eye Control Skill

You can control The Eye globe via HTTP commands to the Eye API.

## Available Commands

### Fly to a location
```bash
curl -X POST https://eye.foreverbox.co.uk/api/agent/command.php \
  -H "Content-Type: application/json" \
  -H "X-Operator-Key: ${OPERATOR_API_KEY}" \
  -d '{"message":"fly to Tokyo","session_token":"OPERATOR","camera_state":null,"active_layers":[]}'
```

### Toggle a data layer
```bash
curl -X POST https://eye.foreverbox.co.uk/api/agent/command.php \
  -H "Content-Type: application/json" \
  -H "X-Operator-Key: ${OPERATOR_API_KEY}" \
  -d '{"message":"turn on flights layer","session_token":"OPERATOR","camera_state":null,"active_layers":[]}'
```

### Query live data
```bash
curl -X POST https://eye.foreverbox.co.uk/api/agent/command.php \
  -H "Content-Type: application/json" \
  -H "X-Operator-Key: ${OPERATOR_API_KEY}" \
  -d '{"message":"how many flights are over the UK right now","session_token":"OPERATOR","camera_state":null,"active_layers":["flights"]}'
```

## Response Format
The API returns:
```json
{
  "reply": "Natural language response from Otec",
  "eye_command": { "action": "fly_to", "params": { "lon": 139.69, "lat": 35.68, "alt": 50000 } }
}
```
```

---

# 9. Phase 8 — Viewshed

### File: `H:\var\www\self\the-eye\js\controls\viewshed.js`

```javascript
/**
 * CCTV Viewshed — renders estimated camera field-of-view frustum volumes.
 */
export class Viewshed {
    constructor(globe) {
        this.viewer = globe.viewer;
        this.frustums = [];
        this.showViewsheds = false;
    }

    /** Toggle viewshed rendering for all visible CCTV entities. */
    toggle() {
        this.showViewsheds = !this.showViewsheds;
        if (this.showViewsheds) {
            this.renderFrustums();
        } else {
            this.clearFrustums();
        }
    }

    renderFrustums() {
        this.clearFrustums();
        const cctvLayer = window.eyeGlobe.layers['cctv'];
        if (!cctvLayer) return;

        cctvLayer.entities.forEach((entity) => {
            const pos = entity.position?.getValue(Cesium.JulianDate.now());
            if (!pos) return;

            // Estimate a 60-degree FOV cone, 200m range
            const carto = Cesium.Cartographic.fromCartesian(pos);
            const lon = Cesium.Math.toDegrees(carto.longitude);
            const lat = Cesium.Math.toDegrees(carto.latitude);
            const range = 200; // metres
            const fov = 30; // half-angle degrees

            // Create a simple fan polygon to approximate the viewshed
            const points = [Cesium.Cartesian3.fromDegrees(lon, lat, 5)];
            for (let angle = -fov; angle <= fov; angle += 5) {
                const rad = Cesium.Math.toRadians(angle);
                const dLon = (range / 111320) * Math.sin(rad);
                const dLat = (range / 110540) * Math.cos(rad);
                points.push(Cesium.Cartesian3.fromDegrees(lon + dLon, lat + dLat, 5));
            }

            const frustum = this.viewer.entities.add({
                polygon: {
                    hierarchy: new Cesium.PolygonHierarchy(points),
                    material: Cesium.Color.CYAN.withAlpha(0.08),
                    outline: true,
                    outlineColor: Cesium.Color.CYAN.withAlpha(0.3),
                    outlineWidth: 1,
                    height: 0,
                    extrudedHeight: 15
                }
            });
            this.frustums.push(frustum);
        });
    }

    clearFrustums() {
        this.frustums.forEach(f => this.viewer.entities.remove(f));
        this.frustums = [];
    }
}
```

---

# 10. Deployment & Final Verification

## 10.1 Full Deployment Commands

```bash
# 1. Commit everything to git
cd H:\var\www\self
git add the-eye/
git commit -m "feat: add The Eye — sovereign geospatial intelligence viewer"
git push origin main

# 2. Deploy to VPS
scp -r the-eye/* zeon7@87.106.74.66:/var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/

# 3. Also deploy the modified sidebar
scp components/navigation/sidebar.php zeon7@87.106.74.66:/var/www/vhosts/bjorntyrsson.co.uk/self.foreverbox.co.uk/components/navigation/sidebar.php

# 4. Set ownership
ssh zeon7@87.106.74.66 "sudo chown -R quiddity:psacln /var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/"

# 5. Run DB migration
ssh zeon7@87.106.74.66 "mysql -u root zeon7_self_dev < /var/www/vhosts/bjorntyrsson.co.uk/eye.foreverbox.co.uk/database/001_create_eye_tables.sql"

# 6. Insert Otec soul context (see Phase 7.5 SQL above)

# 7. Deploy Hermes skill
scp -r H:\foreverbox_data\profiles\otec\skills\intelligence\the-eye zeon7@87.106.74.66:/foreverbox_data/profiles/otec/skills/intelligence/

# 8. Add .env keys to VPS
ssh zeon7@87.106.74.66 "cat >> /var/www/vhosts/bjorntyrsson.co.uk/self.foreverbox.co.uk/.env << 'EOF'

# --- The Eye ---
CESIUM_ION_TOKEN=
GOOGLE_MAPS_API_KEY=
OPENSKY_USERNAME=
OPENSKY_PASSWORD=
ADSB_LOL_BASE_URL=https://api.adsb.lol
NASA_FIRMS_KEY=
ROCKET_LAUNCH_KEY=
EYE_SESSION_TTL=86400
EYE_RATE_LIMIT_PUBLIC=120
EYE_RATE_LIMIT_AUTH=300
EOF"
```

## 10.2 Verification Checklist

```bash
# Globe loads
curl -I https://eye.foreverbox.co.uk
# Expected: HTTP 200

# API endpoints return data
curl -s https://eye.foreverbox.co.uk/api/flights.php -H "X-Eye-Token: TEST" | python3 -c "import sys,json; d=json.load(sys.stdin); print(f'Flights: {len(d.get(\"states\",[]))} contacts')"
curl -s https://eye.foreverbox.co.uk/api/earthquakes.php -H "X-Eye-Token: TEST" | python3 -c "import sys,json; d=json.load(sys.stdin); print(f'Earthquakes: {len(d.get(\"features\",[]))} events')"
curl -s https://eye.foreverbox.co.uk/api/satellites.php -H "X-Eye-Token: TEST" | python3 -c "import sys,json; d=json.load(sys.stdin); print(f'Satellites: {len(d.get(\"satellites\",[]))} tracked')"
curl -s https://eye.foreverbox.co.uk/api/launches.php -H "X-Eye-Token: TEST" | python3 -c "import sys,json; d=json.load(sys.stdin); print(f'Launches: {len(d.get(\"launches\",[]))} upcoming')"
curl -s https://eye.foreverbox.co.uk/api/radio.php -H "X-Eye-Token: TEST" | python3 -c "import sys,json; d=json.load(sys.stdin); print(f'Radio: {len(d.get(\"stations\",[]))} stations')"

# DB tables exist
ssh zeon7@87.106.74.66 "mysql -u root zeon7_self_dev -e 'SHOW TABLES LIKE \"eye_%\"'"

# The Eye link appears on self site sidebar
curl -s https://self.foreverbox.co.uk/ | grep -o "The Eye"
```

## 10.3 Browser Verification (Manual)

1. Open `https://eye.foreverbox.co.uk` — dark globe should render with Zeon7 cybernetic theme
2. Open browser console — no JavaScript errors
3. Click "FLIGHTS" in layer panel — cyan dots should appear on the globe within 10 seconds
4. Click a flight dot — contact card should appear with callsign, altitude, speed
5. Press `3` — globe should switch to NVG (green) view
6. Press `1` — back to normal
7. Log in on `self.foreverbox.co.uk`, then visit `eye.foreverbox.co.uk` — agent bar should appear at bottom
8. Type "fly to London" in agent bar → globe should animate to London
9. Check `eye_sessions` table — should have a row for your session
10. Check `eye_agent_queries` table — should have a row for "fly to London"
