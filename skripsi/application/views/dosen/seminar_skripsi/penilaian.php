<?php
// =========================================================================
// FILE 3: application/views/dosen/seminar_skripsi/penilaian.php  
// =========================================================================
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit mr-2"></i>
            <?= $is_edit ? 'Edit' : 'Input' ?> Penilaian Seminar Skripsi
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dosen/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                <li class="breadcrumb-item active">Penilaian</li>
            </ol>
        </nav>
    </div>

    <form id="penilaian-form" method="post" action="<?= base_url('dosen/seminar_skripsi/penilaian/' . $seminar->id) ?>">
        <div class="row">
            <div class="col-lg-8">
                <!-- Info Seminar -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informasi Seminar
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Mahasiswa:</label>
                                    <div class="font-weight-bold"><?= $seminar->nama_mahasiswa ?> (<?= $seminar->nim ?>)</div>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted">Judul Skripsi:</label>
                                    <div><?= $seminar->judul_current ?? $seminar->judul ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php if(!empty($seminar->tanggal_seminar)): ?>
                                <div class="info-group mb-3">
                                    <label class="text-muted">Tanggal Seminar:</label>
                                    <div>
                                        <?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?>
                                        <?= !empty($seminar->jam_seminar) ? ', ' . date('H:i', strtotime($seminar->jam_seminar)) . ' WIB' : '' ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if(!empty($seminar->tempat_seminar)): ?>
                                <div class="info-group mb-3">
                                    <label class="text-muted">Tempat:</label>
                                    <div><?= $seminar->tempat_seminar ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Penilaian -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-star mr-2"></i>
                            Form Penilaian
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Catatan Revisi per Bab -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="catatan_pendahuluan">Catatan Bab I (Pendahuluan):</label>
                                    <textarea class="form-control" id="catatan_pendahuluan" name="catatan_pendahuluan" rows="3" 
                                              placeholder="Catatan untuk latar belakang, rumusan masalah, tujuan..."><?= $penilaian->catatan_pendahuluan ?? '' ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="catatan_tinjauan_pustaka">Catatan Bab II (Tinjauan Pustaka):</label>
                                    <textarea class="form-control" id="catatan_tinjauan_pustaka" name="catatan_tinjauan_pustaka" rows="3" 
                                              placeholder="Catatan untuk tinjauan pustaka dan landasan teori..."><?= $penilaian->catatan_tinjauan_pustaka ?? '' ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="catatan_metodologi">Catatan Bab III (Metodologi):</label>
                                    <textarea class="form-control" id="catatan_metodologi" name="catatan_metodologi" rows="3" 
                                              placeholder="Catatan untuk metodologi penelitian..."><?= $penilaian->catatan_metodologi ?? '' ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="catatan_hasil_pembahasan">Catatan Bab IV (Hasil & Pembahasan):</label>
                                    <textarea class="form-control" id="catatan_hasil_pembahasan" name="catatan_hasil_pembahasan" rows="3" 
                                              placeholder="Catatan untuk hasil penelitian dan pembahasan..."><?= $penilaian->catatan_hasil_pembahasan ?? '' ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="catatan_kesimpulan">Catatan Bab V (Kesimpulan):</label>
                                    <textarea class="form-control" id="catatan_kesimpulan" name="catatan_kesimpulan" rows="3" 
                                              placeholder="Catatan untuk kesimpulan dan saran..."><?= $penilaian->catatan_kesimpulan ?? '' ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="catatan_umum">Catatan Umum:</label>
                                    <textarea class="form-control" id="catatan_umum" name="catatan_umum" rows="3" 
                                              placeholder="Catatan sistematika, format, atau lainnya..."><?= $penilaian->catatan_umum ?? '' ?></textarea>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Nilai -->
                        <h6 class="font-weight-bold mb-3">
                            <i class="fas fa-calculator mr-2"></i>
                            Input Nilai (0-100)
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_penguji1">
                                        Nilai Penguji 1:
                                        <?php if($dosen_penguji1): ?>
                                            <small class="text-muted">(<?= $dosen_penguji1->nama ?>)</small>
                                        <?php endif; ?>
                                    </label>
                                    <input type="number" class="form-control nilai-input" id="nilai_penguji1" name="nilai_penguji1" 
                                           min="0" max="100" step="0.01" value="<?= $penilaian->nilai_penguji1 ?? '' ?>" 
                                           placeholder="0-100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_penguji2">
                                        Nilai Penguji 2:
                                        <?php if($dosen_penguji2): ?>
                                            <small class="text-muted">(<?= $dosen_penguji2->nama ?>)</small>
                                        <?php endif; ?>
                                    </label>
                                    <input type="number" class="form-control nilai-input" id="nilai_penguji2" name="nilai_penguji2" 
                                           min="0" max="100" step="0.01" value="<?= $penilaian->nilai_penguji2 ?? '' ?>" 
                                           placeholder="0-100">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nilai_pembimbing">
                                        Nilai Pembimbing: <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control nilai-input" id="nilai_pembimbing" name="nilai_pembimbing" 
                                           min="0" max="100" step="0.01" value="<?= $penilaian->nilai_pembimbing ?? '' ?>" 
                                           placeholder="0-100" required>
                                </div>
                            </div>
                        </div>

                        <!-- Nilai Akhir Display -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nilai Akhir (Rata-rata):</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="nilai_akhir_display" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="nilai_huruf_display">-</span>
                                        </div>
                                    </div>
                                    <input type="hidden" id="nilai_akhir" name="nilai_akhir">
                                    <input type="hidden" id="nilai_huruf" name="nilai_huruf">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Rekomendasi -->
                        <h6 class="font-weight-bold mb-3">
                            <i class="fas fa-thumbs-up mr-2"></i>
                            Rekomendasi <span class="text-danger">*</span>
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rekomendasi" value="lulus_tanpa_revisi" 
                                               id="lulus_tanpa_revisi" <?= (isset($penilaian->rekomendasi) && $penilaian->rekomendasi == 'lulus_tanpa_revisi') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-success" for="lulus_tanpa_revisi">
                                            <i class="fas fa-check-circle mr-1"></i> Lulus tanpa revisi
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rekomendasi" value="lulus_dengan_revisi_minor" 
                                               id="lulus_dengan_revisi_minor" <?= (isset($penilaian->rekomendasi) && $penilaian->rekomendasi == 'lulus_dengan_revisi_minor') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-info" for="lulus_dengan_revisi_minor">
                                            <i class="fas fa-edit mr-1"></i> Lulus dengan revisi minor
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rekomendasi" value="lulus_dengan_revisi_mayor" 
                                               id="lulus_dengan_revisi_mayor" <?= (isset($penilaian->rekomendasi) && $penilaian->rekomendasi == 'lulus_dengan_revisi_mayor') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-warning" for="lulus_dengan_revisi_mayor">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Lulus dengan revisi mayor
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rekomendasi" value="tidak_lulus" 
                                               id="tidak_lulus" <?= (isset($penilaian->rekomendasi) && $penilaian->rekomendasi == 'tidak_lulus') ? 'checked' : '' ?>>
                                        <label class="form-check-label text-danger" for="tidak_lulus">
                                            <i class="fas fa-times-circle mr-1"></i> Tidak lulus
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="keterangan_rekomendasi">Keterangan Rekomendasi:</label>
                                    <textarea class="form-control" id="keterangan_rekomendasi" name="keterangan_rekomendasi" rows="4" 
                                              placeholder="Keterangan tambahan untuk rekomendasi..."><?= $penilaian->keterangan_rekomendasi ?? '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Action Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Penilaian
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Draft:</strong> Penilaian disimpan tapi belum terlihat mahasiswa.<br>
                            <strong>Publikasi:</strong> Penilaian dipublikasi ke mahasiswa dan status seminar menjadi selesai.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="draft" class="btn btn-warning btn-block mb-2">
                                <i class="fas fa-save mr-2"></i>
                                Simpan sebagai Draft
                            </button>
                            <button type="submit" name="action" value="publish" class="btn btn-success btn-block">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Publikasikan Penilaian
                            </button>
                        </div>

                        <hr>

                        <small class="text-muted">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Setelah publikasi, mahasiswa akan mendapat notifikasi email dan dapat lanjut ke tahap Publikasi Tugas Akhir.
                        </small>
                    </div>
                </div>

                <!-- Progress Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-chart-line mr-2"></i>
                            Progress Penilaian
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <?php if($is_edit): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-edit fa-2x mb-2"></i>
                                <h6>Mode Edit</h6>
                                <small>Anda sedang mengedit penilaian yang sudah ada.</small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-primary">
                                <i class="fas fa-plus fa-2x mb-2"></i>
                                <h6>Penilaian Baru</h6>
                                <small>Anda sedang membuat penilaian baru untuk seminar ini.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Calculate nilai akhir otomatis
    $('.nilai-input').on('input', function() {
        calculateNilaiAkhir();
    });
    
    function calculateNilaiAkhir() {
        const penguji1 = parseFloat($('#nilai_penguji1').val()) || 0;
        const penguji2 = parseFloat($('#nilai_penguji2').val()) || 0;
        const pembimbing = parseFloat($('#nilai_pembimbing').val()) || 0;
        
        let total = 0;
        let count = 0;
        
        if (penguji1 > 0) { total += penguji1; count++; }
        if (penguji2 > 0) { total += penguji2; count++; }
        if (pembimbing > 0) { total += pembimbing; count++; }
        
        const average = count > 0 ? total / count : 0;
        
        $('#nilai_akhir_display').val(average.toFixed(2));
        $('#nilai_akhir').val(average.toFixed(2));
        
        const huruf = convertToLetterGrade(average);
        $('#nilai_huruf_display').text(huruf);
        $('#nilai_huruf').val(huruf);
    }
    
    function convertToLetterGrade(nilai) {
        if (nilai >= 80) return 'A';
        if (nilai >= 70) return 'B';
        if (nilai >= 60) return 'C';
        if (nilai >= 50) return 'D';
        return 'E';
    }
    
    // Form validation
    $('#penilaian-form').on('submit', function(e) {
        const action = $('button[type=submit][clicked=true]').val();
        const pembimbing = $('#nilai_pembimbing').val();
        const rekomendasi = $('input[name="rekomendasi"]:checked').val();
        
        if (!pembimbing || !rekomendasi) {
            e.preventDefault();
            alert('Nilai pembimbing dan rekomendasi wajib diisi!');
            return;
        }
        
        if (action === 'publish') {
            const penguji1 = $('#nilai_penguji1').val();
            const penguji2 = $('#nilai_penguji2').val();
            
            if (!penguji1 || !penguji2) {
                e.preventDefault();
                alert('Untuk publikasi, semua nilai penguji harus diisi!');
                return;
            }
            
            if (!confirm('Yakin akan mempublikasikan penilaian ini? Mahasiswa akan mendapat notifikasi dan dapat lanjut ke tahap Publikasi.')) {
                e.preventDefault();
                return;
            }
        } else {
            if (!confirm('Yakin menyimpan penilaian sebagai draft?')) {
                e.preventDefault();
                return;
            }
        }
    });
    
    // Track which submit button was clicked
    $('button[type=submit]').click(function() {
        $('button[type=submit]').removeAttr('clicked');
        $(this).attr('clicked', 'true');
    });
    
    // Initialize calculation
    calculateNilaiAkhir();
});
</script>