<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- CSS untuk spacing yang optimal -->
<style>
.content-spacing {
    margin-bottom: 40px; /* Reduced spacing */
}

.card {
    margin-bottom: 1rem; /* Reduced from 1.5rem */
}

.card-stats {
    margin-bottom: 1rem; /* Consistent spacing */
}

.row {
    margin-bottom: 1.5rem; /* Consistent row spacing */
}

.table-responsive {
    margin-bottom: 1rem; /* Reduced margin after tables */
}

.info-section {
    margin-bottom: 30px !important; /* Optimal final section margin */
}

/* Remove excessive padding */
.main-content {
    padding-bottom: 60px; /* Reasonable bottom padding */
}

/* Mobile responsive */
@media (max-width: 768px) {
    .content-spacing { margin-bottom: 30px; }
    .info-section { margin-bottom: 20px !important; }
    .main-content { padding-bottom: 40px; }
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

<!-- Info Panel -->
<div class="row">
    <div class="col-lg-12 mb-3">
        <div class="card bg-gradient-danger">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Seminar Skripsi - Phase 5</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Workflow Phase 5:</strong> Ujian akhir skripsi mahasiswa. Sebagai pembimbing atau penguji, 
                            berikan rekomendasi dan penilaian untuk penyelesaian studi mahasiswa.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-danger rounded-circle shadow">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Sebagai Pembimbing</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            if(isset($seminar_skripsi) && !empty($seminar_skripsi)) {
                                echo count($seminar_skripsi);
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                            <i class="fa fa-user-tie"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-danger mr-2"><i class="fa fa-user"></i></span>
                    <span class="text-nowrap">Ujian skripsi</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Sebagai Penguji</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            if(isset($jadwal_sebagai_penguji) && !empty($jadwal_sebagai_penguji)) {
                                echo count($jadwal_sebagai_penguji);
                            } else {
                                echo '0';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-gavel"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-user-check"></i></span>
                    <span class="text-nowrap">Jadwal menguji</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Perlu Rekomendasi</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $pending = 0;
                            if(isset($seminar_skripsi) && !empty($seminar_skripsi)) {
                                foreach($seminar_skripsi as $ss) {
                                    if(!isset($ss->rekomendasi_pembimbing) || is_null($ss->rekomendasi_pembimbing)) $pending++;
                                }
                            }
                            echo $pending;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-clock"></i></span>
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
                        <h5 class="card-title text-uppercase text-muted mb-0">Siap Lulus</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $lulus = 0;
                            if(isset($seminar_skripsi) && !empty($seminar_skripsi)) {
                                foreach($seminar_skripsi as $ss) {
                                    if(isset($ss->rekomendasi_pembimbing) && $ss->rekomendasi_pembimbing == '1' && 
                                       isset($ss->nilai_pembimbing) && !is_null($ss->nilai_pembimbing)) $lulus++;
                                }
                            }
                            echo $lulus;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-trophy"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-check"></i></span>
                    <span class="text-nowrap">Sudah dinilai</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Pengajuan Seminar Skripsi - Sebagai Pembimbing -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Pengajuan Seminar Skripsi - Sebagai Pembimbing</h3>
                        <p class="mb-0 text-sm">Mahasiswa bimbingan yang mengajukan ujian akhir skripsi</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul Skripsi</th>
                            <th scope="col">Tanggal Pengajuan</th>
                            <th scope="col">Jadwal Ujian</th>
                            <th scope="col">Status Rekomendasi</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($seminar_skripsi) && !empty($seminar_skripsi)): ?>
                            <?php foreach($seminar_skripsi as $ss): ?>
                            <tr>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?php echo isset($ss->nama_mahasiswa) ? $ss->nama_mahasiswa : 'N/A'; ?></span>
                                            <br>
                                            <small class="text-muted">NIM: <?php echo isset($ss->nim) ? $ss->nim : 'N/A'; ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo isset($ss->nama_prodi) ? $ss->nama_prodi : 'N/A'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(isset($ss->judul)): ?>
                                        <span class="text-sm font-weight-bold"><?php echo substr($ss->judul, 0, 40) . '...'; ?></span>
                                        <br>
                                    <?php endif; ?>
                                    
                                    <small class="text-muted">
                                        <i class="fa fa-calendar"></i> Pengajuan: <?php echo isset($ss->created_at) ? date('d/m/Y', strtotime($ss->created_at)) : 'N/A'; ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="text-sm"><?php echo isset($ss->created_at) ? date('d/m/Y', strtotime($ss->created_at)) : 'N/A'; ?></span>
                                </td>
                                <td>
                                    <?php if(isset($ss->tanggal_seminar) && !empty($ss->tanggal_seminar)): ?>
                                        <span class="badge badge-danger">
                                            <?php echo date('d/m/Y H:i', strtotime($ss->tanggal_seminar)); ?>
                                        </span>
                                        <br>
                                        <small class="text-muted"><?php echo isset($ss->tempat_seminar) ? $ss->tempat_seminar : 'TBA'; ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum Dijadwalkan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($ss->rekomendasi_pembimbing) && $ss->rekomendasi_pembimbing == '1'): ?>
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Direkomendasikan
                                        </span>
                                        <?php if(isset($ss->nilai_pembimbing) && !is_null($ss->nilai_pembimbing)): ?>
                                        <br>
                                        <small class="text-success">Nilai: <?php echo $ss->nilai_pembimbing; ?></small>
                                        <?php endif; ?>
                                    <?php elseif(isset($ss->rekomendasi_pembimbing) && $ss->rekomendasi_pembimbing == '2'): ?>
                                        <span class="badge badge-warning">
                                            <i class="fa fa-edit"></i> Perlu Perbaikan
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-info">
                                            <i class="fa fa-clock"></i> Menunggu Rekomendasi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <?php if(isset($ss->id)): ?>
                                            <a class="dropdown-item" href="<?php echo base_url('dosen/seminar_skripsi/detail/' . $ss->id); ?>">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                            <?php if(!isset($ss->rekomendasi_pembimbing) || is_null($ss->rekomendasi_pembimbing)): ?>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?php echo $ss->id; ?>, 1)">
                                                <i class="fa fa-check text-success"></i> Rekomendasikan
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="beriRekomendasi(<?php echo $ss->id; ?>, 2)">
                                                <i class="fa fa-edit text-warning"></i> Minta Perbaikan
                                            </a>
                                            <?php endif; ?>
                                            <?php if(isset($ss->tanggal_seminar) && !empty($ss->tanggal_seminar)): ?>
                                            <a class="dropdown-item" href="<?php echo base_url('dosen/seminar_skripsi/berita_acara/' . $ss->id); ?>">
                                                <i class="fa fa-file-alt"></i> Berita Acara
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
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-center">
                                        <i class="fa fa-graduation-cap fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">Belum ada pengajuan seminar skripsi</h5>
                                        <p class="text-muted">Pengajuan ujian akhir dari mahasiswa bimbingan akan muncul di sini.</p>
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

<!-- Jadwal Ujian Skripsi - Sebagai Penguji (jika ada) -->
<?php if(isset($jadwal_sebagai_penguji) && !empty($jadwal_sebagai_penguji)): ?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Jadwal Ujian Skripsi - Sebagai Penguji</h3>
                        <p class="mb-0 text-sm">Ujian skripsi yang Anda bertugas sebagai penguji</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Pembimbing</th>
                            <th scope="col">Jadwal Ujian</th>
                            <th scope="col">Status Penilaian</th>
                            <th scope="col">Nilai</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($jadwal_sebagai_penguji as $jadwal): ?>
                        <tr>
                            <td>
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <span class="mb-0 text-sm font-weight-bold"><?php echo isset($jadwal->nama_mahasiswa) ? $jadwal->nama_mahasiswa : 'N/A'; ?></span>
                                        <br>
                                        <small class="text-muted">NIM: <?php echo isset($jadwal->nim) ? $jadwal->nim : 'N/A'; ?></small>
                                        <br>
                                        <?php if(isset($jadwal->judul)): ?>
                                        <small class="text-info"><?php echo substr($jadwal->judul, 0, 30) . '...'; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm"><?php echo isset($jadwal->nama_pembimbing) ? $jadwal->nama_pembimbing : 'N/A'; ?></span>
                            </td>
                            <td>
                                <?php if(isset($jadwal->tanggal_seminar)): ?>
                                <span class="badge badge-danger">
                                    <?php echo date('d/m/Y H:i', strtotime($jadwal->tanggal_seminar)); ?>
                                </span>
                                <br>
                                <small class="text-muted"><?php echo isset($jadwal->tempat_seminar) ? $jadwal->tempat_seminar : 'TBA'; ?></small>
                                <?php else: ?>
                                <span class="badge badge-secondary">Belum Dijadwalkan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $sudah_dinilai = false;
                                $nilai_saya = null;
                                if(isset($jadwal->dosen_penguji_1) && $jadwal->dosen_penguji_1 == $this->session->userdata('id') && 
                                   isset($jadwal->nilai_penguji_1) && !is_null($jadwal->nilai_penguji_1)) {
                                    $sudah_dinilai = true;
                                    $nilai_saya = $jadwal->nilai_penguji_1;
                                } elseif(isset($jadwal->dosen_penguji_2) && $jadwal->dosen_penguji_2 == $this->session->userdata('id') && 
                                         isset($jadwal->nilai_penguji_2) && !is_null($jadwal->nilai_penguji_2)) {
                                    $sudah_dinilai = true;
                                    $nilai_saya = $jadwal->nilai_penguji_2;
                                }
                                ?>
                                
                                <?php if($sudah_dinilai): ?>
                                    <span class="badge badge-success">
                                        <i class="fa fa-check"></i> Sudah Dinilai
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">
                                        <i class="fa fa-clock"></i> Belum Dinilai
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($sudah_dinilai): ?>
                                    <span class="badge badge-primary"><?php echo $nilai_saya; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                                
                                <!-- Nilai rata-rata jika semua sudah menilai -->
                                <?php 
                                $total_nilai = 0;
                                $jumlah_penilai = 0;
                                
                                if(isset($jadwal->nilai_pembimbing) && !is_null($jadwal->nilai_pembimbing)) {
                                    $total_nilai += $jadwal->nilai_pembimbing;
                                    $jumlah_penilai++;
                                }
                                if(isset($jadwal->nilai_penguji_1) && !is_null($jadwal->nilai_penguji_1)) {
                                    $total_nilai += $jadwal->nilai_penguji_1;
                                    $jumlah_penilai++;
                                }
                                if(isset($jadwal->nilai_penguji_2) && !is_null($jadwal->nilai_penguji_2)) {
                                    $total_nilai += $jadwal->nilai_penguji_2;
                                    $jumlah_penilai++;
                                }
                                
                                if($jumlah_penilai == 3):
                                    $rata_rata = round($total_nilai / $jumlah_penilai, 2);
                                ?>
                                <br>
                                <small class="text-success">
                                    <strong>Rata-rata: <?php echo $rata_rata; ?></strong>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                        <?php if(isset($jadwal->id)): ?>
                                        <a class="dropdown-item" href="<?php echo base_url('dosen/seminar_skripsi/detail/' . $jadwal->id); ?>">
                                            <i class="fa fa-eye"></i> Detail
                                        </a>
                                        <?php if(!$sudah_dinilai): ?>
                                        <a class="dropdown-item" href="#" onclick="inputNilai(<?php echo $jadwal->id; ?>)">
                                            <i class="fa fa-edit"></i> Input Nilai
                                        </a>
                                        <?php endif; ?>
                                        <a class="dropdown-item" href="<?php echo base_url('dosen/seminar_skripsi/berita_acara/' . $jadwal->id); ?>">
                                            <i class="fa fa-file-alt"></i> Berita Acara
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
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

<!-- Info Card - Tips Section -->
<div class="row info-section">
    <div class="col-lg-12">
        <div class="card bg-gradient-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="icon icon-shape icon-lg bg-gradient-danger text-white rounded-circle shadow">
                            <i class="fa fa-info"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-1">Tahap Ujian Akhir Skripsi</h5>
                        <p class="mb-0 text-sm">
                            Setelah mahasiswa menyelesaikan penelitian dan menyusun skripsi lengkap, mereka dapat mengajukan ujian akhir. 
                            Sebagai pembimbing atau penguji, Anda berperan penting dalam menilai kelayakan kelulusan mahasiswa dan 
                            memberikan penilaian yang objektif.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Modal Rekomendasi -->
<div class="modal fade" id="modalRekomendasi" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?php echo base_url('dosen/seminar_skripsi/rekomendasi'); ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Berikan Rekomendasi Ujian Skripsi</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="modal_seminar_id">
                    <input type="hidden" name="rekomendasi" id="modal_rekomendasi">
                    
                    <div class="form-group">
                        <label>Status Rekomendasi</label>
                        <div id="modal_status_text" class="alert"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Pembimbing *</label>
                        <textarea class="form-control" name="catatan_pembimbing" rows="4" required 
                                  placeholder="Berikan catatan, evaluasi, atau perbaikan yang diperlukan untuk ujian skripsi"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="fa fa-info-circle"></i> 
                            <strong>Catatan:</strong> Rekomendasi ini akan menentukan apakah mahasiswa dapat melaksanakan ujian akhir skripsi.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="modal_submit_btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Berikan Rekomendasi
function beriRekomendasi(seminarId, status) {
    if (document.getElementById('modal_seminar_id')) {
        document.getElementById('modal_seminar_id').value = seminarId;
        document.getElementById('modal_rekomendasi').value = status;
        
        const statusText = document.getElementById('modal_status_text');
        const submitBtn = document.getElementById('modal_submit_btn');
        
        if (status == 1) {
            statusText.className = 'alert alert-success';
            statusText.innerHTML = '<i class="fa fa-check"></i> <strong>Direkomendasikan</strong> - Mahasiswa siap menjalani ujian akhir skripsi';
            submitBtn.className = 'btn btn-success';
            submitBtn.textContent = 'Rekomendasikan';
        } else {
            statusText.className = 'alert alert-warning';
            statusText.innerHTML = '<i class="fa fa-edit"></i> <strong>Perlu Perbaikan</strong> - Skripsi perlu diperbaiki sebelum ujian';
            submitBtn.className = 'btn btn-warning';
            submitBtn.textContent = 'Minta Perbaikan';
        }
        
        jQuery('#modalRekomendasi').modal('show');
    }
}

// Input Nilai
function inputNilai(seminarId) {
    window.location.href = '<?php echo base_url('dosen/seminar_skripsi/detail/'); ?>' + seminarId + '#input-nilai';
}

// Document ready
jQuery(document).ready(function($) {
    // Modal event handling
    $('#modalRekomendasi').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
</script>