<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hasil_penelitian_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Email_model', 'emailm');
	}

	protected $table = "hasil_penelitian";

	public function edit($input, $id)
	{
		$kondisi = [
			'penelitian_id' => $id
		];

		$penelitian = $this->db->get_where($this->table, $kondisi)->row_array();

		$data = [
			'status' => $input['status']
		];

		if ($input['tipe'] == 'admin') {
			$data['berita_acara'] = $input['berita_acara'];
			$data['masukan'] = $input['masukan'];
		}

		$validation = $this->app->validate($data);

		if ($validation == true) {
			if ($input['tipe'] == 'admin') {
				$berita_acara_file = explode(';base64,', $input['berita_acara'])[1];
				$berita_acara_nama = date('Ymdhis') . '.pdf';
				file_put_contents(FCPATH . 'cdn/vendor/berita_acara/' . $berita_acara_nama, base64_decode($berita_acara_file));
				$data['berita_acara'] = $berita_acara_nama;

				$masukan_file = explode(';base64,', $input['masukan'])[1];
				$masukan_nama = date('Ymdhis') . '.pdf';
				file_put_contents(FCPATH . 'cdn/vendor/masukan/' . $masukan_nama, base64_decode($masukan_file));
				$data['masukan'] = $masukan_nama;
			}
			$this->db->update($this->table, $data, ['penelitian_id' => $id]);

			$validation = $this->app->validate($data);

			if ($validation === true) {
				if ($input['sk_tim']) {
					$sk_tim_file = explode(';base64,', $input['sk_tim'])[1];
					$sk_tim_nama = date('Ymdhis') . '.pdf';
					file_put_contents(FCPATH . 'cdn/vendor/sk_tim/' . $sk_tim_nama, base64_decode($sk_tim_file));
					$data['sk_tim'] = $sk_tim_nama;
				}

				$this->db->update('penelitian', $data, ['id' => $id]);

				if ($input['status'] != $input['def_status']) {
					$email = $input['email'];
					$isi_email = '';
					$subjek_email = '';

					if ($input['status'] == 1) {
						$isi_email = '
						<p>Seminar hasil penelitian anda telah disetujui</p>
						';
						$subjek_email = 'Seminar Hasil Penelitian Disetujui';
					} elseif ($input['status'] == 2) {
						$isi_email = '
						<p>Seminar hasil penelitian anda telah ditolak</p>
						';
						$subjek_email = 'Seminar Hasil Penelitian Ditolak';
					}
					$this->emailm->send($subjek_email, $email, $isi_email);
				}

				$hasil = [
					'error' => false,
					'message' => "data berhasil diedit"
				];
			} else {
				$hasil = $validation;
			}
		} else {
			$hasil = $validation;
		}

		return $hasil;
	}
}

/* End of file Hasil_penelitian_model.php */
/* Location: ./application/models/Hasil_penelitian_model.php */