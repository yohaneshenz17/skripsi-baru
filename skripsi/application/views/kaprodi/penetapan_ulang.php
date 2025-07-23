<?php
// ============================================
// FILE: application/views/kaprodi/penetapan_ulang.php
// FORM PENETAPAN ULANG PEMBIMBING UNTUK PROPOSAL YANG DITOLAK DOSEN
// ============================================

ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-redo text-warning"></i> Form Penetapan Ulang Pembimbing
                </h3>
                <small class="text-muted">Penetapan pembimbing baru untuk proposal yang ditolak dosen sebelumnya</small>
            </div>
            <div class="card-body">
                
                <!-- Informasi Penolakan -->
                <div class="alert alert-danger">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle"></i> Dosen Pembimbing Menolak Penunjukan
                            </h5>
                            <p class="mb-2">
                                <strong>Dosen:</strong> <?= isset($proposal->nama_pembimbing_lama) ? $proposal->nama_pembimbing_lama : 'Tidak diketahui' ?><br>
                                <strong>Tanggal Penolakan:</strong> <?= isset($proposal->tanggal_respon_pembimbing) ? date('d F Y H:i', strtotime($proposal->tanggal_respon_pembimbing)) : '-' ?>
                            </p>
                            <?php if(isset($proposal->komentar_pembimbing) && $proposal->komentar_pembimbing): ?>
                            <div class="bg-white p-2 rounded">
                                <strong>Komentar Penolakan:</strong><br>
                                <em>"<?= $proposal->komentar_pembimbing ?>"</em>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Data Mahasiswa dan Proposal -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4><i class="ni ni-single-02"></i> Data Mahasiswa</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="30%"><strong>NIM</strong></td>
                                        <td>: <?= $proposal->nim ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama</strong></td>
                                        <td>: <?= $proposal->nama_mahasiswa ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>: <?= $proposal->email_mahasiswa ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Prodi</strong></td>
                                        <td>: <?= $proposal->nama_prodi ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis Kelamin</strong></td>
                                        <td>: <?= ucfirst($proposal->jenis_kelamin) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Telepon</strong></td>
                                        <td>: <?= $proposal->nomor_telepon ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="ni ni-paper-diploma"></i> Detail Proposal</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Judul Proposal:</h5>
                                <div class="alert alert-info mb-2">
                                    <strong><?= $proposal->judul ?></strong>
                                </div>
                                
                                <?php if (!empty($proposal->jenis_penelitian)): ?>
                                <p><strong>Jenis Penelitian:</strong> <?= $proposal->jenis_penelitian ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($proposal->lokasi_penelitian)): ?>
                                <p><strong>Lokasi Penelitian:</strong> <?= $proposal->lokasi_penelitian ?></p>
                                <?php endif; ?>
                                
                                <p><strong>Ringkasan:</strong></p>
                                <div class="text-muted" style="font-size: 0.9em; max-height: 100px; overflow-y: auto;">
                                    <?= nl2br(substr($proposal->ringkasan, 0, 300)) ?>
                                    <?php if (strlen($proposal->ringkasan) > 300): ?>
                                        <span class="text-primary">... [Selengkapnya]</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <!-- Form Penetapan Ulang -->
                <form method="post" action="<?= base_url() ?>kaprodi/simpan_penetapan_ulang" id="formPenetapanUlang">
                    <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                    
                    <h4 class="mb-3">
                        <i class="fas fa-user-check text-success"></i> Penetapan Dosen Pembimbing Baru
                    </h4>
                    
                    <?php if($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Alasan Penetapan Ulang -->
                    <div class="form-group">
                        <label class="form-control-label" for="alasan_penetapan_ulang">
                            <i class="fas fa-comment-dots"></i> Alasan Penetapan Ulang <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="alasan_penetapan_ulang" id="alasan_penetapan_ulang" rows="3" required 
                                  placeholder="Jelaskan alasan penetapan ulang pembimbing...">Dosen pembimbing sebelumnya (<?= isset($proposal->nama_pembimbing_lama) ? $proposal->nama_pembimbing_lama : '' ?>) menolak penunjukan. Menetapkan dosen pembimbing baru untuk melanjutkan proses bimbingan mahasiswa.</textarea>
                        <small class="form-text text-muted">Alasan ini akan dikirimkan kepada dosen pembimbing baru dan mahasiswa</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_pembimbing_baru_id">
                                    <i class="ni ni-single-02"></i> Dosen Pembimbing Baru <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="dosen_pembimbing_baru_id" id="dosen_pembimbing_baru_id" required>
                                    <option value="">-- Pilih Dosen Pembimbing Baru --</option>
                                    <?php if(isset($dosens) && !empty($dosens)): ?>
                                        <?php foreach($dosens as $d): ?>
                                            <option value="<?= $d->id ?>">
                                                <?= $d->nama ?>
                                                <?php if ($d->level == '4'): ?>
                                                    <small>(Kaprodi)</small>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada dosen tersedia</option>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    <strong>Catatan:</strong> Dosen yang menolak sebelumnya tidak ditampilkan
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_penguji1_id">
                                    <i class="ni ni-user-run"></i> Dosen Penguji 1 
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <select class="form-control" name="dosen_penguji1_id" id="dosen_penguji1_id">
                                    <option value="">-- Pilih Dosen Penguji 1 --</option>
                                    <?php if(isset($dosens) && !empty($dosens)): ?>
                                        <?php foreach($dosens as $d): ?>
                                            <option value="<?= $d->id ?>" <?= ($proposal->dosen_penguji_id == $d->id) ? 'selected' : '' ?>>
                                                <?= $d->nama ?>
                                                <?php if ($d->level == '4'): ?>
                                                    <small>(Kaprodi)</small>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">Bisa diubah atau tetap seperti semula</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-control-label" for="dosen_penguji2_id">
                                    <i class="ni ni-user-run"></i> Dosen Penguji 2 
                                    <small class="text-muted">(Opsional)</small>
                                </label>
                                <select class="form-control" name="dosen_penguji2_id" id="dosen_penguji2_id">
                                    <option value="">-- Pilih Dosen Penguji 2 --</option>
                                    <?php if(isset($dosens) && !empty($dosens)): ?>
                                        <?php foreach($dosens as $d): ?>
                                            <option value="<?= $d->id ?>" <?= ($proposal->dosen_penguji2_id == $d->id) ? 'selected' : '' ?>>
                                                <?= $d->nama ?>
                                                <?php if ($d->level == '4'): ?>
                                                    <small>(Kaprodi)</small>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">Bisa diubah atau tetap seperti semula</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Tambahan -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h6><i class="ni ni-bell-55"></i> Informasi Penting:</h6>
                                <ul class="mb-0">
                                    <li>Dosen pembimbing baru akan mendapat notifikasi email dan diminta memberikan persetujuan</li>
                                    <li>Mahasiswa akan mendapat notifikasi tentang perubahan dosen pembimbing</li>
                                    <li>Status proposal akan kembali ke "Menunggu Persetujuan Dosen Pembimbing"</li>
                                    <li>Riwayat penolakan dosen sebelumnya tetap tersimpan dalam sistem</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <a href="<?= base_url() ?>kaprodi/proposal#penetapan-ulang" class="btn btn-secondary">
                            <i class="ni ni-bold-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-redo"></i> Tetapkan Pembimbing Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 

ob_start(); 
?>
<script>
$(document).ready(function() {
    // Validasi frontend sebelum submit
    $('#formPenetapanUlang').on('submit', function(e) {
        var pembimbing_baru = $('#dosen_pembimbing_baru_id').val();
        var penguji1 = $('#dosen_penguji1_id').val();
        var penguji2 = $('#dosen_penguji2_id').val();
        var alasan = $('#alasan_penetapan_ulang').val().trim();
        
        // Cek pembimbing baru harus dipilih
        if (pembimbing_baru === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Dosen pembimbing baru harus dipilih!',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        // Cek alasan harus diisi
        if (alasan === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Alasan penetapan ulang harus diisi!',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        // Cek tidak ada yang sama (jika penguji juga diisi)
        var dosens = [pembimbing_baru, penguji1, penguji2].filter(val => val !== '');
        if (dosens.length !== new Set(dosens).size) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Pembimbing dan penguji harus orang yang berbeda!',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        // Konfirmasi sebelum submit
        e.preventDefault();
        var dosen_name = $('#dosen_pembimbing_baru_id option:selected').text();
        
        Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Penetapan Ulang',
            html: 'Apakah Anda yakin ingin menetapkan:<br>' +
                  '<strong>Pembimbing Baru:</strong> ' + dosen_name + '<br><br>' +
                  '<strong>Alasan:</strong> ' + alasan.substring(0, 100) + (alasan.length > 100 ? '...' : '') + '<br><br>' +
                  'Notifikasi akan dikirim ke dosen pembimbing baru dan mahasiswa.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tetapkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu, sedang menyimpan penetapan ulang',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                
                // Submit form
                $('#formPenetapanUlang')[0].submit();
            }
        });
    });
    
    // Real-time validation saat memilih dosen
    $('select[name^="dosen_"]').on('change', function() {
        var pembimbing = $('#dosen_pembimbing_baru_id').val();
        var penguji1 = $('#dosen_penguji1_id').val();
        var penguji2 = $('#dosen_penguji2_id').val();
        
        // Reset border colors
        $('select[name^="dosen_"]').removeClass('border-danger border-success');
        
        // Check for duplicates
        var values = [pembimbing, penguji1, penguji2].filter(val => val !== '');
        var hasDuplicate = values.length !== new Set(values).size;
        
        if (hasDuplicate) {
            $('select[name^="dosen_"]').addClass('border-danger');
        } else if (pembimbing) {
            $('select[name^="dosen_"]').addClass('border-success');
        }
    });
    
    // Auto focus ke dosen pembimbing baru
    $('#dosen_pembimbing_baru_id').focus();
});
</script>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => $title,
    'content' => $content,
    'script' => $script
]); 
?>