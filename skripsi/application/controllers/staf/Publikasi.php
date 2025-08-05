// =====================================================
// 3. STAF CONTROLLER
// File: application/controllers/staf/Publikasi.php
// =====================================================

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Publikasi untuk Staf
 * Handle input repository link dan validasi final
 */
class Publikasi extends CI_Controller {

    private $staf_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk staf
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if($this->session->userdata('level') != '5') { // Level 5 = Staf
            show_error('Akses ditolak. Halaman khusus staf.', 403);
        }
        
        $this->staf_id = $this->session->userdata('user_id');
    }

    /**
     * Dashboard publikasi untuk staf
     */
    public function index() {
        $data = [
            'title' => 'Validasi Publikasi Tugas Akhir',
            'pengajuan_validasi' => $this->_get_pengajuan_perlu_validasi(),
            'riwayat_validasi' => $this->_get_riwayat_validasi(),
            'statistik' => $this->_get_statistik_staf()
        ];
        
        $this->load->view('staf/publikasi/index', $data);
    }

    /**
     * Detail dan validasi pengajuan publikasi
     */
    public function validasi($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
        }
        
        // Hanya bisa validasi yang sudah disetujui dosen
        if ($publikasi->status_pembimbing !== 'approved') {
            $this->session->set_flashdata('error', 'Pengajuan belum disetujui dosen pembimbing.');
            redirect('staf/publikasi');
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_validasi($publikasi);
        } else {
            $this->_show_validasi_form($publikasi);
        }
    }

    /**
     * Input link repository dan selesaikan publikasi
     */
    public function selesaikan($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('staf/publikasi');
        }
        
        if ($publikasi->status_pembimbing !== 'approved') {
            $this->session->set_flashdata('error', 'Pengajuan belum disetujui dosen pembimbing.');
            redirect('staf/publikasi');
        }
        
        $this->form_validation->set_rules('link_repository', 'Link Repository', 'required|valid_url|trim');
        $this->form_validation->set_rules('komentar_staf', 'Komentar', 'trim');
        
        if (!$this->form_validation->run()) {
            $this->_show_validasi_form($publikasi);
            return;
        }
        
        $data = [
            'link_repository' => $this->input->post('link_repository'),
            'komentar_staf' => $this->input->post('komentar_staf'),
            'validated_by_staf_id' => $this->staf_id,
            'validated_by_staf_name' => $this->session->userdata('nama')
        ];
        
        $result = $this->publikasi->complete_by_staf($publikasi_id, $data);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Publikasi berhasil diselesaikan. Mahasiswa dapat download surat keterangan.');
            
            // Send notification ke mahasiswa
            $this->_send_notification_to_mahasiswa($publikasi, 'completed');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('staf/publikasi');
    }

    /**
     * Download laporan publikasi
     */
    public function laporan() {
        $filter = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_selesai' => $this->input->get('tanggal_selesai'),
            'prodi_id' => $this->input->get('prodi_id'),
            'status' => $this->input->get('status')
        ];
        
        $data = [
            'title' => 'Laporan Publikasi Tugas Akhir',
            'publikasi_list' => $this->_get_laporan_publikasi($filter),
            'filter' => $filter,
            'prodi_list' => $this->_get_prodi_list()
        ];
        
        if ($this->input->get('export') === 'excel') {
            $this->_export_excel($data);
        } else {
            $this->load->view('staf/publikasi/laporan', $data);
        }
    }

    /**
     * Download file publikasi untuk validasi
     */
    public function download($publikasi_id, $file_type) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            show_404();
        }
        
        $this->_download_file($publikasi, $file_type);
    }

    // =================================================================
    // PRIVATE METHODS
    // =================================================================

    /**
     * Get pengajuan yang perlu divalidasi staf
     */
    private function _get_pengajuan_perlu_validasi() {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        $this->db->where('pta.status_pembimbing', 'approved');
        $this->db->where('pta.status_staf', 'pending');
        $this->db->where_in('pta.status', ['review_staf']);
        $this->db->order_by('pta.tanggal_review_pembimbing', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get riwayat validasi yang sudah dilakukan
     */
    private function _get_riwayat_validasi() {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa,
            pr.nama as nama_prodi
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->where('pta.validated_by_staf_id', $this->staf_id);
        $this->db->where('pta.status_staf !=', 'pending');
        $this->db->order_by('pta.tanggal_validasi_staf', 'DESC');
        $this->db->limit(15);
        
        return $this->db->get()->result();
    }

    /**
     * Get detail publikasi
     */
    private function _get_publikasi_detail($publikasi_id) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            m.nomor_telepon,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing,
            pm.judul as judul_proposal
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
        $this->db->where('pta.id', $publikasi_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get statistik untuk dashboard staf
     */
    private function _get_statistik_staf() {
        $stats = [
            'pending_validasi' => 0,
            'sudah_divalidasi' => 0,
            'publikasi_selesai' => 0,
            'total_bulan_ini' => 0
        ];
        
        // Pending validasi
        $stats['pending_validasi'] = $this->db->where('status_pembimbing', 'approved')
                                            ->where('status_staf', 'pending')
                                            ->count_all_results('publikasi_tugas_akhir');
        
        // Sudah divalidasi
        $stats['sudah_divalidasi'] = $this->db->where('validated_by_staf_id', $this->staf_id)
                                            ->where('status_staf !=', 'pending')
                                            ->count_all_results('publikasi_tugas_akhir');
        
        // Publikasi selesai
        $stats['publikasi_selesai'] = $this->db->where('status', 'completed')
                                             ->count_all_results('publikasi_tugas_akhir');
        
        // Total bulan ini
        $stats['total_bulan_ini'] = $this->db->where('MONTH(tanggal_validasi_staf)', date('n'))
                                           ->where('YEAR(tanggal_validasi_staf)', date('Y'))
                                           ->count_all_results('publikasi_tugas_akhir');
        
        return $stats;
    }

    /**
     * Process validasi from staf
     */
    private function _process_validasi($publikasi) {
        $this->form_validation->set_rules('link_repository', 'Link Repository', 'required|valid_url|trim');
        $this->form_validation->set_rules('komentar_staf', 'Komentar', 'trim');
        
        if (!$this->form_validation->run()) {
            $this->_show_validasi_form($publikasi);
            return;
        }
        
        $data = [
            'link_repository' => $this->input->post('link_repository'),
            'komentar_staf' => $this->input->post('komentar_staf'),
            'validated_by_staf_id' => $this->staf_id,
            'validated_by_staf_name' => $this->session->userdata('nama')
        ];
        
        $result = $this->publikasi->complete_by_staf($publikasi->id, $data);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'Publikasi berhasil diselesaikan.');
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('staf/publikasi');
    }

    /**
     * Show validasi form
     */
    private function _show_validasi_form($publikasi) {
        $data = [
            'title' => 'Validasi Publikasi Tugas Akhir',
            'publikasi' => $publikasi
        ];
        $this->load->view('staf/publikasi/validasi', $data);
    }

    /**
     * Get list prodi untuk filter
     */
    private function _get_prodi_list() {
        $this->db->select('id, nama');
        $this->db->from('prodi');
        $this->db->order_by('nama', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get laporan publikasi dengan filter
     */
    private function _get_laporan_publikasi($filter) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        
        if (!empty($filter['tanggal_mulai'])) {
            $this->db->where('DATE(pta.tanggal_pengajuan) >=', $filter['tanggal_mulai']);
        }
        
        if (!empty($filter['tanggal_selesai'])) {
            $this->db->where('DATE(pta.tanggal_selesai) <=', $filter['tanggal_selesai']);
        }
        
        if (!empty($filter['prodi_id'])) {
            $this->db->where('pr.id', $filter['prodi_id']);
        }
        
        if (!empty($filter['status'])) {
            $this->db->where('pta.status', $filter['status']);
        }
        
        $this->db->order_by('pta.tanggal_pengajuan', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Download file dari publikasi
     */
    private function _download_file($publikasi, $file_type) {
        $file_mapping = [
            'surat_revisi' => $publikasi->file_surat_revisi,
            'skripsi_final' => $publikasi->file_skripsi_final,
            'surat_perpustakaan' => $publikasi->file_surat_perpustakaan
        ];
        
        if (!isset($file_mapping[$file_type])) {
            show_404();
        }
        
        $filename = $file_mapping[$file_type];
        $file_path = "./uploads/publikasi/{$file_type}/{$filename}";
        
        if (!file_exists($file_path)) {
            show_404();
        }
        
        $this->load->helper('download');
        force_download($filename, file_get_contents($file_path));
    }

    /**
     * Export to Excel
     */
    private function _export_excel($data) {
        // TODO: Implement Excel export using PhpSpreadsheet
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="Laporan_Publikasi_' . date('Y-m-d') . '.xls"');
        
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>NIM</th>";
        echo "<th>Nama Mahasiswa</th>";
        echo "<th>Program Studi</th>";
        echo "<th>Judul Skripsi</th>";
        echo "<th>Dosen Pembimbing</th>";
        echo "<th>Tanggal Pengajuan</th>";
        echo "<th>Status</th>";
        echo "<th>Link Repository</th>";
        echo "</tr>";
        
        foreach ($data['publikasi_list'] as $item) {
            echo "<tr>";
            echo "<td>{$item->nim}</td>";
            echo "<td>{$item->nama_mahasiswa}</td>";
            echo "<td>{$item->nama_prodi}</td>";
            echo "<td>{$item->judul_skripsi_final}</td>";
            echo "<td>{$item->nama_pembimbing}</td>";
            echo "<td>{$item->tanggal_pengajuan}</td>";
            echo "<td>{$item->status}</td>";
            echo "<td>{$item->link_repository}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }

    /**
     * Send notification to mahasiswa
     */
    private function _send_notification_to_mahasiswa($publikasi, $status) {
        // TODO: Implement notification system
        log_message('info', "Notification sent to mahasiswa_id: {$publikasi->mahasiswa_id} with status: {$status}");
    }
}
