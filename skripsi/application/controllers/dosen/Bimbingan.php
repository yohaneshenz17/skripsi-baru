<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bimbingan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper('url');
        
        // Cek login dan level dosen
        if(!$this->session->userdata('logged_in') || $this->session->userdata('level') != '2') {
            redirect('auth/login');
        }
    }

    public function index() {
        $data['title'] = 'Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil mahasiswa dengan statistik jurnal bimbingan - IMPROVED QUERY
        $this->db->select('
            pm.id as proposal_id,
            pm.judul,
            pm.jenis_penelitian,
            pm.lokasi_penelitian,
            pm.workflow_status,
            pm.created_at as tanggal_proposal,
            pm.tanggal_penetapan,
            m.id as mahasiswa_id,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            p.nama as nama_prodi,
            COUNT(jb.id) as total_bimbingan,
            SUM(CASE WHEN jb.status_validasi = "1" THEN 1 ELSE 0 END) as jurnal_tervalidasi,
            SUM(CASE WHEN jb.status_validasi = "0" THEN 1 ELSE 0 END) as jurnal_pending,
            SUM(CASE WHEN jb.status_validasi = "2" THEN 1 ELSE 0 END) as jurnal_revisi,
            MAX(jb.tanggal_bimbingan) as bimbingan_terakhir,
            MAX(jb.created_at) as jurnal_terakhir_dibuat
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->join('jurnal_bimbingan jb', 'pm.id = jb.proposal_id', 'left');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_kaprodi', '1');
        $this->db->where('pm.status_pembimbing', '1'); 
        $this->db->group_by('pm.id, m.nim, m.nama, m.email, p.nama, pm.judul, pm.jenis_penelitian, pm.lokasi_penelitian, pm.workflow_status, pm.created_at, m.nomor_telepon');
        $this->db->order_by('jurnal_pending', 'DESC');
        $this->db->order_by('jurnal_terakhir_dibuat', 'DESC');
        
        $mahasiswa_list = $this->db->get()->result();
        
        // Ambil jurnal pending untuk overview  
        $this->db->select('
            jb.*,
            pm.judul as judul_proposal,
            m.nim,
            m.nama as nama_mahasiswa
        ');
        $this->db->from('jurnal_bimbingan jb');
        $this->db->join('proposal_mahasiswa pm', 'jb.proposal_id = pm.id');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('jb.status_validasi', '0');
        $this->db->order_by('jb.created_at', 'DESC');
        $this->db->limit(10);
        
        $data['jurnal_pending_list'] = $this->db->get()->result();
        $data['mahasiswa_bimbingan'] = $mahasiswa_list;
        
        // Statistik untuk dashboard
        $data['total_mahasiswa'] = count($mahasiswa_list);
        $data['total_jurnal_pending'] = 0;
        $data['total_jurnal_tervalidasi'] = 0;
        
        foreach($mahasiswa_list as $mhs) {
            $data['total_jurnal_pending'] += (int)$mhs->jurnal_pending;
            $data['total_jurnal_tervalidasi'] += (int)$mhs->jurnal_tervalidasi;
        }
        
        // GUNAKAN STRUKTUR WRAPPER ASLI ANDA
        $this->load->view('dosen/bimbingan', $data);
    }

    public function detail_mahasiswa($proposal_id) {
        $data['title'] = 'Detail Bimbingan Mahasiswa';
        $dosen_id = $this->session->userdata('id');
        
        // Ambil detail mahasiswa dan proposal
        $this->db->select('
            pm.*,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            m.alamat,
            m.foto,
            p.nama as nama_prodi,
            pm.id as proposal_id
        ');
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
        $this->db->join('prodi p', 'm.prodi_id = p.id');
        $this->db->where('pm.id', $proposal_id);
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1');
        
        $mahasiswa = $this->db->get()->row();
        
        if (!$mahasiswa) {
            $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan atau bukan bimbingan Anda!');
            redirect('dosen/bimbingan');
            return;
        }
        
        $data['mahasiswa'] = $mahasiswa;
        
        // Ambil jurnal bimbingan
        $this->db->select('*');
        $this->db->from('jurnal_bimbingan');
        $this->db->where('proposal_id', $proposal_id);
        $this->db->order_by('pertemuan_ke', 'ASC');
        
        $jurnal_list = $this->db->get()->result();
        $data['jurnal_bimbingan'] = $jurnal_list;
        
        // Hitung statistik
        $data['total_bimbingan'] = count($jurnal_list);
        $data['bimbingan_tervalidasi'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '1'; }));
        $data['bimbingan_pending'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '0'; }));
        $data['bimbingan_revisi'] = count(array_filter($jurnal_list, function($j) { return $j->status_validasi == '2'; }));
        
        // GUNAKAN STRUKTUR WRAPPER ASLI ANDA
        $this->load->view('dosen/bimbingan_detail', $data);
    }

    // [EXISTING METHODS: quick_validasi, validasi_jurnal, tambah_jurnal, edit_jurnal, get_jurnal, delete_jurnal - tetap sama]

    /**
     * ✅ NEW: Export Jurnal ke PDF - FIXED FORMAT SAMA SEPERTI STAF
     */
    public function export_jurnal($proposal_id) {
        $dosen_id = $this->session->userdata('id');
        
        if (!is_numeric($proposal_id)) {
            $this->session->set_flashdata('error', 'ID proposal tidak valid!');
            redirect('dosen/bimbingan');
            return;
        }
        
        try {
            // ✅ Query proposal dengan data kaprodi lengkap (SAMA SEPERTI STAF)
            $proposal = $this->_get_proposal_data($proposal_id);
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Data proposal tidak ditemukan!');
                redirect('dosen/bimbingan');
                return;
            }
            
            // Validasi dosen bimbingan
            if ($proposal->dosen_id != $dosen_id) {
                $this->session->set_flashdata('error', 'Anda tidak memiliki akses untuk data ini!');
                redirect('dosen/bimbingan');
                return;
            }
            
            // ✅ Jika kaprodi tidak ada dari JOIN, ambil manual
            if (empty($proposal->nama_kaprodi) && !empty($proposal->prodi_id)) {
                $kaprodi_data = $this->_get_kaprodi_by_prodi($proposal->prodi_id);
                if ($kaprodi_data) {
                    $proposal->nama_kaprodi = $kaprodi_data->nama_kaprodi;
                    $proposal->nip_kaprodi = $kaprodi_data->nip_kaprodi;
                    $proposal->email_kaprodi = $kaprodi_data->email_kaprodi;
                }
            }
            
            // ✅ Query jurnal dengan validator
            $this->db->select('
                jb.*,
                d.nama as nama_dosen_validator,
                d.nip as nip_dosen_validator
            ');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
            $this->db->where('jb.proposal_id', $proposal_id);
            $this->db->order_by('jb.pertemuan_ke', 'ASC');
            $jurnal_bimbingan = $this->db->get()->result();
            
            // ✅ Prepare data untuk template
            $data = [
                'proposal' => $proposal,
                'jurnal_bimbingan' => $jurnal_bimbingan,
                'generated_by' => $this->session->userdata('nama'),
                'generated_at' => date('d F Y H:i:s')
            ];
            
            // ✅ GUNAKAN TEMPLATE CLEAN YANG SAMA SEPERTI STAF
            $html = $this->load->view('dosen/pdf/jurnal_bimbingan_clean', $data, true);
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', ',', '.'], '_', $proposal->nama_mahasiswa) . '_' . date('Y-m-d') . '.html';
            
            // ✅ Output HTML yang clean untuk browser print (SAMA SEPERTI STAF)
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: inline; filename="' . str_replace('.html', '.pdf', $filename) . '"');
            
            echo '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>' . htmlspecialchars('Jurnal Bimbingan - ' . $proposal->nama_mahasiswa) . '</title>
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
                    📄 <strong>Jurnal Bimbingan - ' . htmlspecialchars($proposal->nama_mahasiswa) . '</strong><br>
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
            log_message('error', 'Error in dosen export_jurnal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export jurnal PDF: ' . $e->getMessage());
            redirect('dosen/bimbingan');
        }
    }

    /**
     * ✅ NEW: Export All Jurnal Bimbingan ke Excel (SAMA SEPERTI STAF)
     */
    public function export_all_excel() {
        $dosen_id = $this->session->userdata('id');
        
        try {
            // Ambil semua data mahasiswa bimbingan dosen ini
            $mahasiswa_data = $this->_get_all_bimbingan_data($dosen_id);
            
            if (empty($mahasiswa_data)) {
                $this->session->set_flashdata('error', 'Tidak ada data mahasiswa bimbingan untuk di-export!');
                redirect('dosen/bimbingan');
                return;
            }
            
            // Coba export Excel format terbaik yang tersedia
            if ($this->_export_xlsx_phpspreadsheet($mahasiswa_data)) {
                return; // Berhasil dengan PhpSpreadsheet
            } elseif ($this->_export_xlsx_simple($mahasiswa_data)) {
                return; // Berhasil dengan Excel XML
            } else {
                // Fallback ke CSV jika semua gagal
                $this->_export_to_csv($mahasiswa_data);
                return;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Error in dosen export_all_excel: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export data Excel: ' . $e->getMessage());
            redirect('dosen/bimbingan');
        }
    }

    /**
     * ✅ NEW: Export Excel XLSX menggunakan PhpSpreadsheet (ADAPTASI DARI STAF)
     */
    private function _export_xlsx_phpspreadsheet($mahasiswa_data) {
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
            $sheet->setCellValue('A1', 'JURNAL BIMBINGAN MAHASISWA - ' . strtoupper($this->session->userdata('nama')));
            $sheet->mergeCells('A1:M1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Subtitle
            $sheet->setCellValue('A2', 'STK Santo Yakobus Merauke');
            $sheet->mergeCells('A2:M2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Generated info
            $sheet->setCellValue('A3', 'Digenerate oleh: ' . $this->session->userdata('nama') . ' | Tanggal: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A3:M3');
            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Headers
            $headers = [
                'A5' => 'No',
                'B5' => 'NIM',
                'C5' => 'Nama Mahasiswa',
                'D5' => 'Program Studi',
                'E5' => 'Judul Proposal',
                'F5' => 'Total Bimbingan',
                'G5' => 'Tervalidasi',
                'H5' => 'Pending',
                'I5' => 'Revisi',
                'J5' => 'Progress %',
                'K5' => 'Status Workflow',
                'L5' => 'Tanggal Pengajuan',
                'M5' => 'Email Mahasiswa'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Apply header style
            $sheet->getStyle('A5:M5')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(35);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(10);
            $sheet->getColumnDimension('I')->setWidth(10);
            $sheet->getColumnDimension('J')->setWidth(12);
            $sheet->getColumnDimension('K')->setWidth(18);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(25);
            
            // Data rows
            $row = 6;
            foreach ($mahasiswa_data as $index => $mhs) {
                $progress_persen = $mhs->total_bimbingan > 0 ? min(($mhs->total_bimbingan / 16) * 100, 100) : 0;
                
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $mhs->nim);
                $sheet->setCellValue('C' . $row, $mhs->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $mhs->nama_prodi);
                $sheet->setCellValue('E' . $row, $mhs->judul);
                $sheet->setCellValue('F' . $row, $mhs->total_bimbingan);
                $sheet->setCellValue('G' . $row, $mhs->jurnal_tervalidasi);
                $sheet->setCellValue('H' . $row, $mhs->jurnal_pending);
                $sheet->setCellValue('I' . $row, $mhs->jurnal_revisi);
                $sheet->setCellValue('J' . $row, round($progress_persen, 1) . '%');
                $sheet->setCellValue('K' . $row, ucfirst(str_replace('_', ' ', $mhs->workflow_status)));
                $sheet->setCellValue('L' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($mhs->tanggal_pengajuan)));
                $sheet->setCellValue('M' . $row, $mhs->email_mahasiswa);
                
                // Format date column
                $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                
                $row++;
            }
            
            // Apply borders to all data
            $sheet->getStyle('A5:M' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            
            // Summary section
            $row += 2;
            $sheet->setCellValue('A' . $row, 'RINGKASAN BIMBINGAN');
            $sheet->mergeCells('A' . $row . ':M' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
            
            $row++;
            $total_mahasiswa = count($mahasiswa_data);
            $total_jurnal = array_sum(array_column($mahasiswa_data, 'total_bimbingan'));
            $total_tervalidasi = array_sum(array_column($mahasiswa_data, 'jurnal_tervalidasi'));
            $total_pending = array_sum(array_column($mahasiswa_data, 'jurnal_pending'));
            
            $sheet->setCellValue('A' . $row, 'Total Mahasiswa Bimbingan:');
            $sheet->setCellValue('B' . $row, $total_mahasiswa);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Jurnal Bimbingan:');
            $sheet->setCellValue('B' . $row, $total_jurnal);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Jurnal Tervalidasi:');
            $sheet->setCellValue('B' . $row, $total_tervalidasi);
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Jurnal Pending:');
            $sheet->setCellValue('B' . $row, $total_pending);
            
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
            log_message('error', 'Error in dosen PhpSpreadsheet export: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NEW: Export Excel menggunakan Excel XML format (fallback)
     */
    private function _export_xlsx_simple($mahasiswa_data) {
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
            echo '<Cell ss:MergeAcross="12" ss:StyleID="HeaderStyle">' . "\n";
            echo '<Data ss:Type="String">JURNAL BIMBINGAN - ' . htmlspecialchars(strtoupper($this->session->userdata('nama'))) . '</Data>' . "\n";
            echo '</Cell>' . "\n";
            echo '</Row>' . "\n";
            
            // Empty row
            echo '<Row></Row>' . "\n";
            
            // Headers
            echo '<Row>' . "\n";
            $headers = ['No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Judul Proposal', 
                       'Total Bimbingan', 'Tervalidasi', 'Pending', 'Revisi', 'Progress %',
                       'Status Workflow', 'Tanggal Pengajuan', 'Email Mahasiswa'];
            
            foreach ($headers as $header) {
                echo '<Cell ss:StyleID="HeaderStyle">' . "\n";
                echo '<Data ss:Type="String">' . htmlspecialchars($header) . '</Data>' . "\n";
                echo '</Cell>' . "\n";
            }
            echo '</Row>' . "\n";
            
            // Data rows
            foreach ($mahasiswa_data as $index => $mhs) {
                $progress_persen = $mhs->total_bimbingan > 0 ? min(($mhs->total_bimbingan / 16) * 100, 100) : 0;
                
                echo '<Row>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . ($index + 1) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nim) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nama_mahasiswa) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nama_prodi) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->judul) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $mhs->total_bimbingan . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $mhs->jurnal_tervalidasi . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $mhs->jurnal_pending . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . $mhs->jurnal_revisi . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . round($progress_persen, 1) . '%</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $mhs->workflow_status))) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . date('d/m/Y', strtotime($mhs->tanggal_pengajuan)) . '</Data></Cell>' . "\n";
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->email_mahasiswa) . '</Data></Cell>' . "\n";
                echo '</Row>' . "\n";
            }
            
            echo '</Table>' . "\n";
            echo '</Worksheet>' . "\n";
            echo '</Workbook>' . "\n";
            
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in dosen Excel XML export: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ NEW: Fallback export ke CSV
     */
    private function _export_to_csv($mahasiswa_data) {
        $filename = 'Jurnal_Bimbingan_' . str_replace([' ', '.'], '_', $this->session->userdata('nama')) . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Judul Proposal',
            'Total Bimbingan', 'Tervalidasi', 'Pending', 'Revisi', 'Progress %',
            'Status Workflow', 'Tanggal Pengajuan', 'Email Mahasiswa'
        ]);
        
        foreach ($mahasiswa_data as $index => $mhs) {
            $progress_persen = $mhs->total_bimbingan > 0 ? min(($mhs->total_bimbingan / 16) * 100, 100) : 0;
            
            fputcsv($output, [
                $index + 1,
                $mhs->nim,
                $mhs->nama_mahasiswa,
                $mhs->nama_prodi,
                $mhs->judul,
                $mhs->total_bimbingan,
                $mhs->jurnal_tervalidasi,
                $mhs->jurnal_pending,
                $mhs->jurnal_revisi,
                round($progress_persen, 1) . '%',
                ucfirst(str_replace('_', ' ', $mhs->workflow_status)),
                date('d/m/Y', strtotime($mhs->tanggal_pengajuan)),
                $mhs->email_mahasiswa
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * ✅ NEW: Get all bimbingan data untuk export (ADAPTASI DARI STAF)
     */
    private function _get_all_bimbingan_data($dosen_id) {
        $this->db->select('
            pm.id as proposal_id,
            pm.judul,
            pm.workflow_status,
            pm.created_at as tanggal_pengajuan,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            p.nama as nama_prodi,
            COUNT(jb.id) as total_bimbingan,
            SUM(CASE WHEN jb.status_validasi = "1" THEN 1 ELSE 0 END) as jurnal_tervalidasi,
            SUM(CASE WHEN jb.status_validasi = "0" THEN 1 ELSE 0 END) as jurnal_pending,
            SUM(CASE WHEN jb.status_validasi = "2" THEN 1 ELSE 0 END) as jurnal_revisi
        ');
        
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner');
        $this->db->join('prodi p', 'm.prodi_id = p.id', 'inner');
        $this->db->join('jurnal_bimbingan jb', 'pm.id = jb.proposal_id', 'left');
        $this->db->where('pm.dosen_id', $dosen_id);
        $this->db->where('pm.status_pembimbing', '1');
        $this->db->group_by('pm.id, m.nim, m.nama, m.email, p.nama, pm.judul, pm.workflow_status, pm.created_at');
        $this->db->order_by('pm.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * ✅ EXISTING: Get proposal data lengkap DENGAN DATA KAPRODI (SAMA SEPERTI STAF)
     */
    private function _get_proposal_data($proposal_id) {
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
            $this->db->where('pm.id', $proposal_id);
            
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
     * ✅ EXISTING: Method untuk mendapatkan data kaprodi
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

    // [EXISTING METHODS LAINNYA TETAP SAMA...]
}