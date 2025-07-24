<?php
// File: application/libraries/Pdf.php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH . 'third_party/tcpdf/tcpdf.php');

class Pdf extends TCPDF {
    
    public $filename = '';
    
    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
        
        // Set default properties
        $this->SetCreator('SIM-TA STK St. Yakobus');
        $this->SetAuthor('STK St. Yakobus');
        $this->SetTitle('Dokumen SIM-TA');
        $this->SetSubject('Sistem Informasi Manajemen Tugas Akhir');
        $this->SetKeywords('TCPDF, PDF, STK, Tugas Akhir');
        
        // Set default header/footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        
        // Set margins
        $this->SetMargins(20, 20, 20);
        $this->SetAutoPageBreak(TRUE, 20);
        
        // Set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Set font
        $this->SetFont('times', '', 12);
        
        $this->filename = 'document_' . date('Y-m-d_H-i-s') . '.pdf';
    }
    
    /**
     * Load HTML content
     */
    public function load_html($html) {
        $this->AddPage();
        $this->writeHTML($html, true, false, true, false, '');
    }
    
    /**
     * Render PDF
     */
    public function render() {
        // PDF is ready to be output
        return true;
    }
    
    /**
     * Stream PDF to browser
     */
    public function stream($filename = null, $options = array()) {
        if ($filename) {
            $this->filename = $filename;
        }
        
        $attachment = isset($options['Attachment']) ? $options['Attachment'] : false;
        $dest = $attachment ? 'D' : 'I';
        
        $this->Output($this->filename, $dest);
    }
    
    /**
     * Save PDF to file
     */
    public function save($path) {
        $this->Output($path, 'F');
        return file_exists($path);
    }
    
    /**
     * Get PDF as string
     */
    public function output_string() {
        return $this->Output('', 'S');
    }
    
    /**
     * Custom header for STK documents
     */
    public function setCustomHeader($title = '', $subtitle = '') {
        $this->setPrintHeader(true);
        $this->setHeaderFont(Array('times', 'B', 14));
        $this->setHeaderData('', 0, $title, $subtitle);
    }
    
    /**
     * Custom footer for STK documents
     */
    public function setCustomFooter($text = '') {
        $this->setPrintFooter(true);
        $this->setFooterFont(Array('times', '', 10));
        $this->setFooterData(array(0,0,0), array(0,0,0));
        
        if ($text) {
            $this->setFooterMargin(15);
        }
    }
}