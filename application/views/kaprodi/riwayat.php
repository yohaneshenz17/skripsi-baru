<?php
// Mulai output buffering
ob_start();
?>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">Riwayat Penetapan Pembimbing & Penguji</h3>
            </div>
            
            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="datatable-riwayat">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Penetapan</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Judul Proposal</th>
                            <th>Pembimbing</th>
                            <th>Tim Penguji</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        <?php if(!empty($proposals)): ?>
                            <?php 
                            $no = 1;
                            foreach($proposals as $p): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($p->tanggal_penetapan)) ?></td>
                                <td><?= $p->nim ?></td>
                                <td><?= $p->nama_mahasiswa ?></td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="top" title="<?= htmlspecialchars($p->judul) ?>">
                                        <?= substr($p->judul, 0, 40) ?>...
                                    </span>
                                </td>
                                <td><?= $p->nama_pembimbing ?: '-' ?></td>
                                <td>
                                    <small>
                                        1. <?= $p->nama_penguji1 ?: '-' ?><br>
                                        2. <?= $p->nama_penguji2 ?: '-' ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada riwayat penetapan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
    $('#datatable-riwayat').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        },
        "order": [[ 1, "desc" ]] // Urutkan berdasarkan tanggal penetapan terbaru
    });
    
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
<?php 
// Tangkap skrip
$script = ob_get_clean(); 

// Panggil template HANYA SEKALI di akhir file
$this->load->view('template/kaprodi', [
    'title' => 'Riwayat Penetapan',
    'content' => $content,
    'script' => $script
]); 
?>