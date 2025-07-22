<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// Prepare data untuk template
$template_data = array(
    'title' => 'Bimbingan Skripsi - Phase 2',
    'content' => '',
    'script' => ''
);

// Load content view sebagai string
ob_start();
?>

<!-- Alert Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-check"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('info')): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-info"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('info') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if (isset($waiting_kaprodi)): ?>
<!-- STATUS 1: PROPOSAL BELUM DIREVIEW KAPRODI -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">📋 Menunggu Review Kaprodi</h3>
                        <p class="text-white mt-2 mb-0">
                            Proposal Anda sedang ditinjau oleh <strong>Kaprodi</strong>. 
                            Setelah disetujui, kaprodi akan menetapkan dosen pembimbing untuk Anda.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fa fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-file-search fa-4x text-info mb-3"></i>
                <h4>Proposal Sedang Ditinjau Kaprodi</h4>
                <p class="text-muted">
                    <strong>Judul:</strong> <?= $waiting_kaprodi->judul ?><br>
                    <strong>Tanggal Pengajuan:</strong> <?= isset($waiting_kaprodi->created_at) && $waiting_kaprodi->created_at ? date('d F Y', strtotime($waiting_kaprodi->created_at)) : '-' ?>
                </p>
                <p class="text-muted">
                    Kaprodi sedang melakukan review terhadap proposal Anda. 
                    Silakan tunggu konfirmasi lebih lanjut atau hubungi kaprodi untuk info lebih detail.
                </p>
                <div class="mt-4">
                    <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                        <i class="fa fa-eye"></i> Lihat Status Proposal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif (isset($rejected_kaprodi)): ?>
<!-- STATUS 2: PROPOSAL DITOLAK KAPRODI -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-danger">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">❌ Proposal Ditolak Kaprodi</h3>
                        <p class="text-white mt-2 mb-0">
                            Proposal Anda telah direview oleh <strong>Kaprodi</strong> dan memerlukan perbaikan. 
                            Silakan lakukan revisi sesuai komentar yang diberikan.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-danger rounded-circle shadow">
                            <i class="fa fa-times"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                <h4>Proposal Memerlukan Perbaikan</h4>
                <p class="text-muted">
                    <strong>Judul:</strong> <?= $rejected_kaprodi->judul ?><br>
                    <strong>Tanggal Review:</strong> <?= isset($rejected_kaprodi->tanggal_review_kaprodi) && $rejected_kaprodi->tanggal_review_kaprodi ? date('d F Y', strtotime($rejected_kaprodi->tanggal_review_kaprodi)) : '-' ?>
                </p>
                <?php if(isset($rejected_kaprodi->komentar_kaprodi) && $rejected_kaprodi->komentar_kaprodi): ?>
                <div class="alert alert-warning">
                    <strong>Komentar Kaprodi:</strong><br>
                    <?= $rejected_kaprodi->komentar_kaprodi ?>
                </div>
                <?php endif; ?>
                <p class="text-muted">
                    Silakan lakukan revisi proposal sesuai dengan komentar kaprodi dan ajukan kembali.
                </p>
                <div class="mt-4">
                    <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Revisi Proposal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif (isset($pending_proposal)): ?>
<!-- STATUS 3: MENUNGGU PERSETUJUAN DOSEN PEMBIMBING -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-warning">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">⏳ Menunggu Persetujuan Dosen Pembimbing</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Kaprodi</strong> telah menetapkan <strong><?= isset($pending_proposal->nama_dosen) ? $pending_proposal->nama_dosen : 'Dosen' ?></strong> sebagai dosen pembimbing Anda. 
                            Saat ini menunggu persetujuan dari dosen yang bersangkutan.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-warning rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-hourglass-half fa-4x text-warning mb-3"></i>
                <h4>Menunggu Konfirmasi Dosen Pembimbing</h4>
                <p class="text-muted">
                    <strong>Dosen Pembimbing:</strong> <?= isset($pending_proposal->nama_dosen) ? $pending_proposal->nama_dosen : 'Belum ditetapkan' ?><br>
                    <strong>Tanggal Penetapan:</strong> <?= isset($pending_proposal->tanggal_penetapan) && $pending_proposal->tanggal_penetapan ? date('d F Y', strtotime($pending_proposal->tanggal_penetapan)) : '-' ?>
                </p>
                <p class="text-muted">
                    Dosen pembimbing yang ditunjuk kaprodi sedang melakukan review proposal Anda. 
                    Silakan tunggu konfirmasi lebih lanjut via email atau hubungi dosen yang bersangkutan.
                </p>
                <div class="mt-4">
                    <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                        <i class="fa fa-eye"></i> Lihat Status Proposal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif (isset($rejected_dosen)): ?>
<!-- STATUS 4: DOSEN PEMBIMBING MENOLAK -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-danger">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">❌ Dosen Pembimbing Menolak</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong><?= isset($rejected_dosen->nama_dosen) ? $rejected_dosen->nama_dosen : 'Dosen' ?></strong> menolak penunjukan sebagai pembimbing. 
                            <strong>Kaprodi</strong> akan menetapkan dosen pembimbing yang baru untuk Anda.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-danger rounded-circle shadow">
                            <i class="fa fa-user-times"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-user-slash fa-4x text-danger mb-3"></i>
                <h4>Penunjukan Pembimbing Ditolak</h4>
                <p class="text-muted">
                    <strong>Dosen:</strong> <?= isset($rejected_dosen->nama_dosen) ? $rejected_dosen->nama_dosen : 'Tidak diketahui' ?><br>
                    <strong>Tanggal Respon:</strong> <?= isset($rejected_dosen->tanggal_respon_pembimbing) && $rejected_dosen->tanggal_respon_pembimbing ? date('d F Y', strtotime($rejected_dosen->tanggal_respon_pembimbing)) : '-' ?>
                </p>
                <?php if(isset($rejected_dosen->komentar_pembimbing) && $rejected_dosen->komentar_pembimbing): ?>
                <div class="alert alert-warning">
                    <strong>Komentar Dosen:</strong><br>
                    <?= $rejected_dosen->komentar_pembimbing ?>
                </div>
                <?php endif; ?>
                <p class="text-muted">
                    Kaprodi akan segera menetapkan dosen pembimbing yang baru. 
                    Silakan tunggu konfirmasi lebih lanjut.
                </p>
                <div class="mt-4">
                    <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                        <i class="fa fa-eye"></i> Lihat Status Proposal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif (!isset($proposal)): ?>
<!-- STATUS 5: BELUM ADA PROPOSAL -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-file-alt fa-4x text-muted mb-3"></i>
                <h4>Belum Ada Proposal</h4>
                <p class="text-muted">
                    Anda belum mengajukan proposal tugas akhir. 
                    Silakan ajukan proposal terlebih dahulu untuk memulai proses bimbingan.
                </p>
                <div class="mt-4">
                    <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Ajukan Proposal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- STATUS 6: BIMBINGAN AKTIF -->

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-success">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">📚 Bimbingan Skripsi - Phase 2</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Dosen Pembimbing:</strong> <?= isset($proposal->nama_dosen) ? $proposal->nama_dosen : 'Belum ditetapkan' ?> | 
                            <strong>Judul:</strong> <?= substr($proposal->judul, 0, 50) ?><?= strlen($proposal->judul) > 50 ? '...' : '' ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-neutral" onclick="tambahJurnalBimbingan()">
                            <i class="fa fa-plus"></i> Tambah Jurnal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Pertemuan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $total_bimbingan ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-calendar"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Target: 16 pertemuan</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tervalidasi</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $bimbingan_tervalidasi ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 
                    <?= $total_bimbingan > 0 ? round(($bimbingan_tervalidasi/$total_bimbingan)*100, 1) : 0 ?>%</span>
                    <span class="text-nowrap">dari total</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Pending</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $bimbingan_pending ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Menunggu validasi</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Progress</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= $total_bimbingan >= 16 ? '100' : round(($total_bimbingan/16)*100, 1) ?>%
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="fa fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <?php if($siap_seminar): ?>
                        <span class="text-success">Siap seminar proposal</span>
                    <?php else: ?>
                        <span class="text-nowrap">Minimal 8 untuk seminar</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Progress Bimbingan</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="h2 font-weight-bold mb-0"><?= $bimbingan_tervalidasi ?>/16</span>
                    </div>
                    <div class="col">
                        <div class="progress progress-xs mb-0">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= min(($bimbingan_tervalidasi/16)*100, 100) ?>%"></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <span class="text-sm">
                            <?php if($bimbingan_tervalidasi >= 16): ?>
                                <span class="badge badge-success">Lengkap</span>
                            <?php elseif($bimbingan_tervalidasi >= 8): ?>
                                <span class="badge badge-info">Siap Seminar Proposal</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Kurang <?= 8 - $bimbingan_tervalidasi ?> untuk seminar</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <small class="text-muted">
                    Minimal 8 pertemuan tervalidasi untuk mengajukan seminar proposal, 
                    16 pertemuan untuk melengkapi seluruh fase bimbingan.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Info Dosen Pembimbing & Jurnal Bimbingan -->
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Dosen Pembimbing</h3>
            </div>
            <div class="card-body">
                <div class="media align-items-center mb-3">
                    <div class="avatar avatar-lg rounded-circle bg-primary">
                        <i class="ni ni-single-02 text-white"></i>
                    </div>
                    <div class="media-body ml-3">
                        <h4 class="mb-0"><?= isset($proposal->nama_dosen) ? $proposal->nama_dosen : 'Dosen Pembimbing' ?></h4>
                        <p class="text-muted mb-0">Dosen Pembimbing</p>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="row">
                    <div class="col-12">
                        <?php if(isset($proposal->email_dosen) && $proposal->email_dosen): ?>
                        <strong>Email:</strong>
                        <p class="text-muted">
                            <i class="fa fa-envelope text-primary"></i> 
                            <a href="mailto:<?= $proposal->email_dosen ?>"><?= $proposal->email_dosen ?></a>
                        </p>
                        <?php endif; ?>
                        
                        <?php if(isset($proposal->telepon_dosen) && $proposal->telepon_dosen): ?>
                        <strong>No. Telepon:</strong>
                        <p class="text-muted">
                            <i class="fa fa-phone text-primary"></i> 
                            <a href="tel:<?= $proposal->telepon_dosen ?>"><?= $proposal->telepon_dosen ?></a>
                        </p>
                        <?php endif; ?>
                        
                        <strong>Proposal:</strong>
                        <p class="text-muted"><?= isset($proposal->judul) ? $proposal->judul : 'Tidak ada judul' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Jurnal Bimbingan -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Jurnal Bimbingan</h3>
                        <p class="mb-0 text-sm">Riwayat pertemuan bimbingan dengan dosen pembimbing</p>
                    </div>
                    <div class="col text-right">
                        <button type="button" class="btn btn-sm btn-primary" onclick="tambahJurnalBimbingan()">
                            <i class="fa fa-plus"></i> Tambah Jurnal
                        </button>
                        <?php if(!empty($jurnal_bimbingan)): ?>
                        <a href="<?= base_url('mahasiswa/bimbingan/export_jurnal') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-download"></i> Export
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Pertemuan</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Materi</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($jurnal_bimbingan)): ?>
                            <?php foreach($jurnal_bimbingan as $jurnal): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-pill badge-primary">
                                        Ke-<?= $jurnal->pertemuan_ke ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm"><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></span>
                                    <br>
                                    <small class="text-muted"><?= date('H:i', strtotime($jurnal->created_at)) ?> WIT</small>
                                </td>
                                <td>
                                    <span class="text-sm"><?= substr($jurnal->materi_bimbingan, 0, 40) ?><?= strlen($jurnal->materi_bimbingan) > 40 ? '...' : '' ?></span>
                                    <?php if(isset($jurnal->tindak_lanjut) && $jurnal->tindak_lanjut): ?>
                                    <br>
                                    <small class="text-info"><strong>TL:</strong> <?= substr($jurnal->tindak_lanjut, 0, 30) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($jurnal->status_validasi == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Tervalidasi
                                        </span>
                                        <?php if(isset($jurnal->catatan_dosen) && $jurnal->catatan_dosen): ?>
                                        <br>
                                        <small class="text-muted" title="<?= $jurnal->catatan_dosen ?>">
                                            <i class="fa fa-comment"></i> Ada catatan
                                        </small>
                                        <?php endif; ?>
                                    <?php elseif($jurnal->status_validasi == '2'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-edit"></i> Perlu Revisi
                                        </span>
                                        <?php if(isset($jurnal->catatan_dosen) && $jurnal->catatan_dosen): ?>
                                        <br>
                                        <small class="text-warning" title="<?= $jurnal->catatan_dosen ?>">
                                            <i class="fa fa-exclamation-triangle"></i> Lihat catatan
                                        </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            <i class="fa fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <a class="dropdown-item" href="#" onclick="lihatDetailJurnal(<?= $jurnal->id ?>)">
                                                <i class="fa fa-eye text-info"></i> Detail
                                            </a>
                                            <?php if($jurnal->status_validasi == '0'): ?>
                                            <a class="dropdown-item" href="<?= base_url('mahasiswa/bimbingan/edit_jurnal/' . $jurnal->id) ?>">
                                                <i class="fa fa-edit text-warning"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="hapusJurnal(<?= $jurnal->id ?>)">
                                                <i class="fa fa-trash text-danger"></i> Hapus
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-center">
                                        <i class="fa fa-book fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada jurnal bimbingan</h5>
                                        <p class="text-muted">Mulai tambahkan jurnal bimbingan dengan dosen pembimbing Anda.</p>
                                        <button type="button" class="btn btn-primary" onclick="tambahJurnalBimbingan()">
                                            <i class="fa fa-plus"></i> Tambah Jurnal Pertama
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Jurnal - TEMPLATE DIPERBAIKI -->
<div class="modal fade" id="modalTambahJurnal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('mahasiswa/bimbingan/tambah_jurnal') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jurnal Bimbingan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Info Mahasiswa (Seragam dengan dosen) -->
                    <div class="form-group">
                        <label>Mahasiswa</label>
                        <input type="text" class="form-control" value="<?= $this->session->userdata('nama') ?> (<?= $this->session->userdata('username') ?>)" readonly>
                        <small class="form-text text-muted">Jurnal bimbingan akan tercatat atas nama Anda</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pertemuan ke- *</label>
                                <input type="number" class="form-control" name="pertemuan_ke" min="1" 
                                       value="<?= isset($total_bimbingan) ? ($total_bimbingan + 1) : 1 ?>" required>
                                <small class="form-text text-muted">Nomor urut pertemuan bimbingan</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Durasi (menit)</label>
                                <input type="number" class="form-control" name="durasi_bimbingan" min="15" max="180" placeholder="60">
                                <small class="form-text text-muted">Estimasi durasi (opsional)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" rows="4" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini, misalnya: diskusi BAB 1, review metodologi, perbaikan rumusan masalah, dll."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Mahasiswa</label>
                        <textarea class="form-control" name="catatan_mahasiswa" rows="3" 
                                  placeholder="Catatan atau pertanyaan dari Anda untuk dosen"></textarea>
                        <small class="form-text text-muted">Field ini akan terlihat oleh dosen pembimbing</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" rows="3" 
                                  placeholder="Tugas atau tindak lanjut yang diberikan dosen"></textarea>
                    </div>

                    <!-- Info untuk mahasiswa -->
                    <div class="alert alert-success">
                        <i class="fa fa-info-circle"></i> 
                        <strong>PERBAIKAN BARU:</strong> Anda sekarang dapat membuat jurnal bimbingan baru meskipun ada jurnal sebelumnya yang masih pending validasi. 
                        Jika ada pertemuan dengan nomor yang sama, sistem akan memperbarui jurnal yang sudah ada.
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-envelope"></i> 
                        <strong>Notifikasi:</strong> Jurnal yang sudah diinput akan dikirim ke dosen pembimbing untuk divalidasi. 
                        Pastikan informasi yang dimasukkan sudah benar.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Jurnal -->
<div class="modal fade" id="modalDetailJurnal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Jurnal Bimbingan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <!-- Content akan diisi via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php
$template_data['content'] = ob_get_clean();

// Add JavaScript untuk functionality
$template_data['script'] = '
<script>
// Tambah Jurnal Bimbingan
function tambahJurnalBimbingan() {
    $("#modalTambahJurnal").modal("show");
}

// Lihat Detail Jurnal - PERBAIKAN FUNCTION
function lihatDetailJurnal(jurnalId) {
    ' . ((!empty($jurnal_bimbingan)) ? '
    var jurnalData = ' . json_encode($jurnal_bimbingan ?? [], JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT) . ';
    var jurnal = null;
    
    // Find jurnal by ID
    for (var i = 0; i < jurnalData.length; i++) {
        if (jurnalData[i].id == jurnalId) {
            jurnal = jurnalData[i];
            break;
        }
    }
    
    if (jurnal) {
        var statusBadge = "";
        var catatanDosen = jurnal.catatan_dosen || "Belum ada catatan dari dosen";
        
        if (jurnal.status_validasi == "1") {
            statusBadge = "<span class=\"badge badge-success\"><i class=\"fa fa-check\"></i> Tervalidasi</span>";
        } else if (jurnal.status_validasi == "2") {
            statusBadge = "<span class=\"badge badge-warning\"><i class=\"fa fa-edit\"></i> Perlu Revisi</span>";
        } else {
            statusBadge = "<span class=\"badge badge-secondary\"><i class=\"fa fa-clock\"></i> Pending Validasi</span>";
        }
        
        var content = "<div class=\"row\">" +
            "<div class=\"col-md-6\">" +
                "<strong>Pertemuan ke:</strong> " + jurnal.pertemuan_ke + "<br>" +
                "<strong>Tanggal:</strong> " + jurnal.tanggal_bimbingan + "<br>" +
                "<strong>Status:</strong> " + statusBadge +
            "</div>" +
            "<div class=\"col-md-6\">" +
                "<strong>Dibuat:</strong> " + jurnal.created_at + "<br>" +
                (jurnal.tanggal_validasi ? "<strong>Divalidasi:</strong> " + jurnal.tanggal_validasi + "<br>" : "") +
            "</div>" +
        "</div>" +
        "<hr>" +
        "<div class=\"form-group\">" +
            "<strong>Materi Bimbingan:</strong>" +
            "<div class=\"bg-light p-3 rounded mt-2\">" +
                (jurnal.materi_bimbingan || "Tidak ada materi") +
            "</div>" +
        "</div>" +
        "<div class=\"form-group\">" +
            "<strong>Tindak Lanjut:</strong>" +
            "<div class=\"bg-light p-3 rounded mt-2\">" +
                (jurnal.tindak_lanjut || "Tidak ada tindak lanjut khusus") +
            "</div>" +
        "</div>" +
        "<div class=\"form-group\">" +
            "<strong>Catatan Dosen:</strong>" +
            "<div class=\"bg-light p-3 rounded mt-2\">" +
                catatanDosen +
            "</div>" +
        "</div>";
        
        document.getElementById("modalDetailContent").innerHTML = content;
        $("#modalDetailJurnal").modal("show");
    } else {
        alert("Data jurnal tidak ditemukan.");
    }
    ' : 'alert("Belum ada data jurnal bimbingan.");') . '
}

// Hapus Jurnal - PERBAIKAN FUNCTION
function hapusJurnal(jurnalId) {
    if (confirm("Apakah Anda yakin ingin menghapus jurnal ini? Jurnal yang sudah divalidasi tidak dapat dihapus.")) {
        // Redirect ke URL hapus
        window.location.href = "' . base_url('mahasiswa/bimbingan/hapus_jurnal/') . '" + jurnalId;
    }
}

// Document ready function
$(document).ready(function() {
    console.log("JavaScript functions loaded successfully");
    
    // Test functions
    if (typeof tambahJurnalBimbingan !== "function") {
        console.error("tambahJurnalBimbingan function not defined");
    }
    if (typeof lihatDetailJurnal !== "function") {
        console.error("lihatDetailJurnal function not defined");
    }
    if (typeof hapusJurnal !== "function") {
        console.error("hapusJurnal function not defined");
    }
});
</script>
';

// Load template
$this->load->view('template/mahasiswa', $template_data);
?>