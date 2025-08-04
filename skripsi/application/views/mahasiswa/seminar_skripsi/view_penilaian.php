<?php
/**
 * View Hasil Penilaian Seminar Skripsi
 * 
 * File: application/views/mahasiswa/seminar_skripsi/view_penilaian.php
 * 
 * FEATURES:
 * ✅ Tampilan nilai lengkap dengan breakdown per penguji
 * ✅ Rekomendasi hasil seminar dengan color coding
 * ✅ Catatan detail dari dosen per aspek penilaian
 * ✅ Next actions berdasarkan hasil penilaian
 * ✅ Design responsive dan profesional
 * ✅ Print-friendly layout
 */
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-star mr-2"></i>
            Hasil Penilaian Seminar Skripsi
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                <li class="breadcrumb-item active">Hasil Penilaian</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Header Card - Info Seminar -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informasi Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Nama Mahasiswa:</label>
                                <div class="font-weight-bold"><?= $seminar->nama_mahasiswa ?></div>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">NIM:</label>
                                <div class="font-weight-bold"><?= $seminar->nim ?></div>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">Program Studi:</label>
                                <div class="font-weight-bold"><?= $seminar->nama_prodi ?? 'Program Studi' ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Dosen Pembimbing:</label>
                                <div class="font-weight-bold"><?= $seminar->nama_pembimbing ?></div>
                            </div>
                            <?php if (!empty($seminar->tanggal_seminar)): ?>
                            <div class="info-group mb-3">
                                <label class="text-muted">Tanggal Seminar:</label>
                                <div class="font-weight-bold">
                                    <?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?>
                                    <?php if (!empty($seminar->jam_seminar)): ?>
                                        <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIB
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="info-group mb-3">
                                <label class="text-muted">Tanggal Penilaian:</label>
                                <div class="font-weight-bold"><?= date('d F Y H:i', strtotime($penilaian->published_at)) ?> WIB</div>
                            </div>
                        </div>
                    </div>
                    <div class="info-group">
                        <label class="text-muted">Judul Skripsi:</label>
                        <div class="font-weight-bold"><?= $seminar->judul_skripsi ?></div>
                    </div>
                </div>
            </div>

            <!-- Nilai & Rekomendasi Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%); color: #2d3436;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-line mr-2"></i>
                        Hasil Penilaian & Rekomendasi
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Nilai Section -->
                    <div class="row mb-4">
                        <div class="col-lg-8">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-star mr-2"></i>Breakdown Nilai
                            </h6>
                            <div class="row text-center">
                                <?php if (!empty($penilaian->nilai_penguji1)): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="nilai-card bg-info text-white p-3 rounded">
                                        <h4 class="mb-1"><?= number_format($penilaian->nilai_penguji1, 1) ?></h4>
                                        <small>Penguji 1</small>
                                        <?php if (!empty($seminar->nama_penguji1)): ?>
                                        <div class="mt-1 small"><?= $seminar->nama_penguji1 ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($penilaian->nilai_penguji2)): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="nilai-card bg-warning text-white p-3 rounded">
                                        <h4 class="mb-1"><?= number_format($penilaian->nilai_penguji2, 1) ?></h4>
                                        <small>Penguji 2</small>
                                        <?php if (!empty($seminar->nama_penguji2)): ?>
                                        <div class="mt-1 small"><?= $seminar->nama_penguji2 ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-md-4 mb-3">
                                    <div class="nilai-card bg-success text-white p-3 rounded">
                                        <h4 class="mb-1"><?= number_format($penilaian->nilai_pembimbing, 1) ?></h4>
                                        <small>Pembimbing</small>
                                        <div class="mt-1 small"><?= $seminar->nama_pembimbing ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-trophy mr-2"></i>Nilai Akhir
                            </h6>
                            <div class="text-center">
                                <div class="nilai-akhir-card p-4 rounded" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                                    <h1 class="text-white mb-2"><?= number_format($penilaian->nilai_akhir, 1) ?></h1>
                                    <h3 class="text-white mb-1"><?= $penilaian->nilai_huruf ?></h3>
                                    <small class="text-white opacity-75">Nilai Akhir</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ FIXED: Rekomendasi Section - Handle Empty Data -->
                    <div class="rekomendasi-section">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-graduation-cap mr-2"></i>Rekomendasi Kelulusan
                        </h6>
                        
                        <?php
                        // ✅ HANDLE EMPTY/INVALID REKOMENDASI
                        $rekomendasi_raw = trim($penilaian->rekomendasi ?? '');
                        
                        if (empty($rekomendasi_raw)) {
                            // Rekomendasi kosong - tampilkan peringatan
                            ?>
                            <div class="alert alert-warning border-left-warning">
                                <i class="fas fa-exclamation-triangle mr-3"></i>
                                <strong>REKOMENDASI DIKIRIMKAN MELALUI EMAIL</strong>
                                <p class="mb-2 mt-2">Silakan cek inbox email Anda untuk melihat hasil rekomendasi atau hubungi dosen.</p>
                                <small class="text-muted">
                                    Nilai Anda: <strong><?= number_format($penilaian->nilai_akhir, 1) ?> (<?= $penilaian->nilai_huruf ?>)</strong>
                                </small>
                            </div>
                            <?php
                        } else {
                            // Ada rekomendasi - tampilkan sesuai data
                            $rekomendasi_config = [
                                'lulus_tanpa_revisi' => [
                                    'class' => 'success',
                                    'icon' => 'check-circle',
                                    'text' => 'LULUS TANPA REVISI'
                                ],
                                'lulus_dengan_revisi_minor' => [
                                    'class' => 'warning',
                                    'icon' => 'edit',
                                    'text' => 'LULUS DENGAN REVISI MINOR'
                                ],
                                'lulus_dengan_revisi_mayor' => [
                                    'class' => 'info',
                                    'icon' => 'tools',
                                    'text' => 'LULUS DENGAN REVISI MAYOR'
                                ],
                                'tidak_lulus' => [
                                    'class' => 'danger',
                                    'icon' => 'times-circle',
                                    'text' => 'TIDAK LULUS'
                                ]
                            ];
                            
                            $rekomendasi_key = strtolower($rekomendasi_raw);
                            $config = $rekomendasi_config[$rekomendasi_key] ?? [
                                'class' => 'secondary',
                                'icon' => 'question-circle',  
                                'text' => 'DATA TIDAK VALID: "' . htmlspecialchars($rekomendasi_raw) . '"'
                            ];
                            ?>
                            
                            <div class="alert alert-<?= $config['class'] ?> border-left-<?= $config['class'] ?> h5 mb-3">
                                <i class="fas fa-<?= $config['icon'] ?> mr-3"></i>
                                <strong><?= $config['text'] ?></strong>
                            </div>
                            <?php
                        }
                        ?>
                    
                        <!-- Keterangan Rekomendasi (jika ada) -->
                        <?php if (!empty($penilaian->keterangan_rekomendasi)): ?>
                        <div class="alert alert-light border">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-comment-alt mr-2"></i>Keterangan Rekomendasi:
                            </h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($penilaian->keterangan_rekomendasi)) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- ✅ INFO: Debugging untuk admin (bisa dihapus di production) -->
                        <?php if ($this->session->userdata('level') == '1'): // Admin only ?>
                        <div class="alert alert-info small">
                            <strong>Debug Info (Admin Only):</strong><br>
                            Raw rekomendasi: "<?= htmlspecialchars($penilaian->rekomendasi ?? 'NULL') ?>"<br>
                            Length: <?= strlen($penilaian->rekomendasi ?? '') ?><br>
                            Dinilai oleh: <?= $penilaian->dinilai_oleh ?><br>
                            Role penilai: <?= $penilaian->role_penilai ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Catatan Detail Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%); color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Catatan Detail Penilaian
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Catatan BAB 1 -->
                        <?php if (!empty($penilaian->catatan_pendahuluan)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="catatan-item">
                                <h6 class="text-primary border-left border-primary pl-3">
                                    <i class="fas fa-book-open mr-2"></i>BAB I - Pendahuluan
                                </h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($penilaian->catatan_pendahuluan)) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Catatan BAB 2 -->
                        <?php if (!empty($penilaian->catatan_tinjauan_pustaka)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="catatan-item">
                                <h6 class="text-info border-left border-info pl-3">
                                    <i class="fas fa-books mr-2"></i>BAB II - Tinjauan Pustaka
                                </h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($penilaian->catatan_tinjauan_pustaka)) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Catatan BAB 3 -->
                        <?php if (!empty($penilaian->catatan_metodologi)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="catatan-item">
                                <h6 class="text-success border-left border-success pl-3">
                                    <i class="fas fa-cogs mr-2"></i>BAB III - Metodologi
                                </h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($penilaian->catatan_metodologi)) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Catatan BAB 4 -->
                        <?php if (!empty($penilaian->catatan_hasil_pembahasan)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="catatan-item">
                                <h6 class="text-warning border-left border-warning pl-3">
                                    <i class="fas fa-chart-line mr-2"></i>BAB IV - Hasil & Pembahasan
                                </h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($penilaian->catatan_hasil_pembahasan)) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Catatan BAB 5 -->
                        <?php if (!empty($penilaian->catatan_kesimpulan)): ?>
                        <div class="col-md-6 mb-4">
                            <div class="catatan-item">
                                <h6 class="text-danger border-left border-danger pl-3">
                                    <i class="fas fa-flag-checkered mr-2"></i>BAB V - Kesimpulan
                                </h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($penilaian->catatan_kesimpulan)) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Catatan Umum -->
                        <?php if (!empty($penilaian->catatan_umum)): ?>
                        <div class="col-12 mb-4">
                            <div class="catatan-item">
                                <h6 class="text-secondary border-left border-secondary pl-3">
                                    <i class="fas fa-comment mr-2"></i>Catatan Umum
                                </h6>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(htmlspecialchars($penilaian->catatan_umum)) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Jika tidak ada catatan detail -->
                    <?php if (empty($penilaian->catatan_pendahuluan) && empty($penilaian->catatan_tinjauan_pustaka) && 
                              empty($penilaian->catatan_metodologi) && empty($penilaian->catatan_hasil_pembahasan) && 
                              empty($penilaian->catatan_kesimpulan) && empty($penilaian->catatan_umum)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                        <h6 class="text-muted">Tidak ada catatan detail dari dosen</h6>
                        <p class="text-muted mb-0">Dosen tidak memberikan catatan khusus untuk aspek penilaian tertentu.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Next Steps Card -->
            <?php if (isset($next_phase_info) && $next_phase_info): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-<?= $next_phase_info['status'] ?>">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-arrow-right mr-2"></i>
                        Langkah Selanjutnya
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <?php if ($next_phase_info['status'] == 'success'): ?>
                            <i class="fas fa-graduation-cap fa-3x text-success mb-3"></i>
                        <?php elseif ($next_phase_info['status'] == 'warning'): ?>
                            <i class="fas fa-edit fa-3x text-warning mb-3"></i>
                        <?php else: ?>
                            <i class="fas fa-redo fa-3x text-danger mb-3"></i>
                        <?php endif; ?>
                        
                        <h6 class="text-<?= $next_phase_info['status'] ?>"><?= $next_phase_info['message'] ?></h6>
                    </div>
                    
                    <div class="text-center">
                        <a href="<?= $next_phase_info['next_url'] ?>" 
                           class="btn btn-<?= $next_phase_info['status'] ?> btn-block">
                            <i class="fas fa-arrow-right mr-2"></i>
                            <?= $next_phase_info['next_action'] ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Cards -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools mr-2"></i>
                        Aksi Tersedia
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Print Penilaian -->
                    <button onclick="window.print()" class="btn btn-info btn-block mb-3">
                        <i class="fas fa-print mr-2"></i>
                        Cetak Penilaian
                    </button>

                    <!-- Download File Skripsi -->
                    <?php if (!empty($seminar->file_skripsi)): ?>
                    <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id) ?>" 
                       class="btn btn-secondary btn-block mb-3">
                        <i class="fas fa-download mr-2"></i>
                        Download Skripsi
                    </a>
                    <?php endif; ?>

                    <!-- Kembali ke Seminar Skripsi -->
                    <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" 
                       class="btn btn-outline-primary btn-block mb-3">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Seminar
                    </a>

                    <!-- Kembali ke Dashboard -->
                    <a href="<?= base_url('mahasiswa/dashboard') ?>" 
                       class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </div>
            </div>

            <!-- Grade Info Card -->
            <div class="card border-left-info shadow">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Nilai Anda
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $penilaian->nilai_akhir ?> (<?= $penilaian->nilai_huruf ?>)
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <?php
                        switch($penilaian->nilai_huruf) {
                            case 'A': echo 'Sangat Baik (85-100)'; break;
                            case 'B': echo 'Baik (70-84)'; break;
                            case 'C': echo 'Cukup (60-69)'; break;
                            case 'D': echo 'Kurang (50-59)'; break;
                            case 'E': echo 'Sangat Kurang (0-49)'; break;
                            default: echo 'Belum ada nilai';
                        }
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-group label {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 2px;
}

.nilai-card {
    transition: transform 0.2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nilai-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.nilai-akhir-card {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.nilai-akhir-card:hover {
    transform: scale(1.05);
}

.catatan-item {
    margin-bottom: 1rem;
}

.border-left {
    border-left: 4px solid !important;
}

.border-left-primary {
    border-left-color: #4e73df !important;
}

.border-left-info {
    border-left-color: #36b9cc !important;
}

.border-left-success {
    border-left-color: #1cc88a !important;
}

.border-left-warning {
    border-left-color: #f6c23e !important;
}

.border-left-danger {
    border-left-color: #e74a3b !important;
}

.border-left-secondary {
    border-left-color: #858796 !important;
}

/* Print Styles */
@media print {
    .col-lg-4 {
        display: none !important;
    }
    
    .col-lg-8 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        break-inside: avoid;
    }
    
    .card-header {
        background: #f8f9fa !important;
        color: #495057 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .text-primary, .text-info, .text-success, .text-warning, .text-danger {
        color: #495057 !important;
    }
    
    .nilai-card {
        border: 2px solid #dee2e6 !important;
        background-color: #f8f9fa !important;
        color: #495057 !important;
    }
    
    .nilai-akhir-card {
        border: 3px solid #495057 !important;
        background-color: #e9ecef !important;
        color: #495057 !important;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .nilai-card {
        margin-bottom: 1rem;
    }
    
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .info-group {
        margin-bottom: 1rem;
    }
}
</style>