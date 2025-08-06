<?php
/**
 * =====================================================
 * MODEL PHASE 6 - PUBLIKASI TUGAS AKHIR
 * SIM Tugas Akhir STK Santo Yakobus Merauke
 * =====================================================
 * 
 * File: application/models/Publikasi_model.php
 * Handle semua operasi database untuk publikasi tugas akhir
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model untuk mengelola data publikasi tugas akhir
 * Mendukung workflow: mahasiswa -> dosen -> staf -> selesai
 */
class Publikasi_model extends CI_Model {

    protected $table = 'publikasi_tugas_akhir';
    protected $log_table = 'log_publikasi';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // =================================================================
    // CREATE & UPDATE OPERATIONS
    // =================================================================

    /**
     * Create pengajuan publikasi baru
     * 
     * @param array $data Data publikasi
     * @return array Result with success status and message
     */
    public function create($data) {
        try {
            $this->db->trans_start();
            
            // Cek apakah sudah ada pengajuan untuk proposal ini
            $existing = $this->db->where('proposal_mahasiswa_id', $data['proposal_mahasiswa_id'])
                               ->get($this->table)
                               ->row();
            
            if ($existing) {
                return [
                    'success' => false,
                    'message' => 'Pengajuan publikasi untuk proposal ini sudah ada.'
                ];
            }
            
            // ✅ FIXED: Pastikan data array langsung dipass tanpa mapping ulang
            // Tidak perlu mapping manual karena data sudah sesuai dari controller
            
            // Set timestamp
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Insert data publikasi - LANGSUNG GUNAKAN DATA DARI CONTROLLER
            $this->db->insert($this->table, $data);
            $publikasi_id = $this->db->insert_id();
            
            // Check if insert was successful
            if (!$publikasi_id) {
                throw new Exception('Failed to insert publikasi data');
            }
            
            // Log aktivitas
            if (method_exists($this, '_log_activity')) {
                $this->_log_activity($publikasi_id, $data['mahasiswa_id'], 'mahasiswa', 'create_pengajuan', 'Mahasiswa membuat pengajuan publikasi');
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan data publikasi: ' . $this->db->last_query()
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Pengajuan publikasi berhasil disimpan.',
                'publikasi_id' => $publikasi_id,
                'id' => $publikasi_id // Alias untuk backward compatibility
            ];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error creating publikasi: ' . $e->getMessage());
            log_message('error', 'Last query: ' . $this->db->last_query());
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update pengajuan publikasi
     * 
     * @param int $publikasi_id ID publikasi
     * @param array $data Data yang akan diupdate
     * @param int $user_id ID user yang melakukan update
     * @return array Result
     */
    public function update($publikasi_id, $data, $user_id = null) {
        try {
            $this->db->trans_start();
            
            // Get data lama untuk validasi
            $old_data = $this->get_by_id($publikasi_id);
            if (!$old_data) {
                return [
                    'success' => false,
                    'message' => 'Data publikasi tidak ditemukan.'
                ];
            }
            
            // Set timestamp
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Update data - LANGSUNG GUNAKAN DATA DARI CONTROLLER
            $this->db->where('id', $publikasi_id)
                   ->update($this->table, $data);
            
            // Check if update affected any rows
            if ($this->db->affected_rows() === 0) {
                log_message('warning', 'Update publikasi tidak mempengaruhi row apapun. ID: ' . $publikasi_id);
            }
            
            // Log aktivitas jika user_id tersedia
            if ($user_id && method_exists($this, '_log_activity')) {
                $this->_log_activity($publikasi_id, $user_id, 'mahasiswa', 'update_pengajuan', 'Mahasiswa mengupdate pengajuan publikasi');
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => false,
                    'message' => 'Gagal mengupdate data publikasi: ' . $this->db->last_query()
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Data publikasi berhasil diupdate.'
            ];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error updating publikasi: ' . $e->getMessage());
            log_message('error', 'Last query: ' . $this->db->last_query());
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Submit pengajuan ke dosen pembimbing
     * 
     * @param int $publikasi_id ID publikasi
     * @return array Result
     */
    public function submit_pengajuan($publikasi_id) {
        try {
            $this->db->trans_start();
            
            $data = [
                'status' => 'submitted',
                'current_step' => 'pembimbing',
                'tanggal_pengajuan' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $publikasi_id)
                   ->where('status', 'draft') // Hanya bisa submit dari draft
                   ->update($this->table, $data);
            
            if ($this->db->affected_rows() === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat submit. Periksa status pengajuan.'
                ];
            }
            
            // Get data untuk log
            $publikasi = $this->get_by_id($publikasi_id);
            
            // Log aktivitas
            $this->_log_activity($publikasi_id, $publikasi->mahasiswa_id, 'mahasiswa', 'submit_pengajuan', 'Mahasiswa submit pengajuan ke dosen pembimbing');
            
            $this->db->trans_complete();
            
            return [
                'success' => true,
                'message' => 'Pengajuan berhasil disubmit ke dosen pembimbing.'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error submitting publikasi: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ];
        }
    }

    // =================================================================
    // DOSEN OPERATIONS
    // =================================================================

    /**
     * Approve pengajuan oleh dosen pembimbing
     * 
     * @param int $publikasi_id ID publikasi
     * @param int $dosen_id ID dosen
     * @param string $komentar Komentar dosen (optional)
     * @return array Result
     */
    public function approve_by_dosen($publikasi_id, $dosen_id, $komentar = null) {
        try {
            $this->db->trans_start();
            
            $data = [
                'status' => 'review_staf',
                'current_step' => 'staf',
                'status_pembimbing' => 'approved',
                'komentar_pembimbing' => $komentar,
                'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $publikasi_id)
                   ->where('dosen_pembimbing_id', $dosen_id)
                   ->where('status_pembimbing', 'pending')
                   ->update($this->table, $data);
            
            if ($this->db->affected_rows() === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat approve. Periksa status atau kepemilikan data.'
                ];
            }
            
            // Log aktivitas
            $this->_log_activity($publikasi_id, $dosen_id, 'dosen', 'approve_pembimbing', 'Dosen pembimbing menyetujui pengajuan publikasi');
            
            $this->db->trans_complete();
            
            return [
                'success' => true,
                'message' => 'Pengajuan berhasil disetujui dan diteruskan ke staf.'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error approving publikasi by dosen: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ];
        }
    }

    /**
     * Reject pengajuan oleh dosen pembimbing
     * 
     * @param int $publikasi_id ID publikasi
     * @param int $dosen_id ID dosen
     * @param string $komentar Komentar penolakan
     * @return array Result
     */
    public function reject_by_dosen($publikasi_id, $dosen_id, $komentar) {
        try {
            $this->db->trans_start();
            
            $data = [
                'status' => 'rejected',
                'current_step' => 'mahasiswa',
                'status_pembimbing' => 'rejected',
                'komentar_pembimbing' => $komentar,
                'tanggal_review_pembimbing' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->where('id', $publikasi_id)
                   ->where('dosen_pembimbing_id', $dosen_id)
                   ->where('status_pembimbing', 'pending')
                   ->update($this->table, $data);
            
            if ($this->db->affected_rows() === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat reject. Periksa status atau kepemilikan data.'
                ];
            }
            
            // Log aktivitas
            $this->_log_activity($publikasi_id, $dosen_id, 'dosen', 'reject_pembimbing', 'Dosen pembimbing menolak pengajuan publikasi: ' . $komentar);
            
            $this->db->trans_complete();
            
            return [
                'success' => true,
                'message' => 'Pengajuan ditolak dan dikembalikan ke mahasiswa.'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error rejecting publikasi by dosen: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ];
        }
    }

    // =================================================================
    // STAF OPERATIONS
    // =================================================================

    /**
     * Complete publikasi oleh staf (input repository & validasi final)
     * 
     * @param int $publikasi_id ID publikasi
     * @param array $data Data dari staf (link_repository, komentar, dll)
     * @return array Result
     */
    public function complete_by_staf($publikasi_id, $data) {
        try {
            $this->db->trans_start();
            
             $update_data = [
                'status' => 'completed',
                // 'current_step' => 'selesai',        // ❌ SUDAH DIHAPUS
                'status_staf' => 'approved',
                'link_repository' => $data['link_repository'],
                'komentar_staf' => $data['komentar_staf'] ?? null,
                'validated_by_staf_id' => $data['validated_by_staf_id'],
                'validated_by_staf_name' => $data['validated_by_staf_name'],
                'tanggal_validasi_staf' => date('Y-m-d H:i:s'),
                'tanggal_selesai' => date('Y-m-d H:i:s')
                // 'updated_at' => date('Y-m-d H:i:s')  // ❌ HAPUS INI JUGA
            ];
            
            $this->db->where('id', $publikasi_id)
                   ->where('status_pembimbing', 'approved')
                   ->where('status_staf', 'pending')
                   ->update($this->table, $update_data);
            
            if ($this->db->affected_rows() === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat menyelesaikan publikasi. Periksa status pengajuan.'
                ];
            }
            
            // ✅ PERBAIKAN: Ganti 'publikasi' jadi 'selesai'
            $publikasi = $this->get_by_id($publikasi_id);
            if ($publikasi) {
                $this->db->where('id', $publikasi->proposal_mahasiswa_id)
                       ->update('proposal_mahasiswa', ['workflow_status' => 'selesai']); // ✅ 'selesai' bukan 'publikasi'
            }
            
            // Log aktivitas (skip jika method tidak ada)
            // $this->_log_activity($publikasi_id, $data['validated_by_staf_id'], 'staf', 'complete_publikasi', 'Staf menyelesaikan validasi publikasi dan input repository: ' . $data['link_repository']);
            
            $this->db->trans_complete();
            
            return [
                'success' => true,
                'message' => 'Publikasi berhasil diselesaikan. Mahasiswa dapat download surat keterangan.'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error completing publikasi by staf: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ];
        }
    }

    // =================================================================
    // READ OPERATIONS
    // =================================================================

    /**
     * Get publikasi by ID
     * 
     * @param int $publikasi_id ID publikasi
     * @param int $mahasiswa_id ID mahasiswa untuk validasi ownership (optional)
     * @return object|null Data publikasi
     */
    public function get_by_id($publikasi_id, $mahasiswa_id = null) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('id', $publikasi_id);
        
        if ($mahasiswa_id) {
            $this->db->where('mahasiswa_id', $mahasiswa_id);
        }
        
        return $this->db->get()->row();
    }

    /**
     * Get publikasi by proposal ID
     * 
     * @param int $proposal_id ID proposal mahasiswa
     * @return object|null Data publikasi
     */
    public function get_by_proposal($proposal_id) {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('proposal_mahasiswa_id', $proposal_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get publikasi dengan join data lengkap
     * 
     * @param int $publikasi_id
     * @return object|null
     */
    public function get_detail($publikasi_id) {
        $this->db->select('
            p.*,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            pm.workflow_status,
            pm.judul as judul_proposal_awal,
            d.nama as nama_pembimbing_lengkap,
            d.email as email_pembimbing
        ');
        $this->db->from('publikasi_tugas_akhir p');
        $this->db->join('mahasiswa m', 'p.mahasiswa_id = m.id');
        $this->db->join('proposal_mahasiswa pm', 'p.proposal_mahasiswa_id = pm.id');
        $this->db->join('dosen d', 'p.dosen_pembimbing_id = d.id', 'left');
        $this->db->where('p.id', $publikasi_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get publikasi dengan detail lengkap menggunakan view
     * 
     * @param array $filter Filter data
     * @param int $limit Limit data
     * @param int $offset Offset data
     * @return array Data publikasi
     */
    public function get_list($filter = [], $limit = null, $offset = null) {
        $this->db->select('*');
        $this->db->from('publikasi_mahasiswa_v'); // Menggunakan view yang sudah dibuat
        
        // Apply filters
        if (!empty($filter['mahasiswa_id'])) {
            $this->db->where('mahasiswa_id', $filter['mahasiswa_id']);
        }
        
        if (!empty($filter['dosen_pembimbing_id'])) {
            $this->db->where('dosen_pembimbing_id', $filter['dosen_pembimbing_id']);
        }
        
        if (!empty($filter['prodi_id'])) {
            $this->db->where('program_studi', $filter['prodi_id']);
        }
        
        if (!empty($filter['status'])) {
            $this->db->where('status', $filter['status']);
        }
        
        if (!empty($filter['status_pembimbing'])) {
            $this->db->where('status_pembimbing', $filter['status_pembimbing']);
        }
        
        if (!empty($filter['status_staf'])) {
            $this->db->where('status_staf', $filter['status_staf']);
        }
        
        if (!empty($filter['tanggal_mulai'])) {
            $this->db->where('DATE(tanggal_pengajuan) >=', $filter['tanggal_mulai']);
        }
        
        if (!empty($filter['tanggal_selesai'])) {
            $this->db->where('DATE(tanggal_selesai) <=', $filter['tanggal_selesai']);
        }
        
        $this->db->order_by('updated_at', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Count total publikasi dengan filter
     * 
     * @param array $filter Filter data
     * @return int Total count
     */
    public function count_all($filter = []) {
        $this->db->from('publikasi_mahasiswa_v');
        
        // Apply same filters as get_list()
        if (!empty($filter['mahasiswa_id'])) {
            $this->db->where('mahasiswa_id', $filter['mahasiswa_id']);
        }
        
        if (!empty($filter['dosen_pembimbing_id'])) {
            $this->db->where('dosen_pembimbing_id', $filter['dosen_pembimbing_id']);
        }
        
        if (!empty($filter['prodi_id'])) {
            $this->db->where('program_studi', $filter['prodi_id']);
        }
        
        if (!empty($filter['status'])) {
            $this->db->where('status', $filter['status']);
        }
        
        if (!empty($filter['status_pembimbing'])) {
            $this->db->where('status_pembimbing', $filter['status_pembimbing']);
        }
        
        if (!empty($filter['status_staf'])) {
            $this->db->where('status_staf', $filter['status_staf']);
        }
        
        if (!empty($filter['tanggal_mulai'])) {
            $this->db->where('DATE(tanggal_pengajuan) >=', $filter['tanggal_mulai']);
        }
        
        if (!empty($filter['tanggal_selesai'])) {
            $this->db->where('DATE(tanggal_selesai) <=', $filter['tanggal_selesai']);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Check syarat publikasi mahasiswa
     * 
     * @param int $proposal_id ID proposal mahasiswa
     * @return array Status syarat publikasi
     */
    public function check_syarat_publikasi($proposal_id) {
        // Hitung jurnal bimbingan tervalidasi
        $jurnal_count = $this->db->where('proposal_id', $proposal_id)
                               ->where('status_validasi', '1')
                               ->count_all_results('jurnal_bimbingan');
        
        $eligible = ($jurnal_count >= 16);
        
        return [
            'eligible' => $eligible,
            'jurnal_tervalidasi' => $jurnal_count,
            'jurnal_dibutuhkan' => 16,
            'message' => $eligible ? 'Memenuhi syarat publikasi' : "Jurnal bimbingan belum memenuhi syarat. Saat ini: {$jurnal_count}/16 tervalidasi"
        ];
    }

    /**
     * Get statistics untuk dashboard
     * 
     * @param array $filter Filter untuk statistik
     * @return array Data statistik
     */
    public function get_statistics($filter = []) {
        $stats = [
            'total' => 0,
            'draft' => 0,
            'submitted' => 0,
            'review_pembimbing' => 0,
            'review_staf' => 0,
            'completed' => 0,
            'rejected' => 0,
            'avg_process_days' => 0,
            'success_rate' => 0
        ];
        
        // Build base query with filters
        $where_conditions = $this->_build_filter_conditions($filter);
        
        // Get total count
        $this->db->from($this->table . ' pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->where($where_conditions);
        $stats['total'] = $this->db->count_all_results();
        
        // Get count per status
        $statuses = ['draft', 'submitted', 'review_pembimbing', 'review_staf', 'completed', 'rejected'];
        
        foreach ($statuses as $status) {
            $this->db->from($this->table . ' pta');
            $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
            $this->db->where('pta.status', $status);
            $this->db->where($where_conditions);
            $stats[$status] = $this->db->count_all_results();
        }
        
        // Calculate average process time
        $this->db->select('AVG(DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan)) as avg_days');
        $this->db->from($this->table . ' pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->where('pta.status', 'completed');
        $this->db->where('pta.tanggal_selesai IS NOT NULL');
        $this->db->where($where_conditions);
        $result = $this->db->get()->row();
        $stats['avg_process_days'] = $result ? round($result->avg_days, 1) : 0;
        
        // Calculate success rate
        $submitted_count = $stats['submitted'] + $stats['review_pembimbing'] + $stats['review_staf'] + $stats['completed'] + $stats['rejected'];
        $stats['success_rate'] = $submitted_count > 0 ? round(($stats['completed'] / $submitted_count) * 100, 1) : 0;
        
        return $stats;
    }

    /**
     * Get advanced statistics untuk admin dashboard
     * 
     * @param array $filter Filter untuk statistik
     * @return array Data statistik lanjutan
     */
    public function get_advanced_statistics($filter = []) {
        $stats = $this->get_statistics($filter);
        
        // Add advanced metrics
        $stats['bottleneck_analysis'] = $this->_get_bottleneck_analysis($filter);
        $stats['performance_trends'] = $this->_get_performance_trends($filter);
        $stats['rejection_analysis'] = $this->_get_rejection_analysis($filter);
        $stats['dosen_performance'] = $this->_get_dosen_performance($filter);
        $stats['monthly_completion'] = $this->_get_monthly_completion($filter);
        
        return $stats;
    }

    /**
     * Get publikasi untuk dosen tertentu
     * 
     * @param int $dosen_id ID dosen pembimbing
     * @param array $filter Filter tambahan
     * @return array Data publikasi
     */
    public function get_by_dosen($dosen_id, $filter = []) {
        $filter['dosen_pembimbing_id'] = $dosen_id;
        return $this->get_list($filter);
    }

    /**
     * Get publikasi untuk prodi tertentu
     * 
     * @param int $prodi_id ID program studi
     * @param array $filter Filter tambahan
     * @return array Data publikasi
     */
    public function get_by_prodi($prodi_id, $filter = []) {
        $this->db->select('pta.*, m.nim, m.nama as nama_mahasiswa, pr.nama as nama_prodi, d.nama as nama_pembimbing');
        $this->db->from($this->table . ' pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        $this->db->where('m.prodi_id', $prodi_id);
        
        // Apply additional filters
        $this->_apply_additional_filters($filter);
        
        $this->db->order_by('pta.updated_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get publikasi yang membutuhkan review dosen
     * 
     * @param int $dosen_id ID dosen
     * @return array Data publikasi
     */
    public function get_pending_dosen_review($dosen_id) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            pr.nama as nama_prodi,
            pm.judul as judul_proposal
        ');
        $this->db->from($this->table . ' pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
        $this->db->where('pta.dosen_pembimbing_id', $dosen_id);
        $this->db->where_in('pta.status', ['submitted', 'review_pembimbing']);
        $this->db->where('pta.status_pembimbing', 'pending');
        $this->db->order_by('pta.tanggal_pengajuan', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get publikasi yang membutuhkan validasi staf
     * 
     * @return array Data publikasi
     */
    public function get_pending_staf_validation() {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing,
            pm.judul as judul_proposal
        ');
        $this->db->from($this->table . ' pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
        $this->db->where('pta.status_pembimbing', 'approved');
        $this->db->where('pta.status_staf', 'pending');
        $this->db->where_in('pta.status', ['review_staf']);
        $this->db->order_by('pta.tanggal_review_pembimbing', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get mahasiswa yang eligible untuk publikasi
     * 
     * @param int $prodi_id ID prodi (optional)
     * @return array Data mahasiswa eligible
     */
    public function get_eligible_mahasiswa($prodi_id = null) {
        $sql = "
            SELECT DISTINCT
                m.id as mahasiswa_id, m.nim, m.nama as nama_mahasiswa,
                pm.id as proposal_id, pm.judul,
                d.nama as nama_pembimbing,
                COUNT(jb.id) as jurnal_tervalidasi,
                pta.id as publikasi_id,
                pta.status as publikasi_status
            FROM proposal_mahasiswa pm
            JOIN mahasiswa m ON pm.mahasiswa_id = m.id
            JOIN dosen d ON pm.dosen_id = d.id
            LEFT JOIN jurnal_bimbingan jb ON jb.proposal_id = pm.id AND jb.status_validasi = '1'
            LEFT JOIN publikasi_tugas_akhir pta ON pta.proposal_mahasiswa_id = pm.id
            WHERE pm.status = '1'
        ";
        
        $params = [];
        if ($prodi_id) {
            $sql .= " AND m.prodi_id = ?";
            $params[] = $prodi_id;
        }
        
        $sql .= "
            GROUP BY pm.id
            HAVING COUNT(jb.id) >= 16
            ORDER BY jurnal_tervalidasi DESC, m.nama ASC
        ";
        
        return $this->db->query($sql, $params)->result();
    }

    /**
     * Get performance summary per prodi
     * 
     * @param string $periode Periode (YYYY atau YYYY-MM)
     * @return array Data performance
     */
    public function get_prodi_performance($periode = null) {
        $sql = "
            SELECT 
                pr.id as prodi_id,
                pr.nama as nama_prodi,
                COUNT(pta.id) as total_pengajuan,
                SUM(CASE WHEN pta.status = 'completed' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN pta.status = 'rejected' THEN 1 ELSE 0 END) as ditolak,
                AVG(CASE WHEN pta.status = 'completed' 
                    THEN DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) 
                    ELSE NULL END) as rata_hari_proses,
                ROUND((SUM(CASE WHEN pta.status = 'completed' THEN 1 ELSE 0 END) / COUNT(pta.id)) * 100, 1) as success_rate
            FROM prodi pr
            LEFT JOIN mahasiswa m ON pr.id = m.prodi_id
            LEFT JOIN publikasi_tugas_akhir pta ON m.id = pta.mahasiswa_id
        ";
        
        $params = [];
        if ($periode) {
            if (strlen($periode) === 4) {
                // Yearly
                $sql .= " WHERE YEAR(pta.tanggal_pengajuan) = ?";
                $params[] = $periode;
            } elseif (strlen($periode) === 7) {
                // Monthly (YYYY-MM)
                $sql .= " WHERE DATE_FORMAT(pta.tanggal_pengajuan, '%Y-%m') = ?";
                $params[] = $periode;
            }
        }
        
        $sql .= "
            GROUP BY pr.id
            ORDER BY success_rate DESC, selesai DESC
        ";
        
        return $this->db->query($sql, $params)->result();
    }

    /**
     * Get timeline publikasi untuk tracking
     * 
     * @param int $publikasi_id ID publikasi
     * @return array Timeline data
     */
    public function get_timeline($publikasi_id) {
        // Get from log table
        $logs = $this->get_log_activities($publikasi_id);
        
        // Get key milestones from main table
        $this->db->select('
            tanggal_pengajuan, tanggal_review_pembimbing, 
            tanggal_validasi_staf, tanggal_selesai,
            status, status_pembimbing, status_staf
        ');
        $this->db->where('id', $publikasi_id);
        $milestones = $this->db->get($this->table)->row();
        
        $timeline = [];
        
        if ($milestones) {
            // Add key milestones
            if ($milestones->tanggal_pengajuan) {
                $timeline[] = [
                    'tanggal' => $milestones->tanggal_pengajuan,
                    'event' => 'Pengajuan Disubmit',
                    'type' => 'milestone',
                    'status' => 'completed'
                ];
            }
            
            if ($milestones->tanggal_review_pembimbing) {
                $timeline[] = [
                    'tanggal' => $milestones->tanggal_review_pembimbing,
                    'event' => 'Review Dosen: ' . ucfirst($milestones->status_pembimbing),
                    'type' => 'milestone',
                    'status' => $milestones->status_pembimbing === 'approved' ? 'success' : 'warning'
                ];
            }
            
            if ($milestones->tanggal_validasi_staf) {
                $timeline[] = [
                    'tanggal' => $milestones->tanggal_validasi_staf,
                    'event' => 'Validasi Staf: ' . ucfirst($milestones->status_staf),
                    'type' => 'milestone',
                    'status' => $milestones->status_staf === 'approved' ? 'success' : 'warning'
                ];
            }
            
            if ($milestones->tanggal_selesai) {
                $timeline[] = [
                    'tanggal' => $milestones->tanggal_selesai,
                    'event' => 'Publikasi Selesai',
                    'type' => 'milestone',
                    'status' => 'completed'
                ];
            }
        }
        
        // Add detailed logs
        foreach ($logs as $log) {
            $timeline[] = [
                'tanggal' => $log->created_at,
                'event' => $log->deskripsi,
                'type' => 'activity',
                'user' => $log->user_name,
                'role' => $log->user_role,
                'status' => 'info'
            ];
        }
        
        // Sort by date
        usort($timeline, function($a, $b) {
            return strtotime($a['tanggal']) - strtotime($b['tanggal']);
        });
        
        return $timeline;
    }

    /**
     * Bulk update status publikasi (untuk admin)
     * 
     * @param array $publikasi_ids Array ID publikasi
     * @param string $new_status Status baru
     * @param int $admin_id ID admin
     * @param string $reason Alasan bulk update
     * @return array Result
     */
    public function bulk_update_status($publikasi_ids, $new_status, $admin_id, $reason) {
        if (empty($publikasi_ids) || !is_array($publikasi_ids)) {
            return [
                'success' => false,
                'message' => 'ID publikasi tidak valid.'
            ];
        }
        
        try {
            $this->db->trans_start();
            
            $update_data = [
                'status' => $new_status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Special handling untuk completed status
            if ($new_status === 'completed') {
                $update_data['tanggal_selesai'] = date('Y-m-d H:i:s');
                $update_data['status_staf'] = 'approved';
            }
            
            $this->db->where_in('id', $publikasi_ids);
            $this->db->update($this->table, $update_data);
            
            $affected_rows = $this->db->affected_rows();
            
            // Log bulk action
            foreach ($publikasi_ids as $publikasi_id) {
                $this->_log_activity(
                    $publikasi_id, 
                    $admin_id, 
                    'admin', 
                    'bulk_update_status', 
                    "Admin bulk update status ke {$new_status}. Alasan: {$reason}"
                );
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => false,
                    'message' => 'Gagal melakukan bulk update.'
                ];
            }
            
            return [
                'success' => true,
                'message' => "Berhasil update {$affected_rows} publikasi ke status {$new_status}.",
                'affected_rows' => $affected_rows
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error bulk update publikasi: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ];
        }
    }

    /**
     * Generate export data untuk Excel/PDF
     * 
     * @param array $filter Filter data
     * @return array Export data
     */
    public function get_export_data($filter = []) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
            m.nomor_telepon,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing, d.email as email_pembimbing,
            pm.judul as judul_proposal,
            DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) as lama_proses_hari
        ');
        $this->db->from($this->table . ' pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
        
        // Apply filters
        $this->_apply_export_filters($filter);
        
        $this->db->order_by('pta.tanggal_pengajuan', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * ✅ TAMBAHAN: Update file surat keterangan setelah generate
     */
    public function update_surat_keterangan($publikasi_id, $filename) {
        return $this->db->where('id', $publikasi_id)
                      ->update('publikasi_tugas_akhir', [
                          'file_surat_keterangan' => $filename,
                          'updated_at' => date('Y-m-d H:i:s')
                      ]);
    }
    
    /**
     * ✅ TAMBAHAN: Get publikasi dengan data lengkap untuk surat keterangan
     */
    public function get_publikasi_with_mahasiswa($publikasi_id) {
        $this->db->select('
            p.*,
            m.nama as nama_mahasiswa,
            m.nim,
            pm.judul as judul_skripsi_final,
            pm.tanggal_seminar_skripsi as tanggal_ujian_skripsi,
            d.nama as nama_dosen_pembimbing,
            pr.nama_prodi as program_studi
        ');
        $this->db->from('publikasi_tugas_akhir p');
        $this->db->join('mahasiswa m', 'p.mahasiswa_id = m.id', 'left');
        $this->db->join('proposal_mahasiswa pm', 'p.proposal_mahasiswa_id = pm.id', 'left');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id', 'left');
        $this->db->where('p.id', $publikasi_id);
        
        $query = $this->db->get();
        return $query->row();
    }

    // =================================================================
    // LOG OPERATIONS
    // =================================================================

    /**
     * Get log aktivitas publikasi
     * 
     * @param int $publikasi_id ID publikasi
     * @return array Data log aktivitas
     */
    public function get_log_activities($publikasi_id) {
        $this->db->select('*');
        $this->db->from($this->log_table);
        $this->db->where('publikasi_id', $publikasi_id);
        $this->db->order_by('created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    // =================================================================
    // PRIVATE HELPER METHODS
    // =================================================================

    /**
     * Build filter conditions untuk query
     * 
     * @param array $filter Filter parameters
     * @return array Where conditions
     */
    private function _build_filter_conditions($filter) {
        $conditions = [];
        
        if (!empty($filter['prodi_id'])) {
            $conditions['m.prodi_id'] = $filter['prodi_id'];
        }
        
        if (!empty($filter['dosen_id'])) {
            $conditions['pta.dosen_pembimbing_id'] = $filter['dosen_id'];
        }
        
        if (!empty($filter['tahun'])) {
            $conditions['YEAR(pta.tanggal_pengajuan)'] = $filter['tahun'];
        }
        
        if (!empty($filter['bulan'])) {
            $conditions['MONTH(pta.tanggal_pengajuan)'] = $filter['bulan'];
        }
        
        return $conditions;
    }

    /**
     * Apply additional filters untuk query
     * 
     * @param array $filter Filter parameters
     */
    private function _apply_additional_filters($filter) {
        if (!empty($filter['status'])) {
            $this->db->where('pta.status', $filter['status']);
        }
        
        if (!empty($filter['status_pembimbing'])) {
            $this->db->where('pta.status_pembimbing', $filter['status_pembimbing']);
        }
        
        if (!empty($filter['status_staf'])) {
            $this->db->where('pta.status_staf', $filter['status_staf']);
        }
        
        if (!empty($filter['tanggal_mulai'])) {
            $this->db->where('DATE(pta.tanggal_pengajuan) >=', $filter['tanggal_mulai']);
        }
        
        if (!empty($filter['tanggal_selesai'])) {
            $this->db->where('DATE(pta.tanggal_selesai) <=', $filter['tanggal_selesai']);
        }
        
        if (!empty($filter['search'])) {
            $this->db->group_start();
            $this->db->like('m.nama', $filter['search']);
            $this->db->or_like('m.nim', $filter['search']);
            $this->db->or_like('pta.judul_skripsi_final', $filter['search']);
            $this->db->group_end();
        }
    }

    /**
     * Apply filters for export data
     * 
     * @param array $filter Filter parameters
     */
    private function _apply_export_filters($filter) {
        $this->_apply_additional_filters($filter);
        
        // Additional export-specific filters
        if (!empty($filter['prodi_id'])) {
            $this->db->where('m.prodi_id', $filter['prodi_id']);
        }
        
        if (!empty($filter['dosen_pembimbing_id'])) {
            $this->db->where('pta.dosen_pembimbing_id', $filter['dosen_pembimbing_id']);
        }
        
        if (!empty($filter['periode_tahun'])) {
            $this->db->where('YEAR(pta.tanggal_pengajuan)', $filter['periode_tahun']);
        }
        
        if (!empty($filter['periode_bulan'])) {
            $this->db->where('MONTH(pta.tanggal_pengajuan)', $filter['periode_bulan']);
        }
    }

    /**
     * Get bottleneck analysis
     * 
     * @param array $filter Filter conditions
     * @return array Bottleneck data
     */
    private function _get_bottleneck_analysis($filter = []) {
        $sql = "
            SELECT 
                'dosen_review' as bottleneck_type,
                COUNT(*) as count,
                AVG(DATEDIFF(NOW(), pta.tanggal_pengajuan)) as avg_days_pending
            FROM publikasi_tugas_akhir pta
            JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE pta.status_pembimbing = 'pending'
            AND pta.tanggal_pengajuan < DATE_SUB(NOW(), INTERVAL 3 DAY)
        ";
        
        if (!empty($filter['prodi_id'])) {
            $sql .= " AND m.prodi_id = " . intval($filter['prodi_id']);
        }
        
        $sql .= "
            UNION ALL
            SELECT 
                'staf_validation' as bottleneck_type,
                COUNT(*) as count,
                AVG(DATEDIFF(NOW(), pta.tanggal_review_pembimbing)) as avg_days_pending
            FROM publikasi_tugas_akhir pta
            JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE pta.status_pembimbing = 'approved'
            AND pta.status_staf = 'pending'
            AND pta.tanggal_review_pembimbing < DATE_SUB(NOW(), INTERVAL 2 DAY)
        ";
        
        if (!empty($filter['prodi_id'])) {
            $sql .= " AND m.prodi_id = " . intval($filter['prodi_id']);
        }
        
        return $this->db->query($sql)->result();
    }

    /**
     * Get performance trends
     * 
     * @param array $filter Filter conditions
     * @return array Trend data
     */
    private function _get_performance_trends($filter = []) {
        $periode = !empty($filter['periode']) ? $filter['periode'] : date('Y');
        
        $sql = "
            SELECT 
                MONTH(pta.tanggal_pengajuan) as bulan,
                COUNT(*) as total_pengajuan,
                SUM(CASE WHEN pta.status = 'completed' THEN 1 ELSE 0 END) as completed,
                AVG(CASE WHEN pta.status = 'completed' 
                    THEN DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) 
                    ELSE NULL END) as avg_process_days
            FROM publikasi_tugas_akhir pta
            JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE YEAR(pta.tanggal_pengajuan) = ?
        ";
        
        $params = [$periode];
        
        if (!empty($filter['prodi_id'])) {
            $sql .= " AND m.prodi_id = ?";
            $params[] = $filter['prodi_id'];
        }
        
        $sql .= "
            GROUP BY MONTH(pta.tanggal_pengajuan)
            ORDER BY bulan ASC
        ";
        
        return $this->db->query($sql, $params)->result();
    }

    /**
     * Get rejection analysis
     * 
     * @param array $filter Filter conditions
     * @return array Rejection data
     */
    private function _get_rejection_analysis($filter = []) {
        $sql = "
            SELECT 
                pta.status_pembimbing,
                COUNT(*) as count,
                GROUP_CONCAT(DISTINCT SUBSTRING(pta.komentar_pembimbing, 1, 100) SEPARATOR '; ') as sample_reasons
            FROM publikasi_tugas_akhir pta
            JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE pta.status = 'rejected'
        ";
        
        $params = [];
        
        if (!empty($filter['prodi_id'])) {
            $sql .= " AND m.prodi_id = ?";
            $params[] = $filter['prodi_id'];
        }
        
        if (!empty($filter['periode'])) {
            $sql .= " AND YEAR(pta.tanggal_pengajuan) = ?";
            $params[] = $filter['periode'];
        }
        
        $sql .= " GROUP BY pta.status_pembimbing";
        
        return $this->db->query($sql, $params)->result();
    }

    /**
     * Get dosen performance analysis
     * 
     * @param array $filter Filter conditions
     * @return array Dosen performance data
     */
    private function _get_dosen_performance($filter = []) {
        $sql = "
            SELECT 
                d.id,
                d.nama as nama_dosen,
                COUNT(pta.id) as total_mahasiswa,
                SUM(CASE WHEN pta.status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN pta.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                AVG(CASE WHEN pta.status = 'completed' 
                    THEN DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan) 
                    ELSE NULL END) as avg_process_days,
                AVG(CASE WHEN pta.status_pembimbing != 'pending'
                    THEN DATEDIFF(pta.tanggal_review_pembimbing, pta.tanggal_pengajuan)
                    ELSE NULL END) as avg_review_days
            FROM dosen d
            LEFT JOIN publikasi_tugas_akhir pta ON d.id = pta.dosen_pembimbing_id
            LEFT JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE d.level IN ('2', '4')
        ";
        
        $params = [];
        
        if (!empty($filter['prodi_id'])) {
            $sql .= " AND d.prodi_id = ?";
            $params[] = $filter['prodi_id'];
        }
        
        if (!empty($filter['periode'])) {
            $sql .= " AND YEAR(pta.tanggal_pengajuan) = ?";
            $params[] = $filter['periode'];
        }
        
        $sql .= "
            GROUP BY d.id
            HAVING total_mahasiswa > 0
            ORDER BY completed DESC, avg_process_days ASC
        ";
        
        return $this->db->query($sql, $params)->result();
    }

    /**
     * Get monthly completion data
     * 
     * @param array $filter Filter conditions
     * @return array Monthly data
     */
    private function _get_monthly_completion($filter = []) {
        $periode = !empty($filter['periode']) ? $filter['periode'] : date('Y');
        
        $sql = "
            SELECT 
                DATE_FORMAT(pta.tanggal_selesai, '%Y-%m') as bulan,
                COUNT(*) as total_selesai,
                AVG(DATEDIFF(pta.tanggal_selesai, pta.tanggal_pengajuan)) as avg_process_days
            FROM publikasi_tugas_akhir pta
            JOIN mahasiswa m ON pta.mahasiswa_id = m.id
            WHERE pta.status = 'completed'
            AND YEAR(pta.tanggal_selesai) = ?
        ";
        
        $params = [$periode];
        
        if (!empty($filter['prodi_id'])) {
            $sql .= " AND m.prodi_id = ?";
            $params[] = $filter['prodi_id'];
        }
        
        $sql .= "
            GROUP BY DATE_FORMAT(pta.tanggal_selesai, '%Y-%m')
            ORDER BY bulan ASC
        ";
        
        return $this->db->query($sql, $params)->result();
    }

    /**
     * Log aktivitas publikasi
     * 
     * @param int $publikasi_id ID publikasi
     * @param int $user_id ID user
     * @param string $user_role Role user
     * @param string $aktivitas Jenis aktivitas
     * @param string $deskripsi Deskripsi aktivitas
     */
    private function _log_activity($publikasi_id, $user_id, $user_role, $aktivitas, $deskripsi) {
        // Get user name based on role
        $user_name = $this->_get_user_name($user_id, $user_role);
        
        $log_data = [
            'publikasi_id' => $publikasi_id,
            'user_id' => $user_id,
            'user_role' => $user_role,
            'user_name' => $user_name,
            'aktivitas' => $aktivitas,
            'deskripsi' => $deskripsi,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert($this->log_table, $log_data);
    }

    /**
     * Get user name by ID and role
     * 
     * @param int $user_id ID user
     * @param string $user_role Role user
     * @return string Nama user
     */
    private function _get_user_name($user_id, $user_role) {
        switch ($user_role) {
            case 'mahasiswa':
                $result = $this->db->select('nama')->where('id', $user_id)->get('mahasiswa')->row();
                return $result ? $result->nama : 'Unknown Mahasiswa';
                
            case 'dosen':
            case 'kaprodi':
            case 'staf':
            case 'admin':
                $result = $this->db->select('nama')->where('id', $user_id)->get('dosen')->row();
                return $result ? $result->nama : 'Unknown ' . ucfirst($user_role);
                
            default:
                return 'Unknown User';
        }
    }

    // =================================================================
    // DELETE OPERATIONS (FOR ADMIN/EMERGENCY USE)
    // =================================================================

    /**
     * Delete publikasi (untuk admin atau emergency)
     * 
     * @param int $publikasi_id ID publikasi
     * @param int $admin_id ID admin yang menghapus
     * @return array Result
     */
    public function delete($publikasi_id, $admin_id) {
        try {
            $this->db->trans_start();
            
            // Get data untuk log
            $publikasi = $this->get_by_id($publikasi_id);
            if (!$publikasi) {
                return [
                    'success' => false,
                    'message' => 'Data publikasi tidak ditemukan.'
                ];
            }
            
            // Log sebelum delete
            $this->_log_activity($publikasi_id, $admin_id, 'admin', 'delete_publikasi', 'Admin menghapus data publikasi: ' . $publikasi->nama_mahasiswa);
            
            // Delete files jika ada
            $this->_delete_files($publikasi);
            
            // Delete log activities
            $this->db->where('publikasi_id', $publikasi_id)->delete($this->log_table);
            
            // Delete publikasi
            $this->db->where('id', $publikasi_id)->delete($this->table);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus data publikasi.'
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Data publikasi berhasil dihapus.'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error deleting publikasi: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ];
        }
    }

    /**
     * Delete uploaded files
     * 
     * @param object $publikasi Data publikasi
     */
    private function _delete_files($publikasi) {
        $files_to_delete = [
            $publikasi->file_surat_revisi,
            $publikasi->file_skripsi_final,
            $publikasi->file_surat_perpustakaan
        ];
        
        $upload_paths = [
            './uploads/publikasi/surat_revisi/',
            './uploads/publikasi/skripsi_final/',
            './uploads/publikasi/surat_perpustakaan/'
        ];
        
        foreach ($files_to_delete as $index => $filename) {
            if (!empty($filename)) {
                $file_path = $upload_paths[$index] . $filename;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Get available statuses
     * 
     * @return array Available statuses
     */
    public function get_available_statuses() {
        return [
            'draft' => 'Draft - Belum disubmit',
            'submitted' => 'Disubmit - Menunggu review dosen',
            'review_pembimbing' => 'Sedang direview dosen pembimbing',
            'review_staf' => 'Menunggu validasi staf',
            'completed' => 'Selesai - Publikasi berhasil',
            'rejected' => 'Ditolak - Perlu revisi'
        ];
    }

    // =================================================================
    // VALIDATION & UTILITY METHODS
    // =================================================================

    /**
     * Validate publikasi data before save
     * 
     * @param array $data Data untuk divalidasi
     * @param string $mode Mode validasi (create/update)
     * @return array Validation result
     */
    public function validate_data($data, $mode = 'create') {
        $errors = [];
        
        // Required fields validation
        $required_fields = [
            'mahasiswa_id' => 'ID Mahasiswa',
            'proposal_mahasiswa_id' => 'ID Proposal',
            'nama_mahasiswa' => 'Nama Mahasiswa',
            'nim' => 'NIM',
            'program_studi' => 'Program Studi',
            'judul_skripsi_final' => 'Judul Skripsi Final',
            'dosen_pembimbing_id' => 'Dosen Pembimbing',
            'tanggal_ujian_skripsi' => 'Tanggal Ujian Skripsi'
        ];
        
        foreach ($required_fields as $field => $label) {
            if (empty($data[$field])) {
                $errors[] = "{$label} tidak boleh kosong.";
            }
        }
        
        // File validation untuk create mode
        if ($mode === 'create') {
            $required_files = [
                'file_surat_revisi' => 'File Surat Revisi',
                'file_skripsi_final' => 'File Skripsi Final',
                'file_surat_perpustakaan' => 'File Surat Perpustakaan'
            ];
            
            foreach ($required_files as $field => $label) {
                if (empty($data[$field])) {
                    $errors[] = "{$label} harus diupload.";
                }
            }
        }
        
        // Date validation
        if (!empty($data['tanggal_ujian_skripsi'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['tanggal_ujian_skripsi']);
            if (!$date || $date->format('Y-m-d') !== $data['tanggal_ujian_skripsi']) {
                $errors[] = "Format tanggal ujian skripsi tidak valid.";
            }
        }
        
        // Unique validation untuk create mode
        if ($mode === 'create' && !empty($data['proposal_mahasiswa_id'])) {
            $existing = $this->get_by_proposal($data['proposal_mahasiswa_id']);
            if ($existing) {
                $errors[] = "Publikasi untuk proposal ini sudah ada.";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Cleanup orphaned files
     * 
     * @return array Cleanup result
     */
    public function cleanup_orphaned_files() {
        $cleaned_files = [];
        $upload_dirs = [
            'surat_revisi' => './uploads/publikasi/surat_revisi/',
            'skripsi_final' => './uploads/publikasi/skripsi_final/',
            'surat_perpustakaan' => './uploads/publikasi/surat_perpustakaan/'
        ];
        
        foreach ($upload_dirs as $type => $dir) {
            if (is_dir($dir)) {
                $files = glob($dir . '*.*');
                foreach ($files as $file) {
                    $filename = basename($file);
                    
                    // Check if file exists in database
                    $field_name = 'file_' . $type;
                    $exists = $this->db->where($field_name, $filename)
                                     ->count_all_results($this->table);
                    
                    if ($exists === 0) {
                        if (unlink($file)) {
                            $cleaned_files[] = $filename;
                        }
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'cleaned_files' => $cleaned_files,
            'total_cleaned' => count($cleaned_files)
        ];
    }

    /**
     * Get system health status
     * 
     * @return array Health status
     */
    public function get_system_health() {
        $health = [
            'database_status' => 'OK',
            'file_storage_status' => 'OK',
            'pending_too_long' => 0,
            'error_rate' => 0,
            'storage_usage_mb' => 0
        ];
        
        try {
            // Check database connectivity
            $this->db->query("SELECT 1");
            
            // Count pending too long
            $health['pending_too_long'] = $this->db
                ->where('status_pembimbing', 'pending')
                ->where('tanggal_pengajuan <', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->count_all_results($this->table);
            
            // Calculate error rate (rejected / total submitted)
            $total_submitted = $this->db->where('status !=', 'draft')->count_all_results($this->table);
            $total_rejected = $this->db->where('status', 'rejected')->count_all_results($this->table);
            $health['error_rate'] = $total_submitted > 0 ? round(($total_rejected / $total_submitted) * 100, 1) : 0;
            
            // Storage usage
            $health['storage_usage_mb'] = $this->_calculate_storage_usage();
            
        } catch (Exception $e) {
            $health['database_status'] = 'ERROR';
            $health['error_message'] = $e->getMessage();
        }
        
        return $health;
    }

    /**
     * Get publikasi summary for dashboard widgets
     * 
     * @param int $user_id ID user
     * @param string $user_role Role user
     * @return array Summary data
     */
    public function get_dashboard_summary($user_id, $user_role) {
        $summary = [];
        
        switch ($user_role) {
            case 'mahasiswa':
                $summary = $this->_get_mahasiswa_summary($user_id);
                break;
            case 'dosen':
                $summary = $this->_get_dosen_summary($user_id);
                break;
            case 'staf':
                $summary = $this->_get_staf_summary();
                break;
            case 'kaprodi':
                $prodi_id = $this->_get_kaprodi_prodi_id($user_id);
                $summary = $this->_get_kaprodi_summary($prodi_id);
                break;
            case 'admin':
                $summary = $this->_get_admin_summary();
                break;
        }
        
        return $summary;
    }

    /**
     * Generate notification data untuk users
     * 
     * @param int $user_id ID user
     * @param string $user_role Role user
     * @return array Notification data
     */
    public function get_notifications($user_id, $user_role) {
        $notifications = [];
        
        switch ($user_role) {
            case 'mahasiswa':
                // Notifikasi untuk mahasiswa
                $this->db->select('*');
                $this->db->from($this->table);
                $this->db->where('mahasiswa_id', $user_id);
                $this->db->where_in('status', ['review_pembimbing', 'review_staf', 'completed', 'rejected']);
                $this->db->where('updated_at >', date('Y-m-d H:i:s', strtotime('-7 days')));
                $results = $this->db->get()->result();
                
                foreach ($results as $pub) {
                    $notifications[] = [
                        'type' => 'publikasi_update',
                        'title' => 'Update Status Publikasi',
                        'message' => "Publikasi Anda berstatus: " . ucfirst($pub->status),
                        'date' => $pub->updated_at,
                        'url' => base_url('mahasiswa/publikasi')
                    ];
                }
                break;
                
            case 'dosen':
                // Notifikasi untuk dosen
                $pending = $this->get_pending_dosen_review($user_id);
                foreach ($pending as $pub) {
                    $notifications[] = [
                        'type' => 'review_required',
                        'title' => 'Review Publikasi Diperlukan',
                        'message' => "Publikasi dari {$pub->nama_mahasiswa} perlu direview",
                        'date' => $pub->tanggal_pengajuan,
                        'url' => base_url('dosen/publikasi/review/' . $pub->id)
                    ];
                }
                break;
                
            case 'staf':
                // Notifikasi untuk staf
                $pending = $this->get_pending_staf_validation();
                foreach ($pending as $pub) {
                    $notifications[] = [
                        'type' => 'validation_required',
                        'title' => 'Validasi Publikasi Diperlukan',
                        'message' => "Publikasi dari {$pub->nama_mahasiswa} perlu divalidasi",
                        'date' => $pub->tanggal_review_pembimbing,
                        'url' => base_url('staf/publikasi/validasi/' . $pub->id)
                    ];
                }
                break;
        }
        
        return $notifications;
    }

    /**
     * Archive old completed publikasi
     * 
     * @param int $days_old Archive publikasi yang sudah selesai X hari
     * @return array Archive result
     */
    public function archive_old_publications($days_old = 365) {
        $cutoff_date = date('Y-m-d', strtotime("-{$days_old} days"));
        
        // Create archive table if not exists
        $this->_create_archive_table();
        
        // Get publikasi to archive
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('status', 'completed');
        $this->db->where('tanggal_selesai <', $cutoff_date);
        $to_archive = $this->db->get()->result_array();
        
        $archived_count = 0;
        
        if (!empty($to_archive)) {
            $this->db->trans_start();
            
            // Insert ke archive table
            foreach ($to_archive as $record) {
                $record['archived_at'] = date('Y-m-d H:i:s');
                $this->db->insert('publikasi_tugas_akhir_archive', $record);
            }
            
            // Delete from main table
            $this->db->where('status', 'completed');
            $this->db->where('tanggal_selesai <', $cutoff_date);
            $this->db->delete($this->table);
            
            $archived_count = $this->db->affected_rows();
            
            $this->db->trans_complete();
        }
        
        return [
            'success' => $this->db->trans_status() !== FALSE,
            'archived_count' => $archived_count,
            'cutoff_date' => $cutoff_date
        ];
    }

    // =================================================================
    // PRIVATE HELPER METHODS FOR DASHBOARD SUMMARIES
    // =================================================================

    /**
     * Get mahasiswa dashboard summary
     */
    private function _get_mahasiswa_summary($mahasiswa_id) {
        $proposal = $this->db->select('id')
                           ->where('mahasiswa_id', $mahasiswa_id)
                           ->where('status', '1')
                           ->get('proposal_mahasiswa')
                           ->row();
        
        if (!$proposal) {
            return ['eligible' => false, 'message' => 'Belum ada proposal yang disetujui'];
        }
        
        $jurnal_count = $this->db->where('proposal_id', $proposal->id)
                               ->where('status_validasi', '1')
                               ->count_all_results('jurnal_bimbingan');
        
        $publikasi = $this->get_by_proposal($proposal->id);
        
        return [
            'eligible' => $jurnal_count >= 16,
            'jurnal_count' => $jurnal_count,
            'publikasi_status' => $publikasi ? $publikasi->status : null,
            'can_submit' => $jurnal_count >= 16 && !$publikasi
        ];
    }

    /**
     * Get dosen dashboard summary
     */
    private function _get_dosen_summary($dosen_id) {
        return [
            'pending_review' => count($this->get_pending_dosen_review($dosen_id)),
            'total_bimbingan' => $this->db->where('dosen_pembimbing_id', $dosen_id)->count_all_results($this->table),
            'completed_this_month' => $this->db->where('dosen_pembimbing_id', $dosen_id)
                                              ->where('status', 'completed')
                                              ->where('MONTH(tanggal_selesai)', date('n'))
                                              ->count_all_results($this->table)
        ];
    }

    /**
     * Get staf dashboard summary
     */
    private function _get_staf_summary() {
        return [
            'pending_validation' => count($this->get_pending_staf_validation()),
            'completed_today' => $this->db->where('status', 'completed')
                                         ->where('DATE(tanggal_selesai)', date('Y-m-d'))
                                         ->count_all_results($this->table),
            'total_this_month' => $this->db->where('status', 'completed')
                                          ->where('MONTH(tanggal_selesai)', date('n'))
                                          ->count_all_results($this->table)
        ];
    }

    /**
     * Get kaprodi dashboard summary
     */
    private function _get_kaprodi_summary($prodi_id) {
        $this->db->join('mahasiswa m', 'publikasi_tugas_akhir.mahasiswa_id = m.id');
        return [
            'total_prodi' => $this->db->where('m.prodi_id', $prodi_id)->count_all_results($this->table),
            'completed_this_month' => $this->db->where('m.prodi_id', $prodi_id)
                                              ->where('publikasi_tugas_akhir.status', 'completed')
                                              ->where('MONTH(publikasi_tugas_akhir.tanggal_selesai)', date('n'))
                                              ->count_all_results($this->table),
            'pending_total' => $this->db->where('m.prodi_id', $prodi_id)
                                       ->where_in('publikasi_tugas_akhir.status', ['submitted', 'review_pembimbing', 'review_staf'])
                                       ->count_all_results($this->table)
        ];
    }

    /**
     * Get admin dashboard summary
     */
    private function _get_admin_summary() {
        return [
            'total_system' => $this->db->count_all_results($this->table),
            'pending_all' => $this->db->where_in('status', ['submitted', 'review_pembimbing', 'review_staf'])
                                     ->count_all_results($this->table),
            'completed_today' => $this->db->where('status', 'completed')
                                         ->where('DATE(tanggal_selesai)', date('Y-m-d'))
                                         ->count_all_results($this->table),
            'system_health' => $this->get_system_health()
        ];
    }

    /**
     * Calculate storage usage
     */
    private function _calculate_storage_usage() {
        $upload_dir = './uploads/publikasi/';
        $size = 0;
        
        if (is_dir($upload_dir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($upload_dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        
        return round($size / (1024 * 1024), 2); // Convert to MB
    }

    /**
     * Create archive table
     */
    private function _create_archive_table() {
        $sql = "
            CREATE TABLE IF NOT EXISTS `publikasi_tugas_akhir_archive` (
                `id` bigint(20) NOT NULL,
                `proposal_mahasiswa_id` bigint(20) NOT NULL,
                `mahasiswa_id` bigint(20) NOT NULL,
                `nama_mahasiswa` varchar(100) NOT NULL,
                `nim` varchar(50) NOT NULL,
                `program_studi` enum('Pendidikan Keagamaan Katolik','Pendidikan Guru Sekolah Dasar') NOT NULL,
                `judul_skripsi_final` text NOT NULL,
                `dosen_pembimbing_id` bigint(20) NOT NULL,
                `nama_dosen_pembimbing` varchar(100) NOT NULL,
                `tanggal_ujian_skripsi` date NOT NULL,
                `file_surat_revisi` varchar(255) NOT NULL,
                `file_skripsi_final` varchar(255) NOT NULL,
                `file_surat_perpustakaan` varchar(255) NOT NULL,
                `link_repository` varchar(500) DEFAULT NULL,
                `status` enum('draft','submitted','review_pembimbing','review_staf','completed','rejected') DEFAULT 'draft',
                `status_pembimbing` enum('pending','approved','rejected') DEFAULT 'pending',
                `status_staf` enum('pending','approved','rejected') DEFAULT 'pending',
                `keterangan_mahasiswa` text DEFAULT NULL,
                `komentar_pembimbing` text DEFAULT NULL,
                `komentar_staf` text DEFAULT NULL,
                `tanggal_pengajuan` datetime DEFAULT NULL,
                `tanggal_review_pembimbing` datetime DEFAULT NULL,
                `tanggal_validasi_staf` datetime DEFAULT NULL,
                `tanggal_selesai` datetime DEFAULT NULL,
                `validated_by_staf_id` bigint(20) DEFAULT NULL,
                `validated_by_staf_name` varchar(100) DEFAULT NULL,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `archived_at` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_archived_date` (`archived_at`),
                KEY `idx_mahasiswa_archive` (`mahasiswa_id`),
                KEY `idx_prodi_archive` (`program_studi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Archive table untuk publikasi yang sudah lama selesai'
        ";
        
        $this->db->query($sql);
    }

    /**
     * Get workflow steps
     * 
     * @return array Workflow steps
     */
    public function get_workflow_steps() {
        return [
            [
                'step' => 'mahasiswa',
                'title' => 'Pengajuan Mahasiswa',
                'description' => 'Mahasiswa mengisi form dan upload dokumen',
                'icon' => 'fa-user-graduate',
                'color' => 'primary'
            ],
            [
                'step' => 'pembimbing',
                'title' => 'Review Dosen Pembimbing',
                'description' => 'Dosen pembimbing memberikan persetujuan',
                'icon' => 'fa-chalkboard-teacher',
                'color' => 'info'
            ],
            [
                'step' => 'staf',
                'title' => 'Validasi Staf',
                'description' => 'Staf input repository link dan validasi final',
                'icon' => 'fa-user-tie',
                'color' => 'warning'
            ],
            [
                'step' => 'selesai',
                'title' => 'Publikasi Selesai',
                'description' => 'Mahasiswa dapat download surat keterangan',
                'icon' => 'fa-check-circle',
                'color' => 'success'
            ]
        ];
    }

    /**
     * Get progress percentage untuk publikasi
     * 
     * @param string $status Current status
     * @return int Progress percentage (0-100)
     */
    public function get_progress_percentage($status) {
        $progress_map = [
            'draft' => 20,
            'submitted' => 40,
            'review_pembimbing' => 60,
            'review_staf' => 80,
            'completed' => 100,
            'rejected' => 0
        ];
        
        return isset($progress_map[$status]) ? $progress_map[$status] : 0;
    }

    /**
     * Get status badge class untuk UI
     * 
     * @param string $status Current status
     * @return string CSS class
     */
    public function get_status_badge_class($status) {
        $badge_map = [
            'draft' => 'badge-secondary',
            'submitted' => 'badge-primary',
            'review_pembimbing' => 'badge-info',
            'review_staf' => 'badge-warning',
            'completed' => 'badge-success',
            'rejected' => 'badge-danger'
        ];
        
        return isset($badge_map[$status]) ? $badge_map[$status] : 'badge-secondary';
    }

    /**
     * Get next action untuk user berdasarkan status
     * 
     * @param object $publikasi Data publikasi
     * @param string $user_role Role user
     * @param int $user_id ID user
     * @return array Next action info
     */
    public function get_next_action($publikasi, $user_role, $user_id) {
        $action = [
            'can_act' => false,
            'action_type' => null,
            'action_text' => null,
            'action_url' => null,
            'action_class' => 'btn-secondary'
        ];
        
        switch ($publikasi->status) {
            case 'draft':
                if ($user_role === 'mahasiswa' && $publikasi->mahasiswa_id == $user_id) {
                    $action = [
                        'can_act' => true,
                        'action_type' => 'submit',
                        'action_text' => 'Submit Pengajuan',
                        'action_url' => base_url('mahasiswa/publikasi/submit/' . $publikasi->id),
                        'action_class' => 'btn-primary'
                    ];
                }
                break;
                
            case 'submitted':
            case 'review_pembimbing':
                if ($user_role === 'dosen' && $publikasi->dosen_pembimbing_id == $user_id && $publikasi->status_pembimbing === 'pending') {
                    $action = [
                        'can_act' => true,
                        'action_type' => 'review',
                        'action_text' => 'Review Publikasi',
                        'action_url' => base_url('dosen/publikasi/review/' . $publikasi->id),
                        'action_class' => 'btn-info'
                    ];
                }
                break;
                
            case 'review_staf':
                if ($user_role === 'staf' && $publikasi->status_pembimbing === 'approved' && $publikasi->status_staf === 'pending') {
                    $action = [
                        'can_act' => true,
                        'action_type' => 'validate',
                        'action_text' => 'Validasi Publikasi',
                        'action_url' => base_url('staf/publikasi/validasi/' . $publikasi->id),
                        'action_class' => 'btn-warning'
                    ];
                }
                break;
                
            case 'completed':
                if ($user_role === 'mahasiswa' && $publikasi->mahasiswa_id == $user_id) {
                    $action = [
                        'can_act' => true,
                        'action_type' => 'download',
                        'action_text' => 'Download Surat',
                        'action_url' => base_url('mahasiswa/publikasi/download_surat/' . $publikasi->id),
                        'action_class' => 'btn-success'
                    ];
                }
                break;
                
            case 'rejected':
                if ($user_role === 'mahasiswa' && $publikasi->mahasiswa_id == $user_id) {
                    $action = [
                        'can_act' => true,
                        'action_type' => 'edit',
                        'action_text' => 'Perbaiki Pengajuan',
                        'action_url' => base_url('mahasiswa/publikasi/edit/' . $publikasi->id),
                        'action_class' => 'btn-warning'
                    ];
                }
                break;
        }
        
        return $action;
    }

    /**
     * Get file download URL yang aman
     * 
     * @param object $publikasi Data publikasi
     * @param string $file_type Type file (surat_revisi, skripsi_final, surat_perpustakaan)
     * @param string $user_role Role user yang request
     * @param int $user_id ID user yang request
     * @return string|null Download URL atau null jika tidak ada akses
     */
    public function get_secure_download_url($publikasi, $file_type, $user_role, $user_id) {
        // Check access permission
        $has_access = false;
        
        switch ($user_role) {
            case 'mahasiswa':
                $has_access = ($publikasi->mahasiswa_id == $user_id);
                break;
            case 'dosen':
                $has_access = ($publikasi->dosen_pembimbing_id == $user_id);
                break;
            case 'staf':
            case 'admin':
                $has_access = true;
                break;
            case 'kaprodi':
                // Check if same prodi
                $mahasiswa = $this->db->select('prodi_id')->where('id', $publikasi->mahasiswa_id)->get('mahasiswa')->row();
                $kaprodi = $this->db->select('prodi_id')->where('id', $user_id)->get('dosen')->row();
                $has_access = ($mahasiswa && $kaprodi && $mahasiswa->prodi_id == $kaprodi->prodi_id);
                break;
        }
        
        if (!$has_access) {
            return null;
        }
        
        // Generate secure download URL
        $controller_map = [
            'mahasiswa' => 'mahasiswa/publikasi',
            'dosen' => 'dosen/publikasi',
            'staf' => 'staf/publikasi',
            'kaprodi' => 'kaprodi/publikasi',
            'admin' => 'admin/publikasi'
        ];
        
        $controller = $controller_map[$user_role];
        return base_url("{$controller}/download/{$publikasi->id}/{$file_type}");
    }

    /**
     * Calculate estimated completion time
     * 
     * @param object $publikasi Data publikasi
     * @return array Estimation data
     */
    public function calculate_estimated_completion($publikasi) {
        // Average process time per status
        $avg_times = [
            'submitted' => 3, // days
            'review_pembimbing' => 2, // days  
            'review_staf' => 1 // days
        ];
        
        $current_status = $publikasi->status;
        $estimated_days = 0;
        
        // Calculate remaining days based on current status
        switch ($current_status) {
            case 'draft':
                $estimated_days = array_sum($avg_times);
                break;
            case 'submitted':
                $estimated_days = $avg_times['review_pembimbing'] + $avg_times['review_staf'];
                break;
            case 'review_pembimbing':
                $estimated_days = $avg_times['review_staf'];
                break;
            case 'review_staf':
                $estimated_days = 1;
                break;
            case 'completed':
                $estimated_days = 0;
                break;
            case 'rejected':
                $estimated_days = array_sum($avg_times); // Start over
                break;
        }
        
        $estimated_date = date('Y-m-d', strtotime("+{$estimated_days} days"));
        
        return [
            'estimated_days' => $estimated_days,
            'estimated_date' => $estimated_date,
            'estimated_date_formatted' => date('d F Y', strtotime($estimated_date))
        ];
    }

    /**
     * Generate publication certificate data
     * 
     * @param int $publikasi_id ID publikasi
     * @return array Certificate data
     */
    public function get_certificate_data($publikasi_id) {
        $this->db->select('
            pta.*,
            m.nim, m.nama as nama_mahasiswa, m.tempat_lahir, m.tanggal_lahir,
            pr.nama as nama_prodi,
            d.nama as nama_pembimbing, d.nip as nip_pembimbing,
            pm.judul as judul_proposal
        ');
        $this->db->from($this->table . ' pta');
        $this->db->join('mahasiswa m', 'pta.mahasiswa_id = m.id');
        $this->db->join('prodi pr', 'm.prodi_id = pr.id');
        $this->db->join('dosen d', 'pta.dosen_pembimbing_id = d.id');
        $this->db->join('proposal_mahasiswa pm', 'pta.proposal_mahasiswa_id = pm.id');
        $this->db->where('pta.id', $publikasi_id);
        $this->db->where('pta.status', 'completed');
        
        $publikasi = $this->db->get()->row();
        
        if (!$publikasi) {
            return null;
        }
        
        // Generate certificate number
        $cert_number = $this->_generate_certificate_number($publikasi);
        
        return [
            'publikasi' => $publikasi,
            'certificate_number' => $cert_number,
            'issue_date' => date('d F Y', strtotime($publikasi->tanggal_selesai)),
            'qr_code_data' => base_url('verify/' . $cert_number),
            'kampus_info' => [
                'nama' => 'Sekolah Tinggi Katolik Santo Yakobus Merauke',
                'alamat' => 'Jl. Missi 2, Mandala, Merauke, Papua Selatan',
                'telepon' => '(0971) 333 0264',
                'website' => 'www.stkyakobus.ac.id'
            ]
        ];
    }

    /**
     * Verify certificate by number
     * 
     * @param string $cert_number Certificate number
     * @return array|null Verification result
     */
    public function verify_certificate($cert_number) {
        // Extract publikasi ID from certificate number
        $publikasi_id = $this->_extract_id_from_cert_number($cert_number);
        
        if (!$publikasi_id) {
            return null;
        }
        
        return $this->get_certificate_data($publikasi_id);
    }

    /**
     * Generate certificate number
     * 
     * @param object $publikasi Data publikasi
     * @return string Certificate number
     */
    private function _generate_certificate_number($publikasi) {
        $year = date('Y', strtotime($publikasi->tanggal_selesai));
        $month = date('m', strtotime($publikasi->tanggal_selesai));
        $prodi_code = ($publikasi->program_studi === 'Pendidikan Keagamaan Katolik') ? 'PKK' : 'PGSD';
        
        return sprintf('PUB/%s/%s/%04d/%03d', $prodi_code, $year, $month, $publikasi->id);
    }

    /**
     * Extract ID from certificate number
     * 
     * @param string $cert_number Certificate number
     * @return int|null Publikasi ID
     */
    private function _extract_id_from_cert_number($cert_number) {
        // Format: PUB/PKK/2025/08/001
        $parts = explode('/', $cert_number);
        
        if (count($parts) === 5 && $parts[0] === 'PUB') {
            return intval($parts[4]);
        }
        
        return null;
    }
}