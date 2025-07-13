<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Crud extends CI_Controller
{

    public function getData($table)
    {
        $data = $this->db->get_where($table, array('mahasiswa_id' => $this->input->post('mahasiswa_id')))->result();
        echo json_encode($data);
    }

    public function getAllData($table)
    {
        $data = $this->db->get_where($table)->result();
        echo json_encode($data);
    }
}

/* End of file Crud.php */
