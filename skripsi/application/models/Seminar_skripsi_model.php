<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Model - Universal untuk Semua Role (Phase 5)
 * 
 * Model universal yang mengikuti pattern Seminar_proposal_mahasiswa_model.php
 * Dapat digunakan oleh semua role: mahasiswa, dosen, kaprodi, staf
 * 100% kompatibel dengan database existing
 * 
 * File: application/models/Seminar_skripsi_model.php
 * 
 * @package     SIM_TA
 * @subpackage  Models
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Universal Simple Model)
 */
class Seminar_skripsi_model extends CI_Model {

    // Table names sesuai database existing
    protected $table = 'seminar_skripsi_mahasiswa';
    protected $view = 'seminar_skripsi_mahasiswa_v';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // =================================================================
    // CORE CRUD METHODS - Pattern dari Seminar_proposal_mahasiswa_model
    // =================================================================

    /**
     * Get seminar skripsi by proposal ID
     * 
     * @param int $proposal_id
     * @return object|null
     */
    public function get_by_proposal_id($proposal_id)
    {
        return $this->db->select('*')
                       ->from($this->view)
                       ->where('proposal_id', $proposal_id)
                       ->get()
                       ->row();
    }

    /**
     * Get seminar skripsi by ID dengan optional security check
     * 
     * @param int $id
     * @param int $mahasiswa_id (optional untuk security check)
     * @return object|null
     */
    public function get_by_id($id, $mahasiswa_id = null)
    {
        $this->db->select('*')
                 ->from($this->view)
                 ->where('id', $id);
        
        if ($mahasiswa_id) {
            $this->db->where('mahasiswa_id', $mahasiswa_id);
        }
        
        return $this->db->get()->row();
    }

    /**
     * Create new seminar skripsi record
     * 
     * @param array $data
     * @return int|false
     */
    public function create($data)
    {
        $this->db->trans_start();
        
        $this->db->insert($this->table, $data);
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $insert_id;
    }

    /**
     * Update seminar skripsi record
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $this->db->trans_start();
        
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status() !== FALSE;
    }

    // =================================================================
    // ROLE-BASED QUERY METHODS
    // =================================================================

    /**
     * Get seminar skripsi untuk mahasiswa
     * 
     * @param int $mahasiswa_id
     * @return array
     */
    public function get_by_mahasiswa($mahasiswa_id)
    {
        $this->db->select('*');
        $this->db->from($this->view);
        $this->db->where('mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get seminar skripsi untuk dosen pembimbing
     * 
     * @param int $dosen_id
     * @param array $status_filter optional
     * @return array
     */
    public function get_by_dosen_pembimbing($dosen_id, $status_filter = [])
    {
        $this->db->select('*');
        $this->db->from($this->view);
        $this->db->where('pembimbing_id', $dosen_id);
        
        if (!empty($status_filter)) {
            $this->db->where_in('status', $status_filter);
        }
        
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get seminar skripsi yang perlu review dosen
     * 
     * @param int $dosen_id
     * @return array
     */
    public function get_perlu_review_dosen($dosen_id)
    {
        $this->db->select('*');
        $this->db->from($this->view);
        $this->db->where('pembimbing_id', $dosen_id);
        $this->db->where_in('status', ['submitted', 'review_pembimbing']);
        $this->db->where('status_pembimbing', 'pending');
        $this->db->order_by('created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get seminar skripsi untuk kaprodi (by prodi)
     * 
     * @param int $prodi_id
     * @param array $status_filter optional
     * @return array
     */
    public function get_by_prodi($prodi_id, $status_filter = [])
    {
        $this->db->select('ssm.*');
        $this->db->from($this->view . ' ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $prodi_id);
        
        if (!empty($status_filter)) {
            $this->db->where_in('ssm.status', $status_filter);
        }
        
        $this->db->order_by('ssm.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get seminar skripsi yang perlu review kaprodi
     * 
     * @param int $prodi_id
     * @return array
     */
    public function get_perlu_review_kaprodi($prodi_id)
    {
        $this->db->select('ssm.*');
        $this->db->from($this->view . ' ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $prodi_id);
        $this->db->where('ssm.status', 'review_kaprodi');
        $this->db->where('ssm.status_kaprodi', 'pending');
        $this->db->order_by('ssm.created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get seminar skripsi untuk staf administrasi
     * 
     * @param array $status_filter optional
     * @return array
     */
    public function get_for_staf($status_filter = [])
    {
        $this->db->select('*');
        $this->db->from($this->view);
        
        if (!empty($status_filter)) {
            $this->db->where_in('status', $status_filter);
        } else {
            // Default: yang sudah scheduled atau completed
            $this->db->where_in('status', ['scheduled', 'completed']);
        }
        
        $this->db->order_by('tanggal_seminar', 'ASC');
        
        return $this->db->get()->result();
    }

    // =================================================================
    // ELIGIBILITY & VALIDATION METHODS - Follow Seminar_proposal pattern
    // =================================================================

    /**
     * Check jurnal bimbingan requirement (minimal 14x untuk seminar skripsi)
     * 
     * @param int $proposal_id
     * @param int $min_required
     * @return array
     */
    public function check_jurnal_requirement($proposal_id, $min_required = 14)
    {
        try {
            // Gunakan database function yang sudah ada jika tersedia
            $sql = "SELECT check_jurnal_requirement_mahasiswa(?) as result";
            $query = $this->db->query($sql, [$proposal_id]);
            $result = $query->row();
            
            if ($result && $result->result) {
                $jurnal_data = json_decode($result->result, true);
                // Override minimum required untuk seminar skripsi
                $jurnal_data['minimum_required'] = $min_required;
                $jurnal_data['eligible'] = $jurnal_data['jurnal_validated_count'] >= $min_required;
                $jurnal_data['missing'] = max(0, $min_required - $jurnal_data['jurnal_validated_count']);
                $jurnal_data['message'] = $jurnal_data['eligible'] ? 
                    'Memenuhi syarat untuk mengajukan seminar skripsi' : 
                    "Perlu " . $jurnal_data['missing'] . " jurnal bimbingan lagi yang divalidasi dosen";
                return $jurnal_data;
            }
        } catch (Exception $e) {
            log_message('debug', 'Database function not available, using fallback: ' . $e->getMessage());
        }
        
        // Fallback manual
        $this->db->select('COUNT(*) as total');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('status_validasi', '1');
        
        $count_result = $this->db->get()->row();
        $count = $count_result ? (int)$count_result->total : 0;
        
        return [
            'eligible' => $count >= $min_required,
            'jurnal_validated_count' => $count,
            'minimum_required' => $min_required,
            'missing' => max(0, $min_required - $count),
            'message' => $count >= $min_required ? 
                'Memenuhi syarat untuk mengajukan seminar skripsi' : 
                "Perlu " . ($min_required - $count) . " jurnal bimbingan lagi yang divalidasi dosen"
        ];
    }

    /**
     * Check eligibility untuk submit seminar skripsi
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function can_submit($proposal_id, $mahasiswa_id)
    {
        $errors = [];
        $requirements = [];
        
        try {
            // 1. Check proposal exists dan workflow status
            $this->db->select('pm.*, m.nim, m.nama as nama_mahasiswa');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $proposal = $this->db->get()->row();
            
            if (!$proposal) {
                $errors[] = 'Proposal tidak ditemukan atau bukan milik Anda';
                return ['eligible' => false, 'errors' => $errors, 'requirements' => $requirements];
            }
            
            // 2. Check workflow status
            $requirements['workflow_status'] = [
                'name' => 'Status Workflow',
                'required' => 'seminar_skripsi',
                'current' => $proposal->workflow_status,
                'met' => $proposal->workflow_status === 'seminar_skripsi'
            ];
            
            if ($proposal->workflow_status !== 'seminar_skripsi') {
                $errors[] = 'Belum menyelesaikan tahap penelitian (status: ' . $proposal->workflow_status . ')';
            }
            
            // 3. Check jurnal bimbingan (14x untuk seminar skripsi)
            $jurnal_req = $this->check_jurnal_requirement($proposal_id, 14);
            $requirements['jurnal_bimbingan'] = [
                'name' => 'Jurnal Bimbingan',
                'required' => 14,
                'current' => $jurnal_req['jurnal_validated_count'],
                'met' => $jurnal_req['eligible']
            ];
            
            if (!$jurnal_req['eligible']) {
                $errors[] = $jurnal_req['message'];
            }
            
            // 4. Check seminar proposal completed
            $this->db->select('status');
            $this->db->from('seminar_proposal_mahasiswa');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status', 'completed');
            $seminar_proposal = $this->db->get()->row();
            
            $requirements['seminar_proposal'] = [
                'name' => 'Seminar Proposal',
                'required' => 'completed',
                'current' => $seminar_proposal ? 'completed' : 'not completed',
                'met' => $seminar_proposal ? true : false
            ];
            
            if (!$seminar_proposal) {
                $errors[] = 'Belum menyelesaikan seminar proposal';
            }
            
            // 5. Check surat izin penelitian
            $this->db->select('COUNT(*) as count');
            $this->db->from('penelitian');
            $this->db->where('proposal_mahasiswa_id', $proposal_id);
            $this->db->where('persetujuan_pembimbing', '1');
            $penelitian_count = $this->db->get()->row()->count;
            
            $requirements['surat_penelitian'] = [
                'name' => 'Surat Izin Penelitian',
                'required' => 1,
                'current' => $penelitian_count,
                'met' => $penelitian_count >= 1
            ];
            
            if ($penelitian_count < 1) {
                $errors[] = 'Belum ada surat izin penelitian yang disetujui';
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error checking seminar skripsi eligibility: ' . $e->getMessage());
            $errors[] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
        
        return [
            'eligible' => empty($errors),
            'errors' => $errors,
            'requirements' => $requirements
        ];
    }

    // =================================================================
    // STATISTICS & REPORTING METHODS
    // =================================================================

    /**
     * Get statistics untuk dashboard (dapat difilter by role)
     * 
     * @param array $filters optional filters
     * @return array
     */
    public function get_statistics($filters = [])
    {
        $stats = [
            'total' => 0,
            'draft' => 0,
            'submitted' => 0,
            'review_pembimbing' => 0,
            'review_kaprodi' => 0,
            'approved' => 0,
            'scheduled' => 0,
            'completed' => 0,
            'rejected' => 0
        ];
        
        $this->db->select('status, COUNT(*) as count');
        $this->db->from($this->table);
        
        // Apply filters if provided
        if (isset($filters['mahasiswa_id'])) {
            $this->db->where('mahasiswa_id', $filters['mahasiswa_id']);
        }
        
        if (isset($filters['dosen_id'])) {
            $this->db->join('proposal_mahasiswa pm', $this->table . '.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $filters['dosen_id']);
        }
        
        if (isset($filters['prodi_id'])) {
            $this->db->join('proposal_mahasiswa pm', $this->table . '.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $filters['prodi_id']);
        }
        
        $this->db->group_by('status');
        $results = $this->db->get()->result();
        
        foreach ($results as $result) {
            $stats[$result->status] = (int)$result->count;
            $stats['total'] += (int)$result->count;
        }
        
        return $stats;
    }

    // =================================================================
    // UTILITY METHODS - Follow Seminar_proposal pattern
    // =================================================================

    /**
     * Get proposal data untuk validasi
     * 
     * @param int $proposal_id
     * @return object|null
     */
    public function get_proposal_data($proposal_id)
    {
        $this->db->select('pm.*, m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa, 
                          d.nama as nama_pembimbing, d.email as email_pembimbing, 
                          p.nama as nama_prodi, k.nama as nama_kaprodi');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen k', 'p.dosen_id = k.id', 'left');
        $this->db->where('pm.id', $proposal_id);
        
        return $this->db->get()->row();
    }

    /**
     * Generate filename untuk upload
     * 
     * @param int $mahasiswa_id
     * @param string $original_name
     * @return string
     */
    public function generate_filename($mahasiswa_id, $original_name)
    {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $timestamp = date('YmdHis');
        $random = substr(md5(uniqid(rand(), true)), 0, 6);
        
        return 'seminar_skripsi_' . $mahasiswa_id . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Delete file secara aman
     * 
     * @param string $filename
     * @param string $subfolder
     * @return bool
     */
    public function delete_file($filename, $subfolder = 'skripsi_files')
    {
        if (empty($filename)) {
            return true;
        }
        
        $file_path = FCPATH . 'uploads/seminar_skripsi/' . $subfolder . '/' . $filename;
        
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        
        return true;
    }

    /**
     * Validate file upload
     * 
     * @param array $file $_FILES array element
     * @return array
     */
    public function validate_file_upload($file)
    {
        $errors = [];
        $allowed_types = ['pdf', 'doc', 'docx'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (empty($file['name'])) {
            $errors[] = 'File skripsi wajib diupload';
            return ['valid' => false, 'errors' => $errors];
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_types)) {
            $errors[] = 'File harus berformat PDF, DOC, atau DOCX';
        }
        
        if ($file['size'] > $max_size) {
            $errors[] = 'Ukuran file maksimal 2MB';
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan saat upload file';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get eligible proposals untuk seminar skripsi
     * 
     * @param int $mahasiswa_id
     * @return array
     */
    public function get_eligible_proposals($mahasiswa_id)
    {
        $this->db->select('id, judul, workflow_status');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('mahasiswa_id', $mahasiswa_id);
        $this->db->where('workflow_status', 'seminar_skripsi');
        $this->db->where('status', '1');
        $this->db->order_by('id', 'DESC');
        
        return $this->db->get()->result();
    }

    // =================================================================
    // BADGE COUNTER METHODS - For Template Integration
    // =================================================================

    /**
     * Get badge count untuk dosen pembimbing
     * 
     * @param int $dosen_id
     * @return int
     */
    public function get_badge_count_dosen($dosen_id)
    {
        try {
            $this->db->select('COUNT(*) as count');
            $this->db->from($this->table . ' ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->where('pm.dosen_id', $dosen_id);
            $this->db->where_in('ssm.status', ['submitted', 'review_pembimbing']);
            $this->db->where('ssm.status_pembimbing', 'pending');
            
            $result = $this->db->get()->row();
            return $result ? (int)$result->count : 0;
        } catch (Exception $e) {
            log_message('debug', 'Seminar skripsi badge count error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get badge count untuk kaprodi
     * 
     * @param int $prodi_id
     * @return int
     */
    public function get_badge_count_kaprodi($prodi_id)
    {
        try {
            $this->db->select('COUNT(*) as count');
            $this->db->from($this->table . ' ssm');
            $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->where('m.prodi_id', $prodi_id);
            $this->db->where('ssm.status', 'review_kaprodi');
            $this->db->where('ssm.status_kaprodi', 'pending');
            
            $result = $this->db->get()->row();
            return $result ? (int)$result->count : 0;
        } catch (Exception $e) {
            log_message('debug', 'Seminar skripsi badge count kaprodi error: ' . $e->getMessage());
            return 0;
        }
    }
}

/**
 * CATATAN PENGGUNAAN:
 * 
 * 1. Model universal untuk semua role - load dengan nama yang sama
 * 2. Pattern konsisten dengan Seminar_proposal_mahasiswa_model
 * 3. Role-based methods untuk query sesuai akses
 * 4. Eligibility check yang comprehensive tapi simple
 * 5. Badge counter untuk template integration
 * 
 * CONTOH PENGGUNAAN:
 * 
 * // Di controller mahasiswa
 * $this->load->model('Seminar_skripsi_model', 'seminar_model');
 * $proposals = $this->seminar_model->get_by_mahasiswa($mahasiswa_id);
 * 
 * // Di controller dosen
 * $this->load->model('Seminar_skripsi_model', 'seminar_model');
 * $perlu_review = $this->seminar_model->get_perlu_review_dosen($dosen_id);
 * 
 * // Di controller kaprodi
 * $this->load->model('Seminar_skripsi_model', 'seminar_model');
 * $perlu_validasi = $this->seminar_model->get_perlu_review_kaprodi($prodi_id);
 * 
 * // Di controller staf
 * $this->load->model('Seminar_skripsi_model', 'seminar_model');
 * $scheduled = $this->seminar_model->get_for_staf(['scheduled', 'completed']);
 */