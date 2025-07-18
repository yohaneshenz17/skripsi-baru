<?php $this->load->view('template/dosen', ['title' => $title, 'content' => $this->load->view('dosen/bimbingan_content', $this, true), 'script' => '']); ?>

<!-- Content untuk bimbingan_content.php -->

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

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-success">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Bimbingan Mahasiswa - Phase 2</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Workflow Phase 2:</strong> Validasi jurnal bimbingan mahasiswa dan pantau progress pengembangan proposal menjadi skripsi lengkap (Bab 1-3). 
                            Minimal 16x pertemuan untuk keseluruhan phase.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                            <i class="ni ni-pin-3"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0"><?= count($mahasiswa_bimbingan) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Jurnal Pending</h5>
                        <span class="h2 font-weight-bold mb-0"><?= count($jurnal_pending) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Bimbingan</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= array_sum(array_column($mahasiswa_bimbingan, 'total_bimbingan')) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-book"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tervalidasi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= array_sum(array_column($mahasiswa_bimbingan, 'bimbingan_tervalidasi')) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Jurnal Bimbingan Pending Validasi -->
<?php if(!empty($jurnal_pending)): ?>
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Jurnal Bimbingan Menunggu Validasi</h3>
                        <p class="mb-0 text-sm">Jurnal yang perlu divalidasi segera</p>
                    </div>
                    <div class="col text-right">
                        <button type="button" class="btn btn-sm btn-success" onclick="validateSelected()">
                            <i class="fa fa-check"></i> Validasi Terpilih
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="reviseSelected()">
                            <i class="fa fa-edit"></i> Minta Revisi
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Pertemuan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Materi Bimbingan</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($jurnal_pending as $jurnal): ?>
                        <tr>
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input jurnal-checkbox" 
                                           id="jurnal_<?= $jurnal->id ?>" value="<?= $jurnal->id ?>">
                                    <label class="custom-control-label" for="jurnal_<?= $jurnal->id ?>"></label>
                                </div>
                            </td>
                            <td>
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm font-weight-bold"><?= $jurnal->nama_mahasiswa ?></span>
                                        <br>
                                        <small class="text-muted">NIM: <?= $jurnal->nim ?></small>
                                        <br>
                                        <small class="text-info"><?= substr($jurnal->judul, 0, 40) ?>...</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-pill badge-primary">
                                    Ke-<?= $jurnal->pertemuan_ke ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-sm"><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></span>
                            </td>
                            <td>
                                <span class="text-sm"><?= substr($jurnal->materi_bimbingan, 0, 50) ?><?= strlen($jurnal->materi_bimbingan) > 50 ? '...' : '' ?></span>
                                <?php if($jurnal->tindak_lanjut): ?>
                                <br>
                                <small class="text-muted"><strong>Tindak lanjut:</strong> <?= substr($jurnal->tindak_lanjut, 0, 40) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                        <a class="dropdown-item" href="#" onclick="quickValidate(<?= $jurnal->id ?>, 1)">
                                            <i class="fa fa-check text-success"></i> Validasi
                                        </a>
                                        <a class="dropdown-item" href="#" onclick="quickValidate(<?= $jurnal->id ?>, 2)">
                                            <i class="fa fa-edit text-warning"></i> Minta Revisi
                                        </a>
                                        <a class="dropdown-item" href="#" onclick="detailJurnal(<?= $jurnal->id ?>)">
                                            <i class="fa fa-eye text-info"></i> Detail
                                        </a>
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

<!-- Daftar Mahasiswa Bimbingan -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Mahasiswa Bimbingan</h3>
                        <p class="mb-0 text-sm">Daftar mahasiswa yang Anda bimbing</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Proposal</th>
                            <th scope="col">Progress Bimbingan</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($mahasiswa_bimbingan)): ?>
                            <?php foreach($mahasiswa_bimbingan as $mahasiswa): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?= $mahasiswa->nama_mahasiswa ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?= $mahasiswa->nim ?></small>
                                            <br>
                                            <small class="text-muted"><?= $mahasiswa->nama_prodi ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold"><?= substr($mahasiswa->judul, 0, 50) ?><?= strlen($mahasiswa->judul) > 50 ? '...' : '' ?></span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fa fa-map-marker-alt"></i> <?= $mahasiswa->lokasi_penelitian ?>
                                    </small>
                                    <br>
                                    <span class="badge badge-pill badge-secondary"><?= $mahasiswa->jenis_penelitian ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="mr-2"><?= $mahasiswa->bimbingan_tervalidasi ?>/<?= $mahasiswa->total_bimbingan ?></span>
                                        <div class="progress" style="width: 100px;">
                                            <?php 
                                            $progress = $mahasiswa->total_bimbingan > 0 ? 
                                                ($mahasiswa->bimbingan_tervalidasi / $mahasiswa->total_bimbingan) * 100 : 0;
                                            ?>
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?= $progress ?>%" 
                                                 aria-valuenow="<?= $progress ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Target: 16 pertemuan | Tervalidasi: <?= $mahasiswa->bimbingan_tervalidasi ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if($mahasiswa->total_bimbingan >= 16 && $mahasiswa->bimbingan_tervalidasi >= 16): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Siap Seminar
                                        </span>
                                    <?php elseif($mahasiswa->total_bimbingan > 0): ?>
                                        <span class="badge badge-info">
                                            <i class="fa fa-clock"></i> Bimbingan Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-play"></i> Belum Mulai
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <a class="dropdown-item" href="<?= base_url('dosen/bimbingan/detail_mahasiswa/' . $mahasiswa->mahasiswa_id) ?>">
                                                <i class="fa fa-eye"></i> Detail Bimbingan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="tambahJurnalBimbingan(<?= $mahasiswa->proposal_id ?>, '<?= $mahasiswa->nama_mahasiswa ?>')">
                                                <i class="fa fa-plus"></i> Tambah Jurnal
                                            </a>
                                            <?php if($mahasiswa->total_bimbingan > 0): ?>
                                            <a class="dropdown-item" href="#" onclick="exportJurnal(<?= $mahasiswa->proposal_id ?>)">
                                                <i class="fa fa-download"></i> Export Jurnal
                                            </a>
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
                                        <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada mahasiswa bimbingan</h5>
                                        <p class="text-muted">Mahasiswa yang Anda setujui sebagai pembimbing akan muncul di sini.</p>
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

<!-- Modal Tambah Jurnal Bimbingan -->
<div class="modal fade" id="modalTambahJurnal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/bimbingan/tambah_jurnal') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jurnal Bimbingan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="proposal_id" id="form_proposal_id">
                    
                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <input type="text" class="form-control" id="form_nama_mahasiswa" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pertemuan ke- *</label>
                                <input type="number" class="form-control" name="pertemuan_ke" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" rows="3" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Dosen</label>
                        <textarea class="form-control" name="catatan_dosen" rows="3" 
                                  placeholder="Catatan, saran, atau evaluasi dari dosen"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" rows="2" 
                                  placeholder="Tugas atau tindak lanjut untuk mahasiswa"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Batch Validation -->
<div class="modal fade" id="modalBatchValidation" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/bimbingan/validasi_batch') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Validasi Batch</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="batch_action">
                    <div id="selected_jurnals"></div>
                    
                    <div class="form-group">
                        <label>Catatan (Opsional)</label>
                        <textarea class="form-control" name="catatan_batch" rows="3" 
                                  placeholder="Catatan yang akan dikirim ke semua mahasiswa terpilih"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="batch_submit_btn">Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Select All Checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.jurnal-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

// Tambah Jurnal Bimbingan
function tambahJurnalBimbingan(proposalId, namaMahasiswa) {
    document.getElementById('form_proposal_id').value = proposalId;
    document.getElementById('form_nama_mahasiswa').value = namaMahasiswa;
    $('#modalTambahJurnal').modal('show');
}

// Quick Validate
function quickValidate(jurnalId, status) {
    const action = status == 1 ? 'validasi' : 'minta revisi';
    if (confirm(`Apakah Anda yakin ingin ${action} jurnal ini?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= base_url('dosen/bimbingan/validasi_jurnal') ?>';
        
        const jurnalInput = document.createElement('input');
        jurnalInput.type = 'hidden';
        jurnalInput.name = 'jurnal_id';
        jurnalInput.value = jurnalId;
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status_validasi';
        statusInput.value = status;
        
        form.appendChild(jurnalInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Batch Validation
function validateSelected() {
    processBatchValidation('validate', 'Validasi');
}

function reviseSelected() {
    processBatchValidation('revise', 'Minta Revisi');
}

function processBatchValidation(action, actionText) {
    const selectedCheckboxes = document.querySelectorAll('.jurnal-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Pilih minimal satu jurnal untuk diproses!');
        return;
    }
    
    document.getElementById('batch_action').value = action;
    document.getElementById('batch_submit_btn').textContent = actionText;
    
    const selectedJurnalsDiv = document.getElementById('selected_jurnals');
    selectedJurnalsDiv.innerHTML = `<p><strong>${selectedCheckboxes.length} jurnal terpilih</strong></p>`;
    
    selectedCheckboxes.forEach(cb => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'jurnal_ids[]';
        hiddenInput.value = cb.value;
        selectedJurnalsDiv.appendChild(hiddenInput);
    });
    
    $('#modalBatchValidation').modal('show');
}

// Export Jurnal
function exportJurnal(proposalId) {
    window.open('<?= base_url('dosen/bimbingan/export_jurnal/') ?>' + proposalId, '_blank');
}

// Detail Jurnal
function detailJurnal(jurnalId) {
    // Implementasi untuk menampilkan detail jurnal
    // Bisa redirect ke halaman detail atau modal
    window.location.href = '<?= base_url('dosen/bimbingan/detail_jurnal/') ?>' + jurnalId;
}
</script>