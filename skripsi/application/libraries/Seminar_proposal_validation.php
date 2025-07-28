<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Validation Library - Fixed Version
 * 
 * CRITICAL FIX: PHP syntax error - file harus dimulai dengan <?php tag
 * 
 * File: application/libraries/Seminar_proposal_validation.php
 * 
 * @package     SIM_TA
 * @subpackage  Libraries
 * @category    Validation
 * @author      Unit SIPD STK Santo Yakobus
 * @version     1.0 (Fixed)
 */
class Seminar_proposal_validation {
    
    protected $CI;
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('form_validation');
    }
    
    /**
     * Setup error messages in Indonesian
     */
    public function setup_error_messages()
    {
        $this->CI->form_validation->set_error_delimiters(
            '<div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>',
            '</div>'
        );
        
        // Set pesan error dalam bahasa Indonesia
        $messages = [
            'required' => '{field} wajib diisi.',
            'numeric' => '{field} harus berupa angka.',
            'decimal' => '{field} harus berupa angka desimal.',
            'max_length' => '{field} maksimal {param} karakter.',
            'min_length' => '{field} minimal {param} karakter.',
            'greater_than_equal_to' => '{field} minimal {param}.',
            'less_than_equal_to' => '{field} maksimal {param}.',
            'in_list' => '{field} harus salah satu dari: {param}.',
            'differs' => '{field} harus berbeda dengan {param}.',
            'regex_match' => '{field} format tidak valid.'
        ];
        
        foreach ($messages as $rule => $message) {
            $this->CI->form_validation->set_message($rule, $message);
        }
    }
    
    /**
     * Validation rules untuk pengajuan seminar proposal mahasiswa
     * 
     * @return array
     */
    public function rules_pengajuan_mahasiswa()
    {
        return [
            [
                'field' => 'proposal_id',
                'label' => 'Proposal ID',
                'rules' => 'required|numeric|callback_check_proposal_exists'
            ],
            [
                'field' => 'keterangan_mahasiswa',
                'label' => 'Keterangan Tambahan',
                'rules' => 'max_length[1000]'
            ]
        ];
    }
    
    /**
     * Validation rules untuk file upload
     * 
     * @return array
     */
    public function rules_file_upload()
    {
        return [
            'file_required' => true,
            'allowed_types' => 'pdf|doc|docx',
            'max_size' => 1024, // 1MB in KB
            'encrypt_name' => true
        ];
    }
    
    /**
     * Custom validation: cek apakah proposal exists dan memenuhi syarat
     * 
     * @param int $proposal_id
     * @return bool
     */
    public function check_proposal_exists($proposal_id)
    {
        $mahasiswa_id = $this->CI->session->userdata('id');
        
        // Cek proposal exists dan milik mahasiswa
        $this->CI->db->select('pm.id, pm.workflow_status, pm.status_pembimbing, pm.status_kaprodi');
        $this->CI->db->from('proposal_mahasiswa pm');
        $this->CI->db->where('pm.id', $proposal_id);
        $this->CI->db->where('pm.mahasiswa_id', $mahasiswa_id);
        
        $proposal = $this->CI->db->get()->row();
        
        if (!$proposal) {
            $this->CI->form_validation->set_message('check_proposal_exists', 
                'Proposal tidak ditemukan atau bukan milik Anda.');
            return false;
        }
        
        // Cek workflow status - harus sudah dalam tahap bimbingan
        if ($proposal->workflow_status !== 'bimbingan') {
            $this->CI->form_validation->set_message('check_proposal_exists', 
                'Proposal belum dalam tahap bimbingan. Status saat ini: ' . $proposal->workflow_status);
            return false;
        }
        
        // Cek status pembimbing - harus sudah menyetujui
        if ($proposal->status_pembimbing !== '1') {
            $this->CI->form_validation->set_message('check_proposal_exists', 
                'Pembimbing belum menyetujui proposal. Tidak dapat mengajukan seminar proposal.');
            return false;
        }
        
        // Cek status kaprodi - harus sudah menyetujui
        if ($proposal->status_kaprodi !== '1') {
            $this->CI->form_validation->set_message('check_proposal_exists', 
                'Kaprodi belum menyetujui proposal. Tidak dapat mengajukan seminar proposal.');
            return false;
        }
        
        // Cek apakah sudah pernah mengajukan seminar proposal
        $this->CI->db->select('id');
        $this->CI->db->from('seminar_proposal_mahasiswa');
        $this->CI->db->where('proposal_id', $proposal_id);
        $this->CI->db->where('mahasiswa_id', $mahasiswa_id);
        
        $existing_seminar = $this->CI->db->get()->row();
        
        if ($existing_seminar) {
            $this->CI->form_validation->set_message('check_proposal_exists', 
                'Sudah ada pengajuan seminar proposal untuk proposal ini.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validasi syarat jurnal bimbingan
     * 
     * @param int $proposal_id
     * @param int $min_required
     * @return array
     */
    public function check_jurnal_requirement($proposal_id, $min_required = 8)
    {
        // Gunakan database function jika tersedia
        $sql = "SELECT check_jurnal_requirement_mahasiswa(?) as result";
        
        try {
            $query = $this->CI->db->query($sql, [$proposal_id]);
            $result = $query->row();
            
            if ($result && $result->result) {
                return json_decode($result->result, true);
            }
        } catch (Exception $e) {
            // Fallback jika function tidak tersedia
        }
        
        // Fallback manual
        $this->CI->db->select('COUNT(*) as total');
        $this->CI->db->from('jurnal_bimbingan');
        $this->CI->db->where('proposal_id', $proposal_id);
        $this->CI->db->where('status_validasi', '1');
        
        $count_result = $this->CI->db->get()->row();
        $count = $count_result ? $count_result->total : 0;
        
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
     * Validation rules untuk penilaian seminar (untuk dosen)
     * 
     * @return array
     */
    public function rules_penilaian_seminar()
    {
        return [
            [
                'field' => 'nilai_substansi_metode',
                'label' => 'Nilai Substansi & Metode',
                'rules' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
            ],
            [
                'field' => 'nilai_presentasi_teknik', 
                'label' => 'Nilai Presentasi & Teknik',
                'rules' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
            ],
            [
                'field' => 'nilai_penguasaan_diskusi',
                'label' => 'Nilai Penguasaan & Diskusi', 
                'rules' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'
            ],
            [
                'field' => 'catatan_revisi',
                'label' => 'Catatan Revisi',
                'rules' => 'max_length[2000]'
            ],
            [
                'field' => 'rekomendasi',
                'label' => 'Rekomendasi',
                'rules' => 'required|in_list[diterima_tanpa_revisi,revisi_minor,revisi_mayor,ditolak]'
            ]
        ];
    }
    
    /**
     * Validation rules untuk jadwal seminar (untuk kaprodi)
     * 
     * @return array
     */
    public function rules_jadwal_seminar()
    {
        return [
            [
                'field' => 'tanggal_seminar',
                'label' => 'Tanggal Seminar',
                'rules' => 'required|callback_check_future_date'
            ],
            [
                'field' => 'jam_seminar',
                'label' => 'Jam Seminar',
                'rules' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]'
            ],
            [
                'field' => 'tempat_seminar',
                'label' => 'Tempat Seminar',
                'rules' => 'required|max_length[255]'
            ],
            [
                'field' => 'dosen_penguji1_id',
                'label' => 'Dosen Penguji 1',
                'rules' => 'required|numeric|callback_check_dosen_exists'
            ],
            [
                'field' => 'dosen_penguji2_id',
                'label' => 'Dosen Penguji 2', 
                'rules' => 'required|numeric|callback_check_dosen_exists|differs[dosen_penguji1_id]'
            ]
        ];
    }
    
    /**
     * Custom validation: cek tanggal masa depan
     * 
     * @param string $date
     * @return bool
     */
    public function check_future_date($date)
    {
        if (strtotime($date) <= strtotime('today')) {
            $this->CI->form_validation->set_message('check_future_date', 
                'Tanggal seminar harus di masa depan.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Custom validation: cek dosen exists dan aktif
     * 
     * @param int $dosen_id
     * @return bool
     */
    public function check_dosen_exists($dosen_id)
    {
        $this->CI->db->select('id, nama');
        $this->CI->db->from('dosen');
        $this->CI->db->where('id', $dosen_id);
        $this->CI->db->where('level IN ("2", "4")'); // Dosen atau Kaprodi saja
        
        $dosen = $this->CI->db->get()->row();
        
        if (!$dosen) {
            $this->CI->form_validation->set_message('check_dosen_exists', 
                'Dosen tidak ditemukan atau tidak memiliki level yang sesuai.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validasi workflow transition
     * 
     * @param string $from_status
     * @param string $to_status
     * @param string $role
     * @return bool
     */
    public function validate_workflow_transition($from_status, $to_status, $role = 'mahasiswa')
    {
        $allowed_transitions = [
            'mahasiswa' => [
                'draft' => ['submitted'],
                'rejected' => ['submitted']
            ],
            'pembimbing' => [
                'submitted' => ['review_pembimbing', 'review_kaprodi', 'rejected'],
                'review_pembimbing' => ['review_kaprodi', 'rejected']
            ],
            'kaprodi' => [
                'review_kaprodi' => ['approved', 'rejected'],
                'approved' => ['scheduled']
            ],
            'staf' => [
                'scheduled' => ['completed']
            ]
        ];
        
        if (!isset($allowed_transitions[$role][$from_status])) {
            return false;
        }
        
        return in_array($to_status, $allowed_transitions[$role][$from_status]);
    }
    
    /**
     * Get status badge HTML untuk UI
     * 
     * @param string $status
     * @return string
     */
    public function get_status_badge($status)
    {
        $badges = [
            'draft' => '<span class="badge badge-secondary">Draft</span>',
            'submitted' => '<span class="badge badge-info">Diajukan</span>',
            'review_pembimbing' => '<span class="badge badge-warning">Review Pembimbing</span>',
            'review_kaprodi' => '<span class="badge badge-warning">Review Kaprodi</span>',
            'approved' => '<span class="badge badge-success">Disetujui</span>',
            'rejected' => '<span class="badge badge-danger">Ditolak</span>',
            'scheduled' => '<span class="badge badge-primary">Terjadwal</span>',
            'completed' => '<span class="badge badge-success">Selesai</span>'
        ];
        
        return $badges[$status] ?? '<span class="badge badge-light">Unknown</span>';
    }
}