<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

// Validasi akses
if (!in_array($_SESSION['role'], ['admin', 'anggota', 'persidangan'])) {
    header("Location: index.php?msg=unauthorized");
    exit;
}

// Ambil ID dari URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: usulan.php?msg=invalid");
    exit;
}

// Ambil data berdasarkan id_usulan
$stmt = $conn->prepare("SELECT du.*, jr.judul_rapat FROM dokumen_usulan du 
    LEFT JOIN jadwalrapat jr ON du.judul_rapat = jr.id WHERE du.id_usulan = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usulan = $result->fetch_assoc();
$stmt->close();

if (!$usulan) {
    header("Location: usulan.php?msg=notfound");
    exit;
}


// Cek akses pemilik data
if ($_SESSION['role'] === 'anggota' && $usulan['id_user'] != $_SESSION['id']) {
    header("Location: usulan.php?msg=forbidden");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $nama_file = $usulan['nama_file']; // default pakai file lama

    // Validasi deskripsi saja, tidak perlu validasi judul_rapat karena tidak diedit
    if (empty($deskripsi)) {
        header("Location: edit.php?id=$id&msg=kosong");
        exit;
    }

    // Ganti file jika upload baru
    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $newName = uniqid('usulan_') . '.' . $ext;
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/usulan/' . $newName;

        // Hapus file lama
        $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/usulan/' . $nama_file;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
            $nama_file = $newName;
        } else {
            header("Location: edit.php?id=$id&msg=uploaderror&obj=usulan");
            exit;
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE dokumen_usulan SET deskripsi = ?, nama_file = ?, tanggal_upload = NOW() WHERE id_usulan = ?");
    $stmt->bind_param("ssi", $deskripsi, $nama_file, $id);

    if ($stmt->execute()) {
        header("Location: usulan.php?msg=updated&obj=usulan");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=usulan");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<?php $pageTitle = 'Edit Dokumen Usulan';
require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content">
            <div class="container py-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h5>Edit Dokumen Usulan</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Judul Rapat</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($usulan['judul_rapat'] ?? '-') ?>" readonly disabled>
                            </div>
                            <div class="form-group mt-3">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3" required><?= htmlspecialchars($usulan['deskripsi']) ?></textarea>
                            </div>
                            <div class="form-group mt-3">
                                <label>Ganti File (Opsional)</label>
                                <input type="file" name="file" class="form-control">
                                <small>File saat ini:
                                    <a href="<?= BASE_URL ?>/uploads/usulan/<?= urlencode($usulan['nama_file']) ?>" target="_blank">
                                        <?= htmlspecialchars($usulan['nama_file']) ?>
                                    </a>
                                </small>
                            </div>
                            <div class="mt-4 d-flex justify-content-between">
                                <a href="usulan.php" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan Perubahan</button>
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