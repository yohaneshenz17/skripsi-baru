<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
    }

    public function index() {
        redirect('auth/login');
    }

    public function login() {
        // Cek jika sudah login
        if($this->session->userdata('logged_in')) {
            $level = $this->session->userdata('level');
            if($level == '1') {
                redirect('admin/dashboard');
            } else if($level == '2') {
                redirect('dosen/dashboard');
            } else if($level == '3') {
                redirect('mahasiswa/dashboard');
            }
        }
        
        // Load login view
        $this->load->view('auth/login');
    }

    public function cek($id = null, $level = null) {
        if($id && $level) {
            // Set session jika dari link lama
            $this->session->set_userdata('id', $id);
            $this->session->set_userdata('level', $level);
            $this->session->set_userdata('logged_in', true);
            
            // Redirect berdasarkan level
            if($level == '1') {
                redirect('admin/dashboard');
            } else if($level == '2') {
                redirect('dosen/dashboard');
            } else if($level == '3') {
                redirect('mahasiswa/dashboard');
            }
        } else {
            redirect('auth/login');
        }
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}