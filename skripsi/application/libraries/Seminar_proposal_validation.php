// =================================================================
// File: application/libraries/Seminar_proposal_validation.php  
// =================================================================

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Seminar Proposal Validation Library
 * 
 * Library untuk validation rules seminar proposal
 */
class Seminar_proposal_validation {
    
    protected $CI;
    
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('form_validation');
    }
    
    /**
     * Validation rules untuk pengajuan mahasiswa
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
                'field' => 'keterangan_tambahan',
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
     * Custom validation: cek apakah proposal exists dan milik mahasiswa
     * 
     * @param int $proposal_id
     * @return bool
     */
    public function check_proposal_exists($proposal_id)
    {
        $mahasiswa_id = $this->CI->session->userdata('id');
        
        $this->CI->db->select('id');
        $this->CI->db->from('proposal_mahasiswa');
        $this->CI->db->where('id', $proposal_id);
        $this->CI->db->where('mahasiswa_id', $mahasiswa_id);
        
        $exists = $this->CI->db->get()->row();
        
        if (!$exists) {
            $this->CI->form_validation->set_message('check_proposal_exists', 'Proposal tidak ditemukan atau bukan milik Anda.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validasi transisi workflow status
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
     * Validation rules untuk penilaian seminar
     * (Untuk role dosen - akan dikembangkan kemudian)
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
     * Validation rules untuk jadwal seminar
     * (Untuk role kaprodi - akan dikembangkan kemudian)
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
            $this->CI->form_validation->set_message('check_future_date', 'Tanggal seminar harus di masa depan.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Custom validation: cek dosen exists
     * 
     * @param int $dosen_id
     * @return bool
     */
    public function check_dosen_exists($dosen_id)
    {
        $this->CI->db->select('id');
        $this->CI->db->from('dosen');
        $this->CI->db->where('id', $dosen_id);
        $this->CI->db->where('status', '1'); // Active dosen only
        
        $exists = $this->CI->db->get()->row();
        
        if (!$exists) {
            $this->CI->form_validation->set_message('check_dosen_exists', 'Dosen tidak ditemukan atau tidak aktif.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Get error messages dalam bahasa Indonesia
     * 
     * @return array
     */
    public function get_error_messages()
    {
        return [
            'required' => '{field} wajib diisi.',
            'numeric' => '{field} harus berupa angka.',
            'decimal' => '{field} harus berupa angka desimal.',
            'max_length' => '{field} tidak boleh lebih dari {param} karakter.',
            'min_length' => '{field} minimal {param} karakter.',
            'greater_than_equal_to' => '{field} minimal {param}.',
            'less_than_equal_to' => '{field} maksimal {param}.',
            'in_list' => '{field} harus salah satu dari: {param}.',
            'differs' => '{field} harus berbeda dengan {param}.',
            'regex_match' => '{field} format tidak valid.'
        ];
    }
    
    /**
     * Setup custom error messages
     */
    public function setup_error_messages()
    {
        $messages = $this->get_error_messages();
        
        foreach ($messages as $rule => $message) {
            $this->CI->form_validation->set_message($rule, $message);
        }
    }
}