<?php
/**
 * Halaman Utama Modul Surat Keterangan
 * Sekolah Tinggi Katolik Santo Yakobus Merauke
 */

require_once 'config.php';
cekLoginAdmin();

$pageTitle = "Surat Keterangan Bebas Perpustakaan";
include '../../includes/header.php'; // Sesuaikan dengan struktur Anda
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt"></i> Surat Keterangan Bebas Perpustakaan
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalBuatSurat">
                            <i class="fas fa-plus"></i> Buat Surat Baru
                        </button>
                        <a href="laporan.php" class="btn btn-sm btn-info">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" id="filterNIM" class="form-control" placeholder="Cari NIM...">
                        </div>
                        <div class="col-md-3">
                            <select id="filterJenis" class="form-control">
                                <option value="">Semua Jenis Surat</option>
                                <option value="UAS">UAS</option>
                                <option value="PPA">PPA</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filterTahun" class="form-control">
                                <option value="">Semua Tahun</option>
                                <?php 
                                $tahun_sekarang = date('Y');
                                for ($i = $tahun_sekarang; $i >= $tahun_sekarang - 5; $i--) {
                                    echo "<option value='{$i}'>{$i}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary" onclick="loadData()">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- Tabel Riwayat Surat -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tableSurat">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Nomor Surat</th>
                                    <th width="10%">NIM</th>
                                    <th width="20%">Nama Mahasiswa</th>
                                    <th width="10%">Jenis</th>
                                    <th width="12%">Tanggal Terbit</th>
                                    <th width="8%">Status</th>
                                    <th width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-surat">
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Memuat data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Surat -->
<div class="modal fade" id="modalBuatSurat" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Surat Keterangan Baru</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formBuatSurat">
                    <!-- Step 1: Pilih Jenis & Input NIM -->
                    <div id="step1">
                        <div class="form-group">
                            <label>Jenis Surat <span class="text-danger">*</span></label>
                            <select name="jenis_surat" id="jenis_surat" class="form-control" required>
                                <option value="">-- Pilih Jenis Surat --</option>
                                <option value="UAS">Ujian Akhir Semester (UAS)</option>
                                <option value="PPA">Penilaian Pembelajaran Akhir (PPA)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>NIM Mahasiswa <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="nim" id="nim" class="form-control" 
                                       placeholder="Masukkan NIM" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" onclick="cekMahasiswa()">
                                        <i class="fas fa-search"></i> Cek
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Masukkan NIM mahasiswa dan klik tombol Cek
                            </small>
                        </div>

                        <div id="infoMahasiswa" style="display: none;">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-user"></i> Data Mahasiswa</h5>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="30%">Nama</td>
                                        <td>: <span id="info_nama"></span></td>
                                    </tr>
                                    <tr>
                                        <td>NIM</td>
                                        <td>: <span id="info_nim"></span></td>
                                    </tr>
                                    <tr>
                                        <td>Program Studi</td>
                                        <td>: <span id="info_prodi"></span></td>
                                    </tr>
                                    <tr>
                                        <td>Angkatan</td>
                                        <td>: <span id="info_angkatan"></span></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Validasi Status -->
                            <div id="validasiStatus"></div>

                            <!-- Override Form -->
                            <div id="overrideForm" style="display: none;">
                                <div class="alert alert-warning">
                                    <div class="form-group mb-0">
                                        <label>
                                            <input type="checkbox" id="override_tunggakan" name="override_tunggakan" value="1">
                                            Override validasi tunggakan (Admin dapat mengabaikan tunggakan)
                                        </label>
                                    </div>
                                    <div class="form-group mb-0 mt-2" id="catatanOverride" style="display: none;">
                                        <label>Alasan Override <span class="text-danger">*</span></label>
                                        <textarea name="catatan" id="catatan" class="form-control" rows="2" 
                                                  placeholder="Jelaskan alasan mengapa tunggakan diabaikan..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="text-right" id="btnLanjut" style="display: none;">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-success" onclick="previewSurat()">
                                    <i class="fas fa-eye"></i> Preview Surat
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Preview -->
                    <div id="step2" style="display: none;">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Surat Siap Diterbitkan</h5>
                            <p class="mb-0">Nomor Surat: <strong id="preview_nomor"></strong></p>
                        </div>

                        <div id="previewContent" class="border p-3" style="background: #f9f9f9;">
                            <!-- Preview surat akan dimuat di sini -->
                        </div>

                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-secondary" onclick="kembaliStep1()">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnTerbitkan">
                                <i class="fas fa-print"></i> Terbitkan & Cetak
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Surat -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Surat Keterangan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadData();
});

// Load data surat
function loadData(page = 1) {
    const nim = $('#filterNIM').val();
    const jenis = $('#filterJenis').val();
    const tahun = $('#filterTahun').val();
    
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: {
            action: 'load_data',
            nim: nim,
            jenis: jenis,
            tahun: tahun,
            page: page
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#tbody-surat').html(response.html);
                $('#pagination').html(response.pagination);
            } else {
                $('#tbody-surat').html('<tr><td colspan="8" class="text-center text-danger">' + response.message + '</td></tr>');
            }
        },
        error: function() {
            $('#tbody-surat').html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat data</td></tr>');
        }
    });
}

// Reset filter
function resetFilter() {
    $('#filterNIM').val('');
    $('#filterJenis').val('');
    $('#filterTahun').val('');
    loadData();
}

// Cek mahasiswa
function cekMahasiswa() {
    const nim = $('#nim').val();
    const jenis_surat = $('#jenis_surat').val();
    
    if (!nim) {
        Swal.fire('Perhatian', 'Masukkan NIM terlebih dahulu', 'warning');
        return;
    }
    
    if (!jenis_surat) {
        Swal.fire('Perhatian', 'Pilih jenis surat terlebih dahulu', 'warning');
        return;
    }
    
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: {
            action: 'cek_mahasiswa',
            nim: nim,
            jenis_surat: jenis_surat
        },
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({
                title: 'Memvalidasi...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
        },
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                // Tampilkan data mahasiswa
                $('#info_nama').text(response.data.nama_mahasiswa);
                $('#info_nim').text(response.data.nim);
                $('#info_prodi').text(response.data.nama_prodi);
                $('#info_angkatan').text(response.data.angkatan);
                $('#infoMahasiswa').show();
                
                // Tampilkan status validasi
                let validasiHtml = '';
                
                if (response.peminjaman_aktif) {
                    validasiHtml += '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Mahasiswa masih memiliki peminjaman aktif</div>';
                }
                
                if (response.tunggakan.ada_tunggakan) {
                    validasiHtml += '<div class="alert alert-danger">';
                    validasiHtml += '<i class="fas fa-exclamation-triangle"></i> ';
                    validasiHtml += 'Mahasiswa memiliki tunggakan denda: <strong>' + response.tunggakan.jumlah + ' tunggakan</strong> ';
                    validasiHtml += '(Total: Rp ' + number_format(response.tunggakan.total_denda) + ')';
                    validasiHtml += '</div>';
                    $('#overrideForm').show();
                } else {
                    $('#overrideForm').hide();
                }
                
                if (!response.peminjaman_aktif && !response.tunggakan.ada_tunggakan) {
                    validasiHtml += '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Mahasiswa memenuhi syarat untuk mendapatkan surat keterangan</div>';
                    $('#btnLanjut').show();
                }
                
                $('#validasiStatus').html(validasiHtml);
                
            } else {
                Swal.fire('Error', response.message, 'error');
                $('#infoMahasiswa').hide();
            }
        },
        error: function() {
            Swal.close();
            Swal.fire('Error', 'Gagal memvalidasi mahasiswa', 'error');
        }
    });
}

// Override checkbox event
$(document).on('change', '#override_tunggakan', function() {
    if ($(this).is(':checked')) {
        $('#catatanOverride').show();
        $('#catatan').attr('required', true);
        $('#btnLanjut').show();
    } else {
        $('#catatanOverride').hide();
        $('#catatan').attr('required', false);
        $('#catatan').val('');
        $('#btnLanjut').hide();
    }
});

// Preview surat
function previewSurat() {
    const formData = $('#formBuatSurat').serialize();
    
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: formData + '&action=preview_surat',
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
        },
        error: function() {
            Swal.fire('Error', 'Gagal membuat preview', 'error');
        }
    });
}

// Kembali ke step 1
function kembaliStep1() {
    $('#step2').hide();
    $('#step1').show();
}

// Submit form
$('#formBuatSurat').submit(function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Terbitkan surat keterangan ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Terbitkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: formData + '&action=terbitkan_surat',
                dataType: 'json',
                beforeSend: function() {
                    $('#btnTerbitkan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: 'Surat berhasil diterbitkan',
                            icon: 'success'
                        }).then(() => {
                            // Buka PDF di tab baru
                            window.open(response.pdf_url, '_blank');
                            
                            // Reset form dan reload data
                            $('#modalBuatSurat').modal('hide');
                            $('#formBuatSurat')[0].reset();
                            $('#step2').hide();
                            $('#step1').show();
                            $('#infoMahasiswa').hide();
                            loadData();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Gagal menerbitkan surat', 'error');
                },
                complete: function() {
                    $('#btnTerbitkan').prop('disabled', false).html('<i class="fas fa-print"></i> Terbitkan & Cetak');
                }
            });
        }
    });
});

// Lihat detail
function lihatDetail(id) {
    $.ajax({
        url: 'ajax_handler.php',
        type: 'POST',
        data: {
            action: 'detail_surat',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#detailContent').html(response.html);
                $('#modalDetail').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        }
    });
}

// Cetak ulang
function cetakUlang(id) {
    window.open('generate_pdf.php?id=' + id, '_blank');
}

// Batalkan surat
function batalkanSurat(id) {
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Batalkan surat ini? Surat yang dibatalkan tidak dapat diaktifkan kembali.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: {
                    action: 'batalkan_surat',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil', response.message, 'success');
                        loadData();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}

// Helper function
function number_format(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}
</script>

<?php include '../../includes/footer.php'; ?>
