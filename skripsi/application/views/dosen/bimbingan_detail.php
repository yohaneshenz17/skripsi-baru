<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

// FIXED: Gunakan pattern yang sama dengan bimbingan.php
$template_data = array(
    'title' => isset($title) ? $title : 'Detail Bimbingan Mahasiswa',
    'content' => '',
    'script' => ''
);

// Load content view sebagai string menggunakan ob_start
ob_start();
include(APPPATH . 'views/dosen/bimbingan_detail_content.php');
$template_data['content'] = ob_get_clean();

// Load template dengan format array
$this->load->view('template/dosen', $template_data);
?>