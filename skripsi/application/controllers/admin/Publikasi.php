// =====================================================
// 4. KAPRODI CONTROLLER
// File: application/controllers/kaprodi/Publikasi.php
// =====================================================

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Publikasi untuk Kaprodi
 * Handle monitoring dan override decisions
 */
class Publikasi extends CI_Controller {

    private $kaprodi_id;
    private $prodi_id;

    public function __construct() {
        parent::__construct();
        
        // Load dependencies
        $this->load->database();
        $this->load->library(['session']);
        $this->load->helper(['url', 'date', 'text']);
        $this->load->model('Publikasi_model', 'publikasi');
        
        // Auth check untuk kaprodi
        if(!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }
        
        if(!in_array($this->session->userdata('level'), ['3', '4'])) { // Kaprodi
            show_error('Akses ditolak. Halaman khusus Kaprodi.', 403);
        }
        
        $this->kaprodi_id = $this->session->userdata('user_id');
        $this->prodi_id = $this->session->userdata('prodi_id');
    }

    /**
     * Dashboard monitoring publikasi untuk kaprodi
     */
    public function index() {
        $data = [
            'title' => 'Monitoring Publikasi Tugas Akhir',
            'summary_stats' => $this->_get_summary_statistics(),
            'publikasi_terbaru' => $this->_get_publikasi_terbaru(),
            'progress_chart_data' => $this->_get_progress_chart_data(),
            'mahasiswa_eligible' => $this->_get_mahasiswa_eligible_publikasi()
        ];
        
        $this->load->view('kaprodi/publikasi/index', $data);
    }

    /**
     * Detail monitoring publikasi mahasiswa
     */
    public function detail($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan.');
            redirect('kaprodi/publikasi');
        }
        
        // Validasi prodi
        if ($publikasi->prodi_id != $this->prodi_id) {
            $this->session->set_flashdata('error', 'Data bukan dari program studi Anda.');
            redirect('kaprodi/publikasi');
        }
        
        $data = [
            'title' => 'Detail Publikasi Tugas Akhir',
            'publikasi' => $publikasi,
            'timeline' => $this->_get_publikasi_timeline($publikasi_id),
            'jurnal_bimbingan' => $this->_get_jurnal_bimbingan($publikasi->proposal_mahasiswa_id)
        ];
        
        $this->load->view('kaprodi/publikasi/detail', $data);
    }

    /**
     * Laporan publikasi per prodi
     */
    public function laporan() {
        $periode = $this->input->get('periode') ?: date('Y');
        
        $data = [
            'title' => 'Laporan Publikasi Tugas Akhir',
            'periode' => $periode,
            'laporan_bulanan' => $this->_get_laporan_bulanan($periode),
            'laporan_mahasiswa' => $this->_get_laporan_mahasiswa($periode),
            'statistik_dosen' => $this->_get_statistik_per_dosen($periode)
        ];
        
        if ($this->input->get('export') === 'pdf') {
            $this->_export_pdf($data);
        } else {
            $this->load->view('kaprodi/publikasi/laporan', $data);
        }
    }

    /**
     * Override decision untuk emergency cases
     */
    public function override($publikasi_id) {
        $publikasi = $this->_get_publikasi_detail($publikasi_id);
        
        if (!$publikasi || $publikasi->prodi_id != $this->prodi_id) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan atau akses ditolak.');
            redirect('kaprodi/publikasi');
        }
        
        if ($this->input->method() === 'post') {
            $this->_process_override($publikasi);
        } else {
            $this->_show_override_form($publikasi);
        }
    }

    // =================================================================
    // PRIVATE METHODS
    // =================================================================

    /**
     * Get summary statistics untuk dashboard
     */
    private function _get_summary_statistics() {
        $stats = [
            'total_mahasiswa_prodi' => 0,
            'eligible_publikasi' => 0,
            'pengajuan_berjalan' => 0,
            'publikasi_selesai' => 0,
            'rata_waktu_proses' => 0
        ];
        
        // Total mahasiswa di prodi ini
        $stats['total_mahasiswa_prodi'] = $this->db->where('prodi_id', $this->prodi_id)
                                                 ->where('status', '1')
                                                 ->count_all_results('mahasiswa');
        
        // Mahasiswa eligible publikasi (16+ jurnal tervalidasi)
        $this->db->select('COUNT(DISTINCT pm.mahasiswa_id) as eligible');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('jurnal_bimbingan jb', 'jb.proposal_id = pm.id AND jb.status_validasi = "1"', 'left');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('pm.status', '1');
        $this->db->group_by('pm.id');
        $this->db->having('COUNT(jb.id) >= 16');
        $result = $this->db->get();
        $stats['eligible_publikasi'] = $result->num_rows();
        
        // Pengajuan yang sedang berjalan
        $this->db->join('mahasiswa m', 'publikasi_tugas_akhir.mahasiswa_id = m.id');
        $stats['pengajuan_berjalan'] = $this->db->where('m.prodi_id', $this->prodi_id)
                                              ->where_in('publikasi_tugas_akhir.status', ['draft', 'submitted', 'review_pembimbing', 'review_staf'])
                                              ->count_all_results('publikasi_tugas_akhir');
        
        // Publikasi selesai
        $this->db->join('mahasiswa m', 'publikasi_tugas_akhir.mahasiswa_id = m.id');
        $stats['publikasi_selesai'] = $this->db->where('m.prodi_id', $this->prodi_id)
                                             ->where('publikasi_tugas_akhir.status', 'completed')
                                             ->count_all_results('publikasi_tugas_akhir');
        
        // Rata-rata waktu proses (dalam hari)
        $this->db->select('AVG(DATEDIFF(tanggal_selesai, tanggal_pengajuan)) as rata_hari');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('pta.status', 'completed');
        $this->db->where('pta.tanggal_selesai IS NOT NULL');
        $result = $this->db->get()->row();
        $stats['rata_waktu_proses'] = $result ? round($result->rata_hari, 1) : 0;
        
        return $stats;
    }

    /**
     * Get publikasi terbaru di prodi
     */
    private function _get_publikasi_terbaru() {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->order_by('pta.updated_at', 'DESC');
        $this->db->limit(10);
        
        return $this->db->get()->result();
    }

    /**
     * Get data untuk chart progress
     */
    private function _get_progress_chart_data() {
        $data = [];
        $statuses = ['draft', 'submitted', 'review_pembimbing', 'review_staf', 'completed'];
        
        foreach ($statuses as $status) {
            $this->db->join('mahasiswa m', 'publikasi_tugas_akhir.mahasiswa_id = m.id');
            $count = $this->db->where('m.prodi_id', $this->prodi_id)
                            ->where('publikasi_tugas_akhir.status', $status)
                            ->count_all_results('publikasi_tugas_akhir');
            
            $data[] = [
                'status' => ucfirst(str_replace('_', ' ', $status)),
                'count' => $count
            ];
        }
        
        return $data;
    }

    /**
     * Get mahasiswa yang eligible untuk publikasi tapi belum mengajukan
     */
    private function _get_mahasiswa_eligible_publikasi() {
        // Query kompleks untuk cari mahasiswa dengan 16+ jurnal tervalidasi
        // tapi belum ada pengajuan publikasi
        $sql = "
            SELECT DISTINCT
                m.id, m.nim, m.nama as nama_mahasiswa,
                pm.judul,
                d.nama as nama_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi
            FROM proposal_mahasiswa pm
            JOIN mahasiswa m ON pm.mahasiswa_id = m.id
            JOIN dosen d ON pm.dosen_id = d.id
            LEFT JOIN jurnal_bimbingan jb ON jb.proposal_id = pm.id AND jb.status_validasi = '1'
            LEFT JOIN publikasi_tugas_akhir pta ON pta.proposal_mahasiswa_id = pm.id
            WHERE m.prodi_id = ?
                AND pm.status = '1'
                AND pta.id IS NULL
            GROUP BY pm.id
            HAVING COUNT(jb.id) >= 16
            ORDER BY jurnal_tervalidasi DESC
        ";
        
        return $this->db->query($sql, [$this->prodi_id])->result();
    }

    /**
     * Get detail publikasi dengan validasi prodi
     */
    private function _get_publikasi_detail($publikasi_id) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            m.prodi_id,
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
     * Get timeline publikasi
     */
    private function _get_publikasi_timeline($publikasi_id) {
        $this->db->select('*');
        $this->db->from('log_publikasi');
        $this->db->where('publikasi_id', $publikasi_id);
        $this->db->order_by('created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get jurnal bimbingan mahasiswa
     */
    private function _get_jurnal_bimbingan($proposal_id) {
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->order_by('tanggal_bimbingan', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get laporan bulanan
     */
    private function _get_laporan_bulanan($periode) {
        $sql = "
            SELECT 
                MONTH(pta.tanggal_pengajuan) as bulan,
                COUNT(*) as total_pengajuan,
                SUM(CASE WHEN pta.status = 'completed' THEN 1 ELSE 0 END) as selesai,
                AVG(CASE WHEN pta.status = 'completed' 
                    THEN DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) 
                    ELSE NULL END) as rata_hari_proses
            FROM publikasi_tugas_akhir pta
            JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE m.prodi_id = ? 
                AND YEAR(pta.tanggal_pengajuan) = ?
            GROUP BY MONTH(pta.tanggal_pengajuan)
            ORDER BY bulan
        ";
        
        return $this->db->query($sql, [$this->prodi_id, $periode])->result();
    }

    /**
     * Get laporan per mahasiswa
     */
    private function _get_laporan_mahasiswa($periode) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing,
            DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) as lama_proses_hari
        ');
        $this->db->from('publikasi_tugas_akhir pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        $this->db->where('m.prodi_id', $this->prodi_id);
        $this->db->where('YEAR(pta.tanggal_pengajuan)', $periode);
        $this->db->order_by('pta.tanggal_pengajuan', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get statistik per dosen pembimbing
     */
    private function _get_statistik_per_dosen($periode) {
        $sql = "
            SELECT 
                d.nama as nama_dosen,
                COUNT(pta.id) as total_mahasiswa,
                SUM(CASE WHEN pta.status = 'completed' THEN 1 ELSE 0 END) as selesai,
                AVG(CASE WHEN pta.status = 'completed' 
                    THEN DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) 
                    ELSE NULL END) as rata_hari_proses
            FROM dosen d
            LEFT JOIN publikasi_tugas_akhir pta ON d.id = pta.dosen_pembimbing_id 
                AND YEAR(pta.tanggal_pengajuan) = ?
            JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE d.prodi_id = ?
                AND d.level IN ('2', '4')
            GROUP BY d.id
            ORDER BY total_mahasiswa DESC
        ";
        
        return $this->db->query($sql, [$periode, $this->prodi_id])->result();
    }

    /**
     * Process override dari kaprodi
     */
    private function _process_override($publikasi) {
        $action = $this->input->post('override_action');
        $komentar = $this->input->post('komentar_kaprodi');
        
        if (empty($komentar)) {
            $this->session->set_flashdata('error', 'Komentar override harus diisi.');
            $this->_show_override_form($publikasi);
            return;
        }
        
        // Log override action
        $log_data = [
            'publikasi_id' => $publikasi->id,
            'user_id' => $this->kaprodi_id,
            'user_role' => 'kaprodi',
            'user_name' => $this->session->userdata('nama'),
            'aktivitas' => 'override_' . $action,
            'deskripsi' => 'Kaprodi override: ' . $action . ' - ' . $komentar,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('log_publikasi', $log_data);
        
        $this->session->set_flashdata('success', 'Override berhasil dilakukan.');
        redirect('kaprodi/publikasi/detail/' . $publikasi->id);
    }

    /**
     * Show override form
     */
    private function _show_override_form($publikasi) {
        $data = [
            'title' => 'Override Publikasi Tugas Akhir',
            'publikasi' => $publikasi
        ];
        $this->load->view('kaprodi/publikasi/override', $data);
    }

    /**
     * Export laporan ke PDF
     */
    private function _export_pdf($data) {
        // TODO: Implement PDF export using TCPDF atau mPDF
        echo "Export PDF functionality untuk laporan kaprodi";
    }
}