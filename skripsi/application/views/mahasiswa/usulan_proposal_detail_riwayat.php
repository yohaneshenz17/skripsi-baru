<?php $this->load->view('template/header', ['title' => $title ?? 'Detail Riwayat Proposal']); ?>

<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    <div class="container-fluid">
        <div class="header-body">
            <!-- Page header -->
            <div class="row align-items-center py-4">
                <div class="col-lg-6 col-7">
                    <h6 class="h2 text-white d-inline-block mb-0">Detail Riwayat Proposal</h6>
                    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                        <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                            <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="<?= base_url('dosen/usulan_proposal') ?>">Usulan Proposal</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail Riwayat</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-lg-6 col-5 text-right">
                    <a href="<?= base_url('dosen/usulan_proposal') ?>" class="btn btn-sm btn-neutral">
                        <i class="fa fa-arrow-left mr-2"></i>
                        Kembali ke Usulan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid mt--7">
    <!-- Alert jika ada flash message -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="ni ni-like-2"></i></span>
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

    <!-- Status Card -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card bg-secondary shadow border-0">
                <div class="card-header bg-transparent pb-3">
                    <div class="text-center">
                        <h3 class="mb-2">
                            <i class="fa fa-file-alt mr-2 text-primary"></i>
                            Riwayat Detail Proposal
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
                        <tr>
                            <td class="font-weight-bold">Tempat, Tgl Lahir</td>
                            <td><?= $proposal->tempat_lahir ?>, <?= date('d/m/Y', strtotime($proposal->tanggal_lahir)) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Jenis Kelamin</td>
                            <td><?= ($proposal->jenis_kelamin == 'L') ? 'Laki-laki' : 'Perempuan' ?></td>
                        </tr>
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
                            <td><?= date('d/m/Y H:i', strtotime($proposal->tanggal_pengajuan)) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Disetujui Kaprodi</td>
                            <td><?= date('d/m/Y H:i', strtotime($proposal->tanggal_disetujui)) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Ditetapkan Pembimbing</td>
                            <td><?= date('d/m/Y H:i', strtotime($proposal->tanggal_penetapan)) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Respon Anda</td>
                            <td><?= date('d/m/Y H:i', strtotime($proposal->tanggal_respon_pembimbing)) ?></td>
                        </tr>
                    </table>
                    
                    <!-- File Proposal jika ada -->
                    <?php if(!empty($proposal->file_proposal)): ?>
                    <div class="mt-3">
                        <h6 class="heading-small text-muted mb-2">File Proposal:</h6>
                        <a href="<?= base_url('cdn/files/proposal/' . $proposal->file_proposal) ?>" 
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

    <!-- Timeline Aktivitas -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header border-0">
                    <h3 class="mb-0">
                        <i class="fa fa-clock mr-2 text-warning"></i>
                        Timeline Aktivitas Proposal
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-one-side">
                        <div class="timeline-block">
                            <span class="timeline-step">
                                <i class="fa fa-paper-plane text-success"></i>
                            </span>
                            <div class="timeline-content">
                                <small class="text-muted font-weight-bold">
                                    <?= date('d M Y, H:i', strtotime($proposal->tanggal_pengajuan)) ?>
                                </small>
                                <h6 class="text-sm mt-1 mb-0">Proposal Diajukan Mahasiswa</h6>
                                <p class="text-sm text-muted mt-1">
                                    Mahasiswa <?= $proposal->nama_mahasiswa ?> mengajukan proposal skripsi.
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-block">
                            <span class="timeline-step">
                                <i class="fa fa-check text-success"></i>
                            </span>
                            <div class="timeline-content">
                                <small class="text-muted font-weight-bold">
                                    <?= date('d M Y, H:i', strtotime($proposal->tanggal_disetujui)) ?>
                                </small>
                                <h6 class="text-sm mt-1 mb-0">Disetujui Kaprodi</h6>
                                <p class="text-sm text-muted mt-1">
                                    Proposal disetujui oleh <?= $proposal->nama_kaprodi ?>.
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-block">
                            <span class="timeline-step">
                                <i class="fa fa-user-tie text-info"></i>
                            </span>
                            <div class="timeline-content">
                                <small class="text-muted font-weight-bold">
                                    <?= date('d M Y, H:i', strtotime($proposal->tanggal_penetapan)) ?>
                                </small>
                                <h6 class="text-sm mt-1 mb-0">Ditetapkan Pembimbing</h6>
                                <p class="text-sm text-muted mt-1">
                                    Anda ditetapkan sebagai dosen pembimbing oleh Kaprodi.
                                </p>
                            </div>
                        </div>
                        
                        <div class="timeline-block">
                            <span class="timeline-step">
                                <i class="fa fa-<?= ($proposal->status_pembimbing == '1') ? 'thumbs-up text-success' : 'thumbs-down text-danger' ?>"></i>
                            </span>
                            <div class="timeline-content">
                                <small class="text-muted font-weight-bold">
                                    <?= date('d M Y, H:i', strtotime($proposal->tanggal_respon_pembimbing)) ?>
                                </small>
                                <h6 class="text-sm mt-1 mb-0">
                                    Respon Pembimbing: <?= ($proposal->status_pembimbing == '1') ? 'Disetujui' : 'Ditolak' ?>
                                </h6>
                                <p class="text-sm text-muted mt-1">
                                    Anda <?= ($proposal->status_pembimbing == '1') ? 'menyetujui' : 'menolak' ?> penunjukan sebagai pembimbing.
                                    <?php if($proposal->status_pembimbing == '2' && !empty($proposal->komentar_pembimbing)): ?>
                                    <br><strong>Alasan:</strong> <?= $proposal->komentar_pembimbing ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
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
                        
                        <?php if($proposal->status_pembimbing == '1'): ?>
                        <a href="<?= base_url('dosen/bimbingan/mahasiswa/' . $proposal->mahasiswa_id) ?>" class="btn btn-primary">
                            <i class="fa fa-user-graduate mr-2"></i>
                            Lihat Progress Bimbingan
                        </a>
                        <?php endif; ?>
                        
                        <button onclick="window.print()" class="btn btn-info">
                            <i class="fa fa-print mr-2"></i>
                            Cetak Detail
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('template/footer'); ?>