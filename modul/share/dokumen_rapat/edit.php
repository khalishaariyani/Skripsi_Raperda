<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// ✅ Validasi role
if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: dok_rapat.php?msg=unauthorized&obj=dok_rapat");
    exit;
}

// ✅ Ambil ID dokumen dari URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: dok_rapat.php?msg=invalid&obj=dok_rapat");
    exit;
}

// ✅ Ambil data dokumen
$stmt = $conn->prepare("SELECT d.*, j.judul_rapat FROM dok_rapat d 
    LEFT JOIN jadwalrapat j ON d.id_rapat = j.id WHERE d.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$dokumen = $result->fetch_assoc();
$stmt->close();

if (!$dokumen) {
    header("Location: dok_rapat.php?msg=invalid&obj=dok_rapat");
    exit;
}

// ✅ Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // Validasi input kosong
    if (empty($nama_dokumen) || empty($deskripsi)) {
        header("Location: edit.php?id=$id&msg=kosong&obj=dok_rapat");
        exit;
    }

    // Cek perubahan data
    if ($nama_dokumen === $dokumen['nama_dokumen'] && $deskripsi === $dokumen['deskripsi']) {
        header("Location: edit.php?id=$id&msg=nochange&obj=dok_rapat");
        exit;
    }

    $file_dok = $dokumen['file_dok']; // tetap pakai file lama
    $pengunggah = $dokumen['diunggah_oleh'];

    // Eksekusi update
    $stmt = $conn->prepare("UPDATE dok_rapat SET nama_dokumen = ?, deskripsi = ?, file_dok = ?, tanggal_upload = NOW(), diunggah_oleh = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nama_dokumen, $deskripsi, $file_dok, $pengunggah, $id);

    if ($stmt->execute()) {
        header("Location: dok_rapat.php?msg=updated&obj=dok_rapat");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=dok_rapat");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<?php $pageTitle = 'Edit Dokumen Rapat';
require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content">
            <div class="container-fluid py-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="main-content-label">Edit Dokumen Rapat</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Judul Rapat</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($dokumen['judul_rapat'] ?? '-') ?>" readonly disabled>
                            </div>

                            <div class="form-group mt-3">
                                <label>Nama Dokumen</label>
                                <input type="text" name="nama_dokumen" class="form-control" value="<?= htmlspecialchars($dokumen['nama_dokumen']) ?>" required>
                            </div>

                            <div class="form-group mt-3">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" rows="3" class="form-control" required><?= htmlspecialchars($dokumen['deskripsi']) ?></textarea>
                            </div>

                            <div class="form-group mt-3">
                                <label>File Saat Ini</label>
                                <div class="bg-light border p-2 rounded">
                                    <?php foreach (explode('|', $dokumen['file_dok']) as $file): ?>
                                        <a href="<?= BASE_URL ?>/uploads/dok_rapat/<?= urlencode($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a><br>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group mt-4 d-flex justify-content-between">
                                <a href="dok_rapat.php" class="btn btn-secondary">Batal</a>
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