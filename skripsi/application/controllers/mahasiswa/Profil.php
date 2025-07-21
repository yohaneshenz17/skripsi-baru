<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        // Pastikan mahasiswa sudah login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // PERBAIKAN: Berdasarkan database, field untuk role tampaknya 'level' bukan 'role'  
        // Level 3 = mahasiswa (dari database dump)
        if (!$this->session->userdata('id') || empty($this->session->userdata('id'))) {
            redirect('auth/logout');
        }
    }

    public function index()
    {
        // Ambil data session untuk validasi
        $mahasiswa_id = $this->session->userdata('id');
        $mahasiswa_nama = $this->session->userdata('nama');
        
        // Validasi session data
        if (empty($mahasiswa_id)) {
            $this->session->sess_destroy();
            redirect('auth');
        }
        
        // Debug data (optional - hapus di production)
        log_message('debug', 'Profil mahasiswa diakses - ID: ' . $mahasiswa_id . ', Nama: ' . $mahasiswa_nama);
        
        // Load view dengan data session
        $data = [
            'mahasiswa_id' => $mahasiswa_id,
            'mahasiswa_nama' => $mahasiswa_nama
        ];
        
        return $this->load->view('mahasiswa/profil', $data);
    }
    
    // Method tambahan untuk debug (hapus di production)
    public function debug()
    {
        echo '<pre>';
        echo 'Session Data: ';
        print_r($this->session->userdata());
        echo '</pre>';
    }
}

/* End of file Profil.php */
/* Location: ./application/controllers/mahasiswa/Profil.php */