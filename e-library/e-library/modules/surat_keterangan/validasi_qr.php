<?php
/**
 * Halaman Validasi QR Code Surat Keterangan
 * Halaman ini bersifat PUBLIK (tidak perlu login)
 */

require_once 'config.php';

$pageTitle = "Validasi Surat Keterangan";

// Jika ada parameter QR data
$qr_data = $_GET['qr'] ?? '';
$hasil_validasi = null;

if ($qr_data) {
    $hasil_validasi = validasiSuratByQR($qr_data);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - STK Santo Yakobus</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .validation-card {
            max-width: 700px;
            margin: 50px auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .header-logo {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px 10px 0 0;
        }
        .header-logo img {
            height: 80px;
        }
        .header-logo h4 {
            margin-top: 10px;
            color: #333;
            font-weight: bold;
        }
        .valid-badge {
            font-size: 1.5rem;
            padding: 10px 30px;
        }
        .qr-scanner-area {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="validation-card">
        <div class="header-logo">
            <img src="<?php echo SK_ASSETS_PATH; ?>stk.png" alt="Logo STK">
            <h4>Sekolah Tinggi Katolik Santo Yakobus Merauke</h4>
            <h5>Validasi Surat Keterangan Bebas Perpustakaan</h5>
        </div>
        
        <div class="card-body qr-scanner-area">
            <?php if (!$qr_data): ?>
                <!-- Form Input Manual atau Scan -->
                <div class="text-center mb-4">
                    <i class="fas fa-qrcode fa-5x text-primary"></i>
                    <h5 class="mt-3">Validasi Keaslian Surat Keterangan</h5>
                    <p class="text-muted">Scan QR Code pada surat atau masukkan data manual</p>
                </div>
                
                <form method="GET" action="">
                    <div class="form-group">
                        <label>Data QR Code <span class="text-danger">*</span></label>
                        <textarea name="qr" class="form-control" rows="3" 
                                  placeholder="Format: NIM|NAMA|NOMOR_SURAT|TANGGAL&#10;Contoh: 2202034|MARIUS MARAWA|001/SKP-PERP/STK/I/2026|02 Desember 2025" 
                                  required></textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Copy data dari QR Code atau ketik manual
                        </small>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check-circle"></i> Validasi Surat
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <!-- Info Tambahan -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Cara Validasi:</h6>
                    <ol class="mb-0">
                        <li>Scan QR Code pada surat menggunakan aplikasi QR Scanner</li>
                        <li>Copy data hasil scan</li>
                        <li>Paste pada form di atas</li>
                        <li>Klik tombol "Validasi Surat"</li>
                    </ol>
                </div>
                
            <?php else: ?>
                <!-- Hasil Validasi -->
                <?php if ($hasil_validasi && $hasil_validasi['valid']): ?>
                    <!-- VALID -->
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h4>✓ SURAT VALID</h4>
                        <p class="mb-0">Surat keterangan ini adalah ASLI dan terdaftar dalam sistem perpustakaan</p>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Detail Surat Keterangan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Nomor Surat</th>
                                    <td><strong><?php echo $hasil_validasi['data']['nomor_surat']; ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Jenis Surat</th>
                                    <td>
                                        <?php if ($hasil_validasi['data']['jenis_surat'] == 'UAS'): ?>
                                            <span class="badge badge-primary">Ujian Akhir Semester</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Penilaian Pembelajaran Akhir</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tanggal Terbit</th>
                                    <td><?php echo formatTanggalIndonesia($hasil_validasi['data']['tanggal_terbit']); ?></td>
                                </tr>
                                <tr>
                                    <th>NIM</th>
                                    <td><?php echo $hasil_validasi['data']['nim']; ?></td>
                                </tr>
                                <tr>
                                    <th>Nama Mahasiswa</th>
                                    <td><?php echo strtoupper($hasil_validasi['data']['nama_mahasiswa']); ?></td>
                                </tr>
                                <tr>
                                    <th>Program Studi</th>
                                    <td><?php echo $hasil_validasi['data']['nama_prodi']; ?></td>
                                </tr>
                                <tr>
                                    <th>Angkatan</th>
                                    <td><?php echo $hasil_validasi['data']['angkatan']; ?></td>
                                </tr>
                                <tr>
                                    <th>Status Surat</th>
                                    <td><span class="badge badge-success">AKTIF</span></td>
                                </tr>
                            </table>
                            
                            <div class="alert alert-info mt-3">
                                <small>
                                    <i class="fas fa-shield-alt"></i> 
                                    Surat ini telah diverifikasi pada: <strong><?php echo date('d/m/Y H:i:s'); ?></strong>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- TIDAK VALID -->
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-times-circle fa-3x mb-3"></i>
                        <h4>✗ SURAT TIDAK VALID</h4>
                        <p class="mb-0"><?php echo $hasil_validasi['message'] ?? 'Data tidak ditemukan dalam sistem'; ?></p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Kemungkinan Penyebab:</h6>
                        <ul class="mb-0">
                            <li>Surat keterangan palsu/dipalsukan</li>
                            <li>Surat sudah dibatalkan oleh admin</li>
                            <li>Data QR Code tidak sesuai format</li>
                            <li>Surat belum terdaftar dalam sistem</li>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="validasi_qr.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Validasi Surat Lain
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card-footer text-center text-muted">
            <small>
                &copy; <?php echo date('Y'); ?> Perpustakaan STK Santo Yakobus Merauke
                <br>
                Untuk informasi lebih lanjut, hubungi: (0971) 3330264
            </small>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
