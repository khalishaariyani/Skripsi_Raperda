<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Konfigurasi SMTP Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'raperda01@gmail.com';
    $mail->Password   = 'qlzr qwxg mmtn maqp'; // ← GANTI dengan app password Gmail kamu
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('raperda01@gmail.com', 'Sekretariat DPRD');

    // Ambil penerima undangan lengkap dengan tanggal, jam, lokasi
    $query = "
        SELECT u.nama, u.email, ur.tanggal, ur.jam, ur.lokasi
        FROM undanganrapat ur
        JOIN user u ON ur.penerima = u.nama
        WHERE u.email IS NOT NULL AND u.email != ''
    ";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $mail->clearAddresses();
            $mail->addAddress($row['email'], $row['nama']);

            // Format tanggal & jam
            $tanggal = date('d F Y', strtotime($row['tanggal']));
            $jam     = date('H:i', strtotime($row['jam']));
            $lokasi  = htmlspecialchars($row['lokasi']);

            // Isi email
            $mail->isHTML(true);
            $mail->Subject = 'Undangan Rapat RAPERDA';
            $mail->Body    = '
                Yth. <b>' . htmlspecialchars($row['nama']) . '</b>,<br><br>
                Anda diundang dalam agenda pembahasan <b>Peraturan Daerah</b> dengan detail sebagai berikut:<br><br>
                <b>Tanggal:</b> ' . $tanggal . '<br>
                <b>Jam:</b> ' . $jam . '<br>
                <b>Lokasi:</b> ' . $lokasi . '<br><br>
                Silakan login ke sistem aplikasi untuk informasi lebih lanjut dan konfirmasi kehadiran Anda.<br><br>
                Hormat Kami,<br><b>Sekretariat DPRD</b>
            ';

            $mail->AltBody = 'Yth. ' . $row['nama'] . ', Anda diundang rapat RAPERDA pada ' . $tanggal . ' pukul ' . $jam . ' di ' . $lokasi;

            $mail->send();
        }

        $_SESSION['success'] = '✅ Semua undangan berhasil dikirim ke penerima yang terdaftar.';
    } else {
        $_SESSION['success'] = '⚠️ Tidak ada penerima undangan yang memiliki email.';
    }

    header("Location: undangan.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = '❌ Gagal mengirim email: ' . $mail->ErrorInfo;
    header("Location: undangan.php");
    exit;
}
