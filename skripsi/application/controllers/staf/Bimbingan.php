<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Staf - Menu Bimbingan (XLSX EXPORT VERSION)
 * FINAL VERSION - Dengan export Excel XLSX format yang rapi
 * Database: stkp7133_skripsi (Updated: 24 July 2025)
 * 
 * @author STK Santo Yakobus Development Team
 * @version 3.4 - ADDED XLSX EXPORT SUPPORT
 */
class Bimbingan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'date', 'file', 'download']);
        
        // Load PDF library jika tersedia
        if (file_exists(APPPATH . 'libraries/Pdf.php')) {
            $this->load->library('pdf');
        }
        
        // Cek login dan level staf
        if (!$this->session->userdata('logged_in') || $this->session->userdata('level') != '5') {
            redirect('auth/login');
        }
    }

    /**
     * Halaman utama menu bimbingan
     * Menampilkan daftar mahasiswa yang sedang dalam tahap bimbingan
     */
    public function index() {
        $data['title'] = 'Monitoring Bimbingan Mahasiswa';
        
        // Filter parameters dari GET request
        $prodi_id = $this->input->get('prodi_id');
        $dosen_id = $this->input->get('dosen_id');
        $status = $this->input->get('status');
        $search = $this->input->get('search');
        
        try {
            // QUERY UTAMA: Ambil mahasiswa yang sedang bimbingan
            // Berdasarkan struktur database asli
            $this->db->select('
                pm.id as proposal_id,
                pm.judul,
                pm.workflow_status,
                pm.created_at as tanggal_pengajuan,
                pm.tanggal_penetapan,
                m.id as mahasiswa_id,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                m.nomor_telepon,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                d.nomor_telepon as telepon_pembimbing
            ');
            
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner');
            $this->db->join('prodi p', 'm.prodi_id = p.id', 'inner');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            
            // FILTER: Hanya mahasiswa yang sedang dalam proses bimbingan
            $this->db->where('pm.status_pembimbing', '1'); // Pembimbing sudah menyetujui
            $this->db->where_in('pm.workflow_status', [
                'bimbingan', 
                'seminar_proposal', 
                'penelitian', 
                'seminar_skripsi', 
                'publikasi'
            ]);
            
            // Apply additional filters
            if ($prodi_id && is_numeric($prodi_id)) {
                $this->db->where('m.prodi_id', $prodi_id);
            }
            if ($dosen_id && is_numeric($dosen_id)) {
                $this->db->where('pm.dosen_id', $dosen_id);
            }
            if ($status && in_array($status, ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi'])) {
                $this->db->where('pm.workflow_status', $status);
            }
            if ($search && strlen(trim($search)) > 0) {
                $search_term = trim($search);
                $this->db->group_start();
                $this->db->like('m.nama', $search_term);
                $this->db->or_like('m.nim', $search_term);
                $this->db->or_like('pm.judul', $search_term);
                $this->db->group_end();
            }
            
            $this->db->order_by('pm.tanggal_penetapan', 'DESC');
            $this->db->order_by('pm.created_at', 'DESC');
            
            $mahasiswa_result = $this->db->get()->result();
            
            // TAMBAH DATA JURNAL untuk setiap mahasiswa
            $data['mahasiswa_bimbingan'] = [];
            foreach ($mahasiswa_result as $mhs) {
                // Hitung jurnal bimbingan
                $jurnal_stats = $this->_get_jurnal_statistics($mhs->proposal_id);
                
                $mhs->total_jurnal = $jurnal_stats['total'];
                $mhs->jurnal_tervalidasi = $jurnal_stats['tervalidasi'];
                $mhs->jurnal_pending = $jurnal_stats['pending'];
                
                $data['mahasiswa_bimbingan'][] = $mhs;
            }
            
            // Data untuk dropdown filter
            $data['prodi_list'] = $this->_get_prodi_list();
            $data['dosen_list'] = $this->_get_dosen_pembimbing_list();
            $data['status_list'] = [
                'bimbingan' => 'Bimbingan',
                'seminar_proposal' => 'Seminar Proposal',
                'penelitian' => 'Penelitian', 
                'seminar_skripsi' => 'Seminar Skripsi',
                'publikasi' => 'Publikasi'
            ];
            
            // Statistik untuk dashboard
            $data['statistics'] = $this->_get_bimbingan_statistics();
            
        } catch (Exception $e) {
            log_message('error', 'Error in staf/bimbingan/index: ' . $e->getMessage());
            $data['mahasiswa_bimbingan'] = [];
            $data['prodi_list'] = [];
            $data['dosen_list'] = [];
            $data['status_list'] = [];
            $data['statistics'] = [];
            
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat data bimbingan.');
        }
        
        $this->load->view('staf/bimbingan/index', $data);
    }

    /**
     * Detail progress mahasiswa
     */
    public function detail_mahasiswa($proposal_id) {
        if (!is_numeric($proposal_id)) {
            $this->session->set_flashdata('error', 'ID proposal tidak valid!');
            redirect('staf/bimbingan');
        }
        
        $data['title'] = 'Detail Progress Bimbingan';
        
        try {
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
                d.nama as nama_pembimbing,
                d.email as email_pembimbing,
                d.nomor_telepon as telepon_pembimbing,
                d.nip as nip_pembimbing
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
            $this->db->where('pm.id', $proposal_id);
            
            $mahasiswa = $this->db->get()->row();
            
            if (!$mahasiswa) {
                $this->session->set_flashdata('error', 'Data mahasiswa tidak ditemukan!');
                redirect('staf/bimbingan');
            }
            
            $data['proposal'] = $mahasiswa;
            
            // ✅ FIXED: Query jurnal bimbingan yang benar
            $this->db->select('
                jb.*,
                d.nama as nama_dosen_validator
            ');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
            $this->db->where('jb.proposal_id', $proposal_id);
            $this->db->order_by('jb.pertemuan_ke', 'ASC');
            $data['jurnal_bimbingan'] = $this->db->get()->result();
            
            // Progress workflow
            $data['progress_data'] = $this->_get_progress_workflow($mahasiswa);
            
            // TAMBAHAN: Statistik bimbingan untuk monitoring staf
            $data['statistik_bimbingan'] = $this->_get_detailed_jurnal_statistics($proposal_id);
            
        } catch (Exception $e) {
            log_message('error', 'Error in detail_mahasiswa: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat memuat detail mahasiswa.');
            redirect('staf/bimbingan');
        }
        
        $this->load->view('staf/bimbingan/detail', $data);
    }

    /**
     * Export jurnal bimbingan ke PDF
     * FIXED: Query dan data yang benar
     */
    public function export_jurnal($proposal_id) {
        if (!is_numeric($proposal_id)) {
            $this->session->set_flashdata('error', 'ID proposal tidak valid!');
            redirect('staf/bimbingan');
        }
        
        try {
            // Validasi proposal
            $proposal = $this->_get_proposal_data($proposal_id);
            if (!$proposal) {
                $this->session->set_flashdata('error', 'Data proposal tidak ditemukan!');
                redirect('staf/bimbingan');
            }
            
            // ✅ FIXED: Query jurnal dengan data lengkap untuk PDF
            $this->db->select('
                jb.*,
                d.nama as nama_dosen_validator,
                d.nip as nip_dosen_validator
            ');
            $this->db->from('jurnal_bimbingan jb');
            $this->db->join('dosen d', 'jb.validasi_oleh = d.id', 'left');
            $this->db->where('jb.proposal_id', $proposal_id);
            $this->db->order_by('jb.pertemuan_ke', 'ASC');
            $jurnal_list = $this->db->get()->result();
            
            // Generate PDF menggunakan view
            $this->_generate_jurnal_pdf($proposal, $jurnal_list);
            
            // Log aktivitas staf
            $this->_log_staf_aktivitas('export_jurnal', $proposal->mahasiswa_id, $proposal_id, 
                                      'Export jurnal bimbingan mahasiswa ' . $proposal->nama_mahasiswa);
            
        } catch (Exception $e) {
            log_message('error', 'Error in export_jurnal: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export jurnal PDF.');
            redirect('staf/bimbingan');
        }
    }

    /**
     * NEW: Export semua data bimbingan ke Excel XLSX format yang rapi
     * Method utama yang akan dicoba dulu dengan PhpSpreadsheet, fallback ke Excel XML
     */
    public function export_all() {
        try {
            // Ambil semua data mahasiswa bimbingan
            $mahasiswa_data = $this->_get_all_bimbingan_data();
            
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
            log_message('error', 'Error in export_all: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat export data. Error: ' . $e->getMessage());
            redirect('staf/bimbingan');
        }
    }

    /**
     * NEW: Export Excel XLSX menggunakan PhpSpreadsheet (jika tersedia)
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
            $sheet->setTitle('Data Bimbingan');
            
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
            $sheet->setCellValue('A1', 'DATA BIMBINGAN MAHASISWA TUGAS AKHIR');
            $sheet->mergeCells('A1:K1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Subtitle
            $sheet->setCellValue('A2', 'STK Santo Yakobus Merauke');
            $sheet->mergeCells('A2:K2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Generated info
            $sheet->setCellValue('A3', 'Digenerate oleh: ' . $this->session->userdata('nama') . ' | Tanggal: ' . date('d F Y H:i:s'));
            $sheet->mergeCells('A3:K3');
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
                'F5' => 'Dosen Pembimbing',
                'G5' => 'Email Pembimbing',
                'H5' => 'Status Workflow',
                'I5' => 'Tanggal Pengajuan',
                'J5' => 'Email Mahasiswa',
                'K5' => 'No. Telepon'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Apply header style
            $sheet->getStyle('A5:K5')->applyFromArray($headerStyle);
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(40);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(18);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(25);
            $sheet->getColumnDimension('K')->setWidth(15);
            
            // Data rows
            $row = 6;
            foreach ($mahasiswa_data as $index => $mhs) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $mhs->nim);
                $sheet->setCellValue('C' . $row, $mhs->nama_mahasiswa);
                $sheet->setCellValue('D' . $row, $mhs->nama_prodi);
                $sheet->setCellValue('E' . $row, $mhs->judul);
                $sheet->setCellValue('F' . $row, $mhs->nama_pembimbing ?: 'Belum ditetapkan');
                $sheet->setCellValue('G' . $row, $mhs->email_pembimbing ?: '-');
                $sheet->setCellValue('H' . $row, ucfirst(str_replace('_', ' ', $mhs->workflow_status)));
                $sheet->setCellValue('I' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($mhs->tanggal_pengajuan)));
                $sheet->setCellValue('J' . $row, $mhs->email_mahasiswa);
                $sheet->setCellValue('K' . $row, $mhs->nomor_telepon ?: '-');
                
                // Format date column
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                
                $row++;
            }
            
            // Apply borders to all data
            $sheet->getStyle('A5:K' . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
            
            // Summary section
            $row += 2;
            $sheet->setCellValue('A' . $row, 'RINGKASAN DATA');
            $sheet->mergeCells('A' . $row . ':K' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray($headerStyle);
            
            $row++;
            $sheet->setCellValue('A' . $row, 'Total Mahasiswa:');
            $sheet->setCellValue('B' . $row, count($mahasiswa_data));
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
            
            // Set filename and download
            $filename = 'Data_Bimbingan_Mahasiswa_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // Headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            
            // Log aktivitas
            $this->_log_staf_aktivitas('export_all_bimbingan_xlsx', null, null, 
                                      'Export semua data bimbingan format XLSX (' . count($mahasiswa_data) . ' records)');
            
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in PhpSpreadsheet export: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NEW: Export Excel menggunakan Excel XML format (fallback)
     */
    private function _export_xlsx_simple($mahasiswa_data) {
        try {
            $filename = 'Data_Bimbingan_Mahasiswa_' . date('Y-m-d_H-i-s') . '.xls';
            
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
            
            echo '<Style ss:ID="TitleStyle">' . "\n";
            echo '<Font ss:Bold="1" ss:Size="16"/>' . "\n";
            echo '<Alignment ss:Horizontal="Center"/>' . "\n";
            echo '</Style>' . "\n";
            echo '</Styles>' . "\n";
            
            // Worksheet
            echo '<Worksheet ss:Name="Data Bimbingan">' . "\n";
            echo '<Table>' . "\n";
            
            // Column definitions
            echo '<Column ss:Width="40"/>' . "\n";  // No
            echo '<Column ss:Width="100"/>' . "\n"; // NIM
            echo '<Column ss:Width="200"/>' . "\n"; // Nama
            echo '<Column ss:Width="150"/>' . "\n"; // Prodi
            echo '<Column ss:Width="300"/>' . "\n"; // Judul
            echo '<Column ss:Width="150"/>' . "\n"; // Pembimbing
            echo '<Column ss:Width="150"/>' . "\n"; // Email Pembimbing
            echo '<Column ss:Width="120"/>' . "\n"; // Status
            echo '<Column ss:Width="100"/>' . "\n"; // Tanggal
            echo '<Column ss:Width="150"/>' . "\n"; // Email Mahasiswa
            echo '<Column ss:Width="120"/>' . "\n"; // Telepon
            
            // Title row
            echo '<Row>' . "\n";
            echo '<Cell ss:MergeAcross="10" ss:StyleID="TitleStyle">' . "\n";
            echo '<Data ss:Type="String">DATA BIMBINGAN MAHASISWA TUGAS AKHIR - STK SANTO YAKOBUS</Data>' . "\n";
            echo '</Cell>' . "\n";
            echo '</Row>' . "\n";
            
            // Empty row
            echo '<Row></Row>' . "\n";
            
            // Headers
            echo '<Row>' . "\n";
            $headers = ['No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Judul Proposal', 
                       'Dosen Pembimbing', 'Email Pembimbing', 'Status Workflow', 
                       'Tanggal Pengajuan', 'Email Mahasiswa', 'No. Telepon'];
            
            foreach ($headers as $header) {
                echo '<Cell ss:StyleID="HeaderStyle">' . "\n";
                echo '<Data ss:Type="String">' . htmlspecialchars($header) . '</Data>' . "\n";
                echo '</Cell>' . "\n";
            }
            echo '</Row>' . "\n";
            
            // Data rows
            foreach ($mahasiswa_data as $index => $mhs) {
                echo '<Row>' . "\n";
                
                // No
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="Number">' . ($index + 1) . '</Data></Cell>' . "\n";
                
                // NIM
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nim) . '</Data></Cell>' . "\n";
                
                // Nama
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nama_mahasiswa) . '</Data></Cell>' . "\n";
                
                // Prodi
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nama_prodi) . '</Data></Cell>' . "\n";
                
                // Judul
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->judul) . '</Data></Cell>' . "\n";
                
                // Pembimbing
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nama_pembimbing ?: 'Belum ditetapkan') . '</Data></Cell>' . "\n";
                
                // Email Pembimbing
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->email_pembimbing ?: '-') . '</Data></Cell>' . "\n";
                
                // Status
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $mhs->workflow_status))) . '</Data></Cell>' . "\n";
                
                // Tanggal
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . date('d/m/Y', strtotime($mhs->tanggal_pengajuan)) . '</Data></Cell>' . "\n";
                
                // Email Mahasiswa
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->email_mahasiswa) . '</Data></Cell>' . "\n";
                
                // Telepon
                echo '<Cell ss:StyleID="DataStyle"><Data ss:Type="String">' . htmlspecialchars($mhs->nomor_telepon ?: '-') . '</Data></Cell>' . "\n";
                
                echo '</Row>' . "\n";
            }
            
            echo '</Table>' . "\n";
            echo '</Worksheet>' . "\n";
            echo '</Workbook>' . "\n";
            
            // Log aktivitas
            $this->_log_staf_aktivitas('export_all_bimbingan_excel', null, null, 
                                      'Export semua data bimbingan format Excel XML (' . count($mahasiswa_data) . ' records)');
            
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error in Excel XML export: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all bimbingan data untuk export
     */
    private function _get_all_bimbingan_data() {
        $this->db->select('
            pm.id as proposal_id,
            pm.judul,
            pm.workflow_status,
            pm.created_at as tanggal_pengajuan,
            pm.tanggal_penetapan,
            m.nim,
            m.nama as nama_mahasiswa,
            m.email as email_mahasiswa,
            m.nomor_telepon,
            p.nama as nama_prodi,
            d.nama as nama_pembimbing,
            d.email as email_pembimbing
        ');
        
        $this->db->from('proposal_mahasiswa pm');
        $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id', 'inner');
        $this->db->join('prodi p', 'm.prodi_id = p.id', 'inner');
        $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
        $this->db->where('pm.status_pembimbing', '1');
        $this->db->where_in('pm.workflow_status', [
            'bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi'
        ]);
        $this->db->order_by('pm.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Fallback: Export ke CSV jika Excel tidak bisa
     */
    private function _export_to_csv($mahasiswa_data) {
        $filename = 'Data_Bimbingan_Mahasiswa_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'No', 'NIM', 'Nama Mahasiswa', 'Program Studi', 'Judul Proposal',
            'Dosen Pembimbing', 'Email Pembimbing', 'Status Workflow', 
            'Tanggal Pengajuan', 'Email Mahasiswa', 'No. Telepon'
        ]);
        
        foreach ($mahasiswa_data as $index => $mhs) {
            fputcsv($output, [
                $index + 1,
                $mhs->nim,
                $mhs->nama_mahasiswa,
                $mhs->nama_prodi,
                $mhs->judul,
                $mhs->nama_pembimbing ?: 'Belum ditetapkan',
                $mhs->email_pembimbing ?: '-',
                ucfirst(str_replace('_', ' ', $mhs->workflow_status)),
                date('d/m/Y', strtotime($mhs->tanggal_pengajuan)),
                $mhs->email_mahasiswa,
                $mhs->nomor_telepon ?: '-'
            ]);
        }
        
        fclose($output);
        exit;
    }

    // [Sisa method private lainnya tetap sama seperti sebelumnya...]
    // _get_dosen_pembimbing_list, _get_prodi_list, dll.

    /**
     * FIXED: Get dosen pembimbing list (tanpa syntax error)
     */
    private function _get_dosen_pembimbing_list() {
        try {
            $this->db->select('d.id, d.nama');
            $this->db->distinct();
            $this->db->from('dosen d');
            $this->db->join('proposal_mahasiswa pm', 'd.id = pm.dosen_id');
            $this->db->where('pm.status_pembimbing', '1');
            $this->db->where('d.level', '2');
            $this->db->order_by('d.nama', 'ASC');
            
            $query = $this->db->get();
            return $query ? $query->result() : [];
        } catch (Exception $e) {
            log_message('error', 'Error getting dosen list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get prodi list
     */
    private function _get_prodi_list() {
        try {
            $this->db->select('id, nama');
            $this->db->from('prodi');
            $this->db->order_by('nama', 'ASC');
            $query = $this->db->get();
            return $query ? $query->result() : [];
        } catch (Exception $e) {
            log_message('error', 'Error getting prodi list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get proposal data lengkap
     */
    private function _get_proposal_data($proposal_id) {
        try {
            $this->db->select('
                pm.*,
                m.nim,
                m.nama as nama_mahasiswa,
                m.email as email_mahasiswa,
                p.nama as nama_prodi,
                d.nama as nama_pembimbing,
                d.nip as nip_pembimbing,
                d.email as email_pembimbing
            ');
            $this->db->from('proposal_mahasiswa pm');
            $this->db->join('mahasiswa m', 'pm.mahasiswa_id = m.id');
            $this->db->join('prodi p', 'm.prodi_id = p.id');
            $this->db->join('dosen d', 'pm.dosen_id = d.id', 'left');
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
     * FIXED: Get statistik jurnal bimbingan per proposal dengan detail yang benar
     */
    private function _get_jurnal_statistics($proposal_id) {
        try {
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return ['total' => 0, 'tervalidasi' => 0, 'pending' => 0, 'revisi' => 0];
            }
            
            // ✅ FIXED: Query dengan status revisi yang benar
            $this->db->select('
                COUNT(*) as total,
                SUM(CASE WHEN status_validasi = "1" THEN 1 ELSE 0 END) as tervalidasi,
                SUM(CASE WHEN status_validasi = "0" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status_validasi = "2" THEN 1 ELSE 0 END) as revisi
            ');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $result = $query->row();
                return [
                    'total' => $result ? (int)$result->total : 0,
                    'tervalidasi' => $result ? (int)$result->tervalidasi : 0,
                    'pending' => $result ? (int)$result->pending : 0,
                    'revisi' => $result ? (int)$result->revisi : 0
                ];
            }
            
            return ['total' => 0, 'tervalidasi' => 0, 'pending' => 0, 'revisi' => 0];
        } catch (Exception $e) {
            log_message('error', 'Error getting jurnal statistics: ' . $e->getMessage());
            return ['total' => 0, 'tervalidasi' => 0, 'pending' => 0, 'revisi' => 0];
        }
    }

    /**
     * ✅ METHOD BARU (TAMBAHKAN INI):
     * Statistik jurnal bimbingan yang lebih detail untuk monitoring staf
     */
    private function _get_detailed_jurnal_statistics($proposal_id) {
        try {
            if (!$this->db->table_exists('jurnal_bimbingan')) {
                return [
                    'total' => 0, 'tervalidasi' => 0, 'pending' => 0, 'revisi' => 0,
                    'pertemuan_terakhir' => null, 'progress_persen' => 0,
                    'target_tercapai' => false, 'sisa_bimbingan' => 16
                ];
            }
            
            // Query statistik detail
            $this->db->select('
                COUNT(*) as total,
                SUM(CASE WHEN status_validasi = "1" THEN 1 ELSE 0 END) as tervalidasi,
                SUM(CASE WHEN status_validasi = "0" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status_validasi = "2" THEN 1 ELSE 0 END) as revisi,
                MAX(tanggal_bimbingan) as pertemuan_terakhir,
                MAX(pertemuan_ke) as pertemuan_ke_terakhir
            ');
            $this->db->from('jurnal_bimbingan');
            $this->db->where('proposal_id', $proposal_id);
            
            $query = $this->db->get();
            
            if ($query && $query->num_rows() > 0) {
                $result = $query->row();
                $total = (int)$result->total;
                $target_minimal = 16;
                
                return [
                    'total' => $total,
                    'tervalidasi' => (int)$result->tervalidasi,
                    'pending' => (int)$result->pending,
                    'revisi' => (int)$result->revisi,
                    'pertemuan_terakhir' => $result->pertemuan_terakhir,
                    'pertemuan_ke_terakhir' => (int)$result->pertemuan_ke_terakhir,
                    'progress_persen' => $total > 0 ? round(($total / $target_minimal) * 100, 1) : 0,
                    'target_tercapai' => $total >= $target_minimal,
                    'sisa_bimbingan' => max(0, $target_minimal - $total),
                    'target_minimal' => $target_minimal
                ];
            }
            
            return [
                'total' => 0, 'tervalidasi' => 0, 'pending' => 0, 'revisi' => 0,
                'pertemuan_terakhir' => null, 'pertemuan_ke_terakhir' => 0,
                'progress_persen' => 0, 'target_tercapai' => false,
                'sisa_bimbingan' => 16, 'target_minimal' => 16
            ];
        } catch (Exception $e) {
            log_message('error', 'Error getting detailed jurnal statistics: ' . $e->getMessage());
            return [
                'total' => 0, 'tervalidasi' => 0, 'pending' => 0, 'revisi' => 0,
                'pertemuan_terakhir' => null, 'pertemuan_ke_terakhir' => 0,
                'progress_persen' => 0, 'target_tercapai' => false,
                'sisa_bimbingan' => 16, 'target_minimal' => 16
            ];
        }
    }

    /**
     * Get statistik bimbingan keseluruhan
     */
    private function _get_bimbingan_statistics() {
        try {
            $stats = [];
            
            // Total mahasiswa bimbingan
            $this->db->select('COUNT(*) as total');
            $this->db->from('proposal_mahasiswa');
            $this->db->where('status_pembimbing', '1');
            $this->db->where_in('workflow_status', ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi']);
            $result = $this->db->get()->row();
            $stats['total_mahasiswa'] = $result ? (int)$result->total : 0;
            
            // Per workflow status
            $workflow_stages = ['bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi'];
            foreach ($workflow_stages as $stage) {
                $this->db->select('COUNT(*) as total');
                $this->db->from('proposal_mahasiswa');
                $this->db->where('workflow_status', $stage);
                $this->db->where('status_pembimbing', '1');
                $result = $this->db->get()->row();
                $stats[$stage] = $result ? (int)$result->total : 0;
            }
            
            return $stats;
        } catch (Exception $e) {
            log_message('error', 'Error getting bimbingan statistics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get progress workflow mahasiswa
     */
    private function _get_progress_workflow($mahasiswa) {
        $progress = [
            'proposal' => ['completed' => true, 'date' => $mahasiswa->created_at],
            'bimbingan' => ['completed' => false, 'date' => null],
            'seminar_proposal' => ['completed' => false, 'date' => null],
            'penelitian' => ['completed' => false, 'date' => null],
            'seminar_skripsi' => ['completed' => false, 'date' => null],
            'publikasi' => ['completed' => false, 'date' => null],
            'selesai' => ['completed' => false, 'date' => null]
        ];
        
        $current_stages = ['proposal', 'bimbingan', 'seminar_proposal', 'penelitian', 'seminar_skripsi', 'publikasi', 'selesai'];
        $current_index = array_search($mahasiswa->workflow_status, $current_stages);
        
        if ($current_index !== false) {
            for ($i = 0; $i <= $current_index; $i++) {
                $progress[$current_stages[$i]]['completed'] = true;
            }
        }
        
        return $progress;
    }

    /**
     * Generate PDF jurnal bimbingan (SIMPLE VERSION)
     */
    private function _generate_jurnal_pdf($proposal, $jurnal_list) {
        try {
            $data = [
                'proposal' => $proposal,
                'jurnal_bimbingan' => $jurnal_list,
                'generated_by' => $this->session->userdata('nama'),
                'generated_at' => date('d F Y H:i:s')
            ];
            
            $html = $this->load->view('staf/bimbingan/pdf_jurnal', $data, TRUE);
            
            $filename = 'Jurnal_Bimbingan_' . str_replace([' ', ',', '.'], '_', $proposal->nama_mahasiswa) . '_' . date('Y-m-d') . '.html';
            
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $html;
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log aktivitas staf
     */
    private function _log_staf_aktivitas($aktivitas, $mahasiswa_id, $proposal_id, $keterangan, $file_output = null) {
        try {
            if (!$this->db->table_exists('staf_aktivitas')) {
                return false;
            }
            
            $data = [
                'staf_id' => $this->session->userdata('id'),
                'aktivitas' => $aktivitas,
                'mahasiswa_id' => $mahasiswa_id,
                'proposal_id' => $proposal_id,
                'keterangan' => $keterangan,
                'file_output' => $file_output,
                'tanggal_aktivitas' => date('Y-m-d H:i:s')
            ];
            
            return $this->db->insert('staf_aktivitas', $data);
        } catch (Exception $e) {
            log_message('error', 'Error logging staf aktivitas: ' . $e->getMessage());
            return false;
        }
    }
}