<?php
/**
 * Navigation Sidebar Component
 *
 * Renders the admin sidebar navigation. Agent identity, logo, and available
 * menu items are determined dynamically by AgentContextService.
 */
function renderSidebar(AgentContextService $agentCtx, string $currentPage = ''): void
{
    $name = htmlspecialchars($agentCtx->getDisplayName());
    $logo = htmlspecialchars($agentCtx->getLogoPath());
    $agentId = htmlspecialchars($agentCtx->getAgentId());
    ?>
    <nav class="sidebar" data-agent="<?= $agentId ?>">
        <div class="brand-container">
            <a href="index.php" title="Mission Control" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none;">
                <img src="/<?= $logo ?>" class="brand-logo" alt="<?= $name ?>" onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=<?= $agentId ?>'">
                <span class="brand-title"><?= strtoupper($name) ?></span>
            </a>
        </div>
        
        <div style="display:flex; flex-direction:column; gap:0.25rem; width:100%;">
            <a href="index.php" class="nav-item <?= ($currentPage === 'dashboard' || $currentPage === 'index.php') ? 'active' : '' ?>">
                <i>⊞</i> <span class="nav-text">Dashboard</span>
            </a>
            <?php if ($agentCtx->hasCapability('news_desk')): ?>
            <a href="news-desk.php" class="nav-item <?= $currentPage === 'news-desk.php' ? 'active' : '' ?>">
                <i>📰</i> <span class="nav-text">News Desk</span>
            </a>
            <?php endif; ?>
            <?php if ($agentCtx->hasCapability('blog')): ?>
            <a href="posts.php" class="nav-item <?= $currentPage === 'posts.php' ? 'active' : '' ?>">
                <i>📝</i> <span class="nav-text">Posts</span>
            </a>
            <?php endif; ?>
            <?php if ($agentCtx->hasCapability('vision')): ?>
            <a href="vision.php" class="nav-item <?= $currentPage === 'vision.php' ? 'active' : '' ?>">
                <i>👁</i> <span class="nav-text">Vision Studio</span>
            </a>
            <?php endif; ?>
            <?php if ($agentCtx->hasCapability('memory')): ?>
            <a href="lore.php" class="nav-item <?= $currentPage === 'lore.php' ? 'active' : '' ?>">
                <i>∞</i> <span class="nav-text">Memory Lore</span>
            </a>
            <?php endif; ?>
            <a href="instructions.php" class="nav-item <?= $currentPage === 'instructions.php' ? 'active' : '' ?>">
                <i>⚙</i> <span class="nav-text">Instructions</span>
            </a>
            <?php if ($agentCtx->hasCapability('knowledge')): ?>
            <a href="knowledge.php" class="nav-item <?= $currentPage === 'knowledge.php' ? 'active' : '' ?>">
                <i>🧠</i> <span class="nav-text">Knowledge</span>
            </a>
            <?php endif; ?>
            <?php if ($agentCtx->hasCapability('chat')): ?>
            <a href="chat_logs.php" class="nav-item <?= $currentPage === 'chat_logs.php' ? 'active' : '' ?>">
                <i>💬</i> <span class="nav-text">Chat Logs</span>
            </a>
            <?php endif; ?>
            <a href="users.php" class="nav-item <?= $currentPage === 'users.php' ? 'active' : '' ?>">
                <i>👤</i> <span class="nav-text">Users</span>
            </a>
            <a href="settings.php" class="nav-item <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                <i>🛠</i> <span class="nav-text">Settings</span>
            </a>
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
    <?php
}
