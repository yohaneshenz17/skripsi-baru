<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * FINAL COMPLETE - Seminar Skripsi Controller untuk Kaprodi
 * 
 * WORKFLOW TAHAP 5 LENGKAP:
 * 1. Mahasiswa ajukan → review dosen (approve/reject)
 * 2. Jika approve lanjut ke kaprodi → kaprodi review turnitin
 * 3. Jika turnitin >30% maka reject → kembali ke mahasiswa
 * 4. Jika turnitin <=30% maka approve → notif ke dosen dan mahasiswa
 * 5. Kaprodi melakukan penjadwalan seminar skripsi + penunjukan dosen penguji 1 & 2
 * 6. Tetapkan jadwal → notif ke dosen, mahasiswa dan staf
 * 
 * FITUR LENGKAP:
 * - Review turnitin dengan threshold 30%
 * - Auto rekomendasi dosen penguji dari seminar proposal
 * - Notifikasi email lengkap
 * - Penjadwalan dengan penunjukan penguji
 * - Status workflow yang benar
 * 
 * File: application/controllers/kaprodi/Seminar_skripsi.php
 * URL: https://stkyakobus.ac.id/skripsi/kaprodi/seminar_skripsi/
 */
class Seminar_skripsi extends CI_Controller {

    private $prodi_id;

    public function __construct() {
        parent::__construct();
        
        $this->load->database();
        $this->load->library(['session', 'email', 'upload']);
        $this->load->helper(['url', 'date', 'file', 'text', 'form']);
        
        // Auth check untuk kaprodi
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        $user_level = $this->session->userdata('level');
        if(!in_array($user_level, ['3', '4'])) {
            show_error('Akses ditolak. Anda bukan Kaprodi.', 403);
        }
        
        // Load model dengan fallback
        try {
            $this->load->model('Seminar_skripsi_model', 'seminar_model');
        } catch (Exception $e) {
            log_message('error', 'Failed to load Seminar_skripsi_model: ' . $e->getMessage());
        }
        
        // ENHANCED: Set prodi_id dengan debug
        $this->_set_prodi_id_debug();
    }

    /**
     * ENHANCED: Index dengan debug mode
     */
    public function index() {
        try {
            // Enable debug mode jika parameter debug=1
            $debug_mode = $this->input->get('debug') == '1';
            
            if ($debug_mode) {
                return $this->debug_data();
            }

            // Data normal
            $data = [
                'title' => 'Kelola Seminar Skripsi',
                'pengajuan_review' => $this->_get_pengajuan_perlu_review_enhanced(),
                'stats' => $this->_get_statistics_simple()
            ];
            
            $content = $this->load->view('kaprodi/seminar_skripsi/index', $data, TRUE);
            
            $template_data = [
                'title' => 'Kelola Seminar Skripsi',
                'content' => $content
            ];
            
            $this->load->view('template/kaprodi', $template_data);
            
        } catch (Exception $e) {
            log_message('error', 'Seminar_skripsi index error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            redirect('kaprodi/dashboard');
        }
    }

    /**
     * ✅ NEW: Debug method untuk troubleshooting
     * URL: kaprodi/seminar_skripsi?debug=1
     */
    public function debug_data() {
        // Hanya untuk development atau admin
        if (ENVIRONMENT === 'production' && $this->session->userdata('level') != '1') {
            show_error('Access denied in production', 403);
        }

        echo "<h2>🔍 DEBUG - Kaprodi Seminar Skripsi</h2>";
        echo "<p><a href='" . base_url('kaprodi/seminar_skripsi') . "'>← Kembali ke Dashboard Normal</a></p>";
        
        // 1. DEBUG SESSION
        echo "<h3>1. 📊 Session Data:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr><th>Key</th><th>Value</th></tr>";
        foreach ($this->session->userdata() as $key => $value) {
            if ($key != 'password') { // Jangan tampilkan password
                echo "<tr><td><strong>$key</strong></td><td>" . 
                     (is_array($value) ? json_encode($value) : $value) . "</td></tr>";
            }
        }
        echo "</table>";

        // 2. DEBUG PRODI ID
        echo "<h3>2. 🏫 Prodi ID Detection:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr><th>Source</th><th>Value</th><th>Status</th></tr>";
        
        $session_prodi = $this->session->userdata('prodi_id');
        echo "<tr><td>Session prodi_id</td><td>$session_prodi</td><td>" . 
             ($session_prodi ? '✅ Found' : '❌ Not Set') . "</td></tr>";
        
        $user_id = $this->session->userdata('id');
        $prodi_from_table = $this->db->get_where('prodi', ['dosen_id' => $user_id])->row();
        echo "<tr><td>Prodi table lookup</td><td>" . 
             ($prodi_from_table ? $prodi_from_table->id : 'NULL') . "</td><td>" . 
             ($prodi_from_table ? '✅ Found' : '❌ Not Found') . "</td></tr>";
        
        echo "<tr><td><strong>Active prodi_id</strong></td><td><strong>$this->prodi_id</strong></td><td>" . 
             ($this->prodi_id ? '✅ Set' : '❌ Missing') . "</td></tr>";
        echo "</table>";

        // 3. DEBUG MAHASISWA DATA
        echo "<h3>3. 👨‍🎓 Mahasiswa Data in Prodi:</h3>";
        $mahasiswa_query = $this->db->select('id, nim, nama, prodi_id')
                                   ->from('mahasiswa')
                                   ->where('prodi_id', $this->prodi_id)
                                   ->get();
        
        if ($mahasiswa_query->num_rows() > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Prodi ID</th></tr>";
            foreach ($mahasiswa_query->result() as $mhs) {
                $highlight = ($mhs->nama == 'Mahasiswa Contoh 3') ? 'style="background-color: yellow;"' : '';
                echo "<tr $highlight><td>$mhs->id</td><td>$mhs->nim</td><td>$mhs->nama</td><td>$mhs->prodi_id</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ Tidak ada mahasiswa di prodi_id: $this->prodi_id</p>";
        }

        // 4. DEBUG SEMINAR SKRIPSI DATA
        echo "<h3>4. 📝 Raw Seminar Skripsi Data:</h3>";
        $seminar_query = $this->db->select('
                ssm.id, ssm.mahasiswa_id, ssm.status, ssm.status_pembimbing, ssm.status_kaprodi,
                m.nim, m.nama as nama_mahasiswa, m.prodi_id,
                pm.judul
            ')
            ->from('seminar_skripsi_mahasiswa ssm')
            ->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'left')
            ->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'left')
            ->get();

        if ($seminar_query->num_rows() > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th>SSM ID</th><th>Mahasiswa</th><th>NIM</th><th>Prodi ID</th><th>Status</th><th>Status Pembimbing</th><th>Status Kaprodi</th><th>Match Criteria</th></tr>";
            foreach ($seminar_query->result() as $ssm) {
                $highlight = ($ssm->nama_mahasiswa == 'Mahasiswa Contoh 3') ? 'style="background-color: yellow;"' : '';
                
                // Check if matches criteria
                $match_prodi = ($ssm->prodi_id == $this->prodi_id);
                $match_status_pembimbing = ($ssm->status_pembimbing == 'approved');
                $match_status_kaprodi = ($ssm->status_kaprodi == 'pending');
                $all_match = $match_prodi && $match_status_pembimbing && $match_status_kaprodi;
                
                $criteria_text = "Prodi: " . ($match_prodi ? '✅' : '❌') . " | " .
                               "Pembimbing: " . ($match_status_pembimbing ? '✅' : '❌') . " | " .
                               "Kaprodi: " . ($match_status_kaprodi ? '✅' : '❌') . " | " .
                               "ALL: " . ($all_match ? '✅ MATCH' : '❌ NO MATCH');
                
                echo "<tr $highlight>";
                echo "<td>$ssm->id</td>";
                echo "<td>$ssm->nama_mahasiswa</td>";
                echo "<td>$ssm->nim</td>";
                echo "<td>$ssm->prodi_id</td>";
                echo "<td>$ssm->status</td>";
                echo "<td>$ssm->status_pembimbing</td>";
                echo "<td>$ssm->status_kaprodi</td>";
                echo "<td><small>$criteria_text</small></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ Tidak ada data di tabel seminar_skripsi_mahasiswa</p>";
        }

        // 5. DEBUG QUERY RESULT
        echo "<h3>5. 🔍 Query Result from _get_pengajuan_perlu_review():</h3>";
        $pengajuan = $this->_get_pengajuan_perlu_review_enhanced();
        
        if (!empty($pengajuan)) {
            echo "<p style='color: green;'>✅ Query berhasil, ditemukan " . count($pengajuan) . " pengajuan</p>";
            echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
            echo "<tr><th>ID</th><th>Mahasiswa</th><th>Judul</th><th>Status Pembimbing</th><th>Status Kaprodi</th></tr>";
            foreach ($pengajuan as $p) {
                echo "<tr>";
                echo "<td>$p->id</td>";
                echo "<td>$p->nama_mahasiswa</td>";
                echo "<td>" . substr($p->judul, 0, 50) . "...</td>";
                echo "<td>$p->status_pembimbing</td>";
                echo "<td>$p->status_kaprodi</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ Query tidak mengembalikan data</p>";
        }

        // 6. RECOMMENDED ACTIONS
        echo "<h3>6. 💡 Rekomendasi Perbaikan:</h3>";
        echo "<ul>";
        
        if (!$this->prodi_id) {
            echo "<li>❌ <strong>Set prodi_id untuk kaprodi ini</strong></li>";
        }
        
        $mahasiswa_contoh3 = $this->db->get_where('mahasiswa', ['nama' => 'Mahasiswa Contoh 3'])->row();
        if ($mahasiswa_contoh3) {
            if ($mahasiswa_contoh3->prodi_id != $this->prodi_id) {
                echo "<li>⚠️ <strong>Update prodi_id Mahasiswa Contoh 3 dari {$mahasiswa_contoh3->prodi_id} ke {$this->prodi_id}</strong></li>";
            }
        }
        
        $seminar_contoh3 = $this->db->select('ssm.*, m.nama')
                                   ->from('seminar_skripsi_mahasiswa ssm')
                                   ->join('mahasiswa m', 'ssm.mahasiswa_id = m.id')
                                   ->where('m.nama', 'Mahasiswa Contoh 3')
                                   ->get()->row();
        
        if ($seminar_contoh3) {
            if ($seminar_contoh3->status_pembimbing != 'approved') {
                echo "<li>⚠️ <strong>Update status_pembimbing Mahasiswa Contoh 3 menjadi 'approved'</strong></li>";
            }
            if ($seminar_contoh3->status_kaprodi != 'pending') {
                echo "<li>⚠️ <strong>Update status_kaprodi Mahasiswa Contoh 3 menjadi 'pending'</strong></li>";
            }
        }
        
        echo "</ul>";

        echo "<br><p><a href='" . base_url('kaprodi/seminar_skripsi') . "'>← Kembali ke Dashboard Normal</a></p>";
    }

    
    /**
     * Detail pengajuan - sederhana
     */
    public function detail($seminar_id) {
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }

        try {
            $seminar = $this->_get_seminar_detail($seminar_id);
            
            if (!$seminar) {
                $this->session->set_flashdata('error', 'Data seminar tidak ditemukan!');
                redirect('kaprodi/seminar_skripsi');
                return;
            }

            $data = [
                'title' => 'Review Seminar Skripsi - ' . $seminar->nama_mahasiswa,
                'seminar' => $seminar
            ];

            $content = $this->load->view('kaprodi/seminar_skripsi/detail', $data, TRUE);
            
            $template_data = [
                'title' => 'Review Seminar Skripsi',
                'content' => $content
            ];
            
            $this->load->view('template/kaprodi', $template_data);

        } catch (Exception $e) {
            log_message('error', 'Seminar_skripsi detail error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('kaprodi/seminar_skripsi');
        }
    }

    /**
     * WORKFLOW CORE: Validasi turnitin dengan threshold 30%
     */
    public function validasi_turnitin() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_skripsi');
        }

        try {
            $seminar_id = $this->input->post('seminar_id');
            $plagiarism_percentage = floatval($this->input->post('plagiarism_percentage'));
            $komentar = trim($this->input->post('komentar_kaprodi'));
            $action = $this->input->post('action'); // 'approve' or 'reject'

            // Validasi input
            if (!$seminar_id || !is_numeric($plagiarism_percentage)) {
                throw new Exception('Data tidak lengkap atau tidak valid');
            }

            // Get seminar data
            $seminar = $this->_get_seminar_detail($seminar_id);
            if (!$seminar) {
                throw new Exception('Data seminar tidak ditemukan');
            }

            // WORKFLOW RULE: Cek threshold 30%
            if ($plagiarism_percentage > 30) {
                // Automatic reject jika >30%
                $result = $this->_process_turnitin_validation($seminar_id, 'rejected', $plagiarism_percentage, 
                    'Proposal ditolak karena plagiarisme melebihi 30% ('. $plagiarism_percentage .'%)');
                
                if ($result) {
                    $this->session->set_flashdata('warning', 
                        "Proposal ditolak otomatis karena plagiarisme {$plagiarism_percentage}% > 30%. Mahasiswa dapat mengajukan ulang setelah perbaikan.");
                    
                    // Send notification untuk penolakan
                    $this->_send_rejection_notification($seminar, $plagiarism_percentage);
                }
            } else {
                // Plagiarisme <=30%, bisa approve atau reject manual
                if ($action === 'reject' && empty($komentar)) {
                    throw new Exception('Komentar wajib diisi untuk penolakan manual');
                }
                
                $status = ($action === 'approve') ? 'approved' : 'rejected';
                $final_comment = ($action === 'approve') ? 
                    "Proposal disetujui. Plagiarisme: {$plagiarism_percentage}% (dalam batas wajar ≤30%)" : 
                    $komentar;
                
                $result = $this->_process_turnitin_validation($seminar_id, $status, $plagiarism_percentage, $final_comment);
                
                if ($result) {
                    if ($action === 'approve') {
                        $this->session->set_flashdata('success', 
                            "Seminar skripsi disetujui! Plagiarisme: {$plagiarism_percentage}%. Silakan lanjut ke penjadwalan.");
                        
                        // Send approval notification
                        $this->_send_approval_notification($seminar, $plagiarism_percentage);
                        
                        // Redirect ke penjadwalan
                        redirect('kaprodi/seminar_skripsi/penjadwalan/' . $seminar_id);
                        return;
                    } else {
                        $this->session->set_flashdata('info', 'Seminar skripsi ditolak. Mahasiswa akan mendapat notifikasi.');
                        
                        // Send rejection notification
                        $this->_send_rejection_notification($seminar, $plagiarism_percentage, $komentar);
                    }
                }
            }

            if (!$result) {
                throw new Exception('Gagal memproses validasi');
            }

        } catch (Exception $e) {
            log_message('error', 'Validasi turnitin error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        redirect('kaprodi/seminar_skripsi');
    }

    /**
     * Penjadwalan seminar dengan penunjukan dosen penguji
     */
    public function penjadwalan($seminar_id) {
        if (!is_numeric($seminar_id)) {
            show_404();
            return;
        }

        try {
            $seminar = $this->_get_seminar_detail($seminar_id);
            
            if (!$seminar || $seminar->status_kaprodi != 'approved') {
                $this->session->set_flashdata('error', 'Data tidak valid untuk penjadwalan!');
                redirect('kaprodi/seminar_skripsi');
                return;
            }

            // Get rekomendasi dosen penguji dari seminar proposal
            $rekomendasi_penguji = $this->_get_rekomendasi_dosen_penguji($seminar->proposal_id);

            $data = [
                'title' => 'Penjadwalan Seminar Skripsi',
                'seminar' => $seminar,
                'rekomendasi_penguji' => $rekomendasi_penguji,
                'dosen_list' => $this->_get_dosen_list(),
                'ruang_list' => $this->_get_ruang_list()
            ];

            $content = $this->load->view('kaprodi/seminar_skripsi/penjadwalan', $data, TRUE);
            
            $template_data = [
                'title' => 'Penjadwalan Seminar Skripsi',
                'content' => $content
            ];
            
            $this->load->view('template/kaprodi', $template_data);

        } catch (Exception $e) {
            log_message('error', 'Penjadwalan error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan sistem.');
            redirect('kaprodi/seminar_skripsi');
        }
    }

    /**
     * WORKFLOW FINAL: Simpan jadwal dan penunjukan dosen penguji
     */
    public function simpan_jadwal() {
        if ($this->input->method() !== 'post') {
            redirect('kaprodi/seminar_skripsi');
        }

        try {
            $seminar_id = $this->input->post('seminar_id');
            $tanggal_seminar = $this->input->post('tanggal_seminar');
            $jam_seminar = $this->input->post('jam_seminar');
            $tempat_seminar = trim($this->input->post('tempat_seminar'));
            $dosen_penguji1_id = $this->input->post('dosen_penguji1_id');
            $dosen_penguji2_id = $this->input->post('dosen_penguji2_id');

            // Validasi input
            if (empty($seminar_id) || empty($tanggal_seminar) || empty($jam_seminar) || 
                empty($tempat_seminar) || empty($dosen_penguji1_id) || empty($dosen_penguji2_id)) {
                throw new Exception('Semua field harus diisi');
            }

            if ($dosen_penguji1_id == $dosen_penguji2_id) {
                throw new Exception('Dosen penguji 1 dan penguji 2 harus berbeda');
            }

            // Get seminar data
            $seminar = $this->_get_seminar_detail($seminar_id);
            if (!$seminar || $seminar->status_kaprodi != 'approved') {
                throw new Exception('Data seminar tidak valid untuk penjadwalan');
            }

            // Process penjadwalan
            $result = $this->_process_scheduling($seminar_id, [
                'tanggal_seminar' => $tanggal_seminar,
                'jam_seminar' => $jam_seminar,
                'tempat_seminar' => $tempat_seminar,
                'dosen_penguji1_id' => $dosen_penguji1_id,
                'dosen_penguji2_id' => $dosen_penguji2_id
            ]);

            if ($result) {
                $this->session->set_flashdata('success', 
                    'Jadwal seminar skripsi berhasil ditetapkan! Notifikasi telah dikirim ke semua pihak.');
                
                // Send final scheduling notification
                $this->_send_scheduling_notification($seminar, [
                    'tanggal_seminar' => $tanggal_seminar,
                    'jam_seminar' => $jam_seminar,
                    'tempat_seminar' => $tempat_seminar,
                    'dosen_penguji1_id' => $dosen_penguji1_id,
                    'dosen_penguji2_id' => $dosen_penguji2_id
                ]);
                
            } else {
                throw new Exception('Gagal menyimpan jadwal');
            }

        } catch (Exception $e) {
            log_message('error', 'Simpan jadwal error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        redirect('kaprodi/seminar_skripsi');
    }

    // ===============================================
    // PRIVATE HELPER METHODS
    // ===============================================

    /**
     * Set prodi_id dari session atau database - ENHANCED dengan debugging
     */
    /**
     * ✅ ENHANCED: Set prodi_id dengan debugging
     */
    private function _set_prodi_id_debug() {
        try {
            $user_id = $this->session->userdata('id');
            
            // Check session first
            $this->prodi_id = $this->session->userdata('prodi_id');
            if ($this->prodi_id) {
                log_message('debug', "Prodi ID from session: {$this->prodi_id}");
                return;
            }
            
            // Try from prodi table
            $kaprodi = $this->db->get_where('prodi', ['dosen_id' => $user_id])->row();
            if ($kaprodi) {
                $this->prodi_id = $kaprodi->id;
                $this->session->set_userdata('prodi_id', $this->prodi_id);
                log_message('debug', "Prodi ID from prodi table: {$this->prodi_id}");
                return;
            }
            
            // Fallback: try from dosen table
            $dosen = $this->db->get_where('dosen', ['id' => $user_id])->row();
            if ($dosen && isset($dosen->prodi_id)) {
                $this->prodi_id = $dosen->prodi_id;
                $this->session->set_userdata('prodi_id', $this->prodi_id);
                log_message('debug', "Prodi ID from dosen table: {$this->prodi_id}");
                return;
            }
            
            // Final fallback
            $this->prodi_id = 1; // Default ke prodi_id = 1
            $this->session->set_userdata('prodi_id', $this->prodi_id);
            log_message('debug', "Prodi ID fallback: {$this->prodi_id}");
            
        } catch (Exception $e) {
            log_message('error', 'Error setting prodi_id: ' . $e->getMessage());
            $this->prodi_id = 1; // Default fallback
        }
    }

    /**
    * FIXED: Get pengajuan - ikuti pola persis dari controller dosen
     */
    private function _get_pengajuan_perlu_review_enhanced() {
        try {
            // Log query attempt
            log_message('debug', "Attempting to get seminar skripsi for prodi_id: {$this->prodi_id}");
            
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id,
                d.nama as nama_pembimbing
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'inner'); // Changed to INNER
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'inner'); // Changed to INNER
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            
            // Main criteria
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.status_pembimbing', 'approved');
            $this->db->where('ssm.status_kaprodi', 'pending');
            
            $this->db->order_by('ssm.created_at', 'DESC');
            
            // Log the query
            $query_string = $this->db->get_compiled_select();
            log_message('debug', 'Seminar skripsi query: ' . $query_string);
            
            // Reset query and execute
            $this->db->select('
                ssm.*,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id,
                d.nama as nama_pembimbing
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id', 'inner');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id', 'inner');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.status_pembimbing', 'approved');
            $this->db->where('ssm.status_kaprodi', 'pending');
            $this->db->order_by('ssm.created_at', 'DESC');
            
            $result = $this->db->get()->result();
            
            log_message('debug', 'Seminar skripsi result count: ' . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error in _get_pengajuan_perlu_review: ' . $e->getMessage());
            return [];
        }
    }

    
    /**
     * Get seminar yang perlu dijadwalkan (sudah approved tapi belum dijadwal)
     */
    private function _get_seminar_perlu_dijadwalkan() {
        try {
            $this->db->select('
                ssm.*,
                pm.judul,
                m.nim,
                m.nama as nama_mahasiswa,
                d.nama as nama_pembimbing
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.status_kaprodi', 'approved');
            $this->db->where('ssm.status', 'approved');
            $this->db->where('ssm.tanggal_seminar IS NULL'); // Belum dijadwalkan
            $this->db->order_by('ssm.tanggal_review_kaprodi', 'ASC');
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar perlu dijadwalkan: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get ALL seminar list untuk monitoring keseluruhan proses
     * ADDED: Tampilkan semua pengajuan dengan berbagai status
     */
    private function _get_all_seminar_list() {
        try {
            $this->db->select('
                ssm.*,
                pm.judul,
                m.nim,
                m.nama as nama_mahasiswa,
                d.nama as nama_pembimbing,
                CASE 
                    WHEN ssm.status_pembimbing = "pending" AND ssm.current_step = "pembimbing" THEN "Menunggu Review Dosen"
                    WHEN ssm.status_pembimbing = "approved" AND ssm.status_kaprodi = "pending" THEN "Menunggu Review Kaprodi"
                    WHEN ssm.status_kaprodi = "approved" AND ssm.tanggal_seminar IS NULL THEN "Menunggu Penjadwalan"
                    WHEN ssm.status_kaprodi = "approved" AND ssm.tanggal_seminar IS NOT NULL THEN "Terjadwal"
                    WHEN ssm.status_kaprodi = "rejected" THEN "Ditolak"
                    WHEN ssm.status_pembimbing = "rejected" THEN "Ditolak Dosen"
                    ELSE "Status Tidak Dikenal"
                END as status_text
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->order_by('ssm.created_at', 'DESC');
            $this->db->limit(20); // Batasi 20 pengajuan terbaru
            
            $result = $this->db->get()->result();
            
            // Debug log
            log_message('debug', 'All seminar list: ' . count($result) . ' records found');
            
            return $result;
        } catch (Exception $e) {
            log_message('error', 'Error getting all seminar list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get jadwal seminar mendatang
     */
    private function _get_jadwal_mendatang() {
        try {
            $this->db->select('
                ssm.*,
                pm.judul,
                m.nim,
                m.nama as nama_mahasiswa,
                d.nama as nama_pembimbing,
                d1.nama as nama_penguji1,
                d2.nama as nama_penguji2
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
            $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.status', 'scheduled');
            $this->db->where('ssm.tanggal_seminar >=', date('Y-m-d'));
            $this->db->order_by('ssm.tanggal_seminar', 'ASC');
            $this->db->order_by('ssm.jam_seminar', 'ASC');
            $this->db->limit(10);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting jadwal mendatang: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get riwayat review kaprodi
     */
    private function _get_riwayat_review() {
        try {
            $this->db->select('
                ssm.*,
                pm.judul,
                m.nim,
                m.nama as nama_mahasiswa
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.reviewed_by_kaprodi', $this->session->userdata('id'));
            $this->db->where_in('ssm.status_kaprodi', ['approved', 'rejected']);
            $this->db->order_by('ssm.tanggal_review_kaprodi', 'DESC');
            $this->db->limit(5);
            
            return $this->db->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat review: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * FIXED: Get statistics - pastikan query PERSIS sama dengan list
     */
    private function _get_statistics_simple() {
        $stats = [];
        
        try {
            // Perlu review
            $this->db->select('COUNT(*) as perlu_review');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.status_pembimbing', 'approved');
            $this->db->where('ssm.status_kaprodi', 'pending');
            $stats['perlu_review'] = $this->db->get()->row()->perlu_review ?? 0;
            
            // Disetujui bulan ini
            $this->db->select('COUNT(*) as disetujui');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.status_kaprodi', 'approved');
            $this->db->where('MONTH(ssm.tanggal_review_kaprodi)', date('n'));
            $this->db->where('YEAR(ssm.tanggal_review_kaprodi)', date('Y'));
            $stats['disetujui'] = $this->db->get()->row()->disetujui ?? 0;
            
            // Ditolak
            $this->db->select('COUNT(*) as ditolak');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.status_kaprodi', 'rejected');
            $stats['ditolak'] = $this->db->get()->row()->ditolak ?? 0;
            
            // Terjadwal
            $this->db->select('COUNT(*) as terjadwal');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $this->prodi_id);
            $this->db->where('ssm.status', 'scheduled');
            $stats['terjadwal'] = $this->db->get()->row()->terjadwal ?? 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            $stats = ['perlu_review' => 0, 'disetujui' => 0, 'ditolak' => 0, 'terjadwal' => 0];
        }
        
        return $stats;
    }

    /**
     * Get detail seminar dengan relasi lengkap
     */
    private function _get_seminar_detail($seminar_id) {
        try {
            $this->db->select('
                ssm.*,
                pm.judul,
                pm.abstrak,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing
            ');
            $this->db->from('seminar_skripsi_mahasiswa ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('ssm.id', $seminar_id);
            $this->db->where('m.prodi_id', $this->prodi_id);
            
            return $this->db->get()->row();
        } catch (Exception $e) {
            log_message('error', 'Error getting seminar detail: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get rekomendasi dosen penguji dari seminar proposal sebelumnya
     */
    private function _get_rekomendasi_dosen_penguji($proposal_id) {
        try {
            // Cari dosen penguji dari seminar proposal
            $this->db->select('spm.dosen_penguji1_id, spm.dosen_penguji2_id');
            $this->db->from('seminar_proposal_mahasiswa spm');
            $this->db->where('spm.proposal_id', $proposal_id);
            $this->db->where('spm.status_kaprodi', 'approved');
            $seminar_proposal = $this->db->get()->row();
            
            if (!$seminar_proposal) {
                return null;
            }
            
            // Get data dosen penguji
            $penguji1 = null;
            $penguji2 = null;
            
            if ($seminar_proposal->dosen_penguji1_id) {
                $penguji1 = $this->db->get_where('dosen', ['id' => $seminar_proposal->dosen_penguji1_id])->row();
            }
            
            if ($seminar_proposal->dosen_penguji2_id) {
                $penguji2 = $this->db->get_where('dosen', ['id' => $seminar_proposal->dosen_penguji2_id])->row();
            }
            
            return [
                'penguji1' => $penguji1,
                'penguji2' => $penguji2
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting rekomendasi penguji: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get daftar dosen untuk penguji
     */
    private function _get_dosen_list() {
        try {
            return $this->db->select('id, nama, email')
                           ->from('dosen')
                           ->where('status', 'aktif')
                           ->order_by('nama', 'ASC')
                           ->get()->result();
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get daftar ruang untuk seminar
     */
    private function _get_ruang_list() {
        return [
            'Ruang Seminar 1',
            'Ruang Seminar 2', 
            'Ruang Kelas A',
            'Ruang Kelas B',
            'Aula'
        ];
    }

    /**
     * Check apakah seminar bisa direview - FIXED: Logic yang lebih fleksibel
     */
    private function _can_review_seminar($seminar) {
        // Bisa review jika sudah approved dosen dan belum direview kaprodi
        return $seminar->status_pembimbing == 'approved' && 
               $seminar->status_kaprodi == 'pending';
    }

    /**
     * CORE WORKFLOW: Process turnitin validation
     */
    private function _process_turnitin_validation($seminar_id, $status, $plagiarism_percentage, $komentar) {
        $this->db->trans_start();
        
        try {
            // Update seminar_skripsi_mahasiswa
            $update_data = [
                'status_kaprodi' => $status,
                'komentar_kaprodi' => $komentar,
                'tanggal_review_kaprodi' => date('Y-m-d H:i:s'),
                'reviewed_by_kaprodi' => $this->session->userdata('id'),
                'plagiarism_percentage' => $plagiarism_percentage,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle file turnitin upload jika ada
            if (!empty($_FILES['file_turnitin']['name'])) {
                $upload_result = $this->_handle_turnitin_upload();
                if ($upload_result['success']) {
                    $update_data['file_turnitin'] = $upload_result['filename'];
                }
            }
            
            // Update status dan current_step berdasarkan hasil validasi
            if ($status == 'approved') {
                $update_data['status'] = 'approved';
                $update_data['current_step'] = 'kaprodi_jadwal'; // Lanjut ke penjadwalan
            } else {
                $update_data['status'] = 'rejected';
                $update_data['current_step'] = 'mahasiswa'; // Kembali ke mahasiswa untuk perbaikan
            }
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            // Update workflow_status di proposal_mahasiswa jika diperlukan
            if ($status == 'approved') {
                $seminar = $this->db->get_where('seminar_skripsi_mahasiswa', ['id' => $seminar_id])->row();
                if ($seminar) {
                    $this->db->where('id', $seminar->proposal_id);
                    $this->db->update('proposal_mahasiswa', [
                        'workflow_status' => 'seminar_skripsi',
                        'status_seminar_skripsi' => '1' // Approved
                    ]);
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Process turnitin validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * CORE WORKFLOW: Process scheduling dengan penunjukan dosen penguji
     */
    private function _process_scheduling($seminar_id, $schedule_data) {
        $this->db->trans_start();
        
        try {
            // Update seminar_skripsi_mahasiswa dengan jadwal dan penguji
            $update_data = [
                'tanggal_seminar' => $schedule_data['tanggal_seminar'],
                'jam_seminar' => $schedule_data['jam_seminar'],
                'tempat_seminar' => $schedule_data['tempat_seminar'],
                'dosen_penguji1_id' => $schedule_data['dosen_penguji1_id'],
                'dosen_penguji2_id' => $schedule_data['dosen_penguji2_id'],
                'status_penguji1' => 'approved', // Auto approved (kebijakan STK)
                'status_penguji2' => 'approved', // Auto approved (kebijakan STK)
                'tanggal_respon_penguji1' => date('Y-m-d H:i:s'),
                'tanggal_respon_penguji2' => date('Y-m-d H:i:s'),
                'status' => 'scheduled',
                'current_step' => 'staf', // Lanjut ke staf untuk monitoring
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $seminar_id);
            $this->db->update('seminar_skripsi_mahasiswa', $update_data);
            
            // Update proposal_mahasiswa workflow status
            $seminar = $this->db->get_where('seminar_skripsi_mahasiswa', ['id' => $seminar_id])->row();
            if ($seminar) {
                $this->db->where('id', $seminar->proposal_id);
                $this->db->update('proposal_mahasiswa', [
                    'tanggal_seminar_skripsi' => $schedule_data['tanggal_seminar'],
                    'tempat_seminar_skripsi' => $schedule_data['tempat_seminar']
                ]);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Process scheduling error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle file upload turnitin
     */
    private function _handle_turnitin_upload() {
        $config = [
            'upload_path' => './uploads/turnitin/',
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 5120, // 5MB
            'encrypt_name' => TRUE
        ];
        
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }
        
        $this->upload->initialize($config);
        
        if ($this->upload->do_upload('file_turnitin')) {
            return [
                'success' => true,
                'filename' => $this->upload->data('file_name')
            ];
        } else {
            return [
                'success' => false,
                'error' => $this->upload->display_errors()
            ];
        }
    }

    // ===============================================
    // NOTIFICATION METHODS
    // ===============================================

    /**
     * Send approval notification
     */
    private function _send_approval_notification($seminar, $plagiarism_percentage) {
        try {
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
            
            // Email ke mahasiswa
            $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('[SIM-TA] Seminar Skripsi Disetujui');
            
            $message = "
            <h3>Seminar Skripsi Disetujui</h3>
            <p>Yth. {$seminar->nama_mahasiswa},</p>
            <p>Pengajuan seminar skripsi Anda telah <strong>DISETUJUI</strong> oleh Kaprodi.</p>
            <ul>
                <li><strong>Judul:</strong> {$seminar->judul}</li>
                <li><strong>Persentase Plagiarisme:</strong> {$plagiarism_percentage}% (dalam batas wajar ≤30%)</li>
                <li><strong>Status:</strong> Menunggu penjadwalan</li>
            </ul>
            <p>Kaprodi akan segera melakukan penjadwalan seminar. Anda akan mendapat notifikasi jadwal melalui email ini.</p>
            <p>Terima kasih.</p>
            ";
            
            $this->email->message($message);
            $this->email->send();
            
            // Email ke dosen pembimbing
            if (!empty($seminar->email_pembimbing)) {
                $this->email->clear();
                $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
                $this->email->to($seminar->email_pembimbing);
                $this->email->subject('[SIM-TA] Seminar Skripsi Mahasiswa Bimbingan Disetujui');
                
                $message_dosen = "
                <h3>Seminar Skripsi Mahasiswa Bimbingan Disetujui</h3>
                <p>Yth. {$seminar->nama_pembimbing},</p>
                <p>Seminar skripsi mahasiswa bimbingan Anda telah disetujui oleh Kaprodi:</p>
                <ul>
                    <li><strong>Nama Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
                    <li><strong>Judul:</strong> {$seminar->judul}</li>
                    <li><strong>Persentase Plagiarisme:</strong> {$plagiarism_percentage}%</li>
                </ul>
                <p>Kaprodi akan segera melakukan penjadwalan seminar.</p>
                ";
                
                $this->email->message($message_dosen);
                $this->email->send();
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Send approval notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send rejection notification
     */
    private function _send_rejection_notification($seminar, $plagiarism_percentage, $komentar = '') {
        try {
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
            
            // Email ke mahasiswa
            $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($seminar->email_mahasiswa);
            $this->email->subject('[SIM-TA] Seminar Skripsi Ditolak - Perlu Perbaikan');
            
            $auto_reject = ($plagiarism_percentage > 30);
            $reason = $auto_reject ? 
                "plagiarisme melebihi batas maksimal 30% ({$plagiarism_percentage}%)" : 
                "alasan berikut";
            
            $message = "
            <h3>Seminar Skripsi Ditolak</h3>
            <p>Yth. {$seminar->nama_mahasiswa},</p>
            <p>Pengajuan seminar skripsi Anda <strong>DITOLAK</strong> karena {$reason}:</p>
            <ul>
                <li><strong>Judul:</strong> {$seminar->judul}</li>
                <li><strong>Persentase Plagiarisme:</strong> {$plagiarism_percentage}%</li>
            </ul>";
            
            if (!empty($komentar)) {
                $message .= "<p><strong>Komentar Kaprodi:</strong><br>" . nl2br($komentar) . "</p>";
            }
            
            $message .= "
            <p><strong>Langkah selanjutnya:</strong></p>
            <ol>
                <li>Perbaiki dokumen skripsi sesuai komentar</li>
                <li>Pastikan plagiarisme ≤30%</li>
                <li>Ajukan ulang melalui sistem</li>
            </ol>
            <p>Silakan berkonsultasi dengan dosen pembimbing untuk perbaikan.</p>
            ";
            
            $this->email->message($message);
            $this->email->send();
            
            // Email ke dosen pembimbing
            if (!empty($seminar->email_pembimbing)) {
                $this->email->clear();
                $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
                $this->email->to($seminar->email_pembimbing);
                $this->email->subject('[SIM-TA] Seminar Skripsi Mahasiswa Bimbingan Ditolak');
                
                $message_dosen = "
                <h3>Seminar Skripsi Mahasiswa Bimbingan Ditolak</h3>
                <p>Yth. {$seminar->nama_pembimbing},</p>
                <p>Seminar skripsi mahasiswa bimbingan Anda ditolak oleh Kaprodi:</p>
                <ul>
                    <li><strong>Nama Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
                    <li><strong>Judul:</strong> {$seminar->judul}</li>
                    <li><strong>Persentase Plagiarisme:</strong> {$plagiarism_percentage}%</li>
                </ul>
                <p>Silakan bimbing mahasiswa untuk melakukan perbaikan sebelum mengajukan ulang.</p>
                ";
                
                $this->email->message($message_dosen);
                $this->email->send();
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Send rejection notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send final scheduling notification ke semua pihak
     */
    private function _send_scheduling_notification($seminar, $schedule_data) {
        try {
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
            
            // Get data dosen penguji
            $penguji1 = $this->db->get_where('dosen', ['id' => $schedule_data['dosen_penguji1_id']])->row();
            $penguji2 = $this->db->get_where('dosen', ['id' => $schedule_data['dosen_penguji2_id']])->row();
            
            $tanggal_formatted = date('d F Y', strtotime($schedule_data['tanggal_seminar']));
            $jam_formatted = date('H:i', strtotime($schedule_data['jam_seminar']));
            
            $recipients = [
                [
                    'email' => $seminar->email_mahasiswa,
                    'nama' => $seminar->nama_mahasiswa,
                    'role' => 'mahasiswa'
                ],
                [
                    'email' => $seminar->email_pembimbing,
                    'nama' => $seminar->nama_pembimbing,
                    'role' => 'pembimbing'
                ],
                [
                    'email' => $penguji1->email,
                    'nama' => $penguji1->nama,
                    'role' => 'penguji1'
                ],
                [
                    'email' => $penguji2->email,
                    'nama' => $penguji2->nama,
                    'role' => 'penguji2'
                ]
            ];
            
            foreach ($recipients as $recipient) {
                if (empty($recipient['email'])) continue;
                
                $this->email->clear();
                $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
                $this->email->to($recipient['email']);
                $this->email->subject('[SIM-TA] Jadwal Seminar Skripsi - ' . $seminar->nama_mahasiswa);
                
                $role_text = ($recipient['role'] == 'mahasiswa') ? 'Yth. ' . $recipient['nama'] : 
                            'Yth. ' . $recipient['nama'] . ' (' . ucfirst($recipient['role']) . ')';
                
                $message = "
                <h3>Jadwal Seminar Skripsi</h3>
                <p>{$role_text},</p>
                <p>Seminar skripsi telah dijadwalkan dengan detail sebagai berikut:</p>
                <table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; width:100%; max-width:500px;'>
                    <tr><td><strong>Nama Mahasiswa</strong></td><td>{$seminar->nama_mahasiswa}</td></tr>
                    <tr><td><strong>NIM</strong></td><td>{$seminar->nim}</td></tr>
                    <tr><td><strong>Judul</strong></td><td>{$seminar->judul}</td></tr>
                    <tr><td><strong>Tanggal</strong></td><td>{$tanggal_formatted}</td></tr>
                    <tr><td><strong>Jam</strong></td><td>{$jam_formatted} WIB</td></tr>
                    <tr><td><strong>Tempat</strong></td><td>{$schedule_data['tempat_seminar']}</td></tr>
                    <tr><td><strong>Pembimbing</strong></td><td>{$seminar->nama_pembimbing}</td></tr>
                    <tr><td><strong>Penguji 1</strong></td><td>{$penguji1->nama}</td></tr>
                    <tr><td><strong>Penguji 2</strong></td><td>{$penguji2->nama}</td></tr>
                </table>
                <p>Harap hadir tepat waktu. Terima kasih.</p>
                ";
                
                $this->email->message($message);
                $this->email->send();
            }
            
            // Send notification ke staf juga
            $this->_send_staff_notification($seminar, $schedule_data);
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Send scheduling notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification ke staf
     */
    private function _send_staff_notification($seminar, $schedule_data) {
        try {
            // Get email staf
            $staf_emails = $this->db->select('email')
                                   ->from('dosen')
                                   ->where('level', '1') // Level 1 = staf
                                   ->where('email IS NOT NULL')
                                   ->where('email !=', '')
                                   ->get()->result();
            
            if (empty($staf_emails)) return true;
            
            $tanggal_formatted = date('d F Y', strtotime($schedule_data['tanggal_seminar']));
            $jam_formatted = date('H:i', strtotime($schedule_data['jam_seminar']));
            
            foreach ($staf_emails as $staf) {
                $this->email->clear();
                $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK Santo Yakobus');
                $this->email->to($staf->email);
                $this->email->subject('[SIM-TA] Jadwal Seminar Skripsi Baru - ' . $seminar->nama_mahasiswa);
                
                $message = "
                <h3>Jadwal Seminar Skripsi Baru</h3>
                <p>Yth. Staf Akademik,</p>
                <p>Kaprodi telah menetapkan jadwal seminar skripsi:</p>
                <ul>
                    <li><strong>Mahasiswa:</strong> {$seminar->nama_mahasiswa} ({$seminar->nim})</li>
                    <li><strong>Tanggal:</strong> {$tanggal_formatted}</li>
                    <li><strong>Jam:</strong> {$jam_formatted} WIB</li>
                    <li><strong>Tempat:</strong> {$schedule_data['tempat_seminar']}</li>
                </ul>
                <p>Silakan lakukan persiapan administrasi yang diperlukan.</p>
                ";
                
                $this->email->message($message);
                $this->email->send();
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Send staff notification error: ' . $e->getMessage());
            return false;
        }
    }
}