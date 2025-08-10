<?php
/*
=================================================
CONTROLLER: application/controllers/kaprodi/Kontak.php
=================================================
*/
?>
<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kontak Controller untuk Kaprodi - FIXED VERSION
 * Mengikuti EXACT pattern dosen/Kontak.php yang sudah stable
 */
class Kontak extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->library(['session', 'email']);
        $this->load->helper('url');
        
        // Validasi akses kaprodi
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '4') {
            redirect('auth/login');
        }
        
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
    }

    public function index()
    {
        // EXACT PATTERN DOSEN STABLE - SIMPLE RETURN VIEW
        $data['title'] = 'Kontak Form - Kaprodi';
        return $this->load->view('kaprodi/kontak', $data);
    }
    
    /**
     * Get data untuk form kontak kaprodi
     */
    public function get_kontak_data()
    {
        header('Content-Type: application/json');
        
        try {
            $kaprodi_id = $this->session->userdata('id');
            
            if (!$kaprodi_id) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Session kaprodi tidak valid. Silakan login ulang.'
                ]);
                return;
            }
            
            // Get prodi_id
            $prodi_id = $this->session->userdata('prodi_id');
            if (!$prodi_id) {
                $kaprodi = $this->db->get_where('prodi', ['dosen_id' => $kaprodi_id])->row();
                if ($kaprodi) {
                    $prodi_id = $kaprodi->id;
                    $this->session->set_userdata('prodi_id', $prodi_id);
                }
            }
            
            $data = [
                'dosen_list' => [],
                'staf_list' => [],
                'mahasiswa_list' => []
            ];
            
            // 1. Get DOSEN
            $this->db->select('d.id, d.nama, d.email, d.nomor_telepon');
            $this->db->from('dosen d');
            $this->db->where('d.level', '2');
            $this->db->order_by('d.nama', 'ASC');
            $query_dosen = $this->db->get();
            
            if ($query_dosen->num_rows() > 0) {
                $data['dosen_list'] = $query_dosen->result();
            }
            
            // 2. Get STAF/ADMIN
            $this->db->select('d.id, d.nama, d.email, d.nomor_telepon');
            $this->db->from('dosen d');
            $this->db->where_in('d.level', ['1', '5']);
            $this->db->order_by('d.nama', 'ASC');
            $query_staf = $this->db->get();
            
            if ($query_staf->num_rows() > 0) {
                $data['staf_list'] = $query_staf->result();
            }
            
            // 3. Get MAHASISWA dari prodi kaprodi
            if ($prodi_id) {
                $this->db->select('m.id, m.nim, m.nama, m.email, m.nomor_telepon');
                $this->db->from('mahasiswa m');
                $this->db->where('m.prodi_id', $prodi_id);
                $this->db->order_by('m.nama', 'ASC');
                $query_mahasiswa = $this->db->get();
                
                if ($query_mahasiswa->num_rows() > 0) {
                    $data['mahasiswa_list'] = $query_mahasiswa->result();
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'message' => 'Data kontak berhasil dimuat'
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in kaprodi kontak get_data: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memuat data kontak. Silakan coba lagi.'
            ]);
        }
    }
    
    /**
     * Kirim pesan
     */
    public function kirim_pesan()
    {
        header('Content-Type: application/json');
        
        try {
            $kategori_penerima = $this->input->post('kategori_penerima');
            $penerima_id = $this->input->post('penerima_id');
            $subjek = trim($this->input->post('subjek'));
            $pesan = trim($this->input->post('pesan'));
            $prioritas = $this->input->post('prioritas') ?: 'normal';
            
            if (empty($kategori_penerima) || empty($subjek) || empty($pesan)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Kategori penerima, subjek, dan pesan wajib diisi!'
                ]);
                return;
            }
            
            // Simplified success response untuk testing
            echo json_encode([
                'status' => 'success',
                'message' => 'Pesan berhasil dikirim! (Testing mode)'
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error in kaprodi kontak kirim_pesan: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ]);
        }
    }
}
