<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Profil Controller untuk Staf Akademik
 * File: application/controllers/staf/Profil.php
 * 
 * CATATAN PENTING:
 * - Auth.php SUDAH MENDUKUNG login staf (level = '5') dengan sempurna
 * - Controller ini melengkapi fungsionalitas profil yang belum ada
 */
class Profil extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['session', 'upload', 'image_lib']);
        $this->load->helper('url');

        // Cek login dan level staf
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    public function index() {
        $staf_id = $this->session->userdata('id');
        
        // Ambil data staf dari tabel dosen (level = 5)
        $data['staf'] = $this->db->get_where('dosen', ['id' => $staf_id, 'level' => '5'])->row();
        
        if (!$data['staf']) {
            $this->session->set_flashdata('error', 'Data profil tidak ditemukan!');
            redirect('staf/dashboard');
        }
        
        $data['title'] = 'Profil Staf Akademik';
        $this->load->view('staf/profil', $data);
    }

    public function update() {
        $staf_id = $this->session->userdata('id');
        
        // Ambil data dari form
        $nama = trim($this->input->post('nama'));
        $nip = trim($this->input->post('nip'));
        $email = trim($this->input->post('email'));
        $nomor_telepon = trim($this->input->post('nomor_telepon'));
        $prodi_id = $this->input->post('prodi_id');
        
        // Validasi input
        if (empty($nama) || empty($nip) || empty($email) || empty($nomor_telepon)) {
            $this->session->set_flashdata('error', 'Semua field yang wajib harus diisi!');
            redirect('staf/profil');
            return;
        }
        
        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('error', 'Format email tidak valid!');
            redirect('staf/profil');
            return;
        }
        
        // Cek duplikasi email (kecuali email sendiri)
        $this->db->where('email', $email);
        $this->db->where('id !=', $staf_id);
        $email_exists = $this->db->count_all_results('dosen');
        
        if ($email_exists > 0) {
            $this->session->set_flashdata('error', 'Email sudah digunakan oleh pengguna lain!');
            redirect('staf/profil');
            return;
        }
        
        // Data yang akan diupdate
        $data_update = [
            'nama' => $nama,
            'email' => $email,
            'nomor_telepon' => $nomor_telepon,
            'nip' => $nip
        ];
        
        // Update prodi_id jika ada
        if (!empty($prodi_id)) {
            $data_update['prodi_id'] = $prodi_id;
        }
        
        // Handle upload foto jika ada
        if (!empty($_FILES['foto']['name'])) {
            $upload_result = $this->_upload_foto();
            if ($upload_result['status']) {
                // Hapus foto lama jika ada
                $old_staf = $this->db->get_where('dosen', ['id' => $staf_id])->row();
                if ($old_staf && !empty($old_staf->foto) && file_exists('./cdn/img/staf/' . $old_staf->foto)) {
                    unlink('./cdn/img/staf/' . $old_staf->foto);
                }
                $data_update['foto'] = $upload_result['filename'];
            } else {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('staf/profil');
                return;
            }
        }
        
        // Update data ke database
        $this->db->where('id', $staf_id);
        if ($this->db->update('dosen', $data_update)) {
            // Update session data
            $this->session->set_userdata([
                'nama' => $nama,
                'email' => $email
            ]);
            
            $this->session->set_flashdata('success', 'Profil berhasil diperbarui!');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui profil!');
        }
        
        redirect('staf/profil');
    }
    
    public function hapus_foto() {
        $staf_id = $this->session->userdata('id');
        
        // Ambil data staf
        $staf = $this->db->get_where('dosen', ['id' => $staf_id, 'level' => '5'])->row();
        
        if ($staf && !empty($staf->foto) && $staf->foto != 'default.png') {
            // Hapus file foto
            $foto_path = './cdn/img/staf/' . $staf->foto;
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
            
            // Update database
            $this->db->where('id', $staf_id);
            $this->db->update('dosen', ['foto' => '']);
            
            $this->session->set_flashdata('success', 'Foto profil berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Foto tidak ditemukan!');
        }
        
        redirect('staf/profil');
    }
    
    private function _upload_foto() {
        // Buat folder jika belum ada
        if (!is_dir('./cdn/img/staf/')) {
            mkdir('./cdn/img/staf/', 0755, true);
        }

        // Konfigurasi upload
        $config['upload_path'] = './cdn/img/staf/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 2048; // 2MB
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
    
    public function debug() {
        echo "<h2>DEBUG MODE - Staf Profil Controller</h2>";
        echo "<h3>1. Session Data:</h3>";
        echo "<pre>";
        print_r($this->session->userdata());
        echo "</pre>";
        
        echo "<h3>2. Database Test:</h3>";
        $staf_id = $this->session->userdata('id');
        $staf = $this->db->get_where('dosen', ['id' => $staf_id, 'level' => '5'])->row();
        echo "<pre>";
        print_r($staf);
        echo "</pre>";
        
        echo "<h3>3. Prodi List:</h3>";
        $prodi = $this->db->get('prodi')->result();
        echo "<pre>";
        print_r($prodi);
        echo "</pre>";
    }
}

/* End of file Profil.php */
/* Location: ./application/controllers/staf/Profil.php */