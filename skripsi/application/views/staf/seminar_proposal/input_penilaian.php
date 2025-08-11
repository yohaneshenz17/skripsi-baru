<?php
/**
 * Staf Seminar Proposal Input Penilaian View - SIM TA STK Santo Yakobus
 * 
 * View untuk input penilaian seminar proposal dari perspektif staf akademik
 * Menggunakan rubrik penilaian sesuai dokumen yang dilampirkan
 * Staf memiliki hak akses yang sama seperti dosen untuk input penilaian
 * 
 * File: application/views/staf/seminar_proposal/input_penilaian.php
 * Controller: staf/Seminar_proposal::input_penilaian()
 * 
 * Features:
 * - Form penilaian berdasarkan rubrik yang dilampirkan
 * - Auto-calculate nilai berdasarkan bobot komponen
 * - Rekomendasi final dan catatan saran
 * - Validasi input real-time
 * - Save/update existing penilaian
 * 
 * @package     SIM_TA
 * @subpackage  Views/Staf
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 */
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-edit mr-2 text-success"></i>
                    <?= $is_edit ? 'Edit' : 'Input' ?> Penilaian Seminar Proposal
                </h1>
                <p class="text-muted">Penilaian untuk: <?= $seminar->nama_mahasiswa ?> (<?= $seminar->nim ?>)</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/seminar_proposal') ?>">Seminar Proposal</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/seminar_proposal/detail/' . $seminar->id) ?>">Detail</a></li>
                    <li class="breadcrumb-item active"><?= $is_edit ? 'Edit' : 'Input' ?> Penilaian</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Alert Messages -->
        <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Validation Errors -->
        <?php if(validation_errors()): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Kesalahan Validasi:</strong>
                <?= validation_errors() ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="row mb-3">
            <div class="col-12">
                <a href="<?= base_url('staf/seminar_proposal/detail/' . $seminar->id) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Detail
                </a>
            </div>
        </div>

        <!-- Main Form -->
        <form method="POST" action="<?= current_url() ?>" id="penilaianForm">
            <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
            
            <div class="row">
                <!-- Left Column: Form Penilaian -->
                <div class="col-md-8">
                    
                    <!-- Info Mahasiswa -->
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">
                                <i class="fas fa-user mr-2"></i>
                                Informasi Mahasiswa & Proposal
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td width="40%"><strong>NIM</strong></td>
                                            <td width="5%">:</td>
                                            <td><?= $seminar->nim ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama</strong></td>
                                            <td>:</td>
                                            <td><?= $seminar->nama_mahasiswa ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Prodi</strong></td>
                                            <td>:</td>
                                            <td><?= $seminar->nama_prodi ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td width="30%"><strong>Tanggal</strong></td>
                                            <td width="5%">:</td>
                                            <td><?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Waktu</strong></td>
                                            <td>:</td>
                                            <td><?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tempat</strong></td>
                                            <td>:</td>
                                            <td><?= $seminar->tempat_seminar ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">Judul Proposal:</label>
                                <p class="text-muted mb-0"><?= $seminar->judul ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- KOMPONEN 1: Substansi dan Metode Penelitian (50%) -->
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">
                                <i class="fas fa-book mr-2"></i>
                                Komponen 1: Substansi dan Metode Penelitian (Bobot: 50%)
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                Komponen ini menilai kualitas dokumen proposal yang diajukan. 
                                Penilaian berfokus pada kedalaman, logika, dan kelayakan ilmiah dari rencana penelitian.
                            </p>
                            
                            <!-- Indikator 1.1 -->
                            <div class="form-group">
                                <label class="font-weight-bold">1.1 Latar Belakang & Rumusan Masalah <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_substansi_1_1" id="nilai_substansi_1_1"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_substansi_1_1 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Masalah sangat relevan, didukung data/fakta kuat, dan dirumuskan dengan sangat tajam dan jelas.<br>
                                            <strong>Cukup:</strong> Masalah relevan, ada data pendukung, rumusan cukup jelas.<br>
                                            <strong>Lemah:</strong> Konteks masalah tidak jelas, tidak ada urgensi, rumusan terlalu luas/kabur.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indikator 1.2 -->
                            <div class="form-group">
                                <label class="font-weight-bold">1.2 Tinjauan Pustaka & Kebaruan (Novelty) <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_substansi_1_2" id="nilai_substansi_1_2"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_substansi_1_2 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Mampu memetakan state-of-the-art dengan baik, menunjukkan research gap secara eksplisit, dan posisi penelitian sangat jelas.<br>
                                            <strong>Cukup:</strong> Ada tinjauan pustaka yang relevan, upaya menunjukkan kebaruan ada tapi kurang tajam.<br>
                                            <strong>Lemah:</strong> Tinjauan pustaka minim, tidak menunjukkan kebaruan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indikator 1.3 -->
                            <div class="form-group">
                                <label class="font-weight-bold">1.3 Landasan Teori <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_substansi_1_3" id="nilai_substansi_1_3"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_substansi_1_3 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Teori yang digunakan sangat relevan, mendalam, dan mampu menjadi pisau analisis yang tajam.<br>
                                            <strong>Cukup:</strong> Teori yang digunakan relevan, namun pembahasannya standar.<br>
                                            <strong>Lemah:</strong> Teori tidak tepat atau hanya tempelan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indikator 1.4 -->
                            <div class="form-group">
                                <label class="font-weight-bold">1.4 Metodologi Penelitian <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_substansi_1_4" id="nilai_substansi_1_4"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_substansi_1_4 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Metode sangat tepat untuk menjawab rumusan masalah. Teknik pengumpulan & analisis data dijelaskan rinci, sistematis, dan logis.<br>
                                            <strong>Cukup:</strong> Pilihan metode bisa diterima. Penjelasan teknik cukup jelas tapi kurang detail.<br>
                                            <strong>Lemah:</strong> Metode tidak sesuai, rancu, atau tidak mungkin dilaksanakan (infeasible).
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indikator 1.5 -->
                            <div class="form-group">
                                <label class="font-weight-bold">1.5 Sistematika & Tata Tulis <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_substansi_1_5" id="nilai_substansi_1_5"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_substansi_1_5 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Alur tulisan sangat logis. Menggunakan bahasa baku, format sitasi konsisten, dan bebas dari kesalahan tik.<br>
                                            <strong>Cukup:</strong> Alur tulisan cukup baik, ada beberapa kesalahan tata tulis atau sitasi.<br>
                                            <strong>Lemah:</strong> Tulisan tidak terstruktur, banyak kesalahan tata bahasa.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Rata-rata Komponen 1 -->
                            <div class="bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Rata-rata Skor Komponen 1:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="badge badge-primary badge-lg" id="rata_rata_substansi">0.0</span>
                                        <small class="text-muted">(Total Skor / 5)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KOMPONEN 2: Presentasi dan Teknik Penyajian (20%) -->
                    <div class="card">
                        <div class="card-header bg-success">
                            <h3 class="card-title">
                                <i class="fas fa-presentation mr-2"></i>
                                Komponen 2: Presentasi dan Teknik Penyajian (Bobot: 20%)
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                Komponen ini menilai kemampuan mahasiswa dalam mengomunikasikan ide penelitiannya secara lisan dan visual.
                            </p>
                            
                            <!-- Indikator 2.1 -->
                            <div class="form-group">
                                <label class="font-weight-bold">2.1 Kejelasan & Alur Penyampaian <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_presentasi_2_1" id="nilai_presentasi_2_1"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_presentasi_2_1 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Berbicara dengan jelas, runtut, dan langsung ke poin-poin penting. Tidak hanya membaca slide.<br>
                                            <strong>Cukup:</strong> Penyampaian cukup jelas, namun terkadang berbelit-belit atau terlalu banyak membaca.<br>
                                            <strong>Lemah:</strong> Sulit dipahami, tidak terstruktur, atau gugup berlebihan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indikator 2.2 -->
                            <div class="form-group">
                                <label class="font-weight-bold">2.2 Desain Media Presentasi (Slide) <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_presentasi_2_2" id="nilai_presentasi_2_2"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_presentasi_2_2 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Slide efektif, visual menarik, ringkas, dan sangat membantu pemahaman.<br>
                                            <strong>Cukup:</strong> Slide cukup informatif, namun desain standar atau terlalu padat teks.<br>
                                            <strong>Lemah:</strong> Slide tidak efektif, sulit dibaca, atau isinya hanya salinan dari naskah.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indikator 2.3 -->
                            <div class="form-group">
                                <label class="font-weight-bold">2.3 Manajemen Waktu & Etika <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_presentasi_2_3" id="nilai_presentasi_2_3"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_presentasi_2_3 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Menyelesaikan presentasi tepat waktu. Menunjukkan sikap percaya diri, sopan, dan menjaga kontak mata.<br>
                                            <strong>Cukup:</strong> Melebihi waktu sedikit, sikap cukup baik.<br>
                                            <strong>Lemah:</strong> Jauh melebihi alokasi waktu, terlihat tidak siap atau kurang sopan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Rata-rata Komponen 2 -->
                            <div class="bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Rata-rata Skor Komponen 2:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="badge badge-success badge-lg" id="rata_rata_presentasi">0.0</span>
                                        <small class="text-muted">(Total Skor / 3)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KOMPONEN 3: Penguasaan Materi dan Diskusi (30%) -->
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h3 class="card-title">
                                <i class="fas fa-comments mr-2"></i>
                                Komponen 3: Penguasaan Materi dan Diskusi (Bobot: 30%)
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                Komponen ini menilai kedalaman pemahaman mahasiswa terhadap proposalnya 
                                dan kemampuannya dalam berdiskusi secara ilmiah.
                            </p>
                            
                            <!-- Indikator 3.1 -->
                            <div class="form-group">
                                <label class="font-weight-bold">3.1 Kemampuan Menjawab Pertanyaan <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_diskusi_3_1" id="nilai_diskusi_3_1"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_diskusi_3_1 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Mampu menjawab semua pertanyaan dengan tepat, logis, dan terstruktur. Menunjukkan pemahaman di luar teks proposal.<br>
                                            <strong>Cukup:</strong> Mampu menjawab sebagian besar pertanyaan, meskipun ada jawaban yang kurang mendalam.<br>
                                            <strong>Lemah:</strong> Tidak mampu menjawab, jawaban tidak relevan atau kebingungan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indikator 3.2 -->
                            <div class="form-group">
                                <label class="font-weight-bold">3.2 Kemampuan Berargumentasi <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_diskusi_3_2" id="nilai_diskusi_3_2"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_diskusi_3_2 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Mampu mempertahankan pilihan topik, teori, dan metode dengan argumentasi yang kokoh dan berbasis bukti/referensi.<br>
                                            <strong>Cukup:</strong> Mampu berargumen, namun terkadang kurang didukung oleh dasar yang kuat.<br>
                                            <strong>Lemah:</strong> Tidak mampu mempertahankan gagasannya, mudah goyah.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Indikator 3.3 -->
                            <div class="form-group">
                                <label class="font-weight-bold">3.3 Sikap Ilmiah dalam Diskusi <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="number" class="form-control nilai-input" 
                                               name="nilai_diskusi_3_3" id="nilai_diskusi_3_3"
                                               min="1" max="100" step="0.1" required
                                               value="<?= $existing_penilaian ? $existing_penilaian->nilai_diskusi_3_3 : '' ?>"
                                               placeholder="1-100">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="text-sm">
                                            <strong>Kuat:</strong> Sangat terbuka dan responsif terhadap masukan/kritik. Menunjukkan sikap menghargai dan tidak defensif.<br>
                                            <strong>Cukup:</strong> Menerima masukan, meskipun terkadang sedikit defensif.<br>
                                            <strong>Lemah:</strong> Menolak masukan, bersikap defensif atau arogan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Rata-rata Komponen 3 -->
                            <div class="bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Rata-rata Skor Komponen 3:</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="badge badge-warning badge-lg" id="rata_rata_diskusi">0.0</span>
                                        <small class="text-muted">(Total Skor / 3)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rekomendasi dan Catatan -->
                    <div class="card">
                        <div class="card-header bg-danger">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-check mr-2"></i>
                                Rekomendasi Penguji dan Catatan
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Rekomendasi Penguji <span class="text-danger">*</span></label>
                                <select name="rekomendasi" id="rekomendasi" class="form-control" required>
                                    <option value="">-- Pilih Rekomendasi --</option>
                                    <option value="diterima_tanpa_revisi" <?= ($existing_penilaian && $existing_penilaian->rekomendasi == 'diterima_tanpa_revisi') ? 'selected' : '' ?>>
                                        ✓ Diterima tanpa revisi
                                    </option>
                                    <option value="diterima_revisi_minor" <?= ($existing_penilaian && $existing_penilaian->rekomendasi == 'diterima_revisi_minor') ? 'selected' : '' ?>>
                                        ⚠ Diterima dengan revisi minor
                                    </option>
                                    <option value="diterima_revisi_mayor" <?= ($existing_penilaian && $existing_penilaian->rekomendasi == 'diterima_revisi_mayor') ? 'selected' : '' ?>>
                                        ⚠ Diterima dengan revisi mayor
                                    </option>
                                    <option value="ditolak_mengulang" <?= ($existing_penilaian && $existing_penilaian->rekomendasi == 'ditolak_mengulang') ? 'selected' : '' ?>>
                                        ✗ Ditolak / Mengulang seminar
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Catatan/Saran Revisi</label>
                                <textarea name="catatan_saran" id="catatan_saran" rows="6" class="form-control" 
                                          placeholder="Berikan catatan, saran perbaikan, atau feedback konstruktif untuk mahasiswa..."><?= $existing_penilaian ? $existing_penilaian->catatan_saran : '' ?></textarea>
                                <small class="text-muted">
                                    Catatan akan membantu mahasiswa memahami area yang perlu diperbaiki.
                                </small>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Summary & Action -->
                <div class="col-md-4">
                    
                    <!-- Rekapitulasi Nilai -->
                    <div class="card sticky-top">
                        <div class="card-header bg-info">
                            <h3 class="card-title">
                                <i class="fas fa-calculator mr-2"></i>
                                Rekapitulasi Nilai Akhir
                            </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Komponen 1 (50%)</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-primary" id="nilai_komponen_1">0.0</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Komponen 2 (20%)</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-success" id="nilai_komponen_2">0.0</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Komponen 3 (30%)</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-warning" id="nilai_komponen_3">0.0</span></td>
                                </tr>
                                <tr class="bg-light">
                                    <td><strong>NILAI AKHIR</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-danger badge-lg" id="nilai_akhir">0.0</span></td>
                                </tr>
                            </table>
                            
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle mr-2"></i>Rumus Perhitungan:</h6>
                                <small>
                                    Nilai Akhir = (Komponen 1 × 50%) + (Komponen 2 × 20%) + (Komponen 3 × 30%)
                                </small>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                    <i class="fas fa-save mr-2"></i>
                                    <?= $is_edit ? 'Update' : 'Simpan' ?> Penilaian
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Info Dewan Penguji -->
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-2"></i>
                                Dewan Penguji
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong class="text-primary">Dosen Pembimbing:</strong><br>
                                <?= $dewan_penguji->nama_pembimbing ?>
                                <br><small class="text-muted">NIP: <?= $dewan_penguji->nip_pembimbing ?></small>
                            </div>
                            
                            <div class="mb-3">
                                <strong class="text-success">Dosen Penguji I:</strong><br>
                                <?= $dewan_penguji->nama_penguji1 ?: '<em class="text-muted">Belum ditetapkan</em>' ?>
                                <?php if($dewan_penguji->nama_penguji1): ?>
                                    <br><small class="text-muted">NIP: <?= $dewan_penguji->nip_penguji1 ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <strong class="text-info">Dosen Penguji II:</strong><br>
                                <?= $dewan_penguji->nama_penguji2 ?: '<em class="text-muted">Belum ditetapkan</em>' ?>
                                <?php if($dewan_penguji->nama_penguji2): ?>
                                    <br><small class="text-muted">NIP: <?= $dewan_penguji->nip_penguji2 ?></small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="alert alert-warning">
                                <small>
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    <strong>Info:</strong> Sebagai staf, Anda dapat memberikan penilaian sebagai backup atau second opinion untuk memastikan objektivitas penilaian.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Petunjuk Penilaian -->
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title">
                                <i class="fas fa-question-circle mr-2"></i>
                                Petunjuk Penilaian
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled text-sm">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-2"></i>
                                    Nilai berkisar antara 1-100
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-2"></i>
                                    Sistem akan menghitung otomatis
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-2"></i>
                                    Pastikan semua field diisi
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-2"></i>
                                    Berikan rekomendasi yang tepat
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-2"></i>
                                    Catatan membantu mahasiswa
                                </li>
                            </ul>
                            
                            <hr>
                            <h6>Skala Penilaian:</h6>
                            <ul class="list-unstyled text-sm">
                                <li><strong>85-100:</strong> Sangat Baik</li>
                                <li><strong>70-84:</strong> Baik</li>
                                <li><strong>55-69:</strong> Cukup</li>
                                <li><strong>40-54:</strong> Kurang</li>
                                <li><strong>1-39:</strong> Sangat Kurang</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>
</section>

<!-- Additional CSS -->
<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.nilai-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.card .card-body table td {
    padding: 0.25rem 0.5rem;
    border: none;
}

.sticky-top {
    top: 20px;
}

@media (max-width: 768px) {
    .sticky-top {
        position: relative;
        top: auto;
    }
}

.text-sm {
    font-size: 0.875rem;
    line-height: 1.4;
}

.form-group label {
    margin-bottom: 0.5rem;
}

.alert-info h6 {
    margin-bottom: 0.5rem;
}
</style>