<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends MY_Controller {

	public function index()
	{
		return $this->load->view('admin/report');
	}

}

/* End of file Report.php */
/* Location: ./application/controllers/admin/Report.php */