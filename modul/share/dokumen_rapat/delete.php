<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// ✅ Role yang diizinkan
if (!in_array($_SESSION['role'], ['admin', 'persidangan'])) {
    header("Location: dok_rapat.php?msg=unauthorized&obj=dok_rapat");
    exit;
}

// ✅ Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: dok_rapat.php?msg=invalid&obj=dok_rapat");
    exit;
}

// ✅ Ambil nama file dari database
$stmt = $conn->prepare("SELECT file_dok FROM dok_rapat WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: dok_rapat.php?msg=invalid&obj=dok_rapat");
    exit;
}

$data = $res->fetch_assoc();

// ✅ Hapus semua file (karena bisa lebih dari satu, dipisah "|")
$files = explode('|', $data['file_dok']);
foreach ($files as $file) {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/raperda/uploads/dok_rapat/' . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// ✅ Hapus data dari tabel
$delete = $conn->prepare("DELETE FROM dok_rapat WHERE id = ?");
$delete->bind_param("i", $id);
if ($delete->execute()) {
    header("Location: dok_rapat.php?msg=deleted&obj=dok_rapat");
    exit;
} else {
    header("Location: dok_rapat.php?msg=error&obj=dok_rapat");
    exit;
}
