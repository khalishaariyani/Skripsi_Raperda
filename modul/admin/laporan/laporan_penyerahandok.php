<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Penyerahan Dokumen';
$bulan = $_GET['bulan'] ?? '';

// Query data utama
$query = "
    SELECT *
    FROM penyerahan_dokumen
    WHERE 1=1
";
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(tanggal_penyerahan, '%Y-%m') = '$bulan'";
}
$query .= " ORDER BY tanggal_penyerahan DESC";
$result = $conn->query($query);

// Data grafik per bulan
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
$jumlahPenyerahan = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(tanggal_penyerahan) AS bulan_ke, COUNT(*) AS total
    FROM penyerahan_dokumen
    GROUP BY bulan_ke
");
while ($g = $grafik->fetch_assoc()) {
    $index = (int)$g['bulan_ke'] - 1;
    $jumlahPenyerahan[$index] = (int)$g['total'];
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


                <!-- Filter -->
                <form method="GET" class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <label for="bulan" class="form-label">Pilih Bulan</label>
                        <input type="month" class="form-control" name="bulan" id="bulan" value="<?= htmlspecialchars($bulan) ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2 align-items-end">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                        <a href="cetak_penyerahandok.php?bulan=<?= urlencode($bulan) ?>" target="_blank" class="btn btn-outline-secondary">
                            <i class="fe fe-printer"></i> Cetak
                        </a>
                    </div>
                </form>

                <!-- Grafik -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Penyerahan Dokumen per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartPenyerahan" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card shadow-sm">
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Penerima</th>
                                    <th>File Dokumen</th>
                                    <th>Tanggal Penyerahan</th>
                                    <th>Waktu Upload</th>
                                    <th>Lihat File</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama_penerima']) ?></td>
                                            <td><?= htmlspecialchars($row['file_dokumen']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal_penyerahan']) ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
                                            <td class="text-center">
                                                <a href="/raperda/uploads/penyerahan/<?= urlencode($row['file_dokumen']) ?>" target="_blank" class="btn btn-sm btn-outline-info">Lihat File</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada data penyerahan ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
    </div>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('chartPenyerahan').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Penyerahan',
                    data: <?= json_encode($jumlahPenyerahan) ?>,
                    backgroundColor: '#20c997',
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
                        text: 'Jumlah Penyerahan Dokumen per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} dokumen`
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