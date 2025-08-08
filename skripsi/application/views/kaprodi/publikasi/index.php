<?php
// Mulai output buffering untuk menangkap konten
ob_start();
?>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Dashboard Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $summary_stats['total_mahasiswa_prodi'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Mahasiswa aktif di prodi</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Eligible Publikasi</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $summary_stats['eligible_publikasi'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Siap ajukan publikasi</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Sedang Proses</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $summary_stats['pengajuan_berjalan'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Dalam workflow</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Selesai</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $summary_stats['publikasi_selesai'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-default text-white rounded-circle shadow">
                            <i class="fa fa-crown"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Publikasi selesai</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Card -->
<div class="card">
    <div class="card-header border-0">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="mb-0">Monitoring Publikasi Tugas Akhir</h3>
                <p class="text-sm mb-0">
                    Pantau dan kelola proses publikasi mahasiswa di program studi Anda
                    <?php if($summary_stats['rata_waktu_proses'] > 0): ?>
                        <span class="badge badge-info ml-2">
                            Rata-rata proses: <?= $summary_stats['rata_waktu_proses'] ?> hari
                        </span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col text-right">
                <a href="<?= base_url('kaprodi/publikasi/laporan') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-chart-bar"></i> Laporan
                </a>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter & Actions -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-group mb-0">
                    <label class="sr-only">Filter Status</label>
                    <select class="form-control form-control-sm" id="filter-status">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Diajukan</option>
                        <option value="review_pembimbing">Review Dosen</option>
                        <option value="approved_pembimbing">Approved Dosen</option>
                        <option value="review_staf">Review Staf</option>
                        <option value="completed">Selesai</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <span class="badge badge-primary">
                    Total: <?= count($publikasi_list) ?> publikasi
                </span>
            </div>
        </div>
        
        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table align-items-center table-flush" id="datatable-publikasi">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Pembimbing</th>
                        <th scope="col">Status</th>
                        <th scope="col">Progress</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($publikasi_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fa fa-crown fa-2x mb-2"></i><br>
                                    <strong>Belum ada publikasi</strong><br>
                                    <small>Belum ada mahasiswa yang mengajukan publikasi tugas akhir</small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($publikasi_list as $key => $pub): ?>
                            <tr data-status="<?= $pub->status ?>">
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="name font-weight-bold"><?= $pub->nama_mahasiswa ?></span><br>
                                            <small class="text-muted"><?= $pub->nim ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm" title="<?= $pub->judul ?>">
                                        <?= word_limiter($pub->judul, 8) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm"><?= $pub->nama_pembimbing ?></span>
                                </td>
                                <td>
                                    <?php
                                    $status_badges = [
                                        'draft' => 'badge-secondary',
                                        'submitted' => 'badge-info',
                                        'review_pembimbing' => 'badge-warning',
                                        'approved_pembimbing' => 'badge-primary',
                                        'review_staf' => 'badge-primary',
                                        'completed' => 'badge-success',
                                        'rejected' => 'badge-danger'
                                    ];
                                    
                                    $status_text = [
                                        'draft' => 'Draft',
                                        'submitted' => 'Diajukan',
                                        'review_pembimbing' => 'Review Dosen',
                                        'approved_pembimbing' => 'Approved Dosen',
                                        'review_staf' => 'Review Staf',
                                        'completed' => 'Selesai',
                                        'rejected' => 'Ditolak'
                                    ];
                                    
                                    $badge_class = $status_badges[$pub->status] ?? 'badge-secondary';
                                    $status_display = $status_text[$pub->status] ?? 'Unknown';
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= $status_display ?></span>
                                </td>
                                <td>
                                    <?php
                                    // Calculate progress based on status
                                    $progress_map = [
                                        'draft' => 20,
                                        'submitted' => 40,
                                        'review_pembimbing' => 50,
                                        'approved_pembimbing' => 70,
                                        'review_staf' => 80,
                                        'completed' => 100,
                                        'rejected' => 30
                                    ];
                                    $progress = $progress_map[$pub->status] ?? 10;
                                    $progress_color = $pub->status == 'completed' ? 'success' : ($pub->status == 'rejected' ? 'danger' : 'info');
                                    ?>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-<?= $progress_color ?>" 
                                             role="progressbar" 
                                             style="width: <?= $progress ?>%"
                                             aria-valuenow="<?= $progress ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= $progress ?>%</small>
                                </td>
                                <td>
                                    <span class="text-sm">
                                        <?= date('d/m/Y', strtotime($pub->tanggal_pengajuan ?? $pub->created_at)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= base_url('kaprodi/publikasi/detail/' . $pub->id) ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Detail">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('kaprodi/publikasi/tracking/' . $pub->id) ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Tracking Progress">
                                            <i class="fa fa-route"></i>
                                        </a>
                                        <?php if (in_array($pub->status, ['review_pembimbing', 'review_staf', 'rejected'])): ?>
                                            <a href="<?= base_url('kaprodi/publikasi/override/' . $pub->id) ?>" 
                                               class="btn btn-sm btn-warning" 
                                               title="Override">
                                                <i class="fa fa-shield-alt"></i>
                                            </a>
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

<!-- Info Card -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-white">
                            <i class="fa fa-info-circle mr-2"></i>
                            Informasi Monitoring Publikasi untuk Kaprodi
                        </h5>
                        <p class="card-text text-white-75 mb-0">
                            <strong>Peran Kaprodi:</strong> Memantau proses publikasi mahasiswa dari pengajuan hingga selesai • 
                            <strong>Override:</strong> Dapat melakukan override keputusan dalam situasi darurat • 
                            <strong>Laporan:</strong> Mengakses laporan dan statistik lengkap publikasi di prodi
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-primary rounded-circle shadow">
                            <i class="fa fa-crown"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Tangkap semua HTML di atas ke dalam variabel $content
$content = ob_get_clean();

// Mulai buffer baru untuk skrip
ob_start();
?>

<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    if (typeof $.fn.DataTable !== 'undefined') {
        var table = $('#datatable-publikasi').DataTable({
            "order": [[ 6, "desc" ]], // Urutkan berdasarkan tanggal terbaru
            "pageLength": 25,
            "responsive": true,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ publikasi",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
        
        // Filter berdasarkan status
        $('#filter-status').on('change', function() {
            var status = $(this).val();
            if (status === '') {
                table.column(4).search('').draw();
            } else {
                table.column(4).search(status).draw();
            }
        });
    }
    
    // Inisialisasi tooltip
    if (typeof $().tooltip !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Auto refresh setiap 5 menit untuk update status real-time
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 menit
});
</script>

<?php
// Tangkap skrip
$script = ob_get_clean();

// JANGAN panggil template di sini - akan dipanggil oleh controller
// Return content dan script untuk digunakan oleh template
echo $content;
?>