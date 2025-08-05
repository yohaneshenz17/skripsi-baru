<!-- 
FORM PENGAJUAN PUBLIKASI - STEP 2 WORKFLOW (10 FIELD) - WORKFLOW FIXED
File: application/views/mahasiswa/publikasi/form.php

✅ PERBAIKAN WORKFLOW STEP 2→3:
- Tambah tombol dengan submit_type untuk handling yang benar
- Tombol "Kirim Ajuan ke Dosen" untuk langsung ke Step 3
- Tombol "Simpan sebagai Draft" untuk tetap di Step 2
- Logika tombol berbeda untuk mode ajukan vs edit

SESUAI WORKFLOW STEP 2 DENGAN 10 FIELD:
1. Nama Lengkap (auto)
2. NIM (auto) 
3. Program Studi (auto)
4. Judul Skripsi Final (manual)
5. Dosen Pembimbing (auto)
6. Tanggal Ujian Skripsi (auto)
7. Surat Perpustakaan (upload)
8. Surat Revisi (upload) - BARU
9. File Skripsi Final (upload)
10. Link Repository (opsional)
-->

<!-- Flash Messages -->
<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-ban"></i> <?= $this->session->flashdata('error') ?>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="icon fas fa-check"></i> <?= $this->session->flashdata('success') ?>
</div>
<?php endif; ?>

<!-- Step Indicator -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-warning card-outline">
            <div class="card-body text-center">
                <h5>
                    <i class="fas fa-edit text-warning"></i>
                    <strong>STEP 2 dari 9 Langkah Workflow:</strong> Isi Form Pengajuan Publikasi
                </h5>
                <p class="mb-0 text-muted">Lengkapi 10 field data yang diperlukan untuk publikasi tugas akhir Anda</p>
            </div>
        </div>
    </div>
</div>

<!-- Form Card -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-upload"></i> 
            <?= $action == 'ajukan' ? 'Form Pengajuan' : 'Edit' ?> Publikasi Tugas Akhir
        </h3>
        <div class="card-tools">
            <span class="badge badge-warning">Step 2/9</span>
        </div>
    </div>
    
    <?php 
    $form_action = $action == 'ajukan' ? 
        base_url('mahasiswa/publikasi/ajukan/' . $proposal->id) : 
        base_url('mahasiswa/publikasi/edit/' . $publikasi->id);
    ?>
    
    <form action="<?= $form_action ?>" method="POST" enctype="multipart/form-data" id="formPublikasi">
        <div class="card-body">
            
            <!-- Informasi Workflow -->
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Informasi Workflow Step 2</h6>
                <p class="mb-2">Anda sedang mengisi form publikasi dengan 10 field data yang diperlukan. Field 1-3, 5-6 akan terisi otomatis dari profil dan data proposal Anda.</p>
                <p class="mb-0"><strong>Yang perlu Anda isi manual:</strong> Field 4 (Judul Final), Field 7-9 (Upload File), Field 10 (Link Repository)</p>
            </div>

            <!-- FIELD 1-3: Data Mahasiswa (Auto-filled) -->
            <h5 class="mb-3"><i class="fas fa-user text-primary"></i> Data Mahasiswa (Auto-filled)</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>1. Nama Lengkap</strong> <span class="badge badge-secondary">Auto</span></label>
                        <input type="text" class="form-control bg-light" value="<?= $this->session->userdata('nama') ?>" readonly>
                        <small class="text-muted">Data diambil dari profil mahasiswa</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>2. NIM (Nomor Induk Mahasiswa)</strong> <span class="badge badge-secondary">Auto</span></label>
                        <input type="text" class="form-control bg-light" value="<?= $this->session->userdata('nim') ?? 'Belum diset' ?>" readonly>
                        <small class="text-muted">Data diambil dari profil mahasiswa</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><strong>3. Program Studi</strong> <span class="badge badge-secondary">Auto</span></label>
                <input type="text" class="form-control bg-light" value="<?= isset($proposal->nama_prodi) ? $proposal->nama_prodi : 'Belum diset' ?>" readonly>
                <small class="text-muted">Data diambil dari profil mahasiswa</small>
            </div>

            <hr>

            <!-- FIELD 4: Judul Skripsi Final (Manual Input) -->
            <h5 class="mb-3"><i class="fas fa-pen text-warning"></i> Data yang Perlu Diisi Manual</h5>
            
            <div class="form-group">
                <label><strong>4. Judul Skripsi Final</strong> <span class="text-danger">*</span> <span class="badge badge-warning">Manual Input</span></label>
                <textarea class="form-control" name="judul_skripsi_final" rows="3" required 
                          placeholder="Masukkan judul skripsi final (bisa sama atau berbeda dengan judul proposal awal)"><?= isset($publikasi->judul_skripsi_final) ? $publikasi->judul_skripsi_final : (isset($proposal->judul) ? $proposal->judul : '') ?></textarea>
                <small class="text-muted">
                    <strong>Judul proposal awal:</strong> "<?= isset($proposal->judul) ? $proposal->judul : 'Tidak tersedia' ?>"<br>
                    Jika ada perubahan dari judul awal, silakan masukkan judul yang final
                </small>
            </div>

            <hr>

            <!-- FIELD 5-6: Data Proposal & Jadwal (Auto-filled) -->
            <h5 class="mb-3"><i class="fas fa-calendar text-success"></i> Data Pembimbing & Jadwal (Auto-filled)</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>5. Dosen Pembimbing</strong> <span class="badge badge-secondary">Auto</span></label>
                        <input type="text" class="form-control bg-light" value="<?= isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : 'Belum ditetapkan' ?>" readonly>
                        <small class="text-muted">Data diambil dari penetapan pembimbing</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>6. Tanggal Ujian Skripsi</strong> <span class="badge badge-secondary">Auto</span></label>
                        <input type="date" class="form-control bg-light" name="tanggal_ujian_skripsi" 
                               value="<?= isset($publikasi->tanggal_ujian_skripsi) ? $publikasi->tanggal_ujian_skripsi : (isset($proposal->tanggal_seminar_skripsi) ? $proposal->tanggal_seminar_skripsi : date('Y-m-d')) ?>" readonly>
                        <small class="text-muted">Data diambil dari penjadwalan seminar skripsi</small>
                    </div>
                </div>
            </div>

            <hr>

            <!-- FIELD 7-9: Upload Dokumen (Manual) -->
            <h5 class="mb-3"><i class="fas fa-upload text-danger"></i> Upload Dokumen Wajib</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>7. Surat Keterangan Penyerahan Skripsi dari Perpustakaan</strong> <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" name="file_surat_perpustakaan" accept=".pdf" 
                               <?= $action == 'ajukan' ? 'required' : '' ?>>
                        <small class="text-muted">
                            Format PDF, maksimal 1 MB. 
                            <a href="<?= base_url('assets/templates/template_surat_perpustakaan.pdf') ?>" target="_blank" class="text-info">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </small>
                        
                        <?php if (isset($publikasi->file_surat_perpustakaan) && $publikasi->file_surat_perpustakaan): ?>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-check"></i> File sudah diupload: 
                                    <a href="<?= base_url('uploads/publikasi/surat_perpustakaan/' . $publikasi->file_surat_perpustakaan) ?>" target="_blank" class="text-success">
                                        <i class="fas fa-file-pdf"></i> Lihat File
                                    </a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>8. Surat Keterangan Revisi Skripsi</strong> <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" name="file_surat_revisi" accept=".pdf" 
                               <?= $action == 'ajukan' ? 'required' : '' ?>>
                        <small class="text-muted">
                            Format PDF, maksimal 1 MB. 
                            <a href="<?= base_url('assets/templates/template_surat_revisi.pdf') ?>" target="_blank" class="text-info">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </small>
                        
                        <?php if (isset($publikasi->file_surat_revisi) && $publikasi->file_surat_revisi): ?>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-check"></i> File sudah diupload: 
                                    <a href="<?= base_url('uploads/publikasi/surat_revisi/' . $publikasi->file_surat_revisi) ?>" target="_blank" class="text-success">
                                        <i class="fas fa-file-pdf"></i> Lihat File
                                    </a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Field 9: File Skripsi Final (Row baru) -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label><strong>9. File Skripsi Final (Full PDF)</strong> <span class="text-danger">*</span></label>
                        <input type="file" class="form-control-file" name="file_skripsi_final" accept=".pdf" 
                               <?= $action == 'ajukan' ? 'required' : '' ?>>
                        <small class="text-muted">Format PDF, maksimal 5 MB. File skripsi lengkap dan final</small>
                        
                        <?php if (isset($publikasi->file_skripsi_final) && $publikasi->file_skripsi_final): ?>
                            <div class="mt-2">
                                <small class="text-success">
                                    <i class="fas fa-check"></i> File sudah diupload: 
                                    <a href="<?= base_url('uploads/publikasi/skripsi_final/' . $publikasi->file_skripsi_final) ?>" target="_blank" class="text-success">
                                        <i class="fas fa-file-pdf"></i> Lihat File
                                    </a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <hr>

            <!-- FIELD 10: Link Repository (Opsional) -->
            <h5 class="mb-3"><i class="fas fa-link text-info"></i> Repository Online (Opsional)</h5>
            
            <div class="form-group">
                <label><strong>10. Link Repository/Publikasi Tugas Akhir</strong> <span class="badge badge-info">Opsional</span></label>
                <input type="url" class="form-control" name="link_repository" 
                       value="<?= isset($publikasi->link_repository) ? $publikasi->link_repository : '' ?>"
                       placeholder="https://repository.stkyakobus.ac.id/skripsi/...">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> 
                    Link repository online jika sudah tersedia. Field ini bisa dikosongi dan akan diisi oleh staf pada Step 7.
                    <br>
                    Contoh: https://repository.stkyakobus.ac.id/skripsi/2025/nama-mahasiswa
                </small>
            </div>

            <!-- Keterangan Tambahan -->
            <hr>
            <h5 class="mb-3"><i class="fas fa-comment text-secondary"></i> Informasi Tambahan</h5>
            
            <div class="form-group">
                <label>Keterangan/Catatan Tambahan</label>
                <textarea class="form-control" name="keterangan_mahasiswa" rows="3" 
                          placeholder="Keterangan atau catatan tambahan yang ingin disampaikan (opsional)"><?= isset($publikasi->keterangan_mahasiswa) ? $publikasi->keterangan_mahasiswa : '' ?></textarea>
            </div>

        </div>
        
        <!-- ✅ PERBAIKAN UTAMA: Card Footer dengan Tombol yang Benar -->
        <div class="card-footer">
            <div class="row">
                <div class="col-md-8">
                    <?php if ($action == 'ajukan'): ?>
                        <!-- TOMBOL UNTUK PENGAJUAN BARU -->
                        <button type="submit" name="submit_type" value="submit" class="btn btn-success btn-lg mr-2">
                            <i class="fas fa-paper-plane"></i> 
                            Kirim Ajuan ke Dosen (Step 3)
                        </button>
                        <button type="submit" name="submit_type" value="draft" class="btn btn-warning btn-lg">
                            <i class="fas fa-save"></i> 
                            Simpan sebagai Draft
                        </button>
                    <?php else: ?>
                        <!-- TOMBOL UNTUK EDIT -->
                        <button type="submit" name="submit_type" value="update" class="btn btn-primary btn-lg mr-2">
                            <i class="fas fa-save"></i> 
                            Update Data Publikasi
                        </button>
                        <?php if (isset($publikasi) && $publikasi->status == 'draft'): ?>
                        <button type="submit" name="submit_type" value="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-paper-plane"></i> 
                            Kirim Ajuan ke Dosen
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-right">
                    <a href="<?= base_url('mahasiswa/publikasi') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
            
            <!-- Informasi Step -->
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i>
                    <?php if ($action == 'ajukan'): ?>
                        <strong>Petunjuk:</strong> Pilih "Simpan sebagai Draft" untuk menyimpan sementara (Step 2), atau "Kirim Ajuan ke Dosen" untuk langsung melanjutkan ke Step 3.
                    <?php else: ?>
                        <strong>Status:</strong> <?= ucfirst($publikasi->status ?? 'draft') ?> - 
                        <?php if (isset($publikasi) && $publikasi->status == 'draft'): ?>
                            Anda bisa mengedit data atau langsung kirim ajuan ke dosen.
                        <?php else: ?>
                            Data hanya bisa diupdate, tidak bisa diajukan ulang.
                        <?php endif; ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </form>
</div>

<!-- Help Card -->
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-question-circle"></i>
            Bantuan Pengisian Form
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-file-upload text-primary"></i> Tips Upload File:</h6>
                <ul class="list-unstyled">
                    <li>• Pastikan file dalam format PDF</li>
                    <li>• Surat perpustakaan & revisi max 1 MB</li>
                    <li>• File skripsi final max 5 MB</li>
                    <li>• Gunakan nama file yang jelas</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-route text-success"></i> Alur Setelah Step 2:</h6>
                <ul class="list-unstyled">
                    <li>• Step 3: Kirim ajuan ke dosen</li>
                    <li>• Step 4-6: Review dosen pembimbing</li>
                    <li>• Step 7: Validasi staf</li>
                    <li>• Step 8-9: Selesai & download surat</li>
                </ul>
            </div>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-12">
                <h6><i class="fas fa-info-circle text-info"></i> Informasi File yang Dibutuhkan:</h6>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Surat Perpustakaan:</strong><br>
                        <small class="text-muted">Surat keterangan bahwa skripsi sudah diserahkan ke perpustakaan STK Santo Yakobus</small>
                    </div>
                    <div class="col-md-4">
                        <strong>Surat Revisi:</strong><br>
                        <small class="text-muted">Surat keterangan bahwa revisi skripsi sesuai hasil ujian sudah selesai</small>
                    </div>
                    <div class="col-md-4">
                        <strong>File Skripsi Final:</strong><br>
                        <small class="text-muted">File PDF skripsi lengkap yang sudah final dan telah direvisi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ PERBAIKAN: JavaScript dengan Konfirmasi yang Sesuai -->
<script>
$(document).ready(function() {
    // Validasi file size
    $('input[type="file"]').change(function() {
        const fileInput = this;
        const file = fileInput.files[0];
        let maxSize;
        
        // Tentukan max size berdasarkan nama field
        if (fileInput.name === 'file_skripsi_final') {
            maxSize = 5 * 1024 * 1024; // 5MB untuk skripsi
        } else {
            maxSize = 1 * 1024 * 1024; // 1MB untuk surat-surat
        }
        
        if (file && file.size > maxSize) {
            alert('Ukuran file terlalu besar!\n\n' +
                  'File: ' + file.name + '\n' +
                  'Ukuran: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB\n' +
                  'Maksimal: ' + (maxSize / 1024 / 1024) + ' MB\n\n' +
                  'Silakan kompres file atau pilih file lain.');
            fileInput.value = '';
        } else if (file) {
            // Show file info
            const fileInfo = $(fileInput).closest('.form-group').find('.file-info');
            if (fileInfo.length === 0) {
                $(fileInput).after('<div class="file-info mt-2"><small class="text-info"><i class="fas fa-file"></i> File dipilih: <strong>' + file.name + '</strong> (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</small></div>');
            } else {
                fileInfo.find('strong').text(file.name);
            }
        }
    });
    
    // ✅ PERBAIKAN: Validasi form dengan konfirmasi berdasarkan submit_type
    $('#formPublikasi').submit(function(e) {
        let isValid = true;
        let errors = [];
        
        // Cek judul skripsi final
        const judul = $('textarea[name="judul_skripsi_final"]').val().trim();
        if (judul.length < 10) {
            errors.push('Judul skripsi final minimal 10 karakter');
            isValid = false;
        }
        
        // Cek file upload untuk pengajuan baru
        <?php if ($action == 'ajukan'): ?>
        if (!$('input[name="file_surat_perpustakaan"]')[0].files.length) {
            errors.push('File surat perpustakaan wajib diupload');
            isValid = false;
        }
        
        if (!$('input[name="file_surat_revisi"]')[0].files.length) {
            errors.push('File surat revisi wajib diupload');
            isValid = false;
        }
        
        if (!$('input[name="file_skripsi_final"]')[0].files.length) {
            errors.push('File skripsi final wajib diupload');
            isValid = false;
        }
        <?php endif; ?>
        
        // Validasi URL jika diisi
        const linkRepo = $('input[name="link_repository"]').val().trim();
        if (linkRepo && !isValidURL(linkRepo)) {
            errors.push('Format link repository tidak valid. Harus dimulai dengan http:// atau https://');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Error dalam pengisian form:\n\n• ' + errors.join('\n• ') + '\n\nSilakan perbaiki dan coba lagi.');
            return false;
        }
        
        // ✅ PERBAIKAN: Konfirmasi submit berdasarkan submit_type
        const submitType = e.originalEvent.submitter.value;
        
        if (submitType === 'draft') {
            return confirm('💾 Simpan sebagai draft?\n\nData akan disimpan tetapi belum dikirim ke dosen pembimbing. Anda masih bisa mengedit nanti dan melanjutkan ke Step 3.');
            
        } else if (submitType === 'submit') {
            return confirm('📧 Kirim ajuan ke dosen pembimbing?\n\n✅ Step 2 → Step 3\n\nSetelah dikirim, ajuan akan masuk ke Step 4-6 untuk review dosen pembimbing. Dosen akan mendapat notifikasi email.\n\nPastikan semua data sudah benar karena setelah ini Anda tidak bisa mengedit lagi.');
            
        } else if (submitType === 'update') {
            return confirm('💾 Update data publikasi?\n\nData akan diperbarui sesuai perubahan yang Anda buat.');
            
        } else {
            return confirm('Yakin ingin melanjutkan? Pastikan semua data sudah benar.');
        }
    });
    
    // Helper function untuk validasi URL
    function isValidURL(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Auto-resize textarea
    $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Character counter untuk judul
    $('textarea[name="judul_skripsi_final"]').on('input', function() {
        const length = $(this).val().length;
        const counter = $(this).next('.char-counter');
        if (counter.length === 0) {
            $(this).after('<div class="char-counter"><small class="text-muted">Karakter: <span class="count">' + length + '</span></small></div>');
        } else {
            counter.find('.count').text(length);
        }
        
        if (length < 10) {
            counter.removeClass('text-success').addClass('text-warning');
        } else {
            counter.removeClass('text-warning').addClass('text-success');
        }
    }).trigger('input');
    
    // ✅ PERBAIKAN: Visual feedback untuk tombol
    $('button[type="submit"]').click(function() {
        const button = $(this);
        const submitType = button.val();
        
        // Reset all buttons
        $('button[type="submit"]').removeClass('btn-outline-success btn-outline-warning btn-outline-primary');
        
        // Highlight clicked button
        if (submitType === 'submit') {
            button.addClass('btn-outline-success');
        } else if (submitType === 'draft') {
            button.addClass('btn-outline-warning');
        } else if (submitType === 'update') {
            button.addClass('btn-outline-primary');
        }
    });
});
</script>

<style>
/* Custom styling untuk form - TIDAK DIUBAH */
.form-group label strong {
    color: #495057;
}

.badge {
    font-size: 0.7em;
    vertical-align: middle;
}

.bg-light {
    border: 1px dashed #dee2e6 !important;
}

.form-control-file {
    border: 2px dashed #dee2e6;
    padding: 10px;
    border-radius: 5px;
    background: #f8f9fa;
}

.form-control-file:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.card-header {
    background: linear-gradient(90deg, #007bff, #0056b3);
    color: white;
}

.card-header .card-title {
    color: white !important;
}

hr {
    border-top: 2px solid #e9ecef;
    margin: 2rem 0;
}

h5 i {
    margin-right: 8px;
}

.file-info {
    padding: 5px 10px;
    background: #e8f4f8;
    border-radius: 3px;
    border-left: 3px solid #17a2b8;
}

.char-counter {
    text-align: right;
    margin-top: 5px;
}

/* ✅ PERBAIKAN: Styling untuk tombol yang aktif */
.btn.btn-outline-success {
    animation: pulse-success 1s infinite;
}

.btn.btn-outline-warning {
    animation: pulse-warning 1s infinite;
}

.btn.btn-outline-primary {
    animation: pulse-primary 1s infinite;
}

@keyframes pulse-success {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

@keyframes pulse-warning {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

@keyframes pulse-primary {
    0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}

/* Success indicators */
.text-success a {
    color: #28a745 !important;
}

.text-success a:hover {
    color: #1e7e34 !important;
    text-decoration: underline;
}
</style>