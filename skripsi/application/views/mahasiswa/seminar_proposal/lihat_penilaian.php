<?php
/**
 * File: application/views/mahasiswa/seminar_proposal/lihat_penilaian.php
 * View untuk menampilkan penilaian seminar proposal kepada mahasiswa
 */
?>

<div class="container-fluid">
    <!-- Header dengan breadcrumb -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-clipboard-check text-primary mr-2"></i>
                Penilaian Seminar Proposal
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/seminar_proposal') ?>">Seminar Proposal</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/seminar_proposal/detail/' . $seminar->id) ?>">Detail</a></li>
                    <li class="breadcrumb-item active">Penilaian</li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('mahasiswa/seminar_proposal/detail/' . $seminar->id) ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <!-- Info Proposal -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-info-circle mr-2"></i>
                Informasi Seminar Proposal
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID Seminar:</strong></td>
                            <td>#SP-<?= str_pad($seminar->id, 4, '0', STR_PAD_LEFT) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Judul Proposal:</strong></td>
                            <td><?= character_limiter($seminar->judul, 60) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Seminar:</strong></td>
                            <td><?= date('d F Y, H:i', strtotime($seminar->tanggal_seminar)) ?> WIT</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Tempat:</strong></td>
                            <td><?= $seminar->tempat_seminar ?: '-' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Selesai
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Penilaian Dipublikasikan:</strong></td>
                            <td><?= date('d F Y, H:i', strtotime($penilaian->published_at)) ?> WIT</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Hasil Penilaian -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-success">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-star mr-2"></i>
                Hasil Penilaian Seminar Proposal
            </h6>
        </div>
        <div class="card-body">
            <!-- Nilai Final -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h2 class="text-primary font-weight-bold mb-2"><?= number_format($penilaian->nilai_akhir, 2) ?></h2>
                            <h4 class="text-primary mb-2">Nilai Huruf: <?= $penilaian->nilai_huruf ?></h4>
                            <p class="text-muted mb-0">Nilai Final</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <?php
                            $rekomendasi_class = '';
                            $rekomendasi_icon = '';
                            $rekomendasi_text = '';
                            
                            switch($penilaian->rekomendasi) {
                                case 'diterima':
                                    $rekomendasi_class = 'text-success';
                                    $rekomendasi_icon = 'fas fa-check-circle';
                                    $rekomendasi_text = 'DITERIMA';
                                    break;
                                case 'revisi_minor':
                                    $rekomendasi_class = 'text-warning';
                                    $rekomendasi_icon = 'fas fa-edit';
                                    $rekomendasi_text = 'DITERIMA DENGAN REVISI MINOR';
                                    break;
                                case 'revisi_mayor':
                                    $rekomendasi_class = 'text-orange';
                                    $rekomendasi_icon = 'fas fa-exclamation-triangle';
                                    $rekomendasi_text = 'DITERIMA DENGAN REVISI MAYOR';
                                    break;
                                case 'ditolak':
                                    $rekomendasi_class = 'text-danger';
                                    $rekomendasi_icon = 'fas fa-times-circle';
                                    $rekomendasi_text = 'DITOLAK';
                                    break;
                                default:
                                    $rekomendasi_class = 'text-secondary';
                                    $rekomendasi_icon = 'fas fa-question-circle';
                                    $rekomendasi_text = 'BELUM DITENTUKAN';
                            }
                            ?>
                            <i class="<?= $rekomendasi_icon ?> fa-3x <?= $rekomendasi_class ?> mb-3"></i>
                            <h5 class="<?= $rekomendasi_class ?> font-weight-bold"><?= $rekomendasi_text ?></h5>
                            <p class="text-muted mb-0">Hasil Rekomendasi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breakdown Nilai -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h6 class="font-weight-bold mb-3">Detail Penilaian:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Komponen Penilaian</th>
                                    <th class="text-center">Nilai Penguji 1</th>
                                    <th class="text-center">Nilai Penguji 2</th>
                                    <th class="text-center">Nilai Pembimbing</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Nilai Akhir</strong></td>
                                    <td class="text-center"><?= number_format($penilaian->nilai_penguji1, 2) ?></td>
                                    <td class="text-center"><?= number_format($penilaian->nilai_penguji2, 2) ?></td>
                                    <td class="text-center"><?= number_format($penilaian->nilai_pembimbing, 2) ?></td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th>Rata-rata Final</th>
                                    <th class="text-center" colspan="3">
                                        <?= number_format($penilaian->nilai_akhir, 2) ?> (<?= $penilaian->nilai_huruf ?>)
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Catatan dan Revisi -->
    <?php if (!empty($penilaian->catatan_umum) || !empty($penilaian->keterangan_rekomendasi)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-sticky-note mr-2"></i>
                Catatan dan Saran Revisi
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($penilaian->catatan_umum)): ?>
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="fas fa-comments mr-2"></i>
                    Catatan Umum dari Dewan Penguji:
                </h6>
                <p class="mb-0"><?= nl2br(htmlspecialchars($penilaian->catatan_umum)) ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($penilaian->keterangan_rekomendasi)): ?>
            <div class="alert alert-warning">
                <h6 class="alert-heading">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Keterangan Rekomendasi:
                </h6>
                <p class="mb-0"><?= nl2br(htmlspecialchars($penilaian->keterangan_rekomendasi)) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Informasi Dewan Penguji -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-users mr-2"></i>
                Dewan Penguji
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <h6 class="text-primary font-weight-bold">Dosen Pembimbing</h6>
                            <p class="mb-1"><?= $penilaian->nama_pembimbing ?: 'Belum Ditentukan' ?></p>
                            <small class="text-muted">NIP: <?= $penilaian->nip_pembimbing ?: '-' ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h6 class="text-success font-weight-bold">Dosen Penguji 1</h6>
                            <p class="mb-1"><?= $penilaian->nama_penguji1 ?: 'Belum Ditentukan' ?></p>
                            <small class="text-muted">NIP: <?= $penilaian->nip_penguji1 ?: '-' ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <h6 class="text-warning font-weight-bold">Dosen Penguji 2</h6>
                            <p class="mb-1"><?= $penilaian->nama_penguji2 ?: 'Belum Ditentukan' ?></p>
                            <small class="text-muted">NIP: <?= $penilaian->nip_penguji2 ?: '-' ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Langkah Selanjutnya -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-secondary">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-route mr-2"></i>
                Langkah Selanjutnya
            </h6>
        </div>
        <div class="card-body">
            <?php if (in_array($penilaian->rekomendasi, ['diterima', 'revisi_minor'])): ?>
                <div class="alert alert-success">
                    <h6 class="alert-heading">
                        <i class="fas fa-check-circle mr-2"></i>
                        Selamat! Proposal Anda Telah Diterima
                    </h6>
                    <p>Anda dapat melanjutkan ke tahap penelitian. Pastikan untuk:</p>
                    <ul class="mb-3">
                        <li>Mengajukan surat izin penelitian jika diperlukan</li>
                        <li>Melakukan revisi sesuai catatan (jika ada)</li>
                        <li>Memulai proses penelitian sesuai metodologi yang disetujui</li>
                        <li>Melakukan bimbingan rutin dengan dosen pembimbing</li>
                    </ul>
                    <a href="<?= base_url('mahasiswa/penelitian') ?>" class="btn btn-success">
                        <i class="fas fa-microscope mr-2"></i>
                        Lanjut ke Fase Penelitian
                    </a>
                </div>
            <?php elseif ($penilaian->rekomendasi == 'revisi_mayor'): ?>
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Diperlukan Revisi Mayor
                    </h6>
                    <p>Proposal Anda memerlukan revisi substantif. Silakan:</p>
                    <ul class="mb-3">
                        <li>Pelajari dengan seksama catatan revisi di atas</li>
                        <li>Konsultasi dengan dosen pembimbing</li>
                        <li>Lakukan revisi sesuai saran dewan penguji</li>
                        <li>Persiapkan untuk seminar proposal ulang jika diperlukan</li>
                    </ul>
                </div>
            <?php elseif ($penilaian->rekomendasi == 'ditolak'): ?>
                <div class="alert alert-danger">
                    <h6 class="alert-heading">
                        <i class="fas fa-times-circle mr-2"></i>
                        Proposal Perlu Diperbaiki
                    </h6>
                    <p>Proposal Anda memerlukan perbaikan signifikan. Silakan:</p>
                    <ul class="mb-3">
                        <li>Konsultasi intensif dengan dosen pembimbing</li>
                        <li>Evaluasi ulang konsep dan metodologi penelitian</li>
                        <li>Lakukan perbaikan fundamental sesuai catatan</li>
                        <li>Ajukan seminar proposal baru setelah perbaikan</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.text-orange {
    color: #fd7e14 !important;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
</style>