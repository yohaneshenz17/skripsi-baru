-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 20 Jan 2026 pada 13.08
-- Versi server: 10.3.39-MariaDB-cll-lve
-- Versi PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stkp7133_e_library`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$L9xXw9mWOgIbScIOcBTfDu8C0g8ShUXIvKF1IE3coYwLrJaSzEswW', 'humas@stkyakobus.ac.id', '2026-01-19 03:19:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `nomor_buku` varchar(50) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pengarang` varchar(255) NOT NULL,
  `penerbit` varchar(255) DEFAULT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `stok_tersedia` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `nomor_buku`, `judul`, `pengarang`, `penerbit`, `tahun_terbit`, `stok`, `stok_tersedia`, `created_at`, `updated_at`) VALUES
(10, '001.011.6.Sur.F', 'Filsafat Ilmu Sebuah  Pengantar Populer', 'Jujun S. Suriasumantri', 'Jakarta, PT. Pancaranintan Indahgraha', '2007', 1, 0, '2026-01-19 07:15:56', '2026-01-20 04:01:58'),
(11, '001.1.8.Mus.M', 'Mengembangkan TALENTA untuk sesama', 'Herman Musakabe', 'Yayasan Citra Insan Pembaru', '2008', 29, 28, '2026-01-19 07:15:56', '2026-01-20 00:27:04'),
(12, '001.2.1.Emz.M', 'Metodologi Penelitian Pendidikan', 'Prof. Dr. Emzir, M.Pd.', 'Jakarta, Rajawali Pers', '2012', 1, 1, '2026-01-19 07:15:56', '2026-01-20 01:17:33'),
(13, '001.2.2.Suk.M', 'Metode Penelitian Pendidikan', 'Prof. Drs. Sukestiyarno, MS, Ph.D', 'Semarang, UNNES Press', '2020', 2, 2, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(14, '001.2.3 Sug.M', 'Metode Penelitian & Pengembangan Research and Development \' Untuk Bidang Pendidikan, Manajemen, Sosial, Teknik', 'Prof. Dr. Sugiyono', 'Bandung, Alfa Beta', '2016', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(15, '001.3.1.Sar.M', 'Metode Riset Skripsi Pendekatan Kuantitatif', 'Jonathan Sarwwono', 'Jakarta, Media Komputer', '2012', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(16, '001.3.2.Ham.M', 'Menulis Laporan Dan  Proposal', 'Alexander Hamilton Institute', 'semarang, Dahara Prize', '1995', 1, 1, '2026-01-19 07:15:56', '2026-01-20 01:39:04'),
(17, '001.3.5. Rir.M', 'Metode Penelitian Sosial Panduan Bagi Mahasiswa', 'Drs. Samel W. Ririhena, M.Si.', 'Fakkara Publishing, Bogor-Indonesia', '2010', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(18, '001.3.6. Moh.M', 'Metodologi & Aplikasi Riset Pendidikan', 'Prof. Mohammad Ali & Prof. Muhammad Asrori', 'Bumi Aksara', '2014', 1, 0, '2026-01-19 07:15:56', '2026-01-20 00:27:40'),
(19, '001.3.7. Sur.M', 'Metodologi Penelitian', 'Sumadi Suryabrata', 'Jakarta, PT Raja Grafindo Persada', '2000', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(20, '001.3.8. Sar. M', 'Metode Penelitian Kuantitatif & Kualitatif', 'Jonathan Sarwwono', 'Graha Ilmu', '2006', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(21, '001.4.1.Sup.M', 'Metode Penelitian Praktis', 'Drs.  M. Suparmoko, M.A.,Ph.D.', 'Yogyakarta, BPFE', '1999', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(22, '001.4.2.Fih. P', 'Penelitian Tindakan Kelas', 'Albertus Fiharsono,  S.Pd., M.Hum', 'Yogyakarta, Kanisius', '2014', 2, 2, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(23, '001.4.3.Str.C', 'Cara Menulis Makalah Filsafat', 'James S. Stramel', 'Yogyakarta, Pustaka Pelajar (Anggota IKAPI)', '2002', 3, 3, '2026-01-19 07:15:56', '2026-01-20 00:22:34'),
(24, '001.4.4. Moo.C', 'Cara Meneliti', 'Nick Moore', 'ITB Bandung', '1995', 10, 9, '2026-01-19 07:15:56', '2026-01-19 13:05:21'),
(25, '001.4.6.Tji. S', 'Strategi Riset Lewat Internet', 'Fandy Tjiptono & Totok Budi Santoso', 'Yogyakarta, Andi', '2000', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(26, '001.4.7.War.P', 'Penelitian Tindakan Kelas', 'Igak Wardhani, dkk', 'Universitas Terbuka', '2007', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(27, '001.4.8.Tae.P', 'Petualangan Intelektual Menuju Metode Penelitian Pendidikan', 'Dr. Paulus Taek, MS', 'Gita Kasih', '2009', 17, 17, '2026-01-19 07:15:56', '2026-01-19 07:15:56'),
(28, '001.4.9. Ind.M', 'Menulis Karya Ilmiah, Artikel, Skripsi, Thesis dan Disertasi', 'Etty Indriati, Ph.D', 'PT. Gramedia Pustaka Utama', '2005', 4, 4, '2026-01-19 07:15:56', '2026-01-20 00:26:12'),
(29, '001.4.10 Suk.M', 'Manajemen Penelitian Tindakan Kelas', 'Sukidin, Basrowi, Suranto', 'Insan Cendekia', '2008', 1, 1, '2026-01-19 07:15:56', '2026-01-19 07:15:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dosen`
--

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL,
  `nuptk` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `dosen`
--

INSERT INTO `dosen` (`id`, `nuptk`, `nama`, `program_studi`, `no_hp`, `foto`, `created_at`, `updated_at`) VALUES
(1, '0161737638130053', 'AGUSTINUS GERADA', 'S1 Pendidikan Guru Sekolah Dasar', '081344948359', '696e10bb2f225.jpg', '2026-01-19 11:03:12', '2026-01-19 11:08:43'),
(2, '8161747648130090', 'AGUSTINUS KIA WOLOMASI', 'S1 Pendidikan Guru Sekolah Dasar', '081386503387', '696e10826e4ac.jpg', '2026-01-19 11:03:12', '2026-01-19 11:07:46'),
(3, '6952748649230140', 'BERLINDA SETYO YUNARTI', 'Profesi Pendidikan Guru PAK', '085244791002', '696e10754eb32.jpg', '2026-01-19 11:03:12', '2026-01-19 11:07:33'),
(4, '8553764665130220', 'DEDIMUS BERANGKA', 'S1 Pendidikan Keagamaan Katolik', '081290909003', '696e103dba893.jpg', '2026-01-19 11:03:12', '2026-01-19 11:06:37'),
(5, '6049748649137040', 'DONATUS WEA', 'S1 Pendidikan Keagamaan Katolik', '081247719057', '696e103399a83.jpg', '2026-01-19 11:03:12', '2026-01-19 11:06:27'),
(6, '5037756657230200', 'ERLY LUMBAN GAOL', 'S1 Pendidikan Guru Sekolah Dasar', '081239904548', '696e102913429.jpg', '2026-01-19 11:03:12', '2026-01-19 11:06:17'),
(7, '3855747648130090', 'FRANCISCO NOERJANTO', 'S1 Pendidikan Keagamaan Katolik', '08114890505', '696e101c86233.jpg', '2026-01-19 11:03:12', '2026-01-19 11:06:04'),
(8, '6341771672130310', 'LAMBERTUS AYIRIGA', 'S1 Pendidikan Guru Sekolah Dasar', '082197819425', '696e1012132d6.jpg', '2026-01-19 11:03:12', '2026-01-19 11:05:54'),
(9, '3041756657130120', 'MARKUS MERAN', 'S1 Pendidikan Keagamaan Katolik', '081385478095', '696e10075f564.jpg', '2026-01-19 11:03:12', '2026-01-19 11:05:43'),
(10, '7962763664130210', 'RAIMUNDUS SEDO', 'Profesi Pendidikan Guru PAK', '081338623494', '696e0ffd14cb1.jpg', '2026-01-19 11:03:12', '2026-01-19 11:05:33'),
(11, '2760758659130170', 'RIKARDUS KRISTIAN SARANG', 'Profesi Pendidikan Guru PAK', '081248525845', '696e0ff39fbd1.jpg', '2026-01-19 11:03:12', '2026-01-19 11:05:23'),
(12, '1361761662230200', 'ROSMAYASINTA MAKASAU', 'Profesi Pendidikan Guru PAK', '085244236555', '696e0fea1b736.jpg', '2026-01-19 11:03:12', '2026-01-19 11:05:14'),
(13, '0838762663130272', 'STEVEN RONALD AHLARO', 'S1 Pendidikan Guru Sekolah Dasar', '082271403437', '696e0fdb920be.jpg', '2026-01-19 11:03:12', '2026-01-19 11:04:59'),
(14, '0051741642130063', 'XAVERIUS WONMUT', 'S1 Pendidikan Keagamaan Katolik', '081248202058', '696e0fd0330b5.jpg', '2026-01-19 11:03:12', '2026-01-19 11:04:48'),
(15, '1559759660130170', 'YAN YUSUF SUBU', 'S1 Pendidikan Keagamaan Katolik', '081227909867', '696e0fc5d97bd.jpg', '2026-01-19 11:03:12', '2026-01-19 11:04:37'),
(16, '9949768669131040', 'YOHANES HENDRO PRANYOTO', 'S1 Pendidikan Keagamaan Katolik', '081295111706', '696e0fb6c9a6e.jpg', '2026-01-19 11:03:12', '2026-01-19 11:04:22'),
(17, '3343776677230160', 'YOLENTA OKTOVIA MAHUZE', 'S1 Pendidikan Guru Sekolah Dasar', '082195487592', '696e0fa583d31.jpg', '2026-01-19 11:03:12', '2026-01-19 11:04:05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `angkatan` varchar(10) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `nim`, `nama`, `program_studi`, `angkatan`, `no_hp`, `foto`, `created_at`, `updated_at`) VALUES
(1, '2586206001', 'ADOLVINUS YIMSI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(2, '2586206002', 'AMANDUS BUA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81234896732', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(3, '2586206003', 'ANDREAS I.P. SOMAHAI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82189520537', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(4, '2586206004', 'AULEN MAYANU', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82142987939', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(5, '2586206005', 'BAYO F. BAKAYUR', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81343135041', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(6, '2586206006', 'DENISTOPAN YERMOGOIN', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81324550165', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(7, '2586206007', 'ELISABET APRILIANTI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82173435947', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(8, '2586206008', 'ESTER SANTIA KAHA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81247732234', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(9, '2586206009', 'ESTERIANA ADE RINI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '85244843589', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(10, '2586206010', 'ESTERLINA LAIKARAN', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82298577643', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(11, '2586206011', 'EVARISTA UKUROP', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82299834869', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(12, '2586206012', 'EVERISTA DINA WELI YOLMEN', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82248500864', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(13, '2586206013', 'FEBRIYANTI KETERI MAKING', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81338052629', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(14, '2586206014', 'FILOMENA BASAGAI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82197970698', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(15, '2586206015', 'FLORENTINA KWERKUJAI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82214956220', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(16, '2586206016', 'FRANSISKUS B. SUJONO', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81399471702', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(17, '2586206017', 'HERI HERODES IKORO AFA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '85282270246', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(18, '2586206018', 'INOSENSIUS SIWASA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81389620465', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(19, '2586206019', 'JEKSON PAET', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82199549202', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(20, '2586206020', 'KORNELIA NUMANGGARUNE', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(21, '2586206021', 'KRISTINA HANAHAGI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82188600415', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(22, '2586206022', 'KRISTINA NASU RUING', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82297742812', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(23, '2586206023', 'LIDYA DONATA G. AKY', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81248617003', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(24, '2586206024', 'MARGARETA PASIM', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82331572848', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(25, '2586206025', 'MARGARETTA BERA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82199794281', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(26, '2586206026', 'MARIA APRILIA RAKMAYATI MEDE', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82266783242', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(27, '2586206027', 'MARIA CEKACA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(28, '2586206028', 'MARIA EKYAK', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82199798832', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(29, '2586206029', 'MARIA LIDWINA JENA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81345379083', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(30, '2586206030', 'MARIA NOVALIA SINTIA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(31, '2586206031', 'MARIUS KAIMU', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81324550165', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(32, '2586206032', 'MARYAM', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82198841864', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(33, '2586206033', 'MODESTA KARISMA KIMKURIN', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82210328523', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(34, '2586206034', 'PASKALIN IRIANTI FOFIED', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82198325592', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(35, '2586206035', 'PATRISIUS DOKO', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '85250616363', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(36, '2586206036', 'PETRUNELA WIRAMU', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82151322389', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(37, '2586206037', 'RICHARDUS SUMAGAI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81240800542', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(38, '2586206038', 'RONI ALBERTUS EBA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82258362914', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(39, '2586206039', 'TIMOTHY TATANGGONA WIBOWO', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81343256528', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(40, '2586206040', 'URSULA REFWALU', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82311543151', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(41, '2586206041', 'WENSISLAUS F. MAHUZE', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82156216166', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(42, '2586206042', 'YASINTA P. GINAYANG', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82233484955', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(43, '2586206043', 'YOHANA DIDIMA', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82239965916', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(44, '2586206044', 'YOHANES SOMAHAI', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81237584108', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(45, '2586206045', 'YULIANA JONG', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82189505015', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(46, '2586206046', 'DELVINA KONOMO TOWAK', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '82248504088', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(47, '2586206047', 'MARIA OKTOVINA NOAN', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '85251625060', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(48, '2586206048', 'RITA ADOLFIA TEIROP', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81354013392', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(49, '2586206049', 'DESIDARIUS TIWU', 'S1 Pendidikan Guru Sekolah Dasar', '2025', '81246141172', NULL, '2026-01-19 11:27:07', '2026-01-19 11:27:07'),
(50, '2586208001', 'ABNER OSEW', 'S1 Pendidikan Keagamaan Katolik', '2025', '82189540074', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(51, '2586208002', 'AFNELI NANDUR BALAGAIZE', 'S1 Pendidikan Keagamaan Katolik', '2025', '85252172470', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(52, '2586208003', 'Agustina Tangang', 'S1 Pendidikan Keagamaan Katolik', '2025', '83298967018', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(53, '2586208004', 'AKNITA GRETSIANA ANDEN', 'S1 Pendidikan Keagamaan Katolik', '2025', '85255971157', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(54, '2586208005', 'ALONSIA KAISMA', 'S1 Pendidikan Keagamaan Katolik', '2025', '81343103156', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(55, '2586208006', 'ALOYSIUS PUTERA KELIMUTU MITAK', 'S1 Pendidikan Keagamaan Katolik', '2025', '82155135854', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(56, '2586208007', 'AMELIA ANTONIA AMOTE', 'S1 Pendidikan Keagamaan Katolik', '2025', '82138627787', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(57, '2586208008', 'ANASTASIA RAE', 'S1 Pendidikan Keagamaan Katolik', '2025', '81244306821', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(58, '2586208009', 'ANASTASIA YUMBAIM', 'S1 Pendidikan Keagamaan Katolik', '2025', '81247688696', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(59, '2586208010', 'ANNA PETRONELA LARATMASE', 'S1 Pendidikan Keagamaan Katolik', '2025', '81343173460', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(60, '2586208011', 'BERGITA GRASELA SAMDERUBUN', 'S1 Pendidikan Keagamaan Katolik', '2025', '81352839122', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(61, '2586208012', 'BERLINDA KOISINE', 'S1 Pendidikan Keagamaan Katolik', '2025', '82298578585', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(62, '2586208013', 'DENSIA ROSALINA KWERKUJAI', 'S1 Pendidikan Keagamaan Katolik', '2025', '85344630894', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(63, '2586208014', 'EMANUEL DOKO', 'S1 Pendidikan Keagamaan Katolik', '2025', '81254300752', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(64, '2586208015', 'EMELIANA EMBUN', 'S1 Pendidikan Keagamaan Katolik', '2025', '82341465056', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(65, '2586208016', 'ENJELIKA BAE', 'S1 Pendidikan Keagamaan Katolik', '2025', '82189768537', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(66, '2586208017', 'FERONIKA SUNAHAGI', 'S1 Pendidikan Keagamaan Katolik', '2025', '82336901921', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(67, '2586208018', 'FIKTORIA F. WOMU', 'S1 Pendidikan Keagamaan Katolik', '2025', '85397866003', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(68, '2586208019', 'FLORIANUS YADOHAMANG', 'S1 Pendidikan Keagamaan Katolik', '2025', '81378440764', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(69, '2586208020', 'IGNASIUS YANTU', 'S1 Pendidikan Keagamaan Katolik', '2025', '85285932079', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(70, '2586208021', 'ISIDORUS MOYUWEND', 'S1 Pendidikan Keagamaan Katolik', '2025', '82248518406', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(71, '2586208022', 'JESISKA RIMAYANA SINAGA', 'S1 Pendidikan Keagamaan Katolik', '2025', '82274040716', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(72, '2586208023', 'KAIFAS JUENEM', 'S1 Pendidikan Keagamaan Katolik', '2025', '8134302085', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(73, '2586208024', 'KRISTINA SERAN', 'S1 Pendidikan Keagamaan Katolik', '2025', '82191525589', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(74, '2586208025', 'LINUS MISA', 'S1 Pendidikan Keagamaan Katolik', '2025', '82152246751', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(75, '2586208026', 'LUSIANA TANE YADOHAMANG', 'S1 Pendidikan Keagamaan Katolik', '2025', '85268187673', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(76, '2586208027', 'MAGDALENA SULASTIKA SAMALOISA', 'S1 Pendidikan Keagamaan Katolik', '2025', '82274040716', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(77, '2586208028', 'MAIKEL', 'S1 Pendidikan Keagamaan Katolik', '2025', '85251626712', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(78, '2586208029', 'MAIKEL FASAK', 'S1 Pendidikan Keagamaan Katolik', '2025', '81248756115', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(79, '2586208030', 'MARIA ELISABET KAIZE', 'S1 Pendidikan Keagamaan Katolik', '2025', '85397324568', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(80, '2586208031', 'MARIA IMAKULATA KAIZE', 'S1 Pendidikan Keagamaan Katolik', '2025', '82231433091', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(81, '2586208032', 'MARIA MAGDALA TENIWUT', 'S1 Pendidikan Keagamaan Katolik', '2025', '81248782965', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(82, '2586208033', 'MARIA SEFANIA MAMBU YOLMEN', 'S1 Pendidikan Keagamaan Katolik', '2025', '82396495103', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(83, '2586208034', 'MARIA SINDRIANI LENA LAKI', 'S1 Pendidikan Keagamaan Katolik', '2025', '81321742824', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(84, '2586208035', 'MARKUS FRANSISKO WAAP', 'S1 Pendidikan Keagamaan Katolik', '2025', '81389349517', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(85, '2586208036', 'MARSELUS NGGUREBA', 'S1 Pendidikan Keagamaan Katolik', '2025', '82348297557', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(86, '2586208037', 'MARTHA NURMALAY', 'S1 Pendidikan Keagamaan Katolik', '2025', '81248086449', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(87, '2586208038', 'MIKAELA JARU', 'S1 Pendidikan Keagamaan Katolik', '2025', '85257098489', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(88, '2586208039', 'MONIKA BURON LEBA', 'S1 Pendidikan Keagamaan Katolik', '2025', '82262468008', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(89, '2586208040', 'MONIKA WIPYA', 'S1 Pendidikan Keagamaan Katolik', '2025', '85245569399', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(90, '2586208041', 'NOBERTA MAGO', 'S1 Pendidikan Keagamaan Katolik', '2025', '85251620780', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(91, '2586208042', 'PASKALINA V. WALEWOWAN', 'S1 Pendidikan Keagamaan Katolik', '2025', '81316456347', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(92, '2586208043', 'PAULINA WENEHENUBUN', 'S1 Pendidikan Keagamaan Katolik', '2025', '81229693675', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(93, '2586208044', 'PETRONELA SARLITA SUNAHAGI', 'S1 Pendidikan Keagamaan Katolik', '2025', '81229964525', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(94, '2586208045', 'SARLOTHA SILETTY', 'S1 Pendidikan Keagamaan Katolik', '2025', '85254217625', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(95, '2586208047', 'THEODOTA TITIRLOLOBY', 'S1 Pendidikan Keagamaan Katolik', '2025', '82297841991', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(96, '2586208048', 'THERESIA TAWURUTUBUN', 'S1 Pendidikan Keagamaan Katolik', '2025', '85213510334', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(97, '2586208049', 'YESIA ROSARI STEPHANI LONGA', 'S1 Pendidikan Keagamaan Katolik', '2025', '85243033723', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(98, '2586208050', 'YOHANES JAUN', 'S1 Pendidikan Keagamaan Katolik', '2025', '82162879065', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(99, '2586208051', 'YORIS BERWIR', 'S1 Pendidikan Keagamaan Katolik', '2026', '82325666507', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(100, '2586208052', 'YULIANA BERNADETA KAMEROP', 'S1 Pendidikan Keagamaan Katolik', '2027', '82189166749', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(101, '2586208053', 'YULIANA MAIMPA', 'S1 Pendidikan Keagamaan Katolik', '2028', '81376950557', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(102, '2586208054', 'YULIANUS K. PASIM', 'S1 Pendidikan Keagamaan Katolik', '2029', '82280464351', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(103, '2586208055', 'YULIANUS M M WAFA', 'S1 Pendidikan Keagamaan Katolik', '2030', '82199225730', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(104, '2586208056', 'YULITA VERA KONDATE', 'S1 Pendidikan Keagamaan Katolik', '2031', '81240258960', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(105, '2586208057', 'YULIUS BRIVAN T. LENA', 'S1 Pendidikan Keagamaan Katolik', '2032', '', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(106, '2586208058', 'FERDINANDUS TIO SARE', 'S1 Pendidikan Keagamaan Katolik', '2033', '82195231210', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(107, '2586208059', 'AGUSTINA PASKALINA WELAFUBUN', 'S1 Pendidikan Keagamaan Katolik', '2034', '81356613223', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(108, '2586208060', 'KATARINA KUPET', 'S1 Pendidikan Keagamaan Katolik', '2035', '85251619856', NULL, '2026-01-19 11:29:04', '2026-01-19 11:29:04'),
(109, '2486208001', 'AGUSTA SIKANWAN', 'S1 Pendidikan Keagamaan Katolik', '2024', '82349490139', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(110, '2486208002', 'ALECXANDER BAHASI', 'S1 Pendidikan Keagamaan Katolik', '2024', '82285102821', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(111, '2486208003', 'ALOISYA MARDIANI YETEROK', 'S1 Pendidikan Keagamaan Katolik', '2024', '85213675346', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(112, '2486208004', 'ANA DIANA BEBA SUMAGI', 'S1 Pendidikan Keagamaan Katolik', '2024', '81344773790', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(113, '2486208005', 'ANASTASIA LISTA', 'S1 Pendidikan Keagamaan Katolik', '2024', '82211064936', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(114, '2486208006', 'ANTONIA Y. WAMTE', 'S1 Pendidikan Keagamaan Katolik', '2024', '82134698625', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(115, '2486208007', 'BASTIANUS DELO', 'S1 Pendidikan Keagamaan Katolik', '2024', '82199116783', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(116, '2486208008', 'BEATUS DAMAR ASO', 'S1 Pendidikan Keagamaan Katolik', '2024', '82278649096', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(117, '2486208009', 'BENYAMIN KARLOS DEMELO TAF MABUR', 'S1 Pendidikan Keagamaan Katolik', '2024', '81316019567', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(118, '2486208010', 'BERTOLD KEVIN BEDI MAKING', 'S1 Pendidikan Keagamaan Katolik', '2024', '82198821859', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(119, '2486208011', 'CLAUDIA IRABAQ KATIMU', 'S1 Pendidikan Keagamaan Katolik', '2024', '', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(120, '2486208012', 'DAMIANA DAM YERMOGOIN', 'S1 Pendidikan Keagamaan Katolik', '2024', '81343246473', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(121, '2486208013', 'DOLFINA WULKI KAIZE', 'S1 Pendidikan Keagamaan Katolik', '2024', '82197650907', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(122, '2486208014', 'DOMINIKUS KOMAKAIMU', 'S1 Pendidikan Keagamaan Katolik', '2024', '81314642749', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(123, '2486208015', 'DORTEA KOWETMIP', 'S1 Pendidikan Keagamaan Katolik', '2024', '81243392998', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(124, '2486208016', 'DORTEA MARLINA KOLIKON KAMIM', 'S1 Pendidikan Keagamaan Katolik', '2024', '81248148563', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(125, '2486208017', 'EDI BUIYI', 'S1 Pendidikan Keagamaan Katolik', '2024', '81356519150', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(126, '2486208018', 'EKMUNDUS YAGOYAMU', 'S1 Pendidikan Keagamaan Katolik', '2024', '81277838233', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(127, '2486208019', 'EKY KORISEN', 'S1 Pendidikan Keagamaan Katolik', '2024', '82248517541', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(128, '2486208020', 'ELISABET', 'S1 Pendidikan Keagamaan Katolik', '2024', '82210124483', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(129, '2486208021', 'EMANUEL TIO WALTEN', 'S1 Pendidikan Keagamaan Katolik', '2024', '82248898074', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(130, '2486208022', 'ENGGELINA IMAKULATA BAPAIMU', 'S1 Pendidikan Keagamaan Katolik', '2024', '82248500650', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(131, '2486208023', 'FRANSISKA NISWOTAR', 'S1 Pendidikan Keagamaan Katolik', '2024', '81240291395', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(132, '2486208024', 'FRANSISKUS FREBONSIUS FEDRIK', 'S1 Pendidikan Keagamaan Katolik', '2024', '82145304695', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(133, '2486208025', 'FREDERIKA M. DUMBON', 'S1 Pendidikan Keagamaan Katolik', '2024', '85244817746', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(134, '2486208026', 'GEMA DUAGE', 'S1 Pendidikan Keagamaan Katolik', '2024', '85254143029', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(135, '2486208027', 'HELENA P PAYOMBAI', 'S1 Pendidikan Keagamaan Katolik', '2024', '82198148475', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(136, '2486208028', 'HERLINA YIMSI', 'S1 Pendidikan Keagamaan Katolik', '2024', '81293463060', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(137, '2486208029', 'JANUARIA WIKI', 'S1 Pendidikan Keagamaan Katolik', '2024', '82249123702', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(138, '2486208030', 'JENIKE FANESA RUBAN', 'S1 Pendidikan Keagamaan Katolik', '2024', '82191249527', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(139, '2486208031', 'KRISTIANA REFOLINA YIBIM', 'S1 Pendidikan Keagamaan Katolik', '2024', '85398789509', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(140, '2486208032', 'KRISTINA UKAGO', 'S1 Pendidikan Keagamaan Katolik', '2024', '82191320396', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(141, '2486208033', 'LAURA VAVUU', 'S1 Pendidikan Keagamaan Katolik', '2024', '82397713927', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(142, '2486208034', 'LONITA WARE', 'S1 Pendidikan Keagamaan Katolik', '2024', '81248617498', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(143, '2486208035', 'MAGDALENA MERLITA', 'S1 Pendidikan Keagamaan Katolik', '2024', '82267878599', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(144, '2486208036', 'MARIA DANDA WARAK', 'S1 Pendidikan Keagamaan Katolik', '2024', '81342839669', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(145, '2486208037', 'MARIA DARISTA', 'S1 Pendidikan Keagamaan Katolik', '2024', '82199547967', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(146, '2486208038', 'MARIA FLORENCITA ADISTA', 'S1 Pendidikan Keagamaan Katolik', '2024', '85215885916', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(147, '2486208039', 'MARIA KABINUBUN', 'S1 Pendidikan Keagamaan Katolik', '2024', '81343488745', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(148, '2486208040', 'MARIA LOIS GRATIA HELJANAN', 'S1 Pendidikan Keagamaan Katolik', '2024', '81336640162', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(149, '2486208042', 'MARIA ONGG', 'S1 Pendidikan Keagamaan Katolik', '2024', '82198852952', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(150, '2486208043', 'MARIA S. TETHOOL', 'S1 Pendidikan Keagamaan Katolik', '2024', '', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(151, '2486208044', 'MARIA THERESIA TAKNDARLERE', 'S1 Pendidikan Keagamaan Katolik', '2024', '81240752720', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(152, '2486208045', 'MARTINA TENG', 'S1 Pendidikan Keagamaan Katolik', '2024', '82145502039', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(153, '2486208046', 'MELKIOR ALVIANO WALLONG', 'S1 Pendidikan Keagamaan Katolik', '2024', '', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(154, '2486208047', 'MERLINA AWUOP TUTUK', 'S1 Pendidikan Keagamaan Katolik', '2024', '81321847607', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(155, '2486208048', 'NATALIA. KRISTIANI. SIRAN', 'S1 Pendidikan Keagamaan Katolik', '2024', '81248486823', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(156, '2486208049', 'NATALIA SERLEY WALEWOWAN', 'S1 Pendidikan Keagamaan Katolik', '2024', '81344016714', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(157, '2486208050', 'NOVITA FEDENSIA ANGGOM', 'S1 Pendidikan Keagamaan Katolik', '2024', '81226692350', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(158, '2486208051', 'OKTOFIANUS KORISEN', 'S1 Pendidikan Keagamaan Katolik', '2024', '82194986834', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(159, '2486208052', 'OLIVA DOKO', 'S1 Pendidikan Keagamaan Katolik', '2024', '85212551774', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(160, '2486208053', 'PASIFIKA ASEK', 'S1 Pendidikan Keagamaan Katolik', '2024', '82188696047', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(161, '2486208054', 'PASIFIKA POSAN', 'S1 Pendidikan Keagamaan Katolik', '2024', '85213234279', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(162, '2486208055', 'PETRONELA ELISABET ERO', 'S1 Pendidikan Keagamaan Katolik', '2024', '82198973447', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(163, '2486208056', 'REMUNDUS MAYO YADOHAMANG', 'S1 Pendidikan Keagamaan Katolik', '2024', '85219714556', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(164, '2486208057', 'RIVALDY ARIANTO ROBERTH MARAMIS', 'S1 Pendidikan Keagamaan Katolik', '2024', '81240285870', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(165, '2486208058', 'ROMI RICHARDUS SIWASA', 'S1 Pendidikan Keagamaan Katolik', '2024', '81389346751', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(166, '2486208059', 'ROSALINA WALBURGA KANGGIN', 'S1 Pendidikan Keagamaan Katolik', '2024', '81248462335', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(167, '2486208060', 'SAUL AMANPEN', 'S1 Pendidikan Keagamaan Katolik', '2024', '82199845287', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(168, '2486208061', 'SAVERIA BASAGAI', 'S1 Pendidikan Keagamaan Katolik', '2024', '82221198852', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(169, '2486208063', 'SISILIA FIFE BUTAFE', 'S1 Pendidikan Keagamaan Katolik', '2024', '', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(170, '2486208064', 'STEFANUS MABUR', 'S1 Pendidikan Keagamaan Katolik', '2024', '82198638328', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(171, '2486208065', 'SUSANA YOHANA FOFID', 'S1 Pendidikan Keagamaan Katolik', '2024', '81240717857', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(172, '2486208066', 'THERESIA LUMIN WATURU', 'S1 Pendidikan Keagamaan Katolik', '2024', '82198336670', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(173, '2486208067', 'THERESIA SANGGI', 'S1 Pendidikan Keagamaan Katolik', '2024', '85399824622', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(174, '2486208068', 'VERONIKA WAMBITMAN', 'S1 Pendidikan Keagamaan Katolik', '2024', '82220792096', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(175, '2486208069', 'VIKO SAMDERUBUN', 'S1 Pendidikan Keagamaan Katolik', '2024', '81248649084', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(176, '2486208070', 'VRILA KARISMA RUBAN', 'S1 Pendidikan Keagamaan Katolik', '2024', '81295705313', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(177, '2486208071', 'YAKOBUS IRAWA', 'S1 Pendidikan Keagamaan Katolik', '2024', '82143525274', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(178, '2486208072', 'YOHANES IRVANTO', 'S1 Pendidikan Keagamaan Katolik', '2024', '82315547328', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(179, '2486208073', 'YULIANA M. I. SUMAGHAI', 'S1 Pendidikan Keagamaan Katolik', '2024', '82311132905', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(180, '2486208074', 'YUSTINA SARI BLOJAI', 'S1 Pendidikan Keagamaan Katolik', '2024', '82239324101', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(181, '2486208075', 'SONYA IKAMTAHAI', 'S1 Pendidikan Keagamaan Katolik', '2024', '82241347545', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(182, '2486208076', 'VALERIANA RESOK', 'S1 Pendidikan Keagamaan Katolik', '2024', '81220787772', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(183, '2486208077', 'MAKARIUS DOKO', 'S1 Pendidikan Keagamaan Katolik', '2024', '82198323321', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(184, '2486208078', 'MARCELINO DEVAN PUTRA', 'S1 Pendidikan Keagamaan Katolik', '2024', '81240095387', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(185, '2486208080', 'CINDI PATRICIA MASRI', 'S1 Pendidikan Keagamaan Katolik', '2024', '85609457349', NULL, '2026-01-19 11:30:40', '2026-01-19 11:30:40'),
(186, '2386208001', 'ADRIANA LAMAK RAJA', 'S1 Pendidikan Keagamaan Katolik', '2023', '81343148620', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(187, '2386208002', 'AGUSTINA ANJELA DETTU', 'S1 Pendidikan Keagamaan Katolik', '2023', '82346942835', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(188, '2386208003', 'AMATUS KAIZE', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(189, '2386208004', 'ANAKLETUSS GERGORIUS YAME', 'S1 Pendidikan Keagamaan Katolik', '2023', '85241211702', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(190, '2386208005', 'ANJELA DE MERICI RIKU', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(191, '2386208008', 'ELISABETH TERECHITA', 'S1 Pendidikan Keagamaan Katolik', '2023', '82261126852', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(192, '2386208010', 'FALENTINA KAIZE', 'S1 Pendidikan Keagamaan Katolik', '2023', '81248842581', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(193, '2386208011', 'FRANSINA SIKTEUBUN', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(194, '2386208012', 'GABRIEL NGAMELUBUN', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(195, '2386208015', 'HIRONIMUS KUMNI KWAMTAGHAI', 'S1 Pendidikan Keagamaan Katolik', '2023', '81385395724', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(196, '2386208016', 'IMELDA OGENAKON WOMBON', 'S1 Pendidikan Keagamaan Katolik', '2023', '81240171910', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(197, '2386208017', 'JAIRUS SEMI KAMOGOU', 'S1 Pendidikan Keagamaan Katolik', '2023', '81216398523', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(198, '2386208018', 'JACOBUS A. K. JEUJANAN', 'S1 Pendidikan Keagamaan Katolik', '2023', '81240613129', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(199, '2386208019', 'JIMI J. K. YUKUSEN', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(200, '2386208020', 'KATARINA IROK', 'S1 Pendidikan Keagamaan Katolik', '2023', '82193664516', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(201, '2386208021', 'KATHARINA KEWA TARANPIRAQ', 'S1 Pendidikan Keagamaan Katolik', '2023', '81343400735', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(202, '2386208024', 'MARIA GORETI KAMOGOU', 'S1 Pendidikan Keagamaan Katolik', '2023', '82115517244', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(203, '2386208025', 'MARIA IRMINA BHALU', 'S1 Pendidikan Keagamaan Katolik', '2023', '82230496083', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(204, '2386208026', 'MARIANA KOWUNUK', 'S1 Pendidikan Keagamaan Katolik', '2023', '81342863850', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(205, '2386208029', 'MINCE DABI', 'S1 Pendidikan Keagamaan Katolik', '2023', '82110771304', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(206, '2386208030', 'ODILIA WAJAMO YERMOGOIN', 'S1 Pendidikan Keagamaan Katolik', '2023', '82187421685', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(207, '2386208031', 'PRICILIANO GUAN DAMIAN C. BANJUMARA', 'S1 Pendidikan Keagamaan Katolik', '2023', '81248547185', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(208, '2386208032', 'SIMON PETRUS YAGUYAMU', 'S1 Pendidikan Keagamaan Katolik', '2023', '81399471645', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(209, '2386208033', 'STEVANUS ALEXSANDRO RENWARIN', 'S1 Pendidikan Keagamaan Katolik', '2023', '82397059664', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(210, '2386208034', 'THERESIA TADI MUKU', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(211, '2386208035', 'WELMINCE NAOLYO MAHUSE', 'S1 Pendidikan Keagamaan Katolik', '2023', '81240406457', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(212, '2386208036', 'YACOBUS CHRISTIAN SANGKEK', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(213, '2386208037', 'YEREMIAS ABY', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(214, '2386208039', 'YOSEP PATRISIUS KESAHAI', 'S1 Pendidikan Keagamaan Katolik', '2023', '81296551387', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(215, '2386208040', 'YOSINA K. M. BEJAI', 'S1 Pendidikan Keagamaan Katolik', '2023', '81343251809', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(216, '2386208041', 'YULIA DJAMDJIK', 'S1 Pendidikan Keagamaan Katolik', '2023', '81344243504', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(217, '2386208042', 'YULIANA K. SAMKAKAI', 'S1 Pendidikan Keagamaan Katolik', '2023', '81343251334', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(218, '2386208043', 'YULIUS MAHUDU CHOBUMUN', 'S1 Pendidikan Keagamaan Katolik', '2023', '81247045041', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(219, '2386208044', 'YULIUS YANAREMA', 'S1 Pendidikan Keagamaan Katolik', '2023', '81247037752', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(220, '2386208047', 'ELSIANA BONDAI KOSNAN', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(221, '2386208048', 'Silvana Salen Yamaka', 'S1 Pendidikan Keagamaan Katolik', '2023', '82125523831', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(222, '2386208049', 'ADELA NATALIA EKU BURA', 'S1 Pendidikan Keagamaan Katolik', '2023', '82144790767', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(223, '2386208050', 'ADRIANA N.I. KAISE', 'S1 Pendidikan Keagamaan Katolik', '2023', '82199965794', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(224, '2386208051', 'AGUSTINA ABUK', 'S1 Pendidikan Keagamaan Katolik', '2023', '81387142013', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(225, '2386208052', 'ANTONIA YIBIN', 'S1 Pendidikan Keagamaan Katolik', '2023', '81248617247', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(226, '2386208053', 'ANTONIUS TURIARIA', 'S1 Pendidikan Keagamaan Katolik', '2023', '82823295513', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(227, '2386208054', 'ELISABETH FEBRIANI AMOTEY', 'S1 Pendidikan Keagamaan Katolik', '2023', '81240204936', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(228, '2386208055', 'ELYAS JACOBUS WALTEN', 'S1 Pendidikan Keagamaan Katolik', '2023', '82220257349', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(229, '2386208058', 'HERMAN AWI', 'S1 Pendidikan Keagamaan Katolik', '2023', '82143525274', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(230, '2386208059', 'IMMANUEL TRITUNGGAL IMBANOP', 'S1 Pendidikan Keagamaan Katolik', '2023', '85243196949', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(231, '2386208061', 'LILI FERDINANDA KUPKUDE', 'S1 Pendidikan Keagamaan Katolik', '2023', '81248548447', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(232, '2386208062', 'MAKXIMELIANUS AKY', 'S1 Pendidikan Keagamaan Katolik', '2023', '82198117309', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(233, '2386208064', 'MARIA MAGDALENA ENGGEP', 'S1 Pendidikan Keagamaan Katolik', '2023', '82250833262', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(234, '2386208066', 'PERENS ENOS YANYAAN', 'S1 Pendidikan Keagamaan Katolik', '2023', '85241211507', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(235, '2386208067', 'RIA NATALIA KAEY', 'S1 Pendidikan Keagamaan Katolik', '2023', '82248393456', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(236, '2386208070', 'ROSLITA OWIYMANAM', 'S1 Pendidikan Keagamaan Katolik', '2023', '81248662134', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(237, '2386208071', 'SHOPIA CHINDIRANI REO LELAONA', 'S1 Pendidikan Keagamaan Katolik', '2023', '81337060912', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(238, '2386208072', 'THERESIA TITIRLOLOBI', 'S1 Pendidikan Keagamaan Katolik', '2023', '81240040656', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(239, '2386208073', 'DIDIMUS BUIPU', 'S1 Pendidikan Keagamaan Katolik', '2023', '81240775457', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(240, '2386208074', 'Hironimus Khabe', 'S1 Pendidikan Keagamaan Katolik', '2023', '81318542049', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(241, '2386208075', 'MARTINUS DAMIANUS GUBA KWAMTAHAI', 'S1 Pendidikan Keagamaan Katolik', '2023', '82223367645', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(242, '2386208077', 'ELISABETH BEWA TOLOK', 'S1 Pendidikan Keagamaan Katolik', '2023', '81336350495', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(243, '2386208078', 'KATARINA LEJA KOTEN', 'S1 Pendidikan Keagamaan Katolik', '2023', '82146254376', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(244, '2386208079', 'YULIANUS KIAAF', 'S1 Pendidikan Keagamaan Katolik', '2023', '82141567547', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(245, '2386208080', 'HENNI ANIKE YELIPELE', 'S1 Pendidikan Keagamaan Katolik', '2023', '', NULL, '2026-01-19 11:31:58', '2026-01-19 11:31:58'),
(246, '2202001', 'ADOLFINA NAWANITA', 'S1 Pendidikan Keagamaan Katolik', '2022', '81240446294', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(247, '2202002', 'AGUS YONATAN W. BENGGIAN', 'S1 Pendidikan Keagamaan Katolik', '2022', '85215907378', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(248, '2202004', 'ANITA YUGUSAN', 'S1 Pendidikan Keagamaan Katolik', '2022', '85236079843', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(249, '2202005', 'APOLINARIS RAHAWARIN', 'S1 Pendidikan Keagamaan Katolik', '2022', '81247328730', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(250, '2202007', 'BLASIUS MOA DEDILADO', 'S1 Pendidikan Keagamaan Katolik', '2022', '82199965283', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(251, '2202009', 'EMELIANA TUKAN', 'S1 Pendidikan Keagamaan Katolik', '2022', '81214055351', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(252, '2202011', 'MARIA MARSELINA KEWOT', 'S1 Pendidikan Keagamaan Katolik', '2022', '82259867672', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(253, '2202012', 'FLESIA SALAY', 'S1 Pendidikan Keagamaan Katolik', '2022', '81369883915', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(254, '2202013', 'FRANSISKA ITLAY', 'S1 Pendidikan Keagamaan Katolik', '2022', '82110771304', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(255, '2202014', 'FRANSISKUS FIKTOR MANGKA', 'S1 Pendidikan Keagamaan Katolik', '2022', '81248532813', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(256, '2202015', 'FRANSISKUS XAVERIUS GEBZE', 'S1 Pendidikan Keagamaan Katolik', '2022', '', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(257, '2202018', 'GERMANA TIUNG', 'S1 Pendidikan Keagamaan Katolik', '2022', '82239375407', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(258, '2202019', 'GRESTINA MERSA GIYAI', 'S1 Pendidikan Keagamaan Katolik', '2022', '81242201448', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(259, '2202021', 'HIRONIMUS ERO', 'S1 Pendidikan Keagamaan Katolik', '2022', '81343245828', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(260, '2202023', 'IGNASIUS GUAM', 'S1 Pendidikan Keagamaan Katolik', '2022', '81248577130', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(261, '2202026', 'KOSTANTINUS METAWE', 'S1 Pendidikan Keagamaan Katolik', '2022', '81247943140', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(262, '2202028', 'LAURENSIUS KUNDUMUYE', 'S1 Pendidikan Keagamaan Katolik', '2022', '82139765084', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(263, '2202029', 'LUSIANA JUMBAIMO', 'S1 Pendidikan Keagamaan Katolik', '2022', '81240813433', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(264, '2202030', 'MAGDALENA YURUM KANDAM', 'S1 Pendidikan Keagamaan Katolik', '2022', '82144806877', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(265, '2202031', 'MARIA DELVINSIANA DEA', 'S1 Pendidikan Keagamaan Katolik', '2022', '81248464035', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(266, '2202032', 'MARIA MARGARETA NONA VANI', 'S1 Pendidikan Keagamaan Katolik', '2022', '85244038456', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(267, '2202033', 'MARIANUS HEATUBUN', 'S1 Pendidikan Keagamaan Katolik', '2022', '81242272037', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(268, '2202034', 'MARIUS MARAWA', 'S1 Pendidikan Keagamaan Katolik', '2022', '82256969719', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(269, '2202035', 'MARTA YABON', 'S1 Pendidikan Keagamaan Katolik', '2022', '82398490690', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(270, '2202036', 'MELANIA SAMDERUBUN', 'S1 Pendidikan Keagamaan Katolik', '2022', '82238945077', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(271, '2202037', 'MELTIDIS ARSANTI', 'S1 Pendidikan Keagamaan Katolik', '2022', '82248503388', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(272, '2202038', 'MIKHELA BEBU KOSNAN', 'S1 Pendidikan Keagamaan Katolik', '2022', '85362729669', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(273, '2202039', 'MODESTA WIHELMINA YAAS', 'S1 Pendidikan Keagamaan Katolik', '2022', '82398884379', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(274, '2202040', 'NATALIS PETRUS NAUCE', 'S1 Pendidikan Keagamaan Katolik', '2022', '81340952751', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(275, '2202041', 'NORBERTUS ANGGUNIP', 'S1 Pendidikan Keagamaan Katolik', '2022', '82198324687', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(276, '2202045', 'PIA YUSTINA KABINUBUN', 'S1 Pendidikan Keagamaan Katolik', '2022', '82239354416', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(277, '2202046', 'RICARDA DEWAKOP', 'S1 Pendidikan Keagamaan Katolik', '2022', '81248413496', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(278, '2202047', 'RONALDUS ANDAP', 'S1 Pendidikan Keagamaan Katolik', '2022', '82397334099', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(279, '2202050', 'SEBASTIANA SAMDERUBUN', 'S1 Pendidikan Keagamaan Katolik', '2022', '85251997984', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(280, '2202051', 'SOFIA FEBRIANI WUA', 'S1 Pendidikan Keagamaan Katolik', '2022', '81364081064', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(281, '2202052', 'THOMAS KAURIA', 'S1 Pendidikan Keagamaan Katolik', '2022', '81240074665', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(282, '2202054', 'VERONIKA WALENG TENAWAHANG', 'S1 Pendidikan Keagamaan Katolik', '2022', '81252289170', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(283, '2202055', 'WILHELMUS TEMORIA', 'S1 Pendidikan Keagamaan Katolik', '2022', '81248492533', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(284, '2202056', 'YOHANA MARIANA MABUR', 'S1 Pendidikan Keagamaan Katolik', '2022', '81276351236', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(285, '2202057', 'YOSEFA SABINA WITI', 'S1 Pendidikan Keagamaan Katolik', '2022', '82279940850', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(286, '2202058', 'YULIANCE WALILO', 'S1 Pendidikan Keagamaan Katolik', '2022', '81229557460', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(287, '2202061', 'PETRUS KALIKIMBIAN', 'S1 Pendidikan Keagamaan Katolik', '2022', '81344051729', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(288, '2202062', 'MELKIANUS POYI', 'S1 Pendidikan Keagamaan Katolik', '2022', '81344798052', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(289, '2202063', 'ANTONIUS NATALIS UTAY', 'S1 Pendidikan Keagamaan Katolik', '2022', '81217599240', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(290, '2202064', 'TIMOTIUS CUANDUKA', 'S1 Pendidikan Keagamaan Katolik', '2022', '', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(291, '2202066', 'MARIA SEIGI', 'S1 Pendidikan Keagamaan Katolik', '2022', '82275900780', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(292, '2202068', 'DOROTHEA ONJAI', 'S1 Pendidikan Keagamaan Katolik', '2022', '', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(293, '1902019', 'Iginasius Pasim', 'S1 Pendidikan Keagamaan Katolik', '2019', '85244438526', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(294, '1902029', 'Maria Ratu Korisen', 'S1 Pendidikan Keagamaan Katolik', '2019', '85254184384', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(295, '2002002', 'Agustinus S. Bumagi', 'S1 Pendidikan Keagamaan Katolik', '2020', '81248330552', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(296, '2002015', 'Kristianus Sumahagi', 'S1 Pendidikan Keagamaan Katolik', '2020', '81247792143', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(297, '2002031', 'Gelarda Wota Aga', 'S1 Pendidikan Keagamaan Katolik', '2020', '85244908875', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(298, '2002035', 'Fermensia Kanisiwag Ndiken', 'S1 Pendidikan Keagamaan Katolik', '2020', '81342859731', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(299, '2102005', 'Aprilia Femilia Soreyar', 'S1 Pendidikan Keagamaan Katolik', '2021', '81252360650', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(300, '2102008', 'Faustina Yanondoyowop', 'S1 Pendidikan Keagamaan Katolik', '2021', '81248463935', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(301, '2102010', 'Fransiska Selfiana', 'S1 Pendidikan Keagamaan Katolik', '2021', '81240514643', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(302, '2102014', 'Jumantris Tobias Mesak', 'S1 Pendidikan Keagamaan Katolik', '2021', '82199055227', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(303, '2102020', 'Modesta Sitaha Koro Aun', 'S1 Pendidikan Keagamaan Katolik', '2021', '81247058369', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(304, '2102023', 'Oliva Ndaro', 'S1 Pendidikan Keagamaan Katolik', '2021', '81293015879', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(305, '2102025', 'Rikardus Gowo', 'S1 Pendidikan Keagamaan Katolik', '2021', '85254607833', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52'),
(306, '2102029', 'Simon Petrus Cikara', 'S1 Pendidikan Keagamaan Katolik', '2021', '81247953859', NULL, '2026-01-19 11:33:52', '2026-01-19 11:33:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `nomor_surat_counter`
--

CREATE TABLE `nomor_surat_counter` (
  `id` int(11) NOT NULL,
  `tahun` year(4) NOT NULL,
  `counter` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran_denda_detail`
--

CREATE TABLE `pembayaran_denda_detail` (
  `id` int(11) NOT NULL,
  `pengembalian_id` int(11) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `nominal` int(11) NOT NULL,
  `metode_pembayaran` enum('cash','transfer','tagihan_studi','waive') DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pembayaran_denda_detail`
--

INSERT INTO `pembayaran_denda_detail` (`id`, `pengembalian_id`, `tanggal_bayar`, `nominal`, `metode_pembayaran`, `keterangan`, `created_at`) VALUES
(1, 2, '2026-01-20', 2000, 'cash', '', '2026-01-20 00:22:34'),
(2, 5, '2026-01-20', 7000, 'tagihan_studi', '', '2026-01-20 01:39:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `kode_peminjaman` varchar(50) NOT NULL,
  `jenis_peminjam` enum('mahasiswa','dosen') NOT NULL,
  `peminjam_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','diperpanjang','dikembalikan','terlambat') DEFAULT 'dipinjam',
  `denda` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `kode_peminjaman`, `jenis_peminjam`, `peminjam_id`, `buku_id`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `tanggal_kembali`, `status`, `denda`, `created_at`, `updated_at`) VALUES
(4, 'PJM202601195509', 'mahasiswa', 50, 24, '2026-01-13', '2026-01-27', NULL, 'diperpanjang', 0, '2026-01-19 13:05:21', '2026-01-19 22:50:59'),
(5, 'PJM202601203855', 'mahasiswa', 222, 23, '2026-01-11', '2026-01-18', '2026-01-20', 'dikembalikan', 2000, '2026-01-19 22:51:51', '2026-01-20 00:22:34'),
(6, 'PJM202601201766', 'dosen', 1, 11, '2026-01-14', '2026-01-21', '2026-01-20', 'dikembalikan', 0, '2026-01-19 22:52:13', '2026-01-20 00:22:19'),
(7, 'PJM202601208098', 'mahasiswa', 186, 28, '2026-01-06', '2026-01-13', '2026-01-20', 'dikembalikan', 7000, '2026-01-19 22:56:39', '2026-01-20 00:26:12'),
(8, 'PJM202601207590', 'mahasiswa', 107, 11, '2026-01-06', '2026-01-13', NULL, 'terlambat', 0, '2026-01-20 00:27:04', '2026-01-20 00:27:04'),
(9, 'PJM202601205325', 'mahasiswa', 107, 16, '2026-01-06', '2026-01-13', '2026-01-20', 'dikembalikan', 7000, '2026-01-20 00:27:04', '2026-01-20 01:39:04'),
(10, 'PJM202601208875', 'mahasiswa', 223, 12, '2026-01-20', '2026-01-27', '2026-01-20', 'dikembalikan', 0, '2026-01-20 00:27:27', '2026-01-20 01:17:33'),
(11, 'PJM202601204305', 'mahasiswa', 111, 18, '2026-01-11', '2026-01-18', NULL, 'terlambat', 0, '2026-01-20 00:27:40', '2026-01-20 00:27:41'),
(12, 'PJM202601208800', 'mahasiswa', 1, 10, '2026-01-20', '2026-01-27', NULL, 'dipinjam', 0, '2026-01-20 04:01:58', '2026-01-20 04:01:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id` int(11) NOT NULL,
  `peminjaman_id` int(11) NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `keterlambatan_hari` int(11) DEFAULT 0,
  `denda` int(11) DEFAULT 0,
  `denda_dibayar` int(11) DEFAULT 0,
  `uang_kembali` int(11) DEFAULT 0,
  `sisa_denda` int(11) DEFAULT 0,
  `metode_pembayaran` enum('cash','transfer','tagihan_studi','waive') DEFAULT 'cash',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pengembalian`
--

INSERT INTO `pengembalian` (`id`, `peminjaman_id`, `tanggal_kembali`, `keterlambatan_hari`, `denda`, `denda_dibayar`, `uang_kembali`, `sisa_denda`, `metode_pembayaran`, `keterangan`, `created_at`) VALUES
(1, 6, '2026-01-20', 0, 0, 0, 0, 0, 'cash', '', '2026-01-20 00:22:19'),
(2, 5, '2026-01-20', 2, 2000, 2000, 0, 0, 'cash', '', '2026-01-20 00:22:34'),
(3, 7, '2026-01-20', 7, 7000, 0, 0, 0, 'waive', 'Bencana Alam', '2026-01-20 00:26:12'),
(4, 10, '2026-01-20', 0, 0, 0, 0, 0, 'cash', '', '2026-01-20 01:17:33'),
(5, 9, '2026-01-20', 7, 7000, 7000, 0, 0, 'tagihan_studi', '', '2026-01-20 01:39:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `perpanjangan`
--

CREATE TABLE `perpanjangan` (
  `id` int(11) NOT NULL,
  `peminjaman_id` int(11) NOT NULL,
  `tanggal_perpanjangan` date NOT NULL,
  `jatuh_tempo_lama` date NOT NULL,
  `jatuh_tempo_baru` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `perpanjangan`
--

INSERT INTO `perpanjangan` (`id`, `peminjaman_id`, `tanggal_perpanjangan`, `jatuh_tempo_lama`, `jatuh_tempo_baru`, `created_at`) VALUES
(1, 4, '2026-01-20', '2026-01-20', '2026-01-27', '2026-01-19 22:50:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `surat_keterangan`
--

CREATE TABLE `surat_keterangan` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(50) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `jenis_surat` enum('UAS','PPA') NOT NULL COMMENT 'UAS = Ujian Akhir Semester, PPA = Penilaian Pembelajaran Akhir',
  `tanggal_terbit` date NOT NULL,
  `tahun_periode` year(4) NOT NULL COMMENT 'Untuk tracking reset nomor surat',
  `status` enum('terbit','dibatalkan') NOT NULL DEFAULT 'terbit',
  `catatan` text DEFAULT NULL COMMENT 'Catatan admin, misal: override tunggakan karena alasan X',
  `admin_id` int(11) DEFAULT NULL COMMENT 'ID admin yang menerbitkan',
  `file_pdf` varchar(255) DEFAULT NULL COMMENT 'Path file PDF yang di-generate',
  `override_tunggakan` tinyint(1) DEFAULT 0 COMMENT '1 jika admin override validasi tunggakan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabel untuk menyimpan riwayat surat keterangan bebas perpustakaan';

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_buku` (`nomor_buku`);

--
-- Indeks untuk tabel `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nuptk` (`nuptk`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- Indeks untuk tabel `nomor_surat_counter`
--
ALTER TABLE `nomor_surat_counter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tahun` (`tahun`);

--
-- Indeks untuk tabel `pembayaran_denda_detail`
--
ALTER TABLE `pembayaran_denda_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengembalian_id` (`pengembalian_id`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_peminjaman` (`kode_peminjaman`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indeks untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peminjaman_id` (`peminjaman_id`);

--
-- Indeks untuk tabel `perpanjangan`
--
ALTER TABLE `perpanjangan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `peminjaman_id` (`peminjaman_id`);

--
-- Indeks untuk tabel `surat_keterangan`
--
ALTER TABLE `surat_keterangan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD KEY `idx_nim` (`nim`),
  ADD KEY `idx_jenis_surat` (`jenis_surat`),
  ADD KEY `idx_tahun_periode` (`tahun_periode`),
  ADD KEY `idx_tanggal_terbit` (`tanggal_terbit`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_nim_tanggal` (`nim`,`tanggal_terbit`),
  ADD KEY `idx_tahun_jenis` (`tahun_periode`,`jenis_surat`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT untuk tabel `nomor_surat_counter`
--
ALTER TABLE `nomor_surat_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pembayaran_denda_detail`
--
ALTER TABLE `pembayaran_denda_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `perpanjangan`
--
ALTER TABLE `perpanjangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `surat_keterangan`
--
ALTER TABLE `surat_keterangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pembayaran_denda_detail`
--
ALTER TABLE `pembayaran_denda_detail`
  ADD CONSTRAINT `pembayaran_denda_detail_ibfk_1` FOREIGN KEY (`pengembalian_id`) REFERENCES `pengembalian` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perpanjangan`
--
ALTER TABLE `perpanjangan`
  ADD CONSTRAINT `perpanjangan_ibfk_1` FOREIGN KEY (`peminjaman_id`) REFERENCES `peminjaman` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
