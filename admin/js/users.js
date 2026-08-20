/**
 * Zeon7 Operator Matrix — Users Manager
 * Handles user CRUD, modal pop-ins, and IP telemetry record removal.
 */

const UsersManager = {
    users: [],
    currentEditingUser: null,

    async init() {
        this.listContainer = document.getElementById('userList');
        this.modal = document.getElementById('userModal');
        this.form = document.getElementById('userForm');

        // Form fields
        this.idInput = document.getElementById('userId');
        this.usernameInput = document.getElementById('userUsername');
        this.emailInput = document.getElementById('userEmail');
        this.firstNameInput = document.getElementById('userFirstName');
        this.lastNameInput = document.getElementById('userLastName');
        this.locationInput = document.getElementById('userLocation');
        this.passwordInput = document.getElementById('userPassword');
        this.isPrimeInput = document.getElementById('userIsPrime');
        this.passwordLabel = document.getElementById('passwordLabel');
        this.passwordHelp = document.getElementById('passwordHelp');
        this.modalTitle = document.getElementById('modalTitle');
        this.ipSection = document.getElementById('ipTelemetrySection');
        this.ipListContainer = document.getElementById('ipListContainer');

        // Event listeners
        document.getElementById('addUserBtn')?.addEventListener('click', () => this.openModal());
        document.getElementById('modalCloseX')?.addEventListener('click', () => this.closeModal());
        document.getElementById('modalCancelBtn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('clearAllIpsBtn')?.addEventListener('click', () => this.handleClearAllIps());
        this.form?.addEventListener('submit', (e) => this.handleSubmit(e));

        // Close on clicking backdrop
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeModal();
        });

        await this.loadUsers();
    },

    async loadUsers() {
        try {
            const res = await fetch('/api/users/all.php', {
                headers: (typeof App !== 'undefined' && App.getHeaders) ? App.getHeaders() : {}
            });
            const data = await res.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to query operators');
            }

            this.users = data.users || [];
            this.renderUserTable();
        } catch (err) {
            console.error('Failed to load users:', err);
            this.listContainer.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 2.5rem; text-align: center; color: var(--color-coral); font-family: var(--font-mono);">
                        ERROR: ${this.escapeHtml(err.message || 'Failed to connect to database')}
                    </td>
                </tr>
            `;
        }
    },

    renderUserTable() {
        if (this.users.length === 0) {
            this.listContainer.innerHTML = `
                <tr>
                    <td colspan="6" style="padding: 2.5rem; text-align: center; color: var(--text-muted); font-family: var(--font-mono);">
                        NO OPERATOR PROFILES REGISTERED.
                    </td>
                </tr>
            `;
            return;
        }

        this.listContainer.innerHTML = this.users.map((u, index) => {
            const fullName = [u.first_name, u.last_name].filter(Boolean).join(' ') || '<span style="color:var(--text-muted); font-style:italic;">No Name</span>';
            const location = u.location ? `<small style="color:var(--text-muted); display:block;">📍 ${this.escapeHtml(u.location)}</small>` : '';
            const email = u.email ? `<a href="mailto:${this.escapeHtml(u.email)}" style="color:var(--color-cyan); text-decoration:none;">${this.escapeHtml(u.email)}</a>` : '<span style="color:var(--text-muted);">Unset</span>';
            
            const isPrime = u.is_prime_user == 1;
            const accessBadge = isPrime 
                ? '<span class="hud-badge gold" style="padding:3px 8px; font-size:0.7rem; font-weight:bold;">PRIME OPERATOR</span>'
                : '<span class="hud-badge" style="padding:3px 8px; font-size:0.7rem; color:var(--text-muted); border:1px solid rgba(255,255,255,0.15);">STANDARD</span>';

            // Parse latest recorded IP
            const ipHistory = u.ip_history || [];
            let ipDisplay = '<span style="color:var(--text-muted); font-family:var(--font-mono); font-size:0.75rem;">No IP logs</span>';
            if (ipHistory.length > 0) {
                const latest = ipHistory[0];
                const latestIp = typeof latest === 'object' ? latest.ip : latest;
                const ipCount = ipHistory.length;
                ipDisplay = `
                    <span style="font-family:var(--font-mono); font-size:0.8rem; color:var(--color-cyan); font-weight:600;">${this.escapeHtml(latestIp)}</span>
                    <small style="color:var(--text-muted); display:block; font-size:0.7rem;">(${ipCount} logged ${ipCount === 1 ? 'session' : 'sessions'})</small>
                `;
            }

            return `
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="padding: 1rem;">
                        <div style="display:flex; align-items:center; gap:0.65rem;">
                            <span style="width:8px; height:8px; border-radius:50%; background:${isPrime ? 'var(--color-gold)' : 'var(--color-cyan)'}; box-shadow:0 0 8px ${isPrime ? 'var(--color-gold)' : 'var(--color-cyan)'}; display:inline-block;"></span>
                            <span style="font-family:var(--font-mono); font-weight:bold; color:var(--text-primary);">${this.escapeHtml(u.username)}</span>
                        </div>
                    </td>
                    <td style="padding: 1rem;">
                        <div>${fullName}</div>
                        ${location}
                    </td>
                    <td style="padding: 1rem; font-family:var(--font-mono); font-size:0.85rem;">
                        ${email}
                    </td>
                    <td style="padding: 1rem;">
                        ${accessBadge}
                    </td>
                    <td style="padding: 1rem;">
                        ${ipDisplay}
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <div style="display:inline-flex; gap:0.5rem;">
                            <button onclick="UsersManager.openEditModal(${index})" class="btn btn-primary" style="padding:0.35rem 0.75rem; font-size:0.75rem;">EDIT</button>
                            <button onclick="UsersManager.deleteUser(${u.id}, '${this.escapeHtml(u.username)}')" class="btn btn-secondary" style="padding:0.35rem 0.75rem; font-size:0.75rem; color:var(--color-coral); border-color:rgba(244,63,94,0.4);">DELETE</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    },

    openModal() {
        this.currentEditingUser = null;
        this.modalTitle.textContent = 'REGISTER NEW OPERATOR';
        this.idInput.value = '';
        this.usernameInput.value = '';
        this.emailInput.value = '';
        this.firstNameInput.value = '';
        this.lastNameInput.value = '';
        this.locationInput.value = '';
        this.passwordInput.value = '';
        this.passwordInput.required = true;
        this.passwordLabel.innerHTML = 'Password <span style="color:var(--color-coral);">*</span>';
        this.passwordHelp.style.display = 'none';
        this.isPrimeInput.checked = false;

        this.ipSection.style.display = 'none';
        this.ipListContainer.innerHTML = '';

        if (typeof ZeonAnimations !== 'undefined' && ZeonAnimations.animateModalOpen) {
            ZeonAnimations.animateModalOpen(this.modal);
        } else {
            this.modal.classList.add('active');
        }
    },

    openEditModal(index) {
        const u = this.users[index];
        if (!u) return;

        this.currentEditingUser = u;
        this.modalTitle.textContent = `EDIT OPERATOR // ${u.username}`;
        this.idInput.value = u.id;
        this.usernameInput.value = u.username || '';
        this.emailInput.value = u.email || '';
        this.firstNameInput.value = u.first_name || '';
        this.lastNameInput.value = u.last_name || '';
        this.locationInput.value = u.location || '';
        this.passwordInput.value = '';
        this.passwordInput.required = false;
        this.passwordLabel.innerHTML = 'Password (Reset)';
        this.passwordHelp.style.display = 'block';
        this.isPrimeInput.checked = u.is_prime_user == 1;

        // Render IP Telemetry Section
        this.ipSection.style.display = 'block';
        this.renderIpList(u.ip_history || []);

        if (typeof ZeonAnimations !== 'undefined' && ZeonAnimations.animateModalOpen) {
            ZeonAnimations.animateModalOpen(this.modal);
        } else {
            this.modal.classList.add('active');
        }
    },

    renderIpList(ipHistory) {
        if (!ipHistory || ipHistory.length === 0) {
            this.ipListContainer.innerHTML = `
                <div style="text-align:center; padding:0.75rem; color:var(--text-muted); font-size:0.75rem;">
                    No recorded IP addresses for this operator.
                </div>
            `;
            return;
        }

        this.ipListContainer.innerHTML = ipHistory.map(entry => {
            const ip = typeof entry === 'object' ? entry.ip : entry;
            const time = typeof entry === 'object' && entry.time ? entry.time : '';
            return `
                <div class="ip-item">
                    <div>
                        <span class="ip-tag">${this.escapeHtml(ip)}</span>
                        ${time ? `<span class="ip-time">${this.escapeHtml(time)}</span>` : ''}
                    </div>
                    <button type="button" onclick="UsersManager.handleRemoveSingleIp('${this.escapeHtml(ip)}')" class="btn-remove-ip">
                        ✕ Remove
                    </button>
                </div>
            `;
        }).join('');
    },

    closeModal() {
        if (typeof ZeonAnimations !== 'undefined' && ZeonAnimations.animateModalClose) {
            ZeonAnimations.animateModalClose(this.modal, () => {
                this.form.reset();
            });
        } else {
            this.modal.classList.remove('active');
            this.form.reset();
        }
    },

    async handleSubmit(e) {
        e.preventDefault();

        const id = this.idInput.value;
        const username = this.usernameInput.value.trim();
        const email = this.emailInput.value.trim();
        const firstName = this.firstNameInput.value.trim();
        const lastName = this.lastNameInput.value.trim();
        const location = this.locationInput.value.trim();
        const password = this.passwordInput.value;
        const isPrime = this.isPrimeInput.checked;

        if (!username) {
            alert('Username is required.');
            return;
        }

        const payload = {
            username,
            email,
            first_name: firstName,
            last_name: lastName,
            location,
            is_prime_user: isPrime
        };

        if (id) {
            payload.id = id;
            if (password) {
                payload.password = password;
            }
        } else {
            if (!password) {
                alert('Password is required for new operators.');
                return;
            }
            payload.password = password;
        }

        const submitBtn = document.getElementById('modalSubmitBtn');
        const origText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'SAVING...';

        try {
            const res = await fetch('/api/users/upsert.php', {
                method: 'POST',
                headers: (typeof App !== 'undefined' && App.getHeaders) ? App.getHeaders() : { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                this.closeModal();
                await this.loadUsers();
            } else {
                alert('Error: ' + (data.error || 'Failed to save operator profile'));
            }
        } catch (err) {
            console.error('Save error:', err);
            alert('Network error occurred while saving.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = origText;
        }
    },

    async deleteUser(id, username) {
        if (!confirm(`Are you sure you want to PERMANENTLY REMOVE operator "${username}"?`)) {
            return;
        }

        try {
            const res = await fetch(`/api/users/delete.php?id=${id}`, {
                method: 'DELETE',
                headers: (typeof App !== 'undefined' && App.getHeaders) ? App.getHeaders() : { 'Content-Type': 'application/json' }
            });
            const data = await res.json();

            if (data.success) {
                await this.loadUsers();
            } else {
                alert('Error: ' + (data.error || 'Failed to delete operator'));
            }
        } catch (err) {
            console.error('Delete error:', err);
            alert('Network error occurred while deleting.');
        }
    },

    async handleRemoveSingleIp(ip) {
        if (!this.currentEditingUser) return;
        if (!confirm(`Remove recorded IP "${ip}" from telemetry logs?`)) return;

        try {
            const res = await fetch('/api/users/remove_ip.php', {
                method: 'POST',
                headers: (typeof App !== 'undefined' && App.getHeaders) ? App.getHeaders() : { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: this.currentEditingUser.id,
                    ip: ip
                })
            });
            const data = await res.json();

            if (data.success) {
                this.currentEditingUser.ip_history = data.ip_history || [];
                this.renderIpList(this.currentEditingUser.ip_history);
                await this.loadUsers(); // Refresh background table counter
            } else {
                alert('Error: ' + (data.error || 'Failed to remove IP'));
            }
        } catch (err) {
            console.error('IP remove error:', err);
            alert('Failed to remove IP.');
        }
    },

    async handleClearAllIps() {
        if (!this.currentEditingUser) return;
        if (!confirm(`Purge ALL recorded login IPs for operator "${this.currentEditingUser.username}"?`)) return;

        try {
            const res = await fetch('/api/users/remove_ip.php', {
                method: 'POST',
                headers: (typeof App !== 'undefined' && App.getHeaders) ? App.getHeaders() : { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: this.currentEditingUser.id,
                    action: 'clear_all'
                })
            });
            const data = await res.json();

            if (data.success) {
                this.currentEditingUser.ip_history = [];
                this.renderIpList([]);
                await this.loadUsers();
            } else {
                alert('Error: ' + (data.error || 'Failed to purge IP telemetry'));
            }
        } catch (err) {
            console.error('Purge error:', err);
            alert('Failed to purge IP telemetry.');
        }
    },

    escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
};

document.addEventListener('DOMContentLoaded', () => {
    UsersManager.init();
});
