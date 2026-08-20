const Settings = {
    init() {
        this.form = document.getElementById('settingsForm');
        this.providerSelect = document.getElementById('provider');
        this.geminiSelect = document.getElementById('geminiModel');
        this.ollamaSelect = document.getElementById('ollamaModel');
        this.customInput = document.getElementById('customModel');
        this.apiKeyInput = document.getElementById('apiKey');
        this.toggleKeyBtn = document.getElementById('toggleKeyBtn');
        this.saveBtn = document.getElementById('saveBtn');
        this.testAiBtn = document.getElementById('testAiBtn');
        this.terminalOutput = document.getElementById('terminalOutput') || document.getElementById('terminal-output');
        this.tokenDisplay = document.getElementById('tokenDisplay');
        this.modelHelp = document.getElementById('modelHelp');
        this.apiKeyHelp = document.getElementById('apiKeyHelp');
        this.apiKeyGroup = document.getElementById('apiKeyGroup');
        this.ollamaThinkGroup = document.getElementById('ollamaThinkGroup');
        this.ollamaThinkCheckbox = document.getElementById('ollamaThink');
        this.thinkStatusText = document.getElementById('thinkStatusText');

        this.totalTokens = 0;
        this.configData = null;

        this.bindEvents();
        this.loadSettings();
    },

    bindEvents() {
        this.providerSelect?.addEventListener('change', () => this.handleProviderChange());
        this.form?.addEventListener('submit', (e) => this.save(e));
        
        if (this.ollamaThinkCheckbox) {
            this.ollamaThinkCheckbox.addEventListener('change', () => this.updateThinkLabel());
        }

        if (this.testAiBtn) {
            this.testAiBtn.addEventListener('click', () => this.testConnection());
        }

        if (this.toggleKeyBtn) {
            this.toggleKeyBtn.addEventListener('click', () => {
                const isPassword = this.apiKeyInput.type === 'password';
                this.apiKeyInput.type = isPassword ? 'text' : 'password';
                this.toggleKeyBtn.textContent = isPassword ? '🔒' : '👁';
            });
        }

        const resetBtn = document.getElementById('resetSystemBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetSystem());
        }
    },

    updateThinkLabel() {
        if (!this.ollamaThinkCheckbox || !this.thinkStatusText) return;
        const isChecked = this.ollamaThinkCheckbox.checked;
        if (isChecked) {
            this.thinkStatusText.textContent = 'THINK: TRUE (Reasoning Scratchpad Enabled)';
            this.thinkStatusText.style.color = '#facc15';
        } else {
            this.thinkStatusText.textContent = 'THINK: FALSE (--think=false Enforced)';
            this.thinkStatusText.style.color = '#22d3ee';
        }
    },

    log(msg, type = 'info') {
        const term = this.terminalOutput || document.getElementById('terminalOutput') || document.getElementById('terminal-output');
        if (!term) return;

        const line = document.createElement('div');
        line.className = `log-line ${type}`;
        line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;

        const cursor = term.querySelector('.cursor');
        if (cursor) {
            term.insertBefore(line, cursor);
        } else {
            term.appendChild(line);
        }
        term.scrollTop = term.scrollHeight;
    },

    async loadSettings() {
        this.log('Querying active neural parameters...', 'system');
        try {
            const res = await fetch('/api/config/get.php');
            const text = await res.text();
            const data = JSON.parse(text.trim());

            if (data.success) {
                this.configData = data.config;
                this.totalTokens = data.total_tokens || 0;
                this.updateTokenDisplay();

                this.providerSelect.value = this.configData.provider || 'ollama';
                this.handleProviderChange();

                if (this.configData.provider === 'gemini') {
                    this.geminiSelect.value = this.configData.model || 'gemini-2.5-flash';
                } else if (this.configData.provider === 'ollama') {
                    this.ollamaSelect.value = this.configData.model || 'Brain32:latest';
                } else {
                    this.customInput.value = this.configData.model || 'openai/gpt-4';
                }

                if (this.ollamaThinkCheckbox) {
                    this.ollamaThinkCheckbox.checked = Boolean(this.configData.ollama_think);
                    this.updateThinkLabel();
                }

                const thinkState = this.configData.ollama_think ? 'THINK: TRUE' : 'THINK: FALSE';
                this.log(`Active Engine: ${this.configData.provider.toUpperCase()} (${this.configData.model}) [${thinkState}]`, 'success');
                this.updateStatus();
            } else {
                this.log('Configuration query failed: ' + data.error, 'error');
            }
        } catch (e) {
            console.error('Failed to load settings', e);
            this.log('Error querying configuration: ' + e.message, 'error');
        }
    },

    handleProviderChange() {
        const provider = this.providerSelect.value;
        
        this.geminiSelect.style.display = 'none';
        this.ollamaSelect.style.display = 'none';
        this.customInput.style.display = 'none';

        if (provider === 'gemini') {
            this.geminiSelect.style.display = 'block';
            this.ollamaThinkGroup.style.display = 'none';
            this.modelHelp.textContent = 'Google Generative Language Model (Gemini 2.5 Flash recommended).';
            this.apiKeyInput.disabled = false;
            this.apiKeyInput.placeholder = '••••••••••••••••';
            this.apiKeyHelp.textContent = 'Enter new Google Gemini API key to update keyring. Empty keeps current key.';
        } else if (provider === 'ollama') {
            this.ollamaSelect.style.display = 'block';
            this.ollamaThinkGroup.style.display = 'block';
            this.modelHelp.textContent = 'Local Ollama Engine running Brain32:latest.';
            this.apiKeyInput.disabled = true;
            this.apiKeyInput.placeholder = 'LOCAL OLLAMA: NO API KEY REQUIRED (http://127.0.0.1:11434)';
            this.apiKeyHelp.textContent = 'Local Ollama connects directly to http://127.0.0.1:11434.';
        } else {
            this.customInput.style.display = 'block';
            this.ollamaThinkGroup.style.display = 'none';
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
            this.setStatus(keyStatusEl, 'LOCAL OLLAMA', 'success');
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
        this.log(`--- INITIATING NEURAL CONNECTION TEST ---`, 'system');
        this.log(`Target: ${provider.toUpperCase()} // Model: ${targetModel} // [${thinkFlag}]`, 'info');

        try {
            const res = await fetch('/api/config/test_connection.php');
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
            ollama_think: this.ollamaThinkCheckbox ? this.ollamaThinkCheckbox.checked : false
        };

        this.saveBtn.disabled = true;
        this.saveBtn.textContent = 'SAVING PROTOCOLS...';
        const thinkStr = config.ollama_think ? 'think=true' : 'think=false';
        this.log(`Transmitting updated parameters (${provider.toUpperCase()}: ${model} [${thinkStr}])...`, 'system');

        try {
            const res = await fetch('/api/config/update.php', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(config)
            });
            const text = await res.text();
            const data = JSON.parse(text.trim());

            if (data.success) {
                this.log(`Protocols saved: Active Provider set to ${provider.toUpperCase()} (${model}) [${thinkStr}]`, 'success');
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