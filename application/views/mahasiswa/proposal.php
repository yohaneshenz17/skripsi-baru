<div class="row">
    <div class="col-lg-12">
        <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>
        
        <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-icon"><i class="ni ni-fat-remove"></i></span>
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>
        
        <?php if(!$proposal): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Ajukan Proposal Skripsi</h3>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url() ?>mahasiswa/proposal/ajukan">
                    <div class="form-group">
                        <label class="form-control-label">Judul Proposal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" required placeholder="Masukkan judul proposal Anda" maxlength="100">
                        <small class="text-muted">Maksimal 100 karakter.</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Ringkasan Proposal <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="ringkasan" rows="5" required placeholder="Jelaskan ringkasan proposal Anda (latar belakang, tujuan, metode)" maxlength="5000"></textarea>
                        <small class="text-muted">Jelaskan secara singkat namun jelas.</small>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <strong>Informasi Penting:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Anda hanya perlu mengajukan judul dan ringkasan proposal.</li>
                            <li>Dosen Pembimbing dan Penguji akan ditetapkan oleh Ketua Program Studi (Kaprodi).</li>
                            <li>Pastikan data yang Anda masukkan sudah benar sebelum mengirim.</li>
                        </ul>
                    </div>
                    
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-paper-plane"></i> Kirim Ajuan Proposal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Status Proposal Anda</h3>
                    </div>
                    <div class="col text-right">
                        <?php if($proposal->status == '0'): ?>
                            <span class="badge badge-warning">Menunggu Penetapan Kaprodi</span>
                        <?php elseif($proposal->status == '1'): ?>
                            <span class="badge badge-success">Disetujui & Ditetapkan</span>
                        <?php elseif($proposal->status == '2'): ?>
                            <span class="badge badge-danger">Ditolak</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h4>Judul Proposal:</h4>
                <p class="text-primary font-weight-bold"><?= $proposal->judul ?></p>
                
                <h4>Ringkasan:</h4>
                <div class="alert alert-secondary">
                    <?= nl2br($proposal->ringkasan) ?>
                </div>
                
                <?php if($proposal->dosen_id): ?>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h4>Dosen Pembimbing:</h4>
                        <p class="text-success">
                            <i class="fa fa-user-tie"></i> <?= $proposal->nama_pembimbing ?: 'Belum ditetapkan' ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h4>Tim Dosen Penguji:</h4>
                        <p>
                            <span class="text-info"><i class="fa fa-user-check"></i> Penguji 1: <?= $proposal->nama_penguji1 ?: 'Belum ditetapkan' ?></span><br>
                            <span class="text-warning"><i class="fa fa-user-check"></i> Penguji 2: <?= $proposal->nama_penguji2 ?: 'Belum ditetapkan' ?></span>
                        </p>
                    </div>
                </div>
                
                <?php if($proposal->tanggal_penetapan): ?>
                <div class="alert alert-success mt-3">
                    <i class="fa fa-check-circle"></i> Tim dosen telah ditetapkan pada tanggal 
                    <strong><?= date('d F Y H:i', strtotime($proposal->tanggal_penetapan)) ?></strong>.
                </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="<?= base_url() ?>mahasiswa/konsultasi" class="btn btn-primary">
                        <i class="fa fa-comments"></i> Lihat Bimbingan
                    </a>
                </div>
                
                <?php else: ?>
                <div class="alert alert-info mt-4">
                    <i class="fa fa-hourglass-half"></i> <strong>Menunggu Persetujuan:</strong> Proposal Anda sedang ditinjau oleh Ketua Program Studi untuk penetapan dosen pembimbing dan penguji. 
                    Anda akan menerima notifikasi jika sudah ditetapkan.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>