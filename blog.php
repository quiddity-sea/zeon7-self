<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Updates - Zeon7</title>
    <link rel="stylesheet" href="../css/zeon7-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/pages/public.css">
</head>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">⚡ Zeon7</a>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="blog.php" class="active">News</a>
                    <a href="admin/index.php">Admin</a>
                    <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme">
                        <span data-theme-icon>🌙</span>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <section class="section">
        <div class="container">
            <h1 class="mb-8">News & Updates</h1>
            
            <div id="posts-grid" class="grid grid-cols-3 gap-6">
                <!-- Posts will be loaded here -->
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-muted);">
                    Loading...
                </div>
            </div>
        </div>
    </section>

    <footer class="section" style="background: var(--bg-secondary); margin-top: auto;">
        <div class="container text-center">
            <p class="text-secondary">© 2024 Zeon7 AI. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/theme-switcher.js"></script>
    <script src="js/public.js"></script>
    <script src="js/chat-widget.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const posts = await Public.fetchAllPosts();
            const container = document.getElementById('posts-grid');
            
            if (posts.length > 0) {
                container.innerHTML = posts.map(post => Public.renderPostCard(post)).join('');
            } else {
                container.innerHTML = `
                    <div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: var(--bg-card); border-radius: var(--radius-lg);">
                        <p>No posts found.</p>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
