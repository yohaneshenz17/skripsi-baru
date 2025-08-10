<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Daftar Penguji Model
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
 * @version     1.0
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
        $seminar_proposal = $this->db->select('
                spm.id, spm.proposal_id, spm.mahasiswa_id, spm.status, spm.current_step,
                spm.tanggal_seminar, spm.jam_seminar, spm.tempat_seminar,
                spm.dosen_penguji1_id, spm.dosen_penguji2_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi,
                CASE 
                    WHEN spm.dosen_penguji1_id = ' . $dosen_id . ' THEN "Penguji 1"
                    WHEN spm.dosen_penguji2_id = ' . $dosen_id . ' THEN "Penguji 2"
                    ELSE "Tidak Diketahui"
                END as posisi_penguji
            ')
            ->from('seminar_proposal_mahasiswa spm')
            ->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id')
            ->join('mahasiswa m', 'spm.mahasiswa_id = m.id')
            ->join('prodi pr', 'm.prodi_id = pr.id')
            ->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left')
            ->join('dosen d_penguji1', 'spm.dosen_penguji1_id = d_penguji1.id', 'left')
            ->join('dosen d_penguji2', 'spm.dosen_penguji2_id = d_penguji2.id', 'left')
            ->where('(spm.dosen_penguji1_id = ' . $dosen_id . ' OR spm.dosen_penguji2_id = ' . $dosen_id . ')')
            ->order_by('spm.tanggal_seminar DESC, spm.created_at DESC')
            ->get()
            ->result();

        // Get seminar skripsi where dosen is penguji
        $seminar_skripsi = $this->db->select('
                ssm.id, ssm.proposal_id, ssm.mahasiswa_id, ssm.status, ssm.current_step,
                ssm.tanggal_seminar, ssm.jam_seminar, ssm.tempat_seminar,
                ssm.dosen_penguji1_id, ssm.dosen_penguji2_id,
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi,
                CASE 
                    WHEN ssm.dosen_penguji1_id = ' . $dosen_id . ' THEN "Penguji 1"
                    WHEN ssm.dosen_penguji2_id = ' . $dosen_id . ' THEN "Penguji 2"
                    ELSE "Tidak Diketahui"
                END as posisi_penguji
            ')
            ->from('seminar_skripsi_mahasiswa ssm')
            ->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id')
            ->join('mahasiswa m', 'ssm.mahasiswa_id = m.id')
            ->join('prodi pr', 'm.prodi_id = pr.id')
            ->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left')
            ->join('dosen d_penguji1', 'ssm.dosen_penguji1_id = d_penguji1.id', 'left')
            ->join('dosen d_penguji2', 'ssm.dosen_penguji2_id = d_penguji2.id', 'left')
            ->where('(ssm.dosen_penguji1_id = ' . $dosen_id . ' OR ssm.dosen_penguji2_id = ' . $dosen_id . ')')
            ->order_by('ssm.tanggal_seminar DESC, ssm.created_at DESC')
            ->get()
            ->result();

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
        return $this->db->select('
                spm.*, 
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.ringkasan, pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing, d_pembimbing.email as email_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi
            ')
            ->from('seminar_proposal_mahasiswa spm')
            ->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id')
            ->join('mahasiswa m', 'spm.mahasiswa_id = m.id')
            ->join('prodi pr', 'm.prodi_id = pr.id')
            ->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left')
            ->join('dosen d_penguji1', 'spm.dosen_penguji1_id = d_penguji1.id', 'left')
            ->join('dosen d_penguji2', 'spm.dosen_penguji2_id = d_penguji2.id', 'left')
            ->where('spm.id', $seminar_id)
            ->where('(spm.dosen_penguji1_id = ' . $dosen_id . ' OR spm.dosen_penguji2_id = ' . $dosen_id . ')')
            ->get()
            ->row();
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
        return $this->db->select('
                ssm.*, 
                m.nim, m.nama as nama_mahasiswa, m.email as email_mahasiswa,
                pm.judul, pm.ringkasan, pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing, d_pembimbing.email as email_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2,
                pr.nama as nama_prodi
            ')
            ->from('seminar_skripsi_mahasiswa ssm')
            ->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id')
            ->join('mahasiswa m', 'ssm.mahasiswa_id = m.id')
            ->join('prodi pr', 'm.prodi_id = pr.id')
            ->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left')
            ->join('dosen d_penguji1', 'ssm.dosen_penguji1_id = d_penguji1.id', 'left')
            ->join('dosen d_penguji2', 'ssm.dosen_penguji2_id = d_penguji2.id', 'left')
            ->where('ssm.id', $seminar_id)
            ->where('(ssm.dosen_penguji1_id = ' . $dosen_id . ' OR ssm.dosen_penguji2_id = ' . $dosen_id . ')')
            ->get()
            ->row();
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
        return $this->db->select('
                spm.dosen_penguji1_id, spm.dosen_penguji2_id,
                pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2
            ')
            ->from('seminar_proposal_mahasiswa spm')
            ->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id')
            ->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left')
            ->join('dosen d_penguji1', 'spm.dosen_penguji1_id = d_penguji1.id', 'left')
            ->join('dosen d_penguji2', 'spm.dosen_penguji2_id = d_penguji2.id', 'left')
            ->where('spm.id', $seminar_id)
            ->get()
            ->row();
    }

    /**
     * Get susunan dewan penguji untuk seminar skripsi
     * 
     * @param int $seminar_id
     * @return object
     */
    public function get_dewan_penguji_skripsi($seminar_id)
    {
        return $this->db->select('
                ssm.dosen_penguji1_id, ssm.dosen_penguji2_id,
                pm.dosen_id as pembimbing_id,
                d_pembimbing.nama as nama_pembimbing,
                d_penguji1.nama as nama_penguji1,
                d_penguji2.nama as nama_penguji2
            ')
            ->from('seminar_skripsi_mahasiswa ssm')
            ->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id')
            ->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left')
            ->join('dosen d_penguji1', 'ssm.dosen_penguji1_id = d_penguji1.id', 'left')
            ->join('dosen d_penguji2', 'ssm.dosen_penguji2_id = d_penguji2.id', 'left')
            ->where('ssm.id', $seminar_id)
            ->get()
            ->row();
    }

    /**
     * Get statistics untuk dashboard
     * 
     * @param int $dosen_id
     * @return array
     */
    public function get_statistics($dosen_id)
    {
        // Count seminar proposal
        $total_proposal = $this->db->where('(dosen_penguji1_id = ' . $dosen_id . ' OR dosen_penguji2_id = ' . $dosen_id . ')')
                                  ->count_all_results('seminar_proposal_mahasiswa');

        // Count seminar skripsi
        $total_skripsi = $this->db->where('(dosen_penguji1_id = ' . $dosen_id . ' OR dosen_penguji2_id = ' . $dosen_id . ')')
                                 ->count_all_results('seminar_skripsi_mahasiswa');

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
        $query = $this->db->select('
                spm.id, m.nim, m.nama as nama_mahasiswa, pm.judul,
                spm.tanggal_seminar, spm.jam_seminar, spm.tempat_seminar,
                spm.status, d_pembimbing.nama as nama_pembimbing,
                CASE 
                    WHEN spm.dosen_penguji1_id = ' . $dosen_id . ' THEN "Penguji 1"
                    WHEN spm.dosen_penguji2_id = ' . $dosen_id . ' THEN "Penguji 2"
                    ELSE "Tidak Diketahui"
                END as posisi_penguji,
                COALESCE(psp.status_penilaian, "belum_dinilai") as status_penilaian
            ')
            ->from('seminar_proposal_mahasiswa spm')
            ->join('proposal_mahasiswa pm', 'spm.proposal_id = pm.id')
            ->join('mahasiswa m', 'spm.mahasiswa_id = m.id')
            ->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left')
            ->join('penilaian_seminar_proposal psp', 'spm.id = psp.seminar_proposal_id AND psp.dinilai_oleh = ' . $dosen_id, 'left')
            ->where('(spm.dosen_penguji1_id = ' . $dosen_id . ' OR spm.dosen_penguji2_id = ' . $dosen_id . ')')
            ->order_by('spm.tanggal_seminar DESC')
            ->get();

        $data = $query->result_array();
        
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
        $query = $this->db->select('
                ssm.id, m.nim, m.nama as nama_mahasiswa, pm.judul,
                ssm.tanggal_seminar, ssm.jam_seminar, ssm.tempat_seminar,
                ssm.status, d_pembimbing.nama as nama_pembimbing,
                CASE 
                    WHEN ssm.dosen_penguji1_id = ' . $dosen_id . ' THEN "Penguji 1"
                    WHEN ssm.dosen_penguji2_id = ' . $dosen_id . ' THEN "Penguji 2"
                    ELSE "Tidak Diketahui"
                END as posisi_penguji,
                COALESCE(pss.status_penilaian, "belum_dinilai") as status_penilaian
            ')
            ->from('seminar_skripsi_mahasiswa ssm')
            ->join('proposal_mahasiswa pm', 'ssm.proposal_id = pm.id')
            ->join('mahasiswa m', 'ssm.mahasiswa_id = m.id')
            ->join('dosen d_pembimbing', 'pm.dosen_id = d_pembimbing.id', 'left')
            ->join('penilaian_seminar_skripsi pss', 'ssm.id = pss.seminar_skripsi_id AND pss.dinilai_oleh = ' . $dosen_id, 'left')
            ->where('(ssm.dosen_penguji1_id = ' . $dosen_id . ' OR ssm.dosen_penguji2_id = ' . $dosen_id . ')')
            ->order_by('ssm.tanggal_seminar DESC')
            ->get();

        $data = $query->result_array();
        
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
}