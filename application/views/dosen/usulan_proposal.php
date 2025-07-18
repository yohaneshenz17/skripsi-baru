<?php $this->load->view('template/dosen', ['title' => $title, 'content' => $this->load->view('dosen/usulan_proposal_content', $this, true), 'script' => '']); ?>

<!-- Content untuk usulan_proposal_content.php -->

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
                        <h3 class="text-white mb-0">Usulan Proposal - Penunjukan Pembimbing</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Phase 1 Workflow:</strong> Dosen menyetujui atau menolak penunjukan sebagai pembimbing yang telah ditetapkan oleh Kaprodi.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="ni ni-app"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Proposals Menunggu Persetujuan -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Penunjukan Pembimbing Menunggu Persetujuan</h3>
                        <p class="mb-0 text-sm">Kaprodi telah menunjuk Anda sebagai pembimbing untuk proposal berikut</p>
                    </div>
                    <div class="col text-right">
                        <span class="badge badge-pill badge-warning">
                            <?= count($proposals) ?> Menunggu
                        </span>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Proposal</th>
                            <th scope="col">Tanggal Penetapan</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($proposals)): ?>
                            <?php foreach($proposals as $proposal): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?= $proposal->nama_mahasiswa ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?= $proposal->nim ?></small>
                                            <br>
                                            <small class="text-muted"><?= $proposal->nama_prodi ?></small>
                                            <br>
                                            <small class="text-info">📞 <?= $proposal->nomor_telepon ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold"><?= substr($proposal->judul, 0, 60) ?><?= strlen($proposal->judul) > 60 ? '...' : '' ?></span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fa fa-map-marker-alt"></i> <?= $proposal->lokasi_penelitian ?>
                                    </small>
                                    <br>
                                    <span class="badge badge-pill badge-secondary"><?= $proposal->jenis_penelitian ?></span>
                                </td>
                                <td>
                                    <?php if($proposal->tanggal_penetapan): ?>
                                        <span class="text-sm"><?= date('d/m/Y H:i', strtotime($proposal->tanggal_penetapan)) ?></span>
                                        <br>
                                        <small class="text-muted">oleh <?= $proposal->nama_kaprodi ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-dot mr-4">
                                        <i class="bg-warning"></i>
                                        Menunggu Persetujuan
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <a class="dropdown-item" href="<?= base_url('dosen/usulan_proposal/detail/' . $proposal->id) ?>">
                                                <i class="fa fa-eye"></i> Detail & Respon
                                            </a>
                                            <?php if($proposal->file_proposal): ?>
                                            <a class="dropdown-item" href="<?= base_url('cdn/vendor/proposal/' . $proposal->file_proposal) ?>" target="_blank">
                                                <i class="fa fa-download"></i> Download Proposal
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
                                        <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Tidak ada penunjukan pembimbing yang menunggu</h5>
                                        <p class="text-muted">Saat ini tidak ada proposal yang memerlukan persetujuan Anda sebagai pembimbing.</p>
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

<!-- Riwayat Persetujuan -->
<?php if(!empty($riwayat_proposals)): ?>
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Riwayat Persetujuan</h3>
                        <p class="mb-0 text-sm">10 proposal terbaru yang telah Anda respon</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Proposal</th>
                            <th scope="col">Tanggal Respon</th>
                            <th scope="col">Status</th>
                            <th scope="col">Komentar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($riwayat_proposals as $riwayat): ?>
                        <tr>
                            <td>
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm font-weight-bold"><?= $riwayat->nama_mahasiswa ?></span>
                                        <br>
                                        <small class="text-muted">NIM: <?= $riwayat->nim ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm"><?= substr($riwayat->judul, 0, 50) ?><?= strlen($riwayat->judul) > 50 ? '...' : '' ?></span>
                            </td>
                            <td>
                                <span class="text-sm"><?= date('d/m/Y H:i', strtotime($riwayat->tanggal_respon_pembimbing)) ?></span>
                            </td>
                            <td>
                                <?php if($riwayat->status_pembimbing == '1'): ?>
                                    <span class="badge badge-success">
                                        <i class="fa fa-check"></i> Disetujui
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger">
                                        <i class="fa fa-times"></i> Ditolak
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($riwayat->komentar_pembimbing): ?>
                                    <small class="text-muted"><?= substr($riwayat->komentar_pembimbing, 0, 50) ?><?= strlen($riwayat->komentar_pembimbing) > 50 ? '...' : '' ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
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

<!-- Quick Actions Panel -->
<div class="row mt-4">
    <div class="col-lg-4">
        <div class="card bg-gradient-success">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Total Disetujui</h5>
                        <span class="h2 font-weight-bold mb-0 text-white">
                            <?= count(array_filter($riwayat_proposals, function($p) { return $p->status_pembimbing == '1'; })) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-gradient-danger">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Total Ditolak</h5>
                        <span class="h2 font-weight-bold mb-0 text-white">
                            <?= count(array_filter($riwayat_proposals, function($p) { return $p->status_pembimbing == '2'; })) ?>
                        </span>
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
    <div class="col-lg-4">
        <div class="card bg-gradient-warning">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Menunggu</h5>
                        <span class="h2 font-weight-bold mb-0 text-white">
                            <?= count($proposals) ?>
                        </span>
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