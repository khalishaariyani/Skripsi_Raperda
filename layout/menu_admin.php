<?php
require_once CONFIG_PATH . '/constants.php';

$current_uri = $_SERVER['REQUEST_URI'];
function is_uri_match(array $patterns): bool
{
    global $current_uri;
    foreach ($patterns as $pattern) {
        if (strpos($current_uri, $pattern) !== false) return true;
    }
    return false;
}
?>
<nav class="main-menu-container nav flex-column" style="background:linear-gradient(135deg, #1e1f31 0%, #3c3f58 60%, #141518 100%); min-height:100vh; padding:24px 0;">

    <ul class="menu-list">

        <!-- DASHBOARD -->
        <li class="menu-category">Dashboard</li>
        <li>
            <a href="<?= BASE_URL ?>/modul/<?= $_SESSION['role'] ?>/dashboard.php" class="menu-item <?= str_contains($current_uri, '/dashboard.php') ? 'active' : '' ?>">
                <i class="ti-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <hr class="menu-divider">
        </li>

        <!-- DATA PENGGUNA -->
        <li class="menu-category">Data Pengguna</li>
        <li>
            <a href="<?= BASE_URL ?>/modul/admin/user/user.php" class="menu-item <?= str_contains($current_uri, '/user/') ? 'active' : '' ?>">
                <i class="ti-user"></i>
                <span>Manajemen User</span>
            </a>
        </li>
        <li>
            <hr class="menu-divider">
        </li>

        <!-- DATA MASTER -->
        <li class="menu-category">Data Master</li>
        <?php
        $data_master_uri = ['/jadwal/', '/dok_usulan/', '/dokumen_rapat/', '/kehadiran/', '/undangan/', '/instansi/', '/kegiatan/',  '/notulen/', '/perda/', '/revisi/', '/penyerahan_dok/'];
        ?>
        <li class="slide has-sub <?= is_uri_match($data_master_uri) ? 'open active' : '' ?>">
            <a href="#" class="menu-item">
                <i class="ti ti-database"></i>
                <span>Manajemen Data</span>
                <i class="fe fe-chevron-right side-menu__angle"></i>
            </a>
            <ul class="slide-menu child2">
                <li><a href="<?= BASE_URL ?>/modul/share/jadwal/jadwal.php" class="menu-item <?= str_contains($current_uri, '/jadwal/') ? 'active' : '' ?>"><i class="ti-agenda"></i> Jadwal Rapat</a></li>
                <li><a href="<?= BASE_URL ?>/modul/share/dok_usulan/usulan.php" class="menu-item <?= str_contains($current_uri, '/dok_usulan/') ? 'active' : '' ?>"><i class="ti-files"></i> Dokumen Usulan</a></li>
                <li><a href="<?= BASE_URL ?>/modul/share/dokumen_rapat/dok_rapat.php" class="menu-item <?= str_contains($current_uri, '/dokumen_rapat/') ? 'active' : '' ?>"><i class="ti-files"></i> Dokumen Rapat</a></li>
                <li><a href="<?= BASE_URL ?>/modul/share/kehadiran/kehadiran.php" class="menu-item <?= str_contains($current_uri, '/kehadiran/') ? 'active' : '' ?>"><i class="ti-check-box"></i> Kehadiran</a></li>
                <li><a href="<?= BASE_URL ?>/modul/share/undangan/undangan.php" class="menu-item <?= str_contains($current_uri, '/undangan/') ? 'active' : '' ?>"><i class="ti-email"></i> Undangan Rapat</a></li>
                <li><a href="<?= BASE_URL ?>/modul/admin/instansi/instansi.php" class="menu-item <?= str_contains($current_uri, '/instansi/') ? 'active' : '' ?>"><i class="ti-briefcase"></i> Anggota Dinas</a></li>
                <li><a href="<?= BASE_URL ?>/modul/share/kegiatan/dokumentasi.php" class="menu-item <?= str_contains($current_uri, '/kegiatan/') ? 'active' : '' ?>"><i class="ti-camera"></i> Dokumentasi Kegiatan</a></li>
                <li><a href="<?= BASE_URL ?>/modul/share/notulen/notulen.php" class="menu-item <?= str_contains($current_uri, '/notulen/') ? 'active' : '' ?>"><i class="ti-notepad"></i> Notulen Rapat</a></li>
                <li><a href="<?= BASE_URL ?>/modul/admin/perda/perda.php" class="menu-item <?= str_contains($current_uri, '/perda/') ? 'active' : '' ?>"><i class="ti-book"></i> Perda</a></li>
                <li><a href="<?= BASE_URL ?>/modul/admin/penyerahan_dok/penyerahan.php" class="menu-item <?= str_contains($current_uri, '/penyerahan_dok/') ? 'active' : '' ?>"><i class="ti-upload"></i> Penyerahan Dokumen</a></li>
            </ul>
        </li>
        <li>
            <hr class="menu-divider">
        </li>

        <!-- ARSIP -->
        <li class="menu-category">Arsip</li>
        <li>
            <a href="<?= BASE_URL ?>/modul/share/arsip/arsip.php"
                class="menu-item <?= str_contains($current_uri, '/arsip/') ? 'active' : '' ?>">
                <i class="ti-archive"></i>
                <span>Arsip Rapat</span>
            </a>
        </li>
        <li>
            <hr class="menu-divider">
        </li>
        <!-- LAPORAN -->
        <li class="menu-category">Laporan</li>
        <li class="slide has-sub <?= is_uri_match(['/laporan/']) ? 'open active' : '' ?>">
            <a href="#" class="menu-item">
                <i class="ti-bar-chart"></i>
                <span>Laporan</span>
                <!-- HAPUS ICON PANAH DI SINI -->
            </a>
            <ul class="slide-menu child2">
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_kehadiran.php" class="menu-item <?= str_contains($current_uri, 'laporan_kehadiran.php') ? 'active' : '' ?>">
                        <i class="ti-check"></i> Laporan Kehadiran
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_anggotadinas.php" class="menu-item <?= str_contains($current_uri, 'laporan_anggotadinas.php') ? 'active' : '' ?>">
                        <i class="ti-user"></i> Laporan Anggota Dinas
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_arsip.php" class="menu-item <?= str_contains($current_uri, 'laporan_arsip.php') ? 'active' : '' ?>">
                        <i class="ti-archive"></i> Laporan Arsip
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_ba.php" class="menu-item <?= str_contains($current_uri, 'laporan_ba.php') ? 'active' : '' ?>">
                        <i class="ti-book"></i> Laporan Berita Acara
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_dokumentasi.php" class="menu-item <?= str_contains($current_uri, 'laporan_dokumentasi.php') ? 'active' : '' ?>">
                        <i class="ti-camera"></i> Laporan Dokumentasi
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_jadwal.php" class="menu-item <?= str_contains($current_uri, 'laporan_jadwal.php') ? 'active' : '' ?>">
                        <i class="ti-calendar"></i> Laporan Jadwal Rapat
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_notulen.php" class="menu-item <?= str_contains($current_uri, 'laporan_notulen.php') ? 'active' : '' ?>">
                        <i class="ti-write"></i> Laporan Notulen
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_penyerahandok.php" class="menu-item <?= str_contains($current_uri, 'laporan_penyerahandok.php') ? 'active' : '' ?>">
                        <i class="ti-upload"></i> Laporan Penyerahan Dokumen
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_rapatseringdibhs.php" class="menu-item <?= str_contains($current_uri, 'laporan_rapatseringdibhs.php') ? 'active' : '' ?>">
                        <i class="ti-bar-chart"></i> Laporan Rapat Sering Dibahas
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_undangan.php" class="menu-item <?= str_contains($current_uri, 'laporan_undangan.php') ? 'active' : '' ?>">
                        <i class="ti-email"></i> Laporan Undangan
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_aktivitas.php" class="menu-item <?= str_contains($current_uri, 'laporan_aktivitas.php') ? 'active' : '' ?>">
                        <i class="ti-layers-alt"></i> Laporan Aktivitas DPRD
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/modul/admin/laporan/laporan_revisi.php" class="menu-item <?= str_contains($current_uri, 'laporan_revisi.php') ? 'active' : '' ?>">
                        <i class="ti-comments"></i> Laporan Saran & Revisi
                    </a>
                </li>
            </ul>


        </li>

        <li>
            <hr class="menu-divider">
        </li>

        <!-- DISKUSI DIPISAH -->
        <li class="menu-category">Diskusi</li>
        <li>
            <a href="<?= BASE_URL ?>/modul/share/diskusi/diskusi.php"
                class="menu-item <?= str_contains($current_uri, '/diskusi/') ? 'active' : '' ?>">
                <i class="ti-comments"></i>
                <span>Diskusi Perda</span>
            </a>
        </li>
        <li>
            <hr class="menu-divider">
        </li>


        <!-- INFORMASI -->
        <li class="menu-category"></i>Informasi</li>
        <li>
            <a href="<?= BASE_URL ?>/modul/admin/info/info.php" class="menu-item <?= str_contains($current_uri, '/info/') ? 'active' : '' ?>">
                <i class="ti-info"></i>
                <span>Informasi Terbaru</span>
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
    }

    .menu-item i {
        margin-right: 16px;
        font-size: 18px;
        min-width: 22px;
        text-align: center;
        color: #ffffff;
    }

    .menu-item:hover,
    .menu-item.active {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        font-weight: 600;
        box-shadow: inset 2px 0 0 #5C6BC0;
    }

    .menu-item.active i,
    .menu-item:hover i {
        color: #ffffff;
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

    .text-indigo {
        color: #5C6BC0 !important;
    }
</style>