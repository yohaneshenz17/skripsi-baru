<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Prevent caching untuk menghindari session issues
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
    }

    public function index() {
        redirect('auth/login');
    }

    public function login() {
        // PERBAIKAN: Clear semua flashdata saat masuk halaman login
        $this->_force_clear_all_flashdata();
        
        // Cek jika sudah login
        if($this->session->userdata('logged_in')) {
            $level = $this->session->userdata('level');
            if($level == '1') redirect('admin/dashboard');
            if($level == '2') redirect('dosen/dashboard');
            if($level == '3') redirect('mahasiswa/dashboard');
            if($level == '4') redirect('kaprodi/dashboard');
        }
        
        // Load view login
        $this->load->view('auth/login');
    }

    public function cek_login() {
        // Ambil input
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        // PERBAIKAN: Force clear flashdata sebelum proses apapun
        $this->_force_clear_all_flashdata();

        // Validasi input
        if (empty($email) || empty($password)) {
            $this->_set_single_flash_message('error', 'Email dan Password wajib diisi!');
            redirect('auth/login');
            return;
        }

        // Cek ke tabel dosen
        $user_dosen = $this->db->get_where('dosen', ['email' => $email])->row();
        if ($user_dosen) {
            if ($password == $user_dosen->nip) {
                // PERBAIKAN: Clear flashdata sebelum login berhasil
                $this->_force_clear_all_flashdata();
                
                // Login berhasil - TIDAK set flash message untuk sukses
                $session_data = [
                    'id'        => $user_dosen->id,
                    'nama'      => $user_dosen->nama,
                    'email'     => $user_dosen->email,
                    'foto'      => $user_dosen->foto,
                    'level'     => $user_dosen->level,
                    'logged_in' => TRUE
                ];
                $this->session->set_userdata($session_data);

                // Set prodi_id untuk kaprodi
                if ($user_dosen->level == '4') {
                    $prodi = $this->db->get_where('prodi', ['dosen_id' => $user_dosen->id])->row();
                    if ($prodi) {
                        $this->session->set_userdata('prodi_id', $prodi->id);
                    }
                }
                
                // Redirect berdasarkan level - TANPA flash message
                if ($user_dosen->level == '1') {
                    redirect('admin/dashboard');
                } elseif ($user_dosen->level == '2') {
                    redirect('dosen/dashboard');
                } elseif ($user_dosen->level == '4') {
                    redirect('kaprodi/dashboard');
                }
                return;
            }
        }

        // Cek ke tabel mahasiswa
        $user_mahasiswa = $this->db->get_where('mahasiswa', ['email' => $email])->row();
        if ($user_mahasiswa) {
            if (password_verify($password, $user_mahasiswa->password)) {
                // PERBAIKAN: Clear flashdata sebelum login berhasil
                $this->_force_clear_all_flashdata();
                
                // Login berhasil - TIDAK set flash message untuk sukses
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

        // HANYA set flash message SEKALI untuk error login
        $this->_set_single_flash_message('error', 'Email atau Password salah!');
        redirect('auth/login');
    }

    public function logout() {
        // PERBAIKAN: Force clear flashdata sebelum logout
        $this->_force_clear_all_flashdata();
        
        // Destroy session
        $this->session->sess_destroy();
        
        // JANGAN set flash message untuk logout untuk mencegah masalah
        // $this->session->set_flashdata('info', 'Anda telah berhasil logout.');
        
        redirect('auth/login');
    }

    /**
     * PERBAIKAN: Helper function untuk FORCE clear semua flashdata
     * Method yang lebih agresif untuk membersihkan flash messages
     */
    private function _force_clear_all_flashdata() {
        // Method 1: Unset manual dari session userdata
        $session_data = $this->session->all_userdata();
        foreach($session_data as $key => $value) {
            if (strpos($key, '__ci_flash') !== false || 
                strpos($key, 'flash:') === 0 ||
                in_array($key, ['success', 'error', 'warning', 'info'])) {
                $this->session->unset_userdata($key);
            }
        }
        
        // Method 2: Set flashdata kosong untuk override yang lama
        $this->session->set_flashdata('success', '');
        $this->session->set_flashdata('error', '');
        $this->session->set_flashdata('warning', '');
        $this->session->set_flashdata('info', '');
        
        // Method 3: Unset flash container
        $this->session->unset_userdata('__ci_flash');
        
        // Method 4: Manual clear dari $_SESSION jika available
        if (isset($_SESSION)) {
            foreach($_SESSION as $key => $value) {
                if (strpos($key, '__ci_flash') !== false || 
                    strpos($key, 'flash:') === 0) {
                    unset($_SESSION[$key]);
                }
            }
        }
    }

    /**
     * PERBAIKAN: Helper function untuk set SINGLE flash message
     * Mencegah duplicate flash messages
     */
    private function _set_single_flash_message($type, $message) {
        // Force clear dulu
        $this->_force_clear_all_flashdata();
        
        // Set new flash message
        $this->session->set_flashdata($type, $message);
        
        // Log untuk debugging
        log_message('debug', "Flash message set: {$type} = {$message}");
    }

    /**
     * Method untuk handle forgot password (jika ada)
     */
    public function forgot_password() {
        if ($this->input->post()) {
            $email = $this->input->post('email');
            
            // Clear flashdata dulu
            $this->_force_clear_all_flashdata();
            
            // Validasi email
            if (empty($email)) {
                $this->_set_single_flash_message('error', 'Email wajib diisi!');
                redirect('auth/forgot_password');
                return;
            }
            
            // Cek email di database
            $user = $this->db->get_where('dosen', ['email' => $email])->row();
            if (!$user) {
                $user = $this->db->get_where('mahasiswa', ['email' => $email])->row();
            }
            
            if ($user) {
                // Process forgot password logic here
                $this->_set_single_flash_message('success', 'Link reset password telah dikirim ke email Anda.');
            } else {
                $this->_set_single_flash_message('error', 'Email tidak ditemukan di sistem.');
            }
            
            redirect('auth/forgot_password');
        }
        
        $this->load->view('auth/forgot_password');
    }

    /**
     * Method untuk handle API login (jika ada)
     */
    public function api_login() {
        // Set JSON header
        $this->output->set_content_type('application/json');
        
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        if (empty($email) || empty($password)) {
            $this->output->set_output(json_encode([
                'error' => true,
                'message' => 'Email dan password wajib diisi'
            ]));
            return;
        }
        
        // Check dosen
        $user_dosen = $this->db->get_where('dosen', ['email' => $email])->row();
        if ($user_dosen && $password == $user_dosen->nip) {
            $this->output->set_output(json_encode([
                'error' => false,
                'message' => 'Login berhasil',
                'data' => [
                    'id' => $user_dosen->id,
                    'nama' => $user_dosen->nama,
                    'email' => $user_dosen->email,
                    'level' => $user_dosen->level
                ]
            ]));
            return;
        }
        
        // Check mahasiswa
        $user_mahasiswa = $this->db->get_where('mahasiswa', ['email' => $email])->row();
        if ($user_mahasiswa && password_verify($password, $user_mahasiswa->password)) {
            $this->output->set_output(json_encode([
                'error' => false,
                'message' => 'Login berhasil',
                'data' => [
                    'id' => $user_mahasiswa->id,
                    'nama' => $user_mahasiswa->nama,
                    'email' => $user_mahasiswa->email,
                    'level' => '3'
                ]
            ]));
            return;
        }
        
        // Login failed
        $this->output->set_output(json_encode([
            'error' => true,
            'message' => 'Email atau password salah'
        ]));
    }

    /**
     * PERBAIKAN: Method untuk clear session secara manual (untuk debugging)
     */
    public function clear_session() {
        if (ENVIRONMENT === 'development') {
            $this->_force_clear_all_flashdata();
            $this->session->sess_destroy();
            echo "Session cleared successfully!";
        } else {
            show_404();
        }
    }
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */