<!-- Header -->
<div class="card bg-warning text-white mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-9">
                <h4 class="text-white mb-0">✏️ Edit Jurnal Bimbingan</h4>
                <p class="mb-0 mt-2">
                    <strong>Pertemuan ke-<?= $jurnal->pertemuan_ke ?></strong> | 
                    Tanggal: <?= date('d F Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                </p>
            </div>
            <div class="col-lg-3 text-end">
                <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Form Edit Jurnal -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit text-warning"></i> 
                    Edit Jurnal Bimbingan Pertemuan ke-<?= $jurnal->pertemuan_ke ?>
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('mahasiswa/bimbingan/edit_jurnal/' . $jurnal->id) ?>" method="POST" id="formEditJurnal">
                    
                    <!-- Info Status -->
                    <?php if($jurnal->status_validasi == '2'): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Jurnal Perlu Revisi:</strong> Dosen pembimbing meminta revisi pada jurnal ini. 
                        Silakan lakukan perbaikan sesuai catatan dosen, kemudian submit ulang untuk validasi.
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Catatan:</strong> Anda hanya dapat mengedit jurnal yang belum divalidasi oleh dosen pembimbing.
                        Jurnal yang sudah divalidasi tidak dapat diubah.
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Pertemuan ke- *</label>
                                <input type="number" class="form-control" name="pertemuan_ke" 
                                       value="<?= $jurnal->pertemuan_ke ?>" min="1" required readonly>
                                <small class="form-text text-muted">Nomor pertemuan tidak dapat diubah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" 
                                       value="<?= $jurnal->tanggal_bimbingan ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" rows="4" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini"><?= $jurnal->materi_bimbingan ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" rows="3" 
                                  placeholder="Tugas atau tindak lanjut yang diberikan dosen (opsional)"><?= $jurnal->tindak_lanjut ?></textarea>
                    </div>

                    <?php if($jurnal->catatan_dosen): ?>
                    <div class="mb-3">
                        <label class="form-label">Catatan Dosen <span class="badge bg-info">Read Only</span></label>
                        <div class="alert alert-light border">
                            <i class="fas fa-comment text-primary"></i>
                            <?= nl2br(htmlspecialchars($jurnal->catatan_dosen)) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Info Jurnal -->
                    <div class="border-top pt-3 mt-4">
                        <div class="row text-sm text-muted">
                            <div class="col-md-6">
                                <strong>Dibuat:</strong> <?= date('d F Y H:i', strtotime($jurnal->created_at)) ?> WIT
                            </div>
                            <div class="col-md-6">
                                <strong>Terakhir diupdate:</strong> <?= date('d F Y H:i', strtotime($jurnal->updated_at)) ?> WIT
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-secondary me-2" onclick="history.back()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> 
                            <?= $jurnal->status_validasi == '2' ? 'Submit Revisi' : 'Update Jurnal' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Info Panel -->
<div class="row mt-4">
    <div class="col-lg-8 mx-auto">
        <div class="card bg-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-lightbulb fa-lg"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h6 class="mb-1">Tips Edit Jurnal Bimbingan</h6>
                        <p class="mb-0 small">
                            <strong>Materi yang baik:</strong> Jelaskan secara spesifik topik yang dibahas (Bab berapa, bagian apa).
                            <strong>Tindak lanjut:</strong> Catat tugas atau perbaikan yang diminta dosen untuk pertemuan berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Validation untuk form
    $("#formEditJurnal").on("submit", function(e) {
        var materi = $("textarea[name='materi_bimbingan']").val().trim();
        var tanggal = $("input[name='tanggal_bimbingan']").val();
        
        if (materi.length < 10) {
            e.preventDefault();
            alert("Materi bimbingan harus minimal 10 karakter.");
            $("textarea[name='materi_bimbingan']").focus();
            return false;
        }
        
        if (!tanggal) {
            e.preventDefault();
            alert("Tanggal bimbingan wajib diisi.");
            $("input[name='tanggal_bimbingan']").focus();
            return false;
        }
        
        // Konfirmasi sebelum submit
        return confirm("Apakah Anda yakin ingin menyimpan perubahan jurnal bimbingan ini?");
    });
    
    // Auto-resize textarea
    $("textarea").on("input", function() {
        this.style.height = "auto";
        this.style.height = (this.scrollHeight) + "px";
    });
});
</script>