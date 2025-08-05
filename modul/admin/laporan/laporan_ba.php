<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Berita Acara';

$bulan = $_GET['bulan'] ?? '';
$pengusul = $_GET['pengusul'] ?? '';

// Query data untuk tabel
$query = "SELECT * FROM perda WHERE 1=1";
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(tanggal_masuk, '%Y-%m') = '$bulan'";
}
if (!empty($pengusul)) {
    $query .= " AND pengusul LIKE '%$pengusul%'";
}
$query .= " ORDER BY tanggal_masuk DESC";
$result = $conn->query($query);

// Query data grafik berita acara per bulan
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
$jumlahPerBulan = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(tanggal_masuk) AS bulan_ke, COUNT(*) AS total
    FROM perda
    GROUP BY bulan_ke
");
while ($g = $grafik->fetch_assoc()) {
    $index = (int)$g['bulan_ke'] - 1;
    $jumlahPerBulan[$index] = (int)$g['total'];
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
                <form method="GET" class="card card-body mb-4 shadow-sm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="pengusul" class="form-label">Pengusul</label>
                            <input type="text" class="form-control" name="pengusul" id="pengusul" value="<?= htmlspecialchars($pengusul) ?>" placeholder="Masukkan nama pengusul...">
                        </div>
                        <div class="col-md-4">
                            <label for="bulan" class="form-label">Bulan</label>
                            <input type="month" class="form-control" name="bulan" id="bulan" value="<?= htmlspecialchars($bulan) ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                        </div>
                    </div>
                </form>

                <!-- Grafik -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Jumlah Berita Acara per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartBA" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Data Berita Acara</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Perda</th>
                                    <th>Tanggal Rapat</th>
                                    <th>Status</th>
                                    <th>Pengusul</th>
                                    <th>Judul</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nomor_perda']) ?></td>
                                            <td><?= date('d-m-Y', strtotime($row['tanggal_masuk'])) ?></td>
                                            <td><?= htmlspecialchars($row['status']) ?></td>
                                            <td><?= htmlspecialchars($row['pengusul']) ?></td>
                                            <td><?= htmlspecialchars($row['judul']) ?></td>
                                            <td><?= htmlspecialchars($row['catatan']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Tidak ada data berita acara.</td>
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
        const ctx = document.getElementById('chartBA').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Berita Acara',
                    data: <?= json_encode($jumlahPerBulan) ?>,
                    backgroundColor: '#ff7f50',
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
                        text: 'Jumlah Berita Acara per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} berita acara`
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