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
     * Show form pengajuan dengan template wrapper - TIDAK DIUBAH
     */
    private function _show_form_pengajuan($proposal) {
        $view_data = [
            'title' => 'Ajukan Publikasi Tugas Akhir',
            'proposal' => $proposal,
            'action' => 'ajukan'
        ];
        
        // Gunakan template wrapper
        $this->_load_template('mahasiswa/publikasi/form', $view_data, 'Ajukan Publikasi Tugas Akhir', $this->_get_form_script());
    }

    /**
     * Show form edit dengan template wrapper - TIDAK DIUBAH
     */
    private function _show_form_edit($publikasi) {
        $view_data = [
            'title' => 'Edit Publikasi Tugas Akhir',
            'publikasi' => $publikasi,
            'action' => 'edit'
        ];
        
        // Gunakan template wrapper
        $this->_load_template('mahasiswa/publikasi/form', $view_data, 'Edit Publikasi Tugas Akhir', $this->_get_form_script());
    }

    // =================================================================
    // ✅ PERBAIKAN UTAMA: 2 METHOD PROCESSING YANG DITAMBAH SUBMIT_TYPE HANDLING
    // =================================================================

    /**
     * ✅ PERBAIKAN UTAMA: Process form publikasi - TAMBAH SUBMIT_TYPE HANDLING
     * Fixed: Tambah logic untuk langsung submit atau simpan draft
     */
    private function _process_form_publikasi($proposal) {
        // Set validation rules - TIDAK DIUBAH
        $this->form_validation->set_rules('judul_skripsi_final', 'Judul Skripsi Final', 'required|trim');
        $this->form_validation->set_rules('tanggal_ujian_skripsi', 'Tanggal Ujian Skripsi', 'required');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'trim');
        
        if ($this->form_validation->run() === FALSE) {
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
        
        // ✅ PERBAIKAN UTAMA: Tentukan tipe submit berdasarkan tombol yang diklik
        $submit_type = $this->input->post('submit_type'); // 'draft' atau 'submit'
        $save_draft = $this->input->post('save_draft'); // Backward compatibility
        
        // Determine status - LOGIC BARU
        if ($save_draft || $submit_type === 'draft') {
            $status = 'draft';
            $workflow_step = 'Step 2 (Draft)';
        } else {
            $status = 'submitted'; // Langsung submit ke dosen
            $workflow_step = 'Step 4-6 (Review Dosen)';
        }
        
        // Prepare data untuk insert - MAPPING TIDAK DIUBAH
        $data = [
            'proposal_mahasiswa_id' => $proposal->id,
            'mahasiswa_id' => $this->mahasiswa_id,
            'dosen_pembimbing_id' => $proposal->dosen_id,
            'nim' => $proposal->nim,
            'nama_mahasiswa' => $proposal->nama_mahasiswa,
            'program_studi' => $proposal->nama_prodi,
            'judul_skripsi_final' => $this->input->post('judul_skripsi_final'),
            'nama_dosen_pembimbing' => isset($proposal->nama_pembimbing) ? $proposal->nama_pembimbing : 'Belum ditetapkan',
            'tanggal_ujian_skripsi' => $this->input->post('tanggal_ujian_skripsi'),
            'file_surat_revisi' => $upload_result['files']['file_surat_revisi'],
            'file_skripsi_final' => $upload_result['files']['file_skripsi_final'],
            'file_surat_perpustakaan' => $upload_result['files']['file_surat_perpustakaan'],
            'link_repository' => $this->input->post('link_repository'),
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
            'status' => $status, // ✅ DYNAMIC STATUS
            'tanggal_pengajuan' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert ke database menggunakan model - TIDAK DIUBAH
        $result = $this->publikasi->create($data);
        
        if ($result['success']) {
            // ✅ PERBAIKAN: Handle berdasarkan status
            if ($status === 'submitted') {
                // LANGSUNG SUBMIT - STEP 3 SELESAI
                $this->session->set_flashdata('success', 
                    '✅ Pengajuan berhasil dikirim ke dosen pembimbing! ' .
                    'Status: Step 4-6 (Review Dosen). ' .
                    'Dosen akan mendapat notifikasi untuk review.'
                );
                
                // ✅ Send notification dengan pola stable (tidak break workflow jika gagal)
                try {
                    $email_sent = $this->_send_notification_to_dosen($proposal);
                    if ($email_sent) {
                        log_message('info', "✅ Email notification berhasil untuk publikasi ID: {$result['id']}");
                    } else {
                        log_message('warning', "⚠️ Email notification gagal tapi workflow tetap lanjut untuk publikasi ID: {$result['id']}");
                        
                        // Tambahkan info ke session bahwa email mungkin bermasalah
                        $current_success = $this->session->flashdata('success');
                        $this->session->set_flashdata('success', 
                            $current_success . ' (Catatan: Notifikasi email mungkin bermasalah, silakan hubungi dosen pembimbing secara manual.)'
                        );
                    }
                } catch (Exception $e) {
                    log_message('error', "❌ Exception saat kirim email untuk publikasi ID {$result['id']}: " . $e->getMessage());
                    
                    // Tetap lanjutkan workflow meski email gagal
                    $current_success = $this->session->flashdata('success');
                    $this->session->set_flashdata('success', 
                        $current_success . ' (Catatan: Sistem email bermasalah, silakan hubungi dosen pembimbing secara manual.)'
                    );
                }
                
            } else {
                // SIMPAN DRAFT - MASIH STEP 2
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
     * ✅ PERBAIKAN UTAMA: Process update publikasi - TAMBAH SUBMIT_TYPE HANDLING
     * Fixed: Tambah logic untuk submit dari mode edit
     */
    private function _process_update($publikasi) {
        // Set validation rules - TIDAK DIUBAH
        $this->form_validation->set_rules('judul_skripsi_final', 'Judul Skripsi Final', 'required|trim');
        $this->form_validation->set_rules('tanggal_ujian_skripsi', 'Tanggal Ujian Skripsi', 'required');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'trim');
        
        if ($this->form_validation->run() === FALSE) {
            $this->_show_form_edit($publikasi);
            return;
        }
        
        // Handle file uploads (optional untuk update) - TIDAK DIUBAH
        $upload_result = $this->_handle_file_uploads(false);
        if (!$upload_result['success']) {
            $this->session->set_flashdata('error', $upload_result['message']);
            $this->_show_form_edit($publikasi);
            return;
        }
        
        // ✅ PERBAIKAN UTAMA: Tentukan tipe submit
        $submit_type = $this->input->post('submit_type'); // 'update' atau 'submit'
        
        // Prepare data untuk update - MAPPING TIDAK DIUBAH
        $data = [
            'judul_skripsi_final' => $this->input->post('judul_skripsi_final'),
            'tanggal_ujian_skripsi' => $this->input->post('tanggal_ujian_skripsi'),
            'link_repository' => $this->input->post('link_repository'),
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // ✅ PERBAIKAN: Update status jika submit ke dosen
        if ($submit_type === 'submit' && $publikasi->status === 'draft') {
            $data['status'] = 'submitted';
            $data['tanggal_pengajuan'] = date('Y-m-d H:i:s');
        }
        
        // Update file jika ada yang diupload - TIDAK DIUBAH
        if (!empty($upload_result['files'])) {
            $data = array_merge($data, $upload_result['files']);
        }
        
        // Update database menggunakan model - TIDAK DIUBAH
        $result = $this->publikasi->update($publikasi->id, $data, $this->mahasiswa_id);
        
        if ($result['success']) {
            // ✅ PERBAIKAN: Handle berdasarkan tipe submit
            if ($submit_type === 'submit' && $publikasi->status === 'draft') {
                // SUBMIT KE DOSEN SETELAH UPDATE
                $this->session->set_flashdata('success', 
                    '✅ Data berhasil diperbarui dan dikirim ke dosen pembimbing! ' .
                    'Status: Step 4-6 (Review Dosen). ' .
                    'Dosen akan mendapat notifikasi untuk review.'
                );
                
                // ✅ Send notification dengan pola stable
                try {
                    $proposal = $this->_get_proposal_by_id($publikasi->proposal_mahasiswa_id);
                    if ($proposal) {
                        $email_sent = $this->_send_notification_to_dosen($proposal);
                        if ($email_sent) {
                            log_message('info', "✅ Email notification berhasil untuk updated publikasi ID: {$publikasi->id}");
                        } else {
                            log_message('warning', "⚠️ Email notification gagal untuk updated publikasi ID: {$publikasi->id}");
                            
                            $current_success = $this->session->flashdata('success');
                            $this->session->set_flashdata('success', 
                                $current_success . ' (Catatan: Notifikasi email mungkin bermasalah, silakan hubungi dosen pembimbing secara manual.)'
                            );
                        }
                    } else {
                        log_message('error', "❌ Data proposal tidak ditemukan untuk publikasi ID: {$publikasi->id}");
                    }
                } catch (Exception $e) {
                    log_message('error', "❌ Exception saat kirim email untuk updated publikasi ID {$publikasi->id}: " . $e->getMessage());
                    
                    $current_success = $this->session->flashdata('success');
                    $this->session->set_flashdata('success', 
                        $current_success . ' (Catatan: Sistem email bermasalah, silakan hubungi dosen pembimbing secara manual.)'
                    );
                }
            } else {
                // UPDATE BIASA
                $this->session->set_flashdata('success', '💾 Data publikasi berhasil diperbarui.');
            }
            
            redirect('mahasiswa/publikasi/tracking/' . $publikasi->id);
        } else {
            $this->session->set_flashdata('error', $result['message']);
            $this->_show_form_edit($publikasi);
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