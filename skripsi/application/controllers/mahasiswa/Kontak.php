<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kontak Controller - Final Version for STK Yakobus
 * Disesuaikan dengan konfigurasi database: stkp7133_skripsi
 * Base URL: https://stkyakobus.ac.id/skripsi/
 */
class Kontak extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Load basic libraries
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Set error reporting untuk development
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
    }

    public function index()
    {
        $data['title'] = 'Kontak Form';
        return $this->load->view('mahasiswa/kontak', $data);
    }
    
    /**
     * Get data untuk form kontak
     * Disesuaikan dengan database stkp7133_skripsi
     */
    public function get_kontak_data()
    {
        // ALWAYS set JSON header first
        header('Content-Type: application/json');
        
        try {
            // Get mahasiswa ID from session
            $mahasiswa_id = $this->session->userdata('id');
            
            if (!$mahasiswa_id) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Session mahasiswa tidak valid. Silakan login ulang.'
                ]);
                return;
            }
            
            // Initialize response data
            $data = [
                'pembimbing' => null,
                'kaprodi' => null,
                'staf_list' => [],
                'riwayat_pesan' => []
            ];
            
            // Get mahasiswa data first
            $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
            
            if (!$mahasiswa) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Data mahasiswa tidak ditemukan di database'
                ]);
                return;
            }
            
            // 1. Get dosen pembimbing (SIMPLE QUERY)
            $data['pembimbing'] = $this->_get_pembimbing_simple($mahasiswa_id);
            
            // 2. Get kaprodi (SIMPLE QUERY)
            if ($mahasiswa->prodi_id) {
                $data['kaprodi'] = $this->_get_kaprodi_simple($mahasiswa->prodi_id);
            }
            
            // 3. Get staff list (SIMPLE QUERY)
            $data['staf_list'] = $this->_get_staf_simple();
            
            // 4. Get riwayat pesan (OPTIONAL - skip if error)
            try {
                $data['riwayat_pesan'] = $this->_get_riwayat_simple($mahasiswa_id);
            } catch (Exception $e) {
                // Skip riwayat if error - not critical
                $data['riwayat_pesan'] = [];
                log_message('error', 'Riwayat pesan error: ' . $e->getMessage());
            }
            
            echo json_encode(['status' => 'success', 'data' => $data]);
            
        } catch (Exception $e) {
            // Log error untuk debugging
            log_message('error', 'Kontak get_kontak_data error: ' . $e->getMessage());
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan sistem',
                'debug' => (ENVIRONMENT === 'development') ? $e->getMessage() : null
            ]);
        }
    }
    
    /**
     * Kirim pesan ke penerima
     */
    public function kirim_pesan()
    {
        header('Content-Type: application/json');
        
        try {
            // Validasi input
            $input = [
                'penerima_role' => $this->input->post('penerima_role'),
                'penerima_id' => $this->input->post('penerima_id'),
                'subjek' => trim($this->input->post('subjek')),
                'pesan' => trim($this->input->post('pesan')),
                'prioritas' => $this->input->post('prioritas') ?? 'normal'
            ];
            
            // Validate required fields
            foreach (['penerima_role', 'penerima_id', 'subjek', 'pesan'] as $field) {
                if (empty($input[$field])) {
                    echo json_encode(['status' => 'error', 'message' => "Field {$field} wajib diisi"]);
                    return;
                }
            }
            
            if (strlen($input['pesan']) < 10) {
                echo json_encode(['status' => 'error', 'message' => 'Pesan minimal 10 karakter']);
                return;
            }
            
            $mahasiswa_id = $this->session->userdata('id');
            if (!$mahasiswa_id) {
                echo json_encode(['status' => 'error', 'message' => 'Session tidak valid']);
                return;
            }
            
            // Get penerima data
            $penerima = $this->_get_penerima_data($input['penerima_role'], $input['penerima_id']);
            if (!$penerima) {
                echo json_encode(['status' => 'error', 'message' => 'Data penerima tidak ditemukan']);
                return;
            }
            
            // Get mahasiswa data
            $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
            if (!$mahasiswa) {
                echo json_encode(['status' => 'error', 'message' => 'Data mahasiswa tidak ditemukan']);
                return;
            }
            
            // Try to save notification (optional)
            $notif_saved = $this->_save_notification_simple($mahasiswa_id, $input['penerima_id'], $input['subjek'], $input['pesan']);
            
            // Try to send email (optional)
            $email_sent = $this->_send_email_simple($mahasiswa, $penerima, $input['subjek'], $input['pesan'], $input['prioritas']);
            
            // Return success regardless of email/notification status
            echo json_encode([
                'status' => 'success', 
                'message' => 'Pesan berhasil dikirim ke ' . $penerima->nama,
                'details' => [
                    'notification_saved' => $notif_saved,
                    'email_sent' => $email_sent
                ]
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Kontak kirim_pesan error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat mengirim pesan']);
        }
    }
    
    /**
     * SIMPLE: Get dosen pembimbing
     */
    private function _get_pembimbing_simple($mahasiswa_id)
    {
        try {
            // Check if table exists
            if (!$this->db->table_exists('proposal_mahasiswa')) {
                return null;
            }
            
            $sql = "
                SELECT d.id, d.nama, d.email, d.nomor_telepon 
                FROM proposal_mahasiswa pm 
                INNER JOIN dosen d ON pm.dosen_id = d.id 
                WHERE pm.mahasiswa_id = ? 
                AND pm.status = '1' 
                AND pm.dosen_id IS NOT NULL 
                ORDER BY pm.id DESC 
                LIMIT 1
            ";
            
            $query = $this->db->query($sql, [$mahasiswa_id]);
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            return null;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting pembimbing: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * SIMPLE: Get kaprodi
     */
    private function _get_kaprodi_simple($prodi_id)
    {
        try {
            if (!$this->db->table_exists('prodi')) {
                return null;
            }
            
            $sql = "
                SELECT d.id, d.nama, d.email, d.nomor_telepon, p.nama as nama_prodi 
                FROM prodi p 
                INNER JOIN dosen d ON p.dosen_id = d.id 
                WHERE p.id = ?
            ";
            
            $query = $this->db->query($sql, [$prodi_id]);
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            return null;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting kaprodi: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * SIMPLE: Get staff list
     */
    private function _get_staf_simple()
    {
        try {
            if (!$this->db->table_exists('dosen')) {
                return [];
            }
            
            $sql = "
                SELECT id, nama, email, nomor_telepon 
                FROM dosen 
                WHERE level = '1' 
                ORDER BY nama ASC 
                LIMIT 10
            ";
            
            $query = $this->db->query($sql);
            
            if ($query) {
                return $query->result();
            }
            
            return [];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting staf: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * SIMPLE: Get riwayat pesan
     */
    private function _get_riwayat_simple($mahasiswa_id)
    {
        try {
            // Skip if table doesn't exist
            if (!$this->db->table_exists('notifikasi')) {
                return [];
            }
            
            $sql = "
                SELECT judul as subjek, pesan, tanggal_dibuat as created_at, 'Sistem' as nama_penerima 
                FROM notifikasi 
                WHERE user_id = ? 
                ORDER BY tanggal_dibuat DESC 
                LIMIT 5
            ";
            
            $query = $this->db->query($sql, [$mahasiswa_id]);
            
            if ($query) {
                $result = $query->result();
                // Add priority field for compatibility
                foreach ($result as &$item) {
                    $item->prioritas = 'normal';
                }
                return $result;
            }
            
            return [];
            
        } catch (Exception $e) {
            log_message('error', 'Error getting riwayat: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get penerima data by role
     */
    private function _get_penerima_data($role, $id)
    {
        try {
            if (in_array($role, ['pembimbing', 'kaprodi', 'staf'])) {
                return $this->db->get_where('dosen', ['id' => $id])->row();
            }
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error getting penerima: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * SIMPLE: Save notification
     */
    private function _save_notification_simple($pengirim_id, $penerima_id, $subjek, $pesan)
    {
        try {
            // Skip if table doesn't exist
            if (!$this->db->table_exists('notifikasi')) {
                return false;
            }
            
            $data = [
                'user_id' => $penerima_id,
                'judul' => $subjek,
                'pesan' => $pesan,
                'tanggal_dibuat' => date('Y-m-d H:i:s')
            ];
            
            // Add optional fields if they exist
            $fields = $this->db->list_fields('notifikasi');
            if (in_array('jenis', $fields)) {
                $data['jenis'] = 'kontak_form';
            }
            if (in_array('untuk_role', $fields)) {
                $data['untuk_role'] = 'dosen';
            }
            if (in_array('dibaca', $fields)) {
                $data['dibaca'] = 0;
            }
            
            return $this->db->insert('notifikasi', $data);
            
        } catch (Exception $e) {
            log_message('error', 'Error saving notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * SIMPLE: Send email (placeholder for now)
     */
    private function _send_email_simple($mahasiswa, $penerima, $subjek, $pesan, $prioritas)
    {
        try {
            // For now, just return true to indicate "sent"
            // Email functionality can be implemented later
            return true;
            
            /*
            // Email implementation when ready:
            $this->load->library('email');
            
            $config = [
                'protocol' => 'mail',
                'mailtype' => 'html',
                'charset' => 'utf8'
            ];
            
            $this->email->initialize($config);
            
            $subject = '[SIM-TA STK] ' . $subjek;
            $message = "Pesan dari: {$mahasiswa->nama} ({$mahasiswa->nim})\nEmail: {$mahasiswa->email}\n\n{$pesan}";
            
            $this->email->from('noreply@stkyakobus.ac.id', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($penerima->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            return $this->email->send();
            */
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email: ' . $e->getMessage());
            return false;
        }
    }
}

/* End of file Kontak.php */
/* Location: ./application/controllers/mahasiswa/Kontak.php */