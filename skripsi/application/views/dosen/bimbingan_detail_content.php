<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Alert Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-check"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('info')): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-info-circle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('info') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- PERBAIKAN: Hidden input untuk proposal ID -->
<input type="hidden" id="current_proposal_id" value="<?= isset($mahasiswa->proposal_id) ? $mahasiswa->proposal_id : (isset($mahasiswa->id) ? $mahasiswa->id : '') ?>">

<!-- Header -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">Detail Bimbingan: <?= isset($mahasiswa->nama_mahasiswa) ? $mahasiswa->nama_mahasiswa : 'N/A' ?></h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>NIM:</strong> <?= isset($mahasiswa->nim) ? $mahasiswa->nim : 'N/A' ?> | 
                            <strong>Prodi:</strong> <?= isset($mahasiswa->nama_prodi) ? $mahasiswa->nama_prodi : 'N/A' ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('dosen/bimbingan') ?>" class="btn btn-sm btn-neutral">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-sm btn-success" onclick="tambahJurnalBimbingan()">
                            <i class="fa fa-plus"></i> Tambah Jurnal
                        </button>
                        <?php if(isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
                        <button type="button" class="btn btn-sm btn-info" onclick="exportJurnal()" data-proposal-id="<?= isset($mahasiswa->proposal_id) ? $mahasiswa->proposal_id : (isset($mahasiswa->id) ? $mahasiswa->id : '') ?>">
                            <i class="fa fa-download"></i> Export PDF
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Row -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Pertemuan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= isset($total_bimbingan) ? $total_bimbingan : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-calendar"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Target: 16 pertemuan</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tervalidasi</h5>
                        <span class="h2 font-weight-bold mb-0"><?= isset($bimbingan_tervalidasi) ? $bimbingan_tervalidasi : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <?php 
                    $total_bimbingan = isset($total_bimbingan) ? $total_bimbingan : 0;
                    $bimbingan_tervalidasi = isset($bimbingan_tervalidasi) ? $bimbingan_tervalidasi : 0;
                    ?>
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 
                    <?= $total_bimbingan > 0 ? round(($bimbingan_tervalidasi/$total_bimbingan)*100, 1) : 0 ?>%</span>
                    <span class="text-nowrap">dari total</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Pending</h5>
                        <span class="h2 font-weight-bold mb-0"><?= isset($bimbingan_pending) ? $bimbingan_pending : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Perlu validasi</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Progress</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= $total_bimbingan >= 16 ? '100' : round(($total_bimbingan/16)*100, 1) ?>%
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="fa fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <?php if($total_bimbingan >= 16): ?>
                        <span class="text-success">Siap seminar proposal</span>
                    <?php else: ?>
                        <span class="text-nowrap">Kurang <?= 16 - $total_bimbingan ?> pertemuan</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Info Mahasiswa dan Proposal -->
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Info Mahasiswa</h3>
            </div>
            <div class="card-body">
                <div class="media align-items-center mb-3">
                    <div class="avatar avatar-lg rounded-circle bg-primary">
                        <i class="ni ni-single-02 text-white"></i>
                    </div>
                    <div class="media-body ml-3">
                        <h4 class="mb-0"><?= isset($mahasiswa->nama_mahasiswa) ? $mahasiswa->nama_mahasiswa : 'N/A' ?></h4>
                        <p class="text-muted mb-0">NIM: <?= isset($mahasiswa->nim) ? $mahasiswa->nim : 'N/A' ?></p>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="row">
                    <div class="col-12">
                        <strong>Program Studi:</strong>
                        <p class="text-muted"><?= isset($mahasiswa->nama_prodi) ? $mahasiswa->nama_prodi : 'N/A' ?></p>
                        
                        <strong>Email:</strong>
                        <p class="text-muted">
                            <i class="fa fa-envelope text-primary"></i> 
                            <?php if(isset($mahasiswa->email_mahasiswa) && !empty($mahasiswa->email_mahasiswa)): ?>
                            <a href="mailto:<?= $mahasiswa->email_mahasiswa ?>"><?= $mahasiswa->email_mahasiswa ?></a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </p>
                        
                        <strong>No. Telepon:</strong>
                        <p class="text-muted">
                            <i class="fa fa-phone text-primary"></i> 
                            <?php if(isset($mahasiswa->nomor_telepon) && !empty($mahasiswa->nomor_telepon)): ?>
                            <a href="tel:<?= $mahasiswa->nomor_telepon ?>"><?= $mahasiswa->nomor_telepon ?></a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </p>
                        
                        <strong>Alamat:</strong>
                        <p class="text-muted"><?= isset($mahasiswa->alamat) && !empty($mahasiswa->alamat) ? $mahasiswa->alamat : 'N/A' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Info Proposal</h3>
            </div>
            <div class="card-body">
                <strong>Judul:</strong>
                <h5 class="text-primary mb-3"><?= isset($mahasiswa->judul) ? $mahasiswa->judul : 'Belum ada judul' ?></h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Jenis Penelitian:</strong>
                        <p class="text-muted">
                            <?php if(isset($mahasiswa->jenis_penelitian) && !empty($mahasiswa->jenis_penelitian)): ?>
                            <span class="badge badge-secondary"><?= $mahasiswa->jenis_penelitian ?></span>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Lokasi Penelitian:</strong>
                        <p class="text-muted">
                            <?php if(isset($mahasiswa->lokasi_penelitian) && !empty($mahasiswa->lokasi_penelitian)): ?>
                            <i class="fa fa-map-marker-alt text-danger"></i> <?= $mahasiswa->lokasi_penelitian ?>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <?php if(isset($mahasiswa->uraian_masalah) && !empty($mahasiswa->uraian_masalah)): ?>
                <strong>Uraian Masalah:</strong>
                <div class="bg-light p-3 rounded">
                    <p class="text-dark mb-0"><?= nl2br($mahasiswa->uraian_masalah) ?></p>
                </div>
                <?php endif; ?>
                
                <hr class="my-3">
                
                <strong>Tanggal Proposal Diajukan:</strong>
                <p class="text-muted">
                    <?php if(isset($mahasiswa->created_at)): ?>
                    <?= date('d F Y', strtotime($mahasiswa->created_at)) ?>
                    <?php else: ?>
                    N/A
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Jurnal Bimbingan -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Jurnal Bimbingan</h3>
                        <p class="mb-0 text-sm">Riwayat pertemuan bimbingan dengan mahasiswa</p>
                    </div>
                    <div class="col text-right">
                        <button type="button" class="btn btn-sm btn-primary" onclick="tambahJurnalBimbingan()">
                            <i class="fa fa-plus"></i> Tambah Jurnal
                        </button>
                        <?php if(isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportJurnal()" data-proposal-id="<?= isset($mahasiswa->proposal_id) ? $mahasiswa->proposal_id : (isset($mahasiswa->id) ? $mahasiswa->id : '') ?>">
                            <i class="fa fa-download"></i> Export PDF
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Pertemuan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Materi Bimbingan</th>
                            <th scope="col">Catatan/Tindak Lanjut</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
                            <?php foreach($jurnal_bimbingan as $jurnal): ?>
                            <tr id="jurnal-row-<?= $jurnal->id ?>">
                                <td>
                                    <span class="badge badge-pill badge-primary">
                                        Ke-<?= $jurnal->pertemuan_ke ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm"><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></span>
                                    <br>
                                    <small class="text-muted"><?= date('H:i', strtotime($jurnal->created_at)) ?> WIT</small>
                                </td>
                                <td>
                                    <span class="text-sm"><?= $jurnal->materi_bimbingan ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($jurnal->catatan_dosen)): ?>
                                        <strong>Catatan:</strong><br>
                                        <span class="text-sm"><?= $jurnal->catatan_dosen ?></span><br>
                                    <?php endif; ?>
                                    <?php if(!empty($jurnal->tindak_lanjut)): ?>
                                        <strong>Tindak Lanjut:</strong><br>
                                        <span class="text-sm text-info"><?= $jurnal->tindak_lanjut ?></span>
                                    <?php endif; ?>
                                    <?php if(empty($jurnal->catatan_dosen) && empty($jurnal->tindak_lanjut)): ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($jurnal->status_validasi == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Tervalidasi
                                        </span>
                                        <?php if(!empty($jurnal->tanggal_validasi)): ?>
                                        <br>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($jurnal->tanggal_validasi)) ?></small>
                                        <?php endif; ?>
                                    <?php elseif($jurnal->status_validasi == '2'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-edit"></i> Perlu Revisi
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">
                                            <i class="fa fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <?php if($jurnal->status_validasi == '0'): ?>
                                            <a class="dropdown-item" href="#" onclick="validasiJurnal(<?= $jurnal->id ?>, 1)">
                                                <i class="fa fa-check text-success"></i> Validasi
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="validasiJurnal(<?= $jurnal->id ?>, 2)">
                                                <i class="fa fa-edit text-warning"></i> Minta Revisi
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <?php endif; ?>
                                            <a class="dropdown-item" href="#" onclick="editJurnal(<?= $jurnal->id ?>)">
                                                <i class="fa fa-edit text-info"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="deleteJurnal(<?= $jurnal->id ?>)">
                                                <i class="fa fa-trash text-danger"></i> Hapus
                                            </a>
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
                                        <h5 class="text-muted">Belum ada jurnal bimbingan</h5>
                                        <p class="text-muted">Mulai tambahkan jurnal bimbingan untuk mahasiswa ini.</p>
                                        <button type="button" class="btn btn-primary" onclick="tambahJurnalBimbingan()">
                                            <i class="fa fa-plus"></i> Tambah Jurnal Pertama
                                        </button>
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

<!-- Modal Tambah/Edit Jurnal -->
<div class="modal fade" id="modalJurnal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/bimbingan/tambah_jurnal') ?>" method="POST" id="formJurnal">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalJurnalTitle">Tambah Jurnal Bimbingan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="proposal_id" value="<?= isset($mahasiswa->proposal_id) ? $mahasiswa->proposal_id : (isset($mahasiswa->id) ? $mahasiswa->id : '') ?>">
                    <input type="hidden" name="jurnal_id" id="edit_jurnal_id" value="">
                    
                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <input type="text" class="form-control" value="<?= (isset($mahasiswa->nama_mahasiswa) ? $mahasiswa->nama_mahasiswa : 'N/A') . ' (' . (isset($mahasiswa->nim) ? $mahasiswa->nim : 'N/A') . ')' ?>" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pertemuan ke- *</label>
                                <input type="number" class="form-control" name="pertemuan_ke" id="input_pertemuan_ke" min="1" value="<?= (isset($total_bimbingan) ? $total_bimbingan : 0) + 1 ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" id="input_tanggal_bimbingan" value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" id="input_materi_bimbingan" rows="3" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Dosen</label>
                        <textarea class="form-control" name="catatan_dosen" id="input_catatan_dosen" rows="3" 
                                  placeholder="Catatan, saran, atau evaluasi dari dosen"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" id="input_tindak_lanjut" rows="2" 
                                  placeholder="Tugas atau tindak lanjut untuk mahasiswa"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitJurnalBtn">Simpan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Validasi Jurnal -->
<div class="modal fade" id="modalValidasi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/bimbingan/validasi_jurnal') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Validasi Jurnal Bimbingan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="jurnal_id" id="validasi_jurnal_id">
                    <input type="hidden" name="status_validasi" id="validasi_status">
                    
                    <div class="form-group">
                        <label>Status Validasi</label>
                        <div id="validasi_info" class="alert"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Dosen</label>
                        <textarea class="form-control" name="catatan_dosen" rows="4" 
                                  placeholder="Berikan catatan untuk mahasiswa (opsional untuk validasi, wajib untuk revisi)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="validasi_submit_btn">Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// PERBAIKAN: Multiple fallback methods untuk mendapatkan proposal ID
var currentProposalId = null;

// Method 1: Ambil dari PHP variable
<?php if(isset($mahasiswa->proposal_id) && !empty($mahasiswa->proposal_id)): ?>
currentProposalId = '<?= $mahasiswa->proposal_id ?>';
<?php elseif(isset($mahasiswa->id) && !empty($mahasiswa->id)): ?>
currentProposalId = '<?= $mahasiswa->id ?>';
<?php endif; ?>

// Method 2: Ambil dari hidden input
if (!currentProposalId) {
    const hiddenInput = document.getElementById('current_proposal_id');
    if (hiddenInput && hiddenInput.value) {
        currentProposalId = hiddenInput.value;
    }
}

// Method 3: Ambil dari URL
if (!currentProposalId) {
    const urlParts = window.location.href.split('/');
    const detailIndex = urlParts.indexOf('detail_mahasiswa');
    if (detailIndex !== -1 && urlParts[detailIndex + 1]) {
        currentProposalId = urlParts[detailIndex + 1];
    }
}

// Method 4: Ambil dari data attribute
if (!currentProposalId) {
    const buttonWithId = document.querySelector('[data-proposal-id]');
    if (buttonWithId && buttonWithId.dataset.proposalId) {
        currentProposalId = buttonWithId.dataset.proposalId;
    }
}

// Debug log
console.log('=== Export Debug Info ===');
console.log('Current Proposal ID:', currentProposalId);
console.log('PHP mahasiswa object available:', <?= isset($mahasiswa) ? 'true' : 'false' ?>);
console.log('Current URL:', window.location.href);

// Variables untuk tracking modal state
var isEditMode = false;
var currentEditJurnalId = null;

// PERBAIKAN: Export function dengan robust proposal ID detection
function exportJurnal() {
    console.log('exportJurnal called, currentProposalId:', currentProposalId);
    
    if (!currentProposalId) {
        // Last resort: try to extract from URL again
        const urlParts = window.location.href.split('/');
        const detailIndex = urlParts.indexOf('detail_mahasiswa');
        if (detailIndex !== -1 && urlParts[detailIndex + 1]) {
            currentProposalId = urlParts[detailIndex + 1];
            console.log('Last resort: found proposal ID from URL:', currentProposalId);
        }
    }
    
    if (!currentProposalId) {
        alert('ID proposal tidak ditemukan! Debug info sudah ditulis ke console.');
        console.error('Export PDF Error: No proposal ID found after all methods');
        return;
    }
    
    const exportUrl = '<?= base_url() ?>dosen/bimbingan/export_jurnal/' + currentProposalId;
    console.log('Opening export URL:', exportUrl);
    window.open(exportUrl, '_blank');
}

// Tambah Jurnal Bimbingan
function tambahJurnalBimbingan() {
    isEditMode = false;
    currentEditJurnalId = null;
    
    document.getElementById('modalJurnalTitle').textContent = 'Tambah Jurnal Bimbingan';
    document.getElementById('formJurnal').action = '<?= base_url("dosen/bimbingan/tambah_jurnal") ?>';
    document.getElementById('formJurnal').reset();
    document.getElementById('edit_jurnal_id').value = '';
    document.getElementById('submitJurnalBtn').textContent = 'Simpan Jurnal';
    
    // Set default values
    document.querySelector('[name="pertemuan_ke"]').value = <?= (isset($total_bimbingan) ? $total_bimbingan : 0) + 1 ?>;
    document.querySelector('[name="tanggal_bimbingan"]').value = '<?= date('Y-m-d') ?>';
    document.querySelector('[name="proposal_id"]').value = currentProposalId || '';
    
    $('#modalJurnal').modal('show');
}

// Edit Jurnal Bimbingan
function editJurnal(jurnalId) {
    isEditMode = true;
    currentEditJurnalId = jurnalId;
    
    document.getElementById('modalJurnalTitle').textContent = 'Edit Jurnal Bimbingan';
    document.getElementById('submitJurnalBtn').textContent = 'Update Jurnal';
    
    // Fetch data jurnal - pastikan jQuery tersedia
    if (typeof $ !== 'undefined') {
        $.get('<?= base_url("dosen/bimbingan/get_jurnal/") ?>' + jurnalId)
        .done(function(data) {
            if (data.error) {
                alert('Error: ' + data.message);
                return;
            }
            
            // Populate form
            document.getElementById('edit_jurnal_id').value = data.data.id;
            document.getElementById('input_pertemuan_ke').value = data.data.pertemuan_ke;
            document.getElementById('input_tanggal_bimbingan').value = data.data.tanggal_bimbingan;
            document.getElementById('input_materi_bimbingan').value = data.data.materi_bimbingan;
            document.getElementById('input_catatan_dosen').value = data.data.catatan_dosen || '';
            document.getElementById('input_tindak_lanjut').value = data.data.tindak_lanjut || '';
            
            $('#modalJurnal').modal('show');
        })
        .fail(function() {
            alert('Terjadi kesalahan saat mengambil data jurnal!');
        });
    } else {
        // Fallback untuk fetch API jika jQuery tidak tersedia
        fetch('<?= base_url("dosen/bimbingan/get_jurnal/") ?>' + jurnalId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.message);
                return;
            }
            
            // Populate form
            document.getElementById('edit_jurnal_id').value = data.data.id;
            document.getElementById('input_pertemuan_ke').value = data.data.pertemuan_ke;
            document.getElementById('input_tanggal_bimbingan').value = data.data.tanggal_bimbingan;
            document.getElementById('input_materi_bimbingan').value = data.data.materi_bimbingan;
            document.getElementById('input_catatan_dosen').value = data.data.catatan_dosen || '';
            document.getElementById('input_tindak_lanjut').value = data.data.tindak_lanjut || '';
            
            // Jika jQuery tersedia gunakan, jika tidak gunakan vanilla JS
            if (typeof $ !== 'undefined') {
                $('#modalJurnal').modal('show');
            } else {
                // Vanilla JS modal show
                const modal = document.getElementById('modalJurnal');
                if (modal) {
                    // Try Bootstrap 4 method
                    if (window.bootstrap && window.bootstrap.Modal) {
                        new window.bootstrap.Modal(modal).show();
                    } else {
                        // Fallback
                        modal.style.display = 'block';
                        modal.classList.add('show');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data jurnal!');
        });
    }
}

// Delete Jurnal Bimbingan
function deleteJurnal(jurnalId) {
    if (!confirm('Apakah Anda yakin ingin menghapus jurnal bimbingan ini?\n\nData yang dihapus tidak dapat dikembalikan!')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('jurnal_id', jurnalId);
    
    fetch('<?= base_url("dosen/bimbingan/delete_jurnal") ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.message);
        } else {
            alert('Success: ' + data.message);
            // Remove row from table atau refresh page
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan sistem!');
    });
}

// Validasi Jurnal
function validasiJurnal(jurnalId, status) {
    document.getElementById('validasi_jurnal_id').value = jurnalId;
    document.getElementById('validasi_status').value = status;
    
    const validasiInfo = document.getElementById('validasi_info');
    const submitBtn = document.getElementById('validasi_submit_btn');
    
    if (status == 1) {
        validasiInfo.className = 'alert alert-success';
        validasiInfo.innerHTML = '<i class="fa fa-check"></i> <strong>Validasi Jurnal</strong><br>Jurnal akan ditandai sebagai tervalidasi.';
        submitBtn.textContent = 'Validasi';
        submitBtn.className = 'btn btn-success';
    } else {
        validasiInfo.className = 'alert alert-warning';
        validasiInfo.innerHTML = '<i class="fa fa-edit"></i> <strong>Minta Revisi</strong><br>Jurnal akan dikembalikan untuk diperbaiki mahasiswa.';
        submitBtn.textContent = 'Minta Revisi';
        submitBtn.className = 'btn btn-warning';
    }
    
    // Show modal
    if (typeof $ !== 'undefined') {
        $('#modalValidasi').modal('show');
    } else {
        const modal = document.getElementById('modalValidasi');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
        }
    }
}

// Handle form submit untuk edit/tambah
document.addEventListener('DOMContentLoaded', function() {
    const formJurnal = document.getElementById('formJurnal');
    if (formJurnal) {
        formJurnal.addEventListener('submit', function(e) {
            if (isEditMode && currentEditJurnalId) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('jurnal_id', currentEditJurnalId);
                
                const submitBtn = document.getElementById('submitJurnalBtn');
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Menyimpan...';
                
                fetch('<?= base_url("dosen/bimbingan/edit_jurnal") ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.message);
                    } else {
                        alert('Success: ' + data.message);
                        if (typeof $ !== 'undefined') {
                            $('#modalJurnal').modal('hide');
                        }
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan sistem!');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            }
            // Untuk mode tambah, biarkan form submit normal
        });
    }
});

// Reset modal saat ditutup
if (typeof $ !== 'undefined') {
    $('#modalJurnal').on('hidden.bs.modal', function () {
        isEditMode = false;
        currentEditJurnalId = null;
        document.getElementById('formJurnal').reset();
    });
}

// Pastikan proposal ID tersedia global
window.currentProposalId = currentProposalId;

// Debug info on load
console.log('Content page loaded. Final proposal ID:', currentProposalId);
</script>