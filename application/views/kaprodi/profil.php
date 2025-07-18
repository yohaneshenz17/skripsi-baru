<?php
ob_start();
?>

<div class="row">
    <div class="col-lg-12">
        <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('success') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-text"><?= $this->session->flashdata('error') ?></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="ni ni-single-02 text-primary"></i> Profil Kaprodi
                </h3>
            </div>
            <div class="card-body">
                <form method="post" action="<?= base_url() ?>kaprodi/profil/update" enctype="multipart/form-data" id="form-profil">
                    <div class="row">
                        <!-- Kolom Foto -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Foto Profil</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <?php 
                                        // Cek apakah ada foto di database
                                        $foto_name = isset($user->foto) && !empty($user->foto) ? $user->foto : 'default.png';
                                        $foto_path = base_url('cdn/img/dosen/' . $foto_name);
                                        ?>
                                        <img id="preview-foto" src="<?= $foto_path ?>" class="img-fluid rounded-circle shadow" style="width: 200px; height: 200px; object-fit: cover;">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-control-label">Upload Foto Baru</label>
                                        <input type="file" class="form-control" name="foto" id="foto" accept=".jpg,.jpeg" onchange="previewFoto()">
                                        <small class="text-muted">Format: JPG/JPEG, Maksimal 500KB</small>
                                    </div>
                                    
                                    <?php if (isset($user->foto) && !empty($user->foto)): ?>
                                    <div class="mt-2">
                                        <a href="<?= base_url() ?>kaprodi/profil/hapus_foto" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus foto profil?')">
                                            <i class="fa fa-trash"></i> Hapus Foto
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Form -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informasi Profil</h5>
                                </div>
                                <div class="card-body">
                                    <h6 class="heading-small text-muted mb-4">Informasi Pengguna</h6>
                                    <div class="pl-lg-4">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label class="form-control-label">Nama Lengkap <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user->nama) ?>" required placeholder="Masukkan nama lengkap">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">NIDN <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nip" value="<?= htmlspecialchars($user->nip) ?>" required placeholder="Masukkan NIDN">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Email <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user->email) ?>" required placeholder="Masukkan email">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Nomor Telepon <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nomor_telepon" value="<?= htmlspecialchars($user->nomor_telepon) ?>" required placeholder="Masukkan nomor telepon">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Level</label>
                                                    <input type="text" class="form-control" value="Ketua Program Studi" readonly>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Informasi Program Studi -->
                                        <h6 class="heading-small text-muted mb-4 mt-4">Informasi Program Studi</h6>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Program Studi</label>
                                                    <?php
                                                    // Ambil info prodi dari database
                                                    $prodi_info = $this->db->select('p.nama as nama_prodi, f.nama as nama_fakultas')
                                                        ->from('prodi p')
                                                        ->join('fakultas f', 'p.fakultas_id = f.id', 'left')
                                                        ->where('p.dosen_id', $user->id)
                                                        ->get()->row();
                                                    ?>
                                                    <input type="text" class="form-control" value="<?= $prodi_info ? $prodi_info->nama_prodi : 'Tidak ada' ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Fakultas</label>
                                                    <input type="text" class="form-control" value="<?= $prodi_info ? $prodi_info->nama_fakultas : 'Tidak ada' ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="text-right">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
ob_start();
?>

<script>
function previewFoto() {
    var file = document.getElementById('foto').files[0];
    var preview = document.getElementById('preview-foto');
    
    if (file) {
        // Validasi ukuran file (500KB = 500 * 1024 bytes)
        if (file.size > 500 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 500KB');
            document.getElementById('foto').value = '';
            return;
        }
        
        // Validasi format file
        var allowedTypes = ['image/jpeg', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Hanya JPG/JPEG yang diizinkan');
            document.getElementById('foto').value = '';
            return;
        }
        
        // Preview foto
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

$(document).ready(function() {
    $('#form-profil').on('submit', function(e) {
        // Validasi form sebelum submit
        var nama = $('input[name="nama"]').val();
        var nip = $('input[name="nip"]').val();
        var email = $('input[name="email"]').val();
        var nomor_telepon = $('input[name="nomor_telepon"]').val();
        
        if (nama == '' || nip == '' || email == '' || nomor_telepon == '') {
            alert('Semua field wajib diisi!');
            e.preventDefault();
            return false;
        }
        
        // Validasi format email
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert('Format email tidak valid!');
            e.preventDefault();
            return false;
        }
        
        // Validasi nomor telepon (hanya angka)
        var phonePattern = /^[0-9]+$/;
        if (!phonePattern.test(nomor_telepon)) {
            alert('Nomor telepon hanya boleh berisi angka!');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
});
</script>

<?php 
$script = ob_get_clean(); 

$this->load->view('template/kaprodi', [
    'title' => 'Profil Saya',
    'content' => $content,
    'script' => $script
]); 
?>