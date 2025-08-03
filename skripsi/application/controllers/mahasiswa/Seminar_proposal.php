<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Controller - Role Mahasiswa (COMPREHENSIVE FIXED VERSION)
 * 
 * 🔧 MAJOR FIXES:
 * - Fixed proposal retrieval for completed seminar proposals
 * - Enhanced workflow status handling for all seminar states
 * - Proper dashboard display for completed proposals with assessment
 * - Prevents re-submission after completion
 * - Enhanced progress tracking and history display
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     4.0 (Comprehensive Fixed)
 */

class Seminar_proposal extends CI_Controller {

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
            log_message('debug', 'Seminar_proposal constructor called - Session: ' . json_encode($this->session->userdata()));
        }
        
        // Check authentication
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') !== '3') {
            redirect('auth/login');
            return;
        }
        
        $user_level = $this->session->userdata('level');
        if ($user_level !== '3') {  // '3' untuk mahasiswa
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Seminar_proposal: Invalid user level: ' . $user_level);
            }
            $this->session->set_flashdata('error', 'Akses ditolak. Anda bukan mahasiswa.');
            redirect('auth/login');
            return;
        }
        
        // Load additional libraries after auth check
        $this->load->library(['form_validation', 'upload', 'email']);
        $this->load->helper(['file', 'security']);
        
        // Try to load model, with fallback
        try {
            $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Failed to load Seminar_proposal_mahasiswa_model: ' . $e->getMessage());
            }
            // Create simple fallback if model doesn't exist
            $this->seminar_model = $this->_create_fallback_model();
        }
    }

    // =================================================================
    // MAIN PAGES
    // =================================================================

    /**
     * 🔧 COMPREHENSIVE FIX: Dashboard Seminar Proposal dengan Logic yang Diperbaiki Total
     * URL: https://stkyakobus.ac.id/skripsi/mahasiswa/seminar_proposal
     */
    public function index()
    {
        try {
            $mahasiswa_id = $this->session->userdata('id');
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Seminar_proposal index - Mahasiswa ID: ' . $mahasiswa_id);
            }
            
            // ✅ ENHANCEMENT: Cek submission success state dari redirect
            $submission_success = $this->session->flashdata('submission_success');
            $is_from_submission = $this->input->get('submission') === 'success';
            
            // 🔧 MAJOR FIX: Comprehensive proposal retrieval untuk semua state
            $proposal_data = $this->_get_comprehensive_proposal_data($mahasiswa_id);
            
            if (!$proposal_data['proposal']) {
                // CASE 1: Belum ada proposal yang memenuhi syarat
                $data = [
                    'title' => 'Seminar Proposal',
                    'content' => 'mahasiswa/seminar_proposal/no_proposal',
                    'proposal' => null,
                    'seminar_data' => null,
                    'current_step' => 'no_approved_proposal'
                ];
                
                $this->_load_view($data);
                return;
            }

            $proposal = $proposal_data['proposal'];
            $seminar_data = $proposal_data['seminar_data'];
            
            // CASE 2: Ada proposal - handle semua kemungkinan state
            $syarat_jurnal = $this->_check_jurnal_requirement($proposal->id);
            
            // ✅ COMPREHENSIVE FIX: Enhanced workflow status determination
            $workflow_status = $this->_determine_comprehensive_workflow_status($proposal, $seminar_data, $syarat_jurnal);
            
            // ✅ COMPREHENSIVE FIX: Calculate comprehensive progress
            $workflow_progress = $this->_calculate_comprehensive_workflow_progress($proposal, $seminar_data);
            
            // 🔧 MAJOR FIX: Get penilaian jika sudah ada
            $penilaian_data = null;
            if ($seminar_data && $seminar_data->status === 'completed') {
                $penilaian_data = $this->_get_published_penilaian($seminar_data->id);
            }
            
            // 🔧 DEBUG: Log untuk troubleshooting
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Comprehensive proposal found: ' . json_encode([
                    'id' => $proposal->id,
                    'workflow_status' => $proposal->workflow_status,
                    'status_kaprodi' => $proposal->status_kaprodi,
                    'status_pembimbing' => $proposal->status_pembimbing
                ]));
                log_message('debug', 'Seminar data: ' . ($seminar_data ? 'FOUND (ID: ' . $seminar_data->id . ', Status: ' . $seminar_data->status . ')' : 'NOT FOUND'));
                log_message('debug', 'Penilaian data: ' . ($penilaian_data ? 'FOUND' : 'NOT FOUND'));
                log_message('debug', 'Syarat jurnal: ' . json_encode($syarat_jurnal));
            }
            
            $data = [
                'title' => 'Seminar Proposal',
                'content' => 'mahasiswa/seminar_proposal/dashboard',
                'proposal' => $proposal,
                'seminar_data' => $seminar_data,
                'penilaian_data' => $penilaian_data,
                'syarat_jurnal' => $syarat_jurnal,
                'workflow_status' => $workflow_status,
                'workflow_progress' => $workflow_progress,
                'progress_percentage' => $workflow_progress['percentage'],
                'submission_success' => $submission_success,
                'is_from_submission' => $is_from_submission,
                'can_submit' => $this->_can_submit_seminar_comprehensive($proposal, $seminar_data, $syarat_jurnal),
                'show_penilaian_link' => ($penilaian_data !== null),
                'is_completed' => ($seminar_data && $seminar_data->status === 'completed')
            ];
            
            $this->_load_view($data);
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Seminar_proposal index error: ' . $e->getMessage());
                show_error('Error: ' . $e->getMessage());
            } else {
                $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
                redirect('mahasiswa/dashboard');
            }
        }
    }

    // =================================================================
    // 🔧 COMPREHENSIVE FIXED HELPER METHODS
    // =================================================================

    /**
     * 🔧 FIXED: Method _get_comprehensive_proposal_data()
     * Mengatasi masalah workflow_status yang sudah lanjut
     */
    private function _get_comprehensive_proposal_data($mahasiswa_id)
    {
        try {
            // STEP 1: Query proposal yang eligible
            $this->db->select('pm.*, d.nama as nama_pembimbing, d.email as email_pembimbing');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status_kaprodi', '1');
            $this->db->order_by('pm.id', 'DESC');
            $proposals = $this->db->get()->result();
            
            if (empty($proposals)) {
                if (ENVIRONMENT === 'development') {
                    log_message('debug', 'No proposals found for mahasiswa: ' . $mahasiswa_id);
                }
                return ['proposal' => null, 'seminar_data' => null];
            }
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Found ' . count($proposals) . ' eligible proposals');
            }
            
            // 🔧 PERBAIKAN: STEP 2 - Cari proposal dengan seminar data (TANPA filter workflow)
            foreach ($proposals as $proposal) {
                if (ENVIRONMENT === 'development') {
                    log_message('debug', 'Checking proposal ' . $proposal->id . ' with workflow: ' . $proposal->workflow_status);
                }
                
                $seminar_data = $this->_get_seminar_with_details($proposal->id);
                
                if ($seminar_data) {
                    if (ENVIRONMENT === 'development') {
                        log_message('debug', 'FOUND seminar for proposal ' . $proposal->id . ' with status: ' . $seminar_data->status);
                    }
                    
                    // ✅ LANGSUNG RETURN tanpa filter workflow status
                    return ['proposal' => $proposal, 'seminar_data' => $seminar_data];
                }
            }
            
            // STEP 3: Jika tidak ada seminar, ambil proposal yang bisa mengajukan
            foreach ($proposals as $proposal) {
                // ✅ PERBAIKAN: Workflow status yang eligible untuk mengajukan
                if (in_array($proposal->workflow_status, ['bimbingan', 'seminar_proposal'])) {
                    if (ENVIRONMENT === 'development') {
                        log_message('debug', 'Found eligible proposal without seminar: ' . $proposal->id);
                    }
                    return ['proposal' => $proposal, 'seminar_data' => null];
                }
            }
            
            // STEP 4: Fallback - ambil proposal pertama yang ada
            if (!empty($proposals)) {
                $proposal = $proposals[0];
                $seminar_data = $this->_get_seminar_with_details($proposal->id);
                
                if (ENVIRONMENT === 'development') {
                    log_message('debug', 'Using fallback proposal: ' . $proposal->id . ' (workflow: ' . $proposal->workflow_status . ')');
                }
                
                return ['proposal' => $proposal, 'seminar_data' => $seminar_data];
            }
            
            return ['proposal' => null, 'seminar_data' => null];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting comprehensive proposal data: ' . $e->getMessage());
            return ['proposal' => null, 'seminar_data' => null];
        }
    }

    /**
     * 🔧 ENHANCED: Get seminar data dengan detail lengkap
     */
    private function _get_seminar_with_details($proposal_id)
    {
        try {
            $this->db->select('
                spm.*,
                pm.judul as proposal_judul_original,
                COALESCE(spm.judul_seminar, pm.judul) as proposal_judul,
                spm.judul_seminar,
                pm.dosen_id,
                m.nama as nama_mahasiswa,
                m.nim,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                pr.nama as nama_prodi,
                d_pembimbing.nama as nama_pembimbing,
                d_pembimbing.email as email_pembimbing,
                d1.nama as nama_penguji1,
                d2.nama as nama_penguji2
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
            $this->db->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left');
            $this->db->join('dosen d1', 'spm.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'spm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('spm.proposal_id', $proposal_id);
            $this->db->order_by('spm.created_at', 'DESC');
            $this->db->limit(1);
            
            $result = $this->db->get()->row();
            
            if (ENVIRONMENT === 'development') {
                log_message('debug', 'Seminar query: ' . $this->db->last_query());
                log_message('debug', 'Seminar result: ' . ($result ? 'FOUND (Status: ' . $result->status . ')' : 'NOT FOUND'));
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar with details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 🔧 NEW: Get published penilaian untuk seminar yang sudah completed
     */
    private function _get_published_penilaian($seminar_id)
    {
        try {
            if (!$this->db->table_exists('penilaian_seminar_proposal')) {
                return null;
            }
            
            $this->db->select('
                psp.*,
                d1.nama as nama_penguji1, d1.nip as nip_penguji1,
                d2.nama as nama_penguji2, d2.nip as nip_penguji2,
                dp.nama as nama_pembimbing, dp.nip as nip_pembimbing
            ');
            $this->db->from('penilaian_seminar_proposal psp');
            $this->db->join('seminar_proposal_mahasiswa spm', 'psp.seminar_proposal_id = spm.id');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('dosen dp', 'pm.dosen_id = dp.id', 'left');
            $this->db->join('dosen d1', 'spm.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'spm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('psp.seminar_proposal_id', $seminar_id);
            $this->db->where('psp.status_penilaian', 'published');
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting published penilaian: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Method untuk mengecek syarat jurnal bimbingan
     */
    private function _check_jurnal_requirement($proposal_id)
    {
        try {
            // Minimum jurnal yang harus tervalidasi (bisa diatur di config)
            $minimum_required = 8;
            
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return [
                    'eligible' => false,
                    'minimum_required' => $minimum_required,
                    'total_validated' => 0,
                    'message' => 'Tabel jurnal bimbingan belum tersedia'
                ];
            }
            
            // Hitung jurnal yang sudah tervalidasi
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $validated_count = $this->db->count_all_results('jurnal_bimbingan');
            
            $eligible = $validated_count >= $minimum_required;
            
            return [
                'eligible' => $eligible,
                'minimum_required' => $minimum_required,
                'total_validated' => $validated_count,
                'message' => $eligible ? 
                    "Syarat jurnal bimbingan terpenuhi ({$validated_count}/{$minimum_required})" :
                    "Jurnal bimbingan belum mencukupi ({$validated_count}/{$minimum_required})"
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error checking jurnal requirement: ' . $e->getMessage());
            return [
                'eligible' => false,
                'minimum_required' => 8,
                'total_validated' => 0,
                'message' => 'Error checking jurnal requirement'
            ];
        }
    }

    /**
     * ✅ COMPREHENSIVE FIX: Enhanced workflow status determination
     */
    private function _determine_comprehensive_workflow_status($proposal, $seminar_data, $syarat_jurnal)
    {
        // CASE 1: Seminar sudah completed dan ada penilaian
        if ($seminar_data && $seminar_data->status === 'completed') {
            $penilaian = $this->_get_published_penilaian($seminar_data->id);
            
            if ($penilaian) {
                return [
                    'current_step' => 'completed_with_assessment',
                    'next_action' => 'Lanjut ke penelitian',
                    'status_text' => 'Selesai - Ada Penilaian',
                    'status_class' => 'success',
                    'description' => 'Seminar proposal telah selesai dan penilaian sudah tersedia. Anda dapat melanjutkan ke fase penelitian.',
                    'action_url' => base_url('mahasiswa/penelitian'),
                    'action_text' => 'Lihat Penilaian & Lanjut',
                    'show_penilaian' => true,
                    'can_submit' => false
                ];
            } else {
                return [
                    'current_step' => 'completed_no_assessment',
                    'next_action' => 'Menunggu publikasi penilaian',
                    'status_text' => 'Selesai - Menunggu Penilaian',
                    'status_class' => 'info',
                    'description' => 'Seminar proposal telah selesai, menunggu publikasi penilaian dari dosen.',
                    'action_url' => base_url('mahasiswa/seminar_proposal/detail/' . $seminar_data->id),
                    'action_text' => 'Lihat Detail',
                    'show_penilaian' => false,
                    'can_submit' => false
                ];
            }
        }
        
        // CASE 2: Belum memenuhi syarat jurnal
        if (!$syarat_jurnal['eligible']) {
            return [
                'current_step' => 'belum_eligible',
                'next_action' => 'Lengkapi jurnal bimbingan',
                'status_text' => 'Belum Memenuhi Syarat',
                'status_class' => 'warning',
                'description' => 'Jurnal bimbingan belum mencukupi untuk mengajukan seminar proposal',
                'action_url' => base_url('mahasiswa/bimbingan'),
                'action_text' => 'Tambah Jurnal Bimbingan',
                'show_penilaian' => false,
                'can_submit' => false
            ];
        }
        
        // CASE 3: Belum ada pengajuan seminar
        if (!$seminar_data) {
            return [
                'current_step' => 'belum_mengajukan',
                'next_action' => 'Ajukan Seminar Proposal',
                'status_text' => 'Siap Mengajukan',
                'status_class' => 'info',
                'description' => 'Semua syarat telah terpenuhi, Anda dapat mengajukan seminar proposal',
                'action_url' => base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id),
                'action_text' => 'Ajukan Sekarang',
                'show_penilaian' => false,
                'can_submit' => true
            ];
        }
        
        // CASE 4-N: Handle semua status seminar yang ada
        switch ($seminar_data->status) {
            case 'draft':
                return [
                    'current_step' => 'draft',
                    'next_action' => 'Lengkapi dan Submit',
                    'status_text' => 'Draft',
                    'status_class' => 'secondary',
                    'description' => 'Draft pengajuan telah dibuat, lengkapi dan submit untuk review',
                    'action_url' => base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id),
                    'action_text' => 'Lengkapi Draft',
                    'show_penilaian' => false,
                    'can_submit' => true
                ];
                
            case 'submitted':
            case 'review_pembimbing':
                return [
                    'current_step' => 'review_pembimbing',
                    'next_action' => 'Menunggu review pembimbing',
                    'status_text' => 'Sedang Direview Pembimbing',
                    'status_class' => 'warning',
                    'description' => 'Pengajuan sedang direview oleh dosen pembimbing. Estimasi: 3-5 hari kerja',
                    'action_url' => base_url('mahasiswa/seminar_proposal/detail/' . $seminar_data->id),
                    'action_text' => 'Lihat Detail',
                    'show_penilaian' => false,
                    'can_submit' => false
                ];
                
            case 'review_kaprodi':
                return [
                    'current_step' => 'review_kaprodi',
                    'next_action' => 'Menunggu review Kaprodi',
                    'status_text' => 'Sedang Direview Kaprodi',
                    'status_class' => 'primary',
                    'description' => 'Dosen pembimbing telah menyetujui, menunggu persetujuan Kaprodi',
                    'action_url' => base_url('mahasiswa/seminar_proposal/detail/' . $seminar_data->id),
                    'action_text' => 'Lihat Detail',
                    'show_penilaian' => false,
                    'can_submit' => false
                ];
                
            case 'approved':
                return [
                    'current_step' => 'approved',
                    'next_action' => 'Menunggu penjadwalan',
                    'status_text' => 'Disetujui',
                    'status_class' => 'success',
                    'description' => 'Pengajuan telah disetujui, menunggu penjadwalan seminar',
                    'action_url' => base_url('mahasiswa/seminar_proposal/detail/' . $seminar_data->id),
                    'action_text' => 'Lihat Detail',
                    'show_penilaian' => false,
                    'can_submit' => false
                ];
                
            case 'scheduled':
                return [
                    'current_step' => 'scheduled',
                    'next_action' => 'Persiapan seminar',
                    'status_text' => 'Terjadwal',
                    'status_class' => 'info',
                    'description' => 'Seminar telah dijadwalkan, bersiaplah untuk presentasi',
                    'action_url' => base_url('mahasiswa/seminar_proposal/detail/' . $seminar_data->id),
                    'action_text' => 'Lihat Jadwal',
                    'show_penilaian' => false,
                    'can_submit' => false
                ];
                
            case 'rejected':
                return [
                    'current_step' => 'rejected',
                    'next_action' => 'Perbaiki dan ajukan ulang',
                    'status_text' => 'Perlu Perbaikan',
                    'status_class' => 'danger',
                    'description' => 'Pengajuan perlu diperbaikan sesuai catatan dari pembimbing',
                    'action_url' => base_url('mahasiswa/seminar_proposal/ajukan/' . $proposal->id),
                    'action_text' => 'Ajukan Ulang',
                    'show_penilaian' => false,
                    'can_submit' => true
                ];
                
            default:
                return [
                    'current_step' => 'unknown',
                    'next_action' => 'Hubungi admin',
                    'status_text' => 'Status Tidak Dikenali',
                    'status_class' => 'secondary',
                    'description' => 'Status tidak dikenali, hubungi admin sistem',
                    'action_url' => base_url('mahasiswa/dashboard'),
                    'action_text' => 'Dashboard',
                    'show_penilaian' => false,
                    'can_submit' => false
                ];
        }
    }

    /**
     * ✅ COMPREHENSIVE FIX: Calculate comprehensive workflow progress
     */
    private function _calculate_comprehensive_workflow_progress($proposal, $seminar_data)
    {
        $progress = [
            'percentage' => 0,
            'current_phase' => 'preparation',
            'completed_steps' => [],
            'current_step' => '',
            'next_step' => '',
            'timeline_steps' => [
                'preparation' => ['title' => 'Persiapan', 'desc' => 'Menyiapkan berkas', 'completed' => false],
                'submission' => ['title' => 'Pengajuan', 'desc' => 'Berkas terkirim', 'completed' => false],
                'pembimbing_review' => ['title' => 'Review Dosen', 'desc' => 'Evaluasi pembimbing', 'completed' => false],
                'kaprodi_review' => ['title' => 'Review Kaprodi', 'desc' => 'Persetujuan akhir', 'completed' => false],
                'scheduling' => ['title' => 'Penjadwalan', 'desc' => 'Penentuan jadwal', 'completed' => false],
                'completion' => ['title' => 'Selesai', 'desc' => 'Seminar terlaksana', 'completed' => false]
            ]
        ];
        
        if (!$seminar_data) {
            $progress['percentage'] = 20;
            $progress['current_phase'] = 'preparation';
            $progress['current_step'] = 'Persiapan pengajuan seminar proposal';
            $progress['next_step'] = 'Ajukan seminar proposal';
            $progress['timeline_steps']['preparation']['completed'] = true;
            return $progress;
        }
        
        switch ($seminar_data->status) {
            case 'draft':
                $progress['percentage'] = 30;
                $progress['current_step'] = 'Draft pengajuan dibuat';
                $progress['next_step'] = 'Kirim pengajuan ke dosen pembimbing';
                $progress['completed_steps'] = ['preparation'];
                $progress['timeline_steps']['preparation']['completed'] = true;
                break;
                
            case 'submitted':
            case 'review_pembimbing':
                $progress['percentage'] = 50;
                $progress['current_step'] = 'Menunggu review dosen pembimbing';
                $progress['next_step'] = 'Review oleh dosen pembimbing';
                $progress['completed_steps'] = ['preparation', 'submission'];
                $progress['timeline_steps']['preparation']['completed'] = true;
                $progress['timeline_steps']['submission']['completed'] = true;
                break;
                
            case 'review_kaprodi':
                $progress['percentage'] = 70;
                $progress['current_step'] = 'Sedang direview Kaprodi';
                $progress['next_step'] = 'Menunggu persetujuan akhir';
                $progress['completed_steps'] = ['preparation', 'submission', 'pembimbing_review'];
                $progress['timeline_steps']['preparation']['completed'] = true;
                $progress['timeline_steps']['submission']['completed'] = true;
                $progress['timeline_steps']['pembimbing_review']['completed'] = true;
                break;
                
            case 'approved':
                $progress['percentage'] = 85;
                $progress['current_step'] = 'Disetujui, menunggu penjadwalan';
                $progress['next_step'] = 'Penentuan jadwal seminar';
                $progress['completed_steps'] = ['preparation', 'submission', 'pembimbing_review', 'kaprodi_review'];
                $progress['timeline_steps']['preparation']['completed'] = true;
                $progress['timeline_steps']['submission']['completed'] = true;
                $progress['timeline_steps']['pembimbing_review']['completed'] = true;
                $progress['timeline_steps']['kaprodi_review']['completed'] = true;
                break;
                
            case 'scheduled':
                $progress['percentage'] = 95;
                $progress['current_step'] = 'Terjadwal, menunggu pelaksanaan';
                $progress['next_step'] = 'Pelaksanaan seminar proposal';
                $progress['completed_steps'] = ['preparation', 'submission', 'pembimbing_review', 'kaprodi_review', 'scheduling'];
                $progress['timeline_steps']['preparation']['completed'] = true;
                $progress['timeline_steps']['submission']['completed'] = true;
                $progress['timeline_steps']['pembimbing_review']['completed'] = true;
                $progress['timeline_steps']['kaprodi_review']['completed'] = true;
                $progress['timeline_steps']['scheduling']['completed'] = true;
                break;
                
            case 'completed':
                $progress['percentage'] = 100;
                $progress['current_phase'] = 'completed';
                $progress['current_step'] = 'Seminar proposal selesai';
                $progress['next_step'] = 'Lanjut ke fase penelitian';
                $progress['completed_steps'] = ['preparation', 'submission', 'pembimbing_review', 'kaprodi_review', 'scheduling', 'completion'];
                foreach ($progress['timeline_steps'] as $key => $step) {
                    $progress['timeline_steps'][$key]['completed'] = true;
                }
                break;
                
            case 'rejected':
                $progress['percentage'] = 40;
                $progress['current_phase'] = 'revision';
                $progress['current_step'] = 'Ditolak, perlu revisi';
                $progress['next_step'] = 'Lakukan perbaikan dan ajukan ulang';
                $progress['completed_steps'] = ['preparation', 'submission'];
                $progress['timeline_steps']['preparation']['completed'] = true;
                $progress['timeline_steps']['submission']['completed'] = true;
                break;
        }
        
        return $progress;
    }

    /**
     * ✅ COMPREHENSIVE FIX: Determine if student can submit seminar proposal
     */
    private function _can_submit_seminar_comprehensive($proposal, $seminar_data, $syarat_jurnal)
    {
        // Check basic requirements
        if (!$syarat_jurnal['eligible']) return false;
        if (!$proposal) return false;
        
        // 🔧 MAJOR FIX: Jika seminar sudah completed, tidak boleh submit lagi
        if ($seminar_data && $seminar_data->status === 'completed') {
            return false;
        }
        
        // Check workflow status - harus dalam fase bimbingan atau seminar_proposal
        // 🔧 NOTE: proposal dengan workflow_status 'penelitian' yang punya seminar completed 
        // sudah di-handle di atas
        if (!in_array($proposal->workflow_status, ['bimbingan', 'seminar_proposal', 'penelitian'])) {
            return false;
        }
        
        // If no existing seminar, can submit (jika workflow masih eligible)
        if (!$seminar_data && in_array($proposal->workflow_status, ['bimbingan', 'seminar_proposal'])) {
            return true;
        }
        
        // If existing seminar is rejected or draft, can resubmit
        if ($seminar_data && in_array($seminar_data->status, ['rejected', 'draft'])) {
            return true;
        }
        
        return false;
    }

    // =================================================================
    // EXISTING METHODS (UNCHANGED)
    // =================================================================

    /**
     * Get validated jurnal
     */
    private function _get_validated_jurnal($proposal_id)
    {
        try {
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return [];
            }
            
            $this->db->select('jb.*, d.nama as nama_validator');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
            $this->db->where('jb.proposal_id', $proposal_id);
            $this->db->where('jb.status_validasi', '1');
            $this->db->order_by('jb.pertemuan_ke', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting validated jurnal: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lihat Penilaian Seminar Proposal yang sudah dipublikasikan
     */
    public function lihat_penilaian($seminar_id) {
        $this->load->helper('text');
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi akses - pastikan seminar milik mahasiswa yang login
        $this->db->select('spm.*, pm.judul, pm.mahasiswa_id');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->where('spm.id', $seminar_id);
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $seminar = $this->db->get()->row();
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau Anda tidak memiliki akses!');
            redirect('mahasiswa/seminar_proposal');
            return;
        }
        
        // Ambil data penilaian yang sudah dipublikasikan
        $penilaian = $this->_get_published_penilaian($seminar_id);
        
        if (!$penilaian) {
            $this->session->set_flashdata('info', 'Penilaian seminar proposal belum tersedia atau belum dipublikasikan.');
            redirect('mahasiswa/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        // Prepare data untuk view
        $data = [
            'title' => 'Hasil Penilaian Seminar Proposal',  // <-- TITLE RINGKAS
            'content' => 'mahasiswa/seminar_proposal/lihat_penilaian',
            'seminar' => $seminar,
            'penilaian' => $penilaian
        ];
        
        $this->_load_view($data);
    }
    
    /**
     * Helper method untuk cek apakah penilaian sudah dipublikasikan
     */
    public function is_penilaian_published($seminar_id) {
        if (!$this->db->table_exists('penilaian_seminar_proposal')) {
            return false;
        }
        
        $this->db->select('id');
        $this->db->from('penilaian_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        $this->db->where('status_penilaian', 'published');
        $this->db->where('published_at IS NOT NULL');
        
        $result = $this->db->get()->row();
        return !empty($result);
    }

    // =================================================================
    // FORM HANDLING METHODS
    // =================================================================

    /**
     * Form Pengajuan Seminar Proposal
     */
    public function ajukan($proposal_id = null)
    {
        if (!$proposal_id) {
            $proposal_id = $this->input->get('proposal_id');
        }
        
        if (!$proposal_id) {
            $this->session->set_flashdata('error', 'ID Proposal tidak ditemukan.');
            redirect('mahasiswa/seminar_proposal');
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get proposal data dengan security check
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau bukan milik Anda.');
            redirect('mahasiswa/seminar_proposal');
        }
        
        // 🔧 ENHANCEMENT: Cek apakah seminar sudah completed
        $existing_seminar = $this->_get_seminar_by_proposal_id($proposal->id);
        if ($existing_seminar && $existing_seminar->status === 'completed') {
            $this->session->set_flashdata('error', 'Seminar proposal sudah selesai. Anda tidak dapat mengajukan ulang.');
            redirect('mahasiswa/seminar_proposal');
            return;
        }
        
        // Cek syarat jurnal bimbingan
        $syarat_jurnal = $this->_check_jurnal_requirement($proposal->id);
        
        if (!$syarat_jurnal['eligible']) {
            $this->session->set_flashdata('error', 
                "Belum memenuhi syarat pengajuan seminar proposal. " . $syarat_jurnal['message']);
            redirect('mahasiswa/seminar_proposal');
        }
        
        $data = [
            'title' => $existing_seminar ? 'Edit Pengajuan Seminar Proposal' : 'Ajukan Seminar Proposal',
            'content' => 'mahasiswa/seminar_proposal/form_ajukan',
            'proposal' => $proposal,
            'existing_seminar' => $existing_seminar,
            'syarat_jurnal' => $syarat_jurnal,
            'is_edit' => (bool) $existing_seminar,
            'can_edit' => $existing_seminar ? in_array($existing_seminar->status, ['draft', 'rejected']) : true,
            // TAMBAHAN: Pass judul yang akan digunakan (prioritas dari seminar jika ada)
            'current_judul' => $existing_seminar && $existing_seminar->judul_seminar ? 
                              $existing_seminar->judul_seminar : $proposal->judul
        ];
        
        // ========================================
        // 🆕 TAMBAHAN BARU UNTUK EDIT JUDUL - AMAN
        // ========================================
        
        // Determine current judul - SAFE with fallback
        $current_judul = $proposal->judul; // Default fallback
        
        if ($existing_seminar && !empty($existing_seminar->judul_seminar)) {
            // If seminar already has custom title, use it
            $current_judul = $existing_seminar->judul_seminar;
        }
        
        // Add to existing data array - NO BREAKING CHANGES
        $data['current_judul'] = $current_judul;
        $data['judul_original'] = $proposal->judul; // For reference
        $data['allow_edit_judul'] = true; // Feature flag
        
        // ========================================
        // EXISTING CODE CONTINUES UNCHANGED
        // ========================================
        
        // Load daftar jurnal bimbingan yang sudah divalidasi
        $data['jurnal_validasi'] = $this->_get_validated_jurnal($proposal->id);
        
        $this->_load_view($data);
    }

    /**
     * Process Form Pengajuan
     */
    public function submit_ajukan()
    {
        $mahasiswa_id = $this->session->userdata('id');
        $proposal_id = $this->input->post('proposal_id');
        $is_edit = $this->input->post('is_edit') === '1';
        
        // Set validation rules
        $this->form_validation->set_rules('proposal_id', 'ID Proposal', 'required|integer');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'required|min_length[10]|max_length[1000]');
        
        if (!$is_edit || !empty($_FILES['file_proposal']['name'])) {
            $this->form_validation->set_rules('file_proposal', 'File Proposal', 'callback__check_file_proposal');
        }

        // ========================================
        // 🆕 TAMBAHAN VALIDATION UNTUK JUDUL - AMAN
        // ========================================
        
        // SAFE: Optional validation with fallback
        $judul_seminar = trim($this->input->post('judul_seminar'));
        if (!empty($judul_seminar)) {
            $this->form_validation->set_rules('judul_seminar', 'Judul Seminar', 'min_length[10]|max_length[250]');
        }
        
        // ========================================
        // VALIDATION RUN - TIDAK BERUBAH
        // ========================================
        
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_proposal/ajukan/' . $proposal_id);
            return;
        }
        
        // Verify proposal ownership
        $proposal = $this->_get_proposal_by_id($proposal_id, $mahasiswa_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak valid.');
            redirect('mahasiswa/seminar_proposal');
            return;
        }
        
        try {
            $this->db->trans_start();
            
            $data = [
                'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
                'status' => 'submitted',
                'current_step' => 'review_pembimbing',
                'status_pembimbing' => 'pending',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // ========================================
            // 🆕 TAMBAHAN UNTUK JUDUL SEMINAR - AMAN
            // ========================================
            
            // SAFE: Add judul_seminar only if provided and valid
            $judul_seminar = trim($this->input->post('judul_seminar'));
            if (!empty($judul_seminar) && strlen($judul_seminar) >= 10) {
                $data['judul_seminar'] = $judul_seminar;
            }
            // If not provided, field remains NULL (uses original proposal title via COALESCE)
            
            // Handle file upload if provided
            if (!empty($_FILES['file_proposal']['name'])) {
                $file_result = $this->_handle_file_upload('file_proposal', 'proposal_files');
                if ($file_result['status']) {
                    $data['file_proposal'] = $file_result['filename'];
                    
                    // Delete old file if editing
                    if ($is_edit) {
                        $existing = $this->_get_seminar_by_proposal_id($proposal_id);
                        if ($existing && $existing->file_proposal) {
                            $old_file_path = FCPATH . 'uploads/seminar_proposal/proposal_files/' . $existing->file_proposal;
                            if (file_exists($old_file_path)) {
                                unlink($old_file_path);
                            }
                        }
                    }
                } else {
                    throw new Exception($file_result['message']);
                }
            }
            
            $seminar_id = null;
            
            if ($is_edit) {
                // Update existing record
                $existing = $this->_get_seminar_by_proposal_id($proposal_id);
                if ($existing) {
                    $this->db->where('id', $existing->id);
                    $this->db->update('seminar_proposal_mahasiswa', $data);
                    $seminar_id = $existing->id;
                    $action_type = 'updated';
                } else {
                    throw new Exception('Data seminar tidak ditemukan untuk diupdate.');
                }
            } else {
                // Create new record
                $data['proposal_id'] = $proposal_id;
                $data['mahasiswa_id'] = $mahasiswa_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['created_by'] = $mahasiswa_id;
                
                $this->db->insert('seminar_proposal_mahasiswa', $data);
                $seminar_id = $this->db->insert_id();
                $action_type = 'created';
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            // Send notifications
            $this->_kirim_notifikasi_seminar_proposal($proposal, $data, $seminar_id);
            
            // Set success data
            $success_message = $is_edit 
                ? 'Pengajuan seminar proposal berhasil diperbarui dan sedang menunggu review ulang dari dosen pembimbing.'
                : 'Pengajuan seminar proposal berhasil dikirim dan sedang menunggu review dari dosen pembimbing.';
            
            $this->session->set_flashdata('submission_success', [
                'seminar_id' => $seminar_id,
                'proposal_id' => $proposal_id,
                'action' => $action_type,
                'status' => 'submitted',
                'current_step' => 'review_pembimbing',
                'message' => $success_message,
                'next_step_description' => 'Dosen pembimbing akan mereview pengajuan Anda dalam 3-5 hari kerja.',
                'estimated_time' => '3-5 hari kerja',
                'timestamp' => date('Y-m-d H:i:s'),
                'can_track' => true
            ]);
            
            redirect('mahasiswa/seminar_proposal?submission=success&seminar_id=' . $seminar_id . '&action=' . $action_type);
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Submit Seminar Proposal Error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat mengirim pengajuan: ' . $e->getMessage());
            redirect('mahasiswa/seminar_proposal/ajukan/' . $proposal_id);
        }
    }

    /**
     * Detail Seminar Proposal
     */
    public function detail($id = null)
    {
        if (!$id) {
            show_404();
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get seminar detail dengan security check
        $seminar = $this->_get_seminar_by_id($id, $mahasiswa_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar proposal tidak ditemukan.');
            redirect('mahasiswa/seminar_proposal');
        }
        
        // Get proposal data
        $proposal = $this->_get_proposal_by_id($seminar->proposal_id, $mahasiswa_id);
        
        // Get jurnal bimbingan yang sudah divalidasi
        $jurnal_validasi = $this->_get_validated_jurnal($seminar->proposal_id);
        
        // Get activity timeline
        $activity_timeline = $this->_get_activity_timeline($seminar->proposal_id, $seminar->id);
        
        // 🔧 ENHANCEMENT: Get penilaian jika ada
        $penilaian_data = null;
        if ($seminar->status === 'completed') {
            $penilaian_data = $this->_get_published_penilaian($seminar->id);
        }
        
        $data = [
            'title' => 'Detail Seminar Proposal',
            'content' => 'mahasiswa/seminar_proposal/detail',
            'seminar' => $seminar,
            'proposal' => $proposal,
            'jurnal_validasi' => $jurnal_validasi,
            'activity_timeline' => $activity_timeline,
            'penilaian_data' => $penilaian_data,
            'show_penilaian_link' => ($penilaian_data !== null)
        ];
        
        $this->_load_view($data);
    }

    // ========================================================================
    // 🆕 LETAKKAN METHOD BARU DI SINI - TEPAT SETELAH METHOD detail() SELESAI
    // ========================================================================
    
    /**
     * 🆕 METHOD BARU: Download file turnitin hasil pengecekkan plagiarisme
     * URL: mahasiswa/seminar_proposal/download_turnitin/ID
     */
    public function download_turnitin($seminar_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi input ID
        if (!is_numeric($seminar_id)) {
            $this->session->set_flashdata('error', 'ID seminar tidak valid.');
            redirect('mahasiswa/seminar_proposal');
            return;
        }
        
        // Get seminar data dengan security check
        $seminar = $this->_get_seminar_by_id($seminar_id, $mahasiswa_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar proposal tidak ditemukan atau Anda tidak memiliki akses.');
            redirect('mahasiswa/seminar_proposal');
            return;
        }
        
        // Cek apakah ada file turnitin yang diupload kaprodi
        if (empty($seminar->file_turnitin)) {
            $this->session->set_flashdata('error', 'File hasil pengecekkan plagiarisme belum tersedia. Kaprodi belum mengupload file turnitin.');
            redirect('mahasiswa/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        // ✅ PATH SESUAI DENGAN KAPRODI: uploads/turnitin/
        $file_path = FCPATH . 'uploads/turnitin/' . $seminar->file_turnitin;
        
        // Cek apakah file fisik ada di server
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server. Hubungi administrator.');
            
            // Log error untuk debugging
            log_message('error', "Turnitin file not found - Expected path: {$file_path}, Seminar ID: {$seminar_id}, File name: {$seminar->file_turnitin}");
            
            redirect('mahasiswa/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        // Validasi tipe file (harus PDF sesuai upload kaprodi)
        $file_info = pathinfo($file_path);
        if (strtolower($file_info['extension']) !== 'pdf') {
            $this->session->set_flashdata('error', 'Format file tidak valid. File harus berupa PDF.');
            redirect('mahasiswa/seminar_proposal/detail/' . $seminar_id);
            return;
        }
        
        // Load helper download
        $this->load->helper('download');
        
        // Generate nama file yang user-friendly untuk download
        // Format: Laporan_Turnitin_NamaMahasiswa_NIM_Tahun.pdf
        $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $seminar->nama_mahasiswa);
        $clean_nim = preg_replace('/[^a-zA-Z0-9]/', '', $seminar->nim);
        $year = date('Y');
        $download_name = "Laporan_Turnitin_{$clean_name}_{$clean_nim}_{$year}.pdf";
        
        // Log download activity untuk audit trail
        log_message('info', "Turnitin file downloaded - Seminar ID: {$seminar_id}, Mahasiswa ID: {$mahasiswa_id}, Original file: {$seminar->file_turnitin}, Download name: {$download_name}");
        
        // Set headers untuk download PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Baca dan output file
        readfile($file_path);
        exit;
    }
    
    // =================================================================
    // REMAINING HELPER METHODS
    // =================================================================

    /**
     * Method untuk mendapatkan proposal berdasarkan ID dengan security check
     */
    private function _get_proposal_by_id($proposal_id, $mahasiswa_id)
    {
        try {
            $this->db->select('
                pm.*,
                m.nama as nama_mahasiswa,
                m.nim,
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                p.nama as nama_prodi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id); // Security check
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting proposal by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Method untuk mendapatkan seminar berdasarkan proposal_id
     */
    private function _get_seminar_by_proposal_id($proposal_id)
    {
        try {
            $this->db->select('*');
            $this->db->from('seminar_proposal_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar by proposal ID: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Method untuk mendapatkan seminar berdasarkan ID dengan security check
     */
    private function _get_seminar_by_id($seminar_id, $mahasiswa_id)
    {
        try {
            $this->db->select('
                sp.*,
                pm.judul as proposal_judul_original,
                COALESCE(sp.judul_seminar, pm.judul) as judul,
                sp.judul_seminar,
                sp.plagiarism_percentage,
                sp.file_turnitin,
                sp.status_kaprodi,
                sp.komentar_kaprodi,
                sp.tanggal_review_kaprodi,
                sp.reviewed_by_kaprodi,
                pm.dosen_id,
                m.nama as nama_mahasiswa,
                m.nim,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                pr.nama as nama_prodi,
                d_pembimbing.nama as nama_pembimbing,
                d_pembimbing.email as email_pembimbing,
                d1.nama as nama_penguji1,
                d2.nama as nama_penguji2,
                dk.nama as nama_kaprodi_reviewer
            ');
            $this->db->from('seminar_proposal_mahasiswa sp');
            $this->db->join('proposal_mahasiswa pm', 'sp.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
            $this->db->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left');
            $this->db->join('dosen d1', 'sp.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'sp.dosen_penguji2_id = d2.id', 'left');
            $this->db->join('dosen dk', 'sp.reviewed_by_kaprodi = dk.id', 'left'); // 🆕 TAMBAHAN
            
            $this->db->where('sp.id', $seminar_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id); // Security check
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * FIXED: Load view dengan template mahasiswa.php
     */
    private function _load_view($data)
    {
        try {
            // Extract view path dan data lainnya
            $view_path = isset($data['content']) ? $data['content'] : '';
            $title = isset($data['title']) ? $data['title'] : 'Seminar Proposal';
            $styles = isset($data['styles']) ? $data['styles'] : '';
            $script = isset($data['script']) ? $data['script'] : '';
            
            // Remove keys yang tidak perlu dikirim ke view
            unset($data['content'], $data['styles'], $data['script']);
            
            // Render view content menjadi HTML string
            if (!empty($view_path)) {
                // Start output buffering
                ob_start();
                
                // Load view dengan data (extract semua variables)
                extract($data);
                include(APPPATH . 'views/' . $view_path . '.php');
                
                // Get rendered content
                $content = ob_get_clean();
            } else {
                $content = '<div class="alert alert-warning">No content specified.</div>';
            }
            
            // Prepare final data untuk template
            $template_data = array(
                'title' => $title,
                'content' => $content,
                'styles' => $styles,
                'script' => $script
            );
            
            // Load template mahasiswa
            if (file_exists(VIEWPATH . 'template/mahasiswa.php')) {
                $this->load->view('template/mahasiswa', $template_data);
            } else if (file_exists(VIEWPATH . 'template/mahasiswa_simple.php')) {
                $this->load->view('template/mahasiswa_simple', $template_data);
            } else {
                throw new Exception('Template mahasiswa tidak ditemukan');
            }
            
        } catch (Exception $e) {
            if (ENVIRONMENT === 'development') {
                show_error('View Loading Error: ' . $e->getMessage() . '<br><br>' . 
                          'View Path: ' . $view_path . '<br>' .
                          'Available Data: ' . print_r(array_keys($data), true));
            } else {
                // Production error handling
                $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
                redirect('mahasiswa/dashboard');
            }
        }
    }

    /**
     * Method untuk file upload dengan validation yang lebih ketat
     */
    private function _handle_file_upload($field_name, $subfolder = 'proposal_files')
    {
        try {
            $upload_path = FCPATH . 'uploads/seminar_proposal/' . $subfolder . '/';
            
            // Buat folder jika belum ada
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            
            // Generate unique filename
            $file_ext = pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
            $unique_name = 'SP_' . date('YmdHis') . '_' . uniqid() . '.' . $file_ext;
            
            $config = [
                'upload_path' => $upload_path,
                'allowed_types' => 'pdf|doc|docx',
                'max_size' => 5120, // 5MB
                'file_name' => $unique_name,
                'encrypt_name' => false,
                'remove_spaces' => true
            ];
            
            $this->upload->initialize($config);
            
            if ($this->upload->do_upload($field_name)) {
                $upload_data = $this->upload->data();
                
                return [
                    'status' => true,
                    'filename' => $upload_data['file_name'],
                    'original_name' => $upload_data['orig_name'],
                    'file_size' => $upload_data['file_size'],
                    'file_type' => $upload_data['file_type']
                ];
            } else {
                return [
                    'status' => false,
                    'message' => $this->upload->display_errors('', ''),
                    'filename' => null
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
                'filename' => null
            ];
        }
    }

    /**
     * Custom validation callback untuk file upload
     */
    public function _check_file_proposal($str)
    {
        if (empty($_FILES['file_proposal']['name'])) {
            $this->form_validation->set_message('_check_file_proposal', 'File proposal harus diupload.');
            return false;
        }
        
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $file_type = $_FILES['file_proposal']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $this->form_validation->set_message('_check_file_proposal', 'Format file tidak didukung. Gunakan PDF, DOC, atau DOCX.');
            return false;
        }
        
        // 🔧 FIXED: Max size 1MB (bukan 5MB)
        if ($_FILES['file_proposal']['size'] > 1048576) { // 1MB
            $this->form_validation->set_message('_check_file_proposal', 'Ukuran file terlalu besar. Maksimal 1MB.');
            return false;
        }
        
        return true;
    }

    /**
     * Get activity timeline for detail page
     */
    private function _get_activity_timeline($proposal_id, $seminar_id = null)
    {
        try {
            $timeline = [];
            
            // Get from jurnal bimbingan
            if ($this->db->table_exists('jurnal_bimbingan')) {
                $this->db->select('
                    created_at as tanggal,
                    CONCAT("Jurnal bimbingan pertemuan ke-", pertemuan_ke, " - ", materi_bimbingan) as aktivitas,
                    "bimbingan" as jenis,
                    status_validasi
                ');
                $this->db->from('jurnal_bimbingan');
                $this->db->where('proposal_id', $proposal_id);
                $this->db->order_by('created_at', 'DESC');
                $this->db->limit(5);
                
                $jurnal_timeline = $this->db->get()->result();
                $timeline = array_merge($timeline, $jurnal_timeline);
            }
            
            // Get from seminar proposal events
            if ($seminar_id && $this->db->table_exists('seminar_proposal_mahasiswa')) {
                $this->db->select('
                    created_at as tanggal,
                    "Pengajuan seminar proposal dibuat" as aktivitas,
                    "seminar_proposal" as jenis,
                    status
                ');
                $this->db->from('seminar_proposal_mahasiswa');
                $this->db->where('id', $seminar_id);
                
                $seminar_timeline = $this->db->get()->result();
                $timeline = array_merge($timeline, $seminar_timeline);
            }
            
            // Sort by date descending
            usort($timeline, function($a, $b) {
                return strtotime($b->tanggal) - strtotime($a->tanggal);
            });
            
            return array_slice($timeline, 0, 10); // Limit to 10 most recent
            
        } catch (Exception $e) {
            log_message('error', 'Error getting activity timeline: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Kirim notifikasi seminar proposal
     */
    // BAGIAN 1: GANTI METHOD _kirim_notifikasi_seminar_proposal() yang tidak lengkap
    private function _kirim_notifikasi_seminar_proposal($proposal, $seminar_data, $seminar_id)
    {
        try {
            // Setup email configuration
            $config = [
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
            
            $this->email->initialize($config);
            
            // Get data mahasiswa
            $mahasiswa = $this->db->select('m.*, p.nama as nama_prodi')
                                  ->from('mahasiswa m')
                                  ->join('prodi p', 'm.prodi_id = p.id')
                                  ->where('m.id', $proposal->mahasiswa_id)
                                  ->get()->row();
            
            // Get data dosen pembimbing
            $dosen_pembimbing = $this->db->get_where('dosen', ['id' => $proposal->dosen_id])->row();
            
            if (!$dosen_pembimbing || !$mahasiswa) {
                log_message('error', 'Data dosen pembimbing atau mahasiswa tidak ditemukan');
                return false;
            }
            
            // Tentukan apakah ini pengajuan baru atau pengajuan ulang
            $is_resubmission = $this->_is_resubmission($proposal->id);
            
            // Kirim email ke dosen pembimbing
            $this->_kirim_email_ke_dosen_pembimbing($mahasiswa, $dosen_pembimbing, $proposal, $seminar_data, $is_resubmission);
            
            // Kirim email konfirmasi ke mahasiswa
            $this->_kirim_email_konfirmasi_mahasiswa($mahasiswa, $dosen_pembimbing, $proposal, $seminar_data, $is_resubmission);
            
            log_message('info', "Email notifications sent successfully - Seminar ID: {$seminar_id}, Proposal ID: {$proposal->id}");
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending seminar proposal notification: ' . $e->getMessage());
            // Don't throw exception, let main process continue
            return false;
        }
    }
    
    // BAGIAN 2: METHOD untuk cek apakah ini pengajuan ulang atau baru
    private function _is_resubmission($proposal_id)
    {
        try {
            // Cek apakah ada seminar proposal sebelumnya yang ditolak untuk proposal ini
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status', 'rejected');
            $this->db->where('status_pembimbing', 'rejected');
            $count = $this->db->count_all_results('seminar_proposal_mahasiswa');
            
            return $count > 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error checking resubmission status: ' . $e->getMessage());
            return false;
        }
    }
    
    // BAGIAN 3: EMAIL KE DOSEN PEMBIMBING
    private function _kirim_email_ke_dosen_pembimbing($mahasiswa, $dosen_pembimbing, $proposal, $seminar_data, $is_resubmission)
    {
        try {
            $subject = $is_resubmission ? 
                '🔄 Pengajuan Ulang Seminar Proposal - Perlu Review' : 
                '📝 Pengajuan Seminar Proposal Baru - Perlu Review';
                
            $status_text = $is_resubmission ? 'PENGAJUAN ULANG' : 'PENGAJUAN BARU';
            $action_text = $is_resubmission ? 
                'Mahasiswa telah melakukan perbaikan dan mengajukan ulang seminar proposal yang sebelumnya ditolak.' :
                'Mahasiswa telah mengajukan seminar proposal.';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📝 {$status_text} Seminar Proposal</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$dosen_pembimbing->nama}</strong>,</p>
                    
                    <p>{$action_text}</p>
                    
                    <div style='background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #2196f3;'>
                        <h4 style='color: #1565c0; margin: 0 0 10px 0;'>👨‍🎓 Detail Mahasiswa:</h4>
                        <ul style='color: #1565c0; margin: 0;'>
                            <li><strong>Nama:</strong> {$mahasiswa->nama}</li>
                            <li><strong>NIM:</strong> {$mahasiswa->nim}</li>
                            <li><strong>Program Studi:</strong> {$mahasiswa->nama_prodi}</li>
                            <li><strong>Email:</strong> {$mahasiswa->email}</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3e0; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ff9800;'>
                        <h4 style='color: #ef6c00; margin: 0 0 10px 0;'>📚 Detail Proposal:</h4>
                        <ul style='color: #ef6c00; margin: 0;'>
                            <li><strong>Judul:</strong> {$proposal->judul}</li>
                            <li><strong>Tanggal Pengajuan:</strong> " . date('d F Y, H:i') . "</li>";
                            
            if (isset($seminar_data['keterangan_mahasiswa']) && !empty($seminar_data['keterangan_mahasiswa'])) {
                $message .= "<li><strong>Keterangan:</strong> {$seminar_data['keterangan_mahasiswa']}</li>";
            }
            
            $message .= "
                        </ul>
                    </div>";
            
            if ($is_resubmission) {
                $message .= "
                    <div style='background-color: #fff8e1; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #f57c00; margin: 0 0 10px 0;'>🔄 Informasi Pengajuan Ulang:</h4>
                        <p style='color: #f57c00; margin: 0;'>
                            Ini adalah pengajuan ulang setelah perbaikan. Mohon review kembali dengan cermat dan berikan feedback yang konstruktif.
                        </p>
                    </div>";
            }
            
            $message .= "
                    <p><strong>Langkah selanjutnya:</strong></p>
                    <ol>
                        <li>Login ke sistem SIM-TA untuk mereview pengajuan</li>
                        <li>Periksa kelengkapan dokumen dan syarat jurnal bimbingan</li>
                        <li>Berikan rekomendasi (setujui/tolak) dengan feedback yang jelas</li>
                        <li>Mahasiswa akan mendapat notifikasi otomatis hasil review Anda</li>
                    </ol>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('dosen/seminar_proposal') . "' 
                           style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            🔍 Review Pengajuan Sekarang
                        </a>
                    </div>
                    
                    <p style='font-size: 12px; color: #6c757d; margin-top: 20px;'>
                        <em>Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini. 
                        Untuk komunikasi dengan mahasiswa, gunakan sistem internal atau email langsung.</em>
                    </p>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                    STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                </div>
            </div>";
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($dosen_pembimbing->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', 'Email berhasil dikirim ke dosen pembimbing: ' . $dosen_pembimbing->email);
            } else {
                log_message('error', 'Gagal mengirim email ke dosen pembimbing: ' . $this->email->print_debugger());
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error mengirim email ke dosen pembimbing: ' . $e->getMessage());
        }
    }
    
    // BAGIAN 4: EMAIL KONFIRMASI KE MAHASISWA
    private function _kirim_email_konfirmasi_mahasiswa($mahasiswa, $dosen_pembimbing, $proposal, $seminar_data, $is_resubmission)
    {
        try {
            $subject = $is_resubmission ? 
                '✅ Konfirmasi Pengajuan Ulang Seminar Proposal' : 
                '✅ Konfirmasi Pengajuan Seminar Proposal';
                
            $status_text = $is_resubmission ? 'pengajuan ulang' : 'pengajuan';
            $greeting_text = $is_resubmission ? 
                'Pengajuan ulang seminar proposal Anda telah berhasil dikirim.' :
                'Pengajuan seminar proposal Anda telah berhasil dikirim.';
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Konfirmasi {$status_text} Seminar Proposal</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$mahasiswa->nama}</strong>,</p>
                    
                    <p>{$greeting_text}</p>
                    
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>📋 Detail Pengajuan:</h4>
                        <ul style='color: #155724; margin: 0;'>
                            <li><strong>Judul:</strong> {$proposal->judul}</li>
                            <li><strong>Pembimbing:</strong> {$dosen_pembimbing->nama}</li>
                            <li><strong>Tanggal Pengajuan:</strong> " . date('d F Y, H:i') . "</li>
                            <li><strong>Status:</strong> Menunggu Review Dosen Pembimbing</li>
                        </ul>
                    </div>
                    
                    <p><strong>Langkah selanjutnya:</strong></p>
                    <ol>
                        <li>Dosen pembimbing akan mereview pengajuan Anda dalam 3-5 hari kerja</li>
                        <li>Anda akan mendapat notifikasi email hasil review</li>
                        <li>Jika disetujui, pengajuan akan diteruskan ke Kaprodi</li>
                        <li>Jika ditolak, lakukan perbaikan sesuai catatan dan ajukan ulang</li>
                    </ol>
                    
                    <div style='background-color: #cce5ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>💡 Tips:</h4>
                        <ul style='color: #004085; margin: 0;'>
                            <li>Pantau status pengajuan melalui dashboard Anda</li>
                            <li>Pastikan email dan notifikasi sistem selalu Anda cek</li>
                            <li>Jika ada pertanyaan, hubungi dosen pembimbing langsung</li>
                        </ul>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('mahasiswa/seminar_proposal') . "' 
                           style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📊 Lihat Status Pengajuan
                        </a>
                    </div>
                    
                    <p>Terima kasih dan semoga sukses!</p>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 10px; text-align: center; font-size: 12px;'>
                    STK Santo Yakobus Merauke - Sistem Informasi Manajemen Tugas Akhir
                </div>
            </div>";
            
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($mahasiswa->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', 'Email konfirmasi berhasil dikirim ke mahasiswa: ' . $mahasiswa->email);
            } else {
                log_message('error', 'Gagal mengirim email konfirmasi ke mahasiswa: ' . $this->email->print_debugger());
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error mengirim email konfirmasi ke mahasiswa: ' . $e->getMessage());
        }
    }

    /**
     * Create fallback model jika model utama tidak ada
     */
    private function _create_fallback_model()
    {
        return new stdClass();
    }

    /**
     * Method untuk testing - bisa dihapus setelah sistem stabil
     */
    public function test()
    {
        if (ENVIRONMENT !== 'development') {
            show_404();
        }
        
        echo "<h2>Seminar Proposal Controller Test - COMPREHENSIVE FIXED VERSION</h2>";
        echo "<p><strong>Status:</strong> Comprehensive Fixed Controller berjalan dengan baik! ✅</p>";
        echo "<p><strong>Base URL:</strong> " . base_url() . "</p>";
        echo "<p><strong>Current URL:</strong> " . current_url() . "</p>";
        echo "<p><strong>Controller:</strong> " . $this->router->class . "</p>";
        echo "<p><strong>Method:</strong> " . $this->router->method . "</p>";
        
        echo "<h3>Session Data:</h3>";
        echo "<pre>" . print_r($this->session->userdata(), true) . "</pre>";
        
        echo "<h3>Database Test:</h3>";
        try {
            $result = $this->db->query("SELECT COUNT(*) as total FROM mahasiswa")->row();
            echo "<p>✅ Database connection OK - Total mahasiswa: " . $result->total . "</p>";
            
            // Test seminar proposal table
            $seminar_count = $this->db->query("SELECT COUNT(*) as total FROM seminar_proposal_mahasiswa")->row();
            echo "<p>✅ Seminar proposal table OK - Total records: " . $seminar_count->total . "</p>";
            
            // Test penilaian table
            if ($this->db->table_exists('penilaian_seminar_proposal')) {
                $penilaian_count = $this->db->query("SELECT COUNT(*) as total FROM penilaian_seminar_proposal")->row();
                echo "<p>✅ Penilaian table OK - Total records: " . $penilaian_count->total . "</p>";
            }
            
            // Test comprehensive data retrieval
            $mahasiswa_id = 44; // Test dengan ID mahasiswa yang ada
            $comprehensive_data = $this->_get_comprehensive_proposal_data($mahasiswa_id);
            echo "<p>✅ Comprehensive data test: " . ($comprehensive_data['proposal'] ? 'FOUND' : 'NOT FOUND') . "</p>";
            if ($comprehensive_data['proposal']) {
                echo "<p>   - Proposal ID: {$comprehensive_data['proposal']->id}</p>";
                echo "<p>   - Workflow Status: {$comprehensive_data['proposal']->workflow_status}</p>";
                echo "<p>   - Seminar Data: " . ($comprehensive_data['seminar_data'] ? 'YES (Status: ' . $comprehensive_data['seminar_data']->status . ')' : 'NO') . "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
        echo "<p><a href='" . base_url('mahasiswa/seminar_proposal') . "'>← Kembali ke Dashboard Seminar Proposal</a></p>";
    }
    
    /**
     * 🔍 DEBUG: Method khusus untuk debug query
     * URL: mahasiswa/seminar_proposal/debug_query
     */
    public function debug_query()
    {
        if (ENVIRONMENT !== 'development') {
            show_404();
            return;
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        echo "<h2>🔍 Debug Query untuk Mahasiswa ID: {$mahasiswa_id}</h2>";
        
        // ===== TEST 1: Query proposal_mahasiswa =====
        echo "<h3>📋 Test 1: Query proposal_mahasiswa</h3>";
        $this->db->select('id, judul, workflow_status, status_kaprodi, status_pembimbing, dosen_id');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('mahasiswa_id', $mahasiswa_id);
        $this->db->where('status_kaprodi', '1');
        $proposals = $this->db->get()->result();
        
        echo "<p><strong>SQL Query:</strong> " . $this->db->last_query() . "</p>";
        echo "<p><strong>Hasil:</strong> " . count($proposals) . " proposal ditemukan</p>";
        
        if ($proposals) {
            foreach ($proposals as $p) {
                echo "<p>✅ Proposal ID: {$p->id}, Workflow: {$p->workflow_status}, Kaprodi: {$p->status_kaprodi}</p>";
                
                // ===== TEST 2: Query seminar_proposal_mahasiswa untuk setiap proposal =====
                echo "<h4>🎯 Test 2: Query seminar untuk proposal ID {$p->id}</h4>";
                
                $this->db->select('id, status, status_pembimbing, status_kaprodi, judul_seminar');
                $this->db->from('seminar_proposal_mahasiswa');
                $this->db->where('proposal_id', $p->id);
                $seminar = $this->db->get()->result();
                
                echo "<p><strong>SQL Query:</strong> " . $this->db->last_query() . "</p>";
                echo "<p><strong>Hasil:</strong> " . count($seminar) . " seminar ditemukan</p>";
                
                if ($seminar) {
                    foreach ($seminar as $s) {
                        echo "<p>✅ Seminar ID: {$s->id}, Status: {$s->status}, Pembimbing: {$s->status_pembimbing}, Kaprodi: {$s->status_kaprodi}</p>";
                    }
                } else {
                    echo "<p>❌ Tidak ada data seminar untuk proposal {$p->id}</p>";
                }
                
                // ===== TEST 3: Test method _get_seminar_with_details =====
                echo "<h4>🔧 Test 3: Method _get_seminar_with_details untuk proposal ID {$p->id}</h4>";
                $seminar_detail = $this->_get_seminar_with_details($p->id);
                
                if ($seminar_detail) {
                    echo "<p>✅ Method berhasil: ID {$seminar_detail->id}, Status: {$seminar_detail->status}</p>";
                } else {
                    echo "<p>❌ Method gagal - tidak mengembalikan data</p>";
                }
                
                echo "<hr>";
            }
        }
        
        // ===== TEST 4: Test method comprehensive =====
        echo "<h3>🔧 Test 4: Method _get_comprehensive_proposal_data</h3>";
        $comprehensive = $this->_get_comprehensive_proposal_data($mahasiswa_id);
        
        echo "<p><strong>Hasil Proposal:</strong> " . ($comprehensive['proposal'] ? 'FOUND ID: ' . $comprehensive['proposal']->id : 'NULL') . "</p>";
        echo "<p><strong>Hasil Seminar:</strong> " . ($comprehensive['seminar_data'] ? 'FOUND ID: ' . $comprehensive['seminar_data']->id : 'NULL') . "</p>";
    }
}