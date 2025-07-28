<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Mahasiswa Model - Database Compatible Version
 * 
 * Model yang 100% kompatibel dengan struktur database existing
 * Menggunakan table dan view yang sudah ada di database stkp7133_skripsi
 * 
 * File: application/models/Seminar_proposal_mahasiswa_model.php
 * 
 * @package     SIM_TA
 * @subpackage  Models
 * @category    Seminar Proposal Mahasiswa
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Database Compatible)
 */
class Seminar_proposal_mahasiswa_model extends CI_Model {

    // Table names sesuai database existing
    protected $table = 'seminar_proposal_mahasiswa';
    protected $view = 'seminar_proposal_mahasiswa_v';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // =================================================================
    // JURNAL BIMBINGAN REQUIREMENTS (Phase 2 Integration)
    // =================================================================

    /**
     * Cek syarat jurnal bimbingan menggunakan database function existing
     * 
     * @param int $proposal_id
     * @param int $min_required
     * @return array
     */
    public function check_jurnal_requirement($proposal_id, $min_required = 8)
    {
        try {
            // Gunakan database function yang sudah ada
            $sql = "SELECT check_jurnal_requirement_mahasiswa(?) as result";
            $query = $this->db->query($sql, [$proposal_id]);
            $result = $query->row();
            
            if ($result && $result->result) {
                return json_decode($result->result, true);
            }
        } catch (Exception $e) {
            // Log error tapi continue dengan fallback
            log_message('debug', 'Database function not available, using fallback: ' . $e->getMessage());
        }
        
        // Fallback manual jika function tidak tersedia
        $this->db->select('COUNT(*) as total');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('status_validasi', '1'); // Sudah divalidasi dosen
        
        $count_result = $this->db->get()->row();
        $count = $count_result ? (int)$count_result->total : 0;
        
        return [
            'eligible' => $count >= $min_required,
            'jurnal_validated_count' => $count,
            'minimum_required' => $min_required,
            'missing' => max(0, $min_required - $count),
            'message' => $count >= $min_required ? 
                'Memenuhi syarat untuk mengajukan seminar proposal' : 
                "Perlu " . ($min_required - $count) . " jurnal bimbingan lagi yang divalidasi dosen"
        ];
    }

    /**
     * Get validated jurnal bimbingan untuk proposal
     * 
     * @param int $proposal_id
     * @return array
     */
    public function get_validated_jurnal($proposal_id)
    {
        $this->db->select('jb.*, d.nama as nama_validator');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
        $this->db->where('jb.proposal_id', $proposal_id);
        $this->db->where('jb.status_validasi', '1');
        $this->db->order_by('jb.pertemuan_ke', 'ASC');
        
        return $this->db->get()->result();
    }

    // =================================================================
    // SEMINAR PROPOSAL METHODS (Phase 3)
    // =================================================================

    /**
     * Get seminar proposal by proposal ID menggunakan view existing
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
     * Get seminar proposal by ID dengan security check
     * 
     * @param int $id
     * @param int $mahasiswa_id (untuk security check)
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
     * Cek eligibility untuk submit seminar proposal
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function can_submit($proposal_id, $mahasiswa_id)
    {
        $errors = [];
        $requirements = [];
        
        // 1. Cek proposal exists dan milik mahasiswa
        $this->db->select('pm.*, m.status as status_mahasiswa');
        $this->db->from('proposal_mahasiswa pm');
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

        // 2. Validasi status mahasiswa
        if ($proposal->status_mahasiswa != '1') {
            $errors[] = 'Status mahasiswa tidak aktif';
        }
        $requirements[] = [
            'item' => 'Status mahasiswa aktif',
            'status' => $proposal->status_mahasiswa == '1' ? 'ok' : 'error'
        ];

        // 3. Validasi workflow status (harus sudah phase 2)
        if ($proposal->workflow_status != 'bimbingan') {
            $errors[] = 'Proposal belum dalam tahap bimbingan (Phase 2)';
        }
        $requirements[] = [
            'item' => 'Tahap bimbingan tercapai (Phase 2)',
            'status' => $proposal->workflow_status == 'bimbingan' ? 'ok' : 'error'
        ];

        // 4. Validasi persetujuan pembimbing (Phase 1)
        if ($proposal->status_pembimbing != '1') {
            $errors[] = 'Pembimbing belum menyetujui proposal';
        }
        $requirements[] = [
            'item' => 'Pembimbing menyetujui proposal',
            'status' => $proposal->status_pembimbing == '1' ? 'ok' : 'error'
        ];

        // 5. Validasi persetujuan kaprodi (Phase 1)
        if ($proposal->status_kaprodi != '1') {
            $errors[] = 'Kaprodi belum menyetujui proposal';
        }
        $requirements[] = [
            'item' => 'Kaprodi menyetujui proposal',
            'status' => $proposal->status_kaprodi == '1' ? 'ok' : 'error'
        ];

        // 6. Validasi syarat jurnal bimbingan (Phase 2)
        $jurnal_check = $this->check_jurnal_requirement($proposal_id);
        if (!$jurnal_check['eligible']) {
            $errors[] = $jurnal_check['message'];
        }
        $requirements[] = [
            'item' => 'Minimal 8 jurnal bimbingan tervalidasi',
            'status' => $jurnal_check['eligible'] ? 'ok' : 'error',
            'detail' => $jurnal_check['jurnal_validated_count'] . '/8 jurnal'
        ];

        // 7. Validasi belum ada pengajuan existing
        $existing = $this->get_by_proposal_id($proposal_id);
        $can_resubmit = !$existing || in_array($existing->status, ['rejected']);
        
        if (!$can_resubmit) {
            $errors[] = 'Sudah ada pengajuan seminar proposal yang sedang diproses';
        }
        $requirements[] = [
            'item' => 'Tidak ada pengajuan yang sedang diproses',
            'status' => $can_resubmit ? 'ok' : 'error'
        ];

        return [
            'can_submit' => empty($errors),
            'reason' => empty($errors) ? 'Memenuhi syarat' : implode(', ', $errors),
            'requirements' => $requirements,
            'jurnal_info' => $jurnal_check,
            'proposal_data' => $proposal
        ];
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
            // Validasi required fields
            $required_fields = ['proposal_id', 'mahasiswa_id'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Field {$field} wajib diisi");
                }
            }

            // Cek eligibility menggunakan method existing
            $eligibility = $this->can_submit($data['proposal_id'], $data['mahasiswa_id']);
            if (!$eligibility['can_submit']) {
                throw new Exception($eligibility['reason']);
            }

            // Prepare data sesuai struktur table existing
            $insert_data = [
                'proposal_id' => $data['proposal_id'],
                'mahasiswa_id' => $data['mahasiswa_id'],
                'status' => 'submitted', // Langsung submitted, bukan draft
                'current_step' => 'pembimbing', // Workflow step pertama
                'file_proposal' => $data['file_proposal'] ?? null,
                'keterangan_mahasiswa' => $data['keterangan_mahasiswa'] ?? null,
                'status_pembimbing' => 'pending', // Sesuai enum di database
                'status_kaprodi' => 'pending', // Sesuai enum di database
                'created_by' => $data['mahasiswa_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert ke table existing
            $this->db->insert($this->table, $insert_data);
            $insert_id = $this->db->insert_id();
            
            if (!$insert_id) {
                throw new Exception('Gagal menyimpan data ke database');
            }

            // Trigger database akan auto-update workflow_status di proposal_mahasiswa
            // Tidak perlu manual update karena sudah ada trigger

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction gagal');
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
     * Update pengajuan seminar proposal existing
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
            // Security check - data exists dan milik mahasiswa
            $existing = $this->get_by_id($id, $mahasiswa_id);
            if (!$existing) {
                throw new Exception('Data tidak ditemukan atau bukan milik Anda');
            }

            // Business rule - hanya bisa edit jika draft atau rejected
            $editable_statuses = ['draft', 'rejected'];
            if (!in_array($existing->status, $editable_statuses)) {
                throw new Exception('Pengajuan tidak dapat diedit karena sudah diproses (status: ' . $existing->status . ')');
            }

            // Prepare update data
            $update_data = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Field yang diizinkan untuk update oleh mahasiswa
            $allowed_fields = ['file_proposal', 'keterangan_mahasiswa'];
            foreach ($allowed_fields as $field) {
                if (isset($data[$field])) {
                    $update_data[$field] = $data[$field];
                }
            }

            // Jika request submit (dari draft ke submitted)
            if (isset($data['submit']) && $data['submit'] === true && $existing->status == 'draft') {
                $update_data['status'] = 'submitted';
                $update_data['current_step'] = 'pembimbing';
            }

            // Update table
            $this->db->where('id', $id);
            $this->db->where('mahasiswa_id', $mahasiswa_id); // Double security check
            $affected_rows = $this->db->update($this->table, $update_data);

            if ($affected_rows === 0) {
                throw new Exception('Tidak ada data yang diupdate');
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction gagal');
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
     * Get workflow status untuk progress tracking
     * 
     * @param int $id
     * @param int $mahasiswa_id
     * @return array
     */
    public function get_workflow_status($id, $mahasiswa_id = null)
    {
        // Get data dari view existing
        $seminar = $this->get_by_id($id, $mahasiswa_id);
        
        if (!$seminar) {
            return [
                'found' => false,
                'current_step' => null,
                'status' => null,
                'progress' => [],
                'next_action' => null
            ];
        }

        // Progress steps sesuai workflow database
        $steps = [
            [
                'key' => 'pengajuan',
                'title' => 'Pengajuan Mahasiswa',
                'icon' => 'fa-paper-plane',
                'description' => 'Upload proposal dan submit pengajuan',
                'completed' => in_array($seminar->status, ['submitted', 'review_pembimbing', 'review_kaprodi', 'approved', 'scheduled', 'completed']),
                'active' => $seminar->status == 'draft'
            ],
            [
                'key' => 'review_pembimbing',
                'title' => 'Review Pembimbing',
                'icon' => 'fa-user-graduate',
                'description' => 'Dosen pembimbing memberikan rekomendasi',
                'completed' => in_array($seminar->status, ['review_kaprodi', 'approved', 'scheduled', 'completed']),
                'active' => in_array($seminar->status, ['submitted', 'review_pembimbing'])
            ],
            [
                'key' => 'review_kaprodi',
                'title' => 'Review Kaprodi',
                'icon' => 'fa-user-tie',
                'description' => 'Kaprodi melakukan validasi dan cek plagiarisme',
                'completed' => in_array($seminar->status, ['approved', 'scheduled', 'completed']),
                'active' => $seminar->status == 'review_kaprodi'
            ],
            [
                'key' => 'penjadwalan',
                'title' => 'Penjadwalan',
                'icon' => 'fa-calendar',
                'description' => 'Penentuan jadwal, tempat, dan penguji',
                'completed' => in_array($seminar->status, ['scheduled', 'completed']),
                'active' => $seminar->status == 'approved'
            ],
            [
                'key' => 'pelaksanaan',
                'title' => 'Pelaksanaan Seminar',
                'icon' => 'fa-presentation',
                'description' => 'Seminar proposal dilaksanakan',
                'completed' => $seminar->status == 'completed',
                'active' => $seminar->status == 'scheduled'
            ]
        ];

        // Next action untuk mahasiswa
        $next_actions = [
            'draft' => 'Lengkapi dan submit pengajuan',
            'submitted' => 'Menunggu review dosen pembimbing',
            'review_pembimbing' => 'Pembimbing sedang melakukan review',
            'review_kaprodi' => 'Kaprodi sedang melakukan validasi',
            'approved' => 'Menunggu penjadwalan seminar',
            'scheduled' => 'Bersiap untuk pelaksanaan seminar',
            'completed' => 'Menunggu hasil penilaian',
            'rejected' => 'Perbaiki berdasarkan catatan dan ajukan ulang'
        ];

        return [
            'found' => true,
            'current_step' => $seminar->current_step,
            'status' => $seminar->status,
            'status_description' => $seminar->status_description,
            'progress' => $steps,
            'next_action' => $next_actions[$seminar->status] ?? 'Status tidak dikenal',
            'seminar' => $seminar
        ];
    }

    // =================================================================
    // UTILITY METHODS
    // =================================================================

    /**
     * Get proposal data untuk validasi (READ ONLY dari existing table)
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
        $this->db->join('dosen k', 'p.dosen_id = k.id', 'left'); // Kaprodi
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
        
        return 'seminar_proposal_' . $mahasiswa_id . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Get allowed file types
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
        return 1 * 1024 * 1024; // 1MB sesuai database constraint
    }

    // =================================================================
    // FUTURE DEVELOPMENT METHODS (Untuk Role Lain)
    // =================================================================

    /**
     * Get list untuk dosen pembimbing (Future - Phase 3B)
     */
    public function get_for_pembimbing($dosen_id)
    {
        $this->db->select('*');
        $this->db->from($this->view);
        $this->db->where('pembimbing_id', $dosen_id);
        $this->db->where('status_pembimbing', 'pending');
        $this->db->order_by('created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get list untuk kaprodi (Future - Phase 3C)
     */
    public function get_for_kaprodi($prodi_id = null)
    {
        $this->db->select('*');
        $this->db->from($this->view);
        $this->db->where('status_kaprodi', 'pending');
        $this->db->where('status_pembimbing', 'approved'); // Harus sudah approved pembimbing
        
        if ($prodi_id) {
            // Filter by prodi if specified
            $this->db->where('prodi_id', $prodi_id);
        }
        
        $this->db->order_by('created_at', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get list untuk staf (Future - Phase 3D)
     */
    public function get_for_staf()
    {
        $this->db->select('*');
        $this->db->from($this->view);
        $this->db->where('status', 'approved'); // Sudah approved kaprodi, siap dijadwalkan
        $this->db->order_by('tanggal_review_kaprodi', 'ASC');
        
        return $this->db->get()->result();
    }
}