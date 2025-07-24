<?php
$this->load->view('template/staf', [
    'title' => 'Manajemen Bimbingan',
    'content' => ob_start(),
    'css' => '
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
        <style>
            .stats-card {
                background: linear-gradient(87deg, #11cdef 0, #1171ef 100%);
                color: white;
                border: none;
                border-radius: 15px;
            }
            .filter-card {
                border: none;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            }
            .status-badge {
                font-size: 0.75rem;
                padding: 0.5rem 0.75rem;
            }
        </style>
    ',
    'script' => '
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    '
]);
?>

<!-- Content -->
<div class="row">
    <!-- Summary Statistics -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="icon icon-lg mb-3">
                    <i class="fas fa-book-open fa-2x"></i>
                </div>
                <h3 class="text-white mb-1"><?= isset($stats['total_jurnal']) ? $stats['total_jurnal'] : 0 ?></h3>
                <p class="text-white text-sm mb-0">Total Jurnal Bimbingan</p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="icon icon-lg mb-3">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <h3 class="text-white mb-1"><?= isset($stats['jurnal_valid']) ? $stats['jurnal_valid'] : 0 ?></h3>
                <p class="text-white text-sm mb-0">Jurnal Tervalidasi</p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="icon icon-lg mb-3">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <h3 class="text-white mb-1"><?= isset($stats['mahasiswa_aktif']) ? $stats['mahasiswa_aktif'] : 0 ?></h3>
                <p class="text-white text-sm mb-0">Mahasiswa Bimbingan</p>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card">
            <div class="card-body text-center">
                <div class="icon icon-lg mb-3">
                    <i class="fas fa-download fa-2x"></i>
                </div>
                <h3 class="text-white mb-1"><?= isset($stats['perlu_export']) ? $stats['perlu_export'] : 0 ?></h3>
                <p class="text-white text-sm mb-0">Perlu Export</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Filter Section -->
    <div class="col-12 mb-4">
        <div class="card filter-card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">🔍 Filter & Pencarian</h6>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-control-label">Program Studi</label>
                                <select class="form-control" id="filter_prodi" name="prodi_id">
                                    <option value="">Semua Prodi</option>
                                    <option value="10">Pendidikan Keagamaan Katolik</option>
                                    <option value="11">Pendidikan Guru Sekolah Dasar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-control-label">Status Validasi</label>
                                <select class="form-control" id="filter_status" name="status_validasi">
                                    <option value="">Semua Status</option>
                                    <option value="0">Pending</option>
                                    <option value="1">Valid</option>
                                    <option value="2">Perlu Revisi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-control-label">Pembimbing</label>
                                <select class="form-control" id="filter_dosen" name="dosen_id">
                                    <option value="">Semua Dosen</option>
                                    <!-- Akan diisi via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-control-label">Periode</label>
                                <select class="form-control" id="filter_periode" name="periode">
                                    <option value="">Semua Periode</option>
                                    <option value="2025-1">2025 Ganjil</option>
                                    <option value="2024-2">2024 Genap</option>
                                    <option value="2024-1">2024 Ganjil</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter Data
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                                <i class="fas fa-undo"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </form>
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
                        <h6 class="h3 mb-0">📚 Data Jurnal Bimbingan</h6>
                        <p class="text-sm mb-0">Monitoring dan manajemen jurnal bimbingan mahasiswa</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm" onclick="exportAll()">
                                <i class="fas fa-file-excel"></i> Export All
                            </button>
                            <button class="btn btn-info btn-sm" onclick="printReport()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-flush table-hover" id="bimbinganTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>NIM</th>
                                <th>Prodi</th>
                                <th>Judul Proposal</th>
                                <th>Pembimbing</th>
                                <th>Pertemuan</th>
                                <th>Status</th>
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

<!-- Modal Detail Jurnal -->
<div class="modal fade" id="modalDetailJurnal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 Detail Jurnal Bimbingan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <!-- Content akan dimuat via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="exportJurnal()">
                    <i class="fas fa-download"></i> Export PDF
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
    var table = $('#bimbinganTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url("api/staf/bimbingan") ?>',
            type: 'POST',
            data: function(d) {
                d.prodi_id = $('#filter_prodi').val();
                d.status_validasi = $('#filter_status').val();
                d.dosen_id = $('#filter_dosen').val();
                d.periode = $('#filter_periode').val();
            }
        },
        columns: [
            { data: 'no', width: '5%' },
            { data: 'nama_mahasiswa' },
            { data: 'nim', width: '10%' },
            { data: 'nama_prodi', width: '15%' },
            { data: 'judul_proposal' },
            { data: 'nama_pembimbing', width: '15%' },
            { data: 'total_pertemuan', width: '8%', className: 'text-center' },
            { 
                data: 'status_validasi', 
                width: '10%',
                render: function(data, type, row) {
                    if (data == '1') {
                        return '<span class="badge badge-success status-badge">Valid</span>';
                    } else if (data == '2') {
                        return '<span class="badge badge-warning status-badge">Revisi</span>';
                    } else {
                        return '<span class="badge badge-secondary status-badge">Pending</span>';
                    }
                }
            },
            { 
                data: 'id', 
                width: '10%',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-sm" onclick="viewDetail(${data})" data-toggle="tooltip" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-success btn-sm" onclick="exportJurnalMahasiswa(${data})" data-toggle="tooltip" title="Export PDF">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm'
            }
        ]
    });

    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Load dosen options
    loadDosenOptions();
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

function loadDosenOptions() {
    $.get('<?= base_url("api/staf/dosen") ?>', function(data) {
        var options = '<option value="">Semua Dosen</option>';
        data.forEach(function(dosen) {
            options += `<option value="${dosen.id}">${dosen.nama}</option>`;
        });
        $('#filter_dosen').html(options);
    });
}

function refreshData() {
    $('#bimbinganTable').DataTable().ajax.reload();
    showAlert('success', 'Data berhasil direfresh');
}

function resetFilter() {
    $('#filterForm')[0].reset();
    $('#bimbinganTable').DataTable().ajax.reload();
}

function viewDetail(id) {
    $('#modalDetailContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
    $('#modalDetailJurnal').modal('show');
    
    $.get(`<?= base_url("staf/bimbingan/detail_mahasiswa/") ?>${id}`, function(data) {
        $('#modalDetailContent').html(data);
    }).fail(function() {
        $('#modalDetailContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
    });
}

function exportJurnalMahasiswa(id) {
    window.open(`<?= base_url("staf/bimbingan/export_jurnal/") ?>${id}`, '_blank');
}

function exportAll() {
    if (confirm('Apakah Anda yakin ingin mengexport semua data jurnal bimbingan?')) {
        window.open('<?= base_url("staf/bimbingan/export_all") ?>', '_blank');
    }
}

function printReport() {
    window.open('<?= base_url("staf/bimbingan/cetak_jurnal/all") ?>', '_blank');
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