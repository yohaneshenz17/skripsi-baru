<?php
/**
 * COMPLETE FIXED VIEW: Detail Seminar Proposal untuk Dosen
 * File: application/views/dosen/seminar_proposal/detail.php
 * 
 * Menampilkan:
 * 1. Informasi Mahasiswa
 * 2. Detail Proposal yang Diajukan
 * 3. Syarat Jurnal Bimbingan
 * 4. Detail Pelaksanaan Seminar (jika sudah approved)
 * 5. Form Rekomendasi Dosen
 * 
 * Compatible dengan database STK St. Yakobus yang sudah ada
 */

// Helper function untuk character limiting (safe fallback)
if (!function_exists('safe_character_limiter')) {
    function safe_character_limiter($text, $limit = 50, $suffix = '...') {
        if (function_exists('character_limiter')) {
            return character_limiter($text, $limit);
        }
        return strlen($text) <= $limit ? $text : substr($text, 0, $limit) . $suffix;
    }
}
?>

<!-- Page Header -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-presentation mr-2"></i>
        Detail Seminar Proposal
    </h1>
    <div>
        <a href="<?= base_url('dosen/seminar_proposal') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
</div>

<!-- Status Alert -->
<?php if($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if($this->session->flashdata('info')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('info') ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        
        <!-- 1. INFORMASI MAHASISWA -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user-graduate mr-2"></i>
                    Informasi Mahasiswa
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="120"><strong>Nama</strong></td>
                                <td>: <?= isset($seminar->nama_mahasiswa) ? $seminar->nama_mahasiswa : '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>NIM</strong></td>
                                <td>: <?= isset($seminar->nim) ? $seminar->nim : '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Program Studi</strong></td>
                                <td>: <?= isset($seminar->nama_prodi) ? $seminar->nama_prodi : '-' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="120"><strong>Email</strong></td>
                                <td>: <?= isset($seminar->email_mahasiswa) ? $seminar->email_mahasiswa : '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>No. Telepon</strong></td>
                                <td>: <?= isset($seminar->nomor_telepon) && !empty($seminar->nomor_telepon) ? $seminar->nomor_telepon : '-' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Daftar</strong></td>
                                <td>: <?= isset($seminar->tanggal_pengajuan_proposal) ? date('d/m/Y', strtotime($seminar->tanggal_pengajuan_proposal)) : '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. DETAIL PROPOSAL YANG DIAJUKAN -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-file-alt mr-2"></i>
                    Detail Proposal yang Diajukan
                </h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="font-weight-bold">Judul Proposal:</label>
                    <h5 class="text-dark"><?= isset($seminar->judul) ? $seminar->judul : 'Tidak ada judul' ?></h5>
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

                <!-- File Proposal untuk Seminar -->
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
                            <a href="<?= base_url('uploads/seminar_proposal/' . $seminar->file_proposal) ?>" 
                               class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- File Draft Proposal Asli -->
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
                            <a href="<?= base_url('uploads/proposal/' . $seminar->file_draft_proposal) ?>" 
                               class="btn btn-outline-secondary btn-sm" target="_blank">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. DETAIL PELAKSANAAN SEMINAR (Jika sudah disetujui Kaprodi) -->
        <?php if(isset($seminar->status) && in_array($seminar->status, ['approved', 'scheduled', 'completed'])): ?>
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
                                <td>: <?= isset($seminar->tanggal_seminar) && !empty($seminar->tanggal_seminar) ? date('d F Y', strtotime($seminar->tanggal_seminar)) : 'Belum dijadwalkan' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Jam Seminar</strong></td>
                                <td>: <?= isset($seminar->jam_seminar) && !empty($seminar->jam_seminar) ? date('H:i', strtotime($seminar->jam_seminar)) . ' WITA' : 'Belum dijadwalkan' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tempat</strong></td>
                                <td>: <?= isset($seminar->tempat_seminar) && !empty($seminar->tempat_seminar) ? $seminar->tempat_seminar : 'Belum ditentukan' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="140"><strong>Dosen Penguji 1</strong></td>
                                <td>: <?= isset($seminar->nama_penguji1) && !empty($seminar->nama_penguji1) ? $seminar->nama_penguji1 : 'Belum ditentukan' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Dosen Penguji 2</strong></td>
                                <td>: <?= isset($seminar->nama_penguji2) && !empty($seminar->nama_penguji2) ? $seminar->nama_penguji2 : 'Belum ditentukan' ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status Seminar</strong></td>
                                <td>: 
                                    <?php
                                    if(isset($seminar->status)) {
                                        switch($seminar->status) {
                                            case 'scheduled':
                                                echo '<span class="badge badge-primary">Terjadwal</span>';
                                                break;
                                            case 'completed':
                                                echo '<span class="badge badge-success">Selesai</span>';
                                                break;
                                            default:
                                                echo '<span class="badge badge-info">Disetujui</span>';
                                        }
                                    } else {
                                        echo '<span class="badge badge-secondary">Status tidak diketahui</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        
        <!-- 4. SYARAT JURNAL BIMBINGAN -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-warning text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-clipboard-check mr-2"></i>
                    Syarat Jurnal Bimbingan
                </h6>
            </div>
            <div class="card-body">
                <?php if(isset($jurnal_requirement) && is_array($jurnal_requirement)): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="font-weight-bold">Progress Bimbingan</small>
                        <small class="font-weight-bold"><?= $jurnal_requirement['jurnal_valid'] ?>/<?= $jurnal_requirement['minimal_required'] ?></small>
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar <?= $jurnal_requirement['is_qualified'] ? 'bg-success' : 'bg-warning' ?>" 
                             style="width: <?= $jurnal_requirement['progress_percentage'] ?>%">
                            <?= $jurnal_requirement['progress_percentage'] ?>%
                        </div>
                    </div>
                    
                    <?php if($jurnal_requirement['is_qualified']): ?>
                        <div class="alert alert-success alert-sm mb-3">
                            <i class="fas fa-check-circle mr-1"></i>
                            <strong>Memenuhi Syarat!</strong> Mahasiswa sudah mencapai minimal bimbingan.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning alert-sm mb-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Belum Memenuhi Syarat!</strong> Masih perlu <?= $jurnal_requirement['minimal_required'] - $jurnal_requirement['jurnal_valid'] ?> jurnal lagi.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row text-center">
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="font-weight-bold text-success"><?= $jurnal_requirement['jurnal_valid'] ?></div>
                            <small class="text-muted">Valid</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="font-weight-bold text-warning"><?= $jurnal_requirement['jurnal_pending'] ?></div>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="font-weight-bold text-danger"><?= $jurnal_requirement['jurnal_revisi'] ?></div>
                            <small class="text-muted">Revisi</small>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Data jurnal bimbingan sedang dimuat...
                </div>
                <?php endif; ?>

                <!-- List Jurnal Bimbingan -->
                <?php if(isset($jurnal_bimbingan) && !empty($jurnal_bimbingan)): ?>
                <div class="mt-3">
                    <h6 class="font-weight-bold mb-2">Riwayat Bimbingan:</h6>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php foreach(array_slice($jurnal_bimbingan, 0, 5) as $jurnal): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <small class="text-muted">
                                            Pertemuan <?= $jurnal->pertemuan_ke ?> - <?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?>
                                        </small>
                                        <p class="mb-1" style="font-size: 0.9rem;">
                                            <?= safe_character_limiter($jurnal->materi_bimbingan, 60) ?>
                                        </p>
                                    </div>
                                    <span class="badge badge-<?= $jurnal->status_class ?> badge-sm ml-2">
                                        <?php
                                        switch($jurnal->status_validasi) {
                                            case '1': echo '<i class="fas fa-check"></i>'; break;
                                            case '2': echo '<i class="fas fa-edit"></i>'; break;
                                            default: echo '<i class="fas fa-clock"></i>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if(count($jurnal_bimbingan) > 5): ?>
                    <div class="text-center mt-2">
                        <a href="<?= base_url('dosen/bimbingan/detail/' . $seminar->proposal_id) ?>" class="btn btn-outline-primary btn-sm">
                            Lihat Semua Jurnal (<?= count($jurnal_bimbingan) ?>)
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="mt-3">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Belum ada jurnal bimbingan yang tercatat.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5. FORM REKOMENDASI DOSEN -->
        <?php if(isset($can_recommend) && $can_recommend): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-edit mr-2"></i>
                    Berikan Rekomendasi
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= base_url('dosen/seminar_proposal/rekomendasi') ?>" id="formRekomendasi">
                    <input type="hidden" name="seminar_id" value="<?= $seminar->id ?>">
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Keputusan Rekomendasi:</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="approved" name="rekomendasi" value="approved" class="custom-control-input" required>
                            <label class="custom-control-label text-success font-weight-bold" for="approved">
                                <i class="fas fa-thumbs-up mr-1"></i> Disetujui - Lanjut ke Kaprodi
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="rejected" name="rekomendasi" value="rejected" class="custom-control-input" required>
                            <label class="custom-control-label text-danger font-weight-bold" for="rejected">
                                <i class="fas fa-thumbs-down mr-1"></i> Ditolak - Kembali ke Mahasiswa
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="font-weight-bold">Komentar/Catatan:</label>
                        <textarea name="komentar_pembimbing" class="form-control" rows="4" 
                                  placeholder="Berikan komentar atau catatan untuk mahasiswa..."></textarea>
                        <small class="text-muted">*Wajib diisi jika menolak</small>
                    </div>
                    
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary btn-block" onclick="return confirm('Yakin dengan keputusan rekomendasi ini?')">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Rekomendasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Status Current -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-secondary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle mr-2"></i>
                    Status Saat Ini
                </h6>
            </div>
            <div class="card-body text-center">
                <?php
                if(isset($seminar->status)) {
                    switch($seminar->status) {
                        case 'review_kaprodi':
                            echo '<div class="alert alert-info">';
                            echo '<i class="fas fa-clock fa-2x mb-2"></i><br>';
                            echo '<strong>Menunggu Review Kaprodi</strong><br>';
                            echo 'Rekomendasi Anda sudah dikirim. Menunggu persetujuan dari Kaprodi.';
                            echo '</div>';
                            break;
                        case 'approved':
                            echo '<div class="alert alert-success">';
                            echo '<i class="fas fa-check-circle fa-2x mb-2"></i><br>';
                            echo '<strong>Disetujui Kaprodi</strong><br>';
                            echo 'Seminar proposal telah disetujui dan akan dijadwalkan.';
                            echo '</div>';
                            break;
                        case 'rejected':
                            echo '<div class="alert alert-danger">';
                            echo '<i class="fas fa-times-circle fa-2x mb-2"></i><br>';
                            echo '<strong>Ditolak</strong><br>';
                            echo 'Seminar proposal ditolak dan dikembalikan ke mahasiswa.';
                            echo '</div>';
                            break;
                        case 'scheduled':
                            echo '<div class="alert alert-primary">';
                            echo '<i class="fas fa-calendar-check fa-2x mb-2"></i><br>';
                            echo '<strong>Terjadwal</strong><br>';
                            echo 'Seminar proposal sudah dijadwalkan oleh Kaprodi.';
                            echo '</div>';
                            break;
                        case 'completed':
                            echo '<div class="alert alert-success">';
                            echo '<i class="fas fa-flag-checkered fa-2x mb-2"></i><br>';
                            echo '<strong>Selesai</strong><br>';
                            echo 'Seminar proposal telah dilaksanakan.';
                            echo '</div>';
                            break;
                        default:
                            if(isset($seminar->status_pembimbing) && $seminar->status_pembimbing !== 'pending') {
                                echo '<div class="alert alert-info">';
                                echo '<i class="fas fa-check-circle fa-2x mb-2"></i><br>';
                                echo '<strong>Rekomendasi Sudah Diberikan</strong><br>';
                                echo 'Status: ' . ucfirst($seminar->status_pembimbing);
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-warning">';
                                echo '<i class="fas fa-question-circle fa-2x mb-2"></i><br>';
                                echo '<strong>Status: ' . ucfirst($seminar->status) . '</strong>';
                                echo '</div>';
                            }
                    }
                } else {
                    echo '<div class="alert alert-warning">';
                    echo '<i class="fas fa-question-circle fa-2x mb-2"></i><br>';
                    echo '<strong>Status tidak dapat ditentukan</strong>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript untuk form validation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formRekomendasi');
    if (form) {
        form.addEventListener('submit', function(e) {
            const rekomendasi = document.querySelector('input[name="rekomendasi"]:checked');
            const komentar = document.querySelector('textarea[name="komentar_pembimbing"]');
            
            if (rekomendasi && rekomendasi.value === 'rejected' && !komentar.value.trim()) {
                e.preventDefault();
                alert('Komentar wajib diisi jika menolak pengajuan!');
                komentar.focus();
                return false;
            }
        });
    }
});
</script>