<?php $this->app->extend('template/dosen') ?>
<?php $this->app->setVar('title', 'Kontak Form - Dosen') ?>
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
                            Kirim pesan via email ke mahasiswa bimbingan, kaprodi, atau staf untuk konsultasi dan koordinasi
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
                        <label class="form-control-label" for="penerima_kategori">
                            <i class="ni ni-circle-08"></i> Kirim Ke *
                        </label>
                        <select class="form-control" id="penerima_kategori" name="penerima_kategori" required>
                            <option value="">-- Pilih Kategori Penerima --</option>
                            <option value="mahasiswa_bimbingan">🎓 Mahasiswa Bimbingan</option>
                            <option value="semua_mahasiswa">👥 Semua Mahasiswa</option>
                            <option value="kaprodi">👑 Kaprodi</option>
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
                               placeholder="Contoh: Penjadwalan Bimbingan Skripsi" maxlength="200" required>
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
                                <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="useTemplate('jadwal_bimbingan')">
                                    <i class="ni ni-calendar-grid-58"></i> Jadwal Bimbingan
                                </button>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-info btn-sm btn-block" onclick="useTemplate('revisi_proposal')">
                                    <i class="ni ni-bulb-61"></i> Revisi Proposal
                                </button>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-success btn-sm btn-block" onclick="useTemplate('persetujuan_seminar')">
                                    <i class="ni ni-hat-3"></i> Persetujuan Seminar
                                </button>
                            </div>
                            <div class="col-lg-6 col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-warning btn-sm btn-block" onclick="useTemplate('koordinasi_kaprodi')">
                                    <i class="ni ni-settings"></i> Koordinasi Kaprodi
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
        
        <!-- WhatsApp Mahasiswa Bimbingan -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">💬 WhatsApp Mahasiswa</h5>
            </div>
            <div class="card-body" id="whatsappInfo">
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-success" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Mengecek mahasiswa bimbingan...</p>
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
                        Tulis pesan dengan bahasa yang profesional
                    </li>
                    <li class="mb-2">
                        <i class="ni ni-check-bold text-success"></i>
                        Sertakan instruksi yang jelas untuk mahasiswa
                    </li>
                    <li class="mb-2">
                        <i class="ni ni-check-bold text-success"></i>
                        Berikan deadline yang realistic
                    </li>
                    <li class="mb-2">
                        <i class="ni ni-check-bold text-success"></i>
                        Follow up jika diperlukan
                    </li>
                </ul>
                
                <div class="alert alert-info">
                    <small>
                        <i class="ni ni-email-83"></i>
                        <strong>Info:</strong> Email dikirim atas nama Anda sebagai dosen. 
                        Balasan akan dikirim ke email pribadi Anda.
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
// CONFIGURATION - Disesuaikan dengan STK Yakobus Dosen
var base_url = '<?= base_url() ?>'; // https://stkyakobus.ac.id/skripsi/
var environment = '<?= ENVIRONMENT ?>';

console.log('🎓 STK Yakobus Dosen Kontak Form initialized');
console.log('Base URL:', base_url);

// Global data storage - STRUKTUR UNTUK DOSEN
let dataKontak = {
    mahasiswa_bimbingan: [],
    semua_mahasiswa: [],
    kaprodi_list: [],
    staf_list: []
};

let formLoaded = false;

// Template pesan untuk dosen
const templates = {
    jadwal_bimbingan: {
        subjek: "Penjadwalan Bimbingan Skripsi",
        pesan: "Yth. Saudara/i,\n\nSaya mengundang Anda untuk melakukan bimbingan skripsi dengan jadwal sebagai berikut:\n\n📅 Hari/Tanggal: [Isi hari dan tanggal]\n🕒 Waktu: [Isi waktu]\n📍 Tempat: [Ruang dosen/Online]\n\nMohon konfirmasi kehadiran dan bawa dokumen:\n- Draft proposal/skripsi terbaru\n- Catatan pertanyaan yang ingin didiskusikan\n\nTerima kasih.\n\nHormat saya,\n<?= $this->session->userdata('nama') ?>"
    },
    revisi_proposal: {
        subjek: "Revisi Proposal Skripsi",
        pesan: "Yth. Saudara/i,\n\nSetelah saya review proposal Anda, berikut beberapa hal yang perlu diperbaiki:\n\n1. [Jelaskan revisi yang diperlukan]\n2. [Tambahkan poin revisi lainnya]\n\nMohon lakukan revisi sesuai catatan di atas dan kirimkan kembali paling lambat [tanggal deadline].\n\nJika ada pertanyaan, silakan hubungi saya.\n\nHormat saya,\n<?= $this->session->userdata('nama') ?>"
    },
    persetujuan_seminar: {
        subjek: "Persetujuan Seminar Proposal/Skripsi",
        pesan: "Yth. Saudara/i,\n\nSetelah meninjau kemajuan penelitian Anda, saya menyetujui Anda untuk mengajukan seminar [proposal/skripsi].\n\nPersyaratan yang sudah dipenuhi:\n✓ [Daftar persyaratan yang sudah terpenuhi]\n\nLangkah selanjutnya:\n1. Mengajukan seminar melalui sistem\n2. Melengkapi berkas administrasi\n3. Koordinasi dengan staf untuk penjadwalan\n\nSelamat dan semoga sukses!\n\nHormat saya,\n<?= $this->session->userdata('nama') ?>"
    },
    koordinasi_kaprodi: {
        subjek: "Koordinasi Akademik",
        pesan: "Yth. Bapak/Ibu Kaprodi,\n\nSaya ingin melakukan koordinasi terkait:\n\n[Jelaskan hal yang ingin dikoordinasikan, misalnya:]\n- Jadwal seminar mahasiswa bimbingan\n- Penetapan dosen penguji\n- Kebijakan akademik terbaru\n- Evaluasi mahasiswa\n\nMohon waktu untuk diskusi lebih lanjut.\n\nTerima kasih.\n\nHormat saya,\n<?= $this->session->userdata('nama') ?>"
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

// Document ready
$(document).ready(function() {
    console.log('📚 Document ready - initializing Dosen Kontak Form');
    
    // Load kontak data
    loadKontakData();
    
    // Load WhatsApp info
    loadWhatsAppInfo();
    
    // Event listeners
    $('#penerima_kategori').change(updatePenerima);
    $('#penerima_id').change(updateInfoPenerima);
    $('#pesan').on('input', updateCharCount);
    
    // Form submit
    $('#kontakForm').submit(function(e) {
        e.preventDefault();
        kirimPesan();
    });
    
    updateCharCount();
});

// Load WhatsApp info untuk mahasiswa bimbingan
function loadWhatsAppInfo() {
    console.log('🔄 Loading WhatsApp mahasiswa bimbingan...');
    
    makeRequest('dosen/kontak/get_mahasiswa_bimbingan')
        .then(function(response) {
            console.log('🔍 WhatsApp Response:', response);
            
            if (response.status === 'success' && response.data.mahasiswa_bimbingan && response.data.mahasiswa_bimbingan.length > 0) {
                updateWhatsAppInfo(response.data.mahasiswa_bimbingan);
            } else {
                updateWhatsAppInfoEmpty(response.message || 'Belum ada mahasiswa bimbingan');
            }
        })
        .catch(function(error) {
            console.error('❌ Load WhatsApp info failed:', error);
            updateWhatsAppInfoError();
        });
}

// Update WhatsApp info dengan data mahasiswa bimbingan
function updateWhatsAppInfo(mahasiswaList) {
    let html = '';
    
    if (mahasiswaList.length > 0) {
        html += `
            <div class="alert alert-success mb-3">
                <small>
                    <i class="ni ni-check-bold"></i>
                    <strong>${mahasiswaList.length} mahasiswa bimbingan</strong> tersedia untuk kontak WhatsApp
                </small>
            </div>
        `;
        
        // List mahasiswa dengan WhatsApp
        mahasiswaList.forEach(function(mahasiswa, index) {
            if (mahasiswa.nomor_telepon) {
                // Format nomor telepon untuk WhatsApp
                let phoneNumber = mahasiswa.nomor_telepon.replace(/\D/g, '');
                if (phoneNumber.startsWith('0')) {
                    phoneNumber = '62' + phoneNumber.substring(1);
                } else if (!phoneNumber.startsWith('62')) {
                    phoneNumber = '62' + phoneNumber;
                }
                
                const whatsappUrl = `https://wa.me/${phoneNumber}?text=Salam%20${encodeURIComponent(mahasiswa.nama)},%20saya%20${encodeURIComponent('<?= $this->session->userdata('nama') ?>')}%20dosen%20pembimbing%20Anda.%20`;
                
                html += `
                    <div class="media align-items-center mb-3 p-2 border rounded">
                        <div class="media-object">
                            <div class="avatar rounded-circle bg-success">
                                <i class="ni ni-single-02"></i>
                            </div>
                        </div>
                        <div class="media-body ml-3">
                            <h6 class="mb-0">${mahasiswa.nama}</h6>
                            <p class="text-sm text-muted mb-1">NIM: ${mahasiswa.nim}</p>
                            <small class="text-success">📱 ${mahasiswa.nomor_telepon}</small>
                        </div>
                        <div class="media-right">
                            <a href="${whatsappUrl}" target="_blank" class="btn btn-success btn-sm">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="media align-items-center mb-3 p-2 border rounded">
                        <div class="media-object">
                            <div class="avatar rounded-circle bg-warning">
                                <i class="ni ni-single-02"></i>
                            </div>
                        </div>
                        <div class="media-body ml-3">
                            <h6 class="mb-0">${mahasiswa.nama}</h6>
                            <p class="text-sm text-muted mb-1">NIM: ${mahasiswa.nim}</p>
                            <small class="text-warning">📱 Nomor tidak tersedia</small>
                        </div>
                        <div class="media-right">
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="ni ni-mobile-button"></i>
                            </button>
                        </div>
                    </div>
                `;
            }
        });
        
    } else {
        html = updateWhatsAppInfoEmpty('Belum ada mahasiswa bimbingan');
    }
    
    $('#whatsappInfo').html(html);
}

// Update WhatsApp info untuk kasus belum ada mahasiswa bimbingan
function updateWhatsAppInfoEmpty(message) {
    return `
        <div class="text-center py-3">
            <i class="ni ni-single-02 fa-2x text-muted mb-3"></i>
            <h6 class="text-muted">Belum Ada Mahasiswa Bimbingan</h6>
            <p class="text-sm text-muted mb-3">${message}</p>
            <small class="text-info">
                <i class="ni ni-bulb-61"></i>
                Mahasiswa akan muncul setelah proposal mereka disetujui dan Anda ditugaskan sebagai pembimbing
            </small>
        </div>
    `;
}

// Update WhatsApp info untuk error
function updateWhatsAppInfoError() {
    $('#whatsappInfo').html(`
        <div class="alert alert-warning">
            <small>
                <i class="ni ni-notification-70"></i>
                <strong>Perhatian:</strong> Tidak dapat memuat info mahasiswa bimbingan. 
                <a href="#" onclick="loadWhatsAppInfo()" class="alert-link">Coba lagi</a>
            </small>
        </div>
    `);
}

// Load kontak data untuk dosen
function loadKontakData() {
    console.log('🔄 Loading kontak data...');
    
    // Show loading state
    showElement('loadingIndicator');
    hideElement('kontakForm');
    hideElement('errorState');
    
    makeRequest('dosen/kontak/get_kontak_data')
        .then(function(response) {
            if (response.status === 'success') {
                dataKontak = response.data;
                updateKontakInfo();
                
                // Show form
                hideElement('loadingIndicator');
                showElement('kontakForm');
                formLoaded = true;
                
                showAlert('success', `✅ Data kontak berhasil dimuat: ${response.debug.mahasiswa_bimbingan_count} mahasiswa bimbingan, ${response.debug.semua_mahasiswa_count} total mahasiswa, ${response.debug.kaprodi_count} kaprodi, ${response.debug.staf_count} staf`, true);
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

// Update dropdown penerima berdasarkan kategori yang dipilih
function updatePenerima() {
    const kategori = $('#penerima_kategori').val();
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
    
    detailDiv.show();
    
    let listData = [];
    let emptyMessage = 'Tidak ada data tersedia';
    
    if (kategori === 'mahasiswa_bimbingan') {
        listData = dataKontak.mahasiswa_bimbingan || [];
        emptyMessage = 'Belum ada mahasiswa bimbingan';
    } else if (kategori === 'semua_mahasiswa') {
        listData = dataKontak.semua_mahasiswa || [];
        emptyMessage = 'Tidak ada mahasiswa tersedia';
    } else if (kategori === 'kaprodi') {
        listData = dataKontak.kaprodi_list || [];
        emptyMessage = 'Tidak ada kaprodi tersedia';
    } else if (kategori === 'staf') {
        listData = dataKontak.staf_list || [];
        emptyMessage = 'Tidak ada staf tersedia';
    }
    
    if (listData.length > 0) {
        listData.forEach(item => {
            let displayName = item.nama;
            if (item.nim) {
                displayName += ` (${item.nim})`;
            } else if (item.nama_prodi) {
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
    const kategori = $('#penerima_kategori').val();
    const penerimaId = $('#penerima_id').val();
    const infoDiv = $('#infoPenerima');
    
    if (!penerimaId || !kategori) {
        infoDiv.html('');
        return;
    }
    
    let data = null;
    
    if (kategori === 'mahasiswa_bimbingan') {
        data = dataKontak.mahasiswa_bimbingan.find(item => item.id == penerimaId);
    } else if (kategori === 'semua_mahasiswa') {
        data = dataKontak.semua_mahasiswa.find(item => item.id == penerimaId);
    } else if (kategori === 'kaprodi') {
        data = dataKontak.kaprodi_list.find(item => item.id == penerimaId);
    } else if (kategori === 'staf') {
        data = dataKontak.staf_list.find(item => item.id == penerimaId);
    }
    
    if (data) {
        let roleIcon = '👤';
        let roleName = 'Kontak';
        let extraInfo = '';
        
        if (kategori === 'mahasiswa_bimbingan' || kategori === 'semua_mahasiswa') {
            roleIcon = '🎓';
            roleName = 'Mahasiswa';
            extraInfo = data.nim ? `<br><small class="text-primary">NIM: ${data.nim}</small>` : '';
            if (data.judul_proposal) {
                extraInfo += `<br><small class="text-success">Judul: ${data.judul_proposal}</small>`;
            }
        } else if (kategori === 'kaprodi') {
            roleIcon = '👑';
            roleName = 'Kaprodi';
        } else if (kategori === 'staf') {
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
                        ${extraInfo}
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
    
    // Mahasiswa Bimbingan
    if (dataKontak.mahasiswa_bimbingan && dataKontak.mahasiswa_bimbingan.length > 0) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-primary">
                        <i class="ni ni-hat-3"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">🎓 Mahasiswa Bimbingan</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.mahasiswa_bimbingan.length} mahasiswa bimbingan</p>
                    <small class="text-success">• ${dataKontak.mahasiswa_bimbingan.slice(0, 2).map(m => m.nama.split(' ')[0]).join(', ')}${dataKontak.mahasiswa_bimbingan.length > 2 ? '...' : ''}</small>
                </div>
            </div>
        `;
    }
    
    // Semua Mahasiswa
    if (dataKontak.semua_mahasiswa && dataKontak.semua_mahasiswa.length > 0) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-info">
                        <i class="ni ni-single-02"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">👥 Semua Mahasiswa</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.semua_mahasiswa.length} mahasiswa tersedia</p>
                </div>
            </div>
        `;
    }
    
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
                    <small class="text-success">• ${dataKontak.kaprodi_list.map(k => k.nama.split(' ')[0]).join(', ')}</small>
                </div>
            </div>
        `;
    }
    
    // Staf
    if (dataKontak.staf_list && dataKontak.staf_list.length > 0) {
        html += `
            <div class="media align-items-center mb-3">
                <div class="media-object">
                    <div class="avatar rounded-circle bg-secondary">
                        <i class="ni ni-settings-gear-65"></i>
                    </div>
                </div>
                <div class="media-body ml-3">
                    <h6 class="mb-0">👨‍💼 Staf/Admin</h6>
                    <p class="text-sm text-muted mb-0">${dataKontak.staf_list.length} staf tersedia</p>
                    <small class="text-success">• ${dataKontak.staf_list.map(s => s.nama.split(' ')[0]).join(', ')}</small>
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
        const totalBimbingan = dataKontak.mahasiswa_bimbingan?.length || 0;
        const totalMahasiswa = dataKontak.semua_mahasiswa?.length || 0;
        const totalKaprodi = dataKontak.kaprodi_list?.length || 0;
        const totalStaf = dataKontak.staf_list?.length || 0;
        
        html += `
            <div class="alert alert-success">
                <small>
                    <i class="ni ni-check-bold"></i>
                    <strong>Kontak tersedia:</strong> ${totalBimbingan} mahasiswa bimbingan, ${totalMahasiswa} total mahasiswa, ${totalKaprodi} kaprodi, ${totalStaf} staf
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

function kirimPesan() {
    console.log('📨 Attempting to send email...');
    
    // Validate form
    const pesan = $('#pesan').val().trim();
    const subjek = $('#subjek').val().trim();
    const penerima_kategori = $('#penerima_kategori').val();
    const penerima_id = $('#penerima_id').val();
    
    if (!penerima_kategori || !penerima_id || !subjek || !pesan) {
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
        penerima_kategori: penerima_kategori,
        penerima_id: penerima_id,
        subjek: subjek,
        pesan: pesan,
        prioritas: $('#prioritas').val()
    };
    
    console.log('📤 Sending email data:', formData);
    
    // Send via AJAX
    makeRequest('dosen/kontak/kirim_pesan', formData, 60000) // 1 minute timeout untuk email
        .then(function(response) {
            if (response.status === 'success') {
                showAlert('success', '✅ ' + response.message, false);
                resetFormSilent();
                console.log('✅ Email sent successfully');
                
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

// Function reset form tanpa notifikasi
function resetFormSilent() {
    $('#kontakForm')[0].reset();
    $('#detailPenerima').hide();
    $('#infoPenerima').html('');
    updateCharCount();
    console.log('Form direset tanpa notifikasi');
}

// Function resetForm dengan alert untuk tombol Reset
function resetForm() {
    resetFormSilent();
    showAlert('info', '🔄 Form telah direset', true);
}

// Show alert dengan auto-hide yang robust
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