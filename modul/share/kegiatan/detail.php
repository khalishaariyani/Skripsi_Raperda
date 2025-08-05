<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$id = $_GET['id'] ?? 0;
$query = "
    SELECT d.*, j.judul_rapat 
    FROM dokumentasikegiatan d 
    JOIN jadwalrapat j ON d.idRapat = j.id 
    WHERE d.id = $id
";
$result = $conn->query($query);
$data = $result->fetch_assoc();

if (!$data) {
    echo "<h4>Data tidak ditemukan.</h4>";
    exit;
}

$filename    = $data['file'];

// Lokasi file secara server (untuk file_exists dan akses fisik)
$uploadDir   = ROOT_PATH . '/uploads/dokumentasi/';
$filePath    = $uploadDir . $filename;

// Lokasi file secara web (untuk tag <img> atau <a href>)
$webBaseURL  = BASE_URL . '/uploads/dokumentasi/';
$webPath     = $webBaseURL . $filename;

?>

<!DOCTYPE html>
<html lang="id">
<?php require_once LAYOUT_PATH . '/head.php'; ?>

<body class="d-flex flex-column min-vh-100">
    <div class="page d-flex flex-column flex-grow-1">
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <div class="page-header">
            <h1 class="page-title">Detail Dokumentasi</h1>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dokumentasi.php">Dokumentasi</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>

        <main class="main-content app-content">
            <div class="container-fluid">
                <div class="card mt-4 shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3 text-uppercase text-indigo fw-bold"><?= htmlspecialchars($data['judul_rapat']) ?></h4>

                        <div class="mb-3">
                            <p><strong>📌 Diunggah oleh:</strong> <?= htmlspecialchars($data['diunggah_oleh']) ?></p>
                            <p><strong>🕒 Waktu unggah:</strong> <?= date('d/m/Y H:i:s', strtotime($data['created_at'])) ?></p>
                            <p><strong>📝 Keterangan:</strong><br><?= nl2br(htmlspecialchars($data['keterangan'])) ?></p>
                        </div>

                        <div class="text-center mt-4">
                            <?php if (file_exists($filePath) && preg_match('/\.(jpg|jpeg|png|webp)$/i', $filename)): ?>
                                <img src="<?= $webPath ?>" class="img-fluid rounded border shadow-sm" style="max-height: 450px;" alt="Foto Dokumentasi">
                            <?php elseif (file_exists($filePath)): ?>
                                <div class="alert alert-info">
                                    File tersedia tapi bukan gambar.<br>
                                    <a href="<?= $webPath ?>" class="btn btn-sm btn-primary mt-2" target="_blank">📎 Unduh File</a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    File dokumentasi tidak ditemukan. Pastikan file <code><?= $filename ?></code> berada di folder <code><?= $webPath ?></code>
                                </div>
                            <?php endif; ?>

                        </div>



                        <div class="mt-4">
                            <a href="dokumentasi.php" class="btn btn-secondary">
                                <i class="fe fe-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php require_once LAYOUT_PATH . '/footer.php'; ?>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>