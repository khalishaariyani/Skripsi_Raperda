<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

$role = $_SESSION['role'] ?? '';
$id_user = intval($_SESSION['id'] ?? 0);

if (!in_array($role, ['admin', 'anggota', 'persidangan'])) {
    header("Location: " . BASE_URL . "/index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Dokumen Usulan';
$where = ($role === 'anggota') ? "WHERE du.id_user = $id_user" : "";

$query = "
    SELECT du.*, u.nama AS nama_pengunggah, jr.judul_rapat
    FROM dokumen_usulan du
    LEFT JOIN user u ON du.id_user = u.id
    LEFT JOIN jadwalrapat jr ON du.judul_rapat = jr.id
    $where
    ORDER BY du.tanggal_upload DESC
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
                    <?php if ($role === 'anggota'): ?>
                        <div id="tombolUploadUsulan">
                            <a href="#formUpload" class="btn btn-primary rounded-pill shadow-sm d-flex align-items-center gap-2">
                                <i class="fe fe-plus"></i> Upload Dokumen
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0 table-striped" id="tabel-usulan">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Judul Rapat</th>
                                        <th>Nama File</th>
                                        <th>Deskripsi</th>
                                        <th>Diunggah Oleh</th>
                                        <th>Waktu Upload</th>
                                        <th>File</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($row['nama_file']) ?></td>
                                            <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_pengunggah'] ?? '-') ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal_upload'])) ?></td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>/uploads/usulan/<?= urlencode($row['nama_file']) ?>"
                                                    target="_blank" class="btn btn-sm btn-info mb-1">
                                                    <i class="fe fe-file"></i> Lihat
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($role === 'admin' || $role === 'persidangan' || ($role === 'anggota' && $row['id_user'] == $id_user)): ?>
                                                    <a href="edit.php?id=<?= htmlspecialchars($row['id_usulan']) ?>"
                                                        class="btn btn-sm btn-warning mb-1">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?= htmlspecialchars($row['id_usulan']) ?>"
                                                        class="btn btn-sm btn-danger mb-1"
                                                        onclick="return confirm('Yakin ingin menghapus dokumen ini?');">
                                                        <i class="fe fe-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($role === 'anggota'): ?>
                            <div class="card shadow-sm border-0 mt-4" id="formUpload">
                                <div class="card-header bg-primary text-white fw-bold">
                                    Upload Usulan Dokumen
                                </div>
                                <div class="card-body">
                                    <form action="upload_usulan.php" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="judul_rapat" class="form-label">Pilih Judul Rapat</label>
                                            <select name="judul_rapat" id="judul_rapat" class="form-control" required>
                                                <option value="" disabled selected>-- Pilih Judul Rapat --</option>
                                                <?php
                                                $namaUser = $conn->real_escape_string($_SESSION['nama']);
                                                $rapat = $conn->query("
                                                    SELECT j.id, j.judul_rapat 
                                                    FROM undanganrapat u
                                                    JOIN jadwalrapat j ON u.idRapat = j.id
                                                    WHERE u.penerima = '$namaUser'
                                                    ORDER BY j.tanggal ASC
                                                ");
                                                while ($r = $rapat->fetch_assoc()):
                                                    echo '<option value="' . $r['id'] . '">' . htmlspecialchars($r['judul_rapat']) . '</option>';
                                                endwhile;
                                                ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="file" class="form-label">Pilih File</label>
                                            <input type="file" name="file" id="file" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="deskripsi" class="form-label">Deskripsi</label>
                                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fe fe-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>
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
            $('#tabel-usulan').DataTable({
                language: {
                    lengthMenu: "Tampilkan _MENU_ dokumen per halaman",
                    zeroRecords: "Tidak ada dokumen ditemukan",
                    info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                    infoEmpty: "Tidak ada data tersedia",
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

            // Geser tombol ke sebelah search box
            $('#tombolUploadUsulan').appendTo('#tabel-usulan_wrapper .dataTables_filter').addClass('ms-3');
            $('#tabel-usulan_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
        });
    </script>
</body>
</html>
