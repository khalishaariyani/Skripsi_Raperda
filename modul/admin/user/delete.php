<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

// ✅ Validasi role
if ($_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "/user.php?msg=unauthorized&obj=user");
    exit;
}

// ✅ Ambil ID user
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ✅ Cek apakah ID valid
if ($id <= 0) {
    header("Location: user.php?msg=invalid&obj=user");
    exit;
}

// ✅ Cek keberadaan user terlebih dahulu
$cek = $conn->query("SELECT * FROM user WHERE id = $id");
if ($cek->num_rows === 0) {
    header("Location: user.php?msg=notfound&obj=user");
    exit;
}

// ✅ Eksekusi penghapusan
$hapus = $conn->query("DELETE FROM user WHERE id = $id");

if ($hapus) {
    header("Location: user.php?msg=deleted&obj=user");
    exit;
} else {
    header("Location: user.php?msg=error&obj=user");
    exit;
}
