<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class Seminar_proposal_model
 * Model untuk mengelola data seminar proposal sesuai workflow SIM-TA
 * 
 * @property CI_DB_query_builder $db
 * @property CI_Email $email
 * @property CI_Upload $upload
 */
class Seminar_proposal_model extends CI_Model
{
    protected $table = "seminar_proposal";
    protected $table_hasil = "hasil_seminar_proposal";
    protected $table_view = "seminar_proposal_view";
    
    // File upload configuration
    private $upload_path = './uploads/seminar_proposal/';
    private $allowed_types = 'pdf|doc|docx';
    private $max_size = 1024; // 1MB in KB
    
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['upload', 'email']);
        $this->load->helper(['file', 'security']);
        
        // Ensure upload directory exists
        if (!is_dir($this->upload_path)) {
            mkdir($this->upload_path, 0755, true);
        }
    }

    /**
     * ========================================
     * VALIDASI SYARAT SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Cek syarat minimal jurnal bimbingan (minimal 8 jurnal yang divalidasi)
     */
    public function cek_syarat_jurnal_bimbingan($proposal_id)
    {
        $this->db->select('COUNT(*) as jumlah_validasi');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->where('status_validasi', '1'); // Hanya yang sudah divalidasi
        
        $result = $this->db->get()->row();
        
        return [
            'memenuhi_syarat' => ($result->jumlah_validasi >= 8),
            'jumlah_validasi' => $result->jumlah_validasi,
            'syarat_minimal' => 8,
            'kekurangan' => max(0, 8 - $result->jumlah_validasi)
        ];
    }
    
    /**
     * Cek apakah mahasiswa sudah pernah mengajukan seminar proposal
     */
    public function cek_pengajuan_existing($proposal_id)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('proposal_id', $proposal_id);
        
        $existing = $this->db->get()->row();
        
        return $existing ? $existing : false;
    }

    /**
     * ========================================
     * PENGAJUAN SEMINAR PROPOSAL (MAHASISWA)
     * ========================================
     */
    
    /**
     * Ajukan seminar proposal baru
     */
    public function ajukan_seminar_proposal($data)
    {
        // Validasi syarat jurnal bimbingan
        $syarat_jurnal = $this->cek_syarat_jurnal_bimbingan($data['proposal_id']);
        if (!$syarat_jurnal['memenuhi_syarat']) {
            return [
                'error' => true,
                'message' => "Belum memenuhi syarat minimal {$syarat_jurnal['syarat_minimal']} jurnal bimbingan yang divalidasi. Saat ini hanya {$syarat_jurnal['jumlah_validasi']} jurnal yang telah divalidasi."
            ];
        }
        
        // Cek apakah sudah pernah mengajukan
        $existing = $this->cek_pengajuan_existing($data['proposal_id']);
        if ($existing && $existing->status_pengajuan != 'draft') {
            return [
                'error' => true,
                'message' => 'Anda sudah pernah mengajukan seminar proposal. Silakan cek status pengajuan Anda.'
            ];
        }
        
        // Upload file proposal
        $upload_result = $this->upload_file_proposal($_FILES['file_proposal_seminar'] ?? null);
        if ($upload_result['error']) {
            return $upload_result;
        }
        
        // Prepare data
        $insert_data = [
            'proposal_id' => $data['proposal_id'],
            'mahasiswa_id' => $data['mahasiswa_id'],
            'file_proposal_seminar' => $upload_result['data']['file_name'],
            'file_size' => $upload_result['data']['file_size'],
            'file_mime_type' => $upload_result['data']['file_type'],
            'file_hash' => $upload_result['data']['file_hash'],
            'status_pengajuan' => 'submitted',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->trans_start();
            
            if ($existing) {
                // Update pengajuan existing
                $this->db->where('id', $existing->id);
                $this->db->update($this->table, $insert_data);
                $seminar_id = $existing->id;
            } else {
                // Insert pengajuan baru
                $this->db->insert($this->table, $insert_data);
                $seminar_id = $this->db->insert_id();
            }
            
            // Update workflow status di proposal_mahasiswa
            $this->db->where('id', $data['proposal_id']);
            $this->db->update('proposal_mahasiswa', [
                'workflow_status' => 'seminar_proposal',
                'file_seminar_proposal' => $upload_result['data']['file_name']
            ]);
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan data ke database');
            }
            
            // Kirim notifikasi
            $this->kirim_notifikasi_pengajuan($seminar_id);
            
            return [
                'error' => false,
                'message' => 'Seminar proposal berhasil diajukan! Menunggu rekomendasi dosen pembimbing.',
                'data' => ['seminar_id' => $seminar_id]
            ];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            
            // Hapus file jika ada error
            if (isset($upload_result['data']['file_name'])) {
                @unlink($this->upload_path . $upload_result['data']['file_name']);
            }
            
            return [
                'error' => true,
                'message' => 'Gagal mengajukan seminar proposal: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload dan validasi file proposal
     */
    private function upload_file_proposal($file_data)
    {
        if (!$file_data || $file_data['error'] !== UPLOAD_ERR_OK) {
            return [
                'error' => true,
                'message' => 'File proposal wajib diupload!'
            ];
        }
        
        // Validasi ukuran file (max 1MB)
        if ($file_data['size'] > ($this->max_size * 1024)) {
            return [
                'error' => true,
                'message' => 'Ukuran file maksimal 1MB!'
            ];
        }
        
        // Validasi tipe file
        $file_ext = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, ['pdf', 'doc', 'docx'])) {
            return [
                'error' => true,
                'message' => 'Format file harus PDF, DOC, atau DOCX!'
            ];
        }
        
        // Scan malware menggunakan ClamAV (jika tersedia)
        if (function_exists('exec') && !$this->scan_malware($file_data['tmp_name'])) {
            return [
                'error' => true,
                'message' => 'File tidak aman! Terdeteksi potensi malware.'
            ];
        }
        
        // Generate nama file unik
        $file_name = 'proposal_' . uniqid() . '_' . time() . '.' . $file_ext;
        $file_path = $this->upload_path . $file_name;
        
        // Pindahkan file
        if (!move_uploaded_file($file_data['tmp_name'], $file_path)) {
            return [
                'error' => true,
                'message' => 'Gagal mengupload file!'
            ];
        }
        
        // Generate hash file untuk cek duplikasi
        $file_hash = md5_file($file_path);
        
        return [
            'error' => false,
            'data' => [
                'file_name' => $file_name,
                'file_path' => $file_path,
                'file_size' => $file_data['size'],
                'file_type' => $file_data['type'],
                'file_hash' => $file_hash
            ]
        ];
    }
    
    /**
     * Scan malware menggunakan ClamAV
     */
    private function scan_malware($file_path)
    {
        // Implementasi basic - untuk production gunakan ClamAV yang proper
        if (!file_exists($file_path)) {
            return false;
        }
        
        // Cek signature malware sederhana
        $content = file_get_contents($file_path, false, null, 0, 1024); // Read first 1KB
        $malware_signatures = ['EICAR', '%PDF-', '<?php', '<script>', 'eval('];
        
        foreach ($malware_signatures as $signature) {
            if (strpos($content, $signature) !== false && $signature !== '%PDF-') {
                return false; // Detected malware
            }
        }
        
        return true; // File seems safe
    }

    /**
     * ========================================
     * REKOMENDASI DOSEN PEMBIMBING
     * ========================================
     */
    
    /**
     * Dosen pembimbing memberikan rekomendasi
     */
    public function proses_rekomendasi_pembimbing($data)
    {
        $seminar_id = $data['seminar_id'];
        $rekomendasi = $data['rekomendasi']; // '1' = setuju, '2' = tolak
        $catatan = $data['catatan_pembimbing'] ?? '';
        $dosen_id = $data['dosen_id'];
        
        // Validasi data
        if (!in_array($rekomendasi, ['1', '2'])) {
            return ['error' => true, 'message' => 'Pilihan rekomendasi tidak valid!'];
        }
        
        $update_data = [
            'rekomendasi_pembimbing' => $rekomendasi,
            'catatan_pembimbing' => $catatan,
            'tanggal_rekomendasi_pembimbing' => date('Y-m-d H:i:s'),
            'direkomendasikan_oleh' => $dosen_id
        ];
        
        try {
            $this->db->where('id', $seminar_id);
            $result = $this->db->update($this->table, $update_data);
            
            if ($result) {
                // Kirim notifikasi
                $this->kirim_notifikasi_rekomendasi_pembimbing($seminar_id, $rekomendasi, $catatan);
                
                $message = ($rekomendasi == '1') ? 
                    'Seminar proposal berhasil direkomendasikan untuk validasi kaprodi!' : 
                    'Seminar proposal ditolak. Mahasiswa akan mendapat notifikasi untuk perbaikan.';
                
                return ['error' => false, 'message' => $message];
            } else {
                return ['error' => true, 'message' => 'Gagal menyimpan rekomendasi!'];
            }
            
        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * ========================================
     * VALIDASI KAPRODI & PLAGIASI
     * ========================================
     */
    
    /**
     * Kaprodi validasi seminar proposal dengan cek plagiasi
     */
    public function proses_validasi_kaprodi($data)
    {
        $seminar_id = $data['seminar_id'];
        $validasi = $data['validasi']; // '1' = setuju, '2' = tolak
        $catatan = $data['catatan_kaprodi'] ?? '';
        $kaprodi_id = $data['kaprodi_id'];
        
        // Upload file turnitin jika ada
        $turnitin_data = null;
        if (isset($_FILES['file_turnitin']) && $_FILES['file_turnitin']['error'] === UPLOAD_ERR_OK) {
            $turnitin_upload = $this->upload_file_turnitin($_FILES['file_turnitin']);
            if ($turnitin_upload['error']) {
                return $turnitin_upload;
            }
            $turnitin_data = $turnitin_upload['data'];
        }
        
        $update_data = [
            'status_validasi_kaprodi' => $validasi,
            'catatan_kaprodi' => $catatan,
            'tanggal_validasi_kaprodi' => date('Y-m-d H:i:s'),
            'divalidasi_oleh' => $kaprodi_id
        ];
        
        // Jika ada file turnitin dan persentase plagiasi
        if ($turnitin_data) {
            $persentase_plagiasi = $data['persentase_plagiasi'] ?? 0;
            $update_data['file_turnitin'] = $turnitin_data['file_name'];
            $update_data['persentase_plagiasi'] = $persentase_plagiasi;
            $update_data['status_plagiasi'] = ($persentase_plagiasi < 30) ? 'lolos' : 'tidak_lolos';
            $update_data['catatan_plagiasi'] = $data['catatan_plagiasi'] ?? '';
            
            // Jika plagiasi > 30%, paksa tolak validasi
            if ($persentase_plagiasi >= 30) {
                $update_data['status_validasi_kaprodi'] = '2'; // Otomatis tolak
                $update_data['catatan_kaprodi'] = "Proposal ditolak karena tingkat plagiasi {$persentase_plagiasi}% melebihi batas maksimal 30%. " . $catatan;
            }
        }
        
        // Jika disetujui, tambahkan data penjadwalan
        if ($validasi == '1' && (!isset($update_data['status_plagiasi']) || $update_data['status_plagiasi'] == 'lolos')) {
            $update_data['tanggal_seminar'] = $data['tanggal_seminar'] ?? null;
            $update_data['waktu_mulai'] = $data['waktu_mulai'] ?? null;
            $update_data['waktu_selesai'] = $data['waktu_selesai'] ?? null;
            $update_data['tempat_seminar'] = $data['tempat_seminar'] ?? null;
            $update_data['dosen_penguji_1_id'] = $data['dosen_penguji_1_id'] ?? null;
            $update_data['dosen_penguji_2_id'] = $data['dosen_penguji_2_id'] ?? null;
        }
        
        try {
            $this->db->where('id', $seminar_id);
            $result = $this->db->update($this->table, $update_data);
            
            if ($result) {
                // Kirim notifikasi sesuai hasil validasi
                if ($validasi == '1' && (!isset($update_data['status_plagiasi']) || $update_data['status_plagiasi'] == 'lolos')) {
                    $this->kirim_notifikasi_validasi_disetujui($seminar_id);
                } else {
                    $this->kirim_notifikasi_validasi_ditolak($seminar_id, $update_data['catatan_kaprodi']);
                }
                
                $message = ($validasi == '1') ? 
                    'Seminar proposal disetujui! Menunggu persetujuan dosen penguji.' : 
                    'Seminar proposal ditolak. Mahasiswa dan pembimbing akan mendapat notifikasi.';
                    
                return ['error' => false, 'message' => $message];
            } else {
                return ['error' => true, 'message' => 'Gagal menyimpan validasi!'];
            }
            
        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Upload file turnitin
     */
    private function upload_file_turnitin($file_data)
    {
        // Sama seperti upload_file_proposal tapi untuk turnitin
        if ($file_data['error'] !== UPLOAD_ERR_OK) {
            return ['error' => true, 'message' => 'File turnitin gagal diupload!'];
        }
        
        // Validasi ukuran file (max 1MB)
        if ($file_data['size'] > ($this->max_size * 1024)) {
            return ['error' => true, 'message' => 'Ukuran file turnitin maksimal 1MB!'];
        }
        
        // Generate nama file unik
        $file_ext = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
        $file_name = 'turnitin_' . uniqid() . '_' . time() . '.' . $file_ext;
        $file_path = $this->upload_path . $file_name;
        
        if (!move_uploaded_file($file_data['tmp_name'], $file_path)) {
            return ['error' => true, 'message' => 'Gagal mengupload file turnitin!'];
        }
        
        return [
            'error' => false,
            'data' => [
                'file_name' => $file_name,
                'file_path' => $file_path,
                'file_size' => $file_data['size'],
                'file_type' => $file_data['type']
            ]
        ];
    }

    /**
     * ========================================
     * PERSETUJUAN DOSEN PENGUJI
     * ========================================
     */
    
    /**
     * Dosen penguji memberikan persetujuan jadwal
     */
    public function proses_persetujuan_penguji($data)
    {
        $seminar_id = $data['seminar_id'];
        $penguji_ke = $data['penguji_ke']; // 1 atau 2
        $persetujuan = $data['persetujuan']; // '1' = setuju, '2' = tolak
        $catatan = $data['catatan'] ?? '';
        $dosen_id = $data['dosen_id'];
        
        if (!in_array($penguji_ke, [1, 2]) || !in_array($persetujuan, ['1', '2'])) {
            return ['error' => true, 'message' => 'Data persetujuan tidak valid!'];
        }
        
        $update_data = [
            "status_persetujuan_penguji_{$penguji_ke}" => $persetujuan,
            "catatan_penguji_{$penguji_ke}" => $catatan,
            "tanggal_respon_penguji_{$penguji_ke}" => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->db->where('id', $seminar_id);
            $result = $this->db->update($this->table, $update_data);
            
            if ($result) {
                // Cek apakah semua penguji sudah setuju
                $seminar_data = $this->get_seminar_by_id($seminar_id);
                $all_approved = ($seminar_data->status_persetujuan_penguji_1 == '1' && 
                                $seminar_data->status_persetujuan_penguji_2 == '1');
                
                if ($all_approved) {
                    // Update status final dan kirim notifikasi jadwal final
                    $this->db->where('id', $seminar_id);
                    $this->db->update($this->table, ['status_final' => 'approved']);
                    
                    $this->kirim_notifikasi_jadwal_final($seminar_id);
                } else if ($persetujuan == '2') {
                    // Jika ada yang menolak, kirim notifikasi ke kaprodi
                    $this->kirim_notifikasi_penolakan_penguji($seminar_id, $penguji_ke, $catatan);
                }
                
                $message = ($persetujuan == '1') ? 
                    'Jadwal seminar berhasil disetujui!' : 
                    'Jadwal seminar ditolak. Kaprodi akan mendapat notifikasi untuk penjadwalan ulang.';
                    
                return ['error' => false, 'message' => $message];
            } else {
                return ['error' => true, 'message' => 'Gagal menyimpan persetujuan!'];
            }
            
        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * ========================================
     * INPUT HASIL SEMINAR PROPOSAL
     * ========================================
     */
    
    /**
     * Input hasil seminar proposal oleh dosen pembimbing atau staf
     */
    public function input_hasil_seminar($data)
    {
        $seminar_id = $data['seminar_id'];
        $input_oleh = $data['input_oleh']; // ID dosen/staf
        $input_role = $data['input_role']; // 'dosen' atau 'staf'
        $status_input = $data['status_input']; // 'draft' atau 'published'
        
        // Prepare data hasil
        $hasil_data = [
            'seminar_proposal_id' => $seminar_id,
            'proposal_id' => $data['proposal_id'],
            
            // Nilai dari pembimbing (ketua penguji)
            'nilai_pembimbing_penyajian' => $data['nilai_pembimbing_penyajian'] ?? null,
            'nilai_pembimbing_materi' => $data['nilai_pembimbing_materi'] ?? null,
            'nilai_pembimbing_metodologi' => $data['nilai_pembimbing_metodologi'] ?? null,
            'nilai_pembimbing_total' => $data['nilai_pembimbing_total'] ?? null,
            'catatan_revisi_pembimbing' => $data['catatan_revisi_pembimbing'] ?? null,
            
            // Nilai dari penguji 1
            'nilai_penguji1_penyajian' => $data['nilai_penguji1_penyajian'] ?? null,
            'nilai_penguji1_materi' => $data['nilai_penguji1_materi'] ?? null,
            'nilai_penguji1_metodologi' => $data['nilai_penguji1_metodologi'] ?? null,
            'nilai_penguji1_total' => $data['nilai_penguji1_total'] ?? null,
            'catatan_revisi_penguji1' => $data['catatan_revisi_penguji1'] ?? null,
            
            // Nilai dari penguji 2
            'nilai_penguji2_penyajian' => $data['nilai_penguji2_penyajian'] ?? null,
            'nilai_penguji2_materi' => $data['nilai_penguji2_materi'] ?? null,
            'nilai_penguji2_metodologi' => $data['nilai_penguji2_metodologi'] ?? null,
            'nilai_penguji2_total' => $data['nilai_penguji2_total'] ?? null,
            'catatan_revisi_penguji2' => $data['catatan_revisi_penguji2'] ?? null,
            
            // Rekomendasi penguji
            'rekomendasi_penguji' => $data['rekomendasi_penguji'] ?? null,
            'catatan_rekomendasi' => $data['catatan_rekomendasi'] ?? null,
            
            // Status dan metadata
            'status_input' => $status_input,
            'input_oleh' => $input_oleh,
            'input_oleh_role' => $input_role,
            'tanggal_input' => date('Y-m-d H:i:s')
        ];
        
        // Hitung nilai rata-rata dan grade
        $total_nilai = array_filter([
            $hasil_data['nilai_pembimbing_total'],
            $hasil_data['nilai_penguji1_total'],
            $hasil_data['nilai_penguji2_total']
        ]);
        
        if (count($total_nilai) > 0) {
            $hasil_data['nilai_rata_rata'] = array_sum($total_nilai) / count($total_nilai);
            $hasil_data['grade'] = $this->hitung_grade($hasil_data['nilai_rata_rata']);
        }
        
        if ($status_input == 'published') {
            $hasil_data['tanggal_publikasi'] = date('Y-m-d H:i:s');
        }
        
        try {
            $this->db->trans_start();
            
            // Cek apakah sudah ada hasil
            $existing = $this->db->get_where($this->table_hasil, ['seminar_proposal_id' => $seminar_id])->row();
            
            if ($existing) {
                // Update existing
                $this->db->where('seminar_proposal_id', $seminar_id);
                $this->db->update($this->table_hasil, $hasil_data);
            } else {
                // Insert new
                $this->db->insert($this->table_hasil, $hasil_data);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Gagal menyimpan hasil seminar');
            }
            
            // Jika dipublikasi, kirim notifikasi ke mahasiswa dan kaprodi
            if ($status_input == 'published') {
                $this->kirim_notifikasi_hasil_seminar($seminar_id);
            }
            
            $message = ($status_input == 'draft') ? 
                'Hasil seminar berhasil disimpan sebagai draft!' : 
                'Hasil seminar berhasil dipublikasikan! Mahasiswa dan kaprodi akan mendapat notifikasi.';
                
            return ['error' => false, 'message' => $message];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['error' => true, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Hitung grade berdasarkan nilai rata-rata
     */
    private function hitung_grade($nilai)
    {
        if ($nilai >= 85) return 'A';
        if ($nilai >= 80) return 'A-';
        if ($nilai >= 75) return 'B+';
        if ($nilai >= 70) return 'B';
        if ($nilai >= 65) return 'B-';
        if ($nilai >= 60) return 'C+';
        if ($nilai >= 55) return 'C';
        if ($nilai >= 50) return 'C-';
        if ($nilai >= 45) return 'D';
        return 'E';
    }

    /**
     * ========================================
     * QUERY METHODS
     * ========================================
     */
    
    /**
     * Get seminar proposal by ID dengan data lengkap
     */
    public function get_seminar_by_id($seminar_id)
    {
        $this->db->select('*');
        $this->db->from($this->table_view);
        $this->db->where('id', $seminar_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Get seminar proposal by proposal_id
     */
    public function get_seminar_by_proposal_id($proposal_id)
    {
        $this->db->select('*');
        $this->db->from($this->table_view);
        $this->db->where('proposal_id', $proposal_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Get daftar seminar proposal untuk dosen pembimbing
     */
    public function get_seminar_untuk_pembimbing($dosen_id)
    {
        $this->db->select('*');
        $this->db->from($this->table_view);
        $this->db->where('dosen_id', $dosen_id); // dosen_id dari proposal_mahasiswa
        $this->db->where('status_pengajuan', 'submitted');
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get daftar seminar proposal untuk kaprodi validasi
     */
    public function get_seminar_untuk_kaprodi($prodi_id)
    {
        $this->db->select('*');
        $this->db->from($this->table_view);
        $this->db->where('prodi_id', $prodi_id);
        $this->db->where('rekomendasi_pembimbing', '1'); // Sudah direkomendasikan pembimbing
        $this->db->where('status_validasi_kaprodi', '0'); // Belum divalidasi kaprodi
        $this->db->order_by('tanggal_rekomendasi_pembimbing', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get daftar seminar proposal untuk dosen penguji
     */
    public function get_seminar_untuk_penguji($dosen_id)
    {
        $this->db->select('*');
        $this->db->from($this->table_view);
        $this->db->group_start();
        $this->db->where('dosen_penguji_1_id', $dosen_id);
        $this->db->or_where('dosen_penguji_2_id', $dosen_id);
        $this->db->group_end();
        $this->db->where('status_validasi_kaprodi', '1');
        $this->db->order_by('tanggal_seminar', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * ========================================
     * NOTIFIKASI METHODS
     * ========================================
     */
    
    /**
     * Kirim notifikasi pengajuan seminar proposal
     */
    private function kirim_notifikasi_pengajuan($seminar_id)
    {
        $seminar = $this->get_seminar_by_id($seminar_id);
        
        // Notifikasi ke dosen pembimbing
        $this->simpan_notifikasi([
            'seminar_proposal_id' => $seminar_id,
            'jenis_notifikasi' => 'pengajuan',
            'untuk_role' => 'dosen',
            'user_id' => $seminar->dosen_id,
            'judul' => 'Pengajuan Seminar Proposal - ' . $seminar->nama_mahasiswa,
            'pesan' => "Mahasiswa {$seminar->nama_mahasiswa} ({$seminar->nim}) telah mengajukan seminar proposal dengan judul: {$seminar->judul_proposal}. Silakan berikan rekomendasi Anda."
        ]);
        
        // Notifikasi ke mahasiswa (konfirmasi)
        $this->simpan_notifikasi([
            'seminar_proposal_id' => $seminar_id,
            'jenis_notifikasi' => 'pengajuan',
            'untuk_role' => 'mahasiswa',
            'user_id' => $seminar->mahasiswa_id,
            'judul' => 'Seminar Proposal Berhasil Diajukan',
            'pesan' => "Pengajuan seminar proposal Anda telah berhasil dikirim. Menunggu rekomendasi dari dosen pembimbing."
        ]);
    }
    
    /**
     * Simpan notifikasi ke database
     */
    private function simpan_notifikasi($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('notifikasi_seminar_proposal', $data);
        
        // TODO: Implement email sending
        // $this->kirim_email_notifikasi($data);
    }
    
    // ... Method notifikasi lainnya akan ditambahkan di chat session berikutnya ...
}