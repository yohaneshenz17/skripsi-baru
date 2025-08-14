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
            d.nomor_telepon as telepon_dosen,
            d.foto as foto_dosen
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
    
    /**
     * ✅ FIXED: Export jurnal bimbingan mahasiswa dengan format konsisten dengan staf & dosen
     */
    public function export_jurnal() {
        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            // ✅ FIXED: Ambil proposal mahasiswa dengan data lengkap
            $proposal = $this->_get_proposal_data($mahasiswa_id);
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Belum ada proposal yang disetujui atau belum ada pembimbing.');
                redirect('mahasiswa/bimbingan');
                return;
            }

            // ✅ FIXED: Ambil data mahasiswa lengkap dengan kaprodi
            $mahasiswa = $this->_get_mahasiswa_data($mahasiswa_id);
            if (!$mahasiswa) {
                $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan.');
                redirect('mahasiswa/bimbingan');
                return;
            }

            // ✅ FIXED: Jika kaprodi tidak ada dari JOIN, ambil manual
            if (empty($proposal->nama_kaprodi) && !empty($mahasiswa->prodi_id)) {
                $kaprodi_data = $this->_get_kaprodi_by_prodi($mahasiswa->prodi_id);
                if ($kaprodi_data) {
                    $proposal->nama_kaprodi = $kaprodi_data->nama_kaprodi;
                    $proposal->nip_kaprodi = $kaprodi_data->nip_kaprodi;
                    $proposal->email_kaprodi = $kaprodi_data->email_kaprodi;
                }
            }

            // ✅ FIXED: Ambil jurnal bimbingan dengan validator
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

            // ✅ FIXED: Prepare data untuk template (konsisten dengan staf & dosen)
            $data = [
                'proposal' => $proposal,
                'jurnal_bimbingan' => $jurnal_list,
                'generated_by' => $this->session->userdata('nama'),
                'generated_at' => date('d F Y H:i:s')
            ];
            
            // ✅ FIXED: Generate HTML dari template CLEAN yang sama seperti dosen
            $html = $this->load->view('mahasiswa/pdf/jurnal_bimbingan_clean', $data, true);
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', ',', '.'], '_', $mahasiswa->nama) . '_' . date('Y-m-d') . '.html';
            
            // ✅ FIXED: Output HTML yang clean untuk browser print (SAMA SEPERTI DOSEN & STAF)
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: inline; filename="' . str_replace('.html', '.pdf', $filename) . '"');
            
            echo '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>' . htmlspecialchars('Jurnal Bimbingan - ' . $mahasiswa->nama) . '</title>
                <style>
                    @media print { 
                        @page { 
                            size: A4 landscape; 
                            margin: 12mm 8mm; 
                        }
                        body { margin: 0; }
                        .no-print { display: none !important; }
                    }
                    body { 
                        font-family: "Times New Roman", Times, serif; 
                        margin: 0;
                        padding: 10px;
                    }
                    .print-info {
                        background: #e8f4fd;
                        border: 1px solid #2c5aa0;
                        padding: 10px;
                        margin-bottom: 15px;
                        text-align: center;
                        font-size: 12px;
                        color: #2c5aa0;
                    }
                    .print-btn {
                        background: #2c5aa0;
                        color: white;
                        border: none;
                        padding: 8px 15px;
                        cursor: pointer;
                        border-radius: 4px;
                        margin: 0 5px;
                        font-size: 11px;
                    }
                    .print-btn:hover {
                        background: #1e3f73;
                    }
                </style>
            </head>
            <body>
                <div class="print-info no-print">
                    📄 <strong>Jurnal Bimbingan - ' . htmlspecialchars($mahasiswa->nama) . '</strong><br>
                    Klik tombol di bawah untuk mencetak atau simpan sebagai PDF. Pastikan pilih orientasi <strong>Landscape</strong> di pengaturan print.
                    <br><br>
                    <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
                    <button class="print-btn" onclick="window.close()">❌ Tutup</button>
                </div>
                
                ' . $html . '
                
                <script>
                    // Auto-focus untuk print
                    document.addEventListener("DOMContentLoaded", function() {
                        // Keyboard shortcut Ctrl+P
                        document.addEventListener("keydown", function(event) {
                            if (event.ctrlKey && event.key === "p") {
                                event.preventDefault();
                                window.print();
                            }
                        });
                    });
                </script>
            </body>
            </html>';
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in mahasiswa export_jurnal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export jurnal PDF: ' . $e->getMessage());
            redirect('mahasiswa/bimbingan');
        }
    }

    /**
     * ✅ NEW: Export jurnal bimbingan mahasiswa ke Excel (SAMA SEPERTI DOSEN & STAF)
     */
    public function export_excel() {
        $mahasiswa_id = $this->session->userdata('id');
        
        try {
            // Ambil data jurnal bimbingan mahasiswa
            $jurnal_data = $this->_get_jurnal_bimbingan_data($mahasiswa_id);
            
            if (empty($jurnal_data)) {
                $this->session->set_flashdata('error', 'Belum ada jurnal bimbingan untuk di-export.');
                redirect('mahasiswa/bimbingan');
                return;
            }
            
            // Coba export Excel format terbaik yang tersedia
            if ($this->_export_xlsx_phpspreadsheet($jurnal_data)) {
                return; // Berhasil dengan PhpSpreadsheet
            } elseif ($this->_export_xlsx_simple($jurnal_data)) {
                return; // Berhasil dengan Excel XML
            } else {
                // Fallback ke CSV jika semua gagal
                $this->_export_to_csv($jurnal_data);
                return;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error in mahasiswa export_excel: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export data Excel: ' . $e->getMessage());
            redirect('mahasiswa/bimbingan');
        }
    }

    /**
     * ✅ NEW: Export Excel XLSX menggunakan PhpSpreadsheet (ADAPTASI DARI DOSEN & STAF)
     */
    private function _export_xlsx_phpspreadsheet($jurnal_data) {
        try {
            // Cek apakah PhpSpreadsheet tersedia
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                // Coba load manual jika ada di vendor
                if (file_exists(FCPATH . 'vendor/autoload.php')) {
                    require_once FCPATH . 'vendor/autoload.php';
                } else {
                    return false; // Library tidak tersedia
                }
            }
            
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                return false; // Library tetap tidak tersedia
            }
            
            // Create new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Jurnal Bimbingan');
            
            // Set header styling
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                ]
            ];
            
            // Title
            $sheet->setCellValue('A1', 'JURNAL BIMBINGAN - ' . strtoupper($this->session->userdata('nama')));
            $sheet->mergeCells('A1:L1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Subtitle
            $sheet->setCellValue('A2', 'STK Santo Yakobus Merauke');
            $sheet->mergeCells('A2:L2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Generated info
            $sheet->setCellValue('A3', 'Digenerate oleh: ' . $this->session->userdata('nama') . ' | Tanggal: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A3:L3');
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Headers
            $headers = [
                'A5' => 'No',
                'B5' => 'Pertemuan Ke',
                'C5' => 'Tanggal Bimbingan',
                'D5' => 'Materi Bimbingan',
                'E5' => 'Tindak Lanjut',
                'F5' => 'Catatan Mahasiswa',
                'G5' => 'Catatan Dosen',
                'H5' => 'Status Validasi',
                'I5' => 'Tanggal Validasi',
                'J5' => 'Validator',
                'K5' => 'Dibuat Tanggal',
                'L5' => 'Diupdate Tanggal'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Apply header style
            $sheet->getStyle('A5:L5')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(10);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(40);
            $sheet->getColumnDimension('E')->setWidth(30);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(20);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            
            // Data rows
            $row = 6;
            foreach ($jurnal_data as $index => $jurnal) {
                $status_text = '';
                switch($jurnal->status_validasi) {
                    case '1': $status_text = 'Tervalidasi'; break;
                    case '2': $status_text = 'Perlu Revisi'; break;
                    default: $status_text = 'Pending'; break;
                }
                
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $jurnal->pertemuan_ke);
                $sheet->setCellValue('C' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($jurnal->tanggal_bimbingan)));
                $sheet->setCellValue('D' . $row, $jurnal->materi_bimbingan);
                $sheet->setCellValue('E' . $row, $jurnal->tindak_lanjut ?: '-');
                $sheet->setCellValue('F' . $row, $jurnal->catatan_mahasiswa ?: '-');
                $sheet->setCellValue('G' . $row, $jurnal->catatan_dosen ?: '-');
                $sheet->setCellValue('H' . $row, $status_text);
                $sheet->setCellValue('I' . $row, $jurnal->tanggal_validasi ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($jurnal->tanggal_validasi)) : '-');
                $sheet->setCellValue('J' . $row, $jurnal->nama_validator ?: '-');
                $sheet->setCellValue('K' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($jurnal->created_at)));
                $sheet->setCellValue('L' . $row, $jurnal->updated_at ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($jurnal->updated_at)) : '-');
                
                // Format date columns
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                
                $row++;
            }
            
            // Apply borders to all data
            $sheet->getStyle('A5:L' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            
            // Summary section
            $row += 2;
            $sheet->setCellValue('A' . $row, 'RINGKASAN BIMBINGAN');
            $sheet->mergeCells('A' . $row . ':L' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
            
            $row++;
            $total_jurnal = count($jurnal_data);
            $total_tervalidasi = count(array_filter($jurnal_data, function($j) { return $j->status_validasi == '1'; }));
            $total_pending = count(array_filter($jurnal_data, function($j) { return $j->status_validasi == '0'; }));
            $total_revisi = count(array_filter($jurnal_data, function($j) { return $j->status_validasi == '2'; }));
            
            $sheet->setCellValue('A' . $row, 'Total Jurnal:');
            $sheet->setCellValue('B' . $row, $total_jurnal);
            $row++;
            $sheet->setCellValue('A' . $row, 'Tervalidasi:');
            $sheet->setCellValue('B' . $row, $total_tervalidasi);
            $row++;
            $sheet->setCellValue('A' . $row, 'Pending:');
            $sheet->setCellValue('B' . $row, $total_pending);
            $row++;
            $sheet->setCellValue('A' . $row, 'Perlu Revisi:');
            $sheet->setCellValue('B' . $row, $total_revisi);
            
            // Set filename and download
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', '.'], '_', $this->session->userdata('nama')) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in mahasiswa PhpSpreadsheet export: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NEW: Export Excel menggunakan Excel XML format (fallback)
     */
    private function _export_xlsx_simple($jurnal_data) {
        try {
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', '.'], '_', $this->session->userdata('nama')) . '_' . date('Y-m-d_H-i-s') . '.xls';
            
            // Headers for Excel
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            // Start output
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
            
            // Styles
            echo '<Styles>' . "\n";
            echo '<Style ss:ID="HeaderStyle">' . "\n";
            echo '<Font ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
            echo '<Interior ss:Color="#4472C4" ss:Pattern="Solid"/>' . "\n";
            echo '<Borders>' . "\n";
            echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '</Borders>' . "\n";
            echo '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
            echo '</Style>' . "\n";
            
            echo '<Style ss:ID="DataStyle">' . "\n";
            echo '<Borders>' . "\n";
            echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
            echo '</Borders>' . "\n";
            echo '</Style>' . "\n";
            echo '</Styles>' . "\n";
            
            // Worksheet
            echo '<Worksheet ss:Name="Jurnal Bimbingan">' . "\n";
            echo '<Table>' . "\n";
            
            // Title row
            echo '<Row>' . "\n";
            echo '<Cell ss:MergeAcross="11" ss:StyleID="HeaderStyle">' . "\n";
            echo '<Data ss:Type="String">JURNAL BIMBINGAN - ' . htmlspecialchars(strtoupper($this->session->userdata('nama'))) . '</Data>' . "\n";
            echo '</Cell>' . "\n";
            echo '</Row>' . "\n";
            
            // Empty row
            echo '<Row></Row>' . "\n";
            
            // Headers
            echo '<Row>' . "\n";
            $headers = ['No', 'Pertemuan Ke', 'Tanggal', 'Materi', 'Tindak Lanjut', 
                       'Catatan Mhs', 'Catatan Dosen', 'Status', 'Tgl Validasi', 
                       'Validator', 'Dibuat', 'Diupdate'];
            
            foreach ($headers as $header) {
                echo '<Cell ss:StyleID="HeaderStyle">' . "\n";
                echo '<Data ss:Type="String">' . htmlspecialchars($header) . '</Data>' . "\n";
                echo '</Cell>' . "\n";
            }
            echo '</Row>' . "\n";
            
            // Data rows
            foreach ($jurnal_data as $index => $jurnal) {
                $status_text = '';
                switch($jurnal->status_validasi) {
                    case '1': $status_text = 'Tervalidasi'; break;
                    case '2': $status_text = 'Perlu Revisi'; break;
                    default: $status_text = 'Pending'; break;
                }
                
                echo '<Row>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . ($index + 1) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $jurnal->pertemuan_ke . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($jurnal->materi_bimbingan) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($jurnal->tindak_lanjut ?: '-') . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($jurnal->catatan_mahasiswa ?: '-') . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($jurnal->catatan_dosen ?: '-') . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($status_text) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . ($jurnal->tanggal_validasi ? date('d/m/Y', strtotime($jurnal->tanggal_validasi)) : '-') . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($jurnal->nama_validator ?: '-') . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . date('d/m/Y', strtotime($jurnal->created_at)) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . ($jurnal->updated_at ? date('d/m/Y', strtotime($jurnal->updated_at)) : '-') . '</Data></Cell>' . "\n";
                echo '</Row>' . "\n";
            }
            
            echo '</Table>' . "\n";
            echo '</Worksheet>' . "\n";
            echo '</Workbook>' . "\n";
            
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in mahasiswa Excel XML export: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NEW: Fallback export ke CSV
     */
    private function _export_to_csv($jurnal_data) {
        $filename = 'Jurnal_Bimbingan_' . str_replace([' ', '.'], '_', $this->session->userdata('nama')) . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'No', 'Pertemuan Ke', 'Tanggal', 'Materi', 'Tindak Lanjut',
            'Catatan Mahasiswa', 'Catatan Dosen', 'Status', 'Tanggal Validasi',
            'Validator', 'Dibuat', 'Diupdate'
        ]);
        
        foreach ($jurnal_data as $index => $jurnal) {
            $status_text = '';
            switch($jurnal->status_validasi) {
                case '1': $status_text = 'Tervalidasi'; break;
                case '2': $status_text = 'Perlu Revisi'; break;
                default: $status_text = 'Pending'; break;
            }
            
            fputcsv($output, [
                $index + 1,
                $jurnal->pertemuan_ke,
                date('d/m/Y', strtotime($jurnal->tanggal_bimbingan)),
                $jurnal->materi_bimbingan,
                $jurnal->tindak_lanjut ?: '-',
                $jurnal->catatan_mahasiswa ?: '-',
                $jurnal->catatan_dosen ?: '-',
                $status_text,
                $jurnal->tanggal_validasi ? date('d/m/Y', strtotime($jurnal->tanggal_validasi)) : '-',
                $jurnal->nama_validator ?: '-',
                date('d/m/Y', strtotime($jurnal->created_at)),
                $jurnal->updated_at ? date('d/m/Y', strtotime($jurnal->updated_at)) : '-'
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * ✅ NEW: Get jurnal bimbingan data untuk export
     */
    private function _get_jurnal_bimbingan_data($mahasiswa_id) {
        $this->db->select('
            jb.*,
            d.nama as nama_validator,
            d.nip as nip_validator,
            pm.judul as judul_proposal
        ');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
        $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
        $this->db->order_by('jb.pertemuan_ke', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * ✅ EXISTING: Get proposal data lengkap DENGAN DATA KAPRODI (SAMA SEPERTI DOSEN & STAF)
     */
    private function _get_proposal_data($mahasiswa_id) {
        try {
            $this->db->select('
                pm.*,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                m.prodi_id,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                d.nip as nip_pembimbing,
                d.email as email_pembimbing,
                kaprodi.nama as nama_kaprodi,
                kaprodi.nip as nip_kaprodi,
                kaprodi.email as email_kaprodi
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->join('dosen kaprodi', 'p.dosen_id = kaprodi.id', 'left');
            $this->db->where('pm.mahasiswa_id', $mahasiswa_id);
            $this->db->where('pm.status_pembimbing', '1');
            $this->db->order_by('pm.created_at', 'DESC');
            $this->db->limit(1);
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error getting proposal data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ NEW: Get mahasiswa data lengkap
     */
    private function _get_mahasiswa_data($mahasiswa_id) {
        try {
            $this->db->select('m.*, p.nama as nama_prodi');
            $this->db->from('mahasiswa m');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->where('m.id', $mahasiswa_id);
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            return null;
        } catch (Exception $e) {
            log_message('error', 'Error getting mahasiswa data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ EXISTING: Method untuk mendapatkan data kaprodi (SAMA SEPERTI DOSEN & STAF)
     */
    private function _get_kaprodi_by_prodi($prodi_id) {
        try {
            $this->db->select('
                d.nama as nama_kaprodi,
                d.nip as nip_kaprodi,
                d.email as email_kaprodi
            ');
            $this->db->from('prodi p');
            $this->db->join('dosen d', 'p.dosen_id = d.id');
            $this->db->where('p.id', $prodi_id);
            $this->db->where('d.level', '4'); // Level 4 = Kaprodi
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            // Fallback: cari kaprodi dari level di tabel dosen
            $this->db->select('
                d.nama as nama_kaprodi,
                d.nip as nip_kaprodi,
                d.email as email_kaprodi
            ');
            $this->db->from('dosen d');
            $this->db->where('d.level', '4');
            $this->db->limit(1);
            
            $query = $this->db->get();
            return $query ? $query->row() : null;
            
        } catch (Exception $e) {
            log_message('error', 'Error getting kaprodi data: ' . $e->getMessage());
            return null;
        }
    }

    // [EXISTING METHODS: _kirim_notifikasi_jurnal_baru, _kirim_notifikasi_revisi_jurnal - tetap sama]
    
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