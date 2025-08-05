<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Cek role
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=penyerahan");
    exit;
}

$pageTitle = 'Tambah Penyerahan Dokumen';

// Ambil ID arsip yang sudah pernah diserahkan
$usedIds = [];
$getUsed = $conn->query("SELECT DISTINCT id_arsip FROM penyerahan_dokumen");
while ($row = $getUsed->fetch_assoc()) {
    $usedIds[] = $row['id_arsip'];
}

// Ambil daftar arsip rapat + judul rapat
$arsipList = $conn->query("
    SELECT a.id, j.judul_rapat 
    FROM arsiprapat a 
    JOIN jadwalrapat j ON a.id_rapat = j.id 
    ORDER BY j.tanggal DESC
");

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_arsip = intval($_POST['id_arsip'] ?? 0);
    $nama_penerima = trim($_POST['nama_penerima'] ?? '');
    $tanggal_penyerahan = $_POST['tanggal_penyerahan'] ?? date('Y-m-d');

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/penyerahan/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $nama_file = '';

    // Validasi file upload
    if (!empty($_FILES['file']['name'])) {
        $tmp = $_FILES['file']['tmp_name'];
        $original = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $nama_file = time() . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $original);
        $targetPath = $uploadDir . $nama_file;

        if (!move_uploaded_file($tmp, $targetPath)) {
            header("Location: penyerahan.php?msg=uploaderror&obj=penyerahan");
            exit;
        }
    }

    // Validasi field wajib
    if ($id_arsip && $nama_penerima && $nama_file) {
        $stmt = $conn->prepare("
            INSERT INTO penyerahan_dokumen (id_arsip, nama_penerima, file_dokumen, tanggal_penyerahan)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $id_arsip, $nama_penerima, $nama_file, $tanggal_penyerahan);

        if ($stmt->execute()) {
            header("Location: penyerahan.php?msg=added&obj=penyerahan");
            exit;
        } else {
            header("Location: penyerahan.php?msg=error&obj=penyerahan");
            exit;
        }
    } else {
        header("Location: penyerahan.php?msg=kosong&obj=penyerahan");
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
                    <div class="px-4 pt-4 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                        <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                            Tambah Penyerahan Dokumen Rapat
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Judul Arsip</label>
                                <select name="id_arsip" class="form-select" required>
                                    <option value="">-- Pilih Judul Arsip --</option>
                                    <?php
                                    $hasAvailable = false;
                                    while ($row = $arsipList->fetch_assoc()):
                                        if (in_array($row['id'], $usedIds)) continue;
                                        $hasAvailable = true;
                                    ?>
                                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['judul_rapat']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <?php if (!$hasAvailable): ?>
                                    <small class="text-danger">Semua arsip sudah diserahkan.</small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Penerima</label>
                                <input type="text" name="nama_penerima" class="form-control"
                                    placeholder="Contoh: Biro Hukum Kota Banjarmasin" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload Dokumen Penyerahan</label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Penyerahan</label>
                                <input type="date" name="tanggal_penyerahan" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="penyerahan.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
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