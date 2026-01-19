# Panduan Pengembangan E-Library STK Yakobus

## Status Modul

### ✅ Sudah Selesai
- Login & Authentication
- Dashboard dengan Statistik
- Forgot Password
- Change Password
- CRUD Buku (Index, Add, Edit, Delete)
- Struktur Database Lengkap

### 🚧 Perlu Dilengkapi

#### 1. Import Excel (Buku, Mahasiswa, Dosen)
**File**: `modules/buku/import.php`, `modules/mahasiswa/import.php`, `modules/dosen/import.php`

**Library yang Diperlukan**:
```bash
composer require phpoffice/phpspreadsheet
```

**Struktur Kode** (Template):
```php
<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../../libraries/vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_FILES['excel_file']['name']) {
    $file = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();
    
    foreach ($rows as $key => $row) {
        if ($key == 0) continue; // Skip header
        
        // Insert ke database
        // $query = "INSERT INTO buku (...) VALUES (...)";
    }
}
?>
```

#### 2. CRUD Mahasiswa
**File yang Perlu Dibuat**:
- `modules/mahasiswa/index.php` - List mahasiswa
- `modules/mahasiswa/add.php` - Tambah mahasiswa
- `modules/mahasiswa/edit.php` - Edit mahasiswa
- `modules/mahasiswa/delete.php` - Hapus mahasiswa
- `modules/mahasiswa/import.php` - Import Excel

**Template** (mirip dengan modul buku, tambahkan upload foto)

#### 3. CRUD Dosen
**File yang Perlu Dibuat**:
- `modules/dosen/index.php` - List dosen
- `modules/dosen/add.php` - Tambah dosen
- `modules/dosen/edit.php` - Edit dosen
- `modules/dosen/delete.php` - Hapus dosen
- `modules/dosen/import.php` - Import Excel

#### 4. Modul Peminjaman
**File yang Perlu Dibuat**:
- `modules/peminjaman/index.php` ✅ (Sudah ada template)
- `modules/peminjaman/add.php` - Form peminjaman baru
- `modules/peminjaman/detail.php` - Detail peminjaman

**Logika Penting**:
```php
// Validasi maksimal 3 buku per peminjam
$query = "SELECT COUNT(*) as total FROM peminjaman 
          WHERE jenis_peminjam = ? AND peminjam_id = ? 
          AND status IN ('dipinjam', 'diperpanjang', 'terlambat')";
          
// Auto kurangi stok
$query = "UPDATE buku SET stok_tersedia = stok_tersedia - 1 WHERE id = ?";

// Set jatuh tempo 7 hari
$tanggal_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));
```

#### 5. Modul Perpanjangan
**File yang Perlu Dibuat**:
- `modules/perpanjangan/index.php` - List perpanjangan
- `modules/perpanjangan/add.php` - Form perpanjangan

**Logika Penting**:
```php
// Validasi hanya 1x perpanjangan
$query = "SELECT COUNT(*) as total FROM perpanjangan WHERE peminjaman_id = ?";

// Perpanjangan +7 hari dari jatuh tempo lama
$jatuh_tempo_baru = date('Y-m-d', strtotime($jatuh_tempo_lama . ' +7 days'));

// Update status peminjaman
$query = "UPDATE peminjaman SET status = 'diperpanjang', tanggal_jatuh_tempo = ? WHERE id = ?";
```

#### 6. Modul Pengembalian
**File yang Perlu Dibuat**:
- `modules/pengembalian/index.php` - List pengembalian
- `modules/pengembalian/process.php` - Proses pengembalian

**Logika Penting**:
```php
// Hitung keterlambatan dan denda
$hari_terlambat = hitungKeterlambatan($tanggal_jatuh_tempo);
$denda = $hari_terlambat * 1000;

// Kembalikan stok buku
$query = "UPDATE buku SET stok_tersedia = stok_tersedia + 1 WHERE id = ?";

// Update status peminjaman
$query = "UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = ? WHERE id = ?";

// Insert ke tabel pengembalian
$query = "INSERT INTO pengembalian (peminjaman_id, keterlambatan_hari, denda, ...) VALUES (...)";
```

#### 7. Modul Denda
**File yang Perlu Dibuat**:
- `modules/denda/index.php` - List denda
- `modules/denda/payment.php` - Proses pembayaran

#### 8. Modul Surat Keterangan
**File yang Perlu Dibuat**:
- `modules/surat_keterangan/index.php` - List surat
- `modules/surat_keterangan/add.php` - Generate surat baru
- `modules/surat_keterangan/pdf.php` - Generate PDF

**Library yang Diperlukan**:
```bash
composer require fpdf/fpdf
composer require endroid/qr-code
```

**Logika Penting**:
```php
// Validasi tidak ada peminjaman aktif
$query = "SELECT COUNT(*) as total FROM peminjaman p
          JOIN mahasiswa m ON p.peminjam_id = m.id
          WHERE m.nim = ? AND p.jenis_peminjam = 'mahasiswa'
          AND p.status IN ('dipinjam', 'diperpanjang', 'terlambat')";

// Validasi tidak ada denda
$query = "SELECT SUM(sisa_denda) as total FROM pengembalian ...";

// Generate nomor surat
$nomor = generateNomorSurat($conn); // xxx/SKP-PERP/STK/I/2026

// Generate QR Code dengan data mahasiswa
$qr_data = json_encode([
    'nim' => $nim,
    'nama' => $nama,
    'nomor_surat' => $nomor,
    'tanggal' => date('Y-m-d')
]);
```

#### 9. Modul Laporan
**File yang Perlu Dibuat**:
- `modules/laporan/index.php` - Pilih jenis laporan
- `modules/laporan/bulanan_pdf.php` - Laporan PDF
- `modules/laporan/bulanan_excel.php` - Laporan Excel

**Library yang Diperlukan**:
```bash
composer require phpoffice/phpspreadsheet
composer require fpdf/fpdf
```

**Isi Laporan Bulanan**:
- Total peminjaman
- Total pengembalian
- Total keterlambatan
- Total akumulasi denda
- Mahasiswa dengan peminjaman aktif
- Mahasiswa dengan keterlambatan
- Mahasiswa paling sering pinjam
- Buku terpopuler

#### 10. Modul Backup
**File yang Perlu Dibuat**:
- `modules/backup/index.php` - Backup database
- `modules/backup/restore.php` - Restore database

**Logika Backup**:
```php
$filename = 'backup_' . date('Y-m-d_His') . '.sql';
$command = "mysqldump -u $user -p$password $database > $filename";
exec($command);
```

## Instalasi Library via Composer

Jika server memiliki Composer:

```bash
cd /home/stkyakobus/public_html/e-library
composer init
composer require phpoffice/phpspreadsheet
composer require endroid/qr-code
```

Jika tidak ada Composer, download manual:
- PhpSpreadsheet: https://github.com/PHPOffice/PhpSpreadsheet
- QR Code: https://github.com/endroid/qr-code
- FPDF: https://github.com/Setasign/FPDF

## Struktur File PDF (Surat Keterangan)

```php
<?php
require('../../libraries/fpdf/fpdf.php');
require('../../libraries/qr-code/vendor/autoload.php');

use Endroid\QrCode\QrCode;

class SuratPDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('../../assets/images/stk.png', 10, 6, 30);
        
        // Title
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 7, 'SEKOLAH TINGGI KATEKETIK YAKOBUS', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Jl. Raya Mandala, Merauke, Papua Selatan', 0, 1, 'C');
        
        // Line
        $this->Line(10, 30, 200, 30);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Generate QR Code
$qrCode = new QrCode($qr_data);
$qrCode->writeFile('qr_temp.png');

// Create PDF
$pdf = new SuratPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Content
$pdf->Cell(0, 10, 'SURAT KETERANGAN BEBAS PERPUSTAKAAN', 0, 1, 'C');
$pdf->Ln(10);
// ... isi surat

// QR Code
$pdf->Image('qr_temp.png', 150, 250, 40);

// Tanda tangan
$pdf->Image('../../assets/images/ttd_yuli.png', 140, 270, 50);

$pdf->Output('F', $filename);
?>
```

## Testing

### Test Data untuk Development

**Buku**:
```sql
INSERT INTO buku VALUES 
(1, 'BK001', 'Alkitab', 'Berbagai Penulis', 10, 10, 'LAI', 2020),
(2, 'BK002', 'Katekismus', 'Gereja Katolik', 5, 5, 'Kanisius', 2019);
```

**Mahasiswa**:
```sql
INSERT INTO mahasiswa VALUES
(1, '2021001', 'John Doe', 'S1 Kateketik', '2021', '081234567890', NULL);
```

**Dosen**:
```sql
INSERT INTO dosen VALUES
(1, '123456789', 'Dr. Jane Smith', 'S1 Kateketik', '081234567891', NULL);
```

## Deployment Checklist

- [ ] Update config/database.php dengan kredensial database
- [ ] Upload file stk.png dan ttd_yuli.png ke assets/images/
- [ ] Jalankan install.php
- [ ] Hapus install.php setelah selesai
- [ ] Test login dengan kredensial default
- [ ] Ganti password admin
- [ ] Set permission folder uploads/ menjadi 755
- [ ] Install library via Composer atau manual
- [ ] Test semua modul
- [ ] Setup backup otomatis

## Notes

- Semua query menggunakan prepared statements untuk keamanan
- Semua input di-sanitize menggunakan fungsi sanitize()
- Password di-hash menggunakan password_hash()
- File upload divalidasi type dan size
- Session management sudah diterapkan
- Error handling sudah ada di sebagian besar modul

## Contact Developer

Untuk konsultasi pengembangan lebih lanjut, hubungi tim IT STK Yakobus.

---

**Last Updated**: 2026-01-19
