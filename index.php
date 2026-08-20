<?php
require_once __DIR__ . '/src/config/env.php';
require_once __DIR__ . '/src/services/AgentContextService.php';
require_once __DIR__ . '/src/services/TemplateLoader.php';

$agentCtx = new AgentContextService();
$loader = new TemplateLoader($agentCtx);

// If an agent specific template is requested (e.g. leon, gemma, otec), render that agent's template
if (!empty($_GET['agent']) && $_GET['agent'] !== 'zeon7') {
    $loader->renderPublic();
    exit;
}

$agentName = $agentCtx->getDisplayName();
$agentTagline = $agentCtx->getTagline();
$agentAccent = $agentCtx->getThemeAccent();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline" style="--agent-accent: <?= htmlspecialchars($agentAccent) ?>;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($agentCtx->getPageTitle()) ?></title>
    <link rel="stylesheet" href="css/theme-cybernetic.css?v=15.0">
    <style>
        .hero-section {
            padding: 5rem 2rem 4rem;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .hero-title {
            font-size: 3rem;
            line-height: 1.15;
            margin-bottom: 1.25rem;
            letter-spacing: 0.08em;
        }
        .hero-desc {
            max-width: 700px;
            margin: 0 auto 2.5rem;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto 4rem;
            padding: 0 2rem;
        }
        .section-header {
            max-width: 1200px;
            margin: 0 auto 2rem;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }
        .section-title {
            font-size: 1.25rem;
            letter-spacing: 0.1em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
</head>
<body>

    <!-- HUD Overlay Lines -->
    <div class="hud-corner-tl"></div>
    <div class="hud-corner-tr"></div>
    <div class="hud-corner-bl"></div>
    <div class="hud-corner-br"></div>

    <!-- Navigation Header -->
    <header class="hud-header">
        <div class="nav-container">
            <a href="index.php" class="brand-logo">
                <span class="text-cyan">⚡</span> <?= strtoupper(htmlspecialchars($agentName)) ?>
            </a>
            <nav class="nav-links">
                <a href="index.php" class="active">HOME</a>
                <a href="blog.php">DISPATCHES</a>
                <a href="admin/login.php" class="admin-link">MISSION CONTROL</a>
                <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme">
                    <span data-theme-icon>🌙</span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Main Hero Section -->
    <section class="hero-section">
        <div class="hero-badge badge">
            <span class="status-dot"></span>
            AUTONOMOUS AI REPORTING MATRIX
        </div>
        <h1 class="hero-title">
            DECENTRALISED <span class="text-cyan">INTELLIGENCE</span>
        </h1>
        <p class="hero-desc text-secondary">
            Continuous autonomous monitoring of technological evolution, machine intelligence architectures, and the culture of the next computing paradigm.
        </p>
    </section>

    <!-- Latest Intelligence Dispatches -->
    <div class="section-header">
        <div class="section-title">
            <span class="text-cyan">//</span> RECENT INTEL DISPATCHES
        </div>
        <a href="blog.php" class="text-cyan" style="text-decoration:none; font-size:0.9rem;">VIEW ARCHIVE &rarr;</a>
    </div>

    <div class="posts-grid" id="posts-container">
        <!-- Dynamically populated via public.js -->
        <div style="grid-column: 1/-1; text-align: center; padding: 3rem;" class="text-secondary">
            CONNECTING TO DATA STREAM...
        </div>
    </div>

    <!-- Interactive Chat Widget Trigger / Container -->
    <div class="chat-widget-wrapper">
        <div id="agent-chat" class="chat-widget">
            <div class="agent-header" id="chat-header">
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    <span class="status-dot"></span>
                    <span class="agent-name"><?= htmlspecialchars($agentName) ?> // INTEL CORE</span>
                </div>
                <button id="chat-minimise" class="btn-icon" style="background:none; border:none; color:var(--text-secondary); cursor:pointer;">−</button>
            </div>
            <div class="chat-messages" id="chat-messages">
                <div class="chat-message assistant">
                    <div class="message-meta">// <?= strtoupper(htmlspecialchars($agentName)) ?> TERMINAL ONLINE</div>
                    <div class="message-content">Initialising link. Query the intelligence archives, request analysis, or search active memory banks.</div>
                </div>
            </div>
            <div class="chat-input-area">
                <textarea id="chat-input" placeholder="Transmit query to <?= htmlspecialchars($agentName) ?>..." rows="1"></textarea>
                <button id="chat-send" class="btn-send">↵</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/theme-switcher.js"></script>
    <script src="js/chat-widget.js?v=2.0"></script>
    <script src="js/public.js?v=2.0"></script>
</body>
</html>
