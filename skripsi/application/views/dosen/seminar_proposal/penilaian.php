<?php
/**
 * File: application/views/dosen/seminar_proposal/penilaian.php
 * Form Penilaian Seminar Proposal - FIXED VERSION (Bebas Error)
 */

// Helper untuk mengambil value form dengan fallback
if (!function_exists('get_form_value')) {
    function get_form_value($field_name, $data_source = null) {
        $ci = &get_instance();
        
        // Jika ada data dari database
        if ($data_source && isset($data_source->$field_name)) {
            return $data_source->$field_name;
        }
        
        // Jika ada post data
        if ($ci->input->post($field_name)) {
            return $ci->input->post($field_name);
        }
        
        return '';
    }
}
?>

<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-list mr-2"></i>
            Penilaian Seminar Proposal
        </h1>
        <a href="<?= base_url('dosen/seminar_proposal') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Alert Information -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Informasi:</strong> Anda dapat melakukan penilaian untuk seminar proposal ini. 
        Pastikan semua komponen penilaian diisi dengan lengkap dan akurat.
    </div>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <!-- Informasi Seminar -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-info-circle mr-2"></i>
                Informasi Seminar Proposal
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr>
                            <td width="20%"><strong>Mahasiswa</strong></td>
                            <td width="3%">:</td>
                            <td><?= isset($seminar->nama_mahasiswa) ? $seminar->nama_mahasiswa : '-' ?> 
                                (<?= isset($seminar->nim) ? $seminar->nim : '-' ?>)</td>
                        </tr>
                        <tr>
                            <td><strong>Program Studi</strong></td>
                            <td>:</td>
                            <td><?= isset($seminar->nama_prodi) ? $seminar->nama_prodi : '-' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status Seminar</strong></td>
                            <td>:</td>
                            <td>
                                <?php if (isset($seminar->status)): ?>
                                    <span class="badge badge-info"><?= ucfirst($seminar->status) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status Penilaian</strong></td>
                            <td>:</td>
                            <td>
                                <?php if (isset($penilaian) && $penilaian): ?>
                                    <?php if ($penilaian->status_penilaian == 'published'): ?>
                                        <span class="badge badge-success">Sudah Dipublikasi</span>
                                        <?php if (isset($penilaian->published_at)): ?>
                                        <small class="text-muted">
                                            (<?= date('d M Y H:i', strtotime($penilaian->published_at)) ?>)
                                        </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Draft</span>
                                        <?php if (isset($penilaian->updated_at)): ?>
                                        <small class="text-muted">
                                            (<?= date('d M Y H:i', strtotime($penilaian->updated_at)) ?>)
                                        </small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Belum Dinilai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="mt-3">
                <strong>Judul Proposal:</strong>
                <p class="text-justify mb-0"><?= isset($seminar->judul) ? $seminar->judul : '-' ?></p>
            </div>
        </div>
    </div>

    <!-- Form Penilaian -->
    <?php 
    $is_published = (isset($penilaian) && $penilaian && $penilaian->status_penilaian == 'published');
    $readonly = $is_published ? 'readonly' : '';
    $disabled = $is_published ? 'disabled' : '';
    ?>
    
    <?php if (!$is_published): ?>
    <form method="post" action="<?= base_url('dosen/seminar_proposal/penilaian/' . $seminar->id) ?>" id="penilaianForm">
    <?php endif; ?>
        
        <!-- Komponen 1: Catatan Revisi Seminar Proposal -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-edit mr-2"></i>
                    1. Catatan Revisi Seminar Proposal
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Masukkan catatan perbaikan dari para dosen penguji untuk berbagai aspek proposal
                </p>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Latar Belakang & Rumusan Masalah:</label>
                            <textarea name="catatan_latar_belakang" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk latar belakang dan rumusan masalah..." 
                                      <?= $readonly ?>><?= get_form_value('catatan_latar_belakang', $penilaian ?? null) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Tinjauan Pustaka & Kebaruan (Novelty):</label>
                            <textarea name="catatan_tinjauan_pustaka" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk tinjauan pustaka dan novelty..." 
                                      <?= $readonly ?>><?= get_form_value('catatan_tinjauan_pustaka', $penilaian ?? null) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Landasan Teori:</label>
                            <textarea name="catatan_landasan_teori" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk landasan teori..." 
                                      <?= $readonly ?>><?= get_form_value('catatan_landasan_teori', $penilaian ?? null) ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Metodologi Penelitian:</label>
                            <textarea name="catatan_metodologi" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk metodologi penelitian..." 
                                      <?= $readonly ?>><?= get_form_value('catatan_metodologi', $penilaian ?? null) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Sistematika & Tata Tulis:</label>
                            <textarea name="catatan_sistematika" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk sistematika dan tata tulis..." 
                                      <?= $readonly ?>><?= get_form_value('catatan_sistematika', $penilaian ?? null) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Catatan Umum:</label>
                            <textarea name="catatan_umum" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan umum atau saran tambahan..." 
                                      <?= $readonly ?>><?= get_form_value('catatan_umum', $penilaian ?? null) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Komponen 2: Nilai Final Seminar Proposal - UPDATED UNTUK SISTEM 3 DOSEN -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-calculator mr-2"></i>
                2. Nilai Final Seminar Proposal
            </h6>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Sistem Penilaian Baru:</strong> Nilai dari 3 dosen (tanpa pembobotan). Preview Nilai Akhir adalah rata-rata ketiganya: (Nilai Penguji 1 + Nilai Penguji 2 + Nilai Pembimbing) ÷ 3
            </p>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-user-graduate mr-1"></i>
                            Nilai Dosen Penguji 1
                            <?php if (isset($dosen_penguji1) && $dosen_penguji1): ?>
                                <br><small class="text-info"><?= $dosen_penguji1->nama ?></small>
                            <?php endif; ?>
                        </label>
                        <div class="input-group">
                            <input type="number" name="nilai_penguji1" class="form-control nilai-input" 
                                   id="nilai_penguji1"
                                   min="0" max="100" step="0.01"
                                   value="<?= get_form_value('nilai_penguji1', $penilaian ?? null) ?>"
                                   placeholder="0-100" <?= $readonly ?>>
                            <div class="input-group-append">
                                <span class="input-group-text">/ 100</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Nilai mentah dari Dosen Penguji 1 (tanpa pembobotan)
                        </small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-user-graduate mr-1"></i>
                            Nilai Dosen Penguji 2
                            <?php if (isset($dosen_penguji2) && $dosen_penguji2): ?>
                                <br><small class="text-info"><?= $dosen_penguji2->nama ?></small>
                            <?php endif; ?>
                        </label>
                        <div class="input-group">
                            <input type="number" name="nilai_penguji2" class="form-control nilai-input" 
                                   id="nilai_penguji2"
                                   min="0" max="100" step="0.01"
                                   value="<?= get_form_value('nilai_penguji2', $penilaian ?? null) ?>"
                                   placeholder="0-100" <?= $readonly ?>>
                            <div class="input-group-append">
                                <span class="input-group-text">/ 100</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Nilai mentah dari Dosen Penguji 2 (tanpa pembobotan)
                        </small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-chalkboard-teacher mr-1"></i>
                            Nilai Dosen Pembimbing
                            <?php if (isset($seminar->nama_pembimbing)): ?>
                                <br><small class="text-success"><?= $seminar->nama_pembimbing ?></small>
                            <?php endif; ?>
                        </label>
                        <div class="input-group">
                            <input type="number" name="nilai_pembimbing" class="form-control nilai-input" 
                                   id="nilai_pembimbing"
                                   min="0" max="100" step="0.01"
                                   value="<?= get_form_value('nilai_pembimbing', $penilaian ?? null) ?>"
                                   placeholder="0-100" <?= $readonly ?>>
                            <div class="input-group-append">
                                <span class="input-group-text">/ 100</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Nilai mentah dari Dosen Pembimbing (tanpa pembobotan)
                        </small>
                    </div>
                </div>
            </div>
                
        <!-- Preview Nilai Akhir - UPDATED -->
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-light border">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-chart-line mr-2"></i>
                        Preview Nilai Akhir:
                    </h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Nilai Angka:</span>
                        <span class="font-weight-bold" id="previewNilaiAngka">
                            <?= (isset($penilaian) && isset($penilaian->nilai_akhir) && $penilaian->nilai_akhir) ? number_format($penilaian->nilai_akhir, 2) : '-' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Nilai Huruf:</span>
                        <span class="font-weight-bold" id="previewNilaiHuruf">
                            <?= (isset($penilaian) && isset($penilaian->nilai_huruf) && $penilaian->nilai_huruf) ? $penilaian->nilai_huruf : '-' ?>
                        </span>
                    </div>
                    <hr class="my-2">
                    <small class="text-muted">
                        <i class="fas fa-calculator mr-1"></i>
                        <strong>Rumus:</strong> (Penguji 1 + Penguji 2 + Pembimbing) ÷ 3
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Konversi Nilai:
                    </h6>
                    <div class="small">
                        <div><span class="badge badge-success">A</span> ≥80: Sangat Baik</div>
                        <div><span class="badge badge-primary">B</span> 70-79.9: Baik</div>
                        <div><span class="badge badge-warning">C</span> 60-69.9: Cukup</div>
                        <div><span class="badge badge-secondary">D</span> 50-59.9: Kurang</div>
                        <div><span class="badge badge-danger">E</span> <50: Sangat Kurang</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info Tambahan untuk Sistem Baru -->
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle mr-2 mt-1"></i>
                        <div>
                            <strong>Perhatian - Sistem Penilaian Baru:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Sistem lama menggunakan <strong>pembobotan</strong>: Substansi (50%) + Presentasi (20%) + Penguasaan (30%)</li>
                                <li>Sistem baru menggunakan <strong>rata-rata sederhana</strong>: (Nilai Penguji 1 + Nilai Penguji 2 + Nilai Pembimbing) ÷ 3</li>
                                <li>Setiap dosen memberikan nilai <strong>0-100</strong> tanpa pembobotan</li>
                                <li>Nilai akhir adalah <strong>rata-rata dari 3 dosen</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>        

        <!-- Komponen 3: Rekomendasi Hasil Seminar -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-thumbs-up mr-2"></i>
                    3. Rekomendasi Hasil Seminar
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Pilih rekomendasi berdasarkan hasil seminar proposal
                </p>
                
                <div class="form-group">
                    <label class="font-weight-bold">Pilih Rekomendasi</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" name="rekomendasi" value="diterima_tanpa_revisi" id="rekomen1" class="custom-control-input" 
                                       <?= (isset($penilaian) && $penilaian && $penilaian->rekomendasi == 'diterima_tanpa_revisi') ? 'checked' : '' ?>
                                       <?= $disabled ?>>
                                <label class="custom-control-label" for="rekomen1">
                                    <i class="fas fa-check-circle text-success mr-1"></i>
                                    <strong>Diterima Tanpa Revisi</strong><br>
                                    <small class="text-muted">Proposal dapat dilanjutkan ke tahap penelitian</small>
                                </label>
                            </div>
                            
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" name="rekomendasi" value="revisi_minor" id="rekomen2" class="custom-control-input"
                                       <?= (isset($penilaian) && $penilaian && $penilaian->rekomendasi == 'revisi_minor') ? 'checked' : '' ?>
                                       <?= $disabled ?>>
                                <label class="custom-control-label" for="rekomen2">
                                    <i class="fas fa-edit text-info mr-1"></i>
                                    <strong>Revisi Minor</strong><br>
                                    <small class="text-muted">Perlu perbaikan kecil, dapat dilanjutkan ke penelitian</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" name="rekomendasi" value="revisi_mayor" id="rekomen3" class="custom-control-input"
                                       <?= (isset($penilaian) && $penilaian && $penilaian->rekomendasi == 'revisi_mayor') ? 'checked' : '' ?>
                                       <?= $disabled ?>>
                                <label class="custom-control-label" for="rekomen3">
                                    <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                    <strong>Revisi Mayor</strong><br>
                                    <small class="text-muted">Perlu perbaikan besar, dapat dilanjutkan setelah revisi</small>
                                </label>
                            </div>
                            
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" name="rekomendasi" value="ditolak" id="rekomen4" class="custom-control-input"
                                       <?= (isset($penilaian) && $penilaian && $penilaian->rekomendasi == 'ditolak') ? 'checked' : '' ?>
                                       <?= $disabled ?>>
                                <label class="custom-control-label" for="rekomen4">
                                    <i class="fas fa-times-circle text-danger mr-1"></i>
                                    <strong>Ditolak</strong><br>
                                    <small class="text-muted">Harus mengajukan ulang seminar proposal</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="font-weight-bold">Keterangan Rekomendasi</label>
                    <textarea name="keterangan_rekomendasi" class="form-control" rows="3" 
                              placeholder="Masukkan keterangan tambahan untuk rekomendasi (opsional)..." 
                              <?= $readonly ?>><?= get_form_value('keterangan_rekomendasi', $penilaian ?? null) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php if (!$is_published): ?>
                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="fas fa-save mr-2"></i>
                            Opsi Penyimpanan
                        </h6>
                        <p class="text-muted mb-3">
                            <strong>Draft:</strong> Penilaian disimpan tapi belum dikirim ke mahasiswa (masih bisa diedit)<br>
                            <strong>Simpan dan Publikasi:</strong> Penilaian final dikirim ke mahasiswa via email & sistem (tidak bisa diedit lagi)
                        </p>
                        <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i>
                            <strong>Penilaian Sudah Dipublikasi</strong><br>
                            <?php if (isset($penilaian->published_at)): ?>
                            Penilaian ini telah dipublikasi pada <?= date('d F Y, H:i', strtotime($penilaian->published_at)) ?> WIB
                            dan tidak dapat diubah lagi.
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php if (!$is_published): ?>
                        <button type="submit" name="action_type" value="draft" class="btn btn-outline-primary btn-lg mb-2">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Draft
                        </button>
                        <br>
                        <button type="submit" name="action_type" value="publish" class="btn btn-success btn-lg" 
                                onclick="return confirm('Apakah Anda yakin ingin mempublikasi penilaian ini? Penilaian yang sudah dipublikasi tidak dapat diubah lagi.')">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Simpan & Publikasi
                        </button>
                        <?php else: ?>
                        <a href="<?= base_url('dosen/seminar_proposal') ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Kembali ke Dashboard
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php if (!$is_published): ?>
    </form>
    <?php endif; ?>

</div>

<script>
// Auto calculate nilai akhir dengan rata-rata sederhana
function calculateNilaiAkhir() {
    const penguji1 = parseFloat(document.querySelector('input[name="nilai_penguji1"]').value) || 0;
    const penguji2 = parseFloat(document.querySelector('input[name="nilai_penguji2"]').value) || 0;
    const pembimbing = parseFloat(document.querySelector('input[name="nilai_pembimbing"]').value) || 0;
    
    if (penguji1 > 0 && penguji2 > 0 && pembimbing > 0) {
        // Rata-rata sederhana (tanpa bobot)
        const nilaiAkhir = (penguji1 + penguji2 + pembimbing) / 3;
        
        let nilaiHuruf = '';
        if (nilaiAkhir >= 80) nilaiHuruf = 'A';
        else if (nilaiAkhir >= 70) nilaiHuruf = 'B';
        else if (nilaiAkhir >= 60) nilaiHuruf = 'C';
        else if (nilaiAkhir >= 50) nilaiHuruf = 'D';
        else nilaiHuruf = 'E';
        
        document.getElementById('previewNilaiAngka').textContent = nilaiAkhir.toFixed(2);
        document.getElementById('previewNilaiHuruf').textContent = nilaiHuruf;
    } else {
        document.getElementById('previewNilaiAngka').textContent = '-';
        document.getElementById('previewNilaiHuruf').textContent = '-';
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners untuk semua input nilai
    const nilaiInputs = document.querySelectorAll('.nilai-input');
    nilaiInputs.forEach(function(input) {
        input.addEventListener('input', calculateNilaiAkhir);
    });
    
    // Calculate on page load
    calculateNilaiAkhir();
    
    // Form validation
    const form = document.getElementById('penilaianForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const actionButton = document.activeElement;
            const actionType = actionButton.value;
            
            if (actionType === 'publish') {
                const penguji1 = parseFloat(document.querySelector('input[name="nilai_penguji1"]').value) || 0;
                const penguji2 = parseFloat(document.querySelector('input[name="nilai_penguji2"]').value) || 0;
                const pembimbing = parseFloat(document.querySelector('input[name="nilai_pembimbing"]').value) || 0;
                const rekomendasi = document.querySelector('input[name="rekomendasi"]:checked');
                
                if (penguji1 <= 0 || penguji2 <= 0 || pembimbing <= 0) {
                    e.preventDefault();
                    alert('Semua komponen nilai harus diisi dengan nilai > 0 untuk publikasi!');
                    return false;
                }
                
                if (!rekomendasi) {
                    e.preventDefault();
                    alert('Rekomendasi hasil seminar harus dipilih untuk publikasi!');
                    return false;
                }
            }
            
            return true;
        });
    }
});
</script>

<?php
// Helper function untuk mendapatkan nilai form
if (!function_exists('get_form_value')) {
    function get_form_value($field_name, $object = null) {
        if ($object && isset($object->$field_name)) {
            return $object->$field_name;
        }
        return '';
    }
}
?>