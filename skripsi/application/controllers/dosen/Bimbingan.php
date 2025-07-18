<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil mahasiswa bimbingan yang sudah disetujui (status_pembimbing = 1)
        $this->db->select('
            pm.id as proposal_id,
            pm.judul,
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            pm.created_at as tanggal_proposal,
            m.id as mahasiswa_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            p.nama as nama_prodi,
            (SELECT COUNT(*) FROM jurnal_bimbingan jb WHERE jb.proposal_id = pm.id) as total_bimbingan,
            (SELECT COUNT(*) FROM jurnal_bimbingan jb WHERE jb.proposal_id = pm.id AND jb.status_validasi = 1) as bimbingan_tervalidasi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1'); // Sudah disetujui sebagai pembimbing
        $this->db->order_by('pm.tanggal_respon_pembimbing', 'DESC');
        
        $data['mahasiswa_bimbingan'] = $this->db->get()->result();
        
        // Ambil jurnal bimbingan yang perlu divalidasi
        $this->db->select('
            jb.*,
            pm.judul,
            m.nim,
            m.nama as nama_mahasiswa
        ');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('jb.status_validasi', '0'); // Belum divalidasi
        $this->db->order_by('jb.tanggal_bimbingan', 'DESC');
        $this->db->limit(10);
        
        $data['jurnal_pending'] = $this->db->get()->result();
        
        $this->load->view('dosen/bimbingan', $data);
    }

    public function detail_mahasiswa($mahasiswa_id) {
        $data['title'] = 'Detail Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil data mahasiswa dan proposal
        $this->db->select('
            pm.id as proposal_id,
            pm.judul,
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            pm.uraian_masalah,
            pm.created_at as tanggal_proposal,
            m.id as mahasiswa_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            m.alamat,
            p.nama as nama_prodi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('m.id', $mahasiswa_id);
        $this->db->where('pm.status_pembimbing', '1');
        
        $data['mahasiswa'] = $this->db->get()->row();
        
        if (!$data['mahasiswa']) {
            $this->session->set_flashdata('error', 'Mahasiswa tidak ditemukan atau bukan bimbingan Anda.');
            redirect('dosen/bimbingan');
        }
        
        // Ambil semua jurnal bimbingan mahasiswa ini
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $data['mahasiswa']->proposal_id);
        $this->db->order_by('pertemuan_ke', 'ASC');
        
        $data['jurnal_bimbingan'] = $this->db->get()->result();
        
        // Hitung statistik
        $data['total_bimbingan'] = count($data['jurnal_bimbingan']);
        $data['bimbingan_tervalidasi'] = count(array_filter($data['jurnal_bimbingan'], function($j) { return $j->status_validasi == 1; }));
        $data['bimbingan_pending'] = count(array_filter($data['jurnal_bimbingan'], function($j) { return $j->status_validasi == 0; }));
        
        $this->load->view('dosen/bimbingan_detail', $data);
    }

    public function validasi_jurnal() {
        $jurnal_id = $this->input->post('jurnal_id');
        $status_validasi = $this->input->post('status_validasi'); // 1 = valid, 2 = revisi
        $catatan_dosen = $this->input->post('catatan_dosen');
        $dosen_id = $this->session->userdata('id');
        
        // Validasi input
        if (!$jurnal_id || !$status_validasi) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/bimbingan');
        }
        
        // Validasi jurnal exists dan dosen berhak
        $jurnal = $this->db->select('jb.*, pm.dosen_id, m.nama as nama_mahasiswa, m.email as email_mahasiswa')
                          ->from('jurnal_bimbingan jb')
                          ->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id')
                          ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                          ->where('jb.id', $jurnal_id)
                          ->where('pm.dosen_id', $dosen_id)
                          ->get()->row();
        
        if (!$jurnal) {
            $this->session->set_flashdata('error', 'Jurnal tidak ditemukan atau Anda tidak berhak mengakses!');
            redirect('dosen/bimbingan');
        }
        
        // Update validasi jurnal
        $update_data = [
            'status_validasi' => $status_validasi,
            'catatan_dosen' => $catatan_dosen,
            'tanggal_validasi' => date('Y-m-d H:i:s'),
            'validasi_oleh' => $dosen_id
        ];
        
        $this->db->where('id', $jurnal_id);
        $update_result = $this->db->update('jurnal_bimbingan', $update_data);
        
        if ($update_result) {
            // Kirim notifikasi ke mahasiswa
            $this->_kirim_notifikasi_validasi($jurnal, $status_validasi, $catatan_dosen);
            
            $message = ($status_validasi == '1') ? 
                'Jurnal bimbingan berhasil divalidasi!' : 
                'Jurnal bimbingan dikembalikan untuk revisi.';
                
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan validasi!');
        }
        
        // Redirect back to detail mahasiswa
        redirect('dosen/bimbingan/detail_mahasiswa/' . $jurnal->mahasiswa_id);
    }

    public function validasi_batch() {
        $jurnal_ids = $this->input->post('jurnal_ids');
        $action = $this->input->post('action');
        $catatan_batch = $this->input->post('catatan_batch');
        $dosen_id = $this->session->userdata('id');
        
        if (empty($jurnal_ids) || !$action) {
            $this->session->set_flashdata('error', 'Pilih jurnal dan aksi yang akan dilakukan!');
            redirect('dosen/bimbingan');
        }
        
        $status_validasi = ($action == 'validate') ? '1' : '2';
        $success_count = 0;
        
        foreach ($jurnal_ids as $jurnal_id) {
            // Validasi jurnal exists dan dosen berhak
            $jurnal = $this->db->select('jb.*, pm.dosen_id')
                              ->from('jurnal_bimbingan jb')
                              ->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id')
                              ->where('jb.id', $jurnal_id)
                              ->where('pm.dosen_id', $dosen_id)
                              ->get()->row();
            
            if ($jurnal) {
                $update_data = [
                    'status_validasi' => $status_validasi,
                    'catatan_dosen' => $catatan_batch,
                    'tanggal_validasi' => date('Y-m-d H:i:s'),
                    'validasi_oleh' => $dosen_id
                ];
                
                $this->db->where('id', $jurnal_id);
                $this->db->update('jurnal_bimbingan', $update_data);
                $success_count++;
            }
        }
        
        $message = ($action == 'validate') ? 
            "Berhasil memvalidasi {$success_count} jurnal bimbingan!" : 
            "Berhasil mengembalikan {$success_count} jurnal untuk revisi!";
            
        $this->session->set_flashdata('success', $message);
        redirect('dosen/bimbingan');
    }

    public function tambah_jurnal() {
        $proposal_id = $this->input->post('proposal_id');
        $pertemuan_ke = $this->input->post('pertemuan_ke');
        $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
        $materi_bimbingan = $this->input->post('materi_bimbingan');
        $catatan_dosen = $this->input->post('catatan_dosen');
        $tindak_lanjut = $this->input->post('tindak_lanjut');
        $dosen_id = $this->session->userdata('id');
        
        // Validasi proposal exists dan dosen berhak
        $proposal = $this->db->get_where('proposal_mahasiswa', [
            'id' => $proposal_id,
            'dosen_id' => $dosen_id,
            'status_pembimbing' => '1'
        ])->row();
        
        if (!$proposal) {
            $this->session->set_flashdata('error', 'Proposal tidak ditemukan atau Anda tidak berhak!');
            redirect('dosen/bimbingan');
        }
        
        // Cek apakah pertemuan_ke sudah ada
        $existing = $this->db->get_where('jurnal_bimbingan', [
            'proposal_id' => $proposal_id,
            'pertemuan_ke' => $pertemuan_ke
        ])->row();
        
        if ($existing) {
            $this->session->set_flashdata('error', 'Pertemuan ke-' . $pertemuan_ke . ' sudah ada!');
            redirect('dosen/bimbingan/detail_mahasiswa/' . $proposal->mahasiswa_id);
        }
        
        // Insert jurnal bimbingan baru
        $data_jurnal = [
            'proposal_id' => $proposal_id,
            'pertemuan_ke' => $pertemuan_ke,
            'tanggal_bimbingan' => $tanggal_bimbingan,
            'materi_bimbingan' => $materi_bimbingan,
            'catatan_dosen' => $catatan_dosen,
            'tindak_lanjut' => $tindak_lanjut,
            'status_validasi' => '1', // Langsung valid karena dibuat oleh dosen
            'tanggal_validasi' => date('Y-m-d H:i:s'),
            'validasi_oleh' => $dosen_id,
            'created_by' => 'dosen',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('jurnal_bimbingan', $data_jurnal);
        
        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Jurnal bimbingan berhasil ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan jurnal bimbingan!');
        }
        
        redirect('dosen/bimbingan/detail_mahasiswa/' . $proposal->mahasiswa_id);
    }

    private function _kirim_notifikasi_validasi($jurnal, $status_validasi, $catatan) {
        $config = [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'stkyakobus@gmail.com',
            'smtp_pass' => 'yonroxhraathnaug',
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'mailtype' => 'html',
            'smtp_crypto' => 'tls'
        ];
        
        $this->email->initialize($config);
        
        $dosen_nama = $this->session->userdata('nama');
        
        if ($status_validasi == '1') {
            $subject = 'Jurnal Bimbingan Divalidasi - Pertemuan ke-' . $jurnal->pertemuan_ke;
            $status_text = 'divalidasi';
            $bg_color = '#28a745';
            $icon = '✅';
        } else {
            $subject = 'Jurnal Bimbingan Perlu Revisi - Pertemuan ke-' . $jurnal->pertemuan_ke;
            $status_text = 'dikembalikan untuk revisi';
            $bg_color = '#ffc107';
            $icon = '📝';
        }
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Validasi Jurnal Bimbingan</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <div style='text-align: center; background-color: {$bg_color}; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                    <h2 style='margin: 0;'>{$icon} Jurnal Bimbingan {$status_text}</h2>
                </div>
                
                <p style='margin: 0 0 20px 0; font-size: 16px;'>
                    Kepada <strong>{$jurnal->nama_mahasiswa}</strong>,
                </p>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Jurnal bimbingan Anda untuk <strong>Pertemuan ke-{$jurnal->pertemuan_ke}</strong> telah <strong>{$status_text}</strong> oleh dosen pembimbing.
                </p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #495057; margin: 0 0 15px 0; font-size: 18px;'>Detail Jurnal Bimbingan:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; width: 30%;'>Pertemuan ke:</td>
                            <td style='padding: 8px 0;'>{$jurnal->pertemuan_ke}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Tanggal:</td>
                            <td style='padding: 8px 0;'>" . date('d F Y', strtotime($jurnal->tanggal_bimbingan)) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Materi:</td>
                            <td style='padding: 8px 0;'>{$jurnal->materi_bimbingan}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Dosen Pembimbing:</td>
                            <td style='padding: 8px 0;'>{$dosen_nama}</td>
                        </tr>
                    </table>
                </div>";
                
        if ($catatan) {
            $message .= "
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>💬 Catatan Dosen:</h4>
                    <p style='margin: 0; color: #856404;'>{$catatan}</p>
                </div>";
        }
        
        $message .= "
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('mahasiswa/bimbingan') . "' 
                       style='background-color: {$bg_color}; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       📚 Lihat Jurnal Bimbingan
                    </a>
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
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($jurnal->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $this->email->send();
    }
}