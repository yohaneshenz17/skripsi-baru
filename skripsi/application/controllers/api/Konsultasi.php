<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Konsultasi extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Konsultasi_model', 'model');
    }

    public function index_post()
    {
        $response = $this->model->get($this->input->post());
        return $this->response($response);
    }

    public function create_post()
    {
        $response = $this->model->create($this->input->post());
        return $this->response($response);
    }

    public function update_post($id = null)
    {
        $response = $this->model->update($this->input->post(), $id);
        return $this->response($response);
    }

    public function destroy_post($id = null)
    {
        $response = $this->model->destroy($id);
        return $this->response($response);
    }

    public function uploadsktim_post($id = null)
    {
        $response = $this->model->uploadsktim($this->input->post(), $id);
        return $this->response($response);
    }

    public function agree_post($id = null)
    {
        $response = $this->model->agree($this->input->post(), $id);
        return $this->response($response);
    }

    public function disagree_post($id = null)
    {
        $response = $this->model->disagree($this->input->post(), $id);
    }
}

/* End of file Konsultasi.php */
