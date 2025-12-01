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
            month: 'long',
            day: 'numeric'
        });

        // Extract a snippet (first 150 chars)
        const snippet = post.content.replace(/[#*`]/g, '').substring(0, 150) + '...';

        return `
            <article class="post-card">
                <div class="post-meta">${date}</div>
                <h3 class="post-title">
                    <a href="post.php?slug=${post.slug}">${this.escapeHtml(post.title)}</a>
                </h3>
                <p class="post-snippet">${this.escapeHtml(snippet)}</p>
                <a href="post.php?slug=${post.slug}" class="read-more">Read Article →</a>
            </article>
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
