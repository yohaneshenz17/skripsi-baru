<?php
ob_start();
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Review Detail Proposal</h3>
                <p class="text-sm mb-0">Periksa proposal dan tentukan dosen pembimbing</p>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Data Mahasiswa</h4>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="30%"><strong>NIM</strong></td>
                                <td>: <?= $proposal->nim ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nama</strong></td>
                                <td>: <?= $proposal->nama_mahasiswa ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: <?= $proposal->email_mahasiswa ?></td>
                            </tr>
                            <tr>
                                <td><strong>Program Studi</strong></td>
                                <td>: <?= $proposal->nama_prodi ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>Status Proposal</h4>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> <strong>Menunggu Review Kaprodi</strong><br>
                            <small>Proposal sedang menunggu review dan persetujuan dari Anda</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h4>Detail Proposal</h4>
                
                <div class="form-group">
                    <label><strong>Judul Proposal</strong></label>
                    <div class="alert alert-info">
                        <?= isset($proposal->judul) ? $proposal->judul : 'Judul tidak tersedia' ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><strong>Uraian Masalah</strong></label>
                    <div class="card">
                        <div class="card-body">
                            <?= isset($proposal->uraian_masalah) ? nl2br($proposal->uraian_masalah) : 'Uraian masalah tidak tersedia' ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><strong>File Draft Proposal</strong></label>
                    <div class="card">
                        <div class="card-body">
                            <?php if(isset($proposal->file_draft_proposal) && !empty($proposal->file_draft_proposal)): ?>
                                <?php 
                                // PERBAIKAN PATH: Gunakan cdn/proposals/ seperti mahasiswa
                                $file_path = FCPATH . 'cdn/proposals/' . $proposal->file_draft_proposal;
                                if(file_exists($file_path)): 
                                ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-file-word text-primary mr-2" style="font-size: 24px;"></i>
                                        <div>
                                            <strong><?= pathinfo($proposal->file_draft_proposal, PATHINFO_FILENAME) ?></strong>
                                            <br><small class="text-muted"><?= pathinfo($proposal->file_draft_proposal, PATHINFO_EXTENSION) ?> | <?= round(filesize($file_path)/1024, 2) ?> KB</small>
                                        </div>
                                    </div>
                                    
                                    <div class="btn-group" role="group">
                                        <a href="<?= base_url() ?>kaprodi/download_proposal/<?= $proposal->id ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <a href="<?= base_url() ?>kaprodi/view_proposal/<?= $proposal->id ?>" class="btn btn-info btn-sm" target="_blank">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-check-circle"></i> File ditemukan di server
                                        </small>
                                    </div>
                                    
                                <?php else: ?>
                                    <div class="alert alert-danger">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <div>
                                                <strong>File proposal tidak ditemukan di server!</strong><br>
                                                <small>File: <?= $proposal->file_draft_proposal ?></small><br>
                                                <small>Path: cdn/proposals/</small>
                                            </div>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <strong>Kemungkinan penyebab:</strong><br>
                                                • File dihapus dari server<br>
                                                • Nama file berubah<br>
                                                • Path folder salah<br>
                                                • Permission folder salah
                                            </small>
                                        </div>
                                        
                                        <?php if(ENVIRONMENT == 'development'): ?>
                                        <div class="mt-2">
                                            <a href="<?= base_url() ?>kaprodi/debug_files" class="btn btn-warning btn-sm" target="_blank">
                                                <i class="fas fa-bug"></i> Debug File System
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle"></i> Tidak ada file proposal yang diupload
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Form Review</h3>
            </div>
            <div class="card-body">
                <?php if($this->session->flashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= $this->session->flashdata('error') ?>
                </div>
                <?php endif; ?>
                
                <!-- Debug info (hapus setelah testing) -->
                <?php if(ENVIRONMENT == 'development'): ?>
                <div class="alert alert-info">
                    <small>Debug: Jumlah dosen tersedia: <?= isset($dosens) ? count($dosens) : 0 ?></small>
                    <?php if(isset($dosens) && !empty($dosens)): ?>
                        <br><small>Dosen pertama: <?= $dosens[0]->nama ?> (Level: <?= $dosens[0]->level ?>)</small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="<?= base_url() ?>kaprodi/proses_review" id="formReview">
                    <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                    
                    <div class="form-group">
                        <label class="form-control-label">Keputusan Review <span class="text-danger">*</span></label>
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" id="setujui" name="aksi" value="setujui" class="custom-control-input" required>
                            <label class="custom-control-label text-success" for="setujui">
                                <i class="fas fa-check"></i> Setujui Proposal
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="tolak" name="aksi" value="tolak" class="custom-control-input" required>
                            <label class="custom-control-label text-danger" for="tolak">
                                <i class="fas fa-times"></i> Tolak Proposal
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="dosen-group" style="display: none;">
                        <label class="form-control-label">Pilih Dosen Pembimbing <span class="text-danger">*</span></label>
                        <select class="form-control" name="dosen_id" id="dosen_id">
                            <option value="">-- Pilih Dosen Pembimbing --</option>
                            <?php if(isset($dosens) && !empty($dosens)): ?>
                                <?php foreach($dosens as $d): ?>
                                    <option value="<?= $d->id ?>">
                                        <?= $d->nama ?> 
                                        <?php if(ENVIRONMENT == 'development'): ?>
                                            (Level: <?= $d->level ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="">Tidak ada dosen tersedia</option>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">Wajib dipilih jika proposal disetujui. Dosen pembimbing akan dimintai persetujuan.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Komentar/Catatan</label>
                        <textarea class="form-control" name="komentar_kaprodi" rows="4" placeholder="Berikan komentar atau catatan untuk proposal ini..."></textarea>
                        <small class="text-muted">Komentar akan dikirimkan ke mahasiswa melalui email.</small>
                    </div>
                    
                    <div class="text-right">
                        <a href="<?= base_url() ?>kaprodi/proposal" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Kirim Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Info Panel -->
        <div class="card mt-3">
            <div class="card-body bg-gradient-info text-white">
                <h5 class="text-white mb-2">
                    <i class="fas fa-info-circle"></i> Informasi
                </h5>
                <p class="text-white mb-1">
                    <small>• Jika proposal disetujui, dosen pembimbing akan mendapat notifikasi email</small>
                </p>
                <p class="text-white mb-1">
                    <small>• Mahasiswa akan mendapat notifikasi hasil review</small>
                </p>
                <p class="text-white mb-0">
                    <small>• Pastikan pilihan dosen pembimbing sudah tepat</small>
                </p>
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
    // Debug: Log status awal
    console.log('Form Review loaded');
    console.log('Jumlah dosen tersedia: <?= isset($dosens) ? count($dosens) : 0 ?>');
    
    // Show/hide dosen selection based on decision
    $('input[name="aksi"]').on('change', function() {
        console.log('Radio changed to: ' + $(this).val());
        
        if ($(this).val() === 'setujui') {
            $('#dosen-group').slideDown(300);
            $('#dosen_id').prop('required', true);
            console.log('Dosen group shown');
        } else {
            $('#dosen-group').slideUp(300);
            $('#dosen_id').prop('required', false);
            $('#dosen_id').val('');
            console.log('Dosen group hidden');
        }
    });
    
    // Form validation
    $('#formReview').on('submit', function(e) {
        var aksi = $('input[name="aksi"]:checked').val();
        var dosen_id = $('#dosen_id').val();
        
        console.log('Form submitted - Aksi: ' + aksi + ', Dosen ID: ' + dosen_id);
        
        if (!aksi) {
            alert('Silakan pilih keputusan review!');
            e.preventDefault();
            return false;
        }
        
        if (aksi === 'setujui' && !dosen_id) {
            alert('Dosen pembimbing harus dipilih jika proposal disetujui!');
            e.preventDefault();
            return false;
        }
        
        // Konfirmasi sebelum submit
        var konfirmasi = aksi === 'setujui' ? 
            'Yakin ingin menyetujui proposal ini dan menetapkan dosen pembimbing?' : 
            'Yakin ingin menolak proposal ini?';
            
        if (!confirm(konfirmasi)) {
            e.preventDefault();
            return false;
        }
        
        // Show loading
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
    });
    
    // Test manual untuk melihat dropdown
    $('#setujui').on('click', function() {
        console.log('Setujui clicked - forcing dropdown show');
    });
    
    // Force show debug info
    setTimeout(function() {
        console.log('Dropdown element exists:', $('#dosen_id').length);
        console.log('Options count:', $('#dosen_id option').length);
    }, 1000);
});
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Review Detail Proposal',
    'content' => $content,
    'script' => $script
]); 
?>