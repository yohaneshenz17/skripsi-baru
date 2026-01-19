<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_peminjam = sanitize($_POST['jenis_peminjam']);
    $peminjam_id = intval($_POST['peminjam_id']);
    $buku_ids = $_POST['buku_id']; // Array of book IDs
    
    // Validasi maksimal 3 buku
    if (count($buku_ids) > 3) {
        setAlert('danger', 'Maksimal 3 buku per peminjaman!');
    } else {
        // Cek peminjaman aktif
        $check = "SELECT COUNT(*) as total FROM peminjaman 
                  WHERE jenis_peminjam = ? AND peminjam_id = ? 
                  AND status IN ('dipinjam', 'diperpanjang', 'terlambat')";
        $stmt = $conn->prepare($check);
        $stmt->bind_param("si", $jenis_peminjam, $peminjam_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $total_pinjam = $row['total'] + count($buku_ids);
        
        if ($total_pinjam > 3) {
            setAlert('danger', 'Peminjam sudah memiliki ' . $row['total'] . ' buku dipinjam. Maksimal total 3 buku!');
        } else {
            $success = true;
            $tanggal_pinjam = date('Y-m-d');
            $tanggal_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));
            
            foreach ($buku_ids as $buku_id) {
                // Cek stok
                $check_stok = "SELECT stok_tersedia FROM buku WHERE id = ?";
                $stmt2 = $conn->prepare($check_stok);
                $stmt2->bind_param("i", $buku_id);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $buku = $result2->fetch_assoc();
                
                if ($buku['stok_tersedia'] <= 0) {
                    setAlert('danger', 'Stok buku tidak tersedia!');
                    $success = false;
                    break;
                }
                
                // Insert peminjaman
                $kode = generateKodePeminjaman();
                $query = "INSERT INTO peminjaman (kode_peminjaman, jenis_peminjam, peminjam_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo, status) 
                          VALUES (?, ?, ?, ?, ?, ?, 'dipinjam')";
                $stmt3 = $conn->prepare($query);
                $stmt3->bind_param("ssiiss", $kode, $jenis_peminjam, $peminjam_id, $buku_id, $tanggal_pinjam, $tanggal_jatuh_tempo);
                
                if (!$stmt3->execute()) {
                    $success = false;
                    break;
                }
                
                // Update stok
                $update = "UPDATE buku SET stok_tersedia = stok_tersedia - 1 WHERE id = ?";
                $stmt4 = $conn->prepare($update);
                $stmt4->bind_param("i", $buku_id);
                $stmt4->execute();
            }
            
            if ($success) {
                setAlert('success', 'Peminjaman berhasil ditambahkan!');
                header('Location: index.php');
                exit;
            }
        }
    }
}

// Get mahasiswa dan dosen
$mahasiswa = $conn->query("SELECT * FROM mahasiswa ORDER BY nama");
$dosen = $conn->query("SELECT * FROM dosen ORDER BY nama");
$buku = $conn->query("SELECT * FROM buku WHERE stok_tersedia > 0 ORDER BY judul");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Peminjaman - E-Library STK Yakobus</title>
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
                    <h1 class="h2"><i class="bi bi-plus-circle me-2"></i>Tambah Peminjaman</h1>
                </div>

                <?php showAlert(); ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" id="formPeminjaman">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Jenis Peminjam <span class="text-danger">*</span></label>
                                        <select class="form-select" name="jenis_peminjam" id="jenisPeminjam" required>
                                            <option value="">Pilih...</option>
                                            <option value="mahasiswa">Mahasiswa</option>
                                            <option value="dosen">Dosen</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Peminjam <span class="text-danger">*</span></label>
                                        <select class="form-select" name="peminjam_id" id="peminjamId" required>
                                            <option value="">Pilih jenis peminjam dulu...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Pilih Buku (Maksimal 3) <span class="text-danger">*</span></label>
                                <div class="row">
                                    <?php while ($b = $buku->fetch_assoc()): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input buku-checkbox" type="checkbox" name="buku_id[]" value="<?= $b['id'] ?>" id="buku<?= $b['id'] ?>">
                                            <label class="form-check-label" for="buku<?= $b['id'] ?>">
                                                <strong><?= $b['judul'] ?></strong><br>
                                                <small class="text-muted"><?= $b['pengarang'] ?> | Tersedia: <?= $b['stok_tersedia'] ?></small>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <small class="text-danger" id="errorBuku"></small>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Informasi:</strong>
                                <ul class="mb-0">
                                    <li>Maksimal 3 buku per peminjaman</li>
                                    <li>Durasi peminjaman: 7 hari</li>
                                    <li>Denda keterlambatan: Rp 1.000/hari</li>
                                    <li>Perpanjangan maksimal 1x (7 hari tambahan)</li>
                                </ul>
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
    <script>
        // Data peminjam
        const mahasiswa = <?= json_encode($mahasiswa->fetch_all(MYSQLI_ASSOC)) ?>;
        const dosen = <?= json_encode($dosen->fetch_all(MYSQLI_ASSOC)) ?>;
        
        // Update peminjam dropdown
        document.getElementById('jenisPeminjam').addEventListener('change', function() {
            const select = document.getElementById('peminjamId');
            select.innerHTML = '<option value="">Pilih...</option>';
            
            const data = this.value === 'mahasiswa' ? mahasiswa : dosen;
            const idField = this.value === 'mahasiswa' ? 'nim' : 'nuptk';
            
            data.forEach(item => {
                select.innerHTML += `<option value="${item.id}">${item.nama} (${item[idField]})</option>`;
            });
        });
        
        // Limit checkbox to 3
        const checkboxes = document.querySelectorAll('.buku-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checked = document.querySelectorAll('.buku-checkbox:checked');
                if (checked.length > 3) {
                    this.checked = false;
                    document.getElementById('errorBuku').textContent = 'Maksimal 3 buku!';
                } else {
                    document.getElementById('errorBuku').textContent = '';
                }
            });
        });
    </script>
</body>
</html>
