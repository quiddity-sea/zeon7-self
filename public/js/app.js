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

    getHeaders() {
        const headers = { 'Content-Type': 'application/json' };
        if (this.csrfToken) {
            headers['X-CSRF-TOKEN'] = this.csrfToken;
        }
        return headers;
    },

    async login(password) {
        const res = await fetch(`${API_BASE}/auth/login.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password })
        });
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Login failed: Invalid JSON response', text);
            return { success: false, error: 'Server error: ' + text.substring(0, 100) + '...' };
        }
    },

    async logout() {
        await fetch(`${API_BASE}/auth/logout.php`, { method: 'POST' });
        window.location.href = '/admin/login.php';
    },

    requireAuth() {
        this.checkAuth().then(isAuth => {
            if (!isAuth) {
                window.location.href = '/admin/login.php';
            }
        });
    },

    redirectIfAuth() {
        this.checkAuth().then(isAuth => {
            if (isAuth) {
                window.location.href = '/admin/index.php';
            }
        });
    }
};

// Handle Logout Button
document.addEventListener('DOMContentLoaded', () => {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            App.logout();
        });
    }
});
