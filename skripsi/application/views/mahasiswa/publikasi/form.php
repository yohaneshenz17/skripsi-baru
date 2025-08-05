<?php
// =======================================================
// 2. FORM PENGAJUAN/EDIT
// File: application/views/mahasiswa/publikasi/form.php
// =======================================================
?>

<!-- FORM PENGAJUAN PUBLIKASI -->
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
                        <li class="breadcrumb-item active"><?= $action === 'ajukan' ? 'Ajukan' : 'Edit' ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Validation Errors -->
            <?php if (validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                <?= validation_errors() ?>
            </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-ban"></i> <?= $this->session->flashdata('error') ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-upload"></i> <?= $action === 'ajukan' ? 'Form Pengajuan' : 'Edit Pengajuan' ?> Publikasi
                            </h3>
                        </div>
                        
                        <?php 
                        $form_action = $action === 'ajukan' ? 
                            base_url('mahasiswa/publikasi/ajukan/' . (isset($proposal) ? $proposal->id : '')) : 
                            base_url('mahasiswa/publikasi/edit/' . (isset($publikasi) ? $publikasi->id : ''));
                        ?>
                        
                        <form action="<?= $form_action ?>" method="post" enctype="multipart/form-data">
                            <div class="card-body">
                                
                                <!-- Data Mahasiswa (Read Only) -->
                                <h5><i class="fas fa-user"></i> Data Mahasiswa</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nama Lengkap</label>
                                            <input type="text" class="form-control" readonly 
                                                   value="<?= isset($proposal) ? $proposal->nama_mahasiswa : (isset($publikasi) ? $publikasi->nama_mahasiswa : '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>NIM</label>
                                            <input type="text" class="form-control" readonly 
                                                   value="<?= isset($proposal) ? $proposal->nim : (isset($publikasi) ? $publikasi->nim : '') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Program Studi</label>
                                            <input type="text" class="form-control" readonly 
                                                   value="<?= isset($proposal) ? $proposal->nama_prodi : (isset($publikasi) ? $publikasi->program_studi : '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Dosen Pembimbing</label>
                                            <input type="text" class="form-control" readonly 
                                                   value="<?= isset($proposal) ? $proposal->nama_pembimbing : (isset($publikasi) ? $publikasi->nama_dosen_pembimbing : '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Data Skripsi -->
                                <h5><i class="fas fa-book"></i> Data Skripsi</h5>
                                <div class="form-group">
                                    <label for="judul_skripsi_final">Judul Skripsi Final <span class="text-danger">*</span></label>
                                    <textarea name="judul_skripsi_final" id="judul_skripsi_final" 
                                              class="form-control" rows="3" required 
                                              placeholder="Masukkan judul skripsi final (boleh berbeda dari proposal)"><?= set_value('judul_skripsi_final', isset($publikasi) ? $publikasi->judul_skripsi_final : '') ?></textarea>
                                    <small class="text-muted">Judul skripsi final yang akan dipublikasikan</small>
                                </div>

                                <div class="form-group">
                                    <label for="tanggal_ujian_skripsi">Tanggal Ujian Skripsi <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_ujian_skripsi" id="tanggal_ujian_skripsi" 
                                           class="form-control" required 
                                           value="<?= set_value('tanggal_ujian_skripsi', isset($publikasi) ? $publikasi->tanggal_ujian_skripsi : '') ?>">
                                    <small class="text-muted">Tanggal pelaksanaan ujian/seminar skripsi</small>
                                </div>

                                <hr>

                                <!-- Upload Files -->
                                <h5><i class="fas fa-upload"></i> Upload Dokumen</h5>
                                <p class="text-muted mb-3">Semua file harus dalam format PDF</p>

                                <div class="form-group">
                                    <label for="surat_revisi">1. Surat Keterangan Revisi <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="surat_revisi" id="surat_revisi" 
                                                   class="custom-file-input" accept=".pdf" 
                                                   <?= $action === 'ajukan' ? 'required' : '' ?>>
                                            <label class="custom-file-label" for="surat_revisi">Choose file</label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Format: PDF, Maksimal: 1MB</small>
                                    <?php if (isset($publikasi) && $publikasi->file_surat_revisi): ?>
                                        <div class="mt-1">
                                            <i class="fas fa-file-pdf text-danger"></i> 
                                            <a href="<?= base_url('mahasiswa/publikasi/download/' . $publikasi->id . '/surat_revisi') ?>" target="_blank">
                                                File saat ini: <?= $publikasi->file_surat_revisi ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="skripsi_final">2. File Skripsi Final <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="skripsi_final" id="skripsi_final" 
                                                   class="custom-file-input" accept=".pdf" 
                                                   <?= $action === 'ajukan' ? 'required' : '' ?>>
                                            <label class="custom-file-label" for="skripsi_final">Choose file</label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Format: PDF, Maksimal: 5MB</small>
                                    <?php if (isset($publikasi) && $publikasi->file_skripsi_final): ?>
                                        <div class="mt-1">
                                            <i class="fas fa-file-pdf text-danger"></i> 
                                            <a href="<?= base_url('mahasiswa/publikasi/download/' . $publikasi->id . '/skripsi_final') ?>" target="_blank">
                                                File saat ini: <?= $publikasi->file_skripsi_final ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="surat_perpustakaan">3. Surat Keterangan Perpustakaan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="surat_perpustakaan" id="surat_perpustakaan" 
                                                   class="custom-file-input" accept=".pdf" 
                                                   <?= $action === 'ajukan' ? 'required' : '' ?>>
                                            <label class="custom-file-label" for="surat_perpustakaan">Choose file</label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Format: PDF, Maksimal: 1MB</small>
                                    <?php if (isset($publikasi) && $publikasi->file_surat_perpustakaan): ?>
                                        <div class="mt-1">
                                            <i class="fas fa-file-pdf text-danger"></i> 
                                            <a href="<?= base_url('mahasiswa/publikasi/download/' . $publikasi->id . '/surat_perpustakaan') ?>" target="_blank">
                                                File saat ini: <?= $publikasi->file_surat_perpustakaan ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <hr>

                                <!-- Keterangan -->
                                <div class="form-group">
                                    <label for="keterangan_mahasiswa">Keterangan Tambahan</label>
                                    <textarea name="keterangan_mahasiswa" id="keterangan_mahasiswa" 
                                              class="form-control" rows="3" 
                                              placeholder="Keterangan atau catatan tambahan (opsional)"><?= set_value('keterangan_mahasiswa', isset($publikasi) ? $publikasi->keterangan_mahasiswa : '') ?></textarea>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?= $action === 'ajukan' ? 'Simpan Pengajuan' : 'Update Pengajuan' ?>
                                </button>
                                <a href="<?= base_url('mahasiswa/publikasi') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Panduan Upload</h3>
                        </div>
                        <div class="card-body">
                            <h6><strong>Dokumen yang Diperlukan:</strong></h6>
                            
                            <div class="mb-3">
                                <h6><i class="fas fa-file-pdf text-danger"></i> Surat Keterangan Revisi</h6>
                                <small class="text-muted">
                                    Surat yang menyatakan bahwa Anda telah menyelesaikan revisi berdasarkan hasil ujian skripsi.
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <h6><i class="fas fa-file-pdf text-danger"></i> File Skripsi Final</h6>
                                <small class="text-muted">
                                    File skripsi lengkap yang sudah final dan siap untuk dipublikasikan.
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <h6><i class="fas fa-file-pdf text-danger"></i> Surat Perpustakaan</h6>
                                <small class="text-muted">
                                    Surat keterangan dari perpustakaan yang menyatakan telah menyerahkan skripsi.
                                </small>
                            </div>
                            
                            <div class="alert alert-warning">
                                <small>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Penting:</strong> Pastikan semua file dalam format PDF dan ukuran sesuai ketentuan.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JavaScript untuk Custom File Input -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update custom file input labels
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function(e) {
            var fileName = e.target.files[0] ? e.target.files[0].name : 'Choose file';
            var label = e.target.nextElementSibling;
            label.textContent = fileName;
        });
    });
});
</script>