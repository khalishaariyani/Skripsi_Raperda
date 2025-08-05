<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Jadwal Rapat';
$role = $_SESSION['role'] ?? 'guest';
$nama = $_SESSION['nama'] ?? '-';

// ✅ Proses ACC/TOLAK hanya oleh Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'admin') {
    $id = intval($_POST['id'] ?? 0);
    $aksi = $_POST['aksi'] ?? '';

    if ($id > 0 && in_array($aksi, ['setujui', 'tolak'])) {
        $statusBaru = $aksi === 'setujui' ? 'disetujui' : 'dibatalkan';
        $stmt = $conn->prepare("UPDATE jadwalrapat SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $statusBaru, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// ✅ Ambil data sesuai role
if ($role === 'anggota') {
    $query = "SELECT * FROM jadwalrapat WHERE status = 'disetujui' ORDER BY tanggal DESC";
    $result = $conn->query($query);
} elseif ($role === 'persidangan') {
    $query = "SELECT * FROM jadwalrapat WHERE dibuat_oleh = ? ORDER BY tanggal DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT * FROM jadwalrapat ORDER BY tanggal DESC";
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content">
            <div class="container-fluid py-4">

                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0"><?= $pageTitle ?></h4>
                    <?php if (in_array($role, ['admin', 'persidangan'])): ?>
                        <div id="tombolTambahJadwal">
                            <a href="add.php" class="btn btn-primary rounded-pill d-flex align-items-center gap-2 shadow-sm">
                                <i class="fe fe-plus"></i> Tambah Jadwal
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0 table-striped" id="tabel-jadwal">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Judul</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Tempat</th>
                                        <th>Agenda Paripurna</th>
                                        <th>Peserta</th>
                                        <th>Pengusul</th>
                                        <th>Status</th>
                                        <th>Dibuat Oleh</th>
                                        <?php if ($role === 'admin'): ?>
                                            <th class="text-center">Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    while ($row = $result->fetch_assoc()): ?>
                                        <?php
                                        $status = strtolower($row['status'] ?? 'usulan');
                                        $badge = match ($status) {
                                            'usulan' => 'bg-warning text-dark',
                                            'disetujui' => 'bg-success',
                                            'dibatalkan' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($row['judul_rapat']) ?></td>
                                            <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                            <td><?= htmlspecialchars($row['waktu']) ?></td>
                                            <td><?= htmlspecialchars($row['tempat']) ?></td>
                                            <td><?= htmlspecialchars($row['agenda_rapat']) ?></td>
                                            <td><?= htmlspecialchars($row['peserta']) ?></td>
                                            <td><?= htmlspecialchars($row['pengusul']) ?></td>
                                            <td><span class="badge <?= $badge ?>"><?= ucfirst($status) ?></span></td>
                                            <td><?= htmlspecialchars($row['dibuat_oleh']) ?></td>
                                            <?php if ($role === 'admin'): ?>
                                                <td class="text-center">
                                                    <div class="d-inline-flex flex-wrap gap-1 justify-content-center">
                                                        <?php if ($status === 'usulan'): ?>
                                                            <div class="btn-group">
                                                                <button class="btn btn-sm btn-outline-success dropdown-toggle rounded-pill"
                                                                    type="button" data-bs-toggle="dropdown">
                                                                    Verifikasi
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <form method="POST">
                                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                                            <input type="hidden" name="aksi" value="setujui">
                                                                            <button type="submit" class="dropdown-item text-success">
                                                                                <i class="fe fe-check"></i> Setujui
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                    <li>
                                                                        <form method="POST">
                                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                                            <input type="hidden" name="aksi" value="tolak">
                                                                            <button type="submit" class="dropdown-item text-danger">
                                                                                <i class="fe fe-x"></i> Tolak
                                                                            </button>
                                                                        </form>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        <?php endif; ?>
                                                        <a href="edit.php?id=<?= $row['id'] ?>"
                                                            class="btn btn-sm btn-outline-warning rounded-circle shadow-sm" title="Edit">
                                                            <i class="fe fe-edit"></i>
                                                        </a>
                                                        <a href="delete.php?id=<?= $row['id'] ?>"
                                                            class="btn btn-sm btn-outline-danger rounded-circle shadow-sm"
                                                            onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
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

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
        <?php require_once LAYOUT_PATH . '/scripts.php'; ?>

        <!-- DataTables JS -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#tabel-jadwal').DataTable({
                    language: {
                        lengthMenu: "Tampilkan _MENU_ data per halaman",
                        zeroRecords: "Data tidak ditemukan",
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

                // Sisipkan tombol tambah ke samping kolom search bawaan
                $('#tombolTambahJadwal').appendTo('#tabel-jadwal_wrapper .dataTables_filter').addClass('ms-3');
                $('#tabel-jadwal_wrapper .dataTables_filter').addClass('d-flex align-items-center justify-content-between mb-3');
            });
        </script>
    </div>
</body>

</html>
