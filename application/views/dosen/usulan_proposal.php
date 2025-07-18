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

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">
                            <i class="ni ni-app mr-2"></i>
                            Usulan Proposal - Penunjukan Pembimbing
                        </h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Phase 1 Workflow:</strong> Dosen menyetujui atau menolak penunjukan sebagai pembimbing yang telah ditetapkan oleh Kaprodi.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="ni ni-single-02"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Menunggu Persetujuan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= isset($proposals) ? count($proposals) : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Proposals pending</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Dosen ID</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $this->session->userdata('id') ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-user"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Session active</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Status</h5>
                        <span class="h2 font-weight-bold mb-0">ONLINE</span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> Ready</span>
                    <span class="text-nowrap">to review</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Proposals Menunggu Persetujuan -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fa fa-list-alt mr-2 text-primary"></i>
                            Penunjukan Pembimbing Menunggu Persetujuan
                        </h3>
                        <p class="mb-0 text-sm">Kaprodi telah menunjuk Anda sebagai pembimbing untuk proposal berikut</p>
                    </div>
                    <div class="col text-right">
                        <span class="badge badge-pill badge-lg badge-warning">
                            <i class="fa fa-clock mr-1"></i>
                            <?= isset($proposals) ? count($proposals) : 0 ?> Menunggu
                        </span>
                    </div>
                </div>
            </div>
            
            <?php if(isset($proposals) && !empty($proposals)): ?>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">
                                <i class="fa fa-user mr-1"></i>
                                Mahasiswa
                            </th>
                            <th scope="col">
                                <i class="fa fa-file-alt mr-1"></i>
                                Proposal
                            </th>
                            <th scope="col">
                                <i class="fa fa-calendar mr-1"></i>
                                Tanggal Penetapan
                            </th>
                            <th scope="col">
                                <i class="fa fa-info-circle mr-1"></i>
                                Status
                            </th>
                            <th scope="col">
                                <i class="fa fa-cogs mr-1"></i>
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($proposals as $proposal): ?>
                        <tr>
                            <td>
                                <div class="media align-items-center">
                                    <div class="avatar rounded-circle mr-3 bg-primary">
                                        <i class="ni ni-single-02 text-white"></i>
                                    </div>
                                    <div class="media-body">
                                        <span class="mb-0 text-sm font-weight-bold"><?= $proposal->nama_mahasiswa ?></span>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fa fa-id-card mr-1"></i>
                                            NIM: <?= $proposal->nim ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fa fa-university mr-1"></i>
                                            <?= isset($proposal->nama_prodi) ? $proposal->nama_prodi : 'N/A' ?>
                                        </small>
                                        <br>
                                        <small class="text-info">
                                            <i class="fa fa-envelope mr-1"></i>
                                            <?= isset($proposal->email_mahasiswa) ? $proposal->email_mahasiswa : 'N/A' ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="mb-2">
                                    <span class="text-sm font-weight-bold text-dark">
                                        <?= substr($proposal->judul, 0, 80) ?><?= strlen($proposal->judul) > 80 ? '...' : '' ?>
                                    </span>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fa fa-map-marker-alt mr-1 text-danger"></i>
                                        <?= isset($proposal->lokasi_penelitian) ? $proposal->lokasi_penelitian : 'N/A' ?>
                                    </small>
                                </div>
                                
                                <div>
                                    <span class="badge badge-pill badge-secondary">
                                        <i class="fa fa-tag mr-1"></i>
                                        <?= isset($proposal->jenis_penelitian) ? $proposal->jenis_penelitian : 'N/A' ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <?php if(isset($proposal->tanggal_penetapan) && $proposal->tanggal_penetapan): ?>
                                    <div class="text-sm">
                                        <i class="fa fa-calendar-alt mr-1 text-primary"></i>
                                        <?= date('d/m/Y', strtotime($proposal->tanggal_penetapan)) ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fa fa-clock mr-1"></i>
                                        <?= date('H:i', strtotime($proposal->tanggal_penetapan)) ?> WIT
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="fa fa-minus-circle mr-1"></i>
                                        Tidak ada data
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-dot mr-4">
                                    <i class="bg-warning"></i>
                                    Menunggu Persetujuan
                                </span>
                                <br>
                                <small class="text-muted mt-1">
                                    <i class="fa fa-hourglass-half mr-1"></i>
                                    Pending Review
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a class="btn btn-sm btn-primary" href="<?= base_url('dosen/usulan_proposal/detail/' . $proposal->id) ?>">
                                        <i class="fa fa-eye mr-1"></i>
                                        Detail & Respon
                                    </a>
                                    <?php if(isset($proposal->file_draft_proposal) && $proposal->file_draft_proposal): ?>
                                    <a class="btn btn-sm btn-outline-secondary" 
                                       href="<?= base_url('cdn/vendor/proposal/' . $proposal->file_draft_proposal) ?>" 
                                       target="_blank">
                                        <i class="fa fa-download mr-1"></i>
                                        Download
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php else: ?>
            <!-- Empty State -->
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fa fa-inbox fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">Tidak ada penunjukan pembimbing yang menunggu</h4>
                    <p class="text-muted mb-4">
                        Saat ini tidak ada proposal yang memerlukan persetujuan Anda sebagai pembimbing.<br>
                        Kaprodi akan memberikan notifikasi jika ada penunjukan baru.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle mr-2"></i>
                                <strong>Info:</strong> 
                                Jika Anda sudah menyetujui penunjukan, silakan akses menu <strong>Bimbingan</strong> untuk melihat mahasiswa bimbingan Anda.
                            </div>
                        </div>
                    </div>
                    <a href="<?= base_url('dosen/bimbingan') ?>" class="btn btn-primary">
                        <i class="fa fa-users mr-2"></i>
                        Lihat Mahasiswa Bimbingan
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card bg-gradient-secondary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="text-white mb-0">
                            <i class="fa fa-info-circle mr-2"></i>
                            Panduan Workflow Phase 1
                        </h4>
                        <p class="text-white mt-2 mb-0">
                            1. Review detail proposal mahasiswa → 2. Setujui atau tolak penunjukan → 3. Sistem otomatis kirim notifikasi → 4. Jika disetujui, mahasiswa masuk Phase 2 (Bimbingan)
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('dosen/bimbingan') ?>" class="btn btn-neutral">
                            <i class="fa fa-arrow-right mr-1"></i>
                            Phase 2: Bimbingan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>