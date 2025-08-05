<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Proteksi role hanya admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Laporan Revisi';

// Data revisi
$query = "
    SELECT r.*, j.judul_rapat
    FROM laporanrevisi r
    JOIN jadwalrapat j ON r.idRapat = j.id
    ORDER BY r.tanggal_masuk DESC
";
$result = $conn->query($query);
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

                <!-- Judul -->
                <div class="mb-3">
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($pageTitle) ?></h4>
                    <p class="text-muted mb-0">Kelola, lihat & revisi Perda di sini.</p>
                </div>

                <!-- Card Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <!-- Search + Tambah -->
                        <div class="row g-2 justify-content-between align-items-center mb-4">
                            <div class="col-md-6"></div>
                            <div class="col-md-6 d-flex justify-content-end gap-2">
                                <input type="text" id="searchBox" class="form-control rounded-pill shadow-sm w-50" placeholder="🔍 Cari Revisi...">
                                <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                                    <i class="fe fe-plus"></i> Tambah Revisi
                                </a>
                            </div>
                        </div>

                        <!-- Tabel -->
                        <!-- Tabel -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0 table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Judul Rapat</th>
                                        <th>Pengusul</th>
                                        <th>Jenis Revisi</th>
                                        <th>Isi Revisi</th>
                                        <th>Tanggal Masuk</th>
                                        <th class="text-center" style="width:150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                                <td><?= htmlspecialchars($row['pengusul']) ?></td>
                                                <td><?= htmlspecialchars($row['jenis_revisi']) ?></td>
                                                <td><?= nl2br(htmlspecialchars($row['isi_revisi'])) ?></td>
                                                <td><?= date('d/m/Y', strtotime($row['tanggal_masuk'])) ?></td>
                                                <td class="text-center">
                                                    <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                            <i class="fe fe-edit"></i>
                                                        </a>
                                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus"
                                                            onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                            <i class="fe fe-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Belum ada data revisi.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>


                    </div>
                </div>

            </div>
        </main>

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
    </div>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
    <script>
        document.getElementById('searchBox').addEventListener('input', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    </script>

</body>

</html>