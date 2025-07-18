<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_email extends CI_Controller {
    
    public function index() {
        $this->load->library('email');
        
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
        $this->email->from('stkyakobus@gmail.com', 'Test SIM');
        $this->email->to('your-test-email@gmail.com'); // GANTI dengan email Anda
        $this->email->subject('Test Email Configuration');
        $this->email->message('Email configuration berhasil! Deploy Phase 1-2 ready.');
        
        if ($this->email->send()) {
            echo "✅ Email berhasil dikirim!";
        } else {
            echo "❌ Email gagal: " . $this->email->print_debugger();
        }
    }
}