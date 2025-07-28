<!-- File: application/views/dosen/seminar_proposal/penilaian.php -->
<!-- Form Penilaian Seminar Proposal sesuai requirement -->

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
        <strong>Informasi:</strong> Anda sedang melakukan penilaian untuk seminar proposal yang telah terlaksana. 
        Pastikan semua komponen penilaian diisi dengan lengkap dan akurat.
    </div>

    <!-- Informasi Mahasiswa & Seminar -->
    <div class="card shadow mb-4">
        <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-user-graduate mr-2"></i>
                Informasi Seminar Proposal
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="140"><strong>Nama Mahasiswa</strong></td>
                            <td>: <?= $seminar->nama_mahasiswa ?></td>
                        </tr>
                        <tr>
                            <td><strong>NIM</strong></td>
                            <td>: <?= $seminar->nim ?></td>
                        </tr>
                        <tr>
                            <td><strong>Program Studi</strong></td>
                            <td>: <?= $seminar->nama_prodi ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>: <?= $seminar->email_mahasiswa ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td width="140"><strong>Tanggal Seminar</strong></td>
                            <td>: 
                                <?php
                                if ($seminar->tanggal_seminar) {
                                    echo date('d F Y', strtotime($seminar->tanggal_seminar));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Waktu</strong></td>
                            <td>: 
                                <?php
                                if ($seminar->jam_seminar) {
                                    echo date('H:i', strtotime($seminar->jam_seminar)) . ' WIT';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tempat</strong></td>
                            <td>: <?= $seminar->tempat_seminar ?: '-' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status Saat Ini</strong></td>
                            <td>: <span class="badge badge-primary">Perlu Penilaian</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="mt-3">
                <strong>Judul Proposal:</strong>
                <p class="text-justify mb-0"><?= $seminar->judul ?></p>
            </div>
        </div>
    </div>

    <!-- Form Penilaian -->
    <form method="post" action="<?= base_url('dosen/seminar_proposal/penilaian/' . $seminar->id) ?>" id="penilaianForm">
        
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
                    Masukkan catatan perbaikan dari setiap penguji untuk berbagai aspek proposal
                </p>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Latar Belakang & Rumusan Masalah:</label>
                            <textarea name="catatan_latar_belakang" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk latar belakang dan rumusan masalah..."><?= set_value('catatan_latar_belakang') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Tinjauan Pustaka & Kebaruan (Novelty):</label>
                            <textarea name="catatan_tinjauan_pustaka" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk tinjauan pustaka dan novelty..."><?= set_value('catatan_tinjauan_pustaka') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Landasan Teori:</label>
                            <textarea name="catatan_landasan_teori" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk landasan teori..."><?= set_value('catatan_landasan_teori') ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Metodologi Penelitian:</label>
                            <textarea name="catatan_metodologi" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk metodologi penelitian..."><?= set_value('catatan_metodologi') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Sistematika & Tata Tulis:</label>
                            <textarea name="catatan_sistematika" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan perbaikan untuk sistematika dan tata tulis..."><?= set_value('catatan_sistematika') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Catatan Umum:</label>
                            <textarea name="catatan_umum" class="form-control" rows="3" 
                                      placeholder="Masukkan catatan umum atau saran tambahan..."><?= set_value('catatan_umum') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Gabungan Catatan untuk Database -->
                <input type="hidden" name="catatan_revisi" id="catatan_revisi_combined">
            </div>
        </div>

        <!-- Komponen 2: Nilai Final Seminar Proposal -->
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
                    Rata-rata penilaian 3 penguji dengan bobot: Substansi & Metode (50%), Presentasi & Teknik (20%), Penguasaan Materi & Diskusi (30%)
                </p>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Penguji 1</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Substansi & Metode (50%):</label>
                                    <input type="number" name="nilai_substansi_1" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_substansi_1') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Presentasi & Teknik (20%):</label>
                                    <input type="number" name="nilai_presentasi_1" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_presentasi_1') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Penguasaan & Diskusi (30%):</label>
                                    <input type="number" name="nilai_diskusi_1" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_diskusi_1') ?>">
                                </div>
                                <div class="alert alert-light">
                                    <strong>Nilai Penguji 1: <span id="total_penguji_1">-</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Penguji 2</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Substansi & Metode (50%):</label>
                                    <input type="number" name="nilai_substansi_2" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_substansi_2') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Presentasi & Teknik (20%):</label>
                                    <input type="number" name="nilai_presentasi_2" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_presentasi_2') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Penguasaan & Diskusi (30%):</label>
                                    <input type="number" name="nilai_diskusi_2" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_diskusi_2') ?>">
                                </div>
                                <div class="alert alert-light">
                                    <strong>Nilai Penguji 2: <span id="total_penguji_2">-</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0">Penguji 3</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Substansi & Metode (50%):</label>
                                    <input type="number" name="nilai_substansi_3" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_substansi_3') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Presentasi & Teknik (20%):</label>
                                    <input type="number" name="nilai_presentasi_3" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_presentasi_3') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Penguasaan & Diskusi (30%):</label>
                                    <input type="number" name="nilai_diskusi_3" class="form-control nilai-input" 
                                           min="0" max="100" step="0.5" placeholder="0-100" value="<?= set_value('nilai_diskusi_3') ?>">
                                </div>
                                <div class="alert alert-light">
                                    <strong>Nilai Penguji 3: <span id="total_penguji_3">-</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hasil Akhir -->
                <div class="card border-info mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-trophy mr-2"></i>Hasil Akhir</h6>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="mb-2">
                            Nilai Akhir: <span id="nilai_akhir" class="text-primary font-weight-bold">-</span>
                        </h3>
                        <h4 class="mb-0">
                            Grade: <span id="grade_nilai" class="badge badge-lg badge-success">-</span>
                        </h4>
                        <input type="hidden" name="nilai_final_calculated" id="nilai_final_calculated">
                        <input type="hidden" name="grade_nilai" id="grade_nilai_input">
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
                    Pilih rekomendasi hasil berdasarkan penilaian keseluruhan
                </p>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Rekomendasi Hasil <span class="text-danger">*</span>:</label>
                            <div class="mt-2">
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="rekomendasi_1" name="rekomendasi_hasil" 
                                           class="custom-control-input" value="diterima_tanpa_revisi" 
                                           <?= set_radio('rekomendasi_hasil', 'diterima_tanpa_revisi') ?>>
                                    <label class="custom-control-label" for="rekomendasi_1">
                                        <span class="badge badge-success mr-2">✓</span>
                                        <strong>Diterima Tanpa Revisi</strong>
                                        <small class="text-muted d-block ml-4">Proposal sangat baik, dapat langsung lanjut ke penelitian</small>
                                    </label>
                                </div>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="rekomendasi_2" name="rekomendasi_hasil" 
                                           class="custom-control-input" value="revisi_minor" 
                                           <?= set_radio('rekomendasi_hasil', 'revisi_minor') ?>>
                                    <label class="custom-control-label" for="rekomendasi_2">
                                        <span class="badge badge-warning mr-2">⚠</span>
                                        <strong>Revisi Minor</strong>
                                        <small class="text-muted d-block ml-4">Perlu perbaikan kecil, dapat lanjut dengan revisi</small>
                                    </label>
                                </div>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="rekomendasi_3" name="rekomendasi_hasil" 
                                           class="custom-control-input" value="revisi_mayor" 
                                           <?= set_radio('rekomendasi_hasil', 'revisi_mayor') ?>>
                                    <label class="custom-control-label" for="rekomendasi_3">
                                        <span class="badge badge-warning mr-2">🔄</span>
                                        <strong>Revisi Mayor</strong>
                                        <small class="text-muted d-block ml-4">Perlu perbaikan signifikan sebelum lanjut</small>
                                    </label>
                                </div>
                                <div class="custom-control custom-radio mb-2">
                                    <input type="radio" id="rekomendasi_4" name="rekomendasi_hasil" 
                                           class="custom-control-input" value="ditolak" 
                                           <?= set_radio('rekomendasi_hasil', 'ditolak') ?>>
                                    <label class="custom-control-label" for="rekomendasi_4">
                                        <span class="badge badge-danger mr-2">✗</span>
                                        <strong>Ditolak</strong>
                                        <small class="text-muted d-block ml-4">Proposal tidak memenuhi standar, perlu pengajuan ulang</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Penjelasan Rekomendasi:</label>
                            <textarea name="penjelasan_rekomendasi" class="form-control" rows="8" 
                                      placeholder="Jelaskan alasan rekomendasi dan arahan untuk mahasiswa..."><?= set_value('penjelasan_rekomendasi') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Alert untuk Rekomendasi Ditolak -->
                <div id="alert_ditolak" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Perhatian:</strong> Jika hasil rekomendasi adalah "Ditolak", mahasiswa harus mengajukan ulang seminar proposal.
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-2">
                            <i class="fas fa-info-circle text-info mr-2"></i>
                            <strong>Draft:</strong> Simpan penilaian tanpa mempublikasikan ke mahasiswa
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-eye text-success mr-2"></i>
                            <strong>Simpan dan Publikasi:</strong> Penilaian akan langsung terlihat oleh mahasiswa
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="submit" name="save_type" value="draft" class="btn btn-secondary mr-2">
                            <i class="fas fa-save mr-2"></i>Simpan Draft
                        </button>
                        <button type="submit" name="save_type" value="publish" class="btn btn-primary" id="btn_publish">
                            <i class="fas fa-paper-plane mr-2"></i>Simpan dan Publikasi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

<!-- JavaScript untuk Kalkulasi Otomatis -->
<script>
$(document).ready(function() {
    
    // Kalkulasi otomatis nilai
    $('.nilai-input').on('input', function() {
        calculateValues();
    });
    
    // Combine catatan revisi sebelum submit
    $('#penilaianForm').on('submit', function() {
        combineRevisionNotes();
        return validateForm();
    });
    
    // Show alert untuk rekomendasi ditolak
    $('input[name="rekomendasi_hasil"]').change(function() {
        if ($(this).val() === 'ditolak') {
            $('#alert_ditolak').show();
        } else {
            $('#alert_ditolak').hide();
        }
    });
    
    function calculateValues() {
        // Kalkulasi untuk setiap penguji
        for (let i = 1; i <= 3; i++) {
            let substansi = parseFloat($(`input[name="nilai_substansi_${i}"]`).val()) || 0;
            let presentasi = parseFloat($(`input[name="nilai_presentasi_${i}"]`).val()) || 0;
            let diskusi = parseFloat($(`input[name="nilai_diskusi_${i}"]`).val()) || 0;
            
            // Hitung dengan bobot: Substansi (50%), Presentasi (20%), Diskusi (30%)
            let totalPenguji = (substansi * 0.5) + (presentasi * 0.2) + (diskusi * 0.3);
            $(`#total_penguji_${i}`).text(totalPenguji.toFixed(2));
        }
        
        // Kalkulasi nilai akhir (rata-rata 3 penguji)
        let total1 = parseFloat($('#total_penguji_1').text()) || 0;
        let total2 = parseFloat($('#total_penguji_2').text()) || 0;
        let total3 = parseFloat($('#total_penguji_3').text()) || 0;
        
        let nilaiAkhir = (total1 + total2 + total3) / 3;
        $('#nilai_akhir').text(nilaiAkhir.toFixed(2));
        $('#nilai_final_calculated').val(nilaiAkhir.toFixed(2));
        
        // Konversi ke grade
        let grade = '';
        if (nilaiAkhir >= 80) {
            grade = 'A';
            $('#grade_nilai').removeClass().addClass('badge badge-lg badge-success').text('A');
        } else if (nilaiAkhir >= 70) {
            grade = 'B';
            $('#grade_nilai').removeClass().addClass('badge badge-lg badge-info').text('B');
        } else if (nilaiAkhir >= 60) {
            grade = 'C';
            $('#grade_nilai').removeClass().addClass('badge badge-lg badge-warning').text('C');
        } else if (nilaiAkhir >= 50) {
            grade = 'D';
            $('#grade_nilai').removeClass().addClass('badge badge-lg badge-danger').text('D');
        } else {
            grade = 'E';
            $('#grade_nilai').removeClass().addClass('badge badge-lg badge-dark').text('E');
        }
        
        $('#grade_nilai_input').val(grade);
    }
    
    function combineRevisionNotes() {
        let notes = {
            'Latar Belakang & Rumusan Masalah': $('textarea[name="catatan_latar_belakang"]').val(),
            'Tinjauan Pustaka & Kebaruan': $('textarea[name="catatan_tinjauan_pustaka"]').val(),
            'Landasan Teori': $('textarea[name="catatan_landasan_teori"]').val(),
            'Metodologi Penelitian': $('textarea[name="catatan_metodologi"]').val(),
            'Sistematika & Tata Tulis': $('textarea[name="catatan_sistematika"]').val(),
            'Catatan Umum': $('textarea[name="catatan_umum"]').val()
        };
        
        let combined = '';
        for (let [key, value] of Object.entries(notes)) {
            if (value && value.trim() !== '') {
                combined += `**${key}:**\n${value.trim()}\n\n`;
            }
        }
        
        $('#catatan_revisi_combined').val(combined);
    }
    
    function validateForm() {
        let isValid = true;
        let errors = [];
        
        // Validasi rekomendasi hasil
        if (!$('input[name="rekomendasi_hasil"]:checked').length) {
            errors.push('Rekomendasi hasil harus dipilih');
            isValid = false;
        }
        
        // Validasi minimal ada satu catatan revisi
        let hasRevision = false;
        $('textarea[name^="catatan_"]').each(function() {
            if ($(this).val().trim() !== '') {
                hasRevision = true;
                return false;
            }
        });
        
        if (!hasRevision) {
            errors.push('Minimal satu catatan revisi harus diisi');
            isValid = false;
        }
        
        // Validasi nilai untuk publikasi
        if ($('#btn_publish').is(':focus') && $('#nilai_final_calculated').val() == '') {
            errors.push('Nilai harus diisi lengkap untuk publikasi');
            isValid = false;
        }
        
        if (!isValid) {
            alert('Errors:\n- ' + errors.join('\n- '));
        }
        
        return isValid;
    }
});
</script>

<style>
.badge-lg {
    font-size: 1.2em;
    padding: 0.5em 1em;
}

.custom-control-label {
    cursor: pointer;
}

.nilai-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.card-header {
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.alert-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}
</style>