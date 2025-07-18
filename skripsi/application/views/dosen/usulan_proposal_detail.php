<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Detail Usulan Proposal') ?>

<?php $this->app->section() ?>

<!-- Flash Messages - ONLY show success messages -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
        <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- REMOVED: Error flash messages - tidak perlu karena hanya proposal yang berhak yang akan tampil -->

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Proposal Details -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Informasi Proposal</h3>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('dosen/usulan_proposal') ?>" class="btn btn-sm btn-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <?php if (isset($proposal->file_draft_proposal) && $proposal->file_draft_proposal): ?>
                            <a href="<?= base_url('cdn/proposals/' . $proposal->file_draft_proposal) ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-download"></i> Download Proposal
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <strong>Judul Proposal:</strong>
                        <h5 class="text-primary mb-3"><?= $proposal->judul ?></h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Jenis Penelitian:</strong>
                                <p class="text-muted">
                                    <span class="badge badge-secondary"><?= isset($proposal->jenis_penelitian) ? $proposal->jenis_penelitian : 'N/A' ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Lokasi Penelitian:</strong>
                                <p class="text-muted">
                                    <i class="fa fa-map-marker-alt text-danger"></i> <?= isset($proposal->lokasi_penelitian) ? $proposal->lokasi_penelitian : 'N/A' ?>
                                </p>
                            </div>
                        </div>
                        
                        <strong>Ringkasan:</strong>
                        <div class="bg-light p-3 rounded mb-3">
                            <p class="text-dark mb-0"><?= isset($proposal->ringkasan) ? nl2br($proposal->ringkasan) : 'Tidak ada ringkasan' ?></p>
                        </div>
                        
                        <?php if(isset($proposal->uraian_masalah) && $proposal->uraian_masalah): ?>
                        <strong>Uraian Masalah:</strong>
                        <div class="bg-light p-3 rounded mb-3">
                            <p class="text-dark mb-0"><?= nl2br($proposal->uraian_masalah) ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Tanggal Pengajuan:</strong>
                                <p class="text-muted"><?= date('d F Y H:i', strtotime($proposal->created_at)) ?> WIT</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Tanggal Penetapan:</strong>
                                <p class="text-muted">
                                    <?= isset($proposal->tanggal_penetapan) && $proposal->tanggal_penetapan ? date('d F Y H:i', strtotime($proposal->tanggal_penetapan)) . ' WIT' : 'Belum ditetapkan' ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if(isset($proposal->komentar_kaprodi) && $proposal->komentar_kaprodi): ?>
                        <hr class="my-4">
                        <strong>Komentar Kaprodi:</strong>
                        <div class="bg-info p-3 rounded">
                            <p class="text-white mb-0"><?= nl2br($proposal->komentar_kaprodi) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Mahasiswa Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Informasi Mahasiswa</h3>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <?php
                    $foto_mahasiswa = (!empty($proposal->foto_mahasiswa)) ? $proposal->foto_mahasiswa : 'default.png';
                    $foto_path = base_url('cdn/img/mahasiswa/' . $foto_mahasiswa);
                    ?>
                    <img src="<?= $foto_path ?>" alt="Foto <?= $proposal->nama_mahasiswa ?>" class="rounded-circle mb-3" width="100" height="100" style="object-fit: cover;">
                    <h4 class="mb-1"><?= $proposal->nama_mahasiswa ?></h4>
                    <p class="text-muted mb-0">NIM: <?= $proposal->nim ?></p>
                    <p class="text-muted mb-3"><?= $proposal->nama_prodi ?></p>
                </div>
                
                <hr class="my-3">
                
                <div class="row">
                    <div class="col-12 mb-2">
                        <strong>Email:</strong>
                        <p class="text-muted mb-0"><?= $proposal->email_mahasiswa ?></p>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>No. Telepon:</strong>
                        <p class="text-muted mb-0"><?= $proposal->nomor_telepon ?: 'Tidak tersedia' ?></p>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Tempat, Tanggal Lahir:</strong>
                        <p class="text-muted mb-0">
                            <?= isset($proposal->tempat_lahir) ? $proposal->tempat_lahir : 'N/A' ?>, 
                            <?= isset($proposal->tanggal_lahir) ? date('d F Y', strtotime($proposal->tanggal_lahir)) : 'N/A' ?>
                        </p>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Jenis Kelamin:</strong>
                        <p class="text-muted mb-0"><?= isset($proposal->jenis_kelamin) ? $proposal->jenis_kelamin : 'N/A' ?></p>
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Alamat:</strong>
                        <p class="text-muted mb-0"><?= isset($proposal->alamat) ? $proposal->alamat : 'N/A' ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action Panel - Only show if proposal needs response -->
        <?php if ((!isset($proposal->status_pembimbing) || $proposal->status_pembimbing == '0')): ?>
        <div class="card">
            <div class="card-header bg-gradient-warning">
                <h3 class="mb-0 text-white">
                    <i class="fa fa-exclamation-triangle"></i> Persetujuan Pembimbing
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <strong>Perhatian!</strong> Kaprodi telah menunjuk Anda sebagai pembimbing untuk proposal ini. 
                    Silakan berikan persetujuan atau penolakan dengan alasan yang jelas.
                </div>
                
                <form method="post" action="<?= base_url('dosen/usulan_proposal/proses_persetujuan') ?>" id="formPersetujuan">
                    <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                    
                    <div class="form-group">
                        <label class="form-control-label">Keputusan Anda <span class="text-danger">*</span></label>
                        <div class="custom-control custom-radio">
                            <input name="status_pembimbing" class="custom-control-input" id="setuju" type="radio" value="1" required>
                            <label class="custom-control-label" for="setuju">
                                <span class="text-success"><i class="fa fa-check"></i> Setuju menjadi pembimbing</span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input name="status_pembimbing" class="custom-control-input" id="tolak" type="radio" value="2" required>
                            <label class="custom-control-label" for="tolak">
                                <span class="text-danger"><i class="fa fa-times"></i> Tolak penunjukan</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Komentar</label>
                        <textarea class="form-control" name="komentar_pembimbing" rows="4" 
                                  placeholder="Berikan komentar atau alasan (wajib diisi jika menolak)"></textarea>
                        <small class="form-text text-muted">
                            Komentar akan dikirim ke mahasiswa dan kaprodi melalui email
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block" id="btnSubmit">
                            <i class="fa fa-paper-plane"></i> Kirim Persetujuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Status sudah direspon -->
        <div class="card">
            <div class="card-header bg-gradient-<?= $proposal->status_pembimbing == '1' ? 'success' : 'danger' ?>">
                <h3 class="mb-0 text-white">
                    <i class="fa fa-<?= $proposal->status_pembimbing == '1' ? 'check' : 'times' ?>"></i> 
                    Status Persetujuan
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-<?= $proposal->status_pembimbing == '1' ? 'success' : 'danger' ?>" role="alert">
                    <strong>
                        <?= $proposal->status_pembimbing == '1' ? '✅ Disetujui' : '❌ Ditolak' ?>
                    </strong>
                    <br>
                    Anda telah <?= $proposal->status_pembimbing == '1' ? 'menyetujui' : 'menolak' ?> penunjukan sebagai pembimbing
                </div>
                
                <?php if (isset($proposal->komentar_pembimbing) && $proposal->komentar_pembimbing): ?>
                <div class="mt-3">
                    <strong>Komentar Anda:</strong>
                    <p class="text-muted mt-1"><?= nl2br($proposal->komentar_pembimbing) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($proposal->tanggal_respon_pembimbing) && $proposal->tanggal_respon_pembimbing): ?>
                <small class="text-muted">
                    Direspon pada: <?= date('d F Y H:i', strtotime($proposal->tanggal_respon_pembimbing)) ?> WIT
                </small>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Timeline Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Timeline Proposal</h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-one-side">
                    <div class="timeline-block">
                        <span class="timeline-step badge-success">
                            <i class="fa fa-user"></i>
                        </span>
                        <div class="timeline-content">
                            <small class="text-muted">
                                <?= date('d M Y H:i', strtotime($proposal->created_at)) ?>
                            </small>
                            <h6 class="text-sm mt-0 mb-0">Proposal Diajukan</h6>
                            <p class="text-muted text-sm mt-1 mb-0">
                                Mahasiswa mengajukan proposal skripsi
                            </p>
                        </div>
                    </div>
                    
                    <?php if (isset($proposal->tanggal_review_kaprodi) && $proposal->tanggal_review_kaprodi): ?>
                    <div class="timeline-block">
                        <span class="timeline-step badge-info">
                            <i class="fa fa-check"></i>
                        </span>
                        <div class="timeline-content">
                            <small class="text-muted">
                                <?= date('d M Y H:i', strtotime($proposal->tanggal_review_kaprodi)) ?>
                            </small>
                            <h6 class="text-sm mt-0 mb-0">Disetujui Kaprodi</h6>
                            <p class="text-muted text-sm mt-1 mb-0">
                                Kaprodi menyetujui proposal
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($proposal->tanggal_penetapan) && $proposal->tanggal_penetapan): ?>
                    <div class="timeline-block">
                        <span class="timeline-step badge-warning">
                            <i class="fa fa-user-plus"></i>
                        </span>
                        <div class="timeline-content">
                            <small class="text-muted">
                                <?= date('d M Y H:i', strtotime($proposal->tanggal_penetapan)) ?>
                            </small>
                            <h6 class="text-sm mt-0 mb-0">Pembimbing Ditetapkan</h6>
                            <p class="text-muted text-sm mt-1 mb-0">
                                Kaprodi menunjuk Anda sebagai pembimbing
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ((!isset($proposal->status_pembimbing) || $proposal->status_pembimbing == '0')): ?>
                    <div class="timeline-block">
                        <span class="timeline-step badge-secondary">
                            <i class="fa fa-clock"></i>
                        </span>
                        <div class="timeline-content">
                            <small class="text-muted">Sekarang</small>
                            <h6 class="text-sm mt-0 mb-0">Menunggu Persetujuan</h6>
                            <p class="text-muted text-sm mt-1 mb-0">
                                Dosen memberikan persetujuan pembimbingan
                            </p>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="timeline-block">
                        <span class="timeline-step badge-<?= $proposal->status_pembimbing == '1' ? 'success' : 'danger' ?>">
                            <i class="fa fa-<?= $proposal->status_pembimbing == '1' ? 'check' : 'times' ?>"></i>
                        </span>
                        <div class="timeline-content">
                            <small class="text-muted">
                                <?= isset($proposal->tanggal_respon_pembimbing) ? date('d M Y H:i', strtotime($proposal->tanggal_respon_pembimbing)) : 'N/A' ?>
                            </small>
                            <h6 class="text-sm mt-0 mb-0">
                                <?= $proposal->status_pembimbing == '1' ? 'Disetujui Pembimbing' : 'Ditolak Pembimbing' ?>
                            </h6>
                            <p class="text-muted text-sm mt-1 mb-0">
                                <?= $proposal->status_pembimbing == '1' ? 'Pembimbing menyetujui penunjukan' : 'Pembimbing menolak penunjukan' ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
$(document).ready(function() {
    // Form validation
    $('#formPersetujuan').on('submit', function(e) {
        var status = $('input[name="status_pembimbing"]:checked').val();
        var komentar = $('textarea[name="komentar_pembimbing"]').val().trim();
        
        // Jika menolak, komentar wajib diisi
        if (status == '2' && komentar == '') {
            e.preventDefault();
            alert('Komentar wajib diisi jika menolak penunjukan pembimbing!');
            $('textarea[name="komentar_pembimbing"]').focus();
            return false;
        }
        
        // Konfirmasi sebelum submit
        var konfirmasi = '';
        if (status == '1') {
            konfirmasi = 'Anda akan MENYETUJUI penunjukan sebagai pembimbing. Lanjutkan?';
        } else if (status == '2') {
            konfirmasi = 'Anda akan MENOLAK penunjukan sebagai pembimbing. Lanjutkan?';
        }
        
        if (konfirmasi && !confirm(konfirmasi)) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        $('#btnSubmit').html('<i class="fa fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);
    });
    
    // Radio button change handler
    $('input[name="status_pembimbing"]').on('change', function() {
        var status = $(this).val();
        var komentarTextarea = $('textarea[name="komentar_pembimbing"]');
        
        if (status == '2') {
            komentarTextarea.attr('required', true);
            komentarTextarea.attr('placeholder', 'Alasan penolakan wajib diisi');
            komentarTextarea.parent().find('label').html('Komentar/Alasan Penolakan <span class="text-danger">*</span>');
        } else {
            komentarTextarea.removeAttr('required');
            komentarTextarea.attr('placeholder', 'Berikan komentar atau catatan (opsional)');
            komentarTextarea.parent().find('label').html('Komentar');
        }
    });
});
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>