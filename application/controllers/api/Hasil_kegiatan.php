<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Hasil_kegiatan extends \Restserver\Libraries\REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Hasil_kegiatan_model', 'model');
	}

	public function index_post()
	{
		$response = $this->model->index($this->input->post());
		return $this->response($response);
	}

	public function tambah_post()
	{
		$response = $this->model->tambah($this->input->post());
		return $this->response($response);
	}

	public function hapus_post($id = null)
	{
		$response = $this->model->hapus($id, $this->input->post());
		return $this->response($response);
	}
}

/* End of file Hasil_kegiatan.php */
/* Location: ./application/controllers/api/Hasil_kegiatan.php */