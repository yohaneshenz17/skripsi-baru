<?php
// =======================================================
// 3. TRACKING TIMELINE
// File: application/views/mahasiswa/publikasi/tracking.php
// =======================================================
?>

<!-- TRACKING TIMELINE PUBLIKASI -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Timeline Publikasi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa/publikasi') ?>">Publikasi</a></li>
                        <li class="breadcrumb-item active">Timeline</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-timeline"></i> Timeline Proses Publikasi</h3>
                        </div>
                        <div class="card-body">
                            
                            <!-- Progress Steps -->
                            <div class="row">
                                <div class="col-12">
                                    <?php 
                                    $steps = [
                                        ['key' => 'draft', 'title' => 'Draft', 'icon' => 'fa-edit'],
                                        ['key' => 'submitted', 'title' => 'Submitted', 'icon' => 'fa-paper-plane'],
                                        ['key' => 'review_pembimbing', 'title' => 'Review Dosen', 'icon' => 'fa-user-graduate'],
                                        ['key' => 'review_staf', 'title' => 'Validasi Staf', 'icon' => 'fa-user-tie'],
                                        ['key' => 'completed', 'title' => 'Selesai', 'icon' => 'fa-check-circle']
                                    ];
                                    ?>
                                    
                                    <div class="timeline">
                                        <?php foreach ($steps as $index => $step): ?>
                                            <?php 
                                            $is_current = ($publikasi->status === $step['key']);
                                            $is_completed = false;
                                            
                                            // Check if step is completed
                                            switch($step['key']) {
                                                case 'draft':
                                                    $is_completed = in_array($publikasi->status, ['submitted', 'review_pembimbing', 'review_staf', 'completed']);
                                                    break;
                                                case 'submitted':
                                                    $is_completed = in_array($publikasi->status, ['review_pembimbing', 'review_staf', 'completed']);
                                                    break;
                                                case 'review_pembimbing':
                                                    $is_completed = in_array($publikasi->status, ['review_staf', 'completed']);
                                                    break;
                                                case 'review_staf':
                                                    $is_completed = ($publikasi->status === 'completed');
                                                    break;
                                                case 'completed':
                                                    $is_completed = ($publikasi->status === 'completed');
                                                    break;
                                            }
                                            
                                            $icon_class = $is_completed ? 'bg-success' : ($is_current ? 'bg-warning' : 'bg-secondary');
                                            ?>
                                            
                                            <div class="time-label">
                                                <span class="<?= $icon_class ?>">
                                                    <i class="fas <?= $step['icon'] ?>"></i>
                                                </span>
                                            </div>
                                            
                                            <div>
                                                <i class="fas <?= $step['icon'] ?> <?= $icon_class ?> text-white"></i>
                                                <div class="timeline-item">
                                                    <span class="time">
                                                        <?php if ($is_completed || $is_current): ?>
                                                            <?php 
                                                            $date = '';
                                                            switch($step['key']) {
                                                                case 'submitted':
                                                                    $date = $publikasi->tanggal_pengajuan;
                                                                    break;
                                                                case 'review_pembimbing':
                                                                    $date = $publikasi->tanggal_review_pembimbing;
                                                                    break;
                                                                case 'review_staf':
                                                                    $date = $publikasi->tanggal_validasi_staf;
                                                                    break;
                                                                case 'completed':
                                                                    $date = $publikasi->tanggal_selesai;
                                                                    break;
                                                            }
                                                            echo $date ? date('d M Y H:i', strtotime($date)) : 'Sedang Proses';
                                                            ?>
                                                        <?php else: ?>
                                                            Menunggu
                                                        <?php endif; ?>
                                                    </span>
                                                    
                                                    <h3 class="timeline-header">
                                                        <?= $step['title'] ?>
                                                        <?php if ($is_completed): ?>
                                                            <span class="badge badge-success ml-2">Selesai</span>
                                                        <?php elseif ($is_current): ?>
                                                            <span class="badge badge-warning ml-2">Sedang Proses</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary ml-2">Menunggu</span>
                                                        <?php endif; ?>
                                                    </h3>
                                                    
                                                    <div class="timeline-body">
                                                        <?php 
                                                        switch($step['key']) {
                                                            case 'draft':
                                                                echo "Pengajuan publikasi dibuat sebagai draft.";
                                                                break;
                                                            case 'submitted':
                                                                echo "Pengajuan disubmit ke dosen pembimbing untuk direview.";
                                                                break;
                                                            case 'review_pembimbing':
                                                                echo "Dosen pembimbing melakukan review dan memberikan persetujuan.";
                                                                if ($publikasi->komentar_pembimbing && $is_completed) {
                                                                    echo "<br><strong>Komentar:</strong> " . $publikasi->komentar_pembimbing;
                                                                }
                                                                break;
                                                            case 'review_staf':
                                                                echo "Staf melakukan validasi final dan input link repository.";
                                                                if ($publikasi->komentar_staf && $is_completed) {
                                                                    echo "<br><strong>Komentar:</strong> " . $publikasi->komentar_staf;
                                                                }
                                                                break;
                                                            case 'completed':
                                                                echo "Publikasi selesai diproses. Surat keterangan dapat didownload.";
                                                                if ($publikasi->link_repository && $is_completed) {
                                                                    echo "<br><strong>Repository:</strong> <a href='" . $publikasi->link_repository . "' target='_blank'>" . $publikasi->link_repository . "</a>";
                                                                }
                                                                break;
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <div>
                                            <i class="far fa-clock bg-gray"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <a href="<?= base_url('mahasiswa/publikasi') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                            </a>
                            
                            <?php if ($publikasi->status === 'completed'): ?>
                                <a href="<?= base_url('mahasiswa/publikasi/download_surat/' . $publikasi->id) ?>" 
                                   class="btn btn-success" target="_blank">
                                    <i class="fas fa-download"></i> Download Surat Keterangan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Info -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle"></i> Detail Publikasi</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Judul</strong></td>
                                    <td><?= $publikasi->judul_skripsi_final ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Ujian</strong></td>
                                    <td><?= date('d F Y', strtotime($publikasi->tanggal_ujian_skripsi)) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>
                                        <?php
                                        $badge_class = [
                                            'draft' => 'secondary',
                                            'submitted' => 'primary', 
                                            'review_pembimbing' => 'info',
                                            'review_staf' => 'warning',
                                            'completed' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        ?>
                                        <span class="badge badge-<?= $badge_class[$publikasi->status] ?? 'secondary' ?>">
                                            <?= ucfirst(str_replace('_', ' ', $publikasi->status)) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php if ($publikasi->tanggal_pengajuan): ?>
                                <tr>
                                    <td><strong>Tanggal Submit</strong></td>
                                    <td><?= date('d F Y H:i', strtotime($publikasi->tanggal_pengajuan)) ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-download"></i> Download File</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($publikasi->file_surat_revisi): ?>
                                <a href="<?= base_url('mahasiswa/publikasi/download/' . $publikasi->id . '/surat_revisi') ?>" 
                                   class="btn btn-outline-danger btn-sm btn-block" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Surat Revisi
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($publikasi->file_skripsi_final): ?>
                                <a href="<?= base_url('mahasiswa/publikasi/download/' . $publikasi->id . '/skripsi_final') ?>" 
                                   class="btn btn-outline-danger btn-sm btn-block" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Skripsi Final
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($publikasi->file_surat_perpustakaan): ?>
                                <a href="<?= base_url('mahasiswa/publikasi/download/' . $publikasi->id . '/surat_perpustakaan') ?>" 
                                   class="btn btn-outline-danger btn-sm btn-block" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Surat Perpustakaan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- End of Views -->