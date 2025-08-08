<?php
/**
 * =====================================================
 * SCRIPT PENGHAPUSAN SELEKTIF MAHASISWA
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * =====================================================
 * 
 * TUJUAN: Menghapus mahasiswa tertentu berdasarkan ID atau kriteria khusus
 * STATUS: PRODUCTION READY - SAFE & SELECTIVE
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
set_time_limit(300);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penghapusan Selektif Mahasiswa - SIM Tugas Akhir</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
        }
        .card { 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
        }
        .btn-primary { background: linear-gradient(45deg, #667eea, #764ba2); border: none; }
        .btn-danger { background: linear-gradient(45deg, #f093fb, #f5576c); border: none; }
        .btn-success { background: linear-gradient(45deg, #4facfe, #00f2fe); border: none; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-user-minus me-2"></i>
                            Penghapusan Selektif Mahasiswa
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
    exit;
}

// =====================================================
// FUNGSI PENGHAPUSAN MAHASISWA TUNGGAL
// =====================================================
function deleteSingleStudent($pdo, $mahasiswa_id) {
    try {
        $pdo->beginTransaction();
        
        $deleted_count = [];
        
        // Cek apakah mahasiswa ada
        $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE id = ?");
        $stmt->execute([$mahasiswa_id]);
        $mahasiswa = $stmt->fetch();
        
        if (!$mahasiswa) {
            return ['success' => false, 'message' => 'Mahasiswa dengan ID ' . $mahasiswa_id . ' tidak ditemukan!'];
        }
        
        // Hapus data secara berurutan (child ke parent)
        
        // 1. Notifikasi
        $stmt = $pdo->prepare("DELETE FROM notifikasi WHERE untuk_role = 'mahasiswa' AND user_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['notifikasi'] = $stmt->rowCount();
        
        // 2. Penilaian seminar skripsi
        $stmt = $pdo->prepare("DELETE FROM penilaian_seminar_skripsi WHERE mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['penilaian_seminar_skripsi'] = $stmt->rowCount();
        
        // 3. Seminar skripsi
        $stmt = $pdo->prepare("DELETE FROM seminar_skripsi_mahasiswa WHERE mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['seminar_skripsi_mahasiswa'] = $stmt->rowCount();
        
        // 4. Publikasi tugas akhir (via proposal)
        $stmt = $pdo->prepare("DELETE pta FROM publikasi_tugas_akhir pta
                              INNER JOIN proposal_mahasiswa pm ON pta.proposal_mahasiswa_id = pm.id 
                              WHERE pm.mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['publikasi_tugas_akhir'] = $stmt->rowCount();
        
        // 5. Permohonan izin penelitian (via proposal)
        $stmt = $pdo->prepare("DELETE pir FROM permohonan_izin_penelitian pir
                              INNER JOIN proposal_mahasiswa pm ON pir.proposal_mahasiswa_id = pm.id 
                              WHERE pm.mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['permohonan_izin_penelitian'] = $stmt->rowCount();
        
        // 6. Penelitian legacy (via proposal)
        $stmt = $pdo->prepare("DELETE p FROM penelitian p
                              INNER JOIN proposal_mahasiswa pm ON p.proposal_mahasiswa_id = pm.id 
                              WHERE pm.mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['penelitian'] = $stmt->rowCount();
        
        // 7. Konsultasi legacy (via proposal)
        $stmt = $pdo->prepare("DELETE k FROM konsultasi k
                              INNER JOIN proposal_mahasiswa pm ON k.proposal_mahasiswa_id = pm.id 
                              WHERE pm.mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['konsultasi'] = $stmt->rowCount();
        
        // 8. Penilaian seminar proposal
        $stmt = $pdo->prepare("DELETE FROM penilaian_seminar_proposal WHERE mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['penilaian_seminar_proposal'] = $stmt->rowCount();
        
        // 9. Seminar proposal
        $stmt = $pdo->prepare("DELETE FROM seminar_proposal_mahasiswa WHERE mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['seminar_proposal_mahasiswa'] = $stmt->rowCount();
        
        // 10. Jurnal bimbingan (via proposal)
        $stmt = $pdo->prepare("DELETE jb FROM jurnal_bimbingan jb
                              INNER JOIN proposal_mahasiswa pm ON jb.proposal_id = pm.id 
                              WHERE pm.mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['jurnal_bimbingan'] = $stmt->rowCount();
        
        // 11. Proposal mahasiswa
        $stmt = $pdo->prepare("DELETE FROM proposal_mahasiswa WHERE mahasiswa_id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['proposal_mahasiswa'] = $stmt->rowCount();
        
        // 12. Mahasiswa (terakhir)
        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id = ?");
        $stmt->execute([$mahasiswa_id]);
        $deleted_count['mahasiswa'] = $stmt->rowCount();
        
        $pdo->commit();
        
        return [
            'success' => true,
            'message' => 'Mahasiswa berhasil dihapus!',
            'deleted_student' => $mahasiswa,
            'deleted_count' => $deleted_count
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// =====================================================
// PROSES BERDASARKAN INPUT
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_by_id') {
        $mahasiswa_id = (int)$_POST['mahasiswa_id'];
        
        if ($mahasiswa_id <= 0) {
            echo '<div class="alert alert-danger">ID mahasiswa tidak valid!</div>';
        } else {
            $result = deleteSingleStudent($pdo, $mahasiswa_id);
            
            if ($result['success']) {
                echo '<div class="alert alert-success">';
                echo '<i class="fas fa-check-circle me-2"></i>';
                echo '<strong>Berhasil!</strong> ' . $result['message'];
                echo '</div>';
                
                echo '<div class="card border-success mb-3">';
                echo '<div class="card-header bg-success text-white">';
                echo '<i class="fas fa-user me-2"></i>Mahasiswa yang Dihapus';
                echo '</div>';
                echo '<div class="card-body">';
                echo '<strong>Nama:</strong> ' . htmlspecialchars($result['deleted_student']['nama']) . '<br>';
                echo '<strong>NIM:</strong> ' . htmlspecialchars($result['deleted_student']['nim']) . '<br>';
                echo '<strong>Email:</strong> ' . htmlspecialchars($result['deleted_student']['email']);
                echo '</div>';
                echo '</div>';
                
                echo '<h6>Detail Data yang Dihapus:</h6>';
                echo '<div class="row">';
                foreach ($result['deleted_count'] as $table => $count) {
                    if ($count > 0) {
                        $table_display = str_replace('_', ' ', ucwords($table, '_'));
                        echo '<div class="col-md-4 mb-2">';
                        echo '<div class="badge bg-danger me-1 mb-1">' . $table_display . ': ' . $count . '</div>';
                        echo '</div>';
                    }
                }
                echo '</div>';
                
            } else {
                echo '<div class="alert alert-danger">';
                echo '<i class="fas fa-exclamation-circle me-2"></i>';
                echo '<strong>Gagal!</strong> ' . $result['message'];
                echo '</div>';
            }
        }
    }
    
    elseif ($action === 'search_students') {
        $search_term = $_POST['search_term'] ?? '';
        
        if (strlen($search_term) < 2) {
            echo '<div class="alert alert-warning">Masukkan minimal 2 karakter untuk pencarian!</div>';
        } else {
            $sql = "SELECT * FROM mahasiswa 
                    WHERE nama LIKE ? OR nim LIKE ? OR email LIKE ?
                    ORDER BY nama";
            
            $search_param = '%' . $search_term . '%';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$search_param, $search_param, $search_param]);
            $students = $stmt->fetchAll();
            
            if (empty($students)) {
                echo '<div class="alert alert-info">Tidak ada mahasiswa yang ditemukan dengan kriteria: <strong>' . htmlspecialchars($search_term) . '</strong></div>';
            } else {
                echo '<h6>Hasil Pencarian (' . count($students) . ' mahasiswa):</h6>';
                echo '<div class="table-responsive">';
                echo '<table class="table table-hover">';
                echo '<thead class="table-light">';
                echo '<tr><th>ID</th><th>Nama</th><th>NIM</th><th>Email</th><th>Aksi</th></tr>';
                echo '</thead><tbody>';
                
                foreach ($students as $student) {
                    echo '<tr>';
                    echo '<td>' . $student['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($student['nama']) . '</td>';
                    echo '<td>' . htmlspecialchars($student['nim']) . '</td>';
                    echo '<td>' . htmlspecialchars($student['email']) . '</td>';
                    echo '<td>';
                    echo '<button class="btn btn-sm btn-danger" onclick="deleteStudent(' . $student['id'] . ', \'' . htmlspecialchars($student['nama']) . '\')">';
                    echo '<i class="fas fa-trash"></i> Hapus';
                    echo '</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
                echo '</div>';
            }
        }
    }
}

// =====================================================
// TAMPILAN FORM
// =====================================================
?>

<!-- Form Pencarian Mahasiswa -->
<div class="mb-4">
    <h5><i class="fas fa-search me-2"></i>Cari Mahasiswa</h5>
    <form method="post" class="row g-3">
        <input type="hidden" name="action" value="search_students">
        <div class="col-md-8">
            <input type="text" class="form-control" name="search_term" 
                   placeholder="Masukkan nama, NIM, atau email mahasiswa..." 
                   value="<?= htmlspecialchars($_POST['search_term'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search me-2"></i>Cari
            </button>
        </div>
    </form>
</div>

<hr>

<!-- Form Penghapusan Berdasarkan ID -->
<div class="mb-4">
    <h5><i class="fas fa-user-minus me-2"></i>Hapus Berdasarkan ID</h5>
    <form method="post" class="row g-3" onsubmit="return confirmDelete()">
        <input type="hidden" name="action" value="delete_by_id">
        <div class="col-md-8">
            <input type="number" class="form-control" name="mahasiswa_id" 
                   placeholder="Masukkan ID mahasiswa yang akan dihapus..." 
                   min="1" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-danger w-100">
                <i class="fas fa-trash-alt me-2"></i>Hapus Mahasiswa
            </button>
        </div>
    </form>
</div>

<hr>

<?php
// Ambil mahasiswa simulasi yang masih ada di database
$sql_simulation = "SELECT * FROM mahasiswa 
                   WHERE (nim LIKE '123456%' OR nama LIKE '%Contoh%' OR nama LIKE '%Agus Bumagi%' OR nama LIKE '%Simulasi%')
                   ORDER BY id";

$stmt_simulation = $pdo->prepare($sql_simulation);
$stmt_simulation->execute();
$simulation_students = $stmt_simulation->fetchAll();
?>

<!-- Preset Mahasiswa Simulasi (Dynamic) -->
<div class="mb-4">
    <h5><i class="fas fa-magic me-2"></i>Preset Mahasiswa Simulasi</h5>
    
    <?php if (!empty($simulation_students)): ?>
        <p class="text-muted">Klik tombol di bawah untuk langsung menghapus mahasiswa simulasi yang teridentifikasi:</p>
        
        <div class="row">
            <?php foreach ($simulation_students as $student): ?>
                <div class="col-md-3 mb-2">
                    <button class="btn btn-outline-danger w-100" onclick="deleteStudent(<?= $student['id'] ?>, '<?= htmlspecialchars($student['nama']) ?>')">
                        <i class="fas fa-user-minus"></i><br>
                        <small>ID: <?= $student['id'] ?><br><?= htmlspecialchars($student['nama']) ?></small>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Bagus!</strong> Tidak ada mahasiswa simulasi yang tersisa di database.
            Sistem sudah bersih dan siap untuk produksi!
        </div>
    <?php endif; ?>
</div>

                    </div>
                    <div class="card-footer text-muted text-center">
                        <small>
                            <i class="fas fa-shield-alt me-1"></i>
                            Script Penghapusan Selektif - SIM Tugas Akhir STK Santo Yakobus
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete() {
            return confirm('PERINGATAN: Mahasiswa dan SEMUA data terkaitnya akan dihapus PERMANEN!\n\nLanjutkan penghapusan?');
        }
        
        function deleteStudent(id, name) {
            if (confirm('KONFIRMASI PENGHAPUSAN\n\nMahasiswa: ' + name + ' (ID: ' + id + ')\n\nSemua data terkait akan dihapus PERMANEN!\n\nLanjutkan?')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_by_id">
                    <input type="hidden" name="mahasiswa_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>