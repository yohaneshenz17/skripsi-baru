<?php
// PERBAIKAN: Buat helper function untuk mendapatkan foto mahasiswa
// Tambahkan di application/helpers/foto_helper.php atau langsung di template

if (!function_exists('get_mahasiswa_foto')) {
    function get_mahasiswa_foto($mahasiswa_id = null, $with_timestamp = true) {
        $CI =& get_instance();
        
        // Gunakan mahasiswa_id dari session jika tidak diberikan
        if (empty($mahasiswa_id)) {
            $mahasiswa_id = $CI->session->userdata('id');
        }
        
        // Ambil foto dari session terlebih dahulu
        $foto_name = $CI->session->userdata('foto');
        
        // Jika session kosong atau tidak valid, query database
        if (empty($foto_name) || $foto_name === 'default.png') {
            try {
                $mahasiswa = $CI->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
                
                if ($mahasiswa && !empty($mahasiswa->foto)) {
                    $foto_name = $mahasiswa->foto;
                    // Update session dengan foto dari database
                    $CI->session->set_userdata('foto', $foto_name);
                } else {
                    $foto_name = 'default.png';
                }
            } catch (Exception $e) {
                log_message('error', 'Error getting mahasiswa foto: ' . $e->getMessage());
                $foto_name = 'default.png';
            }
        }
        
        // Pastikan foto name tidak kosong
        if (empty($foto_name)) {
            $foto_name = 'default.png';
        }
        
        // Build URL dengan cache busting jika diminta
        $foto_url = base_url('cdn/img/mahasiswa/' . $foto_name);
        
        if ($with_timestamp) {
            $foto_url .= '?v=' . time();
        }
        
        return $foto_url;
    }
}

// PERBAIKAN: Function untuk refresh session foto
if (!function_exists('refresh_mahasiswa_foto_session')) {
    function refresh_mahasiswa_foto_session($mahasiswa_id = null) {
        $CI =& get_instance();
        
        if (empty($mahasiswa_id)) {
            $mahasiswa_id = $CI->session->userdata('id');
        }
        
        try {
            $mahasiswa = $CI->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
            
            if ($mahasiswa) {
                $new_foto = !empty($mahasiswa->foto) ? $mahasiswa->foto : 'default.png';
                $CI->session->set_userdata('foto', $new_foto);
                return $new_foto;
            }
        } catch (Exception $e) {
            log_message('error', 'Error refreshing foto session: ' . $e->getMessage());
        }
        
        return 'default.png';
    }
}
?>

<!-- PERBAIKAN: Template mahasiswa header dengan foto yang selalu update -->
<!-- Ganti bagian navbar avatar di template/mahasiswa.php -->

<ul class="navbar-nav align-items-center ml-auto ml-md-0">
    <li class="nav-item dropdown">
        <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <div class="media align-items-center">
                <span class="avatar avatar-sm rounded-circle">
                    <?php
                    // PERBAIKAN: Gunakan helper function dengan fallback yang kuat
                    $mahasiswa_id = $this->session->userdata('id');
                    $foto_session = $this->session->userdata('foto');
                    
                    // Multi-layer fallback untuk foto
                    $foto_name = 'default.png';
                    
                    // Layer 1: Cek session
                    if (!empty($foto_session) && $foto_session !== 'default.png') {
                        $foto_name = $foto_session;
                    } else {
                        // Layer 2: Query database
                        try {
                            $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
                            if ($mahasiswa && !empty($mahasiswa->foto)) {
                                $foto_name = $mahasiswa->foto;
                                // Sync session dengan database
                                $this->session->set_userdata('foto', $foto_name);
                            }
                        } catch (Exception $e) {
                            // Layer 3: Fallback ke default jika ada error
                            log_message('error', 'Error loading mahasiswa photo in template: ' . $e->getMessage());
                            $foto_name = 'default.png';
                        }
                    }
                    
                    // Pastikan file foto ada, jika tidak gunakan default
                    $foto_path = FCPATH . 'cdn/img/mahasiswa/' . $foto_name;
                    if (!file_exists($foto_path) && $foto_name !== 'default.png') {
                        $foto_name = 'default.png';
                        $this->session->set_userdata('foto', 'default.png');
                    }
                    
                    // Build URL dengan timestamp untuk cache busting
                    $timestamp = filemtime(FCPATH . 'cdn/img/mahasiswa/' . $foto_name);
                    $foto_url = base_url('cdn/img/mahasiswa/' . $foto_name) . '?v=' . $timestamp;
                    ?>
                    <img alt="Foto Profil" src="<?= $foto_url ?>" 
                         class="header-avatar-img" 
                         id="header-profile-photo"
                         onerror="this.src='<?= base_url('cdn/img/mahasiswa/default.png') ?>'">
                </span>
                <div class="media-body ml-2 d-none d-lg-block">
                    <span class="mb-0 text-sm font-weight-bold"><?= $this->session->userdata('nama') ?></span>
                </div>
            </div>
        </a>
        
        <!-- Dropdown menu tetap sama -->
        <div class="dropdown-menu dropdown-menu-right">
            <div class="dropdown-header noti-title">
                <h6 class="text-overflow m-0">Selamat datang!</h6>
            </div>
            <a href="<?= base_url() ?>mahasiswa/profil" class="dropdown-item">
                <i class="ni ni-single-02"></i>
                <span>Profil Saya</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= base_url() ?>auth/logout" class="dropdown-item">
                <i class="ni ni-user-run"></i>
                <span>Logout</span>
            </a>
        </div>
    </li>
</ul>

<!-- PERBAIKAN: CSS untuk foto yang responsive dan fallback yang baik -->
<style>
.header-avatar-img {
    width: 36px !important;
    height: 36px !important;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.header-avatar-img:hover {
    transform: scale(1.05);
    border-color: rgba(255, 255, 255, 0.4);
}

/* Sidebar profile photo jika ada */
.sidebar-profile .avatar img {
    width: 48px !important;
    height: 48px !important;
    object-fit: cover;
}

/* Loading state untuk foto */
.header-avatar-img.loading {
    opacity: 0.6;
    animation: pulse 1.5s ease-in-out infinite alternate;
}

@keyframes pulse {
    from { opacity: 0.6; }
    to { opacity: 1; }
}
</style>

<!-- PERBAIKAN: JavaScript global untuk handle update foto -->
<script>
// Global function untuk update foto di header
window.updateHeaderProfilePhoto = function(newFotoName) {
    console.log('Global updateHeaderProfilePhoto called with:', newFotoName);
    
    if (!newFotoName) {
        newFotoName = 'default.png';
    }
    
    const timestamp = new Date().getTime();
    const newFotoUrl = base_url + 'cdn/img/mahasiswa/' + newFotoName + '?v=' + timestamp;
    
    console.log('Updating header photo to:', newFotoUrl);
    
    // Update header photo dengan fallback
    const headerImg = document.getElementById('header-profile-photo');
    if (headerImg) {
        headerImg.classList.add('loading');
        
        // Test jika gambar bisa di-load
        const testImg = new Image();
        testImg.onload = function() {
            headerImg.src = newFotoUrl;
            headerImg.classList.remove('loading');
            console.log('Header photo updated successfully');
        };
        testImg.onerror = function() {
            console.warn('New photo failed to load, keeping current');
            headerImg.classList.remove('loading');
        };
        testImg.src = newFotoUrl;
    }
    
    // Update semua foto profil lain di halaman
    $('.header-avatar-img, .foto-profil, .sidebar-profile img').each(function() {
        if (this.id !== 'header-profile-photo') { // Skip yang sudah di-handle di atas
            $(this).attr('src', newFotoUrl);
        }
    });
};

// Event listener untuk profile photo update
$(document).on('profilePhotoUpdated', function(event, fotoName) {
    if (typeof window.updateHeaderProfilePhoto === 'function') {
        window.updateHeaderProfilePhoto(fotoName);
    }
});
</script>