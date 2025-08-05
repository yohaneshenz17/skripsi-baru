<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Publikasi untuk Staf - Phase 6
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * 
 * Workflow:
 * 1. Mahasiswa Kirim Ajuan
 * 2. Dosen Pembimbing Rekomendasi → jika Approve lanjut ke staf
 * 3. Staf input link repository sesuai form mahasiswa
 * 4. Staf Validasi pengajuan (notifikasi email ke dosen dan mahasiswa)
 * 5. Proses Selesai (completed)
 * 6. Mahasiswa Download Surat Keterangan Publikasi
 * 
 * File: application/controllers/staf/Publikasi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Staf
 * @category    Publikasi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Following DosenPublikasi Pattern)
 */
class Publikasi extends CI_Controller {

    private $staf_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session', 'form_validation', 'email']);
        $this->load->helper(['url', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk staf
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if($this->session->userdata('level') != '5') { // Level 5 = Staf
            show_error('Akses ditolak. Halaman khusus staf.', 403);
        }
        
        $this->staf_id = $this->session->userdata('id');
    }

    /**
     * Dashboard publikasi untuk staf
     * Menggunakan template staf.php yang benar
     */
    public function index() {
        // Prepare data untuk view content
        $view_data = [
            'pengajuan_validasi' => $this->_get_pengajuan_perlu_validasi(),
            'riwayat_validasi' => $this->_get_riwayat_validasi(), 
            'stats' => $this->_get_statistik_staf()
        ];
        
        // Render content view
        $content = $this->load->view('staf/publikasi/index', $view_data, TRUE);
        
        // Load template staf.php dengan structure yang benar
        $this->load->view('template/staf', [
            'title' => 'Validasi Publikasi Tugas Akhir',
            'content' => $content,
            'css' => $this->_get_index_css(),
            'script' => $this->_get_index_script()
        ]);
    }

    /**
     * Detail dan input repository publikasi
     */
    public function detail($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
        }
        
        // Hanya bisa akses yang sudah disetujui dosen
        if ($publikasi->status_pembimbing !== 'approved') {
            $this->session->set_flashdata('error', 'Pengajuan belum disetujui dosen pembimbing.');
            redirect('staf/publikasi');
        }
        
        // Prepare data untuk view content
        $view_data = [
            'publikasi' => $publikasi,
            'allow_input_repository' => ($publikasi->status_staf === 'pending'),
            'show_files' => true
        ];
        
        // Render content view
        $content = $this->load->view('staf/publikasi/detail', $view_data, TRUE);
        
        // Load template staf.php
        $this->load->view('template/staf', [
            'title' => 'Detail Publikasi - ' . $publikasi->nama_mahasiswa,
            'content' => $content,
            'css' => $this->_get_detail_css(),
            'script' => $this->_get_detail_script()
        ]);
    }

    /**
     * Input repository link - Step 3 dalam workflow
     */
    public function input_repository($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
        }
        
        // Validasi status - harus sudah disetujui dosen
        if ($publikasi->status_pembimbing !== 'approved') {
            $this->session->set_flashdata('error', 'Pengajuan belum disetujui dosen pembimbing.');
            redirect('staf/publikasi');
        }
        
        // Validasi status staf - harus masih pending
        if ($publikasi->status_staf !== 'pending') {
            $this->session->set_flashdata('error', 'Publikasi sudah diproses atau ditolak.');
            redirect('staf/publikasi');
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_input_repository($publikasi);
        } else {
            $this->_show_input_repository_form($publikasi);
        }
    }

    /**
     * Validasi final pengajuan publikasi - Step 4 dalam workflow
     */
    public function validasi($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
        }
        
        // Harus sudah ada repository link
        if (empty($publikasi->link_repository)) {
            $this->session->set_flashdata('error', 'Repository link belum diinput.');
            redirect('staf/publikasi/detail/' . $publikasi_id);
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_validasi_final($publikasi);
        } else {
            $this->_show_validasi_form($publikasi);
        }
    }

    /**
     * Download file publikasi
     */
    public function download_file($file_type, $publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            show_404();
        }
        
        $file_map = [
            'surat_revisi' => $publikasi->file_surat_revisi,
            'skripsi_final' => $publikasi->file_skripsi_final,
            'surat_perpustakaan' => $publikasi->file_surat_perpustakaan
        ];
        
        if (!isset($file_map[$file_type]) || empty($file_map[$file_type])) {
            show_404();
        }
        
        $file_path = './uploads/publikasi/' . $file_map[$file_type];
        
        if (!file_exists($file_path)) {
            show_404();
        }
        
        // Force download
        $this->load->helper('download');
        force_download($file_map[$file_type], file_get_contents($file_path));
    }

    // =================================================================
    // PRIVATE METHODS - Following DosenPublikasi Pattern
    // =================================================================

    /**
     * Get pengajuan yang perlu validasi staf
     */
    private function _get_pengajuan_perlu_validasi() {
        try {
            // Query dengan join manual untuk compatibility dengan database structure
            $this->db->select('
                pta.*,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                pm.workflow_status,
                pm.judul as judul_proposal_awal,
                d.nama as nama_pembimbing_lengkap,
                d.email as email_pembimbing
            ');
            $this->db->from('publikasi_tugas_akhir pta');
            $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id', 'left');
            $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id', 'left');
            $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id', 'left');
            $this->db->where('pta.status_pembimbing', 'approved');
            $this->db->where('pta.status_staf', 'pending');
            $this->db->order_by('pta.tanggal_review_pembimbing', 'ASC');
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan perlu validasi staf: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat validasi staf
     */
    private function _get_riwayat_validasi() {
        try {
            // Query langsung ke tabel publikasi_tugas_akhir karena view tidak include validated_by_staf_id
            $this->db->select('
                pta.*,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                pm.workflow_status,
                pm.judul as judul_proposal_awal,
                d.nama as nama_pembimbing_lengkap,
                d.email as email_pembimbing
            ');
            $this->db->from('publikasi_tugas_akhir pta');
            $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id', 'left');
            $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id', 'left');
            $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id', 'left');
            $this->db->where('pta.validated_by_staf_id', $this->staf_id);
            $this->db->where('pta.status_staf !=', 'pending');
            $this->db->order_by('pta.tanggal_validasi_staf', 'DESC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat validasi staf: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistik untuk dashboard staf
     */
    private function _get_statistik_staf() {
        $stats = [
            'menunggu_validasi' => 0,
            'sudah_divalidasi' => 0,
            'publikasi_selesai' => 0,
            'total_bulan_ini' => 0
        ];
        
        try {
            // Menunggu validasi - menggunakan tabel publikasi_tugas_akhir langsung
            $this->db->select('COUNT(*) as total');
            $this->db->from('publikasi_tugas_akhir');
            $this->db->where('status_pembimbing', 'approved');
            $this->db->where('status_staf', 'pending');
            $result = $this->db->get()->row();
            $stats['menunggu_validasi'] = $result ? $result->total : 0;
            
            // Sudah divalidasi oleh staf ini
            $this->db->select('COUNT(*) as total');
            $this->db->from('publikasi_tugas_akhir');
            $this->db->where('validated_by_staf_id', $this->staf_id);
            $this->db->where('status_staf !=', 'pending');
            $result = $this->db->get()->row();
            $stats['sudah_divalidasi'] = $result ? $result->total : 0;
            
            // Publikasi selesai
            $this->db->select('COUNT(*) as total');
            $this->db->from('publikasi_tugas_akhir');
            $this->db->where('status', 'completed');
            $result = $this->db->get()->row();
            $stats['publikasi_selesai'] = $result ? $result->total : 0;
            
            // Total bulan ini - yang divalidasi oleh staf ini
            $this->db->select('COUNT(*) as total');
            $this->db->from('publikasi_tugas_akhir');
            $this->db->where('validated_by_staf_id', $this->staf_id);
            $this->db->where('MONTH(tanggal_validasi_staf)', date('n'));
            $this->db->where('YEAR(tanggal_validasi_staf)', date('Y'));
            $result = $this->db->get()->row();
            $stats['total_bulan_ini'] = $result ? $result->total : 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistik staf: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Get detail publikasi dengan join lengkap
     */
    private function _get_publikasi_detail($publikasi_id) {
        try {
            // Query dengan join manual untuk memastikan semua field tersedia
            $this->db->select('
                pta.*,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                pm.workflow_status,
                pm.judul as judul_proposal_awal,
                d.nama as nama_pembimbing_lengkap,
                d.email as email_pembimbing,
                CASE pta.status 
                    WHEN "draft" THEN "Draft - Belum disubmit"
                    WHEN "submitted" THEN "Menunggu review pembimbing" 
                    WHEN "review_pembimbing" THEN "Sedang direview pembimbing"
                    WHEN "review_staf" THEN "Menunggu validasi staf"
                    WHEN "completed" THEN "Publikasi selesai"
                    WHEN "rejected" THEN "Ditolak"
                    ELSE "Status tidak dikenali"
                END as status_description,
                CASE 
                    WHEN pta.status = "completed" THEN 100
                    WHEN pta.status = "review_staf" THEN 80 
                    WHEN pta.status = "review_pembimbing" THEN 60
                    WHEN pta.status = "submitted" THEN 40
                    WHEN pta.status = "draft" THEN 20
                    ELSE 0 
                END as progress_percentage
            ');
            $this->db->from('publikasi_tugas_akhir pta');
            $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id', 'left');
            $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id', 'left'); 
            $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id', 'left');
            $this->db->where('pta.id', $publikasi_id);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting publikasi detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process input repository link
     */
    private function _process_input_repository($publikasi) {
        // Validasi form
        $this->form_validation->set_rules('link_repository', 'Link Repository', 'required|valid_url|trim');
        $this->form_validation->set_rules('keterangan_staf', 'Keterangan', 'trim');
        
        if (!$this->form_validation->run()) {
            $this->_show_input_repository_form($publikasi);
            return;
        }
        
        // Update data repository
        $update_data = [
            'link_repository' => trim($this->input->post('link_repository')),
            'komentar_staf' => trim($this->input->post('keterangan_staf')),
            'status' => 'review_staf',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->trans_start();
            
            $this->db->where('id', $publikasi->id);
            $this->db->update('publikasi_tugas_akhir', $update_data);
            
            // Log aktivitas
            $this->_log_activity($publikasi->id, 'input_repository', 
                'Staf menginput link repository: ' . $update_data['link_repository']);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            $this->session->set_flashdata('success', 'Link repository berhasil diinput. Silakan lakukan validasi final.');
            redirect('staf/publikasi/validasi/' . $publikasi->id);
            
        } catch (Exception $e) {
            log_message('error', 'Error input repository: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('staf/publikasi');
        }
    }

    /**
     * Process validasi final
     */
    private function _process_validasi_final($publikasi) {
        $action = $this->input->post('action');
        
        if ($action === 'approve') {
            $this->_approve_publikasi($publikasi);
        } elseif ($action === 'reject') {
            $this->_reject_publikasi($publikasi);
        } else {
            $this->session->set_flashdata('error', 'Aksi tidak valid.');
            redirect('staf/publikasi');
        }
    }

    /**
     * Approve publikasi - Step 5 selesai
     */
    private function _approve_publikasi($publikasi) {
        // Validasi komentar jika ada
        $komentar_staf = trim($this->input->post('komentar_staf'));
        
        $update_data = [
            'status' => 'completed',
            'status_staf' => 'approved',
            'komentar_staf' => $komentar_staf,
            'validated_by_staf_id' => $this->staf_id,
            'validated_by_staf_name' => $this->session->userdata('nama'),
            'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
            'tanggal_selesai' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->trans_start();
            
            // Update publikasi
            $this->db->where('id', $publikasi->id);
            $this->db->update('publikasi_tugas_akhir', $update_data);
            
            // Update workflow_status di proposal_mahasiswa ke 'publikasi'
            $this->db->where('id', $publikasi->proposal_mahasiswa_id);
            $this->db->update('proposal_mahasiswa', ['workflow_status' => 'publikasi']);
            
            // Log aktivitas
            $this->_log_activity($publikasi->id, 'approve_final', 
                'Staf menyetujui dan menyelesaikan publikasi');
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            // Send notification
            $this->_send_notification_completed($publikasi);
            
            $this->session->set_flashdata('success', 'Publikasi berhasil diselesaikan dan disetujui.');
            redirect('staf/publikasi');
            
        } catch (Exception $e) {
            log_message('error', 'Error approve publikasi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('staf/publikasi');
        }
    }

    /**
     * Reject publikasi
     */
    private function _reject_publikasi($publikasi) {
        // Validasi komentar wajib untuk reject
        $komentar_staf = trim($this->input->post('komentar_staf'));
        
        if (empty($komentar_staf)) {
            $this->session->set_flashdata('error', 'Komentar penolakan wajib diisi.');
            $this->_show_validasi_form($publikasi);
            return;
        }
        
        $update_data = [
            'status' => 'rejected',
            'status_staf' => 'rejected',
            'komentar_staf' => $komentar_staf,
            'validated_by_staf_id' => $this->staf_id,
            'validated_by_staf_name' => $this->session->userdata('nama'),
            'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->trans_start();
            
            $this->db->where('id', $publikasi->id);
            $this->db->update('publikasi_tugas_akhir', $update_data);
            
            // Log aktivitas
            $this->_log_activity($publikasi->id, 'reject_final', 
                'Staf menolak publikasi: ' . $komentar_staf);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            // Send notification
            $this->_send_notification_rejected($publikasi, $komentar_staf);
            
            $this->session->set_flashdata('success', 'Publikasi ditolak dan dikembalikan ke mahasiswa.');
            redirect('staf/publikasi');
            
        } catch (Exception $e) {
            log_message('error', 'Error reject publikasi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('staf/publikasi');
        }
    }

    /**
     * Show input repository form
     */
    private function _show_input_repository_form($publikasi) {
        $view_data = [
            'publikasi' => $publikasi,
            'form_action' => 'input_repository'
        ];
        
        // Render content view
        $content = $this->load->view('staf/publikasi/input_repository', $view_data, TRUE);
        
        // Load template staf.php
        $this->load->view('template/staf', [
            'title' => 'Input Repository - ' . $publikasi->nama_mahasiswa,
            'content' => $content,
            'css' => $this->_get_form_css(),
            'script' => $this->_get_form_script()
        ]);
    }

    /**
     * Show validasi form
     */
    private function _show_validasi_form($publikasi) {
        $view_data = [
            'publikasi' => $publikasi,
            'form_action' => 'validasi'
        ];
        
        // Render content view
        $content = $this->load->view('staf/publikasi/validasi', $view_data, TRUE);
        
        // Load template staf.php
        $this->load->view('template/staf', [
            'title' => 'Validasi Final Publikasi - ' . $publikasi->nama_mahasiswa,
            'content' => $content,
            'css' => $this->_get_validasi_css(),
            'script' => $this->_get_validasi_script()
        ]);
    }

    /**
     * Send notification setelah completed
     */
    private function _send_notification_completed($publikasi) {
        try {
            // Setup email
            $this->load->library('email');
            $this->email->clear();
            
            // Email ke mahasiswa
            $this->email->to($publikasi->email_mahasiswa);
            $this->email->subject('[SIM-TA] Publikasi Tugas Akhir Selesai');
            
            $message = "
            Yth. {$publikasi->nama_mahasiswa},
            
            Publikasi tugas akhir Anda telah selesai divalidasi dan disetujui oleh staf akademik.
            
            Detail:
            - Judul: {$publikasi->judul_skripsi_final}
            - Repository: {$publikasi->link_repository}
            - Tanggal Selesai: " . date('d/m/Y H:i') . "
            
            Anda dapat mendownload Surat Keterangan Publikasi di dashboard mahasiswa.
            
            Terima kasih.
            ";
            
            $this->email->message($message);
            $result_mahasiswa = $this->email->send();
            
            // Email ke dosen pembimbing
            if (!empty($publikasi->email_pembimbing)) {
                $this->email->clear();
                $this->email->to($publikasi->email_pembimbing);
                $this->email->subject('[SIM-TA] Publikasi Mahasiswa Bimbingan Selesai');
                
                $message_dosen = "
                Yth. {$publikasi->nama_dosen_pembimbing},
                
                Publikasi tugas akhir mahasiswa bimbingan Anda telah selesai divalidasi.
                
                Detail Mahasiswa:
                - Nama: {$publikasi->nama_mahasiswa}
                - NIM: {$publikasi->nim}
                - Judul: {$publikasi->judul_skripsi_final}
                - Repository: {$publikasi->link_repository}
                
                Terima kasih atas bimbingannya.
                ";
                
                $this->email->message($message_dosen);
                $this->email->send();
            }
            
            return $result_mahasiswa;
            
        } catch (Exception $e) {
            log_message('error', 'Error send notification completed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification setelah rejected
     */
    private function _send_notification_rejected($publikasi, $alasan) {
        try {
            $this->load->library('email');
            $this->email->clear();
            
            // Email ke mahasiswa
            $this->email->to($publikasi->email_mahasiswa);
            $this->email->subject('[SIM-TA] Publikasi Tugas Akhir Ditolak');
            
            $message = "
            Yth. {$publikasi->nama_mahasiswa},
            
            Publikasi tugas akhir Anda ditolak oleh staf akademik dengan alasan:
            
            {$alasan}
            
            Detail:
            - Judul: {$publikasi->judul_skripsi_final}
            - Tanggal Penolakan: " . date('d/m/Y H:i') . "
            
            Silakan perbaiki sesuai komentar dan ajukan kembali.
            
            Terima kasih.
            ";
            
            $this->email->message($message);
            return $this->email->send();
            
        } catch (Exception $e) {
            log_message('error', 'Error send notification rejected: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log aktivitas
     */
    private function _log_activity($publikasi_id, $aktivitas, $deskripsi) {
        try {
            $log_data = [
                'publikasi_id' => $publikasi_id,
                'user_id' => $this->staf_id,
                'user_role' => 'staf',
                'user_name' => $this->session->userdata('nama'),
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsi,
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('log_publikasi', $log_data);
            
        } catch (Exception $e) {
            log_message('error', 'Error log activity: ' . $e->getMessage());
        }
    }

    /**
     * Get index script
     */
    private function _get_index_script() {
        return "
        <!-- DataTables CSS -->
        <link rel=\"stylesheet\" type=\"text/css\" href=\"https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css\">
        
        <!-- DataTables JS -->
        <script src=\"https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js\"></script>
        <script src=\"https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js\"></script>
        
        <script>
        $(document).ready(function() {
            // DataTable untuk pengajuan
            if ($('#pengajuanTable').length) {
                $('#pengajuanTable').DataTable({
                    'pageLength': 10,
                    'ordering': true,
                    'searching': true,
                    'language': {
                        'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                    }
                });
            }
            
            // DataTable untuk riwayat
            if ($('#riwayatTable').length) {
                $('#riwayatTable').DataTable({
                    'pageLength': 5,
                    'ordering': true,
                    'searching': false,
                    'language': {
                        'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                    }
                });
            }
        });
        </script>
        ";
    }

    /**
     * Get index CSS
     */
    private function _get_index_css() {
        return "
        <style>
        /* Workflow Steps */
        .workflow-container {
            background: #f8f9fe;
            border-radius: 12px;
            padding: 30px 20px;
        }

        .workflow-step {
            text-align: center;
            position: relative;
            margin-bottom: 20px;
        }

        .workflow-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 25px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }

        .workflow-step.current:not(:last-child)::after {
            background: linear-gradient(to right, #ffc107 50%, #dee2e6 50%);
        }

        .workflow-step.completed:not(:last-child)::after {
            background: #28a745;
        }

        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .step-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #32325d;
        }

        .step-desc {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 0;
            line-height: 1.4;
        }

        /* Empty State */
        .empty-state {
            padding: 40px 20px;
        }

        /* Badge improvements */
        .badge-soft-info {
            color: #2dce89;
            background-color: rgba(45, 206, 137, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .workflow-step:not(:last-child)::after {
                display: none;
            }
            
            .step-number {
                width: 40px;
                height: 40px;
                font-size: 12px;
            }
            
            .step-title {
                font-size: 14px;
            }
            
            .step-desc {
                font-size: 12px;
            }
            
            .workflow-container {
                padding: 20px 15px;
            }
        }
        </style>
        ";
    }

    /**
     * Get detail script
     */
    private function _get_detail_script() {
        return "
        <script>
        // Download file function
        function downloadFile(type, id) {
            window.open('" . base_url('staf/publikasi/download_file/') . "' + type + '/' + id, '_blank');
        }
        
        // Confirm input repository
        function confirmInputRepository() {
            return confirm('Pastikan link repository sudah benar. Lanjutkan?');
        }
        </script>
        ";
    }

    /**
     * Get detail CSS
     */
    private function _get_detail_css() {
        return "
        <style>
        /* Timeline Styles */
        .timeline-container {
            position: relative;
            padding: 20px 0;
        }

        .timeline-container::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 20px;
            bottom: 20px;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;  
            margin-bottom: 30px;
            padding-left: 80px;
        }

        .timeline-item.completed .timeline-marker {
            background-color: #28a745 !important;
        }

        .timeline-item.current .timeline-marker {
            background-color: #ffc107 !important;
            animation: pulse 2s infinite;
        }

        .timeline-item.rejected .timeline-marker {
            background-color: #dc3545 !important;
        }

        .timeline-marker {
            position: absolute;
            left: 15px;
            top: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            z-index: 1;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .timeline-content {
            background: #f8f9fe;
            border-radius: 8px;
            padding: 20px;
            border-left: 3px solid #5e72e4;
        }

        .timeline-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .timeline-title {
            font-weight: 600;
            margin: 0 0 5px 0;
            color: #32325d;
        }

        .timeline-status {
            margin: 10px 0;
        }

        .timeline-desc {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
        }

        @keyframes pulse {
            0% { box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 0 0 0 rgba(255, 193, 7, 0.7); }
            70% { box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 0 0 10px rgba(255, 193, 7, 0); }
            100% { box-shadow: 0 2px 8px rgba(0,0,0,0.15), 0 0 0 0 rgba(255, 193, 7, 0); }
        }

        /* File Cards */
        .file-card {
            text-align: center;
            padding: 25px 20px;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            background: #f8f9fe;
            transition: all 0.3s ease;
            height: 100%;
        }

        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .file-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .file-title {
            font-weight: 600;
            margin-bottom: 20px;
            color: #32325d;
        }

        /* Comments */
        .comment-section {
            margin-bottom: 30px;
        }

        .comment-box {
            background: #f8f9fe;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            font-size: 14px;
            line-height: 1.6;
            color: #525f7f;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .timeline-container::before {
                left: 20px;
            }
            
            .timeline-item {
                padding-left: 60px;
            }
            
            .timeline-marker {
                left: 5px;
                width: 30px;
                height: 30px;
                font-size: 12px;
            }
            
            .timeline-header {
                flex-direction: column;
            }
            
            .file-card {
                margin-bottom: 15px;
                padding: 20px 15px;
            }
        }
        </style>
        ";
    }

    /**
     * Get validasi script
     */
    private function _get_validasi_script() {
        return "
        <script>
        function confirmAction(action) {
            if (action === 'approve') {
                return confirm('Yakin ingin MENYETUJUI publikasi ini? Proses tidak dapat dibatalkan.');
            } else if (action === 'reject') {
                const komentar = $('#komentar_staf').val().trim();
                if (komentar === '') {
                    alert('Komentar penolakan harus diisi.');
                    return false;
                }
                return confirm('Yakin ingin MENOLAK publikasi ini?');
            }
            return false;
        }
        
        // Real-time validation
        $('#komentar_staf').on('input', function() {
            const action = $('input[name=\"action\"]:checked').val();
            if (action === 'reject') {
                const komentar = $(this).val().trim();
                $('#submitBtn').prop('disabled', komentar === '');
            }
        });
        
        $('input[name=\"action\"]').change(function() {
            const action = $(this).val();
            if (action === 'reject') {
                const komentar = $('#komentar_staf').val().trim();
                $('#submitBtn').prop('disabled', komentar === '');
            } else {
                $('#submitBtn').prop('disabled', false);
            }
        });
        
        function downloadFile(type, id) {
            window.open('" . base_url('staf/publikasi/download_file/') . "' + type + '/' + id, '_blank');
        }
        </script>
        ";
    }

    /**
     * Get form CSS untuk input repository
     */
    private function _get_form_css() {
        return "
        <style>
        /* Form styling */
        .form-control-label {
            font-weight: 600;
            color: #32325d;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        /* Alert styling */
        .alert {
            border: none;
            border-radius: 12px;
        }

        .alert-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .alert-icon {
            float: left;
            font-size: 1.5rem;
            margin-right: 15px;
            margin-top: 5px;
        }

        .alert-content {
            overflow: hidden;
        }

        .alert-title {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .alert ul {
            padding-left: 20px;
        }

        .alert li {
            margin-bottom: 8px;
        }

        /* Verification checklist */
        .verification-checklist {
            background: #f8f9fe;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 25px;
            margin-top: 15px;
        }

        .verification-checklist .custom-control {
            margin-bottom: 15px;
        }

        .verification-checklist .custom-control:last-child {
            margin-bottom: 0;
        }

        .custom-control-label {
            font-weight: 500;
            color: #525f7f;
            cursor: pointer;
            padding-left: 8px;
        }

        .custom-control-label::before {
            border-radius: 6px;
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #5e72e4;
            border-color: #5e72e4;
        }

        /* Input group */
        .input-group-text {
            background-color: #5e72e4;
            color: white;
            border-color: #5e72e4;
            font-weight: 600;
        }

        /* Repository preview */
        .repository-preview {
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            background: #f8f9fe;
            transition: all 0.3s ease;
        }

        .repository-preview.loaded {
            border-color: #28a745;
            background: #d4edda;
        }

        .repository-preview.error {
            border-color: #dc3545;
            background: #f8d7da;
        }

        /* Button styling */
        .btn {
            font-weight: 600;
            letter-spacing: 0.025em;
            border-radius: 8px;
            padding: 0.625rem 1.25rem;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
            border: none;
        }

        .btn-warning:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 216, 155, 0.4);
        }
        </style>
        ";
    }

    /**
     * Get form script untuk input repository
     */
    private function _get_form_script() {
        return "
        <script>
        $(document).ready(function() {
            // Enable submit button only when all checkboxes are checked
            $('.verification-checklist input[type=\"checkbox\"]').change(function() {
                const allChecked = $('.verification-checklist input[type=\"checkbox\"]:checked').length === 3;
                const hasUrl = $('#link_repository').val().trim() !== '';
                $('#submitBtn').prop('disabled', !(allChecked && hasUrl));
            });
            
            // Check URL input
            $('#link_repository').on('input', function() {
                const allChecked = $('.verification-checklist input[type=\"checkbox\"]:checked').length === 3;
                const hasUrl = $(this).val().trim() !== '';
                $('#submitBtn').prop('disabled', !(allChecked && hasUrl));
                
                // Update preview
                updateRepositoryPreview($(this).val());
            });
        });

        function testRepositoryLink() {
            const url = $('#link_repository').val().trim();
            if (!url) {
                alert('Masukkan link repository terlebih dahulu');
                return;
            }
            
            if (!isValidUrl(url)) {
                alert('Format URL tidak valid');
                return;
            }
            
            window.open(url, '_blank');
        }

        function updateRepositoryPreview(url) {
            const preview = $('#repositoryPreview');
            
            if (!url || url.trim() === '') {
                preview.html('<div class=\"text-center text-muted py-4\"><i class=\"fas fa-info-circle fa-2x mb-3\"></i><p>Masukkan link repository terlebih dahulu untuk melihat preview</p></div>').removeClass('loaded error');
                return;
            }
            
            if (!isValidUrl(url)) {
                preview.html('<div class=\"text-center text-danger py-4\"><i class=\"fas fa-exclamation-triangle fa-2x mb-3\"></i><p>Format URL tidak valid</p></div>').removeClass('loaded').addClass('error');
                return;
            }
            
            let repoInfo = '', iconClass = '';
            
            if (url.includes('github.com')) {
                repoInfo = 'GitHub Repository';
                iconClass = 'fab fa-github';
            } else if (url.includes('drive.google.com')) {
                repoInfo = 'Google Drive';
                iconClass = 'fab fa-google-drive';
            } else if (url.includes('gitlab.com')) {
                repoInfo = 'GitLab Repository';
                iconClass = 'fab fa-gitlab';
            } else {
                repoInfo = 'Repository Link';
                iconClass = 'fas fa-link';
            }
            
            preview.html('<div class=\"text-center py-4\"><i class=\"' + iconClass + ' fa-3x text-success mb-3\"></i><h5 class=\"text-success\">' + repoInfo + '</h5><p class=\"text-muted mb-3\">' + url + '</p><button type=\"button\" class=\"btn btn-sm btn-outline-primary\" onclick=\"window.open(\'' + url + '\', \'_blank\')\"><i class=\"fas fa-external-link-alt mr-1\"></i> Buka Repository</button></div>').removeClass('error').addClass('loaded');
        }

        function isValidUrl(string) {
            try {
                const url = new URL(string);
                return url.protocol === 'http:' || url.protocol === 'https:';
            } catch (_) {
                return false;
            }
        }

        function confirmInputRepository() {
            const url = $('#link_repository').val().trim();
            return confirm('Yakin ingin menyimpan repository link?\\n\\n' + url + '\\n\\nSetelah disimpan, Anda akan dialihkan ke halaman validasi final.');
        }

        function downloadFile(type, id) {
            window.open('" . base_url('staf/publikasi/download_file/') . "' + type + '/' + id, '_blank');
        }
        </script>
        ";
    }

    /**
     * Get validasi CSS
     */
    private function _get_validasi_css() {
        return "
        <style>
        /* Alert styling */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1.5rem;
        }

        .alert-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .alert-icon {
            float: left;
            font-size: 1.8rem;
            margin-right: 20px;
            margin-top: 5px;
        }

        .alert-content {
            overflow: hidden;
        }

        .alert-title {
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .alert ul {
            padding-left: 20px;
            margin-bottom: 0;
        }

        .alert li {
            margin-bottom: 8px;
            font-weight: 500;
        }

        /* Decision options */
        .decision-options {
            background: #f8f9fe;
            border-radius: 12px;
            padding: 25px;
            margin-top: 15px;
        }

        .decision-label {
            cursor: pointer;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 0;
            transition: all 0.3s ease;
            background: white;
        }

        .decision-label:hover {
            border-color: #5e72e4;
            box-shadow: 0 4px 12px rgba(94, 114, 228, 0.15);
        }

        .custom-control-input:checked + .approve-label {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .custom-control-input:checked + .reject-label {  
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .decision-content {
            display: flex;
            align-items: center;
        }

        .decision-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .decision-text strong {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .decision-text small {
            color: #6c757d;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Email preview */
        .email-preview {
            background: #f8f9fe;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 25px;
            min-height: 120px;
        }

        /* Form controls */
        .form-control-label {
            font-weight: 600;
            color: #32325d;
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #cad1d7;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: #5e72e4;
            box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
        }

        /* Button styling */
        .btn {
            font-weight: 600;
            letter-spacing: 0.025em;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        </style>
        ";
    }
}