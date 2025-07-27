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
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('pm.id', 'DESC');
        $proposal_data = $this->db->get()->row();

        // Reset variabel status
        $data['proposal'] = null;
        $data['pending_proposal'] = null;
        $data['waiting_kaprodi'] = null;

        if ($proposal_data) {
            // WORKFLOW LOGIC YANG BENAR:
            
            // 1. PROPOSAL BELUM DIREVIEW KAPRODI
            if ($proposal_data->status_kaprodi == '0') {
                $data['waiting_kaprodi'] = $proposal_data;
                
            // 2. PROPOSAL DITOLAK KAPRODI  
            } elseif ($proposal_data->status_kaprodi == '2') {
                $data['rejected_kaprodi'] = $proposal_data;
                
            // 3. PROPOSAL DISETUJUI KAPRODI, MENUNGGU DOSEN PEMBIMBING
            } elseif ($proposal_data->status_kaprodi == '1' && $proposal_data->status_pembimbing == '0') {
                $data['pending_proposal'] = $proposal_data;
                
            // 4. DOSEN PEMBIMBING MENOLAK
            } elseif ($proposal_data->status_kaprodi == '1' && $proposal_data->status_pembimbing == '2') {
                $data['rejected_dosen'] = $proposal_data;
                
            // 5. BIMBINGAN AKTIF
            } elseif ($proposal_data->status_kaprodi == '1' && $proposal_data->status_pembimbing == '1') {
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

        // DIRECT VIEW - tidak menggunakan template kompleks
        $this->load->view('mahasiswa/header', $data);
        $this->load->view('mahasiswa/bimbingan', $data);
        $this->load->view('mahasiswa/footer');
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
    
            // Cek hanya untuk jurnal yang sudah divalidasi dengan pertemuan_ke yang sama
            $existing_validated = $this->db->get_where('jurnal_bimbingan', [
                'proposal_id' => $proposal->id,
                'pertemuan_ke' => $pertemuan_ke,
                'status_validasi' => '1' // Hanya cek yang sudah divalidasi
            ])->row();
    
            if ($existing_validated) {
                $this->session->set_flashdata('error', 'Pertemuan ke-' . $pertemuan_ke . ' sudah ada dan telah divalidasi! Silakan gunakan nomor pertemuan yang berbeda.');
                redirect('mahasiswa/bimbingan');
                return;
            }
    
            // Untuk jurnal pending dengan pertemuan_ke yang sama, beri pilihan
            $existing_pending = $this->db->get_where('jurnal_bimbingan', [
                'proposal_id' => $proposal->id,
                'pertemuan_ke' => $pertemuan_ke,
                'status_validasi !=' => '1' // Pending atau revisi
            ])->row();
    
            if ($existing_pending) {
                // Update jurnal yang ada daripada buat baru
                $update_data = [
                    'tanggal_bimbingan' => $tanggal_bimbingan,
                    'materi_bimbingan' => $materi_bimbingan,
                    'tindak_lanjut' => $tindak_lanjut,
                    'status_validasi' => '0', // Reset ke pending
                    'updated_at' => date('Y-m-d H:i:s')
                ];
    
                $this->db->where('id', $existing_pending->id);
                $update_result = $this->db->update('jurnal_bimbingan', $update_data);
    
                if ($update_result) {
                    // Kirim notifikasi ke dosen pembimbing
                    $this->_kirim_notifikasi_jurnal_baru($proposal, $pertemuan_ke, $tanggal_bimbingan, $materi_bimbingan);
                    
                    $this->session->set_flashdata('success', 'Jurnal bimbingan pertemuan ke-' . $pertemuan_ke . ' berhasil diperbarui! Menunggu validasi dari dosen pembimbing.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui jurnal bimbingan!');
                }
            } else {
                // Buat jurnal baru
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
    
                $insert_result = $this->db->insert('jurnal_bimbingan', $data_jurnal);
    
                if ($insert_result) {
                    // Kirim notifikasi ke dosen pembimbing
                    $this->_kirim_notifikasi_jurnal_baru($proposal, $pertemuan_ke, $tanggal_bimbingan, $materi_bimbingan);
                    
                    $this->session->set_flashdata('success', 'Jurnal bimbingan pertemuan ke-' . $pertemuan_ke . ' berhasil ditambahkan! Menunggu validasi dari dosen pembimbing.');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan jurnal bimbingan!');
                }
            }
        }
    
        redirect('mahasiswa/bimbingan');
    }

    public function edit_jurnal($jurnal_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi jurnal milik mahasiswa dan dapat diedit (pending atau perlu revisi)
        $jurnal = $this->db->select('jb.*, pm.mahasiswa_id')
                          ->from('jurnal_bimbingan jb')
                          ->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id')
                          ->where('jb.id', $jurnal_id)
                          ->where('pm.mahasiswa_id', $mahasiswa_id)
                          ->where_in('jb.status_validasi', ['0', '2']) // Bisa edit yang pending atau perlu revisi
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
            
            // Jika jurnal sebelumnya perlu revisi, reset status ke pending untuk review ulang
            if ($jurnal->status_validasi == '2') {
                $update_data['status_validasi'] = '0'; // Reset ke pending
                $update_data['catatan_dosen'] = null; // Clear catatan dosen sebelumnya
                $update_data['tanggal_validasi'] = null; // Clear tanggal validasi sebelumnya
            }

            $this->db->where('id', $jurnal_id);
            $this->db->update('jurnal_bimbingan', $update_data);

            if ($this->db->affected_rows() > 0) {
                // Jika ini adalah revisi, kirim notifikasi ke dosen
                if ($jurnal->status_validasi == '2') {
                    $this->_kirim_notifikasi_revisi_jurnal($jurnal, $tanggal_bimbingan, $materi_bimbingan);
                }
                
                $message = ($jurnal->status_validasi == '2') ? 
                    'Jurnal bimbingan berhasil direvisi dan dikirim ulang untuk validasi dosen!' : 
                    'Jurnal bimbingan berhasil diupdate!';
                $this->session->set_flashdata('success', $message);
            } else {
                $this->session->set_flashdata('error', 'Tidak ada perubahan data atau gagal update!');
            }

            redirect('mahasiswa/bimbingan');
        }

        // Load form edit dengan direct view
        $data['title'] = 'Edit Jurnal Bimbingan';
        $data['jurnal'] = $jurnal;
        $this->load->view('mahasiswa/header', $data);
        $this->load->view('mahasiswa/bimbingan_edit', $data);
        $this->load->view('mahasiswa/footer');
    }

    public function hapus_jurnal($jurnal_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi jurnal milik mahasiswa dan dapat dihapus (pending atau perlu revisi)
        $jurnal = $this->db->select('jb.*, pm.mahasiswa_id')
                          ->from('jurnal_bimbingan jb')
                          ->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id')
                          ->where('jb.id', $jurnal_id)
                          ->where('pm.mahasiswa_id', $mahasiswa_id)
                          ->where_in('jb.status_validasi', ['0', '2']) // Bisa hapus yang pending atau perlu revisi
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

    public function detail_jurnal($jurnal_id)
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Validasi jurnal milik mahasiswa
        $jurnal = $this->db->select('jb.*, pm.mahasiswa_id, pm.judul, d.nama as nama_dosen')
                          ->from('jurnal_bimbingan jb')
                          ->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id')
                          ->join('dosen d', 'pm.dosen_id = d.id')
                          ->where('jb.id', $jurnal_id)
                          ->where('pm.mahasiswa_id', $mahasiswa_id)
                          ->get()->row();
    
        if (!$jurnal) {
            $this->session->set_flashdata('error', 'Jurnal tidak ditemukan atau bukan milik Anda.');
            redirect('mahasiswa/bimbingan');
            return;
        }
    
        // Return JSON untuk AJAX call
        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $jurnal->id,
                    'pertemuan_ke' => $jurnal->pertemuan_ke,
                    'tanggal_bimbingan' => $jurnal->tanggal_bimbingan,
                    'materi_bimbingan' => $jurnal->materi_bimbingan,
                    'catatan_dosen' => $jurnal->catatan_dosen,
                    'tindak_lanjut' => $jurnal->tindak_lanjut,
                    'status_validasi' => $jurnal->status_validasi,
                    'tanggal_validasi' => $jurnal->tanggal_validasi,
                    'created_at' => $jurnal->created_at,
                    'nama_dosen' => $jurnal->nama_dosen,
                    'judul_proposal' => $jurnal->judul
                ]
            ]);
            return;
        }
    
        // Direct view untuk halaman detail
        $data['title'] = 'Detail Jurnal Bimbingan';
        $data['jurnal'] = $jurnal;
        $this->load->view('mahasiswa/header', $data);
        $this->load->view('mahasiswa/bimbingan_detail', $data);
        $this->load->view('mahasiswa/footer');
    }
    
    public function export_jurnal()
    {
        $mahasiswa_id = $this->session->userdata('id');
        
        // Ambil data proposal dan jurnal
        $proposal = $this->db->select('pm.*, d.nama as nama_dosen, d.nip as nip_dosen, d.email as email_dosen')
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

        // Ambil data mahasiswa lengkap
        $mahasiswa = $this->db->select('m.*, p.nama as nama_prodi')
                             ->from('mahasiswa m')
                             ->join('prodi p', 'm.prodi_id = p.id')
                             ->where('m.id', $mahasiswa_id)
                             ->get()->row();

        // Ambil jurnal bimbingan
        $jurnal_list = $this->db->select('jb.*, d.nama as nama_validator, d.nip as nip_validator')
                               ->from('jurnal_bimbingan jb')
                               ->join('dosen d', 'jb.validasi_oleh = d.id', 'left')
                               ->where('jb.proposal_id', $proposal->id)
                               ->order_by('jb.pertemuan_ke', 'ASC')
                               ->get()->result();

        if (empty($jurnal_list)) {
            $this->session->set_flashdata('error', 'Belum ada jurnal bimbingan untuk di-export.');
            redirect('mahasiswa/bimbingan');
            return;
        }

        // Load library PDF
        $this->load->library('pdf');
        
        // Prepare data untuk template
        $data = [
            'mahasiswa' => $mahasiswa,
            'proposal' => $proposal,
            'jurnal_list' => $jurnal_list,
            'tanggal_export' => date('d F Y'),
            'total_bimbingan' => count($jurnal_list),
            'bimbingan_tervalidasi' => count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '1'; }))
        ];
        
        // Generate HTML dari template
        $html = $this->load->view('mahasiswa/pdf/jurnal_bimbingan', $data, true);
        
        // Set filename
        $filename = 'Jurnal_Bimbingan_' . $mahasiswa->nim . '_' . date('Ymd_His') . '.pdf';
        $this->pdf->filename = $filename;
        
        // Generate dan stream PDF
        $this->pdf->load_html($html);
        $this->pdf->stream($filename, array("Attachment" => true));
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

    private function _kirim_notifikasi_revisi_jurnal($jurnal, $tanggal_bimbingan, $materi_bimbingan)
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
        $subject = 'Jurnal Bimbingan Direvisi - Pertemuan ke-' . $jurnal->pertemuan_ke . ' - ' . $mahasiswa_nama;

        // Ambil data proposal untuk mendapatkan info lengkap
        $proposal = $this->db->select('pm.*, d.nama as nama_dosen, d.email as email_dosen')
                            ->from('proposal_mahasiswa pm')
                            ->join('dosen d', 'pm.dosen_id = d.id')
                            ->where('pm.id', $jurnal->proposal_id)
                            ->get()->row();

        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Jurnal Bimbingan Direvisi</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <div style='text-align: center; background-color: #ff9800; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;'>
                    <h2 style='margin: 0;'>🔄 Jurnal Bimbingan Direvisi</h2>
                </div>
                
                <p style='margin: 0 0 20px 0; font-size: 16px;'>
                    Yth. Dosen Pembimbing,
                </p>
                
                <p style='margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;'>
                    Mahasiswa bimbingan Anda telah melakukan revisi pada jurnal bimbingan yang sebelumnya diminta untuk diperbaiki.
                </p>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='color: #856404; margin: 0 0 15px 0; font-size: 18px;'>📝 Detail Revisi Jurnal:</h3>
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
                            <td style='padding: 8px 0;'>{$jurnal->pertemuan_ke}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Tanggal:</td>
                            <td style='padding: 8px 0;'>" . date('d F Y', strtotime($tanggal_bimbingan)) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; font-weight: bold;'>Materi Revisi:</td>
                            <td style='padding: 8px 0;'>{$materi_bimbingan}</td>
                        </tr>
                    </table>
                </div>
                
                <div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='color: #155724; margin: 0 0 10px 0; font-size: 16px;'>✅ Tindakan Diperlukan:</h4>
                    <p style='margin: 0; color: #155724;'>Silakan login ke sistem untuk mereview dan memvalidasi jurnal yang telah direvisi.</p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . base_url('dosen/bimbingan') . "' 
                       style='background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                       📚 Review Jurnal Revisi
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

        if ($proposal && $proposal->email_dosen) {
            $this->email->from('stkyakobus@gmail.com', 'SIM Tugas Akhir STK St. Yakobus');
            $this->email->to($proposal->email_dosen);
            $this->email->subject($subject);
            $this->email->message($message);
            
            $this->email->send();
        }
    }
}