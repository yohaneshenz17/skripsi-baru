<?php
ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Form Penetapan Pembimbing & Penguji</h3>
                <small class="text-muted">Workflow Terbaru - Penetapan Pembimbing dan Penguji</small>
            </div>
            <div class="card-body">
                
                <!-- Data Mahasiswa dan Proposal -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4><i class="ni ni-single-02"></i> Data Mahasiswa</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="30%"><strong>NIM</strong></td>
                                        <td>: <?= $proposal->nim ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama</strong></td>
                                        <td>: <?= $proposal->nama_mahasiswa ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>: <?= $proposal->email ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Prodi</strong></td>
                                        <td>: <?= $proposal->nama_prodi ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis Kelamin</strong></td>
                                        <td>: <?= ucfirst($proposal->jenis_kelamin) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Telepon</strong></td>
                                        <td>: <?= $proposal->nomor_telepon ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="ni ni-paper-diploma"></i> Detail Proposal</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Judul Proposal:</h5>
                                <div class="alert alert-info mb-2">
                                    <strong><?= $proposal->judul ?></strong>
                                </div>
                                
                                <?php if (!empty($proposal->jenis_penelitian)): ?>
                                <p><strong>Jenis Penelitian:</strong> <?= $proposal->jenis_penelitian ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($proposal->lokasi_penelitian)): ?>
                                <p><strong>Lokasi Penelitian:</strong> <?= $proposal->lokasi_penelitian ?></p>
                                <?php endif; ?>
                                
                                <p><strong>Ringkasan:</strong></p>
                                <div class="text-muted" style="font-size: 0.9em; max-height: 100px; overflow-y: auto;">
                                    <?= nl2br(substr($proposal->ringkasan, 0, 300)) ?>
                                    <?php if (strlen($proposal->ringkasan) > 300): ?>
                                        <span class="text-primary">... [Selengkapnya]</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Form Penetapan -->
                <form method="post" action="<?= base_url() ?>kaprodi/dashboard/penetapan_pembimbing/simpan" id="formPenetapan">
                    <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                    
                    <h4 class="mb-3"><i class="ni ni-hat-3"></i> Penetapan Dosen</h4>
                    
                    <?php if($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_pembimbing_id">
                                    <i class="ni ni-single-02"></i> Dosen Pembimbing <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="dosen_pembimbing_id" id="dosen_pembimbing_id" required>
                                    <option value="">-- Pilih Dosen Pembimbing --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= ($proposal->dosen_id == $d->id) ? 'selected' : '' ?>>
                                            <?= $d->nama ?>
                                            <?php if ($d->level == '4'): ?>
                                                <small>(Kaprodi)</small>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Pilih dosen yang akan membimbing mahasiswa</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_penguji1_id">
                                    <i class="ni ni-user-run"></i> Dosen Penguji 1 <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="dosen_penguji1_id" id="dosen_penguji1_id" required>
                                    <option value="">-- Pilih Dosen Penguji 1 --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= ($proposal->dosen_penguji_id == $d->id) ? 'selected' : '' ?>>
                                            <?= $d->nama ?>
                                            <?php if ($d->level == '4'): ?>
                                                <small>(Kaprodi)</small>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Pilih dosen penguji pertama</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_penguji2_id">
                                    <i class="ni ni-user-run"></i> Dosen Penguji 2 <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="dosen_penguji2_id" id="dosen_penguji2_id" required>
                                    <option value="">-- Pilih Dosen Penguji 2 --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= ($proposal->dosen_penguji2_id == $d->id) ? 'selected' : '' ?>>
                                            <?= $d->nama ?>
                                            <?php if ($d->level == '4'): ?>
                                                <small>(Kaprodi)</small>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Pilih dosen penguji kedua</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Tambahan -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6><i class="ni ni-bell-55"></i> Informasi Penting:</h6>
                                <ul class="mb-0">
                                    <li>Semua dosen (pembimbing dan penguji) harus berbeda</li>
                                    <li>Notifikasi akan dikirim otomatis ke mahasiswa dan dosen yang dipilih</li>
                                    <li>Setelah penetapan, mahasiswa dapat memulai proses bimbingan</li>
                                    <li>Penetapan ini akan masuk ke sistem workflow terbaru</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <a href="<?= base_url() ?>kaprodi/dashboard/penetapan_pembimbing" class="btn btn-secondary">
                            <i class="ni ni-bold-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Penetapan
                        </button>
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
        var pembimbing = $('#dosen_pembimbing_id').val();
        var penguji1 = $('#dosen_penguji1_id').val();
        var penguji2 = $('#dosen_penguji2_id').val();
        
        // Cek semua field terisi
        if (pembimbing === '' || penguji1 === '' || penguji2 === '') {
            e.preventDefault();
            alert('Semua dosen harus dipilih!');
            return false;
        }
        
        // Cek tidak ada yang sama
        if (pembimbing == penguji1 || pembimbing == penguji2 || penguji1 == penguji2) {
            e.preventDefault();
            alert('Pembimbing dan penguji harus orang yang berbeda!');
            return false;
        }
        
        // Konfirmasi sebelum submit
        e.preventDefault();
        if (confirm('Apakah Anda yakin ingin menetapkan dosen ini? Notifikasi akan dikirim ke mahasiswa dan dosen yang dipilih.')) {
            $('#formPenetapan')[0].submit();
        }
    });
    
    // Real-time validation saat memilih dosen
    $('select[name^="dosen_"]').on('change', function() {
        var pembimbing = $('#dosen_pembimbing_id').val();
        var penguji1 = $('#dosen_penguji1_id').val();
        var penguji2 = $('#dosen_penguji2_id').val();
        
        // Reset border colors
        $('select[name^="dosen_"]').removeClass('border-danger border-success');
        
        // Check for duplicates
        var values = [pembimbing, penguji1, penguji2];
        var hasDuplicate = values.some((val, index) => val && values.indexOf(val) !== index);
        
        if (hasDuplicate) {
            $('select[name^="dosen_"]').addClass('border-danger');
        } else if (pembimbing && penguji1 && penguji2) {
            $('select[name^="dosen_"]').addClass('border-success');
        }
    });
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