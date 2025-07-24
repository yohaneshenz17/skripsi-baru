<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller untuk Staf Akademik
 * File: application/controllers/staf/Dashboard.php
 */
class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');

        // Cek login dan level staf
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Dashboard Staf Akademik';
        $staf_id = $this->session->userdata('id');
        
        try {
            // 1. STATISTIK TOTAL MAHASISWA
            $data['total_mahasiswa'] = $this->_get_total_mahasiswa();
            
            // 2. STATISTIK TOTAL DOSEN
            $data['total_dosen'] = $this->_get_total_dosen();
            
            // 3. QUICK SHORTCUTS - Tasks yang perlu ditindaklanjuti
            $data['shortcuts'] = $this->_get_shortcuts();
            
            // 4. PENGUMUMAN TAHAPAN
            $data['pengumuman'] = $this->_get_pengumuman();
            
            // 5. WORKFLOW STATISTICS untuk Chart
            $data['workflow_stats'] = $this->_get_workflow_statistics();
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard Staf Error: ' . $e->getMessage());
            // Set default values jika ada error
            $data['total_mahasiswa'] = ['total' => 0, 'mengajukan_proposal' => 0];
            $data['total_dosen'] = ['total' => 0, 'membimbing' => 0];
            $data['shortcuts'] = [
                'bimbingan' => 0,
                'seminar_proposal' => 0,
                'penelitian' => 0,
                'seminar_skripsi' => 0,
                'publikasi' => 0
            ];
            $data['pengumuman'] = [];
            $data['workflow_stats'] = [];
        }
        
        $this->load->view('staf/dashboard', $data);
    }
    
    /**
     * Get total mahasiswa dan yang mengajukan proposal
     */
    private function _get_total_mahasiswa() {
        $total_mahasiswa = [
            'total' => 0,
            'mengajukan_proposal' => 0
        ];
        
        try {
            // Total mahasiswa
            $this->db->select('COUNT(*) as total');
            $this->db->from('mahasiswa');
            $result = $this->db->get()->row();
            $total_mahasiswa['total'] = $result ? $result->total : 0;
            
            // Mahasiswa yang mengajukan proposal
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('created_at >=', date('Y-01-01')); // Tahun ini
            $result = $this->db->get()->row();
            $total_mahasiswa['mengajukan_proposal'] = $result ? $result->total : 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting total mahasiswa: ' . $e->getMessage());
        }
        
        return $total_mahasiswa;
    }
    
    /**
     * Get total dosen dan yang sedang membimbing
     */
    private function _get_total_dosen() {
        $total_dosen = [
            'total' => 0,
            'membimbing' => 0
        ];
        
        try {
            // Total dosen
            $this->db->select('COUNT(*) as total');
            $this->db->from('dosen');
            $this->db->where('level', '2'); // Level dosen
            $result = $this->db->get()->row();
            $total_dosen['total'] = $result ? $result->total : 0;
            
            // Dosen yang sedang membimbing
            $this->db->distinct();
            $this->db->select('pm.dosen_id');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->where('pm.status_pembimbing', '1');
            $this->db->where('pm.dosen_id IS NOT NULL');
            $result = $this->db->get()->num_rows();
            $total_dosen['membimbing'] = $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting total dosen: ' . $e->getMessage());
        }
        
        return $total_dosen;
    }
    
    /**
     * Get shortcuts - Tasks yang perlu ditindaklanjuti staf
     */
    private function _get_shortcuts() {
        $shortcuts = [
            'bimbingan' => 0,
            'seminar_proposal' => 0,
            'penelitian' => 0,
            'seminar_skripsi' => 0,
            'publikasi' => 0
        ];
        
        try {
            // 1. Mahasiswa dalam tahap bimbingan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('status_pembimbing', '1');
            $this->db->where('workflow_status', 'bimbingan');
            $result = $this->db->get()->row();
            $shortcuts['bimbingan'] = $result ? $result->total : 0;
            
            // 2. Seminar proposal yang perlu dijadwalkan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('workflow_status', 'seminar_proposal');
            $result = $this->db->get()->row();
            $shortcuts['seminar_proposal'] = $result ? $result->total : 0;
            
            // 3. Surat izin penelitian yang perlu diproses
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('workflow_status', 'penelitian');
            $result = $this->db->get()->row();
            $shortcuts['penelitian'] = $result ? $result->total : 0;
            
            // 4. Seminar skripsi yang perlu dijadwalkan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('workflow_status', 'seminar_skripsi');
            $result = $this->db->get()->row();
            $shortcuts['seminar_skripsi'] = $result ? $result->total : 0;
            
            // 5. Publikasi yang perlu divalidasi
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('workflow_status', 'publikasi');
            $result = $this->db->get()->row();
            $shortcuts['publikasi'] = $result ? $result->total : 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting shortcuts: ' . $e->getMessage());
        }
        
        return $shortcuts;
    }
    
    /**
     * Get pengumuman tahapan (dummy data atau dari database jika ada)
     */
    private function _get_pengumuman() {
        $pengumuman = [];
        
        try {
            // Cek apakah tabel pengumuman ada
            if ($this->db->table_exists('pengumuman_tahapan')) {
                $this->db->select('*');
                $this->db->from('pengumuman_tahapan');
                $this->db->where('tanggal_deadline >=', date('Y-m-d'));
                $this->db->order_by('tanggal_deadline', 'ASC');
                $this->db->limit(5);
                $pengumuman = $this->db->get()->result();
            } else {
                // Dummy data jika tabel belum ada
                $dummy_pengumuman = [
                    (object)[
                        'no' => 1,
                        'tahapan' => 'Pengajuan Proposal',
                        'tanggal_deadline' => date('Y-m-d', strtotime('+30 days')),
                        'keterangan' => 'Batas akhir pengajuan proposal semester ini'
                    ],
                    (object)[
                        'no' => 2,
                        'tahapan' => 'Seminar Proposal',
                        'tanggal_deadline' => date('Y-m-d', strtotime('+45 days')),
                        'keterangan' => 'Pendaftaran seminar proposal dibuka'
                    ],
                    (object)[
                        'no' => 3,
                        'tahapan' => 'Penelitian',
                        'tanggal_deadline' => date('Y-m-d', strtotime('+60 days')),
                        'keterangan' => 'Pengajuan surat izin penelitian'
                    ]
                ];
                $pengumuman = array_slice($dummy_pengumuman, 0, 3);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error getting pengumuman: ' . $e->getMessage());
        }
        
        return $pengumuman;
    }
    
    /**
     * Get workflow statistics untuk chart
     */
    private function _get_workflow_statistics() {
        $workflow_stats = [];
        
        try {
            $stages = [
                'proposal' => 'Pengajuan Proposal',
                'bimbingan' => 'Bimbingan',
                'seminar_proposal' => 'Seminar Proposal',
                'penelitian' => 'Penelitian',
                'seminar_skripsi' => 'Seminar Skripsi',
                'publikasi' => 'Publikasi',
                'selesai' => 'Selesai'
            ];
            
            foreach ($stages as $stage => $label) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa');
                
                if ($stage == 'proposal') {
                    // Proposal yang belum disetujui atau baru diajukan
                    $this->db->where('(status_kaprodi IS NULL OR status_kaprodi = "0")');
                } elseif ($stage == 'selesai') {
                    // Mahasiswa yang sudah selesai semua tahapan
                    $this->db->where('workflow_status', 'publikasi');
                    // Bisa ditambah kondisi lain untuk menentukan "selesai"
                } else {
                    $this->db->where('workflow_status', $stage);
                }
                
                $result = $this->db->get()->row();
                $total = $result ? $result->total : 0;
                
                $workflow_stats[] = [
                    'label' => $label,
                    'total' => $total,
                    'stage' => $stage
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error getting workflow statistics: ' . $e->getMessage());
        }
        
        return $workflow_stats;
    }
}

/* End of file Dashboard.php */