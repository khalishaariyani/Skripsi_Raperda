<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
session_start();

// ✅ Kecualikan proteksi login untuk halaman register
$currentPath = str_replace('\\', '/', $_SERVER['PHP_SELF']);
if (strpos($currentPath, 'signup.php') === false && strpos($currentPath, 'register.php') === false) {
    if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

// ✅ Cegah double include
if (defined('SESSION_CHECKED')) return;
define('SESSION_CHECKED', true);

// ✅ Ambil informasi user saat ini
$currentRole = $_SESSION['role'] ?? null;
$currentUserId = $_SESSION['id'] ?? null;

// ✅ Deteksi folder dan modul berdasarkan URL
$segments = explode('/', trim($currentPath, '/'));
$key = array_search('modules', $segments);
if ($key === false) {
    // Jika tidak di dalam folder modules, tidak validasi RBAC
    return;
}

$currentFolder = $segments[$key + 1] ?? '';
$currentModule = $segments[$key + 2] ?? '';

// ✅ Daftar RBAC ...
$sharedAccessControl = [ /* tetap seperti sebelumnya */];

// ✅ Validasi berdasarkan folder
if ($currentFolder === 'shared') {
    if (!array_key_exists($currentModule, $sharedAccessControl)) {
        header("Location: " . BASE_URL . "/modules/$currentRole/index.php?error=forbidden_shared");
        exit;
    }
    if (!in_array($currentRole, $sharedAccessControl[$currentModule])) {
        header("Location: " . BASE_URL . "/modules/$currentRole/index.php?error=unauthorized");
        exit;
    }
} else {
    if ($currentRole && $currentFolder !== $currentRole) {
        header("Location: " . BASE_URL . "/modules/$currentRole/index.php?error=forbidden");
        exit;
    }
}
