<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database(); // Memuat database
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function index() {
        redirect('auth/login');
    }

    public function login() {
        // Cek jika sudah login, langsung redirect
        if($this->session->userdata('logged_in')) {
            $level = $this->session->userdata('level');
            if($level == '1') redirect('admin/dashboard');
            if($level == '2') redirect('dosen/dashboard');
            if($level == '3') redirect('mahasiswa/dashboard');
            if($level == '4') redirect('kaprodi/dashboard');
        }
        
        $this->load->view('auth/login');
    }

    /**
     * [FUNGSI BARU] Untuk memproses login dari form
     */
    public function cek_login() {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        // Cek ke tabel dosen terlebih dahulu
        $user_dosen = $this->db->get_where('dosen', ['email' => $email])->row();
        if ($user_dosen) {
            // Untuk dosen, passwordnya adalah NIP
            if ($password == $user_dosen->nip) {
                $session_data = array(
                    'id'        => $user_dosen->id,
                    'nama'      => $user_dosen->nama,
                    'level'     => $user_dosen->level,
                    'logged_in' => TRUE
                );
                $this->session->set_userdata($session_data);

                // [PERBAIKAN] Jika yang login adalah Kaprodi, simpan prodi_id ke session
                if ($user_dosen->level == '4') {
                    $prodi = $this->db->get_where('prodi', ['dosen_id' => $user_dosen->id])->row();
                    if ($prodi) {
                        $this->session->set_userdata('prodi_id', $prodi->id);
                    }
                }
                
                // Redirect berdasarkan level
                if ($user_dosen->level == '1') redirect('admin/dashboard');
                if ($user_dosen->level == '2') redirect('dosen/dashboard');
                if ($user_dosen->level == '4') redirect('kaprodi/dashboard');
            }
        }

        // Cek ke tabel mahasiswa jika tidak ditemukan di dosen
        $user_mahasiswa = $this->db->get_where('mahasiswa', ['email' => $email])->row();
        if ($user_mahasiswa) {
            // Untuk mahasiswa, verifikasi password hash
            if (password_verify($password, $user_mahasiswa->password)) {
                $session_data = array(
                    'id'        => $user_mahasiswa->id,
                    'nama'      => $user_mahasiswa->nama,
                    'level'     => '3', // Level mahasiswa
                    'logged_in' => TRUE
                );
                $this->session->set_userdata($session_data);
                redirect('mahasiswa/dashboard');
            }
        }

        // Jika semua pengecekan gagal
        $this->session->set_flashdata('error', 'Email atau Password salah!');
        redirect('auth/login');
    }

    /**
     * [FUNGSI LAMA] Tetap ada untuk login via link (jika masih dipakai)
     */
    public function cek($id = null, $level = null) {
        if($id && $level) {
            $session_data = [
                'id' => $id,
                'level' => $level,
                'logged_in' => true
            ];
             $this->session->set_userdata($session_data);

            // [PERBAIKAN] Ditambahkan juga di sini untuk konsistensi
            if ($level == '4') {
                $prodi = $this->db->get_where('prodi', ['dosen_id' => $id])->row();
                if ($prodi) {
                    $this->session->set_userdata('prodi_id', $prodi->id);
                }
            }
            
            if($level == '1') redirect('admin/dashboard');
            if($level == '2') redirect('dosen/dashboard');
            if($level == '3') redirect('mahasiswa/dashboard');
            if($level == '4') redirect('kaprodi/dashboard');
        }
        
        redirect('auth/login');
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}