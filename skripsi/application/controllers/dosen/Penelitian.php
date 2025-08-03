<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Penelitian untuk Dosen - COMPLETELY FIXED VERSION
 * 
 * Mengatasi masalah yang muncul setelah update database dengan trigger
 * seminar proposal dan seminar skripsi
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.3 (Database Error + Trigger Compatibility Fixed)
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
     * COMPLETELY FIXED: Proses review permohonan (approve/reject)
     * Mengatasi race condition dengan trigger dan header issues
     */
    public function review() {
        // CRITICAL: Start output buffering to prevent header issues
        ob_start();
        
        if ($this->input->method() !== 'post') {
            ob_end_clean();
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
            ob_end_clean();
            redirect('dosen/penelitian');
            return;
        }
        
        try {
            // FIXED: Ambil data lengkap dengan error handling
            $permohonan = $this->_get_permohonan_detail_secure($permohonan_id, $dosen_id);
            if (!$permohonan) {
                throw new Exception('Data tidak ditemukan atau bukan bimbingan Anda!');
            }
            
            // CRITICAL: Use database transaction to prevent race condition with triggers
            $this->db->trans_start();
            
            // Update status di database
            $update_data = [
                'status_pembimbing' => $status_review,
                'komentar_pembimbing' => $komentar,
                'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                'status' => ($status_review == 'approved') ? 'approved' : 'rejected',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $permohonan_id);
            $this->db->where('dosen_pembimbing_id', $dosen_id);
            $result = $this->db->update('permohonan_izin_penelitian', $update_data);
            
            if (!$result || $this->db->affected_rows() == 0) {
                throw new Exception('Gagal menyimpan review atau data tidak ditemukan');
            }
            
            // Commit transaction before sending notifications
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal');
            }
            
            // Log aktivitas
            $this->_log_aktivitas($permohonan_id, $dosen_id, 'review_pembimbing', 
                'Dosen memberikan review: ' . ($status_review == 'approved' ? 'Disetujui' : 'Ditolak'));
            
            // FIXED NOTIFICATIONS: Send dengan data yang sudah dipastikan lengkap
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
            
        } catch (Exception $e) {
            // Rollback transaction if still active
            if ($this->db->trans_status() !== FALSE) {
                $this->db->trans_rollback();
            }
            
            log_message('error', 'Error in penelitian review: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        // Clean buffer and redirect
        ob_end_clean();
        redirect('dosen/penelitian');
    }

    /**
     * View file proposal yang diupload mahasiswa
     */
    public function view_file($permohonan_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail permohonan dengan validasi ownership
        $permohonan = $this->_get_permohonan_detail_secure($permohonan_id, $dosen_id);
        
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
    // PRIVATE HELPER METHODS - ENHANCED WITH TRIGGER COMPATIBILITY
    // ====================================================================

    /**
     * Get permohonan yang perlu direview oleh dosen
     */
    private function _get_permohonan_perlu_review($dosen_id) {
        try {
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
        } catch (Exception $e) {
            log_message('error', 'Error getting permohonan review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat review yang sudah dilakukan dosen
     */
    private function _get_riwayat_review($dosen_id) {
        try {
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
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * LEGACY: Get detail permohonan dengan validasi ownership
     * Diperlukan untuk backward compatibility
     */
    private function _get_permohonan_detail($permohonan_id, $dosen_id) {
        return $this->_get_permohonan_detail_secure($permohonan_id, $dosen_id);
    }

    /**
     * COMPLETELY FIXED: Get detail permohonan dengan error handling yang robust
     * Mengatasi masalah setelah update database dengan trigger
     */
    private function _get_permohonan_detail_secure($permohonan_id, $dosen_id) {
        try {
            // CRITICAL: Use explicit transaction isolation to avoid trigger race condition
            $this->db->trans_start();
            
            $this->db->select('
                pip.*,
                pm.judul as judul_proposal,
                pm.workflow_status,
                COALESCE(m.nama, pip.nama_mahasiswa) as nama_mahasiswa,
                COALESCE(m.email, "no-email@stkyakobus.ac.id") as email_mahasiswa,
                COALESCE(m.nim, pip.nim) as nim_mahasiswa
            ');
            $this->db->from('permohonan_izin_penelitian pip');
            $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id', 'left');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left');
            $this->db->where('pip.id', $permohonan_id);
            $this->db->where('pip.dosen_pembimbing_id', $dosen_id);
            
            $result = $this->db->get()->row();
            
            $this->db->trans_complete();
            
            // VALIDATION: Pastikan data yang diperlukan untuk notifikasi tersedia
            if ($result) {
                // Fallback untuk data mahasiswa jika JOIN gagal karena trigger
                if (empty($result->email_mahasiswa) || $result->email_mahasiswa == 'no-email@stkyakobus.ac.id') {
                    $result = $this->_fix_missing_mahasiswa_data($result);
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error getting permohonan detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * RESCUE FUNCTION: Fix missing mahasiswa data akibat trigger interference
     */
    private function _fix_missing_mahasiswa_data($permohonan) {
        try {
            // Jika email kosong, cari dari tabel mahasiswa langsung
            if (empty($permohonan->email_mahasiswa) || $permohonan->email_mahasiswa == 'no-email@stkyakobus.ac.id') {
                $this->db->select('m.nama, m.email, m.nim');
                $this->db->from('mahasiswa m');
                $this->db->join('proposal_mahasiswa pm', 'm.id = pm.mahasiswa_id');
                $this->db->where('pm.id', $permohonan->proposal_mahasiswa_id);
                
                $mahasiswa_data = $this->db->get()->row();
                
                if ($mahasiswa_data) {
                    $permohonan->email_mahasiswa = $mahasiswa_data->email;
                    $permohonan->nama_mahasiswa = $mahasiswa_data->nama;
                    $permohonan->nim_mahasiswa = $mahasiswa_data->nim;
                } else {
                    // Ultimate fallback - pakai data dari permohonan_izin_penelitian
                    $permohonan->email_mahasiswa = 'fallback@stkyakobus.ac.id';
                    log_message('warning', 'Using fallback email for permohonan ID: ' . $permohonan->id);
                }
            }
            
            return $permohonan;
            
        } catch (Exception $e) {
            log_message('error', 'Error fixing mahasiswa data: ' . $e->getMessage());
            $permohonan->email_mahasiswa = 'fallback@stkyakobus.ac.id';
            return $permohonan;
        }
    }

    /**
     * Get statistics untuk dashboard
     */
    private function _get_statistics($dosen_id) {
        $stats = [];
        
        try {
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
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            $stats = ['total' => 0, 'perlu_review' => 0, 'disetujui' => 0, 'ditolak' => 0];
        }
        
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
    // EMAIL NOTIFICATION METHODS - COMPLETELY FIXED
    // ====================================================================

    /**
     * COMPLETELY FIXED: Kirim notifikasi ke mahasiswa ketika APPROVED
     */
    private function _send_notification_to_mahasiswa_approved($permohonan, $komentar = '') {
        try {
            // VALIDATION: Pastikan email tersedia
            if (empty($permohonan->email_mahasiswa) || $permohonan->email_mahasiswa == 'fallback@stkyakobus.ac.id') {
                log_message('warning', 'Email mahasiswa tidak tersedia untuk notifikasi approval');
                return false;
            }
            
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
            $this->email->to($permohonan->email_mahasiswa);
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
     * COMPLETELY FIXED: Kirim notifikasi ke mahasiswa ketika REJECTED
     */
    private function _send_notification_to_mahasiswa_rejected($permohonan, $komentar) {
        try {
            // VALIDATION: Pastikan email tersedia
            if (empty($permohonan->email_mahasiswa) || $permohonan->email_mahasiswa == 'fallback@stkyakobus.ac.id') {
                log_message('warning', 'Email mahasiswa tidak tersedia untuk notifikasi rejection');
                return false;
            }
            
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
            $this->email->to($permohonan->email_mahasiswa);
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
     * COMPLETELY FIXED: Kirim notifikasi email ke staf setelah dosen approve
     */
    private function _send_notification_to_staf($permohonan) {
        try {
            // Get email staf dari tabel dosen dengan level = '5'
            $this->db->select('email, nama');
            $this->db->where('level', '5');
            $staf_list = $this->db->get('dosen')->result();
            
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
            'smtp_pass' => 'yonroxhraathnaug',
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