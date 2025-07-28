<?php
// =================================================================
// File: application/views/mahasiswa/seminar_proposal/no_proposal.php
// =================================================================
?>

<!-- No Proposal Available -->
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Seminar Proposal</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>"><i class="ni ni-tv-2"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Seminar Proposal</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 col-5 text-right">
                    <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-sm btn-neutral">
                        <i class="ni ni-tv-2"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--7">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <!-- Icon -->
                    <div class="icon icon-shape icon-shape-warning rounded-circle mb-4 mx-auto" style="width: 80px; height: 80px;">
                        <i class="ni ni-notification-70" style="font-size: 2.5rem;"></i>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="mb-3">Belum Ada Proposal Aktif</h2>
                    
                    <!-- Description -->
                    <p class="text-muted mb-4">
                        Untuk dapat mengajukan seminar proposal, Anda perlu memiliki proposal yang sudah disetujui oleh dosen pembimbing.<br>
                        Silakan lengkapi tahap-tahap berikut terlebih dahulu:
                    </p>
                    
                    <!-- Steps -->
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card border-light">
                                <div class="card-body">
                                    <div class="icon icon-shape bg-primary text-white rounded-circle mb-3 mx-auto">
                                        <i class="ni ni-send"></i>
                                    </div>
                                    <h5>1. Ajukan Proposal</h5>
                                    <p class="text-sm text-muted">
                                        Buat dan ajukan proposal skripsi Anda
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card border-light">
                                <div class="card-body">
                                    <div class="icon icon-shape bg-success text-white rounded-circle mb-3 mx-auto">
                                        <i class="ni ni-check-bold"></i>
                                    </div>
                                    <h5>2. Persetujuan Pembimbing</h5>
                                    <p class="text-sm text-muted">
                                        Tunggu hingga proposal disetujui pembimbing
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-4">
                            <div class="card border-light">
                                <div class="card-body">
                                    <div class="icon icon-shape bg-info text-white rounded-circle mb-3 mx-auto">
                                        <i class="ni ni-books"></i>
                                    </div>
                                    <h5>3. Bimbingan</h5>
                                    <p class="text-sm text-muted">
                                        Lakukan minimal 8 sesi bimbingan yang tervalidasi
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-4">
                        <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary btn-lg mr-3">
                            <i class="ni ni-send"></i> Buat Proposal
                        </a>
                        <a href="<?= base_url('mahasiswa/dashboard') ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="ni ni-tv-2"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Info Card -->
            <div class="card shadow mt-4">
                <div class="card-header border-0">
                    <h3 class="mb-0">
                        <i class="ni ni-notification-70 text-primary"></i>
                        Informasi Tahapan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-one-side" data-timeline-content="axis" data-timeline-axis-style="dashed">
                        <div class="timeline-block">
                            <span class="timeline-step badge-light">
                                <i class="ni ni-send"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm mb-1 text-muted">Tahap 1: Usulan Proposal</h6>
                                <p class="text-sm text-muted mb-0">
                                    Buat dan ajukan proposal penelitian Anda. Tunggu persetujuan dari Kaprodi dan penunjukan dosen pembimbing.
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-block">
                            <span class="timeline-step badge-light">
                                <i class="ni ni-books"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm mb-1 text-muted">Tahap 2: Bimbingan Proposal</h6>
                                <p class="text-sm text-muted mb-0">
                                    Lakukan bimbingan dengan dosen pembimbing. Minimal 8 jurnal bimbingan harus divalidasi untuk dapat melanjutkan ke seminar proposal.
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-block">
                            <span class="timeline-step badge-warning">
                                <i class="ni ni-hat-3"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm mb-1 text-warning">Tahap 3: Seminar Proposal</h6>
                                <p class="text-sm text-muted mb-0">
                                    <strong>Tahap saat ini.</strong> Ajukan seminar proposal setelah memenuhi syarat bimbingan. Proposal akan direview oleh pembimbing dan Kaprodi.
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-block">
                            <span class="timeline-step badge-light">
                                <i class="ni ni-collection"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm mb-1 text-muted">Tahap 4: Penelitian</h6>
                                <p class="text-sm text-muted mb-0">
                                    Setelah seminar proposal lulus, lakukan penelitian sesuai proposal yang telah disetujui.
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-block">
                            <span class="timeline-step badge-light">
                                <i class="ni ni-trophy"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm mb-1 text-muted">Tahap 5: Seminar Akhir & Publikasi</h6>
                                <p class="text-sm text-muted mb-0">
                                    Ajukan seminar akhir dan publikasi tugas akhir setelah penelitian selesai.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>