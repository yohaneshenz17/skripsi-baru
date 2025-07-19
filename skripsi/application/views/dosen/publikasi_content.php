<?php
// ========================================
// FILE: application/views/dosen/publikasi_content.php (Simplified)
// ========================================
?>

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

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-4">
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
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Tugas Akhir</th>
                            <th scope="col">Tanggal Pengajuan</th>
                            <th scope="col">Status Rekomendasi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($publikasi_list)): ?>
                            <?php foreach($publikasi_list as $pub): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?= $pub->nama_mahasiswa ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?= $pub->nim ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold"><?= substr($pub->judul, 0, 50) ?><?= strlen($pub->judul) > 50 ? '...' : '' ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $tanggal = isset($pub->created_at) ? $pub->created_at : $pub->updated_at;
                                    ?>
                                    <span class="text-sm"><?= date('d/m/Y', strtotime($tanggal)) ?></span>
                                </td>
                                <td>
                                    <?php if(isset($pub->rekomendasi_pembimbing) && $pub->rekomendasi_pembimbing == '1'): ?>
                                        <span class="badge badge-success">Direkomendasikan</span>
                                    <?php elseif(isset($pub->rekomendasi_pembimbing) && $pub->rekomendasi_pembimbing == '2'): ?>
                                        <span class="badge badge-warning">Perlu Perbaikan</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Menunggu Rekomendasi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('dosen/publikasi/detail/' . $pub->id) ?>" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
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