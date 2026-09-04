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

        await this.loadSettings();
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

        const config = {
            provider: provider,
            model: model,
            api_key: this.apiKeyInput.value,
            ollama_think: this.ollamaThinkCheckbox ? this.ollamaThinkCheckbox.checked : false,
            ollama_host: this.ollamaHostInput ? this.ollamaHostInput.value.trim() : 'http://127.0.0.1:11434'
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
    }
};
