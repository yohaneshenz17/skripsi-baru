<?php
/**
 * View Detail Penelitian Staf
 * File: application/views/staf/penelitian/detail.php
 */

ob_start();
?>

<div class="row">
    <!-- Info Mahasiswa -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user text-primary"></i>
                    Informasi Mahasiswa
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-control-label">Nama Mahasiswa</label>
                    <p class="form-control-static font-weight-bold"><?= $proposal->nama_mahasiswa ?></p>
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
                <div class="form-group">
                    <label class="form-control-label">Status Workflow</label>
                    <p class="form-control-static">
                        <span class="badge badge-info"><?= ucwords(str_replace('_', ' ', $proposal->workflow_status)) ?></span>
                    </p>
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
                        <h5 class="mb-0">
                            <i class="fas fa-microscope text-primary"></i>
                            Informasi Penelitian
                        </h5>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url('staf/penelitian') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
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
                            <label class="form-control-label">Jenis Penelitian</label>
                            <p class="form-control-static"><?= $proposal->jenis_penelitian ?: '-' ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Lokasi Penelitian</label>
                            <p class="form-control-static"><?= $proposal->lokasi_penelitian ?: '-' ?></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-control-label">Status Izin Penelitian</label>
                    <p class="form-control-static">
                        <?php 
                        $status_text = '';
                        $status_class = '';
                        switch($proposal->status_izin_penelitian) {
                            case '0':
                                $status_text = 'Belum Diminta';
                                $status_class = 'badge-secondary';
                                break;
                            case '1':
                                $status_text = 'Disetujui';
                                $status_class = 'badge-success';
                                break;
                            case '2':
                                $status_text = 'Ditolak';
                                $status_class = 'badge-danger';
                                break;
                            default:
                                $status_text = 'Status Tidak Dikenal';
                                $status_class = 'badge-light';
                        }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                    </p>
                </div>

                <?php if($proposal->surat_izin_penelitian): ?>
                    <div class="form-group">
                        <label class="form-control-label">File Surat Izin</label>
                        <p class="form-control-static">
                            <a href="<?= base_url('staf/penelitian/download_surat/' . $proposal->id) ?>" 
                               class="btn btn-sm btn-success" target="_blank">
                                <i class="fas fa-download"></i> Download Surat
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Permohonan Izin Penelitian (jika ada) -->
        <?php if($permohonan): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt text-info"></i>
                    Detail Permohonan Izin Penelitian
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Tanggal Mulai Penelitian</label>
                            <p class="form-control-static"><?= date('d F Y', strtotime($permohonan->tanggal_mulai_penelitian)) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Tanggal Selesai Penelitian</label>
                            <p class="form-control-static"><?= date('d F Y', strtotime($permohonan->tanggal_selesai_penelitian)) ?></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-control-label">Status Pembimbing</label>
                    <p class="form-control-static">
                        <?php 
                        $status_class = '';
                        $status_text = '';
                        switch($permohonan->status_pembimbing) {
                            case 'pending':
                                $status_class = 'badge-warning';
                                $status_text = 'Menunggu Review';
                                break;
                            case 'approved':
                                $status_class = 'badge-success';
                                $status_text = 'Disetujui';
                                break;
                            case 'rejected':
                                $status_class = 'badge-danger';
                                $status_text = 'Ditolak';
                                break;
                            default:
                                $status_class = 'badge-light';
                                $status_text = 'Status Tidak Dikenal';
                        }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                        
                        <?php if($permohonan->tanggal_review_pembimbing): ?>
                            <small class="text-muted ml-2">
                                (<?= date('d/m/Y H:i', strtotime($permohonan->tanggal_review_pembimbing)) ?>)
                            </small>
                        <?php endif; ?>
                    </p>
                </div>

                <?php if($permohonan->komentar_pembimbing): ?>
                    <div class="form-group">
                        <label class="form-control-label">Komentar Pembimbing</label>
                        <div class="alert alert-info">
                            <i class="fas fa-comment"></i>
                            <?= $permohonan->komentar_pembimbing ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($permohonan->file_surat_izin_staf): ?>
                    <div class="form-group">
                        <label class="form-control-label">Surat dari Staf</label>
                        <p class="form-control-static">
                            <a href="<?= base_url('staf/penelitian/download_surat/' . $proposal->id) ?>" 
                               class="btn btn-sm btn-success" target="_blank">
                                <i class="fas fa-download"></i> Download Surat Final
                            </a>
                            <?php if($permohonan->tanggal_upload_surat_staf): ?>
                                <small class="text-muted d-block mt-1">
                                    Upload: <?= date('d F Y, H:i', strtotime($permohonan->tanggal_upload_surat_staf)) ?>
                                </small>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if($permohonan->keterangan_staf): ?>
                    <div class="form-group">
                        <label class="form-control-label">Keterangan Staf</label>
                        <div class="alert alert-light">
                            <i class="fas fa-info-circle"></i>
                            <?= $permohonan->keterangan_staf ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cog text-primary"></i>
                    Aksi Tersedia
                </h5>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <?php if($permohonan && $permohonan->status_pembimbing == 'approved'): ?>
                        <!-- Actions untuk permohonan yang sudah disetujui -->
                        <a href="<?= base_url('staf/penelitian/cetak_form_permohonan/' . $proposal->id) ?>" 
                           class="btn btn-info" target="_blank">
                            <i class="fas fa-file-alt"></i> Cetak Form Permohonan
                        </a>
                        
                        <a href="<?= base_url('staf/penelitian/cetak_surat/' . $proposal->id) ?>" 
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-print"></i> Cetak Surat Izin
                        </a>
                        
                        <?php if(!$permohonan->file_surat_izin_staf): ?>
                            <button class="btn btn-success" onclick="uploadSurat(<?= $proposal->id ?>)">
                                <i class="fas fa-upload"></i> Upload Surat Bertanda Tangan
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <?php if(!$permohonan): ?>
                                Mahasiswa belum mengajukan permohonan izin penelitian.
                            <?php elseif($permohonan->status_pembimbing == 'pending'): ?>
                                Permohonan masih menunggu review dari dosen pembimbing.
                            <?php elseif($permohonan->status_pembimbing == 'rejected'): ?>
                                Permohonan ditolak oleh dosen pembimbing.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <a href="<?= base_url('staf/penelitian/log_aktivitas/' . $proposal->id) ?>" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-history"></i> Lihat Log Aktivitas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Surat -->
<div class="modal fade" id="modalUploadSurat" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-upload text-success"></i>
                    Upload Surat Izin Bertanda Tangan
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formUploadSurat" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Upload file surat izin penelitian yang sudah ditandatangani pihak berwenang.
                    </div>
                    
                    <div class="form-group">
                        <label>File Surat (PDF, max 2MB) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="file_surat" accept=".pdf" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-file-pdf text-danger"></i>
                            Hanya file PDF dengan ukuran maksimal 2MB
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan (Opsional)</label>
                        <textarea class="form-control" name="keterangan" rows="3" 
                                  placeholder="Tambahkan keterangan jika diperlukan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Upload Surat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$this->load->view('template/staf', [
    'title' => 'Detail Penelitian - ' . $proposal->nama_mahasiswa,
    'content' => $content,
    'script' => ''
]);
?>

<script>
function uploadSurat(proposalId) {
    $('#formUploadSurat').attr('action', '<?= base_url("staf/penelitian/upload_surat/") ?>' + proposalId);
    $('#modalUploadSurat').modal('show');
}

// Handle form submit upload surat
$('#formUploadSurat').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    var actionUrl = $(this).attr('action');
    
    // Show loading
    var submitBtn = $(this).find('button[type="submit"]');
    var originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Uploading...').prop('disabled', true);
    
    $.ajax({
        url: actionUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.status == 'success') {
                alert('✅ ' + response.message);
                location.reload();
            } else {
                alert('❌ Error: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Terjadi kesalahan saat upload file');
        },
        complete: function() {
            // Restore button
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});
</script>