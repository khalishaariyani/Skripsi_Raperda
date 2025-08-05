<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/raperda/config/constants.php';
require_once CONFIG_PATH . '/koneksi.php';
require_once ROOT_PATH . '/session_start.php';
require_once ROOT_PATH . '/fpdf/fpdf.php';
ob_clean();

if (!isset($_GET['idPerda']) || !is_numeric($_GET['idPerda'])) {
    die("ID Perda tidak valid!");
}
$id_perda = intval($_GET['idPerda']);

$stmt = $conn->prepare("SELECT * FROM perda WHERE idPerda = ?");
$stmt->bind_param("i", $id_perda);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows == 0) {
    die("Data tidak ditemukan!");
}
$data = $result->fetch_assoc();

function verbalTanggal($date)
{
    $bulanMap = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $hariMap = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $angkaMap = ['0' => 'Nol', '1' => 'Satu', '2' => 'Dua', '3' => 'Tiga', '4' => 'Empat', '5' => 'Lima', '6' => 'Enam', '7' => 'Tujuh', '8' => 'Delapan', '9' => 'Sembilan'];

    $hari = $hariMap[date('l', strtotime($date))];
    $tgl = date('d', strtotime($date));
    $bulan = $bulanMap[(int)date('m', strtotime($date))];
    $tahun = date('Y', strtotime($date));

    $tglVerbal = implode(' ', array_map(fn($d) => $angkaMap[$d], str_split($tgl)));
    $thnVerbal = implode(' ', array_map(fn($d) => $angkaMap[$d], str_split($tahun)));

    return [
        'verbal' => "    Pada hari $hari, tanggal $tglVerbal, bulan $bulan, tahun $thnVerbal (" . date('d-m-Y', strtotime($date)) . "), kami yang bertanda tangan di bawah ini:"
    ];
}

$tanggal = verbalTanggal($data['tanggal_masuk']);

class PDF extends FPDF
{
    function Header()
    {
        global $data;
        $this->SetFont('Times', 'B', 11);
        $this->Cell(0, 6, 'BERITA ACARA PEMBICARAAN TINGKAT I', 0, 1, 'C');
        $this->Cell(0, 6, 'PEMBAHASAN RANCANGAN PERATURAN DAERAH KOTA BANJARMASIN', 0, 1, 'C');
        $this->Cell(0, 6, 'TENTANG ' . strtoupper($data['judul']), 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('Times', '', 10);
        $this->Cell(0, 5, 'NOMOR: ' . $data['nomor_perda'], 0, 1, 'C');
        $this->Ln(6);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Times', '', 11);

// === PEMBUKA ===
$pdf->MultiCell(0, 6, $tanggal['verbal']);
$pdf->Ln(3);

// === PIHAK PERTAMA ===
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(10, 6, '1.', 0, 0);
$pdf->SetFont('Times', '', 11);
$pdf->Cell(0, 6, strtoupper($data['pengusul']), 0, 1);
$pdf->Cell(20, 6, '', 0, 0);
$pdf->Cell(0, 6, 'Ketua DPRD Kota Banjarmasin untuk selanjutnya disebut PIHAK PERTAMA', 0, 1);
$pdf->Ln(2);

// === PIHAK KEDUA ===
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(10, 6, '2.', 0, 0);
$pdf->SetFont('Times', '', 11);
$pdf->Cell(0, 6, 'IKHSAN BUDIMAN, SH, MM', 0, 1);
$pdf->Cell(20, 6, '', 0, 0);
$pdf->Cell(0, 6, 'Sekretaris Daerah Banjarmasin untuk selanjutnya disebut PIHAK KEDUA', 0, 1);
$pdf->Ln(5);

// === ISI 1 ===
$pdf->MultiCell(0, 6, "1. PARA PIHAK telah selesai melakukan pembahasan Rancangan Peraturan Daerah Kota Banjarmasin tentang " . strtoupper($data['judul']) . " sesuai dengan ketentuan yang diatur dalam Peraturan Pemerintah Nomor 12 Tahun 2018 tentang Penyusunan Tata Tertib Dewan Perwakilan Rakyat Daerah Provinsi, Kabupaten, dan Kota dan ketentuan Peraturan Dewan Perwakilan Rakyat Daerah Kota Banjarmasin Nomor 1 Tahun 2020 tentang Tata Tertib Dewan Perwakilan Rakyat Daerah Kota Banjarmasin.");
$pdf->Ln(3);

// === ISI 2 ===
$pdf->MultiCell(0, 6, "2. Berita Acara Pembicaraan Tingkat I ini dibuat untuk memenuhi ketentuan persyaratan permohonan fasilitasi Rancangan Peraturan Daerah Kota Banjarmasin tentang " . strtoupper($data['judul']) . " kepada Gubernur sebagai Wakil Pemerintah Pusat di Daerah sebagaimana dimaksud dalam ketentuan Pasal 89 ayat (2) huruf b Peraturan Menteri Dalam Negeri Nomor 120 Tahun 2018 tentang Perubahan Atas Peraturan Menteri Dalam Negeri Nomor 80 Tahun 2015 tentang Pembentukan Produk Hukum Daerah.");
$pdf->Ln(3);

// === CATATAN ===
if (!empty($data['catatan'])) {
    $pdf->SetFont('Times', 'B', 11);
    $pdf->Cell(0, 6, 'Catatan:', 0, 1);
    $pdf->SetFont('Times', '', 11);
    $pdf->MultiCell(0, 6, $data['catatan']);
    $pdf->Ln(3);
}

// === PENUTUP ===
$pdf->MultiCell(0, 6, "    Demikian Berita Acara Pembicaraan Tingkat I Rancangan Peraturan Daerah Kota Banjarmasin tentang " . strtoupper($data['judul']) . " dibuat dan ditandatangani oleh PARA PIHAK dalam rangkap 3 (tiga) untuk dipergunakan sebagaimana mestinya.");
$pdf->Ln(10);

// === TANDA TANGAN ===
$pdf->Cell(0, 6, 'Banjarmasin, ' . date('j F Y', strtotime($data['tanggal_masuk'])), 0, 1, 'R');
$pdf->Ln(10);

// Baris jabatan
$pdf->Cell(90, 6, 'Sekretaris Daerah Kota', 0, 0, 'L');
$pdf->Cell(0, 6, 'DPRD Kota Banjarmasin', 0, 1, 'R');
// Baris "Ketua" & "Banjarmasin,"
$pdf->Cell(90, 6, 'Banjarmasin,', 0, 0, 'L');
$pdf->Cell(0, 6, 'Ketua,', 0, 1, 'R');
$pdf->Ln(15);

// Tanda tangan nama
$pdf->SetFont('Times', 'B', 11);
$pdf->Cell(90, 6, 'IKHSAN BUDIMAN, SH, MM', 0, 0, 'L');
$pdf->Cell(0, 6, strtoupper($data['pengusul']), 0, 1, 'R');

// NIP
$pdf->SetFont('Times', '', 11);
$pdf->Cell(90, 6, 'NIP: 19761205 200604 1 016', 0, 0, 'L');

// === OUTPUT ===
$pdf->Output('I', 'Berita_Acara_' . $data['nomor_perda'] . '.pdf');

$conn->close();
$stmt->close();
