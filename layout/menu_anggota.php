<?php
$current_uri = $_SERVER['REQUEST_URI'];
function is_uri_match($patterns)
{
    global $current_uri;
    foreach ($patterns as $pattern) {
        if (strpos($current_uri, $pattern) !== false) return true;
    }
    return false;
}
?>

<nav class="main-menu-container nav flex-column"
    style="background: linear-gradient(135deg, #1e1f31 0%, #3c3f58 60%, #141518 100%); min-height: 100vh; padding: 24px 0;">

    <ul class="menu-list">
        <!-- DASHBOARD -->
        <li class="menu-category">Dashboard</li>
        <li>
            <a href="<?= BASE_URL ?>/modul/anggota/dashboard.php" class="menu-item <?= strpos($current_uri, '/dashboard.php') !== false ? 'active' : '' ?>">
                <i class="ti-home"></i><span>Dashboard</span>
            </a>
        </li>
        <li>
            <hr class="menu-divider">
        </li>

        <!-- AGENDA RAPAT -->
        <li class="menu-category">Agenda Rapat</li>
        <li>
            <a href="<?= BASE_URL ?>/modul/share/jadwal/jadwal.php" class="menu-item <?= strpos($current_uri, '/jadwal/') !== false ? 'active' : '' ?>">
                <i class="ti-agenda"></i><span>Jadwal Kegiatan</span>
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/modul/share/kehadiran/kehadiran.php" class="menu-item <?= strpos($current_uri, '/kehadiran/') !== false ? 'active' : '' ?>">
                <i class="ti-check-box"></i><span>Konfirmasi Kehadiran</span>
            </a>
        </li>
        <li>
            <hr class="menu-divider">
        </li>

        <!-- DOKUMEN & NOTULEN -->
        <li class="menu-category">Informasi Rapat</li>
        <?php $dokumen_uri = ['/dokumen_rapat/', '/notulen/', '/arsip/']; ?>
        <li class="slide has-sub <?= is_uri_match($dokumen_uri) ? 'open active' : '' ?>">
            <a href="#" class="menu-item">
                <i class="ti-folder"></i><span>Rangkuman Dokumen</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li>
                    <a href="<?= BASE_URL ?>/modul/share/dok_usulan/usulan.php" class="menu-item <?= strpos($current_uri, '/dok_usulan/') !== false ? 'active' : '' ?>">
                        <i class="ti-file"></i> Dokumen Usulan
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/share/dokumen_rapat/dok_rapat.php" class="menu-item <?= strpos($current_uri, '/dokumen_rapat/') !== false ? 'active' : '' ?>">
                        <i class="ti-files"></i> Dokumen Rapat
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/share/notulen/notulen.php" class="menu-item <?= strpos($current_uri, '/notulen/') !== false ? 'active' : '' ?>">
                        <i class="ti-write"></i> Notulen Rapat
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/share/arsip/arsip.php" class="menu-item <?= strpos($current_uri, '/arsip/') !== false ? 'active' : '' ?>">
                        <i class="ti-archive"></i> Arsip Rapat
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <hr class="menu-divider">
        </li>

        <!-- DISKUSI -->
        <li class="menu-category">Diskusi</li>
        <li>
            <a href="<?= BASE_URL ?>/modul/share/diskusi/diskusi.php" class="menu-item <?= strpos($current_uri, '/diskusi/') !== false ? 'active' : '' ?>">
                <i class="ti-comments"></i> Diskusi Internal
            </a>
        </li>
        <li>
            <hr class="menu-divider">
        </li>
    </ul>
</nav>

<style>
    .menu-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu-item {
        display: flex;
        align-items: center;
        padding: 12px 32px;
        color: #ccd6f6;
        text-decoration: none;
        border-radius: 6px;
        margin-bottom: 3px;
        transition: background 0.3s, color 0.3s;
        font-size: 15px;
        font-weight: 500;
        position: relative;
    }

    .menu-item i {
        margin-right: 16px;
        font-size: 18px;
        min-width: 22px;
        text-align: center;
        color: #fff;
    }

    .menu-item:hover,
    .menu-item.active {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        font-weight: 600;
        box-shadow: inset 2px 0 0 #5C6BC0;
    }

    .menu-item.active:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #5C6BC0;
        border-radius: 4px;
    }

    .menu-category {
        color: #5C6BC0;
        font-size: 13px;
        font-weight: 700;
        padding: 10px 32px 4px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .menu-divider {
        border-color: #2d3256;
        margin: 8px 0;
    }

    .slide-menu.child2 {
        list-style: none;
        margin: 0;
        padding-left: 10px;
    }
</style>