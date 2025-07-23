<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil mahasiswa dengan statistik jurnal bimbingan - IMPROVED QUERY
        $this->db->select('
            pm.id as proposal_id,
            pm.judul,
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            pm.workflow_status,
            pm.created_at as tanggal_proposal,
            m.id as mahasiswa_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            p.nama as nama_prodi,
            COUNT(jb.id) as total_bimbingan,
            SUM(CASE WHEN jb.status_validasi = "1" THEN 1 ELSE 0 END) as jurnal_tervalidasi,
            SUM(CASE WHEN jb.status_validasi = "0" THEN 1 ELSE 0 END) as jurnal_pending,
            SUM(CASE WHEN jb.status_validasi = "2" THEN 1 ELSE 0 END) as jurnal_revisi,
            MAX(jb.tanggal_bimbingan) as bimbingan_terakhir,
            MAX(jb.created_at) as jurnal_terakhir_dibuat
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('jurnal_bimbingan jb', 'pm.id = jb.proposal_id', 'left');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_kaprodi', '1');
        $this->db->where('pm.status_pembimbing', '1'); 
        $this->db->group_by('pm.id, m.nim, m.nama, m.email, p.nama, pm.judul, pm.jenis_penelitian, pm.lokasi_penelitian, pm.workflow_status, pm.created_at, m.nomor_telepon');
        $this->db->order_by('jurnal_pending', 'DESC');
        $this->db->order_by('jurnal_terakhir_dibuat', 'DESC');
        
        $mahasiswa_list = $this->db->get()->result();
        
        // Ambil jurnal pending untuk overview  
        $this->db->select('
            jb.*,
            pm.judul as judul_proposal,
            m.nim,
            m.nama as nama_mahasiswa
        ');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('jb.status_validasi', '0');
        $this->db->order_by('jb.created_at', 'DESC');
        $this->db->limit(10);
        
        $data['jurnal_pending_list'] = $this->db->get()->result();
        $data['mahasiswa_bimbingan'] = $mahasiswa_list;
        
        // Statistik untuk dashboard
        $data['total_mahasiswa'] = count($mahasiswa_list);
        $data['total_jurnal_pending'] = 0;
        $data['total_jurnal_tervalidasi'] = 0;
        
        foreach($mahasiswa_list as $mhs) {
            $data['total_jurnal_pending'] += (int)$mhs->jurnal_pending;
            $data['total_jurnal_tervalidasi'] += (int)$mhs->jurnal_tervalidasi;
        }
        
        // GUNAKAN STRUKTUR WRAPPER ASLI ANDA
        $this->load->view('dosen/bimbingan', $data);
    }

    public function detail_mahasiswa($proposal_id) {
        $data['title'] = 'Detail Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil detail mahasiswa dan proposal
        $this->db->select('
            pm.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            m.alamat,
            m.foto,
            p.nama as nama_prodi,
            pm.id as proposal_id
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1');
        
        $mahasiswa = $this->db->get()->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/bimbingan');
            return;
        }
        
        $data['mahasiswa'] = $mahasiswa;
        
        // Ambil jurnal bimbingan
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->order_by('pertemuan_ke', 'ASC');
        
        $jurnal_list = $this->db->get()->result();
        $data['jurnal_bimbingan'] = $jurnal_list;
        
        // Hitung statistik
        $data['total_bimbingan'] = count($jurnal_list);
        $data['bimbingan_tervalidasi'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '1'; }));
        $data['bimbingan_pending'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '0'; }));
        $data['bimbingan_revisi'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '2'; }));
        
        // GUNAKAN STRUKTUR WRAPPER ASLI ANDA
        $this->load->view('dosen/bimbingan_detail', $data);
    }

    public function quick_validasi() {
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(['error' => true, 'message' => 'Method not allowed']);
            return;
        }
        
        $jurnal_id = $this->input->post('jurnal_id');
        $status_validasi = $this->input->post('status_validasi');
        $catatan_dosen = $this->input->post('catatan_dosen');
        
        if (empty($jurnal_id) || empty($status_validasi)) {
            echo json_encode(['error' => true, 'message' => 'Data tidak lengkap']);
            return;
        }
        
        // Validasi kepemilikan jurnal
        $this->db->select('jb.*, pm.dosen_id');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->where('jb.id', $jurnal_id);
        $this->db->where('pm.dosen_id', $this->session->userdata('id'));
        $jurnal = $this->db->get()->row();
        
        if (!$jurnal) {
            echo json_encode(['error' => true, 'message' => 'Jurnal tidak ditemukan']);
            return;
        }
        
        // Update validasi jurnal
        $update_data = [
            'status_validasi' => $status_validasi,
            'catatan_dosen' => $catatan_dosen,
            'tanggal_validasi' => date('Y-m-d H:i:s'),
            'validasi_oleh' => $this->session->userdata('id')
        ];
        
        $this->db->where('id', $jurnal_id);
        $result = $this->db->update('jurnal_bimbingan', $update_data);
        
        if ($result) {
            $message = ($status_validasi == '1') ? 'Jurnal berhasil divalidasi!' : 'Jurnal dikembalikan untuk revisi!';
            echo json_encode(['error' => false, 'message' => $message]);
        } else {
            echo json_encode(['error' => true, 'message' => 'Gagal memvalidasi jurnal']);
        }
    }

    public function validasi_jurnal() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/bimbingan');
            return;
        }
        
        $jurnal_id = $this->input->post('jurnal_id');
        $status_validasi = $this->input->post('status_validasi');
        $catatan_dosen = $this->input->post('catatan_dosen');
        
        if (empty($jurnal_id) || empty($status_validasi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/bimbingan');
            return;
        }
        
        // Validasi kepemilikan jurnal
        $this->db->select('jb.*, pm.dosen_id, pm.id as proposal_id');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->where('jb.id', $jurnal_id);
        $this->db->where('pm.dosen_id', $this->session->userdata('id'));
        $jurnal = $this->db->get()->row();
        
        if (!$jurnal) {
            $this->session->set_flashdata('error', 'Jurnal tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/bimbingan');
            return;
        }
        
        // Update validasi jurnal
        $update_data = [
            'status_validasi' => $status_validasi,
            'catatan_dosen' => $catatan_dosen,
            'tanggal_validasi' => date('Y-m-d H:i:s'),
            'validasi_oleh' => $this->session->userdata('id')
        ];
        
        $this->db->where('id', $jurnal_id);
        $result = $this->db->update('jurnal_bimbingan', $update_data);
        
        if ($result) {
            $message = ($status_validasi == '1') ? 'Jurnal berhasil divalidasi!' : 'Jurnal dikembalikan untuk revisi!';
            $this->session->set_flashdata('success', $message);
            redirect('dosen/bimbingan/detail_mahasiswa/' . $jurnal->proposal_id);
        } else {
            $this->session->set_flashdata('error', 'Gagal memvalidasi jurnal!');
            redirect('dosen/bimbingan');
        }
    }

    public function tambah_jurnal() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/bimbingan');
            return;
        }
        
        $proposal_id = $this->input->post('proposal_id');
        $pertemuan_ke = $this->input->post('pertemuan_ke');
        $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
        $materi_bimbingan = $this->input->post('materi_bimbingan');
        $catatan_dosen = $this->input->post('catatan_dosen');
        $tindak_lanjut = $this->input->post('tindak_lanjut');
        
        if (empty($proposal_id) || empty($tanggal_bimbingan) || empty($materi_bimbingan)) {
            $this->session->set_flashdata('error', 'Data wajib tidak lengkap!');
            redirect('dosen/bimbingan');
            return;
        }
        
        // Validasi proposal milik dosen ini
        $this->db->where('id', $proposal_id);
        $this->db->where('dosen_id', $this->session->userdata('id'));
        $proposal = $this->db->get('proposal_mahasiswa')->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/bimbingan');
            return;
        }
        
        // Auto-generate pertemuan_ke jika tidak diisi
        if (empty($pertemuan_ke)) {
            $this->db->select('MAX(pertemuan_ke) as max_pertemuan');
            $this->db->where('proposal_id', $proposal_id);
            $max_result = $this->db->get('jurnal_bimbingan')->row();
            $pertemuan_ke = ($max_result && $max_result->max_pertemuan) ? $max_result->max_pertemuan + 1 : 1;
        }
        
        // Insert jurnal bimbingan
        $insert_data = [
            'proposal_id' => $proposal_id,
            'pertemuan_ke' => $pertemuan_ke,
            'tanggal_bimbingan' => $tanggal_bimbingan,
            'materi_bimbingan' => $materi_bimbingan,
            'catatan_dosen' => $catatan_dosen,
            'tindak_lanjut' => $tindak_lanjut,
            'status_validasi' => '1', // Langsung tervalidasi karena dibuat dosen
            'tanggal_validasi' => date('Y-m-d H:i:s'),
            'validasi_oleh' => $this->session->userdata('id'),
            'created_by' => 'dosen',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->db->insert('jurnal_bimbingan', $insert_data);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Jurnal bimbingan berhasil ditambahkan dan langsung tervalidasi!');
            redirect('dosen/bimbingan/detail_mahasiswa/' . $proposal_id);
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan jurnal bimbingan!');
            redirect('dosen/bimbingan');
        }
    }

    public function export_jurnal($proposal_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Verify access
        $this->db->select('pm.*, m.nama as nama_mahasiswa, m.nim');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.dosen_id', $dosen_id);
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('dosen/bimbingan');
            return;
        }
        
        $this->session->set_flashdata('info', 'Fitur export jurnal akan segera tersedia!');
        redirect('dosen/bimbingan/detail_mahasiswa/' . $proposal_id);
    }
}