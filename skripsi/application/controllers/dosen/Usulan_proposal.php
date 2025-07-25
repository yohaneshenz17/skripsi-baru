<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usulan_proposal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('email');
        $this->load->helper('url');
        $this->load->helper('email_workflow_helper');
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Usulan Proposal - Penunjukan Sebagai Pembimbing';
        $dosen_id = $this->session->userdata('id');
        
        // FIXED: Query yang benar untuk mendapatkan proposal yang ditugaskan ke dosen
        // Hanya berdasarkan dosen_id dan status pembimbing yang belum direspon
        $this->db->select('
            pm.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            m.foto as foto_mahasiswa,
            p.nama as nama_prodi,
            k.nama as nama_kaprodi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen k', 'p.dosen_id = k.id'); // Kaprodi
        
        // KRITERIA UTAMA: Hanya proposal yang sudah ditugaskan ke dosen ini
        $this->db->where('pm.dosen_id', $dosen_id);
        
        // KRITERIA TAMBAHAN: Belum direspon pembimbing
        $this->db->where('(pm.status_pembimbing IS NULL OR pm.status_pembimbing = "0")');
        
        // ORDER BY
        $this->db->order_by('pm.created_at', 'DESC');
        
        $data['proposals'] = $this->db->get()->result();
        
        // Ambil riwayat proposal yang sudah direspon
        $this->db->select('
            pm.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.foto as foto_mahasiswa,
            p.nama as nama_prodi,
            pm.status_pembimbing,
            pm.komentar_pembimbing,
            pm.tanggal_respon_pembimbing
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing IS NOT NULL AND pm.status_pembimbing != "0"');
        $this->db->order_by('pm.tanggal_respon_pembimbing', 'DESC');
        $this->db->limit(10);
        
        $data['riwayat_proposals'] = $this->db->get()->result();
        
        $this->load->view('dosen/usulan_proposal', $data);
    }

    public function detail($proposal_id) {
        $data['title'] = 'Detail Usulan Proposal';
        $dosen_id = $this->session->userdata('id');
        
        // FIXED: Query yang sederhana dan akurat
        // Hanya cek apakah proposal ID ada dan ditugaskan ke dosen ini
        $this->db->select('
            pm.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            m.tempat_lahir,
            m.tanggal_lahir,
            m.jenis_kelamin,
            m.alamat,
            m.foto as foto_mahasiswa,
            p.nama as nama_prodi,
            k.nama as nama_kaprodi,
            k.email as email_kaprodi
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('dosen k', 'p.dosen_id = k.id');
        
        // KRITERIA SEDERHANA: Proposal ID dan dosen yang berhak
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.dosen_id', $dosen_id);
        
        $data['proposal'] = $this->db->get()->row();
        
        // REMOVED: Flash message error yang tidak perlu
        // Jika tidak ditemukan, langsung redirect tanpa pesan error
        if (!$data['proposal']) {
            redirect('dosen/usulan_proposal');
            return;
        }
        
        $this->load->view('dosen/usulan_proposal_detail', $data);
    }

    public function proses_persetujuan() {
        $proposal_id = $this->input->post('proposal_id');
        $status_pembimbing = $this->input->post('status_pembimbing'); // 1 = setuju, 2 = tolak
        $komentar_pembimbing = $this->input->post('komentar_pembimbing');
        $dosen_id = $this->session->userdata('id');
        
        // Validasi input
        if (!$proposal_id || !$status_pembimbing) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('dosen/usulan_proposal/detail/' . $proposal_id);
        }
        
        if ($status_pembimbing == '2' && empty($komentar_pembimbing)) {
            $this->session->set_flashdata('error', 'Komentar wajib diisi jika menolak penunjukan!');
            redirect('dosen/usulan_proposal/detail/' . $proposal_id);
        }
        
        // Validasi proposal exists dan dosen berhak
        $proposal = $this->db->select('pm.*, m.nama as nama_mahasiswa, m.email as email_mahasiswa, m.nim, p.nama as nama_prodi, k.email as email_kaprodi')
                            ->from('proposal_mahasiswa pm')
                            ->join('mahasiswa m', 'pm.mahasiswa_id = m.id')
                            ->join('prodi p', 'm.prodi_id = p.id')
                            ->join('dosen k', 'p.dosen_id = k.id')
                            ->where('pm.id', $proposal_id)
                            ->where('pm.dosen_id', $dosen_id)
                            ->get()->row();
        
        if (!$proposal) {
            redirect('dosen/usulan_proposal');
        }
        
        // PERBAIKAN: Logic update berdasarkan keputusan dosen
        if ($status_pembimbing == '1') {
            // DISETUJUI - update status ke bimbingan
            $update_data = [
                'status_pembimbing' => '1',
                'komentar_pembimbing' => $komentar_pembimbing,
                'tanggal_respon_pembimbing' => date('Y-m-d H:i:s'),
                'workflow_status' => 'bimbingan' // Fase 2: Bimbingan
            ];
        } else {
            // DITOLAK - RESET status agar kaprodi bisa pilih pembimbing baru
            $update_data = [
                'status_pembimbing' => '2',
                'komentar_pembimbing' => $komentar_pembimbing,
                'tanggal_respon_pembimbing' => date('Y-m-d H:i:s'),
                // TAMBAHAN PENTING: Reset status agar muncul kembali di menu kaprodi
                'status' => '0',              // Reset ke belum ditetapkan
                'dosen_id' => NULL,           // Reset pembimbing
                'tanggal_penetapan' => NULL,  // Reset tanggal penetapan
                'penetapan_oleh' => NULL,     // Reset penetapan oleh
                'workflow_status' => 'proposal' // Kembali ke fase proposal
            ];
        }
        
        $this->db->where('id', $proposal_id);
        $update_result = $this->db->update('proposal_mahasiswa', $update_data);
        
        if ($update_result) {
            // Kirim notifikasi
            $this->_kirim_notifikasi_persetujuan($proposal, $status_pembimbing, $komentar_pembimbing);
            
            $message = ($status_pembimbing == '1') ? 
                'Penunjukan sebagai pembimbing berhasil disetujui! Mahasiswa dapat memulai proses bimbingan.' : 
                'Penunjukan sebagai pembimbing berhasil ditolak. Proposal akan dikembalikan ke Kaprodi untuk penetapan pembimbing baru.';
                
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan persetujuan!');
        }
        
        redirect('dosen/usulan_proposal');
    }

    private function _kirim_notifikasi_persetujuan($proposal, $status_pembimbing, $komentar) {
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
    
        if ($status_pembimbing == '1') {
            // DISETUJUI - Kirim ke mahasiswa dan kaprodi
            $this->_kirim_email_persetujuan_mahasiswa($proposal, $dosen_nama);
            $this->_kirim_email_persetujuan_kaprodi($proposal, $dosen_nama);
        } else {
            // DITOLAK - Kirim HANYA ke kaprodi (HAPUS kirim ke mahasiswa)
            $this->_kirim_email_penolakan_kaprodi($proposal, $dosen_nama, $komentar);
        }
    }

    private function _kirim_email_persetujuan_mahasiswa($proposal, $dosen_nama) {
        $subject = 'Penunjukan Pembimbing Disetujui - ' . $proposal->nama_mahasiswa;
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Penunjukan Pembimbing Disetujui</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <div style='text-align: center; background-color: #28a745; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                    <h2 style='margin: 0;'>✅ Penunjukan Pembimbing Disetujui</h2>
                </div>
                
                <p style='margin: 0 0 20px 0; font-size: 16px;'>
                    Selamat <strong>{$proposal->nama_mahasiswa}</strong>,
                </p>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Dosen <strong>{$dosen_nama}</strong> telah menyetujui penunjukan sebagai pembimbing untuk proposal skripsi Anda.
                </p>
                
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #495057; margin: 0 0 15px 0; font-size: 18px;'>Detail Proposal:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold; width: 30%;'>ID Proposal:</td>
                            <td style='padding: 8px 0;'>#{$proposal->id}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>NIM:</td>
                            <td style='padding: 8px 0;'>{$proposal->nim}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Judul:</td>
                            <td style='padding: 8px 0;'>{$proposal->judul}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Dosen Pembimbing:</td>
                            <td style='padding: 8px 0;'>{$dosen_nama}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Prodi:</td>
                            <td style='padding: 8px 0;'>{$proposal->nama_prodi}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #155724; margin: 0 0 10px 0; font-size: 16px;'>📋 Langkah Selanjutnya:</h4>
                    <ol style='color: #155724; margin: 0; padding-left: 20px;'>
                        <li>Hubungi dosen pembimbing untuk memulai proses bimbingan</li>
                        <li>Kembangkan proposal menjadi lengkap (Bab 1-3)</li>
                        <li>Lakukan bimbingan minimal 8x pertemuan</li>
                        <li>Isi jurnal bimbingan secara rutin di sistem</li>
                        <li>Setelah siap, ajukan seminar proposal</li>
                    </ol>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('mahasiswa/bimbingan') . "' 
                       style='background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       📚 Mulai Bimbingan
                    </a>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 3px; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 12px; color: #856404; text-align: center;'>
                        <strong>🎉 Selamat!</strong> Anda telah memasuki <strong>Phase 2: Bimbingan</strong>
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
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($proposal->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $this->email->send();
    }

    private function _kirim_email_persetujuan_kaprodi($proposal, $dosen_nama) {
        $subject = 'Pembimbing Menyetujui Penunjukan - ' . $proposal->nama_mahasiswa;
        
        $message = "
        <h3>Pembimbing Menyetujui Penunjukan</h3>
        <p>Yth. Kaprodi {$proposal->nama_prodi},</p>
        <p>Dosen <strong>{$dosen_nama}</strong> telah <strong>menyetujui</strong> penunjukan sebagai pembimbing untuk:</p>
        <ul>
            <li><strong>Mahasiswa:</strong> {$proposal->nama_mahasiswa} ({$proposal->nim})</li>
            <li><strong>Judul:</strong> {$proposal->judul}</li>
        </ul>
        <p><strong>Status:</strong> Mahasiswa dapat memulai proses bimbingan (Phase 2).</p>
        <p>Login sistem: <a href='" . base_url('kaprodi/dashboard') . "'>Dashboard Kaprodi</a></p>
        <p><br>Terima kasih.<br>Sistem SIM Tugas Akhir</p>
        ";
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($proposal->email_kaprodi);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $this->email->send();
    }

    private function _kirim_email_penolakan_mahasiswa($proposal, $dosen_nama, $komentar) {
        $subject = 'Penunjukan Pembimbing Ditolak - ' . $proposal->nama_mahasiswa;
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Penunjukan Pembimbing Ditolak</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <div style='text-align: center; background-color: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                    <h2 style='margin: 0;'>❌ Penunjukan Pembimbing Ditolak</h2>
                </div>
                
                <p style='margin: 0 0 20px 0; font-size: 16px;'>
                    Kepada <strong>{$proposal->nama_mahasiswa}</strong>,
                </p>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Mohon maaf, dosen <strong>{$dosen_nama}</strong> tidak dapat menerima penunjukan sebagai pembimbing untuk proposal skripsi Anda.
                </p>
                
                <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #721c24; margin: 0 0 10px 0; font-size: 16px;'>💬 Alasan Penolakan:</h4>
                    <p style='margin: 0; color: #721c24;'>{$komentar}</p>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>📋 Langkah Selanjutnya:</h4>
                    <ol style='color: #856404; margin: 0; padding-left: 20px;'>
                        <li>Kaprodi akan menentukan pembimbing yang baru</li>
                        <li>Tunggu notifikasi pembimbing pengganti</li>
                        <li>Atau hubungi kaprodi untuk konsultasi</li>
                    </ol>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('mahasiswa/proposal') . "' 
                       style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       📋 Cek Status Proposal
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
        $this->email->to($proposal->email_mahasiswa);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $this->email->send();
    }

    private function _kirim_email_penolakan_kaprodi($proposal, $dosen_nama, $komentar) {
        $subject = 'Pembimbing Menolak Penunjukan - ' . $proposal->nama_mahasiswa;
        
        $message = "
        <h3>Pembimbing Menolak Penunjukan</h3>
        <p>Yth. Kaprodi {$proposal->nama_prodi},</p>
        <p>Dosen <strong>{$dosen_nama}</strong> <strong>menolak</strong> penunjukan sebagai pembimbing untuk:</p>
        <ul>
            <li><strong>Mahasiswa:</strong> {$proposal->nama_mahasiswa} ({$proposal->nim})</li>
            <li><strong>Judul:</strong> {$proposal->judul}</li>
        </ul>
        <p><strong>Alasan penolakan:</strong> {$komentar}</p>
        <p><strong>Tindakan diperlukan:</strong> Silakan pilih dosen pembimbing yang baru untuk mahasiswa ini.</p>
        <p>Login sistem: <a href='" . base_url('kaprodi/penetapan_pembimbing') . "'>Penetapan Pembimbing</a></p>
        <p><br>Terima kasih.<br>Sistem SIM Tugas Akhir</p>
        ";
        
        $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
        $this->email->to($proposal->email_kaprodi);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $this->email->send();
    }
    
    // Debug function untuk melihat proposal yang bermasalah
    public function debug_proposal_access() {
        $dosen_id = $this->session->userdata('id');
        $proposal_id = 36; // ID proposal yang bermasalah
        
        echo "<h2>🔍 Debug Akses Proposal ID: $proposal_id untuk Dosen ID: $dosen_id</h2>";
        
        // Cek proposal di database
        $this->db->select('*');
        $this->db->from('proposal_mahasiswa');
        $this->db->where('id', $proposal_id);
        $proposal = $this->db->get()->row();
        
        if ($proposal) {
            echo "<h3>✅ Proposal ditemukan di database</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Value</th></tr>";
            foreach ($proposal as $key => $value) {
                echo "<tr><td>$key</td><td>" . ($value ? $value : 'NULL') . "</td></tr>";
            }
            echo "</table>";
            
            echo "<h3>🔍 Validasi Akses:</h3>";
            echo "<p><strong>Dosen ID di proposal:</strong> " . $proposal->dosen_id . "</p>";
            echo "<p><strong>Dosen ID session:</strong> " . $dosen_id . "</p>";
            echo "<p><strong>Match?</strong> " . ($proposal->dosen_id == $dosen_id ? '✅ YA' : '❌ TIDAK') . "</p>";
            
            if ($proposal->dosen_id == $dosen_id) {
                echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px;'>";
                echo "<h4>✅ SOLUTION: Dosen berhak mengakses proposal ini</h4>";
                echo "<p>Problem ada pada query filtering di method detail(). Query sudah diperbaiki.</p>";
                echo "</div>";
            } else {
                echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px;'>";
                echo "<h4>❌ PROBLEM: Dosen tidak berhak mengakses proposal ini</h4>";
                echo "<p>Proposal ini tidak ditugaskan ke dosen yang sedang login.</p>";
                echo "</div>";
            }
        } else {
            echo "<h3>❌ Proposal tidak ditemukan di database</h3>";
        }
        
        echo "<hr>";
        echo "<a href='" . base_url('dosen/usulan_proposal') . "' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Kembali</a>";
    }
}