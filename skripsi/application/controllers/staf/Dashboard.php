<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Sistem Informasi Manajemen Tugas Akhir STK St. Yakobus
 * Role: Staf Akademik
 * Level: 5
 */
class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date', 'file']);
        $this->load->library(['form_validation', 'upload']);
        
        // Cek login dan level
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if ($this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    /**
     * Dashboard Utama Staf
     */
    public function index() {
        $data = $this->_prepare_dashboard_data();
        $this->load->view('staf/dashboard', $data);
    }

    /**
     * Menyiapkan data untuk dashboard
     */
    private function _prepare_dashboard_data() {
        $data = [];
        
        // 1. Data Pengumuman Tahapan
        $data['pengumuman'] = $this->_get_pengumuman_tahapan();
        
        // 2. Total Mahasiswa (semua prodi)
        $data['total_mahasiswa'] = $this->_get_total_mahasiswa();
        
        // 3. Total Dosen (semua prodi)
        $data['total_dosen'] = $this->_get_total_dosen();
        
        // 4. Data untuk Quick Shortcuts
        $data['shortcuts'] = $this->_get_shortcuts_data();
        
        // 5. Infografis Data Tahapan Workflow
        $data['workflow_stats'] = $this->_get_workflow_statistics();
        
        // 6. Data mahasiswa per tahapan untuk chart
        $data['chart_data'] = $this->_get_chart_data();
        
        return $data;
    }

    /**
     * Mengambil data pengumuman tahapan
     */
    private function _get_pengumuman_tahapan() {
        $this->db->select('*');
        $this->db->from('pengumuman_tahapan');
        $this->db->where('aktif', '1');
        $this->db->order_by('no', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Mengambil total mahasiswa semua prodi
     */
    private function _get_total_mahasiswa() {
        $this->db->select('COUNT(*) as total');
        $this->db->from('mahasiswa');
        $this->db->where('status', '1'); // Hanya mahasiswa aktif
        $result = $this->db->get()->row();
        
        // Total yang sudah mengajukan proposal
        $this->db->select('COUNT(DISTINCT mahasiswa_id) as total_proposal');
        $this->db->from('proposal_mahasiswa');
        $proposal_result = $this->db->get()->row();
        
        return [
            'total' => $result ? $result->total : 0,
            'mengajukan_proposal' => $proposal_result ? $proposal_result->total_proposal : 0
        ];
    }

    /**
     * Mengambil total dosen semua prodi
     */
    private function _get_total_dosen() {
        $this->db->select('COUNT(*) as total');
        $this->db->from('dosen');
        $this->db->where('level', '2'); // Hanya dosen
        $result = $this->db->get()->row();
        
        // Dosen yang sedang membimbing
        $this->db->select('COUNT(DISTINCT dosen_id) as total_membimbing');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('status_kaprodi', '1');
        $this->db->where('dosen_id IS NOT NULL');
        $membimbing_result = $this->db->get()->row();
        
        return [
            'total' => $result ? $result->total : 0,
            'membimbing' => $membimbing_result ? $membimbing_result->total_membimbing : 0
        ];
    }

    /**
     * Data untuk quick shortcuts
     */
    private function _get_shortcuts_data() {
        $shortcuts = [];
        
        // Bimbingan - Jurnal yang perlu di-export
        $this->db->select('COUNT(*) as total');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->where('jb.status_validasi', '1');
        $result = $this->db->get()->row();
        $shortcuts['bimbingan'] = $result ? $result->total : 0;
        
        // Seminar Proposal - yang sudah dijadwalkan
        $this->db->select('COUNT(*) as total');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('status_seminar_proposal', '1');
        $this->db->where('tanggal_seminar_proposal IS NOT NULL');
        $result = $this->db->get()->row();
        $shortcuts['seminar_proposal'] = $result ? $result->total : 0;
        
        // Penelitian - yang butuh surat izin
        $this->db->select('COUNT(*) as total');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('workflow_status', 'penelitian');
        $this->db->where('status_izin_penelitian', '0');
        $result = $this->db->get()->row();
        $shortcuts['penelitian'] = $result ? $result->total : 0;
        
        // Seminar Skripsi - yang sudah dijadwalkan
        $this->db->select('COUNT(*) as total');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('status_seminar_skripsi', '1');
        $this->db->where('tanggal_seminar_skripsi IS NOT NULL');
        $result = $this->db->get()->row();
        $shortcuts['seminar_skripsi'] = $result ? $result->total : 0;
        
        // Publikasi - yang perlu validasi staf
        $this->db->select('COUNT(*) as total');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('workflow_status', 'publikasi');
        $this->db->where('status_publikasi', '1');
        $this->db->where('(validasi_staf_publikasi = "0" OR validasi_staf_publikasi IS NULL)');
        $result = $this->db->get()->row();
        $shortcuts['publikasi'] = $result ? $result->total : 0;
        
        return $shortcuts;
    }

    /**
     * Statistik workflow mahasiswa
     */
    private function _get_workflow_statistics() {
        $stats = [];
        
        $workflow_stages = [
            'proposal' => 'Pengajuan Proposal',
            'bimbingan' => 'Bimbingan',
            'seminar_proposal' => 'Seminar Proposal',
            'penelitian' => 'Penelitian',
            'seminar_skripsi' => 'Seminar Skripsi',
            'publikasi' => 'Publikasi',
            'selesai' => 'Selesai'
        ];
        
        foreach ($workflow_stages as $stage => $label) {
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pm.workflow_status', $stage);
            $result = $this->db->get()->row();
            
            $stats[] = [
                'stage' => $stage,
                'label' => $label,
                'total' => $result ? $result->total : 0
            ];
        }
        
        return $stats;
    }

    /**
     * Data untuk chart/grafik
     */
    private function _get_chart_data() {
        // Data mahasiswa per prodi dan tahapan
        $this->db->select('
            p.nama as nama_prodi,
            pm.workflow_status,
            COUNT(*) as total
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->group_by(['p.nama', 'pm.workflow_status']);
        $this->db->order_by(['p.nama', 'pm.workflow_status']);
        
        $results = $this->db->get()->result();
        
        // Organize data for chart
        $chart_data = [];
        $prodi_list = [];
        
        foreach ($results as $row) {
            if (!in_array($row->nama_prodi, $prodi_list)) {
                $prodi_list[] = $row->nama_prodi;
            }
            
            $chart_data[$row->nama_prodi][$row->workflow_status] = $row->total;
        }
        
        return [
            'prodi_list' => $prodi_list,
            'data' => $chart_data
        ];
    }

    /**
     * Halaman Profil Staf
     */
    public function profil() {
        $staf_id = $this->session->userdata('id');
        
        $this->db->select('*');
        $this->db->from('dosen');
        $this->db->where('id', $staf_id);
        $this->db->where('level', '5');
        $data['staf'] = $this->db->get()->row();
        
        if (!$data['staf']) {
            show_404();
        }
        
        $this->load->view('staf/profil', $data);
    }

    /**
     * Update Profil Staf
     */
    public function update_profil() {
        $staf_id = $this->session->userdata('id');
        
        $this->form_validation->set_rules('nama', 'Nama', 'required|max_length[100]');
        $this->form_validation->set_rules('nip', 'NIP', 'required|max_length[30]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[100]');
        $this->form_validation->set_rules('nomor_telepon', 'Nomor Telepon', 'required|max_length[30]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('staf/dashboard/profil');
        }
        
        $data = [
            'nama' => $this->input->post('nama'),
            'nip' => $this->input->post('nip'),
            'email' => $this->input->post('email'),
            'nomor_telepon' => $this->input->post('nomor_telepon')
        ];
        
        // Handle upload foto jika ada
        if ($_FILES['foto']['name']) {
            $config['upload_path'] = './uploads/staf/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048; // 2MB
            $config['encrypt_name'] = TRUE;
            
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }
            
            $this->upload->initialize($config);
            
            if ($this->upload->do_upload('foto')) {
                $upload_data = $this->upload->data();
                $data['foto'] = $upload_data['file_name'];
                
                // Hapus foto lama jika ada
                $old_staf = $this->db->get_where('dosen', ['id' => $staf_id])->row();
                if ($old_staf->foto && file_exists('./uploads/staf/' . $old_staf->foto)) {
                    unlink('./uploads/staf/' . $old_staf->foto);
                }
            }
        }
        
        $this->db->where('id', $staf_id);
        $this->db->where('level', '5');
        $update = $this->db->update('dosen', $data);
        
        if ($update) {
            // Update session data
            $this->session->set_userdata('nama', $data['nama']);
            $this->session->set_userdata('email', $data['email']);
            
            $this->session->set_flashdata('success', 'Profil berhasil diperbarui');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui profil');
        }
        
        redirect('staf/dashboard/profil');
    }

    /**
     * Daftar Mahasiswa Semua Prodi
     */
    public function daftar_mahasiswa() {
        // Pagination setup
        $this->load->library('pagination');
        
        $config['base_url'] = base_url('staf/dashboard/daftar_mahasiswa');
        $config['total_rows'] = $this->db->count_all('mahasiswa');
        $config['per_page'] = 20;
        $config['uri_segment'] = 4;
        
        // Pagination styling
        $config['full_tag_open'] = '<nav><ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tagl_close'] = '</li>';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tagl_close'] = '</li>';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tagl_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tagl_close'] = '</li>';
        $config['attributes'] = ['class' => 'page-link'];
        
        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
        
        // Get mahasiswa with pagination
        $this->db->select('m.*, p.nama as nama_prodi, pm.workflow_status, pm.judul');
        $this->db->from('mahasiswa m');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('proposal_mahasiswa pm', 'm.id = pm.mahasiswa_id', 'left');
        $this->db->order_by('m.nama', 'ASC');
        $this->db->limit($config['per_page'], $page);
        
        $data['mahasiswa'] = $this->db->get()->result();
        $data['pagination'] = $this->pagination->create_links();
        
        $this->load->view('staf/daftar_mahasiswa', $data);
    }

    /**
     * Daftar Dosen Semua Prodi
     */
    public function daftar_dosen() {
        $this->db->select('d.*, p.nama as nama_prodi');
        $this->db->from('dosen d');
        $this->db->join('prodi p', 'd.prodi_id = p.id', 'left');
        $this->db->where_in('d.level', ['2', '4']); // Dosen dan Kaprodi
        $this->db->order_by('d.nama', 'ASC');
        
        $data['dosen'] = $this->db->get()->result();
        
        $this->load->view('staf/daftar_dosen', $data);
    }
}