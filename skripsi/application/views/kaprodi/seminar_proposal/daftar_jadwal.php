<?php
/**
 * Kelola Penjadwalan Seminar Proposal - Kaprodi
 * File: application/views/kaprodi/seminar_proposal/daftar_jadwal.php
 * 
 * Halaman untuk mengelola semua penjadwalan seminar proposal
 * Diakses dari tombol "Kelola Penjadwalan" di dashboard
 */

// Start output buffering untuk content
ob_start();
?>

<!-- Breadcrumb Navigation -->
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-3 pb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi/dashboard') ?>">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi/seminar_proposal') ?>">Seminar Proposal</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Kelola Penjadwalan
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Info Alert -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="fas fa-info-circle"></i></span>
            <span class="alert-text">
                <strong>Kelola Penjadwalan:</strong> 
                Halaman ini memungkinkan Anda mengelola jadwal seminar proposal yang telah disetujui. 
                Pastikan untuk menetapkan waktu, tempat, dan dosen penguji dengan tepat.
            </span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Perlu Dijadwalkan</h5>
                        <span class="h2 font-weight-bold mb-0"><?= count($seminar_perlu_dijadwalkan ?? []) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-warning mr-2"><i class="fas fa-clock"></i></span>
                    <span class="text-nowrap">Menunggu penjadwalan</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Jadwal Mendatang</h5>
                        <span class="h2 font-weight-bold mb-0"><?= count($jadwal_mendatang ?? []) ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-info mr-2"><i class="fas fa-calendar-alt"></i></span>
                    <span class="text-nowrap">Sudah terjadwal</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Hari Ini</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $today_count = 0;
                            if (!empty($jadwal_mendatang)) {
                                foreach ($jadwal_mendatang as $jadwal) {
                                    if (date('Y-m-d', strtotime($jadwal->tanggal_seminar)) == date('Y-m-d')) {
                                        $today_count++;
                                    }
                                }
                            }
                            echo $today_count;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-success text-white rounded-circle shadow">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-success mr-2"><i class="fas fa-check"></i></span>
                    <span class="text-nowrap">Seminar hari ini</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card card-stats">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Minggu Ini</h5>
                        <span class="h2 font-weight-bold mb-0">
                            <?php 
                            $week_count = 0;
                            if (!empty($jadwal_mendatang)) {
                                $week_start = date('Y-m-d', strtotime('monday this week'));
                                $week_end = date('Y-m-d', strtotime('sunday this week'));
                                foreach ($jadwal_mendatang as $jadwal) {
                                    $jadwal_date = date('Y-m-d', strtotime($jadwal->tanggal_seminar));
                                    if ($jadwal_date >= $week_start && $jadwal_date <= $week_end) {
                                        $week_count++;
                                    }
                                }
                            }
                            echo $week_count;
                            ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-purple text-white rounded-circle shadow">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-sm">
                    <span class="text-purple mr-2"><i class="fas fa-clock"></i></span>
                    <span class="text-nowrap">Seminar minggu ini</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Content dengan Tab -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="jadwalTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="perlu-jadwal-tab" data-toggle="tab" href="#perlu-jadwal" role="tab">
                            <i class="fas fa-calendar-plus mr-1"></i>
                            Perlu Dijadwalkan
                            <?php if (!empty($seminar_perlu_dijadwalkan)): ?>
                                <span class="badge badge-warning ml-1"><?= count($seminar_perlu_dijadwalkan) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="sudah-jadwal-tab" data-toggle="tab" href="#sudah-jadwal" role="tab">
                            <i class="fas fa-calendar-check mr-1"></i>
                            Jadwal Mendatang
                            <?php if (!empty($jadwal_mendatang)): ?>
                                <span class="badge badge-info ml-1"><?= count($jadwal_mendatang) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="jadwalTabsContent">
                    
                    <!-- Tab Perlu Dijadwalkan -->
                    <div class="tab-pane fade show active" id="perlu-jadwal" role="tabpanel">
                        <?php if (!empty($seminar_perlu_dijadwalkan)): ?>
                            <div class="mb-3">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Perhatian:</strong> Ada <?= count($seminar_perlu_dijadwalkan) ?> seminar proposal yang sudah disetujui dan perlu dijadwalkan.
                                    Klik tombol "Jadwalkan" untuk mengatur waktu, tempat, dan dosen penguji.
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table align-items-center table-flush" id="table-perlu-jadwal">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Mahasiswa</th>
                                            <th>Judul Proposal</th>
                                            <th>Pembimbing</th>
                                            <th>Tanggal Disetujui</th>
                                            <th>Lama Menunggu</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($seminar_perlu_dijadwalkan as $index => $seminar): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="media align-items-center">
                                                    <div class="avatar rounded-circle mr-3" style="background-color: #fb6340;">
                                                        <span class="text-white font-weight-bold">
                                                            <?= strtoupper(substr($seminar->nama_mahasiswa, 0, 1)) ?>
                                                        </span>
                                                    </div>
                                                    <div class="media-body">
                                                        <span class="name mb-0 text-sm font-weight-bold"><?= htmlspecialchars($seminar->nama_mahasiswa) ?></span>
                                                        <br><small class="text-muted"><?= htmlspecialchars($seminar->nim) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-sm" data-toggle="tooltip" title="<?= htmlspecialchars($seminar->judul) ?>">
                                                    <?= strlen($seminar->judul) > 50 ? substr(htmlspecialchars($seminar->judul), 0, 50) . '...' : htmlspecialchars($seminar->judul) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($seminar->nama_pembimbing ?? '-') ?></td>
                                            <td>
                                                <span class="text-success">
                                                    <i class="fas fa-check mr-1"></i>
                                                    <?= date('d/m/Y', strtotime($seminar->tanggal_review_kaprodi)) ?>
                                                </span>
                                                <br>
                                                <small class="text-muted"><?= date('H:i', strtotime($seminar->tanggal_review_kaprodi)) ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                $days_waiting = (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', strtotime($seminar->tanggal_review_kaprodi)))) / (60*60*24);
                                                $badge_class = $days_waiting > 7 ? 'danger' : ($days_waiting > 3 ? 'warning' : 'info');
                                                ?>
                                                <span class="badge badge-<?= $badge_class ?>">
                                                    <?= $days_waiting ?> hari
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('kaprodi/seminar_proposal/jadwal/' . $seminar->id) ?>" 
                                                   class="btn btn-primary btn-sm" 
                                                   data-toggle="tooltip" 
                                                   title="Jadwalkan Seminar">
                                                    <i class="fas fa-calendar-plus"></i> Jadwalkan
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow mx-auto mb-4" style="width: 80px; height: 80px;">
                                    <i class="fas fa-check-circle" style="font-size: 32px; line-height: 80px;"></i>
                                </div>
                                <h3 class="text-muted">Semua Sudah Terjadwal</h3>
                                <p class="text-muted">
                                    Tidak ada seminar proposal yang perlu dijadwalkan saat ini.<br>
                                    Semua seminar yang disetujui sudah memiliki jadwal.
                                </p>
                                <a href="<?= base_url('kaprodi/seminar_proposal') ?>" class="btn btn-primary">
                                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Tab Jadwal Mendatang -->
                    <div class="tab-pane fade" id="sudah-jadwal" role="tabpanel">
                        <?php if (!empty($jadwal_mendatang)): ?>
                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Info:</strong> Daftar seminar proposal yang sudah dijadwalkan. 
                                    Anda dapat mengubah jadwal jika diperlukan dengan mengklik tombol "Edit".
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table align-items-center table-flush" id="table-jadwal-mendatang">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tanggal & Waktu</th>
                                            <th>Mahasiswa</th>
                                            <th>Judul</th>
                                            <th>Tempat</th>
                                            <th>Dosen Penguji</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($jadwal_mendatang as $jadwal): ?>
                                        <?php 
                                        $jadwal_datetime = strtotime($jadwal->tanggal_seminar . ' ' . $jadwal->jam_seminar);
                                        $is_today = date('Y-m-d', $jadwal_datetime) == date('Y-m-d');
                                        $is_upcoming = $jadwal_datetime > time();
                                        ?>
                                        <tr class="<?= $is_today ? 'table-info' : '' ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($is_today): ?>
                                                        <div class="icon icon-sm icon-shape bg-success text-white rounded-circle mr-2">
                                                            <i class="fas fa-calendar-day"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <span class="font-weight-bold">
                                                            <?= date('d/m/Y', strtotime($jadwal->tanggal_seminar)) ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= date('H:i', strtotime($jadwal->jam_seminar)) ?> WIT
                                                        </small>
                                                        <?php if ($is_today): ?>
                                                            <br><span class="badge badge-success badge-sm">HARI INI</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="media align-items-center">
                                                    <div class="avatar rounded-circle mr-3" style="background-color: #11cdef;">
                                                        <span class="text-white font-weight-bold">
                                                            <?= strtoupper(substr($jadwal->nama_mahasiswa, 0, 1)) ?>
                                                        </span>
                                                    </div>
                                                    <div class="media-body">
                                                        <span class="name mb-0 text-sm font-weight-bold"><?= htmlspecialchars($jadwal->nama_mahasiswa) ?></span>
                                                        <br><small class="text-muted"><?= htmlspecialchars($jadwal->nim) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-sm" data-toggle="tooltip" title="<?= htmlspecialchars($jadwal->judul) ?>">
                                                    <?= strlen($jadwal->judul) > 40 ? substr(htmlspecialchars($jadwal->judul), 0, 40) . '...' : htmlspecialchars($jadwal->judul) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?= htmlspecialchars($jadwal->tempat_seminar) ?></span>
                                            </td>
                                            <td>
                                                <small>
                                                    <strong>Penguji 1:</strong> <?= htmlspecialchars($jadwal->nama_penguji1 ?? '-') ?><br>
                                                    <strong>Penguji 2:</strong> <?= htmlspecialchars($jadwal->nama_penguji2 ?? '-') ?><br>
                                                    <strong>Pembimbing:</strong> <?= htmlspecialchars($jadwal->nama_pembimbing ?? '-') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($is_today): ?>
                                                    <span class="badge badge-success">HARI INI</span>
                                                <?php elseif ($is_upcoming): ?>
                                                    <span class="badge badge-info">MENDATANG</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">LEWAT</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= base_url('kaprodi/seminar_proposal/jadwal/' . $jadwal->id) ?>" 
                                                       class="btn btn-outline-primary btn-sm" 
                                                       data-toggle="tooltip" title="Edit Jadwal">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-outline-info btn-sm" 
                                                            onclick="showDetailJadwal(<?= $jadwal->id ?>)"
                                                            data-toggle="tooltip" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow mx-auto mb-4" style="width: 80px; height: 80px;">
                                    <i class="fas fa-calendar-alt" style="font-size: 32px; line-height: 80px;"></i>
                                </div>
                                <h3 class="text-muted">Belum Ada Jadwal</h3>
                                <p class="text-muted">
                                    Belum ada seminar proposal yang dijadwalkan.<br>
                                    Jadwalkan seminar yang sudah disetujui terlebih dahulu.
                                </p>
                                <a href="#perlu-jadwal" class="btn btn-primary" data-toggle="tab">
                                    <i class="fas fa-calendar-plus mr-2"></i>Lihat Yang Perlu Dijadwalkan
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Jadwal -->
<div class="modal fade" id="detailJadwalModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Detail Jadwal Seminar
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailJadwalContent">
                <!-- Content will be loaded via JavaScript -->
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
                    <p class="text-muted">Memuat detail jadwal...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php
// Tangkap content
$content = ob_get_clean();

// Start output buffering untuk script
ob_start();
?>
<script>
$(document).ready(function() {
    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize DataTables untuk tab perlu dijadwalkan
    if (typeof $.fn.DataTable !== 'undefined' && $('#table-perlu-jadwal').length) {
        $('#table-perlu-jadwal').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            },
            "pageLength": 10,
            "order": [[5, "desc"]], // Sort by lama menunggu
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Disable sort untuk kolom aksi
            ]
        });
    }
    
    // Initialize DataTables untuk tab jadwal mendatang
    if (typeof $.fn.DataTable !== 'undefined' && $('#table-jadwal-mendatang').length) {
        $('#table-jadwal-mendatang').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            },
            "pageLength": 10,
            "order": [[0, "asc"]], // Sort by tanggal
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Disable sort untuk kolom aksi
            ]
        });
    }
    
    // Highlight hari ini
    $('.table-info').css('background-color', '#e3f2fd');
    
    // Auto refresh setiap 5 menit untuk update jadwal
    setInterval(function() {
        location.reload();
    }, 300000);
    
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut();
    }, 5000);
    
    // Tab change handler untuk reinitialize DataTables
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($.fn.DataTable) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    });
});

function showDetailJadwal(jadwalId) {
    // Show modal
    $('#detailJadwalModal').modal('show');
    
    // Reset content
    $('#detailJadwalContent').html(`
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-muted mb-3"></i>
            <p class="text-muted">Memuat detail jadwal...</p>
        </div>
    `);
    
    // Load detail via AJAX (opsional, atau bisa langsung tampil data dari PHP)
    // Untuk implementasi sederhana, kita bisa langsung tampilkan info dari data yang ada
    
    // Cari data jadwal berdasarkan ID
    <?php if (!empty($jadwal_mendatang)): ?>
    const jadwalData = {
        <?php foreach ($jadwal_mendatang as $index => $j): ?>
        <?= $j->id ?>: {
            nama_mahasiswa: '<?= htmlspecialchars($j->nama_mahasiswa) ?>',
            nim: '<?= htmlspecialchars($j->nim) ?>',
            judul: '<?= htmlspecialchars($j->judul) ?>',
            tanggal_seminar: '<?= date('d F Y', strtotime($j->tanggal_seminar)) ?>',
            jam_seminar: '<?= date('H:i', strtotime($j->jam_seminar)) ?>',
            tempat_seminar: '<?= htmlspecialchars($j->tempat_seminar) ?>',
            nama_pembimbing: '<?= htmlspecialchars($j->nama_pembimbing ?? '-') ?>',
            nama_penguji1: '<?= htmlspecialchars($j->nama_penguji1 ?? '-') ?>',
            nama_penguji2: '<?= htmlspecialchars($j->nama_penguji2 ?? '-') ?>'
        }<?= $index < count($jadwal_mendatang) - 1 ? ',' : '' ?>
        <?php endforeach; ?>
    };
    
    if (jadwalData[jadwalId]) {
        const data = jadwalData[jadwalId];
        const content = `
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Detail Seminar Proposal</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mahasiswa:</strong><br>${data.nama_mahasiswa} (${data.nim})</p>
                            <p><strong>Tanggal:</strong><br>${data.tanggal_seminar}</p>
                            <p><strong>Waktu:</strong><br>${data.jam_seminar} WIT</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tempat:</strong><br>${data.tempat_seminar}</p>
                            <p><strong>Pembimbing:</strong><br>${data.nama_pembimbing}</p>
                        </div>
                    </div>
                    <hr>
                    <p><strong>Judul:</strong><br>${data.judul}</p>
                    <hr>
                    <h6>Tim Penguji:</h6>
                    <ul>
                        <li><strong>Pembimbing:</strong> ${data.nama_pembimbing}</li>
                        <li><strong>Penguji 1:</strong> ${data.nama_penguji1}</li>
                        <li><strong>Penguji 2:</strong> ${data.nama_penguji2}</li>
                    </ul>
                </div>
            </div>
        `;
        $('#detailJadwalContent').html(content);
    } else {
        $('#detailJadwalContent').html(`
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                <p class="text-muted">Detail jadwal tidak ditemukan.</p>
            </div>
        `);
    }
    <?php else: ?>
    $('#detailJadwalContent').html(`
        <div class="text-center py-4">
            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
            <p class="text-muted">Data jadwal tidak tersedia.</p>
        </div>
    `);
    <?php endif; ?>
}
</script>
<?php
// Tangkap script
$script = ob_get_clean();

// Load template kaprodi
$this->load->view('template/kaprodi', [
    'title' => 'Kelola Penjadwalan Seminar Proposal',
    'content' => $content,
    'script' => $script
]);