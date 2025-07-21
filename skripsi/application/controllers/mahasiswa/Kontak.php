<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kontak Controller - Fixed Version for STK Yakobus
 * Disesuaikan dengan permintaan:
 * 1. Dropdown "Kirim Ke": Kaprodi, Dosen, Staf/Admin
 * 2. Detail penerima sesuai database
 * 3. Email real, bukan notifikasi sistem
 */
class Kontak extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->library(['session', 'email']);
        $this->load->helper('url');
        
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
     * Get data untuk form kontak - FIXED VERSION
     * Mengembalikan: kaprodi_list, dosen_list, staf_list
     */
    public function get_kontak_data()
    {
        header('Content-Type: application/json');
        
        try {
            $mahasiswa_id = $this->session->userdata('id');
            
            if (!$mahasiswa_id) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Session mahasiswa tidak valid. Silakan login ulang.'
                ]);
                return;
            }
            
            $data = [
                'kaprodi_list' => [],
                'dosen_list' => [],
                'staf_list' => []
            ];
            
            // 1. Get KAPRODI (level = '4') - 2 kaprodi
            $this->db->select('d.id, d.nama, d.email, d.nomor_telepon, p.nama as nama_prodi');
            $this->db->from('dosen d');
            $this->db->join('prodi p', 'd.id = p.dosen_id', 'left');
            $this->db->where('d.level', '4');
            $this->db->order_by('d.nama', 'ASC');
            $query_kaprodi = $this->db->get();
            
            if ($query_kaprodi->num_rows() > 0) {
                $data['kaprodi_list'] = $query_kaprodi->result();
            }
            
            // 2. Get DOSEN (level = '2') - 15 dosen
            $this->db->select('d.id, d.nama, d.email, d.nomor_telepon, p.nama as nama_prodi');
            $this->db->from('dosen d');
            $this->db->join('prodi p', 'd.prodi_id = p.id', 'left');
            $this->db->where('d.level', '2');
            $this->db->order_by('d.nama', 'ASC');
            $query_dosen = $this->db->get();
            
            if ($query_dosen->num_rows() > 0) {
                $data['dosen_list'] = $query_dosen->result();
            }
            
            // 3. Get STAF/ADMIN (level = '1') - 1 orang: Yohanes Hendro Pranyoto
            $this->db->select('id, nama, email, nomor_telepon');
            $this->db->from('dosen');
            $this->db->where('level', '1');
            $this->db->order_by('nama', 'ASC');
            $query_staf = $this->db->get();
            
            if ($query_staf->num_rows() > 0) {
                $data['staf_list'] = $query_staf->result();
            }
            
            echo json_encode([
                'status' => 'success', 
                'data' => $data,
                'debug' => [
                    'kaprodi_count' => count($data['kaprodi_list']),
                    'dosen_count' => count($data['dosen_list']),
                    'staf_count' => count($data['staf_list'])
                ]
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Kontak get_kontak_data error: ' . $e->getMessage());
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Kirim pesan via EMAIL - REAL EMAIL, bukan notifikasi sistem
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
            
            // Get mahasiswa data
            $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
            if (!$mahasiswa) {
                echo json_encode(['status' => 'error', 'message' => 'Data mahasiswa tidak ditemukan']);
                return;
            }
            
            // Get penerima data
            $penerima = $this->db->get_where('dosen', ['id' => $input['penerima_id']])->row();
            if (!$penerima) {
                echo json_encode(['status' => 'error', 'message' => 'Data penerima tidak ditemukan']);
                return;
            }
            
            // SEND REAL EMAIL (menggunakan config yang sudah terbukti bekerja)
            $email_sent = $this->_send_real_email($mahasiswa, $penerima, $input);
            
            if ($email_sent) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Email berhasil dikirim ke ' . $penerima->nama . ' (' . $penerima->email . ')'
                ]);
            } else {
                echo json_encode([
                    'status' => 'warning', 
                    'message' => 'Email tidak dapat dikirim. Silakan coba lagi atau hubungi admin.'
                ]);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Kontak kirim_pesan error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat mengirim email']);
        }
    }
    
    /**
     * Send REAL EMAIL menggunakan konfigurasi yang sudah terbukti bekerja
     */
    private function _send_real_email($mahasiswa, $penerima, $input)
    {
        try {
            // Konfigurasi email yang sudah TERBUKTI BEKERJA di STK Yakobus
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
            if ($input['prioritas'] === 'high') {
                $prioritas_text = '[PRIORITAS TINGGI] ';
            } elseif ($input['prioritas'] === 'urgent') {
                $prioritas_text = '[URGENT] ';
            }
            
            $subject = $prioritas_text . '[SIM-TA STK] ' . $input['subjek'];
            
            // HTML Email Template
            $message = $this->_get_email_template($mahasiswa, $penerima, $input);
            
            // Setup email
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($penerima->email);
            $this->email->reply_to($mahasiswa->email, $mahasiswa->nama);
            $this->email->subject($subject);
            $this->email->message($message);
            
            // Send email
            $sent = $this->email->send();
            
            if ($sent) {
                log_message('info', 'Email berhasil dikirim ke: ' . $penerima->email . ' dari mahasiswa: ' . $mahasiswa->nama);
            } else {
                log_message('error', 'Gagal mengirim email: ' . $this->email->print_debugger());
            }
            
            return $sent;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending real email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Email template yang menarik dan profesional
     */
    private function _get_email_template($mahasiswa, $penerima, $input)
    {
        $prioritas_badge = '';
        $prioritas_color = '#007bff';
        
        if ($input['prioritas'] === 'high') {
            $prioritas_badge = '<span style="background: #ffc107; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">PRIORITAS TINGGI</span>';
            $prioritas_color = '#ffc107';
        } elseif ($input['prioritas'] === 'urgent') {
            $prioritas_badge = '<span style="background: #dc3545; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">URGENT</span>';
            $prioritas_color = '#dc3545';
        }
        
        $template = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$input['subjek']}</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f8f9fa;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8f9fa;'>
                <tr>
                    <td align='center' style='padding: 40px 20px;'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;'>
                            <!-- Header -->
                            <tr>
                                <td style='background: linear-gradient(135deg, {$prioritas_color} 0%, #0056b3 100%); padding: 30px; text-align: center;'>
                                    <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;'>
                                        📧 Pesan dari Mahasiswa
                                    </h1>
                                    <p style='color: #ffffff; margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;'>
                                        Sistem Informasi Manajemen Tugas Akhir
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Priority Badge -->";
                            
        if ($prioritas_badge) {
            $template .= "
                            <tr>
                                <td style='padding: 15px 30px 0 30px; text-align: center;'>
                                    {$prioritas_badge}
                                </td>
                            </tr>";
        }
        
        $template .= "
                            
                            <!-- Content -->
                            <tr>
                                <td style='padding: 30px;'>
                                    <h2 style='color: #333333; margin: 0 0 20px 0; font-size: 20px;'>
                                        {$input['subjek']}
                                    </h2>
                                    
                                    <p style='color: #333333; margin: 0 0 15px 0; font-size: 16px;'>
                                        Yth. <strong>{$penerima->nama}</strong>,
                                    </p>
                                    
                                    <div style='background-color: #f8f9fa; border-left: 4px solid {$prioritas_color}; padding: 20px; margin: 20px 0; border-radius: 4px;'>
                                        " . nl2br(htmlspecialchars($input['pesan'])) . "
                                    </div>
                                    
                                    <p style='color: #666666; margin: 20px 0 0 0; font-size: 14px;'>
                                        Hormat saya,<br>
                                        <strong>{$mahasiswa->nama}</strong>
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Sender Info -->
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 20px 30px; border-top: 1px solid #dee2e6;'>
                                    <h4 style='color: #333333; margin: 0 0 15px 0; font-size: 16px;'>
                                        📋 Informasi Pengirim:
                                    </h4>
                                    <table width='100%' cellpadding='0' cellspacing='0'>
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0; width: 100px;'>
                                                <strong>Nama:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                {$mahasiswa->nama}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0;'>
                                                <strong>NIM:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                {$mahasiswa->nim}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0;'>
                                                <strong>Email:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                <a href='mailto:{$mahasiswa->email}' style='color: {$prioritas_color}; text-decoration: none;'>
                                                    {$mahasiswa->email}
                                                </a>
                                            </td>
                                        </tr>";
                                        
        if ($mahasiswa->nomor_telepon) {
            $template .= "
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0;'>
                                                <strong>Telepon:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                <a href='tel:{$mahasiswa->nomor_telepon}' style='color: {$prioritas_color}; text-decoration: none;'>
                                                    {$mahasiswa->nomor_telepon}
                                                </a>
                                            </td>
                                        </tr>";
        }
        
        $template .= "
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0;'>
                                                <strong>Waktu:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                " . date('d F Y, H:i') . " WIT
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style='background-color: #333333; color: #ffffff; padding: 20px 30px; text-align: center;'>
                                    <p style='margin: 0 0 10px 0; font-size: 16px; font-weight: bold;'>
                                        🏫 STK Santo Yakobus Merauke
                                    </p>
                                    <p style='margin: 0; font-size: 12px; opacity: 0.8; line-height: 1.4;'>
                                        Jl. Missi 2, Mandala, Merauke, Papua Selatan<br>
                                        Telepon: (0971) 333-0264 | Email: sipd@stkyakobus.ac.id<br>
                                        <a href='https://stkyakobus.ac.id' style='color: #ffffff; text-decoration: none;'>www.stkyakobus.ac.id</a>
                                    </p>
                                    <p style='margin: 15px 0 0 0; font-size: 11px; opacity: 0.6;'>
                                        Email ini dikirim otomatis dari Sistem Informasi Manajemen Tugas Akhir. 
                                        Silakan balas langsung ke email pengirim untuk merespons.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
        
        return $template;
    }
}

/* End of file Kontak.php */
/* Location: ./application/controllers/mahasiswa/Kontak.php */