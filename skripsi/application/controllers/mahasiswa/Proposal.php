<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposal extends MY_Controller {

    public function index()
    {
        return $this->load->view('mahasiswa/proposal');
    }

}

/* End of file Proposal.php */
