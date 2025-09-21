-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 21 Sep 2025 pada 17.31
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
(11, 2, 'Finalisasi Penilaian', 'Mahasiswa: ADELFINA FRANSISKA BRIA (ID: 1)', '36.90.146.195', '2025-09-21 10:30:21');

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
  `email` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `status_pegawai` varchar(50) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
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

INSERT INTO `mahasiswa` (`id`, `nim`, `nama_lengkap`, `jenis_kelamin`, `tempat_tugas`, `email`, `no_telepon`, `nik`, `status_pegawai`, `tempat_lahir`, `tanggal_lahir`, `provinsi`, `kabupaten`, `kecamatan`, `jenjang`, `link_sk_mengajar`, `link_administrasi`, `link_inovasi`, `assigned_dosen_id`, `status_penilaian`, `created_at`, `updated_at`, `rpl02_perangkat_ganjil_2019`, `rpl02_perangkat_genap_2019`, `rpl02_perangkat_ganjil_2020`, `rpl02_perangkat_genap_2020`, `rpl02_perangkat_ganjil_2021`, `rpl02_perangkat_genap_2021`, `rpl02_perangkat_ganjil_2022`, `rpl02_perangkat_genap_2022`, `rpl02_perangkat_ganjil_2023`, `rpl02_perangkat_genap_2023`, `rpl02_perangkat_ganjil_2024`, `rpl02_perangkat_genap_2024`, `rpl03_pengembangan_ganjil_2019`, `rpl03_pengembangan_genap_2019`, `rpl03_pengembangan_ganjil_2020`, `rpl03_pengembangan_genap_2020`, `rpl03_pengembangan_ganjil_2021`, `rpl03_pengembangan_genap_2021`, `rpl03_pengembangan_ganjil_2022`, `rpl03_pengembangan_genap_2022`, `rpl03_pengembangan_ganjil_2023`, `rpl03_pengembangan_genap_2023`, `rpl03_pengembangan_ganjil_2024`, `rpl03_pengembangan_genap_2024`) VALUES
(1, '25869050001', 'ADELFINA FRANSISKA BRIA', 'Perempuan', 'SMP NEGERI HELIBAUK', 'adelfinabria24@guru.SMP.belajar.id', '081237045344', '5304084204950007', 'PPPK', 'WEDARE', '1995-04-01', 'NUSA TENGGARA TIMUR', 'KABUPATEN MALAKA', 'MALAKA TENGAH', 'SMP', 'https://drive.google.com/open?id=1-J--zMVJA-BkPY8ScbPEvHm6ULAXCiNl', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 2, 'selesai', '2025-09-21 06:44:14', '2025-09-21 10:30:21', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm', 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm'),
(2, '25869050002', 'ADELHEID SAINA', 'Perempuan', 'SDI LELIT', 'adelheidsaina34@guru.sd.belajar.id', '081338731398', '5310156304910001', 'PPPK', 'GENCOR', '1991-04-22', 'NUSA TENGGARA TIMUR', 'KABUPATEN MANGGARAI', 'SATAR MESE BARAT', 'SD', 'https://drive.google.com/open?id=11wJjdCwaKHTj2TLno_53iclac6b8hTKY', 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', 2, 'belum_dinilai', '2025-09-21 06:44:14', '2025-09-21 10:14:07', 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', NULL, 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', NULL, 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', NULL, 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', NULL, NULL, 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So', 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', NULL, 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', NULL, 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', NULL, 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ', NULL);

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
(1, 1, 2, 80, 70, 80, 75, 89, 'A', 'B', 'A', 'B', 'A', 'final', 'Penilaian selesai', '2025-09-21 10:26:58', '2025-09-21 10:30:21');

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
(3, 'dosen002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Contoh Dosen 2', 'dosen2@stkyakobus.ac.id', 'dosen', 'active', '2025-09-21 06:00:31', '2025-09-21 06:00:31');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `penilaian_rpl`
--
ALTER TABLE `penilaian_rpl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
