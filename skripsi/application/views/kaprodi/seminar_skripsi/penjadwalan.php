<!-- ====================================== -->
<!-- FILE: application/views/kaprodi/seminar_skripsi/penjadwalan.php -->
<!-- Form Penjadwalan Seminar & Penunjukan Dosen Penguji -->
<!-- ====================================== -->

<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calendar-plus"></i> Penjadwalan Seminar Skripsi</h1>
                    <p class="text-muted">Tetapkan Jadwal & Penunjukan Dosen Penguji</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('kaprodi/dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('kaprodi/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                        <li class="breadcrumb-item active">Penjadwalan</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Info Mahasiswa & Status -->
                <div class="col-md-4">
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-graduate mr-2"></i>
                                Informasi Mahasiswa
                            </h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td><?= $seminar->nama_mahasiswa ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIM:</strong></td>
                                    <td><?= $seminar->nim ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Pembimbing:</strong></td>
                                    <td><?= $seminar->nama_pembimbing ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Plagiarisme:</strong></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <?= $seminar->plagiarism_percentage ?>%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check mr-1"></i>
                                            Disetujui
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Enhanced Rekomendasi Dosen Penguji -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb mr-2"></i>
                                Rekomendasi Penguji
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($penguji_recommendations) && $penguji_recommendations['found']): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Rekomendasi Ditemukan!</strong> Berdasarkan seminar proposal 
                                    (<?= date('d/m/Y', strtotime($penguji_recommendations['tanggal_proposal'])) ?>):
                                </div>
                                
                                <?php if ($penguji_recommendations['penguji1']): ?>
                                    <div class="mb-3 p-3 border border-success rounded">
                                        <strong class="text-success">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            Rekomendasi Penguji 1:
                                        </strong><br>
                                        <span class="text-primary font-weight-bold">
                                            <?= $penguji_recommendations['penguji1']['nama'] ?>
                                        </span>
                                        <br><small class="text-muted"><?= $penguji_recommendations['penguji1']['email'] ?></small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($penguji_recommendations['penguji2']): ?>
                                    <div class="mb-3 p-3 border border-success rounded">
                                        <strong class="text-success">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            Rekomendasi Penguji 2:
                                        </strong><br>
                                        <span class="text-primary font-weight-bold">
                                            <?= $penguji_recommendations['penguji2']['nama'] ?>
                                        </span>
                                        <br><small class="text-muted"><?= $penguji_recommendations['penguji2']['email'] ?></small>
                                    </div>
                                <?php endif; ?>
                                
                                <small class="text-muted">
                                    <i class="fas fa-edit mr-1"></i>
                                    Anda dapat mengubah penguji sesuai kebutuhan di form bawah.
                                </small>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Tidak ada rekomendasi</strong> dari seminar proposal sebelumnya.
                                    Silakan pilih dosen penguji secara manual.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <!-- Form Penjadwalan -->
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                Form Penjadwalan Seminar
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Info Judul -->
                            <div class="alert alert-info">
                                <h5><i class="fas fa-book mr-2"></i>Judul Skripsi:</h5>
                                <p class="mb-0"><strong><?= $seminar->judul ?></strong></p>
                            </div>

                            <form action="<?= base_url('kaprodi/seminar_skripsi/simpan_jadwal') ?>" 
                                  method="post" 
                                  id="form-jadwal">
                                <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">

                                <!-- Jadwal Seminar -->
                                <div class="card card-outline card-primary mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-calendar mr-2"></i>
                                            Jadwal Seminar
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tanggal_seminar">
                                                        Tanggal Seminar <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" 
                                                           class="form-control" 
                                                           id="tanggal_seminar" 
                                                           name="tanggal_seminar" 
                                                           min="<?= date('Y-m-d') ?>"
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="jam_seminar">
                                                        Jam Seminar <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="time" 
                                                           class="form-control" 
                                                           id="jam_seminar" 
                                                           name="jam_seminar" 
                                                           required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tempat_seminar">
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
                                </div>

                                <!-- Penunjukan Dosen Penguji -->
                                <div class="card card-outline card-warning mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-users mr-2"></i>
                                            Penunjukan Dosen Penguji
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            <strong>Catatan:</strong> Sesuai kebijakan STK Santo Yakobus, dosen penguji 
                                            langsung ditunjuk tanpa perlu konfirmasi kesediaan.
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="dosen_penguji1_id">
                                                        Dosen Penguji 1 <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control select2" 
                                                            id="dosen_penguji1_id" 
                                                            name="dosen_penguji1_id" 
                                                            required>
                                                        <option value="">-- Pilih Dosen Penguji 1 --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php if ($dosen->id != $seminar->pembimbing_id): ?>
                                                                <option value="<?= $dosen->id ?>"
                                                                    <?php
                                                                    // Auto-select rekomendasi
                                                                    $selected = false;
                                                                    if (isset($penguji_recommendations) && 
                                                                        $penguji_recommendations['found'] && 
                                                                        $penguji_recommendations['penguji1'] && 
                                                                        $penguji_recommendations['penguji1']['id'] == $dosen->id) {
                                                                        $selected = true;
                                                                        echo 'selected';
                                                                    }
                                                                    ?>>
                                                                    <?= $dosen->nama ?>
                                                                    <?= $selected ? ' (Rekomendasi ✓)' : '' ?>
                                                                </option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?php if (isset($penguji_recommendations) && $penguji_recommendations['found'] && $penguji_recommendations['penguji1']): ?>
                                                        <small class="text-success">
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                            Rekomendasi: <?= $penguji_recommendations['penguji1']['nama'] ?> (sudah dipilih)
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="dosen_penguji2_id">
                                                        Dosen Penguji 2 <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-control select2" 
                                                            id="dosen_penguji2_id" 
                                                            name="dosen_penguji2_id" 
                                                            required>
                                                        <option value="">-- Pilih Dosen Penguji 2 --</option>
                                                        <?php foreach ($dosen_list as $dosen): ?>
                                                            <?php if ($dosen->id != $seminar->pembimbing_id): ?>
                                                                <option value="<?= $dosen->id ?>"
                                                                    <?php
                                                                    // Auto-select rekomendasi
                                                                    $selected = false;
                                                                    if (isset($penguji_recommendations) && 
                                                                        $penguji_recommendations['found'] && 
                                                                        $penguji_recommendations['penguji2'] && 
                                                                        $penguji_recommendations['penguji2']['id'] == $dosen->id) {
                                                                        $selected = true;
                                                                        echo 'selected';
                                                                    }
                                                                    ?>>
                                                                    <?= $dosen->nama ?>
                                                                    <?= $selected ? ' (Rekomendasi ✓)' : '' ?>
                                                                </option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <?php if (isset($penguji_recommendations) && $penguji_recommendations['found'] && $penguji_recommendations['penguji2']): ?>
                                                        <small class="text-success">
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                            Rekomendasi: <?= $penguji_recommendations['penguji2']['nama'] ?> (sudah dipilih)
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Info Pembimbing -->
                                        <div class="alert alert-info">
                                            <i class="fas fa-user-tie mr-2"></i>
                                            <strong>Dosen Pembimbing:</strong> <?= $seminar->nama_pembimbing ?>
                                            <br><small class="text-muted">Dosen pembimbing otomatis menjadi bagian tim penguji</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="<?= base_url('kaprodi/seminar_skripsi') ?>" 
                                           class="btn btn-secondary btn-block">
                                            <i class="fas fa-arrow-left mr-2"></i>
                                            Kembali
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary btn-block" id="btn-simpan">
                                            <i class="fas fa-save mr-2"></i>
                                            Tetapkan Jadwal
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JavaScript untuk validasi dan interaktivitas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-jadwal');
    const penguji1Select = document.getElementById('dosen_penguji1_id');
    const penguji2Select = document.getElementById('dosen_penguji2_id');
    const pembimbingId = '<?= $seminar->pembimbing_id ?>';

    // Inisialisasi Select2 jika tersedia
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // Validasi dosen penguji tidak boleh sama
    function validatePenguji() {
        const penguji1 = penguji1Select.value;
        const penguji2 = penguji2Select.value;
        
        if (penguji1 && penguji2 && penguji1 === penguji2) {
            alert('Dosen Penguji 1 dan Penguji 2 tidak boleh sama!');
            penguji2Select.value = '';
            if (typeof $.fn.select2 !== 'undefined') {
                $(penguji2Select).trigger('change');
            }
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
        const today = new Date().toISOString().split('T')[0];
        
        if (tanggalSeminar < today) {
            e.preventDefault();
            alert('Tanggal seminar tidak boleh masa lalu!');
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
        const penguji1Name = penguji1Select.selectedOptions[0].text.replace(' (Rekomendasi)', '');
        const penguji2Name = penguji2Select.selectedOptions[0].text.replace(' (Rekomendasi)', '');
        
        const confirmMessage = `Yakin ingin menetapkan jadwal seminar ini?\n\n` +
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
    });
});
</script>

<!-- CSS tambahan untuk styling -->
<style>
.card-outline {
    border-top: 3px solid;
}

.table-borderless td {
    border: none;
    padding: 0.3rem 0;
}

.select2-container--bootstrap4 .select2-selection {
    height: calc(2.25rem + 2px) !important;
}

.alert {
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #17a2b8;
}

.alert-warning {
    border-left-color: #ffc107;
}

.alert-success {
    border-left-color: #28a745;
}

.badge {
    font-size: 0.85em;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>