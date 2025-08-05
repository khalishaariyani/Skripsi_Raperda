<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

$pageTitle = 'Tambah Kehadiran Rapat';
$role = $_SESSION['role'];
$id_user = $_SESSION['id'];
$nama_user = $_SESSION['nama'];

if (!in_array($role, ['admin', 'persidangan', 'anggota'])) {
    header("Location: kehadiran.php?msg=unauthorized&obj=kehadiran");
    exit;
}

// Ambil daftar rapat sesuai role
if ($role === 'anggota') {
    $rapat = $conn->query("
        SELECT DISTINCT j.id, j.judul_rapat
        FROM undanganrapat u
        JOIN jadwalrapat j ON j.id = u.idRapat
        WHERE u.penerima = '" . $conn->real_escape_string($nama_user) . "'
        ORDER BY j.tanggal DESC
    ");
} else {
    $rapat = $conn->query("
        SELECT id, judul_rapat FROM jadwalrapat
        WHERE status = 'disetujui'
        ORDER BY tanggal DESC
    ");
}

// Ambil user (hanya untuk admin & persidangan)
$users = [];
if (in_array($role, ['admin', 'persidangan'])) {
    $users = $conn->query("SELECT id, nama FROM user WHERE role = 'anggota' ORDER BY nama ASC");
}

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rapat = intval($_POST['id_rapat'] ?? 0);
    $status = $_POST['status'] ?? '';
    $waktu_hadir = date('Y-m-d H:i:s');
    $id_pengisi = ($role === 'anggota') ? $id_user : intval($_POST['id_user'] ?? 0);

    if (!$id_rapat || !$status || !$id_pengisi) {
        header("Location: add.php?msg=kosong&obj=kehadiran");
        exit;
    }

    // Validasi undangan (untuk anggota)
    if ($role === 'anggota') {
        $cekUndangan = $conn->prepare("SELECT id FROM undanganrapat WHERE idRapat = ? AND penerima = ?");
        $cekUndangan->bind_param("is", $id_rapat, $nama_user);
        $cekUndangan->execute();
        $cekUndangan->store_result();

        if ($cekUndangan->num_rows === 0) {
            header("Location: add.php?msg=unauthorized&obj=kehadiran");
            exit;
        }
    }

    // Cek duplikat (kecuali admin)
    if ($role !== 'admin') {
        $cek = $conn->prepare("SELECT id FROM kehadiranrapat WHERE id_user = ? AND id_rapat = ?");
        $cek->bind_param("ii", $id_pengisi, $id_rapat);
        $cek->execute();
        $cek->store_result();
        if ($cek->num_rows > 0) {
            header("Location: kehadiran.php?msg=duplicate&obj=kehadiran");
            exit;
        }
    }

    // Simpan data
    $stmt = $conn->prepare("INSERT INTO kehadiranrapat (id_user, id_rapat, status, waktu_hadir) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $id_pengisi, $id_rapat, $status, $waktu_hadir);

    if ($stmt->execute()) {
        header("Location: kehadiran.php?msg=added&obj=kehadiran");
        exit;
    } else {
        header("Location: add.php?msg=error&obj=kehadiran");
        exit;
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <div class="main-content app-content mt-6" style="background: #edf0f8;">
            <div class="container-fluid">
                <div class="card custom-card" style="margin-top: 1rem;">
                    <div class="" style="background: #e9ecf6; border-radius: 0.5rem 0.5rem 0 0;">
                        <h2 class="mb-3" style="font-size:1.25rem; font-weight:700; color:#222;">
                            Tambah Kehadiran Rapat
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'unauthorized'): ?>
                            <div class="alert alert-danger">
                                Anda tidak memiliki undangan untuk rapat tersebut. Kehadiran tidak dapat disimpan.
                            </div>
                        <?php endif; ?>


                        <form method="POST">
                            <?php if (in_array($role, ['admin', 'persidangan'])): ?>
                                <div class="form-group">
                                    <label>Nama Anggota</label>
                                    <select name="id_user" id="selectAnggota" class="form-control" required>
                                        <option value="">-- Pilih Anggota --</option>
                                        <?php while ($u = $users->fetch_assoc()): ?>
                                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="form-group mt-3">
                                <label>Judul Rapat</label>
                                <select name="id_rapat" class="form-control" required>
                                    <option value="">-- Pilih Rapat --</option>
                                    <?php while ($r = $rapat->fetch_assoc()): ?>
                                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['judul_rapat']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group mt-3">
                                <label>Status Kehadiran</label>
                                <select name="status" class="form-control" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="hadir">Hadir</option>
                                    <option value="izin">Izin</option>
                                    <option value="sakit">Sakit</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="kehadiran.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
    </div>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#selectAnggota').select2({
                placeholder: "-- Pilih Anggota --",
                allowClear: true
            });
        });
    </script>
</body>

</html>