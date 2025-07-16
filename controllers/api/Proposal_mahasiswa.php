<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Proposal_mahasiswa extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Proposal_mahasiswa_model', 'model');
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

    public function agree_post($id = null)
    {
        $response = $this->model->agree($id, $this->input->post('deadline_skripsi'));
        return $this->response($response);
    }

    public function disagree_post($id = null)
    {
        $response = $this->model->disagree($id);
        return $this->response($response);
    }
}

/* End of file Proposal_mahasiswa.php */
