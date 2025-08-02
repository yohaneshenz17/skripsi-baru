<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Penilaian Seminar Skripsi</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('dosen') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('dosen/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                    <li class="breadcrumb-item active">Penilaian</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Form Penilaian -->
        <form action="<?= base_url('dosen/seminar_skripsi/penilaian/' . $seminar->id) ?>" method="POST" 
              id="form-penilaian" novalidate>
              
            <div class="row">
                <!-- Form Input -->
                <div class="col-md-8">
                    
                    <!-- Informasi Seminar -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-2"></i>
                                Informasi Seminar Skripsi
                            </h3>
                            <div class="card-tools">
                                <?php if ($penilaian && $penilaian->status_penilaian == 'published'): ?>
                                    <span class="badge badge-success badge-lg">Published</span>
                                <?php elseif ($penilaian && $penilaian->status_penilaian == 'draft'): ?>
                                    <span class="badge badge-warning badge-lg">Draft</span>
                                <?php else: ?>
                                    <span class="badge badge-info badge-lg">Baru</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="font-weight-bold">Mahasiswa</td>
                                            <td>:</td>
                                            <td><?= $seminar->nama_mahasiswa ?> (<?= $seminar->nim ?>)</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Tanggal Seminar</td>
                                            <td>:</td>
                                            <td><?= $seminar->tanggal_seminar ? date('d F Y', strtotime($seminar->tanggal_seminar)) : 'Belum dijadwalkan' ?></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Waktu & Tempat</td>
                                            <td>:</td>
                                            <td>
                                                <?php if ($seminar->jam_seminar && $seminar->tempat_seminar): ?>
                                                    <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIB - <?= $seminar->tempat_seminar ?>
                                                <?php else: ?>
                                                    Belum dijadwalkan
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="font-weight-bold">Pembimbing</td>
                                            <td>:</td>
                                            <td><?= $seminar->nama_pembimbing ?></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Penguji 1</td>
                                            <td>:</td>
                                            <td><?= $dosen_penguji1 ? $dosen_penguji1->nama : 'Belum ditentukan' ?></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Penguji 2</td>
                                            <td>:</td>
                                            <td><?= $dosen_penguji2 ? $dosen_penguji2->nama : 'Belum ditentukan' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Judul Skripsi -->
                            <div class="mt-3">
                                <strong>Judul Skripsi:</strong>
                                <p class="mt-2 p-3 bg-light border-left border-primary">
                                    <?= $seminar->judul ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Revisi Seminar Skripsi (6 Komponen) -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-edit mr-2"></i>
                                Catatan Revisi Seminar Skripsi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="catatan_pendahuluan">
                                            <i class="fas fa-file-alt mr-2"></i>
                                            Bab I: Pendahuluan
                                        </label>
                                        <textarea name="catatan_pendahuluan" id="catatan_pendahuluan" 
                                                  class="form-control" rows="4" 
                                                  placeholder="Catatan revisi untuk Bab I: Pendahuluan (Latar Belakang, Rumusan Masalah, Tujuan)"><?= $penilaian ? $penilaian->catatan_pendahuluan : '' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="catatan_tinjauan_pustaka">
                                            <i class="fas fa-book mr-2"></i>
                                            Bab II: Tinjauan Pustaka
                                        </label>
                                        <textarea name="catatan_tinjauan_pustaka" id="catatan_tinjauan_pustaka" 
                                                  class="form-control" rows="4" 
                                                  placeholder="Catatan revisi untuk Bab II: Tinjauan Pustaka & Landasan Teori"><?= $penilaian ? $penilaian->catatan_tinjauan_pustaka : '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="catatan_metodologi">
                                            <i class="fas fa-cogs mr-2"></i>
                                            Bab III: Metodologi
                                        </label>
                                        <textarea name="catatan_metodologi" id="catatan_metodologi" 
                                                  class="form-control" rows="4" 
                                                  placeholder="Catatan revisi untuk Bab III: Metodologi Penelitian"><?= $penilaian ? $penilaian->catatan_metodologi : '' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="catatan_hasil_pembahasan">
                                            <i class="fas fa-chart-bar mr-2"></i>
                                            Bab IV: Hasil & Pembahasan
                                        </label>
                                        <textarea name="catatan_hasil_pembahasan" id="catatan_hasil_pembahasan" 
                                                  class="form-control" rows="4" 
                                                  placeholder="Catatan revisi untuk Bab IV: Hasil Penelitian & Pembahasan"><?= $penilaian ? $penilaian->catatan_hasil_pembahasan : '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="catatan_kesimpulan">
                                            <i class="fas fa-flag-checkered mr-2"></i>
                                            Bab V: Kesimpulan & Saran
                                        </label>
                                        <textarea name="catatan_kesimpulan" id="catatan_kesimpulan" 
                                                  class="form-control" rows="4" 
                                                  placeholder="Catatan revisi untuk Bab V: Kesimpulan & Saran"><?= $penilaian ? $penilaian->catatan_kesimpulan : '' ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="catatan_umum">
                                            <i class="fas fa-comment-alt mr-2"></i>
                                            Catatan Umum
                                        </label>
                                        <textarea name="catatan_umum" id="catatan_umum" 
                                                  class="form-control" rows="4" 
                                                  placeholder="Catatan umum, sistematika penulisan, atau saran tambahan"><?= $penilaian ? $penilaian->catatan_umum : '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nilai Final Seminar Skripsi (Sistem 3 Dosen) -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-star mr-2"></i>
                                Nilai Final Seminar Skripsi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Sistem Penilaian 3 Dosen:</strong> 
                                Nilai akhir dihitung dari rata-rata nilai Penguji 1, Penguji 2, dan Pembimbing.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nilai_penguji1">
                                            <i class="fas fa-user-tie mr-2"></i>
                                            Nilai Penguji 1 (0-100)
                                        </label>
                                        <input type="number" name="nilai_penguji1" id="nilai_penguji1" 
                                               class="form-control nilai-input" 
                                               min="0" max="100" step="0.01"
                                               value="<?= $penilaian ? $penilaian->nilai_penguji1 : '' ?>"
                                               placeholder="0.00">
                                        <?php if ($dosen_penguji1): ?>
                                            <small class="text-muted"><?= $dosen_penguji1->nama ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nilai_penguji2">
                                            <i class="fas fa-user-tie mr-2"></i>
                                            Nilai Penguji 2 (0-100)
                                        </label>
                                        <input type="number" name="nilai_penguji2" id="nilai_penguji2" 
                                               class="form-control nilai-input" 
                                               min="0" max="100" step="0.01"
                                               value="<?= $penilaian ? $penilaian->nilai_penguji2 : '' ?>"
                                               placeholder="0.00">
                                        <?php if ($dosen_penguji2): ?>
                                            <small class="text-muted"><?= $dosen_penguji2->nama ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nilai_pembimbing">
                                            <i class="fas fa-user-graduate mr-2"></i>
                                            Nilai Pembimbing (0-100)
                                        </label>
                                        <input type="number" name="nilai_pembimbing" id="nilai_pembimbing" 
                                               class="form-control nilai-input" 
                                               min="0" max="100" step="0.01"
                                               value="<?= $penilaian ? $penilaian->nilai_pembimbing : '' ?>"
                                               placeholder="0.00">
                                        <small class="text-muted"><?= $seminar->nama_pembimbing ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Nilai Akhir & Huruf -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nilai_akhir">
                                            <i class="fas fa-calculator mr-2"></i>
                                            Nilai Akhir (Auto Calculate)
                                        </label>
                                        <input type="number" name="nilai_akhir" id="nilai_akhir" 
                                               class="form-control" readonly
                                               value="<?= $penilaian ? $penilaian->nilai_akhir : '' ?>"
                                               style="background-color: #f8f9fa;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nilai_huruf">
                                            <i class="fas fa-font mr-2"></i>
                                            Nilai Huruf (Auto Convert)
                                        </label>
                                        <input type="text" name="nilai_huruf" id="nilai_huruf" 
                                               class="form-control" readonly
                                               value="<?= $penilaian ? $penilaian->nilai_huruf : '' ?>"
                                               style="background-color: #f8f9fa;">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Keterangan Konversi Nilai -->
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>Konversi Nilai:</strong> 
                                    A (≥80) | B (70-79.9) | C (60-69.9) | D (50-59.9) | E (<50)
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Rekomendasi Seminar Skripsi -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-thumbs-up mr-2"></i>
                                Rekomendasi Seminar Skripsi
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="rekomendasi">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Hasil Rekomendasi <span class="text-danger">*</span>
                                </label>
                                <select name="rekomendasi" id="rekomendasi" class="form-control" required>
                                    <option value="">-- Pilih Rekomendasi --</option>
                                    <option value="lulus_tanpa_revisi" 
                                            <?= ($penilaian && $penilaian->rekomendasi == 'lulus_tanpa_revisi') ? 'selected' : '' ?>>
                                        Lulus Tanpa Revisi
                                    </option>
                                    <option value="lulus_dengan_revisi_minor" 
                                            <?= ($penilaian && $penilaian->rekomendasi == 'lulus_dengan_revisi_minor') ? 'selected' : '' ?>>
                                        Lulus dengan Revisi Minor
                                    </option>
                                    <option value="lulus_dengan_revisi_mayor" 
                                            <?= ($penilaian && $penilaian->rekomendasi == 'lulus_dengan_revisi_mayor') ? 'selected' : '' ?>>
                                        Lulus dengan Revisi Mayor
                                    </option>
                                    <option value="tidak_lulus" 
                                            <?= ($penilaian && $penilaian->rekomendasi == 'tidak_lulus') ? 'selected' : '' ?>>
                                        Tidak Lulus
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="keterangan_rekomendasi">
                                    <i class="fas fa-comment mr-2"></i>
                                    Keterangan Rekomendasi
                                </label>
                                <textarea name="keterangan_rekomendasi" id="keterangan_rekomendasi" 
                                          class="form-control" rows="3" 
                                          placeholder="Berikan keterangan tambahan untuk rekomendasi (opsional)"><?= $penilaian ? $penilaian->keterangan_rekomendasi : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    
                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cogs mr-2"></i>
                                Aksi Penilaian
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if ($penilaian && $penilaian->status_penilaian == 'published'): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Penilaian sudah dipublikasi dan tidak dapat diubah.
                                </div>
                                <a href="<?= base_url('dosen/seminar_skripsi') ?>" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                                </a>
                            <?php else: ?>
                                <div class="d-grid gap-2">
                                    <button type="submit" name="action_type" value="draft" class="btn btn-warning btn-block">
                                        <i class="fas fa-save mr-2"></i>Simpan sebagai Draft
                                    </button>
                                    <button type="submit" name="action_type" value="publish" class="btn btn-success btn-block">
                                        <i class="fas fa-paper-plane mr-2"></i>Publikasi Penilaian
                                    </button>
                                    <a href="<?= base_url('dosen/seminar_skripsi') ?>" class="btn btn-secondary btn-block">
                                        <i class="fas fa-times mr-2"></i>Batal
                                    </a>
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Draft:</strong> Dapat diedit kembali<br>
                                        <strong>Publikasi:</strong> Final dan tidak dapat diubah
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Progress Info -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-2"></i>
                                Status Progress
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <?php
                                $progress = 0;
                                if ($seminar->status == 'scheduled') {
                                    $progress = 80;
                                } elseif ($seminar->status == 'completed') {
                                    $progress = 100;
                                }
                                ?>
                                <div class="progress mb-3" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?= $progress ?>%" 
                                         aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?= $progress ?>%
                                    </div>
                                </div>
                                
                                <div class="row text-center">
                                    <div class="col-6">
                                        <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                        <p class="mb-0"><small>Seminar</small></p>
                                        <strong class="text-primary">
                                            <?= $seminar->status == 'completed' ? 'Selesai' : 'Terjadwal' ?>
                                        </strong>
                                    </div>
                                    <div class="col-6">
                                        <i class="fas fa-clipboard-check fa-2x text-success mb-2"></i>
                                        <p class="mb-0"><small>Penilaian</small></p>
                                        <strong class="text-success">
                                            <?php if ($penilaian): ?>
                                                <?= $penilaian->status_penilaian == 'published' ? 'Selesai' : 'Draft' ?>
                                            <?php else: ?>
                                                Belum
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-link mr-2"></i>
                                Quick Links
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if ($seminar->file_skripsi): ?>
                                    <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                       class="btn btn-outline-primary btn-sm btn-block" target="_blank">
                                        <i class="fas fa-file-pdf mr-2"></i>Lihat File Skripsi
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $seminar->id) ?>" 
                                   class="btn btn-outline-info btn-sm btn-block">
                                    <i class="fas fa-eye mr-2"></i>Lihat Detail Seminar
                                </a>
                                
                                <a href="mailto:<?= $seminar->email_mahasiswa ?>?subject=Penilaian Seminar Skripsi" 
                                   class="btn btn-outline-secondary btn-sm btn-block">
                                    <i class="fas fa-envelope mr-2"></i>Email Mahasiswa
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Panduan Penilaian -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb mr-2"></i>
                                Panduan Penilaian
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="panduanAccordion">
                                <div class="card">
                                    <div class="card-header p-2" id="heading1">
                                        <h6 class="mb-0">
                                            <button class="btn btn-link btn-sm" type="button" data-toggle="collapse" 
                                                    data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                                <i class="fas fa-edit mr-2"></i>Catatan Revisi
                                            </button>
                                        </h6>
                                    </div>
                                    <div id="collapse1" class="collapse" aria-labelledby="heading1" data-parent="#panduanAccordion">
                                        <div class="card-body p-2">
                                            <small>
                                                Berikan catatan revisi spesifik untuk setiap bab skripsi yang perlu diperbaiki.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header p-2" id="heading2">
                                        <h6 class="mb-0">
                                            <button class="btn btn-link btn-sm collapsed" type="button" data-toggle="collapse" 
                                                    data-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                                <i class="fas fa-star mr-2"></i>Nilai Final
                                            </button>
                                        </h6>
                                    </div>
                                    <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#panduanAccordion">
                                        <div class="card-body p-2">
                                            <small>
                                                Nilai akhir dihitung otomatis dari rata-rata nilai 3 dosen (Penguji 1, Penguji 2, Pembimbing).
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header p-2" id="heading3">
                                        <h6 class="mb-0">
                                            <button class="btn btn-link btn-sm collapsed" type="button" data-toggle="collapse" 
                                                    data-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                                <i class="fas fa-thumbs-up mr-2"></i>Rekomendasi
                                            </button>
                                        </h6>
                                    </div>
                                    <div id="collapse3" class="collapse" aria-labelledby="heading3" data-parent="#panduanAccordion">
                                        <div class="card-body p-2">
                                            <small>
                                                Pilih rekomendasi sesuai kualitas skripsi dan presentasi mahasiswa.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>
</section>

<style>
.badge-lg {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.border-left {
    border-left: 4px solid !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
    margin-bottom: 1.5rem;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0.75rem;
}

.d-grid {
    display: grid;
}

.gap-2 {
    gap: 0.5rem;
}

.btn-block {
    display: block;
    width: 100%;
    margin-bottom: 0.5rem;
}

.fa-2x {
    font-size: 2em;
}

.nilai-input:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.progress {
    background-color: #e9ecef;
    border-radius: 0.375rem;
}

.accordion .card {
    border: 1px solid #dee2e6;
    margin-bottom: 0;
}

.accordion .card-header {
    background-color: transparent;
    border-bottom: 1px solid #dee2e6;
}

.btn-link {
    color: #495057;
    text-decoration: none;
}

.btn-link:hover {
    color: #007bff;
    text-decoration: none;
}
</style>