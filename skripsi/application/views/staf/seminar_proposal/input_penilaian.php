<?php
/**
 * Input Penilaian Seminar Proposal - Staf View
 * File: application/views/staf/seminar_proposal/input_penilaian.php
 * 
 * Form untuk input penilaian seminar proposal oleh staf akademik
 * Compatible dengan struktur penilaian dosen
 */

// ✅ Set default values untuk mencegah undefined variable errors
$is_edit = isset($existing_penilaian) && $existing_penilaian && !empty($existing_penilaian) ? true : false;
$page_title = $is_edit ? 'Edit Penilaian Seminar Proposal' : 'Input Penilaian Seminar Proposal';

// Helper function untuk safe property access
function get_penilaian_value($penilaian, $field, $default = '') {
    return (isset($penilaian->$field) && !empty($penilaian->$field)) ? $penilaian->$field : $default;
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-edit mr-2 text-primary"></i>
                    <?= $page_title ?>
                </h1>
                <p class="text-muted">Penilaian untuk: <?= isset($seminar->nama_mahasiswa) ? $seminar->nama_mahasiswa : 'Unknown' ?> (<?= isset($seminar->nim) ? $seminar->nim : 'Unknown' ?>)</p>
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

        <!-- ✅ FIXED: Validation errors dengan null check -->
        <?php if(function_exists('validation_errors') && validation_errors()): ?>
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

        <div class="row">
            <!-- Left Column: Info Seminar -->
            <div class="col-md-4">
                <!-- Card: Info Seminar -->
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informasi Seminar
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Mahasiswa</strong></td>
                                <td>:</td>
                                <td><?= isset($seminar->nama_mahasiswa) ? $seminar->nama_mahasiswa : 'Unknown' ?></td>
                            </tr>
                            <tr>
                                <td><strong>NIM</strong></td>
                                <td>:</td>
                                <td><?= isset($seminar->nim) ? $seminar->nim : 'Unknown' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Program Studi</strong></td>
                                <td>:</td>
                                <td><?= isset($seminar->nama_prodi) ? $seminar->nama_prodi : 'Unknown' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal</strong></td>
                                <td>:</td>
                                <td>
                                    <?php if(isset($seminar->tanggal_seminar) && !empty($seminar->tanggal_seminar)): ?>
                                        <?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?>
                                    <?php else: ?>
                                        Belum ditentukan
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tempat</strong></td>
                                <td>:</td>
                                <td><?= isset($seminar->tempat_seminar) ? $seminar->tempat_seminar : 'Belum ditentukan' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Card: Guidelines -->
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">
                            <i class="fas fa-lightbulb mr-2"></i>
                            Panduan Penilaian
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Komponen Penilaian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Substansi & Metode (50%)</li>
                                <li>Presentasi & Teknik (20%)</li>
                                <li>Penguasaan & Diskusi (30%)</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning">
                            <strong>Rentang Nilai:</strong>
                            <ul class="mb-0 mt-2">
                                <li>A: 80-100 (Sangat Baik)</li>
                                <li>B: 70-79 (Baik)</li>
                                <li>C: 60-69 (Cukup)</li>
                                <li>D: 50-59 (Kurang)</li>
                                <li>E: 0-49 (Sangat Kurang)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form Penilaian -->
            <div class="col-md-8">
                <!-- Form Penilaian -->
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            Form Penilaian Seminar Proposal
                        </h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- Form Start -->
                        <?= form_open('staf/seminar_proposal/input_penilaian/' . $seminar->id, ['class' => 'form-horizontal']) ?>
                        
                        <!-- Judul Proposal -->
                        <div class="form-group">
                            <label class="font-weight-bold">Judul Proposal:</label>
                            <div class="bg-light p-3 rounded">
                                <em><?= isset($seminar->judul) ? $seminar->judul : 'Judul tidak tersedia' ?></em>
                            </div>
                        </div>

                        <!-- Komponen Penilaian -->
                        <div class="row">
                            <!-- Nilai Substansi & Metode -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_substansi" class="font-weight-bold">
                                        Substansi & Metode <span class="text-danger">*</span>
                                        <small class="text-muted d-block">(Bobot: 50%)</small>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="nilai_substansi" 
                                           name="nilai_substansi" 
                                           min="0" 
                                           max="100" 
                                           step="0.1"
                                           value="<?= get_penilaian_value($existing_penilaian, 'nilai_substansi_metode') ?>"
                                           placeholder="0.0" 
                                           required>
                                    <small class="form-text text-muted">Nilai 0-100</small>
                                </div>
                            </div>

                            <!-- Nilai Presentasi & Teknik -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_presentasi" class="font-weight-bold">
                                        Presentasi & Teknik <span class="text-danger">*</span>
                                        <small class="text-muted d-block">(Bobot: 20%)</small>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="nilai_presentasi" 
                                           name="nilai_presentasi" 
                                           min="0" 
                                           max="100" 
                                           step="0.1"
                                           value="<?= get_penilaian_value($existing_penilaian, 'nilai_presentasi_teknik') ?>"
                                           placeholder="0.0" 
                                           required>
                                    <small class="form-text text-muted">Nilai 0-100</small>
                                </div>
                            </div>

                            <!-- Nilai Penguasaan & Diskusi -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_diskusi" class="font-weight-bold">
                                        Penguasaan & Diskusi <span class="text-danger">*</span>
                                        <small class="text-muted d-block">(Bobot: 30%)</small>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="nilai_diskusi" 
                                           name="nilai_diskusi" 
                                           min="0" 
                                           max="100" 
                                           step="0.1"
                                           value="<?= get_penilaian_value($existing_penilaian, 'nilai_penguasaan_diskusi') ?>"
                                           placeholder="0.0" 
                                           required>
                                    <small class="form-text text-muted">Nilai 0-100</small>
                                </div>
                            </div>
                        </div>

                        <!-- Hasil Kalkulasi Otomatis -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-secondary">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <strong>Nilai Akhir:</strong>
                                            <h4 id="nilai_akhir_display" class="text-primary">0.0</h4>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Grade:</strong>
                                            <h4 id="grade_display" class="text-success">-</h4>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Keterangan:</strong>
                                            <p id="keterangan_display" class="mb-0">Masukkan nilai untuk melihat hasil</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- Hidden field untuk nilai akhir -->
                                <input type="hidden" id="nilai_akhir_hidden" name="nilai_akhir" value="<?= get_penilaian_value($existing_penilaian, 'nilai_akhir') ?>">
                                <input type="hidden" id="nilai_huruf_hidden" name="nilai_huruf" value="<?= get_penilaian_value($existing_penilaian, 'nilai_huruf') ?>">
                            </div>
                        </div>

                        <!-- Rekomendasi -->
                        <div class="form-group">
                            <label for="rekomendasi" class="font-weight-bold">
                                Rekomendasi <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="rekomendasi" name="rekomendasi" required>
                                <option value="">-- Pilih Rekomendasi --</option>
                                <option value="diterima_tanpa_revisi" 
                                    <?= get_penilaian_value($existing_penilaian, 'rekomendasi') == 'diterima_tanpa_revisi' ? 'selected' : '' ?>>
                                    Diterima Tanpa Revisi
                                </option>
                                <option value="revisi_minor" 
                                    <?= get_penilaian_value($existing_penilaian, 'rekomendasi') == 'revisi_minor' ? 'selected' : '' ?>>
                                    Diterima dengan Revisi Minor
                                </option>
                                <option value="revisi_mayor" 
                                    <?= get_penilaian_value($existing_penilaian, 'rekomendasi') == 'revisi_mayor' ? 'selected' : '' ?>>
                                    Diterima dengan Revisi Mayor
                                </option>
                                <option value="ditolak" 
                                    <?= get_penilaian_value($existing_penilaian, 'rekomendasi') == 'ditolak' ? 'selected' : '' ?>>
                                    Ditolak / Mengulang Seminar
                                </option>
                            </select>
                        </div>

                        <!-- Catatan Detail -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="catatan_latar_belakang" class="font-weight-bold">
                                        Catatan Latar Belakang & Rumusan Masalah
                                    </label>
                                    <textarea class="form-control" 
                                              id="catatan_latar_belakang" 
                                              name="catatan_latar_belakang" 
                                              rows="3" 
                                              placeholder="Masukkan catatan untuk aspek latar belakang dan rumusan masalah..."><?= get_penilaian_value($existing_penilaian, 'catatan_latar_belakang') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="catatan_tinjauan_pustaka" class="font-weight-bold">
                                        Catatan Tinjauan Pustaka & Kebaruan
                                    </label>
                                    <textarea class="form-control" 
                                              id="catatan_tinjauan_pustaka" 
                                              name="catatan_tinjauan_pustaka" 
                                              rows="3" 
                                              placeholder="Masukkan catatan untuk aspek tinjauan pustaka dan novelty..."><?= get_penilaian_value($existing_penilaian, 'catatan_tinjauan_pustaka') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="catatan_landasan_teori" class="font-weight-bold">
                                        Catatan Landasan Teori
                                    </label>
                                    <textarea class="form-control" 
                                              id="catatan_landasan_teori" 
                                              name="catatan_landasan_teori" 
                                              rows="3" 
                                              placeholder="Masukkan catatan untuk aspek landasan teori..."><?= get_penilaian_value($existing_penilaian, 'catatan_landasan_teori') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="catatan_metodologi" class="font-weight-bold">
                                        Catatan Metodologi Penelitian
                                    </label>
                                    <textarea class="form-control" 
                                              id="catatan_metodologi" 
                                              name="catatan_metodologi" 
                                              rows="3" 
                                              placeholder="Masukkan catatan untuk aspek metodologi penelitian..."><?= get_penilaian_value($existing_penilaian, 'catatan_metodologi') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="catatan_sistematika" class="font-weight-bold">
                                        Catatan Sistematika & Tata Tulis
                                    </label>
                                    <textarea class="form-control" 
                                              id="catatan_sistematika" 
                                              name="catatan_sistematika" 
                                              rows="3" 
                                              placeholder="Masukkan catatan untuk aspek sistematika dan tata tulis..."><?= get_penilaian_value($existing_penilaian, 'catatan_sistematika') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="catatan_umum" class="font-weight-bold">
                                        Catatan Umum & Saran
                                    </label>
                                    <textarea class="form-control" 
                                              id="catatan_umum" 
                                              name="catatan_umum" 
                                              rows="3" 
                                              placeholder="Masukkan catatan umum atau saran tambahan..."><?= get_penilaian_value($existing_penilaian, 'catatan_umum') ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-group">
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="<?= base_url('staf/seminar_proposal/detail/' . $seminar->id) ?>" 
                                       class="btn btn-secondary btn-block">
                                        <i class="fas fa-times mr-2"></i>
                                        Batal
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save mr-2"></i>
                                        <?= $is_edit ? 'Update Penilaian' : 'Simpan Penilaian' ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?= form_close() ?>

                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- JavaScript untuk Kalkulasi Otomatis -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nilaiSubstansi = document.getElementById('nilai_substansi');
    const nilaiPresentasi = document.getElementById('nilai_presentasi');
    const nilaiDiskusi = document.getElementById('nilai_diskusi');
    const nilaiAkhirDisplay = document.getElementById('nilai_akhir_display');
    const gradeDisplay = document.getElementById('grade_display');
    const keteranganDisplay = document.getElementById('keterangan_display');
    const nilaiAkhirHidden = document.getElementById('nilai_akhir_hidden');
    const nilaiHurufHidden = document.getElementById('nilai_huruf_hidden');

    function hitungNilaiAkhir() {
        const substansi = parseFloat(nilaiSubstansi.value) || 0;
        const presentasi = parseFloat(nilaiPresentasi.value) || 0;
        const diskusi = parseFloat(nilaiDiskusi.value) || 0;

        // Hitung dengan bobot: Substansi (50%), Presentasi (20%), Diskusi (30%)
        const nilaiAkhir = (substansi * 0.5) + (presentasi * 0.2) + (diskusi * 0.3);
        
        // Tentukan grade
        let grade, keterangan;
        if (nilaiAkhir >= 80) {
            grade = 'A';
            keterangan = 'Sangat Baik';
        } else if (nilaiAkhir >= 70) {
            grade = 'B';
            keterangan = 'Baik';
        } else if (nilaiAkhir >= 60) {
            grade = 'C';
            keterangan = 'Cukup';
        } else if (nilaiAkhir >= 50) {
            grade = 'D';
            keterangan = 'Kurang';
        } else {
            grade = 'E';
            keterangan = 'Sangat Kurang';
        }

        // Update display
        nilaiAkhirDisplay.textContent = nilaiAkhir.toFixed(1);
        gradeDisplay.textContent = grade;
        keteranganDisplay.textContent = keterangan;
        
        // Update hidden fields
        nilaiAkhirHidden.value = nilaiAkhir.toFixed(2);
        nilaiHurufHidden.value = grade;
    }

    // Event listeners
    nilaiSubstansi.addEventListener('input', hitungNilaiAkhir);
    nilaiPresentasi.addEventListener('input', hitungNilaiAkhir);
    nilaiDiskusi.addEventListener('input', hitungNilaiAkhir);

    // Hitung initial jika ada nilai existing
    hitungNilaiAkhir();
});
</script>

<style>
.form-group label.font-weight-bold {
    color: #495057;
}

.alert-secondary {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

#nilai_akhir_display {
    font-size: 1.5rem;
    margin-bottom: 0;
}

#grade_display {
    font-size: 1.5rem;
    margin-bottom: 0;
}

.text-center h4 {
    margin-bottom: 5px;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>