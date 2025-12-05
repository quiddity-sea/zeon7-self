const Settings = {
    async init() {
        this.form = document.getElementById('settingsForm');
        this.providerSelect = document.getElementById('provider');

        // Model Inputs
        this.geminiSelect = document.getElementById('geminiModel');
        this.customInput = document.getElementById('customModel');
        this.modelHelp = document.getElementById('modelHelp');

        this.apiKeyInput = document.getElementById('apiKey');
        this.toggleKeyBtn = document.getElementById('toggleKeyBtn');
        this.saveBtn = document.getElementById('saveBtn');

        this.terminal = document.getElementById('terminalOutput');
        this.tokenDisplay = document.getElementById('tokenDisplay'); // New Element
        this.totalTokens = 0; // Initialize token count

        this.form.addEventListener('submit', (e) => this.save(e));
        this.providerSelect.addEventListener('change', () => {
            this.log(`Provider switched to: ${this.providerSelect.value}`, 'info');
            this.updateStatus();
            this.toggleModelInput();
        });

        // Toggle API Key Visibility
        if (this.toggleKeyBtn) {
            this.toggleKeyBtn.addEventListener('click', () => {
                const type = this.apiKeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
                this.apiKeyInput.setAttribute('type', type);
                this.toggleKeyBtn.style.opacity = type === 'text' ? '1' : '0.5';
            });
        }

        this.log('System initialized.', 'system');
        this.log('Loading configuration...', 'system');
        await this.loadSettings();
    },

    getModelName() {
        // If config hasn't loaded yet, return NO MODEL
        if (!this.configData) return 'NO MODEL';

        if (this.providerSelect && this.providerSelect.value === 'gemini') {
            return this.geminiSelect && this.geminiSelect.value ? this.geminiSelect.value : 'NO MODEL';
        } else if (this.providerSelect && this.providerSelect.value === 'openrouter') {
            return this.customInput && this.customInput.value ? this.customInput.value : 'NO MODEL';
        }
        return 'NO MODEL';
    },

    log(message, type = 'info') {
        if (!this.terminal) return;

        const line = document.createElement('div');
        line.className = `log-line ${type}`;

        // Add timestamp
        const time = new Date().toLocaleTimeString('en-US', { hour12: false });

        // Get Model Name
        const modelName = this.getModelName().toUpperCase();

        line.textContent = `${modelName} [${time}]$ ${message}`;

        // Insert before cursor
        const cursor = this.terminal.querySelector('.cursor');
        if (cursor) {
            this.terminal.insertBefore(line, cursor);
        } else {
            this.terminal.appendChild(line);
        }

        // Auto scroll
        this.terminal.scrollTop = this.terminal.scrollHeight;
    },

    async loadSettings() {
        try {
            const res = await fetch('/api/config/get.php');
            const data = await res.json();

            if (data.success) {
                this.configData = data.config; // Store for switching
                this.totalTokens = data.total_tokens || 0; // Load persistent tokens
                this.updateTokenDisplay(); // Update UI immediately

                this.log('Configuration loaded successfully.', 'success'); // Log AFTER setting configData
                this.providerSelect.value = data.config.provider;

                // Set values for both inputs
                if (data.config.provider === 'gemini') {
                    this.geminiSelect.value = data.config.model;
                    if (!this.geminiSelect.value) {
                        this.geminiSelect.value = 'gemini-pro-latest';
                    }
                    this.log(`Loaded Gemini config. Model: ${this.geminiSelect.value}`, 'info');
                } else {
                    this.customInput.value = data.config.model || '';
                    this.log(`Loaded OpenRouter config.`, 'info');
                }

                this.toggleModelInput();
                this.updateStatus();
            } else {
                this.log(`Failed to load settings: ${data.error}`, 'error');
                console.error('Failed to load settings', data.error);
            }
        } catch (e) {
            this.log(`Network error loading settings: ${e.message}`, 'error');
            console.error('Failed to load settings', e);
        }
    },

    toggleModelInput() {
        const provider = this.providerSelect.value;

        if (provider === 'gemini') {
            this.geminiSelect.style.display = 'block';
            this.customInput.style.display = 'none';
            if (this.modelHelp) this.modelHelp.textContent = 'Select a verified Gemini model.';
        } else {
            this.geminiSelect.style.display = 'none';
            this.customInput.style.display = 'block';
            if (this.modelHelp) this.modelHelp.textContent = 'Enter the model ID manually (e.g. openai/gpt-4).';
        }
    },

    async updateStatus() {
        const provider = this.providerSelect.value;
        const keyStatusEl = document.getElementById('keyStatus');
        const aiStatusEl = document.getElementById('aiStatus');

        // 1. Update Key Status (Immediate)
        const isKeySet = provider === 'gemini' ? this.configData?.gemini_key_set : this.configData?.openrouter_key_set;

        if (isKeySet) {
            this.setStatus(keyStatusEl, 'KEY PRESENT', 'success');
            this.log(`API Key found for ${provider}.`, 'success');

            // 2. Check AI Connection (Async)
            this.setStatus(aiStatusEl, 'AI: TESTING...', 'neutral');
            this.log(`Initiating connection test to ${provider}...`, 'system');

            try {
                const res = await fetch('/api/config/test_connection.php');
                const data = await res.json();

                if (data.success) {
                    this.setStatus(aiStatusEl, 'AI CONNECTED', 'success');
                    this.log(`Connection successful!`, 'success');
                    this.log(`> Prompt: "${data.prompt}"`, 'info');
                    this.log(`> Reply: "${data.reply}"`, 'info');

                    // Update Token Count if available
                    if (data.usage) {
                        this.totalTokens += data.usage.total || 0;
                        this.updateTokenDisplay();
                        this.log(`Tokens used: ${data.usage.total}`, 'system');
                    }

                } else {
                    this.setStatus(aiStatusEl, 'AI DISCONNECTED', 'error');
                    this.log(`Connection failed: ${data.error}`, 'error');
                    console.warn('AI Test Failed:', data.error);
                }
            } catch (e) {
                this.setStatus(aiStatusEl, 'AI ERROR', 'error');
                this.log(`Connection error: ${e.message}`, 'error');
            }
        } else {
            this.setStatus(keyStatusEl, 'KEY MISSING', 'error');
            this.setStatus(aiStatusEl, 'AI UNAVAILABLE', 'neutral');
            this.log(`No API Key found for ${provider}. Please enter a key.`, 'error');
        }
    },

    updateTokenDisplay() {
        if (this.tokenDisplay) {
            this.tokenDisplay.textContent = `TOKENS USED: ${this.totalTokens}`;
        }
    },

    setStatus(el, text, type) {
        el.textContent = text;
        if (type === 'success') {
            el.style.color = '#00ff00';
            el.style.background = 'rgba(0, 255, 0, 0.1)';
            el.style.border = '1px solid #00ff00';
        } else if (type === 'error') {
            el.style.color = '#ff0000';
            el.style.background = 'rgba(255, 0, 0, 0.1)';
            el.style.border = '1px solid #ff0000';
        } else {
            el.style.color = 'var(--text-muted)';
            el.style.background = 'rgba(255, 255, 255, 0.05)';
            el.style.border = '1px solid transparent';
        }
    },

    async save(e) {
        e.preventDefault();

        const provider = this.providerSelect.value;
        const model = provider === 'gemini' ? this.geminiSelect.value : this.customInput.value;

        const config = {
            provider: provider,
            model: model,
            api_key: this.apiKeyInput.value // Only sent if user typed something
        };

        this.saveBtn.disabled = true;
        this.saveBtn.textContent = 'Saving...';
        this.log('Saving configuration...', 'system');

        try {
            const res = await fetch('/api/config/update.php', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(config)
            });
            const data = await res.json();

            if (data.success) {
                alert('Settings saved successfully!');
                this.log('Settings saved to database.', 'success');
                this.apiKeyInput.value = ''; // Clear sensitive input
                // Reload to update status
                this.loadSettings();
            } else {
                alert('Error: ' + data.error);
                this.log(`Save failed: ${data.error}`, 'error');
            }
        } catch (e) {
            console.error('Save failed', e);
            alert('Failed to save settings.');
            this.log(`Save failed: ${e.message}`, 'error');
        } finally {
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'UPDATE PROTOCOLS';
        }
    }
};
