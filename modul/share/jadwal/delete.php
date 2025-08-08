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

// Cek apakah id_rapat sudah ada di tabel arsiprapat
$stmt = $conn->prepare("SELECT COUNT(*) FROM arsiprapat WHERE id_rapat = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    // Jika sudah ada di arsiprapat, blokir penghapusan
    header("Location: jadwal.php?msg=fk_blocked&obj=jadwalrapat");
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
