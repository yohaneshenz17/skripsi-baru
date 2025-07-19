<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Pastikan class ini extends ke controller dasar Anda, misalnya CI_Controller atau MY_Controller
class Proposal extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email'); // Memuat library email
        $this->load->helper('url');

        // Cek jika mahasiswa sudah login
        if ($this->session->userdata('level') != '3') {
            redirect('auth/login');
        }
    }

    /**
     * Menampilkan halaman utama proposal (form pengajuan atau status)
     */
    public function index()
    {
        $data['title'] = 'Usulan Proposal Skripsi';
        $mahasiswa_id = $this->session->userdata('id');

        // Mengambil data proposal mahasiswa yang sedang login beserta nama pembimbing & penguji
        $this->db->select('
            p.*, 
            d1.nama as nama_pembimbing,
            dp1.nama as nama_penguji1,
            dp2.nama as nama_penguji2
        ');
        $this->db->from('proposal_mahasiswa p');
        $this->db->join('dosen d1', 'p.dosen_id = d1.id', 'left');
        $this->db->join('dosen dp1', 'p.dosen_penguji_id = dp1.id', 'left');
        $this->db->join('dosen dp2', 'p.dosen_penguji2_id = dp2.id', 'left');
        $this->db->where('p.mahasiswa_id', $mahasiswa_id);
        $this->db->where('p.status !=', '2'); // Tampilkan proposal yang tidak ditolak
        $data['proposal'] = $this->db->get()->row();

        // =================================================================
        // PERBAIKAN DIMULAI DARI SINI
        // =================================================================

        // 1. Muat view konten (proposal.php) dan simpan sebagai teks ke dalam variabel $data['content']
        $data['content'] = $this->load->view('mahasiswa/proposal', $data, TRUE);

        // 2. Siapkan variabel script (kosongkan jika tidak ada script khusus untuk halaman ini)
        $data['script'] = '';

        // 3. Muat template utama dan kirim semua data yang sudah disiapkan
        $this->load->view('template/mahasiswa', $data);
    }

    /**
     * Proses saat mahasiswa mengirimkan form pengajuan proposal
     */
    public function ajukan()
    {
        if ($this->input->post()) {
            $mahasiswa_id = $this->session->userdata('id');

            // Cek apakah sudah pernah mengajukan proposal yang aktif
            $existing = $this->db->get_where('proposal_mahasiswa', [
                'mahasiswa_id' => $mahasiswa_id,
                'status !=' => '2' // Status bukan ditolak
            ])->row();

            if ($existing) {
                $this->session->set_flashdata('error', 'Anda sudah memiliki proposal yang sedang diproses!');
                redirect('mahasiswa/proposal');
            }

            $data = array(
                'mahasiswa_id' => $mahasiswa_id,
                'judul' => $this->input->post('judul'),
                'ringkasan' => $this->input->post('ringkasan'),
                'status' => '0', // Status: Menunggu penetapan kaprodi
                'dosen_id' => NULL,
                'dosen_penguji_id' => NULL,
                'dosen_penguji2_id' => NULL,
                'penetapan_oleh' => NULL,
                'tanggal_penetapan' => NULL,
                'dosen2_id' => 1, // Nilai default jika ada
            );

            if ($this->db->insert('proposal_mahasiswa', $data)) {
                $this->_notify_kaprodi($mahasiswa_id, $this->input->post('judul'));
                $this->session->set_flashdata('success', 'Proposal berhasil diajukan! Silahkan tunggu penetapan pembimbing dari Kaprodi.');
            } else {
                $this->session->set_flashdata('error', 'Gagal mengajukan proposal!');
            }
            redirect('mahasiswa/proposal');
        }
    }

    /**
     * Fungsi private untuk mengirim notifikasi email ke Kaprodi
     */
    private function _notify_kaprodi($mahasiswa_id, $judul_proposal)
    {
        // Ambil data mahasiswa
        $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
        if (!$mahasiswa) return; // Keluar jika data mahasiswa tidak ada

        // Ambil data Kaprodi berdasarkan prodi mahasiswa
        $this->db->select('d.nama, d.email');
        $this->db->from('dosen d');
        $this->db->join('prodi p', 'd.id = p.dosen_id');
        $this->db->where('p.id', $mahasiswa->prodi_id);
        $this->db->where('d.level', '4');
        $kaprodi = $this->db->get()->row();

        if ($kaprodi && !empty($kaprodi->email)) {
            // Ambil konfigurasi email dari database
            $config_email = $this->db->get('email_sender')->row();
            if (!$config_email) return; // Keluar jika config email tidak ada

            $config = array(
                'protocol' => 'smtp',
                'smtp_host' => $config_email->smtp_host,
                'smtp_port' => $config_email->smtp_port,
                'smtp_user' => $config_email->email,
                'smtp_pass' => $config_email->password,
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n" // Penting untuk beberapa server
            );

            $this->email->initialize($config);

            $this->email->from($config_email->email, 'Sistem Informasi Skripsi STK');
            $this->email->to($kaprodi->email);
            $this->email->subject('Pengajuan Proposal Baru: ' . $mahasiswa->nama);

            $message = "
            <h3>Pengajuan Proposal Skripsi Baru</h3>
            <p>Yth. Bpk/Ibu {$kaprodi->nama},</p>
            <p>Terdapat pengajuan proposal baru dari mahasiswa yang memerlukan penetapan pembimbing dan penguji. Berikut rinciannya:</p>
            <table border='0' cellpadding='5'>
                <tr><td style='width:120px;'>NIM</td><td>: {$mahasiswa->nim}</td></tr>
                <tr><td>Nama Mahasiswa</td><td>: {$mahasiswa->nama}</td></tr>
                <tr><td>Judul Proposal</td><td>: {$judul_proposal}</td></tr>
            </table>
            <p>Silakan login ke sistem untuk meninjau dan melakukan penetapan.</p>
            <p><a href='" . base_url('auth/login') . "' style='padding:10px 15px; background-color:#007bff; color:white; text-decoration:none; border-radius:5px;'>Login ke Sistem</a></p>
            <br>
            <p>Terima kasih.</p>
            ";

            $this->email->message($message);
            $this->email->send();
        }
    }
}