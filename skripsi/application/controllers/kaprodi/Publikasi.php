<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * =====================================================
 * CONTROLLER KAPRODI - PUBLIKASI TUGAS AKHIR
 * SIM Tugas Akhir STK Santo Yakobus Merauke - Phase 6
 * =====================================================
 * 
 * File: application/controllers/kaprodi/Publikasi.php
 * Role: Monitoring, oversight, dan override decisions untuk publikasi tugas akhir
 * Workflow: Kaprodi memantau proses mahasiswa → dosen → staf
 */
class Publikasi extends CI_Controller {

    private $kaprodi_id;
    private $prodi_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session']);
        $this->load->helper(['url', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk kaprodi
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if(!in_array($this->session->userdata('level'), ['3', '4'])) { // Kaprodi level 3 & 4
            show_error('Akses ditolak. Halaman khusus Kaprodi.', 403);
        }
        
        $this->kaprodi_id = $this->session->userdata('user_id');
        $this->prodi_id = $this->session->userdata('prodi_id');
    }

    /**
     * Dashboard monitoring publikasi untuk kaprodi
     * Menampilkan semua pengajuan publikasi di prodi dengan statistik
     */
    public function index() {
        // Query data publikasi berdasarkan prodi
        $this->db->select('
            pta.*,
            pm.id as proposal_id,
            pm.judul,
            pm.created_at as proposal_created_at,
            m.id as mahasiswa_id,
            m.nim,
            m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing
        ');
        
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id');
        $this->db->join('publikasi_tugas_akhir pta', 'pta.proposal_mahasiswa_id = pm.id', 'left');
        
        // Filter berdasarkan prodi kaprodi
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('pm.status', '1'); // Proposal yang sudah disetujui
        
        // Hanya tampilkan yang sudah mengajukan publikasi
        $this->db->where('pta.id IS NOT NULL');
        
        // Order by terbaru
        $this->db->order_by('pta.updated_at', 'DESC');
        
        $publikasi_list = $this->db->get()->result();
        
        // Statistik dashboard
        $summary_stats = $this->_get_summary_statistics();
        
        // Kirim data ke view
        $data = [
            'title' => 'Monitoring Publikasi Tugas Akhir',
            'publikasi_list' => $publikasi_list,
            'summary_stats' => $summary_stats
        ];
        
        // Load template
        $content = $this->load->view('kaprodi/publikasi/index', $data, TRUE);
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $content
        ]);
    }

    /**
     * Detail monitoring publikasi mahasiswa
     * Menampilkan detail lengkap publikasi dan timeline
     */
    public function detail($publikasi_id) {
        // Ambil data publikasi dengan detail
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data publikasi tidak ditemukan.');
            redirect('kaprodi/publikasi');
        }
        
        // Validasi prodi
        if ($publikasi->prodi_id != $this->prodi_id) {
            $this->session->set_flashdata('error', 'Data bukan dari program studi Anda.');
            redirect('kaprodi/publikasi');
        }
        
        // Get timeline dan jurnal bimbingan
        $timeline = $this->_get_publikasi_timeline($publikasi_id);
        $jurnal_bimbingan = $this->_get_jurnal_bimbingan($publikasi->proposal_mahasiswa_id);
        
        $data = [
            'title' => 'Detail Publikasi - ' . $publikasi->nama_mahasiswa,
            'publikasi' => $publikasi,
            'timeline' => $timeline,
            'jurnal_bimbingan' => $jurnal_bimbingan
        ];
        
        // Load template
        $content = $this->load->view('kaprodi/publikasi/detail', $data, TRUE);
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $content
        ]);
    }

    /**
     * Tracking progress publikasi mahasiswa tertentu
     * Menampilkan workflow 9 langkah secara detail
     */
    public function tracking($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi || $publikasi->prodi_id != $this->prodi_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau akses ditolak.');
            redirect('kaprodi/publikasi');
        }
        
        // Calculate workflow step berdasarkan status
        $step_current = $this->_calculate_workflow_step($publikasi);
        $timeline = $this->_get_publikasi_timeline($publikasi_id);
        
        $data = [
            'title' => 'Tracking Publikasi - ' . $publikasi->nama_mahasiswa,
            'publikasi' => $publikasi,
            'step_current' => $step_current,
            'timeline' => $timeline
        ];
        
        $content = $this->load->view('kaprodi/publikasi/tracking', $data, TRUE);
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $content
        ]);
    }

    /**
     * Override decision untuk emergency cases
     * Kaprodi dapat override keputusan dosen/staf dalam situasi darurat
     */
    public function override($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi || $publikasi->prodi_id != $this->prodi_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau akses ditolak.');
            redirect('kaprodi/publikasi');
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_override($publikasi);
        } else {
            $this->_show_override_form($publikasi);
        }
    }

    /**
     * Laporan publikasi per prodi dengan export
     */
    public function laporan() {
        $periode = $this->input->get('periode') ?: date('Y');
        $export = $this->input->get('export');
        
        // Data laporan
        $data = [
            'title' => 'Laporan Publikasi Tugas Akhir - ' . $periode,
            'periode' => $periode,
            'laporan_bulanan' => $this->_get_laporan_bulanan($periode),
            'laporan_mahasiswa' => $this->_get_laporan_mahasiswa($periode),
            'statistik_dosen' => $this->_get_statistik_per_dosen($periode),
            'summary_prodi' => $this->_get_summary_statistics()
        ];
        
        if ($export === 'pdf') {
            $this->_export_pdf($data);
        } else {
            $content = $this->load->view('kaprodi/publikasi/laporan', $data, TRUE);
            $this->load->view('template/kaprodi', [
                'title' => $data['title'],
                'content' => $content
            ]);
        }
    }

    // =================================================================
    // PRIVATE METHODS - HELPER FUNCTIONS
    // =================================================================

    /**
     * Get summary statistics untuk dashboard
     */
    private function _get_summary_statistics() {
        $stats = [
            'total_mahasiswa_prodi' => 0,
            'eligible_publikasi' => 0,
            'pengajuan_berjalan' => 0,
            'publikasi_selesai' => 0,
            'rata_waktu_proses' => 0
        ];
        
        // Total mahasiswa aktif di prodi
        $stats['total_mahasiswa_prodi'] = $this->db->where('prodi_id', $this->prodi_id)
                                                 ->where('status', '1')
                                                 ->count_all_results('mahasiswa');
        
        // Mahasiswa eligible publikasi (proposal approved + jurnal >= 16)
        $this->db->reset_query();
        $sql_eligible = "
            SELECT COUNT(DISTINCT pm.mahasiswa_id) as eligible
            FROM proposal_mahasiswa pm
            JOIN mahasiswa m ON pm.mahasiswa_id = m.id
            LEFT JOIN jurnal_bimbingan jb ON jb.proposal_id = pm.id AND jb.status_validasi = '1'
            LEFT JOIN publikasi_tugas_akhir pta ON pta.proposal_mahasiswa_id = pm.id
            WHERE m.prodi_id = ? 
                AND pm.status = '1'
                AND pta.id IS NULL
            GROUP BY pm.id
            HAVING COUNT(jb.id) >= 16
        ";
        $result = $this->db->query($sql_eligible, [$this->prodi_id]);
        $stats['eligible_publikasi'] = $result->num_rows();
        
        // Pengajuan yang sedang berjalan (belum completed)
        $this->db->reset_query();
        $this->db->join('mahasiswa m', 'publikasi_tugas_akhir.mahasiswa_id = m.id');
        $stats['pengajuan_berjalan'] = $this->db->where('m.prodi_id', $this->prodi_id)
                                              ->where_in('publikasi_tugas_akhir.status', 
                                                        ['draft', 'submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf'])
                                              ->count_all_results('publikasi_tugas_akhir');
        
        // Publikasi selesai
        $this->db->reset_query();
        $this->db->join('mahasiswa m', 'publikasi_tugas_akhir.mahasiswa_id = m.id');
        $stats['publikasi_selesai'] = $this->db->where('m.prodi_id', $this->prodi_id)
                                             ->where('publikasi_tugas_akhir.status', 'completed')
                                             ->count_all_results('publikasi_tugas_akhir');
        
        // Rata-rata waktu proses (hari)
        $this->db->reset_query();
        $this->db->select('AVG(DATEDIFF(tanggal_selesai, tanggal_pengajuan)) as rata_hari');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('pta.status', 'completed');
        $this->db->where('pta.tanggal_selesai IS NOT NULL');
        $result = $this->db->get()->row();
        $stats['rata_waktu_proses'] = $result ? round($result->rata_hari, 1) : 0;
        
        return $stats;
    }

    /**
     * Get detail publikasi dengan validasi prodi
     */
    private function _get_publikasi_detail($publikasi_id) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            m.prodi_id,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing, d.email as email_pembimbing,
            pm.id as proposal_mahasiswa_id,
            pm.judul as judul_proposal
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id');
        $this->db->where('pta.id', $publikasi_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get timeline publikasi
     */
    private function _get_publikasi_timeline($publikasi_id) {
        $this->db->select('*');
        $this->db->from('log_publikasi');
        $this->db->where('publikasi_id', $publikasi_id);
        $this->db->order_by('created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get jurnal bimbingan mahasiswa
     */
    private function _get_jurnal_bimbingan($proposal_id) {
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->order_by('tanggal_bimbingan', 'DESC');
        $this->db->limit(10); // 10 terakhir
        
        return $this->db->get()->result();
    }

    /**
     * Calculate workflow step berdasarkan status publikasi
     */
    private function _calculate_workflow_step($publikasi) {
        switch($publikasi->status) {
            case 'draft': return 2;
            case 'submitted': return 4;
            case 'review_pembimbing': return 5;
            case 'approved_pembimbing': return 6;
            case 'review_staf': return 7;
            case 'completed': return 9;
            case 'rejected': return 6;
            default: return 1;
        }
    }

    /**
     * Process override dari kaprodi
     */
    private function _process_override($publikasi) {
        $action = $this->input->post('override_action');
        $komentar = $this->input->post('komentar_kaprodi');
        $priority = $this->input->post('priority_level', TRUE);
        
        if (empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar override harus diisi.');
            redirect('kaprodi/publikasi/override/' . $publikasi->id);
            return;
        }
        
        try {
            $this->db->trans_start();
            
            // Update publikasi sesuai override action
            $update_data = ['updated_at' => date('Y-m-d H:i:s')];
            
            switch($action) {
                case 'approve':
                    $update_data['status'] = 'completed';
                    $update_data['status_staf'] = 'approved';
                    $update_data['tanggal_selesai'] = date('Y-m-d H:i:s');
                    $update_data['komentar_staf'] = 'Override approval by Kaprodi: ' . $komentar;
                    break;
                    
                case 'reject':
                    $update_data['status'] = 'rejected';
                    $update_data['komentar_staf'] = 'Override rejection by Kaprodi: ' . $komentar;
                    break;
                    
                case 'reset':
                    $update_data['status'] = 'draft';
                    $update_data['status_pembimbing'] = 'pending';
                    $update_data['status_staf'] = 'pending';
                    break;
                    
                case 'skip_review':
                    $update_data['status'] = 'approved_pembimbing';
                    $update_data['status_pembimbing'] = 'approved';
                    break;
            }
            
            $this->db->where('id', $publikasi->id)->update('publikasi_tugas_akhir', $update_data);
            
            // Log override action
            $log_data = [
                'publikasi_id' => $publikasi->id,
                'user_id' => $this->kaprodi_id,
                'user_role' => 'kaprodi',
                'user_name' => $this->session->userdata('nama'),
                'aktivitas' => 'override_' . $action,
                'deskripsi' => "Kaprodi override: {$action} - Priority: {$priority} - {$komentar}",
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('log_publikasi', $log_data);
            
            $this->db->trans_complete();
            
            $this->session->set_flashdata('success', 'Override berhasil dilakukan. Tindakan: ' . $action);
            
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Gagal melakukan override: ' . $e->getMessage());
        }
        
        redirect('kaprodi/publikasi/detail/' . $publikasi->id);
    }

    /**
     * Show override form
     */
    private function _show_override_form($publikasi) {
        $data = [
            'title' => 'Override Publikasi - ' . $publikasi->nama_mahasiswa,
            'publikasi' => $publikasi
        ];
        
        $content = $this->load->view('kaprodi/publikasi/override', $data, TRUE);
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $content
        ]);
    }

    /**
     * Get laporan bulanan
     */
    private function _get_laporan_bulanan($periode) {
        $sql = "
            SELECT 
                MONTH(pta.tanggal_pengajuan) as bulan,
                COUNT(*) as total_pengajuan,
                SUM(CASE WHEN pta.status = 'completed' THEN 1 ELSE 0 END) as selesai,
                AVG(CASE WHEN pta.status = 'completed' 
                    THEN DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) 
                    ELSE NULL END) as rata_hari_proses
            FROM publikasi_tugas_akhir pta
            JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE m.prodi_id = ? 
                AND YEAR(pta.tanggal_pengajuan) = ?
            GROUP BY MONTH(pta.tanggal_pengajuan)
            ORDER BY bulan
        ";
        
        return $this->db->query($sql, [$this->prodi_id, $periode])->result();
    }

    /**
     * Get laporan per mahasiswa
     */
    private function _get_laporan_mahasiswa($periode) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing,
            pm.judul,
            DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) as lama_proses_hari
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('YEAR(pta.tanggal_pengajuan)', $periode);
        $this->db->order_by('pta.tanggal_pengajuan', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get statistik per dosen pembimbing
     */
    private function _get_statistik_per_dosen($periode) {
        $sql = "
            SELECT 
                d.nama as nama_dosen,
                COUNT(pta.id) as total_mahasiswa,
                SUM(CASE WHEN pta.status = 'completed' THEN 1 ELSE 0 END) as selesai,
                AVG(CASE WHEN pta.status = 'completed' 
                    THEN DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) 
                    ELSE NULL END) as rata_hari_proses
            FROM dosen d
            LEFT JOIN proposal_mahasiswa pm ON d.id = pm.dosen_id
            LEFT JOIN publikasi_tugas_akhir pta ON pta.proposal_mahasiswa_id = pm.id 
                AND YEAR(pta.tanggal_pengajuan) = ?
            JOIN mahasiswa m ON pm.mahasiswa_id = m.id
            WHERE d.prodi_id = ?
                AND d.level IN ('2', '4')
                AND pm.id IS NOT NULL
            GROUP BY d.id
            ORDER BY total_mahasiswa DESC
        ";
        
        return $this->db->query($sql, [$periode, $this->prodi_id])->result();
    }

    /**
     * Export laporan ke PDF
     */
    private function _export_pdf($data) {
        // TODO: Implement PDF export using TCPDF
        echo "Export PDF functionality - Implementation needed";
    }
    
    /**
     * Calculate duration between two dates
     * Tambahkan ke controller Kaprodi/Publikasi.php
     */
    private function _calculate_duration($start_date, $end_date) {
        if (empty($start_date) || empty($end_date)) {
            return 0;
        }
        
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $diff = $start->diff($end);
        
        return $diff->days;
    }

    /**
     * Get workflow step description
     */
    private function _get_step_description($step) {
        $descriptions = [
            1 => 'Mahasiswa memenuhi syarat (16+ jurnal tervalidasi)',
            2 => 'Mahasiswa mengisi form pengajuan publikasi',
            3 => 'Mahasiswa mengirim ajuan ke dosen pembimbing',
            4 => 'Dosen pembimbing menerima pengajuan',
            5 => 'Dosen pembimbing melakukan review',
            6 => 'Dosen memberikan keputusan (approve/reject)',
            7 => 'Staf memvalidasi dan input repository',
            8 => 'Sistem memproses completion',
            9 => 'Publikasi selesai, surat dapat didownload'
        ];
        
        return $descriptions[$step] ?? 'Unknown step';
    }

    /**
     * Get status badge info
     */
    private function _get_status_badge($status) {
        $badges = [
            'draft' => ['class' => 'badge-secondary', 'text' => 'Draft'],
            'submitted' => ['class' => 'badge-info', 'text' => 'Diajukan'],
            'review_pembimbing' => ['class' => 'badge-warning', 'text' => 'Review Dosen'],
            'approved_pembimbing' => ['class' => 'badge-primary', 'text' => 'Approved Dosen'],
            'review_staf' => ['class' => 'badge-primary', 'text' => 'Review Staf'],
            'completed' => ['class' => 'badge-success', 'text' => 'Selesai'],
            'rejected' => ['class' => 'badge-danger', 'text' => 'Ditolak']
        ];
        
        return $badges[$status] ?? ['class' => 'badge-secondary', 'text' => 'Unknown'];
    }

    /**
     * Send notification email (placeholder)
     */
    private function _send_notification($type, $data) {
        // TODO: Implement email notification
        // Contoh: override notification ke admin, status change ke mahasiswa, etc.
        
        try {
            $this->load->library('email');
            
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'your-smtp-host',
                'smtp_port' => 587,
                'smtp_user' => 'your-email@stkyakobus.ac.id',
                'smtp_pass' => 'your-password',
                'mailtype' => 'html',
                'charset' => 'utf-8'
            ];
            
            $this->email->initialize($config);
            
            switch($type) {
                case 'override_notification':
                    $this->_send_override_notification($data);
                    break;
                case 'status_update':
                    $this->_send_status_update($data);
                    break;
            }
            
            return true;
        } catch (Exception $e) {
            log_message('error', 'Failed to send notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log kaprodi activity
     */
    private function _log_kaprodi_activity($publikasi_id, $activity, $description) {
        $log_data = [
            'publikasi_id' => $publikasi_id,
            'user_id' => $this->kaprodi_id,
            'user_role' => 'kaprodi',
            'user_name' => $this->session->userdata('nama'),
            'aktivitas' => $activity,
            'deskripsi' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('log_publikasi', $log_data);
    }

    /**
     * Generate audit trail for override actions
     */
    private function _generate_audit_trail($publikasi_id, $action_type, $details) {
        $audit_data = [
            'publikasi_id' => $publikasi_id,
            'user_id' => $this->kaprodi_id,
            'user_role' => 'kaprodi',
            'action_type' => $action_type,
            'action_details' => json_encode($details),
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert to audit table (create if not exists)
        $this->db->insert('audit_trail', $audit_data);
        
        return true;
    }

    /**
     * Check if kaprodi has permission for override
     */
    private function _can_override($publikasi_status) {
        $overrideable_statuses = [
            'review_pembimbing',
            'approved_pembimbing', 
            'review_staf',
            'rejected'
        ];
        
        return in_array($publikasi_status, $overrideable_statuses);
    }

    /**
     * Get mahasiswa eligible count
     */
    private function _count_eligible_mahasiswa() {
        $sql = "
            SELECT COUNT(DISTINCT pm.mahasiswa_id) as total
            FROM proposal_mahasiswa pm
            JOIN mahasiswa m ON pm.mahasiswa_id = m.id
            LEFT JOIN jurnal_bimbingan jb ON jb.proposal_id = pm.id AND jb.status_validasi = '1'
            LEFT JOIN publikasi_tugas_akhir pta ON pta.proposal_mahasiswa_id = pm.id
            WHERE m.prodi_id = ? 
                AND pm.status = '1'
                AND pta.id IS NULL
            GROUP BY pm.id
            HAVING COUNT(jb.id) >= 16
        ";
        
        $result = $this->db->query($sql, [$this->prodi_id]);
        return $result->num_rows();
    }

    /**
     * Format file size
     */
    private function _format_file_size($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Validate override action
     */
    private function _validate_override_action($action) {
        $valid_actions = ['approve', 'reject', 'reset', 'skip_review'];
        return in_array($action, $valid_actions);
    }

    /**
     * Get performance metrics
     */
    private function _get_performance_metrics() {
        // Average processing time
        $this->db->select('AVG(DATEDIFF(tanggal_selesai, tanggal_pengajuan)) as avg_days');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('pta.status', 'completed');
        $this->db->where('pta.tanggal_selesai IS NOT NULL');
        $avg_result = $this->db->get()->row();
        
        // Success rate
        $this->db->reset_query();
        $total_submitted = $this->db->join('mahasiswa m', 'publikasi_tugas_akhir.mahasiswa_id = m.id')
                                   ->where('m.prodi_id', $this->prodi_id)
                                   ->where_in('publikasi_tugas_akhir.status', ['submitted', 'review_pembimbing', 'approved_pembimbing', 'review_staf', 'completed'])
                                   ->count_all_results('publikasi_tugas_akhir');
        
        $this->db->reset_query();
        $completed = $this->db->join('mahasiswa m', 'publikasi_tugas_akhir.mahasiswa_id = m.id')
                              ->where('m.prodi_id', $this->prodi_id)
                              ->where('publikasi_tugas_akhir.status', 'completed')
                              ->count_all_results('publikasi_tugas_akhir');
        
        return [
            'avg_processing_days' => $avg_result ? round($avg_result->avg_days, 1) : 0,
            'success_rate' => $total_submitted > 0 ? round(($completed / $total_submitted) * 100, 1) : 0,
            'total_submissions' => $total_submitted,
            'completed_count' => $completed
        ];
    }
}