<?php
require_once __DIR__ . '/../../src/Services/ConfigService.php';

// Instantiate service if not already available
if (!isset($configService)) {
    $configService = new ConfigService();
}

$totalTokens = $configService->getTotalTokens();
?>
<span id="tokenDisplay" class="header-badge-1" data-count="<?php echo $totalTokens; ?>">TOKENS USED: <?php echo $totalTokens; ?></span>
