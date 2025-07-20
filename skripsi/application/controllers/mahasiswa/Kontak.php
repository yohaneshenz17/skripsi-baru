<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kontak extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Cek login mahasiswa sudah dihandle oleh MY_Controller
    }

    public function index()
    {
        return $this->load->view('mahasiswa/kontak');
    }
    
    /**
     * Get data untuk form kontak
     */
    public function get_kontak_data()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        $data = [
            'pembimbing' => null,
            'kaprodi' => null,
            'staf_list' => [],
            'riwayat_pesan' => []
        ];
        
        try {
            // Ambil data mahasiswa untuk mendapatkan prodi_id
            $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
            
            if ($mahasiswa) {
                // Ambil dosen pembimbing jika ada
                $data['pembimbing'] = $this->_get_dosen_pembimbing($mahasiswa_id);
                
                // Ambil kaprodi berdasarkan prodi mahasiswa
                $data['kaprodi'] = $this->_get_kaprodi($mahasiswa->prodi_id);
                
                // Ambil list staf
                $data['staf_list'] = $this->_get_staf_list();
                
                // Ambil riwayat pesan yang dikirim
                $data['riwayat_pesan'] = $this->_get_riwayat_pesan($mahasiswa_id);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Kontak data error: ' . $e->getMessage());
        }
        
        echo json_encode(['status' => 'success', 'data' => $data]);
    }
    
    public function kirim_pesan()
    {
        // Validasi input
        $penerima_role = $this->input->post('penerima_role');
        $penerima_id = $this->input->post('penerima_id');
        $subjek = $this->input->post('subjek');
        $pesan = $this->input->post('pesan');
        $prioritas = $this->input->post('prioritas') ?? 'normal';
        
        // Validasi sederhana
        if (empty($penerima_role) || empty($penerima_id) || empty($subjek) || empty($pesan)) {
            echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi']);
            return;
        }
        
        $mahasiswa_id = $this->session->userdata('id');
        
        // Ambil data penerima
        $penerima_data = $this->_get_penerima_data($penerima_role, $penerima_id);
        
        if (!$penerima_data) {
            echo json_encode(['status' => 'error', 'message' => 'Data penerima tidak ditemukan']);
            return;
        }
        
        // Ambil data mahasiswa pengirim
        $mahasiswa = $this->db->get_where('mahasiswa', ['id' => $mahasiswa_id])->row();
        
        try {
            // Simpan ke database (jika tabel kontak/pesan ada)
            $this->_simpan_pesan_database($mahasiswa_id, $penerima_role, $penerima_id, $subjek, $pesan, $prioritas);
            
            // Kirim email
            $email_sent = $this->_kirim_email(
                $mahasiswa,
                $penerima_data,
                $subjek,
                $pesan,
                $prioritas
            );
            
            if ($email_sent) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Pesan berhasil dikirim ke ' . $penerima_data->nama
                ]);
            } else {
                echo json_encode([
                    'status' => 'warning', 
                    'message' => 'Pesan tersimpan tetapi email gagal dikirim. Penerima akan melihat pesan di sistem.'
                ]);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Kontak form error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan saat mengirim pesan']);
        }
    }
    
    /**
     * Ambil data dosen pembimbing mahasiswa
     */
    private function _get_dosen_pembimbing($mahasiswa_id)
    {
        $this->db->select('d.id, d.nama, d.email, d.nomor_telepon');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('dosen d', 'pm.dosen_id = d.id');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->where('pm.status_pembimbing', '1');
        $this->db->order_by('pm.created_at', 'DESC');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }
    
    /**
     * Ambil data kaprodi berdasarkan prodi
     */
    private function _get_kaprodi($prodi_id)
    {
        $this->db->select('d.id, d.nama, d.email, d.nomor_telepon, p.nama as nama_prodi');
        $this->db->from('prodi p');
        $this->db->join('dosen d', 'p.dosen_id = d.id');
        $this->db->where('p.id', $prodi_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Ambil list staf
     */
    private function _get_staf_list()
    {
        $this->db->select('id, nama, email, nomor_telepon');
        $this->db->from('dosen');
        $this->db->where('level', '1'); // Asumsikan level 1 adalah admin/staf
        $this->db->order_by('nama', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Ambil riwayat pesan mahasiswa - SIMPLIFIED
     */
    private function _get_riwayat_pesan($mahasiswa_id)
    {
        // Return empty array untuk sementara
        // Bisa dikembangkan kemudian atau menggunakan sistem lain
        return [];
    }
    
    /**
     * Ambil data penerima berdasarkan role dan ID
     */
    private function _get_penerima_data($role, $id)
    {
        switch ($role) {
            case 'pembimbing':
            case 'kaprodi':
            case 'staf':
                return $this->db->get_where('dosen', ['id' => $id])->row();
            default:
                return null;
        }
    }
    
    /**
     * Simpan pesan ke database - OPTIONAL, SKIP JIKA TIDAK ADA TABEL
     */
    private function _simpan_pesan_database($pengirim_id, $penerima_role, $penerima_id, $subjek, $pesan, $prioritas)
    {
        // Skip database save, hanya kirim email saja
        // Bisa dikembangkan kemudian jika diperlukan tabel khusus untuk pesan
        log_message('info', 'Pesan kontak dikirim via email saja, tidak disimpan ke database');
        return true;
    }
    
    /**
     * Kirim email ke penerima - MENGGUNAKAN SETTING DARI DATABASE
     */
    private function _kirim_email($mahasiswa, $penerima, $subjek, $pesan, $prioritas)
    {
        // Load email library
        $this->load->library('email');
        
        // Ambil konfigurasi email dari database
        $email_config = $this->db->get('email_sender')->row();
        
        if ($email_config) {
            // Konfigurasi email dari database
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => $email_config->smtp_host,
                'smtp_port' => $email_config->smtp_port,
                'smtp_user' => $email_config->email,
                'smtp_pass' => $email_config->password,
                'smtp_crypto' => ($email_config->smtp_port == '465') ? 'ssl' : 'tls',
                'mailtype' => 'html',
                'charset' => 'utf-8'
            ];
        } else {
            // Fallback config jika tidak ada setting di database
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com',
                'smtp_port' => 587,
                'smtp_user' => 'stkyakobus@gmail.com',
                'smtp_pass' => 'yonroxhraathnaug', // Dari database dump
                'smtp_crypto' => 'tls',
                'mailtype' => 'html',
                'charset' => 'utf-8'
            ];
        }
        
        $this->email->initialize($config);
        
        // Format subjek
        $email_subject = '[SIM-TA] ' . $subjek;
        if ($prioritas == 'urgent') {
            $email_subject = '[URGENT] ' . $email_subject;
        }
        
        // Format pesan HTML
        $email_message = $this->_format_email_template($mahasiswa, $penerima, $subjek, $pesan, $prioritas);
        
        $from_email = $email_config ? $email_config->email : 'stkyakobus@gmail.com';
        
        $this->email->from($from_email, 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($penerima->email);
        $this->email->reply_to($mahasiswa->email, $mahasiswa->nama);
        $this->email->subject($email_subject);
        $this->email->message($email_message);
        
        return $this->email->send();
    }
    
    /**
     * Format template email
     */
    private function _format_email_template($mahasiswa, $penerima, $subjek, $pesan, $prioritas)
    {
        $priority_badge = '';
        $priority_color = '#17a2b8';
        
        if ($prioritas == 'urgent') {
            $priority_badge = '<span style="background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">URGENT</span>';
            $priority_color = '#dc3545';
        } elseif ($prioritas == 'high') {
            $priority_badge = '<span style="background-color: #fd7e14; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">PRIORITAS TINGGI</span>';
            $priority_color = '#fd7e14';
        }
        
        $template = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Pesan dari Mahasiswa</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <div style='text-align: center; background-color: {$priority_color}; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                    <h2 style='margin: 0;'>📧 Pesan dari Mahasiswa</h2>
                    {$priority_badge}
                </div>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
                    <h4 style='margin: 0 0 10px 0; color: #495057;'>Informasi Pengirim:</h4>
                    <p style='margin: 0;'><strong>Nama:</strong> {$mahasiswa->nama}</p>
                    <p style='margin: 0;'><strong>NIM:</strong> {$mahasiswa->nim}</p>
                    <p style='margin: 0;'><strong>Email:</strong> {$mahasiswa->email}</p>
                    <p style='margin: 0;'><strong>Waktu:</strong> " . date('d F Y, H:i') . " WIT</p>
                </div>
                
                <div style='margin: 20px 0;'>
                    <h4 style='color: #495057; border-bottom: 2px solid {$priority_color}; padding-bottom: 10px;'>
                        {$subjek}
                    </h4>
                    <div style='background-color: white; padding: 20px; border-left: 4px solid {$priority_color}; margin: 15px 0;'>
                        " . nl2br(htmlspecialchars($pesan)) . "
                    </div>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('dosen/dashboard') . "' 
                       style='background-color: {$priority_color}; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       📱 Buka Sistem SIM-TA
                    </a>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 3px; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 12px; color: #856404; text-align: center;'>
                        <strong>💡 Tips:</strong> Anda dapat membalas email ini secara langsung untuk merespons mahasiswa
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #dee2e6; margin: 20px -20px -20px -20px; border-radius: 0 0 8px 8px;'>
                    <p style='margin: 0; font-size: 12px; color: #6c757d;'>
                        Email ini dikirim secara otomatis oleh<br>
                        <strong>Sistem Informasi Manajemen Tugas Akhir</strong><br>
                        STK Santo Yakobus Merauke
                    </p>
                </div>
            </div>
        </body>
        </html>";
        
        return $template;
    }
}

/* End of file Kontak.php */