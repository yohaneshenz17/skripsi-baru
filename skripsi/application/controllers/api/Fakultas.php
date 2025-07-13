<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Fakultas extends REST_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Fakultas_model', 'model');
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

    public function update_post($id)
    {
        $response = $this->model->update($this->input->post(), $id);
        return $this->response($response);
    }

    public function destroy_post($id = null)
    {
        $response = $this->model->destroy($id);
        return $this->response($response);
    }

}

/* End of file Prodi.php */
