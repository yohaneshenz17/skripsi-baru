<?php
/**
 * View: application/views/dosen/penelitian/detail.php
 * Halaman detail permohonan penelitian untuk review dosen
 */
?>

<!-- Page Header -->
<div class="header bg-primary pb-6">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Detail Permohonan Penelitian</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('dosen/penelitian') ?>">Penelitian</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 col-5 text-right">
                    <a href="<?= base_url('dosen/penelitian') ?>" class="btn btn-sm btn-neutral">
                        <i class="fa fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="container-fluid mt--6">

    <!-- Alert Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="fa fa-check-circle"></i></span>
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <!-- Detail Permohonan -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">
                                <i class="fa fa-file-alt text-primary mr-2"></i>
                                Detail Permohonan
                            </h3>
                        </div>
                        <div class="col-auto">
                            <span class="badge badge-pill 
                                <?php
                                switch($permohonan->status_pembimbing) {
                                    case 'pending': echo 'badge-warning'; break;
                                    case 'approved': echo 'badge-success'; break;
                                    case 'rejected': echo 'badge-danger'; break;
                                    default: echo 'badge-secondary';
                                }
                                ?>">
                                <?php
                                switch($permohonan->status_pembimbing) {
                                    case 'pending': echo 'Menunggu Review'; break;
                                    case 'approved': echo 'Disetujui'; break;
                                    case 'rejected': echo 'Ditolak'; break;
                                    default: echo 'Status Tidak Dikenal';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    
                    <!-- Data Mahasiswa -->
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Nama Mahasiswa</strong></td>
                                    <td>: <?= $permohonan->nama_mahasiswa ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIM</strong></td>
                                    <td>: <?= $permohonan->nim ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Semester</strong></td>
                                    <td>: <?= $permohonan->semester ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Program Studi</strong></td>
                                    <td>: <?= $permohonan->program_studi ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Tempat Penelitian</strong></td>
                                    <td>: <?= $permohonan->tempat_penelitian ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Mulai</strong></td>
                                    <td>: <?= date('d F Y', strtotime($permohonan->tanggal_mulai_penelitian)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Selesai</strong></td>
                                    <td>: <?= date('d F Y', strtotime($permohonan->tanggal_selesai_penelitian)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Pengajuan</strong></td>
                                    <td>: <?= date('d F Y H:i', strtotime($permohonan->created_at)) ?> WIT</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Judul Skripsi -->
                    <div class="mt-4">
                        <h6><strong>Judul Skripsi:</strong></h6>
                        <div class="bg-light p-3 rounded">
                            <p class="text-dark mb-0"><?= $permohonan->judul_skripsi_terbaru ?></p>
                        </div>
                    </div>

                    <!-- File Proposal Revisi -->
                    <?php if ($permohonan->file_proposal_revisi): ?>
                    <div class="mt-4">
                        <h6><strong>File Proposal Revisi:</strong></h6>
                        <div class="bg-light p-3 rounded">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm bg-primary rounded-circle mr-3">
                                            <i class="fa fa-file-pdf text-white"></i>
                                        </div>
                                        <div class="media-body">
                                            <span class="text-dark font-weight-bold"><?= $permohonan->file_proposal_revisi ?></span>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar mr-1"></i>
                                                Diupload: <?= date('d F Y H:i', strtotime($permohonan->created_at)) ?> WIT
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <a href="<?= base_url('dosen/penelitian/view_file/' . $permohonan->id) ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye mr-1"></i> Lihat File
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Riwayat Review (jika sudah ada) -->
                    <?php if ($permohonan->status_pembimbing != 'pending'): ?>
                    <div class="mt-4">
                        <h6><strong>Riwayat Review:</strong></h6>
                        <div class="bg-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?> bg-gradient-light p-3 rounded">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="media align-items-center">
                                        <div class="avatar avatar-sm bg-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?> rounded-circle mr-3">
                                            <i class="fa <?= $permohonan->status_pembimbing == 'approved' ? 'fa-check' : 'fa-times' ?> text-white"></i>
                                        </div>
                                        <div class="media-body">
                                            <span class="text-dark font-weight-bold">
                                                <?= $permohonan->status_pembimbing == 'approved' ? 'Disetujui' : 'Ditolak' ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fa fa-calendar mr-1"></i>
                                                <?= date('d F Y H:i', strtotime($permohonan->tanggal_review_pembimbing)) ?> WIT
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($permohonan->komentar_pembimbing): ?>
                            <div class="mt-3">
                                <strong>Komentar:</strong>
                                <p class="text-dark mb-0 mt-1"><?= nl2br($permohonan->komentar_pembimbing) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Form Review -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fa fa-clipboard-check text-warning mr-2"></i>
                        <?= $permohonan->status_pembimbing == 'pending' ? 'Review Permohonan' : 'Detail Review' ?>
                    </h3>
                </div>
                
                <div class="card-body">
                    
                    <?php if ($permohonan->status_pembimbing == 'pending'): ?>
                    <!-- Form Review untuk pending -->
                    <form method="post" action="<?= base_url('dosen/penelitian/review') ?>" id="reviewForm">
                        <input type="hidden" name="permohonan_id" value="<?= $permohonan->id ?>">
                        <input type="hidden" name="status_review" value="">
                        
                        <div class="form-group">
                            <label class="form-control-label">
                                <strong>Keputusan Review:</strong>
                            </label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="approve" name="status_review" value="approved" class="custom-control-input">
                                <label class="custom-control-label" for="approve">
                                    <i class="fa fa-check-circle text-success mr-2"></i>
                                    <strong>Setujui Permohonan</strong>
                                    <br><small class="text-muted">Permohonan akan diteruskan ke staf untuk diproses</small>
                                </label>
                            </div>
                            <div class="custom-control custom-radio mt-3">
                                <input type="radio" id="reject" name="status_review" value="rejected" class="custom-control-input">
                                <label class="custom-control-label" for="reject">
                                    <i class="fa fa-times-circle text-danger mr-2"></i>
                                    <strong>Tolak Permohonan</strong>
                                    <br><small class="text-muted">Mahasiswa akan diminta melakukan perbaikan</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-control-label" for="komentar_pembimbing">
                                <strong>Komentar/Catatan:</strong>
                            </label>
                            <textarea class="form-control" 
                                      id="komentar_pembimbing" 
                                      name="komentar_pembimbing" 
                                      rows="4" 
                                      placeholder="Berikan komentar atau catatan untuk mahasiswa..."></textarea>
                            <small class="form-text text-muted">
                                <i class="fa fa-info-circle mr-1"></i>
                                Komentar wajib diisi jika menolak permohonan
                            </small>
                        </div>
                        
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save mr-2"></i>
                                Simpan Review
                            </button>
                        </div>
                    </form>
                    
                    <?php else: ?>
                    <!-- Info review yang sudah dilakukan -->
                    <div class="text-center">
                        <div class="avatar avatar-lg bg-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?> rounded-circle mx-auto mb-3">
                            <i class="fa <?= $permohonan->status_pembimbing == 'approved' ? 'fa-check' : 'fa-times' ?> text-white text-lg"></i>
                        </div>
                        
                        <h5 class="text-<?= $permohonan->status_pembimbing == 'approved' ? 'success' : 'danger' ?>">
                            Permohonan <?= $permohonan->status_pembimbing == 'approved' ? 'Disetujui' : 'Ditolak' ?>
                        </h5>
                        
                        <p class="text-muted text-sm">
                            Review dilakukan pada:<br>
                            <strong><?= date('d F Y H:i', strtotime($permohonan->tanggal_review_pembimbing)) ?> WIT</strong>
                        </p>
                        
                        <?php if ($permohonan->status_pembimbing == 'approved'): ?>
                        <div class="alert alert-success">
                            <i class="fa fa-info-circle mr-2"></i>
                            Permohonan telah diteruskan ke staf untuk diproses surat izin penelitian.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fa fa-info-circle mr-2"></i>
                            Mahasiswa akan mendapat notifikasi untuk melakukan perbaikan dan mengajukan ulang.
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>

            <!-- Info Panel -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fa fa-info-circle text-info mr-2"></i>
                        Informasi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-one-side">
                        <div class="timeline-block">
                            <span class="timeline-step badge-success">
                                <i class="fa fa-check"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm font-weight-bold">Jika Disetujui:</h6>
                                <p class="text-sm text-muted mb-0">
                                    - Notifikasi dikirim ke staf<br>
                                    - Staf akan memproses surat izin<br>
                                    - Mahasiswa dapat download surat
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-block">
                            <span class="timeline-step badge-danger">
                                <i class="fa fa-times"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-sm font-weight-bold">Jika Ditolak:</h6>
                                <p class="text-sm text-muted mb-0">
                                    - Notifikasi dikirim ke mahasiswa<br>
                                    - Mahasiswa melakukan perbaikan<br>
                                    - Mahasiswa mengajukan ulang
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

</div>