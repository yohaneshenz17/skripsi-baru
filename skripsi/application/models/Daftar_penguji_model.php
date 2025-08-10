<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Daftar Penguji Model - FIXED VERSION
 * 
 * Model untuk mengelola data penguji seminar proposal dan seminar skripsi
 * Menggunakan tabel yang sudah ada dalam database
 * 
 * File: application/models/Daftar_penguji_model.php
 * 
 * @package     SIM_TA
 * @subpackage  Models
 * @category    Daftar Penguji
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.1 (FIXED DATABASE COMPATIBILITY)
 */
class Daftar_penguji_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get daftar penguji untuk dosen (seminar proposal dan seminar skripsi)
     * 
     * @param int $dosen_id
     * @return array
     */
    public function get_daftar_penguji($dosen_id)
    {
        // Get seminar proposal where dosen is penguji
        $query_proposal = "
            SELECT 
                spm.id, spm.proposal_id, spm.mahasiswa_id, spm.status, spm.current_step,
                spm.tanggal_seminar, spm.jam_seminar, spm.tempat_seminar,
                spm.dosen_penguji1_id, spm.dosen_penguji2_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi,
                IF(spm.dosen_penguji1_id = ?, 'Penguji 1', 'Penguji 2') as posisi_penguji
            FROM seminar_proposal_mahasiswa spm
            JOIN proposal_mahasiswa pm ON spm.proposal_id = pm.id
            JOIN mahasiswa m ON spm.mahasiswa_id = m.id
            JOIN prodi pr ON m.prodi_id = pr.id
            LEFT JOIN dosen d_pembimbing ON pm.dosen_id = d_pembimbing.id
            LEFT JOIN dosen d_penguji1 ON spm.dosen_penguji1_id = d_penguji1.id
            LEFT JOIN dosen d_penguji2 ON spm.dosen_penguji2_id = d_penguji2.id
            WHERE (spm.dosen_penguji1_id = ? OR spm.dosen_penguji2_id = ?)
            ORDER BY spm.tanggal_seminar DESC, spm.created_at DESC
        ";
        
        $seminar_proposal = $this->db->query($query_proposal, [$dosen_id, $dosen_id, $dosen_id])->result();

        // Get seminar skripsi where dosen is penguji
        $query_skripsi = "
            SELECT 
                ssm.id, ssm.proposal_id, ssm.mahasiswa_id, ssm.status, ssm.current_step,
                ssm.tanggal_seminar, ssm.jam_seminar, ssm.tempat_seminar,
                ssm.dosen_penguji1_id, ssm.dosen_penguji2_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi,
                IF(ssm.dosen_penguji1_id = ?, 'Penguji 1', 'Penguji 2') as posisi_penguji
            FROM seminar_skripsi_mahasiswa ssm
            JOIN proposal_mahasiswa pm ON ssm.proposal_id = pm.id
            JOIN mahasiswa m ON ssm.mahasiswa_id = m.id
            JOIN prodi pr ON m.prodi_id = pr.id
            LEFT JOIN dosen d_pembimbing ON pm.dosen_id = d_pembimbing.id
            LEFT JOIN dosen d_penguji1 ON ssm.dosen_penguji1_id = d_penguji1.id
            LEFT JOIN dosen d_penguji2 ON ssm.dosen_penguji2_id = d_penguji2.id
            WHERE (ssm.dosen_penguji1_id = ? OR ssm.dosen_penguji2_id = ?)
            ORDER BY ssm.tanggal_seminar DESC, ssm.created_at DESC
        ";
        
        $seminar_skripsi = $this->db->query($query_skripsi, [$dosen_id, $dosen_id, $dosen_id])->result();

        return [
            'seminar_proposal' => $seminar_proposal,
            'seminar_skripsi' => $seminar_skripsi
        ];
    }

    /**
     * Get detail seminar proposal untuk penguji
     * 
     * @param int $seminar_id
     * @param int $dosen_id
     * @return object|null
     */
    public function get_detail_seminar_proposal($seminar_id, $dosen_id)
    {
        $query = "
            SELECT 
                spm.*, 
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.ringkasan, pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing, d_pembimbing.email as email_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi
            FROM seminar_proposal_mahasiswa spm
            JOIN proposal_mahasiswa pm ON spm.proposal_id = pm.id
            JOIN mahasiswa m ON spm.mahasiswa_id = m.id
            JOIN prodi pr ON m.prodi_id = pr.id
            LEFT JOIN dosen d_pembimbing ON pm.dosen_id = d_pembimbing.id
            LEFT JOIN dosen d_penguji1 ON spm.dosen_penguji1_id = d_penguji1.id
            LEFT JOIN dosen d_penguji2 ON spm.dosen_penguji2_id = d_penguji2.id
            WHERE spm.id = ? AND (spm.dosen_penguji1_id = ? OR spm.dosen_penguji2_id = ?)
        ";
        
        return $this->db->query($query, [$seminar_id, $dosen_id, $dosen_id])->row();
    }

    /**
     * Get detail seminar skripsi untuk penguji
     * 
     * @param int $seminar_id
     * @param int $dosen_id
     * @return object|null
     */
    public function get_detail_seminar_skripsi($seminar_id, $dosen_id)
    {
        $query = "
            SELECT 
                ssm.*, 
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.ringkasan, pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing, d_pembimbing.email as email_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi
            FROM seminar_skripsi_mahasiswa ssm
            JOIN proposal_mahasiswa pm ON ssm.proposal_id = pm.id
            JOIN mahasiswa m ON ssm.mahasiswa_id = m.id
            JOIN prodi pr ON m.prodi_id = pr.id
            LEFT JOIN dosen d_pembimbing ON pm.dosen_id = d_pembimbing.id
            LEFT JOIN dosen d_penguji1 ON ssm.dosen_penguji1_id = d_penguji1.id
            LEFT JOIN dosen d_penguji2 ON ssm.dosen_penguji2_id = d_penguji2.id
            WHERE ssm.id = ? AND (ssm.dosen_penguji1_id = ? OR ssm.dosen_penguji2_id = ?)
        ";
        
        return $this->db->query($query, [$seminar_id, $dosen_id, $dosen_id])->row();
    }

    /**
     * Get penilaian seminar proposal
     * 
     * @param int $seminar_id
     * @param int $dosen_id
     * @return object|null
     */
    public function get_penilaian_proposal($seminar_id, $dosen_id)
    {
        return $this->db->select('*')
            ->from('penilaian_seminar_proposal')
            ->where('seminar_proposal_id', $seminar_id)
            ->where('dinilai_oleh', $dosen_id)
            ->get()
            ->row();
    }

    /**
     * Get penilaian seminar skripsi
     * 
     * @param int $seminar_id
     * @param int $dosen_id
     * @return object|null
     */
    public function get_penilaian_skripsi($seminar_id, $dosen_id)
    {
        return $this->db->select('*')
            ->from('penilaian_seminar_skripsi')
            ->where('seminar_skripsi_id', $seminar_id)
            ->where('dinilai_oleh', $dosen_id)
            ->get()
            ->row();
    }

    /**
     * Get susunan dewan penguji untuk seminar proposal
     * 
     * @param int $seminar_id
     * @return object
     */
    public function get_dewan_penguji_proposal($seminar_id)
    {
        $query = "
            SELECT 
                spm.dosen_penguji1_id, spm.dosen_penguji2_id,
                pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2
            FROM seminar_proposal_mahasiswa spm
            JOIN proposal_mahasiswa pm ON spm.proposal_id = pm.id
            LEFT JOIN dosen d_pembimbing ON pm.dosen_id = d_pembimbing.id
            LEFT JOIN dosen d_penguji1 ON spm.dosen_penguji1_id = d_penguji1.id
            LEFT JOIN dosen d_penguji2 ON spm.dosen_penguji2_id = d_penguji2.id
            WHERE spm.id = ?
        ";
        
        return $this->db->query($query, [$seminar_id])->row();
    }

    /**
     * Get susunan dewan penguji untuk seminar skripsi
     * 
     * @param int $seminar_id
     * @return object
     */
    public function get_dewan_penguji_skripsi($seminar_id)
    {
        $query = "
            SELECT 
                ssm.dosen_penguji1_id, ssm.dosen_penguji2_id,
                pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2
            FROM seminar_skripsi_mahasiswa ssm
            JOIN proposal_mahasiswa pm ON ssm.proposal_id = pm.id
            LEFT JOIN dosen d_pembimbing ON pm.dosen_id = d_pembimbing.id
            LEFT JOIN dosen d_penguji1 ON ssm.dosen_penguji1_id = d_penguji1.id
            LEFT JOIN dosen d_penguji2 ON ssm.dosen_penguji2_id = d_penguji2.id
            WHERE ssm.id = ?
        ";
        
        return $this->db->query($query, [$seminar_id])->row();
    }

    /**
     * Get statistics untuk dashboard
     * 
     * @param int $dosen_id
     * @return array
     */
    public function get_statistics($dosen_id)
    {
        // Count seminar proposal - Using simple query
        $total_proposal = $this->db->query(
            "SELECT COUNT(*) as total FROM seminar_proposal_mahasiswa WHERE (dosen_penguji1_id = ? OR dosen_penguji2_id = ?)",
            [$dosen_id, $dosen_id]
        )->row()->total;

        // Count seminar skripsi - Using simple query
        $total_skripsi = $this->db->query(
            "SELECT COUNT(*) as total FROM seminar_skripsi_mahasiswa WHERE (dosen_penguji1_id = ? OR dosen_penguji2_id = ?)",
            [$dosen_id, $dosen_id]
        )->row()->total;

        // Count yang sudah dinilai (proposal)
        $sudah_nilai_proposal = $this->db->where('dinilai_oleh', $dosen_id)
                                        ->where('status_penilaian', 'published')
                                        ->count_all_results('penilaian_seminar_proposal');

        // Count yang sudah dinilai (skripsi)
        $sudah_nilai_skripsi = $this->db->where('dinilai_oleh', $dosen_id)
                                       ->where('status_penilaian', 'published')
                                       ->count_all_results('penilaian_seminar_skripsi');

        // Count menunggu penilaian
        $menunggu_proposal = $total_proposal - $sudah_nilai_proposal;
        $menunggu_skripsi = $total_skripsi - $sudah_nilai_skripsi;

        return [
            'total_proposal' => $total_proposal,
            'total_skripsi' => $total_skripsi,
            'menunggu_penilaian' => $menunggu_proposal + $menunggu_skripsi,
            'selesai_dinilai' => $sudah_nilai_proposal + $sudah_nilai_skripsi
        ];
    }

    /**
     * Get data untuk DataTables (seminar proposal)
     * 
     * @param int $dosen_id
     * @return array
     */
    public function get_datatable_proposal($dosen_id)
    {
        $query = "
            SELECT 
                spm.id, m.nim, m.nama as nama_mahasiswa, pm.judul,
                spm.tanggal_seminar, spm.jam_seminar, spm.tempat_seminar,
                spm.status, d_pembimbing.nama as nama_pembimbing,
                IF(spm.dosen_penguji1_id = ?, 'Penguji 1', 'Penguji 2') as posisi_penguji,
                COALESCE(psp.status_penilaian, 'belum_dinilai') as status_penilaian
            FROM seminar_proposal_mahasiswa spm
            JOIN proposal_mahasiswa pm ON spm.proposal_id = pm.id
            JOIN mahasiswa m ON spm.mahasiswa_id = m.id
            LEFT JOIN dosen d_pembimbing ON pm.dosen_id = d_pembimbing.id
            LEFT JOIN penilaian_seminar_proposal psp ON spm.id = psp.seminar_proposal_id AND psp.dinilai_oleh = ?
            WHERE (spm.dosen_penguji1_id = ? OR spm.dosen_penguji2_id = ?)
            ORDER BY spm.tanggal_seminar DESC
        ";

        $data = $this->db->query($query, [$dosen_id, $dosen_id, $dosen_id, $dosen_id])->result_array();
        
        return [
            'data' => $data,
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data)
        ];
    }

    /**
     * Get data untuk DataTables (seminar skripsi)
     * 
     * @param int $dosen_id
     * @return array
     */
    public function get_datatable_skripsi($dosen_id)
    {
        $query = "
            SELECT 
                ssm.id, m.nim, m.nama as nama_mahasiswa, pm.judul,
                ssm.tanggal_seminar, ssm.jam_seminar, ssm.tempat_seminar,
                ssm.status, d_pembimbing.nama as nama_pembimbing,
                IF(ssm.dosen_penguji1_id = ?, 'Penguji 1', 'Penguji 2') as posisi_penguji,
                COALESCE(pss.status_penilaian, 'belum_dinilai') as status_penilaian
            FROM seminar_skripsi_mahasiswa ssm
            JOIN proposal_mahasiswa pm ON ssm.proposal_id = pm.id
            JOIN mahasiswa m ON ssm.mahasiswa_id = m.id
            LEFT JOIN dosen d_pembimbing ON pm.dosen_id = d_pembimbing.id
            LEFT JOIN penilaian_seminar_skripsi pss ON ssm.id = pss.seminar_skripsi_id AND pss.dinilai_oleh = ?
            WHERE (ssm.dosen_penguji1_id = ? OR ssm.dosen_penguji2_id = ?)
            ORDER BY ssm.tanggal_seminar DESC
        ";

        $data = $this->db->query($query, [$dosen_id, $dosen_id, $dosen_id, $dosen_id])->result_array();
        
        return [
            'data' => $data,
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data)
        ];
    }

    /**
     * Get data untuk export PDF
     * 
     * @param int $dosen_id
     * @param string $type
     * @return array
     */
    public function get_data_export($dosen_id, $type = 'all')
    {
        $data = [];
        
        if ($type == 'proposal' || $type == 'all') {
            $data['proposal'] = $this->get_daftar_penguji($dosen_id)['seminar_proposal'];
        }
        
        if ($type == 'skripsi' || $type == 'all') {
            $data['skripsi'] = $this->get_daftar_penguji($dosen_id)['seminar_skripsi'];
        }
        
        return $data;
    }

    /**
     * Check if field exists in table (Helper function)
     * 
     * @param string $table
     * @param string $field
     * @return bool
     */
    private function field_exists($table, $field)
    {
        $query = $this->db->query("SHOW COLUMNS FROM {$table} LIKE '{$field}'");
        return $query->num_rows() > 0;
    }

    /**
     * Debug function untuk check table structure
     * 
     * @param string $table
     * @return array
     */
    public function debug_table_structure($table)
    {
        if (ENVIRONMENT === 'development') {
            return $this->db->query("DESCRIBE {$table}")->result_array();
        }
        return [];
    }
}