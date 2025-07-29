<!-- File: application/views/dosen/seminar_proposal/index.php -->
<!-- Production Ready View - Simple & Robust -->

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-presentation mr-2"></i>
            Seminar Proposal
        </h1>
        <div class="text-right">
            <small class="text-muted">Dashboard untuk mengelola seminar proposal mahasiswa bimbingan</small>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle mr-2"></i>
            <?= $this->session->flashdata('info') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Perlu Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['perlu_review']) ? $stats['perlu_review'] : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Perlu Penilaian
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['perlu_penilaian']) ? $stats['perlu_penilaian'] : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Direkomendasikan
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['total_direkomendasikan']) ? $stats['total_direkomendasikan'] : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Penilaian Published
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= isset($stats['total_penilaian_published']) ? $stats['total_penilaian_published'] : 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengajuan Perlu Review -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-clock mr-2"></i>
                Pengajuan Seminar Proposal - Perlu Review Anda
                <?php if (!empty($pengajuan_review)): ?>
                    <span class="badge badge-warning ml-2"><?= count($pengajuan_review) ?></span>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($pengajuan_review)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Mahasiswa</th>
                                <th width="35%">Judul Proposal</th>
                                <th width="15%">Tanggal Ajukan</th>
                                <th width="15%">File Proposal</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pengajuan_review as $index => $pengajuan): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= $pengajuan->nama_mahasiswa ?></strong><br>
                                        <small class="text-muted"><?= $pengajuan->nim ?></small>
                                    </td>
                                    <td>
                                        <span class="text-justify"><?= $pengajuan->judul ?></span>
                                        <?php if (!empty($pengajuan->keterangan_mahasiswa)): ?>
                                            <br><small class="text-info">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                <?= $pengajuan->keterangan_mahasiswa ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d M Y H:i', strtotime($pengajuan->created_at)) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pengajuan->file_proposal)): ?>
                                            <a href="<?= base_url('uploads/seminar_proposal/' . $pengajuan->file_proposal) ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file-pdf mr-1"></i> Lihat
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('dosen/seminar_proposal/detail/' . $pengajuan->id) ?>" 
                                           class="btn btn-sm btn-info mb-1">
                                            <i class="fas fa-eye mr-1"></i> Detail
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success mb-1" 
                                                onclick="openRekomendasi(<?= $pengajuan->id ?>, '<?= addslashes($pengajuan->nama_mahasiswa) ?>', 'approved')">
                                            <i class="fas fa-check mr-1"></i> Setujui
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger mb-1" 
                                                onclick="openRekomendasi(<?= $pengajuan->id ?>, '<?= addslashes($pengajuan->nama_mahasiswa) ?>', 'rejected')">
                                            <i class="fas fa-times mr-1"></i> Tolak
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada pengajuan yang perlu direview</h5>
                    <p class="text-muted">Semua pengajuan seminar proposal sudah direview atau belum ada pengajuan baru.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Seminar Perlu Penilaian -->
    <?php if (!empty($perlu_penilaian)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clipboard-list mr-2"></i>
                Seminar Proposal - Perlu Penilaian
                <span class="badge badge-primary ml-2"><?= count($perlu_penilaian) ?></span>
            </h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Informasi:</strong> Seminar yang sudah dijadwalkan dan ditetapkan penguji oleh Kaprodi. 
                Anda dapat melakukan penilaian setelah seminar dilaksanakan.
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Mahasiswa</th>
                            <th width="30%">Judul Proposal</th>
                            <th width="25%">Jadwal Seminar</th>
                            <th width="15%">Status Penilaian</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($perlu_penilaian as $index => $seminar): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= $seminar->nama_mahasiswa ?></strong><br>
                                    <small class="text-muted"><?= $seminar->nim ?></small>
                                </td>
                                <td class="text-justify"><?= $seminar->judul ?></td>
                                <td>
                                    <?php if ($seminar->tanggal_seminar): ?>
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?= date('d M Y', strtotime($seminar->tanggal_seminar)) ?><br>
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= $seminar->jam_seminar ? date('H:i', strtotime($seminar->jam_seminar)) . ' WIT' : '-' ?><br>
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?= $seminar->tempat_seminar ?: '-' ?>
                                    <?php else: ?>
                                        <span class="text-muted">Belum dijadwalkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($seminar->penilaian_id) && $seminar->penilaian_id): ?>
                                        <?php if (isset($seminar->status_penilaian) && $seminar->status_penilaian == 'published'): ?>
                                            <span class="badge badge-success">Sudah Dipublikasi</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Draft</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum Dinilai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('dosen/seminar_proposal/detail/' . $seminar->id) ?>" 
                                       class="btn btn-sm btn-info mb-1">
                                        <i class="fas fa-eye mr-1"></i> Detail
                                    </a>
                                    <a href="<?= base_url('dosen/seminar_proposal/penilaian/' . $seminar->id) ?>" 
                                       class="btn btn-sm btn-primary mb-1">
                                        <i class="fas fa-clipboard-list mr-1"></i> 
                                        <?= (isset($seminar->penilaian_id) && $seminar->penilaian_id) ? 'Edit Penilaian' : 'Beri Penilaian' ?>
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

    <!-- Riwayat -->
    <?php if (!empty($riwayat_rekomendasi) || !empty($riwayat_penilaian)): ?>
    <div class="row">
        <!-- Riwayat Rekomendasi -->
        <?php if (!empty($riwayat_rekomendasi)): ?>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-history mr-2"></i>
                        Riwayat Rekomendasi (10 Terakhir)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat_rekomendasi as $riwayat): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $riwayat->nama_mahasiswa ?></strong><br>
                                            <small class="text-muted"><?= $riwayat->nim ?></small>
                                        </td>
                                        <td>
                                            <?php if ($riwayat->status_pembimbing == 'approved'): ?>
                                                <span class="badge badge-success">Disetujui</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Ditolak</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= date('d M Y', strtotime($riwayat->tanggal_review_pembimbing)) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Riwayat Penilaian -->
        <?php if (!empty($riwayat_penilaian)): ?>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-line mr-2"></i>
                        Riwayat Penilaian (10 Terakhir)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Mahasiswa</th>
                                    <th>Nilai</th>
                                    <th>Rekomendasi</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat_penilaian as $penilaian): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $penilaian->nama_mahasiswa ?></strong><br>
                                            <small class="text-muted"><?= $penilaian->nim ?></small>
                                        </td>
                                        <td>
                                            <strong><?= $penilaian->nilai_akhir ?></strong>
                                            <span class="badge badge-secondary"><?= $penilaian->nilai_huruf ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $rekomendasi_badges = [
                                                'diterima_tanpa_revisi' => '<span class="badge badge-success">Diterima</span>',
                                                'revisi_minor' => '<span class="badge badge-info">Revisi Minor</span>',
                                                'revisi_mayor' => '<span class="badge badge-warning">Revisi Mayor</span>',
                                                'ditolak' => '<span class="badge badge-danger">Ditolak</span>'
                                            ];
                                            echo $rekomendasi_badges[$penilaian->rekomendasi] ?? $penilaian->rekomendasi;
                                            ?>
                                        </td>
                                        <td>
                                            <?= date('d M Y', strtotime($penilaian->published_at)) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Modal Rekomendasi -->
<div class="modal fade" id="modalRekomendasi" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/seminar_proposal/rekomendasi') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        Rekomendasi Seminar Proposal
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="modal_seminar_id">
                    <input type="hidden" name="rekomendasi" id="modal_rekomendasi">
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Mahasiswa</label>
                        <input type="text" class="form-control" id="modal_nama_mahasiswa" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Status Rekomendasi</label>
                        <div id="modal_status_text" class="alert"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Komentar/Catatan <span id="required_label" class="text-danger">*</span></label>
                        <textarea class="form-control" name="komentar_pembimbing" id="modal_komentar" rows="4" 
                                  placeholder="Berikan komentar atau catatan terkait pengajuan seminar proposal"></textarea>
                        <small class="form-text text-muted">
                            Komentar akan dikirimkan kepada mahasiswa melalui email dan sistem.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="modal_submit_btn">Kirim Rekomendasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRekomendasi(seminar_id, nama_mahasiswa, rekomendasi) {
    document.getElementById('modal_seminar_id').value = seminar_id;
    document.getElementById('modal_rekomendasi').value = rekomendasi;
    document.getElementById('modal_nama_mahasiswa').value = nama_mahasiswa;
    
    const statusDiv = document.getElementById('modal_status_text');
    const submitBtn = document.getElementById('modal_submit_btn');
    const requiredLabel = document.getElementById('required_label');
    const komentarField = document.getElementById('modal_komentar');
    
    if (rekomendasi === 'approved') {
        statusDiv.className = 'alert alert-success';
        statusDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i><strong>SETUJUI</strong> - Pengajuan akan diteruskan ke Kaprodi untuk review';
        submitBtn.className = 'btn btn-success';
        submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Setujui Pengajuan';
        requiredLabel.style.display = 'none';
        komentarField.required = false;
        komentarField.placeholder = 'Komentar atau catatan tambahan (opsional)';
    } else {
        statusDiv.className = 'alert alert-danger';
        statusDiv.innerHTML = '<i class="fas fa-times-circle mr-2"></i><strong>TOLAK</strong> - Pengajuan akan dikembalikan ke mahasiswa untuk perbaikan';
        submitBtn.className = 'btn btn-danger';
        submitBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Tolak Pengajuan';
        requiredLabel.style.display = 'inline';
        komentarField.required = true;
        komentarField.placeholder = 'Jelaskan alasan penolakan dan perbaikan yang diperlukan (wajib diisi)';
    }
    
    // Clear previous comment
    komentarField.value = '';
    
    $('#modalRekomendasi').modal('show');
}

// Form validation
document.querySelector('#modalRekomendasi form').addEventListener('submit', function(e) {
    const rekomendasi = document.getElementById('modal_rekomendasi').value;
    const komentar = document.getElementById('modal_komentar').value.trim();
    
    if (rekomendasi === 'rejected' && komentar === '') {
        e.preventDefault();
        alert('Komentar wajib diisi untuk penolakan pengajuan!');
        return false;
    }
});
</script>