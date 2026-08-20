const API_BASE = '/api';

const App = {
    csrfToken: null,

    async checkAuth() {
        try {
            const res = await fetch(`${API_BASE}/auth/check.php`);
            const text = await res.text();
            try {
                const data = JSON.parse(text);
                if (data.authenticated) {
                    this.csrfToken = data.csrf_token;
                }
                return data.authenticated;
            } catch (e) {
                console.error('Auth check failed: Invalid JSON response', text);
                return false;
            }
        } catch (e) {
            console.error('Auth check failed', e);
            return false;
        }
    },

    async redirectIfAuth() {
        const isAuthenticated = await this.checkAuth();
        if (isAuthenticated) {
            window.location.href = 'index.php';
        }
    },

    getHeaders() {
        const headers = { 'Content-Type': 'application/json' };
        if (this.csrfToken) {
            headers['X-CSRF-TOKEN'] = this.csrfToken;
        }
        return headers;
    },

    async login(username, password) {
        try {
            const res = await fetch(`${API_BASE}/auth/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            const text = await res.text();
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            console.error('Login failed', e);
            return { success: false, error: 'Network or server error' };
        }
    },

    async logout() {
        try {
            await fetch(`${API_BASE}/auth/logout.php`, { method: 'POST' });
            window.location.href = 'login.php';
        } catch (e) {
            console.error('Logout failed', e);
        }
    },

    async requireAuth() {
        const isAuthenticated = await this.checkAuth();
        if (!isAuthenticated) {
            window.location.href = 'login.php';
        } else {
            this.initLogout();
        }
    },

    initLogout() {
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            // Remove old listeners to avoid duplicates if called multiple times
            const newBtn = logoutBtn.cloneNode(true);
            logoutBtn.parentNode.replaceChild(newBtn, logoutBtn);

            newBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }
    }
};
