<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM laporanrevisi WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: revisi.php?msg=deleted&obj=revisi ");
        exit;
    } else {
        header("Location: revisi.php?msg=error&obj=revisi");
        exit;
    }
} else {
    header("Location: revisi.php?msg=invalid&obj=revisi");
    exit;
}
