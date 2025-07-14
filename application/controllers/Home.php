<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        // Fungsi ini sudah benar, tidak perlu diubah.
        $template = $this->db->get('home_template')->row();
        $data = (array) $template;
        $this->load->view('home/index', $data);
    }

    public function registrasi()
    {
        // Fungsi ini untuk menampilkan halaman registrasi
        $data['prodi'] = $this->db->get('prodi')->result();
        return $this->load->view('home/registrasi', $data);
    }

    /**
     * [FUNGSI BARU] Untuk memproses data dari form registrasi
     */
    public function proses_registrasi()
    {
        // 1. Ambil semua data dari form
        $nim = $this->input->post('nim');
        $nama = $this->input->post('nama');
        $prodi_id = $this->input->post('prodi_id');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $nomor_telepon = $this->input->post('nomor_telepon');
        $nomor_telepon_orang_dekat = $this->input->post('nomor_telepon_orang_dekat');
        
        // 2. [PERBAIKAN] Validasi nomor telepon tidak boleh sama
        if ($nomor_telepon == $nomor_telepon_orang_dekat) {
            $this->session->set_flashdata('error', 'Nomor HP Pribadi dan Nomor HP Orang Dekat tidak boleh sama.');
            redirect('home/registrasi');
            return; // Hentikan proses
        }

        // 3. Cek apakah NIM atau Email sudah terdaftar
        $cek_nim = $this->db->get_where('mahasiswa', ['nim' => $nim])->num_rows();
        if ($cek_nim > 0) {
            $this->session->set_flashdata('error', 'NIM ' . $nim . ' sudah terdaftar.');
            redirect('home/registrasi');
            return;
        }

        $cek_email = $this->db->get_where('mahasiswa', ['email' => $email])->num_rows();
        if ($cek_email > 0) {
            $this->session->set_flashdata('error', 'Email ' . $email . ' sudah terdaftar.');
            redirect('home/registrasi');
            return;
        }

        // 4. Siapkan data untuk dimasukkan ke database
        $data_insert = array(
            'nim' => $nim,
            'nama' => $nama,
            'prodi_id' => $prodi_id,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT), // Enkripsi password
            'nomor_telepon' => $nomor_telepon,
            'nomor_telepon_orang_dekat' => $nomor_telepon_orang_dekat,
            'jenis_kelamin' => $this->input->post('jenis_kelamin'),
            'tempat_lahir' => $this->input->post('tempat_lahir'),
            'tanggal_lahir' => $this->input->post('tanggal_lahir'),
            'alamat' => $this->input->post('alamat'),
            'ipk' => $this->input->post('ipk'),
            'status' => '1' // Langsung aktif
        );

        // 5. Simpan ke database
        $insert = $this->db->insert('mahasiswa', $data_insert);

        if ($insert) {
            $this->session->set_flashdata('success', 'Registrasi berhasil! Silakan login dengan email dan password Anda.');
            redirect('auth/login');
        } else {
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat registrasi. Silakan coba lagi.');
            redirect('home/registrasi');
        }
    }

    // Fungsi lain di bawah ini tidak perlu diubah
    public function cek()
    {
        return $this->load->view('home/cek');
    }
    
    // ... (sisa fungsi home_template dan update_home_template Anda tetap di sini) ...

}

/* End of file Home.php */
// [PERBAIKAN] Kurung kurawal '}' ekstra di sini sudah dihapus.