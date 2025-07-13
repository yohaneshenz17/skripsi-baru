-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 13, 2025 at 05:46 PM
-- Server version: 10.3.39-MariaDB-cll-lve
-- PHP Version: 8.1.32

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
,`level` enum('1','2')
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
  `level` enum('1','2') NOT NULL DEFAULT '2' COMMENT '1 = admin, 2 = dosen'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `nip`, `prodi_id`, `nama`, `nomor_telepon`, `email`, `level`) VALUES
(2, '20201015', 1, 'Super Admin', '081295111706', 'admin@admin.com', '1'),
(10, '2721128601', 1, 'Dedimus Berangka, S.Pd., M.Pd.', '081290909003', 'dedimus@stkyakobus.ac.id', '2'),
(11, '2706058401', 1, 'Steven Ronald Ahlaro, S.Pd., M.Pd.', '082271403437', 'steveahlaro@stkyakobus.ac.id', '2'),
(12, '2720067001', 1, 'Dr. Berlinda Setyo Yunarti, M.Pd.', '085244791002', 'lindayunarti@stkyakobus.ac.id', '2'),
(14, '2709109301', 2, 'Lambertus Ayiriga, S.Pd., M.Pd.', '82197819425', 'lambertus@stkyakobus.ac.id', '2'),
(15, '2728048001', 1, 'Rikardus Kristian Sarang, S.Fil., M.Pd.', '81248525845', 'rikardkristians@stkyakobus.ac.id', '2'),
(16, '2730068501', 1, 'Raimundus Sedo, S.T., M.T.', '81338623494', 'raimundus@stkyakobus.ac.id', '2'),
(17, '2705077801', 2, 'Dr. Erly Lumban Gaol, M.Th.', '81239904548', 'erly@stkyakobus.ac.id', '2'),
(18, '2727128101', 1, 'Yan Yusuf Subu, S.Fil., M.Hum.', '81227909867', 'yanyusuf@stkyakobus.ac.id', '2'),
(19, '2729108301', 1, 'Rosmayasinta Makasau, S.Pd., M.Pd.', '85244236555', 'mayamakasau@stkyakobus.ac.id', '2'),
(20, '2717077001', 1, 'Dr. Donatus Wea, Lic.Iur.', '81247719057', 'romodonwea@stkyakobus.ac.id', '2'),
(21, '2719076301', 1, 'Drs. Xaverius Wonmut, M.Hum.', '81248202058', 'xaveriuswonmut@stkyakobus.ac.id', '2'),
(22, '2729086901', 2, 'Agustinus Kia Wolomasi, S.Ag., M.Pd.', '81386503387', 'aguswolomasi@stkyakobus.ac.id', '2'),
(23, '2709077801', 1, 'Markus Meran, S.Ag., M.Th.', '82248526104', 'markusmeran@stkyakobus.ac.id', '2'),
(24, '1423056901', 1, 'Francisco Noerjanto, S.Ag., M.Si.', '8114890505', 'francisco@stkyakobus.ac.id', '2'),
(25, '2717069001', 1, 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', '81295111706', 'yohaneshenz@stkyakobus.ac.id', '1');

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
(19, '2202007', 'BLASIUS MOA DEDILADO', 10, 'laki-laki', 'Merauke', '0000-00-00', 'videlis@stkyakobus.ac.id', 'Merauke', '082199965283', '081295111706', '3,40', '20250706104049.png', '$2y$10$.8H3d3nEHSATJHxF.H5vOenOHsmYCkImDrWaVdo5r.pC5IXvBpkHy', '1'),
(27, '2202008', 'Test Mahasiswa 1', 10, 'laki-laki', 'Merauke', '2000-01-01', 'test1@stkyakobus.ac.id', 'Jl. Test No. 1 Merauke', '081234567890', '081234567890', '3.50', NULL, '$2y$10$UXJihdtpwt5MtDimlzCqi.4tH8590iNRwABpKfjgqXVTesVGUsntO', '1'),
(28, '2202009', 'Test Mahasiswa 2', 11, 'perempuan', 'Merauke', '2000-02-02', 'test2@stkyakobus.ac.id', 'Jl. Test No. 2 Merauke', '081234567891', '081234567891', '3.75', NULL, '$2y$10$cQOubvRoNG27o/QKrZIaYOiKrHJFmbEBgDkrzhupTck7E2vackmdq', '1'),
(29, '2202010', 'Test Mahasiswa 3', 12, 'laki-laki', 'Merauke', '2000-03-03', 'test3@stkyakobus.ac.id', 'Jl. Test No. 3 Merauke', '081234567892', '081234567892', '3.25', NULL, '$2y$10$xjqWJILHt0x5xU8CNxnI5eNGsmOCEv/.PvoaA0r.w4a/vIF3DYrYy', '1'),
(30, '2717069001', 'Bertolomeus Belang', 11, 'laki-laki', 'Merauke', '0000-00-00', 'bertobelang@stkyakobus.ac.id', 'Merauke', '081245783334', '08129611551', '3', '20250713050742.png', '$2y$10$iN705ZhOaZ8u0U3O4F2H4eQFSoSW9cUCPGGdj30ba1dKXvPLXBpl6', '1');

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
,`level` enum('1','2')
,`id` bigint(20)
,`mahasiswa_id` bigint(20)
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`nama_prodi` varchar(50)
);

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
(11, '86206', 'Pendidikan Guru Sekolah Dasar', 11, 1),
(12, '86905', 'Pendidikan Profesi Guru Pendidikan Agama Katolik', 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `proposal_mahasiswa`
--

CREATE TABLE `proposal_mahasiswa` (
  `id` bigint(20) NOT NULL,
  `mahasiswa_id` bigint(20) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `ringkasan` varchar(5000) NOT NULL,
  `dosen_id` bigint(20) NOT NULL COMMENT 'pembimbing',
  `dosen2_id` int(11) NOT NULL DEFAULT 1 COMMENT 'pembimbing 2',
  `dosen_penguji_id` int(11) DEFAULT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '0' COMMENT '1 = disetujui, 2 = tidak disetujui',
  `deadline` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposal_mahasiswa`
--

INSERT INTO `proposal_mahasiswa` (`id`, `mahasiswa_id`, `judul`, `ringkasan`, `dosen_id`, `dosen2_id`, `dosen_penguji_id`, `status`, `deadline`) VALUES
(34, 18, 'Pengaruh x terhadap Y bagi mahasiswa STK', 'Tes saja pak untuk proposalini', 10, 11, 11, '0', NULL),
(35, 19, 'Pengaruh Miras terhadap Pergaulan Bebas', 'Tes saja', 10, 24, 22, '0', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `proposal_mahasiswa_v`
-- (See below for the actual view)
--
CREATE TABLE `proposal_mahasiswa_v` (
`id` bigint(20)
,`mahasiswa_id` bigint(20)
,`judul` varchar(100)
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
-- Indexes for table `penelitian`
--
ALTER TABLE `penelitian`
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
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
-- AUTO_INCREMENT for table `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `penelitian`
--
ALTER TABLE `penelitian`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `prodi`
--
ALTER TABLE `prodi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `proposal_mahasiswa`
--
ALTER TABLE `proposal_mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
