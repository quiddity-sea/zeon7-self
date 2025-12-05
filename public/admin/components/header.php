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
        <?php include __DIR__ . '/operator-badge.php'; ?>
    </div>
</div>
