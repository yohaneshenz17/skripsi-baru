<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PDF Generator Library untuk CodeIgniter
 * Menggunakan DOMPDF sebagai engine
 */

// Load Composer Autoload jika tersedia
if (file_exists(FCPATH . 'vendor/autoload.php')) {
    require_once FCPATH . 'vendor/autoload.php';
}

// Jika DOMPDF tidak tersedia via Composer, gunakan implementasi sederhana
if (!class_exists('Dompdf\Dompdf')) {
    
    class Pdf_generator {
        
        protected $CI;
        
        public function __construct() {
            $this->CI =& get_instance();
        }
        
        /**
         * Generate PDF menggunakan implementasi sederhana
         * Akan menggunakan library TCPDF jika tersedia, atau fallback ke HTML output
         */
        public function generate($html, $filename = 'document.pdf', $stream = true, $paper = 'A4', $orientation = 'portrait') {
            
            // Coba load TCPDF jika tersedia
            if (file_exists(APPPATH . 'third_party/tcpdf/tcpdf.php')) {
                $this->generateWithTCPDF($html, $filename, $stream, $paper, $orientation);
                return;
            }
            
            // Fallback: Output HTML yang diformat untuk print
            $this->generateSimpleHTML($html, $filename, $stream);
        }
        
        /**
         * Generate PDF menggunakan TCPDF
         */
        private function generateWithTCPDF($html, $filename, $stream, $paper, $orientation) {
            require_once APPPATH . 'third_party/tcpdf/tcpdf.php';
            
            // Create new PDF document
            $pdf = new TCPDF($orientation, PDF_UNIT, $paper, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('STK St. Yakobus Merauke');
            $pdf->SetAuthor('Sistem Informasi Manajemen Tugas Akhir');
            $pdf->SetTitle($filename);
            
            // Set margins
            $pdf->SetMargins(15, 27, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 25);
            
            // Add a page
            $pdf->AddPage();
            
            // Output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Close and output PDF document
            if ($stream) {
                $pdf->Output($filename, 'I'); // Display in browser
            } else {
                $pdf->Output($filename, 'D'); // Force download
            }
        }
        
        /**
         * Generate Simple HTML output (fallback)
         */
        private function generateSimpleHTML($html, $filename, $stream) {
            // Set proper headers for PDF-like display
            if ($stream) {
                header('Content-Type: text/html; charset=utf-8');
                header('Content-Disposition: inline; filename="' . $filename . '.html"');
            } else {
                header('Content-Type: text/html; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '.html"');
            }
            
            // Enhanced HTML dengan CSS untuk print
            $printableHTML = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>' . htmlspecialchars($filename) . '</title>
                <style>
                    @media print {
                        .no-print { display: none !important; }
                        body { margin: 0; }
                    }
                    body { 
                        font-family: "Times New Roman", Times, serif; 
                        font-size: 12pt; 
                        line-height: 1.4;
                        margin: 20px;
                        color: #000;
                    }
                    .print-header {
                        text-align: center;
                        border-bottom: 2px solid #000;
                        margin-bottom: 20px;
                        padding-bottom: 15px;
                    }
                    .print-footer {
                        margin-top: 30px;
                        padding-top: 15px;
                        border-top: 1px solid #ccc;
                        text-align: center;
                        font-size: 10pt;
                        color: #666;
                    }
                    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
                    table, th, td { border: 1px solid #000; }
                    th, td { padding: 8px; text-align: left; }
                    th { background-color: #f0f0f0; font-weight: bold; }
                    .signature-area { margin-top: 40px; }
                    .signature-box { 
                        display: inline-block; 
                        width: 200px; 
                        text-align: center; 
                        margin: 0 20px;
                        vertical-align: top;
                    }
                    .signature-line { 
                        border-bottom: 1px solid #000; 
                        height: 60px; 
                        margin-bottom: 5px; 
                    }
                    h1, h2, h3 { color: #000; }
                    .no-print-btn {
                        position: fixed;
                        top: 10px;
                        right: 10px;
                        background: #007bff;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        cursor: pointer;
                        border-radius: 4px;
                        z-index: 1000;
                    }
                </style>
            </head>
            <body>
                <button class="no-print no-print-btn" onclick="window.print()">Print / Save as PDF</button>
                ' . $html . '
            </body>
            </html>';
            
            echo $printableHTML;
            exit;
        }
    }
    
} else {
    
    // Jika DOMPDF tersedia
    class Pdf_generator {
        
        protected $CI;
        
        public function __construct() {
            $this->CI =& get_instance();
        }
        
        public function generate($html, $filename = 'document.pdf', $stream = true, $paper = 'A4', $orientation = 'portrait') {
            
            // Initialize DOMPDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->set_option('enable_php', false);
            $dompdf->set_option('enable_remote', true);
            $dompdf->set_option('enable_html5_parser', true);
            
            // Load HTML
            $dompdf->loadHtml($html);
            
            // Set paper size and orientation
            $dompdf->setPaper($paper, $orientation);
            
            // Render the HTML as PDF
            $dompdf->render();
            
            // Output PDF
            if ($stream) {
                $dompdf->stream($filename, array("Attachment" => false));
            } else {
                $dompdf->stream($filename, array("Attachment" => true));
            }
        }
    }
}

/* End of file Pdf_generator.php */