<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Detail Usulan Jadwal Rapat';

$query = "SELECT * FROM jadwalrapat WHERE status = 'usulan' ORDER BY tanggal DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="main-content-label"><?= $pageTitle ?></h5>
                        <a href="<?= BASE_URL ?>/modul/persidangan/dashboard.php" class="btn btn-sm btn-secondary">
                            ← Kembali ke Dashboard
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead class="bg-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Judul Rapat</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Tempat</th>
                                        <th>Pengusul</th>
                                        <th>Dibuat Oleh</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()):
                                            $status = strtolower($row['status']);
                                            $badge = match ($status) {
                                                'usulan' => 'bg-warning',
                                                'disetujui' => 'bg-success',
                                                'dibatalkan' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                                <td><?= htmlspecialchars($row['waktu']) ?></td>
                                                <td><?= htmlspecialchars($row['tempat']) ?></td>
                                                <td><?= htmlspecialchars($row['pengusul']) ?></td>
                                                <td><?= htmlspecialchars($row['dibuat_oleh']) ?></td>
                                                <td><span class="badge <?= $badge ?>"><?= ucfirst($status) ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Belum ada usulan jadwal rapat baru.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>