<?php
/**
 * Staf Seminar Proposal Index View - SIM TA STK Santo Yakobus
 * 
 * View untuk menampilkan dashboard seminar proposal dari perspektif staf akademik
 * Menampilkan daftar mahasiswa yang sudah disetujui kaprodi dan terjadwal
 * 
 * File: application/views/staf/seminar_proposal/index.php
 * Controller: staf/Seminar_proposal::index()
 * 
 * Features:
 * - Dashboard statistics
 * - List seminar proposal dengan filter
 * - Quick actions untuk download dokumen
 * - Link ke detail dan input penilaian
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
                    <i class="fas fa-presentation mr-2 text-primary"></i>
                    Seminar Proposal
                </h1>
                <p class="text-muted">Administrasi dan Monitoring Seminar Proposal Mahasiswa</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= base_url('staf/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Seminar Proposal</li>
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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['seminar_hari_ini'] ?></h3>
                        <p>Seminar Hari Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="small-box-footer">
                        <span class="text-sm">Jadwal <?= date('d F Y') ?></span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['seminar_minggu_ini'] ?></h3>
                        <p>Seminar Minggu Ini</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="small-box-footer">
                        <span class="text-sm">
                            <?= date('d M', strtotime('monday this week')) ?> - 
                            <?= date('d M Y', strtotime('sunday this week')) ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['belum_dinilai'] ?></h3>
                        <p>Belum Dinilai</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="small-box-footer">
                        <span class="text-sm">Perlu Input Penilaian</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3><?= $stats['sudah_dinilai'] ?></h3>
                        <p>Sudah Dinilai</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="small-box-footer">
                        <span class="text-sm">Penilaian Selesai</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-2"></i>
                    Filter dan Pencarian
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= current_url() ?>" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="filter_prodi" class="mr-2">Prodi:</label>
                        <select name="filter_prodi" id="filter_prodi" class="form-control">
                            <option value="">Semua Prodi</option>
                            <option value="Pendidikan Agama Katolik" <?= $this->input->get('filter_prodi') == 'Pendidikan Agama Katolik' ? 'selected' : '' ?>>PAK</option>
                            <option value="Pendidikan Guru Sekolah Dasar" <?= $this->input->get('filter_prodi') == 'Pendidikan Guru Sekolah Dasar' ? 'selected' : '' ?>>PGSD</option>
                        </select>
                    </div>
                    
                    <div class="form-group mr-3">
                        <label for="filter_bulan" class="mr-2">Bulan:</label>
                        <select name="filter_bulan" id="filter_bulan" class="form-control">
                            <option value="">Semua Bulan</option>
                            <?php for($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= $this->input->get('filter_bulan') == $i ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group mr-3">
                        <label for="search" class="mr-2">Cari:</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="NIM/Nama/Judul..." value="<?= $this->input->get('search') ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search mr-1"></i> Filter
                    </button>
                    <a href="<?= current_url() ?>" class="btn btn-secondary">
                        <i class="fas fa-refresh mr-1"></i> Reset
                    </a>
                </form>
            </div>
        </div>

        <!-- Main Data Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Daftar Seminar Proposal Terjadwal
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary">
                        <?= count($seminar_list) ?> Seminar
                    </span>
                </div>
            </div>
            <div class="card-body">
                <?php if(empty($seminar_list)): ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum Ada Seminar Proposal Terjadwal</h5>
                        <p class="text-muted">
                            Seminar proposal akan muncul di sini setelah mahasiswa mengajukan, 
                            disetujui dosen pembimbing, divalidasi kaprodi, dan dijadwalkan.
                        </p>
                    </div>
                <?php else: ?>
                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="seminarTable">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">NIM</th>
                                    <th width="15%">Mahasiswa</th>
                                    <th width="25%">Judul Proposal</th>
                                    <th width="10%">Prodi</th>
                                    <th width="12%">Jadwal</th>
                                    <th width="15%">Dewan Penguji</th>
                                    <th width="8%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($seminar_list as $index => $seminar): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= $seminar->nim ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= character_limiter($seminar->nama_mahasiswa, 20) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope mr-1"></i>
                                                <?= character_limiter($seminar->email_mahasiswa, 25) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?= character_limiter($seminar->judul, 50) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?= $seminar->lokasi_penelitian ?>
                                            </small>
                                            <br>
                                            <span class="badge badge-info badge-sm">
                                                <?= $seminar->jenis_penelitian ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                <?= $seminar->nama_prodi ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?>
                                            </strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-door-open mr-1"></i>
                                                <?= $seminar->tempat_seminar ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <strong>Pembimbing:</strong><br>
                                                <?= character_limiter($seminar->nama_pembimbing, 20) ?>
                                                <br><br>
                                                <strong>Penguji 1:</strong><br>
                                                <?= $seminar->nama_penguji1 ? character_limiter($seminar->nama_penguji1, 20) : '<em class="text-muted">Belum ditetapkan</em>' ?>
                                                <br><br>
                                                <strong>Penguji 2:</strong><br>
                                                <?= $seminar->nama_penguji2 ? character_limiter($seminar->nama_penguji2, 20) : '<em class="text-muted">Belum ditetapkan</em>' ?>
                                            </small>
                                        </td>
                                        <td>
                                            <!-- Dropdown Action Menu -->
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                        type="button" data-toggle="dropdown">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <!-- Detail -->
                                                    <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/detail/' . $seminar->seminar_id) ?>">
                                                        <i class="fas fa-eye mr-2 text-info"></i>
                                                        Detail Lengkap
                                                    </a>
                                                    
                                                    <div class="dropdown-divider"></div>
                                                    
                                                    <!-- Download Documents -->
                                                    <h6 class="dropdown-header">Download Dokumen</h6>
                                                    
                                                    <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_form_permohonan/' . $seminar->seminar_id) ?>" target="_blank">
                                                        <i class="fas fa-file-alt mr-2 text-primary"></i>
                                                        Form Permohonan
                                                    </a>
                                                    
                                                    <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_undangan/' . $seminar->seminar_id) ?>" target="_blank">
                                                        <i class="fas fa-envelope mr-2 text-success"></i>
                                                        Undangan Seminar
                                                    </a>
                                                    
                                                    <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_berita_acara/' . $seminar->seminar_id) ?>" target="_blank">
                                                        <i class="fas fa-clipboard mr-2 text-warning"></i>
                                                        Berita Acara
                                                    </a>
                                                    
                                                    <!-- Form Penilaian Submenu -->
                                                    <div class="dropdown-submenu">
                                                        <a class="dropdown-item dropdown-toggle" href="#">
                                                            <i class="fas fa-star mr-2 text-danger"></i>
                                                            Form Penilaian
                                                        </a>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->seminar_id . '/all') ?>" target="_blank">
                                                                <i class="fas fa-users mr-2"></i>
                                                                Semua Penguji
                                                            </a>
                                                            <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->seminar_id . '/pembimbing') ?>" target="_blank">
                                                                <i class="fas fa-user-tie mr-2"></i>
                                                                Pembimbing
                                                            </a>
                                                            <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->seminar_id . '/penguji1') ?>" target="_blank">
                                                                <i class="fas fa-user mr-2"></i>
                                                                Penguji 1
                                                            </a>
                                                            <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_form_penilaian/' . $seminar->seminar_id . '/penguji2') ?>" target="_blank">
                                                                <i class="fas fa-user mr-2"></i>
                                                                Penguji 2
                                                            </a>
                                                        </div>
                                                    </div>
                                                    
                                                    <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/download_rekapitulasi_nilai/' . $seminar->seminar_id) ?>" target="_blank">
                                                        <i class="fas fa-chart-bar mr-2 text-purple"></i>
                                                        Rekapitulasi Nilai
                                                    </a>
                                                    
                                                    <div class="dropdown-divider"></div>
                                                    
                                                    <!-- Input Penilaian -->
                                                    <a class="dropdown-item" href="<?= base_url('staf/seminar_proposal/input_penilaian/' . $seminar->seminar_id) ?>">
                                                        <i class="fas fa-edit mr-2 text-success"></i>
                                                        Input Penilaian
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            <!-- Quick Access Buttons -->
                                            <div class="btn-group-vertical btn-group-sm mt-2">
                                                <a href="<?= base_url('staf/seminar_proposal/detail/' . $seminar->seminar_id) ?>" 
                                                   class="btn btn-outline-info btn-xs" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= base_url('staf/seminar_proposal/input_penilaian/' . $seminar->seminar_id) ?>" 
                                                   class="btn btn-outline-success btn-xs" title="Input Penilaian">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats and Info Cards -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>
                            Workflow Seminar Proposal
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="time-label">
                                <span class="bg-success">Workflow Status</span>
                            </div>
                            
                            <div>
                                <i class="fas fa-user bg-primary"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-check text-success"></i></span>
                                    <h3 class="timeline-header">1. Mahasiswa Ajukan Proposal</h3>
                                    <div class="timeline-body">
                                        Mahasiswa mengajukan seminar proposal dengan melengkapi data dan file proposal.
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <i class="fas fa-user-tie bg-warning"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-check text-success"></i></span>
                                    <h3 class="timeline-header">2. Dosen Pembimbing Review</h3>
                                    <div class="timeline-body">
                                        Dosen pembimbing mereview dan memberikan rekomendasi (approve/reject).
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <i class="fas fa-user-graduate bg-info"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-check text-success"></i></span>
                                    <h3 class="timeline-header">3. Kaprodi Validasi</h3>
                                    <div class="timeline-body">
                                        Kaprodi melakukan cek turnitin dan memberikan persetujuan final.
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <i class="fas fa-calendar bg-success"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-check text-success"></i></span>
                                    <h3 class="timeline-header">4. Penjadwalan</h3>
                                    <div class="timeline-body">
                                        Kaprodi menetapkan jadwal, tempat, dan dosen penguji.
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <i class="fas fa-users bg-primary"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-arrow-right text-primary"></i></span>
                                    <h3 class="timeline-header text-primary">5. Administrasi Staf</h3>
                                    <div class="timeline-body">
                                        <strong>Tahap saat ini:</strong> Staf menyiapkan administrasi dan input penilaian seminar.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">
                            <i class="fas fa-tasks mr-2"></i>
                            Checklist Administrasi
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Pastikan administrasi seminar proposal lengkap:</p>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="check1" checked disabled>
                            <label class="custom-control-label" for="check1">
                                <i class="fas fa-file-alt mr-2 text-primary"></i>
                                Form permohonan seminar proposal
                            </label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="check2" checked disabled>
                            <label class="custom-control-label" for="check2">
                                <i class="fas fa-envelope mr-2 text-success"></i>
                                Undangan untuk dewan penguji
                            </label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="check3" checked disabled>
                            <label class="custom-control-label" for="check3">
                                <i class="fas fa-clipboard mr-2 text-warning"></i>
                                Berita acara seminar proposal
                            </label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="check4" checked disabled>
                            <label class="custom-control-label" for="check4">
                                <i class="fas fa-star mr-2 text-danger"></i>
                                Form penilaian untuk setiap penguji
                            </label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="check5">
                            <label class="custom-control-label" for="check5">
                                <i class="fas fa-edit mr-2 text-info"></i>
                                Input penilaian dari staf (opsional)
                            </label>
                        </div>
                        
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="check6">
                            <label class="custom-control-label" for="check6">
                                <i class="fas fa-chart-bar mr-2 text-purple"></i>
                                Rekapitulasi nilai akhir
                            </label>
                        </div>
                        
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-lightbulb mr-1"></i>
                            <strong>Tips:</strong> Gunakan dropdown action untuk mengakses semua dokumen dengan cepat.
                        </small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Additional CSS for dropdowns -->
<style>
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu > .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -6px;
    margin-left: -1px;
    border-radius: 0 6px 6px 6px;
}

.dropdown-submenu:hover > .dropdown-menu {
    display: block;
}

.dropdown-submenu > a:after {
    display: block;
    content: " ";
    float: right;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    border-left-color: #ccc;
    margin-top: 5px;
    margin-right: -10px;
}

.dropdown-submenu:hover > a:after {
    border-left-color: #fff;
}

.dropdown-submenu.pull-left {
    float: none;
}

.dropdown-submenu.pull-left > .dropdown-menu {
    left: -100%;
    margin-left: 10px;
    border-radius: 6px 0 6px 6px;
}

.timeline > div > .timeline-item {
    background: #fff;
    border-radius: 3px;
    width: calc(100% - 40px);
    margin-left: 40px;
    margin-top: 0;
    color: #444;
    border-top: 0;
    padding: 10px;
    position: relative;
    box-shadow: 0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24);
}

.small-box .small-box-footer {
    position: relative;
    text-align: center;
    padding: 3px 0;
    color: rgba(255,255,255,.8);
    color: #fff;
    display: block;
    z-index: 10;
    background: rgba(0,0,0,.1);
    text-decoration: none;
}
</style>