<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dosen extends MY_Controller
{

	public function index()
	{
		return $this->load->view('admin/dosen');
	}

	public function lihat_selengkapnya($id)
	{
		$data['data_bimbingan'] = $this->db->get_where('bimbingan_dosen_v', array('id' => $id))->result();
		$data['data_penguji'] = $this->db->get_where('penguji_dosen_v', array('id' => $id))->result();
		$this->load->view('admin/lihat_selengkapnya', $data);
	}
}

/* End of file Dosen.php */
/* Location: ./application/controllers/admin/Dosen.php */