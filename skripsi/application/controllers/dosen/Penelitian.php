<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Penelitian untuk Dosen - FIXED DATABASE ERRORS
 * 
 * Controller untuk mengelola permohonan izin penelitian dari perspektif dosen pembimbing
 * Sesuai dengan workflow: Mahasiswa Ajukan -> Dosen Review -> Staf Proses
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.2 (Database Error Fixed)
 */
class Penelitian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper(['url', 'date', 'text']);
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard penelitian untuk dosen
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // Prepare data untuk view
        $data = [
            'permohonan_review' => $this->_get_permohonan_perlu_review($dosen_id),
            'riwayat_review' => $this->_get_riwayat_review($dosen_id),
            'stats' => $this->_get_statistics($dosen_id)
        ];
        
        // Load view dengan template dosen
        $this->load->view('template/dosen', [
            'title' => 'Penelitian - Surat Izin Penelitian',
            'content' => $this->load->view('dosen/penelitian/index', $data, TRUE),
            'script' => $this->_get_index_script()
        ]);
    }

    /**
     * Detail permohonan penelitian
     */
    public function detail($permohonan_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail permohonan dengan validasi ownership
        $permohonan = $this->_get_permohonan_detail($permohonan_id, $dosen_id);
        
        if (!$permohonan) {
            $this->session->set_flashdata('error', 'Data permohonan tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/penelitian');
            return;
        }
        
        // Prepare data untuk view
        $data = [
            'permohonan' => $permohonan
        ];
        
        // Load view dengan template dosen
        $this->load->view('template/dosen', [
            'title' => 'Detail Permohonan Penelitian - ' . $permohonan->nama_mahasiswa,
            'content' => $this->load->view('dosen/penelitian/detail', $data, TRUE),
            'script' => $this->_get_detail_script()
        ]);
    }

    /**
     * Proses review permohonan (approve/reject) - FIXED NOTIFICATIONS
     */
    public function review() {
        if ($this->input->method() !== 'post') {
            redirect('dosen/penelitian');
            return;
        }
        
        $dosen_id = $this->session->userdata('id');
        $permohonan_id = $this->input->post('permohonan_id');
        $status_review = $this->input->post('status_review'); // approved/rejected
        $komentar = $this->input->post('komentar_pembimbing');
        
        // Validasi input
        if (empty($permohonan_id) || empty($status_review)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/penelitian');
            return;
        }
        
        // Validasi ownership
        $permohonan = $this->_get_permohonan_detail($permohonan_id, $dosen_id);
        if (!$permohonan) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/penelitian');
            return;
        }
        
        // Update status di database
        $update_data = [
            'status_pembimbing' => $status_review,
            'komentar_pembimbing' => $komentar,
            'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
            'status' => ($status_review == 'approved') ? 'approved' : 'rejected'
        ];
        
        $this->db->where('id', $permohonan_id);
        $this->db->where('dosen_pembimbing_id', $dosen_id);
        $result = $this->db->update('permohonan_izin_penelitian', $update_data);
        
        if ($result) {
            // Log aktivitas
            $this->_log_aktivitas($permohonan_id, $dosen_id, 'review_pembimbing', 
                'Dosen memberikan review: ' . ($status_review == 'approved' ? 'Disetujui' : 'Ditolak'));
            
            // WORKFLOW NOTIFIKASI SESUAI REQUIREMENT - FIXED
            if ($status_review == 'approved') {
                // APPROVE: kirim ke MAHASISWA DAN STAF
                $notif_mahasiswa = $this->_send_notification_to_mahasiswa_approved($permohonan, $komentar);
                $notif_staf = $this->_send_notification_to_staf($permohonan);
                
                if ($notif_mahasiswa && $notif_staf) {
                    $this->session->set_flashdata('success', 'Permohonan berhasil disetujui! Notifikasi telah dikirim ke mahasiswa dan staf.');
                } else {
                    $this->session->set_flashdata('success', 'Permohonan berhasil disetujui! (Beberapa notifikasi email mungkin gagal dikirim)');
                }
            } else {
                // REJECT: kirim ke MAHASISWA saja (untuk ajukan ulang)
                $notif_mahasiswa = $this->_send_notification_to_mahasiswa_rejected($permohonan, $komentar);
                
                if ($notif_mahasiswa) {
                    $this->session->set_flashdata('success', 'Permohonan ditolak. Notifikasi telah dikirim ke mahasiswa untuk perbaikan.');
                } else {
                    $this->session->set_flashdata('success', 'Permohonan ditolak. (Notifikasi email mungkin gagal dikirim)');
                }
            }
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan review!');
        }
        
        redirect('dosen/penelitian');
    }

    /**
     * View file proposal yang diupload mahasiswa
     */
    public function view_file($permohonan_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail permohonan dengan validasi ownership
        $permohonan = $this->_get_permohonan_detail($permohonan_id, $dosen_id);
        
        if (!$permohonan || empty($permohonan->file_proposal_revisi)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan!');
            redirect('dosen/penelitian');
            return;
        }
        
        $file_path = FCPATH . 'uploads/penelitian/proposal_revisi/' . $permohonan->file_proposal_revisi;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan di server!');
            redirect('dosen/penelitian');
            return;
        }
        
        // Set headers untuk view file
        $file_info = pathinfo($file_path);
        $extension = strtolower($file_info['extension']);
        
        if ($extension === 'pdf') {
            header('Content-Type: application/pdf');
        } else {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $permohonan->file_proposal_revisi . '"');
        }
        
        readfile($file_path);
    }

    // ====================================================================
    // PRIVATE HELPER METHODS
    // ====================================================================

    /**
     * Get permohonan yang perlu direview oleh dosen
     */
    private function _get_permohonan_perlu_review($dosen_id) {
        $this->db->select('
            pip.*,
            pm.judul as judul_proposal,
            pm.workflow_status
        ');
        $this->db->from('permohonan_izin_penelitian pip');
        $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
        $this->db->where('pip.dosen_pembimbing_id', $dosen_id);
        $this->db->where('pip.status_pembimbing', 'pending');
        $this->db->where('pip.status', 'submitted');
        $this->db->order_by('pip.created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get riwayat review yang sudah dilakukan dosen
     */
    private function _get_riwayat_review($dosen_id) {
        $this->db->select('
            pip.*,
            pm.judul as judul_proposal,
            pm.workflow_status
        ');
        $this->db->from('permohonan_izin_penelitian pip');
        $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
        $this->db->where('pip.dosen_pembimbing_id', $dosen_id);
        $this->db->where_in('pip.status_pembimbing', ['approved', 'rejected']);
        $this->db->order_by('pip.tanggal_review_pembimbing', 'DESC');
        $this->db->limit(10);
        
        return $this->db->get()->result();
    }

    /**
     * FIXED: Get detail permohonan dengan validasi ownership
     * Memastikan field email_mahasiswa tersedia untuk notifikasi
     */
    private function _get_permohonan_detail($permohonan_id, $dosen_id) {
        $this->db->select('
            pip.*,
            pm.judul as judul_proposal,
            pm.workflow_status,
            m.nama as nama_mahasiswa_db,
            m.email as email_mahasiswa  -- PASTIKAN field ini ada untuk notifikasi
        ');
        $this->db->from('permohonan_izin_penelitian pip');
        $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pip.id', $permohonan_id);
        $this->db->where('pip.dosen_pembimbing_id', $dosen_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get statistics untuk dashboard
     */
    private function _get_statistics($dosen_id) {
        $stats = [];
        
        // Total permohonan
        $this->db->where('dosen_pembimbing_id', $dosen_id);
        $stats['total'] = $this->db->count_all_results('permohonan_izin_penelitian');
        
        // Perlu review
        $this->db->where('dosen_pembimbing_id', $dosen_id);
        $this->db->where('status_pembimbing', 'pending');
        $this->db->where('status', 'submitted');
        $stats['perlu_review'] = $this->db->count_all_results('permohonan_izin_penelitian');
        
        // Disetujui
        $this->db->where('dosen_pembimbing_id', $dosen_id);
        $this->db->where('status_pembimbing', 'approved');
        $stats['disetujui'] = $this->db->count_all_results('permohonan_izin_penelitian');
        
        // Ditolak
        $this->db->where('dosen_pembimbing_id', $dosen_id);
        $this->db->where('status_pembimbing', 'rejected');
        $stats['ditolak'] = $this->db->count_all_results('permohonan_izin_penelitian');
        
        return $stats;
    }

    /**
     * Log aktivitas ke tabel log_penelitian
     */
    private function _log_aktivitas($permohonan_id, $user_id, $aktivitas, $deskripsi) {
        try {
            $log_data = [
                'permohonan_id' => $permohonan_id,
                'user_id' => $user_id,
                'user_role' => 'dosen',
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsi,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('log_penelitian', $log_data);
        } catch (Exception $e) {
            log_message('error', 'Error logging penelitian activity: ' . $e->getMessage());
        }
    }

    // ====================================================================
    // EMAIL NOTIFICATION METHODS - FIXED DATABASE ISSUES
    // ====================================================================

    /**
     * FIXED: Kirim notifikasi ke mahasiswa ketika APPROVED
     */
    private function _send_notification_to_mahasiswa_approved($permohonan, $komentar = '') {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $subject = 'Permohonan Izin Penelitian Disetujui - ' . $permohonan->nama_mahasiswa;
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #28a745; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>✅ Permohonan Izin Penelitian Disetujui</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$permohonan->nama_mahasiswa}</strong>,</p>
                    
                    <p>Selamat! Dosen pembimbing telah menyetujui permohonan izin penelitian Anda.</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #28a745; margin-top: 0;'>Detail Penelitian:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td><strong>Judul:</strong></td><td>{$permohonan->judul_skripsi_terbaru}</td></tr>
                            <tr><td><strong>Tempat:</strong></td><td>{$permohonan->tempat_penelitian}</td></tr>
                            <tr><td><strong>Periode:</strong></td><td>{$permohonan->tanggal_mulai_penelitian} s/d {$permohonan->tanggal_selesai_penelitian}</td></tr>
                        </table>
                    </div>";
            
            if (!empty($komentar)) {
                $message .= "
                    <div style='background-color: #e3f2fd; padding: 15px; border-left: 4px solid #2196f3; margin: 15px 0;'>
                        <h4 style='color: #1976d2; margin-top: 0;'>Catatan Dosen:</h4>
                        <p style='margin: 0;'>" . nl2br(htmlspecialchars($komentar)) . "</p>
                    </div>";
            }
            
            $message .= "
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                        <p style='margin: 0; color: #856404;'><strong>Langkah Selanjutnya:</strong></p>
                        <p style='margin: 5px 0 0 0; color: #856404;'>Staf akademik akan segera memproses surat izin penelitian Anda. Anda akan mendapat notifikasi lanjutan ketika surat sudah siap.</p>
                    </div>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                    <p style='margin: 0;'>SIM Tugas Akhir STK Santo Yakobus</p>
                </div>
            </div>";
            
            $this->email->from('noreply.stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($permohonan->email_mahasiswa);  // Field sudah dipastikan ada
            $this->email->subject($subject);
            $this->email->message($message);
            
            $result = $this->email->send();
            
            if ($result) {
                log_message('info', 'Email approval sent to mahasiswa: ' . $permohonan->email_mahasiswa);
            } else {
                log_message('error', 'Failed to send approval email to mahasiswa: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Exception sending approval email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * FIXED: Kirim notifikasi ke mahasiswa ketika REJECTED
     */
    private function _send_notification_to_mahasiswa_rejected($permohonan, $komentar) {
        try {
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $subject = 'Permohonan Izin Penelitian Perlu Perbaikan - ' . $permohonan->nama_mahasiswa;
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #dc3545; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>📝 Permohonan Izin Penelitian Perlu Perbaikan</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. <strong>{$permohonan->nama_mahasiswa}</strong>,</p>
                    
                    <p>Dosen pembimbing telah memberikan catatan untuk perbaikan permohonan izin penelitian Anda.</p>
                    
                    <div style='background-color: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0;'>
                        <h4 style='color: #721c24; margin-top: 0;'>Catatan Dosen Pembimbing:</h4>
                        <p style='margin: 0; color: #721c24;'>" . nl2br(htmlspecialchars($komentar)) . "</p>
                    </div>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #dc3545; margin-top: 0;'>Detail Permohonan:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td><strong>Judul:</strong></td><td>{$permohonan->judul_skripsi_terbaru}</td></tr>
                            <tr><td><strong>Tempat:</strong></td><td>{$permohonan->tempat_penelitian}</td></tr>
                            <tr><td><strong>Periode:</strong></td><td>{$permohonan->tanggal_mulai_penelitian} s/d {$permohonan->tanggal_selesai_penelitian}</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8;'>
                        <p style='margin: 0; color: #0c5460;'><strong>Langkah Selanjutnya:</strong></p>
                        <p style='margin: 5px 0 0 0; color: #0c5460;'>Silakan perbaiki permohonan Anda sesuai catatan dosen pembimbing dan ajukan kembali melalui sistem SIM-TA.</p>
                    </div>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                    <p style='margin: 0;'>SIM Tugas Akhir STK Santo Yakobus</p>
                </div>
            </div>";
            
            $this->email->from('noreply.stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($permohonan->email_mahasiswa);  // Field sudah dipastikan ada
            $this->email->subject($subject);
            $this->email->message($message);
            
            $result = $this->email->send();
            
            if ($result) {
                log_message('info', 'Email rejection sent to mahasiswa: ' . $permohonan->email_mahasiswa);
            } else {
                log_message('error', 'Failed to send rejection email to mahasiswa: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Exception sending rejection email to mahasiswa: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * FIXED: Kirim notifikasi email ke staf setelah dosen approve
     * Menggunakan tabel dosen dengan level = '5' (bukan tabel staf yang tidak ada)
     */
    private function _send_notification_to_staf($permohonan) {
        try {
            // PERBAIKAN: Get email staf dari tabel dosen dengan level = '5'
            $this->db->select('email, nama');
            $this->db->where('level', '5');  // Staf tersimpan di tabel dosen dengan level 5
            $staf_list = $this->db->get('dosen')->result();  // Query ke tabel dosen, bukan staf
            
            if (empty($staf_list)) {
                log_message('warning', 'No active staff found for penelitian notification');
                return false;
            }
            
            $config = $this->_get_email_config();
            $this->email->initialize($config);
            
            $subject = 'Permohonan Izin Penelitian Disetujui Dosen - ' . $permohonan->nama_mahasiswa;
            
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #007bff; color: white; padding: 20px; text-align: center;'>
                    <h2 style='margin: 0;'>🔬 Permohonan Izin Penelitian Disetujui</h2>
                </div>
                
                <div style='padding: 20px; background-color: #f8f9fa;'>
                    <p>Yth. Tim Staf Akademik,</p>
                    
                    <p>Dosen pembimbing telah menyetujui permohonan izin penelitian mahasiswa berikut:</p>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #007bff; margin-top: 0;'>Detail Mahasiswa:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td width='30%'><strong>Nama:</strong></td><td>{$permohonan->nama_mahasiswa}</td></tr>
                            <tr><td><strong>NIM:</strong></td><td>{$permohonan->nim}</td></tr>
                            <tr><td><strong>Program Studi:</strong></td><td>{$permohonan->program_studi}</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <h4 style='color: #28a745; margin-top: 0;'>Detail Penelitian:</h4>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td width='30%'><strong>Judul:</strong></td><td>{$permohonan->judul_skripsi_terbaru}</td></tr>
                            <tr><td><strong>Tempat:</strong></td><td>{$permohonan->tempat_penelitian}</td></tr>
                            <tr><td><strong>Periode:</strong></td><td>{$permohonan->tanggal_mulai_penelitian} s/d {$permohonan->tanggal_selesai_penelitian}</td></tr>
                        </table>
                    </div>
                    
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                        <p style='margin: 0; color: #856404;'><strong>Tindakan Diperlukan:</strong></p>
                        <p style='margin: 5px 0 0 0; color: #856404;'>Silakan proses surat izin penelitian melalui sistem SIM-TA dan upload surat yang sudah ditandatangani.</p>
                    </div>
                </div>
                
                <div style='background-color: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px;'>
                    <p style='margin: 0;'>SIM Tugas Akhir STK Santo Yakobus</p>
                </div>
            </div>";
            
            $success_count = 0;
            
            // Kirim ke semua staf
            foreach ($staf_list as $staf) {
                $this->email->clear();
                $this->email->from('noreply.stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
                $this->email->to($staf->email);
                $this->email->subject($subject);
                $this->email->message($message);
                
                if ($this->email->send()) {
                    $success_count++;
                    log_message('info', 'Email sent to staff: ' . $staf->email);
                } else {
                    log_message('error', 'Failed to send email to staff: ' . $staf->email);
                }
            }
            
            return $success_count > 0;
            
        } catch (Exception $e) {
            log_message('error', 'Exception sending email to staff: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email configuration
     */
    private function _get_email_config() {
        return [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'stkyakobus@gmail.com',
            'smtp_pass' => 'yonroxhraathnaug', // Ganti dengan password yang benar
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'mailtype' => 'html',
            'smtp_crypto' => 'tls',
            'smtp_timeout' => 30
        ];
    }

    /**
     * JavaScript untuk halaman index
     */
    private function _get_index_script() {
        return "
        <script>
        $(document).ready(function() {
            // Auto refresh setiap 5 menit
            setInterval(function() {
                location.reload();
            }, 300000);
            
            // Confirm dialog untuk review
            $('.btn-review').click(function(e) {
                e.preventDefault();
                var action = $(this).data('action');
                var nama = $(this).data('nama');
                var actionText = action === 'approve' ? 'menyetujui' : 'menolak';
                
                if (confirm('Apakah Anda yakin ingin ' + actionText + ' permohonan dari ' + nama + '?')) {
                    var form = $(this).closest('form');
                    form.find('input[name=\"status_review\"]').val(action === 'approve' ? 'approved' : 'rejected');
                    form.submit();
                }
            });
        });
        </script>
        ";
    }

    /**
     * JavaScript untuk halaman detail
     */
    private function _get_detail_script() {
        return "
        <script>
        $(document).ready(function() {
            // Validate form
            $('#reviewForm').submit(function(e) {
                var status = $('input[name=\"status_review\"]:checked').val();
                var komentar = $('#komentar_pembimbing').val();
                
                if (!status) {
                    alert('Silakan pilih status review!');
                    e.preventDefault();
                    return false;
                }
                
                if (status === 'rejected' && komentar.trim() === '') {
                    alert('Komentar wajib diisi jika menolak permohonan!');
                    e.preventDefault();
                    return false;
                }
                
                return confirm('Apakah Anda yakin dengan review yang diberikan?');
            });
            
            // Toggle komentar wajib
            $('input[name=\"status_review\"]').change(function() {
                var isRejected = $(this).val() === 'rejected';
                $('#komentar_pembimbing').attr('required', isRejected);
                
                if (isRejected) {
                    $('#komentar_pembimbing').focus();
                }
            });
        });
        </script>
        ";
    }
}