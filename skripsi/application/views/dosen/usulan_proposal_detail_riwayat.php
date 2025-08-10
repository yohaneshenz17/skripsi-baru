<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Detail Riwayat Proposal') ?>

<?php $this->app->section() ?>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
        <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="alert-icon"><i class="ni ni-support-16"></i></span>
        <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Breadcrumb -->
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm">
                    <a class="text-white" href="<?= base_url('dosen/dashboard') ?>">
                        <i class="ni ni-app"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item text-sm">
                    <a class="text-white" href="<?= base_url('dosen/usulan_proposal') ?>">Usulan Proposal</a>
                </li>
                <li class="breadcrumb-item text-sm text-white active" aria-current="page">Detail Riwayat</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Status Card -->
<div class="row">
    <div class="col-lg-12">
        <div class="card bg-secondary shadow border-0">
            <div class="card-header bg-transparent pb-3">
                <div class="text-center">
                    <h3 class="mb-2">
                        <i class="fa fa-file-alt mr-2 text-primary"></i>
                        Detail Riwayat Proposal
                    </h3>
                    <p class="text-muted mb-0">
                        Proposal yang sudah Anda respon pada tanggal 
                        <strong><?= date('d F Y', strtotime($proposal->tanggal_respon_pembimbing)) ?></strong>
                    </p>
                </div>
            </div>
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <!-- Status Response -->
                        <div class="alert <?= ($proposal->status_pembimbing == '1') ? 'alert-success' : 'alert-danger' ?> alert-dismissible" role="alert">
                            <span class="alert-icon">
                                <i class="<?= ($proposal->status_pembimbing == '1') ? 'fa fa-check-circle' : 'fa fa-times-circle' ?>"></i>
                            </span>
                            <span class="alert-text">
                                <strong>Status Respon Anda:</strong> 
                                <?= ($proposal->status_pembimbing == '1') ? 'DISETUJUI - Anda menerima penunjukan sebagai pembimbing' : 'DITOLAK - Anda menolak penunjukan sebagai pembimbing' ?>
                            </span>
                        </div>
                        
                        <?php if($proposal->status_pembimbing == '2' && !empty($proposal->komentar_pembimbing)): ?>
                        <div class="alert alert-warning" role="alert">
                            <span class="alert-icon"><i class="fa fa-comment"></i></span>
                            <span class="alert-text">
                                <strong>Alasan Penolakan:</strong><br>
                                <?= nl2br(htmlspecialchars($proposal->komentar_pembimbing)) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Mahasiswa dan Proposal -->
<div class="row">
    <!-- Data Mahasiswa -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header border-0">
                <h3 class="mb-0">
                    <i class="fa fa-user mr-2 text-primary"></i>
                    Data Mahasiswa
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <?php
                    $foto_mahasiswa = (!empty($proposal->foto_mahasiswa)) ? $proposal->foto_mahasiswa : 'default.png';
                    $foto_path = base_url('cdn/img/mahasiswa/' . $foto_mahasiswa);
                    ?>
                    <img src="<?= $foto_path ?>" alt="Foto <?= $proposal->nama_mahasiswa ?>" 
                         class="avatar avatar-xl rounded-circle">
                </div>
                
                <table class="table table-sm">
                    <tr>
                        <td class="font-weight-bold" width="40%">Nama</td>
                        <td><?= $proposal->nama_mahasiswa ?></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">NIM</td>
                        <td><?= $proposal->nim ?></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Program Studi</td>
                        <td><?= $proposal->nama_prodi ?></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Email</td>
                        <td><?= $proposal->email_mahasiswa ?></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">No. Telepon</td>
                        <td><?= $proposal->nomor_telepon ?? '-' ?></td>
                    </tr>
                    <?php if(!empty($proposal->tempat_lahir) && !empty($proposal->tanggal_lahir)): ?>
                    <tr>
                        <td class="font-weight-bold">Tempat, Tgl Lahir</td>
                        <td><?= $proposal->tempat_lahir ?>, <?= date('d/m/Y', strtotime($proposal->tanggal_lahir)) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(!empty($proposal->jenis_kelamin)): ?>
                    <tr>
                        <td class="font-weight-bold">Jenis Kelamin</td>
                        <td><?= ($proposal->jenis_kelamin == 'L') ? 'Laki-laki' : 'Perempuan' ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Detail Proposal -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header border-0">
                <h3 class="mb-0">
                    <i class="fa fa-file-text mr-2 text-success"></i>
                    Detail Proposal
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td class="font-weight-bold" width="30%">ID Proposal</td>
                        <td>#<?= str_pad($proposal->id, 4, '0', STR_PAD_LEFT) ?></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Judul</td>
                        <td class="text-wrap"><?= $proposal->judul ?></td>
                    </tr>
                    <?php if(!empty($proposal->deskripsi)): ?>
                    <tr>
                        <td class="font-weight-bold">Deskripsi</td>
                        <td class="text-wrap"><?= nl2br(htmlspecialchars($proposal->deskripsi)) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="font-weight-bold">Tanggal Diajukan</td>
                        <td><?= date('d/m/Y H:i', strtotime($proposal->created_at)) ?></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Respon Anda</td>
                        <td><?= date('d/m/Y H:i', strtotime($proposal->tanggal_respon_pembimbing)) ?></td>
                    </tr>
                </table>
                
                <!-- File Proposal jika ada -->
                <?php if(!empty($proposal->file_draft_proposal)): ?>
                <div class="mt-3">
                    <h6 class="heading-small text-muted mb-2">File Proposal:</h6>
                    <a href="<?= base_url('cdn/proposals/' . $proposal->file_draft_proposal) ?>" 
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-download mr-2"></i>
                        Download Proposal
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row">
    <div class="col-lg-12">
        <div class="card bg-gradient-secondary shadow border-0">
            <div class="card-body">
                <div class="text-center">
                    <a href="<?= base_url('dosen/usulan_proposal') ?>" class="btn btn-neutral">
                        <i class="fa fa-arrow-left mr-2"></i>
                        Kembali ke Usulan Proposal
                    </a>
                    
                    <button onclick="window.print()" class="btn btn-info">
                        <i class="fa fa-print mr-2"></i>
                        Cetak Detail
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>