<?php
require_once 'config.php';
requireLogin();

$mahasiswa_id = (int)($_GET['id'] ?? 0);
$dosen_id = $_SESSION['user_id'];

if (!$mahasiswa_id) {
    header('Location: dashboard_dosen.php');
    exit();
}

// Ambil data mahasiswa dan cek akses - UPDATE: tambah semua kolom dokumen baru
try {
    $stmt = $pdo->prepare("
        SELECT m.*, p.* 
        FROM mahasiswa m 
        LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id AND p.dosen_penilai_id = ?
        WHERE m.id = ? AND (m.assigned_dosen_id = ? OR ? IN (SELECT id FROM users WHERE role = 'admin'))
    ");
    $stmt->execute([$dosen_id, $mahasiswa_id, $dosen_id, $_SESSION['user_id']]);
    $data = $stmt->fetch();
    
    if (!$data) {
        header('Location: dashboard_dosen.php');
        exit();
    }
    
} catch (PDOException $e) {
    header('Location: dashboard_dosen.php');
    exit();
}

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Validasi input
    $rpl01 = (int)($_POST['rpl01_pedagogik'] ?? 0);
    $rpl02 = (int)($_POST['rpl02_perangkat'] ?? 0);
    $rpl03 = (int)($_POST['rpl03_profesional'] ?? 0);
    $rpl04 = (int)($_POST['rpl04_administrasi'] ?? 0);
    $rpl05 = (int)($_POST['rpl05_inovasi'] ?? 0);
    $catatan = sanitizeInput($_POST['catatan_dosen'] ?? '');
    
    // Validasi range skor
    $scores = [$rpl01, $rpl02, $rpl03, $rpl04, $rpl05];
    $valid = true;
    
    foreach ($scores as $score) {
        if ($score < 0 || $score > 100) {
            $valid = false;
            break;
        }
    }
    
    if (!$valid) {
        $error = 'Semua skor harus dalam rentang 0-100!';
    } else {
        try {
            // Konversi ke huruf mutu (dengan konversi baru)
            $huruf01 = skorKeHurufMutu($rpl01);
            $huruf02 = skorKeHurufMutu($rpl02);
            $huruf03 = skorKeHurufMutu($rpl03);
            $huruf04 = skorKeHurufMutu($rpl04);
            $huruf05 = skorKeHurufMutu($rpl05);
            
            $status = ($action === 'save_final') ? 'final' : 'draft';
            
            if ($data['id']) {
                // Update existing record
                $stmt = $pdo->prepare("
                    UPDATE penilaian_rpl SET 
                        rpl01_pedagogik = ?, rpl01_huruf_mutu = ?,
                        rpl02_perangkat = ?, rpl02_huruf_mutu = ?,
                        rpl03_profesional = ?, rpl03_huruf_mutu = ?,
                        rpl04_administrasi = ?, rpl04_huruf_mutu = ?,
                        rpl05_inovasi = ?, rpl05_huruf_mutu = ?,
                        catatan_dosen = ?, status_penilaian = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([
                    $rpl01, $huruf01, $rpl02, $huruf02, $rpl03, $huruf03,
                    $rpl04, $huruf04, $rpl05, $huruf05, $catatan, $status, $data['id']
                ]);
            } else {
                // Insert new record
                $stmt = $pdo->prepare("
                    INSERT INTO penilaian_rpl (
                        mahasiswa_id, dosen_penilai_id,
                        rpl01_pedagogik, rpl01_huruf_mutu,
                        rpl02_perangkat, rpl02_huruf_mutu,
                        rpl03_profesional, rpl03_huruf_mutu,
                        rpl04_administrasi, rpl04_huruf_mutu,
                        rpl05_inovasi, rpl05_huruf_mutu,
                        catatan_dosen, status_penilaian
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $mahasiswa_id, $dosen_id,
                    $rpl01, $huruf01, $rpl02, $huruf02, $rpl03, $huruf03,
                    $rpl04, $huruf04, $rpl05, $huruf05, $catatan, $status
                ]);
            }
            
            // Update status mahasiswa
            $stmt = $pdo->prepare("UPDATE mahasiswa SET status_penilaian = ? WHERE id = ?");
            $status_mhs = ($status === 'final') ? 'selesai' : 'sedang_dinilai';
            $stmt->execute([$status_mhs, $mahasiswa_id]);
            
            // Log aktivitas
            $action_log = ($status === 'final') ? 'Finalisasi Penilaian' : 'Simpan Draft Penilaian';
            logAktivitas($pdo, $dosen_id, $action_log, "Mahasiswa: {$data['nama_lengkap']} (ID: $mahasiswa_id)");
            
            if ($status === 'final') {
                $message = 'Penilaian berhasil disimpan dan difinalisasi! Data tidak dapat diubah lagi.';
            } else {
                $message = 'Draft penilaian berhasil disimpan. Anda dapat melanjutkan nanti.';
            }
            
            // Refresh data
            $stmt = $pdo->prepare("
                SELECT m.*, p.* 
                FROM mahasiswa m 
                LEFT JOIN penilaian_rpl p ON m.id = p.mahasiswa_id AND p.dosen_penilai_id = ?
                WHERE m.id = ?
            ");
            $stmt->execute([$dosen_id, $mahasiswa_id]);
            $data = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan penilaian: ' . $e->getMessage();
        }
    }
}

// Get dokumen data
$rpl02_documents = getRPL02Documents($data);
$rpl03_documents = getRPL03Documents($data);
$rubrik = getRubrikPenilaian();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian RPL - <?= sanitizeInput($data['nama_lengkap']) ?></title>
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.3rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn:hover { opacity: 0.9; }
        
        .container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }
        
        .main-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .mahasiswa-info {
            background: #e8f4fd;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #3498db;
        }
        
        .mahasiswa-info h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        
        .form-section h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sks-badge {
            background: #3498db;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        input[type="number"], textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        input[type="number"]:focus, textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .score-input {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: center;
        }
        
        .grade-display {
            padding: 0.75rem;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            min-width: 60px;
        }
        
        .grade-A { background: #d4edda; color: #155724; }
        .grade-B { background: #d1ecf1; color: #0c5460; }
        .grade-C { background: #fff3cd; color: #856404; }
        .grade-D { background: #f8d7da; color: #721c24; }
        .grade-E { background: #f8d7da; color: #721c24; }
        
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
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
        
        /* Tabs untuk dokumen multiple */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .tab {
            padding: 0.5rem 1rem;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-bottom: none;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 2px;
            font-size: 0.85rem;
        }
        
        .tab.active {
            background: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
            font-weight: bold;
        }
        
        .tab:hover {
            background: #e9ecef;
        }
        
        .tab-content {
            display: none;
            padding: 1rem;
            border: 1px solid #ddd;
            border-top: none;
            min-height: 100px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .doc-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #e8f4fd;
            color: #0984e3;
            text-decoration: none;
            border-radius: 5px;
            margin: 0.25rem 0;
            font-size: 0.9rem;
            transition: background 0.3s;
            border: 1px solid #d1ecf1;
        }
        
        .doc-link:hover {
            background: #d1ecf1;
        }
        
        .doc-empty {
            color: #999;
            font-style: italic;
            padding: 1rem;
            text-align: center;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .rubrik-info {
            font-size: 0.9rem;
            color: #666;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .final-warning {
            background: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #ffeaa7;
            margin-bottom: 1rem;
        }
        
        .doc-count {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }
        
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
            
            .sidebar {
                position: static;
                order: -1;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .score-input {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .tabs {
                font-size: 0.8rem;
            }
            
            .tab {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Penilaian RPL - <?= sanitizeInput($data['nama_lengkap']) ?></h1>
        <a href="<?= isDosen() ? 'dashboard_dosen.php' : 'dashboard_admin.php' ?>" class="btn btn-primary">
            ← Kembali ke Dashboard
        </a>
    </div>
    
    <div class="container">
        <div class="main-content">
            <?php if ($message): ?>
                <div class="alert alert-success"><?= sanitizeInput($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitizeInput($error) ?></div>
            <?php endif; ?>
            
            <!-- Info Mahasiswa -->
            <div class="mahasiswa-info">
                <h3>Informasi Mahasiswa</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">NIM</span>
                        <span class="info-value"><?= sanitizeInput($data['nim']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nama Lengkap</span>
                        <span class="info-value"><?= sanitizeInput($data['nama_lengkap']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jenjang</span>
                        <span class="info-value"><?= sanitizeInput($data['jenjang']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tempat Tugas</span>
                        <span class="info-value"><?= sanitizeInput($data['tempat_tugas']) ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($data['status_penilaian'] === 'final'): ?>
                <div class="final-warning">
                    <strong>⚠️ Penilaian Sudah Difinalisasi</strong><br>
                    Penilaian ini sudah difinalisasi dan tidak dapat diubah lagi. 
                    Anda hanya dapat melihat hasil penilaian.
                </div>
            <?php endif; ?>
            
            <!-- Form Penilaian -->
            <form method="POST" <?= $data['status_penilaian'] === 'final' ? 'style="pointer-events: none; opacity: 0.7;"' : '' ?>>
                
                <!-- RPL.01 - Single Document -->
                <div class="form-section">
                    <h4>
                        <?= $rubrik['rpl01_pedagogik']['nama'] ?>
                        <span class="sks-badge"><?= $rubrik['rpl01_pedagogik']['sks'] ?> SKS</span>
                    </h4>
                    
                    <div class="rubrik-info">
                        <?= $rubrik['rpl01_pedagogik']['deskripsi'] ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="rpl01_pedagogik">Skor (0-100)</label>
                        <div class="score-input">
                            <input type="number" 
                                   id="rpl01_pedagogik" 
                                   name="rpl01_pedagogik" 
                                   min="0" 
                                   max="100" 
                                   value="<?= $data['rpl01_pedagogik'] ?? '' ?>"
                                   onchange="updateGrade('rpl01_pedagogik')"
                                   required>
                            <div id="grade_rpl01_pedagogik" class="grade-display">
                                <?= $data['rpl01_pedagogik'] ? skorKeHurufMutu($data['rpl01_pedagogik']) : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- RPL.02 - Multiple Documents -->
                <div class="form-section">
                    <h4>
                        <?= $rubrik['rpl02_perangkat']['nama'] ?>
                        <span class="sks-badge"><?= $rubrik['rpl02_perangkat']['sks'] ?> SKS</span>
                        <span class="doc-count"><?= countAvailableDocuments($rpl02_documents) ?>/12 dokumen</span>
                    </h4>
                    
                    <div class="rubrik-info">
                        <?= $rubrik['rpl02_perangkat']['deskripsi'] ?>
                    </div>
                    
                    <!-- Document Tabs -->
                    <div class="tabs" id="rpl02-tabs">
                        <?php foreach ($rpl02_documents as $key => $doc): ?>
                            <div class="tab <?= $key === 'ganjil_2019' ? 'active' : '' ?>" 
                                 onclick="switchTab('rpl02', '<?= $key ?>')">
                                <?= $doc['label'] ?>
                                <?php if (!empty($doc['link'])): ?>
                                    <span style="color: #27ae60;">●</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Tab Contents -->
                    <?php foreach ($rpl02_documents as $key => $doc): ?>
                        <div class="tab-content <?= $key === 'ganjil_2019' ? 'active' : '' ?>" 
                             id="rpl02-<?= $key ?>">
                            <strong><?= $doc['label'] ?></strong>
                            <?php if (!empty($doc['link'])): ?>
                                <br><a href="<?= formatGoogleDriveViewLink($doc['link']) ?>" 
                                       target="_blank" class="doc-link">
                                    📄 Lihat Dokumen Perangkat
                                </a>
                            <?php else: ?>
                                <div class="doc-empty">Tidak ada dokumen untuk semester ini</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="rpl02_perangkat">Skor (0-100)</label>
                        <div class="score-input">
                            <input type="number" 
                                   id="rpl02_perangkat" 
                                   name="rpl02_perangkat" 
                                   min="0" 
                                   max="100" 
                                   value="<?= $data['rpl02_perangkat'] ?? '' ?>"
                                   onchange="updateGrade('rpl02_perangkat')"
                                   required>
                            <div id="grade_rpl02_perangkat" class="grade-display">
                                <?= $data['rpl02_perangkat'] ? skorKeHurufMutu($data['rpl02_perangkat']) : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- RPL.03 - Multiple Documents -->
                <div class="form-section">
                    <h4>
                        <?= $rubrik['rpl03_profesional']['nama'] ?>
                        <span class="sks-badge"><?= $rubrik['rpl03_profesional']['sks'] ?> SKS</span>
                        <span class="doc-count"><?= countAvailableDocuments($rpl03_documents) ?>/12 dokumen</span>
                    </h4>
                    
                    <div class="rubrik-info">
                        <?= $rubrik['rpl03_profesional']['deskripsi'] ?>
                    </div>
                    
                    <!-- Document Tabs -->
                    <div class="tabs" id="rpl03-tabs">
                        <?php foreach ($rpl03_documents as $key => $doc): ?>
                            <div class="tab <?= $key === 'ganjil_2019' ? 'active' : '' ?>" 
                                 onclick="switchTab('rpl03', '<?= $key ?>')">
                                <?= $doc['label'] ?>
                                <?php if (!empty($doc['link'])): ?>
                                    <span style="color: #27ae60;">●</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Tab Contents -->
                    <?php foreach ($rpl03_documents as $key => $doc): ?>
                        <div class="tab-content <?= $key === 'ganjil_2019' ? 'active' : '' ?>" 
                             id="rpl03-<?= $key ?>">
                            <strong><?= $doc['label'] ?></strong>
                            <?php if (!empty($doc['link'])): ?>
                                <br><a href="<?= formatGoogleDriveViewLink($doc['link']) ?>" 
                                       target="_blank" class="doc-link">
                                    📄 Lihat Dokumen Pengembangan
                                </a>
                            <?php else: ?>
                                <div class="doc-empty">Tidak ada dokumen untuk semester ini</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="rpl03_profesional">Skor (0-100)</label>
                        <div class="score-input">
                            <input type="number" 
                                   id="rpl03_profesional" 
                                   name="rpl03_profesional" 
                                   min="0" 
                                   max="100" 
                                   value="<?= $data['rpl03_profesional'] ?? '' ?>"
                                   onchange="updateGrade('rpl03_profesional')"
                                   required>
                            <div id="grade_rpl03_profesional" class="grade-display">
                                <?= $data['rpl03_profesional'] ? skorKeHurufMutu($data['rpl03_profesional']) : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- RPL.04 - Single Document -->
                <div class="form-section">
                    <h4>
                        <?= $rubrik['rpl04_administrasi']['nama'] ?>
                        <span class="sks-badge"><?= $rubrik['rpl04_administrasi']['sks'] ?> SKS</span>
                    </h4>
                    
                    <div class="rubrik-info">
                        <?= $rubrik['rpl04_administrasi']['deskripsi'] ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="rpl04_administrasi">Skor (0-100)</label>
                        <div class="score-input">
                            <input type="number" 
                                   id="rpl04_administrasi" 
                                   name="rpl04_administrasi" 
                                   min="0" 
                                   max="100" 
                                   value="<?= $data['rpl04_administrasi'] ?? '' ?>"
                                   onchange="updateGrade('rpl04_administrasi')"
                                   required>
                            <div id="grade_rpl04_administrasi" class="grade-display">
                                <?= $data['rpl04_administrasi'] ? skorKeHurufMutu($data['rpl04_administrasi']) : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- RPL.05 - Single Document -->
                <div class="form-section">
                    <h4>
                        <?= $rubrik['rpl05_inovasi']['nama'] ?>
                        <span class="sks-badge"><?= $rubrik['rpl05_inovasi']['sks'] ?> SKS</span>
                    </h4>
                    
                    <div class="rubrik-info">
                        <?= $rubrik['rpl05_inovasi']['deskripsi'] ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="rpl05_inovasi">Skor (0-100)</label>
                        <div class="score-input">
                            <input type="number" 
                                   id="rpl05_inovasi" 
                                   name="rpl05_inovasi" 
                                   min="0" 
                                   max="100" 
                                   value="<?= $data['rpl05_inovasi'] ?? '' ?>"
                                   onchange="updateGrade('rpl05_inovasi')"
                                   required>
                            <div id="grade_rpl05_inovasi" class="grade-display">
                                <?= $data['rpl05_inovasi'] ? skorKeHurufMutu($data['rpl05_inovasi']) : '-' ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h4>Catatan Dosen (Opsional)</h4>
                    <div class="form-group">
                        <textarea name="catatan_dosen" 
                                  rows="4" 
                                  placeholder="Masukkan catatan atau komentar tambahan mengenai penilaian..."><?= sanitizeInput($data['catatan_dosen'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <?php if ($data['status_penilaian'] !== 'final'): ?>
                    <div class="actions">
                        <button type="submit" name="action" value="save_draft" class="btn btn-warning">
                            💾 Simpan sebagai Draft
                        </button>
                        <button type="submit" name="action" value="save_final" class="btn btn-success"
                                onclick="return confirm('Apakah Anda yakin ingin memfinalisasi penilaian? Setelah difinalisasi, penilaian tidak dapat diubah lagi.')">
                            ✅ Finalisasi Penilaian
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Sidebar -->
        <div class="sidebar">
            <h3 style="margin-bottom: 1rem; color: #2c3e50;">Dokumen Mahasiswa</h3>
            
            <div style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">SK/Surat Tugas Mengajar</h4>
                <?php if ($data['link_sk_mengajar']): ?>
                    <a href="<?= formatGoogleDriveViewLink($data['link_sk_mengajar']) ?>" 
                       target="_blank" class="doc-link">📄 Lihat Dokumen</a>
                <?php else: ?>
                    <span style="color: #999; font-size: 0.9rem;">Tidak tersedia</span>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Administrasi Pembelajaran</h4>
                <?php if ($data['link_administrasi']): ?>
                    <a href="<?= formatGoogleDriveViewLink($data['link_administrasi']) ?>" 
                       target="_blank" class="doc-link">📄 Lihat Dokumen</a>
                <?php else: ?>
                    <span style="color: #999; font-size: 0.9rem;">Tidak tersedia</span>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Inovasi Pembelajaran</h4>
                <?php if ($data['link_inovasi']): ?>
                    <a href="<?= formatGoogleDriveViewLink($data['link_inovasi']) ?>" 
                       target="_blank" class="doc-link">📄 Lihat Dokumen</a>
                <?php else: ?>
                    <span style="color: #999; font-size: 0.9rem;">Tidak tersedia</span>
                <?php endif; ?>
            </div>
            
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 2rem;">
                <h4 style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Konversi Nilai (Update)</h4>
                <div style="font-size: 0.8rem; color: #666;">
                    <div>80-100 = A</div>
                    <div>70-79,9 = B</div>
                    <div>60-69,9 = C</div>
                    <div>50-59,9 = D</div>
                    <div>0-49,9 = E</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function updateGrade(field) {
            const scoreInput = document.getElementById(field);
            const gradeDisplay = document.getElementById('grade_' + field);
            const score = parseInt(scoreInput.value) || 0;
            
            let grade = 'E';
            let gradeClass = 'grade-E';
            
            if (score >= 80) {
                grade = 'A';
                gradeClass = 'grade-A';
            } else if (score >= 70) {
                grade = 'B';
                gradeClass = 'grade-B';
            } else if (score >= 60) {
                grade = 'C';
                gradeClass = 'grade-C';
            } else if (score >= 50) {
                grade = 'D';
                gradeClass = 'grade-D';
            }
            
            gradeDisplay.textContent = score > 0 ? grade : '-';
            gradeDisplay.className = 'grade-display ' + (score > 0 ? gradeClass : '');
        }
        
        function switchTab(rplType, semesterKey) {
            // Remove active class from all tabs
            const tabs = document.querySelectorAll(`#${rplType}-tabs .tab`);
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all tab contents
            const contents = document.querySelectorAll(`[id^="${rplType}-"]`);
            contents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Add active class to corresponding content
            document.getElementById(`${rplType}-${semesterKey}`).classList.add('active');
        }
        
        // Initialize grades on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateGrade('rpl01_pedagogik');
            updateGrade('rpl02_perangkat');
            updateGrade('rpl03_profesional');
            updateGrade('rpl04_administrasi');
            updateGrade('rpl05_inovasi');
        });
    </script>
</body>
</html>