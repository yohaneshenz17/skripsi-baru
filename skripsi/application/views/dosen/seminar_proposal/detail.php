<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container-fluid">
    <!-- Header dengan informasi status -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 <?= isset($seminar->status) && $seminar->status == 'approved' ? 'bg-success text-white' : 'bg-primary text-white' ?>">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-presentation mr-2"></i>
                        Detail Pengajuan Seminar Proposal - <?= $seminar->nama_mahasiswa ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>NIM</strong></td>
                                    <td>: <?= $seminar->nim ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Mahasiswa</strong></td>
                                    <td>: <?= $seminar->nama_mahasiswa ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Program Studi</strong></td>
                                    <td>: <?= $seminar->nama_prodi ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status Pengajuan</strong></td>
                                    <td>: 
                                        <?php 
                                        $status_class = '';
                                        $status_text = '';
                                        
                                        switch(isset($seminar->status) ? $seminar->status : 'unknown') {
                                            case 'submitted':
                                                $status_class = 'badge-warning';
                                                $status_text = 'Menunggu Review';
                                                break;
                                            case 'review_pembimbing':
                                                $status_class = 'badge-info';
                                                $status_text = 'Sedang Direview';
                                                break;
                                            case 'approved':
                                                $status_class = 'badge-success';
                                                $status_text = 'Disetujui';
                                                break;
                                            case 'rejected':
                                                $status_class = 'badge-danger';
                                                $status_text = 'Ditolak';
                                                break;
                                            default:
                                                $status_class = 'badge-secondary';
                                                $status_text = 'Status Tidak Dikenal';
                                        }
                                        ?>
                                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Email</strong></td>
                                    <td>: <?= isset($seminar->email_mahasiswa) ? $seminar->email_mahasiswa : '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Telepon</strong></td>
                                    <td>: <?= isset($seminar->nomor_telepon) ? $seminar->nomor_telepon : '-' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Pengajuan</strong></td>
                                    <td>: <?= isset($seminar->created_at) ? date('d/m/Y H:i', strtotime($seminar->created_at)) : '-' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. INFORMASI PROPOSAL -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book mr-2"></i>
                        Informasi Proposal
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Judul Proposal:</label>
                        <p class="text-justify"><?= isset($seminar->judul) ? $seminar->judul : '-' ?></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Jenis Penelitian:</label>
                                <p><?= isset($seminar->jenis_penelitian) && !empty($seminar->jenis_penelitian) ? $seminar->jenis_penelitian : '-' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Lokasi Penelitian:</label>
                                <p><?= isset($seminar->lokasi_penelitian) && !empty($seminar->lokasi_penelitian) ? $seminar->lokasi_penelitian : '-' ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(isset($seminar->ringkasan) && !empty($seminar->ringkasan)): ?>
                    <div class="form-group">
                        <label class="font-weight-bold">Ringkasan:</label>
                        <div class="text-justify border p-3 bg-light rounded">
                            <?= nl2br($seminar->ringkasan) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($seminar->uraian_masalah) && !empty($seminar->uraian_masalah)): ?>
                    <div class="form-group">
                        <label class="font-weight-bold">Uraian Masalah:</label>
                        <div class="text-justify border p-3 bg-light rounded">
                            <?= nl2br($seminar->uraian_masalah) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($seminar->keterangan_mahasiswa) && !empty($seminar->keterangan_mahasiswa)): ?>
                    <div class="form-group">
                        <label class="font-weight-bold">Keterangan Tambahan dari Mahasiswa:</label>
                        <div class="alert alert-info">
                            <?= nl2br($seminar->keterangan_mahasiswa) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- FILE PROPOSAL UNTUK SEMINAR - FIX PATH -->
                    <?php if(isset($seminar->file_proposal) && !empty($seminar->file_proposal)): ?>
                    <div class="form-group">
                        <label class="font-weight-bold">File Proposal untuk Seminar:</label>
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="mr-3">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong><?= $seminar->file_proposal ?></strong>
                                <br>
                                <small class="text-muted">File yang akan dipresentasikan di seminar</small>
                            </div>
                            <div>
                                <!-- FIX: Path yang benar sesuai directory -->
                                <a href="<?= base_url('uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal) ?>" 
                                   class="btn btn-outline-primary btn-sm" target="_blank">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- File Draft Proposal Asli - FIX PATH -->
                    <?php if(isset($seminar->file_draft_proposal) && !empty($seminar->file_draft_proposal)): ?>
                    <div class="form-group">
                        <label class="font-weight-bold">File Draft Proposal Asli:</label>
                        <div class="d-flex align-items-center p-3 border rounded bg-light">
                            <div class="mr-3">
                                <i class="fas fa-file-word fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong><?= $seminar->file_draft_proposal ?></strong>
                                <br>
                                <small class="text-muted">File proposal asli yang sudah disetujui</small>
                            </div>
                            <div>
                                <!-- FIX: Path yang benar sesuai directory upload mahasiswa -->
                                <a href="<?= base_url('cdn/proposals/' . $seminar->file_draft_proposal) ?>" 
                                   class="btn btn-outline-secondary btn-sm" target="_blank">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. SYARAT JURNAL BIMBINGAN - FIX ERROR -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Syarat Jurnal Bimbingan
                    </h6>
                </div>
                <div class="card-body">
                    <?php 
                    // Safe check untuk jurnal requirement
                    $jurnal_eligible = false;
                    $jurnal_count = 0;
                    $jurnal_minimum = 8;
                    $jurnal_message = 'Tidak dapat memuat data jurnal';
                    
                    if (isset($jurnal_requirement) && is_array($jurnal_requirement)) {
                        $jurnal_eligible = isset($jurnal_requirement['eligible']) ? $jurnal_requirement['eligible'] : false;
                        $jurnal_count = isset($jurnal_requirement['jurnal_validated_count']) ? $jurnal_requirement['jurnal_validated_count'] : 0;
                        $jurnal_minimum = isset($jurnal_requirement['minimum_required']) ? $jurnal_requirement['minimum_required'] : 8;
                        $jurnal_message = isset($jurnal_requirement['message']) ? $jurnal_requirement['message'] : 'Data jurnal tidak tersedia';
                    }
                    ?>
                    
                    <div class="alert <?= $jurnal_eligible ? 'alert-success' : 'alert-warning' ?>">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <strong>
                                    <i class="fas <?= $jurnal_eligible ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> mr-2"></i>
                                    Status Jurnal Bimbingan
                                </strong>
                                <br>
                                <span><?= $jurnal_message ?></span>
                            </div>
                            <div class="col-md-4 text-right">
                                <h5 class="mb-0">
                                    <span class="badge <?= $jurnal_eligible ? 'badge-success' : 'badge-warning' ?> badge-lg">
                                        <?= $jurnal_count ?>/<?= $jurnal_minimum ?>
                                    </span>
                                </h5>
                            </div>
                        </div>
                    </div>

                    <!-- RIWAYAT JURNAL BIMBINGAN - FIX DISPLAY -->
                    <?php if (isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
                    <div class="mt-3">
                        <h6 class="font-weight-bold">Riwayat Jurnal Bimbingan (Yang Sudah Divalidasi):</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="50">No.</th>
                                        <th width="100">Pertemuan</th>
                                        <th width="120">Tanggal</th>
                                        <th>Agenda/Topik Pembahasan</th>
                                        <th width="120">Validator</th>
                                        <th width="120">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jurnal_bimbingan as $index => $jurnal): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= isset($jurnal->pertemuan_ke) ? $jurnal->pertemuan_ke : '-' ?></td>
                                        <td><?= isset($jurnal->tanggal_bimbingan) ? date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) : '-' ?></td>
                                        <td><?= isset($jurnal->agenda) ? $jurnal->agenda : (isset($jurnal->topik_pembahasan) ? $jurnal->topik_pembahasan : '-') ?></td>
                                        <td><?= isset($jurnal->nama_validator) ? $jurnal->nama_validator : 'Tidak Diketahui' ?></td>
                                        <td>
                                            <span class="badge badge-success badge-sm">Tervalidasi</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle mr-2"></i>
                        Belum ada jurnal bimbingan yang tervalidasi.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. DETAIL PELAKSANAAN SEMINAR (Jika sudah disetujui) -->
    <?php if(isset($seminar->status) && in_array($seminar->status, ['approved', 'scheduled', 'completed'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Detail Pelaksanaan Seminar Proposal
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Seminar proposal telah disetujui Kaprodi dan akan/sudah dijadwalkan.</strong>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="140"><strong>Tanggal Seminar</strong></td>
                                    <td>: <?= isset($seminar->tanggal_seminar) && !empty($seminar->tanggal_seminar) ? 
                                        date('d F Y', strtotime($seminar->tanggal_seminar)) : 'Belum dijadwalkan' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Jam Seminar</strong></td>
                                    <td>: <?= isset($seminar->jam_seminar) && !empty($seminar->jam_seminar) ? 
                                        date('H:i', strtotime($seminar->jam_seminar)) . ' WIB' : 'Belum ditentukan' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tempat</strong></td>
                                    <td>: <?= isset($seminar->tempat_seminar) && !empty($seminar->tempat_seminar) ? 
                                        $seminar->tempat_seminar : 'Belum ditentukan' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="140"><strong>Penguji 1</strong></td>
                                    <td>: <?= isset($seminar->nama_penguji1) ? $seminar->nama_penguji1 : 'Belum ditentukan' ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Penguji 2</strong></td>
                                    <td>: <?= isset($seminar->nama_penguji2) ? $seminar->nama_penguji2 : 'Belum ditentukan' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 4. AKSI DOSEN -->
    <?php if(isset($seminar->status) && in_array($seminar->status, ['submitted', 'review_pembimbing'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-tasks mr-2"></i>
                        Aksi Rekomendasi Dosen
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Menunggu rekomendasi Anda:</strong> Silakan berikan rekomendasi terhadap pengajuan seminar proposal ini.
                    </div>
                    
                    <form method="post" action="<?= base_url('dosen/seminar_proposal/rekomendasi') ?>">
                        <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
                        
                        <div class="form-group">
                            <label class="font-weight-bold">Komentar/Catatan:</label>
                            <textarea name="komentar_pembimbing" class="form-control" rows="4" 
                                placeholder="Berikan komentar atau catatan untuk pengajuan ini..."></textarea>
                            <small class="form-text text-muted">
                                Komentar wajib diisi jika memberikan penolakan.
                            </small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" name="rekomendasi" value="approved" 
                                        class="btn btn-success btn-block">
                                    <i class="fas fa-check mr-2"></i> Setujui Pengajuan
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" name="rekomendasi" value="rejected" 
                                        class="btn btn-danger btn-block"
                                        onclick="return validateReject()">
                                    <i class="fas fa-times mr-2"></i> Tolak Pengajuan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <div class="row">
        <div class="col-12">
            <div class="text-center">
                <a href="<?= base_url('dosen/seminar_proposal') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Seminar Proposal
                </a>
            </div>
        </div>
    </div>
</div>