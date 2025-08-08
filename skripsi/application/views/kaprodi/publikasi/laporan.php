<?php
// Mulai output buffering
ob_start();
?>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">
                            <i class="fa fa-chart-bar mr-2"></i>
                            Laporan Publikasi Tugas Akhir
                        </h3>
                        <p class="text-white-75 mb-0">
                            Statistik dan analisis publikasi mahasiswa periode <?= $periode ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('kaprodi/publikasi') ?>" class="btn btn-neutral">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Export Controls -->
<div class="row mb-4">
    <div class="col">
        <div class="card shadow">
            <div class="card-body">
                <form method="GET" action="<?= base_url('kaprodi/publikasi/laporan') ?>" class="row align-items-end">
                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label class="form-control-label">Periode Tahun</label>
                            <select name="periode" class="form-control">
                                <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                    <option value="<?= $i ?>" <?= ($periode == $i) ? 'selected' : '' ?>>
                                        <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Filter Data
                        </button>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="btn-group" role="group">
                            <a href="<?= base_url('kaprodi/publikasi/laporan?periode=' . $periode . '&export=pdf') ?>" 
                               class="btn btn-success" target="_blank">
                                <i class="fa fa-file-pdf"></i> Export PDF
                            </a>
                            <a href="<?= base_url('kaprodi/publikasi/laporan?periode=' . $periode . '&export=excel') ?>" 
                               class="btn btn-info">
                                <i class="fa fa-file-excel"></i> Export Excel
                            </a>
                            <button type="button" class="btn btn-secondary" onclick="window.print()">
                                <i class="fa fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Mahasiswa</h5>
                        <span class="h2 font-weight-bold mb-0"><?= $summary_prodi['total_mahasiswa_prodi'] ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fa fa-users"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Mahasiswa aktif prodi</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Publikasi <?= $periode ?></h5>
                        <span class="h2 font-weight-bold mb-0"><?= count($laporan_mahasiswa) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fa fa-crown"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Total pengajuan periode ini</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Selesai</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= count(array_filter($laporan_mahasiswa, function($item) { return $item->status == 'completed'; })) ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <?php 
                    $total = count($laporan_mahasiswa);
                    $completed = count(array_filter($laporan_mahasiswa, function($item) { return $item->status == 'completed'; }));
                    $percentage = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                    ?>
                    <span class="text-success mr-2"><?= $percentage ?>%</span>
                    <span class="text-nowrap">dari total pengajuan</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Rata-rata Proses</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?= $summary_prodi['rata_waktu_proses'] ?? 0 ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-default text-white rounded-circle shadow">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-nowrap">Hari proses publikasi</span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Laporan Bulanan -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header">
                <h3 class="mb-0">Laporan Bulanan - <?= $periode ?></h3>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Bulan</th>
                            <th scope="col">Pengajuan</th>
                            <th scope="col">Selesai</th>
                            <th scope="col">Rata-rata Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporan_bulanan)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-calendar fa-2x mb-2"></i><br>
                                        <strong>Belum ada data</strong><br>
                                        <small>Belum ada publikasi untuk periode <?= $periode ?></small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                            $bulan_nama = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                            ?>
                            <?php foreach ($laporan_bulanan as $lap): ?>
                                <tr>
                                    <td>
                                        <span class="font-weight-bold"><?= $bulan_nama[$lap->bulan] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-pill"><?= $lap->total_pengajuan ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success badge-pill"><?= $lap->selesai ?></span>
                                        <?php if ($lap->total_pengajuan > 0): ?>
                                            <small class="text-muted d-block">
                                                (<?= round(($lap->selesai / $lap->total_pengajuan) * 100, 1) ?>%)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($lap->rata_hari_proses): ?>
                                            <span class="text-sm"><?= number_format($lap->rata_hari_proses, 1) ?> hari</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistik Per Dosen -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header">
                <h3 class="mb-0">Statistik Per Dosen Pembimbing</h3>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Dosen Pembimbing</th>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Selesai</th>
                            <th scope="col">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($statistik_dosen)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-user-tie fa-2x mb-2"></i><br>
                                        <strong>Belum ada data</strong><br>
                                        <small>Belum ada data dosen untuk periode ini</small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($statistik_dosen as $stat): ?>
                                <tr>
                                    <td>
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <span class="name text-sm font-weight-bold"><?= $stat->nama_dosen ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-pill"><?= $stat->total_mahasiswa ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success badge-pill"><?= $stat->selesai ?></span>
                                        <?php if ($stat->total_mahasiswa > 0): ?>
                                            <small class="text-muted d-block">
                                                (<?= round(($stat->selesai / $stat->total_mahasiswa) * 100, 1) ?>%)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($stat->rata_hari_proses): ?>
                                            <span class="text-sm"><?= number_format($stat->rata_hari_proses, 1) ?> hari</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detail Laporan Per Mahasiswa -->
<div class="row mt-4">
    <div class="col">
        <div class="card shadow">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Detail Laporan Per Mahasiswa</h3>
                    </div>
                    <div class="col text-right">
                        <small class="text-muted">Periode: <?= $periode ?></small>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="table-laporan-mahasiswa">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Mahasiswa</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Pembimbing</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tanggal Pengajuan</th>
                            <th scope="col">Lama Proses</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporan_mahasiswa)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-users fa-2x mb-2"></i><br>
                                        <strong>Belum ada publikasi</strong><br>
                                        <small>Belum ada mahasiswa yang mengajukan publikasi untuk periode <?= $periode ?></small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($laporan_mahasiswa as $key => $mhs): ?>
                                <tr>
                                    <td><?= $key + 1 ?></td>
                                    <td>
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <span class="name mb-0 text-sm font-weight-bold"><?= $mhs->nama_mahasiswa ?></span><br>
                                                <small class="text-muted"><?= $mhs->nim ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-sm" title="<?= $mhs->judul ?>">
                                            <?= word_limiter($mhs->judul, 8) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-sm"><?= $mhs->nama_pembimbing ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'draft' => 'badge-secondary',
                                            'submitted' => 'badge-info',
                                            'review_pembimbing' => 'badge-warning',
                                            'approved_pembimbing' => 'badge-primary',
                                            'review_staf' => 'badge-primary',
                                            'completed' => 'badge-success',
                                            'rejected' => 'badge-danger'
                                        ];
                                        
                                        $status_text = [
                                            'draft' => 'Draft',
                                            'submitted' => 'Diajukan',
                                            'review_pembimbing' => 'Review Dosen',
                                            'approved_pembimbing' => 'Approved Dosen',
                                            'review_staf' => 'Review Staf',
                                            'completed' => 'Selesai',
                                            'rejected' => 'Ditolak'
                                        ];
                                        
                                        $badge_class = $status_badges[$mhs->status] ?? 'badge-secondary';
                                        $status_display = $status_text[$mhs->status] ?? 'Unknown';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $status_display ?></span>
                                    </td>
                                    <td>
                                        <span class="text-sm">
                                            <?= date('d/m/Y', strtotime($mhs->tanggal_pengajuan)) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($mhs->lama_proses_hari && $mhs->status == 'completed'): ?>
                                            <span class="text-sm">
                                                <?= $mhs->lama_proses_hari ?> hari
                                            </span>
                                            <?php 
                                            $avg_process = $summary_prodi['rata_waktu_proses'] ?? 0;
                                            if ($avg_process > 0):
                                                if ($mhs->lama_proses_hari < $avg_process): ?>
                                                    <small class="text-success d-block">Cepat</small>
                                                <?php elseif ($mhs->lama_proses_hari > $avg_process * 1.5): ?>
                                                    <small class="text-warning d-block">Lambat</small>
                                                <?php endif;
                                            endif; ?>
                                        <?php elseif ($mhs->status == 'completed'): ?>
                                            <span class="text-muted">-</span>
                                        <?php else: ?>
                                            <span class="text-info">Dalam proses</span>
                                            <?php if (!empty($mhs->tanggal_pengajuan)): ?>
                                                <small class="text-muted d-block">
                                                    <?= floor((time() - strtotime($mhs->tanggal_pengajuan)) / (60 * 60 * 24)) ?> hari
                                                </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
echo $content;
?>

<script>
$(document).ready(function() {
    // Initialize DataTable for mahasiswa laporan
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#table-laporan-mahasiswa').DataTable({
            "order": [[ 5, "desc" ]], // Sort by tanggal pengajuan
            "pageLength": 25,
            "responsive": true,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ mahasiswa",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [0] } // Disable sorting on No column
            ]
        });
    }
    
    // Print functionality with custom styling
    window.print = function() {
        window.open('<?= current_url() ?>?print=1', '_blank');
    };
});
</script>

<style media="print">
.btn, .alert, .breadcrumb {
    display: none !important;
}

.card {
    border: none !important;
    box-shadow: none !important;
}

@page {
    margin: 1cm;
}

body {
    font-size: 12px;
}
</style>