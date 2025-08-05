<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// ✅ Proteksi role
if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: ../index.php?msg=unauthorized&obj=dokumentasi");
    exit;
}

$pageTitle = 'Tambah Dokumentasi Kegiatan';

// ✅ Ambil rapat yang disetujui DAN BELUM ada dokumentasi
$jadwals = $conn->query("
    SELECT j.id, j.judul_rapat, j.tanggal
    FROM jadwalrapat j
    LEFT JOIN dokumentasikegiatan d ON j.id = d.idRapat
    WHERE j.status = 'disetujui' AND d.id IS NULL
    ORDER BY j.tanggal DESC
");
?>



<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">

        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">
                <div class="card">
                    <div class="px-4 pt-4 pb-2" style="background: #eceff6; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                        <h2 class="mb-0" style="font-size:1.3rem; font-weight:700; color:#222; line-height:1;">
                            Tambah Dokumentasi Rapat
                        </h2>
                    </div>
                    <div class="card-body">

                        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'invalid_input'): ?>
                            <div class="alert alert-danger">Input tidak lengkap atau format file tidak valid.</div>
                        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'upload_failed'): ?>
                            <div class="alert alert-danger">Gagal mengunggah file. Silakan coba lagi.</div>
                        <?php endif; ?>

                        <form action="upload_dokumentasi.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Judul Rapat <span class="text-danger">*</span></label>
                                <select name="idRapat" class="form-control form-control-lg" required>
                                    <option value="">-- Pilih Judul Rapat --</option>
                                    <?php if ($jadwals->num_rows > 0): ?>
                                        <?php while ($j = $jadwals->fetch_assoc()): ?>
                                            <option value="<?= $j['id'] ?>">
                                                <?= htmlspecialchars($j['judul_rapat']) ?> (<?= date('d/m/Y', strtotime($j['tanggal'])) ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada rapat yang tersedia.</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control form-control-lg" rows="3" placeholder="Contoh: Dokumentasi rapat penyusunan perda..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload File (Foto/PDF) <span class="text-danger">*</span></label>
                                <input type="file" name="file[]" class="form-control form-control-lg" required accept=".jpg,.jpeg,.png,.pdf" multiple>
                                <small class="form-text text-muted">
                                    Hanya file JPG, PNG, PDF. Maksimum 5MB per file.
                                </small>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="dokumentasi.php" class="btn btn-secondary me-2">Batal</a>
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