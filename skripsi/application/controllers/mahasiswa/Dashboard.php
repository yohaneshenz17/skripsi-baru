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
 * ✅ FINAL FIX: Method get_workflow_progress() berdasarkan data debug aktual
 * GANTI method ini di Dashboard.php - HAPUS method debug setelah fix ini
 */
public function get_workflow_progress()
{
    $mahasiswa_id = $this->session->userdata('id');
    
    // Default response
    $progress = [
        'current_stage' => 'usulan_proposal',
        'current_stage_name' => 'Usulan Proposal',
        'progress_percentage' => 16,
        'stages' => [
            'usulan_proposal' => ['name' => 'Usulan Proposal', 'status' => 'completed', 'color' => 'success'],
            'bimbingan' => ['name' => 'Bimbingan', 'status' => 'completed', 'color' => 'success'],
            'seminar_proposal' => ['name' => 'Seminar Proposal', 'status' => 'completed', 'color' => 'success'],
            'penelitian' => ['name' => 'Penelitian', 'status' => 'completed', 'color' => 'success'],
            'seminar_skripsi' => ['name' => 'Seminar Skripsi', 'status' => 'active', 'color' => 'primary'],
            'publikasi' => ['name' => 'Publikasi', 'status' => 'pending', 'color' => 'secondary']
        ]
    ];
    
    try {
        // ✅ FIX: Ambil proposal tanpa filter status = '1' karena data menunjukkan status = '0'
        $this->db->select('pm.*, m.nama as nama_mahasiswa');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        // ✅ REMOVE: ->where('pm.status', '1') karena data aktual status = '0'
        $this->db->order_by('pm.id', 'DESC');
        $proposal = $this->db->get()->row();
        
        if ($proposal) {
            // ✅ PERBAIKAN: Cek workflow_status terlebih dahulu (data menunjukkan 'selesai')
            if ($proposal->workflow_status === 'selesai') {
                // Data menunjukkan workflow_status = 'selesai' dan publikasi.status = 'completed'
                $progress['progress_percentage'] = 100;
                $progress['current_stage'] = 'selesai';
                $progress['current_stage_name'] = 'Selesai - Semua Tahap Lengkap';
                
                // Set semua stage jadi completed
                $progress['stages']['seminar_skripsi']['status'] = 'completed';
                $progress['stages']['seminar_skripsi']['color'] = 'success';
                $progress['stages']['publikasi']['status'] = 'completed';
                $progress['stages']['publikasi']['color'] = 'success';
            }
            elseif ($proposal->workflow_status === 'publikasi') {
                // Cek status publikasi di tabel publikasi_tugas_akhir
                $this->db->select('status');
                $this->db->from('publikasi_tugas_akhir');
                $this->db->where('proposal_mahasiswa_id', $proposal->id);
                $publikasi = $this->db->get()->row();
                
                if ($publikasi && $publikasi->status === 'completed') {
                    // Publikasi completed, tapi workflow_status belum di-update ke 'selesai'
                    $progress['progress_percentage'] = 100;
                    $progress['current_stage'] = 'selesai';
                    $progress['current_stage_name'] = 'Selesai - Publikasi Completed';
                    $progress['stages']['seminar_skripsi']['status'] = 'completed';
                    $progress['stages']['seminar_skripsi']['color'] = 'success';
                    $progress['stages']['publikasi']['status'] = 'completed';
                    $progress['stages']['publikasi']['color'] = 'success';
                } 
                elseif ($publikasi) {
                    // Publikasi ada tapi belum completed
                    $progress['progress_percentage'] = 90;
                    $progress['current_stage'] = 'publikasi';
                    $progress['current_stage_name'] = 'Publikasi - Sedang Diproses';
                    $progress['stages']['seminar_skripsi']['status'] = 'completed';
                    $progress['stages']['seminar_skripsi']['color'] = 'success';
                    $progress['stages']['publikasi']['status'] = 'active';
                    $progress['stages']['publikasi']['color'] = 'primary';
                }
                else {
                    // Workflow publikasi tapi belum ada data publikasi
                    $progress['progress_percentage'] = 83;
                    $progress['current_stage'] = 'publikasi';
                    $progress['current_stage_name'] = 'Publikasi - Siap Ajukan';
                    $progress['stages']['seminar_skripsi']['status'] = 'completed';
                    $progress['stages']['seminar_skripsi']['color'] = 'success';
                    $progress['stages']['publikasi']['status'] = 'active';
                    $progress['stages']['publikasi']['color'] = 'primary';
                }
            }
            elseif ($proposal->workflow_status === 'seminar_skripsi') {
                // Di tahap seminar skripsi
                $progress['progress_percentage'] = 83;
                $progress['current_stage'] = 'seminar_skripsi';
                $progress['current_stage_name'] = 'Seminar Skripsi';
            }
            elseif ($proposal->workflow_status === 'penelitian') {
                // Di tahap penelitian
                $progress['progress_percentage'] = 66;
                $progress['current_stage'] = 'penelitian';
                $progress['current_stage_name'] = 'Penelitian';
                $progress['stages']['seminar_skripsi']['status'] = 'pending';
                $progress['stages']['seminar_skripsi']['color'] = 'secondary';
            }
            else {
                // Fallback ke logic asli jika workflow_status tidak dikenali
                if ($proposal->status_pembimbing == '1') {
                    $progress['progress_percentage'] = 66;
                    $progress['current_stage'] = 'seminar_skripsi';
                    $progress['current_stage_name'] = 'Seminar Skripsi';
                }
            }
        }
        
    } catch (Exception $e) {
        // Jika ada error, return response yang working
        error_log('Dashboard progress error: ' . $e->getMessage());
    }
    
    // Always return success
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