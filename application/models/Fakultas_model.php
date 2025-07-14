<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Fakultas_model extends CI_Model {

    protected $table = "fakultas";

    public function get()
    {
        $this->db->select("*");
        $fakultas = $this->db->get($this->table)->result_array();

        $hasil['error'] = false;
        $hasil['message'] = ($fakultas) ? "data berhasil ditemukan" : "data tidak tersedia";
        $hasil['data'] = $fakultas;

        return $hasil;
    }

    public function create($input)
    {
        $data = [
            'nama' => $input['nama'],
            'dekan' => $input['dekan'],
        ];

        $validate = $this->app->validate($data);

        if ($validate === true) {
            $this->db->insert($this->table, $data);
            $hasil = [
                'error' => false,
                'message' => "data berhasil ditambahkan",
                'data' => $this->db->insert_id()
            ];
        } else {
            $hasil = $validate;
        }

        return $hasil;
    }

    public function update($input, $id)
    {
        $data = [
            'nama' => $input['nama'],
            'dekan' => $input['dekan'],
        ];

        $kondisi = ['fakultas.id' => $id];
        $cek = $this->db->get_where($this->table, $kondisi)->num_rows();
        
        if ($cek > 0) {
            $validate = $this->app->validate($data);
    
            if ($validate === true) {
                $this->db->update($this->table, $data, $kondisi);
                $hasil = [
                    'error' => false,
                    'message' => "data berhasil diedit"
                ];
            } else {
                $hasil = $validate;
            }
        } else {
            $hasil = [
                'error' => true,
                'message' => "data tidak ditemukan"
            ];
        }

        return $hasil;
    }

    public function destroy($id)
    {
        $kondisi = ['fakultas.id' => $id];
        $cek = $this->db->get_where($this->table, $kondisi);

        if ($cek > 0) {
            $this->db->where($kondisi);
            $this->db->delete($this->table);
            $hasil = [
                'error' => false,
                'message' => "data berhasil dihapus"
            ];
        } else {
            $hasil = [
                'error' => true,
                'message' => "data tidak ditemukan"
            ];
        }

        return $hasil;
    }

}

/* End of file Prodi_model.php */
