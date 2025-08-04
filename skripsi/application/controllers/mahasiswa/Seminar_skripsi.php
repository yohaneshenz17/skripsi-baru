<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller Mahasiswa - ENHANCED VERSION
 * 
 * EXISTING FEATURES (UNCHANGED):
 * - ✅ Basic eligibility check (jurnal bimbingan + penelitian)
 * - ✅ Submit seminar skripsi dengan file upload
 * - ✅ View status dan progress
 * - ✅ Resubmit jika ditolak
 * - ✅ Notification ke dosen
 * - ✅ Robust error handling
 * 
 * NEW FEATURES (ADDED):
 * - ✅ Upload surat keterangan penelitian
 * - ✅ Input judul skripsi baru (bisa berbeda dari proposal)
 * - ✅ Download untuk 2 jenis file
 * - ✅ Enhanced file handling
 * 
 * File: application/controllers/mahasiswa/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     4.1 (Enhanced - Stable + New Features)
 */

class Seminar_skripsi extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // Load core libraries
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'form']);

        // Check authentication - Level 3 = Mahasiswa
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') !== '3') {
            redirect('auth/login');
            return;
        }
        
        // Load additional libraries
        $this->load->library(['form_validation', 'upload', 'email']);
        $this->load->helper(['file', 'security']);
    }

    /**
     * ✅ ENHANCED: Index - Dashboard seminar skripsi (stable method, enhanced data)
     */
    public function index()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Initialize safe default data
        $data = [
            'page_title' => 'Seminar Skripsi',
            'has_existing' => false,
            'show_form' => false,
            'show_status' => false,
            'error_message' => null
        ];
        
        try {
            // Step 1: Check existing seminar (UNCHANGED query)
            $existing_seminar = $this->_get_existing_seminar($mahasiswa_id);
            
            if ($existing_seminar) {
                // Ada seminar existing - show status
                $data = array_merge($data, [
                    'has_existing' => true,
                    'show_status' => true,
                    'seminar' => $existing_seminar,
                    'status_info' => $this->_get_status_info($existing_seminar),
                    'can_resubmit' => ($existing_seminar->status == 'rejected')
                ]);
            } else {
                // No existing seminar - check eligibility (UNCHANGED logic)
                $eligibility = $this->_check_eligibility($mahasiswa_id);
                
                if ($eligibility['can_submit']) {
                    // Eligible - show form
                    $data = array_merge($data, [
                        'show_form' => true,
                        'proposal' => $eligibility['proposal'],
                        'requirements' => $eligibility['requirements']
                    ]);
                } else {
                    // Not eligible - show requirements
                    $data = array_merge($data, [
                        'show_form' => false,
                        'requirements' => $eligibility['requirements'],
                        'error_message' => 'Belum memenuhi syarat: ' . implode(', ', $eligibility['missing'])
                    ]);
                }
            }
            
        } catch (Exception $e) {
            log_message('error', 'Seminar skripsi index error: ' . $e->getMessage());
            $data['error_message'] = 'Terjadi kesalahan sistem. Silakan refresh halaman.';
        }
        
        // Load view (UNCHANGED)
        $this->load->view('template/mahasiswa', [
            'title' => 'Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/index', $data, TRUE)
        ]);
    }

    /**
     * ✅ ENHANCED: Submit pengajuan seminar skripsi (stable logic + new features)
     */
    public function submit()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        if ($this->input->method() !== 'post') {
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        // ENHANCED: Validation (added judul_skripsi)
        $this->form_validation->set_rules('proposal_id', 'Proposal ID', 'required|numeric');
        $this->form_validation->set_rules('keterangan', 'Keterangan', 'max_length[500]'); // Made optional
        $this->form_validation->set_rules('judul_skripsi', 'Judul Skripsi', 'required|min_length[10]|max_length[250]'); // NEW
        
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        try {
            $proposal_id = $this->input->post('proposal_id');
            
            // UNCHANGED: Validate proposal ownership
            if (!$this->_validate_proposal_ownership($proposal_id, $mahasiswa_id)) {
                $this->session->set_flashdata('error', 'Proposal tidak valid!');
                redirect('mahasiswa/seminar_skripsi');
                return;
            }
            
            // UNCHANGED: Check if already submitted
            if ($this->_has_existing_seminar($mahasiswa_id)) {
                $this->session->set_flashdata('error', 'Anda sudah mengajukan seminar skripsi!');
                redirect('mahasiswa/seminar_skripsi');
                return;
            }
            
            // ENHANCED: Handle file uploads (now supports 2 files)
            $upload_result = $this->_handle_enhanced_file_upload();
            if (!$upload_result['success']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('mahasiswa/seminar_skripsi');
                return;
            }
            
            // ENHANCED: Insert seminar data (added new fields)
            $seminar_data = [
                'proposal_id' => $proposal_id,
                'mahasiswa_id' => $mahasiswa_id,
                'judul_skripsi' => trim($this->input->post('judul_skripsi')), // NEW FIELD
                'keterangan_mahasiswa' => trim($this->input->post('keterangan')) ?: null,
                'file_skripsi' => $upload_result['files']['file_skripsi'],
                'surat_keterangan_penelitian' => $upload_result['files']['surat_penelitian'], // NEW FIELD
                'status' => 'submitted',
                'status_pembimbing' => 'pending',
                'status_kaprodi' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // UNCHANGED: Database transaction
            $this->db->trans_start();
            $this->db->insert('seminar_skripsi_mahasiswa', $seminar_data);
            $seminar_id = $this->db->insert_id();
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE || !$seminar_id) {
                throw new Exception('Gagal menyimpan data seminar');
            }
            
            // UNCHANGED: Send notification
            $this->_send_notification($proposal_id, $seminar_id);
            
            $this->session->set_flashdata('success', 'Pengajuan seminar skripsi berhasil dikirim!');
            
        } catch (Exception $e) {
            log_message('error', 'Submit seminar error: ' . $e->getMessage());
            
            // NEW: Cleanup uploaded files on error
            if (isset($upload_result['files'])) {
                $this->_cleanup_uploaded_files($upload_result['files']);
            }
            
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }

    /**
     * ✅ ENHANCED: Resubmit pengajuan yang ditolak (stable logic + new features)
     */
    public function resubmit($seminar_id = null)
    {
        if (!$seminar_id) {
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // UNCHANGED: Get seminar data
        $seminar = $this->_get_seminar_by_id($seminar_id, $mahasiswa_id);
        if (!$seminar || $seminar->status !== 'rejected') {
            $this->session->set_flashdata('error', 'Data seminar tidak valid untuk resubmit!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        if ($this->input->method() === 'post') {
            $this->_handle_enhanced_resubmit($seminar_id, $mahasiswa_id); // ENHANCED
        } else {
            $this->_show_resubmit_form($seminar); // UNCHANGED
        }
    }

    /**
     * ✅ ENHANCED: Download file (now supports multiple file types)
     */
    public function download_file($seminar_id = null, $type = 'skripsi')
    {
        if (!$seminar_id) {
            show_404();
            return;
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        $seminar = $this->_get_seminar_by_id($seminar_id, $mahasiswa_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        // ENHANCED: Support multiple file types
        $file_info = $this->_get_file_info($seminar, $type);
        
        if (!$file_info['filename'] || !file_exists($file_info['path'])) {
            $this->session->set_flashdata('error', 'File tidak ditemukan!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        $this->load->helper('download');
        force_download($file_info['download_name'], file_get_contents($file_info['path']));
    }

    // =================================================================
    // EXISTING PRIVATE METHODS (UNCHANGED - STABLE)
    // =================================================================

    /**
     * Get existing seminar (UNCHANGED - stable query)
     */
    private function _get_existing_seminar($mahasiswa_id)
    {
        try {
            $this->db->select('ssm.*, pm.judul as proposal_judul');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('ssm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('ssm.id', 'DESC');
            $this->db->limit(1);
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Get existing seminar error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check eligibility (UNCHANGED - stable logic)
     */
    private function _check_eligibility($mahasiswa_id)
    {
        $result = [
            'can_submit' => false,
            'proposal' => null,
            'requirements' => [],
            'missing' => []
        ];
        
        try {
            // Get proposal (UNCHANGED query)
            $proposal = $this->db->select('id, judul, workflow_status')
                                ->from('proposal_mahasiswa')
                                ->where('mahasiswa_id', $mahasiswa_id)
                                ->where_in('workflow_status', ['penelitian', 'seminar_skripsi'])
                                ->get()->row();
            
            if (!$proposal) {
                $result['missing'][] = 'Tidak ada proposal dalam fase penelitian';
                return $result;
            }
            
            $result['proposal'] = $proposal;
            
            // Requirement 1: Jurnal bimbingan (UNCHANGED logic)
            $jurnal_count = $this->db->where('proposal_id', $proposal->id)
                                   ->where('status_validasi', '1')
                                   ->count_all_results('jurnal_bimbingan');
            
            $result['requirements']['jurnal'] = [
                'name' => 'Jurnal Bimbingan Tervalidasi',
                'current' => $jurnal_count,
                'required' => 14,
                'met' => $jurnal_count >= 14
            ];
            
            if ($jurnal_count < 14) {
                $result['missing'][] = 'Kurang ' . (14 - $jurnal_count) . ' jurnal bimbingan';
            }
            
            // Requirement 2: Penelitian (UNCHANGED logic)
            $penelitian_exists = false;
            
            // Check permohonan_izin_penelitian (safe query)
            if ($this->db->table_exists('permohonan_izin_penelitian')) {
                $penelitian_count = $this->db->where('proposal_mahasiswa_id', $proposal->id)
                                           ->count_all_results('permohonan_izin_penelitian');
                $penelitian_exists = ($penelitian_count > 0);
            }
            
            $result['requirements']['penelitian'] = [
                'name' => 'Izin Penelitian',
                'current' => $penelitian_exists ? 1 : 0,
                'required' => 1,
                'met' => $penelitian_exists
            ];
            
            if (!$penelitian_exists) {
                $result['missing'][] = 'Belum mengajukan izin penelitian';
            }
            
            $result['can_submit'] = empty($result['missing']);
            
        } catch (Exception $e) {
            log_message('error', 'Check eligibility error: ' . $e->getMessage());
            $result['missing'][] = 'Error sistem';
        }
        
        return $result;
    }

    /**
     * Get status info for display (UNCHANGED)
     */
    private function _get_status_info($seminar)
    {
        $status_map = [
            'submitted' => [
                'text' => 'Menunggu Review Dosen Pembimbing',
                'class' => 'warning',
                'progress' => 25
            ],
            'review_pembimbing' => [
                'text' => 'Sedang Direview Dosen Pembimbing', 
                'class' => 'info',
                'progress' => 40
            ],
            'review_kaprodi' => [
                'text' => 'Menunggu Validasi Kaprodi',
                'class' => 'info', 
                'progress' => 60
            ],
            'approved' => [
                'text' => 'Disetujui - Menunggu Penjadwalan',
                'class' => 'success',
                'progress' => 80
            ],
            'scheduled' => [
                'text' => 'Terjadwal - Menunggu Pelaksanaan',
                'class' => 'primary',
                'progress' => 90
            ],
            'completed' => [
                'text' => $this->_get_completed_status_text($seminar),
                'class' => $this->_get_completed_status_class($seminar),
                'progress' => $this->_get_completed_progress($seminar)
            ],
            'rejected' => [
                'text' => 'Ditolak - Perlu Perbaikan',
                'class' => 'danger',
                'progress' => 15
            ]
        ];
        
        return $status_map[$seminar->status] ?? [
            'text' => 'Status: ' . $seminar->status,
            'class' => 'secondary',
            'progress' => 10
        ];
    }

    /**
     * Validate proposal ownership (UNCHANGED)
     */
    private function _validate_proposal_ownership($proposal_id, $mahasiswa_id)
    {
        try {
            $count = $this->db->where('id', $proposal_id)
                            ->where('mahasiswa_id', $mahasiswa_id)
                            ->count_all_results('proposal_mahasiswa');
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if has existing seminar (UNCHANGED)
     */
    private function _has_existing_seminar($mahasiswa_id)
    {
        try {
            $count = $this->db->where('mahasiswa_id', $mahasiswa_id)
                            ->count_all_results('seminar_skripsi_mahasiswa');
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }

/**
 * ✅ FIXED: Get seminar by ID with ownership validation + complete data
 */
private function _get_seminar_by_id($seminar_id, $mahasiswa_id)
{
    try {
        // ✅ FIXED: Tambahkan JOIN dengan mahasiswa, dosen, dan prodi
        $this->db->select('
            ssm.*, 
            pm.judul as proposal_judul,
            pm.judul as judul_skripsi,
            m.nim, 
            m.nama as nama_mahasiswa, 
            m.email as email_mahasiswa,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing,
            d.email as email_pembimbing,
            ssm.tanggal_seminar,
            ssm.jam_seminar,
            ssm.tempat_seminar
        ');
        $this->db->from('seminar_skripsi_mahasiswa ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id'); // <- TAMBAHAN
        $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left'); // <- TAMBAHAN
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left'); // <- TAMBAHAN
        $this->db->where('ssm.id', $seminar_id);
        $this->db->where('ssm.mahasiswa_id', $mahasiswa_id);
        
        $result = $this->db->get()->row();
        
        // Debug log untuk memastikan data lengkap
        if ($result) {
            log_message('debug', 'Seminar data fields: ' . implode(', ', array_keys((array)$result)));
            log_message('debug', 'nama_mahasiswa: ' . ($result->nama_mahasiswa ?? 'NULL'));
            log_message('debug', 'nim: ' . ($result->nim ?? 'NULL'));
            log_message('debug', 'nama_pembimbing: ' . ($result->nama_pembimbing ?? 'NULL'));
        }
        
        return $result;
    } catch (Exception $e) {
        log_message('error', 'Get seminar by ID error: ' . $e->getMessage());
        return null;
    }
}

    /**
     * Show resubmit form (UNCHANGED)
     */
    private function _show_resubmit_form($seminar)
    {
        $data = [
            'seminar' => $seminar,
            'rejection_reason' => $seminar->komentar_pembimbing ?: $seminar->komentar_kaprodi
        ];
        
        $this->load->view('template/mahasiswa', [
            'title' => 'Ajukan Ulang Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/resubmit', $data, TRUE)
        ]);
    }

    /**
     * Send notification (UNCHANGED - stable email logic)
     */
    private function _send_notification($proposal_id, $seminar_id)
    {
        try {
            // Get data (UNCHANGED query)
            $data = $this->db->select('
                    pm.judul,
                    m.nama as nama_mahasiswa,
                    m.nim,
                    d.email as email_pembimbing,
                    d.nama as nama_pembimbing
                ')
                ->from('proposal_mahasiswa pm')
                ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                ->join('dosen d', 'pm.dosen_id = d.id')
                ->where('pm.id', $proposal_id)
                ->get()->row();
                
            if (!$data || !$data->email_pembimbing) {
                return false;
            }
            
            // Email config (UNCHANGED)
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
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM TA STK Santo Yakobus');
            $this->email->to($data->email_pembimbing);
            $this->email->subject('📋 Pengajuan Seminar Skripsi - ' . $data->nama_mahasiswa);
            
            $message = "
                <h3>Pengajuan Seminar Skripsi</h3>
                <p>Mahasiswa berikut telah mengajukan seminar skripsi:</p>
                <ul>
                    <li><strong>Nama:</strong> {$data->nama_mahasiswa}</li>
                    <li><strong>NIM:</strong> {$data->nim}</li>
                    <li><strong>Judul:</strong> {$data->judul}</li>
                </ul>
                <p>Silakan login ke sistem untuk melakukan review.</p>
                <p><a href='".base_url('dosen/seminar_skripsi')."' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Review Pengajuan</a></p>
            ";
            
            $this->email->message($message);
            return $this->email->send();
            
        } catch (Exception $e) {
            log_message('error', 'Notification error: ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    // NEW PRIVATE METHODS (ENHANCED FEATURES)
    // =================================================================

    /**
     * NEW: Handle enhanced file upload (supports 2 files)
     */
    private function _handle_enhanced_file_upload()
    {
        $result = [
            'success' => false,
            'message' => '',
            'files' => []
        ];

        try {
            // Upload file skripsi (REQUIRED)
            $skripsi_result = $this->_upload_single_file('file_skripsi', 'skripsi_files', [
                'allowed_types' => 'pdf|doc|docx',
                'max_size' => 5120 // 5MB
            ]);

            if (!$skripsi_result['success']) {
                return [
                    'success' => false,
                    'message' => 'Error upload file skripsi: ' . $skripsi_result['message']
                ];
            }

            $result['files']['file_skripsi'] = $skripsi_result['filename'];

            // Upload surat keterangan penelitian (REQUIRED)
            $surat_result = $this->_upload_single_file('surat_penelitian', 'surat_penelitian', [
                'allowed_types' => 'pdf|jpg|jpeg|png',
                'max_size' => 3072 // 3MB
            ]);

            if (!$surat_result['success']) {
                // Cleanup file skripsi yang sudah terupload
                $this->_delete_uploaded_file($result['files']['file_skripsi'], 'skripsi_files');
                
                return [
                    'success' => false,
                    'message' => 'Error upload surat penelitian: ' . $surat_result['message']
                ];
            }

            $result['files']['surat_penelitian'] = $surat_result['filename'];
            $result['success'] = true;

        } catch (Exception $e) {
            log_message('error', 'Enhanced file upload error: ' . $e->getMessage());
            
            // Cleanup any uploaded files
            if (!empty($result['files'])) {
                $this->_cleanup_uploaded_files($result['files']);
            }
            
            $result = [
                'success' => false,
                'message' => 'Terjadi kesalahan saat upload file: ' . $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * NEW: Upload single file with custom config
     */
    private function _upload_single_file($field_name, $subfolder, $custom_config = [])
    {
        $upload_path = FCPATH . 'uploads/seminar_skripsi/' . $subfolder . '/';
        
        // Create directory if not exists
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $default_config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 5120,
            'encrypt_name' => true,
            'remove_spaces' => true
        ];

        $config = array_merge($default_config, $custom_config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            $upload_data = $this->upload->data();
            return [
                'success' => true,
                'filename' => $upload_data['file_name']
            ];
        } else {
            return [
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        }
    }

    /**
     * ✅ REPLACE method _handle_enhanced_resubmit() yang ada dengan ini:
     */
    private function _handle_enhanced_resubmit($seminar_id, $mahasiswa_id)
    {
        // ENHANCED: Validation (added judul_skripsi)
        $this->form_validation->set_rules('keterangan', 'Keterangan Perbaikan', 'required|max_length[500]');
        $this->form_validation->set_rules('judul_skripsi', 'Judul Skripsi', 'min_length[10]|max_length[250]');
        
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        try {
            // Get current seminar data
            $current_seminar = $this->_get_seminar_by_id($seminar_id, $mahasiswa_id);
            if (!$current_seminar) {
                throw new Exception('Data seminar tidak ditemukan');
            }
    
            $update_data = [
                'keterangan_mahasiswa' => trim($this->input->post('keterangan')),
                'status' => 'submitted',
                'status_pembimbing' => 'pending',
                'status_kaprodi' => 'pending',
                'komentar_pembimbing' => null,
                'komentar_kaprodi' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
    
            // NEW: Update judul if provided
            $judul_baru = trim($this->input->post('judul_skripsi'));
            if (!empty($judul_baru) && strlen($judul_baru) >= 10) {
                $update_data['judul_skripsi'] = $judul_baru;
            }
    
            // NEW: Handle file uploads (optional for resubmit)
            $files_to_cleanup = [];
    
            // Handle file skripsi update (optional)
            if (!empty($_FILES['file_skripsi']['name'])) {
                $skripsi_result = $this->_upload_single_file('file_skripsi', 'skripsi_files', [
                    'allowed_types' => 'pdf|doc|docx',
                    'max_size' => 5120
                ]);
    
                if ($skripsi_result['success']) {
                    $files_to_cleanup[] = ['filename' => $current_seminar->file_skripsi, 'subfolder' => 'skripsi_files'];
                    $update_data['file_skripsi'] = $skripsi_result['filename'];
                } else {
                    throw new Exception('Error upload file skripsi: ' . $skripsi_result['message']);
                }
            }
    
            // Handle surat penelitian update (optional)
            if (!empty($_FILES['surat_penelitian']['name'])) {
                $surat_result = $this->_upload_single_file('surat_penelitian', 'surat_penelitian', [
                    'allowed_types' => 'pdf|jpg|jpeg|png',
                    'max_size' => 3072
                ]);
    
                if ($surat_result['success']) {
                    $files_to_cleanup[] = ['filename' => $current_seminar->surat_keterangan_penelitian, 'subfolder' => 'surat_penelitian'];
                    $update_data['surat_keterangan_penelitian'] = $surat_result['filename'];
                } else {
                    throw new Exception('Error upload surat penelitian: ' . $surat_result['message']);
                }
            }
    
            // Update database
            $this->db->where('id', $seminar_id)
                    ->where('mahasiswa_id', $mahasiswa_id)
                    ->update('seminar_skripsi_mahasiswa', $update_data);
    
            // Cleanup old files
            foreach ($files_to_cleanup as $file_info) {
                if (!empty($file_info['filename'])) {
                    $this->_delete_uploaded_file($file_info['filename'], $file_info['subfolder']);
                }
            }
            
            // ✅ NEW: KIRIM NOTIFIKASI RESUBMIT KE DOSEN PEMBIMBING
            $this->_send_resubmit_notification($current_seminar->proposal_id, $seminar_id, $update_data);
            
            $this->session->set_flashdata('success', 'Pengajuan ulang berhasil dikirim!');
            
        } catch (Exception $e) {
            log_message('error', 'Enhanced resubmit error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }

    /**
     * NEW: Get file info for download (supports multiple file types)
     */
    private function _get_file_info($seminar, $type)
    {
        $base_path = FCPATH . 'uploads/seminar_skripsi/';
        
        switch ($type) {
            case 'skripsi':
                return [
                    'filename' => $seminar->file_skripsi,
                    'path' => $base_path . 'skripsi_files/' . $seminar->file_skripsi,
                    'download_name' => 'Skripsi_' . date('Ymd') . '_' . pathinfo($seminar->file_skripsi, PATHINFO_EXTENSION)
                ];
                
            case 'surat':
                return [
                    'filename' => $seminar->surat_keterangan_penelitian,
                    'path' => $base_path . 'surat_penelitian/' . $seminar->surat_keterangan_penelitian,
                    'download_name' => 'Surat_Penelitian_' . date('Ymd') . '.' . pathinfo($seminar->surat_keterangan_penelitian, PATHINFO_EXTENSION)
                ];
                
            default:
                return [
                    'filename' => null,
                    'path' => null,
                    'download_name' => null
                ];
        }
    }

    /**
     * NEW: Delete uploaded file
     */
    private function _delete_uploaded_file($filename, $subfolder)
    {
        if (empty($filename)) return;
        
        $file_path = FCPATH . 'uploads/seminar_skripsi/' . $subfolder . '/' . $filename;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    /**
     * NEW: Cleanup multiple uploaded files
     */
    private function _cleanup_uploaded_files($files)
    {
        if (isset($files['file_skripsi'])) {
            $this->_delete_uploaded_file($files['file_skripsi'], 'skripsi_files');
        }
        
        if (isset($files['surat_penelitian'])) {
            $this->_delete_uploaded_file($files['surat_penelitian'], 'surat_penelitian');
        }
    }

    /**
     * OLD: Handle file upload (DEPRECATED - kept for backward compatibility)
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
            'filename' => $upload_data['file_name']
        ];
    }

    /**
     * ✅ REPLACE method _handle_resubmit() yang ada dengan ini:
     */
    private function _handle_resubmit($seminar_id, $mahasiswa_id)
    {
        $this->form_validation->set_rules('keterangan', 'Keterangan Perbaikan', 'required|max_length[500]');
        
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        try {
            // Get current seminar data untuk notifikasi
            $current_seminar = $this->_get_seminar_by_id($seminar_id, $mahasiswa_id);
            if (!$current_seminar) {
                throw new Exception('Data seminar tidak ditemukan');
            }
            
            $update_data = [
                'keterangan_mahasiswa' => $this->input->post('keterangan'),
                'status' => 'submitted',
                'status_pembimbing' => 'pending',
                'status_kaprodi' => 'pending',
                'komentar_pembimbing' => null,
                'komentar_kaprodi' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle new file if uploaded
            if (!empty($_FILES['file_skripsi']['name'])) {
                $upload_result = $this->_handle_file_upload();
                if ($upload_result['success']) {
                    $update_data['file_skripsi'] = $upload_result['filename'];
                }
            }
            
            $this->db->where('id', $seminar_id)
                    ->where('mahasiswa_id', $mahasiswa_id)
                    ->update('seminar_skripsi_mahasiswa', $update_data);
            
            // ✅ NEW: KIRIM NOTIFIKASI RESUBMIT KE DOSEN PEMBIMBING
            $this->_send_resubmit_notification($current_seminar->proposal_id, $seminar_id, $update_data);
            
            $this->session->set_flashdata('success', 'Pengajuan ulang berhasil dikirim!');
            
        } catch (Exception $e) {
            log_message('error', 'Resubmit error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }
    
    /**
     * ✅ NEW: Send resubmit notification ke dosen pembimbing
     * TAMBAHKAN METHOD INI di bagian akhir controller (sebelum closing brace)
     */
    private function _send_resubmit_notification($proposal_id, $seminar_id, $update_data)
    {
        try {
            // Get data mahasiswa dan dosen
            $data = $this->db->select('
                    pm.judul as judul_proposal,
                    ssm.judul_skripsi,
                    ssm.keterangan_mahasiswa,
                    m.nama as nama_mahasiswa,
                    m.nim,
                    d.email as email_pembimbing,
                    d.nama as nama_pembimbing
                ')
                ->from('proposal_mahasiswa pm')
                ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                ->join('dosen d', 'pm.dosen_id = d.id')
                ->join('seminar_skripsi_mahasiswa ssm', 'ssm.proposal_id = pm.id')
                ->where('pm.id', $proposal_id)
                ->where('ssm.id', $seminar_id)
                ->get()->row();
                
            if (!$data || !$data->email_pembimbing) {
                log_message('info', 'Resubmit notification skipped: no dosen email found');
                return false;
            }
            
            // Email config (sama seperti submit pertama)
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
            $this->email->clear();
            $this->email->from('stkyakobus@gmail.com', 'SIM TA STK Santo Yakobus');
            $this->email->to($data->email_pembimbing);
            $this->email->subject('🔄 Pengajuan Ulang Seminar Skripsi - ' . $data->nama_mahasiswa);
            
            // Tentukan judul yang digunakan
            $judul_display = !empty($data->judul_skripsi) ? $data->judul_skripsi : $data->judul_proposal;
            $judul_berubah = !empty($data->judul_skripsi) && ($data->judul_skripsi !== $data->judul_proposal);
            
            // Template email khusus untuk resubmit
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd;'>
                <div style='background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%); color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>🔄 Pengajuan Ulang Seminar Skripsi</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Kepada Yth. <strong>{$data->nama_pembimbing}</strong>,</p>
                    
                    <p>Mahasiswa bimbingan Anda telah melakukan <strong>PENGAJUAN ULANG</strong> seminar skripsi setelah perbaikan:</p>
                    
                    <div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #17a2b8;'>
                        <h4 style='color: #0c5460; margin: 0 0 10px 0;'>👨‍🎓 Data Mahasiswa:</h4>
                        <ul style='color: #0c5460; margin: 0;'>
                            <li><strong>Nama:</strong> {$data->nama_mahasiswa}</li>
                            <li><strong>NIM:</strong> {$data->nim}</li>
                            <li><strong>Judul:</strong> {$judul_display}</li>
                        </ul>
                    </div>";
            
            // Tampilkan info jika judul berubah
            if ($judul_berubah) {
                $message .= "
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>📝 Perubahan Judul:</h4>
                        <p style='color: #856404; margin: 0;'>
                            <strong>Judul Proposal:</strong> {$data->judul_proposal}<br>
                            <strong>Judul Skripsi Baru:</strong> {$data->judul_skripsi}
                        </p>
                    </div>";
            }
            
            $message .= "
                    <div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>💬 Keterangan Perbaikan:</h4>
                        <p style='color: #155724; margin: 0;'>" . nl2br(htmlspecialchars($data->keterangan_mahasiswa)) . "</p>
                    </div>
                    
                    <div style='background-color: #cce5ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>⏭️ Tindak Lanjut:</h4>
                        <p style='color: #004085; margin: 0;'>
                            Silakan review kembali pengajuan ulang ini dan berikan rekomendasi untuk tahap selanjutnya.
                        </p>
                    </div>
                    
                    <p style='text-align: center; margin-top: 20px;'>
                        <a href='" . base_url('dosen/seminar_skripsi') . "' 
                           style='background-color: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                            📋 Review Pengajuan Ulang
                        </a>
                    </p>
                    
                    <p style='font-size: 14px; color: #6c757d; text-align: center; margin-top: 20px;'>
                        Status: Menunggu review ulang dari dosen pembimbing
                    </p>
                </div>
                
                <div style='background-color: #e9ecef; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
                    <p style='margin: 0;'>© " . date('Y') . " STK Santo Yakobus - Sistem Informasi Manajemen Tugas Akhir</p>
                </div>
            </div>";
            
            $this->email->message($message);
            $result = $this->email->send();
            
            if ($result) {
                log_message('info', 'Resubmit notification sent successfully to: ' . $data->email_pembimbing);
            } else {
                log_message('error', 'Failed to send resubmit notification: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Resubmit notification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ PERBAIKAN: Update method view_penilaian untuk handling rekomendasi yang benar
     */
    public function view_penilaian($seminar_id = null)
    {
        if (!$seminar_id) {
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi akses mahasiswa (SUDAH FIXED)
        $seminar = $this->_get_seminar_by_id($seminar_id, $mahasiswa_id);
        if (!$seminar) {
            show_404();
            return;
        }
        
        // Get penilaian data yang sudah published (SUDAH FIXED)
        $penilaian = $this->_get_penilaian_published($seminar_id);
        if (!$penilaian) {
            $this->session->set_flashdata('error', 'Penilaian belum tersedia atau belum dipublikasikan');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        // ✅ NEW: Process rekomendasi untuk fix tampilan "TIDAK LULUS"
        $penilaian = $this->_process_rekomendasi($penilaian);
        
        $data = [
            'title' => 'Hasil Penilaian Seminar Skripsi',
            'seminar' => $seminar,
            'penilaian' => $penilaian
        ];
        
        $this->load->view('template/mahasiswa', [
            'title' => 'Hasil Penilaian Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/view_penilaian', $data, TRUE)
        ]);
    }
    
    /**
 * ✅ NEW: Process rekomendasi untuk fix tampilan yang salah
 */
private function _process_rekomendasi($penilaian)
{
    // Standardize rekomendasi values
    $rekomendasi_map = [
        'diterima_tanpa_revisi' => 'lulus_tanpa_revisi',
        'revisi_minor' => 'lulus_dengan_revisi_minor', 
        'revisi_mayor' => 'lulus_dengan_revisi_mayor',
        'ditolak' => 'tidak_lulus',
        'lulus' => 'lulus_tanpa_revisi',
        'tidak_lulus' => 'tidak_lulus'
    ];
    
    // Map rekomendasi jika perlu
    if (isset($rekomendasi_map[strtolower($penilaian->rekomendasi)])) {
        $penilaian->rekomendasi = $rekomendasi_map[strtolower($penilaian->rekomendasi)];
    }
    
    // ✅ FIX: Jika rekomendasi kosong atau salah, tentukan berdasarkan nilai
    if (empty($penilaian->rekomendasi) || $penilaian->rekomendasi == 'tidak_lulus') {
        if ($penilaian->nilai_akhir >= 80) {
            $penilaian->rekomendasi = 'lulus_tanpa_revisi';
        } elseif ($penilaian->nilai_akhir >= 70) {
            $penilaian->rekomendasi = 'lulus_dengan_revisi_minor';
        } elseif ($penilaian->nilai_akhir >= 60) {
            $penilaian->rekomendasi = 'lulus_dengan_revisi_mayor';
        } else {
            $penilaian->rekomendasi = 'tidak_lulus';
        }
    }
    
    log_message('debug', 'Processed rekomendasi: ' . $penilaian->rekomendasi . ' (nilai: ' . $penilaian->nilai_akhir . ')');
    
    return $penilaian;
}

        
/**
 * ✅ FIXED: Get published penilaian data with complete relations
 */
private function _get_penilaian_published($seminar_id)
{
    try {
        // ✅ OPSI 1: Gunakan view database (RECOMMENDED jika tersedia)
        if ($this->_check_view_exists('penilaian_seminar_skripsi_v')) {
            $this->db->select('*');
            $this->db->from('penilaian_seminar_skripsi_v');
            $this->db->where('seminar_skripsi_id', $seminar_id);
            $this->db->where('status_penilaian', 'published');
            $this->db->where('published_at IS NOT NULL');
            
            $result = $this->db->get()->row();
            
            if ($result) {
                log_message('debug', 'Penilaian data from view: ' . json_encode($result));
                return $result;
            }
        }
        
        // ✅ OPSI 2: Manual JOIN (FALLBACK jika view tidak ada)
        log_message('info', 'Using manual JOIN for penilaian data');
        
        $this->db->select('
            pss.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            pm.judul,
            pm.judul as judul_skripsi,
            pr.nama as nama_prodi,
            ssk.tanggal_seminar,
            ssk.jam_seminar,
            ssk.tempat_seminar,
            d.nama as nama_pembimbing,
            d1.nama as nama_penguji1,
            d2.nama as nama_penguji2
        ');
        $this->db->from('penilaian_seminar_skripsi pss');
        $this->db->join('seminar_skripsi_mahasiswa ssk', 'pss.seminar_skripsi_id = ssk.id');
        $this->db->join('proposal_mahasiswa pm', 'pss.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pss.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('dosen d1', 'ssk.dosen_penguji1_id = d1.id', 'left');
        $this->db->join('dosen d2', 'ssk.dosen_penguji2_id = d2.id', 'left');
        
        $this->db->where('pss.seminar_skripsi_id', $seminar_id);
        $this->db->where('pss.status_penilaian', 'published');
        $this->db->where('pss.published_at IS NOT NULL');
        
        $result = $this->db->get()->row();
        
        if ($result) {
            log_message('debug', 'Penilaian data from manual JOIN: ' . json_encode($result));
        }
        
        return $result;
        
    } catch (Exception $e) {
        log_message('error', 'Get penilaian published error: ' . $e->getMessage());
        return null;
    }
}
    
    /**
     * ✅ NEW: Check if penilaian exists and published
     */
    private function _has_published_penilaian($seminar_id)
    {
        try {
            $count = $this->db->where('seminar_skripsi_id', $seminar_id)
                             ->where('status_penilaian', 'published')
                             ->where('published_at IS NOT NULL')
                             ->count_all_results('penilaian_seminar_skripsi');
            return $count > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * ✅ NEW: Get completed status text based on penilaian availability
     */
    private function _get_completed_status_text($seminar)
    {
        if ($this->_has_published_penilaian($seminar->id)) {
            return 'Seminar Selesai - Nilai Tersedia';
        } else {
            return 'Seminar Selesai - Menunggu Nilai';
        }
    }
    
    /**
     * ✅ NEW: Get completed status class based on penilaian availability
     */
    private function _get_completed_status_class($seminar)
    {
        if ($this->_has_published_penilaian($seminar->id)) {
            return 'success';
        } else {
            return 'info';
        }
    }
    
    /**
     * ✅ NEW: Get completed progress based on penilaian availability
     */
    private function _get_completed_progress($seminar)
    {
        if ($this->_has_published_penilaian($seminar->id)) {
            return 100;
        } else {
            return 95;
        }
    }
    
    /**
     * ✅ NEW: Check if database view exists
     */
    private function _check_view_exists($view_name)
    {
        try {
            $query = $this->db->query("SHOW TABLES LIKE '{$view_name}'");
            return $query->num_rows() > 0;
        } catch (Exception $e) {
            log_message('debug', 'Check view exists error: ' . $e->getMessage());
            return false;
        }
    }
}

/* End of file Seminar_skripsi.php */
/* Location: ./application/controllers/mahasiswa/Seminar_skripsi.php */