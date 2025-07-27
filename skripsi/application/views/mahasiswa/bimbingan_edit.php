<?php 
/**
 * Edit Bimbingan Mahasiswa View - Template Sederhana
 * File: application/views/mahasiswa/bimbingan_edit.php
 * Menggantikan template kompleks dengan approach sederhana
 */

// Capture content untuk template
ob_start();
?>

<!-- Header dengan Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center">
            <div style="width: 50px; height: 50px; background: linear-gradient(87deg, #fb6340 0, #fbb140 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-right: 1rem;">
                ✏️
            </div>
            <div style="flex: 1;">
                <h2 class="mb-1" style="color: #32325d; font-weight: 600;">Edit Jurnal Bimbingan</h2>
                <nav style="font-size: 0.875rem; color: #8898aa;">
                    <a href="<?= base_url() ?>mahasiswa/dashboard" style="color: #8898aa; text-decoration: none;">Dashboard</a>
                    <span> / </span>
                    <a href="<?= base_url() ?>mahasiswa/bimbingan" style="color: #8898aa; text-decoration: none;">Bimbingan</a>
                    <span> / Edit Jurnal</span>
                </nav>
            </div>
            <div>
                <a href="<?= base_url() ?>mahasiswa/bimbingan" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Bimbingan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <strong>Berhasil!</strong> <?= $this->session->flashdata('success') ?>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Error!</strong> <?= $this->session->flashdata('error') ?>
    </div>
<?php endif; ?>

<?php if (!isset($jurnal) || !$jurnal): ?>
<!-- Jurnal Tidak Ditemukan -->
<div class="card">
    <div class="card-body text-center p-5">
        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3;">❌</div>
        <h4 class="mb-3" style="color: #32325d;">Jurnal Tidak Ditemukan</h4>
        <p class="text-muted mb-4">
            Jurnal bimbingan yang Anda cari tidak ditemukan atau sudah dihapus.
        </p>
        <a href="<?= base_url() ?>mahasiswa/bimbingan" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Bimbingan
        </a>
    </div>
</div>

<?php elseif ($jurnal->status_validasi != '0'): ?>
<!-- Jurnal Sudah Divalidasi -->
<div class="card">
    <div class="card-body text-center p-5">
        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3;">🔒</div>
        <h4 class="mb-3" style="color: #32325d;">Jurnal Sudah Divalidasi</h4>
        <p class="text-muted mb-4">
            Jurnal bimbingan ini sudah divalidasi oleh dosen pembimbing dan tidak dapat diubah lagi.
        </p>
        
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <div class="card" style="border-left: 4px solid <?= $jurnal->status_validasi == '1' ? '#2dce89' : '#f5365c' ?>;">
                    <div class="card-body">
                        <h6 class="mb-2">📊 Status Validasi</h6>
                        <?php if ($jurnal->status_validasi == '1'): ?>
                            <span class="btn btn-success btn-sm mb-2">
                                <i class="fas fa-check"></i> Tervalidasi
                            </span>
                            <p class="mb-0 text-muted">
                                Divalidasi pada: <?= date('d F Y H:i', strtotime($jurnal->tanggal_validasi)) ?>
                            </p>
                        <?php else: ?>
                            <span class="btn btn-danger btn-sm mb-2">
                                <i class="fas fa-times"></i> Perlu Revisi
                            </span>
                            <p class="mb-0 text-muted">
                                Silakan buat jurnal bimbingan baru dengan perbaikan yang diperlukan.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <a href="<?= base_url() ?>mahasiswa/bimbingan" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Bimbingan
        </a>
    </div>
</div>

<?php else: ?>
<!-- Form Edit Jurnal -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-edit"></i> Edit Jurnal Bimbingan Pertemuan ke-<?= $jurnal->pertemuan_ke ?>
                </h6>
            </div>
            <div class="card-body">
                <form action="<?= base_url() ?>mahasiswa/bimbingan/edit_jurnal/<?= $jurnal->id ?>" method="POST" id="formEditJurnal">
                    
                    <!-- Info Status -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Catatan:</strong> Anda hanya dapat mengedit jurnal yang belum divalidasi oleh dosen pembimbing. 
                        Jurnal yang sudah divalidasi tidak dapat diubah.
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <!-- Pertemuan ke -->
                        <div style="flex: 1;">
                            <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">
                                Pertemuan ke- <span style="color: #f5365c;">*</span>
                            </label>
                            <input type="number" name="pertemuan_ke" readonly
                                   value="<?= $jurnal->pertemuan_ke ?>" min="1"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem; background: #f8f9fa;">
                            <small style="color: #8898aa;">Nomor pertemuan tidak dapat diubah</small>
                        </div>
                        
                        <!-- Tanggal Bimbingan -->
                        <div style="flex: 1;">
                            <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">
                                Tanggal Bimbingan <span style="color: #f5365c;">*</span>
                            </label>
                            <input type="date" name="tanggal_bimbingan" required
                                   value="<?= $jurnal->tanggal_bimbingan ?>"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem;">
                        </div>
                    </div>
                    
                    <!-- Materi Bimbingan -->
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">
                            Materi Bimbingan <span style="color: #f5365c;">*</span>
                        </label>
                        <textarea name="materi_bimbingan" required rows="4"
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini"
                                  style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem; resize: vertical;"><?= htmlspecialchars($jurnal->materi_bimbingan) ?></textarea>
                    </div>
                    
                    <!-- Catatan Mahasiswa -->
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">Catatan Mahasiswa</label>
                        <textarea name="catatan_mahasiswa" rows="3"
                                  placeholder="Catatan atau pertanyaan dari Anda untuk dosen"
                                  style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem; resize: vertical;"><?= htmlspecialchars($jurnal->catatan_mahasiswa ?? '') ?></textarea>
                        <small style="color: #8898aa;">Field ini akan terlihat oleh dosen pembimbing</small>
                    </div>
                    
                    <!-- Tindak Lanjut -->
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">Tindak Lanjut</label>
                        <textarea name="tindak_lanjut" rows="3"
                                  placeholder="Tugas atau tindak lanjut yang diberikan dosen"
                                  style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem; resize: vertical;"><?= htmlspecialchars($jurnal->tindak_lanjut ?? '') ?></textarea>
                    </div>
                    
                    <!-- Informasi tambahan jika ada catatan dosen -->
                    <?php if (!empty($jurnal->catatan_dosen)): ?>
                        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1rem;">
                            <h6 style="color: #856404; margin-bottom: 0.5rem;">
                                <i class="fas fa-comment"></i> Catatan Dosen Sebelumnya
                            </h6>
                            <p style="margin: 0; color: #856404; font-style: italic;">
                                "<?= htmlspecialchars($jurnal->catatan_dosen) ?>"
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Tombol Submit -->
                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #e9ecef;">
                        <a href="<?= base_url() ?>mahasiswa/bimbingan" 
                           style="padding: 0.75rem 1.5rem; border: 1px solid #e9ecef; background: white; color: #525f7f; border-radius: 0.375rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" 
                                style="padding: 0.75rem 1.5rem; background: #fb6340; color: white; border: none; border-radius: 0.375rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-save"></i> Update Jurnal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Info Jurnal -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">📋 Informasi Jurnal</h6>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 1rem;">
                    <strong>Pertemuan ke:</strong>
                    <div><?= $jurnal->pertemuan_ke ?></div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <strong>Dibuat pada:</strong>
                    <div><?= date('d F Y H:i', strtotime($jurnal->created_at)) ?></div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <strong>Terakhir diupdate:</strong>
                    <div><?= date('d F Y H:i', strtotime($jurnal->updated_at)) ?></div>
                </div>
                
                <div>
                    <strong>Status:</strong>
                    <div>
                        <span class="btn btn-warning btn-sm">
                            <i class="fas fa-clock"></i> Pending Validasi
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tips Edit -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">💡 Tips Edit Jurnal</h6>
            </div>
            <div class="card-body">
                <div style="font-size: 0.875rem; color: #525f7f; line-height: 1.6;">
                    <div style="margin-bottom: 0.75rem;">
                        <strong style="color: #32325d;">📝 Materi Bimbingan:</strong>
                        Jelaskan secara detail topik yang dibahas, progress yang dicapai, dan kendala yang dihadapi.
                    </div>
                    
                    <div style="margin-bottom: 0.75rem;">
                        <strong style="color: #32325d;">💭 Catatan Mahasiswa:</strong>
                        Tuliskan pertanyaan atau hal yang ingin Anda diskusikan lebih lanjut dengan dosen.
                    </div>
                    
                    <div>
                        <strong style="color: #32325d;">🎯 Tindak Lanjut:</strong>
                        Catat tugas atau target yang harus diselesaikan sebelum pertemuan berikutnya.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Page-specific JavaScript -->
<script>
// Form validation
document.getElementById('formEditJurnal')?.addEventListener('submit', function(e) {
    const tanggalBimbingan = document.querySelector('input[name="tanggal_bimbingan"]').value;
    const materiBimbingan = document.querySelector('textarea[name="materi_bimbingan"]').value;
    
    if (!tanggalBimbingan || !materiBimbingan.trim()) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi (*)');
        return false;
    }
    
    // Validasi tanggal tidak boleh lebih dari hari ini
    const today = new Date().toISOString().split('T')[0];
    if (tanggalBimbingan > today) {
        e.preventDefault();
        alert('Tanggal bimbingan tidak boleh lebih dari hari ini');
        return false;
    }
    
    // Disable submit button to prevent double submission
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    // Show loading state
    setTimeout(() => {
        if (submitBtn.disabled) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Jurnal';
        }
    }, 10000); // Reset after 10 seconds if still disabled
});

// Character count for textareas
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(function(textarea) {
        // Add character counter
        const wrapper = textarea.parentNode;
        const counter = document.createElement('small');
        counter.style.color = '#8898aa';
        counter.style.float = 'right';
        counter.style.marginTop = '0.25rem';
        
        function updateCounter() {
            const current = textarea.value.length;
            const max = textarea.getAttribute('maxlength');
            if (max) {
                counter.textContent = current + '/' + max + ' karakter';
                if (current > max * 0.9) {
                    counter.style.color = '#f5365c';
                } else {
                    counter.style.color = '#8898aa';
                }
            }
        }
        
        if (textarea.getAttribute('maxlength')) {
            wrapper.appendChild(counter);
            updateCounter();
            textarea.addEventListener('input', updateCounter);
        }
    });
});

// Warn before leaving if form is dirty
let formIsDirty = false;
document.getElementById('formEditJurnal')?.addEventListener('input', function() {
    formIsDirty = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formIsDirty) {
        const message = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
        e.returnValue = message;
        return message;
    }
});

// Reset dirty flag on form submit
document.getElementById('formEditJurnal')?.addEventListener('submit', function() {
    formIsDirty = false;
});
</script>

<?php
// Capture content dan set untuk template
$content = ob_get_clean();

// Data untuk template
$template_data = [
    'title' => 'Edit Jurnal Bimbingan',
    'content' => $content
];

// Load template sederhana
$this->load->view('template/mahasiswa_simple', $template_data);
?>