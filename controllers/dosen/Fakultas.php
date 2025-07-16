<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fakultas extends MY_Controller {

	public function index()
	{
		return $this->load->view('dosen/fakultas');
	}

}

/* End of file Prodi.php */
/* Location: ./application/controllers/admin/Prodi.php */