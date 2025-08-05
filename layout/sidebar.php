<!-- layouts/sidebar.php -->
<aside class="app-sidebar sticky" id="sidebar">
    <!-- Logo Sidebar -->
    <div class="main-sidebar-header">
        <a href="<?= BASE_URL ?>/index.php" class="header-logo">
            <img src="<?= ASSETS_URL ?>/images/brand-logos/raperda.png" alt="logo" style="height: 40px;" />
        </a>
    </div>

    <!-- Body Sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
            </div>

            <ul class="main-menu">

                <!-- Include dynamic role-based menu -->
                <?php
                $role = $_SESSION['role'] ?? 'guest';
                $menuFile = LAYOUT_PATH . "/menu_{$role}.php";
                if (file_exists($menuFile)) {
                    include $menuFile;
                } else {
                    echo '<li class="slide"><a href="#" class="side-menu__item text-danger">Menu tidak ditemukan</a></li>';
                }
                ?>
            </ul>

            <div class="slide-right" id="slide-right">
                <i class="fe fe-chevron-right"></i>
            </div>
        </nav>
    </div>
</aside>
