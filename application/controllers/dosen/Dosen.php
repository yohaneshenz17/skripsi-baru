<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dosen extends MY_Controller
{

	public function index()
	{
		return $this->load->view('dosen/dosen');
	}
}

/* End of file Dosen.php */
/* Location: ./application/controllers/admin/Dosen.php */