<?php
ob_start();
?>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">Daftar Mahasiswa Program Studi</h3>
            </div>

            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="datatable-mahasiswa">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="list">
                        <?php if(!empty($mahasiswa_list)): ?>
                            <?php 
                            $no = 1;
                            foreach($mahasiswa_list as $m): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $m->nim ?></td>
                                <td><?= $m->nama ?></td>
                                <td><?= $m->email ?></td>
                                <td><?= $m->nomor_telepon ?></td>
                                <td>
                                    <?php if($m->status == '1'): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data mahasiswa di program studi ini.</td>
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
    $('#datatable-mahasiswa').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        }
    });
});
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Daftar Mahasiswa',
    'content' => $content,
    'script' => $script
]); 
?>