<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: dokumentasi.php?msg=unauthorized");
    exit;
}

$idRapat    = isset($_POST['idRapat']) ? intval($_POST['idRapat']) : 0;
$keterangan = trim($_POST['keterangan'] ?? '');
$uploader   = $_SESSION['nama'] ?? 'anonim';
$files      = $_FILES['file'] ?? null;

// Ubah ke array jika hanya satu file
if (!is_array($files['name'])) {
    foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $key) {
        $files[$key] = [$files[$key]];
    }
}

// Validasi input
if (!$idRapat || !$files || !isset($files['name'][0])) {
    header("Location: dokumentasi.php?msg=invalid_input");
    exit;
}

$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
$max_size    = 5 * 1024 * 1024; // 5MB
$upload_path = ROOT_PATH . '/uploads/dokumentasi/'; // ✅ FIXED PATH

if (!is_dir($upload_path)) {
    mkdir($upload_path, 0777, true);
}

$berhasil = 0;

foreach ($files['name'] as $i => $original) {
    $tmp     = $files['tmp_name'][$i];
    $size    = $files['size'][$i];
    $error   = $files['error'][$i];
    $ext     = strtolower(pathinfo($original, PATHINFO_EXTENSION));

    if ($error === UPLOAD_ERR_OK && in_array($ext, $allowed_ext) && $size <= $max_size) {
        $namaFile = time() . '_' . rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9_.]/', '_', $original);
        $dest     = $upload_path . $namaFile;

        if (move_uploaded_file($tmp, $dest)) {
            $stmt = $conn->prepare("INSERT INTO dokumentasikegiatan (idRapat, file, keterangan, diunggah_oleh, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt) {
                $stmt->bind_param("isss", $idRapat, $namaFile, $keterangan, $uploader);
                if ($stmt->execute()) {
                    $berhasil++;
                }
                $stmt->close();
            }
        }
    }
}

if ($berhasil > 0) {
    header("Location: dokumentasi.php?msg=uploaded&count=$berhasil");
} else {
    header("Location: dokumentasi.php?msg=upload_failed");
}
exit;
