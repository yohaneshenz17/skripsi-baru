<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Skripsi extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Skripsi_model', 'model');
    }

    public function index_post()
    {
        $response = $this->model->index($this->input->post());
        return $this->response($response);
    }

    public function admin_index_post()
    {
        $response = $this->model->admin_index($this->input->post());
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

    public function update_post($id = null)
    {
        $response = $this->model->update($this->input->post(), $id);
        return $this->response($response);
    }

    public function agree_post($id = null)
    {
        $response = $this->model->agree($id);
        return $this->response($response);
    }

    public function disagree_post($id = null)
    {
        $response = $this->model->disagree($id);
        return $this->response($response);
    }
}

/* End of file Seminar.php */
/* Location: ./application/controllers/api/Seminar.php */