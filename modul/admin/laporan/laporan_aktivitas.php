<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Aktivitas Pembentukan Perda';
$bulan = $_GET['bulan'] ?? '';

$filterClause = "";
if (!empty($bulan)) {
    $filterClause = " AND DATE_FORMAT(j.tanggal, '%Y-%m') = '$bulan'";
}

$rapats = $conn->query("SELECT * FROM jadwalrapat j WHERE 1=1 $filterClause ORDER BY j.tanggal DESC");

$data = [];
while ($rapat = $rapats->fetch_assoc()) {
    $id_rapat = $rapat['id'];
    $judul = $rapat['judul_rapat'];
    $tanggal = $rapat['tanggal'];

    $data[] = ['judul' => $judul, 'tanggal' => $tanggal, 'jenis' => 'Jadwal', 'keterangan' => 'Tempat: ' . $rapat['tempat']];

    $dok = $conn->query("SELECT COUNT(*) as total FROM dok_rapat WHERE id_rapat = $id_rapat")->fetch_assoc();
    if ($dok['total'] > 0) {
        $data[] = ['judul' => $judul, 'tanggal' => $tanggal, 'jenis' => 'Dokumen Rapat', 'keterangan' => $dok['total'] . ' file diunggah'];
    }

    $notulen = $conn->query("SELECT * FROM notulen WHERE id_rapat = $id_rapat")->fetch_assoc();
    if ($notulen) {
        $data[] = ['judul' => $judul, 'tanggal' => $tanggal, 'jenis' => 'Notulen', 'keterangan' => 'Oleh: ' . $notulen['diinput_oleh']];
    }

    $doks = $conn->query("SELECT COUNT(*) as total FROM dokumentasikegiatan WHERE idRapat = $id_rapat")->fetch_assoc();
    if ($doks['total'] > 0) {
        $data[] = ['judul' => $judul, 'tanggal' => $tanggal, 'jenis' => 'Dokumentasi', 'keterangan' => $doks['total'] . ' file'];
    }

    $pen = $conn->query("SELECT * FROM penyerahan_dokumen WHERE id = $id_rapat")->fetch_assoc();
    if ($pen) {
        $data[] = ['judul' => $judul, 'tanggal' => $pen['tanggal'], 'jenis' => 'Penyerahan', 'keterangan' => 'Kepada: ' . $pen['penerima']];
    }

    $ba = $conn->query("SELECT * FROM perda WHERE idPerda = $id_rapat")->fetch_assoc();
    if ($ba) {
        $data[] = ['judul' => $judul, 'tanggal' => $ba['tanggal_masuk'], 'jenis' => 'Berita Acara', 'keterangan' => 'Status: ' . $ba['status']];
    }
}

// Grafik aktivitas per bulan
$bulanLabel = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$jumlahAktivitas = array_fill(0, 12, 0);

$grafik = $conn->query("
    SELECT MONTH(tanggal_fix) AS bulan_ke FROM (
        SELECT tanggal AS tanggal_fix FROM jadwalrapat
        UNION ALL SELECT created_at AS tanggal_fix FROM dokumentasikegiatan
        UNION ALL SELECT tanggal_input AS tanggal_fix FROM notulen
        UNION ALL SELECT tanggal_upload AS tanggal_fix FROM dok_rapat
        UNION ALL SELECT tanggal_penyerahan AS tanggal_fix FROM penyerahan_dokumen
        UNION ALL SELECT tanggal_masuk AS tanggal_fix FROM perda
    ) AS gabungan
");


while ($g = $grafik->fetch_assoc()) {
    $i = (int)$g['bulan_ke'] - 1;
    $jumlahAktivitas[$i]++;
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
                        <label for="bulan" class="form-label">Filter Bulan</label>
                        <input type="month" class="form-control" name="bulan" id="bulan" value="<?= htmlspecialchars($bulan) ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button class="btn btn-primary">Tampilkan</button>
                        <a href="cetak_aktivitas.php?bulan=<?= urlencode($bulan) ?>" class="btn btn-outline-secondary" target="_blank">
                            <i class="fe fe-printer"></i> Cetak
                        </a>
                    </div>
                </form>

                <!-- Grafik -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Grafik Aktivitas per Bulan</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartAktivitas"></canvas>
                    </div>
                </div>

                <!-- Tabel Ringkasan -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <strong>Ringkasan Kegiatan</strong>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Tanggal</th>
                                    <th>Jenis Kegiatan</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($data) > 0): $no = 1; ?>
                                    <?php foreach ($data as $row): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                            <td><?= htmlspecialchars($row['jenis']) ?></td>
                                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada aktivitas ditemukan.</td>
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
        const ctx = document.getElementById('chartAktivitas').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulanLabel) ?>,
                datasets: [{
                    label: 'Jumlah Aktivitas',
                    data: <?= json_encode($jumlahAktivitas) ?>,
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
                        text: 'Jumlah Aktivitas Pembentukan Perda per Bulan'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.parsed.y} aktivitas`
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