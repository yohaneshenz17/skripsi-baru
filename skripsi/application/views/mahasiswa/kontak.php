<?php $this->app->extend('template/mahasiswa') ?>
<?php $this->app->setVar('title', 'Kontak Form') ?>
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
                            Kirim pesan ke dosen pembimbing, kaprodi, atau staf untuk konsultasi atau bantuan
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
                    
                    <!-- Pilih Penerima -->
                    <div class="form-group">
                        <label class="form-control-label" for="penerima_role">
                            <i class="ni ni-circle-08"></i> Kirim Ke *
                        </label>
                        <select class="form-control" id="penerima_role" name="penerima_role" required>
                            <option value="">-- Pilih Penerima --</option>
                        </select>
                    </div>
                    
                    <!-- Detail Penerima -->
                    <div class="form-group" id="detailPenerima" style="display: none;">
                        <label class="form-control-label" for="penerima_id">Detail Penerima</label>
                        <select class="form-control" id="penerima_id" name="penerima_id">
                            <option value="">-- Pilih --</option>
                        </select>
                        <div id="infoPenerima" class="mt-2"></div>
                    </div>
                    
                    <!-- Prioritas -->
                    <div class="form-group">
                        <label class="form-control-label" for="prioritas">
                            <i class="ni ni-notification-70"></i> Prioritas
                        </label>
                        <select class="form-control" id="prioritas" name="prioritas">
                            <option value="normal">Normal</option>
                            <option value="high">Prioritas Tinggi</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <small class="form-text text-muted">
                            Pilih "Urgent" hanya untuk hal yang sangat mendesak
                        </small>
                    </div>
                    
                    <!-- Subjek -->
                    <div class="form-group">
                        <label class="form-control-label" for="subjek">
                            <i class="ni ni-tag"></i> Subjek *
                        </label>
                        <input type="text" class="form-control" id="subjek" name="subjek" 
                               placeholder="Contoh: Konsultasi Proposal Skripsi" maxlength="200" required>
                    </div>
                    
                    <!-- Pesan -->
                    <div class="form-group">
                        <label class="form-control-label" for="pesan">
                            <i class="ni ni-align-left-2"></i> Pesan *
                        </label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="6" 
                                  placeholder="Tuliskan pesan Anda dengan jelas dan sopan..." required></textarea>
                        <small class="form-text text-muted">
                            <span id="charCount">0</span> karakter. Minimal 10 karakter.
                        </small>
                    </div>
                    
                    <!-- Template Pesan Cepat -->
                    <div class="form-group">
                        <label class="form-control-label">Template Pesan Cepat</label>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="useTemplate('konsultasi')">
                                    <i class="ni ni-bulb-61"></i> Konsultasi Proposal
                                </button>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-info btn-sm btn-block" onclick="useTemplate('bimbingan')">
                                    <i class="ni ni-calendar-grid-58"></i> Jadwal Bimbingan
                                </button>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-success btn-sm btn-block" onclick="useTemplate('seminar')">
                                    <i class="ni ni-hat-3"></i> Pendaftaran Seminar
                                </button>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-warning btn-sm btn-block" onclick="useTemplate('penelitian')">
                                    <i class="ni ni-atom"></i> Izin Penelitian
                                </button>
                            </div>
                        </div>
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
        
        <!-- System Status (Development Only) -->
        <?php if (ENVIRONMENT === 'development'): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🔧 Debug Tools</h5>
            </div>
            <div class="card-body">
                <p class="text-sm text-muted mb-3">Tools untuk debugging (hanya mode development)</p>
                <div class="btn-group-vertical btn-block">
                    <a href="<?= base_url('mahasiswa/debug_kontak/check_system') ?>" class="btn btn-outline-info btn-sm" target="_blank">
                        <i class="ni ni-settings-gear-65"></i> System Check
                    </a>
                    <a href="<?= base_url('mahasiswa/debug_kontak/test_ajax') ?>" class="btn btn-outline-success btn-sm" target="_blank">
                        <i class="ni ni-bullet-list-67"></i> Test AJAX
                    </a>
                    <a href="<?= base_url('mahasiswa/debug_kontak/create_notifikasi') ?>" class="btn btn-outline-warning btn-sm" target="_blank">
                        <i class="ni ni-database"></i> Create Notifikasi Table
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Tips -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">💡 Tips Komunikasi</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled text-sm">
                    <li class="mb-2">
                        <i class="ni ni-check-bold text-success"></i>
                        Gunakan subjek yang jelas dan spesifik
                    </li>
                    <li class="mb-2">
                        <i class="ni ni-check-bold text-success"></i>
                        Tulis pesan dengan bahasa yang sopan
                    </li>
                    <li class="mb-2">
                        <i class="ni ni-check-bold text-success"></i>
                        Sertakan informasi yang diperlukan
                    </li>
                    <li class="mb-2">
                        <i class="ni ni-check-bold text-success"></i>
                        Berikan waktu respons yang wajar
                    </li>
                </ul>
                
                <div class="alert alert-info">
                    <small>
                        <i class="ni ni-notification-70"></i>
                        <strong>Info:</strong> Pesan akan dikirim via email dan tersimpan di sistem
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Contact Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🏫 STK Santo Yakobus</h5>
            </div>
            <div class="card-body">
                <p class="text-sm mb-2">
                    <i class="ni ni-pin-3 text-primary"></i>
                    <strong>Alamat:</strong><br>
                    Jl. Raya Mandala, Merauke, Papua Selatan
                </p>
                <p class="text-sm mb-2">
                    <i class="ni ni-mobile-button text-success"></i>
                    <strong>Telepon:</strong> (0971) 321234
                </p>
                <p class="text-sm mb-0">
                    <i class="ni ni-email-83 text-info"></i>
                    <strong>Email:</strong> info@stkyakobus.ac.id
                </p>
            </div>
        </div>
        
    </div>
</div>

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
// CONFIGURATION - Disesuaikan dengan STK Yakobus
var base_url = '<?= base_url() ?>'; // https://stkyakobus.ac.id/skripsi/
var environment = '<?= ENVIRONMENT ?>';

console.log('🏫 STK Yakobus Kontak Form initialized');
console.log('Base URL:', base_url);
console.log('Environment:', environment);

// Global data storage
let dataKontak = {
    pembimbing: null,
    kaprodi: null,
    staf_list: []
};

let formLoaded = false;

// Template pesan
const templates = {
    konsultasi: {
        subjek: "Konsultasi Proposal Skripsi",
        pesan: "Yth. Bapak/Ibu,\n\nSaya ingin berkonsultasi mengenai proposal skripsi saya. Mohon bantuan untuk:\n\n1. [Jelaskan hal yang ingin dikonsultasikan]\n2. [Tambahkan pertanyaan spesifik]\n\nTerima kasih atas waktu dan bimbingannya.\n\nHormat saya,\n<?= $this->session->userdata('nama') ?>"
    },
    bimbingan: {
        subjek: "Pengaturan Jadwal Bimbingan",
        pesan: "Yth. Bapak/Ibu,\n\nSaya ingin mengatur jadwal bimbingan. Apakah Bapak/Ibu berkenan untuk:\n\nWaktu yang saya usulkan:\n- Hari: [Hari]\n- Tanggal: [Tanggal]\n- Jam: [Jam]\n- Tempat: [Tempat/Online]\n\nTerima kasih.\n\nHormat saya,\n<?= $this->session->userdata('nama') ?>"
    },
    seminar: {
        subjek: "Pendaftaran Seminar Proposal/Skripsi",
        pesan: "Yth. Bapak/Ibu,\n\nSaya bermaksud untuk mendaftar seminar [proposal/skripsi]. Dokumen yang sudah saya siapkan:\n\n- [Daftar dokumen]\n\nMohon bimbingan untuk langkah selanjutnya.\n\nTerima kasih.\n\nHormat saya,\n<?= $this->session->userdata('nama') ?>"
    },
    penelitian: {
        subjek: "Permohonan Surat Izin Penelitian",
        pesan: "Yth. Bapak/Ibu,\n\nSaya bermaksud untuk mengajukan surat izin penelitian dengan detail:\n\nTempat penelitian: [Nama tempat]\nWaktu penelitian: [Periode waktu]\nTujuan: [Jelaskan tujuan penelitian]\n\nMohon bantuan untuk proses selanjutnya.\n\nTerima kasih.\n\nHormat saya,\n<?= $this->session->userdata('nama') ?>"
    }
};

// ROBUST AJAX Function
function makeRequest(endpoint, data = null, timeout = 30000) {
    const url = base_url + endpoint;
    console.log('📡 Making request to:', url);
    
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            type: data ? 'POST' : 'GET',
            data: data,
            dataType: 'json',
            timeout: timeout,
            beforeSend: function(xhr) {
                console.log('📤 Request started:', endpoint);
            },
            success: function(response, textStatus, xhr) {
                console.log('✅ Request success:', endpoint, response);
                resolve(response);
            },
            error: function(xhr, status, error) {
                console.error('❌ Request failed:', {
                    endpoint: endpoint,
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                
                let message = 'Terjadi kesalahan koneksi';
                
                if (xhr.status === 404) {
                    message = 'Endpoint tidak ditemukan';
                } else if (xhr.status === 500) {
                    message = 'Kesalahan server internal';
                } else if (status === 'timeout') {
                    message = 'Koneksi timeout - coba lagi';
                } else if (status === 'parsererror') {
                    message = 'Server mengembalikan data yang tidak valid';
                }
                
                reject({ 
                    message: message, 
                    status: status, 
                    statusCode: xhr.status,
                    details: { xhr, status, error } 
                });
            }
        });
    });
}

// Show alert dengan auto-hide
function showAlert(type, message, autoHide = true) {
    const alertContainer = document.getElementById('alertContainer');
    const alertMessage = document.getElementById('alertMessage');
    const alertDiv = alertContainer.querySelector('.alert');
    
    // Map type to Bootstrap classes
    const typeMap = {
        'error': 'danger',
        'success': 'success',
        'warning': 'warning',
        'info': 'info'
    };
    
    const bootstrapType = typeMap[type] || 'info';
    
    alertMessage.innerHTML = message;
    alertDiv.className = `alert alert-${bootstrapType} alert-dismissible fade show`;
    alertContainer.style.display = 'block';
    
    // Scroll to alert
    alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Auto hide
    if (autoHide) {
        setTimeout(() => {
            alertContainer.style.display = 'none';
        }, 5000);
    }
}

// Document ready
$(document).ready(function() {
    console.log('📚 Document ready - initializing STK Yakobus Kontak Form');
    
    // Load kontak data
    loadKontakData();
    
    // Event listeners
    $('#penerima_role').change(updatePenerima);
    $('#penerima_id').change(updateInfoPenerima);
    $('#pesan').on('input', updateCharCount);
    
    // Form submit
    $('#kontakForm').submit(function(e) {
        e.preventDefault();
        kirimPesan();
    });
    
    updateCharCount();
});

// Load kontak data with comprehensive error handling
function loadKontakData() {
    console.log('🔄 Loading kontak data...');
    
    // Show loading state
    showElement('loadingIndicator');
    hideElement('kontakForm');
    hideElement('errorState');
    
    makeRequest('mahasiswa/kontak/get_kontak_data')
        .then(function(response) {
            if (response.status === 'success') {
                dataKontak = response.data;
                updateDropdownPenerima();
                updateKontakInfo();
                
                // Show form
                hideElement('loadingIndicator');
                showElement('kontakForm');
                formLoaded = true;
                
                showAlert('success', '✅ Data kontak berhasil dimuat', true);
                console.log('✅ Kontak data loaded successfully');
                
            } else {
                throw new Error(response.message || 'Gagal memuat data kontak');
            }
        })
        .catch(function(error) {
            console.error('❌ Load kontak data failed:', error);
            
            hideElement('loadingIndicator');
            showElement('errorState');
            
            showAlert('error', '❌ ' + error.message, false);
            updateKontakInfoError();
        });
}

function updateDropdownPenerima() {
    let options = '<option value="">-- Pilih Penerima --</option>';
    let hasOptions = false;
    
    if (dataKontak.pembimbing) {
        options += '<option value="pembimbing">🎓 Dosen Pembimbing</option>';
        hasOptions = true;
    }
    if (dataKontak.kaprodi) {
        options += '<option value="kaprodi">👑 Kaprodi</option>';
        hasOptions = true;
    }
    if (dataKontak.staf_list && dataKontak.staf_list.length > 0) {
        options += '<option value="staf">👨‍💼 Staf/Admin</option>';
        hasOptions = true;
    }
    
    if (!hasOptions) {
        options += '<option value="" disabled>Tidak ada kontak tersedia</option>';
    }
    
    $('#penerima_role').html(options);
}

function updatePenerima() {
    const role = $('#penerima_role').val();
    const penerimaSelect = $('#penerima_id');
    const detailDiv = $('#detailPenerima');
    const infoDiv = $('#infoPenerima');
    
    // Reset
    penerimaSelect.html('<option value="">-- Pilih --</option>');
    infoDiv.html('');
    
    if (!role) {
        detailDiv.hide();
        return;
    }
    
    detailDiv.show();
    
    if (role === 'pembimbing' && dataKontak.pembimbing) {
        penerimaSelect.html(`<option value="${dataKontak.pembimbing.id}">${dataKontak.pembimbing.nama}</option>`);
        penerimaSelect.val(dataKontak.pembimbing.id);
        updateInfoPenerima();
    } else if (role === 'kaprodi' && dataKontak.kaprodi) {
        penerimaSelect.html(`<option value="${dataKontak.kaprodi.id}">${dataKontak.kaprodi.nama}</option>`);
        penerimaSelect.val(dataKontak.kaprodi.id);
        updateInfoPenerima();
    } else if (role === 'staf' && dataKontak.staf_list.length > 0) {
        dataKontak.staf_list.forEach(staf => {
            penerimaSelect.append(`<option value="${staf.id}">${staf.nama}</option>`);
        });
    }
}

function updateInfoPenerima() {
    const role = $('#penerima_role').val();
    const penerimaId = $('#penerima_id').val();
    const infoDiv = $('#infoPenerima');
    
    let data = null;
    if (role === 'pembimbing' && dataKontak.pembimbing) {
        data = dataKontak.pembimbing;
    } else if (role === 'kaprodi' && dataKontak.kaprodi) {
        data = dataKontak.kaprodi;
    } else if (role === 'staf') {
        data = dataKontak.staf_list.find(s => s.id == penerimaId);
    }
    
    if (data) {
        infoDiv.html(`
            <div class="alert alert-info">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="ni ni-circle-08 fa-2x"></i>
                    </div>
                    <div class="col">
                        <h6 class="mb-0">${data.nama}</h6>
                        <small class="text-muted">${data.email || 'Email tidak tersedia'}</small>
                        ${data.nomor_telepon ? `<br><small class="text-success"><i class="ni ni-mobile-button"></i> ${data.nomor_telepon}</small>` : ''}
                    </div>
                </div>
            </div>
        `);
    }
}

function updateKontakInfo() {
    let html = '';
    
    if (dataKontak.pembimbing) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-primary">
                        <i class="ni ni-single-02"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">Dosen Pembimbing</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.pembimbing.nama}</p>
                    ${dataKontak.pembimbing.nomor_telepon ? `<small class="text-success"><i class="ni ni-mobile-button"></i> ${dataKontak.pembimbing.nomor_telepon}</small>` : ''}
                </div>
            </div>
        `;
    }
    
    if (dataKontak.kaprodi) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-success">
                        <i class="ni ni-hat-3"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">Kaprodi</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.kaprodi.nama}</p>
                    ${dataKontak.kaprodi.nomor_telepon ? `<small class="text-success"><i class="ni ni-mobile-button"></i> ${dataKontak.kaprodi.nomor_telepon}</small>` : ''}
                </div>
            </div>
        `;
    }
    
    if (dataKontak.staf_list && dataKontak.staf_list.length > 0) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-info">
                        <i class="ni ni-settings-gear-65"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">Staf Tersedia</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.staf_list.length} staf/admin</p>
                </div>
            </div>
        `;
    }
    
    if (!html) {
        html = `
            <div class="text-center py-3">
                <i class="ni ni-notification-70 fa-2x text-warning mb-2"></i>
                <p class="text-muted mb-0 text-sm">Belum ada kontak yang tersedia</p>
                <small class="text-muted">Hubungi admin untuk informasi lebih lanjut</small>
            </div>
        `;
    } else {
        html += `
            <div class="alert alert-success">
                <small>
                    <i class="ni ni-check-bold"></i>
                    <strong>Status:</strong> Kontak tersedia dan siap digunakan
                </small>
            </div>
        `;
    }
    
    $('#kontakInfo').html(html);
}

function updateKontakInfoError() {
    $('#kontakInfo').html(`
        <div class="alert alert-warning">
            <small>
                <i class="ni ni-notification-70"></i>
                <strong>Perhatian:</strong> Data kontak tidak dapat dimuat. 
                <a href="#" onclick="loadKontakData()" class="alert-link">Coba lagi</a>
            </small>
        </div>
    `);
}

function useTemplate(type) {
    if (templates[type]) {
        $('#subjek').val(templates[type].subjek);
        $('#pesan').val(templates[type].pesan);
        updateCharCount();
        showAlert('info', `✨ Template "${type}" telah dimuat`, true);
    }
}

function updateCharCount() {
    const pesan = $('#pesan').val();
    const count = pesan.length;
    $('#charCount').text(count);
    
    // Visual feedback
    const countEl = document.getElementById('charCount');
    if (count < 10) {
        countEl.style.color = '#e74c3c';
    } else if (count < 50) {
        countEl.style.color = '#f39c12';
    } else {
        countEl.style.color = '#27ae60';
    }
}

function resetForm() {
    $('#kontakForm')[0].reset();
    $('#detailPenerima').hide();
    $('#infoPenerima').html('');
    $('#alertContainer').hide();
    updateCharCount();
    
    showAlert('info', '🔄 Form telah direset', true);
}

function kirimPesan() {
    console.log('📨 Attempting to send message...');
    
    // Validate form
    const pesan = $('#pesan').val().trim();
    const subjek = $('#subjek').val().trim();
    const penerima_role = $('#penerima_role').val();
    const penerima_id = $('#penerima_id').val();
    
    if (!penerima_role || !penerima_id || !subjek || !pesan) {
        showAlert('warning', '⚠️ Semua field bertanda * wajib diisi');
        return false;
    }
    
    if (pesan.length < 10) {
        showAlert('warning', '⚠️ Pesan minimal 10 karakter');
        $('#pesan').focus();
        return false;
    }
    
    // Disable submit button
    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true);
    submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Mengirim...');
    
    // Get form data
    const formData = {
        penerima_role: penerima_role,
        penerima_id: penerima_id,
        subjek: subjek,
        pesan: pesan,
        prioritas: $('#prioritas').val()
    };
    
    console.log('📤 Sending message data:', formData);
    
    // Send via AJAX
    makeRequest('mahasiswa/kontak/kirim_pesan', formData, 45000)
        .then(function(response) {
            if (response.status === 'success') {
                showAlert('success', '✅ ' + response.message);
                resetForm();
                console.log('✅ Message sent successfully');
            } else if (response.status === 'warning') {
                showAlert('warning', '⚠️ ' + response.message);
                resetForm();
            } else {
                throw new Error(response.message || 'Terjadi kesalahan tidak dikenal');
            }
        })
        .catch(function(error) {
            console.error('❌ Send message failed:', error);
            showAlert('error', '❌ ' + error.message);
        })
        .finally(function() {
            // Re-enable submit button
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
        });
}

// Utility functions
function showElement(id) {
    document.getElementById(id).style.display = 'block';
}

function hideElement(id) {
    document.getElementById(id).style.display = 'none';
}

// Development helpers
if (environment === 'development') {
    console.log('🔧 Development mode active');
    
    // Add global helpers for debugging
    window.debugKontak = {
        dataKontak: dataKontak,
        loadKontakData: loadKontakData,
        makeRequest: makeRequest,
        resetForm: resetForm
    };
}
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>