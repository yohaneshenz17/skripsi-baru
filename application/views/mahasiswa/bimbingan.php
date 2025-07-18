<?php
ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php endif; ?>

        <!-- Form Pengajuan Konsultasi/Bimbingan -->
        <?php if($proposal): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">Ajukan Permintaan Konsultasi</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> <strong>Dosen Pembimbing:</strong> <?= $proposal->nama_dosen ?><br>
                    <i class="fa fa-book"></i> <strong>Judul Proposal:</strong> <?= $proposal->judul ?>
                </div>
                
                <form method="post" action="<?= base_url() ?>mahasiswa/bimbingan/ajukan" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-control-label">Isi Konsultasi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="isi_konsultasi" rows="4" required placeholder="Jelaskan secara detail apa yang ingin Anda konsultasikan"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">File Bukti Konsultasi <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="bukti" accept=".pdf,.doc,.docx" required>
                        <small class="text-muted">Format: PDF, DOC, DOCX. Maksimal 2MB</small>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i> Ajukan Konsultasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> <strong>Perhatian:</strong> Anda belum memiliki proposal yang disetujui dengan dosen pembimbing.
        </div>
        <?php endif; ?>

        <!-- Riwayat Konsultasi/Bimbingan -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Riwayat Konsultasi</h3>
            </div>
            <div class="card-body">
                <?php if($bimbingan && count($bimbingan) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Isi Konsultasi</th>
                                <th>Status Pembimbing</th>
                                <th>Status Kaprodi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bimbingan as $no => $b): ?>
                            <tr>
                                <td><?= $no + 1 ?></td>
                                <td><?= date('d/m/Y', strtotime($b->tanggal)) ?></td>
                                <td><?= date('H:i', strtotime($b->jam)) ?></td>
                                <td><?= substr($b->isi, 0, 50) ?>...</td>
                                <td>
                                    <?php if($b->persetujuan_pembimbing == '1'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Menunggu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($b->persetujuan_kaprodi == '1'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Menunggu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="showDetail(<?= $b->id ?>)">
                                        <i class="fa fa-eye"></i> Detail
                                    </button>
                                    <?php if($b->bukti): ?>
                                    <a href="<?= base_url('cdn/konsultasi/' . $b->bukti) ?>" target="_blank" class="btn btn-sm btn-success">
                                        <i class="fa fa-download"></i> File
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada riwayat konsultasi</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Konsultasi -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Konsultasi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
ob_start(); 
?>

<script>
function showDetail(id) {
    // Implementasi untuk menampilkan detail konsultasi
    $('#detailModal').modal('show');
    $('#detailContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
    
    // Ajax call untuk mengambil detail konsultasi
    $.ajax({
        url: '<?= base_url() ?>mahasiswa/bimbingan/detail/' + id,
        method: 'GET',
        success: function(response) {
            $('#detailContent').html(response);
        },
        error: function() {
            $('#detailContent').html('<div class="alert alert-danger">Gagal memuat detail konsultasi</div>');
        }
    });
}
</script>

<?php 
$script = ob_get_clean(); 

$this->load->view('template/mahasiswa', [
    'content' => $content, 
    'script' => $script
]); 
?>