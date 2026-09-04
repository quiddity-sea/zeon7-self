<?php
require_once __DIR__ . '/../../src/config/env.php';
require_once __DIR__ . '/../../src/services/AgentContextService.php';

$agentCtx = $agentCtx ?? new AgentContextService();
$current_page = basename($_SERVER['PHP_SELF']);
$agentName = $agentCtx->getDisplayName();
$agentId = $agentCtx->getAgentId();
$agentAccent = $agentCtx->getThemeAccent();
$logoPath = $agentCtx->getLogoPath();

// Base navigation common to all agents
$nav_items = [
    ['label' => 'Dashboard', 'icon' => '⊞', 'href' => 'index.php', 'show' => true],
];

// Agent-Specific Capabilities
if ($agentCtx->hasCapability('news_desk')) {
    $nav_items[] = ['label' => 'News Desk', 'icon' => '📰', 'href' => 'news-desk.php', 'show' => true];
}
if ($agentCtx->hasCapability('blog')) {
    $nav_items[] = ['label' => 'Posts', 'icon' => '📝', 'href' => 'posts.php', 'show' => true];
}
if ($agentCtx->hasCapability('vision')) {
    $nav_items[] = ['label' => 'Vision', 'icon' => '👁', 'href' => 'vision.php', 'show' => true];
}

// Memory & Core Intelligence
if ($agentCtx->hasCapability('memory')) {
    $nav_items[] = ['label' => 'Memory Lore', 'icon' => '∞', 'href' => 'lore.php', 'show' => true];
}
$nav_items[] = ['label' => 'Instructions', 'icon' => '⚙', 'href' => 'instructions.php', 'show' => true];

if ($agentCtx->hasCapability('knowledge')) {
    $nav_items[] = ['label' => 'Knowledge', 'icon' => '🧠', 'href' => 'knowledge.php', 'show' => true];
}
if ($agentCtx->hasCapability('chat')) {
    $nav_items[] = ['label' => 'Chat Logs', 'icon' => '💬', 'href' => 'chat_logs.php', 'show' => true];
}

// System Operations
$nav_items[] = ['label' => 'Users', 'icon' => '👤', 'href' => 'users.php', 'show' => true];
$nav_items[] = ['label' => 'Settings', 'icon' => '🛠', 'href' => 'settings.php', 'show' => true];
?>

<nav class="sidebar" style="--agent-accent: <?= htmlspecialchars($agentAccent) ?>;">
    <div class="brand-container">
        <a href="index.php" title="Mission Control" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none;">
            <img src="../<?= htmlspecialchars($logoPath) ?>" class="brand-logo" alt="<?= htmlspecialchars($agentName) ?>" 
                 onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=<?= htmlspecialchars($agentId) ?>'">
            <span class="brand-title" style="color: <?= htmlspecialchars($agentAccent) ?>; text-shadow: 0 0 10px <?= htmlspecialchars($agentAccent) ?>33;">
                <?= strtoupper(htmlspecialchars($agentName)) ?>
            </span>
        </a>
    </div>
    
    <div style="display:flex; flex-direction:column; gap:0.25rem; width:100%;">
        <?php foreach ($nav_items as $item): ?>
            <?php if (!empty($item['show'])): ?>
                <?php $active = ($current_page === $item['href']) ? 'active' : ''; ?>
                <a href="<?= $item['href'] ?>" class="nav-item <?= $active ?>">
                    <i><?= $item['icon'] ?></i> <span class="nav-text"><?= $item['label'] ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div style="flex:1"></div>
    
    <div style="width:100%; border-top:1px solid rgba(255,255,255,0.06); padding-top:0.5rem;">
        <a href="../index.php?agent=<?= urlencode($agentId) ?>" target="_blank" class="nav-item" title="Public Agent View">
            <i>↗</i> <span class="nav-text">Public View (<?= htmlspecialchars($agentName) ?>)</span>
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
