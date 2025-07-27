<!-- Header -->
<div class="card bg-info text-white mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-9">
                <h4 class="text-white mb-0">📖 Detail Jurnal Bimbingan</h4>
                <p class="mb-0 mt-2">
                    <strong>Pertemuan ke-<?= $jurnal->pertemuan_ke ?></strong> | 
                    Tanggal: <?= date('d F Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                </p>
            </div>
            <div class="col-lg-3 text-end">
                <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Detail Jurnal -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt text-info"></i> 
                    Jurnal Bimbingan Pertemuan ke-<?= $jurnal->pertemuan_ke ?>
                </h5>
            </div>
            <div class="card-body">
                
                <!-- Status dan Info Dasar -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Informasi Dasar</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Pertemuan ke:</strong></td>
                                <td><?= $jurnal->pertemuan_ke ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal:</strong></td>
                                <td><?= date('d F Y', strtotime($jurnal->tanggal_bimbingan)) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php if($jurnal->status_validasi == '1'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Tervalidasi
                                        </span>
                                    <?php elseif($jurnal->status_validasi == '2'): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-edit"></i> Perlu Revisi
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-clock"></i> Pending Validasi
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Dosen Pembimbing</h6>
                        <table class="table table-sm">
                            <tr>
                                <td width="40%"><strong>Nama:</strong></td>
                                <td><?= $jurnal->nama_dosen ?></td>
                            </tr>
                            <tr>
                                <td><strong>Proposal:</strong></td>
                                <td><?= substr($jurnal->judul, 0, 40) ?><?= strlen($jurnal->judul) > 40 ? '...' : '' ?></td>
                            </tr>
                            <?php if($jurnal->tanggal_validasi): ?>
                            <tr>
                                <td><strong>Divalidasi:</strong></td>
                                <td><?= date('d F Y H:i', strtotime($jurnal->tanggal_validasi)) ?> WIT</td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <!-- Materi Bimbingan -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-book text-primary"></i> Materi Bimbingan
                    </h6>
                    <div class="bg-light p-3 rounded border">
                        <?= nl2br(htmlspecialchars($jurnal->materi_bimbingan)) ?>
                    </div>
                </div>
                
                <!-- Tindak Lanjut -->
                <?php if($jurnal->tindak_lanjut): ?>
                <div class="mb-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-tasks text-warning"></i> Tindak Lanjut
                    </h6>
                    <div class="bg-light p-3 rounded border">
                        <?= nl2br(htmlspecialchars($jurnal->tindak_lanjut)) ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Catatan Dosen -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-comment text-info"></i> Catatan Dosen
                    </h6>
                    <div class="bg-light p-3 rounded border">
                        <?php if($jurnal->catatan_dosen): ?>
                            <?= nl2br(htmlspecialchars($jurnal->catatan_dosen)) ?>
                        <?php else: ?>
                            <em class="text-muted">Belum ada catatan dari dosen pembimbing</em>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Timeline -->
                <div class="border-top pt-4 mt-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-history text-secondary"></i> Timeline
                    </h6>
                    <div class="timeline">
                        <div class="d-flex mb-2">
                            <div class="flex-shrink-0">
                                <span class="badge bg-primary rounded-pill">1</span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Jurnal Dibuat</strong><br>
                                <small class="text-muted">
                                    <?= date('d F Y H:i', strtotime($jurnal->created_at)) ?> WIT
                                </small>
                            </div>
                        </div>
                        
                        <?php if($jurnal->updated_at != $jurnal->created_at): ?>
                        <div class="d-flex mb-2">
                            <div class="flex-shrink-0">
                                <span class="badge bg-warning rounded-pill">2</span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Jurnal Diupdate</strong><br>
                                <small class="text-muted">
                                    <?= date('d F Y H:i', strtotime($jurnal->updated_at)) ?> WIT
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($jurnal->tanggal_validasi): ?>
                        <div class="d-flex mb-2">
                            <div class="flex-shrink-0">
                                <span class="badge bg-success rounded-pill">
                                    <?= ($jurnal->updated_at != $jurnal->created_at) ? '3' : '2' ?>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Jurnal Divalidasi</strong><br>
                                <small class="text-muted">
                                    <?= date('d F Y H:i', strtotime($jurnal->tanggal_validasi)) ?> WIT
                                </small>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="d-flex mb-2">
                            <div class="flex-shrink-0">
                                <span class="badge bg-secondary rounded-pill">
                                    <?= ($jurnal->updated_at != $jurnal->created_at) ? '3' : '2' ?>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong>Menunggu Validasi Dosen</strong><br>
                                <small class="text-muted">
                                    Jurnal belum divalidasi oleh dosen pembimbing
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                    
                    <?php if($jurnal->status_validasi == '0'): ?>
                    <a href="<?= base_url('mahasiswa/bimbingan/edit_jurnal/' . $jurnal->id) ?>" class="btn btn-warning me-2">
                        <i class="fas fa-edit"></i> Edit Jurnal
                    </a>
                    <button type="button" class="btn btn-danger" onclick="hapusJurnal(<?= $jurnal->id ?>)">
                        <i class="fas fa-trash"></i> Hapus Jurnal
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Panel -->
<?php if($jurnal->status_validasi == '0'): ?>
<div class="row mt-4">
    <div class="col-lg-8 mx-auto">
        <div class="alert alert-info">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-info-circle fa-2x"></i>
                </div>
                <div class="col">
                    <h6 class="alert-heading mb-1">Jurnal Belum Divalidasi</h6>
                    <p class="mb-0">
                        Jurnal bimbingan ini masih menunggu validasi dari dosen pembimbing. 
                        Anda masih dapat mengedit atau menghapus jurnal ini sebelum divalidasi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php elseif($jurnal->status_validasi == '2'): ?>
<div class="row mt-4">
    <div class="col-lg-8 mx-auto">
        <div class="alert alert-warning">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="col">
                    <h6 class="alert-heading mb-1">Jurnal Perlu Revisi</h6>
                    <p class="mb-0">
                        Dosen pembimbing meminta revisi pada jurnal ini. 
                        Silakan periksa catatan dosen dan lakukan perbaikan yang diperlukan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row mt-4">
    <div class="col-lg-8 mx-auto">
        <div class="alert alert-success">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <div class="col">
                    <h6 class="alert-heading mb-1">Jurnal Telah Divalidasi</h6>
                    <p class="mb-0">
                        Jurnal bimbingan ini telah divalidasi oleh dosen pembimbing. 
                        Jurnal yang sudah divalidasi tidak dapat diubah lagi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Hapus Jurnal
function hapusJurnal(jurnalId) {
    if (confirm("Apakah Anda yakin ingin menghapus jurnal ini? Jurnal yang sudah divalidasi tidak dapat dihapus.")) {
        window.location.href = "<?= base_url('mahasiswa/bimbingan/hapus_jurnal/') ?>" + jurnalId;
    }
}
</script>