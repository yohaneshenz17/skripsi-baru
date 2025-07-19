<?php $this->load->view('template/dosen', ['title' => $title, 'content' => $this->load->view('dosen/seminar_proposal_content', $this, true), 'script' => '']); ?>

<!-- Content untuk seminar_proposal_content.php -->

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
                        <h5 class="card-title text-uppercase text-muted mb-0">Sebagai Pembimbing</h5>
                        <span class="h2 font-weight-bold mb-0"><?= count($seminar_proposals) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="fa fa-user-tie"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Sebagai Penguji</h5>
                        <span class="h2 font-weight-bold mb-0"><?= count($jadwal_sebagai_penguji) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-gavel"></i>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Perlu Rekomendasi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $pending = 0;
                            foreach($seminar_proposals as $sp) {
                                if(is_null($sp->rekomendasi_pembimbing)) $pending++;
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
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Sudah Dinilai</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $dinilai = 0;
                            foreach($jadwal_sebagai_penguji as $sp) {
                                if(!is_null($sp->nilai_pembimbing) || !is_null($sp->nilai_penguji_1) || !is_null($sp->nilai_penguji_2)) {
                                    $dinilai++;
                                }
                            }
                            echo $dinilai;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pengajuan Seminar Proposal - Sebagai Pembimbing -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Pengajuan Seminar Proposal - Sebagai Pembimbing</h3>
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
                            <th scope="col">Jadwal Seminar</th>
                            <th scope="col">Status Rekomendasi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($seminar_proposals)): ?>
                            <?php foreach($seminar_proposals as $sp): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?= $sp->nama_mahasiswa ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?= $sp->nim ?></small>
                                            <br>
                                            <small class="text-muted"><?= $sp->nama_prodi ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold"><?= substr($sp->judul, 0, 40) ?>...</span>
                                </td>
                                <td>
                                    <span class="text-sm"><?= date('d/m/Y', strtotime($sp->created_at)) ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($sp->tanggal_seminar)): ?>
                                        <span class="badge badge-info">
                                            <?= date('d/m/Y H:i', strtotime($sp->tanggal_seminar)) ?>
                                        </span>
                                        <br>
                                        <small class="text-muted"><?= $sp->tempat_seminar ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum Dijadwalkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($sp->rekomendasi_pembimbing == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Direkomendasikan
                                        </span>
                                    <?php elseif($sp->rekomendasi_pembimbing == '2'): ?>
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
                                            <a class="dropdown-item" href="<?= base_url('dosen/seminar_proposal/detail/' . $sp->id) ?>">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                            <?php if(is_null($sp->rekomendasi_pembimbing)): ?>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?= $sp->id ?>, 1)">
                                                <i class="fa fa-check text-success"></i> Rekomendasikan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?= $sp->id ?>, 2)">
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
                                <td colspan="6" class="text-center py-4">
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

<!-- Jadwal Seminar - Sebagai Penguji -->
<?php if(!empty($jadwal_sebagai_penguji)): ?>
<div class="row mt-4">
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
                                        <span class="mb-0 text-sm font-weight-bold"><?= $jadwal->nama_mahasiswa ?></span>
                                        <br>
                                        <small class="text-muted">NIM: <?= $jadwal->nim ?></small>
                                        <br>
                                        <small class="text-info"><?= substr($jadwal->judul, 0, 30) ?>...</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm"><?= $jadwal->nama_pembimbing ?></span>
                            </td>
                            <td>
                                <span class="badge badge-primary">
                                    <?= date('d/m/Y H:i', strtotime($jadwal->tanggal_seminar)) ?>
                                </span>
                                <br>
                                <small class="text-muted"><?= $jadwal->tempat_seminar ?></small>
                            </td>
                            <td>
                                <?php 
                                $sudah_dinilai = false;
                                if($jadwal->dosen_penguji_1 == $this->session->userdata('id') && !is_null($jadwal->nilai_penguji_1)) {
                                    $sudah_dinilai = true;
                                } elseif($jadwal->dosen_penguji_2 == $this->session->userdata('id') && !is_null($jadwal->nilai_penguji_2)) {
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
                                        <a class="dropdown-item" href="<?= base_url('dosen/seminar_proposal/detail/' . $jadwal->id) ?>">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                        <?php if(!$sudah_dinilai): ?>
                                        <a class="dropdown-item" href="#" onclick="inputNilai(<?= $jadwal->id ?>)">
                                            <i class="fa fa-edit"></i> Input Nilai
                                        </a>
                                        <?php endif; ?>
                                        <a class="dropdown-item" href="<?= base_url('dosen/seminar_proposal/berita_acara/' . $jadwal->id) ?>">
                                            <i class="fa fa-file-alt"></i> Berita Acara
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

<!-- Modal Rekomendasi -->
<div class="modal fade" id="modalRekomendasi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/seminar_proposal/rekomendasi') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Berikan Rekomendasi</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="modal_seminar_id">
                    <input type="hidden" name="rekomendasi" id="modal_rekomendasi">
                    
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
function beriRekomendasi(seminarId, status) {
    document.getElementById('modal_seminar_id').value = seminarId;
    document.getElementById('modal_rekomendasi').value = status;
    
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
    
    $('#modalRekomendasi').modal('show');
}

// Input Nilai (implementasi sederhana - bisa dikembangkan dengan modal yang lebih detail)
function inputNilai(seminarId) {
    window.location.href = '<?= base_url('dosen/seminar_proposal/detail/') ?>' + seminarId + '#input-nilai';
}
</script>