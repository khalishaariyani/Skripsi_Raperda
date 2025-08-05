<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Tambah Arsip Rapat';

// Ambil rapat yang status disetujui dan BELUM punya arsip
$rapat = $conn->query("
    SELECT j.id, j.judul_rapat
    FROM jadwalrapat j
    WHERE j.status = 'disetujui'
      AND j.id NOT IN (SELECT id_rapat FROM arsiprapat)
    ORDER BY j.tanggal DESC
");

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rapat = intval($_POST['id_rapat']);
    $diunggah_oleh = $_SESSION['id'];
    $folder_upload = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/arsip/';

    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0775, true);
    }

    if (!empty($_FILES['file']['name'])) {
        $tmp = $_FILES['file']['tmp_name'];
        $nama_asli = basename($_FILES['file']['name']);
        $ext = pathinfo($nama_asli, PATHINFO_EXTENSION);
        $nama_bersih = preg_replace('/[^a-zA-Z0-9_.]/', '_', $nama_asli);
        $nama_file = time() . '_' . $nama_bersih;
        $file_path = $folder_upload . $nama_file;

        if (in_array(strtolower($ext), ['pdf', 'doc', 'docx'])) {
            if (move_uploaded_file($tmp, $file_path)) {
                $stmt = $conn->prepare("INSERT INTO arsiprapat (id_rapat, nama_file, file_path, diunggah_oleh) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $id_rapat, $nama_asli, $nama_file, $diunggah_oleh);

                if ($stmt->execute()) {
                    header("Location: arsip.php?msg=added&obj=arsip");
                    exit;
                } else {
                    header("Location: arsip.php?msg=failed&obj=arsip");
                }
            } else {
               header("Location: arsip.php?msg=failed&obj=arsip");
            }
        } else {
           header("Location: arsip.php?msg=invalid&obj=arsip");
        }
    } else {
        $error = "Pilih file untuk diunggah.";
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
                    <div class="px-4 pt-4 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                        <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                            Tambah Arsip Rapat
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Judul Rapat</label>
                                <select name="id_rapat" class="form-select" required>
                                    <option value="">-- Pilih Rapat --</option>
                                    <?php while ($row = $rapat->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['judul_rapat']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="text-muted">Hanya rapat yang belum pernah diarsipkan akan muncul.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">File Arsip</label>
                                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="arsip.php" class="btn btn-secondary me-2">Batal</a>
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