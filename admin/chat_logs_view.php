<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/services/ChatLogService.php';

AuthMiddleware::enforcePageAuth();

$sessionId = $_GET['session_id'] ?? '';
if (empty($sessionId)) {
    header('Location: chat_logs.php');
    exit;
}

$chatLogService = new ChatLogService();
$logs = $chatLogService->getSession($sessionId);
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Chat Logs ? Viewer</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
    <style>
        .chat-bubble {
            max-width: 85%;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            font-family: var(--font-mono);
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--text-primary);
        }
        .chat-bubble.user {
            background: rgba(34, 211, 238, 0.05);
            border: 1px solid rgba(34, 211, 238, 0.2);
            border-left: 3px solid var(--color-cyan);
            margin-left: auto;
        }
        .chat-bubble.assistant {
            background: rgba(244, 63, 94, 0.05);
            border: 1px solid rgba(244, 63, 94, 0.2);
            border-right: 3px solid var(--color-coral);
            margin-right: auto;
        }
        .bubble-header {
            font-weight: bold;
            margin-bottom: 1rem;
            font-size: 0.8rem;
            letter-spacing: 1px;
            display: flex;
            justify-content: space-between;
        }
        .user .bubble-header { color: var(--color-cyan); }
        .assistant .bubble-header { color: var(--color-coral); }
        
        .bubble-footer {
            margin-top: 1rem;
            font-size: 0.75rem;
            color: var(--text-muted);
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 0.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
    </style>
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'SESSION VIEWER';
        $pageSubtitle = htmlspecialchars($sessionId);
        include 'components/header.php';
        ?>
        
        <div class="dashboard-container">
            
            <div style="margin-bottom: 1.5rem;">
                <a href="chat_logs.php" class="btn btn-secondary" style="text-decoration:none; display:inline-block;">&larr; BACK TO SESSIONS</a>
            </div>

            <div class="hud-border" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div>
                        <h2>NEURAL LINK TRANSCRIPT</h2>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                            Session ID: <span style="color:var(--color-cyan);"><?php echo htmlspecialchars($sessionId); ?></span>
                        </p>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column;">
                    <?php if (empty($logs)): ?>
                        <div style="text-align: center; padding: 2.5rem; color: var(--text-muted); font-family: var(--font-mono);">
                            NO LOGS FOUND FOR THIS SESSION...
                        </div>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <div class="chat-bubble <?php echo $log['role']; ?>">
                                <div class="bubble-header">
                                    <span>// <?php echo strtoupper($log['role']); ?> NODE</span>
                                    <span><?php echo substr($log['created_at'], 11, 8); ?></span>
                                </div>
                                <div style="white-space:pre-wrap;"><?php echo htmlspecialchars($log['content']); ?></div>
                                
                                <div class="bubble-footer">
                                    <?php if ($log['tokens_used']): ?>
                                        <span>TOKENS: <?php echo $log['tokens_used']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
