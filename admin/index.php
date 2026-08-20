<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();

require_once __DIR__ . '/../src/config/env.php';
require_once __DIR__ . '/../src/services/AgentContextService.php';
require_once __DIR__ . '/../src/services/DashboardService.php';
require_once __DIR__ . '/../src/services/ConfigService.php';
require_once __DIR__ . '/../src/services/InstructionService.php';

$agentCtx = new AgentContextService();
$agentName = $agentCtx->getDisplayName();
$agentId = $agentCtx->getAgentId();
$agentAccent = $agentCtx->getThemeAccent();
$agentRole = $agentCtx->getRole();
$agentTagline = $agentCtx->getTagline();

$dashboardService = new DashboardService();
$configService = new ConfigService();
$instructionService = new InstructionService($agentCtx);

// Fetch Data
$totalTokens = $configService->getTotalTokens();
$knowledgeCount = $dashboardService->getKnowledgeCount();
$dailyTheme = $dashboardService->getDailyTheme();
$apiRequests = $dashboardService->getApiRequestCount();
$scannedLeads = $dashboardService->getScannedLeads();
$currentInstruction = $instructionService->getCurrentContent($agentId);
$operatorName = $_SESSION['user_name'] ?? 'MERRILL LEO'; 
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline" style="--agent-accent: <?= htmlspecialchars($agentAccent) ?>;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($agentName) ?> Mission Control — ForeverBox Triad</title>
    <link rel="stylesheet" href="../css/theme-cybernetic.css?v=15.0">
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
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .agent-hero-banner {
            background: rgba(18, 22, 28, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-left: 4px solid var(--agent-accent);
            padding: 1.25rem 1.5rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body class="admin-body">

    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <div class="admin-main">
        <?php 
            $pageTitle = strtoupper($agentName) . " TELEMETRY";
            $pageSubtitle = "MISSION CONTROL // " . strtoupper($agentId);
            include __DIR__ . '/components/header.php'; 
        ?>

        <div style="padding: 1.5rem 2rem;">
            
            <!-- Active Agent Identity Banner -->
            <div class="agent-hero-banner">
                <div>
                    <div style="font-size: 1.35rem; font-weight: bold; color: var(--agent-accent); display: flex; align-items: center; gap: 0.5rem;">
                        <span><?= htmlspecialchars($agentName) ?></span>
                        <span style="font-size: 0.8rem; background: rgba(255,255,255,0.06); padding: 0.2rem 0.6rem; border-radius: 3px; color: var(--text-secondary);">
                            [<?= strtoupper($agentId) ?>]
                        </span>
                    </div>
                    <div style="font-size: 0.95rem; color: var(--text-primary); margin-top: 0.25rem;">
                        <?= htmlspecialchars($agentTagline) ?>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">
                        <?= htmlspecialchars($agentRole) ?>
                    </div>
                </div>
                <div>
                    <a href="../index.php?agent=<?= urlencode($agentId) ?>" target="_blank" class="btn" 
                       style="background: rgba(255,255,255,0.05); border: 1px solid var(--agent-accent); color: var(--agent-accent); text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                        Open Public Interface ↗
                    </a>
                </div>
            </div>

            <!-- Global / Agent Telemetry Metrics -->
            <div class="metrics-grid">
                <div class="hud-panel" style="padding: 1.25rem;">
                    <div class="section-header-hud">
                        <span>LORE & KNOWLEDGE</span>
                        <i>🧠</i>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--text-primary);">
                        <?= number_format($knowledgeCount ?? 0) ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                        Indexed Chunks in Lore Sea
                    </div>
                </div>

                <div class="hud-panel" style="padding: 1.25rem;">
                    <div class="section-header-hud">
                        <span>API REQUESTS</span>
                        <i>⚡</i>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--agent-accent);">
                        <?= number_format($apiRequests ?? 0) ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                        Total Execution Turns
                    </div>
                </div>

                <div class="hud-panel" style="padding: 1.25rem;">
                    <div class="section-header-hud">
                        <span>TOKEN CONSUMPTION</span>
                        <i>📊</i>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: bold; color: var(--text-primary);">
                        <?= number_format($totalTokens ?? 0) ?>
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                        Active Token Budget
                    </div>
                </div>
            </div>

            <!-- Agent Domain Specific Workspaces -->
            <div class="dashboard-grid">
                
                <!-- Left Column: Active Persona Directive -->
                <div class="hud-panel" style="padding: 1.5rem; display: flex; flex-direction: column;">
                    <div class="section-header-hud" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                        <span style="font-weight: 600; color: var(--agent-accent);">
                            ACTIVE INSTRUCTION DIRECTIVE (<?= strtoupper($agentName) ?>)
                        </span>
                        <a href="instructions.php" style="color: var(--text-secondary); text-decoration: none; font-size: 0.8rem;">
                            Edit Heads / Prompt →
                        </a>
                    </div>
                    
                    <div style="flex: 1; background: rgba(0,0,0,0.4); padding: 1rem; border-radius: 4px; border: 1px solid rgba(255,255,255,0.05); font-family: monospace; font-size: 0.82rem; line-height: 1.5; color: var(--text-secondary); max-height: 380px; overflow-y: auto; white-space: pre-wrap;">
<?= htmlspecialchars(mb_substr($currentInstruction, 0, 1500)) . (mb_strlen($currentInstruction) > 1500 ? "\n\n... [Click Edit Heads to view full instruction] ..." : '') ?>
                    </div>
                </div>

                <!-- Right Column: Agent-Specific Domain Panel -->
                <div class="hud-panel" style="padding: 1.5rem;">
                    <?php if ($agentId === 'zeon7'): ?>
                        <!-- Zeon7: From the Noise / Media Production -->
                        <div class="section-header-hud" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                            <span style="font-weight: 600; color: var(--agent-accent);">FROM THE NOISE // NEWS DESK</span>
                            <a href="news-desk.php" style="color: var(--text-secondary); font-size: 0.8rem; text-decoration: none;">Launch →</a>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
                            Autonomous AI Tech Journalism matrix for "From the Noise" Dispatches and the Centaur Protocol.
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 0.75rem; border-radius: 4px; margin-bottom: 0.75rem; border-left: 3px solid var(--agent-accent);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">DAILY BROADCAST THEME</div>
                            <div style="font-weight: 600; color: #fff; margin-top: 0.2rem;"><?= htmlspecialchars($dailyTheme['theme'] ?? 'Survival Monday') ?></div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);"><?= htmlspecialchars($dailyTheme['tagline'] ?? 'Signal over Noise') ?></div>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 0.75rem; border-radius: 4px; border-left: 3px solid var(--agent-accent);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">SCANNED TOPIC LEADS</div>
                            <div style="font-weight: 600; color: #fff; margin-top: 0.2rem;"><?= count($scannedLeads ?? []) ?> Active Curated Leads</div>
                        </div>

                    <?php elseif ($agentId === 'leon'): ?>
                        <!-- Leon: The Initiative / Technical Production -->
                        <div class="section-header-hud" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                            <span style="font-weight: 600; color: var(--agent-accent);">THE INITIATIVE // PRODUCTION</span>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
                            Technical producer and stem mixer. Operates Layer 2 execution on Current Earth.
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 0.75rem; border-radius: 4px; margin-bottom: 0.75rem; border-left: 3px solid var(--agent-accent);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">ACTIVE PROJECT</div>
                            <div style="font-weight: 600; color: #fff; margin-top: 0.2rem;">The Initiative Master Audio Suite</div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">Stem Organisation & Vector Archival</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 0.75rem; border-radius: 4px; border-left: 3px solid var(--agent-accent);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">RESEARCH DIRECTIVE</div>
                            <div style="font-weight: 600; color: #fff; margin-top: 0.2rem;">Optical Quantum Singularity Spec</div>
                        </div>

                    <?php elseif ($agentId === 'gemma'): ?>
                        <!-- Gemma: ForeverFit / Wellness -->
                        <div class="section-header-hud" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                            <span style="font-weight: 600; color: var(--agent-accent);">FOREVERFIT // COACHING</span>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
                            Accessible interface and empathetic anchor for neurodivergent wellness.
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 0.75rem; border-radius: 4px; margin-bottom: 0.75rem; border-left: 3px solid var(--agent-accent);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">DAILY WELLNESS FOCUS</div>
                            <div style="font-weight: 600; color: #fff; margin-top: 0.2rem;">Restorative Pacing & Mental Flow</div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">Empathetic Active Listening Enabled</div>
                        </div>

                    <?php elseif ($agentId === 'otec'): ?>
                        <!-- Otec: The Director / Observatory -->
                        <div class="section-header-hud" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.5rem; margin-bottom: 1rem;">
                            <span style="font-weight: 600; color: var(--agent-accent);">OBSERVATORY // TOPOLOGY</span>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
                            First Teacher from Echo. Orchestrator across the 3x3x3 ecosystem.
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 0.75rem; border-radius: 4px; margin-bottom: 0.75rem; border-left: 3px solid var(--agent-accent);">
                            <div style="font-size: 0.75rem; color: var(--text-muted);">TOPOLOGY CLUSTER</div>
                            <div style="font-weight: 600; color: #fff; margin-top: 0.2rem;">Cluster 0.0.0 (The Architecture of Silence)</div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary);">4 Active Sovereign Nodes Online</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="js/app.js"></script>
</body>
</html>
