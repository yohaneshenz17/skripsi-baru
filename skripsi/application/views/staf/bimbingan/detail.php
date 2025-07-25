<?php
// ========================================
// DETAIL BIMBINGAN VIEW - FIXED VERSION
// File: application/views/staf/bimbingan/detail.php
// Fixed: Field name errors and undefined properties
// ========================================

ob_start();
?>

<div class="row">
    <!-- Info Mahasiswa -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-control-label">Nama Mahasiswa</label>
                            <p class="form-control-static"><?= isset($proposal->nama_mahasiswa) ? $proposal->nama_mahasiswa : '-' ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">NIM</label>
                            <p class="form-control-static"><?= isset($proposal->nim) ? $proposal->nim : '-' ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Program Studi</label>
                            <p class="form-control-static"><?= isset($proposal->nama_prodi) ? $proposal->nama_prodi : '-' ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Email</label>
                            <!-- FIXED: Using email_mahasiswa instead of email -->
                            <p class="form-control-static"><?= isset($proposal->email_mahasiswa) ? $proposal->email_mahasiswa : '-' ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">No. Telepon</label>
                            <p class="form-control-static"><?= isset($proposal->nomor_telepon) && !empty($proposal->nomor_telepon) ? $proposal->nomor_telepon : '-' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAMBAHAN: Card Progress Bimbingan -->
        <?php if (isset($statistik_bimbingan)): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Statistik Bimbingan</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="stat-item">
                            <h3 class="text-success"><?= $statistik_bimbingan['tervalidasi'] ?></h3>
                            <small class="text-muted">Tervalidasi</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item">
                            <h3 class="text-warning"><?= $statistik_bimbingan['pending'] ?></h3>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
                <div class="progress mt-3">
                    <div class="progress-bar bg-success" style="width: <?= $statistik_bimbingan['progress_persen'] ?>%"></div>
                </div>
                <small class="text-muted d-block mt-2">
                    Progress: <?= $statistik_bimbingan['total'] ?>/<?= $statistik_bimbingan['target_minimal'] ?> 
                    (<?= $statistik_bimbingan['progress_persen'] ?>%)
                </small>
                <?php if ($statistik_bimbingan['target_tercapai']): ?>
                    <div class="alert alert-success mt-2 py-2">
                        <i class="fas fa-check-circle"></i> Target minimal tercapai!
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-2 py-2">
                        <i class="fas fa-info-circle"></i> Sisa <?= $statistik_bimbingan['sisa_bimbingan'] ?> pertemuan lagi
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info Tugas Akhir -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Informasi Tugas Akhir</h5>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url('staf/bimbingan/export_jurnal/' . (isset($proposal->id) ? $proposal->id : '')) ?>" 
                           class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download"></i> Export Jurnal PDF
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-control-label">Judul Tugas Akhir</label>
                    <p class="form-control-static"><?= isset($proposal->judul) ? $proposal->judul : '-' ?></p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Dosen Pembimbing</label>
                            <p class="form-control-static"><?= isset($proposal->nama_pembimbing) && !empty($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : 'Belum ditetapkan' ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Email Pembimbing</label>
                            <p class="form-control-static"><?= isset($proposal->email_pembimbing) && !empty($proposal->email_pembimbing) ? $proposal->email_pembimbing : '-' ?></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Status Workflow</label>
                            <p class="form-control-static">
                                <?php
                                $workflow_labels = [
                                    'proposal' => 'Pengajuan Proposal',
                                    'bimbingan' => 'Bimbingan',
                                    'seminar_proposal' => 'Seminar Proposal',
                                    'penelitian' => 'Penelitian',
                                    'seminar_skripsi' => 'Seminar Skripsi',
                                    'publikasi' => 'Publikasi',
                                    'selesai' => 'Selesai'
                                ];
                                $current_status = isset($proposal->workflow_status) ? $proposal->workflow_status : 'unknown';
                                echo isset($workflow_labels[$current_status]) ? $workflow_labels[$current_status] : 'Status tidak diketahui';
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Tanggal Pengajuan</label>
                            <p class="form-control-static">
                                <?= isset($proposal->created_at) && !empty($proposal->created_at) ? date('d F Y', strtotime($proposal->created_at)) : '-' ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- TAMBAHAN: Info NIP Pembimbing -->
                <?php if (isset($proposal->nip_pembimbing) && !empty($proposal->nip_pembimbing)): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">NIP Pembimbing</label>
                            <p class="form-control-static"><?= $proposal->nip_pembimbing ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Telepon Pembimbing</label>
                            <p class="form-control-static"><?= isset($proposal->telepon_pembimbing) && !empty($proposal->telepon_pembimbing) ? $proposal->telepon_pembimbing : '-' ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Jurnal Bimbingan</h3>
                        <p class="text-sm mb-0 text-muted">
                            <?php if (isset($jurnal_bimbingan)): ?>
                                Total: <?= count($jurnal_bimbingan) ?> pertemuan
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="filterJurnal('all')">Semua Status</a>
                                <a class="dropdown-item" href="#" onclick="filterJurnal('1')">Tervalidasi</a>
                                <a class="dropdown-item" href="#" onclick="filterJurnal('0')">Pending</a>
                                <a class="dropdown-item" href="#" onclick="filterJurnal('2')">Revisi</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="jurnalTable">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col" style="width: 8%">Pertemuan</th>
                            <th scope="col" style="width: 12%">Tanggal</th>
                            <th scope="col" style="width: 35%">Materi Bimbingan</th>
                            <th scope="col" style="width: 30%">Catatan Dosen</th>
                            <th scope="col" style="width: 10%">Status</th>
                            <th scope="col" style="width: 15%">Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
                            <?php foreach($jurnal_bimbingan as $jurnal): ?>
                                <tr data-status="<?= isset($jurnal->status_validasi) ? $jurnal->status_validasi : '0' ?>">
                                    <td class="text-center">
                                        <span class="badge badge-secondary">
                                            <?= isset($jurnal->pertemuan_ke) ? $jurnal->pertemuan_ke : '-' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= isset($jurnal->tanggal_bimbingan) ? date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) : '-' ?>
                                    </td>
                                    <td class="text-wrap" style="max-width: 300px;">
                                        <?= isset($jurnal->materi_bimbingan) ? htmlspecialchars($jurnal->materi_bimbingan) : '-' ?>
                                        
                                        <!-- TAMBAHAN: Tindak lanjut jika ada -->
                                        <?php if (isset($jurnal->tindak_lanjut) && !empty($jurnal->tindak_lanjut)): ?>
                                            <br><small class="text-info">
                                                <strong>Tindak lanjut:</strong> <?= htmlspecialchars($jurnal->tindak_lanjut) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-wrap" style="max-width: 250px;">
                                        <!-- FIXED: Using catatan_dosen instead of catatan_pembimbing -->
                                        <?= isset($jurnal->catatan_dosen) && !empty($jurnal->catatan_dosen) ? htmlspecialchars($jurnal->catatan_dosen) : '-' ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_val = isset($jurnal->status_validasi) ? $jurnal->status_validasi : '0';
                                        ?>
                                        <?php if($status_val == '1'): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Valid
                                            </span>
                                        <?php elseif($status_val == '2'): ?>
                                            <span class="badge badge-warning">
                                                <i class="fas fa-edit"></i> Revisi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($jurnal->tanggal_validasi) && !empty($jurnal->tanggal_validasi)): ?>
                                            <small class="text-success">
                                                <?= date('d/m/Y', strtotime($jurnal->tanggal_validasi)) ?>
                                            </small>
                                            <?php if (isset($jurnal->nama_dosen_validator) && !empty($jurnal->nama_dosen_validator)): ?>
                                                <br><small class="text-muted">
                                                    oleh: <?= htmlspecialchars($jurnal->nama_dosen_validator) ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <small class="text-muted">Belum divalidasi</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-center">
                                        <i class="fas fa-journal-whills fa-2x mb-3 text-muted"></i>
                                        <h5 class="text-muted">Belum ada jurnal bimbingan</h5>
                                        <p class="text-muted mb-0">
                                            Jurnal bimbingan akan muncul setelah mahasiswa melakukan input jurnal bimbingan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- TAMBAHAN: Footer dengan informasi tambahan -->
            <?php if (isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Pertemuan terakhir: 
                            <?php 
                            if (isset($statistik_bimbingan['pertemuan_terakhir']) && !empty($statistik_bimbingan['pertemuan_terakhir'])) {
                                echo date('d F Y', strtotime($statistik_bimbingan['pertemuan_terakhir']));
                            } else {
                                echo 'Belum ada';
                            }
                            ?>
                        </small>
                    </div>
                    <div class="col-md-6 text-right">
                        <small class="text-muted">
                            Target minimal: 16 pertemuan
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Back Button -->
<div class="row mt-3">
    <div class="col">
        <a href="<?= site_url('staf/bimbingan') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Bimbingan
        </a>
    </div>
</div>

<?php 
$content = ob_get_clean();

// JavaScript untuk filter dan interaktivitas
ob_start();
?>
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto refresh page setiap 5 menit untuk data terbaru
    setTimeout(function() {
        if (confirm('Refresh data untuk mendapatkan informasi terbaru?')) {
            location.reload();
        }
    }, 300000); // 5 menit
});

function filterJurnal(status) {
    const rows = document.querySelectorAll('#jurnalTable tbody tr[data-status]');
    
    rows.forEach(row => {
        if (status === 'all') {
            row.style.display = '';
        } else {
            const rowStatus = row.getAttribute('data-status');
            row.style.display = rowStatus === status ? '' : 'none';
        }
    });
    
    // Update dropdown text
    const dropdownBtn = document.querySelector('.dropdown-toggle');
    const statusText = {
        'all': 'Semua Status',
        '0': 'Pending',
        '1': 'Tervalidasi', 
        '2': 'Revisi'
    };
    dropdownBtn.innerHTML = '<i class="fas fa-filter"></i> ' + statusText[status];
}

function refreshData() {
    if (confirm('Refresh halaman untuk mendapatkan data terbaru?')) {
        location.reload();
    }
}
</script>

<style>
.stat-item h3 {
    margin-bottom: 0;
    font-size: 1.5rem;
}

.progress {
    height: 8px;
}

.table td {
    vertical-align: middle;
}

.text-wrap {
    word-wrap: break-word;
    white-space: normal;
}

.card-footer {
    background-color: #f8f9fe !important;
    border-top: 1px solid #e3ebf0;
}
</style>
<?php 
$script = ob_get_clean();

$this->load->view('template/staf', [
    'title' => 'Detail Bimbingan - ' . (isset($proposal->nama_mahasiswa) ? $proposal->nama_mahasiswa : 'Mahasiswa'),
    'content' => $content,
    'css' => '',
    'script' => $script
]);
?>