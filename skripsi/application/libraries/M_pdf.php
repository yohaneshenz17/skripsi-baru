// application/libraries/M_pdf.php
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'third_party/mpdf/vendor/autoload.php';

class M_pdf {
    public $pdf;

    public function __construct($config = array()) {
        $defaultConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16
        ];
        
        $config = array_merge($defaultConfig, $config);
        $this->pdf = new \Mpdf\Mpdf($config);
    }
}
?>