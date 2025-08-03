<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Controller Mahasiswa - SIMPLE & ROBUST VERSION
 * 
 * SIMPLIFIED FEATURES:
 * - ✅ Basic eligibility check (jurnal bimbingan + penelitian)
 * - ✅ Submit seminar skripsi dengan file upload
 * - ✅ View status dan progress
 * - ✅ Resubmit jika ditolak
 * - ✅ Notification ke dosen
 * - ✅ Robust error handling
 * 
 * REMOVED COMPLEXITY:
 * - Advanced workflow detection 
 * - Complex penilaian system
 * - Auto-update to publikasi
 * - Multiple database table joins
 * 
 * File: application/controllers/mahasiswa/Seminar_skripsi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa  
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     4.0 (Simple & Robust)
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
     * ✅ SIMPLE: Index - Dashboard seminar skripsi
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
            // Step 1: Check existing seminar
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
                // No existing seminar - check eligibility
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
        
        // Load view
        $this->load->view('template/mahasiswa', [
            'title' => 'Seminar Skripsi',
            'content' => $this->load->view('mahasiswa/seminar_skripsi/index', $data, TRUE)
        ]);
    }

    /**
     * ✅ SIMPLE: Submit pengajuan seminar skripsi
     */
    public function submit()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        if ($this->input->method() !== 'post') {
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        // Validation
        $this->form_validation->set_rules('proposal_id', 'Proposal ID', 'required|numeric');
        $this->form_validation->set_rules('keterangan', 'Keterangan', 'required|max_length[500]');
        
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        try {
            $proposal_id = $this->input->post('proposal_id');
            
            // Validate proposal ownership
            if (!$this->_validate_proposal_ownership($proposal_id, $mahasiswa_id)) {
                $this->session->set_flashdata('error', 'Proposal tidak valid!');
                redirect('mahasiswa/seminar_skripsi');
                return;
            }
            
            // Check if already submitted
            if ($this->_has_existing_seminar($mahasiswa_id)) {
                $this->session->set_flashdata('error', 'Anda sudah mengajukan seminar skripsi!');
                redirect('mahasiswa/seminar_skripsi');
                return;
            }
            
            // Handle file upload
            $upload_result = $this->_handle_file_upload();
            if (!$upload_result['success']) {
                $this->session->set_flashdata('error', $upload_result['message']);
                redirect('mahasiswa/seminar_skripsi');
                return;
            }
            
            // Insert seminar data
            $seminar_data = [
                'proposal_id' => $proposal_id,
                'mahasiswa_id' => $mahasiswa_id,
                'keterangan_mahasiswa' => $this->input->post('keterangan'),
                'file_skripsi' => $upload_result['filename'],
                'status' => 'submitted',
                'status_pembimbing' => 'pending',
                'status_kaprodi' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->trans_start();
            $this->db->insert('seminar_skripsi_mahasiswa', $seminar_data);
            $seminar_id = $this->db->insert_id();
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE || !$seminar_id) {
                throw new Exception('Gagal menyimpan data seminar');
            }
            
            // Send notification
            $this->_send_notification($proposal_id, $seminar_id);
            
            $this->session->set_flashdata('success', 'Pengajuan seminar skripsi berhasil dikirim!');
            
        } catch (Exception $e) {
            log_message('error', 'Submit seminar error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }

    /**
     * ✅ SIMPLE: Resubmit pengajuan yang ditolak
     */
    public function resubmit($seminar_id = null)
    {
        if (!$seminar_id) {
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Get seminar data
        $seminar = $this->_get_seminar_by_id($seminar_id, $mahasiswa_id);
        if (!$seminar || $seminar->status !== 'rejected') {
            $this->session->set_flashdata('error', 'Data seminar tidak valid untuk resubmit!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        if ($this->input->method() === 'post') {
            $this->_handle_resubmit($seminar_id, $mahasiswa_id);
        } else {
            $this->_show_resubmit_form($seminar);
        }
    }

    /**
     * ✅ SIMPLE: Download file skripsi
     */
    public function download_file($seminar_id = null)
    {
        if (!$seminar_id) {
            show_404();
            return;
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        $seminar = $this->_get_seminar_by_id($seminar_id, $mahasiswa_id);
        
        if (!$seminar || empty($seminar->file_skripsi)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        $file_path = FCPATH . 'uploads/seminar_skripsi/skripsi_files/' . $seminar->file_skripsi;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ada di server!');
            redirect('mahasiswa/seminar_skripsi');
            return;
        }
        
        $this->load->helper('download');
        force_download('Skripsi_' . date('Ymd') . '.pdf', file_get_contents($file_path));
    }

    // =================================================================
    // PRIVATE METHODS - SIMPLE & ROBUST
    // =================================================================

    /**
     * Get existing seminar (simple query)
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
     * Check eligibility (simplified - only 2 requirements)
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
            // Get proposal
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
            
            // Requirement 1: Jurnal bimbingan (14 tervalidasi)
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
            
            // Requirement 2: Penelitian (simplified check)
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
     * Get status info for display
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
                'text' => 'Seminar Selesai',
                'class' => 'success',
                'progress' => 100
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
     * Validate proposal ownership
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
     * Check if has existing seminar
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
     * Get seminar by ID with ownership validation
     */
    private function _get_seminar_by_id($seminar_id, $mahasiswa_id)
    {
        try {
            return $this->db->select('ssm.*, pm.judul as proposal_judul')
                          ->from('seminar_skripsi_mahasiswa ssm')
                          ->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id')
                          ->where('ssm.id', $seminar_id)
                          ->where('ssm.mahasiswa_id', $mahasiswa_id)
                          ->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Get seminar by ID error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle file upload
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
     * Handle resubmit process
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
            
            $this->session->set_flashdata('success', 'Pengajuan ulang berhasil dikirim!');
            
        } catch (Exception $e) {
            log_message('error', 'Resubmit error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('mahasiswa/seminar_skripsi');
    }

    /**
     * Show resubmit form
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
     * Send notification (simplified)
     */
    private function _send_notification($proposal_id, $seminar_id)
    {
        try {
            // Get data
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
            
            // Email config
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
}

/* End of file Seminar_skripsi.php */
/* Location: ./application/controllers/mahasiswa/Seminar_skripsi.php */