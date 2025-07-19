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
                <h3 class="mb-0">Publikasi Tugas Akhir</h3>
                <p class="text-sm mb-0">Kelola validasi publikasi tugas akhir mahasiswa ke repository</p>
            </div>
            <div class="col text-right">
                <span class="badge badge-primary">
                    Total: <?= isset($publikasi_list) ? count($publikasi_list) : 0 ?> pengajuan
                </span>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table align-items-center table-flush" id="datatable-publikasi">
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
                    <?php if(empty($publikasi_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fa fa-crown fa-2x mb-2"></i><br>
                                    <strong>Tidak ada data publikasi</strong><br>
                                    <small>Belum ada mahasiswa yang mengajukan publikasi</small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($publikasi_list as $key => $proposal): ?>
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
                                    <?php if(!isset($proposal->status_publikasi) || $proposal->status_publikasi == '0'): ?>
                                        <span class="badge badge-warning">Menunggu Review</span>
                                    <?php elseif($proposal->status_publikasi == '1'): ?>
                                        <span class="badge badge-success">Dipublikasi</span>
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
                                    <a href="<?= base_url('kaprodi/publikasi/detail/' . $proposal->id) ?>" 
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
        <div class="card bg-gradient-warning">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h5 class="card-title text-white">👑 Informasi Publikasi Tugas Akhir</h5>
                        <p class="card-text text-white-50">
                            • Publikasi dilakukan setelah mahasiswa lulus seminar skripsi<br>
                            • Mahasiswa mengunggah skripsi final ke repository institusi<br>
                            • Kaprodi bertugas memvalidasi kelengkapan dokumen publikasi<br>
                            • Setelah disetujui, mahasiswa dinyatakan selesai tugas akhir
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
        $('#datatable-publikasi').DataTable({
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
    'title' => 'Publikasi Tugas Akhir',
    'content' => $content,
    'script' => $script
]);
?>