<?php
/**
 * Form Pengajuan Seminar Proposal - Mahasiswa
 * File: application/views/mahasiswa/seminar_proposal/form_ajukan.php
 * 
 * Form untuk mengajukan atau mengedit pengajuan seminar proposal
 * Menggunakan template mahasiswa_simple.php dengan CSS inline
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
        background: rgba(94, 114, 228, 0.05);
    }
    
    .file-upload.has-file .file-upload-label {
        border-color: #2dce89;
        background: rgba(45, 206, 137, 0.05);
    }
    
    .file-info {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: rgba(94, 114, 228, 0.05);
        border-radius: 0.25rem;
        font-size: 0.875rem;
        color: #5e72e4;
    }
    
    /* Card Styles (reused from dashboard) */
    .card {
        background: white;
        border-radius: 0.375rem;
        box-shadow: 0 0 2rem 0 rgba(136, 152, 170, 0.15);
        border: none;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.5rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Alert */
    .alert {
        padding: 1rem 1.25rem;
        border-radius: 0.375rem;
        border: 1px solid transparent;
        margin-bottom: 1rem;
    }
    
    .alert-success {
        background: rgba(45, 206, 137, 0.1);
        color: #155724;
        border-color: rgba(45, 206, 137, 0.2);
    }
    
    .alert-warning {
        background: rgba(251, 99, 64, 0.1);
        color: #8a2b06;
        border-color: rgba(251, 99, 64, 0.2);
    }
    
    .alert-info {
        background: rgba(17, 205, 239, 0.1);
        color: #0c5460;
        border-color: rgba(17, 205, 239, 0.2);
    }
    
    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.375rem;
        border: 1px solid transparent;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s ease;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
        color: white;
        border-color: #5e72e4;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        color: white;
        text-decoration: none;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    
    .btn-outline-primary {
        background: transparent;
        color: #5e72e4;
        border-color: #5e72e4;
    }
    
    .btn-outline-primary:hover {
        background: #5e72e4;
        color: white;
        text-decoration: none;
    }
    
    /* Jurnal List */
    .jurnal-item {
        padding: 1rem;
        border: 1px solid #e3e6f0;
        border-radius: 0.375rem;
        margin-bottom: 0.75rem;
        background: white;
    }
    
    .jurnal-item.validated {
        border-color: #2dce89;
        background: rgba(45, 206, 137, 0.05);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        
        .form-control {
            padding: 0.625rem 0.875rem;
        }
        
        .file-upload-label {
            padding: 1.5rem 1rem;
        }
    }
</style>

<div style="margin-top: -3rem; position: relative; z-index: 10;">
    <div style="max-width: 900px; margin: 0 auto; padding: 0 1.5rem;">
        
        <!-- Form Header -->
        <div class="card">
            <div class="card-header">
                <h4 style="margin: 0; font-weight: 600; color: #32325d;">
                    <i class="fas fa-paper-plane" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                    <?php echo $is_edit ? 'Edit Pengajuan' : 'Ajukan'; ?> Seminar Proposal
                </h4>
                <p style="margin: 0.5rem 0 0 0; color: #8898aa; font-size: 0.875rem;">
                    <?php echo $proposal->judul; ?>
                </p>
            </div>
        </div>
        
        <!-- Breadcrumb -->
        <nav style="margin-bottom: 1.5rem;">
            <a href="<?php echo base_url('mahasiswa/seminar_proposal'); ?>" 
               style="color: #5e72e4; text-decoration: none; font-size: 0.875rem;">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </nav>
        
        <?php if (!$can_edit): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                <strong>Pengajuan sedang diproses.</strong> Anda tidak dapat mengedit pengajuan yang sedang dalam tahap review.
            </div>
        <?php endif; ?>
        
        <?php if ($can_edit): ?>
            
            <!-- Syarat Jurnal Bimbingan -->
            <div class="card">
                <div class="card-header">
                    <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem; color: #2dce89;"></i>
                        Syarat Jurnal Bimbingan Terpenuhi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <strong>✓ Syarat Terpenuhi!</strong><br>
                        Anda memiliki <?php echo $syarat_jurnal['jurnal_validated_count']; ?> jurnal bimbingan yang telah divalidasi dosen pembimbing.
                    </div>
                    
                    <?php if (!empty($jurnal_validasi)): ?>
                        <h6 style="margin-bottom: 1rem; font-weight: 600; color: #32325d;">Daftar Jurnal Tervalidasi:</h6>
                        <?php foreach ($jurnal_validasi as $jurnal): ?>
                            <div class="jurnal-item validated">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong>Pertemuan ke-<?php echo $jurnal->pertemuan_ke; ?></strong><br>
                                        <small style="color: #8898aa;">
                                            <?php echo date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)); ?>
                                            - Validasi: <?php echo $jurnal->nama_validator ?: 'Dosen Pembimbing'; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <span style="background: #2dce89; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                            ✓ Tervalidasi
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Form Pengajuan -->
            <?php echo form_open_multipart('mahasiswa/seminar_proposal/submit_ajukan', ['id' => 'form-seminar', 'novalidate' => 'novalidate']); ?>
                
                <input type="hidden" name="proposal_id" value="<?php echo $proposal->id; ?>">
                <input type="hidden" name="is_edit" value="<?php echo $is_edit ? '1' : '0'; ?>">
                
                <div class="card">
                    <div class="card-header">
                        <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                            <i class="fas fa-file-upload" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                            Upload File Proposal
                        </h6>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($is_edit && $existing_seminar->file_proposal): ?>
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
                                <?php if (!$is_edit): ?>
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
                                      rows="6" placeholder="Jelaskan kesiapan Anda untuk seminar proposal..."><?php echo $existing_seminar ? $existing_seminar->keterangan_mahasiswa : ''; ?></textarea>
                            
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
                                    <?php echo $is_edit ? 'Update Pengajuan' : 'Kirim Pengajuan'; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php echo form_close(); ?>
            
        <?php else: ?>
            
            <!-- Read Only Display -->
            <div class="card">
                <div class="card-header">
                    <h6 style="margin: 0; font-weight: 600; color: #32325d;">
                        <i class="fas fa-eye" style="margin-right: 0.5rem; color: #5e72e4;"></i>
                        Detail Pengajuan (Read Only)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($existing_seminar): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <strong>File Proposal:</strong><br>
                            <?php if ($existing_seminar->file_proposal): ?>
                                <a href="<?php echo base_url('uploads/seminar_proposal/proposal_files/' . $existing_seminar->file_proposal); ?>" 
                                   target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-download"></i>
                                    Download File
                                </a>
                            <?php else: ?>
                                <span style="color: #8898aa;">Tidak ada file</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <strong>Keterangan:</strong><br>
                            <div style="background: #f8f9fe; padding: 1rem; border-radius: 0.375rem; margin-top: 0.5rem;">
                                <?php echo nl2br(htmlspecialchars($existing_seminar->keterangan_mahasiswa)); ?>
                            </div>
                        </div>
                        
                        <div>
                            <strong>Status:</strong> 
                            <span style="background: #5e72e4; color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                <?php echo ucfirst(str_replace('_', ' ', $existing_seminar->status)); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload handling
    const fileInput = document.getElementById('fileProposal');
    const fileUpload = document.getElementById('fileUpload');
    const fileInfo = document.getElementById('fileInfo');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
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