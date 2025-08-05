<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['admin', 'anggota', 'persidangan'])) {
    header("Location: " . BASE_URL . "/index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Dokumen Rapat';

$query = "
    SELECT d.*, j.judul_rapat, u.nama AS nama_pengunggah
    FROM dok_rapat d
    LEFT JOIN jadwalrapat j ON d.id_rapat = j.id
    LEFT JOIN user u ON d.diunggah_oleh = u.nama
    ORDER BY d.tanggal_upload DESC
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
                <?php if (in_array($role, ['admin', 'persidangan'])): ?>
                    <div id="tombolTambahDok">
                        <a href="add.php" class="btn btn-primary rounded-pill shadow-sm d-flex align-items-center gap-2">
                            <i class="fe fe-plus"></i> Tambah Dokumen
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 table-striped" id="tabel-dokrapat">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Rapat</th>
                                    <th>Nama Dokumen</th>
                                    <th>Diunggah Oleh</th>
                                    <th>Waktu Upload</th>
                                    <th>File</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['judul_rapat'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['nama_dokumen'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['nama_pengunggah'] ?? '-') ?></td>
                                        <td><?= $row['tanggal_upload'] ? date('d/m/Y', strtotime($row['tanggal_upload'])) : '-' ?></td>

                                        <!-- File Preview -->
                                        <td>
                                            <?php
                                            $files = explode('|', $row['file_dok']);
                                            foreach ($files as $i => $file):
                                                $file = trim($file);
                                                $fileUrl = BASE_URL . '/uploads/dok_rapat/' . urlencode($file);
                                                $modalId = 'previewFile' . $row['id'] . '_' . $i;
                                            ?>
                                                <button class="btn btn-sm btn-info d-block mb-1" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                                                    <i class="fe fe-eye"></i> Lihat
                                                </button>

                                                <!-- Modal -->
                                                <div class="modal fade" id="<?= $modalId ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-xl modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title">Preview Dokumen</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <?php if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)): ?>
                                                                    <img src="<?= $fileUrl ?>" class="img-fluid rounded shadow border" style="max-height:600px;">
                                                                <?php elseif (preg_match('/\.pdf$/i', $file)): ?>
                                                                    <iframe src="<?= $fileUrl ?>" width="100%" height="600px" style="border:1px solid #ccc;"></iframe>
                                                                <?php else: ?>
                                                                    <p class="text-muted">File tidak dapat dipreview. Unduh:</p>
                                                                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary">
                                                                        <i class="fe fe-download"></i> Unduh File
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </td>

                                        <td><?= htmlspecialchars($row['deskripsi'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                <button class="btn btn-sm btn-outline-info rounded-circle shadow-sm" title="Detail"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['id'] ?>">
                                                    <i class="fe fe-eye"></i>
                                                </button>
                                                <?php if (in_array($role, ['admin', 'persidangan'])): ?>
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($role === 'admin'): ?>
                                                    <a href="javascript:void(0)" onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')"
                                                    class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus">
                                                        <i class="fe fe-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title">Detail Dokumen</h5>
                                                            <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <p><strong>Judul Rapat:</strong> <?= htmlspecialchars($row['judul_rapat']) ?></p>
                                                            <p><strong>Nama Dokumen:</strong> <?= htmlspecialchars($row['nama_dokumen']) ?></p>
                                                            <p><strong>Diunggah Oleh:</strong> <?= htmlspecialchars($row['nama_pengunggah']) ?></p>
                                                            <p><strong>Waktu Upload:</strong> <?= date('d/m/Y', strtotime($row['tanggal_upload'])) ?></p>
                                                            <p><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
                                                            <div class="mt-4"><strong>File Dokumen:</strong><br>
                                                                <?php
                                                                foreach ($files as $file):
                                                                    $file = trim($file);
                                                                    $fileUrl = BASE_URL . '/uploads/dok_rapat/' . urlencode($file);
                                                                    if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)): ?>
                                                                        <img src="<?= $fileUrl ?>" class="img-fluid rounded shadow border mb-3 d-block" style="max-height:400px;">
                                                                    <?php elseif (preg_match('/\.pdf$/i', $file)): ?>
                                                                        <iframe src="<?= $fileUrl ?>" width="100%" height="500px" style="border:1px solid #ccc;" class="mb-3"></iframe>
                                                                    <?php else: ?>
                                                                        <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary mb-2">
                                                                            <i class="fe fe-download"></i> Unduh <?= htmlspecialchars($file) ?>
                                                                        </a><br>
                                                                <?php endif; endforeach; ?>
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

    <?php require_once LAYOUT_PATH . '/footer.php'; ?>
</div>

<?php require_once LAYOUT_PATH . '/scripts.php'; ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#tabel-dokrapat').DataTable({
            language: {
                lengthMenu: "Tampilkan _MENU_ dokumen per halaman",
                zeroRecords: "Tidak ada data ditemukan",
                info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                infoEmpty: "Tidak ada dokumen tersedia",
                infoFiltered: "(difilter dari _MAX_ total dokumen)",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $('#tombolTambahDok').appendTo('#tabel-dokrapat_wrapper .dataTables_filter').addClass('ms-3');
        $('#tabel-dokrapat_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
    });
</script>
</body>
</html>
