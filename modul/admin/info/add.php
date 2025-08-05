<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Hanya Admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=info");
    exit;
}

$pageTitle = 'Tambah Informasi';

// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');
    $author = $_SESSION['nama'] ?? 'Admin';

    // Validasi input kosong
    if (empty($judul) || empty($isi)) {
        header("Location: info.php?msg=kosong&obj=info");
        exit;
    }

    // Siapkan variabel gambar
    $gambarJson = null;

    // Proses upload jika ada file
    if (isset($_FILES['gambar']) && !empty($_FILES['gambar']['name'][0])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/';
        $uploadedFiles = $_FILES['gambar'];
        $gambarNames = [];

        foreach ($uploadedFiles['tmp_name'] as $key => $tmpName) {
            if ($uploadedFiles['error'][$key] === UPLOAD_ERR_OK) {
                $originalName = basename($uploadedFiles['name'][$key]);
                $uniqueName = uniqid() . '_' . preg_replace('/\s+/', '_', $originalName);
                $targetPath = $uploadDir . $uniqueName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $gambarNames[] = $uniqueName;
                } else {
                    // Gagal upload
                    header("Location: info.php?msg=uploaderror&obj=info");
                    exit;
                }
            } else {
                // Error saat upload
                header("Location: info.php?msg=uploaderror&obj=info");
                exit;
            }
        }

        if (!empty($gambarNames)) {
            $gambarJson = json_encode($gambarNames);
        }
    }

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO informasi (judul, isi, tanggal, author, gambar) VALUES (?, ?, CURRENT_DATE, ?, ?)");
    $stmt->bind_param("ssss", $judul, $isi, $author, $gambarJson);

    if ($stmt->execute()) {
        header("Location: info.php?msg=added&obj=info");
        exit;
    } else {
        header("Location: info.php?msg=error&obj=info");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">
                <div class="card">
                    <div class="card shadow-sm">
                        <div class="px-4 pt-2 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                            <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                                Tambah Informasi Rapat
                            </h2>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="card-body">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Judul Informasi</label>
                                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Isi Informasi</label>
                                <textarea name="isi" rows="5" class="form-control" required><?= htmlspecialchars($_POST['isi'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload Gambar (Optional)</label>
                                <input type="file" name="gambar[]" class="form-control" multiple accept="image/*">
                                <small class="text-muted">Boleh pilih lebih dari satu file. File disimpan di folder /uploads/</small>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="info.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>