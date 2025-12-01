<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Updates - Zeon7</title>
    <link rel="stylesheet" href="css/variables.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/base.css">
    <style>
        .post-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .post-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .post-meta {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .post-title {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .post-title a {
            color: var(--text-primary);
        }

        .post-snippet {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            flex: 1;
        }

        .read-more {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--accent-primary);
            text-decoration: none;
        }
        
        .read-more:hover {
            text-decoration: underline;
        }

        header {
            border-bottom: 1px solid var(--border-subtle);
            padding: 1rem 0;
            background: var(--bg-primary);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--text-primary);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-secondary);
            font-weight: 500;
            text-decoration: none;
        }

        .nav-links a:hover {
            color: var(--accent-primary);
        }
        
        .nav-links a.active {
            color: var(--text-primary);
            font-weight: 700;
        }
    </style>
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
