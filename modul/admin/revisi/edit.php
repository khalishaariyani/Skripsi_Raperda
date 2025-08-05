<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Edit Laporan Revisi';
$id = intval($_GET['id'] ?? 0);
$data = $conn->query("SELECT * FROM laporanrevisi WHERE id = $id")->fetch_assoc();
if (!$data) {
    header("Location: revisi.php?msg=not_found");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengusul = trim($_POST['pengusul']);
    $jenis_revisi = trim($_POST['jenis_revisi']);
    $isi_revisi = trim($_POST['isi_revisi']);
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? date('Y-m-d');

    if ($pengusul && $jenis_revisi && $isi_revisi) {
        $stmt = $conn->prepare("UPDATE laporanrevisi SET pengusul=?, jenis_revisi=?, isi_revisi=?, tanggal_masuk=? WHERE id=?");
        $stmt->bind_param("ssssi", $pengusul, $jenis_revisi, $isi_revisi, $tanggal_masuk, $id);
        if ($stmt->execute()) {
            header("Location: revisi.php?msg=updated&obj=revisi");
            exit;
        } else {
            $error = "Gagal update data.";
            header("Location: revisi.php?msg=error&obj=revisi");
            exit;
        }
    } else {
        $error = "Semua field wajib diisi.";
    }
}

$judul = $conn->query("SELECT judul_rapat FROM jadwalrapat WHERE id = {$data['idRapat']}")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><?= $pageTitle ?></h5>
                        <a href="revisi.php" class="btn btn-sm btn-secondary"><i class="fe fe-arrow-left"></i> Kembali</a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Judul Rapat</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($judul['judul_rapat'] ?? '-') ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Nama Pengusul</label>
                                <input type="text" name="pengusul" class="form-control" value="<?= htmlspecialchars($data['pengusul']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Jenis Revisi</label>
                                <input type="text" name="jenis_revisi" class="form-control" value="<?= htmlspecialchars($data['jenis_revisi']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Isi Revisi</label>
                                <textarea name="isi_revisi" rows="4" class="form-control" required><?= htmlspecialchars($data['isi_revisi']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Tanggal Masuk</label>
                                <input type="date" name="tanggal_masuk" class="form-control" value="<?= htmlspecialchars($data['tanggal_masuk']) ?>" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-success"><i class="fe fe-save"></i> Simpan Perubahan</button>
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