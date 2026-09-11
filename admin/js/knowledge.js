const Knowledge = {
    async init() {
        this.loadFiles();
        this.setupUpload();
    },

    statusBadge(status, error) {
        const s = (status || 'indexed').toLowerCase();
        if (s === 'indexed') {
            return `<span class="hud-badge green" style="font-size:0.7rem;">INDEXED</span>`;
        } else if (s === 'pending') {
            return `<span class="hud-badge amber" style="font-size:0.7rem;">PENDING</span>`;
        } else if (s === 'processing') {
            return `<span class="hud-badge blue" style="font-size:0.7rem;">PROCESSING</span>`;
        } else if (s === 'failed') {
            const tip = error ? this.escapeHtml(error) : 'Ingestion failed';
            return `<span class="hud-badge red" style="font-size:0.7rem; cursor:help;" title="${tip}">FAILED</span>`;
        }
        return `<span class="hud-badge" style="font-size:0.7rem;">${this.escapeHtml(s.toUpperCase())}</span>`;
    },

    async loadFiles() {
        const tbody = document.getElementById('filesList');
        try {
            const res = await fetch('/api/knowledge/list.php');
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            if (!data.files || data.files.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: var(--text-secondary); font-family: var(--font-mono);">
                            No documents found in Quiddity Lore Sea.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = data.files.map(file => `
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.75rem 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>📄</span>
                            <span style="font-weight: 500; font-family: var(--font-mono);">${this.escapeHtml(file.filename)}</span>
                        </div>
                    </td>
                    <td style="padding: 0.75rem 1rem; color: var(--color-cyan); font-family: var(--font-mono); font-size: 0.8rem;">
                        ${this.escapeHtml(file.sea_path || file.filename)}
                    </td>
                    <td style="padding: 0.75rem 1rem; color: var(--text-secondary); font-family: var(--font-mono); font-size: 0.8rem;">
                        ${this.formatSize(file.file_size || file.size || 0)}
                    </td>
                    <td style="padding: 0.75rem 1rem;">
                        ${this.statusBadge(file.status, file.error)}
                    </td>
                    <td style="padding: 0.75rem 1rem; color: var(--text-secondary); font-family: var(--font-mono); font-size: 0.8rem;">
                        ${file.created_at ? new Date(file.created_at).toLocaleString() : '—'}
                    </td>
                    <td style="padding: 0.75rem 1rem; text-align: right; white-space: nowrap;">
                        <button onclick="Knowledge.reingestFile(${file.id})" 
                                class="hud-btn-action reingest">
                            RE-INGEST
                        </button>
                        <button onclick="Knowledge.deleteFile(${file.id})" 
                                class="hud-btn-action delete">
                            DELETE
                        </button>
                    </td>
                </tr>
            `).join('');

        } catch (e) {
            console.error('Failed to load files', e);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 2rem; text-align: center; color: var(--danger); font-family: var(--font-mono);">
                        Failed to load files from Council Commons: ${this.escapeHtml(e.message)}
                    </td>
                </tr>
            `;
        }
    },

    async reingestFile(id) {
        try {
            const res = await fetch('/api/knowledge/reingest.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...(App.csrfToken ? { 'X-CSRF-TOKEN': App.csrfToken } : {})
                },
                body: JSON.stringify({ file_id: id })
            });
            const data = await res.json();

            if (data.success) {
                this.loadFiles();
            } else {
                alert('Re-ingestion failed: ' + (data.error || 'Unknown error'));
            }
        } catch (e) {
            console.error('Re-ingestion failed', e);
            alert('An error occurred while triggering re-ingestion.');
        }
    },

    async deleteFile(id) {
        if (!confirm('Are you sure you want to delete this file from the Lore Sea and vector database? This cannot be undone.')) return;

        try {
            const res = await fetch(`/api/knowledge/delete.php?id=${id}`, {
                method: 'DELETE',
                headers: App.getHeaders()
            });
            const data = await res.json();

            if (data.success) {
                this.loadFiles();
            } else {
                alert('Failed to delete file: ' + data.error);
            }
        } catch (e) {
            console.error('Delete failed', e);
            alert('An error occurred while deleting the file.');
        }
    },

    setupUpload() {
        const zone = document.getElementById('uploadZone');
        const input = document.getElementById('fileInput');

        // Click to browse
        zone.addEventListener('click', () => input.click());

        // Drag & Drop
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.style.borderColor = 'var(--primary)';
            zone.style.background = 'rgba(255, 255, 255, 0.05)';
        });

        zone.addEventListener('dragleave', () => {
            zone.style.borderColor = 'var(--border)';
            zone.style.background = 'transparent';
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.style.borderColor = 'var(--border)';
            zone.style.background = 'transparent';

            if (e.dataTransfer.files.length) {
                this.handleUpload(e.dataTransfer.files[0]);
            }
        });

        // File Input Change
        input.addEventListener('change', () => {
            if (input.files.length) {
                this.handleUpload(input.files[0]);
            }
        });
    },

    async handleUpload(file) {
        const status = document.getElementById('uploadStatus');
        const subfolder = document.getElementById('subfolderSelect')?.value || '';

        status.style.display = 'block';
        status.innerHTML = `<div style="color: var(--color-cyan); font-family: var(--font-mono); font-size: 0.85rem;">Ingesting ${this.escapeHtml(file.name)} into Lore Sea...</div>`;

        const formData = new FormData();
        formData.append('file', file);
        if (subfolder) {
            formData.append('subfolder', subfolder);
        }

        const headers = {};
        if (App.csrfToken) {
            headers['X-CSRF-TOKEN'] = App.csrfToken;
        }

        try {
            const res = await fetch('/api/knowledge/upload.php', {
                method: 'POST',
                headers: headers,
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                status.innerHTML = `<div style="color: var(--success); font-family: var(--font-mono); font-size: 0.85rem;">✅ Ingested into Lore Sea: <strong>${this.escapeHtml(data.filename)}</strong> (${data.chunks_count || 0} vectors)</div>`;
                this.loadFiles();
                setTimeout(() => {
                    status.style.display = 'none';
                }, 5000);
            } else {
                status.innerHTML = `<div style="color: var(--danger); font-family: var(--font-mono); font-size: 0.85rem;">❌ Error: ${this.escapeHtml(data.error || 'Upload failed')}</div>`;
            }
        } catch (e) {
            console.error('Upload failed', e);
            status.innerHTML = `<div style="color: var(--danger); font-family: var(--font-mono); font-size: 0.85rem;">❌ Upload failed.</div>`;
        }
    },

    formatSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Initialise when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Knowledge.init();
});
