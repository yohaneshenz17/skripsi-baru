<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bimbingan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');

        // Cek apakah user sudah login sebagai mahasiswa
        if ($this->session->userdata('level') != '3') {
            redirect('auth/login');
        }
    }

    public function index()
    {
        $data['title'] = 'Bimbingan Skripsi - Phase 2';
        $mahasiswa_id = $this->session->userdata('id');

        // PERBAIKAN LOGIKA: Cek status proposal berdasarkan workflow yang benar
        $this->db->select('
            pm.id as proposal_id, 
            pm.judul, 
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            pm.workflow_status,
            pm.status_kaprodi,
            pm.status_pembimbing,
            pm.dosen_id,
            pm.created_at,
            pm.tanggal_review_kaprodi,
            pm.komentar_kaprodi,
            pm.tanggal_penetapan,
            pm.tanggal_respon_pembimbing,
            pm.komentar_pembimbing,
            d.id as dosen_id, 
            d.nama as nama_dosen,
            d.email as email_dosen,
            d.nomor_telepon as telepon_dosen
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left'); // LEFT JOIN karena dosen_id mungkin NULL
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('pm.id', 'DESC'); // Ambil proposal terbaru
        $proposal_data = $this->db->get()->row();

        // Reset variabel status
        $data['proposal'] = null;
        $data['pending_proposal'] = null;
        $data['waiting_kaprodi'] = null;

        if ($proposal_data) {
            // WORKFLOW LOGIC YANG BENAR:
            
            // 1. PROPOSAL BELUM DIREVIEW KAPRODI
            if ($proposal_data->status_kaprodi == '0') {
                // Mahasiswa sudah ajukan proposal, tapi kaprodi belum review
                $data['waiting_kaprodi'] = $proposal_data;
                
            // 2. PROPOSAL DITOLAK KAPRODI  
            } elseif ($proposal_data->status_kaprodi == '2') {
                // Proposal ditolak kaprodi, mahasiswa perlu ajukan ulang
                $data['rejected_kaprodi'] = $proposal_data;
                
            // 3. PROPOSAL DISETUJUI KAPRODI, MENUNGGU DOSEN PEMBIMBING
            } elseif ($proposal_data->status_kaprodi == '1' && $proposal_data->status_pembimbing == '0') {
                // Kaprodi sudah setujui dan tetapkan dosen, tapi dosen belum setuju
                $data['pending_proposal'] = $proposal_data;
                
            // 4. DOSEN PEMBIMBING MENOLAK
            } elseif ($proposal_data->status_kaprodi == '1' && $proposal_data->status_pembimbing == '2') {
                // Dosen menolak, kaprodi perlu tetapkan dosen lain
                $data['rejected_dosen'] = $proposal_data;
                
            // 5. BIMBINGAN AKTIF
            } elseif ($proposal_data->status_kaprodi == '1' && $proposal_data->status_pembimbing == '1') {
                // Semua approve, bimbingan dapat dimulai
                $data['proposal'] = $proposal_data;
            }
        }

        // Ambil jurnal bimbingan hanya jika proposal sudah aktif
        if (isset($data['proposal'])) {
            $this->db->select('*');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $data['proposal']->proposal_id);
            $this->db->order_by('pertemuan_ke', 'ASC');
            $data['jurnal_bimbingan'] = $this->db->get()->result();

            // Hitung statistik
            $data['total_bimbingan'] = count($data['jurnal_bimbingan']);
            $data['bimbingan_tervalidasi'] = count(array_filter($data['jurnal_bimbingan'], function($j) { return $j->status_validasi == '1'; }));
            $data['bimbingan_pending'] = count(array_filter($data['jurnal_bimbingan'], function($j) { return $j->status_validasi == '0'; }));
            $data['bimbingan_revisi'] = count(array_filter($data['jurnal_bimbingan'], function($j) { return $j->status_validasi == '2'; }));
            
            // Cek kelayakan untuk seminar proposal (minimal 8 pertemuan tervalidasi)
            $data['siap_seminar'] = $data['bimbingan_tervalidasi'] >= 8;
        } else {
            $data['jurnal_bimbingan'] = [];
            $data['total_bimbingan'] = 0;
            $data['bimbingan_tervalidasi'] = 0;
            $data['bimbingan_pending'] = 0;
            $data['bimbingan_revisi'] = 0;
            $data['siap_seminar'] = false;
        }

        $this->load->view('mahasiswa/bimbingan', $data);
    }

    public function tambah_jurnal()
    {
        if ($this->input->post()) {
            $mahasiswa_id = $this->session->userdata('id');
            
            // Cek apakah mahasiswa sudah memiliki proposal yang aktif untuk bimbingan
            $proposal = $this->db->get_where('proposal_mahasiswa', [
                'mahasiswa_id' => $mahasiswa_id,
                'status_kaprodi' => '1',      // Disetujui kaprodi
                'status_pembimbing' => '1',   // Disetujui dosen pembimbing
                'dosen_id !=' => NULL        // Ada dosen pembimbing
            ])->row();

            if (!$proposal) {
                $this->session->set_flashdata('error', 'Anda belum memiliki proposal yang disetujui untuk memulai bimbingan.');
                redirect('mahasiswa/bimbingan');
                return;
            }

            $pertemuan_ke = $this->input->post('pertemuan_ke');
            $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
            $materi_bimbingan = $this->input->post('materi_bimbingan');
            $tindak_lanjut = $this->input->post('tindak_lanjut');

            // Validasi input
            if (!$pertemuan_ke || !$tanggal_bimbingan || !$materi_bimbingan) {
                $this->session->set_flashdata('error', 'Semua field wajib diisi!');
                redirect('mahasiswa/bimbingan');
                return;
            }

            // Cek apakah pertemuan_ke sudah ada
            $existing = $this->db->get_where('jurnal_bimbingan', [
                'proposal_id' => $proposal->id,
                'pertemuan_ke' => $pertemuan_ke
            ])->row();

            if ($existing) {
                $this->session->set_flashdata('error', 'Pertemuan ke-' . $pertemuan_ke . ' sudah ada! Silakan edit yang sudah ada atau gunakan nomor pertemuan yang berbeda.');
                redirect('mahasiswa/bimbingan');
                return;
            }

            // Insert jurnal bimbingan baru
            $data_jurnal = [
                'proposal_id' => $proposal->id,
                'pertemuan_ke' => $pertemuan_ke,
                'tanggal_bimbingan' => $tanggal_bimbingan,
                'materi_bimbingan' => $materi_bimbingan,
                'tindak_lanjut' => $tindak_lanjut,
                'status_validasi' => '0', // Pending validasi
                'created_by' => 'mahasiswa',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('jurnal_bimbingan', $data_jurnal);

            if ($this->db->affected_rows() > 0) {
                // Kirim notifikasi ke dosen pembimbing
                $this->_kirim_notifikasi_jurnal_baru($proposal, $pertemuan_ke, $tanggal_bimbingan, $materi_bimbingan);
                
                $this->session->set_flashdata('success', 'Jurnal bimbingan pertemuan ke-' . $pertemuan_ke . ' berhasil ditambahkan! Menunggu validasi dari dosen pembimbing.');
            } else {
                $this->session->set_flashdata('error', 'Gagal menambahkan jurnal bimbingan!');
            }
        }

        redirect('mahasiswa/bimbingan');
    }

    public function edit_jurnal($jurnal_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi jurnal milik mahasiswa dan belum divalidasi
        $jurnal = $this->db->select('jb.*, pm.mahasiswa_id')
                          ->from('jurnal_bimbingan jb')
                          ->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id')
                          ->where('jb.id', $jurnal_id)
                          ->where('pm.mahasiswa_id', $mahasiswa_id)
                          ->where('jb.status_validasi', '0') // Hanya bisa edit yang belum divalidasi
                          ->get()->row();

        if (!$jurnal) {
            $this->session->set_flashdata('error', 'Jurnal tidak ditemukan atau tidak dapat diedit (sudah divalidasi).');
            redirect('mahasiswa/bimbingan');
            return;
        }

        if ($this->input->post()) {
            $tanggal_bimbingan = $this->input->post('tanggal_bimbingan');
            $materi_bimbingan = $this->input->post('materi_bimbingan');
            $tindak_lanjut = $this->input->post('tindak_lanjut');

            // Update jurnal
            $update_data = [
                'tanggal_bimbingan' => $tanggal_bimbingan,
                'materi_bimbingan' => $materi_bimbingan,
                'tindak_lanjut' => $tindak_lanjut,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('id', $jurnal_id);
            $this->db->update('jurnal_bimbingan', $update_data);

            if ($this->db->affected_rows() > 0) {
                $this->session->set_flashdata('success', 'Jurnal bimbingan berhasil diupdate!');
            } else {
                $this->session->set_flashdata('error', 'Tidak ada perubahan data atau gagal update!');
            }

            redirect('mahasiswa/bimbingan');
        }

        // Load form edit
        $data['title'] = 'Edit Jurnal Bimbingan';
        $data['jurnal'] = $jurnal;
        $this->load->view('mahasiswa/bimbingan_edit', $data);
    }

    public function hapus_jurnal($jurnal_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi jurnal milik mahasiswa dan belum divalidasi
        $jurnal = $this->db->select('jb.*, pm.mahasiswa_id')
                          ->from('jurnal_bimbingan jb')
                          ->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id')
                          ->where('jb.id', $jurnal_id)
                          ->where('pm.mahasiswa_id', $mahasiswa_id)
                          ->where('jb.status_validasi', '0') // Hanya bisa hapus yang belum divalidasi
                          ->get()->row();

        if (!$jurnal) {
            $this->session->set_flashdata('error', 'Jurnal tidak ditemukan atau tidak dapat dihapus (sudah divalidasi).');
            redirect('mahasiswa/bimbingan');
            return;
        }

        // Hapus jurnal
        $this->db->where('id', $jurnal_id);
        $this->db->delete('jurnal_bimbingan');

        if ($this->db->affected_rows() > 0) {
            $this->session->set_flashdata('success', 'Jurnal bimbingan berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus jurnal bimbingan!');
        }

        redirect('mahasiswa/bimbingan');
    }

    public function export_jurnal()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Ambil data proposal dan jurnal
        $proposal = $this->db->select('pm.*, d.nama as nama_dosen')
                            ->from('proposal_mahasiswa pm')
                            ->join('dosen d', 'pm.dosen_id = d.id')
                            ->where('pm.mahasiswa_id', $mahasiswa_id)
                            ->where('pm.status_kaprodi', '1')
                            ->where('pm.status_pembimbing', '1')
                            ->get()->row();

        if (!$proposal) {
            $this->session->set_flashdata('error', 'Data proposal tidak ditemukan.');
            redirect('mahasiswa/bimbingan');
            return;
        }

        $jurnal_list = $this->db->get_where('jurnal_bimbingan', ['proposal_id' => $proposal->id])->result();

        // Generate PDF atau Excel
        // Placeholder untuk export functionality
        $this->session->set_flashdata('info', 'Fitur export jurnal akan segera tersedia.');
        redirect('mahasiswa/bimbingan');
    }

    private function _kirim_notifikasi_jurnal_baru($proposal, $pertemuan_ke, $tanggal_bimbingan, $materi_bimbingan)
    {
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

        $mahasiswa_nama = $this->session->userdata('nama');
        $subject = 'Jurnal Bimbingan Baru - Pertemuan ke-' . $pertemuan_ke . ' - ' . $mahasiswa_nama;

        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Jurnal Bimbingan Baru</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <div style='text-align: center; background-color: #007bff; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                    <h2 style='margin: 0;'>📚 Jurnal Bimbingan Baru</h2>
                </div>
                
                <p style='margin: 0 0 20px 0; font-size: 16px;'>
                    Yth. Dosen Pembimbing,
                </p>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Mahasiswa bimbingan Anda telah menambahkan jurnal bimbingan baru yang perlu divalidasi.
                </p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #495057; margin: 0 0 15px 0; font-size: 18px;'>Detail Jurnal Bimbingan:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; width: 30%;'>Mahasiswa:</td>
                            <td style='padding: 8px 0;'>{$mahasiswa_nama}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Judul Proposal:</td>
                            <td style='padding: 8px 0;'>{$proposal->judul}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Pertemuan ke:</td>
                            <td style='padding: 8px 0;'>{$pertemuan_ke}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Tanggal:</td>
                            <td style='padding: 8px 0;'>" . date('d F Y', strtotime($tanggal_bimbingan)) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Materi:</td>
                            <td style='padding: 8px 0;'>{$materi_bimbingan}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>⏰ Tindakan Diperlukan:</h4>
                    <p style='margin: 0; color: #856404;'>Silakan login ke sistem untuk memvalidasi jurnal bimbingan ini.</p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('dosen/bimbingan') . "' 
                       style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       📚 Validasi Jurnal Bimbingan
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

        // Ambil email dosen
        $dosen = $this->db->get_where('dosen', ['id' => $proposal->dosen_id])->row();
        
        if ($dosen && $dosen->email) {
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($dosen->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $this->email->send();
        }
    }
}