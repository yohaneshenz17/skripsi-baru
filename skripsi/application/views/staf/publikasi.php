<?php
$this->load->view('template/staf', [
    'title' => 'Manajemen Publikasi Tugas Akhir',
    'content' => ob_start(),
    'css' => '
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
        <style>
            .publikasi-card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
                transition: transform 0.3s ease;
            }
            .publikasi-card:hover {
                transform: translateY(-5px);
            }
            .repository-preview {
                background: #f8f9fa;
                border: 1px dashed #dee2e6;
                border-radius: 8px;
                padding: 1rem;
                text-align: center;
            }
            .status-pending { background: linear-gradient(87deg, #fb6340 0, #fbb140 100%); }
            .status-valid { background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%); }
            .status-invalid { background: linear-gradient(87deg, #f5365c 0, #f56036 100%); }
            .status-review { background: linear-gradient(87deg, #11cdef 0, #1171ef 100%); }
            
            .link-preview {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 0.75rem;
                margin: 0.5rem 0;
                border-left: 4px solid #5e72e4;
            }
            .validation-result {
                border-radius: 8px;
                padding: 1rem;
                margin: 1rem 0;
            }
        </style>
    ',
    'script' => '
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    '
]);
?>

<!-- Content -->
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card status-pending text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Menunggu Validasi</h5>
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
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card status-valid text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Sudah Valid</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['valid']) ? $stats['valid'] : 0 ?></span>
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
        <div class="card status-invalid text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Perlu Perbaikan</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['invalid']) ? $stats['invalid'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-danger rounded-circle shadow">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card status-review text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Total Publikasi</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['total']) ? $stats['total'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fas fa-globe"></i>
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
                        <li class="nav-item mb-2">
                            <button class="btn btn-outline-warning btn-block" onclick="filterByStatus('0')">
                                <i class="fas fa-clock mr-2"></i>Pending Validasi (<?= isset($stats['pending']) ? $stats['pending'] : 0 ?>)
                            </button>
                        </li>
                        <li class="nav-item mb-2">
                            <button class="btn btn-outline-danger btn-block" onclick="filterByStatus('2')">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Perlu Perbaikan (<?= isset($stats['invalid']) ? $stats['invalid'] : 0 ?>)
                            </button>
                        </li>
                        <li class="nav-item mb-2">
                            <button class="btn btn-outline-primary btn-block" onclick="tambahRepository()">
                                <i class="fas fa-plus mr-2"></i>Input Repository Baru
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-outline-success btn-block" onclick="exportData()">
                                <i class="fas fa-download mr-2"></i>Export Data Publikasi
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Repository Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="h3 mb-0">📊 Statistik Repository</h6>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="repositoryChart" width="400" height="200"></canvas>
                </div>
                <div class="mt-3">
                    <div class="row text-center">
                        <div class="col-6">
                            <span class="h6 font-weight-bold">GitHub</span>
                            <span class="d-block text-sm text-muted"><?= isset($stats['github']) ? $stats['github'] : 0 ?> repos</span>
                        </div>
                        <div class="col-6">
                            <span class="h6 font-weight-bold">Other</span>
                            <span class="d-block text-sm text-muted"><?= isset($stats['other']) ? $stats['other'] : 0 ?> repos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Repository Validator Tool -->
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="h3 mb-0">🔍 Repository Validator</h6>
                <p class="text-sm mb-0">Tool untuk memvalidasi link repository secara real-time</p>
            </div>
            <div class="card-body">
                <form id="validatorForm">
                    <div class="form-group">
                        <label class="form-control-label">URL Repository <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="url" class="form-control" id="repository_url" placeholder="https://github.com/username/repository" required>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i> Validasi
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Masukkan URL repository GitHub, GitLab, atau platform lainnya</small>
                    </div>
                </form>
                
                <div id="validationResult" style="display: none;">
                    <!-- Hasil validasi akan ditampilkan di sini -->
                </div>
                
                <div class="repository-preview" id="repositoryPreview" style="display: none;">
                    <i class="fas fa-code-branch fa-3x text-muted mb-3"></i>
                    <h5>Repository Preview</h5>
                    <p class="text-muted">Pratinjau repository akan ditampilkan di sini setelah validasi</p>
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
                        <h6 class="h3 mb-0">🌐 Data Publikasi Repository</h6>
                        <p class="text-sm mb-0">Kelola dan validasi publikasi tugas akhir mahasiswa</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm" onclick="filterByStatus('all')">
                                <i class="fas fa-list"></i> Semua
                            </button>
                            <button class="btn btn-outline-warning btn-sm" onclick="filterByStatus('0')">
                                <i class="fas fa-clock"></i> Pending
                            </button>
                            <button class="btn btn-outline-success btn-sm" onclick="filterByStatus('1')">
                                <i class="fas fa-check"></i> Valid
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="filterByStatus('2')">
                                <i class="fas fa-times"></i> Invalid
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-flush table-hover" id="publikasiTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>NIM</th>
                                <th>Judul Tugas Akhir</th>
                                <th>Repository URL</th>
                                <th>Status Validasi</th>
                                <th>Validator</th>
                                <th>Tanggal Validasi</th>
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

<!-- Modal Input Repository -->
<div class="modal fade" id="modalRepository" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">🌐 Input Repository</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formRepository">
                <div class="modal-body">
                    <input type="hidden" id="proposal_id" name="proposal_id">
                    
                    <div class="form-group">
                        <label class="form-control-label">Mahasiswa</label>
                        <input type="text" class="form-control" id="nama_mahasiswa" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Judul Tugas Akhir</label>
                        <textarea class="form-control" id="judul_tugas_akhir" rows="2" readonly></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">URL Repository <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="link_repository" name="link_repository" placeholder="https://github.com/username/repository" required>
                        <small class="text-muted">Masukkan URL lengkap repository (GitHub, GitLab, dll)</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Platform Repository</label>
                        <select class="form-control" id="platform_repository" name="platform_repository">
                            <option value="GitHub">GitHub</option>
                            <option value="GitLab">GitLab</option>
                            <option value="Bitbucket">Bitbucket</option>
                            <option value="Other">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Status Validasi</label>
                        <select class="form-control" id="validasi_staf_publikasi" name="validasi_staf_publikasi">
                            <option value="0">Menunggu Validasi</option>
                            <option value="1">Valid</option>
                            <option value="2">Perlu Perbaikan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Catatan Validasi</label>
                        <textarea class="form-control" id="catatan_staf" name="catatan_staf" rows="3" placeholder="Catatan untuk mahasiswa mengenai repository..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Publikasi -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 Detail Publikasi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <!-- Content akan dimuat via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="bukaSertifikat()">
                    <i class="fas fa-certificate"></i> Generate Sertifikat
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
    var table = $('#publikasiTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url("api/staf/publikasi") ?>',
            type: 'POST'
        },
        columns: [
            { data: 'no', width: '5%' },
            { data: 'nama_mahasiswa' },
            { data: 'nim', width: '10%' },
            { data: 'judul', className: 'text-wrap' },
            { 
                data: 'link_repository', 
                width: '20%',
                render: function(data) {
                    if (data) {
                        return `<a href="${data}" target="_blank" class="text-primary"><i class="fas fa-external-link-alt"></i> ${data.substring(0, 50)}...</a>`;
                    }
                    return '<span class="text-muted">Belum diinput</span>';
                }
            },
            { 
                data: 'validasi_staf_publikasi', 
                width: '10%',
                render: function(data, type, row) {
                    if (data == '1') {
                        return '<span class="badge badge-success">Valid</span>';
                    } else if (data == '2') {
                        return '<span class="badge badge-danger">Perlu Perbaikan</span>';
                    } else {
                        return '<span class="badge badge-warning">Pending</span>';
                    }
                }
            },
            { 
                data: 'nama_validator', 
                width: '12%',
                render: function(data) {
                    return data || '<span class="text-muted">-</span>';
                }
            },
            { 
                data: 'tanggal_validasi_staf', 
                width: '10%',
                render: function(data) {
                    return data ? new Date(data).toLocaleDateString('id-ID') : '<span class="text-muted">-</span>';
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
                    
                    if (!row.link_repository || row.validasi_staf_publikasi == '0' || row.validasi_staf_publikasi == '2') {
                        buttons += `
                            <button class="btn btn-primary btn-sm" onclick="inputRepository(${data})" data-toggle="tooltip" title="Input/Edit Repository">
                                <i class="fas fa-edit"></i>
                            </button>
                        `;
                    }
                    
                    if (row.link_repository) {
                        buttons += `
                            <button class="btn btn-success btn-sm" onclick="validateRepository('${row.link_repository}')" data-toggle="tooltip" title="Validasi Repository">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        `;
                    }
                    
                    buttons += '</div>';
                    return buttons;
                }
            }
        ],
        order: [[7, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });

    // Repository validator form
    $('#validatorForm').on('submit', function(e) {
        e.preventDefault();
        var url = $('#repository_url').val();
        validateRepository(url);
    });

    // Repository form submission
    $('#formRepository').on('submit', function(e) {
        e.preventDefault();
        simpanRepository();
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Load statistics chart
    loadRepositoryChart();
});

function filterByStatus(status) {
    if (status === 'all') {
        $('#publikasiTable').DataTable().column(5).search('').draw();
    } else {
        $('#publikasiTable').DataTable().column(5).search(status).draw();
    }
}

function validateRepository(url) {
    $('#validationResult').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Memvalidasi repository...</div>').show();
    
    $.post('<?= base_url("staf/publikasi/validasi_repository") ?>', { url: url }, function(response) {
        if (response.success) {
            var result = response.data;
            var html = `
                <div class="validation-result bg-${result.valid ? 'success' : 'danger'} text-white">
                    <h6><i class="fas fa-${result.valid ? 'check-circle' : 'times-circle'}"></i> ${result.valid ? 'Repository Valid' : 'Repository Tidak Valid'}</h6>
                    <p class="mb-1"><strong>URL:</strong> ${result.url}</p>
                    <p class="mb-1"><strong>Platform:</strong> ${result.platform}</p>
                    <p class="mb-1"><strong>Status:</strong> ${result.status}</p>
                    ${result.message ? `<p class="mb-0"><strong>Pesan:</strong> ${result.message}</p>` : ''}
                </div>
            `;
            $('#validationResult').html(html);
            
            if (result.valid && result.preview) {
                var previewHtml = `
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Informasi Repository</h6>
                            <p><strong>Nama:</strong> ${result.preview.name || 'N/A'}</p>
                            <p><strong>Deskripsi:</strong> ${result.preview.description || 'N/A'}</p>
                            <p><strong>Language:</strong> ${result.preview.language || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Statistik</h6>
                            <p><strong>Stars:</strong> ${result.preview.stars || 0}</p>
                            <p><strong>Forks:</strong> ${result.preview.forks || 0}</p>
                            <p><strong>Last Update:</strong> ${result.preview.updated || 'N/A'}</p>
                        </div>
                    </div>
                `;
                $('#repositoryPreview').html(previewHtml).show();
            }
        } else {
            $('#validationResult').html(`
                <div class="validation-result bg-danger text-white">
                    <h6><i class="fas fa-times-circle"></i> Validasi Gagal</h6>
                    <p class="mb-0">${response.message}</p>
                </div>
            `);
        }
    }).fail(function() {
        $('#validationResult').html(`
            <div class="validation-result bg-danger text-white">
                <h6><i class="fas fa-times-circle"></i> Error</h6>
                <p class="mb-0">Terjadi kesalahan saat memvalidasi repository</p>
            </div>
        `);
    });
}

function inputRepository(id) {
    // Load data proposal
    $.get(`<?= base_url("staf/publikasi/detail/") ?>${id}`, function(data) {
        $('#proposal_id').val(data.id);
        $('#nama_mahasiswa').val(data.nama_mahasiswa);
        $('#judul_tugas_akhir').val(data.judul);
        $('#link_repository').val(data.link_repository || '');
        $('#validasi_staf_publikasi').val(data.validasi_staf_publikasi || '0');
        $('#catatan_staf').val(data.catatan_staf || '');
        
        // Detect platform from URL
        if (data.link_repository) {
            if (data.link_repository.includes('github.com')) {
                $('#platform_repository').val('GitHub');
            } else if (data.link_repository.includes('gitlab.com')) {
                $('#platform_repository').val('GitLab');
            } else if (data.link_repository.includes('bitbucket.org')) {
                $('#platform_repository').val('Bitbucket');
            } else {
                $('#platform_repository').val('Other');
            }
        }
        
        $('#modalRepository').modal('show');
    });
}

function simpanRepository() {
    var formData = $('#formRepository').serialize();
    
    $.post('<?= base_url("staf/publikasi/simpan") ?>', formData, function(response) {
        if (response.success) {
            $('#modalRepository').modal('hide');
            $('#publikasiTable').DataTable().ajax.reload();
            showAlert('success', 'Data repository berhasil disimpan');
        } else {
            showAlert('error', response.message || 'Gagal menyimpan data repository');
        }
    }).fail(function() {
        showAlert('error', 'Terjadi kesalahan server');
    });
}

function viewDetail(id) {
    $('#modalDetailContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
    $('#modalDetail').modal('show');
    
    $.get(`<?= base_url("staf/publikasi/detail/") ?>${id}`, function(data) {
        $('#modalDetailContent').html(data);
    }).fail(function() {
        $('#modalDetailContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
    });
}

function tambahRepository() {
    $('#formRepository')[0].reset();
    $('#modalRepository').modal('show');
}

function exportData() {
    window.open('<?= base_url("staf/publikasi/export") ?>', '_blank');
}

function bukaSertifikat() {
    showAlert('info', 'Fitur generate sertifikat sedang dalam pengembangan');
}

function loadRepositoryChart() {
    // Sample data for repository statistics
    var ctx = document.getElementById('repositoryChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Valid', 'Pending', 'Perlu Perbaikan'],
            datasets: [{
                data: [
                    <?= isset($stats['valid']) ? $stats['valid'] : 0 ?>,
                    <?= isset($stats['pending']) ? $stats['pending'] : 0 ?>,
                    <?= isset($stats['invalid']) ? $stats['invalid'] : 0 ?>
                ],
                backgroundColor: ['#2dce89', '#fb6340', '#f5365c'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 
                     type === 'info' ? 'alert-info' : 'alert-danger';
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