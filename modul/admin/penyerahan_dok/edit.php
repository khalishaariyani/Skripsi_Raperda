<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Proteksi role
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=penyerahan");
    exit;
}

$pageTitle = 'Edit Penyerahan Dokumen';

// Ambil ID & Validasi
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: penyerahan.php?msg=invalid&obj=penyerahan");
    exit;
}

// Ambil data penyerahan JOIN judul arsip
$stmt = $conn->prepare("
    SELECT p.*, j.judul_rapat 
    FROM penyerahan_dokumen p
    JOIN arsiprapat a ON p.id_arsip = a.id
    JOIN jadwalrapat j ON a.id_rapat = j.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: penyerahan.php?msg=invalid&obj=penyerahan");
    exit;
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_penerima = trim($_POST['nama_penerima'] ?? '');
    $tanggal_penyerahan = $_POST['tanggal_penyerahan'] ?? date('Y-m-d');

    // Validasi input kosong
    if (empty($nama_penerima) || empty($tanggal_penyerahan)) {
        header("Location: edit.php?id=$id&msg=kosong&obj=penyerahan");
        exit;
    }

    // Handle file upload
    $file_baru = $data['file_dokumen'] ?? '';
    if (!empty($_FILES['file_dokumen']['name'])) {
        $ext = strtolower(pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION));
        $file_baru = uniqid('dokumen_') . '.' . $ext;

        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/penyerahan/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $upload_dir . $file_baru)) {
            // Hapus file lama
            $oldFile = $upload_dir . $data['file_dokumen'];
            if (is_file($oldFile) && !empty($data['file_dokumen'])) {
                unlink($oldFile);
            }
        } else {
            header("Location: edit.php?id=$id&msg=uploaderror&obj=penyerahan");
            exit;
        }
    }

    // Update ke database
    $stmt = $conn->prepare("UPDATE penyerahan_dokumen SET nama_penerima=?, file_dokumen=?, tanggal_penyerahan=? WHERE id=?");
    $stmt->bind_param("sssi", $nama_penerima, $file_baru, $tanggal_penyerahan, $id);

    if ($stmt->execute()) {
        header("Location: penyerahan.php?msg=updated&obj=penyerahan");
        exit;
    } else {
        header("Location: penyerahan.php?msg=error&obj=penyerahan");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content">
            <div class="container-fluid py-4">

                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= $pageTitle ?></h5>

                    </div>

                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <!-- Judul Arsip Tidak Bisa Diubah -->
                            <div class="mb-3">
                                <label class="form-label">Judul Arsip</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($data['judul_rapat']) ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" name="nama_penerima" class="form-control" value="<?= htmlspecialchars($data['nama_penerima']) ?>" required>
                            </div>

                            <!-- Dokumen -->
                            <div class="mb-3">
                                <label class="form-label">File Dokumen Saat Ini</label><br>
                                <?php if (!empty($data['file_dokumen'])): ?>
                                    <a href="<?= BASE_URL ?>/uploads/penyerahan/<?= htmlspecialchars($data['file_dokumen']) ?>" target="_blank"
                                        class="badge bg-info text-white text-decoration-none">
                                        <i class="fe fe-file-text"></i> Lihat File
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Tidak ada file</span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ganti File Dokumen (Opsional)</label>
                                <input type="file" name="file_dokumen" class="form-control" accept=".pdf,.doc,.docx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Penyerahan</label>
                                <input type="date" name="tanggal_penyerahan" class="form-control"
                                    value="<?= htmlspecialchars($data['tanggal_penyerahan']) ?>" required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="penyerahan.php" class="btn btn-secondary">Kembali</a>
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