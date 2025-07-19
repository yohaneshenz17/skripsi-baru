<?php $this->app->extend('template/kaprodi') ?>

<?php $this->app->setVar('title', 'Profil Kaprodi') ?>

<?php $this->app->section() ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Profil Kaprodi</div>
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
                <form action="<?= base_url('kaprodi/profil/update') ?>" method="post" enctype="multipart/form-data" id="form-foto">
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
                    <a href="<?= base_url() ?>kaprodi/profil/hapus_foto" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus foto profil?')">
                        <i class="fa fa-trash"></i> Hapus Foto
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Data Profil Section -->
            <div class="col-md-8">
                <h6 class="heading-small text-muted mb-4">Informasi Kaprodi</h6>
                
                <form id="edit">
                    <div class="form-group">
                        <label>NIDN/NIP</label>
                        <input type="text" class="form-control" name="nip" placeholder="Masukkan NIDN/NIP" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" placeholder="Masukkan Nama Lengkap" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" class="form-control" name="nomor_telepon" placeholder="Masukkan Nomor Telepon" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
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
    
    function show() {
        // Gunakan API dosen karena kaprodi juga tersimpan di tabel dosen
        call('api/dosen/details/' + id).done(function(res) {
            if (res.error == true) {
                notif(res.message, 'warning').then(function() {
                    window.location = base_url + 'auth/logout';
                })
            } else {
                $('[name=nip]').val(res.data.nip);
                $('[name=nama]').val(res.data.nama);
                $('[name=nomor_telepon]').val(res.data.nomor_telepon);
                $('[name=email]').val(res.data.email);
            }
        })
    }

    show();

    // Form edit data (AJAX) - UPDATE ENDPOINT UNTUK KAPRODI
    $(document).on('submit', 'form#edit', function(e) {
        e.preventDefault();
        console.log('Form edit submitted via AJAX - KAPRODI');
        console.log('Form data:', $(this).serialize());
        
        // GUNAKAN ENDPOINT KAPRODI
        $.ajax({
            url: base_url + 'kaprodi/profil/update',
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

    // Form upload foto (Normal Submit) - ENDPOINT SUDAH BENAR
    $(document).on('submit', 'form#form-foto', function(e) {
        console.log('Form foto submitted - KAPRODI');
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