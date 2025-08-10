<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Kontak Controller untuk Kaprodi - STK Yakobus
 * Mengikuti pola controller dosen/Kontak.php yang sudah stable
 * Disesuaikan untuk akses Kaprodi dengan penerima: Dosen, Staf/Admin, Semua Mahasiswa prodi
 * 
 * File: application/controllers/kaprodi/Kontak.php
 */
class Kontak extends MY_Controller
{
    private $kaprodi_id;
    private $prodi_id;

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
        
        $this->kaprodi_id = $this->session->userdata('id');
        
        // Get prodi_id dari session atau database
        $this->prodi_id = $this->session->userdata('prodi_id');
        if (!$this->prodi_id) {
            $kaprodi = $this->db->get_where('prodi', ['dosen_id' => $this->kaprodi_id])->row();
            if ($kaprodi) {
                $this->session->set_userdata('prodi_id', $kaprodi->id);
                $this->prodi_id = $kaprodi->id;
            }
        }
        
        if (ENVIRONMENT === 'development') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
    }

    public function index()
    {
        // Data untuk view kontak
        $view_data = [
            'title' => 'Kontak Form - Kaprodi'
        ];
        
        // Template data untuk kaprodi.php (mengikuti pola existing)
        $data = [
            'title' => 'Kontak Form',
            'content' => $this->load->view('kaprodi/kontak', $view_data, TRUE)
        ];
        
        // Load template kaprodi yang sudah ada
        $this->load->view('template/kaprodi', $data);
    }
    
    /**
     * Get data untuk form kontak kaprodi - SIMPLIFIED DEBUG VERSION
     */
    public function get_kontak_data()
    {
        // Set JSON header first
        header('Content-Type: application/json');
        
        try {
            // Debug session data
            $session_data = [
                'logged_in' => $this->session->userdata('logged_in'),
                'id' => $this->session->userdata('id'),
                'level' => $this->session->userdata('level'),
                'nama' => $this->session->userdata('nama'),
                'prodi_id' => $this->session->userdata('prodi_id')
            ];
            
            // Log untuk debugging
            log_message('debug', 'Kaprodi Kontak Session: ' . json_encode($session_data));
            
            // Validasi session kaprodi
            if (!$this->session->userdata('logged_in')) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Session tidak valid. Silakan login ulang.',
                    'debug' => 'not_logged_in'
                ]);
                return;
            }
            
            if ($this->session->userdata('level') != '4') {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Akses ditolak. Bukan kaprodi.',
                    'debug' => 'level_not_4',
                    'actual_level' => $this->session->userdata('level')
                ]);
                return;
            }
            
            $this->kaprodi_id = $this->session->userdata('id');
            $this->prodi_id = $this->session->userdata('prodi_id');
            
            // Jika prodi_id belum ada, coba ambil dari database
            if (!$this->prodi_id) {
                $prodi = $this->db->get_where('prodi', ['dosen_id' => $this->kaprodi_id])->row();
                if ($prodi) {
                    $this->prodi_id = $prodi->id;
                    $this->session->set_userdata('prodi_id', $prodi->id);
                }
            }
            
            $data = [
                'dosen_list' => [],
                'staf_list' => [],
                'mahasiswa_list' => [],
                'whatsapp_contacts' => [],
                'debug_info' => [
                    'kaprodi_id' => $this->kaprodi_id,
                    'prodi_id' => $this->prodi_id,
                    'session' => $session_data
                ]
            ];
            
            // 1. Get DOSEN (simple query)
            try {
                $this->db->select('d.id, d.nama, d.email, d.nomor_telepon');
                $this->db->from('dosen d');
                $this->db->where('d.level', '2');
                $this->db->order_by('d.nama', 'ASC');
                $this->db->limit(10); // Limit untuk testing
                $query_dosen = $this->db->get();
                
                if ($query_dosen && $query_dosen->num_rows() > 0) {
                    $data['dosen_list'] = $query_dosen->result();
                }
                
                log_message('debug', 'Dosen count: ' . count($data['dosen_list']));
                
            } catch (Exception $e) {
                log_message('error', 'Error getting dosen: ' . $e->getMessage());
                $data['dosen_list'] = [];
            }
            
            // 2. Get STAF/ADMIN (simple query)
            try {
                $this->db->select('d.id, d.nama, d.email, d.nomor_telepon');
                $this->db->from('dosen d');
                $this->db->where_in('d.level', ['1', '5']); // Admin atau Staf
                $this->db->order_by('d.nama', 'ASC');
                $this->db->limit(10); // Limit untuk testing
                $query_staf = $this->db->get();
                
                if ($query_staf && $query_staf->num_rows() > 0) {
                    $data['staf_list'] = $query_staf->result();
                }
                
                log_message('debug', 'Staf count: ' . count($data['staf_list']));
                
            } catch (Exception $e) {
                log_message('error', 'Error getting staf: ' . $e->getMessage());
                $data['staf_list'] = [];
            }
            
            // 3. Get MAHASISWA (simple query, hanya jika ada prodi_id)
            if ($this->prodi_id) {
                try {
                    $this->db->select('m.id, m.nim, m.nama, m.email, m.nomor_telepon');
                    $this->db->from('mahasiswa m');
                    $this->db->where('m.prodi_id', $this->prodi_id);
                    $this->db->order_by('m.nama', 'ASC');
                    $this->db->limit(10); // Limit untuk testing
                    $query_mahasiswa = $this->db->get();
                    
                    if ($query_mahasiswa && $query_mahasiswa->num_rows() > 0) {
                        $data['mahasiswa_list'] = $query_mahasiswa->result();
                    }
                    
                    log_message('debug', 'Mahasiswa count: ' . count($data['mahasiswa_list']));
                    
                } catch (Exception $e) {
                    log_message('error', 'Error getting mahasiswa: ' . $e->getMessage());
                    $data['mahasiswa_list'] = [];
                }
            } else {
                log_message('warning', 'No prodi_id found for kaprodi');
            }
            
            // 4. WhatsApp contacts (simplified)
            $data['whatsapp_contacts'] = [
                'dosen' => [],
                'staf' => [],
                'mahasiswa' => []
            ];
            
            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'message' => 'Data kontak berhasil dimuat'
            ]);
            
        } catch (Exception $e) {
            log_message('error', 'Fatal error in kaprodi kontak get_data: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                'debug' => 'fatal_error'
            ]);
        }
    }
    
    /**
     * Send pesan dari kaprodi
     */
    public function kirim_pesan()
    {
        header('Content-Type: application/json');
        
        try {
            // Validasi input
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
            
            // Get data kaprodi pengirim
            $kaprodi = $this->db->get_where('dosen', ['id' => $this->kaprodi_id])->row();
            if (!$kaprodi) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Data kaprodi tidak ditemukan!'
                ]);
                return;
            }
            
            $success_count = 0;
            $error_count = 0;
            
            // Proses pengiriman berdasarkan kategori
            if ($kategori_penerima === 'semua_mahasiswa' && $this->prodi_id) {
                // Kirim ke semua mahasiswa program studi
                $success_count = $this->_kirim_ke_semua_mahasiswa($kaprodi, $subjek, $pesan, $prioritas);
            } else {
                // Kirim ke penerima spesifik
                if (empty($penerima_id)) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Penerima harus dipilih!'
                    ]);
                    return;
                }
                
                $result = $this->_kirim_ke_penerima_spesifik($kaprodi, $kategori_penerima, $penerima_id, $subjek, $pesan, $prioritas);
                if ($result) {
                    $success_count = 1;
                } else {
                    $error_count = 1;
                }
            }
            
            // Response hasil
            if ($success_count > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => "Pesan berhasil dikirim ke {$success_count} penerima!",
                    'success_count' => $success_count,
                    'error_count' => $error_count
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Gagal mengirim pesan. Silakan coba lagi.'
                ]);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error in kaprodi kontak kirim_pesan: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ]);
        }
    }
    
    /**
     * Get WhatsApp contacts untuk action button
     */
    private function _get_whatsapp_contacts()
    {
        try {
            $contacts = [
                'dosen' => [],
                'staf' => [],
                'mahasiswa' => []
            ];
            
            // Dosen contacts
            $this->db->select('nama, nomor_telepon');
            $this->db->from('dosen');
            $this->db->where('level', '2');
            $this->db->where('nomor_telepon !=', '');
            $this->db->where('nomor_telepon IS NOT NULL');
            $dosen_wa = $this->db->get()->result();
            
            foreach ($dosen_wa as $dosen) {
                if (!empty($dosen->nomor_telepon)) {
                    $contacts['dosen'][] = [
                        'nama' => $dosen->nama,
                        'nomor' => $this->_format_whatsapp_number($dosen->nomor_telepon)
                    ];
                }
            }
            
            // Staf/Admin contacts
            $this->db->select('nama, nomor_telepon');
            $this->db->from('dosen');
            $this->db->where_in('level', ['1', '5']);
            $this->db->where('nomor_telepon !=', '');
            $this->db->where('nomor_telepon IS NOT NULL');
            $staf_wa = $this->db->get()->result();
            
            foreach ($staf_wa as $staf) {
                if (!empty($staf->nomor_telepon)) {
                    $contacts['staf'][] = [
                        'nama' => $staf->nama,
                        'nomor' => $this->_format_whatsapp_number($staf->nomor_telepon)
                    ];
                }
            }
            
            // Mahasiswa contacts (dari prodi kaprodi)
            if ($this->prodi_id) {
                $this->db->select('nama, nomor_telepon');
                $this->db->from('mahasiswa');
                $this->db->where('prodi_id', $this->prodi_id);
                $this->db->where('nomor_telepon !=', '');
                $this->db->where('nomor_telepon IS NOT NULL');
                $this->db->limit(10); // Batasi untuk performa
                $mahasiswa_wa = $this->db->get()->result();
                
                foreach ($mahasiswa_wa as $mhs) {
                    if (!empty($mhs->nomor_telepon)) {
                        $contacts['mahasiswa'][] = [
                            'nama' => $mhs->nama,
                            'nomor' => $this->_format_whatsapp_number($mhs->nomor_telepon)
                        ];
                    }
                }
            }
            
            return $contacts;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting WhatsApp contacts: ' . $e->getMessage());
            return ['dosen' => [], 'staf' => [], 'mahasiswa' => []];
        }
    }
    
    /**
     * Format nomor WhatsApp ke format internasional
     */
    private function _format_whatsapp_number($nomor)
    {
        // Hapus semua karakter non-digit
        $nomor = preg_replace('/[^0-9]/', '', $nomor);
        
        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($nomor, 0, 1) === '0') {
            $nomor = '62' . substr($nomor, 1);
        }
        
        // Jika tidak dimulai dengan 62, tambahkan 62
        if (substr($nomor, 0, 2) !== '62') {
            $nomor = '62' . $nomor;
        }
        
        return $nomor;
    }
    
    /**
     * Kirim ke semua mahasiswa program studi
     */
    private function _kirim_ke_semua_mahasiswa($kaprodi, $subjek, $pesan, $prioritas)
    {
        try {
            $success_count = 0;
            
            // Get semua mahasiswa aktif di prodi
            $this->db->select('id, nama, email');
            $this->db->from('mahasiswa');
            $this->db->where('prodi_id', $this->prodi_id);
            $this->db->where('status', '1');
            $mahasiswa_list = $this->db->get()->result();
            
            foreach ($mahasiswa_list as $mahasiswa) {
                if ($this->_send_email_to_recipient($kaprodi, $mahasiswa, $subjek, $pesan, $prioritas, 'mahasiswa')) {
                    $success_count++;
                }
                
                // Tambah small delay untuk menghindari spam
                usleep(100000); // 0.1 detik
            }
            
            return $success_count;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending to all mahasiswa: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Kirim ke penerima spesifik
     */
    private function _kirim_ke_penerima_spesifik($kaprodi, $kategori, $penerima_id, $subjek, $pesan, $prioritas)
    {
        try {
            $penerima = null;
            $recipient_type = '';
            
            if ($kategori === 'dosen') {
                $penerima = $this->db->get_where('dosen', ['id' => $penerima_id, 'level' => '2'])->row();
                $recipient_type = 'dosen';
            } elseif ($kategori === 'staf') {
                $penerima = $this->db->get_where('dosen', ['id' => $penerima_id])->row();
                if ($penerima && !in_array($penerima->level, ['1', '5'])) {
                    $penerima = null; // Hanya admin atau staf
                }
                $recipient_type = 'staf';
            } elseif ($kategori === 'mahasiswa') {
                $penerima = $this->db->get_where('mahasiswa', ['id' => $penerima_id])->row();
                $recipient_type = 'mahasiswa';
            }
            
            if (!$penerima) {
                return false;
            }
            
            return $this->_send_email_to_recipient($kaprodi, $penerima, $subjek, $pesan, $prioritas, $recipient_type);
            
        } catch (Exception $e) {
            log_message('error', 'Error sending to specific recipient: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email ke penerima
     */
    private function _send_email_to_recipient($kaprodi, $penerima, $subjek, $pesan, $prioritas, $recipient_type)
    {
        try {
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html',
                'smtp_crypto' => 'tls',
                'smtp_timeout' => 30,
                'wordwrap' => TRUE
            ];
            
            $this->email->initialize($config);
            
            // Email headers
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK Santo Yakobus');
            $this->email->to($penerima->email);
            $this->email->subject('[SIM-TA] ' . $subjek);
            
            // Email body
            $email_body = $this->_build_email_template($kaprodi, $penerima, $subjek, $pesan, $prioritas, $recipient_type);
            $this->email->message($email_body);
            
            $result = $this->email->send();
            
            if (!$result) {
                log_message('error', 'Email sending failed: ' . $this->email->print_debugger());
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error sending email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build email template
     */
    private function _build_email_template($kaprodi, $penerima, $subjek, $pesan, $prioritas, $recipient_type)
    {
        $prioritas_text = [
            'rendah' => 'Rendah',
            'normal' => 'Normal', 
            'tinggi' => 'Tinggi',
            'urgent' => 'URGENT'
        ];
        
        $prioritas_color = [
            'rendah' => '#28a745',
            'normal' => '#007bff',
            'tinggi' => '#fd7e14', 
            'urgent' => '#dc3545'
        ];
        
        $recipient_label = [
            'dosen' => 'Dosen',
            'staf' => 'Staf/Admin',
            'mahasiswa' => 'Mahasiswa'
        ];
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>🎓 SIM Tugas Akhir</h2>
                <p style='margin: 5px 0 0 0;'>STK Santo Yakobus Merauke</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px;'>
                <div style='background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <div style='border-left: 4px solid {$prioritas_color[$prioritas]}; padding-left: 15px; margin-bottom: 20px;'>
                        <h3 style='color: #333; margin: 0 0 10px 0;'>{$subjek}</h3>
                        <p style='color: #666; margin: 0; font-size: 14px;'>
                            <strong>Prioritas:</strong> 
                            <span style='color: {$prioritas_color[$prioritas]}; font-weight: bold;'>
                                {$prioritas_text[$prioritas]}
                            </span>
                        </p>
                    </div>
                    
                    <div style='margin-bottom: 20px;'>
                        <p style='color: #333; line-height: 1.6;'>{$pesan}</p>
                    </div>
                    
                    <div style='border-top: 1px solid #eee; padding-top: 15px; font-size: 14px; color: #666;'>
                        <p style='margin: 5px 0;'><strong>Pengirim:</strong> {$kaprodi->nama} (Kaprodi)</p>
                        <p style='margin: 5px 0;'><strong>Email:</strong> {$kaprodi->email}</p>
                        <p style='margin: 5px 0;'><strong>Kepada:</strong> {$penerima->nama} ({$recipient_label[$recipient_type]})</p>
                        <p style='margin: 5px 0;'><strong>Waktu:</strong> " . date('d F Y, H:i:s') . " WIT</p>
                    </div>
                </div>
                
                <div style='text-align: center; margin-top: 20px; color: #666; font-size: 12px;'>
                    <p>Email ini dikirim otomatis dari Sistem Informasi Manajemen Tugas Akhir<br>
                    STK Santo Yakobus Merauke</p>
                </div>
            </div>
        </div>";
    }
}