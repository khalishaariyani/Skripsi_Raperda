<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Arsip Rapat';
$bulan = $_GET['bulan'] ?? '';

// Ambil data untuk tabel
$query = "
SELECT
    a.id,
    j.judul_rapat,
    j.tanggal AS tanggal_rapat,
    a.nama_file,
    u.nama AS uploader,
    a.tanggal_upload
FROM arsiprapat a
JOIN jadwalrapat j ON a.id_rapat = j.id
JOIN user u ON a.diunggah_oleh = u.id
WHERE 1=1
";
if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(a.tanggal_upload, '%Y-%m') = '$bulan'";
}
$query .= " ORDER BY a.tanggal_upload DESC";
$result = $conn->query($query);

// Ambil data untuk grafik
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
$jumlahArsip = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(tanggal_upload) AS bulan_ke, COUNT(*) AS total
    FROM arsiprapat
    GROUP BY bulan_ke
");
while ($g = $grafik->fetch_assoc()) {
    $index = (int)$g['bulan_ke'] - 1;
    $jumlahArsip[$index] = (int)$g['total'];
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
                            <label for="bulan" class="form-label">Pilih Bulan</label>
                            <input type="month" class="form-control" name="bulan" id="bulan" value="<?= htmlspecialchars($bulan) ?>">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                            <a href="cetak_arsip.php?bulan=<?= urlencode($bulan) ?>" target="_blank" class="btn btn-outline-secondary">
                                <i class="fe fe-printer"></i> Cetak
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Grafik Arsip -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Arsip Rapat per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartArsip" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card">
                    <div class="card-header bg-primary text-white fw-bold">Data Arsip Rapat</div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Tanggal Rapat</th>
                                    <th>Nama File Arsip</th>
                                    <th>Diunggah Oleh</th>
                                    <th>Tanggal Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                if ($result && $result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal_rapat']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_file']) ?></td>
                                            <td><?= htmlspecialchars($row['uploader']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal_upload']) ?></td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data arsip ditemukan.</td>
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
        const ctx = document.getElementById('chartArsip').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Arsip',
                    data: <?= json_encode($jumlahArsip) ?>,
                    backgroundColor: '#28a745',
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
                        text: 'Grafik Jumlah Arsip Rapat per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} arsip`
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