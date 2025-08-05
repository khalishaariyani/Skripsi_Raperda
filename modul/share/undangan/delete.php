<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Hanya admin yang boleh menghapus
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=undangan");
    exit;
}

$id = intval($_GET['id'] ?? 0);

// Jika ID valid
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM undanganrapat WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: undangan.php?msg=deleted&obj=undangan");
        exit;
    } else {
        header("Location: undangan.php?msg=error&obj=undangan");
        exit;
    }
} else {
    header("Location: undangan.php?msg=invalid&obj=undangan");
    exit;
}
