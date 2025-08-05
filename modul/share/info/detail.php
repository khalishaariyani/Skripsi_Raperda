<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Ambil ID
$id = intval($_GET['id'] ?? 0);
$role = $_SESSION['role'] ?? 'guest';

// Validasi ID
if ($id <= 0) {
    die("ID tidak valid.");
}

// Query data
$data = $conn->query("SELECT * FROM informasi WHERE id = $id")->fetch_assoc();

// Validasi data
if (!$data) {
    die("Data dengan ID $id tidak ditemukan.");
}

// Tentukan path dashboard sesuai role
if ($role === 'admin') {
    $backUrl = BASE_URL . '/modul/admin/info/info.php';
} elseif ($role === 'persidangan') {
    $backUrl = BASE_URL . '/modul/persidangan/dashboard.php';
} elseif ($role === 'anggota') {
    $backUrl = BASE_URL . '/modul/anggota/dashboard.php';
} else {
    $backUrl = BASE_URL . '/index.php';
}

?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body>
    <?php require_once LAYOUT_PATH . '/header.php'; ?>
    <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

    <main class="main-content app-content">
        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8 mx-auto mt-5">

                    <!-- Wrapper Paper -->
                    <div class="bg-white p-5 rounded-4 shadow"
                        style="position: relative; max-width: 900px; margin: 0 auto;">


                        <!-- Judul -->
                        <h1 class="fw-bold mb-3 text-center">
                            <?= htmlspecialchars($data['judul']) ?>
                        </h1>

                        <!-- Meta Info -->
                        <div class="text-muted text-center mb-4">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($data['penulis'] ?? 'Admin') ?>
                            &middot; <?= date('d M Y', strtotime($data['created_at'] ?? $data['tanggal'] ?? date('Y-m-d'))) ?>
                        </div>

                        <!-- Gambar -->
                        <?php
                        $gambar = json_decode($data['gambar'] ?? '[]', true);
                        if (json_last_error() !== JSON_ERROR_NONE || !is_array($gambar)) {
                            $gambar = [$data['gambar']];
                        }
                        foreach ($gambar as $img) :
                            if (!empty($img)) :
                        ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($img) ?>"
                                    alt="Foto"
                                    class="img-fluid mb-4 rounded shadow d-block mx-auto"
                                    style="max-height: 400px; object-fit: cover;">
                        <?php endif;
                        endforeach; ?>

                        <!-- Isi -->
                        <div class="fs-6 mb-5" style="white-space: pre-line; text-align: justify; line-height: 1.8;">
                            <?= nl2br(htmlspecialchars($data['isi'] ?? '')) ?>
                        </div>

                        <!-- Tombol Kembali -->
                        <div class="text-center">
                            <a href="<?= $backUrl ?>" class="btn btn-danger rounded-pill px-4">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                            </a>
                        </div>

                    </div>
                    <!-- End Wrapper -->

                </div>
            </div>
    </main>


    <?php require_once LAYOUT_PATH . '/footer.php'; ?>
</body>

</html>