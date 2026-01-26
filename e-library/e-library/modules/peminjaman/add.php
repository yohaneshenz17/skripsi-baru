<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_peminjam = sanitize($_POST['jenis_peminjam']);
    $peminjam_id = intval($_POST['peminjam_id']);
    $buku_ids = $_POST['buku_id']; // Array of book IDs
    
    // Validasi input
    if (empty($buku_ids)) {
        setAlert('danger', 'Pilih minimal 1 buku!');
    } elseif (count($buku_ids) > 3) {
        setAlert('danger', 'Maksimal 3 buku per peminjaman!');
    } else {
        // Cek peminjaman aktif
        $check = "SELECT COUNT(*) as total FROM peminjaman 
                  WHERE jenis_peminjam = ? AND peminjam_id = ? 
                  AND status IN ('dipinjam', 'diperpanjang', 'terlambat')";
        $stmt = $conn->prepare($check);
        $stmt->bind_param("si", $jenis_peminjam, $peminjam_id);
        $stmt->execute();
        $stmt->bind_result($current_total);
        $stmt->fetch();
        $stmt->close();
        
        $total_pinjam = $current_total + count($buku_ids);
        
        if ($total_pinjam > 3) {
            setAlert('danger', 'Peminjam sudah memiliki ' . $current_total . ' buku dipinjam. Maksimal total 3 buku!');
        } else {
            $success = true;
            $tanggal_pinjam = sanitize($_POST['tanggal_pinjam']); // Admin tentukan tanggal
            $tanggal_jatuh_tempo = date('Y-m-d', strtotime($tanggal_pinjam . ' +14 days'));
            
            foreach ($buku_ids as $buku_id) {
                // Cek stok
                $check_stok = "SELECT stok_tersedia, judul FROM buku WHERE id = ?";
                $stmt2 = $conn->prepare($check_stok);
                $stmt2->bind_param("i", $buku_id);
                $stmt2->execute();
                $stmt2->bind_result($stok, $judul_buku);
                $stmt2->fetch();
                $stmt2->close();
                
                if ($stok <= 0) {
                    setAlert('danger', 'Stok buku "' . $judul_buku . '" tidak tersedia!');
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
                    setAlert('danger', 'Gagal menyimpan peminjaman!');
                    break;
                }
                $stmt3->close();
                
                // Update stok
                $update = "UPDATE buku SET stok_tersedia = stok_tersedia - 1 WHERE id = ?";
                $stmt4 = $conn->prepare($update);
                $stmt4->bind_param("i", $buku_id);
                $stmt4->execute();
                $stmt4->close();
            }
            
            if ($success) {
                setAlert('success', 'Peminjaman berhasil ditambahkan! ' . count($buku_ids) . ' buku dipinjam.');
                header('Location: index.php');
                exit;
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
    <title>Tambah Peminjaman - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.8rem 1.5rem;
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            font-size: 1.3rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .navbar-brand i { color: #ffd700; }
        .navbar .nav-link { color: rgba(255,255,255,0.95) !important; }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            width: 250px;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 2px 0 15px rgba(0,0,0,0.08);
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
        .sidebar-heading {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1.5px;
            padding: 1rem 1.2rem 0.5rem;
            color: #64748b;
            background: linear-gradient(90deg, transparent 0%, rgba(102, 126, 234, 0.1) 50%, transparent 100%);
            margin-top: 0.5rem;
            position: relative;
        }
        .sidebar-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 1.2rem;
            right: 1.2rem;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, #667eea 50%, transparent 100%);
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: #475569;
            padding: 0.75rem 1.2rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            margin: 0.2rem 0;
        }
        .sidebar .nav-link i {
            width: 22px;
            font-size: 1.1rem;
            margin-right: 0.7rem;
        }
        .sidebar .nav-link:hover {
            color: #667eea;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
            border-left-color: #667eea;
            transform: translateX(3px);
        }
        .sidebar .nav-link.active {
            color: #667eea;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, rgba(102, 126, 234, 0.05) 100%);
            border-left-color: #667eea;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            margin-top: 56px;
            padding: 1.5rem;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #f093fb;
        }
        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        /* Card */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        /* Form */
        .form-label {
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            padding: 0.6rem 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #f093fb;
            box-shadow: 0 0 0 0.2rem rgba(240, 147, 251, 0.15);
        }
        
        /* Select2 Custom */
        .select2-container--bootstrap-5 .select2-selection {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            min-height: 45px;
        }
        
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: #f093fb;
            box-shadow: 0 0 0 0.2rem rgba(240, 147, 251, 0.15);
        }
        
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #f093fb;
            border: none;
            color: white;
            border-radius: 6px;
            padding: 0.35rem 0.7rem;
        }
        
        /* Button */
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #64748b;
            border: none;
        }
        
        .btn-secondary:hover {
            background: #475569;
        }
        
        /* Alert */
        .alert {
            border: none;
            border-radius: 10px;
            border-left: 4px solid;
        }
        
        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
            border-left: 4px solid #3b82f6;
            border-radius: 10px;
            padding: 1.2rem;
        }
        
        .info-box ul {
            margin-bottom: 0;
            padding-left: 1.2rem;
        }
        
        .info-box li {
            margin-bottom: 0.4rem;
        }
        
        /* Selected Books Display */
        #selectedBooksDisplay {
            display: none;
            margin-top: 1rem;
        }
        
        .selected-book-item {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .selected-book-item:hover {
            border-color: #f093fb;
        }
        
        .badge-count {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 0.35rem 0.7rem;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">
                <i class="bi bi-book-fill me-2"></i>
                <strong>E-Library STK Yakobus</strong>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= $_SESSION['admin_username'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>change_password.php"><i class="bi bi-key me-2"></i>Ganti Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <nav class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>dashboard.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li class="sidebar-heading">DATA MASTER</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/buku/index.php">
                    <i class="bi bi-book"></i>Data Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/mahasiswa/index.php">
                    <i class="bi bi-people"></i>Data Mahasiswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/dosen/index.php">
                    <i class="bi bi-person-badge"></i>Data Dosen
                </a>
            </li>
            <li class="sidebar-heading">TRANSAKSI</li>
            <li class="nav-item">
                <a class="nav-link active" href="<?= BASE_URL ?>modules/peminjaman/index.php">
                    <i class="bi bi-arrow-left-right"></i>Peminjaman Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/perpanjangan/index.php">
                    <i class="bi bi-arrow-clockwise"></i>Perpanjangan Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/pengembalian/index.php">
                    <i class="bi bi-arrow-return-left"></i>Pengembalian Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/denda/index.php">
                    <i class="bi bi-cash-stack"></i>Manajemen Denda
                </a>
            </li>
            <li class="sidebar-heading">LAYANAN</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/surat_keterangan/index.php">
                    <i class="bi bi-file-earmark-text"></i>Surat Keterangan
                </a>
            </li>
            <li class="sidebar-heading">LAPORAN & UTILITAS</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/laporan/index.php">
                    <i class="bi bi-file-bar-graph"></i>Laporan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/backup/index.php">
                    <i class="bi bi-cloud-download"></i>Backup Database
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1><i class="bi bi-plus-circle me-2"></i>Tambah Peminjaman Buku</h1>
        </div>

        <?php showAlert(); ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" id="formPeminjaman">
                            <!-- Input Tanggal Peminjaman -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Tanggal Peminjaman <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_pinjam" id="tanggalPinjam" 
                                           value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        Jatuh Tempo
                                    </label>
                                    <input type="text" class="form-control" id="tanggalJatuhTempo" 
                                           value="<?= date('d M Y', strtotime('+14 days')) ?>" readonly>
                                    <small class="text-muted">💡 Otomatis +14 hari dari tanggal peminjaman</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-person-check me-1"></i>
                                        Jenis Peminjam <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="jenis_peminjam" id="jenisPeminjam" required>
                                        <option value="">-- Pilih Jenis Peminjam --</option>
                                        <option value="mahasiswa">👨‍🎓 Mahasiswa</option>
                                        <option value="dosen">👨‍🏫 Dosen</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-person-fill me-1"></i>
                                        Nama Peminjam <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="peminjam_id" id="peminjamSelect" required disabled>
                                        <option value="">-- Pilih jenis peminjam terlebih dahulu --</option>
                                    </select>
                                    <small class="text-muted">💡 Ketik untuk mencari nama atau NIM/NUPTK</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-book-fill me-1"></i>
                                    Pilih Buku <span class="text-danger">*</span>
                                    <span class="badge-count" id="bukuCounter">0/3</span>
                                </label>
                                <select class="form-select" name="buku_id[]" id="bukuSelect" multiple required>
                                    <!-- Options will be loaded dynamically -->
                                </select>
                                <small class="text-muted">💡 Ketik untuk mencari judul atau nomor buku | Maksimal 3 buku</small>
                            </div>
                            
                            <!-- Selected Books Display -->
                            <div id="selectedBooksDisplay"></div>
                            
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan Peminjaman
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="info-box">
                    <h5 class="mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Informasi Peminjaman
                    </h5>
                    <ul>
                        <li><strong>Durasi:</strong> 14 hari dari tanggal pinjam</li>
                        <li><strong>Maksimal:</strong> 3 buku per peminjam</li>
                        <li><strong>Denda:</strong> Rp 1.000 per hari keterlambatan</li>
                        <li><strong>Perpanjangan:</strong> Maksimal 1x (14 hari tambahan)</li>
                        <li><strong>Stok:</strong> Hanya buku dengan stok tersedia yang bisa dipinjam</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Perhatian!</strong><br>
                    Pastikan data peminjam dan buku sudah benar sebelum menyimpan.
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 for Peminjam
            $('#peminjamSelect').select2({
                theme: 'bootstrap-5',
                placeholder: '🔍 Ketik nama atau NIM/NUPTK...',
                allowClear: true,
                width: '100%'
            });
            
            // Initialize Select2 for Buku
            $('#bukuSelect').select2({
                theme: 'bootstrap-5',
                placeholder: '🔍 Ketik judul atau nomor buku...',
                allowClear: true,
                width: '100%',
                maximumSelectionLength: 3,
                language: {
                    maximumSelected: function() {
                        return "Maksimal 3 buku dapat dipilih!";
                    }
                }
            });
            
            // Load books on page load
            loadBuku();
            
            // Handle jenis peminjam change
            $('#jenisPeminjam').on('change', function() {
                const jenis = $(this).val();
                if (jenis) {
                    loadPeminjam(jenis);
                    $('#peminjamSelect').prop('disabled', false);
                } else {
                    $('#peminjamSelect').html('<option value="">-- Pilih jenis peminjam terlebih dahulu --</option>').prop('disabled', true);
                }
            });
            
            // Track selected books count
            $('#bukuSelect').on('change', function() {
                const count = $(this).val() ? $(this).val().length : 0;
                $('#bukuCounter').text(count + '/3');
                
                if (count >= 3) {
                    $('#bukuCounter').removeClass('badge-count').addClass('badge bg-warning text-dark');
                } else {
                    $('#bukuCounter').removeClass('badge bg-warning text-dark').addClass('badge-count');
                }
            });
            
            // Function to load peminjam
            function loadPeminjam(jenis) {
                $.ajax({
                    url: 'get_peminjam.php',
                    type: 'GET',
                    data: { jenis: jenis },
                    dataType: 'json',
                    success: function(response) {
                        $('#peminjamSelect').empty();
                        $('#peminjamSelect').append('<option value="">-- Pilih Peminjam --</option>');
                        
                        if (response.success && response.data.length > 0) {
                            $.each(response.data, function(index, item) {
                                const identifier = jenis === 'mahasiswa' ? item.nim : item.nuptk;
                                const text = item.nama + ' (' + identifier + ')';
                                $('#peminjamSelect').append(new Option(text, item.id, false, false));
                            });
                        } else {
                            $('#peminjamSelect').append('<option value="">Tidak ada data</option>');
                        }
                    },
                    error: function() {
                        alert('Gagal memuat data peminjam!');
                    }
                });
            }
            
            // Function to load buku
            function loadBuku() {
                $.ajax({
                    url: 'get_buku.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $('#bukuSelect').empty();
                        
                        if (response.success && response.data.length > 0) {
                            $.each(response.data, function(index, item) {
                                const text = item.judul + ' (' + item.nomor_buku + ') - Tersedia: ' + item.stok_tersedia;
                                $('#bukuSelect').append(new Option(text, item.id, false, false));
                            });
                        } else {
                            $('#bukuSelect').append('<option value="">Tidak ada buku tersedia</option>');
                        }
                    },
                    error: function() {
                        alert('Gagal memuat data buku!');
                    }
                });
            }
            
            // Auto-update tanggal jatuh tempo saat tanggal peminjaman berubah
            $('#tanggalPinjam').on('change', function() {
                const tanggalPinjam = new Date($(this).val());
                const tanggalJatuhTempo = new Date(tanggalPinjam);
                tanggalJatuhTempo.setDate(tanggalJatuhTempo.getDate() + 14);
                
                // Format tanggal Indonesia
                const options = { day: 'numeric', month: 'short', year: 'numeric' };
                const formatted = tanggalJatuhTempo.toLocaleDateString('id-ID', options);
                
                $('#tanggalJatuhTempo').val(formatted);
            });
        });
    </script>
</body>
</html>