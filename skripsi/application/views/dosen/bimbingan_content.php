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

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0"><?= count($mahasiswa_bimbingan) ?></span>
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
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Siap Seminar</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $siap_seminar = 0;
                            if(!empty($mahasiswa_bimbingan)) {
                                foreach($mahasiswa_bimbingan as $mhs) {
                                    if($mhs->workflow_status == 'bimbingan' && 
                                       (!empty($mhs->total_bimbingan) && $mhs->total_bimbingan >= 16)) {
                                        $siap_seminar++;
                                    }
                                }
                            }
                            echo $siap_seminar;
                            ?>
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
                    <span class="text-nowrap">Mencapai minimal bimbingan</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Perlu Perhatian</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $perlu_perhatian = 0;
                            if(!empty($mahasiswa_bimbingan)) {
                                foreach($mahasiswa_bimbingan as $mhs) {
                                    if($mhs->workflow_status == 'bimbingan' && 
                                       (empty($mhs->total_bimbingan) || $mhs->total_bimbingan < 8)) {
                                        $perlu_perhatian++;
                                    }
                                }
                            }
                            echo $perlu_perhatian;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i></span>
                    <span class="text-nowrap">Bimbingan kurang aktif</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Mahasiswa Bimbingan -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card">
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
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($mahasiswa_bimbingan)): ?>
                            <?php foreach($mahasiswa_bimbingan as $mahasiswa): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?= $mahasiswa->nama_mahasiswa ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?= $mahasiswa->nim ?></small>
                                            <br>
                                            <small class="text-muted"><?= $mahasiswa->nama_prodi ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold"><?= substr($mahasiswa->judul, 0, 50) ?><?= strlen($mahasiswa->judul) > 50 ? '...' : '' ?></span>
                                    <br>
                                    <?php if(!empty($mahasiswa->lokasi_penelitian)): ?>
                                    <small class="text-muted">
                                        <i class="fa fa-map-marker-alt"></i> <?= $mahasiswa->lokasi_penelitian ?>
                                    </small>
                                    <br>
                                    <?php endif; ?>
                                    <?php if(!empty($mahasiswa->jenis_penelitian)): ?>
                                    <span class="badge badge-pill badge-secondary"><?= $mahasiswa->jenis_penelitian ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $total_bimbingan = isset($mahasiswa->total_bimbingan) ? $mahasiswa->total_bimbingan : 0;
                                    $progress_persen = ($total_bimbingan / 16) * 100;
                                    if($progress_persen > 100) $progress_persen = 100;
                                    ?>
                                    <div class="d-flex align-items-center">
                                        <span class="mr-2"><?= $total_bimbingan ?>/16</span>
                                        <div class="progress" style="width: 100px;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: <?= $progress_persen ?>%" 
                                                 aria-valuenow="<?= $progress_persen ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        Target: 16 pertemuan minimal
                                    </small>
                                </td>
                                <td>
                                    <?php if($total_bimbingan >= 16): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Siap Seminar
                                        </span>
                                    <?php elseif($total_bimbingan >= 8): ?>
                                        <span class="badge badge-info">
                                            <i class="fa fa-sync"></i> Bimbingan Aktif
                                        </span>
                                    <?php elseif($total_bimbingan > 0): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-clock"></i> Perlu Intensif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">
                                            <i class="fa fa-play"></i> Belum Mulai
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <a class="dropdown-item" href="<?= base_url('dosen/bimbingan/detail_mahasiswa/' . $mahasiswa->id) ?>">
                                                <i class="fa fa-eye"></i> Detail Bimbingan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="tambahJurnalMahasiswa(<?= $mahasiswa->id ?>, '<?= addslashes($mahasiswa->nama_mahasiswa) ?>')">
                                                <i class="fa fa-plus"></i> Tambah Jurnal
                                            </a>
                                            <?php if($total_bimbingan > 0): ?>
                                            <a class="dropdown-item" href="<?= base_url('dosen/bimbingan/export_jurnal/' . $mahasiswa->id) ?>">
                                                <i class="fa fa-download"></i> Export Jurnal
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
                                        <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada mahasiswa bimbingan</h5>
                                        <p class="text-muted">Mahasiswa yang Anda setujui sebagai pembimbing akan muncul di sini.</p>
                                        <a href="<?= base_url('dosen/usulan_proposal') ?>" class="btn btn-primary">
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

<!-- Info Card -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card bg-gradient-light">
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
                            <strong>Minimal 16 pertemuan:</strong> Pastikan mahasiswa mencatat setiap bimbingan dengan detail. 
                            <strong>Progress bertahap:</strong> Pantau pengembangan Bab 1-3 secara sistematis. 
                            <strong>Dokumentasi:</strong> Validasi jurnal bimbingan secara berkala untuk memastikan kualitas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Jurnal Bimbingan -->
<div class="modal fade" id="modalTambahJurnal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= base_url('dosen/bimbingan/tambah_jurnal') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jurnal Bimbingan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Pilih Mahasiswa *</label>
                        <select class="form-control" name="proposal_id" id="select_mahasiswa" required>
                            <option value="">-- Pilih Mahasiswa --</option>
                            <?php if(!empty($mahasiswa_bimbingan)): ?>
                                <?php foreach($mahasiswa_bimbingan as $mhs): ?>
                                <option value="<?= $mhs->id ?>">
                                    <?= $mhs->nama_mahasiswa ?> (<?= $mhs->nim ?>)
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" required max="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Durasi (menit)</label>
                                <input type="number" class="form-control" name="durasi_bimbingan" min="15" max="180" placeholder="60">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" rows="3" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini (contoh: Review Bab 1, Diskusi metodologi penelitian, dsb)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Mahasiswa</label>
                        <textarea class="form-control" name="catatan_mahasiswa" rows="3" 
                                  placeholder="Catatan atau pertanyaan dari mahasiswa (opsional)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" rows="2" 
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

<script>
// Show Modal Tambah Jurnal
function showTambahJurnalModal() {
    $('#modalTambahJurnal').modal('show');
}

// Tambah Jurnal untuk Mahasiswa Spesifik
function tambahJurnalMahasiswa(proposalId, namaMahasiswa) {
    document.getElementById('select_mahasiswa').value = proposalId;
    document.getElementById('select_mahasiswa').disabled = true;
    $('#modalTambahJurnal').modal('show');
}

// Reset modal saat ditutup
$('#modalTambahJurnal').on('hidden.bs.modal', function () {
    document.getElementById('select_mahasiswa').disabled = false;
    document.getElementById('select_mahasiswa').value = '';
});

// Set default date to today
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="tanggal_bimbingan"]');
    if (dateInput) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>