<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'anggota'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Edit Komentar Diskusi';
$id = intval($_GET['id'] ?? 0);

// Ambil data lama
$data = $conn->query("SELECT * FROM diskusiperda WHERE id = $id")->fetch_assoc();
if (!$data) {
    header("Location: diskusi.php?msg=invalid&obj=diskusi");
    exit;
}

// Validasi hak akses: hanya admin atau pemilik komentar
if ($_SESSION['role'] !== 'admin' && $_SESSION['id'] != $data['idPengguna']) {
    header("Location: diskusi.php?msg=unauthorized");
    exit;
}

// Ambil daftar rapat untuk dropdown
$rapat = $conn->query("SELECT id, judul_rapat FROM jadwalrapat WHERE status = 'disetujui'");

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRapat = intval($_POST['idRapat']);
    $isiKomentar = $conn->real_escape_string($_POST['isiKomentar']);

    if (!empty($idRapat) && !empty($isiKomentar)) {
        $conn->query("UPDATE diskusiperda SET idRapat = '$idRapat', isiKomentar = '$isiKomentar' WHERE id = $id");
        header("Location: diskusi.php?msg=updated&obj=diskusi");
        exit;
    } else {
        header("Location: diskusi.php?msg=error&obj=diskusi");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= $pageTitle ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="idRapat" class="form-label">Pilih Rapat</label>
                                <select name="idRapat" class="form-select" required>
                                    <option value="">-- Pilih Rapat --</option>
                                    <?php while ($row = $rapat->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>" <?= $data['idRapat'] == $row['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['judul_rapat']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="isiKomentar" class="form-label">Komentar</label>
                                <textarea name="isiKomentar" class="form-control" rows="4" required><?= htmlspecialchars($data['isiKomentar']) ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="diskusi.php" class="btn btn-secondary">Kembali</a>
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