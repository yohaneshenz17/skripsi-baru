<?php
/**
 * View: Detail Seminar Proposal sebagai Penguji - FIXED VERSION
 * 
 * File: application/views/dosen/daftar_penguji/detail_proposal.php
 * 
 * PERBAIKAN:
 * 1. Link download proposal menggunakan method controller yang benar
 * 2. Button "Input Penilaian" diganti "Lihat Penilaian" untuk dosen penguji
 * 3. Konsistensi field name: file_draft_proposal vs file_proposal
 * 4. Path download menggunakan cdn/proposals/
 */
?>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="ni ni-support-16"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Back Button -->
<div class="row">
    <div class="col">
        <div class="mb-3">
            <a href="<?= base_url('dosen/daftar_penguji') ?>" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar Penguji
            </a>
        </div>
    </div>
</div>

<!-- Main Information Card -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Detail Seminar Proposal</h3>
                        <p class="text-sm mb-0">Informasi lengkap seminar proposal mahasiswa</p>
                    </div>
                    <div class="col-auto">
                        <span class="badge badge-lg badge-<?= $is_penguji1 ? 'primary' : 'info' ?>">
                            <?= $is_penguji1 ? 'Penguji 1' : 'Penguji 2' ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Mahasiswa Info -->
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">Mahasiswa</h6>
                    </div>
                    <div class="col-sm-9">
                        <div class="media align-items-center">
                            <span class="avatar avatar-lg rounded-circle mr-3">
                                <img alt="Avatar" src="<?= base_url('cdn/img/mahasiswa/default.png') ?>">
                            </span>
                            <div class="media-body">
                                <h5 class="mb-0"><?= $seminar->nama_mahasiswa ?></h5>
                                <p class="text-sm text-muted mb-0"><?= $seminar->nim ?></p>
                                <p class="text-sm text-muted mb-0"><?= $seminar->nama_prodi ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proposal Info -->
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">Judul Proposal</h6>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-sm mb-2"><?= $seminar->judul ?></p>
                        <?php if (isset($seminar->ringkasan) && $seminar->ringkasan): ?>
                        <div class="mt-3">
                            <h6 class="text-xs font-weight-bold text-uppercase text-muted">Ringkasan</h6>
                            <p class="text-sm text-muted"><?= nl2br($seminar->ringkasan) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- File Proposal - DIPERBAIKI: Menggunakan field dan path yang benar -->
                <?php 
                // Konsistensi field name - cek keduanya untuk backward compatibility
                $file_proposal = isset($seminar->file_draft_proposal) ? $seminar->file_draft_proposal : 
                                (isset($seminar->file_proposal) ? $seminar->file_proposal : null);
                ?>
                <?php if ($file_proposal): ?>
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">File Proposal</h6>
                    </div>
                    <div class="col-sm-9">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-file-pdf fa-2x text-danger mr-3"></i>
                                <div>
                                    <p class="mb-0 text-sm font-weight-bold">File Proposal Tersedia</p>
                                    <small class="text-muted"><?= pathinfo($file_proposal, PATHINFO_BASENAME) ?></small>
                                    <br><small class="text-muted">Path: uploads/seminar_proposal/proposal_files/</small>
                                </div>
                            </div>
                            <!-- PERBAIKAN: Menggunakan method controller yang benar -->
                            <a href="<?= base_url('dosen/daftar_penguji/download_proposal/' . $seminar->id) ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-file-pdf"></i> Unduh File Proposal
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">File Proposal</h6>
                    </div>
                    <div class="col-sm-9">
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle mr-2"></i>
                            <strong>File proposal belum tersedia</strong><br>
                            <small>Mahasiswa belum mengupload file proposal atau file sedang diproses.</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Jadwal Seminar -->
                <?php if (isset($seminar->tanggal_seminar) && $seminar->tanggal_seminar): ?>
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">Jadwal Seminar</h6>
                    </div>
                    <div class="col-sm-9">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label text-xs">Tanggal</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                        </div>
                                        <input type="text" class="form-control" 
                                               value="<?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label text-xs">Waktu</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-clock"></i></span>
                                        </div>
                                        <input type="text" class="form-control" 
                                               value="<?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label text-xs">Tempat</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-map-marker"></i></span>
                                        </div>
                                        <input type="text" class="form-control" 
                                               value="<?= $seminar->tempat_seminar ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Status -->
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">Status</h6>
                    </div>
                    <div class="col-sm-9">
                        <?php
                        $status_class = '';
                        $status_text = '';
                        switch($seminar->status) {
                            case 'scheduled':
                            case 'terjadwal':
                                $status_class = 'success';
                                $status_text = 'Terjadwal';
                                break;
                            case 'completed':
                            case 'selesai':
                                $status_class = 'primary';
                                $status_text = 'Selesai';
                                break;
                            case 'approved':
                            case 'disetujui':
                                $status_class = 'info';
                                $status_text = 'Disetujui';
                                break;
                            default:
                                $status_class = 'warning';
                                $status_text = 'Dalam Proses';
                        }
                        ?>
                        <span class="badge badge-<?= $status_class ?> badge-lg"><?= $status_text ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-xl-4">
        <!-- Dewan Penguji Card -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Dewan Penguji</h4>
            </div>
            <div class="card-body">
                <!-- Pembimbing -->
                <div class="media align-items-center mb-3">
                    <div class="icon icon-shape icon-sm bg-primary text-white rounded-circle">
                        <i class="fa fa-user-tie"></i>
                    </div>
                    <div class="media-body ml-3">
                        <h6 class="mb-0 text-sm">Pembimbing</h6>
                        <p class="text-sm text-muted mb-0"><?= $dewan_penguji->nama_pembimbing ?></p>
                    </div>
                </div>

                <!-- Penguji 1 -->
                <div class="media align-items-center mb-3">
                    <div class="icon icon-shape icon-sm bg-<?= $is_penguji1 ? 'success' : 'info' ?> text-white rounded-circle">
                        <i class="fa fa-gavel"></i>
                    </div>
                    <div class="media-body ml-3">
                        <h6 class="mb-0 text-sm">
                            Penguji 1 
                            <?= $is_penguji1 ? '<span class="badge badge-success badge-sm">Anda</span>' : '' ?>
                        </h6>
                        <p class="text-sm text-muted mb-0"><?= $dewan_penguji->nama_penguji1 ?></p>
                    </div>
                </div>

                <!-- Penguji 2 -->
                <div class="media align-items-center">
                    <div class="icon icon-shape icon-sm bg-<?= $is_penguji2 ? 'success' : 'info' ?> text-white rounded-circle">
                        <i class="fa fa-gavel"></i>
                    </div>
                    <div class="media-body ml-3">
                        <h6 class="mb-0 text-sm">
                            Penguji 2 
                            <?= $is_penguji2 ? '<span class="badge badge-success badge-sm">Anda</span>' : '' ?>
                        </h6>
                        <p class="text-sm text-muted mb-0"><?= $dewan_penguji->nama_penguji2 ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PERBAIKAN: Status Penilaian Card - Ubah dari "Input Penilaian" ke "Lihat Penilaian" -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Status Penilaian</h4>
            </div>
            <div class="card-body">
                <!-- PERBAIKAN: Info untuk dosen penguji (read-only) -->
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-info-circle fa-2x mr-3"></i>
                        <div>
                            <h6 class="mb-0">Informasi Penguji</h6>
                            <small>Sebagai dosen penguji, Anda dapat melihat rangkuman hasil penilaian seminar proposal ini.</small>
                        </div>
                    </div>
                </div>

                <!-- PERBAIKAN: Button "Lihat Penilaian" bukan "Input Penilaian" -->
                <div class="text-center mb-3">
                    <a href="<?= base_url('dosen/daftar_penguji/lihat_penilaian_proposal/' . $seminar->id) ?>" 
                       class="btn btn-info btn-block">
                        <i class="fa fa-eye"></i> Lihat Rangkuman Penilaian
                    </a>
                </div>

                <!-- Status Penilaian Individual (jika ada) -->
                <?php if (isset($penilaian) && $penilaian): ?>
                    <div class="alert alert-success">
                        <h6 class="alert-heading">Penilaian Tersedia</h6>
                        <p class="mb-2">Penilaian untuk seminar proposal ini sudah tersedia.</p>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Status:</small>
                                <h6 class="mb-0">
                                    <?= isset($penilaian->status_penilaian) ? ucfirst($penilaian->status_penilaian) : 'Draft' ?>
                                </h6>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Terakhir Update:</small>
                                <h6 class="mb-0 text-xs">
                                    <?= isset($penilaian->updated_at) ? date('d M Y', strtotime($penilaian->updated_at)) : '-' ?>
                                </h6>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Belum Ada Penilaian</h6>
                        <p class="mb-0">Penilaian seminar proposal belum diinput atau belum dipublikasikan.</p>
                    </div>
                <?php endif; ?>

                <!-- Informasi Tambahan -->
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <h6 class="text-muted text-xs">Pembimbing</h6>
                        <p class="mb-0 text-xs"><?= $dewan_penguji->nama_pembimbing ?></p>
                    </div>
                    <div class="col-4">
                        <h6 class="text-muted text-xs">Penguji I</h6>
                        <p class="mb-0 text-xs">
                            <?= $dewan_penguji->nama_penguji1 ?>
                            <?= $is_penguji1 ? '<br><span class="badge badge-success badge-xs">Anda</span>' : '' ?>
                        </p>
                    </div>
                    <div class="col-4">
                        <h6 class="text-muted text-xs">Penguji II</h6>
                        <p class="mb-0 text-xs">
                            <?= $dewan_penguji->nama_penguji2 ?>
                            <?= $is_penguji2 ? '<br><span class="badge badge-success badge-xs">Anda</span>' : '' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PERBAIKAN: Quick Actions - Update path download yang benar -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Aksi Cepat</h4>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical w-100">
                    <!-- PERBAIKAN: Download menggunakan method controller yang benar -->
                    <?php if ($file_proposal): ?>
                    <a href="<?= base_url('dosen/daftar_penguji/download_proposal/' . $seminar->id) ?>" 
                       class="btn btn-outline-primary btn-sm mb-2">
                        <i class="fa fa-download"></i> Unduh Proposal
                    </a>
                    <small class="text-muted mb-2">
                        <i class="fa fa-info-circle"></i>
                        File dari: uploads/seminar_proposal/proposal_files/
                    </small>
                    <?php else: ?>
                    <button class="btn btn-outline-secondary btn-sm mb-2" disabled>
                        <i class="fa fa-download"></i> File Tidak Tersedia
                    </button>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('dosen/daftar_penguji/export_pdf?type=proposal&id=' . $seminar->id) ?>" 
                       class="btn btn-outline-success btn-sm mb-2" target="_blank">
                        <i class="fa fa-file-pdf"></i> Export Detail PDF
                    </a>
                    
                    <!-- Link ke Rangkuman Penilaian -->
                    <a href="<?= base_url('dosen/daftar_penguji/lihat_penilaian_proposal/' . $seminar->id) ?>" 
                       class="btn btn-outline-info btn-sm mb-2">
                        <i class="fa fa-chart-bar"></i> Rangkuman Penilaian
                    </a>
                    
                    <?php if (in_array($seminar->status, ['completed', 'scheduled', 'selesai', 'terjadwal'])): ?>
                    <a href="<?= base_url('dosen/daftar_penguji/berita_acara_proposal/' . $seminar->id) ?>" 
                       class="btn btn-outline-warning btn-sm">
                        <i class="fa fa-file-alt"></i> Berita Acara
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>