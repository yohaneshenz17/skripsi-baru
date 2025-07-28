<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Mahasiswa Model - Safe Implementation
 * 
 * Model untuk mengelola data seminar proposal mahasiswa (Phase 3)
 * File: application/models/Seminar_proposal_mahasiswa_model.php
 * 
 * SAFE APPROACH:
 * - Menggunakan tabel baru: seminar_proposal_mahasiswa
 * - Read-only access ke existing tables
 * - No modification ke existing data structure
 * 
 * @package     SIM_TA
 * @subpackage  Models
 * @category    Seminar Proposal Mahasiswa
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Safe Implementation)
 */

class Seminar_proposal_mahasiswa_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // =================================================================
    // SAFE METHODS - READ ONLY ACCESS KE EXISTING TABLES
    // =================================================================

    /**
     * Cek syarat jurnal bimbingan dari existing table (READ ONLY)
     * 
     * @param int $proposal_id
     * @return array
     */
    public function check_jurnal_requirement($proposal_id)
    {
        // AMAN: Read dari jurnal_bimbingan existing table
        $this->db->select('COUNT(*) as jumlah_validasi');
        $this->db->from('jurnal_bimbingan'); // Existing table - READ ONLY
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('status_validasi', '1'); // Sudah divalidasi dosen
        
        $result = $this->db->get()->row();
        $count = $result ? (int)$result->jumlah_validasi : 0;
        
        return [
            'eligible' => $count >= 8,
            'jurnal_validated_count' => $count,
            'minimum_required' => 8,
            'missing' => max(0, 8 - $count),
            'message' => $count >= 8 ? 
                'Memenuhi syarat untuk mengajukan seminar proposal' : 
                "Perlu " . (8 - $count) . " jurnal bimbingan lagi yang divalidasi dosen"
        ];
    }

    /**
     * Cek eligibility untuk submit seminar proposal (READ ONLY)
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function can_submit($proposal_id, $mahasiswa_id)
    {
        // AMAN: Read dari proposal_mahasiswa existing table
        $this->db->select('pm.*, m.status as status_mahasiswa');
        $this->db->from('proposal_mahasiswa pm'); // Existing table - READ ONLY
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        
        $proposal = $this->db->get()->row();
        
        if (!$proposal) {
            return [
                'can_submit' => false,
                'reason' => 'Proposal tidak ditemukan atau bukan milik Anda',
                'requirements' => []
            ];
        }

        $errors = [];
        $requirements = [];

        // Validasi berdasarkan existing workflow Phase 1 & 2
        if ($proposal->status_mahasiswa != '1') {
            $errors[] = 'Status mahasiswa tidak aktif';
        }
        $requirements[] = [
            'item' => 'Status mahasiswa aktif',
            'status' => $proposal->status_mahasiswa == '1' ? 'ok' : 'error'
        ];

        // CRITICAL: Harus dalam fase bimbingan (Phase 2 completed)
        if ($proposal->workflow_status != 'bimbingan') {
            $errors[] = 'Proposal belum dalam tahap bimbingan';
        }
        $requirements[] = [
            'item' => 'Tahap bimbingan tercapai',
            'status' => $proposal->workflow_status == 'bimbingan' ? 'ok' : 'error'
        ];

        // CRITICAL: Pembimbing harus sudah menyetujui di Phase 1
        if ($proposal->status_pembimbing != '1') {
            $errors[] = 'Pembimbing belum menyetujui proposal';
        }
        $requirements[] = [
            'item' => 'Pembimbing menyetujui proposal',
            'status' => $proposal->status_pembimbing == '1' ? 'ok' : 'error'
        ];

        // CRITICAL: Kaprodi harus sudah menyetujui di Phase 1
        if ($proposal->status_kaprodi != '1') {
            $errors[] = 'Kaprodi belum menyetujui proposal';
        }
        $requirements[] = [
            'item' => 'Kaprodi menyetujui proposal',
            'status' => $proposal->status_kaprodi == '1' ? 'ok' : 'error'
        ];

        // Cek syarat jurnal bimbingan dari Phase 2
        $jurnal_check = $this->check_jurnal_requirement($proposal_id);
        if (!$jurnal_check['eligible']) {
            $errors[] = $jurnal_check['message'];
        }
        $requirements[] = [
            'item' => 'Minimal 8 jurnal bimbingan tervalidasi',
            'status' => $jurnal_check['eligible'] ? 'ok' : 'error',
            'detail' => $jurnal_check['jurnal_validated_count'] . '/8 jurnal'
        ];

        // Cek apakah sudah pernah submit di Phase 3
        $existing = $this->get_by_proposal_id($proposal_id);
        if ($existing && !in_array($existing->status, ['rejected', 'completed'])) {
            $errors[] = 'Sudah ada pengajuan seminar proposal yang sedang diproses';
        }
        $requirements[] = [
            'item' => 'Tidak ada pengajuan yang sedang diproses',
            'status' => !$existing || in_array($existing->status, ['rejected', 'completed']) ? 'ok' : 'error'
        ];

        return [
            'can_submit' => empty($errors),
            'reason' => empty($errors) ? 'Memenuhi syarat' : implode(', ', $errors),
            'requirements' => $requirements,
            'jurnal_info' => $jurnal_check
        ];
    }

    /**
     * Get validated jurnal bimbingan (READ ONLY)
     * 
     * @param int $proposal_id
     * @return array
     */
    public function get_validated_jurnal($proposal_id)
    {
        $this->db->select('jb.*, d.nama as nama_validator');
        $this->db->from('jurnal_bimbingan jb'); // Existing table - READ ONLY
        $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
        $this->db->where('jb.proposal_id', $proposal_id);
        $this->db->where('jb.status_validasi', '1');
        $this->db->order_by('jb.pertemuan_ke', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get proposal data untuk validasi (READ ONLY)
     * 
     * @param int $proposal_id
     * @return object|null
     */
    public function get_proposal_data($proposal_id)
    {
        $this->db->select('pm.*, m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa, 
                          d.nama as nama_pembimbing, d.email as email_pembimbing, 
                          p.nama as nama_prodi');
        $this->db->from('proposal_mahasiswa pm'); // Existing table - READ ONLY
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.id', $proposal_id);
        
        return $this->db->get()->row();
    }

    // =================================================================
    // NEW TABLE METHODS - SEMINAR_PROPOSAL_MAHASISWA
    // =================================================================

    /**
     * Get seminar proposal by proposal ID
     * 
     * @param int $proposal_id
     * @return object|null
     */
    public function get_by_proposal_id($proposal_id)
    {
        return $this->db->select('*')
                       ->from('seminar_proposal_mahasiswa_v') // New view
                       ->where('proposal_id', $proposal_id)
                       ->get()
                       ->row();
    }

    /**
     * Get seminar proposal by ID dengan security check
     * 
     * @param int $id
     * @param int $mahasiswa_id (untuk security check)
     * @return object|null
     */
    public function get_by_id($id, $mahasiswa_id = null)
    {
        $this->db->select('*')
                 ->from('seminar_proposal_mahasiswa_v') // New view
                 ->where('id', $id);
        
        if ($mahasiswa_id) {
            $this->db->where('mahasiswa_id', $mahasiswa_id);
        }
        
        return $this->db->get()->row();
    }

    /**
     * Create pengajuan seminar proposal baru
     * 
     * @param array $data
     * @return array
     */
    public function create_pengajuan($data)
    {
        $this->db->trans_start();
        
        try {
            // Validasi data required
            $required_fields = ['proposal_id', 'mahasiswa_id'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Field {$field} wajib diisi");
                }
            }

            // Cek eligibility berdasarkan existing data
            $can_submit = $this->can_submit($data['proposal_id'], $data['mahasiswa_id']);
            if (!$can_submit['can_submit']) {
                throw new Exception($can_submit['reason']);
            }

            // Siapkan data insert ke NEW TABLE
            $insert_data = [
                'proposal_id' => $data['proposal_id'],
                'mahasiswa_id' => $data['mahasiswa_id'],
                'status' => 'submitted',
                'current_step' => 'pembimbing',
                'file_proposal' => $data['file_proposal'] ?? null,
                'keterangan_mahasiswa' => $data['keterangan_mahasiswa'] ?? null,
                'created_by' => $data['mahasiswa_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert ke NEW TABLE: seminar_proposal_mahasiswa
            $this->db->insert('seminar_proposal_mahasiswa', $insert_data);
            $insert_id = $this->db->insert_id();

            // OPTIONAL: Update workflow_status di existing table (minimal update)
            $this->db->update('proposal_mahasiswa', 
                ['workflow_status' => 'seminar_proposal'], 
                ['id' => $data['proposal_id']]
            );

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan pengajuan ke database');
            }

            return [
                'success' => true,
                'message' => 'Pengajuan seminar proposal berhasil disimpan',
                'id' => $insert_id,
                'data' => $insert_data
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'id' => null,
                'data' => null
            ];
        }
    }

    /**
     * Update pengajuan seminar proposal
     * 
     * @param int $id
     * @param array $data
     * @param int $mahasiswa_id (untuk security)
     * @return array
     */
    public function update_pengajuan($id, $data, $mahasiswa_id)
    {
        $this->db->trans_start();
        
        try {
            // Cek apakah data ada dan milik mahasiswa
            $existing = $this->get_by_id($id, $mahasiswa_id);
            if (!$existing) {
                throw new Exception('Data tidak ditemukan atau bukan milik Anda');
            }

            // Cek apakah masih bisa diedit
            if (!in_array($existing->status, ['draft', 'rejected'])) {
                throw new Exception('Pengajuan tidak dapat diedit karena sudah diproses');
            }

            // Siapkan data update
            $update_data = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update field yang diizinkan untuk mahasiswa
            $allowed_fields = ['file_proposal', 'keterangan_mahasiswa'];
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_data[$field] = $data[$field];
                }
            }

            // Jika status draft dan akan disubmit
            if (isset($data['submit']) && $data['submit'] === true) {
                $update_data['status'] = 'submitted';
                $update_data['current_step'] = 'pembimbing';
            }

            // Update NEW TABLE: seminar_proposal_mahasiswa
            $this->db->where('id', $id);
            $this->db->where('mahasiswa_id', $mahasiswa_id);
            $this->db->update('seminar_proposal_mahasiswa', $update_data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal memperbarui data');
            }

            return [
                'success' => true,
                'message' => 'Data berhasil diperbarui',
                'data' => $update_data
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get workflow status untuk tracking progress
     * 
     * @param int $id
     * @param int $mahasiswa_id
     * @return array
     */
    public function get_workflow_status($id, $mahasiswa_id = null)
    {
        $seminar = $this->get_by_id($id, $mahasiswa_id);
        
        if (!$seminar) {
            return [
                'found' => false,
                'current_step' => null,
                'progress' => [],
                'next_action' => null
            ];
        }

        // Define workflow steps untuk Phase 3
        $steps = [
            [
                'key' => 'pengajuan',
                'title' => 'Pengajuan Mahasiswa',
                'icon' => 'ni-send',
                'description' => 'Upload proposal dan submit pengajuan',
                'completed' => in_array($seminar->status, ['submitted', 'review_pembimbing', 'review_kaprodi', 'approved', 'scheduled', 'completed']),
                'active' => $seminar->status == 'draft'
            ],
            [
                'key' => 'review_pembimbing',
                'title' => 'Review Pembimbing',
                'icon' => 'ni-single-02',
                'description' => 'Dosen pembimbing memberikan rekomendasi',
                'completed' => in_array($seminar->status, ['review_kaprodi', 'approved', 'scheduled', 'completed']),
                'active' => in_array($seminar->status, ['submitted', 'review_pembimbing'])
            ],
            [
                'key' => 'review_kaprodi',
                'title' => 'Review Kaprodi',
                'icon' => 'ni-badge',
                'description' => 'Kaprodi melakukan validasi dan cek plagiarisme',
                'completed' => in_array($seminar->status, ['approved', 'scheduled', 'completed']),
                'active' => $seminar->status == 'review_kaprodi'
            ],
            [
                'key' => 'penjadwalan',
                'title' => 'Penjadwalan',
                'icon' => 'ni-calendar-grid-58',
                'description' => 'Penentuan jadwal, tempat, dan penguji',
                'completed' => in_array($seminar->status, ['scheduled', 'completed']),
                'active' => $seminar->status == 'approved'
            ],
            [
                'key' => 'pelaksanaan',
                'title' => 'Pelaksanaan Seminar',
                'icon' => 'ni-trophy',
                'description' => 'Seminar proposal dilaksanakan',
                'completed' => $seminar->status == 'completed',
                'active' => $seminar->status == 'scheduled'
            ]
        ];

        // Determine next action untuk mahasiswa
        $next_action = null;
        switch ($seminar->status) {
            case 'draft':
                $next_action = 'Lengkapi dan submit pengajuan';
                break;
            case 'submitted':
                $next_action = 'Menunggu review dosen pembimbing';
                break;
            case 'review_pembimbing':
                $next_action = 'Pembimbing sedang melakukan review';
                break;
            case 'review_kaprodi':
                $next_action = 'Kaprodi sedang melakukan validasi';
                break;
            case 'approved':
                $next_action = 'Menunggu penjadwalan seminar';
                break;
            case 'scheduled':
                $next_action = 'Bersiap untuk pelaksanaan seminar';
                break;
            case 'completed':
                $next_action = 'Menunggu hasil penilaian';
                break;
            case 'rejected':
                $next_action = 'Perbaiki dan ajukan ulang';
                break;
        }

        return [
            'found' => true,
            'current_step' => $seminar->current_step,
            'status' => $seminar->status,
            'status_description' => $seminar->status_description,
            'progress' => $steps,
            'next_action' => $next_action,
            'seminar' => $seminar
        ];
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Get allowed file types untuk upload
     * 
     * @return array
     */
    public function get_allowed_file_types()
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
    }

    /**
     * Get max file size dalam bytes
     * 
     * @return int
     */
    public function get_max_file_size()
    {
        return 1 * 1024 * 1024; // 1MB
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
        
        return 'seminar_proposal_' . $mahasiswa_id . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Get status badge class untuk UI
     * 
     * @param string $status
     * @return string
     */
    public function get_status_badge_class($status)
    {
        $classes = [
            'draft' => 'badge-secondary',
            'submitted' => 'badge-info',
            'review_pembimbing' => 'badge-warning',
            'review_kaprodi' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            'scheduled' => 'badge-primary',
            'completed' => 'badge-success'
        ];
        
        return $classes[$status] ?? 'badge-light';
    }

    // =================================================================
    // FUTURE METHODS (untuk role dosen, kaprodi, staf - dikembangkan kemudian)
    // =================================================================

    /**
     * Get list seminar proposal untuk dosen pembimbing
     * (Future development - Phase 3B)
     */
    public function get_for_pembimbing($dosen_id)
    {
        // TODO: Implement untuk role dosen
        return [];
    }

    /**
     * Get list seminar proposal untuk kaprodi
     * (Future development - Phase 3C)
     */
    public function get_for_kaprodi($prodi_id)
    {
        // TODO: Implement untuk role kaprodi
        return [];
    }

    /**
     * Get list seminar proposal untuk staf
     * (Future development - Phase 3D)
     */
    public function get_for_staf()
    {
        // TODO: Implement untuk role staf
        return [];
    }
}