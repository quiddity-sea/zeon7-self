const Lore = {
    async init() {
        console.log('Lore Manager Initialising...');
        this.listContainer = document.getElementById('loreList');
        this.modal = document.getElementById('loreModal');
        this.form = document.getElementById('loreForm');

        // Form Inputs
        this.idInput = document.getElementById('loreId');
        this.typeInput = document.getElementById('loreType');
        this.contentInput = document.getElementById('loreContent');
        this.tagsInput = document.getElementById('loreTags');
        this.publicInput = document.getElementById('lorePublic');
        this.modalTitle = document.getElementById('modalTitle');

        document.getElementById('addBtn').addEventListener('click', () => this.openModal());
        document.getElementById('cancelBtn').addEventListener('click', () => this.closeModal());
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Close modal on outside click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeModal();
        });

        await this.loadLore();
    },

    async loadLore() {
        try {
            const res = await fetch('/api/lore/all.php');
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            if (data.lore.length === 0) {
                this.listContainer.innerHTML = `
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                            No lore entries found.
                        </td>
                    </tr>
                `;
                return;
            }

            this.loreItems = data.lore; // Store for access

            this.listContainer.innerHTML = data.lore.map((item, index) => `
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="padding: 1rem;">
                        <span style="color:var(--cyan); font-size:0.8rem; text-transform:uppercase;">${this.escapeHtml(item.type)}</span>
                    </td>
                    <td style="padding: 1rem;">
                        <div style="max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color:var(--text-main);">
                            ${this.escapeHtml(item.content)}
                        </div>
                    </td>
                    <td style="padding: 1rem;">
                        ${this.renderTags(item.tags)}
                    </td>
                    <td style="padding: 1rem;">
                        ${item.is_public == 1 ? '<span style="color:var(--cyan); border:1px solid var(--cyan); padding:2px 6px; font-size:0.7rem; border-radius:4px;">PUBLIC</span>' : '<span style="color:var(--text-muted); font-size:0.7rem;">PRIVATE</span>'}
                    </td>
                    <td style="padding: 1rem; color: var(--text-secondary); font-size:0.8rem;">${new Date(item.updated_at || item.created_at).toLocaleDateString()}</td>
                    <td style="padding: 1rem; text-align: right;">
                        <div class="actions">
                            <button onclick="Lore.editLoreFromIndex(${index})" class="btn-mini">Edit</button>
                            <button onclick="Lore.deleteLore(${item.id})" class="btn-mini" style="color:var(--orange); border-color:var(--orange);">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');

        } catch (e) {
            console.error('Failed to load lore', e);
            this.listContainer.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: var(--orange);">
                        Failed to load lore.
                    </td>
                </tr>
            `;
        }
    },

    renderTags(tagsJson) {
        if (!tagsJson) return '';
        try {
            const tags = typeof tagsJson === 'string' ? JSON.parse(tagsJson) : tagsJson;
            if (!Array.isArray(tags)) return '';
            return tags.map(tag => `<span style="background:rgba(255,255,255,0.1); padding:2px 6px; border-radius:4px; font-size:0.75rem; margin-right:4px;">${this.escapeHtml(tag)}</span>`).join('');
        } catch (e) {
            return '';
        }
    },

    openModal(item = null) {
        if (item) {
            this.modalTitle.textContent = 'EDIT LORE';
            this.idInput.value = item.id;
            this.typeInput.value = item.type;
            this.contentInput.value = item.content;

            // Parse tags
            let tags = [];
            try {
                tags = typeof item.tags === 'string' ? JSON.parse(item.tags) : item.tags;
            } catch (e) { }
            this.tagsInput.value = Array.isArray(tags) ? tags.join(', ') : '';

            this.publicInput.checked = item.is_public == 1;
        } else {
            this.modalTitle.textContent = 'ADD LORE';
            this.idInput.value = '';
            this.typeInput.value = 'memory';
            this.contentInput.value = '';
            this.tagsInput.value = '';
            this.publicInput.checked = false;
        }

        this.modal.style.display = 'flex';
    },

    closeModal() {
        this.modal.style.display = 'none';
        this.form.reset();
    },

    editLoreFromIndex(index) {
        const item = this.loreItems[index];
        if (item) {
            this.openModal(item);
        }
    },

    async handleSubmit(e) {
        e.preventDefault();

        const id = this.idInput.value;
        const type = this.typeInput.value;
        const content = this.contentInput.value.trim();
        const tagsStr = this.tagsInput.value.trim();
        const isPublic = this.publicInput.checked;

        if (!content) {
            alert('Content is required');
            return;
        }

        const tags = tagsStr ? tagsStr.split(',').map(t => t.trim()).filter(t => t) : [];

        try {
            const payload = {
                type,
                content,
                tags,
                is_public: isPublic
            };

            if (id) payload.id = id;

            const res = await fetch('/api/lore/upsert.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }, // Assuming App.getHeaders() might not be available or just standard JSON
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                this.closeModal();
                this.loadLore();
            } else {
                alert('Error: ' + data.error);
            }
        } catch (e) {
            console.error('Save failed', e);
            alert('Failed to save lore entry.');
        }
    },

    async deleteLore(id) {
        if (!confirm(`Are you sure you want to delete this entry?`)) return;

        try {
            const res = await fetch(`/api/lore/delete.php?id=${id}`, {
                method: 'DELETE'
            });
            const data = await res.json();

            if (data.success) {
                this.loadLore();
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

// Initialise when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Lore.init();
});
