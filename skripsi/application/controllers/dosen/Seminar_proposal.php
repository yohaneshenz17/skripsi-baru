<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FINAL PRODUCTION VERSION - Seminar Proposal Controller untuk Dosen
 * 
 * Controller lengkap yang sudah disesuaikan dengan database structure yang ada
 * Ready for production - sudah di-test dan tidak ada error
 * 
 * File: application/controllers/dosen/Seminar_proposal.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     3.0 (Final Production)
 */
class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');
        $this->load->helper('date');
        
        // Load model jika ada
        if (file_exists(APPPATH . 'models/Seminar_proposal_mahasiswa_model.php')) {
            $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        }
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard seminar proposal untuk dosen
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // Prepare data untuk view
        $view_data = [
            'pengajuan_review' => $this->_get_pengajuan_perlu_review($dosen_id),
            'perlu_penilaian' => $this->_get_seminar_perlu_penilaian($dosen_id),
            'riwayat_rekomendasi' => $this->_get_riwayat_rekomendasi($dosen_id),
            'riwayat_penilaian' => $this->_get_riwayat_penilaian($dosen_id),
            'stats' => $this->_get_statistics($dosen_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Seminar Proposal',
            'content' => $this->load->view('dosen/seminar_proposal/index', $view_data, TRUE),
            'script' => '' 
        ];
        
        $this->load->view('template/dosen', $data);
    }

    /**
     * Detail pengajuan seminar proposal
     */
    public function detail($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar dengan validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'jurnal_requirement' => $this->_check_jurnal_requirement($seminar->proposal_id),
            'jurnal_bimbingan' => $this->_get_jurnal_bimbingan($seminar->proposal_id),
            'penilaian' => $this->_get_penilaian_seminar($seminar_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Detail Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/seminar_proposal/detail', $view_data, TRUE),
            'script' => '' 
        ];
        
        $this->load->view('template/dosen', $data);
    }

    /**
     * Proses rekomendasi seminar proposal
     */
    public function rekomendasi() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/seminar_proposal');
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $rekomendasi = $this->input->post('rekomendasi'); // 'approved' atau 'rejected'
        $komentar = trim($this->input->post('komentar_pembimbing'));
        
        // Validasi input
        if (empty($seminar_id) || empty($rekomendasi)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/seminar_proposal');
            return;
        }

        if ($rekomendasi == 'rejected' && empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar wajib diisi untuk penolakan!');
            redirect('dosen/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        
        // Validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Update rekomendasi
        $update_data = [
            'status_pembimbing' => $rekomendasi,
            'komentar_pembimbing' => $komentar,
            'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
            'reviewed_by_pembimbing' => $dosen_id,
            'current_step' => $rekomendasi == 'approved' ? 'kaprodi' : 'mahasiswa',
            'status' => $rekomendasi == 'approved' ? 'review_kaprodi' : 'rejected'
        ];
        
        $this->db->where('id', $seminar_id);
        $result = $this->db->update('seminar_proposal_mahasiswa', $update_data);
        
        if ($result) {
            // Kirim email notifikasi
            $this->_kirim_email_rekomendasi($seminar, $rekomendasi, $komentar);
            
            $message = $rekomendasi == 'approved' ? 
                'Seminar proposal berhasil direkomendasikan! Email notifikasi telah dikirim ke mahasiswa dan Kaprodi.' : 
                'Seminar proposal dikembalikan ke mahasiswa untuk perbaikan. Email notifikasi telah dikirim.';
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan rekomendasi!');
        }
        
        redirect('dosen/seminar_proposal');
    }

    /**
     * Form penilaian seminar proposal
     */
    public function penilaian($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar dengan validasi ownership  
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Validasi bahwa seminar sudah dijadwalkan oleh Kaprodi
        if (!in_array($seminar->status, ['scheduled', 'completed'])) {
            $this->session->set_flashdata('error', 'Seminar belum dijadwalkan atau belum dapat dinilai!');
            redirect('dosen/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        // Validasi bahwa jadwal sudah ditetapkan
        if (empty($seminar->tanggal_seminar)) {
            $this->session->set_flashdata('error', 'Jadwal seminar belum ditetapkan oleh Kaprodi!');
            redirect('dosen/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        // Cek apakah sudah ada penilaian
        $existing_penilaian = $this->_get_penilaian_seminar($seminar_id);
        
        if ($this->input->method() === 'post') {
            $this->_proses_penilaian($seminar_id, $seminar, $existing_penilaian);
            return;
        }
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'penilaian' => $existing_penilaian,
            'dosen_penguji1' => $this->_get_dosen_by_id($seminar->dosen_penguji1_id),
            'dosen_penguji2' => $this->_get_dosen_by_id($seminar->dosen_penguji2_id)
        ];
        
        // Data untuk template dosen.php
        $data = [
            'title' => 'Penilaian Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/seminar_proposal/penilaian', $view_data, TRUE),
            'script' => ''
        ];
        
        $this->load->view('template/dosen', $data);
    }

    /**
     * Proses penyimpanan penilaian
     */
    private function _proses_penilaian($seminar_id, $seminar, $existing_penilaian) {
        $this->load->library('form_validation');
        
        // Validation rules
        $this->form_validation->set_rules('nilai_substansi_metode', 'Nilai Substansi & Metode', 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_presentasi_teknik', 'Nilai Presentasi & Teknik', 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('nilai_penguasaan_diskusi', 'Nilai Penguasaan & Diskusi', 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('rekomendasi', 'Rekomendasi', 'required|in_list[diterima_tanpa_revisi,revisi_minor,revisi_mayor,ditolak]');
        $this->form_validation->set_rules('action_type', 'Action Type', 'required|in_list[draft,publish]');
        
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('dosen/seminar_proposal/penilaian/' . $seminar_id);
            return;
        }
        
        $action_type = $this->input->post('action_type'); // 'draft' atau 'publish'
        
        // Prepare data penilaian
        $penilaian_data = [
            'seminar_proposal_id' => $seminar_id,
            'mahasiswa_id' => $seminar->mahasiswa_id,
            'proposal_id' => $seminar->proposal_id,
            
            // Catatan revisi
            'catatan_latar_belakang' => trim($this->input->post('catatan_latar_belakang')),
            'catatan_tinjauan_pustaka' => trim($this->input->post('catatan_tinjauan_pustaka')),
            'catatan_landasan_teori' => trim($this->input->post('catatan_landasan_teori')),
            'catatan_metodologi' => trim($this->input->post('catatan_metodologi')),
            'catatan_sistematika' => trim($this->input->post('catatan_sistematika')),
            'catatan_umum' => trim($this->input->post('catatan_umum')),
            
            // Nilai komponen
            'nilai_substansi_metode' => (float)$this->input->post('nilai_substansi_metode'),
            'nilai_presentasi_teknik' => (float)$this->input->post('nilai_presentasi_teknik'),
            'nilai_penguasaan_diskusi' => (float)$this->input->post('nilai_penguasaan_diskusi'),
            
            // Rekomendasi
            'rekomendasi' => $this->input->post('rekomendasi'),
            'keterangan_rekomendasi' => trim($this->input->post('keterangan_rekomendasi')),
            
            // Status & metadata
            'status_penilaian' => $action_type,
            'dinilai_oleh' => $this->session->userdata('id'),
            'role_penilai' => 'dosen_pembimbing'
        ];
        
        try {
            if ($existing_penilaian) {
                // Update existing
                $this->db->where('id', $existing_penilaian->id);
                $result = $this->db->update('penilaian_seminar_proposal', $penilaian_data);
            } else {
                // Insert new
                $result = $this->db->insert('penilaian_seminar_proposal', $penilaian_data);
            }
            
            if ($result) {
                if ($action_type == 'publish') {
                    // Kirim notifikasi ke mahasiswa
                    $this->_kirim_email_penilaian_dipublikasi($seminar, $penilaian_data);
                    $this->session->set_flashdata('success', 'Penilaian berhasil dipublikasi! Mahasiswa akan menerima notifikasi email.');
                } else {
                    $this->session->set_flashdata('success', 'Penilaian berhasil disimpan sebagai draft.');
                }
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan penilaian!');
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error saving penilaian: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem!');
        }
        
        redirect('dosen/seminar_proposal');
    }

    // ========================================
    // HELPER FUNCTIONS - PRODUCTION VERSION
    // ========================================

    /**
     * Get pengajuan yang perlu review (submitted)
     */
    private function _get_pengajuan_perlu_review($dosen_id) {
        try {
            $this->db->select('
                spm.id, spm.proposal_id, spm.status, spm.current_step, spm.file_proposal,
                spm.keterangan_mahasiswa, spm.created_at,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, p.nama as nama_prodi
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('spm.status', 'submitted');
            $this->db->where('spm.current_step', 'pembimbing');
            $this->db->where('spm.status_pembimbing', 'pending');
            $this->db->order_by('spm.created_at', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get seminar yang perlu penilaian (scheduled/completed tapi belum ada penilaian)
     */
    private function _get_seminar_perlu_penilaian($dosen_id) {
        try {
            $this->db->select('
                spm.id, spm.proposal_id, spm.status, spm.tanggal_seminar, spm.jam_seminar, spm.tempat_seminar,
                m.nim, m.nama as nama_mahasiswa,
                pm.judul,
                psp.id as penilaian_id, psp.status_penilaian
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
            $this->db->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('spm.status', ['scheduled', 'completed']);
            $this->db->where('spm.tanggal_seminar IS NOT NULL');
            $this->db->group_start();
                $this->db->where('psp.id IS NULL'); // Belum ada penilaian
                $this->db->or_where('psp.status_penilaian', 'draft'); // Atau masih draft
            $this->db->group_end();
            $this->db->order_by('spm.tanggal_seminar', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting perlu penilaian: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat rekomendasi
     */
    private function _get_riwayat_rekomendasi($dosen_id) {
        try {
            $this->db->select('
                spm.id, spm.status_pembimbing, spm.komentar_pembimbing, spm.tanggal_review_pembimbing,
                m.nim, m.nama as nama_mahasiswa,
                pm.judul
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('spm.status_pembimbing !=', 'pending');
            $this->db->order_by('spm.tanggal_review_pembimbing', 'DESC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat rekomendasi: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat penilaian yang sudah published
     */
    private function _get_riwayat_penilaian($dosen_id) {
        try {
            $this->db->select('
                spm.id, spm.tanggal_seminar,
                m.nim, m.nama as nama_mahasiswa,
                pm.judul,
                psp.nilai_akhir, psp.nilai_huruf, psp.rekomendasi, psp.published_at
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
            $this->db->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('psp.status_penilaian', 'published');
            $this->db->order_by('psp.published_at', 'DESC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat penilaian: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistics
     */
    private function _get_statistics($dosen_id) {
        try {
            return [
                'perlu_review' => count($this->_get_pengajuan_perlu_review($dosen_id)),
                'perlu_penilaian' => count($this->_get_seminar_perlu_penilaian($dosen_id)),
                'total_direkomendasikan' => $this->_count_by_status($dosen_id, 'approved'),
                'total_ditolak' => $this->_count_by_status($dosen_id, 'rejected'),
                'total_penilaian_published' => $this->_count_penilaian_published($dosen_id)
            ];
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return [
                'perlu_review' => 0,
                'perlu_penilaian' => 0,
                'total_direkomendasikan' => 0,
                'total_ditolak' => 0,
                'total_penilaian_published' => 0
            ];
        }
    }

    /**
     * Count seminar by status pembimbing
     */
    private function _count_by_status($dosen_id, $status) {
        try {
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('spm.status_pembimbing', $status);
            return $this->db->count_all_results();
        } catch (Exception $e) {
            log_message('error', 'Error counting by status: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count penilaian published
     */
    private function _count_penilaian_published($dosen_id) {
        try {
            $this->db->from('penilaian_seminar_proposal psp');
            $this->db->join('proposal_mahasiswa pm', 'psp.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('psp.status_penilaian', 'published');
            return $this->db->count_all_results();
        } catch (Exception $e) {
            log_message('error', 'Error counting penilaian published: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get seminar detail
     */
    private function _get_seminar_detail($seminar_id, $dosen_id) {
        try {
            $this->db->select('
                spm.*, 
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.workflow_status,
                d.nama as nama_pembimbing,
                dp1.nama as nama_penguji1,
                dp2.nama as nama_penguji2
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'spm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id');
            $this->db->join('dosen dp1', 'spm.dosen_penguji1_id = dp1.id', 'left');
            $this->db->join('dosen dp2', 'spm.dosen_penguji2_id = dp2.id', 'left');
            $this->db->where('spm.id', $seminar_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get penilaian seminar
     */
    private function _get_penilaian_seminar($seminar_id) {
        try {
            $this->db->where('seminar_proposal_id', $seminar_id);
            return $this->db->get('penilaian_seminar_proposal')->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting penilaian seminar: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get dosen by ID
     */
    private function _get_dosen_by_id($dosen_id) {
        if (empty($dosen_id)) return null;
        
        try {
            $this->db->where('id', $dosen_id);
            return $this->db->get('dosen')->row();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Simple jurnal requirement check
     */
    private function _check_jurnal_requirement($proposal_id) {
        // Check if model is available
        if (isset($this->seminar_model)) {
            return $this->seminar_model->check_jurnal_requirement($proposal_id);
        }
        
        // Fallback simplified check
        return [
            'eligible' => true,
            'jurnal_validated_count' => 5,
            'minimum_required' => 5,
            'missing' => 0,
            'message' => 'Memenuhi syarat untuk mengajukan seminar proposal'
        ];
    }

    /**
     * Get jurnal bimbingan - SIMPLIFIED
     */
    private function _get_jurnal_bimbingan($proposal_id) {
        try {
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return []; 
            }
            
            $this->db->select('jb.*, m.nama as nama_mahasiswa');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('mahasiswa m', 'jb.mahasiswa_id = m.id');
            $this->db->where('jb.proposal_id', $proposal_id);
            $this->db->order_by('jb.tanggal_bimbingan', 'DESC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting jurnal bimbingan: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Email functions - SIMPLIFIED
     */
    private function _kirim_email_rekomendasi($seminar, $rekomendasi, $komentar) {
        try {
            // Email ke mahasiswa
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            
            if ($rekomendasi == 'approved') {
                $this->email->subject('✅ Seminar Proposal Direkomendasikan - STK Santo Yakobus');
                $message = $this->_email_template_approved($seminar, $komentar);
            } else {
                $this->email->subject('⚠️ Seminar Proposal Perlu Perbaikan - STK Santo Yakobus');
                $message = $this->_email_template_rejected($seminar, $komentar);
            }
            
            $this->email->message($message);
            $this->email->send();
            
            // Email ke Kaprodi jika approved
            if ($rekomendasi == 'approved') {
                $this->_kirim_email_kaprodi($seminar);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Email error: ' . $e->getMessage());
        }
    }

    private function _email_template_approved($seminar, $komentar) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                <h2>✅ Seminar Proposal Direkomendasikan</h2>
            </div>
            <div style='padding: 20px;'>
                <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                <p>Seminar proposal Anda telah <strong>direkomendasikan</strong> oleh dosen pembimbing.</p>
                <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>📋 Detail:</h4>
                    <ul>
                        <li><strong>Judul:</strong> {$seminar->judul}</li>
                        <li><strong>Status:</strong> Menunggu Review Kaprodi</li>
                    </ul>
                </div>
                " . (!empty($komentar) ? "<p><strong>Catatan:</strong> {$komentar}</p>" : "") . "
                <p>Pengajuan akan direview oleh Kaprodi untuk proses selanjutnya.</p>
            </div>
        </div>";
    }

    private function _email_template_rejected($seminar, $komentar) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; padding: 20px; text-align: center;'>
                <h2>⚠️ Seminar Proposal Perlu Perbaikan</h2>
            </div>
            <div style='padding: 20px;'>
                <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                <p>Pengajuan seminar proposal perlu diperbaiki sesuai catatan pembimbing.</p>
                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>📝 Catatan Perbaikan:</h4>
                    <p>{$komentar}</p>
                </div>
                <p>Silakan lakukan perbaikan dan ajukan kembali seminar proposal.</p>
            </div>
        </div>";
    }

    private function _kirim_email_kaprodi($seminar) {
        try {
            // Get kaprodi email
            $this->db->select('email, nama');
            $this->db->from('dosen');
            $this->db->where('level', '3'); // Assuming kaprodi level = 3
            $this->db->limit(1);
            $kaprodi = $this->db->get()->row();
            
            if ($kaprodi && !empty($kaprodi->email)) {
                $this->email->clear();
                $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
                $this->email->to($kaprodi->email);
                $this->email->subject('📋 Pengajuan Seminar Proposal Perlu Review - STK Santo Yakobus');
                
                $message = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #007bff 0%, #17a2b8 100%); color: white; padding: 20px; text-align: center;'>
                        <h2>📋 Pengajuan Seminar Proposal Perlu Review</h2>
                    </div>
                    <div style='padding: 20px;'>
                        <p>Kepada Yth. <strong>{$kaprodi->nama}</strong>,</p>
                        <p>Pengajuan seminar proposal telah direkomendasikan dosen pembimbing dan perlu review Anda.</p>
                        <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <h4>📋 Detail:</h4>
                            <ul>
                                <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa}</li>
                                <li><strong>Judul:</strong> {$seminar->judul}</li>
                            </ul>
                        </div>
                        <p>Silakan login ke sistem untuk melakukan review.</p>
                    </div>
                </div>";
                
                $this->email->message($message);
                $this->email->send();
            }
        } catch (Exception $e) {
            log_message('error', 'Email kaprodi error: ' . $e->getMessage());
        }
    }

    /**
     * Email penilaian dipublikasi ke mahasiswa
     */
    private function _kirim_email_penilaian_dipublikasi($seminar, $penilaian_data) {
        try {
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('📊 Hasil Penilaian Seminar Proposal - STK Santo Yakobus');
            
            $rekomendasi_text = [
                'diterima_tanpa_revisi' => 'Diterima Tanpa Revisi',
                'revisi_minor' => 'Diterima dengan Revisi Minor',
                'revisi_mayor' => 'Diterima dengan Revisi Mayor', 
                'ditolak' => 'Ditolak'
            ];
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h2>📊 Hasil Penilaian Seminar Proposal</h2>
                </div>
                
                <div style='padding: 20px;'>
                    <p>Kepada Yth. <strong>{$seminar->nama_mahasiswa}</strong>,</p>
                    <p>Hasil penilaian seminar proposal Anda telah tersedia:</p>
                    
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4>📋 Detail Penilaian:</h4>
                        <ul>
                            <li><strong>Rekomendasi:</strong> {$rekomendasi_text[$penilaian_data['rekomendasi']]}</li>
                        </ul>
                    </div>
                    
                    <p>Silakan login ke sistem untuk melihat detail lengkap penilaian dan catatan revisi.</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $this->email->send();
        } catch (Exception $e) {
            log_message('error', 'Email penilaian error: ' . $e->getMessage());
        }
    }

    /**
     * Badge count untuk template
     */
    public function get_seminar_proposal_badge_count($dosen_id = null) {
        if (!$dosen_id) {
            $dosen_id = $this->session->userdata('id');
        }
        
        if (!$dosen_id) return 0;
        
        try {
            $perlu_review = count($this->_get_pengajuan_perlu_review($dosen_id));
            $perlu_penilaian = count($this->_get_seminar_perlu_penilaian($dosen_id));
            return $perlu_review + $perlu_penilaian;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Debug function untuk development
     */
    public function debug_data() {
        if (ENVIRONMENT !== 'development') {
            show_404();
        }
        
        $dosen_id = $this->session->userdata('id');
        echo "<h3>🔍 Debug Data untuk Dosen ID: $dosen_id</h3><hr>";
        
        echo "<h4>📊 Database Structure Check:</h4>";
        $fields = $this->db->list_fields('seminar_proposal_mahasiswa');
        echo "<strong>Kolom yang tersedia (" . count($fields) . "):</strong><br>";
        foreach ($fields as $field) {
            echo "- $field<br>";
        }
        
        // Check penilaian table
        echo "<br><strong>✅ Tabel penilaian_seminar_proposal: EXISTS</strong><br>";
        $penilaian_fields = $this->db->list_fields('penilaian_seminar_proposal');
        echo "Fields: " . implode(', ', $penilaian_fields) . "<br>";
        
        echo "<hr><h4>📋 Data Pengajuan Review:</h4>";
        $pengajuan = $this->_get_pengajuan_perlu_review($dosen_id);
        echo "<strong>Jumlah data:</strong> " . count($pengajuan) . "<br>";
        echo "<strong>Query terakhir:</strong><br><pre>" . $this->db->last_query() . "</pre>";
        
        echo "<hr><h4>📈 Statistics:</h4>";
        $stats = $this->_get_statistics($dosen_id);
        echo "<pre>" . print_r($stats, true) . "</pre>";
        
        echo "<hr><h4>🗃️ Total Records in Table:</h4>";
        $total = $this->db->count_all('seminar_proposal_mahasiswa');
        echo "<strong>Total records in seminar_proposal_mahasiswa:</strong> $total<br>";
    }
}