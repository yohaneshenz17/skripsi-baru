<?php if (isset($waiting_kaprodi)): ?>
<!-- STATUS 1: PROPOSAL BELUM DIREVIEW KAPRODI -->
<div class="card status-card">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-search fa-4x text-info"></i>
        </div>
        <h3 class="text-info">📋 Menunggu Review Kaprodi</h3>
        <p class="text-muted">
            Proposal Anda sedang ditinjau oleh <strong>Kaprodi</strong>. 
            Setelah disetujui, kaprodi akan menetapkan dosen pembimbing untuk Anda.
        </p>
        <div class="mt-3">
            <strong>Judul:</strong> <?= $waiting_kaprodi->judul ?><br>
            <strong>Tanggal Pengajuan:</strong> <?= isset($waiting_kaprodi->created_at) && $waiting_kaprodi->created_at ? date('d F Y', strtotime($waiting_kaprodi->created_at)) : '-' ?>
        </div>
        <div class="mt-4">
            <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                <i class="fas fa-eye"></i> Lihat Status Proposal
            </a>
        </div>
    </div>
</div>

<?php elseif (isset($rejected_kaprodi)): ?>
<!-- STATUS 2: PROPOSAL DITOLAK KAPRODI -->
<div class="card status-card border-danger">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
        </div>
        <h3 class="text-danger">❌ Proposal Ditolak Kaprodi</h3>
        <p class="text-muted">
            Proposal Anda telah direview oleh <strong>Kaprodi</strong> dan memerlukan perbaikan. 
            Silakan lakukan revisi sesuai komentar yang diberikan.
        </p>
        <div class="mt-3">
            <strong>Judul:</strong> <?= $rejected_kaprodi->judul ?><br>
            <strong>Tanggal Review:</strong> <?= isset($rejected_kaprodi->tanggal_review_kaprodi) && $rejected_kaprodi->tanggal_review_kaprodi ? date('d F Y', strtotime($rejected_kaprodi->tanggal_review_kaprodi)) : '-' ?>
        </div>
        <?php if(isset($rejected_kaprodi->komentar_kaprodi) && $rejected_kaprodi->komentar_kaprodi): ?>
        <div class="alert alert-warning mt-3">
            <strong>Komentar Kaprodi:</strong><br>
            <?= $rejected_kaprodi->komentar_kaprodi ?>
        </div>
        <?php endif; ?>
        <div class="mt-4">
            <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Revisi Proposal
            </a>
        </div>
    </div>
</div>

<?php elseif (isset($pending_proposal)): ?>
<!-- STATUS 3: MENUNGGU PERSETUJUAN DOSEN PEMBIMBING -->
<div class="card status-card border-warning">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-hourglass-half fa-4x text-warning"></i>
        </div>
        <h3 class="text-warning">⏳ Menunggu Persetujuan Dosen Pembimbing</h3>
        <p class="text-muted">
            <strong>Kaprodi</strong> telah menetapkan <strong><?= isset($pending_proposal->nama_dosen) ? $pending_proposal->nama_dosen : 'Dosen' ?></strong> sebagai dosen pembimbing Anda. 
            Saat ini menunggu persetujuan dari dosen yang bersangkutan.
        </p>
        <div class="mt-3">
            <strong>Dosen Pembimbing:</strong> <?= isset($pending_proposal->nama_dosen) ? $pending_proposal->nama_dosen : 'Belum ditetapkan' ?><br>
            <strong>Tanggal Penetapan:</strong> <?= isset($pending_proposal->tanggal_penetapan) && $pending_proposal->tanggal_penetapan ? date('d F Y', strtotime($pending_proposal->tanggal_penetapan)) : '-' ?>
        </div>
        <div class="mt-4">
            <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                <i class="fas fa-eye"></i> Lihat Status Proposal
            </a>
        </div>
    </div>
</div>

<?php elseif (isset($rejected_dosen)): ?>
<!-- STATUS 4: DOSEN PEMBIMBING MENOLAK -->
<div class="card status-card border-danger">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-user-slash fa-4x text-danger"></i>
        </div>
        <h3 class="text-danger">❌ Dosen Pembimbing Menolak</h3>
        <p class="text-muted">
            <strong><?= isset($rejected_dosen->nama_dosen) ? $rejected_dosen->nama_dosen : 'Dosen' ?></strong> menolak penunjukan sebagai pembimbing. 
            <strong>Kaprodi</strong> akan menetapkan dosen pembimbing yang baru untuk Anda.
        </p>
        <div class="mt-3">
            <strong>Dosen:</strong> <?= isset($rejected_dosen->nama_dosen) ? $rejected_dosen->nama_dosen : 'Tidak diketahui' ?><br>
            <strong>Tanggal Respon:</strong> <?= isset($rejected_dosen->tanggal_respon_pembimbing) && $rejected_dosen->tanggal_respon_pembimbing ? date('d F Y', strtotime($rejected_dosen->tanggal_respon_pembimbing)) : '-' ?>
        </div>
        <?php if(isset($rejected_dosen->komentar_pembimbing) && $rejected_dosen->komentar_pembimbing): ?>
        <div class="alert alert-warning mt-3">
            <strong>Komentar Dosen:</strong><br>
            <?= $rejected_dosen->komentar_pembimbing ?>
        </div>
        <?php endif; ?>
        <div class="mt-4">
            <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                <i class="fas fa-eye"></i> Lihat Status Proposal
            </a>
        </div>
    </div>
</div>

<?php elseif (!isset($proposal)): ?>
<!-- STATUS 5: BELUM ADA PROPOSAL -->
<div class="card status-card">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-file-alt fa-4x text-muted"></i>
        </div>
        <h4>Belum Ada Proposal</h4>
        <p class="text-muted">
            Anda belum mengajukan proposal tugas akhir. 
            Silakan ajukan proposal terlebih dahulu untuk memulai proses bimbingan.
        </p>
        <div class="mt-4">
            <a href="<?= base_url('mahasiswa/proposal') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajukan Proposal
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- STATUS 6: BIMBINGAN AKTIF -->

<!-- Info Panel -->
<div class="card bg-success text-white mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-9">
                <h4 class="text-white mb-0">📚 Bimbingan Skripsi - Phase 2</h4>
                <p class="mb-0 mt-2">
                    <strong>Dosen Pembimbing:</strong> <?= isset($proposal->nama_dosen) ? $proposal->nama_dosen : 'Belum ditetapkan' ?> | 
                    <strong>Judul:</strong> <?= substr($proposal->judul, 0, 50) ?><?= strlen($proposal->judul) > 50 ? '...' : '' ?>
                </p>
            </div>
            <div class="col-lg-3 text-end">
                <button type="button" class="btn btn-light btn-sm" onclick="tambahJurnalBimbingan()">
                    <i class="fas fa-plus"></i> Tambah Jurnal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2">
                    <i class="fas fa-calendar fa-2x"></i>
                </div>
                <h5 class="card-title">Total Pertemuan</h5>
                <h2 class="text-primary mb-0"><?= $total_bimbingan ?></h2>
                <small class="text-muted">Target: 16 pertemuan</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2">
                    <i class="fas fa-check fa-2x"></i>
                </div>
                <h5 class="card-title">Tervalidasi</h5>
                <h2 class="text-success mb-0"><?= $bimbingan_tervalidasi ?></h2>
                <small class="text-muted">
                    <?= $total_bimbingan > 0 ? round(($bimbingan_tervalidasi/$total_bimbingan)*100, 1) : 0 ?>% dari total
                </small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <h5 class="card-title">Pending</h5>
                <h2 class="text-warning mb-0"><?= $bimbingan_pending ?></h2>
                <small class="text-muted">Menunggu validasi</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2">
                    <i class="fas fa-chart-pie fa-2x"></i>
                </div>
                <h5 class="card-title">Progress</h5>
                <h2 class="text-info mb-0">
                    <?= $total_bimbingan >= 16 ? '100' : round(($total_bimbingan/16)*100, 1) ?>%
                </h2>
                <small class="text-muted">
                    <?php if($siap_seminar): ?>
                        Siap seminar proposal
                    <?php else: ?>
                        Minimal 8 untuk seminar
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Progress Bimbingan</h5>
    </div>
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="h4 mb-0"><?= $bimbingan_tervalidasi ?>/16</span>
            </div>
            <div class="col">
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: <?= min(($bimbingan_tervalidasi/16)*100, 100) ?>%"></div>
                </div>
            </div>
            <div class="col-auto">
                <?php if($bimbingan_tervalidasi >= 16): ?>
                    <span class="badge bg-success">Lengkap</span>
                <?php elseif($bimbingan_tervalidasi >= 8): ?>
                    <span class="badge bg-info">Siap Seminar Proposal</span>
                <?php else: ?>
                    <span class="badge bg-warning">Kurang <?= 8 - $bimbingan_tervalidasi ?> untuk seminar</span>
                <?php endif; ?>
            </div>
        </div>
        <small class="text-muted mt-2 d-block">
            Minimal 8 pertemuan tervalidasi untuk mengajukan seminar proposal, 
            16 pertemuan untuk melengkapi seluruh fase bimbingan.
        </small>
    </div>
</div>

<!-- Info Dosen Pembimbing & Jurnal Bimbingan -->
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Dosen Pembimbing</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user text-white fa-2x"></i>
                    </div>
                </div>
                
                <div class="text-center">
                    <h5><?= isset($proposal->nama_dosen) ? $proposal->nama_dosen : 'Dosen Pembimbing' ?></h5>
                    <p class="text-muted">Dosen Pembimbing</p>
                </div>
                
                <hr>
                
                <?php if(isset($proposal->email_dosen) && $proposal->email_dosen): ?>
                <p class="mb-2">
                    <i class="fas fa-envelope text-primary"></i> 
                    <a href="mailto:<?= $proposal->email_dosen ?>"><?= $proposal->email_dosen ?></a>
                </p>
                <?php endif; ?>
                
                <?php if(isset($proposal->telepon_dosen) && $proposal->telepon_dosen): ?>
                <p class="mb-2">
                    <i class="fas fa-phone text-primary"></i> 
                    <a href="tel:<?= $proposal->telepon_dosen ?>"><?= $proposal->telepon_dosen ?></a>
                </p>
                <?php endif; ?>
                
                <p class="mb-0">
                    <i class="fas fa-file-alt text-primary"></i> 
                    <strong>Proposal:</strong><br>
                    <small class="text-muted"><?= isset($proposal->judul) ? $proposal->judul : 'Tidak ada judul' ?></small>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Jurnal Bimbingan -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Jurnal Bimbingan</h5>
                    <small class="text-muted">Riwayat pertemuan bimbingan dengan dosen pembimbing</small>
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="tambahJurnalBimbingan()">
                        <i class="fas fa-plus"></i> Tambah Jurnal
                    </button>
                    <?php if(!empty($jurnal_bimbingan)): ?>
                    <a href="<?= base_url('mahasiswa/bimbingan/export_jurnal') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download"></i> Export
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if(!empty($jurnal_bimbingan)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Pertemuan</th>
                                <th>Tanggal</th>
                                <th>Materi</th>
                                <th>Status</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($jurnal_bimbingan as $jurnal): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary">Ke-<?= $jurnal->pertemuan_ke ?></span>
                                </td>
                                <td>
                                    <span class="small"><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></span>
                                    <br>
                                    <small class="text-muted"><?= date('H:i', strtotime($jurnal->created_at)) ?> WIT</small>
                                </td>
                                <td>
                                    <span class="small"><?= substr($jurnal->materi_bimbingan, 0, 40) ?><?= strlen($jurnal->materi_bimbingan) > 40 ? '...' : '' ?></span>
                                    <?php if(isset($jurnal->tindak_lanjut) && $jurnal->tindak_lanjut): ?>
                                    <br>
                                    <small class="text-info"><strong>TL:</strong> <?= substr($jurnal->tindak_lanjut, 0, 30) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($jurnal->status_validasi == '1'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Tervalidasi
                                        </span>
                                        <?php if(isset($jurnal->catatan_dosen) && $jurnal->catatan_dosen): ?>
                                        <br>
                                        <small class="text-muted" title="<?= $jurnal->catatan_dosen ?>">
                                            <i class="fas fa-comment"></i> Ada catatan
                                        </small>
                                        <?php endif; ?>
                                    <?php elseif($jurnal->status_validasi == '2'): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-edit"></i> Perlu Revisi
                                        </span>
                                        <?php if(isset($jurnal->catatan_dosen) && $jurnal->catatan_dosen): ?>
                                        <br>
                                        <small class="text-warning" title="<?= $jurnal->catatan_dosen ?>">
                                            <i class="fas fa-exclamation-triangle"></i> Lihat catatan dosen
                                        </small>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-danger">
                                            <i class="fas fa-arrow-right"></i> <strong>Silakan revisi</strong>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                onclick="lihatDetailJurnal(<?= $jurnal->id ?>)" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if($jurnal->status_validasi == '0' || $jurnal->status_validasi == '2'): ?>
                                        <a href="<?= base_url('mahasiswa/bimbingan/edit_jurnal/' . $jurnal->id) ?>" 
                                           class="btn btn-outline-warning btn-sm" title="<?= $jurnal->status_validasi == '2' ? 'Revisi Jurnal' : 'Edit' ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($jurnal->status_validasi == '0'): ?>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete" 
                                                onclick="hapusJurnal(<?= $jurnal->id ?>)" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada jurnal bimbingan</h5>
                    <p class="text-muted">Mulai tambahkan jurnal bimbingan dengan dosen pembimbing Anda.</p>
                    <button type="button" class="btn btn-primary" onclick="tambahJurnalBimbingan()">
                        <i class="fas fa-plus"></i> Tambah Jurnal Pertama
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Jurnal -->
<div class="modal fade" id="modalTambahJurnal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('mahasiswa/bimbingan/tambah_jurnal') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jurnal Bimbingan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Info Mahasiswa -->
                    <div class="mb-3">
                        <label class="form-label">Mahasiswa</label>
                        <input type="text" class="form-control" value="<?= $this->session->userdata('nama') ?>" readonly>
                        <small class="form-text text-muted">Jurnal bimbingan akan tercatat atas nama Anda</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Pertemuan ke- *</label>
                                <input type="number" class="form-control" name="pertemuan_ke" min="1" 
                                       value="<?= isset($total_bimbingan) ? ($total_bimbingan + 1) : 1 ?>" required>
                                <small class="form-text text-muted">Nomor urut pertemuan bimbingan</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Bimbingan *</label>
                                <input type="date" class="form-control" name="tanggal_bimbingan" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Durasi (menit)</label>
                                <input type="number" class="form-control" name="durasi_bimbingan" min="15" max="180" placeholder="60">
                                <small class="form-text text-muted">Estimasi durasi (opsional)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Materi Bimbingan *</label>
                        <textarea class="form-control" name="materi_bimbingan" rows="4" required 
                                  placeholder="Jelaskan materi yang dibahas dalam bimbingan ini, misalnya: diskusi BAB 1, review metodologi, perbaikan rumusan masalah, dll."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan Mahasiswa</label>
                        <textarea class="form-control" name="catatan_mahasiswa" rows="3" 
                                  placeholder="Catatan atau pertanyaan dari Anda untuk dosen"></textarea>
                        <small class="form-text text-muted">Field ini akan terlihat oleh dosen pembimbing</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tindak Lanjut</label>
                        <textarea class="form-control" name="tindak_lanjut" rows="3" 
                                  placeholder="Tugas atau tindak lanjut yang diberikan dosen"></textarea>
                    </div>

                    <!-- Info untuk mahasiswa -->
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i> 
                        <strong>PERBAIKAN BARU:</strong> Anda sekarang dapat membuat jurnal bimbingan baru meskipun ada jurnal sebelumnya yang masih pending validasi. 
                        Jika ada pertemuan dengan nomor yang sama, sistem akan memperbarui jurnal yang sudah ada.
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-envelope"></i> 
                        <strong>Notifikasi:</strong> Jurnal yang sudah diinput akan dikirim ke dosen pembimbing untuk divalidasi. 
                        Pastikan informasi yang dimasukkan sudah benar.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Jurnal -->
<div class="modal fade" id="modalDetailJurnal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Jurnal Bimbingan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <!-- Content akan diisi via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<script>
// Tambah Jurnal Bimbingan
function tambahJurnalBimbingan() {
    var modalElement = document.getElementById('modalTambahJurnal');
    var modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Lihat Detail Jurnal
function lihatDetailJurnal(jurnalId) {
    <?php if (!empty($jurnal_bimbingan)): ?>
    var jurnalData = <?= json_encode($jurnal_bimbingan ?? [], JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT) ?>;
    var jurnal = null;
    
    // Find jurnal by ID
    for (var i = 0; i < jurnalData.length; i++) {
        if (jurnalData[i].id == jurnalId) {
            jurnal = jurnalData[i];
            break;
        }
    }
    
    if (jurnal) {
        var statusBadge = "";
        var catatanDosen = jurnal.catatan_dosen || "Belum ada catatan dari dosen";
        
        if (jurnal.status_validasi == "1") {
            statusBadge = "<span class=\"badge bg-success\"><i class=\"fas fa-check\"></i> Tervalidasi</span>";
        } else if (jurnal.status_validasi == "2") {
            statusBadge = "<span class=\"badge bg-warning\"><i class=\"fas fa-edit\"></i> Perlu Revisi</span>";
        } else {
            statusBadge = "<span class=\"badge bg-secondary\"><i class=\"fas fa-clock\"></i> Pending Validasi</span>";
        }
        
        var content = "<div class=\"row\">" +
            "<div class=\"col-md-6\">" +
                "<strong>Pertemuan ke:</strong> " + jurnal.pertemuan_ke + "<br>" +
                "<strong>Tanggal:</strong> " + jurnal.tanggal_bimbingan + "<br>" +
                "<strong>Status:</strong> " + statusBadge +
            "</div>" +
            "<div class=\"col-md-6\">" +
                "<strong>Dibuat:</strong> " + jurnal.created_at + "<br>" +
                (jurnal.tanggal_validasi ? "<strong>Divalidasi:</strong> " + jurnal.tanggal_validasi + "<br>" : "") +
            "</div>" +
        "</div>" +
        "<hr>" +
        "<div class=\"mb-3\">" +
            "<strong>Materi Bimbingan:</strong>" +
            "<div class=\"bg-light p-3 rounded mt-2\">" +
                (jurnal.materi_bimbingan || "Tidak ada materi") +
            "</div>" +
        "</div>" +
        "<div class=\"mb-3\">" +
            "<strong>Tindak Lanjut:</strong>" +
            "<div class=\"bg-light p-3 rounded mt-2\">" +
                (jurnal.tindak_lanjut || "Tidak ada tindak lanjut khusus") +
            "</div>" +
        "</div>" +
        "<div class=\"mb-3\">" +
            "<strong>Catatan Dosen:</strong>" +
            "<div class=\"bg-light p-3 rounded mt-2\">" +
                catatanDosen +
            "</div>" +
        "</div>";
        
        document.getElementById("modalDetailContent").innerHTML = content;
        var modalElement = document.getElementById('modalDetailJurnal');
        var modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else {
        alert("Data jurnal tidak ditemukan.");
    }
    <?php else: ?>
    alert("Belum ada data jurnal bimbingan.");
    <?php endif; ?>
}

// Hapus Jurnal
function hapusJurnal(jurnalId) {
    if (confirm("Apakah Anda yakin ingin menghapus jurnal ini? Jurnal yang sudah divalidasi tidak dapat dihapus.")) {
        window.location.href = "<?= base_url('mahasiswa/bimbingan/hapus_jurnal/') ?>" + jurnalId;
    }
}
</script>