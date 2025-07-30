<?php
/**
 * View: application/views/dosen/penelitian_content.php
 * Updated untuk kompatibilitas dengan controller penelitian yang baru
 * File ini di-include oleh penelitian.php (wrapper file)
 */
?>

<!-- Hero Section -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8">
                        <h2 class="text-white mb-0">
                            <i class="fa fa-microscope mr-3"></i>
                            Penelitian - Surat Izin Penelitian
                        </h2>
                        <p class="text-white opacity-8 mt-2 mb-0">
                            Berikan rekomendasi untuk proses penelitian lapangan atau laboratorium.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fa fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Pengajuan</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)) {
                                echo count($surat_izin_penelitian);
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-arrow-up"></i></span>
                    <span class="text-nowrap">Surat izin penelitian</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Perlu Review</h5>
                        <span class="h2 font-weight-bold mb-0 text-warning">
                            <?php 
                            $perlu_review = 0;
                            if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)) {
                                foreach($surat_izin_penelitian as $item) {
                                    // Cek apakah masih menunggu rekomendasi
                                    if(isset($item->status_pembimbing) && $item->status_pembimbing == '0') {
                                        $perlu_review++;
                                    } elseif(!isset($item->status_pembimbing) || $item->status_pembimbing === null) {
                                        $perlu_review++;
                                    }
                                }
                            }
                            echo $perlu_review;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i></span>
                    <span class="text-nowrap">Menunggu rekomendasi</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Disetujui</h5>
                        <span class="h2 font-weight-bold mb-0 text-success">
                            <?php 
                            $disetujui = 0;
                            if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)) {
                                foreach($surat_izin_penelitian as $item) {
                                    if(isset($item->status_pembimbing) && $item->status_pembimbing == '1') {
                                        $disetujui++;
                                    }
                                }
                            }
                            echo $disetujui;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-check"></i></span>
                    <span class="text-nowrap">Sudah direkomendasikan</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Proses Staf</h5>
                        <span class="h2 font-weight-bold mb-0 text-primary">
                            <?php 
                            $proses_staf = 0;
                            if(isset($surat_izin_penelitian) && !empty($surat_izin_penelitian)) {
                                foreach($surat_izin_penelitian as $item) {
                                    // Yang sudah disetujui dosen dan sedang diproses staf
                                    if(isset($item->status_pembimbing) && $item->status_pembimbing == '1' && 
                                       (!isset($item->surat_izin_file) || empty($item->surat_izin_file))) {
                                        $proses_staf++;
                                    }
                                }
                            }
                            echo $proses_staf;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                            <i class="fa fa-cogs"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-primary mr-2"><i class="fa fa-spinner"></i></span>
                    <span class="text-nowrap">Sedang diproses</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fa fa-list text-primary mr-2"></i>
                            Daftar Pengajuan Surat Izin Penelitian
                        </h3>
                    </div>
                    <div class="col text-right">
                        <p class="text-muted mb-0">
                            <i class="fa fa-info-circle mr-1"></i>
                            Kelola permohonan izin penelitian mahasiswa bimbingan
                        </p>
                    </div>
                </div>
            </div>
            
            <?php if(!empty($surat_izin_penelitian)): ?>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Penelitian</th>
                            <th scope="col">Tanggal Pengajuan</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($surat_izin_penelitian as $surat): ?>
                        <tr>
                            <td>
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="name mb-0 text-sm font-weight-bold">
                                            <?= isset($surat->nama) ? $surat->nama : 'N/A' ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fa fa-id-card mr-1"></i>
                                            <?= isset($surat->nim) ? $surat->nim : 'N/A' ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fa fa-graduation-cap mr-1"></i>
                                            <?= isset($surat->nama_prodi) ? $surat->nama_prodi : 'N/A' ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <strong><?= isset($surat->judul) ? character_limiter($surat->judul, 50) : 'N/A' ?></strong>
                                </div>
                                <small class="text-muted">
                                    <i class="fa fa-map-marker-alt mr-1"></i>
                                    <?= isset($surat->lokasi_penelitian) ? $surat->lokasi_penelitian : 'Belum ditentukan' ?>
                                </small>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <i class="fa fa-calendar-alt mr-1 text-primary"></i>
                                    <?= isset($surat->created_at) ? date('d/m/Y', strtotime($surat->created_at)) : 'N/A' ?>
                                </div>
                                <small class="text-muted">
                                    <i class="fa fa-clock mr-1"></i>
                                    <?= isset($surat->created_at) ? date('H:i', strtotime($surat->created_at)) : 'N/A' ?> WIT
                                </small>
                            </td>
                            <td>
                                <?php 
                                $status = isset($surat->status_pembimbing) ? $surat->status_pembimbing : '0';
                                switch($status) {
                                    case '1':
                                        echo '<span class="badge badge-dot mr-4">
                                                <i class="bg-success"></i>
                                                Disetujui
                                              </span>';
                                        break;
                                    case '2':
                                        echo '<span class="badge badge-dot mr-4">
                                                <i class="bg-danger"></i>
                                                Ditolak
                                              </span>';
                                        break;
                                    default:
                                        echo '<span class="badge badge-dot mr-4">
                                                <i class="bg-warning"></i>
                                                Menunggu Review
                                              </span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                        <?php if(isset($surat->id)): ?>
                                        <a class="dropdown-item" href="<?= base_url('dosen/penelitian/detail/' . $surat->id) ?>">
                                            <i class="fa fa-eye mr-2"></i> Detail & Review
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if(isset($surat->file_draft_proposal) && $surat->file_draft_proposal): ?>
                                        <a class="dropdown-item" href="<?= base_url('cdn/proposals/' . $surat->file_draft_proposal) ?>" target="_blank">
                                            <i class="fa fa-download mr-2"></i> Lihat Proposal
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php else: ?>
            <div class="card-body">
                <div class="text-center py-4">
                    <div class="icon icon-shape bg-gradient-secondary shadow border-radius-md mb-4 mx-auto" style="width: 60px; height: 60px;">
                        <i class="fa fa-inbox text-lg opacity-10"></i>
                    </div>
                    <h6 class="text-muted">Belum ada pengajuan surat izin penelitian</h6>
                    <p class="text-sm text-muted">
                        Pengajuan surat izin penelitian dari mahasiswa bimbingan akan muncul di sini.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Info Workflow -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h4 class="text-white mb-3">
                            <i class="fa fa-info-circle mr-2"></i>
                            Workflow Surat Izin Penelitian
                        </h4>
                        <div class="row text-white">
                            <div class="col-md-4">
                                <h6 class="text-white"><i class="fa fa-user mr-2"></i>Mahasiswa</h6>
                                <p class="text-sm text-white opacity-8">
                                    Mengajukan permohonan izin penelitian setelah seminar proposal disetujui
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-white"><i class="fa fa-user-tie mr-2"></i>Dosen Pembimbing</h6>
                                <p class="text-sm text-white opacity-8">
                                    Memberikan rekomendasi persetujuan atau penolakan terhadap permohonan
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-white"><i class="fa fa-users mr-2"></i>Staf Akademik</h6>
                                <p class="text-sm text-white opacity-8">
                                    Memproses dan menerbitkan surat izin penelitian resmi
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>