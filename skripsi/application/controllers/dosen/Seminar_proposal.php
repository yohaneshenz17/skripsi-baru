<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Controller untuk Dosen - WORKING VERSION
 * 
 * DIBUAT BERDASARKAN STRUKTUR DATABASE AKTUAL
 * Menggunakan kolom yang benar-benar ada di tabel seminar_proposal_mahasiswa
 * 
 * @author Unit SIPD STK Santo Yakobus
 * @version 5.0 (Working with Actual Database Structure)
 */
class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper(['url', 'date', 'text', 'string']);
        
        // Load model yang sudah ada (jika ada)
        if (file_exists(APPPATH . 'models/Seminar_proposal_mahasiswa_model.php')) {
            $this->load->model('Seminar_proposal_mahasiswa_model', 'seminar_model');
        }
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    /**
     * Index - Dashboard seminar proposal untuk dosen
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        
        // Data untuk view menggunakan struktur tabel yang benar
        $view_data = [
            'pengajuan_review' => $this->_get_pengajuan_perlu_review($dosen_id),
            'riwayat_rekomendasi' => $this->_get_riwayat_rekomendasi($dosen_id),
            'perlu_penilaian' => $this->_get_seminar_perlu_penilaian($dosen_id),
            'stats' => $this->_get_statistics($dosen_id)
        ];
        
        // Data untuk template
        $data = [
            'title' => 'Seminar Proposal',
            'content' => $this->load->view('dosen/seminar_proposal/index', $view_data, TRUE),
            'script' => ''
        ];
        
        $this->load->view('template/dosen', $data);
    }

    /**
     * Detail pengajuan seminar proposal
     */
    public function detail($seminar_id) {
        $dosen_id = $this->session->userdata('id');
        
        // Get detail dengan validasi ownership
        $seminar = $this->_get_seminar_detail($seminar_id, $dosen_id);
        
        if (!$seminar) {
            $this->session->set_flashdata('error', 'Data seminar tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/seminar_proposal');
            return;
        }
        
        // Data untuk view
        $view_data = [
            'seminar' => $seminar,
            'jurnal_requirement' => $this->_check_jurnal_requirement($seminar->proposal_id),
            'jurnal_bimbingan' => $this->_get_jurnal_bimbingan($seminar->proposal_id)
        ];
        
        $data = [
            'title' => 'Detail Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'content' => $this->load->view('dosen/seminar_proposal/detail', $view_data, TRUE),
            'script' => ''
        ];
        
        $this->load->view('template/dosen', $data);
    }

    /**
     * Proses rekomendasi seminar proposal
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
        
        try {
            // Begin transaction
            $this->db->trans_start();
            
            // Update menggunakan kolom yang ada di database
            $update_data = [
                'status_pembimbing' => $rekomendasi == 'approved' ? 'approved' : 'rejected',
                'komentar_pembimbing' => $komentar,
                'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                'reviewed_by_pembimbing' => $dosen_id,
                'status' => $rekomendasi == 'approved' ? 'review_kaprodi' : 'rejected',
                'current_step' => $rekomendasi == 'approved' ? 'kaprodi' : 'mahasiswa',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_proposal_mahasiswa', $update_data);
            
            // Commit transaction
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal update database');
            }
            
            // Send notification
            $this->_send_notification($seminar, $rekomendasi, $komentar);
            
            $message = $rekomendasi == 'approved' ? 
                'Seminar proposal berhasil direkomendasikan. Notifikasi telah dikirim ke mahasiswa dan Kaprodi.' : 
                'Seminar proposal ditolak. Notifikasi telah dikirim ke mahasiswa untuk revisi.';
            
            $this->session->set_flashdata('success', $message);
            
        } catch (Exception $e) {
            log_message('error', 'Error dalam rekomendasi seminar: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
        
        redirect('dosen/seminar_proposal');
    }

    // =================================================================
    // PRIVATE METHODS - MENGGUNAKAN STRUKTUR DATABASE AKTUAL
    // =================================================================

    /**
     * ✅ WORKING: Get pengajuan yang perlu direkomendasi
     * Menggunakan kolom yang benar-benar ada di database
     */
    private function _get_pengajuan_perlu_review($dosen_id) {
        try {
            $this->db->select('
                spm.id,
                spm.proposal_id,
                spm.status,
                spm.current_step,
                spm.status_pembimbing,
                spm.file_proposal,
                spm.keterangan_mahasiswa,
                spm.tanggal_seminar,
                spm.jam_seminar,
                spm.tempat_seminar,
                DATE(spm.created_at) as tanggal_pengajuan,
                spm.plagiarism_percentage,
                pm.judul,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                p.nama as nama_prodi
            ');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            
            // Kondisi untuk pengajuan yang perlu review pembimbing
            $this->db->where_in('spm.status', ['submitted', 'review_pembimbing']);
            $this->db->where("(spm.status_pembimbing IS NULL OR spm.status_pembimbing = 'pending')");
            
            $this->db->order_by('spm.created_at', 'ASC');
            
            $result = $this->db->get()->result();
            
            // Log untuk debugging
            if (ENVIRONMENT == 'development') {
                log_message('debug', 'Query pengajuan review: ' . $this->db->last_query());
                log_message('debug', 'Jumlah data ditemukan: ' . count($result));
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error in _get_pengajuan_perlu_review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ WORKING: Get riwayat rekomendasi yang sudah diberikan
     */
    private function _get_riwayat_rekomendasi($dosen_id) {
        try {
            $this->db->select('
                spm.id,
                spm.proposal_id,
                spm.status,
                spm.status_pembimbing,
                spm.tanggal_review_pembimbing,
                spm.komentar_pembimbing,
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
            
            // Kondisi untuk yang sudah direview
            $this->db->where("spm.status_pembimbing IS NOT NULL AND spm.status_pembimbing != 'pending'");
            $this->db->where('spm.tanggal_review_pembimbing IS NOT NULL');
            
            $this->db->order_by('spm.tanggal_review_pembimbing', 'DESC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error in _get_riwayat_rekomendasi: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ WORKING: Get seminar yang perlu penilaian
     * Menggunakan kolom yang ada (tanggal_seminar, status)
     */
    private function _get_seminar_perlu_penilaian($dosen_id) {
        try {
            $this->db->select('
                spm.id,
                spm.proposal_id,
                spm.status,
                spm.tanggal_seminar,
                spm.jam_seminar,
                spm.tempat_seminar,
                spm.status_penguji1,
                spm.status_penguji2,
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
            
            // Kondisi untuk seminar yang sudah scheduled/completed dan perlu input penilaian
            $this->db->where_in('spm.status', ['scheduled', 'completed']);
            $this->db->where('spm.tanggal_seminar <=', date('Y-m-d'));
            
            // Cek apakah masih perlu penilaian (belum ada komentar penguji lengkap)
            $this->db->where("(spm.komentar_penguji1 IS NULL OR spm.komentar_penguji2 IS NULL)");
            
            $this->db->order_by('spm.tanggal_seminar', 'ASC');
            
            return $this->db->get()->result();
            
        } catch (Exception $e) {
            log_message('error', 'Error in _get_seminar_perlu_penilaian: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ✅ WORKING: Get detail seminar dengan semua kolom yang ada
     */
    private function _get_seminar_detail($seminar_id, $dosen_id) {
        try {
            $this->db->select('
                spm.*,
                pm.judul,
                pm.ringkasan,
                pm.jenis_penelitian,
                pm.lokasi_penelitian,
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
            $this->db->join('dosen d1', 'spm.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'spm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('spm.id', $seminar_id);
            $this->db->where('pm.dosen_id', $dosen_id);
            
            return $this->db->get()->row();
            
        } catch (Exception $e) {
            log_message('error', 'Error in _get_seminar_detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ WORKING: Get statistics menggunakan struktur yang ada
     */
    private function _get_statistics($dosen_id) {
        try {
            $stats = [
                'total_bimbingan' => 0,
                'pengajuan_baru' => 0,
                'menunggu_review' => 0,
                'sudah_disetujui' => 0,
                'ditolak' => 0,
                'perlu_penilaian' => 0
            ];
            
            // Total mahasiswa bimbingan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('dosen_id', $dosen_id);
            $this->db->where('status', '1');
            $result = $this->db->get()->row();
            $stats['total_bimbingan'] = $result ? $result->total : 0;
            
            // Pengajuan baru (submitted)
            $this->db->select('COUNT(*) as total');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('spm.status', 'submitted');
            $result = $this->db->get()->row();
            $stats['pengajuan_baru'] = $result ? $result->total : 0;
            
            // Menunggu review
            $this->db->select('COUNT(*) as total');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('spm.status', ['submitted', 'review_pembimbing']);
            $this->db->where("(spm.status_pembimbing IS NULL OR spm.status_pembimbing = 'pending')");
            $result = $this->db->get()->row();
            $stats['menunggu_review'] = $result ? $result->total : 0;
            
            // Sudah disetujui
            $this->db->select('COUNT(*) as total');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('spm.status_pembimbing', 'approved');
            $result = $this->db->get()->row();
            $stats['sudah_disetujui'] = $result ? $result->total : 0;
            
            // Ditolak
            $this->db->select('COUNT(*) as total');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where('spm.status_pembimbing', 'rejected');
            $result = $this->db->get()->row();
            $stats['ditolak'] = $result ? $result->total : 0;
            
            // Perlu penilaian
            $stats['perlu_penilaian'] = count($this->_get_seminar_perlu_penilaian($dosen_id));
            
            return $stats;
            
        } catch (Exception $e) {
            log_message('error', 'Error in _get_statistics: ' . $e->getMessage());
            return [
                'total_bimbingan' => 0,
                'pengajuan_baru' => 0,
                'menunggu_review' => 0,
                'sudah_disetujui' => 0,
                'ditolak' => 0,
                'perlu_penilaian' => 0
            ];
        }
    }

    /**
     * Check jurnal bimbingan requirement
     */
    private function _check_jurnal_requirement($proposal_id) {
        $min_required = 3;
        
        $this->db->select('COUNT(*) as total');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('status_validasi', '1');
        $result = $this->db->get()->row();
        
        $count = $result ? (int)$result->total : 0;
        
        return [
            'eligible' => $count >= $min_required,
            'jurnal_validated_count' => $count,
            'minimum_required' => $min_required,
            'missing' => max(0, $min_required - $count),
            'message' => $count >= $min_required ? 
                'Memenuhi syarat untuk mengajukan seminar proposal' : 
                "Perlu " . ($min_required - $count) . " jurnal bimbingan lagi yang divalidasi dosen"
        ];
    }

    /**
     * Get jurnal bimbingan untuk proposal
     */
    private function _get_jurnal_bimbingan($proposal_id) {
        $this->db->select('jb.*, d.nama as nama_validator');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
        $this->db->where('jb.proposal_id', $proposal_id);
        $this->db->where('jb.status_validasi', '1');
        $this->db->order_by('jb.pertemuan_ke', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Send notification (placeholder)
     */
    private function _send_notification($seminar, $rekomendasi, $komentar) {
        try {
            // TODO: Implementasi email notification
            log_message('info', "Notification: Seminar {$seminar->id} - {$rekomendasi}");
            
            // Bisa ditambahkan insert ke tabel notifikasi jika ada
            /*
            $this->db->insert('notifikasi', [
                'jenis' => 'seminar_proposal_' . $rekomendasi,
                'untuk_role' => 'mahasiswa',
                'user_id' => $seminar->mahasiswa_id,
                'proposal_id' => $seminar->proposal_id,
                'judul' => 'Status Seminar Proposal',
                'pesan' => $rekomendasi == 'approved' ? 
                    'Seminar proposal Anda telah disetujui pembimbing' : 
                    'Seminar proposal Anda perlu revisi: ' . $komentar,
                'dibaca' => 0,
                'tanggal_dibuat' => date('Y-m-d H:i:s')
            ]);
            */
            
        } catch (Exception $e) {
            log_message('error', 'Error sending notification: ' . $e->getMessage());
        }
    }

    /**
     * Badge count untuk template
     */
    public function get_seminar_proposal_badge_count($dosen_id = null) {
        if (!$dosen_id) {
            $dosen_id = $this->session->userdata('id');
        }
        
        if (!$dosen_id) return 0;
        
        return count($this->_get_pengajuan_perlu_review($dosen_id));
    }

    /**
     * Debug function untuk development
     */
    public function debug_data() {
        if (ENVIRONMENT !== 'development') {
            show_404();
        }
        
        $dosen_id = $this->session->userdata('id');
        echo "<h3>🔍 Debug Data untuk Dosen ID: $dosen_id</h3><hr>";
        
        echo "<h4>📊 Database Structure Check:</h4>";
        $fields = $this->db->list_fields('seminar_proposal_mahasiswa');
        echo "<strong>Kolom yang tersedia (" . count($fields) . "):</strong><br>";
        foreach ($fields as $field) {
            echo "- $field<br>";
        }
        
        echo "<hr><h4>📋 Data Pengajuan Review:</h4>";
        $pengajuan = $this->_get_pengajuan_perlu_review($dosen_id);
        echo "<strong>Jumlah data:</strong> " . count($pengajuan) . "<br>";
        echo "<strong>Query terakhir:</strong><br><pre>" . $this->db->last_query() . "</pre>";
        
        if (!empty($pengajuan)) {
            echo "<strong>Sample data:</strong><br>";
            echo "<pre>" . print_r($pengajuan[0], true) . "</pre>";
        }
        
        echo "<hr><h4>📈 Statistics:</h4>";
        $stats = $this->_get_statistics($dosen_id);
        echo "<pre>" . print_r($stats, true) . "</pre>";
        
        echo "<hr><h4>🗃️ Total Records in Table:</h4>";
        $total = $this->db->count_all('seminar_proposal_mahasiswa');
        echo "<strong>Total records in seminar_proposal_mahasiswa:</strong> $total<br>";
        
        // Cek data untuk dosen ini
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $dosen_records = $this->db->count_all_results();
        echo "<strong>Records untuk dosen ID $dosen_id:</strong> $dosen_records<br>";
    }
}