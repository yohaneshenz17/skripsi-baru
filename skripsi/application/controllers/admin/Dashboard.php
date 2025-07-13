<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function index()
    {
        return $this->load->view('admin/dashboard');
    }

}

/* End of file Dashboard.php */
