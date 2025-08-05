<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

$pageTitle = 'Manajemen Kehadiran Rapat';

if (!in_array($_SESSION['role'], ['admin', 'persidangan', 'anggota'])) {
    header("Location: " . BASE_URL . "/index.php?msg=unauthorized");
    exit;
}

$role = $_SESSION['role'];
$id_user = $_SESSION['id'];

$query = "
    SELECT kh.id, kh.id_rapat, j.judul_rapat, u.nama AS nama_anggota, kh.status, kh.waktu_hadir
    FROM kehadiranrapat kh
    JOIN jadwalrapat j ON kh.id_rapat = j.id
    JOIN user u ON kh.id_user = u.id
";

if ($role === 'anggota') {
    $query .= " WHERE kh.id_user = $id_user";
}

$query .= " ORDER BY j.tanggal ASC, kh.waktu_hadir DESC";

$result = $conn->query($query) or die($conn->error);
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
                <div id="tombolTambahKehadiran">
                    <?php if (in_array($role, ['anggota', 'persidangan', 'admin'])): ?>
                        <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                            <i class="fe fe-plus"></i> Tambah Kehadiran
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle table-striped" id="kehadiranTable">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th>Judul Rapat</th>
                                    <th>Nama Anggota</th>
                                    <th>Status</th>
                                    <th>Waktu Hadir</th>
                                    <?php if (in_array($role, ['admin', 'persidangan'])): ?>
                                        <th class="text-center" style="width:140px;">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_anggota']) ?></td>
                                        <td>
                                            <?php
                                            $status = strtolower($row['status']);
                                            $badge = match ($status) {
                                                'hadir' => 'badge bg-success',
                                                'izin' => 'badge bg-warning text-dark',
                                                'sakit' => 'badge bg-info',
                                                default => 'badge bg-secondary'
                                            };
                                            ?>
                                            <span class="<?= $badge ?>"><?= ucfirst($status) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($row['waktu_hadir']) ?></td>
                                        <?php if (in_array($role, ['admin', 'persidangan'])): ?>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <?php if ($role === 'admin'): ?>
                                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus"
                                                           onclick="return confirm('Yakin ingin menghapus kehadiran ini?')">
                                                            <i class="fe fe-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#kehadiranTable').DataTable({
            language: {
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Tidak ada data ditemukan",
                info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                infoEmpty: "Tidak ada kehadiran tersedia",
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

        $('#tombolTambahKehadiran').appendTo('#kehadiranTable_wrapper .dataTables_filter').addClass('ms-3');
        $('#kehadiranTable_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
    });
</script>
</body>
</html>
