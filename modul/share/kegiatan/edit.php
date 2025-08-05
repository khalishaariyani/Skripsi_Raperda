<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: ../index.php?msg=unauthorized&obj=dokumentasi");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: dokumentasi.php?msg=invalid&obj=dokumentasi");
    exit;
}

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM dokumentasikegiatan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: dokumentasi.php?msg=invalid&obj=dokumentasi");
    exit;
}

// Ambil jadwal rapat
$jadwals = $conn->query("
    SELECT id, judul_rapat 
    FROM jadwalrapat 
    WHERE status = 'disetujui' 
    ORDER BY tanggal DESC
");

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRapat = intval($_POST['idRapat'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $file = $_FILES['file'] ?? null;

    if (!$idRapat || empty($keterangan)) {
        header("Location: edit.php?id=$id&msg=kosong&obj=dokumentasi");
        exit;
    }

    $namaFileBaru = $data['file']; // default: tetap pakai file lama

    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($ext, $allowed)) {
            header("Location: edit.php?id=$id&msg=uploaderror&obj=dokumentasi");
            exit;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            header("Location: edit.php?id=$id&msg=uploaderror&obj=dokumentasi");
            exit;
        }

        $namaFileBaru = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $file['name']);
        $target = ROOT_PATH . '/uploads/dokumentasi/' . $namaFileBaru;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            header("Location: edit.php?id=$id&msg=uploaderror&obj=dokumentasi");
            exit;
        }

        $lama = ROOT_PATH . '/uploads/dokumentasi/' . $data['file'];
        if (file_exists($lama)) unlink($lama);
    }

    $stmt = $conn->prepare("UPDATE dokumentasikegiatan SET idRapat = ?, file = ?, keterangan = ? WHERE id = ?");
    $stmt->bind_param("issi", $idRapat, $namaFileBaru, $keterangan, $id);

    if ($stmt->execute()) {
        header("Location: dokumentasi.php?msg=updated&obj=dokumentasi");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=dokumentasi");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid py-4 mt-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Edit Data Dokumentasi</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Judul Rapat</label>
                                <select name="idRapat" class="form-control" required>
                                    <option value="">-- Pilih Rapat --</option>
                                    <?php while ($j = $jadwals->fetch_assoc()): ?>
                                        <option value="<?= $j['id'] ?>" <?= $data['idRapat'] == $j['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($j['judul_rapat']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label>File (Kosongkan jika tidak diganti)</label>
                                <input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                <small>File saat ini: <strong><?= htmlspecialchars($data['file']) ?></strong></small>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="dokumentasi.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>