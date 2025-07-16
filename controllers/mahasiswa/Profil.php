<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profil extends MY_Controller {

	public function index()
	{
		return $this->load->view('mahasiswa/profil');
	}

}

/* End of file Profil.php */
/* Location: ./application/controllers/mahasiswa/Profil.php */