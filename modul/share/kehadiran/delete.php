<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// ✅ Hanya admin & persidangan yang bisa hapus
if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: kehadiran.php?msg=unauthorized&obj=kehadiran");
    exit;
}

// ✅ Validasi ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: kehadiran.php?msg=invalid&obj=kehadiran");
    exit;
}

// ✅ Eksekusi DELETE
$stmt = $conn->prepare("DELETE FROM kehadiranrapat WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: kehadiran.php?msg=deleted&obj=kehadiran");
    exit;
} else {
    header("Location: kehadiran.php?msg=error&obj=kehadiran");
    exit;
}
