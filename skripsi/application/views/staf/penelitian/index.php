<?php 
/**
 * View Staf Penelitian - Fixed Version (Mengatasi Error Script)
 * File: application/views/staf/penelitian/index.php
 * ERROR FIXED: Hapus pemanggilan index_script.php yang tidak ada
 */

ob_start(); 
?>

<!-- Header Stats -->
<div class="row">
    <div class="col-xl-3 col-lg-6">
        <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Penelitian</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $stats['total_penelitian'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="fas fa-microscope"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Butuh Surat Izin</h5>
                        <span class="h2 font-weight-bold mb-0 text-warning"><?= $stats['butuh_surat_izin'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fas fa-file-signature"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Sudah Ada Surat</h5>
                        <span class="h2 font-weight-bold mb-0 text-success"><?= $stats['sudah_ada_surat'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6">
        <div class="card card-stats mb-4 mb-xl-0">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Bulan Ini</h5>
                        <span class="h2 font-weight-bold mb-0 text-info"><?= $stats['bulan_ini'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col">
        <!-- Filter Section -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-search text-primary"></i>
                            Filter Data Penelitian
                        </h3>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url('staf/penelitian/download_template') ?>" 
                           class="btn btn-sm btn-info" target="_blank">
                            <i class="fas fa-download"></i> Download Template Surat
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= base_url('staf/penelitian') ?>">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-control-label" for="prodi_id">Program Studi</label>
                                <select class="form-control" name="prodi_id" id="prodi_id">
                                    <option value="">Semua Prodi</option>
                                    <?php foreach($prodi_list as $prodi): ?>
                                        <option value="<?= $prodi->id ?>" <?= ($filters['prodi_id'] == $prodi->id) ? 'selected' : '' ?>>
                                            <?= $prodi->nama ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-control-label" for="status_izin">Status Izin</label>
                                <select class="form-control" name="status_izin" id="status_izin">
                                    <option value="">Semua Status</option>
                                    <option value="0" <?= ($filters['status_izin'] === '0') ? 'selected' : '' ?>>Butuh Surat Izin</option>
                                    <option value="1" <?= ($filters['status_izin'] === '1') ? 'selected' : '' ?>>Sudah Ada Surat</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="search">Cari mahasiswa/lokasi...</label>
                                <input type="text" class="form-control" name="search" id="search" 
                                       placeholder="Nama, NIM, judul, atau lokasi penelitian"
                                       value="<?= $filters['search'] ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-control-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="<?= base_url('staf/penelitian') ?>" class="btn btn-secondary">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col">
        <!-- Tabel Data Penelitian -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-list text-primary"></i>
                    Daftar Penelitian Mahasiswa
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Prodi</th>
                                <th>Judul</th>
                                <th>Lokasi Penelitian</th>
                                <th>Pembimbing</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($penelitian)): ?>
                                <?php foreach($penelitian as $p): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?= $p->nama_mahasiswa ?></strong><br>
                                                <small class="text-muted"><?= $p->nim ?></small>
                                            </div>
                                        </td>
                                        <td><?= $p->nama_prodi ?></td>
                                        <td>
                                            <div style="max-width: 300px;">
                                                <?= character_limiter($p->judul, 80) ?>
                                            </div>
                                        </td>
                                        <td><?= $p->lokasi_penelitian ?: '-' ?></td>
                                        <td><?= $p->nama_pembimbing ?: '-' ?></td>
                                        <td>
                                            <?php 
                                            $status_class = '';
                                            $status_text = '';
                                            $status_icon = '';
                                            
                                            switch($p->action_status) {
                                                case 'butuh_surat':
                                                    $status_class = 'badge-warning';
                                                    $status_text = 'Butuh Surat Izin';
                                                    $status_icon = 'fas fa-exclamation-triangle';
                                                    break;
                                                case 'surat_ready':
                                                    $status_class = 'badge-success';
                                                    $status_text = 'Surat Sudah Ready';
                                                    $status_icon = 'fas fa-check-circle';
                                                    break;
                                                case 'menunggu_pembimbing':
                                                    $status_class = 'badge-info';
                                                    $status_text = 'Menunggu Pembimbing';
                                                    $status_icon = 'fas fa-clock';
                                                    break;
                                                case 'ditolak_pembimbing':
                                                    $status_class = 'badge-danger';
                                                    $status_text = 'Ditolak Pembimbing';
                                                    $status_icon = 'fas fa-times-circle';
                                                    break;
                                                case 'belum_mengajukan':
                                                    $status_class = 'badge-secondary';
                                                    $status_text = 'Belum Mengajukan';
                                                    $status_icon = 'fas fa-minus-circle';
                                                    break;
                                                case 'review_pembimbing':
                                                    $status_class = 'badge-primary';
                                                    $status_text = 'Review Pembimbing';
                                                    $status_icon = 'fas fa-eye';
                                                    break;
                                                default:
                                                    $status_class = 'badge-light';
                                                    $status_text = 'No Action';
                                                    $status_icon = 'fas fa-question';
                                            }
                                            ?>
                                            <span class="badge <?= $status_class ?>">
                                                <i class="<?= $status_icon ?>"></i>
                                                <?= $status_text ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($p->tanggal_review_pembimbing): ?>
                                                <?= date('d/m/Y', strtotime($p->tanggal_review_pembimbing)) ?>
                                            <?php else: ?>
                                                <?= date('d/m/Y', strtotime($p->created_at)) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- ACTION BUTTONS - Ini yang missing di dashboard sebelumnya -->
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                        type="button" data-toggle="dropdown">
                                                    <i class="fas fa-cog"></i> Aksi
                                                </button>
                                                <div class="dropdown-menu">
                                                    <!-- Detail selalu tersedia -->
                                                    <a class="dropdown-item" href="<?= base_url('staf/penelitian/detail/' . $p->id) ?>">
                                                        <i class="fas fa-eye text-info"></i> Lihat Detail
                                                    </a>
                                                    
                                                    <div class="dropdown-divider"></div>
                                                    
                                                    <?php if($p->action_status == 'butuh_surat'): ?>
                                                        <!-- Mahasiswa yang perlu diproses surat izin -->
                                                        <a class="dropdown-item" 
                                                           href="<?= base_url('staf/penelitian/cetak_form_permohonan/' . $p->id) ?>" 
                                                           target="_blank">
                                                            <i class="fas fa-file-alt text-primary"></i> Cetak Form Permohonan
                                                        </a>
                                                        <a class="dropdown-item" 
                                                           href="<?= base_url('staf/penelitian/cetak_surat/' . $p->id) ?>" 
                                                           target="_blank">
                                                            <i class="fas fa-print text-success"></i> Cetak Surat Izin
                                                        </a>
                                                        <a class="dropdown-item" 
                                                           href="#" onclick="uploadSurat(<?= $p->id ?>)">
                                                            <i class="fas fa-upload text-warning"></i> Upload Surat Bertanda Tangan
                                                        </a>
                                                        
                                                    <?php elseif($p->action_status == 'surat_ready'): ?>
                                                        <!-- Surat sudah ready -->
                                                        <a class="dropdown-item" 
                                                           href="<?= base_url('staf/penelitian/download_surat/' . $p->id) ?>" 
                                                           target="_blank">
                                                            <i class="fas fa-download text-success"></i> Download Surat
                                                        </a>
                                                        <a class="dropdown-item" 
                                                           href="<?= base_url('staf/penelitian/cetak_surat/' . $p->id) ?>" 
                                                           target="_blank">
                                                            <i class="fas fa-print text-primary"></i> Cetak Ulang Surat
                                                        </a>
                                                        
                                                    <?php elseif($p->action_status == 'menunggu_pembimbing'): ?>
                                                        <!-- Masih menunggu approval pembimbing -->
                                                        <span class="dropdown-item-text">
                                                            <i class="fas fa-clock text-info"></i> Menunggu approval pembimbing
                                                        </span>
                                                        
                                                    <?php elseif($p->action_status == 'ditolak_pembimbing'): ?>
                                                        <!-- Ditolak pembimbing -->
                                                        <span class="dropdown-item-text">
                                                            <i class="fas fa-times text-danger"></i> Ditolak pembimbing
                                                        </span>
                                                        
                                                    <?php elseif($p->action_status == 'belum_mengajukan'): ?>
                                                        <!-- Belum mengajukan -->
                                                        <span class="dropdown-item-text">
                                                            <i class="fas fa-info text-secondary"></i> Mahasiswa belum mengajukan
                                                        </span>
                                                        
                                                    <?php else: ?>
                                                        <!-- Status lain -->
                                                        <span class="dropdown-item-text">
                                                            <i class="fas fa-info text-secondary"></i> Tidak ada aksi tersedia
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <div class="dropdown-divider"></div>
                                                    
                                                    <!-- Log aktivitas selalu tersedia -->
                                                    <a class="dropdown-item" 
                                                       href="<?= base_url('staf/penelitian/log_aktivitas/' . $p->id) ?>">
                                                        <i class="fas fa-history text-secondary"></i> Log Aktivitas
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="mb-3">
                                            <i class="fas fa-search text-muted" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="text-muted">Tidak ada data penelitian</h5>
                                        <p class="text-muted">Belum ada mahasiswa yang masuk tahap penelitian atau sesuai filter yang dipilih.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Surat -->
<div class="modal fade" id="modalUploadSurat" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload text-warning"></i>
                    Upload Surat Izin Bertanda Tangan
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formUploadSurat" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Upload file surat izin penelitian yang sudah ditandatangani pihak berwenang.
                    </div>
                    
                    <div class="form-group">
                        <label>File Surat (PDF, max 2MB) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file_surat" accept=".pdf" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-file-pdf text-danger"></i>
                            Hanya file PDF dengan ukuran maksimal 2MB
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan (Opsional)</label>
                        <textarea class="form-control" name="keterangan" rows="3" 
                                  placeholder="Tambahkan keterangan jika diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Surat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * JavaScript untuk Upload Surat dan fungsi lainnya
 */

function uploadSurat(proposalId) {
    $('#formUploadSurat').attr('action', '<?= base_url("staf/penelitian/upload_surat/") ?>' + proposalId);
    $('#modalUploadSurat').modal('show');
}

// Handle form submit upload surat
$('#formUploadSurat').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    var actionUrl = $(this).attr('action');
    
    // Show loading
    var submitBtn = $(this).find('button[type="submit"]');
    var originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Uploading...').prop('disabled', true);
    
    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.status == 'success') {
                alert('✅ ' + response.message);
                location.reload();
            } else {
                alert('❌ Error: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Terjadi kesalahan saat upload file');
        },
        complete: function() {
            // Restore button
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

// Filter form enhancement
$(document).ready(function() {
    // Auto submit on select change
    $('#prodi_id, #status_izin').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Search on enter key
    $('#search').on('keypress', function(e) {
        if (e.which == 13) {
            $(this).closest('form').submit();
        }
    });
    
    // Tooltip initialization jika menggunakan Bootstrap tooltip
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?php 
$content = ob_get_clean();
$this->load->view('template/staf', [
    'title' => 'Manajemen Penelitian',
    'content' => $content,
    'css' => '',
    'script' => '' // FIXED: Hapus pemanggilan script yang tidak ada
]);
?>