<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Edit Data Perda';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: perda.php?msg=invalid&obj=perda");
    exit;
}

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM perda WHERE idPerda = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
if (!$data) {
    header("Location: perda.php?msg=notfound&obj=perda");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor = trim($_POST['nomor_perda']);
    $tanggal = $_POST['tanggal_masuk'];
    $status = trim($_POST['status']);
    $pengusul = trim($_POST['pengusul']);
    $catatan = trim($_POST['catatan']);
    $judul = trim($_POST['judul']);

    if ($nomor && $tanggal && $status && $pengusul && $judul) {
        $stmt = $conn->prepare("UPDATE perda 
            SET nomor_perda=?, tanggal_masuk=?, status=?, pengusul=?, judul=?, catatan=? 
            WHERE idPerda=?");
        $stmt->bind_param("ssssssi", $nomor, $tanggal, $status, $pengusul, $judul, $catatan, $id);

        if ($stmt->execute()) {
            header("Location: perda.php?msg=updated&obj=perda");
            exit;
        } else {
            header("Location: perda.php?msg=error&obj=perda");
            exit;
        }
    } else {
        $error = "Semua field wajib diisi.";
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

        <main class="main-content app-content mt-8">
            <div class="container-fluid mt-4">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><?= $pageTitle ?></h5>
                    </div>
                    <div class="card-body">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- Judul Perda -->
                            <div class="mb-3">
                                <label>Judul Perda</label>
                                <input type="text" name="judul" class="form-control"
                                    value="<?= htmlspecialchars($data['judul']) ?>" readonly>
                            </div>

                            <!-- Nomor Perda -->
                            <div class="mb-3">
                                <label>Nomor Perda</label>
                                <input type="text" name="nomor_perda" class="form-control"
                                    value="<?= htmlspecialchars($data['nomor_perda']) ?>" required>
                            </div>

                            <!-- Tanggal & Status -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Tanggal Masuk</label>
                                    <input type="date" name="tanggal_masuk" class="form-control"
                                        value="<?= htmlspecialchars($data['tanggal_masuk']) ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Status Perda</label>
                                    <input type="text" name="status" class="form-control"
                                        value="<?= htmlspecialchars($data['status']) ?>" required>
                                </div>
                            </div>

                            <!-- Pengusul -->
                            <div class="mb-3">
                                <label>Pengusul</label>
                                <input type="text" name="pengusul" class="form-control"
                                    value="<?= htmlspecialchars($data['pengusul']) ?>" readonly>
                            </div>

                            <!-- Catatan -->
                            <div class="mb-3">
                                <label>Catatan</label>
                                <textarea name="catatan" rows="3" class="form-control"><?= htmlspecialchars($data['catatan']) ?></textarea>
                            </div>

                            <!-- Tombol Simpan -->
                            <div class="d-flex justify-content-between">
                                <a href="perda.php" class="btn btn-secondary">Kembali</a>
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