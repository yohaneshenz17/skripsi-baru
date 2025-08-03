<!-- 
File: application/views/dosen/seminar_skripsi/penilaian.php
Form Penilaian Seminar Skripsi untuk Dosen
Style konsisten dengan Seminar Proposal
-->

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?= $is_edit ? 'Edit Penilaian' : 'Input Penilaian' ?> Seminar Skripsi
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dosen/dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('dosen/seminar_skripsi') ?>">Seminar Skripsi</a></li>
                <li class="breadcrumb-item active">Penilaian</li>
            </ol>
        </nav>
    </div>

    <!-- Alert Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $this->session->flashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Form Penilaian -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit"></i> <?= $is_edit ? 'Edit' : 'Input' ?> Penilaian Seminar Skripsi
                    </h6>
                </div>
                <div class="card-body">
                    <form id="penilaian-form" method="POST" action="<?= base_url('dosen/seminar_skripsi/penilaian/' . $seminar->id) ?>">
                        
                        <!-- Informasi Mahasiswa -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Nama Mahasiswa</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($seminar->nama_mahasiswa) ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">NIM</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($seminar->nim) ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold">Judul Skripsi</label>
                            <textarea class="form-control" rows="2" readonly><?= htmlspecialchars(!empty($seminar->judul_skripsi) ? $seminar->judul_skripsi : $seminar->judul) ?></textarea>
                        </div>

                        <!-- Informasi Jadwal (jika ada) -->
                        <?php if (!empty($seminar->tanggal_seminar)): ?>
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Tanggal Seminar:</strong><br>
                                    <?= date('d/m/Y', strtotime($seminar->tanggal_seminar)) ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Waktu:</strong><br>
                                    <?= $seminar->jam_seminar ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Tempat:</strong><br>
                                    <?= htmlspecialchars($seminar->tempat_seminar) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Catatan Pendahuluan -->
                        <div class="form-group mb-4">
                            <label for="catatan_pendahuluan" class="font-weight-bold">
                                <i class="fas fa-comment-alt"></i> Catatan Pendahuluan
                            </label>
                            <textarea class="form-control" 
                                      id="catatan_pendahuluan" 
                                      name="catatan_pendahuluan" 
                                      rows="3" 
                                      placeholder="Catatan umum tentang presentasi dan diskusi mahasiswa..."><?= isset($penilaian->catatan_pendahuluan) ? htmlspecialchars($penilaian->catatan_pendahuluan) : '' ?></textarea>
                        </div>

                        <!-- Tinjauaan Pustaka -->
                        <div class="form-group mb-4">
                            <label for="catatan_tinjauan_pustaka" class="font-weight-bold">
                                <i class="fas fa-book"></i> Catatan Tinjauan Pustaka
                            </label>
                            <textarea class="form-control" 
                                      id="catatan_tinjauan_pustaka" 
                                      name="catatan_tinjauan_pustaka" 
                                      rows="3" 
                                      placeholder="Evaluasi terhadap tinjauan pustaka dan referensi..."><?= isset($penilaian->catatan_tinjauan_pustaka) ? htmlspecialchars($penilaian->catatan_tinjauan_pustaka) : '' ?></textarea>
                        </div>

                        <!-- Metodologi -->
                        <div class="form-group mb-4">
                            <label for="catatan_metodologi" class="font-weight-bold">
                                <i class="fas fa-cogs"></i> Catatan Metodologi
                            </label>
                            <textarea class="form-control" 
                                      id="catatan_metodologi" 
                                      name="catatan_metodologi" 
                                      rows="3" 
                                      placeholder="Evaluasi metodologi penelitian yang digunakan..."><?= isset($penilaian->catatan_metodologi) ? htmlspecialchars($penilaian->catatan_metodologi) : '' ?></textarea>
                        </div>

                        <!-- Hasil Pembahasan -->
                        <div class="form-group mb-4">
                            <label for="catatan_hasil_pembahasan" class="font-weight-bold">
                                <i class="fas fa-chart-line"></i> Catatan Hasil Pembahasan
                            </label>
                            <textarea class="form-control" 
                                      id="catatan_hasil_pembahasan" 
                                      name="catatan_hasil_pembahasan" 
                                      rows="3" 
                                      placeholder="Evaluasi hasil penelitian dan pembahasan..."><?= isset($penilaian->catatan_hasil_pembahasan) ? htmlspecialchars($penilaian->catatan_hasil_pembahasan) : '' ?></textarea>
                        </div>

                        <!-- Kesimpulan -->
                        <div class="form-group mb-4">
                            <label for="catatan_kesimpulan" class="font-weight-bold">
                                <i class="fas fa-flag-checkered"></i> Catatan Kesimpulan
                            </label>
                            <textarea class="form-control" 
                                      id="catatan_kesimpulan" 
                                      name="catatan_kesimpulan" 
                                      rows="3" 
                                      placeholder="Evaluasi kesimpulan dan saran..."><?= isset($penilaian->catatan_kesimpulan) ? htmlspecialchars($penilaian->catatan_kesimpulan) : '' ?></textarea>
                        </div>

                        <!-- Catatan Umum -->
                        <div class="form-group mb-4">
                            <label for="catatan_umum" class="font-weight-bold">
                                <i class="fas fa-sticky-note"></i> Catatan Umum
                            </label>
                            <textarea class="form-control" 
                                      id="catatan_umum" 
                                      name="catatan_umum" 
                                      rows="3" 
                                      placeholder="Catatan umum dan masukan untuk mahasiswa..."><?= isset($penilaian->catatan_umum) ? htmlspecialchars($penilaian->catatan_umum) : '' ?></textarea>
                        </div>

                        <!-- Penilaian Angka -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-star"></i> Penilaian Komponen (0-100)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Nilai Penguji 1 -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nilai_penguji1" class="font-weight-bold">
                                                Nilai Penguji 1
                                                <?php if ($dosen_penguji1): ?>
                                                    <small class="text-muted">(<?= htmlspecialchars($dosen_penguji1->nama) ?>)</small>
                                                <?php endif; ?>
                                            </label>
                                            <input type="number" 
                                                   class="form-control nilai-input" 
                                                   id="nilai_penguji1" 
                                                   name="nilai_penguji1" 
                                                   min="0" 
                                                   max="100" 
                                                   step="0.01"
                                                   placeholder="0.00"
                                                   value="<?= isset($penilaian->nilai_penguji1) ? $penilaian->nilai_penguji1 : '' ?>"
                                                   required>
                                        </div>
                                    </div>

                                    <!-- Nilai Penguji 2 -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nilai_penguji2" class="font-weight-bold">
                                                Nilai Penguji 2
                                                <?php if ($dosen_penguji2): ?>
                                                    <small class="text-muted">(<?= htmlspecialchars($dosen_penguji2->nama) ?>)</small>
                                                <?php endif; ?>
                                            </label>
                                            <input type="number" 
                                                   class="form-control nilai-input" 
                                                   id="nilai_penguji2" 
                                                   name="nilai_penguji2" 
                                                   min="0" 
                                                   max="100" 
                                                   step="0.01"
                                                   placeholder="0.00"
                                                   value="<?= isset($penilaian->nilai_penguji2) ? $penilaian->nilai_penguji2 : '' ?>"
                                                   required>
                                        </div>
                                    </div>

                                    <!-- Nilai Pembimbing -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nilai_pembimbing" class="font-weight-bold">
                                                Nilai Pembimbing <span class="text-danger">*</span>
                                            </label>
                                            <input type="number" 
                                                   class="form-control nilai-input" 
                                                   id="nilai_pembimbing" 
                                                   name="nilai_pembimbing" 
                                                   min="0" 
                                                   max="100" 
                                                   step="0.01"
                                                   placeholder="0.00"
                                                   value="<?= isset($penilaian->nilai_pembimbing) ? $penilaian->nilai_pembimbing : '' ?>"
                                                   required>
                                        </div>
                                    </div>

                                    <!-- Nilai Akhir (Auto Calculate) -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Nilai Akhir (Rata-rata)</label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="nilai_akhir_display" 
                                                       readonly 
                                                       step="0.01"
                                                       placeholder="0.00">
                                                <div class="input-group-append">
                                                    <span class="input-group-text" id="nilai_huruf_display">-</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="nilai_akhir" id="nilai_akhir">
                                            <input type="hidden" name="nilai_huruf" id="nilai_huruf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rekomendasi -->
                        <div class="form-group mb-4">
                            <label class="font-weight-bold">
                                <i class="fas fa-thumbs-up"></i> Rekomendasi <span class="text-danger">*</span>
                            </label>
                            <div class="mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="rekomendasi" 
                                           id="lulus_tanpa_revisi" 
                                           value="lulus_tanpa_revisi"
                                           <?= (isset($penilaian->rekomendasi) && $penilaian->rekomendasi == 'lulus_tanpa_revisi') ? 'checked' : '' ?>>
                                    <label class="form-check-label text-success" for="lulus_tanpa_revisi">
                                        <i class="fas fa-check-circle"></i> Lulus Tanpa Revisi
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="rekomendasi" 
                                           id="lulus_dengan_revisi_minor" 
                                           value="lulus_dengan_revisi_minor"
                                           <?= (isset($penilaian->rekomendasi) && $penilaian->rekomendasi == 'lulus_dengan_revisi_minor') ? 'checked' : '' ?>>
                                    <label class="form-check-label text-warning" for="lulus_dengan_revisi_minor">
                                        <i class="fas fa-edit"></i> Lulus dengan Revisi Minor
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="rekomendasi" 
                                           id="lulus_dengan_revisi_mayor" 
                                           value="lulus_dengan_revisi_mayor"
                                           <?= (isset($penilaian->rekomendasi) && $penilaian->rekomendasi == 'lulus_dengan_revisi_mayor') ? 'checked' : '' ?>>
                                    <label class="form-check-label text-info" for="lulus_dengan_revisi_mayor">
                                        <i class="fas fa-redo"></i> Lulus dengan Revisi Mayor
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="rekomendasi" 
                                           id="tidak_lulus" 
                                           value="tidak_lulus"
                                           <?= (isset($penilaian->rekomendasi) && $penilaian->rekomendasi == 'tidak_lulus') ? 'checked' : '' ?>>
                                    <label class="form-check-label text-danger" for="tidak_lulus">
                                        <i class="fas fa-times-circle"></i> Tidak Lulus
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan Rekomendasi -->
                        <div class="form-group mb-4">
                            <label for="keterangan_rekomendasi" class="font-weight-bold">
                                <i class="fas fa-info-circle"></i> Keterangan Rekomendasi
                            </label>
                            <textarea class="form-control" 
                                      id="keterangan_rekomendasi" 
                                      name="keterangan_rekomendasi" 
                                      rows="3" 
                                      placeholder="Jelaskan detail rekomendasi, revisi yang diperlukan, atau alasan penilaian..."><?= isset($penilaian->keterangan_rekomendasi) ? htmlspecialchars($penilaian->keterangan_rekomendasi) : '' ?></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-between">
                                <a href="<?= base_url('dosen/seminar_skripsi') ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                
                                <div>
                                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary">
                                        <i class="fas fa-save"></i> Simpan Draft
                                    </button>
                                    <button type="submit" name="action" value="publish" class="btn btn-success ml-2">
                                        <i class="fas fa-paper-plane"></i> Publikasikan Penilaian
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Info Mahasiswa -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-graduate"></i> Informasi Mahasiswa
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td width="40%" class="font-weight-bold">Nama</td>
                            <td><?= htmlspecialchars($seminar->nama_mahasiswa) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">NIM</td>
                            <td><?= htmlspecialchars($seminar->nim) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email</td>
                            <td><?= htmlspecialchars($seminar->email_mahasiswa) ?></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Status</td>
                            <td>
                                <?php
                                $status_badges = [
                                    'draft' => '<span class="badge badge-secondary">Draft</span>',
                                    'submitted' => '<span class="badge badge-info">Diajukan</span>',
                                    'review_pembimbing' => '<span class="badge badge-warning">Review Pembimbing</span>',
                                    'review_kaprodi' => '<span class="badge badge-warning">Review Kaprodi</span>',
                                    'approved' => '<span class="badge badge-success">Disetujui</span>',
                                    'rejected' => '<span class="badge badge-danger">Ditolak</span>',
                                    'scheduled' => '<span class="badge badge-primary">Terjadwal</span>',
                                    'completed' => '<span class="badge badge-success">Selesai</span>'
                                ];
                                echo $status_badges[$seminar->status] ?? '<span class="badge badge-secondary">Unknown</span>';
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Info Dosen Penguji -->
            <?php if ($dosen_penguji1 || $dosen_penguji2): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users"></i> Tim Penguji
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($dosen_penguji1): ?>
                        <div class="mb-2">
                            <strong>Penguji 1:</strong><br>
                            <?= htmlspecialchars($dosen_penguji1->nama) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($dosen_penguji1->email) ?></small>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($dosen_penguji2): ?>
                        <div class="mb-2">
                            <strong>Penguji 2:</strong><br>
                            <?= htmlspecialchars($dosen_penguji2->nama) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($dosen_penguji2->email) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Aksi Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (!empty($seminar->file_skripsi)): ?>
                        <a href="<?= base_url('dosen/seminar_skripsi/view_file/' . $seminar->id) ?>" 
                           class="btn btn-outline-primary btn-sm mb-2" target="_blank">
                            <i class="fas fa-file-pdf"></i> Buka File Skripsi
                        </a>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('dosen/seminar_skripsi/detail/' . $seminar->id) ?>" 
                           class="btn btn-outline-info btn-sm mb-2">
                            <i class="fas fa-eye"></i> Lihat Detail Lengkap
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional CSS -->
<style>
.nilai-input:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.form-check-input:checked + .form-check-label {
    font-weight: bold;
}

.table-borderless td {
    border: none;
    padding: 0.3rem 0.5rem;
}

@media (max-width: 768px) {
    .form-check-inline {
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .btn {
        margin-bottom: 0.5rem;
    }
}
</style>