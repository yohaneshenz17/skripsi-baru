-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 22, 2025 at 08:53 AM
-- Server version: 10.3.39-MariaDB-cll-lve
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stkp7133_skripsi`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `bimbingan_dosen_v`
-- (See below for the actual view)
--
CREATE TABLE `bimbingan_dosen_v` (
`nip` varchar(30)
,`nama` varchar(100)
,`nomor_telepon` varchar(30)
,`email` varchar(100)
,`level` enum('1','2','4')
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`nama_prodi` varchar(50)
,`mahasiswa_id` bigint(20)
,`id` bigint(20)
);

-- --------------------------------------------------------

--
-- Table structure for table `dokumen_hasil`
--

CREATE TABLE `dokumen_hasil` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) NOT NULL,
  `kegiatan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` bigint(20) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `prodi_id` bigint(20) NOT NULL DEFAULT 1,
  `nama` varchar(100) NOT NULL,
  `nomor_telepon` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `level` enum('1','2','4') NOT NULL DEFAULT '2' COMMENT '1 = admin, 2 = dosen, 4 = kaprodi',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `nip`, `prodi_id`, `nama`, `nomor_telepon`, `email`, `level`, `foto`) VALUES
(2, '20201015', 1, 'Super Admin', '081295111706', 'admin@admin.com', '1', ''),
(10, '2721128601', 10, 'Dedimus Berangka, S.Pd., M.Pd. (Kaprodi PKK)', '081290909003', 'dedimus@stkyakobus.ac.id', '4', '47b8a620332e72136adead99e0b0120e.jpg'),
(11, '2706058401', 11, 'Steven Ronald Ahlaro, S.Pd., M.Pd. (Kaprodi PGSD)', '082271403437', 'pgsd@stkyakobus.ac.id', '4', ''),
(12, '2720067001', 10, 'Dr. Berlinda Setyo Yunarti, M.Pd.', '085244791002', 'lindayunarti@stkyakobus.ac.id', '2', ''),
(14, '2709109301', 11, 'Lambertus Ayiriga, S.Pd., M.Pd.', '82197819425', 'lambertus@stkyakobus.ac.id', '2', ''),
(15, '2728048001', 11, 'Rikardus Kristian Sarang, S.Fil., M.Pd.', '81248525845', 'rikardkristians@stkyakobus.ac.id', '2', ''),
(16, '2730068501', 10, 'Raimundus Sedo, S.T., M.T.', '81338623494', 'raimundus@stkyakobus.ac.id', '2', ''),
(17, '2705077801', 11, 'Dr. Erly Lumban Gaol, M.Th.', '81239904548', 'erly@stkyakobus.ac.id', '2', ''),
(18, '2727128101', 10, 'Yan Yusuf Subu, S.Fil., M.Hum.', '81227909867', 'yanyusuf@stkyakobus.ac.id', '2', ''),
(19, '2729108301', 11, 'Rosmayasinta Makasau, S.Pd., M.Pd.', '85244236555', 'mayamakasau@stkyakobus.ac.id', '2', ''),
(20, '2717077001', 10, 'Dr. Donatus Wea, Lic.Iur.', '81247719057', 'romodonwea@stkyakobus.ac.id', '2', '9759cfa94b69c11c6e36d5d41b5f777f.jpg'),
(21, '2719076301', 10, 'Drs. Xaverius Wonmut, M.Hum.', '81248202058', 'xaveriuswonmut@stkyakobus.ac.id', '2', ''),
(22, '2729086901', 11, 'Agustinus Kia Wolomasi, S.Ag., M.Pd.', '081386503387', 'aguswolomasi@stkyakobus.ac.id', '2', ''),
(23, '2709077801', 10, 'Markus Meran, S.Ag., M.Th.', '82248526104', 'markusmeran@stkyakobus.ac.id', '2', ''),
(24, '1423056901', 10, 'Francisco Noerjanto, S.Ag., M.Si.', '8114890505', 'francisco@stkyakobus.ac.id', '2', ''),
(25, '2717069001', 10, 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', '081295111706', 'yohaneshenz@stkyakobus.ac.id', '2', '78af977767e79451ad0b98e6c5280799.jpg'),
(26, '2721128601', 10, 'Dedimus Berangka, S.Pd., M.Pd.', '081290909003', 'dedydbeau@gmail.com', '2', ''),
(27, '2706058401', 11, 'Steven Ronald Ahlaro, S.Pd., M.Pd.', '082271403437', 'steveahlaro@stkyakobus.ac.id', '2', ''),
(28, '2717069001', 10, 'Yohanes Hendro Pranyoto (Admin)', '081295111706', 'sipd@stkyakobus.ac.id', '1', '');

-- --------------------------------------------------------

--
-- Table structure for table `dosen_backup_20250717`
--

CREATE TABLE `dosen_backup_20250717` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `nip` varchar(30) NOT NULL,
  `prodi_id` bigint(20) NOT NULL DEFAULT 1,
  `nama` varchar(100) NOT NULL,
  `nomor_telepon` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `level` enum('1','2','4') NOT NULL DEFAULT '2' COMMENT '1 = admin, 2 = dosen, 4 = kaprodi',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dosen_backup_20250717`
--

INSERT INTO `dosen_backup_20250717` (`id`, `nip`, `prodi_id`, `nama`, `nomor_telepon`, `email`, `level`, `foto`) VALUES
(2, '20201015', 1, 'Super Admin', '081295111706', 'admin@admin.com', '1', ''),
(10, '2721128601', 1, 'Dedimus Berangka, S.Pd., M.Pd. (Kaprodi PKK)', '081290909003', 'dedimus@stkyakobus.ac.id', '4', '9cc27fe949d6ee43f944b6453035f9d9.jpeg'),
(11, '2706058401', 1, 'Steven Ronald Ahlaro, S.Pd., M.Pd. (Kaprodi PGSD)', '082271403437', 'steveahlaro@stkyakobus.ac.id', '4', ''),
(12, '2720067001', 1, 'Dr. Berlinda Setyo Yunarti, M.Pd.', '085244791002', 'lindayunarti@stkyakobus.ac.id', '4', ''),
(14, '2709109301', 2, 'Lambertus Ayiriga, S.Pd., M.Pd.', '82197819425', 'lambertus@stkyakobus.ac.id', '2', ''),
(15, '2728048001', 1, 'Rikardus Kristian Sarang, S.Fil., M.Pd.', '81248525845', 'rikardkristians@stkyakobus.ac.id', '2', ''),
(16, '2730068501', 1, 'Raimundus Sedo, S.T., M.T.', '81338623494', 'raimundus@stkyakobus.ac.id', '2', ''),
(17, '2705077801', 2, 'Dr. Erly Lumban Gaol, M.Th.', '81239904548', 'erly@stkyakobus.ac.id', '2', ''),
(18, '2727128101', 1, 'Yan Yusuf Subu, S.Fil., M.Hum.', '81227909867', 'yanyusuf@stkyakobus.ac.id', '2', ''),
(19, '2729108301', 1, 'Rosmayasinta Makasau, S.Pd., M.Pd.', '85244236555', 'mayamakasau@stkyakobus.ac.id', '2', ''),
(20, '2717077001', 1, 'Dr. Donatus Wea, Lic.Iur.', '81247719057', 'romodonwea@stkyakobus.ac.id', '2', ''),
(21, '2719076301', 1, 'Drs. Xaverius Wonmut, M.Hum.', '81248202058', 'xaveriuswonmut@stkyakobus.ac.id', '2', ''),
(22, '2729086901', 2, 'Agustinus Kia Wolomasi, S.Ag., M.Pd.', '81386503387', 'aguswolomasi@stkyakobus.ac.id', '2', ''),
(23, '2709077801', 1, 'Markus Meran, S.Ag., M.Th.', '82248526104', 'markusmeran@stkyakobus.ac.id', '2', ''),
(24, '1423056901', 1, 'Francisco Noerjanto, S.Ag., M.Si.', '8114890505', 'francisco@stkyakobus.ac.id', '2', ''),
(25, '2717069001', 1, 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', '81295111706', 'yohaneshenz@stkyakobus.ac.id', '1', '');

-- --------------------------------------------------------

--
-- Table structure for table `email_sender`
--

CREATE TABLE `email_sender` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `smtp_port` varchar(50) DEFAULT NULL,
  `smtp_host` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_sender`
--

INSERT INTO `email_sender` (`id`, `email`, `password`, `smtp_port`, `smtp_host`) VALUES
(1, 'stkyakobus@gmail.com', 'yonroxhraathnaug', '587', 'smtp.gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `email_sender_backup_20250720_124531`
--

CREATE TABLE `email_sender_backup_20250720_124531` (
  `id` int(11) NOT NULL DEFAULT 0,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_host` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `email_sender_backup_20250720_124531`
--

INSERT INTO `email_sender_backup_20250720_124531` (`id`, `email`, `password`, `smtp_port`, `smtp_host`) VALUES
(1, 'stkyakobus@gmail.com', 'yonroxhraathnaug', '465', 'ssl://smtp.gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `fakultas`
--

CREATE TABLE `fakultas` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `dekan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fakultas`
--

INSERT INTO `fakultas` (`id`, `nama`, `dekan`) VALUES
(1, 'Fakultas Keguruan dan Ilmu Pendidikan', 'Rikardus Kristian Sarang');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_kegiatan`
--

CREATE TABLE `hasil_kegiatan` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) NOT NULL,
  `file` varchar(50) NOT NULL,
  `kegiatan` varchar(5000) DEFAULT NULL,
  `file_kegiatan` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `hasil_kegiatan_v`
-- (See below for the actual view)
--
CREATE TABLE `hasil_kegiatan_v` (
`mahasiswa_id` bigint(20)
,`id` bigint(20)
,`file` varchar(50)
,`kegiatan` varchar(5000)
,`file_kegiatan` varchar(50)
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`nama_prodi` varchar(50)
,`status` varchar(50)
);

-- --------------------------------------------------------

--
-- Table structure for table `hasil_penelitian`
--

CREATE TABLE `hasil_penelitian` (
  `id` bigint(20) NOT NULL,
  `penelitian_id` bigint(20) NOT NULL,
  `berita_acara` varchar(50) NOT NULL,
  `masukan` varchar(50) NOT NULL,
  `status` enum('1','2') NOT NULL COMMENT '1 = lulus, 2 = tidak lulus'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_seminar`
--

CREATE TABLE `hasil_seminar` (
  `id` bigint(20) NOT NULL,
  `seminar_id` bigint(20) NOT NULL,
  `berita_acara` text NOT NULL,
  `masukan` text NOT NULL COMMENT 'komentar pdf (pembimbing, penguji, catatan)',
  `status` enum('1','2','3') NOT NULL COMMENT '1 = lanjut, 2 = lanjut (perbaikan), 3 = ditolak'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `home_template`
--

CREATE TABLE `home_template` (
  `id` int(11) NOT NULL,
  `carousel_bg1` varchar(100) DEFAULT NULL,
  `carousel_subtitle1` varchar(100) DEFAULT NULL,
  `carousel_title1` varchar(100) DEFAULT NULL,
  `carousel_description1` varchar(500) DEFAULT NULL,
  `carousel_btn_href1` varchar(100) DEFAULT NULL,
  `carousel_btn_text1` varchar(20) DEFAULT NULL,
  `carousel_bg2` varchar(100) DEFAULT NULL,
  `carousel_subtitle2` varchar(100) DEFAULT '',
  `carousel_title2` varchar(100) DEFAULT '',
  `carousel_description2` varchar(500) DEFAULT '',
  `carousel_btn_href2` varchar(100) DEFAULT '',
  `carousel_btn_text2` varchar(20) DEFAULT '',
  `carousel_bg3` varchar(100) DEFAULT '',
  `carousel_subtitle3` varchar(100) DEFAULT '',
  `carousel_title3` varchar(100) DEFAULT '',
  `carousel_description3` varchar(500) DEFAULT '',
  `carousel_btn_href3` varchar(100) DEFAULT '',
  `carousel_btn_text3` varchar(20) DEFAULT '',
  `tentang_kami_subtitle` varchar(100) DEFAULT NULL,
  `tentang_kami_isi` varchar(5000) DEFAULT '',
  `social_description` varchar(500) DEFAULT NULL,
  `link_fb` varchar(100) DEFAULT NULL,
  `link_twitter` varchar(100) DEFAULT NULL,
  `alamat` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `kontak_subtitle` varchar(100) DEFAULT NULL,
  `page_title` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_template`
--

INSERT INTO `home_template` (`id`, `carousel_bg1`, `carousel_subtitle1`, `carousel_title1`, `carousel_description1`, `carousel_btn_href1`, `carousel_btn_text1`, `carousel_bg2`, `carousel_subtitle2`, `carousel_title2`, `carousel_description2`, `carousel_btn_href2`, `carousel_btn_text2`, `carousel_bg3`, `carousel_subtitle3`, `carousel_title3`, `carousel_description3`, `carousel_btn_href3`, `carousel_btn_text3`, `tentang_kami_subtitle`, `tentang_kami_isi`, `social_description`, `link_fb`, `link_twitter`, `alamat`, `phone`, `email`, `kontak_subtitle`, `page_title`) VALUES
(1, 'Salinan_dari_Sekolah_Tinggi_Katolik_Santo_Yakobus_Merauke2.jpg', 'Aplikasi SIM', 'Manajemen Tugas Akhir STK St. Yakobus Merauke', 'Aplikasi ini digunakan untuk mengelola Tugas Akhir mahasiswa Sekolah Tinggi Katolik Santo Yakobus Merauke.', 'https://stkyakobus.ac.id/skripsi/auth/login', 'Mulai', 'Salinan_dari_Sekolah_Tinggi_Katolik_Santo_Yakobus_Merauke4.jpg', 'Alur Proses', 'Registrasi, Seminar Proposal, Ujian Skripsi', 'Setiap mahasiswa wajib mengikuti alur proses Tugas Akhir mencakup: registrasi judul, bimbingan proposal, seminar proposal, bimbingan skripsi dan seminar akhir atau ujian skripsi.', 'https://stkyakobus.ac.id/skripsi/auth/login', 'Mulai', 'Salinan_dari_Sekolah_Tinggi_Katolik_Santo_Yakobus_Merauke5.jpg', 'Mekanisme', 'Metode Penyelesaian Tugas Akhir', 'Semua proses mekanisme penyelesaian Tugas Akhir mahasiswa dilaksanakan secara hybrid (daring dan luring) dan seluruh proses didokumentasikan secara daring melalui aplikasi ini.', 'https://stkyakobus.ac.id/skripsi/auth/login', 'Mulai', 'Aplikasi Sistem Informasi Tugas Akhir Mahasiswa Sekolah Tinggi Katolik Santo Yakobus Merauke', 'Aplikasi SIM Tugas Akhir ini digunakan untuk: memonitor tugas akhir mahasiswa Sekolah Tinggi Katolik Santo Yakobus Merauke. Monitoring tugas akhir mahasiswa jenjang sarjana dalam bentuk skripsi mulai dari: pendaftaran judul, bimbingan proposal dan skripsi, seminar proposal, seminar hasil dan ujian skripsi. Monitoring dalam hal ini diperuntukan untuk pengelola program studi dan dosen agar dapat mengawasi mahasiswa bimbingannya dan mengetahui perkembangan mahasiswa bimbingannya. Aplikasi ini dikembangkan oleh Unit Sistem Informasi dan Pangkalan Data Sekolah Tinggi Katolik Santo Yakobus Merauke.', 'Informasi lain, silahkan kunjungi website: https://www.stkyakobus.ac.id atau media sosial official kami berikut:', 'https://www.facebook.com/stkyakobus', 'https://x.com/stkyakobus', 'Jl. Missi 2, Mandala, Merauke, Papua Selatan', '09713330264', 'sipd@stkyakobus.ac.id', 'Unit Sistem Informasi dan Pangkalan Data STK St. Yakobus Merauke', 'Sistem Informasi Manajemen Tugas Akhir');

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_bimbingan`
--

CREATE TABLE `jurnal_bimbingan` (
  `id` int(11) NOT NULL,
  `proposal_id` bigint(20) NOT NULL,
  `pertemuan_ke` int(11) NOT NULL COMMENT 'Urutan pertemuan (1, 2, 3, dst)',
  `tanggal_bimbingan` date NOT NULL COMMENT 'Tanggal pelaksanaan bimbingan',
  `materi_bimbingan` text NOT NULL COMMENT 'Materi yang dibahas dalam bimbingan',
  `catatan_dosen` text DEFAULT NULL COMMENT 'Catatan dari dosen pembimbing (setelah validasi)',
  `tindak_lanjut` text DEFAULT NULL COMMENT 'Tindak lanjut untuk mahasiswa',
  `durasi_bimbingan` int(3) DEFAULT NULL COMMENT 'Durasi bimbingan dalam menit',
  `catatan_mahasiswa` text DEFAULT NULL COMMENT 'Catatan atau pertanyaan dari mahasiswa',
  `status_validasi` enum('0','1','2') DEFAULT '0' COMMENT '0=pending, 1=valid, 2=revisi',
  `tanggal_validasi` datetime DEFAULT NULL COMMENT 'Tanggal dosen memvalidasi',
  `validasi_oleh` bigint(20) DEFAULT NULL,
  `created_by` enum('mahasiswa','dosen') DEFAULT 'mahasiswa' COMMENT 'Dibuat oleh mahasiswa atau dosen',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Jurnal bimbingan mahasiswa';

--
-- Dumping data for table `jurnal_bimbingan`
--

INSERT INTO `jurnal_bimbingan` (`id`, `proposal_id`, `pertemuan_ke`, `tanggal_bimbingan`, `materi_bimbingan`, `catatan_dosen`, `tindak_lanjut`, `durasi_bimbingan`, `catatan_mahasiswa`, `status_validasi`, `tanggal_validasi`, `validasi_oleh`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 38, 1, '2025-07-22', 'Latar Belakang Masalah BAB I', NULL, 'Penambahan referensi', NULL, NULL, '0', NULL, NULL, 'mahasiswa', '2025-07-22 08:47:18', '2025-07-22 08:50:46');

-- --------------------------------------------------------

--
-- Stand-in structure for view `kaprodi_v`
-- (See below for the actual view)
--
CREATE TABLE `kaprodi_v` (
`id` bigint(20)
,`nip` varchar(30)
,`nama` varchar(100)
,`email` varchar(100)
,`nomor_telepon` varchar(30)
,`prodi_id` bigint(20)
,`nama_prodi` varchar(50)
,`nama_fakultas` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi`
--

CREATE TABLE `konsultasi` (
  `id` bigint(20) NOT NULL,
  `proposal_mahasiswa_id` bigint(20) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `isi` text NOT NULL,
  `bukti` text NOT NULL,
  `sk_tim` varchar(50) DEFAULT NULL,
  `persetujuan_pembimbing` enum('1','0') NOT NULL DEFAULT '0' COMMENT '1 = true, 0 = false',
  `persetujuan_kaprodi` enum('1','0') NOT NULL DEFAULT '0' COMMENT '1 = true, 0 = false',
  `komentar_pembimbing` text DEFAULT NULL,
  `komentar_kaprodi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `konsultasi`
--

INSERT INTO `konsultasi` (`id`, `proposal_mahasiswa_id`, `tanggal`, `jam`, `isi`, `bukti`, `sk_tim`, `persetujuan_pembimbing`, `persetujuan_kaprodi`, `komentar_pembimbing`, `komentar_kaprodi`) VALUES
(10, 33, '2022-04-26', '11:00:00', 'Bimbingan BAB 3 Metodologi Penelitian', '20220426060102.doc', NULL, '1', '1', NULL, NULL),
(11, 33, '2022-04-26', '11:05:00', 'Bimbingan Abstrak dan Latar Belakang', '20220426060601.doc', NULL, '1', '1', NULL, NULL),
(12, 32, '2022-04-26', '11:42:00', 'Bimbingan BAB 1 - BAB 2', '20220426064325.doc', NULL, '1', '1', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` bigint(20) NOT NULL,
  `nim` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `prodi_id` bigint(20) NOT NULL,
  `jenis_kelamin` enum('laki-laki','perempuan') NOT NULL,
  `tempat_lahir` varchar(20) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `nomor_telepon` varchar(30) NOT NULL,
  `nomor_telepon_orang_dekat` varchar(30) NOT NULL,
  `ipk` text NOT NULL,
  `foto` varchar(50) DEFAULT NULL,
  `password` text NOT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '1' COMMENT '1 = aktif, 0 = nonaktif'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `nim`, `nama`, `prodi_id`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `email`, `alamat`, `nomor_telepon`, `nomor_telepon_orang_dekat`, `ipk`, `foto`, `password`, `status`) VALUES
(32, '25104540', 'Hendro Mahasiswa', 10, 'laki-laki', 'Merauke, Bade', '2025-07-22', 'yohaneshenz@gmail.com', 'Merauke', '081295111732', '081295111782', '3', '20250721021959_32.png', '$2y$10$Upai5wQDDl1XXxXAQjF5oOPElLJ6ztbHhpLHvpMTeI0z1ZrnAFFB6', '1'),
(33, '2736373738', 'Herybertus Oktaviani', 10, 'laki-laki', 'Merauke', '1990-07-12', 'danielpuraka@student.stkyakobus.ac.id', 'Merauke', '081295111706', '081295111705', '3.20', '6877147cddd68.jpg', '$2y$10$Pq5WC53ySok2ae9Y4/hHZOCVLXavBZZKLRRnPYwi5RCfI78EbY4re', '1'),
(39, '2342345456', 'Fransiskus Nam Kaize', 10, 'laki-laki', 'Merauke', '2025-07-02', 'fransiskus.nam27@guru.sma.belajar.id', 'Missi 2, Mandala Merauke', '0812324546551', '0812324546552', '2.5', '20250721042447_39.png', '$2y$10$vlVta1kzzcDcxB5gZxx5e.VE9pQgaYNK54UNU/476kcC1B9rSx/ue', '1'),
(40, '2343466778', 'Apang Pinim', 10, 'laki-laki', 'SUKAKIONG', '2025-07-09', 'jurnaljpkat@gmail.com', 'Jl. Yogya-Wonosari Km. 23, Putat II, RT 034, RW 009, Desa Putat, Kecamatan Patuk', '081295111706', '081295111703', '3.2', '687cbc2ae5983.jpg', '$2y$10$GnauHDsx./X42rg5ZMhAX.Z/KP30wzbMFdAQ1qNbIVkr/Nb5nI.PO', '1');

-- --------------------------------------------------------

--
-- Stand-in structure for view `mahasiswa_v`
-- (See below for the actual view)
--
CREATE TABLE `mahasiswa_v` (
`nama_prodi` varchar(50)
,`id` bigint(20)
,`nim` varchar(50)
,`nama` varchar(100)
,`prodi_id` bigint(20)
,`jenis_kelamin` enum('laki-laki','perempuan')
,`tempat_lahir` varchar(20)
,`tanggal_lahir` date
,`email` varchar(100)
,`alamat` text
,`nomor_telepon` varchar(30)
,`nomor_telepon_orang_dekat` varchar(30)
,`ipk` text
,`foto` varchar(50)
,`password` text
,`status` enum('1','0')
);

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` bigint(20) NOT NULL,
  `jenis` enum('proposal_masuk','proposal_disetujui','proposal_ditolak','pembimbing_ditunjuk','pembimbing_menyetujui','pembimbing_menolak') NOT NULL,
  `untuk_role` enum('mahasiswa','dosen','kaprodi','admin') NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `proposal_id` bigint(20) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `dibaca` tinyint(1) DEFAULT 0,
  `tanggal_dibuat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `jenis`, `untuk_role`, `user_id`, `proposal_id`, `judul`, `pesan`, `dibaca`, `tanggal_dibuat`) VALUES
(1, '', 'dosen', 28, NULL, 'Pendaftaran Seminar Proposal/Skripsi', 'Yth. Bapak/Ibu,\n\nSaya bermaksud untuk mendaftar seminar [proposal/skripsi]. Dokumen yang sudah saya siapkan:\n\n- [Daftar dokumen]\n\nMohon bimbingan untuk langkah selanjutnya.\n\nTerima kasih.\n\nHormat saya,\nYohanes Kandam', 0, '2025-07-21 16:59:53'),
(2, '', 'dosen', 28, NULL, 'Konsultasi Proposal Skripsi', 'Yth. Bapak/Ibu,\n\nSaya ingin berkonsultasi mengenai proposal skripsi saya. Mohon bantuan untuk:\n\n1. [Jelaskan hal yang ingin dikonsultasikan]\n2. [Tambahkan pertanyaan spesifik]\n\nTerima kasih atas waktu dan bimbingannya.\n\nHormat saya,\nYohanes Kandam', 0, '2025-07-21 17:00:11'),
(3, '', 'dosen', 10, NULL, 'Pendaftaran Seminar Proposal/Skripsi', 'Yth. Bapak/Ibu,\n\nSaya bermaksud untuk mendaftar seminar [proposal/skripsi]. Dokumen yang sudah saya siapkan:\n\n- [Daftar dokumen]\n\nMohon bimbingan untuk langkah selanjutnya.\n\nTerima kasih.\n\nHormat saya,\nYohanes Kandam', 0, '2025-07-21 17:02:10'),
(4, '', 'dosen', 10, NULL, 'Pengaturan Jadwal Bimbingan', 'Yth. Bapak/Ibu,\n\nSaya ingin mengatur jadwal bimbingan. Apakah Bapak/Ibu berkenan untuk:\n\nWaktu yang saya usulkan:\n- Hari: [Hari]\n- Tanggal: [Tanggal]\n- Jam: [Jam]\n- Tempat: [Tempat/Online]\n\nTerima kasih.\n\nHormat saya,\nYohanes Kandam', 0, '2025-07-21 17:04:37'),
(5, '', 'dosen', 2, NULL, 'Pengaturan Jadwal Bimbingan', 'Yth. Bapak/Ibu,\n\nSaya ingin mengatur jadwal bimbingan. Apakah Bapak/Ibu berkenan untuk:\n\nWaktu yang saya usulkan:\n- Hari: [Hari]\n- Tanggal: [Tanggal]\n- Jam: [Jam]\n- Tempat: [Tempat/Online]\n\nTerima kasih.\n\nHormat saya,\nYohanes Kandam', 0, '2025-07-21 17:05:48');

-- --------------------------------------------------------

--
-- Table structure for table `penelitian`
--

CREATE TABLE `penelitian` (
  `id` bigint(20) NOT NULL,
  `judul_penelitian` varchar(100) DEFAULT NULL,
  `proposal_mahasiswa_id` bigint(20) NOT NULL,
  `pembimbing_id` bigint(20) NOT NULL,
  `penguji_id` bigint(20) NOT NULL,
  `bukti` text NOT NULL,
  `persetujuan_pembimbing` enum('1','2') NOT NULL COMMENT '1 = true, 2 = false',
  `persetujuan_penguji` enum('1','2') NOT NULL COMMENT '1 = true, 2 = false',
  `komentar_pembimbing` text DEFAULT NULL,
  `komentar_penguji` text DEFAULT NULL,
  `sk_tim` varchar(50) DEFAULT NULL,
  `file_seminar` varchar(50) DEFAULT NULL,
  `bukti_konsultasi` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `penelitian`
--

INSERT INTO `penelitian` (`id`, `judul_penelitian`, `proposal_mahasiswa_id`, `pembimbing_id`, `penguji_id`, `bukti`, `persetujuan_pembimbing`, `persetujuan_penguji`, `komentar_pembimbing`, `komentar_penguji`, `sk_tim`, `file_seminar`, `bukti_konsultasi`) VALUES
(20, 'Rancang Bangun CMS Berbasi IT Service Menggunakan ITIL V3', 33, 8, 1, '20220426034134.pdf', '2', '2', NULL, NULL, '20220426034134.pdf', '20220426034134.pdf', '20220426034134.pdf');

-- --------------------------------------------------------

--
-- Stand-in structure for view `penguji_dosen_v`
-- (See below for the actual view)
--
CREATE TABLE `penguji_dosen_v` (
`nip` varchar(30)
,`nama` varchar(100)
,`nomor_telepon` varchar(30)
,`email` varchar(100)
,`level` enum('1','2','4')
,`id` bigint(20)
,`mahasiswa_id` bigint(20)
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`nama_prodi` varchar(50)
);

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman_tahapan`
--

CREATE TABLE `pengumuman_tahapan` (
  `id` int(11) NOT NULL,
  `no` int(11) NOT NULL,
  `tahapan` varchar(255) NOT NULL,
  `tanggal_deadline` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `aktif` enum('1','0') DEFAULT '1' COMMENT '1=aktif, 0=non-aktif',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengumuman_tahapan`
--

INSERT INTO `pengumuman_tahapan` (`id`, `no`, `tahapan`, `tanggal_deadline`, `keterangan`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pengajuan Proposal', '2025-08-07', 'Periode 1 2025', '1', '2025-07-15 17:29:36', '2025-07-20 07:05:21'),
(3, 2, 'Seminar Proposal', '2025-10-31', 'Seminar Proposal Bab 1-3', '1', '2025-07-15 17:29:36', '2025-07-19 17:42:25'),
(4, 3, 'Ujian Skripsi', '2026-05-25', 'Seminar Hasil Bab 1-5', '1', '2025-07-15 17:29:36', '2025-07-19 16:54:31'),
(5, 4, 'Revisi dan Publikasi', '2026-07-30', 'Perbaikan dan Publikasi Skripsi', '1', '2025-07-15 17:29:36', '2025-07-19 16:54:52'),
(6, 5, 'Yudisium', '2026-08-05', 'Pengukuhan dan Wisuda', '1', '2025-07-15 17:29:36', '2025-07-19 16:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `prodi`
--

CREATE TABLE `prodi` (
  `id` bigint(20) NOT NULL,
  `kode` varchar(30) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `dosen_id` bigint(20) NOT NULL COMMENT 'ketua prodi (pembimbing)',
  `fakultas_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `prodi`
--

INSERT INTO `prodi` (`id`, `kode`, `nama`, `dosen_id`, `fakultas_id`) VALUES
(10, '86208', 'Pendidikan Keagamaan Katolik', 10, 1),
(11, '86206', 'Pendidikan Guru Sekolah Dasar', 11, 1);

-- --------------------------------------------------------

--
-- Table structure for table `proposal_mahasiswa`
--

CREATE TABLE `proposal_mahasiswa` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) NOT NULL,
  `judul` varchar(250) NOT NULL,
  `ringkasan` varchar(5000) NOT NULL,
  `jenis_penelitian` enum('Kuantitatif','Kualitatif','Mixed Method') DEFAULT NULL,
  `lokasi_penelitian` varchar(255) DEFAULT NULL,
  `uraian_masalah` text DEFAULT NULL,
  `file_draft_proposal` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Tanggal pengajuan proposal oleh mahasiswa',
  `dosen_id` bigint(20) DEFAULT NULL COMMENT 'pembimbing',
  `dosen2_id` int(11) NOT NULL DEFAULT 1 COMMENT 'pembimbing 2',
  `dosen_penguji_id` int(11) DEFAULT NULL,
  `dosen_penguji2_id` bigint(20) DEFAULT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '0' COMMENT '1 = disetujui, 2 = tidak disetujui',
  `status_kaprodi` enum('0','1','2') DEFAULT '0' COMMENT '0=menunggu review, 1=disetujui, 2=ditolak',
  `komentar_kaprodi` text DEFAULT NULL,
  `tanggal_review_kaprodi` datetime DEFAULT NULL,
  `status_pembimbing` enum('0','1','2') DEFAULT '0' COMMENT '0=belum diminta, 1=menyetujui, 2=menolak',
  `komentar_pembimbing` text DEFAULT NULL,
  `tanggal_respon_pembimbing` datetime DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `tanggal_penetapan` datetime DEFAULT NULL COMMENT 'Tanggal kaprodi menetapkan pembimbing & penguji',
  `penetapan_oleh` bigint(20) DEFAULT NULL COMMENT 'ID kaprodi yang menetapkan',
  `workflow_status` enum('proposal','bimbingan','seminar_proposal','penelitian','seminar_skripsi','publikasi','selesai') DEFAULT 'proposal' COMMENT 'Status workflow saat ini: proposal->bimbingan->seminar_proposal->penelitian->seminar_skripsi->publikasi->selesai',
  `status_seminar_proposal` enum('0','1','2') DEFAULT '0' COMMENT '0=menunggu review, 1=disetujui, 2=ditolak',
  `komentar_seminar_proposal` text DEFAULT NULL COMMENT 'Komentar kaprodi untuk seminar proposal',
  `tanggal_review_seminar_proposal` datetime DEFAULT NULL COMMENT 'Tanggal kaprodi review seminar proposal',
  `tanggal_seminar_proposal` date DEFAULT NULL COMMENT 'Tanggal pelaksanaan seminar proposal',
  `tempat_seminar_proposal` varchar(255) DEFAULT NULL COMMENT 'Tempat pelaksanaan seminar proposal',
  `status_seminar_skripsi` enum('0','1','2') DEFAULT '0' COMMENT '0=menunggu review, 1=disetujui, 2=ditolak',
  `komentar_seminar_skripsi` text DEFAULT NULL COMMENT 'Komentar kaprodi untuk seminar skripsi',
  `tanggal_review_seminar_skripsi` datetime DEFAULT NULL COMMENT 'Tanggal kaprodi review seminar skripsi',
  `tanggal_seminar_skripsi` date DEFAULT NULL COMMENT 'Tanggal pelaksanaan seminar skripsi',
  `tempat_seminar_skripsi` varchar(255) DEFAULT NULL COMMENT 'Tempat pelaksanaan seminar skripsi',
  `status_publikasi` enum('0','1','2') DEFAULT '0' COMMENT '0=menunggu review, 1=disetujui, 2=ditolak',
  `komentar_publikasi` text DEFAULT NULL COMMENT 'Komentar kaprodi untuk publikasi',
  `tanggal_review_publikasi` datetime DEFAULT NULL COMMENT 'Tanggal kaprodi review publikasi',
  `link_repository` varchar(500) DEFAULT NULL COMMENT 'Link repository publikasi tugas akhir',
  `tanggal_publikasi` date DEFAULT NULL COMMENT 'Tanggal publikasi ke repository',
  `file_seminar_proposal` varchar(255) DEFAULT NULL COMMENT 'File dokumen seminar proposal (Bab 1-3)',
  `file_seminar_skripsi` varchar(255) DEFAULT NULL COMMENT 'File dokumen seminar skripsi (Bab 1-5)',
  `file_skripsi_final` varchar(255) DEFAULT NULL COMMENT 'File skripsi final untuk publikasi',
  `surat_izin_penelitian` varchar(255) DEFAULT NULL COMMENT 'File surat izin penelitian',
  `status_izin_penelitian` enum('0','1','2') DEFAULT '0' COMMENT '0=belum diminta, 1=disetujui, 2=ditolak'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposal_mahasiswa`
--

INSERT INTO `proposal_mahasiswa` (`id`, `mahasiswa_id`, `judul`, `ringkasan`, `jenis_penelitian`, `lokasi_penelitian`, `uraian_masalah`, `file_draft_proposal`, `created_at`, `dosen_id`, `dosen2_id`, `dosen_penguji_id`, `dosen_penguji2_id`, `status`, `status_kaprodi`, `komentar_kaprodi`, `tanggal_review_kaprodi`, `status_pembimbing`, `komentar_pembimbing`, `tanggal_respon_pembimbing`, `deadline`, `tanggal_penetapan`, `penetapan_oleh`, `workflow_status`, `status_seminar_proposal`, `komentar_seminar_proposal`, `tanggal_review_seminar_proposal`, `tanggal_seminar_proposal`, `tempat_seminar_proposal`, `status_seminar_skripsi`, `komentar_seminar_skripsi`, `tanggal_review_seminar_skripsi`, `tanggal_seminar_skripsi`, `tempat_seminar_skripsi`, `status_publikasi`, `komentar_publikasi`, `tanggal_review_publikasi`, `link_repository`, `tanggal_publikasi`, `file_seminar_proposal`, `file_seminar_skripsi`, `file_skripsi_final`, `surat_izin_penelitian`, `status_izin_penelitian`) VALUES
(36, 32, 'Pengaruh Gaya Berpacaran terhadap Partisipasi Orang Muda Katolik (OMK) dalam Hidup Menggereja di Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke Tahun 2025', 'Partisipasi Orang Muda Katolik (OMK) dalam hidup menggereja merupakan indikator penting keberlangsungan Gereja Katolik di masa depan. Namun, kenyataan di lapangan menunjukkan adanya penurunan keterlibatan OMK dalam kegiatan-kegiatan gerejawi, seperti', 'Kuantitatif', 'Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke', 'Partisipasi Orang Muda Katolik (OMK) dalam hidup menggereja merupakan indikator penting keberlangsungan Gereja Katolik di masa depan. Namun, kenyataan di lapangan menunjukkan adanya penurunan keterlibatan OMK dalam kegiatan-kegiatan gerejawi, seperti perayaan Ekaristi, doa lingkungan, dan pelayanan sosial. Salah satu faktor yang diduga berkontribusi terhadap rendahnya partisipasi tersebut adalah gaya berpacaran yang dijalani oleh OMK. Di Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke, fenomena ini mulai tampak signifikan. Gaya pacaran yang tidak sehat—seperti hubungan yang posesif, terlalu mendominasi waktu, atau berorientasi pada kesenangan semata—berpotensi mengalihkan fokus dan komitmen OMK dari kegiatan rohani dan pelayanan gerejawi. Di sisi lain, gaya pacaran yang dewasa dan dilandasi nilai-nilai Kristiani justru dapat mendorong partisipasi aktif dalam kehidupan menggereja. Oleh karena itu, penting untuk menelaah lebih jauh bagaimana gaya berpacaran OMK memengaruhi tingkat keterlibatan mereka dalam hidup menggereja. Penelitian ini bertujuan untuk mengidentifikasi pola gaya pacaran yang dominan serta dampaknya terhadap semangat OMK dalam menjalani hidup menggereja di lingkungan Stasi Santo Mikael, demi merancang strategi pastoral yang lebih efektif.', '306cf686ff3f7323b18304b48f7c6e43.docx', '2025-07-18 10:21:14', 25, 1, NULL, NULL, '0', '1', 'Proposal sudah baik dan bisa langsung mulai bimbingan. Terimakasih', '2025-07-17 17:18:16', '0', NULL, NULL, NULL, '2025-07-17 17:18:16', 10, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(37, 33, 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke', 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke, ini latihan saja ya', 'Kuantitatif', 'STK St. Yakobus Merauke', 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke, ini latihan saja ya', 'd2ff01bd1f6cb9b54d4059526a3fb112.docx', '2025-07-18 10:21:14', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(38, 39, 'Pengaruh Minuman Keras terhadap Perilaku Berpacaran Remaja di Sekolah Tinggi Katolik Santo Yakobus Merauke', 'Ini Latihan saja ya', 'Kuantitatif', 'STK St. Yakobus Merauke', 'Ini Latihan saja ya', 'b18d9dd09a1ce4486dfe87ed93958f0c.pdf', '2025-07-21 18:00:46', 25, 1, NULL, NULL, '0', '1', 'Proposal sudah baik, lanjutkan bimbingan dengan dosen', '2025-07-21 18:27:47', '1', 'Saya menyetujui sebagai pembimbing', '2025-07-21 19:11:13', NULL, '2025-07-21 18:27:47', 10, 'bimbingan', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_mahasiswa_backup_20250717`
--

CREATE TABLE `proposal_mahasiswa_backup_20250717` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `mahasiswa_id` bigint(20) NOT NULL,
  `judul` varchar(250) NOT NULL,
  `ringkasan` varchar(5000) NOT NULL,
  `jenis_penelitian` enum('Kuantitatif','Kualitatif','Mixed Method') DEFAULT NULL,
  `lokasi_penelitian` varchar(255) DEFAULT NULL,
  `uraian_masalah` text DEFAULT NULL,
  `file_draft_proposal` varchar(255) DEFAULT NULL,
  `dosen_id` bigint(20) DEFAULT NULL COMMENT 'pembimbing',
  `dosen2_id` int(11) NOT NULL DEFAULT 1 COMMENT 'pembimbing 2',
  `dosen_penguji_id` int(11) DEFAULT NULL,
  `dosen_penguji2_id` bigint(20) DEFAULT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '0' COMMENT '1 = disetujui, 2 = tidak disetujui',
  `status_kaprodi` enum('0','1','2') DEFAULT '0' COMMENT '0=menunggu review, 1=disetujui, 2=ditolak',
  `komentar_kaprodi` text DEFAULT NULL,
  `tanggal_review_kaprodi` datetime DEFAULT NULL,
  `status_pembimbing` enum('0','1','2') DEFAULT '0' COMMENT '0=belum diminta, 1=menyetujui, 2=menolak',
  `komentar_pembimbing` text DEFAULT NULL,
  `tanggal_respon_pembimbing` datetime DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `tanggal_penetapan` datetime DEFAULT NULL COMMENT 'Tanggal kaprodi menetapkan pembimbing & penguji',
  `penetapan_oleh` bigint(20) DEFAULT NULL COMMENT 'ID kaprodi yang menetapkan'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposal_mahasiswa_backup_20250717`
--

INSERT INTO `proposal_mahasiswa_backup_20250717` (`id`, `mahasiswa_id`, `judul`, `ringkasan`, `jenis_penelitian`, `lokasi_penelitian`, `uraian_masalah`, `file_draft_proposal`, `dosen_id`, `dosen2_id`, `dosen_penguji_id`, `dosen_penguji2_id`, `status`, `status_kaprodi`, `komentar_kaprodi`, `tanggal_review_kaprodi`, `status_pembimbing`, `komentar_pembimbing`, `tanggal_respon_pembimbing`, `deadline`, `tanggal_penetapan`, `penetapan_oleh`) VALUES
(34, 18, 'Pengaruh x terhadap Y bagi mahasiswa STK', 'Tes saja pak untuk proposalini', NULL, NULL, NULL, NULL, 10, 11, 11, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `proposal_mahasiswa_detail_v`
-- (See below for the actual view)
--
CREATE TABLE `proposal_mahasiswa_detail_v` (
`id` bigint(20)
,`mahasiswa_id` bigint(20)
,`judul` varchar(250)
,`ringkasan` varchar(5000)
,`dosen_id` bigint(20)
,`dosen2_id` int(11)
,`dosen_penguji_id` int(11)
,`dosen_penguji2_id` bigint(20)
,`status` enum('1','0')
,`deadline` datetime
,`tanggal_penetapan` datetime
,`penetapan_oleh` bigint(20)
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`email_mahasiswa` varchar(100)
,`nama_prodi` varchar(50)
,`nama_pembimbing` varchar(100)
,`nama_pembimbing2` varchar(100)
,`nama_penguji1` varchar(100)
,`nama_penguji2` varchar(100)
,`nama_kaprodi_penetapan` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `proposal_mahasiswa_v`
-- (See below for the actual view)
--
CREATE TABLE `proposal_mahasiswa_v` (
`id` bigint(20)
,`mahasiswa_id` bigint(20)
,`judul` varchar(250)
,`ringkasan` varchar(5000)
,`dosen_id` bigint(20)
,`dosen_penguji_id` int(11)
,`status` enum('1','0')
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`nama_prodi` varchar(50)
,`deadline` datetime
,`email` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `proposal_workflow`
--

CREATE TABLE `proposal_workflow` (
  `id` bigint(20) NOT NULL,
  `proposal_id` bigint(20) NOT NULL,
  `tahap` enum('pengajuan','review_kaprodi','approval_pembimbing','penetapan_selesai') NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL,
  `komentar` text DEFAULT NULL,
  `diproses_oleh` bigint(20) DEFAULT NULL,
  `tanggal_proses` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposal_workflow`
--

INSERT INTO `proposal_workflow` (`id`, `proposal_id`, `tahap`, `status`, `komentar`, `diproses_oleh`, `tanggal_proses`) VALUES
(3, 36, 'pengajuan', 'approved', NULL, 32, '2025-07-17 08:59:52'),
(4, 37, 'pengajuan', 'approved', NULL, 33, '2025-07-17 08:59:52');

-- --------------------------------------------------------

--
-- Table structure for table `seminar`
--

CREATE TABLE `seminar` (
  `id` bigint(20) NOT NULL,
  `proposal_mahasiswa_id` bigint(20) NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `tempat` text NOT NULL,
  `file_proposal` varchar(50) NOT NULL,
  `sk_tim` varchar(50) NOT NULL,
  `bukti_konsultasi` varchar(50) DEFAULT NULL,
  `persetujuan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skripsi`
--

CREATE TABLE `skripsi` (
  `id` int(11) NOT NULL,
  `judul_skripsi` varchar(100) DEFAULT NULL,
  `dosen_id` int(11) DEFAULT NULL,
  `dosen_penguji_id` int(11) DEFAULT NULL,
  `file_skripsi` varchar(50) DEFAULT '',
  `sk_tim` varchar(50) DEFAULT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `jadwal_skripsi` datetime DEFAULT NULL,
  `status` varchar(1) DEFAULT '',
  `persetujuan` varchar(50) DEFAULT NULL,
  `bukti_konsultasi` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skripsi`
--

INSERT INTO `skripsi` (`id`, `judul_skripsi`, `dosen_id`, `dosen_penguji_id`, `file_skripsi`, `sk_tim`, `mahasiswa_id`, `jadwal_skripsi`, `status`, `persetujuan`, `bukti_konsultasi`) VALUES
(19, 'Rancang Bangun CMS Berbasi IT Service Menggunakan ITIL V3', 8, 1, '20220426040137.pdf', '20220426040137.pdf', 3, '2022-12-26 12:00:00', '1', '20220426040137.pdf', '20220426040137.pdf');

-- --------------------------------------------------------

--
-- Stand-in structure for view `skripsi_v`
-- (See below for the actual view)
--
CREATE TABLE `skripsi_v` (
`nim` varchar(50)
,`nama_prodi` varchar(50)
,`nama_mahasiswa` varchar(100)
,`id` int(11)
,`judul_skripsi` varchar(100)
,`dosen_id` int(11)
,`dosen_penguji_id` int(11)
,`sk_tim` varchar(50)
,`mahasiswa_id` int(11)
,`nama_pembimbing` varchar(100)
,`jadwal_skripsi` datetime
,`file_skripsi` varchar(50)
,`status` varchar(1)
,`persetujuan` varchar(50)
,`bukti_konsultasi` varchar(50)
,`email` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `skripsi_vl`
-- (See below for the actual view)
--
CREATE TABLE `skripsi_vl` (
`nim` varchar(50)
,`nama_prodi` varchar(50)
,`nama_mahasiswa` varchar(100)
,`id` int(11)
,`judul_skripsi` varchar(100)
,`dosen_id` int(11)
,`dosen_penguji_id` int(11)
,`sk_tim` varchar(50)
,`mahasiswa_id` int(11)
,`nama_pembimbing` varchar(100)
,`nama_penguji` varchar(100)
,`jadwal_skripsi` datetime
,`file_skripsi` varchar(50)
,`status` varchar(1)
,`persetujuan` varchar(50)
,`bukti_konsultasi` varchar(50)
,`email` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure for view `bimbingan_dosen_v`
--
DROP TABLE IF EXISTS `bimbingan_dosen_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `bimbingan_dosen_v`  AS SELECT `d`.`nip` AS `nip`, `d`.`nama` AS `nama`, `d`.`nomor_telepon` AS `nomor_telepon`, `d`.`email` AS `email`, `d`.`level` AS `level`, `pmv`.`nim` AS `nim`, `pmv`.`nama_mahasiswa` AS `nama_mahasiswa`, `pmv`.`nama_prodi` AS `nama_prodi`, `pmv`.`mahasiswa_id` AS `mahasiswa_id`, `d`.`id` AS `id` FROM (`dosen` `d` join `proposal_mahasiswa_v` `pmv` on(`d`.`id` = `pmv`.`dosen_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `hasil_kegiatan_v`
--
DROP TABLE IF EXISTS `hasil_kegiatan_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `hasil_kegiatan_v`  AS SELECT `hasil_kegiatan`.`mahasiswa_id` AS `mahasiswa_id`, `hasil_kegiatan`.`id` AS `id`, `hasil_kegiatan`.`file` AS `file`, `hasil_kegiatan`.`kegiatan` AS `kegiatan`, `hasil_kegiatan`.`file_kegiatan` AS `file_kegiatan`, `mahasiswa_v`.`nim` AS `nim`, `mahasiswa_v`.`nama` AS `nama_mahasiswa`, `mahasiswa_v`.`nama_prodi` AS `nama_prodi`, `hasil_kegiatan`.`status` AS `status` FROM (`hasil_kegiatan` join `mahasiswa_v` on(`mahasiswa_v`.`id` = `hasil_kegiatan`.`mahasiswa_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `kaprodi_v`
--
DROP TABLE IF EXISTS `kaprodi_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `kaprodi_v`  AS SELECT `d`.`id` AS `id`, `d`.`nip` AS `nip`, `d`.`nama` AS `nama`, `d`.`email` AS `email`, `d`.`nomor_telepon` AS `nomor_telepon`, `p`.`id` AS `prodi_id`, `p`.`nama` AS `nama_prodi`, `f`.`nama` AS `nama_fakultas` FROM ((`dosen` `d` join `prodi` `p` on(`d`.`id` = `p`.`dosen_id`)) join `fakultas` `f` on(`p`.`fakultas_id` = `f`.`id`)) WHERE `d`.`level` = '4' ;

-- --------------------------------------------------------

--
-- Structure for view `mahasiswa_v`
--
DROP TABLE IF EXISTS `mahasiswa_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `mahasiswa_v`  AS SELECT `p`.`nama` AS `nama_prodi`, `m`.`id` AS `id`, `m`.`nim` AS `nim`, `m`.`nama` AS `nama`, `m`.`prodi_id` AS `prodi_id`, `m`.`jenis_kelamin` AS `jenis_kelamin`, `m`.`tempat_lahir` AS `tempat_lahir`, `m`.`tanggal_lahir` AS `tanggal_lahir`, `m`.`email` AS `email`, `m`.`alamat` AS `alamat`, `m`.`nomor_telepon` AS `nomor_telepon`, `m`.`nomor_telepon_orang_dekat` AS `nomor_telepon_orang_dekat`, `m`.`ipk` AS `ipk`, `m`.`foto` AS `foto`, `m`.`password` AS `password`, `m`.`status` AS `status` FROM (`mahasiswa` `m` join `prodi` `p` on(`m`.`prodi_id` = `p`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `penguji_dosen_v`
--
DROP TABLE IF EXISTS `penguji_dosen_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `penguji_dosen_v`  AS SELECT `dosen`.`nip` AS `nip`, `dosen`.`nama` AS `nama`, `dosen`.`nomor_telepon` AS `nomor_telepon`, `dosen`.`email` AS `email`, `dosen`.`level` AS `level`, `dosen`.`id` AS `id`, `proposal_mahasiswa_v`.`mahasiswa_id` AS `mahasiswa_id`, `proposal_mahasiswa_v`.`nim` AS `nim`, `proposal_mahasiswa_v`.`nama_mahasiswa` AS `nama_mahasiswa`, `proposal_mahasiswa_v`.`nama_prodi` AS `nama_prodi` FROM (`dosen` join `proposal_mahasiswa_v` on(`dosen`.`id` = `proposal_mahasiswa_v`.`dosen_penguji_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `proposal_mahasiswa_detail_v`
--
DROP TABLE IF EXISTS `proposal_mahasiswa_detail_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `proposal_mahasiswa_detail_v`  AS SELECT `pm`.`id` AS `id`, `pm`.`mahasiswa_id` AS `mahasiswa_id`, `pm`.`judul` AS `judul`, `pm`.`ringkasan` AS `ringkasan`, `pm`.`dosen_id` AS `dosen_id`, `pm`.`dosen2_id` AS `dosen2_id`, `pm`.`dosen_penguji_id` AS `dosen_penguji_id`, `pm`.`dosen_penguji2_id` AS `dosen_penguji2_id`, `pm`.`status` AS `status`, `pm`.`deadline` AS `deadline`, `pm`.`tanggal_penetapan` AS `tanggal_penetapan`, `pm`.`penetapan_oleh` AS `penetapan_oleh`, `m`.`nim` AS `nim`, `m`.`nama` AS `nama_mahasiswa`, `m`.`email` AS `email_mahasiswa`, `pr`.`nama` AS `nama_prodi`, `d1`.`nama` AS `nama_pembimbing`, `d2`.`nama` AS `nama_pembimbing2`, `dp1`.`nama` AS `nama_penguji1`, `dp2`.`nama` AS `nama_penguji2`, `dk`.`nama` AS `nama_kaprodi_penetapan` FROM (((((((`proposal_mahasiswa` `pm` join `mahasiswa` `m` on(`pm`.`mahasiswa_id` = `m`.`id`)) join `prodi` `pr` on(`m`.`prodi_id` = `pr`.`id`)) left join `dosen` `d1` on(`pm`.`dosen_id` = `d1`.`id`)) left join `dosen` `d2` on(`pm`.`dosen2_id` = `d2`.`id`)) left join `dosen` `dp1` on(`pm`.`dosen_penguji_id` = `dp1`.`id`)) left join `dosen` `dp2` on(`pm`.`dosen_penguji2_id` = `dp2`.`id`)) left join `dosen` `dk` on(`pm`.`penetapan_oleh` = `dk`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `proposal_mahasiswa_v`
--
DROP TABLE IF EXISTS `proposal_mahasiswa_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `proposal_mahasiswa_v`  AS SELECT `pm`.`id` AS `id`, `pm`.`mahasiswa_id` AS `mahasiswa_id`, `pm`.`judul` AS `judul`, `pm`.`ringkasan` AS `ringkasan`, `pm`.`dosen_id` AS `dosen_id`, `pm`.`dosen_penguji_id` AS `dosen_penguji_id`, `pm`.`status` AS `status`, `mv`.`nim` AS `nim`, `mv`.`nama` AS `nama_mahasiswa`, `mv`.`nama_prodi` AS `nama_prodi`, `pm`.`deadline` AS `deadline`, `mv`.`email` AS `email` FROM (`proposal_mahasiswa` `pm` join `mahasiswa_v` `mv` on(`pm`.`mahasiswa_id` = `mv`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `skripsi_v`
--
DROP TABLE IF EXISTS `skripsi_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `skripsi_v`  AS SELECT `mahasiswa_v`.`nim` AS `nim`, `mahasiswa_v`.`nama_prodi` AS `nama_prodi`, `mahasiswa_v`.`nama` AS `nama_mahasiswa`, `skripsi`.`id` AS `id`, `skripsi`.`judul_skripsi` AS `judul_skripsi`, `skripsi`.`dosen_id` AS `dosen_id`, `skripsi`.`dosen_penguji_id` AS `dosen_penguji_id`, `skripsi`.`sk_tim` AS `sk_tim`, `skripsi`.`mahasiswa_id` AS `mahasiswa_id`, `dosen`.`nama` AS `nama_pembimbing`, `skripsi`.`jadwal_skripsi` AS `jadwal_skripsi`, `skripsi`.`file_skripsi` AS `file_skripsi`, `skripsi`.`status` AS `status`, `skripsi`.`persetujuan` AS `persetujuan`, `skripsi`.`bukti_konsultasi` AS `bukti_konsultasi`, `mahasiswa_v`.`email` AS `email` FROM ((`skripsi` join `mahasiswa_v` on(`skripsi`.`mahasiswa_id` = `mahasiswa_v`.`id`)) join `dosen` on(`skripsi`.`dosen_id` = `dosen`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `skripsi_vl`
--
DROP TABLE IF EXISTS `skripsi_vl`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `skripsi_vl`  AS SELECT `skripsi_v`.`nim` AS `nim`, `skripsi_v`.`nama_prodi` AS `nama_prodi`, `skripsi_v`.`nama_mahasiswa` AS `nama_mahasiswa`, `skripsi_v`.`id` AS `id`, `skripsi_v`.`judul_skripsi` AS `judul_skripsi`, `skripsi_v`.`dosen_id` AS `dosen_id`, `skripsi_v`.`dosen_penguji_id` AS `dosen_penguji_id`, `skripsi_v`.`sk_tim` AS `sk_tim`, `skripsi_v`.`mahasiswa_id` AS `mahasiswa_id`, `skripsi_v`.`nama_pembimbing` AS `nama_pembimbing`, `dosen`.`nama` AS `nama_penguji`, `skripsi_v`.`jadwal_skripsi` AS `jadwal_skripsi`, `skripsi_v`.`file_skripsi` AS `file_skripsi`, `skripsi_v`.`status` AS `status`, `skripsi_v`.`persetujuan` AS `persetujuan`, `skripsi_v`.`bukti_konsultasi` AS `bukti_konsultasi`, `skripsi_v`.`email` AS `email` FROM (`skripsi_v` join `dosen` on(`skripsi_v`.`dosen_penguji_id` = `dosen`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dokumen_hasil`
--
ALTER TABLE `dokumen_hasil`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_sender`
--
ALTER TABLE `email_sender`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fakultas`
--
ALTER TABLE `fakultas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hasil_kegiatan`
--
ALTER TABLE `hasil_kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hasil_penelitian`
--
ALTER TABLE `hasil_penelitian`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hasil_seminar`
--
ALTER TABLE `hasil_seminar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_template`
--
ALTER TABLE `home_template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jurnal_bimbingan`
--
ALTER TABLE `jurnal_bimbingan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proposal_pertemuan` (`proposal_id`,`pertemuan_ke`),
  ADD KEY `idx_proposal_pertemuan` (`proposal_id`,`pertemuan_ke`),
  ADD KEY `idx_tanggal_bimbingan` (`tanggal_bimbingan`),
  ADD KEY `idx_status_validasi` (`status_validasi`),
  ADD KEY `fk_jurnal_dosen` (`validasi_oleh`);

--
-- Indexes for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_role` (`user_id`,`untuk_role`),
  ADD KEY `idx_dibaca` (`dibaca`);

--
-- Indexes for table `penelitian`
--
ALTER TABLE `penelitian`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengumuman_tahapan`
--
ALTER TABLE `pengumuman_tahapan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prodi`
--
ALTER TABLE `prodi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proposal_mahasiswa`
--
ALTER TABLE `proposal_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_workflow_status` (`workflow_status`),
  ADD KEY `idx_status_seminar_proposal` (`status_seminar_proposal`),
  ADD KEY `idx_status_seminar_skripsi` (`status_seminar_skripsi`),
  ADD KEY `idx_status_publikasi` (`status_publikasi`),
  ADD KEY `idx_mahasiswa_workflow` (`mahasiswa_id`,`workflow_status`);

--
-- Indexes for table `proposal_workflow`
--
ALTER TABLE `proposal_workflow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proposal_id` (`proposal_id`),
  ADD KEY `idx_tahap` (`tahap`);

--
-- Indexes for table `seminar`
--
ALTER TABLE `seminar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `skripsi`
--
ALTER TABLE `skripsi`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dokumen_hasil`
--
ALTER TABLE `dokumen_hasil`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `email_sender`
--
ALTER TABLE `email_sender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fakultas`
--
ALTER TABLE `fakultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `hasil_kegiatan`
--
ALTER TABLE `hasil_kegiatan`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `hasil_penelitian`
--
ALTER TABLE `hasil_penelitian`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `hasil_seminar`
--
ALTER TABLE `hasil_seminar`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `home_template`
--
ALTER TABLE `home_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jurnal_bimbingan`
--
ALTER TABLE `jurnal_bimbingan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `penelitian`
--
ALTER TABLE `penelitian`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `pengumuman_tahapan`
--
ALTER TABLE `pengumuman_tahapan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prodi`
--
ALTER TABLE `prodi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `proposal_mahasiswa`
--
ALTER TABLE `proposal_mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `proposal_workflow`
--
ALTER TABLE `proposal_workflow`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `seminar`
--
ALTER TABLE `seminar`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `skripsi`
--
ALTER TABLE `skripsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jurnal_bimbingan`
--
ALTER TABLE `jurnal_bimbingan`
  ADD CONSTRAINT `fk_jurnal_dosen` FOREIGN KEY (`validasi_oleh`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_jurnal_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposal_mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proposal_mahasiswa`
--
ALTER TABLE `proposal_mahasiswa`
  ADD CONSTRAINT `fk_penetapan` FOREIGN KEY (`penetapan_oleh`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_penguji2` FOREIGN KEY (`dosen_penguji2_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
