const Posts = {
    // --- List View Logic ---
    async initList() {
        this.listContainer = document.getElementById('postsList');
        await this.loadPosts();
    },

    async loadPosts() {
        try {
            const res = await fetch('/api/posts/list.php');
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            if (data.posts.length === 0) {
                this.listContainer.innerHTML = `
                    <tr>
                        <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                            No posts found.
                        </td>
                    </tr>
                `;
                return;
            }

            this.listContainer.innerHTML = data.posts.map(post => `
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 1rem;">
                        <a href="post-editor.php?id=${post.id}" style="color: var(--text); font-weight: 500; text-decoration: none;">
                            ${this.escapeHtml(post.title)}
                        </a>
                    </td>
                    <td style="padding: 1rem;">
                        <span style="
                            padding: 0.25rem 0.5rem; 
                            border-radius: 0.25rem; 
                            font-size: 0.75rem; 
                            background: ${post.status === 'published' ? 'rgba(var(--success-rgb), 0.1)' : 'rgba(var(--warning-rgb), 0.1)'};
                            color: ${post.status === 'published' ? 'var(--success)' : 'var(--warning)'};
                        ">
                            ${post.status.toUpperCase()}
                        </span>
                    </td>
                    <td style="padding: 1rem; color: var(--text-secondary);">${new Date(post.created_at).toLocaleDateString()}</td>
                    <td style="padding: 1rem; color: var(--text-secondary);">${new Date(post.updated_at).toLocaleDateString()}</td>
                    <td style="padding: 1rem; text-align: right;">
                        <a href="post-editor.php?id=${post.id}" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">Edit</a>
                    </td>
                </tr>
            `).join('');

        } catch (e) {
            console.error('Failed to load posts', e);
            this.listContainer.innerHTML = `
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center; color: var(--danger);">
                        Failed to load posts.
                    </td>
                </tr>
            `;
        }
    },

    // --- Editor Logic ---
    async initEditor() {
        this.titleInput = document.getElementById('title');
        this.slugInput = document.getElementById('slug');
        this.contentInput = document.getElementById('content');
        this.previewPane = document.getElementById('preview');
        this.pageTitle = document.getElementById('pageTitle');
        this.saveBtn = document.getElementById('saveBtn');
        this.publishBtn = document.getElementById('publishBtn');
        this.deleteBtn = document.getElementById('deleteBtn');

        // Parse ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        this.postId = urlParams.get('id');

        // Event Listeners
        this.contentInput.addEventListener('input', () => this.updatePreview());
        this.titleInput.addEventListener('input', () => this.generateSlug());
        this.saveBtn.addEventListener('click', (e) => this.savePost(e, 'draft'));
        this.publishBtn.addEventListener('click', (e) => this.savePost(e, 'published'));

        if (this.postId) {
            this.deleteBtn.style.display = 'inline-block';
            this.deleteBtn.addEventListener('click', (e) => this.deletePost(e));
            await this.loadPost(this.postId);
        } else {
            this.pageTitle.textContent = 'New Post';
        }
    },

    updatePreview() {
        const markdown = this.contentInput.value;
        this.previewPane.innerHTML = marked.parse(markdown);
    },

    generateSlug() {
        if (this.postId) return; // Don't auto-update slug for existing posts to avoid breaking links
        const title = this.titleInput.value;
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)+/g, '');
        this.slugInput.value = slug;
    },

    async loadPost(id) {
        try {
            const res = await fetch(`/api/posts/get.php?id=${id}`);
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            const post = data.post;
            this.titleInput.value = post.title;
            this.slugInput.value = post.slug;
            this.contentInput.value = post.content;
            this.pageTitle.textContent = 'Edit Post';

            if (post.status === 'published') {
                this.publishBtn.textContent = 'Update & Publish';
            }

            this.updatePreview();

        } catch (e) {
            console.error('Failed to load post', e);
            alert('Failed to load post details.');
        }
    },

    async savePost(e, status) {
        e.preventDefault();

        const postData = {
            title: this.titleInput.value,
            slug: this.slugInput.value,
            content: this.contentInput.value,
            status: status
        };

        if (!postData.title) {
            alert('Title is required');
            return;
        }

        const btn = status === 'published' ? this.publishBtn : this.saveBtn;
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
            let url = '/api/posts/create.php';
            let method = 'POST';

            if (this.postId) {
                url = '/api/posts/update.php';
                method = 'PUT';
                postData.id = this.postId;
            }

            const res = await fetch(url, {
                method: method,
                headers: App.getHeaders(),
                body: JSON.stringify(postData)
            });

            const data = await res.json();

            if (data.success) {
                if (!this.postId) {
                    // Redirect to edit mode for the new post
                    window.location.href = `post-editor.php?id=${data.id}`;
                } else {
                    alert('Post saved successfully!');
                    if (status === 'published') {
                        this.publishBtn.textContent = 'Update & Publish';
                    }
                }
            } else {
                alert('Error: ' + data.error);
            }

        } catch (e) {
            console.error('Save failed', e);
            alert('Failed to save post.');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    },

    async deletePost(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this post? This cannot be undone.')) return;

        try {
            const res = await fetch(`/api/posts/delete.php?id=${this.postId}`, {
                method: 'DELETE',
                headers: App.getHeaders()
            });
            const data = await res.json();

            if (data.success) {
                window.location.href = 'posts.php';
            } else {
                alert('Failed to delete: ' + data.error);
            }
        } catch (e) {
            console.error('Delete failed', e);
            alert('An error occurred while deleting.');
        }
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
