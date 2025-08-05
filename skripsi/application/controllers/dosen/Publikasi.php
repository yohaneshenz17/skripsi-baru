// =====================================================
// 2. DOSEN CONTROLLER
// File: application/controllers/dosen/Publikasi.php
// =====================================================

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Publikasi untuk Dosen
 * Handle review dan approve pengajuan publikasi
 */
class Publikasi extends CI_Controller {

    private $dosen_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk dosen
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if(!in_array($this->session->userdata('level'), ['2', '4'])) { // Dosen atau Kaprodi
            show_error('Akses ditolak. Halaman khusus dosen.', 403);
        }
        
        $this->dosen_id = $this->session->userdata('user_id');
    }

    /**
     * Dashboard publikasi untuk dosen
     */
    public function index() {
        $data = [
            'title' => 'Review Publikasi Tugas Akhir',
            'pengajuan_review' => $this->_get_pengajuan_perlu_review(),
            'riwayat_review' => $this->_get_riwayat_review(),
            'statistik' => $this->_get_statistik_dosen()
        ];
        
        $this->load->view('dosen/publikasi/index', $data);
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
     * Approve pengajuan publikasi
     */
    public function approve($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi || $publikasi->dosen_pembimbing_id != $this->dosen_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau akses ditolak.');
            redirect('dosen/publikasi');
        }
        
        if ($publikasi->status_pembimbing !== 'pending') {
            $this->session->set_flashdata('error', 'Pengajuan sudah direview sebelumnya.');
            redirect('dosen/publikasi');
        }
        
        $result = $this->publikasi->approve_by_dosen($publikasi_id, $this->dosen_id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Pengajuan berhasil disetujui dan diteruskan ke staf.');
            
            // Send notification ke staf
            $this->_send_notification_to_staf($publikasi);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('dosen/publikasi');
    }

    /**
     * Reject pengajuan publikasi
     */
    public function reject($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi || $publikasi->dosen_pembimbing_id != $this->dosen_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau akses ditolak.');
            redirect('dosen/publikasi');
        }
        
        $komentar = $this->input->post('komentar_pembimbing');
        if (empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar penolakan harus diisi.');
            redirect('dosen/publikasi/review/' . $publikasi_id);
        }
        
        $result = $this->publikasi->reject_by_dosen($publikasi_id, $this->dosen_id, $komentar);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Pengajuan ditolak dan dikembalikan ke mahasiswa.');
            
            // Send notification ke mahasiswa
            $this->_send_notification_to_mahasiswa($publikasi, 'rejected');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('dosen/publikasi');
    }

    /**
     * Download file publikasi mahasiswa
     */
    public function download($publikasi_id, $file_type) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi || $publikasi->dosen_pembimbing_id != $this->dosen_id) {
            show_404();
        }
        
        $this->_download_file($publikasi, $file_type);
    }

    // =================================================================
    // PRIVATE METHODS
    // =================================================================

    /**
     * Get pengajuan yang perlu direview oleh dosen ini
     */
    private function _get_pengajuan_perlu_review() {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            pr.nama as nama_prodi
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->where('pta.dosen_pembimbing_id', $this->dosen_id);
        $this->db->where_in('pta.status', ['submitted', 'review_pembimbing']);
        $this->db->where('pta.status_pembimbing', 'pending');
        $this->db->order_by('pta.tanggal_pengajuan', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get riwayat review yang sudah diberikan
     */
    private function _get_riwayat_review() {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa,
            pr.nama as nama_prodi
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->where('pta.dosen_pembimbing_id', $this->dosen_id);
        $this->db->where('pta.status_pembimbing !=', 'pending');
        $this->db->where('pta.tanggal_review_pembimbing IS NOT NULL');
        $this->db->order_by('pta.tanggal_review_pembimbing', 'DESC');
        $this->db->limit(10);
        
        return $this->db->get()->result();
    }

    /**
     * Get detail publikasi dengan validasi ownership
     */
    private function _get_publikasi_detail($publikasi_id) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            m.nomor_telepon,
            pr.nama as nama_prodi,
            pm.judul as judul_proposal
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
        $this->db->where('pta.id', $publikasi_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get statistik untuk dashboard dosen
     */
    private function _get_statistik_dosen() {
        $stats = [
            'total_mahasiswa_bimbingan' => 0,
            'pending_review' => 0,
            'sudah_direview' => 0,
            'publikasi_selesai' => 0
        ];
        
        // Total mahasiswa bimbingan yang eligible publikasi (16+ jurnal)
        $this->db->select('COUNT(DISTINCT pm.id) as total');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
        $this->db->where('pm.dosen_id', $this->dosen_id);
        $this->db->group_by('pm.id');
        $this->db->having('COUNT(jb.id) >= 16');
        $result = $this->db->get();
        $stats['total_mahasiswa_bimbingan'] = $result->num_rows();
        
        // Pending review
        $stats['pending_review'] = $this->db->where('dosen_pembimbing_id', $this->dosen_id)
                                          ->where('status_pembimbing', 'pending')
                                          ->where_in('status', ['submitted', 'review_pembimbing'])
                                          ->count_all_results('publikasi_tugas_akhir');
        
        // Sudah direview
        $stats['sudah_direview'] = $this->db->where('dosen_pembimbing_id', $this->dosen_id)
                                          ->where('status_pembimbing !=', 'pending')
                                          ->count_all_results('publikasi_tugas_akhir');
        
        // Publikasi selesai
        $stats['publikasi_selesai'] = $this->db->where('dosen_pembimbing_id', $this->dosen_id)
                                             ->where('status', 'completed')
                                             ->count_all_results('publikasi_tugas_akhir');
        
        return $stats;
    }

    /**
     * Process review from dosen
     */
    private function _process_review($publikasi) {
        $action = $this->input->post('action');
        $komentar = $this->input->post('komentar_pembimbing');
        
        if ($action === 'approve') {
            $result = $this->publikasi->approve_by_dosen($publikasi->id, $this->dosen_id, $komentar);
            $message = 'Pengajuan berhasil disetujui.';
        } elseif ($action === 'reject') {
            if (empty($komentar)) {
                $this->session->set_flashdata('error', 'Komentar penolakan harus diisi.');
                $this->_show_review_form($publikasi);
                return;
            }
            $result = $this->publikasi->reject_by_dosen($publikasi->id, $this->dosen_id, $komentar);
            $message = 'Pengajuan ditolak dan dikembalikan ke mahasiswa.';
        } else {
            $this->session->set_flashdata('error', 'Aksi tidak valid.');
            redirect('dosen/publikasi');
        }
        
        if ($result['success']) {
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('dosen/publikasi');
    }

    /**
     * Show review form
     */
    private function _show_review_form($publikasi) {
        // Get jurnal bimbingan mahasiswa untuk referensi
        $jurnal_bimbingan = $this->_get_jurnal_bimbingan($publikasi->proposal_mahasiswa_id);
        
        $data = [
            'title' => 'Review Publikasi Tugas Akhir',
            'publikasi' => $publikasi,
            'jurnal_bimbingan' => $jurnal_bimbingan
        ];
        $this->load->view('dosen/publikasi/review', $data);
    }

    /**
     * Get jurnal bimbingan mahasiswa
     */
    private function _get_jurnal_bimbingan($proposal_id) {
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('status_validasi', '1');
        $this->db->order_by('tanggal_bimbingan', 'DESC');
        $this->db->limit(10);
        
        return $this->db->get()->result();
    }

    /**
     * Download file dari publikasi
     */
    private function _download_file($publikasi, $file_type) {
        $file_mapping = [
            'surat_revisi' => $publikasi->file_surat_revisi,
            'skripsi_final' => $publikasi->file_skripsi_final,
            'surat_perpustakaan' => $publikasi->file_surat_perpustakaan
        ];
        
        if (!isset($file_mapping[$file_type])) {
            show_404();
        }
        
        $filename = $file_mapping[$file_type];
        $file_path = "./uploads/publikasi/{$file_type}/{$filename}";
        
        if (!file_exists($file_path)) {
            show_404();
        }
        
        $this->load->helper('download');
        force_download($filename, file_get_contents($file_path));
    }

    /**
     * Send notification to staf
     */
    private function _send_notification_to_staf($publikasi) {
        // TODO: Implement notification system
        log_message('info', "Notification sent to staf for publikasi_id: {$publikasi->id}");
    }

    /**
     * Send notification to mahasiswa
     */
    private function _send_notification_to_mahasiswa($publikasi, $status) {
        // TODO: Implement notification system
        log_message('info', "Notification sent to mahasiswa_id: {$publikasi->mahasiswa_id} with status: {$status}");
    }
}