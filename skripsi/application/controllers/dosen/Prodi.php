<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prodi extends MY_Controller {

	public function index()
	{
		return $this->load->view('dosen/prodi');
	}

}

/* End of file Prodi.php */
/* Location: ./application/controllers/admin/Prodi.php */