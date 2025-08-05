<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

// Hanya admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized&obj=info");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM informasi WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: info.php?msg=deleted&obj=info");
        exit;
    } else {
        header("Location: info.php?msg=error&obj=info");
        exit;
    }
} else {
    header("Location: info.php?msg=invalid&obj=info");
    exit;
}