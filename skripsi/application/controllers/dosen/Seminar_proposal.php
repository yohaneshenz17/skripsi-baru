<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Dosen - Seminar Proposal
 * Mengelola rekomendasi seminar proposal oleh dosen pembimbing
 * Dan persetujuan jadwal oleh dosen penguji
 * 
 * @property Seminar_proposal_model $seminar_model
 * @property CI_Email $email
 */
class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['session', 'form_validation', 'email']);
        $this->load->helper(['url', 'form', 'date']);
        $this->load->model('Seminar_proposal_model', 'seminar_model');
        
        // Cek login dan level dosen
        if (!$this->session->userdata('logged_in') || !in_array($this->session->userdata('level'), ['2', '4'])) {
            redirect('auth/login');
        }
        
        // Load helper classes
        $this->load->library('Seminar_proposal_validation', 'validation');
        $this->load->library('Seminar_proposal_helper', 'helper');
    }

    /**
     * ========================================
     * HALAMAN UTAMA DOSEN SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Dashboard seminar proposal untuk dosen
     */
    public function index() {
        $dosen_id = $this->session->userdata('id');
        $dosen_level = $this->session->userdata('level');
        
        $data['title'] = 'Seminar Proposal - Rekomendasi & Penilaian';
        $data['dosen_name'] = $this->session->userdata('nama');
        
        // Daftar seminar proposal sebagai pembimbing (perlu rekomendasi)
        $data['seminar_pembimbing'] = $this->seminar_model->get_seminar_untuk_pembimbing($dosen_id);
        
        // Daftar seminar proposal sebagai penguji (perlu persetujuan jadwal)
        $data['seminar_penguji'] = $this->seminar_model->get_seminar_untuk_penguji($dosen_id);
        
        // Statistik untuk dosen
        $data['stats'] = $this->_get_dosen_stats($dosen_id);
        
        // Notifikasi terbaru
        $data['notifikasi_terbaru'] = $this->_get_notifikasi_terbaru($dosen_id, 5);
        
        $this->load->view('dosen/seminar_proposal/index', $data);
    }

    /**
     * ========================================
     * REKOMENDASI DOSEN PEMBIMBING
     * ========================================
     */
    
    /**
     * Halaman detail seminar proposal untuk rekomendasi pembimbing
     */
    public function detail($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $dosen_id = $this->session->userdata('id');
        
        // Ambil data seminar lengkap
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar) {
            show_404();
        }
        
        // Validasi akses: dosen harus pembimbing atau penguji
        $has_access = ($seminar->dosen_id == $dosen_id || 
                      $seminar->dosen_penguji_1_id == $dosen_id || 
                      $seminar->dosen_penguji_2_id == $dosen_id);
        
        if (!$has_access) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses ke seminar proposal ini!');
            redirect('dosen/seminar_proposal');
        }
        
        // Tentukan role dosen untuk seminar ini
        $data['dosen_role'] = 'pembimbing'; // default
        if ($seminar->dosen_penguji_1_id == $dosen_id) {
            $data['dosen_role'] = 'penguji_1';
        } elseif ($seminar->dosen_penguji_2_id == $dosen_id) {
            $data['dosen_role'] = 'penguji_2';
        }
        
        // Ambil jurnal bimbingan mahasiswa
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $seminar->proposal_id);
        $this->db->where('status_validasi', '1'); // Hanya yang sudah divalidasi
        $this->db->order_by('pertemuan_ke', 'ASC');
        
        $data['jurnal_bimbingan'] = $this->db->get()->result();
        
        // Timeline aktivitas
        $this->db->select('las.*, d.nama as pelaku_nama');
        $this->db->from('log_aktivitas_seminar_proposal las');
        $this->db->join('dosen d', 'las.dilakukan_oleh = d.id', 'left');
        $this->db->where('las.seminar_proposal_id', $seminar_id);
        $this->db->order_by('las.created_at', 'DESC');
        $this->db->limit(10);
        
        $data['timeline'] = $this->db->get()->result();
        
        $data['seminar'] = $seminar;
        $data['title'] = 'Detail Seminar Proposal - ' . $seminar->nama_mahasiswa;
        
        $this->load->view('dosen/seminar_proposal/detail', $data);
    }
    
    /**
     * Proses rekomendasi pembimbing
     */
    public function proses_rekomendasi() {
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan!']);
            return;
        }
        
        // Set validation rules
        $this->form_validation->set_rules($this->validation->rules_rekomendasi_pembimbing());
        
        if (!$this->form_validation->run()) {
            echo json_encode([
                'error' => true, 
                'message' => 'Validasi gagal!',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        $data = [
            'seminar_id' => $this->input->post('seminar_id'),
            'rekomendasi' => $this->input->post('rekomendasi'),
            'catatan_pembimbing' => $this->input->post('catatan_pembimbing'),
            'dosen_id' => $this->session->userdata('id')
        ];
        
        $result = $this->seminar_model->proses_rekomendasi_pembimbing($data);
        
        // Log aktivitas
        if (!$result['error']) {
            $this->_log_aktivitas($data['seminar_id'], 'rekomendasi', 
                                 'Dosen pembimbing memberikan rekomendasi: ' . 
                                 ($data['rekomendasi'] == '1' ? 'Disetujui' : 'Ditolak'), $data);
        }
        
        echo json_encode($result);
    }

    /**
     * ========================================
     * PERSETUJUAN DOSEN PENGUJI
     * ========================================
     */
    
    /**
     * Halaman persetujuan jadwal untuk dosen penguji
     */
    public function persetujuan_jadwal($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $dosen_id = $this->session->userdata('id');
        
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar) {
            show_404();
        }
        
        // Validasi: dosen harus penguji dan seminar sudah divalidasi kaprodi
        $is_penguji = ($seminar->dosen_penguji_1_id == $dosen_id || $seminar->dosen_penguji_2_id == $dosen_id);
        
        if (!$is_penguji || $seminar->status_validasi_kaprodi != '1') {
            $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk memberikan persetujuan jadwal!');
            redirect('dosen/seminar_proposal');
        }
        
        // Tentukan posisi penguji
        $penguji_ke = ($seminar->dosen_penguji_1_id == $dosen_id) ? 1 : 2;
        $status_field = "status_persetujuan_penguji_{$penguji_ke}";
        $catatan_field = "catatan_penguji_{$penguji_ke}";
        
        $data['seminar'] = $seminar;
        $data['penguji_ke'] = $penguji_ke;
        $data['current_status'] = $seminar->$status_field;
        $data['current_catatan'] = $seminar->$catatan_field;
        $data['title'] = 'Persetujuan Jadwal Seminar Proposal';
        
        // Status penguji lain
        $penguji_lain = ($penguji_ke == 1) ? 2 : 1;
        $status_lain_field = "status_persetujuan_penguji_{$penguji_lain}";
        $data['status_penguji_lain'] = $seminar->$status_lain_field;
        
        $this->load->view('dosen/seminar_proposal/persetujuan_jadwal', $data);
    }
    
    /**
     * Proses persetujuan jadwal penguji
     */
    public function proses_persetujuan_jadwal() {
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan!']);
            return;
        }
        
        // Set validation rules
        $this->form_validation->set_rules($this->validation->rules_persetujuan_penguji());
        
        if (!$this->form_validation->run()) {
            echo json_encode([
                'error' => true, 
                'message' => 'Validasi gagal!',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        $data = [
            'seminar_id' => $this->input->post('seminar_id'),
            'penguji_ke' => $this->input->post('penguji_ke'),
            'persetujuan' => $this->input->post('persetujuan'),
            'catatan' => $this->input->post('catatan'),
            'dosen_id' => $this->session->userdata('id')
        ];
        
        $result = $this->seminar_model->proses_persetujuan_penguji($data);
        
        // Log aktivitas
        if (!$result['error']) {
            $status_text = ($data['persetujuan'] == '1') ? 'menyetujui' : 'menolak';
            $this->_log_aktivitas($data['seminar_id'], 'persetujuan', 
                                 "Dosen penguji {$data['penguji_ke']} {$status_text} jadwal seminar", $data);
        }
        
        echo json_encode($result);
    }

    /**
     * ========================================
     * INPUT HASIL SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Halaman input hasil seminar proposal (untuk pembimbing)
     */
    public function input_hasil($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $dosen_id = $this->session->userdata('id');
        
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar) {
            show_404();
        }
        
        // Validasi: hanya pembimbing yang bisa input hasil
        if ($seminar->dosen_id != $dosen_id) {
            $this->session->set_flashdata('error', 'Hanya dosen pembimbing yang dapat menginput hasil seminar!');
            redirect('dosen/seminar_proposal');
        }
        
        // Cek apakah seminar sudah dilaksanakan
        if ($seminar->tanggal_seminar > date('Y-m-d') || $seminar->status_final != 'approved') {
            $this->session->set_flashdata('warning', 'Seminar belum dilaksanakan atau belum disetujui semua penguji!');
            redirect('dosen/seminar_proposal/detail/' . $seminar_id);
        }
        
        // Ambil hasil yang sudah ada (jika ada)
        $this->db->select('*');
        $this->db->from('hasil_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        
        $existing_hasil = $this->db->get()->row();
        
        $data['seminar'] = $seminar;
        $data['existing_hasil'] = $existing_hasil;
        $data['title'] = 'Input Hasil Seminar Proposal';
        $data['is_edit'] = (bool) $existing_hasil;
        
        // Opsi rekomendasi penguji
        $data['rekomendasi_options'] = Seminar_proposal_config::get_status_options()['rekomendasi_penguji'];
        
        $this->load->view('dosen/seminar_proposal/input_hasil', $data);
    }
    
    /**
     * Proses input hasil seminar proposal
     */
    public function proses_input_hasil() {
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan!']);
            return;
        }
        
        // Set validation rules
        $this->form_validation->set_rules($this->validation->rules_hasil_seminar());
        
        if (!$this->form_validation->run()) {
            echo json_encode([
                'error' => true, 
                'message' => 'Validasi gagal!',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }
        
        // Prepare data hasil seminar
        $data = [
            'seminar_id' => $this->input->post('seminar_id'),
            'proposal_id' => $this->input->post('proposal_id'),
            'input_oleh' => $this->session->userdata('id'),
            'input_role' => 'dosen',
            'status_input' => $this->input->post('status_input'),
            
            // Nilai pembimbing
            'nilai_pembimbing_penyajian' => $this->input->post('nilai_pembimbing_penyajian'),
            'nilai_pembimbing_materi' => $this->input->post('nilai_pembimbing_materi'),
            'nilai_pembimbing_metodologi' => $this->input->post('nilai_pembimbing_metodologi'),
            'nilai_pembimbing_total' => $this->input->post('nilai_pembimbing_total'),
            'catatan_revisi_pembimbing' => $this->input->post('catatan_revisi_pembimbing'),
            
            // Nilai penguji 1
            'nilai_penguji1_penyajian' => $this->input->post('nilai_penguji1_penyajian'),
            'nilai_penguji1_materi' => $this->input->post('nilai_penguji1_materi'),
            'nilai_penguji1_metodologi' => $this->input->post('nilai_penguji1_metodologi'),
            'nilai_penguji1_total' => $this->input->post('nilai_penguji1_total'),
            'catatan_revisi_penguji1' => $this->input->post('catatan_revisi_penguji1'),
            
            // Nilai penguji 2
            'nilai_penguji2_penyajian' => $this->input->post('nilai_penguji2_penyajian'),
            'nilai_penguji2_materi' => $this->input->post('nilai_penguji2_materi'),
            'nilai_penguji2_metodologi' => $this->input->post('nilai_penguji2_metodologi'),
            'nilai_penguji2_total' => $this->input->post('nilai_penguji2_total'),
            'catatan_revisi_penguji2' => $this->input->post('catatan_revisi_penguji2'),
            
            // Rekomendasi
            'rekomendasi_penguji' => $this->input->post('rekomendasi_penguji'),
            'catatan_rekomendasi' => $this->input->post('catatan_rekomendasi')
        ];
        
        $result = $this->seminar_model->input_hasil_seminar($data);
        
        // Log aktivitas
        if (!$result['error']) {
            $status_text = ($data['status_input'] == 'draft') ? 'menyimpan draft' : 'mempublikasi';
            $this->_log_aktivitas($data['seminar_id'], 'input_hasil', 
                                 "Dosen pembimbing {$status_text} hasil seminar", $data);
        }
        
        echo json_encode($result);
    }

    /**
     * ========================================
     * EXPORT & DOWNLOAD
     * ========================================
     */
    
    /**
     * Download file proposal seminar
     */
    public function download_proposal($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $dosen_id = $this->session->userdata('id');
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar) {
            show_404();
        }
        
        // Validasi akses
        $has_access = ($seminar->dosen_id == $dosen_id || 
                      $seminar->dosen_penguji_1_id == $dosen_id || 
                      $seminar->dosen_penguji_2_id == $dosen_id);
        
        if (!$has_access) {
            show_404();
        }
        
        $file_path = FCPATH . 'uploads/seminar_proposal/' . $seminar->file_proposal_seminar;
        
        if (!file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File tidak ditemukan!');
            redirect('dosen/seminar_proposal/detail/' . $seminar_id);
        }
        
        // Log download
        $this->_log_aktivitas($seminar_id, 'download', 'Dosen mendownload file proposal');
        
        $this->load->helper('download');
        force_download($seminar->file_proposal_seminar, file_get_contents($file_path));
    }
    
    /**
     * Export jurnal bimbingan mahasiswa
     */
    public function export_jurnal_bimbingan($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $dosen_id = $this->session->userdata('id');
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar || $seminar->dosen_id != $dosen_id) {
            show_404();
        }
        
        // Generate Excel jurnal bimbingan
        $this->load->library('excel');
        
        // Ambil data jurnal
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $seminar->proposal_id);
        $this->db->where('status_validasi', '1');
        $this->db->order_by('pertemuan_ke', 'ASC');
        
        $jurnal_data = $this->db->get()->result();
        
        // Create Excel content
        $excel_data = $this->_generate_jurnal_excel($seminar, $jurnal_data);
        
        // Log aktivitas
        $this->_log_aktivitas($seminar_id, 'download', 'Dosen export jurnal bimbingan');
        
        // Output Excel file
        $filename = "Jurnal_Bimbingan_{$seminar->nim}_{$seminar->nama_mahasiswa}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        echo $excel_data;
    }

    /**
     * ========================================
     * AJAX METHODS
     * ========================================
     */
    
    /**
     * Get daftar seminar proposal untuk DataTables
     */
    public function get_seminar_list() {
        $dosen_id = $this->session->userdata('id');
        $role_filter = $this->input->post('role_filter'); // 'pembimbing' atau 'penguji'
        
        if ($role_filter == 'pembimbing') {
            $seminar_list = $this->seminar_model->get_seminar_untuk_pembimbing($dosen_id);
        } else {
            $seminar_list = $this->seminar_model->get_seminar_untuk_penguji($dosen_id);
        }
        
        // Format data untuk DataTables
        $formatted_data = [];
        foreach ($seminar_list as $seminar) {
            $formatted_data[] = [
                'nim' => $seminar->nim,
                'nama_mahasiswa' => $seminar->nama_mahasiswa,
                'judul_proposal' => $seminar->judul_proposal,
                'tanggal_pengajuan' => date('d/m/Y', strtotime($seminar->created_at)),
                'status' => Seminar_proposal_helper::format_status_seminar($seminar),
                'action' => $this->_generate_action_buttons($seminar, $role_filter)
            ];
        }
        
        echo json_encode([
            'data' => $formatted_data
        ]);
    }
    
    /**
     * Validasi nilai seminar via AJAX
     */
    public function validasi_nilai() {
        $nilai_array = $this->input->post('nilai');
        $errors = [];
        
        foreach ($nilai_array as $field => $nilai) {
            if (!empty($nilai)) {
                if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
                    $errors[$field] = 'Nilai harus berupa angka antara 0-100';
                }
            }
        }
        
        echo json_encode([
            'valid' => empty($errors),
            'errors' => $errors
        ]);
    }

    /**
     * ========================================
     * HELPER METHODS
     * ========================================
     */
    
    /**
     * Get statistik untuk dosen
     */
    private function _get_dosen_stats($dosen_id) {
        $stats = [];
        
        // Total mahasiswa bimbingan yang mengajukan seminar proposal
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal sp');
        $this->db->join('proposal_mahasiswa pm', 'sp.proposal_id = pm.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $stats['total_bimbingan'] = $this->db->get()->row()->total;
        
        // Menunggu rekomendasi
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal sp');
        $this->db->join('proposal_mahasiswa pm', 'sp.proposal_id = pm.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('sp.rekomendasi_pembimbing', '0');
        $stats['menunggu_rekomendasi'] = $this->db->get()->row()->total;
        
        // Total sebagai penguji
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal');
        $this->db->group_start();
        $this->db->where('dosen_penguji_1_id', $dosen_id);
        $this->db->or_where('dosen_penguji_2_id', $dosen_id);
        $this->db->group_end();
        $stats['total_penguji'] = $this->db->get()->row()->total;
        
        // Menunggu persetujuan jadwal
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal');
        $this->db->where('status_validasi_kaprodi', '1');
        $this->db->group_start();
        $this->db->group_start();
        $this->db->where('dosen_penguji_1_id', $dosen_id);
        $this->db->where('status_persetujuan_penguji_1', '0');
        $this->db->group_end();
        $this->db->or_group_start();
        $this->db->where('dosen_penguji_2_id', $dosen_id);
        $this->db->where('status_persetujuan_penguji_2', '0');
        $this->db->group_end();
        $this->db->group_end();
        $stats['menunggu_persetujuan'] = $this->db->get()->row()->total;
        
        return $stats;
    }
    
    /**
     * Get notifikasi terbaru untuk dosen
     */
    private function _get_notifikasi_terbaru($dosen_id, $limit = 5) {
        $this->db->select('*');
        $this->db->from('notifikasi_seminar_proposal');
        $this->db->where('user_id', $dosen_id);
        $this->db->where('untuk_role', 'dosen');
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
    
    /**
     * Generate action buttons untuk DataTables
     */
    private function _generate_action_buttons($seminar, $role) {
        $buttons = [];
        
        $buttons[] = '<a href="' . base_url('dosen/seminar_proposal/detail/' . $seminar->id) . '" class="btn btn-sm btn-info" title="Lihat Detail"><i class="fas fa-eye"></i></a>';
        
        if ($role == 'pembimbing' && $seminar->rekomendasi_pembimbing == '0') {
            $buttons[] = '<button onclick="prosesRekomendasi(' . $seminar->id . ')" class="btn btn-sm btn-success" title="Berikan Rekomendasi"><i class="fas fa-check"></i></button>';
        }
        
        if ($role == 'penguji' && $seminar->status_validasi_kaprodi == '1') {
            $penguji_ke = ($seminar->dosen_penguji_1_id == $this->session->userdata('id')) ? 1 : 2;
            $status_field = "status_persetujuan_penguji_{$penguji_ke}";
            
            if ($seminar->$status_field == '0') {
                $buttons[] = '<a href="' . base_url('dosen/seminar_proposal/persetujuan_jadwal/' . $seminar->id) . '" class="btn btn-sm btn-warning" title="Persetujuan Jadwal"><i class="fas fa-calendar-check"></i></a>';
            }
        }
        
        return implode(' ', $buttons);
    }
    
    /**
     * Log aktivitas dosen
     */
    private function _log_aktivitas($seminar_id, $jenis, $deskripsi, $data = null) {
        if (!$seminar_id) return;
        
        $log_data = [
            'seminar_proposal_id' => $seminar_id,
            'jenis_aktivitas' => $jenis,
            'deskripsi' => $deskripsi,
            'dilakukan_oleh' => $this->session->userdata('id'),
            'role_pelaku' => 'dosen',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'data_perubahan' => $data ? json_encode($data) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('log_aktivitas_seminar_proposal', $log_data);
    }
    
    /**
     * Generate Excel content untuk jurnal bimbingan
     */
    private function _generate_jurnal_excel($seminar, $jurnal_data) {
        // Implementasi PHPSpreadsheet atau library Excel lainnya
        // Untuk sementara return simple CSV format
        
        $csv_content = "JURNAL BIMBINGAN SEMINAR PROPOSAL\n";
        $csv_content .= "Nama Mahasiswa: {$seminar->nama_mahasiswa}\n";
        $csv_content .= "NIM: {$seminar->nim}\n";
        $csv_content .= "Judul: {$seminar->judul_proposal}\n";
        $csv_content .= "Dosen Pembimbing: {$seminar->nama_pembimbing}\n\n";
        
        $csv_content .= "Pertemuan,Tanggal,Materi Bimbingan,Catatan Dosen,Tindak Lanjut\n";
        
        foreach ($jurnal_data as $jurnal) {
            $csv_content .= "\"{$jurnal->pertemuan_ke}\",";
            $csv_content .= "\"{$jurnal->tanggal_bimbingan}\",";
            $csv_content .= "\"" . str_replace('"', '""', $jurnal->materi_bimbingan) . "\",";
            $csv_content .= "\"" . str_replace('"', '""', $jurnal->catatan_dosen ?? '') . "\",";
            $csv_content .= "\"" . str_replace('"', '""', $jurnal->tindak_lanjut ?? '') . "\"\n";
        }
        
        return $csv_content;
    }
}