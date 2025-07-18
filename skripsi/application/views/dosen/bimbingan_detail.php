<?php $this->load->view('template/dosen', ['title' => $title, 'content' => $this->load->view('dosen/bimbingan_detail_content', $this, true), 'script' => '']); ?>

<!-- Content untuk bimbingan_detail_content.php -->

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

<!-- Header -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">Detail Bimbingan: <?= $mahasiswa->nama_mahasiswa ?></h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>NIM:</strong> <?= $mahasiswa->nim ?> | 
                            <strong>Prodi:</strong> <?= $mahasiswa->nama_prodi ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('dosen/bimbingan') ?>" class="btn btn-sm btn-neutral">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-sm btn-success" onclick="tambahJurnalBimbingan()">
                            <i class="fa fa-plus"></i> Tambah Jurnal
                        </button>
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
                        <span class="h2 font-weight-bold mb-0"><?= $total_bimbingan ?></span>
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
                        <span class="h2 font-weight-bold mb-0"><?= $bimbingan_tervalidasi ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
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
                        <span class="h2 font-weight-bold mb-0"><?= $bimbingan_pending ?></span>
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
                        <h4 class="mb-0"><?= $mahasiswa->nama_mahasiswa ?></h4>
                        <p class="text-muted mb-0">NIM: <?= $mahasiswa->nim ?></p>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="row">
                    <div class="col-12">
                        <strong>Program Studi:</strong>
                        <p class="text-muted"><?= $mahasiswa->nama_prodi ?></p>
                        
                        <strong>Email:</strong>
                        <p class="text-muted">
                            <i class="fa fa-envelope text-primary"></i> 
                            <a href="mailto:<?= $mahasiswa->email_mahasiswa ?>"><?= $mahasiswa->email_mahasiswa ?></a>
                        </p>
                        
                        <strong>No. Telepon:</strong>
                        <p class="text-muted">
                            <i class="fa fa-phone text-primary"></i> 
                            <a href="tel:<?= $mahasiswa->nomor_telepon ?>"><?= $mahasiswa->nomor_telepon ?></a>
                        </p>
                        
                        <strong>Alamat:</strong>
                        <p class="text-muted"><?= $mahasiswa->alamat ?></p>
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
                <h5 class="text-primary mb-3"><?= $mahasiswa->judul ?></h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Jenis Penelitian:</strong>
                        <p class="text-muted">
                            <span class="badge badge-secondary"><?= $mahasiswa->jenis_penelitian ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <strong>Lokasi Penelitian:</strong>
                        <p class="text-muted">
                            <i class="fa fa-map-marker-alt text-danger"></i> <?= $mahasiswa->lokasi_penelitian ?>
                        </p>
                    </div>
                </div>
                
                <strong>Uraian Masalah:</strong>
                <div class="bg-light p-3 rounded">
                    <p class="text-dark mb-0"><?= nl2br($mahasiswa->uraian_masalah) ?></p>
                </div>
                
                <hr class="my-3">
                
                <strong>Tanggal Proposal Diajukan:</strong>
                <p class="text-muted"><?= date('d F Y', strtotime($mahasiswa->tanggal_proposal)) ?></p>
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
                        <?php if(!empty($jurnal_bimbingan)): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportJurnal()">
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
                        <?php if(!empty($jurnal_bimbingan)): ?>
                            <?php foreach($jurnal_bimbingan as $jurnal): ?>
                            <tr>
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
                                    <?php if($jurnal->catatan_dosen): ?>
                                        <strong>Catatan:</strong><br>
                                        <span class="text-sm"><?= $jurnal->catatan_dosen ?></span><br>
                                    <?php endif; ?>
                                    <?php if($jurnal->tindak_lanjut): ?>
                                        <strong>Tindak Lanjut:</strong><br>
                                        <span class="text-sm text-info"><?= $jurnal->tindak_lanjut ?></span>
                                    <?php endif; ?>
                                    <?php if(!$jurnal->catatan_dosen && !$jurnal->tindak_lanjut): ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($jurnal->status_validasi == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Tervalidasi
                                        </span>
                                        <?php if($jurnal->tanggal_validasi): ?>
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
                    <input type="hidden" name="proposal_id" value="<?= $mahasiswa->proposal_id ?>">
                    
                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <input type="text" class="form-control" value="<?= $mahasiswa->nama_mahasiswa ?> (<?= $mahasiswa->nim ?>)" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pertemuan ke- *</label>
                                <input type="number" class="form-control" name="pertemuan_ke" min="1" value="<?= $total_bimbingan + 1 ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" value="<?= date('Y-m-d') ?>" required>
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
// Tambah Jurnal Bimbingan
function tambahJurnalBimbingan() {
    document.getElementById('modalJurnalTitle').textContent = 'Tambah Jurnal Bimbingan';
    document.getElementById('formJurnal').reset();
    document.querySelector('[name="pertemuan_ke"]').value = <?= $total_bimbingan + 1 ?>;
    document.querySelector('[name="tanggal_bimbingan"]').value = '<?= date('Y-m-d') ?>';
    $('#modalJurnal').modal('show');
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
    
    $('#modalValidasi').modal('show');
}

// Export Jurnal
function exportJurnal() {
    window.open('<?= base_url('dosen/bimbingan/export_jurnal/' . $mahasiswa->proposal_id) ?>', '_blank');
}

// Edit Jurnal (placeholder)
function editJurnal(jurnalId) {
    alert('Fitur edit jurnal akan dikembangkan selanjutnya.');
}

// Delete Jurnal (placeholder)
function deleteJurnal(jurnalId) {
    if (confirm('Apakah Anda yakin ingin menghapus jurnal ini?')) {
        alert('Fitur hapus jurnal akan dikembangkan selanjutnya.');
    }
}
</script>