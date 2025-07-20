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
     * IMPLEMENTASI LENGKAP: Method untuk handle forgot password
     */
    public function forgot_password() {
        // Redirect jika sudah login
        if($this->session->userdata('logged_in')) {
            $level = $this->session->userdata('level');
            if($level == '1') redirect('admin/dashboard');
            if($level == '2') redirect('dosen/dashboard');
            if($level == '3') redirect('mahasiswa/dashboard');
            if($level == '4') redirect('kaprodi/dashboard');
        }
        
        if ($this->input->post()) {
            $email = trim($this->input->post('email'));
            
            // Clear flashdata dulu (menggunakan method yang sudah ada)
            $this->_force_clear_all_flashdata();
            
            // Validasi email
            if (empty($email)) {
                $this->_set_single_flash_message('error', 'Email wajib diisi!');
                redirect('auth/forgot_password');
                return;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->_set_single_flash_message('error', 'Format email tidak valid!');
                redirect('auth/forgot_password');
                return;
            }
            
            // Cek email di database (sama seperti method cek_login)
            $user_dosen = $this->db->get_where('dosen', ['email' => $email])->row();
            $user_mahasiswa = null;
            
            if (!$user_dosen) {
                $user_mahasiswa = $this->db->get_where('mahasiswa', ['email' => $email])->row();
            }
            
            if ($user_dosen || $user_mahasiswa) {
                // Kirim email dengan informasi login
                $email_sent = $this->_kirim_email_forgot_password($user_dosen ?: $user_mahasiswa, $user_dosen ? 'dosen' : 'mahasiswa');
                
                if ($email_sent) {
                    $this->_set_single_flash_message('success', 
                        'Informasi login telah dikirim ke email ' . $email . '. Silakan periksa inbox atau folder spam Anda.'
                    );
                    log_message('info', 'Email forgot password berhasil dikirim ke: ' . $email);
                } else {
                    $this->_set_single_flash_message('error', 
                        'Terjadi kesalahan saat mengirim email. Silakan hubungi administrator atau coba lagi nanti.'
                    );
                    log_message('error', 'Gagal mengirim email forgot password ke: ' . $email);
                }
            } else {
                // Email tidak ditemukan - tetap kirim pesan sukses untuk keamanan
                $this->_set_single_flash_message('success', 
                    'Jika email ' . $email . ' terdaftar di sistem, informasi login akan dikirim ke email tersebut.'
                );
                log_message('info', 'Percobaan forgot password untuk email tidak terdaftar: ' . $email);
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

    // ==================================================================================
    // HELPER METHODS EXISTING (SUDAH ADA DI SISTEM ANDA)
    // ==================================================================================

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

    // ==================================================================================
    // IMPLEMENTASI BARU: HELPER METHODS UNTUK FORGOT PASSWORD
    // ==================================================================================

    /**
     * BARU: Method untuk kirim email forgot password
     * Menggunakan konfigurasi email yang sudah WORKING di config/email.php
     */
    private function _kirim_email_forgot_password($user, $user_type)
    {
        try {
            // Load library email
            $this->load->library('email');
            
            // GUNAKAN konfigurasi email yang sudah WORKING dari config/email.php
            // JANGAN ubah konfigurasi yang sudah TESTED & WORKING!
            
            // Tentukan password berdasarkan jenis user (sama seperti login)
            if ($user_type == 'dosen') {
                $password_info = $user->nip; // Dosen menggunakan NIP sebagai password
                $role_name = $this->_get_role_name($user->level);
            } else {
                $password_info = "Password yang Anda gunakan saat registrasi"; // Mahasiswa
                $role_name = "Mahasiswa";
            }
            
            // Format email menggunakan config yang sudah ada
            $this->email->from($this->config->item('email_from'), $this->config->item('email_from_name'));
            $this->email->to($user->email);
            $this->email->subject('[SIM-TA] Informasi Login Anda');
            
            // Template email
            $email_message = $this->_create_forgot_password_template($user, $password_info, $role_name, $user_type);
            $this->email->message($email_message);
            
            // Kirim email
            if ($this->email->send()) {
                return true;
            } else {
                log_message('error', 'Email send error: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exception in _kirim_email_forgot_password: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * BARU: Method untuk template email forgot password
     */
    private function _create_forgot_password_template($user, $password_info, $role_name, $user_type)
    {
        $login_url = base_url('auth/login');
        $current_date = date('d F Y H:i:s');
        
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
                .info-box { background-color: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0; }
                .warning-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                .btn { display: inline-block; padding: 12px 24px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 15px 0; }
                .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔐 Informasi Login Anda</h1>
                    <p>Sistem Informasi Manajemen Tugas Akhir<br>STK Santo Yakobus Merauke</p>
                </div>
                <div class="content">
                    <h2>Halo, ' . htmlspecialchars($user->nama) . '</h2>
                    <p>Anda telah meminta informasi login untuk akun <strong>' . $role_name . '</strong> di Sistem SIM-TA STK Santo Yakobus.</p>
                    
                    <div class="info-box">
                        <h3>📧 Informasi Login Anda:</h3>
                        <p><strong>Email/Username:</strong> ' . htmlspecialchars($user->email) . '</p>';
        
        if ($user_type == 'dosen') {
            $template .= '<p><strong>Password:</strong> ' . htmlspecialchars($password_info) . '</p>
                         <p><em>Catatan: Password Anda adalah NIP yang terdaftar di sistem</em></p>';
        } else {
            $template .= '<p><strong>Password:</strong> ' . $password_info . '</p>
                         <p><em>Catatan: Gunakan password yang Anda buat saat registrasi pertama kali</em></p>';
        }
        
        $template .= '
                    </div>
                    
                    <div class="warning-box">
                        <p><strong>⚠️ Penting untuk Keamanan:</strong></p>
                        <ul>
                            <li>Jangan bagikan informasi login Anda kepada orang lain</li>
                            <li>Pastikan logout setelah selesai menggunakan sistem</li>
                            <li>Jika Anda tidak meminta reset password ini, segera hubungi administrator</li>
                        </ul>
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="' . $login_url . '" class="btn">🚀 Login Sekarang</a>
                    </div>
                    
                    <p>Link login: <a href="' . $login_url . '">' . $login_url . '</a></p>
                    
                    <hr style="margin: 30px 0;">
                    <p><strong>Butuh Bantuan?</strong></p>
                    <p>Jika Anda mengalami kesulitan login atau memiliki pertanyaan, silakan hubungi:</p>
                    <ul>
                        <li>📧 Email: sipd@stkyakobus.ac.id</li>
                        <li>📞 Telepon: 09713330264</li>
                        <li>🏢 Kantor: Unit SIPD STK Santo Yakobus Merauke</li>
                    </ul>
                </div>
                <div class="footer">
                    <p>Email ini dikirim secara otomatis pada ' . $current_date . '</p>
                    <p>Sistem Informasi Manajemen Tugas Akhir - STK Santo Yakobus Merauke</p>
                    <p>Jl. Missi 2, Mandala, Merauke, Papua Selatan</p>
                    <p>Jangan membalas email ini karena dikirim secara otomatis</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }

    /**
     * BARU: Helper untuk role name
     */
    private function _get_role_name($level)
    {
        switch ($level) {
            case '1':
                return 'Administrator';
            case '2':
                return 'Dosen';
            case '4':
                return 'Kepala Program Studi';
            default:
                return 'User';
        }
    }
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */