<?php
/**
 * View Jadwal Seminar Skripsi
 * 
 * File: application/views/mahasiswa/seminar_skripsi/jadwal_seminar.php
 * 
 * Menampilkan informasi jadwal seminar setelah disetujui Kaprodi
 * dan dijadwalkan oleh staf.
 */

// Defensive programming
$seminar = isset($seminar) ? $seminar : new stdClass();
$jadwal = isset($jadwal) ? $jadwal : new stdClass();

// Default values
if (!isset($seminar->id)) $seminar->id = 0;
if (!isset($seminar->judul_skripsi)) $seminar->judul_skripsi = 'Judul tidak tersedia';
if (!isset($seminar->nama_mahasiswa)) $seminar->nama_mahasiswa = '';
if (!isset($seminar->nim)) $seminar->nim = '';
if (!isset($seminar->nama_pembimbing)) $seminar->nama_pembimbing = '';

// Jadwal info
if (!isset($jadwal->tanggal_seminar)) $jadwal->tanggal_seminar = '';
if (!isset($jadwal->jam_seminar)) $jadwal->jam_seminar = '';
if (!isset($jadwal->tempat_seminar)) $jadwal->tempat_seminar = '';
if (!isset($jadwal->nama_penguji1)) $jadwal->nama_penguji1 = '';
if (!isset($jadwal->nama_penguji2)) $jadwal->nama_penguji2 = '';

// Parse tanggal untuk countdown
$tanggal_seminar = !empty($jadwal->tanggal_seminar) ? strtotime($jadwal->tanggal_seminar . ' ' . ($jadwal->jam_seminar ?: '00:00:00')) : null;
$hari_ini = time();
$selisih_hari = $tanggal_seminar ? ceil(($tanggal_seminar - $hari_ini) / (60 * 60 * 24)) : 0;
?>

<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt mr-2"></i>
            Jadwal Seminar Skripsi
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                <li class="breadcrumb-item active">Jadwal Seminar</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            
            <!-- Countdown Card (if seminar belum dilaksanakan) -->
            <?php if ($tanggal_seminar && $tanggal_seminar > $hari_ini): ?>
            <div class="card shadow mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body text-center">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <i class="fas fa-hourglass-half fa-3x mb-3"></i>
                            <h5>Countdown Seminar</h5>
                        </div>
                        <div class="col-md-8">
                            <div id="countdown-timer" class="countdown-display">
                                <div class="countdown-item">
                                    <span id="days">0</span>
                                    <small>Hari</small>
                                </div>
                                <div class="countdown-item">
                                    <span id="hours">0</span>
                                    <small>Jam</small>
                                </div>
                                <div class="countdown-item">
                                    <span id="minutes">0</span>
                                    <small>Menit</small>
                                </div>
                                <div class="countdown-item">
                                    <span id="seconds">0</span>
                                    <small>Detik</small>
                                </div>
                            </div>
                            <?php if ($selisih_hari <= 7): ?>
                                <div class="mt-3">
                                    <span class="badge badge-warning badge-lg">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Seminar dalam <?= $selisih_hari ?> hari!
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Info Jadwal Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Informasi Jadwal Seminar
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Tanggal & Waktu -->
                            <div class="info-item mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="icon-circle bg-primary text-white mr-3">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-primary">Tanggal & Waktu</h6>
                                        <small class="text-muted">Jadwal pelaksanaan seminar</small>
                                    </div>
                                </div>
                                <div class="ml-5">
                                    <?php if (!empty($jadwal->tanggal_seminar)): ?>
                                        <div class="font-weight-bold text-dark mb-1">
                                            <?= date('l, d F Y', strtotime($jadwal->tanggal_seminar)) ?>
                                        </div>
                                        <?php if (!empty($jadwal->jam_seminar)): ?>
                                            <div class="text-muted">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?= date('H:i', strtotime($jadwal->jam_seminar)) ?> WIB
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Belum dijadwalkan</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tempat -->
                            <div class="info-item mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="icon-circle bg-success text-white mr-3">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-success">Tempat Seminar</h6>
                                        <small class="text-muted">Lokasi pelaksanaan</small>
                                    </div>
                                </div>
                                <div class="ml-5">
                                    <?php if (!empty($jadwal->tempat_seminar)): ?>
                                        <div class="font-weight-bold text-dark">
                                            <?= htmlspecialchars($jadwal->tempat_seminar) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Belum ditentukan</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Tim Penguji -->
                            <div class="info-item mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="icon-circle bg-warning text-white mr-3">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-warning">Tim Penguji</h6>
                                        <small class="text-muted">Dosen penguji seminar</small>
                                    </div>
                                </div>
                                <div class="ml-5">
                                    <div class="mb-2">
                                        <strong class="text-primary">Pembimbing:</strong><br>
                                        <span class="text-dark"><?= htmlspecialchars($seminar->nama_pembimbing ?: 'Belum ditentukan') ?></span>
                                    </div>
                                    
                                    <?php if (!empty($jadwal->nama_penguji1)): ?>
                                    <div class="mb-2">
                                        <strong class="text-info">Penguji 1:</strong><br>
                                        <span class="text-dark"><?= htmlspecialchars($jadwal->nama_penguji1) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($jadwal->nama_penguji2)): ?>
                                    <div class="mb-2">
                                        <strong class="text-info">Penguji 2:</strong><br>
                                        <span class="text-dark"><?= htmlspecialchars($jadwal->nama_penguji2) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (empty($jadwal->nama_penguji1) && empty($jadwal->nama_penguji2)): ?>
                                        <span class="text-muted">Tim penguji belum ditentukan</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Seminar Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i>
                        Detail Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%" class="font-weight-bold text-muted">Mahasiswa:</td>
                            <td><?= htmlspecialchars($seminar->nama_mahasiswa) ?> (<?= htmlspecialchars($seminar->nim) ?>)</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Judul Skripsi:</td>
                            <td>
                                <div class="judul-wrap">
                                    <?= htmlspecialchars($seminar->judul_skripsi) ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold text-muted">Status:</td>
                            <td>
                                <span class="badge badge-info badge-lg">
                                    <i class="fas fa-calendar-check mr-1"></i>
                                    Terjadwal
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Persiapan Seminar Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        Persiapan Seminar
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-left-info">
                        <h6><i class="fas fa-lightbulb mr-2"></i>Tips Persiapan Seminar:</h6>
                        <ul class="mb-0">
                            <li>Persiapkan presentasi PowerPoint yang ringkas dan jelas</li>
                            <li>Latihan presentasi dengan durasi 15-20 menit</li>
                            <li>Siapkan jawaban untuk pertanyaan yang mungkin diajukan</li>
                            <li>Bawa salinan skripsi untuk tim penguji</li>
                            <li>Datang 15 menit sebelum jadwal seminar</li>
                            <li>Berpakaian formal dan sopan</li>
                        </ul>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="checklist-item">
                                <h6 class="text-primary">
                                    <i class="fas fa-check-square mr-2"></i>
                                    Dokumen yang Diperlukan:
                                </h6>
                                <ul class="list-unstyled ml-3">
                                    <li><i class="fas fa-file-pdf text-success mr-2"></i>File skripsi lengkap</li>
                                    <li><i class="fas fa-presentation text-info mr-2"></i>Presentasi PowerPoint</li>
                                    <li><i class="fas fa-print text-warning mr-2"></i>Salinan cetak skripsi (3 eksemplar)</li>
                                    <li><i class="fas fa-id-card text-secondary mr-2"></i>Kartu mahasiswa/identitas</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="checklist-item">
                                <h6 class="text-success">
                                    <i class="fas fa-cog mr-2"></i>
                                    Persiapan Teknis:
                                </h6>
                                <ul class="list-unstyled ml-3">
                                    <li><i class="fas fa-laptop text-primary mr-2"></i>Laptop + charger</li>
                                    <li><i class="fas fa-usb-drive text-info mr-2"></i>Backup file di flashdisk</li>
                                    <li><i class="fas fa-wifi text-success mr-2"></i>Koneksi internet stabil</li>
                                    <li><i class="fas fa-microphone text-warning mr-2"></i>Test microphone/speaker</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <!-- Quick Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info mr-2"></i>
                        Ringkasan Jadwal
                    </h6>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($jadwal->tanggal_seminar)): ?>
                        <div class="calendar-display mb-3">
                            <div class="calendar-month bg-primary text-white p-2">
                                <?= date('M', strtotime($jadwal->tanggal_seminar)) ?>
                            </div>
                            <div class="calendar-day bg-light p-3">
                                <h2 class="mb-0 text-primary"><?= date('d', strtotime($jadwal->tanggal_seminar)) ?></h2>
                                <small class="text-muted"><?= date('Y', strtotime($jadwal->tanggal_seminar)) ?></small>
                            </div>
                        </div>
                        
                        <h5 class="text-primary"><?= date('l', strtotime($jadwal->tanggal_seminar)) ?></h5>
                        
                        <?php if (!empty($jadwal->jam_seminar)): ?>
                            <h4 class="text-dark"><?= date('H:i', strtotime($jadwal->jam_seminar)) ?> WIB</h4>
                        <?php endif; ?>
                        
                        <?php if ($selisih_hari > 0): ?>
                            <div class="mt-3">
                                <span class="badge badge-<?= $selisih_hari <= 3 ? 'danger' : ($selisih_hari <= 7 ? 'warning' : 'info') ?> badge-lg">
                                    <?= $selisih_hari ?> hari lagi
                                </span>
                            </div>
                        <?php elseif ($selisih_hari == 0): ?>
                            <div class="mt-3">
                                <span class="badge badge-success badge-lg animate-pulse">
                                    <i class="fas fa-calendar-day mr-1"></i>
                                    HARI INI!
                                </span>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Jadwal Belum Ditentukan</h6>
                        <p class="text-muted mb-0">Menunggu konfirmasi dari staf akademik</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools mr-2"></i>
                        Aksi Tersedia
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Download Files -->
                    <?php if (!empty($seminar->file_skripsi)): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/download_file/' . $seminar->id . '/skripsi') ?>" 
                           class="btn btn-success btn-block mb-2">
                            <i class="fas fa-download mr-2"></i>
                            Download Skripsi
                        </a>
                    <?php endif; ?>

                    <!-- Print Jadwal -->
                    <button onclick="window.print()" class="btn btn-info btn-block mb-2">
                        <i class="fas fa-print mr-2"></i>
                        Cetak Jadwal
                    </button>

                    <!-- Add to Calendar -->
                    <?php if (!empty($jadwal->tanggal_seminar)): ?>
                        <a href="<?= base_url('mahasiswa/seminar_skripsi/add_to_calendar/' . $seminar->id) ?>" 
                           class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            Tambah ke Kalender
                        </a>
                    <?php endif; ?>

                    <!-- Back to Seminar -->
                    <a href="<?= base_url('mahasiswa/seminar_skripsi') ?>" 
                       class="btn btn-outline-primary btn-block mb-2">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Seminar
                    </a>

                    <!-- Dashboard -->
                    <a href="<?= base_url('mahasiswa/dashboard') ?>" 
                       class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </div>
            </div>

            <!-- Contact Info Card -->
            <div class="card shadow">
                <div class="card-header py-3 bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-phone mr-2"></i>
                        Kontak Darurat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="contact-item mb-3">
                        <h6 class="text-primary">Staf Akademik</h6>
                        <small class="text-muted">
                            <i class="fas fa-envelope mr-1"></i>
                            akademik@stkyakobus.ac.id<br>
                            <i class="fas fa-phone mr-1"></i>
                            (0971) 123-456
                        </small>
                    </div>
                    
                    <div class="contact-item">
                        <h6 class="text-success">IT Support</h6>
                        <small class="text-muted">
                            <i class="fas fa-envelope mr-1"></i>
                            it@stkyakobus.ac.id<br>
                            <i class="fas fa-phone mr-1"></i>
                            (0971) 123-457
                        </small>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Hubungi jika ada kendala teknis atau perubahan jadwal mendadak
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.countdown-display {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
}

.countdown-item {
    text-align: center;
    background: rgba(255,255,255,0.2);
    border-radius: 8px;
    padding: 1rem;
    min-width: 60px;
}

.countdown-item span {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    line-height: 1;
}

.countdown-item small {
    display: block;
    font-size: 0.75rem;
    opacity: 0.8;
    margin-top: 0.25rem;
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.info-item {
    position: relative;
}

.calendar-display {
    max-width: 120px;
    margin: 0 auto;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.calendar-month {
    text-align: center;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 0.85rem;
}

.calendar-day {
    text-align: center;
    border: 1px solid #dee2e6;
    border-top: none;
}

.judul-wrap {
    word-wrap: break-word;
    word-break: break-word;
    line-height: 1.4;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.border-left-info {
    border-left: 4px solid #36b9cc !important;
}

.animate-pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.checklist-item ul li {
    margin-bottom: 0.5rem;
}

.contact-item {
    padding-bottom: 1rem;
    border-bottom: 1px solid #f8f9fa;
}

.contact-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

/* Print Styles */
@media print {
    .col-lg-4 {
        display: none !important;
    }
    
    .col-lg-8 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        break-inside: avoid;
        margin-bottom: 1rem !important;
    }
    
    .card-header {
        background: #f8f9fa !important;
        color: #495057 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .countdown-display {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .countdown-item {
        min-width: 50px;
        padding: 0.75rem;
    }
    
    .countdown-item span {
        font-size: 1.5rem;
    }
    
    .info-item {
        margin-bottom: 2rem;
    }
}
</style>

<script>
$(document).ready(function() {
    
    // Countdown Timer
    <?php if ($tanggal_seminar && $tanggal_seminar > $hari_ini): ?>
    function updateCountdown() {
        var seminarDate = new Date(<?= $tanggal_seminar * 1000 ?>);
        var now = new Date().getTime();
        var distance = seminarDate - now;
        
        if (distance > 0) {
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById("days").innerHTML = days;
            document.getElementById("hours").innerHTML = hours;
            document.getElementById("minutes").innerHTML = minutes;
            document.getElementById("seconds").innerHTML = seconds;
        } else {
            document.getElementById("countdown-timer").innerHTML = '<h4 class="text-white"><i class="fas fa-calendar-check mr-2"></i>Seminar sedang berlangsung!</h4>';
        }
    }
    
    // Update countdown every second
    updateCountdown();
    setInterval(updateCountdown, 1000);
    <?php endif; ?>
    
    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
    
    // Smooth scroll for any anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
});
</script>