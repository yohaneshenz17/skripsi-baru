<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'third_party/tcpdf/tcpdf.php');

/**
 * PDF Library using TCPDF
 * Wrapper class untuk TCPDF di CodeIgniter
 */
class Pdf extends TCPDF {
    
    public $filename = '';
    
    public function __construct() {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('STK Santo Yakobus');
        $this->SetTitle('Dokumen SIM-TA');
        $this->SetSubject('Sistem Informasi Manajemen Tugas Akhir');
        
        // Set default header and footer fonts
        $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // Set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Remove default header/footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
    }
    
    /**
     * Load HTML content
     */
    public function load_html($html) {
        $this->AddPage();
        $this->writeHTML($html, true, false, true, false, '');
    }
    
    /**
     * Output PDF to browser
     */
    public function stream($filename = 'document.pdf', $options = array()) {
        $this->Output($filename, 'I');
    }
    
    /**
     * Download PDF
     */
    public function download($filename = 'document.pdf') {
        $this->Output($filename, 'D');
    }
    
    /**
     * Save PDF to file
     */
    public function save($filepath) {
        $this->Output($filepath, 'F');
    }
}