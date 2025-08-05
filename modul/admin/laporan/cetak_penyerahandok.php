<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';

if ($_SESSION['role'] !== 'admin') {
    echo "<script>alert('Unauthorized');window.close();</script>";
    exit;
}

ob_clean();

$bulan = $_GET['bulan'] ?? '';
$tanggal_cetak = date('d F Y');

$query = "
    SELECT *
    FROM penyerahan_dokumen
    WHERE 1=1
";

if (!empty($bulan)) {
    $query .= " AND DATE_FORMAT(tanggal_penyerahan, '%Y-%m') = '$bulan'";
}

$query .= " ORDER BY tanggal_penyerahan DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Penyerahan Dokumen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        .kop-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .kop-container img {
            width: 75px;
            height: auto;
        }

        .kop-text {
            text-align: center;
        }

        .kop-text h2 {
            font-size: 18px;
            margin: 0;
        }

        .kop-text p {
            font-size: 13px;
            margin: 0;
        }

        .line {
            border-bottom: 2px solid #000;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        h3 {
            text-align: center;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .sub {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 7px 10px;
            font-size: 13px;
        }

        th {
            background-color: #f0f0f0;
        }

        .noprint {
            text-align: right;
            margin-bottom: 10px;
        }

        .ttd-container {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .ttd-box {
            width: 45%;
        }

        .ttd-box p {
            margin: 0;
        }

        .ttd-name {
            margin-top: 80px;
            font-weight: bold;
            text-decoration: underline;
        }

        @media print {
            .noprint {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="noprint">
        <button onclick="window.print()">🖨️ Cetak</button>
        <button onclick="window.close()">❌ Tutup</button>
    </div>

    <!-- KOP SURAT -->
    <div class="kop">
        <div class="kop-container">
            <img src="<?= BASE_URL ?>/assets/images/dprdbulat.png" alt="Logo DPRD">
            <div class="kop-text">
                <h2>DEWAN PERWAKILAN RAKYAT DAERAH<br>KOTA BANJARMASIN</h2>
                <p>Jalan Lambung Mangkurat, Kelurahan Kertak Baru Ilir, Kecamatan Banjarmasin Tengah,<br>
                    Kota Banjarmasin, Kalimantan Selatan - 70114</p>
            </div>
        </div>
        <div class="line"></div>
    </div>

    <h3>Laporan Penyerahan Dokumen</h3>
    <?php if (!empty($bulan)): ?>
        <p class="sub">Periode: <strong><?= date('F Y', strtotime($bulan . '-01')) ?></strong></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Penerima</th>
                <th>File Dokumen</th>
                <th>Tanggal Penyerahan</th>
                <th>Waktu Upload</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): $no = 1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_penerima']) ?></td>
                        <td><?= htmlspecialchars($row['file_dokumen']) ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal_penyerahan'])) ?></td>
                        <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada data penyerahan ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="ttd-container" style="display: flex; justify-content: space-between; margin-top: 60px; font-size: 14px;">
        <!-- Sekretaris Dewan -->
        <div class="ttd-box text-center" style="width: 40%;">
            <p style="margin-bottom: 97px;">Sekretaris Dewan</p> <!-- Sedikit naik -->
            <div class="ttd-name">Iwan Ristianto, A.P., M.AP</div>
            <p>NIP: 19761205 200604 1 016</p>
        </div>

        <!-- Ketua DPRD -->
        <div class="ttd-box text-center" style="width: 40%;">
            <p>Banjarmasin, <?= $tanggal_cetak ?></p>
            <p style="margin-bottom: 60px;">Ketua DPRD Kota Banjarmasin</p>
            <div class="ttd-name">H. Harry Wijaya, S.H., M.H.</div>
            <p>NIP: 19780510 200903 1 002</p>
        </div>
    </div>




</body>

</html>