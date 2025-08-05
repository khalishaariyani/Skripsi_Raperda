<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

// Hanya admin dan anggota
if (!in_array($_SESSION['role'], ['admin', 'anggota', 'persidangan'])) {
    header("Location: usulan.php?msg=unauthorized");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: usulan.php?msg=invalid");
    exit;
}

// Ambil data berdasarkan id_usulan
$query = $conn->prepare("SELECT * FROM dokumen_usulan WHERE id_usulan = ?");
$query->bind_param("i", $id);
$query->execute();
$res = $query->get_result();
$usulan = $res->fetch_assoc();
$query->close();

if (!$usulan) {
    header("Location: usulan.php?msg=notfound");
    exit;
}

// Validasi hak akses
if ($_SESSION['role'] === 'anggota' && $usulan['id_user'] != $_SESSION['id']) {
    header("Location: usulan.php?msg=forbidden");
    exit;
}

// Hapus file dari direktori
$filePath = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/usulan/' . $usulan['nama_file'];
if (file_exists($filePath)) {
    unlink($filePath);
}

// Hapus dari database
$del = $conn->prepare("DELETE FROM dokumen_usulan WHERE id_usulan = ?");
$del->bind_param("i", $id);
if ($del->execute()) {
    header("Location: usulan.php?msg=deleted&obj=usulan");
    exit;
} else {
    header("Location: usulan.php?msg=error&obj=usulan");
    exit;
}
