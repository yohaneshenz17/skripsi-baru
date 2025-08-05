<?php
// =================================================================
// 2. VIEW REVIEW - Form Review Publikasi
// File: application/views/dosen/publikasi/review.php
// =================================================================
?>

<style>
    .file-info {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .syarat-info {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 15px;
        margin-bottom: 20px;
    }
    .form-group label {
        font-weight: 600;
        color: #495057;
    }
    .btn-file-preview {
        margin-left: 10px;
    }
</style>

<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('dosen/publikasi') ?>">Publikasi</a>
                    </li>
                    <li class="breadcrumb-item active">Review</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-clipboard-check mr-2"></i>
                Review Publikasi - <?= htmlspecialchars($publikasi->nama_mahasiswa) ?>
            </h1>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Detail Mahasiswa & Syarat -->
        <div class="col-lg-4">
            <!-- Info Mahasiswa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-graduate mr-2"></i>
                        Info Mahasiswa
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="font-weight-bold">Nama:</td>
                            <td><?= htmlspecialchars($publikasi->nama_mahasiswa) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">NIM:</td>
                            <td><?= $publikasi->nim ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Prodi:</td>
                            <td><?= $publikasi->nama_prodi ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email:</td>
                            <td><?= $publikasi->email_mahasiswa ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tanggal Pengajuan:</td>
                            <td><?= date('d/m/Y H:i', strtotime($publikasi->tanggal_pengajuan)) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Validasi Syarat -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks mr-2"></i>
                        Validasi Syarat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="syarat-info">
                        <h6 class="font-weight-bold">Jurnal Bimbingan</h6>
                        <p class="mb-2">
                            Jumlah jurnal tervalidasi: 
                            <span class="badge badge-<?= $jurnal_count >= $min_jurnal_required ? 'success' : 'warning' ?>">
                                <?= $jurnal_count ?>/<?= $min_jurnal_required ?>
                            </span>
                        </p>
                        <?php if ($jurnal_count >= $min_jurnal_required): ?>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> Syarat jurnal bimbingan terpenuhi
                            </small>
                        <?php else: ?>
                            <small class="text-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Dibutuhkan minimal <?= $min_jurnal_required - $jurnal_count ?> jurnal lagi
                            </small>
                        <?php endif; ?>
                    </div>

                    <?php if (!$can_approve): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            <strong>Perhatian:</strong> Syarat belum terpenuhi. Pengajuan hanya bisa ditolak untuk diperbaiki.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Form Review -->
        <div class="col-lg-8">
            <!-- Detail Pengajuan -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt mr-2"></i>
                        Detail Pengajuan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Judul Skripsi:</label>
                        <p class="form-control-plaintext border p-2 bg-light">
                            <?= htmlspecialchars($publikasi->judul_skripsi) ?>
                        </p>
                    </div>

                    <?php if (!empty($publikasi->keterangan_mahasiswa)): ?>
                        <div class="form-group">
                            <label>Keterangan Mahasiswa:</label>
                            <p class="form-control-plaintext border p-2 bg-light">
                                <?= nl2br(htmlspecialchars($publikasi->keterangan_mahasiswa)) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- File-file yang diupload -->
                    <div class="form-group">
                        <label>Dokumen yang Diupload:</label>
                        
                        <?php if (!empty($publikasi->file_skripsi_final)): ?>
                            <div class="file-info">
                                <strong>Skripsi Final:</strong> <?= $publikasi->file_skripsi_final ?>
                                <button type="button" class="btn btn-sm btn-primary btn-file-preview btn-preview" 
                                        data-url="<?= $publikasi->file_skripsi_final_path ?>">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($publikasi->file_surat_revisi)): ?>
                            <div class="file-info">
                                <strong>Surat Revisi:</strong> <?= $publikasi->file_surat_revisi ?>
                                <button type="button" class="btn btn-sm btn-primary btn-file-preview btn-preview" 
                                        data-url="<?= $publikasi->file_surat_revisi_path ?>">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($publikasi->file_surat_perpustakaan)): ?>
                            <div class="file-info">
                                <strong>Surat Perpustakaan:</strong> <?= $publikasi->file_surat_perpustakaan ?>
                                <button type="button" class="btn btn-sm btn-primary btn-file-preview btn-preview" 
                                        data-url="<?= $publikasi->file_surat_perpustakaan_path ?>">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Form Review -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        Form Review
                    </h6>
                </div>
                <div class="card-body">
                    <?= form_open('dosen/publikasi/review/' . $publikasi->id) ?>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Keputusan Review: <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rekomendasi" id="approved" 
                                       value="approved" <?= !$can_approve ? 'disabled' : '' ?>>
                                <label class="form-check-label text-success font-weight-bold" for="approved">
                                    <i class="fas fa-check-circle"></i> Setujui Publikasi
                                </label>
                                <?php if (!$can_approve): ?>
                                    <small class="text-muted d-block">Tidak dapat disetujui karena syarat belum terpenuhi</small>
                                <?php endif; ?>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rekomendasi" id="rejected" value="rejected">
                                <label class="form-check-label text-danger font-weight-bold" for="rejected">
                                    <i class="fas fa-times-circle"></i> Tolak (Perlu Perbaikan)
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="komentar-group" style="display:none;">
                            <label for="komentar_pembimbing" class="font-weight-bold">
                                Komentar/Catatan: 
                                <span class="text-danger" id="komentar-required" style="display:none;">*</span>
                            </label>
                            <textarea class="form-control" id="komentar_pembimbing" name="komentar_pembimbing" 
                                      rows="4" placeholder="Berikan komentar atau catatan untuk mahasiswa..."></textarea>
                            <small class="form-text text-muted">
                                Komentar akan dikirim ke mahasiswa melalui email
                            </small>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim Review
                            </button>
                            <a href="<?= base_url('dosen/publikasi') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>

                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>

</div>