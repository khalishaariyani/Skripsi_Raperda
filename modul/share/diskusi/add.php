<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Perbolehkan: admin, anggota, persidangan
if (!in_array($_SESSION['role'], ['admin', 'anggota', 'persidangan'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Tambah Komentar Diskusi';

// Ambil daftar rapat yang disetujui
$rapat = $conn->query("SELECT id, judul_rapat FROM jadwalrapat WHERE status = 'disetujui' ORDER BY tanggal DESC");

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRapat = intval($_POST['idRapat']);
    $idPengguna = $_SESSION['id'];
    $isiKomentar = trim($_POST['isiKomentar']);

    if (!empty($idRapat) && !empty($isiKomentar)) {
        $stmt = $conn->prepare("INSERT INTO diskusiperda (idRapat, idPengguna, isiKomentar) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $idRapat, $idPengguna, $isiKomentar);
        $stmt->execute();
        $stmt->close();

        header("Location: diskusi.php?msg=added&obj=diskusi");
        exit;
    } else {
        header("Location: diskusi.php?msg=error&obj=diskusi");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                        <h5 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="idRapat" class="form-label">Pilih Rapat</label>
                                <select name="idRapat" id="idRapat" class="form-select" required>
                                    <option value="">-- Pilih Rapat --</option>
                                    <?php while ($row = $rapat->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['judul_rapat']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="isiKomentar" class="form-label">Komentar</label>
                                <textarea name="isiKomentar" id="isiKomentar" rows="4" class="form-control" required></textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="diskusi.php" class="btn btn-secondary me-2">
                                    <i class="fe fe-x me-1"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-ungu">
                                    <i class="fe fe-save me-1"></i> Simpan
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </main>
    </div>

    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>

    <style>
        /* Tombol ungu seragam */
        .btn-ungu {
            background-color: #6c5ce7;
            color: #fff;
            border: none;
        }

        .btn-ungu:hover {
            background-color: #5a4bcf;
        }
    </style>
</body>

</html>