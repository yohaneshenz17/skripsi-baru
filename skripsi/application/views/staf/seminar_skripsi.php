<?php
$this->load->view('template/staf', [
    'title' => 'Manajemen Seminar Skripsi',
    'content' => ob_start(),
    'css' => '
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.js">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
        <style>
            .skripsi-card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
                transition: transform 0.3s ease;
            }
            .skripsi-card:hover {
                transform: translateY(-5px);
            }
            .calendar-card {
                min-height: 400px;
            }
            .fc-event {
                border-radius: 8px;
                border: none;
                color: white !important;
            }
            .fc-event-title {
                font-weight: 600;
            }
            .berita-acara-preview {
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 1.5rem;
                background: #f8f9fa;
            }
            .nilai-input {
                width: 80px;
                text-align: center;
            }
        </style>
    ',
    'script' => '
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
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
                            <i class="fas fa-graduation-cap"></i>
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
                        <h5 class="card-title text-uppercase text-white mb-0">Dijadwalkan</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['dijadwalkan']) ? $stats['dijadwalkan'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-success rounded-circle shadow">
                            <i class="fas fa-calendar-check"></i>
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
                        <h5 class="card-title text-uppercase text-white mb-0">Selesai</h5>
                        <span class="h2 font-weight-bold mb-0 text-white"><?= isset($stats['selesai']) ? $stats['selesai'] : 0 ?></span>
                    </div>
                    <div class="col-auto">
                        <div class="icon icon-shape bg-white text-info rounded-circle shadow">
                            <i class="fas fa-check-circle"></i>
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
    <!-- Calendar Section -->
    <div class="col-xl-8 mb-4">
        <div class="card calendar-card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">📅 Kalender Seminar Skripsi</h6>
                        <p class="text-sm mb-0">Jadwal seminar skripsi bulan ini</p>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group">
                            <button class="btn btn-outline-primary btn-sm" onclick="viewCalendar('month')">
                                <i class="fas fa-calendar"></i> Bulan
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="viewCalendar('week')">
                                <i class="fas fa-calendar-week"></i> Minggu
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="viewCalendar('day')">
                                <i class="fas fa-calendar-day"></i> Hari
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions & Today's Schedule -->
    <div class="col-xl-4 mb-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="h3 mb-0">⚡ Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="nav-wrapper">
                    <ul class="nav nav-pills nav-fill flex-column" role="tablist">
                        <li class="nav-item mb-2">
                            <button class="btn btn-outline-warning btn-block" onclick="filterByStatus('0')">
                                <i class="fas fa-clock mr-2"></i>Pending Review (<?= isset($stats['pending']) ? $stats['pending'] : 0 ?>)
                            </button>
                        </li>
                        <li class="nav-item mb-2">
                            <button class="btn btn-outline-success btn-block" onclick="filterByStatus('1')">
                                <i class="fas fa-calendar-check mr-2"></i>Sudah Dijadwalkan (<?= isset($stats['dijadwalkan']) ? $stats['dijadwalkan'] : 0 ?>)
                            </button>
                        </li>
                        <li class="nav-item mb-2">
                            <button class="btn btn-outline-primary btn-block" onclick="tambahJadwal()">
                                <i class="fas fa-plus mr-2"></i>Tambah Jadwal Baru
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-outline-info btn-block" onclick="exportBeritaAcara()">
                                <i class="fas fa-file-pdf mr-2"></i>Export Semua Berita Acara
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Today's Schedule -->
        <div class="card">
            <div class="card-header">
                <h6 class="h3 mb-0">📋 Jadwal Hari Ini</h6>
                <p class="text-sm mb-0"><?= date('d F Y') ?></p>
            </div>
            <div class="card-body">
                <div id="todaySchedule">
                    <!-- Schedule akan dimuat via AJAX -->
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin text-muted"></i>
                        <p class="text-muted mt-2">Memuat jadwal...</p>
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
                        <h6 class="h3 mb-0">🎓 Data Seminar Skripsi</h6>
                        <p class="text-sm mb-0">Kelola pengajuan dan penjadwalan seminar skripsi</p>
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
                                <i class="fas fa-check"></i> Dijadwalkan
                            </button>
                            <button class="btn btn-outline-info btn-sm" onclick="filterByStatus('2')">
                                <i class="fas fa-graduation-cap"></i> Selesai
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-flush table-hover" id="skripsiTable">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Mahasiswa</th>
                                <th>NIM</th>
                                <th>Judul Skripsi</th>
                                <th>Pembimbing</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Jadwal Seminar</th>
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

<!-- Modal Penjadwalan -->
<div class="modal fade" id="modalJadwal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📅 Penjadwalan Seminar Skripsi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formJadwal">
                <div class="modal-body">
                    <input type="hidden" id="proposal_id" name="proposal_id">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong> Pastikan mahasiswa sudah menyelesaikan penelitian dan siap untuk seminar skripsi (Bab 1-5).
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Tanggal Seminar <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_seminar_skripsi" name="tanggal_seminar_skripsi" required>
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
                        <select class="form-control" id="tempat_seminar_skripsi" name="tempat_seminar_skripsi" required>
                            <option value="">Pilih Tempat</option>
                            <option value="Aula STK Santo Yakobus">Aula STK Santo Yakobus</option>
                            <option value="Ruang Seminar 1">Ruang Seminar 1</option>
                            <option value="Ruang Seminar 2">Ruang Seminar 2</option>
                            <option value="Ruang Kelas A">Ruang Kelas A</option>
                            <option value="Ruang Kelas B">Ruang Kelas B</option>
                            <option value="Online (Zoom)">Online (Zoom)</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Dosen Penguji 1 <span class="text-danger">*</span></label>
                                <select class="form-control" id="penguji1_skripsi" name="penguji1_skripsi" required>
                                    <option value="">Pilih Dosen Penguji 1</option>
                                    <!-- Akan diisi via AJAX -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Dosen Penguji 2</label>
                                <select class="form-control" id="penguji2_skripsi" name="penguji2_skripsi">
                                    <option value="">Pilih Dosen Penguji 2 (Opsional)</option>
                                    <!-- Akan diisi via AJAX -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Catatan Penjadwalan</label>
                        <textarea class="form-control" id="catatan_jadwal" name="catatan_jadwal" rows="3" placeholder="Catatan khusus untuk seminar skripsi..."></textarea>
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

<!-- Modal Berita Acara -->
<div class="modal fade" id="modalBeritaAcara" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 Berita Acara Seminar Skripsi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formBeritaAcara">
                <div class="modal-body">
                    <input type="hidden" id="berita_proposal_id" name="proposal_id">
                    
                    <!-- Info Mahasiswa -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informasi Mahasiswa</h6>
                            <p class="mb-1"><strong>Nama:</strong> <span id="berita_nama_mahasiswa">-</span></p>
                            <p class="mb-1"><strong>NIM:</strong> <span id="berita_nim">-</span></p>
                            <p class="mb-0"><strong>Prodi:</strong> <span id="berita_prodi">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Informasi Seminar</h6>
                            <p class="mb-1"><strong>Tanggal:</strong> <span id="berita_tanggal">-</span></p>
                            <p class="mb-1"><strong>Waktu:</strong> <span id="berita_waktu">-</span></p>
                            <p class="mb-0"><strong>Tempat:</strong> <span id="berita_tempat">-</span></p>
                        </div>
                    </div>
                    
                    <!-- Penilaian -->
                    <div class="row">
                        <div class="col-12">
                            <h6>Penilaian Seminar Skripsi</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="40%">Aspek Penilaian</th>
                                            <th width="15%">Bobot (%)</th>
                                            <th width="15%">Nilai Pembimbing</th>
                                            <th width="15%">Nilai Penguji 1</th>
                                            <th width="15%">Nilai Penguji 2</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Isi/Substansi Skripsi</td>
                                            <td class="text-center">40</td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_pembimbing_isi" min="0" max="100"></td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_penguji1_isi" min="0" max="100"></td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_penguji2_isi" min="0" max="100"></td>
                                        </tr>
                                        <tr>
                                            <td>Presentasi dan Komunikasi</td>
                                            <td class="text-center">30</td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_pembimbing_presentasi" min="0" max="100"></td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_penguji1_presentasi" min="0" max="100"></td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_penguji2_presentasi" min="0" max="100"></td>
                                        </tr>
                                        <tr>
                                            <td>Penguasaan Materi</td>
                                            <td class="text-center">20</td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_pembimbing_penguasaan" min="0" max="100"></td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_penguji1_penguasaan" min="0" max="100"></td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_penguji2_penguasaan" min="0" max="100"></td>
                                        </tr>
                                        <tr>
                                            <td>Attitude dan Etika</td>
                                            <td class="text-center">10</td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_pembimbing_attitude" min="0" max="100"></td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_penguji1_attitude" min="0" max="100"></td>
                                            <td><input type="number" class="form-control nilai-input" name="nilai_penguji2_attitude" min="0" max="100"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hasil dan Catatan -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Hasil Seminar <span class="text-danger">*</span></label>
                                <select class="form-control" id="hasil_seminar" name="hasil_seminar" required>
                                    <option value="">Pilih Hasil</option>
                                    <option value="lulus">Lulus</option>
                                    <option value="lulus_perbaikan">Lulus dengan Perbaikan</option>
                                    <option value="tidak_lulus">Tidak Lulus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">Nilai Akhir</label>
                                <input type="number" class="form-control" id="nilai_akhir" name="nilai_akhir" min="0" max="100" readonly>
                                <small class="text-muted">Otomatis dihitung dari penilaian</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-control-label">Catatan dan Saran Perbaikan</label>
                        <textarea class="form-control" id="catatan_berita_acara" name="catatan_berita_acara" rows="4" placeholder="Catatan hasil seminar dan saran perbaikan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Berita Acara
                    </button>
                </div>
            </form>
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
    var table = $('#skripsiTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url("api/staf/seminar_skripsi") ?>',
            type: 'POST'
        },
        columns: [
            { data: 'no', width: '5%' },
            { data: 'nama_mahasiswa' },
            { data: 'nim', width: '10%' },
            { data: 'judul', className: 'text-wrap' },
            { data: 'nama_pembimbing', width: '15%' },
            { 
                data: 'created_at', 
                width: '10%',
                render: function(data) {
                    return new Date(data).toLocaleDateString('id-ID');
                }
            },
            { 
                data: 'tanggal_seminar_skripsi', 
                width: '10%',
                render: function(data, type, row) {
                    if (data) {
                        return new Date(data).toLocaleDateString('id-ID') + '<br><small class="text-muted">' + row.tempat_seminar_skripsi + '</small>';
                    }
                    return '<span class="text-muted">Belum dijadwalkan</span>';
                }
            },
            { 
                data: 'status_seminar_skripsi', 
                width: '8%',
                render: function(data, type, row) {
                    if (data == '1' && row.tanggal_seminar_skripsi) {
                        return '<span class="badge badge-success">Dijadwalkan</span>';
                    } else if (data == '1') {
                        return '<span class="badge badge-info">Disetujui</span>';
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
                    
                    if (row.status_seminar_skripsi == '1' && !row.tanggal_seminar_skripsi) {
                        buttons += `
                            <button class="btn btn-primary btn-sm" onclick="jadwalkanSeminar(${data})" data-toggle="tooltip" title="Jadwalkan">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                        `;
                    }
                    
                    if (row.tanggal_seminar_skripsi) {
                        buttons += `
                            <button class="btn btn-success btn-sm" onclick="inputBeritaAcara(${data})" data-toggle="tooltip" title="Input Berita Acara">
                                <i class="fas fa-file-alt"></i>
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

    // Initialize Calendar
    initializeCalendar();
    
    // Load dosen options
    loadDosenOptions();
    
    // Load today's schedule
    loadTodaySchedule();
    
    // Form submissions
    $('#formJadwal').on('submit', function(e) {
        e.preventDefault();
        simpanJadwal();
    });
    
    $('#formBeritaAcara').on('submit', function(e) {
        e.preventDefault();
        simpanBeritaAcara();
    });
    
    // Auto calculate nilai akhir
    $('[name^="nilai_"]').on('input', function() {
        calculateNilaiAkhir();
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

function initializeCalendar() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'id',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            $.get('<?= base_url("api/staf/seminar_skripsi_calendar") ?>', {
                start: fetchInfo.startStr,
                end: fetchInfo.endStr
            }, function(data) {
                successCallback(data);
            }).fail(function() {
                failureCallback();
            });
        },
        eventClick: function(info) {
            viewDetail(info.event.extendedProps.proposal_id);
        }
    });
    calendar.render();
    
    // Store calendar globally
    window.seminarCalendar = calendar;
}

function loadDosenOptions() {
    $.get('<?= base_url("api/staf/dosen") ?>', function(data) {
        var options = '<option value="">Pilih Dosen</option>';
        data.forEach(function(dosen) {
            if (dosen.level == '2') { // Hanya dosen biasa yang bisa jadi penguji
                options += `<option value="${dosen.id}">${dosen.nama}</option>`;
            }
        });
        $('#penguji1_skripsi, #penguji2_skripsi').html(options);
    });
}

function loadTodaySchedule() {
    var today = new Date().toISOString().split('T')[0];
    
    $.get('<?= base_url("api/staf/seminar_skripsi_today") ?>', { date: today }, function(data) {
        var html = '';
        
        if (data.length > 0) {
            data.forEach(function(item) {
                html += `
                    <div class="timeline-block py-2">
                        <span class="timeline-step">
                            <i class="fas fa-graduation-cap text-primary"></i>
                        </span>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="text-sm mb-1">${item.nama_mahasiswa}</h6>
                                <small class="text-muted">${item.waktu}</small>
                            </div>
                            <p class="text-xs mb-1">${item.tempat}</p>
                            <p class="text-xs text-muted mb-0">${item.judul.substring(0, 50)}...</p>
                        </div>
                    </div>
                `;
            });
        } else {
            html = `
                <div class="text-center py-4">
                    <i class="fas fa-calendar-day fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Tidak ada seminar hari ini</p>
                </div>
            `;
        }
        
        $('#todaySchedule').html(html);
    }).fail(function() {
        $('#todaySchedule').html(`
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">Gagal memuat jadwal</p>
            </div>
        `);
    });
}

function filterByStatus(status) {
    if (status === 'all') {
        $('#skripsiTable').DataTable().column(7).search('').draw();
    } else {
        $('#skripsiTable').DataTable().column(7).search(status).draw();
    }
}

function viewCalendar(view) {
    if (window.seminarCalendar) {
        window.seminarCalendar.changeView(view === 'month' ? 'dayGridMonth' : 
                                         view === 'week' ? 'timeGridWeek' : 'timeGridDay');
    }
}

function jadwalkanSeminar(id) {
    $('#proposal_id').val(id);
    $('#modalJadwal').modal('show');
}

function simpanJadwal() {
    var formData = $('#formJadwal').serialize();
    
    $.post('<?= base_url("staf/seminar_skripsi/jadwal") ?>', formData, function(response) {
        if (response.success) {
            $('#modalJadwal').modal('hide');
            $('#skripsiTable').DataTable().ajax.reload();
            if (window.seminarCalendar) {
                window.seminarCalendar.refetchEvents();
            }
            loadTodaySchedule();
            showAlert('success', 'Jadwal seminar berhasil disimpan');
        } else {
            showAlert('error', response.message || 'Gagal menyimpan jadwal');
        }
    }).fail(function() {
        showAlert('error', 'Terjadi kesalahan server');
    });
}

function inputBeritaAcara(id) {
    // Load data seminar
    $.get(`<?= base_url("staf/seminar_skripsi/detail/") ?>${id}`, function(data) {
        $('#berita_proposal_id').val(data.id);
        $('#berita_nama_mahasiswa').text(data.nama_mahasiswa);
        $('#berita_nim').text(data.nim);
        $('#berita_prodi').text(data.nama_prodi);
        $('#berita_tanggal').text(new Date(data.tanggal_seminar_skripsi).toLocaleDateString('id-ID'));
        $('#berita_waktu').text(data.waktu_seminar || '-');
        $('#berita_tempat').text(data.tempat_seminar_skripsi);
        
        $('#modalBeritaAcara').modal('show');
    });
}

function simpanBeritaAcara() {
    var formData = $('#formBeritaAcara').serialize();
    
    $.post('<?= base_url("staf/seminar_skripsi/berita_acara") ?>', formData, function(response) {
        if (response.success) {
            $('#modalBeritaAcara').modal('hide');
            $('#skripsiTable').DataTable().ajax.reload();
            showAlert('success', 'Berita acara berhasil disimpan');
        } else {
            showAlert('error', response.message || 'Gagal menyimpan berita acara');
        }
    }).fail(function() {
        showAlert('error', 'Terjadi kesalahan server');
    });
}

function calculateNilaiAkhir() {
    var bobotIsi = 0.4;
    var bobotPresentasi = 0.3;
    var bobotPenguasaan = 0.2;
    var bobotAttitude = 0.1;
    
    // Get all nilai
    var nilaiPembimbingIsi = parseFloat($('[name="nilai_pembimbing_isi"]').val()) || 0;
    var nilaiPenguji1Isi = parseFloat($('[name="nilai_penguji1_isi"]').val()) || 0;
    var nilaiPenguji2Isi = parseFloat($('[name="nilai_penguji2_isi"]').val()) || 0;
    
    var nilaiPembimbingPresentasi = parseFloat($('[name="nilai_pembimbing_presentasi"]').val()) || 0;
    var nilaiPenguji1Presentasi = parseFloat($('[name="nilai_penguji1_presentasi"]').val()) || 0;
    var nilaiPenguji2Presentasi = parseFloat($('[name="nilai_penguji2_presentasi"]').val()) || 0;
    
    var nilaiPembimbingPenguasaan = parseFloat($('[name="nilai_pembimbing_penguasaan"]').val()) || 0;
    var nilaiPenguji1Penguasaan = parseFloat($('[name="nilai_penguji1_penguasaan"]').val()) || 0;
    var nilaiPenguji2Penguasaan = parseFloat($('[name="nilai_penguji2_penguasaan"]').val()) || 0;
    
    var nilaiPembimbingAttitude = parseFloat($('[name="nilai_pembimbing_attitude"]').val()) || 0;
    var nilaiPenguji1Attitude = parseFloat($('[name="nilai_penguji1_attitude"]').val()) || 0;
    var nilaiPenguji2Attitude = parseFloat($('[name="nilai_penguji2_attitude"]').val()) || 0;
    
    // Calculate average for each aspect
    var rataIsi = (nilaiPembimbingIsi + nilaiPenguji1Isi + nilaiPenguji2Isi) / 3;
    var rataPresentasi = (nilaiPembimbingPresentasi + nilaiPenguji1Presentasi + nilaiPenguji2Presentasi) / 3;
    var rataPenguasaan = (nilaiPembimbingPenguasaan + nilaiPenguji1Penguasaan + nilaiPenguji2Penguasaan) / 3;
    var rataAttitude = (nilaiPembimbingAttitude + nilaiPenguji1Attitude + nilaiPenguji2Attitude) / 3;
    
    // Calculate final score
    var nilaiAkhir = (rataIsi * bobotIsi) + (rataPresentasi * bobotPresentasi) + 
                     (rataPenguasaan * bobotPenguasaan) + (rataAttitude * bobotAttitude);
    
    $('#nilai_akhir').val(Math.round(nilaiAkhir * 100) / 100);
}

function viewDetail(id) {
    window.open(`<?= base_url("staf/seminar_skripsi/detail/") ?>${id}`, '_blank');
}

function tambahJadwal() {
    $('#formJadwal')[0].reset();
    $('#modalJadwal').modal('show');
}

function exportBeritaAcara() {
    window.open('<?= base_url("staf/seminar_skripsi/export_berita_acara") ?>', '_blank');
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