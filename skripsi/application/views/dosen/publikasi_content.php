<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- CSS untuk spacing yang optimal -->
<style>
.content-spacing {
    margin-bottom: 40px; /* Reduced spacing */
}

.card {
    margin-bottom: 1rem; /* Reduced from 1.5rem */
}

.card-stats {
    margin-bottom: 1rem; /* Consistent spacing */
}

.row {
    margin-bottom: 1.5rem; /* Consistent row spacing */
}

.table-responsive {
    margin-bottom: 1rem; /* Reduced margin after tables */
}

.info-section {
    margin-bottom: 30px !important; /* Optimal final section margin */
}

/* Remove excessive padding */
.main-content {
    padding-bottom: 60px; /* Reasonable bottom padding */
}

/* Mobile responsive */
@media (max-width: 768px) {
    .content-spacing { margin-bottom: 30px; }
    .info-section { margin-bottom: 20px !important; }
    .main-content { padding-bottom: 40px; }
}
</style>

<div class="content-spacing">

<!-- Alert Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-check"></i></span>
    <span class="alert-text"><?php echo $this->session->flashdata('success'); ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?php echo $this->session->flashdata('error'); ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('info')): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-info-circle"></i></span>
    <span class="alert-text"><?php echo $this->session->flashdata('info'); ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-3">
        <div class="card bg-gradient-success">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Publikasi Tugas Akhir - Phase 6</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Workflow Phase 6 (Final):</strong> Tahap terakhir dari proses bimbingan. 
                            Berikan rekomendasi untuk publikasi tugas akhir mahasiswa ke repository institusi.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                            <i class="fa fa-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Pengajuan</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            if(isset($publikasi_list) && !empty($publikasi_list)) {
                                echo count($publikasi_list);
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-book"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                    <span class="text-nowrap">Publikasi tugas akhir</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Perlu Rekomendasi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $pending = 0;
                            if(isset($publikasi_list) && !empty($publikasi_list)) {
                                foreach($publikasi_list as $pub) {
                                    if(!isset($pub->rekomendasi_pembimbing) || is_null($pub->rekomendasi_pembimbing)) $pending++;
                                }
                            }
                            echo $pending;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i></span>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Direkomendasikan</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $approved = 0;
                            if(isset($publikasi_list) && !empty($publikasi_list)) {
                                foreach($publikasi_list as $pub) {
                                    if(isset($pub->rekomendasi_pembimbing) && $pub->rekomendasi_pembimbing == '1') $approved++;
                                }
                            }
                            echo $approved;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="fa fa-thumbs-up"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-primary mr-2"><i class="fa fa-check"></i></span>
                    <span class="text-nowrap">Sudah disetujui</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Sudah Publikasi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $published = 0;
                            if(isset($publikasi_list) && !empty($publikasi_list)) {
                                foreach($publikasi_list as $pub) {
                                    if(isset($pub->link_repository) && !empty($pub->link_repository)) $published++;
                                }
                            }
                            echo $published;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-globe"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-globe"></i></span>
                    <span class="text-nowrap">Di repository</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Publikasi Tugas Akhir -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Pengajuan Publikasi Tugas Akhir</h3>
                        <p class="mb-0 text-sm">Daftar mahasiswa yang mengajukan publikasi tugas akhir</p>
                    </div>
                    <div class="col text-right">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="fa fa-filter"></i> Filter Status
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="filterTable('all')">Semua Status</a>
                                <a class="dropdown-item" href="#" onclick="filterTable('pending')">Menunggu Rekomendasi</a>
                                <a class="dropdown-item" href="#" onclick="filterTable('approved')">Direkomendasikan</a>
                                <a class="dropdown-item" href="#" onclick="filterTable('revision')">Perlu Perbaikan</a>
                                <a class="dropdown-item" href="#" onclick="filterTable('published')">Sudah Dipublikasi</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="publikasiTable">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Tugas Akhir</th>
                            <th scope="col">Dokumen</th>
                            <th scope="col">Repository</th>
                            <th scope="col">Status Rekomendasi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($publikasi_list) && !empty($publikasi_list)): ?>
                            <?php foreach($publikasi_list as $pub): ?>
                            <tr data-status="<?php 
                                if(!isset($pub->rekomendasi_pembimbing) || is_null($pub->rekomendasi_pembimbing)) echo 'pending';
                                elseif($pub->rekomendasi_pembimbing == '1') echo 'approved';
                                elseif($pub->rekomendasi_pembimbing == '2') echo 'revision';
                                if(isset($pub->link_repository) && !empty($pub->link_repository)) echo ' published';
                            ?>">
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?php echo isset($pub->nama_mahasiswa) ? $pub->nama_mahasiswa : 'N/A'; ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?php echo isset($pub->nim) ? $pub->nim : 'N/A'; ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo isset($pub->nama_prodi) ? $pub->nama_prodi : 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(isset($pub->judul)): ?>
                                        <span class="text-sm font-weight-bold"><?php echo substr($pub->judul, 0, 50) . (strlen($pub->judul) > 50 ? '...' : ''); ?></span>
                                        <br>
                                    <?php endif; ?>
                                    
                                    <small class="text-muted">
                                        <i class="fa fa-calendar"></i> Pengajuan: <?php echo isset($pub->created_at) ? date('d/m/Y', strtotime($pub->created_at)) : 'N/A'; ?>
                                    </small>
                                    <br>
                                    
                                    <?php if(isset($pub->jenis_penelitian) && !empty($pub->jenis_penelitian)): ?>
                                    <span class="badge badge-pill badge-secondary"><?php echo $pub->jenis_penelitian; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($pub->file_skripsi_final) && !empty($pub->file_skripsi_final)): ?>
                                        <a href="<?php echo base_url('uploads/publikasi/' . $pub->file_skripsi_final); ?>" 
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fa fa-file-pdf"></i> Skripsi Final
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Belum Upload</span>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($pub->lembar_pengesahan) && !empty($pub->lembar_pengesahan)): ?>
                                        <br>
                                        <a href="<?php echo base_url('uploads/publikasi/' . $pub->lembar_pengesahan); ?>" 
                                           class="btn btn-sm btn-outline-success mt-1" target="_blank">
                                            <i class="fa fa-file-alt"></i> Pengesahan
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($pub->link_repository) && !empty($pub->link_repository)): ?>
                                        <a href="<?php echo $pub->link_repository; ?>" class="btn btn-sm btn-success" target="_blank">
                                            <i class="fa fa-external-link-alt"></i> Lihat Repository
                                        </a>
                                        <br>
                                        <small class="text-success">
                                            <i class="fa fa-check"></i> Sudah Dipublikasi
                                        </small>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum Dipublikasi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($pub->rekomendasi_pembimbing) && $pub->rekomendasi_pembimbing == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Direkomendasikan
                                        </span>
                                        <?php if(isset($pub->catatan_pembimbing) && !empty($pub->catatan_pembimbing)): ?>
                                        <br>
                                        <small class="text-muted" title="<?php echo $pub->catatan_pembimbing; ?>">
                                            <?php echo substr($pub->catatan_pembimbing, 0, 30) . '...'; ?>
                                        </small>
                                        <?php endif; ?>
                                    <?php elseif(isset($pub->rekomendasi_pembimbing) && $pub->rekomendasi_pembimbing == '2'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-edit"></i> Perlu Perbaikan
                                        </span>
                                        <?php if(isset($pub->catatan_pembimbing) && !empty($pub->catatan_pembimbing)): ?>
                                        <br>
                                        <small class="text-warning" title="<?php echo $pub->catatan_pembimbing; ?>">
                                            <?php echo substr($pub->catatan_pembimbing, 0, 30) . '...'; ?>
                                        </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-info">
                                            <i class="fa fa-clock"></i> Menunggu Rekomendasi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <?php if(isset($pub->id)): ?>
                                            <a class="dropdown-item" href="<?php echo base_url('dosen/publikasi/detail/' . $pub->id); ?>">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                            <?php if(!isset($pub->rekomendasi_pembimbing) || is_null($pub->rekomendasi_pembimbing)): ?>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?php echo $pub->id; ?>, 1, '<?php echo addslashes($pub->nama_mahasiswa); ?>')">
                                                <i class="fa fa-check text-success"></i> Rekomendasikan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?php echo $pub->id; ?>, 2, '<?php echo addslashes($pub->nama_mahasiswa); ?>')">
                                                <i class="fa fa-edit text-warning"></i> Minta Perbaikan
                                            </a>
                                            <?php endif; ?>
                                            <?php if(isset($pub->link_repository) && !empty($pub->link_repository)): ?>
                                            <a class="dropdown-item" href="<?php echo $pub->link_repository; ?>" target="_blank">
                                                <i class="fa fa-external-link-alt text-info"></i> Buka Repository
                                            </a>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-center">
                                        <i class="fa fa-book fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada pengajuan publikasi</h5>
                                        <p class="text-muted">Pengajuan publikasi tugas akhir dari mahasiswa bimbingan akan muncul di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Info Card - Tips Section -->
<div class="row info-section">
    <div class="col-lg-12">
        <div class="card bg-gradient-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="icon icon-shape icon-lg bg-gradient-success text-white rounded-circle shadow">
                            <i class="fa fa-info"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1">Tahap Akhir Proses Bimbingan</h5>
                        <p class="mb-0 text-sm">
                            Publikasi tugas akhir adalah tahap terakhir dari proses bimbingan. Setelah mahasiswa lulus ujian skripsi dan 
                            melengkapi semua persyaratan, mereka dapat mengajukan publikasi ke repository institusi. 
                            Sebagai pembimbing, Anda berperan dalam memvalidasi kelengkapan dokumen dan memberikan persetujuan final.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Modal Rekomendasi -->
<div class="modal fade" id="modalRekomendasi" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?php echo base_url('dosen/publikasi/rekomendasi'); ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Rekomendasi Publikasi Tugas Akhir</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="publikasi_id" id="modal_publikasi_id">
                    <input type="hidden" name="rekomendasi" id="modal_rekomendasi">
                    
                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <input type="text" class="form-control" id="modal_nama_mahasiswa" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Status Rekomendasi</label>
                        <div id="modal_status_text" class="alert"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Pembimbing *</label>
                        <textarea class="form-control" name="catatan_pembimbing" rows="4" required 
                                  placeholder="Berikan catatan, evaluasi, atau instruksi terkait publikasi tugas akhir"></textarea>
                        <small class="form-text text-muted">
                            Catatan ini akan membantu mahasiswa dan staf dalam proses publikasi ke repository.
                        </small>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fa fa-info-circle"></i> Checklist Publikasi:</h6>
                        <ul class="mb-0">
                            <li>Skripsi final sudah sesuai dengan hasil revisi ujian</li>
                            <li>Lembar pengesahan telah ditandatangani lengkap</li>
                            <li>Format dokumen sesuai template institusi</li>
                            <li>Tidak ada pelanggaran hak cipta atau plagiarisme</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="modal_submit_btn">Simpan Rekomendasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Berikan Rekomendasi
function beriRekomendasi(publikasiId, status, namaMahasiswa) {
    if (document.getElementById('modal_publikasi_id')) {
        document.getElementById('modal_publikasi_id').value = publikasiId;
        document.getElementById('modal_rekomendasi').value = status;
        document.getElementById('modal_nama_mahasiswa').value = namaMahasiswa;
        
        const statusText = document.getElementById('modal_status_text');
        const submitBtn = document.getElementById('modal_submit_btn');
        
        if (status == 1) {
            statusText.className = 'alert alert-success';
            statusText.innerHTML = '<i class="fa fa-check"></i> <strong>Direkomendasikan untuk Publikasi</strong><br>Tugas akhir mahasiswa dapat dipublikasikan ke repository institusi';
            submitBtn.className = 'btn btn-success';
            submitBtn.innerHTML = '<i class="fa fa-check"></i> Rekomendasikan Publikasi';
        } else {
            statusText.className = 'alert alert-warning';
            statusText.innerHTML = '<i class="fa fa-edit"></i> <strong>Perlu Perbaikan Dokumen</strong><br>Dokumen perlu diperbaiki sebelum dapat dipublikasikan';
            submitBtn.className = 'btn btn-warning';
            submitBtn.innerHTML = '<i class="fa fa-edit"></i> Minta Perbaikan';
        }
        
        jQuery('#modalRekomendasi').modal('show');
    }
}

// Filter Table
function filterTable(status) {
    const table = document.getElementById('publikasiTable');
    if (table) {
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const rowStatus = row.getAttribute('data-status');
            
            if (status === 'all') {
                row.style.display = '';
            } else if (status === 'published' && rowStatus && rowStatus.includes('published')) {
                row.style.display = '';
            } else if (rowStatus && rowStatus.includes(status)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }
}

// Document ready
jQuery(document).ready(function($) {
    // Initialize tooltips
    $('[title]').tooltip();
    
    // Modal event handling
    $('#modalRekomendasi').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
</script>