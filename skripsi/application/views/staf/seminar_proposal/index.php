<!-- =====================================================
     VIEW: Staf Seminar Proposal Index - READ-ONLY PENILAIAN VERSION
     File: application/views/staf/seminar_proposal/index.php
     
     PERUBAHAN:
     - Tombol "Input Penilaian" diganti "Lihat Penilaian"
     - Staf hanya bisa view, tidak bisa input/edit
     ===================================================== -->

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-presentation mr-2"></i>
            <?= isset($page_title) ? $page_title : 'Seminar Proposal - Administrasi Staf' ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('staf/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Seminar Proposal</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- ✅ PERUBAHAN: Info alert tentang role read-only -->
    <?php if($this->session->flashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            <?= $this->session->flashdata('info') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards Row -->
    <div class="row">
        <!-- Seminar Hari Ini -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Seminar Hari Ini
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['seminar_hari_ini']) ? $stats['seminar_hari_ini'] : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seminar Minggu Ini -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Seminar Minggu Ini
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['seminar_minggu_ini']) ? $stats['seminar_minggu_ini'] : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ PERUBAHAN: Ubah statistik untuk read-only -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Sudah Dinilai Dosen
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['sudah_dinilai']) ? $stats['sudah_dinilai'] : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Administrasi -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Perlu Administrasi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['belum_dinilai']) ? $stats['belum_dinilai'] : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-alt mr-2"></i>
                        Daftar Seminar Proposal Mahasiswa
                        <?php if (isset($seminar_list) && !empty($seminar_list)): ?>
                            <span class="badge badge-primary ml-2"><?= count($seminar_list) ?></span>
                        <?php endif; ?>
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Export Data:</div>
                            <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/export_excel') ?>">
                                <i class="fas fa-file-excel fa-sm fa-fw mr-2 text-success"></i>
                                Export Excel
                            </a>
                            <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/export_pdf') ?>">
                                <i class="fas fa-file-pdf fa-sm fa-fw mr-2 text-danger"></i>
                                Export PDF
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal') ?>">
                                <i class="fas fa-sync fa-sm fa-fw mr-2 text-info"></i>
                                Refresh Data
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- ✅ PERUBAHAN: Info untuk staf tentang akses read-only -->
                    <div class="alert alert-light border-left-warning">
                        <i class="fas fa-info-circle text-warning mr-2"></i>
                        <strong>Catatan:</strong> Sebagai staf, Anda memiliki akses <strong>read-only</strong> untuk melihat penilaian. 
                        Hanya dosen yang dapat melakukan input dan edit penilaian seminar proposal.
                    </div>

                    <!-- Filter Row -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filter_prodi">Filter Program Studi:</label>
                                <select class="form-control" id="filter_prodi">
                                    <option value="">-- Semua Prodi --</option>
                                    <!-- Options akan diisi via JavaScript atau dari controller -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filter_status">Filter Status:</label>
                                <select class="form-control" id="filter_status">
                                    <option value="">-- Semua Status --</option>
                                    <option value="scheduled">Terjadwal</option>
                                    <option value="completed">Selesai</option>
                                    <option value="evaluated">Sudah Dinilai</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search_mahasiswa">Cari Mahasiswa:</label>
                                <input type="text" class="form-control" id="search_mahasiswa" 
                                       placeholder="Nama atau NIM mahasiswa...">
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="seminarTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="12%">NIM</th>
                                    <th width="15%">Nama Mahasiswa</th>
                                    <th width="25%">Judul Proposal</th>
                                    <th width="10%">Prodi</th>
                                    <th width="12%">Tanggal Seminar</th>
                                    <th width="8%">Status</th>
                                    <th width="13%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($seminar_list) && !empty($seminar_list)): ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($seminar_list as $seminar): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($seminar->nim ?? 'N/A') ?></strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm mr-2">
                                                        <span class="avatar-title bg-primary text-white rounded-circle">
                                                            <?= strtoupper(substr($seminar->nama_mahasiswa ?? 'N', 0, 1)) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($seminar->nama_mahasiswa ?? 'N/A') ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= htmlspecialchars($seminar->email_mahasiswa ?? 'N/A') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-wrap">
                                                    <?php 
                                                    // ✅ FIXED: Safe text truncation without character_limiter helper
                                                    $judul = $seminar->judul ?? 'Tidak ada judul';
                                                    echo htmlspecialchars(strlen($judul) > 80 ? substr($judul, 0, 80) . '...' : $judul);
                                                    ?>
                                                </div>
                                                <?php if (isset($seminar->nama_pembimbing) && !empty($seminar->nama_pembimbing)): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user-tie mr-1"></i>
                                                        Pembimbing: <?= htmlspecialchars($seminar->nama_pembimbing) ?>
                                                    </small>
                                                <?php endif; ?>
                                                
                                                <!-- ✅ TAMBAHAN: Display dosen penguji dari controller yang sudah diperbaiki -->
                                                <?php if (isset($seminar->nama_penguji1) && !empty($seminar->nama_penguji1)): ?>
                                                    <br>
                                                    <small class="text-info">
                                                        <i class="fas fa-users mr-1"></i>
                                                        Penguji: <?= htmlspecialchars($seminar->nama_penguji1) ?>
                                                        <?php if (isset($seminar->nama_penguji2) && !empty($seminar->nama_penguji2)): ?>
                                                            , <?= htmlspecialchars($seminar->nama_penguji2) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($seminar->nama_prodi) && !empty($seminar->nama_prodi)): ?>
                                                    <span class="badge badge-info"><?= htmlspecialchars($seminar->nama_prodi) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (isset($seminar->tanggal_seminar) && !empty($seminar->tanggal_seminar)): ?>
                                                    <div>
                                                        <i class="fas fa-calendar mr-1 text-primary"></i>
                                                        <strong class="text-primary">
                                                            <?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?>
                                                        </strong>
                                                    </div>
                                                    <?php if (isset($seminar->jam_seminar) && !empty($seminar->jam_seminar)): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT
                                                        </small>
                                                    <?php endif; ?>
                                                    <?php if (isset($seminar->tempat_seminar) && !empty($seminar->tempat_seminar)): ?>
                                                        <br>
                                                        <small class="text-info">
                                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                                            <?= htmlspecialchars($seminar->tempat_seminar) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Belum Terjadwal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                // ✅ FIXED: Status badge berdasarkan field yang benar dari controller
                                                $status = $seminar->status ?? 'unknown';
                                                $current_step = $seminar->current_step ?? '';
                                                
                                                if ($status === 'scheduled' && $current_step === 'staf') {
                                                    echo '<span class="badge badge-warning">Siap Administrasi</span>';
                                                } elseif ($status === 'scheduled') {
                                                    echo '<span class="badge badge-info">Terjadwal</span>';
                                                } elseif ($status === 'completed') {
                                                    echo '<span class="badge badge-success">Selesai</span>';
                                                } elseif ($status === 'approved') {
                                                    echo '<span class="badge badge-primary">Disetujui</span>';
                                                } else {
                                                    echo '<span class="badge badge-secondary">' . htmlspecialchars(ucfirst($status)) . '</span>';
                                                }
                                                ?>
                                                <br>
                                                <small class="text-muted">
                                                    Step: <?= htmlspecialchars($current_step) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- Tombol Detail -->
                                                    <a href="<?= base_url('staf/seminar_proposal/detail/' . $seminar->id) ?>" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Lihat Detail" 
                                                       data-toggle="tooltip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <!-- ✅ PERUBAHAN: Tombol "Lihat Penilaian" bukan "Input Penilaian" -->
                                                    <?php if (!empty($seminar->tanggal_seminar)): ?>
                                                        <a href="<?= base_url('staf/seminar_proposal/lihat_penilaian/' . $seminar->id) ?>" 
                                                           class="btn btn-secondary btn-sm" 
                                                           title="Lihat Penilaian (Read-Only)"
                                                           data-toggle="tooltip">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Dropdown untuk Download -->
                                                    <div class="btn-group" role="group">
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm dropdown-toggle" 
                                                                data-toggle="dropdown" 
                                                                aria-haspopup="true" 
                                                                aria-expanded="false" 
                                                                title="Download Dokumen">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <h6 class="dropdown-header">Dokumen Administrasi</h6>
                                                            <a class="dropdown-item" 
                                                               href="<?= base_url('staf/seminar_proposal/download_form_permohonan/' . $seminar->id) ?>">
                                                                <i class="fas fa-file-alt mr-2"></i>Form Permohonan
                                                            </a>
                                                            <a class="dropdown-item" 
                                                               href="<?= base_url('staf/seminar_proposal/download_undangan/' . $seminar->id) ?>">
                                                                <i class="fas fa-envelope mr-2"></i>Surat Undangan
                                                            </a>
                                                            <div class="dropdown-divider"></div>
                                                            <h6 class="dropdown-header">Dokumen Hasil</h6>
                                                            <a class="dropdown-item" 
                                                               href="<?= base_url('staf/seminar_proposal/download_berita_acara/' . $seminar->id) ?>">
                                                                <i class="fas fa-file-contract mr-2"></i>Berita Acara
                                                            </a>
                                                            <a class="dropdown-item" 
                                                               href="<?= base_url('staf/seminar_proposal/download_rekapitulasi_nilai/' . $seminar->id) ?>">
                                                                <i class="fas fa-chart-bar mr-2"></i>Rekapitulasi Nilai
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="py-4">
                                                <i class="fas fa-calendar-times fa-3x text-gray-400 mb-3"></i>
                                                <h5 class="text-gray-600">Belum Ada Data Seminar Proposal</h5>
                                                <p class="text-muted mb-0">
                                                    Data seminar proposal yang sudah dijadwalkan kaprodi akan muncul di sini.<br>
                                                    <small class="text-info">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Menampilkan seminar dengan status <strong>"scheduled"</strong> dan <strong>current_step "staf"</strong>
                                                    </small>
                                                </p>
                                                <hr class="my-3">
                                                <small class="text-muted">
                                                    <strong>Debug Info:</strong><br>
                                                    Controller: <?= $this->router->class ?? 'Unknown' ?><br>
                                                    Method: <?= $this->router->method ?? 'Unknown' ?><br>
                                                    Timestamp: <?= date('Y-m-d H:i:s') ?>
                                                </small>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ✅ TAMBAHAN: Info Footer untuk debugging -->
                    <?php if (isset($seminar_list) && !empty($seminar_list)): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Menampilkan <?= count($seminar_list) ?> data seminar proposal. 
                                    <strong>Akses Staf:</strong> Read-only untuk penilaian.
                                    Last updated: <?= date('d/m/Y H:i:s') ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ ENHANCED: Styles untuk avatar dan UI improvements -->
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
}

.text-wrap {
    word-wrap: break-word;
    white-space: normal;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

/* Enhanced table styling */
.table td {
    vertical-align: middle;
}

.dropdown-header {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 600;
}

/* Tooltip improvements */
[data-toggle="tooltip"] {
    cursor: pointer;
}

/* Badge enhancements */
.badge {
    font-size: 0.75rem;
}

/* ✅ PERUBAHAN: Styling untuk read-only access */
.border-left-warning {
    border-left: 0.25rem solid #f1c40f !important;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 2px;
        border-radius: 0.25rem !important;
    }
}
</style>

<!-- ✅ TAMBAHAN: JavaScript untuk enhanced functionality -->
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Enhanced table search functionality
    $('#search_mahasiswa').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#seminarTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Status filter functionality
    $('#filter_status').on('change', function() {
        var selectedStatus = $(this).val().toLowerCase();
        $('#seminarTable tbody tr').each(function() {
            var statusText = $(this).find('td:eq(6) .badge').text().toLowerCase();
            if (selectedStatus === '' || statusText.indexOf(selectedStatus) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        // Optional: Add auto-refresh functionality
        // location.reload();
    }, 300000); // 5 minutes
});
</script>