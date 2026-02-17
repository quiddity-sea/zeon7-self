<?php
require_once __DIR__ . '/../src/Services/DashboardService.php';
require_once __DIR__ . '/../src/Services/ConfigService.php';

// Initialize services
$dashboardService = new DashboardService();
$configService = new ConfigService();

// Fetch Data
$totalTokens = $configService->getTotalTokens();
$apiRequests = $dashboardService->getApiRequestCount();
$knowledgeCount = $dashboardService->getKnowledgeCount();
$instructionPreview = $dashboardService->getActiveInstructionPreview();
$dailyTheme = $dashboardService->getDailyTheme();
$scannedLeads = $dashboardService->getScannedLeads();

// User Name (Mock for now, or pull from session if available)
$operatorName = $_SESSION['user_name'] ?? 'MERRILL LEO'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zeon7 Mission Control</title>
    <link href="https://fonts.googleapis.com/css2?family=Maven+Pro:wght@400;500;600&family=Montserrat:wght@400;600;800;900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=4">
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'MISSION CONTROL';
        $pageSubtitle = 'SYSTEM OVERVIEW';
        include 'components/header.php';
        ?>

        <div class="dashboard-container">
            
            <!-- Row 2: Metrics & Status -->
            <div class="row-metrics-status">
                <!-- Col 1: System Metrics -->
                <div>
                    <div class="section-header">System Metrics</div>
                    <div class="stats-row">
                        <div class="stat-card">
                            <div class="stat-val" style="color:var(--cyan)"><?php echo number_format($totalTokens); ?></div>
                            <div class="stat-label">Total Tokens</div>
                            <div class="stat-icon">🧠</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-val" style="color:var(--orange)">0</div>
                            <div class="stat-label">Images Pending</div>
                            <div class="stat-icon">👁</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-val"><?php echo number_format($apiRequests); ?></div>
                            <div class="stat-label">API Requests</div>
                            <div class="stat-icon">⚡</div>
                        </div>
                    </div>
                </div>

                <!-- Col 2: System Status -->
                <div>
                    <div class="section-header">System Status</div>
                    <div class="stat-card" style="background: rgba(11, 18, 25, 0.4); height: auto;">
                        <div style="font-family: var(--font-ui); font-size: 0.8rem; color: var(--text-muted);">
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                                <span>Database</span><span style="color:#00ff00">ONLINE</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                                <span>AI Core</span><span style="color:#00ff00">ONLINE</span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span>Memory Bank</span><span style="color:#00ff00">SYNCED</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Modules & Terminal -->
            <div class="row-modules-terminal">
                <!-- Col 1: Operational Modules -->
                <div class="col-modules">
                    <div class="section-header">Operational Modules</div>
                    <div class="action-grid">
                        <!-- News Desk Card -->
                        <a href="news-desk.php" class="action-card">
                            <span class="card-icon">📰</span>
                            <span class="card-title">News Desk</span>
                            <div class="card-desc">
                                Active Theme: <strong style="color:var(--cyan)"><?php echo is_array($dailyTheme) ? htmlspecialchars($dailyTheme['theme']) : htmlspecialchars($dailyTheme); ?></strong><br>
                                <?php if (empty($scannedLeads)): ?>
                                    Leads Scanned: <span style="color:var(--text-muted)">No</span>
                                <?php else: ?>
                                    Leads Scanned: <span style="color:var(--cyan)">YES</span><br>
                                    <ul style="margin:0.5rem 0 0 1rem; font-size:0.8rem;">
                                        <?php foreach ($scannedLeads as $lead): ?>
                                            <li><?php echo htmlspecialchars($lead); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </a>

                        <!-- Vision Studio Card -->
                        <a href="vision.php" class="action-card">
                            <span class="card-icon">👁</span>
                            <span class="card-title">Vision Studio</span>
                            <div class="card-desc">
                                Process incoming visuals and manage portfolios.
                            </div>
                        </a>

                        <!-- Lore Manager Card -->
                        <a href="lore.php" class="action-card">
                            <span class="card-icon">📚</span>
                            <span class="card-title">Lore Manager</span>
                            <div class="card-desc">
                                Edit deep memory and biography facts.
                            </div>
                        </a>

                        <!-- Settings Card -->
                        <a href="settings.php" class="action-card">
                            <span class="card-icon">⚙️</span>
                            <span class="card-title">Settings</span>
                            <div class="card-desc">
                                Configure AI Provider and API Keys.
                            </div>
                        </a>

                        <!-- Instructions Card -->
                        <a href="instructions.php" class="action-card">
                            <span class="card-icon">📝</span>
                            <span class="card-title">Instructions</span>
                            <div class="card-desc">
                                "Zeon7 AI Prompt - CRISPE Framework (V3.7 - Sourcing & Production Split) C - Context: The "Why" Goal: To deploy an AI persona, Zeon7, that perfectly emulates..."
                            </div>
                        </a>

                        <!-- Knowledge Card -->
                        <a href="knowledge.php" class="action-card">
                            <span class="card-icon">🧠</span>
                            <span class="card-title">Knowledge Base</span>
                            <div class="card-desc">
                                Files Processed: <strong style="color:var(--cyan)"><?php echo $knowledgeCount; ?></strong>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Terminal Output -->
                <div class="col-terminal">
                    <div class="section-header">
                        <span>ZEON7 - SYSTEM TERMINAL</span>
                        <span style="font-size:0.7rem; color:var(--text-muted)">TOKENS USED: <?php echo number_format($totalTokens); ?></span>
                    </div>
                    <div class="terminal-window" style="flex:1; background:#000; border:1px solid #333; padding:1rem; font-family:'Courier New', monospace; font-size:0.9rem; color:#0f0; overflow-y:auto;">
                        <div>Initializing Zeon7 Interface...</div>
                        <div>Loading modules...</div>
                        <div style="color:var(--cyan)">> SYSTEM READY.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>