<?php
/*
=================================================
VIEW: application/views/kaprodi/kontak.php  
FIXED VERSION - Hapus alert "Form telah direset"
=================================================
*/
?>

<!-- Content Start -->
<div class="row">
    <!-- Form Kontak -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">📧 Kirim Pesan</h3>
                <p class="text-sm mb-0 text-muted">
                    Kirim pesan via email ke dosen, staf/admin, atau mahasiswa untuk koordinasi akademik
                </p>
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
                
                <!-- Loading -->
                <div id="loadingIndicator" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Memuat data kontak...</p>
                </div>
                
                <!-- Form -->
                <form id="kontakForm" style="display: none;">
                    
                    <!-- Kirim Ke -->
                    <div class="form-group">
                        <label class="form-control-label">Kirim Ke *</label>
                        <select class="form-control" id="penerima_role" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="dosen">Dosen</option>
                            <option value="staf">Staf/Admin</option>
                            <option value="mahasiswa">Mahasiswa Program Studi</option>
                        </select>
                    </div>
                    
                    <!-- Detail Penerima -->
                    <div class="form-group" id="detailPenerima" style="display: none;">
                        <label class="form-control-label">Pilih Penerima *</label>
                        <select class="form-control" id="penerima_id" required>
                            <option value="">-- Pilih penerima --</option>
                        </select>
                    </div>
                    
                    <!-- Subjek -->
                    <div class="form-group">
                        <label class="form-control-label">Subjek *</label>
                        <input type="text" class="form-control" id="subjek" 
                               placeholder="Masukkan subjek pesan..." required>
                    </div>
                    
                    <!-- Template -->
                    <div class="form-group">
                        <label class="form-control-label">Template Pesan Cepat</label>
                        <div class="row">
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-primary btn-sm btn-block" 
                                        onclick="useTemplate('rapat')">Rapat</button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-success btn-sm btn-block" 
                                        onclick="useTemplate('pengumuman')">Pengumuman</button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-info btn-sm btn-block" 
                                        onclick="useTemplate('koordinasi')">Koordinasi</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pesan -->
                    <div class="form-group">
                        <label class="form-control-label">Pesan *</label>
                        <textarea class="form-control" id="pesan" rows="6" 
                                  placeholder="Tulis pesan Anda..." required></textarea>
                        <small class="text-muted"><span id="charCount">0</span> karakter</small>
                    </div>
                    
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Kirim Pesan</button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-xl-4">
        
        <!-- Kontak Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📞 Kontak Tersedia</h5>
            </div>
            <div class="card-body" id="kontakInfo">
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-muted"></div>
                    <p class="text-muted mt-2 mb-0">Memuat...</p>
                </div>
            </div>
        </div>
        
        <!-- WhatsApp -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">💬 Kontak WhatsApp</h5>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-success btn-sm btn-block mb-2" 
                        onclick="loadWhatsApp('dosen')">WhatsApp Dosen</button>
                <button type="button" class="btn btn-success btn-sm btn-block mb-2" 
                        onclick="loadWhatsApp('staf')">WhatsApp Staf/Admin</button>
                <button type="button" class="btn btn-success btn-sm btn-block" 
                        onclick="loadWhatsApp('mahasiswa')">WhatsApp Mahasiswa</button>
                
                <div id="whatsappList" class="mt-3" style="display: none;"></div>
            </div>
        </div>
        
    </div>
</div>

<!-- JavaScript -->
<script>
var base_url = '<?= base_url() ?>';

let dataKontak = {
    dosen_list: [],
    staf_list: [],
    mahasiswa_list: []
};

// Template pesan
const templates = {
    rapat: {
        subjek: "Undangan Rapat Koordinasi",
        pesan: "Yth. Bapak/Ibu,\n\nKami mengundang untuk menghadiri rapat koordinasi.\n\nHormat kami,\nKaprodi STK Santo Yakobus"
    },
    pengumuman: {
        subjek: "Pengumuman Penting",
        pesan: "Yth. Bapak/Ibu/Saudara/i,\n\nKami sampaikan pengumuman penting.\n\nHormat kami,\nKaprodi STK Santo Yakobus"
    },
    koordinasi: {
        subjek: "Koordinasi Kegiatan",
        pesan: "Yth. Bapak/Ibu,\n\nPerlu koordinasi kegiatan.\n\nHormat kami,\nKaprodi STK Santo Yakobus"
    }
};

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    loadKontakData();
    
    document.getElementById('penerima_role').addEventListener('change', updatePenerima);
    document.getElementById('pesan').addEventListener('input', updateCharCount);
    
    document.getElementById('kontakForm').addEventListener('submit', function(e) {
        e.preventDefault();
        kirimPesan();
    });
    
    updateCharCount();
});

function loadKontakData() {
    fetch(base_url + 'kaprodi/kontak/get_kontak_data')
        .then(response => response.json())
        .then(function(response) {
            if (response.status === 'success') {
                dataKontak = response.data;
                updateKontakInfo();
                
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('kontakForm').style.display = 'block';
            } else {
                showAlert('error', 'Gagal memuat data: ' + response.message);
            }
        })
        .catch(function(error) {
            showAlert('error', 'Gagal memuat data kontak');
        });
}

function updatePenerima() {
    const role = document.getElementById('penerima_role').value;
    const select = document.getElementById('penerima_id');
    const detail = document.getElementById('detailPenerima');
    
    select.innerHTML = '<option value="">-- Pilih penerima --</option>';
    
    if (!role) {
        detail.style.display = 'none';
        return;
    }
    
    let contacts = [];
    if (role === 'dosen') contacts = dataKontak.dosen_list || [];
    if (role === 'staf') contacts = dataKontak.staf_list || [];
    if (role === 'mahasiswa') contacts = dataKontak.mahasiswa_list || [];
    
    contacts.forEach(function(contact) {
        let name = contact.nama;
        if (contact.nim) name += ` (${contact.nim})`;
        select.innerHTML += `<option value="${contact.id}">${name}</option>`;
    });
    
    detail.style.display = 'block';
}

function updateKontakInfo() {
    const dosenCount = (dataKontak.dosen_list || []).length;
    const stafCount = (dataKontak.staf_list || []).length;
    const mahasiswaCount = (dataKontak.mahasiswa_list || []).length;
    
    document.getElementById('kontakInfo').innerHTML = `
        <div class="row text-center">
            <div class="col-4">
                <h4 class="text-primary mb-0">${dosenCount}</h4>
                <p class="text-muted mb-0 text-sm">Dosen</p>
            </div>
            <div class="col-4">
                <h4 class="text-success mb-0">${stafCount}</h4>
                <p class="text-muted mb-0 text-sm">Staf</p>
            </div>
            <div class="col-4">
                <h4 class="text-info mb-0">${mahasiswaCount}</h4>
                <p class="text-muted mb-0 text-sm">Mahasiswa</p>
            </div>
        </div>
    `;
}

function loadWhatsApp(type) {
    const list = document.getElementById('whatsappList');
    list.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div></div>';
    list.style.display = 'block';
    
    fetch(base_url + `kaprodi/kontak/get_whatsapp_contacts/${type}`)
        .then(response => response.json())
        .then(function(response) {
            if (response.status === 'success' && response.data.length > 0) {
                let html = '';
                response.data.forEach(function(contact) {
                    html += `<div class="mb-1">
                        <a href="${contact.url}" target="_blank" class="btn btn-outline-success btn-sm btn-block text-left">
                            <i class="fab fa-whatsapp mr-1"></i> ${contact.name}
                        </a>
                    </div>`;
                });
                list.innerHTML = html;
            } else {
                list.innerHTML = '<p class="text-muted text-sm mb-0">Tidak ada kontak</p>';
            }
        })
        .catch(function() {
            list.innerHTML = '<p class="text-muted text-sm mb-0">Gagal memuat</p>';
        });
}

function useTemplate(type) {
    if (templates[type]) {
        document.getElementById('subjek').value = templates[type].subjek;
        document.getElementById('pesan').value = templates[type].pesan;
        updateCharCount();
        showAlert('info', `Template "${type}" dimuat`);
    }
}

function updateCharCount() {
    const count = document.getElementById('pesan').value.length;
    document.getElementById('charCount').textContent = count;
}

// FIXED: resetForm tanpa alert "Form telah direset"
function resetForm() {
    document.getElementById('kontakForm').reset();
    document.getElementById('detailPenerima').style.display = 'none';
    
    const whatsappList = document.getElementById('whatsappList');
    if (whatsappList) {
        whatsappList.style.display = 'none';
    }
    
    updateCharCount();
    // HAPUS: showAlert('info', 'Form telah direset'); 
    // Biarkan hanya success alert yang muncul
}

function kirimPesan() {
    const role = document.getElementById('penerima_role').value;
    const id = document.getElementById('penerima_id').value;
    const subjek = document.getElementById('subjek').value.trim();
    const pesan = document.getElementById('pesan').value.trim();
    
    if (!role || !id || !subjek || !pesan) {
        showAlert('warning', 'Semua field wajib diisi');
        return;
    }
    
    if (pesan.length < 10) {
        showAlert('warning', 'Pesan minimal 10 karakter');
        return;
    }
    
    const btn = document.getElementById('submitBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Mengirim...';
    
    const formData = new FormData();
    formData.append('penerima_role', role);
    formData.append('penerima_id', id);
    formData.append('subjek', subjek);
    formData.append('pesan', pesan);
    formData.append('prioritas', 'normal');
    
    fetch(base_url + 'kaprodi/kontak/kirim_pesan', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(function(response) {
        if (response.status === 'success') {
            showAlert('success', response.message);
            resetForm(); // Akan reset form tanpa alert tambahan
        } else {
            showAlert('error', response.message || 'Gagal mengirim');
        }
    })
    .catch(function() {
        showAlert('error', 'Terjadi kesalahan');
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function showAlert(type, message) {
    const container = document.getElementById('alertContainer');
    const alert = container.querySelector('.alert');
    const messageEl = container.querySelector('#alertMessage');
    
    const typeMap = { 'error': 'danger', 'success': 'success', 'warning': 'warning', 'info': 'info' };
    const bootstrapType = typeMap[type] || 'info';
    
    messageEl.innerHTML = message;
    alert.className = `alert alert-${bootstrapType} alert-dismissible fade show`;
    container.style.display = 'block';
    
    setTimeout(() => container.style.display = 'none', 5000);
}

</script>