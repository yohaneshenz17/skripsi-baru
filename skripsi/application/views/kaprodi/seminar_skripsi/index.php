<?php
/**
 * PERBAIKAN VIEW - Index Seminar Skripsi Kaprodi
 * File: application/views/kaprodi/seminar_skripsi/index.php
 * 
 * PERBAIKAN:
 * - Modal info jadwal menampilkan data lengkap (pembimbing, penguji 1 & 2)
 * - Memperbaiki JavaScript untuk mengambil data dosen dari list
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-graduation-cap text-primary mr-2"></i>
                        Kelola Seminar Skripsi
                    </h1>
                    <p class="mb-0 text-muted">Validasi dan penjadwalan seminar skripsi mahasiswa</p>
                </div>
                <div class="text-right">
                    <button class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['pending_review'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Disetujui Bulan Ini
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['approved_month'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Ditolak Bulan Ini
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['rejected_month'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Terjadwal
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['scheduled'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
        <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
        <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endif; ?>

    <!-- Main Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-2"></i>
                Daftar Seminar Skripsi 
                <span class="badge badge-primary ml-2"><?= count($seminar_skripsi) ?> pengajuan</span>
            </h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Aksi:</div>
                    <a class="dropdown-item" href="#" onclick="location.reload()">
                        <i class="fas fa-sync-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Refresh Data
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Mahasiswa</th>
                            <th width="20%">Judul</th>
                            <th width="12%">Pembimbing</th>
                            <th width="8%">Plagiarisme</th>
                            <th width="10%">Status</th>
                            <th width="10%">Tanggal Pengajuan</th>
                            <th width="20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($seminar_skripsi)): ?>
                            <?php $no = 1; foreach ($seminar_skripsi as $seminar): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($seminar->nama_mahasiswa ?? 'N/A') ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($seminar->nim ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($seminar->judul_skripsi ?? $seminar->judul ?? 'N/A') ?>">
                                        <?= htmlspecialchars($seminar->judul_skripsi ?? $seminar->judul ?? 'N/A') ?>
                                    </div>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($seminar->nama_pembimbing ?? 'N/A') ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($seminar->plagiarism_percentage)): ?>
                                        <?php 
                                        $plag_class = 'badge-success';
                                        if ($seminar->plagiarism_percentage > 30) $plag_class = 'badge-danger';
                                        elseif ($seminar->plagiarism_percentage > 20) $plag_class = 'badge-warning';
                                        ?>
                                        <span class="badge <?= $plag_class ?>">
                                            <?= number_format($seminar->plagiarism_percentage, 1) ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = 'secondary';
                                    $status_text = 'Unknown';
                                    $status_icon = 'question';
                                    
                                    switch ($seminar->status_kaprodi ?? 'pending') {
                                        case 'pending':
                                            $status_class = 'warning';
                                            $status_text = 'Menunggu Review';
                                            $status_icon = 'clock';
                                            break;
                                        case 'approved':
                                            if (!empty($seminar->tanggal_seminar)) {
                                                $status_class = 'info';
                                                $status_text = 'Terjadwal';
                                                $status_icon = 'calendar-check';
                                            } else {
                                                $status_class = 'success';
                                                $status_text = 'Disetujui';
                                                $status_icon = 'check';
                                            }
                                            break;
                                        case 'rejected':
                                            $status_class = 'danger';
                                            $status_text = 'Ditolak';
                                            $status_icon = 'times';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?= $status_class ?>">
                                        <i class="fas fa-<?= $status_icon ?> mr-1"></i>
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d/m/Y', strtotime($seminar->created_at ?? 'now')) ?><br>
                                        <span class="text-muted"><?= date('H:i', strtotime($seminar->created_at ?? 'now')) ?></span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Detail Button -->
                                        <a href="<?= base_url('kaprodi/seminar_skripsi/detail/' . $seminar->id) ?>" 
                                           class="btn btn-info btn-sm" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Action Buttons Based on Status -->
                                        <?php if (($seminar->status_kaprodi ?? 'pending') === 'approved' && empty($seminar->tanggal_seminar)): ?>
                                            <!-- Penjadwalan Button -->
                                            <a href="<?= base_url('kaprodi/seminar_skripsi/penjadwalan/' . $seminar->id) ?>" 
                                               class="btn btn-success btn-sm" 
                                               title="Jadwalkan Seminar">
                                                <i class="fas fa-calendar-plus"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($seminar->tanggal_seminar)): ?>
                                            <!-- Info Jadwal Button -->
                                            <button type="button" 
                                                    class="btn btn-primary btn-sm" 
                                                    title="Info Jadwal"
                                                    onclick="showJadwalInfo(<?= htmlspecialchars(json_encode([
                                                        'tanggal_seminar' => $seminar->tanggal_seminar,
                                                        'jam_seminar' => $seminar->jam_seminar,
                                                        'tempat_seminar' => $seminar->tempat_seminar,
                                                        'nama_mahasiswa' => $seminar->nama_mahasiswa,
                                                        'nim' => $seminar->nim,
                                                        'judul' => $seminar->judul_skripsi ?? $seminar->judul ?? 'N/A',
                                                        'nama_pembimbing' => $seminar->nama_pembimbing ?? 'N/A',
                                                        'nama_penguji1' => $seminar->nama_penguji1 ?? 'N/A',
                                                        'nama_penguji2' => $seminar->nama_penguji2 ?? 'N/A'
                                                    ]), ENT_QUOTES) ?>)">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h6>Belum ada pengajuan seminar skripsi</h6>
                                        <p class="mb-0">Data akan muncul setelah mahasiswa mengajukan seminar skripsi</p>
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

<!-- ✅ PERBAIKAN: Modal Info Jadwal dengan Data Lengkap -->
<div class="modal fade" id="modalJadwalInfo" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Informasi Jadwal Seminar
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Info Mahasiswa -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-user-graduate mr-2"></i>
                            Informasi Mahasiswa
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nama:</strong>
                                <p id="info-nama-mahasiswa" class="mb-2">-</p>
                            </div>
                            <div class="col-md-6">
                                <strong>NIM:</strong>
                                <p id="info-nim" class="mb-2">-</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <strong>Judul Skripsi:</strong>
                                <p id="info-judul" class="mb-0">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Jadwal -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Jadwal Seminar
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Tanggal:</strong>
                                <p id="info-tanggal" class="mb-2">-</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Waktu:</strong>
                                <p id="info-waktu" class="mb-2">-</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Tempat:</strong>
                                <p id="info-tempat" class="mb-2">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ✅ PERBAIKAN: Info Tim Penguji Lengkap -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-users mr-2"></i>
                            Tim Penguji
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="fas fa-user-tie fa-2x text-primary mb-2"></i>
                                    <h6 class="font-weight-bold">Dosen Pembimbing</h6>
                                    <p id="info-pembimbing" class="mb-0 small">-</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="fas fa-user-graduate fa-2x text-success mb-2"></i>
                                    <h6 class="font-weight-bold">Penguji 1</h6>
                                    <p id="info-penguji1" class="mb-0 small">-</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded">
                                    <i class="fas fa-user-graduate fa-2x text-info mb-2"></i>
                                    <h6 class="font-weight-bold">Penguji 2</h6>
                                    <p id="info-penguji2" class="mb-0 small">-</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Catatan:</strong> Semua anggota tim penguji akan menerima notifikasi email mengenai jadwal seminar ini.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CSS Styling -->
<style>
.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.75rem;
}

.btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

.card {
    border-radius: 0.5rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.modal-header {
    border-radius: 0.5rem 0.5rem 0 0;
}

.alert {
    border-left: 4px solid;
    border-radius: 0.5rem;
}

.alert-success { border-left-color: #28a745; }
.alert-danger { border-left-color: #dc3545; }
.alert-warning { border-left-color: #ffc107; }
.alert-info { border-left-color: #17a2b8; }

@media (max-width: 768px) {
    .btn-group-sm {
        flex-direction: column;
    }
    .btn-group-sm > .btn {
        margin-bottom: 0.25rem;
    }
}
</style>

<!-- ✅ PERBAIKAN: JavaScript dengan Data Lengkap -->
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json"
        },
        "order": [[6, "desc"]], // Sort by date (newest first)
        "pageLength": 25,
        "responsive": true,
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Disable sorting on Action column
        ]
    });

    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
});

// ✅ PERBAIKAN: Function showJadwalInfo dengan Data Lengkap
function showJadwalInfo(jadwalData) {
    try {
        // ✅ Populate modal dengan data lengkap
        $('#info-nama-mahasiswa').text(jadwalData.nama_mahasiswa || '-');
        $('#info-nim').text(jadwalData.nim || '-');
        $('#info-judul').text(jadwalData.judul || '-');
        
        // Format tanggal
        if (jadwalData.tanggal_seminar) {
            const tanggal = new Date(jadwalData.tanggal_seminar);
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            $('#info-tanggal').text(tanggal.toLocaleDateString('id-ID', options));
        } else {
            $('#info-tanggal').text('-');
        }
        
        // Format waktu
        if (jadwalData.jam_seminar) {
            $('#info-waktu').text(jadwalData.jam_seminar + ' WIB');
        } else {
            $('#info-waktu').text('-');
        }
        
        $('#info-tempat').text(jadwalData.tempat_seminar || '-');
        
        // ✅ PERBAIKAN: Populate data tim penguji
        $('#info-pembimbing').text(jadwalData.nama_pembimbing || 'Belum ditentukan');
        $('#info-penguji1').text(jadwalData.nama_penguji1 || 'Belum ditentukan');
        $('#info-penguji2').text(jadwalData.nama_penguji2 || 'Belum ditentukan');
        
        // Show modal
        $('#modalJadwalInfo').modal('show');
        
    } catch (error) {
        console.error('Error showing jadwal info:', error);
        alert('Terjadi kesalahan saat menampilkan informasi jadwal');
    }
}

// Function to refresh data
function refreshData() {
    location.reload();
}

// Confirm before navigation
function confirmAction(message, url) {
    if (confirm(message)) {
        window.location.href = url;
    }
}
</script>