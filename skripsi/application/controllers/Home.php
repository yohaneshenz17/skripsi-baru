<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');
        $this->load->database();
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        $template = $this->db->get('home_template')->row();
        $data = (array) $template;
        $this->load->view('home/index', $data);
    }

    public function pengumuman()
    {
        $data['title'] = 'Pengumuman Tahapan Skripsi';
        
        // Ambil data pengumuman yang aktif
        $this->db->where('aktif', '1');
        $this->db->order_by('no', 'ASC');
        $data['pengumuman'] = $this->db->get('pengumuman_tahapan')->result();
        
        $this->load->view('home/pengumuman', $data);
    }

    public function registrasi()
    {
        $data['prodi'] = $this->db->get('prodi')->result();
        $data['old_input'] = $this->session->flashdata('old_input');
        return $this->load->view('home/registrasi', $data);
    }

    public function proses_registrasi()
    {
        $input_data = $this->input->post();
        $this->session->set_flashdata('old_input', $input_data);
    
        // --- VALIDASI DATA (existing code) ---
        if ($input_data['nomor_telepon'] == $input_data['nomor_telepon_orang_dekat']) {
            $this->session->set_flashdata('error', 'Nomor HP Pribadi dan Nomor HP Orang Dekat tidak boleh sama.');
            redirect('home/registrasi');
            return;
        }
    
        if ($input_data['password'] != $input_data['password_konfirmasi']) {
            $this->session->set_flashdata('error', 'Password dan Konfirmasi Password tidak cocok.');
            redirect('home/registrasi');
            return;
        }
    
        $cek_nim = $this->db->get_where('mahasiswa', ['nim' => $input_data['nim']])->num_rows();
        if ($cek_nim > 0) {
            $this->session->set_flashdata('error', 'NIM sudah terdaftar.');
            redirect('home/registrasi');
            return;
        }
        
        $cek_email = $this->db->get_where('mahasiswa', ['email' => $input_data['email']])->num_rows();
        if ($cek_email > 0) {
            $this->session->set_flashdata('error', 'Email sudah terdaftar.');
            redirect('home/registrasi');
            return;
        }
    
        // --- PERSIAPAN DATA UNTUK DATABASE (existing code) ---
        $data_to_save = [
            'nim' => $input_data['nim'],
            'nama' => $input_data['nama'],
            'prodi_id' => $input_data['prodi_id'],
            'email' => $input_data['email'],
            'password' => password_hash($input_data['password'], PASSWORD_DEFAULT),
            'nomor_telepon' => $input_data['nomor_telepon'],
            'nomor_telepon_orang_dekat' => $input_data['nomor_telepon_orang_dekat'],
            'jenis_kelamin' => $input_data['jenis_kelamin'],
            'tempat_lahir' => $input_data['tempat_lahir'],
            'tanggal_lahir' => date('Y-m-d', strtotime(str_replace('/', '-', $input_data['tanggal_lahir']))),
            'alamat' => $input_data['alamat'],
            'ipk' => $input_data['ipk'],
            'status' => '1'
        ];
    
        // Handle upload foto (existing code)
        if (!empty($input_data['foto'])) {
            $img_data = $input_data['foto'];
            list($type, $img_data) = explode(';', $img_data);
            list(, $img_data) = explode(',', $img_data);
            $decoded_image = base64_decode($img_data);
            $filename = uniqid() . '.jpg';
            $filepath = './cdn/img/mahasiswa/' . $filename;
            
            if (file_put_contents($filepath, $decoded_image)) {
                $data_to_save['foto'] = $filename;
            }
        }
    
        // --- PERBAIKAN: SIMPAN DATA DAN KIRIM NOTIFIKASI ---
        $insert_result = $this->db->insert('mahasiswa', $data_to_save);
        
        if ($insert_result) {
            $mahasiswa_id = $this->db->insert_id();
            
            // PERBAIKAN 1: Log activity untuk debug
            log_message('info', 'Registrasi berhasil untuk mahasiswa ID: ' . $mahasiswa_id . ', Email: ' . $input_data['email']);
            
            // PERBAIKAN 2: Kirim email notifikasi (dengan password plain text)
            $email_sent = $this->_kirim_email_registrasi_berhasil($mahasiswa_id, $input_data);
            
            // PERBAIKAN 3: Set flash message sukses
            if ($email_sent) {
                $this->session->set_flashdata('success', 
                    'Registrasi berhasil! Email konfirmasi telah dikirim ke ' . $input_data['email'] . 
                    '. Silakan login menggunakan email dan password yang telah Anda daftarkan.'
                );
                log_message('info', 'Email registrasi berhasil dikirim ke: ' . $input_data['email']);
            } else {
                $this->session->set_flashdata('success', 
                    'Registrasi berhasil! Namun email konfirmasi gagal dikirim. ' .
                    'Silakan login menggunakan email dan password yang telah Anda daftarkan.'
                );
                log_message('error', 'Registrasi berhasil tapi email gagal untuk: ' . $input_data['email']);
            }
            
            // Clear old input data karena registrasi sudah berhasil
            $this->session->unset_userdata('old_input');
            
            // PERBAIKAN KRITIS: Redirect ke halaman login jika berhasil
            redirect('auth/login');
            
        } else {
            $this->session->set_flashdata('error', 'Gagal melakukan registrasi. Silakan coba lagi.');
            log_message('error', 'Gagal insert data mahasiswa: ' . $this->db->last_query());
            
            // Error tetap redirect ke registrasi
            redirect('home/registrasi');
        }
    }
    
    /**
     * PERBAIKAN: Method baru untuk kirim email registrasi dengan password plain text
     */
    private function _kirim_email_registrasi_berhasil($mahasiswa_id, $input_data)
    {
        try {
            // Load library dan helper yang diperlukan
            $this->load->library('email');
            $this->load->helper('email_workflow');
            
            // Ambil data prodi untuk informasi lengkap
            $prodi = $this->db->get_where('prodi', ['id' => $input_data['prodi_id']])->row();
            $nama_prodi = $prodi ? $prodi->nama : 'Program Studi';
            
            // Setup email config (menggunakan config yang sudah terbukti bekerja)
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls',
                'smtp_timeout' => 30
            ];
            
            $this->email->initialize($config);
            $this->email->clear();
            
            // Setup email content
            $subject = '[SIM Tugas Akhir] Registrasi Akun Berhasil - STK Santo Yakobus';
            
            // Email template menggunakan format yang konsisten dengan sistem
            $message = template_email_header();
            $message .= "
            <tr>
                <td style='padding: 40px 30px;'>
                    <h2 style='color: #28a745; margin: 0 0 20px 0; font-size: 24px;'>
                        🎉 Registrasi Berhasil!
                    </h2>
                    
                    <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                        Yth. <strong>{$input_data['nama']}</strong>,
                    </p>
                    
                    <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                        Selamat! Akun Anda telah berhasil terdaftar di Sistem Informasi Manajemen Tugas Akhir 
                        STK Santo Yakobus Merauke.
                    </p>
                    
                    <div style='background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 20px; margin: 20px 0; border-radius: 4px;'>
                        <h3 style='color: #1976d2; margin: 0 0 15px 0; font-size: 18px;'>📋 Informasi Akun Anda:</h3>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; width: 120px;'>NIM:</td>
                                <td style='padding: 8px 0;'>{$input_data['nim']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Nama:</td>
                                <td style='padding: 8px 0;'>{$input_data['nama']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Prodi:</td>
                                <td style='padding: 8px 0;'>{$nama_prodi}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Email:</td>
                                <td style='padding: 8px 0;'>{$input_data['email']}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>🔐 Cara Login:</h4>
                        <ul style='color: #856404; margin: 0; padding-left: 20px;'>
                            <li><strong>Email:</strong> {$input_data['email']}</li>
                            <li><strong>Password:</strong> {$input_data['password']}</li>
                            <li><strong>URL Login:</strong> <a href='" . base_url('auth/login') . "'>" . base_url('auth/login') . "</a></li>
                        </ul>
                        <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 3px; margin-top: 15px;'>
                            <small style='color: #721c24;'>
                                <strong>⚠️ Penting:</strong> Simpan email ini dengan aman dan hapus setelah Anda berhasil login. 
                                Jangan bagikan informasi login Anda kepada orang lain.
                            </small>
                        </div>
                    </div>
                    
                    <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <h4 style='color: #0c5460; margin: 0 0 10px 0;'>📚 Langkah Selanjutnya:</h4>
                        <ol style='color: #0c5460; margin: 0; padding-left: 20px;'>
                            <li>Login ke sistem menggunakan data di atas</li>
                            <li>Lengkapi profil Anda jika diperlukan</li>
                            <li>Mulai proses pengajuan proposal tugas akhir</li>
                            <li>Ikuti workflow yang telah ditetapkan</li>
                        </ol>
                    </div>
                    
                    <p style='margin: 20px 0; text-align: center;'>
                        <a href='" . base_url('auth/login') . "' 
                           style='background-color: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                           🚀 Login Sekarang
                        </a>
                    </p>
                    
                    <p style='margin: 20px 0 0 0; font-size: 14px; color: #666; line-height: 1.5;'>
                        Jika Anda mengalami kesulitan dalam mengakses sistem, silakan hubungi admin 
                        atau bagian akademik STK Santo Yakobus Merauke.
                    </p>
                </td>
            </tr>";
            $message .= template_email_footer();
            
            // Send email
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($input_data['email']);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Gagal kirim email registrasi: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error dalam _kirim_email_registrasi_berhasil: ' . $e->getMessage());
            return false;
        }
    }

    public function cek()
    {
        return $this->load->view('home/cek');
    }
}