<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Anggota Dinas';

$instansi = $_POST['instansi'] ?? '';
$isAktif = $_POST['isAktif'] ?? '';

// Query filter data anggota
$query = "SELECT * FROM anggotadinas WHERE 1=1";
if (!empty($instansi)) {
    $query .= " AND jabatan LIKE '%$instansi%'";
}
if ($isAktif !== '') {
    $query .= " AND isAktif = " . intval($isAktif);
}
$query .= " ORDER BY jabatan, nama ASC";
$result = $conn->query($query);

// Query data untuk grafik (jumlah per instansi)
$grafik = $conn->query("
    SELECT jabatan, COUNT(*) AS total 
    FROM anggotadinas 
    " . ($isAktif !== '' ? "WHERE isAktif = " . intval($isAktif) : "") . "
    GROUP BY jabatan ORDER BY jabatan ASC
");

$instansiLabel = [];
$jumlahAnggota = [];
while ($g = $grafik->fetch_assoc()) {
    $instansiLabel[] = $g['jabatan'];
    $jumlahAnggota[] = $g['total'];
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
                <form method="POST" class="card card-body mb-4 shadow-sm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Instansi</label>
                            <input type="text" name="instansi" class="form-control" placeholder="Contoh: Dinas Pendidikan" value="<?= htmlspecialchars($instansi) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status Aktif</label>
                            <select name="isAktif" class="form-select">
                                <option value="">-- Semua --</option>
                                <option value="1" <?= $isAktif === '1' ? 'selected' : '' ?>>Aktif</option>
                                <option value="0" <?= $isAktif === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                            <a href="cetak_anggotadinas.php?instansi=<?= urlencode($instansi) ?>&isAktif=<?= urlencode($isAktif) ?>" target="_blank" class="btn btn-outline-secondary">
                                <i class="fe fe-printer"></i> Cetak
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Grafik -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">Jumlah Anggota per Instansi</div>
                    <div class="card-body" style="height: 250px;">
                        <canvas id="chartAnggota" height="60"></canvas>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white fw-bold">Data Anggota Dinas</div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Instansi</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['telepon']) ?></td>
                                            <td><?= $row['isAktif'] ? 'Aktif' : 'Tidak Aktif' ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data ditemukan.</td>
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
        const ctx = document.getElementById('chartAnggota').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($instansiLabel) ?>,
                datasets: [{
                    label: 'Jumlah Anggota',
                    data: <?= json_encode($jumlahAnggota) ?>,
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
                        text: 'Jumlah Anggota Dinas per Instansi'
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.parsed.y} anggota`
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
                            text: 'Instansi'
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>