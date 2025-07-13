<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Dosen extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Dosen_model', 'model');
    }

    public function get_byid_post()
    {
        $response = $this->model->getById();
        echo json_encode($response);
    }

    public function index_post()
    {
        $response = $this->model->get();
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

    public function details_post($id = null)
    {
        $response = $this->model->details($id);
        return $this->response($response);
    }
}

/* End of file Dosen.php */
