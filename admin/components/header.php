<?php
/**
 * Reusable Header Component
 * Expects:
 * - $pageTitle (string)
 * - $pageSubtitle (string)
 */
?>
<div class="header-bar">
    <div>
        <span class="page-title"><?php echo htmlspecialchars($pageTitle ?? 'ZEON7'); ?></span>
        <span class="page-subtitle"><?php echo htmlspecialchars($pageSubtitle ?? 'SYSTEM TERMINAL'); ?></span>
    </div>
    <div style="display: flex; gap: 1.5rem; align-items: center;">
        <?php if (isset($headerActions)) echo $headerActions; ?>
        <?php include __DIR__ . '/token-counter.php'; ?>
        <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme" style="background:none; border:none; cursor:pointer; font-size:1.2rem;">
            <span data-theme-icon>🌙</span>
        </button>
        <?php include __DIR__ . '/operator-badge.php'; ?>
    </div>
</div>
