const ChatWidget = {
    init() {
        this.createWidgetUI();
        this.bindEvents();
        this.history = [];
    },

    createWidgetUI() {
        const widget = document.createElement('div');
        widget.id = 'zeon-chat-widget';
        widget.innerHTML = `
            <div id="chat-toggle" class="chat-toggle">
                💬
            </div>
            <div id="chat-window" class="chat-window hidden">
                <div class="chat-header">
                    <span>Zeon7 AI</span>
                    <button id="chat-close">×</button>
                </div>
                <div id="chat-messages" class="chat-messages">
                    <div class="message assistant">
                        Hello! I'm Zeon7. How can I help you today?
                    </div>
                </div>
                <form id="chat-form" class="chat-input-area">
                    <input type="text" id="chat-input" placeholder="Type a message..." required autocomplete="off">
                    <button type="submit">Send</button>
                </form>
            </div>
        `;
        document.body.appendChild(widget);

        // Add Styles dynamically
        const style = document.createElement('style');
        style.textContent = `
            .chat-toggle {
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                width: 60px;
                height: 60px;
                background: var(--accent-primary, #007bff);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                z-index: 1000;
                transition: transform 0.2s;
            }
            .chat-toggle:hover {
                transform: scale(1.1);
            }
            .chat-window {
                position: fixed;
                bottom: 6rem;
                right: 2rem;
                width: 350px;
                height: 500px;
                background: var(--bg-card, #fff);
                border: 1px solid var(--border-subtle, #eee);
                border-radius: 1rem;
                box-shadow: 0 8px 24px rgba(0,0,0,0.15);
                display: flex;
                flex-direction: column;
                z-index: 1000;
                overflow: hidden;
            }
            .chat-window.hidden {
                display: none;
            }
            .chat-header {
                padding: 1rem;
                background: var(--bg-secondary, #f8f9fa);
                border-bottom: 1px solid var(--border-subtle, #eee);
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: bold;
            }
            .chat-header button {
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: var(--text-secondary, #666);
            }
            .chat-messages {
                flex: 1;
                padding: 1rem;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            .message {
                padding: 0.75rem 1rem;
                border-radius: 1rem;
                max-width: 80%;
                line-height: 1.4;
                font-size: 0.9rem;
            }
            .message.user {
                background: var(--accent-primary, #007bff);
                color: white;
                align-self: flex-end;
                border-bottom-right-radius: 0.25rem;
            }
            .message.assistant {
                background: var(--bg-secondary, #f1f1f1);
                color: var(--text-primary, #333);
                align-self: flex-start;
                border-bottom-left-radius: 0.25rem;
            }
            .chat-input-area {
                padding: 1rem;
                border-top: 1px solid var(--border-subtle, #eee);
                display: flex;
                gap: 0.5rem;
            }
            .chat-input-area input {
                flex: 1;
                padding: 0.5rem;
                border: 1px solid var(--border-subtle, #ccc);
                border-radius: 0.5rem;
                outline: none;
            }
            .chat-input-area button {
                padding: 0.5rem 1rem;
                background: var(--accent-primary, #007bff);
                color: white;
                border: none;
                border-radius: 0.5rem;
                cursor: pointer;
            }
            .chat-input-area button:disabled {
                opacity: 0.7;
                cursor: not-allowed;
            }
            @media (max-width: 480px) {
                .chat-window {
                    width: 100%;
                    height: 100%;
                    bottom: 0;
                    right: 0;
                    border-radius: 0;
                }
            }
        `;
        document.head.appendChild(style);
    },

    bindEvents() {
        this.toggleBtn = document.getElementById('chat-toggle');
        this.window = document.getElementById('chat-window');
        this.closeBtn = document.getElementById('chat-close');
        this.form = document.getElementById('chat-form');
        this.input = document.getElementById('chat-input');
        this.messagesContainer = document.getElementById('chat-messages');

        this.toggleBtn.addEventListener('click', () => this.toggleChat());
        this.closeBtn.addEventListener('click', () => this.toggleChat());
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    },

    toggleChat() {
        this.window.classList.toggle('hidden');
        if (!this.window.classList.contains('hidden')) {
            this.input.focus();
        }
    },

    async handleSubmit(e) {
        e.preventDefault();
        const message = this.input.value.trim();
        if (!message) return;

        this.appendMessage('user', message);
        this.input.value = '';
        this.setLoading(true);

        try {
            const res = await fetch('/api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    history: this.history
                })
            });
            const data = await res.json();

            if (data.success) {
                this.appendMessage('assistant', data.reply);
                // Update history (simple version)
                this.history.push({ role: 'user', content: message });
                this.history.push({ role: 'assistant', content: data.reply });
                // Limit history to last 10 messages
                if (this.history.length > 10) this.history = this.history.slice(-10);
            } else {
                this.appendMessage('assistant', 'Sorry, I encountered an error: ' + (data.error || 'Unknown error'));
            }
        } catch (e) {
            console.error('Chat error', e);
            this.appendMessage('assistant', 'Sorry, I am having trouble connecting right now.');
        } finally {
            this.setLoading(false);
        }
    },

    appendMessage(role, text) {
        const div = document.createElement('div');
        div.className = `message ${role}`;
        div.textContent = text;
        this.messagesContainer.appendChild(div);
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    },

    setLoading(isLoading) {
        const btn = this.form.querySelector('button');
        btn.disabled = isLoading;
        btn.textContent = isLoading ? '...' : 'Send';
    }
};

document.addEventListener('DOMContentLoaded', () => ChatWidget.init());
