<?php
/**
 * Staf Seminar Proposal Detail View - READ-ONLY PENILAIAN VERSION
 * 
 * PERUBAHAN:
 * - Tombol "Input Penilaian" diganti "Lihat Penilaian"
 * - Staf hanya bisa view penilaian yang diinput dosen
 * - Ditambah info alert tentang akses read-only
 * 
 * File: application/views/staf/seminar_proposal/detail.php
 * Controller: staf/Seminar_proposal::detail()
 * 
 * @package     SIM_TA
 * @subpackage  Views/Staf
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 */

// ✅ FIXED: Helper functions untuk safe property access
function safe_get($object, $property, $default = 'Tidak tersedia') {
    return isset($object->$property) && !empty($object->$property) ? $object->$property : $default;
}

function safe_email($object, $property, $default = 'Tidak tersedia') {
    $email = safe_get($object, $property, $default);
    return ($email != $default && filter_var($email, FILTER_VALIDATE_EMAIL)) ? $email : $default;
}

function safe_date($date, $format = 'd F Y', $default = 'Tidak tersedia') {
    if (empty($date) || $date == '0000-00-00' || $date == null) {
        return $default;
    }
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return $default;
    }
}

function safe_number($number, $decimals = 1, $default = 'Belum dinilai') {
    return (is_numeric($number) && $number > 0) ? number_format($number, $decimals) : $default;
}
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
                <p class="text-muted">Administrasi dan Monitoring: <?= safe_get($seminar, 'nama_mahasiswa', 'Mahasiswa') ?></p>
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

        <!-- ✅ PERUBAHAN: Info alert untuk akses read-only -->
        <?php if($this->session->flashdata('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle mr-2"></i>
                <?= $this->session->flashdata('info') ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- ✅ PERUBAHAN: Alert tentang akses read-only untuk staf -->
        <div class="alert alert-light border-left-info">
            <i class="fas fa-shield-alt text-info mr-2"></i>
            <strong>Akses Staf:</strong> Anda memiliki akses <strong>read-only</strong> untuk penilaian seminar proposal. 
            Hanya dosen yang dapat melakukan input dan edit penilaian.
        </div>

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
                
                <!-- ✅ PERUBAHAN: Tombol "Lihat Penilaian" bukan "Input Penilaian" -->
                <a href="<?= base_url('staf/seminar_proposal/lihat_penilaian/' . $seminar->id) ?>" class="btn btn-info">
                    <i class="fas fa-eye mr-2"></i>
                    Lihat Penilaian
                </a>
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
                                        <td><?= safe_get($seminar, 'nim') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama Lengkap</strong></td>
                                        <td>:</td>
                                        <td><?= safe_get($seminar, 'nama_mahasiswa') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Program Studi</strong></td>
                                        <td>:</td>
                                        <td>
                                            <span class="badge badge-secondary"><?= safe_get($seminar, 'nama_prodi') ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>:</td>
                                        <td>
                                            <?php $email = safe_email($seminar, 'email_mahasiswa'); ?>
                                            <?php if($email != 'Tidak tersedia'): ?>
                                                <a href="mailto:<?= $email ?>">
                                                    <i class="fas fa-envelope mr-1"></i>
                                                    <?= $email ?>
                                                </a>
                                            <?php else: ?>
                                                <em class="text-muted"><?= $email ?></em>
                                            <?php endif; ?>
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
                                            <?php $phone = safe_get($seminar, 'nomor_telepon'); ?>
                                            <?php if($phone != 'Tidak tersedia'): ?>
                                                <a href="tel:<?= $phone ?>">
                                                    <i class="fas fa-phone mr-1"></i>
                                                    <?= $phone ?>
                                                </a>
                                            <?php else: ?>
                                                <em class="text-muted"><?= $phone ?></em>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tempat Lahir</strong></td>
                                        <td>:</td>
                                        <td><?= safe_get($seminar, 'tempat_lahir') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Lahir</strong></td>
                                        <td>:</td>
                                        <td><?= safe_date(safe_get($seminar, 'tanggal_lahir', null)) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dosen Pembimbing</strong></td>
                                        <td>:</td>
                                        <td class="dosen-name-wrap">
                                            <strong><?= safe_get($seminar, 'nama_pembimbing') ?></strong>
                                            <br>
                                            <small class="text-muted">NIP: <?= safe_get($seminar, 'nip_pembimbing') ?></small>
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
                            <p class="bg-light p-3 rounded"><?= safe_get($seminar, 'judul') ?></p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Jenis Penelitian:</label>
                                    <p>
                                        <span class="badge badge-info badge-lg">
                                            <?= safe_get($seminar, 'jenis_penelitian') ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold">Lokasi Penelitian:</label>
                                    <p>
                                        <i class="fas fa-map-marker-alt mr-2 text-danger"></i>
                                        <?= safe_get($seminar, 'lokasi_penelitian') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Ringkasan Proposal:</label>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(safe_get($seminar, 'ringkasan')) ?>
                            </div>
                        </div>
                        
                        <?php $uraian = safe_get($seminar, 'uraian_masalah', null); ?>
                        <?php if($uraian && $uraian != 'Tidak tersedia'): ?>
                        <div class="form-group">
                            <label class="font-weight-bold">Uraian Masalah:</label>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br($uraian) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php $file_proposal = safe_get($seminar, 'file_draft_proposal', null); ?>
                        <?php if($file_proposal && $file_proposal != 'Tidak tersedia'): ?>
                            <div class="form-group">
                                <label class="font-weight-bold">File Proposal:</label>
                                <p>
                                    <a href="<?= base_url('uploads/proposal/' . $file_proposal) ?>" 
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
                                        <h6 class="font-weight-bold dosen-name-wrap"><?= safe_get($dewan_penguji, 'nama_pembimbing', safe_get($seminar, 'nama_pembimbing')) ?></h6>
                                        <p class="text-muted mb-1">NIP: <?= safe_get($dewan_penguji, 'nip_pembimbing', safe_get($seminar, 'nip_pembimbing')) ?></p>
                                        <p class="text-muted">
                                            <?php $email_pembimbing = safe_email($dewan_penguji, 'email_pembimbing', safe_email($seminar, 'email_pembimbing')); ?>
                                            <?php if($email_pembimbing != 'Tidak tersedia'): ?>
                                                <a href="mailto:<?= $email_pembimbing ?>">
                                                    <i class="fas fa-envelope mr-1"></i>
                                                    <?= $email_pembimbing ?>
                                                </a>
                                            <?php else: ?>
                                                <em class="text-muted"><?= $email_pembimbing ?></em>
                                            <?php endif; ?>
                                        </p>
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
                                        <?php $nama_penguji1 = safe_get($dewan_penguji, 'nama_penguji1', safe_get($seminar, 'nama_penguji1')); ?>
                                        <?php if($nama_penguji1 != 'Tidak tersedia'): ?>
                                            <h6 class="font-weight-bold dosen-name-wrap"><?= $nama_penguji1 ?></h6>
                                            <p class="text-muted mb-1">NIP: <?= safe_get($dewan_penguji, 'nip_penguji1', safe_get($seminar, 'nip_penguji1')) ?></p>
                                            <p class="text-muted">
                                                <?php $email_penguji1 = safe_email($dewan_penguji, 'email_penguji1', safe_email($seminar, 'email_penguji1')); ?>
                                                <?php if($email_penguji1 != 'Tidak tersedia'): ?>
                                                    <a href="mailto:<?= $email_penguji1 ?>">
                                                        <i class="fas fa-envelope mr-1"></i>
                                                        <?= $email_penguji1 ?>
                                                    </a>
                                                <?php else: ?>
                                                    <em class="text-muted"><?= $email_penguji1 ?></em>
                                                <?php endif; ?>
                                            </p>
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
                                        <?php $nama_penguji2 = safe_get($dewan_penguji, 'nama_penguji2', safe_get($seminar, 'nama_penguji2')); ?>
                                        <?php if($nama_penguji2 != 'Tidak tersedia'): ?>
                                            <h6 class="font-weight-bold dosen-name-wrap"><?= $nama_penguji2 ?></h6>
                                            <p class="text-muted mb-1">NIP: <?= safe_get($dewan_penguji, 'nip_penguji2', safe_get($seminar, 'nip_penguji2')) ?></p>
                                            <p class="text-muted">
                                                <?php $email_penguji2 = safe_email($dewan_penguji, 'email_penguji2', safe_email($seminar, 'email_penguji2')); ?>
                                                <?php if($email_penguji2 != 'Tidak tersedia'): ?>
                                                    <a href="mailto:<?= $email_penguji2 ?>">
                                                        <i class="fas fa-envelope mr-1"></i>
                                                        <?= $email_penguji2 ?>
                                                    </a>
                                                <?php else: ?>
                                                    <em class="text-muted"><?= $email_penguji2 ?></em>
                                                <?php endif; ?>
                                            </p>
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
                                        <?= safe_date(safe_get($seminar, 'tanggal_seminar', null)) ?>
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php $tgl_seminar = safe_get($seminar, 'tanggal_seminar', null); ?>
                                        <?php if($tgl_seminar && $tgl_seminar != 'Tidak tersedia'): ?>
                                            <?= date('l', strtotime($tgl_seminar)) ?>
                                        <?php endif; ?>
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-clock text-warning mr-2"></i><strong>Waktu</strong></td>
                                <td>:</td>
                                <td>
                                    <?php $jam_seminar = safe_get($seminar, 'jam_seminar'); ?>
                                    <?php if($jam_seminar != 'Tidak tersedia'): ?>
                                        <strong class="text-warning">
                                            <?= date('H:i', strtotime($jam_seminar)) ?> WIT
                                        </strong>
                                    <?php else: ?>
                                        <em class="text-muted"><?= $jam_seminar ?></em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-door-open text-info mr-2"></i><strong>Tempat</strong></td>
                                <td>:</td>
                                <td>
                                    <strong class="text-info">
                                        <?= safe_get($seminar, 'tempat_seminar') ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Countdown -->
                        <?php 
                        $tgl_seminar = safe_get($seminar, 'tanggal_seminar', null);
                        $jam_seminar = safe_get($seminar, 'jam_seminar', null);
                        if($tgl_seminar != 'Tidak tersedia' && $jam_seminar != 'Tidak tersedia') {
                            $seminar_datetime = $tgl_seminar . ' ' . $jam_seminar;
                            $now = time();
                            $seminar_time = strtotime($seminar_datetime);
                            $diff = $seminar_time - $now;
                        } else {
                            $diff = null;
                        }
                        ?>
                        
                        <hr>
                        <div class="text-center">
                            <?php if($diff !== null): ?>
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
                            <?php else: ?>
                                <span class="badge badge-secondary">
                                    <i class="fas fa-calendar-times mr-1"></i>
                                    Jadwal Belum Ditetapkan
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
                        <!-- ✅ PERUBAHAN: Info tentang akses read-only -->
                        <div class="alert alert-info alert-sm">
                            <i class="fas fa-eye mr-2"></i>
                            <strong>Mode:</strong> Read-only untuk staf
                        </div>

                        <?php if(isset($existing_penilaian) && $existing_penilaian): ?>
                            <!-- Penilaian Sudah Ada -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Penilaian Telah Diinput Dosen</strong>
                                <br>
                                <small>
                                    Tanggal: <?= safe_date(safe_get($existing_penilaian, 'created_at', null), 'd F Y H:i') ?>
                                </small>
                            </div>
                            
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nilai Substansi</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-primary"><?= safe_number(safe_get($existing_penilaian, 'rata_rata_substansi', null)) ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Nilai Presentasi</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-success"><?= safe_number(safe_get($existing_penilaian, 'rata_rata_presentasi', null)) ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Nilai Diskusi</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-info"><?= safe_number(safe_get($existing_penilaian, 'rata_rata_diskusi', null)) ?></span></td>
                                </tr>
                                <tr class="bg-light">
                                    <td><strong>Nilai Akhir</strong></td>
                                    <td>:</td>
                                    <td><span class="badge badge-warning badge-lg"><?= safe_number(safe_get($existing_penilaian, 'nilai_akhir', null)) ?></span></td>
                                </tr>
                            </table>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Rekomendasi:</label>
                                <p>
                                    <?php
                                    $rekomendasi = safe_get($existing_penilaian, 'rekomendasi', null);
                                    $rekomendasi_labels = [
                                        'diterima_tanpa_revisi' => '<span class="badge badge-success">Diterima Tanpa Revisi</span>',
                                        'diterima_revisi_minor' => '<span class="badge badge-warning">Diterima dengan Revisi Minor</span>',
                                        'diterima_revisi_mayor' => '<span class="badge badge-danger">Diterima dengan Revisi Mayor</span>',
                                        'ditolak_mengulang' => '<span class="badge badge-dark">Ditolak / Mengulang Seminar</span>',
                                        'revisi_minor' => '<span class="badge badge-warning">Revisi Minor</span>',
                                        'revisi_mayor' => '<span class="badge badge-danger">Revisi Mayor</span>',
                                        'ditolak' => '<span class="badge badge-dark">Ditolak</span>'
                                    ];
                                    echo isset($rekomendasi_labels[$rekomendasi]) ? $rekomendasi_labels[$rekomendasi] : '<span class="badge badge-secondary">Belum Ditetapkan</span>';
                                    ?>
                                </p>
                            </div>
                            
                            <?php $catatan = safe_get($existing_penilaian, 'catatan_saran', null); ?>
                            <?php if($catatan && $catatan != 'Tidak tersedia'): ?>
                                <div class="form-group">
                                    <label class="font-weight-bold">Catatan/Saran:</label>
                                    <div class="bg-light p-2 rounded text-sm">
                                        <?= nl2br($catatan) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- ✅ PERUBAHAN: Tombol "Lihat Detail" bukan "Edit" -->
                            <div class="text-center mt-3">
                                <a href="<?= base_url('staf/seminar_proposal/lihat_penilaian/' . $seminar->id) ?>" 
                                   class="btn btn-info">
                                    <i class="fas fa-eye mr-2"></i>
                                    Lihat Detail Penilaian
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Belum Ada Penilaian -->
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Penilaian Belum Diinput Dosen</strong>
                                <br>
                                <small>Menunggu dosen pembimbing melakukan penilaian</small>
                            </div>
                            
                            <!-- ✅ PERUBAHAN: Tombol "Lihat" bukan "Input" -->
                            <div class="text-center">
                                <a href="<?= base_url('staf/seminar_proposal/lihat_penilaian/' . $seminar->id) ?>" 
                                   class="btn btn-secondary btn-lg">
                                    <i class="fas fa-eye mr-2"></i>
                                    Lihat Status Penilaian
                                </a>
                            </div>
                            
                            <hr>
                            <p class="text-muted text-sm">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Info:</strong> Hanya dosen pembimbing yang dapat melakukan input penilaian. 
                                Staf memiliki akses read-only untuk monitoring.
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

/* ✅ NEW: CSS untuk wrap text nama dosen */
.dosen-name-wrap {
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.3;
    max-width: 100%;
}

/* Khusus untuk table cells */
td.dosen-name-wrap {
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    max-width: 200px; /* Batasi lebar maksimal */
}

/* Khusus untuk card body di dewan penguji */
.card-body .dosen-name-wrap {
    word-wrap: break-word;
    word-break: break-word;
    white-space: normal;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

/* ✅ PERUBAHAN: CSS untuk read-only alerts */
.alert-sm {
    padding: 0.375rem 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

.border-left-info {
    border-left: 0.25rem solid #17a2b8 !important;
}
</style>