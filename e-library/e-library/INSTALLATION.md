# Panduan Instalasi Cepat E-Library STK Yakobus

## Langkah 1: Persiapan

1. Extract file `e-library.zip` ke komputer Anda
2. Login ke cPanel di https://stkyakobus.ac.id/cpanel

## Langkah 2: Upload ke Server

1. Buka **File Manager** di cPanel
2. Navigate ke folder `/public_html/`
3. Buat folder baru bernama `e-library`
4. Masuk ke folder `e-library`
5. Upload semua file dari folder yang sudah di-extract
6. Klik kanan pada file zip (jika upload dalam bentuk zip) → Extract

## Langkah 3: Buat Database

1. Kembali ke cPanel, buka **MySQL Databases**
2. Buat database baru:
   - Database Name: `e_library` (atau sesuai keinginan)
   - Klik "Create Database"
3. Buat user database:
   - Username: (terserah Anda)
   - Password: (buat password yang kuat)
   - Klik "Create User"
4. Tambahkan user ke database:
   - Pilih user yang baru dibuat
   - Pilih database `e_library`
   - Centang "All Privileges"
   - Klik "Add"
5. **CATAT informasi ini:**
   - Database Host: `localhost`
   - Database Name: `e_library` (atau nama yang Anda buat)
   - Database Username: (username yang Anda buat)
   - Database Password: (password yang Anda buat)

## Langkah 4: Konfigurasi Database

1. Kembali ke File Manager
2. Edit file `config/database.php`
3. Ubah baris berikut dengan informasi database Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username_anda'); // Ganti dengan username database
define('DB_PASS', 'password_anda'); // Ganti dengan password database
define('DB_NAME', 'e_library');      // Ganti jika nama database berbeda
```

4. Simpan file

## Langkah 5: Jalankan Instalasi

1. Buka browser
2. Akses: `https://stkyakobus.ac.id/e-library/install.php`
3. Tunggu proses instalasi selesai
4. Jika muncul pesan "Installation Complete", instalasi berhasil
5. **PENTING**: Catat kredensial login yang ditampilkan

## Langkah 6: Hapus File Install

1. Kembali ke File Manager
2. **Hapus file `install.php`** untuk keamanan
3. Hapus juga file `database.sql` jika tidak diperlukan

## Langkah 7: Upload Aset

1. Buka folder `assets/images/` di File Manager
2. Upload file berikut:
   - `stk.png` - Logo STK Yakobus
   - `ttd_yuli.png` - Tanda tangan digital Ibu Yuliana Mangera

## Langkah 8: Login Pertama Kali

1. Akses: `https://stkyakobus.ac.id/e-library/`
2. Login dengan kredensial default:
   - **Username**: `admin`
   - **Password**: `@Merauke99616`
3. **SEGERA ganti password** setelah login:
   - Klik nama admin di pojok kanan atas
   - Pilih "Ganti Password"
   - Masukkan password lama dan password baru

## Langkah 9: Set Permission Folder

Jika ada error saat upload file, set permission folder:

1. Klik kanan pada folder `uploads/` → Change Permissions
2. Set ke **755**
3. Centang "Recurse into subdirectories"
4. Apply

## Langkah 10: Instalasi Library (Opsional)

Jika menggunakan fitur import Excel dan generate PDF, install library via Composer:

```bash
cd /home/username/public_html/e-library
composer install
```

Atau download manual dan ekstrak ke folder `libraries/vendor/`

## Troubleshooting

### Error: Connection Failed
- Periksa kembali `config/database.php`
- Pastikan username, password, dan nama database benar

### Error: 500 Internal Server Error  
- Periksa permission folder: harus 755
- Periksa file .htaccess

### Error: Upload File Gagal
- Set permission folder `uploads/` ke 755
- Cek php.ini untuk upload_max_filesize

## Selesai!

Aplikasi E-Library sudah siap digunakan di:
**https://stkyakobus.ac.id/e-library/**

---

## Kontak Support

📧 Email: humas@stkyakobus.ac.id
🌐 Website: https://stkyakobus.ac.id

**Semoga sukses! 🚀**
