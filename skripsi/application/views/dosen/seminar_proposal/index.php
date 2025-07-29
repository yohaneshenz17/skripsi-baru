<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Seminar Proposal</h1>
    <small class="text-muted">Kelola pengajuan seminar proposal mahasiswa bimbingan</small>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Pengajuan
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= isset($stats['total']) ? $stats['total'] : 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            Disetujui
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= isset($stats['disetujui']) ? $stats['disetujui'] : 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Ditolak
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= isset($stats['ditolak']) ? $stats['ditolak'] : 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pengajuan Perlu Review -->
<?php if (!empty($pengajuan_review)): ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-warning text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Pengajuan Seminar Proposal - Perlu Review Anda
            <span class="badge badge-light ml-2"><?= count($pengajuan_review) ?></span>
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th width="100">NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Judul Proposal</th>
                        <th width="120">Tanggal Ajukan</th>
                        <th width="100">File Proposal</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pengajuan_review as $index => $pengajuan): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $pengajuan->nim ?></td>
                        <td>
                            <strong><?= $pengajuan->nama_mahasiswa ?></strong>
                            <br><small class="text-muted"><?= $pengajuan->nama_prodi ?></small>
                        </td>
                        <td class="text-justify">
                            <?php 
                            // Safe character limiter
                            $judul = isset($pengajuan->judul) ? $pengajuan->judul : 'Tidak ada judul';
                            echo (strlen($judul) > 80) ? substr($judul, 0, 80) . '...' : $judul;
                            ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($pengajuan->created_at)) ?></td>
                        <td class="text-center">
                            <?php if (!empty($pengajuan->file_proposal)): ?>
                                <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $pengajuan->file_proposal) ?>" 
                                   target="_blank" class="btn btn-info btn-sm" title="Lihat File Proposal">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Tidak ada file</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm">
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
    </div>
</div>
<?php endif; ?>

<!-- Riwayat Rekomendasi -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-history mr-2"></i>
            Riwayat Rekomendasi Seminar Proposal
        </h6>
    </div>
    <div class="card-body">
        <?php if (!empty($riwayat_rekomendasi)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th width="50">No</th>
                        <th width="100">NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Judul Proposal</th>
                        <th width="100">Status</th>
                        <th width="120">Tanggal</th>
                        <th width="100">File</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat_rekomendasi as $index => $riwayat): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $riwayat->nim ?></td>
                        <td>
                            <strong><?= $riwayat->nama_mahasiswa ?></strong>
                            <br><small class="text-muted"><?= $riwayat->nama_prodi ?></small>
                        </td>
                        <td class="text-justify">
                            <?php 
                            // Safe character limiter
                            $judul = isset($riwayat->judul) ? $riwayat->judul : 'Tidak ada judul';
                            echo (strlen($judul) > 60) ? substr($judul, 0, 60) . '...' : $judul;
                            ?>
                        </td>
                        <td>
                            <?php 
                            $status_class = '';
                            $status_text = '';
                            switch($riwayat->status) {
                                case 'approved':
                                    $status_class = 'badge-success';
                                    $status_text = 'Disetujui';
                                    break;
                                case 'rejected':
                                    $status_class = 'badge-danger';
                                    $status_text = 'Ditolak';
                                    break;
                                case 'scheduled':
                                    $status_class = 'badge-info';
                                    $status_text = 'Terjadwal';
                                    break;
                                case 'completed':
                                    $status_class = 'badge-primary';
                                    $status_text = 'Selesai';
                                    break;
                                default:
                                    $status_class = 'badge-secondary';
                                    $status_text = 'Unknown';
                            }
                            ?>
                            <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($riwayat->updated_at)) ?></td>
                        <td class="text-center">
                            <?php if (!empty($riwayat->file_proposal)): ?>
                                <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $riwayat->file_proposal) ?>" 
                                   target="_blank" class="btn btn-outline-info btn-sm" title="Lihat File">
                                    <i class="fas fa-eye"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= base_url('dosen/seminar_proposal/detail/' . $riwayat->id) ?>" 
                               class="btn btn-outline-primary btn-sm" title="Lihat Detail">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            Belum ada riwayat rekomendasi seminar proposal.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Seminar Perlu Penilaian -->
<?php if (!empty($perlu_penilaian)): ?>
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-info text-white">
        <h6 class="m-0 font-weight-bold">
            <i class="fas fa-clipboard-check mr-2"></i>
            Seminar yang Perlu Penilaian
            <span class="badge badge-light ml-2"><?= count($perlu_penilaian) ?></span>
        </h6>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Informasi:</strong> Seminar proposal berikut sudah terjadwal dan perlu penilaian dari Anda.
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th width="50">No</th>
                        <th width="100">NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Judul Proposal</th>
                        <th width="120">Tanggal Seminar</th>
                        <th width="100">File</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perlu_penilaian as $index => $seminar): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $seminar->nim ?></td>
                        <td>
                            <strong><?= $seminar->nama_mahasiswa ?></strong>
                            <br><small class="text-muted"><?= $seminar->nama_prodi ?></small>
                        </td>
                        <td class="text-justify">
                            <?php 
                            // Safe character limiter
                            $judul = isset($seminar->judul) ? $seminar->judul : 'Tidak ada judul';
                            echo (strlen($judul) > 60) ? substr($judul, 0, 60) . '...' : $judul;
                            ?>
                        </td>
                        <td>
                            <?= isset($seminar->tanggal_seminar) ? date('d/m/Y', strtotime($seminar->tanggal_seminar)) : '-' ?>
                            <?php if(isset($seminar->jam_seminar)): ?>
                            <br><small class="text-muted"><?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIB</small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($seminar->file_proposal)): ?>
                                <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal) ?>" 
                                   target="_blank" class="btn btn-outline-info btn-sm" title="Lihat File">
                                    <i class="fas fa-eye"></i>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group-vertical btn-group-sm">
                                <a href="<?= base_url('dosen/seminar_proposal/detail/' . $seminar->id) ?>" 
                                   class="btn btn-info btn-sm" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a href="<?= base_url('dosen/seminar_proposal/penilaian/' . $seminar->id) ?>" 
                                   class="btn btn-primary btn-sm" title="Input Penilaian">
                                    <i class="fas fa-star"></i> Nilai
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal untuk Quick Action -->
<div class="modal fade" id="rekomendasiModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rekomendasi Seminar Proposal</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="<?= base_url('dosen/seminar_proposal/rekomendasi') ?>">
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="modalSeminarId">
                    <input type="hidden" name="rekomendasi" id="modalRekomendasi">
                    
                    <div class="alert" id="modalAlert"></div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Mahasiswa:</label>
                        <p id="modalMahasiswa"></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="modalKomentar" class="font-weight-bold">Komentar/Catatan:</label>
                        <textarea name="komentar_pembimbing" id="modalKomentar" class="form-control" rows="4" 
                            placeholder="Berikan komentar atau catatan untuk pengajuan ini..."></textarea>
                        <small class="form-text text-muted">
                            Komentar wajib diisi jika memberikan penolakan.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" id="modalSubmitBtn">Proses</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.btn-action {
    margin-bottom: 2px;
}

.badge-lg {
    font-size: 1.1em;
    padding: 0.5em 0.75em;
}

.table th {
    background-color: #f8f9fc;
    font-weight: 600;
    color: #5a5c69;
    border-color: #e3e6f0;
}

.table td {
    border-color: #e3e6f0;
    vertical-align: middle;
}

.btn-group-vertical .btn {
    margin-bottom: 2px;
}

.btn-group-vertical .btn:last-child {
    margin-bottom: 0;
}
</style>