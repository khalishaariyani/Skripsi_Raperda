<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?msg=unauthorized");
    exit;
}

$id_undangan = intval($_POST['id_undangan'] ?? 0);
$penerima = $_POST['penerima'] ?? [];
$penerima_emails = [];
$penerima_names = [];

foreach ($penerima as $item) {
    $parts = explode('|', $item, 2);
    if (count($parts) === 2) {
        list($nama, $email) = $parts;
        $penerima_names[] = $nama;
        $penerima_emails[] = $email;
    }
}
$undangan = $conn->query("SELECT u.*, j.judul_rapat, j.tanggal, j.waktu FROM undanganrapat u JOIN jadwalrapat j ON u.idRapat = j.id WHERE u.id = $id_undangan")->fetch_assoc();
// Pastikan kolom 'waktu' memang ada di tabel jadwalrapat, ganti dengan nama kolom yang benar jika berbeda
$subjek = "Undangan Rapat: " . $undangan['judul_rapat'];
$isi_email = "Yth. Bapak/Ibu,<br><br>Dengan hormat kami mengundang Anda untuk menghadiri rapat:<br><br>" .
    "<strong>Judul:</strong> " . $undangan['judul_rapat'] . "<br>" .
    "<strong>Tanggal:</strong> " . $undangan['tanggal'] . "<br>" .
    "<strong>Jam:</strong> " . (isset($undangan['jam']) ? $undangan['jam'] : (isset($undangan['waktu']) ? $undangan['waktu'] : '')) . "<br>" .
    "<strong>Tempat:</strong> " . $undangan['lokasi'] . "<br><br>" .
    "Demikian undangan ini kami sampaikan. Atas perhatiannya, kami ucapkan terima kasih.";
"<strong>Tanggal:</strong> " . $undangan['tanggal'] . "<br>" .
    "<strong>Jam:</strong> " . $undangan['jam'] . "<br>" .
    "<strong>Tempat:</strong> " . $undangan['lokasi'] . "<br><br>" .
    "Demikian undangan ini kami sampaikan. Atas perhatiannya, kami ucapkan terima kasih.";

if (isset($_POST['konfirmasi'])) {
    $stmt = $conn->prepare("UPDATE undanganrapat SET penerima = ? WHERE id = ?");
    $stmt->bind_param("si", $penerima_join, $id_undangan);

    if ($stmt->execute()) {
        // di sini bisa ditambahkan fungsi kirim email jika sudah siap PHPMailer
        header("Location: undangan.php?msg=confirmed");
        exit;
    } else {
        $error = "Gagal menyimpan penerima: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">
<?php require_once LAYOUT_PATH . '/head.php'; ?>


<head>
    <style>
        .email-preview {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            background: #f9f9f9;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .form-control[readonly] {
            background-color: #e9ecef;
        }

        textarea {
            height: 200px;
        }
    </style>
</head>

<body>
    <div class="page">
        <?php require_once LAYOUT_PATH . '/switcher.php'; ?>
        <?php require_once LAYOUT_PATH . '/header.php'; ?>
        <?php require_once LAYOUT_PATH . '/sidebar.php'; ?>

        <main class="main-content app-content mt-0">
            <div class="container-fluid mt-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Konfirmasi Kirim Undangan Rapat</h5>
                    </div>
                    <form method="POST" class="card-body">
                        <input type="hidden" name="id_undangan" value="<?= $id_undangan ?>">
                        <?php foreach ($penerima as $p): ?>
                            <input type="hidden" name="penerima[]" value="<?= htmlspecialchars($p) ?>">
                        <?php endforeach; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Preview Surat -->
                            <div class="col-md-6">
                                <div class="email-preview">
                                    <h5 style="text-align:center; font-weight:bold; text-transform:uppercase;">PEMERINTAH KOTA BANJARMASIN</h5>
                                    <p style="text-align:center;">Jalan Lambung Mangkurat No. 7, Banjarmasin<br>Email: dprd@banjarmasin.go.id</p>
                                    <hr>
                                    <p><strong>Nomor:</strong> 005/UND/DPRD/<?= date('Y') ?><br>
                                        <strong>Lampiran:</strong> -<br>
                                        <strong>Hal:</strong> Undangan Rapat
                                    </p>
                                    <br>
                                    <ul>
                                        <li><strong>Hari/Tanggal:</strong> <?= date('l, d F Y', strtotime($undangan['tanggal'])) ?></li>
                                        <li><strong>Waktu:</strong> <?= htmlspecialchars(isset($undangan['jam']) ? $undangan['jam'] : (isset($undangan['waktu']) ? $undangan['waktu'] : '')) ?> WITA</li>
                                        <li><strong>Tempat:</strong> <?= htmlspecialchars($undangan['lokasi']) ?></li>
                                    </ul>
                                    <li><strong>Hari/Tanggal:</strong> <?= date('l, d F Y', strtotime($undangan['tanggal'])) ?></li>
                                    <li><strong>Waktu:</strong> <?= htmlspecialchars($undangan['jam']) ?> WITA</li>
                                    <li><strong>Tempat:</strong> <?= htmlspecialchars($undangan['lokasi']) ?></li>
                                    </ul>
                                    <p>Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya diucapkan terima kasih.</p>
                                    <br>
                                    <p style="text-align:right;">Hormat kami,<br><br><strong>Ketua DPRD Kota Banjarmasin</strong></p>
                                </div>
                            </div>

                            <!-- Form Email -->
                            <div class="col-md-6">
                                <h6><strong>Kirim Email ke:</strong></h6>
                                <div class="mb-2">
                                    <?php foreach ($penerima_names as $index => $nama): ?>
                                        <span class="badge bg-primary me-1 mb-1">
                                            <?= htmlspecialchars($nama) ?> &lt;<?= htmlspecialchars($penerima_emails[$index]) ?>&gt;
                                        </span>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="judul_email" class="form-label">Judul Email</label>
                                    <input type="text" class="form-control" name="judul_email" value="<?= htmlspecialchars($subjek) ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="isi_email" class="form-label">Isi Email</label>
                                    <textarea name="isi_email" class="form-control"><?= htmlspecialchars($isi_email) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="undang_penerima.php?id=<?= $id_undangan ?>" class="btn btn-secondary">Kembali</a>
                            <button type="submit" name="konfirmasi" class="btn btn-success">
                                <i class="fe fe-send"></i> Kirim Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <?php require_once LAYOUT_PATH . '/scripts.php'; ?>
</body>

</html>