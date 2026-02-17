<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts Manager - Zeon7 Admin</title>
    <link rel="stylesheet" href="/css/zeon7-theme.css">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="css/style.css?v=3">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;900&family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'components/sidebar.php'; ?>

    <div class="main-stage">
        <main class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1>Posts</h1>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem;">Manage your blog posts and generated content.</p>
                </div>
                <a href="post-editor.php" class="btn btn-primary">
                    + New Post
                </a>
            </div>

            <div class="card">
                <div class="table-container">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid var(--border);">
                                <th style="padding: 1rem;">Title</th>
                                <th style="padding: 1rem;">Status</th>
                                <th style="padding: 1rem;">Created</th>
                                <th style="padding: 1rem;">Updated</th>
                                <th style="padding: 1rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="postsList">
                            <tr>
                                <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-secondary);">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/theme-switcher.js"></script>
    <script src="js/app.js"></script>
    <script src="js/posts.js"></script>
    <script>
        App.requireAuth();
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof Posts !== 'undefined') Posts.initList();
        });
    </script>
</body>
</html>
