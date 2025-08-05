<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan', 'anggota'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$role = $_SESSION['role'];
$pageTitle = 'Undangan Rapat';

$query = "
    SELECT u.id, j.judul_rapat, u.penerima, u.tanggal, u.jam, u.lokasi
    FROM undanganrapat u
    JOIN jadwalrapat j ON u.idRapat = j.id
    ORDER BY j.judul_rapat ASC, u.tanggal DESC
";
$result = $conn->query($query) or die($conn->error);

$grouped = [];
while ($row = $result->fetch_assoc()) {
    $grouped[$row['judul_rapat']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />

<body class="d-flex flex-column min-vh-100">
<div class="page d-flex flex-column flex-grow-1">
    <?php require_once LAYOUT_PATH . '/header.php'; ?>
    <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

    <main class="main-content app-content">
        <div class="container-fluid py-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
                <?php if (in_array($role, ['admin', 'persidangan'])): ?>
                    <div id="tombolTambahUndangan">
                        <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                            <i class="fe fe-plus"></i> Tambah Undangan
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (count($grouped) > 0): ?>
                        <?php $groupId = 1; ?>
                        <?php foreach ($grouped as $judul => $undanganList): ?>
                            <div class="mb-5">
                                <h6 class="fw-bold mb-3 text-uppercase"><?= htmlspecialchars($judul) ?></h6>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0 table-striped undangan-tabel" id="undanganTable<?= $groupId ?>">
                                        <thead class="table-primary text-center">
                                            <tr>
                                                <th style="width:50px;">No</th>
                                                <th>Penerima</th>
                                                <th>Tanggal</th>
                                                <th>Jam</th>
                                                <th>Lokasi</th>
                                                <?php if ($role === 'admin'): ?>
                                                    <th style="width:160px;">Aksi</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no = 1; foreach ($undanganList as $row): ?>
                                                <tr>
                                                    <td class="text-center"><?= $no++ ?></td>
                                                    <td><?= $row['penerima'] ? htmlspecialchars($row['penerima']) : '<em>Belum ada</em>' ?></td>
                                                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                                    <td class="text-center"><?= htmlspecialchars($row['jam']) ?></td>
                                                    <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                                    <?php if ($role === 'admin'): ?>
                                                        <td class="text-center">
                                                            <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                                <a href="kirim.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info rounded-pill shadow-sm" title="Kirim">
                                                                    <i class="fe fe-send"></i>
                                                                </a>
                                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                                    <i class="fe fe-edit"></i>
                                                                </a>
                                                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus"
                                                                   onclick="return confirm('Yakin ingin menghapus undangan ini?')">
                                                                    <i class="fe fe-trash"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php $groupId++; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">Belum ada undangan rapat.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php require_once LAYOUT_PATH . '/footer.php'; ?>
</div>

<?php require_once LAYOUT_PATH . '/scripts.php'; ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        // Aktifkan DataTables untuk setiap tabel undangan
        $('table.undangan-tabel').each(function () {
            $(this).DataTable({
                language: {
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Tidak ada data ditemukan",
                    info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        });

        // Geser tombol ke samping search dari tabel pertama
        const tableFilter = $('#undanganTable1_wrapper .dataTables_filter');
        $('#tombolTambahUndangan').appendTo(tableFilter).addClass('ms-3');
        tableFilter.addClass('d-flex align-items-center justify-content-between mb-3');
    });
</script>
</body>
</html>
