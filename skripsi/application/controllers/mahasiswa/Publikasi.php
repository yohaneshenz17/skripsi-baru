<?php
/**
 * =====================================================
 * CONTROLLER PHASE 6 - PUBLIKASI TUGAS AKHIR LENGKAP
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * =====================================================
 * 
 * 1. MAHASISWA CONTROLLER - Pengajuan publikasi
 * 2. DOSEN CONTROLLER - Review dan approve
 * 3. STAF CONTROLLER - Input repository dan validasi
 * 4. KAPRODI CONTROLLER - Monitoring
 * 5. ADMIN CONTROLLER - Management dan override
 */

// =====================================================
// 1. MAHASISWA CONTROLLER
// File: application/controllers/mahasiswa/Publikasi.php
// =====================================================

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Publikasi untuk Mahasiswa
 * Handle pengajuan publikasi tugas akhir
 */
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
        
        if($this->session->userdata('level') != 'mahasiswa') {
            show_error('Akses ditolak. Halaman khusus mahasiswa.', 403);
        }
        
        $this->mahasiswa_id = $this->session->userdata('user_id');
    }

    /**
     * Dashboard publikasi mahasiswa
     */
    public function index() {
        // Get proposal mahasiswa yang eligible (16+ jurnal tervalidasi)
        $proposal = $this->_get_proposal_eligible();
        
        $data = [
            'title' => 'Publikasi Tugas Akhir',
            'proposal' => $proposal,
            'publikasi' => null,
            'syarat_status' => null,
            'jurnal_count' => 0,
            'eligible' => false
        ];
        
        if ($proposal) {
            // Cek syarat publikasi
            $data['syarat_status'] = $this->_check_syarat_publikasi($proposal->id);
            $data['jurnal_count'] = $this->_count_jurnal_tervalidasi($proposal->id);
            $data['eligible'] = ($data['syarat_status'] === 'ELIGIBLE');
            
            // Get existing publikasi jika ada
            $data['publikasi'] = $this->publikasi->get_by_proposal($proposal->id);
        }
        
        $this->load->view('mahasiswa/publikasi/index', $data);
    }

    /**
     * Perbaikan method ajukan - remove validasi seminar skripsi
     */
    public function ajukan($proposal_id = null) {
        if (!$proposal_id) {
            $proposal = $this->_get_proposal_eligible();
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Tidak ada proposal yang eligible.');
                redirect('mahasiswa/publikasi');
            }
            $proposal_id = $proposal->id;
        }
        
        $proposal = $this->_get_proposal_by_id($proposal_id);
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan.');
            redirect('mahasiswa/publikasi');
        }
        
        // Cek syarat dengan detail
        $syarat_check = $this->_check_syarat_publikasi($proposal_id);
        if ($syarat_check['status'] !== 'ELIGIBLE') {
            $this->session->set_flashdata('error', $syarat_check['message']);
            redirect('mahasiswa/publikasi');
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_form_publikasi($proposal);
        } else {
            $this->_show_form_pengajuan($proposal);
        }
    }

    /**
     * Edit pengajuan publikasi
     */
    public function edit($publikasi_id) {
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->mahasiswa_id != $this->mahasiswa_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('mahasiswa/publikasi');
        }
        
        // Hanya bisa edit jika status draft atau rejected
        if (!in_array($publikasi->status, ['draft', 'rejected'])) {
            $this->session->set_flashdata('error', 'Tidak dapat mengedit. Status: ' . $publikasi->status);
            redirect('mahasiswa/publikasi');
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_update($publikasi);
        } else {
            $this->_show_form_edit($publikasi);
        }
    }

    /**
     * Submit pengajuan ke dosen pembimbing
     */
    public function submit($publikasi_id) {
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->mahasiswa_id != $this->mahasiswa_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('mahasiswa/publikasi');
        }
        
        if ($publikasi->status !== 'draft') {
            $this->session->set_flashdata('error', 'Pengajuan sudah disubmit sebelumnya.');
            redirect('mahasiswa/publikasi');
        }
        
        // Update status ke submitted
        $result = $this->publikasi->submit_pengajuan($publikasi_id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Pengajuan berhasil disubmit ke dosen pembimbing.');
            
            // Send notification ke dosen pembimbing
            $this->_send_notification_to_dosen($publikasi);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('mahasiswa/publikasi');
    }

    /**
     * Method untuk download surat keterangan publikasi
     */
    public function download_surat($publikasi_id) {
        $publikasi = $this->publikasi->get_by_id($publikasi_id, $this->mahasiswa_id);
        
        if (!$publikasi || $publikasi->status !== 'completed') {
            $this->session->set_flashdata('error', 'Surat belum tersedia.');
            redirect('mahasiswa/publikasi');
        }
        
        // Generate PDF surat keterangan
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
    // PRIVATE METHODS
    // =================================================================

    /**
     * Get proposal mahasiswa yang eligible untuk publikasi
     * Syarat: Hanya minimal 16 jurnal bimbingan tervalidasi
     */
    private function _get_proposal_eligible() {
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
        $this->db->where('pm.status', '1'); // Proposal sudah disetujui
        $this->db->group_by('pm.id');
        $this->db->having('COUNT(jb.id) >= 16'); // Minimal 16 jurnal tervalidasi
        $this->db->order_by('pm.id', 'DESC');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    /**
     * Get proposal by ID dengan validasi ownership
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
     * Perbaikan method _check_syarat_publikasi
     * Cukup validasi minimal 16 jurnal bimbingan tervalidasi
     */
    private function _check_syarat_publikasi($proposal_id) {
        // Cek jurnal bimbingan minimal 16 tervalidasi
        $jurnal_count = $this->_count_jurnal_tervalidasi($proposal_id);
        
        // Return status dengan detail
        if ($jurnal_count >= 16) {
            return [
                'status' => 'ELIGIBLE',
                'jurnal_count' => $jurnal_count,
                'message' => 'Anda memenuhi syarat untuk mengajukan publikasi'
            ];
        }
        
        return [
            'status' => 'NOT_ELIGIBLE', 
            'jurnal_count' => $jurnal_count,
            'missing' => ["Jurnal bimbingan: {$jurnal_count}/16"],
            'message' => "Syarat belum terpenuhi: Jurnal bimbingan masih {$jurnal_count}/16"
        ];
    }

    /**
     * Hitung jurnal bimbingan tervalidasi
     */
    private function _count_jurnal_tervalidasi($proposal_id) {
        return $this->db->where('proposal_id', $proposal_id)
                       ->where('status_validasi', '1')
                       ->count_all_results('jurnal_bimbingan');
    }

    /**
     * Process pengajuan baru
     */
    private function _process_pengajuan($proposal) {
        $this->form_validation->set_rules('judul_skripsi_final', 'Judul Skripsi Final', 'required|trim');
        $this->form_validation->set_rules('tanggal_ujian_skripsi', 'Tanggal Ujian Skripsi', 'required');
        $this->form_validation->set_rules('keterangan_mahasiswa', 'Keterangan', 'trim');
        
        if (!$this->form_validation->run()) {
            $this->_show_form_pengajuan($proposal);
            return;
        }
        
        // Get tanggal ujian skripsi (bisa dari input manual atau default hari ini)
        $tanggal_ujian = $this->input->post('tanggal_ujian_skripsi') ?: date('Y-m-d');
        
        $data = [
            'proposal_mahasiswa_id' => $proposal->id,
            'mahasiswa_id' => $this->mahasiswa_id,
            'nama_mahasiswa' => strtoupper($proposal->nama_mahasiswa),
            'nim' => $proposal->nim,
            'program_studi' => $proposal->nama_prodi,
            'judul_skripsi_final' => $this->input->post('judul_skripsi_final'),
            'dosen_pembimbing_id' => $proposal->dosen_id,
            'nama_dosen_pembimbing' => $proposal->nama_pembimbing,
            'tanggal_ujian_skripsi' => $tanggal_ujian,
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa'),
            'status' => 'draft',
            'status_pembimbing' => 'pending',
            'status_staf' => 'pending'
        ];
        
        // Handle file uploads
        $upload_results = $this->_handle_file_uploads();
        if (!$upload_results['success']) {
            $this->session->set_flashdata('error', $upload_results['message']);
            $this->_show_form_pengajuan($proposal);
            return;
        }
        
        $data = array_merge($data, $upload_results['files']);
        
        $result = $this->publikasi->create($data);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Pengajuan publikasi berhasil disimpan sebagai draft.');
            redirect('mahasiswa/publikasi');
        } else {
            $this->session->set_flashdata('error', $result['message']);
            $this->_show_form_pengajuan($proposal);
        }
    }

    /**
     * Process update pengajuan
     */
    private function _process_update($publikasi) {
        $this->form_validation->set_rules('judul_skripsi_final', 'Judul Skripsi Final', 'required|trim');
        $this->form_validation->set_rules('tanggal_ujian_skripsi', 'Tanggal Ujian Skripsi', 'required');
        
        if (!$this->form_validation->run()) {
            $this->_show_form_edit($publikasi);
            return;
        }
        
        $data = [
            'judul_skripsi_final' => $this->input->post('judul_skripsi_final'),
            'tanggal_ujian_skripsi' => $this->input->post('tanggal_ujian_skripsi'),
            'keterangan_mahasiswa' => $this->input->post('keterangan_mahasiswa')
        ];
        
        // Handle file uploads if any
        $upload_results = $this->_handle_file_uploads(false); // false = not required
        if (!$upload_results['success']) {
            $this->session->set_flashdata('error', $upload_results['message']);
            $this->_show_form_edit($publikasi);
            return;
        }
        
        if (!empty($upload_results['files'])) {
            $data = array_merge($data, $upload_results['files']);
        }
        
        $result = $this->publikasi->update($publikasi->id, $data, $this->mahasiswa_id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Data publikasi berhasil diupdate.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('mahasiswa/publikasi');
    }

    /**
     * Handle file uploads
     */
    private function _handle_file_uploads($required = true) {
        $files = [
            'file_surat_revisi' => [
                'field' => 'surat_revisi',
                'max_size' => 1024, // 1MB
                'required' => $required
            ],
            'file_skripsi_final' => [
                'field' => 'skripsi_final', 
                'max_size' => 5120, // 5MB
                'required' => $required
            ],
            'file_surat_perpustakaan' => [
                'field' => 'surat_perpustakaan',
                'max_size' => 1024, // 1MB
                'required' => $required
            ]
        ];
        
        $uploaded_files = [];
        
        foreach ($files as $db_field => $config) {
            if (!$config['required'] && empty($_FILES[$config['field']]['name'])) {
                continue;
            }
            
            $upload_config = [
                'upload_path' => './uploads/publikasi/' . str_replace('file_', '', $db_field) . '/',
                'allowed_types' => 'pdf',
                'max_size' => $config['max_size'],
                'file_name' => $this->_generate_filename($db_field)
            ];
            
            // Create directory jika belum ada
            if (!is_dir($upload_config['upload_path'])) {
                mkdir($upload_config['upload_path'], 0755, true);
            }
            
            $this->upload->initialize($upload_config);
            
            if (!$this->upload->do_upload($config['field'])) {
                $error = $this->upload->display_errors('', '');
                return [
                    'success' => false,
                    'message' => "Upload {$config['field']} gagal: {$error}"
                ];
            }
            
            $uploaded_files[$db_field] = $this->upload->data('file_name');
        }
        
        return [
            'success' => true,
            'files' => $uploaded_files
        ];
    }

    /**
     * Generate unique filename
     */
    private function _generate_filename($type) {
        return 'PUBLIKASI_' . date('YmdHis') . '_' . $this->mahasiswa_id . '_' . uniqid();
    }

    /**
     * Show form pengajuan
     */
    private function _show_form_pengajuan($proposal) {
        $data = [
            'title' => 'Ajukan Publikasi Tugas Akhir',
            'proposal' => $proposal,
            'action' => 'ajukan'
        ];
        $this->load->view('mahasiswa/publikasi/form', $data);
    }

    /**
     * Show form edit
     */
    private function _show_form_edit($publikasi) {
        $data = [
            'title' => 'Edit Publikasi Tugas Akhir',
            'publikasi' => $publikasi,
            'action' => 'edit'
        ];
        $this->load->view('mahasiswa/publikasi/form', $data);
    }

    /**
     * Send notification to dosen pembimbing
     */
    private function _send_notification_to_dosen($publikasi) {
        // TODO: Implement notification system
        log_message('info', "Notification sent to dosen_id: {$publikasi->dosen_pembimbing_id} for publikasi_id: {$publikasi->id}");
    }

    /**
     * Generate surat keterangan publikasi
     */
    private function _generate_surat_publikasi($publikasi) {
        // TODO: Implement PDF generation
        echo "Generate surat publikasi untuk: " . $publikasi->nama_mahasiswa;
    }
    
    /**
     * Perbaikan template loading sesuai pattern project
     */
    private function _load_view($view_file, $data) {
        $template_data = [
            'title' => isset($data['title']) ? $data['title'] : 'Publikasi Tugas Akhir',
            'content' => $this->load->view($view_file, $data, TRUE),
            'script' => isset($data['script']) ? $data['script'] : ''
        ];
        
        $this->load->view('template/mahasiswa', $template_data);
    }
}