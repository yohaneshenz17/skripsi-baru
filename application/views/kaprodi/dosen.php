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
                                <td><?= $d->nomor_telepon ?></td>
                                <td>
                                    <?php 
                                        if($d->level == '2') echo '<span class="badge badge-info">Dosen</span>';
                                        if($d->level == '4') echo '<span class="badge badge-primary">Kaprodi</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data dosen di program studi ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
});
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Daftar Dosen',
    'content' => $content,
    'script' => $script
]); 
?>