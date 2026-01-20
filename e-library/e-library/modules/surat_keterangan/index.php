<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check login
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../config/database.php';

$pageTitle = "Surat Keterangan Bebas Perpustakaan";
$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - E-Library STK Yakobus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0.8rem 1.5rem;
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            font-size: 1.3rem;
        }
        .navbar-brand i { color: #ffd700; }
        .navbar .nav-link { color: rgba(255,255,255,0.95) !important; }
        
        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            width: 250px;
            background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 2px 0 15px rgba(0,0,0,0.08);
            overflow-y: auto;
        }
        .sidebar-heading {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            padding: 1rem 1.2rem 0.5rem;
            color: #64748b;
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: #475569;
            padding: 0.75rem 1.2rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link i {
            width: 22px;
            margin-right: 0.7rem;
        }
        .sidebar .nav-link:hover {
            color: #667eea;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
            border-left-color: #667eea;
        }
        .sidebar .nav-link.active {
            color: #667eea;
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.15) 0%, rgba(102, 126, 234, 0.05) 100%);
            border-left-color: #667eea;
            font-weight: 600;
        }
        
        .main-content {
            margin-left: 250px;
            margin-top: 56px;
            padding: 1.5rem;
        }
        
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #f093fb;
        }
        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            border: none;
            padding: 1rem 0.75rem;
        }
        .table tbody tr:hover {
            background: rgba(240, 147, 251, 0.05);
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        #search_results {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            max-height: 300px;
            overflow-y: auto;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .search-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
        }
        .search-item:hover {
            background: rgba(102, 126, 234, 0.08);
            color: #667eea;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../dashboard.php">
                <i class="bi bi-book-fill me-2"></i>
                <strong>E-Library STK Yakobus</strong>
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= $admin_username ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <nav class="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>dashboard.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li class="sidebar-heading">DATA MASTER</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/buku/index.php">
                    <i class="bi bi-book"></i>Data Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/mahasiswa/index.php">
                    <i class="bi bi-people"></i>Data Mahasiswa
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/dosen/index.php">
                    <i class="bi bi-person-badge"></i>Data Dosen
                </a>
            </li>
            <li class="sidebar-heading">TRANSAKSI</li>
            <li class="nav-item">
                <a class="nav-link active" href="<?= BASE_URL ?>modules/peminjaman/index.php">
                    <i class="bi bi-arrow-left-right"></i>Peminjaman Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/perpanjangan/index.php">
                    <i class="bi bi-arrow-clockwise"></i>Perpanjangan Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/pengembalian/index.php">
                    <i class="bi bi-arrow-return-left"></i>Pengembalian Buku
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/denda/index.php">
                    <i class="bi bi-cash-stack"></i>Manajemen Denda
                </a>
            </li>
            <li class="sidebar-heading">LAYANAN</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/surat_keterangan/index.php">
                    <i class="bi bi-file-earmark-text"></i>Surat Keterangan
                </a>
            </li>
            <li class="sidebar-heading">LAPORAN & UTILITAS</li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/laporan/index.php">
                    <i class="bi bi-file-bar-graph"></i>Laporan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>modules/backup/index.php">
                    <i class="bi bi-cloud-download"></i>Backup Database
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h1><i class="bi bi-file-earmark-text me-2"></i><?= $pageTitle ?></h1>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBuatSurat">
                    <i class="bi bi-plus-circle me-2"></i>Buat Surat Baru
                </button>
                <a href="laporan.php" class="btn btn-info text-white">
                    <i class="bi bi-file-bar-graph me-2"></i>Laporan
                </a>
            </div>
        </div>

                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <div class="d-flex gap-2">
                       <input type="text" id="filterNIM" class="form-control" placeholder="Cari NIM..." style="width: 150px;">
                       <button class="btn btn-primary" onclick="loadData()"><i class="bi bi-search"></i></button>
                       <button class="btn btn-secondary" onclick="resetFilter()"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button class="btn btn-danger" id="btnHapusBatch" onclick="hapusBatch()" style="display:none;">
                            <i class="bi bi-trash-fill me-2"></i>Hapus (<span id="countHapus">0</span>)
                        </button>
                
                        <button class="btn btn-secondary" onclick="cetakBatch()">
                            <i class="bi bi-printer-fill me-2"></i>Cetak (<span id="countSelected">0</span>)
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                <th width="5%">No</th>
                                
                                <th style="cursor: pointer;" onclick="changeSort('nomor_surat')">
                                    Nomor Surat <i class="bi bi-arrow-down-up text-muted small ms-1" id="icon-nomor_surat"></i>
                                </th>
                                <th style="cursor: pointer;" onclick="changeSort('nim')">
                                    NIM <i class="bi bi-arrow-down-up text-muted small ms-1" id="icon-nim"></i>
                                </th>
                                <th style="cursor: pointer;" onclick="changeSort('nama')">
                                    Nama <i class="bi bi-arrow-down-up text-muted small ms-1" id="icon-nama"></i>
                                </th>
                                <th style="cursor: pointer;" onclick="changeSort('jenis')">
                                    Jenis <i class="bi bi-arrow-down-up text-muted small ms-1" id="icon-jenis"></i>
                                </th>
                                <th style="cursor: pointer;" onclick="changeSort('tanggal')">
                                    Tanggal <i class="bi bi-arrow-down-up text-muted small ms-1" id="icon-tanggal"></i>
                                </th>
                                <th style="cursor: pointer;" onclick="changeSort('status')">
                                    Status <i class="bi bi-arrow-down-up text-muted small ms-1" id="icon-status"></i>
                                </th>
                                
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-surat">
                            </tbody>
                    </table>
                </div>
                <div id="pagination"></div>
            </div>
        </div>
    </main>

    <!-- Modal Buat Surat -->
    <div class="modal fade" id="modalBuatSurat">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title">Buat Surat Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formBuatSurat">
                        <div id="step1">
                            <div class="mb-3">
                                <label>Jenis Surat <span class="text-danger">*</span></label>
                                <select name="jenis_surat" id="jenis_surat" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="UAS">UAS</option>
                                    <option value="PPA">PPA</option>
                                </select>
                            </div>
                            <div class="mb-3 position-relative">
                                <label>Cari Mahasiswa <span class="text-danger">*</span></label>
                                <input type="text" id="search_mahasiswa" class="form-control" placeholder="Ketik NIM atau nama (min 3 karakter)..." autocomplete="off">
                                <input type="hidden" name="nim" id="nim" required>
                                <div id="search_results" style="display: none;"></div>
                            </div>
                            <div id="infoMahasiswa" style="display: none;">
                                <div class="alert alert-info">
                                    <h6><i class="bi bi-person-check"></i> Data Mahasiswa</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><td width="30%">Nama</td><td>: <strong id="info_nama"></strong></td></tr>
                                        <tr><td>NIM</td><td>: <strong id="info_nim"></strong></td></tr>
                                        <tr><td>Prodi</td><td>: <strong id="info_prodi"></strong></td></tr>
                                        <tr><td>Angkatan</td><td>: <strong id="info_angkatan"></strong></td></tr>
                                    </table>
                                </div>
                                <div id="validasiStatus"></div>
                                <div id="overrideForm" style="display: none;">
                                    <div class="alert alert-warning">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="override_tunggakan" name="override_tunggakan" value="1">
                                            <label class="form-check-label">Override tunggakan</label>
                                        </div>
                                        <div id="catatanOverride" style="display: none;" class="mt-2">
                                            <label>Alasan</label>
                                            <textarea name="catatan" id="catatan" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end" id="btnLanjut" style="display: none;">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-success" onclick="previewSurat()"><i class="bi bi-eye"></i> Preview</button>
                                </div>
                            </div>
                        </div>
                        <div id="step2" style="display: none;">
                            <div class="alert alert-success">
                                <h6><i class="bi bi-check-circle"></i> Surat Siap</h6>
                                <p class="mb-0">Nomor: <strong id="preview_nomor"></strong></p>
                            </div>
                            <div id="previewContent" style="border: 1px solid #ddd; padding: 15px; max-height: 400px; overflow-y: auto; background: #f9f9f9;"></div>
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-secondary" onclick="kembaliStep1()"><i class="bi bi-arrow-left"></i> Kembali</button>
                                <button type="submit" class="btn btn-primary" id="btnTerbitkan"><i class="bi bi-printer"></i> Terbitkan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5>Detail Surat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// --- VARIABEL GLOBAL UNTUK SORTING & PAGINATION ---
let currentSort = 'created_at'; // Default sort berdasarkan waktu dibuat
let currentOrder = 'DESC';      // Default urutan terbaru (DESC)
let currentPage = 1;            // Halaman saat ini

$(document).ready(function() {
    loadData(); // Load data pertama kali
    
    // Logic Pencarian Mahasiswa di Modal (Auto-complete)
    let searchTimeout;
    $('#search_mahasiswa').on('input', function() {
        clearTimeout(searchTimeout);
        const term = $(this).val();
        
        if (term.length < 3) {
            $('#search_results').hide();
            return;
        }
        
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: { action: 'search_mahasiswa', term: term },
                dataType: 'json',
                success: function(data) {
                    if (data.length > 0) {
                        let html = '';
                        data.forEach(function(item) {
                            html += '<div class="search-item" data-nim="' + item.nim + '" data-nama="' + item.nama + '" data-prodi="' + item.program_studi + '" data-angkatan="' + item.angkatan + '">' + item.label + '</div>';
                        });
                        $('#search_results').html(html).show();
                    } else {
                        $('#search_results').html('<div class="search-item text-muted">Tidak ditemukan</div>').show();
                    }
                }
            });
        }, 500);
    });
    
    // Klik item hasil pencarian
    $(document).on('click', '.search-item', function() {
        if ($(this).data('nim')) {
            $('#nim').val($(this).data('nim'));
            $('#search_mahasiswa').val($(this).data('nama'));
            $('#search_results').hide();
            setTimeout(cekMahasiswa, 300);
        }
    });
    
    // Tutup dropdown search jika klik di luar
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#search_mahasiswa, #search_results').length) {
            $('#search_results').hide();
        }
    });
});

// --- FUNGSI LOAD DATA (UPDATE: Support Sorting & Pagination) ---
function loadData(page = 1) {
    currentPage = page;
    
    // Ambil nilai filter
    let nim = $('#filterNIM').val();
    let jenis = $('#filterJenis').val();
    let tahun = $('#filterTahun').val();
    
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: { 
            action: 'load_data', 
            nim: nim, 
            jenis: jenis, 
            tahun: tahun, 
            page: page,
            sort_by: currentSort,   // Kirim parameter sort
            order: currentOrder     // Kirim parameter order
        },
        dataType: 'json',
        success: function(response) {
            $('#tbody-surat').html(response.success ? response.html : '<tr><td colspan="9" class="text-center text-danger">' + response.message + '</td></tr>');
            $('#pagination').html(response.pagination || '');
            
            // Reset checkbox "Pilih Semua" setiap kali data berubah
            $('#checkAll').prop('checked', false);
            updateCount();
            
            // Update ikon panah di header tabel
            updateSortIcons();
        },
        error: function(xhr) {
            console.error('Load data error:', xhr);
            $('#tbody-surat').html('<tr><td colspan="9" class="text-center text-danger">Error loading data</td></tr>');
        }
    });
}

// --- FUNGSI SORTING (BARU) ---
function changeSort(column) {
    // Jika klik kolom yang sama, balik urutannya (ASC <-> DESC)
    if (currentSort === column) {
        currentOrder = (currentOrder === 'ASC') ? 'DESC' : 'ASC';
    } else {
        // Jika klik kolom baru, set default jadi ASC
        currentSort = column;
        currentOrder = 'ASC';
    }
    // Reload data dari halaman 1
    loadData(1);
}

function updateSortIcons() {
    // Reset semua ikon jadi netral (atas-bawah abu-abu)
    $('i[id^="icon-"]').attr('class', 'bi bi-arrow-down-up text-muted small ms-1');
    
    // Set ikon pada kolom yang aktif (biru)
    let activeIcon = $('#icon-' + currentSort);
    if (currentOrder === 'ASC') {
        activeIcon.attr('class', 'bi bi-arrow-up text-primary small ms-1');
    } else {
        activeIcon.attr('class', 'bi bi-arrow-down text-primary small ms-1');
    }
}

function resetFilter() {
    $('#filterNIM, #filterJenis, #filterTahun').val('');
    loadData(1);
}

// --- FUNGSI-FUNGSI LOGIKA SURAT ---

function cekMahasiswa() {
    const nim = $('#nim').val();
    const jenis = $('#jenis_surat').val();
    
    if (!nim || !jenis) {
        Swal.fire('Perhatian', 'Pilih jenis surat dan mahasiswa', 'warning');
        return;
    }
    
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: { action: 'cek_mahasiswa', nim: nim, jenis_surat: jenis },
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({ title: 'Memvalidasi...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        },
        success: function(response) {
            Swal.close();
            if (response.success) {
                $('#info_nama').text(response.data.nama_mahasiswa);
                $('#info_nim').text(response.data.nim);
                $('#info_prodi').text(response.data.nama_prodi);
                $('#info_angkatan').text(response.data.angkatan);
                $('#infoMahasiswa').show();
                
                let html = '';
                if (response.peminjaman_aktif) html += '<div class="alert alert-danger"><i class="bi bi-x"></i> Masih ada peminjaman aktif</div>';
                if (response.tunggakan.ada_tunggakan) {
                    html += '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Ada tunggakan: Rp ' + new Intl.NumberFormat('id-ID').format(response.tunggakan.total_denda) + '</div>';
                    $('#overrideForm').show();
                } else {
                    $('#overrideForm').hide();
                }
                if (!response.peminjaman_aktif && !response.tunggakan.ada_tunggakan) {
                    html += '<div class="alert alert-success"><i class="bi bi-check"></i> Memenuhi syarat</div>';
                    $('#btnLanjut').show();
                }
                $('#validasiStatus').html(html);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.close();
            Swal.fire('Error', 'Gagal validasi', 'error');
        }
    });
}

$(document).on('change', '#override_tunggakan', function() {
    if ($(this).is(':checked')) {
        $('#catatanOverride').show();
        $('#catatan').attr('required', true);
        $('#btnLanjut').show();
    } else {
        $('#catatanOverride').hide();
        $('#catatan').attr('required', false).val('');
        $('#btnLanjut').hide();
    }
});

function previewSurat() {
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: $('#formBuatSurat').serialize() + '&action=preview_surat',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#preview_nomor').text(response.nomor_surat);
                $('#previewContent').html(response.preview_html);
                $('#step1').hide();
                $('#step2').show();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        }
    });
}

function kembaliStep1() {
    $('#step2').hide();
    $('#step1').show();
}

$('#formBuatSurat').submit(function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Terbitkan surat ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: $(this).serialize() + '&action=terbitkan_surat',
                dataType: 'json',
                beforeSend: function() {
                    $('#btnTerbitkan').prop('disabled', true).html('<i class="bi bi-hourglass"></i> Proses...');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil', 'Surat diterbitkan', 'success').then(() => {
                            window.open(response.pdf_url, '_blank');
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                complete: function() {
                    $('#btnTerbitkan').prop('disabled', false).html('<i class="bi bi-printer"></i> Terbitkan');
                }
            });
        }
    });
});

function lihatDetail(id) {
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: { action: 'detail_surat', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#detailContent').html(response.html);
                new bootstrap.Modal(document.getElementById('modalDetail')).show();
            }
        }
    });
}

function cetakUlang(id) {
    window.open('generate_pdf.php?id=' + id, '_blank');
}

function batalkanSurat(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Batalkan surat?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: { action: 'batalkan_surat', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil', response.message, 'success');
                        loadData(currentPage); // Reload halaman yang sama
                    }
                }
            });
        }
    });
}

// --- LOGIKA BATCH PRINT (CETAK BANYAK) ---

// 1. Handle "Pilih Semua"
$('#checkAll').change(function() {
    $('.chk-surat').prop('checked', $(this).prop('checked'));
    updateCount();
});

// 2. Handle Klik Checkbox Individual
$(document).on('change', '.chk-surat', function() {
    updateCount();
    if(false === $(this).prop('checked')) {
        $('#checkAll').prop('checked', false);
    }
});

// 3. Fungsi Update Counter Jumlah
// --- UPDATE FUNGSI updateCount ---
function updateCount() {
    let count = $('.chk-surat:checked').length;
    $('#countSelected').text(count);
    $('#countHapus').text(count); // Update angka di tombol hapus
    
    // Tampilkan tombol hapus jika ada yg dicentang
    if(count > 0) {
        $('#btnHapusBatch').show();
    } else {
        $('#btnHapusBatch').hide();
    }
}

// 4. Fungsi Utama: Cetak Batch
function cetakBatch() {
    let selectedIds = [];
    $('.chk-surat:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        Swal.fire('Peringatan', 'Pilih minimal satu surat untuk dicetak!', 'warning');
        return;
    }

    Swal.fire({
        title: 'Cetak Massal',
        text: 'Akan mencetak ' + selectedIds.length + ' surat dalam format hemat kertas (2 surat/halaman). Lanjutkan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Cetak',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            let idsString = selectedIds.join(',');
            window.open('generate_batch_pdf.php?ids=' + idsString, '_blank');
        }
    });
}

// --- FUNGSI HAPUS SATUAN (Dari tombol sampah di tabel) ---
function hapusSatu(id) {
    Swal.fire({
        title: 'Hapus Permanen?',
        text: "Data dan file PDF akan dihapus permanen. QR Code tidak akan valid lagi.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            eksekusiHapus(id);
        }
    });
}

// --- FUNGSI HAPUS MASSAL (Batch) ---
function hapusBatch() {
    let selectedIds = [];
    $('.chk-surat:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    Swal.fire({
        title: 'Hapus ' + selectedIds.length + ' Data?',
        text: "PERINGATAN: Data yang dihapus tidak dapat dikembalikan. Gunakan fitur ini untuk membersihkan data lama.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Bersihkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            eksekusiHapus(selectedIds.join(','));
        }
    });
}

// --- FUNGSI EKSEKUSI AJAX HAPUS ---
function eksekusiHapus(ids) {
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: { action: 'hapus_data', ids: ids },
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({title: 'Menghapus...', didOpen: () => Swal.showLoading()});
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Terhapus!', response.message, 'success');
                loadData(currentPage); // Refresh halaman
                $('#checkAll').prop('checked', false);
                $('#btnHapusBatch').hide();
            } else {
                Swal.fire('Gagal', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Terjadi kesalahan server', 'error');
        }
    });
}
</script>
</body>
</html>
