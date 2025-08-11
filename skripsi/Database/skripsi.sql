-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 11, 2025 at 08:11 AM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`stkp7133`@`localhost` PROCEDURE `CleanupExpiredValidation` ()   BEGIN
    DELETE FROM validasi_dokumen 
    WHERE expired_at < NOW() 
    AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH);
    
    SELECT CONCAT('Cleaned up expired validation hashes: ', ROW_COUNT()) AS result;
END$$

--
-- Functions
--
CREATE DEFINER=`stkp7133`@`localhost` FUNCTION `check_jurnal_requirement_mahasiswa` (`p_proposal_id` BIGINT) RETURNS LONGTEXT CHARSET utf8mb4 COLLATE utf8mb4_bin DETERMINISTIC READS SQL DATA BEGIN
    DECLARE v_count INT DEFAULT 0;
    DECLARE v_result JSON;
    
    -- Hitung jurnal yang sudah divalidasi dari existing table
    SELECT COUNT(*) INTO v_count
    FROM jurnal_bimbingan 
    WHERE proposal_id = p_proposal_id 
    AND status_validasi = '1';
    
    -- Buat result JSON
    SET v_result = JSON_OBJECT(
        'eligible', IF(v_count >= 8, TRUE, FALSE),
        'jurnal_validated_count', v_count,
        'minimum_required', 8,
        'missing', GREATEST(0, 8 - v_count),
        'message', IF(v_count >= 8, 
            'Memenuhi syarat untuk mengajukan seminar proposal', 
            CONCAT('Perlu ', (8 - v_count), ' jurnal bimbingan lagi yang divalidasi dosen')
        )
    );
    
    RETURN v_result;
END$$

CREATE DEFINER=`stkp7133`@`localhost` FUNCTION `check_jurnal_requirement_seminar_skripsi` (`p_proposal_id` BIGINT) RETURNS LONGTEXT CHARSET utf8mb4 COLLATE utf8mb4_bin DETERMINISTIC READS SQL DATA BEGIN
    DECLARE v_count INT DEFAULT 0;
    DECLARE v_surat_izin_approved BOOLEAN DEFAULT FALSE;
    DECLARE v_result JSON;
    
    -- Hitung jurnal yang sudah divalidasi minimal 14x
    SELECT COUNT(*) INTO v_count
    FROM jurnal_bimbingan 
    WHERE proposal_id = p_proposal_id 
    AND status_validasi = '1';
    
    -- Cek apakah ada surat izin penelitian yang disetujui
    SELECT COUNT(*) > 0 INTO v_surat_izin_approved
    FROM permohonan_izin_penelitian
    WHERE proposal_mahasiswa_id = p_proposal_id
    AND status_izin_penelitian = '1';
    
    -- Buat result JSON
    SET v_result = JSON_OBJECT(
        'eligible', IF(v_count >= 14 AND v_surat_izin_approved, TRUE, FALSE),
        'jurnal_validated_count', v_count,
        'minimum_required_jurnal', 14,
        'missing_jurnal', GREATEST(0, 14 - v_count),
        'surat_izin_approved', v_surat_izin_approved,
        'message', CASE 
            WHEN v_count < 14 AND NOT v_surat_izin_approved THEN 
                CONCAT('Perlu ', (14 - v_count), ' jurnal bimbingan lagi yang divalidasi dosen dan surat izin penelitian yang disetujui')
            WHEN v_count < 14 THEN 
                CONCAT('Perlu ', (14 - v_count), ' jurnal bimbingan lagi yang divalidasi dosen')
            WHEN NOT v_surat_izin_approved THEN 
                'Perlu surat izin penelitian yang disetujui dosen pembimbing'
            ELSE 
                'Memenuhi syarat untuk mengajukan seminar skripsi'
        END
    );
    
    RETURN v_result;
END$$

CREATE DEFINER=`stkp7133`@`localhost` FUNCTION `check_syarat_publikasi` (`proposal_id` BIGINT) RETURNS VARCHAR(500) CHARSET latin1 COLLATE latin1_swedish_ci DETERMINISTIC READS SQL DATA BEGIN
    DECLARE jurnal_count INT DEFAULT 0;
    DECLARE workflow_stat VARCHAR(50) DEFAULT '';
    DECLARE result VARCHAR(500) DEFAULT '';
    
    -- Cek jumlah jurnal bimbingan tervalidasi
    SELECT COUNT(*) INTO jurnal_count
    FROM jurnal_bimbingan 
    WHERE proposal_id = proposal_id AND status_validasi = '1';
    
    -- Cek workflow status dari proposal_mahasiswa
    SELECT workflow_status INTO workflow_stat
    FROM proposal_mahasiswa 
    WHERE id = proposal_id;
    
    -- Validasi syarat
    IF jurnal_count < 16 THEN
        SET result = CONCAT('Jurnal bimbingan belum memenuhi syarat. Saat ini: ', jurnal_count, '/16 tervalidasi');
    ELSEIF workflow_stat != 'seminar_skripsi' AND workflow_stat != 'publikasi' THEN
        SET result = CONCAT('Workflow belum sampai tahap publikasi. Status saat ini: ', workflow_stat);
    ELSE
        SET result = 'ELIGIBLE';
    END IF;
    
    RETURN result;
END$$

DELIMITER ;

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
,`level` enum('1','2','4','5')
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
  `level` enum('1','2','4','5') NOT NULL DEFAULT '2' COMMENT '1 = admin, 2 = dosen, 4 = kaprodi, 5 = staf',
  `foto` varchar(255) DEFAULT NULL,
  `bidang_keilmuan` varchar(255) DEFAULT NULL COMMENT 'Bidang keilmuan sesuai latar belakang pendidikan'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `nip`, `prodi_id`, `nama`, `nomor_telepon`, `email`, `level`, `foto`, `bidang_keilmuan`) VALUES
(2, '123456', 1, 'Super Admin', '081295111706', 'admin@stkyakobus.ac.id', '1', '', NULL),
(10, '2721128601', 10, 'Dedimus Berangka, S.Pd., M.Pd. (Kaprodi PKK)', '081290909003', 'dedimus@stkyakobus.ac.id', '4', 'f5f78573e6f98ae0ec49bc71c07024b8.jpg', NULL),
(11, '2706058401', 11, 'Steven Ronald Ahlaro, S.Pd., M.Pd. (Kaprodi PGSD)', '082271403437', 'pgsd@stkyakobus.ac.id', '4', '', NULL),
(12, '2720067001', 10, 'Dr. Berlinda Setyo Yunarti, M.Pd.', '085244791002', 'lindayunarti@stkyakobus.ac.id', '2', '0aeb4ba6a821018aaeda920f716230ea.jpg', 'Manajemen Pendidikan'),
(14, '2709109301', 11, 'Lambertus Ayiriga, S.Pd., M.Pd.', '82197819425', 'lambertus@stkyakobus.ac.id', '2', 'acdb01613d1ffe29172e4412ce6bb43d.jpg', 'Pendidikan Guru Sekolah Dasar, Evaluasi Pembelajaran'),
(15, '2728048001', 11, 'Rikardus Kristian Sarang, S.Fil., M.Pd.', '81248525845', 'rikardkristians@stkyakobus.ac.id', '2', '343c76cd72357ee976280e95822526ed.jpg', 'Filsafat, Manajemen Pendidikan'),
(16, '2730068501', 10, 'Raimundus Sedo, S.T., M.T.', '81338623494', 'raimundus@stkyakobus.ac.id', '2', '2a43963890246436769d2e5ae2c2b5bd.jpg', 'Sistem Informasi, Teknik Komputer'),
(17, '2705077801', 11, 'Dr. Erly Lumban Gaol, M.Th.', '81239904548', 'erly@stkyakobus.ac.id', '2', '6a5f094014253fffe2f79bb5e26b89fd.JPG', 'Pastoral, Teknologi Pendidikan'),
(18, '2727128101', 10, 'Yan Yusuf Subu, S.Fil., M.Hum.', '81227909867', 'yanyusuf@stkyakobus.ac.id', '2', '45b252dbccad7b99bd9e3b2060feee72.jpg', 'Teologi'),
(19, '2729108301', 11, 'Rosmayasinta Makasau, S.Pd., M.Pd.', '85244236555', 'mayamakasau@stkyakobus.ac.id', '2', 'b9389b10cf94c423662d55e86cc326a7.jpg', 'Pendidikan Bahasa Inggris'),
(20, '2717077001', 10, 'Dr. Donatus Wea, Lic.Iur.', '81247719057', 'romodonwea@stkyakobus.ac.id', '2', '9759cfa94b69c11c6e36d5d41b5f777f.jpg', 'Hukum Gereja, Manajemen Pendidikan'),
(21, '2719076301', 10, 'Drs. Xaverius Wonmut, M.Hum.', '81248202058', 'xaveriuswonmut@stkyakobus.ac.id', '2', 'a2ead8e0f3726f05e09c850575fbdfe0.jpg', 'Antropologi Budaya'),
(22, '2729086901', 11, 'Agustinus Kia Wolomasi, S.Ag., M.Pd.', '081386503387', 'aguswolomasi@stkyakobus.ac.id', '2', '18b86379703536f30994654376417e60.jpg', 'Filsafat, Manajemen Pendidikan'),
(23, '2709077801', 10, 'Markus Meran, S.Ag., M.Th.', '82248526104', 'markusmeran@stkyakobus.ac.id', '2', 'dec3cf8eab5496d4fece1474ab2833b9.JPG', 'Pastoral, Perbandingan Agama'),
(24, '1423056901', 10, 'Francisco Noerjanto, S.Ag., M.Si.', '8114890505', 'francisco@stkyakobus.ac.id', '2', '90e289fc18476464542d24a99756a2b8.jpg', 'Filsafat, Psikologi'),
(25, '2717069001', 10, 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', '081295111706', 'yohaneshenz@stkyakobus.ac.id', '2', 'cb7f7e58e87bc27937e271490b9d767e.jpg', 'Pendidikan Agama Katolik, Manajemen Pendidikan'),
(26, '2721128601', 10, 'Dedimus Berangka, S.Pd., M.Pd.', '081290909003', 'dedydbeau@gmail.com', '2', 'dcf30a6f727a39d2011da1853ccadcdd.jpg', 'Pendidikan Agama Katolik, Manajemen Pendidikan'),
(27, '2706058401', 11, 'Steven Ronald Ahlaro, S.Pd., M.Pd.', '082271403437', 'steveahlaro@stkyakobus.ac.id', '2', 'ae93469473c97b4aea5c79313daa68fc.jpg', 'Pendidikan Bahasa Inggris, Teknologi Pembelajaran'),
(28, '2717069000', 10, 'Yohanes Hendro Pranyoto (Admin)', '081295111706', 'humas@stkyakobus.ac.id', '1', '', NULL),
(29, 'STF001', 1, 'Maria Karolina Itu', '082124745593', 'mariadue@stkyakobus.ac.id', '5', 'c8b577cebc9eb3a9387528fe17e45fc6.png', NULL),
(30, 'STF002', 1, 'Elisabeth Yanu Dwi Astuti', '081240273873', 'elisabethyanu@stkyakobus.ac.id', '5', 'd704d5724dd83011ef04089d6ddbb805.jpg', NULL),
(31, 'STF003', 1, 'Adris Paulina Kause', '085244636278', 'adriskause@stkyakobus.ac.id', '5', '33680a07916505550ce810bbc012640a.jpg', NULL),
(32, 'STF004', 1, 'Yuliana Mangera', '082399795210', 'yulimangera@stkyakobus.ac.id', '5', 'f293f808ee1bf95ad6f65f586b130806.JPG', NULL),
(33, 'STF005', 1, 'Herybertus Oktaviani', '081295111706', 'herybertus@stkyakobus.ac.id', '5', '8b10d5c2d6e13a36d6a718e00d49e064.jpeg', NULL),
(34, 'STF001', 10, 'Admin SIPD', '081234567890', 'sipd@stkyakobus.ac.id', '5', 'c20b3eff5e1c0a13ab8d524beea7f047.png', NULL);

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
-- Table structure for table `hasil_kegiatan_backup_full_20250725_070204`
--

CREATE TABLE `hasil_kegiatan_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
-- Table structure for table `hasil_penelitian_backup_full_20250725_070204`
--

CREATE TABLE `hasil_penelitian_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
-- Table structure for table `hasil_seminar_backup_full_20250725_070204`
--

CREATE TABLE `hasil_seminar_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_bimbingan_backup_20250724`
--

CREATE TABLE `jurnal_bimbingan_backup_20250724` (
  `id` int(11) NOT NULL DEFAULT 0,
  `proposal_id` bigint(20) NOT NULL,
  `pertemuan_ke` int(11) NOT NULL COMMENT 'Urutan pertemuan (1, 2, 3, dst)',
  `tanggal_bimbingan` date NOT NULL COMMENT 'Tanggal pelaksanaan bimbingan',
  `materi_bimbingan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Materi yang dibahas dalam bimbingan',
  `catatan_dosen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Catatan dari dosen pembimbing (setelah validasi)',
  `tindak_lanjut` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tindak lanjut untuk mahasiswa',
  `durasi_bimbingan` int(3) DEFAULT NULL COMMENT 'Durasi bimbingan dalam menit',
  `catatan_mahasiswa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Catatan atau pertanyaan dari mahasiswa',
  `status_validasi` enum('0','1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '0=pending, 1=valid, 2=revisi',
  `tanggal_validasi` datetime DEFAULT NULL COMMENT 'Tanggal dosen memvalidasi',
  `validasi_oleh` bigint(20) DEFAULT NULL,
  `created_by` enum('mahasiswa','dosen') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'mahasiswa' COMMENT 'Dibuat oleh mahasiswa atau dosen',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jurnal_bimbingan_backup_full_20250725_070204`
--

CREATE TABLE `jurnal_bimbingan_backup_full_20250725_070204` (
  `id` int(11) NOT NULL DEFAULT 0,
  `proposal_id` bigint(20) NOT NULL,
  `pertemuan_ke` int(11) NOT NULL COMMENT 'Urutan pertemuan (1, 2, 3, dst)',
  `tanggal_bimbingan` date NOT NULL COMMENT 'Tanggal pelaksanaan bimbingan',
  `materi_bimbingan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Materi yang dibahas dalam bimbingan',
  `catatan_dosen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Catatan dari dosen pembimbing (setelah validasi)',
  `tindak_lanjut` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tindak lanjut untuk mahasiswa',
  `durasi_bimbingan` int(3) DEFAULT NULL COMMENT 'Durasi bimbingan dalam menit',
  `catatan_mahasiswa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Catatan atau pertanyaan dari mahasiswa',
  `status_validasi` enum('0','1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0' COMMENT '0=pending, 1=valid, 2=revisi',
  `tanggal_validasi` datetime DEFAULT NULL COMMENT 'Tanggal dosen memvalidasi',
  `validasi_oleh` bigint(20) DEFAULT NULL,
  `created_by` enum('mahasiswa','dosen') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'mahasiswa' COMMENT 'Dibuat oleh mahasiswa atau dosen',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `jurnal_bimbingan_backup_full_20250725_070204`
--

INSERT INTO `jurnal_bimbingan_backup_full_20250725_070204` (`id`, `proposal_id`, `pertemuan_ke`, `tanggal_bimbingan`, `materi_bimbingan`, `catatan_dosen`, `tindak_lanjut`, `durasi_bimbingan`, `catatan_mahasiswa`, `status_validasi`, `tanggal_validasi`, `validasi_oleh`, `created_by`, `created_at`, `updated_at`) VALUES
(19, 41, 1, '2025-07-25', 'Test jurnal setelah perbaikan database lengkap', NULL, 'Lanjutkan ke BAB berikutnya', NULL, NULL, '0', NULL, NULL, 'mahasiswa', '2025-07-24 19:26:36', '2025-07-24 19:26:36');

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

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi_backup_full_20250725_070204`
--

CREATE TABLE `konsultasi_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
-- Dumping data for table `konsultasi_backup_full_20250725_070204`
--

INSERT INTO `konsultasi_backup_full_20250725_070204` (`id`, `proposal_mahasiswa_id`, `tanggal`, `jam`, `isi`, `bukti`, `sk_tim`, `persetujuan_pembimbing`, `persetujuan_kaprodi`, `komentar_pembimbing`, `komentar_kaprodi`) VALUES
(10, 33, '2022-04-26', '11:00:00', 'Bimbingan BAB 3 Metodologi Penelitian', '20220426060102.doc', NULL, '1', '1', NULL, NULL),
(11, 33, '2022-04-26', '11:05:00', 'Bimbingan Abstrak dan Latar Belakang', '20220426060601.doc', NULL, '1', '1', NULL, NULL),
(12, 32, '2022-04-26', '11:42:00', 'Bimbingan BAB 1 - BAB 2', '20220426064325.doc', NULL, '1', '1', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `log_penelitian`
--

CREATE TABLE `log_penelitian` (
  `id` bigint(20) NOT NULL,
  `permohonan_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_role` enum('mahasiswa','dosen','staf','kaprodi','admin') NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_publikasi`
--

CREATE TABLE `log_publikasi` (
  `id` bigint(20) NOT NULL,
  `publikasi_id` bigint(20) NOT NULL COMMENT 'FK ke publikasi_tugas_akhir',
  `user_id` bigint(20) NOT NULL COMMENT 'ID user yang melakukan aktivitas',
  `user_role` enum('mahasiswa','dosen','staf','kaprodi','admin') NOT NULL COMMENT 'Role user',
  `user_name` varchar(100) NOT NULL COMMENT 'Nama user',
  `aktivitas` varchar(100) NOT NULL COMMENT 'Jenis aktivitas',
  `deskripsi` text NOT NULL COMMENT 'Deskripsi detail aktivitas',
  `data_before` text DEFAULT NULL COMMENT 'Data sebelum perubahan (JSON)',
  `data_after` text DEFAULT NULL COMMENT 'Data setelah perubahan (JSON)',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address user',
  `user_agent` text DEFAULT NULL COMMENT 'Browser user agent',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log aktivitas publikasi tugas akhir';

--
-- Dumping data for table `log_publikasi`
--

INSERT INTO `log_publikasi` (`id`, `publikasi_id`, `user_id`, `user_role`, `user_name`, `aktivitas`, `deskripsi`, `data_before`, `data_after`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'create_pengajuan', 'Mahasiswa membuat pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 11:16:50'),
(2, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 11:18:45'),
(3, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 11:56:03'),
(4, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 12:02:29'),
(5, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 12:24:05'),
(6, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 12:35:55'),
(7, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 12:36:03'),
(8, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 12:37:49'),
(9, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 12:48:43'),
(10, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 12:50:49'),
(11, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 12:58:12'),
(12, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 13:01:17'),
(13, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 13:03:41'),
(14, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 13:05:37'),
(15, 3, 46, 'mahasiswa', 'Mahasiswa Contoh 3', 'create_pengajuan', 'Mahasiswa membuat pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 13:09:52'),
(16, 3, 46, '', 'System Auto', 'status_changed', 'Status berubah dari draft ke submitted', NULL, NULL, NULL, NULL, '2025-08-05 13:25:37'),
(17, 3, 46, 'mahasiswa', 'Mahasiswa Contoh 3', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.146.211', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 13:25:37'),
(18, 2, 45, '', 'System Auto', 'status_changed', 'Status berubah dari draft ke submitted', NULL, NULL, NULL, NULL, '2025-08-05 14:50:29'),
(19, 2, 45, 'mahasiswa', 'Mahasiswa Contoh 2', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '2404:c0:47f4::1ba9:4893', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-05 14:50:29'),
(20, 3, 46, '', 'System Auto', 'status_changed', 'Status berubah dari submitted ke review_staf', NULL, NULL, NULL, NULL, '2025-08-05 15:34:13'),
(21, 3, 25, 'dosen', 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', 'approved', 'Review publikasi: approved. Komentar: Saya merekomendasikan publikasi ini untuk latihan saja ya', NULL, NULL, '2404:c0:47f4::1bc1:78ac', NULL, '2025-08-05 15:43:01'),
(22, 2, 45, '', 'System Auto', 'status_changed', 'Status berubah dari submitted ke review_staf', NULL, NULL, NULL, NULL, '2025-08-05 15:59:49'),
(23, 2, 25, 'dosen', 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', 'approved', 'Review publikasi: approved. Komentar: Quick approve', NULL, NULL, '2404:c0:47f4::1bc1:78ac', NULL, '2025-08-05 15:59:55'),
(24, 3, 29, 'staf', 'Maria Karolina Itu', 'input_repository', 'Input link repository: https://stkyakobus.ac.id/wp-content/uploads/2020/08/Form-Pernyataan-Mahasiswa-Baru.pdf', NULL, NULL, NULL, NULL, '2025-08-06 06:35:27'),
(25, 3, 29, 'staf', 'Maria Karolina Itu', 'input_repository', 'Input link repository: https://stkyakobus.ac.id/wp-content/uploads/2024/04/Formulir-PMB.pdf', NULL, NULL, NULL, NULL, '2025-08-06 06:51:21'),
(26, 2, 29, 'staf', 'Maria Karolina Itu', 'input_repository', 'Input link repository: https://stkyakobus.ac.id/wp-content/uploads/2019/10/SK-Ketua-No.-33-Tahun-2017-tentang-Pengangkatan-Kepala-LPMI.pdf', NULL, NULL, NULL, NULL, '2025-08-06 07:07:00'),
(27, 2, 29, 'staf', 'Maria Karolina Itu', 'input_repository', 'Input link repository: https://stkyakobus.ac.id/', NULL, NULL, NULL, NULL, '2025-08-06 09:53:52'),
(28, 2, 29, 'staf', 'Maria Karolina Itu', 'input_repository', 'Input link repository: https://stkyakobus.ac.id/download', NULL, NULL, NULL, NULL, '2025-08-06 10:00:44'),
(29, 2, 29, 'staf', 'Maria Karolina Itu', 'input_repository', 'Input link repository: https://stkyakobus.ac.id/download.pdf', NULL, NULL, NULL, NULL, '2025-08-06 10:19:20'),
(30, 2, 29, 'staf', 'Maria Karolina Itu', 'input_repository', 'Input link repository: https://stkyakobus.ac.id/downloadphp.pdf', NULL, NULL, NULL, NULL, '2025-08-06 10:29:46'),
(31, 4, 44, 'mahasiswa', 'Mahasiswa Contoh', 'create_pengajuan', 'Mahasiswa membuat pengajuan publikasi', NULL, NULL, '36.90.147.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-06 15:32:52'),
(32, 4, 44, '', 'System Auto', 'status_changed', 'Status berubah dari submitted ke rejected', NULL, NULL, NULL, NULL, '2025-08-06 16:08:39'),
(33, 4, 25, 'dosen', 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', 'rejected', 'Review publikasi: rejected. Komentar: ajukan ulang', NULL, NULL, '36.90.147.112', NULL, '2025-08-06 16:08:42'),
(34, 4, 44, 'mahasiswa', 'Mahasiswa Contoh', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.147.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-06 16:09:44'),
(35, 4, 44, 'mahasiswa', 'Mahasiswa Contoh', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.147.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-06 16:10:04'),
(36, 4, 44, '', 'System Auto', 'status_changed', 'Status berubah dari rejected ke submitted', NULL, NULL, NULL, NULL, '2025-08-06 16:20:16'),
(37, 4, 44, 'mahasiswa', 'Mahasiswa Contoh', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.147.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-06 16:20:16'),
(38, 4, 44, '', 'System Auto', 'status_changed', 'Status berubah dari submitted ke rejected', NULL, NULL, NULL, NULL, '2025-08-06 16:52:13'),
(39, 4, 25, 'dosen', 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', 'rejected', 'Review publikasi: rejected. Komentar: perlu perbaikan', NULL, NULL, '36.90.147.112', NULL, '2025-08-06 16:52:17'),
(42, 4, 44, 'mahasiswa', 'Mahasiswa Contoh', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.147.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-06 17:09:16'),
(43, 4, 44, '', 'System Auto', 'status_changed', 'Status berubah dari rejected ke submitted', NULL, NULL, NULL, NULL, '2025-08-06 17:09:38'),
(44, 4, 44, 'mahasiswa', 'Mahasiswa Contoh', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.147.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-06 17:09:38'),
(45, 4, 44, '', 'System Auto', 'status_changed', 'Status berubah dari submitted ke rejected', NULL, NULL, NULL, NULL, '2025-08-06 17:10:36'),
(46, 4, 25, 'dosen', 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', 'rejected', 'Review publikasi: rejected. Komentar: Parah sih', NULL, NULL, '36.90.147.112', NULL, '2025-08-06 17:10:39'),
(47, 4, 44, 'mahasiswa', 'Mahasiswa Contoh', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi', NULL, NULL, '36.90.147.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-06 17:12:36'),
(48, 4, 44, '', 'System Auto', 'status_changed', 'Status berubah dari rejected ke submitted', NULL, NULL, NULL, NULL, '2025-08-06 17:28:41'),
(49, 5, 47, 'mahasiswa', 'Agus Bumagi', 'create_pengajuan', 'Mahasiswa membuat pengajuan publikasi', NULL, NULL, '36.90.147.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-08-06 17:45:16'),
(50, 5, 47, '', 'System Auto', 'status_changed', 'Status berubah dari draft ke submitted', NULL, NULL, NULL, NULL, '2025-08-06 17:45:44'),
(51, 4, 44, '', 'System Auto', 'status_changed', 'Status berubah dari submitted ke review_staf', NULL, NULL, NULL, NULL, '2025-08-06 17:46:05'),
(52, 4, 25, 'dosen', 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', 'approved', 'Review publikasi: approved. Komentar: Quick approve', NULL, NULL, '36.90.147.112', NULL, '2025-08-06 17:46:12'),
(53, 4, 44, '', 'System Auto', 'status_changed', 'Status berubah dari review_staf ke completed', NULL, NULL, NULL, NULL, '2025-08-06 18:00:33'),
(54, 5, 29, 'staf', 'Maria Karolina Itu', 'status_changed', 'Status berubah dari submitted ke review_staf', NULL, NULL, NULL, NULL, '2025-08-06 18:09:09'),
(55, 5, 25, 'dosen', 'Yohanes Hendro Pranyoto, S.Pd., M.Pd.', 'approved', 'Review publikasi: approved. Komentar: Quick approve', NULL, NULL, '36.90.147.112', NULL, '2025-08-06 18:09:15'),
(56, 5, 47, '', 'System Auto', 'status_changed', 'Status berubah dari review_staf ke completed', NULL, NULL, NULL, NULL, '2025-08-06 18:10:09');

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
(49, '2002002', 'Agustinus S. Bumagi', 10, 'laki-laki', 'Yagatsu', '1998-09-29', 'agustinusbumagi40@gmail.com', 'Yagatsu', '082349063127', '082248519231', '1,97', '68942f2a51029.jpg', '$2y$10$e7CsTHfY.j5y4AiSwTLWGeVEu67d9hsmh5C9CWsLG8fTb6UztQGNi', '1'),
(50, '2102014', 'Jumantris Tobias Mesak', 10, 'laki-laki', 'Nakreu', '1998-01-16', 'nakreu1601@gmail.com', 'Merauke ', '082197462154', '083326523746', '2,80', '6894339681ea9.jpg', '$2y$10$OnC/Tk2DQjfCtybbwaCMz.7QS1qk4b1/s3fne3YXnNfbbPBZisO0m', '1'),
(51, '2202057', 'Yosefa Sabina Witi', 10, 'perempuan', 'Boru', '2002-01-22', 'yosefaswiti@student.stkyakobus.ac.id', 'Jalan Missi II STK St. Yakobus Merauke-Asrama', '082279940850', '082116823727', '3.78', '689486b4175b3.jpg', '$2y$10$3uOcIeGUo6aZVQ7GXA1GseUveclOD8gSPz/CH4/TsB7OkmIa0alHm', '1'),
(52, '2202050', 'Sebastiana Samderubun', 10, 'perempuan', 'Ngefuit Atas', '2025-06-08', 'gresantysamderubun@gmail.com', 'Jalan pendidikan, Kel. Mandala', '085251997984', '081232275135', '3, 02', NULL, '$2y$10$UR51BFvemyTpBzdzPTqs3uRjLK7u/ByfmLI3/SvlEHEp6C67xTUJG', '1'),
(53, '2202029', 'LUSIANA JUMBAIMO', 10, 'perempuan', 'Iowara', '1996-05-26', 'jumbaimolusiana@gmail.com', 'jl. Natuna', '081343134731', '081240813433', '3 : 35', '68957de160827.jpg', '$2y$10$snl.RQkm7dLFKdbbi02aS.KWm5eI3ri1U5wzVI5FsEA9q1tJ8YJ3K', '1'),
(54, '2202046', 'Ricarda Dewakop', 10, 'perempuan', 'Asiki', '2002-03-08', 'ricardadewakop@gmail.com', 'Jalan Biak', '081248413496', ' 082231146559', '2.92', '6895821699f99.jpg', '$2y$10$QDm3EqbJrGD6TYtSWNuW4.Joy//iUs1yrVmvwltZ7PMuKu05FxLS6', '1'),
(55, '2202045', 'Pia yustina kabinubun', 10, 'perempuan', 'ngefuit atas ', '2004-05-06', 'piakabinubun@gmail.com', 'jalan pendidikan kel.mandala', '081247056101', '08138145840535', '2,90', NULL, '$2y$10$ftlnrOlf4Pkd.xe9ZO2WwO.ApslLzxicQ60dJ4CZ6hHbm5dEPWGMi', '1'),
(56, '2202009', 'Emeliana Tukan', 10, 'perempuan', 'Ohoiel ', '2001-06-30', 'Nhonaemeliana22@gmail.com', 'JALAN MISI II', '081214055351', '085251185850', '3.26', '6895da3e9a2f2.jpg', '$2y$10$Y244O9w2z6ubYh53sxfWFe4kJ8neVXJ/qi9HaHJtMBnuATNU6ePbe', '1'),
(57, '2202036', 'Melania Samderubun ', 10, 'perempuan', 'waur ', '2004-05-18', 'melaniasamder@gmail.com', 'jalan missi  II', '081360965146', '081254680303', '2,98', NULL, '$2y$10$nUgohvio3nauJ857s5GMYOWXJYAwYXeiPV.fQj6yN7bgpBn3cm7r2', '1'),
(58, '2202007', 'Blasius Moa dedilado', 10, 'laki-laki', 'Merauke ', '2003-02-28', 'blasiusmoa0@gmail.com', 'Jl. Ndorem kai buti', '082160543164', '082198821780', '3,19', NULL, '$2y$10$YJIIeZvkAuIzpIuoJaxfFuLfoVsq0zwx576iY2NrWQkmbiL47eaSq', '1'),
(59, '2202012', 'FLESIA SALAY', 10, 'perempuan', 'kabalsiang ', '2000-11-18', 'flesiasalay@gmail.com', 'Jl.sumatera', '081369883915', '081248398322', '2.99', '20250808081351_59.png', '$2y$10$AmHqIBrd1QD0mVfsed0iqe3K7kdfi/Sg7.JVjPXmOnwKzlvh29jIO', '1'),
(60, '2202052', 'thomas kauria', 10, 'laki-laki', 'auwira', '1998-08-05', 'kauriathomas4@gmail.com', 'jalan. johar ', '085211866805', '082146254376', '2.07', '6896197688e5e.jpg', '$2y$10$Jpl7jPoO6w91udgi4yH1ZuIGQB3JOU/95fRk.EAgYVyvGJH61K.Um', '1'),
(61, '2202018', 'GERMANA TIUNG', 10, 'perempuan', 'MERAUKE', '2004-06-15', 'germanatiung8@gmail.com', 'JL. MISSI II (ASRAMA) STK ST.YAKOBUS MERAUKE', '0822-3937-5407', '082198987192', '3.52', '20250809052441_61.png', '$2y$10$3EM4jPmg1HNEZKlXf/k78.VpYFYifYz99bDxPMEjkI6e9VljUuFPy', '1'),
(62, '2202021', 'hironimus ero', 10, 'laki-laki', 'rembong', '2000-08-16', 'aguerohendro272@gmail.com', 'jl. pendidikan', '081231586881', '085338251024', '3.85', '6896728e1d3a0.jpg', '$2y$10$.eR1d9aHyXds8N/ZNX71oeAePZUZMp9hu9caTIKYMoDhZeA1jddUa', '1'),
(63, '2202032', 'Maria Margareta Nona Vani', 10, 'perempuan', 'Merauke', '3024-08-09', 'margatetanonavani@gmail.com', 'Jalan bupul kelapa lima', '085244038456', '082197946340', '3,11', NULL, '$2y$10$hLN6bqaWJqUFXy88B3.77uwg9aXgquALgHzviGmtPLrXVqTztfcVq', '1'),
(64, '2202005', 'Apolinaris Rahawarin ', 10, 'laki-laki', 'Ohoidertutu ', '2005-08-20', 'arisrahawarin4@gmail.com', 'Jln cemarah ', '085257099194', '082227512087', '2,43', '6896a82170a61.jpg', '$2y$10$cXnnh7uMW4FlZUlaXAsQkuQv.rIr0Yf/97/HqTaxErJqIcxFHUZj2', '1'),
(65, '2202066', 'Maria Seigi', 10, 'perempuan', 'Uwus', '2002-02-01', 'mariaseigi01@gmail.com', 'Jln,Missi II ,di Asrama STK Santo Yakobus Merauke', '082275900780', '081343157104', '3,12', '20250809091824_65.png', '$2y$10$uGqfLZhSyCog61NMLOfYneWwey941Mz.tF.EmQ0zBKgSTyTpReBkm', '1'),
(66, '2202023', 'IGNASIUS GUAM', 10, 'laki-laki', 'Tanah merah', '2025-11-11', 'epenjupen645@gmail.com', 'Jalan sayap 1', '085251618849', '085257099194', '0.71', '6896b3336682f.jpg', '$2y$10$0SGpsCdGd15h0Ce8tF/PGeJ8GEqoQOspGnv73F47aL8xad5YWIzWi', '1'),
(67, '2202039', 'MODESTA WIHELMINA YAAS', 10, 'perempuan', 'KONEBI', '2000-04-04', 'modesta3321@gmail.com', 'BADE KABUPATEN MAPPI PROPINSI PAPUA SELATAN', '085251623731', '082198819117', '3,20', '6896b6650475a.jpg', '$2y$10$vX8m4dBV5dxHgw6DhOAMMe/qhDOQBekhPnlSXSriHVc3p4scNPjjG', '1'),
(68, '2202004', 'Anita Yugusan', 10, 'perempuan', 'Osso', '2000-04-24', 'anitayugusan@student.stkyakobus.ac.id', 'jalan brawijaya merauke', '085236079843', '085251623731', '2.78', '6896dc4708a73.jpg', '$2y$10$JtWIM5rGOAahA71Ot.PfYOutpDOS3jByyD5iRfBi7Wjql7sgU4zlC', '1'),
(69, '2202001', 'Adolfina Nawanita', 10, 'perempuan', 'AUWIRA', '2002-01-08', 'adolfinanawanita1@gmail.com', 'jalan missi 2', '085251185850', '082231146559', '2.77', NULL, '$2y$10$gJzdj.7IETDWVP6Xn0ARXejydPQP3UZW6IEBSvHQAuN44wDvBpAMa', '1'),
(70, '2202054', 'VERONIKA WALENG TENAWAHANG', 10, 'perempuan', 'BELOGILI/LARANTUKA', '1984-09-27', 'veronikawtenawahang@student.stkyakobus.ac.id', 'jln Libra', '081252289170', '082144870923', '3.80', NULL, '$2y$10$HbEFq8uB8gWxkFfn0dRo3eT1daauHKQAW5fdeeYEgBwOlIeamIn6a', '1'),
(71, '2202026', 'Kostantinus Metawe', 10, 'laki-laki', 'Iromoro', '1999-08-06', 'kostanawym@gmail.com', 'Mangga dua', '081234896732', '081248313231', '2,74', '20250809074149_71.png', '$2y$10$lH5JgaVeP1MmTo6mYcbrrejnAcYW3sBq3MDbjBGCxxO0J3h3FFt2e', '1'),
(72, '2202035', 'MARTA YABON', 10, 'perempuan', 'BOVEN DIGOEL', '2002-08-29', 'marthakaipman998@gmail.com', 'STK ST. YAKOBUS MERAUKE', '081248146374', '085244877376', '3.43', '20250809074805_72.png', '$2y$10$0ImNP25MdUn6xZp8hMPnee1KoGqmyZM9zcpybhRxoHrokzZqhIpIy', '1'),
(73, '2202062', 'Melkianus Poyi', 10, 'laki-laki', 'Uwus 25-02-2002', '2002-02-25', 'melkianuspoyi42@gmail.com', 'Brawijaya Gank Daut ', '081344798052', '085299787346', '2.79', '689742d34586b.jpg', '$2y$10$aEJ1m6/8UK6tpPi1rq9R0.Rb6nXcina9xsF0yMIiLwd5u4.j0pI3C', '1'),
(74, '2202013', 'FRANSISKA ITLAY', 10, 'perempuan', 'ABULAKMA', '2003-09-06', 'fransiskaitlay1@gmail.com', 'JL. MISSI II', '082155533045', '082136882268', '2.83', '20250809083654_74.png', '$2y$10$w9e6GskmyYWzWYaEESrpJ.vBGdp97yaGre4rPs0X6Wi6ZpXWQ42Ey', '1'),
(75, '2202037', 'MELTIDIS ARSANTI', 10, 'perempuan', 'Merauke', '2001-12-05', 'arsantingabut@gmail.com', 'Jalan Missi 2', '082248503388', '085244817746', '2,96', NULL, '$2y$10$e/l9QeLSXaUAnhnMOk4m/.WLtyNIHziBMRx0AsTyzDDp5f9kLUOFq', '1'),
(76, '12345678', 'Mahasiswa Contoh', 10, 'laki-laki', 'Merauke', '2025-08-06', 'yohaneshenz@gmail.com', 'Missi 2, Mandala, Merauke', '09713330264', '09713330265', 'dedimus@stkyakobus.ac.id', '68982727c2edf.jpg', '$2y$10$yDASQ4VTx7YS4LS.gBXKn.Nq22N1y33L0w49kBVD2Q0lZfGF3ITz.', '1'),
(77, '2002031', 'Gelarda Wota Aga', 10, 'perempuan', 'Busiri', '2001-06-01', 'Gelardawotaa@gmail.com', 'jl.mangga dua merauke', '082248744925', '085244908875', '1.85', '689833d6d9cba.jpg', '$2y$10$BnSMZjHFv9WUd2YnKTpZieh.FD0294ExyS8WXMMG13B6fOlCpbXfW', '1'),
(78, '2202056', 'YOHAYA MARIANA MABUR', 10, 'perempuan', 'Mur', '2004-12-04', 'yohanamabur@gmail.com', 'Jln, bandara ', '085355978068', '081343157104', '2,96', '20250810055738_78.png', '$2y$10$snLPyN1GgXLlfWhYyf0k3.x8CzCI0TSPPEO9wzgVzxQ8y3IvKzzP6', '1'),
(79, '2202061', 'Petrus kalikimbian', 10, 'laki-laki', 'Semangga', '1970-01-01', 'kalikimbianpetrus@gmail.com', 'Kampung marga mulya', '082239151418', '+62 813-4405-1729', '2.87', '689913b2393f8.jpg', '$2y$10$NbjuKeo5SvWnlCIuYsriBOt65ZB.Leg35mLaVV/bW.E3s3XqU0Dgu', '1');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa_backup_full_20250725_070204`
--

CREATE TABLE `mahasiswa_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
-- Dumping data for table `mahasiswa_backup_full_20250725_070204`
--

INSERT INTO `mahasiswa_backup_full_20250725_070204` (`id`, `nim`, `nama`, `prodi_id`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `email`, `alamat`, `nomor_telepon`, `nomor_telepon_orang_dekat`, `ipk`, `foto`, `password`, `status`) VALUES
(32, '25104540', 'Hendro Mahasiswa', 10, 'laki-laki', 'Merauke, Bade', '2025-07-22', 'yohaneshenz@gmail.com', 'Merauke', '081295111732', '081295111782', '3', '20250721021959_32.png', '$2y$10$Upai5wQDDl1XXxXAQjF5oOPElLJ6ztbHhpLHvpMTeI0z1ZrnAFFB6', '1'),
(33, '2736373738', 'Herybertus Oktaviani', 10, 'laki-laki', 'Merauke', '1990-07-12', 'danielpuraka@student.stkyakobus.ac.id', 'Merauke', '081295111707', '081295111705', '3.20', '6877147cddd68.jpg', '$2y$10$Pq5WC53ySok2ae9Y4/hHZOCVLXavBZZKLRRnPYwi5RCfI78EbY4re', '1'),
(42, '233423546', 'Videlis Nilo Leba', 10, 'laki-laki', 'Merauke', '2025-07-16', 'videlis@stkyakobus.ac.id', 'Merauke', '081223244545', '081223244544', '3', '6880d10d56199.jpg', '$2y$10$hkKE/2dEy2dX0g.T0Njo6eDxldhTZHbBREayDHGXn9r34tsKbOZdq', '1');

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
(5, '', 'dosen', 2, NULL, 'Pengaturan Jadwal Bimbingan', 'Yth. Bapak/Ibu,\n\nSaya ingin mengatur jadwal bimbingan. Apakah Bapak/Ibu berkenan untuk:\n\nWaktu yang saya usulkan:\n- Hari: [Hari]\n- Tanggal: [Tanggal]\n- Jam: [Jam]\n- Tempat: [Tempat/Online]\n\nTerima kasih.\n\nHormat saya,\nYohanes Kandam', 0, '2025-07-21 17:05:48'),
(7, 'proposal_masuk', 'dosen', 25, NULL, 'Review Pengajuan Seminar Proposal', 'Mahasiswa Mahasiswa Contoh (12345676) telah mengajukan seminar proposal dan membutuhkan review Anda.', 0, '2025-07-28 12:49:09'),
(9, 'proposal_masuk', 'dosen', 25, 45, '???? Review Pengajuan Seminar Proposal Diperlukan', 'Mahasiswa Mahasiswa Contoh 2 (12345679) telah mengajukan seminar proposal dengan ID #SP-0002 dan membutuhkan review Anda. Harap segera lakukan review melalui sistem.', 0, '2025-07-29 11:42:09'),
(10, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nDengan hormat,\r\nPerlu koordinasi terkait:\r\n\r\n???? [Isi kegiatan]\r\n???? [Isi jadwal]\r\n\r\nMohon konfirmasi.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 15:36:46'),
(11, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nDengan hormat,\r\nPerlu koordinasi terkait:\r\n\r\n???? [Isi kegiatan]\r\n???? [Isi jadwal]\r\n\r\nMohon konfirmasi.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 15:37:03'),
(12, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nDengan hormat,\r\nPerlu koordinasi terkait:\r\n\r\n???? [Isi kegiatan]\r\n???? [Isi jadwal]\r\n\r\nMohon konfirmasi.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 15:37:36'),
(13, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nDengan hormat,\r\nPerlu koordinasi terkait:\r\n\r\n???? [Isi kegiatan]\r\n???? [Isi jadwal]\r\n\r\nMohon konfirmasi.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 15:38:14'),
(14, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nDengan hormat,\r\nPerlu koordinasi terkait:\r\n\r\n???? [Isi kegiatan]\r\n???? [Isi jadwal]\r\n\r\nMohon konfirmasi.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 15:42:23'),
(15, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 15:46:47'),
(16, 'proposal_masuk', 'mahasiswa', 34, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 15:47:51'),
(17, 'proposal_masuk', 'mahasiswa', 12, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 15:48:28'),
(18, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:01:21'),
(19, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:01:57'),
(20, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:05:53'),
(21, 'proposal_masuk', 'mahasiswa', 26, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:09:42'),
(22, 'proposal_masuk', 'mahasiswa', 26, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:09:51'),
(23, 'proposal_masuk', 'mahasiswa', 26, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:14:14'),
(24, 'proposal_masuk', 'mahasiswa', 26, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:14:14'),
(25, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:16:50'),
(26, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:16:50'),
(27, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:19:41'),
(28, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:19:41'),
(29, 'proposal_masuk', 'mahasiswa', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:21:27'),
(30, '', '', 25, NULL, 'Koordinasi Kegiatan', 'Yth. Bapak/Ibu,\r\n\r\nPerlu koordinasi kegiatan.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:26:49'),
(31, '', '', 34, NULL, 'Pengumuman Penting', 'Yth. Bapak/Ibu/Saudara/i,\r\n\r\nKami sampaikan pengumuman penting.\r\n\r\nHormat kami,\r\nKaprodi STK Santo Yakobus', 0, '2025-08-10 16:27:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi_backup_full_20250725_070204`
--

CREATE TABLE `notifikasi_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `jenis` enum('proposal_masuk','proposal_disetujui','proposal_ditolak','pembimbing_ditunjuk','pembimbing_menyetujui','pembimbing_menolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `untuk_role` enum('mahasiswa','dosen','kaprodi','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `proposal_id` bigint(20) DEFAULT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pesan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dibaca` tinyint(1) DEFAULT 0,
  `tanggal_dibuat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `penelitian_backup_full_20250725_070204`
--

CREATE TABLE `penelitian_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
-- Dumping data for table `penelitian_backup_full_20250725_070204`
--

INSERT INTO `penelitian_backup_full_20250725_070204` (`id`, `judul_penelitian`, `proposal_mahasiswa_id`, `pembimbing_id`, `penguji_id`, `bukti`, `persetujuan_pembimbing`, `persetujuan_penguji`, `komentar_pembimbing`, `komentar_penguji`, `sk_tim`, `file_seminar`, `bukti_konsultasi`) VALUES
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
,`level` enum('1','2','4','5')
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
(1, 1, 'Pengajuan Proposal Skripsi', '2025-08-09', 'Periode 1 2025', '1', '2025-07-15 17:29:36', '2025-08-07 18:13:04'),
(3, 2, 'Seminar Proposal', '2025-10-31', 'Seminar Proposal Bab 1-3', '1', '2025-07-15 17:29:36', '2025-07-19 17:42:25'),
(4, 3, 'Ujian Skripsi', '2026-05-25', 'Seminar Hasil Bab 1-5', '1', '2025-07-15 17:29:36', '2025-07-19 16:54:31'),
(5, 4, 'Revisi dan Publikasi', '2026-07-30', 'Perbaikan dan Publikasi Skripsi', '1', '2025-07-15 17:29:36', '2025-07-19 16:54:52'),
(6, 5, 'Yudisium', '2026-08-05', 'Pengukuhan dan Wisuda', '1', '2025-07-15 17:29:36', '2025-07-19 16:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `penilaian_seminar_proposal`
--

CREATE TABLE `penilaian_seminar_proposal` (
  `id` bigint(20) NOT NULL,
  `seminar_proposal_id` bigint(20) NOT NULL COMMENT 'FK ke seminar_proposal_mahasiswa',
  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa (redundant untuk performance)',
  `proposal_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa (redundant untuk performance)',
  `catatan_latar_belakang` text DEFAULT NULL COMMENT 'Catatan revisi untuk Latar Belakang & Rumusan Masalah',
  `catatan_tinjauan_pustaka` text DEFAULT NULL COMMENT 'Catatan revisi untuk Tinjauan Pustaka & Kebaruan (Novelty)',
  `catatan_landasan_teori` text DEFAULT NULL COMMENT 'Catatan revisi untuk Landasan Teori',
  `catatan_metodologi` text DEFAULT NULL COMMENT 'Catatan revisi untuk Metodologi Penelitian',
  `catatan_sistematika` text DEFAULT NULL COMMENT 'Catatan revisi untuk Sistematika & Tata Tulis',
  `catatan_umum` text DEFAULT NULL COMMENT 'Catatan umum atau saran tambahan',
  `nilai_penguji1` decimal(5,2) DEFAULT NULL COMMENT 'Nilai dari Dosen Penguji 1 (0-100, tanpa pembobotan)',
  `nilai_penguji2` decimal(5,2) DEFAULT NULL COMMENT 'Nilai dari Dosen Penguji 2 (0-100, tanpa pembobotan)',
  `nilai_pembimbing` decimal(5,2) DEFAULT NULL COMMENT 'Nilai dari Dosen Pembimbing (0-100, tanpa pembobotan)',
  `nilai_substansi_metode` decimal(5,2) DEFAULT NULL COMMENT '[DEPRECATED] Gunakan nilai_penguji1',
  `nilai_presentasi_teknik` decimal(5,2) DEFAULT NULL COMMENT '[DEPRECATED] Gunakan nilai_penguji2',
  `nilai_penguasaan_diskusi` decimal(5,2) DEFAULT NULL COMMENT '[DEPRECATED] Gunakan nilai_pembimbing',
  `nilai_akhir` decimal(5,2) DEFAULT NULL COMMENT 'Nilai akhir: (nilai_penguji1 + nilai_penguji2 + nilai_pembimbing) / 3',
  `nilai_huruf` enum('A','B','C','D','E') DEFAULT NULL COMMENT 'Konversi nilai: ≥80=A, 70-79.9=B, 60-69.9=C, 50-59.9=D, <50=E',
  `rekomendasi` enum('diterima_tanpa_revisi','revisi_minor','revisi_mayor','ditolak') DEFAULT NULL COMMENT 'Rekomendasi hasil seminar',
  `keterangan_rekomendasi` text DEFAULT NULL COMMENT 'Keterangan tambahan untuk rekomendasi',
  `status_penilaian` enum('draft','published') DEFAULT 'draft' COMMENT 'Status form: draft (masih bisa diedit) atau published (final)',
  `dinilai_oleh` bigint(20) NOT NULL COMMENT 'FK ke dosen/staf yang menginput penilaian',
  `role_penilai` enum('dosen_pembimbing','staf') DEFAULT 'dosen_pembimbing' COMMENT 'Role yang menginput: dosen pembimbing atau staf',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Tanggal pembuatan penilaian',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Tanggal terakhir update',
  `published_at` datetime DEFAULT NULL COMMENT 'Tanggal publikasi penilaian ke mahasiswa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel penilaian seminar proposal yang detail untuk dosen & staf';

--
-- Triggers `penilaian_seminar_proposal`
--
DELIMITER $$
CREATE TRIGGER `tr_penilaian_auto_complete` AFTER INSERT ON `penilaian_seminar_proposal` FOR EACH ROW BEGIN
    -- Langsung complete ketika ada penilaian baru (apapun statusnya)
    UPDATE seminar_proposal_mahasiswa 
    SET status = 'completed', current_step = 'mahasiswa'
    WHERE id = NEW.seminar_proposal_id;
    
    -- Update workflow
    UPDATE proposal_mahasiswa 
    SET workflow_status = CASE 
        WHEN NEW.rekomendasi = 'ditolak' THEN 'seminar_proposal'
        ELSE 'penelitian'
    END
    WHERE id = NEW.proposal_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_penilaian_calculate_nilai_v2` BEFORE INSERT ON `penilaian_seminar_proposal` FOR EACH ROW BEGIN
    IF NEW.nilai_penguji1 IS NOT NULL AND NEW.nilai_penguji2 IS NOT NULL AND NEW.nilai_pembimbing IS NOT NULL THEN
        SET NEW.nilai_akhir = ROUND((NEW.nilai_penguji1 + NEW.nilai_penguji2 + NEW.nilai_pembimbing) / 3, 2);
        SET NEW.nilai_huruf = CASE 
            WHEN NEW.nilai_akhir >= 80 THEN 'A'
            WHEN NEW.nilai_akhir >= 70 THEN 'B'
            WHEN NEW.nilai_akhir >= 60 THEN 'C'
            WHEN NEW.nilai_akhir >= 50 THEN 'D'
            ELSE 'E'
        END;
    END IF;
    
    IF NEW.status_penilaian = 'published' THEN
        SET NEW.published_at = NOW();
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_penilaian_calculate_nilai_v2_update` BEFORE UPDATE ON `penilaian_seminar_proposal` FOR EACH ROW BEGIN
    IF NEW.nilai_penguji1 IS NOT NULL AND NEW.nilai_penguji2 IS NOT NULL AND NEW.nilai_pembimbing IS NOT NULL THEN
        SET NEW.nilai_akhir = ROUND((NEW.nilai_penguji1 + NEW.nilai_penguji2 + NEW.nilai_pembimbing) / 3, 2);
        SET NEW.nilai_huruf = CASE 
            WHEN NEW.nilai_akhir >= 80 THEN 'A'
            WHEN NEW.nilai_akhir >= 70 THEN 'B'
            WHEN NEW.nilai_akhir >= 60 THEN 'C'
            WHEN NEW.nilai_akhir >= 50 THEN 'D'
            ELSE 'E'
        END;
    END IF;
    
    IF NEW.status_penilaian = 'published' AND OLD.status_penilaian = 'draft' THEN
        SET NEW.published_at = NOW();
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `penilaian_seminar_proposal_backup_20250729`
--

CREATE TABLE `penilaian_seminar_proposal_backup_20250729` (
  `id` bigint(20) NOT NULL,
  `seminar_proposal_id` bigint(20) NOT NULL COMMENT 'FK ke seminar_proposal_mahasiswa',
  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa (redundant untuk performance)',
  `proposal_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa (redundant untuk performance)',
  `catatan_latar_belakang` text DEFAULT NULL COMMENT 'Catatan revisi untuk Latar Belakang & Rumusan Masalah',
  `catatan_tinjauan_pustaka` text DEFAULT NULL COMMENT 'Catatan revisi untuk Tinjauan Pustaka & Kebaruan (Novelty)',
  `catatan_landasan_teori` text DEFAULT NULL COMMENT 'Catatan revisi untuk Landasan Teori',
  `catatan_metodologi` text DEFAULT NULL COMMENT 'Catatan revisi untuk Metodologi Penelitian',
  `catatan_sistematika` text DEFAULT NULL COMMENT 'Catatan revisi untuk Sistematika & Tata Tulis',
  `catatan_umum` text DEFAULT NULL COMMENT 'Catatan umum atau saran tambahan',
  `nilai_substansi_metode` decimal(5,2) DEFAULT NULL COMMENT 'Nilai Substansi & Metode Penelitian (bobot 50%)',
  `nilai_presentasi_teknik` decimal(5,2) DEFAULT NULL COMMENT 'Nilai Presentasi & Teknik Penyajian (bobot 20%)',
  `nilai_penguasaan_diskusi` decimal(5,2) DEFAULT NULL COMMENT 'Nilai Penguasaan Materi & Diskusi (bobot 30%)',
  `nilai_akhir` decimal(5,2) DEFAULT NULL COMMENT 'Nilai akhir rata-rata (auto calculated)',
  `nilai_huruf` enum('A','B','C','D','E') DEFAULT NULL COMMENT 'Konversi nilai: ≥80=A, 70-79.9=B, 60-69.9=C, 50-59.9=D, <50=E',
  `rekomendasi` enum('diterima_tanpa_revisi','revisi_minor','revisi_mayor','ditolak') DEFAULT NULL COMMENT 'Rekomendasi hasil seminar',
  `keterangan_rekomendasi` text DEFAULT NULL COMMENT 'Keterangan tambahan untuk rekomendasi',
  `status_penilaian` enum('draft','published') DEFAULT 'draft' COMMENT 'Status form: draft (masih bisa diedit) atau published (final)',
  `dinilai_oleh` bigint(20) NOT NULL COMMENT 'FK ke dosen/staf yang menginput penilaian',
  `role_penilai` enum('dosen_pembimbing','staf') DEFAULT 'dosen_pembimbing' COMMENT 'Role yang menginput: dosen pembimbing atau staf',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Tanggal pembuatan penilaian',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Tanggal terakhir update',
  `published_at` datetime DEFAULT NULL COMMENT 'Tanggal publikasi penilaian ke mahasiswa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel penilaian seminar proposal yang detail untuk dosen & staf';

--
-- Dumping data for table `penilaian_seminar_proposal_backup_20250729`
--

INSERT INTO `penilaian_seminar_proposal_backup_20250729` (`id`, `seminar_proposal_id`, `mahasiswa_id`, `proposal_id`, `catatan_latar_belakang`, `catatan_tinjauan_pustaka`, `catatan_landasan_teori`, `catatan_metodologi`, `catatan_sistematika`, `catatan_umum`, `nilai_substansi_metode`, `nilai_presentasi_teknik`, `nilai_penguasaan_diskusi`, `nilai_akhir`, `nilai_huruf`, `rekomendasi`, `keterangan_rekomendasi`, `status_penilaian`, `dinilai_oleh`, `role_penilai`, `created_at`, `updated_at`, `published_at`) VALUES
(1, 1, 44, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'draft', 25, 'dosen_pembimbing', '2025-07-29 11:37:29', '2025-07-29 11:37:29', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `penilaian_seminar_proposal_v`
-- (See below for the actual view)
--
CREATE TABLE `penilaian_seminar_proposal_v` (
`id` bigint(20)
,`seminar_proposal_id` bigint(20)
,`mahasiswa_id` bigint(20)
,`proposal_id` bigint(20)
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`email_mahasiswa` varchar(100)
,`judul` varchar(250)
,`nama_pembimbing` varchar(100)
,`tanggal_seminar` date
,`jam_seminar` time
,`tempat_seminar` varchar(255)
,`nilai_penguji1` decimal(5,2)
,`nilai_penguji2` decimal(5,2)
,`nilai_pembimbing` decimal(5,2)
,`nilai_substansi_metode_old` decimal(5,2)
,`nilai_presentasi_teknik_old` decimal(5,2)
,`nilai_penguasaan_diskusi_old` decimal(5,2)
,`nilai_akhir` decimal(5,2)
,`nilai_huruf` enum('A','B','C','D','E')
,`rekomendasi` enum('diterima_tanpa_revisi','revisi_minor','revisi_mayor','ditolak')
,`status_penilaian` enum('draft','published')
,`role_penilai` enum('dosen_pembimbing','staf')
,`nama_penilai` varchar(100)
,`created_at` datetime
,`updated_at` datetime
,`published_at` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `penilaian_seminar_skripsi`
--

CREATE TABLE `penilaian_seminar_skripsi` (
  `id` bigint(20) NOT NULL,
  `seminar_skripsi_id` bigint(20) NOT NULL COMMENT 'FK ke seminar_skripsi_mahasiswa',
  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa (redundant untuk performance)',
  `proposal_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa (redundant untuk performance)',
  `catatan_pendahuluan` text DEFAULT NULL COMMENT 'Catatan revisi untuk Bab I: Pendahuluan (Latar Belakang, Rumusan Masalah, Tujuan)',
  `catatan_tinjauan_pustaka` text DEFAULT NULL COMMENT 'Catatan revisi untuk Bab II: Tinjauan Pustaka & Landasan Teori',
  `catatan_metodologi` text DEFAULT NULL COMMENT 'Catatan revisi untuk Bab III: Metodologi Penelitian',
  `catatan_hasil_pembahasan` text DEFAULT NULL COMMENT 'Catatan revisi untuk Bab IV: Hasil Penelitian & Pembahasan',
  `catatan_kesimpulan` text DEFAULT NULL COMMENT 'Catatan revisi untuk Bab V: Kesimpulan & Saran',
  `catatan_umum` text DEFAULT NULL COMMENT 'Catatan umum, sistematika penulisan, atau saran tambahan',
  `nilai_penguji1` decimal(5,2) DEFAULT NULL COMMENT 'Nilai dari dosen penguji 1 (0-100)',
  `nilai_penguji2` decimal(5,2) DEFAULT NULL COMMENT 'Nilai dari dosen penguji 2 (0-100)',
  `nilai_pembimbing` decimal(5,2) DEFAULT NULL COMMENT 'Nilai dari dosen pembimbing (0-100)',
  `nilai_substansi_hasil` decimal(5,2) DEFAULT NULL COMMENT 'Nilai Substansi & Hasil Penelitian (untuk analisis)',
  `nilai_presentasi_teknik` decimal(5,2) DEFAULT NULL COMMENT 'Nilai Presentasi & Teknik Penyajian (untuk analisis)',
  `nilai_penguasaan_diskusi` decimal(5,2) DEFAULT NULL COMMENT 'Nilai Penguasaan Materi & Diskusi (untuk analisis)',
  `nilai_akhir` decimal(5,2) DEFAULT NULL COMMENT 'Nilai akhir: (nilai_penguji1 + nilai_penguji2 + nilai_pembimbing) / 3',
  `nilai_huruf` enum('A','B','C','D','E') DEFAULT NULL COMMENT 'Konversi nilai: ≥80=A, 70-79.9=B, 60-69.9=C, 50-59.9=D, <50=E',
  `rekomendasi` enum('lulus_tanpa_revisi','lulus_dengan_revisi_minor','lulus_dengan_revisi_mayor','tidak_lulus') DEFAULT NULL COMMENT 'Rekomendasi hasil seminar skripsi',
  `keterangan_rekomendasi` text DEFAULT NULL COMMENT 'Keterangan tambahan untuk rekomendasi',
  `status_penilaian` enum('draft','published') DEFAULT 'draft' COMMENT 'Status form: draft (masih bisa diedit) atau published (final)',
  `dinilai_oleh` bigint(20) NOT NULL COMMENT 'FK ke dosen/staf yang menginput penilaian',
  `role_penilai` enum('dosen_pembimbing','staf') DEFAULT 'dosen_pembimbing' COMMENT 'Role yang menginput: dosen pembimbing atau staf',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Tanggal pembuatan penilaian',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Tanggal terakhir update',
  `published_at` datetime DEFAULT NULL COMMENT 'Tanggal publikasi penilaian ke mahasiswa'
) ;

--
-- Triggers `penilaian_seminar_skripsi`
--
DELIMITER $$
CREATE TRIGGER `tr_penilaian_skripsi_calculate_insert` BEFORE INSERT ON `penilaian_seminar_skripsi` FOR EACH ROW BEGIN
    -- Auto calculate nilai akhir jika semua nilai tersedia
    IF NEW.nilai_penguji1 IS NOT NULL AND NEW.nilai_penguji2 IS NOT NULL AND NEW.nilai_pembimbing IS NOT NULL THEN
        SET NEW.nilai_akhir = ROUND((NEW.nilai_penguji1 + NEW.nilai_penguji2 + NEW.nilai_pembimbing) / 3, 2);
        
        -- Auto set nilai huruf
        SET NEW.nilai_huruf = CASE 
            WHEN NEW.nilai_akhir >= 80 THEN 'A'
            WHEN NEW.nilai_akhir >= 70 THEN 'B'
            WHEN NEW.nilai_akhir >= 60 THEN 'C'
            WHEN NEW.nilai_akhir >= 50 THEN 'D'
            ELSE 'E'
        END;
        
        -- ❌ DIHAPUS: Auto set rekomendasi 
        -- Rekomendasi harus input manual dari dosen, bukan auto-generate
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_penilaian_skripsi_calculate_update` BEFORE UPDATE ON `penilaian_seminar_skripsi` FOR EACH ROW BEGIN
    -- Auto calculate nilai akhir jika semua nilai tersedia
    IF NEW.nilai_penguji1 IS NOT NULL AND NEW.nilai_penguji2 IS NOT NULL AND NEW.nilai_pembimbing IS NOT NULL THEN
        SET NEW.nilai_akhir = ROUND((NEW.nilai_penguji1 + NEW.nilai_penguji2 + NEW.nilai_pembimbing) / 3, 2);
        
        -- Auto set nilai huruf
        SET NEW.nilai_huruf = CASE 
            WHEN NEW.nilai_akhir >= 80 THEN 'A'
            WHEN NEW.nilai_akhir >= 70 THEN 'B'
            WHEN NEW.nilai_akhir >= 60 THEN 'C'
            WHEN NEW.nilai_akhir >= 50 THEN 'D'
            ELSE 'E'
        END;
        
        -- ❌ DIHAPUS: Auto set rekomendasi 
        -- Rekomendasi tetap sesuai input dosen
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_penilaian_skripsi_workflow` AFTER UPDATE ON `penilaian_seminar_skripsi` FOR EACH ROW BEGIN
    -- Jika penilaian di-publish, update status seminar skripsi
    IF NEW.status_penilaian = 'published' AND OLD.status_penilaian = 'draft' THEN
        UPDATE seminar_skripsi_mahasiswa 
        SET status = 'completed', current_step = 'mahasiswa'
        WHERE id = NEW.seminar_skripsi_id;
        
        -- Logika workflow berdasarkan rekomendasi:
        -- Jika tidak lulus, workflow tetap di seminar_skripsi (untuk mengulang)
        -- Jika lulus (semua jenis), workflow lanjut ke publikasi
        IF NEW.rekomendasi = 'tidak_lulus' THEN
            UPDATE proposal_mahasiswa 
            SET workflow_status = 'seminar_skripsi'
            WHERE id = NEW.proposal_id;
        ELSE
            -- Semua jenis lulus (tanpa revisi, revisi minor, revisi mayor) lanjut ke publikasi
            UPDATE proposal_mahasiswa 
            SET workflow_status = 'publikasi'
            WHERE id = NEW.proposal_id;
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `penilaian_seminar_skripsi_v`
-- (See below for the actual view)
--
CREATE TABLE `penilaian_seminar_skripsi_v` (
`id` bigint(20)
,`seminar_skripsi_id` bigint(20)
,`mahasiswa_id` bigint(20)
,`proposal_id` bigint(20)
,`nilai_penguji1` decimal(5,2)
,`nilai_penguji2` decimal(5,2)
,`nilai_pembimbing` decimal(5,2)
,`nilai_akhir` decimal(5,2)
,`nilai_huruf` enum('A','B','C','D','E')
,`rekomendasi` enum('lulus_tanpa_revisi','lulus_dengan_revisi_minor','lulus_dengan_revisi_mayor','tidak_lulus')
,`status_penilaian` enum('draft','published')
,`role_penilai` enum('dosen_pembimbing','staf')
,`created_at` datetime
,`updated_at` datetime
,`published_at` datetime
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`email_mahasiswa` varchar(100)
,`judul` varchar(250)
,`tanggal_seminar` date
,`jam_seminar` time
,`tempat_seminar` varchar(255)
,`nama_pembimbing` varchar(100)
,`nama_penguji1` varchar(100)
,`nama_penguji2` varchar(100)
,`nama_penilai` varchar(100)
,`catatan_pendahuluan` text
,`catatan_tinjauan_pustaka` text
,`catatan_metodologi` text
,`catatan_hasil_pembahasan` text
,`catatan_kesimpulan` text
,`catatan_umum` text
);

-- --------------------------------------------------------

--
-- Table structure for table `permohonan_izin_penelitian`
--

CREATE TABLE `permohonan_izin_penelitian` (
  `id` bigint(20) NOT NULL,
  `proposal_mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa (readonly)',
  `nama_mahasiswa` varchar(100) NOT NULL COMMENT 'Nama mahasiswa (input manual, huruf kapital)',
  `nim` varchar(20) NOT NULL COMMENT 'NIM mahasiswa (input manual)',
  `semester` varchar(10) NOT NULL COMMENT 'Semester (VII, VIII, IX)',
  `program_studi` enum('Pendidikan Keagamaan Katolik','Pendidikan Guru Sekolah Dasar') NOT NULL COMMENT 'Program studi',
  `judul_skripsi_terbaru` text NOT NULL COMMENT 'Judul skripsi terbaru setelah seminar proposal',
  `tempat_penelitian` varchar(255) NOT NULL COMMENT 'Lokasi penelitian (wilayah gerejawi, instansi, dll)',
  `tanggal_mulai_penelitian` date NOT NULL COMMENT 'Tanggal mulai penelitian',
  `tanggal_selesai_penelitian` date NOT NULL COMMENT 'Tanggal selesai penelitian',
  `dosen_pembimbing_id` bigint(20) NOT NULL COMMENT 'FK ke dosen pembimbing (dari dropdown)',
  `file_proposal_revisi` varchar(255) NOT NULL COMMENT 'File proposal yang sudah direvisi (PDF/Word, max 2MB)',
  `status` enum('draft','submitted','review_pembimbing','approved','rejected','surat_ready','completed') DEFAULT 'draft' COMMENT 'Status workflow permohonan',
  `status_pembimbing` enum('pending','approved','rejected') DEFAULT 'pending' COMMENT 'Status review dosen pembimbing',
  `komentar_pembimbing` text DEFAULT NULL COMMENT 'Komentar dosen pembimbing',
  `tanggal_review_pembimbing` datetime DEFAULT NULL COMMENT 'Tanggal review pembimbing',
  `file_surat_izin_staf` varchar(255) DEFAULT NULL COMMENT 'File surat izin yang diupload staf (PDF, max 1MB)',
  `tanggal_upload_surat_staf` datetime DEFAULT NULL COMMENT 'Tanggal staf upload surat',
  `uploaded_by_staf` bigint(20) DEFAULT NULL COMMENT 'ID staf yang upload surat',
  `keterangan_staf` text DEFAULT NULL COMMENT 'Keterangan dari staf',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Tanggal pengajuan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permohonan izin penelitian - tidak mengubah tabel existing';

--
-- Triggers `permohonan_izin_penelitian`
--
DELIMITER $$
CREATE TRIGGER `tr_permohonan_completed` AFTER UPDATE ON `permohonan_izin_penelitian` FOR EACH ROW BEGIN
    -- Jika status berubah ke completed, update field existing di proposal_mahasiswa
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        UPDATE proposal_mahasiswa 
        SET 
            status_izin_penelitian = '1',
            surat_izin_penelitian = NEW.file_surat_izin_staf
        WHERE id = NEW.proposal_mahasiswa_id;
    END IF;
    
    -- Jika ditolak pembimbing
    IF NEW.status_pembimbing = 'rejected' AND OLD.status_pembimbing != 'rejected' THEN
        UPDATE proposal_mahasiswa 
        SET status_izin_penelitian = '2'
        WHERE id = NEW.proposal_mahasiswa_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_workflow_to_seminar_skripsi` AFTER UPDATE ON `permohonan_izin_penelitian` FOR EACH ROW BEGIN
    -- Ketika dosen approve, update workflow ke seminar_skripsi
    IF NEW.status_pembimbing = 'approved' AND OLD.status_pembimbing != 'approved' THEN
        UPDATE proposal_mahasiswa 
        SET workflow_status = 'seminar_skripsi'
        WHERE id = NEW.proposal_mahasiswa_id;
    END IF;
END
$$
DELIMITER ;

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
  `status_izin_penelitian` enum('0','1','2') DEFAULT '0' COMMENT '0=belum diminta, 1=disetujui, 2=ditolak',
  `tanggal_penetapan_ulang` datetime DEFAULT NULL,
  `penetapan_ulang_oleh` bigint(20) DEFAULT NULL,
  `alasan_penetapan_ulang` text DEFAULT NULL,
  `jumlah_penetapan_ulang` int(11) DEFAULT 0,
  `validasi_staf_publikasi` enum('0','1','2') DEFAULT '0' COMMENT '0=menunggu, 1=valid, 2=perlu perbaikan',
  `staf_validator_id` bigint(20) DEFAULT NULL COMMENT 'ID staf yang memvalidasi',
  `tanggal_validasi_staf` datetime DEFAULT NULL,
  `catatan_staf` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposal_mahasiswa`
--

INSERT INTO `proposal_mahasiswa` (`id`, `mahasiswa_id`, `judul`, `ringkasan`, `jenis_penelitian`, `lokasi_penelitian`, `uraian_masalah`, `file_draft_proposal`, `created_at`, `dosen_id`, `dosen2_id`, `dosen_penguji_id`, `dosen_penguji2_id`, `status`, `status_kaprodi`, `komentar_kaprodi`, `tanggal_review_kaprodi`, `status_pembimbing`, `komentar_pembimbing`, `tanggal_respon_pembimbing`, `deadline`, `tanggal_penetapan`, `penetapan_oleh`, `workflow_status`, `status_seminar_proposal`, `komentar_seminar_proposal`, `tanggal_review_seminar_proposal`, `tanggal_seminar_proposal`, `tempat_seminar_proposal`, `status_seminar_skripsi`, `komentar_seminar_skripsi`, `tanggal_review_seminar_skripsi`, `tanggal_seminar_skripsi`, `tempat_seminar_skripsi`, `status_publikasi`, `komentar_publikasi`, `tanggal_review_publikasi`, `link_repository`, `tanggal_publikasi`, `file_seminar_proposal`, `file_seminar_skripsi`, `file_skripsi_final`, `surat_izin_penelitian`, `status_izin_penelitian`, `tanggal_penetapan_ulang`, `penetapan_ulang_oleh`, `alasan_penetapan_ulang`, `jumlah_penetapan_ulang`, `validasi_staf_publikasi`, `staf_validator_id`, `tanggal_validasi_staf`, `catatan_staf`) VALUES
(49, 51, 'PENGARUH PROGRAM BEASISWA TERHADAP MOTIVASI\r\nBERPRESTASI MAHASISWA SEKOLAH TINGGI KATOLIK\r\nSANTO YAKOBUS MERAUKE', 'Keberhasilan mahasiswa tidak hanya diukur dari IPK, tetapi juga dari motivasi berprestasi yang dipengaruhi oleh berbagai faktor, termasuk insentif finansial seperti beasiswa. Meskipun secara teoritis beasiswa diyakini dapat meningkatkan motivasi dan ', 'Kuantitatif', 'Sekolah Tinggi Katolik Santo Yakobus Merauke', 'Keberhasilan mahasiswa tidak hanya diukur dari IPK, tetapi juga dari motivasi berprestasi yang dipengaruhi oleh berbagai faktor, termasuk insentif finansial seperti beasiswa. Meskipun secara teoritis beasiswa diyakini dapat meningkatkan motivasi dan prestasi mahasiswa, data awal dari STK Santo Yakobus Merauke menunjukkan tidak adanya perbedaan signifikan dalam capaian IPK antara penerima dan non-penerima beasiswa. Hal ini menimbulkan kesenjangan antara teori dan realitas, sehingga penelitian ini penting untuk menguji secara kuantitatif pengaruh program beasiswa terhadap motivasi berprestasi mahasiswa di STK Santo Yakobus Merauke.', '856b0f9a720f276be7a228342509e986.docx', '2025-08-07 18:24:37', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(50, 49, 'Ketinggalan pendidikan modern dikanlangan masyarakat orang asli Papua ', 'Bagaimana dampak keterbatasan pendidikan modern terhadap pengembangan SDM ', 'Kualitatif', 'Di kantor PKD', 'Bagaimana dampak keterbatasan pendidikan modern terhadap pengembangan SDM ', '2e3b45089a69e4f660bf1c32ec540d67.docx', '2025-08-08 10:04:38', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, '', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(51, 52, 'Pengarug pola asuh orang tua ditinjau dari Gravissimum Educationis terhadap prestasi akademik anak tingkat sekolah dasar kelas I sampe VI di Kampung Baru Kota Merauke', 'bagaiman cara orang tua menerapkan pola asuh yang ditinjau dari Gravissimum Educationis terhadap prestasi akademik anak', 'Kuantitatif', 'SD Inpres kampung Baru', 'bagaiman cara orang tua menerapkan pola asuh yang ditinjau dari Gravissimum Educationis terhadap prestasi akademik anak', '7468623c110491fea6a7d8a9642fd8ce.docx', '2025-08-08 11:27:45', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(52, 53, 'MINIMNYA PEMAHAMAN UMAT TENTANG KITAB SUCI DAN IMPLIKASINYA BAGI PELAYAN SABDA DI STASI St. YOHANES MARIA VIANNEY UMAP, PAROKI HATI KUDUS YESUS MOKBIRAN', 'Di stasi kami itu ada umat yang tidak memahami tentang kitab suci sehingga pada saat perayaan ibadat sabda berlangsung, dan pelayan sabda sedang membawakan renungan, umat tersebut merasa tersinggung dengan renungannya sehingga dia melakukan kekerasan', 'Kualitatif', 'Stasi St. Yohanes Maria Vianney Umap, Paroki hati Kudus Yesus Mokbiran', 'Di stasi kami itu ada umat yang tidak memahami tentang kitab suci sehingga pada saat perayaan ibadat sabda berlangsung, dan pelayan sabda sedang membawakan renungan, umat tersebut merasa tersinggung dengan renungannya sehingga dia melakukan kekerasan verval terhadap pelayan sabda menggunakan alat tajam ( sangkur). Hal itu membuat pelayan sabda merasa nyawanya terancam sehingga ia memutuskan untuk tidak menjadi pelayan sabda lagi pada ibadat sabda hai minggu. keputusan pelayan sabda ini membuat kehidupan menggereja di stasi kami tidak berjalan dengan baik. Umat tidak mengikuti ibadat sabda hari minggu karena tidak ada pemimpin ibadat, dan tidak ada pemenrimaan sakramen-sakramen ( baptis,komoni dan pernikahan) sebab tidak ada komunikasi yang baik antara pelayan sabda (dewan stasi) dan pastor paroki. Kehidupan menggereja tidak berjalan dengan baik selama empat tahun ( 2020- 2024 ). sakramen- sakramen dilakukan pada bulan desember 2024. Dengan demikian penulis ingin mengambil judul ini untuk mendeskripsikan penyebab minimnya pemahaman umat di stasi, St. Yohanes Maria Vianney Umap, Paroki Hati Kudus Yesus Mokbiran.', '79e64b217be66b336850125462539725.docx', '2025-08-08 13:01:40', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(53, 55, 'pengaruh literasi terhadap motivasi belajar mahasiswa di stk dari dekrit gravesim educations', ' dari judul proposal ni saya  melihat banyaka mahasiswa yang kurang dalam  dalam motivasi belajar2', 'Kualitatif', 'STK SANTO YAKOBUS MERAUKE', ' dari judul proposal ni saya  melihat banyaka mahasiswa yang kurang dalam  dalam motivasi belajar2', '53467031e4b5105979f60397d0812595.docx', '2025-08-08 17:23:39', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(54, 56, 'PERAN PENDAMPINGAN PASTORAL DALAM MENGATASI PERILAKU KONSUMTIF  MIRAS GENERASI MUDA: STUDI KONTEKSTUAL DI AMPERA 4 KABUPATEN MERAUKE', 'MASALAH GENERASI MUDA YANG MENGKOMSUMSI MINUMAN KERAS(MIRAS) DAN MEMBUAT ONAR DI JALAN AMPERA 4 KABUPATEN MERUAKE', 'Kualitatif', 'AMPERA 4 KABUPATEN MERUAKE', 'MASALAH GENERASI MUDA YANG MENGKOMSUMSI MINUMAN KERAS(MIRAS) DAN MEMBUAT ONAR DI JALAN AMPERA 4 KABUPATEN MERUAKE', '977319d6d81408b50b92c1c6edf16688.docx', '2025-08-08 18:22:20', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(55, 59, 'pengaruh kegiatan menggereja terhadap motivasi belajar di paroki santo fransiskus xaverius katedral merauke', 'pengaruh kegiatan menggereja orang muda katolik terhadap motivasi belajar di paroki santo fransiskus xaverius katedral merauke,di pilih karena adanya kesenjangan antara peran kegiatan menggereja dalam pembinaan orang muda katolik dan tantangan pening', 'Kualitatif', 'Di paroki santo fransiskus xaverius katedral merauke', 'pengaruh kegiatan menggereja orang muda katolik terhadap motivasi belajar di paroki santo fransiskus xaverius katedral merauke,di pilih karena adanya kesenjangan antara peran kegiatan menggereja dalam pembinaan orang muda katolik dan tantangan peningkatan motivasi  belajar di tengah dinamika modern. paroki santo fransiskus xaverius katedral merauke memiliki peran sentral dalam membina kaum muda melalui berbagai kegiatan. namun, efektivitas kegiatan ini dalam meningkatkan motivasi belajar masih perlu di pertanyakan, mengingat tantangan unik yang di hadapi di paroki santo fransiskus xaverius katedral merauke, seperti keterbatasan sumber daya pendidikan, dan tekanan sosial ekonomi. maka diharpakan penelitian ini dapat memberikan pemahaman tentang bagaiman  kegiatan menggereja dapat dioptimalkan untuk mendukung motivasi belajar orang muda katolik di paroki santo fransiskus xaverius katedral merauke.', '1dcbe9e8a1652b8c79275b402bc1972d.pdf', '2025-08-08 21:00:19', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(56, 60, 'ANALISIS REGRESI PENGARUH KESESUAIAN GAYA BERPAKAIAN DENGAN NORMA GEREJA TERHADAP TINGGKAT KEHADIRAN ORANG MUDA KATOLIKDALAM PERAYAAN EKARISTI DI GEREJA SANTA MARIA FATIMA KELAPA LIMA', 'PENGARUH KESESUAIAN GAYA BERPAKAIAN DENGAN NORMA GEREJA TERHADAP KEHADIRAN ORANG MUDA KATOLIK DALAM PERAYAAN EKARISTI', 'Kuantitatif', 'GEREJA SANTA MARIA FATIMA, KELAPA LIMA', 'PENGARUH KESESUAIAN GAYA BERPAKAIAN DENGAN NORMA GEREJA TERHADAP KEHADIRAN ORANG MUDA KATOLIK DALAM PERAYAAN EKARISTI', '34fe2c4c58f16c8faf417a70b5aabea6.pdf', '2025-08-08 23:22:40', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(57, 61, 'DAMPAK PERGAULAN TEMAN SEBAYA PADA REMAJA TERHADAP PARTISIPASI PELAYANAN PUTERA-PUTERI ALTAR', 'SAYA MEMILIH JUDUL PROPOSAL INI KARENA YANG INGIN MENELITI LEBIH LANJUT PADA REMAJA DI STASI SP7  YANG KURANG BERPARTISIPASI DALAM PELAYANAN DI GEREJA, PELAYANAN DIKALANGAN REMAJA SEPERTI MENJADI PELAYAN ALTAR ATAU PPA. REMAJA DI STASI SP7 LEBIH CEND', 'Kualitatif', 'STASI SANTO YAKOBUS SP7 PAROKI BUNDA HATI KUDUS KUPER', 'SAYA MEMILIH JUDUL PROPOSAL INI KARENA YANG INGIN MENELITI LEBIH LANJUT PADA REMAJA DI STASI SP7  YANG KURANG BERPARTISIPASI DALAM PELAYANAN DI GEREJA, PELAYANAN DIKALANGAN REMAJA SEPERTI MENJADI PELAYAN ALTAR ATAU PPA. REMAJA DI STASI SP7 LEBIH CENDERUNG MENGHABISKAN WAKTU DENGAN HAL-HAL DUNIAWI, MEREKA LEBIH MEMILIH UNTUK BERMAIN BERSAMA TEMAN SEBAYANYA, NONGKRONG, GOSIP. DAMPAKNYA MEREKA TIDAK MENGENAL JATI DIRI, KURANG MENGHAYATI IMAN, DAN PUTUS SEKOLAH.', 'd201d184e7096394a3caae59b708a0c1.docx', '2025-08-09 05:11:33', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(58, 62, 'MENJAGA KEBERLANGSUNGAN EKOLOGIS DALAM MENGURAI DAMPAK PEMBUANGAN SAMPAH DI KALI WEDA TERHADAP KESEHATAN LINGKUNGAN \r\n		 MENURUD PANDANGAN ENSIKLIK LAUDATO SI\r\n', 'masalah sampah yang terjadi di kali weda akibat kurangnya kesadaran masyarakat terhadap pentingnya menjaga lingkungan', 'Kualitatif', 'kali weda,kabupaten merauke,papua selatan', 'masalah sampah yang terjadi di kali weda akibat kurangnya kesadaran masyarakat terhadap pentingnya menjaga lingkungan', '09ba6d0252fcec96f9f1808daa555586.docx', '2025-08-09 05:25:10', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(59, 63, 'Faktor penyebab kurangnya pemahaman tentang alat-alat liturgi terhadap omk st. Petrus erom dan dampaknya terhadap pelayanan altar ', 'Masalah yg terjadi di gereja Katolik st Petrus erom  yaitu orang muda Katolik kurang memahami alat-alat liturgi dalam Gereja', 'Kualitatif', 'Gereja Katolik st. Petrus erom', 'Masalah yg terjadi di gereja Katolik st Petrus erom  yaitu orang muda Katolik kurang memahami alat-alat liturgi dalam Gereja', '9fbe620e82197468cf76fb76df15366b.docx', '2025-08-09 07:14:16', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(60, 64, 'Kurangnya keaktifan pemuda pemudi dalam kegiatan gereja khususnya di gereja katolik salib suci gudang arang ', 'Permasalahan yang saya ambil ini terjadi di gereja salib suci gudang arang dimana pada saat hari Meraka lebih mementingkan pekerjaan mereka sendiri dari pada harus ke gereja', 'Kualitatif', 'Di gereja salib suci gudang arang ', 'Permasalahan yang saya ambil ini terjadi di gereja salib suci gudang arang dimana pada saat hari Meraka lebih mementingkan pekerjaan mereka sendiri dari pada harus ke gereja', '4b819f4bf66c961384e4eca6a9356b2e.docx', '2025-08-09 08:59:41', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(61, 65, 'KEGIATAN POLA PENDIDIKAN ANAK DALAM KELUARGA BERDASARKAN NILAI-NILAI BUDAYA DAN TRADISI LOKAL MASYARAKAT ASMAT DI KAMPUNG UWUS KABUPATEN ASMAT.', ' Di kabupaten Asmat khususnya di kampung Uwus masi banyak sekali anak-anak yang putus sekolah karenah masi minim sekali perhatian orang tua terhadap pendidikan disana yang mana usia anak-anak yang menginjak remaja sudah memilih pasangan untuk Nikah m', 'Kualitatif', 'Di paroki Santo  Petrus Ewer', ' Di kabupaten Asmat khususnya di kampung Uwus masi banyak sekali anak-anak yang putus sekolah karenah masi minim sekali perhatian orang tua terhadap pendidikan disana yang mana usia anak-anak yang menginjak remaja sudah memilih pasangan untuk Nikah mudah ada juga yang lebih memilih berhenti  bersekolah dan lebih memilih untuk berkeluarga. ', 'dee2de380617d354a8c12c3be8e1c8bd.docx', '2025-08-09 09:54:41', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(62, 67, 'KITAB HUKUM KANONIK GEREJA KATOLIK TERHADAP PRAKTIK PERKAWINAN SEDARAH PADA SUKU AUYU YAAS DI KABUPATEN MERAUKE  PROVINSI PAPUA SELATAN', 'Penulis merasa tertarik untuk melakukan penelitian ini karena, suku Auyu Yaas banyak  yang sudah melakukan perkawinan sedarah sehingga hal ini menjadi salah satu keprihatinan peneliti untuk melakukan penelitian ini agar tidak dapat di teruskan oleh g', 'Kualitatif', 'JALAN BIAK KABUPATEN MERAUKE PROVINSI PAPUA SELATAN', 'Penulis merasa tertarik untuk melakukan penelitian ini karena, suku Auyu Yaas banyak  yang sudah melakukan perkawinan sedarah sehingga hal ini menjadi salah satu keprihatinan peneliti untuk melakukan penelitian ini agar tidak dapat di teruskan oleh generasi-generasi mudah selanjutnya. ', '088d76e8e00cd7e466b7285185253a49.pdf', '2025-08-09 10:02:18', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(63, 58, 'Pengaruh pergaulan teman sebaya terhadap konsumsi minuman beralkohol di paroki Santa Theresia Buti ', 'Masa remaja merupakan fase penting dalam perkembangan individu yang ditandai dengan perubahan biologis, psikologis, dan sosial. Pada tahap ini, remaja mulai membentuk identitas diri dan mencari pengakuan dari lingkungan sosil, terutama dari teman seb', 'Kuantitatif', 'Santa Theresia Buti Merauke ', 'Masa remaja merupakan fase penting dalam perkembangan individu yang ditandai dengan perubahan biologis, psikologis, dan sosial. Pada tahap ini, remaja mulai membentuk identitas diri dan mencari pengakuan dari lingkungan sosil, terutama dari teman sebaya. Pergulan dengan teman sebaya menjadi sangat dominan dalam kehidupan remaja dan sering kali memiliki pengaruh lebih besar dibandingkan keluarga dalam hal perilaku social, termasuk perilaku berisiko seperti konsumsi minuman beralkohol. “Pergaulan   pada   dasarnya   merupakan   salah   satu   cara   seseorang   untuk berinteraksi   dengan lingkungannya. Pergaulan adalah kontak  langsung  antara  individu  yang  satu dengan individu yang lainnya. Dalam hal ini, pergaulan sehari-hari yang dilakukan individu satu dengan yang lainnya adakalanya setingkatusianya,   pengetahuannya,   pengalamannya,   dan sebagainya.” (Dongoran & Boiliu, 2020). \r\nSalah satu bentuk pergaulan yang paling sering terjadi, terutama pada masa remaja adalah pergaula dengan teman sebaya. Teman sebaya merupakan kelompok sosial yang memiliki usia, minat, aktivitas dan latar belakang yang relatif sama. Teman sebaya merupakan kelompok sebaya yang terdiri dari sejumlah individu yang rata-rata usianya hampir ama yang memiliki kepentingan tertentu yang bersifat sangat sementara.  Kelompok sebaya merupakan agen sosialisasi yang mempunyai pengaruh yang kuat searah dengan bertambahnya usia anak. kelompok teman sebaya sebagai suatu kumpulan orang yang kurang lebih berusia sama yang berpikir dan bertindak bersama-sama (Dongoran & Boiliu, 2020).\r\nPergaulan teman sebaya di kalangan remaja sering kali menjadi faktor penting dalam membentuk perilaku sosial dan gaya hidup mereka. Salah satu perilaku yang cukup mencemaskan adalah konsumsi minuman beralkohol. Dalam mengonsumsi minuman beralkohol, seringkali tidak terbatas pada jumlah kecil atau dalam situasi yang terkendali. Dalam pergaulan dengan teman sebaya, banyak remaja yang melakukannya secara berlebihan tanpa menyadari dampak negatifnya terhadap kesehatan fisik dan mental mereka. Dalam jangka pendek, kebiasaan ini dapat menyebabkan penurunan kesadaran, kehilangan kontrol diri, hingga kecelakaan atau tindakan kekerasan. Dalam jangka panjang, kebiasaan ini dapat menyebabkan gangguan perilaku, kecanduan, penurunan prestasi akademik, bahkan keterlibatan dalam tindak kriminal. Meskipun alkohol dikenal memiliki dampak negatif yang signifikan bagi kesehatan, sosial, dan psikologi remaja, namun fenomena ini masih banyak ditemui diberbagai kalangan, termasuk di lingkungan remaja yang seharusnya mendpat pengawasan dari orang tua, Gereja da masyarakat sekitar.\r\nSecara umum, adapun beberapa permasalahan yang terjadi dilapangan yakni tingginya tekanan dari teman sebaya. Beberapa remaja merasa terpaksa mengikuti teman-temannya yang lebih sering mengonsumsi alkohol untuk mendapatkan status dan pengakuan sosial di kelompok mereka. Kebiasaan ini seringkali membuat remaja merasa dikucilkan atau dianggap tidak \"keren\". Kurangnya pengetahuan mengenai dampak negatif alkohol. Banyak remaja minum alkohol tanpa memahami bahayanya terhadap kesehatan fisik dan mental mereka.  Faktor ini diperburuk oleh kurangnya edukasi tentang bahaya alkohol, baik di sekolah, di gereja maupun lingkungan masyarakat. Peran keluarga yang terbatas dalam pengawasan. Beberapa remaja yang terlibat dalam konsumsi alkohol berasal dari keluarga yang kurang memberikan perhatian dan pengawasan. Sebagian besar remaja ini merasa kebebasan yang berlebihan tanpa adanya kontrol atau pengarahan yang cukup dari orang tua. Minimnyaa kegiatan positif dan alternatif yang melibatkan remaja.  Keterlibatan remaja dalam kegiatan rohani gereja atau aktivitas social di lingkungan Paroki Buti relative terbatas. Banyak remaja yang lebih memilih berkumpul dengan teman-teman di luar kegatan gereja dan mencari kegiatan yang lebih “seru”, yang sering kali melibatkan konsumsi alkohol. Kurangnya pengawasan dari pihak orang tua, gereja dan masyarakat. Meskipun gereja memiliki pengaruh penting dalam membentuk perilaku moral remaja, pengawasan pengawasan terhadap perilaku remaja yang terlibat dalam konsumsi alkohol belum cukup optimal. Selain itu, keterlibatan orang tua dalam mendukung program-program gereja yang mengedukasi remaja tentang dampak negative alkohol masih terbatas. \r\nFenomena konsumsi minuman beralkohol dikalangan remaja menjadi isu yan semakin mengkhawatirkan, termasuk di lingkunan Santo Antonius-Paroki Buti. Minuman yang sebelumnya dikonsumsi terbatas oleh orang dewasa, namun kini juga mulai dikonsumsi oleh remaja, baik dalam konteks pergaulan, tekanan teman sebaya, maupun pelarian dari masalah pribadi atau keluarga. Di Lingkungan Santo Antonius, belum banyak penelitian yang secara spefisik mengkaji apakah ada hubunan yang signifikan antara tingkat partisipas pendidikan dengan perilaku konsumsi alkohol dikalangan remaja. Padahal pemahaman terhadap hubungan ini penting untuk merancang intervensi atau program edukatif yang dapat menekan angka konsumsi alkohol. Di Paroki Santa Theresia Buti, meskipun dikenal sebagai kawasan yang religius dengan keaktifan kegiatan rohani, fenomena konsumsi alkohol di kalangan remaja masih menunjukkan angka yang cukup tinggi.\r\nDengan mempertimbangkan masalah-masalah diatas, penulis ingin melakukan penelitian tambahan untuk menemukan solusi mengenai masalah pergaulan teman sebaya yang berdampak pada konsumsi minuman berlkohol. Bedasarkan berbagai penjelasan diatas, maka penulis melakukan studi dengan judul “Pengaruh Pergaulan Teman Sebaya Terhadap Konsumsi Minuman Beralkohol Pada Remaja Di Paroki Santa Theresia Buti”', '76d0c470e418e735679809b0f7ee1334.docx', '2025-08-09 10:06:54', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(64, 57, 'PEMAHAMAN MAHASISWA SEKOLAH TINNGI KATOLIK SANTO YAKOBUS MERAUKE TERHADAP LITURGI PRAKTIS BERDASARKAN SACROSANCTUMCONCILIUM', ' sejauh mana mahasiswa Sekolah Tinggi katolik Santo Yakobus Merauke memahami dan menghayati liturgi praktis dalam terang ajaran Sacrosanctum Concilium. \r\nBagaimana tingkat pemahaman mahasiswa Sekolah Tinggi Santo Yakobus Merauke terhadap konsep dasar', 'Kualitatif', 'SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MEAUKE ', ' sejauh mana mahasiswa Sekolah Tinggi katolik Santo Yakobus Merauke memahami dan menghayati liturgi praktis dalam terang ajaran Sacrosanctum Concilium. \r\nBagaimana tingkat pemahaman mahasiswa Sekolah Tinggi Santo Yakobus Merauke terhadap konsep dasar liturgi dalam ajaran Gereja Katolik berdasarkan Sacrosanctum Concilium\r\n\r\n', 'e07af967f1be331544abe7012f97eca5.docx', '2025-08-09 11:23:55', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(65, 54, 'UPAYA MENINGKATKAN KETERLIBATAN UMAT DALAM MENGIKUTI KEGIATAN KATEKESE MELALUI KATEKESE MODEL SOTARAE DI LINGKUNGAN ST. MIKAEL PAROKI BAMPEL KEUSKUPAN AGUNG MERAUKE', 'pada tahun 2024 saya mengikuti ibadah katekese  pada saat ibadah katekese umat kurang (cuma beberapa saja )  yang  hadir lebih banyak anak-anak sekolah dan ini membuat saya tersentuh untuk mengambil judul ini agar bisa saya mencari tau umat di lingku', 'Kualitatif', 'Lokasi penelitian akan penulis adakan di lingkungan st mikael jln missi.', 'pada tahun 2024 saya mengikuti ibadah katekese  pada saat ibadah katekese umat kurang (cuma beberapa saja )  yang  hadir lebih banyak anak-anak sekolah dan ini membuat saya tersentuh untuk mengambil judul ini agar bisa saya mencari tau umat di lingkungan sto. mikael ada banyak atau sedikit  dan mungkin dengan penepatan judul yang saya ambil ini bisa diminati dan bisa meningkatkan keaktifan umat di lingkungan terlebih ibadah katekese.', 'be7469552945fe7b7133af2b60c781d3.docx', '2025-08-09 13:21:24', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(66, 70, 'IMPLEMENTASI KEBIJAKAN PENDIDIKAN INKLUSIF DALAM PROGRAM BEVAK PINTAR DI PASAR MOPAH BARU KABUPATEN MERAUKE ', 'implementasi pendidikan di bevak pintar', 'Kualitatif', 'Pasar Mopah Baru', 'implementasi pendidikan di bevak pintar', '82b5d20526336e867afbc9fbb956b6ab.docx', '2025-08-09 14:47:44', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(67, 69, 'ANALISIS KESADARAN PENDIDIKAN ORANG TUA DAN DAMPAKNYA TERHADAP MASA DEPAN ANAK-ANAK DI KAMPUNG KONJOMBANDO, KABUPATEN MERAUKE”', 'orang tua tidak mau menyekolakan anak anak mereka ke jenjang berikutnya seperti smp ,Sma/Smk.ini terjadi Kerena mereka masih sangat kurang sekali tentang pendidikan yang sangat penting.', 'Kualitatif', 'Kampung Konjombado Kabupaten Merauke', 'orang tua tidak mau menyekolakan anak anak mereka ke jenjang berikutnya seperti smp ,Sma/Smk.ini terjadi Kerena mereka masih sangat kurang sekali tentang pendidikan yang sangat penting.', 'afaf85c05076eae94c3723ab09728f34.docx', '2025-08-09 14:57:30', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(68, 71, 'Model pastoral yang efektif berbasis kearifan lokal bagi umat suku Kamaam di kampung nggolar/ paroki santa maria fatima kelapa lima', 'Kurangnya keaktifan dalam gereja oleh suku kimaam di kampung nggolar', 'Kualitatif', 'Kampung nggolar', 'Kurangnya keaktifan dalam gereja oleh suku kimaam di kampung nggolar', 'fd7d4bb03fca99b4aef6eaccc3d71361.pdf', '2025-08-09 19:39:01', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(69, 72, 'FAKTOR-FAKTOR YANG MEMPERNGARUHI RENDANHNYA PARTISIPASI MAHASISWA ASRAMA SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE DALAM KEGIATAN KEROHANIAN DI LINGKUNGAN SANTO MIKHAEL, PAROKI ST YOSEP BAMPU PEMALI', 'Gereja katolik menempatkan kaum muda, termasuk mahasiswa sebagai harapan dan masa depan gereja. Keterlibatan aktif mereka dalam kehidupan menggereja, khususnya dalam komunitas basis seperti lingkungan, merupakan salah satu indikator vitalitas dan keb', 'Kualitatif', 'kampus STK ST YAKOBUS MERAUKE', 'Gereja katolik menempatkan kaum muda, termasuk mahasiswa sebagai harapan dan masa depan gereja. Keterlibatan aktif mereka dalam kehidupan menggereja, khususnya dalam komunitas basis seperti lingkungan, merupakan salah satu indikator vitalitas dan keberlanjutan iman. Sekolah Tinggi Katolik (STK) Santo Yakobus Merauke sebagai lembaga pendidikan Tinggi Katolik memiliki visi dan misi untuk membentuk calon-calon katekis, guru agama, dan pastoralis yang tidak hanya unggul secara akademis, tetapi juga matang dalam kegiatan rohani dan aktif dalam pelayanan Gereja.\r\nLingkungan Santo Mikhael, yang berada di bawah naungan Paroki Santo Yosep Bambu Pemali, secara geografis mencakup wilayah domisili sebagai mahasiswa STK santo Yakobus Merauke. Idealnya para mahasiswa terlebih khusus mahasiswa yang tinggal diasrama ini menjadi motor penggerak dan teladan partisispasi dalam berbagai kegiatan kerohanian dilingkungan mereka khususnya di lingkungan santo Mikhael Paroki Bambu Pemali, sepeRTI doa rosario, pendalaman iman atau ibadat sabda. Partisipasi ini bukan hanya bentuk kewajiban iman, tetapi juag merupakan laboratorium praktik bagi ilmu teologi dan pastoral yang mereka pelajari di bangku kuliah.\r\nNamun berdasarkan observasi awal dan informasi dari beberapa pengurus lingkungan, teridentifikasi sebuah fenomena yang kontradiksi. Terdapat indikasi rendahnya tingkat partisipasi mahasiswa asrama STK santo Yakobus Merauke dalam kegiatan-kegiatan kerohanian yang diselenggarakan oleh lingkungan Santo Mikhael. Kehadiran mereka cenderung minim dan tidak konsisten. Fenomena ini menimbulkan pertanyaan besar: mengapa mahasiswa asrama yang dididik dalam lingkungan Katolik yang kental dan dipersipakan untuk menjadi pelayan Gereja justru menunjukkan partisipasi yang rendah dikomunitas basis mereka sendiri? \r\nMenurut penelitian yang dilakukan oleh Suryani (2018), partisipasi mahasiswa dalam kegiatan kerohanian sangat dipengaruhi oleh faktor internal seperti motivasi pribadi, persepsi terhadap manfaat kegiatan, serta tingkat religiusitas individu. Selain itu, faktor eksternal seperti dukungan lingkungan, peran pembina asrama, serta ketersediaan fasilitas dan sarana pendukung juga turut berkontribusi terhadap tingkat partisipasi mahasiswa dalam kegiatan kerohanian (Suryani, 2018).\r\nLebih lanjut, penelitian yang dilakukan oleh Simbolon (2020) menunjukkan bahwa adanya perbedaan latar belakang budaya dan agama diantara mahasiswa asrama dapat mempengaruhi tingkat keterlibatan mereka dalam kegiatan kerohanian. Mahasiswa yang berasal dari keluarga dengan tradisi keagamaan yang kuat cenderung lebih aktif dalam mengikuti kegiatan kerohanian, sedangkan mahasiswa yang berasal dari keluarga dengan trdasis keagamaan yang kurang kuat cenderung kurang aktif (Simbolon 2020). Selain itu, faktor lingkungan sosial di asrama, seperti adanya kelompok-kelompok pertemanan yang kurang mendukung aktivitas kerohanian juga dapat menjadi salah satu penyebab rendahnya partisipasi mahasiswa dalam kegiatan tersebut.\r\nDi sisi lain, peran pembina asrama dan pengurus lingkungan Santo Mikhael juga sangat menentukan dalam upaya meningkatkan partisipasi mahasiswa dalam kegiatan kerohanian. Pembina asrama yang mampu memberika teladan, motivasi, serta pendekatan yang sesuai dengan karakter mahasiswa, dapat mendorong mahasiswa untuk lebih aktif terlibat dalam kegiatan kerohanian. Namun, jika pembinaan asrama kurang proaktif atau kurang mampu membangun komunikasi yang efektif dengan mahasiswa, maka hal ini dapat berdampak pada rendahnya minat mahasiwa untuk mengikuti kegiatan kerohanian (Suryani, 2018).\r\nSelain faktor-faktor diatas, ketersediaan fasilitas dan sarana pendukung juga menjadi aspek penting yang mempengaruhi pastisipasi mahasiswa dalam kegiatan kerohanian. Fasilitas yang memadai, seperti ruang ibadah yang nyaman, alat-alat pendukung kegiatan, serta akses informasi yang mudah, dapat meningkatkan minat mahasiswa untuk partisipasi dalam kegiatan kerohanian. Sebailiknya, keterbatana fasilitas dan sarana pendukang dapat  mmenjadi hambatan bagi mahasiswa untuk terlibat secara aktif dalam kegiatan tersebut (Simbolon, 2020)\r\nKondisi ideal yang diharapkan adalah terciptanya lingkungan asrama yang kondusif bagi pengembangan spiritualitas mahasiswa melalui partisipasi aktif dalam kegiatan kerohanian. Kegiatan kerohanian diharapkan dapat menjadi sarana pembentukan karakter, penguatan nilai-nilai moral, serta penigkatan kualitas hidup mahasiswa, baik secara individual maupun sebagai anggota komunitas. Namun, kondisi faktual yang terjadi menunjukkan adanya kesenjangan antara harapan dan kenyataan dimana partisipasi mahasiswa dalam kegiatan kerohanian masih tergolong rendah.\r\nPermasalahan rendahnya partisipasi mahasiswa dalam kegiatan kerohanian ini perluh mendapatkan perhatian serius dari berbagai pihak, baik dari pihak pengelola asrama, pengurus lingkungan Santo Mikhael, maupun pihak kampus secara keseluruhan. Upaya-upaya yang mempengaruhi rendahnya partisipasi mahasiswa, sehingga dapat dirumuskan solusi yang tepat dan efektif untuk meningkatkan keterlibatan mahasiswa dalam kegiatan kerohanian.\r\nSalah satu solusi yang dapat dilakukan adalah dengn melakukan inovasi dalam pelaksanaan kegiatan kerohanian, baik dari segi metode, materi, maupun pendekatan yang digunakan. Kegiatan kerohanian perluh dirancang sedemikian rupa agar mampu menarik minat dan perhatian mahasiswa, serta memberikan manfaat yang nyata bagi pengembangan diri mereka. Selain itu, perluh dilakukan upaya peningkatan peran pembina asrama dan pengurus lingkungn dalam membangun komunikasi yang efektif dengan mahasiswa, serta menciptakan suasana yang kondusif bagi pengembangan spiritualitas mahasiswa.\r\nSelain itu, penting pula untuk melakukan evaluasi terhadap ketersediaan fasilitas dan sarana pendukung kegiatan kerohaniaan dilingkungan asrama. Fasilitas yang memadai dapat menjadi faktor pendorong bagi mahasiswa untuk lebih aktif terlibat dalam kegiatan kerohanian. Oleh karena itu, pihak kampus perluh memberikan perhatian khusus terhadap penyediaan fasilitas dan sarana pendukung yang diperlukan untuk mendukung pelaksanaan kegiatan kerohanian di leingkungan asrama.\r\nDengan demikian, analisis terhadap faktor-faktor yang mempengaruhi rendahnya partisipasi mahasiswa asrama Sekolah Tinggi Katolik Santo Yakobus Merauke dalam kegiatan kerohanian dilingkungan Santo Mikhael, Paroki Santo Yosep Bampu Pemali, menjadi sangat penting untuk dilakukan. Hasil analisis ini diharapkan dapat memberikan gambaran yang komprehensif mengenai permasalahan yang dihadapi, serta menjadi dasar bagi perumusan strategi dan kebijakan yang efektif dalam upaya meningkatkan partisipasi mahsiswa dalam kegiatan kerohanian. Dengan meningkatnya partisipasi mahasiswa dalam kegiatan kerohanian, diharapkan dapat tercipta lingkungan asrama yang religius, haromonis, dan kondusif bagi pengembangan karakter dan spiritualitas mahasiswa.\r\nKesenjangan antara harapan ideal (mahasiswa STK sebagai agen pastoral yang aktif) dan realitas empiris (rendahnya partisipasi dilingkungan) inilah yang menjadi urgensi penelitian ini. Rendahnya partitsipasi ini dapat berakibat pada beberapa hal : (1) kurangnya regenerasi dalam kepengurusan lingkungan; (2) kegiatan kerohanian menjadi monoton dan kurang dinamis; dan (3) mahasiswa kehilangan kesempatan untuk mengintegrasikan teori pastoral dengan praktik nyata dilapangan.\r\nOleh karena itu, sangat penting untuk melakukan sebuah analisis mendalam guna mengidentifikasi dan memahami faktor-faktor apa saja yang secara signifikan mempengaruhi rendahnya tingkat partisipasi tersebut. Penelitian ini diharapkan dapat memberikan gambaran utuh mengenai persoalan in dari perspektif mahasiswa itu sendiri, sehingga dapat dirumuskan solusi dan rekomendasi yang tepat sasaran.\r\n', 'eeddaec41e21ff67b8d377d742556815.docx', '2025-08-09 19:45:34', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(70, 73, 'KURANGNYA PERHATIAN GURU PADA SISWA YANG BELUM BISA MENULIS DAN MEMBACA \r\n\r\nDI KELAS IV SD BAMPEL ', 'Karena banyak anak-anak yang  sudah kelas IV tetapi belum bisa membaca dan menulis karena itu saya mengambil judul ini ', 'Kuantitatif', 'SD BAMPEL ', 'Karena banyak anak-anak yang  sudah kelas IV tetapi belum bisa membaca dan menulis karena itu saya mengambil judul ini ', '4f1a45a24623d3dea6847acf263d9fd9.docx', '2025-08-09 19:58:02', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(71, 74, 'STUDI FENOMENOLI: MAKNA PENDIDIKAN DAN MASA DEPAN BAGI PEREMPUAN ASLI PAPUA YANG TIDAK MELANJUTKAN SEKOLAH DI KAMPUNG SIEPKOSSY DISTRIK SIEPKOSSY KABUPATEN JAYAWIJAYA PROVINSI PAPUA PEGUNUNGAN', 'SAYA INGIN MENELITI MENGAPA PEREMPUAN WAMENA TIDAK TERTARIK UNTUK MELANJUTKAN PENDIDIKAN DAN LEBIH TERTARIK DENGAN KENYAMANAN DUNIAWI (HAMIL DILUAR NIKAH) ', 'Kualitatif', 'KAMPUNG SIEPKOSSY DISTRIK SIEPKOSSY KABUPATEN JAYAWIJAYA PROVINSI PAPUA PEGUNUNGAN', 'SAYA INGIN MENELITI MENGAPA PEREMPUAN WAMENA TIDAK TERTARIK UNTUK MELANJUTKAN PENDIDIKAN DAN LEBIH TERTARIK DENGAN KENYAMANAN DUNIAWI (HAMIL DILUAR NIKAH) ', '3ff5563fdf632cf20512893b94773b9e.docx', '2025-08-09 20:36:01', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(72, 68, 'KURANGNYA KESADARAN MAHASISWA DALAM MEMANFAATKAN PERPUSTAKAAN SEBAGAI SUMBER REFRENSI DI SEKOLAH TINGGI KATOLIK SANTO YAKOBUS MERAUKE', 'Yang melatarbelakangi dari judul ini adalah karena mahasiswa di kampus STK ini mengangap perpustakaan sebagai tempat pemijamnan buku intuk mengerjakan tugas tugas dari dosen maupun tugas akhir.Dan faktor penyebabnya  karena rendahnya minat baca,penga', 'Kualitatif', 'LOKASIH PENELITIAN : BERTEMPAT  DALAM LINGKUNGAN KAMPUS STK ST. YAKOBUS MERAUKE', 'Yang melatarbelakangi dari judul ini adalah karena mahasiswa di kampus STK ini mengangap perpustakaan sebagai tempat pemijamnan buku intuk mengerjakan tugas tugas dari dosen maupun tugas akhir.Dan faktor penyebabnya  karena rendahnya minat baca,pengaruh hp  menoton tiktok,fesbuk,malas dalam membaca buku dan mahasiswa lebih menghabiskan waktu belajar dengan hal-hal yang di luar kelas. seperti cerita ,jalan menoton di lingkuangan kampus.', 'ed21c43fddb37ac8eab14699a4850ced.pdf', '2025-08-10 07:01:24', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(73, 76, 'Judul Ini Hanya untuk Simulasi Sistem Informasi Manajemen Tugas Akhir: Mohon Diabaikan Saja', 'Ini hanya simulasi, Judul Ini Hanya untuk Simulasi Sistem Informasi Manajemen Tugas Akhir: Mohon Diabaikan Saja', 'Kualitatif', 'STK St. Yakobus Merauke', 'Ini hanya simulasi, Judul Ini Hanya untuk Simulasi Sistem Informasi Manajemen Tugas Akhir: Mohon Diabaikan Saja', '5f8bc15ed69b39ea255929826917b48a.pdf', '2025-08-10 12:00:42', 25, 1, NULL, NULL, '0', '1', 'Simulasi saja', '2025-08-10 12:44:20', '1', 'Baik, saya akan membimbing', '2025-08-10 12:46:48', NULL, '2025-08-10 12:44:20', 10, 'bimbingan', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(74, 75, 'ANALISISA FAKTOR PEYEBAB DAN DAMPAK KURANGNYA KETERLIBATAN UMAT DALAM HIDUP MENGEREJA DI STASI SANTO YOSEP PAROKI SANTO PETRUS EROM', 'saya ingin meneliti dampak dari kurangnya keterlibatan hidup menggereja umat stasi santo yosep paroki santo petrus erom yang menyebabkan umat kurang mengimani Yesus, pendidikan iman anak dalam keluarga kurang diperhatikan.\r\n ', 'Kualitatif', 'STASI SANTO YOSEP PAROKI SANTO PETRUS EROM', 'saya ingin meneliti dampak dari kurangnya keterlibatan hidup menggereja umat stasi santo yosep paroki santo petrus erom yang menyebabkan umat kurang mengimani Yesus, pendidikan iman anak dalam keluarga kurang diperhatikan.\r\n ', 'f6ef482c2b9270db7bfac57d23129fe2.pdf', '2025-08-10 12:02:25', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(75, 77, 'PENGARUH KETERLIBATAN MAHASISWA DALAM KEGIATAN EKSTRAKURIKULER TERHADAP PENGEMBANGAN DIRI DAN PRESTASI AKADEMIK DI STK SANTO YAKOBUS MERAUKE’’', '1.3	Rumusan masalah\r\nBerdasarkan pembatasan masalah tersebut,maka dapat di rumuskan masalah sebagi berikut.\r\n1.Bagaimana prosedur kegiatan ekstrakulikuler dengan menggunakan  mendia gambar untuk  meninkatkan hasil kegiatan ekstrakulikuler pendidikan ', 'Kuantitatif', 'STK SANTO YAKOBUS MERAUKE', '1.3	Rumusan masalah\r\nBerdasarkan pembatasan masalah tersebut,maka dapat di rumuskan masalah sebagi berikut.\r\n1.Bagaimana prosedur kegiatan ekstrakulikuler dengan menggunakan  mendia gambar untuk  meninkatkan hasil kegiatan ekstrakulikuler pendidikan agama katolik pada mahasiswa-mahasiswi  sekolah tinggi katolik santo yakabus merauke?\r\n2.Apakah pengunaan mendia  dapat meningkatkan hasil kegiatan mahasiswa-mahasiswi untuk menikuti kegiatan ekstrakulikuler  sekolah tinggi katolik santo yakobus merauke.\r\n3.Sejauh mana efekivitas penggunaan  mendia dalam meningkatkan hasil kegiatan ekstrakulikuler pada mahasiswi- mahasiswi  sekolah tinggi katolik santo yakobus merauke.\r\n1.4 Tunjuan Penulisan\r\nBerdasarkan masalah yang telah dirumuskan, maka peneliti bertujuan untuk mengetahui hal-hal sebagai berikut:\r\n1.Agar  Menganlisis pengaruh dalam menggunakanan media  dalam  kegiatan  terhadap motivasi  mahasiswa-mahasiswi untuk menikuti ekstrakulikuler di sekolah tinggi katolik santo yakobus merauke.\r\n2.Untuk menenukan besar pengaruh  terhadap mahasiswa-mahasiswi untuk menikuti kegiatan ekstrakulikuler, karena kecendurugan mahasiswa-mahasiswi   tidak aktif  dalam menikuti kegiatan ekstarkulikuler pada hari sabtu di sekolah tinggi santo yakobus merauke.\r\n3.Untuk mendeskripsikan  dalam usulan program sebagai meningkatkan perkembangan  kegiatan ekstrakulikuler di sekolah tinggi katolik santo yakobus merauke.\r\n', 'e583424887ba3f83e1a3a73d53b98970.docx', '2025-08-10 14:19:28', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(76, 78, '                   PERAN KELUARGA DALAM MENGURANGI TINGGINYA \r\nANGKA ANAK PUTUS SEKOLAH DI LINGKUNGAN ST. YOHANES DON BOSKO\r\n', 'permasalahannya mengenai anak-anak yang putus sekolah ', 'Kualitatif', 'Lingkungan Santo Yohanis Don Bosko', 'permasalahannya mengenai anak-anak yang putus sekolah ', 'a48c8eb5699cba88870267689ad16644.docx', '2025-08-10 17:54:56', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(77, 66, 'PARTISIPASIH GEREJA KATOLIK TERHADAP MAMA MAMA ASAMAT YANG MEJADI PEMULUNG BARANG BEKAS DI KOTA MERAUKE', 'mama mama asmat yang menjadi pemulung barang bekas faktornya ekonomi dan faktor lingkungan yang sering terjadi pencuri masyarakat punya barang barnang dirumah', 'Kualitatif', 'stadion maro', 'mama mama asmat yang menjadi pemulung barang bekas faktornya ekonomi dan faktor lingkungan yang sering terjadi pencuri masyarakat punya barang barnang dirumah', 'a31a353bb17d450733dee291b6bb5550.docx', '2025-08-10 18:00:37', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(78, 79, 'PERAN SAKRAMEN KRISMA DALAM PEMBENTUKAN KARAKTER UMAT KATOLIK DI LINGKUNGAN SANTA MARGARETHA, PAROKI SANTO MIKAEL KUDA MATI\r\n\r\n', 'PERAN SAKRAMEN KRISMA DALAM PEMBENTUKAN KARAKTER UMAT KATOLIK DI LINGKUNGAN SANTA MARGARETHA, \r\nKurangnya pemahaman umat dalam memaknai sakramen krisma sehingga berdampak pada karakter umat yang tidak mendalami ajaran gereja dan nilai nilai agama', 'Kuantitatif', 'Lingkungan santa margaretha paroki santo Mikael kudamati', 'PERAN SAKRAMEN KRISMA DALAM PEMBENTUKAN KARAKTER UMAT KATOLIK DI LINGKUNGAN SANTA MARGARETHA, \r\nKurangnya pemahaman umat dalam memaknai sakramen krisma sehingga berdampak pada karakter umat yang tidak mendalami ajaran gereja dan nilai nilai agama', 'c0458fda5958413d840ed62880791faf.docx', '2025-08-11 04:56:44', NULL, 1, NULL, NULL, '0', '0', NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL);

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
-- Table structure for table `proposal_mahasiswa_backup_20250724`
--

CREATE TABLE `proposal_mahasiswa_backup_20250724` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
  `status_izin_penelitian` enum('0','1','2') DEFAULT '0' COMMENT '0=belum diminta, 1=disetujui, 2=ditolak',
  `tanggal_penetapan_ulang` datetime DEFAULT NULL,
  `penetapan_ulang_oleh` bigint(20) DEFAULT NULL,
  `alasan_penetapan_ulang` text DEFAULT NULL,
  `jumlah_penetapan_ulang` int(11) DEFAULT 0,
  `validasi_staf_publikasi` enum('0','1','2') DEFAULT '0' COMMENT '0=menunggu, 1=valid, 2=perlu perbaikan',
  `staf_validator_id` bigint(20) DEFAULT NULL COMMENT 'ID staf yang memvalidasi',
  `tanggal_validasi_staf` datetime DEFAULT NULL,
  `catatan_staf` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposal_mahasiswa_backup_20250724`
--

INSERT INTO `proposal_mahasiswa_backup_20250724` (`id`, `mahasiswa_id`, `judul`, `ringkasan`, `jenis_penelitian`, `lokasi_penelitian`, `uraian_masalah`, `file_draft_proposal`, `created_at`, `dosen_id`, `dosen2_id`, `dosen_penguji_id`, `dosen_penguji2_id`, `status`, `status_kaprodi`, `komentar_kaprodi`, `tanggal_review_kaprodi`, `status_pembimbing`, `komentar_pembimbing`, `tanggal_respon_pembimbing`, `deadline`, `tanggal_penetapan`, `penetapan_oleh`, `workflow_status`, `status_seminar_proposal`, `komentar_seminar_proposal`, `tanggal_review_seminar_proposal`, `tanggal_seminar_proposal`, `tempat_seminar_proposal`, `status_seminar_skripsi`, `komentar_seminar_skripsi`, `tanggal_review_seminar_skripsi`, `tanggal_seminar_skripsi`, `tempat_seminar_skripsi`, `status_publikasi`, `komentar_publikasi`, `tanggal_review_publikasi`, `link_repository`, `tanggal_publikasi`, `file_seminar_proposal`, `file_seminar_skripsi`, `file_skripsi_final`, `surat_izin_penelitian`, `status_izin_penelitian`, `tanggal_penetapan_ulang`, `penetapan_ulang_oleh`, `alasan_penetapan_ulang`, `jumlah_penetapan_ulang`, `validasi_staf_publikasi`, `staf_validator_id`, `tanggal_validasi_staf`, `catatan_staf`) VALUES
(36, 32, 'Pengaruh Gaya Berpacaran terhadap Partisipasi Orang Muda Katolik (OMK) dalam Hidup Menggereja di Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke Tahun 2025', 'Partisipasi Orang Muda Katolik (OMK) dalam hidup menggereja merupakan indikator penting keberlangsungan Gereja Katolik di masa depan. Namun, kenyataan di lapangan menunjukkan adanya penurunan keterlibatan OMK dalam kegiatan-kegiatan gerejawi, seperti', 'Kuantitatif', 'Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke', 'Partisipasi Orang Muda Katolik (OMK) dalam hidup menggereja merupakan indikator penting keberlangsungan Gereja Katolik di masa depan. Namun, kenyataan di lapangan menunjukkan adanya penurunan keterlibatan OMK dalam kegiatan-kegiatan gerejawi, seperti perayaan Ekaristi, doa lingkungan, dan pelayanan sosial. Salah satu faktor yang diduga berkontribusi terhadap rendahnya partisipasi tersebut adalah gaya berpacaran yang dijalani oleh OMK. Di Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke, fenomena ini mulai tampak signifikan. Gaya pacaran yang tidak sehat—seperti hubungan yang posesif, terlalu mendominasi waktu, atau berorientasi pada kesenangan semata—berpotensi mengalihkan fokus dan komitmen OMK dari kegiatan rohani dan pelayanan gerejawi. Di sisi lain, gaya pacaran yang dewasa dan dilandasi nilai-nilai Kristiani justru dapat mendorong partisipasi aktif dalam kehidupan menggereja. Oleh karena itu, penting untuk menelaah lebih jauh bagaimana gaya berpacaran OMK memengaruhi tingkat keterlibatan mereka dalam hidup menggereja. Penelitian ini bertujuan untuk mengidentifikasi pola gaya pacaran yang dominan serta dampaknya terhadap semangat OMK dalam menjalani hidup menggereja di lingkungan Stasi Santo Mikael, demi merancang strategi pastoral yang lebih efektif.', '306cf686ff3f7323b18304b48f7c6e43.docx', '2025-07-18 10:21:14', 25, 1, NULL, NULL, '0', '1', 'Proposal sudah baik dan bisa langsung mulai bimbingan. Terimakasih', '2025-07-17 17:18:16', '1', 'Baik saya menerima', '2025-07-23 17:55:14', NULL, NULL, NULL, 'bimbingan', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2025-07-23 17:45:02', 10, 'Dosen pembimbing sebelumnya () menolak penunjukan. Menetapkan dosen pembimbing baru untuk melanjutkan proses bimbingan mahasiswa.', 0, '0', NULL, NULL, NULL),
(37, 33, 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke', 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke, ini latihan saja ya', 'Kuantitatif', 'STK St. Yakobus Merauke', 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke, ini latihan saja ya', 'd2ff01bd1f6cb9b54d4059526a3fb112.docx', '2025-07-18 10:21:14', 26, 1, NULL, NULL, '0', '1', 'Lanjutkan', '2025-07-23 18:35:54', '1', 'Ya saya setuju membimbing ', '2025-07-23 19:25:53', NULL, '2025-07-23 18:35:54', 10, 'bimbingan', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(41, 42, 'Pengaruh Tes Saja', 'Pengaruh Tes Saja', 'Kuantitatif', 'Merauke', 'Pengaruh Tes Saja', '68795a1fa5ea2fa268d3bfe05362db14.docx', '2025-07-23 19:10:29', 25, 1, NULL, NULL, '0', '1', 'Lanjutkan bimbingan', '2025-07-23 19:11:52', '1', 'Ok saya setuju\r\n', '2025-07-24 18:34:07', NULL, '2025-07-23 19:11:52', 10, 'bimbingan', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `proposal_mahasiswa_backup_fix_20250723`
--

CREATE TABLE `proposal_mahasiswa_backup_fix_20250723` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
-- Dumping data for table `proposal_mahasiswa_backup_fix_20250723`
--

INSERT INTO `proposal_mahasiswa_backup_fix_20250723` (`id`, `mahasiswa_id`, `judul`, `ringkasan`, `jenis_penelitian`, `lokasi_penelitian`, `uraian_masalah`, `file_draft_proposal`, `created_at`, `dosen_id`, `dosen2_id`, `dosen_penguji_id`, `dosen_penguji2_id`, `status`, `status_kaprodi`, `komentar_kaprodi`, `tanggal_review_kaprodi`, `status_pembimbing`, `komentar_pembimbing`, `tanggal_respon_pembimbing`, `deadline`, `tanggal_penetapan`, `penetapan_oleh`, `workflow_status`, `status_seminar_proposal`, `komentar_seminar_proposal`, `tanggal_review_seminar_proposal`, `tanggal_seminar_proposal`, `tempat_seminar_proposal`, `status_seminar_skripsi`, `komentar_seminar_skripsi`, `tanggal_review_seminar_skripsi`, `tanggal_seminar_skripsi`, `tempat_seminar_skripsi`, `status_publikasi`, `komentar_publikasi`, `tanggal_review_publikasi`, `link_repository`, `tanggal_publikasi`, `file_seminar_proposal`, `file_seminar_skripsi`, `file_skripsi_final`, `surat_izin_penelitian`, `status_izin_penelitian`) VALUES
(36, 32, 'Pengaruh Gaya Berpacaran terhadap Partisipasi Orang Muda Katolik (OMK) dalam Hidup Menggereja di Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke Tahun 2025', 'Partisipasi Orang Muda Katolik (OMK) dalam hidup menggereja merupakan indikator penting keberlangsungan Gereja Katolik di masa depan. Namun, kenyataan di lapangan menunjukkan adanya penurunan keterlibatan OMK dalam kegiatan-kegiatan gerejawi, seperti', 'Kuantitatif', 'Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke', 'Partisipasi Orang Muda Katolik (OMK) dalam hidup menggereja merupakan indikator penting keberlangsungan Gereja Katolik di masa depan. Namun, kenyataan di lapangan menunjukkan adanya penurunan keterlibatan OMK dalam kegiatan-kegiatan gerejawi, seperti perayaan Ekaristi, doa lingkungan, dan pelayanan sosial. Salah satu faktor yang diduga berkontribusi terhadap rendahnya partisipasi tersebut adalah gaya berpacaran yang dijalani oleh OMK. Di Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke, fenomena ini mulai tampak signifikan. Gaya pacaran yang tidak sehat—seperti hubungan yang posesif, terlalu mendominasi waktu, atau berorientasi pada kesenangan semata—berpotensi mengalihkan fokus dan komitmen OMK dari kegiatan rohani dan pelayanan gerejawi. Di sisi lain, gaya pacaran yang dewasa dan dilandasi nilai-nilai Kristiani justru dapat mendorong partisipasi aktif dalam kehidupan menggereja. Oleh karena itu, penting untuk menelaah lebih jauh bagaimana gaya berpacaran OMK memengaruhi tingkat keterlibatan mereka dalam hidup menggereja. Penelitian ini bertujuan untuk mengidentifikasi pola gaya pacaran yang dominan serta dampaknya terhadap semangat OMK dalam menjalani hidup menggereja di lingkungan Stasi Santo Mikael, demi merancang strategi pastoral yang lebih efektif.', '306cf686ff3f7323b18304b48f7c6e43.docx', '2025-07-18 10:21:14', 25, 1, NULL, NULL, '0', '1', 'Proposal sudah baik dan bisa langsung mulai bimbingan. Terimakasih', '2025-07-17 17:18:16', '2', 'Beban kerja berlebih', '2025-07-23 16:36:04', NULL, '2025-07-17 17:18:16', 10, 'proposal', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_mahasiswa_backup_full_20250725_070204`
--

CREATE TABLE `proposal_mahasiswa_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
  `status_izin_penelitian` enum('0','1','2') DEFAULT '0' COMMENT '0=belum diminta, 1=disetujui, 2=ditolak',
  `tanggal_penetapan_ulang` datetime DEFAULT NULL,
  `penetapan_ulang_oleh` bigint(20) DEFAULT NULL,
  `alasan_penetapan_ulang` text DEFAULT NULL,
  `jumlah_penetapan_ulang` int(11) DEFAULT 0,
  `validasi_staf_publikasi` enum('0','1','2') DEFAULT '0' COMMENT '0=menunggu, 1=valid, 2=perlu perbaikan',
  `staf_validator_id` bigint(20) DEFAULT NULL COMMENT 'ID staf yang memvalidasi',
  `tanggal_validasi_staf` datetime DEFAULT NULL,
  `catatan_staf` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposal_mahasiswa_backup_full_20250725_070204`
--

INSERT INTO `proposal_mahasiswa_backup_full_20250725_070204` (`id`, `mahasiswa_id`, `judul`, `ringkasan`, `jenis_penelitian`, `lokasi_penelitian`, `uraian_masalah`, `file_draft_proposal`, `created_at`, `dosen_id`, `dosen2_id`, `dosen_penguji_id`, `dosen_penguji2_id`, `status`, `status_kaprodi`, `komentar_kaprodi`, `tanggal_review_kaprodi`, `status_pembimbing`, `komentar_pembimbing`, `tanggal_respon_pembimbing`, `deadline`, `tanggal_penetapan`, `penetapan_oleh`, `workflow_status`, `status_seminar_proposal`, `komentar_seminar_proposal`, `tanggal_review_seminar_proposal`, `tanggal_seminar_proposal`, `tempat_seminar_proposal`, `status_seminar_skripsi`, `komentar_seminar_skripsi`, `tanggal_review_seminar_skripsi`, `tanggal_seminar_skripsi`, `tempat_seminar_skripsi`, `status_publikasi`, `komentar_publikasi`, `tanggal_review_publikasi`, `link_repository`, `tanggal_publikasi`, `file_seminar_proposal`, `file_seminar_skripsi`, `file_skripsi_final`, `surat_izin_penelitian`, `status_izin_penelitian`, `tanggal_penetapan_ulang`, `penetapan_ulang_oleh`, `alasan_penetapan_ulang`, `jumlah_penetapan_ulang`, `validasi_staf_publikasi`, `staf_validator_id`, `tanggal_validasi_staf`, `catatan_staf`) VALUES
(36, 32, 'Pengaruh Gaya Berpacaran terhadap Partisipasi Orang Muda Katolik (OMK) dalam Hidup Menggereja di Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke Tahun 2025', 'Partisipasi Orang Muda Katolik (OMK) dalam hidup menggereja merupakan indikator penting keberlangsungan Gereja Katolik di masa depan. Namun, kenyataan di lapangan menunjukkan adanya penurunan keterlibatan OMK dalam kegiatan-kegiatan gerejawi, seperti', 'Kuantitatif', 'Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke', 'Partisipasi Orang Muda Katolik (OMK) dalam hidup menggereja merupakan indikator penting keberlangsungan Gereja Katolik di masa depan. Namun, kenyataan di lapangan menunjukkan adanya penurunan keterlibatan OMK dalam kegiatan-kegiatan gerejawi, seperti perayaan Ekaristi, doa lingkungan, dan pelayanan sosial. Salah satu faktor yang diduga berkontribusi terhadap rendahnya partisipasi tersebut adalah gaya berpacaran yang dijalani oleh OMK. Di Stasi Santo Mikael, Paroki Sang Penebus Kampung Baru, Keuskupan Agung Merauke, fenomena ini mulai tampak signifikan. Gaya pacaran yang tidak sehat—seperti hubungan yang posesif, terlalu mendominasi waktu, atau berorientasi pada kesenangan semata—berpotensi mengalihkan fokus dan komitmen OMK dari kegiatan rohani dan pelayanan gerejawi. Di sisi lain, gaya pacaran yang dewasa dan dilandasi nilai-nilai Kristiani justru dapat mendorong partisipasi aktif dalam kehidupan menggereja. Oleh karena itu, penting untuk menelaah lebih jauh bagaimana gaya berpacaran OMK memengaruhi tingkat keterlibatan mereka dalam hidup menggereja. Penelitian ini bertujuan untuk mengidentifikasi pola gaya pacaran yang dominan serta dampaknya terhadap semangat OMK dalam menjalani hidup menggereja di lingkungan Stasi Santo Mikael, demi merancang strategi pastoral yang lebih efektif.', '306cf686ff3f7323b18304b48f7c6e43.docx', '2025-07-18 10:21:14', 25, 1, NULL, NULL, '0', '1', 'Proposal sudah baik dan bisa langsung mulai bimbingan. Terimakasih', '2025-07-17 17:18:16', '1', 'Baik saya menerima', '2025-07-23 17:55:14', NULL, NULL, NULL, 'bimbingan', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '2025-07-23 17:45:02', 10, 'Dosen pembimbing sebelumnya () menolak penunjukan. Menetapkan dosen pembimbing baru untuk melanjutkan proses bimbingan mahasiswa.', 0, '0', NULL, NULL, NULL),
(37, 33, 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke', 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke, ini latihan saja ya', 'Kuantitatif', 'STK St. Yakobus Merauke', 'Pengaruh Pendidikan Seksualitas terhadap Minat Berprestasi Mahasiswa Sekolah TInggi Katolik Santo Yakobus Merauke, ini latihan saja ya', 'd2ff01bd1f6cb9b54d4059526a3fb112.docx', '2025-07-18 10:21:14', 26, 1, NULL, NULL, '0', '1', 'Lanjutkan', '2025-07-23 18:35:54', '1', 'Ya saya setuju membimbing ', '2025-07-23 19:25:53', NULL, '2025-07-23 18:35:54', 10, 'bimbingan', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL),
(41, 42, 'Pengaruh Tes Saja', 'Pengaruh Tes Saja', 'Kuantitatif', 'Merauke', 'Pengaruh Tes Saja', '68795a1fa5ea2fa268d3bfe05362db14.docx', '2025-07-23 19:10:29', 25, 1, NULL, NULL, '0', '1', 'Lanjutkan bimbingan', '2025-07-23 19:11:52', '1', 'Ok saya setuju\r\n', '2025-07-24 18:34:07', NULL, '2025-07-23 19:11:52', 10, 'bimbingan', '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, 0, '0', NULL, NULL, NULL);

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

-- --------------------------------------------------------

--
-- Table structure for table `proposal_workflow_backup_full_20250725_070204`
--

CREATE TABLE `proposal_workflow_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `proposal_id` bigint(20) NOT NULL,
  `tahap` enum('pengajuan','review_kaprodi','approval_pembimbing','penetapan_selesai') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `komentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `diproses_oleh` bigint(20) DEFAULT NULL,
  `tanggal_proses` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `proposal_workflow_backup_full_20250725_070204`
--

INSERT INTO `proposal_workflow_backup_full_20250725_070204` (`id`, `proposal_id`, `tahap`, `status`, `komentar`, `diproses_oleh`, `tanggal_proses`) VALUES
(3, 36, 'pengajuan', 'approved', NULL, 32, '2025-07-17 08:59:52'),
(4, 37, 'pengajuan', 'approved', NULL, 33, '2025-07-17 08:59:52'),
(5, 36, '', 'approved', 'Penetapan ulang pembimbing dari dosen ID  ke dosen ID 25. Alasan: Dosen pembimbing sebelumnya () menolak penunjukan. Menetapkan dosen pembimbing baru untuk melanjutkan proses bimbingan mahasiswa.', 10, '2025-07-23 17:45:02');

-- --------------------------------------------------------

--
-- Stand-in structure for view `publikasi_mahasiswa_v`
-- (See below for the actual view)
--
CREATE TABLE `publikasi_mahasiswa_v` (
`id` bigint(20)
,`proposal_mahasiswa_id` bigint(20)
,`mahasiswa_id` bigint(20)
,`nama_mahasiswa` varchar(100)
,`nim` varchar(50)
,`program_studi` enum('Pendidikan Keagamaan Katolik','Pendidikan Guru Sekolah Dasar')
,`judul_skripsi_final` text
,`nama_dosen_pembimbing` varchar(100)
,`tanggal_ujian_skripsi` date
,`status` enum('draft','submitted','review_pembimbing','review_staf','completed','rejected')
,`status_pembimbing` enum('pending','approved','rejected')
,`status_staf` enum('pending','approved','rejected')
,`tanggal_pengajuan` datetime
,`tanggal_review_pembimbing` datetime
,`tanggal_validasi_staf` datetime
,`tanggal_selesai` datetime
,`link_repository` varchar(500)
,`file_surat_revisi` varchar(255)
,`file_skripsi_final` varchar(255)
,`file_surat_perpustakaan` varchar(255)
,`keterangan_mahasiswa` text
,`komentar_pembimbing` text
,`komentar_staf` text
,`email_mahasiswa` varchar(100)
,`nomor_telepon` varchar(30)
,`workflow_status` enum('proposal','bimbingan','seminar_proposal','penelitian','seminar_skripsi','publikasi','selesai')
,`judul_proposal_awal` varchar(250)
,`nama_pembimbing_lengkap` varchar(100)
,`email_pembimbing` varchar(100)
,`status_description` varchar(26)
,`progress_percentage` int(3)
,`syarat_publikasi_status` varchar(500)
,`created_at` datetime
,`updated_at` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `publikasi_tugas_akhir`
--

CREATE TABLE `publikasi_tugas_akhir` (
  `id` bigint(20) NOT NULL,
  `proposal_mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa',
  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa untuk redundancy check',
  `nama_mahasiswa` varchar(100) NOT NULL COMMENT 'Nama lengkap mahasiswa',
  `nim` varchar(50) NOT NULL COMMENT 'NIM mahasiswa',
  `program_studi` enum('Pendidikan Keagamaan Katolik','Pendidikan Guru Sekolah Dasar') NOT NULL,
  `judul_skripsi_final` text NOT NULL COMMENT 'Judul skripsi final (input manual)',
  `dosen_pembimbing_id` bigint(20) NOT NULL COMMENT 'FK ke dosen pembimbing',
  `nama_dosen_pembimbing` varchar(100) NOT NULL COMMENT 'Nama dosen pembimbing',
  `tanggal_ujian_skripsi` date NOT NULL COMMENT 'Tanggal ujian skripsi (auto dari seminar skripsi)',
  `file_surat_revisi` varchar(255) NOT NULL COMMENT 'Surat keterangan telah melaksanakan revisi (PDF max 1MB)',
  `file_skripsi_final` varchar(255) NOT NULL COMMENT 'File skripsi final (PDF max 5MB)',
  `file_surat_perpustakaan` varchar(255) NOT NULL COMMENT 'Surat keterangan penyerahan skripsi dari perpustakaan (PDF max 1MB)',
  `link_repository` varchar(500) DEFAULT NULL COMMENT 'Link repository/publikasi tugas akhir (opsional, diinput oleh staf)',
  `file_surat_keterangan` varchar(255) DEFAULT NULL COMMENT 'File surat keterangan publikasi',
  `status` enum('draft','submitted','review_pembimbing','review_staf','completed','rejected') DEFAULT 'draft' COMMENT 'Status pengajuan',
  `status_pembimbing` enum('pending','approved','rejected') DEFAULT 'pending' COMMENT 'Status approval dosen pembimbing',
  `status_staf` enum('pending','approved','rejected') DEFAULT 'pending' COMMENT 'Status validasi staf',
  `keterangan_mahasiswa` text DEFAULT NULL COMMENT 'Catatan dari mahasiswa',
  `komentar_pembimbing` text DEFAULT NULL COMMENT 'Komentar dari dosen pembimbing',
  `komentar_staf` text DEFAULT NULL COMMENT 'Komentar dari staf',
  `tanggal_pengajuan` datetime DEFAULT NULL COMMENT 'Tanggal mahasiswa submit pengajuan',
  `tanggal_review_pembimbing` datetime DEFAULT NULL COMMENT 'Tanggal dosen memberikan review',
  `tanggal_validasi_staf` datetime DEFAULT NULL COMMENT 'Tanggal staf validasi',
  `tanggal_selesai` datetime DEFAULT NULL COMMENT 'Tanggal proses publikasi selesai',
  `validated_by_staf_id` bigint(20) DEFAULT NULL COMMENT 'ID staf yang melakukan validasi',
  `validated_by_staf_name` varchar(100) DEFAULT NULL COMMENT 'Nama staf yang validasi',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk mengelola publikasi tugas akhir mahasiswa';

--
-- Triggers `publikasi_tugas_akhir`
--
DELIMITER $$
CREATE TRIGGER `tr_publikasi_completed_safe` AFTER UPDATE ON `publikasi_tugas_akhir` FOR EACH ROW BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        UPDATE proposal_mahasiswa 
        SET workflow_status = 'selesai'
        WHERE id = NEW.proposal_mahasiswa_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_publikasi_log_safe` AFTER UPDATE ON `publikasi_tugas_akhir` FOR EACH ROW BEGIN
    IF NEW.status != OLD.status THEN
        INSERT INTO log_publikasi (publikasi_id, user_id, user_role, user_name, aktivitas, deskripsi, created_at) 
        VALUES (NEW.id, COALESCE(NEW.validated_by_staf_id, NEW.mahasiswa_id), 
                CASE WHEN NEW.validated_by_staf_id IS NOT NULL THEN 'staf' ELSE 'system' END,
                COALESCE(NEW.validated_by_staf_name, 'System Auto'), 'status_changed',
                CONCAT('Status berubah dari ', OLD.status, ' ke ', NEW.status), NOW());
    END IF;
END
$$
DELIMITER ;

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
-- Table structure for table `seminar_backup_before_mahasiswa_phase`
--

CREATE TABLE `seminar_backup_before_mahasiswa_phase` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
-- Table structure for table `seminar_backup_full_20250725_070204`
--

CREATE TABLE `seminar_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
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
-- Table structure for table `seminar_proposal_mahasiswa`
--

CREATE TABLE `seminar_proposal_mahasiswa` (
  `id` bigint(20) NOT NULL,
  `proposal_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa (READ ONLY)',
  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa (redundant untuk performance)',
  `status` enum('draft','submitted','review_pembimbing','review_kaprodi','approved','rejected','scheduled','completed') DEFAULT 'draft',
  `current_step` varchar(50) DEFAULT 'mahasiswa' COMMENT 'mahasiswa|pembimbing|kaprodi|staf',
  `file_proposal` varchar(255) DEFAULT NULL COMMENT 'File proposal untuk seminar (Word/PDF max 1MB)',
  `keterangan_mahasiswa` text DEFAULT NULL COMMENT 'Keterangan tambahan dari mahasiswa',
  `judul_seminar` varchar(250) DEFAULT NULL COMMENT 'Judul proposal untuk seminar (bisa berbeda dari usulan awal)',
  `status_pembimbing` enum('pending','approved','rejected') DEFAULT 'pending',
  `komentar_pembimbing` text DEFAULT NULL,
  `tanggal_review_pembimbing` datetime DEFAULT NULL,
  `reviewed_by_pembimbing` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen',
  `status_kaprodi` enum('pending','approved','rejected') DEFAULT 'pending',
  `komentar_kaprodi` text DEFAULT NULL,
  `tanggal_review_kaprodi` datetime DEFAULT NULL,
  `reviewed_by_kaprodi` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen',
  `file_turnitin` varchar(255) DEFAULT NULL COMMENT 'File hasil Turnitin dari Kaprodi',
  `plagiarism_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Persentase plagiarisme dari Turnitin',
  `tanggal_seminar` date DEFAULT NULL,
  `jam_seminar` time DEFAULT NULL,
  `tempat_seminar` varchar(255) DEFAULT NULL,
  `dosen_penguji1_id` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen',
  `dosen_penguji2_id` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen',
  `status_penguji1` enum('pending','approved','rejected') DEFAULT 'pending',
  `komentar_penguji1` text DEFAULT NULL,
  `tanggal_respon_penguji1` datetime DEFAULT NULL,
  `status_penguji2` enum('pending','approved','rejected') DEFAULT 'pending',
  `komentar_penguji2` text DEFAULT NULL,
  `tanggal_respon_penguji2` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL COMMENT 'FK ke mahasiswa yang membuat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Seminar Proposal Mahasiswa - Phase 3 (Safe Implementation)';

--
-- Triggers `seminar_proposal_mahasiswa`
--
DELIMITER $$
CREATE TRIGGER `tr_seminar_proposal_mhs_insert` AFTER INSERT ON `seminar_proposal_mahasiswa` FOR EACH ROW BEGIN
    -- Update workflow_status di proposal_mahasiswa existing table
    UPDATE proposal_mahasiswa 
    SET workflow_status = 'seminar_proposal'
    WHERE id = NEW.proposal_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_seminar_proposal_mhs_update` AFTER UPDATE ON `seminar_proposal_mahasiswa` FOR EACH ROW BEGIN
    -- Jika status berubah ke completed, siap ke fase penelitian
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        UPDATE proposal_mahasiswa 
        SET workflow_status = 'penelitian'
        WHERE id = NEW.proposal_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `seminar_proposal_mahasiswa_backup_$(date +%Y%m%d)`
--

CREATE TABLE `seminar_proposal_mahasiswa_backup_$(date +%Y%m%d)` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `proposal_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa (READ ONLY)',
  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa (redundant untuk performance)',
  `status` enum('draft','submitted','review_pembimbing','review_kaprodi','approved','rejected','scheduled','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `current_step` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'mahasiswa' COMMENT 'mahasiswa|pembimbing|kaprodi|staf',
  `file_proposal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File proposal untuk seminar (Word/PDF max 1MB)',
  `keterangan_mahasiswa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Keterangan tambahan dari mahasiswa',
  `status_pembimbing` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `komentar_pembimbing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_review_pembimbing` datetime DEFAULT NULL,
  `reviewed_by_pembimbing` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen',
  `status_kaprodi` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `komentar_kaprodi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_review_kaprodi` datetime DEFAULT NULL,
  `reviewed_by_kaprodi` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen',
  `file_turnitin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File hasil Turnitin dari Kaprodi',
  `plagiarism_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Persentase plagiarisme dari Turnitin',
  `tanggal_seminar` date DEFAULT NULL,
  `jam_seminar` time DEFAULT NULL,
  `tempat_seminar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dosen_penguji1_id` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen',
  `dosen_penguji2_id` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen',
  `status_penguji1` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `komentar_penguji1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_respon_penguji1` datetime DEFAULT NULL,
  `status_penguji2` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `komentar_penguji2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_respon_penguji2` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL COMMENT 'FK ke mahasiswa yang membuat'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `seminar_proposal_mahasiswa_backup_$(date +%Y%m%d)`
--

INSERT INTO `seminar_proposal_mahasiswa_backup_$(date +%Y%m%d)` (`id`, `proposal_id`, `mahasiswa_id`, `status`, `current_step`, `file_proposal`, `keterangan_mahasiswa`, `status_pembimbing`, `komentar_pembimbing`, `tanggal_review_pembimbing`, `reviewed_by_pembimbing`, `status_kaprodi`, `komentar_kaprodi`, `tanggal_review_kaprodi`, `reviewed_by_kaprodi`, `file_turnitin`, `plagiarism_percentage`, `tanggal_seminar`, `jam_seminar`, `tempat_seminar`, `dosen_penguji1_id`, `dosen_penguji2_id`, `status_penguji1`, `komentar_penguji1`, `tanggal_respon_penguji1`, `status_penguji2`, `komentar_penguji2`, `tanggal_respon_penguji2`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 44, 44, 'submitted', 'pembimbing', '65deab67fc9fb8be407309c6ff4caf63.docx', 'Proposal Fix ya', 'pending', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, 'pending', NULL, NULL, '2025-07-28 12:49:03', '2025-07-28 12:49:03', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `seminar_proposal_mahasiswa_v`
-- (See below for the actual view)
--
CREATE TABLE `seminar_proposal_mahasiswa_v` (
`id` bigint(20)
,`proposal_id` bigint(20)
,`mahasiswa_id` bigint(20)
,`status` enum('draft','submitted','review_pembimbing','review_kaprodi','approved','rejected','scheduled','completed')
,`current_step` varchar(50)
,`file_proposal` varchar(255)
,`keterangan_mahasiswa` text
,`status_pembimbing` enum('pending','approved','rejected')
,`status_kaprodi` enum('pending','approved','rejected')
,`tanggal_seminar` date
,`jam_seminar` time
,`tempat_seminar` varchar(255)
,`created_at` datetime
,`updated_at` datetime
,`nim` varchar(50)
,`nama_mahasiswa` varchar(100)
,`email_mahasiswa` varchar(100)
,`judul` varchar(250)
,`workflow_status` enum('proposal','bimbingan','seminar_proposal','penelitian','seminar_skripsi','publikasi','selesai')
,`pembimbing_id` bigint(20)
,`nama_pembimbing` varchar(100)
,`email_pembimbing` varchar(100)
,`nama_penguji1` varchar(100)
,`nama_penguji2` varchar(100)
,`status_description` varchar(32)
);

-- --------------------------------------------------------

--
-- Table structure for table `seminar_skripsi_mahasiswa`
--

CREATE TABLE `seminar_skripsi_mahasiswa` (
  `id` bigint(20) NOT NULL,
  `proposal_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa (READ ONLY)',
  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa (redundant untuk performance)',
  `status` enum('draft','submitted','review_pembimbing','review_kaprodi','approved','rejected','scheduled','completed') DEFAULT 'draft',
  `current_step` varchar(50) DEFAULT 'mahasiswa' COMMENT 'mahasiswa|pembimbing|kaprodi|staf',
  `file_skripsi` varchar(255) DEFAULT NULL COMMENT 'File skripsi final lengkap (Word/PDF max 2MB)',
  `keterangan_mahasiswa` text DEFAULT NULL COMMENT 'Keterangan tambahan dari mahasiswa (opsional)',
  `judul_skripsi` varchar(250) DEFAULT NULL COMMENT 'Judul skripsi untuk seminar (bisa berbeda dari proposal)',
  `surat_keterangan_penelitian` varchar(255) DEFAULT NULL COMMENT 'File surat keterangan penelitian',
  `status_pembimbing` enum('pending','approved','rejected') DEFAULT 'pending',
  `komentar_pembimbing` text DEFAULT NULL COMMENT 'Komentar/feedback dari dosen pembimbing',
  `tanggal_review_pembimbing` datetime DEFAULT NULL,
  `reviewed_by_pembimbing` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen pembimbing',
  `status_kaprodi` enum('pending','approved','rejected') DEFAULT 'pending',
  `komentar_kaprodi` text DEFAULT NULL COMMENT 'Komentar validasi dari Kaprodi',
  `tanggal_review_kaprodi` datetime DEFAULT NULL,
  `reviewed_by_kaprodi` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen Kaprodi',
  `file_turnitin` varchar(255) DEFAULT NULL COMMENT 'File hasil Turnitin dari Kaprodi',
  `plagiarism_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Persentase plagiarisme dari Turnitin (max 30%)',
  `tanggal_seminar` date DEFAULT NULL COMMENT 'Tanggal pelaksanaan seminar skripsi',
  `jam_seminar` time DEFAULT NULL COMMENT 'Jam pelaksanaan seminar skripsi',
  `tempat_seminar` varchar(255) DEFAULT NULL COMMENT 'Tempat pelaksanaan seminar skripsi',
  `dosen_penguji1_id` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen penguji 1 (auto dari seminar proposal)',
  `dosen_penguji2_id` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen penguji 2 (auto dari seminar proposal)',
  `status_penguji1` enum('pending','approved','rejected') DEFAULT 'approved' COMMENT 'Default approved (langsung ditunjuk)',
  `komentar_penguji1` text DEFAULT NULL COMMENT 'Komentar dari penguji 1 (opsional)',
  `tanggal_respon_penguji1` datetime DEFAULT NULL,
  `status_penguji2` enum('pending','approved','rejected') DEFAULT 'approved' COMMENT 'Default approved (langsung ditunjuk)',
  `komentar_penguji2` text DEFAULT NULL COMMENT 'Komentar dari penguji 2 (opsional)',
  `tanggal_respon_penguji2` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Tanggal pengajuan oleh mahasiswa',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL COMMENT 'FK ke mahasiswa yang membuat pengajuan'
) ;

--
-- Triggers `seminar_skripsi_mahasiswa`
--
DELIMITER $$
CREATE TRIGGER `tr_seminar_skripsi_mhs_insert` AFTER INSERT ON `seminar_skripsi_mahasiswa` FOR EACH ROW BEGIN
    UPDATE proposal_mahasiswa 
    SET workflow_status = 'seminar_skripsi'
    WHERE id = NEW.proposal_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_seminar_skripsi_mhs_update` AFTER UPDATE ON `seminar_skripsi_mahasiswa` FOR EACH ROW BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        UPDATE proposal_mahasiswa 
        SET workflow_status = 'publikasi'
        WHERE id = NEW.proposal_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `seminar_skripsi_mahasiswa_backup_20250804`
--

CREATE TABLE `seminar_skripsi_mahasiswa_backup_20250804` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `proposal_id` bigint(20) NOT NULL COMMENT 'FK ke proposal_mahasiswa (READ ONLY)',
  `mahasiswa_id` bigint(20) NOT NULL COMMENT 'FK ke mahasiswa (redundant untuk performance)',
  `status` enum('draft','submitted','review_pembimbing','review_kaprodi','approved','rejected','scheduled','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `current_step` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'mahasiswa' COMMENT 'mahasiswa|pembimbing|kaprodi|staf',
  `file_skripsi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File skripsi final lengkap (Word/PDF max 2MB)',
  `keterangan_mahasiswa` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Keterangan tambahan dari mahasiswa (opsional)',
  `judul_skripsi` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Judul skripsi untuk seminar (bisa berbeda dari proposal)',
  `surat_keterangan_penelitian` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File surat keterangan penelitian',
  `status_pembimbing` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `komentar_pembimbing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Komentar/feedback dari dosen pembimbing',
  `tanggal_review_pembimbing` datetime DEFAULT NULL,
  `reviewed_by_pembimbing` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen pembimbing',
  `status_kaprodi` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `komentar_kaprodi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Komentar validasi dari Kaprodi',
  `tanggal_review_kaprodi` datetime DEFAULT NULL,
  `reviewed_by_kaprodi` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen Kaprodi',
  `file_turnitin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'File hasil Turnitin dari Kaprodi',
  `plagiarism_percentage` decimal(5,2) DEFAULT NULL COMMENT 'Persentase plagiarisme dari Turnitin (max 30%)',
  `tanggal_seminar` date DEFAULT NULL COMMENT 'Tanggal pelaksanaan seminar skripsi',
  `jam_seminar` time DEFAULT NULL COMMENT 'Jam pelaksanaan seminar skripsi',
  `tempat_seminar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tempat pelaksanaan seminar skripsi',
  `dosen_penguji1_id` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen penguji 1 (auto dari seminar proposal)',
  `dosen_penguji2_id` bigint(20) DEFAULT NULL COMMENT 'FK ke dosen penguji 2 (auto dari seminar proposal)',
  `status_penguji1` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'approved' COMMENT 'Default approved (langsung ditunjuk)',
  `komentar_penguji1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Komentar dari penguji 1 (opsional)',
  `tanggal_respon_penguji1` datetime DEFAULT NULL,
  `status_penguji2` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'approved' COMMENT 'Default approved (langsung ditunjuk)',
  `komentar_penguji2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Komentar dari penguji 2 (opsional)',
  `tanggal_respon_penguji2` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'Tanggal pengajuan oleh mahasiswa',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL COMMENT 'FK ke mahasiswa yang membuat pengajuan'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `seminar_skripsi_mahasiswa_backup_20250804`
--

INSERT INTO `seminar_skripsi_mahasiswa_backup_20250804` (`id`, `proposal_id`, `mahasiswa_id`, `status`, `current_step`, `file_skripsi`, `keterangan_mahasiswa`, `judul_skripsi`, `surat_keterangan_penelitian`, `status_pembimbing`, `komentar_pembimbing`, `tanggal_review_pembimbing`, `reviewed_by_pembimbing`, `status_kaprodi`, `komentar_kaprodi`, `tanggal_review_kaprodi`, `reviewed_by_kaprodi`, `file_turnitin`, `plagiarism_percentage`, `tanggal_seminar`, `jam_seminar`, `tempat_seminar`, `dosen_penguji1_id`, `dosen_penguji2_id`, `status_penguji1`, `komentar_penguji1`, `tanggal_respon_penguji1`, `status_penguji2`, `komentar_penguji2`, `tanggal_respon_penguji2`, `created_at`, `updated_at`, `created_by`) VALUES
(11, 44, 44, 'submitted', 'mahasiswa', '1fba172d0cb18a5a282f585a2d8d781d.pdf', 'Perbaikan pengajuan', 'Perbaikan Pengaruh Pembelajaran Aktif terhadap Hasil Belajar Kognitif Mahasiswa Sekolah Tinggi Katolik Santo Yakobus Merauke Tahun Akademik 2024/2025', '0cabd8645f96a6b4a58e9e0f6f3bdf89.pdf', 'pending', NULL, '2025-08-03 17:30:13', 25, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'approved', NULL, NULL, '2025-08-03 06:30:48', '2025-08-04 07:21:34', NULL),
(12, 46, 46, 'review_kaprodi', 'kaprodi', 'df6c327d48141b9b85fcbcc07203c423.pdf', 'INI LATIHAN SAJA', NULL, NULL, 'approved', '', '2025-08-03 12:16:41', 25, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'approved', NULL, NULL, '2025-08-03 09:17:54', '2025-08-03 12:16:41', NULL),
(13, 45, 45, 'review_kaprodi', 'kaprodi', 'c8988a951c333c3c93c5b101981316bb.pdf', 'Pengajuan Seminar Baru', 'BARU PENGARUH PENGGUNAAN MEDIA TEKNOLOGI PEMBELAJARAN TERHADAP HASIL BELAJAR SISWA SMPN 2 MERAUKE', '7cff7dc2b9f0be396787f32057c56594.pdf', 'approved', 'Disetujui', '2025-08-04 08:35:55', 25, 'pending', NULL, NULL, NULL, NULL, 25.50, NULL, NULL, NULL, NULL, NULL, 'approved', NULL, NULL, 'approved', NULL, NULL, '2025-08-04 07:22:46', '2025-08-04 08:45:23', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `seminar_skripsi_progress_v`
-- (See below for the actual view)
--
CREATE TABLE `seminar_skripsi_progress_v` (
`phase` varchar(15)
,`total_mahasiswa` bigint(21)
,`draft_count` bigint(21)
,`submitted_count` bigint(21)
,`review_pembimbing_count` bigint(21)
,`review_kaprodi_count` bigint(21)
,`approved_count` bigint(21)
,`scheduled_count` bigint(21)
,`completed_count` bigint(21)
,`rejected_count` bigint(21)
,`avg_progress_percentage` decimal(6,4)
);

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

-- --------------------------------------------------------

--
-- Table structure for table `skripsi_backup_full_20250725_070204`
--

CREATE TABLE `skripsi_backup_full_20250725_070204` (
  `id` int(11) NOT NULL DEFAULT 0,
  `judul_skripsi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dosen_id` int(11) DEFAULT NULL,
  `dosen_penguji_id` int(11) DEFAULT NULL,
  `file_skripsi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sk_tim` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mahasiswa_id` int(11) DEFAULT NULL,
  `jadwal_skripsi` datetime DEFAULT NULL,
  `status` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `persetujuan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bukti_konsultasi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `skripsi_backup_full_20250725_070204`
--

INSERT INTO `skripsi_backup_full_20250725_070204` (`id`, `judul_skripsi`, `dosen_id`, `dosen_penguji_id`, `file_skripsi`, `sk_tim`, `mahasiswa_id`, `jadwal_skripsi`, `status`, `persetujuan`, `bukti_konsultasi`) VALUES
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
-- Table structure for table `staf_aktivitas`
--

CREATE TABLE `staf_aktivitas` (
  `id` bigint(20) NOT NULL,
  `staf_id` bigint(20) NOT NULL,
  `aktivitas` enum('export_jurnal','export_berita_acara','export_surat_izin','upload_repository','validasi_publikasi') DEFAULT NULL,
  `mahasiswa_id` bigint(20) DEFAULT NULL,
  `proposal_id` bigint(20) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `file_output` varchar(255) DEFAULT NULL,
  `tanggal_aktivitas` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staf_aktivitas`
--

INSERT INTO `staf_aktivitas` (`id`, `staf_id`, `aktivitas`, `mahasiswa_id`, `proposal_id`, `keterangan`, `file_output`, `tanggal_aktivitas`) VALUES
(7, 29, '', NULL, NULL, 'Export semua data bimbingan format Excel XML (2 records)', NULL, '2025-07-24 18:04:41'),
(8, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:47:19'),
(9, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:47:41'),
(10, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:56:43'),
(11, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:56:48'),
(12, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:56:52'),
(13, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:56:56'),
(14, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:57:00'),
(15, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:57:03'),
(16, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 14:58:52'),
(17, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 15:39:03'),
(18, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 16:16:57'),
(19, 29, '', 44, 44, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-07-25 17:35:43'),
(20, 29, '', 45, 45, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh 2', NULL, '2025-07-27 15:35:10'),
(21, 29, '', NULL, NULL, 'Export semua data bimbingan format Excel XML (2 records)', NULL, '2025-07-27 15:39:49'),
(22, 29, '', 45, 45, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh 2', NULL, '2025-07-27 15:40:49'),
(23, 29, '', NULL, NULL, 'Export semua data bimbingan format Excel XML (2 records)', NULL, '2025-07-27 16:58:46'),
(24, 29, '', 45, 45, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh 2', NULL, '2025-07-27 17:42:32'),
(25, 29, '', NULL, 44, 'Cetak form permohonan untuk MAHASISWA CONTOH', NULL, '2025-08-01 15:00:05'),
(26, 29, '', NULL, 44, 'Cetak surat izin penelitian untuk Mahasiswa Contoh', NULL, '2025-08-01 15:00:10'),
(27, 29, '', NULL, 44, 'Cetak surat izin penelitian untuk Mahasiswa Contoh', NULL, '2025-08-01 15:01:59'),
(28, 29, '', NULL, 44, 'Cetak form permohonan untuk MAHASISWA CONTOH', NULL, '2025-08-01 15:02:02'),
(29, 30, '', NULL, 44, 'Cetak form permohonan untuk MAHASISWA CONTOH', NULL, '2025-08-01 15:40:54'),
(30, 30, '', NULL, 44, 'Cetak surat izin penelitian untuk Mahasiswa Contoh', NULL, '2025-08-01 15:41:00'),
(31, 30, '', NULL, 44, 'Upload surat izin penelitian untuk Mahasiswa Contoh', NULL, '2025-08-01 16:07:27'),
(32, 30, '', NULL, 44, 'Cetak surat izin penelitian untuk Mahasiswa Contoh', NULL, '2025-08-01 16:07:35'),
(33, 29, '', NULL, 47, 'Upload surat izin penelitian untuk Agus Bumagi', NULL, '2025-08-03 08:16:40'),
(34, 29, '', 47, 47, 'Melihat detail bimbingan mahasiswa Agus Bumagi', NULL, '2025-08-05 16:01:14'),
(35, 29, '', NULL, 46, 'Upload surat izin penelitian untuk Mahasiswa Contoh 3', NULL, '2025-08-05 17:46:51'),
(36, 29, '', NULL, 45, 'Cetak form permohonan untuk MAHASISWA CONTOH 2', NULL, '2025-08-05 17:47:13'),
(37, 29, '', NULL, 45, 'Cetak surat izin penelitian untuk Mahasiswa Contoh 2', NULL, '2025-08-05 17:47:16'),
(38, 29, '', NULL, 45, 'Cetak form permohonan untuk MAHASISWA CONTOH 2', NULL, '2025-08-05 17:47:27'),
(39, 29, '', NULL, 45, 'Upload surat izin penelitian untuk Mahasiswa Contoh 2', NULL, '2025-08-05 17:47:37'),
(40, 29, '', 76, 73, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-08-10 17:46:17'),
(41, 29, '', 76, 73, 'Melihat detail bimbingan mahasiswa Mahasiswa Contoh', NULL, '2025-08-10 18:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `staf_aktivitas_backup_full_20250725_070204`
--

CREATE TABLE `staf_aktivitas_backup_full_20250725_070204` (
  `id` bigint(20) NOT NULL DEFAULT 0,
  `staf_id` bigint(20) NOT NULL,
  `aktivitas` enum('export_jurnal','export_berita_acara','export_surat_izin','upload_repository','validasi_publikasi') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mahasiswa_id` bigint(20) DEFAULT NULL,
  `proposal_id` bigint(20) DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `file_output` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_aktivitas` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `staf_aktivitas_backup_full_20250725_070204`
--

INSERT INTO `staf_aktivitas_backup_full_20250725_070204` (`id`, `staf_id`, `aktivitas`, `mahasiswa_id`, `proposal_id`, `keterangan`, `file_output`, `tanggal_aktivitas`) VALUES
(1, 29, 'export_jurnal', 33, 37, 'Export jurnal bimbingan mahasiswa Herybertus Oktaviani', NULL, '2025-07-24 12:25:03'),
(2, 29, 'export_jurnal', 33, 37, 'Export jurnal bimbingan mahasiswa Herybertus Oktaviani', NULL, '2025-07-24 17:36:42'),
(3, 29, 'export_jurnal', 32, 36, 'Export jurnal bimbingan mahasiswa Hendro Mahasiswa', NULL, '2025-07-24 17:37:38'),
(4, 29, 'export_jurnal', 33, 37, 'Export jurnal bimbingan mahasiswa Herybertus Oktaviani', NULL, '2025-07-24 17:47:18'),
(5, 29, 'export_jurnal', 33, 37, 'Export jurnal bimbingan mahasiswa Herybertus Oktaviani', NULL, '2025-07-24 17:48:27'),
(6, 29, 'export_jurnal', 33, 37, 'Export jurnal bimbingan mahasiswa Herybertus Oktaviani', NULL, '2025-07-24 17:49:50');

-- --------------------------------------------------------

--
-- Stand-in structure for view `staf_v`
-- (See below for the actual view)
--
CREATE TABLE `staf_v` (
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
-- Stand-in structure for view `validasi_stats_v`
-- (See below for the actual view)
--
CREATE TABLE `validasi_stats_v` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_penelitian_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `v_penelitian_dashboard` (
`permohonan_id` bigint(20)
,`proposal_mahasiswa_id` bigint(20)
,`nim` varchar(20)
,`nama_mahasiswa` varchar(100)
,`semester` varchar(10)
,`program_studi` enum('Pendidikan Keagamaan Katolik','Pendidikan Guru Sekolah Dasar')
,`judul_skripsi_terbaru` text
,`tempat_penelitian` varchar(255)
,`tanggal_mulai_penelitian` date
,`tanggal_selesai_penelitian` date
,`status` enum('draft','submitted','review_pembimbing','approved','rejected','surat_ready','completed')
,`status_pembimbing` enum('pending','approved','rejected')
,`tanggal_pengajuan` datetime
,`tanggal_review_pembimbing` datetime
,`tanggal_upload_surat_staf` datetime
,`nama_pembimbing` varchar(100)
,`nip_pembimbing` varchar(30)
,`email_pembimbing` varchar(100)
,`workflow_status` enum('proposal','bimbingan','seminar_proposal','penelitian','seminar_skripsi','publikasi','selesai')
,`status_izin_penelitian` enum('0','1','2')
,`surat_izin_penelitian` varchar(255)
,`status_description` varchar(36)
,`progress_percentage` int(3)
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
-- Structure for view `penilaian_seminar_proposal_v`
--
DROP TABLE IF EXISTS `penilaian_seminar_proposal_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `penilaian_seminar_proposal_v`  AS SELECT `psp`.`id` AS `id`, `psp`.`seminar_proposal_id` AS `seminar_proposal_id`, `psp`.`mahasiswa_id` AS `mahasiswa_id`, `psp`.`proposal_id` AS `proposal_id`, `m`.`nim` AS `nim`, `m`.`nama` AS `nama_mahasiswa`, `m`.`email` AS `email_mahasiswa`, `pm`.`judul` AS `judul`, `d`.`nama` AS `nama_pembimbing`, `spm`.`tanggal_seminar` AS `tanggal_seminar`, `spm`.`jam_seminar` AS `jam_seminar`, `spm`.`tempat_seminar` AS `tempat_seminar`, `psp`.`nilai_penguji1` AS `nilai_penguji1`, `psp`.`nilai_penguji2` AS `nilai_penguji2`, `psp`.`nilai_pembimbing` AS `nilai_pembimbing`, `psp`.`nilai_substansi_metode` AS `nilai_substansi_metode_old`, `psp`.`nilai_presentasi_teknik` AS `nilai_presentasi_teknik_old`, `psp`.`nilai_penguasaan_diskusi` AS `nilai_penguasaan_diskusi_old`, `psp`.`nilai_akhir` AS `nilai_akhir`, `psp`.`nilai_huruf` AS `nilai_huruf`, `psp`.`rekomendasi` AS `rekomendasi`, `psp`.`status_penilaian` AS `status_penilaian`, `psp`.`role_penilai` AS `role_penilai`, `dp`.`nama` AS `nama_penilai`, `psp`.`created_at` AS `created_at`, `psp`.`updated_at` AS `updated_at`, `psp`.`published_at` AS `published_at` FROM (((((`penilaian_seminar_proposal` `psp` join `seminar_proposal_mahasiswa` `spm` on(`psp`.`seminar_proposal_id` = `spm`.`id`)) join `mahasiswa` `m` on(`psp`.`mahasiswa_id` = `m`.`id`)) join `proposal_mahasiswa` `pm` on(`psp`.`proposal_id` = `pm`.`id`)) join `dosen` `d` on(`pm`.`dosen_id` = `d`.`id`)) join `dosen` `dp` on(`psp`.`dinilai_oleh` = `dp`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `penilaian_seminar_skripsi_v`
--
DROP TABLE IF EXISTS `penilaian_seminar_skripsi_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `penilaian_seminar_skripsi_v`  AS SELECT `pss`.`id` AS `id`, `pss`.`seminar_skripsi_id` AS `seminar_skripsi_id`, `pss`.`mahasiswa_id` AS `mahasiswa_id`, `pss`.`proposal_id` AS `proposal_id`, `pss`.`nilai_penguji1` AS `nilai_penguji1`, `pss`.`nilai_penguji2` AS `nilai_penguji2`, `pss`.`nilai_pembimbing` AS `nilai_pembimbing`, `pss`.`nilai_akhir` AS `nilai_akhir`, `pss`.`nilai_huruf` AS `nilai_huruf`, `pss`.`rekomendasi` AS `rekomendasi`, `pss`.`status_penilaian` AS `status_penilaian`, `pss`.`role_penilai` AS `role_penilai`, `pss`.`created_at` AS `created_at`, `pss`.`updated_at` AS `updated_at`, `pss`.`published_at` AS `published_at`, `m`.`nim` AS `nim`, `m`.`nama` AS `nama_mahasiswa`, `m`.`email` AS `email_mahasiswa`, `pm`.`judul` AS `judul`, `ssk`.`tanggal_seminar` AS `tanggal_seminar`, `ssk`.`jam_seminar` AS `jam_seminar`, `ssk`.`tempat_seminar` AS `tempat_seminar`, `d`.`nama` AS `nama_pembimbing`, `d1`.`nama` AS `nama_penguji1`, `d2`.`nama` AS `nama_penguji2`, `dp`.`nama` AS `nama_penilai`, `pss`.`catatan_pendahuluan` AS `catatan_pendahuluan`, `pss`.`catatan_tinjauan_pustaka` AS `catatan_tinjauan_pustaka`, `pss`.`catatan_metodologi` AS `catatan_metodologi`, `pss`.`catatan_hasil_pembahasan` AS `catatan_hasil_pembahasan`, `pss`.`catatan_kesimpulan` AS `catatan_kesimpulan`, `pss`.`catatan_umum` AS `catatan_umum` FROM (((((((`penilaian_seminar_skripsi` `pss` join `seminar_skripsi_mahasiswa` `ssk` on(`pss`.`seminar_skripsi_id` = `ssk`.`id`)) join `proposal_mahasiswa` `pm` on(`pss`.`proposal_id` = `pm`.`id`)) join `mahasiswa` `m` on(`pss`.`mahasiswa_id` = `m`.`id`)) left join `dosen` `d` on(`pm`.`dosen_id` = `d`.`id`)) left join `dosen` `d1` on(`ssk`.`dosen_penguji1_id` = `d1`.`id`)) left join `dosen` `d2` on(`ssk`.`dosen_penguji2_id` = `d2`.`id`)) left join `dosen` `dp` on(`pss`.`dinilai_oleh` = `dp`.`id`)) ;

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
-- Structure for view `publikasi_mahasiswa_v`
--
DROP TABLE IF EXISTS `publikasi_mahasiswa_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `publikasi_mahasiswa_v`  AS SELECT `p`.`id` AS `id`, `p`.`proposal_mahasiswa_id` AS `proposal_mahasiswa_id`, `p`.`mahasiswa_id` AS `mahasiswa_id`, `p`.`nama_mahasiswa` AS `nama_mahasiswa`, `p`.`nim` AS `nim`, `p`.`program_studi` AS `program_studi`, `p`.`judul_skripsi_final` AS `judul_skripsi_final`, `p`.`nama_dosen_pembimbing` AS `nama_dosen_pembimbing`, `p`.`tanggal_ujian_skripsi` AS `tanggal_ujian_skripsi`, `p`.`status` AS `status`, `p`.`status_pembimbing` AS `status_pembimbing`, `p`.`status_staf` AS `status_staf`, `p`.`tanggal_pengajuan` AS `tanggal_pengajuan`, `p`.`tanggal_review_pembimbing` AS `tanggal_review_pembimbing`, `p`.`tanggal_validasi_staf` AS `tanggal_validasi_staf`, `p`.`tanggal_selesai` AS `tanggal_selesai`, `p`.`link_repository` AS `link_repository`, `p`.`file_surat_revisi` AS `file_surat_revisi`, `p`.`file_skripsi_final` AS `file_skripsi_final`, `p`.`file_surat_perpustakaan` AS `file_surat_perpustakaan`, `p`.`keterangan_mahasiswa` AS `keterangan_mahasiswa`, `p`.`komentar_pembimbing` AS `komentar_pembimbing`, `p`.`komentar_staf` AS `komentar_staf`, `m`.`email` AS `email_mahasiswa`, `m`.`nomor_telepon` AS `nomor_telepon`, `pm`.`workflow_status` AS `workflow_status`, `pm`.`judul` AS `judul_proposal_awal`, `d`.`nama` AS `nama_pembimbing_lengkap`, `d`.`email` AS `email_pembimbing`, CASE `p`.`status` WHEN 'draft' THEN 'Draft - Belum disubmit' WHEN 'submitted' THEN 'Menunggu review pembimbing' WHEN 'review_pembimbing' THEN 'Sedang direview pembimbing' WHEN 'review_staf' THEN 'Menunggu validasi staf' WHEN 'completed' THEN 'Publikasi selesai' WHEN 'rejected' THEN 'Ditolak' ELSE 'Status tidak dikenali' END AS `status_description`, CASE WHEN `p`.`status` = 'completed' THEN 100 WHEN `p`.`status` = 'review_staf' THEN 80 WHEN `p`.`status` = 'review_pembimbing' THEN 60 WHEN `p`.`status` = 'submitted' THEN 40 WHEN `p`.`status` = 'draft' THEN 20 ELSE 0 END AS `progress_percentage`, `check_syarat_publikasi`(`p`.`proposal_mahasiswa_id`) AS `syarat_publikasi_status`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at` FROM (((`publikasi_tugas_akhir` `p` join `mahasiswa` `m` on(`p`.`mahasiswa_id` = `m`.`id`)) join `proposal_mahasiswa` `pm` on(`p`.`proposal_mahasiswa_id` = `pm`.`id`)) left join `dosen` `d` on(`p`.`dosen_pembimbing_id` = `d`.`id`)) ORDER BY `p`.`updated_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `seminar_proposal_mahasiswa_v`
--
DROP TABLE IF EXISTS `seminar_proposal_mahasiswa_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `seminar_proposal_mahasiswa_v`  AS SELECT `spm`.`id` AS `id`, `spm`.`proposal_id` AS `proposal_id`, `spm`.`mahasiswa_id` AS `mahasiswa_id`, `spm`.`status` AS `status`, `spm`.`current_step` AS `current_step`, `spm`.`file_proposal` AS `file_proposal`, `spm`.`keterangan_mahasiswa` AS `keterangan_mahasiswa`, `spm`.`status_pembimbing` AS `status_pembimbing`, `spm`.`status_kaprodi` AS `status_kaprodi`, `spm`.`tanggal_seminar` AS `tanggal_seminar`, `spm`.`jam_seminar` AS `jam_seminar`, `spm`.`tempat_seminar` AS `tempat_seminar`, `spm`.`created_at` AS `created_at`, `spm`.`updated_at` AS `updated_at`, `m`.`nim` AS `nim`, `m`.`nama` AS `nama_mahasiswa`, `m`.`email` AS `email_mahasiswa`, `pm`.`judul` AS `judul`, `pm`.`workflow_status` AS `workflow_status`, `pm`.`dosen_id` AS `pembimbing_id`, `d`.`nama` AS `nama_pembimbing`, `d`.`email` AS `email_pembimbing`, `d1`.`nama` AS `nama_penguji1`, `d2`.`nama` AS `nama_penguji2`, CASE WHEN `spm`.`status` = 'draft' THEN 'Menyiapkan pengajuan' WHEN `spm`.`status` = 'submitted' THEN 'Menunggu review dosen pembimbing' WHEN `spm`.`status` = 'review_pembimbing' THEN 'Sedang direview dosen pembimbing' WHEN `spm`.`status` = 'review_kaprodi' THEN 'Sedang direview Kaprodi' WHEN `spm`.`status` = 'approved' THEN 'Disetujui, menunggu penjadwalan' WHEN `spm`.`status` = 'rejected' THEN 'Ditolak, perlu revisi' WHEN `spm`.`status` = 'scheduled' THEN 'Terjadwal, menunggu pelaksanaan' WHEN `spm`.`status` = 'completed' THEN 'Selesai, menunggu hasil' ELSE 'Status tidak dikenal' END AS `status_description` FROM (((((`seminar_proposal_mahasiswa` `spm` join `proposal_mahasiswa` `pm` on(`spm`.`proposal_id` = `pm`.`id`)) join `mahasiswa` `m` on(`spm`.`mahasiswa_id` = `m`.`id`)) left join `dosen` `d` on(`pm`.`dosen_id` = `d`.`id`)) left join `dosen` `d1` on(`spm`.`dosen_penguji1_id` = `d1`.`id`)) left join `dosen` `d2` on(`spm`.`dosen_penguji2_id` = `d2`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `seminar_skripsi_progress_v`
--
DROP TABLE IF EXISTS `seminar_skripsi_progress_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `seminar_skripsi_progress_v`  AS SELECT 'seminar_skripsi' AS `phase`, count(0) AS `total_mahasiswa`, count(case when `ssk`.`status` = 'draft' then 1 end) AS `draft_count`, count(case when `ssk`.`status` = 'submitted' then 1 end) AS `submitted_count`, count(case when `ssk`.`status` = 'review_pembimbing' then 1 end) AS `review_pembimbing_count`, count(case when `ssk`.`status` = 'review_kaprodi' then 1 end) AS `review_kaprodi_count`, count(case when `ssk`.`status` = 'approved' then 1 end) AS `approved_count`, count(case when `ssk`.`status` = 'scheduled' then 1 end) AS `scheduled_count`, count(case when `ssk`.`status` = 'completed' then 1 end) AS `completed_count`, count(case when `ssk`.`status` = 'rejected' then 1 end) AS `rejected_count`, avg(case when `ssk`.`status` = 'draft' then 20 when `ssk`.`status` = 'submitted' or `ssk`.`status` = 'review_pembimbing' then 40 when `ssk`.`status` = 'review_kaprodi' then 60 when `ssk`.`status` = 'approved' then 80 when `ssk`.`status` = 'scheduled' then 95 when `ssk`.`status` = 'completed' then 100 when `ssk`.`status` = 'rejected' then 25 else 0 end) AS `avg_progress_percentage` FROM (`seminar_skripsi_mahasiswa` `ssk` join `proposal_mahasiswa` `pm` on(`ssk`.`proposal_id` = `pm`.`id`)) WHERE `pm`.`workflow_status` = 'seminar_skripsi' ;

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

-- --------------------------------------------------------

--
-- Structure for view `staf_v`
--
DROP TABLE IF EXISTS `staf_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `staf_v`  AS SELECT `d`.`id` AS `id`, `d`.`nip` AS `nip`, `d`.`nama` AS `nama`, `d`.`email` AS `email`, `d`.`nomor_telepon` AS `nomor_telepon`, `p`.`id` AS `prodi_id`, `p`.`nama` AS `nama_prodi`, `f`.`nama` AS `nama_fakultas` FROM ((`dosen` `d` left join `prodi` `p` on(`d`.`prodi_id` = `p`.`id`)) left join `fakultas` `f` on(`p`.`fakultas_id` = `f`.`id`)) WHERE `d`.`level` = '5' ;

-- --------------------------------------------------------

--
-- Structure for view `validasi_stats_v`
--
DROP TABLE IF EXISTS `validasi_stats_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133`@`localhost` SQL SECURITY DEFINER VIEW `validasi_stats_v`  AS SELECT `validasi_dokumen`.`jenis_dokumen` AS `jenis_dokumen`, count(0) AS `total_dokumen`, sum(case when `validasi_dokumen`.`is_verified` = 1 then 1 else 0 end) AS `dokumen_terverifikasi`, sum(`validasi_dokumen`.`verify_count`) AS `total_verifikasi`, avg(`validasi_dokumen`.`verify_count`) AS `rata_rata_verifikasi`, count(case when `validasi_dokumen`.`expired_at` > current_timestamp() then 1 end) AS `masih_valid`, count(case when `validasi_dokumen`.`expired_at` <= current_timestamp() then 1 end) AS `sudah_expired` FROM `validasi_dokumen` GROUP BY `validasi_dokumen`.`jenis_dokumen` ;

-- --------------------------------------------------------

--
-- Structure for view `v_penelitian_dashboard`
--
DROP TABLE IF EXISTS `v_penelitian_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`stkp7133_skripsi`@`localhost` SQL SECURITY DEFINER VIEW `v_penelitian_dashboard`  AS SELECT `pip`.`id` AS `permohonan_id`, `pip`.`proposal_mahasiswa_id` AS `proposal_mahasiswa_id`, `pip`.`nim` AS `nim`, `pip`.`nama_mahasiswa` AS `nama_mahasiswa`, `pip`.`semester` AS `semester`, `pip`.`program_studi` AS `program_studi`, `pip`.`judul_skripsi_terbaru` AS `judul_skripsi_terbaru`, `pip`.`tempat_penelitian` AS `tempat_penelitian`, `pip`.`tanggal_mulai_penelitian` AS `tanggal_mulai_penelitian`, `pip`.`tanggal_selesai_penelitian` AS `tanggal_selesai_penelitian`, `pip`.`status` AS `status`, `pip`.`status_pembimbing` AS `status_pembimbing`, `pip`.`created_at` AS `tanggal_pengajuan`, `pip`.`tanggal_review_pembimbing` AS `tanggal_review_pembimbing`, `pip`.`tanggal_upload_surat_staf` AS `tanggal_upload_surat_staf`, `d`.`nama` AS `nama_pembimbing`, `d`.`nip` AS `nip_pembimbing`, `d`.`email` AS `email_pembimbing`, `pm`.`workflow_status` AS `workflow_status`, `pm`.`status_izin_penelitian` AS `status_izin_penelitian`, `pm`.`surat_izin_penelitian` AS `surat_izin_penelitian`, CASE `pip`.`status` WHEN 'draft' THEN 'Draft Permohonan' WHEN 'submitted' THEN 'Menunggu Review Pembimbing' WHEN 'review_pembimbing' THEN 'Sedang Direview Pembimbing' WHEN 'approved' THEN 'Disetujui Pembimbing - Menunggu Staf' WHEN 'rejected' THEN 'Ditolak Pembimbing' WHEN 'surat_ready' THEN 'Surat Siap - Menunggu Download' WHEN 'completed' THEN 'Selesai' ELSE 'Status Tidak Dikenal' END AS `status_description`, CASE `pip`.`status` WHEN 'draft' THEN 10 WHEN 'submitted' THEN 25 WHEN 'review_pembimbing' THEN 40 WHEN 'approved' THEN 60 WHEN 'rejected' THEN 0 WHEN 'surat_ready' THEN 80 WHEN 'completed' THEN 100 ELSE 0 END AS `progress_percentage` FROM ((`permohonan_izin_penelitian` `pip` left join `dosen` `d` on(`pip`.`dosen_pembimbing_id` = `d`.`id`)) left join `proposal_mahasiswa` `pm` on(`pip`.`proposal_mahasiswa_id` = `pm`.`id`)) ;

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
  ADD KEY `fk_jurnal_dosen` (`validasi_oleh`),
  ADD KEY `idx_jurnal_proposal_validasi` (`proposal_id`,`status_validasi`);

--
-- Indexes for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_penelitian`
--
ALTER TABLE `log_penelitian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_permohonan` (`permohonan_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_aktivitas` (`aktivitas`);

--
-- Indexes for table `log_publikasi`
--
ALTER TABLE `log_publikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_publikasi` (`publikasi_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_aktivitas` (`aktivitas`),
  ADD KEY `idx_tanggal` (`created_at`);

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
-- Indexes for table `penilaian_seminar_proposal`
--
ALTER TABLE `penilaian_seminar_proposal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_seminar_penilaian` (`seminar_proposal_id`),
  ADD KEY `idx_mahasiswa` (`mahasiswa_id`),
  ADD KEY `idx_proposal` (`proposal_id`),
  ADD KEY `idx_penilai` (`dinilai_oleh`),
  ADD KEY `idx_status` (`status_penilaian`),
  ADD KEY `idx_rekomendasi` (`rekomendasi`),
  ADD KEY `idx_nilai_huruf` (`nilai_huruf`),
  ADD KEY `idx_published_at` (`published_at`),
  ADD KEY `idx_nilai_penguji1` (`nilai_penguji1`),
  ADD KEY `idx_nilai_penguji2` (`nilai_penguji2`),
  ADD KEY `idx_nilai_pembimbing` (`nilai_pembimbing`);

--
-- Indexes for table `penilaian_seminar_proposal_backup_20250729`
--
ALTER TABLE `penilaian_seminar_proposal_backup_20250729`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_seminar_penilaian` (`seminar_proposal_id`),
  ADD KEY `idx_mahasiswa` (`mahasiswa_id`),
  ADD KEY `idx_proposal` (`proposal_id`),
  ADD KEY `idx_penilai` (`dinilai_oleh`),
  ADD KEY `idx_status` (`status_penilaian`),
  ADD KEY `idx_rekomendasi` (`rekomendasi`),
  ADD KEY `idx_nilai_huruf` (`nilai_huruf`),
  ADD KEY `idx_published_at` (`published_at`);

--
-- Indexes for table `penilaian_seminar_skripsi`
--
ALTER TABLE `penilaian_seminar_skripsi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_seminar_penilai` (`seminar_skripsi_id`,`dinilai_oleh`,`role_penilai`),
  ADD KEY `idx_mahasiswa` (`mahasiswa_id`),
  ADD KEY `idx_proposal` (`proposal_id`),
  ADD KEY `idx_status` (`status_penilaian`),
  ADD KEY `idx_rekomendasi` (`rekomendasi`),
  ADD KEY `fk_pss_penilai` (`dinilai_oleh`);

--
-- Indexes for table `permohonan_izin_penelitian`
--
ALTER TABLE `permohonan_izin_penelitian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proposal_permohonan` (`proposal_mahasiswa_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_pembimbing` (`dosen_pembimbing_id`),
  ADD KEY `idx_status_pembimbing` (`status_pembimbing`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_penelitian_workflow_safe` (`status`,`created_at`),
  ADD KEY `idx_penelitian_pembimbing_safe` (`dosen_pembimbing_id`,`status_pembimbing`);

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
  ADD KEY `idx_mahasiswa_workflow` (`mahasiswa_id`,`workflow_status`),
  ADD KEY `fk_penetapan` (`penetapan_oleh`),
  ADD KEY `fk_penguji2` (`dosen_penguji2_id`),
  ADD KEY `idx_proposal_workflow_status` (`workflow_status`,`status_pembimbing`);

--
-- Indexes for table `proposal_workflow`
--
ALTER TABLE `proposal_workflow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proposal_id` (`proposal_id`),
  ADD KEY `idx_tahap` (`tahap`);

--
-- Indexes for table `publikasi_tugas_akhir`
--
ALTER TABLE `publikasi_tugas_akhir`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proposal_publikasi` (`proposal_mahasiswa_id`),
  ADD KEY `idx_mahasiswa` (`mahasiswa_id`),
  ADD KEY `idx_pembimbing` (`dosen_pembimbing_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_workflow` (`status_pembimbing`,`status_staf`),
  ADD KEY `idx_tanggal` (`tanggal_pengajuan`,`tanggal_selesai`);

--
-- Indexes for table `seminar`
--
ALTER TABLE `seminar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seminar_proposal_mahasiswa`
--
ALTER TABLE `seminar_proposal_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proposal_seminar_mhs` (`proposal_id`),
  ADD KEY `idx_mahasiswa_spm` (`mahasiswa_id`),
  ADD KEY `idx_status_spm` (`status`),
  ADD KEY `idx_current_step_spm` (`current_step`),
  ADD KEY `idx_tanggal_seminar_spm` (`tanggal_seminar`),
  ADD KEY `fk_spm_pembimbing_reviewer` (`reviewed_by_pembimbing`),
  ADD KEY `fk_spm_kaprodi_reviewer` (`reviewed_by_kaprodi`),
  ADD KEY `fk_spm_penguji1` (`dosen_penguji1_id`),
  ADD KEY `fk_spm_penguji2` (`dosen_penguji2_id`);

--
-- Indexes for table `seminar_skripsi_mahasiswa`
--
ALTER TABLE `seminar_skripsi_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_proposal_seminar_skripsi` (`proposal_id`),
  ADD KEY `idx_mahasiswa` (`mahasiswa_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_current_step` (`current_step`),
  ADD KEY `idx_tanggal_seminar` (`tanggal_seminar`),
  ADD KEY `fk_ssk_pembimbing_reviewer` (`reviewed_by_pembimbing`),
  ADD KEY `fk_ssk_kaprodi_reviewer` (`reviewed_by_kaprodi`),
  ADD KEY `fk_ssk_penguji1` (`dosen_penguji1_id`),
  ADD KEY `fk_ssk_penguji2` (`dosen_penguji2_id`);

--
-- Indexes for table `skripsi`
--
ALTER TABLE `skripsi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staf_aktivitas`
--
ALTER TABLE `staf_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_staf_id` (`staf_id`),
  ADD KEY `idx_mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `idx_proposal_id` (`proposal_id`);

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `log_penelitian`
--
ALTER TABLE `log_penelitian`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `log_publikasi`
--
ALTER TABLE `log_publikasi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
-- AUTO_INCREMENT for table `penilaian_seminar_proposal`
--
ALTER TABLE `penilaian_seminar_proposal`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `penilaian_seminar_proposal_backup_20250729`
--
ALTER TABLE `penilaian_seminar_proposal_backup_20250729`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penilaian_seminar_skripsi`
--
ALTER TABLE `penilaian_seminar_skripsi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permohonan_izin_penelitian`
--
ALTER TABLE `permohonan_izin_penelitian`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prodi`
--
ALTER TABLE `prodi`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `proposal_mahasiswa`
--
ALTER TABLE `proposal_mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `proposal_workflow`
--
ALTER TABLE `proposal_workflow`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `publikasi_tugas_akhir`
--
ALTER TABLE `publikasi_tugas_akhir`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `seminar`
--
ALTER TABLE `seminar`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `seminar_proposal_mahasiswa`
--
ALTER TABLE `seminar_proposal_mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `seminar_skripsi_mahasiswa`
--
ALTER TABLE `seminar_skripsi_mahasiswa`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skripsi`
--
ALTER TABLE `skripsi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `staf_aktivitas`
--
ALTER TABLE `staf_aktivitas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
-- Constraints for table `log_penelitian`
--
ALTER TABLE `log_penelitian`
  ADD CONSTRAINT `fk_log_permohonan` FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan_izin_penelitian` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `penilaian_seminar_proposal`
--
ALTER TABLE `penilaian_seminar_proposal`
  ADD CONSTRAINT `fk_penilaian_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penilaian_penilai` FOREIGN KEY (`dinilai_oleh`) REFERENCES `dosen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penilaian_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposal_mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penilaian_seminar_proposal` FOREIGN KEY (`seminar_proposal_id`) REFERENCES `seminar_proposal_mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `penilaian_seminar_skripsi`
--
ALTER TABLE `penilaian_seminar_skripsi`
  ADD CONSTRAINT `fk_pss_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pss_penilai` FOREIGN KEY (`dinilai_oleh`) REFERENCES `dosen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pss_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposal_mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pss_seminar` FOREIGN KEY (`seminar_skripsi_id`) REFERENCES `seminar_skripsi_mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permohonan_izin_penelitian`
--
ALTER TABLE `permohonan_izin_penelitian`
  ADD CONSTRAINT `fk_permohonan_pembimbing_readonly` FOREIGN KEY (`dosen_pembimbing_id`) REFERENCES `dosen` (`id`),
  ADD CONSTRAINT `fk_permohonan_proposal_readonly` FOREIGN KEY (`proposal_mahasiswa_id`) REFERENCES `proposal_mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proposal_mahasiswa`
--
ALTER TABLE `proposal_mahasiswa`
  ADD CONSTRAINT `fk_penetapan` FOREIGN KEY (`penetapan_oleh`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_penguji2` FOREIGN KEY (`dosen_penguji2_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `seminar_proposal_mahasiswa`
--
ALTER TABLE `seminar_proposal_mahasiswa`
  ADD CONSTRAINT `fk_spm_kaprodi_reviewer` FOREIGN KEY (`reviewed_by_kaprodi`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_spm_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_spm_pembimbing_reviewer` FOREIGN KEY (`reviewed_by_pembimbing`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_spm_penguji1` FOREIGN KEY (`dosen_penguji1_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_spm_penguji2` FOREIGN KEY (`dosen_penguji2_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_spm_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposal_mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seminar_skripsi_mahasiswa`
--
ALTER TABLE `seminar_skripsi_mahasiswa`
  ADD CONSTRAINT `fk_ssk_kaprodi_reviewer` FOREIGN KEY (`reviewed_by_kaprodi`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ssk_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ssk_pembimbing_reviewer` FOREIGN KEY (`reviewed_by_pembimbing`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ssk_penguji1` FOREIGN KEY (`dosen_penguji1_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ssk_penguji2` FOREIGN KEY (`dosen_penguji2_id`) REFERENCES `dosen` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ssk_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposal_mahasiswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
