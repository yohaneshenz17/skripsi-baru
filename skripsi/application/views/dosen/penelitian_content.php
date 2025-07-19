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
        <div class="card bg-gradient-secondary">
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
                        <div class="icon icon-shape bg-white text-secondary rounded-circle shadow">
                            <i class="fa fa-search"></i>
                        </div>
                    </div>
                </div>
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
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Penelitian</th>
                            <th scope="col">Lokasi Penelitian</th>
                            <th scope="col">Status Rekomendasi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($surat_izin_penelitian)): ?>
                            <?php foreach($surat_izin_penelitian as $sip): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?= $sip->nama_mahasiswa ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?= $sip->nim ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold"><?= substr($sip->judul, 0, 40) ?>...</span>
                                </td>
                                <td>
                                    <span class="text-sm"><?= $sip->lokasi_penelitian ?></span>
                                </td>
                                <td>
                                    <?php if(isset($sip->rekomendasi_pembimbing) && $sip->rekomendasi_pembimbing == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Direkomendasikan
                                        </span>
                                    <?php elseif(isset($sip->rekomendasi_pembimbing) && $sip->rekomendasi_pembimbing == '2'): ?>
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
                                            <a class="dropdown-item" href="<?= base_url('dosen/penelitian/detail/' . $sip->id) ?>">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                            <?php if(!isset($sip->rekomendasi_pembimbing) || is_null($sip->rekomendasi_pembimbing)): ?>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?= $sip->id ?>, 1)">
                                                <i class="fa fa-check text-success"></i> Rekomendasikan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?= $sip->id ?>, 2)">
                                                <i class="fa fa-edit text-warning"></i> Minta Perbaikan
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

<!-- Modal Rekomendasi -->
<div class="modal fade" id="modalRekomendasi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/penelitian/rekomendasi') ?>" method="POST">
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
                        <label>Status Rekomendasi</label>
                        <div id="modal_status_text" class="alert"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Pembimbing *</label>
                        <textarea class="form-control" name="catatan_pembimbing" rows="4" required 
                                  placeholder="Berikan catatan, evaluasi, atau instruksi terkait penelitian mahasiswa"></textarea>
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
function beriRekomendasi(suratId, status) {
    document.getElementById('modal_surat_id').value = suratId;
    document.getElementById('modal_rekomendasi').value = status;
    
    const statusText = document.getElementById('modal_status_text');
    const submitBtn = document.getElementById('modal_submit_btn');
    
    if (status == 1) {
        statusText.className = 'alert alert-success';
        statusText.innerHTML = '<i class="fa fa-check"></i> <strong>Direkomendasikan</strong><br>Mahasiswa dapat melanjutkan ke tahap penelitian lapangan';
        submitBtn.className = 'btn btn-success';
        submitBtn.textContent = 'Rekomendasikan';
    } else {
        statusText.className = 'alert alert-warning';
        statusText.innerHTML = '<i class="fa fa-edit"></i> <strong>Perlu Perbaikan</strong><br>Mahasiswa perlu memperbaiki persiapan penelitian';
        submitBtn.className = 'btn btn-warning';
        submitBtn.textContent = 'Minta Perbaikan';
    }
    
    $('#modalRekomendasi').modal('show');
}
</script>