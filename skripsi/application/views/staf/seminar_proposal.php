<?php
$this->load->view('template/staf', [
    'title' => 'Manajemen Seminar Proposal',
    'content' => ob_start(),
    'css' => '
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <style>
            .seminar-card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
                transition: transform 0.3s ease;
            }
            .seminar-card:hover {
                transform: translateY(-5px);
            }
            .status-pending { border-left: 4px solid #fb6340; }
            .status-disetujui { border-left: 4px solid #2dce89; }
            .status-dijadwalkan { border-left: 4px solid #11cdef; }
            .status-selesai { border-left: 4px solid #5e72e4; }
            
            .timeline-item {
                border-left: 2px solid #e9ecef;
                padding-left: 1.5rem;
                margin-bottom: 1rem;
                position: relative;
            }
            .timeline-item:before {
                content: "";
                position: absolute;
                left: -8px;
                top: 5px;
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #5e72e4;
            }
        </style>
    ',
    'script' => '
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    '
]);
?>

<!-- Content -->
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Total Pengajuan</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['total']) ? $stats['total'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-primary rounded-circle shadow">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-gradient-success text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Disetujui</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['disetujui']) ? $stats['disetujui'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-gradient-info text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Dijadwalkan</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['dijadwalkan']) ? $stats['dijadwalkan'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card bg-gradient-warning text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Pending</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['pending']) ? $stats['pending'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-warning rounded-circle shadow">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-xl-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="h3 mb-0">⚡ Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="nav-wrapper">
                    <ul class="nav nav-pills nav-fill flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link mb-sm-3 mb-md-0 active" data-toggle="tab" href="#pending-tab" role="tab">
                                <i class="fas fa-clock mr-2"></i>Pending Review (<?= isset($stats['pending']) ? $stats['pending'] : 0 ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mb-sm-3 mb-md-0" data-toggle="tab" href="#jadwal-tab" role="tab">
                                <i class="fas fa-calendar mr-2"></i>Perlu Dijadwalkan (<?= isset($stats['perlu_jadwal']) ? $stats['perlu_jadwal'] : 0 ?>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mb-sm-3 mb-md-0" data-toggle="tab" href="#berita-tab" role="tab">
                                <i class="fas fa-file-pdf mr-2"></i>Berita Acara (<?= isset($stats['berita_acara']) ? $stats['berita_acara'] : 0 ?>)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Calendar/Schedule -->
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">📅 Jadwal Seminar Proposal</h6>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" onclick="tambahJadwal()">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="calendar-container">
                    <!-- Calendar akan diload di sini -->
                    <div class="row">
                        <?php 
                        $today = date('Y-m-d');
                        for($i = 0; $i < 7; $i++): 
                            $date = date('Y-m-d', strtotime($today . ' +' . $i . ' days'));
                            $day = date('d', strtotime($date));
                            $dayName = date('D', strtotime($date));
                        ?>
                        <div class="col">
                            <div class="card text-center">
                                <div class="card-header py-2">
                                    <small class="text-muted"><?= $dayName ?></small>
                                </div>
                                <div class="card-body py-2">
                                    <h6 class="mb-0"><?= $day ?></h6>
                                    <div class="schedule-items">
                                        <!-- Schedule items akan diload via AJAX -->
                                        <small class="text-muted">No events</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Data Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">📋 Data Seminar Proposal</h6>
                        <p class="text-sm mb-0">Kelola pengajuan dan penjadwalan seminar proposal</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm" onclick="filterStatus('all')">
                                <i class="fas fa-list"></i> Semua
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="filterStatus('0')">
                                <i class="fas fa-clock"></i> Pending
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="filterStatus('1')">
                                <i class="fas fa-check"></i> Disetujui
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-flush table-hover" id="seminarTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>NIM</th>
                                <th>Judul Proposal</th>
                                <th>Pembimbing</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                                <th>Jadwal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan dimuat via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Penjadwalan -->
<div class="modal fade" id="modalJadwal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📅 Penjadwalan Seminar Proposal</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formJadwal">
                <div class="modal-body">
                    <input type="hidden" id="proposal_id" name="proposal_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Tanggal Seminar <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tanggal_seminar" name="tanggal_seminar" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Waktu <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="waktu_seminar" name="waktu_seminar" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Tempat Seminar <span class="text-danger">*</span></label>
                        <select class="form-control" id="tempat_seminar" name="tempat_seminar" required>
                            <option value="">Pilih Tempat</option>
                            <option value="Ruang Seminar 1">Ruang Seminar 1</option>
                            <option value="Ruang Seminar 2">Ruang Seminar 2</option>
                            <option value="Aula STK">Aula STK</option>
                            <option value="Ruang Kelas A">Ruang Kelas A</option>
                            <option value="Ruang Kelas B">Ruang Kelas B</option>
                            <option value="Online (Zoom)">Online (Zoom)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Dosen Penguji 1 <span class="text-danger">*</span></label>
                        <select class="form-control" id="penguji1" name="penguji1" required>
                            <option value="">Pilih Dosen Penguji 1</option>
                            <!-- Akan diisi via AJAX -->
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Dosen Penguji 2</label>
                        <select class="form-control" id="penguji2" name="penguji2">
                            <option value="">Pilih Dosen Penguji 2 (Opsional)</option>
                            <!-- Akan diisi via AJAX -->
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Catatan Penjadwalan</label>
                        <textarea class="form-control" id="catatan_jadwal" name="catatan_jadwal" rows="3" placeholder="Catatan tambahan untuk penjadwalan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Seminar -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 Detail Seminar Proposal</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <!-- Content akan dimuat via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="cetakBeritaAcara()">
                    <i class="fas fa-print"></i> Cetak Berita Acara
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
echo $content;
?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#seminarTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url("api/staf/seminar_proposal") ?>',
            type: 'POST'
        },
        columns: [
            { data: 'no', width: '5%' },
            { data: 'nama_mahasiswa' },
            { data: 'nim', width: '10%' },
            { data: 'judul_proposal' },
            { data: 'nama_pembimbing', width: '15%' },
            { 
                data: 'created_at', 
                width: '10%',
                render: function(data) {
                    return new Date(data).toLocaleDateString('id-ID');
                }
            },
            { 
                data: 'status_seminar_proposal', 
                width: '10%',
                render: function(data, type, row) {
                    if (data == '1') {
                        return '<span class="badge badge-success">Disetujui</span>';
                    } else if (data == '2') {
                        return '<span class="badge badge-danger">Ditolak</span>';
                    } else {
                        return '<span class="badge badge-warning">Pending</span>';
                    }
                }
            },
            { 
                data: 'tanggal_seminar_proposal', 
                width: '10%',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString('id-ID') : '<span class="text-muted">Belum dijadwalkan</span>';
                }
            },
            { 
                data: 'id', 
                width: '12%',
                render: function(data, type, row) {
                    var buttons = `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-sm" onclick="viewDetail(${data})" data-toggle="tooltip" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                    `;
                    
                    if (row.status_seminar_proposal == '1' && !row.tanggal_seminar_proposal) {
                        buttons += `
                            <button class="btn btn-primary btn-sm" onclick="jadwalkanSeminar(${data})" data-toggle="tooltip" title="Jadwalkan">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                        `;
                    }
                    
                    if (row.tanggal_seminar_proposal) {
                        buttons += `
                            <button class="btn btn-success btn-sm" onclick="cetakBeritaAcara(${data})" data-toggle="tooltip" title="Cetak Berita Acara">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                        `;
                    }
                    
                    buttons += '</div>';
                    return buttons;
                }
            }
        ],
        order: [[5, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });

    // Initialize date picker
    flatpickr("#tanggal_seminar", {
        dateFormat: "Y-m-d",
        locale: "id",
        minDate: "today"
    });

    // Load dosen options
    loadDosenOptions();
    
    // Form submission
    $('#formJadwal').on('submit', function(e) {
        e.preventDefault();
        simpanJadwal();
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

function loadDosenOptions() {
    $.get('<?= base_url("api/staf/dosen") ?>', function(data) {
        var options = '<option value="">Pilih Dosen</option>';
        data.forEach(function(dosen) {
            if (dosen.level == '2') { // Hanya dosen biasa yang bisa jadi penguji
                options += `<option value="${dosen.id}">${dosen.nama}</option>`;
            }
        });
        $('#penguji1, #penguji2').html(options);
    });
}

function filterStatus(status) {
    if (status === 'all') {
        table.column(6).search('').draw();
    } else {
        table.column(6).search(status).draw();
    }
}

function viewDetail(id) {
    $('#modalDetailContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
    $('#modalDetail').modal('show');
    
    $.get(`<?= base_url("staf/seminar_proposal/detail/") ?>${id}`, function(data) {
        $('#modalDetailContent').html(data);
    }).fail(function() {
        $('#modalDetailContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
    });
}

function jadwalkanSeminar(id) {
    $('#proposal_id').val(id);
    $('#modalJadwal').modal('show');
}

function simpanJadwal() {
    var formData = $('#formJadwal').serialize();
    
    $.post('<?= base_url("staf/seminar_proposal/jadwal") ?>', formData, function(response) {
        if (response.success) {
            $('#modalJadwal').modal('hide');
            $('#seminarTable').DataTable().ajax.reload();
            showAlert('success', 'Jadwal seminar berhasil disimpan');
        } else {
            showAlert('error', response.message || 'Gagal menyimpan jadwal');
        }
    }).fail(function() {
        showAlert('error', 'Terjadi kesalahan server');
    });
}

function cetakBeritaAcara(id) {
    window.open(`<?= base_url("staf/seminar_proposal/berita_acara/") ?>${id}`, '_blank');
}

function tambahJadwal() {
    $('#formJadwal')[0].reset();
    $('#modalJadwal').modal('show');
}

function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    $('.card-body').first().prepend(alert);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}
</script>