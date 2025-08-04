<?php
// =========================================================================
// FILE 2: application/views/dosen/seminar_skripsi/detail.php
// =========================================================================
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-info-circle mr-2"></i>
            Detail Seminar Skripsi
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dosen/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Info Mahasiswa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user mr-2"></i>
                        Informasi Mahasiswa
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Nama Mahasiswa:</label>
                                <div class="font-weight-bold"><?= $seminar->nama_mahasiswa ?></div>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">NIM:</label>
                                <div class="font-weight-bold"><?= $seminar->nim ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Email:</label>
                                <div><?= $seminar->email_mahasiswa ?></div>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">Status:</label>
                                <div>
                                    <?php
                                    switch($seminar->status) {
                                        case 'submitted':
                                            echo '<span class="badge badge-warning">Menunggu Review</span>';
                                            break;
                                        case 'review_pembimbing':
                                            echo '<span class="badge badge-info">Sedang Direview</span>';
                                            break;
                                        case 'review_kaprodi':
                                            echo '<span class="badge badge-primary">Review Kaprodi</span>';
                                            break;
                                        case 'approved':
                                            echo '<span class="badge badge-success">Disetujui</span>';
                                            break;
                                        case 'rejected':
                                            echo '<span class="badge badge-danger">Ditolak</span>';
                                            break;
                                        case 'scheduled':
                                            echo '<span class="badge badge-info">Terjadwal</span>';
                                            break;
                                        case 'completed':
                                            echo '<span class="badge badge-success">Selesai</span>';
                                            break;
                                        default:
                                            echo '<span class="badge badge-secondary">' . ucfirst($seminar->status) . '</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ ENHANCED: Judul Skripsi dengan Perbandingan -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-book mr-2"></i>
                        Judul Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (isset($seminar->is_judul_changed) && $seminar->is_judul_changed): ?>
                        <!-- Ada perubahan judul -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Perhatian:</strong> Mahasiswa mengubah judul skripsi dari judul proposal awal.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Judul Proposal (Awal):</h6>
                                <div class="p-3 border rounded bg-light mb-3">
                                    <?= $seminar->judul_proposal_original ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Judul Skripsi (Baru):</h6>
                                <div class="p-3 border border-primary rounded bg-primary text-white mb-3">
                                    <strong><?= $seminar->judul_skripsi ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Tidak ada perubahan judul -->
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle mr-2"></i>
                            Judul skripsi sama dengan judul proposal awal.
                        </div>
                        <div class="p-3 border rounded bg-light">
                            <strong><?= $seminar->judul_current ?? $seminar->judul_proposal_original ?></strong>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($seminar->keterangan_mahasiswa)): ?>
                        <div class="mt-3">
                            <label class="text-muted">Keterangan Mahasiswa:</label>
                            <div class="p-2 border-left border-info bg-light">
                                <?= nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ✅ ENHANCED: File Download - 2 Files -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-download mr-2"></i>
                        File Dokumen
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- File Skripsi -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                    <h6 class="card-title">File Skripsi Lengkap</h6>
                                    <?php if(!empty($seminar->file_skripsi)): ?>
                                        <div class="mb-3">
                                            <small class="text-muted"><?= $seminar->file_skripsi ?></small>
                                        </div>
                                        <div class="btn-group-vertical w-100">
                                            <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                               class="btn btn-primary" target="_blank">
                                                <i class="fas fa-eye mr-1"></i> Lihat File
                                            </a>
                                            <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                               class="btn btn-outline-primary" download>
                                                <i class="fas fa-download mr-1"></i> Download
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">File tidak tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- File Surat Keterangan Penelitian -->
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                                    <h6 class="card-title">Surat Keterangan Penelitian</h6>
                                    <?php if(!empty($seminar->surat_keterangan_penelitian)): ?>
                                        <div class="mb-3">
                                            <small class="text-muted"><?= $seminar->surat_keterangan_penelitian ?></small>
                                        </div>
                                        <div class="btn-group-vertical w-100">
                                            <a href="<?= base_url('dosen/seminar_skripsi/view_surat_penelitian/' . $seminar->id) ?>" 
                                               class="btn btn-success" target="_blank">
                                                <i class="fas fa-eye mr-1"></i> Lihat Surat
                                            </a>
                                            <a href="<?= base_url('dosen/seminar_skripsi/view_surat_penelitian/' . $seminar->id) ?>" 
                                               class="btn btn-outline-success" download>
                                                <i class="fas fa-download mr-1"></i> Download
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">File tidak tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jurnal Bimbingan Reference -->
            <?php if(!empty($jurnal_bimbingan)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        Riwayat Jurnal Bimbingan Terakhir
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Materi Bimbingan</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($jurnal_bimbingan, 0, 3) as $jurnal): ?>
                                    <tr>
                                        <td>
                                            <small><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></small>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;">
                                                <?= $jurnal->materi_bimbingan ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= $jurnal->progress_mahasiswa ?>%</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Action Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks mr-2"></i>
                        Tindakan
                    </h6>
                </div>
                <div class="card-body">
                    <?php if($seminar->status == 'submitted' || $seminar->status == 'resubmitted'): ?>
                        <!-- Form Rekomendasi -->
                        <form id="rekomendasi-form" method="post" action="<?= base_url('dosen/seminar_skripsi/rekomendasi') ?>">
                            <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Rekomendasi:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rekomendasi" value="approved" id="approved">
                                    <label class="form-check-label text-success" for="approved">
                                        <i class="fas fa-check mr-1"></i> Setujui
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="rekomendasi" value="rejected" id="rejected">
                                    <label class="form-check-label text-danger" for="rejected">
                                        <i class="fas fa-times mr-1"></i> Tolak
                                    </label>
                                </div>
                            </div>

                            <div class="form-group" id="komentar-section" style="display: none;">
                                <label for="komentar_pembimbing" class="font-weight-bold text-danger">
                                    Alasan Penolakan: <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="komentar_pembimbing" name="komentar_pembimbing" 
                                          rows="4" placeholder="Jelaskan alasan penolakan atau saran perbaikan..."></textarea>
                            </div>

                            <div class="form-group" id="komentar-optional" style="display: none;">
                                <label for="komentar_opsional" class="font-weight-bold text-success">
                                    Komentar (Opsional):
                                </label>
                                <textarea class="form-control" id="komentar_opsional" name="komentar_pembimbing" 
                                          rows="3" placeholder="Catatan atau saran tambahan..."></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Kirim Rekomendasi
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- Status Info -->
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <h6>Status: <?= ucfirst($seminar->status) ?></h6>
                            <small>Tidak ada tindakan yang diperlukan saat ini.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-clock mr-2"></i>
                        Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Pengajuan Dibuat</h6>
                                <small class="text-muted">
                                    <?= date('d F Y, H:i', strtotime($seminar->created_at)) ?> WIB
                                </small>
                            </div>
                        </div>

                        <?php if(!empty($seminar->tanggal_review_pembimbing)): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-<?= $seminar->status_pembimbing == 'approved' ? 'success' : 'danger' ?>"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Review Pembimbing</h6>
                                <small class="text-muted">
                                    <?= date('d F Y, H:i', strtotime($seminar->tanggal_review_pembimbing)) ?> WIB
                                </small>
                                <div class="mt-1">
                                    <span class="badge badge-<?= $seminar->status_pembimbing == 'approved' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($seminar->status_pembimbing) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($seminar->tanggal_seminar)): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Jadwal Seminar</h6>
                                <small class="text-muted">
                                    <?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?>
                                    <?= !empty($seminar->jam_seminar) ? ', ' . date('H:i', strtotime($seminar->jam_seminar)) . ' WIB' : '' ?>
                                </small>
                                <?php if(!empty($seminar->tempat_seminar)): ?>
                                    <div class="mt-1">
                                        <small class="text-info">📍 <?= $seminar->tempat_seminar ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS -->
<style>
.info-group label {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-title {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.card-body .btn-group-vertical .btn {
    margin-bottom: 0.5rem;
}

.card-body .btn-group-vertical .btn:last-child {
    margin-bottom: 0;
}
</style>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Handle radio button change
    $('input[name="rekomendasi"]').on('change', function() {
        if ($(this).val() === 'rejected') {
            $('#komentar-section').show();
            $('#komentar-optional').hide();
            $('#komentar_pembimbing').prop('required', true);
        } else if ($(this).val() === 'approved') {
            $('#komentar-section').hide();
            $('#komentar-optional').show();
            $('#komentar_pembimbing').prop('required', false);
        }
    });

    // Form validation
    $('#rekomendasi-form').on('submit', function(e) {
        const rekomendasi = $('input[name="rekomendasi"]:checked').val();
        const komentar = $('#komentar_pembimbing').val().trim();
        
        if (!rekomendasi) {
            e.preventDefault();
            alert('Pilih rekomendasi terlebih dahulu!');
            return;
        }
        
        if (rekomendasi === 'rejected' && !komentar) {
            e.preventDefault();
            alert('Alasan penolakan wajib diisi!');
            $('#komentar_pembimbing').focus();
            return;
        }
        
        return confirm('Yakin dengan rekomendasi ini? Mahasiswa akan mendapat notifikasi email.');
    });
});
</script>