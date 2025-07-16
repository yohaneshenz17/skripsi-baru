<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Skripsi extends MY_Controller
{

    public function index()
    {
        return $this->load->view('admin/skripsi');
    }
}

/* End of file Seminar.php */
