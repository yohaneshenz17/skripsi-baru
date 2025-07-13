<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Email_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        // Memuat library email, CodeIgniter akan otomatis menggunakan application/config/email.php
        $this->load->library('email');
    }

    function send($subject, $to_email, $message)
    {
        // Menggunakan nama pengirim yang lebih deskriptif
        $from_name = 'Sistem Informasi Managemen Tugas Akhir'; 
        // Mengambil email pengirim dari file konfigurasi
        $from_email = $this->config->item('smtp_user');

        $this->email->from($from_email, $from_name);
        $this->email->to($to_email);
        $this->email->subject($subject);
        $this->email->message($message);

        // Kirim email dan tangani hasilnya
        if ($this->email->send()) {
            return true; // Berhasil
        } else {
            // Jika gagal, kembalikan pesan error detail untuk debugging
            // Pesan ini yang perlu Anda kirimkan ke saya jika masih gagal
            return $this->email->print_debugger(array('headers'));
        }
    }
}

/* End of file Email_model.php */