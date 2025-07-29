<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Controller untuk Dosen - FIXED VERSION
 * 
 * Controller untuk mengelola seminar proposal dari perspektif dosen pembimbing
 * Menggunakan template existing dan helper function approach untuk badge counter
 * 
 * Features:
 * 1. Dashboard dengan statistics dan overview
 * 2. Detail pengajuan dengan validasi syarat jurnal bimbingan - FIXED
 * 3. Rekomendasi (setujui/tolak) dengan email notification
 * 4. Helper functions untuk badge counter dan utilities
 * 
 * File: application/controllers/dosen/Seminar_proposal.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Seminar Proposal
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.1 (Error Fixed)
 */
class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');
        $this->load->helper('date');
        $this->load->helper('text'); // TAMBAHAN: Load text helper untuk character_limiter
        
        // Load model dengan error handling
        try {
            $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        } catch (Exception $e) {
            log_message('error', 'Error loading seminar model: ' . $e->getMessage());
        }
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard seminar proposal untuk dosen
     * Menggunakan template existing dosen.php
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // Prepare data untuk view dengan error handling
        $data = [
            'pengajuan_review' => $this->_get_pengajuan_perlu_review($dosen_id),
            'riwayat_rekomendasi' => $this->_get_riwayat_rekomendasi($dosen_id),
            'perlu_penilaian' => $this->_get_seminar_perlu_penilaian($dosen_id),
            'stats' => $this->_get_statistics($dosen_id)
        ];
        
        // Pastikan semua data tidak null
        $data['pengajuan_review'] = $data['pengajuan_review'] ?: [];
        $data['riwayat_rekomendasi'] = $data['riwayat_rekomendasi'] ?: [];
        $data['perlu_penilaian'] = $data['perlu_penilaian'] ?: [];
        $data['stats'] = $data['stats'] ?: ['total' => 0, 'perlu_review' => 0, 'disetujui' => 0, 'ditolak' => 0];
        
        // Load view langsung dengan data
        $this->load->view('template/dosen', [
            'title' => 'Seminar Proposal',
            'content' => $this->load->view('dosen/seminar_proposal/index', $data, TRUE),
            'script' => $this->_get_index_script() // JavaScript untuk halaman index
        ]);
    }

    /**
     * Detail pengajuan seminar proposal - FIXED VERSION
     * Menggunakan template existing dosen.php
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
        
        // FIXED: Safe jurnal requirement check
        $jurnal_requirement = $this->_safe_check_jurnal_requirement($seminar->proposal_id);
        
        // FIXED: Safe jurnal bimbingan retrieval
        $jurnal_bimbingan = $this->_safe_get_jurnal_bimbingan($seminar->proposal_id);
        
        // Prepare data untuk view
        $data = [
            'seminar' => $seminar,
            'jurnal_requirement' => $jurnal_requirement,
            'jurnal_bimbingan' => $jurnal_bimbingan
        ];
        
        // Load view langsung dengan data
        $this->load->view('template/dosen', [
            'title' => 'Detail Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/seminar_proposal/detail', $data, TRUE),
            'script' => $this->_get_detail_script() // JavaScript untuk halaman detail
        ]);
    }

    /**
     * View file proposal - FIXED PATH
     */
    public function view_file($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail seminar dengan validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar || empty($seminar->file_proposal)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // FIXED: Path yang benar sesuai directory
        $file_path = FCPATH . 'uploads/seminar_proposal/proposal_files/' . $seminar->file_proposal;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Set headers untuk download/view
        $mime_type = mime_content_type($file_path);
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: inline; filename="' . $seminar->file_proposal . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }

    /**
     * Proses rekomendasi seminar proposal - ENHANCED dengan EMAIL NOTIFICATION
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
        
        // Process rekomendasi
        $this->db->trans_start();
        
        try {
            $update_data = [
                'status_pembimbing' => $rekomendasi == 'approved' ? 'approved' : 'rejected',
                'komentar_pembimbing' => $komentar,
                'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                'reviewed_by_pembimbing' => $dosen_id,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Update status berdasarkan rekomendasi
            if ($rekomendasi == 'approved') {
                // DISETUJUI: Lanjut ke Kaprodi
                $update_data['status'] = 'review_kaprodi';
                $update_data['current_step'] = 'kaprodi';
            } else {
                // DITOLAK: Kembali ke mahasiswa
                $update_data['status'] = 'rejected';
                $update_data['current_step'] = 'mahasiswa';
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_proposal_mahasiswa', $update_data);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            // Kirim email notification sesuai logika workflow
            $this->_kirim_notifikasi_rekomendasi($seminar, $rekomendasi, $komentar, $dosen_id);
            
            // Success message
            $message = $rekomendasi == 'approved' ? 
                'Pengajuan seminar proposal berhasil disetujui! Email notifikasi telah dikirim ke Kaprodi.' : 
                'Pengajuan seminar proposal berhasil ditolak! Email notifikasi telah dikirim ke mahasiswa.';
            
            $this->session->set_flashdata('success', $message);
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error processing rekomendasi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memproses rekomendasi!');
        }
        
        redirect('dosen/seminar_proposal');
    }

    // =================================================================
    // PRIVATE HELPER METHODS - FIXED VERSION
    // =================================================================

    /**
     * Get JavaScript untuk halaman index
     */
    private function _get_index_script() {
        return '
<script>
function rekomendasi(seminarId, action, namaMahasiswa) {
    document.getElementById("modalSeminarId").value = seminarId;
    document.getElementById("modalRekomendasi").value = action;
    document.getElementById("modalMahasiswa").textContent = namaMahasiswa;
    
    const alertDiv = document.getElementById("modalAlert");
    const submitBtn = document.getElementById("modalSubmitBtn");
    
    if (action === "approved") {
        alertDiv.className = "alert alert-success";
        alertDiv.innerHTML = "<i class=\"fas fa-check-circle mr-2\"></i><strong>Menyetujui pengajuan seminar proposal</strong>";
        submitBtn.className = "btn btn-success";
        submitBtn.innerHTML = "<i class=\"fas fa-check mr-2\"></i>Setujui";
    } else {
        alertDiv.className = "alert alert-warning";
        alertDiv.innerHTML = "<i class=\"fas fa-exclamation-triangle mr-2\"></i><strong>Menolak pengajuan seminar proposal</strong>";
        submitBtn.className = "btn btn-danger";
        submitBtn.innerHTML = "<i class=\"fas fa-times mr-2\"></i>Tolak";
    }
    
    // Clear previous comment
    document.getElementById("modalKomentar").value = "";
    
    $("#rekomendasiModal").modal("show");
}

// Form validation
$(document).ready(function() {
    $("#rekomendasiModal form").on("submit", function(e) {
        const rekomendasi = document.getElementById("modalRekomendasi").value;
        const komentar = document.getElementById("modalKomentar").value.trim();
        
        if (rekomendasi === "rejected" && komentar === "") {
            e.preventDefault();
            alert("Komentar wajib diisi untuk penolakan!");
            return false;
        }
        
        const action = rekomendasi === "approved" ? "menyetujui" : "menolak";
        return confirm("Yakin ingin " + action + " pengajuan seminar proposal ini?");
    });
});
</script>';
    }

    /**
     * Get JavaScript untuk halaman detail
     */
    private function _get_detail_script() {
        return '
<script>
function validateReject() {
    const komentar = document.querySelector("textarea[name=\"komentar_pembimbing\"]").value.trim();
    if (komentar === "") {
        alert("Komentar wajib diisi untuk penolakan!");
        return false;
    }
    return confirm("Yakin ingin menolak pengajuan seminar proposal ini?");
}
</script>';
    }

    /**
     * Kirim notifikasi email berdasarkan rekomendasi dosen
     */
    private function _kirim_notifikasi_rekomendasi($seminar, $rekomendasi, $komentar, $dosen_id) {
        try {
            // Get dosen info
            $dosen = $this->_get_dosen_by_id($dosen_id);
            $dosen_nama = $dosen ? $dosen->nama : 'Dosen Pembimbing';
            
            if ($rekomendasi == 'approved') {
                // DISETUJUI: Kirim email ke Kaprodi
                $this->_kirim_email_ke_kaprodi($seminar, $dosen_nama);
            } else {
                // DITOLAK: Kirim email ke Mahasiswa
                $this->_kirim_email_ke_mahasiswa($seminar, $dosen_nama, $komentar);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error sending notification: ' . $e->getMessage());
        }
    }

    /**
     * Kirim email ke mahasiswa jika pengajuan DITOLAK
     */
    private function _kirim_email_ke_mahasiswa($seminar, $dosen_nama, $komentar) {
        // Konfigurasi email
        $config = $this->_get_email_config();
        $this->email->initialize($config);
        
        $subject = 'Pengajuan Seminar Proposal Perlu Diperbaiki - STK Santo Yakobus';
        
        $message = $this->_get_template_email_mahasiswa_ditolak($seminar, $dosen_nama, $komentar);
        
        $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($seminar->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        
        if (!$this->email->send()) {
            log_message('error', 'Failed to send rejection email to student: ' . $this->email->print_debugger());
        } else {
            log_message('info', 'Rejection email sent to student: ' . $seminar->email_mahasiswa);
        }
    }

    /**
     * Kirim email ke kaprodi jika pengajuan DISETUJUI
     */
    private function _kirim_email_ke_kaprodi($seminar, $dosen_nama) {
        // Get info kaprodi
        $kaprodi = $this->_get_kaprodi_info();
        if (!$kaprodi) {
            log_message('error', 'Kaprodi info not found');
            return;
        }
        
        // Konfigurasi email
        $config = $this->_get_email_config();
        $this->email->initialize($config);
        
        $subject = 'Pengajuan Seminar Proposal Perlu Review - STK Santo Yakobus';
        
        $message = $this->_get_template_email_kaprodi_review($seminar, $dosen_nama, $kaprodi);
        
        $this->email->from('stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($kaprodi->email);
        $this->email->subject($subject);
        $this->email->message($message);
        
        if (!$this->email->send()) {
            log_message('error', 'Failed to send review email to kaprodi: ' . $this->email->print_debugger());
        } else {
            log_message('info', 'Review email sent to kaprodi: ' . $kaprodi->email);
        }
    }

    /**
     * Email configuration
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
            'smtp_crypto' => 'tls'
        ];
    }

    /**
     * Template email untuk mahasiswa ketika pengajuan DITOLAK
     */
    private function _get_template_email_mahasiswa_ditolak($seminar, $dosen_nama, $komentar) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Pengajuan Seminar Proposal Perlu Diperbaiki</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white;'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>📝 Pengajuan Perlu Diperbaiki</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px;'>
                    <p>Kepada Yth.<br>
                    <strong>{$seminar->nama_mahasiswa}</strong><br>
                    NIM: {$seminar->nim}</p>
                    
                    <p>Pengajuan seminar proposal Anda telah direview oleh dosen pembimbing, namun <strong>perlu diperbaiki</strong> sebelum dapat dilanjutkan ke tahap berikutnya.</p>
                    
                    <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #721c24; margin: 0 0 10px 0;'>📋 Detail Pengajuan:</h4>
                        <ul style='color: #721c24; margin: 0;'>
                            <li><strong>Judul:</strong> {$seminar->judul}</li>
                            <li><strong>Dosen Pembimbing:</strong> {$dosen_nama}</li>
                            <li><strong>Tanggal Review:</strong> " . date('d F Y, H:i') . " WIB</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #856404; margin: 0 0 10px 0;'>💬 Catatan dari Dosen Pembimbing:</h4>
                        <p style='color: #856404; margin: 0; font-style: italic; line-height: 1.6;'>
                            \"{$komentar}\"
                        </p>
                    </div>
                    
                    <div style='background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #0c5460; margin: 0 0 10px 0;'>📋 Langkah Selanjutnya:</h4>
                        <ol style='color: #0c5460; margin: 0; padding-left: 20px;'>
                            <li>Perbaiki proposal sesuai catatan dari dosen pembimbing</li>
                            <li>Ajukan kembali seminar proposal melalui sistem SIM-TA</li>
                            <li>Pastikan semua perbaikan telah dilakukan sebelum mengajukan ulang</li>
                            <li>Konsultasikan dengan dosen pembimbing jika ada hal yang kurang jelas</li>
                        </ol>
                    </div>
                    
                    <p style='margin-top: 20px; text-align: center;'>
                        <a href='" . base_url('mahasiswa/seminar_proposal') . "' 
                           style='background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                           🔄 Ajukan Ulang Seminar Proposal
                        </a>
                    </p>
                    
                    <p style='margin-top: 20px; color: #6c757d; font-size: 14px;'>
                        Terima kasih atas upaya perbaikan yang akan Anda lakukan. Jangan ragu untuk berkonsultasi dengan dosen pembimbing Anda.
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                        STK Santo Yakobus Merauke<br>
                        Email ini dikirim secara otomatis, mohon tidak membalas langsung.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Template email untuk kaprodi ketika pengajuan DISETUJUI
     */
    private function _get_template_email_kaprodi_review($seminar, $dosen_nama, $kaprodi) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Pengajuan Seminar Proposal Perlu Review</title>
        </head>
        <body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white;'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>📋 Pengajuan Seminar Proposal Perlu Review</h1>
                    <p style='margin: 5px 0 0 0; opacity: 0.9;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px;'>
                    <p>Kepada Yth.<br>
                    <strong>{$kaprodi->nama}</strong><br>
                    Ketua Program Studi</p>
                    
                    <p>Terdapat pengajuan seminar proposal yang telah <strong>disetujui oleh dosen pembimbing</strong> dan memerlukan review dari Anda:</p>
                    
                    <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #155724; margin: 0 0 10px 0;'>📋 Detail Mahasiswa:</h4>
                        <ul style='color: #155724; margin: 0;'>
                            <li><strong>Nama:</strong> {$seminar->nama_mahasiswa}</li>
                            <li><strong>NIM:</strong> {$seminar->nim}</li>
                            <li><strong>Program Studi:</strong> {$seminar->nama_prodi}</li>
                            <li><strong>Dosen Pembimbing:</strong> {$dosen_nama}</li>
                        </ul>
                    </div>
                    
                    <div style='background-color: #e2e3e5; border: 1px solid #d6d8db; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #383d41; margin: 0 0 10px 0;'>📄 Judul Proposal:</h4>
                        <p style='color: #383d41; margin: 0; font-weight: 600; line-height: 1.6;'>
                            {$seminar->judul}
                        </p>
                    </div>
                    
                    <div style='background-color: #cce5ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #004085; margin: 0 0 10px 0;'>📋 Tugas Review Kaprodi:</h4>
                        <ol style='color: #004085; margin: 0; padding-left: 20px;'>
                            <li>Review kelengkapan dokumen proposal</li>
                            <li>Verifikasi plagiarisme menggunakan sistem yang tersedia</li>
                            <li>Evaluasi kelayakan topik penelitian</li>
                            <li>Menentukan dosen penguji jika disetujui</li>
                            <li>Penjadwalan seminar proposal</li>
                        </ol>
                    </div>
                    
                    <p style='margin-top: 20px; text-align: center;'>
                        <a href='" . base_url('kaprodi/seminar_proposal') . "' 
                           style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                           📊 Review Pengajuan Sekarang
                        </a>
                    </p>
                    
                    <p style='margin-top: 20px; color: #6c757d; font-size: 14px;'>
                        Target waktu review: <strong>3-5 hari kerja</strong> dari tanggal pengajuan.
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                        STK Santo Yakobus Merauke<br>
                        Email ini dikirim secara otomatis, mohon tidak membalas langsung.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get dosen by ID - HELPER METHOD
     */
    private function _get_dosen_by_id($dosen_id) {
        try {
            $this->db->where('id', $dosen_id);
            return $this->db->get('dosen')->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get kaprodi information
     */
    private function _get_kaprodi_info() {
        try {
            $this->db->select('d.*, p.nama as nama_prodi');
            $this->db->from('dosen d');
            $this->db->join('prodi p', 'd.prodi_id = p.id', 'left');
            $this->db->where('d.level', '4'); // Level kaprodi
            $this->db->limit(1);
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting kaprodi info: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * FIXED: Safe jurnal requirement check
     */
    private function _safe_check_jurnal_requirement($proposal_id) {
        try {
            // Cek apakah model tersedia dan method exists
            if (isset($this->seminar_model) && method_exists($this->seminar_model, 'check_jurnal_requirement')) {
                return $this->seminar_model->check_jurnal_requirement($proposal_id);
            }
            
            // Fallback manual check
            return $this->_manual_check_jurnal_requirement($proposal_id);
            
        } catch (Exception $e) {
            log_message('error', 'Error checking jurnal requirement: ' . $e->getMessage());
            
            // Return safe default
            return [
                'eligible' => false,
                'jurnal_validated_count' => 0,
                'minimum_required' => 8,
                'missing' => 8,
                'message' => 'Error checking jurnal requirement'
            ];
        }
    }

    /**
     * Manual jurnal requirement check sebagai fallback
     */
    private function _manual_check_jurnal_requirement($proposal_id) {
        try {
            $minimum_required = 8;
            
            // Check if table exists
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return [
                    'eligible' => false,
                    'jurnal_validated_count' => 0,
                    'minimum_required' => $minimum_required,
                    'missing' => $minimum_required,
                    'message' => 'Tabel jurnal bimbingan belum tersedia'
                ];
            }
            
            // Count validated jurnal
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $count = $this->db->count_all_results('jurnal_bimbingan');
            
            $eligible = $count >= $minimum_required;
            $missing = max(0, $minimum_required - $count);
            
            return [
                'eligible' => $eligible,
                'jurnal_validated_count' => $count,
                'minimum_required' => $minimum_required,
                'missing' => $missing,
                'message' => $eligible ? 
                    'Memenuhi syarat untuk mengajukan seminar proposal' : 
                    "Perlu {$missing} jurnal bimbingan lagi yang divalidasi dosen"
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error in manual jurnal check: ' . $e->getMessage());
            return [
                'eligible' => false,
                'jurnal_validated_count' => 0,
                'minimum_required' => 8,
                'missing' => 8,
                'message' => 'Error checking jurnal requirement'
            ];
        }
    }

    /**
     * FIXED: Safe get jurnal bimbingan
     */
    private function _safe_get_jurnal_bimbingan($proposal_id) {
        try {
            // Check if table exists
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return [];
            }
            
            $this->db->select('jb.*, d.nama as nama_validator');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
            $this->db->where('jb.proposal_id', $proposal_id);
            $this->db->where('jb.status_validasi', '1'); // Sudah divalidasi
            $this->db->order_by('jb.pertemuan_ke', 'ASC');
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting jurnal bimbingan: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pengajuan yang perlu direview
     */
    private function _get_pengajuan_perlu_review($dosen_id) {
        try {
            $this->db->select('
                spm.*,
                pm.judul,
                m.nim,
                m.nama as nama_mahasiswa,
                p.nama as nama_prodi
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('spm.status', ['submitted', 'review_pembimbing']);
            $this->db->order_by('spm.created_at', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat rekomendasi
     */
    private function _get_riwayat_rekomendasi($dosen_id) {
        try {
            $this->db->select('
                spm.*,
                pm.judul,
                m.nim,
                m.nama as nama_mahasiswa,
                p.nama as nama_prodi
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('spm.status', ['approved', 'rejected', 'scheduled', 'completed']);
            $this->db->order_by('spm.updated_at', 'DESC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat rekomendasi: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get seminar yang perlu penilaian - UPDATED
     */
    private function _get_seminar_perlu_penilaian($dosen_id) {
        try {
            $this->db->select('
                spm.*,
                pm.judul,
                pm.dosen_penguji_id,
                pm.dosen_penguji2_id,
                m.nim,
                m.nama as nama_mahasiswa,
                p.nama as nama_prodi,
                psp.id as penilaian_id,
                psp.status_penilaian
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id AND psp.dinilai_oleh = ' . $dosen_id, 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            
            // PERUBAHAN UTAMA: Tampilkan sejak status 'submitted'
            // Hapus pembatasan tanggal seminar dan status 'scheduled'
            $this->db->where_in('spm.status', ['submitted', 'review_pembimbing', 'review_kaprodi', 'approved', 'scheduled', 'completed']);
            
            $this->db->order_by('spm.created_at', 'DESC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar penilaian: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistics untuk dashboard
     */
    private function _get_statistics($dosen_id) {
        try {
            $stats = [];
            
            // Total pengajuan
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $stats['total'] = $this->db->count_all_results();
            
            // Perlu review
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('spm.status', ['submitted', 'review_pembimbing']);
            $stats['perlu_review'] = $this->db->count_all_results();
            
            // Disetujui
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('spm.status', ['approved', 'scheduled', 'completed']);
            $stats['disetujui'] = $this->db->count_all_results();
            
            // Ditolak
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('spm.status', 'rejected');
            $stats['ditolak'] = $this->db->count_all_results();
            
            return $stats;
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return [
                'total' => 0,
                'perlu_review' => 0,
                'disetujui' => 0,
                'ditolak' => 0
            ];
        }
    }

    /**
     * Get detail seminar dengan validasi ownership
     */
    private function _get_seminar_detail($seminar_id, $dosen_id) {
        try {
            $this->db->select('
                spm.*,
                pm.judul,
                pm.ringkasan,
                pm.jenis_penelitian,
                pm.lokasi_penelitian,
                pm.uraian_masalah,
                pm.file_draft_proposal,
                pm.dosen_penguji_id,
                pm.dosen_penguji2_id,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                p.nama as nama_prodi,
                d1.nama as nama_penguji1,
                d2.nama as nama_penguji2
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->join('dosen d1', 'pm.dosen_penguji_id = d1.id', 'left');
            $this->db->join('dosen d2', 'pm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('spm.id', $seminar_id);
            $this->db->where('pm.dosen_id', $dosen_id); // Validasi ownership
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }
    
    // ====================================================================
    // 2. TAMBAHKAN METHODS BARU INI SEBELUM CLOSING CLASS BRACE }
    // ====================================================================
    /*
    LETAKKAN METHODS INI SEBELUM BARIS TERAKHIR } DI FILE CONTROLLER
    */
    
    /**
     * Halaman input penilaian seminar proposal - FIXED VERSION
     */
    public function penilaian($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Debug log untuk troubleshooting
        log_message('debug', 'Accessing penilaian page for seminar_id: ' . $seminar_id . ', dosen_id: ' . $dosen_id);
        
        // Get detail seminar dengan validasi ownership
        $seminar = $this->_get_seminar_detail_for_penilaian($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Handle form submission
        if ($this->input->method() === 'post') {
            $this->_process_penilaian($seminar_id, $dosen_id, $seminar);
            return;
        }
        
        // Get existing penilaian jika ada
        $existing_penilaian = $this->_get_existing_penilaian($seminar_id, $dosen_id);
        
        // Get info dosen penguji untuk ditampilkan (optional)
        $dosen_penguji1 = null;
        $dosen_penguji2 = null;
        if (isset($seminar->dosen_penguji_id) && $seminar->dosen_penguji_id) {
            $dosen_penguji1 = $this->_get_dosen_by_id($seminar->dosen_penguji_id);
        }
        if (isset($seminar->dosen_penguji2_id) && $seminar->dosen_penguji2_id) {
            $dosen_penguji2 = $this->_get_dosen_by_id($seminar->dosen_penguji2_id);
        }
        
        // Prepare data untuk view
        $view_data = [
            'seminar' => $seminar,
            'penilaian' => $existing_penilaian,
            'dosen_penguji1' => $dosen_penguji1,
            'dosen_penguji2' => $dosen_penguji2,
            'is_edit' => !empty($existing_penilaian)
        ];
        
        // Debug log
        log_message('debug', 'Penilaian view data: ' . print_r($view_data, true));
        
        // Template data untuk dosen.php
        $template_data = [
            'title' => 'Penilaian Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/seminar_proposal/penilaian', $view_data, TRUE),
            'script' => $this->_get_penilaian_script()
        ];
        
        // Load template dosen
        $this->load->view('template/dosen', $template_data);
    }
    
        /**
         * Proses simpan penilaian
         */
        private function _proses_simpan_penilaian($seminar_id, $seminar, $dosen_id) {
            $action_type = $this->input->post('action_type'); // 'draft' atau 'publish'
            
            // Ambil data form
            $data_penilaian = [
                'seminar_proposal_id' => $seminar_id,
                'mahasiswa_id' => $seminar->mahasiswa_id,
                'proposal_id' => $seminar->proposal_id,
                'catatan_latar_belakang' => $this->input->post('catatan_latar_belakang'),
                'catatan_tinjauan_pustaka' => $this->input->post('catatan_tinjauan_pustaka'),
                'catatan_landasan_teori' => $this->input->post('catatan_landasan_teori'),
                'catatan_metodologi' => $this->input->post('catatan_metodologi'),
                'catatan_sistematika' => $this->input->post('catatan_sistematika'),
                'catatan_umum' => $this->input->post('catatan_umum'),
                'nilai_substansi_metode' => $this->input->post('nilai_substansi_metode'),
                'nilai_presentasi_teknik' => $this->input->post('nilai_presentasi_teknik'),
                'nilai_penguasaan_diskusi' => $this->input->post('nilai_penguasaan_diskusi'),
                'rekomendasi' => $this->input->post('rekomendasi'),
                'keterangan_rekomendasi' => $this->input->post('keterangan_rekomendasi'),
                'status_penilaian' => ($action_type == 'publish') ? 'published' : 'draft',
                'dinilai_oleh' => $dosen_id,
                'role_penilai' => 'dosen_pembimbing',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Hitung nilai akhir dan konversi huruf
            if (!empty($data_penilaian['nilai_substansi_metode']) && 
                !empty($data_penilaian['nilai_presentasi_teknik']) && 
                !empty($data_penilaian['nilai_penguasaan_diskusi'])) {
                
                $nilai_akhir = $this->_hitung_nilai_akhir(
                    $data_penilaian['nilai_substansi_metode'],
                    $data_penilaian['nilai_presentasi_teknik'],
                    $data_penilaian['nilai_penguasaan_diskusi']
                );
                
                $data_penilaian['nilai_akhir'] = $nilai_akhir;
                $data_penilaian['nilai_huruf'] = $this->_konversi_nilai_huruf($nilai_akhir);
            }
            
            // Validasi untuk publikasi
            if ($action_type == 'publish') {
                $validation_errors = $this->_validate_penilaian_for_publish($data_penilaian);
                if (!empty($validation_errors)) {
                    $this->session->set_flashdata('error', implode('<br>', $validation_errors));
                    redirect('dosen/seminar_proposal/penilaian/' . $seminar_id);
                    return;
                }
                $data_penilaian['published_at'] = date('Y-m-d H:i:s');
            }
            
            $this->db->trans_start();
            
            try {
                // Cek apakah sudah ada penilaian
                $existing = $this->_get_existing_penilaian($seminar_id, $dosen_id);
                
                if ($existing) {
                    // Update existing
                    $this->db->where('id', $existing->id);
                    $this->db->update('penilaian_seminar_proposal', $data_penilaian);
                } else {
                    // Insert new
                    $data_penilaian['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('penilaian_seminar_proposal', $data_penilaian);
                }
                
                // Jika publish, update status seminar dan kirim notifikasi
                if ($action_type == 'publish') {
                    $this->_update_seminar_status_after_penilaian($seminar_id, $data_penilaian['rekomendasi']);
                    $this->_kirim_notifikasi_penilaian($seminar, $data_penilaian);
                }
                
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Gagal menyimpan penilaian');
                }
                
                $message = ($action_type == 'publish') ? 
                    'Penilaian berhasil disimpan dan dipublikasi! Mahasiswa akan mendapat notifikasi.' : 
                    'Draft penilaian berhasil disimpan. Anda dapat melanjutkan edit nanti.';
                    
                $this->session->set_flashdata('success', $message);
                
            } catch (Exception $e) {
                $this->db->trans_rollback();
                log_message('error', 'Error simpan penilaian: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
            
            redirect('dosen/seminar_proposal');
        }
    
        /**
         * Get detail seminar untuk penilaian - FIXED VERSION
         */
        private function _get_seminar_detail_for_penilaian($seminar_id, $dosen_id) {
            try {
                $this->db->select('
                    spm.*, 
                    pm.judul, pm.mahasiswa_id, pm.dosen_id, 
                    pm.dosen_penguji_id, pm.dosen_penguji2_id,
                    m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                    p.nama as nama_prodi, 
                    d.nama as nama_pembimbing
                ');
                $this->db->from('seminar_proposal_mahasiswa spm');
                $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
                $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
                $this->db->join('prodi p', 'm.prodi_id = p.id');
                $this->db->join('dosen d', 'pm.dosen_id = d.id');
                $this->db->where('spm.id', $seminar_id);
                $this->db->where('pm.dosen_id', $dosen_id);
                
                // Bisa diakses sejak status 'submitted'
                $this->db->where_in('spm.status', ['submitted', 'review_pembimbing', 'review_kaprodi', 'approved', 'scheduled', 'completed']);
                
                $result = $this->db->get()->row();
                
                // Debug log
                if (!$result) {
                    log_message('debug', 'No seminar found for seminar_id: ' . $seminar_id . ', dosen_id: ' . $dosen_id);
                    log_message('debug', 'Last query: ' . $this->db->last_query());
                }
                
                return $result;
            } catch (Exception $e) {
                log_message('error', 'Error getting seminar detail for penilaian: ' . $e->getMessage());
                return null;
            }
        }
        
        /**
         * Process penyimpanan penilaian - FIXED VERSION
         */
        private function _process_penilaian($seminar_id, $dosen_id, $seminar) {
            // Validasi input basic
            $action_type = $this->input->post('action_type'); // 'draft' atau 'publish'
            
            if (!in_array($action_type, ['draft', 'publish'])) {
                $this->session->set_flashdata('error', 'Action type tidak valid!');
                redirect('dosen/seminar_proposal/penilaian/' . $seminar_id);
                return;
            }
            
            // Jika publish, validasi field required
            if ($action_type == 'publish') {
                $required_fields = ['nilai_substansi_metode', 'nilai_presentasi_teknik', 'nilai_penguasaan_diskusi', 'rekomendasi'];
                foreach ($required_fields as $field) {
                    if (empty($this->input->post($field))) {
                        $this->session->set_flashdata('error', 'Field ' . str_replace('_', ' ', $field) . ' wajib diisi untuk publikasi!');
                        redirect('dosen/seminar_proposal/penilaian/' . $seminar_id);
                        return;
                    }
                }
            }
            
            $this->db->trans_start();
            
            try {
                // Hitung nilai akhir dengan bobot sesuai form
                $substansi = floatval($this->input->post('nilai_substansi_metode'));
                $presentasi = floatval($this->input->post('nilai_presentasi_teknik')); 
                $penguasaan = floatval($this->input->post('nilai_penguasaan_diskusi'));
                
                // Bobot: Substansi 50%, Presentasi 20%, Penguasaan 30%
                $nilai_akhir = null;
                if ($substansi > 0 && $presentasi > 0 && $penguasaan > 0) {
                    $nilai_akhir = ($substansi * 0.5) + ($presentasi * 0.2) + ($penguasaan * 0.3);
                }
                
                // Konversi ke nilai huruf
                $nilai_huruf = null;
                if ($nilai_akhir !== null) {
                    $nilai_huruf = $this->_convert_to_grade($nilai_akhir);
                }
                
                // Data penilaian
                $penilaian_data = [
                    'seminar_proposal_id' => $seminar_id,
                    'mahasiswa_id' => $seminar->mahasiswa_id,
                    'proposal_id' => $seminar->proposal_id,
                    'dinilai_oleh' => $dosen_id,
                    'role_penilai' => 'dosen_pembimbing',
                    'catatan_latar_belakang' => $this->input->post('catatan_latar_belakang'),
                    'catatan_tinjauan_pustaka' => $this->input->post('catatan_tinjauan_pustaka'),
                    'catatan_landasan_teori' => $this->input->post('catatan_landasan_teori'),
                    'catatan_metodologi' => $this->input->post('catatan_metodologi'),
                    'catatan_sistematika' => $this->input->post('catatan_sistematika'),
                    'catatan_umum' => $this->input->post('catatan_umum'),
                    'nilai_substansi_metode' => $substansi > 0 ? $substansi : null,
                    'nilai_presentasi_teknik' => $presentasi > 0 ? $presentasi : null,
                    'nilai_penguasaan_diskusi' => $penguasaan > 0 ? $penguasaan : null,
                    'nilai_akhir' => $nilai_akhir,
                    'nilai_huruf' => $nilai_huruf,
                    'rekomendasi' => $this->input->post('rekomendasi'),
                    'keterangan_rekomendasi' => $this->input->post('keterangan_rekomendasi'),
                    'status_penilaian' => $action_type == 'publish' ? 'published' : 'draft',
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($action_type == 'publish') {
                    $penilaian_data['published_at'] = date('Y-m-d H:i:s');
                }
                
                // Cek apakah sudah ada penilaian
                $existing = $this->_get_existing_penilaian($seminar_id, $dosen_id);
                
                if ($existing) {
                    // Update existing
                    $this->db->where('id', $existing->id);
                    $this->db->update('penilaian_seminar_proposal', $penilaian_data);
                    log_message('debug', 'Updated penilaian ID: ' . $existing->id);
                } else {
                    // Insert new
                    $penilaian_data['created_at'] = date('Y-m-d H:i:s');
                    $this->db->insert('penilaian_seminar_proposal', $penilaian_data);
                    $insert_id = $this->db->insert_id();
                    log_message('debug', 'Inserted new penilaian ID: ' . $insert_id);
                }
                
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    throw new Exception('Gagal menyimpan penilaian - transaction failed');
                }
                
                $message = ($action_type == 'publish') ? 
                    'Penilaian berhasil disimpan dan dipublikasi!' : 
                    'Draft penilaian berhasil disimpan.';
                    
                $this->session->set_flashdata('success', $message);
                
            } catch (Exception $e) {
                $this->db->trans_rollback();
                log_message('error', 'Error simpan penilaian: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            }
            
            redirect('dosen/seminar_proposal');
        }
    
        /**
         * Get existing penilaian - HELPER METHOD
         */
        private function _get_existing_penilaian($seminar_id, $dosen_id) {
            try {
                $this->db->where('seminar_proposal_id', $seminar_id);
                $this->db->where('dinilai_oleh', $dosen_id);
                return $this->db->get('penilaian_seminar_proposal')->row();
            } catch (Exception $e) {
                log_message('error', 'Error getting existing penilaian: ' . $e->getMessage());
                return null;
            }
        }
    
        /**
         * Hitung nilai akhir berdasarkan bobot
         */
        private function _hitung_nilai_akhir($substansi, $presentasi, $penguasaan) {
            // Bobot: Substansi & Metode (50%), Presentasi & Teknik (20%), Penguasaan & Diskusi (30%)
            return round(($substansi * 0.5) + ($presentasi * 0.2) + ($penguasaan * 0.3), 2);
        }
    
        /**
         * Konversi nilai angka ke huruf - HELPER METHOD
         */
        private function _convert_to_grade($nilai) {
            if ($nilai >= 80) return 'A';
            if ($nilai >= 70) return 'B';
            if ($nilai >= 60) return 'C';
            if ($nilai >= 50) return 'D';
            return 'E';
        }
    
        /**
         * Validasi penilaian untuk publikasi
         */
        private function _validate_penilaian_for_publish($data) {
            $errors = [];
            
            // Validasi nilai wajib diisi
            if (empty($data['nilai_substansi_metode']) || $data['nilai_substansi_metode'] < 0 || $data['nilai_substansi_metode'] > 100) {
                $errors[] = 'Nilai Substansi & Metode harus diisi (0-100)';
            }
            if (empty($data['nilai_presentasi_teknik']) || $data['nilai_presentasi_teknik'] < 0 || $data['nilai_presentasi_teknik'] > 100) {
                $errors[] = 'Nilai Presentasi & Teknik harus diisi (0-100)';
            }
            if (empty($data['nilai_penguasaan_diskusi']) || $data['nilai_penguasaan_diskusi'] < 0 || $data['nilai_penguasaan_diskusi'] > 100) {
                $errors[] = 'Nilai Penguasaan & Diskusi harus diisi (0-100)';
            }
            
            // Validasi rekomendasi wajib diisi
            if (empty($data['rekomendasi'])) {
                $errors[] = 'Rekomendasi hasil seminar harus dipilih';
            }
            
            return $errors;
        }
    
        /**
         * Update status seminar setelah penilaian
         */
        private function _update_seminar_status_after_penilaian($seminar_id, $rekomendasi) {
            try {
                $update_data = ['updated_at' => date('Y-m-d H:i:s')];
                
                if ($rekomendasi == 'ditolak') {
                    // Jika ditolak, mahasiswa harus ajukan ulang
                    $update_data['status'] = 'rejected';
                    $update_data['current_step'] = 'mahasiswa';
                } else {
                    // Jika diterima (dengan/tanpa revisi), lanjut ke penelitian
                    $update_data['status'] = 'completed';
                    $update_data['current_step'] = 'completed';
                    
                    // Update workflow status di proposal_mahasiswa ke fase penelitian
                    $proposal_id = $this->db->select('proposal_id')
                                           ->from('seminar_proposal_mahasiswa')
                                           ->where('id', $seminar_id)
                                           ->get()->row()->proposal_id;
                    
                    $this->db->where('id', $proposal_id);
                    $this->db->update('proposal_mahasiswa', ['workflow_status' => 'penelitian']);
                }
                
                $this->db->where('id', $seminar_id);
                $this->db->update('seminar_proposal_mahasiswa', $update_data);
                
            } catch (Exception $e) {
                log_message('error', 'Error updating seminar status: ' . $e->getMessage());
            }
        }
    
        /**
         * Kirim notifikasi penilaian ke mahasiswa
         */
        private function _kirim_notifikasi_penilaian($seminar, $penilaian) {
            try {
                // Notifikasi database
                $notif_data = [
                    'jenis' => 'penilaian_seminar_proposal',
                    'untuk_role' => 'mahasiswa',
                    'user_id' => $seminar->mahasiswa_id,
                    'proposal_id' => $seminar->proposal_id,
                    'judul' => 'Penilaian Seminar Proposal Tersedia',
                    'pesan' => 'Penilaian seminar proposal Anda sudah tersedia. Nilai akhir: ' . 
                               $penilaian['nilai_akhir'] . ' (' . $penilaian['nilai_huruf'] . '). ' .
                               'Rekomendasi: ' . ucwords(str_replace('_', ' ', $penilaian['rekomendasi'])),
                    'dibaca' => 0,
                    'tanggal_dibuat' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('notifikasi', $notif_data);
                
            } catch (Exception $e) {
                log_message('error', 'Error sending penilaian notification: ' . $e->getMessage());
            }
        }
    
    /**
     * Script untuk halaman penilaian - HELPER METHOD
     */
    private function _get_penilaian_script() {
        return '
        <script>
        // Auto calculate nilai akhir saat input berubah
        function calculateNilaiAkhir() {
            const substansi = parseFloat(document.querySelector(\'input[name="nilai_substansi_metode"]\').value) || 0;
            const presentasi = parseFloat(document.querySelector(\'input[name="nilai_presentasi_teknik"]\').value) || 0;
            const penguasaan = parseFloat(document.querySelector(\'input[name="nilai_penguasaan_diskusi"]\').value) || 0;
            
            if (substansi > 0 && presentasi > 0 && penguasaan > 0) {
                const nilaiAkhir = (substansi * 0.5) + (presentasi * 0.2) + (penguasaan * 0.3);
                
                let nilaiHuruf = "";
                if (nilaiAkhir >= 80) nilaiHuruf = "A";
                else if (nilaiAkhir >= 70) nilaiHuruf = "B";
                else if (nilaiAkhir >= 60) nilaiHuruf = "C";
                else if (nilaiAkhir >= 50) nilaiHuruf = "D";
                else nilaiHuruf = "E";
                
                document.getElementById("previewNilaiAngka").textContent = nilaiAkhir.toFixed(2);
                document.getElementById("previewNilaiHuruf").textContent = nilaiHuruf;
            } else {
                document.getElementById("previewNilaiAngka").textContent = "-";
                document.getElementById("previewNilaiHuruf").textContent = "-";
            }
        }
        
        // Event listeners
        document.addEventListener("DOMContentLoaded", function() {
            // Add event listeners untuk semua input nilai
            const nilaiInputs = document.querySelectorAll("input[name^=\"nilai_\"]");
            nilaiInputs.forEach(function(input) {
                input.addEventListener("input", calculateNilaiAkhir);
            });
            
            // Calculate on page load
            calculateNilaiAkhir();
            
            // Form validation
            if (document.getElementById("penilaianForm")) {
                document.getElementById("penilaianForm").addEventListener("submit", function(e) {
                    const actionType = document.activeElement.value;
                    
                    if (actionType === "publish") {
                        const substansi = parseFloat(document.querySelector(\'input[name="nilai_substansi_metode"]\').value) || 0;
                        const presentasi = parseFloat(document.querySelector(\'input[name="nilai_presentasi_teknik"]\').value) || 0;
                        const penguasaan = parseFloat(document.querySelector(\'input[name="nilai_penguasaan_diskusi"]\').value) || 0;
                        const rekomendasi = document.querySelector(\'input[name="rekomendasi"]:checked\');
                        
                        if (substansi <= 0 || presentasi <= 0 || penguasaan <= 0) {
                            e.preventDefault();
                            alert("Semua komponen nilai harus diisi dengan nilai > 0 untuk publikasi!");
                            return false;
                        }
                        
                        if (!rekomendasi) {
                            e.preventDefault();
                            alert("Rekomendasi hasil seminar harus dipilih untuk publikasi!");
                            return false;
                        }
                        
                        if (!confirm("Apakah Anda yakin ingin mempublikasi penilaian ini? Penilaian yang sudah dipublikasi tidak dapat diubah lagi.")) {
                            e.preventDefault();
                            return false;
                        }
                    }
                    
                    return true;
                });
            }
        });
        </script>';
    }
}