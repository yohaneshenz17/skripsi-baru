<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Hasil_penelitian extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Hasil_penelitian_model', 'model');
	}

	public function edit_post($id = null)
	{
		$response = $this->model->edit($this->input->post(), $id);
		return $this->response($response);
	}

}

/* End of file Hasil_penelitian.php */
/* Location: ./application/controllers/api/Hasil_penelitian.php */