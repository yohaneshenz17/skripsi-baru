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
     * ✅ PERBAIKAN: Method get_workflow_progress() yang benar
     */
    public function get_workflow_progress()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            // Ambil data proposal mahasiswa terbaru
            $this->db->select('
                pm.id, pm.status, pm.status_kaprodi, pm.status_pembimbing, 
                pm.workflow_status, pm.dosen_id,
                pm.status_seminar_proposal, pm.status_seminar_skripsi, pm.status_publikasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('pm.created_at', 'DESC');
            $this->db->limit(1);
            
            $proposal = $this->db->get()->row();
            
            if (!$proposal) {
                // Belum ada proposal
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'current_stage' => 'belum_mulai',
                        'current_stage_name' => 'Belum Memulai',
                        'progress_percentage' => 0,
                        'stages' => $this->_get_default_stages()
                    ]
                ]);
                return;
            }

            // ✅ Hitung status berdasarkan data aktual
            $stages = $this->_calculate_stages_status($proposal);
            $progress_data = $this->_calculate_progress_data($proposal, $stages);
            
            echo json_encode([
                'status' => 'success',
                'data' => $progress_data
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard workflow error: ' . $e->getMessage());
            
            // Return default safe response
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'current_stage' => 'usulan_proposal',
                    'current_stage_name' => 'Usulan Proposal',
                    'progress_percentage' => 0,
                    'stages' => $this->_get_default_stages()
                ]
            ]);
        }
    }

    /**
     * FIXED: Get notifikasi terbaru - PERBAIKI QUERY JOIN
     */
    public function get_notifikasi()
    {
        $mahasiswa_id = $this->session->userdata('id');
        $notifikasi = [];
        
        try {
            // PERBAIKAN: Query yang lebih sederhana dan aman
            $this->db->select('n.*, COALESCE(d.nama, "Sistem") as nama_pengirim');
            $this->db->from('notifikasi n');
            $this->db->join('proposal_mahasiswa pm', 'n.proposal_id = pm.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
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
                    'nama_pengirim' => $row->nama_pengirim,
                    'created_at' => $row->tanggal_dibuat,
                    'proposal_id' => $row->proposal_id
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard notifikasi error: ' . $e->getMessage());
            
            // FALLBACK: Jika query JOIN bermasalah, gunakan query sederhana
            try {
                $this->db->select('id, jenis, judul, pesan, tanggal_dibuat, proposal_id');
                $this->db->from('notifikasi');
                $this->db->where('user_id', $mahasiswa_id);
                $this->db->where('untuk_role', 'mahasiswa');
                $this->db->where('dibaca', 0);
                $this->db->order_by('tanggal_dibuat', 'DESC');
                $this->db->limit(5);
                $result = $this->db->get()->result();
                
                foreach($result as $row) {
                    $notifikasi[] = [
                        'id' => $row->id,
                        'jenis' => $row->jenis,
                        'judul' => $row->judul,
                        'pesan' => $row->pesan,
                        'nama_pengirim' => 'Sistem',
                        'created_at' => $row->tanggal_dibuat,
                        'proposal_id' => $row->proposal_id
                    ];
                }
            } catch (Exception $e2) {
                log_message('error', 'Dashboard fallback notifikasi error: ' . $e2->getMessage());
                // Return empty array jika semua gagal
            }
        }
        
        echo json_encode(['status' => 'success', 'data' => $notifikasi]);
    }
    
    /**
     * PERBAIKAN: Method untuk mark notifikasi sebagai dibaca
     */
    public function mark_notifikasi_dibaca()
    {
        $notifikasi_id = $this->input->post('notifikasi_id');
        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            $this->db->where('id', $notifikasi_id);
            $this->db->where('user_id', $mahasiswa_id);
            $this->db->update('notifikasi', ['dibaca' => 1]);
            
            echo json_encode(['status' => 'success', 'message' => 'Notifikasi ditandai sebagai dibaca']);
        } catch (Exception $e) {
            log_message('error', 'Error mark notifikasi dibaca: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Gagal update notifikasi']);
        }
    }

    /**
     * FIXED: Get statistik bimbingan
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
            
            if ($proposal) {
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
     * FIXED: Get recent activities - MENGGUNAKAN TABEL JURNAL_BIMBINGAN
     */
    public function get_recent_activities()
    {
        $mahasiswa_id = $this->session->userdata('id');
        $activities = [];
        
        try {
            // Query ke jurnal_bimbingan yang sudah ada
            $this->db->select('jb.*, pm.judul');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('jb.created_at', 'DESC');
            $this->db->limit(5);
            $activities = $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard activities error: ' . $e->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'data' => $activities]);
    }

    /**
     * ✅ METHOD HELPER BARU: Hitung status setiap tahap berdasarkan data aktual
     */
private function _calculate_stages_status($proposal)
{
    // Default semua tahap pending
    $stages = [
        'usulan_proposal' => ['name' => 'Usulan Proposal', 'status' => 'pending', 'color' => 'secondary'],
        'bimbingan' => ['name' => 'Bimbingan', 'status' => 'pending', 'color' => 'secondary'],
        'seminar_proposal' => ['name' => 'Seminar Proposal', 'status' => 'pending', 'color' => 'secondary'],
        'penelitian' => ['name' => 'Penelitian', 'status' => 'pending', 'color' => 'secondary'],
        'seminar_skripsi' => ['name' => 'Seminar Skripsi', 'status' => 'pending', 'color' => 'secondary'],
        'publikasi' => ['name' => 'Publikasi', 'status' => 'pending', 'color' => 'secondary']
    ];
    
    // ✅ PERBAIKAN TAHAP 1: Usulan Proposal
    // Debug: Proposal ID = 49, Status Kaprodi = 0, Status Pembimbing = 0
    
    // Cek apakah proposal sudah dibuat
    if (!empty($proposal->id) && $proposal->id > 0) {
        
        // Cek apakah sudah selesai semua (kaprodi DAN pembimbing setuju)
        if ($proposal->status_kaprodi == '1' && $proposal->status_pembimbing == '1') {
            // SELESAI: Kaprodi setuju DAN pembimbing setuju
            $stages['usulan_proposal'] = ['name' => 'Usulan Proposal', 'status' => 'completed', 'color' => 'success'];
        } else {
            // ✅ PROSES: Proposal sudah dibuat tapi belum selesai semua
            // Untuk kasus Yosefa: ID=49, status_kaprodi=0, status_pembimbing=0
            // Ini masuk kondisi "PROSES" karena proposal sudah dibuat
            $stages['usulan_proposal'] = ['name' => 'Usulan Proposal', 'status' => 'active', 'color' => 'primary'];
        }
    }
    // Jika proposal->id = 0 atau null, tetap 'pending'
    
    // TAHAP 2: Bimbingan (hanya jika usulan proposal SELESAI)
    if ($stages['usulan_proposal']['status'] == 'completed') {
        // Hitung jurnal bimbingan tervalidasi
        $this->db->where('proposal_id', $proposal->id);
        $this->db->where('status_validasi', '1');
        $jurnal_count = $this->db->count_all_results('jurnal_bimbingan');
        
        if ($jurnal_count >= 8) {
            $stages['bimbingan'] = ['name' => 'Bimbingan', 'status' => 'completed', 'color' => 'success'];
        } elseif ($jurnal_count > 0) {
            $stages['bimbingan'] = ['name' => 'Bimbingan', 'status' => 'active', 'color' => 'primary'];
        }
    }
    
    // TAHAP 3: Seminar Proposal (hanya jika bimbingan SELESAI)
    if ($stages['bimbingan']['status'] == 'completed') {
        if ($proposal->status_seminar_proposal == '1') {
            $stages['seminar_proposal'] = ['name' => 'Seminar Proposal', 'status' => 'completed', 'color' => 'success'];
        } else {
            $stages['seminar_proposal'] = ['name' => 'Seminar Proposal', 'status' => 'active', 'color' => 'primary'];
        }
    }
    
    // TAHAP 4: Penelitian
    if ($stages['seminar_proposal']['status'] == 'completed') {
        if ($proposal->workflow_status == 'penelitian' || $proposal->workflow_status == 'seminar_skripsi' || $proposal->workflow_status == 'publikasi') {
            $stages['penelitian'] = ['name' => 'Penelitian', 'status' => 'completed', 'color' => 'success'];
        } else {
            $stages['penelitian'] = ['name' => 'Penelitian', 'status' => 'active', 'color' => 'primary'];
        }
    }
    
    // TAHAP 5: Seminar Skripsi
    if ($stages['penelitian']['status'] == 'completed') {
        if ($proposal->status_seminar_skripsi == '1') {
            $stages['seminar_skripsi'] = ['name' => 'Seminar Skripsi', 'status' => 'completed', 'color' => 'success'];
        } else {
            $stages['seminar_skripsi'] = ['name' => 'Seminar Skripsi', 'status' => 'active', 'color' => 'primary'];
        }
    }
    
    // TAHAP 6: Publikasi
    if ($stages['seminar_skripsi']['status'] == 'completed') {
        if ($proposal->status_publikasi == '1') {
            $stages['publikasi'] = ['name' => 'Publikasi', 'status' => 'completed', 'color' => 'success'];
        } else {
            $stages['publikasi'] = ['name' => 'Publikasi', 'status' => 'active', 'color' => 'primary'];
        }
    }
    
    return $stages;
}

    /**
     * ✅ METHOD HELPER BARU: Hitung progress percentage dan current stage
     */
private function _calculate_progress_data($proposal, $stages)
{
    $total_stages = 6;
    $completed_count = 0;
    $active_count = 0;
    $current_stage = 'usulan_proposal';
    $current_stage_name = 'Usulan Proposal';
    
    // Hitung berapa tahap yang selesai dan active
    foreach ($stages as $key => $stage) {
        if ($stage['status'] == 'completed') {
            $completed_count++;
        } elseif ($stage['status'] == 'active') {
            $active_count++;
        }
    }
    
    // ✅ PERBAIKAN: Hitung progress percentage yang benar
    $progress_percentage = 0;
    
    if ($completed_count > 0) {
        // Jika ada yang completed, hitung berdasarkan completed (setiap tahap = 16.67%)
        $progress_percentage = round(($completed_count / $total_stages) * 100);
    } elseif ($active_count > 0) {
        // ✅ KASUS YOSEFA: Ada tahap active (usulan_proposal sedang proses)
        // Progress minimal 16% untuk tahap pertama yang sedang proses
        $progress_percentage = 16;
    } else {
        // Tidak ada progress sama sekali
        $progress_percentage = 0;
    }
    
    // ✅ PERBAIKAN: Tentukan current stage dan nama
    if ($completed_count == $total_stages) {
        // Semua selesai
        $current_stage = 'publikasi';
        $current_stage_name = 'Selesai';
    } else {
        // Cari tahap yang sedang aktif atau pending pertama
        foreach ($stages as $key => $stage) {
            if ($stage['status'] == 'active') {
                $current_stage = $key;
                $current_stage_name = $stage['name'];
                break; // Ambil yang pertama active
            } elseif ($stage['status'] == 'pending' && $current_stage == 'usulan_proposal') {
                // Jika tidak ada yang active, ambil pending pertama
                $current_stage = $key;
                $current_stage_name = $stage['name'];
                break;
            }
        }
    }
    
    // ✅ DEBUGGING: Log untuk memastikan logic berjalan
    log_message('debug', 'Progress Debug - Completed: ' . $completed_count . ', Active: ' . $active_count . ', Progress: ' . $progress_percentage . '%');
    
    return [
        'current_stage' => $current_stage,
        'current_stage_name' => $current_stage_name,
        'progress_percentage' => $progress_percentage,
        'stages' => $stages
    ];
}

    /**
     * ✅ METHOD HELPER BARU: Default stages untuk kasus belum ada proposal
     */
    private function _get_default_stages()
    {
        return [
            'usulan_proposal' => ['name' => 'Usulan Proposal', 'status' => 'pending', 'color' => 'secondary'],
            'bimbingan' => ['name' => 'Bimbingan', 'status' => 'pending', 'color' => 'secondary'],
            'seminar_proposal' => ['name' => 'Seminar Proposal', 'status' => 'pending', 'color' => 'secondary'],
            'penelitian' => ['name' => 'Penelitian', 'status' => 'pending', 'color' => 'secondary'],
            'seminar_skripsi' => ['name' => 'Seminar Skripsi', 'status' => 'pending', 'color' => 'secondary'],
            'publikasi' => ['name' => 'Publikasi', 'status' => 'pending', 'color' => 'secondary']
        ];
    }
}

/* End of file Dashboard.php */