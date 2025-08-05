<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

$pageTitle = 'Tambah Dokumen Rapat';

// Ambil daftar rapat yang disetujui
$jadwalRapat = $conn->query("SELECT id, judul_rapat FROM jadwalrapat WHERE status = 'disetujui' ORDER BY tanggal ASC");

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rapat = intval($_POST['id_rapat'] ?? 0);
    $nama_dokumen = trim($_POST['nama_dokumen'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $id_user = intval($_SESSION['id'] ?? 0);
    $queryUser = $conn->query("SELECT nama FROM user WHERE id = $id_user LIMIT 1");
    $diunggah_oleh = $queryUser->fetch_assoc()['nama'] ?? 'system';

    // Validasi kosong
    if (!$id_rapat || !$nama_dokumen || empty($_FILES['file_dok']['name'][0])) {
        header("Location: add.php?msg=kosong&obj=dok_rapat");
        exit;
    }

    // Cek ID rapat valid
    $cek = $conn->prepare("SELECT id FROM jadwalrapat WHERE id = ? AND status = 'disetujui'");
    $cek->bind_param("i", $id_rapat);
    $cek->execute();
    $cek->store_result();
    if ($cek->num_rows === 0) {
        header("Location: add.php?msg=invalid&obj=dok_rapat");
        exit;
    }

    // Siapkan direktori upload
    $upload_dir = ROOT_PATH . '/uploads/dok_rapat/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $uploaded_files = [];

    foreach ($_FILES['file_dok']['name'] as $key => $filename) {
        $tmp_name = $_FILES['file_dok']['tmp_name'][$key];
        $error = $_FILES['file_dok']['error'][$key];
        $size = $_FILES['file_dok']['size'][$key];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($error !== 0 || !in_array($ext, $allowed_ext) || $size > $max_size) {
            header("Location: add.php?msg=uploaderror&obj=dok_rapat");
            exit;
        }

        $unique_name = 'dok_' . uniqid() . '.' . $ext;
        $target_file = $upload_dir . $unique_name;

        if (move_uploaded_file($tmp_name, $target_file)) {
            $uploaded_files[] = $unique_name;
        } else {
            header("Location: add.php?msg=uploaderror&obj=dok_rapat");
            exit;
        }
    }

    if (count($uploaded_files) > 0) {
        $file_dok = implode('|', $uploaded_files);
        $tanggal_upload = date('Y-m-d');

        $stmt = $conn->prepare("
            INSERT INTO dok_rapat 
                (id_rapat, nama_dokumen, deskripsi, file_dok, tanggal_upload, diunggah_oleh) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssss", $id_rapat, $nama_dokumen, $deskripsi, $file_dok, $tanggal_upload, $diunggah_oleh);

        if ($stmt->execute()) {
            header("Location: dok_rapat.php?msg=added&obj=dok_rapat");
            exit;
        } else {
            header("Location: add.php?msg=error&obj=dok_rapat");
            exit;
        }
    } else {
        header("Location: add.php?msg=uploaderror&obj=dok_rapat");
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
                <h4 class="fw-bold mb-3"><?= $pageTitle ?></h4>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Pilih Judul Rapat -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Judul Rapat</label>
                                <select name="id_rapat" class="form-control" required>
                                    <option value="">-- Pilih Judul Rapat --</option>
                                    <?php if ($jadwalRapat->num_rows > 0): ?>
                                        <?php while ($row = $jadwalRapat->fetch_assoc()): ?>
                                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['judul_rapat']) ?></option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada rapat yang tersedia</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Nama Dokumen -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Dokumen</label>
                                <input type="text" name="nama_dokumen" class="form-control" required>
                            </div>

                            <!-- Deskripsi -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                            </div>

                            <!-- Upload File -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Upload File</label>
                                <input type="file" name="file_dok[]" class="form-control" multiple required>
                                <div class="form-text">Boleh upload lebih dari 1 file. Format: PDF, JPG, PNG.</div>
                            </div>

                            <!-- Tombol -->
                            <div class="d-flex justify-content-end">
                                <a href="dok_rapat.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary rounded-pill">
                                    <i class="fe fe-save me-2"></i> Simpan Dokumen
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