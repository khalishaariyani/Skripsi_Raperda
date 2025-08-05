<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Penyerahan Dokumen';

$query = "
    SELECT p.*, j.judul_rapat 
    FROM penyerahan_dokumen p
    JOIN arsiprapat a ON p.id_arsip = a.id
    JOIN jadwalrapat j ON a.id_rapat = j.id
    ORDER BY p.tanggal_penyerahan DESC
";
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
                <div id="tombolTambahPenyerahan">
                    <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                        <i class="fe fe-plus"></i> Tambah Penyerahan
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0 table-striped" id="tabel-penyerahan">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Judul Arsip</th>
                                    <th>Nama Penerima</th>
                                    <th>Dokumen</th>
                                    <th>Tanggal Penyerahan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_penerima']) ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($row['file_dokumen'])): ?>
                                                <a href="<?= BASE_URL ?>/uploads/penyerahan/<?= htmlspecialchars($row['file_dokumen']) ?>"
                                                    target="_blank"
                                                    class="badge bg-info text-white text-decoration-none shadow-sm">
                                                    <i class="fe fe-file-text"></i> Lihat File
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">Tidak ada file</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $row['tanggal_penyerahan'] ? date('d/m/Y', strtotime($row['tanggal_penyerahan'])) : '-' ?></td>
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
                                                <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus data ini?')"
                                                   class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus">
                                                    <i class="fe fe-trash"></i>
                                                </a>
                                            </div>

                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title">Detail Penyerahan Dokumen</h5>
                                                            <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Judul Arsip:</strong> <?= htmlspecialchars($row['judul_rapat']) ?></p>
                                                            <p><strong>Nama Penerima:</strong> <?= htmlspecialchars($row['nama_penerima']) ?></p>
                                                            <p><strong>Tanggal Penyerahan:</strong> <?= date('d/m/Y', strtotime($row['tanggal_penyerahan'])) ?></p>
                                                            <p><strong>Dokumen:</strong></p>
                                                            <?php if (!empty($row['file_dokumen'])): ?>
                                                                <?php
                                                                $fileUrl = BASE_URL . '/uploads/penyerahan/' . rawurlencode($row['file_dokumen']);
                                                                $ext = strtolower(pathinfo($row['file_dokumen'], PATHINFO_EXTENSION));
                                                                ?>
                                                                <?php if ($ext === 'pdf'): ?>
                                                                    <iframe src="<?= $fileUrl ?>" frameborder="0" width="100%" height="600px"></iframe>
                                                                <?php else: ?>
                                                                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary">
                                                                        <i class="fe fe-download"></i> Lihat/Unduh Dokumen
                                                                    </a>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <p class="text-muted fst-italic">Tidak ada file dokumen.</p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Modal -->
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
        $('#tabel-penyerahan').DataTable({
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

        $('#tombolTambahPenyerahan').appendTo('#tabel-penyerahan_wrapper .dataTables_filter').addClass('ms-3');
        $('#tabel-penyerahan_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
    });
</script>
</body>
</html>
