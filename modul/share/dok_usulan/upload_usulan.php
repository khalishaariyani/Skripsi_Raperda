<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once ROOT_PATH . '/session_start.php';
require_once CONFIG_PATH . '/koneksi.php';

if ($_SESSION['role'] !== 'anggota') {
    header("Location: " . BASE_URL . "/index.php?msg=unauthorized");
    exit;
}

$id_user = intval($_SESSION['id']);
$deskripsi = trim($_POST['deskripsi'] ?? '');
$judul_rapat_id = intval($_POST['judul_rapat'] ?? 0);
$waktu_sekarang = date('dmY_His');

// Direktori tujuan
$target_dir = ROOT_PATH . "/uploads/usulan/";
if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

// Validasi file
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
    die("❌ File tidak ditemukan atau terjadi kesalahan saat upload.");
}

$originalName = $_FILES['file']['name'];
$tmpPath = $_FILES['file']['tmp_name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowed = ['pdf', 'jpg', 'jpeg', 'png', 'docx'];
if (!in_array($ext, $allowed)) {
    die("❌ Format file tidak diizinkan: $ext");
}

// ✅ Ambil nama user
$getUser = $conn->query("SELECT nama FROM user WHERE id = $id_user LIMIT 1");
$userData = $getUser->fetch_assoc();
$nama_user = str_replace(' ', '', strtolower($userData['nama'] ?? 'user'));

// ✅ Ambil judul rapat
$getRapat = $conn->query("SELECT judul_rapat FROM jadwalrapat WHERE id = $judul_rapat_id LIMIT 1");
$rapatData = $getRapat->fetch_assoc();
$judul_rapat = str_replace(' ', '_', strtolower($rapatData['judul_rapat'] ?? 'rapat'));

// 🔧 Format nama file akhir
$newName = "usulan_{$nama_user}_{$judul_rapat}." . $ext;
$savePath = $target_dir . $newName;

// ✅ Pindahkan file dan simpan ke DB
if (move_uploaded_file($tmpPath, $savePath)) {
    $stmt = $conn->prepare("INSERT INTO dokumen_usulan (id_user, nama_file, deskripsi, tanggal_upload, judul_rapat) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("issi", $id_user, $newName, $deskripsi, $judul_rapat_id);

    if ($stmt->execute()) {
        header("Location: usulan.php?msg=upload_success");
        exit;
    } else {
        die("❌ Gagal menyimpan ke database.");
    }
} else {
    die("❌ Gagal memindahkan file.");
}
