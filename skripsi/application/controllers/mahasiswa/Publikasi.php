<?php
/**
 * =====================================================
 * CONTROLLER PUBLIKASI MAHASISWA - WORKFLOW FIXED VERSION
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * =====================================================
 * 
 * PERBAIKAN WORKFLOW STEP 2→3:
 * 1. ✅ Tambah handling submit_type di _process_form_publikasi()
 * 2. ✅ Tambah handling submit_type di _process_update() 
 * 3. ✅ Semua method lain TETAP TIDAK DIUBAH
 * 
 * File: application/controllers/mahasiswa/Publikasi.php
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Publikasi extends CI_Controller {

    private $mahasiswa_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session', 'upload', 'form_validation']);
        $this->load->helper(['url', 'file', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk mahasiswa
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if($this->session->userdata('level') != '3') {
            show_error('Akses ditolak. Halaman khusus mahasiswa.', 403);
        }
        
        $this->mahasiswa_id = $this->session->userdata('id');
    }

    /**
     * Dashboard publikasi mahasiswa - TIDAK DIUBAH
     */
    public function index() {
        // ✅ ROBUST: Get proposal dengan logic yang tidak terpengaruh inkonsistensi data
        $proposal = $this->_get_proposal_eligible();
        
        // Prepare data untuk view content - STRUKTUR TIDAK DIUBAH
        $view_data = [
            'proposal' => $proposal,
            'publikasi' => null,
            'syarat_status' => null,
            'jurnal_count' => 0,
            'eligible' => false
        ];
        
        if ($proposal) {
            // ✅ ROBUST: Cek syarat publikasi dengan logic simple
            $view_data['syarat_status'] = $this->_check_syarat_publikasi($proposal->id);
            $view_data['jurnal_count'] = $this->_count_jurnal_tervalidasi($proposal->id);
            $view_data['eligible'] = ($view_data['syarat_status'] === 'ELIGIBLE');
            
            // Get existing publikasi jika ada - LOGIC TIDAK DIUBAH
            $view_data['publikasi'] = $this->publikasi->get_by_proposal($proposal->id);
        }
        
        // Load template dengan pattern yang konsisten - TIDAK DIUBAH
        $this->_load_template('mahasiswa/publikasi/index', $view_data, 'Publikasi Tugas Akhir');
    }

    /**
     * Form pengajuan - TIDAK DIUBAH
     */
    public function ajukan($proposal_id = null) {
        // LOGIC BISNIS TIDAK DIUBAH - hanya method internal yang di-robust
        if (!$proposal_id) {
            $proposal = $this->_get_proposal_eligible();
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Tidak ada proposal yang eligible.');
                redirect('mahasiswa/publikasi');
                return;
            }
            $proposal_id = $proposal->id;
        }
        
        $proposal = $this->_get_proposal_by_id($proposal_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        // ✅ ROBUST: Cek syarat dengan logic simple (backward compatible)
        $syarat_check = $this->_check_syarat_publikasi($proposal_id);
        if ($syarat_check !== 'ELIGIBLE') {
            // Buat message yang user-friendly
            $jurnal_count = $this->_count_jurnal_tervalidasi($proposal_id);
            $message = "Syarat belum terpenuhi: Jurnal bimbingan masih {$jurnal_count}/16";
            $this->session->set_flashdata('error', $message);
            redirect('mahasiswa/publikasi');
            return;
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_form_publikasi($proposal);
        } else {
            $this->_show_form_pengajuan($proposal);
        }
    }

    /**
     * Edit pengajuan - TIDAK DIUBAH
     */
    public function edit($publikasi_id) {
        // LOGIC BISNIS TIDAK DIUBAH
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->mahasiswa_id != $this->mahasiswa_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('mahasiswa/publikasi/tracking/' . $publikasi_id);
            return;
        }
        
        // Hanya bisa edit jika status draft atau rejected - LOGIC TIDAK DIUBAH
        if (!in_array($publikasi->status, ['draft', 'rejected'])) {
            $this->session->set_flashdata('error', 'Tidak dapat mengedit. Status: ' . $publikasi->status);
            redirect('mahasiswa/publikasi/tracking/' . $publikasi_id);
            return;
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_update($publikasi);
        } else {
            $this->_show_form_edit($publikasi);
        }
    }

    /**
     * Detail/Tracking publikasi - TIDAK DIUBAH
     */
    public function tracking($publikasi_id) {
        // ✅ FIX: Gunakan method yang melakukan JOIN dengan tabel dosen
        $publikasi = $this->_get_publikasi_detail($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data publikasi tidak ditemukan.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        // Prepare data untuk view
        $view_data = [
            'publikasi' => $publikasi,
            'timeline' => $this->_get_publikasi_timeline($publikasi_id)
        ];
        
        $this->_load_template('mahasiswa/publikasi/tracking', $view_data, 'Tracking Publikasi - ' . $publikasi->nama_mahasiswa);
    }

    /**
     * Submit pengajuan ke dosen pembimbing - TIDAK DIUBAH
     */
    public function submit($publikasi_id) {
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->mahasiswa_id != $this->mahasiswa_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('mahasiswa/publikasi'); // FIX: Redirect ke index, bukan tracking
            return;
        }
        
        if ($publikasi->status !== 'draft') {
            $this->session->set_flashdata('error', 'Pengajuan sudah disubmit sebelumnya.');
            redirect('mahasiswa/publikasi'); // FIX: Redirect ke index
            return;
        }
        
        // Update status ke submitted
        $result = $this->publikasi->submit_pengajuan($publikasi_id);
        
        if ($result['success']) {
            // FIX: Set flashdata dengan info workflow step
            $this->session->set_flashdata('success', 
                'Pengajuan berhasil disubmit ke dosen pembimbing. ' .
                'Status: Menunggu review dosen (Step 5/9).'
            );
            
            // FIX: Send notification ke dosen pembimbing dengan error handling
            try {
                $this->_send_notification_to_dosen($publikasi);
                log_message('info', "Email notification sent for publikasi ID: {$publikasi_id}");
            } catch (Exception $e) {
                log_message('error', "Failed to send email notification: " . $e->getMessage());
                // Jangan gagalkan proses jika email gagal
            }
            
            // FIX: Log activity untuk tracking
            $this->_log_publikasi_activity($publikasi_id, 'submit_pengajuan', 'Mahasiswa submit pengajuan publikasi');
            
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        // FIX: Redirect ke dashboard publikasi (index) dengan progress workflow
        redirect('mahasiswa/publikasi');
    }

    /**
     * Download surat keterangan publikasi - TIDAK DIUBAH
     */
    public function download_surat($publikasi_id) {
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->status !== 'completed') {
            $this->session->set_flashdata('error', 'Surat belum tersedia.');
            redirect('mahasiswa/publikasi');
            return;
        }
        
        // Generate PDF surat keterangan - LOGIC TIDAK DIUBAH
        $this->load->library('pdf');
        
        $data = [
            'publikasi' => $publikasi,
            'mahasiswa' => $this->_get_mahasiswa_data(),
            'tanggal_surat' => date('d F Y')
        ];
        
        $html = $this->load->view('mahasiswa/publikasi/surat_keterangan', $data, TRUE);
        
        $this->pdf->filename = 'Surat_Keterangan_Publikasi_' . $publikasi->nim . '.pdf';
        $this->pdf->load_html($html);
        $this->pdf->render();
        $this->pdf->stream($this->pdf->filename, ["Attachment" => false]);
    }

    // =================================================================
    // ✅ ROBUST PRIVATE METHODS - HANYA 2 METHOD INI YANG DIUBAH
    // =================================================================

    /**
     * ✅ ROBUST: Get proposal eligible - TIDAK TERPENGARUH INKONSISTENSI DATA
     */
    private function _get_proposal_eligible() {
        try {
            $this->db->select('
                pm.*,
                m.nim, m.nama as nama_mahasiswa, m.email,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing, d.email as email_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
            $this->db->where('pm.mahasiswa_id', $this->mahasiswa_id);
            
            // ✅ ROBUST CHANGE: Cek status_kaprodi (yang reliable) bukan status (yang inkonsisten)
            // Ini mengatasi masalah inkonsistensi data secara permanen
            $this->db->where('pm.status_kaprodi', '1'); // Proposal sudah disetujui kaprodi
            
            $this->db->group_by('pm.id');
            $this->db->having('COUNT(jb.id) >= 16'); // Minimal 16 jurnal tervalidasi
            $this->db->order_by('pm.id', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error in _get_proposal_eligible: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ ROBUST: Check syarat publikasi - SIMPLE DAN KONSISTEN
     */
    private function _check_syarat_publikasi($proposal_id) {
        try {
            // ✅ SIMPLE: Hanya cek jurnal bimbingan, tidak ada syarat lain
            $jurnal_count = $this->_count_jurnal_tervalidasi($proposal_id);
            
            // Simple rule: 16+ jurnal = ELIGIBLE
            return ($jurnal_count >= 16) ? 'ELIGIBLE' : 'NOT_ELIGIBLE';
            
        } catch (Exception $e) {
            log_message('error', 'Error checking syarat publikasi: ' . $e->getMessage());
            return 'NOT_ELIGIBLE';
        }
    }

    // =================================================================
    // PRIVATE METHODS LAIN - SEMUA TIDAK DIUBAH KECUALI 2 METHOD PROCESSING
    // =================================================================

    /**
     * Template loading method yang konsisten - TIDAK DIUBAH
     */
    private function _load_template($view_path, $data = [], $title = 'Publikasi Tugas Akhir', $script = '') {
        // Prepare template data seperti controller phase lain yang working
        $template_data = [
            'title' => $title,
            'content' => $this->load->view($view_path, $data, TRUE),
            'script' => $script
        ];
        
        // Load template mahasiswa dengan pattern konsisten
        $this->load->view('template/mahasiswa', $template_data);
    }

    /**
     * ✅ FIX: Method untuk get publikasi dengan JOIN lengkap - NAMA TABEL DIPERBAIKI
     * Letakkan setelah method _get_publikasi_timeline()
     */
    private function _get_publikasi_detail($publikasi_id, $mahasiswa_id = null) {
        try {
            // ✅ PERBAIKAN: Gunakan nama tabel yang benar 'publikasi_tugas_akhir'
            $this->db->select('
                pta.*,
                pm.judul as judul_proposal,
                pm.dosen_id as pembimbing_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pr.nama as nama_prodi,
                d.nama as nama_pembimbing, d.email as email_pembimbing
            ');
            $this->db->from('publikasi_tugas_akhir pta');  // ← PERBAIKAN: nama tabel yang benar
            $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi pr', 'm.prodi_id = pr.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pta.id', $publikasi_id);
            
            if ($mahasiswa_id) {
                $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            }
            
            $result = $this->db->get()->row();
            
            // Fallback untuk nama_pembimbing jika null
            if ($result && empty($result->nama_pembimbing)) {
                $result->nama_pembimbing = 'Belum Ditetapkan';
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting publikasi detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get proposal by ID - TIDAK DIUBAH
     */
    private function _get_proposal_by_id($proposal_id) {
        $this->db->select('
            pm.*,
            m.nim, m.nama as nama_mahasiswa, m.email,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing, d.email as email_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.mahasiswa_id', $this->mahasiswa_id);
        
        return $this->db->get()->row();
    }

    /**
     * Count jurnal tervalidasi - TIDAK DIUBAH
     */
    private function _count_jurnal_tervalidasi($proposal_id) {
        return $this->db->where('proposal_id', $proposal_id)
                       ->where('status_validasi', '1')
                       ->count_all_results('jurnal_bimbingan');
    }

    /**
     * ✅ UBAH: Show form pengajuan dengan dosen list
     */
    private function _show_form_pengajuan($proposal) {
        $view_data = [
            'title' => 'Ajukan Publikasi Tugas Akhir',
            'proposal' => $proposal,
            'action' => 'ajukan',
            'dosen_list' => $this->_get_dosen_list() // ✅ TAMBAH: Load dosen list
        ];
        
        $this->_load_template('mahasiswa/publikasi/form', $view_data, 'Ajukan Publikasi Tugas Akhir', $this->_get_form_script());
    }

    /**
     * ✅ UBAH: Show form edit dengan dosen list
     */
    private function _show_form_edit($publikasi) {
        $view_data = [
            'title' => 'Edit Publikasi Tugas Akhir',
            'publikasi' => $publikasi,
            'action' => 'edit',
            'dosen_list' => $this->_get_dosen_list() // ✅ TAMBAH: Load dosen list
        ];
        
        $this->_load_template('mahasiswa/publikasi/form', $view_data, 'Edit Publikasi Tugas Akhir', $this->_get_form_script());
    }

    // =================================================================
    // ✅ PERBAIKAN UTAMA: 2 METHOD PROCESSING YANG DITAMBAH SUBMIT_TYPE HANDLING
    // =================================================================

    /**
     * ✅ ENHANCED: Process form publikasi dengan dropdown dosen
     */
    private function _process_form_publikasi($proposal) {
        // ✅ Validation rules untuk dropdown dosen
        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('nim', 'NIM', 'required|trim|numeric|max_length[20]');
        $this->form_validation->set_rules('program_studi', 'Program Studi', 'required|trim');
        $this->form_validation->set_rules('judul_skripsi_final', 'Judul Skripsi Final', 'required|trim|min_length[10]');
        $this->form_validation->set_rules('dosen_pembimbing_id', 'Dosen Pembimbing', 'required|numeric'); // ✅ UBAH: ID bukan nama
        $this->form_validation->set_rules('tanggal_ujian_skripsi', 'Tanggal Ujian Skripsi', 'required');
        $this->form_validation->set_rules('link_repository', 'Link Repository', 'trim|valid_url');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'trim');
        
        if ($this->form_validation->run() === FALSE) {
            $this->_show_form_pengajuan($proposal);
            return;
        }
        
        // ✅ Get data dosen dari dropdown selection
        $dosen_pembimbing_id = $this->input->post('dosen_pembimbing_id');
        $dosen_data = $this->_get_dosen_by_id($dosen_pembimbing_id);
        
        if (!$dosen_data) {
            $this->session->set_flashdata('error', 'Data dosen pembimbing tidak ditemukan. Silakan pilih dosen lain.');
            $this->_show_form_pengajuan($proposal);
            return;
        }
        
        // Handle file uploads - TIDAK DIUBAH
        $upload_result = $this->_handle_file_uploads(true);
        if (!$upload_result['success']) {
            $this->session->set_flashdata('error', $upload_result['message']);
            $this->_show_form_pengajuan($proposal);
            return;
        }
        
        // Submit type logic - TIDAK DIUBAH
        $submit_type = $this->input->post('submit_type');
        $save_draft = $this->input->post('save_draft');
        
        if ($save_draft || $submit_type === 'draft') {
            $status = 'draft';
            $workflow_step = 'Step 2 (Draft)';
        } else {
            $status = 'submitted';
            $workflow_step = 'Step 4-6 (Review Dosen)';
        }
        
        // ✅ ENHANCED: Data mapping dengan data dosen dari database
        $data = [
            'proposal_mahasiswa_id' => $proposal ? $proposal->id : null,
            'mahasiswa_id' => $this->mahasiswa_id,
            'dosen_pembimbing_id' => $dosen_pembimbing_id, // ✅ UBAH: Simpan ID dosen
            
            // Manual input data
            'nim' => $this->input->post('nim'),
            'nama_mahasiswa' => $this->input->post('nama_lengkap'),
            'program_studi' => $this->input->post('program_studi'),
            'judul_skripsi_final' => $this->input->post('judul_skripsi_final'),
            'nama_dosen_pembimbing' => $dosen_data->nama, // ✅ DARI DATABASE
            'tanggal_ujian_skripsi' => $this->input->post('tanggal_ujian_skripsi'),
            
            // File uploads - TIDAK DIUBAH
            'file_surat_revisi' => $upload_result['files']['file_surat_revisi'] ?? null,
            'file_skripsi_final' => $upload_result['files']['file_skripsi_final'] ?? null,
            'file_surat_perpustakaan' => $upload_result['files']['file_surat_perpustakaan'] ?? null,
            'link_repository' => $this->input->post('link_repository'),
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
            'status' => $status,
            'tanggal_pengajuan' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert ke database - TIDAK DIUBAH
        $result = $this->publikasi->create($data);
        
        if ($result['success']) {
            if ($status === 'submitted') {
                $this->session->set_flashdata('success', 
                    '✅ Pengajuan berhasil dikirim ke dosen pembimbing! ' .
                    'Status: Step 4-6 (Review Dosen). ' .
                    'Dosen akan mendapat notifikasi untuk review.'
                );
                
                // ✅ ENHANCED: Email notification dengan data dosen reliable
                try {
                    $email_sent = $this->_send_notification_to_dosen_reliable($dosen_data, $data);
                    if ($email_sent) {
                        log_message('info', "✅ Email notification berhasil untuk publikasi ID: {$result['id']}");
                    } else {
                        log_message('warning', "⚠️ Email notification gagal tapi workflow tetap lanjut");
                        $current_success = $this->session->flashdata('success');
                        $this->session->set_flashdata('success', 
                            $current_success . ' (Catatan: Notifikasi email mungkin bermasalah, silakan hubungi dosen pembimbing secara manual.)'
                        );
                    }
                } catch (Exception $e) {
                    log_message('error', "❌ Exception saat kirim email: " . $e->getMessage());
                    $current_success = $this->session->flashdata('success');
                    $this->session->set_flashdata('success', 
                        $current_success . ' (Catatan: Sistem email bermasalah, silakan hubungi dosen pembimbing secara manual.)'
                    );
                }
                
            } else {
                $this->session->set_flashdata('success', 
                    '💾 Pengajuan publikasi berhasil disimpan sebagai draft. ' .
                    'Status: Step 2 (Draft). ' .
                    'Anda dapat melanjutkan untuk mengirim ajuan ke dosen pembimbing.'
                );
            }
            
            redirect('mahasiswa/publikasi/tracking/' . $result['id']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
            $this->_show_form_pengajuan($proposal);
        }
    }

/**
 * ✅ COMPLETELY FIXED: Process update publikasi untuk proper resubmit
 * Mengatasi masalah status_pembimbing tidak direset saat resubmit
 */
private function _process_update($publikasi) {
    // Validation rules - TIDAK BERUBAH
    $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|trim|max_length[100]');
    $this->form_validation->set_rules('nim', 'NIM', 'required|trim|numeric|max_length[20]');
    $this->form_validation->set_rules('program_studi', 'Program Studi', 'required|trim');
    $this->form_validation->set_rules('judul_skripsi_final', 'Judul Skripsi Final', 'required|trim|min_length[10]');
    $this->form_validation->set_rules('dosen_pembimbing_id', 'Dosen Pembimbing', 'required|numeric');
    $this->form_validation->set_rules('tanggal_ujian_skripsi', 'Tanggal Ujian Skripsi', 'required');
    $this->form_validation->set_rules('link_repository', 'Link Repository', 'trim|valid_url');
    $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'trim');
    
    if ($this->form_validation->run() === FALSE) {
        $this->_show_form_edit($publikasi);
        return;
    }
    
    // Get data dosen - TIDAK BERUBAH
    $dosen_pembimbing_id = $this->input->post('dosen_pembimbing_id');
    $dosen_data = $this->_get_dosen_by_id($dosen_pembimbing_id);
    
    if (!$dosen_data) {
        $this->session->set_flashdata('error', 'Data dosen pembimbing tidak ditemukan. Silakan pilih dosen lain.');
        $this->_show_form_edit($publikasi);
        return;
    }
    
    // Handle file uploads - TIDAK BERUBAH
    $upload_result = $this->_handle_file_uploads(false);
    if (!$upload_result['success']) {
        $this->session->set_flashdata('error', $upload_result['message']);
        $this->_show_form_edit($publikasi);
        return;
    }
    
    $submit_type = $this->input->post('submit_type');
    $original_status = $publikasi->status;
    $original_status_pembimbing = $publikasi->status_pembimbing;
    
    // ✅ CRITICAL: Data mapping dengan explicit field handling
    $data = [
        'dosen_pembimbing_id' => $dosen_pembimbing_id,
        'nim' => $this->input->post('nim'),
        'nama_mahasiswa' => $this->input->post('nama_lengkap'),
        'program_studi' => $this->input->post('program_studi'),
        'judul_skripsi_final' => $this->input->post('judul_skripsi_final'),
        'nama_dosen_pembimbing' => $dosen_data->nama,
        'tanggal_ujian_skripsi' => $this->input->post('tanggal_ujian_skripsi'),
        'link_repository' => $this->input->post('link_repository'),
        'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // ✅ CRITICAL FIX: Enhanced status update logic
    $is_resubmit = false;
    $is_new_submit = false;
    
    if ($submit_type === 'submit') {
        // ✅ Handle both draft dan rejected status untuk submission
        if (in_array($original_status, ['draft', 'rejected'])) {
            
            // ✅ CORE RESUBMIT LOGIC: Update semua field yang diperlukan
            $data['status'] = 'submitted';
            $data['status_pembimbing'] = 'pending'; // ⚠️ CRITICAL: Reset ke pending
            $data['tanggal_pengajuan'] = date('Y-m-d H:i:s'); // ⚠️ CRITICAL: New timestamp
            
            // ✅ CLEAN SLATE: Reset previous review data
            $data['tanggal_review_pembimbing'] = null;
            $data['komentar_pembimbing'] = null;
            
            // ✅ TRACK type of submission
            if ($original_status === 'rejected') {
                $is_resubmit = true;
                
                // Add resubmit marker untuk tracking
                $original_keterangan = $data['keterangan_mahasiswa'];
                if (!empty($original_keterangan)) {
                    $data['keterangan_mahasiswa'] = '[RESUBMIT] ' . $original_keterangan;
                } else {
                    $data['keterangan_mahasiswa'] = '[RESUBMIT] Pengajuan ulang setelah perbaikan sesuai catatan dosen pembimbing';
                }
                
                log_message('info', "Publikasi resubmit: ID {$publikasi->id}, Mahasiswa: {$this->mahasiswa_id}");
                
            } else {
                $is_new_submit = true;
                log_message('info', "Publikasi new submit: ID {$publikasi->id}, Mahasiswa: {$this->mahasiswa_id}");
            }
        }
    } elseif ($submit_type === 'update') {
        // ✅ PURE UPDATE: hanya update data tanpa mengubah workflow status
        // Tidak mengubah: status, status_pembimbing, tanggal_pengajuan, dll
        log_message('info', "Publikasi data update: ID {$publikasi->id}, Mahasiswa: {$this->mahasiswa_id}");
    }
    
    // Update file jika ada - TIDAK BERUBAH
    if (!empty($upload_result['files'])) {
        $data = array_merge($data, $upload_result['files']);
    }
    
    // ✅ DATABASE TRANSACTION: Ensure data consistency
    $this->db->trans_start();
    
    try {
        // ✅ EXECUTE update dengan proper validation
        $result = $this->publikasi->update($publikasi->id, $data, $this->mahasiswa_id);
        
        if (!$result['success']) {
            throw new Exception($result['message']);
        }
        
        // ✅ VERIFICATION: Check if update actually happened
        $updated_publikasi = $this->publikasi->get_by_id($publikasi->id, $this->mahasiswa_id);
        
        if ($is_resubmit && $submit_type === 'submit') {
            // ✅ VERIFY critical fields updated correctly
            if ($updated_publikasi->status !== 'submitted' || 
                $updated_publikasi->status_pembimbing !== 'pending') {
                
                throw new Exception("Resubmit verification failed: status={$updated_publikasi->status}, status_pembimbing={$updated_publikasi->status_pembimbing}");
            }
            
            log_message('info', "Resubmit verification successful: ID {$publikasi->id}");
        }
        
        // ✅ NOTIFICATION: Send email untuk resubmit/new submit
        if (($is_resubmit || $is_new_submit) && $submit_type === 'submit') {
            try {
                $this->_send_submission_notification($publikasi->id, $dosen_pembimbing_id, $is_resubmit);
                log_message('info', "Submission notification sent - Publikasi ID: {$publikasi->id}, Type: " . ($is_resubmit ? 'resubmit' : 'new'));
            } catch (Exception $e) {
                log_message('error', 'Error sending submission notification: ' . $e->getMessage());
                // Continue - notification error tidak menghentikan proses
            }
        }
        
        $this->db->trans_complete();
        
        // ✅ SUCCESS MESSAGES: Enhanced berdasarkan action type
        if ($submit_type === 'submit' && in_array($original_status, ['draft', 'rejected'])) {
            if ($is_resubmit) {
                $this->session->set_flashdata('success', 
                    '✅ Pengajuan berhasil dikirim ulang ke dosen pembimbing! ' .
                    'Status berubah dari "rejected" ke "submitted". ' .
                    'Dosen akan menerima notifikasi untuk review ulang dokumen yang telah diperbaiki. ' .
                    'Data akan segera muncul di dashboard dosen untuk direview.'
                );
            } else {
                $this->session->set_flashdata('success', 
                    '✅ Pengajuan berhasil dikirim ke dosen pembimbing! ' .
                    'Status: Step 4-6 (Review Dosen). ' .
                    'Dosen akan mendapat notifikasi untuk review dokumen publikasi Anda.'
                );
            }
        } elseif ($submit_type === 'update') {
            if ($original_status === 'rejected') {
                $this->session->set_flashdata('success', 
                    '💾 Data publikasi berhasil diperbarui. ' .
                    'Setelah selesai memperbaiki semua dokumen, jangan lupa klik tombol "Kirim Ajuan Ulang ke Dosen" untuk mengirim ke dosen pembimbing.'
                );
            } else {
                $this->session->set_flashdata('success', '💾 Data publikasi berhasil diperbarui.');
            }
        }
        
        redirect('mahasiswa/publikasi/tracking/' . $result['id']);
        
    } catch (Exception $e) {
        $this->db->trans_rollback();
        log_message('error', 'Failed to update publikasi: ' . $e->getMessage());
        $this->session->set_flashdata('error', 'Gagal menyimpan perubahan: ' . $e->getMessage());
        $this->_show_form_edit($publikasi);
    }
}

/**
 * ✅ ENHANCED: Send submission notification dengan type differentiation
 */
private function _send_submission_notification($publikasi_id, $dosen_id, $is_resubmit = false) {
    try {
        // Get publikasi data dengan detail lengkap
        $publikasi = $this->publikasi->get_detail($publikasi_id);
        $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
        
        if (!$publikasi || !$dosen || empty($dosen->email)) {
            log_message('warning', 'Cannot send notification: missing data or email');
            return false;
        }
        
        // Setup email configuration
        $this->email->clear();
        $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($dosen->email);
        
        // ✅ DIFFERENTIATE: Email content berdasarkan type
        if ($is_resubmit) {
            $this->email->subject('🔄 Pengajuan Publikasi Ulang - ' . $publikasi->nama_mahasiswa);
            $title = '🔄 Pengajuan Publikasi Ulang';
            $message_intro = 'Mahasiswa bimbingan Anda telah <strong>mengajukan ulang publikasi</strong> setelah melakukan perbaikan sesuai catatan Anda sebelumnya.';
            $bg_color = '#28a745'; // Green untuk resubmit
        } else {
            $this->email->subject('📝 Pengajuan Publikasi Baru - ' . $publikasi->nama_mahasiswa);
            $title = '📝 Pengajuan Publikasi Baru';
            $message_intro = 'Mahasiswa bimbingan Anda telah <strong>mengajukan publikasi tugas akhir</strong> dan memerlukan review dari Anda.';
            $bg_color = '#007bff'; // Blue untuk new submission
        }
        
        // Enhanced email template
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: {$bg_color}; color: white; padding: 20px; text-align: center;'>
                <h2>{$title}</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Kepada Yth. <strong>{$dosen->nama}</strong>,</p>
                
                <p>{$message_intro}</p>
                
                <div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>👨‍🎓 Data Mahasiswa:</h4>
                    <ul>
                        <li><strong>Nama:</strong> {$publikasi->nama_mahasiswa}</li>
                        <li><strong>NIM:</strong> {$publikasi->nim}</li>
                        <li><strong>Program Studi:</strong> {$publikasi->program_studi}</li>
                        <li><strong>Judul Skripsi:</strong> {$publikasi->judul_skripsi_final}</li>
                    </ul>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>📋 Detail Pengajuan:</h4>
                    <p><strong>Tanggal Pengajuan:</strong> " . date('d F Y, H:i') . " WIT</p>
                    <p><strong>Status:</strong> Menunggu Review Dosen Pembimbing</p>
                    " . (!empty($publikasi->keterangan_mahasiswa) ? 
                        "<p><strong>Keterangan:</strong> " . strip_tags($publikasi->keterangan_mahasiswa) . "</p>" : '') . "
                </div>
                
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='" . base_url('dosen/publikasi/review/' . $publikasi_id) . "' 
                       style='background: {$bg_color}; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        👁️ " . ($is_resubmit ? 'Review Pengajuan Ulang' : 'Review Pengajuan') . "
                    </a>
                </div>
                
                <p><small><em>Email otomatis dari Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus Merauke</em></small></p>
            </div>
        </div>
        ";
        
        $this->email->message($message);
        $send_result = $this->email->send();
        
        if (!$send_result) {
            log_message('error', 'Failed to send email: ' . $this->email->print_debugger());
        }
        
        return $send_result;
        
    } catch (Exception $e) {
        log_message('error', 'Error sending submission notification: ' . $e->getMessage());
        return false;
    }
}

/**
 * ✅ NEW METHOD: Send resubmit notification ke dosen
 */
private function _send_resubmit_notification($publikasi_id, $dosen_id) {
    try {
        // Get publikasi data dengan detail lengkap
        $publikasi = $this->publikasi->get_detail($publikasi_id);
        $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
        
        if (!$publikasi || !$dosen || empty($dosen->email)) {
            return false;
        }
        
        // Setup email configuration - sama seperti notification lainnya
        $this->email->clear();
        $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
        $this->email->to($dosen->email);
        $this->email->subject('🔄 Pengajuan Publikasi Ulang - ' . $publikasi->nama_mahasiswa);
        
        // Enhanced email template untuk resubmit
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #28a745; color: white; padding: 20px; text-align: center;'>
                <h2>🔄 Pengajuan Publikasi Ulang</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <p>Kepada Yth. <strong>{$dosen->nama}</strong>,</p>
                
                <p>Mahasiswa bimbingan Anda telah <strong>mengajukan ulang publikasi</strong> setelah melakukan perbaikan.</p>
                
                <div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>👨‍🎓 Data Mahasiswa:</h4>
                    <ul>
                        <li><strong>Nama:</strong> {$publikasi->nama_mahasiswa}</li>
                        <li><strong>NIM:</strong> {$publikasi->nim}</li>
                        <li><strong>Program Studi:</strong> {$publikasi->program_studi}</li>
                        <li><strong>Judul Skripsi:</strong> {$publikasi->judul_skripsi_final}</li>
                    </ul>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    <h4>🔄 Status Resubmit:</h4>
                    <p><strong>Tanggal Pengajuan Ulang:</strong> " . date('d F Y, H:i') . " WIT</p>
                    <p><strong>Status:</strong> Menunggu Review Dosen Pembimbing</p>
                    <p><strong>Keterangan:</strong> " . strip_tags($publikasi->keterangan_mahasiswa) . "</p>
                </div>
                
                <div style='text-align: center; margin: 20px 0;'>
                    <a href='" . base_url('dosen/publikasi/review/' . $publikasi_id) . "' 
                       style='background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        👁️ Review Pengajuan Ulang
                    </a>
                </div>
                
                <p><small><em>Email otomatis dari Sistem Informasi Manajemen Tugas Akhir STK Santo Yakobus Merauke</em></small></p>
            </div>
        </div>
        ";
        
        $this->email->message($message);
        return $this->email->send();
        
    } catch (Exception $e) {
        log_message('error', 'Error sending resubmit notification: ' . $e->getMessage());
        return false;
    }
}

    // =================================================================
    // SEMUA METHOD HELPER LAINNYA - TIDAK DIUBAH
    // =================================================================

    /**
     * Handle file uploads - TIDAK DIUBAH
     */
    private function _handle_file_uploads($required = true) {
        // Definisi mapping file ke subfolder masing-masing
        $file_config = [
            'file_surat_perpustakaan' => [
                'path' => './uploads/publikasi/surat_perpustakaan/',
                'max_size' => 1024, // 1MB
                'prefix' => 'SURAT_PERPUS'
            ],
            'file_surat_revisi' => [
                'path' => './uploads/publikasi/surat_revisi/',
                'max_size' => 1024, // 1MB  
                'prefix' => 'SURAT_REVISI'
            ],
            'file_skripsi_final' => [
                'path' => './uploads/publikasi/skripsi_final/',
                'max_size' => 5120, // 5MB
                'prefix' => 'SKRIPSI_FINAL'
            ]
        ];
        
        $uploaded_files = [];
        $errors = [];
        
        // Loop setiap file dengan config masing-masing
        foreach ($file_config as $field => $config) {
            if (!empty($_FILES[$field]['name'])) {
                
                // Buat direktori jika belum ada
                if (!is_dir($config['path'])) {
                    mkdir($config['path'], 0755, true);
                }
                
                // Set upload config untuk file ini
                $upload_config = [
                    'upload_path' => $config['path'],
                    'allowed_types' => 'pdf',
                    'max_size' => $config['max_size'],
                    'encrypt_name' => false,
                    'file_name' => $this->_generate_filename($config['prefix'])
                ];
                
                $this->upload->initialize($upload_config);
                
                if ($this->upload->do_upload($field)) {
                    $uploaded_files[$field] = $this->upload->data('file_name');
                    log_message('info', "File uploaded: {$field} -> {$uploaded_files[$field]}");
                } else {
                    $upload_error = $this->upload->display_errors('', '');
                    $errors[] = $field . ': ' . $upload_error;
                    log_message('error', "Upload failed for {$field}: {$upload_error}");
                }
                
            } elseif ($required) {
                $errors[] = $field . ': File wajib diupload';
            }
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => implode('<br>', $errors)
            ];
        }
        
        return [
            'success' => true,
            'files' => $uploaded_files
        ];
    }

    /**
     * Generate filename untuk upload - TIDAK DIUBAH
     */
    private function _generate_filename($prefix = 'PUBLIKASI') {
        return $prefix . '_' . date('YmdHis') . '_' . $this->mahasiswa_id . '_' . uniqid();
    }

    /**
     * ✅ REPLACE METHOD _send_notification_to_dosen() - IKUTI POLA STABLE
     */
    private function _send_notification_to_dosen($proposal) {
        try {
            // ✅ Validasi data email (sama seperti controller stable)
            if (!$proposal || empty($proposal->email_pembimbing)) {
                log_message('error', "Email dosen pembimbing tidak tersedia untuk proposal ID: {$proposal->id}");
                return false;
            }
            
            // ✅ Load email library dan initialize (sama seperti controller stable)
            $this->load->library('email');
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            $this->email->clear();
            
            // ✅ Send notification ke dosen pembimbing
            $result_dosen = $this->_kirim_email_publikasi_ke_dosen($proposal);
            
            // ✅ Send konfirmasi ke mahasiswa (seperti controller stable lainnya)
            $result_mahasiswa = $this->_kirim_email_konfirmasi_publikasi_mahasiswa($proposal);
            
            // ✅ Log hasil (seperti controller stable)
            if ($result_dosen) {
                log_message('info', "✅ Email publikasi berhasil dikirim ke dosen: {$proposal->email_pembimbing}");
            }
            
            if ($result_mahasiswa) {
                log_message('info', "✅ Email konfirmasi berhasil dikirim ke mahasiswa: {$proposal->email}");
            }
            
            // Return true jika minimal 1 email berhasil (seperti pola controller stable)
            return ($result_dosen || $result_mahasiswa);
            
        } catch (Exception $e) {
            log_message('error', "❌ Exception dalam send notification publikasi: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ Email ke dosen pembimbing - IKUTI POLA STABLE
     */
    private function _kirim_email_publikasi_ke_dosen($proposal) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            $this->email->clear();
            
            $this->email->from('stkyakobus@gmail.com', 'SIM TA STK Santo Yakobus');
            $this->email->to($proposal->email_pembimbing);
            $this->email->subject('📄 Pengajuan Publikasi Tugas Akhir - ' . $proposal->nama_mahasiswa);
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #007bff; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📄 Pengajuan Publikasi Tugas Akhir</h2>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>Pengajuan Baru</p>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$proposal->nama_pembimbing}</strong>,</p>
                    
                    <p>Mahasiswa bimbingan Anda telah mengajukan publikasi tugas akhir dengan detail sebagai berikut:</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td style='padding: 5px 0;'><strong>Nama:</strong></td><td>{$proposal->nama_mahasiswa}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>NIM:</strong></td><td>{$proposal->nim}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>Judul:</strong></td><td>{$proposal->judul}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>Tanggal Ajukan:</strong></td><td>" . date('d F Y H:i') . "</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #495057; margin-top: 0;'>📋 Tindakan Diperlukan:</h4>
                        <p style='margin: 5px 0;'>• Review kelengkapan dokumen publikasi</p>
                        <p style='margin: 5px 0;'>• Verifikasi kesesuaian dengan hasil seminar skripsi</p>
                        <p style='margin: 5px 0;'>• Berikan rekomendasi (Setujui/Revisi)</p>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('dosen/publikasi') . "' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📄 Buka Menu Publikasi
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Email ini dikirim otomatis oleh sistem untuk koordinasi proses publikasi tugas akhir.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', "Email publikasi sent to dosen: {$proposal->email_pembimbing}");
                return true;
            } else {
                log_message('error', 'Failed to send email to dosen: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email to dosen: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ Email konfirmasi ke mahasiswa - IKUTI POLA STABLE
     */
    private function _kirim_email_konfirmasi_publikasi_mahasiswa($proposal) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            $this->email->clear();
            
            $this->email->from('stkyakobus@gmail.com', 'SIM TA STK Santo Yakobus');
            $this->email->to($proposal->email);
            $this->email->subject('✅ Pengajuan Publikasi Berhasil Dikirim');
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #28a745; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Publikasi Berhasil Diajukan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$proposal->nama_mahasiswa}</strong>,</p>
                    
                    <p>Terima kasih! Pengajuan publikasi tugas akhir Anda telah berhasil dikirim kepada dosen pembimbing untuk review.</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #28a745; margin-top: 0;'>Detail Pengajuan:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td><strong>Judul:</strong></td><td>{$proposal->judul}</td></tr>
                            <tr><td><strong>Pembimbing:</strong></td><td>{$proposal->nama_pembimbing}</td></tr>
                            <tr><td><strong>Waktu Submit:</strong></td><td>" . date('d F Y H:i') . "</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span style='color: #ffc107;'>⏳ Menunggu Review Dosen</span></td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #d1ecf1; padding: 15px; border-left: 4px solid #bee5eb; margin: 15px 0;'>
                        <h4 style='color: #0c5460; margin-top: 0;'>📋 Langkah Selanjutnya:</h4>
                        <p style='margin: 5px 0; color: #0c5460;'>• Dosen pembimbing akan melakukan review</p>
                        <p style='margin: 5px 0; color: #0c5460;'>• Anda akan mendapat notifikasi hasil review</p>
                        <p style='margin: 5px 0; color: #0c5460;'>• Jika disetujui, akan diteruskan ke staf untuk validasi</p>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('mahasiswa/publikasi') . "' style='background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📊 Lihat Status Publikasi
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px;'>
                        Anda dapat memantau progress publikasi melalui dashboard mahasiswa.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', "Email konfirmasi publikasi sent to mahasiswa: {$proposal->email}");
                return true;
            } else {
                log_message('error', 'Failed to send confirmation email to mahasiswa: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending confirmation email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get publikasi timeline - TIDAK DIUBAH
     */
    private function _get_publikasi_timeline($publikasi_id) {
        // Get timeline/log untuk publikasi
        return [];
    }

    /**
     * ✅ TAMBAH: Get dosen list untuk dropdown
     */
    private function _get_dosen_list() {
        try {
            $this->db->select('id, nama, nip, email');
            $this->db->from('dosen');
            $this->db->where('level', '2'); // ✅ CONFIRMED: Dosen hanya level 2
            $this->db->where('email !=', ''); // Pastikan ada email
            $this->db->order_by('nama', 'ASC');
            
            $result = $this->db->get()->result();
            
            if (empty($result)) {
                log_message('warning', 'No dosen found for dropdown');
                return [];
            }
            
            log_message('info', 'Loaded ' . count($result) . ' dosen for dropdown');
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error loading dosen list: ' . $e->getMessage());
            return [];
        }
    }

    // =================================================================
    // ✅ PERBAIKAN 4: Reliable email notification
    // =================================================================
    
    /**
     * ✅ ENHANCED: Reliable email notification dengan data dosen dari DB
     */
    private function _send_notification_to_dosen_reliable($dosen_data, $publikasi_data) {
        try {
            // ✅ Data sudah pasti ada karena dari dropdown DB
            if (!$dosen_data || empty($dosen_data->email)) {
                log_message('error', "Invalid dosen data for email notification");
                return false;
            }
            
            $mahasiswa_email = $this->session->userdata('email');
            
            // Load email library
            $this->load->library('email');
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $result_dosen = false;
            $result_mahasiswa = false;
            
            // ✅ Send ke dosen pembimbing
            $result_dosen = $this->_kirim_email_publikasi_ke_dosen_reliable($dosen_data, $publikasi_data);
            
            // ✅ Send konfirmasi ke mahasiswa
            if ($mahasiswa_email) {
                $result_mahasiswa = $this->_kirim_email_konfirmasi_mahasiswa_reliable($dosen_data, $publikasi_data, $mahasiswa_email);
            }
            
            // Log hasil
            if ($result_dosen) {
                log_message('info', "✅ Email publikasi berhasil dikirim ke dosen: {$dosen_data->email}");
            }
            
            if ($result_mahasiswa) {
                log_message('info', "✅ Email konfirmasi berhasil dikirim ke mahasiswa: {$mahasiswa_email}");
            }
            
            // Return true jika minimal 1 email berhasil
            return ($result_dosen || $result_mahasiswa);
            
        } catch (Exception $e) {
            log_message('error', "❌ Exception dalam reliable email notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ ENHANCED: Email ke dosen dengan data reliable dari DB
     */
    private function _kirim_email_publikasi_ke_dosen_reliable($dosen_data, $publikasi_data) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            $this->email->clear();
            
            $this->email->from('stkyakobus@gmail.com', 'SIM TA STK Santo Yakobus');
            $this->email->to($dosen_data->email);
            
            // ✅ DETECT resubmit dari keterangan untuk subject line
            $is_resubmit = strpos($publikasi_data['keterangan_mahasiswa'], '[RESUBMIT]') === 0;
            $subject_prefix = $is_resubmit ? '🔄 Pengajuan Ulang' : '📄 Pengajuan';
            
            $this->email->subject($subject_prefix . ' Publikasi Tugas Akhir - ' . $publikasi_data['nama_mahasiswa']);
            
            // ✅ TAMBAH SEDIKIT INFO RESUBMIT di template TANPA MENGUBAH STRUKTUR
            $resubmit_alert = '';
            if ($is_resubmit) {
                $resubmit_alert = "
                <div style='background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px 0;'>
                    <p style='margin: 0; color: #856404; font-weight: bold;'>
                        🔄 <strong>PENGAJUAN ULANG:</strong> Mahasiswa telah melakukan perbaikan sesuai masukan sebelumnya.
                    </p>
                </div>";
            }
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #007bff; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📄 Pengajuan Publikasi Tugas Akhir</h2>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>" . ($is_resubmit ? 'Pengajuan Ulang' : 'Pengajuan Baru') . "</p>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$dosen_data->nama}</strong>,</p>
                    
                    <p>Mahasiswa bimbingan Anda telah mengajukan publikasi tugas akhir dengan detail sebagai berikut:</p>
                    
                    {$resubmit_alert}
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td style='padding: 5px 0;'><strong>Nama:</strong></td><td>{$publikasi_data['nama_mahasiswa']}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>NIM:</strong></td><td>{$publikasi_data['nim']}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>Program Studi:</strong></td><td>{$publikasi_data['program_studi']}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>Judul:</strong></td><td>{$publikasi_data['judul_skripsi_final']}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>Tanggal Ujian:</strong></td><td>{$publikasi_data['tanggal_ujian_skripsi']}</td></tr>
                            <tr><td style='padding: 5px 0;'><strong>Tanggal Ajukan:</strong></td><td>" . date('d F Y H:i') . "</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #495057; margin-top: 0;'>📋 Tindakan Diperlukan:</h4>
                        <p style='margin: 5px 0;'>• Review kelengkapan dokumen publikasi</p>
                        <p style='margin: 5px 0;'>• Verifikasi kesesuaian dengan hasil seminar skripsi</p>
                        <p style='margin: 5px 0;'>• Berikan rekomendasi (Setujui/Revisi)</p>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('dosen/publikasi') . "' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📄 Buka Menu Publikasi
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                        Email ini dikirim otomatis oleh sistem untuk koordinasi proses publikasi tugas akhir.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', "Email publikasi reliable sent to dosen: {$dosen_data->email}");
                return true;
            } else {
                log_message('error', 'Failed to send reliable email to dosen: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending reliable email to dosen: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ ENHANCED: Email konfirmasi ke mahasiswa dengan data reliable
     */
    private function _kirim_email_konfirmasi_mahasiswa_reliable($dosen_data, $publikasi_data, $mahasiswa_email) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            $this->email->clear();
            
            $this->email->from('stkyakobus@gmail.com', 'SIM TA STK Santo Yakobus');
            $this->email->to($mahasiswa_email);
            $this->email->subject('✅ Pengajuan Publikasi Berhasil Dikirim');
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #28a745; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Publikasi Berhasil Diajukan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$publikasi_data['nama_mahasiswa']}</strong>,</p>
                    
                    <p>Terima kasih! Pengajuan publikasi tugas akhir Anda telah berhasil dikirim kepada dosen pembimbing untuk review.</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #28a745; margin-top: 0;'>Detail Pengajuan:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td><strong>Judul:</strong></td><td>{$publikasi_data['judul_skripsi_final']}</td></tr>
                            <tr><td><strong>Pembimbing:</strong></td><td>{$dosen_data->nama}</td></tr>
                            <tr><td><strong>Email Dosen:</strong></td><td>{$dosen_data->email}</td></tr>
                            <tr><td><strong>Waktu Submit:</strong></td><td>" . date('d F Y H:i') . "</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span style='color: #ffc107;'>⏳ Menunggu Review Dosen</span></td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #d1ecf1; padding: 15px; border-left: 4px solid #bee5eb; margin: 15px 0;'>
                        <h4 style='color: #0c5460; margin-top: 0;'>📋 Langkah Selanjutnya:</h4>
                        <p style='margin: 5px 0; color: #0c5460;'>• Dosen pembimbing akan melakukan review</p>
                        <p style='margin: 5px 0; color: #0c5460;'>• Anda akan mendapat notifikasi hasil review</p>
                        <p style='margin: 5px 0; color: #0c5460;'>• Jika disetujui, akan diteruskan ke staf untuk validasi</p>
                    </div>
                    
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . base_url('mahasiswa/publikasi') . "' style='background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            📊 Lihat Status Publikasi
                        </a>
                    </div>
                    
                    <p style='color: #6c757d; font-size: 14px;'>
                        Anda dapat memantau progress publikasi melalui dashboard mahasiswa.
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            
            if ($this->email->send()) {
                log_message('info', "Email konfirmasi reliable sent to mahasiswa: {$mahasiswa_email}");
                return true;
            } else {
                log_message('error', 'Failed to send reliable confirmation email to mahasiswa: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending reliable confirmation email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ TAMBAH: Get dosen by ID
     */
    private function _get_dosen_by_id($dosen_id) {
        try {
            $this->db->select('id, nama, nip, email');
            $this->db->from('dosen');
            $this->db->where('id', $dosen_id);
            
            $result = $this->db->get()->row();
            
            if ($result) {
                log_message('info', "Dosen found: {$result->nama} ({$result->email})");
            } else {
                log_message('warning', "Dosen not found for ID: {$dosen_id}");
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get mahasiswa data - TIDAK DIUBAH
     */
    private function _get_mahasiswa_data() {
        // Get data mahasiswa untuk surat
        return $this->db->get_where('mahasiswa', ['id' => $this->mahasiswa_id])->row();
    }

    /**
     * FIX: Method untuk log activity
     */
    private function _log_publikasi_activity($publikasi_id, $action, $description) {
        $data = [
            'publikasi_id' => $publikasi_id,
            'user_id' => $this->mahasiswa_id,
            'user_type' => 'mahasiswa',
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert ke tabel log_publikasi
        $this->db->insert('log_publikasi', $data);
    }

    /**
     * ✅ Method konfigurasi email - SAMA SEPERTI CONTROLLER STABLE LAINNYA
     */
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

    /**
     * JavaScript untuk form publikasi - TIDAK DIUBAH
     */
    private function _get_form_script() {
        return '
        <script>
        $(document).ready(function() {
            // Validasi file size
            $("input[type=\'file\']").change(function() {
                const fileInput = this;
                const file = fileInput.files[0];
                const maxSize = fileInput.name === "file_skripsi_final" ? 5 * 1024 * 1024 : 1 * 1024 * 1024;
                
                if (file && file.size > maxSize) {
                    alert("Ukuran file terlalu besar. Maksimal " + (maxSize / 1024 / 1024) + " MB");
                    fileInput.value = "";
                }
            });
            
            // Konfirmasi submit
            $("#formPublikasi").submit(function(e) {
                if (!confirm("Yakin ingin mengajukan publikasi ini? Pastikan semua data sudah benar.")) {
                    e.preventDefault();
                }
            });
        });
        </script>';
    }
}
?>