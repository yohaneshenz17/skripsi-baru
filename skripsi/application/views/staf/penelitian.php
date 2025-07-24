<?php
$this->load->view('template/staf', [
    'title' => 'Manajemen Surat Izin Penelitian',
    'content' => ob_start(),
    'css' => '
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
        <style>
            .surat-card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
                transition: transform 0.3s ease;
            }
            .surat-card:hover {
                transform: translateY(-5px);
            }
            .status-pending { border-left: 4px solid #fb6340; }
            .status-approved { border-left: 4px solid #2dce89; }
            .status-signed { border-left: 4px solid #5e72e4; }
            
            .document-preview {
                border: 1px dashed #dee2e6;
                border-radius: 8px;
                padding: 2rem;
                text-align: center;
                background: #f8f9fa;
            }
            .upload-zone {
                border: 2px dashed #11cdef;
                border-radius: 8px;
                padding: 2rem;
                text-align: center;
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .upload-zone:hover {
                border-color: #5e72e4;
                background: #f8f9fa;
            }
            .upload-zone.dragover {
                border-color: #2dce89;
                background: #eafbf4;
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
        <div class="card bg-gradient-warning text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Menunggu Proses</h5>
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
        <div class="card bg-gradient-info text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="card-title text-uppercase text-white mb-0">Siap Dicetak</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['approved']) ? $stats['approved'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fas fa-print"></i>
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
                        <h5 class="card-title text-uppercase text-white mb-0">Sudah Ditandatangani</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['signed']) ? $stats['signed'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                            <i class="fas fa-signature"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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
                                <i class="fas fa-clock mr-2"></i>Pending Approval (<?= isset($stats['pending']) ? $stats['pending'] : 0 ?>)
                            </button>
                        </li>
                        <li class="nav-item mb-2">
                            <button class="btn btn-outline-info btn-block" onclick="filterByStatus('1')">
                                <i class="fas fa-print mr-2"></i>Siap Dicetak (<?= isset($stats['approved']) ? $stats['approved'] : 0 ?>)
                            </button>
                        </li>
                        <li class="nav-item mb-2">
                            <button class="btn btn-outline-primary btn-block" onclick="generateBatch()">
                                <i class="fas fa-file-pdf mr-2"></i>Generate Batch PDF
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-outline-success btn-block" onclick="uploadSigned()">
                                <i class="fas fa-upload mr-2"></i>Upload Hasil TTD
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Template Surat -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="h3 mb-0">📄 Template Surat</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <h6 class="mb-1">Template Penelitian Umum</h6>
                            <small class="text-muted">Untuk penelitian di instansi umum</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadTemplate('umum')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <h6 class="mb-1">Template Penelitian Sekolah</h6>
                            <small class="text-muted">Untuk penelitian di sekolah</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadTemplate('sekolah')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <h6 class="mb-1">Template Penelitian Gereja</h6>
                            <small class="text-muted">Untuk penelitian di lingkungan gereja</small>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="downloadTemplate('gereja')">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Document Processor -->
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="h3 mb-0">🛠️ Document Processor</h6>
                <p class="text-sm mb-0">Generate dan kelola surat izin penelitian</p>
            </div>
            <div class="card-body">
                <div class="nav-wrapper">
                    <ul class="nav nav-pills nav-fill" id="tabs-icons-text" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link mb-sm-3 mb-md-0 active" id="tabs-icons-text-1-tab" data-toggle="tab" href="#tabs-icons-text-1" role="tab" aria-controls="tabs-icons-text-1" aria-selected="true">
                                <i class="fas fa-plus mr-2"></i>Generate Surat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mb-sm-3 mb-md-0" id="tabs-icons-text-2-tab" data-toggle="tab" href="#tabs-icons-text-2" role="tab" aria-controls="tabs-icons-text-2" aria-selected="false">
                                <i class="fas fa-upload mr-2"></i>Upload TTD
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mb-sm-3 mb-md-0" id="tabs-icons-text-3-tab" data-toggle="tab" href="#tabs-icons-text-3" role="tab" aria-controls="tabs-icons-text-3" aria-selected="false">
                                <i class="fas fa-history mr-2"></i>Riwayat
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="tabs-icons-text-1" role="tabpanel" aria-labelledby="tabs-icons-text-1-tab">
                        <form id="generateSuratForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Pilih Mahasiswa</label>
                                        <select class="form-control" id="mahasiswa_id" name="mahasiswa_id" required>
                                            <option value="">Pilih Mahasiswa</option>
                                            <!-- Akan diisi via AJAX -->
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Template Surat</label>
                                        <select class="form-control" id="template_surat" name="template_surat" required>
                                            <option value="">Pilih Template</option>
                                            <option value="umum">Penelitian Umum</option>
                                            <option value="sekolah">Penelitian Sekolah</option>
                                            <option value="gereja">Penelitian Gereja</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-control-label">Lokasi Penelitian</label>
                                <input type="text" class="form-control" id="lokasi_penelitian" name="lokasi_penelitian" placeholder="Nama instansi/tempat penelitian" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Alamat Lokasi</label>
                                        <textarea class="form-control" id="alamat_lokasi" name="alamat_lokasi" rows="2" placeholder="Alamat lengkap lokasi penelitian"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Kontak Person</label>
                                        <input type="text" class="form-control" id="kontak_person" name="kontak_person" placeholder="Nama dan jabatan kontak person">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-pdf"></i> Generate Surat PDF
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="tabs-icons-text-2" role="tabpanel" aria-labelledby="tabs-icons-text-2-tab">
                        <div class="upload-zone" id="uploadZone">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h5>Upload Surat yang Sudah Ditandatangani</h5>
                            <p class="text-muted">Drag & drop file PDF atau klik untuk memilih file</p>
                            <input type="file" id="signedDocument" accept=".pdf" style="display: none;">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('signedDocument').click()">
                                <i class="fas fa-folder-open"></i> Pilih File
                            </button>
                        </div>
                        
                        <div id="uploadProgress" style="display: none;" class="mt-3">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div id="uploadResult" style="display: none;" class="mt-3">
                            <!-- Upload result akan ditampilkan di sini -->
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="tabs-icons-text-3" role="tabpanel" aria-labelledby="tabs-icons-text-3-tab">
                        <div class="timeline timeline-one-side">
                            <div class="timeline-block py-2">
                                <span class="timeline-step">
                                    <i class="ni ni-check-bold text-success"></i>
                                </span>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="text-sm mb-1">Surat Generated</h6>
                                        <small class="text-muted">2 jam yang lalu</small>
                                    </div>
                                    <p class="text-xs mb-0">Generate surat izin penelitian untuk Hendro Mahasiswa</p>
                                </div>
                            </div>
                            
                            <div class="timeline-block py-2">
                                <span class="timeline-step">
                                    <i class="ni ni-cloud-upload-96 text-info"></i>
                                </span>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="text-sm mb-1">Upload Dokumen TTD</h6>
                                        <small class="text-muted">1 hari yang lalu</small>
                                    </div>
                                    <p class="text-xs mb-0">Upload 3 dokumen surat yang sudah ditandatangani</p>
                                </div>
                            </div>
                        </div>
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
                        <h6 class="h3 mb-0">📋 Data Surat Izin Penelitian</h6>
                        <p class="text-sm mb-0">Kelola pengajuan dan penerbitan surat izin penelitian</p>
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
                                <i class="fas fa-check"></i> Approved
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-flush table-hover" id="penelitianTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>NIM</th>
                                <th>Judul Penelitian</th>
                                <th>Lokasi Penelitian</th>
                                <th>Tanggal Pengajuan</th>
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

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 Detail Surat Izin Penelitian</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <!-- Content akan dimuat via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="downloadSurat()">
                    <i class="fas fa-download"></i> Download PDF
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
    var table = $('#penelitianTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url("api/staf/penelitian") ?>',
            type: 'POST'
        },
        columns: [
            { data: 'no', width: '5%' },
            { data: 'nama_mahasiswa' },
            { data: 'nim', width: '10%' },
            { data: 'judul', className: 'text-wrap' },
            { data: 'lokasi_penelitian', width: '15%' },
            { 
                data: 'created_at', 
                width: '10%',
                render: function(data) {
                    return new Date(data).toLocaleDateString('id-ID');
                }
            },
            { 
                data: 'status_izin_penelitian', 
                width: '10%',
                render: function(data, type, row) {
                    if (data == '1') {
                        return '<span class="badge badge-success">Approved</span>';
                    } else if (data == '2') {
                        return '<span class="badge badge-danger">Ditolak</span>';
                    } else {
                        return '<span class="badge badge-warning">Pending</span>';
                    }
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
                    
                    if (row.status_izin_penelitian == '0') {
                        buttons += `
                            <button class="btn btn-primary btn-sm" onclick="generateSurat(${data})" data-toggle="tooltip" title="Generate Surat">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                        `;
                    }
                    
                    if (row.status_izin_penelitian == '1') {
                        buttons += `
                            <button class="btn btn-success btn-sm" onclick="downloadSurat(${data})" data-toggle="tooltip" title="Download Surat">
                                <i class="fas fa-download"></i>
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

    // Load mahasiswa options
    loadMahasiswaOptions();
    
    // Form submission
    $('#generateSuratForm').on('submit', function(e) {
        e.preventDefault();
        generateSuratFromForm();
    });
    
    // File upload setup
    setupFileUpload();
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

function loadMahasiswaOptions() {
    $.get('<?= base_url("api/staf/mahasiswa_penelitian") ?>', function(data) {
        var options = '<option value="">Pilih Mahasiswa</option>';
        data.forEach(function(mahasiswa) {
            options += `<option value="${mahasiswa.id}">${mahasiswa.nama} (${mahasiswa.nim}) - ${mahasiswa.judul}</option>`;
        });
        $('#mahasiswa_id').html(options);
    });
}

function filterByStatus(status) {
    if (status === 'all') {
        $('#penelitianTable').DataTable().column(6).search('').draw();
    } else {
        $('#penelitianTable').DataTable().column(6).search(status).draw();
    }
}

function generateSuratFromForm() {
    var formData = $('#generateSuratForm').serialize();
    
    $.post('<?= base_url("staf/penelitian/generate_surat") ?>', formData, function(response) {
        if (response.success) {
            $('#penelitianTable').DataTable().ajax.reload();
            showAlert('success', 'Surat izin penelitian berhasil digenerate');
            
            // Download otomatis
            if (response.file_url) {
                window.open(response.file_url, '_blank');
            }
        } else {
            showAlert('error', response.message || 'Gagal generate surat');
        }
    }).fail(function() {
        showAlert('error', 'Terjadi kesalahan server');
    });
}

function generateSurat(id) {
    if (confirm('Generate surat izin penelitian untuk mahasiswa ini?')) {
        $.post('<?= base_url("staf/penelitian/generate_surat_id") ?>', { id: id }, function(response) {
            if (response.success) {
                $('#penelitianTable').DataTable().ajax.reload();
                showAlert('success', 'Surat berhasil digenerate');
                
                if (response.file_url) {
                    window.open(response.file_url, '_blank');
                }
            } else {
                showAlert('error', response.message || 'Gagal generate surat');
            }
        });
    }
}

function viewDetail(id) {
    $('#modalDetailContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat data...</div>');
    $('#modalDetail').modal('show');
    
    $.get(`<?= base_url("staf/penelitian/detail/") ?>${id}`, function(data) {
        $('#modalDetailContent').html(data);
    }).fail(function() {
        $('#modalDetailContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
    });
}

function downloadSurat(id) {
    window.open(`<?= base_url("staf/penelitian/download/") ?>${id}`, '_blank');
}

function downloadTemplate(type) {
    window.open(`<?= base_url("staf/penelitian/template/") ?>${type}`, '_blank');
}

function generateBatch() {
    if (confirm('Generate semua surat yang sudah approved?')) {
        $.post('<?= base_url("staf/penelitian/generate_batch") ?>', function(response) {
            if (response.success) {
                showAlert('success', `Berhasil generate ${response.count} surat`);
                if (response.zip_url) {
                    window.open(response.zip_url, '_blank');
                }
            } else {
                showAlert('error', response.message || 'Gagal generate batch');
            }
        });
    }
}

function uploadSigned() {
    $('#tabs-icons-text-2-tab').click();
}

function setupFileUpload() {
    var uploadZone = $('#uploadZone');
    var fileInput = $('#signedDocument');
    
    // Drag and drop
    uploadZone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    uploadZone.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    uploadZone.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0]);
        }
    });
    
    // File input change
    fileInput.on('change', function() {
        if (this.files.length > 0) {
            handleFileUpload(this.files[0]);
        }
    });
    
    // Click to open file dialog
    uploadZone.on('click', function() {
        fileInput.click();
    });
}

function handleFileUpload(file) {
    if (file.type !== 'application/pdf') {
        showAlert('error', 'Hanya file PDF yang diperbolehkan');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        showAlert('error', 'Ukuran file maksimal 5MB');
        return;
    }
    
    var formData = new FormData();
    formData.append('signed_document', file);
    
    $('#uploadProgress').show();
    
    $.ajax({
        url: '<?= base_url("staf/penelitian/upload_signed") ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total * 100;
                    $('.progress-bar').css('width', percentComplete + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            $('#uploadProgress').hide();
            
            if (response.success) {
                $('#uploadResult').html(`
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> File berhasil diupload: ${response.filename}
                    </div>
                `).show();
                
                $('#penelitianTable').DataTable().ajax.reload();
            } else {
                $('#uploadResult').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> ${response.message}
                    </div>
                `).show();
            }
        },
        error: function() {
            $('#uploadProgress').hide();
            $('#uploadResult').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> Terjadi kesalahan saat upload
                </div>
            `).show();
        }
    });
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