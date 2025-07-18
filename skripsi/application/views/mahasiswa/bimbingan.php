<?php $this->load->view('template/mahasiswa', ['title' => $title, 'content' => $this->load->view('mahasiswa/bimbingan_content', $this, true), 'script' => '']); ?>

<!-- Content untuk bimbingan_content.php -->

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

<?php if (isset($pending_proposal)): ?>
<!-- Status Pending Approval -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-warning">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">⏳ Menunggu Persetujuan Dosen Pembimbing</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Kaprodi</strong> telah menetapkan <strong><?= $pending_proposal->nama_dosen_ditunjuk ?></strong> sebagai dosen pembimbing Anda. 
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
                <h4>Proposal Anda Sedang Menunggu Persetujuan</h4>
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

<?php elseif (!isset($proposal)): ?>
<!-- Belum Ada Proposal -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-file-alt fa-4x text-muted mb-3"></i>
                <h4>Belum Ada Proposal yang Disetujui</h4>
                <p class="text-muted">
                    Anda belum memiliki proposal yang disetujui dengan dosen pembimbing. 
                    Silakan ajukan proposal terlebih dahulu atau tunggu persetujuan dari kaprodi dan dosen pembimbing.
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
<!-- Main Content: Bimbingan Active -->

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-success">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">📚 Bimbingan Skripsi - Phase 2</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Dosen Pembimbing:</strong> <?= $proposal->nama_dosen ?> | 
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

<!-- Info Dosen Pembimbing -->
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
                        <h4 class="mb-0"><?= $proposal->nama_dosen ?></h4>
                        <p class="text-muted mb-0">Dosen Pembimbing</p>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="row">
                    <div class="col-12">
                        <?php if($proposal->email_dosen): ?>
                        <strong>Email:</strong>
                        <p class="text-muted">
                            <i class="fa fa-envelope text-primary"></i> 
                            <a href="mailto:<?= $proposal->email_dosen ?>"><?= $proposal->email_dosen ?></a>
                        </p>
                        <?php endif; ?>
                        
                        <?php if($proposal->telepon_dosen): ?>
                        <strong>No. Telepon:</strong>
                        <p class="text-muted">
                            <i class="fa fa-phone text-primary"></i> 
                            <a href="tel:<?= $proposal->telepon_dosen ?>"><?= $proposal->telepon_dosen ?></a>
                        </p>
                        <?php endif; ?>
                        
                        <strong>Proposal:</strong>
                        <p class="text-muted"><?= $proposal->judul ?></p>
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
                                    <?php if($jurnal->tindak_lanjut): ?>
                                    <br>
                                    <small class="text-info"><strong>TL:</strong> <?= substr($jurnal->tindak_lanjut, 0, 30) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($jurnal->status_validasi == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Tervalidasi
                                        </span>
                                        <?php if($jurnal->catatan_dosen): ?>
                                        <br>
                                        <small class="text-muted" title="<?= $jurnal->catatan_dosen ?>">
                                            <i class="fa fa-comment"></i> Ada catatan
                                        </small>
                                        <?php endif; ?>
                                    <?php elseif($jurnal->status_validasi == '2'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-edit"></i> Perlu Revisi
                                        </span>
                                        <?php if($jurnal->catatan_dosen): ?>
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

<!-- Modal Tambah Jurnal -->
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pertemuan ke- *</label>
                                <input type="number" class="form-control" name="pertemuan_ke" min="1" value="<?= $total_bimbingan + 1 ?>" required>
                                <small class="form-text text-muted">Nomor urut pertemuan bimbingan</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" rows="4" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini, misalnya: diskusi BAB 1, review metodologi, perbaikan rumusan masalah, dll."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" rows="3" 
                                  placeholder="Tugas atau tindak lanjut yang diberikan dosen (opsional)"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Catatan:</strong> Jurnal yang sudah diinput akan dikirim ke dosen pembimbing untuk divalidasi. 
                        Pastikan informasi yang dimasukkan sudah benar.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jurnal</button>
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

<script>
// Tambah Jurnal Bimbingan
function tambahJurnalBimbingan() {
    $('#modalTambahJurnal').modal('show');
}

// Lihat Detail Jurnal
function lihatDetailJurnal(jurnalId) {
    // Find jurnal data from PHP
    <?php if(!empty($jurnal_bimbingan)): ?>
    const jurnalData = <?= json_encode($jurnal_bimbingan) ?>;
    const jurnal = jurnalData.find(j => j.id == jurnalId);
    
    if (jurnal) {
        let statusBadge = '';
        let catatanDosen = jurnal.catatan_dosen || 'Tidak ada catatan';
        
        if (jurnal.status_validasi == '1') {
            statusBadge = '<span class="badge badge-success"><i class="fa fa-check"></i> Tervalidasi</span>';
        } else if (jurnal.status_validasi == '2') {
            statusBadge = '<span class="badge badge-warning"><i class="fa fa-edit"></i> Perlu Revisi</span>';
        } else {
            statusBadge = '<span class="badge badge-secondary"><i class="fa fa-clock"></i> Pending Validasi</span>';
        }
        
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Pertemuan ke:</strong> ${jurnal.pertemuan_ke}<br>
                    <strong>Tanggal:</strong> ${new Date(jurnal.tanggal_bimbingan).toLocaleDateString('id-ID')}<br>
                    <strong>Status:</strong> ${statusBadge}
                </div>
                <div class="col-md-6">
                    <strong>Dibuat:</strong> ${new Date(jurnal.created_at).toLocaleString('id-ID')}<br>
                    ${jurnal.tanggal_validasi ? '<strong>Divalidasi:</strong> ' + new Date(jurnal.tanggal_validasi).toLocaleString('id-ID') : ''}
                </div>
            </div>
            <hr>
            <div class="form-group">
                <strong>Materi Bimbingan:</strong>
                <div class="bg-light p-3 rounded mt-2">
                    ${jurnal.materi_bimbingan}
                </div>
            </div>
            <div class="form-group">
                <strong>Tindak Lanjut:</strong>
                <div class="bg-light p-3 rounded mt-2">
                    ${jurnal.tindak_lanjut || 'Tidak ada tindak lanjut khusus'}
                </div>
            </div>
            <div class="form-group">
                <strong>Catatan Dosen:</strong>
                <div class="bg-light p-3 rounded mt-2">
                    ${catatanDosen}
                </div>
            </div>
        `;
        
        document.getElementById('modalDetailContent').innerHTML = content;
        $('#modalDetailJurnal').modal('show');
    }
    <?php endif; ?>
}

// Hapus Jurnal
function hapusJurnal(jurnalId) {
    if (confirm('Apakah Anda yakin ingin menghapus jurnal ini? Jurnal yang sudah divalidasi tidak dapat dihapus.')) {
        window.location.href = '<?= base_url('mahasiswa/bimbingan/hapus_jurnal/') ?>' + jurnalId;
    }
}
</script>