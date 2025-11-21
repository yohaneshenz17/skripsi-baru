-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 29 Sep 2025 pada 12.11
-- Versi server: 10.3.39-MariaDB-cll-lve
-- Versi PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stkp7133_stkyakobus_rpl_system`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumen_perangkat`
--

CREATE TABLE `dokumen_perangkat` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `jenis_dokumen` enum('perangkat','kegiatan') NOT NULL,
  `link_dokumen` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `user_id`, `aktivitas`, `detail`, `ip_address`, `created_at`) VALUES
(1, 1, 'Login', 'User berhasil login', '36.90.146.195', '2025-09-21 06:39:12'),
(2, 1, 'Assign Mahasiswa', 'Berhasil assign 0 mahasiswa ke dosen', '36.90.146.195', '2025-09-21 06:40:24'),
(3, 1, 'Logout', 'User berhasil logout', '36.90.146.195', '2025-09-21 06:40:56'),
(4, 2, 'Login', 'User berhasil login', '36.90.146.195', '2025-09-21 06:41:07'),
(5, 2, 'Logout', 'User berhasil logout', '36.90.146.195', '2025-09-21 06:41:26'),
(6, 1, 'Login', 'User berhasil login', '36.90.146.195', '2025-09-21 06:41:37'),
(7, 1, 'Import Mahasiswa', 'Berhasil import 2 mahasiswa', '36.90.146.195', '2025-09-21 06:44:14'),
(8, 2, 'Login', 'User berhasil login', '36.90.146.195', '2025-09-21 10:26:55'),
(9, 2, 'Mulai Penilaian', 'Mahasiswa ID: 1', '36.90.146.195', '2025-09-21 10:26:58'),
(10, 2, 'Simpan Draft Penilaian', 'Mahasiswa: ADELFINA FRANSISKA BRIA (ID: 1)', '36.90.146.195', '2025-09-21 10:29:29'),
(11, 2, 'Finalisasi Penilaian', 'Mahasiswa: ADELFINA FRANSISKA BRIA (ID: 1)', '36.90.146.195', '2025-09-21 10:30:21'),
(12, 2, 'Logout', 'User berhasil logout', '36.90.146.195', '2025-09-21 10:34:38'),
(13, 1, 'Login', 'User berhasil login', '36.90.146.195', '2025-09-21 10:34:48'),
(14, 1, 'Delete Dosen', 'Hapus dosen: dosen002 - Prof. Contoh Dosen 2, Reset 0 mahasiswa', '36.90.146.195', '2025-09-21 10:53:28'),
(15, 1, 'Tambah Dosen', 'Username: 2717069001, Nama: Yohanes Hendro Pranyoto', '36.90.146.195', '2025-09-21 10:54:14'),
(16, 1, 'Tambah Dosen', 'Username: 2721128601, Nama: Dedimus Berangka', '36.90.146.195', '2025-09-21 10:54:37'),
(17, 1, 'Tambah Dosen', 'Username: 2717077001, Nama: Donatus Wea', '36.90.146.195', '2025-09-21 10:55:32'),
(18, 1, 'Import Mahasiswa Multiple Docs', 'Berhasil import 0 mahasiswa', '36.90.146.195', '2025-09-21 10:56:28'),
(19, 1, 'Assign Mahasiswa', 'Berhasil assign 0 mahasiswa ke dosen', '36.90.146.195', '2025-09-21 10:59:47'),
(20, 1, 'Delete Mahasiswa Single', 'Hapus mahasiswa: 25869050001 - ADELFINA FRANSISKA BRIA', '36.90.146.195', '2025-09-21 11:00:04'),
(21, 1, 'Delete Mahasiswa Single', 'Hapus mahasiswa: 25869050002 - ADELHEID SAINA', '36.90.146.195', '2025-09-21 11:00:10'),
(22, 1, 'Drop Database Columns', 'Dropped: email, nik, tempat_lahir, kecamatan', '36.90.146.195', '2025-09-21 11:13:37'),
(23, 1, 'Import CSV Batch', 'File: batch_1.csv, Imported: 0, Updated: 0, Errors: 0', '36.90.146.195', '2025-09-21 11:32:54'),
(24, 1, 'Import CSV Batch', 'File: batch_1.csv, Imported: 0, Updated: 0, Errors: 0', '36.90.146.195', '2025-09-21 11:34:10'),
(25, 1, 'Import CSV Batch', 'File: batch_1.csv, Imported: 0, Updated: 0, Errors: 0', '36.90.146.195', '2025-09-21 11:37:11'),
(26, 1, 'Import CSV Batch', 'File: batch_1.csv, Imported: 0, Updated: 0, Errors: 0', '36.90.146.195', '2025-09-21 11:39:34'),
(27, 1, 'Import CSV Batch', 'File: batch_1.csv, Imported: 0, Updated: 0, Errors: 0', '36.90.146.195', '2025-09-21 11:40:52'),
(28, 1, 'Import CSV Robust', 'Total: 200, Success: 0, Failed: 200', '36.90.146.195', '2025-09-21 11:57:11'),
(29, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '36.90.146.195', '2025-09-21 11:59:45'),
(30, 1, 'Assign Mahasiswa Manual', 'Assign 1 mahasiswa ke dosen ID: 4', '36.90.146.195', '2025-09-21 12:14:31'),
(31, 1, 'Logout', 'User berhasil logout', '36.90.146.195', '2025-09-21 12:14:49'),
(32, 4, 'Login', 'User berhasil login', '36.90.146.195', '2025-09-21 12:14:59'),
(33, 4, 'Mulai Penilaian', 'Mahasiswa ID: 3', '36.90.146.195', '2025-09-21 12:15:04'),
(34, 4, 'Logout', 'User berhasil logout', '36.90.146.195', '2025-09-21 12:28:59'),
(35, 1, 'Login', 'User berhasil login', '36.90.146.195', '2025-09-21 12:29:08'),
(36, 1, 'Logout', 'User berhasil logout', '36.90.146.195', '2025-09-21 12:30:37'),
(37, 4, 'Login', 'User berhasil login', '36.90.146.195', '2025-09-21 12:30:52'),
(38, 1, 'Login', 'User berhasil login', '125.167.172.138', '2025-09-28 00:47:02'),
(39, 1, 'Logout', 'User berhasil logout', '125.167.172.138', '2025-09-28 00:47:22'),
(40, 1, 'Login', 'User berhasil login', '125.167.172.138', '2025-09-29 04:17:21'),
(41, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '125.167.172.138', '2025-09-29 04:50:09'),
(42, 1, 'Import CSV Robust', 'Total: 200, Success: 0, Failed: 0', '125.167.172.138', '2025-09-29 05:04:28'),
(43, 1, 'Import CSV Robust', 'Total: 200, Success: 198, Failed: 0', '125.167.172.138', '2025-09-29 05:04:41'),
(44, 1, 'Import CSV Robust', 'Total: 2, Success: 2, Failed: 0', '125.167.172.138', '2025-09-29 05:07:46'),
(45, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '125.167.172.138', '2025-09-29 05:09:58'),
(46, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '125.167.172.138', '2025-09-29 05:10:06'),
(47, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '125.167.172.138', '2025-09-29 05:10:12'),
(48, 1, 'Import CSV Robust', 'Total: 200, Success: 0, Failed: 0', '125.167.172.138', '2025-09-29 05:10:16'),
(49, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '125.167.172.138', '2025-09-29 05:10:24'),
(50, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '125.167.172.138', '2025-09-29 05:10:29'),
(51, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '125.167.172.138', '2025-09-29 05:10:36'),
(52, 1, 'Import CSV Robust', 'Total: 200, Success: 0, Failed: 0', '125.167.172.138', '2025-09-29 05:10:38'),
(53, 1, 'Import CSV Robust', 'Total: 200, Success: 200, Failed: 0', '125.167.172.138', '2025-09-29 05:10:43'),
(54, 1, 'Import CSV Robust', 'Total: 260, Success: 260, Failed: 0', '125.167.172.138', '2025-09-29 05:10:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `tempat_tugas` varchar(200) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `status_pegawai` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `jenjang` enum('SD','SMP','SMA','SMK') NOT NULL,
  `link_sk_mengajar` text DEFAULT NULL,
  `link_administrasi` text DEFAULT NULL,
  `link_inovasi` text DEFAULT NULL,
  `assigned_dosen_id` int(11) DEFAULT NULL,
  `status_penilaian` enum('belum_dinilai','sedang_dinilai','selesai') DEFAULT 'belum_dinilai',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rpl02_perangkat_ganjil_2019` text DEFAULT NULL,
  `rpl02_perangkat_genap_2019` text DEFAULT NULL,
  `rpl02_perangkat_ganjil_2020` text DEFAULT NULL,
  `rpl02_perangkat_genap_2020` text DEFAULT NULL,
  `rpl02_perangkat_ganjil_2021` text DEFAULT NULL,
  `rpl02_perangkat_genap_2021` text DEFAULT NULL,
  `rpl02_perangkat_ganjil_2022` text DEFAULT NULL,
  `rpl02_perangkat_genap_2022` text DEFAULT NULL,
  `rpl02_perangkat_ganjil_2023` text DEFAULT NULL,
  `rpl02_perangkat_genap_2023` text DEFAULT NULL,
  `rpl02_perangkat_ganjil_2024` text DEFAULT NULL,
  `rpl02_perangkat_genap_2024` text DEFAULT NULL,
  `rpl03_pengembangan_ganjil_2019` text DEFAULT NULL,
  `rpl03_pengembangan_genap_2019` text DEFAULT NULL,
  `rpl03_pengembangan_ganjil_2020` text DEFAULT NULL,
  `rpl03_pengembangan_genap_2020` text DEFAULT NULL,
  `rpl03_pengembangan_ganjil_2021` text DEFAULT NULL,
  `rpl03_pengembangan_genap_2021` text DEFAULT NULL,
  `rpl03_pengembangan_ganjil_2022` text DEFAULT NULL,
  `rpl03_pengembangan_genap_2022` text DEFAULT NULL,
  `rpl03_pengembangan_ganjil_2023` text DEFAULT NULL,
  `rpl03_pengembangan_genap_2023` text DEFAULT NULL,
  `rpl03_pengembangan_ganjil_2024` text DEFAULT NULL,
  `rpl03_pengembangan_genap_2024` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `nim`, `nama_lengkap`, `jenis_kelamin`, `tempat_tugas`, `no_telepon`, `status_pegawai`, `tanggal_lahir`, `provinsi`, `kabupaten`, `jenjang`, `link_sk_mengajar`, `link_administrasi`, `link_inovasi`, `assigned_dosen_id`, `status_penilaian`, `created_at`, `updated_at`, `rpl02_perangkat_ganjil_2019`, `rpl02_perangkat_genap_2019`, `rpl02_perangkat_ganjil_2020`, `rpl02_perangkat_genap_2020`, `rpl02_perangkat_ganjil_2021`, `rpl02_perangkat_genap_2021`, `rpl02_perangkat_ganjil_2022`, `rpl02_perangkat_genap_2022`, `rpl02_perangkat_ganjil_2023`, `rpl02_perangkat_genap_2023`, `rpl02_perangkat_ganjil_2024`, `rpl02_perangkat_genap_2024`, `rpl03_pengembangan_ganjil_2019`, `rpl03_pengembangan_genap_2019`, `rpl03_pengembangan_ganjil_2020`, `rpl03_pengembangan_genap_2020`, `rpl03_pengembangan_ganjil_2021`, `rpl03_pengembangan_genap_2021`, `rpl03_pengembangan_ganjil_2022`, `rpl03_pengembangan_genap_2022`, `rpl03_pengembangan_ganjil_2023`, `rpl03_pengembangan_genap_2023`, `rpl03_pengembangan_ganjil_2024`, `rpl03_pengembangan_genap_2024`) VALUES
(3, '25869050001', 'ADELFINA FRANSISKA BRIA', 'Perempuan', 'SMP NEGERI HELIBAUK', '81237045344', 'PPPK', '1995-04-02', 'NUSA TENGGARA TIMUR', 'KABUPATEN MALAKA', 'SMP', 'https://drive.google.com/open?id=1-J--zMVJA-BkPY8ScbPEvHm6ULAXCiNl', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 4, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 12:14:31', 'https://drive.google.com/open?id=12-mliCc7pprdg-rK34H_Mju2mC3KxNoR', 'https://drive.google.com/open?id=1Y0LoybkOoZukc6MfrdOw5gLiuJqnWOxI', 'https://drive.google.com/open?id=1aS7AxSzh8lg-36TshtvSoTror_86q0TM', 'https://drive.google.com/open?id=1xJX15_n4qrheRr-oxtigl1A3sUxUGCgn', 'https://drive.google.com/open?id=1m559cFGcQSoUzoto4KZCWfWC0PlJe99C', 'https://drive.google.com/open?id=1dOLnZEHWU1I0GmKfeS8OPyH4IRBi4U2M', 'https://drive.google.com/open?id=19Ir7VAOH0dPJUuluzXJZRUShwdaT-5HW', 'https://drive.google.com/open?id=1xS_U26maeYuSt_6BC9JWiw-rU1XAgUs3', 'https://drive.google.com/open?id=1BBUPm9a03kIwcXO9_NVptskgJ88Xtbr7', 'https://drive.google.com/open?id=1c7egz93cAlkoXQCjZISJwOYgu91B1zMV', 'https://drive.google.com/open?id=12RLrRu_QJ17NCD8pT2cVr54uNMbw0Q2W', 'https://drive.google.com/open?id=1WqXjzr8AcoFF4b7eDS80Q6-bajYdp8Ub', 'https://drive.google.com/open?id=1qXDlWgyBp3PpvCH4LQuJJEpLhRAsiY64', 'https://drive.google.com/open?id=18v0yhv3pNbKQ1qWMFeLnPuRlP_K_QDTR', 'https://drive.google.com/open?id=1mp0bIndALXmiDd-to4rklG5hjiCNOyPx', 'https://drive.google.com/open?id=1ZKsyq8Jw-ArYvwdWZoZMNLLKgcuIuwnG', 'https://drive.google.com/open?id=1isiUsvKeJzn5K5EgLZ6apImMyf4SjUd4', 'https://drive.google.com/open?id=1fU-7Wefc0J6gR6y7Lan6HVJTIEF-SnhT', 'https://drive.google.com/open?id=1GymkNSOXdCsrotCqx1FP6p20Gceq19gZ', 'https://drive.google.com/open?id=1PWD8BcY35-6W0vPfDh50qkc_1NJ5qoJl', 'https://drive.google.com/open?id=1zUG0U3NPoSCvga_eP9qnncPtZC_rk6H0', 'https://drive.google.com/open?id=1gEvd93f3h7-i5VAwJTAxfRReRb3qAd6P', 'https://drive.google.com/open?id=17Ov_qrYggLZoPSg_-iyDub_tQC6W1JIw', 'https://drive.google.com/open?id=1q3LCmhXBEW8wllk13Z9UCXs45po6x3XT'),
(4, '25869050002', 'ADELHEID SAINA', 'Perempuan', 'SDI LELIT', '81338731398', 'PPPK', '1991-04-23', 'NUSA TENGGARA TIMUR', 'KABUPATEN MANGGARAI', 'SD', 'https://drive.google.com/open?id=11wJjdCwaKHTj2TLno_53iclac6b8hTKY', 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1Y5ax4BOcKmVr6UK42lM5J_f5Dact1g2Y', 'https://drive.google.com/open?id=1ozO98VGHpAxL_4W-_R0p340Y4Kpspqkl', 'https://drive.google.com/open?id=14pnxLT_Y6nDVSXJKUj9KHt_6pz6pgkhv', 'https://drive.google.com/open?id=1rMDvR4XQZPt1rqCTHk975YDl9shZUlsW', 'https://drive.google.com/open?id=139w3F-jwo_zl25xbZBV9O7nGdi30CPAr', 'https://drive.google.com/open?id=1wnaRmmCaR_JUkM5IaV774Xu4fCErmnoD', 'https://drive.google.com/open?id=1KWXOh2n8OXOaUzUy_qb0dWrO0ggwoWXC', 'https://drive.google.com/open?id=1RndMYoFZDtvlwG-yKBQCCk7meZaCkRvJ', 'https://drive.google.com/open?id=1dHj-Z3zmf4wA7--tHUeGbi1XFZeZTDAl', 'https://drive.google.com/open?id=1Gn4KJ_u-Q4Ld2GWpSf6x-GtgdJBN13W1', 'https://drive.google.com/open?id=1LPcphwBqm9_OWIdXcIlKon_TlxpGxcUt', 'https://drive.google.com/open?id=1mgn4ihyenyitTXn97FBPIHVNcMisiQf7', 'https://drive.google.com/open?id=1KJKabJlmVK45JCHbltFEVHmJU9UsIuuD', 'https://drive.google.com/open?id=1g83D-4qxL8F9LrETshpJ2otzulFh0-u8', 'https://drive.google.com/open?id=14-O29B236-4IXIWS0lN09lwMtzzl87lN', 'https://drive.google.com/open?id=1RV9zQZHk3iq_NJNaSNigyU_Z_HHtfln_', 'https://drive.google.com/open?id=1odmMqflFW7zsK93H2hkdZoMf88mo0Vaq', 'https://drive.google.com/open?id=1a-7gDf7xb4HfALQ3iAfmZYc-PTj6q6bM', 'https://drive.google.com/open?id=1h1ZJsNdERrrbTYh-TLtl8cmTGANjoTo6', 'https://drive.google.com/open?id=1jh4VWB0l8D2e43e1YRsfpw-zbe7PwM-Z', 'https://drive.google.com/open?id=1Kyd3PPseDc2aQQ_BBLnq6QxZPdZlsHlY', 'https://drive.google.com/open?id=1rdQhiptu-atApz4QwcEeAMe76OGGJ99Q', 'https://drive.google.com/open?id=1i5ZwLFyWthCTVodXJzOyOkBokYIjI9ta', 'https://drive.google.com/open?id=1RsomiGAiwo2TWUbBAiWlSw7kfhhRzAeq'),
(5, '25869050003', 'ADELIN SEMOI SOGEN', 'Perempuan', 'SMPN 1 NUNUKAN', '82144868574', 'PNS', '1993-08-18', 'KALIMANTAN UTARA', 'KABUPATEN NUNUKAN', 'SMP', 'https://drive.google.com/open?id=1K4WqMmc8JxNExZPYwWDu3S_4ClMOrAaj', 'https://drive.google.com/open?id=1yYnHsJ1GzMoxBLQm0gBT2xfls40Vanus', 'https://drive.google.com/open?id=1wflYAm1xeUSj3gRv7jjR5dwVOECOSDnO', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1cmnUZekygcYzOWqPQ6BmAqbvxlq-f8jl', 'https://drive.google.com/open?id=18G--2poJ9JKQk3fbn45Et0gvYfsK7fJv', 'https://drive.google.com/open?id=15D1DLFyJRHn8snQfz1LE2f_PbC7xPwJq', 'https://drive.google.com/open?id=1DOgQbifyQRaNFXqlYdoyRLGXhPACX1NK', 'https://drive.google.com/open?id=138V10kbb5j1QXplQqkNumvlozfBjTtpk', 'https://drive.google.com/open?id=1NcIzD6Z1d-ODFo7t46RCVjv4EvEEMcxo', 'https://drive.google.com/open?id=1zDwkVT3HbG1Tf5r9EuEQ-TyBH5e4ibkO', 'https://drive.google.com/open?id=1PNkeFOL4zQj8p-swJqkvpgUQULwpd5PE', 'https://drive.google.com/open?id=1nU42vI7Y5qgawF0DlZa2TEM7mSWgsmFU', 'https://drive.google.com/open?id=1WavNxNuGHypYYvSBMQtVB6AosDTwAQW2', 'https://drive.google.com/open?id=1299dSTesbetTu3BvTcAyTMq8raf1jWvx', 'https://drive.google.com/open?id=1Bi4hML9U9cf4nltlq5rkkTdNfU1bq4QS', 'https://drive.google.com/open?id=1sfV2ZTg_51kXK654kukGNmps44t5J8Qb', NULL, 'https://drive.google.com/open?id=1big_-fLcFff6h5pIUEabpW6nSPvWR_PJ', 'https://drive.google.com/open?id=1Zjps-m2nMSuCkVvdyaIMoZGbJUkjvsUq', 'https://drive.google.com/open?id=1Nfdoy7tN4pLi6C1YSfiiU5QC9ExJz8Ym', NULL, 'https://drive.google.com/open?id=1jr_cNKpSvi5gtvzQWdw-P3_VR44-QN5A', 'https://drive.google.com/open?id=1_hGC6R_2cISHXkUhPBEf37zpCk87j9SV', 'https://drive.google.com/open?id=1sKwpYYVkCxDVgOjT89NzJy3H0unGEdl_', 'https://drive.google.com/open?id=1jpD5A9s46WVK_FWPyq_SNJsKBnGFYp8F', 'https://drive.google.com/open?id=1HfRDy2AuWlo7z2WOXLmXtKvui96C1wyw', 'https://drive.google.com/open?id=1XDmnVQeINwXVzCG_1QCMP1isyanPTyl2'),
(6, '25869050004', 'ADELINA NARTI', 'Perempuan', 'SMA NEGERI 3 MIMIKA', '82278827050', 'PPPK', '1988-02-02', 'PAPUA', 'KABUPATEN MIMIKA', 'SMA', 'https://drive.google.com/open?id=1PF8ov6ZBCq2PCqVHeSJnr3yqb13BtlNu', 'https://drive.google.com/open?id=1MqltAGPEWOBALR24BPYHQG79IyhrhOIs', 'https://drive.google.com/open?id=1nWqM2P-KhptLb3tBgSKLnnLCVwIFYMuS', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1eVlU2WzYIg65pbJnQPo4YiDkJCTluvma', 'https://drive.google.com/open?id=189O2Al5hyhdgVUi2V2iuWf8sCahGh6M4', 'https://drive.google.com/open?id=1zJFxQkA2j6Nk0MfShCSGjwMQwe55flNz', 'https://drive.google.com/open?id=1SG9QFvYK0WB6BLp70rNIzVdRqZ-nBEPT', 'https://drive.google.com/open?id=1tXe31QLxd3YI96165fT5_9lzQJykPNBO', 'https://drive.google.com/open?id=1VE5E_KwIrDgcVqyphotvFJou5CHzo4CS', 'https://drive.google.com/open?id=1nbDR_Z4O7XIkuJzA_7_03nYwg2SLdFSa', 'https://drive.google.com/open?id=1DOw9BzXQAye_PAmDBH_u0x55pIL7Dony', 'https://drive.google.com/open?id=17-4rDg6NFc7vkIdA9bq8eo10PamYXd3i', 'https://drive.google.com/open?id=1k6flAnHuaEgs8GXmtMXQyI0uT1qnSNjd', 'https://drive.google.com/open?id=1M_-6CDRFyrcmC5e7oP1LpZApdblPY0m2', 'https://drive.google.com/open?id=1e-baPZiLdq5CnsVGTyf_ikzccY9XIOmY', 'https://drive.google.com/open?id=1Hx5oiw26grdBwzLA6VswnQUh3Knxxg_l', 'https://drive.google.com/open?id=1j9kcQcZ8PSOwsuKvPkZBvybAMICwBl0f', 'https://drive.google.com/open?id=13RwI5jTKLvTJcRCjJ3wN9CARXueo_lZW', 'https://drive.google.com/open?id=1ROGVP5pHvli9sc1927oG7ZSV-vCrHAZt', 'https://drive.google.com/open?id=117Sz2X_02Cjp1Bo6UlivDUscojBHCSpH', 'https://drive.google.com/open?id=1yBWcjfUy-mYAt_hLHu8JMNi3h59Ub_cW', 'https://drive.google.com/open?id=18IFqj-FSsa9A3Z5IRjhaxNLhjBLhLXJN', 'https://drive.google.com/open?id=1BZTMJ2wIFajpz-GfFWDrRWKTPgUhmOiX', 'https://drive.google.com/open?id=1Nb8ujXFNyDQgTq5pRxF5nnvdeKvAJbOw', 'https://drive.google.com/open?id=1YODktthn1NvN4i74EQgYhj9eW1ANvlKw', 'https://drive.google.com/open?id=1ffJEZm61V0sRWoG5wEFEoI5UywjefL4-', 'https://drive.google.com/open?id=1v8h76fVx3M9B0A9ue8zitgwcd7lghU2A'),
(7, '25869050005', 'ADOLFINA HOAR LEKIK', 'Perempuan', 'SMP KATHOLIK ST ISODORUS BESIKAMA', '82147737209', 'NON ASN', '1991-01-20', 'NUSA TENGGARA TIMUR', 'KABUPATEN MALAKA', 'SMP', 'https://drive.google.com/open?id=1EE5HdkzZWvgNbtoFQLTmgvU6e7Ij4vgd', 'https://drive.google.com/open?id=1P4AAiXQ37Je9xVdGSul11A7X1YkcIELZ', 'https://drive.google.com/open?id=14wC1hoGaZ_taKlTfP-YZTHvkYBiTLZjU', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1pxPkh3dYOiGXQB-tE9UNNzkmiOMWHksL', 'https://drive.google.com/open?id=1_VzJZC-bHNz4A_UTbFXmhVNBMFQyv-VP', 'https://drive.google.com/open?id=1ZsvHU_EDvqqPdEDVx1qB7KUp68y_5xbk', 'https://drive.google.com/open?id=187JklA5YW7H1MBkheW2Xln8JE1CmrUru', 'https://drive.google.com/open?id=1Uguy6_R6vnVIcuuJgPGTGtUO7a6GiukX', 'https://drive.google.com/open?id=1WV2XlbqN-12vKMzB7X5ExW67fwoiXHQ8', 'https://drive.google.com/open?id=1dDPXogWOig_qk6DCex5GerFSjlhDj77B', 'https://drive.google.com/open?id=1Si8J1SelVxKWf-4QYqVhsxeSACBohtrb', 'https://drive.google.com/open?id=1I4RuaWWFHiKXqtukX5HWEmfPerfvo3dt', 'https://drive.google.com/open?id=1oUrtPkdHuSebI7JiyaBMEoqzogktL7dV', 'https://drive.google.com/open?id=1-50Jpw0cQcyrRKV65L3GAugt7-6rIVBl', 'https://drive.google.com/open?id=1bpf26-h90U_xQmKVd4ffi90JDr2PBUiU', 'https://drive.google.com/open?id=1QU9klCdXQTKiocYTXsbDtUQ2u9bH5nK_', 'https://drive.google.com/open?id=1_d7v9ONTvakWBeCPtDCMYM-NwByg3gU2', 'https://drive.google.com/open?id=1--QSrZPAW-ugtmC05ZDyrgaQGZcLX83V', 'https://drive.google.com/open?id=1lcy9R-52KJuPVKNuO0aMFGgp4OLPzI-j', 'https://drive.google.com/open?id=1NObGYQi0zKHgXfsrkiL_2oj86RftesAD', 'https://drive.google.com/open?id=1K9FOmZmH3IBv0SLp9XWOW1_8zshnqcMj', 'https://drive.google.com/open?id=1nkEtTr2TN4uPHjrokv3FBorADxalw-tz', 'https://drive.google.com/open?id=1LKqvJxilwQ6CzY4U_kBqM0M8oAVjfA7_', 'https://drive.google.com/open?id=13rtRt86fluojQF7PdRG2n9U6y7ZL1HXV', 'https://drive.google.com/open?id=1an5xZPV_7292lIftWn2flthO_M-z64BY', 'https://drive.google.com/open?id=182wNPla-JP7zwR8c46ytLgM10iq0Idh6', 'https://drive.google.com/open?id=1mGiPaTf6XPat-ouG9ZwsnoUm6gn0hPja'),
(8, '25869050006', 'ADOLFINA NENU WEA', 'Perempuan', 'SD NEGERI SAKULETE SUFA', '82117516306', 'PNS', '1985-08-06', 'NUSA TENGGARA TIMUR', 'KABUPATEN KUPANG', 'SD', 'https://drive.google.com/open?id=17a_QOtAfljYdWr4wgDC2a1zs-eAwO8pM', 'https://drive.google.com/open?id=1jKXp15nV-BK083Zp_F1n9djbMGxsmLA0', 'https://drive.google.com/open?id=1qfI8l3YWuuPTobJeQejL3bxWpc-ImxwN', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1ROlO8OjwYnJ5B_2EJwPebadKcehpN-P6', 'https://drive.google.com/open?id=1hl6e3AalzYqoOjaQqqWsYQAQEz6HOpMl', 'https://drive.google.com/open?id=1g-ujU02ZpTcVnjd6n2D1uXIkCQ3MWJOT', 'https://drive.google.com/open?id=1Xr4S0lXcv36F1MYUGNtuiyN-5StXy3iD', 'https://drive.google.com/open?id=1bfHyTidDmNmca0xamucfepNEUoyCG9QT', 'https://drive.google.com/open?id=1kj5V9dxMlpAvNm8tvFVLAnE1Z37jKOvK', 'https://drive.google.com/open?id=11Z8gvPE1Yx6A2ujBgVhCrKGlJw5PMDNX', 'https://drive.google.com/open?id=17u3_UmHXwqMx2zSgv8VfH58HT16FqXGe', 'https://drive.google.com/open?id=1r8IzNbspLwA488H89TMR3Th2jEu9JN-_', 'https://drive.google.com/open?id=1HbtMXqgNmyKICNPmiJAo_EQA7OKZoVwy', 'https://drive.google.com/open?id=14PxCFY5dU71GBGpJFeKdDIX7oFsETr53', 'https://drive.google.com/open?id=1l9wqtDtVYmtBWZ7cUIZuM0uEHyIMW9ud', 'https://drive.google.com/open?id=1Ss-zephSSuDTMdukLvWkHwDo61xOoEiH', 'https://drive.google.com/open?id=1rM2C2EFA1LDWfBDrCb6_0HN-aNot51VK', 'https://drive.google.com/open?id=10Kz3EgUN14-dxC_tMqX0Mjgjof7IRUIM', 'https://drive.google.com/open?id=1aDhX9k5c_iCWO63ZzRNO8Oz_brIYyx_3', 'https://drive.google.com/open?id=1how5QGEA63lNFR0u4cnjRbEgEci2d0cx', 'https://drive.google.com/open?id=1xMppr4gHn4coxANbDsErL9Er0u4y96JH', 'https://drive.google.com/open?id=195QY_jZM3l2_-BiK2CLS7GhNeOwOMD40', 'https://drive.google.com/open?id=1PxTvnQe5NvEaPoB8R-lvVDbYFBkB6cgy', 'https://drive.google.com/open?id=18o4fEPc_ippMeM6WRPAraRLqcslBex-t', 'https://drive.google.com/open?id=15_MElS7JxAKkQcHlR6gZpnRw4Pk2h-Uv', 'https://drive.google.com/open?id=1roeNn5IlHtXl8zVtxla8O1KuXpT5-C4W', 'https://drive.google.com/open?id=1JYLDbjYwwkKWAiOqdHzO6jWJhd5IvRHz'),
(9, '25869050007', 'ADOLFINA YASINTHA KESE', 'Perempuan', 'SD NEGERI NEFOTUFE', '81236066329', 'PPPK', '1989-04-07', 'NUSA TENGGARA TIMUR', 'KABUPATEN TIMOR TENGAH SELATAN', 'SD', 'https://drive.google.com/open?id=1kaw1MB8qajvmaCi_L8BgxwsL9wLU0YGE', 'https://drive.google.com/open?id=1bZBJg4tYTq7J5Pc2w2--bNm5--NpdrJ7', 'https://drive.google.com/open?id=1WFJVJGjtJLqZFUNxxfPGNIhX0BCD36DN', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1iFEBmhExZYIiDW0FXyD0r22JwFn4Z3L0', 'https://drive.google.com/open?id=1-h224f12tYGv6hxgZrQh_x_ZKwH3Bhoo', 'https://drive.google.com/open?id=1JHrolJxtRAYI5UdpdYKizkbT8PwnkMM6', 'https://drive.google.com/open?id=17RTsUC5eKfIAWTP9YpF8pSUadX-oaq7C', 'https://drive.google.com/open?id=1KgP80y_1h3ePPRHoUFuFfQmiXYj1AwL5', 'https://drive.google.com/open?id=1_hW2R0B6WGXj4kgCgiEMvTg2I6ZjvWX-', 'https://drive.google.com/open?id=1llMn5vtaNTB2urwE2ke__k7vnP4F1RJX', 'https://drive.google.com/open?id=1Y0GWDgSJJinvIN2iygbKTSiBHezRPy-L', 'https://drive.google.com/open?id=1BDXfpxhc8SQaLNO1Dhp9OesStelYMyqi', 'https://drive.google.com/open?id=12rkkNYLxoSxc4XSKtd-TRE9qYUwp9Had', 'https://drive.google.com/open?id=1WdevX0MBXWrZgo0J0O6J3cMuF8xCapyx', 'https://drive.google.com/open?id=1BR6imow91b1NMlICswtPLfAbF6cnBzEZ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1kVP_nye8L45v69n28HEDPKxJlDPdJqHp', 'https://drive.google.com/open?id=1LeW34bjTA9Z57_0XQD7pmz_QzQSUt5eR', 'https://drive.google.com/open?id=1fQRpj9wI7FgbsIj5yMjQ_X-P1s8oDmqh', 'https://drive.google.com/open?id=1dS6-8Oo8Y6UCzUWvb7aVd4joQFS-ZNi-'),
(10, '25869050008', 'ADOLFUS DASAN', 'Laki-laki', 'SMAK ST. STEFANUS KETANG', '85238341086', 'NON ASN', '1986-06-17', 'NUSA TENGGARA TIMUR', 'KABUPATEN MANGGARAI', 'SMA', 'https://drive.google.com/open?id=103FfLlscAX0UaSNt7fm7P4-2pBtaBzzs', 'https://drive.google.com/open?id=1BirxYz8jfolMcG41z_wmX1wzRfRVruRO', 'https://drive.google.com/open?id=1tmPBdRpdWd5Y89jxtB20QGxNtlXXAEVA', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1oaUvTlCTK2G5wjLKDELeF_qnE0At4EoT', 'https://drive.google.com/open?id=1ZSJZBwd-HscG8UVjBjkteTw_RSir9CQW', 'https://drive.google.com/open?id=1VD_WTz2VVuYhmIg9c1y06y5uo0cNZUrC', 'https://drive.google.com/open?id=1_oyaTpO7vMgT7zW8fPsxMrp8Xe_jiP60', 'https://drive.google.com/open?id=1Aqd5fcbmRSkB5D0CXwvdHTSuFR_8lSpY', 'https://drive.google.com/open?id=1SwIEw10qVzzB6tPkU-LF-maxlwAVjdbM', 'https://drive.google.com/open?id=1z2dzVhkrV3cN2A_32n6HwKZDrtPXaGCL', 'https://drive.google.com/open?id=12Ftv_fQ7YBimV5XFwy-nK_MZJX5dvdk4', 'https://drive.google.com/open?id=1FEWdPE_Hzo9g3-AtnHmM5jxZ_OBqaaIY', 'https://drive.google.com/open?id=1lnBb0bSycooIbUGuCiaoSkor9OxI1Sa0', 'https://drive.google.com/open?id=1_3sdNV-Hz8lT0J4w10KOYMNHh6oDcUGy', 'https://drive.google.com/open?id=1kw3d_UXKp3PbFxQFbP71CwpkAFQmAuOP', 'https://drive.google.com/open?id=1Su-yKvjtkHxWHtW8geJ0lGlvTj2M9Tud', 'https://drive.google.com/open?id=1jQ3DVWJbG002kfAivQdJIbQLWbs2oeCU', 'https://drive.google.com/open?id=15dVXZuYZ_AKD1jSb634m3Pay_P_RDeU_', 'https://drive.google.com/open?id=1gp9WTn4PGwxCDkYg9QWNkSZ_n1eVF9Kz', 'https://drive.google.com/open?id=1TRrKEaGzCQNOwLqBHiQeuzNiYH806Hfh', 'https://drive.google.com/open?id=1gPKBiQlXdD6qeW6vS5uyvScM8QrjwAUb', 'https://drive.google.com/open?id=18OHXEqc7gc4-u16g_ZrFYa0gifYQJdAP', 'https://drive.google.com/open?id=1FQWIpx9wvrZQcmzF6H1JP86gD6fJ-Uuo', 'https://drive.google.com/open?id=1UOZAJOpT-nfnW4d4zMRldpvO5xrWwHaL', 'https://drive.google.com/open?id=1xuLhD5H64HPbeRH7awQgMcE9ms3dP8Lh', 'https://drive.google.com/open?id=1tUlTK5kzYz9rFLVdhUqTxJuzfG4EVELe', 'https://drive.google.com/open?id=1J7bXCzfTENF5FoXvyBRC3KMLlNcCVXFB'),
(11, '25869050009', 'ADOLFUS FREDERICK KOPONG TUPEN', 'Laki-laki', 'SMP NEGERI 4 NUBATUKAN', '81237329538', 'NON ASN', '1994-06-17', 'NUSA TENGGARA TIMUR', 'KABUPATEN LEMBATA', 'SMP', 'https://drive.google.com/open?id=1FZCewcdW41QQUcwb8oGdT6nnSBAHt1tP', 'https://drive.google.com/open?id=1TCZHmEhctAVNyu_pYZ0wqfODnuUw2__f', 'https://drive.google.com/open?id=1gO7yycDBRdOP3kKtFKgBlS3Fbr7RP8RW', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1y04CVjPsvYF2PDMsgMaFbJGSuLF1h7Pr', 'https://drive.google.com/open?id=1PC4_wT1cbS-2eLilCQyJodzn9BFedtNX', 'https://drive.google.com/open?id=16-Xhg88JnYIb80KRcW0pZvf_8CcxoSKE', 'https://drive.google.com/open?id=1Ev0EvkBCEunSZhKbBkwq5xnAyDxlEBCl', 'https://drive.google.com/open?id=1yrC3YrZJ0k-K2au95boHbPRq9GHGfxjs', 'https://drive.google.com/open?id=1f47yk9Eo5abRId3f5equ9CihWQcURREb', 'https://drive.google.com/open?id=1ziW43OdU3rK8smsrNmDygyGM-qr1YkN5', 'https://drive.google.com/open?id=1wMeMpB6S2UrKik6tVcqyJjpVmLEHXuY7', NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1bNz1uNOB23qWR4SWyPA3bqcgHTTikVCd', 'https://drive.google.com/open?id=14qqW8TAPF4uKpSCf6ORla-iw0KSKhxYh', 'https://drive.google.com/open?id=137a2LfQuQF2Wpub3IWZ1TxiViPsgiTZ6', 'https://drive.google.com/open?id=1KgUdGb416xWYUxHPTkGPuGxI5xnCSF_B', 'https://drive.google.com/open?id=1JJ12sbRRXSnW8fyGq6vhNpvf9Nk4R6iq', 'https://drive.google.com/open?id=1WZduYxKrJApUUkdrgjuwZTqwBXvbyq3r', 'https://drive.google.com/open?id=1I-tzNfAHchW305NxLlqWjAjUwWcXUL1A', 'https://drive.google.com/open?id=1qQBfI89_9fNtZtzsqWPF5HATsJ3FzKMM'),
(12, '25869050010', 'ADOLFUS L.B. BALAWELING', 'Laki-laki', 'SMP NEGERI 02 SATAP WAILOLONG', '81236084144', 'PNS', '1991-06-12', 'NUSA TENGGARA TIMUR', 'KABUPATEN LEMBATA', 'SMP', 'https://drive.google.com/open?id=1BSN45Ez-dt48BTJRNerK9oeUEGygKSab', 'https://drive.google.com/open?id=1O4NmwYabm_z9oqTFitMXS7Zz1HaexGvh', 'https://drive.google.com/open?id=1Z-dZFQuEASKHsEQ15A--T6pEwsrBTYRR', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1Ct_L68N3VmsMgqif1BtxsU3uzJ6th9xQ', 'https://drive.google.com/open?id=1cQIIQJTdooiunXa_LUIziToTLFqfrKH4', 'https://drive.google.com/open?id=1bJc9DiS0nh-aVAvXmaT4Qt6kULKv6Yka', 'https://drive.google.com/open?id=1TqnobPcJUCSfzG2aVT99LX1YDl9agA9_', 'https://drive.google.com/open?id=1tCaHVy8vZDOHB6H_JuHj-ZkeFI-f6m6j', 'https://drive.google.com/open?id=1d-nurHadogvo80FtSl-RbDPw83Fhb0wV', 'https://drive.google.com/open?id=14L5xC1A-65x0v7ioQVNMO-iB5Bmt7Z5T', 'https://drive.google.com/open?id=1cFJ1X-Tw9sTtIG6bvaJDkxboiMpJbEVG', 'https://drive.google.com/open?id=1E2GYUTZJUPx2LDXRBLp3vxQBCa4bU7ms', 'https://drive.google.com/open?id=1fyIz2o26cz3w4362n0OvPO__NYPQIgt4', 'https://drive.google.com/open?id=1JY0IVrm5r4nD3D-HECv9zOXgb75QOnZi', 'https://drive.google.com/open?id=1NncHHgRgC9ClfrLMMqnC0iOvj8QD8nNm', NULL, NULL, 'https://drive.google.com/open?id=1KLUedG04q4p7CIeSJpuPo6L7wBebqFs4', 'https://drive.google.com/open?id=1WrSxoUqwBP8XR7DY9otW_OdArdY3b7bw', NULL, NULL, 'https://drive.google.com/open?id=1KshSg0m_F33XQYvM2icO1euqGFWYrSVj', 'https://drive.google.com/open?id=1M7MvZlbAaknmOocfOxv2PiM0IOoeS0lD', 'https://drive.google.com/open?id=1GmE0oQ4giJDN61AcpbXGXiQOvfUAs5eG', 'https://drive.google.com/open?id=1IZcjfe4FG-9hqIr383DFQRoOsJ5At-dJ', 'https://drive.google.com/open?id=1Y0iiQTHS9j9-f6HcWtu9s5k7WNlqFXns', 'https://drive.google.com/open?id=1MbXTR1EJar9oTvkT-4K2In6lZufV6w_o'),
(13, '25869050011', 'ADOLFUS WOGE', 'Laki-laki', 'SD NEGERI NUSANGGALA', '85271111310', 'PNS', '1989-06-13', 'NUSA TENGGARA TIMUR', 'KABUPATEN ENDE', 'SD', 'https://drive.google.com/open?id=1rmpixQsKzL1a7auNORwSgR_HFEOuK8Dc', 'https://drive.google.com/open?id=1RlLYYTOeWinnfvgyi0uA2xw7OBEVJoLm', 'https://drive.google.com/open?id=1oUc4Om1T-Dlq8GnkxqHvzX8pMx1FOVjl', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1gMqlVt2OhEpqEATq6nQmbzTrSVhznA3H', 'https://drive.google.com/open?id=1MMwVNRn1L1VNCkP1p9bWG-gA82wsTuXJ', 'https://drive.google.com/open?id=1jEUBVdZc6ZLhms9de7E8H6DEYz0FtO9P', 'https://drive.google.com/open?id=1yIhOXdtsL0a1K0j-Iz-49uOA3Wo6kjbR', 'https://drive.google.com/open?id=19O4ERw3PKd1-GMQouWfCjfqBCd94LYCr', 'https://drive.google.com/open?id=1FqEqoBE7gOj0IVr-7qEMjhXfMi1oKQCn', 'https://drive.google.com/open?id=1RT3tfF_hBYPFihkGZ32TOtBcIbPIRB6u', 'https://drive.google.com/open?id=1MiKlrgLJWWVn8JXnq4Abtg5d3bTPM7Kb', 'https://drive.google.com/open?id=1HgNNCETzVCiPzV0nzvmsSwGchwFTdEVi', 'https://drive.google.com/open?id=1kaFYjFYNx-d2-SowJ-r8QRZmbxC8Kn05', 'https://drive.google.com/open?id=1rUAKMntiJnyjR02p9v7Vt27S9o5otvva', 'https://drive.google.com/open?id=1hjZadHD9SdTKZDs4Pc3k3efqC6WEVTUs', 'https://drive.google.com/open?id=14UgDfWokPTRFuc_UrPvrUud_JBDa8aNH', 'https://drive.google.com/open?id=1TUAiNqjVkPf6fpScC58CONqb75_N7J8X', 'https://drive.google.com/open?id=1A1vCb2ixGlH_JDQt-FIbo4tFdpMNOxNd', 'https://drive.google.com/open?id=1mEHB0uGTtEyvLLN6e6jS9ei5hGpAYsp7', 'https://drive.google.com/open?id=1LgG3qrVk5d4rFl0qibFeRo7vIc1mzab3', 'https://drive.google.com/open?id=1hTT1dCClAe4plp8DCE29zg2CT4dZk9n0', 'https://drive.google.com/open?id=10nEZT1nAN1gHxSfTvNH_YXkmgE_aDWff', 'https://drive.google.com/open?id=1puJahJJhK6W9G0xfpAV_e0FHfHhFjPJw', 'https://drive.google.com/open?id=1Ei2TnI1lfJ8NnkUyXy1KBXnTKWjkYnK_', 'https://drive.google.com/open?id=1fGjQtJ9NIFkBUzeR5Og-JxwV5g0B67Ai', 'https://drive.google.com/open?id=1uP8gHxreY_iH8RPnj1g53C5gUq8ERFMf', 'https://drive.google.com/open?id=1pZHOnwNHpQwPCidsono7LuZMtYskBlzK'),
(14, '25869050012', 'ADRIANA', 'Perempuan', 'SDN 2 NANGGALA', '85299770157', 'PPPK', '1994-07-16', 'SULAWESI SELATAN', 'KABUPATEN TORAJA UTARA', 'SD', 'https://drive.google.com/open?id=1m-sHHP27ViSgm5zJ1deKDoSlHYVC8dYx', 'https://drive.google.com/open?id=1PR9VVulaSjVh6wJZstC-Hq6CicwPCoQm', 'https://drive.google.com/open?id=1AMoI9jiPR91kHc6QCWpzFCOeA1Wrf9FJ', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1pWPKdPvvCBR-6bwHxJqZ1Pjhm4l6mq4c', 'https://drive.google.com/open?id=1pMpfuHhCNneyoVTOAdu2EiqipG8_RfBb', 'https://drive.google.com/open?id=1BXong_9ZdKUH_5E2W7jRQZYYywIwIxb-', 'https://drive.google.com/open?id=1Rvu9sDUYPxyTZAyxv54Rpf8dL0AhmivK', 'https://drive.google.com/open?id=1V0l7u3pCRQo1DdepeHjbSuWCUlvqk1l7', 'https://drive.google.com/open?id=14rxl5NH7DXyQuSIolusDicRFLfxGfqIw', 'https://drive.google.com/open?id=1o8Tf3fFoOOj6BSvIv4HCdv07TsGEy0ba', 'https://drive.google.com/open?id=1Aejgx4Dp69nqRXGdMGyR70a8KL1Cn5NR', 'https://drive.google.com/open?id=1nOswUC9PsobWhd-wVhkGLyNSqlJeEy2q', 'https://drive.google.com/open?id=1pQZYdkgn0qnjYvF5jo-V5rPiKcVPQFeA', 'https://drive.google.com/open?id=1Si-5dzb46f7uSsxgmFCK8Qo0NledpjoC', 'https://drive.google.com/open?id=1BWw9fATrg8kYDTOa5RdD4jnH7tGQn8TV', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1q4qrdOaPKjW7XpNJcTggvdop77c0DhER', 'https://drive.google.com/open?id=19efV3HxAereO-2coTXA-pU4NLnUNXdBy', 'https://drive.google.com/open?id=1DfLMA1FLFTGyPI7F9yNxvxQzBssx2i1b', 'https://drive.google.com/open?id=1UXQf3UrabgFo6C-8Q2MK0z3m-w7ZS3b7'),
(15, '25869050013', 'ADRIANA BALOK', 'Perempuan', 'SEKOLAH DASAR NEGERI USAPIBAKI', '81246472264', 'PPPK', '1995-04-10', 'NUSA TENGGARA TIMUR', 'KABUPATEN TIMOR TENGAH UTARA', 'SD', 'https://drive.google.com/open?id=1DhGFi92ABZRAgnUNGaYXQkoZUmJMxT45', 'https://drive.google.com/open?id=1qvvFwDs_ycnhwTR8JXHFJpe1yTtuNT72', 'https://drive.google.com/open?id=1dbxzIBdiSwDFM1Gu49iAayGgiCodwRXs', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1mZ7qqZtGm-uvVmWOxw_3tg0_poBptIFe', 'https://drive.google.com/open?id=11srdmgnWso0iv9HSJTpPNNkDIHzcjBBC', 'https://drive.google.com/open?id=10VVloHixnk24Tp2Y6eteECY-IJNxkwli', 'https://drive.google.com/open?id=1Jf0Hn6AJJuKrQFNZ8cxkNL89HdOhBMGv', 'https://drive.google.com/open?id=16rlBT6l6k_sofupEDwjfNJbnsg5HBeDg', 'https://drive.google.com/open?id=1fN3l9qgFSYL9J9zJC8CropeNfyLEPBHx', 'https://drive.google.com/open?id=1xQeSmpA4Y-G6me6rWbJVj1R5oTwswvuG', 'https://drive.google.com/open?id=10xDtqi10uWDlcZjtZFthd8xCDFwv_bbi', 'https://drive.google.com/open?id=1pQjTfmNuZ32e_lYZjpnrK_oiNsg0O693', 'https://drive.google.com/open?id=1DdFxeacsS9bhqEcrrD_kxAzbeB_7WV8h', 'https://drive.google.com/open?id=1WGLJNCtmsEGf0USMbljIDGaQOff-zGYS', 'https://drive.google.com/open?id=1PJRR0_rAam-oo4PCAb4MxuNevMUcIUYU', 'https://drive.google.com/open?id=1bQ_LCSs2XyXNWygUjn5b_uwGbwZXGYTp', 'https://drive.google.com/open?id=1N_zmXMlFSfd-CsxcbY4nnqMhJq40ccaQ', 'https://drive.google.com/open?id=1NkDD3Ztusqy8-ePLfbG1hLnS68Z-q3j1', 'https://drive.google.com/open?id=1U6zj-qnj9j1Bs9PpRseDQtBmhzsEZKgL', 'https://drive.google.com/open?id=156YcXf-38L3GxgiJe8UIO_g7TeWAAmza', 'https://drive.google.com/open?id=1HUz244d-aQza_iA3qPhL6u0a1TU6eJK6', 'https://drive.google.com/open?id=1r-8LSe-hEt3L81_AIW7nvWt4V27CIlKV', 'https://drive.google.com/open?id=1uQZ0TEJAdx77S7q2TvkmyM_FWZWSFPQS', 'https://drive.google.com/open?id=1k-SMZL1tCfGTMwHHUxrlNalH6wR9SIoA', 'https://drive.google.com/open?id=15OYURpEfhAKYi8OuR52Fx8m6PyD4GkG2', 'https://drive.google.com/open?id=1iWqGoef1Y1t-scJznAGGqYAZN_lcmyPg', 'https://drive.google.com/open?id=1jqq2sZL47A5fUXzhXBPjVawdvujLc7ek'),
(16, '25869050014', 'ADRIANA FARDINA', 'Perempuan', 'SD INPRES WATU TERE', '81236112567', 'NON ASN', '1990-06-07', 'NUSA TENGGARA TIMUR', 'KABUPATEN MANGGARAI BARAT', 'SD', 'https://drive.google.com/open?id=1oNavXSUC1eADWa5val20w1ZbogJLJdtL', 'https://drive.google.com/open?id=1pxki6LYttsAxIUEOUG1Nq9wvYuBUC9qJ', 'https://drive.google.com/open?id=1qXzNXXLKm0wfM74ukagWHaS2g8mwevcf', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1Bhgx87anVKa5Q-MVqgnUxkG3zQeIPs0N', 'https://drive.google.com/open?id=1O8pHqooep0tWALFnqkuSEXX5z26ytPV9', 'https://drive.google.com/open?id=132EjzejjnFq1TaOE9vNyXMIC3oxSTPxl', 'https://drive.google.com/open?id=1dvkmy7t5_VqdRIPNXP_QsmURB8Prs7iB', 'https://drive.google.com/open?id=1cp88mJNJzBUQWfihCXfF-y1RYCjQXpn0', 'https://drive.google.com/open?id=1AB51CAvxGt3qkmHaWIlmi_wJHpV4wPCO', 'https://drive.google.com/open?id=1cVZY8kVaW29GdXLvezUu9jSOptojcLOj', 'https://drive.google.com/open?id=1f3G2986uXWG2FCZCI7IZzJp-GQAhdBzq', 'https://drive.google.com/open?id=1zmz_6JfM2VGrtIoNkt8o4tVWH5oZiP6t', 'https://drive.google.com/open?id=1pBkUkUkCoEXD_ZkJ_--9S8sTT_QGAn-J', 'https://drive.google.com/open?id=1z87TSownPBCO9a9g0PjrmLyQwww-Npom', 'https://drive.google.com/open?id=17Oyf_vv68rBhYeTFyQMr0dRhIz252EgU', 'https://drive.google.com/open?id=1RkqIGXt3Rs16hemfe151LkpuF5dwbARv', 'https://drive.google.com/open?id=12gF9oa_K6yoL5YfRbaUY392Xf3cdCpva', 'https://drive.google.com/open?id=1TrVMLGUwLbiuwAsdUw0n4IEFVAQHZenV', 'https://drive.google.com/open?id=1hpvH9a6UsJuq9NP0DBV0A5XabYkjfkpe', 'https://drive.google.com/open?id=1D3bfa9rLTrY5s5Ys1IlYlpggqlIDcGb0', 'https://drive.google.com/open?id=1_8_ZVBBhN06mMSY2sLvk-IMsKMglnHSn', 'https://drive.google.com/open?id=1GhwVdJxDd9ieDMSIBrPlD7GHvxD1IZ61', 'https://drive.google.com/open?id=1Bk1kulJBtNefelIzIgiCJ2e0AffcOP4l', 'https://drive.google.com/open?id=1UiglxYf1PY_MaBmcwvfxmNrBjoi1Qoay', 'https://drive.google.com/open?id=1scXySyDFqF3EE5_U5I0rinqm76Qkkbdf', 'https://drive.google.com/open?id=1aBJM2QCXQ9-DLinGNndCCbot2Z09wlve', 'https://drive.google.com/open?id=1ofZgU7SHFeYvAVg7Yd59uhzjINySiLgC'),
(17, '25869050015', 'ADRIANA KOLO', 'Perempuan', 'SMP NEGERI SATU ATAP OENAES', '82146674992', 'NON ASN', '1992-04-04', 'NUSA TENGGARA TIMUR', 'KABUPATEN TIMOR TENGAH UTARA', 'SMP', 'https://drive.google.com/open?id=1ggsfI-hTqrkpaZ6CyrhprDLiHR1MIL6L', 'https://drive.google.com/open?id=1Oa4ahQQuSOBBbkde9LIHSFWfaneUMll4', 'https://drive.google.com/open?id=1PgehWB5P83rzXm9kMkD6qCB8RHL0Ne3i', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1FPgNuWowMZISr3iE59r2p7qf1JcnhMqi', 'https://drive.google.com/open?id=1o5fLDeK0Dbzobgd91_wtWWii9IZfeVWm', 'https://drive.google.com/open?id=1MDDzOjz_JHpOM8V_rjf69wqPdHU7qDcb', 'https://drive.google.com/open?id=1NdSq61gWP0vbHlkTJGA41i-4cbBdosB7', 'https://drive.google.com/open?id=1AXm71RJ83gsylVDLn6aSFEJNtwrq7qzm', 'https://drive.google.com/open?id=1i3JJfLXrf-fA3NOu6Ouy5QlK4YONayRd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1r21drPHbUXIGmWHuwFa65AKXTclBnxgg', 'https://drive.google.com/open?id=1elVH_vwJU873bsdBq8keW7ltMKAS9Sz_', 'https://drive.google.com/open?id=126Yc1U-ZS0XlIblt7fWyJyvQN0T-c73y', 'https://drive.google.com/open?id=1skG_jgOks-9-wz_JL1mRJYEitk8znjSe'),
(18, '25869050016', 'ADRIANA MULIATI', 'Perempuan', 'SD INPRES NEMPONG', '81338511856', 'PPPK', '1984-05-30', 'NUSA TENGGARA TIMUR', 'KABUPATEN MANGGARAI TIMUR', 'SD', 'https://drive.google.com/open?id=1g774UE45HyQmJF8v4BCCh144wn5Bbtg6', 'https://drive.google.com/open?id=1TBKcM42HORgB16leN-tZV7-2cNxgJx1l', 'https://drive.google.com/open?id=1RKsgceqfivKaYnw6udSkrXLv3IpedNnd', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=10iTY7tW_kft_3uSQ6sUBmAKJHJ_pd5eO', 'https://drive.google.com/open?id=13T3PKHI38luO5DcWzRNIem9nUgAODUec', 'https://drive.google.com/open?id=1MjlBIaSJmbH4HJadheVOnzOFr_kg9qak', 'https://drive.google.com/open?id=1OXz2u3OTwZjZ2o4g_BYknlz2v4Wzb3Bd', 'https://drive.google.com/open?id=1SgUBRAylatuYuOjOACyahYSgswllY1E1', 'https://drive.google.com/open?id=1emgkxhlRjPxWNoLSvlZcIbb10Ca6eNTm', 'https://drive.google.com/open?id=1uSptiKVNUBmLbPhTCQx34ZQzBufIIOb8', 'https://drive.google.com/open?id=1nGTGoymY3Ith2OqaZRwDNKfVoNfsyzAH', 'https://drive.google.com/open?id=1QL8_88DaNLAqRzcvV1NRtzHZNGrRdYP4', 'https://drive.google.com/open?id=17uZaDvm3XQDT1X4lVwwgFqgt1jtudC4v', 'https://drive.google.com/open?id=1JevqlD2Qka2ouJT17M0SOGq6SqoddZS3', 'https://drive.google.com/open?id=1myNElkdunbh4mKIR9-mpj2L4MxnJ4iI8', 'https://drive.google.com/open?id=1uBrJX0or2ccJPfkT6VPb7TS9FMeTO0kP', 'https://drive.google.com/open?id=1UTH1okQJy4WbMNJg_lQNz8-Tzgs5HhRK', 'https://drive.google.com/open?id=1hzK4jR0dTGe-1GYluljya4z-PaPvQ0Ll', 'https://drive.google.com/open?id=1AsuaTneKs4LAhqrCjEeWjg0GKkMKHsZD', 'https://drive.google.com/open?id=1IPFXM920sv77lFGHc4oOxwl4znwH0rlB', 'https://drive.google.com/open?id=1DA1qIEeE4qsfYNgeXkePsX9thP7BRga9', 'https://drive.google.com/open?id=1BzAsVzSIMrQ6BuwMTKyh47f0DDaTQuJK', 'https://drive.google.com/open?id=1qKBsy2nZzyw2XHFdy7LHlwZ-oKBADH9F', 'https://drive.google.com/open?id=1CS6fDYmfodCpOEe_h4uhAmQUne74_f9V', 'https://drive.google.com/open?id=1ApAhMgnJbQnFd0C77_OQuBJxxUsO3WXh', 'https://drive.google.com/open?id=1W9PRksDMOh74Dgen0Wj8yLKxEN-vWTHY', 'https://drive.google.com/open?id=1JHHpDZWEnvYlyH3hwvsfQ6ZvpYvw03SY'),
(19, '25869050017', 'ADRIANA PENU', 'Perempuan', 'SMP NEGERI 19 KUPANG', '81317126754', 'PPPK', '1992-08-16', 'NUSA TENGGARA TIMUR', 'KOTA KUPANG', 'SMP', 'https://drive.google.com/open?id=1P6t37lP05T-NywtJTkGGdm5_4CnukfX0', 'https://drive.google.com/open?id=1z1OctZv1ov2INFaYwT-TEzfG3u60xT-3', 'https://drive.google.com/open?id=1kHZ3tHVB_NjJlowRkSzibs6qncaKphTT', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1Baqp-ije_gjvBNPhW4EQzjhW7AX7ZUje', 'https://drive.google.com/open?id=1pZpoNJE3zVYw_xLD4hgqLpeiQlBay35f', 'https://drive.google.com/open?id=10ZrXfoOHv3u78c7tIZBxxqDgGEGxJRB_', 'https://drive.google.com/open?id=13fPBi26VW7WzJ7fNqe_DwpCu3LrMUtF1', 'https://drive.google.com/open?id=19vK_eIMMTd9Yi08onGTjeITmJRezQxPG', 'https://drive.google.com/open?id=1V3QTh1yWw0SM8_cOcdtVMBsAeYE4z8KA', 'https://drive.google.com/open?id=1g4sTaWbQCcO3uEdRLDBTQYh-0eYiuowR', 'https://drive.google.com/open?id=17-FcD-WEP2VaBLuucBM_MWhsbz7zi7Jf', 'https://drive.google.com/open?id=1gaCrPU7Qk6-se6ilf6WR448pTbLNK032', 'https://drive.google.com/open?id=1h0JnZDWh800ObaIMKwXTppIN5BLyNC3C', 'https://drive.google.com/open?id=17nG95J1JE5E-CtkkzV2zX6iABbYiHI4h', 'https://drive.google.com/open?id=1OyVhGW3ggmTlzJf5bkexPS5jR3eXY6WL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1U-17fg7uTrudpH_loLx9ICLLVaexdZQA', 'https://drive.google.com/open?id=1gSvGV5Vzhm_dgW7LnsuVBWVqp7hF6IeB', 'https://drive.google.com/open?id=1rsgCeIjVvQRvlzjXQWVt69DX2jN8kKc4', 'https://drive.google.com/open?id=1h5LyDA1L0fnRuAejy4qu6q7SfbWvK1z5'),
(20, '25869050018', 'ADRIANA SAIMAN', 'Perempuan', 'SMA NEGERI 5 KOTA KOMBA', '81246395027', 'PPPK', '1986-04-25', 'NUSA TENGGARA TIMUR', 'KABUPATEN MANGGARAI TIMUR', 'SMA', 'https://drive.google.com/open?id=1IDMWIjbkZ5x7LoBeNN2NSA5l2abUuqIf', 'https://drive.google.com/open?id=11X2ZPtJSBKyWiod-QRLT81HInHdsDq9p', 'https://drive.google.com/open?id=11bsSV_g7vwheSe-0zX9owFpl43EK_UVp', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1CUoOcP8EdQjUpcKQ57KFrq1cUnjIgaeG', 'https://drive.google.com/open?id=1eAKe7tUnX-RuFJxooDzilYOmTXI8qc-q', 'https://drive.google.com/open?id=1QSqe4mkHO2vp4Nic6360aDLJXE0mWTZ3', 'https://drive.google.com/open?id=1mnfGqgAeLP1jxHUwGnaqBDDZAiTs8OtC', 'https://drive.google.com/open?id=14kIparBtAwumQGOr0DG-eF1YnmQhAS1K', 'https://drive.google.com/open?id=1dp7uXr9ZGuUDHOZ4TniKQjvjaPPPfsjE', 'https://drive.google.com/open?id=1_Ii3LTFIbo5KU6CAojuBUTKFNFCm3q09', 'https://drive.google.com/open?id=12MkhXhu_dhbGDlggKMB1EPlSRwpqVVYn', 'https://drive.google.com/open?id=1iecqXFrvdDmnjezkWvhBL8D2uOo2U9k8', 'https://drive.google.com/open?id=1VyeHbddcgj9TBZDSejJw49kgL1BfvM3N', 'https://drive.google.com/open?id=1IWdCe3R8PdpFqrKiXvG56Qxzt0afZTuJ', 'https://drive.google.com/open?id=1ZtkE7mjeT3J8D1q3yqTwkTMinsByQjsc', 'https://drive.google.com/open?id=1OLJZVyIMJ8JrVecMsprCOk5ltOGigWKO', 'https://drive.google.com/open?id=1Qk4UHpzbe5c8kJLw4juBxj7skwk0dlxY', 'https://drive.google.com/open?id=1rs5B5olCV_Km9mAnamZmnKxjx7yfVyRE', 'https://drive.google.com/open?id=1H5uEtdxDJHtivEDLs8U4Iz53Kxbc-pq0', 'https://drive.google.com/open?id=19I0xeQIq4sMQcS1IH_ZOBSYTQhYtvtI3', 'https://drive.google.com/open?id=1UDMUZJHPn7w-jcKxzQAoDMRWnBXy_Pae', 'https://drive.google.com/open?id=19h8tnJTku81dDxeHYs7OWJxEidv26aCs', 'https://drive.google.com/open?id=1Iu5rY9bIZGTSG4ehMckuXZOaLcQVXkaA', 'https://drive.google.com/open?id=1pO87fl_cBctDF4d4KdFAvDaen4SCQUBq', 'https://drive.google.com/open?id=1uQeX9fj2HgPotR_LVIsUzORIqdMTwtMJ', 'https://drive.google.com/open?id=1SCYdxT1_7w-qu83Gk0BgYcpSz1-rooft', 'https://drive.google.com/open?id=1lJMrChBni-XTOP0ZgPSr7KwQ-aJxk4NN'),
(21, '25869050019', 'ADRIANA VENANSIA HOAR KLAU', 'Perempuan', 'SD NEGERI WEBRIANOMEN', '82128446824', 'NON ASN', '1995-04-01', 'NUSA TENGGARA TIMUR', 'KABUPATEN MALAKA', 'SD', 'https://drive.google.com/open?id=1K_ce4tzL-beuWzBWv-aVE1oeAV89ydCh', 'https://drive.google.com/open?id=1QDWr6yf7VrFqRvMjtxrcffrSP7oaw9To', 'https://drive.google.com/open?id=1U-Not8Zd29HOP6vKSjrqK_j3pL_COCdL', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1ICeew-phGOCsp3q1DcTuQLQe0ySFOg4Y', 'https://drive.google.com/open?id=1_WDxlrA99p3k1CLk8gTINupneoz88-SJ', 'https://drive.google.com/open?id=1tDfvhWOoGvX7tZmhGeytqkkiIMoYS7k7', 'https://drive.google.com/open?id=169cVuUSav2AGZTEeE3CjTEUiFBNSzxJ_', 'https://drive.google.com/open?id=1PEZFcaNELlb7YohqurpMSPgqXDq38mR2', 'https://drive.google.com/open?id=1y1bbfV9NBjfVKpl7JDP_lEHZF-oiVIlG', NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1R3vIzyUhd0jkkEI3nDpFuQpx8C7lZ7sy', 'https://drive.google.com/open?id=1bEMR-Bu1fjgYxQXybHIvnfOlCjt1BFod', 'https://drive.google.com/open?id=14WuJSfQLdDYO20hdm8UGEAsdHyTSLMyC', 'https://drive.google.com/open?id=1sQHTCv0msI2a3G8E9dbKRHq7N4BRVm_A', 'https://drive.google.com/open?id=1yxgY2gK1vPTYQHaht7zzBEJ6Nfhr1GEn', 'https://drive.google.com/open?id=14vzGgonUfLGKEj6RTEi5QOxpkjTz9mmy'),
(22, '25869050020', 'ADRIANUS ALIN BEREK', 'Laki-laki', 'SMP NEGERI 1 EDERA', '81240614688', 'PNS', '1988-08-11', 'PAPUA', 'KABUPATEN MAPPI', 'SMP', 'https://drive.google.com/open?id=1gFEikP8k-fG4IBbee95lHw8kcwxFORNx', 'https://drive.google.com/open?id=1nc82Pbv_TFGypkbl0PldE9ey2uxjgK14', 'https://drive.google.com/open?id=1YLA9QfqVb9uIF99chHWma5bTm1MWQ3oj', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1hCdHcVDt0fpJjuXTK3IlbdTBRCUSB-Nm', 'https://drive.google.com/open?id=1MPM2Umv2peZtvzXci6tnt2YlQ_hjPkAU', 'https://drive.google.com/open?id=1nq1J7Ki94j1kmkXnhC2ziom0Xji2ZK1-', 'https://drive.google.com/open?id=1za4W9wod67YpZyoEiuxHWEtXcYAxSMjq', 'https://drive.google.com/open?id=1bCLmFnvVrH9aOF6FD4lfoWrdhoOVdfaI', 'https://drive.google.com/open?id=18MUqpgZ2XIyc4NbxfyDQF3YW-08lgozO', 'https://drive.google.com/open?id=1rbDFDtSOGtyos4ZoSpb0McXZLZwblLrj', 'https://drive.google.com/open?id=19X04hE3fIdzovu3VbmnufEGY-BDTIBrc', 'https://drive.google.com/open?id=1MXnfRrTmlgTwdVP3c7olmw2WDRIR__9Q', 'https://drive.google.com/open?id=1mnfzE92t9FnqcKFRFTSXfAZX6T9PtagV', 'https://drive.google.com/open?id=1HQ3P8CWJZkvUAlFplXq3eND6lXygrzTq', 'https://drive.google.com/open?id=1i7IFXRdUcxQwYunJ1cqztKRUK4j4EZ5f', 'https://drive.google.com/open?id=1CrRJ7igzOjea_PGr2IdfaK6mtydLS253', 'https://drive.google.com/open?id=1CdKHcXTmBuGtTUa6Zk9QuVjfjPopgFiu', 'https://drive.google.com/open?id=1Yph_MOcyoC6aw2JdW70fuSncBGjSlor2', 'https://drive.google.com/open?id=1QVux2h-ylWcx2PalPDc31snwIYaNA9S3', 'https://drive.google.com/open?id=1QNgWRb0qskI5dgX5r1dEo7BgNFjpW95K', 'https://drive.google.com/open?id=1r3AuA9JQtR1tccWac4-tBAbTGowu2VAK', 'https://drive.google.com/open?id=1nzcFKa688Ru7U4Dg1eiYXzlp7M3xjq2X', 'https://drive.google.com/open?id=10RWo5_JOPwWpey-NzAP7eo8Lky0noxZw', 'https://drive.google.com/open?id=1tPQuf-AtbA4_iZYc2u0CQi9DHO47hk0C', 'https://drive.google.com/open?id=1m0alswa16zr7LAj8etWxpZ5LnM-qBr1G', 'https://drive.google.com/open?id=1eh7PVN_UdLKOW5JinDfAzLEAWoeNGRdP', 'https://drive.google.com/open?id=16RkHeAT9a0hD7vCa5gjoiQpIx2YtMpb6'),
(23, '25869050021', 'ADRIANUS JEHAMAT', 'Laki-laki', 'SD INPRES ROBO', '85337030528', 'PPPK', '1990-08-23', 'NUSA TENGGARA TIMUR', 'KABUPATEN MANGGARAI', 'SD', 'https://drive.google.com/open?id=1hRG-cfMCh6ag0yksfnmENoiylrtQYDyy', 'https://drive.google.com/open?id=1abFwkwJxRvTf0MqGmiopTGuLUUJ721pa', 'https://drive.google.com/open?id=1qtFy5xWhg_9llmUQzoGQI_BKTB-vd1_q', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1ahWqKh7GSI264phJY8GaBEgvEuKpXBZO', 'https://drive.google.com/open?id=1DvyPQHFgNRN4wPDmMNEdnJHo1_FgCZHx', 'https://drive.google.com/open?id=1tHW7t1tQl9navudteMvMFPJ8kKzEMsKA', 'https://drive.google.com/open?id=1WlJXKRqbg9gsrtGxn3Uq9KnSD9_JZMZ2', 'https://drive.google.com/open?id=1nb9Bcqaota2UYqdAqixp0c7ZTIxNOK_u', 'https://drive.google.com/open?id=1BLzIH7MWDpPRyLGsNBnFa_yC14T7gX-R', 'https://drive.google.com/open?id=1dOMpZI8iM-T_IBCrZ6EPcEu3KuxtgIcl', 'https://drive.google.com/open?id=1Ot553H_vfyrRx0V-1t3bmScVnGxx6PEq', 'https://drive.google.com/open?id=1CbAl7gZemNtSC1-q8pKocsQs8kwZ8uPb', 'https://drive.google.com/open?id=1iyBzzQ-b_EsDHsjfgr5cMZrIS-Ia5gWt', 'https://drive.google.com/open?id=15rRPrJTFDterr-2Cp2ZMYo9_StjeCCWA', 'https://drive.google.com/open?id=1oZRbosdtUa1vkHqITUENqEhFBYY5Q7PS', 'https://drive.google.com/open?id=181VvQmII7YHPKDxDg8t8zd1qMjZJwGvy', 'https://drive.google.com/open?id=1ZGOXzmMKRPrUPFixp8DHFYxp_hQV3th7', 'https://drive.google.com/open?id=1szCO85IzFxuVU7YDmLunlY49NgO70HTH', 'https://drive.google.com/open?id=1AodFB8tbZZuCjanHY19hcUFJpxzXOHT6', 'https://drive.google.com/open?id=1qAYNKIqT-kJre8PMAEZpl9oNFOkjyMKx', 'https://drive.google.com/open?id=1Xy_k5wY1RRo-CCMLJgRmdQU4K_P4UqpR', 'https://drive.google.com/open?id=1HA1FUNzhVyZq7dEPUsQfXk84EXaWRKjd', 'https://drive.google.com/open?id=1mKR6f7Gn5AfKBd7qBQ8oquP3ASzmYrqg', 'https://drive.google.com/open?id=1vfBwE8fXfTkheXY9BWWvBEuS78UHIjX1', 'https://drive.google.com/open?id=1reolKTtToSKwkA-wgjZ891uN0wohnmwl', 'https://drive.google.com/open?id=1BrYUBwg6z0bzZuvEYANPEvIJBDo66PM7', 'https://drive.google.com/open?id=1Zv7gWI8c49DxdWu0TZ71f0efM1rxUiB-'),
(24, '25869050022', 'ADRIANUS KIIK', 'Laki-laki', 'SMA NEGERI 2 TAEBENU', '81353528122', 'PPPK', '1992-12-16', 'NUSA TENGGARA TIMUR', 'KABUPATEN KUPANG', 'SMA', 'https://drive.google.com/open?id=1HfG_6YD5lfIjy4wMVHsSfXxDZf7x2k_P', 'https://drive.google.com/open?id=1EDCKm82z04S8yqpBRB2V4aLOE5kT_WDu', 'https://drive.google.com/open?id=1-RFPCJA3E2BEfCjK6ljTylhgonE7itOz', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1FDA-7sh8UgR7DVjOi-frt8Ol3PlMjipN', 'https://drive.google.com/open?id=1GQ-5pLXkD3mNfil1hilssqs20OV6sJTH', 'https://drive.google.com/open?id=1NMUS5RVFYwIiGVtacyPEaFwSaIgbgSTj', 'https://drive.google.com/open?id=1NkBY3ENpYudJ9EMSh943f55MB1NZvsnc', 'https://drive.google.com/open?id=16PUeDn-mbaQTGFwMzmgPjvLszXP0SsB4', 'https://drive.google.com/open?id=1QF-Xo0m2LWoTpq4MeeZpqCG0kH8-DXJH', 'https://drive.google.com/open?id=1jHFithmeBq78HiThVm9KfmG-O_1acaN9', 'https://drive.google.com/open?id=1-bje5ZYdIc_PJKylGK8glhWjUUJEaRIb', 'https://drive.google.com/open?id=1gKVhD8jqTCq0IbW9fHiLBPU1UWwYny1G', 'https://drive.google.com/open?id=1fU-vfHIpSv9zT9-HUE5SlTQk3zCbDpPl', 'https://drive.google.com/open?id=1xjF_rx4izin3rGbA3He2D9AA9foXnk03', 'https://drive.google.com/open?id=1H-dwN82BHyfZY7JSeP8vof6zUTr8LVUe', 'https://drive.google.com/open?id=1FpWzXXoulO51HiUa6akmRSR1t2qVZ2Gw', 'https://drive.google.com/open?id=1PHMh197uhouNwkOIbEAY8iLEKjDyTPWH', 'https://drive.google.com/open?id=1QPyS9QIdWtTqH6Qn6U73jjDyoY6vFuEP', 'https://drive.google.com/open?id=17LAzDeEEsIlgNN27i_OAXyB6AQQc9p9K', 'https://drive.google.com/open?id=1x6AlL6y0folV4CX6E3Knz_REYZefpQ-a', 'https://drive.google.com/open?id=1pOa8Z5t4oVKCgewtEK22TDIej70ymDSY', 'https://drive.google.com/open?id=1Jp0yyVxjU90Su8dWzSFeAk0rzHQnGUiw', 'https://drive.google.com/open?id=1lx1Z1kqAFiGyb1vu-puNhXmZ5kYXOuW9', 'https://drive.google.com/open?id=1IkJVotvXGT4G9BqMpWWi5kAO9pQWE_uZ', 'https://drive.google.com/open?id=1GYgiYHIo9pf5H26vi4zwUDbEh7PzZtp2', 'https://drive.google.com/open?id=1cxbm6j0UQ5yTr0x8c1OOI3JWlqzAyQQ6', 'https://drive.google.com/open?id=1jROt7oFA4hzHxIWui6V_iZJvHh4-1jaq'),
(25, '25869050023', 'ADRIANUS LAZARUS FIOS', 'Laki-laki', 'SMA NEGERI NOEMUTI TIMUR', '85239212333', 'PPPK', '1980-04-23', 'NUSA TENGGARA TIMUR', 'KABUPATEN TIMOR TENGAH UTARA', 'SMA', 'https://drive.google.com/open?id=1BfCrEg5T4qKIBS9pObt31Q_BS9eYwPO3', 'https://drive.google.com/open?id=1aQfonYXt4XAfqTQ0TzC5qYO_T3aAfjtt', 'https://drive.google.com/open?id=1q2ByorHfe1fzOJacdFT9ziPjHhItHy-y', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1cbEDvWoQZ1YW5jPggyMPQdYvnvhQH1HF', 'https://drive.google.com/open?id=1pC3lOtdoFkVvBGcA1YBakHD3w1MFWdEM', 'https://drive.google.com/open?id=1Cu084ZteNTv2Uw6-J_ZL-rd3PdK1Ags9', 'https://drive.google.com/open?id=1PSbPO7ZvjcOqFDUz7BaacIgvZ7667xwZ', 'https://drive.google.com/open?id=1uHVjCjIO04Gg-LYLDaWA7xMlGYf9e2vs', 'https://drive.google.com/open?id=1-fzl-qAolqk5Qaq6iUt2LORwM_Y0hPXB', 'https://drive.google.com/open?id=1bjC3UEZaXekavCH5JoB9FdJKUD6qm2Ui', 'https://drive.google.com/open?id=1QQNZD2lx-v5IZeAWSZqQiObqzy2fG2MN', 'https://drive.google.com/open?id=15pWhSxS7p2i0q7HQL68fLaTSi0p3GH5U', 'https://drive.google.com/open?id=12p7bsWyfKHfADGtlc3YKz3GFusbDuD_Q', 'https://drive.google.com/open?id=1DPbVOKDkQjmY2XgZTJW6oKpmn66nCopM', 'https://drive.google.com/open?id=1rlS_c99ynvODcChsSFSa2rATv-Q_muPM', 'https://drive.google.com/open?id=1zLpHIradJZiuI-77aTrzqyO8XepBgfNq', 'https://drive.google.com/open?id=1Tezvq6AHLrE2LQioOp6X8Hu5g0SWP55I', 'https://drive.google.com/open?id=1FJTrkGxjdfy3wSB2CFxZsV52Vg5LxmVz', 'https://drive.google.com/open?id=1WeIrW4K2v_des_jj1MbQCwZBmO371KbQ', 'https://drive.google.com/open?id=1L1qd-FmJc5ggtG0fET472BM5VHL0em8w', 'https://drive.google.com/open?id=1hhTn0kRnUVqek5tD7ng_0Mbigg8GfA9b', 'https://drive.google.com/open?id=1AittXrr_GCEcZ-R3BCUdeRrzQ2V7gwCT', 'https://drive.google.com/open?id=1BnD7xYHOF32YKOa4Wa8JPt7pkGqJYskP', 'https://drive.google.com/open?id=15PMEZyMdkWuguBsdU3TSyp_hWPrFrWn7', 'https://drive.google.com/open?id=1h0-VWW_fCyV2BP9tfqeXxbzWqxKAHf6p', 'https://drive.google.com/open?id=1M1yYcPPvYXT3b3VsQA2wRKFS-qBXZZet', 'https://drive.google.com/open?id=15q-a0MYWYyvKI1UYaK18fYcwRF02TFE1'),
(26, '25869050024', 'ADRIANUS MADI', 'Laki-laki', 'SMP YPPK YOHANES PALUS II OBAA', '81238341436', 'NON ASN', '1986-04-03', 'PAPUA', 'KABUPATEN MAPPI', 'SMP', 'https://drive.google.com/open?id=1Nn3Vpg3XKBeFnVc1WxmvYhTS7j3_Je9k', 'https://drive.google.com/open?id=1z70LPmBI4JBh4HcljyIkkoRB1MskuLus', 'https://drive.google.com/open?id=1RzDqRMpgqnidqmLs2FVxWjRt0dnvUSD3', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1D6GW8i4s-kUOtKZAT4wH6zbVOpTVoVIi', 'https://drive.google.com/open?id=1K-duNcK1osubRyRbb5dXLO58zTyCiEvY', 'https://drive.google.com/open?id=1Ji9F4naiBbUgvfr4_fMq8-52RUzxGLKU', 'https://drive.google.com/open?id=13wEYMTILgyLhtWbb58077VBuijbg29tT', 'https://drive.google.com/open?id=1tervumSCxHzWsqauQUMVMsTsVmpfyYDJ', 'https://drive.google.com/open?id=1V_YUZp9p5dWNb54rX7hbs-Acnz9ol3sO', NULL, NULL, NULL, NULL, NULL, NULL, 'https://drive.google.com/open?id=1QL2Ks9e1Bkrys8g4-3GafKBRMfGxZ0DF', 'https://drive.google.com/open?id=1w5yqTpdYHr6Uuts1BNqljD8vZLqeOenk', 'https://drive.google.com/open?id=12D8Cf4x2-OZx6xjTI2LU-T3ecTQ2OYTX', 'https://drive.google.com/open?id=1F9sCtGCYPaJalgD7T_X5vsbXk4TVKBZK', 'https://drive.google.com/open?id=19bgA5TuosBeUPFlmqTN6IPmZe_kfoqGK', 'https://drive.google.com/open?id=1eNE7mRRwzrdgJhpsMnpWHwoMiVKHWs7q'),
(27, '25869050025', 'ADRIANUS NOMPIDURA', 'Laki-laki', 'SD INPRES LONGOS', '81337302578', 'PPPK', '1988-09-06', 'NUSA TENGGARA TIMUR', 'KABUPATEN MANGGARAI', 'SD', 'https://drive.google.com/open?id=1hsJPpldQMXJwwo6u-7_WY0Eg35-uf0T3', 'https://drive.google.com/open?id=1o2hdtTH1iY-cvchstlx5Uuk7FGT7iqPb', 'https://drive.google.com/open?id=1QMjHkd3WnKDWixI01V2_TPQtNsOXVYMl', NULL, 'belum_dinilai', '2025-09-21 11:59:44', '2025-09-21 11:59:44', 'https://drive.google.com/open?id=1zi4CtxerCDe9YPR-gb_jP2Civgt82ZIw', 'https://drive.google.com/open?id=1cbvc5kS48KvzDDBhBv1MXoBbN2vBABz9', 'https://drive.google.com/open?id=1D9gaauX0mUnQvtfQY2b402OubOQPUCDQ', 'https://drive.google.com/open?id=1Wgq9s_k46PWoJvKp01WM44Rm1iNF_06v', 'https://drive.google.com/open?id=1iEgHHMWovBRY9l6IcKeur7KgGdtuBtbe', 'https://drive.google.com/open?id=1ZAH6zgmXGG-EYbIV7fNaS8n5394QNh0i', 'https://drive.google.com/open?id=1-gmIqk8uLDpG4GtH30xvolzRFO2rz9Qp', 'https://drive.google.com/open?id=1_bBe70bpL_NnBmPRyzM5LGFaEUTWTOIx', 'https://drive.google.com/open?id=1dbTd3lpcMLSARdXdVHQ4W4T7ZxJQW4n5', 'https://drive.google.com/open?id=15ZwaH6DVsk2zrqG5cDKCpsf2R81sKDZg', 'https://drive.google.com/open?id=1MKxiitncFom_Fig7k7bM9UU9wHssx7x8', 'https://drive.google.com/open?id=1DA3i3kWqsbtmnbWZ1lqL2BysgvJKa9qD', 'https://drive.google.com/open?id=171Xm7wDmKpziTuRFHfEqrN9I5-bn1cka', 'https://drive.google.com/open?id=182_piXFmGFV6XGu83SOlcPhIWXA1g_Ai', 'https://drive.google.com/open?id=1xkwjWAzz3ESZ3gEIDjiJWyjmKRxeHndt', 'https://drive.google.com/open?id=1OImvSNIo9HbON7CbYLlEJq-evqxvtDRk', 'https://drive.google.com/open?id=1fT30w2tXxs4WE2WX609QtvoxjQT5afHG', 'https://drive.google.com/open?id=1IpcpEQdqM4Z0yZzm7mHNRr84qjrqkLEc', 'https://drive.google.com/open?id=1wtk83sO98cHLDm65CGYODr6WLipHU3nx', 'https://drive.google.com/open?id=1DBfM3eBXLRQC9yHb78yliM0tzB3qt1-G', 'https://drive.google.com/open?id=1MSH8BPd6xf1OOF5uhf_UjqKNxyjwT1OY', 'https://drive.google.com/open?id=1RFrAiWapZ_LhZjiZ6ge16vzWltF868ET', 'https://drive.google.com/open?id=1jhMSANhMM6y43nXL4HK_lBY_JsMwSigu', 'https://drive.google.com/open?id=1iAuVhVI9zO8hUJ6TrN2LJMWL2GrA0pHd');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penilaian_rpl`
--

CREATE TABLE `penilaian_rpl` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `dosen_penilai_id` int(11) NOT NULL,
  `rpl01_pedagogik` int(11) DEFAULT NULL CHECK (`rpl01_pedagogik` >= 0 and `rpl01_pedagogik` <= 100),
  `rpl02_perangkat` int(11) DEFAULT NULL CHECK (`rpl02_perangkat` >= 0 and `rpl02_perangkat` <= 100),
  `rpl03_profesional` int(11) DEFAULT NULL CHECK (`rpl03_profesional` >= 0 and `rpl03_profesional` <= 100),
  `rpl04_administrasi` int(11) DEFAULT NULL CHECK (`rpl04_administrasi` >= 0 and `rpl04_administrasi` <= 100),
  `rpl05_inovasi` int(11) DEFAULT NULL CHECK (`rpl05_inovasi` >= 0 and `rpl05_inovasi` <= 100),
  `rpl01_huruf_mutu` char(1) DEFAULT NULL,
  `rpl02_huruf_mutu` char(1) DEFAULT NULL,
  `rpl03_huruf_mutu` char(1) DEFAULT NULL,
  `rpl04_huruf_mutu` char(1) DEFAULT NULL,
  `rpl05_huruf_mutu` char(1) DEFAULT NULL,
  `status_penilaian` enum('draft','final') DEFAULT 'draft',
  `catatan_dosen` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `penilaian_rpl`
--

INSERT INTO `penilaian_rpl` (`id`, `mahasiswa_id`, `dosen_penilai_id`, `rpl01_pedagogik`, `rpl02_perangkat`, `rpl03_profesional`, `rpl04_administrasi`, `rpl05_inovasi`, `rpl01_huruf_mutu`, `rpl02_huruf_mutu`, `rpl03_huruf_mutu`, `rpl04_huruf_mutu`, `rpl05_huruf_mutu`, `status_penilaian`, `catatan_dosen`, `created_at`, `updated_at`) VALUES
(2, 3, 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', NULL, '2025-09-21 12:15:04', '2025-09-21 12:15:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','dosen') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator RPL', 'admin@stkyakobus.ac.id', 'admin', 'active', '2025-09-21 06:00:31', '2025-09-21 06:00:31'),
(2, 'dosen001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Contoh Dosen', 'dosen1@stkyakobus.ac.id', 'dosen', 'active', '2025-09-21 06:00:31', '2025-09-21 06:00:31'),
(4, '2717069001', '$2y$10$pN9762.s/6NsVH3DdmEIRuoLub/fgUDrviZ3Efa86ze7Cvp5vdoAa', 'Yohanes Hendro Pranyoto', 'yohaneshenz@stkyakobus.ac.id', 'dosen', 'active', '2025-09-21 10:54:14', '2025-09-21 10:54:14'),
(5, '2721128601', '$2y$10$/Y1Ycp518L/ba5NNm2XO/./ThznAKfappQ0qv2k9l/pfc7uh54tmq', 'Dedimus Berangka', 'dedimus@stkyakobus.ac.id', 'dosen', 'active', '2025-09-21 10:54:37', '2025-09-21 10:54:37'),
(6, '2717077001', '$2y$10$6TdfB/LDl/LHlZ0A/vx/WO4dPCGwk4lUSRKykl8X5F/fUY6RBh.Ty', 'Donatus Wea', 'romodonwea@stkyakobus.ac.id', 'dosen', 'active', '2025-09-21 10:55:32', '2025-09-21 10:55:32');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_laporan_penilaian`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_laporan_penilaian` (
`nim` varchar(20)
,`nama_lengkap` varchar(100)
,`jenjang` enum('SD','SMP','SMA','SMK')
,`tempat_tugas` varchar(200)
,`nama_dosen` varchar(100)
,`rpl01_pedagogik` int(11)
,`rpl01_huruf_mutu` char(1)
,`rpl02_perangkat` int(11)
,`rpl02_huruf_mutu` char(1)
,`rpl03_profesional` int(11)
,`rpl03_huruf_mutu` char(1)
,`rpl04_administrasi` int(11)
,`rpl04_huruf_mutu` char(1)
,`rpl05_inovasi` int(11)
,`rpl05_huruf_mutu` char(1)
,`status_penilaian` enum('draft','final')
,`tanggal_penilaian` timestamp
);

-- --------------------------------------------------------

--
-- Struktur untuk view `view_laporan_penilaian`
--
DROP TABLE IF EXISTS `view_laporan_penilaian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_penilaian`  AS SELECT `m`.`nim` AS `nim`, `m`.`nama_lengkap` AS `nama_lengkap`, `m`.`jenjang` AS `jenjang`, `m`.`tempat_tugas` AS `tempat_tugas`, `u`.`nama_lengkap` AS `nama_dosen`, `p`.`rpl01_pedagogik` AS `rpl01_pedagogik`, `p`.`rpl01_huruf_mutu` AS `rpl01_huruf_mutu`, `p`.`rpl02_perangkat` AS `rpl02_perangkat`, `p`.`rpl02_huruf_mutu` AS `rpl02_huruf_mutu`, `p`.`rpl03_profesional` AS `rpl03_profesional`, `p`.`rpl03_huruf_mutu` AS `rpl03_huruf_mutu`, `p`.`rpl04_administrasi` AS `rpl04_administrasi`, `p`.`rpl04_huruf_mutu` AS `rpl04_huruf_mutu`, `p`.`rpl05_inovasi` AS `rpl05_inovasi`, `p`.`rpl05_huruf_mutu` AS `rpl05_huruf_mutu`, `p`.`status_penilaian` AS `status_penilaian`, `p`.`updated_at` AS `tanggal_penilaian` FROM ((`mahasiswa` `m` left join `penilaian_rpl` `p` on(`m`.`id` = `p`.`mahasiswa_id`)) left join `users` `u` on(`p`.`dosen_penilai_id` = `u`.`id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `dokumen_perangkat`
--
ALTER TABLE `dokumen_perangkat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dok_semester` (`mahasiswa_id`,`semester`,`jenis_dokumen`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `idx_mahasiswa_nim` (`nim`),
  ADD KEY `idx_mahasiswa_assigned` (`assigned_dosen_id`);

--
-- Indeks untuk tabel `penilaian_rpl`
--
ALTER TABLE `penilaian_rpl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_penilaian` (`mahasiswa_id`,`dosen_penilai_id`),
  ADD KEY `idx_penilaian_mahasiswa` (`mahasiswa_id`),
  ADD KEY `idx_penilaian_dosen` (`dosen_penilai_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `dokumen_perangkat`
--
ALTER TABLE `dokumen_perangkat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2263;

--
-- AUTO_INCREMENT untuk tabel `penilaian_rpl`
--
ALTER TABLE `penilaian_rpl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `dokumen_perangkat`
--
ALTER TABLE `dokumen_perangkat`
  ADD CONSTRAINT `dokumen_perangkat_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`assigned_dosen_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `penilaian_rpl`
--
ALTER TABLE `penilaian_rpl`
  ADD CONSTRAINT `penilaian_rpl_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penilaian_rpl_ibfk_2` FOREIGN KEY (`dosen_penilai_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
