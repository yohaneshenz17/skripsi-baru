<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- CSS untuk spacing dan styling -->
<style>
.content-spacing { margin-bottom: 100px; }
.last-card { margin-bottom: 80px !important; }
@media (max-width: 768px) {
    .content-spacing { margin-bottom: 120px; }
    .last-card { margin-bottom: 100px !important; }
}
.jurnal-pending-highlight {
    border-left: 4px solid #ffc107;
    background-color: #fff8e1;
}
.jurnal-urgent {
    border-left: 4px solid #dc3545;
    background-color: #fff5f5;
}
.alert-dismissible .close {
    z-index: 2;
}
.dropdown-menu {
    z-index: 1050;
}
</style>

<div class="content-spacing">

<!-- Alert Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-check"></i></span>
    <span class="alert-text"><?php echo $this->session->flashdata('success'); ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?php echo $this->session->flashdata('error'); ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('info')): ?>
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-info-circle"></i></span>
    <span class="alert-text"><?php echo $this->session->flashdata('info'); ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Panel Jurnal Pending yang Urgent -->
<?php if(isset($jurnal_pending_list) && !empty($jurnal_pending_list)): ?>
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card border-warning shadow">
            <div class="card-header bg-warning text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0 text-white">⏰ Jurnal Bimbingan Perlu Validasi</h3>
                        <p class="mb-0 text-white opacity-8">
                            <strong><?php echo count($jurnal_pending_list); ?> jurnal</strong> menunggu validasi Anda
                        </p>
                    </div>
                    <div class="col-auto">
                        <span class="badge badge-light badge-pill"><?php echo count($jurnal_pending_list); ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Pertemuan</th>
                                <th>Tanggal</th>
                                <th>Materi</th>
                                <th>Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($jurnal_pending_list as $jurnal): ?>
                            <?php 
                            $days_pending = floor((time() - strtotime($jurnal->created_at)) / (60 * 60 * 24));
                            $urgent_class = $days_pending > 3 ? 'jurnal-urgent' : 'jurnal-pending-highlight';
                            ?>
                            <tr class="<?php echo $urgent_class; ?>">
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="font-weight-bold text-sm"><?php echo htmlspecialchars($jurnal->nama_mahasiswa); ?></span>
                                            <br><small class="text-muted">NIM: <?php echo htmlspecialchars($jurnal->nim); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-primary badge-pill">Ke-<?php echo $jurnal->pertemuan_ke; ?></span>
                                </td>
                                <td>
                                    <span class="text-sm"><?php echo date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)); ?></span>
                                    <br>
                                    <small class="text-muted"><?php echo $days_pending; ?> hari yang lalu</small>
                                    <?php if($days_pending > 3): ?>
                                    <br><small class="text-danger"><i class="fa fa-exclamation-triangle"></i> Urgent</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-sm"><?php echo htmlspecialchars(substr($jurnal->materi_bimbingan, 0, 50)); ?><?php echo strlen($jurnal->materi_bimbingan) > 50 ? '...' : ''; ?></span>
                                    <?php if(isset($jurnal->tindak_lanjut) && !empty($jurnal->tindak_lanjut)): ?>
                                    <br><small class="text-info"><strong>TL:</strong> <?php echo htmlspecialchars(substr($jurnal->tindak_lanjut, 0, 30)); ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-success mr-1" onclick="quickValidasi(<?php echo $jurnal->id; ?>, 1)" title="Validasi Jurnal">
                                        <i class="fa fa-check"></i> Validasi
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="quickValidasi(<?php echo $jurnal->id; ?>, 2)" title="Minta Revisi">
                                        <i class="fa fa-edit"></i> Revisi
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-info shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Kelola Bimbingan Mahasiswa - Phase 2</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Workflow Phase 2:</strong> Kelola jurnal bimbingan dan pantau progress pengembangan proposal mahasiswa. 
                            Validasi setiap pertemuan bimbingan untuk memastikan kualitas penelitian.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fa fa-comments"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards dengan Data Real -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php echo isset($total_mahasiswa) ? $total_mahasiswa : '0'; ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i></span>
                    <span class="text-nowrap">Mahasiswa bimbingan aktif</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Jurnal Pending</h5>
                        <span class="h2 font-weight-bold mb-0 text-warning">
                            <?php echo isset($total_jurnal_pending) ? $total_jurnal_pending : '0'; ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-exclamation-triangle"></i></span>
                    <span class="text-nowrap">Perlu validasi segera</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Tervalidasi</h5>
                        <span class="h2 font-weight-bold mb-0 text-success">
                            <?php echo isset($total_jurnal_tervalidasi) ? $total_jurnal_tervalidasi : '0'; ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-check"></i></span>
                    <span class="text-nowrap">Jurnal sudah divalidasi</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Siap Seminar</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $siap_seminar = 0;
                            if(isset($mahasiswa_bimbingan) && !empty($mahasiswa_bimbingan)) {
                                foreach($mahasiswa_bimbingan as $mhs) {
                                    if(isset($mhs->jurnal_tervalidasi) && (int)$mhs->jurnal_tervalidasi >= 8) {
                                        $siap_seminar++;
                                    }
                                }
                            }
                            echo $siap_seminar;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-trophy"></i></span>
                    <span class="text-nowrap">Mencapai 8+ bimbingan</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Mahasiswa Bimbingan -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Mahasiswa Bimbingan</h3>
                        <p class="mb-0 text-sm">Daftar mahasiswa yang sedang Anda bimbing dalam fase pengembangan proposal</p>
                    </div>
                    <div class="col text-right">
                        <button type="button" class="btn btn-sm btn-primary" onclick="showTambahJurnalModal()">
                            <i class="fa fa-plus"></i> Tambah Jurnal Bimbingan
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Proposal</th>
                            <th scope="col">Progress Bimbingan</th>
                            <th scope="col">Jurnal Status</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($mahasiswa_bimbingan) && !empty($mahasiswa_bimbingan)): ?>
                            <?php foreach($mahasiswa_bimbingan as $mahasiswa): ?>
                            <?php
                            $total_bimbingan = isset($mahasiswa->total_bimbingan) ? (int)$mahasiswa->total_bimbingan : 0;
                            $jurnal_tervalidasi = isset($mahasiswa->jurnal_tervalidasi) ? (int)$mahasiswa->jurnal_tervalidasi : 0;
                            $jurnal_pending = isset($mahasiswa->jurnal_pending) ? (int)$mahasiswa->jurnal_pending : 0;
                            $jurnal_revisi = isset($mahasiswa->jurnal_revisi) ? (int)$mahasiswa->jurnal_revisi : 0;
                            ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?php echo isset($mahasiswa->nama_mahasiswa) ? htmlspecialchars($mahasiswa->nama_mahasiswa) : 'N/A'; ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?php echo isset($mahasiswa->nim) ? htmlspecialchars($mahasiswa->nim) : 'N/A'; ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo isset($mahasiswa->nama_prodi) ? htmlspecialchars($mahasiswa->nama_prodi) : 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(isset($mahasiswa->judul) && !empty($mahasiswa->judul)): ?>
                                        <span class="text-sm font-weight-bold" title="<?php echo htmlspecialchars($mahasiswa->judul); ?>">
                                            <?php echo htmlspecialchars(substr($mahasiswa->judul, 0, 50)) . (strlen($mahasiswa->judul) > 50 ? '...' : ''); ?>
                                        </span>
                                        <br>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($mahasiswa->lokasi_penelitian) && !empty($mahasiswa->lokasi_penelitian)): ?>
                                    <small class="text-muted">
                                        <i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($mahasiswa->lokasi_penelitian); ?>
                                    </small>
                                    <br>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($mahasiswa->jenis_penelitian) && !empty($mahasiswa->jenis_penelitian)): ?>
                                    <span class="badge badge-pill badge-secondary"><?php echo htmlspecialchars($mahasiswa->jenis_penelitian); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $progress_persen = $total_bimbingan > 0 ? min(($total_bimbingan / 16) * 100, 100) : 0; ?>
                                    <div class="d-flex align-items-center">
                                        <span class="mr-2"><?php echo $total_bimbingan; ?>/16</span>
                                        <div class="progress" style="width: 100px;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: <?php echo $progress_persen; ?>%" 
                                                 aria-valuenow="<?php echo $progress_persen; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"
                                                 title="<?php echo round($progress_persen, 1); ?>% dari target"></div>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Target: 16 pertemuan minimal
                                    </small>
                                </td>
                                <td>
                                    <!-- Tampilkan detail status jurnal -->
                                    <div class="text-sm">
                                        <?php if($jurnal_tervalidasi > 0): ?>
                                        <span class="badge badge-success badge-sm mr-1" title="<?php echo $jurnal_tervalidasi; ?> jurnal tervalidasi">
                                            <i class="fa fa-check"></i> <?php echo $jurnal_tervalidasi; ?>
                                        </span>
                                        <?php endif; ?>
                                        
                                        <?php if($jurnal_pending > 0): ?>
                                        <span class="badge badge-warning badge-sm mr-1" title="<?php echo $jurnal_pending; ?> jurnal pending">
                                            <i class="fa fa-clock"></i> <?php echo $jurnal_pending; ?>
                                        </span>
                                        <?php endif; ?>
                                        
                                        <?php if($jurnal_revisi > 0): ?>
                                        <span class="badge badge-danger badge-sm" title="<?php echo $jurnal_revisi; ?> jurnal perlu revisi">
                                            <i class="fa fa-edit"></i> <?php echo $jurnal_revisi; ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        Validasi | Pending | Revisi
                                    </small>
                                </td>
                                <td>
                                    <?php if($jurnal_tervalidasi >= 16): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Lengkap
                                        </span>
                                    <?php elseif($jurnal_tervalidasi >= 8): ?>
                                        <span class="badge badge-info">
                                            <i class="fa fa-trophy"></i> Siap Seminar
                                        </span>
                                    <?php elseif($total_bimbingan > 0): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-sync"></i> Bimbingan Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            <i class="fa fa-play"></i> Belum Mulai
                                        </span>
                                    <?php endif; ?>
                                    
                                    <!-- Alert untuk jurnal pending -->
                                    <?php if($jurnal_pending > 0): ?>
                                    <br>
                                    <small class="text-warning">
                                        <i class="fa fa-exclamation-triangle"></i> <?php echo $jurnal_pending; ?> perlu validasi
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <?php if(isset($mahasiswa->proposal_id)): ?>
                                            <a class="dropdown-item" href="<?php echo base_url('dosen/bimbingan/detail_mahasiswa/' . $mahasiswa->proposal_id); ?>">
                                                <i class="fa fa-eye"></i> Detail Bimbingan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="tambahJurnalMahasiswa(<?php echo $mahasiswa->proposal_id; ?>, '<?php echo addslashes(htmlspecialchars($mahasiswa->nama_mahasiswa)); ?>')">
                                                <i class="fa fa-plus"></i> Tambah Jurnal
                                            </a>
                                            <?php if($total_bimbingan > 0): ?>
                                            <a class="dropdown-item" href="<?php echo base_url('dosen/bimbingan/export_jurnal/' . $mahasiswa->proposal_id); ?>">
                                                <i class="fa fa-download"></i> Export PDF
                                            </a>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-center">
                                        <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada mahasiswa bimbingan</h5>
                                        <p class="text-muted">Mahasiswa yang Anda setujui sebagai pembimbing akan muncul di sini.</p>
                                        <a href="<?php echo base_url('dosen/usulan_proposal'); ?>" class="btn btn-primary">
                                            <i class="fa fa-eye"></i> Cek Usulan Proposal
                                        </a>
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

<!-- INFO CARD DIKURANGI dan DIPINDAH SEBELUM MODAL -->
<div class="row mt-4 last-card">
    <div class="col-lg-12">
        <div class="card bg-gradient-light shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="icon icon-shape icon-lg bg-gradient-info text-white rounded-circle shadow">
                            <i class="fa fa-lightbulb"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1">Tips Bimbingan Efektif</h5>
                        <p class="mb-0 text-sm">
                            <strong>Validasi Berkala:</strong> Periksa jurnal bimbingan yang pending secara rutin untuk menjaga momentum mahasiswa. 
                            <strong>Feedback Konstruktif:</strong> Berikan catatan yang spesifik dan actionable dalam setiap validasi. 
                            <strong>Progress Monitoring:</strong> Pastikan minimal 8 pertemuan tervalidasi sebelum mahasiswa mengajukan seminar proposal.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Modal Tambah Jurnal Bimbingan -->
<div class="modal fade" id="modalTambahJurnal" tabindex="-1" role="dialog" aria-labelledby="modalTambahJurnalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?php echo base_url('dosen/bimbingan/tambah_jurnal'); ?>" method="POST" id="formTambahJurnal">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahJurnalLabel">
                        <i class="fa fa-plus-circle mr-2"></i>Tambah Jurnal Bimbingan
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="select_mahasiswa">Pilih Mahasiswa *</label>
                        <select class="form-control" name="proposal_id" id="select_mahasiswa" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            <?php if(isset($mahasiswa_bimbingan) && !empty($mahasiswa_bimbingan)): ?>
                                <?php foreach($mahasiswa_bimbingan as $mhs): ?>
                                <?php if(isset($mhs->proposal_id) && isset($mhs->nama_mahasiswa) && isset($mhs->nim)): ?>
                                <option value="<?php echo $mhs->proposal_id; ?>">
                                    <?php echo htmlspecialchars($mhs->nama_mahasiswa . ' (' . $mhs->nim . ')'); ?>
                                </option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_bimbingan">Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" id="tanggal_bimbingan" required max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pertemuan_ke">Pertemuan ke-</label>
                                <input type="number" class="form-control" name="pertemuan_ke" id="pertemuan_ke" min="1" placeholder="Auto generate jika kosong">
                                <small class="form-text text-muted">Akan otomatis generate jika dikosongkan</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="materi_bimbingan">Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" id="materi_bimbingan" rows="3" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini (contoh: Review Bab 1, Diskusi metodologi penelitian, dsb)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="catatan_dosen">Catatan Dosen</label>
                        <textarea class="form-control" name="catatan_dosen" id="catatan_dosen" rows="3" 
                                  placeholder="Catatan, evaluasi, atau saran untuk mahasiswa"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="tindak_lanjut">Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" id="tindak_lanjut" rows="2" 
                                  placeholder="Tugas atau tindak lanjut untuk pertemuan berikutnya"></textarea>
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

<!-- Modal Quick Validasi -->
<div class="modal fade" id="modalQuickValidasi" tabindex="-1" role="dialog" aria-labelledby="modalQuickValidasiLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalQuickValidasiLabel">Validasi Cepat Jurnal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="quickValidasiInfo" class="alert"></div>
                <div class="form-group">
                    <label for="quickCatatanDosen">Catatan untuk Mahasiswa</label>
                    <textarea class="form-control" id="quickCatatanDosen" rows="3" 
                              placeholder="Berikan catatan atau feedback (wajib untuk revisi, opsional untuk validasi)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="quickValidasiSubmit">Proses</button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript untuk handling modal dan quick validasi
var currentJurnalId = null;
var currentJurnalAction = null;

function showTambahJurnalModal() {
    $('#modalTambahJurnal').modal('show');
}

function tambahJurnalMahasiswa(proposalId, namaMahasiswa) {
    const selectElement = document.getElementById('select_mahasiswa');
    if (selectElement) {
        selectElement.value = proposalId;
        selectElement.disabled = true;
        $('#modalTambahJurnal').modal('show');
    }
}

// Function untuk quick validasi jurnal
function quickValidasi(jurnalId, action) {
    currentJurnalId = jurnalId;
    currentJurnalAction = action;
    
    const infoDiv = document.getElementById('quickValidasiInfo');
    const submitBtn = document.getElementById('quickValidasiSubmit');
    
    if (!infoDiv || !submitBtn) {
        alert('Error: Modal elements not found');
        return;
    }
    
    if (action == 1) {
        infoDiv.className = 'alert alert-success';
        infoDiv.innerHTML = '<i class="fa fa-check"></i> <strong>Validasi Jurnal</strong><br>Jurnal akan ditandai sebagai tervalidasi dan mahasiswa dapat melanjutkan.';
        submitBtn.textContent = 'Validasi Jurnal';
        submitBtn.className = 'btn btn-success';
    } else {
        infoDiv.className = 'alert alert-warning';
        infoDiv.innerHTML = '<i class="fa fa-edit"></i> <strong>Minta Revisi</strong><br>Jurnal akan dikembalikan untuk diperbaiki mahasiswa. <strong>Catatan wajib diisi.</strong>';
        submitBtn.textContent = 'Minta Revisi';
        submitBtn.className = 'btn btn-warning';
    }
    
    document.getElementById('quickCatatanDosen').value = '';
    $('#modalQuickValidasi').modal('show');
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Set default date to today
    const dateInput = document.getElementById('tanggal_bimbingan');
    if (dateInput) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Handle submit quick validasi
    const quickSubmitBtn = document.getElementById('quickValidasiSubmit');
    if (quickSubmitBtn) {
        quickSubmitBtn.addEventListener('click', function() {
            if (!currentJurnalId || !currentJurnalAction) {
                alert('Data tidak lengkap!');
                return;
            }
            
            const catatan = document.getElementById('quickCatatanDosen').value.trim();
            
            if (currentJurnalAction == 2 && catatan.length < 5) {
                alert('Catatan wajib diisi untuk revisi (minimal 5 karakter)!');
                document.getElementById('quickCatatanDosen').focus();
                return;
            }
            
            // Disable button dan show loading
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            
            // Kirim request via AJAX
            const formData = new FormData();
            formData.append('jurnal_id', currentJurnalId);
            formData.append('status_validasi', currentJurnalAction);
            formData.append('catatan_dosen', catatan);
            
            fetch('<?php echo base_url("dosen/bimbingan/quick_validasi"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.message);
                } else {
                    alert('Success: ' + data.message);
                    // Reload halaman untuk update data
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem! Silakan coba lagi.');
            })
            .finally(() => {
                // Restore button
                this.disabled = false;
                this.innerHTML = originalText;
                $('#modalQuickValidasi').modal('hide');
            });
        });
    }
});

// Reset modal saat ditutup
$('#modalTambahJurnal').on('hidden.bs.modal', function () {
    const selectElement = document.getElementById('select_mahasiswa');
    if (selectElement) {
        selectElement.disabled = false;
        selectElement.value = '';
    }
    
    // Reset form
    document.getElementById('formTambahJurnal').reset();
    
    // Set default date again
    const dateInput = document.getElementById('tanggal_bimbingan');
    if (dateInput) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
});

// Reset quick validasi modal
$('#modalQuickValidasi').on('hidden.bs.modal', function () {
    currentJurnalId = null;
    currentJurnalAction = null;
    document.getElementById('quickCatatanDosen').value = '';
});
</script>