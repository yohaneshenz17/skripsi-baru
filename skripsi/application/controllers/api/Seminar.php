<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Seminar extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Seminar_model', 'model');
	}

	public function index_post()
	{
		$response = $this->model->index($this->input->post());
		return $this->response($response);
	}

	public function create_post()
	{
		$response = $this->model->create($this->input->post());
		return $this->response($response);
	}

	public function destroy_post($id = null)
	{
		$response = $this->model->destroy($id);
		return $this->response($response);
	}

	public function details_post($id = null)
	{
		$response = $this->model->details($id);
		return $this->response($response);
	}
}

/* End of file Seminar.php */
/* Location: ./application/controllers/api/Seminar.php */