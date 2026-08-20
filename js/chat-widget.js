/**
 * Zeon7 Chat Widget Controller
 * Pure Vanilla JS + GSAP animations + Markdown Rendering + Thinking Toggle
 */

const ChatWidget = {
    isOpen: false,
    history: [],
    isThinking: false,
    sessionId: null,

    init() {
        this.sessionId = ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
        this.render();
        this.bindEvents();
        this.fetchInitialStatus();
    },

    async fetchInitialStatus() {
        try {
            const res = await fetch('/api/chat.php');
            const data = await res.json();
            if (data.success && typeof data.think !== 'undefined') {
                this.isThinking = Boolean(data.think);
                this.updateThinkBadge();
            }
        } catch (e) {
            console.warn('Could not fetch initial chat status', e);
        }
    },

    render() {
        if (document.getElementById('zeon7-chat-widget')) return;

        const widgetHtml = `
            <div id="zeon7-chat-widget">
                <!-- Floating Toggle Button -->
                <button id="chat-toggle" class="chat-launcher" aria-label="Open Zeon7 AI Terminal">
                    <span class="pulse-ring"></span>
                    <span class="icon">💬</span>
                </button>

                <!-- Chat Window -->
                <div id="chat-window" class="chat-window hidden">
                    <!-- Header -->
                    <div class="chat-header">
                        <div class="header-info">
                            <span class="status-dot"></span>
                            <span class="title">ZEON7 NEURAL LINK</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <button type="button" id="chat-think-toggle" class="think-badge think-off" title="Toggle Reasoning / Think Mode">
                                [ THINKING OFF ]
                            </button>
                            <button id="chat-close" class="chat-close-btn" aria-label="Close Chat">✕</button>
                        </div>
                    </div>

                    <!-- Messages Stream -->
                    <div id="chat-messages" class="chat-messages">
                        <div class="message assistant">
                            <p><strong>[SYSTEM SYNCHRONISED]</strong></p>
                            <p>Zeon7 neural channel open. Awaiting inquiry.</p>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <form id="chat-form" class="chat-input-area">
                        <input type="text" id="chat-input" placeholder="Transmit query..." autocomplete="off" required>
                        <button type="submit" id="chat-send">EXEC</button>
                    </form>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', widgetHtml);
        this.injectStyles();
    },

    injectStyles() {
        const style = document.createElement('style');
        style.textContent = `
            #zeon7-chat-widget {
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                z-index: 999999;
                font-family: var(--font-sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);
            }
            .chat-launcher {
                position: relative;
                width: 54px;
                height: 54px;
                border-radius: 50%;
                background: #030609;
                border: 2px solid var(--color-cyan, #22d3ee);
                color: var(--color-cyan, #22d3ee);
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.35rem;
                box-shadow: 0 0 20px rgba(34, 211, 238, 0.4);
                transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            .chat-launcher:hover {
                transform: scale(1.08);
                box-shadow: 0 0 28px rgba(34, 211, 238, 0.7);
            }
            .pulse-ring {
                position: absolute;
                inset: -6px;
                border-radius: 50%;
                border: 1px solid rgba(34, 211, 238, 0.4);
                animation: pulseGlow 2.5s infinite;
                pointer-events: none;
            }
            @keyframes pulseGlow {
                0% { transform: scale(0.95); opacity: 0.8; }
                50% { transform: scale(1.15); opacity: 0; }
                100% { transform: scale(0.95); opacity: 0; }
            }
            .chat-window {
                position: absolute;
                bottom: 70px;
                right: 0;
                width: 420px;
                height: 540px;
                max-width: calc(100vw - 2rem);
                max-height: calc(100vh - 100px);
                background: #070b10;
                border: 1px solid rgba(34, 211, 238, 0.4);
                border-radius: 4px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.85), 0 0 20px rgba(34, 211, 238, 0.15);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                transition: opacity 0.2s ease, transform 0.2s ease;
            }
            [data-theme="light"] .chat-window {
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
            }
            .chat-window.hidden {
                display: none !important;
            }
            .chat-header {
                padding: 0.65rem 0.85rem;
                background: #030609;
                border-bottom: 1px solid rgba(34, 211, 238, 0.25);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            [data-theme="light"] .chat-header {
                background: #f1f5f9;
                border-bottom: 1px solid #e2e8f0;
            }
            .header-info {
                display: flex;
                align-items: center;
                gap: 0.4rem;
            }
            .status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #00ff41;
                box-shadow: 0 0 8px #00ff41;
            }
            .chat-header .title {
                font-family: var(--font-mono, monospace);
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                color: var(--color-cyan, #22d3ee);
            }
            [data-theme="light"] .chat-header .title {
                color: #0284c7;
            }
            .think-badge {
                padding: 0.2rem 0.5rem;
                border-radius: 3px;
                font-family: var(--font-mono, monospace);
                font-size: 0.65rem;
                font-weight: 800;
                letter-spacing: 0.05em;
                cursor: pointer;
                transition: all 0.2s ease;
                user-select: none;
                line-height: 1.2;
            }
            .think-badge.think-on {
                background: rgba(0, 255, 65, 0.15);
                border: 1px solid #00ff41;
                color: #00ff41;
                box-shadow: 0 0 8px rgba(0, 255, 65, 0.35);
            }
            .think-badge.think-on:hover {
                background: rgba(0, 255, 65, 0.3);
                box-shadow: 0 0 12px rgba(0, 255, 65, 0.6);
            }
            .think-badge.think-off {
                background: rgba(244, 63, 94, 0.15);
                border: 1px solid #f43f5e;
                color: #f43f5e;
                box-shadow: 0 0 8px rgba(244, 63, 94, 0.25);
            }
            .think-badge.think-off:hover {
                background: rgba(244, 63, 94, 0.3);
                box-shadow: 0 0 12px rgba(244, 63, 94, 0.5);
            }
            .chat-close-btn {
                background: none;
                border: none;
                color: var(--text-muted, #94a3b8);
                cursor: pointer;
                font-size: 1rem;
                line-height: 1;
                padding: 0.2rem;
            }
            .chat-close-btn:hover {
                color: #f43f5e;
            }
            .chat-messages {
                flex: 1;
                padding: 1.25rem;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 1rem;
                background: #030609 !important;
            }
            [data-theme="light"] .chat-messages {
                background: #f8fafc !important;
            }
            .message {
                padding: 0.85rem 1.1rem;
                border-radius: 4px;
                max-width: 88%;
                line-height: 1.5;
                font-size: 0.9rem;
                word-break: break-word;
            }
            .message p {
                margin: 0 0 0.5rem 0;
            }
            .message p:last-child {
                margin-bottom: 0;
            }
            .message strong {
                font-weight: 700;
                color: inherit;
            }
            .message ul, .message ol {
                margin: 0.35rem 0;
                padding-left: 1.25rem;
            }
            .message li {
                margin-bottom: 0.25rem;
            }
            /* Dark Mode AI Assistant */
            .message.assistant {
                background: #0f1926 !important;
                color: #ffffff !important;
                border: 1px solid rgba(34, 211, 238, 0.35) !important;
                border-left: 4px solid #22d3ee !important;
                align-self: flex-start;
                box-shadow: 0 4px 15px rgba(0,0,0,0.5);
                font-weight: 500;
            }
            /* Light Mode AI Assistant */
            [data-theme="light"] .message.assistant {
                background: #ffffff !important;
                color: #0f172a !important;
                border: 1px solid #cbd5e1 !important;
                border-left: 4px solid #0284c7 !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.06);
                font-weight: 500;
            }
            /* User Message */
            .message.user {
                background: rgba(0, 255, 65, 0.15) !important;
                color: #00ff41 !important;
                border: 1px solid rgba(0, 255, 65, 0.35) !important;
                align-self: flex-end;
                font-family: var(--font-mono, monospace);
                font-size: 0.85rem;
                font-weight: 600;
            }
            [data-theme="light"] .message.user {
                background: #0284c7 !important;
                color: #ffffff !important;
                border: 1px solid #0284c7 !important;
                font-weight: 600;
            }
            .message.system-notice {
                align-self: center;
                background: rgba(255, 255, 255, 0.04);
                border: 1px dashed rgba(34, 211, 238, 0.3);
                font-family: var(--font-mono, monospace);
                font-size: 0.72rem;
                padding: 0.4rem 0.8rem;
                border-radius: 2px;
                color: var(--color-cyan, #22d3ee);
                max-width: 95%;
                text-align: center;
            }
            .chat-input-area {
                padding: 0.85rem 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                display: flex;
                gap: 0.5rem;
                background: #070b10;
            }
            [data-theme="light"] .chat-input-area {
                background: #ffffff;
                border-top: 1px solid #e2e8f0;
            }
            .chat-input-area input {
                flex: 1;
                padding: 0.65rem 0.85rem;
                background: #030609 !important;
                color: #ffffff !important;
                border: 1px solid rgba(34, 211, 238, 0.3) !important;
                border-radius: 2px;
                outline: none;
                font-family: var(--font-mono, monospace);
                font-size: 0.85rem;
            }
            [data-theme="light"] .chat-input-area input {
                background: #f8fafc !important;
                color: #0f172a !important;
                border: 1px solid #cbd5e1 !important;
            }
            .chat-input-area input:focus {
                border-color: #22d3ee !important;
                box-shadow: 0 0 10px rgba(34, 211, 238, 0.25);
            }
            [data-theme="light"] .chat-input-area input:focus {
                border-color: #0284c7 !important;
                box-shadow: 0 0 8px rgba(2, 132, 199, 0.2);
            }
            .chat-input-area button {
                padding: 0.65rem 1.1rem;
                background: rgba(34, 211, 238, 0.15);
                color: #22d3ee;
                border: 1px solid #22d3ee;
                border-radius: 2px;
                cursor: pointer;
                font-family: var(--font-mono, monospace);
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                transition: all 0.2s;
            }
            [data-theme="light"] .chat-input-area button {
                background: #0284c7;
                color: #ffffff;
                border-color: #0284c7;
            }
            .chat-input-area button:hover {
                background: #22d3ee;
                color: #000;
            }
            [data-theme="light"] .chat-input-area button:hover {
                background: #0369a1;
                color: #ffffff;
            }
            .chat-input-area button:disabled {
                opacity: 0.5;
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
        this.thinkToggleBtn = document.getElementById('chat-think-toggle');

        this.toggleBtn?.addEventListener('click', () => this.toggleChat());
        this.closeBtn?.addEventListener('click', () => this.toggleChat());
        this.form?.addEventListener('submit', (e) => this.handleSubmit(e));
        
        if (this.thinkToggleBtn) {
            this.thinkToggleBtn.addEventListener('click', () => this.toggleThinkMode());
        }
    },

    toggleThinkMode() {
        this.isThinking = !this.isThinking;
        this.updateThinkBadge();
        
        const modeLabel = this.isThinking ? 'THINKING: ON (Deep Reasoning Enabled)' : 'THINKING: OFF (--think=false Enforced)';
        this.appendSystemNotice(`[MODE TOGGLE] ${modeLabel}`);
    },

    updateThinkBadge() {
        if (!this.thinkToggleBtn) return;
        if (this.isThinking) {
            this.thinkToggleBtn.className = 'think-badge think-on';
            this.thinkToggleBtn.textContent = '[ THINKING ON ]';
            this.thinkToggleBtn.title = 'Reasoning scratchpad active. Click to disable.';
        } else {
            this.thinkToggleBtn.className = 'think-badge think-off';
            this.thinkToggleBtn.textContent = '[ THINKING OFF ]';
            this.thinkToggleBtn.title = 'Fast direct response active (--think=false). Click to enable reasoning.';
        }
    },

    appendSystemNotice(text) {
        const div = document.createElement('div');
        div.className = 'message system-notice';
        div.textContent = text;
        this.messagesContainer.appendChild(div);
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        
        if (typeof gsap !== 'undefined') {
            gsap.from(div, { opacity: 0, scale: 0.95, duration: 0.2, ease: 'power1.out' });
        }
    },

    toggleChat() {
        this.window.classList.toggle('hidden');
        if (!this.window.classList.contains('hidden')) {
            this.input.focus();
            if (typeof gsap !== 'undefined') {
                gsap.fromTo(this.window, 
                    { scale: 0.9, opacity: 0, y: 20 },
                    { scale: 1, opacity: 1, y: 0, duration: 0.3, ease: 'back.out(1.2)' }
                );
            }
        }
    },

    formatMarkdown(text) {
        if (!text) return '';
        if (typeof marked !== 'undefined' && typeof marked.parse === 'function') {
            return marked.parse(text);
        }
        
        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Headers
        html = html.replace(/^### (.*$)/gim, '<h4 style="margin: 0.4rem 0 0.2rem; color: #22d3ee; font-size: 0.95rem;">$1</h4>');
        html = html.replace(/^## (.*$)/gim, '<h3 style="margin: 0.5rem 0 0.25rem; color: #22d3ee; font-size: 1.05rem;">$1</h3>');
        html = html.replace(/^# (.*$)/gim, '<h2 style="margin: 0.6rem 0 0.3rem; color: #22d3ee; font-size: 1.15rem;">$1</h2>');

        // Bold & Italic
        html = html.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

        // Code blocks & inline code
        html = html.replace(/```([\s\S]*?)```/g, '<pre style="background: rgba(0,0,0,0.5); padding: 0.5rem; border-radius: 4px; overflow-x: auto;"><code>$1</code></pre>');
        html = html.replace(/`([^`]+)`/g, '<code style="background: rgba(255,255,255,0.1); padding: 0.1rem 0.3rem; border-radius: 2px;">$1</code>');

        // Bullet points
        html = html.replace(/^\* (.*$)/gim, '<li style="margin-left: 1.2rem;">$1</li>');
        html = html.replace(/^- (.*$)/gim, '<li style="margin-left: 1.2rem;">$1</li>');

        // Paragraphs
        html = html.replace(/\n\n+/g, '</p><p>');
        html = html.replace(/\n/g, '<br>');

        return `<p>${html}</p>`;
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
                    session_id: this.sessionId,
                    history: this.history,
                    think: this.isThinking
                })
            });
            const data = await res.json();

            if (data.success) {
                this.appendMessage('assistant', data.reply);
                this.history.push({ role: 'user', content: message });
                this.history.push({ role: 'assistant', content: data.reply });
                if (this.history.length > 10) this.history = this.history.slice(-10);
            } else {
                this.appendMessage('assistant', 'System Notice: ' + (data.error || 'Connection timeout.'));
            }
        } catch (e) {
            console.error('Chat error', e);
            this.appendMessage('assistant', 'System Notice: Neural streaming channel unreachable.');
        } finally {
            this.setLoading(false);
        }
    },

    appendMessage(role, text) {
        const div = document.createElement('div');
        div.className = `message ${role}`;
        if (role === 'assistant') {
            div.innerHTML = this.formatMarkdown(text);
        } else {
            div.textContent = text;
        }
        this.messagesContainer.appendChild(div);
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        
        if (typeof gsap !== 'undefined') {
            gsap.from(div, { opacity: 0, y: 10, duration: 0.25, ease: 'power2.out' });
        }
    },

    setLoading(isLoading) {
        const btn = this.form.querySelector('button');
        if (btn) {
            btn.disabled = isLoading;
            btn.textContent = isLoading ? '...' : 'EXEC';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => ChatWidget.init());