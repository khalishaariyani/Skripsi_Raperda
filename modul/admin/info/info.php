<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan', 'anggota'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$role = $_SESSION['role'];
$pageTitle = 'Informasi';

$result = $conn->query("SELECT * FROM informasi ORDER BY tanggal DESC") or die($conn->error);
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
                <?php if ($role === 'admin'): ?>
                    <div id="tombolTambahInformasi">
                        <a href="add.php" class="btn btn-primary rounded-pill px-3 d-flex align-items-center gap-2 shadow-sm">
                            <i class="fe fe-plus"></i> Tambah Informasi
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] == 'added'): ?>
                    <div class="alert alert-success alert-dismissible fade show"><i class="fe fe-check-circle"></i> Informasi berhasil ditambahkan.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['msg'] == 'updated'): ?>
                    <div class="alert alert-info alert-dismissible fade show"><i class="fe fe-info"></i> Informasi berhasil diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-warning alert-dismissible fade show"><i class="fe fe-trash"></i> Informasi berhasil dihapus.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle mb-0" id="tabel-info">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Gambar</th>
                                    <th>Judul</th>
                                    <th>Deskripsi</th>
                                    <th>Tanggal</th>
                                    <?php if ($role === 'admin'): ?>
                                        <th class="text-center">Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <?php
                                            $gambarList = json_decode($row['gambar'], true);
                                            if (is_array($gambarList) && !empty($gambarList[0])): ?>
                                                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($gambarList[0]) ?>" alt="Gambar" class="img-thumbnail" style="max-height: 60px;">
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="detail.php?id=<?= $row['id'] ?>" class="fw-bold text-decoration-none">
                                                <?= htmlspecialchars($row['judul']) ?>
                                            </a>
                                        </td>
                                        <td style="white-space: pre-wrap;">
                                            <?= htmlspecialchars(substr(strip_tags($row['isi']), 0, 100)) ?>...
                                        </td>
                                        <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                        <?php if ($role === 'admin'): ?>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Yakin ingin menghapus informasi ini?')" title="Hapus">
                                                        <i class="fe fe-trash"></i>
                                                    </a>
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
</div>

<?php require_once LAYOUT_PATH . '/scripts.php'; ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#tabel-info').DataTable({
            language: {
                lengthMenu: "Tampilkan _MENU_ informasi per halaman",
                zeroRecords: "Tidak ada informasi ditemukan",
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

        $('#tombolTambahInformasi').appendTo('#tabel-info_wrapper .dataTables_filter').addClass('ms-3');
        $('#tabel-info_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
    });
</script>
</body>
</html>
