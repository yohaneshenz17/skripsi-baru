# Sistem Penilaian RPL - STKYAKOBUS

Aplikasi web untuk penilaian Rekognisi Pembelajaran Lampau (RPL) mahasiswa dengan fitur assignment dosen dan reporting yang komprehensif.

## 🎯 Fitur Utama

- **Manajemen User**: Admin dan Dosen Penilai dengan role-based access
- **Import Data Mahasiswa**: Support import dari file Excel dengan 2260+ data mahasiswa
- **Assignment System**: Auto-assign mahasiswa ke dosen secara merata
- **Form Penilaian**: Interface yang user-friendly dengan 5 bidang RPL
- **Konversi Otomatis**: Skor 0-100 otomatis dikonversi ke huruf mutu A/B/C/D/E
- **Laporan & Export**: Dashboard, laporan komprehensif, dan export CSV
- **Google Drive Integration**: Akses langsung ke dokumen mahasiswa
- **Audit Trail**: Log aktivitas semua user untuk tracking

## 📋 Requirements

- **Web Server**: Apache/Nginx dengan PHP 7.4+
- **Database**: MySQL 5.7+ atau MariaDB 10.3+
- **PHP Extensions**: PDO, PDO_MySQL, mbstring, openssl
- **Storage**: Minimal 100MB untuk aplikasi dan database

## 🚀 Panduan Instalasi

### 1. Persiapan Database

```sql
-- Buat database baru
CREATE DATABASE stkyakobus_rpl_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat user database (opsional)
CREATE USER 'stkyakobus_rpl'@'localhost' IDENTIFIED BY 'password_yang_kuat';
GRANT ALL PRIVILEGES ON stkyakobus_rpl_system.* TO 'stkyakobus_rpl'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Upload Files ke cPanel

1. Login ke cPanel stkyakobus.ac.id
2. Buka **File Manager**
3. Navigasi ke folder `public_html` atau subdirectory (misal: `public_html/rpl/`)
4. Upload semua file aplikasi:
   - `config.php`
   - `login.php`
   - `dashboard_admin.php`
   - `dashboard_dosen.php`
   - `penilaian.php`
   - `manage_dosen.php`
   - `manage_mahasiswa.php`
   - `laporan.php`
   - `import_mahasiswa.php`
   - `logout.php`
   - `index.php`
   - `.htaccess`

### 3. Konfigurasi Database

Edit file `config.php`:

```php
// Sesuaikan dengan setting database cPanel Anda
define('DB_HOST', 'localhost');
define('DB_USER', 'stkyakobus_rpl');     // Username database
define('DB_PASS', 'password_database');  // Password database  
define('DB_NAME', 'stkyakobus_rpl_system'); // Nama database

// Sesuaikan URL aplikasi
define('BASE_URL', 'https://stkyakobus.ac.id/rpl/');
```

### 4. Jalankan SQL Schema

Buka **phpMyAdmin** di cPanel dan jalankan script SQL dari file database schema untuk membuat tabel-tabel yang diperlukan.

### 5. Testing Aplikasi

1. Akses aplikasi: `https://stkyakobus.ac.id/rpl/`
2. Login dengan akun default:
   - **Admin**: `admin` / `password`
   - **Dosen**: `dosen001` / `password`

⚠️ **PENTING**: Ganti password default setelah login pertama!

## 📊 Import Data Mahasiswa

### Option 1: Sample Data (Testing)
- Login sebagai admin
- Masuk ke **Kelola Mahasiswa** → **Import Data**
- Klik **Import Sample Data** untuk testing

### Option 2: Import Manual via SQL
```sql
-- Contoh insert manual
INSERT INTO mahasiswa (nim, nama_lengkap, jenjang, tempat_tugas, email, ...) 
VALUES ('25869050001', 'NAMA MAHASISWA', 'SMP', 'TEMPAT TUGAS', 'email@domain.com', ...);
```

### Option 3: CSV Upload (Recommended)
1. Convert Excel "Data RPL Mahasiswa.xlsx" ke CSV
2. Buat script PHP untuk parsing CSV dan insert ke database
3. Gunakan library seperti PhpSpreadsheet untuk handling Excel langsung

## 👥 Manajemen User

### Menambah Dosen Penilai
1. Login sebagai admin
2. **Kelola Dosen** → **Tambah Dosen Baru**
3. Isi data lengkap dan password
4. Dosen bisa langsung login dan mulai menilai

### Assignment Mahasiswa
1. **Dashboard Admin** → **Auto Assign ke Dosen**
2. Atau manual via **Kelola Mahasiswa** → pilih mahasiswa → assign ke dosen tertentu
3. Sistem akan mendistribusi secara merata (~141 mahasiswa per dosen)

## 📝 Proses Penilaian

### Untuk Dosen:
1. Login → **Dashboard Dosen**
2. Lihat daftar mahasiswa yang ditugaskan
3. Klik **Mulai Penilaian** pada mahasiswa
4. Isi skor 0-100 untuk 5 bidang RPL:
   - RPL01: Pengembangan Kompetensi Pedagogik (6 SKS)
   - RPL02: Penyusunan Perangkat Pembelajaran (6 SKS)
   - RPL03: Pengembangan Kompetensi Profesional (6 SKS)
   - RPL04: Pengelolaan Administrasi Pembelajaran (6 SKS)
   - RPL05: Inovasi Pembelajaran (3 SKS)
5. Sistem otomatis konversi ke huruf mutu
6. **Simpan Draft** atau **Finalisasi Penilaian**

### Konversi Nilai:
- 85-100 = A (4.00)
- 80-84 = B (3.75)
- 75-79 = C (3.50)
- 70-74 = D (3.25)
- 65-69 = E (3.00)
- 60-64 = E (2.75)
- <60 = F

## 📊 Laporan & Monitoring

### Dashboard Admin:
- Statistik real-time progress penilaian
- Monitoring per dosen
- Auto-assign dan management tools

### Laporan Komprehensif:
- Filter berdasarkan jenjang, dosen, status
- Distribusi nilai per bidang RPL
- Export CSV untuk analisis lanjutan
- Print-friendly report

## 🔒 Keamanan

- Password hashing dengan bcrypt
- Session management yang aman
- Input sanitization dan validation
- SQL injection protection
- XSS protection via headers
- Role-based access control
- Audit trail logging

## 🛠️ Maintenance

### Backup Database Reguler:
```bash
# Via cPanel atau command line
mysqldump -u username -p stkyakobus_rpl_system > backup_$(date +%Y%m%d).sql
```

### Monitor Log Aktivitas:
- Cek tabel `log_aktivitas` untuk tracking user actions
- Monitor login attempts dan perubahan data penting

### Update Password Dosen:
- Admin bisa reset password dosen via **Kelola Dosen**
- Sarankan dosen ganti password secara berkala

## 📞 Support & Troubleshooting

### Error Umum:

1. **Database Connection Failed**
   - Cek kredensial di `config.php`
   - Pastikan database service running
   - Cek privileges user database

2. **Session Issues** 
   - Cek permission folder untuk session PHP
   - Pastikan cookies enabled di browser

3. **File Upload Issues**
   - Cek `upload_max_filesize` di php.ini
   - Pastikan folder writable

4. **Google Drive Links Tidak Bisa Diakses**
   - Pastikan link sharing public/viewable
   - Cek format URL Google Drive

### Optimasi Performance:
- Enable caching di server
- Compress static files (CSS/JS)
- Optimasi query database dengan indexing
- Gunakan CDN untuk assets jika perlu

## 🔧 Customization

### Mengubah Rubrik Penilaian:
Edit file `penilaian.php` bagian array `$rubrik` dan sesuaikan dengan kebutuhan institusi.

### Menambah Field Mahasiswa:
1. ALTER table `mahasiswa` untuk field baru
2. Update form import dan interface sesuai kebutuhan

### Branding:
- Edit konstanta `APP_NAME` di `config.php`
- Sesuaikan warna dan logo di CSS files

## 📄 License

Aplikasi ini dikembangkan khusus untuk STKYAKOBUS. Untuk penggunaan atau modifikasi, silakan hubungi developer.

---

**Developed for STKYAKOBUS** | Version 1.0 | 2025