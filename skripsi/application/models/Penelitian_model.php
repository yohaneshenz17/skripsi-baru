<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Penelitian Model - FIXED FOR EXISTING DATABASE STRUCTURE
 * 
 * Model yang diperbaiki sesuai dengan struktur database yang sudah ada
 * Menggunakan FK proposal_mahasiswa_id, bukan mahasiswa_id langsung
 * FIXED: Menghapus field updated_at yang tidak ada di database
 * 
 * File: application/models/Penelitian_model.php
 * 
 * @package     SIM_TA
 * @subpackage  Models
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.2 (Fixed Database Fields)
 */
class Penelitian_model extends CI_Model
{
    protected $table = "permohonan_izin_penelitian";

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // =================================================================
    // METHODS FIXED FOR EXISTING DATABASE STRUCTURE
    // =================================================================

    /**
     * Cek syarat eligibility mahasiswa untuk mengajukan izin penelitian
     * FIXED: Menggunakan struktur database yang benar
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function check_eligibility($proposal_id, $mahasiswa_id)
    {
        try {
            // 1. Cek seminar proposal completed dengan struktur yang benar
            $this->db->select('
                pm.id, pm.judul, pm.workflow_status, pm.mahasiswa_id,
                spm.status as status_seminar,
                psp.status_penilaian, psp.published_at
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('seminar_proposal_mahasiswa spm', 'pm.id = spm.proposal_id', 'left');
            $this->db->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id AND psp.status_penilaian = "published"', 'left');
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            
            $proposal = $this->db->get()->row();
            
            if (!$proposal) {
                return [
                    'error' => true,
                    'message' => 'Proposal tidak ditemukan',
                    'eligible' => false
                ];
            }

            // Check seminar proposal completed (status completed + penilaian published)
            $seminar_ok = ($proposal->status_seminar == 'completed' && 
                          $proposal->status_penilaian == 'published');

            // 2. Cek jurnal bimbingan minimal 9
            $this->db->select('COUNT(*) as total');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1');
            
            $jurnal_count = $this->db->get()->row()->total;
            $jurnal_ok = ($jurnal_count >= 9);

            $eligible = $seminar_ok && $jurnal_ok;
            
            return [
                'error' => false,
                'eligible' => $eligible,
                'requirements' => [
                    'seminar_proposal' => [
                        'status' => $seminar_ok ? 'OK' : 'BELUM',
                        'detail' => $seminar_ok ? 'Seminar proposal selesai dan penilaian dipublikasi' : 'Seminar proposal belum selesai atau penilaian belum dipublikasi'
                    ],
                    'jurnal_bimbingan' => [
                        'status' => $jurnal_ok ? 'OK' : 'KURANG',
                        'count' => $jurnal_count,
                        'required' => 9,
                        'detail' => $jurnal_ok ? "Memiliki {$jurnal_count} jurnal tervalidasi" : "Hanya {$jurnal_count} dari 9 jurnal yang diperlukan"
                    ]
                ],
                'message' => $eligible ? 'Memenuhi syarat mengajukan izin penelitian' : 'Belum memenuhi syarat'
            ];

        } catch (Exception $e) {
            log_message('error', 'Error check eligibility: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Terjadi kesalahan sistem',
                'eligible' => false
            ];
        }
    }

    /**
     * Get permohonan izin penelitian by mahasiswa
     * FIXED: Menggunakan JOIN dengan proposal_mahasiswa
     * 
     * @param int $mahasiswa_id
     * @return array
     */
    public function get_permohonan_by_mahasiswa($mahasiswa_id)
    {
        try {
            $this->db->select('
                pip.*,
                pm.judul as judul_proposal,
                pm.workflow_status,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing
            ');
            $this->db->from($this->table . ' pip');
            $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
            $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id', 'left');
            // FIXED: Gunakan pm.mahasiswa_id, bukan pip.mahasiswa_id
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('pip.created_at', 'DESC');
            
            $result = $this->db->get()->result();
            
            return [
                'error' => false,
                'data' => $result
            ];

        } catch (Exception $e) {
            log_message('error', 'Error get permohonan: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Gagal mengambil data permohonan',
                'data' => []
            ];
        }
    }

    /**
     * Get detail permohonan
     * FIXED: Menggunakan struktur JOIN yang benar
     * 
     * @param int $permohonan_id
     * @param int $mahasiswa_id (optional untuk validasi)
     * @return array
     */
    public function get_permohonan_detail($permohonan_id, $mahasiswa_id = null)
    {
        try {
            $this->db->select('
                pip.*,
                pm.judul as judul_proposal,
                pm.workflow_status,
                pm.mahasiswa_id,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                d.nip as nip_pembimbing,
                m.nama as nama_mahasiswa_db,
                m.nim as nim_mahasiswa_db
            ');
            $this->db->from($this->table . ' pip');
            $this->db->join('proposal_mahasiswa pm', 'pip.proposal_mahasiswa_id = pm.id');
            $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id', 'left');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'left');
            $this->db->where('pip.id', $permohonan_id);
            
            // FIXED: Validasi menggunakan pm.mahasiswa_id
            if ($mahasiswa_id) {
                $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            }
            
            $result = $this->db->get()->row();
            
            if (!$result) {
                return [
                    'error' => true,
                    'message' => 'Permohonan tidak ditemukan'
                ];
            }

            return [
                'error' => false,
                'data' => $result
            ];

        } catch (Exception $e) {
            log_message('error', 'Error get detail: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Gagal mengambil detail permohonan'
            ];
        }
    }

    /**
     * Create permohonan baru
     * FIXED: Sesuai struktur database existing + HAPUS field updated_at
     * 
     * @param array $data
     * @return array
     */
    public function create_permohonan($data)
    {
        $this->db->trans_start();
        
        try {
            // Basic validation
            if (empty($data['proposal_mahasiswa_id'])) {
                throw new Exception('proposal_mahasiswa_id tidak boleh kosong');
            }

            // Validasi proposal exists dan milik mahasiswa yang benar
            $this->db->select('pm.id, pm.mahasiswa_id, pm.judul, pm.dosen_id');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->where('pm.id', $data['proposal_mahasiswa_id']);
            if (isset($data['mahasiswa_id'])) {
                $this->db->where('pm.mahasiswa_id', $data['mahasiswa_id']);
            }
            
            $proposal_check = $this->db->get()->row();
            if (!$proposal_check) {
                throw new Exception('Proposal tidak ditemukan atau bukan milik Anda');
            }

            // Check eligibility
            $eligibility = $this->check_eligibility($data['proposal_mahasiswa_id'], $proposal_check->mahasiswa_id);
            if (!$eligibility['eligible']) {
                throw new Exception($eligibility['message']);
            }

            // Check existing permohonan (menggunakan unique key yang sudah ada)
            $existing = $this->db->get_where($this->table, [
                'proposal_mahasiswa_id' => $data['proposal_mahasiswa_id']
            ])->row();

            if ($existing && $existing->status != 'rejected') {
                throw new Exception('Sudah ada permohonan yang sedang diproses untuk proposal ini');
            }

            // FIXED: Insert data sesuai struktur tabel yang ada (tanpa mahasiswa_id)
            $insert_data = [
                'proposal_mahasiswa_id' => $data['proposal_mahasiswa_id'],
                'nama_mahasiswa' => strtoupper($data['nama_mahasiswa']),
                'nim' => $data['nim'],
                'semester' => $data['semester'],
                'program_studi' => $data['program_studi'],
                'judul_skripsi_terbaru' => $data['judul_skripsi_terbaru'],
                'tempat_penelitian' => $data['tempat_penelitian'],
                'tanggal_mulai_penelitian' => $data['tanggal_mulai_penelitian'],
                'tanggal_selesai_penelitian' => $data['tanggal_selesai_penelitian'],
                'dosen_pembimbing_id' => $data['dosen_pembimbing_id'],
                'status' => 'submitted',
                'status_pembimbing' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
                // REMOVED: 'updated_at' karena field ini tidak ada di database
            ];

            // Handle file upload jika ada
            if (isset($data['file_proposal_revisi']) && !empty($data['file_proposal_revisi'])) {
                $insert_data['file_proposal_revisi'] = $data['file_proposal_revisi'];
            }

            // Insert ke database
            $this->db->insert($this->table, $insert_data);
            $permohonan_id = $this->db->insert_id();

            // FIXED: Update proposal workflow status (TANPA updated_at)
            $this->db->where('id', $data['proposal_mahasiswa_id']);
            $this->db->update('proposal_mahasiswa', [
                'workflow_status' => 'penelitian'
                // REMOVED: 'updated_at' karena field ini tidak ada di tabel proposal_mahasiswa
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan permohonan');
            }

            return [
                'error' => false,
                'message' => 'Permohonan berhasil diajukan',
                'data' => ['permohonan_id' => $permohonan_id]
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error create permohonan: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update status permohonan
     * FIXED: HAPUS updated_at field
     * 
     * @param int $permohonan_id
     * @param string $status
     * @param array $additional_data
     * @return array
     */
    public function update_status($permohonan_id, $status, $additional_data = [])
    {
        try {
            $update_data = array_merge([
                'status' => $status
                // REMOVED: 'updated_at' karena field ini tidak ada di database
            ], $additional_data);

            $this->db->where('id', $permohonan_id);
            $this->db->update($this->table, $update_data);

            if ($this->db->affected_rows() > 0) {
                return [
                    'error' => false,
                    'message' => 'Status berhasil diupdate'
                ];
            } else {
                return [
                    'error' => true,
                    'message' => 'Gagal update status'
                ];
            }

        } catch (Exception $e) {
            log_message('error', 'Error update status: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Terjadi kesalahan sistem'
            ];
        }
    }

    /**
     * Menggunakan view yang sudah tersedia untuk dashboard
     * BONUS: Manfaatkan v_penelitian_dashboard yang sudah ada
     * 
     * @param int $mahasiswa_id
     * @return array
     */
    public function get_dashboard_data($mahasiswa_id)
    {
        try {
            // Gunakan view yang sudah tersedia
            $this->db->select('*');
            $this->db->from('v_penelitian_dashboard');
            $this->db->join('proposal_mahasiswa pm', 'v_penelitian_dashboard.proposal_mahasiswa_id = pm.id');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('v_penelitian_dashboard.tanggal_pengajuan', 'DESC');
            
            $result = $this->db->get()->result();
            
            return [
                'error' => false,
                'data' => $result
            ];

        } catch (Exception $e) {
            log_message('error', 'Error get dashboard data: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Gagal mengambil data dashboard',
                'data' => []
            ];
        }
    }

    // =================================================================
    // LEGACY COMPATIBILITY - MINIMAL
    // =================================================================

    /**
     * Legacy method untuk compatibility dengan API controller existing
     */
    public function index($input)
    {
        if (isset($input['mahasiswa_id'])) {
            return $this->get_permohonan_by_mahasiswa($input['mahasiswa_id']);
        }
        
        return ['error' => true, 'message' => 'Parameter tidak valid'];
    }

    /**
     * Legacy method untuk compatibility
     */
    public function create($input)
    {
        return $this->create_permohonan($input);
    }

    /**
     * Legacy method untuk compatibility
     */
    public function details($id)
    {
        return $this->get_permohonan_detail($id);
    }

    /**
     * Legacy method - not used but kept for compatibility
     */
    public function destroy($id)
    {
        return ['error' => true, 'message' => 'Method not implemented'];
    }
}

/* End of file Penelitian_model.php */
/* Location: ./application/models/Penelitian_model.php */