<?php
ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Daftar Proposal Menunggu Penetapan</h3>
                        <small class="text-muted">Workflow Terbaru - Penetapan Pembimbing dan Penguji</small>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url() ?>kaprodi/dashboard" class="btn btn-secondary btn-sm">
                            <i class="ni ni-bold-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                
                <?php if($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Email</th>
                                <th>Judul Proposal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($proposals)): ?>
                                <?php foreach ($proposals as $index => $p): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <span class="badge badge-outline-primary"><?= $p->nim ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle mr-3">
                                                <div class="avatar-initials bg-primary text-white">
                                                    <?= strtoupper(substr($p->nama_mahasiswa, 0, 2)) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <strong><?= $p->nama_mahasiswa ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= $p->email_mahasiswa ?></small>
                                    </td>
                                    <td>
                                        <div class="text-wrap" style="max-width: 300px;">
                                            <strong><?= substr($p->judul, 0, 80) ?></strong>
                                            <?php if (strlen($p->judul) > 80): ?>
                                                <span class="text-primary">...</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                <?= !empty($p->jenis_penelitian) ? $p->jenis_penelitian : 'Belum ditentukan' ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning badge-pill">
                                            <i class="fa fa-clock"></i> Menunggu Penetapan
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= base_url() ?>kaprodi/dashboard/penetapan_pembimbing/detail/<?= $p->id ?>" 
                                               class="btn btn-sm btn-primary"
                                               title="Tetapkan Pembimbing & Penguji">
                                                <i class="fa fa-user-check"></i> Tetapkan
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    data-toggle="modal" 
                                                    data-target="#modalDetail<?= $p->id ?>"
                                                    title="Lihat Detail">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="ni ni-folder-17" style="font-size: 48px; color: #ddd;"></i>
                                            <h4 class="mt-3 mb-2">Tidak ada proposal yang menunggu penetapan</h4>
                                            <p class="text-muted">Semua proposal sudah ditetapkan pembimbing dan pengujinya.</p>
                                            <a href="<?= base_url() ?>kaprodi/dashboard" class="btn btn-primary">
                                                <i class="ni ni-bold-left"></i> Kembali ke Dashboard
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($proposals)): ?>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">
                            Menampilkan <?= count($proposals) ?> proposal yang menunggu penetapan
                        </span>
                        <div>
                            <a href="<?= base_url() ?>kaprodi/dashboard" class="btn btn-secondary">
                                <i class="ni ni-bold-left"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Proposal -->
<?php if (!empty($proposals)): ?>
    <?php foreach ($proposals as $p): ?>
    <div class="modal fade" id="modalDetail<?= $p->id ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Proposal - <?= $p->nama_mahasiswa ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Data Mahasiswa</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>NIM</strong></td>
                                    <td>: <?= $p->nim ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama</strong></td>
                                    <td>: <?= $p->nama_mahasiswa ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td>: <?= $p->email_mahasiswa ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Data Proposal</h6>
                            <?php if (!empty($p->jenis_penelitian)): ?>
                            <p><strong>Jenis Penelitian:</strong> <?= $p->jenis_penelitian ?></p>
                            <?php endif; ?>
                            <?php if (!empty($p->lokasi_penelitian)): ?>
                            <p><strong>Lokasi Penelitian:</strong> <?= $p->lokasi_penelitian ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Judul Proposal</h6>
                            <div class="alert alert-info">
                                <strong><?= $p->judul ?></strong>
                            </div>
                            
                            <h6>Ringkasan</h6>
                            <div class="text-justify" style="max-height: 200px; overflow-y: auto;">
                                <?= nl2br($p->ringkasan) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <a href="<?= base_url() ?>kaprodi/dashboard/penetapan_pembimbing/detail/<?= $p->id ?>" 
                       class="btn btn-primary">
                        <i class="fa fa-user-check"></i> Tetapkan Pembimbing & Penguji
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php 
$content = ob_get_clean(); 

ob_start(); 
?>
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => $title,
    'content' => $content,
    'script' => $script
]); 
?>