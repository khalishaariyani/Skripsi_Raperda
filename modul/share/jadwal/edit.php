<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Validasi role
if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: jadwal.php?msg=unauthorized&obj=jadwalrapat");
    exit;
}

$pageTitle = 'Edit Jadwal Rapat';

// ID dari GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: jadwal.php?msg=invalid&obj=jadwalrapat");
    exit;
}

// Ambil data lama
$stmt = $conn->prepare("SELECT * FROM jadwalrapat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: jadwal.php?msg=invalid&obj=jadwalrapat");
    exit;
}
$jadwal = $result->fetch_assoc();

// Submit update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = trim($_POST['judul_rapat'] ?? '');
    $tanggal     = $_POST['tanggal'] ?? '';
    $waktu       = $_POST['waktu'] ?? '';
    $tempat      = trim($_POST['tempat'] ?? '');
    $pengusul    = trim($_POST['pengusul'] ?? '');
    $agenda      = trim($_POST['agenda_rapat'] ?? '');
    $peserta     = trim($_POST['peserta'] ?? '');
    $dibuat_oleh = trim($_POST['dibuat_oleh'] ?? '');
    $status      = ($_SESSION['role'] === 'admin') ? ($_POST['status'] ?? 'usulan') : $jadwal['status'];

    // Validasi wajib isi
    if (empty($judul) || empty($tanggal) || empty($waktu) || empty($tempat)) {
        header("Location: edit.php?id=$id&msg=kosong&obj=jadwalrapat");
        exit;
    }

    // Eksekusi update
    $stmt = $conn->prepare("UPDATE jadwalrapat 
        SET judul_rapat = ?, tanggal = ?, waktu = ?, tempat = ?, pengusul = ?, agenda_rapat = ?, peserta = ?, status = ?, dibuat_oleh = ? 
        WHERE id = ?");
    $stmt->bind_param(
        "sssssssssi",
        $judul,
        $tanggal,
        $waktu,
        $tempat,
        $pengusul,
        $agenda,
        $peserta,
        $status,
        $dibuat_oleh,
        $id
    );

    if ($stmt->execute()) {
        header("Location: jadwal.php?msg=updated&obj=jadwalrapat");
        exit;
    } else {
        header("Location: edit.php?id=$id&msg=error&obj=jadwalrapat");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="id" dir="ltr">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content flex-grow-1">
            <div class="container-fluid mt-4">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h5>
                    </div>
                    <form method="post" class="card-body">
                        <div class="form-group mb-3">
                            <label>Judul Rapat</label>
                            <input type="text" name="judul_rapat" class="form-control"
                                value="<?= htmlspecialchars($jadwal['judul_rapat']) ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control"
                                value="<?= htmlspecialchars($jadwal['tanggal']) ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Waktu</label>
                            <input type="time" name="waktu" class="form-control"
                                value="<?= htmlspecialchars($jadwal['waktu']) ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Tempat</label>
                            <input type="text" name="tempat" class="form-control"
                                value="<?= htmlspecialchars($jadwal['tempat']) ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Pengusul</label>
                            <input type="text" name="pengusul" class="form-control"
                                value="<?= htmlspecialchars($jadwal['pengusul']) ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label>Agenda Paripurna</label>
                            <input type="text" name="agenda_rapat" class="form-control"
                                value="<?= htmlspecialchars($jadwal['agenda_rapat']) ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label>Peserta</label>
                            <input type="text" name="peserta" class="form-control"
                                value="<?= htmlspecialchars($jadwal['peserta']) ?>">
                        </div>

                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <div class="form-group mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="usulan" <?= $jadwal['status'] === 'usulan' ? 'selected' : '' ?>>Usulan</option>
                                    <option value="disetujui" <?= $jadwal['status'] === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                    <option value="dibatalkan" <?= $jadwal['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="form-group mb-3">
                            <label>Dibuat Oleh</label>
                            <input type="text" name="dibuat_oleh" class="form-control"
                                value="<?= htmlspecialchars($jadwal['dibuat_oleh']) ?>" readonly>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="jadwal.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>