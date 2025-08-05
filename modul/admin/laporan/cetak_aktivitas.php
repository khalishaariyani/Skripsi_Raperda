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

// Filter bulan
$filterClause = "";
if (!empty($bulan)) {
    $filterClause = " AND DATE_FORMAT(j.tanggal, '%Y-%m') = '$bulan'";
}

// Ambil semua rapat
$rapats = $conn->query("SELECT * FROM jadwalrapat j WHERE 1=1 $filterClause ORDER BY j.tanggal DESC");

$data = [];

while ($rapat = $rapats->fetch_assoc()) {
    $id_rapat = $rapat['id'];
    $judul = $rapat['judul_rapat'];
    $tanggal = $rapat['tanggal'];

    // Jadwal
    $data[] = [
        'judul' => $judul,
        'tanggal' => $tanggal,
        'jenis' => 'Jadwal',
        'keterangan' => 'Tempat: ' . $rapat['tempat']
    ];

    // Dokumen
    $dok = $conn->query("SELECT COUNT(*) as total FROM dok_rapat WHERE id_rapat = $id_rapat")->fetch_assoc();
    if ($dok['total'] > 0) {
        $data[] = [
            'judul' => $judul,
            'tanggal' => $tanggal,
            'jenis' => 'Dokumen Rapat',
            'keterangan' => $dok['total'] . ' file diunggah'
        ];
    }

    // Notulen
    $notulen = $conn->query("SELECT * FROM notulen WHERE id_rapat = $id_rapat")->fetch_assoc();
    if ($notulen) {
        $data[] = [
            'judul' => $judul,
            'tanggal' => $tanggal,
            'jenis' => 'Notulen',
            'keterangan' => 'Oleh: ' . $notulen['diinput_oleh']
        ];
    }

    // Dokumentasi
    $doks = $conn->query("SELECT COUNT(*) as total FROM dokumentasikegiatan WHERE idRapat = $id_rapat")->fetch_assoc();
    if ($doks['total'] > 0) {
        $data[] = [
            'judul' => $judul,
            'tanggal' => $tanggal,
            'jenis' => 'Dokumentasi',
            'keterangan' => $doks['total'] . ' file'
        ];
    }

    // Penyerahan Dokumen
    $pen = $conn->query("SELECT * FROM penyerahan_dokumen WHERE id = $id_rapat")->fetch_assoc();
    if ($pen) {
        $data[] = [
            'judul' => $judul,
            'tanggal' => $pen['tanggal_penyerahan'],
            'jenis' => 'Penyerahan',
            'keterangan' => 'Kepada: ' . $pen['penerima']
        ];
    }

    // Berita Acara
    $ba = $conn->query("SELECT * FROM perda WHERE idPerda = $id_rapat")->fetch_assoc();
    if ($ba) {
        $data[] = [
            'judul' => $judul,
            'tanggal' => $ba['tanggal_masuk'],
            'jenis' => 'Berita Acara',
            'keterangan' => 'Status: ' . $ba['status']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Aktivitas Pembentukan Perda</title>
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
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
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

    <div class="noprint" style="text-align:right; margin-bottom:10px;">
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

    <h3>Laporan Aktivitas Pembentukan Perda</h3>
    <?php if (!empty($bulan)): ?>
        <p class="sub">Periode: <strong><?= date('F Y', strtotime($bulan . '-01')) ?></strong></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Rapat</th>
                <th>Tanggal</th>
                <th>Jenis Kegiatan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($data) > 0): $no = 1; ?>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['jenis']) ?></td>
                        <td><?= $row['keterangan'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada aktivitas ditemukan.</td>
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
f