<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hasil_kegiatan_model extends CI_Model
{

	protected $table = 'hasil_kegiatan';

	public function index($input)
	{
		if ($input['mahasiswa_id']) {
			$this->db->where(['mahasiswa_id' => $input['mahasiswa_id']]);
		}
		$hasil_kegiatan = $this->db->get('hasil_kegiatan_v')->result_array();

		$hasil = [
			'error' => false,
			'message' => $hasil_kegiatan ? "data berhasil ditemukan" : "data tidak tersedia",
			'data' => $hasil_kegiatan
		];

		return $hasil;
	}

	public function tambah($input)
	{
		$data = [
			'kegiatan' => $input['kegiatan'],
			'status' => $input['status'],
			'file_kegiatan' => $input['file_kegiatan'],
			'mahasiswa_id' => $this->session->userdata('id')
		];

		$validation = $this->app->validate($data);

		if ($validation === true) {

			$where = array(
				'mahasiswa_id' => $data['mahasiswa_id'],
			);
			$file_nama = date('Ymdhis') . '.pdf';
			$file = explode(';base64,', $data['file_kegiatan'])[1];
			file_put_contents(FCPATH . 'cdn/vendor/hasil_kegiatan/kegiatan/' . $file_nama, base64_decode($file));
			$data['file_kegiatan'] = $file_nama;

			$this->db->insert($this->table, $data);
			$hasil = [
				'error' => false,
				'message' => "data berhasil ditambah",
				'data_id' => $this->db->insert_id()
			];
		} else {
			$hasil = $validation;
		}

		return $hasil;
	}

	public function hapus($id = null, $data)
	{
		$hasil_kegiatan = $this->db->get_where($this->table, ['id' => $id])->row_array();

		if ($hasil_kegiatan) {
			unlink(FCPATH . '/cdn/vendor/hasil_kegiatan/hasil/' . $data['file']);
			unlink(FCPATH . '/cdn/vendor/hasil_kegiatan/kegiatan/' . $data['file_kegiatan']);

			$this->db->delete($this->table, ['id' => $id]);

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

/* End of file Hasil_kegiatan_model.php */
/* Location: ./application/models/Hasil_kegiatan_model.php */