<?php
/**
 * Dashboard Seminar Proposal Kaprodi (FIXED - Template Integration)
 * File: application/views/kaprodi/seminar_proposal/index.php
 * 
 * Dashboard untuk mengelola seminar proposal dari perspektif Kaprodi
 * Menggunakan template kaprodi.php yang sudah ada (konsisten dengan sistem existing)
 */

// Start output buffering untuk content
ob_start();
?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Perlu Review</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $statistics['pending_review'] ?? 0 ?></span>
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
                        <span class="h2 font-weight-bold mb-0"><?= $statistics['approved_month'] ?? 0 ?></span>
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
                        <span class="h2 font-weight-bold mb-0"><?= $statistics['rejected_month'] ?? 0 ?></span>
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
                        <span class="h2 font-weight-bold mb-0"><?= $statistics['scheduled_month'] ?? 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fas fa-arrow-up"></i></span>
                    <span class="text-nowrap">Bulan ini</span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <!-- Pengajuan Perlu Review -->
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Pengajuan Perlu Review</h3>
                    </div>
                    <div class="col text-right">
                        <span class="badge badge-warning badge-pill">
                            <?= count($pending_reviews) ?> pengajuan
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <?php if(!empty($pending_reviews)): ?>
                <div class="table-responsive">
                    <table class="table table-flush" id="pengajuan-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Judul</th>
                                <th>Pembimbing</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pending_reviews as $review): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="name mb-0 text-sm font-weight-bold">
                                                <?= htmlspecialchars($review->nama_mahasiswa) ?>
                                            </span>
                                            <br><small class="text-muted"><?= htmlspecialchars($review->nim) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm" data-toggle="tooltip" title="<?= htmlspecialchars($review->judul) ?>">
                                        <?= substr(htmlspecialchars($review->judul), 0, 50) ?>...
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm"><?= htmlspecialchars($review->nama_pembimbing ?? 'Belum ada') ?></span>
                                </td>
                                <td>
                                    <span class="text-sm">
                                        <?= date('d/m/Y H:i', strtotime($review->created_at)) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('kaprodi/seminar_proposal/detail/' . $review->id) ?>" 
                                       class="btn btn-sm btn-primary" data-toggle="tooltip" title="Review Pengajuan">
                                        <i class="fas fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4>Tidak Ada Pengajuan</h4>
                    <p class="text-muted">Saat ini tidak ada pengajuan seminar proposal yang perlu direview.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <!-- Recent Approved -->
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Baru Disetujui</h3>
                    </div>
                    <div class="col text-right">
                        <a href="#" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <?php if(!empty($recent_approved)): ?>
                <div class="timeline timeline-one-side" data-timeline-content="axis" data-timeline-axis-style="dashed">
                    <?php foreach($recent_approved as $approved): ?>
                    <div class="timeline-block">
                        <span class="timeline-step badge-success">
                            <i class="fas fa-check"></i>
                        </span>
                        <div class="timeline-content">
                            <small class="text-muted font-weight-bold">
                                <?= date('d/m/Y H:i', strtotime($approved->tanggal_review_kaprodi)) ?>
                            </small>
                            <h5 class="mt-3 mb-0"><?= htmlspecialchars($approved->nama_mahasiswa) ?></h5>
                            <p class="text-sm mt-1 mb-0">
                                <?= substr(htmlspecialchars($approved->judul), 0, 60) ?>...
                            </p>
                            <div class="mt-2">
                                <span class="badge badge-success">Disetujui</span>
                                <?php if(!empty($approved->tanggal_seminar)): ?>
                                <small class="text-muted ml-2">
                                    <i class="fas fa-calendar"></i> 
                                    <?= date('d/m/Y', strtotime($approved->tanggal_seminar)) ?>
                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                    <h5>Belum Ada</h5>
                    <p class="text-sm text-muted">Belum ada seminar proposal yang disetujui baru-baru ini.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= base_url('kaprodi/proposal') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-file-alt text-primary"></i>
                            </div>
                            <div class="col ml--2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-sm">Usulan Proposal</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?= base_url('kaprodi/mahasiswa') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-users text-info"></i>
                            </div>
                            <div class="col ml--2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-sm">Daftar Mahasiswa</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?= base_url('kaprodi/dosen') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-chalkboard-teacher text-success"></i>
                            </div>
                            <div class="col ml--2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-sm">Daftar Dosen</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    
                    <a href="<?= base_url('kaprodi/laporan') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-chart-bar text-warning"></i>
                            </div>
                            <div class="col ml--2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-sm">Laporan</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Tangkap content
$content = ob_get_clean();

// Start output buffering untuk script
ob_start();
?>
<script>
$(document).ready(function() {
    // Initialize DataTables
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#pengajuan-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            },
            "pageLength": 10,
            "order": [[3, "desc"]],
            "columnDefs": [
                { "orderable": false, "targets": [4] }
            ]
        });
    }
    
    // Initialize tooltips
    if (typeof $().tooltip !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Auto refresh every 5 minutes
    setInterval(function() {
        if (confirm('Refresh data terbaru?')) {
            location.reload();
        }
    }, 300000);
});
</script>
<?php
// Tangkap script
$script = ob_get_clean();

// Load template kaprodi yang sudah ada (FIXED - Format yang benar)
$this->load->view('template/kaprodi', [
    'title' => 'Seminar Proposal',
    'content' => $content,
    'script' => $script
]);
?>