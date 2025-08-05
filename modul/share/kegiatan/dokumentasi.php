<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$role = $_SESSION['role'];
$pageTitle = 'Dokumentasi Kegiatan';

$query = "
    SELECT d.*, j.judul_rapat
    FROM dokumentasikegiatan d
    JOIN jadwalrapat j ON d.idRapat = j.id
    ORDER BY d.id DESC
";
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
                <div id="tombolTambahDokumentasi">
                    <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                        <i class="fe fe-plus"></i> Tambah Dokumentasi
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 table-striped" id="tabel-dokumentasi">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Keterangan</th>
                                    <th>Diunggah Oleh</th>
                                    <th>Waktu</th>
                                    <th>File</th>
                                    <th class="text-center" style="width:180px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                while ($row = $result->fetch_assoc()):
                                    $files = !empty($row['file']) ? explode('|', $row['file']) : [];
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                        <td><?= htmlspecialchars($row['keterangan']) ?></td>
                                        <td><?= htmlspecialchars($row['diunggah_oleh']) ?></td>
                                        <td><?= $row['created_at'] ? date('d/m/Y H:i:s', strtotime($row['created_at'])) : '-' ?></td>
                                        <td>
                                            <?php if (count($files) > 0): ?>
                                                <?php foreach ($files as $file): ?>
                                                    <?php $fileUrl = BASE_URL . '/uploads/dokumentasi/' . urlencode($file); ?>
                                                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-info mb-1 d-block">
                                                        <i class="fe fe-file"></i> Lihat
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
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
                                                <?php if ($role === 'admin'): ?>
                                                    <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus dokumentasi ini?')"
                                                       class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus">
                                                        <i class="fe fe-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title">Detail Dokumentasi</h5>
                                                            <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <strong>Judul Rapat:</strong> <?= htmlspecialchars($row['judul_rapat']) ?><br>
                                                                <strong>Diunggah Oleh:</strong> <?= htmlspecialchars($row['diunggah_oleh']) ?><br>
                                                                <strong>Waktu:</strong> <?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?><br>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Keterangan:</strong>
                                                                <p><?= nl2br(htmlspecialchars($row['keterangan'])) ?></p>
                                                            </div>
                                                            <div>
                                                                <strong>File Dokumentasi:</strong><br>
                                                                <?php foreach ($files as $file): ?>
                                                                    <?php
                                                                    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/dokumentasi/' . $file;
                                                                    $fileUrl = BASE_URL . '/uploads/dokumentasi/' . urlencode($file);
                                                                    ?>
                                                                    <?php if (file_exists($filePath) && preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)): ?>
                                                                        <img src="<?= $fileUrl ?>" class="img-fluid rounded shadow-sm border mb-3 d-block" style="max-height:400px;">
                                                                    <?php elseif (file_exists($filePath)): ?>
                                                                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary mb-2">
                                                                            <i class="fe fe-download"></i> Unduh <?= htmlspecialchars($file) ?>
                                                                        </a><br>
                                                                    <?php else: ?>
                                                                        <div class="alert alert-warning mb-2">
                                                                            File <code><?= htmlspecialchars($file) ?></code> tidak ditemukan.
                                                                        </div>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
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
        $('#tabel-dokumentasi').DataTable({
            language: {
                lengthMenu: "Tampilkan _MENU_ dokumentasi per halaman",
                zeroRecords: "Tidak ada dokumentasi ditemukan",
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

        $('#tombolTambahDokumentasi').appendTo('#tabel-dokumentasi_wrapper .dataTables_filter').addClass('ms-3');
        $('#tabel-dokumentasi_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
    });
</script>
</body>
</html>
