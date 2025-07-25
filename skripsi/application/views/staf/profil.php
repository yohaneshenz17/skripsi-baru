<?php
// File: application/views/staf/profil.php
// PERBAIKAN: Menambahkan JavaScript untuk update foto real-time setelah berhasil submit

// Capture content untuk template
ob_start();
?>

<!-- Flash Messages -->
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

<!-- Content -->
<div class="row">
    <!-- Profile Info Card -->
    <div class="col-xl-4">
        <div class="card profile-card">
            <div class="profile-header text-white text-center py-5" style="background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%); border-radius: 15px 15px 0 0;">
                <div class="avatar-upload" style="position: relative; display: inline-block;">
                    <div class="avatar-edit" style="position: absolute; right: 12px; z-index: 1; top: 10px;">
                        <input type="file" id="imageUpload" accept=".png, .jpg, .jpeg" style="display: none;" />
                        <label for="imageUpload" style="display: inline-block; width: 34px; height: 34px; margin-bottom: 0; border-radius: 100%; background: #FFFFFF; border: 1px solid transparent; box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12); cursor: pointer; font-weight: normal; transition: all 0.2s ease-in-out;">
                            <i class="fas fa-edit" style="color: #757575; position: absolute; top: 8px; left: 0; right: 0; text-align: center; margin: auto;"></i>
                        </label>
                    </div>
                    <div class="avatar-preview" style="width: 150px; height: 150px; position: relative; border-radius: 100%; border: 6px solid #F8F8F8; box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.1);">
                        <!-- PERBAIKAN: Tambahkan ID untuk preview dan sinkronisasi dengan template -->
                        <div id="imagePreview" style="width: 100%; height: 100%; border-radius: 100%; background-size: cover; background-repeat: no-repeat; background-position: center; background-image: url('<?= $staf->foto ? base_url('cdn/img/staf/' . $staf->foto) : base_url('assets/img/theme/default-avatar.png') ?>');"></div>
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
                
                <!-- Tombol Hapus Foto -->
                <?php if($staf->foto): ?>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deletePhoto()">
                        <i class="fas fa-trash"></i> Hapus Foto
                    </button>
                </div>
                <?php endif; ?>
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
                            // Hitung publikasi yang divalidasi staf ini
                            $this->db->where('staf_validator_id', $staf->id);
                            echo $this->db->count_all_results('proposal_mahasiswa');
                            ?>
                        </span>
                        <span class="text-sm text-muted">Validasi Publikasi</span>
                    </div>
                    <div class="col-6 text-center">
                        <span class="h2 font-weight-bold d-block">
                            <?php
                            // Hitung total aktivitas staf ini
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
                <!-- PERBAIKAN: Tambahkan ID untuk form dan loading state -->
                <form id="profileForm" action="<?= base_url('staf/profil/update') ?>" method="POST" enctype="multipart/form-data">
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
                                    <!-- PERBAIKAN: Tambahkan ID dan event listener -->
                                    <input type="file" id="foto" name="foto" class="form-control" accept="image/*" onchange="previewFoto(this)">
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
                        <!-- PERBAIKAN: Tambahkan ID untuk button submit -->
                        <button type="submit" id="submitBtn" class="btn btn-primary">
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
                    <?php
                    // Ambil 5 aktivitas terbaru staf ini
                    $this->db->where('staf_id', $staf->id);
                    $this->db->order_by('tanggal_aktivitas', 'DESC');
                    $this->db->limit(5);
                    $aktivitas_list = $this->db->get('staf_aktivitas')->result();
                    
                    if($aktivitas_list):
                        foreach($aktivitas_list as $aktivitas):
                    ?>
                    <div class="timeline-block py-2">
                        <span class="timeline-step">
                            <i class="ni ni-check-bold text-success"></i>
                        </span>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="text-sm mb-1"><?= ucfirst(str_replace('_', ' ', $aktivitas->aktivitas)) ?></h6>
                                <small class="text-muted"><?= date('d M Y H:i', strtotime($aktivitas->tanggal_aktivitas)) ?></small>
                            </div>
                            <p class="text-xs mb-0"><?= $aktivitas->keterangan ?></p>
                        </div>
                    </div>
                    <?php 
                        endforeach;
                    else:
                    ?>
                    <div class="timeline-block py-2">
                        <span class="timeline-step">
                            <i class="ni ni-info text-info"></i>
                        </span>
                        <div class="timeline-content">
                            <h6 class="text-sm mb-1">Belum Ada Aktivitas</h6>
                            <p class="text-xs mb-0">Aktivitas Anda akan muncul di sini</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="text-center mt-3">
                    <a href="<?= base_url('staf/laporan') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-history"></i> Lihat Semua Aktivitas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PERBAIKAN: JavaScript yang diperbaiki untuk integrasi dengan template -->
<script>
$(document).ready(function() {
    console.log('Staf profil page loaded');
    
    // PERBAIKAN: Flag untuk tracking jika ada foto yang diupload
    let fotoUploaded = false;
    let uploadedFotoName = '';
    
    // PERBAIHAN: Image upload preview dan tracking
    window.previewFoto = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            fotoUploaded = true; // Set flag bahwa ada foto yang diupload
            
            // Validasi file
            if (!file.type.match(/^image\/(jpeg|jpg|png)$/)) {
                alert('Format file harus JPG, JPEG, atau PNG!');
                input.value = '';
                fotoUploaded = false;
                return;
            }
            
            if (file.size > 2048000) { // 2MB dalam bytes
                alert('Ukuran file maksimal 2MB!');
                input.value = '';
                fotoUploaded = false;
                return;
            }
            
            // Preview foto
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
                $('#imagePreview').hide().fadeIn(650);
                console.log('Photo preview updated');
            };
            reader.readAsDataURL(file);
        } else {
            fotoUploaded = false;
        }
    };
    
    // PERBAIKAN: Form submit dengan deteksi foto update
    $('#profileForm').on('submit', function(e) {
        const $submitBtn = $('#submitBtn');
        const originalText = $submitBtn.html();
        
        // Set loading state
        $submitBtn.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        // PERBAIKAN: Jika ada foto yang diupload, siapkan untuk update setelah submit
        if (fotoUploaded) {
            console.log('Photo upload detected, will update header/sidebar after success');
            
            // Set timeout untuk update foto setelah redirect berhasil
            // Ini akan berjalan setelah halaman reload dan flashdata diproses
            setTimeout(function() {
                // Check apakah ada flashdata foto_updated di template
                if (typeof window.updateStafProfilePhoto === 'function') {
                    // Function ini akan dipanggil otomatis oleh template jika ada flashdata
                    console.log('updateStafProfilePhoto function is available');
                }
            }, 500);
        }
        
        // Form akan submit secara normal, tidak mencegah default behavior
        // Controller akan handle upload dan set flashdata untuk update foto
        
        // Reset button jika ada error (failsafe)
        setTimeout(function() {
            $submitBtn.prop('disabled', false).html(originalText);
        }, 10000);
    });
    
    // Form validation yang sudah ada
    $('#profileForm').on('submit', function(e) {
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
        
        // File size validation (sudah ada di previewFoto, tapi double check)
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
    
    console.log('Staf profil JavaScript initialized');
});

function deletePhoto() {
    if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
        window.location.href = '<?= base_url("staf/profil/hapus_foto") ?>';
    }
}
</script>

<?php 
$content = ob_get_clean();

// Load template staf dengan content yang sudah di-capture
$this->load->view('template/staf', [
    'title' => 'Profil Staf Akademik',
    'content' => $content,
    'css' => '
        <style>
            .profile-card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            }
        </style>
    ',
    'script' => ''
]);
?>