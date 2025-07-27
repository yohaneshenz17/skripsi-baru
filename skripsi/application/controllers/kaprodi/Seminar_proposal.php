<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Kaprodi - Seminar Proposal
 * Mengelola validasi, cek plagiasi, dan penjadwalan seminar proposal
 * 
 * @property Seminar_proposal_model $seminar_model
 * @property CI_Upload $upload
 * @property CI_Email $email
 */
class Seminar_proposal extends CI_Controller {

    private $prodi_id;
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['session', 'form_validation', 'upload', 'email']);
        $this->load->helper(['url', 'form', 'file', 'date']);
        $this->load->model('Seminar_proposal_model', 'seminar_model');
        
        // Cek login dan level kaprodi
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        // Ambil prodi_id dari session atau database
        $this->prodi_id = $this->session->userdata('prodi_id');
        if (!$this->prodi_id) {
            $prodi = $this->db->get_where('prodi', ['dosen_id' => $this->session->userdata('id')])->row();
            if ($prodi) {
                $this->session->set_userdata('prodi_id', $prodi->id);
                $this->prodi_id = $prodi->id;
            } else {
                $this->session->set_flashdata('error', 'Data prodi tidak ditemukan!');
                redirect('auth/logout');
            }
        }
        
        // Load helper classes
        $this->load->library('Seminar_proposal_validation', 'validation');
        $this->load->library('Seminar_proposal_helper', 'helper');
    }

    /**
     * ========================================
     * HALAMAN UTAMA KAPRODI SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Dashboard seminar proposal untuk kaprodi
     */
    public function index() {
        $data['title'] = 'Seminar Proposal - Validasi & Penjadwalan';
        $data['kaprodi_name'] = $this->session->userdata('nama');
        
        // Daftar seminar proposal untuk validasi (sudah direkomendasikan pembimbing)
        $data['seminar_validasi'] = $this->seminar_model->get_seminar_untuk_kaprodi($this->prodi_id);
        
        // Statistik per prodi
        $data['stats'] = $this->_get_kaprodi_stats();
        
        // Seminar yang dijadwalkan bulan ini
        $this->db->select('spv.*, DATE_FORMAT(spv.tanggal_seminar, "%d/%m/%Y") as tanggal_format, TIME_FORMAT(spv.waktu_mulai, "%H:%i") as waktu_format');
        $this->db->from('seminar_proposal_view spv');
        $this->db->where('spv.prodi_id', $this->prodi_id);
        $this->db->where('spv.status_final', 'approved');
        $this->db->where('MONTH(spv.tanggal_seminar)', date('m'));
        $this->db->where('YEAR(spv.tanggal_seminar)', date('Y'));
        $this->db->order_by('spv.tanggal_seminar', 'ASC');
        
        $data['jadwal_bulan_ini'] = $this->db->get()->result();
        
        // Notifikasi terbaru
        $data['notifikasi_terbaru'] = $this->_get_notifikasi_terbaru(5);
        
        $this->load->view('kaprodi/seminar_proposal/index', $data);
    }

    /**
     * ========================================
     * VALIDASI SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Halaman detail untuk validasi seminar proposal
     */
    public function detail($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        // Ambil data seminar lengkap
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar) {
            show_404();
        }
        
        // Validasi prodi
        if ($seminar->prodi_id != $this->prodi_id) {
            $this->session->set_flashdata('error', 'Seminar proposal bukan dari prodi Anda!');
            redirect('kaprodi/seminar_proposal');
        }
        
        // Cek apakah sudah direkomendasikan pembimbing
        if ($seminar->rekomendasi_pembimbing != '1') {
            $this->session->set_flashdata('warning', 'Seminar proposal belum direkomendasikan oleh dosen pembimbing!');
            redirect('kaprodi/seminar_proposal');
        }
        
        // Ambil jurnal bimbingan mahasiswa untuk verifikasi
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $seminar->proposal_id);
        $this->db->where('status_validasi', '1');
        $this->db->order_by('pertemuan_ke', 'ASC');
        
        $data['jurnal_bimbingan'] = $this->db->get()->result();
        
        // Daftar dosen untuk penguji (exclude pembimbing)
        $this->db->select('id, nama, nip, email');
        $this->db->from('dosen');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->where('level', '2'); // Hanya dosen biasa
        $this->db->where('id !=', $seminar->dosen_id); // Exclude pembimbing
        $this->db->order_by('nama', 'ASC');
        
        $data['dosen_penguji'] = $this->db->get()->result();
        
        // Timeline aktivitas
        $this->db->select('las.*, d.nama as pelaku_nama');
        $this->db->from('log_aktivitas_seminar_proposal las');
        $this->db->join('dosen d', 'las.dilakukan_oleh = d.id', 'left');
        $this->db->where('las.seminar_proposal_id', $seminar_id);
        $this->db->order_by('las.created_at', 'DESC');
        $this->db->limit(10);
        
        $data['timeline'] = $this->db->get()->result();
        
        $data['seminar'] = $seminar;
        $data['title'] = 'Validasi Seminar Proposal - ' . $seminar->nama_mahasiswa;
        $data['max_plagiasi'] = Seminar_proposal_config::MAX_PLAGIASI_PERCENT;
        
        $this->load->view('kaprodi/seminar_proposal/detail', $data);
    }
    
    /**
     * Proses validasi seminar proposal oleh kaprodi
     */
    public function proses_validasi() {
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan!']);
            return;
        }
        
        // Set validation rules
        $this->form_validation->set_rules($this->validation->rules_validasi_kaprodi());
        
        if (!$this->form_validation->run()) {
            echo json_encode([
                'error' => true, 
                'message' => 'Validasi gagal!',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        // Validasi file turnitin jika diupload
        if (isset($_FILES['file_turnitin']) && $_FILES['file_turnitin']['error'] === UPLOAD_ERR_OK) {
            $file_validation = Seminar_proposal_helper::validate_file_upload($_FILES['file_turnitin'], 1);
            if (!$file_validation['valid']) {
                echo json_encode(['error' => true, 'message' => 'File turnitin: ' . $file_validation['message']]);
                return;
            }
            
            // Scan malware
            if (!Seminar_proposal_helper::basic_malware_scan($_FILES['file_turnitin']['tmp_name'])) {
                echo json_encode(['error' => true, 'message' => 'File turnitin tidak aman! Terdeteksi potensi malware.']);
                return;
            }
        }
        
        // Prepare data untuk model
        $data = [
            'seminar_id' => $this->input->post('seminar_id'),
            'validasi' => $this->input->post('validasi'),
            'catatan_kaprodi' => $this->input->post('catatan_kaprodi'),
            'kaprodi_id' => $this->session->userdata('id'),
            'persentase_plagiasi' => $this->input->post('persentase_plagiasi'),
            'catatan_plagiasi' => $this->input->post('catatan_plagiasi')
        ];
        
        // Data penjadwalan jika disetujui
        if ($data['validasi'] == '1') {
            $data['tanggal_seminar'] = $this->input->post('tanggal_seminar');
            $data['waktu_mulai'] = $this->input->post('waktu_mulai');
            $data['waktu_selesai'] = $this->input->post('waktu_selesai');
            $data['tempat_seminar'] = $this->input->post('tempat_seminar');
            $data['dosen_penguji_1_id'] = $this->input->post('dosen_penguji_1_id');
            $data['dosen_penguji_2_id'] = $this->input->post('dosen_penguji_2_id');
        }
        
        $result = $this->seminar_model->proses_validasi_kaprodi($data);
        
        // Log aktivitas
        if (!$result['error']) {
            $status_text = ($data['validasi'] == '1') ? 'menyetujui' : 'menolak';
            $this->_log_aktivitas($data['seminar_id'], 'validasi', 
                                 "Kaprodi {$status_text} validasi seminar proposal", $data);
        }
        
        echo json_encode($result);
    }

    /**
     * ========================================
     * PENJADWALAN ULANG & PENGUJI
     * ========================================
     */
    
    /**
     * Halaman penjadwalan ulang seminar proposal
     */
    public function penjadwalan_ulang($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar || $seminar->prodi_id != $this->prodi_id) {
            show_404();
        }
        
        // Cek apakah memerlukan penjadwalan ulang
        $need_reschedule = ($seminar->status_persetujuan_penguji_1 == '2' || 
                           $seminar->status_persetujuan_penguji_2 == '2');
        
        if (!$need_reschedule) {
            $this->session->set_flashdata('info', 'Seminar proposal tidak memerlukan penjadwalan ulang.');
            redirect('kaprodi/seminar_proposal/detail/' . $seminar_id);
        }
        
        // Daftar dosen untuk penguji baru (exclude pembimbing dan penguji sebelumnya)
        $this->db->select('id, nama, nip, email');
        $this->db->from('dosen');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->where('level', '2');
        $this->db->where('id !=', $seminar->dosen_id); // Exclude pembimbing
        $this->db->where('id !=', $seminar->dosen_penguji_1_id); // Exclude penguji 1 lama
        $this->db->where('id !=', $seminar->dosen_penguji_2_id); // Exclude penguji 2 lama
        $this->db->order_by('nama', 'ASC');
        
        $data['dosen_alternatif'] = $this->db->get()->result();
        
        $data['seminar'] = $seminar;
        $data['title'] = 'Penjadwalan Ulang Seminar Proposal';
        
        // Alasan penolakan dari penguji
        $data['alasan_penolakan'] = [];
        if ($seminar->status_persetujuan_penguji_1 == '2') {
            $data['alasan_penolakan']['penguji_1'] = $seminar->catatan_penguji_1;
        }
        if ($seminar->status_persetujuan_penguji_2 == '2') {
            $data['alasan_penolakan']['penguji_2'] = $seminar->catatan_penguji_2;
        }
        
        $this->load->view('kaprodi/seminar_proposal/penjadwalan_ulang', $data);
    }
    
    /**
     * Proses penjadwalan ulang
     */
    public function proses_penjadwalan_ulang() {
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan!']);
            return;
        }
        
        $seminar_id = $this->input->post('seminar_id');
        $tipe_perubahan = $this->input->post('tipe_perubahan'); // 'jadwal_only' atau 'ganti_penguji'
        
        // Validasi data
        if (!$seminar_id || !in_array($tipe_perubahan, ['jadwal_only', 'ganti_penguji'])) {
            echo json_encode(['error' => true, 'message' => 'Data tidak valid!']);
            return;
        }
        
        $update_data = [
            'tanggal_seminar' => $this->input->post('tanggal_seminar'),
            'waktu_mulai' => $this->input->post('waktu_mulai'),
            'waktu_selesai' => $this->input->post('waktu_selesai'),
            'tempat_seminar' => $this->input->post('tempat_seminar'),
            'jumlah_reschedule' => 'jumlah_reschedule + 1',
            'alasan_reschedule' => $this->input->post('alasan_reschedule'),
            'status_final' => 'reschedule'
        ];
        
        // Jika ganti penguji, reset status persetujuan
        if ($tipe_perubahan == 'ganti_penguji') {
            $penguji_1_baru = $this->input->post('dosen_penguji_1_id');
            $penguji_2_baru = $this->input->post('dosen_penguji_2_id');
            
            if ($penguji_1_baru) {
                $update_data['dosen_penguji_1_id'] = $penguji_1_baru;
                $update_data['status_persetujuan_penguji_1'] = '0';
                $update_data['catatan_penguji_1'] = null;
                $update_data['tanggal_respon_penguji_1'] = null;
            }
            
            if ($penguji_2_baru) {
                $update_data['dosen_penguji_2_id'] = $penguji_2_baru;
                $update_data['status_persetujuan_penguji_2'] = '0';
                $update_data['catatan_penguji_2'] = null;
                $update_data['tanggal_respon_penguji_2'] = null;
            }
        } else {
            // Hanya jadwal ulang, reset status yang menolak saja
            $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
            if ($seminar->status_persetujuan_penguji_1 == '2') {
                $update_data['status_persetujuan_penguji_1'] = '0';
                $update_data['catatan_penguji_1'] = null;
                $update_data['tanggal_respon_penguji_1'] = null;
            }
            if ($seminar->status_persetujuan_penguji_2 == '2') {
                $update_data['status_persetujuan_penguji_2'] = '0';
                $update_data['catatan_penguji_2'] = null;
                $update_data['tanggal_respon_penguji_2'] = null;
            }
        }
        
        try {
            $this->db->where('id', $seminar_id);
            $this->db->set('jumlah_reschedule', 'jumlah_reschedule + 1', FALSE);
            $result = $this->db->update('seminar_proposal', $update_data);
            
            if ($result) {
                // Kirim notifikasi ke penguji (baru/lama)
                $this->_kirim_notifikasi_reschedule($seminar_id, $tipe_perubahan);
                
                // Log aktivitas
                $this->_log_aktivitas($seminar_id, 'reschedule', 
                                     "Kaprodi melakukan penjadwalan ulang: {$tipe_perubahan}", $update_data);
                
                echo json_encode([
                    'error' => false, 
                    'message' => 'Penjadwalan ulang berhasil! Notifikasi telah dikirim ke dosen penguji.'
                ]);
            } else {
                echo json_encode(['error' => true, 'message' => 'Gagal menyimpan penjadwalan ulang!']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['error' => true, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * ========================================
     * MONITORING & LAPORAN
     * ========================================
     */
    
    /**
     * Halaman monitoring seminar proposal
     */
    public function monitoring() {
        // Filter
        $status_filter = $this->input->get('status');
        $bulan_filter = $this->input->get('bulan') ?: date('Y-m');
        $dosen_filter = $this->input->get('dosen');
        
        // Base query
        $this->db->select('spv.*');
        $this->db->from('seminar_proposal_view spv');
        $this->db->where('spv.prodi_id', $this->prodi_id);
        
        // Apply filters
        if ($status_filter) {
            switch ($status_filter) {
                case 'menunggu_validasi':
                    $this->db->where('spv.rekomendasi_pembimbing', '1');
                    $this->db->where('spv.status_validasi_kaprodi', '0');
                    break;
                case 'menunggu_persetujuan':
                    $this->db->where('spv.status_validasi_kaprodi', '1');
                    $this->db->group_start();
                    $this->db->where('spv.status_persetujuan_penguji_1', '0');
                    $this->db->or_where('spv.status_persetujuan_penguji_2', '0');
                    $this->db->group_end();
                    break;
                case 'dijadwalkan':
                    $this->db->where('spv.status_final', 'approved');
                    break;
                case 'selesai':
                    $this->db->where('spv.status_hasil', 'published');
                    break;
            }
        }
        
        if ($bulan_filter) {
            $this->db->where('DATE_FORMAT(spv.created_at, "%Y-%m")', $bulan_filter);
        }
        
        if ($dosen_filter) {
            $this->db->where('spv.dosen_id', $dosen_filter);
        }
        
        $this->db->order_by('spv.created_at', 'DESC');
        
        $data['seminar_list'] = $this->db->get()->result();
        
        // Data untuk filter
        $this->db->select('id, nama');
        $this->db->from('dosen');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->where('level', '2');
        $this->db->order_by('nama', 'ASC');
        
        $data['dosen_list'] = $this->db->get()->result();
        
        $data['filters'] = [
            'status' => $status_filter,
            'bulan' => $bulan_filter,
            'dosen' => $dosen_filter
        ];
        
        $data['title'] = 'Monitoring Seminar Proposal';
        
        $this->load->view('kaprodi/seminar_proposal/monitoring', $data);
    }
    
    /**
     * Export laporan seminar proposal
     */
    public function export_laporan() {
        $format = $this->input->get('format') ?: 'excel';
        $periode_start = $this->input->get('periode_start') ?: date('Y-m-01');
        $periode_end = $this->input->get('periode_end') ?: date('Y-m-t');
        
        // Query data
        $this->db->select('spv.*');
        $this->db->from('seminar_proposal_view spv');
        $this->db->where('spv.prodi_id', $this->prodi_id);
        $this->db->where('DATE(spv.created_at) >=', $periode_start);
        $this->db->where('DATE(spv.created_at) <=', $periode_end);
        $this->db->order_by('spv.created_at', 'ASC');
        
        $seminar_data = $this->db->get()->result();
        
        if ($format == 'excel') {
            $this->_export_excel_laporan($seminar_data, $periode_start, $periode_end);
        } else {
            $this->_export_pdf_laporan($seminar_data, $periode_start, $periode_end);
        }
    }

    /**
     * ========================================
     * AJAX METHODS
     * ========================================
     */
    
    /**
     * Get daftar seminar untuk DataTables
     */
    public function get_seminar_datatable() {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search')['value'];
        
        // Base query
        $this->db->select('spv.*');
        $this->db->from('seminar_proposal_view spv');
        $this->db->where('spv.prodi_id', $this->prodi_id);
        
        // Search
        if ($search) {
            $this->db->group_start();
            $this->db->like('spv.nim', $search);
            $this->db->or_like('spv.nama_mahasiswa', $search);
            $this->db->or_like('spv.judul_proposal', $search);
            $this->db->group_end();
        }
        
        // Total records before pagination
        $total_records = $this->db->count_all_results('', FALSE);
        
        // Pagination
        $this->db->limit($length, $start);
        $this->db->order_by('spv.created_at', 'DESC');
        
        $seminar_data = $this->db->get()->result();
        
        // Format data
        $formatted_data = [];
        foreach ($seminar_data as $seminar) {
            $formatted_data[] = [
                'nim' => $seminar->nim,
                'nama_mahasiswa' => $seminar->nama_mahasiswa,
                'judul_proposal' => substr($seminar->judul_proposal, 0, 50) . '...',
                'nama_pembimbing' => $seminar->nama_pembimbing,
                'tanggal_pengajuan' => date('d/m/Y', strtotime($seminar->created_at)),
                'status' => $this->_format_status_badge($seminar),
                'action' => $this->_generate_action_buttons_kaprodi($seminar)
            ];
        }
        
        echo json_encode([
            'draw' => (int) $draw,
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_records,
            'data' => $formatted_data
        ]);
    }
    
    /**
     * Cek konflik jadwal via AJAX
     */
    public function cek_konflik_jadwal() {
        $tanggal = $this->input->post('tanggal');
        $waktu_mulai = $this->input->post('waktu_mulai');
        $waktu_selesai = $this->input->post('waktu_selesai');
        $tempat = $this->input->post('tempat');
        $exclude_id = $this->input->post('exclude_id'); // Untuk edit
        
        // Cek konflik jadwal di tempat yang sama
        $this->db->select('spv.nama_mahasiswa, spv.waktu_mulai, spv.waktu_selesai');
        $this->db->from('seminar_proposal_view spv');
        $this->db->where('spv.tanggal_seminar', $tanggal);
        $this->db->where('spv.tempat_seminar', $tempat);
        $this->db->where('spv.status_final', 'approved');
        
        if ($exclude_id) {
            $this->db->where('spv.id !=', $exclude_id);
        }
        
        // Cek overlap waktu
        $this->db->group_start();
        $this->db->where("TIME('$waktu_mulai') < spv.waktu_selesai", NULL, FALSE);
        $this->db->where("TIME('$waktu_selesai') > spv.waktu_mulai", NULL, FALSE);
        $this->db->group_end();
        
        $konflik = $this->db->get()->result();
        
        echo json_encode([
            'ada_konflik' => !empty($konflik),
            'konflik_data' => $konflik
        ]);
    }

    /**
     * ========================================
     * HELPER METHODS
     * ========================================
     */
    
    /**
     * Get statistik untuk kaprodi
     */
    private function _get_kaprodi_stats() {
        $stats = [];
        
        // Total seminar proposal prodi
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where('prodi_id', $this->prodi_id);
        $stats['total_seminar'] = $this->db->get()->row()->total;
        
        // Menunggu validasi
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->where('rekomendasi_pembimbing', '1');
        $this->db->where('status_validasi_kaprodi', '0');
        $stats['menunggu_validasi'] = $this->db->get()->row()->total;
        
        // Menunggu persetujuan penguji
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->where('status_validasi_kaprodi', '1');
        $this->db->group_start();
        $this->db->where('status_persetujuan_penguji_1', '0');
        $this->db->or_where('status_persetujuan_penguji_2', '0');
        $this->db->group_end();
        $stats['menunggu_persetujuan'] = $this->db->get()->row()->total;
        
        // Sudah dijadwalkan
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->where('status_final', 'approved');
        $stats['sudah_dijadwalkan'] = $this->db->get()->row()->total;
        
        // Sudah selesai
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where('prodi_id', $this->prodi_id);
        $this->db->where('status_hasil', 'published');
        $stats['sudah_selesai'] = $this->db->get()->row()->total;
        
        return $stats;
    }
    
    /**
     * Get notifikasi terbaru untuk kaprodi
     */
    private function _get_notifikasi_terbaru($limit = 5) {
        $this->db->select('*');
        $this->db->from('notifikasi_seminar_proposal');
        $this->db->where('user_id', $this->session->userdata('id'));
        $this->db->where('untuk_role', 'kaprodi');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
    
    /**
     * Format status badge untuk display
     */
    private function _format_status_badge($seminar) {
        $status = Seminar_proposal_helper::format_status_seminar($seminar);
        
        $badge_class = 'secondary';
        switch ($status['status']) {
            case 'menunggu_validasi': $badge_class = 'warning'; break;
            case 'menunggu_persetujuan': $badge_class = 'info'; break;
            case 'dijadwalkan': $badge_class = 'success'; break;
            case 'selesai': $badge_class = 'primary'; break;
            case 'ditolak': $badge_class = 'danger'; break;
        }
        
        return "<span class='badge badge-{$badge_class}'>{$status['text']}</span>";
    }
    
    /**
     * Generate action buttons untuk kaprodi
     */
    private function _generate_action_buttons_kaprodi($seminar) {
        $buttons = [];
        
        $buttons[] = '<a href="' . base_url('kaprodi/seminar_proposal/detail/' . $seminar->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
        
        if ($seminar->rekomendasi_pembimbing == '1' && $seminar->status_validasi_kaprodi == '0') {
            $buttons[] = '<button onclick="prosesValidasi(' . $seminar->id . ')" class="btn btn-sm btn-success" title="Validasi"><i class="fas fa-check"></i></button>';
        }
        
        if ($seminar->status_persetujuan_penguji_1 == '2' || $seminar->status_persetujuan_penguji_2 == '2') {
            $buttons[] = '<a href="' . base_url('kaprodi/seminar_proposal/penjadwalan_ulang/' . $seminar->id) . '" class="btn btn-sm btn-warning" title="Jadwal Ulang"><i class="fas fa-calendar-alt"></i></a>';
        }
        
        return implode(' ', $buttons);
    }
    
    /**
     * Kirim notifikasi reschedule
     */
    private function _kirim_notifikasi_reschedule($seminar_id, $tipe_perubahan) {
        // Implementation untuk kirim notifikasi
        // Akan dikembangkan di Chat Session berikutnya
    }
    
    /**
     * Log aktivitas kaprodi
     */
    private function _log_aktivitas($seminar_id, $jenis, $deskripsi, $data = null) {
        if (!$seminar_id) return;
        
        $log_data = [
            'seminar_proposal_id' => $seminar_id,
            'jenis_aktivitas' => $jenis,
            'deskripsi' => $deskripsi,
            'dilakukan_oleh' => $this->session->userdata('id'),
            'role_pelaku' => 'kaprodi',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'data_perubahan' => $data ? json_encode($data) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('log_aktivitas_seminar_proposal', $log_data);
    }
    
    /**
     * Export Excel laporan
     */
    private function _export_excel_laporan($data, $start, $end) {
        // Implementation untuk export Excel
        // Akan dikembangkan di Chat Session berikutnya
    }
    
    /**
     * Export PDF laporan
     */
    private function _export_pdf_laporan($data, $start, $end) {
        // Implementation untuk export PDF
        // Akan dikembangkan di Chat Session berikutnya
    }
}