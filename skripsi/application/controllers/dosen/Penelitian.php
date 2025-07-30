<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Penelitian untuk Dosen - Updated untuk workflow penelitian
 * 
 * Controller untuk mengelola permohonan izin penelitian dari perspektif dosen pembimbing
 * Sesuai dengan workflow: Mahasiswa Ajukan -> Dosen Review -> Staf Proses
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Dosen
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (Updated Workflow)
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
     * Proses review permohonan (approve/reject)
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
            
            // Kirim email notifikasi
            if ($status_review == 'approved') {
                $this->_send_notification_to_staf($permohonan);
                $this->session->set_flashdata('success', 'Permohonan berhasil disetujui! Notifikasi telah dikirim ke staf.');
            } else {
                $this->_send_notification_to_mahasiswa($permohonan, $komentar);
                $this->session->set_flashdata('success', 'Permohonan ditolak. Notifikasi telah dikirim ke mahasiswa.');
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
     * Get detail permohonan dengan validasi ownership
     */
    private function _get_permohonan_detail($permohonan_id, $dosen_id) {
        $this->db->select('
            pip.*,
            pm.judul as judul_proposal,
            pm.workflow_status,
            m.nama as nama_mahasiswa_db,
            m.email as email_mahasiswa
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
        $log_data = [
            'permohonan_id' => $permohonan_id,
            'user_id' => $user_id,
            'user_role' => 'dosen',
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('log_penelitian', $log_data);
    }

    /**
     * Kirim notifikasi email ke staf setelah dosen approve
     */
    private function _send_notification_to_staf($permohonan) {
        // Get email staf (level 5)
        $this->db->select('email, nama');
        $this->db->where('level', '5');
        $this->db->where('status', '1');
        $staf_list = $this->db->get('staf')->result();
        
        if (empty($staf_list)) {
            return false;
        }
        
        // Setup email
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'ssl://smtp.gmail.com';
        $config['smtp_port'] = 465;
        $config['smtp_user'] = 'noreply.stkyakobus@gmail.com';
        $config['smtp_pass'] = 'your_email_password'; // Ganti dengan password yang benar
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html';
        
        $this->email->initialize($config);
        
        $subject = 'Permohonan Izin Penelitian Disetujui Dosen - ' . $permohonan->nama_mahasiswa;
        
        $message = "
        <h3>Permohonan Izin Penelitian Disetujui</h3>
        <p>Dosen pembimbing telah menyetujui permohonan izin penelitian mahasiswa:</p>
        
        <strong>Detail Mahasiswa:</strong><br>
        - Nama: {$permohonan->nama_mahasiswa}<br>
        - NIM: {$permohonan->nim}<br>
        - Program Studi: {$permohonan->program_studi}<br>
        
        <strong>Detail Penelitian:</strong><br>
        - Judul: {$permohonan->judul_skripsi_terbaru}<br>
        - Tempat: {$permohonan->tempat_penelitian}<br>
        - Periode: {$permohonan->tanggal_mulai_penelitian} s/d {$permohonan->tanggal_selesai_penelitian}<br>
        
        <p>Silakan proses surat izin penelitian melalui sistem.</p>
        ";
        
        // Kirim ke semua staf
        foreach ($staf_list as $staf) {
            $this->email->from('noreply.stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
            $this->email->to($staf->email);
            $this->email->subject($subject);
            $this->email->message($message);
            $this->email->send();
        }
        
        return true;
    }

    /**
     * Kirim notifikasi email ke mahasiswa jika ditolak
     */
    private function _send_notification_to_mahasiswa($permohonan, $komentar) {
        // Setup email
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'ssl://smtp.gmail.com';
        $config['smtp_port'] = 465;
        $config['smtp_user'] = 'noreply.stkyakobus@gmail.com';
        $config['smtp_pass'] = 'your_email_password'; // Ganti dengan password yang benar
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['mailtype'] = 'html';
        
        $this->email->initialize($config);
        
        $subject = 'Permohonan Izin Penelitian Perlu Perbaikan - ' . $permohonan->nama_mahasiswa;
        
        $message = "
        <h3>Permohonan Izin Penelitian Perlu Perbaikan</h3>
        <p>Dosen pembimbing telah memberikan catatan untuk perbaikan permohonan izin penelitian Anda:</p>
        
        <strong>Catatan Dosen:</strong><br>
        <div style='background-color: #f8f9fa; padding: 10px; border-left: 4px solid #dc3545;'>
            {$komentar}
        </div>
        
        <p>Silakan perbaiki permohonan Anda sesuai catatan dan ajukan kembali melalui sistem.</p>
        ";
        
        $this->email->from('noreply.stkyakobus@gmail.com', 'SIM-TA STK Santo Yakobus');
        $this->email->to($permohonan->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        
        return $this->email->send();
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