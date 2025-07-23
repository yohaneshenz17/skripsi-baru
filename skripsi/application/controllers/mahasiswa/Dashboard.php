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
     * FIXED: Get workflow progress - DISESUAIKAN DENGAN DATABASE EXISTING
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
            // Cek proposal mahasiswa - MENGGUNAKAN KOLOM YANG ADA
            $proposal = $this->db->get_where('proposal_mahasiswa', ['mahasiswa_id' => $mahasiswa_id])->row();
            
            if ($proposal) {
                $completed_stages = [];
                $current_stage = 'usulan_proposal';
                $progress_percentage = 0;
                
                // FIXED: Berdasarkan kolom status yang ada di database
                // 1. Cek status proposal
                if ($proposal->status_kaprodi == '1' && $proposal->status_pembimbing == '1') {
                    $completed_stages[] = 'usulan_proposal';
                    $current_stage = 'bimbingan';
                    $progress_percentage = 16.66;
                    
                    // 2. Cek apakah sudah ada bimbingan
                    $bimbingan_count = $this->db->where('proposal_id', $proposal->id)
                                              ->count_all_results('jurnal_bimbingan');
                    if ($bimbingan_count >= 8) { // Asumsi minimal 8 bimbingan
                        $completed_stages[] = 'bimbingan';
                        $current_stage = 'seminar_proposal';
                        $progress_percentage = 33.33;
                        
                        // 3. Cek status seminar proposal
                        if ($proposal->status_seminar_proposal == '1') {
                            $completed_stages[] = 'seminar_proposal';
                            $current_stage = 'penelitian';
                            $progress_percentage = 50;
                            
                            // 4. Cek izin penelitian
                            if ($proposal->status_izin_penelitian == '1') {
                                $completed_stages[] = 'penelitian';
                                $current_stage = 'seminar_skripsi';
                                $progress_percentage = 66.66;
                                
                                // 5. Cek seminar skripsi
                                if ($proposal->status_seminar_skripsi == '1') {
                                    $completed_stages[] = 'seminar_skripsi';
                                    $current_stage = 'publikasi';
                                    $progress_percentage = 83.33;
                                    
                                    // 6. Cek publikasi
                                    if ($proposal->status_publikasi == '1') {
                                        $completed_stages[] = 'publikasi';
                                        $current_stage = 'selesai';
                                        $progress_percentage = 100;
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Set completed stages
                foreach ($completed_stages as $stage) {
                    if (isset($progress['stages'][$stage])) {
                        $progress['stages'][$stage]['status'] = 'completed';
                        $progress['stages'][$stage]['color'] = 'success';
                    }
                }
                
                // Set current stage
                $progress['current_stage'] = $current_stage;
                $progress['current_stage_name'] = $progress['stages'][$current_stage]['name'] ?? 'Selesai';
                $progress['progress_percentage'] = $progress_percentage;
                
                // Set current stage as active (jika belum selesai)
                if ($current_stage !== 'selesai' && isset($progress['stages'][$current_stage])) {
                    $progress['stages'][$current_stage]['status'] = 'active';
                    $progress['stages'][$current_stage]['color'] = 'primary';
                }
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dashboard workflow progress error: ' . $e->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'data' => $progress]);
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
}

/* End of file Dashboard.php */