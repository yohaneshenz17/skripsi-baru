<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FIXED & COMPLETE - Kaprodi Seminar Proposal Controller 
 * 
 * PERBAIKAN LENGKAP:
 * 1. Tambah sistem notifikasi email lengkap
 * 2. Tambah method penjadwalan seminar (simpan_jadwal)  
 * 3. Follow workflow sesuai requirements
 * 4. Handle pengajuan ulang mahasiswa
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Kaprodi  
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (Complete Workflow)
 */
class Seminar_proposal extends CI_Controller {
    
    private $prodi_id;
    
    public function __construct() {
        parent::__construct();
        
        $this->load->database();
        $this->load->library(['session', 'email', 'upload']);
        $this->load->helper(['url', 'file', 'security', 'form', 'email_workflow']);
        
        // Load model yang sama dengan dosen (tidak perlu diubah)
        $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        
        // Auth check untuk kaprodi
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        // Get prodi_id dari session
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
     * Detail pengajuan seminar proposal
     */
    public function detail($seminar_id) {
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }
        
        $seminar = $this->seminar_model->get_by_id($seminar_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        // Validasi akses kaprodi
        if (!$this->_is_mahasiswa_from_prodi($seminar->mahasiswa_id)) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke data ini!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $data = [
            'title' => 'Review Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'seminar' => $seminar,
            'jurnal_bimbingan_count' => $this->_get_jurnal_count($seminar->proposal_id),
            'is_jurnal_sufficient' => $this->_check_jurnal_sufficient($seminar->proposal_id)
        ];
        
        $this->load->view('kaprodi/seminar_proposal/detail', $data);
    }
    
    /**
     * ✅ FIXED: Proses validasi plagiarisme dengan notifikasi email lengkap
     */
    public function validasi_plagiarisme() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $plagiarism_percentage = $this->input->post('plagiarism_percentage');
        $keputusan = $this->input->post('keputusan');
        $komentar = trim($this->input->post('komentar_kaprodi'));
        
        // Validasi input
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
        
        // ✅ WORKFLOW RULE: Validasi threshold plagiarisme 30%
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
        
        // Handle file upload
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
        
        // ✅ Get seminar data untuk notifikasi
        $seminar = $this->seminar_model->get_by_id($seminar_id);
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
            redirect('kaprodi/seminar_proposal');
            return;
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
            
            // ✅ WORKFLOW: Update status sesuai keputusan
            if ($keputusan === 'approve') {
                $update_data['status'] = 'approved';
                $update_data['current_step'] = 'staf_jadwal'; // Lanjut ke penjadwalan
            } else {
                $update_data['status'] = 'rejected'; 
                $update_data['current_step'] = 'mahasiswa'; // Kembali ke mahasiswa untuk perbaikan
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_proposal_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan data');
            }
            
            // ✅ KIRIM NOTIFIKASI EMAIL LENGKAP
            $this->_kirim_notifikasi_validasi($seminar, $keputusan, $plagiarism_percentage, $komentar);
            
            $message = $keputusan === 'approve' ? 
                "Seminar proposal disetujui! Plagiarisme: {$plagiarism_percentage}%. Silakan lanjut ke penjadwalan." :
                "Seminar proposal ditolak. Mahasiswa akan mendapat notifikasi untuk perbaikan.";
                
            $this->session->set_flashdata('success', $message);
            if ($keputusan === 'approve') {
                redirect('kaprodi/seminar_proposal/jadwal/' . $seminar_id);
                return;
            }
            
            // Redirect ke jadwal jika disetujui
            if ($keputusan === 'approve') {
                redirect('kaprodi/seminar_proposal/jadwal/' . $seminar_id);
            }
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error validasi plagiarisme: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
        
        redirect('kaprodi/seminar_proposal');
    }
    
    /**
     * ✅ NEW: Form penjadwalan seminar proposal
     */
    public function jadwal($seminar_id) {
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }
        
        // ✅ PERBAIKAN: Get seminar data dengan query manual untuk memastikan semua field ada
        $this->db->select('
            spm.*,
            pm.judul,
            pm.dosen_id as pembimbing_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            d.nama as nama_pembimbing,
            d.email as email_pembimbing
        ');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('spm.id', $seminar_id);
        
        $seminar = $this->db->get()->row();
        
        if (!$seminar || $seminar->status_kaprodi !== 'approved') {
            $this->session->set_flashdata('error', 'Seminar belum disetujui atau tidak dapat dijadwalkan!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
            
        // 🔧 FIX: Get dosen list untuk penguji (exclude pembimbing)
        $this->db->select('id, nama, nip');
        $this->db->from('dosen');
        $this->db->where('id !=', $seminar->pembimbing_id); // Exclude pembimbing
        // 🔧 FIXED: Ganti 'status' dengan 'level' sesuai struktur database
        $this->db->where_in('level', ['2', '4']); // Level 2=dosen, 4=kaprodi (keduanya bisa jadi penguji)
        $this->db->order_by('nama', 'ASC');
        $dosen_list = $this->db->get()->result();
        
        $data = [
            'title' => 'Penjadwalan Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'seminar' => $seminar,
            'dosen_list' => $dosen_list
        ];
        
        $this->load->view('kaprodi/seminar_proposal/jadwal', $data);
    }
    
    /**
     * ✅ FIXED: Simpan jadwal seminar proposal  
     */
    public function simpan_jadwal() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $tanggal_seminar = $this->input->post('tanggal_seminar');
        $jam_seminar = $this->input->post('jam_seminar');
        $tempat_seminar = trim($this->input->post('tempat_seminar'));
        $penguji1_id = $this->input->post('penguji1_id');
        $penguji2_id = $this->input->post('penguji2_id');
        $catatan_kaprodi = trim($this->input->post('catatan_kaprodi'));
        
        // Validasi input
        if (empty($seminar_id) || empty($tanggal_seminar) || empty($jam_seminar) || 
            empty($tempat_seminar) || empty($penguji1_id) || empty($penguji2_id)) {
            $this->session->set_flashdata('error', 'Semua field wajib diisi!');
            redirect('kaprodi/seminar_proposal/jadwal/' . $seminar_id);
            return;
        }
        
        if ($penguji1_id === $penguji2_id) {
            $this->session->set_flashdata('error', 'Penguji 1 dan Penguji 2 tidak boleh sama!');
            redirect('kaprodi/seminar_proposal/jadwal/' . $seminar_id);
            return;
        }
        
        // Validasi tanggal (minimal H+1)
        $datetime_seminar = $tanggal_seminar . ' ' . $jam_seminar;
        if (strtotime($datetime_seminar) <= strtotime('+1 day')) {
            $this->session->set_flashdata('error', 'Jadwal seminar minimal H+1 dari sekarang!');
            redirect('kaprodi/seminar_proposal/jadwal/' . $seminar_id);
            return;
        }
        
        // ✅ PERBAIKAN: Get seminar data lengkap untuk notifikasi
        $this->db->select('
            spm.*,
            pm.judul,
            pm.dosen_id as pembimbing_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            d.nama as nama_pembimbing,
            d.email as email_pembimbing,
            pr.nama as nama_prodi
        ');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
        $this->db->where('spm.id', $seminar_id);
        $seminar = $this->db->get()->row();
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        // ✅ TAMBAHAN: Validasi status
        if ($seminar->status_kaprodi !== 'approved') {
            $this->session->set_flashdata('error', 'Seminar belum disetujui, tidak dapat dijadwalkan!');
            redirect('kaprodi/seminar_proposal');
            return;
        }
        
        $this->db->trans_start();
        
        try {
            // ✅ PERBAIKAN: Update data dengan field yang pasti ada
            $update_data = [
                'tanggal_seminar' => $tanggal_seminar,
                'jam_seminar' => $jam_seminar,
                'tempat_seminar' => $tempat_seminar,
                'dosen_penguji1_id' => $penguji1_id,
                'dosen_penguji2_id' => $penguji2_id,
                'status' => 'scheduled',
                'current_step' => 'pelaksanaan',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Tambah catatan jika ada
            if (!empty($catatan_kaprodi)) {
                $update_data['komentar_kaprodi'] = $catatan_kaprodi;
            }
            
            // Tambah field tracking jika ada di database
            if ($this->db->field_exists('tanggal_jadwal', 'seminar_proposal_mahasiswa')) {
                $update_data['tanggal_jadwal'] = date('Y-m-d H:i:s');
            }
            if ($this->db->field_exists('dijadwalkan_oleh', 'seminar_proposal_mahasiswa')) {
                $update_data['dijadwalkan_oleh'] = $this->session->userdata('id');
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_proposal_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan jadwal');
            }
            
            // ✅ KIRIM NOTIFIKASI EMAIL KE SEMUA PIHAK
            $this->_kirim_notifikasi_jadwal($seminar, $update_data);
            
            $this->session->set_flashdata('success', 
                'Jadwal seminar berhasil disimpan! Notifikasi telah dikirim ke semua pihak terkait.');
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error simpan jadwal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('kaprodi/seminar_proposal');
    }

    // =================================================================
    // HELPER METHODS 
    // =================================================================
    
    /**
     * Check apakah mahasiswa dari prodi yang sama
     */
    private function _is_mahasiswa_from_prodi($mahasiswa_id) {
        $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
        return $mahasiswa && $mahasiswa->prodi_id == $this->prodi_id;
    }
    
    /**
     * Get pengajuan yang perlu review
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
        $this->db->where('spm.status_pembimbing', 'approved');
        $this->db->where('spm.status_kaprodi', 'pending');
        $this->db->order_by('spm.created_at', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get statistik untuk dashboard
     */
    private function _get_statistics() {
        $stats = [];
        
        // Count pending reviews
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status', 'review_kaprodi');
        $this->db->where('spm.status_pembimbing', 'approved');
        $stats['pending_review'] = $this->db->count_all_results();
        
        // Count approved this month
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status_kaprodi', 'approved');
        $this->db->where('MONTH(spm.tanggal_review_kaprodi)', date('m'));
        $this->db->where('YEAR(spm.tanggal_review_kaprodi)', date('Y'));
        $stats['approved_month'] = $this->db->count_all_results();
        
        // Count rejected this month
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status_kaprodi', 'rejected');
        $this->db->where('MONTH(spm.tanggal_review_kaprodi)', date('m'));
        $this->db->where('YEAR(spm.tanggal_review_kaprodi)', date('Y'));
        $stats['rejected_month'] = $this->db->count_all_results();
        
        // Count scheduled this month
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('spm.status', 'scheduled');
        $this->db->where('MONTH(spm.tanggal_seminar)', date('m'));
        $this->db->where('YEAR(spm.tanggal_seminar)', date('Y'));
        $stats['scheduled_month'] = $this->db->count_all_results();
        
        return $stats;
    }
    
    /**
     * Get recent approved
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
                        ->where('status_validasi', '1')
                        ->count_all_results('jurnal_bimbingan');
    }
    
    /**
     * Check jurnal sufficient (minimal 8)
     */
    private function _check_jurnal_sufficient($proposal_id) {
        $count = $this->_get_jurnal_count($proposal_id);
        return $count >= 8;
    }
    
    /**
     * Handle file upload dengan validation
     */
    private function _handle_file_upload() {
        $upload_path = FCPATH . 'uploads/turnitin/';
        
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
    
    /**
     * ✅ NEW: Kirim notifikasi validasi plagiarisme
     */
    private function _kirim_notifikasi_validasi($seminar, $keputusan, $plagiarism_percentage, $komentar) {
        try {
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls'
            ];
            
            $this->email->initialize($config);
            
            if ($keputusan === 'approve') {
                // ✅ DISETUJUI - Kirim ke mahasiswa dan dosen pembimbing
                $this->_kirim_email_proposal_disetujui($seminar, $plagiarism_percentage);
            } else {
                // ✅ DITOLAK - Kirim ke mahasiswa dan dosen pembimbing  
                $this->_kirim_email_proposal_ditolak($seminar, $plagiarism_percentage, $komentar);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending validation email: ' . $e->getMessage());
        }
    }
    
    /**
     * ✅ NEW: Kirim notifikasi jadwal seminar
     */
    private function _kirim_notifikasi_jadwal($seminar, $jadwal_data) {
        try {
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls'
            ];
            
            $this->email->initialize($config);
            
            // Get data dosen penguji
            $penguji1 = $this->db->get_where('dosen', ['id' => $jadwal_data['dosen_penguji1_id']])->row();
            $penguji2 = $this->db->get_where('dosen', ['id' => $jadwal_data['dosen_penguji2_id']])->row();
            
            // ✅ Kirim ke mahasiswa
            $this->_kirim_email_jadwal_mahasiswa($seminar, $jadwal_data, $penguji1, $penguji2);
            
            // ✅ Kirim ke dosen pembimbing  
            $this->_kirim_email_jadwal_pembimbing($seminar, $jadwal_data, $penguji1, $penguji2);
            
            // ✅ Kirim ke dosen penguji 1 & 2
            $this->_kirim_email_jadwal_penguji($seminar, $jadwal_data, $penguji1, 'Penguji 1');
            $this->_kirim_email_jadwal_penguji($seminar, $jadwal_data, $penguji2, 'Penguji 2');
            
            // ✅ Kirim ke staf  
            $this->_kirim_email_jadwal_staf($seminar, $jadwal_data, $penguji1, $penguji2);
            
        } catch (Exception $e) {
            log_message('error', 'Error sending schedule email: ' . $e->getMessage());
        }
    }
    
    /**
     * Email template untuk proposal disetujui
     */
    private function _kirim_email_proposal_disetujui($seminar, $plagiarism_percentage) {
        // Email ke mahasiswa
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($seminar->email_mahasiswa);
        $this->email->subject('✅ Seminar Proposal Disetujui - ' . $seminar->nama_mahasiswa);
        
        $message = "
        <h2 style='color: #28a745;'>✅ Seminar Proposal Disetujui</h2>
        <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
        <p>Selamat! Seminar proposal Anda telah <strong>DISETUJUI</strong> oleh Ketua Program Studi dengan hasil validasi plagiarisme <strong>{$plagiarism_percentage}%</strong> (di bawah batas maksimal 30%).</p>
        <p><strong>Judul:</strong> {$seminar->judul}</p>
        <p>Selanjutnya, Kaprodi akan melakukan penjadwalan seminar dan penunjukan dosen penguji. Anda akan mendapat notifikasi jadwal seminar melalui email.</p>
        <p>Terima kasih.</p>
        ";
        
        $this->email->message($message);
        $this->email->send();
        
        // Email ke dosen pembimbing
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($seminar->email_pembimbing);
        $this->email->subject('📋 Seminar Proposal Mahasiswa Bimbingan Disetujui');
        
        $message = "
        <h2 style='color: #28a745;'>📋 Seminar Proposal Disetujui</h2>
        <p>Kepada Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
        <p>Seminar proposal mahasiswa bimbingan Anda telah <strong>DISETUJUI</strong> oleh Kaprodi:</p>
        <ul>
        <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
        <li><strong>Judul:</strong> {$seminar->judul}</li>
        <li><strong>Plagiarisme:</strong> {$plagiarism_percentage}%</li>
        </ul>
        <p>Selanjutnya akan dilakukan penjadwalan seminar dan penunjukan dosen penguji.</p>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email template untuk proposal ditolak
     */
    private function _kirim_email_proposal_ditolak($seminar, $plagiarism_percentage, $komentar) {
        // Email ke mahasiswa
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($seminar->email_mahasiswa);
        $this->email->subject('❌ Seminar Proposal Perlu Perbaikan - ' . $seminar->nama_mahasiswa);
        
        $message = "
        <h2 style='color: #dc3545;'>❌ Seminar Proposal Perlu Perbaikan</h2>
        <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
        <p>Seminar proposal Anda memerlukan perbaikan berdasarkan hasil review Ketua Program Studi:</p>
        <ul>
        <li><strong>Judul:</strong> {$seminar->judul}</li>
        <li><strong>Plagiarisme:</strong> {$plagiarism_percentage}%</li>
        </ul>
        <p><strong>Catatan Kaprodi:</strong><br>{$komentar}</p>
        <p>Silakan lakukan perbaikan sesuai catatan di atas dan mengajukan kembali seminar proposal melalui sistem.</p>
        <p>Terima kasih.</p>
        ";
        
        $this->email->message($message);
        $this->email->send();
        
        // Email ke dosen pembimbing
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($seminar->email_pembimbing);
        $this->email->subject('📋 Seminar Proposal Mahasiswa Bimbingan Perlu Perbaikan');
        
        $message = "
        <h2 style='color: #dc3545;'>📋 Seminar Proposal Perlu Perbaikan</h2>
        <p>Kepada Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
        <p>Seminar proposal mahasiswa bimbingan Anda memerlukan perbaikan:</p>
        <ul>
        <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
        <li><strong>Judul:</strong> {$seminar->judul}</li>
        <li><strong>Plagiarisme:</strong> {$plagiarism_percentage}%</li>
        </ul>
        <p><strong>Catatan Kaprodi:</strong><br>{$komentar}</p>
        <p>Mohon bantu membimbing mahasiswa untuk melakukan perbaikan sesuai catatan di atas.</p>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email jadwal ke mahasiswa
     */
    private function _kirim_email_jadwal_mahasiswa($seminar, $jadwal, $penguji1, $penguji2) {
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($seminar->email_mahasiswa);
        $this->email->subject('📅 Jadwal Seminar Proposal - ' . $seminar->nama_mahasiswa);
        
        $tanggal_indo = date('d F Y', strtotime($jadwal['tanggal_seminar']));
        $jam = date('H:i', strtotime($jadwal['jam_seminar']));
        
        $message = "
        <h2 style='color: #007bff;'>📅 Jadwal Seminar Proposal</h2>
        <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
        <p>Seminar proposal Anda telah dijadwalkan:</p>
        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
        <h3>Detail Seminar:</h3>
        <ul>
        <li><strong>Judul:</strong> {$seminar->judul}</li>
        <li><strong>Tanggal:</strong> {$tanggal_indo}</li>
        <li><strong>Waktu:</strong> {$jam} WIT</li>
        <li><strong>Tempat:</strong> {$jadwal['tempat_seminar']}</li>
        </ul>
        <h3>Tim Penguji:</h3>
        <ul>
        <li><strong>Pembimbing:</strong> {$seminar->nama_pembimbing}</li>
        <li><strong>Penguji 1:</strong> {$penguji1->nama}</li>
        <li><strong>Penguji 2:</strong> {$penguji2->nama}</li>
        </ul>
        </div>
        <p>Silakan mempersiapkan diri dengan baik. Selamat!</p>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email jadwal ke dosen pembimbing
     */
    private function _kirim_email_jadwal_pembimbing($seminar, $jadwal, $penguji1, $penguji2) {
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($seminar->email_pembimbing);
        $this->email->subject('📅 Jadwal Seminar Proposal Mahasiswa Bimbingan');
        
        $tanggal_indo = date('d F Y', strtotime($jadwal['tanggal_seminar']));
        $jam = date('H:i', strtotime($jadwal['jam_seminar']));
        
        $message = "
        <h2 style='color: #007bff;'>📅 Jadwal Seminar Proposal</h2>
        <p>Kepada Yth. <strong>{$seminar->nama_pembimbing}</strong>,</p>
        <p>Seminar proposal mahasiswa bimbingan Anda telah dijadwalkan:</p>
        <ul>
        <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
        <li><strong>Tanggal:</strong> {$tanggal_indo}</li>
        <li><strong>Waktu:</strong> {$jam} WIT</li>
        <li><strong>Tempat:</strong> {$jadwal['tempat_seminar']}</li>
        <li><strong>Penguji 1:</strong> {$penguji1->nama}</li>
        <li><strong>Penguji 2:</strong> {$penguji2->nama}</li>
        </ul>
        <p>Mohon hadir tepat waktu. Terima kasih.</p>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email jadwal ke dosen penguji
     */
    private function _kirim_email_jadwal_penguji($seminar, $jadwal, $penguji, $role) {
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($penguji->email);
        $this->email->subject('📅 Penunjukan ' . $role . ' Seminar Proposal');
        
        $tanggal_indo = date('d F Y', strtotime($jadwal['tanggal_seminar']));
        $jam = date('H:i', strtotime($jadwal['jam_seminar']));
        
        $message = "
        <h2 style='color: #007bff;'>📅 Penunjukan {$role} Seminar Proposal</h2>
        <p>Kepada Yth. <strong>{$penguji->nama}</strong>,</p>
        <p>Anda ditunjuk sebagai <strong>{$role}</strong> pada seminar proposal:</p>
        <ul>
        <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
        <li><strong>Judul:</strong> {$seminar->judul}</li>
        <li><strong>Tanggal:</strong> {$tanggal_indo}</li>
        <li><strong>Waktu:</strong> {$jam} WIT</li>
        <li><strong>Tempat:</strong> {$jadwal['tempat_seminar']}</li>
        <li><strong>Pembimbing:</strong> {$seminar->nama_pembimbing}</li>
        </ul>
        <p>Sesuai kebijakan STK Santo Yakobus, penunjukan ini tidak memerlukan konfirmasi kesediaan. Mohon hadir tepat waktu.</p>
        <p>Terima kasih.</p>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }
    
    /**
     * Email jadwal ke staf
     */
    private function _kirim_email_jadwal_staf($seminar, $jadwal, $penguji1, $penguji2) {
        // Get email staf dari database atau config
        $email_staf = 'admin@stkyakobus.ac.id'; // Sesuaikan dengan email staf
        
        $this->email->clear();
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($email_staf);
        $this->email->subject('📅 Jadwal Seminar Proposal - Persiapan Administrasi');
        
        $tanggal_indo = date('d F Y', strtotime($jadwal['tanggal_seminar']));
        $jam = date('H:i', strtotime($jadwal['jam_seminar']));
        
        $message = "
        <h2 style='color: #007bff;'>📅 Jadwal Seminar Proposal</h2>
        <p>Kepada Yth. Tim Administrasi,</p>
        <p>Terdapat jadwal seminar proposal yang perlu dipersiapkan:</p>
        <ul>
        <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
        <li><strong>Judul:</strong> {$seminar->judul}</li>
        <li><strong>Tanggal:</strong> {$tanggal_indo}</li>
        <li><strong>Waktu:</strong> {$jam} WIT</li>
        <li><strong>Tempat:</strong> {$jadwal['tempat_seminar']}</li>
        <li><strong>Pembimbing:</strong> {$seminar->nama_pembimbing}</li>
        <li><strong>Penguji 1:</strong> {$penguji1->nama}</li>
        <li><strong>Penguji 2:</strong> {$penguji2->nama}</li>
        </ul>
        <p>Mohon dipersiapkan:</p>
        <ul>
        <li>Ruang dan fasilitas seminar</li>
        <li>Berita acara seminar proposal</li>
        <li>Form penilaian</li>
        </ul>
        <p>Terima kasih.</p>
        ";
        
        $this->email->message($message);
        $this->email->send();
    }
}
?>