<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$operatorName = $_SESSION['user_name'] ?? 'MERRILL LEO';
?>
<div class="header-badge-1">
    OPERATOR: <?php echo htmlspecialchars($operatorName); ?>
</div>
