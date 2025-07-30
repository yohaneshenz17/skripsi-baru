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

<!-- ✅ TAMBAHAN BARU: Quick Actions Menu -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">⚡ Menu Aksi Cepat</h3>
                        <p class="text-white mb-0">Akses langsung ke fungsi penting Kaprodi</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4 mb-2">
                        <a href="<?= base_url('kaprodi/seminar_proposal/jadwal') ?>" class="btn btn-white btn-block">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            Kelola Penjadwalan
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-white btn-block" onclick="showRiwayatModal()">
                            <i class="fas fa-history mr-2"></i>
                            Riwayat Review
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button class="btn btn-outline-white btn-block" onclick="showJadwalModal()">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Jadwal Mendatang
                        </button>
                    </div>
                </div>
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

        <!-- ✅ TAMBAHAN BARU: Seminar Perlu Dijadwalkan -->
        <div class="card mt-4">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">⏰ Perlu Dijadwalkan</h3>
                    </div>
                    <div class="col text-right">
                        <span class="badge badge-info badge-pill">
                            <?= count($seminar_perlu_dijadwalkan) ?> seminar
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <?php if(!empty($seminar_perlu_dijadwalkan)): ?>
                <div class="table-responsive">
                    <table class="table table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Judul</th>
                                <th>Disetujui</th>
                                <th>Lama Menunggu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($seminar_perlu_dijadwalkan as $seminar): ?>
                            <?php 
                            $days_waiting = (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($seminar->tanggal_review_kaprodi)))) / (60*60*24);
                            $badge_class = $days_waiting > 7 ? 'danger' : ($days_waiting > 3 ? 'warning' : 'info');
                            ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="name mb-0 text-sm font-weight-bold">
                                                <?= htmlspecialchars($seminar->nama_mahasiswa) ?>
                                            </span>
                                            <br><small class="text-muted"><?= htmlspecialchars($seminar->nim) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm" data-toggle="tooltip" title="<?= htmlspecialchars($seminar->judul) ?>">
                                        <?= substr(htmlspecialchars($seminar->judul), 0, 40) ?>...
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm text-success">
                                        <i class="fas fa-check mr-1"></i>
                                        <?= date('d/m/Y', strtotime($seminar->tanggal_review_kaprodi)) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $badge_class ?>">
                                        <?= $days_waiting ?> hari
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('kaprodi/seminar_proposal/jadwal/' . $seminar->id) ?>" 
                                       class="btn btn-sm btn-info" data-toggle="tooltip" title="Jadwalkan Seminar">
                                        <i class="fas fa-calendar-plus"></i> Jadwalkan
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                    <h5>Semua Sudah Terjadwal</h5>
                    <p class="text-sm text-muted">Tidak ada seminar proposal yang perlu dijadwalkan saat ini.</p>
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

<!-- ✅ TAMBAHAN BARU: Modal Riwayat Review -->
<div class="modal fade" id="riwayatModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history mr-2"></i>
                    Riwayat Review Kaprodi
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!empty($riwayat_review)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Mahasiswa</th>
                                    <th>Keputusan</th>
                                    <th>Plagiarisme</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat_review as $riwayat): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($riwayat->tanggal_review_kaprodi)) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($riwayat->nama_mahasiswa) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($riwayat->nim) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($riwayat->status_kaprodi == 'approved'): ?>
                                            <span class="badge badge-success">DISETUJUI</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">DITOLAK</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($riwayat->plagiarism_percentage): ?>
                                            <span class="badge badge-<?= $riwayat->plagiarism_percentage <= 30 ? 'success' : 'danger' ?>">
                                                <?= $riwayat->plagiarism_percentage ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">Belum ada riwayat review.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ TAMBAHAN BARU: Modal Jadwal Mendatang -->
<div class="modal fade" id="jadwalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Jadwal Seminar Mendatang
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!empty($jadwal_mendatang)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Mahasiswa</th>
                                    <th>Tempat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jadwal_mendatang as $jadwal): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('d/m/Y', strtotime($jadwal->tanggal_seminar)) ?></strong><br>
                                        <small class="text-info"><?= date('H:i', strtotime($jadwal->jam_seminar)) ?> WIT</small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($jadwal->nama_mahasiswa) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($jadwal->nim) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($jadwal->tempat_seminar) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted">Tidak ada jadwal seminar mendatang.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a href="<?= base_url('kaprodi/seminar_proposal/jadwal') ?>" class="btn btn-primary">
                    <i class="fas fa-calendar-plus mr-1"></i> Kelola Jadwal
                </a>
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

// ✅ TAMBAHAN BARU: Functions untuk modal
function showRiwayatModal() {
    $('#riwayatModal').modal('show');
}

function showJadwalModal() {
    $('#jadwalModal').modal('show');
}
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