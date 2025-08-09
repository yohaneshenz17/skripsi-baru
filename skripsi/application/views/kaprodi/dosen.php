<?php
ob_start();
?>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">Daftar Dosen Program Studi</h3>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="datatable-dosen">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama Dosen</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Level</th>
                            <th>Bidang Keahlian</th>
                            <th class="text-center">Bimbingan</th>
                            <th class="text-center">Penguji</th>
                            <th class="text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        <?php if(!empty($dosen_list)): ?>
                            <?php 
                            $no = 1;
                            foreach($dosen_list as $d): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $d->nip ?></td>
                                <td><?= $d->nama ?></td>
                                <td><?= $d->email ?></td>
                                <td><?= $d->nomor_telepon ?: '-' ?></td>
                                <td>
                                    <?php 
                                        if($d->level == '2') echo '<span class="badge badge-info">Dosen</span>';
                                        if($d->level == '4') echo '<span class="badge badge-primary">Kaprodi</span>';
                                        if($d->level == '1') echo '<span class="badge badge-success">Admin</span>';
                                        if($d->level == '5') echo '<span class="badge badge-warning">Staf</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php if(!empty($d->bidang_keilmuan)): ?>
                                        <span class="text-sm"><?= htmlspecialchars($d->bidang_keilmuan) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">Belum diisi</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success"><?= $d->jumlah_bimbingan ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning"><?= $d->jumlah_penguji ?></span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="showDosenDetail(<?= $d->id ?>)" 
                                            data-toggle="tooltip" 
                                            title="Lihat detail profil dosen">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data dosen di program studi ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Dosen -->
<div class="modal fade" id="modalDetailDosen" tabindex="-1" role="dialog" aria-labelledby="modalDetailDosenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailDosenLabel">
                    <i class="fas fa-user-graduate"></i> Detail Profil Dosen
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailDosenContent">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
    $('#datatable-dosen').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        }
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

// Function untuk menampilkan detail dosen
function showDosenDetail(dosenId) {
    $('#modalDetailDosen').modal('show');
    
    // Reset content dengan loading spinner
    $('#detailDosenContent').html(`
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2">Memuat data...</p>
        </div>
    `);
    
    // AJAX call untuk get detail dosen
    $.ajax({
        url: base_url + 'kaprodi/ajax_dosen_detail/' + dosenId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#detailDosenContent').html(response.html);
            } else {
                $('#detailDosenContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        ${response.message || 'Gagal memuat data dosen'}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            $('#detailDosenContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Terjadi kesalahan saat memuat data. Silakan coba lagi.
                </div>
            `);
        }
    });
}
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Daftar Dosen',
    'content' => $content,
    'script' => $script
]); 
?>