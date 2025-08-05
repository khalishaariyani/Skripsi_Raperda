<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Hanya admin yang boleh menghapus
if ($_SESSION['role'] !== 'admin') {
    header("Location: jadwal.php?msg=unauthorized&obj=jadwalrapat");
    exit;
}

// Validasi ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: jadwal.php?msg=invalid&obj=jadwalrapat");
    exit;
}

// Hapus data berdasarkan ID
$stmt = $conn->prepare("DELETE FROM jadwalrapat WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: jadwal.php?msg=deleted&obj=jadwalrapat");
    exit;
} else {
    header("Location: jadwal.php?msg=error&obj=jadwalrapat");
    exit;
}
