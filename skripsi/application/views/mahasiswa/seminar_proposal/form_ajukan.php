<?php
/**
 * Form Pengajuan Seminar Proposal - Mahasiswa (FIXED VERSION)
 * File: application/views/mahasiswa/seminar_proposal/form_ajukan.php
 * 
 * Form untuk mengajukan atau mengedit pengajuan seminar proposal
 * PERBAIKAN: Menambahkan form tag yang hilang dengan action yang benar
 */
?>

<style>
    /* Form Styles */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        color: #32325d;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        transition: all 0.15s ease;
        background: white;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #5e72e4;
        box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.15);
    }
    
    .form-control.is-invalid {
        border-color: #f5365c;
    }
    
    .form-text {
        font-size: 0.75rem;
        color: #8898aa;
        margin-top: 0.25rem;
    }
    
    .invalid-feedback {
        font-size: 0.75rem;
        color: #f5365c;
        margin-top: 0.25rem;
    }
    
    /* File Upload */
    .file-upload {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .file-upload input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-upload-label {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        border: 2px dashed #e3e6f0;
        border-radius: 0.375rem;
        background: #f8f9fe;
        cursor: pointer;
        transition: all 0.15s ease;
        text-align: center;
    }
    
    .file-upload-label:hover {
        border-color: #5e72e4;
        background: #f0f3ff;
    }
    
    .file-upload.has-file .file-upload-label {
        border-color: #28a745;
        background: #f8fff9;
    }
    
    .file-info {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: #f8f9fa;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
    
    /* Progress Bar */
    .progress-container {
        background: #f8f9fe;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .progress-title {
        font-weight: 600;
        color: #32325d;
        margin-bottom: 0.5rem;
    }
    
    .progress {
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-bar {
        background: linear-gradient(90deg, #11cdef 0%, #5e72e4 100%);
        transition: width 0.6s ease;
    }
    
    /* Cards */
    .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
    }
    
    .card-header {
        background: #f8f9fe;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.25rem;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    /* Buttons */
    .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.375rem;
        transition: all 0.15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid transparent;
        cursor: pointer;
    }
    
    .btn-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        color: white;
        border-color: #5e72e4;
    }
    
    .btn-primary:hover {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        transform: translateY(-1px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        border-color: #545b62;
        transform: translateY(-1px);
    }
    
    /* Alert Styles */
    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.375rem;
    }
    
    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }
    
    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeaa7;
    }
</style>

<!-- Progress Workflow -->
<div class="progress-container">
    <div class="progress-title">
        <i class="fas fa-tasks" style="margin-right: 0.5rem; color: #5e72e4;"></i>
        Progress Pengajuan Seminar Proposal
    </div>
    <div class="progress">
        <div class="progress-bar" style="width: 60%"></div>
    </div>
    <small style="color: #8898aa; margin-top: 0.5rem; display: block;">
        Phase 3: Mengajukan Seminar Proposal (60% dari keseluruhan workflow)
    </small>
</div>

<!-- ============================================== -->
<!-- FORM TAG YANG BENAR - INI YANG HILANG SEBELUMNYA -->
<!-- ============================================== -->
<form action="<?php echo base_url('mahasiswa/seminar_proposal/submit_ajukan'); ?>" 
      method="post" 
      enctype="multipart/form-data" 
      id="form-seminar"
      novalidate>

    <!-- Hidden Fields -->
    <input type="hidden" name="proposal_id" value="<?php echo isset($proposal) ? $proposal->id : ''; ?>">
    <input type="hidden" name="is_edit" value="<?php echo isset($is_edit) && $is_edit ? '1' : '0'; ?>">

    <!-- Flash Messages -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Informasi Proposal -->
    <div class="card">
        <div class="card-header">
            <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                <i class="fas fa-info-circle" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                Informasi Proposal
            </h6>
        </div>
        <div class="card-body">
            <?php if (isset($proposal)): ?>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID Proposal:</strong> #<?php echo $proposal->id; ?></p>
                        <p><strong>Judul:</strong> <?php echo htmlspecialchars($proposal->judul); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Dosen Pembimbing:</strong> <?php echo isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : 'Belum ditetapkan'; ?></p>
                        <p><strong>Status Proposal:</strong> 
                            <span class="badge badge-success">Disetujui untuk Seminar</span>
                        </p>
                    </div>
                </div>

                <!-- Syarat Jurnal Bimbingan -->
                <?php if (isset($syarat_jurnal)): ?>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-check-circle"></i> Syarat Jurnal Bimbingan</h6>
                        <p style="margin: 0;">
                            ✅ Jurnal bimbingan telah memenuhi syarat: 
                            <strong><?php echo $syarat_jurnal['total_validated']; ?> dari <?php echo $syarat_jurnal['minimum_required']; ?> pertemuan minimum</strong>
                        </p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Informasi proposal tidak tersedia.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 🆕 TAMBAHAN BARU: FIELD EDIT JUDUL SEMINAR -->
    <div class="card">
        <div class="card-header">
            <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                <i class="fas fa-edit" style="margin-right: 0.5rem; color: #28a745;"></i>
                Judul untuk Seminar Proposal
            </h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Catatan:</strong> Anda dapat mengubah judul jika selama bimbingan ada perbaikan formulasi. 
                Judul ini yang akan digunakan untuk undangan seminar, berita acara, dan dokumen resmi lainnya.
            </div>
            
            <!-- Field Judul Original untuk Referensi -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-history"></i> Judul Proposal Original
                </label>
                <div class="form-control" style="background-color: #f8f9fe; border: 1px dashed #e3e6f0;">
                    <?php echo htmlspecialchars($proposal->judul); ?>
                </div>
                <div class="form-text">
                    Judul dari usulan proposal awal yang disetujui Kaprodi.
                </div>
            </div>
            
            <!-- Field Edit Judul Seminar -->
            <div class="form-group">
                <label for="judul_seminar" class="form-label">
                    <i class="fas fa-edit"></i> Judul Proposal untuk Seminar <span style="color: #f5365c;">*</span>
                </label>
                
                <textarea name="judul_seminar" id="judul_seminar" class="form-control" 
                          rows="4" maxlength="250" required 
                          placeholder="Masukkan judul proposal yang akan digunakan untuk seminar..."><?php echo htmlspecialchars($current_judul); ?></textarea>
                
                <div class="form-text">
                    <div class="d-flex justify-content-between">
                        <span>Maksimal 250 karakter. Pastikan judul sudah sesuai hasil bimbingan.</span>
                        <span id="char-counter" class="text-muted">0/250</span>
                    </div>
                </div>
            </div>
            
            <!-- Peringatan jika ada perubahan judul -->
            <?php if (isset($existing_seminar) && $existing_seminar && 
                      isset($existing_seminar->judul_seminar) && 
                      $existing_seminar->judul_seminar != $proposal->judul): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Perubahan Terdeteksi:</strong> Judul seminar berbeda dari usulan awal.
                <br><small>Pastikan perubahan ini sudah didiskusikan dengan dosen pembimbing.</small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload File Proposal -->
    <div class="card">
        <div class="card-header">
            <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                <i class="fas fa-file-upload" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                Upload File Proposal
            </h6>
        </div>
        <div class="card-body">
            
            <?php if (isset($is_edit) && $is_edit && isset($existing_seminar) && $existing_seminar->file_proposal): ?>
                <div class="alert alert-info">
                    <strong>File saat ini:</strong> 
                    <a href="<?php echo base_url('uploads/seminar_proposal/proposal_files/' . $existing_seminar->file_proposal); ?>" 
                       target="_blank" style="color: #0c5460;">
                        <?php echo $existing_seminar->file_proposal; ?>
                    </a>
                    <br><small>Kosongkan jika tidak ingin mengubah file.</small>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">
                    File Proposal Seminar 
                    <?php if (!isset($is_edit) || !$is_edit): ?>
                        <span style="color: #f5365c;">*</span>
                    <?php endif; ?>
                </label>
                
                <div class="file-upload" id="fileUpload">
                    <input type="file" name="file_proposal" id="fileProposal" accept=".pdf,.doc,.docx">
                    <div class="file-upload-label">
                        <div>
                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #8898aa; margin-bottom: 0.5rem;"></i>
                            <p style="margin: 0; color: #8898aa; font-weight: 600;">
                                Klik untuk upload file atau drag & drop
                            </p>
                            <small style="color: #8898aa;">
                                Format: PDF, DOC, DOCX | Maksimal: 1MB
                            </small>
                        </div>
                    </div>
                </div>
                
                <div id="fileInfo" class="file-info" style="display: none;"></div>
                
                <div class="form-text">
                    Upload file proposal yang akan dipresentasikan dalam seminar.
                    File harus berisi proposal yang sudah diperbaiki sesuai saran pembimbing.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Keterangan Pengajuan -->
    <div class="card">
        <div class="card-header">
            <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                <i class="fas fa-edit" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                Keterangan Pengajuan
            </h6>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="keterangan" class="form-label">
                    Keterangan Tambahan <span style="color: #f5365c;">*</span>
                </label>
                
                <textarea name="keterangan_mahasiswa" id="keterangan" class="form-control" 
                          rows="6" placeholder="Jelaskan kesiapan Anda untuk seminar proposal..."><?php echo (isset($existing_seminar) && $existing_seminar) ? $existing_seminar->keterangan_mahasiswa : ''; ?></textarea>
                
                <div class="form-text">
                    Minimal 10 karakter. Jelaskan kesiapan Anda mengikuti seminar proposal, 
                    poin-poin penting yang akan dipresentasikan, atau hal lain yang perlu disampaikan.
                </div>
            </div>
        </div>
    </div>
    
    <!-- Submit Actions -->
    <div class="card">
        <div class="card-body">
            <div style="display: flex; gap: 1rem; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div>
                    <small style="color: #8898aa;">
                        <i class="fas fa-info-circle"></i>
                        Pastikan semua data sudah benar sebelum mengajukan.
                    </small>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <a href="<?php echo base_url('mahasiswa/seminar_proposal'); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Batal
                    </a>
                    
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo (isset($is_edit) && $is_edit) ? 'Update Pengajuan' : 'Ajukan Seminar Proposal'; ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
<!-- END FORM -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload handling
    const fileInput = document.getElementById('fileProposal');
    const fileUpload = document.getElementById('fileUpload');
    const fileInfo = document.getElementById('fileInfo');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                // Validate file size (1MB = 1048576 bytes)
                if (file.size > 1048576) {
                    alert('Ukuran file terlalu besar. Maksimal 1MB.');
                    fileInput.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan PDF, DOC, atau DOCX.');
                    fileInput.value = '';
                    return;
                }
                
                // Show file info
                fileUpload.classList.add('has-file');
                fileInfo.innerHTML = `
                    <i class="fas fa-file"></i>
                    <strong>${file.name}</strong> 
                    <span style="color: #8898aa;">(${(file.size / 1024).toFixed(1)} KB)</span>
                `;
                fileInfo.style.display = 'block';
            } else {
                fileUpload.classList.remove('has-file');
                fileInfo.style.display = 'none';
            }
        });
    }
    
    // Form validation
    const form = document.getElementById('form-seminar');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate keterangan
            const keterangan = document.getElementById('keterangan');
            if (keterangan && keterangan.value.trim().length < 10) {
                isValid = false;
                keterangan.classList.add('is-invalid');
                alert('Keterangan minimal 10 karakter.');
            } else if (keterangan) {
                keterangan.classList.remove('is-invalid');
            }
            
            // Validate file upload for new submission
            const isEdit = document.querySelector('input[name="is_edit"]').value === '1';
            if (!isEdit && (!fileInput.files || fileInput.files.length === 0)) {
                isValid = false;
                alert('File proposal harus diupload.');
            }
            
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Confirm submission
            const action = isEdit ? 'memperbarui' : 'mengirim';
            if (!confirm(`Yakin ingin ${action} pengajuan seminar proposal? Pastikan semua data sudah benar.`)) {
                e.preventDefault();
                return false;
            }
            
            // Disable submit button to prevent double submission
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        });
    }
    
    // Character counter for keterangan
    const keterangan = document.getElementById('keterangan');
    if (keterangan) {
        keterangan.addEventListener('input', function() {
            const length = this.value.length;
            const minLength = 10;
            
            if (length < minLength) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
});
</script>