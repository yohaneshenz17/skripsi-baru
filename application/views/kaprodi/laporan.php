<?php
ob_start();
?>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Rekapitulasi Proposal Menunggu Penetapan</h3>
                    </div>
                    <div class="col text-right">
                        <button onclick="printTable('tabel-menunggu')" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> Cetak</button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="tabel-menunggu">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Judul Proposal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($proposals_menunggu)): ?>
                            <?php $no = 1; foreach($proposals_menunggu as $p): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $p->nim ?></td>
                                <td><?= $p->nama_mahasiswa ?></td>
                                <td><?= $p->judul ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">Tidak ada proposal yang menunggu penetapan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                        <h3 class="mb-0">Rekapitulasi Riwayat Penetapan</h3>
                    </div>
                     <div class="col text-right">
                        <button onclick="printTable('tabel-riwayat')" class="btn btn-sm btn-success"><i class="fa fa-print"></i> Cetak</button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush" id="tabel-riwayat">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tgl. Penetapan</th>
                            <th>Mahasiswa</th>
                            <th>Judul</th>
                            <th>Pembimbing</th>
                            <th>Tim Penguji</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($proposals_riwayat)): ?>
                            <?php $no = 1; foreach($proposals_riwayat as $r): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($r->tanggal_penetapan)) ?></td>
                                <td><?= $r->nim ?> - <?= $r->nama_mahasiswa ?></td>
                                <td><?= $r->judul ?></td>
                                <td><?= $r->nama_pembimbing ?></td>
                                <td>
                                    <small>
                                        1. <?= $r->nama_penguji1 ?><br>
                                        2. <?= $r->nama_penguji2 ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Tidak ada riwayat penetapan.</td></tr>
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
// Fungsi simple untuk print tabel
function printTable(tableId) {
    var printContents = document.getElementById(tableId).outerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Rekapitulasi Laporan',
    'content' => $content,
    'script' => $script
]); 
?>