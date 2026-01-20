-- ============================================
-- SQL untuk Modul Surat Keterangan
-- Sekolah Tinggi Katolik Santo Yakobus Merauke
-- ============================================

-- Tabel untuk menyimpan data surat keterangan
CREATE TABLE IF NOT EXISTS `surat_keterangan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_surat` (`nomor_surat`),
  KEY `idx_nim` (`nim`),
  KEY `idx_jenis_surat` (`jenis_surat`),
  KEY `idx_tahun_periode` (`tahun_periode`),
  KEY `idx_tanggal_terbit` (`tanggal_terbit`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Tabel untuk menyimpan riwayat surat keterangan bebas perpustakaan';

-- Index tambahan untuk performa query
CREATE INDEX idx_nim_tanggal ON surat_keterangan(nim, tanggal_terbit);
CREATE INDEX idx_tahun_jenis ON surat_keterangan(tahun_periode, jenis_surat);

-- Insert contoh data (opsional, bisa dihapus)
-- INSERT INTO `surat_keterangan` 
-- (`nomor_surat`, `nim`, `jenis_surat`, `tanggal_terbit`, `tahun_periode`, `status`, `admin_id`) 
-- VALUES 
-- ('001/SKP-PERP/STK/I/2026', '2202034', 'UAS', '2026-01-20', 2026, 'terbit', 1);

-- ============================================
-- Catatan Penting:
-- 1. SQL ini AMAN dan tidak mempengaruhi tabel lain
-- 2. Menggunakan IF NOT EXISTS untuk menghindari error
-- 3. Index sudah dioptimalkan untuk query cepat
-- 4. Charset utf8mb4 untuk support karakter Indonesia
-- ============================================
