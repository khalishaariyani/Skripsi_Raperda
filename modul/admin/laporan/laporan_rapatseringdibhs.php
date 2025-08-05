<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: /raperda/index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Rapat yang Sering Dibahas';
$bulan = $_GET['bulan'] ?? '';

// Query data
$query = "
    SELECT 
        judul_rapat,
        COUNT(*) AS jumlah_dibahas,
        MIN(tanggal) AS tanggal_pertama,
        MAX(tanggal) AS tanggal_terakhir
    FROM jadwalrapat
    WHERE 1=1
";
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan'";
}
$query .= " GROUP BY judul_rapat ORDER BY jumlah_dibahas DESC";

$result = $conn->query($query);

// Data untuk grafik
$grafikLabels = [];
$grafikData = [];

if ($result && $result->num_rows > 0) {
    $i = 0;
    foreach ($result as $r) {
        if ($i >= 10) break; // Tampilkan max 10 saja di grafik
        $grafikLabels[] = $r['judul_rapat'];
        $grafikData[] = $r['jumlah_dibahas'];
        $i++;
    }
    $result->data_seek(0); // Reset pointer untuk tabel
}
?>

<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-6">
            <div class="container-fluid pt-2">
                <h4 class="fw-bold mb-4 mt-4"><?= $pageTitle ?></h4>

                <!-- Filter Bulan -->
                <form method="GET" class="card card-body mb-4 shadow-sm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Filter Bulan</label>
                            <input type="month" name="bulan" class="form-control" value="<?= htmlspecialchars($bulan) ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                            <a href="cetak_rapatseringdibhs.php<?= !empty($bulan) ? '?bulan=' . urlencode($bulan) : '' ?>" target="_blank" class="btn btn-outline-secondary">
                                <i class="fe fe-printer"></i> Cetak
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Grafik -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Rapat Paling Sering Dibahas</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartRapat"></canvas>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Daftar Rapat yang Sering Dibahas</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Jumlah Dibahas</th>
                                    <th>Tanggal Pertama</th>
                                    <th>Tanggal Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                            <td class="text-center"><?= $row['jumlah_dibahas'] ?></td>
                                            <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_pertama'])) ?></td>
                                            <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal_terakhir'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data ditemukan.</td>
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
        const ctx = document.getElementById('chartRapat').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($grafikLabels) ?>,
                datasets: [{
                    label: 'Jumlah Dibahas',
                    data: <?= json_encode($grafikData) ?>,
                    backgroundColor: '#0d6efd',
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
                        text: '10 Judul Rapat yang Paling Sering Dibahas'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} kali`
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
                            text: 'Judul Rapat'
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>