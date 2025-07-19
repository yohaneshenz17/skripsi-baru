<?php
// File: application/views/kaprodi/dashboard.php
// IMPROVED VERSION - Mirip dengan styling Dashboard Dosen
?>

<!-- Alert Messages (if any) -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Welcome Message - Style yang sama dengan Dosen -->
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">👋 Selamat Datang, <?= $this->session->userdata('nama') ?>!</h3>
                        <p class="text-white mt-2 mb-0 opacity-8">
                            Kelola semua tahapan tugas akhir mahasiswa dengan mudah melalui dashboard ini.
                        </p>
                        <p class="text-white-50 mt-1 mb-0 small">
                            <strong>Workflow:</strong> 
                            <strong>1. Usulan Proposal</strong> → <strong>2. Seminar Proposal</strong> → 
                            <strong>3. Penelitian</strong> → <strong>4. Seminar Skripsi</strong> → 
                            <strong>5. Publikasi</strong> → <strong>6. Selesai</strong>
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="ni ni-hat-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards - Style yang sama dengan Dosen -->
<div class="row">
    <!-- Card 1: Proposal Masuk -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Proposal Masuk</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($total_proposal_masuk) ? $total_proposal_masuk : 2 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
                            <i class="ni ni-paper-diploma"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Belum ditetapkan</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Mahasiswa -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($total_mahasiswa) ? $total_mahasiswa : 2 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-orange text-white rounded-circle shadow">
                            <i class="ni ni-single-02"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fa fa-check"></i></span>
                    <span class="text-nowrap">Mahasiswa aktif prodi</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Dosen -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Dosen</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= isset($total_dosen) ? $total_dosen : 17 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-green text-white rounded-circle shadow">
                            <i class="ni ni-hat-3"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fa fa-users"></i></span>
                    <span class="text-nowrap">Dosen semua prodi</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Card 4: Pengumuman Aktif -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Pengumuman Aktif</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php
                            // Cek apakah tabel pengumuman_tahapan ada
                            $pengumuman_count = 0;
                            if ($this->db->table_exists('pengumuman_tahapan')) {
                                $pengumuman_count = $this->db->get_where('pengumuman_tahapan', ['aktif' => '1'])->num_rows();
                            }
                            echo $pengumuman_count;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-gradient-yellow text-white rounded-circle shadow">
                            <i class="ni ni-bell-55"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fa fa-exclamation-triangle"></i></span>
                    <span class="text-nowrap">Perlu review</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Pengumuman Tahapan Aktif -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">📋 Pengumuman Tahapan Aktif</h6>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url('kaprodi/pengumuman') ?>" class="btn btn-sm btn-primary">Kelola</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Tahapan</th>
                                <th scope="col">Deadline</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Pengajuan Proposal</td>
                                <td><span class="badge badge-dot mr-4">
                                    <i class="bg-success"></i>06/08/2025
                                </span></td>
                                <td><span class="badge badge-success">Aktif</span></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Seminar Proposal</td>
                                <td><span class="badge badge-dot mr-4">
                                    <i class="bg-warning"></i>31/10/2025
                                </span></td>
                                <td><span class="badge badge-warning">Menunggu</span></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Ujian Skripsi</td>
                                <td><span class="badge badge-dot mr-4">
                                    <i class="bg-info"></i>25/05/2026
                                </span></td>
                                <td><span class="badge badge-secondary">Draft</span></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Revisi dan Publikasi</td>
                                <td><span class="badge badge-dot mr-4">
                                    <i class="bg-primary"></i>30/07/2026
                                </span></td>
                                <td><span class="badge badge-secondary">Draft</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions - Mirip dengan Dosen -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="h3 mb-0">⚡ Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= base_url('kaprodi/proposal') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar avatar-sm rounded-circle" style="background-color: #ff6b6b;">
                                    <i class="ni ni-paper-diploma text-white"></i>
                                </div>
                            </div>
                            <div class="col ml--2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-sm">Usulan Proposal</h4>
                                        <small class="text-muted">Review proposal mahasiswa</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="<?= base_url('kaprodi/seminar_proposal') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar avatar-sm rounded-circle" style="background-color: #4ecdc4;">
                                    <i class="ni ni-books text-white"></i>
                                </div>
                            </div>
                            <div class="col ml--2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-sm">Seminar Proposal</h4>
                                        <small class="text-muted">Kelola jadwal seminar</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="<?= base_url('kaprodi/seminar_skripsi') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar avatar-sm rounded-circle" style="background-color: #45b7d1;">
                                    <i class="ni ni-hat-3 text-white"></i>
                                </div>
                            </div>
                            <div class="col ml--2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-sm">Seminar Skripsi</h4>
                                        <small class="text-muted">Validasi seminar akhir</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="<?= base_url('kaprodi/publikasi') ?>" class="list-group-item list-group-item-action">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="avatar avatar-sm rounded-circle" style="background-color: #f7b731;">
                                    <i class="ni ni-trophy text-white"></i>
                                </div>
                            </div>
                            <div class="col ml--2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0 text-sm">Publikasi</h4>
                                        <small class="text-muted">Validasi publikasi akhir</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Proposal Menunggu Review -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">📝 Proposal Menunggu Review</h6>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url('kaprodi/proposal') ?>" class="btn btn-sm btn-primary">Lihat semua</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">NIM</th>
                                <th scope="col">Nama Mahasiswa</th>
                                <th scope="col">Judul Proposal</th>
                                <th scope="col">Tanggal Pengajuan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>273637373</td>
                                <td>Herybertus Oktaviani</td>
                                <td class="text-wrap">Sistem Informasi Pembelajaran Berbasis Web...</td>
                                <td>15 Juli 2025</td>
                                <td>
                                    <a href="<?= base_url('kaprodi/review_proposal/1') ?>" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                            <!-- Tambahkan data lainnya sesuai kebutuhan -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Development Tools (hanya dalam development mode) -->
<?php if (ENVIRONMENT === 'development'): ?>
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card border-warning">
            <div class="card-header bg-warning">
                <h5 class="mb-0 text-white">🔧 Development Tools - Database Cleanup</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>Perhatian:</strong> Tools ini hanya tersedia dalam mode development untuk membersihkan data lama.
                </div>
                
                <div class="btn-group mb-3" role="group">
                    <a href="<?= base_url() ?>kaprodi/dashboard/cleanup_old_proposals" 
                       class="btn btn-danger"
                       onclick="return confirm('Apakah Anda yakin ingin menghapus proposal lama (ID 34 dan 35)? Data akan dibackup terlebih dahulu.')">
                        <i class="fa fa-trash"></i> Bersihkan Data Proposal Lama
                    </a>
                    
                    <a href="<?= base_url() ?>kaprodi/dashboard/debug_data" 
                       class="btn btn-secondary"
                       target="_blank">
                        <i class="fa fa-bug"></i> Debug Data
                    </a>
                </div>
                
                <small class="text-muted d-block">
                    • <strong>Bersihkan Data:</strong> Akan menghapus proposal ID 34 dan 35 (data lama/tidak valid)<br>
                    • <strong>Debug Data:</strong> Melihat detail data proposal saat ini
                </small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Auto-refresh Script -->
<script>
$(document).ready(function() {
    // Auto refresh dashboard setiap 5 menit untuk update data terbaru
    setInterval(function() {
        // Optional: AJAX call untuk refresh data statistik
        console.log('Dashboard auto-refresh...');
    }, 300000);
    
    // Initialize tooltips dan popovers untuk interaksi yang lebih baik
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
    
    // Smooth scroll untuk navigasi internal
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
});
</script>