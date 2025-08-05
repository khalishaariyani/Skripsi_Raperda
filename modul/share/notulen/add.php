<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['persidangan', 'admin'])) {
    header("Location: ../index.php?msg=unauthorized&obj=notulen");
    exit;
}

$pageTitle = 'Tambah Notulen Rapat';

// Filter rapat yang BELUM ada notulen
$rapat = $conn->query("
    SELECT id, judul_rapat 
    FROM jadwalrapat 
    WHERE status = 'disetujui' 
      AND id NOT IN (SELECT id_rapat FROM notulen)
    ORDER BY tanggal DESC
");

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_rapat = intval($_POST['id_rapat'] ?? 0);
    $ringkasan = trim($_POST['ringkasan'] ?? '');
    $diinput_oleh = $_SESSION['id'];

    if ($id_rapat && $ringkasan) {
        $stmt = $conn->prepare("INSERT INTO notulen (id_rapat, ringkasan, diinput_oleh) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $id_rapat, $ringkasan, $diinput_oleh);

        if ($stmt->execute()) {
            header("Location: notulen.php?msg=added&obj=notulen");
            exit;
        } else {
            header("Location: notulen.php?msg=error&obj=notulen");
            exit;
        }
    } else {
        header("Location: add.php?msg=kosong&obj=notulen");
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
                    <div class="px-4 pt-4 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                        <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                            Tambah Notulen Rapat
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Judul Rapat</label>
                                <select name="id_rapat" class="form-select" required>
                                    <option value="">-- Pilih Rapat --</option>
                                    <?php while ($row = $rapat->fetch_assoc()): ?>
                                        <option value="<?= $row['id'] ?>">
                                            <?= htmlspecialchars($row['judul_rapat']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <?php if ($rapat->num_rows === 0): ?>
                                    <div class="text-muted mt-1">Semua rapat sudah memiliki notulen.</div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ringkasan Notulen</label>
                                <textarea name="ringkasan" class="form-control" rows="6" required></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="notulen.php" class="btn btn-secondary me-2">Batal</a>
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