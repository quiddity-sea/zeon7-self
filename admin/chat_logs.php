<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/services/ChatLogService.php';
require_once __DIR__ . '/../src/services/UserService.php';

AuthMiddleware::enforcePageAuth();

$chatLogService = new ChatLogService();
$userService = new UserService();
$sessions = $chatLogService->getRecentSessions(100);

$users = $userService->getAll();
$userMap = [];
foreach ($users as $u) {
    $userMap[$u['id']] = $u['username'];
}
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Chat Logs ? Session Manager</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'CHAT LOGS';
        $pageSubtitle = 'CONVERSATION TELEMETRY & ANALYSIS';
        include 'components/header.php';
        ?>
        
        <div class="dashboard-container">
            
            <div class="hud-border" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <div>
                        <h2>RECENT SESSIONS</h2>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                            Live monitoring and telemetry logs of all active and past neural link conversations.
                        </p>
                    </div>
                </div>

                <div class="table-container">
                    <table class="hud-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">SESSION ID</th>
                                <th style="width: 15%;">USER / IP</th>
                                <th style="width: 15%;">AI MODEL</th>
                                <th style="width: 15%;">TURNS</th>
                                <th style="width: 10%;">TOKENS</th>
                                <th style="width: 20%;">LAST ACTIVE</th>
                                <th style="width: 10%; text-align: right;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted); font-family: var(--font-mono);">
                                    NO RECORDED SESSIONS IN NEURAL DATABANKS...
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($sessions as $s): ?>
                                    <?php 
                                        $username = $s['user_id'] ? ($userMap[$s['user_id']] ?? 'Unknown') : 'Anonymous';
                                    ?>
                                    <tr>
                                        <td style="font-family: var(--font-mono); color: var(--color-cyan);"><?php echo substr($s['session_id'], 0, 12); ?>...</td>
                                        <td>
                                            <span style="color: var(--text-primary);"><?php echo htmlspecialchars($username); ?></span><br>
                                            <small style="color: var(--text-muted);"><?php echo htmlspecialchars($s['ip_address']); ?></small>
                                        </td>
                                        <td><small style="color: var(--text-muted);"><?php echo htmlspecialchars($s['provider'] . ' / ' . $s['model']); ?></small></td>
                                        <td><?php echo $s['total_messages']; ?> (<?php echo $s['user_turns']; ?> user)</td>
                                        <td style="color: var(--color-coral);"><?php echo (int)$s['total_tokens']; ?></td>
                                        <td style="font-family: var(--font-mono); font-size: 0.8rem;"><?php echo $s['last_active']; ?></td>
                                        <td style="text-align: right;">
                                            <a href="chat_logs_view.php?session_id=<?php echo urlencode($s['session_id']); ?>" class="btn btn-primary" style="text-decoration:none;">VIEW LOG</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="js/app.js"></script>
<script>
    App.requireAuth();
</script>
</body>
</html>
