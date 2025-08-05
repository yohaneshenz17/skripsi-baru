<?php
// PERBAIKAN UNTUK: application/views/mahasiswa/publikasi/form.php
// Sesuai workflow step 2
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= $title ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/publikasi') ?>">Publikasi</a></li>
                        <li class="breadcrumb-item active"><?= $action == 'ajukan' ? 'Ajukan' : 'Edit' ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Flash Messages -->
            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-ban"></i> <?= $this->session->flashdata('error') ?>
            </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-upload"></i> 
                        <?= $action == 'ajukan' ? 'Form Pengajuan' : 'Edit' ?> Publikasi Tugas Akhir
                    </h3>
                </div>
                
                <?php 
                $form_action = $action == 'ajukan' ? 
                    base_url('mahasiswa/publikasi/ajukan/' . $proposal->id) : 
                    base_url('mahasiswa/publikasi/edit/' . $publikasi->id);
                ?>
                
                <form action="<?= $form_action ?>" method="POST" enctype="multipart/form-data" id="formPublikasi">
                    <div class="card-body">
                        
                        <!-- Data Mahasiswa (Auto-fill) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>1. Nama Lengkap</label>
                                    <input type="text" class="form-control" value="<?= $this->session->userdata('nama') ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>2. NIM</label>
                                    <input type="text" class="form-control" value="<?= $this->session->userdata('nim') ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>3. Program Studi</label>
                                    <input type="text" class="form-control" value="<?= $this->session->userdata('nama_prodi') ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>5. Dosen Pembimbing</label>
                                    <input type="text" class="form-control" value="<?= isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : '' ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>4. Judul Skripsi Final <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="judul_skripsi_final" rows="3" required 
                                      placeholder="Masukkan judul skripsi final"><?= isset($publikasi->judul_skripsi_final) ? $publikasi->judul_skripsi_final : (isset($proposal->judul) ? $proposal->judul : '') ?></textarea>
                        </div>

                        <!-- File Upload Section -->
                        <hr>
                        <h5><i class="fas fa-upload"></i> Upload Dokumen</h5>
                        
                        <div class="form-group">
                            <label>6. Surat Keterangan Revisi Tugas Akhir <span class="text-danger">*</span></label>
                            <input type="file" class="form-control-file" name="file_surat_revisi" accept=".pdf" required>
                            <small class="text-muted">Format PDF, maksimal 1 MB. <a href="<?= base_url('assets/templates/template_surat_revisi.pdf') ?>" target="_blank">Download Template</a></small>
                            
                            <?php if (isset($publikasi->file_surat_revisi) && $publikasi->file_surat_revisi): ?>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check"></i> File sudah diupload: 
                                        <a href="<?= base_url('uploads/publikasi/surat_revisi/' . $publikasi->file_surat_revisi) ?>" target="_blank">
                                            <?= $publikasi->file_surat_revisi ?>
                                        </a>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>7. File Skripsi Final <span class="text-danger">*</span></label>
                            <input type="file" class="form-control-file" name="file_skripsi_final" accept=".pdf" required>
                            <small class="text-muted">Format PDF, maksimal 5 MB</small>
                            
                            <?php if (isset($publikasi->file_skripsi_final) && $publikasi->file_skripsi_final): ?>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check"></i> File sudah diupload: 
                                        <a href="<?= base_url('uploads/publikasi/skripsi_final/' . $publikasi->file_skripsi_final) ?>" target="_blank">
                                            <?= $publikasi->file_skripsi_final ?>
                                        </a>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>8. Surat Keterangan Penyerahan Skripsi dari Perpustakaan <span class="text-danger">*</span></label>
                            <input type="file" class="form-control-file" name="file_surat_perpustakaan" accept=".pdf" required>
                            <small class="text-muted">Format PDF, maksimal 1 MB</small>
                            
                            <?php if (isset($publikasi->file_surat_perpustakaan) && $publikasi->file_surat_perpustakaan): ?>
                                <div class="mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check"></i> File sudah diupload: 
                                        <a href="<?= base_url('uploads/publikasi/surat_perpustakaan/' . $publikasi->file_surat_perpustakaan) ?>" target="_blank">
                                            <?= $publikasi->file_surat_perpustakaan ?>
                                        </a>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>9. Link Repository/Publikasi Tugas Akhir (Opsional)</label>
                            <input type="url" class="form-control" name="link_repository" 
                                   value="<?= isset($publikasi->link_repository) ? $publikasi->link_repository : '' ?>"
                                   placeholder="https://repository.example.com/skripsi/...">
                            <small class="text-muted">Link ini dapat diinputkan oleh staf setelah validasi dosen</small>
                        </div>

                        <!-- Keterangan Tambahan -->
                        <div class="form-group">
                            <label>Keterangan Tambahan</label>
                            <textarea class="form-control" name="keterangan_mahasiswa" rows="3" 
                                      placeholder="Keterangan atau catatan tambahan (opsional)"><?= isset($publikasi->keterangan_mahasiswa) ? $publikasi->keterangan_mahasiswa : '' ?></textarea>
                        </div>

                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> 
                            <?= $action == 'ajukan' ? 'Ajukan Publikasi' : 'Update Publikasi' ?>
                        </button>
                        <a href="<?= base_url('mahasiswa/publikasi') ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Validasi file size
    $('input[type="file"]').change(function() {
        const fileInput = this;
        const file = fileInput.files[0];
        const maxSize = fileInput.name === 'file_skripsi_final' ? 5 * 1024 * 1024 : 1 * 1024 * 1024; // 5MB untuk skripsi, 1MB untuk yang lain
        
        if (file && file.size > maxSize) {
            alert('Ukuran file terlalu besar. Maksimal ' + (maxSize / 1024 / 1024) + ' MB');
            fileInput.value = '';
        }
    });
    
    // Konfirmasi submit
    $('#formPublikasi').submit(function(e) {
        if (!confirm('Yakin ingin <?= $action == "ajukan" ? "mengajukan" : "mengupdate" ?> publikasi ini? Pastikan semua data sudah benar.')) {
            e.preventDefault();
        }
    });
});
</script>