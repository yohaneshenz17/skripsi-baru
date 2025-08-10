<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kontak Controller untuk Kaprodi - COMPLETE FIXED VERSION
 * 
 * PERBAIKAN:
 * 1. Email real menggunakan konfigurasi yang terbukti working
 * 2. Database schema yang benar (user_id, bukan pengirim_id)
 * 3. Error handling yang komprehensif
 * 4. Template email yang rapi
 * 
 * File: application/controllers/kaprodi/Kontak.php
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
    }

    public function index()
    {
        $data['title'] = 'Kontak Form - Kaprodi';
        $data['content'] = $this->load->view('kaprodi/kontak', '', TRUE);
        
        $this->load->view('template/kaprodi', $data);
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
                    'message' => 'Session tidak valid'
                ]);
                return;
            }
            
            $data = [
                'dosen_list' => [],
                'staf_list' => [],
                'mahasiswa_list' => []
            ];
            
            // 1. Get DOSEN
            $this->db->select('id, nama, email, nomor_telepon');
            $this->db->from('dosen');
            $this->db->where('level', '2');
            $this->db->where('id !=', $kaprodi_id);
            $this->db->order_by('nama', 'ASC');
            $query_dosen = $this->db->get();
            
            if ($query_dosen && $query_dosen->num_rows() > 0) {
                $data['dosen_list'] = $query_dosen->result();
            }
            
            // 2. Get STAF/ADMIN
            $this->db->select('id, nama, email, nomor_telepon, level');
            $this->db->from('dosen');
            $this->db->where_in('level', ['1', '5']);
            $this->db->order_by('nama', 'ASC');
            $query_staf = $this->db->get();
            
            if ($query_staf && $query_staf->num_rows() > 0) {
                $staf_result = $query_staf->result();
                foreach ($staf_result as $staf) {
                    $staf->role_name = ($staf->level == '1') ? 'Admin' : 'Staf';
                }
                $data['staf_list'] = $staf_result;
            }
            
            // 3. Get MAHASISWA
            $this->db->select('id, nim, nama, email, nomor_telepon');
            $this->db->from('mahasiswa');
            $this->db->order_by('nama', 'ASC');
            $this->db->limit(50);
            $query_mahasiswa = $this->db->get();
            
            if ($query_mahasiswa && $query_mahasiswa->num_rows() > 0) {
                $data['mahasiswa_list'] = $query_mahasiswa->result();
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Kaprodi kontak get_kontak_data error: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan saat memuat data'
            ]);
        }
    }
    
    /**
     * Kirim pesan dari kaprodi - FIXED dengan EMAIL REAL
     */
    public function kirim_pesan()
    {
        header('Content-Type: application/json');
        
        try {
            if ($this->input->method() !== 'post') {
                echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
                return;
            }
            
            $kaprodi_id = $this->session->userdata('id');
            
            if (!$kaprodi_id) {
                echo json_encode(['status' => 'error', 'message' => 'Session tidak valid']);
                return;
            }
            
            // Get input data
            $penerima_role = $this->input->post('penerima_role');
            $penerima_id = $this->input->post('penerima_id');
            $subjek = $this->input->post('subjek');
            $pesan = $this->input->post('pesan');
            $prioritas = $this->input->post('prioritas');
            
            // Validate input
            if (empty($penerima_role) || empty($penerima_id) || empty($subjek) || empty($pesan)) {
                echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi']);
                return;
            }
            
            if (strlen($pesan) < 10) {
                echo json_encode(['status' => 'error', 'message' => 'Pesan minimal 10 karakter']);
                return;
            }
            
            // Get kaprodi data
            $kaprodi = $this->db->get_where('dosen', ['id' => $kaprodi_id])->row();
            if (!$kaprodi) {
                echo json_encode(['status' => 'error', 'message' => 'Data kaprodi tidak ditemukan']);
                return;
            }
            
            // Get penerima data
            $penerima = $this->_get_penerima_data_safe($penerima_role, $penerima_id);
            if (!$penerima) {
                echo json_encode(['status' => 'error', 'message' => 'Data penerima tidak ditemukan']);
                return;
            }
            
            // Save notification (non-critical)
            $notif_saved = false;
            try {
                $notif_saved = $this->_save_notification_safe($penerima_id, $subjek, $pesan);
            } catch (Exception $e) {
                log_message('error', 'Failed to save notification: ' . $e->getMessage());
            }
            
            // FIXED: Send REAL EMAIL dengan konfigurasi yang benar
            $email_sent = false;
            try {
                $email_sent = $this->_send_email_safe($kaprodi, $penerima, $subjek, $pesan, $prioritas);
            } catch (Exception $e) {
                log_message('error', 'Failed to send email: ' . $e->getMessage());
            }
            
            // Response berdasarkan hasil email
            if ($email_sent) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "Pesan berhasil dikirim ke {$penerima->nama}",
                    'penerima' => $penerima->nama,
                    'details' => [
                        'notification_saved' => $notif_saved,
                        'email_sent' => $email_sent
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'warning',
                    'message' => "Notifikasi tersimpan, tetapi email gagal dikirim ke {$penerima->nama}. Silakan coba lagi.",
                    'penerima' => $penerima->nama,
                    'details' => [
                        'notification_saved' => $notif_saved,
                        'email_sent' => $email_sent
                    ]
                ]);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Kaprodi kontak kirim_pesan error: ' . $e->getMessage());
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan saat mengirim pesan'
            ]);
        }
    }
    
    /**
     * Get WhatsApp contacts
     */
    public function get_whatsapp_contacts($role = null)
    {
        header('Content-Type: application/json');
        
        try {
            $contacts = [];
            
            if (!$role || $role === 'dosen') {
                $this->db->select('id, nama, nomor_telepon');
                $this->db->from('dosen');
                $this->db->where('level', '2');
                $this->db->where('nomor_telepon IS NOT NULL');
                $this->db->where('nomor_telepon !=', '');
                $this->db->limit(10);
                $dosen_contacts = $this->db->get();
                
                if ($dosen_contacts && $dosen_contacts->num_rows() > 0) {
                    foreach ($dosen_contacts->result() as $contact) {
                        $phone = $this->_format_whatsapp_number($contact->nomor_telepon);
                        if (!empty($phone)) {
                            $message = urlencode('Halo ' . $contact->nama . ', saya Kaprodi STK Santo Yakobus.');
                            
                            $contacts[] = [
                                'type' => 'dosen',
                                'name' => $contact->nama,
                                'phone' => $phone,
                                'url' => "https://wa.me/{$phone}?text={$message}"
                            ];
                        }
                    }
                }
            }
            
            if (!$role || $role === 'staf') {
                $this->db->select('id, nama, nomor_telepon, level');
                $this->db->from('dosen');
                $this->db->where_in('level', ['1', '5']);
                $this->db->where('nomor_telepon IS NOT NULL');
                $this->db->where('nomor_telepon !=', '');
                $this->db->limit(10);
                $staf_contacts = $this->db->get();
                
                if ($staf_contacts && $staf_contacts->num_rows() > 0) {
                    foreach ($staf_contacts->result() as $contact) {
                        $phone = $this->_format_whatsapp_number($contact->nomor_telepon);
                        if (!empty($phone)) {
                            $message = urlencode('Halo ' . $contact->nama . ', saya Kaprodi STK Santo Yakobus.');
                            $role_name = ($contact->level == '1') ? 'Admin' : 'Staf';
                            
                            $contacts[] = [
                                'type' => 'staf',
                                'name' => $contact->nama . ' (' . $role_name . ')',
                                'phone' => $phone,
                                'url' => "https://wa.me/{$phone}?text={$message}"
                            ];
                        }
                    }
                }
            }
            
            if (!$role || $role === 'mahasiswa') {
                $this->db->select('id, nim, nama, nomor_telepon');
                $this->db->from('mahasiswa');
                $this->db->where('nomor_telepon IS NOT NULL');
                $this->db->where('nomor_telepon !=', '');
                $this->db->order_by('nama', 'ASC');
                $this->db->limit(10);
                $mahasiswa_contacts = $this->db->get();
                
                if ($mahasiswa_contacts && $mahasiswa_contacts->num_rows() > 0) {
                    foreach ($mahasiswa_contacts->result() as $contact) {
                        $phone = $this->_format_whatsapp_number($contact->nomor_telepon);
                        if (!empty($phone)) {
                            $message = urlencode('Halo ' . $contact->nama . ', saya Kaprodi STK Santo Yakobus.');
                            
                            $contacts[] = [
                                'type' => 'mahasiswa',
                                'name' => $contact->nama . ' (' . $contact->nim . ')',
                                'phone' => $phone,
                                'url' => "https://wa.me/{$phone}?text={$message}"
                            ];
                        }
                    }
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $contacts,
                'total' => count($contacts)
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Error getting WhatsApp contacts: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error', 
                'message' => 'Gagal mengambil kontak WhatsApp',
                'data' => []
            ]);
        }
    }
    
    /**
     * Get data penerima berdasarkan role dan ID
     */
    private function _get_penerima_data_safe($role, $id)
    {
        try {
            switch ($role) {
                case 'dosen':
                    $this->db->where('id', $id);
                    $this->db->where('level', '2');
                    return $this->db->get('dosen')->row();
                    
                case 'staf':
                    $this->db->where('id', $id);
                    $this->db->where_in('level', ['1', '5']);
                    return $this->db->get('dosen')->row();
                    
                case 'mahasiswa':
                    $this->db->where('id', $id);
                    return $this->db->get('mahasiswa')->row();
                    
                default:
                    return null;
            }
        } catch (Exception $e) {
            log_message('error', 'Error getting penerima data: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Save notification - FIXED SCHEMA sesuai dengan dosen/mahasiswa
     */
    private function _save_notification_safe($penerima_id, $subjek, $pesan)
    {
        try {
            if (!$this->db->table_exists('notifikasi')) {
                return true; // Skip jika tidak ada tabel
            }
            
            // SCHEMA YANG BENAR: gunakan 'user_id' bukan 'pengirim_id'
            $data = [
                'user_id' => $penerima_id,          // ✅ Field yang benar
                'judul' => $subjek,
                'pesan' => $pesan,
                'created_at' => date('Y-m-d H:i:s')  // ✅ Sesuai schema
            ];
            
            // Check fields yang ada
            $fields = $this->db->list_fields('notifikasi');
            $safe_data = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $fields)) {
                    $safe_data[$key] = $value;
                }
            }
            
            // Alternative field names
            if (in_array('tanggal_dibuat', $fields) && !in_array('created_at', $fields)) {
                $safe_data['tanggal_dibuat'] = date('Y-m-d H:i:s');
                unset($safe_data['created_at']);
            }
            
            if (in_array('jenis', $fields)) {
                $safe_data['jenis'] = 'kontak_kaprodi';
            }
            
            if (in_array('untuk_role', $fields)) {
                $safe_data['untuk_role'] = 'umum';
            }
            
            if (in_array('dibaca', $fields)) {
                $safe_data['dibaca'] = 0;
            }
            
            if (count($safe_data) > 0) {
                return $this->db->insert('notifikasi', $safe_data);
            }
            
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'Error saving notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email - FIXED VERSION dengan konfigurasi yang TERBUKTI WORKING
     */
    private function _send_email_safe($kaprodi, $penerima, $subjek, $pesan, $prioritas)
    {
        try {
            // Konfigurasi email yang TERBUKTI BEKERJA di STK Yakobus
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_timeout' => 30,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'validation' => TRUE,
                'priority' => 3,
                'crlf' => "\r\n",
                'smtp_crypto' => 'tls',
                'wordwrap' => TRUE,
                'wrapchars' => 76,
                'smtp_debug' => FALSE,
                'smtp_keepalive' => FALSE,
                'smtp_auto_tls' => TRUE
            ];
            
            $this->email->initialize($config);
            $this->email->clear();
            
            // Setup email content
            $prioritas_text = '';
            if ($prioritas === 'high') {
                $prioritas_text = '[PRIORITAS TINGGI] ';
            } elseif ($prioritas === 'urgent') {
                $prioritas_text = '[URGENT] ';
            }
            
            $subject = $prioritas_text . '[SIM-TA STK] ' . $subjek;
            
            // HTML Email Template yang rapi
            $message = $this->_get_email_template($kaprodi, $penerima, $subjek, $pesan, $prioritas);
            
            // Setup email
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($penerima->email);
            $this->email->reply_to($kaprodi->email, $kaprodi->nama);
            $this->email->subject($subject);
            $this->email->message($message);
            
            // Send email
            $sent = $this->email->send();
            
            if ($sent) {
                log_message('info', "Email berhasil dikirim dari Kaprodi {$kaprodi->nama} ke {$penerima->nama} ({$penerima->email})");
                return true;
            } else {
                log_message('error', 'Email gagal dikirim: ' . $this->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exception saat kirim email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Email template yang rapi untuk Kaprodi
     */
    private function _get_email_template($kaprodi, $penerima, $subjek, $pesan, $prioritas)
    {
        $prioritas_badge = '';
        if ($prioritas === 'high') {
            $prioritas_badge = '<span style="background:#ff6b35; color:white; padding:4px 8px; border-radius:4px; font-size:12px;">PRIORITAS TINGGI</span>';
        } elseif ($prioritas === 'urgent') {
            $prioritas_badge = '<span style="background:#dc3545; color:white; padding:4px 8px; border-radius:4px; font-size:12px;">URGENT</span>';
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Pesan dari Kaprodi</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            
            <!-- Header -->
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h2 style='margin: 0; font-size: 24px;'>📧 Pesan dari Kaprodi</h2>
                <p style='margin: 5px 0 0 0; opacity: 0.9;'>Sistem Informasi Manajemen Tugas Akhir</p>
            </div>
            
            <!-- Content -->
            <div style='background: #ffffff; padding: 30px; border: 1px solid #e9ecef; border-top: none;'>
                
                <!-- Prioritas Badge -->
                " . ($prioritas_badge ? "<div style='margin-bottom: 20px;'>{$prioritas_badge}</div>" : "") . "
                
                <!-- Info Pengirim -->
                <div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin-bottom: 20px;'>
                    <p style='margin: 0; font-weight: bold; color: #007bff;'>Pengirim:</p>
                    <p style='margin: 5px 0 0 0;'>{$kaprodi->nama} (Kaprodi)</p>
                    <p style='margin: 0; font-size: 14px; color: #6c757d;'>STK Santo Yakobus Merauke</p>
                </div>
                
                <!-- Info Penerima -->
                <div style='margin-bottom: 20px;'>
                    <p style='margin: 0; font-weight: bold;'>Kepada:</p>
                    <p style='margin: 5px 0 0 0;'>{$penerima->nama}</p>
                    <p style='margin: 0; font-size: 14px; color: #6c757d;'>{$penerima->email}</p>
                </div>
                
                <!-- Subjek -->
                <div style='margin-bottom: 20px;'>
                    <p style='margin: 0; font-weight: bold;'>Subjek:</p>
                    <p style='margin: 5px 0 0 0;'>{$subjek}</p>
                </div>
                
                <!-- Divider -->
                <hr style='border: none; border-top: 2px solid #e9ecef; margin: 25px 0;'>
                
                <!-- Pesan -->
                <div style='background: #ffffff; padding: 20px; border: 1px solid #dee2e6; border-radius: 6px;'>
                    <p style='margin: 0 0 10px 0; font-weight: bold; color: #495057;'>Pesan:</p>
                    <div style='color: #212529; line-height: 1.7;'>"
                        . nl2br(htmlspecialchars($pesan)) . 
                    "</div>
                </div>
                
            </div>
            
            <!-- Footer -->
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border: 1px solid #e9ecef; border-top: none; border-radius: 0 0 8px 8px;'>
                <p style='margin: 0; font-size: 14px; color: #6c757d;'>
                    <strong>STK Santo Yakobus Merauke</strong><br>
                    Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
                    📞 09713330264 | ✉️ sipd@stkyakobus.ac.id
                </p>
                <hr style='border: none; border-top: 1px solid #dee2e6; margin: 15px 0;'>
                <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                    Email otomatis dari Sistem Informasi Manajemen Tugas Akhir<br>
                    Mohon tidak membalas langsung ke email ini.
                </p>
            </div>
            
        </body>
        </html>";
    }
    
    /**
     * Format nomor telepon untuk WhatsApp
     */
    private function _format_whatsapp_number($phone)
    {
        if (empty($phone)) {
            return '';
        }
        
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (empty($phone)) {
            return '';
        }
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}

/* End of file Kontak.php */
/* Location: ./application/controllers/kaprodi/Kontak.php */