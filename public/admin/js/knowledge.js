const Knowledge = {
    async init() {
        this.loadFiles();
        this.setupUpload();
    },

    async loadFiles() {
        const tbody = document.getElementById('filesList');
        try {
            const res = await fetch('/api/knowledge/list.php');
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            if (data.files.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                            No files uploaded yet.
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = data.files.map(file => `
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span>📄</span>
                            <span style="font-weight: 500;">${this.escapeHtml(file.filename)}</span>
                        </div>
                    </td>
                    <td style="padding: 1rem; color: var(--text-secondary);">${this.formatSize(file.size)}</td>
                    <td style="padding: 1rem; color: var(--text-secondary);">${new Date(file.created_at).toLocaleDateString()}</td>
                    <td style="padding: 1rem; text-align: right;">
                        <button onclick="Knowledge.deleteFile(${file.id})" 
                                style="background: transparent; border: 1px solid var(--danger); color: var(--danger); padding: 0.25rem 0.75rem; border-radius: 0.25rem; cursor: pointer; font-size: 0.875rem;">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join('');

        } catch (e) {
            console.error('Failed to load files', e);
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" style="padding: 2rem; text-align: center; color: var(--danger);">
                        Failed to load files.
                    </td>
                </tr>
            `;
        }
    },

    async deleteFile(id) {
        if (!confirm('Are you sure you want to delete this file? This cannot be undone.')) return;

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
        const status = document.getElementById('uploadStatus');

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
        status.style.display = 'block';
        status.innerHTML = `<div style="color: var(--text-secondary);">Uploading ${file.name}...</div>`;

        const formData = new FormData();
        formData.append('file', file);

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
                status.innerHTML = `<div style="color: var(--success);">✅ Uploaded successfully!</div>`;
                this.loadFiles();
                setTimeout(() => {
                    status.style.display = 'none';
                }, 3000);
            } else {
                status.innerHTML = `<div style="color: var(--danger);">❌ Error: ${data.error}</div>`;
            }
        } catch (e) {
            console.error('Upload failed', e);
            status.innerHTML = `<div style="color: var(--danger);">❌ Upload failed.</div>`;
        }
    },

    formatSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Knowledge.init();
});
