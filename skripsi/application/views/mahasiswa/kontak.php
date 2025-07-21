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
                            Kirim pesan via email ke kaprodi, dosen, atau staf untuk konsultasi dan bantuan
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
                            <option value="kaprodi">👑 Kaprodi</option>
                            <option value="dosen">🎓 Dosen</option>
                            <option value="staf">👨‍💼 Staf/Admin</option>
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
                            <i class="ni ni-send"></i> Kirim Email
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
        
        <!-- Tips -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">💡 Tips Komunikasi Email</h5>
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
                        Berikan waktu respons yang wajar (1-2 hari)
                    </li>
                    <li class="mb-2">
                        <i class="ni ni-check-bold text-success"></i>
                        Periksa email Anda untuk balasan
                    </li>
                </ul>
                
                <div class="alert alert-info">
                    <small>
                        <i class="ni ni-email-83"></i>
                        <strong>Info:</strong> Pesan akan dikirim langsung ke email penerima. 
                        Balasan akan dikirim ke email Anda.
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
                    Jl. Missi 2, Mandala, Merauke, Papua Selatan
                </p>
                <p class="text-sm mb-2">
                    <i class="ni ni-mobile-button text-success"></i>
                    <strong>Telepon:</strong> (0971) 333-0264
                </p>
                <p class="text-sm mb-0">
                    <i class="ni ni-email-83 text-info"></i>
                    <strong>Email:</strong> sipd@stkyakobus.ac.id
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

// Global data storage - STRUKTUR BARU
let dataKontak = {
    kaprodi_list: [],
    dosen_list: [],
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

// Load kontak data - UPDATED untuk struktur baru
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
                updateKontakInfo();
                
                // Show form
                hideElement('loadingIndicator');
                showElement('kontakForm');
                formLoaded = true;
                
                showAlert('success', `✅ Data kontak berhasil dimuat: ${response.debug.kaprodi_count} kaprodi, ${response.debug.dosen_count} dosen, ${response.debug.staf_count} staf`, true);
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

// Update dropdown penerima berdasarkan role yang dipilih
function updatePenerima() {
    const role = $('#penerima_role').val();
    const penerimaSelect = $('#penerima_id');
    const detailDiv = $('#detailPenerima');
    const infoDiv = $('#infoPenerima');
    
    // Reset
    penerimaSelect.html('<option value="">-- Pilih Nama Penerima --</option>');
    infoDiv.html('');
    
    if (!role) {
        detailDiv.hide();
        return;
    }
    
    detailDiv.show();
    
    let listData = [];
    let emptyMessage = 'Tidak ada data tersedia';
    
    if (role === 'kaprodi') {
        listData = dataKontak.kaprodi_list || [];
        emptyMessage = 'Tidak ada kaprodi tersedia';
    } else if (role === 'dosen') {
        listData = dataKontak.dosen_list || [];
        emptyMessage = 'Tidak ada dosen tersedia';
    } else if (role === 'staf') {
        listData = dataKontak.staf_list || [];
        emptyMessage = 'Tidak ada staf tersedia';
    }
    
    if (listData.length > 0) {
        listData.forEach(item => {
            let displayName = item.nama;
            if (item.nama_prodi) {
                displayName += ` (${item.nama_prodi})`;
            }
            penerimaSelect.append(`<option value="${item.id}">${displayName}</option>`);
        });
    } else {
        penerimaSelect.append(`<option value="" disabled>${emptyMessage}</option>`);
    }
}

// Update info penerima berdasarkan pilihan
function updateInfoPenerima() {
    const role = $('#penerima_role').val();
    const penerimaId = $('#penerima_id').val();
    const infoDiv = $('#infoPenerima');
    
    if (!penerimaId || !role) {
        infoDiv.html('');
        return;
    }
    
    let data = null;
    
    if (role === 'kaprodi') {
        data = dataKontak.kaprodi_list.find(item => item.id == penerimaId);
    } else if (role === 'dosen') {
        data = dataKontak.dosen_list.find(item => item.id == penerimaId);
    } else if (role === 'staf') {
        data = dataKontak.staf_list.find(item => item.id == penerimaId);
    }
    
    if (data) {
        let roleIcon = '👤';
        let roleName = 'Kontak';
        
        if (role === 'kaprodi') {
            roleIcon = '👑';
            roleName = 'Kaprodi';
        } else if (role === 'dosen') {
            roleIcon = '🎓';
            roleName = 'Dosen';
        } else if (role === 'staf') {
            roleIcon = '👨‍💼';
            roleName = 'Staf/Admin';
        }
        
        infoDiv.html(`
            <div class="alert alert-info">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="ni ni-circle-08 fa-2x"></i>
                    </div>
                    <div class="col">
                        <h6 class="mb-0">${roleIcon} ${data.nama}</h6>
                        <small class="text-muted">${roleName}</small>
                        ${data.nama_prodi ? `<br><small class="text-primary">${data.nama_prodi}</small>` : ''}
                        <br><small class="text-success"><i class="ni ni-email-83"></i> ${data.email}</small>
                        ${data.nomor_telepon ? `<br><small class="text-info"><i class="ni ni-mobile-button"></i> ${data.nomor_telepon}</small>` : ''}
                    </div>
                </div>
            </div>
        `);
    }
}

// Update info kontak di sidebar
function updateKontakInfo() {
    let html = '';
    
    // Kaprodi
    if (dataKontak.kaprodi_list && dataKontak.kaprodi_list.length > 0) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-warning">
                        <i class="ni ni-hat-3"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">👑 Kaprodi</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.kaprodi_list.length} kaprodi tersedia</p>
                    <small class="text-success">• ${dataKontak.kaprodi_list.map(k => k.nama.split('(')[0].trim()).join(', ')}</small>
                </div>
            </div>
        `;
    }
    
    // Dosen
    if (dataKontak.dosen_list && dataKontak.dosen_list.length > 0) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-primary">
                        <i class="ni ni-single-02"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">🎓 Dosen</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.dosen_list.length} dosen tersedia</p>
                </div>
            </div>
        `;
    }
    
    // Staf
    if (dataKontak.staf_list && dataKontak.staf_list.length > 0) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-info">
                        <i class="ni ni-settings-gear-65"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">👨‍💼 Staf/Admin</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.staf_list.length} staf tersedia</p>
                    <small class="text-success">• ${dataKontak.staf_list.map(s => s.nama).join(', ')}</small>
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
        const totalKontak = (dataKontak.kaprodi_list?.length || 0) + (dataKontak.dosen_list?.length || 0) + (dataKontak.staf_list?.length || 0);
        html += `
            <div class="alert alert-success">
                <small>
                    <i class="ni ni-check-bold"></i>
                    <strong>Total ${totalKontak} kontak</strong> tersedia dan siap menerima email
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

// PERBAIKAN untuk function kirimPesan() dan resetForm()

function kirimPesan() {
    console.log('📨 Attempting to send email...');
    
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
    submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Mengirim Email...');
    
    // Get form data
    const formData = {
        penerima_role: penerima_role,
        penerima_id: penerima_id,
        subjek: subjek,
        pesan: pesan,
        prioritas: $('#prioritas').val()
    };
    
    console.log('📤 Sending email data:', formData);
    
    // Send via AJAX
    makeRequest('mahasiswa/kontak/kirim_pesan', formData, 60000) // 1 minute timeout untuk email
        .then(function(response) {
            if (response.status === 'success') {
                // PERBAIKAN: Tampilkan pesan sukses terlebih dahulu
                showAlert('success', '✅ ' + response.message, false); // false = tidak auto hide
                
                // Reset form TANPA showAlert
                resetFormSilent();
                
                console.log('✅ Email sent successfully');
                
                // Auto hide success message setelah 8 detik
                setTimeout(function() {
                    $('#alertContainer').fadeOut();
                }, 8000);
                
            } else if (response.status === 'warning') {
                showAlert('warning', '⚠️ ' + response.message);
            } else {
                throw new Error(response.message || 'Terjadi kesalahan tidak dikenal');
            }
        })
        .catch(function(error) {
            console.error('❌ Send email failed:', error);
            showAlert('error', '❌ ' + error.message);
        })
        .finally(function() {
            // Re-enable submit button
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
        });
}

// PERBAIKAN: Function reset form tanpa notifikasi
function resetFormSilent() {
    $('#kontakForm')[0].reset();
    $('#detailPenerima').hide();
    $('#infoPenerima').html('');
    updateCharCount();
    
    console.log('Form direset tanpa notifikasi');
}

// Function resetForm yang lama (dengan alert) untuk tombol Reset
function resetForm() {
    resetFormSilent();
    showAlert('info', '🔄 Form telah direset', true);
}

// PERBAIKAN: Function showAlert yang lebih robust
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
    
    // Clear any existing timeouts
    if (window.alertTimeout) {
        clearTimeout(window.alertTimeout);
    }
    
    alertMessage.innerHTML = message;
    alertDiv.className = `alert alert-${bootstrapType} alert-dismissible fade show`;
    alertContainer.style.display = 'block';
    
    // Scroll to alert
    alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Auto hide with different timing based on type
    if (autoHide) {
        let hideDelay = 5000; // Default 5 seconds
        
        if (type === 'success') {
            hideDelay = 8000; // Success message 8 seconds
        } else if (type === 'error') {
            hideDelay = 10000; // Error message 10 seconds
        }
        
        window.alertTimeout = setTimeout(() => {
            alertContainer.style.display = 'none';
        }, hideDelay);
    }
}

// Utility functions
function showElement(id) {
    document.getElementById(id).style.display = 'block';
}

function hideElement(id) {
    document.getElementById(id).style.display = 'none';
}
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>