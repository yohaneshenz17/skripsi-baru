<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- CSS untuk spacing yang optimal -->
<style>
.content-spacing {
    margin-bottom: 40px; /* Reduced from 5rem */
}

.card {
    margin-bottom: 1rem; /* Reduced from 1.5rem */
}

.card-stats {
    margin-bottom: 1rem; /* Reduced spacing between stat cards */
}

.row {
    margin-bottom: 1.5rem; /* Consistent spacing between rows */
}

.table-responsive {
    margin-bottom: 1rem; /* Reduced margin after tables */
}

.info-section {
    margin-bottom: 30px !important; /* Reduced final section margin */
}

/* Remove excessive padding and min-height */
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
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Seminar Proposal - Phase 3</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Workflow Phase 3:</strong> Berikan rekomendasi untuk pengajuan seminar proposal mahasiswa. 
                            Sebagai pembimbing atau penguji, Anda dapat memberikan penilaian dan catatan perbaikan.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-primary rounded-circle shadow">
                            <i class="fa fa-presentation"></i>
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
                            if(isset($seminar_proposals) && !empty($seminar_proposals)) {
                                echo count($seminar_proposals);
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="fa fa-presentation"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                    <span class="text-nowrap">Sebagai pembimbing</span>
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
                            if(isset($seminar_proposals) && !empty($seminar_proposals)) {
                                foreach($seminar_proposals as $sp) {
                                    if(!isset($sp->rekomendasi_pembimbing) || is_null($sp->rekomendasi_pembimbing)) $pending++;
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
                            if(isset($seminar_proposals) && !empty($seminar_proposals)) {
                                foreach($seminar_proposals as $sp) {
                                    if(isset($sp->rekomendasi_pembimbing) && $sp->rekomendasi_pembimbing == '1') $approved++;
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Sebagai Penguji</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            if(isset($jadwal_sebagai_penguji) && !empty($jadwal_sebagai_penguji)) {
                                echo count($jadwal_sebagai_penguji);
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-gavel"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-user-check"></i></span>
                    <span class="text-nowrap">Jadwal menguji</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Pengajuan Seminar Proposal - Sebagai Pembimbing -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Pengajuan Seminar Proposal</h3>
                        <p class="mb-0 text-sm">Mahasiswa bimbingan yang mengajukan seminar proposal</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Tanggal Pengajuan</th>
                            <th scope="col">Status Rekomendasi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($seminar_proposals) && !empty($seminar_proposals)): ?>
                            <?php foreach($seminar_proposals as $sp): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?php echo isset($sp->nama_mahasiswa) ? $sp->nama_mahasiswa : 'N/A'; ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?php echo isset($sp->nim) ? $sp->nim : 'N/A'; ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo isset($sp->nama_prodi) ? $sp->nama_prodi : 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(isset($sp->judul)): ?>
                                        <span class="text-sm font-weight-bold"><?php echo substr($sp->judul, 0, 40) . '...'; ?></span>
                                        <br>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($sp->lokasi_penelitian) && !empty($sp->lokasi_penelitian)): ?>
                                    <small class="text-muted">
                                        <i class="fa fa-map-marker-alt"></i> <?php echo $sp->lokasi_penelitian; ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $tanggal = isset($sp->created_at) ? $sp->created_at : (isset($sp->updated_at) ? $sp->updated_at : date('Y-m-d'));
                                    ?>
                                    <span class="text-sm"><?php echo date('d/m/Y', strtotime($tanggal)); ?></span>
                                </td>
                                <td>
                                    <?php if(isset($sp->rekomendasi_pembimbing) && $sp->rekomendasi_pembimbing == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Direkomendasikan
                                        </span>
                                    <?php elseif(isset($sp->rekomendasi_pembimbing) && $sp->rekomendasi_pembimbing == '2'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-edit"></i> Perlu Perbaikan
                                        </span>
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
                                            <?php if(isset($sp->id)): ?>
                                            <a class="dropdown-item" href="<?php echo base_url('dosen/seminar_proposal/detail/' . $sp->id); ?>">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                            <?php if(!isset($sp->rekomendasi_pembimbing) || is_null($sp->rekomendasi_pembimbing)): ?>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?php echo $sp->id; ?>, 1, '<?php echo addslashes($sp->nama_mahasiswa); ?>')">
                                                <i class="fa fa-check text-success"></i> Rekomendasikan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?php echo $sp->id; ?>, 2, '<?php echo addslashes($sp->nama_mahasiswa); ?>')">
                                                <i class="fa fa-edit text-warning"></i> Minta Perbaikan
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
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-center">
                                        <i class="fa fa-presentation fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada pengajuan seminar proposal</h5>
                                        <p class="text-muted">Pengajuan seminar proposal dari mahasiswa bimbingan akan muncul di sini.</p>
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

<!-- Jadwal Seminar - Sebagai Penguji (jika ada) -->
<?php if(isset($jadwal_sebagai_penguji) && !empty($jadwal_sebagai_penguji)): ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Jadwal Seminar - Sebagai Penguji</h3>
                        <p class="mb-0 text-sm">Seminar proposal yang Anda bertugas sebagai penguji</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Pembimbing</th>
                            <th scope="col">Jadwal</th>
                            <th scope="col">Status Penilaian</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($jadwal_sebagai_penguji as $jadwal): ?>
                        <tr>
                            <td>
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm font-weight-bold"><?php echo isset($jadwal->nama_mahasiswa) ? $jadwal->nama_mahasiswa : 'N/A'; ?></span>
                                        <br>
                                        <small class="text-muted">NIM: <?php echo isset($jadwal->nim) ? $jadwal->nim : 'N/A'; ?></small>
                                        <br>
                                        <?php if(isset($jadwal->judul)): ?>
                                        <small class="text-info"><?php echo substr($jadwal->judul, 0, 30) . '...'; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm"><?php echo isset($jadwal->nama_pembimbing) ? $jadwal->nama_pembimbing : 'N/A'; ?></span>
                            </td>
                            <td>
                                <?php if(isset($jadwal->tanggal_seminar)): ?>
                                <span class="badge badge-primary">
                                    <?php echo date('d/m/Y H:i', strtotime($jadwal->tanggal_seminar)); ?>
                                </span>
                                <br>
                                <small class="text-muted"><?php echo isset($jadwal->tempat_seminar) ? $jadwal->tempat_seminar : 'TBA'; ?></small>
                                <?php else: ?>
                                <span class="badge badge-secondary">Belum Dijadwalkan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $sudah_dinilai = false;
                                if(isset($jadwal->dosen_penguji_1) && $jadwal->dosen_penguji_1 == $this->session->userdata('id') && !is_null($jadwal->nilai_penguji_1)) {
                                    $sudah_dinilai = true;
                                } elseif(isset($jadwal->dosen_penguji_2) && $jadwal->dosen_penguji_2 == $this->session->userdata('id') && !is_null($jadwal->nilai_penguji_2)) {
                                    $sudah_dinilai = true;
                                }
                                ?>
                                
                                <?php if($sudah_dinilai): ?>
                                    <span class="badge badge-success">
                                        <i class="fa fa-check"></i> Sudah Dinilai
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <i class="fa fa-clock"></i> Belum Dinilai
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                        <?php if(isset($jadwal->id)): ?>
                                        <a class="dropdown-item" href="<?php echo base_url('dosen/seminar_proposal/detail/' . $jadwal->id); ?>">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                        <?php if(!$sudah_dinilai): ?>
                                        <a class="dropdown-item" href="#" onclick="inputNilai(<?php echo $jadwal->id; ?>)">
                                            <i class="fa fa-edit"></i> Input Nilai
                                        </a>
                                        <?php endif; ?>
                                        <a class="dropdown-item" href="<?php echo base_url('dosen/seminar_proposal/berita_acara/' . $jadwal->id); ?>">
                                            <i class="fa fa-file-alt"></i> Berita Acara
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Info Card - Tips Section (dengan spacing optimal) -->
<div class="row info-section">
    <div class="col-lg-12">
        <div class="card bg-gradient-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="icon icon-shape icon-lg bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fa fa-info"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1">Tahap Seminar Proposal</h5>
                        <p class="mb-0 text-sm">
                            Setelah mahasiswa menyelesaikan minimal 16x bimbingan dan proposal sudah lengkap (Bab 1-3), 
                            mereka dapat mengajukan seminar proposal. Sebagai pembimbing, Anda berperan dalam memberikan 
                            rekomendasi kelayakan untuk pelaksanaan seminar.
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?php echo base_url('dosen/seminar_proposal/rekomendasi'); ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Rekomendasi Seminar Proposal</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="modal_seminar_id">
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
                                  placeholder="Berikan catatan, saran, atau perbaikan yang diperlukan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="modal_submit_btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Berikan Rekomendasi
function beriRekomendasi(seminarId, status, namaMahasiswa) {
    if (document.getElementById('modal_seminar_id')) {
        document.getElementById('modal_seminar_id').value = seminarId;
        document.getElementById('modal_rekomendasi').value = status;
        document.getElementById('modal_nama_mahasiswa').value = namaMahasiswa;
        
        const statusText = document.getElementById('modal_status_text');
        const submitBtn = document.getElementById('modal_submit_btn');
        
        if (status == 1) {
            statusText.className = 'alert alert-success';
            statusText.innerHTML = '<i class="fa fa-check"></i> <strong>Direkomendasikan</strong> - Seminar proposal dapat dilaksanakan';
            submitBtn.className = 'btn btn-success';
            submitBtn.textContent = 'Rekomendasikan';
        } else {
            statusText.className = 'alert alert-warning';
            statusText.innerHTML = '<i class="fa fa-edit"></i> <strong>Perlu Perbaikan</strong> - Mahasiswa harus memperbaiki proposal terlebih dahulu';
            submitBtn.className = 'btn btn-warning';
            submitBtn.textContent = 'Minta Perbaikan';
        }
        
        jQuery('#modalRekomendasi').modal('show');
    }
}

// Input Nilai
function inputNilai(seminarId) {
    window.location.href = '<?php echo base_url('dosen/seminar_proposal/detail/'); ?>' + seminarId + '#input-nilai';
}

// Document ready
jQuery(document).ready(function($) {
    // Modal event handling
    $('#modalRekomendasi').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
</script>