<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Dokumentasi Kegiatan';
$bulan = $_GET['bulan'] ?? '';

// Query utama tabel
$query = "
    SELECT d.*, r.judul_rapat
    FROM dokumentasikegiatan d
    JOIN jadwalrapat r ON r.id = d.idRapat
    WHERE 1=1
";
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(d.created_at, '%Y-%m') = '$bulan'";
}
$query .= " ORDER BY d.created_at DESC";
$result = $conn->query($query);

// Query grafik dokumentasi per bulan
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
$jumlahDokumentasi = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(created_at) AS bulan_ke, COUNT(*) AS total
    FROM dokumentasikegiatan
    GROUP BY bulan_ke
");

while ($g = $grafik->fetch_assoc()) {
    $index = (int)$g['bulan_ke'] - 1;
    $jumlahDokumentasi[$index] = (int)$g['total'];
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
                <form method="GET" class="card card-body mb-4 shadow-sm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="bulan" class="form-label">Filter Bulan</label>
                            <input type="month" name="bulan" id="bulan" class="form-control" value="<?= htmlspecialchars($bulan) ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                            <a href="cetak_dokumentasi.php?bulan=<?= urlencode($bulan) ?>" target="_blank" class="btn btn-outline-secondary">
                                <i class="fe fe-printer"></i> Cetak
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Grafik Dokumentasi -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Dokumentasi Kegiatan per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartDokumentasi" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel Dokumentasi -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Data Dokumentasi Kegiatan</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Keterangan</th>
                                    <th>Diunggah Oleh</th>
                                    <th>Tanggal Upload</th>
                                    <th>File</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                            <td><?= htmlspecialchars($row['diunggah_oleh']) ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
                                            <td>
                                                <a href="/raperda/uploads/dokumentasi/<?= urlencode($row['file']) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                    Lihat File
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada dokumentasi ditemukan.</td>
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
        const ctx = document.getElementById('chartDokumentasi').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Dokumentasi',
                    data: <?= json_encode($jumlahDokumentasi) ?>,
                    backgroundColor: '#17a2b8',
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
                        text: 'Jumlah Dokumentasi Kegiatan per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} dokumentasi`
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