<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Kehadiran Rapat';

$status = $_POST['status'] ?? '';
$bulan  = $_POST['bulan'] ?? '';

// Query untuk data tabel
$query = "
SELECT
    u.nama AS nama_anggota,
    r.judul_rapat AS judul_rapat,
    k.status AS status_kehadiran,
    DATE(k.waktu_hadir) AS tanggal_kehadiran
FROM kehadiranrapat k
JOIN user u ON u.id = k.id_user
JOIN jadwalrapat r ON r.id = k.id_rapat
WHERE 1=1
";
if (!empty($status)) {
    $query .= " AND k.status = '$status'";
}
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(k.waktu_hadir, '%Y-%m') = '$bulan'";
}
$result = $conn->query($query);

// Grafik per bulan (Januari–Desember)
$bulanLabel = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
];
$jumlahAktivitas = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(waktu_hadir) AS bulan_ke, COUNT(*) AS total
    FROM kehadiranrapat
    GROUP BY bulan_ke
");

while ($g = $grafik->fetch_assoc()) {
    $index = (int)$g['bulan_ke'] - 1;
    $jumlahAktivitas[$index] = (int)$g['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content">
            <div class="container-fluid py-4">
                <h4 class="fw-bold mb-4"><?= $pageTitle ?></h4>

                <!-- Filter -->
                <form method="POST" class="card card-body mb-4 shadow-sm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status Kehadiran</label>
                            <select class="form-select" name="status" id="status">
                                <option value="">Semua</option>
                                <option value="hadir" <?= $status == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                <option value="sakit" <?= $status == 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                <option value="izin" <?= $status == 'izin' ? 'selected' : '' ?>>Izin</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bulan" class="form-label">Pilih Bulan</label>
                            <input type="month" class="form-control" name="bulan" id="bulan" value="<?= htmlspecialchars($bulan) ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                            <a href="cetak_kehadiran.php?status=<?= urlencode($status) ?>&bulan=<?= urlencode($bulan) ?>" target="_blank" class="btn btn-outline-secondary">
                                <i class="fe fe-printer"></i> Cetak
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Grafik Kehadiran -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Jumlah Kehadiran per Bulan (Januari–Desember)</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartKehadiran" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel Kehadiran -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Data Kehadiran</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anggota</th>
                                    <th>Judul Rapat</th>
                                    <th>Status Kehadiran</th>
                                    <th>Tanggal Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_anggota']) ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                            <td><?= htmlspecialchars($row['status_kehadiran']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal_kehadiran']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data kehadiran.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('chartKehadiran').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Kehadiran',
                    data: <?= json_encode($jumlahAktivitas) ?>,
                    backgroundColor: [
                        '#007bff', '#6610f2', '#6f42c1', '#e83e8c',
                        '#fd7e14', '#ffc107', '#28a745', '#20c997',
                        '#17a2b8', '#6c757d', '#343a40', '#dc3545'
                    ],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Grafik Jumlah Kehadiran Rapat per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.y} kehadiran`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>