<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Hanya admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=info");
    exit;
}

$pageTitle = 'Edit Informasi';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: info.php?msg=invalid&obj=info");
    exit;
}

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM informasi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    header("Location: info.php?msg=invalid&obj=info");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $isi = trim($_POST['isi'] ?? '');

    if (empty($judul) || empty($isi)) {
        header("Location: edit.php?id=$id&msg=kosong&obj=info");
        exit;
    }

    // Jika tidak ada perubahan konten dan gambar
    if ($judul === $data['judul'] && $isi === $data['isi'] && empty($_FILES['gambar']['name'][0])) {
        header("Location: edit.php?id=$id&msg=nochange&obj=info");
        exit;
    }

    // Gambar default (pakai yang lama)
    $gambarJson = $data['gambar'];

    // Proses upload gambar jika ada
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
                    header("Location: edit.php?id=$id&msg=uploaderror&obj=info");
                    exit;
                }
            } else {
                header("Location: edit.php?id=$id&msg=uploaderror&obj=info");
                exit;
            }
        }

        if (!empty($gambarNames)) {
            $gambarJson = json_encode($gambarNames);
        }
    }

    // Simpan perubahan
    $stmt = $conn->prepare("UPDATE informasi SET judul = ?, isi = ?, gambar = ? WHERE id = ?");
    $stmt->bind_param("sssi", $judul, $isi, $gambarJson, $id);

    if ($stmt->execute()) {
        header("Location: info.php?msg=updated&obj=info");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=info");
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
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h5>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Judul Informasi</label>
                            <input type="text" name="judul" class="form-control"
                                value="<?= htmlspecialchars($_POST['judul'] ?? $data['judul']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Isi Informasi</label>
                            <textarea name="isi" rows="5" class="form-control"
                                required><?= htmlspecialchars($_POST['isi'] ?? $data['isi']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Gambar Baru (Optional)</label>
                            <input type="file" name="gambar[]" class="form-control" multiple accept="image/*">
                            <small class="text-muted">Abaikan jika tidak ingin mengganti gambar.</small>
                        </div>

                        <?php if (!empty($data['gambar'])): ?>
                            <div class="mb-3">
                                <label class="form-label d-block">Gambar Lama:</label>
                                <?php
                                $gambarLama = json_decode($data['gambar'], true);
                                if (is_array($gambarLama)):
                                    foreach ($gambarLama as $g): ?>
                                        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($g) ?>"
                                            alt="Foto"
                                            class="img-thumbnail me-2 mb-2" style="max-height: 100px;">
                                <?php endforeach;
                                endif;
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between">
                            <a href="info.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>