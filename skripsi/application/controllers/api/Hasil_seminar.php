<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Hasil_seminar extends REST_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Hasil_seminar_model', 'model');
	}

	public function edit_post($seminar_id = null)
	{
		$response = $this->model->edit($this->input->post(), $seminar_id);
		return $this->response($response);
	}

}

/* End of file Hasil_seminar.php */
/* Location: ./application/controllers/api/Hasil_seminar.php */