<?php
/**
 * FINAL FIXED - Seminar Skripsi Index (Data Muncul + Header Benar)
 * File: application/views/kaprodi/seminar_skripsi/index.php
 * 
 * MENGGABUNGKAN:
 * 1. STRUKTUR HEADER dari file baru (tidak double)
 * 2. LOGIC DATA dari file lama (data muncul)
 * 3. MENGHILANGKAN tombol checklist validasi
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

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Perlu Review</h5>
                        <span class="h2 font-weight-bold mb-0 text-warning"><?= isset($stats['pending_review']) ? $stats['pending_review'] : 0 ?></span>
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
                        <span class="h2 font-weight-bold mb-0 text-success"><?= isset($stats['approved_month']) ? $stats['approved_month'] : 0 ?></span>
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
                        <span class="h2 font-weight-bold mb-0 text-danger"><?= isset($stats['rejected_month']) ? $stats['rejected_month'] : 0 ?></span>
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
                        <span class="h2 font-weight-bold mb-0 text-info"><?= isset($stats['scheduled']) ? $stats['scheduled'] : 0 ?></span>
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

<!-- Main Content -->
<div class="row mt-4">
    <div class="col">
        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-book mr-2"></i>
                            Daftar Seminar Skripsi
                        </h3>
                    </div>
                    <div class="col text-right">
                        <span class="text-muted">
                            <i class="fas fa-list mr-1"></i>
                            <?= is_array($seminar_skripsi) ? count($seminar_skripsi) : 0 ?> pengajuan
                        </span>
                        <button type="button" class="btn btn-primary btn-sm ml-2" onclick="location.reload()">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
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
                                            <!-- FIXED: HANYA TOMBOL DETAIL -->
                                            <a href="<?= base_url('kaprodi/seminar_skripsi/detail/' . $item->id) ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Lihat Detail & Validasi">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Info tambahan untuk jadwal jika sudah ada -->
                                            <?php if(!empty($item->tanggal_seminar)): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info ml-1" 
                                                        onclick="showJadwalInfo('<?= $item->tanggal_seminar ?>', '<?= $item->jam_seminar ?>', '<?= htmlspecialchars($item->tempat_seminar) ?>')"
                                                        title="Info Jadwal">
                                                    <i class="fas fa-calendar-check"></i>
                                                </button>
                                            <?php endif; ?>
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

<!-- Quick Actions Tips -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="text-white mb-1">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Tips Validasi Seminar Skripsi
                        </h5>
                        <p class="text-white mb-0 opacity-8">
                            • Pastikan persentase plagiarisme ≤ 30% untuk approval
                            • Review rekomendasi dari dosen pembimbing
                            • Jadwalkan dengan mempertimbangkan ketersediaan dosen penguji
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('kaprodi/seminar_skripsi/bantuan') ?>" 
                           class="btn btn-sm btn-white">
                            <i class="fas fa-question-circle mr-1"></i>
                            Bantuan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Info Jadwal (tetap dipertahankan untuk info) -->
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

<style>
.card-stats {
    border: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card-stats:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    border-top: 1px solid #e9ecef;
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.375rem 0.75rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.media-body {
    flex: 1 1 auto;
    min-width: 0;
}

.icon-shape {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    vertical-align: middle;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #11cdef 0%, #1171ef 100%);
}

.opacity-8 {
    opacity: 0.8;
}
</style>

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
    
    // Initialize tooltips
    $('[title]').tooltip();
});

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