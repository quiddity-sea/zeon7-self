/**
 * Agent Bar — collapsed input strip at bottom of screen.
 * Expands to full popup on click. 30-40 word limit in collapsed mode.
 */
export class AgentBar {
    constructor() {
        this.bar = document.getElementById('agent-bar');
        this.input = document.getElementById('agent-input');
        this.wordCount = document.getElementById('agent-word-count');
        this.lastReply = document.getElementById('agent-last-reply');
        this.popup = document.getElementById('agent-popup');
        this.popupInput = document.getElementById('popup-input');
        this.chatHistory = document.getElementById('chat-history');
        this.closeBtn = document.getElementById('close-popup');
        this.sendBtn = document.getElementById('popup-send');
        this.onCommand = null; // Callback: (reply, eyeCommand) => {}

        this.history = [];
        this.maxBarWords = 40;

        this.initEvents();
    }

    initEvents() {
        // Word count enforcement on bar input
        this.input.addEventListener('input', () => {
            const words = this.input.value.trim().split(/\s+/).filter(w => w.length > 0);
            const count = words.length;
            this.wordCount.textContent = `${count}/${this.maxBarWords}`;
            if (count > this.maxBarWords) {
                this.wordCount.style.color = '#f43f5e';
            } else {
                this.wordCount.style.color = '';
            }
        });

        // Submit on Enter (bar)
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const words = this.input.value.trim().split(/\s+/).filter(w => w.length > 0);
                if (words.length > 0 && words.length <= this.maxBarWords) {
                    this.sendMessage(this.input.value.trim());
                    this.input.value = '';
                    this.wordCount.textContent = '0/40';
                }
            }
        });

        // Click bar to expand popup
        this.bar.addEventListener('click', (e) => {
            if (e.target !== this.input) {
                this.openPopup();
            }
        });

        // Close popup
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.closePopup());
        }

        // Submit on Enter (popup)
        if (this.popupInput) {
            this.popupInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const msg = this.popupInput.value.trim();
                    if (msg) {
                        this.sendMessage(msg);
                        this.popupInput.value = '';
                    }
                }
            });
        }

        // Send button (popup)
        if (this.sendBtn) {
            this.sendBtn.addEventListener('click', () => {
                const msg = this.popupInput.value.trim();
                if (msg) {
                    this.sendMessage(msg);
                    this.popupInput.value = '';
                }
            });
        }

        // Escape to close popup
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.popup.classList.contains('hidden')) {
                this.closePopup();
            }
        });
    }

    openPopup() {
        this.popup.classList.remove('hidden');
        this.popupInput.focus();
    }

    closePopup() {
        this.popup.classList.add('hidden');
        this.input.focus();
    }

    /** Send a message to Otec via the agent command API. */
    async sendMessage(text) {
        // Add user message to chat
        this.addChatMessage(text, 'user');

        try {
            const cameraState = window.eyeGlobe ? window.eyeGlobe.getCameraState() : null;
            const activeLayers = window.eyeGlobe ? Array.from(window.eyeGlobe.activeLayers) : [];

            const res = await fetch('api/agent/command.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Eye-Token': window.EYE_CONFIG.sessionToken
                },
                body: JSON.stringify({
                    message: text,
                    session_token: window.EYE_CONFIG.sessionToken,
                    camera_state: cameraState,
                    active_layers: activeLayers
                })
            });

            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                this.addChatMessage(errData.error || 'Command failed.', 'agent');
                return;
            }

            const data = await res.json();
            const reply = data.reply || 'Acknowledged.';
            const eyeCommand = data.eye_command || null;

            // Display reply
            this.addChatMessage(reply, 'agent');
            this.lastReply.textContent = reply;

            // Execute globe command
            if (eyeCommand && this.onCommand) {
                this.onCommand(reply, eyeCommand);
            }

        } catch (e) {
            this.addChatMessage('Connection failed.', 'agent');
            console.error('[Eye] Agent command failed:', e);
        }
    }

    addChatMessage(text, sender) {
        const div = document.createElement('div');
        div.className = `chat-msg ${sender}`;
        div.textContent = text;
        this.chatHistory.appendChild(div);
        this.chatHistory.scrollTop = this.chatHistory.scrollHeight;
        this.history.push({ text, sender, time: Date.now() });
    }
}
