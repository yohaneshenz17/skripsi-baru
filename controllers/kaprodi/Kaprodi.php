<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kaprodi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');
        
        // Cek login dan level
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        // Get prodi_id kaprodi
        $this->prodi_id = $this->session->userdata('prodi_id');
        if (!$this->prodi_id) {
            // Coba ambil dari database jika tidak ada di session
            $kaprodi = $this->db->get_where('prodi', ['dosen_id' => $this->session->userdata('id')])->row();
            if ($kaprodi) {
                $this->session->set_userdata('prodi_id', $kaprodi->id);
                $this->prodi_id = $kaprodi->id;
            }
        }
    }

    public function index() {
        redirect('kaprodi/dashboard');
    }

    public function dashboard() {
        $data['title'] = 'Dashboard Kaprodi';
        
        // Statistik proposal berdasarkan status kaprodi
        $data['proposal_menunggu_review'] = $this->db->from('proposal_mahasiswa')
            ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
            ->where('proposal_mahasiswa.status_kaprodi', '0')
            ->where('mahasiswa.prodi_id', $this->prodi_id)
            ->count_all_results();
            
        $data['proposal_disetujui_kaprodi'] = $this->db->from('proposal_mahasiswa')
            ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
            ->where('proposal_mahasiswa.status_kaprodi', '1')
            ->where('mahasiswa.prodi_id', $this->prodi_id)
            ->count_all_results();
            
        $data['proposal_menunggu_pembimbing'] = $this->db->from('proposal_mahasiswa')
            ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
            ->where('proposal_mahasiswa.status_kaprodi', '1')
            ->where('proposal_mahasiswa.status_pembimbing', '0')
            ->where('mahasiswa.prodi_id', $this->prodi_id)
            ->count_all_results();
            
        // Statistik total mahasiswa dan dosen
        $data['total_mahasiswa'] = $this->db->where('prodi_id', $this->prodi_id)
            ->count_all_results('mahasiswa');
            
        $data['total_dosen'] = $this->db->where('level !=', '1')
            ->count_all_results('dosen');
        
        $this->load->view('kaprodi/dashboard', $data);
    }

    public function proposal() {
        $data['title'] = 'Review Proposal Mahasiswa';
        
        // Ambil semua proposal dari mahasiswa prodi ini
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa,
            mahasiswa.email as email_mahasiswa,
            d1.nama as nama_pembimbing
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('dosen d1', 'proposal_mahasiswa.dosen_id = d1.id', 'left');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/proposal', $data);
    }

    public function review_proposal($proposal_id) {
        $data['title'] = 'Review Detail Proposal';
        
        // Ambil detail proposal dengan semua field termasuk file_draft_proposal
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa, 
            mahasiswa.email as email_mahasiswa,
            prodi.nama as nama_prodi
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if(!$data['proposal']) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan!');
            redirect('kaprodi/proposal');
        }
        
        // PERBAIKAN: Ambil SEMUA dosen dari SEMUA prodi sebagai calon pembimbing
        // Kaprodi bisa memilih pembimbing dari dosen manapun
        $this->db->select('dosen.*, prodi.nama as nama_prodi');
        $this->db->from('dosen');
        $this->db->join('prodi', 'dosen.prodi_id = prodi.id', 'left');
        $this->db->where('dosen.level', '2'); // Exclude admin saja
        $this->db->where('dosen.id !=', $this->session->userdata('id')); // Exclude kaprodi yang sedang login
        $this->db->order_by('dosen.nama', 'ASC');
        $data['dosens'] = $this->db->get()->result();
        
        // Debug: Log jumlah dosen
        log_message('debug', 'Jumlah dosen tersedia untuk pembimbing: ' . count($data['dosens']));
        
        $this->load->view('kaprodi/review_proposal', $data);
    }

    // Method untuk download file proposal
       public function download_proposal($proposal_id) {
        // Validasi proposal
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            show_404();
        }
        
        // PERBAIKAN: Gunakan field yang benar dan path yang benar
        if (empty($proposal->file_draft_proposal)) {
            $this->session->set_flashdata('error', 'File proposal tidak tersedia!');
            redirect('kaprodi/review_proposal/' . $proposal_id);
        }
        
        // PERBAIKAN PATH: Gunakan cdn/proposals/ seperti mahasiswa
        $file_path = FCPATH . 'cdn/proposals/' . $proposal->file_draft_proposal;
        
        // Cek apakah file ada
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File proposal tidak ditemukan di server!');
            redirect('kaprodi/review_proposal/' . $proposal_id);
        }
        
        // Download file
        $this->load->helper('download');
        $file_name = 'Proposal_' . str_replace(' ', '_', $proposal->nama_mahasiswa) . '_' . date('Y-m-d') . '.' . pathinfo($proposal->file_draft_proposal, PATHINFO_EXTENSION);
        
        force_download($file_name, file_get_contents($file_path));
    }
    
    // Method untuk view file proposal di browser
       public function view_proposal($proposal_id) {
        // Validasi proposal
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            show_404();
        }
        
        // PERBAIKAN: Gunakan field yang benar
        if (empty($proposal->file_draft_proposal)) {
            echo '<div style="padding: 20px; font-family: Arial;"><h3>File tidak tersedia</h3><p>File proposal tidak ada atau belum diupload.</p></div>';
            return;
        }
        
        // PERBAIKAN PATH: Gunakan cdn/proposals/ seperti mahasiswa
        $file_path = FCPATH . 'cdn/proposals/' . $proposal->file_draft_proposal;
        
        // Cek apakah file ada
        if (!file_exists($file_path)) {
            echo '<div style="padding: 20px; font-family: Arial;"><h3>File tidak ditemukan</h3><p>File proposal tidak ditemukan di server.</p></div>';
            return;
        }
        
        // Set header untuk menampilkan file
        $file_info = pathinfo($file_path);
        $extension = strtolower($file_info['extension']);
        
        switch($extension) {
            case 'pdf':
                header('Content-Type: application/pdf');
                break;
            case 'doc':
                header('Content-Type: application/msword');
                break;
            case 'docx':
                header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                break;
            default:
                header('Content-Type: application/octet-stream');
        }
        
        header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
    }

    public function proses_review() {
        $proposal_id = $this->input->post('proposal_id');
        $aksi = $this->input->post('aksi'); // 'setujui' atau 'tolak'
        $komentar = $this->input->post('komentar_kaprodi');
        $dosen_id = $this->input->post('dosen_id'); // hanya jika disetujui
        
        // Validasi proposal
        $proposal = $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa, mahasiswa.email as email_mahasiswa')
                            ->from('proposal_mahasiswa')
                            ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
                            ->where('proposal_mahasiswa.id', $proposal_id)
                            ->where('mahasiswa.prodi_id', $this->prodi_id)
                            ->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan!');
            redirect('kaprodi/proposal');
        }
        
        if ($aksi == 'setujui') {
            if (!$dosen_id) {
                $this->session->set_flashdata('error', 'Dosen pembimbing harus dipilih!');
                redirect('kaprodi/review_proposal/' . $proposal_id);
            }
            
            // Update proposal - disetujui kaprodi dan tetapkan pembimbing
            $data_update = [
                'status_kaprodi' => '1',
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'dosen_id' => $dosen_id,
                'penetapan_oleh' => $this->session->userdata('id'),
                'tanggal_penetapan' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $proposal_id)->update('proposal_mahasiswa', $data_update);
            
            // Kirim notifikasi ke dosen pembimbing
            $this->_kirim_notifikasi_pembimbing($proposal_id, $dosen_id);
            
            // Kirim notifikasi ke mahasiswa
            $this->_kirim_notifikasi_mahasiswa($proposal_id, 'disetujui');
            
            $this->session->set_flashdata('success', 'Proposal berhasil disetujui dan dosen pembimbing telah ditetapkan!');
            
        } else if ($aksi == 'tolak') {
            // Update proposal - ditolak kaprodi
            $data_update = [
                'status_kaprodi' => '2',
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $proposal_id)->update('proposal_mahasiswa', $data_update);
            
            // Kirim notifikasi ke mahasiswa
            $this->_kirim_notifikasi_mahasiswa($proposal_id, 'ditolak');
            
            $this->session->set_flashdata('success', 'Proposal telah ditolak dan mahasiswa telah diberi tahu.');
        }
        
        redirect('kaprodi/proposal');
    }

    public function penetapan($proposal_id) {
        $data['title'] = 'Form Penetapan Pembimbing & Penguji';
        
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa, mahasiswa.email');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        if(!$data['proposal']) {
            redirect('kaprodi/proposal');
        }
        
        // PERBAIKAN: Ambil SEMUA dosen dari SEMUA prodi untuk penetapan pembimbing & penguji
        $this->db->select('dosen.*, prodi.nama as nama_prodi');
        $this->db->from('dosen');
        $this->db->join('prodi', 'dosen.prodi_id = prodi.id', 'left');
        $this->db->where('dosen.level', '2'); // Exclude admin saja
        $this->db->order_by('dosen.nama', 'ASC');
        $data['dosens'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/penetapan', $data);
    }

    private function _kirim_notifikasi_pembimbing($proposal_id, $dosen_id) {
        // Ambil data proposal dan mahasiswa
        $data = $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa, mahasiswa.nim, mahasiswa.email as email_mahasiswa, prodi.nama as nama_prodi')
                        ->from('proposal_mahasiswa')
                        ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
                        ->join('prodi', 'mahasiswa.prodi_id = prodi.id')
                        ->where('proposal_mahasiswa.id', $proposal_id)
                        ->get()->row();
        
        $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
        
        if ($data && $dosen) {
            // Setup email
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls'
            ];
            
            $this->email->initialize($config);
            
            $subject = 'Penunjukan sebagai Dosen Pembimbing - ' . $data->nama_mahasiswa;
            
            $message = "
            <h3>Penunjukan sebagai Dosen Pembimbing</h3>
            <p>Yth. {$dosen->nama},</p>
            <p>Anda telah ditunjuk sebagai <strong>Dosen Pembimbing</strong> untuk mahasiswa:</p>
            <ul>
                <li>Nama: {$data->nama_mahasiswa}</li>
                <li>NIM: {$data->nim}</li>
                <li>Prodi: {$data->nama_prodi}</li>
                <li>Judul: {$data->judul}</li>
            </ul>
            <p>Silakan login ke sistem untuk memberikan persetujuan: <a href='" . base_url('dosen/bimbingan') . "'>Login Sistem</a></p>
            <p>Terima kasih atas kesediaannya.</p>
            ";
            
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($dosen->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $this->email->send();
        }
    }

    private function _kirim_notifikasi_mahasiswa($proposal_id, $status) {
        // Ambil data proposal dan mahasiswa
        $data = $this->db->select('proposal_mahasiswa.*, mahasiswa.nama as nama_mahasiswa, mahasiswa.email as email_mahasiswa')
                        ->from('proposal_mahasiswa')
                        ->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id')
                        ->where('proposal_mahasiswa.id', $proposal_id)
                        ->get()->row();
        
        if ($data) {
            // Setup email
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls'
            ];
            
            $this->email->initialize($config);
            
            if ($status == 'disetujui') {
                $subject = 'Proposal Disetujui - Menunggu Persetujuan Dosen Pembimbing';
                $message = "
                <h3>Proposal Disetujui</h3>
                <p>Yth. {$data->nama_mahasiswa},</p>
                <p>Proposal Anda dengan judul <strong>{$data->judul}</strong> telah <strong>DISETUJUI</strong> oleh Kaprodi.</p>
                <p>Status saat ini: <strong>Menunggu persetujuan dosen pembimbing</strong></p>
                <p>Silakan pantau perkembangan di sistem: <a href='" . base_url('mahasiswa/proposal') . "'>Login Sistem</a></p>
                ";
            } else {
                $subject = 'Proposal Ditolak - Perlu Perbaikan';
                $message = "
                <h3>Proposal Ditolak</h3>
                <p>Yth. {$data->nama_mahasiswa},</p>
                <p>Proposal Anda dengan judul <strong>{$data->judul}</strong> belum dapat disetujui.</p>
                <p>Komentar: {$data->komentar_kaprodi}</p>
                <p>Silakan lakukan perbaikan dan ajukan kembali.</p>
                ";
            }
            
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($data->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $this->email->send();
        }
    }

    public function riwayat() {
        $data['title'] = 'Riwayat Review Proposal';
        
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa,
            d1.nama as nama_pembimbing,
            dk.nama as nama_kaprodi
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('dosen d1', 'proposal_mahasiswa.dosen_id = d1.id', 'left');
        $this->db->join('dosen dk', 'proposal_mahasiswa.penetapan_oleh = dk.id', 'left');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->where('proposal_mahasiswa.tanggal_review_kaprodi IS NOT NULL');
        $this->db->order_by('proposal_mahasiswa.tanggal_review_kaprodi', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/riwayat', $data);
    }

    public function mahasiswa() {
        $data['title'] = 'Daftar Mahasiswa Prodi';
        
        $this->db->from('mahasiswa');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->order_by('nim', 'ASC');
        $data['mahasiswa_list'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/mahasiswa', $data);
    }

    public function dosen() {
        $data['title'] = 'Daftar Seluruh Dosen';
        
        // PERBAIKAN: Tampilkan semua dosen dari semua prodi dengan info prodi
        $this->db->select('dosen.*, prodi.nama as nama_prodi');
        $this->db->from('dosen');
        $this->db->join('prodi', 'dosen.prodi_id = prodi.id', 'left');
        $this->db->where('dosen.level', '2');; // Exclude admin saja
        $this->db->order_by('dosen.nama', 'ASC');
        $data['dosen_list'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/dosen', $data);
    }
    
    public function laporan() {
        $data['title'] = 'Rekapitulasi Laporan';

        // Data proposal berdasarkan status
        $this->db->select('proposal_mahasiswa.*, mahasiswa.nim, mahasiswa.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
        $data['all_proposals'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/laporan', $data);
    }

    public function profil() {
        $data['title'] = 'Profil Kaprodi';
        $data['user'] = $this->db->get_where('dosen', array('id' => $this->session->userdata('id')))->row();
        $this->load->view('kaprodi/profil', $data);
    }

    // Method debug yang sudah diperbaiki
    public function debug_dosen() {
        echo "<h3>Debug Dosen Query - UPDATED untuk Semua Prodi</h3>";
        
        // Cek total dosen di database
        $total_dosen = $this->db->count_all('dosen');
        echo "Total dosen di database: " . $total_dosen . "<br><br>";
        
        // Cek semua dosen dengan level (tanpa status)
        echo "<h4>Semua Dosen dengan Level dan Prodi:</h4>";
        $all_dosen = $this->db->select('dosen.id, dosen.nama, dosen.level, dosen.prodi_id, prodi.nama as nama_prodi')
                              ->from('dosen')
                              ->join('prodi', 'dosen.prodi_id = prodi.id', 'left')
                              ->get()->result();
        foreach($all_dosen as $d) {
            echo "ID: {$d->id} | Nama: {$d->nama} | Level: {$d->level} | Prodi: {$d->nama_prodi}<br>";
        }
        
        echo "<br><h4>Query untuk Review Proposal (semua dosen kecuali admin):</h4>";
        $dosens_filter = $this->db->select('dosen.*, prodi.nama as nama_prodi')
                                  ->from('dosen')
                                  ->join('prodi', 'dosen.prodi_id = prodi.id', 'left')
                                  ->where_not_in('level', ['1'])
                                  ->where('dosen.id !=', $this->session->userdata('id'))
                                  ->order_by('dosen.nama', 'ASC')
                                  ->get()->result();
        
        echo "Jumlah dosen yang memenuhi criteria: " . count($dosens_filter) . "<br>";
        foreach($dosens_filter as $d) {
            echo "ID: {$d->id} | Nama: {$d->nama} | Level: {$d->level} | Prodi: {$d->nama_prodi}<br>";
        }
    }
}