<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penelitian Controller untuk Mahasiswa - Tahap 4 Workflow
 * 
 * Controller untuk mengelola permohonan izin penelitian mahasiswa
 * sesuai dengan workflow tahap 4 yang telah didefinisikan.
 * 
 * Features:
 * - Dashboard penelitian dengan progress tracking
 * - Form pengajuan izin penelitian dengan validasi syarat
 * - Upload proposal revisi dan tracking status
 * - Download surat izin penelitian dari staf
 * - Integration dengan template mahasiswa existing
 * 
 * File: application/controllers/mahasiswa/Penelitian.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Mahasiswa
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (Workflow Tahap 4)
 */
class Penelitian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['session', 'upload', 'form_validation']);
        $this->load->helper(['url', 'form', 'file', 'security']);
        $this->load->model('Penelitian_model', 'penelitian');
        
        // Cek login mahasiswa
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        // Validasi mahasiswa - pastikan yang login adalah mahasiswa
        $mahasiswa_id = $this->session->userdata('id');
        if (empty($mahasiswa_id)) {
            $this->session->sess_destroy();
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard penelitian mahasiswa
     * Menampilkan status dan daftar permohonan izin penelitian
     */
    public function index() {
        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            // Ambil daftar permohonan mahasiswa
            $permohonan_result = $this->penelitian->get_permohonan_by_mahasiswa($mahasiswa_id);
            $permohonan_list = $permohonan_result['error'] ? [] : $permohonan_result['data'];
            
            // Ambil proposal mahasiswa untuk cek eligibility
            $this->db->select('id, judul, workflow_status');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('created_at', 'DESC');
            $proposal_list = $this->db->get()->result();
            
            // Prepare data untuk view
            $view_data = [
                'permohonan_list' => $permohonan_list,
                'proposal_list' => $proposal_list,
                'statistics' => $this->_get_statistics($mahasiswa_id)
            ];

            // Render view content
            ob_start();
            $this->load->view('mahasiswa/penelitian/index', $view_data);
            $content = ob_get_clean();

            // Load template mahasiswa
            $this->load->view('template/mahasiswa', [
                'title' => 'Penelitian - Tahap 4',
                'content' => $content,
                'script' => $this->_get_index_scripts()
            ]);

        } catch (Exception $e) {
            log_message('error', 'Error in penelitian index: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat halaman');
            redirect('mahasiswa/dashboard');
        }
    }

    /**
     * Form pengajuan izin penelitian
     * GET: Tampilkan form, POST: Process form submission
     */
    public function ajukan($proposal_id = null) {
        $mahasiswa_id = $this->session->userdata('id');
        
        if ($this->input->method() === 'post') {
            return $this->_process_pengajuan();
        }

        // GET Request - Tampilkan form
        if (!$proposal_id) {
            $this->session->set_flashdata('error', 'ID Proposal tidak valid');
            redirect('mahasiswa/penelitian');
        }

        try {
            // Cek eligibility
            $eligibility = $this->penelitian->check_eligibility($proposal_id, $mahasiswa_id);
            
            if ($eligibility['error']) {
                $this->session->set_flashdata('error', $eligibility['message']);
                redirect('mahasiswa/penelitian');
            }

            if (!$eligibility['eligible']) {
                $this->session->set_flashdata('warning', $eligibility['message']);
                redirect('mahasiswa/penelitian');
            }

            // Ambil data proposal untuk pre-fill form
            $proposal_result = $this->penelitian->get_proposal_data($proposal_id, $mahasiswa_id);
            if ($proposal_result['error']) {
                $this->session->set_flashdata('error', $proposal_result['message']);
                redirect('mahasiswa/penelitian');
            }

            // Ambil daftar dosen untuk dropdown
            $dosen_result = $this->penelitian->get_dosen_list();
            $dosen_list = $dosen_result['error'] ? [] : $dosen_result['data'];

            // Prepare data untuk view
            $view_data = [
                'proposal' => $proposal_result['data'],
                'dosen_list' => $dosen_list,
                'eligibility' => $eligibility
            ];

            // Render view content
            ob_start();
            $this->load->view('mahasiswa/penelitian/form_ajukan', $view_data);
            $content = ob_get_clean();

            // Load template mahasiswa
            $this->load->view('template/mahasiswa', [
                'title' => 'Ajukan Izin Penelitian',
                'content' => $content,
                'script' => $this->_get_form_scripts(),
                'styles' => $this->_get_form_styles()
            ]);

        } catch (Exception $e) {
            log_message('error', 'Error in ajukan form: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat form');
            redirect('mahasiswa/penelitian');
        }
    }

    /**
     * Detail permohonan izin penelitian
     * Untuk tracking status dan download surat
     */
    public function detail($permohonan_id = null) {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$permohonan_id) {
            $this->session->set_flashdata('error', 'ID Permohonan tidak valid');
            redirect('mahasiswa/penelitian');
        }

        try {
            // Ambil detail permohonan
            $detail_result = $this->penelitian->get_permohonan_detail($permohonan_id, $mahasiswa_id);
            
            if ($detail_result['error']) {
                $this->session->set_flashdata('error', $detail_result['message']);
                redirect('mahasiswa/penelitian');
            }

            $permohonan = $detail_result['data'];

            // Prepare data untuk view
            $view_data = [
                'permohonan' => $permohonan,
                'progress' => $this->_get_progress_data($permohonan)
            ];

            // Render view content
            ob_start();
            $this->load->view('mahasiswa/penelitian/detail', $view_data);
            $content = ob_get_clean();

            // Load template mahasiswa
            $this->load->view('template/mahasiswa', [
                'title' => 'Detail Izin Penelitian',
                'content' => $content,
                'script' => $this->_get_detail_scripts()
            ]);

        } catch (Exception $e) {
            log_message('error', 'Error in detail: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat detail');
            redirect('mahasiswa/penelitian');
        }
    }

    /**
     * Download surat izin penelitian yang sudah diupload staf
     */
    public function download_surat($permohonan_id = null) {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$permohonan_id) {
            $this->session->set_flashdata('error', 'ID Permohonan tidak valid');
            redirect('mahasiswa/penelitian');
        }

        try {
            // Validasi akses
            $detail_result = $this->penelitian->get_permohonan_detail($permohonan_id, $mahasiswa_id);
            
            if ($detail_result['error']) {
                $this->session->set_flashdata('error', $detail_result['message']);
                redirect('mahasiswa/penelitian');
            }

            $permohonan = $detail_result['data'];
            
            // Cek apakah surat sudah tersedia
            if (empty($permohonan->file_surat_izin_staf)) {
                $this->session->set_flashdata('error', 'Surat izin penelitian belum tersedia');
                redirect('mahasiswa/penelitian/detail/' . $permohonan_id);
            }

            // Path file surat
            $file_path = FCPATH . 'uploads/penelitian/surat_izin/' . $permohonan->file_surat_izin_staf;
            
            if (!file_exists($file_path)) {
                $this->session->set_flashdata('error', 'File surat tidak ditemukan');
                redirect('mahasiswa/penelitian/detail/' . $permohonan_id);
            }

            // Download file
            $this->load->helper('download');
            $download_name = 'Surat_Izin_Penelitian_' . $permohonan->nim . '_' . date('Y-m-d') . '.pdf';
            force_download($download_name, file_get_contents($file_path));

        } catch (Exception $e) {
            log_message('error', 'Error in download: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat download');
            redirect('mahasiswa/penelitian');
        }
    }

    /**
     * Cek syarat untuk mengajukan izin penelitian
     * AJAX endpoint untuk real-time validation
     */
    public function check_syarat($proposal_id = null) {
        $mahasiswa_id = $this->session->userdata('id');
        
        if (!$proposal_id || $this->input->method() !== 'post') {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => true, 'message' => 'Invalid request']));
            return;
        }

        try {
            $eligibility = $this->penelitian->check_eligibility($proposal_id, $mahasiswa_id);
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($eligibility));

        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'error' => true,
                    'message' => 'Terjadi kesalahan saat validasi syarat'
                ]));
        }
    }

    // =================================================================
    // PRIVATE METHODS
    // =================================================================

    /**
     * Process form pengajuan izin penelitian
     */
    private function _process_pengajuan() {
        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            // Validasi form
            $this->form_validation->set_rules('proposal_mahasiswa_id', 'Proposal ID', 'required|numeric');
            $this->form_validation->set_rules('nama_mahasiswa', 'Nama Mahasiswa', 'required|max_length[100]');
            $this->form_validation->set_rules('nim', 'NIM', 'required|max_length[20]');
            $this->form_validation->set_rules('semester', 'Semester', 'required|max_length[10]');
            $this->form_validation->set_rules('program_studi', 'Program Studi', 'required');
            $this->form_validation->set_rules('judul_skripsi_terbaru', 'Judul Skripsi', 'required');
            $this->form_validation->set_rules('tempat_penelitian', 'Tempat Penelitian', 'required|max_length[255]');
            $this->form_validation->set_rules('tanggal_mulai_penelitian', 'Tanggal Mulai', 'required');
            $this->form_validation->set_rules('tanggal_selesai_penelitian', 'Tanggal Selesai', 'required');
            $this->form_validation->set_rules('dosen_pembimbing_id', 'Dosen Pembimbing', 'required|numeric');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('mahasiswa/penelitian/ajukan/' . $this->input->post('proposal_mahasiswa_id'));
            }

            // Prepare data untuk model
            $input_data = [
                'proposal_mahasiswa_id' => $this->input->post('proposal_mahasiswa_id'),
                'mahasiswa_id' => $mahasiswa_id,
                'nama_mahasiswa' => $this->input->post('nama_mahasiswa'),
                'nim' => $this->input->post('nim'),
                'semester' => $this->input->post('semester'),
                'program_studi' => $this->input->post('program_studi'),
                'judul_skripsi_terbaru' => $this->input->post('judul_skripsi_terbaru'),
                'tempat_penelitian' => $this->input->post('tempat_penelitian'),
                'tanggal_mulai_penelitian' => $this->input->post('tanggal_mulai_penelitian'),
                'tanggal_selesai_penelitian' => $this->input->post('tanggal_selesai_penelitian'),
                'dosen_pembimbing_id' => $this->input->post('dosen_pembimbing_id'),
                'file_proposal_revisi' => $this->input->post('file_proposal_revisi') // base64 dari form
            ];

            // Process melalui model
            $result = $this->penelitian->create_permohonan($input_data);
            
            if ($result['error']) {
                $this->session->set_flashdata('error', $result['message']);
                redirect('mahasiswa/penelitian/ajukan/' . $input_data['proposal_mahasiswa_id']);
            }

            $this->session->set_flashdata('success', $result['message']);
            redirect('mahasiswa/penelitian');

        } catch (Exception $e) {
            log_message('error', 'Error processing pengajuan: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memproses pengajuan');
            redirect('mahasiswa/penelitian');
        }
    }

    /**
     * Get statistik untuk dashboard
     */
    private function _get_statistics($mahasiswa_id) {
        try {
            // Total permohonan
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $total_permohonan = $this->db->count_all_results('permohonan_izin_penelitian');

            // Permohonan pending
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->where_in('status', ['submitted', 'review_pembimbing', 'approved']);
            $pending_permohonan = $this->db->count_all_results('permohonan_izin_penelitian');

            // Permohonan selesai
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->where('status', 'completed');
            $completed_permohonan = $this->db->count_all_results('permohonan_izin_penelitian');

            return [
                'total_permohonan' => $total_permohonan,
                'pending_permohonan' => $pending_permohonan,
                'completed_permohonan' => $completed_permohonan
            ];

        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return [
                'total_permohonan' => 0,
                'pending_permohonan' => 0,
                'completed_permohonan' => 0
            ];
        }
    }

    /**
     * Get progress data untuk status tracking
     */
    private function _get_progress_data($permohonan) {
        $steps = [
            [
                'name' => 'Pengajuan',
                'description' => 'Mahasiswa mengajukan permohonan',
                'status' => 'completed',
                'date' => $permohonan->created_at
            ],
            [
                'name' => 'Review Pembimbing',
                'description' => 'Dosen pembimbing review permohonan',
                'status' => $this->_get_step_status($permohonan, 'pembimbing'),
                'date' => $permohonan->tanggal_review_pembimbing
            ],
            [
                'name' => 'Proses Staf',
                'description' => 'Staf memproses surat izin penelitian',
                'status' => $this->_get_step_status($permohonan, 'staf'),
                'date' => $permohonan->tanggal_upload_surat_staf
            ],
            [
                'name' => 'Selesai',
                'description' => 'Surat izin penelitian siap didownload',
                'status' => $this->_get_step_status($permohonan, 'completed'),
                'date' => ($permohonan->status == 'completed') ? $permohonan->updated_at : null
            ]
        ];

        return [
            'steps' => $steps,
            'current_step' => $this->_get_current_step($permohonan),
            'progress_percentage' => $this->_get_progress_percentage($permohonan)
        ];
    }

    /**
     * Get status step untuk progress tracking
     */
    private function _get_step_status($permohonan, $step) {
        switch ($step) {
            case 'pembimbing':
                if ($permohonan->status_pembimbing == 'approved') return 'completed';
                if ($permohonan->status_pembimbing == 'rejected') return 'rejected';
                if (in_array($permohonan->status, ['submitted', 'review_pembimbing'])) return 'active';
                return 'pending';
                
            case 'staf':
                if (!empty($permohonan->file_surat_izin_staf)) return 'completed';
                if ($permohonan->status == 'approved') return 'active';
                return 'pending';
                
            case 'completed':
                return ($permohonan->status == 'completed') ? 'completed' : 'pending';
                
            default:
                return 'pending';
        }
    }

    /**
     * Get current step untuk UI
     */
    private function _get_current_step($permohonan) {
        if ($permohonan->status == 'completed') return 4;
        if (!empty($permohonan->file_surat_izin_staf)) return 3;
        if ($permohonan->status_pembimbing == 'approved') return 3;
        if (in_array($permohonan->status, ['submitted', 'review_pembimbing'])) return 2;
        return 1;
    }

    /**
     * Get progress percentage untuk progress bar
     */
    private function _get_progress_percentage($permohonan) {
        switch ($permohonan->status) {
            case 'submitted': return 25;
            case 'review_pembimbing': return 40;
            case 'approved': return 60;
            case 'surat_ready': return 80;
            case 'completed': return 100;
            case 'rejected': return 0;
            default: return 10;
        }
    }

    // =================================================================
    // JAVASCRIPT & CSS HELPERS
    // =================================================================

    /**
     * JavaScript untuk halaman index
     */
    private function _get_index_scripts() {
        return '
        <script>
        $(document).ready(function() {
            // Initialize tooltips
            $("[data-toggle=\"tooltip\"]").tooltip();
            
            // Initialize DataTable if exists
            if ($.fn.DataTable) {
                $(".datatable").DataTable({
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
                    }
                });
            }
            
            // Refresh button
            $(".btn-refresh").click(function() {
                location.reload();
            });
        });
        </script>';
    }

    /**
     * JavaScript untuk form pengajuan
     */
    private function _get_form_scripts() {
        return '
        <script>
        $(document).ready(function() {
            // File upload preview
            $("#file_proposal_revisi").change(function() {
                const file = this.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        alert("Ukuran file terlalu besar. Maksimal 2MB");
                        $(this).val("");
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $("#file_preview").text(file.name + " (" + (file.size/1024/1024).toFixed(2) + " MB)");
                        $("input[name=\"file_proposal_revisi\"]").val(e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Form validation
            $("#form_pengajuan").submit(function(e) {
                if (!$("input[name=\"file_proposal_revisi\"]").val()) {
                    alert("File proposal revisi wajib diupload");
                    e.preventDefault();
                    return false;
                }
                
                // Confirm submission
                if (!confirm("Yakin ingin mengajukan izin penelitian ini?")) {
                    e.preventDefault();
                    return false;
                }
            });
        });
        </script>';
    }

    /**
     * JavaScript untuk halaman detail
     */
    private function _get_detail_scripts() {
        return '
        <script>
        $(document).ready(function() {
            // Auto refresh status setiap 30 detik
            setInterval(function() {
                // Implementasi AJAX refresh jika diperlukan
            }, 30000);
            
            // Download tracking
            $(".btn-download").click(function() {
                // Track download event jika diperlukan
            });
        });
        </script>';
    }

    /**
     * CSS untuk form pengajuan
     */
    private function _get_form_styles() {
        return '
        <style>
        .form-step {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        .file-upload-area:hover {
            border-color: #007bff;
            background: #e3f2fd;
        }
        .progress-bar-custom {
            height: 6px;
            border-radius: 10px;
        }
        .requirement-item {
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 4px;
        }
        .requirement-ok {
            background: #d4edda;
            color: #155724;
        }
        .requirement-warning {
            background: #fff3cd;
            color: #856404;
        }
        </style>';
    }
}

/* End of file Penelitian.php */
/* Location: ./application/controllers/mahasiswa/Penelitian.php */