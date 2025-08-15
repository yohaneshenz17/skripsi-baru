<?php
/**
 * View: Lihat Rangkuman Penilaian Seminar Proposal (Dosen Penguji)
 * 
 * File: application/views/dosen/daftar_penguji/lihat_penilaian_proposal.php
 * 
 * View khusus untuk dosen penguji melihat rangkuman hasil penilaian
 * dari semua dewan penguji (pembimbing + penguji 1 + penguji 2)
 * 
 * Data yang tersedia:
 * - $seminar: data lengkap seminar proposal
 * - $penilaian_data: array hasil penilaian dari semua penguji
 * - $dewan_penguji: data susunan dewan penguji
 * - $berita_acara: data berita acara (jika sudah ada)
 * - $is_penguji1, $is_penguji2: boolean posisi penguji
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

<!-- Breadcrumb Navigation -->
<div class="row">
    <div class="col">
        <div class="mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('dosen/daftar_penguji') ?>">Daftar Penguji</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('dosen/daftar_penguji/detail_proposal/' . $seminar->id) ?>">Detail Proposal</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Rangkuman Penilaian</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Header Information Card -->
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header bg-gradient-info">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0 text-white">
                            <i class="fa fa-chart-bar mr-2"></i>
                            Rangkuman Hasil Penilaian Seminar Proposal
                        </h3>
                        <p class="text-white-50 mb-0">
                            Status: Dosen <?= $is_penguji1 ? 'Penguji I' : 'Penguji II' ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('dosen/daftar_penguji/detail_proposal/' . $seminar->id) ?>" 
                           class="btn btn-sm btn-neutral">
                            <i class="fa fa-arrow-left"></i> Kembali ke Detail
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Info Mahasiswa & Proposal -->
                        <div class="media align-items-center mb-3">
                            <span class="avatar avatar-lg rounded-circle mr-3">
                                <img alt="Avatar" src="<?= base_url('cdn/img/mahasiswa/default.png') ?>">
                            </span>
                            <div class="media-body">
                                <h5 class="mb-0"><?= $seminar->nama_mahasiswa ?></h5>
                                <p class="text-sm text-muted mb-0"><?= $seminar->nim ?> | <?= $seminar->nama_prodi ?></p>
                            </div>
                        </div>
                        
                        <h6 class="text-uppercase text-muted mb-2">Judul Proposal:</h6>
                        <p class="text-sm mb-0"><?= $seminar->judul ?></p>
                    </div>
                    <div class="col-md-4">
                        <!-- Jadwal Seminar -->
                        <?php if (isset($seminar->tanggal_seminar) && $seminar->tanggal_seminar): ?>
                        <div class="text-center bg-gradient-default rounded p-3">
                            <i class="fa fa-calendar fa-2x text-white mb-2"></i>
                            <h6 class="text-white mb-0"><?= date('d F Y', strtotime($seminar->tanggal_seminar)) ?></h6>
                            <p class="text-white-50 mb-0"><?= date('H:i', strtotime($seminar->jam_seminar)) ?> WIT</p>
                            <small class="text-white-50"><?= $seminar->tempat_seminar ?></small>
                        </div>
                        <?php else: ?>
                        <div class="text-center bg-secondary rounded p-3">
                            <i class="fa fa-clock fa-2x text-white mb-2"></i>
                            <h6 class="text-white mb-0">Belum Dijadwalkan</h6>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dewan Penguji Info -->
<div class="row mt-4">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fa fa-users mr-2"></i>
                    Susunan Dewan Penguji
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Pembimbing -->
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="icon icon-shape icon-lg bg-primary text-white rounded-circle mx-auto mb-3">
                                <i class="fa fa-user-tie"></i>
                            </div>
                            <h6 class="mb-1">Dosen Pembimbing</h6>
                            <p class="text-sm text-muted mb-0">
                                <strong><?= $dewan_penguji->nama_pembimbing ?></strong>
                                <?php if (isset($dewan_penguji->nip_pembimbing)): ?>
                                <br><small>NIP: <?= $dewan_penguji->nip_pembimbing ?></small>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Penguji 1 -->
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="icon icon-shape icon-lg bg-<?= $is_penguji1 ? 'success' : 'info' ?> text-white rounded-circle mx-auto mb-3">
                                <i class="fa fa-gavel"></i>
                            </div>
                            <h6 class="mb-1">
                                Dosen Penguji I
                                <?= $is_penguji1 ? '<span class="badge badge-success ml-1">Anda</span>' : '' ?>
                            </h6>
                            <p class="text-sm text-muted mb-0">
                                <strong><?= $dewan_penguji->nama_penguji1 ?></strong>
                                <?php if (isset($dewan_penguji->nip_penguji1)): ?>
                                <br><small>NIP: <?= $dewan_penguji->nip_penguji1 ?></small>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Penguji 2 -->
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="icon icon-shape icon-lg bg-<?= $is_penguji2 ? 'success' : 'info' ?> text-white rounded-circle mx-auto mb-3">
                                <i class="fa fa-gavel"></i>
                            </div>
                            <h6 class="mb-1">
                                Dosen Penguji II
                                <?= $is_penguji2 ? '<span class="badge badge-success ml-1">Anda</span>' : '' ?>
                            </h6>
                            <p class="text-sm text-muted mb-0">
                                <strong><?= $dewan_penguji->nama_penguji2 ?></strong>
                                <?php if (isset($dewan_penguji->nip_penguji2)): ?>
                                <br><small>NIP: <?= $dewan_penguji->nip_penguji2 ?></small>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rangkuman Penilaian -->
<div class="row mt-4">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fa fa-star mr-2"></i>
                    Rangkuman Hasil Penilaian
                </h4>
            </div>
            <div class="card-body">
                <?php if (!empty($penilaian_data)): ?>
                    <?php foreach ($penilaian_data as $index => $penilaian): ?>
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="mb-0">
                                        <i class="fa fa-user mr-2"></i>
                                        <?= $penilaian->posisi_penguji ?>: <strong><?= $penilaian->nama_penguji ?></strong>
                                    </h6>
                                    <?php if (isset($penilaian->nip) && $penilaian->nip): ?>
                                    <small class="text-muted">NIP: <?= $penilaian->nip ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-auto">
                                    <?php if (isset($penilaian->nilai_total) && $penilaian->nilai_total): ?>
                                    <span class="badge badge-primary badge-lg">
                                        Nilai: <?= number_format($penilaian->nilai_total, 1) ?>
                                    </span>
                                    <?php else: ?>
                                    <span class="badge badge-warning">Belum Dinilai</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Komponen Penilaian -->
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Komponen Penilaian:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <?php if (isset($penilaian->nilai_presentasi)): ?>
                                                <tr>
                                                    <td><strong>Presentasi:</strong></td>
                                                    <td class="text-right">
                                                        <span class="badge badge-info"><?= $penilaian->nilai_presentasi ?></span>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($penilaian->nilai_materi)): ?>
                                                <tr>
                                                    <td><strong>Materi:</strong></td>
                                                    <td class="text-right">
                                                        <span class="badge badge-info"><?= $penilaian->nilai_materi ?></span>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($penilaian->nilai_metodologi)): ?>
                                                <tr>
                                                    <td><strong>Metodologi:</strong></td>
                                                    <td class="text-right">
                                                        <span class="badge badge-info"><?= $penilaian->nilai_metodologi ?></span>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($penilaian->nilai_penguasaan)): ?>
                                                <tr>
                                                    <td><strong>Penguasaan:</strong></td>
                                                    <td class="text-right">
                                                        <span class="badge badge-info"><?= $penilaian->nilai_penguasaan ?></span>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($penilaian->nilai_total)): ?>
                                                <tr class="table-primary">
                                                    <td><strong>Total:</strong></td>
                                                    <td class="text-right">
                                                        <span class="badge badge-primary badge-lg">
                                                            <?= number_format($penilaian->nilai_total, 1) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Catatan & Saran -->
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Catatan & Saran Perbaikan:</h6>
                                    <div class="bg-light rounded p-3">
                                        <?php if (isset($penilaian->catatan_perbaikan) && $penilaian->catatan_perbaikan): ?>
                                            <p class="text-sm mb-0">
                                                <?= nl2br(htmlspecialchars($penilaian->catatan_perbaikan)) ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-sm text-muted mb-0">
                                                <em>Tidak ada catatan perbaikan</em>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (isset($penilaian->created_at)): ?>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fa fa-clock mr-1"></i>
                                            Dinilai pada: <?= date('d F Y, H:i', strtotime($penilaian->created_at)) ?> WIT
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Statistik Ringkas -->
                    <?php 
                    $total_nilai = 0;
                    $jumlah_penilai = 0;
                    foreach ($penilaian_data as $p) {
                        if (isset($p->nilai_total) && $p->nilai_total > 0) {
                            $total_nilai += $p->nilai_total;
                            $jumlah_penilai++;
                        }
                    }
                    $rata_rata = $jumlah_penilai > 0 ? $total_nilai / $jumlah_penilai : 0;
                    ?>
                    
                    <?php if ($jumlah_penilai > 0): ?>
                    <div class="alert alert-info">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="mb-0">
                                    <i class="fa fa-calculator mr-2"></i>
                                    Statistik Penilaian
                                </h6>
                            </div>
                            <div class="col-auto">
                                <div class="row text-center">
                                    <div class="col">
                                        <h5 class="mb-0"><?= $jumlah_penilai ?></h5>
                                        <small class="text-muted">Penilai</small>
                                    </div>
                                    <div class="col">
                                        <h5 class="mb-0"><?= number_format($rata_rata, 1) ?></h5>
                                        <small class="text-muted">Rata-rata</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Belum Ada Penilaian -->
                    <div class="text-center py-5">
                        <div class="icon icon-shape icon-lg bg-warning text-white rounded-circle mx-auto mb-4">
                            <i class="fa fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="text-muted">Belum Ada Penilaian</h5>
                        <p class="text-sm text-muted mb-0">
                            Penilaian seminar proposal belum diinput oleh dewan penguji.<br>
                            Silakan cek kembali setelah seminar dilaksanakan dan penilaian diinput.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Berita Acara (jika ada) -->
<?php if (isset($berita_acara) && $berita_acara): ?>
<div class="row mt-4">
    <div class="col">
        <div class="card">
            <div class="card-header bg-gradient-warning">
                <h4 class="mb-0 text-white">
                    <i class="fa fa-file-alt mr-2"></i>
                    Berita Acara Seminar
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Keputusan Seminar:</h6>
                        <h4 class="mb-3">
                            <span class="badge badge-<?= isset($berita_acara->keputusan) && $berita_acara->keputusan == 'LULUS' ? 'success' : 'warning' ?> badge-lg">
                                <?= isset($berita_acara->keputusan) ? $berita_acara->keputusan : 'Belum Ditentukan' ?>
                            </span>
                        </h4>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Nilai Rata-rata:</h6>
                        <h4 class="text-primary mb-3">
                            <?= isset($berita_acara->nilai_rata_rata) ? number_format($berita_acara->nilai_rata_rata, 1) : '-' ?>
                        </h4>
                    </div>
                </div>
                
                <?php if (isset($berita_acara->catatan_umum) && $berita_acara->catatan_umum): ?>
                <hr>
                <h6 class="text-muted">Catatan Umum:</h6>
                <div class="bg-light rounded p-3">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($berita_acara->catatan_umum)) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <a href="<?= base_url('dosen/daftar_penguji') ?>" class="btn btn-secondary">
                            <i class="fa fa-arrow-left mr-2"></i>
                            Kembali ke Daftar Penguji
                        </a>
                        <a href="<?= base_url('dosen/daftar_penguji/detail_proposal/' . $seminar->id) ?>" 
                           class="btn btn-outline-secondary ml-2">
                            <i class="fa fa-eye mr-2"></i>
                            Detail Proposal
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <?php 
                        // Cek field file proposal (backward compatibility)
                        $file_proposal = isset($seminar->file_draft_proposal) ? $seminar->file_draft_proposal : 
                                        (isset($seminar->file_proposal) ? $seminar->file_proposal : null);
                        ?>
                        <?php if ($file_proposal): ?>
                        <a href="<?= base_url('dosen/daftar_penguji/download_proposal/' . $seminar->id) ?>" 
                           class="btn btn-primary">
                            <i class="fa fa-download mr-2"></i>
                            Download Proposal
                        </a>
                        <small class="d-block text-muted mt-1">
                            <i class="fa fa-info-circle"></i>
                            Path: uploads/seminar_proposal/proposal_files/
                        </small>
                        <?php else: ?>
                        <button class="btn btn-secondary" disabled>
                            <i class="fa fa-download mr-2"></i>
                            File Tidak Tersedia
                        </button>
                        <?php endif; ?>
                        
                        <?php if (isset($berita_acara) && $berita_acara): ?>
                        <a href="<?= base_url('dosen/daftar_penguji/berita_acara_proposal/' . $seminar->id) ?>" 
                           class="btn btn-info ml-2">
                            <i class="fa fa-file-alt mr-2"></i>
                            Cetak Berita Acara
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>