<?php
ob_start();
?>
<style>
    .custom-file-label {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 85px;
    }
    .custom-file-label::after {
        content: "Browse";
    }
</style>
<?php
$styles = ob_get_clean();
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

        <?php if(!$proposal): ?>
        <div class="card">
            <div class="card-header"><h3 class="mb-0">Form Pengajuan Usulan Proposal Skripsi</h3></div>
            <div class="card-body">
                <form method="post" action="<?= base_url() ?>mahasiswa/proposal/ajukan" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-control-label">1. Judul Proposal <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="judul" required placeholder="Tuliskan usulan judul proposal Anda secara lengkap" maxlength="250" rows="3"></textarea>
                        <small class="text-muted">Maksimal 250 karakter.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">2. Jenis Penelitian <span class="text-danger">*</span></label>
                        <select name="jenis_penelitian" class="form-control" required>
                            <option value="">- Pilih Jenis Penelitian -</option>
                            <option value="Kuantitatif">Kuantitatif</option>
                            <option value="Kualitatif">Kualitatif</option>
                            <option value="Mixed Method">Mixed Method</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">3. Lokasi Penelitian <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lokasi_penelitian" required placeholder="Tuliskan rencana lokasi penelitian Anda nanti">
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">4. Uraian Masalah Penelitian <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="uraian_masalah" rows="5" required placeholder="Uraikan masalah yang melatarbelakangi pemilihan judul Anda secara singkat"></textarea>
                        <small class="text-muted">Maksimal 200 kata.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">5. Upload File Draft Proposal <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="draft_proposal" id="draft_proposal" required>
                            <label class="custom-file-label" for="draft_proposal">Pilih file...</label>
                        </div>
                        <small class="text-muted">File Word atau PDF, maksimal 500 KB.</small>
                    </div>
                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Ajukan Proposal</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php else: ?>
        
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col"><h3 class="mb-0">Status Proposal Anda</h3></div>
                    <div class="col text-right">
                        <?php
                        $status_text = '';
                        $status_class = '';
                    
                        // Logika untuk menentukan teks dan warna status
                        if ($proposal->status_kaprodi == '0') {
                            $status_text = 'Menunggu Penetapan Kaprodi';
                            $status_class = 'badge-warning';
                        } elseif ($proposal->status_kaprodi == '1' && $proposal->status_pembimbing == '0') {
                            $status_text = 'Menunggu Persetujuan Dosen Pembimbing';
                            $status_class = 'badge-info';
                        } elseif ($proposal->status_kaprodi == '1' && $proposal->status_pembimbing == '1') {
                            $status_text = 'Proposal Disetujui';
                            $status_class = 'badge-success';
                        } elseif ($proposal->status_kaprodi == '1' && $proposal->status_pembimbing == '2') {
                            $status_text = 'Pembimbing Menolak, Menunggu Penetapan Ulang';
                            $status_class = 'badge-danger';
                        } elseif ($proposal->status_kaprodi == '2') {
                            $status_text = 'Proposal Ditolak Kaprodi';
                            $status_class = 'badge-danger';
                        }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h4 class="font-weight-bold">Judul Proposal:</h4>
                <p class="text-primary"><?= $proposal->judul ?></p>
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="font-weight-bold">Jenis Penelitian:</h4>
                        <p><?= $proposal->jenis_penelitian ?: '-' ?></p>
                    </div>
                    <div class="col-md-6">
                        <h4 class="font-weight-bold">Lokasi Penelitian:</h4>
                        <p><?= $proposal->lokasi_penelitian ?: '-' ?></p>
                    </div>
                </div>
                <h4 class="font-weight-bold">Uraian Masalah:</h4>
                <div class="alert alert-secondary p-3" style="white-space: pre-wrap;"><?= $proposal->uraian_masalah ?: '-' ?></div>
                <?php if($proposal->file_draft_proposal): ?>
                    <a href="<?= base_url('cdn/proposals/' . $proposal->file_draft_proposal) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file-alt"></i> Lihat Draft Proposal</a>
                <?php endif; ?>
                <hr>
                <?php if($proposal->dosen_id): ?>
                <h4 class="font-weight-bold">Tim Dosen Anda:</h4>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h5>Dosen Pembimbing:</h5>
                        <p class="text-success"><i class="fa fa-user-tie"></i> <?= $proposal->nama_pembimbing ?: 'Belum ditetapkan' ?></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="fa fa-hourglass-half"></i> <strong>Menunggu Persetujuan:</strong> Proposal Anda sedang ditinjau oleh Ketua Program Studi.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
ob_start(); 
?>
<script>
$('.custom-file-input').on('change', function() {
   let fileName = $(this).val().split('\\').pop();
   $(this).next('.custom-file-label').addClass("selected").html(fileName);
});
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/mahasiswa', [
    'content' => $content, 
    'styles' => isset($styles) ? $styles : '', 
    'script' => $script
]); 
?>