<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$operatorName = $_SESSION['user_name'] ?? 'MERRILL LEO';
?>
<div class="hud-badge" style="border-color: rgba(34, 211, 238, 0.4);">
    <span class="status-dot"></span>
    OP: <?php echo htmlspecialchars($operatorName); ?>
</div>
