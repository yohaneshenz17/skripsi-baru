<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Prodi extends REST_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Prodi_model', 'model');
    }

    // TAMBAH METHOD GET untuk JavaScript call()
    public function index_get()
    {
        $response = $this->model->get();
        return $this->response($response);
    }

    // Method POST tetap ada untuk compatibility
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