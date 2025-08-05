<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/index.php?msg=unauthorized");
    exit;
}

$sessionId = $_SESSION['id'] ?? 0;
$pageTitle = 'Manajemen User';
$query = "SELECT * FROM User ORDER BY id DESC";
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

                <!-- Judul -->
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-1">Manajemen User</h4>
                    <div id="tombolTambahUser">
                                <button class="btn btn-primary rounded-pill shadow-sm d-flex align-items-center gap-2"
                                    data-bs-toggle="modal" data-bs-target="#modalTambah">
                                    <i class="fe fe-user-plus"></i> Tambah User
                                </button>
                            </div>
                </div>

                <!-- Card Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <!-- Tabel -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0 table-striped" id="tabel-user">

                                <thead class="table-primary">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th class="text-center" style="width: 180px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td>
                                                <?php
                                                $roleClass = match ($row['role']) {
                                                    'admin' => 'badge bg-primary',
                                                    'persidangan' => 'badge bg-warning text-dark',
                                                    'anggota' => 'badge bg-success',
                                                    default => 'badge bg-secondary'
                                                };
                                                ?>
                                                <span class="<?= $roleClass ?>"><?= strtoupper($row['role']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex flex-wrap gap-1 justify-content-center">
                                                    <a href="<?= BASE_URL ?>/modul/admin/user/edit.php?id=<?= $row['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary rounded-circle shadow-sm" title="Edit">
                                                        <i class="fe fe-edit"></i>
                                                    </a>
                                                    <?php if ($sessionId != $row['id']): ?>
                                                        <a href="javascript:void(0)" onclick="confirmDelete('delete.php?id=<?= $row['id'] ?>')"
                                                        class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus">
                                                            <i class="fe fe-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-success small">Aktif</span>
                                                    <?php endif; ?>
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

        <!-- Modal Tambah -->
        <div class="modal fade" id="modalTambah" tabindex="-1">
            <div class="modal-dialog">
                <form action="add.php" method="POST" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fe fe-user-plus me-1"></i> Tambah Pengguna Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama..." required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Email..." required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Password..." required>
                                <span class="input-group-text" onclick="togglePassword()" style="cursor:pointer;">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </span>
                            </div>
                        </div>
                        <script>
                            function togglePassword() {
                                const input = document.getElementById('passwordInput');
                                const icon = document.getElementById('toggleIcon');
                                if (input.type === 'password') {
                                    input.type = 'text';
                                    icon.classList.remove('bi-eye');
                                    icon.classList.add('bi-eye-slash');
                                } else {
                                    input.type = 'password';
                                    icon.classList.remove('bi-eye-slash');
                                    icon.classList.add('bi-eye');
                                }
                            }
                        </script>
                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="persidangan">Persidangan</option>
                                <option value="anggota">Anggota Rapat</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-gradient-primary">
                            <i class="fe fe-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
        <?php require_once LAYOUT_PATH . '/scripts.php'; ?>

        <!-- Tambahkan Script DataTables -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function() {
                const table = $('#tabel-user').DataTable({
                    language: {
                        lengthMenu: "Tampilkan _MENU_ user per halaman",
                        zeroRecords: "Data tidak ditemukan",
                        info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                        infoEmpty: "Tidak ada data user tersedia",
                        infoFiltered: "(difilter dari _MAX_ total user)",
                        search: "Cari:",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Berikutnya",
                            previous: "Sebelumnya"
                        }
                    }
                });

                // Pindahkan tombol ke baris filter DataTables
                $('#tombolTambahUser').appendTo('#tabel-user_wrapper .dataTables_filter').addClass('ms-3');

                // Tambahkan style flex agar search dan tombol sejajar
                $('#tabel-user_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
            });
        </script>   
    </div>
</body>

</html>