const Instructions = {
    components: {},
    activeComponent: null,
    currentAgentId: null,

    async init() {
        this.editor = document.getElementById('instructionEditor');
        this.saveBtn = document.getElementById('saveBtn');
        this.resetBtn = document.getElementById('resetHeadBtn');
        this.historyContainer = document.getElementById('versionHistory');
        this.statusMsg = document.getElementById('statusMessage');
        this.tabsContainer = document.getElementById('componentTabs');
        this.infoLabel = document.getElementById('activeComponentInfo');
        this.wordCountLabel = document.getElementById('wordCount');

        this.editor.addEventListener('input', () => this.updateWordCount());
        this.saveBtn.addEventListener('click', () => this.save());
        this.resetBtn?.addEventListener('click', () => this.resetToActiveHead());

        await this.loadCurrent();
        await this.loadHistory();
    },

    updateWordCount() {
        const text = this.editor.value.trim();
        const words = text ? text.split(/\s+/).length : 0;
        this.wordCountLabel.textContent = `${words} words | ${text.length} chars`;
    },

    async loadCurrent() {
        try {
            const res = await fetch('/api/instruction/current.php');
            const data = await res.json();

            if (data.success) {
                this.currentAgentId = data.agent_id;
                this.components = data.components || {};
                this.renderTabs(data.components);

                if (data.content) {
                    this.editor.value = data.content;
                    this.infoLabel.textContent = `Active ${data.agent_name} Prompt (Version ${data.version})`;
                } else {
                    this.editor.value = '';
                    this.infoLabel.textContent = `No active instruction found for ${data.agent_name}`;
                }
                this.updateWordCount();
            }
        } catch (e) {
            console.error('Failed to load current instruction', e);
            this.showStatus('Failed to load current instruction.', 'error');
        }
    },

    renderTabs(components) {
        if (!this.tabsContainer) return;
        
        let html = `
            <span style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; margin-right: 0.5rem;">
                HEADS / COMPONENTS:
            </span>
        `;

        const compKeys = Object.keys(components);
        if (compKeys.length === 0) {
            html += `<span class="comp-tab active">Active Custom Prompt</span>`;
            this.tabsContainer.innerHTML = html;
            return;
        }

        compKeys.forEach((key, index) => {
            const comp = components[key];
            const activeClass = (index === 0) ? 'active' : '';
            html += `
                <button type="button" class="comp-tab ${activeClass}" data-comp="${key}" onclick="Instructions.selectComponent('${key}')" title="${comp.description}">
                    ${comp.name}
                </button>
            `;
        });

        this.tabsContainer.innerHTML = html;
    },

    selectComponent(key) {
        const comp = this.components[key];
        if (!comp) return;

        this.activeComponent = key;
        this.editor.value = comp.content || '';
        this.infoLabel.textContent = `Viewing Component: ${comp.name}`;

        document.querySelectorAll('.comp-tab').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-comp') === key);
        });

        this.updateWordCount();
        this.showStatus(`Loaded [${comp.name}] into editor. Click 'Save & Activate' to make it live for ${this.currentAgentId}.`, 'info');
    },

    resetToActiveHead() {
        if (this.activeComponent && this.components[this.activeComponent]) {
            this.editor.value = this.components[this.activeComponent].content;
            this.updateWordCount();
            this.showStatus(`Reset editor to baseline [${this.components[this.activeComponent].name}]`, 'info');
        }
    },

    async loadHistory() {
        try {
            const res = await fetch('/api/instruction/versions.php');
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            if (!data.versions || data.versions.length === 0) {
                this.historyContainer.innerHTML = `
                    <div style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">
                        No custom versions saved for this agent yet.
                    </div>
                `;
                return;
            }

            this.versions = data.versions;

            this.historyContainer.innerHTML = data.versions.map((version, index) => `
                <div class="version-item ${version.is_active == 1 ? 'active' : ''}" onclick="Instructions.loadVersionFromHistory(${index}, this)">
                    <div style="display:flex; justify-content:space-between; font-weight:600; font-size:0.85rem;">
                        <span>Version ${data.versions.length - index}</span>
                        ${version.is_active == 1 ? '<span style="color:var(--agent-accent); font-size:0.75rem;">● ACTIVE</span>' : ''}
                    </div>
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

        this.editor.value = version.content;
        this.infoLabel.textContent = `Loaded Version ${this.versions.length - index} (${version.created_at})`;

        document.querySelectorAll('.version-item').forEach(el => el.classList.remove('active'));
        if (element) {
            element.classList.add('active');
        }

        this.updateWordCount();
        this.showStatus('Loaded archived version into editor. Click Save to activate.', 'info');
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
                headers: (typeof App !== 'undefined' && App.getHeaders) ? App.getHeaders() : { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    content,
                    agent_id: this.currentAgentId,
                    component: this.activeComponent || 'custom'
                })
            });
            const data = await res.json();

            if (data.success) {
                this.showStatus(`Successfully saved & activated new instruction for ${this.currentAgentId}!`, 'success');
                await this.loadHistory();
            } else {
                this.showStatus('Error: ' + data.error, 'error');
            }
        } catch (e) {
            console.error('Save failed', e);
            this.showStatus('Failed to save instruction.', 'error');
        } finally {
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'Save & Activate Version';
        }
    },

    showStatus(msg, type) {
        this.statusMsg.textContent = msg;
        this.statusMsg.style.display = 'block';

        if (type === 'error') {
            this.statusMsg.style.color = '#ef4444';
        } else if (type === 'success') {
            this.statusMsg.style.color = '#10b981';
        } else {
            this.statusMsg.style.color = '#94a3b8';
        }

        setTimeout(() => {
            this.statusMsg.style.display = 'none';
        }, 6000);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Instructions.init();
});
