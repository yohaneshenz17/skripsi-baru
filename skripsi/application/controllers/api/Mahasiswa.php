<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Mahasiswa extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mahasiswa_model', 'model');
    }

    public function index_post()
    {
        $response = $this->model->get($this->input->post());
        return $this->response($response);
    }

    // TAMBAH METHOD GET untuk JavaScript call()
    public function index_get()
    {
        $response = $this->model->get([]);
        return $this->response($response);
    }

    public function create_post()
    {
        // [PERBAIKAN] Validasi nomor telepon ditambahkan di sini
        $nomor_telepon = $this->post('nomor_telepon');
        $nomor_telepon_orang_dekat = $this->post('nomor_telepon_orang_dekat');

        if ($nomor_telepon && !empty($nomor_telepon) && $nomor_telepon == $nomor_telepon_orang_dekat) {
            // Kirim respons error jika nomor HP sama
            $this->response([
                'error' => true,
                'message' => 'Nomor HP pribadi dan Nomor HP orang dekat tidak boleh sama.'
            ], REST_Controller::HTTP_BAD_REQUEST); // HTTP 400
            return; // Hentikan eksekusi
        }
        
        // Jika validasi lolos, lanjutkan proses ke model
        $response = $this->model->create($this->post());
        return $this->response($response);
    }

    public function update_post($id = null)
    {
        $response = $this->model->update($this->input->post(), $id);
        return $this->response($response);
    }

    public function update2_post($id = null)
    {
        $response = $this->model->update2($this->input->post(), $id);
        return $this->response($response);
    }

    public function destroy_post($id = null)
    {
        $response = $this->model->destroy($id);
        return $this->response($response);
    }

    // METHOD POST (sudah ada)
    public function detail_post($id = null)
    {
        $response = $this->model->detail($id);
        return $this->response($response);
    }

    // TAMBAH METHOD GET untuk JavaScript call('api/mahasiswa/detail/ID')
    public function detail_get($id = null)
    {
        $response = $this->model->detail($id);
        return $this->response($response);
    }

    public function search_post()
    {
        $response = $this->model->search($this->input->post());
        return $this->response($response);
    }

    public function dataperprodi_post()
    {
        $response = $this->model->dataperprodi();
        return $this->response($response);
    }

    public function verifikasi_post($id = null)
    {
        $response = $this->model->verifikasi($this->input->post(), $id);
        return $this->response($response);
    }
}

/* End of file Mahasiswa.php */