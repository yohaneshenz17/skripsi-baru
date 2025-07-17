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

        // --- VALIDASI DATA ---
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

        // --- PERSIAPAN DATA UNTUK DATABASE ---
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

        if (!empty($input_data['foto'])) {
            $img_data = $input_data['foto'];
            list($type, $img_data) = explode(';', $img_data);
            list(, $img_data) = explode(',', $img_data);
            $decoded_image = base64_decode($img_data);
            $filename = uniqid() . '.jpg';
            $filepath = './cdn/img/mahasiswa/' . $filename;
            file_put_contents($filepath, $decoded_image);
            $data_to_save['foto'] = $filename;
        }

        // --- PROSES PENYIMPANAN & NOTIFIKASI ---
        if ($this->db->insert('mahasiswa', $data_to_save)) {
            $config_email = $this->db->get('email_sender')->row();
            if ($config_email) {
                $config = [
                    'protocol'  => 'smtp', 'smtp_host' => $config_email->smtp_host,
                    'smtp_port' => $config_email->smtp_port, 'smtp_user' => $config_email->email,
                    'smtp_pass' => $config_email->password, 'mailtype'  => 'html', 
                    'charset'   => 'utf-8', 'newline'   => "\r\n"
                ];
                $this->email->initialize($config);
                $this->email->from($config_email->email, 'Sistem Informasi Skripsi STK');
                $this->email->to($input_data['email']);
                $this->email->subject('Akun Anda Telah Berhasil Didaftarkan');

                $nama_mhs = htmlspecialchars($input_data['nama']);
                $email_mhs = htmlspecialchars($input_data['email']);
                $password_mhs = htmlspecialchars($input_data['password']);
                $login_link = base_url('auth/login');

                $message = <<<HTML
                    <h3>Selamat Datang, {$nama_mhs}!</h3>
                    <p>Pendaftaran akun Anda pada Sistem Informasi Manajemen Tugas Akhir STK St. Yakobus Merauke telah berhasil.</p>
                    <p>Gunakan rincian berikut untuk login ke sistem:</p>
                    <ul style="list-style-type: none; padding: 0;">
                        <li style="margin-bottom: 5px;"><b>Username:</b> {$email_mhs}</li>
                        <li><b>Password:</b> {$password_mhs}</li>
                    </ul>
                    <p>Silakan login melalui tautan di bawah ini.</p>
                    <p><a href="{$login_link}" style="display: inline-block; padding: 10px 18px; background-color: #5e72e4; color: white; text-decoration: none; border-radius: 5px;">Login ke Sistem</a></p>
                    <br>
                    <p>Terima kasih.</p>
                HTML;
                
                $this->email->message($message);
                $this->email->send();
            }
            
            $this->session->set_flashdata('error', null);
            $this->session->set_flashdata('old_input', null);
            $this->session->set_flashdata('success', 'Registrasi berhasil! Silakan cek email Anda untuk detail akun dan link login.');
            redirect('auth/login');
        } 
    }

    public function cek()
    {
        return $this->load->view('home/cek');
    }
}