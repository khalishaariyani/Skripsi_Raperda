<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan', 'anggota'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Notulen Rapat';

$query = "
    SELECT n.*, j.judul_rapat, u.nama AS nama_penginput, u.role AS role_penginput
    FROM notulen n
    JOIN jadwalrapat j ON n.id_rapat = j.id
    JOIN user u ON n.diinput_oleh = u.id
    ORDER BY n.tanggal_input DESC
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

                <!-- Judul dan Tombol -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
                    <?php if (in_array($_SESSION['role'], ['admin', 'persidangan'])): ?>
                        <div id="tombolTambahNotulen">
                            <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                                <i class="fe fe-plus"></i> Tambah Notulen
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tabel Notulen -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0 table-striped" id="tabel-notulen">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Judul Rapat</th>
                                        <th>Ringkasan</th>
                                        <th>Diinput Oleh</th>
                                        <th style="width: 180px;">Tanggal Input</th>
                                        <th class="text-center" style="width: 130px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                    ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                                <td><?= nl2br(htmlspecialchars($row['ringkasan'])) ?></td>
                                                <td>
                                                    <?php if ($_SESSION['role'] === 'anggota'): ?>
                                                        <?= ucfirst(htmlspecialchars($row['role_penginput'])) ?>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($row['nama_penginput']) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?= date("d M Y, H:i", strtotime($row['tanggal_input'])) ?></td>
                                                <td class="text-center">
                                                    <div class="d-inline-flex gap-1 flex-wrap justify-content-center">
                                                        <?php if ($_SESSION['role'] === 'admin'): ?>
                                                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                                <i class="fe fe-edit"></i>
                                                            </a>
                                                            <a href="javascript:void(0)" onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')"
                                                                class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus">
                                                                <i class="fe fe-trash"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada notulen.</td>
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
    <script src="<?= BASE_URL ?>/assets/js/sweetalert2.all.min.js"></script>

    <script>
        // Inisialisasi DataTables
        $(document).ready(function() {
            $('#tabel-notulen').DataTable({
                language: {
                    lengthMenu: "Tampilkan _MENU_ notulen per halaman",
                    zeroRecords: "Tidak ada notulen ditemukan",
                    info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari _MAX_ total notulen)",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Sisipkan tombol tambah ke area filter
            $('#tombolTambahNotulen').appendTo('#tabel-notulen_wrapper .dataTables_filter').addClass('ms-3');
            $('#tabel-notulen_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');

            const headerCount = $('#tabel-notulen thead th').length;
            const colspanCount = $('#tabel-notulen tbody td[colspan]').attr('colspan');
            if (parseInt(headerCount) !== parseInt(colspanCount)) {
                console.warn("⚠️ Jumlah kolom dan colspan tidak cocok!");
            }
        });
    </script>
</body>

</html>