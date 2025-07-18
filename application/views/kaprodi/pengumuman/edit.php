<?php
ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Edit Pengumuman Tahapan Skripsi</h3>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url() ?>kaprodi/pengumuman" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url() ?>kaprodi/pengumuman/update/<?= $pengumuman->id ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">No. Urut <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="no" required min="1" value="<?= $pengumuman->no ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Tanggal Deadline <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_deadline" required value="<?= $pengumuman->tanggal_deadline ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Nama Tahapan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="tahapan" required value="<?= $pengumuman->tahapan ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Masukkan keterangan tambahan (opsional)"><?= $pengumuman->keterangan ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="aktif" name="aktif" <?= $pengumuman->aktif == '1' ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="aktif">
                                Aktifkan pengumuman ini
                            </label>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-right">
                        <a href="<?= base_url() ?>kaprodi/pengumuman" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => $title,
    'content' => $content
]); 
?>