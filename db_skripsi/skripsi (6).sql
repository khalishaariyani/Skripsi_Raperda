-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2025 at 08:14 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skripsi`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggotadinas`
--

CREATE TABLE `anggotadinas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `isAktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anggotadinas`
--

INSERT INTO `anggotadinas` (`id`, `nama`, `jabatan`, `email`, `telepon`, `isAktif`) VALUES
(4, 'dinas 3', 'DINAS PENDIDIKAN', 'dinaskehutaanan3@gmail.com', '087746323444', 1),
(5, 'dinas 2', 'DINAS KEHUTANAN', 'dinaskehutaanan2@gmail.com', '087746323444', 1),
(8, 'dinas 4', 'DINAS PARIWISATA', 'dinaspariwisata1@gmail.com', '0987653356', 1);

-- --------------------------------------------------------

--
-- Table structure for table `arsiprapat`
--

CREATE TABLE `arsiprapat` (
  `id` int(11) NOT NULL,
  `id_rapat` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `diunggah_oleh` int(11) NOT NULL,
  `tanggal_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `arsiprapat`
--

INSERT INTO `arsiprapat` (`id`, `id_rapat`, `nama_file`, `file_path`, `diunggah_oleh`, `tanggal_upload`) VALUES
(15, 25, 'Berita_Acara_100.3.2_8_DPRD_IX_2025.pdf', '1754288674_Berita_Acara_100.3.2_8_DPRD_IX_2025.pdf', 1, '2025-08-04 14:24:34');

-- --------------------------------------------------------

--
-- Table structure for table `beritaacara`
--

CREATE TABLE `beritaacara` (
  `id` int(11) NOT NULL,
  `idRapat` int(11) DEFAULT NULL,
  `nomorBeritaAcara` varchar(100) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `pihakPertama` varchar(100) DEFAULT NULL,
  `pihakKedua` varchar(100) DEFAULT NULL,
  `isi` text DEFAULT NULL,
  `statusFinalisasi` enum('draft','final') DEFAULT NULL,
  `filePDF` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diskusiperda`
--

CREATE TABLE `diskusiperda` (
  `id` int(11) NOT NULL,
  `idRapat` int(11) NOT NULL,
  `idPengguna` int(11) NOT NULL,
  `isiKomentar` text NOT NULL,
  `tanggalKomentar` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokumentasikegiatan`
--

CREATE TABLE `dokumentasikegiatan` (
  `id` int(11) NOT NULL,
  `idRapat` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `diunggah_oleh` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokumentasikegiatan`
--

INSERT INTO `dokumentasikegiatan` (`id`, `idRapat`, `file`, `keterangan`, `diunggah_oleh`, `created_at`) VALUES
(16, 25, '1754279556_DOKUMENTASI_RAPAT_1.jpg', 'DOKUMENTASI KEGIATAN  RAPAT', 'admin', '2025-08-04 10:10:01'),
(18, 26, '1754279578_7726_DOKUMENTASI_RAPAT_2.jpg', 'dokumentasi terbaru', 'persidangan', '2025-08-04 11:52:58');

-- --------------------------------------------------------

--
-- Table structure for table `dokumen_usulan`
--

CREATE TABLE `dokumen_usulan` (
  `id_usulan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_upload` datetime DEFAULT current_timestamp(),
  `judul_rapat` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dok_rapat`
--

CREATE TABLE `dok_rapat` (
  `id` int(11) NOT NULL,
  `id_rapat` int(11) NOT NULL,
  `nama_dokumen` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `file_dok` varchar(255) NOT NULL,
  `tanggal_upload` date NOT NULL,
  `diunggah_oleh` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `informasi`
--

CREATE TABLE `informasi` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL DEFAULT curdate(),
  `author` varchar(100) NOT NULL,
  `gambar` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `informasi`
--

INSERT INTO `informasi` (`id`, `judul`, `isi`, `tanggal`, `author`, `gambar`) VALUES
(8, 'Strategi Penyusunan Perda Berbasis Partisipasi Publik di Tingkat Daerah', 'Partisipasi publik menjadi elemen penting dalam proses penyusunan rancangan peraturan daerah (Perda) agar kebijakan yang dihasilkan benar-benar mencerminkan kebutuhan dan aspirasi masyarakat. Dewan Perwakilan Rakyat Daerah (DPRD) sebagai lembaga legislatif di tingkat daerah memiliki peran kunci dalam memastikan partisipasi masyarakat terakomodasi dengan baik pada setiap tahapan pembentukan Perda, mulai dari perencanaan, penyusunan naskah akademik, pembahasan, hingga evaluasi implementasi.\r\n\r\nStrategi penyusunan Perda berbasis partisipasi publik dilakukan dengan membuka ruang konsultasi publik secara terbuka dan terjadwal. Forum diskusi, dengar pendapat, hingga penyebaran draft Raperda melalui kanal digital menjadi langkah-langkah konkret yang dapat dilakukan untuk menjaring masukan konstruktif dari berbagai elemen masyarakat, termasuk tokoh adat, akademisi, pelaku usaha, dan kelompok rentan. Dengan keterlibatan publik yang luas, DPRD dapat memetakan masalah secara lebih komprehensif dan merumuskan solusi yang relevan dengan kondisi di lapangan.\r\n\r\nSelain itu, optimalisasi peran media massa dan media sosial juga menjadi sarana efektif dalam menyosialisasikan Raperda yang sedang dibahas. Masyarakat diharapkan dapat mengakses informasi secara cepat, memberikan kritik, serta menyampaikan saran sebelum kebijakan disahkan. Transparansi informasi ini menjadi penanda bahwa DPRD menjalankan prinsip akuntabilitas dan keterbukaan dalam proses legislasi.\r\n\r\nDi sisi lain, peningkatan kapasitas anggota DPRD serta tim perumus Perda juga menjadi faktor penentu keberhasilan strategi ini. Pemahaman mengenai metodologi partisipasi publik, teknik komunikasi kebijakan, serta pemanfaatan teknologi informasi perlu terus diperkuat agar penyusunan Perda dapat lebih adaptif dengan tantangan zaman. Melalui pendekatan partisipatif ini, diharapkan setiap Perda yang disahkan tidak hanya memiliki legitimasi hukum, tetapi juga memiliki legitimasi sosial yang kuat di mata masyarakat.\r\n\r\nPada akhirnya, strategi penyusunan Perda berbasis partisipasi publik menjadi salah satu indikator keberhasilan otonomi daerah yang demokratis. Dengan sinergi antara DPRD, pemerintah daerah, serta seluruh unsur masyarakat, diharapkan peraturan daerah yang dihasilkan benar-benar membawa dampak positif, meningkatkan kualitas pelayanan publik, serta menjawab berbagai tantangan pembangunan daerah secara berkelanjutan', '2025-07-04', 'lala', '[\"6873ad390eb5e_images.jpg\"]'),
(15, 'Optimalisasi Peran DPRD dalam Meningkatkan Transparansi Pembentukan Peraturan Daerah', 'Dewan Perwakilan Rakyat Daerah (DPRD) memiliki tanggung jawab penting dalam mewujudkan tata kelola pemerintahan daerah yang transparan, akuntabel, dan partisipatif. Melalui fungsi legislasi, anggaran, dan pengawasan, DPRD diharapkan mampu memastikan setiap rancangan peraturan daerah (Raperda) disusun dengan mempertimbangkan aspirasi masyarakat, kepentingan publik, serta prinsip-prinsip good governance. Optimalisasi peran DPRD dalam meningkatkan transparansi pembentukan Perda dilakukan melalui pembahasan terbuka, penyusunan naskah akademik yang komprehensif, serta keterlibatan berbagai pemangku kepentingan sejak tahap perencanaan hingga pengesahan.\r\n\r\nSelain itu, DPRD juga mendorong pemanfaatan teknologi informasi sebagai sarana publikasi dokumen, jadwal rapat, hasil pembahasan, dan ringkasan kebijakan yang dapat diakses secara mudah oleh masyarakat. Hal ini penting agar masyarakat tidak hanya menjadi objek kebijakan, tetapi juga memiliki ruang untuk memberikan masukan secara konstruktif terhadap substansi Perda yang sedang dibahas. Dengan demikian, proses pembentukan Perda dapat terhindar dari praktik-praktik tertutup yang rawan konflik kepentingan dan penyalahgunaan wewenang.\r\n\r\nDi tingkat internal, DPRD berupaya memperkuat kapasitas SDM melalui pelatihan berkelanjutan, pendampingan ahli, dan kolaborasi dengan lembaga riset atau perguruan tinggi. Langkah ini diharapkan dapat meningkatkan kualitas analisis kebijakan, perumusan pasal-pasal Perda yang sesuai kebutuhan daerah, serta memperkecil potensi lahirnya peraturan yang tumpang tindih dengan peraturan di atasnya.', '2025-07-09', 'icha', '[\"6873ad0f1bee0_download_(2).jpg\"]');

-- --------------------------------------------------------

--
-- Table structure for table `jadwalrapat`
--

CREATE TABLE `jadwalrapat` (
  `id` int(11) NOT NULL,
  `judul_rapat` varchar(255) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `tempat` text DEFAULT NULL,
  `pengusul` varchar(255) DEFAULT NULL,
  `status` enum('usulan','disetujui','dibatalkan') NOT NULL,
  `dibuat_oleh` varchar(255) NOT NULL,
  `tanggal_input` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `agenda_rapat` varchar(255) NOT NULL,
  `peserta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwalrapat`
--

INSERT INTO `jadwalrapat` (`id`, `judul_rapat`, `tanggal`, `waktu`, `tempat`, `pengusul`, `status`, `dibuat_oleh`, `tanggal_input`, `agenda_rapat`, `peserta`) VALUES
(25, 'Rapat Paripurna Pembahasan RAPBD 2025', '2025-08-30', '10:25:00', 'RUANGAN PARIPURNA LT.2 , Gedung DPRD KOTA BANJARMASIN', 'H. Harry Wijaya, S.H., M.H.', 'disetujui', 'admin', '2025-07-31 14:05:27', 'penentuan  anggaran', 'dinas terkait beserta anggota dewan dprd yang terkait'),
(32, 'PENYELENGGARAAN DANA JALAN DEWAN (4)', '2025-09-17', '11:14:00', 'RUANGAN PARIPURNA LT.2 , Gedung DPRD KOTA BANJARMASIN', 'H. Harry Wijaya, S.H., M.H.', 'dibatalkan', 'admin', '2025-08-04 00:12:48', 'pembancaan syarat untuk perda', 'dinas terkait dan seluruh anggotadewan terkait'),
(33, 'RAPAT JALAN (1)', '2025-08-05', '09:17:00', 'RUANGAN PARIPURNA LT.2 , Gedung DPRD KOTA BANJARMASIN', 'KHALISHA ARIYANI', 'dibatalkan', 'admin', '2025-08-04 00:11:37', 'penentuan  anggaran', 'dinas terkait beserta anggota dewan dprd yang terkait'),
(35, 'Rapat Internal Penyusunan Agenda Sidang Periode II Tahun 2025', '2025-08-10', '10:00:00', 'RUANGAN PARIPURNA LT.2 , Gedung DPRD KOTA BANJARMASIN', 'H. Ahmad Syuki, S.E', 'disetujui', 'admin', '2025-08-04 00:12:52', 'Penyusunan jadwal dan target legislasi', 'Sekretariat DPRD dan Pimpinan Komisi H'),
(38, 'Rapat Dengar Pendapat Umum Perda Pengelolaan Sampah', '2025-09-16', '09:45:00', 'Gedung DPRD Kota Banjarmasin', 'khalisha ariyani', 'usulan', 'persidangan', '2025-08-04 03:48:35', 'pembacaan perda dengan syarat dan ketentuannya', 'dinas terkait dan anggota rapat'),
(39, 'RAPAT PEMBAHASAN PERDA DPRD KOTA BANJARMASIN 2025', '2025-09-12', '10:13:00', 'RUANGAN PARIPURNA LT.2 , Gedung DPRD KOTA BANJARMASIN', 'HELMANI', 'disetujui', 'admin', '2025-08-04 12:12:25', 'penentuan  anggaran', 'dinas terkait beserta anggota dewan dprd yang terkait');

-- --------------------------------------------------------

--
-- Table structure for table `kehadiranrapat`
--

CREATE TABLE `kehadiranrapat` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_rapat` int(11) DEFAULT NULL,
  `status` enum('hadir','izin','sakit') DEFAULT NULL,
  `waktu_hadir` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporanrevisi`
--

CREATE TABLE `laporanrevisi` (
  `id` int(11) NOT NULL,
  `idRapat` int(11) DEFAULT NULL,
  `pengusul` varchar(100) DEFAULT NULL,
  `jenis_revisi` varchar(50) DEFAULT NULL,
  `isi_revisi` text DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laporanrevisi`
--

INSERT INTO `laporanrevisi` (`id`, `idRapat`, `pengusul`, `jenis_revisi`, `isi_revisi`, `tanggal_masuk`, `status`) VALUES
(1, 9, 'khalisha ariyani S.Kom', 'pasal', 'hehehapasal di prbakii', '2025-06-21', 'Perlu Revisi'),
(2, 5, 'MUHAMMAD SYAUQI PANJALU', 'pembahasan', 'haiiiii ini isi revisi yah', '2025-04-15', 'Perlu Revisi');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasirapat`
--

CREATE TABLE `notifikasirapat` (
  `id` int(11) NOT NULL,
  `idUndangan` int(11) DEFAULT NULL,
  `idPenerima` int(11) DEFAULT NULL,
  `idAnggotaDinas` int(11) DEFAULT NULL,
  `idKaryawan` int(11) DEFAULT NULL,
  `isi` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `jenisNotifikasi` varchar(20) DEFAULT NULL,
  `waktuKirim` datetime DEFAULT NULL,
  `dikirim_oleh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notulen`
--

CREATE TABLE `notulen` (
  `id` int(11) NOT NULL,
  `id_rapat` int(11) NOT NULL,
  `ringkasan` text NOT NULL,
  `diinput_oleh` int(11) NOT NULL,
  `tanggal_input` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penyerahan_dokumen`
--

CREATE TABLE `penyerahan_dokumen` (
  `id` int(11) NOT NULL,
  `id_arsip` int(11) NOT NULL,
  `nama_penerima` varchar(255) NOT NULL,
  `file_dokumen` varchar(255) DEFAULT NULL,
  `tanggal_penyerahan` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penyerahan_dokumen`
--

INSERT INTO `penyerahan_dokumen` (`id`, `id_arsip`, `nama_penerima`, `file_dokumen`, `tanggal_penyerahan`, `created_at`) VALUES
(5, 5, 'pemko banjarmasin', '1751958936_Berita_Acara.pdf', '2025-07-08', '2025-07-08 07:15:36'),
(6, 7, 'pemko banjarmasin', '1752221168_Berita_Acara.pdf', '2025-07-11', '2025-07-11 08:06:08'),
(7, 9, 'BIRO HUKUM', '1752657283_Berita_Acara.pdf', '2025-07-16', '2025-07-16 09:14:43'),
(8, 11, 'pemko banjarmasin', '1754156509_Berita_Acara.pdf', '2025-08-03', '2025-08-02 17:41:49');

-- --------------------------------------------------------

--
-- Table structure for table `perda`
--

CREATE TABLE `perda` (
  `idPerda` int(11) NOT NULL,
  `nomor_perda` varchar(100) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `status` varchar(50) NOT NULL,
  `pengusul` varchar(100) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perda`
--

INSERT INTO `perda` (`idPerda`, `nomor_perda`, `tanggal_masuk`, `status`, `pengusul`, `judul`, `catatan`) VALUES
(16, '100.3.2/7/DPRD/IX/2024', '2025-09-12', 'FINALISASI', 'HELMANI', 'RAPAT PEMBAHASAN PERDA DPRD KOTA BANJARMASIN 2025', 'finalisasi perda');

-- --------------------------------------------------------

--
-- Table structure for table `profil`
--

CREATE TABLE `profil` (
  `id` int(11) NOT NULL,
  `idUser` int(11) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `noHP` varchar(20) DEFAULT NULL,
  `fotoProfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rapatseringdibahas`
--

CREATE TABLE `rapatseringdibahas` (
  `id` int(11) NOT NULL,
  `idRapat` int(11) DEFAULT NULL,
  `topik` varchar(255) DEFAULT NULL,
  `jumlahDibahas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rekapankehadiran`
--

CREATE TABLE `rekapankehadiran` (
  `id` int(11) NOT NULL,
  `idRapat` int(11) DEFAULT NULL,
  `jumlahHadir` int(11) DEFAULT NULL,
  `jumlahTidakHadir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `undanganrapat`
--

CREATE TABLE `undanganrapat` (
  `id` int(11) NOT NULL,
  `idRapat` int(11) DEFAULT NULL,
  `penerima` varchar(100) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam` time NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `catatan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','anggota','persidangan') NOT NULL,
  `foto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `nama`, `email`, `password`, `role`, `foto`) VALUES
(1, 'admin', 'admin123@gmail.com', '123', 'admin', 'user_1_1754309237.png'),
(23, 'anggota1', 'rezqyridha@gmail.com', '123', 'anggota', 'user_23_1752911617.png'),
(26, 'persidangan', 'persidangan222@gmail.com', '321', 'persidangan', 'user_26_1751873608.png'),
(29, 'anggota2', 'heloword@gmail.com', '123', 'anggota', 'user_29_1752651271.png'),
(38, 'persidangan 2', 'persidangan2@gmail.com', '123', 'persidangan', ''),
(39, 'dinas 2', 'dinaskehutaanan2@gmail.com', '123', 'anggota', 'user_39_1754266205.png'),
(40, 'admin1', 'admin2@gmail.com', '123', 'admin', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggotadinas`
--
ALTER TABLE `anggotadinas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `arsiprapat`
--
ALTER TABLE `arsiprapat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rapat` (`id_rapat`),
  ADD KEY `diunggah_oleh` (`diunggah_oleh`);

--
-- Indexes for table `beritaacara`
--
ALTER TABLE `beritaacara`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idRapat` (`idRapat`);

--
-- Indexes for table `diskusiperda`
--
ALTER TABLE `diskusiperda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idRapat` (`idRapat`),
  ADD KEY `idPengguna` (`idPengguna`);

--
-- Indexes for table `dokumentasikegiatan`
--
ALTER TABLE `dokumentasikegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dokumen_usulan`
--
ALTER TABLE `dokumen_usulan`
  ADD PRIMARY KEY (`id_usulan`);

--
-- Indexes for table `dok_rapat`
--
ALTER TABLE `dok_rapat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `informasi`
--
ALTER TABLE `informasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jadwalrapat`
--
ALTER TABLE `jadwalrapat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kehadiranrapat`
--
ALTER TABLE `kehadiranrapat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `fk_rapat` (`id_rapat`);

--
-- Indexes for table `laporanrevisi`
--
ALTER TABLE `laporanrevisi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idRapat` (`idRapat`);

--
-- Indexes for table `notifikasirapat`
--
ALTER TABLE `notifikasirapat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idUndangan` (`idUndangan`);

--
-- Indexes for table `notulen`
--
ALTER TABLE `notulen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rapat` (`id_rapat`),
  ADD KEY `diinput_oleh` (`diinput_oleh`);

--
-- Indexes for table `penyerahan_dokumen`
--
ALTER TABLE `penyerahan_dokumen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perda`
--
ALTER TABLE `perda`
  ADD PRIMARY KEY (`idPerda`);

--
-- Indexes for table `profil`
--
ALTER TABLE `profil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idUser` (`idUser`);

--
-- Indexes for table `rapatseringdibahas`
--
ALTER TABLE `rapatseringdibahas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idRapat` (`idRapat`);

--
-- Indexes for table `rekapankehadiran`
--
ALTER TABLE `rekapankehadiran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idRapat` (`idRapat`);

--
-- Indexes for table `undanganrapat`
--
ALTER TABLE `undanganrapat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idRapat` (`idRapat`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggotadinas`
--
ALTER TABLE `anggotadinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `arsiprapat`
--
ALTER TABLE `arsiprapat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `beritaacara`
--
ALTER TABLE `beritaacara`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diskusiperda`
--
ALTER TABLE `diskusiperda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `dokumentasikegiatan`
--
ALTER TABLE `dokumentasikegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `dokumen_usulan`
--
ALTER TABLE `dokumen_usulan`
  MODIFY `id_usulan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `dok_rapat`
--
ALTER TABLE `dok_rapat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `informasi`
--
ALTER TABLE `informasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `jadwalrapat`
--
ALTER TABLE `jadwalrapat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `kehadiranrapat`
--
ALTER TABLE `kehadiranrapat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `laporanrevisi`
--
ALTER TABLE `laporanrevisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifikasirapat`
--
ALTER TABLE `notifikasirapat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notulen`
--
ALTER TABLE `notulen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `penyerahan_dokumen`
--
ALTER TABLE `penyerahan_dokumen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `perda`
--
ALTER TABLE `perda`
  MODIFY `idPerda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `profil`
--
ALTER TABLE `profil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rapatseringdibahas`
--
ALTER TABLE `rapatseringdibahas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rekapankehadiran`
--
ALTER TABLE `rekapankehadiran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `undanganrapat`
--
ALTER TABLE `undanganrapat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `arsiprapat`
--
ALTER TABLE `arsiprapat`
  ADD CONSTRAINT `arsiprapat_ibfk_1` FOREIGN KEY (`id_rapat`) REFERENCES `jadwalrapat` (`id`),
  ADD CONSTRAINT `arsiprapat_ibfk_2` FOREIGN KEY (`diunggah_oleh`) REFERENCES `user` (`id`);

--
-- Constraints for table `diskusiperda`
--
ALTER TABLE `diskusiperda`
  ADD CONSTRAINT `diskusiperda_ibfk_1` FOREIGN KEY (`idRapat`) REFERENCES `jadwalrapat` (`id`),
  ADD CONSTRAINT `diskusiperda_ibfk_2` FOREIGN KEY (`idPengguna`) REFERENCES `user` (`id`);

--
-- Constraints for table `kehadiranrapat`
--
ALTER TABLE `kehadiranrapat`
  ADD CONSTRAINT `fk_rapat` FOREIGN KEY (`id_rapat`) REFERENCES `jadwalrapat` (`id`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`);

--
-- Constraints for table `notifikasirapat`
--
ALTER TABLE `notifikasirapat`
  ADD CONSTRAINT `notifikasirapat_ibfk_1` FOREIGN KEY (`idUndangan`) REFERENCES `undanganrapat` (`id`);

--
-- Constraints for table `notulen`
--
ALTER TABLE `notulen`
  ADD CONSTRAINT `notulen_ibfk_1` FOREIGN KEY (`id_rapat`) REFERENCES `jadwalrapat` (`id`),
  ADD CONSTRAINT `notulen_ibfk_2` FOREIGN KEY (`diinput_oleh`) REFERENCES `user` (`id`);

--
-- Constraints for table `profil`
--
ALTER TABLE `profil`
  ADD CONSTRAINT `profil_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
