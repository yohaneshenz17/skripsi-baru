<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kontak Controller untuk Dosen - STK Yakobus
 * Disesuaikan dengan permintaan:
 * 1. Dropdown "Kirim Ke": Mahasiswa (khususnya mahasiswa bimbingan)
 * 2. Detail penerima sesuai database mahasiswa
 * 3. Email real, bukan notifikasi sistem
 * 4. WhatsApp contact untuk mahasiswa bimbingan
 */
class Kontak extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->database();
        $this->load->library(['session', 'email']);
        $this->load->helper('url');
        
        // Validasi akses dosen
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
        
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
    }

    public function index()
    {
        $data['title'] = 'Kontak Form - Dosen';
        return $this->load->view('dosen/kontak', $data);
    }
    
    /**
     * Get data untuk form kontak dosen
     * Mengembalikan: mahasiswa_bimbingan, semua_mahasiswa, kaprodi_list, staf_list
     */
    public function get_kontak_data()
    {
        header('Content-Type: application/json');
        
        try {
            $dosen_id = $this->session->userdata('id');
            
            if (!$dosen_id) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Session dosen tidak valid. Silakan login ulang.'
                ]);
                return;
            }
            
            $data = [
                'mahasiswa_bimbingan' => [],
                'semua_mahasiswa' => [],
                'kaprodi_list' => [],
                'staf_list' => []
            ];
            
            // 1. Get MAHASISWA BIMBINGAN berdasarkan proposal yang disetujui
            // FIXED: Sesuaikan dengan enum database (1 = approved, 2 = approved)
            $this->db->select('
                m.id, m.nim, m.nama, m.email, m.nomor_telepon, 
                p.nama as nama_prodi,
                pm.judul as judul_proposal,
                pm.status_kaprodi as status_proposal
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            // PERBAIKAN: Gunakan status yang sesuai dengan enum database
            $this->db->where_in('pm.status_kaprodi', ['1', '2']); // 1=setuju, 2=approved
            $this->db->order_by('m.nama', 'ASC');
            $query_bimbingan = $this->db->get();
            
            if ($query_bimbingan->num_rows() > 0) {
                $data['mahasiswa_bimbingan'] = $query_bimbingan->result();
            }
            
            // 2. Get SEMUA MAHASISWA (untuk opsi tambahan jika perlu)
            $this->db->select('
                m.id, m.nim, m.nama, m.email, m.nomor_telepon,
                p.nama as nama_prodi
            ');
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->order_by('m.nama', 'ASC');
            $query_mahasiswa = $this->db->get();
            
            if ($query_mahasiswa->num_rows() > 0) {
                $data['semua_mahasiswa'] = $query_mahasiswa->result();
            }
            
            // 3. Get KAPRODI untuk komunikasi dengan atasan
            $this->db->select('d.id, d.nama, d.email, d.nomor_telepon, p.nama as nama_prodi');
            $this->db->from('dosen d');
            $this->db->join('prodi p', 'd.id = p.dosen_id', 'left');
            $this->db->where('d.level', '4');
            $this->db->order_by('d.nama', 'ASC');
            $query_kaprodi = $this->db->get();
            
            if ($query_kaprodi->num_rows() > 0) {
                $data['kaprodi_list'] = $query_kaprodi->result();
            }
            
            // 4. Get STAF/ADMIN untuk keperluan administratif
            $this->db->select('id, nama, email, nomor_telepon');
            $this->db->from('dosen');
            $this->db->where('level', '5');
            $this->db->order_by('nama', 'ASC');
            $query_staf = $this->db->get();
            
            if ($query_staf->num_rows() > 0) {
                $data['staf_list'] = $query_staf->result();
            }
            
            echo json_encode([
                'status' => 'success', 
                'data' => $data,
                'debug' => [
                    'mahasiswa_bimbingan_count' => count($data['mahasiswa_bimbingan']),
                    'semua_mahasiswa_count' => count($data['semua_mahasiswa']),
                    'kaprodi_count' => count($data['kaprodi_list']),
                    'staf_count' => count($data['staf_list']),
                    'dosen_id' => $dosen_id,
                    'last_query_bimbingan' => $this->db->last_query(),
                    'environment' => ENVIRONMENT
                ]
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Dosen Kontak get_kontak_data error: ' . $e->getMessage());
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get data mahasiswa bimbingan untuk WhatsApp contact
     */
    public function get_mahasiswa_bimbingan()
    {
        header('Content-Type: application/json');
        
        try {
            $dosen_id = $this->session->userdata('id');
            
            if (!$dosen_id) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Session dosen tidak valid'
                ]);
                return;
            }
            
            // Get mahasiswa bimbingan dengan status proposal yang disetujui
            // FIXED: Gunakan enum database yang benar
            $this->db->select('
                m.id, m.nim, m.nama, m.email, m.nomor_telepon,
                p.nama as nama_prodi,
                pm.judul as judul_proposal,
                pm.created_at as tanggal_bimbingan,
                COUNT(jb.id) as total_bimbingan
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'left');
            $this->db->join('jurnal_bimbingan jb', 'pm.id = jb.proposal_id', 'left');
            $this->db->where('pm.dosen_id', $dosen_id);
            // PERBAIKAN: Gunakan status yang sesuai dengan enum database
            $this->db->where_in('pm.status_kaprodi', ['1', '2']); // 1=setuju, 2=approved
            $this->db->group_by('m.id, pm.id');
            $this->db->order_by('pm.created_at', 'DESC');
            
            $query = $this->db->get();
            
            if ($query->num_rows() > 0) {
                $mahasiswa_list = $query->result();
                
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'mahasiswa_bimbingan' => $mahasiswa_list,
                        'total_mahasiswa' => count($mahasiswa_list)
                    ],
                    'message' => 'Data mahasiswa bimbingan ditemukan'
                ]);
            } else {
                echo json_encode([
                    'status' => 'info',
                    'message' => 'Belum ada mahasiswa bimbingan yang disetujui',
                    'data' => [
                        'mahasiswa_bimbingan' => [],
                        'total_mahasiswa' => 0
                    ],
                    'debug' => [
                        'dosen_id' => $dosen_id,
                        'query_executed' => $this->db->last_query()
                    ]
                ]);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Dosen get_mahasiswa_bimbingan error: ' . $e->getMessage());
            
            echo json_encode([
                'status' => 'error', 
                'message' => 'Database error: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'dosen_id' => $dosen_id ?? 'NULL'
                ]
            ]);
        }
    }
    
    /**
     * Kirim pesan via EMAIL - REAL EMAIL
     */
    public function kirim_pesan()
    {
        header('Content-Type: application/json');
        
        try {
            // Validasi input
            $input = [
                'penerima_kategori' => $this->input->post('penerima_kategori'), // mahasiswa/kaprodi/staf
                'penerima_id' => $this->input->post('penerima_id'),
                'subjek' => trim($this->input->post('subjek')),
                'pesan' => trim($this->input->post('pesan')),
                'prioritas' => $this->input->post('prioritas') ?? 'normal'
            ];
            
            // Validate required fields
            foreach (['penerima_kategori', 'penerima_id', 'subjek', 'pesan'] as $field) {
                if (empty($input[$field])) {
                    echo json_encode(['status' => 'error', 'message' => "Field {$field} wajib diisi"]);
                    return;
                }
            }
            
            if (strlen($input['pesan']) < 10) {
                echo json_encode(['status' => 'error', 'message' => 'Pesan minimal 10 karakter']);
                return;
            }
            
            $dosen_id = $this->session->userdata('id');
            if (!$dosen_id) {
                echo json_encode(['status' => 'error', 'message' => 'Session tidak valid']);
                return;
            }
            
            // Get dosen data
            $dosen = $this->db->get_where('dosen', ['id' => $dosen_id])->row();
            if (!$dosen) {
                echo json_encode(['status' => 'error', 'message' => 'Data dosen tidak ditemukan']);
                return;
            }
            
            // Get penerima data berdasarkan kategori
            $penerima = $this->_get_penerima_data($input['penerima_kategori'], $input['penerima_id']);
            if (!$penerima) {
                echo json_encode(['status' => 'error', 'message' => 'Data penerima tidak ditemukan']);
                return;
            }
            
            // SEND REAL EMAIL
            $email_sent = $this->_send_real_email($dosen, $penerima, $input);
            
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
            log_message('error', 'Dosen Kontak kirim_pesan error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat mengirim email']);
        }
    }
    
    /**
     * Get data penerima berdasarkan kategori
     */
    private function _get_penerima_data($kategori, $penerima_id)
    {
        if ($kategori === 'mahasiswa') {
            return $this->db->get_where('mahasiswa', ['id' => $penerima_id])->row();
        } elseif ($kategori === 'kaprodi' || $kategori === 'staf') {
            return $this->db->get_where('dosen', ['id' => $penerima_id])->row();
        }
        return null;
    }
    
    /**
     * Send REAL EMAIL menggunakan konfigurasi yang sudah terbukti bekerja
     */
    private function _send_real_email($dosen, $penerima, $input)
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
            $message = $this->_get_email_template($dosen, $penerima, $input);
            
            // Setup email
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($penerima->email);
            $this->email->reply_to($dosen->email, $dosen->nama);
            $this->email->subject($subject);
            $this->email->message($message);
            
            // Send email
            $sent = $this->email->send();
            
            if ($sent) {
                log_message('info', 'Email berhasil dikirim dari dosen: ' . $dosen->nama . ' ke: ' . $penerima->email);
            } else {
                log_message('error', 'Gagal mengirim email dari dosen: ' . $this->email->print_debugger());
            }
            
            return $sent;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email from dosen: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Email template untuk dosen
     */
    private function _get_email_template($dosen, $penerima, $input)
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
        
        // Tentukan salutation berdasarkan jenis penerima
        $penerima_nim = isset($penerima->nim) ? $penerima->nim : '';
        $role_penerima = $penerima_nim ? 'Mahasiswa' : 'Dosen/Staf';
        
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
                                        🎓 Pesan dari Dosen
                                    </h1>
                                    <p style='color: #ffffff; margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;'>
                                        Sistem Informasi Manajemen Tugas Akhir
                                    </p>
                                </td>
                            </tr>";
                            
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
                                        <strong>{$dosen->nama}</strong><br>
                                        <em>Dosen STK Santo Yakobus</em>
                                    </p>
                                </td>
                            </tr>
                            
                            <!-- Sender Info -->
                            <tr>
                                <td style='background-color: #f8f9fa; padding: 20px 30px; border-top: 1px solid #dee2e6;'>
                                    <h4 style='color: #333333; margin: 0 0 15px 0; font-size: 16px;'>
                                        👨‍🏫 Informasi Pengirim:
                                    </h4>
                                    <table width='100%' cellpadding='0' cellspacing='0'>
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0; width: 100px;'>
                                                <strong>Nama:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                {$dosen->nama}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0;'>
                                                <strong>Jabatan:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                Dosen STK Santo Yakobus
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0;'>
                                                <strong>Email:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                <a href='mailto:{$dosen->email}' style='color: {$prioritas_color}; text-decoration: none;'>
                                                    {$dosen->email}
                                                </a>
                                            </td>
                                        </tr>";
                                        
        if ($dosen->nomor_telepon) {
            $template .= "
                                        <tr>
                                            <td style='color: #666666; font-size: 14px; padding: 3px 0;'>
                                                <strong>Telepon:</strong>
                                            </td>
                                            <td style='color: #333333; font-size: 14px; padding: 3px 0;'>
                                                <a href='tel:{$dosen->nomor_telepon}' style='color: {$prioritas_color}; text-decoration: none;'>
                                                    {$dosen->nomor_telepon}
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
                            </tr>";
                            
        // Tambahan info untuk mahasiswa
        if ($penerima_nim) {
            $template .= "
                            <tr>
                                <td style='background-color: #e7f3ff; padding: 15px 30px; border-top: 1px solid #b3d9ff;'>
                                    <p style='margin: 0; font-size: 12px; color: #0066cc; text-align: center;'>
                                        <strong>📚 Untuk Mahasiswa:</strong> 
                                        Jika ada pertanyaan lebih lanjut, silakan balas email ini atau hubungi dosen melalui WhatsApp 
                                        (jika tersedia) untuk komunikasi yang lebih cepat.
                                    </p>
                                </td>
                            </tr>";
        }
        
        $template .= "
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
/* Location: ./application/controllers/dosen/Kontak.php */