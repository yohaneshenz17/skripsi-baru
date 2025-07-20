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
                <form id="kontakForm">
                    
                    <!-- Pilih Penerima -->
                    <div class="form-group">
                        <label class="form-control-label" for="penerima_role">
                            <i class="ni ni-circle-08"></i> Kirim Ke
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
                            <i class="ni ni-tag"></i> Subjek
                        </label>
                        <input type="text" class="form-control" id="subjek" name="subjek" 
                               placeholder="Contoh: Konsultasi Proposal Skripsi" maxlength="200" required>
                    </div>
                    
                    <!-- Pesan -->
                    <div class="form-group">
                        <label class="form-control-label" for="pesan">
                            <i class="ni ni-align-left-2"></i> Pesan
                        </label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="6" 
                                  placeholder="Tuliskan pesan Anda dengan jelas dan sopan..." required></textarea>
                        <small class="form-text text-muted">
                            <span id="charCount">0</span> karakter. Min 10 karakter.
                        </small>
                    </div>
                    
                    <!-- Template Pesan Cepat -->
                    <div class="form-group">
                        <label class="form-control-label">Template Pesan Cepat</label>
                        <div class="btn-group-toggle" data-toggle="buttons">
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="useTemplate('konsultasi')">
                                        Konsultasi Proposal
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-info btn-sm btn-block" onclick="useTemplate('bimbingan')">
                                        Jadwal Bimbingan
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-success btn-sm btn-block" onclick="useTemplate('seminar')">
                                        Pendaftaran Seminar
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-warning btn-sm btn-block" onclick="useTemplate('penelitian')">
                                        Izin Penelitian
                                    </button>
                                </div>
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
                <h5 class="mb-0">📞 Kontak Darurat</h5>
            </div>
            <div class="card-body" id="kontakInfo">
                <div class="text-center py-3">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Memuat kontak...</p>
                </div>
            </div>
        </div>
        
        <!-- Riwayat Pesan -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📋 Riwayat Pesan Terbaru</h5>
            </div>
            <div class="card-body" id="riwayatPesan">
                <div class="text-center py-3">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="text-muted mt-2">Memuat riwayat...</p>
                </div>
            </div>
        </div>
        
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
                
                <div class="alert alert-warning">
                    <small>
                        <strong>Catatan:</strong> Pesan akan dikirim ke email dan notifikasi sistem
                    </small>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
// Data untuk JavaScript
let dataKontak = {
    pembimbing: null,
    kaprodi: null,
    staf: []
};

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

$(document).ready(function() {
    // Load data kontak
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

function loadKontakData() {
    call('mahasiswa/kontak/get_kontak_data').done(function(response) {
        if (response.status === 'success') {
            dataKontak = response.data;
            
            // Update dropdown penerima
            let options = '<option value="">-- Pilih Penerima --</option>';
            if (dataKontak.pembimbing) {
                options += '<option value="pembimbing">Dosen Pembimbing</option>';
            }
            if (dataKontak.kaprodi) {
                options += '<option value="kaprodi">Kaprodi</option>';
            }
            if (dataKontak.staf_list && dataKontak.staf_list.length > 0) {
                options += '<option value="staf">Staf/Admin</option>';
            }
            $('#penerima_role').html(options);
            
            // Update kontak info
            updateKontakInfo();
            
            // Update riwayat pesan
            updateRiwayatPesan();
        }
    });
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
                        <small class="text-muted">${data.email}</small>
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
    
    html += `
        <div class="alert alert-info">
            <small>
                <i class="ni ni-bell-55"></i>
                <strong>Tips:</strong> Untuk hal urgent, hubungi langsung via telepon atau WhatsApp
            </small>
        </div>
    `;
    
    $('#kontakInfo').html(html);
}

function updateRiwayatPesan() {
    const riwayat = dataKontak.riwayat_pesan;
    let html = '';
    
    if (riwayat && riwayat.length > 0) {
        riwayat.forEach(pesan => {
            html += `
                <div class="media align-items-center mb-3">
                    <div class="media-object">
                        <div class="avatar rounded-circle bg-${pesan.prioritas == 'urgent' ? 'danger' : (pesan.prioritas == 'high' ? 'warning' : 'info')}">
                            <i class="ni ni-email-83"></i>
                        </div>
                    </div>
                    <div class="media-body ml-3">
                        <h6 class="mb-0 text-sm">${pesan.subjek.substring(0, 30)}${pesan.subjek.length > 30 ? '...' : ''}</h6>
                        <p class="text-xs text-muted mb-0">
                            Ke: ${pesan.nama_penerima}
                        </p>
                        <small class="text-muted">
                            ${formatDate(pesan.created_at)}
                        </small>
                    </div>
                </div>
            `;
        });
    } else {
        html = `
            <div class="text-center py-3">
                <i class="ni ni-email-83 fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0 text-sm">Belum ada pesan terkirim</p>
            </div>
        `;
    }
    
    $('#riwayatPesan').html(html);
}

function useTemplate(type) {
    if (templates[type]) {
        $('#subjek').val(templates[type].subjek);
        $('#pesan').val(templates[type].pesan);
        updateCharCount();
    }
}

function updateCharCount() {
    const pesan = $('#pesan').val();
    $('#charCount').text(pesan.length);
}

function resetForm() {
    $('#kontakForm')[0].reset();
    $('#detailPenerima').hide();
    $('#infoPenerima').html('');
    updateCharCount();
}

function kirimPesan() {
    const pesan = $('#pesan').val();
    if (pesan.length < 10) {
        alert('Pesan minimal 10 karakter');
        return false;
    }
    
    // Disable submit button
    const submitBtn = $('#submitBtn');
    submitBtn.prop('disabled', true);
    submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');
    
    // Get form data
    const formData = {
        penerima_role: $('#penerima_role').val(),
        penerima_id: $('#penerima_id').val(),
        subjek: $('#subjek').val(),
        pesan: $('#pesan').val(),
        prioritas: $('#prioritas').val()
    };
    
    // Send via AJAX
    $.post(base_url + 'mahasiswa/kontak/kirim_pesan', formData)
        .done(function(response) {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.status === 'success') {
                alert('✅ ' + data.message);
                resetForm();
                loadKontakData(); // Refresh riwayat
            } else if (data.status === 'warning') {
                alert('⚠️ ' + data.message);
                resetForm();
            } else {
                alert('❌ ' + data.message);
            }
        })
        .fail(function() {
            alert('❌ Terjadi kesalahan saat mengirim pesan');
        })
        .always(function() {
            // Re-enable submit button
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="ni ni-send"></i> Kirim Pesan');
        });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'short', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>