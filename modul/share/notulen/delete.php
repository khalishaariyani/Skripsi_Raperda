<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=notulen");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: notulen.php?msg=invalid&obj=notulen");
    exit;
}

// Cek apakah data notulen ada
$cek = $conn->prepare("SELECT id FROM notulen WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows === 0) {
    header("Location: notulen.php?msg=invalid&obj=notulen");
    exit;
}

// Hapus data
$stmt = $conn->prepare("DELETE FROM notulen WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: notulen.php?msg=deleted&obj=notulen");
    exit;
} else {
    header("Location: notulen.php?msg=error&obj=notulen");
    exit;
}
