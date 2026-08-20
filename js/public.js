const Public = {
    async fetchLatestPosts(limit = 3) {
        try {
            const res = await fetch(`/api/posts/list.php?status=published&limit=${limit}`);
            const data = await res.json();
            return data.success ? data.posts.slice(0, limit) : [];
        } catch (e) {
            console.error('Failed to fetch posts', e);
            return [];
        }
    },

    async fetchAllPosts() {
        try {
            const res = await fetch('/api/posts/list.php?status=published');
            const data = await res.json();
            return data.success ? data.posts : [];
        } catch (e) {
            console.error('Failed to fetch posts', e);
            return [];
        }
    },

    async fetchPostBySlug(slug) {
        try {
            const res = await fetch(`/api/posts/get.php?slug=${slug}`);
            const data = await res.json();
            return data.success ? data.post : null;
        } catch (e) {
            console.error('Failed to fetch post', e);
            return null;
        }
    },

    renderPostCard(post) {
        const date = new Date(post.published_at || post.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        // Extract a snippet (first 140 chars)
        const snippet = (post.content || '').replace(/[#*`]/g, '').substring(0, 140) + '...';

        return `
            <a href="post.php?slug=${encodeURIComponent(post.slug)}" class="hud-border post-card" data-tilt>
                <div class="hud-corner-tr"></div>
                <div class="hud-corner-bl"></div>
                <div>
                    <div class="post-card-date">// ${date}</div>
                    <div class="post-card-title">${this.escapeHtml(post.title)}</div>
                    <div class="post-card-snippet">${this.escapeHtml(snippet)}</div>
                </div>
                <div class="post-card-action">READ FULL INTEL ?</div>
            </a>
        `;
    },

    escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
};
