<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{

    public function login($input)
    {
        $email = $input['email'];
        $nip = $input['nip'];

        if (empty($email)) {
            $hasil = [
                'error' => true,
                'message' => "email harus diisi"
            ];
            goto output;
        }
        if (empty($nip)) {
            $hasil = [
                'error' => true,
                'message' => "Password harus diisi"
            ];
            goto output;
        }

        $dosen = $this->db->get_where('dosen', ['email' => $email]);

        if ($dosen->num_rows() > 0) {
            foreach ($dosen->result_array() as $key => $item) {
                if ($item['nip'] == $nip) {
                    $hasil = [
                        'error' => false,
                        'message' => "berhasil login",
                        'data' => $item
                    ];
                    goto output;
                }
            }
            $hasil = [
                'error' => true,
                'message' => "Password salah"
            ];
            goto output;
        } else {
            $hasil = [
                'error' => true,
                'message' => "email tidak ditemukan"
            ];
            goto output;
        }

        output:
        return $hasil;
    }
}

/* End of file Auth_model.php */
