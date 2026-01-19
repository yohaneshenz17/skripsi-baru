<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = sanitize($_POST['nim']);
    $nama = sanitize($_POST['nama']);
    $program_studi = sanitize($_POST['program_studi']);
    $angkatan = sanitize($_POST['angkatan']);
    $no_hp = sanitize($_POST['no_hp']);
    
    // Check if NIM already exists
    $check = "SELECT id FROM mahasiswa WHERE nim = ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $stmt->bind_result($found_id);
    
    if ($stmt->fetch()) {
        $stmt->close();
        setAlert('danger', 'NIM sudah terdaftar!');
    } else {
        $stmt->close();
        
        // Handle foto upload
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload = uploadFoto($_FILES['foto'], UPLOAD_MAHASISWA);
            if ($upload['success']) {
                $foto = $upload['filename'];
            } else {
                setAlert('warning', $upload['message']);
            }
        }
        
        // Insert mahasiswa
        $query = "INSERT INTO mahasiswa (nim, nama, program_studi, angkatan, no_hp, foto) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $nim, $nama, $program_studi, $angkatan, $no_hp, $foto);
        
        if ($stmt->execute()) {
            $stmt->close();
            setAlert('success', 'Mahasiswa berhasil ditambahkan!');
            header('Location: index.php');
            exit;
        } else {
            $stmt->close();
            setAlert('danger', 'Gagal menambahkan mahasiswa!');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-plus-circle me-2"></i>Tambah Mahasiswa</h1>
                </div>

                <?php showAlert(); ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">NIM <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nim" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nama" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                                        <select class="form-select" name="program_studi" required>
                                            <option value="">Pilih...</option>
                                            <option value="S1 Kateketik">S1 Kateketik</option>
                                            <option value="S1 Teologi">S1 Teologi</option>
                                            <option value="S2 Teologi">S2 Teologi</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Angkatan <span class="text-danger">*</span></label>
                                        <select class="form-select" name="angkatan" required>
                                            <option value="">Pilih...</option>
                                            <?php for ($y = date('Y'); $y >= 2015; $y--): ?>
                                            <option value="<?= $y ?>"><?= $y ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">No. HP</label>
                                        <input type="text" class="form-control" name="no_hp" placeholder="08xx">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Foto (Opsional)</label>
                                        <input type="file" class="form-control" name="foto" accept="image/*">
                                        <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>