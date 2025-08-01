<?php
/**
 * SIMPLE VERSION - File: application/views/staf/penelitian/log_aktivitas.php
 * Buat file ini di server dengan path yang tepat
 */

ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">
                            <i class="fas fa-history text-primary"></i>
                            Log Aktivitas Penelitian
                        </h3>
                        <?php if(isset($mahasiswa) && $mahasiswa): ?>
                            <p class="text-muted">
                                <strong><?= $mahasiswa->nama ?></strong> (<?= $mahasiswa->nim ?>)
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url('staf/penelitian') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if(!empty($log_aktivitas)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Aktivitas</th>
                                    <th>Keterangan</th>
                                    <th>Staf</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($log_aktivitas as $index => $log): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <?php
                                            // Icon berdasarkan jenis aktivitas
                                            $icon_class = 'fas fa-circle';
                                            $badge_class = 'badge-secondary';
                                            
                                            switch($log->aktivitas) {
                                                case 'cetak_form_permohonan':
                                                    $icon_class = 'fas fa-file-alt';
                                                    $badge_class = 'badge-info';
                                                    $activity_name = 'Cetak Form Permohonan';
                                                    break;
                                                case 'cetak_surat':
                                                    $icon_class = 'fas fa-print';
                                                    $badge_class = 'badge-primary';
                                                    $activity_name = 'Cetak Surat Izin';
                                                    break;
                                                case 'upload_surat':
                                                    $icon_class = 'fas fa-upload';
                                                    $badge_class = 'badge-success';
                                                    $activity_name = 'Upload Surat';
                                                    break;
                                                case 'download_surat':
                                                    $icon_class = 'fas fa-download';
                                                    $badge_class = 'badge-warning';
                                                    $activity_name = 'Download Surat';
                                                    break;
                                                case 'export_surat_izin':
                                                    $icon_class = 'fas fa-file-export';
                                                    $badge_class = 'badge-primary';
                                                    $activity_name = 'Export Surat Izin';
                                                    break;
                                                case 'export_jurnal':
                                                    $icon_class = 'fas fa-file-export';
                                                    $badge_class = 'badge-info';
                                                    $activity_name = 'Export Jurnal';
                                                    break;
                                                default:
                                                    $activity_name = ucwords(str_replace('_', ' ', $log->aktivitas));
                                            }
                                            ?>
                                            <span class="badge <?= $badge_class ?>">
                                                <i class="<?= $icon_class ?>"></i>
                                                <?= $activity_name ?>
                                            </span>
                                        </td>
                                        <td><?= $log->keterangan ?: '-' ?></td>
                                        <td><?= $log->nama_staf ?: 'System' ?></td>
                                        <td>
                                            <?php
                                            // Format tanggal yang user-friendly
                                            $timestamp = strtotime($log->tanggal_aktivitas);
                                            $now = time();
                                            $diff = $now - $timestamp;
                                            
                                            if ($diff < 60) {
                                                echo '<span class="text-success">Baru saja</span>';
                                            } elseif ($diff < 3600) {
                                                $minutes = floor($diff / 60);
                                                echo '<span class="text-info">' . $minutes . ' menit lalu</span>';
                                            } elseif ($diff < 86400) {
                                                $hours = floor($diff / 3600);
                                                echo '<span class="text-warning">' . $hours . ' jam lalu</span>';
                                            } else {
                                                echo '<span class="text-muted">' . date('d/m/Y H:i', $timestamp) . '</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Summary -->
                    <div class="mt-4">
                        <div class="alert alert-light">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h4 class="text-primary"><?= count($log_aktivitas) ?></h4>
                                    <small class="text-muted">Total Aktivitas</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-info">
                                        <?= count(array_filter($log_aktivitas, function($log) { 
                                            return in_array($log->aktivitas, ['cetak_form_permohonan', 'cetak_surat', 'export_surat_izin']); 
                                        })) ?>
                                    </h4>
                                    <small class="text-muted">Cetak/Export</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-success">
                                        <?= count(array_filter($log_aktivitas, function($log) { 
                                            return $log->aktivitas == 'upload_surat'; 
                                        })) ?>
                                    </h4>
                                    <small class="text-muted">Upload</small>
                                </div>
                                <div class="col-md-3">
                                    <h4 class="text-warning">
                                        <?= count(array_filter($log_aktivitas, function($log) { 
                                            return in_array($log->aktivitas, ['download_surat', 'export_jurnal']); 
                                        })) ?>
                                    </h4>
                                    <small class="text-muted">Download</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Empty state -->
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-history text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                        <h5 class="text-muted">Belum Ada Aktivitas</h5>
                        <p class="text-muted">
                            Log aktivitas akan muncul ketika staf melakukan aksi pada penelitian mahasiswa ini.
                        </p>
                        <a href="<?= base_url('staf/penelitian') ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Penelitian
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$this->load->view('template/staf', [
    'title' => 'Log Aktivitas Penelitian',
    'content' => $content,
    'script' => ''
]);
?>

<script>
$(document).ready(function() {
    // Auto refresh setiap 5 menit
    setTimeout(function() {
        location.reload();
    }, 300000);
});
</script>