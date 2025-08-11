<?php
/**
 * Staf Seminar Proposal Detail View - SIM TA STK Santo Yakobus
 * 
 * View untuk menampilkan detail lengkap seminar proposal dari perspektif staf akademik
 * Menampilkan identitas mahasiswa, detail proposal, jadwal, dewan penguji, dan aksi administrasi
 * 
 * File: application/views/staf/seminar_proposal/detail.php
 * Controller: staf/Seminar_proposal::detail()
 * 
 * Features:
 * - Detail identitas mahasiswa dan proposal
 * - Detail tempat dan jadwal pelaksanaan
 * - Detail dewan penguji lengkap
 * - Download/print semua dokumen administrasi
 * - Input penilaian (jika belum ada)
 * - Status penilaian existing
 * 
 * @package     SIM_TA
 * @subpackage  Views/Staf
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 */
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-eye mr-2 text-primary"></i>
                    Detail Seminar Proposal
                </h1>
                <p class="text-muted">Administrasi dan Monitoring: <?= $seminar->nama_mahasiswa ?></p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/seminar_proposal') ?>">Seminar Proposal</a></li>
                    <li class="breadcrumb-item active">Detail</li>
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

        <!-- Back Button dan Quick Actions -->
        <div class="row mb-3">
            <div class="col-md-6">
                <a href="<?= base_url('staf/seminar_proposal') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Daftar
                </a>
            </div>
            <div class="col-md-6 text-right">
                <!-- Quick Download Buttons -->
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-download mr-2"></i>
                        Download Dokumen
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_form_permohonan/' . $seminar->id) ?>" target="_blank">
                            <i class="fas fa-file-alt mr-2 text-primary"></i>
                            Form Permohonan
                        </a>
                        <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_undangan/' . $seminar->id) ?>" target="_blank">
                            <i class="fas fa-envelope mr-2 text-success"></i>
                            Undangan Seminar
                        </a>
                        <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_berita_acara/' . $seminar->id) ?>" target="_blank">
                            <i class="fas fa-clipboard mr-2 text-warning"></i>
                            Berita Acara
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->id . '/all') ?>" target="_blank">
                            <i class="fas fa-star mr-2 text-danger"></i>
                            Form Penilaian (Semua)
                        </a>
                        <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_rekapitulasi_nilai/' . $seminar->id) ?>" target="_blank">
                            <i class="fas fa-chart-bar mr-2 text-purple"></i>
                            Rekapitulasi Nilai
                        </a>
                    </div>
                </div>
                
                <?php if(empty($existing_penilaian)): ?>
                    <a href="<?= base_url('staf/seminar_proposal/input_penilaian/' . $seminar->id) ?>" class="btn btn-success">
                        <i class="fas fa-edit mr-2"></i>
                        Input Penilaian
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('staf/seminar_proposal/input_penilaian/' . $seminar->id) ?>" class="btn btn-warning">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Penilaian
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Detail Mahasiswa & Proposal -->
            <div class="col-md-8">
                
                <!-- Card: Identitas Mahasiswa -->
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title">
                            <i class="fas fa-user mr-2"></i>
                            Identitas Mahasiswa
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>NIM</strong></td>
                                        <td width="5%">:</td>
                                        <td><?= $seminar->nim ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama Lengkap</strong></td>
                                        <td>:</td>
                                        <td><?= $seminar->nama_mahasiswa ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Program Studi</strong></td>
                                        <td>:</td>
                                        <td>
                                            <span class="badge badge-secondary"><?= $seminar->nama_prodi ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>:</td>
                                        <td>
                                            <a href="mailto:<?= $seminar->email_mahasiswa ?>">
                                                <i class="fas fa-envelope mr-1"></i>
                                                <?= $seminar->email_mahasiswa ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>No. Telepon</strong></td>
                                        <td width="5%">:</td>
                                        <td>
                                            <?php if($seminar->nomor_telepon): ?>
                                                <a href="tel:<?= $seminar->nomor_telepon ?>">
                                                    <i class="fas fa-phone mr-1"></i>
                                                    <?= $seminar->nomor_telepon ?>
                                                </a>
                                            <?php else: ?>
                                                <em class="text-muted">Tidak tersedia</em>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tempat Lahir</strong></td>
                                        <td>:</td>
                                        <td><?= $seminar->tempat_lahir ?: '<em class="text-muted">Tidak tersedia</em>' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Lahir</strong></td>
                                        <td>:</td>
                                        <td>
                                            <?= $seminar->tanggal_lahir ? date('d F Y', strtotime($seminar->tanggal_lahir)) : '<em class="text-muted">Tidak tersedia</em>' ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dosen Pembimbing</strong></td>
                                        <td>:</td>
                                        <td>
                                            <strong><?= $seminar->nama_pembimbing ?></strong>
                                            <br>
                                            <small class="text-muted">NIP: <?= $seminar->nip_pembimbing ?></small>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Detail Proposal -->
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt mr-2"></i>
                            Detail Proposal Penelitian
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Judul Proposal:</label>
                            <p class="bg-light p-3 rounded"><?= $seminar->judul ?></p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Jenis Penelitian:</label>
                                    <p>
                                        <span class="badge badge-info badge-lg">
                                            <?= $seminar->jenis_penelitian ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold">Lokasi Penelitian:</label>
                                    <p>
                                        <i class="fas fa-map-marker-alt mr-2 text-danger"></i>
                                        <?= $seminar->lokasi_penelitian ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Ringkasan Proposal:</label>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br($seminar->ringkasan) ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Uraian Masalah:</label>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br($seminar->uraian_masalah) ?>
                            </div>
                        </div>
                        
                        <?php if($seminar->file_draft_proposal): ?>
                            <div class="form-group">
                                <label class="font-weight-bold">File Proposal:</label>
                                <p>
                                    <a href="<?= base_url('uploads/proposal/' . $seminar->file_draft_proposal) ?>" 
                                       target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-download mr-2"></i>
                                        Download File Proposal
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card: Detail Dewan Penguji -->
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-2"></i>
                            Detail Dewan Penguji
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Dosen Pembimbing -->
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-header text-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-user-tie mr-2 text-primary"></i>
                                            Dosen Pembimbing
                                        </h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <h6 class="font-weight-bold"><?= $dewan_penguji->nama_pembimbing ?></h6>
                                        <p class="text-muted mb-1">NIP: <?= $dewan_penguji->nip_pembimbing ?></p>
                                        <p class="text-muted">
                                            <a href="mailto:<?= $dewan_penguji->email_pembimbing ?>">
                                                <i class="fas fa-envelope mr-1"></i>
                                                <?= $dewan_penguji->email_pembimbing ?>
                                            </a>
                                        </p>
                                        <a href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->id . '/pembimbing') ?>" 
                                           target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fas fa-download mr-1"></i>
                                            Form Penilaian
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dosen Penguji 1 -->
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-header text-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-user mr-2 text-success"></i>
                                            Dosen Penguji I
                                        </h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if($dewan_penguji->nama_penguji1): ?>
                                            <h6 class="font-weight-bold"><?= $dewan_penguji->nama_penguji1 ?></h6>
                                            <p class="text-muted mb-1">NIP: <?= $dewan_penguji->nip_penguji1 ?></p>
                                            <p class="text-muted">
                                                <a href="mailto:<?= $dewan_penguji->email_penguji1 ?>">
                                                    <i class="fas fa-envelope mr-1"></i>
                                                    <?= $dewan_penguji->email_penguji1 ?>
                                                </a>
                                            </p>
                                            <a href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->id . '/penguji1') ?>" 
                                               target="_blank" class="btn btn-success btn-sm">
                                                <i class="fas fa-download mr-1"></i>
                                                Form Penilaian
                                            </a>
                                        <?php else: ?>
                                            <p class="text-muted">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Belum ditetapkan
                                            </p>
                                            <small class="text-danger">Hubungi Kaprodi untuk penetapan dosen penguji</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dosen Penguji 2 -->
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-header text-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-user mr-2 text-info"></i>
                                            Dosen Penguji II
                                        </h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if($dewan_penguji->nama_penguji2): ?>
                                            <h6 class="font-weight-bold"><?= $dewan_penguji->nama_penguji2 ?></h6>
                                            <p class="text-muted mb-1">NIP: <?= $dewan_penguji->nip_penguji2 ?></p>
                                            <p class="text-muted">
                                                <a href="mailto:<?= $dewan_penguji->email_penguji2 ?>">
                                                    <i class="fas fa-envelope mr-1"></i>
                                                    <?= $dewan_penguji->email_penguji2 ?>
                                                </a>
                                            </p>
                                            <a href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->id . '/penguji2') ?>" 
                                               target="_blank" class="btn btn-info btn-sm">
                                                <i class="fas fa-download mr-1"></i>
                                                Form Penilaian
                                            </a>
                                        <?php else: ?>
                                            <p class="text-muted">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Belum ditetapkan
                                            </p>
                                            <small class="text-danger">Hubungi Kaprodi untuk penetapan dosen penguji</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Jadwal & Status -->
            <div class="col-md-4">
                
                <!-- Card: Jadwal Seminar -->
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Jadwal Seminar
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="35%"><i class="fas fa-calendar text-primary mr-2"></i><strong>Tanggal</strong></td>
                                <td width="5%">:</td>
                                <td>
                                    <strong class="text-primary">
                                        <?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?>
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= date('l', strtotime($seminar->tanggal_seminar)) ?>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-clock text-warning mr-2"></i><strong>Waktu</strong></td>
                                <td>:</td>
                                <td>
                                    <strong class="text-warning">
                                        <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-door-open text-info mr-2"></i><strong>Tempat</strong></td>
                                <td>:</td>
                                <td>
                                    <strong class="text-info">
                                        <?= $seminar->tempat_seminar ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Countdown -->
                        <?php 
                        $seminar_datetime = $seminar->tanggal_seminar . ' ' . $seminar->jam_seminar;
                        $now = time();
                        $seminar_time = strtotime($seminar_datetime);
                        $diff = $seminar_time - $now;
                        ?>
                        
                        <hr>
                        <div class="text-center">
                            <?php if($diff > 0): ?>
                                <p class="mb-1"><strong>Waktu Tersisa:</strong></p>
                                <div id="countdown" class="text-primary font-weight-bold" 
                                     data-target="<?= $seminar_datetime ?>">
                                    <span id="days">0</span> hari 
                                    <span id="hours">0</span> jam 
                                    <span id="minutes">0</span> menit
                                </div>
                            <?php elseif($diff > -3600): ?>
                                <span class="badge badge-warning badge-lg">
                                    <i class="fas fa-clock mr-1"></i>
                                    Sedang Berlangsung
                                </span>
                            <?php else: ?>
                                <span class="badge badge-secondary badge-lg">
                                    <i class="fas fa-check mr-1"></i>
                                    Telah Selesai
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Card: Status Penilaian -->
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            Status Penilaian
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if($existing_penilaian): ?>
                            <!-- Penilaian Sudah Ada -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Penilaian Telah Diinput</strong>
                                <br>
                                <small>
                                    Tanggal: <?= date('d F Y H:i', strtotime($existing_penilaian->created_at)) ?>
                                </small>
                            </div>
                            
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nilai Substansi</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-primary"><?= number_format($existing_penilaian->rata_rata_substansi, 1) ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Nilai Presentasi</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-success"><?= number_format($existing_penilaian->rata_rata_presentasi, 1) ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Nilai Diskusi</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-info"><?= number_format($existing_penilaian->rata_rata_diskusi, 1) ?></span></td>
                                </tr>
                                <tr class="bg-light">
                                    <td><strong>Nilai Akhir</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-warning badge-lg"><?= number_format($existing_penilaian->nilai_akhir, 1) ?></span></td>
                                </tr>
                            </table>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Rekomendasi:</label>
                                <p>
                                    <?php
                                    $rekomendasi_labels = [
                                        'diterima_tanpa_revisi' => '<span class="badge badge-success">Diterima Tanpa Revisi</span>',
                                        'diterima_revisi_minor' => '<span class="badge badge-warning">Diterima dengan Revisi Minor</span>',
                                        'diterima_revisi_mayor' => '<span class="badge badge-danger">Diterima dengan Revisi Mayor</span>',
                                        'ditolak_mengulang' => '<span class="badge badge-dark">Ditolak / Mengulang Seminar</span>'
                                    ];
                                    echo $rekomendasi_labels[$existing_penilaian->rekomendasi] ?? $existing_penilaian->rekomendasi;
                                    ?>
                                </p>
                            </div>
                            
                            <?php if($existing_penilaian->catatan_saran): ?>
                                <div class="form-group">
                                    <label class="font-weight-bold">Catatan/Saran:</label>
                                    <div class="bg-light p-2 rounded text-sm">
                                        <?= nl2br($existing_penilaian->catatan_saran) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-center mt-3">
                                <a href="<?= base_url('staf/seminar_proposal/input_penilaian/' . $seminar->id) ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-edit mr-2"></i>
                                    Edit Penilaian
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Belum Ada Penilaian -->
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Penilaian Belum Diinput</strong>
                                <br>
                                <small>Staf dapat memberikan penilaian sebagai backup/second opinion</small>
                            </div>
                            
                            <div class="text-center">
                                <a href="<?= base_url('staf/seminar_proposal/input_penilaian/' . $seminar->id) ?>" 
                                   class="btn btn-success btn-lg">
                                    <i class="fas fa-edit mr-2"></i>
                                    Input Penilaian
                                </a>
                            </div>
                            
                            <hr>
                            <p class="text-muted text-sm">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Info:</strong> Staf dan dosen pembimbing memiliki hak akses yang sama untuk input penilaian selama belum dipublikasikan.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card: Download Dokumen -->
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title">
                            <i class="fas fa-download mr-2"></i>
                            Download Dokumen
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="<?= base_url('staf/seminar_proposal/download_form_permohonan/' . $seminar->id) ?>" 
                               target="_blank" class="list-group-item list-group-item-action">
                                <i class="fas fa-file-alt mr-2 text-primary"></i>
                                Form Permohonan Seminar
                                <i class="fas fa-external-link-alt float-right mt-1"></i>
                            </a>
                            
                            <a href="<?= base_url('staf/seminar_proposal/download_undangan/' . $seminar->id) ?>" 
                               target="_blank" class="list-group-item list-group-item-action">
                                <i class="fas fa-envelope mr-2 text-success"></i>
                                Undangan untuk Penguji
                                <i class="fas fa-external-link-alt float-right mt-1"></i>
                            </a>
                            
                            <a href="<?= base_url('staf/seminar_proposal/download_berita_acara/' . $seminar->id) ?>" 
                               target="_blank" class="list-group-item list-group-item-action">
                                <i class="fas fa-clipboard mr-2 text-warning"></i>
                                Berita Acara Seminar
                                <i class="fas fa-external-link-alt float-right mt-1"></i>
                            </a>
                            
                            <a href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->id . '/all') ?>" 
                               target="_blank" class="list-group-item list-group-item-action">
                                <i class="fas fa-star mr-2 text-danger"></i>
                                Form Penilaian (Semua)
                                <i class="fas fa-external-link-alt float-right mt-1"></i>
                            </a>
                            
                            <a href="<?= base_url('staf/seminar_proposal/download_rekapitulasi_nilai/' . $seminar->id) ?>" 
                               target="_blank" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-bar mr-2 text-purple"></i>
                                Rekapitulasi Nilai Akhir
                                <i class="fas fa-external-link-alt float-right mt-1"></i>
                            </a>
                        </div>
                        
                        <hr>
                        <p class="text-muted text-sm mb-0">
                            <i class="fas fa-lightbulb mr-1"></i>
                            <strong>Tips:</strong> Dokumen akan terbuka di tab baru. Pastikan pop-up blocker tidak aktif.
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- Additional CSS -->
<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.card .card-body table td {
    padding: 0.5rem 0.25rem;
    border: none;
}

#countdown {
    font-size: 1.2rem;
}

.text-purple {
    color: #6f42c1 !important;
}
</style>