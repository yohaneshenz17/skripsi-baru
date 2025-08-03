<!-- ====================================== -->
<!-- FILE: application/views/kaprodi/seminar_skripsi/detail.php -->
<!-- Form Review Turnitin untuk Kaprodi -->
<!-- ====================================== -->

<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-search"></i> Review Seminar Skripsi</h1>
                    <p class="text-muted">Validasi Turnitin & Kelayakan Seminar</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('kaprodi/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('kaprodi/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                        <li class="breadcrumb-item active">Review</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Info Mahasiswa -->
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-graduate mr-2"></i>
                                Informasi Mahasiswa
                            </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td><?= $seminar->nama_mahasiswa ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIM:</strong></td>
                                    <td><?= $seminar->nim ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Prodi:</strong></td>
                                    <td><?= $seminar->nama_prodi ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Pembimbing:</strong></td>
                                    <td><?= $seminar->nama_pembimbing ?: 'Belum ditetapkan' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <?php
                                        $status_badge = '';
                                        switch ($seminar->status) {
                                            case 'review_kaprodi':
                                                $status_badge = '<span class="badge badge-warning">Menunggu Review</span>';
                                                break;
                                            case 'approved':
                                                $status_badge = '<span class="badge badge-success">Disetujui</span>';
                                                break;
                                            case 'rejected':
                                                $status_badge = '<span class="badge badge-danger">Ditolak</span>';
                                                break;
                                            default:
                                                $status_badge = '<span class="badge badge-secondary">' . ucfirst($seminar->status) . '</span>';
                                        }
                                        echo $status_badge;
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- File Skripsi -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-pdf mr-2"></i>
                                File Skripsi
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($seminar->file_skripsi)): ?>
                                <div class="text-center">
                                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                    <br>
                                    <a href="<?= base_url('uploads/seminar_skripsi/' . $seminar->file_skripsi) ?>" 
                                       target="_blank" 
                                       class="btn btn-primary">
                                        <i class="fas fa-download mr-2"></i>
                                        Download Skripsi
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    File skripsi belum diupload.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form Review -->
                <div class="col-md-8">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-search mr-2"></i>
                                Form Review Turnitin
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Info Judul -->
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle mr-2"></i>Judul Skripsi:</h5>
                                <p class="mb-0"><strong><?= $seminar->judul ?></strong></p>
                                
                                <?php if (!empty($seminar->keterangan_mahasiswa)): ?>
                                    <hr>
                                    <h6>Keterangan Mahasiswa:</h6>
                                    <p class="mb-0"><?= nl2br($seminar->keterangan_mahasiswa) ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Rekomendasi Dosen Pembimbing -->
                            <?php if ($seminar->status_pembimbing == 'approved'): ?>
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle mr-2"></i>Rekomendasi Dosen Pembimbing</h5>
                                    <p><strong>Status:</strong> Disetujui</p>
                                    <p><strong>Tanggal:</strong> <?= date('d F Y, H:i', strtotime($seminar->tanggal_review_pembimbing)) ?> WIB</p>
                                    <?php if (!empty($seminar->komentar_pembimbing)): ?>
                                        <p><strong>Komentar:</strong> <?= nl2br($seminar->komentar_pembimbing) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Form Validasi Turnitin -->
                            <?php if ($seminar->status_kaprodi == 'pending'): ?>
                                <form action="<?= base_url('kaprodi/seminar_skripsi/validasi_turnitin') ?>" 
                                      method="post" 
                                      enctype="multipart/form-data" 
                                      id="form-validasi">
                                    <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
                                    
                                    <div class="form-group">
                                        <label for="file_turnitin">
                                            <i class="fas fa-upload mr-2"></i>
                                            Upload File Hasil Turnitin <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" 
                                               class="form-control-file" 
                                               id="file_turnitin" 
                                               name="file_turnitin" 
                                               accept=".pdf"
                                               required>
                                        <small class="form-text text-muted">
                                            Format: PDF, Maksimal 5MB
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label for="plagiarism_percentage">
                                            <i class="fas fa-chart-line mr-2"></i>
                                            Persentase Plagiarisme (%) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="plagiarism_percentage" 
                                               name="plagiarism_percentage" 
                                               min="0" 
                                               max="100" 
                                               step="0.01" 
                                               placeholder="Contoh: 25.50"
                                               required>
                                        <small class="form-text text-muted">
                                            <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                                            <strong>Kebijakan:</strong> Maksimal 30% untuk dapat disetujui
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label for="komentar_kaprodi">
                                            <i class="fas fa-comment mr-2"></i>
                                            Komentar Validasi
                                        </label>
                                        <textarea class="form-control" 
                                                  id="komentar_kaprodi" 
                                                  name="komentar_kaprodi" 
                                                  rows="4" 
                                                  placeholder="Berikan komentar atau catatan tambahan..."></textarea>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="submit" 
                                                    name="keputusan" 
                                                    value="approve" 
                                                    class="btn btn-success btn-block"
                                                    id="btn-approve">
                                                <i class="fas fa-check mr-2"></i>
                                                Setujui Seminar
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" 
                                                    name="keputusan" 
                                                    value="reject" 
                                                    class="btn btn-danger btn-block"
                                                    id="btn-reject">
                                                <i class="fas fa-times mr-2"></i>
                                                Tolak Seminar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?>
                                <!-- Hasil Review -->
                                <div class="alert <?= $seminar->status_kaprodi == 'approved' ? 'alert-success' : 'alert-danger' ?>">
                                    <h5>
                                        <i class="fas <?= $seminar->status_kaprodi == 'approved' ? 'fa-check-circle' : 'fa-times-circle' ?> mr-2"></i>
                                        Hasil Review
                                    </h5>
                                    <p><strong>Status:</strong> <?= $seminar->status_kaprodi == 'approved' ? 'Disetujui' : 'Ditolak' ?></p>
                                    <p><strong>Persentase Plagiarisme:</strong> <?= $seminar->plagiarism_percentage ?>%</p>
                                    <p><strong>Tanggal Review:</strong> <?= date('d F Y, H:i', strtotime($seminar->tanggal_review_kaprodi)) ?> WIB</p>
                                    <?php if (!empty($seminar->komentar_kaprodi)): ?>
                                        <p><strong>Komentar:</strong> <?= nl2br($seminar->komentar_kaprodi) ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($seminar->file_turnitin)): ?>
                                        <hr>
                                        <a href="<?= base_url('uploads/turnitin/' . $seminar->file_turnitin) ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            Lihat File Turnitin
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <?php if ($seminar->status_kaprodi == 'approved' && empty($seminar->tanggal_seminar)): ?>
                                    <div class="text-center mt-3">
                                        <a href="<?= base_url('kaprodi/seminar_skripsi/penjadwalan/' . $seminar->id) ?>" 
                                           class="btn btn-primary btn-lg">
                                            <i class="fas fa-calendar-plus mr-2"></i>
                                            Lanjut ke Penjadwalan
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JavaScript untuk validasi form -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-validasi');
    const plagiarismInput = document.getElementById('plagiarism_percentage');
    const btnApprove = document.getElementById('btn-approve');
    const btnReject = document.getElementById('btn-reject');
    const komentarTextarea = document.getElementById('komentar_kaprodi');

    if (form) {
        // Validasi real-time persentase plagiarisme
        plagiarismInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            
            if (value > 30) {
                btnApprove.disabled = true;
                btnApprove.innerHTML = '<i class="fas fa-times mr-2"></i>Tidak Dapat Disetujui (>30%)';
                btnApprove.classList.remove('btn-success');
                btnApprove.classList.add('btn-secondary');
            } else {
                btnApprove.disabled = false;
                btnApprove.innerHTML = '<i class="fas fa-check mr-2"></i>Setujui Seminar';
                btnApprove.classList.remove('btn-secondary');
                btnApprove.classList.add('btn-success');
            }
        });

        // Validasi komentar untuk penolakan
        btnReject.addEventListener('click', function(e) {
            if (komentarTextarea.value.trim() === '') {
                e.preventDefault();
                alert('Komentar wajib diisi untuk penolakan!');
                komentarTextarea.focus();
                return false;
            }
        });

        // Konfirmasi sebelum submit
        form.addEventListener('submit', function(e) {
            const keputusan = e.submitter.value;
            const plagiarism = parseFloat(plagiarismInput.value);
            
            let message = '';
            if (keputusan === 'approve') {
                message = `Yakin ingin menyetujui seminar ini?\nPlagiarisme: ${plagiarism}%`;
            } else {
                message = 'Yakin ingin menolak seminar ini?';
            }
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>