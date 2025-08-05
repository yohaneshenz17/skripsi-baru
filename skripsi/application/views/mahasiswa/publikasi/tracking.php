<!-- 
TRACKING PUBLIKASI - 9 LANGKAH WORKFLOW DETAIL
File: application/views/mahasiswa/publikasi/tracking.php

MENAMPILKAN PROGRESS DETAIL SEMUA 9 LANGKAH:
1. Mahasiswa Memenuhi Syarat
2. Isi Form Pengajuan  
3. Kirim Ajuan
4. Dosen Review
5. Dosen Approve/Reject
6. Kembali ke Mahasiswa (jika reject)
7. Staf Validasi
8. Selesai
9. Download Surat
-->

<!-- Page Header -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-info card-outline">
            <div class="card-body text-center">
                <h4>
                    <i class="fas fa-route text-info"></i>
                    <strong>Tracking Publikasi:</strong> 9 Langkah Workflow Tugas Akhir
                </h4>
                <p class="mb-0 text-muted">Pantau progress publikasi Anda dari Step 1 hingga Step 9</p>
            </div>
        </div>
    </div>
</div>

<!-- Progress Overview -->
<div class="row">
    <div class="col-lg-8">
        <!-- Progress Bar Visual -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Progress Overview</h3>
            </div>
            <div class="card-body">
                
                <!-- Progress Bar -->
                <div class="progress mb-4" style="height: 25px;">
                    <?php 
                    $progress_percentage = 11; // Default step 1 (11% per step)
                    $step_current = 1;
                    
                    // Tentukan progress berdasarkan status
                    if (isset($publikasi->status)) {
                        switch($publikasi->status) {
                            case 'draft': 
                                $progress_percentage = 22; 
                                $step_current = 2;
                                break;
                            case 'submitted': 
                                $progress_percentage = 33; 
                                $step_current = 3;
                                break;
                            case 'review_pembimbing': 
                                $progress_percentage = 44; 
                                $step_current = 4;
                                break;
                            case 'approved_pembimbing': 
                                $progress_percentage = 66; 
                                $step_current = 6;
                                break;
                            case 'rejected': 
                                $progress_percentage = 55; 
                                $step_current = 5;
                                break;
                            case 'review_staf': 
                                $progress_percentage = 77; 
                                $step_current = 7;
                                break;
                            case 'completed': 
                                $progress_percentage = 100; 
                                $step_current = 9;
                                break;
                        }
                    }
                    ?>
                    <div class="progress-bar bg-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'rejected' ? 'warning' : 'primary') ?> progress-bar-striped progress-bar-animated" 
                         style="width: <?= $progress_percentage ?>%">
                        Step <?= $step_current ?>/9 (<?= $progress_percentage ?>%)
                    </div>
                </div>

                <!-- Current Status Alert -->
                <div class="alert alert-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'rejected' ? 'warning' : 'info') ?>">
                    <h6><i class="fas fa-info-circle"></i> Status Saat Ini</h6>
                    <?php if ($publikasi->status === 'draft'): ?>
                        <strong>Step 2:</strong> Form pengajuan masih dalam tahap draft. Silakan lengkapi dan kirim ajuan.
                    <?php elseif ($publikasi->status === 'submitted'): ?>
                        <strong>Step 3:</strong> Ajuan telah dikirim dan menunggu masuk ke tahap review dosen.
                    <?php elseif ($publikasi->status === 'review_pembimbing'): ?>
                        <strong>Step 4:</strong> Sedang dalam tahap review oleh dosen pembimbing.
                    <?php elseif ($publikasi->status === 'approved_pembimbing'): ?>
                        <strong>Step 6:</strong> Disetujui dosen pembimbing, menunggu validasi staf.
                    <?php elseif ($publikasi->status === 'rejected'): ?>
                        <strong>Step 5:</strong> Ditolak dosen pembimbing, kembali ke mahasiswa untuk perbaikan.
                    <?php elseif ($publikasi->status === 'review_staf'): ?>
                        <strong>Step 7:</strong> Sedang dalam tahap validasi dan input repository oleh staf.
                    <?php elseif ($publikasi->status === 'completed'): ?>
                        <strong>Step 9:</strong> 🎉 Selamat! Semua tahap selesai. Publikasi berhasil!
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Timeline Detail 9 Langkah -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Timeline Detail Workflow</h3>
            </div>
            <div class="card-body">
                
                <div class="timeline">
                    
                    <!-- STEP 1: Syarat Terpenuhi -->
                    <div class="time-label">
                        <span class="bg-success">STEP 1</span>
                    </div>
                    <div>
                        <i class="fas fa-check bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-check-circle"></i> Selesai</span>
                            <h3 class="timeline-header">Mahasiswa Memenuhi Syarat</h3>
                            <div class="timeline-body">
                                <div class="alert alert-success mb-2">
                                    <strong>✅ Syarat Terpenuhi:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Minimal 16 Jurnal Bimbingan Tervalidasi</li>
                                        <li>Seminar Skripsi telah selesai dan disetujui</li>
                                    </ul>
                                </div>
                                <small class="text-muted">Step ini otomatis tervalidasi sistem berdasarkan data jurnal bimbingan dan status seminar skripsi.</small>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Isi Form -->
                    <div class="time-label">
                        <span class="bg-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed']) ? 'success' : ($publikasi->status == 'draft' ? 'warning' : 'secondary') ?>">STEP 2</span>
                    </div>
                    <div>
                        <i class="fas fa-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed']) ? 'check' : ($publikasi->status == 'draft' ? 'edit' : 'clock') ?> 
                           bg-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed']) ? 'success' : ($publikasi->status == 'draft' ? 'warning' : 'secondary') ?>"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="far fa-clock"></i> 
                                <?= isset($publikasi->created_at) ? date('d/m/Y H:i', strtotime($publikasi->created_at)) : '-' ?>
                            </span>
                            <h3 class="timeline-header">Isi Form Pengajuan Publikasi (9 Field)</h3>
                            <div class="timeline-body">
                                <?php if ($publikasi->status == 'draft'): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-edit"></i> <strong>Sedang Berlangsung:</strong> Form masih dalam tahap draft.
                                    </div>
                                    <div class="mt-2">
                                        <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Lengkapi Form
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check"></i> <strong>Selesai:</strong> Form pengajuan telah diisi lengkap dengan 9 field data.
                                    </div>
                                    <div class="row text-sm">
                                        <div class="col-md-6">
                                            <strong>Data yang diisi:</strong>
                                            <ul class="list-unstyled mt-1">
                                                <li>✅ 1. Nama Lengkap</li>
                                                <li>✅ 2. NIM</li>
                                                <li>✅ 3. Program Studi</li>
                                                <li>✅ 4. Judul Skripsi Final</li>
                                                <li>✅ 5. Dosen Pembimbing</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <br>
                                            <ul class="list-unstyled mt-1">
                                                <li>✅ 6. Tanggal Ujian Skripsi</li>
                                                <li>✅ 7. Surat Perpustakaan</li>
                                                <li>✅ 8. File Skripsi Final</li>
                                                <li>✅ 9. Link Repository</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="mb-0"><strong>Judul Final:</strong> "<?= $publikasi->judul_skripsi_final ?>"</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Kirim Ajuan -->
                    <div class="time-label">
                        <span class="bg-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed']) ? 'success' : 'secondary' ?>">STEP 3</span>
                    </div>
                    <div>
                        <i class="fas fa-paper-plane bg-<?= in_array($publikasi->status, ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed']) ? 'success' : 'secondary' ?>"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="far fa-clock"></i> 
                                <?= isset($publikasi->tanggal_pengajuan) ? date('d/m/Y H:i', strtotime($publikasi->tanggal_pengajuan)) : '-' ?>
                            </span>
                            <h3 class="timeline-header">Kirim Ajuan ke Dosen Pembimbing</h3>
                            <div class="timeline-body">
                                <?php if (in_array($publikasi->status, ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed'])): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check"></i> <strong>Selesai:</strong> Ajuan telah berhasil dikirim ke dosen pembimbing.
                                    </div>
                                    <p><strong>Dikirim kepada:</strong> <?= $publikasi->nama_pembimbing ?? 'Dosen Pembimbing' ?></p>
                                <?php else: ?>
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-clock"></i> <strong>Menunggu:</strong> Ajuan belum dikirim. Selesaikan Step 2 terlebih dahulu.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: Dosen Review -->
                    <div class="time-label">
                        <span class="bg-<?= in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed']) ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : ($publikasi->status == 'review_pembimbing' ? 'warning' : 'secondary')) ?>">STEP 4</span>
                    </div>
                    <div>
                        <i class="fas fa-<?= in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed']) ? 'check' : ($publikasi->status == 'rejected' ? 'times' : ($publikasi->status == 'review_pembimbing' ? 'clock' : 'clock')) ?> 
                           bg-<?= in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed']) ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : ($publikasi->status == 'review_pembimbing' ? 'warning' : 'secondary')) ?>"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="far fa-clock"></i> 
                                <?= isset($publikasi->tanggal_review_pembimbing) ? date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) : '-' ?>
                            </span>
                            <h3 class="timeline-header">Dosen Pembimbing Review Ajuan</h3>
                            <div class="timeline-body">
                                <?php if ($publikasi->status == 'review_pembimbing'): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-clock"></i> <strong>Sedang Berlangsung:</strong> Dosen pembimbing sedang melakukan review terhadap ajuan publikasi Anda.
                                    </div>
                                    <p>Harap menunggu. Dosen akan melakukan evaluasi terhadap dokumen dan data yang Anda submit.</p>
                                    
                                <?php elseif (in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed'])): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check"></i> <strong>Selesai:</strong> Dosen pembimbing telah selesai melakukan review.
                                    </div>
                                    
                                <?php elseif ($publikasi->status == 'rejected'): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-times"></i> <strong>Selesai:</strong> Dosen pembimbing telah menyelesaikan review.
                                    </div>
                                    
                                <?php else: ?>
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-clock"></i> <strong>Menunggu:</strong> Belum masuk tahap review. Selesaikan step sebelumnya.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 5: Hasil Review Dosen -->
                    <div class="time-label">
                        <span class="bg-<?= in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed']) ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : 'secondary') ?>">STEP 5</span>
                    </div>
                    <div>
                        <i class="fas fa-<?= in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed']) ? 'thumbs-up' : ($publikasi->status == 'rejected' ? 'thumbs-down' : 'clock') ?> 
                           bg-<?= in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed']) ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : 'secondary') ?>"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="far fa-clock"></i> 
                                <?= isset($publikasi->tanggal_review_pembimbing) ? date('d/m/Y H:i', strtotime($publikasi->tanggal_review_pembimbing)) : '-' ?>
                            </span>
                            <h3 class="timeline-header">Keputusan Dosen Pembimbing</h3>
                            <div class="timeline-body">
                                <?php if (in_array($publikasi->status, ['approved_pembimbing', 'review_staf', 'completed'])): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-thumbs-up"></i> <strong>✅ DISETUJUI:</strong> Dosen pembimbing menyetujui publikasi Anda.
                                    </div>
                                    <?php if (isset($publikasi->komentar_pembimbing) && $publikasi->komentar_pembimbing): ?>
                                        <div class="mt-2">
                                            <strong>Komentar Dosen:</strong><br>
                                            <em>"<?= $publikasi->komentar_pembimbing ?>"</em>
                                        </div>
                                    <?php endif; ?>
                                    <p class="text-success mt-2"><strong>Status:</strong> Lanjut ke Step 7 (Validasi Staf)</p>
                                    
                                <?php elseif ($publikasi->status == 'rejected'): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-thumbs-down"></i> <strong>❌ DITOLAK:</strong> Publikasi perlu diperbaiki.
                                    </div>
                                    <?php if (isset($publikasi->komentar_pembimbing) && $publikasi->komentar_pembimbing): ?>
                                        <div class="mt-2">
                                            <strong>Alasan Penolakan:</strong><br>
                                            <em>"<?= $publikasi->komentar_pembimbing ?>"</em>
                                        </div>
                                    <?php endif; ?>
                                    <p class="text-danger mt-2"><strong>Status:</strong> Kembali ke Step 6 (Perbaikan Mahasiswa)</p>
                                    
                                <?php else: ?>
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-clock"></i> <strong>Menunggu:</strong> Keputusan belum dibuat. Tunggu Step 4 selesai.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 6: Kembali ke Mahasiswa (Jika Ditolak) -->
                    <?php if ($publikasi->status == 'rejected'): ?>
                    <div class="time-label">
                        <span class="bg-warning">STEP 6</span>
                    </div>
                    <div>
                        <i class="fas fa-redo bg-warning"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Kembali ke Mahasiswa untuk Perbaikan</h3>
                            <div class="timeline-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Action Required:</strong> Silakan perbaiki pengajuan sesuai komentar dosen.
                                </div>
                                <div class="mt-2">
                                    <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Perbaiki Pengajuan (Kembali ke Step 2)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- STEP 7: Staf Validasi -->
                    <div class="time-label">
                        <span class="bg-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'review_staf' ? 'warning' : 'secondary') ?>">STEP 7</span>
                    </div>
                    <div>
                        <i class="fas fa-<?= $publikasi->status == 'completed' ? 'check' : ($publikasi->status == 'review_staf' ? 'cog' : 'clock') ?> 
                           bg-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'review_staf' ? 'warning' : 'secondary') ?>"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="far fa-clock"></i> 
                                <?= isset($publikasi->tanggal_validasi_staf) ? date('d/m/Y H:i', strtotime($publikasi->tanggal_validasi_staf)) : '-' ?>
                            </span>
                            <h3 class="timeline-header">Staf Input Repository dan Validasi</h3>
                            <div class="timeline-body">
                                <?php if ($publikasi->status == 'review_staf'): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-cog fa-spin"></i> <strong>Sedang Berlangsung:</strong> Staf sedang memproses validasi dan input repository.
                                    </div>
                                    <p><strong>Aktivitas staf meliputi:</strong></p>
                                    <ul>
                                        <li>Input/upload ke repository institusi</li>
                                        <li>Validasi final dokumen</li>
                                        <li>Persiapan surat keterangan</li>
                                    </ul>
                                    
                                <?php elseif ($publikasi->status == 'completed'): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check"></i> <strong>Selesai:</strong> Staf telah menyelesaikan validasi dan input repository.
                                    </div>
                                    <?php if (isset($publikasi->link_repository) && $publikasi->link_repository): ?>
                                        <div class="mt-2">
                                            <strong>Link Repository:</strong><br>
                                            <a href="<?= $publikasi->link_repository ?>" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-external-link-alt"></i> Lihat Publikasi Online
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($publikasi->catatan_staf) && $publikasi->catatan_staf): ?>
                                        <div class="mt-2">
                                            <strong>Catatan Staf:</strong><br>
                                            <em>"<?= $publikasi->catatan_staf ?>"</em>
                                        </div>
                                    <?php endif; ?>
                                    
                                <?php else: ?>
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-clock"></i> <strong>Menunggu:</strong> Belum masuk tahap validasi staf. Step 5 harus selesai dengan persetujuan dosen.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 8: Publikasi Selesai -->
                    <div class="time-label">
                        <span class="bg-<?= $publikasi->status == 'completed' ? 'success' : 'secondary' ?>">STEP 8</span>
                    </div>
                    <div>
                        <i class="fas fa-flag-checkered bg-<?= $publikasi->status == 'completed' ? 'success' : 'secondary' ?>"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="far fa-clock"></i> 
                                <?= $publikasi->status == 'completed' ? date('d/m/Y H:i', strtotime($publikasi->tanggal_validasi_staf)) : '-' ?>
                            </span>
                            <h3 class="timeline-header">Publikasi Tugas Akhir Selesai</h3>
                            <div class="timeline-body">
                                <?php if ($publikasi->status == 'completed'): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-trophy"></i> <strong>🎉 SELAMAT!</strong> Publikasi tugas akhir Anda telah selesai diproses.
                                    </div>
                                    <p><strong>Pencapaian:</strong></p>
                                    <ul>
                                        <li>✅ Tugas akhir telah terpublikasi di repository institusi</li>
                                        <li>✅ Dokumen telah tervalidasi lengkap</li>
                                        <li>✅ Surat keterangan publikasi siap diunduh</li>
                                    </ul>
                                    
                                <?php else: ?>
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-clock"></i> <strong>Menunggu:</strong> Publikasi belum selesai. Tunggu Step 7 selesai.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 9: Download Surat -->
                    <div class="time-label">
                        <span class="bg-<?= $publikasi->status == 'completed' ? 'success' : 'secondary' ?>">STEP 9</span>
                    </div>
                    <div>
                        <i class="fas fa-download bg-<?= $publikasi->status == 'completed' ? 'success' : 'secondary' ?>"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Download Surat Keterangan Publikasi</h3>
                            <div class="timeline-body">
                                <?php if ($publikasi->status == 'completed'): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-certificate"></i> <strong>Tersedia:</strong> Surat keterangan publikasi siap diunduh.
                                    </div>
                                    <div class="mt-3">
                                        <a href="<?= base_url('mahasiswa/publikasi/download_surat/' . $publikasi->id) ?>" 
                                           class="btn btn-success btn-lg" target="_blank">
                                            <i class="fas fa-download"></i> Download Surat Keterangan Publikasi
                                        </a>
                                    </div>
                                    <p class="mt-2 text-muted">
                                        <small>
                                            <i class="fas fa-info-circle"></i> 
                                            Surat ini adalah bukti resmi bahwa tugas akhir Anda telah dipublikasikan sesuai dengan ketentuan institusi.
                                        </small>
                                    </p>
                                    
                                <?php else: ?>
                                    <div class="alert alert-secondary">
                                        <i class="fas fa-clock"></i> <strong>Belum Tersedia:</strong> Surat keterangan akan tersedia setelah Step 8 selesai.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- End Timeline -->
                    <div>
                        <i class="far fa-clock bg-gray"></i>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Sidebar: Info Publikasi -->
    <div class="col-lg-4">
        <!-- Detail Publikasi -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Detail Publikasi</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td width="40%"><strong>Mahasiswa</strong></td>
                        <td>: <?= $publikasi->nama_mahasiswa ?></td>
                    </tr>
                    <tr>
                        <td><strong>NIM</strong></td>
                        <td>: <?= $publikasi->nim ?></td>
                    </tr>
                    <tr>
                        <td><strong>Program Studi</strong></td>
                        <td>: <?= isset($publikasi->nama_prodi) ? $publikasi->nama_prodi : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Judul Skripsi</strong></td>
                        <td>: <?= $publikasi->judul_skripsi_final ?></td>
                    </tr>
                    <tr>
                        <td><strong>Dosen Pembimbing</strong></td>
                        <td>: <?= $publikasi->nama_pembimbing ?></td>
                    </tr>
                    <tr>
                        <td><strong>Status Workflow</strong></td>
                        <td>: 
                            <span class="badge badge-<?= $publikasi->status == 'completed' ? 'success' : ($publikasi->status == 'rejected' ? 'danger' : 'warning') ?>">
                                Step <?= $step_current ?>/9
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Pengajuan</strong></td>
                        <td>: <?= isset($publikasi->tanggal_pengajuan) ? date('d F Y', strtotime($publikasi->tanggal_pengajuan)) : '-' ?></td>
                    </tr>
                    <?php if (isset($publikasi->link_repository) && $publikasi->link_repository): ?>
                    <tr>
                        <td><strong>Repository</strong></td>
                        <td>: 
                            <a href="<?= $publikasi->link_repository ?>" target="_blank" class="btn btn-xs btn-info">
                                <i class="fas fa-external-link-alt"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical w-100">
                    <a href="<?= base_url('mahasiswa/publikasi') ?>" class="btn btn-secondary mb-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                    
                    <?php if ($publikasi->status == 'draft'): ?>
                        <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning mb-2">
                            <i class="fas fa-edit"></i> Lengkapi Form (Step 2)
                        </a>
                    <?php elseif ($publikasi->status == 'rejected'): ?>
                        <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-danger mb-2">
                            <i class="fas fa-edit"></i> Perbaiki Pengajuan
                        </a>
                    <?php elseif ($publikasi->status == 'completed'): ?>
                        <a href="<?= base_url('mahasiswa/publikasi/download_surat/' . $publikasi->id) ?>" class="btn btn-success mb-2" target="_blank">
                            <i class="fas fa-download"></i> Download Surat
                        </a>
                    <?php endif; ?>
                    
                    <button type="button" class="btn btn-info" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Timeline
                    </button>
                </div>
            </div>
        </div>

        <!-- Help -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-question-circle"></i> Bantuan</h3>
            </div>
            <div class="card-body">
                <p class="text-sm">
                    <strong>Butuh bantuan?</strong><br>
                    Jika ada pertanyaan mengenai proses publikasi, silakan hubungi:
                </p>
                <ul class="list-unstyled text-sm">
                    <li><i class="fas fa-envelope"></i> Email: admin@stkyakobus.ac.id</li>
                    <li><i class="fas fa-phone"></i> Telp: (0971) 321195</li>
                    <li><i class="fas fa-clock"></i> Senin-Jumat 08:00-16:00</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for Timeline -->
<style>
.timeline {
    position: relative;
    margin: 0 0 30px 0;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50px;
    width: 4px;
    background: linear-gradient(to bottom, #007bff, #28a745);
    border-radius: 2px;
}

.timeline > div {
    position: relative;
    margin-right: 10px;
    margin-bottom: 25px;
}

.timeline > div > .timeline-item {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    margin-top: 0;
    background: #fff;
    margin-left: 70px;
    margin-right: 15px;
    padding: 0;
    position: relative;
    border-left: 3px solid #007bff;
}

.timeline > div > .timeline-item > .time {
    color: #999;
    float: right;
    padding: 10px;
    font-size: 12px;
    font-weight: 600;
}

.timeline > div > .timeline-item > .timeline-header {
    margin: 0;
    color: #333;
    border-bottom: 1px solid #f4f4f4;
    padding: 15px;
    font-weight: 700;
    font-size: 16px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.timeline > div > .timeline-item > .timeline-body {
    padding: 15px;
}

.timeline > div > .fa,
.timeline > div > .fas,
.timeline > div > .far {
    width: 35px;
    height: 35px;
    font-size: 16px;
    line-height: 35px;
    position: absolute;
    color: #666;
    background: #f4f4f4;
    border-radius: 50%;
    text-align: center;
    left: 33px;
    top: 0;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.time-label > span {
    font-weight: 700;
    color: #fff;
    border-radius: 6px;
    padding: 8px 15px;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Progress bar enhancements */
.progress {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
}

.progress-bar {
    font-weight: 600;
    font-size: 14px;
}

/* Alert styling */
.alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd, #ffeaa7);
    color: #856404;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    color: #0c5460;
}

.alert-secondary {
    background: linear-gradient(135deg, #e2e3e5, #d6d8db);
    color: #383d41;
}

/* Card enhancements */
.card {
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-bottom: none;
}

.card-header .card-title {
    color: white !important;
    font-weight: 600;
}

/* Button styling */
.btn {
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Badge styling */
.badge {
    font-size: 0.75em;
    padding: 0.4em 0.8em;
    border-radius: 12px;
}

/* Print styles */
@media print {
    .card-header,
    .btn,
    .time-label {
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .timeline:before {
        background: #000 !important;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .timeline {
        margin-left: 0;
    }
    
    .timeline:before {
        left: 20px;
    }
    
    .timeline > div > .timeline-item {
        margin-left: 40px;
    }
    
    .timeline > div > .fa,
    .timeline > div > .fas,
    .timeline > div > .far {
        left: 5px;
    }
    
    .btn-group-vertical .btn {
        margin-bottom: 5px;
    }
}
</style>

<script>
$(document).ready(function() {
    // Animation untuk timeline items
    $('.timeline-item').each(function(index) {
        $(this).delay(index * 200).fadeIn(600);
    });
    
    // Smooth scroll ke step saat ini
    const currentStep = <?= $step_current ?>;
    if (currentStep > 1) {
        setTimeout(function() {
            $('html, body').animate({
                scrollTop: $('.time-label:contains("STEP ' + currentStep + '")').offset().top - 100
            }, 1000);
        }, 1000);
    }
    
    // Auto refresh setiap 30 detik jika masih dalam proses
    <?php if (!in_array($publikasi->status, ['completed', 'rejected'])): ?>
    setInterval(function() {
        // Auto refresh halaman untuk update status terbaru
        // location.reload(); // Uncomment jika ingin auto refresh
    }, 30000);
    <?php endif; ?>
    
    // Tooltip untuk semua elemen dengan data-toggle
    $('[data-toggle="tooltip"]').tooltip();
});
</script>