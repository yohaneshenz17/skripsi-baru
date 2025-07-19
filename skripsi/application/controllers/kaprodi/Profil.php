<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('upload');
        $this->load->library('image_lib');
        $this->load->helper('url');
        $this->load->helper('file');

        // Cek login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        // Cek level kaprodi (level '4' = kaprodi)
        if ($this->session->userdata('level') != '4') {
            show_error('Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.', 403);
        }
    }

    public function index()
    {
        $data['title'] = 'Profil Kaprodi';
        $dosen_id = $this->session->userdata('id');

        // Ambil data kaprodi (tetap dari tabel dosen karena kaprodi adalah dosen level 4)
        $data['user'] = $this->db->get_where('dosen', ['id' => $dosen_id])->row();

        if (!$data['user']) {
            // Jika data tidak ditemukan, buat object kosong untuk menghindari error
            $data['user'] = (object) [
                'nip' => '',
                'nama' => '',
                'nomor_telepon' => '',
                'email' => '',
                'foto' => ''
            ];
        }

        $this->load->view('kaprodi/profil', $data);
    }

    public function update()
    {
        error_log("DEBUG: update() method called - KAPRODI");
        error_log("DEBUG: POST data: " . print_r($_POST, true));
        error_log("DEBUG: FILES data: " . print_r($_FILES, true));
        
        if ($this->input->post()) {
            $dosen_id = $this->session->userdata('id');
            error_log("DEBUG: Kaprodi ID from session: $dosen_id");
            
            // Cek apakah ini request upload foto atau update data biasa
            $is_photo_upload = !empty($_FILES['foto']['name']) || $this->input->post('action') == 'upload_foto';
            error_log("DEBUG: Is photo upload: " . ($is_photo_upload ? 'YES' : 'NO'));
            
            if ($is_photo_upload) {
                error_log("DEBUG: Routing to photo upload handler");
                // Handle foto upload dengan form submit
                $this->_handle_photo_upload($dosen_id);
            } else {
                error_log("DEBUG: Routing to data update handler");
                // Handle update data biasa dengan AJAX  
                $this->_handle_data_update($dosen_id);
            }
        } else {
            error_log("DEBUG: No POST data, redirecting to kaprodi profile");
            redirect('kaprodi/profil');
        }
    }
    
    private function _handle_photo_upload($dosen_id)
    {
        error_log("DEBUG: _handle_photo_upload called with kaprodi_id: $dosen_id");
        error_log("DEBUG: FILES data: " . print_r($_FILES, true));
        
        // Handle upload foto
        $upload_result = $this->_upload_foto();
        error_log("DEBUG: Upload result: " . print_r($upload_result, true));
        
        if ($upload_result['status']) {
            $data_update = ['foto' => $upload_result['filename']];
            
            // Hapus foto lama jika ada
            $old_dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
            if ($old_dosen && !empty($old_dosen->foto) && $old_dosen->foto != 'default.png') {
                $old_file = './cdn/img/dosen/' . $old_dosen->foto;
                if (file_exists($old_file)) {
                    unlink($old_file);
                    error_log("DEBUG: Old photo deleted: $old_file");
                }
            }
            
            // Update database
            $this->db->where('id', $dosen_id);
            if ($this->db->update('dosen', $data_update)) {
                error_log("DEBUG: Database updated successfully with foto: " . $upload_result['filename']);
                
                // Update session foto
                $this->session->set_userdata('foto', $upload_result['filename']);
                error_log("DEBUG: Session updated with foto: " . $upload_result['filename']);
                
                $this->session->set_flashdata('success', 'Foto profil berhasil diperbarui!');
            } else {
                error_log("DEBUG: Database update failed: " . $this->db->error()['message']);
                $this->session->set_flashdata('error', 'Gagal memperbarui foto profil!');
            }
        } else {
            error_log("DEBUG: Upload failed: " . $upload_result['message']);
            $this->session->set_flashdata('error', $upload_result['message']);
        }
        
        redirect('kaprodi/profil');
    }
    
    private function _handle_data_update($dosen_id)
    {
        // Debug: Log input received
        error_log("DEBUG: _handle_data_update called with kaprodi_id: $dosen_id");
        
        // Validasi input
        $nama = $this->input->post('nama');
        $email = $this->input->post('email');
        $nomor_telepon = $this->input->post('nomor_telepon');
        $nip = $this->input->post('nip');

        error_log("DEBUG: Input data - NIP: $nip, Email: $email");

        // Validasi email unique (kecuali untuk kaprodi yang sama)
        $this->db->where('email', $email);
        $this->db->where('id !=', $dosen_id);
        $email_exists = $this->db->get('dosen')->num_rows();

        error_log("DEBUG: Email validation - exists: $email_exists");

        if ($email_exists > 0) {
            error_log("DEBUG: Email already exists, returning error");
            echo json_encode(['error' => true, 'message' => 'Email sudah digunakan oleh dosen/kaprodi lain.']);
            return;
        }

        // SKIP VALIDASI NIP - Abaikan duplikasi NIP di database
        // Validasi NIP dihilangkan sesuai permintaan user
        error_log("DEBUG: NIP validation SKIPPED as requested");

        // Data yang akan diupdate
        $data_update = [
            'nama' => $nama,
            'email' => $email,
            'nomor_telepon' => $nomor_telepon,
            'nip' => $nip
        ];

        error_log("DEBUG: Attempting database update");

        // Update data
        $this->db->where('id', $dosen_id);
        if ($this->db->update('dosen', $data_update)) {
            error_log("DEBUG: Database update successful");
            
            // Update session data
            $this->session->set_userdata([
                'nama' => $nama,
                'email' => $email
            ]);

            echo json_encode(['error' => false, 'message' => 'Profil berhasil diperbarui!']);
        } else {
            error_log("DEBUG: Database update failed - " . $this->db->error()['message']);
            echo json_encode(['error' => true, 'message' => 'Gagal memperbarui profil!']);
        }
    }

    private function _upload_foto()
    {
        // Buat folder jika belum ada (tetap di folder dosen karena kaprodi juga dosen)
        if (!is_dir('./cdn/img/dosen/')) {
            mkdir('./cdn/img/dosen/', 0755, true);
        }

        // Konfigurasi upload - sesuai requirement: JPG maksimal 1MB
        $config['upload_path'] = './cdn/img/dosen/';
        $config['allowed_types'] = 'jpg|jpeg';
        $config['max_size'] = 1024; // 1MB = 1024KB
        $config['encrypt_name'] = TRUE;
        $config['remove_spaces'] = TRUE;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('foto')) {
            return [
                'status' => false,
                'message' => 'Gagal upload foto: ' . $this->upload->display_errors('', '')
            ];
        }

        $upload_data = $this->upload->data();
        
        // Resize foto jika terlalu besar
        $config_resize = [
            'image_library' => 'gd2',
            'source_image' => $upload_data['full_path'],
            'maintain_ratio' => TRUE,
            'width' => 300,
            'height' => 300
        ];

        $this->image_lib->initialize($config_resize);
        $this->image_lib->resize();

        return [
            'status' => true,
            'filename' => $upload_data['file_name'],
            'message' => 'Foto berhasil diupload'
        ];
    }

    public function debug()
    {
        echo "<h2>DEBUG MODE - Kaprodi Profil Controller</h2>";
        echo "<h3>1. Session Data:</h3>";
        echo "<pre>";
        print_r($this->session->userdata());
        echo "</pre>";
        
        echo "<h3>2. Database Current Data:</h3>";
        $dosen_id = $this->session->userdata('id');
        $current_data = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
        echo "<pre>";
        print_r($current_data);
        echo "</pre>";
        
        echo "<h3>3. Upload Directory Check:</h3>";
        $upload_dir = './cdn/img/dosen/';
        echo "Directory exists: " . (is_dir($upload_dir) ? 'YES' : 'NO') . "<br>";
        echo "Directory writable: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "<br>";
        echo "Directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";
        
        echo "<h3>4. Files in Upload Directory:</h3>";
        if (is_dir($upload_dir)) {
            $files = scandir($upload_dir);
            echo "<pre>";
            print_r($files);
            echo "</pre>";
        }
        
        echo "<h3>5. PHP Upload Settings:</h3>";
        echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
        echo "post_max_size: " . ini_get('post_max_size') . "<br>";
        echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
        echo "file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "<br>";
        
        echo "<hr><p><a href='" . base_url('kaprodi/profil') . "'>← Back to Profile</a></p>";
    }

    public function test_upload()
    {
        echo "<h2>DEBUG - Test Upload Process (Kaprodi)</h2>";
        
        if ($_POST) {
            echo "<h3>POST Data Received:</h3>";
            echo "<pre>";
            print_r($_POST);
            echo "</pre>";
            
            echo "<h3>FILES Data Received:</h3>";
            echo "<pre>";
            print_r($_FILES);
            echo "</pre>";
            
            if (!empty($_FILES['foto']['name'])) {
                echo "<h3>Processing Upload...</h3>";
                $upload_result = $this->_upload_foto();
                echo "<pre>";
                print_r($upload_result);
                echo "</pre>";
                
                if ($upload_result['status']) {
                    echo "<h3>Upload Success - Testing Database Update...</h3>";
                    $dosen_id = $this->session->userdata('id');
                    $this->db->where('id', $dosen_id);
                    $update_result = $this->db->update('dosen', ['foto' => $upload_result['filename']]);
                    echo "Database update result: " . ($update_result ? 'SUCCESS' : 'FAILED') . "<br>";
                    echo "Database error: " . $this->db->error()['message'] . "<br>";
                    
                    echo "<h3>Updating Session...</h3>";
                    $this->session->set_userdata('foto', $upload_result['filename']);
                    echo "Session foto updated to: " . $this->session->userdata('foto') . "<br>";
                }
            } else {
                echo "<h3>Testing Data Update (No Photo)...</h3>";
                $this->_debug_data_update();
            }
        } else {
            // Show test form
            echo '<form method="post" enctype="multipart/form-data">';
            echo '<h3>Test Photo Upload:</h3>';
            echo '<input type="file" name="foto" accept=".jpg,.jpeg"><br><br>';
            echo '<input type="hidden" name="action" value="upload_foto">';
            echo '<button type="submit">Test Upload</button>';
            echo '</form>';
            
            echo '<hr>';
            echo '<form method="post">';
            echo '<h3>Test Data Update:</h3>';
            echo 'NIP: <input type="text" name="nip" value="TEST_NIP_123"><br><br>';
            echo 'Nama: <input type="text" name="nama" value="Test Kaprodi Name"><br><br>';
            echo 'Email: <input type="email" name="email" value="testkaprodi@example.com"><br><br>';
            echo 'Telepon: <input type="text" name="nomor_telepon" value="081234567890"><br><br>';
            echo '<button type="submit">Test Data Update</button>';
            echo '</form>';
        }
        
        echo "<hr><p><a href='" . base_url('kaprodi/profil') . "'>← Back to Profile</a></p>";
    }
    
    private function _debug_data_update()
    {
        $dosen_id = $this->session->userdata('id');
        $nama = $this->input->post('nama');
        $email = $this->input->post('email');
        $nomor_telepon = $this->input->post('nomor_telepon');
        $nip = $this->input->post('nip');
        
        echo "Kaprodi ID: $dosen_id<br>";
        echo "Input NIP: $nip<br>";
        echo "Input Email: $email<br>";
        
        // Check email validation
        echo "<h4>Email Validation Check:</h4>";
        $this->db->where('email', $email);
        $this->db->where('id !=', $dosen_id);
        $email_exists = $this->db->get('dosen')->num_rows();
        echo "Email exists query: " . $this->db->last_query() . "<br>";
        echo "Email exists result: $email_exists<br>";
        
        // Check NIP validation (should be skipped)
        echo "<h4>NIP Validation Check (Should be SKIPPED):</h4>";
        echo "NIP validation should be disabled in the code.<br>";
        
        // Test update
        echo "<h4>Testing Database Update:</h4>";
        $data_update = [
            'nama' => $nama,
            'email' => $email,
            'nomor_telepon' => $nomor_telepon,
            'nip' => $nip
        ];
        
        $this->db->where('id', $dosen_id);
        $update_result = $this->db->update('dosen', $data_update);
        echo "Update query: " . $this->db->last_query() . "<br>";
        echo "Update result: " . ($update_result ? 'SUCCESS' : 'FAILED') . "<br>";
        echo "Database error: " . $this->db->error()['message'] . "<br>";
    }

    public function hapus_foto()
    {
        $dosen_id = $this->session->userdata('id');
        
        // Ambil data kaprodi (dari tabel dosen)
        $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
        
        if ($dosen && !empty($dosen->foto) && $dosen->foto != 'default.png') {
            // Hapus file foto
            $foto_path = './cdn/img/dosen/' . $dosen->foto;
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
            
            // Update database
            $this->db->where('id', $dosen_id);
            $this->db->update('dosen', ['foto' => '']);
            
            // Update session
            $this->session->set_userdata('foto', '');
            
            $this->session->set_flashdata('success', 'Foto profil berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Foto tidak ditemukan!');
        }
        
        redirect('kaprodi/profil');
    }
}

/* End of file Profil.php */
/* Location: ./application/controllers/kaprodi/Profil.php */