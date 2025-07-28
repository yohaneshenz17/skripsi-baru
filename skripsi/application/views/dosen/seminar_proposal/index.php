<!-- File: application/views/dosen/seminar_proposal/index.php -->
<!-- FIXED VERSION - Dashboard Seminar Proposal untuk Dosen -->

<div class="container-fluid">
    
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-presentation mr-2"></i>
            Seminar Proposal
        </h1>
        <div class="d-none d-lg-inline-block">
            <span class="badge badge-info badge-counter"><?= count($pengajuan_review) ?></span>
            <small class="text-muted ml-2">Perlu Review</small>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Bimbingan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_bimbingan'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Menunggu Review</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['menunggu_review'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Sudah Disetujui</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['sudah_disetujui'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Perlu Penilaian</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['perlu_penilaian'] ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengajuan Perlu Review -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Pengajuan Perlu Review (<?= count($pengajuan_review) ?>)
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($pengajuan_review)): ?>
                <div class="text-center py-4">
                    <img src="<?= base_url('assets/img/undraw_no_data.svg') ?>" alt="No Data" class="img-fluid mb-3" style="max-width: 200px; opacity: 0.6;">
                    <h5 class="text-muted">Tidak Ada Pengajuan Baru</h5>
                    <p class="text-muted">Saat ini tidak ada pengajuan seminar proposal yang perlu direview.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">NIM</th>
                                <th width="20%">Nama Mahasiswa</th>
                                <th width="35%">Judul Proposal</th>
                                <th width="10%">Prodi</th>
                                <th width="10%">Tanggal</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($pengajuan_review as $pengajuan): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <strong><?= $pengajuan->nim ?></strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm mr-3">
                                            <span class="avatar-title bg-primary text-white rounded-circle">
                                                <?= strtoupper(substr($pengajuan->nama_mahasiswa, 0, 1)) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <strong><?= $pengajuan->nama_mahasiswa ?></strong>
                                            <br>
                                            <small class="text-muted"><?= $pengajuan->email_mahasiswa ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-wrap">
                                        <?php
                                        $judul = $pengajuan->judul;
                                        if (strlen($judul) > 80) {
                                            echo substr($judul, 0, 80) . '...';
                                        } else {
                                            echo $judul;
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= $pengajuan->nama_prodi ?></span>
                                </td>
                                <td>
                                    <small>
                                        <?php
                                        if (isset($pengajuan->tanggal_pengajuan)) {
                                            echo date('d M Y', strtotime($pengajuan->tanggal_pengajuan));
                                        } elseif (isset($pengajuan->created_at)) {
                                            echo date('d M Y', strtotime($pengajuan->created_at));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                        <br>
                                        <span class="badge badge-warning badge-sm">Perlu Review</span>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <a href="<?= base_url('dosen/seminar_proposal/detail/' . $pengajuan->id) ?>" 
                                           class="btn btn-primary btn-sm mb-1">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        <button type="button" class="btn btn-success btn-sm mb-1" 
                                                onclick="quickApprove(<?= $pengajuan->id ?>)">
                                            <i class="fas fa-check"></i> Setujui
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="quickReject(<?= $pengajuan->id ?>)">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat Rekomendasi -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history mr-2"></i>
                Riwayat Rekomendasi Terbaru
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($riwayat_rekomendasi)): ?>
                <div class="text-center py-3">
                    <p class="text-muted mb-0">Belum ada riwayat rekomendasi.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>NIM</th>
                                <th>Nama Mahasiswa</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($riwayat_rekomendasi as $riwayat): ?>
                            <tr>
                                <td><?= $riwayat->nim ?></td>
                                <td><?= $riwayat->nama_mahasiswa ?></td>
                                <td>
                                    <?php
                                    $judul = $riwayat->judul;
                                    if (strlen($judul) > 50) {
                                        echo substr($judul, 0, 50) . '...';
                                    } else {
                                        echo $judul;
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $status = isset($riwayat->status_pembimbing) ? $riwayat->status_pembimbing : $riwayat->persetujuan;
                                    if ($status == 'approved' || $status == '1') {
                                        echo '<span class="badge badge-success">Disetujui</span>';
                                    } elseif ($status == 'rejected' || $status == '2') {
                                        echo '<span class="badge badge-danger">Ditolak</span>';
                                    } else {
                                        echo '<span class="badge badge-warning">Pending</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <small>
                                        <?php
                                        if (isset($riwayat->tanggal_review_pembimbing)) {
                                            echo date('d M Y', strtotime($riwayat->tanggal_review_pembimbing));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Perlu Penilaian -->
    <?php if (!empty($perlu_penilaian)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-clipboard-list mr-2"></i>
                Seminar yang Perlu Penilaian
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Judul</th>
                            <th>Tanggal Seminar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($perlu_penilaian as $penilaian): ?>
                        <tr>
                            <td><?= $penilaian->nim ?></td>
                            <td><?= $penilaian->nama_mahasiswa ?></td>
                            <td>
                                <?php
                                $judul = $penilaian->judul;
                                if (strlen($judul) > 60) {
                                    echo substr($judul, 0, 60) . '...';
                                } else {
                                    echo $judul;
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if (isset($penilaian->tanggal_seminar)) {
                                    echo date('d M Y', strtotime($penilaian->tanggal_seminar));
                                } elseif (isset($penilaian->tanggal)) {
                                    echo date('d M Y', strtotime($penilaian->tanggal));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="<?= base_url('dosen/seminar_proposal/penilaian/' . $penilaian->id) ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Input Penilaian
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- Quick Action Modals -->
<div class="modal fade" id="quickApproveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Setujui Seminar Proposal</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickApproveForm" method="post" action="<?= base_url('dosen/seminar_proposal/rekomendasi') ?>">
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="approve_seminar_id">
                    <input type="hidden" name="rekomendasi" value="approved">
                    <p>Apakah Anda yakin ingin menyetujui seminar proposal ini?</p>
                    <div class="form-group">
                        <label>Komentar (Opsional):</label>
                        <textarea name="komentar_pembimbing" class="form-control" rows="3" 
                                  placeholder="Masukkan komentar atau catatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Ya, Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="quickRejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Seminar Proposal</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="quickRejectForm" method="post" action="<?= base_url('dosen/seminar_proposal/rekomendasi') ?>">
                <div class="modal-body">
                    <input type="hidden" name="seminar_id" id="reject_seminar_id">
                    <input type="hidden" name="rekomendasi" value="rejected">
                    <p>Mengapa Anda menolak seminar proposal ini?</p>
                    <div class="form-group">
                        <label>Alasan Penolakan <span class="text-danger">*</span>:</label>
                        <textarea name="komentar_pembimbing" class="form-control" rows="4" 
                                  placeholder="Jelaskan alasan penolakan dan saran perbaikan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function quickApprove(seminarId) {
    $('#approve_seminar_id').val(seminarId);
    $('#quickApproveModal').modal('show');
}

function quickReject(seminarId) {
    $('#reject_seminar_id').val(seminarId);
    $('#quickRejectModal').modal('show');
}

// DataTable initialization
$(document).ready(function() {
    $('#dataTable').DataTable({
        "pageLength": 10,
        "ordering": true,
        "searching": true,
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data tersedia",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
});
</script>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 600;
}

.card-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-group-vertical .btn + .btn {
    margin-left: 0;
}

.text-wrap {
    word-wrap: break-word;
    word-break: break-word;
}

.table td {
    vertical-align: middle;
}

.badge-counter {
    font-size: 0.9em;
    padding: 0.4em 0.8em;
}
</style>