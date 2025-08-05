<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Publikasi untuk Staf - Phase 6
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * 
 * Workflow:
 * 1. Mahasiswa Kirim Ajuan
 * 2. Dosen Pembimbing Rekomendasi → jika Approve lanjut ke staf
 * 3. Staf input link repository sesuai form mahasiswa
 * 4. Staf Validasi pengajuan (notifikasi email ke dosen dan mahasiswa)
 * 5. Proses Selesai (completed)
 * 6. Mahasiswa Download Surat Keterangan Publikasi
 * 
 * File: application/controllers/staf/Publikasi.php
 * 
 * @package     SIM_TA
 * @subpackage  Controllers/Staf
 * @category    Publikasi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Following DosenPublikasi Pattern)
 */
class Publikasi extends CI_Controller {

    private $staf_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session', 'form_validation', 'email']);
        $this->load->helper(['url', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk staf
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if($this->session->userdata('level') != '5') { // Level 5 = Staf
            show_error('Akses ditolak. Halaman khusus staf.', 403);
        }
        
        $this->staf_id = $this->session->userdata('id');
    }

    /**
     * Dashboard publikasi untuk staf
     * Menggunakan template existing staf.php
     */
    public function index() {
        // Prepare data untuk view
        $view_data = [
            'pengajuan_validasi' => $this->_get_pengajuan_perlu_validasi(),
            'riwayat_validasi' => $this->_get_riwayat_validasi(), 
            'stats' => $this->_get_statistik_staf()
        ];
        
        // Template data untuk staf.php
        $data = [
            'title' => 'Validasi Publikasi Tugas Akhir',
            'content' => $this->load->view('staf/publikasi/index', $view_data, TRUE),
            'script' => $this->_get_index_script()
        ];
        
        // Load template existing
        $this->load->view('template/staf', $data);
    }

    /**
     * Detail dan input repository publikasi
     */
    public function detail($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
        }
        
        // Hanya bisa akses yang sudah disetujui dosen
        if ($publikasi->status_pembimbing !== 'approved') {
            $this->session->set_flashdata('error', 'Pengajuan belum disetujui dosen pembimbing.');
            redirect('staf/publikasi');
        }
        
        // Template data
        $view_data = [
            'publikasi' => $publikasi,
            'allow_input_repository' => ($publikasi->status_staf === 'pending'),
            'show_files' => true
        ];
        
        $data = [
            'title' => 'Detail Publikasi - ' . $publikasi->nama_mahasiswa,
            'content' => $this->load->view('staf/publikasi/detail', $view_data, TRUE),
            'script' => $this->_get_detail_script()
        ];
        
        $this->load->view('template/staf', $data);
    }

    /**
     * Input repository link - Step 3 dalam workflow
     */
    public function input_repository($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
        }
        
        // Validasi status - harus sudah disetujui dosen
        if ($publikasi->status_pembimbing !== 'approved') {
            $this->session->set_flashdata('error', 'Pengajuan belum disetujui dosen pembimbing.');
            redirect('staf/publikasi');
        }
        
        // Validasi status staf - harus masih pending
        if ($publikasi->status_staf !== 'pending') {
            $this->session->set_flashdata('error', 'Publikasi sudah diproses atau ditolak.');
            redirect('staf/publikasi');
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_input_repository($publikasi);
        } else {
            $this->_show_input_repository_form($publikasi);
        }
    }

    /**
     * Validasi final pengajuan publikasi - Step 4 dalam workflow
     */
    public function validasi($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
        }
        
        // Harus sudah ada repository link
        if (empty($publikasi->link_repository)) {
            $this->session->set_flashdata('error', 'Repository link belum diinput.');
            redirect('staf/publikasi/detail/' . $publikasi_id);
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_validasi_final($publikasi);
        } else {
            $this->_show_validasi_form($publikasi);
        }
    }

    /**
     * Download file publikasi
     */
    public function download_file($file_type, $publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            show_404();
        }
        
        $file_map = [
            'surat_revisi' => $publikasi->file_surat_revisi,
            'skripsi_final' => $publikasi->file_skripsi_final,
            'surat_perpustakaan' => $publikasi->file_surat_perpustakaan
        ];
        
        if (!isset($file_map[$file_type]) || empty($file_map[$file_type])) {
            show_404();
        }
        
        $file_path = './uploads/publikasi/' . $file_map[$file_type];
        
        if (!file_exists($file_path)) {
            show_404();
        }
        
        // Force download
        $this->load->helper('download');
        force_download($file_map[$file_type], file_get_contents($file_path));
    }

    // =================================================================
    // PRIVATE METHODS - Following DosenPublikasi Pattern
    // =================================================================

    /**
     * Get pengajuan yang perlu validasi staf
     */
    private function _get_pengajuan_perlu_validasi() {
        try {
            // Query menggunakan view publikasi_mahasiswa_v
            $this->db->select('*');
            $this->db->from('publikasi_mahasiswa_v');
            $this->db->where('status_pembimbing', 'approved');
            $this->db->where('status_staf', 'pending');
            $this->db->order_by('tanggal_review_pembimbing', 'ASC');
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting pengajuan perlu validasi staf: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat validasi staf
     */
    private function _get_riwayat_validasi() {
        try {
            $this->db->select('*');
            $this->db->from('publikasi_mahasiswa_v');
            $this->db->where('validated_by_staf_id', $this->staf_id);
            $this->db->where('status_staf !=', 'pending');
            $this->db->order_by('tanggal_validasi_staf', 'DESC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat validasi staf: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistik untuk dashboard staf
     */
    private function _get_statistik_staf() {
        $stats = [
            'menunggu_validasi' => 0,
            'sudah_divalidasi' => 0,
            'publikasi_selesai' => 0,
            'total_bulan_ini' => 0
        ];
        
        try {
            // Menunggu validasi
            $this->db->where('status_pembimbing', 'approved');
            $this->db->where('status_staf', 'pending');
            $stats['menunggu_validasi'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
            // Sudah divalidasi oleh staf ini
            $this->db->where('validated_by_staf_id', $this->staf_id);
            $this->db->where('status_staf !=', 'pending');
            $stats['sudah_divalidasi'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
            // Publikasi selesai
            $this->db->where('status', 'completed');
            $stats['publikasi_selesai'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
            // Total bulan ini
            $this->db->where('MONTH(tanggal_validasi_staf)', date('n'));
            $this->db->where('YEAR(tanggal_validasi_staf)', date('Y'));
            $stats['total_bulan_ini'] = $this->db->count_all_results('publikasi_tugas_akhir');
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistik staf: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Get detail publikasi dengan join lengkap
     */
    private function _get_publikasi_detail($publikasi_id) {
        try {
            $this->db->select('*');
            $this->db->from('publikasi_mahasiswa_v');
            $this->db->where('id', $publikasi_id);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting publikasi detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process input repository link
     */
    private function _process_input_repository($publikasi) {
        // Validasi form
        $this->form_validation->set_rules('link_repository', 'Link Repository', 'required|valid_url|trim');
        $this->form_validation->set_rules('keterangan_staf', 'Keterangan', 'trim');
        
        if (!$this->form_validation->run()) {
            $this->_show_input_repository_form($publikasi);
            return;
        }
        
        // Update data repository
        $update_data = [
            'link_repository' => trim($this->input->post('link_repository')),
            'komentar_staf' => trim($this->input->post('keterangan_staf')),
            'status' => 'review_staf',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->trans_start();
            
            $this->db->where('id', $publikasi->id);
            $this->db->update('publikasi_tugas_akhir', $update_data);
            
            // Log aktivitas
            $this->_log_activity($publikasi->id, 'input_repository', 
                'Staf menginput link repository: ' . $update_data['link_repository']);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            $this->session->set_flashdata('success', 'Link repository berhasil diinput. Silakan lakukan validasi final.');
            redirect('staf/publikasi/validasi/' . $publikasi->id);
            
        } catch (Exception $e) {
            log_message('error', 'Error input repository: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('staf/publikasi');
        }
    }

    /**
     * Process validasi final
     */
    private function _process_validasi_final($publikasi) {
        $action = $this->input->post('action');
        
        if ($action === 'approve') {
            $this->_approve_publikasi($publikasi);
        } elseif ($action === 'reject') {
            $this->_reject_publikasi($publikasi);
        } else {
            $this->session->set_flashdata('error', 'Aksi tidak valid.');
            redirect('staf/publikasi');
        }
    }

    /**
     * Approve publikasi - Step 5 selesai
     */
    private function _approve_publikasi($publikasi) {
        // Validasi komentar jika ada
        $komentar_staf = trim($this->input->post('komentar_staf'));
        
        $update_data = [
            'status' => 'completed',
            'status_staf' => 'approved',
            'komentar_staf' => $komentar_staf,
            'validated_by_staf_id' => $this->staf_id,
            'validated_by_staf_name' => $this->session->userdata('nama'),
            'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
            'tanggal_selesai' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->trans_start();
            
            // Update publikasi
            $this->db->where('id', $publikasi->id);
            $this->db->update('publikasi_tugas_akhir', $update_data);
            
            // Update workflow_status di proposal_mahasiswa ke 'publikasi'
            $this->db->where('id', $publikasi->proposal_mahasiswa_id);
            $this->db->update('proposal_mahasiswa', ['workflow_status' => 'publikasi']);
            
            // Log aktivitas
            $this->_log_activity($publikasi->id, 'approve_final', 
                'Staf menyetujui dan menyelesaikan publikasi');
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            // Send notification
            $this->_send_notification_completed($publikasi);
            
            $this->session->set_flashdata('success', 'Publikasi berhasil diselesaikan dan disetujui.');
            redirect('staf/publikasi');
            
        } catch (Exception $e) {
            log_message('error', 'Error approve publikasi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('staf/publikasi');
        }
    }

    /**
     * Reject publikasi
     */
    private function _reject_publikasi($publikasi) {
        // Validasi komentar wajib untuk reject
        $komentar_staf = trim($this->input->post('komentar_staf'));
        
        if (empty($komentar_staf)) {
            $this->session->set_flashdata('error', 'Komentar penolakan wajib diisi.');
            $this->_show_validasi_form($publikasi);
            return;
        }
        
        $update_data = [
            'status' => 'rejected',
            'status_staf' => 'rejected',
            'komentar_staf' => $komentar_staf,
            'validated_by_staf_id' => $this->staf_id,
            'validated_by_staf_name' => $this->session->userdata('nama'),
            'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->trans_start();
            
            $this->db->where('id', $publikasi->id);
            $this->db->update('publikasi_tugas_akhir', $update_data);
            
            // Log aktivitas
            $this->_log_activity($publikasi->id, 'reject_final', 
                'Staf menolak publikasi: ' . $komentar_staf);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            // Send notification
            $this->_send_notification_rejected($publikasi, $komentar_staf);
            
            $this->session->set_flashdata('success', 'Publikasi ditolak dan dikembalikan ke mahasiswa.');
            redirect('staf/publikasi');
            
        } catch (Exception $e) {
            log_message('error', 'Error reject publikasi: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('staf/publikasi');
        }
    }

    /**
     * Show input repository form
     */
    private function _show_input_repository_form($publikasi) {
        $view_data = [
            'publikasi' => $publikasi,
            'form_action' => 'input_repository'
        ];
        
        $data = [
            'title' => 'Input Repository - ' . $publikasi->nama_mahasiswa,
            'content' => $this->load->view('staf/publikasi/input_repository', $view_data, TRUE),
            'script' => ''
        ];
        
        $this->load->view('template/staf', $data);
    }

    /**
     * Show validasi form
     */
    private function _show_validasi_form($publikasi) {
        $view_data = [
            'publikasi' => $publikasi,
            'form_action' => 'validasi'
        ];
        
        $data = [
            'title' => 'Validasi Final Publikasi - ' . $publikasi->nama_mahasiswa,
            'content' => $this->load->view('staf/publikasi/validasi', $view_data, TRUE),
            'script' => $this->_get_validasi_script()
        ];
        
        $this->load->view('template/staf', $data);
    }

    /**
     * Send notification setelah completed
     */
    private function _send_notification_completed($publikasi) {
        try {
            // Setup email
            $this->load->library('email');
            $this->email->clear();
            
            // Email ke mahasiswa
            $this->email->to($publikasi->email_mahasiswa);
            $this->email->subject('[SIM-TA] Publikasi Tugas Akhir Selesai');
            
            $message = "
            Yth. {$publikasi->nama_mahasiswa},
            
            Publikasi tugas akhir Anda telah selesai divalidasi dan disetujui oleh staf akademik.
            
            Detail:
            - Judul: {$publikasi->judul_skripsi_final}
            - Repository: {$publikasi->link_repository}
            - Tanggal Selesai: " . date('d/m/Y H:i') . "
            
            Anda dapat mendownload Surat Keterangan Publikasi di dashboard mahasiswa.
            
            Terima kasih.
            ";
            
            $this->email->message($message);
            $result_mahasiswa = $this->email->send();
            
            // Email ke dosen pembimbing
            if (!empty($publikasi->email_pembimbing)) {
                $this->email->clear();
                $this->email->to($publikasi->email_pembimbing);
                $this->email->subject('[SIM-TA] Publikasi Mahasiswa Bimbingan Selesai');
                
                $message_dosen = "
                Yth. {$publikasi->nama_dosen_pembimbing},
                
                Publikasi tugas akhir mahasiswa bimbingan Anda telah selesai divalidasi.
                
                Detail Mahasiswa:
                - Nama: {$publikasi->nama_mahasiswa}
                - NIM: {$publikasi->nim}
                - Judul: {$publikasi->judul_skripsi_final}
                - Repository: {$publikasi->link_repository}
                
                Terima kasih atas bimbingannya.
                ";
                
                $this->email->message($message_dosen);
                $this->email->send();
            }
            
            return $result_mahasiswa;
            
        } catch (Exception $e) {
            log_message('error', 'Error send notification completed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification setelah rejected
     */
    private function _send_notification_rejected($publikasi, $alasan) {
        try {
            $this->load->library('email');
            $this->email->clear();
            
            // Email ke mahasiswa
            $this->email->to($publikasi->email_mahasiswa);
            $this->email->subject('[SIM-TA] Publikasi Tugas Akhir Ditolak');
            
            $message = "
            Yth. {$publikasi->nama_mahasiswa},
            
            Publikasi tugas akhir Anda ditolak oleh staf akademik dengan alasan:
            
            {$alasan}
            
            Detail:
            - Judul: {$publikasi->judul_skripsi_final}
            - Tanggal Penolakan: " . date('d/m/Y H:i') . "
            
            Silakan perbaiki sesuai komentar dan ajukan kembali.
            
            Terima kasih.
            ";
            
            $this->email->message($message);
            return $this->email->send();
            
        } catch (Exception $e) {
            log_message('error', 'Error send notification rejected: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log aktivitas
     */
    private function _log_activity($publikasi_id, $aktivitas, $deskripsi) {
        try {
            $log_data = [
                'publikasi_id' => $publikasi_id,
                'user_id' => $this->staf_id,
                'user_role' => 'staf',
                'user_name' => $this->session->userdata('nama'),
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsi,
                'ip_address' => $this->input->ip_address(),
                'user_agent' => $this->input->user_agent(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('log_publikasi', $log_data);
            
        } catch (Exception $e) {
            log_message('error', 'Error log activity: ' . $e->getMessage());
        }
    }

    /**
     * Get index script
     */
    private function _get_index_script() {
        return "
        <script>
        $(document).ready(function() {
            // DataTable untuk pengajuan
            $('#pengajuanTable').DataTable({
                'pageLength': 10,
                'ordering': true,
                'searching': true,
                'language': {
                    'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });
            
            // DataTable untuk riwayat
            $('#riwayatTable').DataTable({
                'pageLength': 5,
                'ordering': true,
                'searching': false,
                'language': {
                    'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });
        });
        </script>
        ";
    }

    /**
     * Get detail script
     */
    private function _get_detail_script() {
        return "
        <script>
        // Download file function
        function downloadFile(type, id) {
            window.open('" . base_url('staf/publikasi/download_file/') . "' + type + '/' + id, '_blank');
        }
        
        // Confirm input repository
        function confirmInputRepository() {
            return confirm('Pastikan link repository sudah benar. Lanjutkan?');
        }
        </script>
        ";
    }

    /**
     * Get validasi script
     */
    private function _get_validasi_script() {
        return "
        <script>
        function confirmAction(action) {
            if (action === 'approve') {
                return confirm('Yakin ingin MENYETUJUI publikasi ini? Proses tidak dapat dibatalkan.');
            } else if (action === 'reject') {
                const komentar = $('#komentar_staf').val().trim();
                if (komentar === '') {
                    alert('Komentar penolakan harus diisi.');
                    return false;
                }
                return confirm('Yakin ingin MENOLAK publikasi ini?');
            }
            return false;
        }
        
        // Real-time validation
        $('#komentar_staf').on('input', function() {
            const action = $('input[name=\"action\"]:checked').val();
            if (action === 'reject') {
                const komentar = $(this).val().trim();
                $('#submitBtn').prop('disabled', komentar === '');
            }
        });
        
        $('input[name=\"action\"]').change(function() {
            const action = $(this).val();
            if (action === 'reject') {
                const komentar = $('#komentar_staf').val().trim();
                $('#submitBtn').prop('disabled', komentar === '');
            } else {
                $('#submitBtn').prop('disabled', false);
            }
        });
        </script>
        ";
    }
}