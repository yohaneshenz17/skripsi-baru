<!-- 
Detail Seminar Proposal Dosen - Menggunakan Template Existing
File: application/views/dosen/seminar_proposal/detail.php
-->

<style>
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }
    .info-card {
        border-left: 4px solid #4e73df;
        background: #f8f9fc;
    }
    .requirement-card {
        border-left: 4px solid #1cc88a;
        background: #f8fff4;
    }
    .requirement-card.not-met {
        border-left-color: #e74a3b;
        background: #fff5f5;
    }
    .status-badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
    }
    .file-preview {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #e3e6f0;
        padding: 15px;
        border-radius: 5px;
        background: #f8f9fa;
    }
    .jurnal-item {
        border-bottom: 1px solid #e3e6f0;
        padding: 10px 0;
    }
    .jurnal-item:last-child {
        border-bottom: none;
    }
    .btn-action {
        margin: 0 5px;
    }
</style>

<!-- Breadcrumb -->
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('dosen/dashboard') ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('dosen/seminar_proposal') ?>">Seminar Proposal</a>
                </li>
                <li class="breadcrumb-item active"><?= $seminar->nama_mahasiswa ?></li>
            </ol>
        </nav>
    </div>
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

                    <div class="row">
                        <!-- Kolom Kiri: Info Mahasiswa & Proposal -->
                        <div class="col-lg-8">
                            
                            <!-- Info Mahasiswa -->
                            <div class="card shadow mb-4">
                                <div class="card-header card-header-custom py-3">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-user-graduate mr-2"></i>
                                        Informasi Mahasiswa
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="120"><strong>Nama</strong></td>
                                                    <td>: <?= $seminar->nama_mahasiswa ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>NIM</strong></td>
                                                    <td>: <?= $seminar->nim ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Program Studi</strong></td>
                                                    <td>: <?= $seminar->nama_prodi ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email</strong></td>
                                                    <td>: <?= $seminar->email_mahasiswa ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="120"><strong>No. Telepon</strong></td>
                                                    <td>: <?= $seminar->nomor_telepon ?: '-' ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Pengajuan</strong></td>
                                                    <td>: <?= date('d F Y H:i', strtotime($seminar->created_at)) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Status Saat Ini</strong></td>
                                                    <td>: 
                                                        <?php
                                                        switch($seminar->status) {
                                                            case 'submitted':
                                                            case 'review_pembimbing':
                                                                echo '<span class="badge badge-warning status-badge"><i class="fas fa-clock mr-1"></i>Menunggu Review</span>';
                                                                break;
                                                            case 'review_kaprodi':
                                                                echo '<span class="badge badge-info status-badge"><i class="fas fa-user-tie mr-1"></i>Review Kaprodi</span>';
                                                                break;
                                                            case 'approved':
                                                                echo '<span class="badge badge-success status-badge"><i class="fas fa-check mr-1"></i>Disetujui</span>';
                                                                break;
                                                            case 'rejected':
                                                                echo '<span class="badge badge-danger status-badge"><i class="fas fa-times mr-1"></i>Ditolak</span>';
                                                                break;
                                                            case 'scheduled':
                                                                echo '<span class="badge badge-primary status-badge"><i class="fas fa-calendar mr-1"></i>Terjadwal</span>';
                                                                break;
                                                            default:
                                                                echo '<span class="badge badge-secondary status-badge">Draft</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detail Proposal -->
                            <div class="card shadow mb-4">
                                <div class="card-header card-header-custom py-3">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-book mr-2"></i>
                                        Detail Proposal
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Judul Proposal:</label>
                                        <p class="text-justify"><?= $seminar->judul ?></p>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Jenis Penelitian:</label>
                                                <p><?= $seminar->jenis_penelitian ?: '-' ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Lokasi Penelitian:</label>
                                                <p><?= $seminar->lokasi_penelitian ?: '-' ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Ringkasan:</label>
                                        <div class="text-justify">
                                            <?= nl2br($seminar->ringkasan) ?>
                                        </div>
                                    </div>

                                    <?php if($seminar->keterangan_mahasiswa): ?>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Keterangan Tambahan:</label>
                                        <div class="alert alert-info">
                                            <?= nl2br($seminar->keterangan_mahasiswa) ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- File Proposal -->
                                    <?php if($seminar->file_proposal): ?>
                                    <div class="form-group">
                                        <label class="font-weight-bold">File Proposal:</label>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                            </div>
                                            <div>
                                                <strong><?= $seminar->file_proposal ?></strong>
                                                <br>
                                                <small class="text-muted">Klik untuk download</small>
                                            </div>
                                            <div class="ml-auto">
                                                <a href="<?= base_url('uploads/seminar_proposal/' . $seminar->file_proposal) ?>" 
                                                   class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-download mr-1"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Tim Penguji -->
                                    <?php if($seminar->nama_penguji1 || $seminar->nama_penguji2): ?>
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tim Penguji:</label>
                                        <ul>
                                            <li><strong>Pembimbing:</strong> <?= $this->session->userdata('nama') ?></li>
                                            <?php if($seminar->nama_penguji1): ?>
                                            <li><strong>Penguji 1:</strong> <?= $seminar->nama_penguji1 ?></li>
                                            <?php endif; ?>
                                            <?php if($seminar->nama_penguji2): ?>
                                            <li><strong>Penguji 2:</strong> <?= $seminar->nama_penguji2 ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                        <!-- Kolom Kanan: Syarat & Aksi -->
                        <div class="col-lg-4">
                            
                            <!-- Syarat Jurnal Bimbingan -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 <?= $jurnal_requirement['eligible'] ? 'bg-success' : 'bg-warning' ?> text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-clipboard-check mr-2"></i>
                                        Syarat Jurnal Bimbingan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <?php if($jurnal_requirement['eligible']): ?>
                                            <div class="mb-3">
                                                <i class="fas fa-check-circle fa-3x text-success"></i>
                                            </div>
                                            <h5 class="text-success">Memenuhi Syarat</h5>
                                            <p class="text-muted mb-0">
                                                <?= $jurnal_requirement['jurnal_validated_count'] ?> jurnal bimbingan telah divalidasi
                                            </p>
                                        <?php else: ?>
                                            <div class="mb-3">
                                                <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                                            </div>
                                            <h5 class="text-warning">Belum Memenuhi Syarat</h5>
                                            <p class="text-muted">
                                                Baru <?= $jurnal_requirement['jurnal_validated_count'] ?> dari minimal <?= $jurnal_requirement['minimum_required'] ?> jurnal
                                            </p>
                                            <small class="text-danger">
                                                Perlu <?= $jurnal_requirement['missing'] ?> jurnal lagi
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Riwayat Bimbingan Terakhir -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-info text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-history mr-2"></i>
                                        5 Bimbingan Terakhir
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if(empty($jurnal_bimbingan)): ?>
                                        <div class="text-center py-3">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada bimbingan</p>
                                        </div>
                                    <?php else: ?>
                                        <div style="max-height: 250px; overflow-y: auto;">
                                            <?php foreach($jurnal_bimbingan as $jurnal): ?>
                                                <div class="jurnal-item">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <small class="text-muted">
                                                                <?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                                                            </small>
                                                            <p class="mb-1" style="font-size: 0.9rem;">
                                                                <?= character_limiter($jurnal->topik_bimbingan, 60) ?>
                                                            </p>
                                                        </div>
                                                        <span class="badge badge-success badge-sm ml-2">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Aksi -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-primary text-white">
                                    <h6 class="m-0 font-weight-bold">
                                        <i class="fas fa-cogs mr-2"></i>
                                        Aksi
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if($seminar->status_pembimbing == 'pending'): ?>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-success btn-block mb-2" 
                                                    onclick="rekomendasi(<?= $seminar->id ?>, 'approved', '<?= $seminar->nama_mahasiswa ?>')">
                                                <i class="fas fa-check mr-2"></i>
                                                Setujui Pengajuan
                                            </button>
                                            <button class="btn btn-danger btn-block mb-3" 
                                                    onclick="rekomendasi(<?= $seminar->id ?>, 'rejected', '<?= $seminar->nama_mahasiswa ?>')">
                                                <i class="fas fa-times mr-2"></i>
                                                Tolak Pengajuan
                                            </button>
                                        </div>
                                        
                                        <?php if(!$jurnal_requirement['eligible']): ?>
                                        <div class="alert alert-warning">
                                            <small>
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                <strong>Perhatian:</strong> Mahasiswa belum memenuhi syarat minimal jurnal bimbingan.
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                        
                                    <?php else: ?>
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <h6>Sudah Direkomendasi</h6>
                                            <small>
                                                Rekomendasi: 
                                                <?php if($seminar->status_pembimbing == 'approved'): ?>
                                                    <span class="badge badge-success">Disetujui</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Ditolak</span>
                                                <?php endif; ?>
                                            </small>
                                            <?php if($seminar->tanggal_review_pembimbing): ?>
                                                <br><small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($seminar->tanggal_review_pembimbing)) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if($seminar->komentar_pembimbing): ?>
                                        <div class="alert alert-secondary">
                                            <strong>Komentar Anda:</strong><br>
                                            <em><?= nl2br($seminar->komentar_pembimbing) ?></em>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <hr>
                                    
                                    <div class="text-center">
                                        <a href="<?= base_url('dosen/seminar_proposal') ?>" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-arrow-left mr-2"></i>
                                            Kembali ke Daftar
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

<!-- Modal Rekomendasi -->
<div class="modal fade" id="modalRekomendasi" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/seminar_proposal/rekomendasi') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Rekomendasi Seminar Proposal</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="modal_seminar_id">
                    <input type="hidden" name="rekomendasi" id="modal_rekomendasi">
                    
                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <input type="text" class="form-control" id="modal_nama_mahasiswa" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Status Rekomendasi</label>
                        <div id="modal_status_text" class="alert"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Komentar/Catatan <span id="required_label" class="text-danger">*</span></label>
                        <textarea class="form-control" name="komentar_pembimbing" id="modal_komentar" rows="4" 
                                  placeholder="Berikan komentar atau catatan terkait pengajuan seminar proposal"></textarea>
                        <small class="form-text text-muted">
                            Komentar akan dikirimkan kepada mahasiswa melalui email dan sistem.
                        </small>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle mr-2"></i>Informasi:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Jika <strong>disetujui</strong>, pengajuan akan diteruskan ke Kaprodi untuk review dan validasi plagiarisme</li>
                            <li>Jika <strong>ditolak</strong>, mahasiswa perlu memperbaiki proposal sesuai catatan Anda</li>
                            <li>Mahasiswa akan mendapat notifikasi email otomatis</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn_submit">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Rekomendasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function rekomendasi(seminar_id, status, nama_mahasiswa) {
        $('#modal_seminar_id').val(seminar_id);
        $('#modal_rekomendasi').val(status);
        $('#modal_nama_mahasiswa').val(nama_mahasiswa);
        
        if (status === 'approved') {
            $('#modal_status_text').removeClass('alert-danger').addClass('alert-success');
            $('#modal_status_text').html('<i class="fas fa-check-circle mr-2"></i><strong>MENYETUJUI</strong> pengajuan seminar proposal');
            $('#btn_submit').removeClass('btn-danger').addClass('btn-success');
            $('#btn_submit').html('<i class="fas fa-check mr-2"></i>Setujui & Teruskan ke Kaprodi');
            $('#required_label').hide();
            $('#modal_komentar').prop('required', false);
            $('#modal_komentar').attr('placeholder', 'Komentar opsional untuk mahasiswa');
        } else {
            $('#modal_status_text').removeClass('alert-success').addClass('alert-danger');
            $('#modal_status_text').html('<i class="fas fa-times-circle mr-2"></i><strong>MENOLAK</strong> pengajuan seminar proposal');
            $('#btn_submit').removeClass('btn-success').addClass('btn-danger');
            $('#btn_submit').html('<i class="fas fa-times mr-2"></i>Tolak & Minta Perbaikan');
            $('#required_label').show();
            $('#modal_komentar').prop('required', true);
            $('#modal_komentar').attr('placeholder', 'Jelaskan alasan penolakan dan perbaikan yang diperlukan');
        }
        
        $('#modal_komentar').val('');
        $('#modalRekomendasi').modal('show');
    }
    
    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
    
    // Confirm before submit
    $('form').on('submit', function(e) {
        const rekomendasi = $('#modal_rekomendasi').val();
        const nama = $('#modal_nama_mahasiswa').val();
        const action = rekomendasi === 'approved' ? 'menyetujui' : 'menolak';
        
        if (!confirm(`Apakah Anda yakin akan ${action} pengajuan seminar proposal dari ${nama}?`)) {
            e.preventDefault();
            return false;
        }
    });
</script>