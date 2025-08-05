<!-- 
FORM PENGAJUAN PUBLIKASI - DROPDOWN DOSEN VERSION
File: application/views/mahasiswa/publikasi/form.php

✅ ENHANCED: Dropdown dosen untuk eliminasi error input manual
- Data dosen diambil dari database
- Tidak ada kemungkinan salah input nama dosen
- Email notification pasti reliable
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
        base_url('mahasiswa/publikasi/ajukan/' . (isset($proposal->id) ? $proposal->id : '')) : 
        base_url('mahasiswa/publikasi/edit/' . $publikasi->id);
    ?>
    
    <form action="<?= $form_action ?>" method="POST" enctype="multipart/form-data" id="formPublikasi">
        <div class="card-body">
            
            <!-- Informasi Workflow -->
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Informasi Workflow Step 2</h6>
                <p class="mb-2">Anda sedang mengisi form publikasi dengan 10 field data yang diperlukan. Dosen pembimbing dipilih dari dropdown untuk memastikan data akurat.</p>
                <p class="mb-0"><strong>Pastikan semua data sesuai dengan dokumen resmi tugas akhir Anda.</strong></p>
            </div>

            <!-- Data Mahasiswa -->
            <h5 class="mb-3"><i class="fas fa-user text-primary"></i> Data Mahasiswa</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>1. Nama Lengkap</strong> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" required
                               value="<?= isset($publikasi->nama_mahasiswa) ? $publikasi->nama_mahasiswa : $this->session->userdata('nama') ?>"
                               placeholder="Masukkan nama lengkap sesuai ijazah">
                        <small class="text-muted">Nama lengkap sesuai dokumen resmi</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>2. NIM (Nomor Induk Mahasiswa)</strong> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nim" required
                               value="<?= isset($publikasi->nim) ? $publikasi->nim : $this->session->userdata('nim') ?>"
                               placeholder="Contoh: 20210001">
                        <small class="text-muted">NIM sesuai kartu mahasiswa</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><strong>3. Program Studi</strong> <span class="text-danger">*</span></label>
                <select class="form-control" name="program_studi" required>
                    <option value="">-- Pilih Program Studi --</option>
                    <option value="Pendidikan Keagamaan Katolik" <?= (isset($publikasi->program_studi) && $publikasi->program_studi == 'Pendidikan Keagamaan Katolik') ? 'selected' : '' ?>>Pendidikan Keagamaan Katolik</option>
                    <option value="Pendidikan Guru Sekolah Dasar" <?= (isset($publikasi->program_studi) && $publikasi->program_studi == 'Pendidikan Guru Sekolah Dasar') ? 'selected' : '' ?>>Pendidikan Guru Sekolah Dasar</option>
                </select>
                <small class="text-muted">Pilih program studi sesuai yang tertera di transkrip</small>
            </div>

            <hr>

            <!-- Data Tugas Akhir -->
            <h5 class="mb-3"><i class="fas fa-pen text-warning"></i> Data Tugas Akhir</h5>
            
            <div class="form-group">
                <label><strong>4. Judul Skripsi Final</strong> <span class="text-danger">*</span></label>
                <textarea class="form-control" name="judul_skripsi_final" rows="3" required 
                          placeholder="Masukkan judul skripsi final sesuai yang tertera di cover skripsi"><?= isset($publikasi->judul_skripsi_final) ? $publikasi->judul_skripsi_final : '' ?></textarea>
                <small class="text-muted">Judul skripsi yang final sesuai cover dan lembar pengesahan</small>
            </div>

            <hr>

            <!-- ✅ ENHANCED: Dosen Pembimbing Dropdown + Tanggal Manual -->
            <h5 class="mb-3"><i class="fas fa-calendar text-success"></i> Data Pembimbing & Jadwal</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>5. Dosen Pembimbing</strong> <span class="text-danger">*</span></label>
                        <select class="form-control" name="dosen_pembimbing_id" id="dosenPembimbing" required>
                            <option value="">-- Pilih Dosen Pembimbing --</option>
                            <?php if (isset($dosen_list) && !empty($dosen_list)): ?>
                                <?php foreach($dosen_list as $dosen): ?>
                                    <option value="<?= $dosen->id ?>" 
                                            data-nama="<?= htmlspecialchars($dosen->nama) ?>"
                                            data-email="<?= htmlspecialchars($dosen->email) ?>"
                                            <?= (isset($publikasi->dosen_pembimbing_id) && $publikasi->dosen_pembimbing_id == $dosen->id) ? 'selected' : '' ?>>
                                        <?= $dosen->nama ?>
                                        <?php if (!empty($dosen->nip)): ?>
                                            (NIP: <?= $dosen->nip ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Data dosen tidak tersedia</option>
                            <?php endif; ?>
                        </select>
                        <small class="text-muted">
                            Pilih dosen pembimbing sesuai lembar pengesahan. 
                            <span id="dosenInfo" class="text-info"></span>
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><strong>6. Tanggal Ujian Skripsi</strong> <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="tanggal_ujian_skripsi" required
                               value="<?= isset($publikasi->tanggal_ujian_skripsi) ? $publikasi->tanggal_ujian_skripsi : '' ?>">
                        <small class="text-muted">Tanggal pelaksanaan ujian/seminar skripsi</small>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Upload Dokumen - TIDAK DIUBAH -->
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

            <!-- Field 9: File Skripsi Final -->
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

            <!-- Repository & Keterangan - TIDAK DIUBAH -->
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
        
        <!-- Card Footer dengan Tombol - TIDAK DIUBAH -->
        <div class="card-footer">
            <div class="row">
                <div class="col-md-8">
                    <?php if ($action == 'ajukan'): ?>
                        <button type="submit" name="submit_type" value="submit" class="btn btn-success btn-lg mr-2">
                            <i class="fas fa-paper-plane"></i> 
                            Kirim Ajuan ke Dosen (Step 3)
                        </button>
                        <button type="submit" name="submit_type" value="draft" class="btn btn-warning btn-lg">
                            <i class="fas fa-save"></i> 
                            Simpan sebagai Draft
                        </button>
                    <?php else: ?>
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

<!-- Help Card - Enhanced -->
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
                <h6><i class="fas fa-user-graduate text-primary"></i> Tips Pilih Dosen:</h6>
                <ul class="list-unstyled">
                    <li>• Pilih dosen sesuai lembar pengesahan</li>
                    <li>• Pastikan nama dan NIP cocok</li>
                    <li>• Jika tidak ada, hubungi admin</li>
                    <li>• Email otomatis ke dosen terpilih</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-file-upload text-warning"></i> Tips Upload File:</h6>
                <ul class="list-unstyled">
                    <li>• Pastikan file dalam format PDF</li>
                    <li>• Surat perpustakaan & revisi max 1 MB</li>
                    <li>• File skripsi final max 5 MB</li>
                    <li>• Gunakan nama file yang jelas</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ✅ ENHANCED JavaScript untuk Dropdown Dosen -->
<script>
$(document).ready(function() {
    // ✅ Enhanced dropdown dosen interaction
    $('#dosenPembimbing').change(function() {
        const selectedOption = $(this).find('option:selected');
        const dosenNama = selectedOption.data('nama');
        const dosenEmail = selectedOption.data('email');
        
        if (dosenNama && dosenEmail) {
            $('#dosenInfo').html(`<i class="fas fa-envelope"></i> ${dosenEmail}`);
        } else {
            $('#dosenInfo').html('');
        }
    });
    
    // Trigger change event jika sudah ada selection (edit mode)
    $('#dosenPembimbing').trigger('change');
    
    // File validation - TIDAK DIUBAH
    $('input[type="file"]').change(function() {
        const fileInput = this;
        const file = fileInput.files[0];
        let maxSize;
        
        if (fileInput.name === 'file_skripsi_final') {
            maxSize = 5 * 1024 * 1024; // 5MB
        } else {
            maxSize = 1 * 1024 * 1024; // 1MB
        }
        
        if (file && file.size > maxSize) {
            alert('Ukuran file terlalu besar!\n\n' +
                  'File: ' + file.name + '\n' +
                  'Ukuran: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB\n' +
                  'Maksimal: ' + (maxSize / 1024 / 1024) + ' MB\n\n' +
                  'Silakan kompres file atau pilih file lain.');
            fileInput.value = '';
        } else if (file) {
            const fileInfo = $(fileInput).closest('.form-group').find('.file-info');
            if (fileInfo.length === 0) {
                $(fileInput).after('<div class="file-info mt-2"><small class="text-info"><i class="fas fa-file"></i> File dipilih: <strong>' + file.name + '</strong> (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</small></div>');
            } else {
                fileInfo.find('strong').text(file.name);
            }
        }
    });
    
    // Form validation dengan enhanced dosen check
    $('#formPublikasi').submit(function(e) {
        let isValid = true;
        let errors = [];
        
        // Check required fields
        const requiredFields = [
            {name: 'nama_lengkap', label: 'Nama Lengkap'},
            {name: 'nim', label: 'NIM'},
            {name: 'program_studi', label: 'Program Studi'},
            {name: 'judul_skripsi_final', label: 'Judul Skripsi Final'},
            {name: 'dosen_pembimbing_id', label: 'Dosen Pembimbing'},
            {name: 'tanggal_ujian_skripsi', label: 'Tanggal Ujian Skripsi'}
        ];
        
        requiredFields.forEach(function(field) {
            const value = $(`[name="${field.name}"]`).val();
            if (!value || value.trim() === '') {
                errors.push(`${field.label} wajib dipilih/diisi`);
                isValid = false;
            }
        });
        
        // Validate NIM format
        const nim = $('input[name="nim"]').val().trim();
        if (nim && !/^\d+$/.test(nim)) {
            errors.push('NIM harus berupa angka');
            isValid = false;
        }
        
        // Validate judul length
        const judul = $('textarea[name="judul_skripsi_final"]').val().trim();
        if (judul && judul.length < 10) {
            errors.push('Judul skripsi minimal 10 karakter');
            isValid = false;
        }
        
        // Check file uploads untuk pengajuan baru
        <?php if ($action == 'ajukan'): ?>
        const requiredFiles = [
            {name: 'file_surat_perpustakaan', label: 'Surat Perpustakaan'},
            {name: 'file_surat_revisi', label: 'Surat Revisi'},
            {name: 'file_skripsi_final', label: 'File Skripsi Final'}
        ];
        
        requiredFiles.forEach(function(file) {
            if (!$(`input[name="${file.name}"]`)[0].files.length) {
                errors.push(`${file.label} wajib diupload`);
                isValid = false;
            }
        });
        <?php endif; ?>
        
        // Validate URL jika diisi
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
        
        // Konfirmasi submit
        const submitType = e.originalEvent.submitter.value;
        const dosenNama = $('#dosenPembimbing option:selected').text();
        
        let confirmMessage = '';
        if (submitType === 'draft') {
            confirmMessage = '💾 Simpan sebagai draft?\n\nData akan disimpan tetapi belum dikirim ke dosen pembimbing.';
        } else if (submitType === 'submit') {
            confirmMessage = `📧 Kirim ajuan ke dosen pembimbing?\n\n✅ Step 2 → Step 3\n\nDosen: ${dosenNama}\n\nSetelah dikirim, dosen akan mendapat email notifikasi untuk review. Pastikan semua data sudah benar!`;
        } else if (submitType === 'update') {
            confirmMessage = '💾 Update data publikasi?\n\nData akan diperbarui sesuai perubahan yang Anda buat.';
        }
        
        return confirm(confirmMessage);
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
});
</script>