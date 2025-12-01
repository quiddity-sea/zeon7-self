<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 News</title>
    <link rel="stylesheet" href="css/variables.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/base.css">
    <style>
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

        .article-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-subtle);
        }

        .article-meta {
            color: var(--text-muted);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .article-content {
            max-width: 720px;
            margin: 0 auto;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .article-content h2 {
            margin-top: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .article-content p {
            margin-bottom: 1.5rem;
        }
        
        .article-content ul, .article-content ol {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }
        
        .article-content li {
            margin-bottom: 0.5rem;
        }
        
        .article-content blockquote {
            border-left: 4px solid var(--accent-primary);
            padding-left: 1.5rem;
            margin: 2rem 0;
            font-style: italic;
            color: var(--text-secondary);
        }

        .source-link {
            display: inline-block;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: var(--text-muted);
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
                    <a href="blog.php">News</a>
                    <a href="admin/index.php">Admin</a>
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
