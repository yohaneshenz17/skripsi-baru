<?php $this->load->view('template/kaprodi', ['content' => '']); ob_start(); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Form Penetapan Pembimbing & Penguji</h3>
            </div>
            <div class="card-body">
                <!-- Info Mahasiswa -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Data Mahasiswa</h4>
                        <table class="table table-sm">
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
                
                <div class="row mb-3">
                    <div class="col-12">
                        <h4>Ringkasan Proposal</h4>
                        <div class="card bg-secondary">
                            <div class="card-body">
                                <p><?= $proposal->ringkasan ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Form Penetapan -->
                <form method="post" action="<?= base_url() ?>kaprodi/simpan_penetapan" id="formPenetapan">
                    <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                    
                    <h4 class="mb-3">Penetapan Pembimbing & Penguji</h4>
                    
                    <?php if($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_id">
                                    <i class="fa fa-user-tie text-primary"></i> Dosen Pembimbing <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="dosen_id" id="dosen_id" required>
                                    <option value="">-- Pilih Dosen Pembimbing --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= $proposal->dosen_id == $d->id ? 'selected' : '' ?>>
                                            <?= $d->nama ?> (<?= $d->nip ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Dosen yang akan membimbing mahasiswa dalam penyusunan skripsi</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_penguji_id">
                                    <i class="fa fa-user-check text-success"></i> Dosen Penguji 1 <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="dosen_penguji_id" id="dosen_penguji_id" required>
                                    <option value="">-- Pilih Dosen Penguji 1 --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= $proposal->dosen_penguji_id == $d->id ? 'selected' : '' ?>>
                                            <?= $d->nama ?> (<?= $d->nip ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Dosen penguji pertama untuk seminar proposal</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_penguji2_id">
                                    <i class="fa fa-user-check text-warning"></i> Dosen Penguji 2 <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="dosen_penguji2_id" id="dosen_penguji2_id" required>
                                    <option value="">-- Pilih Dosen Penguji 2 --</option>
                                    <?php foreach($dosens as $d): ?>
                                        <option value="<?= $d->id ?>" <?= isset($proposal->dosen_penguji2_id) && $proposal->dosen_penguji2_id == $d->id ? 'selected' : '' ?>>
                                            <?= $d->nama ?> (<?= $d->nip ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Dosen penguji kedua untuk seminar proposal</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">
                                    <i class="fa fa-calendar text-info"></i> Deadline Pengerjaan
                                </label>
                                <input type="datetime-local" class="form-control" name="deadline" 
                                    value="<?= $proposal->deadline ? date('Y-m-d\TH:i', strtotime($proposal->deadline)) : '' ?>">
                                <small class="text-muted">Opsional: Tentukan batas waktu pengerjaan proposal</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> <strong>Perhatian:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Pembimbing dan kedua penguji harus berbeda orang</li>
                            <li>Pastikan dosen yang dipilih memiliki kapasitas untuk membimbing/menguji</li>
                            <li>Setelah ditetapkan, mahasiswa akan menerima notifikasi email</li>
                        </ul>
                    </div>
                    
                    <div class="text-right">
                        <a href="<?= base_url() ?>kaprodi/proposal" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Penetapan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Validasi form
    $('#formPenetapan').on('submit', function(e) {
        e.preventDefault();
        
        var pembimbing = $('#dosen_id').val();
        var penguji1 = $('#dosen_penguji_id').val();
        var penguji2 = $('#dosen_penguji2_id').val();
        
        // Validasi tidak boleh sama
        if(pembimbing == penguji1 || pembimbing == penguji2 || penguji1 == penguji2) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Pembimbing dan penguji harus berbeda orang!'
            });
            return false;
        }
        
        // Konfirmasi
        Swal.fire({
            title: 'Konfirmasi Penetapan',
            text: 'Apakah Anda yakin akan menetapkan pembimbing dan penguji ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Tetapkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });
    
    // Select2 untuk dropdown
    $('select').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});
</script>
<?php $script = ob_get_clean(); ?>

<?php $this->load->view('template/kaprodi', ['content' => $content, 'script' => $script]); ?>