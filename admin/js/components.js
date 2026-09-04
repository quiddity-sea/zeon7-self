/**
 * Zeon7 Admin Components
 * Handles reusable UI elements like the Sidebar.
 */

const Components = {
    Sidebar: {
        render(activePage) {
            const menuItems = [
                { id: 'dashboard', label: 'Dashboard', icon: '⊞', href: 'index.php' },
                { id: 'news-desk', label: 'News Desk', icon: '●', href: 'news-desk.php' },
                { id: 'knowledge', label: 'Knowledge', icon: '🧠', href: 'knowledge.php' },
                { id: 'instructions', label: 'Instructions', icon: '📝', href: 'instructions.php' },
                { id: 'vision', label: 'Vision', icon: '👁', href: 'vision.php' },
                { id: 'lore', label: 'Lore', icon: '∞', href: 'lore.php' },
                { id: 'settings', label: 'Settings', icon: '⚙️', href: 'settings.php' }
            ];

            const navLinks = menuItems.map(item => {
                const isActive = item.id === activePage ? 'active' : '';
                return `<a href="${item.href}" class="nav-item ${isActive}"><i>${item.icon}</i> ${item.label}</a>`;
            }).join('');

            return `
                <div class="brand-container">
                    <img src="../assets/images/logo_1759683970.png" class="brand-logo" alt="ZEON7">
                </div>
                ${navLinks}
                <div style="flex:1"></div>
                <a href="#" id="logoutBtn" class="nav-item"><i>×</i> Logout</a>
            `;
        },

        init(containerId, activePage) {
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = this.render(activePage);

                // Re-bind logout if App exists
                const logoutBtn = document.getElementById('logoutBtn');
                if (logoutBtn && typeof App !== 'undefined') {
                    logoutBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        App.logout();
                    });
                }
            } else {
                console.error(`Sidebar container #${containerId} not found.`);
            }
        }
    }
};
