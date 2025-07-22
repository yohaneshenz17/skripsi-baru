<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// Prepare data untuk template
$template_data = array(
    'title' => 'Edit Jurnal Bimbingan',
    'content' => '',
    'script' => ''
);

// Load content view sebagai string
ob_start();
?>

<!-- Alert Messages -->
<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Header -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-warning">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">✏️ Edit Jurnal Bimbingan</h3>
                        <p class="text-white mt-2 mb-0">
                            <strong>Pertemuan ke-<?= $jurnal->pertemuan_ke ?></strong> | 
                            Tanggal: <?= date('d F Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('mahasiswa/bimbingan') ?>" class="btn btn-sm btn-neutral">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Edit Jurnal -->
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fa fa-edit text-warning"></i> 
                    Edit Jurnal Bimbingan Pertemuan ke-<?= $jurnal->pertemuan_ke ?>
                </h3>
            </div>
            <div class="card-body">
                <form action="<?= base_url('mahasiswa/bimbingan/edit_jurnal/' . $jurnal->id) ?>" method="POST" id="formEditJurnal">
                    
                    <!-- Info Status -->
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Catatan:</strong> Anda hanya dapat mengedit jurnal yang belum divalidasi oleh dosen pembimbing.
                        Jurnal yang sudah divalidasi tidak dapat diubah.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pertemuan ke- *</label>
                                <input type="number" class="form-control" name="pertemuan_ke" 
                                       value="<?= $jurnal->pertemuan_ke ?>" min="1" required readonly>
                                <small class="form-text text-muted">Nomor pertemuan tidak dapat diubah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" 
                                       value="<?= $jurnal->tanggal_bimbingan ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" rows="4" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini"><?= $jurnal->materi_bimbingan ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" rows="3" 
                                  placeholder="Tugas atau tindak lanjut yang diberikan dosen (opsional)"><?= $jurnal->tindak_lanjut ?></textarea>
                    </div>

                    <?php if($jurnal->catatan_dosen): ?>
                    <div class="form-group">
                        <label>Catatan Dosen <span class="badge badge-info">Read Only</span></label>
                        <div class="alert alert-light border">
                            <i class="fa fa-comment text-primary"></i>
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
                        <button type="button" class="btn btn-secondary mr-2" onclick="history.back()">
                            <i class="fa fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-save"></i> Update Jurnal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Info Panel -->
<div class="row mt-4">
    <div class="col-lg-8 offset-lg-2">
        <div class="card bg-gradient-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="icon icon-shape icon-lg bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fa fa-lightbulb"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h6 class="mb-1">Tips Edit Jurnal Bimbingan</h6>
                        <p class="mb-0 text-sm">
                            <strong>Materi yang baik:</strong> Jelaskan secara spesifik topik yang dibahas (Bab berapa, bagian apa).
                            <strong>Tindak lanjut:</strong> Catat tugas atau perbaikan yang diminta dosen untuk pertemuan berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$template_data['content'] = ob_get_clean();

// Add JavaScript untuk validation
$template_data['script'] = '
<script>
$(document).ready(function() {
    // Validation untuk form
    $("#formEditJurnal").on("submit", function(e) {
        var materi = $("textarea[name=\'materi_bimbingan\']").val().trim();
        var tanggal = $("input[name=\'tanggal_bimbingan\']").val();
        
        if (materi.length < 10) {
            e.preventDefault();
            alert("Materi bimbingan harus minimal 10 karakter.");
            $("textarea[name=\'materi_bimbingan\']").focus();
            return false;
        }
        
        if (!tanggal) {
            e.preventDefault();
            alert("Tanggal bimbingan wajib diisi.");
            $("input[name=\'tanggal_bimbingan\']").focus();
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
';

// Load template
$this->load->view('template/mahasiswa', $template_data);
?>