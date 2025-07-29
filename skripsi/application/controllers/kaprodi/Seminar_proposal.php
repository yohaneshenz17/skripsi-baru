<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MINIMAL FIX - Kaprodi Seminar Proposal Controller 
 * 
 * PERBAIKAN SEDERHANA:
 * 1. Load form helper untuk mengatasi error form_open_multipart()
 * 2. Gunakan pattern yang sama dengan dosen controller
 * 3. Tidak perlu ubah model - model sudah bekerja untuk dosen & mahasiswa
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Kaprodi  
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.1 (Minimal Fix)
 */
class Seminar_proposal extends CI_Controller {
    
    private $prodi_id;
    
    public function __construct() {
        parent::__construct();
        
        // PERBAIKAN UTAMA: Load form helper untuk mengatasi error form_open_multipart()
        $this->load->database();
        $this->load->library(['session', 'email', 'upload']);
        $this->load->helper(['url', 'file', 'security', 'form']); // ✅ TAMBAH form helper
        
        // Load model yang sama dengan dosen (tidak perlu diubah)
        $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        
        // Auth check untuk kaprodi
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        // Get prodi_id dari session (sama seperti controller kaprodi lain)
        $this->prodi_id = $this->session->userdata('prodi_id');
        if (!$this->prodi_id) {
            $kaprodi = $this->db->get_where('prodi', ['dosen_id' => $this->session->userdata('id')])->row();
            if ($kaprodi) {
                $this->session->set_userdata('prodi_id', $kaprodi->id);
                $this->prodi_id = $kaprodi->id;
            }
        }
    }
    
    /**
     * Dashboard Seminar Proposal Kaprodi
     */
    public function index() {
        $data = [
            'title' => 'Kelola Seminar Proposal',
            'pending_reviews' => $this->_get_pengajuan_perlu_review(),
            'statistics' => $this->_get_statistics(),
            'recent_approved' => $this->_get_recent_approved()
        ];
        
        $this->load->view('kaprodi/seminar_proposal/index', $data);
    }
    
    /**
     * PERBAIKAN: Detail menggunakan pattern yang sama dengan dosen
     * Model tidak perlu diubah - gunakan method yang sudah ada
     */
    public function detail($seminar_id) {
        // Validasi input
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }
        
        // GUNAKAN MODEL YANG SAMA seperti dosen - tidak perlu diubah
        $seminar = $this->seminar_model->get_by_id($seminar_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        // Validasi akses kaprodi (mahasiswa harus dari prodi yang sama)
        if (!$this->_is_mahasiswa_from_prodi($seminar->mahasiswa_id)) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke data ini!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        // Prepare data untuk view (gunakan pattern yang sama dengan dosen)
        $data = [
            'title' => 'Review Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'seminar' => $seminar,
            // Tambahan data spesifik kaprodi
            'jurnal_bimbingan_count' => $this->_get_jurnal_count($seminar->proposal_id),
            'is_jurnal_sufficient' => $this->_check_jurnal_sufficient($seminar->proposal_id)
        ];
        
        // Load view dengan template kaprodi
        $this->load->view('template/kaprodi', [
            'title' => $data['title'],
            'content' => $this->load->view('kaprodi/seminar_proposal/detail', $data, TRUE)
        ]);
    }
    
    /**
     * PERBAIKAN: Proses validasi plagiarisme dengan error handling
     */
    public function validasi_plagiarisme() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $plagiarism_percentage = $this->input->post('plagiarism_percentage');
        $keputusan = $this->input->post('keputusan'); // 'approve' atau 'reject'
        $komentar = trim($this->input->post('komentar_kaprodi'));
        
        // Validasi input dasar
        if (empty($seminar_id) || empty($keputusan)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        if (empty($plagiarism_percentage) || !is_numeric($plagiarism_percentage)) {
            $this->session->set_flashdata('error', 'Persentase plagiarisme harus diisi dengan angka!');
            redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        $plagiarism_percentage = floatval($plagiarism_percentage);
        
        // Validasi threshold plagiarisme
        if ($keputusan === 'approve' && $plagiarism_percentage >= 30) {
            $this->session->set_flashdata('error', 'Proposal dengan plagiarisme ≥30% tidak dapat disetujui!');
            redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        if ($keputusan === 'reject' && empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar wajib diisi untuk penolakan!');
            redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        // Handle file upload jika ada
        $turnitin_file = null;
        if (!empty($_FILES['file_turnitin']['name'])) {
            $upload_result = $this->_handle_file_upload();
            if (!$upload_result['success']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
                return;
            }
            $turnitin_file = $upload_result['filename'];
        }
        
        // Proses update ke database
        $this->db->trans_start();
        
        try {
            $update_data = [
                'status_kaprodi' => $keputusan === 'approve' ? 'approved' : 'rejected',
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'reviewed_by_kaprodi' => $this->session->userdata('id'),
                'plagiarism_percentage' => $plagiarism_percentage,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($turnitin_file) {
                $update_data['file_turnitin'] = $turnitin_file;
            }
            
            // Update status workflow
            if ($keputusan === 'approve') {
                $update_data['status'] = 'approved';
                $update_data['current_step'] = 'staf';
            } else {
                $update_data['status'] = 'rejected'; 
                $update_data['current_step'] = 'mahasiswa';
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_proposal_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan data');
            }
            
            $message = $keputusan === 'approve' ? 
                "Seminar proposal disetujui! Plagiarisme: {$plagiarism_percentage}%." :
                "Seminar proposal ditolak. Mahasiswa akan mendapat notifikasi.";
                
            $this->session->set_flashdata('success', $message);
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error validasi plagiarisme: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
        
        redirect('kaprodi/seminar_proposal');
    }
    
    // =================================================================
    // HELPER METHODS - MINIMAL YANG DIPERLUKAN
    // =================================================================
    
    /**
     * Check apakah mahasiswa dari prodi yang sama
     */
    private function _is_mahasiswa_from_prodi($mahasiswa_id) {
        $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
        return $mahasiswa && $mahasiswa->prodi_id == $this->prodi_id;
    }
    
    /**
     * Get pengajuan yang perlu review (minimal query)
     */
    private function _get_pengajuan_perlu_review() {
        $this->db->select('
            spm.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing
        ');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status', 'review_kaprodi');
        $this->db->order_by('spm.created_at', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get statistics sederhana
     */
    private function _get_statistics() {
        // Total pengajuan
        $total = $this->db->join('mahasiswa m', 'seminar_proposal_mahasiswa.mahasiswa_id = m.id')
                          ->where('m.prodi_id', $this->prodi_id)
                          ->count_all_results('seminar_proposal_mahasiswa');
        
        // Pending review
        $pending = $this->db->join('mahasiswa m', 'seminar_proposal_mahasiswa.mahasiswa_id = m.id')
                            ->where('m.prodi_id', $this->prodi_id)
                            ->where('seminar_proposal_mahasiswa.status', 'review_kaprodi')
                            ->count_all_results('seminar_proposal_mahasiswa');
        
        // Approved this month
        $approved = $this->db->join('mahasiswa m', 'seminar_proposal_mahasiswa.mahasiswa_id = m.id')
                             ->where('m.prodi_id', $this->prodi_id)
                             ->where('seminar_proposal_mahasiswa.status_kaprodi', 'approved')
                             ->where('MONTH(seminar_proposal_mahasiswa.tanggal_review_kaprodi)', date('n'))
                             ->count_all_results('seminar_proposal_mahasiswa');
        
        return [
            'total' => $total,
            'pending' => $pending,
            'approved_this_month' => $approved
        ];
    }
    
    /**
     * Get recent approved (untuk dashboard)
     */
    private function _get_recent_approved() {
        $this->db->select('spm.*, pm.judul, m.nama as nama_mahasiswa');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status_kaprodi', 'approved');
        $this->db->order_by('spm.tanggal_review_kaprodi', 'DESC');
        $this->db->limit(5);
        
        return $this->db->get()->result();
    }
    
    /**
     * Get jurnal bimbingan count
     */
    private function _get_jurnal_count($proposal_id) {
        return $this->db->where('proposal_id', $proposal_id)
                        ->where('status_validasi', 'valid')
                        ->count_all_results('jurnal_bimbingan');
    }
    
    /**
     * Check jurnal sufficient (minimal 10)
     */
    private function _check_jurnal_sufficient($proposal_id) {
        $count = $this->_get_jurnal_count($proposal_id);
        return $count >= 10;
    }
    
    /**
     * PERBAIKAN: Handle file upload dengan validation proper
     */
    private function _handle_file_upload() {
        $upload_path = FCPATH . 'uploads/turnitin/';
        
        // Create directory if not exists
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf',
            'max_size' => 5120, // 5MB
            'file_name' => 'TURNITIN_' . time() . '_' . uniqid(),
            'remove_spaces' => true
        ];
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload('file_turnitin')) {
            $upload_data = $this->upload->data();
            return [
                'success' => true,
                'filename' => $upload_data['file_name']
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Upload gagal: ' . $this->upload->display_errors('', '')
            ];
        }
    }
}