<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

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
        $data['title'] = 'Dashboard Dosen';
        $dosen_id = $this->session->userdata('id');
        
        // 1. TOTAL MAHASISWA YANG DIBIMBING
        $this->db->select('COUNT(*) as total');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1'); // Sudah disetujui sebagai pembimbing
        $result = $this->db->get()->row();
        $data['total_mahasiswa_bimbingan'] = $result ? $result->total : 0;
        
        // 2. PROGRESS BIMBINGAN PER TAHAP
        $data['progress_bimbingan'] = $this->_get_progress_bimbingan($dosen_id);
        
        // 3. DAFTAR MAHASISWA BIMBINGAN AKTIF
        $data['mahasiswa_bimbingan'] = $this->_get_mahasiswa_bimbingan($dosen_id);
        
        // 4. RECENT ACTIVITIES
        $data['recent_activities'] = $this->_get_recent_activities($dosen_id);
        
        // 5. STATISTIK TAMBAHAN (diperbaiki sesuai kebutuhan Quick Actions)
        $data['stats_tambahan'] = $this->_get_stats_tambahan($dosen_id);
        
        $this->load->view('dosen/dashboard', $data);
    }
    
    private function _get_progress_bimbingan($dosen_id) {
        $progress = [
            'pengajuan_proposal' => 0,
            'bimbingan_proposal' => 0, 
            'seminar_proposal' => 0,
            'penelitian' => 0,
            'seminar_skripsi' => 0,
            'publikasi' => 0
        ];
        
        try {
            // FIXED: Query sederhana tanpa CASE WHEN yang kompleks
            $this->db->select('pm.status_pembimbing, pm.workflow_status');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->where('pm.dosen_id', $dosen_id);
            
            $mahasiswa_list = $this->db->get()->result();
            
            // Hitung progress per tahap dengan PHP logic (lebih aman)
            foreach ($mahasiswa_list as $mhs) {
                $current_stage = 'pengajuan_proposal'; // default
                
                // Tentukan tahap berdasarkan status
                if (is_null($mhs->status_pembimbing) || $mhs->status_pembimbing == '0') {
                    $current_stage = 'pengajuan_proposal';
                } elseif ($mhs->status_pembimbing == '1') {
                    if (is_null($mhs->workflow_status) || $mhs->workflow_status == 'proposal' || $mhs->workflow_status == 'bimbingan') {
                        $current_stage = 'bimbingan_proposal';
                    } elseif ($mhs->workflow_status == 'seminar_proposal') {
                        $current_stage = 'seminar_proposal';
                    } elseif ($mhs->workflow_status == 'penelitian') {
                        $current_stage = 'penelitian';
                    } elseif ($mhs->workflow_status == 'seminar_skripsi') {
                        $current_stage = 'seminar_skripsi';
                    } elseif ($mhs->workflow_status == 'publikasi') {
                        $current_stage = 'publikasi';
                    }
                }
                
                if (isset($progress[$current_stage])) {
                    $progress[$current_stage]++;
                }
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard progress error: ' . $e->getMessage());
        }
        
        return $progress;
    }
    
    private function _get_mahasiswa_bimbingan($dosen_id) {
        $mahasiswa_bimbingan = [];
        
        try {
            // FIXED: Query sederhana tanpa CASE WHEN yang bermasalah
            $this->db->select('pm.id, pm.judul, pm.workflow_status, pm.created_at, pm.status_pembimbing, m.nama as nama_mahasiswa, m.nim, m.foto, p.nama as nama_prodi');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->order_by('pm.created_at', 'DESC');
            $this->db->limit(10);
            
            $mahasiswa_raw = $this->db->get()->result();
            
            // Process status dengan PHP (lebih aman dari SQL error)
            foreach ($mahasiswa_raw as $mhs) {
                // Tentukan status display dan color
                if (is_null($mhs->status_pembimbing) || $mhs->status_pembimbing == '0') {
                    $mhs->status_display = 'Menunggu Persetujuan';
                    $mhs->status_color = 'warning';
                } elseif ($mhs->status_pembimbing == '1') {
                    if (is_null($mhs->workflow_status) || $mhs->workflow_status == 'proposal' || $mhs->workflow_status == 'bimbingan') {
                        $mhs->status_display = 'Bimbingan Proposal';
                        $mhs->status_color = 'info';
                    } elseif ($mhs->workflow_status == 'seminar_proposal') {
                        $mhs->status_display = 'Seminar Proposal';
                        $mhs->status_color = 'primary';
                    } elseif ($mhs->workflow_status == 'penelitian') {
                        $mhs->status_display = 'Penelitian';
                        $mhs->status_color = 'secondary';
                    } elseif ($mhs->workflow_status == 'seminar_skripsi') {
                        $mhs->status_display = 'Seminar Skripsi';
                        $mhs->status_color = 'danger';
                    } elseif ($mhs->workflow_status == 'publikasi') {
                        $mhs->status_display = 'Publikasi';
                        $mhs->status_color = 'success';
                    } else {
                        $mhs->status_display = 'Bimbingan Proposal';
                        $mhs->status_color = 'info';
                    }
                } else {
                    $mhs->status_display = 'Unknown';
                    $mhs->status_color = 'secondary';
                }
                
                $mahasiswa_bimbingan[] = $mhs;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard mahasiswa bimbingan error: ' . $e->getMessage());
        }
        
        return $mahasiswa_bimbingan;
    }
    
    private function _get_recent_activities($dosen_id) {
        $activities = [];
        
        try {
            // 1. Jurnal bimbingan terbaru (jika tabel ada)
            if ($this->db->table_exists('jurnal_bimbingan')) {
                $this->db->select('jb.tanggal_bimbingan, jb.materi_bimbingan, jb.status_validasi, jb.created_at, m.nama as nama_mahasiswa, m.nim');
                $this->db->from('jurnal_bimbingan jb');
                $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->where('pm.dosen_id', $dosen_id);
                $this->db->order_by('jb.created_at', 'DESC');
                $this->db->limit(5);
                
                $jurnal_activities = $this->db->get()->result();
                
                // Add activity type
                foreach ($jurnal_activities as $activity) {
                    $activity->activity_type = 'jurnal_bimbingan';
                    $activity->icon = 'fa-comments';
                    $activity->color = 'info';
                }
                
                $activities = array_merge($activities, $jurnal_activities);
            }
            
            // 2. Persetujuan proposal terbaru
            $this->db->select('pm.tanggal_respon_pembimbing as tanggal_bimbingan, pm.judul as materi_bimbingan, pm.status_pembimbing as status_validasi, pm.tanggal_respon_pembimbing as created_at, m.nama as nama_mahasiswa, m.nim');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('pm.tanggal_respon_pembimbing IS NOT NULL');
            $this->db->order_by('pm.tanggal_respon_pembimbing', 'DESC');
            $this->db->limit(3);
            
            $proposal_activities = $this->db->get()->result();
            
            // Add activity type and modify content
            foreach ($proposal_activities as $activity) {
                $activity->activity_type = 'persetujuan_proposal';
                $activity->materi_bimbingan = 'Persetujuan pembimbingan: ' . substr($activity->materi_bimbingan, 0, 30) . '...';
                $activity->icon = 'fa-check-circle';
                $activity->color = 'success';
            }
            
            $activities = array_merge($activities, $proposal_activities);
            
            // Sort by created_at
            if (!empty($activities)) {
                usort($activities, function($a, $b) {
                    $time_a = isset($a->created_at) ? strtotime($a->created_at) : 0;
                    $time_b = isset($b->created_at) ? strtotime($b->created_at) : 0;
                    return $time_b - $time_a;
                });
            }
            
        } catch (Exception $e) {
            // Jika ada error, return empty array
            log_message('error', 'Dashboard activities error: ' . $e->getMessage());
        }
        
        return array_slice($activities, 0, 8);
    }
    
    private function _get_stats_tambahan($dosen_id) {
        $stats = [
            'menunggu_persetujuan' => 0,
            'total_dibimbing' => 0,
            'selesai_publikasi' => 0,
            'bimbingan_bulan_ini' => 0,
            'seminar_proposal_pending' => 0,
            'seminar_skripsi_pending' => 0,
            'penelitian_pending' => 0,
            'publikasi_pending' => 0
        ];
        
        try {
            // 1. Mahasiswa yang menunggu persetujuan sebagai pembimbing
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('dosen_id', $dosen_id);
            $this->db->where('status', '1'); // Sudah disetujui kaprodi
            $this->db->where('(status_pembimbing IS NULL OR status_pembimbing = "0")'); // Belum direspon
            $result = $this->db->get()->row();
            $stats['menunggu_persetujuan'] = $result ? $result->total : 0;
            
            // 2. Total mahasiswa yang sedang dibimbing
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('dosen_id', $dosen_id);
            $this->db->where('status_pembimbing', '1'); // Sudah disetujui sebagai pembimbing
            $result = $this->db->get()->row();
            $stats['total_dibimbing'] = $result ? $result->total : 0;
            
            // 3. Mahasiswa yang sudah selesai (publikasi)
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('dosen_id', $dosen_id);
            $this->db->where('status_pembimbing', '1');
            $this->db->where('workflow_status', 'publikasi');
            $result = $this->db->get()->row();
            $stats['selesai_publikasi'] = $result ? $result->total : 0;
            
            // 4. Total jurnal bimbingan bulan ini (jika tabel ada)
            if ($this->db->table_exists('jurnal_bimbingan')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('jurnal_bimbingan jb');
                $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
                $this->db->where('pm.dosen_id', $dosen_id);
                $this->db->where('MONTH(jb.created_at)', date('m'));
                $this->db->where('YEAR(jb.created_at)', date('Y'));
                $result = $this->db->get()->row();
                $stats['bimbingan_bulan_ini'] = $result ? $result->total : 0;
            }
            
            // 5. Seminar proposal yang butuh rekomendasi (jika tabel ada)
            if ($this->db->table_exists('seminar_proposal')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('seminar_proposal sp');
                $this->db->join('proposal_mahasiswa pm', 'sp.proposal_id = pm.id');
                $this->db->where('pm.dosen_id', $dosen_id);
                $this->db->where('sp.rekomendasi_pembimbing IS NULL');
                $result = $this->db->get()->row();
                $stats['seminar_proposal_pending'] = $result ? $result->total : 0;
            }
            
            // 6. Seminar skripsi yang butuh rekomendasi (jika tabel ada)
            if ($this->db->table_exists('seminar_skripsi')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('seminar_skripsi ss');
                $this->db->join('proposal_mahasiswa pm', 'ss.proposal_id = pm.id');
                $this->db->where('pm.dosen_id', $dosen_id);
                $this->db->where('ss.rekomendasi_pembimbing IS NULL');
                $result = $this->db->get()->row();
                $stats['seminar_skripsi_pending'] = $result ? $result->total : 0;
            }
            
            // 7. Surat izin penelitian yang butuh rekomendasi (jika tabel ada)
            if ($this->db->table_exists('surat_izin_penelitian')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('surat_izin_penelitian sip');
                $this->db->join('proposal_mahasiswa pm', 'sip.proposal_id = pm.id');
                $this->db->where('pm.dosen_id', $dosen_id);
                $this->db->where('sip.rekomendasi_pembimbing IS NULL');
                $result = $this->db->get()->row();
                $stats['penelitian_pending'] = $result ? $result->total : 0;
            }
            
            // 8. Publikasi yang butuh rekomendasi (jika tabel ada)
            if ($this->db->table_exists('publikasi_tugas_akhir')) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('publikasi_tugas_akhir pub');
                $this->db->join('proposal_mahasiswa pm', 'pub.proposal_id = pm.id');
                $this->db->where('pm.dosen_id', $dosen_id);
                $this->db->where('pub.rekomendasi_pembimbing IS NULL');
                $result = $this->db->get()->row();
                $stats['publikasi_pending'] = $result ? $result->total : 0;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard stats error: ' . $e->getMessage());
        }
        
        return $stats;
    }
}

/* End of file Dashboard.php */