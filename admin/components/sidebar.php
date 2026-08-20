<?php
$current_page = basename($_SERVER['PHP_SELF']);
$menu_items = [
    ['label' => 'Dashboard', 'icon' => '⊞', 'href' => 'index.php'],
    ['label' => 'News Desk', 'icon' => '📰', 'href' => 'news-desk.php'],
    ['label' => 'Posts', 'icon' => '📝', 'href' => 'posts.php'],
    ['label' => 'Vision', 'icon' => '👁', 'href' => 'vision.php'],
    ['label' => 'Memory Lore', 'icon' => '∞', 'href' => 'lore.php'],
    ['label' => 'Instructions', 'icon' => '⚙', 'href' => 'instructions.php'],
    ['label' => 'Knowledge', 'icon' => '🧠', 'href' => 'knowledge.php'],
    ['label' => 'Chat Logs', 'icon' => '💬', 'href' => 'chat_logs.php'],
    ['label' => 'Users', 'icon' => '👤', 'href' => 'users.php'],
    ['label' => 'Settings', 'icon' => '🛠', 'href' => 'settings.php']
];
?>

<nav class="sidebar">
    <div class="brand-container">
        <a href="index.php" title="Zeon7 Mission Control" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none;">
            <img src="../assets/images/logo_1759683970.png" class="brand-logo" alt="ZEON7" onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=zeon7'">
            <span class="brand-title">ZEON7</span>
        </a>
    </div>
    
    <div style="display:flex; flex-direction:column; gap:0.25rem; width:100%;">
        <?php foreach ($menu_items as $item): ?>
            <?php $active = ($current_page === $item['href']) ? 'active' : ''; ?>
            <a href="<?php echo $item['href']; ?>" class="nav-item <?php echo $active; ?>">
                <i><?php echo $item['icon']; ?></i> <span class="nav-text"><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div style="flex:1"></div>
    
    <div style="width:100%; border-top:1px solid rgba(255,255,255,0.06); padding-top:0.5rem;">
        <a href="../index.php" target="_blank" class="nav-item" title="Public View">
            <i>↗</i> <span class="nav-text">Public Site</span>
        </a>
        <a href="#" id="logoutBtn" class="nav-item" title="Logout Session">
            <i>✕</i> <span class="nav-text">Logout</span>
        </a>
    </div>
</nav>

<!-- GSAP Core & Plugins CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="../js/theme-switcher.js"></script>
<script src="js/animations.js?v=11.0"></script>
<script>
document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
    e.preventDefault();
    if (confirm('TERMINATE OPERATOR SESSION?')) {
        try {
            await fetch('../api/auth/logout.php', { method: 'POST' });
        } catch(err) {}
        window.location.href = 'login.php';
    }
});
</script>
