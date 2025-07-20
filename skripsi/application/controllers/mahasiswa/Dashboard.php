<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        // Data untuk workflow progress - akan di-load via AJAX
        return $this->load->view('mahasiswa/dashboard');
    }

    // Method existing tetap dipertahankan
    public function cekdeadline($id)
    {
        $cek = $this->db->get_where('skripsi', array('mahasiswa_id' => $id))->num_rows();
        if ($cek == 0) {
            $this->db->where('id', $id);
            $this->db->update('mahasiswa', array('status' => '0'));

            $this->db->where('mahasiswa_id', $id);
            $this->db->update('proposal_mahasiswa', array('deadline' => null, 'status' => '0'));
            echo json_encode('waktu habis');
        } else {
            echo json_encode('aman');
        }
    }

    public function getDeadline()
    {
        $mahasiswa_id = $this->input->post('mahasiswa_id');
        $kondisi = array(
            'mahasiswa_id' => $mahasiswa_id,
            'status' => 1
        );
        $this->db->where($kondisi);
        $data = $this->db->get('proposal_mahasiswa_v')->result();
        echo json_encode($data);
    }

    /**
     * NEW: Get workflow progress untuk mahasiswa - DISESUAIKAN DENGAN DATABASE EXISTING
     */
    public function get_workflow_progress()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        $progress = [
            'current_stage' => 'usulan_proposal',
            'current_stage_name' => 'Usulan Proposal',
            'progress_percentage' => 0,
            'stages' => [
                'usulan_proposal' => ['name' => 'Usulan Proposal', 'status' => 'pending', 'color' => 'secondary'],
                'bimbingan' => ['name' => 'Bimbingan', 'status' => 'pending', 'color' => 'secondary'],
                'seminar_proposal' => ['name' => 'Seminar Proposal', 'status' => 'pending', 'color' => 'secondary'],
                'penelitian' => ['name' => 'Penelitian', 'status' => 'pending', 'color' => 'secondary'],
                'seminar_skripsi' => ['name' => 'Seminar Skripsi', 'status' => 'pending', 'color' => 'secondary'],
                'publikasi' => ['name' => 'Publikasi', 'status' => 'pending', 'color' => 'secondary']
            ]
        ];
        
        try {
            // Cek proposal mahasiswa
            $proposal = $this->db->get_where('proposal_mahasiswa', ['mahasiswa_id' => $mahasiswa_id])->row();
            
            if ($proposal) {
                // Mapping workflow_status dari database ke progress
                $workflow_map = [
                    'proposal' => ['stage' => 'usulan_proposal', 'progress' => 16.66],
                    'bimbingan' => ['stage' => 'bimbingan', 'progress' => 33.33],
                    'seminar_proposal' => ['stage' => 'seminar_proposal', 'progress' => 50],
                    'penelitian' => ['stage' => 'penelitian', 'progress' => 66.66],
                    'seminar_skripsi' => ['stage' => 'seminar_skripsi', 'progress' => 83.33],
                    'publikasi' => ['stage' => 'publikasi', 'progress' => 100],
                    'selesai' => ['stage' => 'selesai', 'progress' => 100]
                ];
                
                // Set stages sebagai completed berdasarkan workflow_status
                $current_workflow = $proposal->workflow_status ?: 'proposal';
                $completed_stages = [];
                
                // Tentukan stage mana yang sudah completed
                switch ($current_workflow) {
                    case 'selesai':
                        $completed_stages = ['usulan_proposal', 'bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi'];
                        break;
                    case 'publikasi':
                        $completed_stages = ['usulan_proposal', 'bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi'];
                        break;
                    case 'seminar_skripsi':
                        $completed_stages = ['usulan_proposal', 'bimbingan', 'seminar_proposal', 'penelitian'];
                        break;
                    case 'penelitian':
                        $completed_stages = ['usulan_proposal', 'bimbingan', 'seminar_proposal'];
                        break;
                    case 'seminar_proposal':
                        $completed_stages = ['usulan_proposal', 'bimbingan'];
                        break;
                    case 'bimbingan':
                        $completed_stages = ['usulan_proposal'];
                        break;
                    case 'proposal':
                    default:
                        $completed_stages = [];
                        break;
                }
                
                // Set completed stages
                foreach ($completed_stages as $stage) {
                    if (isset($progress['stages'][$stage])) {
                        $progress['stages'][$stage]['status'] = 'completed';
                        $progress['stages'][$stage]['color'] = 'success';
                    }
                }
                
                // Set current stage
                if (isset($workflow_map[$current_workflow])) {
                    $current_stage_key = $workflow_map[$current_workflow]['stage'];
                    $progress['current_stage'] = $current_stage_key;
                    $progress['current_stage_name'] = $progress['stages'][$current_stage_key]['name'];
                    $progress['progress_percentage'] = $workflow_map[$current_workflow]['progress'];
                    
                    // Set current stage as active (jika belum selesai)
                    if ($current_workflow !== 'selesai' && isset($progress['stages'][$current_stage_key])) {
                        $progress['stages'][$current_stage_key]['status'] = 'active';
                        $progress['stages'][$current_stage_key]['color'] = 'primary';
                    }
                }
                
                // Detail status berdasarkan field specific
                $progress['detail_status'] = [
                    'status_kaprodi' => $proposal->status_kaprodi,
                    'status_pembimbing' => $proposal->status_pembimbing,
                    'status_seminar_proposal' => $proposal->status_seminar_proposal,
                    'status_seminar_skripsi' => $proposal->status_seminar_skripsi,
                    'status_publikasi' => $proposal->status_publikasi,
                    'status_izin_penelitian' => $proposal->status_izin_penelitian
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard workflow progress error: ' . $e->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'data' => $progress]);
    }

    /**
     * NEW: Get notifikasi terbaru - DISESUAIKAN DENGAN TABEL EXISTING
     */
    public function get_notifikasi()
    {
        $mahasiswa_id = $this->session->userdata('id');
        $notifikasi = [];
        
        try {
            // Menggunakan tabel notifikasi yang sudah ada
            $this->db->select('n.*, d.nama as nama_pengirim');
            $this->db->from('notifikasi n');
            $this->db->join('dosen d', 'n.user_id = d.id AND n.untuk_role != "mahasiswa"', 'left'); // Join untuk nama pengirim jika bukan untuk mahasiswa
            $this->db->where('n.user_id', $mahasiswa_id);
            $this->db->where('n.untuk_role', 'mahasiswa');
            $this->db->where('n.dibaca', 0);
            $this->db->order_by('n.tanggal_dibuat', 'DESC');
            $this->db->limit(5);
            $result = $this->db->get()->result();
            
            foreach($result as $row) {
                $notifikasi[] = [
                    'id' => $row->id,
                    'jenis' => $row->jenis,
                    'judul' => $row->judul,
                    'pesan' => $row->pesan,
                    'nama_pengirim' => $row->nama_pengirim ?: 'Sistem',
                    'created_at' => $row->tanggal_dibuat,
                    'proposal_id' => $row->proposal_id
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard notifikasi error: ' . $e->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'data' => $notifikasi]);
    }

    /**
     * NEW: Get statistik bimbingan
     */
    public function get_statistik_bimbingan()
    {
        $mahasiswa_id = $this->session->userdata('id');
        $stats = [
            'total_bimbingan' => 0,
            'bimbingan_bulan_ini' => 0,
            'status_validasi' => [
                'pending' => 0,
                'approved' => 0,
                'revision' => 0
            ]
        ];
        
        try {
            $proposal = $this->db->get_where('proposal_mahasiswa', ['mahasiswa_id' => $mahasiswa_id])->row();
            
            if ($proposal && $this->db->table_exists('jurnal_bimbingan')) {
                // Total bimbingan
                $this->db->where('proposal_id', $proposal->id);
                $stats['total_bimbingan'] = $this->db->count_all_results('jurnal_bimbingan');
                
                // Bimbingan bulan ini
                $this->db->where('proposal_id', $proposal->id);
                $this->db->where('MONTH(tanggal_bimbingan)', date('m'));
                $this->db->where('YEAR(tanggal_bimbingan)', date('Y'));
                $stats['bimbingan_bulan_ini'] = $this->db->count_all_results('jurnal_bimbingan');
                
                // Status validasi
                $this->db->select('status_validasi, COUNT(*) as jumlah');
                $this->db->from('jurnal_bimbingan');
                $this->db->where('proposal_id', $proposal->id);
                $this->db->group_by('status_validasi');
                $validasi_data = $this->db->get()->result();
                
                foreach ($validasi_data as $v) {
                    if ($v->status_validasi == '0') $stats['status_validasi']['pending'] = $v->jumlah;
                    if ($v->status_validasi == '1') $stats['status_validasi']['approved'] = $v->jumlah;
                    if ($v->status_validasi == '2') $stats['status_validasi']['revision'] = $v->jumlah;
                }
            }
        } catch (Exception $e) {
            log_message('error', 'Dashboard statistik error: ' . $e->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'data' => $stats]);
    }

    /**
     * NEW: Get recent activities
     */
    public function get_recent_activities()
    {
        $mahasiswa_id = $this->session->userdata('id');
        $activities = [];
        
        try {
            if ($this->db->table_exists('jurnal_bimbingan')) {
                $this->db->select('jb.*, pm.judul');
                $this->db->from('jurnal_bimbingan jb');
                $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
                $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
                $this->db->order_by('jb.created_at', 'DESC');
                $this->db->limit(5);
                $activities = $this->db->get()->result();
            }
        } catch (Exception $e) {
            log_message('error', 'Dashboard activities error: ' . $e->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'data' => $activities]);
    }
}

/* End of file Dashboard.php */