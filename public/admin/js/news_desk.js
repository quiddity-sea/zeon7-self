/**
 * Zeon7 Cockpit Logic
 * Fixed: Aligned selectors with Final HTML structure
 */
const NewsDesk = {
    init() {
        console.log('Zeon7 Cockpit: Initializing...');
        this.cacheDOM();
        this.bindEvents();
        this.initTabs();
    },

    cacheDOM() {
        this.dom = {
            // Navigation
            tabs: document.querySelectorAll('.tab'),
            views: document.querySelectorAll('.controls'),

            // Chat
            chatStream: document.getElementById('chatStream'),
            chatInput: document.getElementById('chatInput'),
            sendBtn: document.getElementById('sendBtn'),

            // Generation
            generateBtn: document.getElementById('generateBtn'),
            generatedContent: document.getElementById('generatedContent'),
            resultsContainer: document.getElementById('resultsContainer'),

            // Scanner
            scanBtn: document.getElementById('scanBtn'),
            leadContainer: document.getElementById('leadContainer'),

            // Brain (Upload)
            brainDropzone: document.getElementById('brainDropzone'),
            brainFileList: document.getElementById('brainFileList'),

            // Memory (Logs)
            memoryLogContainer: document.getElementById('memoryLogContainer'),

            // Inputs
            sliders: {
                satire: document.getElementById('toneSatire'),
                hope: document.getElementById('toneHope')
            }
        };
    },

    bindEvents() {
        // Tab Switching
        if (this.dom.tabs) {
            this.dom.tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    e.preventDefault();
                    const target = e.target.closest('.tab').dataset.target;
                    this.switchTab(target);
                });
            });
        }

        // Chat
        if (this.dom.sendBtn) {
            this.dom.sendBtn.addEventListener('click', () => this.sendMessage());
        }

        if (this.dom.chatInput) {
            this.dom.chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }

        // Generate Suite
        if (this.dom.generateBtn) {
            this.dom.generateBtn.addEventListener('click', () => this.generateSuite());
        }

        // News Scan
        if (this.dom.scanBtn) {
            this.dom.scanBtn.addEventListener('click', () => this.scanNews());
        }

        // Brain: Drag & Drop
        if (this.dom.brainDropzone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                this.dom.brainDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            this.dom.brainDropzone.addEventListener('dragover', () => {
                this.dom.brainDropzone.style.borderColor = 'var(--cyan)';
                this.dom.brainDropzone.style.background = 'rgba(77, 238, 234, 0.1)';
            });

            this.dom.brainDropzone.addEventListener('dragleave', () => {
                this.dom.brainDropzone.style.borderColor = '';
                this.dom.brainDropzone.style.background = '';
            });

            this.dom.brainDropzone.addEventListener('drop', (e) => {
                this.dom.brainDropzone.style.borderColor = '';
                this.dom.brainDropzone.style.background = '';
                const files = e.dataTransfer.files;
                this.handleFiles(files);
            });
        }
    },

    initTabs() {
        this.switchTab('produce');
    },

    switchTab(targetId) {
        if (!targetId) return;

        // 1. Update Buttons
        this.dom.tabs.forEach(t => {
            const isTarget = t.dataset.target === targetId;
            t.classList.toggle('active', isTarget);
        });

        // 2. Update Views
        let viewFound = false;
        this.dom.views.forEach(v => {
            const isTarget = v.id === `view-${targetId}`;
            v.classList.toggle('active', isTarget);
            v.style.display = isTarget ? 'block' : 'none';
            if (isTarget) viewFound = true;
        });

        if (!viewFound) console.warn(`No view found for target: view-${targetId}`);

        // 3. Load Data if needed
        if (targetId === 'memory') {
            this.loadMemoryLogs();
        } else if (targetId === 'brain') {
            this.loadBrainFiles();
        }
    },

    async sendMessage() {
        const text = this.dom.chatInput.value.trim();
        if (!text) return;

        this.appendMessage('user', text);
        this.dom.chatInput.value = '';

        const payload = {
            message: text,
            context: {
                tone: {
                    satire: this.dom.sliders.satire.value,
                    hope: this.dom.sliders.hope.value
                }
            }
        };

        try {
            const headers = (typeof App !== 'undefined' && App.getHeaders)
                ? App.getHeaders()
                : { 'Content-Type': 'application/json' };

            const res = await fetch('/api/ai/chat.php', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                this.appendMessage('ai', data.reply);
            } else {
                this.appendMessage('ai', 'Error: ' + (data.error || 'Unknown error'));
            }
        } catch (e) {
            console.error(e);
            setTimeout(() => {
                this.appendMessage('ai', "I'm processing that request. (API Connection Simulated)");
            }, 500);
        }
    },

    appendMessage(role, text) {
        const div = document.createElement('div');
        div.className = `msg ${role}`;
        div.innerHTML = `
            <div>
                <span class="label">ZEON7 // ${role === 'user' ? 'USER' : 'SYSTEM'}</span>
                <div class="content">${text}</div>
            </div>
        `;
        this.dom.chatStream.appendChild(div);
        this.dom.chatStream.scrollTop = this.dom.chatStream.scrollHeight;
    },

    // --- GENERATION LOGIC ---
    async generateSuite() {
        const btn = this.dom.generateBtn;
        const container = this.dom.generatedContent;
        const results = this.dom.resultsContainer;

        // UI State: Loading
        btn.disabled = true;
        btn.innerHTML = 'GENERATING... <span class="blink">_</span>';
        container.style.display = 'block';
        results.innerHTML = '<div style="padding:2rem; text-align:center; color:var(--text-muted);">Zeon7 is drafting content based on active context...</div>';

        try {
            // Simulate API call (Replace with actual endpoint /api/ai/generate.php later)
            // const res = await fetch('/api/ai/generate.php', { method: 'POST', ... });

            // Simulation delay
            await new Promise(r => setTimeout(r, 2000));

            // Simulated Data
            const mockData = {
                success: true,
                posts: [
                    { type: 'Article', title: 'The Signal in the Noise', content: 'In a world saturated with static, finding the true signal becomes an act of rebellion...' },
                    { type: 'Social', title: 'Tweet', content: 'Static is just a frequency you haven\'t tuned out yet. #Zeon7 #SurvivalMonday' },
                    { type: 'Prompt', title: 'Image Gen', content: 'Cyberpunk city street, rain slicked, neon reflection, solitary figure tuning a radio device, cinematic lighting.' }
                ]
            };

            if (mockData.success) {
                this.renderResults(mockData.posts);
                this.appendMessage('ai', "Content suite generated. Review the output below.");
            }

        } catch (e) {
            console.error(e);
            results.innerHTML = '<div style="color:var(--orange)">Generation Failed. System Error.</div>';
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'GENERATE SUITE';
        }
    },

    renderResults(posts) {
        const html = posts.map(post => `
            <div class="post-card" style="background:rgba(255,255,255,0.03); border:1px solid var(--border-hairline); padding:1rem; margin-bottom:1rem; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                    <span style="color:var(--cyan); font-weight:700; font-size:0.8rem; text-transform:uppercase;">${post.type}</span>
                    <button class="btn-copy" style="background:none; border:none; color:var(--text-muted); cursor:pointer;">[COPY]</button>
                </div>
                <div style="font-family:var(--font-head); font-weight:700; margin-bottom:0.5rem;">${post.title}</div>
                <div style="font-family:var(--font-body); font-size:0.9rem; line-height:1.5; color:var(--text-main); opacity:0.9;">${post.content}</div>
            </div>
        `).join('');
        this.dom.resultsContainer.innerHTML = html;
    },

    async scanNews() {
        this.dom.leadContainer.innerHTML = '<div class="lead-card" style="opacity:0.5">Scanning frequencies...</div>';

        try {
            const res = await fetch('/api/ai/scan.php');
            const data = await res.json();

            if (data.success) {
                this.dom.leadContainer.innerHTML = `
                    <div class="lead-card">
                        <div class="lead-meta">INTELLIGENCE REPORT</div>
                        <div class="lead-head" style="font-size:0.9rem; white-space: pre-wrap;">${data.leads}</div>
                    </div>
                `;
                this.appendMessage('ai', "Scan complete. Intelligence report acquired.");
            } else {
                this.dom.leadContainer.innerHTML = '<div class="lead-card" style="color:var(--orange)">Scan Failed</div>';
            }
        } catch (e) {
            console.error(e);
            this.dom.leadContainer.innerHTML = '<div class="lead-card" style="color:var(--orange)">Connection Error</div>';
        }
    },

    // --- BRAIN LOGIC ---
    async handleFiles(files) {
        if (!files.length) return;

        const file = files[0]; // Handle one for now
        this.dom.brainFileList.innerHTML = `<div>Uploading: ${file.name}...</div>`;

        const isPublic = document.getElementById('brainPublicFlag').checked;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('is_public', isPublic ? '1' : '0');

        try {
            const res = await fetch('/api/knowledge/upload.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                const publicBadge = isPublic ? ' <span style="color:var(--cyan); border:1px solid var(--cyan); padding:0 4px; font-size:0.7rem;">PUBLIC</span>' : '';
                this.dom.brainFileList.innerHTML = `<div style="color:var(--cyan)">✓ ${file.name}${publicBadge} ingested.</div>`;
                this.appendMessage('ai', `I have absorbed new knowledge: ${file.name}`);
                this.loadBrainFiles(); // Refresh list
            } else {
                this.dom.brainFileList.innerHTML = `<div style="color:var(--orange)">✗ Error: ${data.error}</div>`;
            }
        } catch (e) {
            this.dom.brainFileList.innerHTML = `<div style="color:var(--orange)">✗ Upload Failed</div>`;
        }
    },

    async loadBrainFiles() {
        this.dom.brainFileList.innerHTML = '<div style="color:var(--text-muted)">Scanning knowledge base...</div>';
        try {
            const res = await fetch('/api/knowledge/list.php');
            const data = await res.json();

            if (data.success && data.files.length > 0) {
                this.dom.brainFileList.innerHTML = data.files.map(f => `
                    <div style="padding:0.5rem 0; border-bottom:1px solid rgba(255,255,255,0.05); display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:var(--cyan); font-family:var(--font-ui); font-size:0.9rem;">${f.filename}</span>
                        <span style="color:var(--text-muted); font-size:0.75rem;">${(f.size / 1024).toFixed(1)} KB</span>
                    </div>
                `).join('');
            } else {
                this.dom.brainFileList.innerHTML = '<div style="color:var(--text-muted)">No knowledge files found.</div>';
            }
        } catch (e) {
            console.error(e);
            this.dom.brainFileList.innerHTML = '<div style="color:var(--orange)">Failed to load file list.</div>';
        }
    },

    // --- MEMORY LOGIC ---
    async loadMemoryLogs() {
        this.dom.memoryLogContainer.innerHTML = 'Loading memory banks...';

        try {
            // Use Lore API as proxy for memory logs
            const res = await fetch('/api/lore/all.php');
            const data = await res.json();

            if (data.success && data.lore) {
                const html = data.lore.map(item => `
                    <div style="padding:1rem; border-bottom:1px solid rgba(255,255,255,0.05);">
                        <div style="color:var(--cyan); font-weight:700; margin-bottom:0.3rem;">${item.key}</div>
                        <div style="color:var(--text-main); opacity:0.8;">${item.value.substring(0, 100)}${item.value.length > 100 ? '...' : ''}</div>
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.5rem;">UPDATED: ${item.updated_at || 'Unknown'}</div>
                    </div>
                `).join('');
                this.dom.memoryLogContainer.innerHTML = html || 'Memory banks empty.';
            } else {
                this.dom.memoryLogContainer.innerHTML = 'No memory traces found.';
            }
        } catch (e) {
            this.dom.memoryLogContainer.innerHTML = 'Failed to access memory banks.';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => NewsDesk.init());