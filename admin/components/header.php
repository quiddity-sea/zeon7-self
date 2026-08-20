<?php
/**
 * Reusable HUD Header Component
 * Expects:
 * - $pageTitle (string)
 * - $pageSubtitle (string)
 */
?>
<div class="header-bar">
    <div class="header-title-group">
        <span class="page-title">
            <span class="status-dot"></span>
            <?php echo htmlspecialchars($pageTitle ?? 'ZEON7'); ?>
        </span>
        <span class="page-subtitle">// <?php echo htmlspecialchars($pageSubtitle ?? 'SYSTEM OVERVIEW'); ?></span>
    </div>
    <div style="display: flex; gap: 1.25rem; align-items: center;">
        <?php if (isset($headerActions)) echo $headerActions; ?>
        <?php include __DIR__ . '/token-counter.php'; ?>
        <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme" style="background:none; border:none; cursor:pointer; font-size:1.1rem; color:var(--text-secondary);">
            <span data-theme-icon>??</span>
        </button>
        <?php include __DIR__ . '/operator-badge.php'; ?>
    </div>
</div>
