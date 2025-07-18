<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengaturan extends MY_Controller
{

	public function index()
	{
		$data['dataEmail'] = $this->db->get('email_sender')->result();
		return $this->load->view('admin/pengaturan', $data);
	}

	public function atur_send_email()
	{
		$id = $this->input->post('id');
		$smtp_host = $this->input->post('smtp_host');
		$smtp_port = $this->input->post('smtp_port');
		$smtp_user = $this->input->post('smtp_user');
		$smtp_password = $this->input->post('smtp_password');

		$dataUpdate = array(
			'smtp_host' => $smtp_host,
			'smtp_port' => $smtp_port,
			'email' => $smtp_user,
			'password' => $smtp_password,
		);

		$this->db->where('id', $id);
		if ($this->db->update('email_sender', $dataUpdate)) {
			redirect(base_url('admin/pengaturan'), 'refresh');
		}
	}
}

/* End of file Pengaturan.php */
/* Location: ./application/controllers/admin/Pengaturan.php */