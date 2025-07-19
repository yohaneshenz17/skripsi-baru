<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// Prepare data untuk template (menggunakan array() bukan [])
$template_data = array(
    'title' => 'Seminar Skripsi',
    'content' => '',
    'script' => ''
);

// Load content view sebagai string
ob_start();
include(APPPATH . 'views/dosen/seminar_skripsi_content.php');
$template_data['content'] = ob_get_clean();

// Load template
$this->load->view('template/dosen', $template_data);
?>