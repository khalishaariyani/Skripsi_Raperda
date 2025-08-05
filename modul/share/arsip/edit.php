<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$pageTitle = 'Edit Arsip Rapat';

// Ambil data arsip
$stmt = $conn->prepare("SELECT a.*, j.judul_rapat FROM arsiprapat a JOIN jadwalrapat j ON a.id_rapat = j.id WHERE a.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die("Data tidak ditemukan.");
}

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folder_upload = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/arsip/';
    $nama_lama = $data['file_path'];

    if (!empty($_FILES['file']['name'])) {
        $tmp = $_FILES['file']['tmp_name'];
        $nama_asli = basename($_FILES['file']['name']);
        $ext = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        $nama_bersih = preg_replace('/[^a-zA-Z0-9_.]/', '_', $nama_asli);
        $nama_baru = time() . '_' . $nama_bersih;
        $tujuan = $folder_upload . $nama_baru;

        if (in_array($ext, ['pdf', 'doc', 'docx'])) {
            if (move_uploaded_file($tmp, $tujuan)) {
                // Hapus file lama
                if (file_exists($folder_upload . $nama_lama)) unlink($folder_upload . $nama_lama);

                $stmt = $conn->prepare("UPDATE arsiprapat SET nama_file=?, file_path=? WHERE id=?");
                $stmt->bind_param("ssi", $nama_asli, $nama_baru, $id);
                $stmt->execute();
                $stmt->close();

                header("Location: arsip.php?msg=updated");
                exit;
            } else {
                $error = "Gagal upload file baru.";
            }
        } else {
            $error = "Format file tidak valid.";
        }
    } else {
        header("Location: arsip.php?msg=updated");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content">
            <div class="container-fluid py-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= $pageTitle ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Judul Rapat</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($data['judul_rapat']) ?>" readonly>
                                <small class="text-muted">Judul rapat tidak dapat diubah.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">File Sebelumnya</label>
                                <div class="border rounded bg-light p-2"><?= htmlspecialchars($data['nama_file']) ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ganti File (Opsional)</label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="arsip.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-save me-1"></i> Simpan Perubahan
                                </button>
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