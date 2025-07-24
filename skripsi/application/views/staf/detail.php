<?php
// ========================================
// 6. DETAIL BIMBINGAN VIEW
// File: application/views/staf/bimbingan/detail.php
// ========================================

ob_start();
?>

<div class="row">
    <!-- Info Mahasiswa -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-control-label">Nama Mahasiswa</label>
                            <p class="form-control-static"><?= $proposal->nama_mahasiswa ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">NIM</label>
                            <p class="form-control-static"><?= $proposal->nim ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Program Studi</label>
                            <p class="form-control-static"><?= $proposal->nama_prodi ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">Email</label>
                            <p class="form-control-static"><?= $proposal->email ?></p>
                        </div>
                        <div class="form-group">
                            <label class="form-control-label">No. Telepon</label>
                            <p class="form-control-static"><?= $proposal->nomor_telepon ?: '-' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Tugas Akhir -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Informasi Tugas Akhir</h5>
                    </div>
                    <div class="col text-right">
                        <a href="<?= base_url('staf/bimbingan/export_jurnal/' . $proposal->id) ?>" 
                           class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download"></i> Export Jurnal PDF
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-control-label">Judul Tugas Akhir</label>
                    <p class="form-control-static"><?= $proposal->judul ?></p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Dosen Pembimbing</label>
                            <p class="form-control-static"><?= $proposal->nama_pembimbing ?: '-' ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Email Pembimbing</label>
                            <p class="form-control-static"><?= $proposal->email_pembimbing ?: '-' ?></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Status Workflow</label>
                            <p class="form-control-static">
                                <?php
                                $workflow_labels = [
                                    'proposal' => 'Pengajuan Proposal',
                                    'bimbingan' => 'Bimbingan',
                                    'seminar_proposal' => 'Seminar Proposal',
                                    'penelitian' => 'Penelitian',
                                    'seminar_skripsi' => 'Seminar Skripsi',
                                    'publikasi' => 'Publikasi',
                                    'selesai' => 'Selesai'
                                ];
                                echo $workflow_labels[$proposal->workflow_status] ?? 'Unknown';
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Tanggal Pengajuan</label>
                            <p class="form-control-static">
                                <?= $proposal->created_at ? date('d F Y', strtotime($proposal->created_at)) : '-' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="mb-0">Jurnal Bimbingan</h3>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Materi Bimbingan</th>
                            <th scope="col">Catatan Pembimbing</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tanggal Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($jurnal_bimbingan)): ?>
                            <?php $no = 1; foreach($jurnal_bimbingan as $jurnal): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) ?></td>
                                    <td class="text-wrap" style="max-width: 300px;">
                                        <?= $jurnal->materi_bimbingan ?>
                                    </td>
                                    <td class="text-wrap" style="max-width: 250px;">
                                        <?= $jurnal->catatan_pembimbing ?: '-' ?>
                                    </td>
                                    <td>
                                        <?php if($jurnal->status_validasi == '1'): ?>
                                            <span class="badge badge-success">Valid</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $jurnal->tanggal_validasi ? 
                                            date('d/m/Y', strtotime($jurnal->tanggal_validasi)) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada jurnal bimbingan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$this->load->view('template/staf', [
    'title' => 'Detail Bimbingan - ' . $proposal->nama_mahasiswa,
    'content' => $content,
    'css' => '',
    'script' => ''
]);
