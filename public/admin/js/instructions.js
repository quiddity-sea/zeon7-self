const Instructions = {
    async init() {
        this.editor = document.getElementById('instructionEditor');
        this.saveBtn = document.getElementById('saveBtn');
        this.historyContainer = document.getElementById('versionHistory');
        this.statusMsg = document.getElementById('statusMessage');

        this.saveBtn.addEventListener('click', () => this.save());

        await Promise.all([
            this.loadCurrent(),
            this.loadHistory()
        ]);
    },

    async loadCurrent() {
        try {
            const res = await fetch('/api/instruction/current.php');
            const data = await res.json();

            if (data.success && data.content) {
                this.editor.value = data.content;
            } else {
                this.editor.value = ''; // No current instruction or error
            }
        } catch (e) {
            console.error('Failed to load current instruction', e);
            this.showStatus('Failed to load current instruction.', 'error');
        }
    },

    async loadHistory() {
        try {
            const res = await fetch('/api/instruction/versions.php');
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            if (data.versions.length === 0) {
                this.historyContainer.innerHTML = `
                    <div style="padding: 1rem; text-align: center; color: var(--text-secondary);">
                        No versions found.
                    </div>
                `;
                return;
            }

            this.versions = data.versions; // Store for access

            this.historyContainer.innerHTML = data.versions.map((version, index) => `
                <div class="version-item" onclick="Instructions.loadVersionFromHistory(${index}, this)">
                    <div style="font-weight: 500;">Version ${data.versions.length - index}</div>
                    <div class="version-meta">
                        ${new Date(version.created_at).toLocaleString()}
                    </div>
                </div>
            `).join('');

        } catch (e) {
            console.error('Failed to load history', e);
            this.historyContainer.innerHTML = `
                <div style="padding: 1rem; text-align: center; color: var(--danger);">
                    Failed to load history.
                </div>
            `;
        }
    },

    loadVersionFromHistory(index, element) {
        const version = this.versions[index];
        if (!version) return;

        // Update editor content
        this.editor.value = version.content;

        // Update active state in UI
        document.querySelectorAll('.version-item').forEach(el => el.classList.remove('active'));
        if (element) {
            element.classList.add('active');
        }

        this.showStatus('Loaded version into editor. Click Save to make it current.', 'info');
    },

    async save() {
        const content = this.editor.value.trim();
        if (!content) {
            this.showStatus('Instruction content cannot be empty.', 'error');
            return;
        }

        this.saveBtn.disabled = true;
        this.saveBtn.textContent = 'Saving...';

        try {
            const res = await fetch('/api/instruction/create.php', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify({ content })
            });
            const data = await res.json();

            if (data.success) {
                this.showStatus('Instruction saved successfully!', 'success');
                await this.loadHistory(); // Refresh history
            } else {
                this.showStatus('Error: ' + data.error, 'error');
            }
        } catch (e) {
            console.error('Save failed', e);
            this.showStatus('Failed to save instruction.', 'error');
        } finally {
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'Save New Version';
        }
    },

    showStatus(msg, type) {
        this.statusMsg.textContent = msg;
        this.statusMsg.style.display = 'block';
        this.statusMsg.className = ''; // Reset classes

        if (type === 'error') {
            this.statusMsg.style.color = 'var(--danger)';
        } else if (type === 'success') {
            this.statusMsg.style.color = 'var(--success)';
        } else {
            this.statusMsg.style.color = 'var(--text-secondary)';
        }

        setTimeout(() => {
            this.statusMsg.style.display = 'none';
        }, 5000);
    },

    escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;")
            .replace(/\\/g, "\\\\") // Escape backslashes for JS string
            .replace(/\n/g, "\\n")  // Escape newlines for JS string
            .replace(/\r/g, "");
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    Instructions.init();
});
