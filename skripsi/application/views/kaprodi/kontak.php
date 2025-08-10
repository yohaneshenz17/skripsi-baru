<?php
/*
=================================================
VIEW: application/views/kaprodi/kontak.php  
=================================================
*/
?>
<?php $this->app->extend('template/kaprodi') ?>
<?php $this->app->setVar('title', 'Kontak Form - Kaprodi') ?>
<?php $this->app->section() ?>

<div class="row">
    <!-- Form Kontak -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">📧 Kirim Pesan</h3>
                        <p class="text-sm mb-0 text-muted">
                            Kirim pesan via email ke dosen, staf/admin, atau mahasiswa program studi untuk koordinasi dan komunikasi
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Alert Container -->
                <div id="alertContainer" style="display: none;">
                    <div class="alert alert-dismissible fade show" role="alert">
                        <span id="alertMessage"></span>
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                
                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Memuat data kontak...</p>
                </div>
                
                <!-- Error State -->
                <div id="errorState" class="text-center py-4" style="display: none;">
                    <i class="ni ni-notification-70 fa-3x text-warning mb-3"></i>
                    <h5 class="text-muted">Tidak dapat memuat data kontak</h5>
                    <p class="text-sm text-muted">Silakan refresh halaman atau hubungi admin</p>
                    <button class="btn btn-primary btn-sm" onclick="loadKontakData()">
                        <i class="ni ni-curved-next"></i> Coba Lagi
                    </button>
                </div>
                
                <!-- Main Form -->
                <form id="kontakForm" style="display: none;">
                    
                    <!-- Pilih Kategori Penerima -->
                    <div class="form-group">
                        <label class="form-control-label" for="kategori_penerima">
                            <i class="ni ni-circle-08"></i> Kirim Ke *
                        </label>
                        <select class="form-control" id="kategori_penerima" name="kategori_penerima" required>
                            <option value="">-- Pilih Kategori Penerima --</option>
                            <option value="dosen">👨‍🏫 Dosen</option>
                            <option value="staf">👨‍💼 Staf/Admin</option>
                            <option value="mahasiswa">🎓 Mahasiswa (Pilih Spesifik)</option>
                            <option value="semua_mahasiswa">👥 Semua Mahasiswa Program Studi</option>
                        </select>
                        <small class="form-text text-muted">
                            Pilih kategori penerima pesan
                        </small>
                    </div>
                    
                    <!-- Detail Penerima -->
                    <div class="form-group" id="detailPenerima" style="display: none;">
                        <label class="form-control-label" for="penerima_id">Detail Penerima *</label>
                        <select class="form-control" id="penerima_id" name="penerima_id" required>
                            <option value="">-- Pilih Nama Penerima --</option>
                        </select>
                        <div id="infoPenerima" class="mt-2"></div>
                    </div>
                    
                    <!-- Prioritas -->
                    <div class="form-group">
                        <label class="form-control-label" for="prioritas">
                            <i class="ni ni-notification-70"></i> Prioritas Pesan
                        </label>
                        <select class="form-control" id="prioritas" name="prioritas">
                            <option value="normal">🔵 Normal</option>
                            <option value="tinggi">🟠 Tinggi</option>
                            <option value="urgent">🔴 Urgent</option>
                            <option value="rendah">🟢 Rendah</option>
                        </select>
                    </div>
                    
                    <!-- Subjek -->
                    <div class="form-group">
                        <label class="form-control-label" for="subjek">
                            <i class="ni ni-tag"></i> Subjek *
                        </label>
                        <input type="text" class="form-control" id="subjek" name="subjek" 
                               placeholder="Masukkan subjek pesan..." maxlength="100" required>
                        <small class="form-text text-muted">Maksimal 100 karakter</small>
                    </div>
                    
                    <!-- Pesan -->
                    <div class="form-group">
                        <label class="form-control-label" for="pesan">
                            <i class="ni ni-email-83"></i> Pesan *
                        </label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="6" 
                                  placeholder="Tulis pesan Anda di sini..." maxlength="1000" required></textarea>
                        <small class="form-text text-muted">
                            <span id="karakterCount">0</span>/1000 karakter
                        </small>
                    </div>
                    
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="ni ni-curved-next"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="ni ni-send"></i> Kirim Pesan
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-xl-4">
        
        <!-- Kontak Quick Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📞 Kontak Tersedia</h5>
            </div>
            <div class="card-body" id="kontakInfo">
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-muted" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Memuat kontak...</p>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons WhatsApp -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">💬 Kontak WhatsApp</h5>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical w-100" role="group">
                    <button type="button" class="btn btn-success btn-sm mb-2">
                        <i class="fab fa-whatsapp mr-2"></i>
                        WhatsApp Dosen
                    </button>
                    <button type="button" class="btn btn-success btn-sm mb-2">
                        <i class="fab fa-whatsapp mr-2"></i>
                        WhatsApp Staf/Admin
                    </button>
                    <button type="button" class="btn btn-success btn-sm">
                        <i class="fab fa-whatsapp mr-2"></i>
                        WhatsApp Mahasiswa
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
// Global variables
let dataKontak = {};
let formLoaded = false;

$(document).ready(function() {
    console.log('🚀 Kontak form jQuery ready');
    loadKontakData();
    
    // Form change handlers
    $('#kategori_penerima').change(updatePenerima);
    $('#penerima_id').change(updateInfoPenerima);
    
    // Character counter
    $('#pesan').on('input', function() {
        const count = $(this).val().length;
        $('#karakterCount').text(count);
        
        if (count > 950) {
            $('#karakterCount').addClass('text-warning');
        } else if (count > 1000) {
            $('#karakterCount').addClass('text-danger');
        } else {
            $('#karakterCount').removeClass('text-warning text-danger');
        }
    });
    
    // Form submission
    $('#kontakForm').submit(function(e) {
        e.preventDefault();
        kirimPesan();
    });
});

function loadKontakData() {
    console.log('📡 Starting AJAX call to get_kontak_data');
    
    showElement('loadingIndicator');
    hideElement('errorState');
    hideElement('kontakForm');
    
    $.ajax({
        url: base_url + 'kaprodi/kontak/get_kontak_data',
        type: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            console.log('✅ AJAX Success:', response);
            
            if (response.status === 'success') {
                dataKontak = response.data;
                
                updateKontakInfo();
                
                hideElement('loadingIndicator');
                showElement('kontakForm');
                formLoaded = true;
                
                showAlert('success', '✅ Data kontak berhasil dimuat', true);
                
            } else {
                throw new Error(response.message || 'Gagal memuat data kontak');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', {xhr, status, error});
            console.error('Response Text:', xhr.responseText);
            
            hideElement('loadingIndicator');
            showElement('errorState');
            
            let errorMsg = 'Gagal memuat data kontak';
            if (status === 'timeout') {
                errorMsg = 'Koneksi timeout. Silakan coba lagi.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            
            showAlert('error', '❌ ' + errorMsg, false);
            updateKontakInfoError();
        }
    });
}

function updatePenerima() {
    const kategori = $('#kategori_penerima').val();
    const penerimaSelect = $('#penerima_id');
    const detailDiv = $('#detailPenerima');
    const infoDiv = $('#infoPenerima');
    
    // Reset
    penerimaSelect.html('<option value="">-- Pilih Nama Penerima --</option>');
    infoDiv.html('');
    
    if (!kategori) {
        detailDiv.hide();
        return;
    }
    
    if (kategori === 'semua_mahasiswa') {
        detailDiv.hide();
        return;
    }
    
    detailDiv.show();
    
    let options = '<option value="">-- Pilih Nama Penerima --</option>';
    let dataList = [];
    
    if (kategori === 'dosen' && dataKontak.dosen_list) {
        dataList = dataKontak.dosen_list;
    } else if (kategori === 'staf' && dataKontak.staf_list) {
        dataList = dataKontak.staf_list;
    } else if (kategori === 'mahasiswa' && dataKontak.mahasiswa_list) {
        dataList = dataKontak.mahasiswa_list;
    }
    
    dataList.forEach(function(item) {
        let displayText = item.nama;
        if (item.nim) displayText += ` (${item.nim})`;
        
        options += `<option value="${item.id}">${displayText}</option>`;
    });
    
    penerimaSelect.html(options);
}

function updateInfoPenerima() {
    const kategori = $('#kategori_penerima').val();
    const penerimaId = $('#penerima_id').val();
    const infoDiv = $('#infoPenerima');
    
    if (!kategori || !penerimaId) {
        infoDiv.html('');
        return;
    }
    
    let dataList = [];
    if (kategori === 'dosen') dataList = dataKontak.dosen_list;
    else if (kategori === 'staf') dataList = dataKontak.staf_list;
    else if (kategori === 'mahasiswa') dataList = dataKontak.mahasiswa_list;
    
    const penerima = dataList.find(item => item.id == penerimaId);
    
    if (penerima) {
        let infoHtml = `
            <div class="alert alert-info alert-sm">
                <strong>📧 Email:</strong> ${penerima.email || 'Tidak tersedia'}<br>
                <strong>📱 Telepon:</strong> ${penerima.nomor_telepon || 'Tidak tersedia'}
            </div>
        `;
        
        infoDiv.html(infoHtml);
    }
}

function updateKontakInfo() {
    const dosenCount = dataKontak.dosen_list ? dataKontak.dosen_list.length : 0;
    const stafCount = dataKontak.staf_list ? dataKontak.staf_list.length : 0;
    const mahasiswaCount = dataKontak.mahasiswa_list ? dataKontak.mahasiswa_list.length : 0;
    
    const infoHtml = `
        <div class="row text-center">
            <div class="col-4">
                <div class="border-right">
                    <h4 class="text-primary mb-0">${dosenCount}</h4>
                    <small class="text-muted">Dosen</small>
                </div>
            </div>
            <div class="col-4">
                <div class="border-right">
                    <h4 class="text-warning mb-0">${stafCount}</h4>
                    <small class="text-muted">Staf/Admin</small>
                </div>
            </div>
            <div class="col-4">
                <h4 class="text-success mb-0">${mahasiswaCount}</h4>
                <small class="text-muted">Mahasiswa</small>
            </div>
        </div>
        <hr class="my-3">
        <p class="text-sm text-muted mb-0">
            <i class="ni ni-check-bold text-success mr-1"></i>
            Kontak siap digunakan untuk komunikasi
        </p>
    `;
    
    $('#kontakInfo').html(infoHtml);
}

function updateKontakInfoError() {
    $('#kontakInfo').html(`
        <div class="text-center py-3">
            <i class="ni ni-fat-remove fa-2x text-danger mb-2"></i>
            <p class="text-muted mb-0">Gagal memuat kontak</p>
        </div>
    `);
}

function kirimPesan() {
    const kategori = $('#kategori_penerima').val();
    const penerimaId = $('#penerima_id').val();
    const subjek = $('#subjek').val().trim();
    const pesan = $('#pesan').val().trim();
    
    if (!kategori) {
        showAlert('error', 'Pilih kategori penerima terlebih dahulu!', false);
        return;
    }
    
    if (kategori !== 'semua_mahasiswa' && !penerimaId) {
        showAlert('error', 'Pilih penerima terlebih dahulu!', false);
        return;
    }
    
    if (!subjek) {
        showAlert('error', 'Subjek pesan wajib diisi!', false);
        return;
    }
    
    if (!pesan) {
        showAlert('error', 'Pesan wajib diisi!', false);
        return;
    }
    
    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Mengirim...');
    
    $.ajax({
        url: base_url + 'kaprodi/kontak/kirim_pesan',
        type: 'POST',
        dataType: 'json',
        data: {
            kategori_penerima: kategori,
            penerima_id: penerimaId,
            subjek: subjek,
            pesan: pesan,
            prioritas: $('#prioritas').val()
        },
        timeout: 30000,
        success: function(response) {
            if (response.status === 'success') {
                showAlert('success', response.message, true);
                resetForm();
            } else {
                showAlert('error', response.message || 'Gagal mengirim pesan', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Send message failed:', {xhr, status, error});
            
            let errorMsg = 'Gagal mengirim pesan. Silakan coba lagi.';
            if (status === 'timeout') {
                errorMsg = 'Pengiriman timeout. Silakan coba lagi.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            
            showAlert('error', errorMsg, false);
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
}

function resetForm() {
    $('#kontakForm')[0].reset();
    $('#detailPenerima').hide();
    $('#infoPenerima').html('');
    $('#karakterCount').text('0').removeClass('text-warning text-danger');
    hideAlert();
}

// Utility functions
function showElement(elementId) {
    $('#' + elementId).show();
}

function hideElement(elementId) {
    $('#' + elementId).hide();
}

function showAlert(type, message, autoHide = true) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'ni-check-bold' : 'ni-fat-remove';
    
    $('#alertContainer .alert')
        .removeClass('alert-success alert-danger')
        .addClass(alertClass);
    
    $('#alertMessage').html(`<i class="ni ${iconClass} mr-1"></i> ${message}`);
    $('#alertContainer').show();
    
    if (autoHide) {
        setTimeout(hideAlert, 5000);
    }
}

function hideAlert() {
    $('#alertContainer').hide();
}
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>