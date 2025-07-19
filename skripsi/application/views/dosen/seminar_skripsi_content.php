<?php
// ========================================
// FILE: application/views/dosen/seminar_skripsi_content.php (Simplified)
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
        <div class="card bg-gradient-danger">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Seminar Skripsi - Phase 5</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Workflow Phase 5:</strong> Ujian akhir skripsi mahasiswa. Sebagai pembimbing atau penguji, 
                            berikan rekomendasi dan penilaian untuk penyelesaian studi mahasiswa.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-danger rounded-circle shadow">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Seminar Skripsi -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Pengajuan Seminar Skripsi</h3>
                        <p class="mb-0 text-sm">Mahasiswa bimbingan yang mengajukan ujian akhir skripsi</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Skripsi</th>
                            <th scope="col">Tanggal Pengajuan</th>
                            <th scope="col">Status Rekomendasi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($seminar_skripsi)): ?>
                            <?php foreach($seminar_skripsi as $ss): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?= $ss->nama_mahasiswa ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?= $ss->nim ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold"><?= substr($ss->judul, 0, 40) ?>...</span>
                                </td>
                                <td>
                                    <?php 
                                    $tanggal = isset($ss->created_at) ? $ss->created_at : $ss->updated_at;
                                    ?>
                                    <span class="text-sm"><?= date('d/m/Y', strtotime($tanggal)) ?></span>
                                </td>
                                <td>
                                    <?php if(isset($ss->rekomendasi_pembimbing) && $ss->rekomendasi_pembimbing == '1'): ?>
                                        <span class="badge badge-success">Direkomendasikan</span>
                                    <?php elseif(isset($ss->rekomendasi_pembimbing) && $ss->rekomendasi_pembimbing == '2'): ?>
                                        <span class="badge badge-warning">Perlu Perbaikan</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Menunggu Rekomendasi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $ss->id) ?>" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-center">
                                        <i class="fa fa-graduation-cap fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada pengajuan seminar skripsi</h5>
                                        <p class="text-muted">Pengajuan ujian akhir dari mahasiswa bimbingan akan muncul di sini.</p>
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