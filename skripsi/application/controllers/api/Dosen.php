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

    // TAMBAH METHOD GET untuk JavaScript call() - FIX ERROR "index_get"
    public function index_get()
    {
        $response = $this->model->get();
        return $this->response($response);
    }

    // TAMBAH METHOD GET untuk details - FIX ERROR "details_get"  
    public function details_get($id = null)
    {
        $response = $this->model->details($id);
        return $this->response($response);
    }

    // TAMBAH METHOD GET untuk getById jika diperlukan
    public function get_byid_get()
    {
        // Untuk GET method, gunakan parameter dari URL atau query string
        $id = $this->input->get('id');
        if (!$id) {
            return $this->response([
                'error' => true,
                'message' => 'ID parameter required'
            ], 400);
        }
        
        // Set manual untuk model yang menggunakan post() 
        $_POST['id'] = $id;
        $response = $this->model->getById();
        return $this->response($response);
    }

    // METHOD POST YANG SUDAH ADA - TETAP DIPERTAHANKAN UNTUK COMPATIBILITY
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