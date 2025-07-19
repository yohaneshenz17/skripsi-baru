<?php
// ============================================
// FILE: application/views/kaprodi/proposal.php (LAYOUT DIPERBAIKI)
// PERBAIKAN: Layout mepet sidebar, tidak terlalu ke kanan
// ============================================

ob_start();
?>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="ni ni-fat-remove"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- PERBAIKAN: Container utama tanpa row agar full width -->
<div class="card">
    <div class="card-header border-0">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="mb-0">Daftar Proposal Mahasiswa untuk Review</h3>
                <p class="text-sm mb-0">Kelola review dan penetapan pembimbing untuk proposal mahasiswa</p>
            </div>
            <div class="col-auto">
                <a href="javascript:void(0)" onclick="showRiwayatTab()" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-history"></i> Riwayat Review
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs untuk filter status -->
    <div class="card-body pt-0">
        <div class="nav-wrapper">
            <ul class="nav nav-pills nav-fill flex-column flex-md-row" id="tabs-icons-text" role="tablist">
                <li class="nav-item">
                    <a class="nav-link mb-sm-3 mb-md-0 active" id="tabs-icons-text-1-tab" data-toggle="tab" href="#tabs-icons-text-1" role="tab" aria-controls="tabs-icons-text-1" aria-selected="true">
                        <i class="ni ni-cloud-upload-96 mr-2"></i>Menunggu Review
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mb-sm-3 mb-md-0" id="tabs-icons-text-2-tab" data-toggle="tab" href="#tabs-icons-text-2" role="tab" aria-controls="tabs-icons-text-2" aria-selected="false">
                        <i class="ni ni-check-bold mr-2"></i>Disetujui
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mb-sm-3 mb-md-0" id="tabs-icons-text-3-tab" data-toggle="tab" href="#tabs-icons-text-3" role="tab" aria-controls="tabs-icons-text-3" aria-selected="false">
                        <i class="ni ni-fat-remove mr-2"></i>Ditolak
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mb-sm-3 mb-md-0" id="tabs-icons-text-4-tab" data-toggle="tab" href="#tabs-icons-text-4" role="tab" aria-controls="tabs-icons-text-4" aria-selected="false">
                        <i class="ni ni-single-02 mr-2"></i>Menunggu Pembimbing
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mb-sm-3 mb-md-0" id="tabs-icons-text-5-tab" data-toggle="tab" href="#tabs-icons-text-5" role="tab" aria-controls="tabs-icons-text-5" aria-selected="false">
                        <i class="fas fa-history mr-2"></i>Riwayat Review
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="tab-content" id="myTabContent">
            <!-- Tab Menunggu Review (status_kaprodi = 0) -->
            <div class="tab-pane fade show active" id="tabs-icons-text-1" role="tabpanel" aria-labelledby="tabs-icons-text-1-tab">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush" id="table-menunggu">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Judul Proposal</th>
                                <th>Tanggal Ajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $count_menunggu = 0;
                            // PERBAIKAN: Cek isset dan is_array dulu
                            if(isset($proposals) && is_array($proposals) && count($proposals) > 0): 
                                foreach($proposals as $p): 
                                    // PERBAIKAN: Cek property ada atau tidak
                                    if(isset($p->status_kaprodi) && $p->status_kaprodi == '0'): // Menunggu review
                                        $count_menunggu++;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge badge-dot mr-4"><i class="bg-warning"></i><?= isset($p->nim) ? $p->nim : '-' ?></span></td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="mb-0 text-sm font-weight-bold"><?= isset($p->nama_mahasiswa) ? $p->nama_mahasiswa : '-' ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top" title="<?= isset($p->judul) ? htmlspecialchars($p->judul) : '' ?>">
                                        <?= isset($p->judul) ? substr($p->judul, 0, 50) . '...' : '-' ?>
                                    </span>
                                </td>
                                <td><?= isset($p->created_at) ? date('d/m/Y H:i', strtotime($p->created_at)) : (isset($p->id) ? date('d/m/Y H:i', strtotime($p->id)) : '-') ?></td>
                                <td>
                                    <a href="<?= base_url() ?>kaprodi/review_proposal/<?= isset($p->id) ? $p->id : '' ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Review
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                    endif;
                                endforeach; 
                            endif;
                            
                            if($count_menunggu == 0):
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2"></i><br>
                                        <strong>Tidak ada proposal yang menunggu review</strong><br>
                                        <small>Belum ada mahasiswa yang mengajukan proposal</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Disetujui (status_kaprodi = 1) -->
            <div class="tab-pane fade" id="tabs-icons-text-2" role="tabpanel" aria-labelledby="tabs-icons-text-2-tab">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush" id="table-disetujui">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Judul Proposal</th>
                                <th>Pembimbing</th>
                                <th>Status Pembimbing</th>
                                <th>Tanggal Disetujui</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $count_disetujui = 0;
                            // PERBAIKAN: Cek isset dan is_array dulu
                            if(isset($proposals) && is_array($proposals) && count($proposals) > 0): 
                                foreach($proposals as $p): 
                                    // PERBAIKAN: Cek property ada atau tidak
                                    if(isset($p->status_kaprodi) && $p->status_kaprodi == '1'): // Disetujui
                                        $count_disetujui++;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge badge-dot mr-4"><i class="bg-success"></i><?= isset($p->nim) ? $p->nim : '-' ?></span></td>
                                <td><?= isset($p->nama_mahasiswa) ? $p->nama_mahasiswa : '-' ?></td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top" title="<?= isset($p->judul) ? htmlspecialchars($p->judul) : '' ?>">
                                        <?= isset($p->judul) ? substr($p->judul, 0, 40) . '...' : '-' ?>
                                    </span>
                                </td>
                                <td><?= (isset($p->nama_pembimbing) && $p->nama_pembimbing) ? $p->nama_pembimbing : '<span class="text-muted">Belum ada</span>' ?></td>
                                <td>
                                    <?php 
                                    if(isset($p->status_pembimbing)):
                                        switch($p->status_pembimbing) {
                                            case '0':
                                                echo '<span class="badge badge-warning">Menunggu Respon</span>';
                                                break;
                                            case '1':
                                                echo '<span class="badge badge-success">Menyetujui</span>';
                                                break;
                                            case '2':
                                                echo '<span class="badge badge-danger">Menolak</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-secondary">Belum Ditentukan</span>';
                                        }
                                    else:
                                        echo '<span class="badge badge-secondary">Belum Ditentukan</span>';
                                    endif;
                                    ?>
                                </td>
                                <td><?= isset($p->tanggal_review_kaprodi) ? date('d/m/Y H:i', strtotime($p->tanggal_review_kaprodi)) : '-' ?></td>
                            </tr>
                            <?php 
                                    endif;
                                endforeach; 
                            endif;
                            
                            if($count_disetujui == 0):
                            ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2"></i><br>
                                        <strong>Belum ada proposal yang disetujui</strong><br>
                                        <small>Proposal yang disetujui akan muncul di sini</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Ditolak (status_kaprodi = 2) -->
            <div class="tab-pane fade" id="tabs-icons-text-3" role="tabpanel" aria-labelledby="tabs-icons-text-3-tab">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush" id="table-ditolak">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Judul Proposal</th>
                                <th>Komentar</th>
                                <th>Tanggal Ditolak</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $count_ditolak = 0;
                            // PERBAIKAN: Cek isset dan is_array dulu
                            if(isset($proposals) && is_array($proposals) && count($proposals) > 0): 
                                foreach($proposals as $p): 
                                    // PERBAIKAN: Cek property ada atau tidak
                                    if(isset($p->status_kaprodi) && $p->status_kaprodi == '2'): // Ditolak
                                        $count_ditolak++;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge badge-dot mr-4"><i class="bg-danger"></i><?= isset($p->nim) ? $p->nim : '-' ?></span></td>
                                <td><?= isset($p->nama_mahasiswa) ? $p->nama_mahasiswa : '-' ?></td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top" title="<?= isset($p->judul) ? htmlspecialchars($p->judul) : '' ?>">
                                        <?= isset($p->judul) ? substr($p->judul, 0, 40) . '...' : '-' ?>
                                    </span>
                                </td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top" title="<?= isset($p->komentar_kaprodi) ? htmlspecialchars($p->komentar_kaprodi) : '' ?>">
                                        <?= isset($p->komentar_kaprodi) ? substr($p->komentar_kaprodi, 0, 30) . '...' : 'Tidak ada komentar' ?>
                                    </span>
                                </td>
                                <td><?= isset($p->tanggal_review_kaprodi) ? date('d/m/Y H:i', strtotime($p->tanggal_review_kaprodi)) : '-' ?></td>
                            </tr>
                            <?php 
                                    endif;
                                endforeach; 
                            endif;
                            
                            if($count_ditolak == 0):
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2"></i><br>
                                        <strong>Belum ada proposal yang ditolak</strong><br>
                                        <small>Proposal yang ditolak akan muncul di sini</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Menunggu Pembimbing -->
            <div class="tab-pane fade" id="tabs-icons-text-4" role="tabpanel" aria-labelledby="tabs-icons-text-4-tab">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush" id="table-menunggu-pembimbing">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Judul Proposal</th>
                                <th>Pembimbing Ditunjuk</th>
                                <th>Status</th>
                                <th>Tanggal Penunjukan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $count_menunggu_pembimbing = 0;
                            // PERBAIKAN: Cek isset dan is_array dulu
                            if(isset($proposals) && is_array($proposals) && count($proposals) > 0): 
                                foreach($proposals as $p): 
                                    // PERBAIKAN: Cek property ada atau tidak
                                    if(isset($p->status_kaprodi) && $p->status_kaprodi == '1' && 
                                       isset($p->status_pembimbing) && $p->status_pembimbing == '0'): // Disetujui tapi pembimbing belum respon
                                        $count_menunggu_pembimbing++;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge badge-dot mr-4"><i class="bg-info"></i><?= isset($p->nim) ? $p->nim : '-' ?></span></td>
                                <td><?= isset($p->nama_mahasiswa) ? $p->nama_mahasiswa : '-' ?></td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top" title="<?= isset($p->judul) ? htmlspecialchars($p->judul) : '' ?>">
                                        <?= isset($p->judul) ? substr($p->judul, 0, 40) . '...' : '-' ?>
                                    </span>
                                </td>
                                <td><?= isset($p->nama_pembimbing) ? $p->nama_pembimbing : '-' ?></td>
                                <td><span class="badge badge-warning">Menunggu Respon</span></td>
                                <td><?= isset($p->tanggal_penetapan) ? date('d/m/Y H:i', strtotime($p->tanggal_penetapan)) : '-' ?></td>
                            </tr>
                            <?php 
                                    endif;
                                endforeach; 
                            endif;
                            
                            if($count_menunggu_pembimbing == 0):
                            ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2"></i><br>
                                        <strong>Tidak ada proposal yang menunggu pembimbing</strong><br>
                                        <small>Proposal yang menunggu respon pembimbing akan muncul di sini</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Riwayat Review -->
            <div class="tab-pane fade" id="tabs-icons-text-5" role="tabpanel" aria-labelledby="tabs-icons-text-5-tab">
                <div class="table-responsive">
                    <table class="table align-items-center table-flush" id="table-riwayat">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Judul Proposal</th>
                                <th>Status Review</th>
                                <th>Dosen Pembimbing</th>
                                <th>Tanggal Review</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $count_riwayat = 0;
                            // PERBAIKAN: Cek isset dan is_array dulu
                            if(isset($proposals) && is_array($proposals) && count($proposals) > 0): 
                                foreach($proposals as $p): 
                                    // PERBAIKAN: Cek property ada atau tidak
                                    if(isset($p->tanggal_review_kaprodi) && $p->tanggal_review_kaprodi): // Sudah direview (disetujui atau ditolak)
                                        $count_riwayat++;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge badge-dot mr-4">
                                    <i class="bg-<?= (isset($p->status_kaprodi) && $p->status_kaprodi == '1') ? 'success' : 'danger' ?>"></i>
                                    <?= isset($p->nim) ? $p->nim : '-' ?>
                                </span></td>
                                <td><?= isset($p->nama_mahasiswa) ? $p->nama_mahasiswa : '-' ?></td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top" title="<?= isset($p->judul) ? htmlspecialchars($p->judul) : '' ?>">
                                        <?= isset($p->judul) ? substr($p->judul, 0, 40) . '...' : '-' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(isset($p->status_kaprodi) && $p->status_kaprodi == '1'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= (isset($p->nama_pembimbing) && $p->nama_pembimbing) ? $p->nama_pembimbing : '<span class="text-muted">-</span>' ?></td>
                                <td><?= isset($p->tanggal_review_kaprodi) ? date('d/m/Y H:i', strtotime($p->tanggal_review_kaprodi)) : '-' ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= base_url() ?>kaprodi/review_proposal/<?= isset($p->id) ? $p->id : '' ?>" 
                                           class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if(isset($p->status_kaprodi) && $p->status_kaprodi == '2'): // Jika ditolak, bisa direview ulang ?>
                                        <a href="<?= base_url() ?>kaprodi/review_proposal/<?= isset($p->id) ? $p->id : '' ?>" 
                                           class="btn btn-warning btn-sm" title="Review Ulang">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                    endif;
                                endforeach; 
                            endif;
                            
                            if($count_riwayat == 0):
                            ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fa fa-inbox fa-2x mb-2"></i><br>
                                        <strong>Belum ada riwayat review</strong><br>
                                        <small>Riwayat review proposal akan muncul di sini</small>
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
</div>

<!-- Info Panel -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3 class="text-white mb-0">Workflow Proposal</h3>
                        <p class="text-white mt-2 mb-0">
                            1. Mahasiswa submit proposal → 
                            2. <strong>Kaprodi review & tetapkan pembimbing</strong> → 
                            3. Dosen pembimbing setujui/tolak → 
                            4. Mulai bimbingan proposal
                        </p>
                    </div>
                    <div class="col-auto">
                        <span class="h2 text-white">📋</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 

ob_start(); 
?>
<script>
$(document).ready(function() {
    // Initialize DataTables untuk setiap tab jika library tersedia
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#table-menunggu').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            "order": [[ 4, "desc" ]],
            "pageLength": 25,
            "responsive": true
        });
        
        $('#table-disetujui').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            "order": [[ 6, "desc" ]],
            "pageLength": 25,
            "responsive": true
        });
        
        $('#table-ditolak').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            "order": [[ 5, "desc" ]],
            "pageLength": 25,
            "responsive": true
        });
        
        $('#table-menunggu-pembimbing').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            "order": [[ 6, "desc" ]],
            "pageLength": 25,
            "responsive": true
        });
        
        $('#table-riwayat').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            "order": [[ 6, "desc" ]],
            "pageLength": 25,
            "responsive": true
        });
    }
    
    // Enable tooltips jika library tersedia
    if (typeof $().tooltip !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Function untuk menampilkan tab Riwayat Review
function showRiwayatTab() {
    // Aktifkan tab Riwayat Review
    $('#tabs-icons-text-5-tab').tab('show');
    
    // Scroll ke atas tabel
    $('html, body').animate({
        scrollTop: $('#tabs-icons-text').offset().top - 100
    }, 500);
}
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Review Proposal Mahasiswa', 
    'content' => $content, 
    'script' => $script
]); 
?>