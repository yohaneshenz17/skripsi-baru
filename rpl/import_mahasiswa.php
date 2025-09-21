<?php
require_once 'config.php';
requireAdmin();

// Script untuk import data mahasiswa dari Excel
// Gunakan script ini untuk import data mahasiswa dari file "Data RPL Mahasiswa.xlsx"

$message = '';
$error = '';
$imported_count = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_import'])) {
    
    // Data sample berdasarkan struktur Excel yang telah dianalisis
    // Dalam implementasi nyata, Anda bisa menggunakan library seperti PhpSpreadsheet
    // untuk membaca file Excel secara langsung
    
    $sample_data = [
        [
            'nim' => '25869050001',
            'nama_lengkap' => 'ADELFINA FRANSISKA BRIA',
            'jenis_kelamin' => 'Perempuan',
            'tempat_tugas' => 'SMP NEGERI HELIBAUK',
            'email' => 'adelfinabria24@guru.SMP.belajar.id',
            'no_telepon' => '081237045344',
            'nik' => '5304084204950007',
            'status_pegawai' => 'PPPK',
            'tempat_lahir' => 'WEDARE',
            'tanggal_lahir' => '1995-04-01',
            'provinsi' => 'NUSA TENGGARA TIMUR',
            'kabupaten' => 'KABUPATEN MALAKA',
            'kecamatan' => 'MALAKA TENGAH',
            'jenjang' => 'SMP',
            'link_sk_mengajar' => 'https://drive.google.com/open?id=1-J--zMVJA-BkPY8ScbPEvHm6ULAXCiNl',
            'link_administrasi' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'link_inovasi' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm'
        ],
        [
            'nim' => '25869050002',
            'nama_lengkap' => 'ADELHEID SAINA',
            'jenis_kelamin' => 'Perempuan',
            'tempat_tugas' => 'SDI LELIT',
            'email' => 'adelheidsaina34@guru.sd.belajar.id',
            'no_telepon' => '081338731398',
            'nik' => '5310156304910001',
            'status_pegawai' => 'PPPK',
            'tempat_lahir' => 'GENCOR',
            'tanggal_lahir' => '1991-04-22',
            'provinsi' => 'NUSA TENGGARA TIMUR',
            'kabupaten' => 'KABUPATEN MANGGARAI',
            'kecamatan' => 'SATAR MESE BARAT',
            'jenjang' => 'SD',
            'link_sk_mengajar' => 'https://drive.google.com/open?id=11wJjdCwaKHTj2TLno_53iclac6b8hTKY',
            'link_administrasi' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'link_inovasi' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ'
        ]
        // Tambahkan data lainnya...
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($sample_data as $data) {
            // Cek apakah NIM sudah ada
            $stmt = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
            $stmt->execute([$data['nim']]);
            
            if (!$stmt->fetch()) {
                // Insert data mahasiswa baru
                $stmt = $pdo->prepare("
                    INSERT INTO mahasiswa (
                        nim, nama_lengkap, jenis_kelamin, tempat_tugas, email, no_telepon,
                        nik, status_pegawai, tempat_lahir, tanggal_lahir, provinsi, 
                        kabupaten, kecamatan, jenjang, link_sk_mengajar, link_administrasi, link_inovasi
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $data['nim'], $data['nama_lengkap'], $data['jenis_kelamin'],
                    $data['tempat_tugas'], $data['email'], $data['no_telepon'],
                    $data['nik'], $data['status_pegawai'], $data['tempat_lahir'],
                    $data['tanggal_lahir'], $data['provinsi'], $data['kabupaten'],
                    $data['kecamatan'], $data['jenjang'], $data['link_sk_mengajar'],
                    $data['link_administrasi'], $data['link_inovasi']
                ]);
                
                $imported_count++;
            }
        }
        
        $pdo->commit();
        
        logAktivitas($pdo, $_SESSION['user_id'], 'Import Mahasiswa', "Berhasil import $imported_count mahasiswa");
        $message = "Berhasil mengimport $imported_count data mahasiswa!";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Gagal import data: ' . $e->getMessage();
    }
}

// Statistik database
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $total_mahasiswa = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $total_mahasiswa = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data Mahasiswa - <?= APP_NAME ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
            transition: background 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .info-box {
            background: #e8f4fd;
            padding: 1.5rem;
            border-radius: 5px;
            border-left: 4px solid #3498db;
            margin-bottom: 2rem;
        }
        
        .step {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        
        .step h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 0.9rem;
            border: 1px solid #e0e0e0;
        }
        
        .stats {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .stats .number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Import Data Mahasiswa</h1>
        <a href="dashboard_admin.php" class="btn btn-primary">← Kembali</a>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= sanitizeInput($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitizeInput($error) ?></div>
        <?php endif; ?>
        
        <!-- Statistik -->
        <div class="stats">
            <div class="number"><?= number_format($total_mahasiswa) ?></div>
            <div>Total Mahasiswa di Database</div>
        </div>
        
        <!-- Info -->
        <div class="info-box">
            <h3>📋 Petunjuk Import Data Mahasiswa</h3>
            <p>Halaman ini digunakan untuk mengimport data mahasiswa dari file Excel "Data RPL Mahasiswa.xlsx" ke dalam database sistem penilaian RPL.</p>
        </div>
        
        <!-- Step 1: Manual Import -->
        <div class="step">
            <h3>Option 1: Import Sample Data (Demo)</h3>
            <p>Import beberapa data sample untuk testing aplikasi:</p>
            <form method="POST">
                <button type="submit" name="confirm_import" class="btn btn-success"
                        onclick="return confirm('Import sample data mahasiswa?')">
                    🚀 Import Sample Data
                </button>
            </form>
        </div>
        
        <!-- Step 2: Manual SQL -->
        <div class="step">
            <h3>Option 2: Import Manual via SQL</h3>
            <p>Untuk import data lengkap 2260 mahasiswa, gunakan script SQL berikut di phpMyAdmin atau database manager:</p>
            
            <div class="alert alert-warning">
                <strong>⚠️ Perhatian:</strong> Sesuaikan format tanggal dan data sesuai dengan file Excel Anda.
            </div>
            
            <pre>-- Contoh insert data mahasiswa
INSERT INTO mahasiswa (
    nim, nama_lengkap, jenis_kelamin, tempat_tugas, email, no_telepon,
    nik, status_pegawai, tempat_lahir, tanggal_lahir, provinsi, 
    kabupaten, kecamatan, jenjang, link_sk_mengajar, link_administrasi, link_inovasi
) VALUES 
('25869050001', 'ADELFINA FRANSISKA BRIA', 'Perempuan', 'SMP NEGERI HELIBAUK', 
 'adelfinabria24@guru.SMP.belajar.id', '081237045344', '5304084204950007', 'PPPK',
 'WEDARE', '1995-04-01', 'NUSA TENGGARA TIMUR', 'KABUPATEN MALAKA', 'MALAKA TENGAH', 'SMP',
 'https://drive.google.com/open?id=1-J--zMVJA-BkPY8ScbPEvHm6ULAXCiNl',
 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm');

-- Ulangi untuk semua 2260 data mahasiswa...</pre>
        </div>
        
        <!-- Step 3: PhpSpreadsheet -->
        <div class="step">
            <h3>Option 3: Otomatis dengan PhpSpreadsheet (Recommended)</h3>
            <p>Untuk implementasi production, disarankan menggunakan library PhpSpreadsheet:</p>
            
            <pre>// Install via Composer:
composer require phpoffice/phpspreadsheet

// Contoh script untuk baca Excel:
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('Data RPL Mahasiswa.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

foreach ($rows as $key => $row) {
    if ($key == 0) continue; // Skip header
    
    // Insert ke database
    $stmt = $pdo->prepare("INSERT INTO mahasiswa (...) VALUES (...)");
    $stmt->execute([...]);
}</pre>
        </div>
        
        <!-- Step 4: CSV Conversion -->
        <div class="step">
            <h3>Option 4: Konversi Excel ke CSV</h3>
            <ol>
                <li>Buka file "Data RPL Mahasiswa.xlsx" di Excel/Google Sheets</li>
                <li>Save As → CSV format</li>
                <li>Upload CSV ke server dan parsing dengan PHP:</li>
            </ol>
            
            <pre>$handle = fopen('data_mahasiswa.csv', 'r');
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    // Process each row
    $stmt = $pdo->prepare("INSERT INTO mahasiswa (...) VALUES (...)");
    $stmt->execute($data);
}</pre>
        </div>
        
        <div class="card">
            <h3>🔧 Next Steps</h3>
            <p>Setelah data mahasiswa berhasil diimport:</p>
            <ol>
                <li>Pastikan semua link Google Drive dapat diakses</li>
                <li>Assign mahasiswa ke dosen penilai menggunakan fitur "Auto Assign"</li>
                <li>Dosen dapat mulai melakukan penilaian RPL</li>
                <li>Monitor progress melalui dashboard admin</li>
            </ol>
        </div>
    </div>
</body>
</html>