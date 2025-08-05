<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: /raperda/index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Jadwal Rapat';
$bulan = $_GET['bulan'] ?? '';

// Query data tabel jadwal rapat
$query = "
    SELECT * FROM jadwalrapat
    WHERE 1=1
";
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan'";
}
$query .= " ORDER BY tanggal DESC";
$result = $conn->query($query);

// Query data grafik per bulan
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
$jumlahRapat = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(tanggal) AS bulan_ke, COUNT(*) AS total
    FROM jadwalrapat
    GROUP BY bulan_ke
");

while ($g = $grafik->fetch_assoc()) {
    $index = (int)$g['bulan_ke'] - 1;
    $jumlahRapat[$index] = (int)$g['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-6">
            <div class="container-fluid pt-2">
                <h4 class="fw-bold mb-4 mt-4"><?= $pageTitle ?></h4>


                <!-- Filter -->
                <form method="GET" class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <label for="bulan" class="form-label">Filter Bulan</label>
                        <input type="month" class="form-control" name="bulan" id="bulan" value="<?= htmlspecialchars($bulan) ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                        <a href="cetak_jadwal.php?bulan=<?= urlencode($bulan) ?>" target="_blank" class="btn btn-outline-secondary">
                            <i class="fe fe-printer"></i> Cetak
                        </a>
                    </div>
                </form>

                <!-- Grafik Jadwal -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Jumlah Rapat per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartJadwal" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel Jadwal -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Data Jadwal Rapat</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Tempat</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                            <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($row['waktu']) ?></td>
                                            <td><?= htmlspecialchars($row['tempat']) ?></td>
                                            <td class="text-capitalize"><?= htmlspecialchars($row['status']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada jadwal rapat ditemukan.</td>
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
        const ctx = document.getElementById('chartJadwal').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Rapat',
                    data: <?= json_encode($jumlahRapat) ?>,
                    backgroundColor: '#4e73df',
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
                        text: 'Jumlah Jadwal Rapat per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} rapat`
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