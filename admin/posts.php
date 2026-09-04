<?php
require_once __DIR__ . '/../src/middleware/AuthMiddleware.php';
AuthMiddleware::enforcePageAuth();
?>
<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts Manager ? Zeon7 Admin</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css?v=14.0">
</head>
<body>
<div class="app-wrapper">
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <?php
        $pageTitle = 'DISPATCH POSTS';
        $pageSubtitle = 'NEWS & GENERATED ARTICLES';
        include 'components/header.php';
        ?>

        <div class="dashboard-container">
            <div class="hud-border" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <div>
                        <h2>DISPATCH ARCHIVE</h2>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                            Review, edit, publish, or purge generated news items and editorial articles.
                        </p>
                    </div>
                    <a href="post-editor.php" class="btn btn-green">+ CREATE NEW DISPATCH</a>
                </div>

                <div class="table-container">
                    <table class="hud-table">
                        <thead>
                            <tr>
                                <th>TITLE</th>
                                <th>STATUS</th>
                                <th>CREATED</th>
                                <th>UPDATED</th>
                                <th style="text-align: right;">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="postsList">
                            <tr>
                                <td colspan="5" style="padding: 2.5rem; text-align: center; color: var(--text-muted); font-family: var(--font-mono);">
                                    QUERYING DISPATCH DATABASE...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/app.js"></script>
<script src="js/posts.js"></script>
<script>
    App.requireAuth();
</script>
</body>
</html>
