<?php
require_once __DIR__ . '/../../src/Services/ConfigService.php';

if (!isset($configService)) {
    $configService = new ConfigService();
}

$totalTokens = $configService->getTotalTokens();
?>
<span id="tokenDisplay" class="hud-badge green" data-count="<?php echo (int)$totalTokens; ?>" title="Accumulated Token Count">
    ?? TOKENS: <?php echo number_format((int)$totalTokens); ?>
</span>
