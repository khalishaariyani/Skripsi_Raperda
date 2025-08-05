<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: arsip.php?msg=invalid&obj=arsip");
    exit;
}

// Ambil data untuk hapus file
$stmt = $conn->prepare("SELECT file_path FROM arsiprapat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: arsip.php?msg=not_found");
    exit;
}

// Hapus file
$path = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/arsip/' . $data['file_path'];
if (file_exists($path)) {
    unlink($path);
}

// Hapus dari database
$stmt = $conn->prepare("DELETE FROM arsiprapat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: arsip.php?msg=deleted&obj=arsip");
exit;
