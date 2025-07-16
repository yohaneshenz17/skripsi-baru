<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Seminar extends MY_Controller {

    public function index()
    {
        return $this->load->view('mahasiswa/seminar');
    }

    public function detail($id = null)
    {
    	if ($id) {
    		return $this->load->view('mahasiswa/seminar_detail', ['seminar_id' => $id]);
    	}
    	redirect(base_url('mahasiswa/seminar'));
    }

}

/* End of file Seminar.php */
