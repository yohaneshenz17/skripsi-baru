<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penelitian extends MY_Controller {

	public function index()
	{
		return $this->load->view('mahasiswa/penelitian');
	}

	public function detail($id = null)
	{
		if ($id) {
			return $this->load->view('mahasiswa/penelitian_detail', ['penelitian_id' => $id]);
		} else {
			return redirect(base_url('mahasiswa/penelitian'));
		}
	}

}

/* End of file Penelitian.php */
/* Location: ./application/controllers/mahasiswa/Penelitian.php */