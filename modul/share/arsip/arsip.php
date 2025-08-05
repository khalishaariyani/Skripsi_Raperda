<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan', 'anggota'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Arsip Rapat';

$query = "SELECT a.*, j.judul_rapat, u.nama AS nama_uploader 
          FROM arsiprapat a
          JOIN jadwalrapat j ON a.id_rapat = j.id
          JOIN user u ON a.diunggah_oleh = u.id
          ORDER BY a.tanggal_upload DESC";
$result = $conn->query($query);
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
                <?php if (in_array($_SESSION['role'], ['admin', 'persidangan'])): ?>
                    <div id="tombolTambahArsip">
                        <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                            <i class="fe fe-plus"></i> Tambah Arsip
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 table-striped" id="tabel-arsip">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Nama File</th>
                                    <th>Uploader</th>
                                    <th>Tanggal Upload</th>
                                    <th>Unduh</th>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                        <th class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_file']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_uploader']) ?></td>
                                        <td class="text-center"><?= date("d M Y, H:i", strtotime($row['tanggal_upload'])) ?></td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>/uploads/arsip/<?= htmlspecialchars($row['file_path']) ?>" target="_blank"
                                               class="badge bg-info text-white text-decoration-none shadow-sm">
                                                <i class="fe fe-download"></i> Unduh
                                            </a>
                                        </td>
                                        <?php if ($_SESSION['role'] === 'admin'): ?>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                    <button class="btn btn-sm btn-outline-info rounded-circle shadow-sm"
                                                            title="Detail" data-bs-toggle="modal"
                                                            data-bs-target="#detailModal<?= $row['id'] ?>">
                                                        <i class="fe fe-eye"></i>
                                                    </button>
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm"
                                                       onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
                                                        <i class="fe fe-trash"></i>
                                                    </a>
                                                </div>

                                                <!-- Modal Detail -->
                                                <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">Detail Arsip Rapat</h5>
                                                                <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Judul Rapat:</strong> <?= htmlspecialchars($row['judul_rapat']) ?></p>
                                                                <p><strong>Nama File:</strong> <?= htmlspecialchars($row['nama_file']) ?></p>
                                                                <p><strong>Uploader:</strong> <?= htmlspecialchars($row['nama_uploader']) ?></p>
                                                                <p><strong>Tanggal Upload:</strong> <?= date('d/m/Y H:i:s', strtotime($row['tanggal_upload'])) ?></p>
                                                                <?php
                                                                $encodedFile = rawurlencode($row['file_path']);
                                                                $fileUrl = BASE_URL . '/uploads/arsip/' . $encodedFile;
                                                                $ext = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                                                                ?>
                                                                <?php if ($ext === 'pdf'): ?>
                                                                    <iframe src="<?= $fileUrl ?>" frameborder="0" width="100%" height="600px"></iframe>
                                                                <?php else: ?>
                                                                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary mt-2">
                                                                        <i class="fe fe-download"></i> Lihat/Unduh Arsip
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if ($result->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="<?= $_SESSION['role'] === 'admin' ? 7 : 6 ?>" class="text-center text-muted">
                                            Belum ada arsip.
                                        </td>
                                    </tr>
                                <?php endif; ?>
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
        $('#tabel-arsip').DataTable({
            language: {
                lengthMenu: "Tampilkan _MENU_ arsip per halaman",
                zeroRecords: "Tidak ada arsip ditemukan",
                info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                infoEmpty: "Tidak ada data tersedia",
                infoFiltered: "(difilter dari _MAX_ total arsip)",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $('#tombolTambahArsip').appendTo('#tabel-arsip_wrapper .dataTables_filter').addClass('ms-3');
        $('#tabel-arsip_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
    });
</script>
</body>
</html>
