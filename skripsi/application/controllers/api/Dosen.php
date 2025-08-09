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

    // ✅ UPDATE: METHOD GET untuk details dengan JOIN ke tabel prodi
    public function details_get($id = null)
    {
        header('Content-Type: application/json');
        
        try {
            if (!$id) {
                echo json_encode([
                    'error' => true,
                    'message' => 'ID dosen tidak ditemukan'
                ]);
                return;
            }

            // ✅ TAMBAHAN: Query dengan JOIN ke tabel prodi dan include field baru
            $this->db->select('
                d.id,
                d.nip,
                d.nama,
                d.nomor_telepon,
                d.email,
                d.level,
                d.foto,
                d.prodi_id,
                d.bidang_keilmuan,
                p.nama as nama_prodi,
                p.kode as kode_prodi
            ');
            $this->db->from('dosen d');
            $this->db->join('prodi p', 'd.prodi_id = p.id', 'left');
            $this->db->where('d.id', $id);
            
            $query = $this->db->get();
            
            if ($query->num_rows() > 0) {
                $data = $query->row();
                
                // ✅ TAMBAHAN: Format response dengan field baru
                $response_data = [
                    'id' => $data->id,
                    'nip' => $data->nip,
                    'nama' => $data->nama,
                    'nomor_telepon' => $data->nomor_telepon,
                    'email' => $data->email,
                    'level' => $data->level,
                    'foto' => $data->foto,
                    'prodi_id' => $data->prodi_id,
                    'bidang_keilmuan' => $data->bidang_keilmuan,
                    'nama_prodi' => $data->nama_prodi,
                    'kode_prodi' => $data->kode_prodi
                ];
                
                echo json_encode([
                    'error' => false,
                    'message' => 'Data berhasil diambil',
                    'data' => $response_data
                ]);
            } else {
                echo json_encode([
                    'error' => true,
                    'message' => 'Data dosen tidak ditemukan'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
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