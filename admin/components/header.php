<?php
/**
 * Reusable HUD Header Component with Agent Selector Dropdown
 * Expects:
 * - $pageTitle (string)
 * - $pageSubtitle (string)
 */
require_once __DIR__ . '/../../src/config/env.php';
require_once __DIR__ . '/../../src/services/AgentContextService.php';

$agentCtx = $agentCtx ?? new AgentContextService();
$activeAgentId = $agentCtx->getAgentId();
$availableAgents = $agentCtx->listAvailableAgents();
$activeAgent = $availableAgents[$activeAgentId] ?? [
    'name' => $agentCtx->getDisplayName(),
    'accent' => $agentCtx->getThemeAccent()
];
?>
<div class="header-bar">
    <div class="header-title-group">
        <span class="page-title">
            <span class="status-dot" style="background: <?= htmlspecialchars($activeAgent['accent']) ?>; box-shadow: 0 0 10px <?= htmlspecialchars($activeAgent['accent']) ?>;"></span>
            <?= htmlspecialchars($pageTitle ?? $agentCtx->getDisplayName()); ?>
        </span>
        <span class="page-subtitle">// <?= htmlspecialchars($pageSubtitle ?? 'SYSTEM OVERVIEW'); ?></span>
    </div>
    
    <div style="display: flex; gap: 1rem; align-items: center;">
        <!-- Agent Switcher Dropdown: Redirects directly to that agent's Mission Control index page -->
        <div class="agent-selector-container" style="position: relative;">
            <label for="adminAgentSelect" style="display:none;">Select Agent</label>
            <select id="adminAgentSelect" onchange="window.location.href = 'index.php?agent=' + encodeURIComponent(this.value);" 
                    style="background: rgba(18, 22, 28, 0.9); color: var(--text-primary, #fff); border: 1px solid <?= htmlspecialchars($activeAgent['accent']) ?>; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem; font-family: inherit; font-weight: 600; cursor: pointer; outline: none; box-shadow: 0 0 8px rgba(0,0,0,0.4);">
                <?php foreach ($availableAgents as $slug => $info): ?>
                    <option value="<?= htmlspecialchars($slug) ?>" <?= $slug === $activeAgentId ? 'selected' : '' ?> style="background: #12161c; color: #fff;">
                        <?= htmlspecialchars($info['name']) ?> [<?= strtoupper($slug) ?>]
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (isset($headerActions)) echo $headerActions; ?>
        <?php include __DIR__ . '/token-counter.php'; ?>
        <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme" style="background:none; border:none; cursor:pointer; font-size:1.1rem; color:var(--text-secondary);">
            <span data-theme-icon>🌙</span>
        </button>
        <?php include __DIR__ . '/operator-badge.php'; ?>
    </div>
</div>
