<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Perda';

$perdas = $conn->query("SELECT * FROM perda ORDER BY tanggal_masuk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />

<body>
<div class="page">
    <?php require_once LAYOUT_PATH . '/header.php'; ?>
    <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

    <main class="main-content app-content mt-4">
        <div class="container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <h4 class="fw-bold mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
                <div id="tombolTambahPerda">
                    <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                        <i class="fe fe-plus"></i> Tambah Perda
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
                <div class="alert alert-success">Data perda berhasil ditambahkan.</div>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success">Data perda berhasil dihapus.</div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0" id="tabel-perda">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Perda</th>
                                    <th>Tanggal Rapat</th>
                                    <th>Status</th>
                                    <th>Pengusul</th>
                                    <th>Judul</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                while ($row = $perdas->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nomor_perda']) ?></td>
                                        <td><?= htmlspecialchars($row['tanggal_masuk']) ?></td>
                                        <td><?= htmlspecialchars($row['status']) ?></td>
                                        <td><?= htmlspecialchars($row['pengusul']) ?></td>
                                        <td><?= htmlspecialchars($row['judul']) ?></td>
                                        <td><?= htmlspecialchars($row['catatan']) ?></td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                <button class="btn btn-sm btn-outline-info rounded-circle shadow-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['idPerda'] ?>" title="Detail">
                                                    <i class="fe fe-eye"></i>
                                                </button>
                                                <a href="edit.php?id=<?= $row['idPerda'] ?>" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $row['idPerda'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus">
                                                    <i class="fe fe-trash"></i>
                                                </a>
                                                <a href="cetak.php?idPerda=<?= $row['idPerda'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm" title="Cetak">
                                                    <i class="fe fe-printer"></i>
                                                </a>
                                            </div>

                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal<?= $row['idPerda'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-info text-white">
                                                            <h5 class="modal-title">Detail Perda</h5>
                                                            <button type="button" class="btn-close text-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p><strong>Nomor Perda:</strong> <?= htmlspecialchars($row['nomor_perda']) ?></p>
                                                            <p><strong>Tanggal Rapat:</strong> <?= htmlspecialchars($row['tanggal_masuk']) ?></p>
                                                            <p><strong>Status:</strong> <?= htmlspecialchars($row['status']) ?></p>
                                                            <p><strong>Pengusul:</strong> <?= htmlspecialchars($row['pengusul']) ?></p>
                                                            <p><strong>Judul:</strong> <?= htmlspecialchars($row['judul']) ?></p>
                                                            <p><strong>Catatan:</strong><br><?= nl2br(htmlspecialchars($row['catatan'])) ?></p>
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
        $('#tabel-perda').DataTable({
            language: {
                lengthMenu: "Tampilkan _MENU_ perda per halaman",
                zeroRecords: "Tidak ada perda ditemukan",
                info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                infoEmpty: "Tidak ada data tersedia",
                infoFiltered: "(difilter dari _MAX_ total perda)",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $('#tombolTambahPerda').appendTo('#tabel-perda_wrapper .dataTables_filter').addClass('ms-3');
        $('#tabel-perda_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
    });
</script>
</body>
</html>
