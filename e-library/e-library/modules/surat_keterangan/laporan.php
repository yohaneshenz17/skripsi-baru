<?php
/**
 * Halaman Laporan Surat Keterangan
 */

require_once 'config.php';
cekLoginAdmin();

$pageTitle = "Laporan Surat Keterangan";
include '../../includes/header.php';

// Ambil data untuk filter
$tahun_list = [];
$sql_tahun = "SELECT DISTINCT tahun_periode FROM surat_keterangan ORDER BY tahun_periode DESC";
$result_tahun = $conn->query($sql_tahun);
while ($row = $result_tahun->fetch_assoc()) {
    $tahun_list[] = $row['tahun_periode'];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Laporan Surat Keterangan
                    </h3>
                    <div class="card-tools">
                        <a href="index.php" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Laporan -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label>Tahun</label>
                            <select id="filter_tahun" class="form-control">
                                <option value="">Semua Tahun</option>
                                <?php foreach ($tahun_list as $tahun): ?>
                                    <option value="<?php echo $tahun; ?>" <?php echo ($tahun == date('Y')) ? 'selected' : ''; ?>>
                                        <?php echo $tahun; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Bulan</label>
                            <select id="filter_bulan" class="form-control">
                                <option value="">Semua Bulan</option>
                                <?php 
                                global $BULAN_INDONESIA;
                                foreach ($BULAN_INDONESIA as $key => $bulan): 
                                ?>
                                    <option value="<?php echo $key; ?>"><?php echo $bulan; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Jenis Surat</label>
                            <select id="filter_jenis" class="form-control">
                                <option value="">Semua Jenis</option>
                                <option value="UAS">UAS</option>
                                <option value="PPA">PPA</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label><br>
                            <button type="button" class="btn btn-primary" onclick="loadLaporan()">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportExcel()">
                                <i class="fas fa-file-excel"></i> Export
                            </button>
                        </div>
                    </div>

                    <!-- Statistik -->
                    <div class="row" id="statistik">
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Surat</span>
                                    <span class="info-box-number" id="stat_total">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Surat UAS</span>
                                    <span class="info-box-number" id="stat_uas">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-award"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Surat PPA</span>
                                    <span class="info-box-number" id="stat_ppa">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Override Tunggakan</span>
                                    <span class="info-box-number" id="stat_override">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Laporan -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tableLaporan">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="12%">Nomor Surat</th>
                                    <th width="10%">Tanggal</th>
                                    <th width="10%">NIM</th>
                                    <th width="20%">Nama Mahasiswa</th>
                                    <th width="15%">Program Studi</th>
                                    <th width="8%">Jenis</th>
                                    <th width="10%">Admin</th>
                                    <th width="10%">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-laporan">
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat laporan...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadLaporan();
});

function loadLaporan() {
    const tahun = $('#filter_tahun').val();
    const bulan = $('#filter_bulan').val();
    const jenis = $('#filter_jenis').val();
    
    $.ajax({
        url: 'ajax_laporan.php',
        type: 'POST',
        data: {
            action: 'load_laporan',
            tahun: tahun,
            bulan: bulan,
            jenis: jenis
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update statistik
                $('#stat_total').text(response.statistik.total);
                $('#stat_uas').text(response.statistik.uas);
                $('#stat_ppa').text(response.statistik.ppa);
                $('#stat_override').text(response.statistik.override);
                
                // Update tabel
                $('#tbody-laporan').html(response.html);
            } else {
                $('#tbody-laporan').html('<tr><td colspan="9" class="text-center text-danger">' + response.message + '</td></tr>');
            }
        },
        error: function() {
            $('#tbody-laporan').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat laporan</td></tr>');
        }
    });
}

function exportExcel() {
    const tahun = $('#filter_tahun').val();
    const bulan = $('#filter_bulan').val();
    const jenis = $('#filter_jenis').val();
    
    const params = new URLSearchParams({
        action: 'export_excel',
        tahun: tahun,
        bulan: bulan,
        jenis: jenis
    });
    
    window.open('ajax_laporan.php?' + params.toString(), '_blank');
}
</script>

<?php include '../../includes/footer.php'; ?>
