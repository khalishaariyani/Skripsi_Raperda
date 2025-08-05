<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Tambah Laporan Revisi';

// Cek rapat yg sudah direvisi
$usedIds = [];
$used = $conn->query("SELECT DISTINCT idRapat FROM laporanrevisi");
while ($u = $used->fetch_assoc()) {
    $usedIds[] = $u['idRapat'];
}

// Rapat yg belum direvisi
$jadwals = $conn->query("SELECT id, judul_rapat FROM jadwalrapat WHERE status='disetujui' ORDER BY tanggal DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRapat = intval($_POST['idRapat']);
    $pengusul = trim($_POST['pengusul']);
    $jenis_revisi = trim($_POST['jenis_revisi']);
    $isi_revisi = trim($_POST['isi_revisi']);
    $tanggal_masuk = $_POST['tanggal_masuk'] ?? date('Y-m-d');

    if ($idRapat && $pengusul && $jenis_revisi && $isi_revisi) {
        $stmt = $conn->prepare("INSERT INTO laporanrevisi (idRapat, pengusul, jenis_revisi, isi_revisi, tanggal_masuk) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $idRapat, $pengusul, $jenis_revisi, $isi_revisi, $tanggal_masuk);
        if ($stmt->execute()) {
            header("Location: revisi.php?msg=added&obj=revisi");
            exit;
        } else {
            header("Location: revisi.php?msg=error&obj=revisi");
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
                                <select name="idRapat" class="form-select" required>
                                    <option value="">-- Pilih Judul Rapat --</option>
                                    <?php while ($j = $jadwals->fetch_assoc()): ?>
                                        <?php if (!in_array($j['id'], $usedIds)): ?>
                                            <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['judul_rapat']) ?></option>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Nama Pengusul</label>
                                <input type="text" name="pengusul" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Jenis Revisi</label>
                                <input type="text" name="jenis_revisi" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Isi Revisi</label>
                                <textarea name="isi_revisi" rows="4" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Tanggal Masuk</label>
                                <input type="date" name="tanggal_masuk" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-success"><i class="fe fe-save"></i> Simpan Revisi</button>
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