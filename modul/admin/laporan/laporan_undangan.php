<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Undangan Rapat';
$bulan = $_GET['bulan'] ?? '';

// Query Data Utama
$query = "
SELECT 
    u.*, 
    j.judul_rapat 
FROM undanganrapat u
JOIN jadwalrapat j ON u.idRapat = j.id
WHERE 1=1
";

if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(u.tanggal, '%Y-%m') = '$bulan'";
}

$query .= " ORDER BY u.tanggal DESC";
$result = $conn->query($query);

// Query Grafik: Jumlah Undangan per Bulan
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
$jumlahUndangan = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(tanggal) AS bulan_ke, COUNT(*) AS total
    FROM undanganrapat
    GROUP BY bulan_ke
");

while ($g = $grafik->fetch_assoc()) {
    $index = (int)$g['bulan_ke'] - 1;
    $jumlahUndangan[$index] = (int)$g['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-6">
            <div class="container-fluid pt-2">
                <h4 class="fw-bold mb-4 mt-4"><?= $pageTitle ?></h4>


                <!-- Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="bulan" class="form-label">Filter Bulan</label>
                        <input type="month" name="bulan" id="bulan" class="form-control" value="<?= htmlspecialchars($bulan) ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                        <a href="cetak_undangan.php?bulan=<?= urlencode($bulan) ?>" target="_blank" class="btn btn-outline-secondary">
                            <i class="fe fe-printer"></i> Cetak
                        </a>
                    </div>
                </form>

                <!-- Grafik -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Jumlah Undangan Rapat per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartUndangan" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Data Undangan Rapat</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Penerima</th>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                            <td><?= htmlspecialchars($row['penerima']) ?></td>
                                            <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($row['jam']) ?></td>
                                            <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada data undangan ditemukan.</td>
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
        const ctx = document.getElementById('chartUndangan').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Undangan',
                    data: <?= json_encode($jumlahUndangan) ?>,
                    backgroundColor: '#fd7e14',
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
                        text: 'Jumlah Undangan Rapat per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} undangan`
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