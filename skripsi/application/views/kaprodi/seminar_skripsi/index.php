<?php
/**
 * FINAL FIXED - Kaprodi Seminar Skripsi Index View
 * 
 * DISESUAIKAN DENGAN DATABASE STRUCTURE:
 * - Tabel: seminar_skripsi_mahasiswa 
 * - View: seminar_skripsi_progress_v
 * - Field sesuai dengan struktur database aktual
 * 
 * PERBAIKAN:
 * 1. Fix word_limiter() dengan fallback function
 * 2. Field names sesuai database structure
 * 3. Status handling sesuai enum values di database
 * 4. Proper null checks untuk semua data
 * 
 * File: application/views/kaprodi/seminar_skripsi/index.php
 */

// ✅ Helper function untuk word limiter dengan fallback
if (!function_exists('safe_word_limiter')) {
    function safe_word_limiter($str, $limit = 8, $end_char = '...') {
        if (empty($str)) return '-';
        
        if (function_exists('word_limiter')) {
            return word_limiter($str, $limit, $end_char);
        }
        
        // Fallback manual jika text helper tidak tersedia
        $words = explode(' ', trim($str));
        if (count($words) > $limit) {
            return implode(' ', array_slice($words, 0, $limit)) . $end_char;
        }
        return $str;
    }
}

// Helper function untuk status badge
function get_status_badge($status_kaprodi, $status_pembimbing = null) {
    switch ($status_kaprodi) {
        case 'pending':
            return '<span class="badge badge-warning">Menunggu Review</span>';
        case 'approved':
            return '<span class="badge badge-success">Disetujui</span>';
        case 'rejected':
            return '<span class="badge badge-danger">Ditolak</span>';
        default:
            return '<span class="badge badge-secondary">Draft</span>';
    }
}

// Pastikan data tersedia
$seminar_skripsi = isset($seminar_skripsi) ? $seminar_skripsi : [];
$stats = isset($stats) ? $stats : [];
?>

<!-- Page Header -->
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Kelola Seminar Skripsi</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item">
                                <a href="<?= base_url('kaprodi/dashboard') ?>">
                                    <i class="fas fa-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Seminar Skripsi</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 col-5 text-right">
                    <a href="#" class="btn btn-sm btn-neutral" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="container-fluid mt--6">
    
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Perlu Review</h5>
                            <span class="h2 font-weight-bold mb-0"><?= isset($stats['pending_review']) ? $stats['pending_review'] : 0 ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-warning mr-2"><i class="fas fa-arrow-up"></i></span>
                        <span class="text-nowrap">Menunggu validasi</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Disetujui</h5>
                            <span class="h2 font-weight-bold mb-0"><?= isset($stats['approved_month']) ? $stats['approved_month'] : 0 ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-success mr-2"><i class="fas fa-arrow-up"></i></span>
                        <span class="text-nowrap">Bulan ini</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Ditolak</h5>
                            <span class="h2 font-weight-bold mb-0"><?= isset($stats['rejected_month']) ? $stats['rejected_month'] : 0 ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-danger mr-2"><i class="fas fa-arrow-down"></i></span>
                        <span class="text-nowrap">Bulan ini</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-muted mb-0">Terjadwal</h5>
                            <span class="h2 font-weight-bold mb-0"><?= isset($stats['scheduled']) ? $stats['scheduled'] : 0 ?></span>
                        </div>
                        <div class="col-auto">
                            <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 mb-0 text-sm">
                        <span class="text-info mr-2"><i class="fas fa-calendar"></i></span>
                        <span class="text-nowrap">Sudah dijadwalkan</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="row">
        <div class="col">
            <div class="card">
                <!-- Card Header -->
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Daftar Seminar Skripsi</h3>
                        </div>
                        <div class="col text-right">
                            <span class="text-muted">
                                <i class="fas fa-list mr-1"></i>
                                <?= is_array($seminar_skripsi) ? count($seminar_skripsi) : 0 ?> pengajuan
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Card Body -->
                <div class="card-body">
                    <!-- Flash Messages -->
                    <?php if($this->session->flashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check mr-2"></i>
                            <?= $this->session->flashdata('success') ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Tabel Data -->
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush" id="datatable-seminar-skripsi">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="sort" data-sort="number">No</th>
                                    <th scope="col" class="sort" data-sort="mahasiswa">Mahasiswa</th>
                                    <th scope="col" class="sort" data-sort="judul">Judul Skripsi</th>
                                    <th scope="col" class="sort" data-sort="pembimbing">Pembimbing</th>
                                    <th scope="col" class="sort" data-sort="status">Status</th>
                                    <th scope="col" class="sort" data-sort="plagiarism">Plagiarisme</th>
                                    <th scope="col" class="sort" data-sort="tanggal">Tanggal Pengajuan</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($seminar_skripsi)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-graduation-cap fa-3x mb-3 text-muted"></i><br>
                                                <h5 class="text-muted">Tidak ada data seminar skripsi</h5>
                                                <p class="text-muted mb-0">Belum ada mahasiswa yang mengajukan seminar skripsi</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($seminar_skripsi as $key => $item): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-soft-primary"><?= $key + 1 ?></span>
                                            </td>
                                            <td>
                                                <div class="media align-items-center">
                                                    <div class="media-body">
                                                        <span class="name font-weight-bold mb-0">
                                                            <?= htmlspecialchars($item->nama_mahasiswa ?? 'N/A') ?>
                                                        </span><br>
                                                        <small class="text-muted">
                                                            <?= htmlspecialchars($item->nim ?? 'N/A') ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-sm" title="<?= htmlspecialchars($item->judul_skripsi ?? $item->judul_proposal ?? 'N/A') ?>">
                                                    <?= safe_word_limiter($item->judul_skripsi ?? $item->judul_proposal ?? 'N/A', 8) ?>
                                                </span>
                                                <?php if(!empty($item->file_skripsi)): ?>
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-file-pdf mr-1"></i>File tersedia
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-sm">
                                                    <?= htmlspecialchars($item->nama_pembimbing ?? 'Belum ditentukan') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= get_status_badge($item->status_kaprodi ?? 'pending', $item->status_pembimbing ?? null) ?>
                                                <?php if($item->status_pembimbing == 'approved' && $item->status_kaprodi == 'pending'): ?>
                                                    <br><small class="text-info mt-1">
                                                        <i class="fas fa-user-check mr-1"></i>Direkomendasikan pembimbing
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(!empty($item->plagiarism_percentage)): ?>
                                                    <?php 
                                                    $plagiarism = floatval($item->plagiarism_percentage);
                                                    $badge_class = $plagiarism <= 30 ? 'success' : 'danger';
                                                    ?>
                                                    <span class="badge badge-<?= $badge_class ?>">
                                                        <?= number_format($plagiarism, 1) ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-sm">
                                                    <?= $item->created_at ? date('d/m/Y', strtotime($item->created_at)) : '-' ?>
                                                </span>
                                                <?php if($item->created_at): ?>
                                                    <br><small class="text-muted">
                                                        <?= date('H:i', strtotime($item->created_at)) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('kaprodi/seminar_skripsi/detail/' . $item->id) ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if($item->status_kaprodi == 'pending' && $item->status_pembimbing == 'approved'): ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                onclick="showValidasiModal(<?= $item->id ?>, '<?= htmlspecialchars($item->nama_mahasiswa) ?>')"
                                                                title="Validasi Turnitin">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($item->status_kaprodi == 'approved' && empty($item->tanggal_seminar)): ?>
                                                        <a href="<?= base_url('kaprodi/seminar_skripsi/penjadwalan/' . $item->id) ?>" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           title="Jadwalkan Seminar">
                                                            <i class="fas fa-calendar-plus"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if(!empty($item->tanggal_seminar)): ?>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-info" 
                                                                onclick="showJadwalInfo('<?= $item->tanggal_seminar ?>', '<?= $item->jam_seminar ?>', '<?= htmlspecialchars($item->tempat_seminar) ?>')"
                                                                title="Info Jadwal">
                                                            <i class="fas fa-calendar-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Validasi Turnitin -->
<div class="modal fade" id="validasiModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="validasiForm" method="POST" action="<?= base_url('kaprodi/seminar_skripsi/validasi_turnitin') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-search mr-2"></i>Validasi Plagiarisme Turnitin
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="validasi_seminar_id">
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Kebijakan Plagiarisme:</strong> Maksimal 30% untuk dapat disetujui
                    </div>
                    
                    <div class="form-group">
                        <label>Mahasiswa:</label>
                        <p class="form-control-plaintext font-weight-bold" id="validasi_nama_mahasiswa"></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="plagiarism_percentage">
                            <i class="fas fa-percentage mr-1"></i>
                            Persentase Plagiarisme (%) <span class="text-danger">*</span>
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="plagiarism_percentage" 
                               name="plagiarism_percentage" 
                               min="0" 
                               max="100" 
                               step="0.1" 
                               placeholder="Contoh: 25.5"
                               required>
                        <small class="form-text text-muted">
                            Input hasil persentase plagiarisme dari Turnitin
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="decision">
                            <i class="fas fa-gavel mr-1"></i>
                            Keputusan <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="decision" name="decision" required>
                            <option value="">-- Pilih Keputusan --</option>
                            <option value="approved">Setujui (≤ 30%)</option>
                            <option value="rejected">Tolak (> 30%)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="komentar_kaprodi">
                            <i class="fas fa-comment mr-1"></i>
                            Komentar Validasi
                        </label>
                        <textarea class="form-control" 
                                  id="komentar_kaprodi" 
                                  name="komentar_kaprodi" 
                                  rows="4" 
                                  placeholder="Berikan komentar terkait hasil validasi turnitin..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan Validasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Info Jadwal -->
<div class="modal fade" id="jadwalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-check mr-2"></i>Informasi Jadwal Seminar
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Tanggal:</strong>
                    </div>
                    <div class="col-md-8" id="jadwal_tanggal"></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <strong>Waktu:</strong>
                    </div>
                    <div class="col-md-8" id="jadwal_waktu"></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <strong>Tempat:</strong>
                    </div>
                    <div class="col-md-8" id="jadwal_tempat"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Initialize DataTable jika tersedia
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#datatable-seminar-skripsi').DataTable({
            "pageLength": 25,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "paginate": {
                    "previous": "<i class='fas fa-angle-left'></i>",
                    "next": "<i class='fas fa-angle-right'></i>"
                }
            }
        });
    }
    
    // Auto-update decision based on plagiarism percentage
    $('#plagiarism_percentage').on('input', function() {
        const percentage = parseFloat($(this).val());
        const decisionSelect = $('#decision');
        
        if (!isNaN(percentage)) {
            if (percentage <= 30) {
                decisionSelect.val('approved');
                decisionSelect.removeClass('is-invalid').addClass('is-valid');
            } else {
                decisionSelect.val('rejected');
                decisionSelect.removeClass('is-valid').addClass('is-invalid');
            }
        }
    });
    
    // Form validation
    $('#validasiForm').on('submit', function(e) {
        const percentage = parseFloat($('#plagiarism_percentage').val());
        const decision = $('#decision').val();
        
        if (isNaN(percentage) || percentage < 0 || percentage > 100) {
            e.preventDefault();
            alert('Persentase plagiarisme tidak valid!');
            return false;
        }
        
        if (!decision) {
            e.preventDefault();
            alert('Silakan pilih keputusan!');
            return false;
        }
        
        if (percentage > 30 && decision === 'approved') {
            if (!confirm('Persentase plagiarisme > 30% tetapi Anda memilih SETUJUI. Yakin melanjutkan?')) {
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
});

function showValidasiModal(seminarId, namaMahasiswa) {
    $('#validasi_seminar_id').val(seminarId);
    $('#validasi_nama_mahasiswa').text(namaMahasiswa);
    $('#validasiModal').modal('show');
}

function showJadwalInfo(tanggal, jam, tempat) {
    const formatTanggal = new Date(tanggal).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    $('#jadwal_tanggal').text(formatTanggal);
    $('#jadwal_waktu').text(jam);
    $('#jadwal_tempat').text(tempat);
    $('#jadwalModal').modal('show');
}
</script>