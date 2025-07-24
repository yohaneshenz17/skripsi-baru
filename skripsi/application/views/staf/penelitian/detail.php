// ========================================
// 7. DETAIL PENELITIAN VIEW
// File: application/views/staf/penelitian/detail.php
// ========================================

ob_start();
?>

<div class="row">
    <!-- Info Mahasiswa -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-control-label">Nama Mahasiswa</label>
                    <p class="form-control-static"><?= $proposal->nama_mahasiswa ?></p>
                </div>
                <div class="form-group">
                    <label class="form-control-label">NIM</label>
                    <p class="form-control-static"><?= $proposal->nim ?></p>
                </div>
                <div class="form-group">
                    <label class="form-control-label">Program Studi</label>
                    <p class="form-control-static"><?= $proposal->nama_prodi ?></p>
                </div>
                <div class="form-group">
                    <label class="form-control-label">Email</label>
                    <p class="form-control-static"><?= $proposal->email ?></p>
                </div>
                <div class="form-group">
                    <label class="form-control-label">No. Telepon</label>
                    <p class="form-control-static"><?= $proposal->nomor_telepon ?: '-' ?></p>
                </div>
                <div class="form-group">
                    <label class="form-control-label">Dosen Pembimbing</label>
                    <p class="form-control-static"><?= $proposal->nama_pembimbing ?: '-' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Penelitian -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Informasi Penelitian</h5>
                    </div>
                    <div class="col text-right">
                        <?php if($proposal->status_izin_penelitian == '0'): ?>
                            <a href="<?= base_url('staf/penelitian/cetak_surat/' . $proposal->id) ?>" 
                               class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-print"></i> Cetak Surat Izin
                            </a>
                        <?php endif; ?>
                        
                        <?php if($proposal->surat_izin_penelitian): ?>
                            <a href="<?= base_url('staf/penelitian/download_surat/' . $proposal->id) ?>" 
                               class="btn btn-sm btn-success" target="_blank">
                                <i class="fas fa-download"></i> Download Surat
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-control-label">Judul Penelitian</label>
                    <p class="form-control-static"><?= $proposal->judul ?></p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Lokasi Penelitian</label>
                            <p class="form-control-static"><?= $proposal->lokasi_penelitian ?: '-' ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Status Izin</label>
                            <p class="form-control-static">
                                <?php
                                switch($proposal->status_izin_penelitian) {
                                    case '0':
                                        echo '<span class="badge badge-warning">Butuh Surat Izin</span>';
                                        break;
                                    case '1':
                                        echo '<span class="badge badge-success">Sudah Ada Surat</span>';
                                        break;
                                    case '2':
                                        echo '<span class="badge badge-danger">Ditolak</span>';
                                        break;
                                    default:
                                        echo '<span class="badge badge-secondary">Unknown</span>';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if($proposal->surat_izin_penelitian): ?>
                    <div class="form-group">
                        <label class="form-control-label">File Surat Izin</label>
                        <p class="form-control-static">
                            <i class="fas fa-file-pdf text-danger"></i> 
                            <?= $proposal->surat_izin_penelitian ?>
                            <small class="text-muted">
                                (Upload: <?= $proposal->tanggal_upload_surat ? 
                                    date('d/m/Y H:i', strtotime($proposal->tanggal_upload_surat)) : '-' ?>)
                            </small>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if($proposal->status_izin_penelitian == '1' && !$proposal->surat_izin_penelitian): ?>
                    <!-- Form Upload Surat -->
                    <div class="alert alert-info">
                        <strong>Info:</strong> Surat izin penelitian sudah dicetak. 
                        Silakan upload surat yang sudah ditandatangani.
                    </div>
                    
                    <form action="<?= base_url('staf/penelitian/upload_surat') ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                        <div class="form-group">
                            <label class="form-control-label">Upload Surat Izin (PDF)</label>
                            <input type="file" name="surat_file" class="form-control" accept=".pdf" required>
                            <small class="form-text text-muted">
                                Upload surat izin penelitian yang sudah ditandatangani (format PDF, max 5MB)
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Surat
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if(!empty($log_aktivitas)): ?>
    <div class="row mt-4">
        <div class="col">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="mb-0">Log Aktivitas</h3>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Aktivitas</th>
                                <th scope="col">Staf</th>
                                <th scope="col">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($log_aktivitas as $log): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($log->tanggal_aktivitas)) ?></td>
                                    <td>
                                        <?php
                                        $aktivitas_labels = [
                                            'export_surat_izin' => 'Export Surat Izin',
                                            'upload_surat_izin' => 'Upload Surat Izin'
                                        ];
                                        echo $aktivitas_labels[$log->aktivitas] ?? $log->aktivitas;
                                        ?>
                                    </td>
                                    <td><?= $log->nama_staf ?></td>
                                    <td><?= $log->keterangan ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
$this->load->view('template/staf', [
    'title' => 'Detail Penelitian - ' . $proposal->nama_mahasiswa,
    'content' => $content,
    'css' => '',
    'script' => ''
]);
?>