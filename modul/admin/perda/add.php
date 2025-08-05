<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$pageTitle = 'Tambah Data Perda';

// Ambil jadwal rapat yang status disetujui & belum terpakai di perda
$jadwal = $conn->query("
    SELECT j.id, j.judul_rapat, j.tanggal, j.tempat, j.pengusul
    FROM jadwalrapat j
    WHERE j.status = 'disetujui'
      AND NOT EXISTS (
        SELECT 1 FROM perda p WHERE p.judul = j.judul_rapat
      )
    ORDER BY j.tanggal DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor = trim($_POST['nomor_perda']);
    $id_jadwal = intval($_POST['id_jadwal']);
    $status = trim($_POST['status']);
    $catatan = trim($_POST['catatan']);
    $pengusul = trim($_POST['pengusul']); // diambil dari input hidden

    if ($nomor && $id_jadwal && $status && $pengusul) {
        // Ambil detail jadwal rapat
        $stmt = $conn->prepare("SELECT judul_rapat, tanggal FROM jadwalrapat WHERE id = ?");
        $stmt->bind_param("i", $id_jadwal);
        $stmt->execute();
        $jadwalData = $stmt->get_result()->fetch_assoc();

        if ($jadwalData) {
            $judul = $jadwalData['judul_rapat'];
            $tanggal = $jadwalData['tanggal'];

            $stmt2 = $conn->prepare("INSERT INTO perda (nomor_perda, tanggal_masuk, status, pengusul, judul, catatan)
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("ssssss", $nomor, $tanggal, $status, $pengusul, $judul, $catatan);

            if ($stmt2->execute()) {
                header("Location: perda.php?msg=added&obj=perda");
                exit;
            } else {
                header("Location: perda.php?msg=failed&obj=perda");
            }
        } else {
            $error = "Data jadwal tidak valid.";
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
                    <div class="px-4 pt-4 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                        <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                            Tambah Perda Rapat
                        </h2>
                    </div>
                    <div class="card-body">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- Judul Rapat -->
                            <div class="mb-3">
                                <label>Judul Rapat</label>
                                <select name="id_jadwal" id="select-jadwal" class="form-select" required>
                                    <option value="">-- Pilih Judul Rapat --</option>
                                    <?php while ($row = $jadwal->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>"
                                            data-tanggal="<?= $row['tanggal'] ?>"
                                            data-tempat="<?= htmlspecialchars($row['tempat']) ?>"
                                            data-pengusul="<?= htmlspecialchars($row['pengusul']) ?>">
                                            <?= htmlspecialchars($row['judul_rapat']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Nomor Perda -->
                            <div class="mb-3">
                                <label>Nomor Perda</label>
                                <input type="text" name="nomor_perda" class="form-control" required>
                            </div>

                            <!-- Tanggal & Tempat -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Tanggal Masuk</label>
                                    <input type="date" id="tanggal_masuk" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Tempat Rapat</label>
                                    <input type="text" id="tempat" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- Pengusul & Status -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Pengusul</label>
                                    <input type="text" id="pengusul_display" class="form-control" readonly>
                                    <input type="hidden" name="pengusul" id="pengusul">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Status Perda</label>
                                    <input type="text" name="status" class="form-control" required>
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div class="mb-3">
                                <label>Catatan</label>
                                <textarea name="catatan" rows="3" class="form-control"></textarea>
                            </div>

                            <!-- Tombol -->
                            <div class="d-flex justify-content-end">
                                <a href="perda.php" class="btn btn-secondary me-2">Batal</a>
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
    <script>
        const jadwalSelect = document.getElementById('select-jadwal');
        const tanggalInput = document.getElementById('tanggal_masuk');
        const tempatInput = document.getElementById('tempat');
        const pengusulInputDisplay = document.getElementById('pengusul_display');
        const pengusulInput = document.getElementById('pengusul');

        jadwalSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            tanggalInput.value = selected.dataset.tanggal || '';
            tempatInput.value = selected.dataset.tempat || '';
            pengusulInputDisplay.value = selected.dataset.pengusul || '';
            pengusulInput.value = selected.dataset.pengusul || '';
        });
    </script>
</body>

</html>