# E-Library STK Yakobus Merauke
Aplikasi Manajemen Perpustakaan untuk STK Yakobus Merauke

## Fitur Aplikasi

### 1. Manajemen Data
- ✅ CRUD Buku (Tambah, Edit, Hapus, Import Excel)
- ✅ CRUD Mahasiswa (Tambah, Edit, Hapus, Import Excel)
- ✅ CRUD Dosen (Tambah, Edit, Hapus, Import Excel)

### 2. Transaksi Perpustakaan
- ✅ Peminjaman Buku (Maksimal 3 buku, durasi 7 hari)
- ✅ Perpanjangan Buku (1x perpanjangan, 7 hari tambahan)
- ✅ Pengembalian Buku (Otomatis update stok)
- ✅ Manajemen Denda (Rp 1.000/hari keterlambatan)

### 3. Layanan
- ✅ Generate Surat Keterangan Bebas Perpustakaan (PDF + QR Code)
- ✅ Laporan Bulanan (PDF & Excel)

### 4. Dashboard & Monitoring
- ✅ Statistik Real-time
- ✅ Notifikasi Buku Habis
- ✅ Notifikasi Keterlambatan
- ✅ History Peminjaman

## Persyaratan Sistem

- PHP >= 7.4
- MySQL >= 5.7
- Web Server (Apache/Nginx)
- Ekstensi PHP: mysqli, gd, zip, mbstring
- Composer (untuk instalasi library)

## Instalasi

### 1. Upload File ke cPanel

1. Login ke cPanel stkyakobus.ac.id
2. Buka **File Manager**
3. Navigate ke folder: `/public_html/`
4. Buat folder baru: `e-library`
5. Upload semua file aplikasi ke folder `e-library`
6. Extract file jika dalam format ZIP

### 2. Konfigurasi Database

1. Buka **phpMyAdmin** di cPanel
2. Buat database baru dengan nama: `e_library`
3. Catat informasi database:
   - Host: `localhost`
   - Username: (username database Anda)
   - Password: (password database Anda)
   - Database Name: `e_library`

### 3. Update Konfigurasi

Edit file `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username_database_anda'); // Ganti dengan username database
define('DB_PASS', 'password_database_anda'); // Ganti dengan password database
define('DB_NAME', 'e_library');
```

### 4. Install Library (via Composer)

Jika server memiliki akses Composer:

```bash
cd /home/stkyakobus/public_html/e-library
composer require phpoffice/phpspreadsheet
composer require fpdf/fpdf
composer require endroid/qr-code
```

**Alternatif tanpa Composer:**
- Library sudah disertakan dalam folder `libraries/`
- Pastikan folder `libraries/` ter-upload dengan lengkap

### 5. Jalankan Instalasi

1. Buka browser dan akses: `https://stkyakobus.ac.id/e-library/install.php`
2. Proses instalasi akan membuat tabel database dan setup admin
3. Catat kredensial login yang ditampilkan
4. **PENTING:** Hapus file `install.php` setelah instalasi selesai

### 6. Login

Akses: `https://stkyakobus.ac.id/e-library/`

**Kredensial Default:**
- Username: `admin`
- Password: `@Merauke99616`

**Segera ganti password setelah login pertama!**

## Upload Aset

Upload file berikut ke folder `assets/images/`:

1. `stk.png` - Logo institusi
2. `ttd_yuli.png` - Tanda tangan digital Kepala Perpustakaan

## Konfigurasi Email (Opsional)

Untuk fitur lupa password, edit file `forgot_password.php`:

```php
// Konfigurasi SMTP
$mail_host = 'smtp.gmail.com';
$mail_port = 587;
$mail_username = 'email@stkyakobus.ac.id';
$mail_password = 'password_email';
$mail_from = 'noreply@stkyakobus.ac.id';
```

## Struktur Folder

```
e-library/
├── config/              # Konfigurasi database dan functions
├── modules/             # Modul-modul aplikasi
│   ├── buku/           # CRUD Buku
│   ├── mahasiswa/      # CRUD Mahasiswa
│   ├── dosen/          # CRUD Dosen
│   ├── peminjaman/     # Transaksi Peminjaman
│   ├── perpanjangan/   # Perpanjangan Buku
│   ├── pengembalian/   # Pengembalian Buku
│   ├── denda/          # Manajemen Denda
│   ├── surat_keterangan/ # Generate Surat
│   ├── laporan/        # Laporan Bulanan
│   └── backup/         # Backup Database
├── includes/           # Navbar & Sidebar
├── assets/             # CSS, JS, Images
├── uploads/            # Upload foto & file
├── libraries/          # External libraries
├── index.php           # Halaman Login
├── dashboard.php       # Dashboard
└── database.sql        # SQL Schema
```

## Panduan Penggunaan

### Import Data Excel

**Format Template Buku:**
| Nomor Buku | Judul | Pengarang | Stok | Penerbit | Tahun Terbit |
|------------|-------|-----------|------|----------|--------------|
| BK001      | ...   | ...       | 5    | ...      | 2020         |

**Format Template Mahasiswa:**
| NIM | Nama | Program Studi | Angkatan | No. HP |
|-----|------|---------------|----------|--------|
| ... | ...  | ...           | 2020     | ...    |

**Format Template Dosen:**
| NUPTK | Nama | Program Studi | No. HP |
|-------|------|---------------|--------|
| ...   | ...  | ...           | ...    |

### Generate Surat Keterangan

1. Pilih menu **Surat Keterangan > Tambah**
2. Pilih Mahasiswa
3. Pilih Keperluan (Ujian Akhir / Pengambilan Ijazah)
4. Sistem akan validasi:
   - Tidak ada peminjaman aktif
   - Tidak ada denda tertunggak (atau bisa di-waive admin)
5. Generate PDF dengan QR Code

## Keamanan

### File .htaccess

Tambahkan file `.htaccess` di root folder:

```apache
# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^(config|database\.sql|install\.php)">
    Order allow,deny
    Deny from all
</FilesMatch>

# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

### Best Practices

1. **Hapus install.php** setelah instalasi
2. **Ganti password default** segera
3. **Backup database** secara berkala
4. **Update PHP** ke versi terbaru
5. **Monitor log** aktivitas

## Troubleshooting

### Error: Connection Failed

**Solusi:**
- Periksa kredensial database di `config/database.php`
- Pastikan MySQL service aktif
- Cek hak akses user database

### Error: Upload File Gagal

**Solusi:**
- Periksa permission folder `uploads/` (755)
- Cek `php.ini` untuk `upload_max_filesize` dan `post_max_size`
- Pastikan folder dapat di-write oleh web server

### Error: Library Not Found

**Solusi:**
- Install via Composer: `composer install`
- Atau download manual library ke folder `libraries/`

## Support & Kontak

Jika ada pertanyaan atau masalah:

📧 Email: humas@stkyakobus.ac.id
🌐 Website: https://stkyakobus.ac.id

## Lisensi

© 2026 STK Yakobus Merauke. All Rights Reserved.

---

## Changelog

### Version 1.0.0 (2026-01-19)
- ✅ Initial release
- ✅ CRUD Buku, Mahasiswa, Dosen
- ✅ Peminjaman & Pengembalian
- ✅ Generate Surat Keterangan
- ✅ Laporan Bulanan
- ✅ Dashboard & Statistik

---

**Developed for STK Yakobus Merauke**
