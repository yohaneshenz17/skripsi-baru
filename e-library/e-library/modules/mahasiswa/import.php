<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

$results = array();
$success_count = 0;
$failed_count = 0;
$duplicate_count = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        setAlert('danger', 'Gagal mengupload file!');
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($ext !== 'csv') {
            setAlert('danger', 'Format file harus CSV (.csv)!');
        } else {
            if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                $row_num = 0;
                $delimiter = ',';
                
                // Auto-detect delimiter
                $first_line = fgets($handle);
                rewind($handle);
                
                $comma_count = substr_count($first_line, ',');
                $semicolon_count = substr_count($first_line, ';');
                $tab_count = substr_count($first_line, "\t");
                
                if ($semicolon_count > $comma_count && $semicolon_count > $tab_count) {
                    $delimiter = ';';
                } elseif ($tab_count > $comma_count && $tab_count > $semicolon_count) {
                    $delimiter = "\t";
                }
                
                $delimiter_name = ($delimiter == ';') ? 'titik koma (;)' : (($delimiter == "\t") ? 'tab' : 'koma (,)');
                
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    $row_num++;
                    
                    if ($row_num == 1) continue;
                    
                    $nim = isset($row[0]) ? trim(str_replace("\xEF\xBB\xBF", '', $row[0])) : '';
                    $nama = isset($row[1]) ? trim($row[1]) : '';
                    $program_studi = isset($row[2]) ? trim($row[2]) : '';
                    $angkatan = isset($row[3]) ? trim($row[3]) : '';
                    $no_hp = isset($row[4]) ? trim($row[4]) : '';
                    
                    if (empty($nim) && empty($nama)) continue;
                    
                    if (empty($nim) || empty($nama) || empty($program_studi) || empty($angkatan)) {
                        $results[] = array(
                            'status' => 'failed',
                            'nim' => $nim ?: '(kosong)',
                            'nama' => $nama ?: '(kosong)',
                            'message' => 'Data tidak lengkap (NIM, Nama, Program Studi, atau Angkatan kosong)'
                        );
                        $failed_count++;
                        continue;
                    }
                    
                    // Check duplicate
                    $check = "SELECT id FROM mahasiswa WHERE nim = ?";
                    $stmt = $conn->prepare($check);
                    $stmt->bind_param("s", $nim);
                    $stmt->execute();
                    $stmt->bind_result($found_id);
                    
                    if ($stmt->fetch()) {
                        $stmt->close();
                        $results[] = array(
                            'status' => 'duplicate',
                            'nim' => $nim,
                            'nama' => $nama,
                            'message' => 'NIM sudah ada di database'
                        );
                        $duplicate_count++;
                        continue;
                    }
                    $stmt->close();
                    
                    // Insert to database
                    $query = "INSERT INTO mahasiswa (nim, nama, program_studi, angkatan, no_hp) 
                              VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssss", $nim, $nama, $program_studi, $angkatan, $no_hp);
                    
                    if ($stmt->execute()) {
                        $results[] = array(
                            'status' => 'success',
                            'nim' => $nim,
                            'nama' => $nama,
                            'message' => 'Berhasil ditambahkan'
                        );
                        $success_count++;
                    } else {
                        $results[] = array(
                            'status' => 'failed',
                            'nim' => $nim,
                            'nama' => $nama,
                            'message' => 'Gagal menyimpan: ' . $stmt->error
                        );
                        $failed_count++;
                    }
                    $stmt->close();
                }
                
                fclose($handle);
                
                $summary = "Import selesai! Delimiter: $delimiter_name | Berhasil: $success_count, Gagal: $failed_count, Duplikat: $duplicate_count";
                setAlert('info', $summary);
            } else {
                setAlert('danger', 'Gagal membaca file CSV!');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Mahasiswa CSV - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-filetype-csv me-2"></i>Import Mahasiswa dari CSV</h1>
                    <div class="btn-toolbar">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                    </div>
                </div>

                <?php showAlert(); ?>

                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Fitur Auto-Detect:</strong> Sistem otomatis mendeteksi pemisah (koma, titik koma, atau tab)
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Upload File CSV</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih File CSV</label>
                                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                                        <small class="text-muted">Format: .csv | Pemisah: koma (,) atau titik koma (;)</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload me-2"></i>Upload & Import
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="bi bi-download me-2"></i>Download Template</h5>
                            </div>
                            <div class="card-body">
                                <p>Download template CSV untuk diisi dengan data mahasiswa.</p>
                                <a href="template_csv.php" class="btn btn-success" download>
                                    <i class="bi bi-filetype-csv me-2"></i>Download Template CSV
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Cara Menggunakan</h5>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li>Download template CSV</li>
                                    <li>Buka dengan Excel atau Google Sheets</li>
                                    <li>Isi data mahasiswa (hapus baris contoh)</li>
                                    <li>Save As → pilih format <strong>CSV UTF-8</strong></li>
                                    <li>Upload file CSV yang sudah diisi</li>
                                </ol>
                                
                                <div class="alert alert-info mb-3">
                                    <strong>Format Kolom:</strong>
                                    <ol class="mb-0 mt-2">
                                        <li><strong>NIM</strong> (wajib)</li>
                                        <li><strong>Nama</strong> (wajib)</li>
                                        <li><strong>Program Studi</strong> (wajib)</li>
                                        <li><strong>Angkatan</strong> (wajib)</li>
                                        <li><strong>No. HP</strong> (opsional)</li>
                                    </ol>
                                </div>
                                
                                <div class="alert alert-warning mb-0">
                                    <strong>Program Studi:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>S1 Pendidikan Keagamaan Katolik</li>
                                        <li>S1 Pendidikan Guru Sekolah Dasar</li>
                                        <li>Profesi Pendidikan Guru PAK</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($results)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Hasil Import Detail</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Ringkasan:</strong><br>
                            ✅ Berhasil: <strong class="text-success"><?= $success_count ?></strong> | 
                            ❌ Gagal: <strong class="text-danger"><?= $failed_count ?></strong> | 
                            ⚠️ Duplikat: <strong class="text-warning"><?= $duplicate_count ?></strong>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th width="10%">Status</th>
                                        <th width="15%">NIM</th>
                                        <th width="35%">Nama</th>
                                        <th width="40%">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $r): ?>
                                    <tr>
                                        <td>
                                            <?php if ($r['status'] == 'success'): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> OK</span>
                                            <?php elseif ($r['status'] == 'duplicate'): ?>
                                                <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Duplikat</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Gagal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?= htmlspecialchars($r['nim']) ?></code></td>
                                        <td><?= htmlspecialchars($r['nama']) ?></td>
                                        <td><small><?= htmlspecialchars($r['message']) ?></small></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>