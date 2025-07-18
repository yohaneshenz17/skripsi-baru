<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    }

    public function login() {
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        $response = array();
        
        if(empty($email) || empty($password)) {
            $response['error'] = true;
            $response['message'] = 'Email dan password harus diisi!';
            echo json_encode($response);
            return;
        }
        
        // Cek di tabel dosen terlebih dahulu
        $dosen = $this->db->get_where('dosen', array('email' => $email))->row();
        
        if($dosen) {
            // Login sebagai dosen/admin
            // Untuk dosen, NIP digunakan sebagai password
            if($dosen->nip == $password) {
                $response['error'] = false;
                $response['message'] = 'Login berhasil!';
                $response['data'] = array(
                    'id' => $dosen->id,
                    'level' => $dosen->level, // 1 = admin, 2 = dosen
                    'type' => 'dosen'
                );
                
                // Set session
                $this->session->set_userdata('id', $dosen->id);
                $this->session->set_userdata('level', $dosen->level);
                $this->session->set_userdata('type', 'dosen');
                $this->session->set_userdata('logged_in', true);
            } else {
                $response['error'] = true;
                $response['message'] = 'Password salah!';
            }
        } else {
            // Cek di tabel mahasiswa
            $mahasiswa = $this->db->get_where('mahasiswa', array('email' => $email))->row();
            
            if($mahasiswa) {
                // Verifikasi password mahasiswa (menggunakan bcrypt)
                if(password_verify($password, $mahasiswa->password)) {
                    // Cek status mahasiswa
                    if($mahasiswa->status == '1') {
                        $response['error'] = false;
                        $response['message'] = 'Login berhasil!';
                        $response['data'] = array(
                            'id' => $mahasiswa->id,
                            'level' => 3, // Level 3 untuk mahasiswa
                            'type' => 'mahasiswa'
                        );
                        
                        // Set session
                        $this->session->set_userdata('id', $mahasiswa->id);
                        $this->session->set_userdata('level', '3');
                        $this->session->set_userdata('type', 'mahasiswa');
                        $this->session->set_userdata('logged_in', true);
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'Akun Anda belum aktif. Silahkan hubungi admin!';
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = 'Password salah!';
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'Email tidak terdaftar!';
            }
        }
        
        echo json_encode($response);
    }
    
    public function logout() {
        $this->session->sess_destroy();
        redirect(base_url('auth/login'));
    }
}