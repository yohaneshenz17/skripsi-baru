<?php
/**
 * Staf Lihat Penilaian View - READ-ONLY VERSION
 * 
 * View untuk staf melihat penilaian seminar proposal (tidak bisa edit)
 * Hanya menampilkan penilaian yang sudah diinput oleh dosen
 * 
 * File: application/views/staf/seminar_proposal/lihat_penilaian.php
 * Controller: staf/Seminar_proposal::lihat_penilaian()
 * 
 * @package     SIM_TA
 * @subpackage  Views/Staf
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 */

// Helper functions untuk form values
function get_form_value($field, $data = null, $default = '') {
    if ($data && isset($data->$field)) {
        return htmlspecialchars($data->$field);
    }
    return $default;
}

function format_rekomendasi($rekomendasi) {
    $labels = [
        'diterima_tanpa_revisi' => 'Diterima Tanpa Revisi',
        'revisi_minor' => 'Revisi Minor',
        'revisi_mayor' => 'Revisi Mayor', 
        'ditolak' => 'Ditolak'
    ];
    return isset($labels[$rekomendasi]) ? $labels[$rekomendasi] : ucwords(str_replace('_', ' ', $rekomendasi));
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-eye mr-2 text-info"></i>
            <?= $page_title ?? 'Lihat Penilaian Seminar Proposal' ?>
        </h1>
        <div>
            <a href="<?= base_url('staf/seminar_proposal/detail/' . $seminar->id) ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Detail
            </a>
            <a href="<?= base_url('staf/seminar_proposal') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-list mr-1"></i> Daftar Seminar
            </a>
        </div>
    </div>

    <!-- Alert untuk Read-Only Mode -->
    <div class="alert alert-info">
        <i class="fas fa-shield-alt mr-2"></i>
        <strong>Mode Read-Only:</strong> Anda hanya dapat melihat penilaian seminar proposal. 
        Input dan edit penilaian hanya dapat dilakukan oleh dosen pembimbing.
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Column: Data Mahasiswa & Form Penilaian -->
        <div class="col-md-8">
            
            <!-- Card: Data Mahasiswa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-graduate mr-2"></i>
                        Data Mahasiswa & Proposal
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nama</strong></td>
                                    <td>:</td>
                                    <td><?= htmlspecialchars($seminar->nama_mahasiswa ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIM</strong></td>
                                    <td>:</td>
                                    <td><?= htmlspecialchars($seminar->nim ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Program Studi</strong></td>
                                    <td>:</td>
                                    <td><?= htmlspecialchars($seminar->nama_prodi ?? 'N/A') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Tanggal Seminar</strong></td>
                                    <td>:</td>
                                    <td>
                                        <?php if(isset($seminar->tanggal_seminar) && !empty($seminar->tanggal_seminar)): ?>
                                            <?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?>
                                        <?php else: ?>
                                            <em class="text-muted">Belum dijadwalkan</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu</strong></td>
                                    <td>:</td>
                                    <td>
                                        <?php if(isset($seminar->jam_seminar) && !empty($seminar->jam_seminar)): ?>
                                            <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT
                                        <?php else: ?>
                                            <em class="text-muted">Belum ditentukan</em>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tempat</strong></td>
                                    <td>:</td>
                                    <td><?= htmlspecialchars($seminar->tempat_seminar ?? 'Belum ditentukan') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Judul Proposal:</label>
                        <p class="bg-light p-3 rounded mb-0">
                            <?= htmlspecialchars($seminar->judul ?? 'Tidak ada judul') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Status Penilaian -->
            <?php if(isset($existing_penilaian) && $existing_penilaian): ?>
                
                <!-- Card: Hasil Penilaian -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-check-circle mr-2"></i>
                            Hasil Penilaian Dosen
                        </h6>
                    </div>
                    <div class="card-body">
                        
                        <!-- Info Penilaian -->
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Status:</strong> Penilaian telah diinput oleh dosen pembimbing
                            <br>
                            <small>
                                <strong>Tanggal:</strong> 
                                <?= isset($existing_penilaian->created_at) ? date('d F Y, H:i', strtotime($existing_penilaian->created_at)) : 'Tidak diketahui' ?>
                            </small>
                        </div>

                        <!-- Komponen 1: Catatan Revisi -->
                        <div class="form-group">
                            <h5 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-edit mr-2"></i>
                                1. Catatan Revisi dari Dosen
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Latar Belakang & Rumusan Masalah:</label>
                                        <div class="bg-light p-3 rounded" style="min-height: 80px;">
                                            <?php 
                                            $catatan = get_form_value('catatan_latar_belakang', $existing_penilaian);
                                            echo $catatan ? nl2br($catatan) : '<em class="text-muted">Tidak ada catatan</em>';
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tinjauan Pustaka:</label>
                                        <div class="bg-light p-3 rounded" style="min-height: 80px;">
                                            <?php 
                                            $catatan = get_form_value('catatan_tinjauan_pustaka', $existing_penilaian);
                                            echo $catatan ? nl2br($catatan) : '<em class="text-muted">Tidak ada catatan</em>';
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Landasan Teori:</label>
                                        <div class="bg-light p-3 rounded" style="min-height: 80px;">
                                            <?php 
                                            $catatan = get_form_value('catatan_landasan_teori', $existing_penilaian);
                                            echo $catatan ? nl2br($catatan) : '<em class="text-muted">Tidak ada catatan</em>';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Metodologi Penelitian:</label>
                                        <div class="bg-light p-3 rounded" style="min-height: 80px;">
                                            <?php 
                                            $catatan = get_form_value('catatan_metodologi', $existing_penilaian);
                                            echo $catatan ? nl2br($catatan) : '<em class="text-muted">Tidak ada catatan</em>';
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Sistematika & Tata Tulis:</label>
                                        <div class="bg-light p-3 rounded" style="min-height: 80px;">
                                            <?php 
                                            $catatan = get_form_value('catatan_sistematika', $existing_penilaian);
                                            echo $catatan ? nl2br($catatan) : '<em class="text-muted">Tidak ada catatan</em>';
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Catatan Umum:</label>
                                        <div class="bg-light p-3 rounded" style="min-height: 80px;">
                                            <?php 
                                            $catatan = get_form_value('catatan_umum', $existing_penilaian);
                                            echo $catatan ? nl2br($catatan) : '<em class="text-muted">Tidak ada catatan</em>';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Komponen 2: Nilai -->
                        <div class="form-group">
                            <h5 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-calculator mr-2"></i>
                                2. Nilai Seminar Proposal
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="font-weight-bold">Nilai Dosen Penguji 1</h6>
                                            <h4 class="text-primary">
                                                <?= isset($existing_penilaian->nilai_penguji1) && is_numeric($existing_penilaian->nilai_penguji1) ? 
                                                    number_format($existing_penilaian->nilai_penguji1, 1) : 
                                                    '<span class="text-muted">Belum dinilai</span>' ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="font-weight-bold">Nilai Dosen Penguji 2</h6>
                                            <h4 class="text-success">
                                                <?= isset($existing_penilaian->nilai_penguji2) && is_numeric($existing_penilaian->nilai_penguji2) ? 
                                                    number_format($existing_penilaian->nilai_penguji2, 1) : 
                                                    '<span class="text-muted">Belum dinilai</span>' ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="font-weight-bold">Nilai Dosen Pembimbing</h6>
                                            <h4 class="text-info">
                                                <?= isset($existing_penilaian->nilai_pembimbing) && is_numeric($existing_penilaian->nilai_pembimbing) ? 
                                                    number_format($existing_penilaian->nilai_pembimbing, 1) : 
                                                    '<span class="text-muted">Belum dinilai</span>' ?>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nilai Akhir -->
                            <div class="row mt-3">
                                <div class="col-md-6 mx-auto">
                                    <div class="card bg-warning">
                                        <div class="card-body text-center">
                                            <h5 class="font-weight-bold text-white mb-2">NILAI AKHIR</h5>
                                            <h2 class="text-white">
                                                <?php if(isset($existing_penilaian->nilai_akhir) && is_numeric($existing_penilaian->nilai_akhir)): ?>
                                                    <?= number_format($existing_penilaian->nilai_akhir, 2) ?>
                                                    <span class="badge badge-light ml-2" style="font-size: 1.2rem;">
                                                        <?= $existing_penilaian->nilai_huruf ?? 'N/A' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-light">Belum dihitung</span>
                                                <?php endif; ?>
                                            </h2>
                                            <small class="text-light">
                                                (Rata-rata dari 3 dosen penilai)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Komponen 3: Rekomendasi -->
                        <div class="form-group">
                            <h5 class="font-weight-bold text-primary mb-3">
                                <i class="fas fa-thumbs-up mr-2"></i>
                                3. Rekomendasi Hasil Seminar
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Rekomendasi:</label>
                                        <div class="bg-light p-3 rounded">
                                            <?php if(isset($existing_penilaian->rekomendasi) && !empty($existing_penilaian->rekomendasi)): ?>
                                                <?php
                                                $rekomendasi = $existing_penilaian->rekomendasi;
                                                $badge_colors = [
                                                    'diterima_tanpa_revisi' => 'success',
                                                    'revisi_minor' => 'warning',
                                                    'revisi_mayor' => 'danger',
                                                    'ditolak' => 'dark'
                                                ];
                                                $color = isset($badge_colors[$rekomendasi]) ? $badge_colors[$rekomendasi] : 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $color ?> badge-lg">
                                                    <?= format_rekomendasi($rekomendasi) ?>
                                                </span>
                                            <?php else: ?>
                                                <em class="text-muted">Belum ditetapkan</em>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Keterangan Rekomendasi:</label>
                                        <div class="bg-light p-3 rounded" style="min-height: 80px;">
                                            <?php 
                                            $keterangan = get_form_value('keterangan_rekomendasi', $existing_penilaian);
                                            echo $keterangan ? nl2br($keterangan) : '<em class="text-muted">Tidak ada keterangan</em>';
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Publikasi -->
                        <div class="form-group">
                            <label class="font-weight-bold">Status Publikasi:</label>
                            <div class="bg-light p-3 rounded">
                                <?php
                                $status = isset($existing_penilaian->status_penilaian) ? $existing_penilaian->status_penilaian : 'draft';
                                if($status === 'published'):
                                ?>
                                    <span class="badge badge-success badge-lg">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Dipublikasi
                                    </span>
                                    <?php if(isset($existing_penilaian->published_at) && !empty($existing_penilaian->published_at)): ?>
                                        <small class="text-muted ml-2">
                                            pada <?= date('d F Y, H:i', strtotime($existing_penilaian->published_at)) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-warning badge-lg">
                                        <i class="fas fa-clock mr-1"></i>
                                        Masih Draft
                                    </span>
                                    <small class="text-muted ml-2">
                                        Penilaian belum dipublikasi ke mahasiswa
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

            <?php else: ?>
                
                <!-- Tidak Ada Penilaian -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Status Penilaian
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="py-5">
                            <i class="fas fa-clipboard-list fa-4x text-gray-400 mb-3"></i>
                            <h4 class="text-gray-600">Penilaian Belum Diinput</h4>
                            <p class="text-muted">
                                Dosen pembimbing belum melakukan penilaian untuk seminar proposal ini.
                                <br>
                                Silakan hubungi dosen pembimbing untuk melakukan penilaian.
                            </p>
                            
                            <div class="alert alert-light mt-4">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                <strong>Catatan:</strong> Hanya dosen pembimbing yang dapat melakukan input penilaian. 
                                Staf memiliki akses read-only untuk monitoring dan administrasi.
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>

        <!-- Right Column: Info & Actions -->
        <div class="col-md-4">
            
            <!-- Card: Dewan Penguji -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-users mr-2"></i>
                        Dewan Penguji
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h6 class="font-weight-bold">Dosen Pembimbing</h6>
                        <p class="text-primary mb-1">
                            <?= htmlspecialchars($dewan_penguji->nama_pembimbing ?? 'Belum ditentukan') ?>
                        </p>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-6 text-center">
                            <h6 class="font-weight-bold">Penguji 1</h6>
                            <p class="text-success mb-1">
                                <?= htmlspecialchars($dewan_penguji->nama_penguji1 ?? 'Belum ditentukan') ?>
                            </p>
                        </div>
                        <div class="col-6 text-center">
                            <h6 class="font-weight-bold">Penguji 2</h6>
                            <p class="text-info mb-1">
                                <?= htmlspecialchars($dewan_penguji->nama_penguji2 ?? 'Belum ditentukan') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-tools mr-2"></i>
                        Aksi Cepat (Staf)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?= base_url('staf/seminar_proposal/download_form_permohonan/' . $seminar->id) ?>" 
                           target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt mr-2 text-primary"></i>
                            Download Form Permohonan
                        </a>
                        
                        <a href="<?= base_url('staf/seminar_proposal/download_undangan/' . $seminar->id) ?>" 
                           target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-envelope mr-2 text-success"></i>
                            Download Undangan
                        </a>
                        
                        <a href="<?= base_url('staf/seminar_proposal/download_berita_acara/' . $seminar->id) ?>" 
                           target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-clipboard mr-2 text-warning"></i>
                            Download Berita Acara
                        </a>
                        
                        <?php if(isset($existing_penilaian) && $existing_penilaian): ?>
                        <a href="<?= base_url('staf/seminar_proposal/download_rekapitulasi_nilai/' . $seminar->id) ?>" 
                           target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar mr-2 text-purple"></i>
                            Download Rekapitulasi Nilai
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Card: Info System -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-info-circle mr-2"></i>
                        Informasi Sistem
                    </h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <i class="fas fa-user-shield mr-1"></i>
                        <strong>Role:</strong> Staf (Read-Only)
                        <br>
                        <i class="fas fa-eye mr-1"></i>
                        <strong>Akses:</strong> Hanya Lihat
                        <br>
                        <i class="fas fa-edit mr-1"></i>
                        <strong>Edit:</strong> Tidak Diizinkan
                        <br>
                        <i class="fas fa-clock mr-1"></i>
                        <strong>Update:</strong> <?= date('d/m/Y H:i') ?>
                    </small>
                    
                    <hr>
                    
                    <div class="alert alert-light alert-sm">
                        <i class="fas fa-lightbulb text-warning mr-2"></i>
                        <strong>Tips:</strong> Untuk melakukan input atau edit penilaian, 
                        hubungi dosen pembimbing yang bersangkutan.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Additional CSS -->
<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.text-purple {
    color: #6f42c1 !important;
}

.alert-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.card .card-body table td {
    padding: 0.5rem 0.25rem;
    border: none;
}

/* Read-only specific styles */
.bg-light {
    background-color: #f8f9fa !important;
}

.form-group .bg-light {
    border: 1px solid #dee2e6;
}

/* Card hover effects */
.card:hover {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .col-md-4, .col-md-6, .col-md-8 {
        margin-bottom: 1rem;
    }
}
</style>

<!-- JavaScript untuk interaktivitas -->
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto refresh setiap 2 menit untuk cek update penilaian
    setInterval(function() {
        // Optional: Check for updates
        console.log('Checking for penilaian updates...');
    }, 120000); // 2 minutes
    
    // Handle download clicks dengan loading indicator
    $('.list-group-item[href*="download"]').on('click', function() {
        var $this = $(this);
        var $icon = $this.find('i:first');
        var originalClass = $icon.attr('class');
        
        // Show loading
        $icon.removeClass().addClass('fas fa-spinner fa-spin');
        
        // Reset after 3 seconds
        setTimeout(function() {
            $icon.removeClass().addClass(originalClass);
        }, 3000);
    });
});
</script>