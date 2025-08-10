<?php
/**
 * View: Detail Seminar Skripsi sebagai Penguji
 * 
 * File: application/views/dosen/daftar_penguji/detail_skripsi.php
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
                        <h3 class="mb-0">Detail Seminar Skripsi</h3>
                        <p class="text-sm mb-0">Informasi lengkap seminar skripsi mahasiswa</p>
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

                <!-- Skripsi Info -->
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">Judul Skripsi</h6>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-sm mb-2"><?= $seminar->judul ?></p>
                        <?php if ($seminar->ringkasan): ?>
                        <div class="mt-3">
                            <h6 class="text-xs font-weight-bold text-uppercase text-muted">Ringkasan</h6>
                            <p class="text-sm text-muted"><?= nl2br($seminar->ringkasan) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- File Skripsi -->
                <?php if ($seminar->file_skripsi): ?>
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">File Skripsi</h6>
                    </div>
                    <div class="col-sm-9">
                        <a href="<?= base_url('uploads/skripsi/' . $seminar->file_skripsi) ?>" 
                           class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fa fa-file-pdf"></i> Unduh File Skripsi
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Surat Keterangan Penelitian -->
                <?php if ($seminar->surat_keterangan_penelitian): ?>
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">Surat Penelitian</h6>
                    </div>
                    <div class="col-sm-9">
                        <a href="<?= base_url('uploads/penelitian/' . $seminar->surat_keterangan_penelitian) ?>" 
                           class="btn btn-sm btn-outline-success" target="_blank">
                            <i class="fa fa-file-alt"></i> Unduh Surat Keterangan Penelitian
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Jadwal Seminar -->
                <?php if ($seminar->tanggal_seminar): ?>
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

                <!-- Keterangan Mahasiswa -->
                <?php if ($seminar->keterangan_mahasiswa): ?>
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <h6 class="text-sm font-weight-bold text-uppercase">Keterangan</h6>
                    </div>
                    <div class="col-sm-9">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Catatan dari Mahasiswa</h6>
                            <p class="mb-0"><?= nl2br($seminar->keterangan_mahasiswa) ?></p>
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
                                $status_class = 'success';
                                $status_text = 'Terjadwal';
                                break;
                            case 'completed':
                                $status_class = 'primary';
                                $status_text = 'Selesai';
                                break;
                            case 'approved':
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

        <!-- Penilaian Card -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Status Penilaian</h4>
            </div>
            <div class="card-body">
                <?php if ($penilaian): ?>
                    <div class="alert alert-success">
                        <h6 class="alert-heading">Sudah Dinilai</h6>
                        <p class="mb-2">Anda telah memberikan penilaian untuk seminar skripsi ini.</p>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Nilai Akhir:</small>
                                <h5 class="mb-0"><?= number_format($penilaian->nilai_akhir, 1) ?> (<?= $penilaian->nilai_huruf ?>)</h5>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Rekomendasi:</small>
                                <h6 class="mb-0 text-capitalize"><?= str_replace('_', ' ', $penilaian->rekomendasi) ?></h6>
                            </div>
                        </div>
                        <?php if ($penilaian->status_penilaian == 'published'): ?>
                            <small class="text-muted">
                                <i class="fa fa-check text-success"></i>
                                Dipublikasi pada <?= date('d M Y H:i', strtotime($penilaian->published_at)) ?>
                            </small>
                        <?php else: ?>
                            <small class="text-warning">
                                <i class="fa fa-clock"></i>
                                Masih dalam status draft
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Catatan Penilaian -->
                    <?php if ($penilaian->catatan_pendahuluan || $penilaian->catatan_tinjauan_pustaka || 
                              $penilaian->catatan_metodologi || $penilaian->catatan_hasil_pembahasan || 
                              $penilaian->catatan_kesimpulan || $penilaian->catatan_umum): ?>
                    <div class="mt-3">
                        <h6 class="text-sm font-weight-bold">Catatan Penilaian:</h6>
                        <?php if ($penilaian->catatan_pendahuluan): ?>
                        <p class="text-xs"><strong>Pendahuluan:</strong> <?= $penilaian->catatan_pendahuluan ?></p>
                        <?php endif; ?>
                        <?php if ($penilaian->catatan_tinjauan_pustaka): ?>
                        <p class="text-xs"><strong>Tinjauan Pustaka:</strong> <?= $penilaian->catatan_tinjauan_pustaka ?></p>
                        <?php endif; ?>
                        <?php if ($penilaian->catatan_metodologi): ?>
                        <p class="text-xs"><strong>Metodologi:</strong> <?= $penilaian->catatan_metodologi ?></p>
                        <?php endif; ?>
                        <?php if ($penilaian->catatan_hasil_pembahasan): ?>
                        <p class="text-xs"><strong>Hasil & Pembahasan:</strong> <?= $penilaian->catatan_hasil_pembahasan ?></p>
                        <?php endif; ?>
                        <?php if ($penilaian->catatan_kesimpulan): ?>
                        <p class="text-xs"><strong>Kesimpulan:</strong> <?= $penilaian->catatan_kesimpulan ?></p>
                        <?php endif; ?>
                        <?php if ($penilaian->catatan_umum): ?>
                        <p class="text-xs"><strong>Catatan Umum:</strong> <?= $penilaian->catatan_umum ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Belum Dinilai</h6>
                        <p class="mb-0">Anda belum memberikan penilaian untuk seminar skripsi ini.</p>
                    </div>
                    
                    <?php if ($seminar->status == 'completed' || $seminar->status == 'scheduled'): ?>
                    <div class="text-center mt-3">
                        <a href="<?= base_url('dosen/penilaian_skripsi/input/' . $seminar->id) ?>" 
                           class="btn btn-success">
                            <i class="fa fa-edit"></i> Input Penilaian
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Aksi Cepat</h4>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical w-100">
                    <?php if ($seminar->file_skripsi): ?>
                    <a href="<?= base_url('uploads/skripsi/' . $seminar->file_skripsi) ?>" 
                       class="btn btn-outline-primary btn-sm mb-2" target="_blank">
                        <i class="fa fa-download"></i> Unduh Skripsi
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($seminar->surat_keterangan_penelitian): ?>
                    <a href="<?= base_url('uploads/penelitian/' . $seminar->surat_keterangan_penelitian) ?>" 
                       class="btn btn-outline-success btn-sm mb-2" target="_blank">
                        <i class="fa fa-file-alt"></i> Unduh Surat Penelitian
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= base_url('dosen/daftar_penguji/export_pdf?type=skripsi&id=' . $seminar->id) ?>" 
                       class="btn btn-outline-success btn-sm mb-2" target="_blank">
                        <i class="fa fa-file-pdf"></i> Export Detail PDF
                    </a>
                    
                    <?php if ($seminar->status == 'completed' || $seminar->status == 'scheduled'): ?>
                    <a href="<?= base_url('dosen/berita_acara_skripsi/' . $seminar->id) ?>" 
                       class="btn btn-outline-info btn-sm">
                        <i class="fa fa-file-alt"></i> Lihat Berita Acara
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>