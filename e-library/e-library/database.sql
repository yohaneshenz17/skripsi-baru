-- Database: e_library
-- STK Yakobus E-Library System

CREATE DATABASE IF NOT EXISTS e_library;
USE e_library;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin
INSERT INTO admin (username, password, email) VALUES 
('admin', '$2y$10$rGZxH5QJ5qX5fKJ5J5fKJeZxH5QJ5qX5fKJ5J5fKJeZxH5QJ5qX5f', 'humas@stkyakobus.ac.id');
-- Password: @Merauke99616 (akan di-hash saat install)

-- Tabel Buku
CREATE TABLE IF NOT EXISTS buku (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_buku VARCHAR(50) UNIQUE NOT NULL,
    judul VARCHAR(255) NOT NULL,
    pengarang VARCHAR(255) NOT NULL,
    penerbit VARCHAR(255),
    tahun_terbit YEAR,
    stok INT DEFAULT 0,
    stok_tersedia INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    program_studi VARCHAR(100) NOT NULL,
    angkatan VARCHAR(10) NOT NULL,
    no_hp VARCHAR(20),
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Dosen
CREATE TABLE IF NOT EXISTS dosen (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nuptk VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    program_studi VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_peminjaman VARCHAR(50) UNIQUE NOT NULL,
    jenis_peminjam ENUM('mahasiswa', 'dosen') NOT NULL,
    peminjam_id INT NOT NULL,
    buku_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    tanggal_kembali DATE,
    status ENUM('dipinjam', 'diperpanjang', 'dikembalikan', 'terlambat') DEFAULT 'dipinjam',
    denda INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- Tabel Perpanjangan
CREATE TABLE IF NOT EXISTS perpanjangan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    peminjaman_id INT NOT NULL,
    tanggal_perpanjangan DATE NOT NULL,
    jatuh_tempo_lama DATE NOT NULL,
    jatuh_tempo_baru DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE
);

-- Tabel Pengembalian
CREATE TABLE IF NOT EXISTS pengembalian (
    id INT PRIMARY KEY AUTO_INCREMENT,
    peminjaman_id INT NOT NULL,
    tanggal_kembali DATE NOT NULL,
    keterlambatan_hari INT DEFAULT 0,
    denda INT DEFAULT 0,
    denda_dibayar INT DEFAULT 0,
    sisa_denda INT DEFAULT 0,
    metode_pembayaran ENUM('cash', 'transfer', 'waive') DEFAULT 'cash',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE
);

-- Tabel Surat Keterangan
CREATE TABLE IF NOT EXISTS surat_keterangan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_surat VARCHAR(50) UNIQUE NOT NULL,
    nim VARCHAR(20) NOT NULL,
    nama VARCHAR(255) NOT NULL,
    program_studi VARCHAR(100) NOT NULL,
    keperluan ENUM('ujian_akhir', 'pengambilan_ijazah') NOT NULL,
    tanggal_terbit DATE NOT NULL,
    qr_code TEXT,
    file_pdf VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nim) REFERENCES mahasiswa(nim) ON DELETE CASCADE
);

-- Tabel Nomor Surat (untuk tracking)
CREATE TABLE IF NOT EXISTS nomor_surat_counter (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tahun YEAR NOT NULL,
    counter INT DEFAULT 0,
    UNIQUE KEY (tahun)
);
