<?php
// Mulai output buffering untuk menangkap konten
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

<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Content Card -->
<div class="card">
    <div class="card-header border-0">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="mb-0">Seminar Proposal</h3>
                <p class="text-sm mb-0">Kelola validasi dan penjadwalan seminar proposal mahasiswa</p>
            </div>
            <div class="col text-right">
                <span class="badge badge-primary">
                    Total: <?= isset($seminar_proposals) ? count($seminar_proposals) : 0 ?> pengajuan
                </span>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table align-items-center table-flush" id="datatable-seminar-proposal">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">Judul</th>
                        <th scope="col">Pembimbing</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tanggal Pengajuan</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($seminar_proposals)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fa fa-inbox fa-2x mb-2"></i><br>
                                    <strong>Tidak ada data seminar proposal</strong><br>
                                    <small>Belum ada mahasiswa yang mengajukan seminar proposal</small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($seminar_proposals as $key => $proposal): ?>
                            <tr>
                                <td><?= $key + 1 ?></td>
                                <td>
                                    <div class="media align-items-center">
                                        <div class="media-body">
                                            <span class="name font-weight-bold"><?= $proposal->nama_mahasiswa ?></span><br>
                                            <small class="text-muted"><?= $proposal->nim ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm" title="<?= $proposal->judul ?>">
                                        <?= word_limiter($proposal->judul, 8) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm"><?= isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : '-' ?></span>
                                </td>
                                <td>
                                    <?php if(!isset($proposal->status_seminar_proposal) || $proposal->status_seminar_proposal == '0'): ?>
                                        <span class="badge badge-warning">Menunggu Review</span>
                                    <?php elseif($proposal->status_seminar_proposal == '1'): ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-sm">
                                        <?= date('d/m/Y H:i', strtotime($proposal->created_at)) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('kaprodi/seminar_proposal/detail/' . $proposal->id) ?>" 
                                       class="btn btn-sm btn-primary" title="Lihat Detail">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Info Card -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-white">📋 Informasi Seminar Proposal</h5>
                        <p class="card-text text-white-50">
                            • Seminar proposal dilaksanakan setelah mahasiswa menyelesaikan minimum 8x bimbingan<br>
                            • Mahasiswa mempresentasikan Bab 1-3 dari proposal skripsi<br>
                            • Kaprodi bertugas memvalidasi pengajuan dan menjadwalkan seminar<br>
                            • Setelah disetujui, mahasiswa dapat melanjutkan ke tahap penelitian
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Tangkap semua HTML di atas ke dalam variabel $content
$content = ob_get_clean();

// Mulai buffer baru untuk skrip
ob_start();
?>
<script>
$(document).ready(function() {
    // Inisialisasi DataTable jika library tersedia
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#datatable-seminar-proposal').DataTable({
            "order": [[ 5, "desc" ]], // Urutkan berdasarkan tanggal pengajuan terbaru
            "pageLength": 25,
            "responsive": true
        });
    }
    
    // Inisialisasi tooltip jika bootstrap tersedia
    if (typeof $().tooltip !== 'undefined') {
        $('[data-toggle="tooltip"]').tooltip();
    }
});
</script>
<?php
// Tangkap skrip
$script = ob_get_clean();

// Panggil template HANYA SEKALI di akhir file - FORMAT YANG BENAR
$this->load->view('template/kaprodi', [
    'title' => 'Seminar Proposal',
    'content' => $content,
    'script' => $script
]);
?>