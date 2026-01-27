<?php
// SAVE AS: modules/buku/import.php
// VERSI PERBAIKAN - Auto-detect delimiter (koma atau titik koma) + IS_REFERENSI

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
            // Read CSV file
            if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
                $row_num = 0;
                $delimiter = ','; // Default delimiter
                
                // Auto-detect delimiter from first line
                $first_line = fgets($handle);
                rewind($handle);
                
                // Count occurrences of common delimiters
                $comma_count = substr_count($first_line, ',');
                $semicolon_count = substr_count($first_line, ';');
                $tab_count = substr_count($first_line, "\t");
                
                // Choose the most common delimiter
                if ($semicolon_count > $comma_count && $semicolon_count > $tab_count) {
                    $delimiter = ';';
                } elseif ($tab_count > $comma_count && $tab_count > $semicolon_count) {
                    $delimiter = "\t";
                }
                
                // Notify user about detected delimiter
                $delimiter_name = ($delimiter == ';') ? 'titik koma (;)' : (($delimiter == "\t") ? 'tab' : 'koma (,)');
                
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                    $row_num++;
                    
                    // Skip header
                    if ($row_num == 1) continue;
                    
                    // Get data - remove BOM if exists
                    $nomor_buku = isset($row[0]) ? trim(str_replace("\xEF\xBB\xBF", '', $row[0])) : '';
                    $judul = isset($row[1]) ? trim($row[1]) : '';
                    $pengarang = isset($row[2]) ? trim($row[2]) : '';
                    $penerbit = isset($row[3]) ? trim($row[3]) : '';
                    $tahun_terbit = isset($row[4]) ? trim($row[4]) : '';
                    $stok = isset($row[5]) ? intval($row[5]) : 0;
                    // TAMBAHAN: Handle kolom referensi (Ya/Tidak)
                    $is_referensi = isset($row[6]) ? (strtolower(trim($row[6])) == 'ya' ? 1 : 0) : 0;
                    
                    // Extract year from tahun_terbit if it contains "Cet." or other text
                    if (!empty($tahun_terbit) && !is_numeric($tahun_terbit)) {
                        // Extract 4-digit year
                        if (preg_match('/(\d{4})/', $tahun_terbit, $matches)) {
                            $tahun_terbit = $matches[1];
                        } else {
                            $tahun_terbit = '';
                        }
                    }
                    
                    // Skip empty rows
                    if (empty($nomor_buku) && empty($judul)) continue;
                    
                    // Validate required fields
                    if (empty($nomor_buku) || empty($judul) || empty($pengarang)) {
                        $results[] = array(
                            'status' => 'failed',
                            'nomor' => $nomor_buku ?: '(kosong)',
                            'judul' => $judul ?: '(kosong)',
                            'message' => 'Data tidak lengkap (Nomor Buku, Judul, atau Pengarang kosong)'
                        );
                        $failed_count++;
                        continue;
                    }
                    
                    // Validate stok
                    if ($stok <= 0) {
                        $results[] = array(
                            'status' => 'failed',
                            'nomor' => $nomor_buku,
                            'judul' => $judul,
                            'message' => 'Stok harus lebih dari 0'
                        );
                        $failed_count++;
                        continue;
                    }
                    
                    // Check duplicate
                    $check = "SELECT id FROM buku WHERE nomor_buku = ?";
                    $stmt = $conn->prepare($check);
                    $stmt->bind_param("s", $nomor_buku);
                    $stmt->execute();
                    $stmt->bind_result($found_id);
                    
                    if ($stmt->fetch()) {
                        $stmt->close();
                        $results[] = array(
                            'status' => 'duplicate',
                            'nomor' => $nomor_buku,
                            'judul' => $judul,
                            'message' => 'Nomor buku sudah ada di database'
                        );
                        $duplicate_count++;
                        continue;
                    }
                    $stmt->close();
                    
                    // Insert to database - TAMBAH is_referensi
                    $query = "INSERT INTO buku (nomor_buku, judul, pengarang, penerbit, tahun_terbit, stok, stok_tersedia, is_referensi) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssssiii", $nomor_buku, $judul, $pengarang, $penerbit, $tahun_terbit, $stok, $stok, $is_referensi);
                    
                    if ($stmt->execute()) {
                        $results[] = array(
                            'status' => 'success',
                            'nomor' => $nomor_buku,
                            'judul' => $judul,
                            'message' => 'Berhasil ditambahkan' . ($is_referensi ? ' (Referensi)' : '')
                        );
                        $success_count++;
                    } else {
                        $results[] = array(
                            'status' => 'failed',
                            'nomor' => $nomor_buku,
                            'judul' => $judul,
                            'message' => 'Gagal menyimpan ke database: ' . $stmt->error
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
    <title>Import Buku CSV - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-filetype-csv me-2"></i>Import Buku dari CSV</h1>
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
                                <p>Download template CSV untuk diisi dengan data buku.</p>
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
                                    <li>Isi data buku (hapus baris contoh)</li>
                                    <li>Save As → pilih format <strong>CSV UTF-8</strong></li>
                                    <li>Upload file CSV yang sudah diisi</li>
                                </ol>
                                
                                <div class="alert alert-info mb-3">
                                    <strong>Format Kolom:</strong>
                                    <ol class="mb-0 mt-2">
                                        <li><strong>Nomor Buku</strong> (wajib)</li>
                                        <li><strong>Judul</strong> (wajib)</li>
                                        <li><strong>Pengarang</strong> (wajib)</li>
                                        <li><strong>Penerbit</strong> (opsional)</li>
                                        <li><strong>Tahun Terbit</strong> (opsional)</li>
                                        <li><strong>Stok</strong> (wajib, harus > 0)</li>
                                        <li><strong>Referensi</strong> (Ya/Tidak)</li>
                                    </ol>
                                </div>
                                
                                <div class="alert alert-warning mb-0">
                                    <strong>Tips:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Jangan ubah urutan kolom</li>
                                        <li>Nomor buku harus unik</li>
                                        <li>Baris pertama adalah header</li>
                                        <li>Tahun bisa format: "2020" atau "Cet. 2.2020"</li>
                                        <li>Referensi: isi "Ya" atau "Tidak"</li>
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
                                        <th width="15%">Nomor</th>
                                        <th width="35%">Judul</th>
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
                                        <td><code><?= htmlspecialchars($r['nomor']) ?></code></td>
                                        <td><?= htmlspecialchars($r['judul']) ?></td>
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