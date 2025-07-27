<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Seminar Proposal
 * Mengelola administrasi, dokumentasi, dan export dokumen seminar proposal
 * 
 * @property Seminar_proposal_model $seminar_model
 * @property CI_Pdf $pdf
 * @property CI_Excel $excel
 */
class Seminar_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library(['session', 'form_validation', 'upload']);
        $this->load->helper(['url', 'form', 'file', 'download', 'date']);
        $this->load->model('Seminar_proposal_model', 'seminar_model');
        
        // Load PDF dan Excel libraries jika tersedia
        if (file_exists(APPPATH . 'libraries/Pdf.php')) {
            $this->load->library('pdf');
        }
        
        // Cek login dan level staf
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
        
        // Load helper classes
        $this->load->library('Seminar_proposal_helper', 'helper');
    }

    /**
     * ========================================
     * HALAMAN UTAMA STAF SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Dashboard seminar proposal untuk staf
     */
    public function index() {
        // Filter
        $prodi_id = $this->input->get('prodi_id');
        $status = $this->input->get('status');
        $periode = $this->input->get('periode') ?: date('Y-m');
        $search = $this->input->get('search');
        
        // Base query
        $this->db->select('spv.*');
        $this->db->from('seminar_proposal_view spv');
        
        // Filter hanya yang sudah tahap seminar proposal atau lebih
        $this->db->where_in('spv.workflow_status', ['seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        
        // Apply filters
        if ($prodi_id) {
            $this->db->where('spv.prodi_id', $prodi_id);
        }
        
        if ($status !== '') {
            switch ($status) {
                case 'dijadwalkan':
                    $this->db->where('spv.status_final', 'approved');
                    $this->db->where('spv.tanggal_seminar >=', date('Y-m-d'));
                    break;
                case 'selesai_belum_input':
                    $this->db->where('spv.status_final', 'approved');
                    $this->db->where('spv.tanggal_seminar <', date('Y-m-d'));
                    $this->db->where('(spv.status_hasil IS NULL OR spv.status_hasil = "draft")');
                    break;
                case 'hasil_published':
                    $this->db->where('spv.status_hasil', 'published');
                    break;
                case 'menunggu_jadwal':
                    $this->db->where('spv.status_validasi_kaprodi', '1');
                    $this->db->group_start();
                    $this->db->where('spv.status_persetujuan_penguji_1', '0');
                    $this->db->or_where('spv.status_persetujuan_penguji_2', '0');
                    $this->db->group_end();
                    break;
            }
        }
        
        if ($periode) {
            $this->db->where('DATE_FORMAT(spv.tanggal_seminar, "%Y-%m")', $periode);
        }
        
        if ($search) {
            $this->db->group_start();
            $this->db->like('spv.nama_mahasiswa', $search);
            $this->db->or_like('spv.nim', $search);
            $this->db->or_like('spv.judul_proposal', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('spv.tanggal_seminar', 'ASC');
        
        $data['seminar_proposal'] = $this->db->get()->result();
        
        // Data untuk filter
        $data['prodi_list'] = $this->db->get('prodi')->result();
        
        $data['filters'] = [
            'prodi_id' => $prodi_id,
            'status' => $status,
            'periode' => $periode,
            'search' => $search
        ];
        
        // Statistik untuk dashboard
        $data['stats'] = $this->_get_staf_stats();
        
        // Jadwal seminar minggu ini
        $data['jadwal_minggu_ini'] = $this->_get_jadwal_minggu_ini();
        
        $data['title'] = 'Administrasi Seminar Proposal';
        $data['staf_name'] = $this->session->userdata('nama');
        
        $this->load->view('staf/seminar_proposal/index', $data);
    }

    /**
     * ========================================
     * DETAIL SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Detail seminar proposal untuk staf
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
        
        // Ambil hasil seminar jika ada
        $this->db->select('*');
        $this->db->from('hasil_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        
        $hasil_seminar = $this->db->get()->row();
        
        // Ambil dokumen yang sudah dibuat
        $this->db->select('*');
        $this->db->from('dokumen_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        $this->db->order_by('generated_at', 'DESC');
        
        $dokumen_list = $this->db->get()->result();
        
        // Timeline aktivitas
        $this->db->select('las.*, d.nama as pelaku_nama');
        $this->db->from('log_aktivitas_seminar_proposal las');
        $this->db->join('dosen d', 'las.dilakukan_oleh = d.id', 'left');
        $this->db->where('las.seminar_proposal_id', $seminar_id);
        $this->db->order_by('las.created_at', 'DESC');
        $this->db->limit(15);
        
        $timeline = $this->db->get()->result();
        
        $data['seminar'] = $seminar;
        $data['hasil_seminar'] = $hasil_seminar;
        $data['dokumen_list'] = $dokumen_list;
        $data['timeline'] = $timeline;
        $data['title'] = 'Detail Administrasi - ' . $seminar->nama_mahasiswa;
        
        // Status dan actions yang bisa dilakukan staf
        $data['available_actions'] = $this->_get_available_actions($seminar, $hasil_seminar);
        
        $this->load->view('staf/seminar_proposal/detail', $data);
    }

    /**
     * ========================================
     * EXPORT DOKUMEN SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Export undangan seminar proposal
     */
    public function export_undangan($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar || $seminar->status_final != 'approved') {
            $this->session->set_flashdata('error', 'Seminar belum dikonfirmasi atau tidak ditemukan!');
            redirect('staf/seminar_proposal');
        }
        
        // Generate nomor undangan
        $nomor_undangan = $this->_generate_nomor_undangan($seminar);
        
        // Prepare data untuk template
        $template_data = [
            'seminar' => $seminar,
            'nomor_undangan' => $nomor_undangan,
            'tanggal_surat' => date('d/m/Y'),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ];
        
        // Generate PDF
        if ($this->pdf) {
            $html = $this->load->view('staf/seminar_proposal/pdf/undangan', $template_data, true);
            
            $this->pdf->loadHtml($html);
            $this->pdf->setPaper('A4', 'portrait');
            $this->pdf->render();
            
            $filename = "Undangan_Seminar_Proposal_{$seminar->nim}_{$seminar->nama_mahasiswa}.pdf";
            
            // Simpan file ke server
            $file_content = $this->pdf->output();
            $file_path = $this->_save_document_file($filename, $file_content);
            
            // Simpan record dokumen
            $this->_save_document_record($seminar_id, 'undangan', $filename, $file_path, $nomor_undangan);
            
            // Log aktivitas
            $this->_log_aktivitas($seminar_id, 'export_undangan', 
                                 "Staf generate undangan seminar proposal dengan nomor: {$nomor_undangan}");
            
            // Download file
            $this->pdf->stream($filename, array("Attachment" => false));
        } else {
            $this->session->set_flashdata('error', 'PDF library tidak tersedia!');
            redirect('staf/seminar_proposal/detail/' . $seminar_id);
        }
    }
    
    /**
     * Export berita acara seminar proposal
     */
    public function export_berita_acara($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar) {
            show_404();
        }
        
        // Ambil hasil seminar jika ada
        $this->db->select('*');
        $this->db->from('hasil_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        
        $hasil = $this->db->get()->row();
        
        // Generate nomor berita acara
        $nomor_berita_acara = $this->_generate_nomor_berita_acara($seminar);
        
        $template_data = [
            'seminar' => $seminar,
            'hasil' => $hasil,
            'nomor_berita_acara' => $nomor_berita_acara,
            'tanggal_surat' => date('d/m/Y'),
            'generated_by' => $this->session->userdata('nama'),
            'generated_at' => date('d/m/Y H:i:s')
        ];
        
        if ($this->pdf) {
            $html = $this->load->view('staf/seminar_proposal/pdf/berita_acara', $template_data, true);
            
            $this->pdf->loadHtml($html);
            $this->pdf->setPaper('A4', 'portrait');
            $this->pdf->render();
            
            $filename = "Berita_Acara_Seminar_Proposal_{$seminar->nim}_{$seminar->nama_mahasiswa}.pdf";
            
            // Simpan dan record dokumen
            $file_content = $this->pdf->output();
            $file_path = $this->_save_document_file($filename, $file_content);
            $this->_save_document_record($seminar_id, 'berita_acara', $filename, $file_path, $nomor_berita_acara);
            
            $this->_log_aktivitas($seminar_id, 'export_berita_acara', 
                                 "Staf generate berita acara seminar proposal dengan nomor: {$nomor_berita_acara}");
            
            $this->pdf->stream($filename, array("Attachment" => false));
        } else {
            $this->session->set_flashdata('error', 'PDF library tidak tersedia!');
            redirect('staf/seminar_proposal/detail/' . $seminar_id);
        }
    }
    
    /**
     * Export form penilaian seminar proposal
     */
    public function export_form_penilaian($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar) {
            show_404();
        }
        
        $template_data = [
            'seminar' => $seminar,
            'tanggal_cetak' => date('d/m/Y'),
            'generated_by' => $this->session->userdata('nama')
        ];
        
        if ($this->pdf) {
            $html = $this->load->view('staf/seminar_proposal/pdf/form_penilaian', $template_data, true);
            
            $this->pdf->loadHtml($html);
            $this->pdf->setPaper('A4', 'portrait');
            $this->pdf->render();
            
            $filename = "Form_Penilaian_Seminar_Proposal_{$seminar->nim}_{$seminar->nama_mahasiswa}.pdf";
            
            // Simpan dan record dokumen
            $file_content = $this->pdf->output();
            $file_path = $this->_save_document_file($filename, $file_content);
            $this->_save_document_record($seminar_id, 'form_penilaian', $filename, $file_path);
            
            $this->_log_aktivitas($seminar_id, 'export_form_penilaian', 
                                 "Staf generate form penilaian seminar proposal");
            
            $this->pdf->stream($filename, array("Attachment" => false));
        } else {
            $this->session->set_flashdata('error', 'PDF library tidak tersedia!');
            redirect('staf/seminar_proposal/detail/' . $seminar_id);
        }
    }

    /**
     * ========================================
     * INPUT HASIL SEMINAR PROPOSAL (STAF)
     * ========================================
     */
    
    /**
     * Halaman input hasil seminar proposal oleh staf
     */
    public function input_hasil($seminar_id = null) {
        if (!$seminar_id) {
            show_404();
        }
        
        $seminar = $this->seminar_model->get_seminar_by_id($seminar_id);
        
        if (!$seminar) {
            show_404();
        }
        
        // Cek apakah seminar sudah dilaksanakan
        if ($seminar->tanggal_seminar > date('Y-m-d') || $seminar->status_final != 'approved') {
            $this->session->set_flashdata('warning', 'Seminar belum dilaksanakan atau belum disetujui semua penguji!');
            redirect('staf/seminar_proposal/detail/' . $seminar_id);
        }
        
        // Ambil hasil yang sudah ada (jika ada)
        $this->db->select('*');
        $this->db->from('hasil_seminar_proposal');
        $this->db->where('seminar_proposal_id', $seminar_id);
        
        $existing_hasil = $this->db->get()->row();
        
        $data['seminar'] = $seminar;
        $data['existing_hasil'] = $existing_hasil;
        $data['title'] = 'Input Hasil Seminar Proposal - ' . $seminar->nama_mahasiswa;
        $data['is_edit'] = (bool) $existing_hasil;
        
        // Opsi rekomendasi penguji
        $data['rekomendasi_options'] = Seminar_proposal_config::get_status_options()['rekomendasi_penguji'];
        
        $this->load->view('staf/seminar_proposal/input_hasil', $data);
    }
    
    /**
     * Proses input hasil seminar proposal oleh staf
     */
    public function proses_input_hasil() {
        if ($this->input->method() !== 'post') {
            echo json_encode(['error' => true, 'message' => 'Method tidak diizinkan!']);
            return;
        }
        
        // Prepare data hasil seminar
        $data = [
            'seminar_id' => $this->input->post('seminar_id'),
            'proposal_id' => $this->input->post('proposal_id'),
            'input_oleh' => $this->session->userdata('id'),
            'input_role' => 'staf',
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
                                 "Staf {$status_text} hasil seminar", $data);
        }
        
        echo json_encode($result);
    }

    /**
     * ========================================
     * EXPORT LAPORAN
     * ========================================
     */
    
    /**
     * Export laporan seminar proposal
     */
    public function export_laporan() {
        $format = $this->input->get('format') ?: 'excel';
        $periode_start = $this->input->get('periode_start') ?: date('Y-m-01');
        $periode_end = $this->input->get('periode_end') ?: date('Y-m-t');
        $prodi_id = $this->input->get('prodi_id');
        
        // Query data
        $this->db->select('spv.*');
        $this->db->from('seminar_proposal_view spv');
        $this->db->where('DATE(spv.tanggal_seminar) >=', $periode_start);
        $this->db->where('DATE(spv.tanggal_seminar) <=', $periode_end);
        
        if ($prodi_id) {
            $this->db->where('spv.prodi_id', $prodi_id);
        }
        
        $this->db->order_by('spv.tanggal_seminar', 'ASC');
        
        $seminar_data = $this->db->get()->result();
        
        if ($format == 'excel') {
            $this->_export_excel_laporan($seminar_data, $periode_start, $periode_end);
        } else {
            $this->_export_pdf_laporan($seminar_data, $periode_start, $periode_end);
        }
        
        // Log aktivitas
        $this->_log_aktivitas(null, 'export_laporan', 
                             "Staf export laporan seminar proposal format {$format} periode {$periode_start} - {$periode_end}");
    }
    
    /**
     * Export Excel laporan
     */
    private function _export_excel_laporan($data, $start, $end) {
        // Header untuk CSV (fallback jika tidak ada library Excel)
        $filename = "Laporan_Seminar_Proposal_{$start}_to_{$end}.csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM untuk Excel
        fputs($output, "\xEF\xBB\xBF");
        
        // Header CSV
        fputcsv($output, [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Judul Proposal',
            'Dosen Pembimbing',
            'Dosen Penguji 1',
            'Dosen Penguji 2',
            'Tanggal Seminar',
            'Waktu',
            'Tempat',
            'Status',
            'Nilai Rata-rata',
            'Grade',
            'Rekomendasi'
        ]);
        
        // Data rows
        $no = 1;
        foreach ($data as $seminar) {
            fputcsv($output, [
                $no++,
                $seminar->nim,
                $seminar->nama_mahasiswa,
                $seminar->nama_prodi,
                $seminar->judul_proposal,
                $seminar->nama_pembimbing,
                $seminar->nama_penguji_1 ?: '-',
                $seminar->nama_penguji_2 ?: '-',
                $seminar->tanggal_seminar ? date('d/m/Y', strtotime($seminar->tanggal_seminar)) : '-',
                $seminar->waktu_mulai ? date('H:i', strtotime($seminar->waktu_mulai)) : '-',
                $seminar->tempat_seminar ?: '-',
                Seminar_proposal_helper::format_status_seminar($seminar)['text'],
                $seminar->nilai_rata_rata ?: '-',
                $seminar->grade ?: '-',
                $seminar->rekomendasi_penguji ? Seminar_proposal_helper::format_rekomendasi_penguji($seminar->rekomendasi_penguji) : '-'
            ]);
        }
        
        fclose($output);
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
        $order = $this->input->post('order')[0];
        
        // Base query
        $this->db->select('spv.*');
        $this->db->from('seminar_proposal_view spv');
        $this->db->where_in('spv.workflow_status', ['seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        
        // Search
        if ($search) {
            $this->db->group_start();
            $this->db->like('spv.nim', $search);
            $this->db->or_like('spv.nama_mahasiswa', $search);
            $this->db->or_like('spv.judul_proposal', $search);
            $this->db->or_like('spv.nama_pembimbing', $search);
            $this->db->group_end();
        }
        
        // Count total records
        $total_records = $this->db->count_all_results('', FALSE);
        
        // Order
        $columns = ['nim', 'nama_mahasiswa', 'tanggal_seminar', 'status_final'];
        if (isset($columns[$order['column']])) {
            $this->db->order_by($columns[$order['column']], $order['dir']);
        }
        
        // Pagination
        $this->db->limit($length, $start);
        
        $seminar_data = $this->db->get()->result();
        
        // Format data
        $formatted_data = [];
        foreach ($seminar_data as $seminar) {
            $formatted_data[] = [
                'nim' => $seminar->nim,
                'nama_mahasiswa' => $seminar->nama_mahasiswa,
                'judul_proposal' => substr($seminar->judul_proposal, 0, 50) . '...',
                'tanggal_seminar' => $seminar->tanggal_seminar ? date('d/m/Y', strtotime($seminar->tanggal_seminar)) : '-',
                'status' => $this->_format_status_badge($seminar),
                'action' => $this->_generate_action_buttons($seminar)
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
     * ========================================
     * HELPER METHODS
     * ========================================
     */
    
    /**
     * Get statistik untuk staf
     */
    private function _get_staf_stats() {
        $stats = [];
        
        // Total seminar proposal
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where_in('workflow_status', ['seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai']);
        $stats['total_seminar'] = $this->db->get()->row()->total;
        
        // Dijadwalkan
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where('status_final', 'approved');
        $this->db->where('tanggal_seminar >=', date('Y-m-d'));
        $stats['dijadwalkan'] = $this->db->get()->row()->total;
        
        // Selesai belum input hasil
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where('status_final', 'approved');
        $this->db->where('tanggal_seminar <', date('Y-m-d'));
        $this->db->where('(status_hasil IS NULL OR status_hasil = "draft")');
        $stats['selesai_belum_input'] = $this->db->get()->row()->total;
        
        // Hasil sudah published
        $this->db->select('COUNT(*) as total');
        $this->db->from('seminar_proposal_view');
        $this->db->where('status_hasil', 'published');
        $stats['hasil_published'] = $this->db->get()->row()->total;
        
        return $stats;
    }
    
    /**
     * Get jadwal seminar minggu ini
     */
    private function _get_jadwal_minggu_ini() {
        $start_week = date('Y-m-d', strtotime('monday this week'));
        $end_week = date('Y-m-d', strtotime('sunday this week'));
        
        $this->db->select('spv.*');
        $this->db->from('seminar_proposal_view spv');
        $this->db->where('spv.status_final', 'approved');
        $this->db->where('spv.tanggal_seminar >=', $start_week);
        $this->db->where('spv.tanggal_seminar <=', $end_week);
        $this->db->order_by('spv.tanggal_seminar', 'ASC');
        $this->db->order_by('spv.waktu_mulai', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get actions yang tersedia untuk staf
     */
    private function _get_available_actions($seminar, $hasil_seminar) {
        $actions = [];
        
        // Export undangan (jika sudah dijadwalkan)
        if ($seminar->status_final == 'approved') {
            $actions['export_undangan'] = [
                'label' => 'Export Undangan',
                'url' => base_url('staf/seminar_proposal/export_undangan/' . $seminar->id),
                'icon' => 'fas fa-file-pdf',
                'class' => 'btn-info'
            ];
        }
        
        // Export form penilaian
        if ($seminar->status_final == 'approved') {
            $actions['export_form_penilaian'] = [
                'label' => 'Export Form Penilaian',
                'url' => base_url('staf/seminar_proposal/export_form_penilaian/' . $seminar->id),
                'icon' => 'fas fa-clipboard-list',
                'class' => 'btn-warning'
            ];
        }
        
        // Input hasil (jika sudah lewat tanggal seminar)
        if ($seminar->status_final == 'approved' && $seminar->tanggal_seminar <= date('Y-m-d')) {
            $actions['input_hasil'] = [
                'label' => 'Input Hasil',
                'url' => base_url('staf/seminar_proposal/input_hasil/' . $seminar->id),
                'icon' => 'fas fa-edit',
                'class' => 'btn-success'
            ];
        }
        
        // Export berita acara (jika sudah ada hasil)
        if ($hasil_seminar) {
            $actions['export_berita_acara'] = [
                'label' => 'Export Berita Acara',
                'url' => base_url('staf/seminar_proposal/export_berita_acara/' . $seminar->id),
                'icon' => 'fas fa-file-alt',
                'class' => 'btn-primary'
            ];
        }
        
        return $actions;
    }
    
    /**
     * Generate nomor undangan
     */
    private function _generate_nomor_undangan($seminar) {
        $tahun = date('Y');
        $bulan = date('m');
        
        // Hitung jumlah undangan bulan ini
        $this->db->where('MONTH(tanggal_seminar)', $bulan);
        $this->db->where('YEAR(tanggal_seminar)', $tahun);
        $this->db->where('status_final', 'approved');
        $count = $this->db->count_all_results('seminar_proposal') + 1;
        
        return sprintf("%03d/STK-SY/UND-SP/%s/%s", $count, $bulan, $tahun);
    }
    
    /**
     * Generate nomor berita acara
     */
    private function _generate_nomor_berita_acara($seminar) {
        $tahun = date('Y');
        $bulan = date('m');
        
        $this->db->where('MONTH(generated_at)', $bulan);
        $this->db->where('YEAR(generated_at)', $tahun);
        $this->db->where('jenis_dokumen', 'berita_acara');
        $count = $this->db->count_all_results('dokumen_seminar_proposal') + 1;
        
        return sprintf("%03d/STK-SY/BA-SP/%s/%s", $count, $bulan, $tahun);
    }
    
    /**
     * Simpan file dokumen ke server
     */
    private function _save_document_file($filename, $content) {
        $upload_path = FCPATH . 'uploads/dokumen_seminar_proposal/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $file_path = $upload_path . $filename;
        file_put_contents($file_path, $content);
        
        return $file_path;
    }
    
    /**
     * Simpan record dokumen ke database
     */
    private function _save_document_record($seminar_id, $jenis_dokumen, $nama_file, $file_path, $nomor_dokumen = null) {
        $data = [
            'seminar_proposal_id' => $seminar_id,
            'jenis_dokumen' => $jenis_dokumen,
            'nama_file' => $nama_file,
            'nama_file_sistem' => $nama_file,
            'file_path' => $file_path,
            'file_size' => file_exists($file_path) ? filesize($file_path) : 0,
            'file_mime_type' => 'application/pdf',
            'file_hash' => file_exists($file_path) ? md5_file($file_path) : null,
            'generated_by' => $this->session->userdata('id'),
            'generated_by_role' => 'staf',
            'generated_at' => date('Y-m-d H:i:s'),
            'nomor_dokumen' => $nomor_dokumen,
            'is_final' => 1,
            'is_public' => 1,
            'access_roles' => 'mahasiswa,dosen,staf,kaprodi'
        ];
        
        return $this->db->insert('dokumen_seminar_proposal', $data);
    }
    
    /**
     * Format status badge
     */
    private function _format_status_badge($seminar) {
        $status = Seminar_proposal_helper::format_status_seminar($seminar);
        
        $badge_class = 'secondary';
        switch ($status['status']) {
            case 'dijadwalkan': $badge_class = 'success'; break;
            case 'selesai': $badge_class = 'primary'; break;
            case 'menunggu_jadwal': $badge_class = 'warning'; break;
        }
        
        return "<span class='badge badge-{$badge_class}'>{$status['text']}</span>";
    }
    
    /**
     * Generate action buttons
     */
    private function _generate_action_buttons($seminar) {
        $buttons = [];
        
        $buttons[] = '<a href="' . base_url('staf/seminar_proposal/detail/' . $seminar->id) . '" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>';
        
        if ($seminar->status_final == 'approved') {
            $buttons[] = '<a href="' . base_url('staf/seminar_proposal/export_undangan/' . $seminar->id) . '" class="btn btn-sm btn-primary" title="Undangan"><i class="fas fa-file-pdf"></i></a>';
        }
        
        if ($seminar->status_final == 'approved' && $seminar->tanggal_seminar <= date('Y-m-d')) {
            $buttons[] = '<a href="' . base_url('staf/seminar_proposal/input_hasil/' . $seminar->id) . '" class="btn btn-sm btn-success" title="Input Hasil"><i class="fas fa-edit"></i></a>';
        }
        
        return implode(' ', $buttons);
    }
    
    /**
     * Log aktivitas staf
     */
    private function _log_aktivitas($seminar_id, $jenis, $deskripsi, $data = null) {
        if (!$seminar_id && $jenis != 'export_laporan') return;
        
        $log_data = [
            'seminar_proposal_id' => $seminar_id,
            'jenis_aktivitas' => $jenis,
            'deskripsi' => $deskripsi,
            'dilakukan_oleh' => $this->session->userdata('id'),
            'role_pelaku' => 'staf',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'data_perubahan' => $data ? json_encode($data) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('log_aktivitas_seminar_proposal', $log_data);
        
        // Log ke tabel staf_aktivitas juga
        $staf_log = [
            'staf_id' => $this->session->userdata('id'),
            'aktivitas' => $jenis,
            'mahasiswa_id' => null, // Akan diisi jika ada
            'proposal_id' => null,  // Akan diisi jika ada
            'keterangan' => $deskripsi,
            'tanggal_aktivitas' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('staf_aktivitas', $staf_log);
    }
}