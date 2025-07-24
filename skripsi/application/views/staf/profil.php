<?php
$this->load->view('template/staf', [
    'title' => 'Profil Staf Akademik',
    'content' => ob_start(),
    'css' => '
        <style>
            .profile-card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            }
            .profile-header {
                background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%);
                border-radius: 15px 15px 0 0;
            }
            .avatar-upload {
                position: relative;
                display: inline-block;
            }
            .avatar-upload .avatar-edit {
                position: absolute;
                right: 12px;
                z-index: 1;
                top: 10px;
            }
            .avatar-upload .avatar-edit input {
                display: none;
            }
            .avatar-upload .avatar-edit label {
                display: inline-block;
                width: 34px;
                height: 34px;
                margin-bottom: 0;
                border-radius: 100%;
                background: #FFFFFF;
                border: 1px solid transparent;
                box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);
                cursor: pointer;
                font-weight: normal;
                transition: all 0.2s ease-in-out;
            }
            .avatar-upload .avatar-edit label:hover {
                background: #f1f1f1;
                border-color: #d6d6d6;
            }
            .avatar-upload .avatar-edit label:after {
                content: "\\f040";
                font-family: "Font Awesome 5 Free";
                color: #757575;
                position: absolute;
                top: 10px;
                left: 0;
                right: 0;
                text-align: center;
                margin: auto;
            }
            .avatar-upload .avatar-preview {
                width: 150px;
                height: 150px;
                position: relative;
                border-radius: 100%;
                border: 6px solid #F8F8F8;
                box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.1);
            }
            .avatar-upload .avatar-preview > div {
                width: 100%;
                height: 100%;
                border-radius: 100%;
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center;
            }
        </style>
    ',
    'script' => ''
]);
?>

<!-- Content -->
<div class="row">
    <!-- Profile Info Card -->
    <div class="col-xl-4">
        <div class="card profile-card">
            <div class="profile-header text-white text-center py-5">
                <div class="avatar-upload">
                    <div class="avatar-edit">
                        <input type="file" id="imageUpload" accept=".png, .jpg, .jpeg" />
                        <label for="imageUpload"></label>
                    </div>
                    <div class="avatar-preview">
                        <div id="imagePreview" style="background-image: url('<?= $staf->foto ? base_url('uploads/staf/' . $staf->foto) : base_url('assets/img/theme/default-avatar.png') ?>');"></div>
                    </div>
                </div>
                <h3 class="text-white mt-4 mb-1"><?= $staf->nama ?></h3>
                <p class="text-white-50 mb-0">Staf Akademik</p>
                <p class="text-white-50 mb-4">STK Santo Yakobus Merauke</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="card-profile-stats d-flex justify-content-center">
                            <div class="text-center mr-4">
                                <span class="heading">ID</span>
                                <span class="description"><?= $staf->id ?></span>
                            </div>
                            <div class="text-center mr-4">
                                <span class="heading">NIP</span>
                                <span class="description"><?= $staf->nip ?></span>
                            </div>
                            <div class="text-center">
                                <span class="heading">Level</span>
                                <span class="description">Staf</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <h5>Informasi Kontak</h5>
                    <div class="h6 font-weight-300">
                        <i class="ni ni-email-83 mr-2"></i><?= $staf->email ?>
                    </div>
                    <div class="h6 mt-2">
                        <i class="ni ni-mobile-button mr-2"></i><?= $staf->nomor_telepon ?>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <div class="h6 text-muted">
                        <i class="ni ni-building mr-2"></i>Program Studi: 
                        <?php if($staf->prodi_id): ?>
                            <?php
                            $prodi = $this->db->get_where('prodi', ['id' => $staf->prodi_id])->row();
                            echo $prodi ? $prodi->nama : 'Tidak Terdefinisi';
                            ?>
                        <?php else: ?>
                            Semua Program Studi
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="h3 mb-0">📊 Statistik Aktivitas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <span class="h2 font-weight-bold d-block">
                            <?php
                            $this->db->where('staf_validator_id', $staf->id);
                            echo $this->db->count_all_results('proposal_mahasiswa');
                            ?>
                        </span>
                        <span class="text-sm text-muted">Validasi Publikasi</span>
                    </div>
                    <div class="col-6 text-center">
                        <span class="h2 font-weight-bold d-block">
                            <?php
                            $this->db->where('staf_id', $staf->id);
                            echo $this->db->count_all_results('staf_aktivitas');
                            ?>
                        </span>
                        <span class="text-sm text-muted">Total Aktivitas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Edit Form -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">✏️ Edit Profil</h6>
                        <p class="text-sm mb-0">Kelola informasi profil dan pengaturan akun Anda</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Alert Messages -->
                <?php if($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-check-bold"></i></span>
                        <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <span class="alert-icon"><i class="ni ni-fat-remove"></i></span>
                        <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
                        <button type="button" class="close" data-dismiss="alert">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <form action="<?= base_url('staf/dashboard/update_profil') ?>" method="POST" enctype="multipart/form-data">
                    <h6 class="heading-small text-muted mb-4">Informasi Personal</h6>
                    <div class="pl-lg-4">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-control-label" for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama lengkap" value="<?= $staf->nama ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-control-label" for="nip">NIP <span class="text-danger">*</span></label>
                                    <input type="text" id="nip" name="nip" class="form-control" placeholder="Nomor Induk Pegawai" value="<?= $staf->nip ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-control-label" for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="alamat@email.com" value="<?= $staf->email ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-control-label" for="nomor_telepon">Nomor Telepon <span class="text-danger">*</span></label>
                                    <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control" placeholder="08xxxxxxxxxx" value="<?= $staf->nomor_telepon ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-control-label" for="prodi_id">Program Studi</label>
                                    <select id="prodi_id" name="prodi_id" class="form-control">
                                        <option value="">Semua Program Studi</option>
                                        <?php
                                        $prodi_list = $this->db->get('prodi')->result();
                                        foreach($prodi_list as $prodi):
                                        ?>
                                            <option value="<?= $prodi->id ?>" <?= $staf->prodi_id == $prodi->id ? 'selected' : '' ?>>
                                                <?= $prodi->nama ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Kosongkan jika mengelola semua program studi</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-control-label" for="foto">Foto Profil</label>
                                    <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
                                    <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Security Settings -->
                    <h6 class="heading-small text-muted mb-4">Pengaturan Keamanan</h6>
                    <div class="pl-lg-4">
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info" role="alert">
                                    <span class="alert-icon"><i class="ni ni-notification-70"></i></span>
                                    <span class="alert-text">
                                        <strong>Informasi:</strong> Untuk keamanan, password login menggunakan NIP. 
                                        Jika perlu mengubah password, hubungi administrator sistem.
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-control-label">Password Login</label>
                                    <input type="password" class="form-control" value="<?= $staf->nip ?>" readonly>
                                    <small class="text-muted">Password sama dengan NIP Anda</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-control-label">Status Akun</label>
                                    <input type="text" class="form-control" value="Aktif" readonly>
                                    <small class="text-success">Akun Anda dalam status aktif</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Action Buttons -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Activity Log -->
        <div class="card mt-4">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="h3 mb-0">📈 Log Aktivitas Terbaru</h6>
                        <p class="text-sm mb-0">Riwayat aktivitas dalam sistem</p>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="timeline timeline-one-side">
                    <!-- Sample activity log -->
                    <div class="timeline-block py-2">
                        <span class="timeline-step">
                            <i class="ni ni-check-bold text-success"></i>
                        </span>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="text-sm mb-1">Validasi Publikasi</h6>
                                <small class="text-muted">2 jam yang lalu</small>
                            </div>
                            <p class="text-xs mb-0">Memvalidasi repository publikasi untuk mahasiswa: Hendro Mahasiswa</p>
                        </div>
                    </div>
                    
                    <div class="timeline-block py-2">
                        <span class="timeline-step">
                            <i class="ni ni-single-copy-04 text-info"></i>
                        </span>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="text-sm mb-1">Export Jurnal Bimbingan</h6>
                                <small class="text-muted">1 hari yang lalu</small>
                            </div>
                            <p class="text-xs mb-0">Mengexport jurnal bimbingan untuk periode Juli 2025</p>
                        </div>
                    </div>
                    
                    <div class="timeline-block py-2">
                        <span class="timeline-step">
                            <i class="ni ni-calendar-grid-58 text-warning"></i>
                        </span>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="text-sm mb-1">Penjadwalan Seminar</h6>
                                <small class="text-muted">3 hari yang lalu</small>
                            </div>
                            <p class="text-xs mb-0">Menjadwalkan seminar proposal untuk 5 mahasiswa</p>
                        </div>
                    </div>
                    
                    <div class="timeline-block py-2">
                        <span class="timeline-step">
                            <i class="ni ni-settings text-secondary"></i>
                        </span>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="text-sm mb-1">Update Profil</h6>
                                <small class="text-muted">1 minggu yang lalu</small>
                            </div>
                            <p class="text-xs mb-0">Memperbarui informasi profil dan foto</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-history"></i> Lihat Semua Aktivitas
                    </button>
                </div>
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
    // Image upload preview
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').css('background-image', 'url('+e.target.result +')');
                $('#imagePreview').hide();
                $('#imagePreview').fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    $("#imageUpload").change(function() {
        readURL(this);
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var email = $('#email').val();
        var nip = $('#nip').val();
        var nama = $('#nama').val();
        var telepon = $('#nomor_telepon').val();
        
        // Basic validation
        if (!nama.trim()) {
            alert('Nama lengkap harus diisi');
            e.preventDefault();
            return false;
        }
        
        if (!nip.trim()) {
            alert('NIP harus diisi');
            e.preventDefault();
            return false;
        }
        
        if (!email.trim()) {
            alert('Email harus diisi');
            e.preventDefault();
            return false;
        }
        
        // Email format validation
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Format email tidak valid');
            e.preventDefault();
            return false;
        }
        
        // Phone number validation (Indonesian format)
        var phoneRegex = /^(\+62|62|0)8[1-9][0-9]{6,9}$/;
        if (!phoneRegex.test(telepon.replace(/\s/g, ''))) {
            alert('Format nomor telepon tidak valid. Gunakan format Indonesia (08xxxxxxxxxx)');
            e.preventDefault();
            return false;
        }
        
        // File size validation
        var fileInput = $('#foto')[0];
        if (fileInput.files.length > 0) {
            var fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 2) {
                alert('Ukuran file foto maksimal 2MB');
                e.preventDefault();
                return false;
            }
        }
        
        return true;
    });
    
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Format phone number input
    $('#nomor_telepon').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        if (value.length > 0 && !value.startsWith('0')) {
            value = '0' + value;
        }
        $(this).val(value);
    });
    
    // NIP format validation
    $('#nip').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });
});

function deletePhoto() {
    if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
        $.post('<?= base_url("staf/dashboard/hapus_foto") ?>', function(response) {
            if (response.success) {
                $('#imagePreview').css('background-image', 'url(<?= base_url("assets/img/theme/default-avatar.png") ?>)');
                showAlert('success', 'Foto profil berhasil dihapus');
            } else {
                showAlert('error', 'Gagal menghapus foto profil');
            }
        }).fail(function() {
            showAlert('error', 'Terjadi kesalahan server');
        });
    }
}

function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <span class="alert-text">${message}</span>
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