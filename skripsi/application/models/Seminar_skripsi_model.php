<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Skripsi Model - Universal untuk Semua Role (Phase 5) - FIXED VERSION
 * 
 * Model universal yang mengikuti pattern Seminar_proposal_mahasiswa_model.php
 * Dapat digunakan oleh semua role: mahasiswa, dosen, kaprodi, staf
 * 100% kompatibel dengan database existing
 * 
 * PERBAIKAN:
 * - Fixed method can_submit() untuk pengecekan surat penelitian
 * - Added method check_eligibility() yang lebih robust
 * - Added debug methods untuk troubleshooting
 * 
 * File: application/models/Seminar_skripsi_model.php
 * 
 * @package     SIM_TA
 * @subpackage  Models
 * @category    Seminar Skripsi
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.1 (FIXED - Penelitian Table Issue)
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
    // ELIGIBILITY & VALIDATION METHODS - FIXED VERSION
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
     * 🔧 FIXED: Check eligibility untuk submit seminar skripsi
     * Perbaikan utama: pengecekan surat izin penelitian menggunakan tabel yang benar
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function check_eligibility($proposal_id, $mahasiswa_id = null)
    {
        $errors = [];
        $requirements = [];
        
        try {
            // 1. Check proposal exists
            $this->db->select('*');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('id', $proposal_id);
            if ($mahasiswa_id) {
                $this->db->where('mahasiswa_id', $mahasiswa_id);
            }
            $proposal = $this->db->get()->row();
            
            if (!$proposal) {
                return [
                    'eligible' => false,
                    'errors' => ['Proposal tidak ditemukan'],
                    'requirements' => []
                ];
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
            $this->db->select('COUNT(*) as count');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            $jurnal_count = $this->db->get()->row()->count;
            
            $requirements['jurnal_bimbingan'] = [
                'name' => 'Jurnal Bimbingan',
                'required' => 14,
                'current' => $jurnal_count,
                'met' => $jurnal_count >= 14
            ];
            
            if ($jurnal_count < 14) {
                $errors[] = 'Perlu ' . (14 - $jurnal_count) . ' jurnal bimbingan lagi yang divalidasi dosen';
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
            
            // 🔧 PERBAIKAN UTAMA: Check surat izin penelitian menggunakan tabel yang benar
            $penelitian_approved = false;
            
            try {
                // Gunakan tabel permohonan_izin_penelitian yang benar
                $this->db->select('COUNT(*) as count');
                $this->db->from('permohonan_izin_penelitian');
                $this->db->where('proposal_mahasiswa_id', $proposal_id);
                $this->db->where('status_pembimbing', 'approved');
                $permohonan_count = $this->db->get()->row()->count;
                
                $penelitian_approved = ($permohonan_count > 0);
                
            } catch (Exception $e) {
                log_message('error', 'Error checking permohonan_izin_penelitian: ' . $e->getMessage());
                
                // Fallback ke tabel lama jika ada error
                try {
                    $this->db->select('COUNT(*) as count');
                    $this->db->from('penelitian');
                    $this->db->where('proposal_mahasiswa_id', $proposal_id);
                    $this->db->where('persetujuan_pembimbing', '1');
                    $penelitian_count = $this->db->get()->row()->count;
                    
                    $penelitian_approved = ($penelitian_count > 0);
                } catch (Exception $e2) {
                    log_message('error', 'Error checking both penelitian tables: ' . $e2->getMessage());
                }
            }
            
            $requirements['surat_penelitian'] = [
                'name' => 'Surat Izin Penelitian',
                'required' => 1,
                'current' => $penelitian_approved ? 1 : 0,
                'met' => $penelitian_approved
            ];
            
            if (!$penelitian_approved) {
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

    /**
     * 🔧 FIXED: Check eligibility untuk submit seminar skripsi (alias untuk backward compatibility)
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function can_submit($proposal_id, $mahasiswa_id)
    {
        return $this->check_eligibility($proposal_id, $mahasiswa_id);
    }

    // =================================================================
    // 🔍 DEBUG METHODS - Untuk Troubleshooting
    // =================================================================

    /**
     * 🔍 DEBUG: Method untuk test model langsung
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function debug_check_eligibility($proposal_id, $mahasiswa_id = null)
    {
        echo "<h3>🔧 Debug Model check_eligibility untuk Proposal ID: {$proposal_id}</h3>";
        
        $result = $this->check_eligibility($proposal_id, $mahasiswa_id);
        
        echo "<p><strong>Eligible:</strong> " . ($result['eligible'] ? '✅ YES' : '❌ NO') . "</p>";
        
        if (!empty($result['errors'])) {
            echo "<p><strong>Errors:</strong> " . implode(', ', $result['errors']) . "</p>";
        } else {
            echo "<p>✅ <strong>No errors - All requirements met!</strong></p>";
        }
        
        if (isset($result['requirements'])) {
            echo "<h4>Requirements Details:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Requirement</th><th>Current</th><th>Required</th><th>Met</th></tr>";
            foreach ($result['requirements'] as $name => $req) {
                $met_icon = $req['met'] ? '✅' : '❌';
                echo "<tr>";
                echo "<td>{$req['name']}</td>";
                echo "<td>{$req['current']}</td>";
                echo "<td>{$req['required']}</td>";
                echo "<td>{$met_icon}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        return $result;
    }

    /**
     * 🔍 DEBUG: Method untuk test pengecekan surat penelitian
     * 
     * @param int $proposal_id
     * @return array
     */
    public function debug_penelitian_check($proposal_id)
    {
        echo "<h3>🔬 Debug Pengecekan Surat Penelitian untuk Proposal ID: {$proposal_id}</h3>";
        
        $results = [];
        
        // Test tabel permohonan_izin_penelitian
        try {
            $this->db->select('id, status_pembimbing, created_at');
            $this->db->from('permohonan_izin_penelitian');
            $this->db->where('proposal_mahasiswa_id', $proposal_id);
            $permohonan = $this->db->get()->result();
            
            echo "<h4>📋 Tabel permohonan_izin_penelitian:</h4>";
            echo "<p><strong>SQL Query:</strong> " . $this->db->last_query() . "</p>";
            echo "<p><strong>Count:</strong> " . count($permohonan) . "</p>";
            
            if ($permohonan) {
                $approved_count = 0;
                foreach ($permohonan as $p) {
                    echo "<p>- ID: {$p->id}, Status: {$p->status_pembimbing}, Created: {$p->created_at}</p>";
                    if ($p->status_pembimbing === 'approved') {
                        $approved_count++;
                    }
                }
                echo "<p><strong>Approved Count:</strong> {$approved_count} " . ($approved_count > 0 ? '✅' : '❌') . "</p>";
                $results['permohonan'] = $approved_count;
            } else {
                echo "<p>❌ Tidak ada data di tabel permohonan_izin_penelitian</p>";
                $results['permohonan'] = 0;
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Error tabel permohonan: " . $e->getMessage() . "</p>";
            $results['permohonan'] = 0;
        }
        
        // Test tabel penelitian lama
        try {
            $this->db->select('COUNT(*) as count');
            $this->db->from('penelitian');
            $this->db->where('proposal_mahasiswa_id', $proposal_id);
            $this->db->where('persetujuan_pembimbing', '1');
            $penelitian_count = $this->db->get()->row()->count;
            
            echo "<h4>📋 Tabel penelitian (lama):</h4>";
            echo "<p><strong>SQL Query:</strong> " . $this->db->last_query() . "</p>";
            echo "<p><strong>Count:</strong> {$penelitian_count} " . ($penelitian_count > 0 ? '✅' : '❌') . "</p>";
            $results['penelitian'] = $penelitian_count;
            
        } catch (Exception $e) {
            echo "<p>❌ Error tabel penelitian: " . $e->getMessage() . "</p>";
            $results['penelitian'] = 0;
        }
        
        // Summary
        echo "<h4>📊 Summary:</h4>";
        $total_approved = $results['permohonan'] + $results['penelitian'];
        echo "<p><strong>Total Approved:</strong> {$total_approved} " . ($total_approved > 0 ? '✅' : '❌') . "</p>";
        echo "<p><strong>Memenuhi Syarat:</strong> " . ($total_approved > 0 ? '✅ YES' : '❌ NO') . "</p>";
        
        return $results;
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
    
    /**
     * Get statistics untuk dashboard kaprodi
     * 
     * @param int $prodi_id
     * @return array
     */
    public function get_statistics_kaprodi($prodi_id)
    {
        $stats = [];
        
        // Total pengajuan dalam prodi
        $this->db->select('COUNT(*) as total');
        $this->db->from($this->table . ' ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('m.prodi_id', $prodi_id);
        $stats['total'] = $this->db->get()->row()->total ?? 0;
        
        // Perlu review kaprodi
        $this->db->select('COUNT(*) as perlu_review');
        $this->db->from($this->table . ' ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('ssm.status', 'review_kaprodi');
        $this->db->where('ssm.status_pembimbing', 'approved');
        $this->db->where('m.prodi_id', $prodi_id);
        $stats['perlu_review'] = $this->db->get()->row()->perlu_review ?? 0;
        
        // Perlu dijadwalkan (approved tapi belum ada jadwal)
        $this->db->select('COUNT(*) as perlu_dijadwalkan');
        $this->db->from($this->table . ' ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('ssm.status', 'approved');
        $this->db->where('ssm.status_kaprodi', 'approved');
        $this->db->where('ssm.tanggal_seminar IS NULL');
        $this->db->where('m.prodi_id', $prodi_id);
        $stats['perlu_dijadwalkan'] = $this->db->get()->row()->perlu_dijadwalkan ?? 0;
        
        // Terjadwal
        $this->db->select('COUNT(*) as terjadwal');
        $this->db->from($this->table . ' ssm');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->where('ssm.status', 'scheduled');
        $this->db->where('m.prodi_id', $prodi_id);
        $stats['terjadwal'] = $this->db->get()->row()->terjadwal ?? 0;
        
        return $stats;
    }
    
    /**
     * Get seminar yang perlu dijadwalkan (approved tapi belum ada jadwal)
     * 
     * @param int $prodi_id
     * @return array
     */
    public function get_perlu_dijadwalkan($prodi_id)
    {
        $this->db->select('
            ssm.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing
        ');
        $this->db->from($this->table . ' ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('ssm.status', 'approved');
        $this->db->where('ssm.status_kaprodi', 'approved');
        $this->db->where('ssm.tanggal_seminar IS NULL');
        $this->db->where('m.prodi_id', $prodi_id);
        $this->db->order_by('ssm.tanggal_review_kaprodi', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get jadwal seminar mendatang untuk prodi
     * 
     * @param int $prodi_id
     * @return array
     */
    public function get_jadwal_mendatang($prodi_id)
    {
        $this->db->select('
            ssm.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            d.nama as nama_pembimbing,
            d1.nama as nama_penguji1,
            d2.nama as nama_penguji2
        ');
        $this->db->from($this->table . ' ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
        $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
        $this->db->where('ssm.status', 'scheduled');
        $this->db->where('ssm.tanggal_seminar >=', date('Y-m-d'));
        $this->db->where('m.prodi_id', $prodi_id);
        $this->db->order_by('ssm.tanggal_seminar', 'ASC');
        $this->db->order_by('ssm.jam_seminar', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get seminar detail lengkap dengan filter prodi
     * 
     * @param int $seminar_id
     * @param int $prodi_id
     * @return object|null
     */
    public function get_seminar_detail($seminar_id, $prodi_id = null)
    {
        $this->db->select('
            ssm.*,
            pm.judul,
            pm.dosen_id as pembimbing_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.prodi_id,
            d.nama as nama_pembimbing,
            d.email as email_pembimbing,
            p.nama as nama_prodi,
            d1.nama as nama_penguji1,
            d1.email as email_penguji1,
            d2.nama as nama_penguji2,
            d2.email as email_penguji2
        ');
        $this->db->from($this->table . ' ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
        $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
        $this->db->where('ssm.id', $seminar_id);
        
        if ($prodi_id) {
            $this->db->where('m.prodi_id', $prodi_id);
        }
        
        return $this->db->get()->row();
    }
    
    /**
     * Get rekomendasi dosen penguji dari seminar proposal sebelumnya
     * 
     * @param int $proposal_id
     * @return object|null
     */
    public function get_dosen_penguji_rekomendasi($proposal_id)
    {
        $this->db->select('
            spm.dosen_penguji1_id,
            spm.dosen_penguji2_id,
            d1.nama as nama_penguji1,
            d2.nama as nama_penguji2
        ');
        $this->db->from('seminar_proposal_mahasiswa spm');
        $this->db->join('dosen d1', 'spm.dosen_penguji1_id = d1.id', 'left');
        $this->db->join('dosen d2', 'spm.dosen_penguji2_id = d2.id', 'left');
        $this->db->where('spm.proposal_id', $proposal_id);
        $this->db->where('spm.status', 'completed'); // Hanya dari seminar yang sudah selesai
        
        return $this->db->get()->row();
    }
    
    /**
     * Get daftar dosen aktif untuk dropdown penguji
     * 
     * @return array
     */
    public function get_dosen_list()
    {
        $this->db->select('id, nama, email');
        $this->db->from('dosen');
        $this->db->where('status', '1'); // Aktif
        $this->db->order_by('nama', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get seminar detail lengkap dengan data penguji untuk notifikasi
     * 
     * @param int $seminar_id
     * @return object|null
     */
    public function get_seminar_lengkap_notifikasi($seminar_id)
    {
        $this->db->select('
            ssm.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            d.nama as nama_pembimbing,
            d.email as email_pembimbing,
            d1.nama as nama_penguji1,
            d1.email as email_penguji1,
            d2.nama as nama_penguji2,
            d2.email as email_penguji2
        ');
        $this->db->from($this->table . ' ssm');
        $this->db->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'ssm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('dosen d1', 'ssm.dosen_penguji1_id = d1.id', 'left');
        $this->db->join('dosen d2', 'ssm.dosen_penguji2_id = d2.id', 'left');
        $this->db->where('ssm.id', $seminar_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Check validitas penjadwalan (cek konflik jadwal)
     * 
     * @param string $tanggal_seminar
     * @param string $jam_seminar
     * @param string $tempat_seminar
     * @param int $exclude_id (untuk update)
     * @return bool
     */
    public function check_jadwal_conflict($tanggal_seminar, $jam_seminar, $tempat_seminar, $exclude_id = null)
    {
        $this->db->select('COUNT(*) as count');
        $this->db->from($this->table);
        $this->db->where('tanggal_seminar', $tanggal_seminar);
        $this->db->where('jam_seminar', $jam_seminar);
        $this->db->where('tempat_seminar', $tempat_seminar);
        $this->db->where('status', 'scheduled');
        
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        
        $result = $this->db->get()->row();
        return ($result->count > 0);
    }
    
    /**
     * Update workflow status dan current step
     * 
     * @param int $seminar_id
     * @param string $status
     * @param string $current_step
     * @return bool
     */
    public function update_workflow($seminar_id, $status, $current_step)
    {
        $data = [
            'status' => $status,
            'current_step' => $current_step,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $seminar_id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Get progress percentage untuk progress bar
     * 
     * @param object $seminar
     * @return int (0-100)
     */
    public function get_progress_percentage($seminar)
    {
        if (!$seminar) return 0;
        
        $progress_map = [
            'draft' => 10,
            'submitted' => 25,
            'review_pembimbing' => 40,
            'review_kaprodi' => 60,
            'approved' => 75,
            'scheduled' => 90,
            'completed' => 100
        ];
        
        return $progress_map[$seminar->status] ?? 0;
    }

}

/**
 * 🔧 PERBAIKAN SUMMARY:
 * 
 * 1. ✅ FIXED method check_eligibility() - pengecekan surat penelitian menggunakan tabel permohonan_izin_penelitian
 * 2. ✅ Added method debug_check_eligibility() - untuk troubleshooting model
 * 3. ✅ Added method debug_penelitian_check() - untuk test spesifik surat penelitian
 * 4. ✅ Backward compatibility - can_submit() masih berfungsi
 * 5. ✅ Error handling yang robust dengan fallback ke tabel lama
 * 
 * TESTING METHODS:
 * 
 * // Test di controller
 * $this->load->model('Seminar_skripsi_model', 'ss_model');
 * $result = $this->ss_model->debug_check_eligibility(47, 47);
 * $penelitian = $this->ss_model->debug_penelitian_check(47);
 * 
 * EXPECTED RESULT:
 * - check_eligibility() akan return ['eligible' => true, 'errors' => []]
 * - Mahasiswa Agus Bumagi akan bisa mengajukan seminar skripsi
 */