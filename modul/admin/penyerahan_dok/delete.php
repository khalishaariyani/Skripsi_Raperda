<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=penyerahan");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: penyerahan.php?msg=invalid&obj=penyerahan");
    exit;
}

// Cek apakah data benar-benar ada
$cek = $conn->prepare("SELECT id FROM penyerahan_dokumen WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows === 0) {
    header("Location: penyerahan.php?msg=invalid&obj=penyerahan");
    exit;
}

// Hapus data
$stmt = $conn->prepare("DELETE FROM penyerahan_dokumen WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    header("Location: penyerahan.php?msg=deleted&obj=penyerahan");
    exit;
} else {
    header("Location: penyerahan.php?msg=error&obj=penyerahan");
    exit;
}
