<!-- =====================================================
     VIEW: Staf Seminar Proposal Index - FIXED VERSION
     File: application/views/staf/seminar_proposal/index.php
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

        <!-- Belum Dinilai -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Perlu Input Penilaian
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['belum_dinilai']) ? $stats['belum_dinilai'] : '0' ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sudah Dinilai -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Sudah Dinilai
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
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-alt mr-2"></i>
                        Daftar Seminar Proposal Mahasiswa
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
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Row -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filter_prodi">Filter Program Studi:</label>
                                <select class="form-control" id="filter_prodi">
                                    <option value="">-- Semua Prodi --</option>
                                    <!-- Options akan diisi via JavaScript -->
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
                                                <strong><?= htmlspecialchars($seminar->nim) ?></strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm mr-2">
                                                        <span class="avatar-title bg-primary text-white rounded-circle">
                                                            <?= strtoupper(substr($seminar->nama_mahasiswa, 0, 1)) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($seminar->nama_mahasiswa) ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= htmlspecialchars($seminar->email_mahasiswa) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-wrap">
                                                    <?= character_limiter(htmlspecialchars($seminar->judul), 80) ?>
                                                </div>
                                                <?php if (isset($seminar->nama_pembimbing)): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user-tie mr-1"></i>
                                                        Pembimbing: <?= htmlspecialchars($seminar->nama_pembimbing) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= htmlspecialchars($seminar->nama_prodi) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($seminar->tanggal_seminar)): ?>
                                                    <div>
                                                        <i class="fas fa-calendar mr-1"></i>
                                                        <?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?>
                                                    </div>
                                                    <?php if (!empty($seminar->jam_seminar)): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            <?= date('H:i', strtotime($seminar->jam_seminar)) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    <?php if (!empty($seminar->tempat_seminar)): ?>
                                                        <br>
                                                        <small class="text-muted">
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
                                                $status_class = 'secondary';
                                                $status_text = 'Menunggu';
                                                
                                                if (isset($seminar->status)) {
                                                    switch ($seminar->status) {
                                                        case 'scheduled':
                                                            $status_class = 'primary';
                                                            $status_text = 'Terjadwal';
                                                            break;
                                                        case 'completed':
                                                            $status_class = 'success';
                                                            $status_text = 'Selesai';
                                                            break;
                                                        case 'evaluated':
                                                            $status_class = 'info';
                                                            $status_text = 'Dinilai';
                                                            break;
                                                    }
                                                }
                                                ?>
                                                <span class="badge badge-<?= $status_class ?>">
                                                    <?= $status_text ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- Tombol Detail -->
                                                    <a href="<?= base_url('staf/seminar_proposal/detail/' . $seminar->id) ?>" 
                                                       class="btn btn-info btn-sm" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <!-- Tombol Input Penilaian (jika sudah terjadwal) -->
                                                    <?php if (!empty($seminar->tanggal_seminar)): ?>
                                                        <a href="<?= base_url('staf/seminar_proposal/input_penilaian/' . $seminar->id) ?>" 
                                                           class="btn btn-warning btn-sm" title="Input Penilaian">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Dropdown untuk Download -->
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" 
                                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" 
                                                               href="<?= base_url('staf/seminar_proposal/download_form_permohonan/' . $seminar->id) ?>">
                                                                <i class="fas fa-file-alt mr-2"></i>Form Permohonan
                                                            </a>
                                                            <a class="dropdown-item" 
                                                               href="<?= base_url('staf/seminar_proposal/download_undangan/' . $seminar->id) ?>">
                                                                <i class="fas fa-envelope mr-2"></i>Surat Undangan
                                                            </a>
                                                            <div class="dropdown-divider"></div>
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
                                                <i class="fas fa-inbox fa-3x text-gray-400 mb-3"></i>
                                                <h5 class="text-gray-600">Belum Ada Data Seminar Proposal</h5>
                                                <p class="text-muted">
                                                    Data seminar proposal yang sudah disetujui kaprodi akan muncul di sini.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles untuk avatar -->
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
</style>