<?php
require_once 'config.php';
requireAdmin();

// Script untuk import data mahasiswa dengan multiple documents support
$message = '';
$error = '';
$imported_count = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_import'])) {
    
    // Sample data berdasarkan struktur Excel dengan multiple documents
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
            // RPL.01 - Single document
            'link_sk_mengajar' => 'https://drive.google.com/open?id=1-J--zMVJA-BkPY8ScbPEvHm6ULAXCiNl',
            // RPL.02 - 12 documents (Perangkat Pembelajaran)
            'rpl02_perangkat_ganjil_2019' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_genap_2019' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_ganjil_2020' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_genap_2020' => '',  // Empty example
            'rpl02_perangkat_ganjil_2021' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_genap_2021' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_ganjil_2022' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_genap_2022' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_ganjil_2023' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_genap_2023' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_ganjil_2024' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            'rpl02_perangkat_genap_2024' => 'https://drive.google.com/open?id=1PbZGppM1pm2pXcPKwESwVSjPwhpB_zBr',
            // RPL.03 - 12 documents (Pengembangan Profesional)
            'rpl03_pengembangan_ganjil_2019' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_genap_2019' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_ganjil_2020' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_genap_2020' => '',  // Empty example
            'rpl03_pengembangan_ganjil_2021' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_genap_2021' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_ganjil_2022' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_genap_2022' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_ganjil_2023' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_genap_2023' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_ganjil_2024' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            'rpl03_pengembangan_genap_2024' => 'https://drive.google.com/open?id=1MWyMVCTzJ2-BprzFVo44lXBD8xuf3igm',
            // RPL.04 dan RPL.05 - Single documents
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
            // RPL.01
            'link_sk_mengajar' => 'https://drive.google.com/open?id=11wJjdCwaKHTj2TLno_53iclac6b8hTKY',
            // RPL.02 - Beberapa semester kosong untuk testing
            'rpl02_perangkat_ganjil_2019' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'rpl02_perangkat_genap_2019' => '',
            'rpl02_perangkat_ganjil_2020' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'rpl02_perangkat_genap_2020' => '',
            'rpl02_perangkat_ganjil_2021' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'rpl02_perangkat_genap_2021' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'rpl02_perangkat_ganjil_2022' => '',
            'rpl02_perangkat_genap_2022' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'rpl02_perangkat_ganjil_2023' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'rpl02_perangkat_genap_2023' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'rpl02_perangkat_ganjil_2024' => '',
            'rpl02_perangkat_genap_2024' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            // RPL.03 - Beberapa semester kosong untuk testing
            'rpl03_pengembangan_ganjil_2019' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ',
            'rpl03_pengembangan_genap_2019' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ',
            'rpl03_pengembangan_ganjil_2020' => '',
            'rpl03_pengembangan_genap_2020' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ',
            'rpl03_pengembangan_ganjil_2021' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ',
            'rpl03_pengembangan_genap_2021' => '',
            'rpl03_pengembangan_ganjil_2022' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ',
            'rpl03_pengembangan_genap_2022' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ',
            'rpl03_pengembangan_ganjil_2023' => '',
            'rpl03_pengembangan_genap_2023' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ',
            'rpl03_pengembangan_ganjil_2024' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ',
            'rpl03_pengembangan_genap_2024' => '',
            // RPL.04 dan RPL.05
            'link_administrasi' => 'https://drive.google.com/open?id=1jyCFAla9HzbMMkHyr2A7UmrAHGmZx4So',
            'link_inovasi' => 'https://drive.google.com/open?id=1JS3-5nvkAV48kFHLgNfGtgbIIGMblMOJ'
        ]
    ];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($sample_data as $data) {
            // Cek apakah NIM sudah ada
            $stmt = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
            $stmt->execute([$data['nim']]);
            
            if (!$stmt->fetch()) {
                // Insert data mahasiswa baru dengan semua kolom
                $stmt = $pdo->prepare("
                    INSERT INTO mahasiswa (
                        nim, nama_lengkap, jenis_kelamin, tempat_tugas, email, no_telepon,
                        nik, status_pegawai, tempat_lahir, tanggal_lahir, provinsi, 
                        kabupaten, kecamatan, jenjang, 
                        link_sk_mengajar, link_administrasi, link_inovasi,  -- ✅ BENAR
                        rpl02_perangkat_ganjil_2019, rpl02_perangkat_genap_2019,
                        rpl02_perangkat_ganjil_2020, rpl02_perangkat_genap_2020,
                        rpl02_perangkat_ganjil_2021, rpl02_perangkat_genap_2021,
                        rpl02_perangkat_ganjil_2022, rpl02_perangkat_genap_2022,
                        rpl02_perangkat_ganjil_2023, rpl02_perangkat_genap_2023,
                        rpl02_perangkat_ganjil_2024, rpl02_perangkat_genap_2024,
                        rpl03_pengembangan_ganjil_2019, rpl03_pengembangan_genap_2019,
                        rpl03_pengembangan_ganjil_2020, rpl03_pengembangan_genap_2020,
                        rpl03_pengembangan_ganjil_2021, rpl03_pengembangan_genap_2021,
                        rpl03_pengembangan_ganjil_2022, rpl03_pengembangan_genap_2022,
                        rpl03_pengembangan_ganjil_2023, rpl03_pengembangan_genap_2023,
                        rpl03_pengembangan_ganjil_2024, rpl03_pengembangan_genap_2024
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $data['nim'], $data['nama_lengkap'], $data['jenis_kelamin'],
                    $data['tempat_tugas'], $data['email'], $data['no_telepon'],
                    $data['nik'], $data['status_pegawai'], $data['tempat_lahir'],
                    $data['tanggal_lahir'], $data['provinsi'], $data['kabupaten'],
                    $data['kecamatan'], $data['jenjang'], 
                    $data['link_sk_mengajar'], $data['link_administrasi'], $data['link_inovasi'],
                    // RPL.02 documents
                    $data['rpl02_perangkat_ganjil_2019'], $data['rpl02_perangkat_genap_2019'],
                    $data['rpl02_perangkat_ganjil_2020'], $data['rpl02_perangkat_genap_2020'],
                    $data['rpl02_perangkat_ganjil_2021'], $data['rpl02_perangkat_genap_2021'],
                    $data['rpl02_perangkat_ganjil_2022'], $data['rpl02_perangkat_genap_2022'],
                    $data['rpl02_perangkat_ganjil_2023'], $data['rpl02_perangkat_genap_2023'],
                    $data['rpl02_perangkat_ganjil_2024'], $data['rpl02_perangkat_genap_2024'],
                    // RPL.03 documents
                    $data['rpl03_pengembangan_ganjil_2019'], $data['rpl03_pengembangan_genap_2019'],
                    $data['rpl03_pengembangan_ganjil_2020'], $data['rpl03_pengembangan_genap_2020'],
                    $data['rpl03_pengembangan_ganjil_2021'], $data['rpl03_pengembangan_genap_2021'],
                    $data['rpl03_pengembangan_ganjil_2022'], $data['rpl03_pengembangan_genap_2022'],
                    $data['rpl03_pengembangan_ganjil_2023'], $data['rpl03_pengembangan_genap_2023'],
                    $data['rpl03_pengembangan_ganjil_2024'], $data['rpl03_pengembangan_genap_2024']
                ]);
                
                $imported_count++;
            }
        }
        
        $pdo->commit();
        
        logAktivitas($pdo, $_SESSION['user_id'], 'Import Mahasiswa Multiple Docs', "Berhasil import $imported_count mahasiswa");
        $message = "Berhasil mengimport $imported_count data mahasiswa dengan multiple documents!";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Gagal import data: ' . $e->getMessage();
    }
}

// Statistik database
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $total_mahasiswa = $stmt->fetch()['total'];
    
    // Count docs availability
    $stmt = $pdo->query("
        SELECT 
            COUNT(CASE WHEN link_sk_mengajar IS NOT NULL AND link_sk_mengajar != '' THEN 1 END) as rpl01_docs,
            COUNT(CASE WHEN rpl02_perangkat_ganjil_2019 IS NOT NULL AND rpl02_perangkat_ganjil_2019 != '' THEN 1 END) as rpl02_docs,
            COUNT(CASE WHEN rpl03_pengembangan_ganjil_2019 IS NOT NULL AND rpl03_pengembangan_ganjil_2019 != '' THEN 1 END) as rpl03_docs,
            COUNT(CASE WHEN link_administrasi IS NOT NULL AND link_administrasi != '' THEN 1 END) as rpl04_docs,
            COUNT(CASE WHEN link_inovasi IS NOT NULL AND link_inovasi != '' THEN 1 END) as rpl05_docs
        FROM mahasiswa
    ");
    $doc_stats = $stmt->fetch();
} catch (PDOException $e) {
    $total_mahasiswa = 0;
    $doc_stats = ['rpl01_docs' => 0, 'rpl02_docs' => 0, 'rpl03_docs' => 0, 'rpl04_docs' => 0, 'rpl05_docs' => 0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data Mahasiswa - Multiple Documents - <?= APP_NAME ?></title>
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
            max-width: 1000px;
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
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.9; }
        
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #3498db;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
            font-size: 0.9rem;
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
            font-size: 0.85rem;
            border: 1px solid #e0e0e0;
        }
        
        .column-structure {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        
        .column-group {
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: white;
            border-radius: 3px;
        }
        
        .column-group h5 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .column-list {
            font-size: 0.8rem;
            color: #666;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Import Data Mahasiswa - Multiple Documents</h1>
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
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($total_mahasiswa) ?></div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($doc_stats['rpl01_docs']) ?></div>
                <div class="stat-label">RPL.01 Docs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($doc_stats['rpl02_docs']) ?></div>
                <div class="stat-label">RPL.02 Docs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($doc_stats['rpl03_docs']) ?></div>
                <div class="stat-label">RPL.03 Docs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($doc_stats['rpl04_docs']) ?></div>
                <div class="stat-label">RPL.04 Docs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($doc_stats['rpl05_docs']) ?></div>
                <div class="stat-label">RPL.05 Docs</div>
            </div>
        </div>
        
        <!-- Info -->
        <div class="info-box">
            <h3>📋 Import Data Mahasiswa dengan Multiple Documents</h3>
            <p>Sistem telah diupdate untuk mendukung multiple documents untuk RPL.02 (12 semester) dan RPL.03 (12 semester). Setiap mahasiswa kini dapat memiliki hingga 26 dokumen terpisah.</p>
        </div>
        
        <!-- Step 1: Demo Import -->
        <div class="step">
            <h3>Option 1: Import Sample Data (Demo)</h3>
            <p>Import 2 data sample dengan multiple documents untuk testing aplikasi:</p>
            <form method="POST">
                <button type="submit" name="confirm_import" class="btn btn-success"
                        onclick="return confirm('Import sample data mahasiswa dengan multiple documents?')">
                    🚀 Import Sample Data
                </button>
            </form>
        </div>
        
        <!-- Column Structure Guide -->
        <div class="step">
            <h3>📊 Struktur Kolom Excel yang Diperlukan</h3>
            <p>Untuk import data lengkap, file Excel harus memiliki kolom-kolom berikut:</p>
            
            <div class="column-structure">
                <div class="column-group">
                    <h5>Data Mahasiswa (Standard)</h5>
                    <div class="column-list">
                        nim, nama_lengkap, jenis_kelamin, tempat_tugas, email, no_telepon, nik, status_pegawai, 
                        tempat_lahir, tanggal_lahir, provinsi, kabupaten, kecamatan, jenjang
                    </div>
                </div>
                
                <div class="column-group">
                    <h5>RPL.01 - Pengembangan Kompetensi Pedagogik (1 kolom)</h5>
                    <div class="column-list">
                        link_sk_mengajar
                    </div>
                </div>
                
                <div class="column-group">
                    <h5>RPL.02 - Penyusunan Perangkat Pembelajaran (12 kolom)</h5>
                    <div class="column-list">
                        rpl02_perangkat_ganjil_2019, rpl02_perangkat_genap_2019,<br>
                        rpl02_perangkat_ganjil_2020, rpl02_perangkat_genap_2020,<br>
                        rpl02_perangkat_ganjil_2021, rpl02_perangkat_genap_2021,<br>
                        rpl02_perangkat_ganjil_2022, rpl02_perangkat_genap_2022,<br>
                        rpl02_perangkat_ganjil_2023, rpl02_perangkat_genap_2023,<br>
                        rpl02_perangkat_ganjil_2024, rpl02_perangkat_genap_2024
                    </div>
                </div>
                
                <div class="column-group">
                    <h5>RPL.03 - Pengembangan Kompetensi Profesional (12 kolom)</h5>
                    <div class="column-list">
                        rpl03_pengembangan_ganjil_2019, rpl03_pengembangan_genap_2019,<br>
                        rpl03_pengembangan_ganjil_2020, rpl03_pengembangan_genap_2020,<br>
                        rpl03_pengembangan_ganjil_2021, rpl03_pengembangan_genap_2021,<br>
                        rpl03_pengembangan_ganjil_2022, rpl03_pengembangan_genap_2022,<br>
                        rpl03_pengembangan_ganjil_2023, rpl03_pengembangan_genap_2023,<br>
                        rpl03_pengembangan_ganjil_2024, rpl03_pengembangan_genap_2024
                    </div>
                </div>
                
                <div class="column-group">
                    <h5>RPL.04 & RPL.05 (2 kolom)</h5>
                    <div class="column-list">
                        link_administrasi, link_inovasi
                    </div>
                </div>
            </div>
            
            <div class="alert alert-warning" style="margin-top: 1rem;">
                <strong>Total: 29 kolom</strong> (14 data mahasiswa + 1 RPL.01 + 12 RPL.02 + 12 RPL.03 + 2 RPL.04&05)
            </div>
        </div>
        
        <!-- Step 2: Manual SQL -->
        <div class="step">
            <h3>Option 2: Import Manual via SQL</h3>
            <p>Untuk import data lengkap, gunakan script SQL berikut dengan semua kolom:</p>
            
            <pre>-- Example insert dengan multiple documents
INSERT INTO mahasiswa (
    nim, nama_lengkap, jenis_kelamin, tempat_tugas, email, no_telepon,
    nik, status_pegawai, tempat_lahir, tanggal_lahir, provinsi, kabupaten, kecamatan, jenjang,
    link_sk_mengajar, link_administrasi, link_inovasi,
    -- RPL.02 Documents (12 columns)
    rpl02_perangkat_ganjil_2019, rpl02_perangkat_genap_2019,
    rpl02_perangkat_ganjil_2020, rpl02_perangkat_genap_2020,
    rpl02_perangkat_ganjil_2021, rpl02_perangkat_genap_2021,
    rpl02_perangkat_ganjil_2022, rpl02_perangkat_genap_2022,
    rpl02_perangkat_ganjil_2023, rpl02_perangkat_genap_2023,
    rpl02_perangkat_ganjil_2024, rpl02_perangkat_genap_2024,
    -- RPL.03 Documents (12 columns)
    rpl03_pengembangan_ganjil_2019, rpl03_pengembangan_genap_2019,
    rpl03_pengembangan_ganjil_2020, rpl03_pengembangan_genap_2020,
    rpl03_pengembangan_ganjil_2021, rpl03_pengembangan_genap_2021,
    rpl03_pengembangan_ganjil_2022, rpl03_pengembangan_genap_2022,
    rpl03_pengembangan_ganjil_2023, rpl03_pengembangan_genap_2023,
    rpl03_pengembangan_ganjil_2024, rpl03_pengembangan_genap_2024
) VALUES (
    '25869050001', 'NAMA MAHASISWA', 'Perempuan', 'TEMPAT TUGAS', 
    'email@test.com', '081234567890', '1234567890123456', 'PPPK',
    'KOTA LAHIR', '1990-01-01', 'PROVINSI', 'KABUPATEN', 'KECAMATAN', 'SMP',
    'https://drive.google.com/link1', 'https://drive.google.com/link2', 'https://drive.google.com/link3',
    -- RPL.02 Links (12 values - dapat kosong jika tidak ada)
    'https://drive.google.com/perangkat1', 'https://drive.google.com/perangkat2',
    '', 'https://drive.google.com/perangkat4',  -- Note: dapat kosong
    'https://drive.google.com/perangkat5', 'https://drive.google.com/perangkat6',
    'https://drive.google.com/perangkat7', '',
    'https://drive.google.com/perangkat9', 'https://drive.google.com/perangkat10',
    'https://drive.google.com/perangkat11', 'https://drive.google.com/perangkat12',
    -- RPL.03 Links (12 values - dapat kosong jika tidak ada)
    'https://drive.google.com/profesional1', 'https://drive.google.com/profesional2',
    'https://drive.google.com/profesional3', '',
    'https://drive.google.com/profesional5', 'https://drive.google.com/profesional6',
    '', 'https://drive.google.com/profesional8',
    'https://drive.google.com/profesional9', 'https://drive.google.com/profesional10',
    'https://drive.google.com/profesional11', ''
);</pre>
        </div>
        
        <!-- Step 3: PhpSpreadsheet -->
        <div class="step">
            <h3>Option 3: Otomatis dengan PhpSpreadsheet (Production)</h3>
            <p>Script PHP untuk membaca Excel dengan multiple columns:</p>
            
            <pre>use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('Data RPL Mahasiswa.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

foreach ($rows as $key => $row) {
    if ($key == 0) continue; // Skip header
    
    // Map columns to variables
    $nim = $row[0];
    $nama_lengkap = $row[1];
    // ... other basic columns
    
    // RPL.02 Documents (columns 15-26)
    $rpl02_docs = array_slice($row, 15, 12);
    
    // RPL.03 Documents (columns 27-38)
    $rpl03_docs = array_slice($row, 27, 12);
    
    // Insert with all documents
    $stmt = $pdo->prepare("INSERT INTO mahasiswa (...) VALUES (...)");
    $stmt->execute([...]);
}</pre>
        </div>
        
        <div class="card">
            <h3>🔧 Next Steps Setelah Import</h3>
            <ol>
                <li><strong>Verifikasi data:</strong> Cek bahwa semua dokumen berhasil diimport</li>
                <li><strong>Test links:</strong> Pastikan link Google Drive dapat diakses</li>
                <li><strong>Assign dosen:</strong> Gunakan fitur "Auto Assign" untuk distribusi mahasiswa</li>
                <li><strong>Test penilaian:</strong> Coba form penilaian dengan tabs/accordion baru</li>
                <li><strong>Training dosen:</strong> Sosialisasi interface baru ke dosen penilai</li>
            </ol>
            
            <div class="alert alert-warning" style="margin-top: 1rem;">
                <strong>Catatan Penting:</strong><br>
                • Tidak semua mahasiswa harus memiliki dokumen untuk setiap semester<br>
                • Kolom dapat dikosongkan (NULL) jika tidak ada dokumen<br>
                • Dosen akan melihat tab dengan indikator dokumen tersedia/tidak<br>
                • Sistem mendukung hingga 26 dokumen per mahasiswa
            </div>
        </div>
    </div>
</body>
</html>