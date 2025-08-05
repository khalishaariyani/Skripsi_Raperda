<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// ✅ Proteksi role
if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: jadwal.php?msg=unauthorized&obj=jadwalrapat");
    exit;
}

$pageTitle = 'Tambah Jadwal Rapat';
$role = $_SESSION['role'];
$dibuat_oleh = $_SESSION['nama'] ?? 'anonim';

// ✅ Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul_rapat'] ?? '');
    $tanggal   = $_POST['tanggal'] ?? '';
    $waktu     = $_POST['waktu'] ?? '';
    $tempat    = trim($_POST['tempat'] ?? '');
    $pengusul  = trim($_POST['pengusul'] ?? '');
    $agenda    = trim($_POST['agenda_rapat'] ?? '');
    $peserta   = trim($_POST['peserta'] ?? '');
    $status    = ($role === 'admin') ? ($_POST['status'] ?? 'usulan') : 'usulan';

    // Validasi field wajib
    if (empty($judul) || empty($tanggal) || empty($waktu) || empty($tempat)) {
        header("Location: jadwal.php?msg=kosong&obj=jadwalrapat");
        exit;
    }

    $query = "INSERT INTO jadwalrapat 
              (judul_rapat, tanggal, waktu, tempat, pengusul, agenda_rapat, peserta, status, dibuat_oleh)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssss", $judul, $tanggal, $waktu, $tempat, $pengusul, $agenda, $peserta, $status, $dibuat_oleh);

    if ($stmt->execute()) {
        header("Location: jadwal.php?msg=added&obj=jadwalrapat");
        exit;
    } else {
        header("Location: jadwal.php?msg=error&obj=jadwalrapat");
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

        <div class="main-content app-content mt-12">
            <div class="container-fluid">
                <div class="card custom-card">
                    <div class="px-4 pt-4 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                        <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                            Tambah Jadwal Rapat
                        </h2>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Judul Rapat</label>
                                <input type="text" name="judul_rapat" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Waktu</label>
                                <input type="time" name="waktu" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Tempat</label>
                                <input type="text" name="tempat" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Pengusul</label>
                                <input type="text" name="pengusul" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Agenda Paripurna</label>
                                <input type="text" name="agenda_rapat" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Peserta</label>
                                <input type="text" name="peserta" class="form-control">
                            </div>

                            <?php if ($role === 'admin'): ?>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="usulan">Usulan</option>
                                        <option value="disetujui">Disetujui</option>
                                        <option value="dibatalkan">Dibatalkan</option>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Dibuat Oleh</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($dibuat_oleh) ?>" readonly>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="jadwal.php" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i> Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
        <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
    </div>
</body>

</html>