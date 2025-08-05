<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Hanya admin yang boleh hapus
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

// Ambil ID komentar
$id = intval($_GET['id'] ?? 0);

// Validasi ID
if ($id > 0) {
    // Cek apakah data ada
    $check = $conn->query("SELECT id FROM diskusiperda WHERE id = $id");
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM diskusiperda WHERE id = $id");
        header("Location: diskusi.php?msg=deleted&obj=diskusi");
        exit;
    } else {
        header("Location: diskusi.php?msg=error&obj=diskusi");
        exit;
    }
} else {
    header("Location: diskusi.php?msg=invalid&obj=diskusi");
    exit;
}
