<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function index() {
        redirect('auth/login');
    }

    public function login() {
        if($this->session->userdata('logged_in')) {
            $level = $this->session->userdata('level');
            if($level == '1') redirect('admin/dashboard');
            if($level == '2') redirect('dosen/dashboard');
            if($level == '3') redirect('mahasiswa/dashboard');
            if($level == '4') redirect('kaprodi/dashboard');
        }
        
        $this->load->view('auth/login');
    }

    public function cek_login() {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        // Cek ke tabel dosen
        $user_dosen = $this->db->get_where('dosen', ['email' => $email])->row();
        if ($user_dosen) {
            if ($password == $user_dosen->nip) {
                $session_data = [
                    'id'        => $user_dosen->id,
                    'nama'      => $user_dosen->nama,
                    'email'     => $user_dosen->email,
                    'level'     => $user_dosen->level,
                    'logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);

                if ($user_dosen->level == '4') {
                    $prodi = $this->db->get_where('prodi', ['dosen_id' => $user_dosen->id])->row();
                    if ($prodi) {
                        $this->session->set_userdata('prodi_id', $prodi->id);
                    }
                }
                
                if ($user_dosen->level == '1') redirect('admin/dashboard');
                if ($user_dosen->level == '2') redirect('dosen/dashboard');
                if ($user_dosen->level == '4') redirect('kaprodi/dashboard');
                return;
            }
        }

        // Cek ke tabel mahasiswa
        $user_mahasiswa = $this->db->get_where('mahasiswa', ['email' => $email])->row();
        if ($user_mahasiswa) {
            if (password_verify($password, $user_mahasiswa->password)) {
                // Data sesi untuk mahasiswa sudah lengkap di sini
                $session_data = [
                    'id'        => $user_mahasiswa->id,
                    'nama'      => $user_mahasiswa->nama,
                    'email'     => $user_mahasiswa->email,
                    'foto'      => $user_mahasiswa->foto,
                    'level'     => '3',
                    'logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);
                redirect('mahasiswa/dashboard');
                return;
            }
        }

        $this->session->set_flashdata('error', 'Email atau Password salah!');
        redirect('auth/login');
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}