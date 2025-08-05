<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Saran & Revisi (Diskusi Perda)';
$bulan = $_GET['bulan'] ?? '';

// Query utama data diskusi
$query = "
    SELECT d.*, r.judul_rapat, u.nama 
    FROM diskusiperda d
    JOIN jadwalrapat r ON d.idRapat = r.id
    JOIN user u ON d.idPengguna = u.id
    WHERE 1=1
";
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(d.tanggalKomentar, '%Y-%m') = '$bulan'";
}
$query .= " ORDER BY d.tanggalKomentar DESC";
$result = $conn->query($query);

// Data grafik: total komentar per bulan
$bulanLabel = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$jumlahKomentar = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(tanggalKomentar) AS bulan_ke, COUNT(*) AS total
    FROM diskusiperda
    GROUP BY bulan_ke
");
while ($g = $grafik->fetch_assoc()) {
    $i = (int)$g['bulan_ke'] - 1;
    $jumlahKomentar[$i] = (int)$g['total'];
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
                            <a href="cetak_revisi.php?bulan=<?= urlencode($bulan) ?>" target="_blank" class="btn btn-outline-secondary">
                                <i class="fe fe-printer"></i> Cetak
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Grafik -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Jumlah Komentar per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartKomentar"></canvas>
                    </div>
                </div>

                <!-- Tabel Revisi / Diskusi -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Data Saran & Revisi (Diskusi Perda)</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Pengusul</th>
                                    <th>Komentar</th>
                                    <th>Tanggal Komentar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= htmlspecialchars($row['isiKomentar']) ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($row['tanggalKomentar'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada saran atau revisi ditemukan.</td>
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
        const ctx = document.getElementById('chartKomentar').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Komentar',
                    data: <?= json_encode($jumlahKomentar) ?>,
                    backgroundColor: '#e83e8c',
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
                        text: 'Jumlah Saran & Revisi per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} komentar`
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