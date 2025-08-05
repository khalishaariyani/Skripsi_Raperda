<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Cek role harus admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

// Validasi id
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: perda.php?msg=invalid&obj=perda");
    exit;
}

// Pastikan data ada
$stmt = $conn->prepare("SELECT idPerda FROM perda WHERE idPerda = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->fetch_assoc()) {
    header("Location: perda.php?msg=notfound&obj=perda");
    exit;
}

// Jalankan query DELETE
$delete = $conn->prepare("DELETE FROM perda WHERE idPerda = ?");
$delete->bind_param("i", $id);

if ($delete->execute()) {
    header("Location: perda.php?msg=deleted&obj=perda");
    exit;
} else {
    header("Location: perda.php?msg=error&obj=perda");
    exit;
}
