<?php
/**
 * FIXED UI - Penjadwalan Seminar Skripsi
 * File: application/views/kaprodi/seminar_skripsi/penjadwalan.php
 * 
 * PERBAIKAN UI:
 * - Menyesuaikan dengan template kaprodi existing
 * - Layout responsive yang lebih baik
 * - Card design yang konsisten
 * - Form styling yang rapi
 */
?>

<!-- Breadcrumb Navigation -->
<div class="row">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-3 pb-0">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi/dashboard') ?>">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('kaprodi/seminar_skripsi') ?>">Seminar Skripsi</a>
                </li>
                <li class="breadcrumb-item active">Penjadwalan</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="h3 mb-0">
                    <i class="fas fa-calendar-plus text-primary mr-2"></i>
                    Penjadwalan Seminar Skripsi
                </h2>
                <p class="text-muted mb-0">Tetapkan jadwal dan penunjukan dosen penguji</p>
            </div>
            <a href="<?= base_url('kaprodi/seminar_skripsi') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Informasi Mahasiswa & Rekomendasi -->
    <div class="col-lg-4 col-md-12 mb-4">
        <!-- Info Mahasiswa -->
        <div class="card shadow mb-4">
            <div class="card-header bg-success">
                <h6 class="text-white mb-0">
                    <i class="fas fa-user-graduate mr-2"></i>
                    Informasi Mahasiswa
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="35%" class="font-weight-bold">Nama:</td>
                            <td><?= htmlspecialchars($seminar->nama_mahasiswa ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">NIM:</td>
                            <td><?= htmlspecialchars($seminar->nim ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Pembimbing:</td>
                            <td><?= htmlspecialchars($seminar->nama_pembimbing ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Plagiarisme:</td>
                            <td>
                                <?php if (!empty($seminar->plagiarism_percentage)): ?>
                                    <span class="badge badge-success">
                                        <?= number_format($seminar->plagiarism_percentage, 1) ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Status:</td>
                            <td>
                                <span class="badge badge-success">
                                    <i class="fas fa-check mr-1"></i>Disetujui
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rekomendasi Penguji -->
        <div class="card shadow">
            <div class="card-header bg-info">
                <h6 class="text-white mb-0">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Rekomendasi Penguji
                </h6>
            </div>
            <div class="card-body">
                <?php if (isset($penguji_recommendations) && !empty($penguji_recommendations) && isset($penguji_recommendations['penguji1'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Rekomendasi Ditemukan!</strong><br>
                        <small>Berdasarkan seminar proposal sebelumnya</small>
                    </div>
                    
                    <?php if (!empty($penguji_recommendations['penguji1'])): ?>
                        <div class="mb-3 p-3 border border-success rounded bg-light">
                            <div class="font-weight-bold text-success mb-1">
                                <i class="fas fa-user-tie mr-1"></i>
                                Rekomendasi Penguji 1:
                            </div>
                            <div class="text-primary font-weight-bold">
                                <?= htmlspecialchars($penguji_recommendations['penguji1']['nama'] ?? 'N/A') ?>
                            </div>
                            <?php if (!empty($penguji_recommendations['penguji1']['email'])): ?>
                                <small class="text-muted"><?= htmlspecialchars($penguji_recommendations['penguji1']['email']) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($penguji_recommendations['penguji2'])): ?>
                        <div class="mb-3 p-3 border border-success rounded bg-light">
                            <div class="font-weight-bold text-success mb-1">
                                <i class="fas fa-user-tie mr-1"></i>
                                Rekomendasi Penguji 2:
                            </div>
                            <div class="text-primary font-weight-bold">
                                <?= htmlspecialchars($penguji_recommendations['penguji2']['nama'] ?? 'N/A') ?>
                            </div>
                            <?php if (!empty($penguji_recommendations['penguji2']['email'])): ?>
                                <small class="text-muted"><?= htmlspecialchars($penguji_recommendations['penguji2']['email']) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <small class="text-muted">
                        <i class="fas fa-edit mr-1"></i>
                        Anda dapat mengubah penguji di form sebelah kanan.
                    </small>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Tidak ada rekomendasi</strong><br>
                        <small>Silakan pilih dosen penguji secara manual.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Form Penjadwalan -->
    <div class="col-lg-8 col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary">
                <h6 class="text-white mb-0">
                    <i class="fas fa-calendar-plus mr-2"></i>
                    Form Penjadwalan Seminar
                </h6>
            </div>
            <div class="card-body">
                <!-- Info Judul -->
                <div class="alert alert-info">
                    <h6 class="mb-2">
                        <i class="fas fa-book mr-2"></i>Judul Skripsi:
                    </h6>
                    <p class="mb-0 font-weight-bold">
                        <?= htmlspecialchars($seminar->judul ?? 'N/A') ?>
                    </p>
                </div>

                <form action="<?= base_url('kaprodi/seminar_skripsi/simpan_jadwal') ?>" 
                      method="post" 
                      id="form-jadwal">
                    <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">

                    <!-- Jadwal Seminar -->
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-light border-primary">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-calendar mr-2"></i>
                                Jadwal Seminar
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="tanggal_seminar" class="form-label font-weight-bold">
                                        Tanggal Seminar <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="tanggal_seminar" 
                                           name="tanggal_seminar" 
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                           required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="jam_seminar" class="form-label font-weight-bold">
                                        Jam Seminar <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" 
                                           class="form-control" 
                                           id="jam_seminar" 
                                           name="jam_seminar" 
                                           required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="tempat_seminar" class="form-label font-weight-bold">
                                        Tempat Seminar <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="tempat_seminar" 
                                           name="tempat_seminar" 
                                           placeholder="Contoh: Ruang Seminar Lt. 2"
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Penunjukan Dosen Penguji -->
                    <div class="card border-warning mb-4">
                        <div class="card-header bg-light border-warning">
                            <h6 class="mb-0 text-warning">
                                <i class="fas fa-users mr-2"></i>
                                Penunjukan Dosen Penguji
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Catatan:</strong> Sesuai kebijakan STK Santo Yakobus, dosen penguji 
                                langsung ditunjuk tanpa perlu konfirmasi kesediaan.
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dosen_penguji1_id" class="form-label font-weight-bold">
                                        Dosen Penguji 1 <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" 
                                            id="dosen_penguji1_id" 
                                            name="dosen_penguji1_id" 
                                            required>
                                        <option value="">-- Pilih Dosen Penguji 1 --</option>
                                        <?php if (!empty($dosen_list)): ?>
                                            <?php foreach ($dosen_list as $dosen): ?>
                                                <?php if ($dosen->id != ($seminar->pembimbing_id ?? null)): ?>
                                                    <option value="<?= $dosen->id ?>"
                                                        <?php
                                                        // Auto-select rekomendasi
                                                        if (isset($penguji_recommendations['penguji1']) && 
                                                            $penguji_recommendations['penguji1']['id'] == $dosen->id) {
                                                            echo 'selected';
                                                        }
                                                        ?>>
                                                        <?= htmlspecialchars($dosen->nama) ?>
                                                        <?php 
                                                        if (isset($penguji_recommendations['penguji1']) && 
                                                            $penguji_recommendations['penguji1']['id'] == $dosen->id) {
                                                            echo ' (Rekomendasi ✓)';
                                                        }
                                                        ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (isset($penguji_recommendations['penguji1'])): ?>
                                        <small class="text-success">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Rekomendasi: <?= htmlspecialchars($penguji_recommendations['penguji1']['nama']) ?> (sudah dipilih)
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="dosen_penguji2_id" class="form-label font-weight-bold">
                                        Dosen Penguji 2 <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" 
                                            id="dosen_penguji2_id" 
                                            name="dosen_penguji2_id" 
                                            required>
                                        <option value="">-- Pilih Dosen Penguji 2 --</option>
                                        <?php if (!empty($dosen_list)): ?>
                                            <?php foreach ($dosen_list as $dosen): ?>
                                                <?php if ($dosen->id != ($seminar->pembimbing_id ?? null)): ?>
                                                    <option value="<?= $dosen->id ?>"
                                                        <?php
                                                        // Auto-select rekomendasi
                                                        if (isset($penguji_recommendations['penguji2']) && 
                                                            $penguji_recommendations['penguji2']['id'] == $dosen->id) {
                                                            echo 'selected';
                                                        }
                                                        ?>>
                                                        <?= htmlspecialchars($dosen->nama) ?>
                                                        <?php 
                                                        if (isset($penguji_recommendations['penguji2']) && 
                                                            $penguji_recommendations['penguji2']['id'] == $dosen->id) {
                                                            echo ' (Rekomendasi ✓)';
                                                        }
                                                        ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (isset($penguji_recommendations['penguji2'])): ?>
                                        <small class="text-success">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Rekomendasi: <?= htmlspecialchars($penguji_recommendations['penguji2']['nama']) ?> (sudah dipilih)
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Info Pembimbing -->
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-user-tie mr-2"></i>
                                <strong>Dosen Pembimbing:</strong> <?= htmlspecialchars($seminar->nama_pembimbing ?? 'N/A') ?>
                                <br><small class="text-muted">Dosen pembimbing otomatis menjadi bagian tim penguji</small>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="form-group mb-4">
                        <label for="catatan_kaprodi" class="form-label font-weight-bold">
                            Catatan Tambahan (Opsional)
                        </label>
                        <textarea class="form-control" 
                                  id="catatan_kaprodi" 
                                  name="catatan_kaprodi" 
                                  rows="3" 
                                  placeholder="Catatan khusus untuk seminar ini..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="<?= base_url('kaprodi/seminar_skripsi') ?>" 
                               class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Kembali
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button type="submit" class="btn btn-primary btn-block" id="btn-simpan">
                                <i class="fas fa-save mr-2"></i>
                                Tetapkan Jadwal & Kirim Notifikasi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CSS Styling -->
<style>
.table-borderless td {
    border: none;
    padding: 0.5rem 0.75rem;
}

.card {
    border-radius: 0.75rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.alert {
    border-radius: 0.5rem;
    border-left: 4px solid;
}

.alert-info { border-left-color: #17a2b8; }
.alert-warning { border-left-color: #ffc107; }
.alert-success { border-left-color: #28a745; }

.form-control {
    border-radius: 0.375rem;
    border: 1px solid #d1d3e2;
}

.form-control:focus {
    border-color: #5a5c69;
    box-shadow: 0 0 0 0.2rem rgba(90, 92, 105, 0.25);
}

.btn {
    border-radius: 0.375rem;
    font-weight: 400;
}

.card-header {
    border-radius: 0.75rem 0.75rem 0 0 !important;
}

.badge {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.375rem 0.75rem;
}

.border-primary { border-color: #4e73df !important; }
.border-warning { border-color: #f6c23e !important; }

@media (max-width: 768px) {
    .col-lg-4 {
        margin-bottom: 1rem;
    }
    
    .btn-block {
        margin-bottom: 0.5rem;
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-jadwal');
    const penguji1Select = document.getElementById('dosen_penguji1_id');
    const penguji2Select = document.getElementById('dosen_penguji2_id');
    const pembimbingId = '<?= $seminar->pembimbing_id ?? '' ?>';

    // Validasi dosen penguji tidak boleh sama
    function validatePenguji() {
        const penguji1 = penguji1Select.value;
        const penguji2 = penguji2Select.value;
        
        if (penguji1 && penguji2 && penguji1 === penguji2) {
            alert('Dosen Penguji 1 dan Penguji 2 tidak boleh sama!');
            penguji2Select.value = '';
            return false;
        }
        return true;
    }

    // Event listener untuk validasi
    penguji1Select.addEventListener('change', validatePenguji);
    penguji2Select.addEventListener('change', validatePenguji);

    // Validasi form sebelum submit
    form.addEventListener('submit', function(e) {
        // Validasi dosen penguji
        if (!validatePenguji()) {
            e.preventDefault();
            return false;
        }

        // Validasi tanggal tidak boleh masa lalu
        const tanggalSeminar = document.getElementById('tanggal_seminar').value;
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDate = tomorrow.toISOString().split('T')[0];
        
        if (tanggalSeminar < minDate) {
            e.preventDefault();
            alert('Tanggal seminar minimal H+1 dari sekarang!');
            return false;
        }

        // Konfirmasi sebelum submit
        const tanggalFormatted = new Date(tanggalSeminar).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const jamSeminar = document.getElementById('jam_seminar').value;
        const tempatSeminar = document.getElementById('tempat_seminar').value;
        const penguji1Name = penguji1Select.selectedOptions[0].text.replace(' (Rekomendasi ✓)', '');
        const penguji2Name = penguji2Select.selectedOptions[0].text.replace(' (Rekomendasi ✓)', '');
        
        const confirmMessage = `Yakin ingin menetapkan jadwal seminar ini?\n\n` +
                              `Mahasiswa: <?= htmlspecialchars($seminar->nama_mahasiswa ?? '') ?>\n` +
                              `Tanggal: ${tanggalFormatted}\n` +
                              `Waktu: ${jamSeminar} WIB\n` +
                              `Tempat: ${tempatSeminar}\n` +
                              `Penguji 1: ${penguji1Name}\n` +
                              `Penguji 2: ${penguji2Name}\n\n` +
                              `Setelah ditetapkan, notifikasi akan dikirim ke semua pihak.`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }

        // Disable tombol submit untuk mencegah double click
        const btnSubmit = document.getElementById('btn-simpan');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    });

    // Auto dismiss alerts
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
});
</script>