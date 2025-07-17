<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan_model extends CI_Model {

	public function __construct()
	{
		parent::__construct();
		$this->aplikasi = json_decode(file_get_contents(FCPATH . 'cdn/db/app.json'), true);
	}

	public function index()
	{
		if ($this->aplikasi) {
			return [
				'error' => false,
				'message' => "data berhasil ditemukan",
				'data' => $this->aplikasi
			];
		} else {
			file_put_contents(FCPATH . 'cdn/db/app.json', json_encode(['nama' => "Skripsi", "instansi" => "Fopegram", "icon" => "", "informasi" => "-"]));
			return [
				'error' => true,
				'message' => "data belum ada. Silahkan refresh halaman"
			];
		}
	}

	public function edit($input)
	{
		$data = [
			'nama' => $input['nama'],
			'instansi' => $input['instansi'],
			'informasi' => $input['informasi']
		];

		$validation = $this->app->validate($data);

		if ($validation === true) {
			if ($input['icon']) {
				$icon_file = base64_decode(explode(';base64,', $input['icon'])[1]);
				$icon_nama = date('Ymdhis') . '.png';
				file_put_contents(FCPATH . 'cdn/img/icons/' . $icon_nama, $icon_file);
				if ($this->aplikasi['icon']) {
					unlink(FCPATH . 'cdn/img/icon/' . $this->aplikasi['icon']);
				}
				$data['icon'] = $icon_nama;
			} else {
				$data['icon'] = $this->aplikasi['icon'];
			}
			file_put_contents(FCPATH . 'cdn/db/app.json', json_encode($data));

			$hasil = [
				'error' => false,
				'message' => "data berhasil diedit"
			];
		} else {
			$hasil = $validation;
		}

		return $hasil;
	}

}

/* End of file Pengaturan_model.php */
/* Location: ./application/models/Pengaturan_model.php */