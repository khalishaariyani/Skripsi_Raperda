<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Validasi role
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=dokumentasi");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: dokumentasi.php?msg=invalid&obj=dokumentasi");
    exit;
}

// Ambil data file lama
$stmt = $conn->prepare("SELECT file FROM dokumentasikegiatan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: dokumentasi.php?msg=invalid&obj=dokumentasi");
    exit;
}

// Hapus file fisik
$filePath = ROOT_PATH . '/uploads/dokumentasi/' . $data['file'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Hapus dari database
$stmtDelete = $conn->prepare("DELETE FROM dokumentasikegiatan WHERE id = ?");
$stmtDelete->bind_param("i", $id);

if ($stmtDelete->execute()) {
    header("Location: dokumentasi.php?msg=deleted&obj=dokumentasi");
    exit;
} else {
    header("Location: dokumentasi.php?msg=error&obj=dokumentasi");
    exit;
}
