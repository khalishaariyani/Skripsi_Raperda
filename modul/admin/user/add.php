<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';

$nama = $_POST['nama'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

$query = "INSERT INTO user (nama,  email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $nama, $email, $password, $role);

if ($stmt->execute()) {
    header("Location: user.php?msg=added&obj=user");
} else {
    header("Location: user.php?msg=failed&obj=user");
}
exit;
