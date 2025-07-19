<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Mahasiswa extends MY_Controller {

    public function index()
    {
        return $this->load->view('dosen/mahasiswa');
    }

    public function detail($id = null)
    {
        if (empty($id)) {
            return redirect(base_url('dosen/dashboard'));
        }
        return $this->load->view('dosen/mahasiswa_detail', ['mahasiswa_id' => $id]);
    }

}

/* End of file Mahasiswa.php */
