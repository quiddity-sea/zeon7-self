const Settings = {
    async init() {
        this.form = document.getElementById('settingsForm');
        this.providerSelect = document.getElementById('provider');
        this.modelInput = document.getElementById('model');
        this.apiKeyInput = document.getElementById('apiKey');
        this.saveBtn = document.getElementById('saveBtn');

        this.form.addEventListener('submit', (e) => this.save(e));

        await this.loadSettings();
    },

    async loadSettings() {
        try {
            const res = await fetch('/api/config/get.php');
            const data = await res.json();

            if (data.success) {
                this.providerSelect.value = data.config.provider;
                this.modelInput.value = data.config.model || '';
                // API key is not returned for security
            } else {
                console.error('Failed to load settings', data.error);
            }
        } catch (e) {
            console.error('Failed to load settings', e);
        }
    },

    async save(e) {
        e.preventDefault();

        const config = {
            provider: this.providerSelect.value,
            model: this.modelInput.value,
            api_key: this.apiKeyInput.value // Only sent if user typed something
        };

        this.saveBtn.disabled = true;
        this.saveBtn.textContent = 'Saving...';

        try {
            const res = await fetch('/api/config/update.php', {
                method: 'POST',
                headers: App.getHeaders(),
                body: JSON.stringify(config)
            });
            const data = await res.json();

            if (data.success) {
                alert('Settings saved successfully!');
                this.apiKeyInput.value = ''; // Clear sensitive input
            } else {
                alert('Error: ' + data.error);
            }
        } catch (e) {
            console.error('Save failed', e);
            alert('Failed to save settings.');
        } finally {
            this.saveBtn.disabled = false;
            this.saveBtn.textContent = 'Save Changes';
        }
    }
};
