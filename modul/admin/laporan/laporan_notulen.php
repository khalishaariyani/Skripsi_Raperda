<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: /raperda/index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Notulen Rapat';
$bulan = $_GET['bulan'] ?? '';

// Query data untuk tabel
$query = "
    SELECT n.*, r.judul_rapat, r.tanggal 
    FROM notulen n
    LEFT JOIN jadwalrapat r ON r.id = n.id_rapat
    WHERE 1=1
";
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(n.tanggal_input, '%Y-%m') = '$bulan'";
}
$query .= " ORDER BY n.tanggal_input DESC";
$result = $conn->query($query);

// Query data grafik
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
$jumlahNotulen = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(tanggal_input) AS bulan_ke, COUNT(*) AS total
    FROM notulen
    GROUP BY bulan_ke
");
while ($g = $grafik->fetch_assoc()) {
    $index = (int)$g['bulan_ke'] - 1;
    $jumlahNotulen[$index] = (int)$g['total'];
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

                <!-- Filter -->
                <form method="GET" class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Pilih Bulan</label>
                        <input type="month" name="bulan" class="form-control" value="<?= htmlspecialchars($bulan) ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Tampilkan</button>
                        <a href="cetak_notulen.php?bulan=<?= urlencode($bulan) ?>" class="btn btn-outline-secondary" target="_blank">
                            <i class="fe fe-printer"></i> Cetak
                        </a>
                    </div>
                </form>

                <!-- Grafik Notulen -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Notulen per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartNotulen" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel Notulen -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Data Notulen Rapat</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Isi Notulen</th>
                                    <th>Tanggal Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat'] ?? '-') ?></td>
                                            <td><?= nl2br(htmlspecialchars($row['ringkasan'])) ?></td>
                                            <td><?= date('d-m-Y H:i', strtotime($row['tanggal_input'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Tidak ada notulen ditemukan.</td>
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
        const ctx = document.getElementById('chartNotulen').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Notulen',
                    data: <?= json_encode($jumlahNotulen) ?>,
                    backgroundColor: '#f39c12',
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
                        text: 'Jumlah Notulen Rapat per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} notulen`
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