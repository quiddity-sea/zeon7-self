<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 News</title>
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
                    <a href="blog.php">News</a>
                    <a href="admin/index.php">Admin</a>
                    <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme">
                        <span data-theme-icon>🌙</span>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <main class="section">
        <div class="container">
            <article id="post-container">
                <div style="text-align: center; padding: 4rem; color: var(--text-muted);">
                    Loading article...
                </div>
            </article>
        </div>
    </main>

    <footer class="section" style="background: var(--bg-secondary); margin-top: auto;">
        <div class="container text-center">
            <p class="text-secondary">© 2024 Zeon7 AI. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="js/theme-switcher.js"></script>
    <script src="js/public.js"></script>
    <script src="js/chat-widget.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const slug = urlParams.get('slug');
            const container = document.getElementById('post-container');

            if (!slug) {
                container.innerHTML = '<div style="text-align: center;">Post not found.</div>';
                return;
            }

            const post = await Public.fetchPostBySlug(slug);
            
            if (post) {
                document.title = `${post.title} - Zeon7`;
                const date = new Date(post.published_at || post.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                container.innerHTML = `
                    <header class="article-header">
                        <div class="article-meta">${date}</div>
                        <h1>${Public.escapeHtml(post.title)}</h1>
                    </header>
                    <div class="article-content">
                        ${marked.parse(post.content)}
                        
                        ${post.source_url ? `
                            <a href="${Public.escapeHtml(post.source_url)}" target="_blank" class="source-link">
                                Source: ${Public.escapeHtml(post.source_url)}
                            </a>
                        ` : ''}
                    </div>
                `;
            } else {
                container.innerHTML = '<div style="text-align: center;">Post not found.</div>';
            }
        });
    </script>
</body>
</html>
