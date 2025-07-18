<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PenetapanPembimbing extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');
        
        // Cek login dan level kaprodi
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
        $data['title'] = 'Penetapan Pembimbing - Workflow Terbaru';
        
        // Ambil proposal yang belum ditetapkan (hanya yang valid)
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa,
            mahasiswa.email as email_mahasiswa
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        $this->db->where('proposal_mahasiswa.status', '0'); // Belum ditetapkan
        // Filter: hanya proposal yang valid (bukan data lama)
        $this->db->where('proposal_mahasiswa.id NOT IN (34, 35)');
        $this->db->order_by('proposal_mahasiswa.id', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/penetapan_pembimbing/index', $data);
    }

    public function detail($proposal_id) {
        $data['title'] = 'Form Penetapan Pembimbing & Penguji';
        
        // Ambil detail proposal
        $this->db->select('
            proposal_mahasiswa.*, 
            mahasiswa.nim, 
            mahasiswa.nama as nama_mahasiswa, 
            mahasiswa.email,
            mahasiswa.tempat_lahir,
            mahasiswa.tanggal_lahir,
            mahasiswa.jenis_kelamin,
            mahasiswa.alamat,
            mahasiswa.nomor_telepon,
            prodi.nama as nama_prodi
        ');
        $this->db->from('proposal_mahasiswa');
        $this->db->join('mahasiswa', 'proposal_mahasiswa.mahasiswa_id = mahasiswa.id');
        $this->db->join('prodi', 'mahasiswa.prodi_id = prodi.id');
        $this->db->where('proposal_mahasiswa.id', $proposal_id);
        $this->db->where('mahasiswa.prodi_id', $this->prodi_id);
        // Filter: hanya proposal yang valid
        $this->db->where('proposal_mahasiswa.id NOT IN (34, 35)');
        
        $data['proposal'] = $this->db->get()->row();
        
        if (!$data['proposal']) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau tidak valid!');
            redirect('kaprodi/penetapan_pembimbing');
        }
        
        // Ambil dosen yang bisa menjadi pembimbing dan penguji
        // Level 2 = dosen biasa, level 4 = kaprodi (tapi exclude kaprodi yang sedang login)
        $this->db->where('level', '2');
        $this->db->where('id !=', $this->session->userdata('id')); // Exclude kaprodi yang sedang login
        $this->db->order_by('nama', 'ASC');
        $data['dosens'] = $this->db->get('dosen')->result();
        
        $this->load->view('kaprodi/penetapan_pembimbing/detail', $data);
    }

    public function simpan_penetapan() {
        $proposal_id = $this->input->post('proposal_id');
        $dosen_pembimbing_id = $this->input->post('dosen_pembimbing_id');
        $dosen_penguji1_id = $this->input->post('dosen_penguji1_id');
        $dosen_penguji2_id = $this->input->post('dosen_penguji2_id');
        
        // Validasi input
        if (!$proposal_id || !$dosen_pembimbing_id || !$dosen_penguji1_id || !$dosen_penguji2_id) {
            $this->session->set_flashdata('error', 'Semua field harus diisi!');
            redirect('kaprodi/penetapan_pembimbing/detail/' . $proposal_id);
        }
        
        // Validasi dosen tidak boleh sama
        $dosens = [$dosen_pembimbing_id, $dosen_penguji1_id, $dosen_penguji2_id];
        if (count($dosens) !== count(array_unique($dosens))) {
            $this->session->set_flashdata('error', 'Dosen pembimbing dan penguji harus berbeda!');
            redirect('kaprodi/penetapan_pembimbing/detail/' . $proposal_id);
        }
        
        // Validasi proposal exists dan valid
        $proposal = $this->db->select('pm.*, m.nama as nama_mahasiswa, m.email as email_mahasiswa')
                            ->from('proposal_mahasiswa pm')
                            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                            ->where('pm.id', $proposal_id)
                            ->where('m.prodi_id', $this->prodi_id)
                            ->where('pm.id NOT IN (34, 35)') // Hanya proposal valid
                            ->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau tidak valid!');
            redirect('kaprodi/penetapan_pembimbing');
        }
        
        // Update proposal dengan penetapan
        $update_data = [
            'dosen_id' => $dosen_pembimbing_id,
            'dosen_penguji_id' => $dosen_penguji1_id,
            'dosen_penguji2_id' => $dosen_penguji2_id,
            'status' => '1',
            // TAMBAH BARIS INI:
            'tanggal_penetapan' => date('Y-m-d H:i:s'),
            'penetapan_oleh' => $this->session->userdata('id'),
            'status_kaprodi' => '1'
        ];
        
        $this->db->where('id', $proposal_id);
        $update_result = $this->db->update('proposal_mahasiswa', $update_data);
        
        if ($update_result) {
            // Update workflow tracking
            $this->_update_workflow_tracking($proposal_id);
            
            // Kirim notifikasi
            $this->_kirim_notifikasi($proposal_id, $dosen_pembimbing_id, $dosen_penguji1_id, $dosen_penguji2_id);
            
            $this->session->set_flashdata('success', 'Penetapan pembimbing dan penguji berhasil disimpan!');
            redirect('kaprodi/penetapan_pembimbing');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan penetapan!');
            redirect('kaprodi/penetapan_pembimbing/detail/' . $proposal_id);
        }
    }

    private function _update_workflow_tracking($proposal_id) {
        // Insert workflow tracking
        $workflow_data = [
            'proposal_id' => $proposal_id,
            'tahap' => 'penetapan_selesai',
            'status' => 'approved',
            'komentar' => 'Pembimbing dan penguji telah ditetapkan',
            'diproses_oleh' => $this->session->userdata('id'),
            'tanggal_proses' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('proposal_workflow', $workflow_data);
    }

    private function _kirim_notifikasi($proposal_id, $dosen_pembimbing_id, $dosen_penguji1_id, $dosen_penguji2_id) {
        // Ambil data proposal dan mahasiswa
        $proposal = $this->db->select('pm.*, m.nama as nama_mahasiswa, m.nim, m.email as email_mahasiswa')
                            ->from('proposal_mahasiswa pm')
                            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                            ->where('pm.id', $proposal_id)
                            ->get()->row();
        
        if (!$proposal) return;
        
        // Ambil data dosen
        $dosen_pembimbing = $this->db->get_where('dosen', ['id' => $dosen_pembimbing_id])->row();
        $dosen_penguji1 = $this->db->get_where('dosen', ['id' => $dosen_penguji1_id])->row();
        $dosen_penguji2 = $this->db->get_where('dosen', ['id' => $dosen_penguji2_id])->row();
        
        // Kirim notifikasi ke mahasiswa
        $this->_kirim_email_mahasiswa($proposal, $dosen_pembimbing, $dosen_penguji1, $dosen_penguji2);
        
        // Kirim notifikasi ke dosen pembimbing
        $this->_kirim_email_dosen_pembimbing($proposal, $dosen_pembimbing);
        
        // Kirim notifikasi ke dosen penguji
        $this->_kirim_email_dosen_penguji($proposal, $dosen_penguji1, 'Penguji 1');
        $this->_kirim_email_dosen_penguji($proposal, $dosen_penguji2, 'Penguji 2');
    }

    private function _kirim_email_mahasiswa($proposal, $dosen_pembimbing, $dosen_penguji1, $dosen_penguji2) {
        // Setup email config
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
        
        $subject = 'Penetapan Pembimbing dan Penguji - ' . $proposal->nama_mahasiswa;
        
        $message = "
        <h3>Penetapan Pembimbing dan Penguji</h3>
        <p>Yth. {$proposal->nama_mahasiswa},</p>
        <p>Proposal Anda dengan judul <strong>{$proposal->judul}</strong> telah ditetapkan pembimbing dan pengujinya:</p>
        <ul>
            <li><strong>Dosen Pembimbing:</strong> {$dosen_pembimbing->nama}</li>
            <li><strong>Dosen Penguji 1:</strong> {$dosen_penguji1->nama}</li>
            <li><strong>Dosen Penguji 2:</strong> {$dosen_penguji2->nama}</li>
        </ul>
        <p>Silakan menghubungi dosen pembimbing untuk memulai proses bimbingan.</p>
        <p>Login ke sistem: <a href='" . base_url('mahasiswa/proposal') . "'>Klik di sini</a></p>
        <p><br>Terima kasih.<br>Kaprodi</p>
        ";
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($proposal->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $this->email->send();
    }

    private function _kirim_email_dosen_pembimbing($proposal, $dosen) {
        if (!$dosen) return;
        
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
        
        $subject = 'Penunjukan sebagai Dosen Pembimbing - ' . $proposal->nama_mahasiswa;
        
        $message = "
        <h3>Penunjukan sebagai Dosen Pembimbing</h3>
        <p>Yth. {$dosen->nama},</p>
        <p>Anda telah ditunjuk sebagai <strong>Dosen Pembimbing</strong> untuk mahasiswa:</p>
        <ul>
            <li><strong>Nama:</strong> {$proposal->nama_mahasiswa}</li>
            <li><strong>NIM:</strong> {$proposal->nim}</li>
            <li><strong>Judul:</strong> {$proposal->judul}</li>
        </ul>
        <p>Silakan login ke sistem untuk memulai proses bimbingan: <a href='" . base_url('dosen/bimbingan') . "'>Login Sistem</a></p>
        <p><br>Terima kasih atas kesediaannya.<br>Kaprodi</p>
        ";
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($dosen->email);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $this->email->send();
    }

    private function _kirim_email_dosen_penguji($proposal, $dosen, $role) {
        if (!$dosen) return;
        
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
        
        $subject = 'Penunjukan sebagai Dosen ' . $role . ' - ' . $proposal->nama_mahasiswa;
        
        $message = "
        <h3>Penunjukan sebagai Dosen {$role}</h3>
        <p>Yth. {$dosen->nama},</p>
        <p>Anda telah ditunjuk sebagai <strong>Dosen {$role}</strong> untuk mahasiswa:</p>
        <ul>
            <li><strong>Nama:</strong> {$proposal->nama_mahasiswa}</li>
            <li><strong>NIM:</strong> {$proposal->nim}</li>
            <li><strong>Judul:</strong> {$proposal->judul}</li>
        </ul>
        <p>Silakan login ke sistem untuk melihat detail proposal: <a href='" . base_url('dosen/penguji') . "'>Login Sistem</a></p>
        <p><br>Terima kasih atas kesediaannya.<br>Kaprodi</p>
        ";
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($dosen->email);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $this->email->send();
    }

    public function riwayat() {
        $data['title'] = 'Riwayat Penetapan Pembimbing';
        
        // Ambil proposal yang sudah ditetapkan
        $this->db->select('
            pm.*, 
            m.nim, 
            m.nama as nama_mahasiswa,
            dp.nama as nama_pembimbing,
            dp1.nama as nama_penguji1,
            dp2.nama as nama_penguji2,
            dk.nama as nama_kaprodi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('dosen dp', 'pm.dosen_id = dp.id', 'left');
        $this->db->join('dosen dp1', 'pm.dosen_penguji_id = dp1.id', 'left');
        $this->db->join('dosen dp2', 'pm.dosen_penguji2_id = dp2.id', 'left');
        $this->db->join('dosen dk', 'pm.penetapan_oleh = dk.id', 'left');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('pm.status', '1'); // Sudah ditetapkan
        $this->db->where('pm.tanggal_penetapan IS NOT NULL');
        // Filter: hanya proposal yang valid
        $this->db->where('pm.id NOT IN (34, 35)');
        $this->db->order_by('pm.tanggal_penetapan', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        $this->load->view('kaprodi/penetapan_pembimbing/riwayat', $data);
    }
}