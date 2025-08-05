<?php
/**
 * =======================================================
 * VIEWS MAHASISWA - PUBLIKASI TUGAS AKHIR (SIMPLE & CLEAN)
 * =======================================================
 * 
 * 1. Dashboard - index.php
 * 2. Form Pengajuan/Edit - form.php  
 * 3. Progress Tracking - tracking.php
 */

// =======================================================
// 1. DASHBOARD MAHASISWA
// File: application/views/mahasiswa/publikasi/index.php
// =======================================================
?>

<!-- DASHBOARD PUBLIKASI MAHASISWA -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= $title ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Publikasi Tugas Akhir</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Flash Messages -->
            <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-check"></i> <?= $this->session->flashdata('success') ?>
            </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-ban"></i> <?= $this->session->flashdata('error') ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- Status Card -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Status Publikasi Tugas Akhir</h3>
                        </div>
                        <div class="card-body">
                            
                            <?php if (!$proposal): ?>
                                <!-- Belum Ada Proposal -->
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> Informasi</h5>
                                    Anda belum memiliki proposal yang disetujui. Silakan ajukan proposal terlebih dahulu untuk dapat mengajukan publikasi.
                                </div>
                                <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Ajukan Proposal
                                </a>
                                
                            <?php elseif (!$eligible): ?>
                                <!-- Belum Memenuhi Syarat -->
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Belum Memenuhi Syarat</h5>
                                    <p><?= $syarat_status ?></p>
                                    <small class="text-muted">
                                        Anda memerlukan minimal 16 jurnal bimbingan yang sudah divalidasi dosen pembimbing.
                                    </small>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning"><i class="fas fa-book"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Jurnal Bimbingan</span>
                                                <span class="info-box-number"><?= $jurnal_count ?>/16 Tervalidasi</span>
                                                <div class="progress">
                                                    <div class="progress-bar bg-warning" style="width: <?= ($jurnal_count/16)*100 ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="<?= base_url('mahasiswa/jurnal_bimbingan') ?>" class="btn btn-warning btn-block">
                                            <i class="fas fa-plus"></i> Tambah Jurnal Bimbingan
                                        </a>
                                    </div>
                                </div>
                                
                            <?php elseif (!$publikasi): ?>
                                <!-- Eligible, Belum Ada Pengajuan -->
                                <div class="alert alert-success">
                                    <h5><i class="icon fas fa-check"></i> Memenuhi Syarat!</h5>
                                    <p>Selamat! Anda sudah memenuhi syarat untuk mengajukan publikasi tugas akhir.</p>
                                    <ul class="mb-0">
                                        <li>✅ Jurnal bimbingan: <?= $jurnal_count ?>/16 tervalidasi</li>
                                        <li>✅ Proposal telah disetujui</li>
                                    </ul>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="<?= base_url('mahasiswa/publikasi/ajukan/' . $proposal->id) ?>" class="btn btn-success btn-lg">
                                        <i class="fas fa-upload"></i> Ajukan Publikasi Sekarang
                                    </a>
                                </div>
                                
                            <?php else: ?>
                                <!-- Sudah Ada Pengajuan -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>Status Pengajuan Publikasi</h5>
                                        
                                        <!-- Progress Bar -->
                                        <?php 
                                        $progress = 20;
                                        $status_text = 'Draft';
                                        $status_class = 'secondary';
                                        
                                        switch($publikasi->status) {
                                            case 'draft':
                                                $progress = 20;
                                                $status_text = 'Draft';
                                                $status_class = 'secondary';
                                                break;
                                            case 'submitted':
                                                $progress = 40;
                                                $status_text = 'Menunggu Review Dosen';
                                                $status_class = 'primary';
                                                break;
                                            case 'review_pembimbing':
                                                $progress = 60;
                                                $status_text = 'Sedang Direview Dosen';
                                                $status_class = 'info';
                                                break;
                                            case 'review_staf':
                                                $progress = 80;
                                                $status_text = 'Menunggu Validasi Staf';
                                                $status_class = 'warning';
                                                break;
                                            case 'completed':
                                                $progress = 100;
                                                $status_text = 'Publikasi Selesai';
                                                $status_class = 'success';
                                                break;
                                            case 'rejected':
                                                $progress = 0;
                                                $status_text = 'Ditolak - Perlu Perbaikan';
                                                $status_class = 'danger';
                                                break;
                                        }
                                        ?>
                                        
                                        <div class="progress mb-3" style="height: 25px;">
                                            <div class="progress-bar bg-<?= $status_class ?>" role="progressbar" 
                                                 style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?= $progress ?>%
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-<?= $status_class ?>">
                                            <h6><i class="fas fa-info-circle"></i> Status: <?= $status_text ?></h6>
                                            
                                            <?php if ($publikasi->status === 'draft'): ?>
                                                <p>Pengajuan masih dalam bentuk draft. Silakan lengkapi dan submit pengajuan Anda.</p>
                                                
                                            <?php elseif ($publikasi->status === 'submitted' || $publikasi->status === 'review_pembimbing'): ?>
                                                <p>Pengajuan Anda sedang menunggu review dari dosen pembimbing.</p>
                                                <small class="text-muted">Disubmit pada: <?= date('d F Y H:i', strtotime($publikasi->tanggal_pengajuan)) ?></small>
                                                
                                            <?php elseif ($publikasi->status === 'review_staf'): ?>
                                                <p>Pengajuan telah disetujui dosen pembimbing dan sedang menunggu validasi staf.</p>
                                                <?php if ($publikasi->komentar_pembimbing): ?>
                                                    <p><strong>Komentar Dosen:</strong> <?= $publikasi->komentar_pembimbing ?></p>
                                                <?php endif; ?>
                                                
                                            <?php elseif ($publikasi->status === 'completed'): ?>
                                                <p>🎉 Selamat! Publikasi tugas akhir Anda telah selesai diproses.</p>
                                                <?php if ($publikasi->link_repository): ?>
                                                    <p><strong>Link Repository:</strong> <a href="<?= $publikasi->link_repository ?>" target="_blank"><?= $publikasi->link_repository ?></a></p>
                                                <?php endif; ?>
                                                
                                            <?php elseif ($publikasi->status === 'rejected'): ?>
                                                <p>Pengajuan ditolak dan perlu diperbaiki. Silakan perbaiki sesuai komentar dan ajukan kembali.</p>
                                                <?php if ($publikasi->komentar_pembimbing): ?>
                                                    <p><strong>Komentar Dosen:</strong> <?= $publikasi->komentar_pembimbing ?></p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="mt-3">
                                            <?php if ($publikasi->status === 'draft'): ?>
                                                <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning">
                                                    <i class="fas fa-edit"></i> Edit Pengajuan
                                                </a>
                                                <a href="<?= base_url('mahasiswa/publikasi/submit/' . $publikasi->id) ?>" 
                                                   class="btn btn-primary" onclick="return confirm('Yakin ingin submit pengajuan?')">
                                                    <i class="fas fa-paper-plane"></i> Submit Pengajuan
                                                </a>
                                                
                                            <?php elseif ($publikasi->status === 'rejected'): ?>
                                                <a href="<?= base_url('mahasiswa/publikasi/edit/' . $publikasi->id) ?>" class="btn btn-warning">
                                                    <i class="fas fa-edit"></i> Perbaiki Pengajuan
                                                </a>
                                                
                                            <?php elseif ($publikasi->status === 'completed'): ?>
                                                <a href="<?= base_url('mahasiswa/publikasi/download_surat/' . $publikasi->id) ?>" 
                                                   class="btn btn-success" target="_blank">
                                                    <i class="fas fa-download"></i> Download Surat Keterangan
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="<?= base_url('mahasiswa/publikasi/tracking/' . $publikasi->id) ?>" class="btn btn-info">
                                                <i class="fas fa-timeline"></i> Lihat Timeline
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Info Sidebar -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi</h3>
                        </div>
                        <div class="card-body">
                            <h6><strong>Syarat Publikasi:</strong></h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Minimal 16 jurnal bimbingan tervalidasi</li>
                                <li><i class="fas fa-check text-success"></i> Proposal telah disetujui</li>
                            </ul>
                            
                            <h6 class="mt-3"><strong>Dokumen yang Diperlukan:</strong></h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-file-pdf text-danger"></i> Surat Keterangan Revisi (PDF, max 1MB)</li>
                                <li><i class="fas fa-file-pdf text-danger"></i> File Skripsi Final (PDF, max 5MB)</li>
                                <li><i class="fas fa-file-pdf text-danger"></i> Surat Perpustakaan (PDF, max 1MB)</li>
                            </ul>
                            
                            <h6 class="mt-3"><strong>Alur Proses:</strong></h6>
                            <ol class="list-unstyled">
                                <li><span class="badge badge-secondary">1</span> Mahasiswa mengajukan</li>
                                <li><span class="badge badge-primary">2</span> Review dosen pembimbing</li>
                                <li><span class="badge badge-warning">3</span> Validasi staf</li>
                                <li><span class="badge badge-success">4</span> Publikasi selesai</li>
                            </ol>
                        </div>
                    </div>
                    
                    <?php if ($proposal): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-graduate"></i> Data Mahasiswa</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nama</strong></td>
                                    <td><?= $proposal->nama_mahasiswa ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIM</strong></td>
                                    <td><?= $proposal->nim ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Program Studi</strong></td>
                                    <td><?= $proposal->nama_prodi ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Dosen Pembimbing</strong></td>
                                    <td><?= $proposal->nama_pembimbing ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
