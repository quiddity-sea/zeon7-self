<?php
$current_page = basename($_SERVER['PHP_SELF']);
$menu_items = [
    ['label' => 'Dashboard', 'icon' => '⊞', 'href' => 'index.php'],
    ['label' => 'News Desk', 'icon' => '●', 'href' => 'news-desk.php'],
    ['label' => 'Knowledge', 'icon' => '🧠', 'href' => 'knowledge.php'],
    ['label' => 'Instructions', 'icon' => '📝', 'href' => 'instructions.php'],
    ['label' => 'Vision', 'icon' => '👁', 'href' => 'vision.php'],
    ['label' => 'Lore', 'icon' => '∞', 'href' => 'lore.php'],
    ['label' => 'Settings', 'icon' => '⚙️', 'href' => 'settings.php']
];
?>
<link rel="stylesheet" href="css/components/sidebar.css">
<nav class="sidebar">
    <div class="brand-container">
        <img src="assets/logo_1759683970.png" class="brand-logo" alt="ZEON7">
    </div>
    
    <?php foreach ($menu_items as $item): ?>
        <?php $active = ($current_page === $item['href']) ? 'active' : ''; ?>
        <a href="<?php echo $item['href']; ?>" class="nav-item <?php echo $active; ?>">
            <i><?php echo $item['icon']; ?></i> <span class="nav-text"><?php echo $item['label']; ?></span>
        </a>
    <?php endforeach; ?>

    <div style="flex:1"></div>
    <a href="#" id="logoutBtn" class="nav-item"><i>×</i> <span class="nav-text">Logout</span></a>
</nav>
