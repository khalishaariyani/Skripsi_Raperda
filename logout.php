<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';

session_start();
session_unset(); // Hapus semua variabel session
session_destroy(); // Hapus session

// Redirect ke halaman login utama
header("Location: " . BASE_URL . "/index.php?msg=logout");
exit;
