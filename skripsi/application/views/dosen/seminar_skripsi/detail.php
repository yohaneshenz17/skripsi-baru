<!-- 
File: application/views/dosen/seminar_skripsi/detail.php
IMPROVED UI - Detail Seminar Skripsi untuk Review Dosen
-->

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Seminar Skripsi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('dosen/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>

    <!-- Alert Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Info Mahasiswa & Proposal -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-graduate"></i> Informasi Mahasiswa & Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">Nama</td>
                                    <td><?= htmlspecialchars($seminar->nama_mahasiswa) ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">NIM</td>
                                    <td><?= htmlspecialchars($seminar->nim) ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Email</td>
                                    <td><?= htmlspecialchars($seminar->email_mahasiswa) ?></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status</td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'draft' => '<span class="badge badge-secondary">Draft</span>',
                                            'submitted' => '<span class="badge badge-info">Diajukan</span>',
                                            'review_pembimbing' => '<span class="badge badge-warning">Review Pembimbing</span>',
                                            'review_kaprodi' => '<span class="badge badge-warning">Review Kaprodi</span>',
                                            'approved' => '<span class="badge badge-success">Disetujui</span>',
                                            'rejected' => '<span class="badge badge-danger">Ditolak</span>',
                                            'scheduled' => '<span class="badge badge-primary">Terjadwal</span>',
                                            'completed' => '<span class="badge badge-success">Selesai</span>'
                                        ];
                                        echo $status_badges[$seminar->status] ?? '<span class="badge badge-secondary">Unknown</span>';
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">Tanggal Pengajuan</td>
                                    <td><?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?></td>
                                </tr>
                                <?php if (!empty($seminar->tanggal_seminar)): ?>
                                <tr>
                                    <td class="font-weight-bold">Jadwal Seminar</td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?><br>
                                        <small class="text-muted">
                                            <?= $seminar->jam_seminar ?> - <?= htmlspecialchars($seminar->tempat_seminar) ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php if (!empty($seminar->nama_penguji1) || !empty($seminar->nama_penguji2)): ?>
                                <tr>
                                    <td class="font-weight-bold">Dosen Penguji</td>
                                    <td>
                                        <?php if (!empty($seminar->nama_penguji1)): ?>
                                            <div>1. <?= htmlspecialchars($seminar->nama_penguji1) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($seminar->nama_penguji2)): ?>
                                            <div>2. <?= htmlspecialchars($seminar->nama_penguji2) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Judul Skripsi -->
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-book"></i> Judul Skripsi
                        </h6>
                        <p class="text-justify">
                            <?= htmlspecialchars(!empty($seminar->judul_skripsi) ? $seminar->judul_skripsi : $seminar->judul) ?>
                        </p>
                        <?php if (!empty($seminar->judul_skripsi) && $seminar->judul_skripsi != $seminar->judul): ?>
                            <small class="text-muted">
                                <strong>Judul Proposal:</strong> <?= htmlspecialchars($seminar->judul) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Keterangan Mahasiswa -->
                    <?php if (!empty($seminar->keterangan_mahasiswa)): ?>
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-comment"></i> Keterangan Mahasiswa
                        </h6>
                        <div class="alert alert-light">
                            <?= nl2br(htmlspecialchars($seminar->keterangan_mahasiswa)) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- File Skripsi -->
                    <?php if (!empty($seminar->file_skripsi)): ?>
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-file-pdf"></i> File Skripsi
                        </h6>
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf fa-2x text-danger mr-3"></i>
                                <div>
                                    <strong><?= htmlspecialchars($seminar->file_skripsi) ?></strong><br>
                                    <small class="text-muted">Diunggah: <?= date('d/m/Y H:i', strtotime($seminar->created_at)) ?></small>
                                </div>
                                <div class="ml-auto">
                                    <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                                       class="btn btn-primary btn-sm" target="_blank">
                                        <i class="fas fa-eye"></i> Lihat File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- File Turnitin (jika ada) -->
                    <?php if (!empty($seminar->file_turnitin)): ?>
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-file-alt"></i> File Turnitin
                        </h6>
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-alt fa-2x text-info mr-3"></i>
                                <div>
                                    <strong><?= htmlspecialchars($seminar->file_turnitin) ?></strong><br>
                                    <?php if (!empty($seminar->plagiarism_percentage)): ?>
                                        <small class="text-muted">
                                            Persentase Kemiripan: <strong><?= $seminar->plagiarism_percentage ?>%</strong>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-auto">
                                    <a href="<?= base_url('uploads/turnitin/' . $seminar->file_turnitin) ?>" 
                                       class="btn btn-info btn-sm" target="_blank">
                                        <i class="fas fa-eye"></i> Lihat File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Surat Keterangan Penelitian -->
                    <?php if (!empty($seminar->surat_keterangan_penelitian)): ?>
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-certificate"></i> Surat Keterangan Penelitian
                        </h6>
                        <div class="alert alert-success">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-certificate fa-2x text-success mr-3"></i>
                                <div>
                                    <strong><?= htmlspecialchars($seminar->surat_keterangan_penelitian) ?></strong><br>
                                    <small class="text-muted">Penelitian telah selesai dilaksanakan</small>
                                </div>
                                <div class="ml-auto">
                                    <a href="<?= base_url('uploads/surat_penelitian/' . $seminar->surat_keterangan_penelitian) ?>" 
                                       class="btn btn-success btn-sm" target="_blank">
                                        <i class="fas fa-eye"></i> Lihat Surat
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Review History -->
                    <?php if (!empty($seminar->komentar_pembimbing) || !empty($seminar->tanggal_review_pembimbing) || 
                              !empty($seminar->komentar_kaprodi) || !empty($seminar->tanggal_review_kaprodi)): ?>
                    <div class="mb-4">
                        <h6 class="font-weight-bold text-primary mb-2">
                            <i class="fas fa-history"></i> Riwayat Review
                        </h6>
                        <div class="timeline">
                            <!-- Review Pembimbing -->
                            <?php if (!empty($seminar->tanggal_review_pembimbing)): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <strong>Review Pembimbing</strong>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?>
                                        </small>
                                    </div>
                                    <?php if ($seminar->status_pembimbing == 'approved'): ?>
                                        <span class="badge badge-success mb-2">Disetujui</span>
                                    <?php elseif ($seminar->status_pembimbing == 'rejected'): ?>
                                        <span class="badge badge-danger mb-2">Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning mb-2">Pending</span>
                                    <?php endif; ?>
                                    <?php if (!empty($seminar->komentar_pembimbing)): ?>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($seminar->komentar_pembimbing)) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Review Kaprodi -->
                            <?php if (!empty($seminar->tanggal_review_kaprodi)): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <strong>Review Kaprodi</strong>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_kaprodi)) ?>
                                        </small>
                                    </div>
                                    <?php if ($seminar->status_kaprodi == 'approved'): ?>
                                        <span class="badge badge-success mb-2">Disetujui</span>
                                    <?php elseif ($seminar->status_kaprodi == 'rejected'): ?>
                                        <span class="badge badge-danger mb-2">Ditolak</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning mb-2">Pending</span>
                                    <?php endif; ?>
                                    <?php if (!empty($seminar->komentar_kaprodi)): ?>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($seminar->komentar_kaprodi)) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Eligibility Check -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-check-circle"></i> Kelayakan Seminar
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Seminar Proposal</span>
                            <?php if ($eligibility['seminar_proposal_completed']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Selesai</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Penelitian</span>
                            <?php if ($eligibility['penelitian_completed']): ?>
                                <span class="badge badge-success"><i class="fas fa-check"></i> Selesai</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($eligibility['can_proceed']): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> 
                            Memenuhi syarat untuk seminar skripsi
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Ada persyaratan yang belum terpenuhi:
                            <ul class="mb-0 mt-2">
                                <?php foreach ($eligibility['issues'] as $issue): ?>
                                    <li><?= htmlspecialchars($issue) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Rekomendasi -->
            <?php if ($seminar->status == 'submitted' && $seminar->current_step == 'pembimbing'): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-thumbs-up"></i> Berikan Rekomendasi
                    </h6>
                </div>
                <div class="card-body">
                    <form id="rekomendasi-form" method="POST" action="<?= base_url('dosen/seminar_skripsi/rekomendasi') ?>">
                        <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Pilih Rekomendasi:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rekomendasi" id="approved" value="approved">
                                <label class="form-check-label text-success" for="approved">
                                    <i class="fas fa-check"></i> Setujui untuk dilanjutkan ke Kaprodi
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="rekomendasi" id="rejected" value="rejected">
                                <label class="form-check-label text-danger" for="rejected">
                                    <i class="fas fa-times"></i> Tolak dan minta revisi
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="komentar-section" style="display: none;">
                            <label for="komentar_pembimbing" class="font-weight-bold">
                                Komentar/Feedback <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" 
                                      id="komentar_pembimbing" 
                                      name="komentar_pembimbing" 
                                      rows="4" 
                                      placeholder="Berikan komentar atau feedback untuk mahasiswa..."></textarea>
                            <small class="form-text text-muted">
                                Komentar wajib diisi jika menolak pengajuan
                            </small>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Kirim Rekomendasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Jurnal Bimbingan Reference -->
            <?php if (!empty($jurnal_bimbingan)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book-open"></i> Jurnal Bimbingan Terkini
                    </h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <?php $count = 0; foreach ($jurnal_bimbingan as $jurnal): $count++; ?>
                            <div class="mb-2 <?= $count > 3 ? 'd-none' : '' ?>">
                                <div class="d-flex justify-content-between">
                                    <strong><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></strong>
                                    <?php if ($jurnal->status_validasi == '1'): ?>
                                        <span class="badge badge-success badge-sm">Valid</span>
                                    <?php elseif ($jurnal->status_validasi == '2'): ?>
                                        <span class="badge badge-warning badge-sm">Revisi</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary badge-sm">Pending</span>
                                    <?php endif; ?>
                                </div>
                                <p class="mb-1"><?= character_limiter($jurnal->materi_bimbingan, 60) ?></p>
                                <?php if (!empty($jurnal->catatan_dosen)): ?>
                                    <small class="text-muted">Catatan: <?= character_limiter($jurnal->catatan_dosen, 40) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (count($jurnal_bimbingan) > 3): ?>
                            <button class="btn btn-link btn-sm p-0" onclick="toggleJurnal()">
                                <span id="toggle-text">Lihat selengkapnya...</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('dosen/seminar_skripsi') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                        </a>
                        
                        <?php if (!empty($seminar->file_skripsi)): ?>
                        <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                           class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fas fa-file-pdf"></i> Buka File Skripsi
                        </a>
                        <?php endif; ?>
                        
                        <?php if (in_array($seminar->status, ['scheduled', 'completed'])): ?>
                        <a href="<?= base_url('dosen/seminar_skripsi/penilaian/' . $seminar->id) ?>" 
                           class="btn btn-outline-success btn-sm">
                            <i class="fas fa-edit"></i> Input Penilaian
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional CSS -->
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.table-borderless td {
    border: none;
    padding: 0.3rem 0.5rem;
}

.alert-light {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #495057;
}

.form-check-label {
    cursor: pointer;
}

.form-check-input:checked + .form-check-label {
    font-weight: bold;
}

@media (max-width: 768px) {
    .d-grid {
        display: grid !important;
        gap: 0.5rem !important;
    }
    
    .timeline {
        padding-left: 20px;
    }
    
    .timeline-marker {
        left: -25px;
        width: 10px;
        height: 10px;
    }
}
</style>

<script>
function toggleJurnal() {
    const hiddenItems = document.querySelectorAll('.d-none');
    const toggleText = document.getElementById('toggle-text');
    
    if (hiddenItems.length > 0) {
        hiddenItems.forEach(item => item.classList.remove('d-none'));
        toggleText.textContent = 'Sembunyikan...';
    } else {
        const items = document.querySelectorAll('.mb-2');
        for (let i = 3; i < items.length - 1; i++) {
            items[i].classList.add('d-none');
        }
        toggleText.textContent = 'Lihat selengkapnya...';
    }
}
</script>