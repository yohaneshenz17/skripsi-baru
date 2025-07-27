<?php 
/**
 * Bimbingan Mahasiswa View - Template Sederhana
 * File: application/views/mahasiswa/bimbingan.php
 * Menggantikan template kompleks dengan approach sederhana
 */

// Capture content untuk template
ob_start();
?>

<!-- Header Info & Actions -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="d-flex align-items-center">
            <div style="width: 60px; height: 60px; background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.8rem; margin-right: 1rem;">
                👥
            </div>
            <div>
                <h2 class="mb-1" style="color: #32325d; font-weight: 600;">Bimbingan Tugas Akhir</h2>
                <p class="mb-0" style="color: #8898aa;">Kelola jurnal bimbingan dengan dosen pembimbing Anda</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 text-right">
        <?php if (isset($proposal) && $proposal): ?>
            <button type="button" class="btn btn-primary" onclick="tambahJurnalBimbingan()">
                <i class="fas fa-plus"></i> Tambah Jurnal Bimbingan
            </button>
        <?php endif; ?>
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

<?php if ($this->session->flashdata('warning')): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Perhatian!</strong> <?= $this->session->flashdata('warning') ?>
    </div>
<?php endif; ?>

<?php if (!isset($proposal) || !$proposal): ?>
<!-- Belum Ada Proposal -->
<div class="card">
    <div class="card-body text-center p-5">
        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3;">📋</div>
        <h4 class="mb-3" style="color: #32325d;">Belum Ada Proposal</h4>
        <p class="text-muted mb-4">
            Anda belum memiliki proposal yang disetujui. Silakan ajukan proposal terlebih dahulu 
            sebelum memulai proses bimbingan.
        </p>
        <a href="<?= base_url() ?>mahasiswa/proposal" class="btn btn-primary">
            <i class="fas fa-file-alt"></i> Kelola Proposal
        </a>
    </div>
</div>

<?php elseif ($proposal->status != '1'): ?>
<!-- Proposal Belum Disetujui -->
<div class="card">
    <div class="card-body text-center p-5">
        <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3;">⏳</div>
        <h4 class="mb-3" style="color: #32325d;">Proposal Belum Disetujui</h4>
        <p class="text-muted mb-4">
            Proposal Anda masih dalam tahap review. Bimbingan dapat dimulai setelah proposal disetujui 
            dan dosen pembimbing ditunjuk.
        </p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card" style="border-left: 4px solid #fb6340;">
                    <div class="card-body">
                        <h6 class="mb-2">📄 Status Proposal</h6>
                        <p class="mb-2"><strong>Judul:</strong> <?= $proposal->judul ?></p>
                        <p class="mb-0">
                            <span class="btn btn-warning btn-sm">⏳ Menunggu Persetujuan</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Proposal Sudah Disetujui - Tampilkan Bimbingan -->

<!-- Info Proposal & Dosen Pembimbing -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">📄 Informasi Proposal</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="mb-2" style="color: #32325d;"><?= $proposal->judul ?></h6>
                        <p class="text-muted mb-1">
                            <i class="fas fa-calendar"></i> 
                            Disetujui: <?= date('d F Y', strtotime($proposal->tanggal_penetapan)) ?>
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-graduation-cap"></i> 
                            <?= $proposal->nama_prodi ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Disetujui
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">👨‍🏫 Dosen Pembimbing</h6>
            </div>
            <div class="card-body text-center">
                <?php if (isset($dosen_pembimbing) && $dosen_pembimbing): ?>
                    <div style="width: 50px; height: 50px; background: #e9ecef; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        👨‍🏫
                    </div>
                    <h6 class="mb-1"><?= $dosen_pembimbing['nama'] ?></h6>
                    <small class="text-muted"><?= $dosen_pembimbing['email'] ?></small>
                <?php else: ?>
                    <div class="text-muted">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👨‍🏫</div>
                        <small>Dosen pembimbing belum ditunjuk</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bimbingan -->
<div class="card mb-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">📊 Progress Bimbingan</h6>
            <small class="text-muted">
                <?= isset($total_bimbingan_valid) ? $total_bimbingan_valid : 0 ?> dari minimal 8 bimbingan tervalidasi
            </small>
        </div>
    </div>
    <div class="card-body">
        <?php 
        $progress_percentage = isset($total_bimbingan_valid) ? min(($total_bimbingan_valid / 8) * 100, 100) : 0;
        ?>
        <div class="progress-wrapper">
            <div class="progress-info">
                <span class="progress-label">Progress Bimbingan</span>
                <span class="progress-percentage"><?= round($progress_percentage) ?>%</span>
            </div>
            <div class="progress">
                <div class="progress-bar" style="width: <?= $progress_percentage ?>%"></div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-3 text-center">
                <div style="font-size: 1.5rem; color: #2dce89;">✅</div>
                <h6 class="mb-0"><?= isset($total_bimbingan_valid) ? $total_bimbingan_valid : 0 ?></h6>
                <small class="text-muted">Tervalidasi</small>
            </div>
            <div class="col-md-3 text-center">
                <div style="font-size: 1.5rem; color: #fb6340;">⏳</div>
                <h6 class="mb-0"><?= isset($total_bimbingan_pending) ? $total_bimbingan_pending : 0 ?></h6>
                <small class="text-muted">Pending</small>
            </div>
            <div class="col-md-3 text-center">
                <div style="font-size: 1.5rem; color: #f5365c;">❌</div>
                <h6 class="mb-0"><?= isset($total_bimbingan_revisi) ? $total_bimbingan_revisi : 0 ?></h6>
                <small class="text-muted">Perlu Revisi</small>
            </div>
            <div class="col-md-3 text-center">
                <div style="font-size: 1.5rem; color: #525f7f;">📋</div>
                <h6 class="mb-0"><?= isset($total_bimbingan) ? $total_bimbingan : 0 ?></h6>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Jurnal Bimbingan -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">📚 Jurnal Bimbingan</h6>
            <button type="button" class="btn btn-primary btn-sm" onclick="tambahJurnalBimbingan()">
                <i class="fas fa-plus"></i> Tambah Jurnal
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th width="8%">No.</th>
                            <th width="12%">Tanggal</th>
                            <th width="35%">Materi Bimbingan</th>
                            <th width="20%">Status</th>
                            <th width="15%">Validasi</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jurnal_bimbingan as $index => $jurnal): ?>
                            <tr>
                                <td class="text-center">
                                    <div style="width: 30px; height: 30px; background: #5e72e4; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; margin: 0 auto;">
                                        <?= $jurnal->pertemuan_ke ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.875rem; font-weight: 500;">
                                        <?= date('d M Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="max-width: 300px;">
                                        <p class="mb-1" style="font-size: 0.875rem; font-weight: 500;">
                                            <?= substr($jurnal->materi_bimbingan, 0, 80) ?>
                                            <?= strlen($jurnal->materi_bimbingan) > 80 ? '...' : '' ?>
                                        </p>
                                        <?php if ($jurnal->tindak_lanjut): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-arrow-right"></i> 
                                                <?= substr($jurnal->tindak_lanjut, 0, 50) ?>
                                                <?= strlen($jurnal->tindak_lanjut) > 50 ? '...' : '' ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($jurnal->status_validasi == '1'): ?>
                                        <span class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Tervalidasi
                                        </span>
                                    <?php elseif ($jurnal->status_validasi == '2'): ?>
                                        <span class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i> Perlu Revisi
                                        </span>
                                    <?php else: ?>
                                        <span class="btn btn-warning btn-sm">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($jurnal->status_validasi == '1' && $jurnal->tanggal_validasi): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i>
                                            <?= date('d/m/Y', strtotime($jurnal->tanggal_validasi)) ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.25rem;">
                                        <!-- Detail Button -->
                                        <button type="button" class="btn btn-info btn-sm" 
                                                onclick="lihatDetailJurnal(<?= $jurnal->id ?>)" 
                                                title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <!-- Edit Button - hanya untuk jurnal pending -->
                                        <?php if ($jurnal->status_validasi == '0'): ?>
                                            <a href="<?= base_url() ?>mahasiswa/bimbingan/edit_jurnal/<?= $jurnal->id ?>" 
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Delete Button - hanya untuk jurnal pending -->
                                        <?php if ($jurnal->status_validasi == '0'): ?>
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    onclick="hapusJurnal(<?= $jurnal->id ?>)" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center p-5">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3;">📚</div>
                <h5 class="mb-3" style="color: #32325d;">Belum Ada Jurnal Bimbingan</h5>
                <p class="text-muted mb-4">
                    Mulai tambahkan jurnal bimbingan dengan dosen pembimbing Anda.
                </p>
                <button type="button" class="btn btn-primary" onclick="tambahJurnalBimbingan()">
                    <i class="fas fa-plus"></i> Tambah Jurnal Pertama
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

<!-- Modal Tambah Jurnal Bimbingan -->
<div id="modalTambahJurnal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 0.5rem; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <form action="<?= base_url() ?>mahasiswa/bimbingan/tambah_jurnal" method="POST" id="formTambahJurnal">
            <!-- Modal Header -->
            <div style="padding: 1.5rem; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center;">
                <h5 style="margin: 0; color: #32325d; font-weight: 600;">
                    <i class="fas fa-plus-circle"></i> Tambah Jurnal Bimbingan
                </h5>
                <button type="button" onclick="tutupModal()" style="background: none; border: none; font-size: 1.5rem; color: #8898aa; cursor: pointer;">
                    ×
                </button>
            </div>
            
            <!-- Modal Body -->
            <div style="padding: 1.5rem;">
                <!-- Info Mahasiswa -->
                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">Mahasiswa</label>
                    <input type="text" readonly 
                           value="<?= $this->session->userdata('nama') ?> (<?= $this->session->userdata('username') ?>)"
                           style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem; background: #f8f9fa;">
                    <small style="color: #8898aa;">Jurnal bimbingan akan tercatat atas nama Anda</small>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <!-- Pertemuan ke -->
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">
                            Pertemuan ke- <span style="color: #f5365c;">*</span>
                        </label>
                        <input type="number" name="pertemuan_ke" required min="1"
                               value="<?= isset($total_bimbingan) ? ($total_bimbingan + 1) : 1 ?>"
                               style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem;">
                        <small style="color: #8898aa;">Nomor urut pertemuan bimbingan</small>
                    </div>
                    
                    <!-- Tanggal Bimbingan -->
                    <div style="flex: 1;">
                        <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">
                            Tanggal Bimbingan <span style="color: #f5365c;">*</span>
                        </label>
                        <input type="date" name="tanggal_bimbingan" required value="<?= date('Y-m-d') ?>"
                               style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem;">
                    </div>
                </div>
                
                <!-- Materi Bimbingan -->
                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">
                        Materi Bimbingan <span style="color: #f5365c;">*</span>
                    </label>
                    <textarea name="materi_bimbingan" required rows="4"
                              placeholder="Jelaskan materi yang dibahas dalam bimbingan ini, misalnya: diskusi BAB 1, review metodologi, perbaikan rumusan masalah, dll."
                              style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem; resize: vertical;"></textarea>
                </div>
                
                <!-- Catatan Mahasiswa -->
                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">Catatan Mahasiswa</label>
                    <textarea name="catatan_mahasiswa" rows="3"
                              placeholder="Catatan atau pertanyaan dari Anda untuk dosen"
                              style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem; resize: vertical;"></textarea>
                    <small style="color: #8898aa;">Field ini akan terlihat oleh dosen pembimbing</small>
                </div>
                
                <!-- Tindak Lanjut -->
                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: 600; color: #32325d; margin-bottom: 0.5rem; display: block;">Tindak Lanjut</label>
                    <textarea name="tindak_lanjut" rows="3"
                              placeholder="Tugas atau tindak lanjut yang diberikan dosen"
                              style="width: 100%; padding: 0.75rem; border: 1px solid #e9ecef; border-radius: 0.375rem; resize: vertical;"></textarea>
                </div>
                
                <!-- Info untuk mahasiswa -->
                <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1rem;">
                    <div style="color: #155724; font-size: 0.875rem;">
                        <i class="fas fa-info-circle"></i> 
                        <strong>PERBAIKAN BARU:</strong> Anda sekarang dapat membuat jurnal bimbingan baru meskipun ada jurnal sebelumnya yang masih pending validasi.
                        Jika ada pertemuan dengan nomor yang sama, sistem akan memperbarui jurnal yang sudah ada.
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div style="padding: 1rem 1.5rem; border-top: 1px solid #e9ecef; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="tutupModal()" 
                        style="padding: 0.5rem 1rem; border: 1px solid #e9ecef; background: white; color: #525f7f; border-radius: 0.375rem; cursor: pointer;">
                    Batal
                </button>
                <button type="submit" 
                        style="padding: 0.5rem 1rem; background: #5e72e4; color: white; border: none; border-radius: 0.375rem; cursor: pointer;">
                    <i class="fas fa-save"></i> Simpan Jurnal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Page-specific JavaScript -->
<script>
// Toggle Modal
function tambahJurnalBimbingan() {
    document.getElementById('modalTambahJurnal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function tutupModal() {
    document.getElementById('modalTambahJurnal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Lihat Detail Jurnal
function lihatDetailJurnal(id) {
    // Implementasi detail jurnal - bisa redirect ke halaman detail
    window.location.href = '<?= base_url() ?>mahasiswa/bimbingan/detail_jurnal/' + id;
}

// Hapus Jurnal dengan konfirmasi
function hapusJurnal(id) {
    if (confirm('Apakah Anda yakin ingin menghapus jurnal bimbingan ini?\n\nData yang sudah dihapus tidak dapat dikembalikan.')) {
        window.location.href = '<?= base_url() ?>mahasiswa/bimbingan/hapus_jurnal/' + id;
    }
}

// Close modal when clicking outside
document.getElementById('modalTambahJurnal').addEventListener('click', function(e) {
    if (e.target === this) {
        tutupModal();
    }
});

// Form validation
document.getElementById('formTambahJurnal').addEventListener('submit', function(e) {
    const pertemuanKe = document.querySelector('input[name="pertemuan_ke"]').value;
    const tanggalBimbingan = document.querySelector('input[name="tanggal_bimbingan"]').value;
    const materiBimbingan = document.querySelector('textarea[name="materi_bimbingan"]').value;
    
    if (!pertemuanKe || !tanggalBimbingan || !materiBimbingan.trim()) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi (*)');
        return false;
    }
    
    // Disable submit button to prevent double submission
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
});

// Initialize progress bar animation
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        const targetWidth = progressBar.style.width;
        progressBar.style.width = '0%';
        setTimeout(function() {
            progressBar.style.width = targetWidth;
        }, 500);
    }
});
</script>

<?php
// Capture content dan set untuk template
$content = ob_get_clean();

// Data untuk template
$template_data = [
    'title' => 'Bimbingan Tugas Akhir',
    'content' => $content
];

// Load template sederhana
$this->load->view('template/mahasiswa_simple', $template_data);
?>