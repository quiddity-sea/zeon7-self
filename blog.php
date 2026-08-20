<!DOCTYPE html>
<html lang="en" class="hud-scanline">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatches Archive — Zeon7</title>
    <link rel="stylesheet" href="css/zeon7-theme.css?v=14.0">
    <style>
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
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
            <a href="blog.php" class="nav-link active">Dispatches</a>
            <a href="admin/index.php" class="hud-badge green" style="text-decoration:none;">⚡ Mission Control</a>
            <button class="theme-toggle" data-theme-toggle aria-label="Toggle theme" style="background:none; border:none; cursor:pointer; font-size:1.1rem; padding:0.25rem;">
                <span data-theme-icon>🌙</span>
            </button>
        </div>
    </nav>

    <header style="max-width: 1400px; margin: 0 auto; padding: 3rem 2rem 1rem;">
        <div class="hud-badge" style="margin-bottom: 1rem;">
            // REPOSITORY ARCHIVE
        </div>
        <h1 style="font-size: 2.25rem;">INTELLIGENCE DISPATCHES</h1>
        <p style="color: var(--text-secondary); margin-top: 0.5rem; max-width: 650px;">
            Complete chronological chronicle of AI synthesised tech reports, market intelligence, and architectural briefs.
        </p>
    </header>

    <main class="posts-grid" id="posts-grid">
        <div class="hud-border" style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-muted); font-family: var(--font-mono);">
            <div class="hud-corner-tr"></div>
            <div class="hud-corner-bl"></div>
            FETCHING ARCHIVE DISPATCH STREAM...
        </div>
    </main>

    <footer class="footer-hud">
        <div style="margin-bottom: 0.5rem; font-weight:700;">
            ⚡ ZEON7 CYBERNETIC INTELLIGENCE // OPERATED ON INVIGOR CLUSTER
        </div>
        <div>
            © 2026 ZEON7. ALL RIGHTS RESERVED.
        </div>
    </footer>

    <!-- GSAP CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="js/theme-switcher.js"></script>
    <script src="js/animations.js?v=11.0"></script>
    <script src="js/public.js?v=11.0"></script>
    <script src="js/chat-widget.js?v=17.0"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const container = document.getElementById('posts-grid');
            try {
                const posts = await Public.fetchAllPosts();
                if (posts && posts.length > 0) {
                    container.innerHTML = posts.map(post => Public.renderPostCard(post)).join('');
                    ZeonAnimations.init3DTilt();
                    ZeonAnimations.staggerIn('#posts-grid .post-card', 0.08);
                } else {
                    container.innerHTML = `
                        <div class="hud-border" style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-muted); font-family: var(--font-mono);">
                            <div class="hud-corner-tr"></div>
                            <div class="hud-corner-bl"></div>
                            NO DISPATCHES FOUND IN THE SYSTEM.
                        </div>
                    `;
                }
            } catch(e) {
                container.innerHTML = `
                    <div class="hud-border" style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-muted); font-family: var(--font-mono);">
                        <div class="hud-corner-tr"></div>
                        <div class="hud-corner-bl"></div>
                        DISPATCH STREAM TEMPORARILY STANDBY.
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
