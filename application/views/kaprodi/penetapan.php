<?php
// [PERBAIKAN] Langsung mulai buffer, hapus pemanggilan template dari sini
ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Form Penetapan Pembimbing & Penguji</h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Data Mahasiswa</h4>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="30%">NIM</td>
                                <td>: <?= $proposal->nim ?></td>
                            </tr>
                            <tr>
                                <td>Nama</td>
                                <td>: <?= $proposal->nama_mahasiswa ?></td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>: <?= $proposal->email ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Judul Proposal</h4>
                        <div class="alert alert-info">
                            <?= $proposal->judul ?>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <form method="post" action="<?= base_url() ?>kaprodi/simpan_penetapan" id="formPenetapan">
                    <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                    
                    <h4 class="mb-3">Penetapan Dosen</h4>
                    
                    <?php if($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= $this->session->flashdata('error') ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_id">Dosen Pembimbing <span class="text-danger">*</span></label>
                                <select class="form-control" name="dosen_id" id="dosen_id" required>
                                    <option value="">-- Pilih Dosen Pembimbing --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= $proposal->dosen_id == $d->id ? 'selected' : '' ?>><?= $d->nama ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_penguji_id">Dosen Penguji 1 <span class="text-danger">*</span></label>
                                <select class="form-control" name="dosen_penguji_id" id="dosen_penguji_id" required>
                                    <option value="">-- Pilih Dosen Penguji 1 --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= $proposal->dosen_penguji_id == $d->id ? 'selected' : '' ?>><?= $d->nama ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_penguji2_id">Dosen Penguji 2 <span class="text-danger">*</span></label>
                                <select class="form-control" name="dosen_penguji2_id" id="dosen_penguji2_id" required>
                                    <option value="">-- Pilih Dosen Penguji 2 --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= $proposal->dosen_penguji2_id == $d->id ? 'selected' : '' ?>><?= $d->nama ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <a href="<?= base_url() ?>kaprodi/proposal" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Penetapan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 

ob_start(); 
?>
<script>
$(document).ready(function() {
    // Validasi frontend sebelum submit
    $('#formPenetapan').on('submit', function(e) {
        var pembimbing = $('#dosen_id').val();
        var penguji1 = $('#dosen_penguji_id').val();
        var penguji2 = $('#dosen_penguji2_id').val();
        
        if (pembimbing === '' || penguji1 === '' || penguji2 === '') {
            alert('Semua dosen harus dipilih!');
            e.preventDefault();
            return false;
        }
        
        if(pembimbing == penguji1 || pembimbing == penguji2 || penguji1 == penguji2) {
            alert('Pembimbing dan penguji harus orang yang berbeda!');
            e.preventDefault();
            return false;
        }
    });
});
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Form Penetapan',
    'content' => $content,
    'script' => $script
]); 
?>