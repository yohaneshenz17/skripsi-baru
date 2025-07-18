<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penelitian extends MY_Controller {

	public function index()
	{
		return $this->load->view('admin/penelitian');
	}

	public function detail($id = null)
	{
		if ($id) {
			return $this->load->view('admin/penelitian_detail', ['penelitian_id' => $id]);
		}
		return redirect(base_url('admin/penelitian'));
	}

}

/* End of file Penelitian.php */
/* Location: ./application/controllers/admin/Penelitian.php */