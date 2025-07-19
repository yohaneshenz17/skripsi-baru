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

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-3">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Penelitian - Phase 4</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Workflow Phase 4:</strong> Kelola pengajuan surat izin penelitian mahasiswa bimbingan. 
                            Berikan rekomendasi untuk proses penelitian lapangan atau laboratorium.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fa fa-search"></i>
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
                            if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)) {
                                echo count($surat_izin_penelitian);
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-arrow-up"></i></span>
                    <span class="text-nowrap">Surat izin penelitian</span>
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
                            if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)) {
                                foreach($surat_izin_penelitian as $sip) {
                                    if(!isset($sip->rekomendasi_pembimbing) || is_null($sip->rekomendasi_pembimbing)) $pending++;
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
                            if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)) {
                                foreach($surat_izin_penelitian as $sip) {
                                    if(isset($sip->rekomendasi_pembimbing) && $sip->rekomendasi_pembimbing == '1') $approved++;
                                }
                            }
                            echo $approved;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-check"></i></span>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Sudah Diproses</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $processed = 0;
                            if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)) {
                                foreach($surat_izin_penelitian as $sip) {
                                    if(isset($sip->surat_izin_file) && !empty($sip->surat_izin_file)) $processed++;
                                }
                            }
                            echo $processed;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="fa fa-file-pdf"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-primary mr-2"><i class="fa fa-file"></i></span>
                    <span class="text-nowrap">Surat tersedia</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Surat Izin Penelitian -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Pengajuan Surat Izin Penelitian</h3>
                        <p class="mb-0 text-sm">Mahasiswa bimbingan yang mengajukan surat izin penelitian</p>
                    </div>
                    <div class="col text-right">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="fa fa-filter"></i> Filter Status
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="filterByStatus('all')">Semua Status</a>
                                <a class="dropdown-item" href="#" onclick="filterByStatus('pending')">Menunggu Rekomendasi</a>
                                <a class="dropdown-item" href="#" onclick="filterByStatus('approved')">Direkomendasikan</a>
                                <a class="dropdown-item" href="#" onclick="filterByStatus('revision')">Perlu Perbaikan</a>
                                <a class="dropdown-item" href="#" onclick="filterByStatus('processed')">Sudah Diproses</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="penelitianTable">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Penelitian</th>
                            <th scope="col">Lokasi Penelitian</th>
                            <th scope="col">Tujuan Penelitian</th>
                            <th scope="col">Status Rekomendasi</th>
                            <th scope="col">Surat Izin</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)): ?>
                            <?php foreach($surat_izin_penelitian as $sip): ?>
                            <tr data-status="<?php 
                                if(!isset($sip->rekomendasi_pembimbing) || is_null($sip->rekomendasi_pembimbing)) echo 'pending';
                                elseif($sip->rekomendasi_pembimbing == '1') echo 'approved';
                                elseif($sip->rekomendasi_pembimbing == '2') echo 'revision';
                                if(isset($sip->surat_izin_file) && !empty($sip->surat_izin_file)) echo ' processed';
                            ?>">
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?php echo isset($sip->nama_mahasiswa) ? $sip->nama_mahasiswa : 'N/A'; ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?php echo isset($sip->nim) ? $sip->nim : 'N/A'; ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo isset($sip->nama_prodi) ? $sip->nama_prodi : 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(isset($sip->judul)): ?>
                                        <span class="text-sm font-weight-bold"><?php echo substr($sip->judul, 0, 40) . '...'; ?></span>
                                        <br>
                                    <?php endif; ?>
                                    
                                    <small class="text-muted">
                                        <i class="fa fa-calendar"></i> Pengajuan: <?php echo isset($sip->created_at) ? date('d/m/Y', strtotime($sip->created_at)) : 'N/A'; ?>
                                    </small>
                                    <br>
                                    
                                    <?php if(isset($sip->jenis_penelitian) && !empty($sip->jenis_penelitian)): ?>
                                    <span class="badge badge-pill badge-secondary"><?php echo $sip->jenis_penelitian; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold"><?php echo isset($sip->lokasi_penelitian) ? $sip->lokasi_penelitian : 'N/A'; ?></span>
                                    <?php if(isset($sip->alamat_penelitian) && !empty($sip->alamat_penelitian)): ?>
                                    <br>
                                    <small class="text-muted"><?php echo substr($sip->alamat_penelitian, 0, 30) . '...'; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($sip->tujuan_penelitian) && !empty($sip->tujuan_penelitian)): ?>
                                        <span class="text-sm"><?php echo substr($sip->tujuan_penelitian, 0, 50) . '...'; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($sip->rekomendasi_pembimbing) && $sip->rekomendasi_pembimbing == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Direkomendasikan
                                        </span>
                                        <?php if(isset($sip->tanggal_rekomendasi) && !empty($sip->tanggal_rekomendasi)): ?>
                                        <br>
                                        <small class="text-success"><?php echo date('d/m/Y', strtotime($sip->tanggal_rekomendasi)); ?></small>
                                        <?php endif; ?>
                                    <?php elseif(isset($sip->rekomendasi_pembimbing) && $sip->rekomendasi_pembimbing == '2'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-edit"></i> Perlu Perbaikan
                                        </span>
                                        <?php if(isset($sip->catatan_pembimbing) && !empty($sip->catatan_pembimbing)): ?>
                                        <br>
                                        <small class="text-warning" title="<?php echo $sip->catatan_pembimbing; ?>">
                                            <?php echo substr($sip->catatan_pembimbing, 0, 20) . '...'; ?>
                                        </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-info">
                                            <i class="fa fa-clock"></i> Menunggu Rekomendasi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($sip->surat_izin_file) && !empty($sip->surat_izin_file)): ?>
                                        <a href="<?php echo base_url('uploads/penelitian/' . $sip->surat_izin_file); ?>" 
                                           class="btn btn-sm btn-success" target="_blank">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                        <br>
                                        <small class="text-success">
                                            <i class="fa fa-check"></i> Sudah Diproses
                                        </small>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum Diproses</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <?php if(isset($sip->id)): ?>
                                            <a class="dropdown-item" href="<?php echo base_url('dosen/penelitian/detail/' . $sip->id); ?>">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                            <?php if(!isset($sip->rekomendasi_pembimbing) || is_null($sip->rekomendasi_pembimbing)): ?>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?php echo $sip->id; ?>, 1, '<?php echo addslashes($sip->nama_mahasiswa); ?>')">
                                                <i class="fa fa-check text-success"></i> Rekomendasikan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?php echo $sip->id; ?>, 2, '<?php echo addslashes($sip->nama_mahasiswa); ?>')">
                                                <i class="fa fa-edit text-warning"></i> Minta Perbaikan
                                            </a>
                                            <?php endif; ?>
                                            <?php if(isset($sip->surat_izin_file) && !empty($sip->surat_izin_file)): ?>
                                            <a class="dropdown-item" href="<?php echo base_url('uploads/penelitian/' . $sip->surat_izin_file); ?>" target="_blank">
                                                <i class="fa fa-download text-info"></i> Download Surat
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
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-center">
                                        <i class="fa fa-search fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada pengajuan surat izin penelitian</h5>
                                        <p class="text-muted">Pengajuan surat izin penelitian dari mahasiswa bimbingan akan muncul di sini.</p>
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
                        <div class="icon icon-shape icon-lg bg-gradient-info text-white rounded-circle shadow">
                            <i class="fa fa-info"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1">Tahap Penelitian Lapangan</h5>
                        <p class="mb-0 text-sm">
                            Setelah mahasiswa menyelesaikan seminar proposal dan mendapatkan persetujuan, mereka perlu mengajukan 
                            surat izin penelitian untuk melakukan penelitian lapangan atau laboratorium. Sebagai pembimbing, 
                            Anda berperan dalam memvalidasi kesiapan mahasiswa dan memberikan rekomendasi.
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
            <form action="<?php echo base_url('dosen/penelitian/rekomendasi'); ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Rekomendasi Surat Izin Penelitian</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="surat_id" id="modal_surat_id">
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
                                  placeholder="Berikan catatan, evaluasi, atau instruksi terkait penelitian mahasiswa"></textarea>
                        <small class="form-text text-muted">
                            Catatan ini akan membantu staf akademik dalam memproses surat izin penelitian.
                        </small>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="fa fa-info-circle"></i> Checklist Penelitian:</h6>
                        <ul class="mb-0">
                            <li>Proposal telah disetujui dalam seminar proposal</li>
                            <li>Mahasiswa telah memenuhi syarat minimal bimbingan</li>
                            <li>Lokasi dan metode penelitian sudah jelas</li>
                            <li>Mahasiswa siap melaksanakan penelitian lapangan</li>
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
function beriRekomendasi(suratId, status, namaMahasiswa) {
    if (document.getElementById('modal_surat_id')) {
        document.getElementById('modal_surat_id').value = suratId;
        document.getElementById('modal_rekomendasi').value = status;
        document.getElementById('modal_nama_mahasiswa').value = namaMahasiswa;
        
        const statusText = document.getElementById('modal_status_text');
        const submitBtn = document.getElementById('modal_submit_btn');
        
        if (status == 1) {
            statusText.className = 'alert alert-success';
            statusText.innerHTML = '<i class="fa fa-check"></i> <strong>Direkomendasikan</strong><br>Mahasiswa dapat melanjutkan ke tahap penelitian lapangan';
            submitBtn.className = 'btn btn-success';
            submitBtn.innerHTML = '<i class="fa fa-check"></i> Rekomendasikan';
        } else {
            statusText.className = 'alert alert-warning';
            statusText.innerHTML = '<i class="fa fa-edit"></i> <strong>Perlu Perbaikan</strong><br>Mahasiswa perlu memperbaiki persiapan penelitian';
            submitBtn.className = 'btn btn-warning';
            submitBtn.innerHTML = '<i class="fa fa-edit"></i> Minta Perbaikan';
        }
        
        jQuery('#modalRekomendasi').modal('show');
    }
}

// Filter by Status
function filterByStatus(status) {
    const table = document.getElementById('penelitianTable');
    if (table) {
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const rowStatus = row.getAttribute('data-status');
            
            if (status === 'all') {
                row.style.display = '';
            } else if (status === 'processed' && rowStatus && rowStatus.includes('processed')) {
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