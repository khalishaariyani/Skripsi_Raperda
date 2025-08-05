<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

$role = $_SESSION['role'] ?? 'guest';
$namaPengguna = $_SESSION['nama'] ?? '';
$fotoProfil = $_SESSION['foto'] ?? '';
$fotoPath = ($fotoProfil !== '')
    ? BASE_URL . '/uploads/' . $fotoProfil
    : ASSETS_URL . '/images/default-avatar.png';

$sqlNotif = "
    SELECT u.penerima, u.tanggal AS tanggal_undangan, j.judul_rapat AS judul
    FROM undanganrapat u
    JOIN jadwalrapat j ON u.idRapat = j.id
    WHERE u.penerima = ?
    ORDER BY u.tanggal DESC
    LIMIT 5
";
$stmt = $conn->prepare($sqlNotif);
$stmt->bind_param("s", $namaPengguna);
$stmt->execute();
$notif = $stmt->get_result();
$jumlah = $notif->num_rows;

?>


<header class="app-header">
    <style>
        .notifikasi-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 16px;
        }

        .notifikasi-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .notifikasi-teks {
            flex: 1;
            word-break: break-word;
            white-space: normal;
            line-height: 1.4;
        }
    </style>

    <div class="main-header-container container-fluid d-flex justify-content-between align-items-center">

        <!-- Sidebar Toggle -->
        <div class="d-flex align-items-center">
            <div class="header-element">
                <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);">
                    <span></span>
                </a>
            </div>
        </div>

        <!-- Notifikasi + Profil -->
        <div class="d-flex align-items-center">

            <!-- Notifikasi -->
            <div class="header-element notifications-dropdown me-3">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="messageDropdown" aria-expanded="false">
                    <i class="fe fe-bell header-link-icon fs-20"></i>
                    <?php if ($jumlah > 0): ?>
                        <span class="badge bg-secondary header-icon-badge pulse pulse-secondary" id="notification-icon-badge">
                            <?= $jumlah ?>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="main-header-dropdown dropdown-menu dropdown-menu-end">
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 fs-17 fw-semibold">Notifikasi</p>
                            <span class="badge bg-secondary rounded-pill"><?= $jumlah ?></span>
                        </div>
                    </div>
                    <div class="dropdown-divider m-0"></div>
                    <div class="p-3" style="max-height: 300px; overflow-y: auto;">
                        <?php if ($jumlah > 0): ?>
                            <?php while ($row = $notif->fetch_assoc()): ?>
                                <div class="notifikasi-item">
                                    <span class="notifikasi-icon avatar bg-primary-transparent text-primary">
                                        <i class="fe fe-calendar fs-18"></i>
                                    </span>
                                    <div class="notifikasi-teks">
                                        <p class="mb-1 fs-14">
                                            📢 Undangan untuk rapat <b><?= htmlspecialchars($row['judul']) ?></b><br>
                                            telah dikirim ke <b><?= htmlspecialchars($row['penerima']) ?></b>.
                                        </p>
                                        <small class="text-muted"><?= date('d M Y', strtotime($row['tanggal_undangan'])) ?></small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center p-4">
                                <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                                    <i class="ri-notification-off-line fs-2"></i>
                                </span>
                                <h6 class="fw-semibold mt-3">No New Notifications</h6>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Profil -->
            <div class="header-element">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <img src="<?= $fotoPath ?>" alt="foto profil" class="rounded-circle" width="32" height="32">
                    </div>
                </a>
                <ul class="main-header-dropdown dropdown-menu header-profile-dropdown dropdown-menu-end" aria-labelledby="mainHeaderProfile">
                    <li>
                        <div class="header-navheading border-bottom">
                            <h6 class="main-notification-title">Nama: <?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></h6>
                            <p class="main-notification-text mb-0">Role: <?= ucfirst($_SESSION['role'] ?? '') ?></p>
                        </div>
                    </li>
                    <li><a class="dropdown-item d-flex border-bottom" href="<?= BASE_URL ?>/modul/share/profil/profil.php"><i class="fe fe-user fs-16 me-2"></i>Profil Saya</a></li>
                    <li><a class="dropdown-item d-flex" href="<?= BASE_URL ?>/logout.php"><i class="fe fe-power fs-16 me-2"></i>Keluar</a></li>
                </ul>
            </div>

        </div>
    </div>
</header>