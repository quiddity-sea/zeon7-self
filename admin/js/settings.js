/**
 * Zeon7 Mission Control — Settings Manager
 * Dynamic AI Provider switcher & API Keyring manager
 */

const Settings = {
    configData: null,
    totalTokens: 0,

    async init() {
        this.form = document.getElementById('settingsForm');
        this.providerSelect = document.getElementById('provider');
        this.geminiSelect = document.getElementById('geminiModel');
        this.ollamaSelect = document.getElementById('ollamaModel');
        this.customInput = document.getElementById('customModel');
        this.modelHelp = document.getElementById('modelHelp');
        
        this.apiKeyGroup = document.getElementById('apiKeyGroup');
        this.apiKeyInput = document.getElementById('apiKey');
        this.apiKeyHelp = document.getElementById('apiKeyHelp');
        this.toggleKeyBtn = document.getElementById('toggleKeyBtn');

        this.ollamaThinkGroup = document.getElementById('ollamaThinkGroup');
        this.ollamaThinkCheckbox = document.getElementById('ollamaThink');
        this.thinkStatusText = document.getElementById('thinkStatusText');

        this.ollamaHostGroup = document.getElementById('ollamaHostGroup');
        this.ollamaHostInput = document.getElementById('ollamaHost');
        
        this.testBtn = document.getElementById('testAiBtn');
        this.saveBtn = document.getElementById('saveBtn');
        this.terminalOutput = document.getElementById('terminalOutput');
        this.tokenDisplay = document.getElementById('tokenDisplay');

        // Neural Link Matrix elements
        this.publicChatSelect = document.getElementById('publicChatAgent');
        this.authChatSelect = document.getElementById('authChatAgent');
        this.configAgentSelect = document.getElementById('configAgentSelect');
        this.agentProviderSelect = document.getElementById('agentProvider');
        this.agentOllamaSelect = document.getElementById('agentOllamaModel');
        this.agentGeminiSelect = document.getElementById('agentGeminiModel');
        this.agentCustomInput = document.getElementById('agentCustomModel');
        this.agentModelHelp = document.getElementById('agentModelHelp');
        this.agentThinkCheckbox = document.getElementById('agentThink');
        this.agentEngines = {};
        this.currentConfigAgent = 'zeon7';

        if (this.configAgentSelect) {
            this.configAgentSelect.addEventListener('change', () => this.handleConfigAgentChange());
        }
        if (this.agentProviderSelect) {
            this.agentProviderSelect.addEventListener('change', () => this.handleAgentProviderChange());
        }
        if (this.agentOllamaSelect) {
            this.agentOllamaSelect.addEventListener('change', () => this.syncCurrentAgentToState());
        }
        if (this.agentGeminiSelect) {
            this.agentGeminiSelect.addEventListener('change', () => this.syncCurrentAgentToState());
        }
        if (this.agentCustomInput) {
            this.agentCustomInput.addEventListener('input', () => this.syncCurrentAgentToState());
        }
        if (this.agentThinkCheckbox) {
            this.agentThinkCheckbox.addEventListener('change', () => this.syncCurrentAgentToState());
        }

        // Tab Switching Handlers
        const tabBtns = document.querySelectorAll('.settings-tab-btn');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-tab');
                this.switchTab(targetId);
            });
        });

        // Handlers
        this.providerSelect.addEventListener('change', () => this.handleProviderChange());
        this.form.addEventListener('submit', (e) => this.save(e));
        this.testBtn.addEventListener('click', () => this.testConnection());

        if (this.ollamaThinkCheckbox) {
            this.ollamaThinkCheckbox.addEventListener('change', () => {
                this.updateThinkStatusText(this.ollamaThinkCheckbox.checked);
            });
        }

        if (this.toggleKeyBtn) {
            this.toggleKeyBtn.addEventListener('click', () => {
                const type = this.apiKeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
                this.apiKeyInput.setAttribute('type', type);
                this.toggleKeyBtn.textContent = type === 'password' ? '👁' : '🔒';
            });
        }

        const resetBtn = document.getElementById('resetSystemBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetSystem());
        }

        this.initEnvTab();

        await this.loadSettings();
    },

    switchTab(tabId) {
        document.querySelectorAll('.settings-tab-btn').forEach(b => {
            b.classList.toggle('active', b.getAttribute('data-tab') === tabId);
        });
        document.querySelectorAll('.settings-tab-pane').forEach(p => {
            p.style.display = (p.id === tabId) ? 'block' : 'none';
        });

        const defaultFooter = document.getElementById('defaultFooterActions');
        if (defaultFooter) {
            defaultFooter.style.display = (tabId === 'tab-api-keys') ? 'none' : 'flex';
        }

        if (tabId === 'tab-api-keys' && !this.envLoaded) {
            this.loadEnvConfig();
        }
    },

    handleAgentProviderChange() {
        const prov = this.agentProviderSelect ? this.agentProviderSelect.value : 'gemini';
        if (this.agentGeminiSelect) this.agentGeminiSelect.style.display = (prov === 'gemini') ? 'block' : 'none';
        if (this.agentOllamaSelect) this.agentOllamaSelect.style.display = (prov === 'ollama') ? 'block' : 'none';
        if (this.agentCustomInput) this.agentCustomInput.style.display = (prov === 'openrouter') ? 'block' : 'none';

        if (this.agentModelHelp) {
            if (prov === 'gemini') this.agentModelHelp.textContent = 'Google Gemini 2.5 models with web grounding and high speed.';
            else if (prov === 'ollama') this.agentModelHelp.textContent = 'Local hardware / Tailscale VPN remote Ollama inference.';
            else this.agentModelHelp.textContent = 'Specify OpenRouter multi-LLM model identifier.';
        }
        this.syncCurrentAgentToState();
    },

    getAgentCurrentModel() {
        const prov = this.agentProviderSelect ? this.agentProviderSelect.value : 'gemini';
        if (prov === 'gemini') {
            return this.agentGeminiSelect ? this.agentGeminiSelect.value : 'gemini-2.5-flash';
        } else if (prov === 'ollama') {
            return this.agentOllamaSelect ? this.agentOllamaSelect.value : 'Brain32:latest';
        } else {
            return this.agentCustomInput ? this.agentCustomInput.value.trim() : '';
        }
    },

    syncCurrentAgentToState() {
        if (!this.currentConfigAgent) return;
        const prov = this.agentProviderSelect ? this.agentProviderSelect.value : 'gemini';
        let model = this.getAgentCurrentModel();
        if (!model) {
            model = (prov === 'ollama') ? 'Brain32:latest' : (prov === 'gemini' ? 'gemini-2.5-flash' : '');
        }
        this.agentEngines[this.currentConfigAgent] = {
            provider: prov,
            model: model,
            think: this.agentThinkCheckbox ? this.agentThinkCheckbox.checked : false
        };
    },

    handleConfigAgentChange() {
        this.syncCurrentAgentToState();
        const slug = this.configAgentSelect ? this.configAgentSelect.value : 'zeon7';
        this.currentConfigAgent = slug;
        const cfg = this.agentEngines[slug] || { provider: 'gemini', model: 'gemini-2.5-flash', think: false };
        
        const prov = cfg.provider || 'gemini';
        if (this.agentProviderSelect) this.agentProviderSelect.value = prov;
        
        let modelVal = cfg.model;
        if (!modelVal) {
            modelVal = (prov === 'ollama') ? 'Brain32:latest' : (prov === 'gemini' ? 'gemini-2.5-flash' : '');
        }

        if (prov === 'gemini' && this.agentGeminiSelect) {
            this.agentGeminiSelect.value = modelVal;
        } else if (prov === 'ollama' && this.agentOllamaSelect) {
            this.agentOllamaSelect.value = modelVal;
            if (!this.agentOllamaSelect.value) {
                this.agentOllamaSelect.value = 'Brain32:latest';
            }
        } else if (this.agentCustomInput) {
            this.agentCustomInput.value = modelVal;
        }
        
        this.handleAgentProviderChange();
        if (this.agentThinkCheckbox) this.agentThinkCheckbox.checked = Boolean(cfg.think);
    },

    updateThinkStatusText(checked) {
        if (!this.thinkStatusText) return;
        if (checked) {
            this.thinkStatusText.textContent = 'THINK: ENABLED (--think=true)';
            this.thinkStatusText.style.color = 'var(--color-gold)';
        } else {
            this.thinkStatusText.textContent = 'THINK: DISABLED (--think=false)';
            this.thinkStatusText.style.color = 'var(--color-cyan)';
        }
    },

    log(msg, type = 'info') {
        if (!this.terminalOutput) return;
        const div = document.createElement('div');
        div.className = `terminal-line ${type}`;
        const time = new Date().toLocaleTimeString('en-US', { hour12: false });
        div.innerHTML = `<span class="time">[${time}]</span> ${msg}`;
        this.terminalOutput.appendChild(div);
        this.terminalOutput.scrollTop = this.terminalOutput.scrollHeight;
    },

    async loadSettings() {
        this.log('Querying System Configuration...', 'system');
        try {
            const res = await fetch('/api/config/get.php');
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text.trim());
            } catch(jsonErr) {
                throw new Error('Invalid JSON from server: ' + text.substring(0, 50));
            }

            if (!data.success) throw new Error(data.error || 'Server rejected query');

            this.configData = data.config;
            this.totalTokens = data.total_tokens || 0;
            this.updateTokenDisplay();

            // Populate form
            this.providerSelect.value = data.config.provider || 'ollama';

            if (data.config.provider === 'gemini') {
                this.geminiSelect.value = data.config.model || 'gemini-2.5-flash';
            } else if (data.config.provider === 'ollama') {
                this.ollamaSelect.value = data.config.ollama_model || 'Brain32:latest';
            } else {
                this.customInput.value = data.config.model || '';
            }

            if (this.ollamaThinkCheckbox) {
                const thinkVal = data.config.ollama_think === true || data.config.ollama_think === 'true';
                this.ollamaThinkCheckbox.checked = thinkVal;
                this.updateThinkStatusText(thinkVal);
            }

            if (this.ollamaHostInput) {
                this.ollamaHostInput.value = data.config.ollama_host || 'http://127.0.0.1:11434';
            }

            // Load Neural Link Chat Mode configurations
            if (data.config.public_chat_agent && this.publicChatSelect) {
                this.publicChatSelect.value = data.config.public_chat_agent;
            }
            if (data.config.authenticated_default_agent && this.authChatSelect) {
                this.authChatSelect.value = data.config.authenticated_default_agent;
            }
            if (data.config.agent_engines) {
                this.agentEngines = data.config.agent_engines;
                this.handleConfigAgentChange();
            }

            this.handleProviderChange();
            this.updateStatus();
            this.log(`Active Provider: ${data.config.provider.toUpperCase()} (Model: ${data.config.model})`, 'success');

        } catch (e) {
            console.error('Failed to load settings', e);
            this.log(`Initialization Error: ${e.message}`, 'error');
        }
    },

    handleProviderChange() {
        const provider = this.providerSelect.value;

        // Hide all model selects
        this.geminiSelect.style.display = 'none';
        this.ollamaSelect.style.display = 'none';
        this.customInput.style.display = 'none';

        if (provider === 'gemini') {
            this.geminiSelect.style.display = 'block';
            this.ollamaThinkGroup.style.display = 'none';
            this.ollamaHostGroup.style.display = 'none';
            this.apiKeyGroup.style.display = 'block';
            this.modelHelp.textContent = 'Google Gemini 2.5 models support web grounding and fast token streams.';
            this.apiKeyInput.disabled = false;
            this.apiKeyInput.placeholder = 'AIzaSy•••••••••••••••••••••••••••••';
            this.apiKeyHelp.textContent = 'Enter Google AI Studio API key. Empty keeps current key.';
        } else if (provider === 'ollama') {
            this.ollamaSelect.style.display = 'block';
            this.ollamaThinkGroup.style.display = 'block';
            this.ollamaHostGroup.style.display = 'block';
            this.apiKeyGroup.style.display = 'none';
            this.modelHelp.textContent = 'Local Ollama runs on your hardware or via Tailscale Mesh VPN tunnel.';
            this.apiKeyInput.disabled = true;
            this.apiKeyInput.placeholder = 'No API key required for Ollama';
            this.apiKeyHelp.textContent = 'Ollama uses local network host endpoints.';
        } else {
            this.customInput.style.display = 'block';
            this.ollamaThinkGroup.style.display = 'none';
            this.ollamaHostGroup.style.display = 'none';
            this.apiKeyGroup.style.display = 'block';
            this.modelHelp.textContent = 'Specify OpenRouter model string (e.g. anthropic/claude-3.5-sonnet).';
            this.apiKeyInput.disabled = false;
            this.apiKeyInput.placeholder = 'sk-or-v1-••••••••';
            this.apiKeyHelp.textContent = 'Enter OpenRouter API key. Empty keeps current key.';
        }
    },

    async updateStatus() {
        const provider = this.providerSelect.value;
        const keyStatusEl = document.getElementById('keyStatus');
        const aiStatusEl = document.getElementById('aiStatus');

        if (provider === 'ollama') {
            this.setStatus(keyStatusEl, 'LOCAL/TUNNEL', 'success');
            this.setStatus(aiStatusEl, 'AI: READY', 'success');
            return;
        }

        const isKeySet = provider === 'gemini' ? this.configData?.gemini_key_set : this.configData?.openrouter_key_set;

        if (isKeySet) {
            this.setStatus(keyStatusEl, 'KEY PRESENT', 'success');
            this.setStatus(aiStatusEl, 'AI: READY', 'success');
        } else {
            this.setStatus(keyStatusEl, 'KEY MISSING', 'error');
            this.setStatus(aiStatusEl, 'AI UNAVAILABLE', 'neutral');
            this.log(`Notice: No API Key configured for ${provider.toUpperCase()}.`, 'error');
        }
    },

    async testConnection() {
        const aiStatusEl = document.getElementById('aiStatus');
        this.setStatus(aiStatusEl, 'AI: TESTING...', 'neutral');
        const provider = this.providerSelect.value;
        
        let targetModel = '';
        if (provider === 'gemini') targetModel = this.geminiSelect.value;
        else if (provider === 'ollama') targetModel = this.ollamaSelect.value;
        else targetModel = this.customInput.value;

        const thinkFlag = (provider === 'ollama' && this.ollamaThinkCheckbox?.checked) ? 'THINK=TRUE' : 'THINK=FALSE';
        const hostInfo = (provider === 'ollama' && this.ollamaHostInput) ? ` // Endpoint: ${this.ollamaHostInput.value}` : '';
        this.log(`--- INITIATING NEURAL CONNECTION TEST ---`, 'system');
        this.log(`Target: ${provider.toUpperCase()} // Model: ${targetModel}${hostInfo} // [${thinkFlag}]`, 'info');

        try {
            const url = `/api/config/test_connection.php?provider=${encodeURIComponent(provider)}&model=${encodeURIComponent(targetModel)}`;
            const res = await fetch(url);
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text.trim());
            } catch(jsonErr) {
                throw new Error('Invalid server stream: ' + text.substring(0, 80));
            }

            if (data.success) {
                this.setStatus(aiStatusEl, 'AI CONNECTED', 'success');
                this.log(`>> TEST PROMPT: "${data.prompt}"`, 'info');
                this.log(`<< RESPONSE: "${data.reply}"`, 'success');

                if (data.usage) {
                    const promptTok = data.usage.prompt || 0;
                    const respTok = data.usage.response || 0;
                    const totalTok = data.usage.total || (promptTok + respTok);
                    this.totalTokens += totalTok;
                    this.updateTokenDisplay();
                    this.log(`* TELEMETRY: Prompt: ${promptTok} | Response: ${respTok} | Total: ${totalTok} tokens`, 'system');
                }
                this.log(`--- CONNECTION VERIFIED (STATUS: ONLINE) ---`, 'success');
            } else {
                this.setStatus(aiStatusEl, 'AI DISCONNECTED', 'error');
                this.log(`!! CONNECTION FAILED: ${data.error}`, 'error');
            }
        } catch (e) {
            this.setStatus(aiStatusEl, 'AI ERROR', 'error');
            this.log(`!! NETWORK ERROR: ${e.message}`, 'error');
        }
    },

    updateTokenDisplay() {
        if (this.tokenDisplay) {
            this.tokenDisplay.textContent = `TOKENS USED: ${new Intl.NumberFormat('en-US').format(this.totalTokens)}`;
        }
    },

    setStatus(el, text, type) {
        if (!el) return;
        el.textContent = text;
        if (type === 'success') {
            el.style.color = '#00ff41';
            el.style.background = 'rgba(0, 255, 65, 0.1)';
            el.style.border = '1px solid #00ff41';
        } else if (type === 'error') {
            el.style.color = '#f43f5e';
            el.style.background = 'rgba(244, 63, 94, 0.1)';
            el.style.border = '1px solid #f43f5e';
        } else {
            el.style.color = 'var(--text-muted)';
            el.style.background = 'rgba(255, 255, 255, 0.05)';
            el.style.border = '1px solid transparent';
        }
    },

    async save(e) {
        e.preventDefault();

        const provider = this.providerSelect.value;
        let model = '';
        if (provider === 'gemini') {
            model = this.geminiSelect.value;
        } else if (provider === 'ollama') {
            model = this.ollamaSelect.value;
        } else {
            model = this.customInput.value;
        }

        this.syncCurrentAgentToState();

        const config = {
            provider: provider,
            model: model,
            api_key: this.apiKeyInput.value,
            ollama_think: this.ollamaThinkCheckbox ? this.ollamaThinkCheckbox.checked : false,
            ollama_host: this.ollamaHostInput ? this.ollamaHostInput.value.trim() : 'http://127.0.0.1:11434',
            public_chat_agent: this.publicChatSelect ? this.publicChatSelect.value : 'zeon7',
            authenticated_default_agent: this.authChatSelect ? this.authChatSelect.value : 'zeon7',
            agent_engines: this.agentEngines
        };

        this.saveBtn.disabled = true;
        this.saveBtn.textContent = 'SAVING PROTOCOLS...';
        const thinkStr = config.ollama_think ? 'think=true' : 'think=false';
        this.log(`Transmitting updated parameters (${provider.toUpperCase()}: ${model} [${thinkStr}] Endpoint: ${config.ollama_host})...`, 'system');

        try {
            const res = await fetch('/api/config/update.php', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(config)
            });
            const text = await res.text();
            const data = JSON.parse(text.trim());

            if (data.success) {
                this.log(`Protocols saved: Active Provider set to ${provider.toUpperCase()} (${model})`, 'success');
                this.apiKeyInput.value = '';
                await this.loadSettings();
                await this.testConnection();
            } else {
                this.log(`Save rejected: ${data.error}`, 'error');
                alert('Save failed: ' + data.error);
            }
        } catch (e) {
            console.error('Save failed', e);
            this.log(`Transmission error: ${e.message}`, 'error');
            alert('Failed to save settings: ' + e.message);
        } finally {
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'UPDATE SYSTEM PROTOCOLS';
        }
    },

    async resetSystem() {
        if (!confirm('WARNING: This will wipe all Lore, Knowledge, Instructions, and Chat History.\\n\\nAre you sure you want to perform a FACTORY RESET?')) {
            return;
        }

        const btn = document.getElementById('resetSystemBtn');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'RESETTING SYSTEM...';
        }
        this.log('INITIATING FACTORY RESET...', 'system');

        try {
            const res = await fetch('/admin/api/system-reset.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });
            const text = await res.text();
            const data = JSON.parse(text.trim());

            if (data.success) {
                this.log('SYSTEM RESET COMPLETE.', 'success');
                if (data.report && Array.isArray(data.report)) {
                    data.report.forEach(msg => this.log(`> ${msg}`, 'info'));
                }
                alert('System successfully reset to factory settings.');
            } else {
                this.log(`RESET FAILED: ${data.error}`, 'error');
                alert('Reset Failed: ' + data.error);
            }
        } catch (e) {
            this.log(`RESET ERROR: ${e.message}`, 'error');
            alert('Reset Error: ' + e.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'EXECUTE FACTORY SYSTEM RESET';
            }
        }
    },

    // ─────────────────────────────────────────────────────────────
    // Environment & API Keys Manager (.env)
    // ─────────────────────────────────────────────────────────────
    envEntries: [],
    deletedEnvKeys: new Set(),
    envLoaded: false,

    initEnvTab() {
        const reloadBtn = document.getElementById('reloadEnvBtn');
        if (reloadBtn) {
            reloadBtn.addEventListener('click', () => this.loadEnvConfig(true));
        }

        const addBtn = document.getElementById('addEnvKeyBtn');
        const newKeyCard = document.getElementById('newKeyCard');
        const cancelNewKeyBtn = document.getElementById('cancelNewKeyBtn');
        const confirmNewKeyBtn = document.getElementById('confirmNewKeyBtn');
        const searchInput = document.getElementById('envSearchInput');
        const saveEnvBtn = document.getElementById('saveEnvBtn');

        if (addBtn && newKeyCard) {
            addBtn.addEventListener('click', () => {
                const isHidden = newKeyCard.style.display === 'none' || !newKeyCard.style.display;
                newKeyCard.style.display = isHidden ? 'block' : 'none';
                if (isHidden) {
                    const nameInput = document.getElementById('newKeyName');
                    if (nameInput) nameInput.focus();
                }
            });
        }

        if (cancelNewKeyBtn && newKeyCard) {
            cancelNewKeyBtn.addEventListener('click', () => {
                newKeyCard.style.display = 'none';
                const nameInput = document.getElementById('newKeyName');
                const valInput = document.getElementById('newKeyValue');
                if (nameInput) nameInput.value = '';
                if (valInput) valInput.value = '';
            });
        }

        if (confirmNewKeyBtn) {
            confirmNewKeyBtn.addEventListener('click', () => this.addNewEnvKey());
        }

        if (searchInput) {
            searchInput.addEventListener('input', () => this.renderEnvFields());
        }

        if (saveEnvBtn) {
            saveEnvBtn.addEventListener('click', () => this.saveEnvConfig());
        }
    },

    async loadEnvConfig(forceReload = false) {
        if (this.envLoaded && !forceReload) return;

        const container = document.getElementById('envKeysContainer');
        const loader = document.getElementById('envLoadingIndicator');
        const statusBadge = document.getElementById('envStatusBadge');
        if (loader) {
            loader.style.display = 'block';
            loader.textContent = 'SYNCING KEYRING MATRIX...';
        }
        if (container) container.innerHTML = '';
        this.setStatus(statusBadge, 'ENV: SYNCING...', 'neutral');

        this.log('Querying .env Environment Keyring...', 'system');

        try {
            const res = await fetch('/admin/api/env_handler.php');
            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text.trim());
            } catch (err) {
                throw new Error('Invalid JSON response: ' + text.substring(0, 80));
            }

            if (!data.success) {
                throw new Error(data.error || 'Failed to read environment configuration');
            }

            this.envEntries = data.entries || [];
            this.deletedEnvKeys = new Set();
            this.envLoaded = true;

            if (loader) loader.style.display = 'none';
            this.renderEnvFields();

            const count = this.envEntries.length;
            this.setStatus(statusBadge, `ENV: ${count} KEYS SYNCED`, 'success');
            this.log(`Environment Keyring: ${count} keys loaded from .env (Writable: ${data.writable ? 'YES' : 'NO'})`, 'success');

        } catch (e) {
            if (loader) {
                loader.innerHTML = `<span style="color: var(--color-coral); font-weight: bold;">Failed to load .env: ${this.escapeHtml(e.message)}</span>`;
            }
            this.setStatus(statusBadge, 'ENV: ERROR', 'error');
            this.log(`Environment Keyring Error: ${e.message}`, 'error');
        }
    },

    renderEnvFields() {
        const container = document.getElementById('envKeysContainer');
        if (!container) return;

        container.innerHTML = '';
        const searchInput = document.getElementById('envSearchInput');
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();

        const filtered = this.envEntries.filter(item => {
            if (query === '') return true;
            return item.key.toLowerCase().includes(query) || 
                   (item.comment && item.comment.toLowerCase().includes(query)) ||
                   (item.value && item.value.toLowerCase().includes(query));
        });

        if (filtered.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-family: var(--font-mono); font-size: 0.8rem; border: 1px dashed rgba(255, 255, 255, 0.1); border-radius: 4px;">
                    NO MATCHING KEYS FOUND IN .ENV
                </div>
            `;
            return;
        }

        filtered.forEach(item => {
            const isDeleted = this.deletedEnvKeys.has(item.key);
            const isSecret = /KEY|PASS|SECRET|TOKEN|CREDENTIAL/i.test(item.key);

            const card = document.createElement('div');
            card.className = 'env-key-card' + (isDeleted ? ' deleted' : '');
            card.id = `env-card-${item.key}`;

            const commentHtml = item.comment ? `
                <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.35rem; font-family: var(--font-mono);">
                    // ${this.escapeHtml(item.comment)}
                </div>
            ` : '';

            card.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; gap: 0.5rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.82rem; color: var(--color-cyan); letter-spacing: 0.05em;">
                            ${this.escapeHtml(item.key)}
                        </span>
                        ${isSecret ? '<span class="hud-badge orange" style="font-size: 0.55rem; padding: 1px 4px;">SECRET</span>' : ''}
                        ${isDeleted ? '<span class="hud-badge coral" style="font-size: 0.55rem; padding: 1px 4px;">MARKED DELETED</span>' : ''}
                    </div>
                    <div style="display: flex; gap: 0.3rem;">
                        <button type="button" class="env-action-btn copy-btn" title="Copy value to clipboard" data-key="${this.escapeHtml(item.key)}">📋</button>
                        <button type="button" class="env-action-btn toggle-vis-btn" title="Toggle visibility" data-key="${this.escapeHtml(item.key)}">${isSecret ? '👁️' : '🔒'}</button>
                        <button type="button" class="env-action-btn delete delete-btn" title="${isDeleted ? 'Restore key' : 'Delete key'}" data-key="${this.escapeHtml(item.key)}">${isDeleted ? '↩️' : '🗑️'}</button>
                    </div>
                </div>
                ${commentHtml}
                <div style="position: relative;">
                    <input type="${isSecret ? 'password' : 'text'}" 
                           class="input-box env-value-input" 
                           data-key="${this.escapeHtml(item.key)}" 
                           value="${this.escapeHtml(item.value)}" 
                           ${isDeleted ? 'disabled' : ''}
                           style="width: 100%; font-family: var(--font-mono); font-size: 0.8rem; padding-right: 10px;">
                </div>
            `;

            // Bind card actions
            const copyBtn = card.querySelector('.copy-btn');
            copyBtn.addEventListener('click', () => {
                const input = card.querySelector('.env-value-input');
                navigator.clipboard.writeText(input.value).then(() => {
                    this.log(`Copied ${item.key} value to clipboard`, 'info');
                    copyBtn.textContent = '✓';
                    setTimeout(() => copyBtn.textContent = '📋', 1500);
                }).catch(() => {
                    input.select();
                    document.execCommand('copy');
                    copyBtn.textContent = '✓';
                    setTimeout(() => copyBtn.textContent = '📋', 1500);
                });
            });

            const toggleVisBtn = card.querySelector('.toggle-vis-btn');
            toggleVisBtn.addEventListener('click', () => {
                const input = card.querySelector('.env-value-input');
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                toggleVisBtn.textContent = isPass ? '🔒' : '👁️';
            });

            const deleteBtn = card.querySelector('.delete-btn');
            deleteBtn.addEventListener('click', () => {
                if (this.deletedEnvKeys.has(item.key)) {
                    this.deletedEnvKeys.delete(item.key);
                    this.log(`Restored key ${item.key}`, 'info');
                } else {
                    this.deletedEnvKeys.add(item.key);
                    this.log(`Marked key ${item.key} for deletion upon save`, 'info');
                }
                this.renderEnvFields();
            });

            // Live update value in memory
            const valInput = card.querySelector('.env-value-input');
            valInput.addEventListener('input', (e) => {
                item.value = e.target.value;
            });

            container.appendChild(card);
        });
    },

    addNewEnvKey() {
        const nameInput = document.getElementById('newKeyName');
        const valInput = document.getElementById('newKeyValue');
        const rawName = nameInput ? nameInput.value.trim().toUpperCase() : '';
        const val = valInput ? valInput.value : '';

        if (!rawName) {
            alert('Please specify a valid Key Identifier');
            return;
        }

        // Sanitize key name (must be alphanumeric and underscores)
        const cleanName = rawName.replace(/[^A-Z0-9_]/g, '');
        if (!cleanName) {
            alert('Key identifier must contain only letters, numbers, and underscores');
            return;
        }

        // Check if key already exists
        const existing = this.envEntries.find(i => i.key === cleanName);
        if (existing) {
            if (this.deletedEnvKeys.has(cleanName)) {
                this.deletedEnvKeys.delete(cleanName);
                existing.value = val;
            } else {
                alert(`Key ${cleanName} already exists in .env. You can update its value directly.`);
                return;
            }
        } else {
            this.envEntries.push({
                key: cleanName,
                value: val,
                comment: 'Added via Admin Keyring Manager'
            });
        }

        nameInput.value = '';
        valInput.value = '';
        const newKeyCard = document.getElementById('newKeyCard');
        if (newKeyCard) newKeyCard.style.display = 'none';

        this.renderEnvFields();
        this.log(`Added key ${cleanName} to pending list. Click UPDATE .ENV KEYRING to save.`, 'info');

        // Scroll to card
        const cardEl = document.getElementById(`env-card-${cleanName}`);
        if (cardEl) {
            cardEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            cardEl.style.borderColor = 'var(--color-cyan)';
        }
    },

    async saveEnvConfig() {
        const saveBtn = document.getElementById('saveEnvBtn');
        const statusBadge = document.getElementById('envStatusBadge');

        // Collect all values from DOM inputs and in-memory entries
        const inputs = document.querySelectorAll('.env-value-input');
        const payloadKeys = {};
        inputs.forEach(inp => {
            const k = inp.getAttribute('data-key');
            if (k && !this.deletedEnvKeys.has(k)) {
                payloadKeys[k] = inp.value;
            }
        });

        // Ensure in-memory entries that might have been filtered by search are preserved
        this.envEntries.forEach(item => {
            if (!this.deletedEnvKeys.has(item.key) && !(item.key in payloadKeys)) {
                payloadKeys[item.key] = item.value;
            }
        });

        const deletedList = Array.from(this.deletedEnvKeys);

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'COMMITTING .ENV...';
        }
        this.setStatus(statusBadge, 'ENV: COMMITTING...', 'neutral');
        this.log(`Committing updates to .env (${Object.keys(payloadKeys).length} keys, ${deletedList.length} deleted)...`, 'system');

        try {
            const res = await fetch('/admin/api/env_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    keys: payloadKeys,
                    deleted: deletedList
                })
            });

            const text = await res.text();
            let data;
            try {
                data = JSON.parse(text.trim());
            } catch (err) {
                throw new Error('Invalid server response: ' + text.substring(0, 80));
            }

            if (data.success) {
                this.setStatus(statusBadge, 'ENV: COMMITTED', 'success');
                this.log('Configuration successfully written to .env. Backup saved as .env.backup', 'success');
                // Reload to sync state
                await this.loadEnvConfig(true);
            } else {
                this.setStatus(statusBadge, 'ENV: WRITE FAILED', 'error');
                this.log(`Write Failed: ${data.error}`, 'error');
                alert('Save failed: ' + data.error);
            }
        } catch (e) {
            this.setStatus(statusBadge, 'ENV: ERROR', 'error');
            this.log(`Save Error: ${e.message}`, 'error');
            alert('Save error: ' + e.message);
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'UPDATE .ENV KEYRING';
            }
        }
    },

    escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
};
