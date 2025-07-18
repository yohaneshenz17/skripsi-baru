<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Pengaturan extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Pengaturan_model', 'model');
	}

	public function index_post()
	{
		$response = $this->model->index();
		return $this->response($response);
	}

	public function edit_post()
	{
		$response = $this->model->edit($this->input->post());
		return $this->response($response);
	}

}

/* End of file Pengaturan.php */
/* Location: ./application/controllers/api/Pengaturan.php */