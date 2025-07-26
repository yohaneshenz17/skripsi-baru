<?php
ob_start();
?>
<style>
    .custom-file-label {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-right: 85px;
    }
    .custom-file-label::after {
        content: "Browse";
    }
    
    /* TAMBAHAN: Style untuk form pengajuan ulang */
    .resubmission-form {
        border: 2px dashed #28a745;
        border-radius: 10px;
        background-color: #f8fff9;
    }
    
    .rejected-proposal-info {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border-radius: 8px;
        color: white;
        margin-bottom: 20px;
    }
    
    .feedback-box {
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        border-left: 4px solid #f39c12;
        border-radius: 5px;
        padding: 15px;
        margin: 15px 0;
    }
</style>
<?php
$styles = ob_get_clean();
ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php endif; ?>

        <?php if(!$proposal): ?>
        <!-- FORM PENGAJUAN PROPOSAL BARU (EXISTING - TIDAK BERUBAH) -->
        <div class="card">
            <div class="card-header"><h3 class="mb-0">Form Pengajuan Usulan Proposal Skripsi</h3></div>
            <div class="card-body">
                <form method="post" action="<?= base_url() ?>mahasiswa/proposal/ajukan" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-control-label">1. Judul Proposal <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="judul" required placeholder="Tuliskan usulan judul proposal Anda secara lengkap" maxlength="250" rows="3"></textarea>
                        <small class="text-muted">Maksimal 250 karakter.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">2. Jenis Penelitian <span class="text-danger">*</span></label>
                        <select name="jenis_penelitian" class="form-control" required>
                            <option value="">- Pilih Jenis Penelitian -</option>
                            <option value="Kuantitatif">Kuantitatif</option>
                            <option value="Kualitatif">Kualitatif</option>
                            <option value="Mixed Method">Mixed Method</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">3. Lokasi Penelitian <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lokasi_penelitian" required placeholder="Tuliskan rencana lokasi penelitian Anda nanti">
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">4. Uraian Masalah Penelitian <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="uraian_masalah" rows="5" required placeholder="Uraikan masalah yang melatarbelakangi pemilihan judul Anda secara singkat"></textarea>
                        <small class="text-muted">Maksimal 200 kata.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">5. Upload File Draft Proposal <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="draft_proposal" id="draft_proposal" required>
                            <label class="custom-file-label" for="draft_proposal">Pilih file...</label>
                        </div>
                        <small class="text-muted">File Word atau PDF, maksimal 500 KB.</small>
                    </div>
                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Ajukan Proposal</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php else: ?>
        
        <!-- STATUS PROPOSAL EXISTING -->
        <?php if ($proposal->status_kaprodi == '2'): ?>
        <!-- ========================================= -->
        <!-- TAMBAHAN BARU: PROPOSAL DITOLAK KAPRODI -->
        <!-- ========================================= -->
        <div class="rejected-proposal-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">
                            <i class="fa fa-exclamation-triangle mr-2"></i>
                            Proposal Memerlukan Perbaikan
                        </h3>
                        <p class="text-white mt-2 mb-0 opacity-8">
                            Proposal Anda telah direview oleh Kaprodi dan perlu diperbaiki sesuai catatan di bawah ini
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-danger rounded-circle shadow">
                            <i class="fa fa-edit"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Proposal yang Ditolak -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">📋 Detail Review Proposal</h3>
            </div>
            <div class="card-body">
                <!-- Info Proposal Lama -->
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="alert-heading mb-1">
                                <i class="fa fa-info-circle"></i> Proposal yang Direview:
                            </h5>
                            <p class="mb-1"><strong>Judul:</strong> <?= $proposal->judul ?></p>
                            <p class="mb-0">
                                <strong>Tanggal Review:</strong> 
                                <?= $proposal->tanggal_review_kaprodi ? date('d F Y', strtotime($proposal->tanggal_review_kaprodi)) : '-' ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-danger badge-lg">Ditolak Kaprodi</span>
                        </div>
                    </div>
                </div>
                
                <!-- Komentar/Feedback dari Kaprodi -->
                <?php if($proposal->komentar_kaprodi): ?>
                <div class="feedback-box">
                    <h5 class="text-warning mb-3">
                        <i class="fa fa-comments"></i> Catatan Perbaikan dari Kaprodi:
                    </h5>
                    <div class="bg-white p-3 rounded border-left-warning">
                        <?= nl2br(htmlspecialchars($proposal->komentar_kaprodi)) ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="feedback-box">
                    <h5 class="text-warning mb-2">
                        <i class="fa fa-info-circle"></i> Informasi:
                    </h5>
                    <p class="mb-0 text-warning">
                        Silakan lakukan perbaikan sesuai panduan penulisan proposal yang berlaku dan konsultasikan dengan dosen pembimbing akademik.
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- Tombol untuk Menampilkan Form Pengajuan Ulang -->
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-success btn-lg" onclick="showResubmissionForm()" id="btn-show-resubmission">
                        <i class="fa fa-redo mr-2"></i> Perbaiki & Ajukan Ulang Proposal
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Pengajuan Ulang (Hidden by default) -->
        <div class="card resubmission-form" id="resubmission-form" style="display: none;">
            <div class="card-header bg-success">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0 text-white">
                            <i class="fa fa-edit mr-2"></i> Form Pengajuan Ulang Proposal
                        </h3>
                        <p class="text-white mt-1 mb-0 opacity-8">Perbaiki proposal sesuai catatan Kaprodi dan ajukan kembali</p>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="hideResubmissionForm()">
                            <i class="fa fa-times"></i> Tutup Form
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="fa fa-lightbulb fa-2x text-warning"></i>
                        </div>
                        <div class="col">
                            <h6 class="alert-heading mb-1">Petunjuk Perbaikan:</h6>
                            <p class="mb-0">
                                • Bacalah catatan Kaprodi dengan teliti<br>
                                • Perbaiki semua bagian yang diminta<br>
                                • Pastikan format penulisan sesuai panduan<br>
                                • Upload file proposal yang sudah diperbaiki
                            </p>
                        </div>
                    </div>
                </div>
                
                <form method="post" action="<?= base_url('mahasiswa/proposal/ajukan_ulang') ?>" enctype="multipart/form-data" id="form-resubmission">
                    <div class="form-group">
                        <label class="form-control-label">1. Judul Proposal (Diperbaiki) <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="judul" required placeholder="Perbaiki judul proposal sesuai saran Kaprodi" maxlength="250" rows="3"><?= htmlspecialchars($proposal->judul) ?></textarea>
                        <small class="text-muted">Maksimal 250 karakter. Sesuaikan dengan catatan perbaikan.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">2. Jenis Penelitian <span class="text-danger">*</span></label>
                        <select name="jenis_penelitian" class="form-control" required>
                            <option value="">- Pilih Jenis Penelitian -</option>
                            <option value="Kuantitatif" <?= $proposal->jenis_penelitian == 'Kuantitatif' ? 'selected' : '' ?>>Kuantitatif</option>
                            <option value="Kualitatif" <?= $proposal->jenis_penelitian == 'Kualitatif' ? 'selected' : '' ?>>Kualitatif</option>
                            <option value="Mixed Method" <?= $proposal->jenis_penelitian == 'Mixed Method' ? 'selected' : '' ?>>Mixed Method</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">3. Lokasi Penelitian <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lokasi_penelitian" required placeholder="Lokasi penelitian yang sudah diperbaiki" value="<?= htmlspecialchars($proposal->lokasi_penelitian) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">4. Uraian Masalah Penelitian (Diperbaiki) <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="uraian_masalah" rows="6" required placeholder="Perbaiki uraian masalah sesuai catatan Kaprodi"><?= htmlspecialchars($proposal->uraian_masalah) ?></textarea>
                        <small class="text-muted">Pastikan sudah sesuai dengan catatan perbaikan dari Kaprodi.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-control-label">5. Upload File Proposal Baru <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="draft_proposal" id="draft_proposal_resubmission" required>
                            <label class="custom-file-label" for="draft_proposal_resubmission">Pilih file proposal yang sudah diperbaiki...</label>
                        </div>
                        <small class="text-muted">
                            File Word atau PDF, maksimal 5MB. <strong>Wajib upload file baru yang sudah diperbaiki.</strong>
                        </small>
                        <?php if($proposal->file_draft_proposal): ?>
                        <div class="mt-2">
                            <small class="text-info">
                                <i class="fa fa-info-circle"></i> 
                                File lama: <a href="<?= base_url('cdn/proposals/' . $proposal->file_draft_proposal) ?>" target="_blank" class="text-info">Lihat file sebelumnya</a>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary btn-block" onclick="hideResubmissionForm()">
                                <i class="fa fa-times mr-2"></i> Batal
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fa fa-paper-plane mr-2"></i> Ajukan Ulang Proposal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- ============================================= -->
        <!-- STATUS PROPOSAL EXISTING (TIDAK BERUBAH) -->
        <!-- ============================================= -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col"><h3 class="mb-0">Status Proposal Anda</h3></div>
                    <div class="col text-right">
                        <?php
                        $status_text = '';
                        $status_class = '';
                    
                        // Logika untuk menentukan teks dan warna status
                        if ($proposal->status_kaprodi == '0') {
                            $status_text = 'Menunggu Penetapan Kaprodi';
                            $status_class = 'badge-warning';
                        } elseif ($proposal->status_kaprodi == '1' && $proposal->status_pembimbing == '0') {
                            $status_text = 'Menunggu Persetujuan Dosen Pembimbing';
                            $status_class = 'badge-info';
                        } elseif ($proposal->status_kaprodi == '1' && $proposal->status_pembimbing == '1') {
                            $status_text = 'Proposal Disetujui';
                            $status_class = 'badge-success';
                        } elseif ($proposal->status_kaprodi == '1' && $proposal->status_pembimbing == '2') {
                            $status_text = 'Pembimbing Menolak, Menunggu Penetapan Ulang';
                            $status_class = 'badge-danger';
                        }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h4 class="font-weight-bold">Judul Proposal:</h4>
                <p class="text-primary"><?= $proposal->judul ?></p>
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="font-weight-bold">Jenis Penelitian:</h4>
                        <p><?= $proposal->jenis_penelitian ?: '-' ?></p>
                    </div>
                    <div class="col-md-6">
                        <h4 class="font-weight-bold">Lokasi Penelitian:</h4>
                        <p><?= $proposal->lokasi_penelitian ?: '-' ?></p>
                    </div>
                </div>
                <h4 class="font-weight-bold">Uraian Masalah:</h4>
                <div class="alert alert-secondary p-3" style="white-space: pre-wrap;"><?= $proposal->uraian_masalah ?: '-' ?></div>
                <?php if($proposal->file_draft_proposal): ?>
                    <a href="<?= base_url('cdn/proposals/' . $proposal->file_draft_proposal) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file-alt"></i> Lihat Draft Proposal</a>
                <?php endif; ?>
                <hr>
                <?php if($proposal->dosen_id): ?>
                <h4 class="font-weight-bold">Tim Dosen Anda:</h4>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h5>Dosen Pembimbing:</h5>
                        <p class="text-success"><i class="fa fa-user-tie"></i> <?= $proposal->nama_pembimbing ?: 'Belum ditetapkan' ?></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="fa fa-hourglass-half"></i> <strong>Menunggu Persetujuan:</strong> Proposal Anda sedang ditinjau oleh Ketua Program Studi.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
ob_start(); 
?>
<script>
// JavaScript existing (tidak berubah)
$('.custom-file-input').on('change', function() {
   let fileName = $(this).val().split('\\').pop();
   $(this).next('.custom-file-label').addClass("selected").html(fileName);
});

// TAMBAHAN BARU: JavaScript untuk form pengajuan ulang
function showResubmissionForm() {
    document.getElementById('resubmission-form').style.display = 'block';
    document.getElementById('btn-show-resubmission').style.display = 'none';
    
    // Scroll to form
    document.getElementById('resubmission-form').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

function hideResubmissionForm() {
    document.getElementById('resubmission-form').style.display = 'none';
    document.getElementById('btn-show-resubmission').style.display = 'inline-block';
}

// Handle file input untuk form pengajuan ulang
$(document).ready(function() {
    $('#draft_proposal_resubmission').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
    
    // Form validation untuk pengajuan ulang
    $('#form-resubmission').on('submit', function(e) {
        let fileInput = $('#draft_proposal_resubmission')[0];
        if (!fileInput.files || fileInput.files.length === 0) {
            e.preventDefault();
            alert('Harap upload file proposal yang sudah diperbaiki!');
            return false;
        }
        
        // Confirm submission
        if (!confirm('Yakin ingin mengajukan ulang proposal ini? Pastikan semua perbaikan sudah sesuai catatan Kaprodi.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
<?php 
$script = ob_get_clean(); 

$this->load->view('template/mahasiswa', [
    'content' => $content, 
    'styles' => isset($styles) ? $styles : '', 
    'script' => $script
]); 
?>