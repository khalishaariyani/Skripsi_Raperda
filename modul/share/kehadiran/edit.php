<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

$pageTitle = 'Edit Kehadiran Rapat';

// ✅ Proteksi Role
if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: kehadiran.php?msg=unauthorized&obj=kehadiran");
    exit;
}

// ✅ Ambil ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: kehadiran.php?msg=invalid&obj=kehadiran");
    exit;
}

// ✅ Ambil data lama
$stmt = $conn->prepare("SELECT * FROM kehadiranrapat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    header("Location: kehadiran.php?msg=invalid&obj=kehadiran");
    exit;
}

// ✅ Ambil user dan rapat aktif
$users = $conn->query("SELECT id, nama FROM user WHERE role = 'anggota' ORDER BY nama ASC");
$rapats = $conn->query("SELECT id, judul_rapat FROM jadwalrapat WHERE status = 'disetujui' ORDER BY tanggal DESC");

// ✅ Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = intval($_POST['id_user']);
    $id_rapat = intval($_POST['id_rapat']);
    $status = $_POST['status'];
    $waktu_hadir = $_POST['waktu_hadir'];

    // Validasi input kosong
    if (!$id_user || !$id_rapat || !$status || !$waktu_hadir) {
        header("Location: edit.php?id=$id&msg=kosong&obj=kehadiran");
        exit;
    }

    $stmt = $conn->prepare("UPDATE kehadiranrapat SET id_user = ?, id_rapat = ?, status = ?, waktu_hadir = ? WHERE id = ?");
    $stmt->bind_param("iissi", $id_user, $id_rapat, $status, $waktu_hadir, $id);

    if ($stmt->execute()) {
        header("Location: kehadiran.php?msg=updated&obj=kehadiran");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=kehadiran");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= $pageTitle ?></h5>
                    </div>
                    <form method="POST" class="card-body">
                        <div class="mb-3">
                            <label for="id_user" class="form-label">Nama Anggota</label>
                            <select name="id_user" class="form-control" required>
                                <option value="">-- Pilih Anggota --</option>
                                <?php while ($u = $users->fetch_assoc()): ?>
                                    <option value="<?= $u['id'] ?>" <?= $u['id'] == $data['id_user'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="id_rapat" class="form-label">Judul Rapat</label>
                            <select name="id_rapat" class="form-control" required>
                                <option value="">-- Pilih Rapat --</option>
                                <?php while ($r = $rapats->fetch_assoc()): ?>
                                    <option value="<?= $r['id'] ?>" <?= $r['id'] == $data['id_rapat'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['judul_rapat']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Kehadiran</label>
                            <select name="status" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="hadir" <?= $data['status'] === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                <option value="izin" <?= $data['status'] === 'izin' ? 'selected' : '' ?>>Izin</option>
                                <option value="sakit" <?= $data['status'] === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="waktu_hadir" class="form-label">Waktu Kehadiran</label>
                            <input type="datetime-local" name="waktu_hadir" class="form-control"
                                value="<?= date('Y-m-d\TH:i', strtotime($data['waktu_hadir'])) ?>" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="kehadiran.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>