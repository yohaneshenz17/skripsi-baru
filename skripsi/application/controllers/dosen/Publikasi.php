<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Publikasi untuk Dosen - Phase 6
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * 
 * Features:
 * 1. Dashboard review publikasi dengan statistics
 * 2. Detail pengajuan publikasi mahasiswa bimbingan
 * 3. Review dan approve/reject publikasi
 * 4. Riwayat detail review pengajuan mahasiswa
 * 5. Email notification ke staf dan mahasiswa
 * 
 * File: application/controllers/dosen/Publikasi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Publikasi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Following Mahasiswa Publikasi Pattern)
 */
class Publikasi extends CI_Controller {

    private $dosen_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session', 'form_validation', 'email']);
        $this->load->helper(['url', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk dosen
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if(!in_array($this->session->userdata('level'), ['2', '4'])) { // Dosen atau Kaprodi
            show_error('Akses ditolak. Halaman khusus dosen.', 403);
        }
        
        $this->dosen_id = $this->session->userdata('id');
    }

    /**
     * Dashboard publikasi untuk dosen
     * Menggunakan template existing dosen.php
     */
    public function index() {
        // Prepare data untuk view
        $view_data = [
            'pengajuan_review' => $this->_get_pengajuan_perlu_review(),
            'riwayat_review' => $this->_get_riwayat_review(),
            'stats' => $this->_get_statistik_dosen()
        ];
        
        // Template data untuk dosen.php
        $data = [
            'title' => 'Review Publikasi Tugas Akhir',
            'content' => $this->load->view('dosen/publikasi/index', $view_data, TRUE),
            'script' => $this->_get_index_script()
        ];
        
        // Load template existing
        $this->load->view('template/dosen', $data);
    }

    /**
     * Detail dan review pengajuan publikasi
     */
    public function review($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('dosen/publikasi');
        }
        
        // Validasi ownership - harus dosen pembimbing
        if ($publikasi->dosen_pembimbing_id != $this->dosen_id) {
            $this->session->set_flashdata('error', 'Anda bukan dosen pembimbing untuk mahasiswa ini.');
            redirect('dosen/publikasi');
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_review($publikasi);
        } else {
            $this->_show_review_form($publikasi);
        }
    }

    /**
     * Riwayat detail review pengajuan mahasiswa
     */
    public function riwayat($mahasiswa_id = null) {
        // Debug logging untuk troubleshooting
        log_message('debug', 'Riwayat method called - mahasiswa_id: ' . $mahasiswa_id . ', dosen_id: ' . $this->dosen_id);
        
        if (!$mahasiswa_id) {
            $this->session->set_flashdata('error', 'ID mahasiswa tidak valid.');
            redirect('dosen/publikasi');
            return;
        }
        
        // ✅ FIX: Validasi ownership berdasarkan publikasi, bukan proposal
        $is_bimbingan = $this->_is_mahasiswa_bimbingan_publikasi($mahasiswa_id);
        
        if (!$is_bimbingan) {
            log_message('error', 'Ownership validation failed - mahasiswa_id: ' . $mahasiswa_id . ', dosen_id: ' . $this->dosen_id);
            $this->session->set_flashdata('error', 'Mahasiswa bukan bimbingan Anda atau tidak ada publikasi.');
            redirect('dosen/publikasi');
            return;
        }
        
        // Get data mahasiswa
        $mahasiswa = $this->_get_mahasiswa_data($mahasiswa_id);
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
            redirect('dosen/publikasi');
            return;
        }
        
        // Get riwayat lengkap publikasi mahasiswa
        $view_data = [
            'mahasiswa' => $mahasiswa,
            'riwayat_publikasi' => $this->_get_riwayat_publikasi_mahasiswa($mahasiswa_id),
            'detail_reviews' => $this->_get_detail_reviews($mahasiswa_id)
        ];
        
        // Template data untuk dosen.php
        $data = [
            'title' => 'Riwayat Review Publikasi - ' . $mahasiswa->nama,
            'content' => $this->load->view('dosen/publikasi/riwayat', $view_data, TRUE),
            'script' => ''
        ];
        
        $this->load->view('template/dosen', $data);
    }

    /**
     * Quick approve pengajuan publikasi (AJAX)
     */
    public function quick_approve($publikasi_id) {
        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi || $publikasi->dosen_pembimbing_id != $this->dosen_id) {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan atau akses ditolak']);
            return;
        }
        
        if ($publikasi->status_pembimbing !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'Pengajuan sudah direview sebelumnya']);
            return;
        }
        
        // Process quick approve
        $result = $this->_process_quick_approve($publikasi);
        
        echo json_encode($result);
    }

/**
 * ✅ DEBUG METHOD: Untuk troubleshoot masalah publikasi tidak muncul di dosen
 * Tambahkan method ini ke controller dosen publikasi untuk debugging
 */
public function debug_publikasi($mahasiswa_id = null) {
    // ✅ SECURITY: Hanya untuk development/admin
    if (ENVIRONMENT !== 'development') {
        show_404();
        return;
    }
    
    header('Content-Type: text/plain');
    echo "=== PUBLIKASI DEBUG REPORT ===\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    echo "Dosen ID: " . $this->dosen_id . "\n\n";
    
    // 1. Check data publikasi di database
    echo "1. PUBLIKASI TUGAS AKHIR TABLE:\n";
    
    $query = "SELECT id, mahasiswa_id, dosen_pembimbing_id, status, status_pembimbing, 
              tanggal_pengajuan, tanggal_review_pembimbing, created_at, updated_at 
              FROM publikasi_tugas_akhir";
    
    if ($mahasiswa_id) {
        $query .= " WHERE mahasiswa_id = " . intval($mahasiswa_id);
    } else {
        $query .= " WHERE dosen_pembimbing_id = " . intval($this->dosen_id);
    }
    
    $query .= " ORDER BY updated_at DESC LIMIT 10";
    
    $results = $this->db->query($query)->result_array();
    
    if (empty($results)) {
        echo "   >>> TIDAK ADA DATA PUBLIKASI DITEMUKAN!\n\n";
    } else {
        foreach ($results as $row) {
            echo "   ID: {$row['id']}\n";
            echo "   Mahasiswa ID: {$row['mahasiswa_id']}\n";
            echo "   Dosen Pembimbing ID: {$row['dosen_pembimbing_id']}\n";
            echo "   Status: {$row['status']}\n";
            echo "   Status Pembimbing: {$row['status_pembimbing']}\n";
            echo "   Tanggal Pengajuan: {$row['tanggal_pengajuan']}\n";
            echo "   Tanggal Review: {$row['tanggal_review_pembimbing']}\n";
            echo "   Created: {$row['created_at']}\n";
            echo "   Updated: {$row['updated_at']}\n";
            echo "   ---\n";
        }
    }
    
    // 2. Test query method dari model
    echo "2. MODEL METHOD get_pending_dosen_review():\n";
    
    $pending = $this->publikasi->get_pending_dosen_review($this->dosen_id);
    
    if (empty($pending)) {
        echo "   >>> TIDAK ADA DATA PENDING REVIEW!\n";
        
        // Debug query model
        echo "   >>> DEBUGGING QUERY CONDITIONS:\n";
        echo "   >>> SELECT * FROM publikasi_tugas_akhir \n";
        echo "   >>> WHERE dosen_pembimbing_id = {$this->dosen_id}\n";
        echo "   >>> AND status_pembimbing = 'pending'\n";
        echo "   >>> AND status IN ('submitted', 'review_pembimbing')\n\n";
        
        // Check each condition separately
        $total = $this->db->where('dosen_pembimbing_id', $this->dosen_id)->count_all_results('publikasi_tugas_akhir');
        echo "   >>> Total publikasi dengan dosen_pembimbing_id {$this->dosen_id}: {$total}\n";
        
        $this->db->reset_query();
        $pending_status = $this->db->where('dosen_pembimbing_id', $this->dosen_id)
                                  ->where('status_pembimbing', 'pending')
                                  ->count_all_results('publikasi_tugas_akhir');
        echo "   >>> Total dengan status_pembimbing = 'pending': {$pending_status}\n";
        
        $this->db->reset_query();
        $submitted = $this->db->where('dosen_pembimbing_id', $this->dosen_id)
                             ->where('status_pembimbing', 'pending')
                             ->where_in('status', ['submitted', 'review_pembimbing'])
                             ->count_all_results('publikasi_tugas_akhir');
        echo "   >>> Total yang memenuhi semua kondisi: {$submitted}\n\n";
        
    } else {
        echo "   >>> DITEMUKAN " . count($pending) . " DATA PENDING:\n";
        foreach ($pending as $pub) {
            echo "   ID: {$pub->id} - {$pub->nama_mahasiswa} - Status: {$pub->status}\n";
        }
        echo "\n";
    }
    
    // 3. Check recent database changes
    echo "3. RECENT UPDATES (Last 24 hours):\n";
    
    $recent = $this->db->select('id, mahasiswa_id, status, status_pembimbing, updated_at')
                      ->where('dosen_pembimbing_id', $this->dosen_id)
                      ->where('updated_at >', date('Y-m-d H:i:s', strtotime('-24 hours')))
                      ->order_by('updated_at', 'DESC')
                      ->get('publikasi_tugas_akhir')
                      ->result_array();
    
    if (empty($recent)) {
        echo "   >>> Tidak ada update dalam 24 jam terakhir\n\n";
    } else {
        foreach ($recent as $update) {
            echo "   ID: {$update['id']} - Status: {$update['status']} - Status Pembimbing: {$update['status_pembimbing']} - Updated: {$update['updated_at']}\n";
        }
        echo "\n";
    }
    
    // 4. Check controller method result
    echo "4. CONTROLLER METHOD _get_pengajuan_perlu_review():\n";
    
    $controller_result = $this->_get_pengajuan_perlu_review();
    
    if (empty($controller_result)) {
        echo "   >>> CONTROLLER METHOD MENGEMBALIKAN ARRAY KOSONG!\n";
        echo "   >>> Periksa method _get_pengajuan_perlu_review() di controller\n\n";
    } else {
        echo "   >>> CONTROLLER METHOD MENGEMBALIKAN " . count($controller_result) . " ITEM:\n";
        foreach ($controller_result as $item) {
            echo "   ID: {$item->id} - {$item->nama_mahasiswa} - Status: {$item->status}\n";
        }
    }
    
    // 5. Database connection test
    echo "5. DATABASE CONNECTION TEST:\n";
    echo "   Database: " . $this->db->database . "\n";
    echo "   Last Query: " . $this->db->last_query() . "\n";
    echo "   Connection ID: " . $this->db->conn_id . "\n\n";
    
    echo "=== END DEBUG REPORT ===\n";
}

/**
 * ✅ QUICK FIX: Method untuk force refresh data publikasi
 */
public function force_refresh_publikasi($publikasi_id) {
    // Security check
    if (ENVIRONMENT !== 'development') {
        show_404();
        return;
    }
    
    $publikasi = $this->db->get_where('publikasi_tugas_akhir', ['id' => $publikasi_id])->row();
    
    if (!$publikasi) {
        echo "Publikasi ID {$publikasi_id} tidak ditemukan";
        return;
    }
    
    // Force update tanggal_pengajuan jika status submitted tapi tanggal_pengajuan kosong/lama
    if ($publikasi->status === 'submitted' && $publikasi->status_pembimbing === 'pending') {
        
        $update_data = [
            'tanggal_pengajuan' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $publikasi_id)
                 ->update('publikasi_tugas_akhir', $update_data);
        
        echo "✅ Publikasi ID {$publikasi_id} berhasil di-refresh.\n";
        echo "Tanggal pengajuan diupdate ke: " . date('Y-m-d H:i:s') . "\n";
        echo "Sekarang data akan muncul di dashboard dosen.\n";
    } else {
        echo "❌ Publikasi ID {$publikasi_id} tidak perlu di-refresh.\n";
        echo "Status: {$publikasi->status}\n";
        echo "Status Pembimbing: {$publikasi->status_pembimbing}\n";
    }
}

    // ================================================================
    // PRIVATE METHODS
    // ================================================================
    
    /**
     * Get pengajuan yang perlu direview
     */
    private function _get_pengajuan_perlu_review() {
        try {
            $this->db->select('
                pta.id,
                pta.proposal_mahasiswa_id,
                pta.mahasiswa_id,
                pta.status,
                pta.status_pembimbing,
                pta.tanggal_pengajuan,
                pta.keterangan_mahasiswa,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                pm.judul as judul_skripsi,
                pr.nama as nama_prodi
            ');
            $this->db->from('publikasi_tugas_akhir pta');
            $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
            $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->where('pta.dosen_pembimbing_id', $this->dosen_id);
            $this->db->where('pta.status_pembimbing', 'pending');
            $this->db->where_in('pta.status', ['submitted', 'review_pembimbing']);
            $this->db->order_by('pta.tanggal_pengajuan', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan perlu review: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get riwayat review yang sudah dilakukan
     */
    private function _get_riwayat_review() {
        try {
            $this->db->select('
                pta.id,
                pta.proposal_mahasiswa_id,
                pta.mahasiswa_id,
                pta.status,
                pta.status_pembimbing,
                pta.tanggal_pengajuan,
                pta.tanggal_review_pembimbing,
                pta.komentar_pembimbing,
                m.nim,
                m.nama as nama_mahasiswa,
                pm.judul as judul_skripsi,
                pr.nama as nama_prodi
            ');
            $this->db->from('publikasi_tugas_akhir pta');
            $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
            $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->where('pta.dosen_pembimbing_id', $this->dosen_id);
            $this->db->where_in('pta.status_pembimbing', ['approved', 'rejected']);
            $this->db->order_by('pta.tanggal_review_pembimbing', 'DESC');
            $this->db->limit(10); // Batasi 10 terakhir untuk dashboard
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat review: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * ✅ NEW METHOD: Validasi ownership berdasarkan tabel publikasi_tugas_akhir
     * Lebih tepat karena langsung cek di tabel publikasi
     */
    private function _is_mahasiswa_bimbingan_publikasi($mahasiswa_id) {
        try {
            // Cek apakah mahasiswa memiliki publikasi dengan dosen ini sebagai pembimbing
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->where('dosen_pembimbing_id', $this->dosen_id);
            $count = $this->db->count_all_results('publikasi_tugas_akhir');
            
            log_message('debug', 'Ownership check - Count publikasi: ' . $count);
            
            return $count > 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error checking ownership publikasi: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Get statistik untuk dashboard dosen
     */
    private function _get_statistik_dosen() {
        try {
            $stats = [
                'total_pengajuan' => 0,
                'perlu_review' => 0,
                'approved' => 0,
                'rejected' => 0,
                'completed' => 0
            ];
            
            // Total pengajuan sebagai pembimbing
            $this->db->where('dosen_pembimbing_id', $this->dosen_id);
            $stats['total_pengajuan'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
            // Perlu review
            $this->db->where('dosen_pembimbing_id', $this->dosen_id);
            $this->db->where('status_pembimbing', 'pending');
            $this->db->where_in('status', ['submitted', 'review_pembimbing']);
            $stats['perlu_review'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
            // Approved
            $this->db->where('dosen_pembimbing_id', $this->dosen_id);
            $this->db->where('status_pembimbing', 'approved');
            $stats['approved'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
            // Rejected
            $this->db->where('dosen_pembimbing_id', $this->dosen_id);
            $this->db->where('status_pembimbing', 'rejected');
            $stats['rejected'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
            // Completed
            $this->db->where('dosen_pembimbing_id', $this->dosen_id);
            $this->db->where('status', 'completed');
            $stats['completed'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
            return $stats;
        } catch (Exception $e) {
            log_message('error', 'Error getting statistik dosen: ' . $e->getMessage());
            return [
                'total_pengajuan' => 0,
                'perlu_review' => 0,
                'approved' => 0,
                'rejected' => 0,
                'completed' => 0
            ];
        }
    }
    
    /**
     * Get detail publikasi dengan validasi
     */
    private function _get_publikasi_detail($publikasi_id) {
        try {
            $this->db->select('
                pta.*,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                pm.judul as judul_skripsi,
                pm.id as proposal_id,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing
            ');
            $this->db->from('publikasi_tugas_akhir pta');
            $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
            $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id', 'left');
            $this->db->where('pta.id', $publikasi_id);
            
            $result = $this->db->get()->row();
            
            if ($result) {
                // Add file paths
                $result->file_skripsi_final_path = $this->_get_file_path($result->file_skripsi_final, 'skripsi_final');
                $result->file_surat_revisi_path = $this->_get_file_path($result->file_surat_revisi, 'surat_revisi');
                $result->file_surat_perpustakaan_path = $this->_get_file_path($result->file_surat_perpustakaan, 'surat_perpustakaan');
            }
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Error getting publikasi detail: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Show review form
     */
    private function _show_review_form($publikasi) {
        // Get jurnal bimbingan untuk validasi syarat
        $jurnal_count = $this->_count_jurnal_bimbingan($publikasi->proposal_id);
        
        $view_data = [
            'publikasi' => $publikasi,
            'jurnal_count' => $jurnal_count,
            'min_jurnal_required' => 16, // Minimum jurnal yang diperlukan
            'can_approve' => ($jurnal_count >= 16) // Syarat approve
        ];
        
        // Template data untuk dosen.php
        $data = [
            'title' => 'Review Publikasi - ' . $publikasi->nama_mahasiswa,
            'content' => $this->load->view('dosen/publikasi/review', $view_data, TRUE),
            'script' => $this->_get_review_script()
        ];
        
        $this->load->view('template/dosen', $data);
    }
    
    /**
     * Process review form submission
     */
    private function _process_review($publikasi) {
        // Validation
        $this->form_validation->set_rules('rekomendasi', 'Rekomendasi', 'required|in_list[approved,rejected]');
        $this->form_validation->set_rules('komentar_pembimbing', 'Komentar', 'trim');
        
        if (!$this->form_validation->run()) {
            $this->_show_review_form($publikasi);
            return;
        }
        
        $rekomendasi = $this->input->post('rekomendasi');
        $komentar = trim($this->input->post('komentar_pembimbing'));
        
        // Jika reject, komentar wajib
        if ($rekomendasi == 'rejected' && empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar wajib diisi untuk penolakan.');
            $this->_show_review_form($publikasi);
            return;
        }
        
        // Update database
        $update_data = [
            'status_pembimbing' => $rekomendasi,
            'komentar_pembimbing' => $komentar,
            'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
        ];
        
        // Update status utama berdasarkan rekomendasi
        if ($rekomendasi == 'approved') {
            $update_data['status'] = 'review_staf'; // Lanjut ke staf
        } else {
            $update_data['status'] = 'rejected'; // Kembali ke mahasiswa
        }
        
        $this->db->where('id', $publikasi->id);
        $update_success = $this->db->update('publikasi_tugas_akhir', $update_data);
        
        if ($update_success) {
            // Send email notifications
            $this->_send_review_notifications($publikasi, $rekomendasi, $komentar);
            
            // Set success message
            $action_text = ($rekomendasi == 'approved') ? 'disetujui' : 'ditolak';
            $this->session->set_flashdata('success', "Publikasi berhasil {$action_text}. Notifikasi telah dikirim.");
            
            // Log activity
            $this->_log_review_activity($publikasi->id, $rekomendasi, $komentar);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan review. Silakan coba lagi.');
        }
        
        redirect('dosen/publikasi');
    }
    
    /**
     * Process quick approve
     */
    private function _process_quick_approve($publikasi) {
        try {
            // Check jurnal requirement
            $jurnal_count = $this->_count_jurnal_bimbingan($publikasi->proposal_id);
            
            if ($jurnal_count < 16) {
                return [
                    'success' => false,
                    'message' => "Syarat jurnal bimbingan belum terpenuhi. Dibutuhkan minimal 16 jurnal, tersedia {$jurnal_count}."
                ];
            }
            
            // Update database
            $update_data = [
                'status' => 'review_staf',
                'status_pembimbing' => 'approved',
                'komentar_pembimbing' => 'Disetujui melalui quick approve',
                'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
            ];
            
            $this->db->where('id', $publikasi->id);
            $update_success = $this->db->update('publikasi_tugas_akhir', $update_data);
            
            if ($update_success) {
                // Send notifications
                $this->_send_review_notifications($publikasi, 'approved', 'Disetujui melalui quick approve');
                
                // Log activity
                $this->_log_review_activity($publikasi->id, 'approved', 'Quick approve');
                
                return [
                    'success' => true,
                    'message' => 'Publikasi berhasil disetujui. Notifikasi telah dikirim ke staf dan mahasiswa.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan approval. Silakan coba lagi.'
                ];
            }
        } catch (Exception $e) {
            log_message('error', 'Error in quick approve: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ];
        }
    }
    
    /**
     * Send review notifications
     */
    private function _send_review_notifications($publikasi, $rekomendasi, $komentar) {
        try {
            $this->load->library('email');
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            if ($rekomendasi == 'approved') {
                // Send to staf and mahasiswa
                $this->_send_email_to_staf($publikasi);
                $this->_send_email_to_mahasiswa_approved($publikasi);
            } else {
                // Send to mahasiswa only
                $this->_send_email_to_mahasiswa_rejected($publikasi, $komentar);
            }
        } catch (Exception $e) {
            log_message('error', 'Error sending review notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Send email to staf when approved
     */
    private function _send_email_to_staf($publikasi) {
        try {
            // Get staf emails
            $staf_emails = $this->_get_staf_emails();
            
            if (empty($staf_emails)) {
                log_message('warning', 'No staf emails found for notification');
                return false;
            }
            
            $subject = '[SIM-TA] Publikasi Siap Validasi - ' . $publikasi->nama_mahasiswa;
            $message = $this->load->view('emails/publikasi_to_staf', [
                'publikasi' => $publikasi,
                'dosen_nama' => $this->session->userdata('nama')
            ], TRUE);
            
            $this->email->clear();
            $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK');
            $this->email->to($staf_emails);
            $this->email->subject($subject);
            $this->email->message($message);
            
            return $this->email->send();
        } catch (Exception $e) {
            log_message('error', 'Error sending email to staf: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email to mahasiswa when approved
     */
    private function _send_email_to_mahasiswa_approved($publikasi) {
        try {
            if (empty($publikasi->email_mahasiswa)) {
                return false;
            }
            
            $subject = '[SIM-TA] Publikasi Disetujui Dosen Pembimbing';
            $message = $this->load->view('emails/publikasi_approved_mahasiswa', [
                'publikasi' => $publikasi,
                'dosen_nama' => $this->session->userdata('nama')
            ], TRUE);
            
            $this->email->clear();
            $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK');
            $this->email->to($publikasi->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message);
            
            return $this->email->send();
        } catch (Exception $e) {
            log_message('error', 'Error sending email to mahasiswa approved: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email to mahasiswa when rejected
     */
    private function _send_email_to_mahasiswa_rejected($publikasi, $komentar) {
        try {
            if (empty($publikasi->email_mahasiswa)) {
                return false;
            }
            
            $subject = '[SIM-TA] Publikasi Perlu Diperbaiki';
            $message = $this->load->view('emails/publikasi_rejected_mahasiswa', [
                'publikasi' => $publikasi,
                'komentar' => $komentar,
                'dosen_nama' => $this->session->userdata('nama')
            ], TRUE);
            
            $this->email->clear();
            $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK');
            $this->email->to($publikasi->email_mahasiswa);
            $this->email->subject($subject);
            $this->email->message($message);
            
            return $this->email->send();
        } catch (Exception $e) {
            log_message('error', 'Error sending email to mahasiswa rejected: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Helper methods
     */
    private function _count_jurnal_bimbingan($proposal_id) {
        return $this->db->where('proposal_id', $proposal_id)
                       ->where('status_validasi', '1')
                       ->count_all_results('jurnal_bimbingan');
    }
    
    private function _get_file_path($filename, $type) {
        if (empty($filename)) return null;
        
        $base_path = base_url('uploads/publikasi/');
        switch ($type) {
            case 'skripsi_final':
                return $base_path . 'skripsi_final/' . $filename;
            case 'surat_revisi':
                return $base_path . 'surat_revisi/' . $filename;
            case 'surat_perpustakaan':
                return $base_path . 'surat_perpustakaan/' . $filename;
            default:
                return $base_path . $filename;
        }
    }
    
    private function _get_staf_emails() {
        $this->db->select('email');
        $this->db->where('level', '5'); // Level staf
        $query = $this->db->get('dosen');
        
        $emails = [];
        foreach ($query->result() as $row) {
            if (!empty($row->email)) {
                $emails[] = $row->email;
            }
        }
        
        return $emails;
    }
    
    private function _is_mahasiswa_bimbingan($mahasiswa_id) {
        $this->db->where('mahasiswa_id', $mahasiswa_id);
        $this->db->where('dosen_id', $this->dosen_id);
        $this->db->where('status', '1'); // Status aktif
        return $this->db->count_all_results('proposal_mahasiswa') > 0;
    }
    
    private function _get_mahasiswa_data($mahasiswa_id) {
        return $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
    }
    
    private function _get_riwayat_publikasi_mahasiswa($mahasiswa_id) {
        $this->db->select('*');
        $this->db->where('mahasiswa_id', $mahasiswa_id);
        $this->db->where('dosen_pembimbing_id', $this->dosen_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('publikasi_tugas_akhir')->result();
    }
    
    private function _get_detail_reviews($mahasiswa_id) {
        // Get all review history
        $this->db->select('
            tanggal_review_pembimbing as tanggal,
            status_pembimbing as status,
            komentar_pembimbing as komentar,
            "dosen_pembimbing" as reviewer_type
        ');
        $this->db->where('mahasiswa_id', $mahasiswa_id);
        $this->db->where('dosen_pembimbing_id', $this->dosen_id);
        $this->db->where('tanggal_review_pembimbing IS NOT NULL');
        $this->db->order_by('tanggal_review_pembimbing', 'DESC');
        return $this->db->get('publikasi_tugas_akhir')->result();
    }
    
    /**
     * ALTERNATIF: Minimal logging dengan field wajib saja
     * Gunakan ini jika masih ada field yang error
     */
    private function _log_review_activity($publikasi_id, $action, $komentar) {
        try {
            // Data minimal yang pasti ada di tabel
            $data = [
                'publikasi_id' => $publikasi_id,
                'user_id' => $this->dosen_id,
                'user_role' => 'dosen',
                'aktivitas' => $action,
                'deskripsi' => "Review publikasi: {$action}. Komentar: " . substr($komentar, 0, 200)
            ];
            
            // Tambah field optional dengan safe check
            if (!empty($this->session->userdata('nama'))) {
                $data['user_name'] = $this->session->userdata('nama');
            }
            
            if (method_exists($this->input, 'ip_address')) {
                $data['ip_address'] = $this->input->ip_address();
            }
            
            $result = $this->db->insert('log_publikasi', $data);
            
            if ($result) {
                log_message('info', "✅ Review activity logged untuk publikasi ID: {$publikasi_id}");
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', "❌ Error logging: " . $e->getMessage());
            return false;
        }
    }
    
    private function _get_email_config() {
        return [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'stkyakobus@gmail.com',
            'smtp_pass' => 'yonroxhraathnaug',
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'mailtype' => 'html',
            'smtp_crypto' => 'tls',
            'smtp_timeout' => 30,
            'wordwrap' => TRUE
        ];
    }
    
    private function _get_index_script() {
        return '
        <script>
        $(document).ready(function() {
            // Quick approve button
            $(".btn-quick-approve").click(function() {
                var publikasiId = $(this).data("id");
                var mahasiswaNama = $(this).data("nama");
                
                if (confirm("Yakin ingin menyetujui publikasi " + mahasiswaNama + "?")) {
                    $.post("' . base_url('dosen/publikasi/quick_approve/') . '" + publikasiId, function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    }, "json");
                }
            });
            
            // Refresh badge every 30 seconds
            setInterval(function() {
                // Optional: refresh notification badge
            }, 30000);
        });
        </script>';
    }
    
    private function _get_review_script() {
        return '
        <script>
        $(document).ready(function() {
            // Toggle komentar field based on rekomendasi
            $("input[name=rekomendasi]").change(function() {
                if ($(this).val() == "rejected") {
                    $("#komentar-group").show();
                    $("#komentar_pembimbing").attr("required", true);
                } else {
                    $("#komentar-group").show(); // Show for both
                    $("#komentar_pembimbing").attr("required", false);
                }
            });
            
            // File preview
            $(".btn-preview").click(function() {
                var url = $(this).data("url");
                window.open(url, "_blank");
            });
        });
        </script>';
    }
    
    // ================================================================
    // BONUS: METHOD UNTUK CEK LOG AKTIVITAS (Optional)
    // ================================================================
    
    /**
     * BONUS: Method untuk get log aktivitas publikasi
     * Bisa digunakan untuk menampilkan riwayat aktivitas di view
     */
    private function _get_publikasi_logs($publikasi_id) {
        try {
            $this->db->select('*');
            $this->db->from('log_publikasi');
            $this->db->where('publikasi_id', $publikasi_id);
            $this->db->order_by('created_at', 'DESC');
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting publikasi logs: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * BONUS: Method untuk get aktivitas dosen
     * Bisa digunakan untuk dashboard atau laporan
     */
    private function _get_dosen_activities($dosen_id, $limit = 10) {
        try {
            $this->db->select('lp.*, pta.status, m.nama as nama_mahasiswa, m.nim');
            $this->db->from('log_publikasi lp');
            $this->db->join('publikasi_tugas_akhir pta', 'lp.publikasi_id = pta.id');
            $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
            $this->db->where('lp.user_id', $dosen_id);
            $this->db->where('lp.user_role', 'dosen');
            $this->db->order_by('lp.created_at', 'DESC');
            $this->db->limit($limit);
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen activities: ' . $e->getMessage());
            return [];
        }
    }
}