<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();

require_once __DIR__ . '/../src/Services/DashboardService.php';
require_once __DIR__ . '/../src/Services/ConfigService.php';

// Initialise services
$dashboardService = new DashboardService();
$configService = new ConfigService();

// Fetch Data
$totalTokens = $configService->getTotalTokens();
$knowledgeCount = $dashboardService->getKnowledgeCount();
$dailyTheme = $dashboardService->getDailyTheme();
$apiRequests = $dashboardService->getApiRequestCount();
$scannedLeads = $dashboardService->getScannedLeads();

$operatorName = $_SESSION['user_name'] ?? 'MERRILL LEO'; 
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Mission Control ? Cybernetic Matrix</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .section-header-hud {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--color-cyan);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(34, 211, 238, 0.2);
            padding-bottom: 0.4rem;
        }
        .status-table {
            width: 100%;
            font-family: var(--font-mono);
            font-size: 0.8rem;
        }
        .status-table td {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'MISSION CONTROL';
        $pageSubtitle = 'CYBERNETIC COCKPIT';
        include 'components/header.php';
        ?>

        <div class="dashboard-container">
            
            <!-- Top Metrics Row -->
            <div class="metrics-grid">
                <!-- Total Tokens -->
                <div class="hud-border stat-card" data-tilt>
                    <div class="hud-corner-tr"></div>
                    <div class="hud-corner-bl"></div>
                    <div class="stat-icon">??</div>
                    <div class="stat-label">Neural Tokens Fired</div>
                    <div class="stat-val text-cyan" data-val="<?php echo (int)$totalTokens; ?>">
                        <?php echo number_format((int)$totalTokens); ?>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--color-primary); font-family: var(--font-mono);">
                        ? ACTIVE STREAMING
                    </div>
                </div>

                <!-- Knowledge Documents -->
                <div class="hud-border stat-card" data-tilt>
                    <div class="hud-corner-tr"></div>
                    <div class="hud-corner-bl"></div>
                    <div class="stat-icon">??</div>
                    <div class="stat-label">Knowledge Docs & Chunks</div>
                    <div class="stat-val text-green" data-val="<?php echo (int)$knowledgeCount; ?>">
                        <?php echo (int)$knowledgeCount; ?>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-family: var(--font-mono);">
                        SYNCHRONISED WITH DB
                    </div>
                </div>

                <!-- API Operations -->
                <div class="hud-border stat-card" data-tilt>
                    <div class="hud-corner-tr"></div>
                    <div class="hud-corner-bl"></div>
                    <div class="stat-icon">?</div>
                    <div class="stat-label">API Invocations</div>
                    <div class="stat-val text-orange" data-val="<?php echo (int)$apiRequests; ?>">
                        <?php echo number_format((int)$apiRequests); ?>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--color-orange); font-family: var(--font-mono);">
                        LATENCY: ~14MS (NOMINAL)
                    </div>
                </div>
            </div>

            <!-- Main Dual Column HUD Layout -->
            <div class="dashboard-grid">
                
                <!-- Left Column: Operational Matrix Modules -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="section-header-hud">
                        <span>OPERATIONAL SUBSYSTEMS</span>
                        <span style="font-size: 0.7rem; color: var(--color-primary);">6 MATRIX CORES ACTIVE</span>
                    </div>

                    <div class="action-grid">
                        <!-- News Desk Card -->
                        <a href="news-desk.php" class="hud-border action-card" data-tilt>
                            <div class="hud-corner-tr"></div>
                            <div class="hud-corner-bl"></div>
                            <span class="card-icon">??</span>
                            <span class="card-title">News Desk</span>
                            <div class="card-desc">
                                Active Protocol: <strong class="text-cyan"><?php echo is_array($dailyTheme) ? htmlspecialchars($dailyTheme['theme'] ?? 'Standard') : htmlspecialchars($dailyTheme); ?></strong><br>
                                <?php if (empty($scannedLeads)): ?>
                                    Lead Scanner: <span class="text-muted">Awaiting Dispatch</span>
                                <?php else: ?>
                                    Lead Scanner: <span class="text-green">ONLINE (<?php echo count($scannedLeads); ?> LEADS)</span>
                                <?php endif; ?>
                            </div>
                        </a>

                        <!-- Vision Studio Card -->
                        <a href="vision.php" class="hud-border action-card" data-tilt>
                            <div class="hud-corner-tr"></div>
                            <div class="hud-corner-bl"></div>
                            <span class="card-icon">??</span>
                            <span class="card-title">Vision Studio</span>
                            <div class="card-desc">
                                Multimodal visual scanning, image processing, and prompt portfolio asset curation.
                            </div>
                        </a>

                        <!-- Memory Lore Card -->
                        <a href="lore.php" class="hud-border action-card" data-tilt>
                            <div class="hud-corner-tr"></div>
                            <div class="hud-corner-bl"></div>
                            <span class="card-icon">?</span>
                            <span class="card-title">Memory Bank (Lore)</span>
                            <div class="card-desc">
                                Edit immutable core facts, biographical anchors, and contextual personas.
                            </div>
                        </a>

                        <!-- Instructions Card -->
                        <a href="instructions.php" class="hud-border action-card" data-tilt>
                            <div class="hud-corner-tr"></div>
                            <div class="hud-corner-bl"></div>
                            <span class="card-icon">?</span>
                            <span class="card-title">CRISPE Instructions</span>
                            <div class="card-desc">
                                Versioned AI prompt architectures, sourcing frameworks, and behaviour policies.
                            </div>
                        </a>

                        <!-- Knowledge Base Card -->
                        <a href="knowledge.php" class="hud-border action-card" data-tilt>
                            <div class="hud-corner-tr"></div>
                            <div class="hud-corner-bl"></div>
                            <span class="card-icon">??</span>
                            <span class="card-title">Knowledge Engine</span>
                            <div class="card-desc">
                                Ingest markdown corpus with full-text chunking & neural vector readiness.
                            </div>
                        </a>

                        <!-- Settings & Keyring Card -->
                        <a href="settings.php" class="hud-border action-card" data-tilt>
                            <div class="hud-corner-tr"></div>
                            <div class="hud-corner-bl"></div>
                            <span class="card-icon">??</span>
                            <span class="card-title">System Settings</span>
                            <div class="card-desc">
                                Gemini / OpenRouter API configurations and AES-256 encrypted key management.
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Right Column: System Status Matrix & Live Terminal -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
                    <!-- System Health HUD Panel -->
                    <div class="hud-border" data-tilt>
                        <div class="hud-corner-tr"></div>
                        <div class="hud-corner-bl"></div>
                        <div class="section-header-hud">
                            <span>SYSTEM MATRIX STATUS</span>
                            <span class="status-dot"></span>
                        </div>
                        <table class="status-table">
                            <tr>
                                <td style="color:var(--text-muted);">MariaDB Database</td>
                                <td style="text-align:right;"><span class="hud-badge green" style="padding:2px 6px;">ONLINE</span></td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">GSAP Kinetic Engine</td>
                                <td style="text-align:right;"><span class="hud-badge green" style="padding:2px 6px;">ACTIVE</span></td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">AI Neural Core</td>
                                <td style="text-align:right;"><span class="hud-badge green" style="padding:2px 6px;">READY</span></td>
                            </tr>
                            <tr>
                                <td style="color:var(--text-muted);">SSL Subdomain Layer</td>
                                <td style="text-align:right;"><span class="hud-badge green" style="padding:2px 6px;">VALID</span></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Live Terminal Panel -->
                    <?php include 'components/terminal-panel.php'; ?>

                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
