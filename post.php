<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeon7 Intel Dispatch</title>
    <link rel="stylesheet" href="css/zeon7-theme.css?v=14.0">
    <style>
        .article-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 2.5rem;
        }
        .article-header {
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(34, 211, 238, 0.2);
            padding-bottom: 1.5rem;
        }
        [data-theme="light"] .article-header {
            border-bottom: 1px solid #e2e8f0;
        }
        .article-meta {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--color-cyan);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
        }
        .article-content {
            font-size: 1.05rem;
            line-height: 1.8;
            color: var(--text-primary);
        }
        .article-content h1, .article-content h2, .article-content h3 {
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .article-content p {
            margin-bottom: 1.5rem;
        }
        .article-content blockquote {
            border-left: 3px solid var(--color-cyan);
            padding-left: 1.5rem;
            margin: 1.5rem 0;
            color: var(--text-secondary);
            font-style: italic;
        }
        .article-content pre {
            background: #020406;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 1.25rem;
            border-radius: var(--radius-sm);
            overflow-x: auto;
            margin: 1.5rem 0;
            font-family: var(--font-mono);
            font-size: 0.85rem;
        }
        [data-theme="light"] .article-content pre {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #0f172a;
        }
        .source-link {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.75rem 1.25rem;
            background: rgba(34, 211, 238, 0.08);
            border: 1px solid rgba(34, 211, 238, 0.3);
            color: var(--color-cyan);
            text-decoration: none;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            border-radius: var(--radius-sm);
            font-weight: 700;
        }
        [data-theme="light"] .source-link {
            background: #f0fdf4;
            border-color: #86efac;
            color: #166534;
        }
        .source-link:hover {
            background: var(--color-cyan);
            color: #000;
        }
        .footer-hud {
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 2.5rem 2rem;
            text-align: center;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--text-muted);
            background: rgba(3, 6, 9, 0.8);
            margin-top: 4rem;
        }
        [data-theme="light"] .footer-hud {
            background: #ffffff !important;
            border-top: 1px solid #e2e8f0 !important;
            color: #64748b !important;
        }
    </style>
</head>
<body>
    <nav class="public-nav">
        <a href="index.php" class="nav-logo">
            <span class="status-dot"></span>
            ⚡ ZEON7
        </a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Intelligence Home</a>
            <a href="blog.php" class="nav-link">Dispatches</a>
            <a href="admin/index.php" class="hud-badge green" style="text-decoration:none;">⚡ Mission Control</a>
            <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme" style="background:none; border:none; cursor:pointer; font-size:1.1rem; padding:0.25rem;">
                <span data-theme-icon>🌙</span>
            </button>
        </div>
    </nav>

    <main class="container">
        <article id="post-container" class="hud-border article-container" data-tilt>
            <div class="hud-corner-tr"></div>
            <div class="hud-corner-bl"></div>
            <div style="text-align: center; padding: 4rem; color: var(--text-muted); font-family: var(--font-mono);">
                DECRYPTING INTEL DISPATCH STREAM...
            </div>
        </article>
    </main>

    <footer class="footer-hud">
        <div style="margin-bottom: 0.5rem; font-weight:700;">
            ⚡ ZEON7 CYBERNETIC INTELLIGENCE // OPERATED ON INVIGOR CLUSTER
        </div>
        <div>
            © 2026 ZEON7. ALL RIGHTS RESERVED.
        </div>
    </footer>

    <!-- Marked & GSAP CDN -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="js/theme-switcher.js"></script>
    <script src="js/animations.js?v=11.0"></script>
    <script src="js/public.js?v=11.0"></script>
    <script src="js/chat-widget.js?v=17.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const slug = urlParams.get('slug');
            const container = document.getElementById('post-container');

            if (!slug) {
                container.innerHTML = '<div style="text-align: center; font-family: var(--font-mono);">DISPATCH SLUG NOT SPECIFIED.</div>';
                return;
            }

            try {
                const post = await Public.fetchPostBySlug(slug);
                if (post) {
                    document.title = `${post.title} — Zeon7 Intel`;
                    const date = new Date(post.published_at || post.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    container.innerHTML = `
                        <div class="hud-corner-tr"></div>
                        <div class="hud-corner-bl"></div>
                        <header class="article-header">
                            <div class="article-meta">// DISPATCH ISSUED: ${date}</div>
                            <h1 style="font-size: 2.25rem;">${Public.escapeHtml(post.title)}</h1>
                        </header>
                        <div class="article-content">
                            ${marked.parse(post.content)}
                            
                            ${post.source_url ? `
                                <div>
                                    <a href="${Public.escapeHtml(post.source_url)}" target="_blank" class="source-link">
                                        EXTERNAL SOURCE INTEL: ${Public.escapeHtml(post.source_url)} ↗
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    `;
                    ZeonAnimations.init3DTilt();
                    if (typeof gsap !== 'undefined') {
                        gsap.from('#post-container .article-content', { opacity: 0, y: 20, duration: 0.6, ease: "power2.out" });
                    }
                } else {
                    container.innerHTML = '<div style="text-align: center; font-family: var(--font-mono);">DISPATCH NOT FOUND IN DATABASE.</div>';
                }
            } catch (err) {
                container.innerHTML = '<div style="text-align: center; font-family: var(--font-mono);">STREAM TRANSMISSION ERROR.</div>';
            }
        });
    </script>
</body>
</html>
