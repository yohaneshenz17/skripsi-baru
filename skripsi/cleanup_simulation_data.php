<?php
/**
 * =====================================================
 * SCRIPT PEMBERSIHAN DATA MAHASISWA SIMULASI
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * =====================================================
 * 
 * TUJUAN: Membersihkan data mahasiswa simulasi dari semua tahapan workflow
 * STATUS: PRODUCTION READY - SAFE FOR LAUNCH
 * 
 * Author: Auto-generated for STK Santo Yakobus
 * Date: August 2025
 * Database: stkp7133_skripsi
 */

// =====================================================
// KONFIGURASI DATABASE (SESUAI database.php)
// =====================================================
$host = 'localhost';
$dbname = 'stkp7133_skripsi';
$username = 'stkp7133_skripsi'; // Dari database.php
$password = 'stkmerauke01'; // Dari database.php

// =====================================================
// SECURITY & ERROR HANDLING
// =====================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 menit timeout

// Pastikan script hanya dijalankan via browser dengan konfirmasi
session_start();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembersihan Data Simulasi - SIM Tugas Akhir STK Santo Yakobus</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: 'Segoe UI', sans-serif;
        }
        .card { 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
        }
        .btn-primary { 
            background: linear-gradient(45deg, #667eea, #764ba2); 
            border: none; 
        }
        .btn-danger { 
            background: linear-gradient(45deg, #f093fb, #f5576c); 
            border: none; 
        }
        .btn-success { 
            background: linear-gradient(45deg, #4facfe, #00f2fe); 
            border: none; 
        }
        .alert { 
            border-radius: 10px; 
        }
        .progress { 
            height: 20px; 
            border-radius: 10px; 
        }
        .mahasiswa-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .mahasiswa-card:hover {
            transform: translateY(-2px);
        }
        .mahasiswa-card.selected {
            border: 2px solid #007bff;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-broom me-2"></i>
                            Pembersihan Data Mahasiswa Simulasi - SIM Tugas Akhir
                        </h4>
                        <small>STK Santo Yakobus Merauke - Database: <?= $dbname ?></small>
                    </div>
                    <div class="card-body">

<?php
// =====================================================
// KONEKSI DATABASE
// =====================================================
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Koneksi database berhasil!</div>';
    
} catch(PDOException $e) {
    echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Koneksi database gagal: ' . $e->getMessage() . '</div>';
    echo '</div></div></div></div></body></html>';
    exit;
}

// =====================================================
// IDENTIFIKASI MAHASISWA SIMULASI
// =====================================================
function identifySimulationStudents($pdo) {
    $sql = "SELECT * FROM mahasiswa 
            WHERE (nim LIKE '123456%' OR nama LIKE '%Contoh%' OR nama LIKE '%Agus Bumagi%' OR nama LIKE '%Simulasi%')
            ORDER BY id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// =====================================================
// ANALISIS DATA YANG AKAN DIHAPUS
// =====================================================
function analyzeDataToDelete($pdo, $mahasiswa_ids) {
    if (empty($mahasiswa_ids)) return [];
    
    $ids_str = implode(',', $mahasiswa_ids);
    
    $analysis = [];
    
    // Proposal mahasiswa
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM proposal_mahasiswa WHERE mahasiswa_id IN ($ids_str)");
    $analysis['proposal_mahasiswa'] = $stmt->fetch()['count'];
    
    // Jurnal bimbingan (via proposal)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM jurnal_bimbingan jb 
                        INNER JOIN proposal_mahasiswa pm ON jb.proposal_id = pm.id 
                        WHERE pm.mahasiswa_id IN ($ids_str)");
    $analysis['jurnal_bimbingan'] = $stmt->fetch()['count'];
    
    // Seminar proposal
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM seminar_proposal_mahasiswa WHERE mahasiswa_id IN ($ids_str)");
    $analysis['seminar_proposal_mahasiswa'] = $stmt->fetch()['count'];
    
    // Penilaian seminar proposal
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM penilaian_seminar_proposal WHERE mahasiswa_id IN ($ids_str)");
    $analysis['penilaian_seminar_proposal'] = $stmt->fetch()['count'];
    
    // Permohonan izin penelitian
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM permohonan_izin_penelitian pir
                        INNER JOIN proposal_mahasiswa pm ON pir.proposal_mahasiswa_id = pm.id 
                        WHERE pm.mahasiswa_id IN ($ids_str)");
    $analysis['permohonan_izin_penelitian'] = $stmt->fetch()['count'];
    
    // Penelitian (legacy)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM penelitian p
                        INNER JOIN proposal_mahasiswa pm ON p.proposal_mahasiswa_id = pm.id 
                        WHERE pm.mahasiswa_id IN ($ids_str)");
    $analysis['penelitian'] = $stmt->fetch()['count'];
    
    // Seminar skripsi
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM seminar_skripsi_mahasiswa WHERE mahasiswa_id IN ($ids_str)");
    $analysis['seminar_skripsi_mahasiswa'] = $stmt->fetch()['count'];
    
    // Penilaian seminar skripsi
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM penilaian_seminar_skripsi WHERE mahasiswa_id IN ($ids_str)");
    $analysis['penilaian_seminar_skripsi'] = $stmt->fetch()['count'];
    
    // Publikasi tugas akhir
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM publikasi_tugas_akhir pta
                        INNER JOIN proposal_mahasiswa pm ON pta.proposal_mahasiswa_id = pm.id 
                        WHERE pm.mahasiswa_id IN ($ids_str)");
    $analysis['publikasi_tugas_akhir'] = $stmt->fetch()['count'];
    
    // Notifikasi
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM notifikasi 
                        WHERE untuk_role = 'mahasiswa' AND user_id IN ($ids_str)");
    $analysis['notifikasi'] = $stmt->fetch()['count'];
    
    // Konsultasi (legacy)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM konsultasi k
                        INNER JOIN proposal_mahasiswa pm ON k.proposal_mahasiswa_id = pm.id 
                        WHERE pm.mahasiswa_id IN ($ids_str)");
    $analysis['konsultasi'] = $stmt->fetch()['count'];
    
    return $analysis;
}

// =====================================================
// PROSES PENGHAPUSAN DATA
// =====================================================
function cleanupStudentData($pdo, $mahasiswa_ids) {
    if (empty($mahasiswa_ids)) {
        return ['success' => false, 'message' => 'Tidak ada mahasiswa yang dipilih'];
    }
    
    $ids_str = implode(',', $mahasiswa_ids);
    $deleted_count = [];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Hapus notifikasi mahasiswa
        $stmt = $pdo->prepare("DELETE FROM notifikasi WHERE untuk_role = 'mahasiswa' AND user_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['notifikasi'] = $stmt->rowCount();
        
        // 2. Hapus penilaian seminar skripsi
        $stmt = $pdo->prepare("DELETE FROM penilaian_seminar_skripsi WHERE mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['penilaian_seminar_skripsi'] = $stmt->rowCount();
        
        // 3. Hapus seminar skripsi mahasiswa
        $stmt = $pdo->prepare("DELETE FROM seminar_skripsi_mahasiswa WHERE mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['seminar_skripsi_mahasiswa'] = $stmt->rowCount();
        
        // 4. Hapus publikasi tugas akhir (via proposal)
        $stmt = $pdo->prepare("DELETE pta FROM publikasi_tugas_akhir pta
                              INNER JOIN proposal_mahasiswa pm ON pta.proposal_mahasiswa_id = pm.id 
                              WHERE pm.mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['publikasi_tugas_akhir'] = $stmt->rowCount();
        
        // 5. Hapus permohonan izin penelitian (via proposal)
        $stmt = $pdo->prepare("DELETE pir FROM permohonan_izin_penelitian pir
                              INNER JOIN proposal_mahasiswa pm ON pir.proposal_mahasiswa_id = pm.id 
                              WHERE pm.mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['permohonan_izin_penelitian'] = $stmt->rowCount();
        
        // 6. Hapus penelitian legacy (via proposal)
        $stmt = $pdo->prepare("DELETE p FROM penelitian p
                              INNER JOIN proposal_mahasiswa pm ON p.proposal_mahasiswa_id = pm.id 
                              WHERE pm.mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['penelitian'] = $stmt->rowCount();
        
        // 7. Hapus konsultasi legacy (via proposal)
        $stmt = $pdo->prepare("DELETE k FROM konsultasi k
                              INNER JOIN proposal_mahasiswa pm ON k.proposal_mahasiswa_id = pm.id 
                              WHERE pm.mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['konsultasi'] = $stmt->rowCount();
        
        // 8. Hapus penilaian seminar proposal
        $stmt = $pdo->prepare("DELETE FROM penilaian_seminar_proposal WHERE mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['penilaian_seminar_proposal'] = $stmt->rowCount();
        
        // 9. Hapus seminar proposal mahasiswa
        $stmt = $pdo->prepare("DELETE FROM seminar_proposal_mahasiswa WHERE mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['seminar_proposal_mahasiswa'] = $stmt->rowCount();
        
        // 10. Hapus jurnal bimbingan (via proposal)
        $stmt = $pdo->prepare("DELETE jb FROM jurnal_bimbingan jb
                              INNER JOIN proposal_mahasiswa pm ON jb.proposal_id = pm.id 
                              WHERE pm.mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['jurnal_bimbingan'] = $stmt->rowCount();
        
        // 11. Hapus proposal mahasiswa
        $stmt = $pdo->prepare("DELETE FROM proposal_mahasiswa WHERE mahasiswa_id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['proposal_mahasiswa'] = $stmt->rowCount();
        
        // 12. Hapus mahasiswa (terakhir)
        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id IN ($ids_str)");
        $stmt->execute();
        $deleted_count['mahasiswa'] = $stmt->rowCount();
        
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'Data mahasiswa simulasi berhasil dihapus!',
            'deleted_count' => $deleted_count
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false, 
            'message' => 'Error saat menghapus data: ' . $e->getMessage()
        ];
    }
}

// =====================================================
// PROSES BERDASARKAN AKSI
// =====================================================
$action = $_GET['action'] ?? 'scan';
$simulation_students = identifySimulationStudents($pdo);

if ($action === 'scan') {
    // Tampilkan mahasiswa simulasi yang terdeteksi
    echo '<div class="alert alert-info">
            <i class="fas fa-search me-2"></i>
            <strong>Pemindaian Selesai!</strong> Ditemukan ' . count($simulation_students) . ' mahasiswa simulasi.
          </div>';
    
    if (!empty($simulation_students)) {
        echo '<h5 class="mt-4"><i class="fas fa-users me-2"></i>Mahasiswa Simulasi Terdeteksi:</h5>';
        echo '<form method="post" action="?action=analyze">';
        echo '<div class="row">';
        
        foreach ($simulation_students as $student) {
            echo '<div class="col-md-6 mb-3">';
            echo '<div class="card mahasiswa-card" onclick="toggleStudent(' . $student['id'] . ')">';
            echo '<div class="card-body">';
            echo '<div class="form-check">';
            echo '<input class="form-check-input" type="checkbox" name="selected_students[]" value="' . $student['id'] . '" id="student_' . $student['id'] . '" checked>';
            echo '<label class="form-check-label" for="student_' . $student['id'] . '">';
            echo '<strong>' . htmlspecialchars($student['nama']) . '</strong><br>';
            echo '<small class="text-muted">NIM: ' . htmlspecialchars($student['nim']) . '</small><br>';
            echo '<small class="text-muted">Email: ' . htmlspecialchars($student['email']) . '</small>';
            echo '</label>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '</div>';
        echo '<div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">';
        echo '<button type="submit" class="btn btn-primary me-md-2">';
        echo '<i class="fas fa-chart-line me-2"></i>Analisis Data yang Akan Dihapus';
        echo '</button>';
        echo '</div>';
        echo '</form>';
    }

} elseif ($action === 'analyze' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $selected_students = $_POST['selected_students'] ?? [];
    
    if (empty($selected_students)) {
        echo '<div class="alert alert-warning">Tidak ada mahasiswa yang dipilih!</div>';
    } else {
        $analysis = analyzeDataToDelete($pdo, $selected_students);
        
        // Tampilkan hasil analisis
        echo '<h5><i class="fas fa-chart-bar me-2"></i>Analisis Data yang Akan Dihapus:</h5>';
        echo '<div class="alert alert-warning">';
        echo '<strong><i class="fas fa-exclamation-triangle me-2"></i>PERHATIAN:</strong> ';
        echo 'Data berikut akan dihapus PERMANEN dari database:';
        echo '</div>';
        
        echo '<div class="row">';
        
        $total_records = 0;
        foreach ($analysis as $table => $count) {
            if ($count > 0) {
                $total_records += $count;
                $table_display = str_replace('_', ' ', ucwords($table, '_'));
                
                echo '<div class="col-md-6 mb-3">';
                echo '<div class="card border-warning">';
                echo '<div class="card-body text-center">';
                echo '<i class="fas fa-database fa-2x text-warning mb-2"></i>';
                echo '<h6>' . $table_display . '</h6>';
                echo '<h4 class="text-danger">' . $count . ' record</h4>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        }
        
        echo '</div>';
        
        echo '<div class="alert alert-danger mt-4">';
        echo '<strong>Total Record yang Akan Dihapus: ' . $total_records . '</strong>';
        echo '</div>';
        
        if ($total_records > 0) {
            echo '<form method="post" action="?action=cleanup">';
            foreach ($selected_students as $student_id) {
                echo '<input type="hidden" name="selected_students[]" value="' . $student_id . '">';
            }
            
            echo '<div class="form-check mb-3">';
            echo '<input class="form-check-input" type="checkbox" id="confirm_delete" name="confirm_delete" required>';
            echo '<label class="form-check-label text-danger" for="confirm_delete">';
            echo '<strong>Saya memahami bahwa data yang dihapus TIDAK DAPAT DIKEMBALIKAN</strong>';
            echo '</label>';
            echo '</div>';
            
            echo '<div class="d-grid gap-2 d-md-flex justify-content-md-end">';
            echo '<a href="?action=scan" class="btn btn-secondary me-md-2">';
            echo '<i class="fas fa-arrow-left me-2"></i>Kembali';
            echo '</a>';
            echo '<button type="submit" class="btn btn-danger">';
            echo '<i class="fas fa-trash-alt me-2"></i>Hapus Data Sekarang';
            echo '</button>';
            echo '</div>';
            echo '</form>';
        } else {
            echo '<div class="alert alert-info">Tidak ada data yang perlu dihapus untuk mahasiswa yang dipilih.</div>';
        }
    }

} elseif ($action === 'cleanup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['confirm_delete'])) {
        echo '<div class="alert alert-danger">Konfirmasi penghapusan tidak diberikan!</div>';
    } else {
        $selected_students = $_POST['selected_students'] ?? [];
        
        if (empty($selected_students)) {
            echo '<div class="alert alert-warning">Tidak ada mahasiswa yang dipilih!</div>';
        } else {
            // Proses penghapusan
            echo '<div id="cleanup-progress">';
            echo '<h5><i class="fas fa-cog fa-spin me-2"></i>Proses Pembersihan Sedang Berlangsung...</h5>';
            echo '<div class="progress mb-3">';
            echo '<div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 50%"></div>';
            echo '</div>';
            echo '</div>';
            
            // Flush output untuk menampilkan progress
            ob_flush();
            flush();
            
            $result = cleanupStudentData($pdo, $selected_students);
            
            echo '<script>document.getElementById("cleanup-progress").style.display = "none";</script>';
            
            if ($result['success']) {
                echo '<div class="alert alert-success">';
                echo '<i class="fas fa-check-circle me-2"></i>';
                echo '<strong>Pembersihan Berhasil!</strong> ' . $result['message'];
                echo '</div>';
                
                echo '<h6>Detail Data yang Dihapus:</h6>';
                echo '<div class="row">';
                foreach ($result['deleted_count'] as $table => $count) {
                    if ($count > 0) {
                        $table_display = str_replace('_', ' ', ucwords($table, '_'));
                        echo '<div class="col-md-4 mb-2">';
                        echo '<div class="card border-success">';
                        echo '<div class="card-body text-center py-2">';
                        echo '<small>' . $table_display . '</small><br>';
                        echo '<strong class="text-success">' . $count . ' dihapus</strong>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                echo '</div>';
                
                echo '<div class="alert alert-info mt-4">';
                echo '<i class="fas fa-rocket me-2"></i>';
                echo '<strong>Sistem Siap untuk Launching!</strong> Data simulasi telah dibersihkan dengan aman.';
                echo '</div>';
                
            } else {
                echo '<div class="alert alert-danger">';
                echo '<i class="fas fa-exclamation-circle me-2"></i>';
                echo '<strong>Pembersihan Gagal!</strong> ' . $result['message'];
                echo '</div>';
            }
            
            echo '<div class="d-grid gap-2 mt-4">';
            echo '<a href="?action=scan" class="btn btn-primary">';
            echo '<i class="fas fa-refresh me-2"></i>Scan Ulang';
            echo '</a>';
            echo '</div>';
        }
    }
}

// =====================================================
// FOOTER & JAVASCRIPT
// =====================================================
?>

                    </div>
                    <div class="card-footer text-muted text-center">
                        <small>
                            <i class="fas fa-shield-alt me-1"></i>
                            Script Pembersihan Data - SIM Tugas Akhir STK Santo Yakobus | 
                            <i class="fas fa-database me-1"></i>
                            Database: <?= $dbname ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleStudent(studentId) {
            const checkbox = document.getElementById('student_' + studentId);
            const card = checkbox.closest('.mahasiswa-card');
            
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        }
        
        // Auto-check pada load
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_students[]"]');
            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    checkbox.closest('.mahasiswa-card').classList.add('selected');
                }
            });
        });
    </script>
</body>
</html>