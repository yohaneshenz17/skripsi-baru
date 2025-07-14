<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Prodi_model extends CI_Model {

    protected $table = "prodi";

    public function get()
    {
        $this->db->select("*");
        $prodi = $this->db->get($this->table)->result_array();

        $hasil['error'] = false;
        $hasil['message'] = ($prodi) ? "data berhasil ditemukan" : "data tidak tersedia";
        $hasil['data'] = $prodi;

        foreach ($prodi as $key => $item) {
            $hasil['data'][$key]['kaprodi'] = $this->db->get_where('dosen', ['dosen.id' => $item['dosen_id']])->row_array();
            $hasil['data'][$key]['fakultas'] = $this->db->get_where('fakultas', ['fakultas.id' => $item['fakultas_id']])->row_array();
        }

        return $hasil;
    }

    public function create($input)
    {
        $data = [
            'kode' => $input['kode'],
            'nama' => $input['nama'],
            'dosen_id' => $input['dosen_id'],
            'fakultas_id' => $input['fakultas_id'],
        ];

        $validate = $this->app->validate($data);

        if ($validate === true) {
            $cek = $this->db->get_where($this->table, ['prodi.kode' => $data['kode']])->num_rows();
            if ($cek > 0) {
                $hasil = [
                    'error' => true,
                    'message' => "kode sudah digunakan"
                ];
            } else {
                $this->db->insert($this->table, $data);
                $hasil = [
                    'error' => false,
                    'message' => "data berhasil ditambahkan",
                    'data' => $this->db->insert_id()
                ];
            }
        } else {
            $hasil = $validate;
        }

        return $hasil;
    }

    public function update($input, $id)
    {
        $data = [
            'kode' => $input['kode'],
            'nama' => $input['nama'],
            'dosen_id' => $input['dosen_id'],
            'fakultas_id' => $input['fakultas_id']
        ];

        $kondisi = ['prodi.id' => $id];
        $cek = $this->db->get_where($this->table, $kondisi)->num_rows();
        
        if ($cek > 0) {
            $validate = $this->app->validate($data);
    
            if ($validate === true) {
                $cek = $this->db->get_where($this->table, ['prodi.id <>' => $id,'prodi.kode' => $data['kode']])->num_rows();
                if ($cek > 0) {
                    $hasil = [
                        'error' => true,
                        'message' => "kode sudah digunakan"
                    ];
                } else {
                    $this->db->update($this->table, $data, $kondisi);
                    $hasil = [
                        'error' => false,
                        'message' => "data berhasil diedit"
                    ];
                }
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
        $kondisi = ['prodi.id' => $id];
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
