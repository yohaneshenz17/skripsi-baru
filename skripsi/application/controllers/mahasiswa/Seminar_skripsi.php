<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller Mahasiswa - ENHANCED WITH MISSING METHODS
 * 
 * ENHANCED FEATURES:
 * - ✅ Notifikasi ke dosen ketika submit seminar skripsi (EXISTING)
 * - ✅ Resubmission jika dosen tolak pengajuan (NEW)
 * - ✅ Notifikasi ulang ke dosen untuk resubmission (NEW)
 * - ✅ Resubmission jika kaprodi tolak pengajuan (NEW)
 * - ✅ Notifikasi ulang ke dosen untuk resubmission penolakan kaprodi (NEW)
 * - ✅ View hasil penilaian dosen yang sudah published (NEW)
 * - ✅ Workflow status completed → lanjut phase 6 Publikasi (NEW)
 * 
 * File: application/controllers/mahasiswa/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     3.0 (Enhanced with Missing Methods)
 */

class Seminar_skripsi extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // Load core libraries
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form', 'text']);

        // Debug mode untuk development
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            log_message('debug', 'Seminar_skripsi constructor called - Session: ' . json_encode($this->session->userdata()));
        }
        
        // Check authentication - Level 3 = Mahasiswa
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') !== '3') {
            redirect('auth/login');
            return;
        }
        
        // Load additional libraries after auth check
        $this->load->library(['form_validation', 'upload', 'email']);
        $this->load->helper(['file', 'security']);
        
        // Load model
        try {
            $this->load->model('Seminar_skripsi_model', 'seminar_model');
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Failed to load Seminar_skripsi_model: ' . $e->getMessage());
            }
            $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        }
    }

    /**
     * ✅ ENHANCED: Index method dengan workflow detection ke publikasi
     * STABLE BASE + Enhancement untuk detect penilaian published
     */
    public function index()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            // STEP 1: Cek existing seminar
            $existing_seminar = $this->_get_latest_seminar($mahasiswa_id);
            
            if ($existing_seminar) {
                // ✅ ENHANCED: Cek apakah ada penilaian published
                $published_penilaian = $this->_get_published_penilaian($existing_seminar->id, $mahasiswa_id);
                
                // ✅ ENHANCED: Auto update workflow ke publikasi jika completed dengan penilaian
                if ($existing_seminar->status == 'completed' && $published_penilaian) {
                    $this->_auto_update_workflow_to_publikasi($existing_seminar->proposal_id, $published_penilaian);
                }
                
                // Ada seminar existing - tampilkan progress
                $data = [
                    'has_existing_seminar' => true,
                    'current_seminar' => $existing_seminar,
                    'published_penilaian' => $published_penilaian, // ✅ NEW
                    'show_progress' => true,
                    'show_form' => false,
                    'show_eligibility_check' => false,
                    'status_text' => $this->_get_status_text($existing_seminar, $published_penilaian), // ✅ ENHANCED
                    'progress_percentage' => $this->_get_progress_percentage($existing_seminar),
                    'can_resubmit' => $existing_seminar->status == 'rejected', // ✅ NEW
                    'can_view_penilaian' => !empty($published_penilaian), // ✅ NEW
                    'can_proceed_publikasi' => $this->_can_proceed_to_publikasi($published_penilaian) // ✅ NEW
                ];
            } else {
                // STEP 2: Tidak ada seminar - cek eligibility (SIMPLIFIED)
                $eligibility = $this->_check_simplified_eligibility($mahasiswa_id);
                
                if ($eligibility['eligible']) {
                    // ✅ ELIGIBLE - tampilkan form pengajuan
                    $data = [
                        'has_existing_seminar' => false,
                        'show_progress' => false,
                        'show_form' => true,
                        'show_eligibility_check' => false,
                        'can_create_new' => true,
                        'eligible_proposal' => $eligibility['proposal'],
                        'action_url' => base_url('mahasiswa/seminar_skripsi/pengajuan/' . $eligibility['proposal']->id),
                        'requirements' => $eligibility['requirements'],
                        'summary' => $eligibility['summary']
                    ];
                } else {
                    // ❌ NOT ELIGIBLE - tampilkan syarat
                    $data = [
                        'has_existing_seminar' => false,
                        'show_progress' => false,
                        'show_form' => false,
                        'show_eligibility_check' => true,
                        'can_create_new' => false,
                        'eligibility' => $eligibility,
                        'errors' => $eligibility['errors'],
                        'requirements' => $eligibility['requirements']
                    ];
                }
            }
            
        } catch (Exception $e) {
            log_message('error', 'Seminar_skripsi index error: ' . $e->getMessage());
            
            // FALLBACK: Error handling
            $data = [
                'has_existing_seminar' => false,
                'show_progress' => false,
                'show_form' => false,
                'show_eligibility_check' => true,
                'error' => true,
                'error_message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ];
        }
        
        // Load view dengan data yang sudah jelas
        $this->load->view('template/mahasiswa', [
            'title' => 'Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/index', $data, TRUE)
        ]);
    }

    /**
     * Pengajuan Seminar Skripsi - Form submission
     * STABLE - TIDAK DIUBAH
     */
    public function pengajuan($proposal_id = null)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$proposal_id) {
            $this->session->set_flashdata('error', 'ID Proposal tidak valid');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        if ($this->input->method() === 'post') {
            $this->_handle_pengajuan_submit($proposal_id, $mahasiswa_id);
        } else {
            $this->_show_pengajuan_form($proposal_id, $mahasiswa_id);
        }
    }

    /**
     * Detail seminar skripsi
     * STABLE - TIDAK DIUBAH
     */
    public function detail($seminar_id = null)
    {
        if (!$seminar_id) {
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $mahasiswa_id = $this->session->userdata('id');
        $seminar = $this->_get_seminar_detail($seminar_id, $mahasiswa_id);

        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $data = [
            'seminar' => $seminar,
            'progress_data' => $this->_build_simple_progress($seminar)
        ];

        $this->load->view('template/mahasiswa', [
            'title' => 'Detail Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/detail', $data, TRUE)
        ]);
    }

    /**
     * View/Download file skripsi
     * STABLE - TIDAK DIUBAH
     */
    public function view_file($seminar_id = null)
    {
        if (!$seminar_id) {
            show_404();
            return;
        }

        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            // Get seminar data dengan validasi ownership
            $this->db->select('ssm.*, pm.id as proposal_id');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('ssm.id', $seminar_id);
            $this->db->where('ssm.mahasiswa_id', $mahasiswa_id);
            
            $seminar = $this->db->get()->row();
            
            if (!$seminar) {
                $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau Anda tidak memiliki akses');
                redirect('mahasiswa/seminar_skripsi');
                return;
            }
            
            if (empty($seminar->file_skripsi)) {
                $this->session->set_flashdata('error', 'File skripsi tidak tersedia');
                redirect('mahasiswa/seminar_skripsi/detail/' . $seminar_id);
                return;
            }
            
            // Path file skripsi
            $file_path = FCPATH . 'uploads/seminar_skripsi/skripsi_files/' . $seminar->file_skripsi;
            
            if (!file_exists($file_path)) {
                $this->session->set_flashdata('error', 'File tidak ditemukan di server');
                redirect('mahasiswa/seminar_skripsi/detail/' . $seminar_id);
                return;
            }
            
            // Force download dengan nama yang user-friendly
            $this->load->helper('download');
            
            // Get file extension
            $file_ext = pathinfo($seminar->file_skripsi, PATHINFO_EXTENSION);
            $download_name = 'Skripsi_' . $mahasiswa_id . '_' . date('Ymd') . '.' . $file_ext;
            
            force_download($download_name, file_get_contents($file_path));
            
        } catch (Exception $e) {
            log_message('error', 'View file skripsi error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat mengakses file');
            redirect('mahasiswa/seminar_skripsi');
        }
    }

    // =================================================================
    // ✅ NEW ENHANCED METHODS - MISSING METHODS IMPLEMENTATION
    // =================================================================

    /**
     * NEW: Resubmit pengajuan setelah ditolak dosen atau kaprodi
     * URL: mahasiswa/seminar_skripsi/resubmit/SEMINAR_ID
     */
    public function resubmit($seminar_id = null)
    {
        if (!$seminar_id) {
            $this->session->set_flashdata('error', 'ID Seminar tidak valid');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi ownership dan status
        $seminar = $this->_get_seminar_detail($seminar_id, $mahasiswa_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        // Hanya bisa resubmit jika status rejected
        if ($seminar->status !== 'rejected') {
            $this->session->set_flashdata('error', 'Seminar tidak dalam status yang bisa diajukan ulang!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        if ($this->input->method() === 'post') {
            $this->_handle_resubmit_process($seminar_id, $mahasiswa_id, $seminar);
        } else {
            $this->_show_resubmit_form($seminar_id, $mahasiswa_id, $seminar);
        }
    }

    /**
     * NEW: View hasil penilaian yang sudah published
     * URL: mahasiswa/seminar_skripsi/view_penilaian/SEMINAR_ID
     */
    public function view_penilaian($seminar_id = null)
    {
        if (!$seminar_id) {
            $this->session->set_flashdata('error', 'ID Seminar tidak valid');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        $mahasiswa_id = $this->session->userdata('id');
        
        // Get penilaian yang published untuk mahasiswa ini
        $penilaian = $this->_get_published_penilaian($seminar_id, $mahasiswa_id);
        
        if (!$penilaian) {
            $this->session->set_flashdata('error', 'Penilaian belum tersedia atau tidak ditemukan!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }

        // Get detail seminar untuk context
        $seminar = $this->_get_seminar_detail($seminar_id, $mahasiswa_id);
        
        $data = [
            'seminar' => $seminar,
            'penilaian' => $penilaian,
            'page_title' => 'Hasil Penilaian Seminar Skripsi',
            'can_proceed_to_publikasi' => $this->_can_proceed_to_publikasi($penilaian),
            'next_phase_info' => $this->_get_next_phase_info($penilaian)
        ];

        $this->load->view('template/mahasiswa', [
            'title' => 'Hasil Penilaian Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/view_penilaian', $data, TRUE)
        ]);
    }

    /**
     * NEW: Redirect ke phase publikasi jika memenuhi syarat
     * URL: mahasiswa/seminar_skripsi/proceed_to_publikasi
     */
    public function proceed_to_publikasi()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Cek apakah mahasiswa memenuhi syarat untuk publikasi
        $latest_seminar = $this->_get_latest_seminar($mahasiswa_id);
        
        if (!$latest_seminar || $latest_seminar->status !== 'completed') {
            $this->session->set_flashdata('error', 'Seminar skripsi belum selesai!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        $penilaian = $this->_get_published_penilaian($latest_seminar->id, $mahasiswa_id);
        
        if (!$this->_can_proceed_to_publikasi($penilaian)) {
            $this->session->set_flashdata('error', 'Belum memenuhi syarat untuk publikasi!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        // Update workflow status ke publikasi
        $this->_auto_update_workflow_to_publikasi($latest_seminar->proposal_id, $penilaian);
        
        $this->session->set_flashdata('success', 'Selamat! Anda telah lulus seminar skripsi dan dapat melanjutkan ke tahap publikasi tugas akhir.');
        redirect('mahasiswa/publikasi');
    }

    // =================================================================
    // ✅ STABLE PRIVATE METHODS - TIDAK DIUBAH
    // =================================================================

    /**
     * Get latest seminar by mahasiswa (simple & reliable)
     * STABLE - TIDAK DIUBAH
     */
    private function _get_latest_seminar($mahasiswa_id)
    {
        try {
            $this->db->select('ssm.*, pm.judul, pm.workflow_status');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('ssm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('ssm.created_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Get latest seminar error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Simplified eligibility check - hanya 2 syarat
     * STABLE - TIDAK DIUBAH
     */
    private function _check_simplified_eligibility($mahasiswa_id)
    {
        try {
            // Get proposal mahasiswa (fase penelitian atau seminar_skripsi)
            $proposal = $this->db->select('id, judul, workflow_status')
                                ->from('proposal_mahasiswa')
                                ->where('mahasiswa_id', $mahasiswa_id)
                                ->where_in('workflow_status', ['penelitian', 'seminar_skripsi'])
                                ->get()->row();
            
            if (!$proposal) {
                return [
                    'eligible' => false,
                    'errors' => ['Tidak ada proposal dalam fase penelitian'],
                    'requirements' => [],
                    'summary' => 'Belum ada proposal yang memenuhi syarat'
                ];
            }
            
            $requirements = [];
            $errors = [];
            
            // SYARAT 1: 14 jurnal bimbingan tervalidasi
            $jurnal_count = $this->db->where('proposal_id', $proposal->id)
                                    ->where('status_validasi', '1')
                                    ->count_all_results('jurnal_bimbingan');
            
            $requirements['jurnal'] = [
                'name' => 'Jurnal Bimbingan Tervalidasi',
                'current' => $jurnal_count,
                'required' => 14,
                'met' => $jurnal_count >= 14
            ];
            
            if ($jurnal_count < 14) {
                $errors[] = "Perlu " . (14 - $jurnal_count) . " jurnal bimbingan lagi";
            }
            
            // SYARAT 2: Sudah mengajukan surat izin penelitian
            $penelitian_count = 0;
            
            // Cek di tabel permohonan_izin_penelitian
            if ($this->db->table_exists('permohonan_izin_penelitian')) {
                $penelitian_count = $this->db->where('proposal_mahasiswa_id', $proposal->id)
                                            ->count_all_results('permohonan_izin_penelitian');
            }
            
            // Fallback: cek di tabel penelitian
            if ($penelitian_count == 0 && $this->db->table_exists('penelitian')) {
                $penelitian_count = $this->db->where('proposal_mahasiswa_id', $proposal->id)
                                            ->count_all_results('penelitian');
            }
            
            $requirements['penelitian'] = [
                'name' => 'Surat Izin Penelitian',
                'current' => $penelitian_count,
                'required' => 1,
                'met' => $penelitian_count >= 1
            ];
            
            if ($penelitian_count < 1) {
                $errors[] = "Belum mengajukan surat izin penelitian";
            }
            
            return [
                'eligible' => empty($errors),
                'requirements' => $requirements,
                'errors' => $errors,
                'proposal' => $proposal,
                'summary' => empty($errors) ? 
                    'Memenuhi syarat untuk mengajukan seminar skripsi' : 
                    'Belum memenuhi syarat: ' . implode(', ', $errors)
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Eligibility check error: ' . $e->getMessage());
            return [
                'eligible' => false,
                'errors' => ['Terjadi kesalahan sistem'],
                'requirements' => [],
                'summary' => 'Error sistem'
            ];
        }
    }

    /**
     * ✅ ENHANCED: Get status text with penilaian context
     * ENHANCED VERSION dengan parameter tambahan
     */
    private function _get_status_text($seminar, $published_penilaian = null)
    {
        switch ($seminar->status) {
            case 'submitted':
                if ($seminar->status_pembimbing == 'pending') {
                    return 'Menunggu Review Dosen Pembimbing';
                } elseif ($seminar->status_pembimbing == 'approved' && $seminar->status_kaprodi == 'pending') {
                    return 'Menunggu Validasi Kaprodi';
                } else {
                    return 'Sedang Diproses';
                }
            case 'review_pembimbing':
                return 'Sedang Direview Dosen Pembimbing';
            case 'review_kaprodi':
                return 'Menunggu Validasi Kaprodi';
            case 'approved':
                return 'Disetujui - Menunggu Penjadwalan';
            case 'scheduled':
                return 'Seminar Telah Dijadwalkan';
            case 'completed':
                if ($published_penilaian) {
                    return 'Seminar Selesai - Penilaian Tersedia';
                } else {
                    return 'Seminar Selesai - Menunggu Penilaian';
                }
            case 'rejected':
                return 'Pengajuan Ditolak - Perlu Perbaikan';
            default:
                return 'Status: ' . ucfirst($seminar->status);
        }
    }

    /**
     * Get progress percentage
     * STABLE - TIDAK DIUBAH
     */
    private function _get_progress_percentage($seminar)
    {
        switch ($seminar->status) {
            case 'submitted':
            case 'review_pembimbing':
                return 20;
            case 'review_kaprodi':
                return 40;
            case 'approved':
                return 60;
            case 'scheduled':
                return 80;
            case 'completed':
                return 100;
            case 'rejected':
                return 10;
            default:
                return 5;
        }
    }

    /**
     * Build simple progress steps
     * STABLE - TIDAK DIUBAH
     */
    private function _build_simple_progress($seminar)
    {
        $steps = [
            ['title' => 'Pengajuan Dikirim', 'status' => 'completed'],
            ['title' => 'Review Pembimbing', 'status' => $seminar->status_pembimbing == 'approved' ? 'completed' : 'active'],
            ['title' => 'Validasi Kaprodi', 'status' => $seminar->status_kaprodi == 'approved' ? 'completed' : 'pending'],
            ['title' => 'Penjadwalan', 'status' => $seminar->status == 'scheduled' ? 'completed' : 'pending'],
            ['title' => 'Pelaksanaan', 'status' => $seminar->status == 'completed' ? 'completed' : 'pending']
        ];
        
        return $steps;
    }

    // =================================================================
    // ✅ NEW PRIVATE METHODS - MISSING FUNCTIONALITY
    // =================================================================

    /**
     * NEW: Get published penilaian untuk mahasiswa
     */
    private function _get_published_penilaian($seminar_id, $mahasiswa_id)
    {
        try {
            $this->db->select('pss.*, ssm.proposal_id');
            $this->db->from('penilaian_seminar_skripsi pss');
            $this->db->join('seminar_skripsi_mahasiswa ssm', 'pss.seminar_skripsi_id = ssm.id');
            $this->db->where('pss.seminar_skripsi_id', $seminar_id);
            $this->db->where('ssm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pss.status_penilaian', 'published');
            $this->db->order_by('pss.published_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting published penilaian: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * NEW: Check apakah bisa proceed ke publikasi
     */
    private function _can_proceed_to_publikasi($penilaian)
    {
        if (!$penilaian) {
            return false;
        }
        
        // Bisa proceed jika rekomendasi bukan 'tidak_lulus'
        return in_array($penilaian->rekomendasi, [
            'lulus_tanpa_revisi',
            'lulus_dengan_revisi_minor', 
            'lulus_dengan_revisi_mayor'
        ]);
    }

    /**
     * NEW: Auto update workflow status ke publikasi
     */
    private function _auto_update_workflow_to_publikasi($proposal_id, $penilaian)
    {
        if (!$this->_can_proceed_to_publikasi($penilaian)) {
            return false;
        }
        
        try {
            // Cek current workflow status
            $proposal = $this->db->select('workflow_status')
                                ->from('proposal_mahasiswa')
                                ->where('id', $proposal_id)
                                ->get()->row();
            
            // Update ke publikasi jika belum
            if ($proposal && $proposal->workflow_status !== 'publikasi') {
                $this->db->where('id', $proposal_id);
                $this->db->update('proposal_mahasiswa', [
                    'workflow_status' => 'publikasi',
                    'status_seminar_skripsi' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                log_message('info', "Auto updated workflow to publikasi for proposal_id: {$proposal_id}");
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            log_message('error', 'Error auto updating workflow: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NEW: Show resubmit form
     */
    private function _show_resubmit_form($seminar_id, $mahasiswa_id, $seminar)
    {
        // Get rejection reason
        $rejection_reason = '';
        if ($seminar->status_pembimbing == 'rejected') {
            $rejection_reason = $seminar->komentar_pembimbing;
        } elseif ($seminar->status_kaprodi == 'rejected') {
            $rejection_reason = $seminar->komentar_kaprodi;
        }
        
        $data = [
            'seminar' => $seminar,
            'rejection_reason' => $rejection_reason,
            'form_action' => base_url('mahasiswa/seminar_skripsi/resubmit/' . $seminar_id),
            'page_title' => 'Ajukan Ulang Seminar Skripsi'
        ];
        
        $this->load->view('template/mahasiswa', [
            'title' => 'Ajukan Ulang Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/resubmit', $data, TRUE)
        ]);
    }

    /**
     * NEW: Handle resubmit process
     */
    private function _handle_resubmit_process($seminar_id, $mahasiswa_id, $seminar)
    {
        // Set validation rules
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan Perbaikan', 'required|max_length[1000]');
        
        if (!$this->form_validation->run()) {
            $this->_show_resubmit_form($seminar_id, $mahasiswa_id, $seminar);
            return;
        }
        
        try {
            // Handle file upload jika ada file baru
            $new_filename = null;
            if (!empty($_FILES['file_skripsi']['name'])) {
                $upload_result = $this->_handle_file_upload();
                
                if (!$upload_result['success']) {
                    $this->session->set_flashdata('error', $upload_result['message']);
                    redirect('mahasiswa/seminar_skripsi/resubmit/' . $seminar_id);
                    return;
                }
                
                $new_filename = $upload_result['filename'];
            }
            
            // Update data untuk resubmission
            $update_data = [
                'status' => 'submitted',
                'current_step' => 'pembimbing',
                'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
                'status_pembimbing' => 'pending',
                'status_kaprodi' => 'pending',
                'komentar_pembimbing' => null,
                'komentar_kaprodi' => null,
                'tanggal_review_pembimbing' => null,
                'tanggal_review_kaprodi' => null,
                'reviewed_by_pembimbing' => null,
                'reviewed_by_kaprodi' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Update filename jika ada file baru
            if ($new_filename) {
                $update_data['file_skripsi'] = $new_filename;
                
                // Hapus file lama jika ada
                if (!empty($seminar->file_skripsi)) {
                    $old_file = FCPATH . 'uploads/seminar_skripsi/skripsi_files/' . $seminar->file_skripsi;
                    if (file_exists($old_file)) {
                        @unlink($old_file);
                    }
                }
            }
            
            $this->db->trans_start();
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan pengajuan ulang');
            }
            
            // Send notification untuk resubmission
            $this->_send_resubmission_notification($seminar, $this->input->post('keterangan_mahasiswa'));
            
            $this->session->set_flashdata('success', 'Pengajuan ulang seminar skripsi berhasil dikirim! Dosen pembimbing akan mendapat notifikasi.');
            
        } catch (Exception $e) {
            log_message('error', 'Error resubmitting seminar skripsi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }

    /**
     * NEW: Send resubmission notification
     */
    private function _send_resubmission_notification($seminar, $keterangan_perbaikan)
    {
        try {
            // Get proposal dan dosen data
            $proposal_data = $this->db->select('
                pm.*, 
                m.nama as nama_mahasiswa, 
                m.nim, 
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing, 
                d.email as email_pembimbing
            ')
            ->from('proposal_mahasiswa pm')
            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
            ->join('dosen d', 'pm.dosen_id = d.id', 'left')
            ->where('pm.id', $seminar->proposal_id)
            ->get()->row();
            
            if (!$proposal_data || !$proposal_data->email_pembimbing) {
                log_message('warning', 'No proposal data or pembimbing email for resubmission notification');
                return false;
            }
            
            // Email config
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            // Send to dosen pembimbing
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($proposal_data->email_pembimbing);
            $this->email->subject('🔄 Pengajuan Ulang Seminar Skripsi - ' . $proposal_data->nama_mahasiswa);
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #17a2b8 0%, #28a745 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>🔄 Pengajuan Ulang Seminar Skripsi</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$proposal_data->nama_pembimbing}</strong>,</p>
                    
                    <p>Mahasiswa berikut telah mengajukan ulang seminar skripsi setelah melakukan perbaikan:</p>
                    
                    <div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #17a2b8;'>
                        <h4 style='color: #0c5460; margin: 0 0 10px 0;'>👨‍🎓 Detail Mahasiswa:</h4>
                        <ul style='color: #0c5460; margin: 0;'>
                            <li><strong>Nama:</strong> {$proposal_data->nama_mahasiswa}</li>
                            <li><strong>NIM:</strong> {$proposal_data->nim}</li>
                            <li><strong>Judul:</strong> {$proposal_data->judul}</li>
                            <li><strong>Tanggal Pengajuan Ulang:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>📝 Keterangan Perbaikan:</h4>
                        <p style='color: #856404; margin: 0; background-color: white; padding: 10px; border-radius: 3px;'>" . nl2br(htmlspecialchars($keterangan_perbaikan)) . "</p>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('dosen/seminar_skripsi') . "' style='background-color: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            🔍 Review Pengajuan Ulang
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Email ini dikirim otomatis oleh sistem. Silakan login ke sistem untuk melakukan review pengajuan ulang.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $email_sent = $this->email->send();
            
            if ($email_sent) {
                log_message('info', "Resubmission notification sent successfully - Seminar ID: {$seminar->id}");
                return true;
            } else {
                log_message('error', "Failed to send resubmission notification - Seminar ID: {$seminar->id}");
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Resubmission notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NEW: Get next phase info berdasarkan penilaian
     */
    private function _get_next_phase_info($penilaian)
    {
        if (!$penilaian) {
            return null;
        }
        
        switch ($penilaian->rekomendasi) {
            case 'lulus_tanpa_revisi':
                return [
                    'status' => 'success',
                    'message' => 'Selamat! Anda lulus tanpa revisi dan dapat melanjutkan ke tahap publikasi.',
                    'next_action' => 'Lanjut ke Publikasi',
                    'next_url' => base_url('mahasiswa/publikasi')
                ];
                
            case 'lulus_dengan_revisi_minor':
                return [
                    'status' => 'warning',
                    'message' => 'Anda lulus dengan revisi minor. Lakukan revisi sesuai catatan dosen sebelum publikasi.',
                    'next_action' => 'Lanjut ke Publikasi',
                    'next_url' => base_url('mahasiswa/publikasi')
                ];
                
            case 'lulus_dengan_revisi_mayor':
                return [
                    'status' => 'warning',
                    'message' => 'Anda lulus dengan revisi mayor. Lakukan revisi besar sesuai catatan dosen sebelum publikasi.',
                    'next_action' => 'Lanjut ke Publikasi',
                    'next_url' => base_url('mahasiswa/publikasi')
                ];
                
            case 'tidak_lulus':
                return [
                    'status' => 'danger',
                    'message' => 'Mohon maaf, Anda belum lulus seminar skripsi. Perlu mengulang seminar.',
                    'next_action' => 'Ajukan Ulang Seminar',
                    'next_url' => base_url('mahasiswa/seminar_skripsi')
                ];
                
            default:
                return null;
        }
    }

    // =================================================================
    // FORM HANDLING METHODS - STABLE
    // =================================================================

    /**
     * Show pengajuan form
     * STABLE - TIDAK DIUBAH
     */
    private function _show_pengajuan_form($proposal_id, $mahasiswa_id)
    {
        // Validasi proposal ownership
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
    
        // Check eligibility
        $eligibility = $this->_check_simplified_eligibility($mahasiswa_id);
        if (!$eligibility['eligible']) {
            $this->session->set_flashdata('error', 'Belum memenuhi syarat: ' . implode(', ', $eligibility['errors']));
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
    
        // Cek existing seminar untuk edit mode
        $existing_seminar = null;
        $this->db->where('proposal_id', $proposal_id);
        $existing_seminar = $this->db->get('seminar_skripsi_mahasiswa')->row();
        
        $is_edit = !empty($existing_seminar);
        
        // Tentukan judul current
        $current_judul = $proposal->judul;
        if ($existing_seminar && !empty($existing_seminar->judul_skripsi)) {
            $current_judul = $existing_seminar->judul_skripsi;
        }
    
        $data = [
            'proposal' => $proposal,
            'eligibility' => $eligibility,
            'form_action' => base_url('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id),
            'is_edit' => $is_edit,
            'form_title' => $is_edit ? 'Edit Pengajuan Seminar Skripsi' : 'Form Pengajuan Seminar Skripsi',
            'existing_seminar' => $existing_seminar,
            'current_judul' => $current_judul,
            'judul_original' => $proposal->judul,
            'requirements' => ['requirements' => [], 'all_met' => true]
        ];
    
        $this->load->view('template/mahasiswa', [
            'title' => 'Ajukan Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/pengajuan', $data, TRUE),
            'script' => $this->_get_form_script()
        ]);
    }

    /**
     * Handle pengajuan submission
     * STABLE - TIDAK DIUBAH
     */
    private function _handle_pengajuan_submit($proposal_id, $mahasiswa_id)
    {
        // Set validation rules
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan Tambahan', 'required|max_length[500]');

        if (!$this->form_validation->run()) {
            $this->_show_pengajuan_form($proposal_id, $mahasiswa_id);
            return;
        }

        try {
            // Handle file upload
            $upload_result = $this->_handle_file_upload();
            
            if (!$upload_result['success']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('mahasiswa/seminar_skripsi/pengajuan/' . $proposal_id);
                return;
            }

            // Prepare data for insert
            $data = [
                'proposal_id' => $proposal_id,
                'mahasiswa_id' => $mahasiswa_id,
                'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
                'status' => 'submitted',
                'current_step' => 'pembimbing',
                'file_skripsi' => $upload_result['filename'],
                'status_pembimbing' => 'pending',
                'status_kaprodi' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert to database
            $this->db->trans_start();
            
            $this->db->insert('seminar_skripsi_mahasiswa', $data);
            $insert_id = $this->db->insert_id();
            
            if (!$insert_id) {
                throw new Exception('Gagal menyimpan pengajuan');
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction gagal');
            }
            
            // Send notification
            $this->_send_notification_success($proposal_id, $insert_id);
            
            $this->session->set_flashdata('success', 'Pengajuan seminar skripsi berhasil dikirim! Dosen pembimbing akan mendapat notifikasi.');
            
        } catch (Exception $e) {
            log_message('error', 'Error saving seminar skripsi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }

    // =================================================================
    // UTILITY METHODS - STABLE & ENHANCED
    // =================================================================

    /**
     * Handle file upload
     * STABLE - TIDAK DIUBAH
     */
    private function _handle_file_upload()
    {
        $upload_path = FCPATH . 'uploads/seminar_skripsi/skripsi_files/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
    
        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 5120, // 5MB
            'encrypt_name' => TRUE
        ];
    
        $this->upload->initialize($config);
    
        if (!$this->upload->do_upload('file_skripsi')) {
            return [
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        }
    
        $upload_data = $this->upload->data();
        return [
            'success' => true,
            'filename' => $upload_data['file_name'],
            'original_name' => $upload_data['orig_name']
        ];
    }

    /**
     * Send notification after successful submission
     * STABLE - TIDAK DIUBAH (sudah ada implementasi lengkap)
     */
    private function _send_notification_success($proposal_id, $seminar_id)
    {
        try {
            // Get proposal data with joins
            $proposal_data = $this->db->select('
                pm.*, 
                m.nama as nama_mahasiswa, 
                m.nim, 
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing, 
                d.email as email_pembimbing
            ')
            ->from('proposal_mahasiswa pm')
            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
            ->join('dosen d', 'pm.dosen_id = d.id', 'left')
            ->where('pm.id', $proposal_id)
            ->get()->row();
            
            if (!$proposal_data || !$proposal_data->email_pembimbing) {
                log_message('warning', 'No proposal data or pembimbing email for notification');
                return false;
            }
            
            // Email config
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            // Send to dosen pembimbing
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($proposal_data->email_pembimbing);
            $this->email->subject('📋 Pengajuan Seminar Skripsi - ' . $proposal_data->nama_mahasiswa);
            
            $message = "
            <h3>Pengajuan Seminar Skripsi</h3>
            <p>Mahasiswa berikut telah mengajukan seminar skripsi:</p>
            <ul>
                <li><strong>Nama:</strong> {$proposal_data->nama_mahasiswa}</li>
                <li><strong>NIM:</strong> {$proposal_data->nim}</li>
                <li><strong>Judul:</strong> {$proposal_data->judul}</li>
            </ul>
            <p>Silakan login ke sistem untuk melakukan review pengajuan.</p>
            <p><a href='".base_url('dosen/seminar_skripsi')."' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Review Pengajuan</a></p>
            ";
            
            $this->email->message($message);
            $email_sent = $this->email->send();
            
            if ($email_sent) {
                log_message('info', "Seminar skripsi notification sent successfully - ID: {$seminar_id}");
                return true;
            } else {
                log_message('error', "Failed to send seminar skripsi notification - ID: {$seminar_id}");
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NEW: Get email configuration
     */
    private function _get_email_config()
    {
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
            'smtp_timeout' => 30
        ];
    }

    /**
     * Get proposal by ID with ownership validation
     * STABLE - TIDAK DIUBAH
     */
    private function _get_proposal_by_id($proposal_id, $mahasiswa_id)
    {
        return $this->db->select('pm.*, m.nim, m.nama as nama_mahasiswa')
                       ->from('proposal_mahasiswa pm')
                       ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                       ->where('pm.id', $proposal_id)
                       ->where('pm.mahasiswa_id', $mahasiswa_id)
                       ->get()->row();
    }

    /**
     * Get seminar detail dengan field yang benar
     * STABLE - TIDAK DIUBAH
     */
    private function _get_seminar_detail($seminar_id, $mahasiswa_id)
    {
        try {
            $this->db->select('
                ssm.*,
                COALESCE(ssm.judul_skripsi, pm.judul) as judul_skripsi,
                pm.judul as proposal_judul,
                pm.workflow_status,
                m.nim, 
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                pr.nama as nama_prodi
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
            $this->db->where('ssm.id', $seminar_id);
            $this->db->where('ssm.mahasiswa_id', $mahasiswa_id);
            
            $result = $this->db->get()->row();
            
            if ($result) {
                if (!isset($result->status_pembimbing)) $result->status_pembimbing = 'pending';
                if (!isset($result->status_kaprodi)) $result->status_kaprodi = 'pending';
                if (!isset($result->file_skripsi)) $result->file_skripsi = '';
                
                if (empty($result->judul_skripsi)) {
                    $result->judul_skripsi = $result->proposal_judul ?? 'Judul tidak tersedia';
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Get seminar detail error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get index page script
     * STABLE - TIDAK DIUBAH
     */
    private function _get_index_script()
    {
        return '
        <script>
        $(document).ready(function() {
            // Auto refresh progress setiap 30 detik jika ada seminar aktif
            if ($(".progress-bar").length > 0) {
                setInterval(function() {
                    location.reload();
                }, 30000);
            }
        });
        </script>
        ';
    }

    /**
     * Get form page script
     * STABLE - TIDAK DIUBAH
     */
    private function _get_form_script()
    {
        return '
        <script>
        $(document).ready(function() {
            $("#file_skripsi").change(function() {
                var fileSize = this.files[0].size;
                if (fileSize > 2097152) { // 2MB
                    alert("File terlalu besar. Maksimal 2MB.");
                    $(this).val("");
                }
            });
        });
        </script>
        ';
    }
}

/* End of file Seminar_skripsi.php */
/* Location: ./application/controllers/mahasiswa/Seminar_skripsi.php */