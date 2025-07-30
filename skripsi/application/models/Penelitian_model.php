<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Penelitian Model - Tahap 4 Workflow
 * 
 * Model untuk mengelola permohonan izin penelitian mahasiswa
 * sesuai dengan workflow tahap 4 yang telah didefinisikan.
 * 
 * Features:
 * - Validasi syarat pengajuan (seminar proposal + jurnal bimbingan)
 * - Manajemen permohonan izin penelitian
 * - Upload dan tracking file proposal revisi
 * - Workflow pembimbing review dan staf upload surat
 * - Integration dengan tabel existing proposal_mahasiswa
 * 
 * File: application/models/Penelitian_model.php
 * 
 * @package     SIM_TA
 * @subpackage  Models
 * @category    Penelitian
 * @author      Unit SIPD STK Santo Yakobus
 * @version     2.0 (Workflow Tahap 4)
 */
class Penelitian_model extends CI_Model
{
    // Tabel utama untuk permohonan izin penelitian (tabel baru)
    protected $table = "permohonan_izin_penelitian";
    
    // Tabel existing untuk integrasi
    protected $proposal_table = "proposal_mahasiswa";
    protected $jurnal_table = "jurnal_bimbingan";
    protected $seminar_table = "seminar_proposal_mahasiswa";
    protected $penilaian_table = "penilaian_seminar_proposal";

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('upload');
        $this->load->helper(['file', 'security']);
    }

    // =================================================================
    // VALIDASI SYARAT PENGAJUAN IZIN PENELITIAN
    // =================================================================

    /**
     * Cek apakah mahasiswa memenuhi syarat untuk mengajukan izin penelitian
     * Syarat: 1) Seminar proposal selesai + penilaian published, 2) Minimal 9 jurnal tervalidasi
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function check_eligibility($proposal_id, $mahasiswa_id)
    {
        try {
            // 1. Cek syarat seminar proposal
            $this->db->select('
                pm.id as proposal_id,
                pm.mahasiswa_id,
                pm.judul,
                pm.workflow_status,
                spm.status as status_seminar,
                psp.status_penilaian,
                psp.rekomendasi,
                psp.published_at
            ');
            $this->db->from($this->proposal_table . ' pm');
            $this->db->join($this->seminar_table . ' spm', 'pm.id = spm.proposal_id', 'left');
            $this->db->join($this->penilaian_table . ' psp', 'spm.id = psp.seminar_proposal_id AND psp.status_penilaian = "published"', 'left');
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            
            $proposal_data = $this->db->get()->row();
            
            if (!$proposal_data) {
                return [
                    'error' => true,
                    'message' => 'Proposal tidak ditemukan atau bukan milik Anda',
                    'eligible' => false
                ];
            }

            // Cek syarat seminar proposal
            $seminar_ok = ($proposal_data->status_seminar == 'completed' && 
                          $proposal_data->status_penilaian == 'published');

            // 2. Cek syarat jurnal bimbingan (minimal 9 tervalidasi)
            $this->db->select('COUNT(*) as total_tervalidasi');
            $this->db->from($this->jurnal_table);
            $this->db->where('proposal_id', $proposal_id);
            $this->db->where('status_validasi', '1'); // 1 = tervalidasi
            
            $jurnal_result = $this->db->get()->row();
            $jurnal_count = $jurnal_result ? (int)$jurnal_result->total_tervalidasi : 0;
            $jurnal_ok = ($jurnal_count >= 9);

            // 3. Hasil eligibility
            $eligible = $seminar_ok && $jurnal_ok;
            
            $result = [
                'error' => false,
                'eligible' => $eligible,
                'proposal_data' => $proposal_data,
                'requirements' => [
                    'seminar_proposal' => [
                        'status' => $seminar_ok ? 'OK' : 'BELUM',
                        'detail' => $seminar_ok ? 'Seminar proposal selesai dan penilaian sudah dipublikasi' : 
                                   'Seminar proposal belum selesai atau penilaian belum dipublikasi'
                    ],
                    'jurnal_bimbingan' => [
                        'status' => $jurnal_ok ? 'OK' : 'KURANG',
                        'count' => $jurnal_count,
                        'required' => 9,
                        'detail' => $jurnal_ok ? "Memiliki {$jurnal_count} jurnal tervalidasi" : 
                                   "Hanya {$jurnal_count} dari 9 jurnal yang diperlukan"
                    ]
                ],
                'message' => $eligible ? 'Memenuhi syarat untuk mengajukan izin penelitian' : 
                            'Belum memenuhi syarat untuk mengajukan izin penelitian'
            ];

            return $result;

        } catch (Exception $e) {
            log_message('error', 'Error checking eligibility: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Terjadi kesalahan saat memvalidasi syarat',
                'eligible' => false
            ];
        }
    }

    // =================================================================
    // MANAJEMEN PERMOHONAN IZIN PENELITIAN
    // =================================================================

    /**
     * Ambil daftar permohonan izin penelitian mahasiswa
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
            $this->db->join($this->proposal_table . ' pm', 'pip.proposal_mahasiswa_id = pm.id');
            $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id', 'left');
            $this->db->where('pip.mahasiswa_id', $mahasiswa_id);
            $this->db->order_by('pip.created_at', 'DESC');
            
            $permohonan_list = $this->db->get()->result();
            
            return [
                'error' => false,
                'message' => 'Data berhasil diambil',
                'data' => $permohonan_list
            ];

        } catch (Exception $e) {
            log_message('error', 'Error getting permohonan: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengambil data permohonan'
            ];
        }
    }

    /**
     * Ambil detail permohonan berdasarkan ID
     * 
     * @param int $permohonan_id
     * @param int $mahasiswa_id (untuk validasi ownership)
     * @return array
     */
    public function get_permohonan_detail($permohonan_id, $mahasiswa_id = null)
    {
        try {
            $this->db->select('
                pip.*,
                pm.judul as judul_proposal,
                pm.workflow_status,
                pm.status_izin_penelitian,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                d.nip as nip_pembimbing
            ');
            $this->db->from($this->table . ' pip');
            $this->db->join($this->proposal_table . ' pm', 'pip.proposal_mahasiswa_id = pm.id');
            $this->db->join('dosen d', 'pip.dosen_pembimbing_id = d.id', 'left');
            $this->db->where('pip.id', $permohonan_id);
            
            if ($mahasiswa_id) {
                $this->db->where('pip.mahasiswa_id', $mahasiswa_id);
            }
            
            $permohonan = $this->db->get()->row();
            
            if (!$permohonan) {
                return [
                    'error' => true,
                    'message' => 'Permohonan tidak ditemukan atau bukan milik Anda'
                ];
            }

            return [
                'error' => false,
                'message' => 'Data berhasil ditemukan',
                'data' => $permohonan
            ];

        } catch (Exception $e) {
            log_message('error', 'Error getting permohonan detail: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Terjadi kesalahan saat mengambil detail permohonan'
            ];
        }
    }

    /**
     * Buat permohonan izin penelitian baru
     * 
     * @param array $input_data
     * @return array
     */
    public function create_permohonan($input_data)
    {
        $this->db->trans_start();
        
        try {
            // 1. Validasi input required
            $required_fields = [
                'proposal_mahasiswa_id', 'mahasiswa_id', 'nama_mahasiswa', 
                'nim', 'semester', 'program_studi', 'judul_skripsi_terbaru',
                'tempat_penelitian', 'tanggal_mulai_penelitian', 
                'tanggal_selesai_penelitian', 'dosen_pembimbing_id'
            ];

            foreach ($required_fields as $field) {
                if (!isset($input_data[$field]) || empty($input_data[$field])) {
                    throw new Exception("Field {$field} wajib diisi");
                }
            }

            // 2. Validasi eligibility terlebih dahulu
            $eligibility = $this->check_eligibility($input_data['proposal_mahasiswa_id'], $input_data['mahasiswa_id']);
            if (!$eligibility['eligible']) {
                throw new Exception($eligibility['message']);
            }

            // 3. Cek apakah sudah ada permohonan active untuk proposal ini
            $existing = $this->db->get_where($this->table, [
                'proposal_mahasiswa_id' => $input_data['proposal_mahasiswa_id'],
                'status !=' => 'rejected'
            ])->row();

            if ($existing) {
                throw new Exception('Sudah ada permohonan yang sedang diproses untuk proposal ini');
            }

            // 4. Handle file upload proposal revisi
            $file_proposal_revisi = null;
            if (isset($input_data['file_proposal_revisi']) && !empty($input_data['file_proposal_revisi'])) {
                $upload_result = $this->_handle_file_upload($input_data['file_proposal_revisi'], 'proposal_revisi');
                if ($upload_result['error']) {
                    throw new Exception($upload_result['message']);
                }
                $file_proposal_revisi = $upload_result['file_name'];
            }

            // 5. Prepare data untuk insert
            $insert_data = [
                'proposal_mahasiswa_id' => $input_data['proposal_mahasiswa_id'],
                'mahasiswa_id' => $input_data['mahasiswa_id'],
                'nama_mahasiswa' => strtoupper($input_data['nama_mahasiswa']),
                'nim' => $input_data['nim'],
                'semester' => $input_data['semester'],
                'program_studi' => $input_data['program_studi'],
                'judul_skripsi_terbaru' => $input_data['judul_skripsi_terbaru'],
                'tempat_penelitian' => $input_data['tempat_penelitian'],
                'tanggal_mulai_penelitian' => $input_data['tanggal_mulai_penelitian'],
                'tanggal_selesai_penelitian' => $input_data['tanggal_selesai_penelitian'],
                'dosen_pembimbing_id' => $input_data['dosen_pembimbing_id'],
                'file_proposal_revisi' => $file_proposal_revisi,
                'status' => 'submitted',
                'status_pembimbing' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // 6. Insert ke database
            $this->db->insert($this->table, $insert_data);
            $permohonan_id = $this->db->insert_id();

            // 7. Update workflow_status di proposal_mahasiswa
            $this->db->where('id', $input_data['proposal_mahasiswa_id']);
            $this->db->update($this->proposal_table, [
                'workflow_status' => 'penelitian',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // 8. Log aktivitas
            $this->_log_activity($permohonan_id, $input_data['mahasiswa_id'], 'mahasiswa', 
                               'create_permohonan', 'Mahasiswa mengajukan permohonan izin penelitian');

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan permohonan izin penelitian');
            }

            return [
                'error' => false,
                'message' => 'Permohonan izin penelitian berhasil diajukan',
                'data' => ['permohonan_id' => $permohonan_id]
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error creating permohonan: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    // =================================================================
    // DROPDOWN DATA HELPERS
    // =================================================================

    /**
     * Ambil daftar dosen untuk dropdown pembimbing
     * 
     * @return array
     */
    public function get_dosen_list()
    {
        try {
            $this->db->select('id, nama, nip, email');
            $this->db->from('dosen');
            $this->db->where('level', '2'); // Level 2 = dosen
            $this->db->order_by('nama', 'ASC');
            
            $dosen_list = $this->db->get()->result();
            
            return [
                'error' => false,
                'data' => $dosen_list
            ];

        } catch (Exception $e) {
            log_message('error', 'Error getting dosen list: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Gagal mengambil daftar dosen'
            ];
        }
    }

    /**
     * Ambil proposal mahasiswa untuk form (pre-fill data)
     * 
     * @param int $proposal_id
     * @param int $mahasiswa_id
     * @return array
     */
    public function get_proposal_data($proposal_id, $mahasiswa_id)
    {
        try {
            $this->db->select('
                pm.*,
                m.nim, m.nama as nama_mahasiswa,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing, d.id as dosen_pembimbing_id
            ');
            $this->db->from($this->proposal_table . ' pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.id', $proposal_id);
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            
            $proposal = $this->db->get()->row();
            
            if (!$proposal) {
                return [
                    'error' => true,
                    'message' => 'Data proposal tidak ditemukan'
                ];
            }

            return [
                'error' => false,
                'data' => $proposal
            ];

        } catch (Exception $e) {
            log_message('error', 'Error getting proposal data: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Gagal mengambil data proposal'
            ];
        }
    }

    // =================================================================
    // FILE HANDLING
    // =================================================================

    /**
     * Handle file upload dengan validasi keamanan
     * 
     * @param string $file_data (base64 atau file upload)
     * @param string $subfolder
     * @return array
     */
    private function _handle_file_upload($file_data, $subfolder = 'proposal_revisi')
    {
        try {
            // Direktori upload
            $upload_path = FCPATH . 'uploads/penelitian/' . $subfolder . '/';
            
            // Buat direktori jika belum ada
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            // Handle base64 upload (dari form)
            if (strpos($file_data, 'data:') === 0) {
                // Parse base64 data
                $file_parts = explode(';base64,', $file_data);
                if (count($file_parts) !== 2) {
                    throw new Exception('Format file tidak valid');
                }

                $file_type = str_replace('data:', '', $file_parts[0]);
                $file_content = base64_decode($file_parts[1]);
                
                // Validasi tipe file
                $allowed_types = ['application/pdf', 'application/msword', 
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception('Tipe file tidak diizinkan. Hanya PDF dan Word yang diperbolehkan');
                }

                // Generate nama file unik
                $extension = ($file_type === 'application/pdf') ? '.pdf' : 
                           (($file_type === 'application/msword') ? '.doc' : '.docx');
                $file_name = 'proposal_' . date('YmdHis') . '_' . uniqid() . $extension;
                
                // Simpan file
                $file_path = $upload_path . $file_name;
                if (!file_put_contents($file_path, $file_content)) {
                    throw new Exception('Gagal menyimpan file');
                }

                // Validasi ukuran file (max 2MB)
                if (filesize($file_path) > 2 * 1024 * 1024) {
                    unlink($file_path);
                    throw new Exception('Ukuran file terlalu besar. Maksimal 2MB');
                }

                return [
                    'error' => false,
                    'file_name' => $file_name,
                    'file_path' => $file_path
                ];
            }

            throw new Exception('Format upload tidak didukung');

        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    // =================================================================
    // ACTIVITY LOGGING
    // =================================================================

    /**
     * Log aktivitas untuk audit trail
     * 
     * @param int $permohonan_id
     * @param int $user_id
     * @param string $user_role
     * @param string $aktivitas
     * @param string $deskripsi
     */
    private function _log_activity($permohonan_id, $user_id, $user_role, $aktivitas, $deskripsi)
    {
        try {
            // Cek apakah tabel log ada
            if (!$this->db->table_exists('log_penelitian')) {
                return; // Skip jika tabel belum dibuat
            }

            $log_data = [
                'permohonan_id' => $permohonan_id,
                'user_id' => $user_id,
                'user_role' => $user_role,
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsi,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('log_penelitian', $log_data);

        } catch (Exception $e) {
            log_message('debug', 'Log activity error: ' . $e->getMessage());
            // Tidak perlu throw exception, karena ini hanya logging
        }
    }

    // =================================================================
    // LEGACY COMPATIBILITY (untuk backward compatibility)
    // =================================================================

    /**
     * Method untuk kompatibilitas dengan sistem lama
     * DEPRECATED: Gunakan method baru di atas
     */
    public function index($input)
    {
        // Redirect ke method baru
        if (isset($input['mahasiswa_id'])) {
            return $this->get_permohonan_by_mahasiswa($input['mahasiswa_id']);
        }
        
        return [
            'error' => true,
            'message' => 'Parameter tidak valid'
        ];
    }

    /**
     * Method untuk kompatibilitas dengan sistem lama  
     * DEPRECATED: Gunakan create_permohonan()
     */
    public function create($input)
    {
        return $this->create_permohonan($input);
    }

    /**
     * Method untuk kompatibilitas dengan sistem lama
     * DEPRECATED: Gunakan get_permohonan_detail()
     */
    public function details($id)
    {
        return $this->get_permohonan_detail($id);
    }
}

/* End of file Penelitian_model.php */
/* Location: ./application/models/Penelitian_model.php */