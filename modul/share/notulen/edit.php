<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=notulen");
    exit;
}

$pageTitle = 'Edit Notulen Rapat';
$id = intval($_GET['id'] ?? 0);

// Validasi ID
if ($id <= 0) {
    header("Location: notulen.php?msg=invalid&obj=notulen");
    exit;
}

// Ambil data notulen
$stmt = $conn->prepare("SELECT * FROM notulen WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: notulen.php?msg=invalid&obj=notulen");
    exit;
}

// Ambil judul rapat
$judulRapat = '';
$getRapat = $conn->prepare("SELECT judul_rapat FROM jadwalrapat WHERE id = ?");
$getRapat->bind_param("i", $data['id_rapat']);
$getRapat->execute();
$getRapat->bind_result($judulRapat);
$getRapat->fetch();
$getRapat->close();

// Simpan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ringkasan = trim($_POST['ringkasan'] ?? '');

    if (empty($ringkasan)) {
        header("Location: edit.php?id=$id&msg=kosong&obj=notulen");
        exit;
    }

    $stmtUpdate = $conn->prepare("UPDATE notulen SET ringkasan = ? WHERE id = ?");
    $stmtUpdate->bind_param("si", $ringkasan, $id);

    if ($stmtUpdate->execute()) {
        header("Location: notulen.php?msg=updated&obj=notulen");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=notulen");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid py-4">

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= $pageTitle ?></h5>

                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Judul Rapat</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($judulRapat) ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ringkasan Notulen</label>
                                <textarea name="ringkasan" class="form-control" rows="6" required><?= htmlspecialchars($data['ringkasan']) ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="notulen.php" class="btn btn-secondary">Kembali</a>
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