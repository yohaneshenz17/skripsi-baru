<?php $this->app->extend('template/dosen') ?>

<?php $this->app->setVar('title', 'Profil') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Profil Saya</div>
    </div>
    <div class="card-body">
        
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Foto Profil Section -->
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <h6 class="heading-small text-muted mb-4">Foto Profil</h6>
                    <?php
                    $foto_name = !empty($user->foto) ? $user->foto : 'default.png';
                    // Add timestamp untuk cache busting
                    $foto_path = base_url('cdn/img/dosen/' . $foto_name) . '?t=' . time();
                    ?>
                    <img id="preview-foto" src="<?= $foto_path ?>" class="img-fluid rounded-circle shadow" style="width: 200px; height: 200px; object-fit: cover;">
                </div>
                
                <!-- Form Upload Foto -->
                <form action="<?= base_url('dosen/profil/update') ?>" method="post" enctype="multipart/form-data" id="form-foto">
                    <!-- Add hidden fields untuk memastikan ini adalah upload foto -->
                    <input type="hidden" name="action" value="upload_foto">
                    <div class="form-group">
                        <label class="form-control-label">Upload Foto Baru</label>
                        <input type="file" class="form-control" name="foto" id="foto" accept=".jpg,.jpeg" onchange="previewFoto()" required>
                        <small class="text-muted">Format: JPG/JPEG, Maksimal 1MB</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-upload"></i> Upload Foto
                    </button>
                </form>
                
                <?php if (isset($user->foto) && !empty($user->foto)): ?>
                <div class="mt-2">
                    <a href="<?= base_url() ?>dosen/profil/hapus_foto" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus foto profil?')">
                        <i class="fa fa-trash"></i> Hapus Foto
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Data Profil Section -->
            <div class="col-md-8">
                <h6 class="heading-small text-muted mb-4">Informasi Dosen</h6>
                
                <form id="edit">
                    <div class="form-group">
                        <label>NIDN/NIP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nip" placeholder="Masukkan NIDN/NIP" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" placeholder="Masukkan Nama Lengkap" autocomplete="off">
                    </div>
                    
                    <!-- ✅ TAMBAHAN: Program Studi Dropdown -->
                    <div class="form-group">
                        <label>Program Studi <span class="text-danger">*</span></label>
                        <select class="form-control" name="prodi_id" id="prodi_id">
                            <option value="">- Pilih Program Studi -</option>
                            <!-- Options akan diisi oleh JavaScript -->
                        </select>
                        <small class="text-muted">Pilih program studi yang Anda ampu</small>
                    </div>
                    
                    <!-- ✅ TAMBAHAN: Bidang Keilmuan -->
                    <div class="form-group">
                        <label>Bidang Keilmuan</label>
                        <input type="text" class="form-control" name="bidang_keilmuan" placeholder="Contoh: Pendidikan Matematika, Teknologi Pendidikan" autocomplete="off">
                        <small class="text-muted">Sesuai latar belakang pendidikan Anda</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nomor Telepon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nomor_telepon" placeholder="Masukkan Nomor Telepon" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" placeholder="Masukkan Email" autocomplete="off">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $this->app->endSection('content') ?>

<?php $this->app->section() ?>
<script>
$(document).ready(function() {
    var id = '<?= $this->session->userdata('id') ?>'
    
    // ✅ TAMBAHAN: Load data program studi untuk dropdown
    function loadProgramStudi() {
        call('api/prodi').done(function(req) {
            let options = '<option value="">- Pilih Program Studi -</option>';
            if (req.data && req.data.length > 0) {
                req.data.forEach(function(prodi) {
                    options += `<option value="${prodi.id}">${prodi.nama}</option>`;
                });
            }
            $('#prodi_id').html(options);
            
            // Setelah dropdown terisi, load data dosen
            show();
        }).fail(function(xhr, status, error) {
            console.error('Failed to load program studi:', error);
            // Tetap load data dosen meskipun prodi gagal
            show();
        });
    }
    
    function show() {
        call('api/dosen/details/' + id).done(function(res) {
            if (res.error == true) {
                notif(res.message, 'warning').then(function() {
                    window.location = base_url + 'auth/logout';
                })
            } else {
                console.log('Data dosen loaded:', res.data); // Debug log
                
                // Fill existing fields
                $('[name=nip]').val(res.data.nip || '');
                $('[name=nama]').val(res.data.nama || '');
                $('[name=nomor_telepon]').val(res.data.nomor_telepon || '');
                $('[name=email]').val(res.data.email || '');
                
                // ✅ TAMBAHAN: Fill new fields
                $('[name=prodi_id]').val(res.data.prodi_id || '');
                $('[name=bidang_keilmuan]').val(res.data.bidang_keilmuan || '');
            }
        }).fail(function(xhr, status, error) {
            console.error('Failed to load dosen data:', error);
            notif('Gagal memuat data profil', 'error');
        });
    }

    // Initialize - ✅ TAMBAHAN: Load program studi terlebih dahulu
    loadProgramStudi();

    // ✅ UPDATE: Form edit data (AJAX) untuk include field baru + validasi
    $(document).on('submit', 'form#edit', function(e) {
        e.preventDefault();
        console.log('Form edit submitted via AJAX');
        console.log('Form data:', $(this).serialize());
        
        // ✅ TAMBAHAN: Validasi field yang wajib diisi
        let nip = $('[name=nip]').val().trim();
        let nama = $('[name=nama]').val().trim();
        let prodi_id = $('[name=prodi_id]').val();
        let nomor_telepon = $('[name=nomor_telepon]').val().trim();
        let email = $('[name=email]').val().trim();
        
        if (!nip || !nama || !prodi_id || !nomor_telepon || !email) {
            notif('Mohon lengkapi semua field yang wajib diisi (bertanda *)', 'warning');
            return false;
        }
        
        // GUNAKAN ENDPOINT YANG BENAR - bukan api/dosen tapi dosen/profil/update
        $.ajax({
            url: base_url + 'dosen/profil/update',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(req) {
                console.log('AJAX response:', req);
                if (req.error == true) {
                    notif(req.message, 'error', true);
                } else {
                    notif(req.message, 'success');
                    show(); // Refresh data
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX failed:', error);
                console.error('Response:', xhr.responseText);
                notif('Error: ' + error, 'error');
            }
        });
    })

    // Form upload foto (Normal Submit) - FIX FORM ACTION
    $(document).on('submit', 'form#form-foto', function(e) {
        console.log('Form foto submitted');
        console.log('Form action:', this.action);
        // Let the form submit normally for file upload
        return true;
    })
})

// Preview foto function
function previewFoto() {
    const file = document.getElementById('foto').files[0];
    const preview = document.getElementById('preview-foto');
    
    if (file) {
        // Validasi file type
        if (!file.type.match(/^image\/(jpeg|jpg)$/)) {
            alert('Format file harus JPG atau JPEG!');
            document.getElementById('foto').value = '';
            return;
        }
        
        // Validasi file size (1MB = 1024 * 1024 bytes)
        if (file.size > 1024 * 1024) {
            alert('Ukuran file maksimal 1MB!');
            document.getElementById('foto').value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>
<?php $this->app->endSection('script') ?>

<?php $this->app->init() ?>