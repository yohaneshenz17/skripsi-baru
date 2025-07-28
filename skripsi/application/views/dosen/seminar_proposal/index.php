<!-- 
Dashboard Seminar Proposal Dosen - FIXED VERSION
File: application/views/dosen/seminar_proposal/index.php
-->

<style>
    .card-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 15px;
    }
    .card-stats-2 {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        border: none;
        border-radius: 15px;
    }
    .card-stats-3 {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border: none;
        border-radius: 15px;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: all 0.2s;
    }
    .btn-action {
        margin: 0 2px;
        padding: 5px 10px;
        font-size: 0.8rem;
    }
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom: none;
    }
</style>

<!-- Content untuk template existing -->
<div class="container-fluid">
                    
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-presentation mr-2"></i>
                            Seminar Proposal
                        </h1>
                        <div class="d-none d-lg-inline-block">
                            <span class="text-muted">Dashboard untuk mengelola seminar proposal mahasiswa bimbingan</span>
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

                    <?php if($this->session->flashdata('info')): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle mr-2"></i>
                            <?= $this->session->flashdata('info') ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card card-stats h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold mb-1">PERLU REVIEW</div>
                                            <div class="h5 mb-0 font-weight-bold">
                                                <?= isset($stats->perlu_review) ? $stats->perlu_review : 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock-o fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card card-stats-2 h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold mb-1">DIREKOMENDASI BULAN INI</div>
                                            <div class="h5 mb-0 font-weight-bold">
                                                <?= isset($stats->direkomendasi_bulan_ini) ? $stats->direkomendasi_bulan_ini : 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card card-stats-3 h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold mb-1">PERLU PENILAIAN</div>
                                            <div class="h5 mb-0 font-weight-bold">
                                                <?= isset($stats->perlu_penilaian) ? $stats->perlu_penilaian : 0 ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-edit fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pengajuan Perlu Review -->
                    <div class="card shadow mb-4">
                        <div class="card-header card-header-custom py-3">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-hourglass-half mr-2"></i>
                                Pengajuan Perlu Review (<?= count($pengajuan_review) ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if(empty($pengajuan_review)): ?>
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="fas fa-inbox fa-3x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">Tidak Ada Pengajuan Baru</h5>
                                    <p class="text-muted mb-0">Saat ini tidak ada pengajuan seminar proposal yang perlu direview.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Mahasiswa</th>
                                                <th>Judul Proposal</th>
                                                <th>Tanggal Pengajuan</th>
                                                <th>Status</th>
                                                <th width="200">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($pengajuan_review as $pengajuan): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm mr-3">
                                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                                    <?= strtoupper(substr($pengajuan->nama_mahasiswa, 0, 1)) ?>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="font-weight-bold"><?= $pengajuan->nama_mahasiswa ?></div>
                                                                <small class="text-muted"><?= $pengajuan->nim ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-wrap" style="max-width: 300px;">
                                                            <strong><?= truncate_text($pengajuan->judul, 60) ?></strong>
                                                            <br><small class="text-muted"><?= $pengajuan->nama_prodi ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?= date('d/m/Y H:i', strtotime($pengajuan->created_at)) ?>
                                                        <br><small class="text-muted"><?= timespan(strtotime($pengajuan->created_at), time()) ?> yang lalu</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-warning status-badge">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Menunggu Review
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="<?= base_url('dosen/seminar_proposal/detail/' . $pengajuan->id) ?>" 
                                                               class="btn btn-info btn-action" title="Lihat Detail">
                                                                <i class="fas fa-eye"></i> Detail
                                                            </a>
                                                            <button class="btn btn-success btn-action" 
                                                                    onclick="rekomendasi(<?= $pengajuan->id ?>, 'approved', '<?= $pengajuan->nama_mahasiswa ?>')" 
                                                                    title="Setujui">
                                                                <i class="fas fa-check"></i> Setujui
                                                            </button>
                                                            <button class="btn btn-danger btn-action" 
                                                                    onclick="rekomendasi(<?= $pengajuan->id ?>, 'rejected', '<?= $pengajuan->nama_mahasiswa ?>')" 
                                                                    title="Tolak">
                                                                <i class="fas fa-times"></i> Tolak
                                                            </button>
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

                    <!-- Seminar Perlu Penilaian -->
                    <?php if(!empty($perlu_penilaian)): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-edit mr-2"></i>
                                Seminar Perlu Penilaian (<?= count($perlu_penilaian) ?>)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>Judul Proposal</th>
                                            <th>Tanggal Seminar</th>
                                            <th>Status</th>
                                            <th width="150">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($perlu_penilaian as $seminar): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm mr-3">
                                                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                                <?= strtoupper(substr($seminar->nama_mahasiswa, 0, 1)) ?>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold"><?= $seminar->nama_mahasiswa ?></div>
                                                            <small class="text-muted"><?= $seminar->nim ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-wrap" style="max-width: 300px;">
                                                        <strong><?= truncate_text($seminar->judul, 60) ?></strong>
                                                        <br><small class="text-muted"><?= $seminar->nama_prodi ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y H:i', strtotime($seminar->tanggal_seminar . ' ' . $seminar->jam_seminar)) ?>
                                                    <br><small class="text-muted"><?= $seminar->tempat_seminar ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info status-badge">
                                                        <i class="fas fa-calendar-check mr-1"></i>
                                                        Selesai - Perlu Nilai
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?= base_url('dosen/seminar_proposal/penilaian/' . $seminar->id) ?>" 
                                                       class="btn btn-primary btn-sm btn-action" title="Input Penilaian">
                                                        <i class="fas fa-edit"></i> Input Nilai
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Riwayat Rekomendasi -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-history mr-2"></i>
                                Riwayat Rekomendasi (10 Terakhir)
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if(empty($riwayat_rekomendasi)): ?>
                                <div class="text-center py-4">
                                    <div class="mb-3">
                                        <i class="fas fa-history fa-3x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">Belum Ada Riwayat</h5>
                                    <p class="text-muted mb-0">Riwayat rekomendasi akan tampil setelah Anda memberikan rekomendasi pertama.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Mahasiswa</th>
                                                <th>Judul Proposal</th>
                                                <th>Tanggal Review</th>
                                                <th>Keputusan</th>
                                                <th>Komentar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($riwayat_rekomendasi as $riwayat): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm mr-3">
                                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                                    <?= strtoupper(substr($riwayat->nama_mahasiswa, 0, 1)) ?>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <div class="font-weight-bold"><?= $riwayat->nama_mahasiswa ?></div>
                                                                <small class="text-muted"><?= $riwayat->nim ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="text-wrap" style="max-width: 250px;">
                                                            <?= truncate_text($riwayat->judul, 50) ?>
                                                            <br><small class="text-muted"><?= $riwayat->nama_prodi ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?= date('d/m/Y H:i', strtotime($riwayat->tanggal_review_pembimbing)) ?>
                                                    </td>
                                                    <td>
                                                        <?php if($riwayat->status_pembimbing == 'approved'): ?>
                                                            <span class="badge badge-success status-badge">
                                                                <i class="fas fa-check mr-1"></i>
                                                                Disetujui
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger status-badge">
                                                                <i class="fas fa-times mr-1"></i>
                                                                Ditolak
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if($riwayat->komentar_pembimbing): ?>
                                                            <div class="text-wrap" style="max-width: 200px;">
                                                                <?= truncate_text($riwayat->komentar_pembimbing, 40) ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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