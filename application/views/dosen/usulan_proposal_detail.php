<?php $this->load->view('template/dosen', ['title' => $title, 'content' => $this->load->view('dosen/usulan_proposal_detail_content', $this, true), 'script' => '']); ?>

<!-- Content untuk usulan_proposal_detail_content.php -->

<!-- Alert Messages -->
<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-check"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <span class="alert-icon"><i class="fa fa-exclamation-triangle"></i></span>
    <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<!-- Header Info -->
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card bg-gradient-primary">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="text-white mb-0">Detail Penunjukan Pembimbing</h3>
                        <p class="text-white mt-2 mb-0">
                            Silakan review proposal dan berikan persetujuan atau penolakan penunjukan sebagai pembimbing.
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?= base_url('dosen/usulan_proposal') ?>" class="btn btn-sm btn-neutral">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Detail Mahasiswa -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Data Mahasiswa</h3>
            </div>
            <div class="card-body">
                <div class="media align-items-center mb-3">
                    <div class="avatar avatar-lg rounded-circle bg-primary">
                        <i class="ni ni-single-02 text-white"></i>
                    </div>
                    <div class="media-body ml-3">
                        <h4 class="mb-0"><?= $proposal->nama_mahasiswa ?></h4>
                        <p class="text-muted mb-0">NIM: <?= $proposal->nim ?></p>
                    </div>
                </div>
                
                <hr class="my-3">
                
                <div class="row">
                    <div class="col-12">
                        <strong>Program Studi:</strong>
                        <p class="text-muted"><?= $proposal->nama_prodi ?></p>
                        
                        <strong>Tempat, Tanggal Lahir:</strong>
                        <p class="text-muted"><?= $proposal->tempat_lahir ?>, <?= date('d F Y', strtotime($proposal->tanggal_lahir)) ?></p>
                        
                        <strong>Jenis Kelamin:</strong>
                        <p class="text-muted"><?= $proposal->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
                        
                        <strong>Alamat:</strong>
                        <p class="text-muted"><?= $proposal->alamat ?></p>
                        
                        <strong>No. Telepon:</strong>
                        <p class="text-muted">
                            <i class="fa fa-phone text-primary"></i> 
                            <a href="tel:<?= $proposal->nomor_telepon ?>"><?= $proposal->nomor_telepon ?></a>
                        </p>
                        
                        <strong>Email:</strong>
                        <p class="text-muted">
                            <i class="fa fa-envelope text-primary"></i> 
                            <a href="mailto:<?= $proposal->email_mahasiswa ?>"><?= $proposal->email_mahasiswa ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detail Proposal -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Detail Proposal Skripsi</h3>
                    </div>
                    <div class="col-auto">
                        <?php if($proposal->file_proposal): ?>
                        <a href="<?= base_url('cdn/vendor/proposal/' . $proposal->file_proposal) ?>" 
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fa fa-download"></i> Download Proposal
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <strong>Judul Proposal:</strong>
                        <h5 class="text-primary mb-3"><?= $proposal->judul ?></h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Jenis Penelitian:</strong>
                                <p class="text-muted">
                                    <span class="badge badge-secondary"><?= $proposal->jenis_penelitian ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <strong>Lokasi Penelitian:</strong>
                                <p class="text-muted">
                                    <i class="fa fa-map-marker-alt text-danger"></i> <?= $proposal->lokasi_penelitian ?>
                                </p>
                            </div>
                        </div>
                        
                        <strong>Uraian Masalah:</strong>
                        <div class="bg-light p-3 rounded">
                            <p class="text-dark mb-0"><?= nl2br($proposal->uraian_masalah) ?></p>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Tanggal Pengajuan:</strong>
                                <p class="text-muted"><?= date('d F Y H:i', strtotime($proposal->created_at)) ?> WIT</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Tanggal Penetapan:</strong>
                                <p class="text-muted">
                                    <?= $proposal->tanggal_penetapan ? date('d F Y H:i', strtotime($proposal->tanggal_penetapan)) . ' WIT' : '-' ?>
                                </p>
                            </div>
                        </div>
                        
                        <strong>Status Kaprodi:</strong>
                        <p>
                            <span class="badge badge-success">
                                <i class="fa fa-check"></i> Disetujui oleh <?= $proposal->nama_kaprodi ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Persetujuan -->
        <div class="card mt-4">
            <div class="card-header bg-primary">
                <h3 class="mb-0 text-white">
                    <i class="fa fa-user-check"></i> Persetujuan Penunjukan Pembimbing
                </h3>
            </div>
            <div class="card-body">
                <?php if($proposal->status_pembimbing && $proposal->status_pembimbing != '0'): ?>
                    <!-- Jika sudah direspon -->
                    <div class="alert <?= $proposal->status_pembimbing == '1' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                        <span class="alert-icon">
                            <i class="fa <?= $proposal->status_pembimbing == '1' ? 'fa-check' : 'fa-times' ?>"></i>
                        </span>
                        <span class="alert-text">
                            <strong>Sudah Direspon:</strong> 
                            Anda telah <?= $proposal->status_pembimbing == '1' ? 'menyetujui' : 'menolak' ?> penunjukan ini pada 
                            <?= date('d F Y H:i', strtotime($proposal->tanggal_respon_pembimbing)) ?> WIT
                        </span>
                    </div>
                    
                    <?php if($proposal->komentar_pembimbing): ?>
                    <div class="form-group">
                        <label>Komentar Anda:</label>
                        <div class="bg-light p-3 rounded">
                            <?= nl2br($proposal->komentar_pembimbing) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Form untuk memberikan respon -->
                    <form action="<?= base_url('dosen/usulan_proposal/proses_persetujuan') ?>" method="POST">
                        <input type="hidden" name="proposal_id" value="<?= $proposal->id ?>">
                        
                        <div class="form-group">
                            <label class="form-control-label">Keputusan Persetujuan *</label>
                            <div class="custom-control custom-radio">
                                <input name="status_pembimbing" class="custom-control-input" id="setuju" type="radio" value="1" required>
                                <label class="custom-control-label" for="setuju">
                                    <span class="text-success"><i class="fa fa-check"></i> <strong>Setuju</strong> menjadi pembimbing</span>
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input name="status_pembimbing" class="custom-control-input" id="tolak" type="radio" value="2" required>
                                <label class="custom-control-label" for="tolak">
                                    <span class="text-danger"><i class="fa fa-times"></i> <strong>Tolak</strong> penunjukan sebagai pembimbing</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" id="komentar-group" style="display: none;">
                            <label class="form-control-label">Alasan Penolakan *</label>
                            <textarea class="form-control" name="komentar_pembimbing" rows="4" 
                                      placeholder="Jelaskan alasan penolakan (wajib diisi jika menolak)"></textarea>
                            <small class="form-text text-muted">
                                Alasan penolakan akan dikirim ke mahasiswa dan kaprodi untuk menentukan pembimbing pengganti.
                            </small>
                        </div>
                        
                        <div class="form-group" id="komentar-setuju-group" style="display: none;">
                            <label class="form-control-label">Catatan (Opsional)</label>
                            <textarea class="form-control" name="komentar_pembimbing" rows="3" 
                                      placeholder="Tambahkan catatan atau pesan untuk mahasiswa (opsional)"></textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <a href="<?= base_url('dosen/usulan_proposal') ?>" class="btn btn-secondary btn-block">
                                    <i class="fa fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('Apakah Anda yakin dengan keputusan ini?')">
                                    <i class="fa fa-paper-plane"></i> Kirim Keputusan
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Info Panel Workflow -->
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card bg-gradient-info">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="text-white mb-0">📋 Workflow Information</h4>
                        <p class="text-white mt-2 mb-0">
                            <strong>Phase 1:</strong> Setelah Anda menyetujui, mahasiswa akan masuk ke <strong>Phase 2: Bimbingan</strong>. 
                            Jika menolak, kaprodi akan memilih pembimbing yang baru.
                        </p>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fa fa-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioButtons = document.querySelectorAll('input[name="status_pembimbing"]');
    const komentarGroup = document.getElementById('komentar-group');
    const komentarSetujuGroup = document.getElementById('komentar-setuju-group');
    
    radioButtons.forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === '2') { // Tolak
                komentarGroup.style.display = 'block';
                komentarSetujuGroup.style.display = 'none';
                komentarGroup.querySelector('textarea').required = true;
                komentarSetujuGroup.querySelector('textarea').required = false;
            } else if (this.value === '1') { // Setuju
                komentarGroup.style.display = 'none';
                komentarSetujuGroup.style.display = 'block';
                komentarGroup.querySelector('textarea').required = false;
                komentarSetujuGroup.querySelector('textarea').required = false;
            }
        });
    });
});
</script>